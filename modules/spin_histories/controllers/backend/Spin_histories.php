<?php
defined('BASEPATH') OR exit('No direct script access allowed');


/**
*| --------------------------------------------------------------------------
*| Spin Histories Controller
*| --------------------------------------------------------------------------
*| Spin Histories site
*|
*/
class Spin_histories extends Admin	
{
	
	public function __construct()
	{
		parent::__construct();

		$this->load->model('model_spin_histories');
		$this->lang->load('web_lang', $this->current_lang);
	}

	/**
	* show all Spin Historiess
	*
	* @var $offset String
	*/
	public function index($offset = 0)
	{
		$this->is_allowed('spin_histories_list');

		$filter = $this->input->get('q');
		$field 	= $this->input->get('f');

		$this->data['spin_historiess'] = $this->model_spin_histories->get($filter, $field, $this->limit_page, $offset);
		$this->data['spin_histories_counts'] = $this->model_spin_histories->count_all($filter, $field);

		$config = [
			'base_url'     => 'administrator/spin_histories/index/',
			'total_rows'   => $this->model_spin_histories->count_all($filter, $field),
			'per_page'     => $this->limit_page,
			'uri_segment'  => 4,
		];

		$this->data['pagination'] = $this->pagination($config);

		// Summary 4 Box Stats
		$this->data['summary_total_scan'] = $this->db->where('status_verifikasi', 'VERIFIED')->count_all_results('unique_qr_tokens');
		$this->data['summary_total_spin'] = $this->db->count_all_results('spin_histories');
		$this->data['summary_total_claim'] = $this->db->where('status_klaim', 'Claimed')->count_all_results('spin_histories');

		$total_nominal = $this->db->select_sum('prizes.nilai_hadiah')
		                          ->from('spin_histories')
		                          ->join('prizes', 'prizes.id = spin_histories.prize_id')
		                          ->where('spin_histories.status_klaim', 'Claimed')
		                          ->get()
		                          ->row();
		$this->data['summary_total_nominal_claim'] = ($total_nominal && $total_nominal->nilai_hadiah) ? (float)$total_nominal->nilai_hadiah : 0.00;

		$this->template->title('Spin Histories List');
		$this->render('backend/standart/administrator/spin_histories/spin_histories_list', $this->data);
	}
	
	/**
	* Add new spin_historiess
	*
	*/
	public function add()
	{
		$this->is_allowed('spin_histories_add');

		$this->template->title('Spin Histories New');
		$this->render('backend/standart/administrator/spin_histories/spin_histories_add', $this->data);
	}

	/**
	* Add New Spin Historiess
	*
	* @return JSON
	*/
	public function add_save()
	{
		if (!$this->is_allowed('spin_histories_add', false)) {
			echo json_encode([
				'success' => false,
				'message' => cclang('sorry_you_do_not_have_permission_to_access')
				]);
			exit;
		}

		$this->form_validation->set_rules('qr_token_id', 'Qr Token Id', 'trim|required|max_length[10]');
		$this->form_validation->set_rules('no_pesanan', 'No Pesanan', 'trim|required|max_length[100]');
		$this->form_validation->set_rules('nama_pemenang', 'Nama Pemenang', 'trim|required|max_length[100]');
		$this->form_validation->set_rules('no_whatsapp', 'No Whatsapp', 'trim|required|max_length[30]');
		$this->form_validation->set_rules('alamat_pengiriman', 'Alamat Pengiriman', 'trim|required');
		$this->form_validation->set_rules('prize_id', 'Prize Id', 'trim|required|max_length[10]');
		$this->form_validation->set_rules('jenis_spin', 'Jenis Spin', 'trim|required|max_length[50]');
		$this->form_validation->set_rules('waktu_spin', 'Waktu Spin', 'trim|required');
		$this->form_validation->set_rules('batas_waktu_klaim', 'Batas Waktu Klaim', 'trim|required');
		$this->form_validation->set_rules('status_klaim', 'Status Klaim', 'trim|required');
		$this->form_validation->set_rules('status_hadiah', 'Status Hadiah', 'trim|required');
		$this->form_validation->set_rules('no_resi', 'No Resi', 'trim|required|max_length[100]');
		$this->form_validation->set_rules('jasa_kirim', 'Jasa Kirim', 'trim|required|max_length[50]');
		$this->form_validation->set_rules('status_wa', 'Status Wa', 'trim|required');
		

		if ($this->form_validation->run()) {
		
			$save_data = [
				'qr_token_id' => $this->input->post('qr_token_id'),
				'no_pesanan' => $this->input->post('no_pesanan'),
				'nama_pemenang' => $this->input->post('nama_pemenang'),
				'no_whatsapp' => $this->input->post('no_whatsapp'),
				'alamat_pengiriman' => $this->input->post('alamat_pengiriman'),
				'prize_id' => $this->input->post('prize_id'),
				'jenis_spin' => $this->input->post('jenis_spin'),
				'waktu_spin' => $this->input->post('waktu_spin'),
				'batas_waktu_klaim' => $this->input->post('batas_waktu_klaim'),
				'status_klaim' => $this->input->post('status_klaim'),
				'status_hadiah' => $this->input->post('status_hadiah'),
				'no_resi' => $this->input->post('no_resi'),
				'jasa_kirim' => $this->input->post('jasa_kirim'),
				'status_wa' => $this->input->post('status_wa'),
			];

			
			$save_spin_histories = $this->model_spin_histories->store($save_data);
            

			if ($save_spin_histories) {
				if ($this->input->post('save_type') == 'stay') {
					$this->data['success'] = true;
					$this->data['id'] 	   = $save_spin_histories;
					$this->data['message'] = cclang('success_save_data_stay', [
						anchor('administrator/spin_histories/edit/' . $save_spin_histories, 'Edit Spin Histories'),
						anchor('administrator/spin_histories', ' Go back to list')
					]);
				} else {
					set_message(
						cclang('success_save_data_redirect', [
						anchor('administrator/spin_histories/edit/' . $save_spin_histories, 'Edit Spin Histories')
					]), 'success');

            		$this->data['success'] = true;
					$this->data['redirect'] = base_url('administrator/spin_histories');
				}
			} else {
				if ($this->input->post('save_type') == 'stay') {
					$this->data['success'] = false;
					$this->data['message'] = cclang('data_not_change');
				} else {
            		$this->data['success'] = false;
            		$this->data['message'] = cclang('data_not_change');
					$this->data['redirect'] = base_url('administrator/spin_histories');
				}
			}

		} else {
			$this->data['success'] = false;
			$this->data['message'] = validation_errors();
		}

		echo json_encode($this->data);
	}
	
		/**
	* Update view Spin Historiess
	*
	* @var $id String
	*/
	public function edit($id)
	{
		$this->is_allowed('spin_histories_update');

		$this->data['spin_histories'] = $this->model_spin_histories->find($id);

		$this->template->title('Spin Histories Update');
		$this->render('backend/standart/administrator/spin_histories/spin_histories_update', $this->data);
	}

	/**
	* Update Spin Historiess
	*
	* @var $id String
	*/
	public function edit_save($id)
	{
		if (!$this->is_allowed('spin_histories_update', false)) {
			echo json_encode([
				'success' => false,
				'message' => cclang('sorry_you_do_not_have_permission_to_access')
				]);
			exit;
		}
		
		$this->form_validation->set_rules('qr_token_id', 'Qr Token Id', 'trim|required|max_length[10]');
		$this->form_validation->set_rules('no_pesanan', 'No Pesanan', 'trim|required|max_length[100]');
		$this->form_validation->set_rules('nama_pemenang', 'Nama Pemenang', 'trim|required|max_length[100]');
		$this->form_validation->set_rules('no_whatsapp', 'No Whatsapp', 'trim|required|max_length[30]');
		$this->form_validation->set_rules('alamat_pengiriman', 'Alamat Pengiriman', 'trim|required');
		$this->form_validation->set_rules('prize_id', 'Prize Id', 'trim|required|max_length[10]');
		$this->form_validation->set_rules('jenis_spin', 'Jenis Spin', 'trim|required|max_length[50]');
		$this->form_validation->set_rules('waktu_spin', 'Waktu Spin', 'trim|required');
		$this->form_validation->set_rules('batas_waktu_klaim', 'Batas Waktu Klaim', 'trim|required');
		$this->form_validation->set_rules('status_klaim', 'Status Klaim', 'trim|required');
		$this->form_validation->set_rules('status_hadiah', 'Status Hadiah', 'trim|required');
		$this->form_validation->set_rules('no_resi', 'No Resi', 'trim|required|max_length[100]');
		$this->form_validation->set_rules('jasa_kirim', 'Jasa Kirim', 'trim|required|max_length[50]');
		$this->form_validation->set_rules('status_wa', 'Status Wa', 'trim|required');
		
		if ($this->form_validation->run()) {
		
			$save_data = [
				'qr_token_id' => $this->input->post('qr_token_id'),
				'no_pesanan' => $this->input->post('no_pesanan'),
				'nama_pemenang' => $this->input->post('nama_pemenang'),
				'no_whatsapp' => $this->input->post('no_whatsapp'),
				'alamat_pengiriman' => $this->input->post('alamat_pengiriman'),
				'prize_id' => $this->input->post('prize_id'),
				'jenis_spin' => $this->input->post('jenis_spin'),
				'waktu_spin' => $this->input->post('waktu_spin'),
				'batas_waktu_klaim' => $this->input->post('batas_waktu_klaim'),
				'status_klaim' => $this->input->post('status_klaim'),
				'status_hadiah' => $this->input->post('status_hadiah'),
				'no_resi' => $this->input->post('no_resi'),
				'jasa_kirim' => $this->input->post('jasa_kirim'),
				'status_wa' => $this->input->post('status_wa'),
			];

			
			$save_spin_histories = $this->model_spin_histories->change($id, $save_data);

			if ($save_spin_histories) {
				if ($this->input->post('save_type') == 'stay') {
					$this->data['success'] = true;
					$this->data['id'] 	   = $id;
					$this->data['message'] = cclang('success_update_data_stay', [
						anchor('administrator/spin_histories', ' Go back to list')
					]);
				} else {
					set_message(
						cclang('success_update_data_redirect', [
					]), 'success');

            		$this->data['success'] = true;
					$this->data['redirect'] = base_url('administrator/spin_histories');
				}
			} else {
				if ($this->input->post('save_type') == 'stay') {
					$this->data['success'] = false;
					$this->data['message'] = cclang('data_not_change');
				} else {
            		$this->data['success'] = false;
            		$this->data['message'] = cclang('data_not_change');
					$this->data['redirect'] = base_url('administrator/spin_histories');
				}
			}
		} else {
			$this->data['success'] = false;
			$this->data['message'] = validation_errors();
		}

		echo json_encode($this->data);
	}
	
	/**
	* delete Spin Historiess
	*
	* @var $id String
	*/
	public function delete($id = null)
	{
		$this->is_allowed('spin_histories_delete');

		$this->load->helper('file');

		$arr_id = $this->input->get('id');
		$remove = false;

		if (!empty($id)) {
			$remove = $this->_remove($id);
		} elseif (count($arr_id) >0) {
			foreach ($arr_id as $id) {
				$remove = $this->_remove($id);
			}
		}

		if ($remove) {
            set_message(cclang('has_been_deleted', 'spin_histories'), 'success');
        } else {
            set_message(cclang('error_delete', 'spin_histories'), 'error');
        }

		redirect_back();
	}

		/**
	* View view Spin Historiess
	*
	* @var $id String
	*/
	public function view($id)
	{
		$this->is_allowed('spin_histories_view');

		$this->data['spin_histories'] = $this->model_spin_histories->join_avaiable()->filter_avaiable()->find($id);

		$this->template->title('Spin Histories Detail');
		$this->render('backend/standart/administrator/spin_histories/spin_histories_view', $this->data);
	}
	
	/**
	* delete Spin Historiess
	*
	* @var $id String
	*/
	private function _remove($id)
	{
		$spin_histories = $this->model_spin_histories->find($id);

		
		
		return $this->model_spin_histories->remove($id);
	}
	
	
	/**
	* Export to excel
	*
	* @return Files Excel .xls
	*/
	public function export()
	{
		$this->is_allowed('spin_histories_export');

		$this->model_spin_histories->export('spin_histories', 'spin_histories');
	}

	/**
	* Export to PDF
	*
	* @return Files PDF .pdf
	*/
	public function export_pdf()
	{
		$this->is_allowed('spin_histories_export');

		$this->model_spin_histories->pdf('spin_histories', 'spin_histories');
	}


	public function single_pdf($id = null)
	{
		$this->is_allowed('spin_histories_export');

		$table = $title = 'spin_histories';
		$this->load->library('HtmlPdf');
      
        $config = array(
            'orientation' => 'p',
            'format' => 'a4',
            'marges' => array(5, 5, 5, 5)
        );

        $this->pdf = new HtmlPdf($config);
        $this->pdf->setDefaultFont('stsongstdlight'); 

        $result = $this->db->get($table);
       
        $data = $this->model_spin_histories->find($id);
        $fields = $result->list_fields();

        $content = $this->pdf->loadHtmlPdf('core_template/pdf/pdf_single', [
            'data' => $data,
            'fields' => $fields,
            'title' => $title
        ], TRUE);

        $this->pdf->initialize($config);
        $this->pdf->pdf->SetDisplayMode('fullpage');
        $this->pdf->writeHTML($content);
        $this->pdf->Output($table.'.pdf', 'H');
	}

	/**
	* Follow Up / Broadcast via WhatsApp
	*
	* @param int $id
	*/
	public function followup_wa($id = null)
	{
		$this->is_allowed('spin_histories_view');

		if (!$id) {
			redirect('administrator/spin_histories');
		}

		$history = $this->db->select('spin_histories.*, prizes.nama_hadiah, prizes.nilai_hadiah')
		                    ->from('spin_histories')
		                    ->join('prizes', 'prizes.id = spin_histories.prize_id', 'left')
		                    ->where('spin_histories.id', $id)
		                    ->get()
		                    ->row();

		if (!$history) {
			show_404();
		}

		// Update status_wa menjadi 'Sudah Broadcast'
		$this->db->where('id', $id)->update('spin_histories', ['status_wa' => 'Sudah Broadcast']);

		// Format nomor WA
		$phone = preg_replace('/[^0-9]/', '', $history->no_whatsapp);
		if (substr($phone, 0, 1) === '0') {
			$phone = '62' . substr($phone, 1);
		}

		$nama = $history->nama_pemenang;
		$no_pesanan = $history->no_pesanan;
		$hadiah = $history->nama_hadiah;
		$status_klaim = $history->status_klaim;
		$status_hadiah = $history->status_hadiah;
		$batas = date('d M Y H:i', strtotime($history->batas_waktu_klaim));

		$message = "Halo Kak *{$nama}*,\n\n";
		$message .= "Terima kasih telah berbelanja dengan No. Pesanan: *{$no_pesanan}*.\n";
		$message .= "Selamat! Kakak berhasil memenangkan hadiah Spin Wheel: *{$hadiah}* 🎉\n\n";
		$message .= "Status Klaim: *{$status_klaim}*\n";
		$message .= "Status Pengiriman: *{$status_hadiah}*\n";
		if ($history->no_resi) {
			$message .= "No. Resi: *{$history->no_resi}* ({$history->jasa_kirim})\n";
		}
		$message .= "Batas Waktu Klaim: *{$batas} WIB*\n\n";
		$message .= "Mohon konfirmasi atau lengkapi alamat pengiriman Kakak agar hadiah dapat segera kami proses. Terima kasih! 🙏";

		$wa_url = "https://api.whatsapp.com/send?phone=" . $phone . "&text=" . urlencode($message);
		redirect($wa_url);
	}

	/**
	* Get Transaction Detail AJAX for Modal
	*
	* @param string $no_pesanan
	*/
	public function get_transaction_detail($no_pesanan = null)
	{
		$this->is_allowed('spin_histories_view');

		if (!$no_pesanan) {
			$no_pesanan = $this->input->get('no_pesanan');
		}

		$trx = $this->db->where('no_pesanan', $no_pesanan)->get('transactions')->row();

		if (!$trx) {
			echo '<div class="alert alert-warning"><i class="fa fa-info-circle"></i> Data transaksi dengan nomor pesanan <strong>' . htmlspecialchars($no_pesanan) . '</strong> tidak ditemukan di database.</div>';
			return;
		}

		$spin_badge = ($trx->is_used_spin == 1) 
			? '<span class="label label-danger">Sudah Digunakan Spin</span>' 
			: '<span class="label label-success">Belum Digunakan</span>';

		$html = '
		<table class="table table-bordered table-striped">
			<tbody>
				<tr>
					<th style="width: 35%;">No. Pesanan</th>
					<td><strong>' . htmlspecialchars($trx->no_pesanan) . '</strong></td>
				</tr>
				<tr>
					<th>Tanggal Transaksi</th>
					<td>' . date('d M Y H:i', strtotime($trx->tanggal_transaksi)) . '</td>
				</tr>
				<tr>
					<th>Nama Cabang</th>
					<td>' . htmlspecialchars($trx->nama_cabang) . '</td>
				</tr>
				<tr>
					<th>Detail Barang</th>
					<td>' . nl2br(htmlspecialchars($trx->detail_barang)) . '</td>
				</tr>
				<tr>
					<th>Qty</th>
					<td>' . $trx->qty . ' pcs</td>
				</tr>
				<tr>
					<th>Harga Barang</th>
					<td>' . idr($trx->harga_barang) . '</td>
				</tr>
				<tr>
					<th>Sub Total</th>
					<td>' . idr($trx->sub_total) . '</td>
				</tr>
				<tr>
					<th>Ongkir</th>
					<td>' . idr($trx->ongkir) . '</td>
				</tr>
				<tr>
					<th>Total Bayar</th>
					<td><strong>' . idr($trx->total_bayar) . '</strong></td>
				</tr>
				<tr>
					<th>Purchase Specialist</th>
					<td>' . htmlspecialchars($trx->purchase_specialist) . ' (' . htmlspecialchars($trx->no_purchase_specialist) . ')</td>
				</tr>
				<tr>
					<th>Status Transaksi</th>
					<td><span class="label label-info">' . htmlspecialchars($trx->status_transaksi) . '</span></td>
				</tr>
				<tr>
					<th>Status Pemakaian Spin</th>
					<td>' . $spin_badge . '</td>
				</tr>
			</tbody>
		</table>';

		echo $html;
	}
}

/* End of file spin_histories.php */
/* Location: ./application/controllers/administrator/Spin Histories.php */