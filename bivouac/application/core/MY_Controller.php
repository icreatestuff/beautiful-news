<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class MY_Controller extends CI_Controller {
	
	public function __construct()
	{
		parent::__construct();
		
		$this->is_logged_in();
		$this->set_site();
	}
	
	/**
	 * Check if user logged in and has admin permissions.
	 *
	 */
	function is_logged_in()
	{
		$is_logged_in = $this->session->userdata('is_logged_in');
		$is_admin = $this->session->userdata('is_admin');
		
		if (!isset($is_logged_in) OR $is_logged_in !== true)
		{
			redirect('admin/members/index');
		}
		
		if (!isset($is_admin) OR $is_admin !== 'y')
		{
			redirect('admin/members/index');
		}
	}
	
	/**
	 * Set Site
	 *
	 */
	function set_site()
	{
		$site_id = $this->session->userdata('site_id');
		
		if (!isset($site_id))
		{
			$site_id = 1;
			$this->session->set_userdata('site_id', $site_id);
		}
		
		$this->load->model('site_model');
		$this->data['sites'] = $this->site_model->get_all_bar_current($site_id);
		$this->data['current_site'] = $this->site_model->get_single_row($site_id);
	}
}