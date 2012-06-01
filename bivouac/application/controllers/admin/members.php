<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Members extends CI_Controller {

	function index()
	{
		if ($this->is_logged_in())
		{
			redirect('admin/bookings/index');
		}
		else
		{
			$this->load->view('admin/login/index');
		}
	}
	
	function validate_user()
	{
		$this->load->library('password_hash', array(10, FALSE));
		$this->load->model('member_model');
		
		// Hash given password and check against db.
		$hashed_password = $this->password_hash->HashPassword($this->input->post('password'));
		$username = $this->input->post('username');		
		
		// Get all records matching username
		$validate = $this->member_model->validate($username);
		
		// If only 1 row is returned we have a valid user
		if ($validate->num_rows() > 0)
		{
			$valid_user = $validate->row();
			
			// Check to see if password matches
			if ($this->password_hash->CheckPassword($this->input->post('password'), $valid_user->password))
			{		
				$data = array(
					'screen_name' 	=> $valid_user->screen_name,
					'is_logged_in' 	=> true,
					'is_admin'		=> $valid_user->admin_access,
					'site_id' 		=> 1,
					'user_id'		=> $valid_user->id
				);
				
				$this->session->set_userdata($data);
				
				redirect('admin/bookings/index');
			}
			else
			{
				echo "Username or Password don't match any users on our records.";
			}
		}
		else
		{
			$this->index();
		}
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
			return FALSE;
		}
		
		if (!isset($is_admin) OR $is_admin !== 'y')
		{
			return FALSE;
		}
		
		return TRUE;
	}
}