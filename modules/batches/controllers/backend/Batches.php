<?php
defined('BASEPATH') OR exit('No direct script access allowed');


/**
*| --------------------------------------------------------------------------
*| Batches Controller
*| --------------------------------------------------------------------------
*| Batches site
*|
*/
class Batches extends Admin	
{
	
	public function __construct()
	{
		parent::__construct();

		$this->load->model('model_batches');
		$this->lang->load('web_lang', $this->current_lang);
	}

	/**
	* show all Batchess
	*
	* @var $offset String
	*/
	public function index($offset = 0)
	{
		$this->is_allowed('batches_list');

		$filter = $this->input->get('q');
		$field 	= $this->input->get('f');

		$this->data['batchess'] = $this->model_batches->get($filter, $field, $this->limit_page, $offset);
		$this->data['batches_counts'] = $this->model_batches->count_all($filter, $field);

		$config = [
			'base_url'     => 'administrator/batches/index/',
			'total_rows'   => $this->model_batches->count_all($filter, $field),
			'per_page'     => $this->limit_page,
			'uri_segment'  => 4,
		];

		$this->data['pagination'] = $this->pagination($config);

		$this->template->title('Batches List');
		$this->render('backend/standart/administrator/batches/batches_list', $this->data);
	}
	
	/**
	* Add new batchess
	*
	*/
	public function add()
	{
		$this->is_allowed('batches_add');

		$last_batch = $this->db->select_max('no_akhir')->get('batches')->row();
		$no_awal = ($last_batch && $last_batch->no_akhir > 0) ? ((int)$last_batch->no_akhir + 1) : 1;
		$this->data['auto_no_awal'] = $no_awal;

		$this->template->title('Batches New');
		$this->render('backend/standart/administrator/batches/batches_add', $this->data);
	}

	/**
	* Add New Batchess
	*
	* @return JSON
	*/
	public function add_save()
	{
		if (!$this->is_allowed('batches_add', false)) {
			echo json_encode([
				'success' => false,
				'message' => cclang('sorry_you_do_not_have_permission_to_access')
				]);
			exit;
		}

		$this->form_validation->set_rules('batch_produksi', 'Batch Produksi', 'trim|required|max_length[50]');
		$this->form_validation->set_rules('jumlah_produk', 'Jumlah Produk', 'trim|required|integer|greater_than[0]');
		$this->form_validation->set_rules('tgl_expired', 'Tgl Expired', 'trim|required');

		if ($this->form_validation->run()) {
			$batch_produksi = $this->input->post('batch_produksi');
			$jumlah_produk  = (int) $this->input->post('jumlah_produk');
			$tgl_expired    = $this->input->post('tgl_expired');

			// Hitung no_awal otomatis dari database untuk mencegah race condition
			$last_batch = $this->db->select_max('no_akhir')->get('batches')->row();
			$no_awal = ($last_batch && $last_batch->no_akhir > 0) ? ((int)$last_batch->no_akhir + 1) : 1;
			$no_akhir = $no_awal + $jumlah_produk - 1;

			$save_data = [
				'batch_produksi'       => $batch_produksi,
				'no_awal'              => $no_awal,
				'no_akhir'             => $no_akhir,
				'jumlah_produk'        => $jumlah_produk,
				'jumlah_terverifikasi' => 0,
				'tgl_expired'          => $tgl_expired,
				'created_at'           => date('Y-m-d H:i:s'),
			];

			$save_batches = $this->model_batches->store($save_data);

			if ($save_batches) {
				// Bulk insert tokens dengan chunk size agar tidak timeout
				$chunk_size = 1000;
				$tokens = [];
				for ($i = 0; $i < $jumlah_produk; $i++) {
					$no_produk = $no_awal + $i;
					$qr_token = strtoupper(substr(bin2hex(random_bytes(8)), 0, 16));
					$tokens[] = [
						'batch_id'           => $save_batches,
						'no_produk'          => $no_produk,
						'qr_token'           => $qr_token,
						'status_verifikasi'  => 'UNVERIFIED',
						'tanggal_verifikasi' => null
					];

					if (count($tokens) >= $chunk_size) {
						$this->db->insert_batch('unique_qr_tokens', $tokens);
						$tokens = [];
					}
				}
				if (!empty($tokens)) {
					$this->db->insert_batch('unique_qr_tokens', $tokens);
				}

				if ($this->input->post('save_type') == 'stay') {
					$this->data['success'] = true;
					$this->data['id'] 	   = $save_batches;
					$this->data['message'] = cclang('success_save_data_stay', [
						anchor('administrator/batches/edit/' . $save_batches, 'Edit Batches'),
						anchor('administrator/batches', ' Go back to list')
					]);
				} else {
					set_message(
						cclang('success_save_data_redirect', [
						anchor('administrator/batches/edit/' . $save_batches, 'Edit Batches')
					]), 'success');

            		$this->data['success'] = true;
					$this->data['redirect'] = base_url('administrator/batches');
				}
			} else {
				if ($this->input->post('save_type') == 'stay') {
					$this->data['success'] = false;
					$this->data['message'] = cclang('data_not_change');
				} else {
            		$this->data['success'] = false;
            		$this->data['message'] = cclang('data_not_change');
					$this->data['redirect'] = base_url('administrator/batches');
				}
			}

		} else {
			$this->data['success'] = false;
			$this->data['message'] = validation_errors();
		}

		echo json_encode($this->data);
	}
	
		/**
	* Update view Batchess
	*
	* @var $id String
	*/
	public function edit($id)
	{
		$this->is_allowed('batches_update');

		$this->data['batches'] = $this->model_batches->find($id);

		$this->template->title('Batches Update');
		$this->render('backend/standart/administrator/batches/batches_update', $this->data);
	}

	/**
	* Update Batchess
	*
	* @var $id String
	*/
	public function edit_save($id)
	{
		if (!$this->is_allowed('batches_update', false)) {
			echo json_encode([
				'success' => false,
				'message' => cclang('sorry_you_do_not_have_permission_to_access')
				]);
			exit;
		}
		
		$this->form_validation->set_rules('batch_produksi', 'Batch Produksi', 'trim|required|max_length[50]');
		$this->form_validation->set_rules('no_awal', 'No Awal', 'trim|required|max_length[20]');
		$this->form_validation->set_rules('no_akhir', 'No Akhir', 'trim|required|max_length[20]');
		$this->form_validation->set_rules('jumlah_produk', 'Jumlah Produk', 'trim|required|max_length[10]');
		$this->form_validation->set_rules('jumlah_terverifikasi', 'Jumlah Terverifikasi', 'trim|required|max_length[10]');
		$this->form_validation->set_rules('tgl_expired', 'Tgl Expired', 'trim|required');
		$this->form_validation->set_rules('created_at', 'Created At', 'trim|required');
		
		if ($this->form_validation->run()) {
		
			$save_data = [
				'batch_produksi' => $this->input->post('batch_produksi'),
				'no_awal' => $this->input->post('no_awal'),
				'no_akhir' => $this->input->post('no_akhir'),
				'jumlah_produk' => $this->input->post('jumlah_produk'),
				'jumlah_terverifikasi' => $this->input->post('jumlah_terverifikasi'),
				'tgl_expired' => $this->input->post('tgl_expired'),
				'created_at' => $this->input->post('created_at'),
			];

			
			$save_batches = $this->model_batches->change($id, $save_data);

			if ($save_batches) {
				if ($this->input->post('save_type') == 'stay') {
					$this->data['success'] = true;
					$this->data['id'] 	   = $id;
					$this->data['message'] = cclang('success_update_data_stay', [
						anchor('administrator/batches', ' Go back to list')
					]);
				} else {
					set_message(
						cclang('success_update_data_redirect', [
					]), 'success');

            		$this->data['success'] = true;
					$this->data['redirect'] = base_url('administrator/batches');
				}
			} else {
				if ($this->input->post('save_type') == 'stay') {
					$this->data['success'] = false;
					$this->data['message'] = cclang('data_not_change');
				} else {
            		$this->data['success'] = false;
            		$this->data['message'] = cclang('data_not_change');
					$this->data['redirect'] = base_url('administrator/batches');
				}
			}
		} else {
			$this->data['success'] = false;
			$this->data['message'] = validation_errors();
		}

		echo json_encode($this->data);
	}
	
	/**
	* delete Batchess
	*
	* @var $id String
	*/
	public function delete($id = null)
	{
		$this->is_allowed('batches_delete');

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
            set_message(cclang('has_been_deleted', 'batches'), 'success');
        } else {
            set_message(cclang('error_delete', 'batches'), 'error');
        }

		redirect_back();
	}

		/**
	* View view Batchess
	*
	* @var $id String
	*/
	public function view($id)
	{
		$this->is_allowed('batches_view');

		$this->data['batches'] = $this->model_batches->join_avaiable()->filter_avaiable()->find($id);

		$this->template->title('Batches Detail');
		$this->render('backend/standart/administrator/batches/batches_view', $this->data);
	}
	
	/**
	* delete Batchess
	*
	* @var $id String
	*/
	private function _remove($id)
	{
		$batches = $this->model_batches->find($id);

		
		
		return $this->model_batches->remove($id);
	}
	
	
	/**
	* Export to excel
	*
	* @return Files Excel .xls
	*/
	public function export()
	{
		$this->is_allowed('batches_export');

		$this->model_batches->export('batches', 'batches');
	}

	/**
	* Export to PDF
	*
	* @return Files PDF .pdf
	*/
	public function export_pdf()
	{
		$this->is_allowed('batches_export');

		$this->model_batches->pdf('batches', 'batches');
	}


	public function single_pdf($id = null)
	{
		$this->is_allowed('batches_export');

		$table = $title = 'batches';
		$this->load->library('HtmlPdf');
      
        $config = array(
            'orientation' => 'p',
            'format' => 'a4',
            'marges' => array(5, 5, 5, 5)
        );

        $this->pdf = new HtmlPdf($config);
        $this->pdf->setDefaultFont('stsongstdlight'); 

        $result = $this->db->get($table);
       
        $data = $this->model_batches->find($id);
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
	* Export QR Tokens to Printable View / PDF
	*
	* @param int $batch_id
	*/
	public function export_tokens_pdf($batch_id = null)
	{
		$this->is_allowed('batches_export');

		if (!$batch_id) {
			redirect('administrator/batches');
		}

		$batch = $this->model_batches->find($batch_id);
		if (!$batch) {
			show_404();
		}

		$this->load->helper('qr');
		$tokens = $this->db->where('batch_id', $batch_id)
		                   ->order_by('no_produk', 'ASC')
		                   ->get('unique_qr_tokens')
		                   ->result();

		$with_spin = ($this->input->get('spin') === '1' || $this->input->get('with_spin') === '1');

		$data = [
			'batch'     => $batch,
			'tokens'    => $tokens,
			'with_spin' => $with_spin
		];

		$this->load->view('backend/standart/administrator/batches/batches_export_tokens', $data);
	}
}


/* End of file batches.php */
/* Location: ./application/controllers/administrator/Batches.php */