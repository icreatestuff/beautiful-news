<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Sites extends MY_Controller {

	/**
	 * Index Page for this controller.
	 *
	 */
	public function index()
	{	
		$this->load->model('site_model');
		$data['query'] = $this->site_model->get_all();
		$data['sites'] = $this->data['sites'];
		$data['current_site'] = $this->data['current_site'];
		
		$this->load->view('admin/sites/index', $data);
	}
	
	/**
	 * New Site.
	 *
	 */
	public function new_site()
	{
		$this->site_form();

		if ($this->form_validation->run() == FALSE)
		{	
			$data['current_site'] = $this->data['current_site'];
			$data['sites'] = $this->data['sites'];
			
			$this->load->view('admin/sites/new', $data);
		}
		else
		{
			foreach ($_POST as $key => $val)
			{
				$data[$key] = $this->input->post($key);
			}
			
			// unset submit
			unset($data['submit']);
			
			// Create accommodation row in db
			$this->site_model->insert_row($data);	
			
			$data = array();
			$data['query'] = $this->site_model->get_all();
			$data['sites'] = $this->data['sites'];
			$data['current_site'] = $this->data['current_site'];
			
			$this->load->view('admin/sites/index', $data);
		}
	}
	
	/**
	 * Edit Site.
	 *
	 */
	public function edit_site()
	{
		if ($this->uri->segment(4) === FALSE)
		{
			redirect('admin/sites/index');
		}
		else
		{
			$site_id = $this->uri->segment(4);
		
			$this->site_form();
		
			if ($this->form_validation->run() == FALSE)
			{		
				// Get Accommodation Information	
				$site_row = $this->site_model->get_single_row($site_id);
				
				$data['site'] = $site_row->row();
				$data['current_site'] = $this->data['current_site'];
				$data['sites'] = $this->data['sites'];
				
				$this->load->view('admin/sites/edit', $data);
			}
			else
			{
				foreach ($_POST as $key => $val)
				{
					$data[$key] = $this->input->post($key);
				}
				
				// unset submit
				unset($data['submit']);
				
				// Create accommodation row in db
				$this->site_model->update_row($site_id, $data);
	
				$data = array();
				$data['query'] = $this->site_model->get_all();	
				$data['sites'] = $this->data['sites'];
				$data['current_site'] = $this->data['current_site'];
				
				$this->load->view('admin/sites/index', $data);
			}
		}
	}
	
	/**
	 * Site Form
	 *
	 */
	function site_form()
	{
		$this->load->library('form_validation');
		$this->load->model('site_model');
		
		$this->form_validation->set_rules('name', 'Name', 'trim|required|min_length[3]');
		$this->form_validation->set_rules('address_line_1', 'Address Line 1', 'trim|required|min_length[2]');
		$this->form_validation->set_rules('address_line_2', 'Address Line 2', 'trim|min_length[2]');
		$this->form_validation->set_rules('city', 'Town/City', 'trim|required|min_length[2]');
		$this->form_validation->set_rules('county', 'County', 'trim|required|min_length[2]');
		$this->form_validation->set_rules('postcode', 'Postcode', 'trim|required|min_length[5]');
		$this->form_validation->set_rules('deposit_percentage', 'Deposit %', 'trim|required|numeric');
	}
	
	/**
	 * Site Delete
	 *
	 */
	function delete()
	{
		$data['id'] = $this->input->post('id');
		
		$this->load->model('site_model');
		$this->site_model->delete_row($data);
	}
	
	/**
	 * Change Site
	 *
	 */
	function change_site()
	{
		$site_id = $this->input->post('id');
		$this->session->unset_userdata('site_id');	
		$this->session->set_userdata('site_id', $site_id);
	}
}

/* End of file welcome.php */
/* Location: ./application/controllers/welcome.php */