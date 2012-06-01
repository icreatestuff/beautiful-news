<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Offers extends MY_Controller {

	/**
	 * Index Page for this controller.
	 *
	 */
	public function index()
	{
		$this->load->model('booking_model');
		$data['query'] = $this->booking_model->get_all_offers();
		$data['sites'] = $this->data['sites'];
		$data['current_site'] = $this->data['current_site'];
			
		$this->load->view('admin/offers/index', $data);
	}
	
	
	/**
	 * New Wedding
	 *
	 */
	public function new_offer()
	{
		$this->offer_form();
	
		if ($this->form_validation->run() == FALSE)
		{		
			$data['sites'] = $this->data['sites'];
			$data['current_site'] = $this->data['current_site'];
			
			$this->load->view('admin/offers/new', $data);
		}
		else
		{
			foreach ($_POST as $key => $val)
			{
				$data[$key] = $this->input->post($key);
			}
			
			// Format Dates
			$data['start_date'] = date('Y-m-d H:i:s', strtotime($data['start_date']));
			$data['end_date'] = date('Y-m-d H:i:s', (strtotime($data['start_date']) + (3 * 60 * 60 * 24)));
			
			$accommodation_ids = $data['accommodation_ids'];
			
			// unset submit
			unset($data['submit']);
			unset($data['site_id']);
			unset($data['discount_price']);
			unset($data['total_price']);
			unset($data['accommodation_ids']);
			
			// Create new booking row in db
			$this->load->model('booking_model');
			
			// Loop through all selected accommodation units to get total_price
			foreach ($accommodation_ids as $accommodation)
			{
				$ppn = $this->get_price_per_night($accommodation, $data['start_date']);
				$data['total_price'] = $ppn * 3; // Weekend break so 3 nights	
				$data['discount_price'] = round((($data['total_price'] / 100)) * (100 - $data['percentage_discount']) / 5) * 5;
				$data['accommodation_id'] = $accommodation;
				
				$this->booking_model->insert_offer_row($data);
			}
			
			redirect('admin/offers/index');
		}
	}	
		
	
	
	/**
	 * Update Offer
	 *
	 */
	public function edit_offer()
	{
		if ($this->uri->segment(4) === FALSE)
		{
			redirect('admin/offers/index');
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
				
				// Create new booking row in db
				$this->booking_model->update_contact($contact_id, $contact_data);
				
				
				
				redirect('admin/offers/index');
			}
		}
	}	
		
	
	// ---------------------------------------------------------------------------------------------------------------
	/**
	 * Get available accommodation from start_date and duration
	 *
	 */
	public function get_available_accommodation()
	{
		$post_start_date = $this->input->post('start_date');
		$post_duration = $this->input->post('duration');
		
		// Format dates
		$start_date_ts = strtotime($post_start_date);
		$start_date = date('Y-m-d', $start_date_ts);
		
		$end_date_ts = $start_date_ts + (60 * 60 * 24 * $post_duration);
		$end_date = date('Y-m-d', $end_date_ts);
		
		// Get all calendar entries whose start_date is between chosen start dates and end date
		$this->load->model('booking_model');
		$calendar_query = $this->booking_model->get_calendar_entries_between_dates($start_date, $end_date);
		
		// Get all accommodation
		$accommodation_query = $this->booking_model->get_all_accommodation(); // May need to modify this for a specific site in future
	
		$accommodation_list = "";
	
		if ($calendar_query->num_rows() > 0)
		{
			$all_accommodation = $accommodation_query->result();
		
			foreach ($calendar_query->result() as $calendar)
			{
				$accommodation_id = $calendar->accommodation_id;
				
				// Check if id is in all accommodation array
				foreach ($all_accommodation as $key => $row)
				{
					if ($row->id === $accommodation_id)
					{
						// Is it a bunk barn?
						// If yes then we need to calculate how many beds are available
						if ($row->type_name == "Bunk Barn")
						{
							$bed_date_data = array();
							$total_beds = $row->sleeps;
							$current_date = $start_date_ts;
							
							// Go through each day of the holiday and get the beds available
							while ($current_date <= $end_date_ts)
							{  
								$bed_date_data = $this->guests_per_day($row->id, $current_date, $bed_date_data, $total_beds);
								
								// Add a day to the current date  
			    				$current_date = $current_date + (60 * 60 * 24); 
							}
							
							// Search the bed data array and see if any days have 0 beds available.
							// If there are any days with 0 then unset it
							if (in_array(0, $bed_date_data))
							{
								unset($all_accommodation[$key]);
							}
						}
						else
						{
							unset($all_accommodation[$key]);
						}
					}
				}
			}
			
			// Now all unavailable accommodation has been filtered out lets create the list
			if (count($all_accommodation) > 0)
			{
				$accommodation_list .= $this->create_accommodation_list($all_accommodation, $accommodation_list, $start_date, $post_duration);
			}
			else
			{
				$accommodation_list .= "<li class='clearfix type-header'><h1>Sorry there are no units available for the date selected.</h1></li>";
			}
		}
		else
		{
			// No bookings made in that timeframe so return all accommodation
			if ($accommodation_query->num_rows() > 0)
			{
				$accommodation_list .= $this->create_accommodation_list($accommodation_query->result(), $accommodation_list, $start_date, $post_duration);
			}
			else
			{
				$accommodation_list .= "<li class='clearfix type-header'><h1>Sorry there are no units available for the date selected.</h1></li>";
			}
		}

		echo json_encode($accommodation_list);
	}
	
	
	function create_accommodation_list($all_accommodation, $accommodation_list, $start_date, $duration)
	{
		$type_header = "";
		
		foreach ($all_accommodation as $accommodation)
		{
			// Calculate price for this accommodation for this timeframe
			$ppn = $this->get_price_per_night($accommodation->id, $start_date);
			
			$price = ($ppn * $duration) + ($accommodation->additional_per_night_charge * $duration);
		
			if ($accommodation->type_name != "Bunk Barn")
			{
				// If the duration is 7 days or more they get a discount!
				if ($duration == 7)
				{
					// 5% discount
					$price = round($price - (($price / 100) * 5));
	
					// Round to nearest 5
					$price = round($price / 5) * 5;
				}
				else if ($duration == 10 || $duration == 11)
				{
					// 10% discount
					$price = round($price - (($price / 100) * 10));
	
					// Round to nearest 5
					$price = round($price / 5) * 5;
				}
				else if ($duration == 13 || $duration == 14)
				{
					// 15% discount
					$price = round($price - (($price / 100) * 15));
	
					// Round to nearest 5
					$price = round($price / 5) * 5;
				}

				if ($accommodation->type_name !== $type_header) 
				{
					$type_header = $accommodation->type_name;
					$accommodation_list .= "<li class='clearfix type-header' data-type='" . $type_header . "'><h2>" . $type_header . "s</h2></li>";
				}
				else
				{
					$accommodation_list .= "<li class='clearfix' data-id='" . $accommodation->id . "' data-price='" . $price . "' data-sleeps='" . $accommodation->sleeps . "' data-type='" . $accommodation->type_name . "' data-dogs='" . $accommodation->dogs_allowed . "'>";
				}
				
				$accommodation_list .= $accommodation->name . " &mdash; This would normally cost <b>&pound;" . $price . "</b> for " . $duration . " nights " . "<input type='checkbox' name='accommodation_ids[]' value='" . $accommodation->id . "'></li>";
			}
		}
		
		return $accommodation_list;
	}
	
	
	// ---------------------------------------------------------------------------------------------------------------
	/**
	 * Get Accommodation guests on a given date. Return array of remaining beds for dates
	 *
	 */
	private function guests_per_day($accommodation_id, $current_date, $bed_date_data, $total_beds)
	{
		$calendar_query = $this->booking_model->get_guests_from_calendar($accommodation_id);
		
		if ($calendar_query->num_rows() > 0)
		{	
			foreach ($calendar_query->result() as $calendar_row)
			{												
				$start_date = date('d-m-Y', strtotime($calendar_row->start_date));				
				$end_date = date('d-m-Y', strtotime($calendar_row->end_date));
				
				if ($this->check_in_range($start_date, $end_date, date('d-m-Y', $current_date)))
				{
					if (array_key_exists($current_date, $bed_date_data))
					{
						// id = 11 large bunk barn, id = 21 small bunk barn
						if ($accommodation_id == 11)
						{
							$bed_date_data[$current_date] = $bed_date_data[$current_date] - $calendar_row->bunk_barn_guests;
						}
						else if ($accommodation_id == 21)
						{
							$bed_date_data[$current_date] = $bed_date_data[$current_date] - $calendar_row->small_bunk_barn_guests;
						}
					}
					else
					{
						// id = 11 large bunk barn, id = 21 small bunk barn
						if ($accommodation_id == 11)
						{	
							$total_beds = $total_beds - $calendar_row->bunk_barn_guests;
						}
						else if ($accommodation_id == 21)
						{
							$total_beds = $total_beds - $calendar_row->small_bunk_barn_guests;
						}
						
						$bed_date_data[$current_date] = $total_beds;
					}
				}
			}
		}
		
		return $bed_date_data;
	}
	
	
	// ---------------------------------------------------------------------------------------------------------------
	/**
	 * Get Base Price for Start Date
	 *
	 */
	function get_price_per_night($accommodation_id, $selected_date)
	{
		$this->load->model('booking_model');
		
		$price_query = $this->booking_model->get_high_price($accommodation_id);
		if ($price_query->num_rows() > 0)
		{
			$price_row = $price_query->row();
			$price = $price_row->high_price;
			$type = $price_row->name;
		}
		
		$query = $this->booking_model->get_price();
		
		if ($query->num_rows() > 0)
		{
			foreach ($query->result() as $row)
			{
				// Check if given date is between row start and end dates
				$in_range = $this->check_in_range($row->start_date, $row->end_date, $selected_date);
				
				if ($in_range)
				{	
					$type = strtolower(str_replace(" ", "_", $type));
					$percentage_decrease = $row->$type; 
										
					$price = round($price - (($price / 100) * $percentage_decrease));
					
					// Round to nearest 5
					$price = round($price / 5) * 5;
				}
			}
		}
		
		return $price;
	}
	
	
	// ---------------------------------------------------------------------------------------------------------------
	/**
	 * Check if given date is in range
	 *
	 */
	function check_in_range($start_date, $end_date, $date_from_user)
	{
		// Convert to timestamp
		$start_ts = strtotime($start_date);
		$end_ts = strtotime($end_date);
		$user_ts = strtotime($date_from_user);
		
		// Check that user date is between start & end
		return (($user_ts >= $start_ts) && ($user_ts <= $end_ts));
	}
	
	
	/**
	 * Wedding Form
	 *
	 */
	function offer_form()
	{
		$this->load->library('form_validation');
		$this->load->model('booking_model');
		
		$this->form_validation->set_rules('accommodation_ids[]', 'Accommodation unit', 'trim|required');
		$this->form_validation->set_rules('status', 'Status', 'trim|required|min_length[2]');
		$this->form_validation->set_rules('start_date', 'Start Date', 'trim|required');
		$this->form_validation->set_rules('end_date', 'End Date', 'trim|required');
		$this->form_validation->set_rules('total_price', 'Total Price', 'trim|required');
		$this->form_validation->set_rules('discount_price', 'Discount Price', 'trim|required');
		$this->form_validation->set_rules('percentage_discount', 'Percentage Discount', 'trim|required');
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