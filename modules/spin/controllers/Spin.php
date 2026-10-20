<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Controller Frontend Spin Wheel
 * 
 * Mengatur alur verifikasi token QR, validasi nomor pesanan transaksi,
 * eksekusi engine roda putar hadiah (Spin Wheel), serta klaim via WhatsApp.
 */
class Spin extends MY_Controller {

    public function __construct()
    {
        parent::__construct();
        $this->output->enable_profiler(FALSE);
        $this->load->library(['session', 'form_validation']);
        $this->load->helper(['url', 'form']);
        $this->load->model('prizes/model_prizes');
    }

    /**
     * Page 1: Form Verifikasi Token & Data Pembeli
     *
     * @param string $qr_token
     */
    public function index($qr_token = null)
    {
        if (empty($qr_token)) {
            $qr_token = $this->input->get('token');
        }

        $data = [
            'qr_token'    => $qr_token,
            'token_valid' => false,
            'token_data'  => null,
            'error_msg'   => null
        ];

        if (!empty($qr_token)) {
            // Cek token di tabel unique_qr_tokens join batches
            $token = $this->db->select('unique_qr_tokens.*, batches.batch_produksi, batches.tgl_expired')
                              ->from('unique_qr_tokens')
                              ->join('batches', 'batches.id = unique_qr_tokens.batch_id')
                              ->where('unique_qr_tokens.qr_token', $qr_token)
                              ->get()
                              ->row();

            if (!$token) {
                $data['error_msg'] = 'Kode QR Token tidak ditemukan atau tidak valid.';
            } elseif ($token->status_verifikasi === 'VERIFIED') {
                $data['error_msg'] = 'Kode QR Token ini sudah pernah digunakan dan terverifikasi pada ' . date('d M Y H:i', strtotime($token->tanggal_verifikasi)) . ' WIB.';
            } elseif (strtotime($token->tgl_expired) < strtotime(date('Y-m-d'))) {
                $data['error_msg'] = 'Kode QR Token ini sudah kedaluwarsa pada ' . date('d M Y', strtotime($token->tgl_expired)) . '.';
            } else {
                $data['token_valid'] = true;
                $data['token_data']  = $token;
            }
        }

        $this->load->view('spin/index', $data);
    }

    /**
     * Endpoint AJAX: Submit Verifikasi Data Pembeli & Validasi Pesanan
     */
    public function submit_verification()
    {
        $this->form_validation->set_rules('qr_token', 'Kode QR Token', 'trim|required');
        $this->form_validation->set_rules('no_pesanan', 'Nomor Pesanan', 'trim|required');
        $this->form_validation->set_rules('nama_pemenang', 'Nama Lengkap', 'trim|required|max_length[100]');
        $this->form_validation->set_rules('no_whatsapp', 'Nomor WhatsApp', 'trim|required|max_length[30]');

        if ($this->form_validation->run() === FALSE) {
            echo json_encode([
                'success' => false,
                'message' => validation_errors()
            ]);
            return;
        }

        $qr_token       = trim($this->input->post('qr_token'));
        $no_pesanan     = trim($this->input->post('no_pesanan'));
        $nama_pemenang  = trim($this->input->post('nama_pemenang'));
        $no_whatsapp    = trim($this->input->post('no_whatsapp'));

        // 1. Cek Token QR di database
        $token = $this->db->select('unique_qr_tokens.*, batches.tgl_expired')
                          ->from('unique_qr_tokens')
                          ->join('batches', 'batches.id = unique_qr_tokens.batch_id')
                          ->where('unique_qr_tokens.qr_token', $qr_token)
                          ->get()
                          ->row();

        if (!$token) {
            echo json_encode(['success' => false, 'message' => 'Token QR tidak ditemukan.']);
            return;
        }

        if ($token->status_verifikasi === 'VERIFIED') {
            echo json_encode(['success' => false, 'message' => 'Token QR ini sudah pernah digunakan sebelumnya.']);
            return;
        }

        if (strtotime($token->tgl_expired) < strtotime(date('Y-m-d'))) {
            echo json_encode(['success' => false, 'message' => 'Token QR ini sudah kedaluwarsa.']);
            return;
        }

        // 2. Cek Nomor Pesanan di tabel transactions
        $trx = $this->db->where('no_pesanan', $no_pesanan)->get('transactions')->row();
        if (!$trx) {
            echo json_encode([
                'success' => false,
                'message' => 'Nomor Pesanan <strong>' . htmlspecialchars($no_pesanan) . '</strong> tidak ditemukan di database transaksi. Pastikan nomor pesanan sesuai dengan struk/marketplace Anda.'
            ]);
            return;
        }

        if ((int)$trx->is_used_spin === 1) {
            echo json_encode([
                'success' => false,
                'message' => 'Nomor Pesanan <strong>' . htmlspecialchars($no_pesanan) . '</strong> sudah pernah digunakan untuk spin hadiah sebelumnya.'
            ]);
            return;
        }

        // 3. Update Token QR menjadi VERIFIED & tambah counter verifikasi di batches
        $this->db->where('id', $token->id)->update('unique_qr_tokens', [
            'status_verifikasi'  => 'VERIFIED',
            'tanggal_verifikasi' => date('Y-m-d H:i:s')
        ]);

        $this->db->set('jumlah_terverifikasi', 'jumlah_terverifikasi + 1', FALSE)
                 ->where('id', $token->batch_id)
                 ->update('batches');

        // 4. Set Session Spin
        $this->session->set_userdata('spin_session', [
            'token_id'       => $token->id,
            'qr_token'       => $token->qr_token,
            'no_pesanan'     => $no_pesanan,
            'nama_pemenang'  => $nama_pemenang,
            'no_whatsapp'    => $no_whatsapp,
            'transaction_id' => $trx->id,
            'verified_at'    => time(),
            'has_spun'       => false
        ]);

        echo json_encode([
            'success'  => true,
            'message'  => 'Verifikasi berhasil! Mengalihkan ke roda keberuntungan...',
            'redirect' => site_url('spin/wheel')
        ]);
    }

    /**
     * Page 2: Halaman Spin Wheel Interaktif
     */
    public function wheel()
    {
        $session = $this->session->userdata('spin_session');
        if (!$session) {
            redirect('spin');
            return;
        }

        // Ambil semua hadiah aktif
        $prizes = $this->db->where('is_active', 1)
                           ->order_by('id', 'ASC')
                           ->get('prizes')
                           ->result();

        $data = [
            'session_data' => $session,
            'prizes'       => $prizes
        ];

        $this->load->view('spin/wheel', $data);
    }

    /**
     * Endpoint AJAX: Putar Roda & Tentukan Hadiah (Engine Spin)
     */
    public function play_spin()
    {
        $session = $this->session->userdata('spin_session');
        if (!$session) {
            echo json_encode(['success' => false, 'message' => 'Sesi Anda telah berakhir. Silakan verifikasi ulang.']);
            return;
        }

        if (!empty($session['has_spun']) && $session['has_spun'] === true) {
            echo json_encode(['success' => false, 'message' => 'Anda sudah melakukan spin untuk sesi ini.']);
            return;
        }

        // Cek kembali transaksi untuk mencegah race condition double spin
        $trx = $this->db->where('no_pesanan', $session['no_pesanan'])->get('transactions')->row();
        if (!$trx || (int)$trx->is_used_spin === 1) {
            echo json_encode(['success' => false, 'message' => 'Nomor pesanan ini sudah digunakan untuk spin.']);
            return;
        }

        // Jalankan Algoritma Engine Spin
        $winning_prize = $this->model_prizes->get_winning_prize();

        if (!$winning_prize) {
            echo json_encode(['success' => false, 'message' => 'Mohon maaf, saat ini semua stok hadiah sedang kosong atau belum memenuhi syarat kuota. Silakan hubungi admin.']);
            return;
        }

        // Hitung batas waktu klaim 48 jam (2x24 jam)
        $waktu_spin = date('Y-m-d H:i:s');
        $batas_waktu_klaim = date('Y-m-d H:i:s', strtotime('+48 hours'));

        // Simpan ke spin_histories
        $history_data = [
            'qr_token_id'       => $session['token_id'],
            'no_pesanan'        => $session['no_pesanan'],
            'nama_pemenang'     => $session['nama_pemenang'],
            'no_whatsapp'       => $session['no_whatsapp'],
            'alamat_pengiriman' => null,
            'prize_id'          => $winning_prize->id,
            'jenis_spin'        => 'Spin Wheel',
            'waktu_spin'        => $waktu_spin,
            'batas_waktu_klaim' => $batas_waktu_klaim,
            'status_klaim'      => 'Unclaimed',
            'status_hadiah'     => 'Pending',
            'status_wa'         => 'Belum Broadcast'
        ];

        $this->db->insert('spin_histories', $history_data);
        $history_id = $this->db->insert_id();

        // Tandai transactions is_used_spin = 1
        $this->db->where('no_pesanan', $session['no_pesanan'])->update('transactions', ['is_used_spin' => 1]);

        // Update session
        $session['has_spun']          = true;
        $session['history_id']        = $history_id;
        $session['winning_prize_id']  = $winning_prize->id;
        $session['winning_prize_name']= $winning_prize->nama_hadiah;
        $this->session->set_userdata('spin_session', $session);

        // Ambil semua hadiah aktif untuk menghitung index slice pada roda
        $all_prizes = $this->db->where('is_active', 1)->order_by('id', 'ASC')->get('prizes')->result();
        $target_index = 0;
        foreach ($all_prizes as $idx => $p) {
            if ($p->id == $winning_prize->id) {
                $target_index = $idx;
                break;
            }
        }

        echo json_encode([
            'success'           => true,
            'prize_id'          => $winning_prize->id,
            'prize_name'        => $winning_prize->nama_hadiah,
            'prize_category'    => $winning_prize->kategori_hadiah,
            'target_index'      => $target_index,
            'history_id'        => $history_id,
            'waktu_spin'        => $waktu_spin,
            'batas_waktu_klaim' => date('d M Y H:i', strtotime($batas_waktu_klaim)) . ' WIB'
        ]);
    }

    /**
     * Endpoint AJAX: Simpan Alamat Pengiriman & Generate Link Klaim WhatsApp
     */
    public function claim_prize()
    {
        $session = $this->session->userdata('spin_session');
        $history_id = $this->input->post('history_id');
        $alamat = trim($this->input->post('alamat_pengiriman'));

        if (!$history_id) {
            $history_id = isset($session['history_id']) ? $session['history_id'] : null;
        }

        if (!$history_id) {
            echo json_encode(['success' => false, 'message' => 'Data spin tidak ditemukan.']);
            return;
        }

        if (empty($alamat)) {
            echo json_encode(['success' => false, 'message' => 'Alamat pengiriman wajib diisi untuk klaim hadiah.']);
            return;
        }

        // Ambil record riwayat spin
        $history = $this->db->select('spin_histories.*, prizes.nama_hadiah')
                            ->from('spin_histories')
                            ->join('prizes', 'prizes.id = spin_histories.prize_id')
                            ->where('spin_histories.id', $history_id)
                            ->get()
                            ->row();

        if (!$history) {
            echo json_encode(['success' => false, 'message' => 'Riwayat hadiah tidak ditemukan.']);
            return;
        }

        // Update alamat pengiriman dan status_klaim menjadi 'Claimed'
        $this->db->where('id', $history_id)->update('spin_histories', [
            'alamat_pengiriman' => $alamat,
            'status_klaim'      => 'Claimed'
        ]);

        // Ambil nomor WA Admin dari cc_options atau fallback
        $admin_wa = get_option('admin_whatsapp', '6281234567890');
        $admin_wa = preg_replace('/[^0-9]/', '', $admin_wa);
        if (substr($admin_wa, 0, 1) === '0') {
            $admin_wa = '62' . substr($admin_wa, 1);
        }

        // Susun teks konfirmasi klaim ke WhatsApp
        $msg  = "🎉 *KONFIRMASI KLAIM HADIAH SPIN WHEEL* 🎉\n\n";
        $msg .= "Halo Admin, saya ingin konfirmasi klaim hadiah saya:\n";
        $msg .= "━━━━━━━━━━━━━━━━━━━━━━\n";
        $msg .= "🔖 *No. Pesanan* : " . $history->no_pesanan . "\n";
        $msg .= "👤 *Nama*        : " . $history->nama_pemenang . "\n";
        $msg .= "📱 *No. WhatsApp*: " . $history->no_whatsapp . "\n";
        $msg .= "🎁 *Hadiah*      : *" . $history->nama_hadiah . "*\n";
        $msg .= "🕒 *Waktu Spin*  : " . date('d-m-Y H:i', strtotime($history->waktu_spin)) . " WIB\n";
        $msg .= "📍 *Alamat Kirim*: " . $alamat . "\n";
        $msg .= "━━━━━━━━━━━━━━━━━━━━━━\n\n";
        $msg .= "Mohon segera diproses ya Min. Terima kasih banyak! 🙏";

        $wa_url = "https://api.whatsapp.com/send?phone=" . $admin_wa . "&text=" . urlencode($msg);

        // Bersihkan session spin setelah klaim
        $this->session->unset_userdata('spin_session');

        echo json_encode([
            'success' => true,
            'message' => 'Alamat berhasil disimpan! Mengalihkan ke WhatsApp Admin...',
            'wa_url'  => $wa_url
        ]);
    }
}
