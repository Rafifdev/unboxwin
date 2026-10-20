<?php
defined('BASEPATH') OR exit('No direct script access allowed');


/**
*| --------------------------------------------------------------------------
*| Unique Qr Tokens Controller
*| --------------------------------------------------------------------------
*| Unique Qr Tokens site
*|
*/
class Unique_qr_tokens extends Admin	
{
	
	public function __construct()
	{
		parent::__construct();

		$this->load->model('model_unique_qr_tokens');
		$this->lang->load('web_lang', $this->current_lang);
	}

	/**
	* show all Unique Qr Tokenss
	*
	* @var $offset String
	*/
	public function index($offset = 0)
	{
		$this->is_allowed('unique_qr_tokens_list');

		$filter = $this->input->get('q');
		$field 	= $this->input->get('f');

		$this->data['unique_qr_tokenss'] = $this->model_unique_qr_tokens->get($filter, $field, $this->limit_page, $offset);
		$this->data['unique_qr_tokens_counts'] = $this->model_unique_qr_tokens->count_all($filter, $field);

		$config = [
			'base_url'     => 'administrator/unique_qr_tokens/index/',
			'total_rows'   => $this->model_unique_qr_tokens->count_all($filter, $field),
			'per_page'     => $this->limit_page,
			'uri_segment'  => 4,
		];

		$this->data['pagination'] = $this->pagination($config);

		$this->template->title('Unique Qr Tokens List');
		$this->render('backend/standart/administrator/unique_qr_tokens/unique_qr_tokens_list', $this->data);
	}
	
	/**
	* Add new unique_qr_tokenss
	*
	*/
	public function add()
	{
		$this->is_allowed('unique_qr_tokens_add');

		$this->template->title('Unique Qr Tokens New');
		$this->render('backend/standart/administrator/unique_qr_tokens/unique_qr_tokens_add', $this->data);
	}

	/**
	* Add New Unique Qr Tokenss
	*
	* @return JSON
	*/
	public function add_save()
	{
		if (!$this->is_allowed('unique_qr_tokens_add', false)) {
			echo json_encode([
				'success' => false,
				'message' => cclang('sorry_you_do_not_have_permission_to_access')
				]);
			exit;
		}

		$this->form_validation->set_rules('batch_id', 'Batch Id', 'trim|required|max_length[10]');
		$this->form_validation->set_rules('no_produk', 'No Produk', 'trim|required|max_length[20]');
		$this->form_validation->set_rules('qr_token', 'Qr Token', 'trim|required|max_length[20]');
		$this->form_validation->set_rules('status_verifikasi', 'Status Verifikasi', 'trim|required');
		$this->form_validation->set_rules('tanggal_verifikasi', 'Tanggal Verifikasi', 'trim|required');
		

		if ($this->form_validation->run()) {
		
			$save_data = [
				'batch_id' => $this->input->post('batch_id'),
				'no_produk' => $this->input->post('no_produk'),
				'qr_token' => $this->input->post('qr_token'),
				'status_verifikasi' => $this->input->post('status_verifikasi'),
				'tanggal_verifikasi' => $this->input->post('tanggal_verifikasi'),
			];

			
			$save_unique_qr_tokens = $this->model_unique_qr_tokens->store($save_data);
            

			if ($save_unique_qr_tokens) {
				if ($this->input->post('save_type') == 'stay') {
					$this->data['success'] = true;
					$this->data['id'] 	   = $save_unique_qr_tokens;
					$this->data['message'] = cclang('success_save_data_stay', [
						anchor('administrator/unique_qr_tokens/edit/' . $save_unique_qr_tokens, 'Edit Unique Qr Tokens'),
						anchor('administrator/unique_qr_tokens', ' Go back to list')
					]);
				} else {
					set_message(
						cclang('success_save_data_redirect', [
						anchor('administrator/unique_qr_tokens/edit/' . $save_unique_qr_tokens, 'Edit Unique Qr Tokens')
					]), 'success');

            		$this->data['success'] = true;
					$this->data['redirect'] = base_url('administrator/unique_qr_tokens');
				}
			} else {
				if ($this->input->post('save_type') == 'stay') {
					$this->data['success'] = false;
					$this->data['message'] = cclang('data_not_change');
				} else {
            		$this->data['success'] = false;
            		$this->data['message'] = cclang('data_not_change');
					$this->data['redirect'] = base_url('administrator/unique_qr_tokens');
				}
			}

		} else {
			$this->data['success'] = false;
			$this->data['message'] = validation_errors();
		}

		echo json_encode($this->data);
	}
	
		/**
	* Update view Unique Qr Tokenss
	*
	* @var $id String
	*/
	public function edit($id)
	{
		$this->is_allowed('unique_qr_tokens_update');

		$this->data['unique_qr_tokens'] = $this->model_unique_qr_tokens->find($id);

		$this->template->title('Unique Qr Tokens Update');
		$this->render('backend/standart/administrator/unique_qr_tokens/unique_qr_tokens_update', $this->data);
	}

	/**
	* Update Unique Qr Tokenss
	*
	* @var $id String
	*/
	public function edit_save($id)
	{
		if (!$this->is_allowed('unique_qr_tokens_update', false)) {
			echo json_encode([
				'success' => false,
				'message' => cclang('sorry_you_do_not_have_permission_to_access')
				]);
			exit;
		}
		
		$this->form_validation->set_rules('batch_id', 'Batch Id', 'trim|required|max_length[10]');
		$this->form_validation->set_rules('no_produk', 'No Produk', 'trim|required|max_length[20]');
		$this->form_validation->set_rules('qr_token', 'Qr Token', 'trim|required|max_length[20]');
		$this->form_validation->set_rules('status_verifikasi', 'Status Verifikasi', 'trim|required');
		$this->form_validation->set_rules('tanggal_verifikasi', 'Tanggal Verifikasi', 'trim|required');
		
		if ($this->form_validation->run()) {
		
			$save_data = [
				'batch_id' => $this->input->post('batch_id'),
				'no_produk' => $this->input->post('no_produk'),
				'qr_token' => $this->input->post('qr_token'),
				'status_verifikasi' => $this->input->post('status_verifikasi'),
				'tanggal_verifikasi' => $this->input->post('tanggal_verifikasi'),
			];

			
			$save_unique_qr_tokens = $this->model_unique_qr_tokens->change($id, $save_data);

			if ($save_unique_qr_tokens) {
				if ($this->input->post('save_type') == 'stay') {
					$this->data['success'] = true;
					$this->data['id'] 	   = $id;
					$this->data['message'] = cclang('success_update_data_stay', [
						anchor('administrator/unique_qr_tokens', ' Go back to list')
					]);
				} else {
					set_message(
						cclang('success_update_data_redirect', [
					]), 'success');

            		$this->data['success'] = true;
					$this->data['redirect'] = base_url('administrator/unique_qr_tokens');
				}
			} else {
				if ($this->input->post('save_type') == 'stay') {
					$this->data['success'] = false;
					$this->data['message'] = cclang('data_not_change');
				} else {
            		$this->data['success'] = false;
            		$this->data['message'] = cclang('data_not_change');
					$this->data['redirect'] = base_url('administrator/unique_qr_tokens');
				}
			}
		} else {
			$this->data['success'] = false;
			$this->data['message'] = validation_errors();
		}

		echo json_encode($this->data);
	}
	
	/**
	* delete Unique Qr Tokenss
	*
	* @var $id String
	*/
	public function delete($id = null)
	{
		$this->is_allowed('unique_qr_tokens_delete');

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
            set_message(cclang('has_been_deleted', 'unique_qr_tokens'), 'success');
        } else {
            set_message(cclang('error_delete', 'unique_qr_tokens'), 'error');
        }

		redirect_back();
	}

		/**
	* View view Unique Qr Tokenss
	*
	* @var $id String
	*/
	public function view($id)
	{
		$this->is_allowed('unique_qr_tokens_view');

		$this->data['unique_qr_tokens'] = $this->model_unique_qr_tokens->join_avaiable()->filter_avaiable()->find($id);

		$this->template->title('Unique Qr Tokens Detail');
		$this->render('backend/standart/administrator/unique_qr_tokens/unique_qr_tokens_view', $this->data);
	}
	
	/**
	* delete Unique Qr Tokenss
	*
	* @var $id String
	*/
	private function _remove($id)
	{
		$unique_qr_tokens = $this->model_unique_qr_tokens->find($id);

		
		
		return $this->model_unique_qr_tokens->remove($id);
	}
	
	
	/**
	* Export to excel
	*
	* @return Files Excel .xls
	*/
	public function export()
	{
		$this->is_allowed('unique_qr_tokens_export');

		$this->model_unique_qr_tokens->export('unique_qr_tokens', 'unique_qr_tokens');
	}

	/**
	* Export to PDF
	*
	* @return Files PDF .pdf
	*/
	public function export_pdf()
	{
		$this->is_allowed('unique_qr_tokens_export');

		$this->model_unique_qr_tokens->pdf('unique_qr_tokens', 'unique_qr_tokens');
	}


	public function single_pdf($id = null)
	{
		$this->is_allowed('unique_qr_tokens_export');

		$table = $title = 'unique_qr_tokens';
		$this->load->library('HtmlPdf');
      
        $config = array(
            'orientation' => 'p',
            'format' => 'a4',
            'marges' => array(5, 5, 5, 5)
        );

        $this->pdf = new HtmlPdf($config);
        $this->pdf->setDefaultFont('stsongstdlight'); 

        $result = $this->db->get($table);
       
        $data = $this->model_unique_qr_tokens->find($id);
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
}


/* End of file unique_qr_tokens.php */
/* Location: ./application/controllers/administrator/Unique Qr Tokens.php */