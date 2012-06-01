<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Pricing extends MY_Controller {

	/**
	 * Index Page for this controller.
	 *
	 */
	public function index()
	{
		$this->pricing_form();
		
		$site_id = $this->session->userdata('site_id');
		
		$data['query'] = $this->pricing_model->get_all_for_site($site_id);
		$data['sites'] = $this->data['sites'];
		$data['current_site'] = $this->data['current_site'];
		
		if ($this->form_validation->run() == FALSE)
		{	
			$data['sites'] = $this->data['sites'];
			$data['current_site'] = $this->data['current_site'];
			
			$this->load->view('admin/pricing/index', $data);
		}
		else
		{
			foreach ($_POST as $key => $val)
			{
				$price_data[$key] = $this->input->post($key);
			}
			
			// Format Dates
			$price_data['start_date'] = date('Y-m-d H:i:s', strtotime($price_data['start_date']));
			$price_data['end_date'] = date('Y-m-d H:i:s', strtotime($price_data['end_date']));
			
			// Unset submit
			unset($price_data['submit']);

			// Create accommodation row in db
			$this->pricing_model->insert_row($price_data);	
			
			redirect('admin/pricing/index');
		}		
	}
	
	
	/**
	 * Update Date/Price Range
	 *
	 */
	public function edit_schema()
	{
		if ($this->uri->segment(4) === FALSE)
		{
			redirect('admin/pricing/index');
		}
		else
		{
			$pricing_id = $this->uri->segment(4);
		
			$this->pricing_form();
		
			if ($this->form_validation->run() == FALSE)
			{		
				// Get Price/Date Range Information	
				$pricing_row = $this->pricing_model->get_single_row($pricing_id);
				$data['pricing'] = $pricing_row->row();
				
				$data['sites'] = $this->data['sites'];
				$data['current_site'] = $this->data['current_site'];
				
				$this->load->view('admin/pricing/edit_schema', $data);
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
				
				// Create accommodation row in db
				$this->pricing_model->update_row($pricing_id, $data);
				
				redirect('admin/pricing/index');
			}
		}
	}
		
		
	/**
	 * Check Start Date for duplicate already existing
	 *
	 */	
	function start_date_check()
	{
		$start_date = date('Y-m-d H:i:s', strtotime($this->input->post('start_date')));
		
		//echo $start_date;
		
		$this->load->model('pricing_model');
		$query = $this->pricing_model->check_start_date($start_date);
		
		if ($query->num_rows() > 0)
		{
			echo 'true';
		}
		else
		{
			echo 'false';
		}
	}	
		
	
	/**
	 * Accomodation Form
	 *
	 */
	function pricing_form()
	{
		$this->load->library('form_validation');
		$this->load->model('pricing_model');
		
		$this->form_validation->set_rules('start_date', 'Start Date', 'trim|required');
		$this->form_validation->set_rules('end_date', 'End Date', 'trim|required');
		$this->form_validation->set_rules('woodland_shack', 'Woodland Shack Percentage', 'trim|numeric|required');
		$this->form_validation->set_rules('meadow_yurt', 'Meadow Yurt Percentage', 'trim|numeric|required');
		$this->form_validation->set_rules('bunk_barn', 'Bunk Barn Percentage', 'trim|numeric|required');
		$this->form_validation->set_rules('family_lodge', 'Family Lodge Percentage', 'trim|numeric');
		$this->form_validation->set_rules('camping_pitch', 'Camping Pitch Percentage', 'trim|numeric');
	}
	
	
	/**
	 * Accomodation Delete
	 *
	 */
	function delete()
	{
		$data['id'] = $this->input->post('id');
		
		$this->load->model('pricing_model');
		$this->pricing_model->delete_row($data);
	}
}

/* End of file welcome.php */
/* Location: ./application/controllers/welcome.php */