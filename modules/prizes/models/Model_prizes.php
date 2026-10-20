<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Model_prizes extends MY_Model {

    private $primary_key    = 'id';
    private $table_name     = 'prizes';
    private $field_search   = ['nama_hadiah', 'kategori_hadiah', 'nilai_hadiah', 'stok_tersedia', 'max_frekuensi_bulanan', 'max_frekuensi_tahunan', 'min_omzet_tercapai', 'bobot_probabilitas', 'warna_slice', 'is_active'];

    public function __construct()
    {
        $config = array(
            'primary_key'   => $this->primary_key,
            'table_name'    => $this->table_name,
            'field_search'  => $this->field_search,
         );

        parent::__construct($config);
    }

    public function count_all($q = null, $field = null)
    {
        $iterasi = 1;
        $num = count($this->field_search);
        $where = NULL;
        $q = $this->scurity($q);
        $field = $this->scurity($field);

        if (empty($field)) {
            foreach ($this->field_search as $field) {
                if ($iterasi == 1) {
                    $where .= "prizes.".$field . " LIKE '%" . $q . "%' ";
                } else {
                    $where .= "OR " . "prizes.".$field . " LIKE '%" . $q . "%' ";
                }
                $iterasi++;
            }

            $where = '('.$where.')';
        } else {
            $where .= "(" . "prizes.".$field . " LIKE '%" . $q . "%' )";
        }

        $this->join_avaiable()->filter_avaiable();
        $this->db->where($where);
        $query = $this->db->get($this->table_name);

        return $query->num_rows();
    }

    public function get($q = null, $field = null, $limit = 0, $offset = 0, $select_field = [])
    {
        $iterasi = 1;
        $num = count($this->field_search);
        $where = NULL;
        $q = $this->scurity($q);
        $field = $this->scurity($field);

        if (empty($field)) {
            foreach ($this->field_search as $field) {
                if ($iterasi == 1) {
                    $where .= "prizes.".$field . " LIKE '%" . $q . "%' ";
                } else {
                    $where .= "OR " . "prizes.".$field . " LIKE '%" . $q . "%' ";
                }
                $iterasi++;
            }

            $where = '('.$where.')';
        } else {
            $where .= "(" . "prizes.".$field . " LIKE '%" . $q . "%' )";
        }

        if (is_array($select_field) AND count($select_field)) {
            $this->db->select($select_field);
        }
        
        $this->join_avaiable()->filter_avaiable();
        $this->db->where($where);
        $this->db->limit($limit, $offset);
                $this->db->order_by('prizes.'.$this->primary_key, "DESC");
                $query = $this->db->get($this->table_name);

        return $query->result();
    }

    public function join_avaiable() {
        
        $this->db->select('prizes.*');


        return $this;
    }

    public function filter_avaiable() {

        if (!$this->aauth->is_admin()) {
            }

        return $this;
    }

    /**
     * Engine Algoritma Penentuan Hadiah Spin Wheel
     *
     * 1. Ambil semua hadiah is_active = 1 dan stok_tersedia > 0
     * 2. Hitung total omzet sistem saat ini dari transactions (SUM total_bayar)
     * 3. Filter eliminasi:
     *    - min_omzet_tercapai > current_omzet
     *    - spin_histories bulan ini >= max_frekuensi_bulanan (jika > 0)
     *    - spin_histories tahun ini >= max_frekuensi_tahunan (jika > 0)
     * 4. Weighted Random Selection berdasarkan bobot_probabilitas
     * 5. Kurangi stok_tersedia sebanyak 1
     *
     * @return object|false
     */
    public function get_winning_prize()
    {
        // 1. Ambil hadiah yang aktif dan memiliki stok
        $prizes = $this->db->where('is_active', 1)
                           ->where('stok_tersedia >', 0)
                           ->get('prizes')
                           ->result();

        if (empty($prizes)) {
            return false;
        }

        // 2. Hitung total omzet berjalan dari transactions
        $omzet_row = $this->db->select_sum('total_bayar')->get('transactions')->row();
        $current_omzet = ($omzet_row && $omzet_row->total_bayar) ? (float)$omzet_row->total_bayar : 0.00;

        $current_month = date('Y-m');
        $current_year  = date('Y');

        // 3. Filter kandidat hadiah yang memenuhi syarat
        $candidates = [];
        foreach ($prizes as $prize) {
            // Syarat minimal omzet tercapai
            if ($current_omzet < (float)$prize->min_omzet_tercapai) {
                continue;
            }

            // Batas maksimal frekuensi kemunculan bulanan
            if ((int)$prize->max_frekuensi_bulanan > 0) {
                $count_m = $this->db->where('prize_id', $prize->id)
                                    ->where("DATE_FORMAT(waktu_spin, '%Y-%m') =", $current_month)
                                    ->count_all_results('spin_histories');
                if ($count_m >= (int)$prize->max_frekuensi_bulanan) {
                    continue;
                }
            }

            // Batas maksimal frekuensi kemunculan tahunan
            if ((int)$prize->max_frekuensi_tahunan > 0) {
                $count_y = $this->db->where('prize_id', $prize->id)
                                    ->where("DATE_FORMAT(waktu_spin, '%Y') =", $current_year)
                                    ->count_all_results('spin_histories');
                if ($count_y >= (int)$prize->max_frekuensi_tahunan) {
                    continue;
                }
            }

            $candidates[] = $prize;
        }

        // Fallback jika tidak ada yang lolos filter (ambil hadiah hiburan/stok terbanyak)
        if (empty($candidates)) {
            $fallback = $this->db->where('is_active', 1)
                                 ->where('stok_tersedia >', 0)
                                 ->where('kategori_hadiah', 'HIBURAN/VOUCHER')
                                 ->order_by('bobot_probabilitas', 'DESC')
                                 ->limit(1)
                                 ->get('prizes')
                                 ->row();
            if ($fallback) {
                $candidates[] = $fallback;
            } else {
                $fallback2 = $this->db->where('is_active', 1)
                                      ->where('stok_tersedia >', 0)
                                      ->order_by('nilai_hadiah', 'ASC')
                                      ->limit(1)
                                      ->get('prizes')
                                      ->row();
                if ($fallback2) {
                    $candidates[] = $fallback2;
                } else {
                    return false;
                }
            }
        }

        // 4. Weighted Random Selection
        $total_weight = 0;
        foreach ($candidates as $c) {
            $weight = max(1, (int)$c->bobot_probabilitas);
            $total_weight += $weight;
        }

        $rand = mt_rand(1, $total_weight);
        $running_sum = 0;
        $selected_prize = null;
        foreach ($candidates as $c) {
            $weight = max(1, (int)$c->bobot_probabilitas);
            $running_sum += $weight;
            if ($rand <= $running_sum) {
                $selected_prize = $c;
                break;
            }
        }

        if (!$selected_prize) {
            $selected_prize = $candidates[0];
        }

        // 5. Kurangi stok_tersedia sebanyak 1
        $this->db->set('stok_tersedia', 'stok_tersedia - 1', FALSE)
                 ->where('id', $selected_prize->id)
                 ->where('stok_tersedia >', 0)
                 ->update('prizes');

        return $selected_prize;
    }
}

/* End of file Model_prizes.php */
/* Location: ./application/models/Model_prizes.php */