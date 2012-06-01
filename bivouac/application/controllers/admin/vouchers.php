<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Vouchers extends MY_Controller {

	/**
	 * Index Page for this controller.
	 *
	 */
	public function index()
	{
		$this->vouchers_form();
		
		$data['query'] = $this->voucher_model->get_all();
		$data['sites'] = $this->data['sites'];
		$data['current_site'] = $this->data['current_site'];
		
		if ($this->form_validation->run() == FALSE)
		{	
			$data['sites'] = $this->data['sites'];
			$data['current_site'] = $this->data['current_site'];
			
			$this->load->view('admin/vouchers/index', $data);
		}
		else
		{
			foreach ($_POST as $key => $val)
			{
				$voucher_data[$key] = $this->input->post($key);
			}
			
			// Format Dates
			$voucher_data['start_date'] = date('Y-m-d H:i:s', strtotime($voucher_data['start_date']));
			$voucher_data['end_date'] = date('Y-m-d H:i:s', strtotime($voucher_data['end_date']));
			$voucher_data['valid_from'] = date('Y-m-d H:i:s', strtotime($voucher_data['valid_from']));
			$voucher_data['valid_to'] = date('Y-m-d H:i:s', strtotime($voucher_data['valid_to']));
			
			// Unset submit
			unset($voucher_data['submit']);

			// Create holiday row in db
			$this->voucher_model->insert_row($voucher_data);	
			
			redirect('admin/vouchers/index');
		}		
	}
	
	
	/**
	 * Update Voucher Code
	 *
	 */
	public function edit_voucher()
	{
		if ($this->uri->segment(4) === FALSE)
		{
			redirect('admin/vouchers/index');
		}
		else
		{
			$voucher_id = $this->uri->segment(4);
		
			$this->vouchers_form();
		
			if ($this->form_validation->run() == FALSE)
			{		
				// Get Voucher Data
				$voucher_row = $this->voucher_model->get_single_row($voucher_id);
				$data['voucher'] = $voucher_row->row();
				
				$data['sites'] = $this->data['sites'];
				$data['current_site'] = $this->data['current_site'];
				
				$this->load->view('admin/vouchers/edit_voucher', $data);
			}
			else
			{
				foreach ($_POST as $key => $val)
				{
					$data[$key] = $this->input->post($key);
				}
				
				// Format Dates
				$data['start_date'] = date('Y-m-d H:i:s', strtotime($data['start_date']));
				$data['end_date'] = date('Y-m-d H:i:s', strtotime($data['end_date']));
				$data['valid_from'] = date('Y-m-d H:i:s', strtotime($data['valid_from']));
				$data['valid_to'] = date('Y-m-d H:i:s', strtotime($data['valid_to']));
				
				// unset submit
				unset($data['submit']);
				
				// Update holiday row in db
				$this->voucher_model->update_row($voucher_id, $data);
				
				redirect('admin/vouchers/index');
			}
		}
	}	
		
	
	/**
	 * Holidays Form
	 *
	 */
	function vouchers_form()
	{
		$this->load->library('form_validation');
		$this->load->model('voucher_model');
		
		$this->form_validation->set_rules('name', 'Voucher Name', 'trim|required|min_length[2]');
		$this->form_validation->set_rules('start_date', 'Start Date', 'trim|required');
		$this->form_validation->set_rules('end_date', 'End Date', 'trim|required');
		$this->form_validation->set_rules('discount_price', 'Discount Price', 'trim|decimal');
		$this->form_validation->set_rules('discount_percentage', 'Discount Percentage', 'trim|number');
		$this->form_validation->set_rules('valid_from', 'Valid From Date', 'trim');
		$this->form_validation->set_rules('valid_to', 'Valid To Date', 'trim');
	}
	
	
	/**
	 * Holidays Delete
	 *
	 */
	function delete()
	{
		$data['id'] = $this->input->post('id');
		
		$this->load->model('voucher_model');
		$this->voucher_model->delete_row($data);
	}
}

/* End of file vouchers.php */
/* Location: ./application/controllers/vouchers.php */