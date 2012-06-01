<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Weddings extends MY_Controller {

	/**
	 * Index Page for this controller.
	 *
	 */
	public function index()
	{
		$this->load->model('booking_model');
		$data['query'] = $this->booking_model->get_all_weddings();
		$data['sites'] = $this->data['sites'];
		$data['current_site'] = $this->data['current_site'];
		$data['sites'] = $this->data['sites'];
		$data['current_site'] = $this->data['current_site'];
			
		$this->load->view('admin/weddings/index', $data);
	}
	
	
	/**
	 * New Wedding
	 *
	 */
	public function new_wedding()
	{
		$this->wedding_form();
	
		if ($this->form_validation->run() == FALSE)
		{		
			$data['sites'] = $this->data['sites'];
			$data['current_site'] = $this->data['current_site'];
			
			$this->load->view('admin/weddings/new', $data);
		}
		else
		{
			foreach ($_POST as $key => $val)
			{
				$data[$key] = $this->input->post($key);
			}
			
			// Format Dates
			$data['start_date'] = date('Y-m-d H:i:s', strtotime($data['start_date']));
			
			// If start day is a monday (1) then make end date 4 days away (Mid week)
			// If start day is a friday (5) then make end date 3 days away (Weekend)
			if (date('w', strtotime($data['start_date'])) == 1)
			{
				$data['end_date'] = date('Y-m-d H:i:s', (strtotime($data['start_date']) + (4 * 60 * 60 * 24)));
			}
			else
			{
				$data['end_date'] = date('Y-m-d H:i:s', (strtotime($data['start_date']) + (3 * 60 * 60 * 24)));
			}
			
			// unset submit
			unset($data['submit']);
			
			// Insert contact into db
			$contact_data = array(
				'member_id'			=> 0,
				'title'				=> $data['title'],
				'first_name'		=> $data['first_name'],
				'last_name'			=> $data['last_name'],
				'birth_day'			=> $data['birth_day'],
				'birth_month'		=> $data['birth_month'],
				'birth_year'		=> $data['birth_year'],
				'house_name'		=> $data['house_name'],
				'address_line_1'	=> $data['address_line_1'],
				'address_line_2'	=> $data['address_line_2'],
				'city'				=> $data['city'],
				'county'			=> $data['county'],
				'post_code'			=> $data['post_code'],
				'daytime_number'	=> $data['daytime_number'],
				'mobile_number'		=> $data['mobile_number'],
				'email_address'		=> $data['email_address']
			);
			
			// Create new booking row in db
			$this->booking_model->insert_contact_row($contact_data);
			
			// Set all our booking record fields
			// Get accommodation ids for all accommodation
			$this->load->model('accommodation_model');
			$query = $this->accommodation_model->get_all_for_site($this->session->userdata('site_id'));
			
			if ($query->num_rows() > 0)
			{
				foreach ($query->result() as $row)
				{
					$accommodation_ids_string .= $row->id . "|";
				}
			}
			
			$booking_data = array(
				'site_id'				=> $this->session->userdata('site_id'),
				'type'					=> 'wedding',
				'booking_ref'			=> date('dmY', strtotime($data['start_date'])) . "_ALL_0_" . strtoupper($data['last_name']),
				'accommodation_ids'		=> $accommodation_ids_string,
				'extra_ids'				=> '',
				'contact_id'			=> $this->db->insert_id(),
				'start_date'			=> $data['start_date'],
				'end_date'				=> $data['end_date'],
				'adults'				=> 0,
				'children'				=> 0,
				'babies'				=> 0,
				'total_price'			=> $data['total_price'],
				'amount_paid'			=> $data['amount_paid'],
				'payment_status'		=> 'pending',
				'booking_creation_date'	=> date('Y-m-d H:i:s'),
				'notes'					=> filter_var($data['notes'], FILTER_SANITIZE_STRING)
			);
			
			if (isset($data['amount_paid']) && !empty($data['amount_paid']))
			{
				$booking_data['payment_status'] = 'deposit';
				
				if ($data['amount_paid'] == $data['total_amount'])
				{
					$booking_data['payment_status'] = 'fully paid';
				}
			}
			
			// Create new booking row in db
			$this->booking_model->insert_booking_row($booking_data);
			
			// Create calendar rows for all accommodation ids
			$accommodation_ids = explode('|', $accommodation_ids_string);
			$booking_id = $this->db->insert_id();
			
			foreach($accommodation_ids as $id)
			{
				$calendar_data = array(
					'site_id'			=> $this->session->userdata('site_id'),
					'accommodation_id'	=> $id,
					'booking_id'		=> $booking_id,
					'start_date'		=> $data['start_date'],
					'end_date'			=> $data['end_date']
				);
				
				// Sort out bunk barn total_guests
				if ($id == 11 || $id == 21)
				{
					// Get total_guests for bunk barns
					$query = $this->accommodation_model->get_single_row($id);
					
					if ($query->num_rows() > 0)
					{
						$row = $query->row();
					
						if ($id == 11)
						{
							$calendar_data['bunk_barn_guests'] = $row->sleeps;
						}
						else if ($id == 21)
						{
							$calendar_data['small_bunk_barn_guests'] = $row->sleeps;
						}	
					}
				}
			
				$this->booking_model->insert_calendar_row($calendar_data);
			}
			
			redirect('admin/weddings/index');
		}
	}	
		
	
	
	/**
	 * Update Wedding
	 *
	 */
	public function edit_wedding()
	{
		if ($this->uri->segment(4) === FALSE)
		{
			redirect('admin/weddings/index');
		}
		else
		{	
			$this->wedding_form();
			
			if ($this->form_validation->run() == FALSE)
			{		
				$data['sites'] = $this->data['sites'];
				$data['current_site'] = $this->data['current_site'];
				
				// Get saved data
				$id = $this->uri->segment(4);
			
				$data['booking_data'] = $this->booking_model->get_booking($id);	
				
				if ($data['booking_data']->num_rows() > 0)
				{
					$row = $data['booking_data']->row();
					
					// Get contact details
					$data['contact_data'] = $this->booking_model->get_contact($row->contact_id);
				}
			
				$this->load->view('admin/weddings/edit', $data);
			}
			else
			{
				foreach ($_POST as $key => $val)
				{
					$data[$key] = $this->input->post($key);
				}
				
				// Get saved data
				$booking_id = $this->uri->segment(4);
			
				$query = $this->booking_model->get_booking($booking_id);	
				
				if ($query->num_rows() > 0)
				{
					$row = $query->row();
					$contact_id = $row->contact_id;
				}
				
				// Format Dates
				$data['start_date'] = date('Y-m-d H:i:s', strtotime($data['start_date']));
				$data['end_date'] = date('Y-m-d H:i:s', (strtotime($data['start_date']) + (3 * 60 * 60 * 24)));
				
				// unset submit
				unset($data['submit']);
				
				// Insert contact into db
				$contact_data = array(
					'member_id'			=> 0,
					'title'				=> $data['title'],
					'first_name'		=> $data['first_name'],
					'last_name'			=> $data['last_name'],
					'birth_day'			=> $data['birth_day'],
					'birth_month'		=> $data['birth_month'],
					'birth_year'		=> $data['birth_year'],
					'house_name'		=> $data['house_name'],
					'address_line_1'	=> $data['address_line_1'],
					'address_line_2'	=> $data['address_line_2'],
					'city'				=> $data['city'],
					'county'			=> $data['county'],
					'post_code'			=> $data['post_code'],
					'daytime_number'	=> $data['daytime_number'],
					'mobile_number'		=> $data['mobile_number'],
					'email_address'		=> $data['email_address']
				);
				
				// Create new booking row in db
				$this->booking_model->update_contact($contact_id, $contact_data);
				
				// Set all our booking record fields
				// Get accommodation ids for all accommodation
				$this->load->model('accommodation_model');
				$query = $this->accommodation_model->get_all_for_site($this->session->userdata('site_id'));
				
				if ($query->num_rows() > 0)
				{
					foreach ($query->result() as $row)
					{
						$accommodation_ids_string .= $row->id . "|";
					}
				}
				
				$booking_data = array(
					'site_id'				=> $this->session->userdata('site_id'),
					'type'					=> 'wedding',
					'booking_ref'			=> date('dmY', strtotime($data['start_date'])) . "_ALL_0_" . strtoupper($data['last_name']),
					'accommodation_ids'		=> $accommodation_ids_string,
					'extra_ids'				=> '',
					'contact_id'			=> $contact_id,
					'start_date'			=> $data['start_date'],
					'end_date'				=> $data['end_date'],
					'adults'				=> 0,
					'children'				=> 0,
					'babies'				=> 0,
					'total_price'			=> $data['total_price'],
					'payment_status'		=> 'pending',
					'booking_creation_date'	=> date('Y-m-d H:i:s'),
				);
				
				
				if (isset($data['amount_paid']) && !empty($data['amount_paid']))
				{
					$booking_data['payment_status'] = 'deposit';
					
					if ($data['amount_paid'] == $data['total_amount'])
					{
						$booking_data['payment_status'] = 'fully paid';
					}
				}
				
				// Create new booking row in db
				$this->booking_model->update_booking($booking_id, $booking_data);
				
				// Create calendar rows for all accommodation ids
				$accommodation_ids = explode('|', $accommodation_ids_string);
				
				// Delete all calendar records first then add them all again incase of a date change.
				$this->booking_model->delete_calendar_record($booking_id);
				
				foreach ($accommodation_ids as $id)
				{
					$calendar_data = array(
						'site_id'			=> $this->session->userdata('site_id'),
						'accommodation_id'	=> $id,
						'booking_id'		=> $booking_id,
						'start_date'		=> $data['start_date'],
						'end_date'			=> $data['end_date']
					);
					
					// Sort out bunk barn total_guests
					if ($id == 11 || $id == 21)
					{
						// Get total_guests for bunk barns
						$query = $this->accommodation_model->get_single_row($id);
						
						if ($query->num_rows() > 0)
						{
							$row = $query->row();
						
							if ($id == 11)
							{
								$calendar_data['bunk_barn_guests'] = $row->sleeps;
							}
							else if ($id == 21)
							{
								$calendar_data['small_bunk_barn_guests'] = $row->sleeps;
							}	
						}
					}
				
					$this->booking_model->insert_calendar_row($calendar_data);
				}
				
				redirect('admin/weddings/index');
			}
		}
	}	
		
	
	/**
	 * Wedding Form
	 *
	 */
	function wedding_form()
	{
		$this->load->library('form_validation');
		$this->load->model('booking_model');
		
		$this->form_validation->set_rules('first_name', 'First Name', 'trim|required|min_length[2]');
		$this->form_validation->set_rules('last_name', 'Surname', 'trim|required|min_length[2]');
		$this->form_validation->set_rules('start_date', 'Start Date', 'trim|required');
	}
	
	
	/**
	 * Wedding Delete
	 *
	 */
	function delete()
	{
		// Get ID from POST
		$id = $this->input->post('id');
		
		// Delete all calendar entries associated with booking id
		$this->load->model('booking_model');
		$this->booking_model->delete_calendar_record($id);
		
		// Set payment_status to 'cancelled' for this booking.
		$this->booking_model->delete_booking_record($id);
	}
}

/* End of file holidays.php */
/* Location: ./application/controllers/holiodays.php */