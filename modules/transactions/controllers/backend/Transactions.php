<?php
defined('BASEPATH') OR exit('No direct script access allowed');


/**
*| --------------------------------------------------------------------------
*| Transactions Controller
*| --------------------------------------------------------------------------
*| Transactions site
*|
*/
class Transactions extends Admin	
{
	
	public function __construct()
	{
		parent::__construct();

		$this->load->model('model_transactions');
		$this->lang->load('web_lang', $this->current_lang);
	}

	/**
	* show all Transactionss
	*
	* @var $offset String
	*/
	public function index($offset = 0)
	{
		$this->is_allowed('transactions_list');

		$filter = $this->input->get('q');
		$field 	= $this->input->get('f');

		$this->data['transactionss'] = $this->model_transactions->get($filter, $field, $this->limit_page, $offset);
		$this->data['transactions_counts'] = $this->model_transactions->count_all($filter, $field);

		$config = [
			'base_url'     => 'administrator/transactions/index/',
			'total_rows'   => $this->model_transactions->count_all($filter, $field),
			'per_page'     => $this->limit_page,
			'uri_segment'  => 4,
		];

		$this->data['pagination'] = $this->pagination($config);

		$this->template->title('Transactions List');
		$this->render('backend/standart/administrator/transactions/transactions_list', $this->data);
	}
	
	/**
	* Add new transactionss
	*
	*/
	public function add()
	{
		$this->is_allowed('transactions_add');

		$this->template->title('Transactions New');
		$this->render('backend/standart/administrator/transactions/transactions_add', $this->data);
	}

	/**
	* Add New Transactionss
	*
	* @return JSON
	*/
	public function add_save()
	{
		if (!$this->is_allowed('transactions_add', false)) {
			echo json_encode([
				'success' => false,
				'message' => cclang('sorry_you_do_not_have_permission_to_access')
				]);
			exit;
		}

		$this->form_validation->set_rules('no_pesanan', 'No Pesanan', 'trim|required|max_length[100]');
		$this->form_validation->set_rules('tanggal_transaksi', 'Tanggal Transaksi', 'trim|required');
		$this->form_validation->set_rules('nama_cabang', 'Nama Cabang', 'trim|required|max_length[100]');
		$this->form_validation->set_rules('detail_barang', 'Detail Barang', 'trim|required');
		$this->form_validation->set_rules('qty', 'Qty', 'trim|required|max_length[11]');
		$this->form_validation->set_rules('harga_barang', 'Harga Barang', 'trim|required|max_length[15]');
		$this->form_validation->set_rules('sub_total', 'Sub Total', 'trim|required|max_length[15]');
		$this->form_validation->set_rules('ongkir', 'Ongkir', 'trim|required|max_length[15]');
		$this->form_validation->set_rules('total_bayar', 'Total Bayar', 'trim|required|max_length[15]');
		$this->form_validation->set_rules('purchase_specialist', 'Purchase Specialist', 'trim|required|max_length[100]');
		$this->form_validation->set_rules('no_purchase_specialist', 'No Purchase Specialist', 'trim|required|max_length[30]');
		$this->form_validation->set_rules('status_transaksi', 'Status Transaksi', 'trim|required|max_length[50]');
		$this->form_validation->set_rules('is_used_spin', 'Is Used Spin', 'trim|required|max_length[1]');
		

		if ($this->form_validation->run()) {
		
			$save_data = [
				'no_pesanan' => $this->input->post('no_pesanan'),
				'tanggal_transaksi' => $this->input->post('tanggal_transaksi'),
				'nama_cabang' => $this->input->post('nama_cabang'),
				'detail_barang' => $this->input->post('detail_barang'),
				'qty' => $this->input->post('qty'),
				'harga_barang' => $this->input->post('harga_barang'),
				'sub_total' => $this->input->post('sub_total'),
				'ongkir' => $this->input->post('ongkir'),
				'total_bayar' => $this->input->post('total_bayar'),
				'purchase_specialist' => $this->input->post('purchase_specialist'),
				'no_purchase_specialist' => $this->input->post('no_purchase_specialist'),
				'status_transaksi' => $this->input->post('status_transaksi'),
				'is_used_spin' => $this->input->post('is_used_spin'),
			];

			
			$save_transactions = $this->model_transactions->store($save_data);
            

			if ($save_transactions) {
				if ($this->input->post('save_type') == 'stay') {
					$this->data['success'] = true;
					$this->data['id'] 	   = $save_transactions;
					$this->data['message'] = cclang('success_save_data_stay', [
						anchor('administrator/transactions/edit/' . $save_transactions, 'Edit Transactions'),
						anchor('administrator/transactions', ' Go back to list')
					]);
				} else {
					set_message(
						cclang('success_save_data_redirect', [
						anchor('administrator/transactions/edit/' . $save_transactions, 'Edit Transactions')
					]), 'success');

            		$this->data['success'] = true;
					$this->data['redirect'] = base_url('administrator/transactions');
				}
			} else {
				if ($this->input->post('save_type') == 'stay') {
					$this->data['success'] = false;
					$this->data['message'] = cclang('data_not_change');
				} else {
            		$this->data['success'] = false;
            		$this->data['message'] = cclang('data_not_change');
					$this->data['redirect'] = base_url('administrator/transactions');
				}
			}

		} else {
			$this->data['success'] = false;
			$this->data['message'] = validation_errors();
		}

		echo json_encode($this->data);
	}
	
		/**
	* Update view Transactionss
	*
	* @var $id String
	*/
	public function edit($id)
	{
		$this->is_allowed('transactions_update');

		$this->data['transactions'] = $this->model_transactions->find($id);

		$this->template->title('Transactions Update');
		$this->render('backend/standart/administrator/transactions/transactions_update', $this->data);
	}

	/**
	* Update Transactionss
	*
	* @var $id String
	*/
	public function edit_save($id)
	{
		if (!$this->is_allowed('transactions_update', false)) {
			echo json_encode([
				'success' => false,
				'message' => cclang('sorry_you_do_not_have_permission_to_access')
				]);
			exit;
		}
		
		$this->form_validation->set_rules('no_pesanan', 'No Pesanan', 'trim|required|max_length[100]');
		$this->form_validation->set_rules('tanggal_transaksi', 'Tanggal Transaksi', 'trim|required');
		$this->form_validation->set_rules('nama_cabang', 'Nama Cabang', 'trim|required|max_length[100]');
		$this->form_validation->set_rules('detail_barang', 'Detail Barang', 'trim|required');
		$this->form_validation->set_rules('qty', 'Qty', 'trim|required|max_length[11]');
		$this->form_validation->set_rules('harga_barang', 'Harga Barang', 'trim|required|max_length[15]');
		$this->form_validation->set_rules('sub_total', 'Sub Total', 'trim|required|max_length[15]');
		$this->form_validation->set_rules('ongkir', 'Ongkir', 'trim|required|max_length[15]');
		$this->form_validation->set_rules('total_bayar', 'Total Bayar', 'trim|required|max_length[15]');
		$this->form_validation->set_rules('purchase_specialist', 'Purchase Specialist', 'trim|required|max_length[100]');
		$this->form_validation->set_rules('no_purchase_specialist', 'No Purchase Specialist', 'trim|required|max_length[30]');
		$this->form_validation->set_rules('status_transaksi', 'Status Transaksi', 'trim|required|max_length[50]');
		$this->form_validation->set_rules('is_used_spin', 'Is Used Spin', 'trim|required|max_length[1]');
		
		if ($this->form_validation->run()) {
		
			$save_data = [
				'no_pesanan' => $this->input->post('no_pesanan'),
				'tanggal_transaksi' => $this->input->post('tanggal_transaksi'),
				'nama_cabang' => $this->input->post('nama_cabang'),
				'detail_barang' => $this->input->post('detail_barang'),
				'qty' => $this->input->post('qty'),
				'harga_barang' => $this->input->post('harga_barang'),
				'sub_total' => $this->input->post('sub_total'),
				'ongkir' => $this->input->post('ongkir'),
				'total_bayar' => $this->input->post('total_bayar'),
				'purchase_specialist' => $this->input->post('purchase_specialist'),
				'no_purchase_specialist' => $this->input->post('no_purchase_specialist'),
				'status_transaksi' => $this->input->post('status_transaksi'),
				'is_used_spin' => $this->input->post('is_used_spin'),
			];

			
			$save_transactions = $this->model_transactions->change($id, $save_data);

			if ($save_transactions) {
				if ($this->input->post('save_type') == 'stay') {
					$this->data['success'] = true;
					$this->data['id'] 	   = $id;
					$this->data['message'] = cclang('success_update_data_stay', [
						anchor('administrator/transactions', ' Go back to list')
					]);
				} else {
					set_message(
						cclang('success_update_data_redirect', [
					]), 'success');

            		$this->data['success'] = true;
					$this->data['redirect'] = base_url('administrator/transactions');
				}
			} else {
				if ($this->input->post('save_type') == 'stay') {
					$this->data['success'] = false;
					$this->data['message'] = cclang('data_not_change');
				} else {
            		$this->data['success'] = false;
            		$this->data['message'] = cclang('data_not_change');
					$this->data['redirect'] = base_url('administrator/transactions');
				}
			}
		} else {
			$this->data['success'] = false;
			$this->data['message'] = validation_errors();
		}

		echo json_encode($this->data);
	}
	
	/**
	* delete Transactionss
	*
	* @var $id String
	*/
	public function delete($id = null)
	{
		$this->is_allowed('transactions_delete');

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
            set_message(cclang('has_been_deleted', 'transactions'), 'success');
        } else {
            set_message(cclang('error_delete', 'transactions'), 'error');
        }

		redirect_back();
	}

		/**
	* View view Transactionss
	*
	* @var $id String
	*/
	public function view($id)
	{
		$this->is_allowed('transactions_view');

		$this->data['transactions'] = $this->model_transactions->join_avaiable()->filter_avaiable()->find($id);

		$this->template->title('Transactions Detail');
		$this->render('backend/standart/administrator/transactions/transactions_view', $this->data);
	}
	
	/**
	* delete Transactionss
	*
	* @var $id String
	*/
	private function _remove($id)
	{
		$transactions = $this->model_transactions->find($id);

		
		
		return $this->model_transactions->remove($id);
	}
	
	
	/**
	* Export to excel
	*
	* @return Files Excel .xls
	*/
	public function export()
	{
		$this->is_allowed('transactions_export');

		$this->model_transactions->export('transactions', 'transactions');
	}

	/**
	* Export to PDF
	*
	* @return Files PDF .pdf
	*/
	public function export_pdf()
	{
		$this->is_allowed('transactions_export');

		$this->model_transactions->pdf('transactions', 'transactions');
	}


	public function single_pdf($id = null)
	{
		$this->is_allowed('transactions_export');

		$table = $title = 'transactions';
		$this->load->library('HtmlPdf');
      
        $config = array(
            'orientation' => 'p',
            'format' => 'a4',
            'marges' => array(5, 5, 5, 5)
        );

        $this->pdf = new HtmlPdf($config);
        $this->pdf->setDefaultFont('stsongstdlight'); 

        $result = $this->db->get($table);
       
        $data = $this->model_transactions->find($id);
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


/* End of file transactions.php */
/* Location: ./application/controllers/administrator/Transactions.php */