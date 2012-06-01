<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Churches extends CI_Controller {

	public function __construct()
   {
        parent::__construct();
        
        // Set the church model
        $this->load->model('church_model');
   }

	public function index()
	{
		$data['churches'] = $this->church_model->get_all_churches();
		$this->load->view('admin/churches/index', $data);
	}
	
	public function create()
	{
		$this->load->helper('form');
		$this->load->library('form_validation');
		
		if ($this->form_validation->run() == FALSE)
		{
			// We need to get all church groups for dropdown
			
			
			// We need to get all regions for dropdown
			
			
			// We need to get all membership categories for dropdown
	
			$this->load->view('admin/churches/new');	
		}
		else
		{
			$this->load->view('admin/churches');
		}
	}
	
	
	
	
	
	
	public function regions()
	{
		$this->load->helper('form');
		$this->load->library('form_validation');
		
		if ($this->form_validation->run() == FALSE)
		{
			// We need to get all directors for dropdown
	
	
			$this->load->view('admin/churches/regions');	
		}
		else
		{
			$this->load->view('admin//churches/regions');
		}
	}
}

/* End of file churches.php */
/* Location: ./application/controllers/admin/churches.php */