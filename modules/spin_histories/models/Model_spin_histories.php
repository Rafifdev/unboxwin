<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Model_spin_histories extends MY_Model {

    private $primary_key    = 'id';
    private $table_name     = 'spin_histories';
    private $field_search   = ['qr_token_id', 'no_pesanan', 'nama_pemenang', 'no_whatsapp', 'alamat_pengiriman', 'prize_id', 'jenis_spin', 'waktu_spin', 'batas_waktu_klaim', 'status_klaim', 'status_hadiah', 'no_resi', 'jasa_kirim', 'status_wa'];

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
                    $where .= "spin_histories.".$field . " LIKE '%" . $q . "%' ";
                } else {
                    $where .= "OR " . "spin_histories.".$field . " LIKE '%" . $q . "%' ";
                }
                $iterasi++;
            }

            $where = '('.$where.')';
        } else {
            $where .= "(" . "spin_histories.".$field . " LIKE '%" . $q . "%' )";
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
                    $where .= "spin_histories.".$field . " LIKE '%" . $q . "%' ";
                } else {
                    $where .= "OR " . "spin_histories.".$field . " LIKE '%" . $q . "%' ";
                }
                $iterasi++;
            }

            $where = '('.$where.')';
        } else {
            $where .= "(" . "spin_histories.".$field . " LIKE '%" . $q . "%' )";
        }

        if (is_array($select_field) AND count($select_field)) {
            $this->db->select($select_field);
        }
        
        $this->join_avaiable()->filter_avaiable();
        $this->db->where($where);
        $this->db->limit($limit, $offset);
                $this->db->order_by('spin_histories.'.$this->primary_key, "DESC");
                $query = $this->db->get($this->table_name);

        return $query->result();
    }

    public function join_avaiable() {
        $this->db->select('spin_histories.*, prizes.nama_hadiah, prizes.warna_slice, prizes.nilai_hadiah, unique_qr_tokens.qr_token');
        $this->db->join('prizes', 'prizes.id = spin_histories.prize_id', 'LEFT');
        $this->db->join('unique_qr_tokens', 'unique_qr_tokens.id = spin_histories.qr_token_id', 'LEFT');

        return $this;
    }

    public function filter_avaiable() {

        if (!$this->aauth->is_admin()) {
            }

        return $this;
    }

}

/* End of file Model_spin_histories.php */
/* Location: ./application/models/Model_spin_histories.php */