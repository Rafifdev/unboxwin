<?php
defined('BASEPATH') OR exit('No direct script access allowed');


/**
*| --------------------------------------------------------------------------
*| Prizes Controller
*| --------------------------------------------------------------------------
*| Prizes site
*|
*/
class Prizes extends Admin	
{
	
	public function __construct()
	{
		parent::__construct();

		$this->load->model('model_prizes');
		$this->lang->load('web_lang', $this->current_lang);
	}

	/**
	* show all Prizess
	*
	* @var $offset String
	*/
	public function index($offset = 0)
	{
		$this->is_allowed('prizes_list');

		$filter = $this->input->get('q');
		$field 	= $this->input->get('f');

		$this->data['prizess'] = $this->model_prizes->get($filter, $field, $this->limit_page, $offset);
		$this->data['prizes_counts'] = $this->model_prizes->count_all($filter, $field);

		$config = [
			'base_url'     => 'administrator/prizes/index/',
			'total_rows'   => $this->model_prizes->count_all($filter, $field),
			'per_page'     => $this->limit_page,
			'uri_segment'  => 4,
		];

		$this->data['pagination'] = $this->pagination($config);

		$this->template->title('Prizes List');
		$this->render('backend/standart/administrator/prizes/prizes_list', $this->data);
	}
	
	/**
	* Add new prizess
	*
	*/
	public function add()
	{
		$this->is_allowed('prizes_add');

		$this->template->title('Prizes New');
		$this->render('backend/standart/administrator/prizes/prizes_add', $this->data);
	}

	/**
	* Add New Prizess
	*
	* @return JSON
	*/
	public function add_save()
	{
		if (!$this->is_allowed('prizes_add', false)) {
			echo json_encode([
				'success' => false,
				'message' => cclang('sorry_you_do_not_have_permission_to_access')
				]);
			exit;
		}

		$this->form_validation->set_rules('nama_hadiah', 'Nama Hadiah', 'trim|required|max_length[100]');
		$this->form_validation->set_rules('kategori_hadiah', 'Kategori Hadiah', 'trim|required');
		$this->form_validation->set_rules('nilai_hadiah', 'Nilai Hadiah', 'trim|required|max_length[15]');
		$this->form_validation->set_rules('stok_tersedia', 'Stok Tersedia', 'trim|required|max_length[11]');
		$this->form_validation->set_rules('max_frekuensi_bulanan', 'Max Frekuensi Bulanan', 'trim|required|max_length[11]');
		$this->form_validation->set_rules('max_frekuensi_tahunan', 'Max Frekuensi Tahunan', 'trim|required|max_length[11]');
		$this->form_validation->set_rules('min_omzet_tercapai', 'Min Omzet Tercapai', 'trim|required|max_length[15]');
		$this->form_validation->set_rules('bobot_probabilitas', 'Bobot Probabilitas', 'trim|required|max_length[11]');
		$this->form_validation->set_rules('warna_slice', 'Warna Slice', 'trim|required|max_length[20]');
		$this->form_validation->set_rules('is_active', 'Is Active', 'trim|required|max_length[1]');
		

		if ($this->form_validation->run()) {
		
			$save_data = [
				'nama_hadiah' => $this->input->post('nama_hadiah'),
				'kategori_hadiah' => $this->input->post('kategori_hadiah'),
				'nilai_hadiah' => $this->input->post('nilai_hadiah'),
				'stok_tersedia' => $this->input->post('stok_tersedia'),
				'max_frekuensi_bulanan' => $this->input->post('max_frekuensi_bulanan'),
				'max_frekuensi_tahunan' => $this->input->post('max_frekuensi_tahunan'),
				'min_omzet_tercapai' => $this->input->post('min_omzet_tercapai'),
				'bobot_probabilitas' => $this->input->post('bobot_probabilitas'),
				'warna_slice' => $this->input->post('warna_slice'),
				'is_active' => $this->input->post('is_active'),
			];

			
			$save_prizes = $this->model_prizes->store($save_data);
            

			if ($save_prizes) {
				if ($this->input->post('save_type') == 'stay') {
					$this->data['success'] = true;
					$this->data['id'] 	   = $save_prizes;
					$this->data['message'] = cclang('success_save_data_stay', [
						anchor('administrator/prizes/edit/' . $save_prizes, 'Edit Prizes'),
						anchor('administrator/prizes', ' Go back to list')
					]);
				} else {
					set_message(
						cclang('success_save_data_redirect', [
						anchor('administrator/prizes/edit/' . $save_prizes, 'Edit Prizes')
					]), 'success');

            		$this->data['success'] = true;
					$this->data['redirect'] = base_url('administrator/prizes');
				}
			} else {
				if ($this->input->post('save_type') == 'stay') {
					$this->data['success'] = false;
					$this->data['message'] = cclang('data_not_change');
				} else {
            		$this->data['success'] = false;
            		$this->data['message'] = cclang('data_not_change');
					$this->data['redirect'] = base_url('administrator/prizes');
				}
			}

		} else {
			$this->data['success'] = false;
			$this->data['message'] = validation_errors();
		}

		echo json_encode($this->data);
	}
	
		/**
	* Update view Prizess
	*
	* @var $id String
	*/
	public function edit($id)
	{
		$this->is_allowed('prizes_update');

		$this->data['prizes'] = $this->model_prizes->find($id);

		$this->template->title('Prizes Update');
		$this->render('backend/standart/administrator/prizes/prizes_update', $this->data);
	}

	/**
	* Update Prizess
	*
	* @var $id String
	*/
	public function edit_save($id)
	{
		if (!$this->is_allowed('prizes_update', false)) {
			echo json_encode([
				'success' => false,
				'message' => cclang('sorry_you_do_not_have_permission_to_access')
				]);
			exit;
		}
		
		$this->form_validation->set_rules('nama_hadiah', 'Nama Hadiah', 'trim|required|max_length[100]');
		$this->form_validation->set_rules('kategori_hadiah', 'Kategori Hadiah', 'trim|required');
		$this->form_validation->set_rules('nilai_hadiah', 'Nilai Hadiah', 'trim|required|max_length[15]');
		$this->form_validation->set_rules('stok_tersedia', 'Stok Tersedia', 'trim|required|max_length[11]');
		$this->form_validation->set_rules('max_frekuensi_bulanan', 'Max Frekuensi Bulanan', 'trim|required|max_length[11]');
		$this->form_validation->set_rules('max_frekuensi_tahunan', 'Max Frekuensi Tahunan', 'trim|required|max_length[11]');
		$this->form_validation->set_rules('min_omzet_tercapai', 'Min Omzet Tercapai', 'trim|required|max_length[15]');
		$this->form_validation->set_rules('bobot_probabilitas', 'Bobot Probabilitas', 'trim|required|max_length[11]');
		$this->form_validation->set_rules('warna_slice', 'Warna Slice', 'trim|required|max_length[20]');
		$this->form_validation->set_rules('is_active', 'Is Active', 'trim|required|max_length[1]');
		
		if ($this->form_validation->run()) {
		
			$save_data = [
				'nama_hadiah' => $this->input->post('nama_hadiah'),
				'kategori_hadiah' => $this->input->post('kategori_hadiah'),
				'nilai_hadiah' => $this->input->post('nilai_hadiah'),
				'stok_tersedia' => $this->input->post('stok_tersedia'),
				'max_frekuensi_bulanan' => $this->input->post('max_frekuensi_bulanan'),
				'max_frekuensi_tahunan' => $this->input->post('max_frekuensi_tahunan'),
				'min_omzet_tercapai' => $this->input->post('min_omzet_tercapai'),
				'bobot_probabilitas' => $this->input->post('bobot_probabilitas'),
				'warna_slice' => $this->input->post('warna_slice'),
				'is_active' => $this->input->post('is_active'),
			];

			
			$save_prizes = $this->model_prizes->change($id, $save_data);

			if ($save_prizes) {
				if ($this->input->post('save_type') == 'stay') {
					$this->data['success'] = true;
					$this->data['id'] 	   = $id;
					$this->data['message'] = cclang('success_update_data_stay', [
						anchor('administrator/prizes', ' Go back to list')
					]);
				} else {
					set_message(
						cclang('success_update_data_redirect', [
					]), 'success');

            		$this->data['success'] = true;
					$this->data['redirect'] = base_url('administrator/prizes');
				}
			} else {
				if ($this->input->post('save_type') == 'stay') {
					$this->data['success'] = false;
					$this->data['message'] = cclang('data_not_change');
				} else {
            		$this->data['success'] = false;
            		$this->data['message'] = cclang('data_not_change');
					$this->data['redirect'] = base_url('administrator/prizes');
				}
			}
		} else {
			$this->data['success'] = false;
			$this->data['message'] = validation_errors();
		}

		echo json_encode($this->data);
	}
	
	/**
	* delete Prizess
	*
	* @var $id String
	*/
	public function delete($id = null)
	{
		$this->is_allowed('prizes_delete');

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
            set_message(cclang('has_been_deleted', 'prizes'), 'success');
        } else {
            set_message(cclang('error_delete', 'prizes'), 'error');
        }

		redirect_back();
	}

		/**
	* View view Prizess
	*
	* @var $id String
	*/
	public function view($id)
	{
		$this->is_allowed('prizes_view');

		$this->data['prizes'] = $this->model_prizes->join_avaiable()->filter_avaiable()->find($id);

		$this->template->title('Prizes Detail');
		$this->render('backend/standart/administrator/prizes/prizes_view', $this->data);
	}
	
	/**
	* delete Prizess
	*
	* @var $id String
	*/
	private function _remove($id)
	{
		$prizes = $this->model_prizes->find($id);

		
		
		return $this->model_prizes->remove($id);
	}
	
	
	/**
	* Export to excel
	*
	* @return Files Excel .xls
	*/
	public function export()
	{
		$this->is_allowed('prizes_export');

		$this->model_prizes->export('prizes', 'prizes');
	}

	/**
	* Export to PDF
	*
	* @return Files PDF .pdf
	*/
	public function export_pdf()
	{
		$this->is_allowed('prizes_export');

		$this->model_prizes->pdf('prizes', 'prizes');
	}


	public function single_pdf($id = null)
	{
		$this->is_allowed('prizes_export');

		$table = $title = 'prizes';
		$this->load->library('HtmlPdf');
      
        $config = array(
            'orientation' => 'p',
            'format' => 'a4',
            'marges' => array(5, 5, 5, 5)
        );

        $this->pdf = new HtmlPdf($config);
        $this->pdf->setDefaultFont('stsongstdlight'); 

        $result = $this->db->get($table);
       
        $data = $this->model_prizes->find($id);
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


/* End of file prizes.php */
/* Location: ./application/controllers/administrator/Prizes.php */