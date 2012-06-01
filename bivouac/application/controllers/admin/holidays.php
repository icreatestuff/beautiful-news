<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Holidays extends MY_Controller {

	/**
	 * Index Page for this controller.
	 *
	 */
	public function index()
	{
		$this->holidays_form();
		
		$data['query'] = $this->holiday_model->get_all();
		$data['sites'] = $this->data['sites'];
		$data['current_site'] = $this->data['current_site'];
		
		if ($this->form_validation->run() == FALSE)
		{	
			$data['sites'] = $this->data['sites'];
			$data['current_site'] = $this->data['current_site'];
			
			$this->load->view('admin/holidays/index', $data);
		}
		else
		{
			foreach ($_POST as $key => $val)
			{
				$holiday_data[$key] = $this->input->post($key);
			}
			
			// Format Dates
			$holiday_data['start_date'] = date('Y-m-d H:i:s', strtotime($holiday_data['start_date']));
			$holiday_data['end_date'] = date('Y-m-d H:i:s', strtotime($holiday_data['end_date']));
			
			// Unset submit
			unset($holiday_data['submit']);

			// Create holiday row in db
			$this->holiday_model->insert_row($holiday_data);	
			
			redirect('admin/holidays/index');
		}		
	}
	
	
	/**
	 * Update Public Holiday
	 *
	 */
	public function edit_holiday()
	{
		if ($this->uri->segment(4) === FALSE)
		{
			redirect('admin/holidays/index');
		}
		else
		{
			$holiday_id = $this->uri->segment(4);
		
			$this->holidays_form();
		
			if ($this->form_validation->run() == FALSE)
			{		
				// Get Price/Date Range Information	
				$holiday_row = $this->holiday_model->get_single_row($holiday_id);
				$data['holiday'] = $holiday_row->row();
				
				$data['sites'] = $this->data['sites'];
				$data['current_site'] = $this->data['current_site'];
				
				$this->load->view('admin/holidays/edit_holiday', $data);
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
				
				// unset submit
				unset($data['submit']);
				
				// Update holiday row in db
				$this->holiday_model->update_row($holiday_id, $data);
				
				redirect('admin/holidays/index');
			}
		}
	}	
		
	
	/**
	 * Holidays Form
	 *
	 */
	function holidays_form()
	{
		$this->load->library('form_validation');
		$this->load->model('holiday_model');
		
		$this->form_validation->set_rules('name', 'Holiday Name', 'trim|required|min_length[2]');
		$this->form_validation->set_rules('start_date', 'Start Date', 'trim|required');
		$this->form_validation->set_rules('end_date', 'End Date', 'trim|required');
	}
	
	
	/**
	 * Holidays Delete
	 *
	 */
	function delete()
	{
		$data['id'] = $this->input->post('id');
		
		$this->load->model('holiday_model');
		$this->holiday_model->delete_row($data);
	}
}

/* End of file holidays.php */
/* Location: ./application/controllers/holiodays.php */