<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Site_closed extends MY_Controller {

	/**
	 * Index Page for this controller.
	 *
	 */
	public function index()
	{
		$this->site_closed_form();
		
		$data['query'] = $this->site_closed_model->get_all();
		$data['sites'] = $this->data['sites'];
		$data['current_site'] = $this->data['current_site'];
		
		if ($this->form_validation->run() == FALSE)
		{	
			$data['sites'] = $this->data['sites'];
			$data['current_site'] = $this->data['current_site'];
			
			$this->load->view('admin/site_closed/index', $data);
		}
		else
		{
			foreach ($_POST as $key => $val)
			{
				$closed_data[$key] = $this->input->post($key);
			}
			
			// Format Dates
			$closed_data['start_date'] = date('Y-m-d H:i:s', strtotime($closed_data['start_date']));
			$closed_data['end_date'] = date('Y-m-d H:i:s', strtotime($closed_data['end_date']));
			
			// Unset submit
			unset($closed_data['submit']);

			// Create holiday row in db
			$this->site_closed_model->insert_row($closed_data);	
			
			redirect('admin/site_closed/index');
		}		
	}
	
	
	/**
	 * Update Public Holiday
	 *
	 */
	public function edit_closed_dates()
	{
		if ($this->uri->segment(4) === FALSE)
		{
			redirect('admin/site_closed/index');
		}
		else
		{
			$closed_id = $this->uri->segment(4);
		
			$this->site_closed_form();
		
			if ($this->form_validation->run() == FALSE)
			{		
				// Get Price/Date Range Information	
				$closed_row = $this->site_closed_model->get_single_row($closed_id);
				$data['closed'] = $closed_row->row();
				
				$data['sites'] = $this->data['sites'];
				$data['current_site'] = $this->data['current_site'];
				
				$this->load->view('admin/site_closed/edit_closed_dates', $data);
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
				$this->site_closed_model->update_row($closed_id, $data);
				
				redirect('admin/site_closed/index');
			}
		}
	}	
		
	
	/**
	 * Holidays Form
	 *
	 */
	function site_closed_form()
	{
		$this->load->library('form_validation');
		$this->load->model('site_closed_model');
		
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
		
		$this->load->model('site_closed_model');
		$this->site_closed_model->delete_row($data);
	}
}

/* End of file holidays.php */
/* Location: ./application/controllers/holiodays.php */