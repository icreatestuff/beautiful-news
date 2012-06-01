<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Accommodation extends MY_Controller {

	/**
	 * Index Page for this controller.
	 *
	 */
	public function index()
	{
		$this->load->model('accommodation_model');
		$site_id = $this->session->userdata('site_id');
		
		$data['query'] = $this->accommodation_model->get_all_for_site($site_id);
		$data['sites'] = $this->data['sites'];
		$data['current_site'] = $this->data['current_site'];
		
		$this->load->view('admin/accommodation/index', $data);
	}
	
	
	/**
	 * New Accommodation.
	 *
	 */
	public function new_accommodation()
	{
		$this->accommodation_form();

		if ($this->form_validation->run() == FALSE)
		{	
			$data['types'] = $this->accommodation_model->get_types();
			$data['sites'] = $this->data['sites'];
			$data['current_site'] = $this->data['current_site'];
			
			$this->load->view('admin/accommodation/new', $data);
		}
		else
		{
			foreach ($_POST as $key => $val)
			{
				$data[$key] = $this->input->post($key);
			}
			
			// unset submit
			unset($data['submit']);
			
			$data = $this->do_upload($_FILES, $data);
			
			// Did all images get uploaded correctly?
			if (!isset($data['upload_error']))
			{
				// Create accommodation row in db
				$this->accommodation_model->insert_row($data);	
				
				$data = array();
				$data['sites'] = $this->data['sites'];
				$data['current_site'] = $this->data['current_site'];
				
				$site_id = $this->session->userdata('site_id');
				$data['query'] = $this->accommodation_model->get_all_for_site($site_id);
				
				$this->load->view('admin/accommodation/index', $data);
			}
			else
			{
				$data['types'] = $this->accommodation_model->get_types();
				$data['sites'] = $this->data['sites'];
				$data['current_site'] = $this->data['current_site'];
				
				$this->load->view('admin/accommodation/new', $data);
			}
		}
	}
	
	
	/**
	 * Update Accommodation Unit
	 *
	 */
	public function edit_accommodation()
	{
		if ($this->uri->segment(4) === FALSE)
		{
			redirect('admin/accommodation/index');
		}
		else
		{
			$accommodation_id = $this->uri->segment(4);
		
			$this->accommodation_form();
		
			if ($this->form_validation->run() == FALSE)
			{		
				// Get Accommodation Information	
				$accommodation_row = $this->accommodation_model->get_single_row($accommodation_id);
				$data['accommodation'] = $accommodation_row->row();
				
				$data['types'] = $this->accommodation_model->get_types();
				$data['sites'] = $this->data['sites'];
				$data['current_site'] = $this->data['current_site'];
				
				$this->load->view('admin/accommodation/edit', $data);
			}
			else
			{	
				// Set up image fields to be empty by default
				$data = array(
					'photo_1'	=> NULL,
					'photo_2'	=> NULL,
					'photo_3'	=> NULL,
					'photo_4'	=> NULL,
					'photo_5'	=> NULL,
					'photo_6'	=> NULL
				);
				
				foreach ($_POST as $key => $val)
				{
					$data[$key] = $this->input->post($key);
				}
				
				// unset submit
				unset($data['submit']);
				
				$data = $this->do_upload($_FILES, $data);
				
				// Did all images get uploaded correctly?
				if (!isset($data['upload_error']))
				{
					// Update accommodation row in db
					$this->accommodation_model->update_row($accommodation_id, $data);
					
					$data = array();
					$data['sites'] = $this->data['sites'];
					$data['current_site'] = $this->data['current_site'];
					
					$site_id = $this->session->userdata('site_id');
					$data['query'] = $this->accommodation_model->get_all_for_site($site_id);
					
					$this->load->view('admin/accommodation/index', $data);
				}
				else
				{
					// Get Accommodation Information	
					$accommodation_row = $this->accommodation_model->get_single_row($accommodation_id);
					$data['accommodation'] = $accommodation_row->row();
				
					$data['types'] = $this->accommodation_model->get_types();
					$data['sites'] = $this->data['sites'];
					$data['current_site'] = $this->data['current_site'];
					
					$this->load->view('admin/accommodation/edit', $data);
				}
			}
		}
	}
	
	
	/**
	 * Manage Accommodation Types
	 *
	 */
	public function types()
	{
		$this->load->model('accommodation_model');
		$site_id = $this->session->userdata('site_id');
		
		$data['query'] = $this->accommodation_model->get_types_for_site($site_id);
		$data['sites'] = $this->data['sites'];
		$data['current_site'] = $this->data['current_site'];
		
		$this->load->view('admin/accommodation/types', $data);
	}
	
	
	/**
	 * New Accommodation Type.
	 *
	 */
	public function new_accommodation_type()
	{
		$this->accommodation_type_form();

		if ($this->form_validation->run() == FALSE)
		{	
			$data['sites'] = $this->data['sites'];
			$data['current_site'] = $this->data['current_site'];
			$this->load->view('admin/accommodation/new_type', $data);
		}
		else
		{
			foreach ($_POST as $key => $val)
			{
				$data[$key] = $this->input->post($key);
			}
			
			// unset submit
			unset($data['submit']);
			
			// Create accommodation type row in db
			$this->accommodation_model->insert_type_row($data);	
			
			$data = array();
			$data['sites'] = $this->data['sites'];
			$data['current_site'] = $this->data['current_site'];
			
			$site_id = $this->session->userdata('site_id');
			$data['query'] = $this->accommodation_model->get_types_for_site($site_id);
			$this->load->view('admin/accommodation/types', $data);
		}
	}

	
	/**
	 * Update Accommodation Type
	 *
	 */
	public function edit_accommodation_type()
	{
		if ($this->uri->segment(4) === FALSE)
		{
			redirect('admin/accommodation/types');
		}
		else
		{
			$type_id = $this->uri->segment(4);
		
			$this->accommodation_type_form();
		
			if ($this->form_validation->run() == FALSE)
			{		
				$type_row = $this->accommodation_model->get_type_row($type_id);
				$data['type'] = $type_row->row();
				$data['sites'] = $this->data['sites'];
				$data['current_site'] = $this->data['current_site'];
				
				$this->load->view('admin/accommodation/edit_type', $data);
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
				$this->accommodation_model->update_type_row($type_id, $data);
				
				$data = array();
				$data['sites'] = $this->data['sites'];
				$data['current_site'] = $this->data['current_site'];
				
				$site_id = $this->session->userdata('site_id');
				$data['query'] = $this->accommodation_model->get_types_for_site($site_id);
				
				$this->load->view('admin/accommodation/types', $data);
			}
		}
	}

	
	/**
	 * Multiple File Uploading
	 *
	 */
	private function do_upload($_FILES, $data)
	{
		$this->load->library('upload');
			
		$upload_dir = "";
		$config['upload_path'] = './images/accommodation/';
		$config['allowed_types'] = 'gif|jpg|png|jpeg';
		$config['max_size']	= '1048576';
		$config['max_width']  = '3000';
		$config['max_height']  = '3000';
		$config['overwrite'] = True;
		
		foreach ($_FILES as $key => $val)
		{
			if (!empty($val['name'])) 
			{
				// Get extension and create new filename
				$extension = strtolower(end(explode(".", $val['name'])));
				$config['file_name'] = $data['name'] . "_" . $key . "." . $extension;
				
				$this->upload->initialize($config);
				
				if ($this->upload->do_upload($key))
				{
					$file_data = $this->upload->data();
					$data[$key] = $file_data['file_name'];
				} else {
					$data['upload_error'] = $this->upload->display_errors();
				}
			}
		}
		
		return $data;
	}
		
	
	/**
	 * Accomodation Form
	 *
	 */
	function accommodation_form()
	{
		$this->load->library('form_validation');
		$this->load->model('accommodation_model');
		
		$this->form_validation->set_rules('name', 'Name', 'trim|required|min_length[3]');
		$this->form_validation->set_rules('site_id', 'Site', 'trim|numeric|required');
		$this->form_validation->set_rules('type', 'Accommodation Type', 'trim|numeric|required');
		$this->form_validation->set_rules('description', 'Description', 'trim|required');
		$this->form_validation->set_rules('sleeps', 'Sleeps', 'trim|required|numeric');
		$this->form_validation->set_rules('bedrooms', 'Bedrooms', 'trim|required|numeric');
		$this->form_validation->set_rules('amenities', 'Amenities', 'trim');
		$this->form_validation->set_rules('additional_per_night_charge', 'Additional Charge', 'trim|decimal');
	}
	
	
	/**
	 * Accomodation Type Form
	 *
	 */
	function accommodation_type_form()
	{
		$this->load->library('form_validation');
		$this->load->model('accommodation_model');
		
		$this->form_validation->set_rules('name', 'Name', 'trim|required|min_length[3]');
		$this->form_validation->set_rules('high_price', 'High Price', 'trim|required|decimal');
	}
	
	
	/**
	 * Accomodation Delete
	 *
	 */
	function delete()
	{
		$data['id'] = $this->input->post('id');
		
		$this->load->model('accommodation_model');
		$this->accommodation_model->delete_row($data);
	}
}

/* End of file welcome.php */
/* Location: ./application/controllers/welcome.php */