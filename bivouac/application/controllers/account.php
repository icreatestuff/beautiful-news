<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Account extends CI_Controller {

	/**
	 * Index Page for this controller.
	 *
	 */
	public function index()
	{
		redirect('account/bookings');
	}
	
	public function bookings()
	{
		// Check if user is logged in already
		$this->is_logged_in();
		
		// User is logged in. So gather all booking info and pass to template
		$this->load->model('member_model');
		$data['bookings'] = $this->member_model->get_member_bookings($this->session->userdata('user_id'));
		
		$user = $this->member_model->get_member_info($this->session->userdata('user_id'));
		
		if ($user->num_rows == 1)
		{
			$data['user'] = $user->row();
		}
		
		$this->load->view('account/bookings', $data);
	}
	
	
	public function settings()
	{
		// Check if user is logged in already
		$this->is_logged_in();
		
		// User is logged in.
		$this->settings_form();
		$this->load->model('member_model');
		$member_id = $this->session->userdata('user_id');
		
		if ($this->form_validation->run() == FALSE)
		{
			$member = $this->member_model->get_member_email($member_id);
			
			if ($member->num_rows() == 1)
			{
				$data['member'] = $member->row();
			}
			
			$this->load->view('account/settings', $data);
		}
		else
		{
			$this->load->library('password_hash', array(10, FALSE));
			
			// Update member record and contact record	
			$new_member_data = array();
			
			if (!empty($_POST['email_address']))
			{
				$email_address = $this->input->post('email_address');
				
				$new_member_data['email_address'] = $email_address;
				$new_member_data['username'] = $email_address;
			}
			
			if (!empty($_POST['password']))
			{
				$hashed_password = $this->password_hash->HashPassword($this->input->post('password'));
				
				$new_member_data['password'] = $hashed_password;
			}
			
			if (isset($email_address))
			{
				$this->member_model->update_contact_email(array('email_address' => $email_address), $member_id);
				
				$this->member_model->update_member($new_member_data, $member_id);				
			}
			else if (isset($hashed_password))
			{
				$this->member_model->update_member($new_member_data, $member_id);
			}
			
			// Set flashdata with success message
			$this->session->set_flashdata('member_update_message', 'Your account settings have been successfully updated.');
			
			//Load accounts bookings page
			redirect('account/bookings');
		}
	}
	
	
	public function address()
	{
		// Check if user is logged in already
		$this->is_logged_in();
		
		// User is logged in.
		$this->address_form();
		
		$this->load->model('member_model');
		$member_id = $this->session->userdata('user_id');
		
		if ($this->form_validation->run() == FALSE)
		{
			$address = $this->member_model->get_member_info($member_id);
			
			if ($address->num_rows() == 1)
			{
				$data['address'] = $address->row();
			}
			
			$this->load->view('account/address', $data);
		}
		else
		{
			// Form validates, update contacts address
			foreach ($_POST as $key => $val)
			{
				$data[$key] = $this->input->post($key);
			}
			
			unset($data['submit']);	
			
			$this->member_model->update_contact($data, $member_id);
			
			// Set success user message
			$this->session->set_flashdata('member_update_message', 'Your address details have been successfully updated.');
			
			redirect('account/bookings');
		}
	}
	
	
	public function booking_overview()
	{	
		if ($this->uri->segment(3) === FALSE || !is_numeric($this->uri->segment(3)))
		{
			redirect('account/bookings');
		}
		else
		{
			// Check if user is logged in already
			$this->is_logged_in();
			
			$this->load->model('member_model');
			$member_id = $this->session->userdata('user_id');
		
			// Check if session data booking_id equals segment 3
			$booking_id = $this->uri->segment(3);
			
			// Check this booking belongs to this member!
			$check = $this->member_model->booking_member_check($member_id, $booking_id);
			
			if ($check->num_rows() == 1)
			{
				// Booking belongs to logged in member
				// Get Booking Details
				$this->load->model('booking_model');
				$data['booking_details'] = $this->booking_model->get_booking($booking_id);
				
				if ($data['booking_details']->num_rows() > 0)
				{
					$booking = $data['booking_details']->row();
					$accommodation_ids = explode("|", $booking->accommodation_ids);
					$contact_id = $booking->contact_id;
				}
				
				// Get Accommodation Details
				foreach ($accommodation_ids as $accommodation_id)
				{
					$data['accommodation_details'][] = $this->booking_model->get_accommodation($accommodation_id);
				}
				
				// Get Contact Details
				$data['contact_details'] = $this->booking_model->get_contact($contact_id);
				
				// Get Extras
				$data['extras'] = $this->booking_model->get_booked_extras($booking_id);
				
				// User Details
				$data['user'] = array(
					'screen_name' 	=> $this->session->userdata('screen_name'),
					'user_id'		=> $this->session->userdata('user_id')
				);
			
				$this->load->view('account/booking_overview', $data);
			}
			else
			{
				// booking does not belong to logged in member! Redirect back to their account page
				redirect('account/bookings');
			}
		}		
	}
	
	
	public function login()
	{
		$this->load->view('account/login');
	}
	
	
	public function logout()
	{
		// Destroy the session
		$this->session->sess_destroy();
		
		// Return to booking homepage
		redirect('booking/index');
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
				
				redirect('account/bookings');
			}
			else
			{
				$this->session->set_flashdata("user_message", "Username or Password don't match any users on our records.");
				redirect('account/login');
			}
		}
		else
		{
			$this->session->set_flashdata("user_message", "Username or Password don't match any users on our records.");
			redirect('account/login');
		}
	}
	
	function forgot_password()
	{
		$this->forgot_password_form();
		
		if ($this->form_validation->run() == FALSE)
		{
			$this->load->view('account/forgot_password');
		}
		else
		{
			$this->load->library('password_hash', array(10, FALSE));
			$this->load->model('member_model');
			
			// Email Address to match against members.
			$email = $this->input->post('email_address');
			
			// See if email address matches any member accounts that do not have admin access
			$validate = $this->member_model->validate($email);
	
			// If only 1 row is returned we have a valid user
			if ($validate->num_rows() > 0)
			{
				$valid_user = $validate->row();
				
				// Reset their password to something random
				$new_password = $this->generate_password();
				$hashed_password = $this->password_hash->HashPassword($new_password);
				$data['password'] = $hashed_password;
				
				$this->member_model->update_member($data, $valid_user->id);			
				
				$this->load->library("email");
				
				// Make sure we're sending hmtl email	
				$this->email->from("bookings@thebivouac.co.uk", "The Bivouac");
				$this->email->to($valid_user->email_address);
				
				$this->email->subject("Password Reset (Bivouac)");
				
				$this->email->message("Hello\n\nAs requested, your password has been reset.\n\nOnce you have logged in you should create a new password under your account settings for increased security.\n\nNew Password: " . $new_password . "\n\nYou can login to your Bivouac account by going to this url:\nhttp://booking.thebivouac.co.uk/account/login\n\nThank you,\nBivouac");
				
				// Send the email!
				if (!$this->email->send())
				{
					$this->session->set_flashdata("user_message", "We were unable to send you your new password. Please try resetting it again. Thank you.");
					redirect('account/forgot_password');		
				}
				else
				{
					$this->session->set_flashdata("user_message", "We have sent you a new password by email. Please create a new password when you have logged in under your account settings. Thank you");
					redirect('account/login');
				}
			}
			else
			{
				$this->session->set_flashdata("user_message", "Email Address does not match any users on our records.");
				redirect('account/forgot_password');
			}
		}
	}
	
	
	function forgot_password_form()
	{
		$this->load->library('form_validation');
		
		$this->form_validation->set_rules('email_address', 'Email Address', 'trim|valid_email|required');
	}
	
	function settings_form()
	{
		$this->load->library('form_validation');
		
		$this->form_validation->set_rules('email_address', 'Email Address', 'trim|valid_email');
		$this->form_validation->set_rules('password', 'password', 'trim|min_length[6]');
		$this->form_validation->set_rules('password_confirmation', 'Password Confirmation', 'trim|matches[password]');
	}
	
	
	function address_form()
	{
		$this->load->library('form_validation');
		
		$this->form_validation->set_rules('house_name', 'House name/number', 'trim|required');
		$this->form_validation->set_rules('address_line_1', 'Address Line 1', 'trim|required|min_length[2]');
		$this->form_validation->set_rules('address_line_2', 'Address Line 2', 'trim');
		$this->form_validation->set_rules('city', 'Town/City', 'trim|required|min_length[2]');
		$this->form_validation->set_rules('county', 'County', 'trim|required|min_length[2]');
		$this->form_validation->set_rules('post_code', 'Postcode', 'trim|required|min_length[6]');
	}
	
	function generate_password($length = 8)
	{	
		// start with a blank password
		$password = "";
		$possible = "2346789bcdfghjkmnpqrtvwxyzBCDFGHJKLMNPQRTVWXYZ";
		
		// we refer to the length of $possible a few times, so let's grab it now
		$maxlength = strlen($possible);
		
		// check for length overflow and truncate if necessary
		if ($length > $maxlength) 
		{
			$length = $maxlength;
		}
		
		// set up a counter for how many characters are in the password so far
		$i = 0; 
		
		// add random characters to $password until $length is reached
		while ($i < $length) 
		{ 
			// pick a random character from the possible ones
			$char = substr($possible, mt_rand(0, $maxlength-1), 1);
			
			// have we already used this character in $password?
			if (!strstr($password, $char)) 
			{ 
				// no, so it's OK to add it onto the end of whatever we've already got...
				$password .= $char;
				// ... and increase the counter by one
				$i++;
			}
		}
		
		return $password;	
	}
	
	
	function is_logged_in()
	{
		$is_logged_in = $this->session->userdata('is_logged_in');
		
		if (!isset($is_logged_in) OR $is_logged_in !== true)
		{
			redirect('account/login');
		}
	}	
}