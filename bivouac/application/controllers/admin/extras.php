<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Extras extends MY_Controller {

	/**
	 * Index Page for this controller.
	 *
	 */
	public function index()
	{
		$this->load->model('extras_model');
		$site_id = $this->session->userdata('site_id');
		
		$data['query'] = $this->extras_model->get_all_for_site($site_id);
		$data['sites'] = $this->data['sites'];
		$data['current_site'] = $this->data['current_site'];
		
		$this->load->view('admin/extras/index', $data);
	}
	
	/**
	 * New extras.
	 *
	 */
	public function new_extra()
	{
		$this->extras_form();

		if ($this->form_validation->run() == FALSE)
		{	
			$data['types'] = $this->extras_model->get_types();
			$data['sites'] = $this->data['sites'];
			$data['current_site'] = $this->data['current_site'];
			
			$this->load->view('admin/extras/new', $data);
		}
		else
		{
			foreach ($_POST as $key => $val)
			{
				$data[$key] = $this->input->post($key);
			}
			
			// unset submit
			unset($data['submit']);
			
			// Format Dates
			if (!empty($data['start_date']))
			{
				$data['start_date'] = date('Y-m-d H:i:s', strtotime($data['start_date']));
			}
			
			if (!empty($data['end_date']))
			{
				$data['end_date'] = date('Y-m-d H:i:s', strtotime($data['end_date']));
			}
			
			if (!empty($data['cut_off_date']))
			{
				$data['cut_off_date'] = date('Y-m-d H:i:s', strtotime($data['cut_off_date']));
			}
			
			$data = $this->do_upload($_FILES, $data);
			
			// Create extras row in db
			$this->extras_model->insert_row($data);	
			redirect('admin/extras/index');
		}
	}
	
	public function edit_extra()
	{
		if ($this->uri->segment(4) === FALSE)
		{
			redirect('admin/extras/index');
		}
		else
		{
			$extras_id = $this->uri->segment(4);
		
			$this->extras_form();
		
			if ($this->form_validation->run() == FALSE)
			{		
				// Get extras Information	
				$extras_row = $this->extras_model->get_single_row($extras_id);
				$data['extra'] = $extras_row->row();
				
				$data['types'] = $this->extras_model->get_types();
				$data['sites'] = $this->data['sites'];
				$data['current_site'] = $this->data['current_site'];
				
				$this->load->view('admin/extras/edit', $data);
			}
			else
			{
				foreach ($_POST as $key => $val)
				{
					$data[$key] = $this->input->post($key);
				}
				
				// unset submit
				unset($data['submit']);
				
				// Format Dates
				if (!empty($data['start_date']))
				{
					$data['start_date'] = date('Y-m-d H:i:s', strtotime($data['start_date']));
				}
				
				if (!empty($data['end_date']))
				{
					$data['end_date'] = date('Y-m-d H:i:s', strtotime($data['end_date']));
				}
				
				if (!empty($data['cut_off_date']))
				{
					$data['cut_off_date'] = date('Y-m-d H:i:s', strtotime($data['cut_off_date']));
				}
				
				$data = $this->do_upload($_FILES, $data);
				
				// Create extras row in db
				$this->extras_model->update_row($extras_id, $data);
				
				redirect('admin/extras/index');
			}
		}
	}
	
	/**
	 * New extras Type.
	 *
	 */
	public function new_extra_type()
	{
		$this->extras_type_form();

		if ($this->form_validation->run() == FALSE)
		{	
			$data['sites'] = $this->data['sites'];
			$data['current_site'] = $this->data['current_site'];
			$this->load->view('admin/extras/new_type', $data);
		}
		else
		{
			foreach ($_POST as $key => $val)
			{
				$data[$key] = $this->input->post($key);
			}
			
			// unset submit
			unset($data['submit']);
			
			// Create extras type row in db
			$this->extras_model->insert_type_row($data);	
			redirect('admin/extras/index');
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
		$config['upload_path'] = './images/extras/';
		$config['allowed_types'] = 'gif|jpg|png|jpeg';
		$config['max_size']	= '1048576';
		$config['max_width']  = '2000';
		$config['max_height']  = '2000';
		$config['overwrite'] = True;
		
		foreach ($_FILES as $key => $val)
		{
			if(!empty($val['name'])) 
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
					echo $this->upload->display_errors();
				}
			}
		}
		
		return $data;
	}
	
	/**
	 * Accomodation Form
	 *
	 */
	function extras_form()
	{
		$this->load->library('form_validation');
		$this->load->model('extras_model');
		
		$this->form_validation->set_rules('name', 'Name', 'trim|required|min_length[3]');
		$this->form_validation->set_rules('site_id', 'Site', 'trim|numeric');
		$this->form_validation->set_rules('extra_type', 'Extra Type', 'trim|numeric|required');
		$this->form_validation->set_rules('description', 'Description', 'trim|required');
		$this->form_validation->set_rules('price', 'Price', 'trim|required|decimal');
	}
	
	/**
	 * Accomodation Type Form
	 *
	 */
	function extras_type_form()
	{
		$this->load->library('form_validation');
		$this->load->model('extras_model');
		
		$this->form_validation->set_rules('name', 'Name', 'trim|required|min_length[3]');
	}
	
	/**
	 * Accomodation Delete
	 *
	 */
	function delete()
	{
		$data['id'] = $this->input->post('id');
		
		$this->load->model('extras_model');
		$this->extras_model->delete_row($data);
	}
}

/* End of file welcome.php */
/* Location: ./application/controllers/welcome.php */