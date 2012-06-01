<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Accommodation extends CI_Controller {

	function __construct()
	{
		parent::__construct();
		
		// Site Id session variable
		$this->load->library('session');
		$site_id = $this->session->userdata('site_id');
		
		if (!isset($site_id) || empty($site_id))
		{
			$site_id = 1;
			$this->session->set_userdata('site_id', $site_id);
		}
	}

	// ---------------------------------------------------------------------------------------------------------------
	/**
	 * Index Page for this controller.
	 *
	 */
	public function index()
	{
		$site_id = $this->session->userdata('site_id');
		
		$this->load->model('accommodation_model');
		$data['query'] = $this->accommodation_model->get_all_for_site($site_id);
		
		$this->load->view('accommodation/index', $data);
	}
	
	
	// ---------------------------------------------------------------------------------------------------------------
	/**
	 * Single entry accommodation page
	 *
	 */
	public function unit()
	{
		if ($this->uri->segment(3) === FALSE)
		{
			redirect('/accommodation/index');
		}
		else
		{
			$this->load->model('accommodation_model');
			$this->accommodation_booking_form();
			$accommodation_id = $this->uri->segment(3);
			
			if ($this->form_validation->run() == FALSE)
			{	
				$accommodation_row = $this->accommodation_model->get_single_row($accommodation_id);
				$data['accommodation'] = $accommodation_row->row();
			
				$this->load->view('accommodation/unit', $data);
			}
			else
			{
				// Booking Form validates so create new booking.
				foreach ($_POST as $key => $val)
				{
					$data[$key] = $this->input->post($key);
				}
				
				// Calculate the price of the booking
				$id = $data['accommodation_ids'];
				
				$this->load->model('booking_model');
				$accommodation_query = $this->booking_model->get_accommodation($id);
				
				if ($accommodation_query->num_rows > 0)
				{
					$accommodation_row = $accommodation_query->row();
	
					$ppn = $this->get_price_per_night($id, $data['start_date']);
				
					$type_query = $this->booking_model->get_type($id);
					
					if ($type_query->num_rows > 0)
					{
						$type_row = $type_query->row();
						
						if ($type_row->name === "Bunk Barn")
						{
							// Is this the large or small bunk barn? 11 = large, 21 = small
							if ($id == 11)
							{
								// Add up all adults and children
								$large_bunk_barn_guests = (int) $data['adults'] + (int) $data['children'];
								
								$accom_price = ceil(($ppn * $data['duration']) / 1) * 1;	
								
								$price = $price + ($accom_price * $large_bunk_barn_guests);
							}
							else
							{								
								// Add up all adults and children
								$small_bunk_barn_guests = (int) $data['adults'] + (int) $data['children'];
								
								$accom_price = ceil(($ppn * $data['duration']) / 1) * 1;	
								
								$price = $price + ($accom_price * $small_bunk_barn_guests);
							}
						
						
							$accom_price = ceil(($ppn * $data['duration']) / 1) * 1;
							$data['total_price'] = $accom_price * ((int) $data['adults'] + $data['children']);
						}					
						else
						{
							$accom_price = ceil(($ppn * $data['duration']) / 5) * 5;
							$data['total_price'] = $accom_price + ($accommodation_row->additional_per_night_charge * $data['duration']);
							
							// If the duration is 7 days or more they get a discount!
							if ($data['duration'] == 7)
							{
								// 5% discount
								$data['total_price'] = round($price - (($price / 100) * 5));
				
								// Round to nearest 5
								$data['total_price'] = ceil($price / 5) * 5;
							}
							else if ($data['duration'] == 10 || $data['duration'] == 11)
							{
								// 10% discount
								$data['total_price'] = round($price - (($price / 100) * 10));
				
								// Round to nearest 5
								$data['total_price'] = ceil($price / 5) * 5;
							}
							else if ($data['duration'] == 14)
							{
								// 5% discount
								$data['total_price'] = round($price - (($price / 100) * 15));
				
								// Round to nearest 5
								$data['total_price'] = ceil($price / 5) * 5;
							}
						}
					}
				}
				
				
				// Are any dogs coming? If dogs is > 0 then we need to get the price of a dog per night from extras
				// We also at this stage need to add the extra to the booking.
				if (isset($data['dogs']) && (int) $data['dogs'] > 0)
				{
					// Get price of dog extra (id. 14)
					$extra_query = $this->booking_model->get_extra_by_id(14);
					
					if ($extra_query->num_rows() > 0)
					{
						$extra_row = $extra_query->row();
						
						$dogs_price = (int) $data['dogs'] * (int) $data['duration'] * (int) $extra_row->price;
						
						// Update total booking price
						$data['total_price'] = $data['total_price'] + $dogs_price;
						
						// Write dogs to purchased_extras
						// Insert as unique entries in purchased_entries table
						$insert_data = array(
							'extra_id' 		=> 14,
							'quantity'		=> $data['dogs'],
							'nights'		=> $data['duration'],
							'price'			=> $dogs_price,
							'date'			=> ""
						);	
					}
				}
				
				unset($data['dogs']);
				
				
				// Add payment status and calculate end_date from start_date + duration
				$data['payment_status'] = "Unpaid";
				
				$start_date_ts = strtotime($data['start_date']);
				$end_date_ts = $start_date_ts + (60 * 60 * 24 * $data['duration']);
				$data['start_date'] = date('Y-m-d H:i:s', $start_date_ts);
				$data['end_date'] = date('Y-m-d H:i:s', $end_date_ts);
				
				// Generate Booking Reference number FORMAT: ddmmyy_UNITID_#GUESTS
				// We need the unit Code for this accommodation
				$unit_query = $this->accommodation_model->get_unit_code($accommodation_id);
				$unit_row = $unit_query->row();
				
				$data['booking_ref'] = date('dmY', $start_date_ts) . "_" . $unit_row->unit_id . "_" . ((int) $data['adults'] + (int) $data['children']);
			
				// Set creation date to now
				$data['booking_creation_date'] = date('Y-m-d H:i:s');
			
				// Unset unused elements
				unset($data['submit']);	
				unset($data['duration']);
				
				// Create Booking record
				$this->booking_model->insert_booking_row($data);
				
				// Create Calendar record
				// Get last insert ID from bookings table
				$data['booking_id'] = $this->db->insert_id();
				
				// Add dog extras if $insert_data exists
				if (isset($insert_data) && !empty($insert_data))
				{
					$insert_data['booking_id'] = $data['booking_id'];
						
					$this->booking_model->insert_extra_purchase($insert_data);	
				}
				
				$key_booking_data = array(
					'booking_id' => $data['booking_id'],
					'booking_ref' => $data['booking_ref']
				);
				
				// Unset any fields we don't need for calendar table
				unset($data['adults']);
				unset($data['children']);
				unset($data['babies']);
				unset($data['total_price']);
				unset($data['payment_status']);
				unset($data['booking_ref']);
				unset($data['booking_creation_date']);
				
				$data['accommodation_id'] = $data['accommodation_ids'];
				unset($data['accommodation_ids']);
				
				if ($id == 11)
				{
					$data['bunk_barn_guests'] = $large_bunk_barn_guests;
				}
				else if ($id == 21)
				{
					$data['small_bunk_barn_guests'] = $small_bunk_barn_guests;
				}
				
				
				$this->booking_model->insert_calendar_row($data);
				
				$this->load->library('session');
				$this->session->set_userdata('booking_id', $key_booking_data['booking_id']);
				
				redirect('booking/extras/' . $key_booking_data['booking_id']);
			}
		}
	}
	
	
	// ---------------------------------------------------------------------------------------------------------------
	/**
	 * Single entry accommodation page
	 *
	 */
	public function unit_lightbox()
	{
		$this->load->model('accommodation_model');
		$this->accommodation_booking_form();
		$accommodation_id = $this->uri->segment(3);
		
		$accommodation_row = $this->accommodation_model->get_single_row($accommodation_id);
		$data['accommodation'] = $accommodation_row->row();
	
		$this->load->view('accommodation/unit_lightbox', $data);
	}
	
	
	// ---------------------------------------------------------------------------------------------------------------
	/**
	 * Get booked dates to make unavailable on calendar
	 *
	 */
	public function get_booked_dates()
	{
		$data = array('response' => false);
		
		$accommodation_id = $this->input->post('id');
		$is_bunk_barn = $this->input->post('bunk_barn');
		
		$this->load->model('booking_model');
		
		$accommodation_beds_query = $this->booking_model->get_total_beds($accommodation_id);
		$accommodation_row = $accommodation_beds_query->row();
		$data['beds'] = $accommodation_row->sleeps;
		
		$bed_date_data = array();
		
		// Get booked up dates
		$query = $this->booking_model->get_all_from_accommodation_id($accommodation_id);
		
		if ($query->num_rows() > 0)
		{
			$data['response'] = true;
						
			foreach ($query->result() as $row)
			{
				// Get dates between start and end date, formatted as dd/mm/yy
				$start_date_ts = strtotime($row->start_date);
				$start_date = date('d-m-Y', $start_date_ts);
				
				// The end_date is actually a new booking in day so we need to make it -1 day
				$end_date_ts = strtotime($row->end_date) - (60 * 60 * 24);
				$end_date = date('d-m-Y', $end_date_ts);
				
				$current_date = $start_date_ts;
				
				while ($current_date <= $end_date_ts)
				{  
				    $total_beds = $accommodation_row->sleeps;
				  
				  	if ($is_bunk_barn != "false")
					{
						$bed_date_data = $this->guests_per_day($accommodation_id, $current_date, $bed_date_data, $total_beds);
						
						// Add this new day to the dates array  
						$data['dates'][] = array(date('d-m-Y', $current_date), $bed_date_data[$current_date]);
					}
					else
					{
						// Add this new day to the dates array  
						$data['dates'][] = array(date('d-m-Y', $current_date), $total_beds);
					}
					
					// Add a day to the current date  
			    	$current_date = $current_date + (60 * 60 * 24); 
				}  
			}
		}
		
		// Get all site closed dates
		$this->load->model('site_closed_model');
		
		$query = $this->site_closed_model->get_all();
		
		if ($query->num_rows() > 0)
		{
			$data['response'] = true;
						
			foreach ($query->result() as $row)
			{
				// Get dates between start and end date, formatted as dd/mm/yy
				$start_date_ts = strtotime($row->start_date);
				$start_date = date('d-m-Y', $start_date_ts);
				
				// The end_date is actually a new booking in day so we need to make it -1 day
				$end_date_ts = strtotime($row->end_date);
				$end_date = date('d-m-Y', $end_date_ts);
				
				$current_date = $start_date_ts;
				
				while ($current_date <= $end_date_ts)
				{  
					$total_beds = $accommodation_row->sleeps;
				  
				  	if ($is_bunk_barn != "false")
					{	
						// Add this new day to the dates array  
						$data['dates'][] = array(date('d-m-Y', $current_date), 0);
					}
					else
					{
						// Add this new day to the dates array  
						$data['dates'][] = array(date('d-m-Y', $current_date), $total_beds);
					}
					
					// Add a day to the current date  
			    	$current_date = $current_date + (60 * 60 * 24); 
				}  
			}
		}

		echo json_encode($data);
	}	

	
	
	// ---------------------------------------------------------------------------------------------------------------
	/**
	 * Get available durations & prices based on selected start_date
	 *
	 */
	public function get_durations()
	{
		$accommodation_id = $this->input->post('id');
		$start_date = $this->input->post('start_date');
		
		// Get accommodation price per night (ppn)
		$ppn = $this->get_price_per_night($accommodation_id, $start_date);
		
		$additional_cost = $this->get_additional_accommodation_cost($accommodation_id);
	
		// Get all bookings up to 2 weeks after this start_date
		$start_date_ts = strtotime($start_date);
		$start_date = date('Y-m-d', $start_date_ts);
		$selected_start_date = date('D jS M', strtotime($start_date));
		$day = date('l', $start_date_ts);
		$two_weeks_ahead = date('Y-m-d', $start_date_ts + (60 * 60 * 24 * 14));
		
		// Get all site closed dates
		$closed_dates = $this->closed_dates($start_date_ts);
		
		$this->load->model('booking_model');

		$one_day = 60 * 60 * 24;
		$holiday_in_range = false;
		$selected_start = date('Y-m-d', $start_date_ts);
		$duration = array();

		// run through all site closed_dates
		if (count($closed_dates) > 0)
		{
			// Closed start date
			$closed_start = $closed_dates[0]['start_date'];	
			$closed_in_range = FALSE;
			$booked_in_range = FALSE;
			
			if ($this->date_difference($start_date, $closed_start) < 15)
			{
				$closed_in_range = TRUE;
			}
			
			$query = $this->booking_model->get_bookings_within_two_weeks($accommodation_id, $start_date, $two_weeks_ahead);
			if ($query->num_rows() > 0)
			{
				$row = $query->row();
				$booked_start = $row->start_date;	
				$booked_in_range = TRUE;
			}
			
			// Check if there are any public holidays within 2 weeks of selected start date
			// Get all public holidays
			$public_holidays = $this->public_holidays();
			$holiday_in_range = FALSE;
	
			foreach ($public_holidays as $holiday)
			{
				if ($holiday_in_range)
				{
					break;
				}
				
				// Is holiday date a monday?
				if (date('l', strtotime($holiday['start_date'])) === "Monday")
				{
					$holiday_in_range = $this->check_in_range($selected_start, $two_weeks_ahead, $holiday['start_date']);
					$holiday_date = $holiday['start_date'];
				}
			}
			
			if ($closed_in_range && $booked_in_range)
			{
				if (min(strtotime($closed_start), strtotime($booked_start)) === strtotime($closed_start))
				{
					$start = $closed_start;
				}
				else
				{
					$start = $booked_start;
				}
			}
			else if ($closed_in_range && ! $booked_in_range)
			{
				$start = $closed_start;
			}
			else if (! $closed_in_range && $booked_in_range)
			{
				$start = $booked_start;
			}
			else
			{
				$start = FALSE;
			}
			
			// Any booked/closed dates to account for?
			if (!$start)
			{
				// No closed/booked dates within 2 weeks
				// Any public holidays?
				if ($holiday_in_range)
				{
					$diff = $this->date_difference($start_date, $holiday_date);
					
					if ($day === "Monday")
					{
						if ($diff == 7)
						{
							$duration = array(
								array(4, $this->duration_calc($ppn, 4, $additional_cost), $selected_start_date, date('D jS M', $start_date_ts + (4 * $one_day))),
								array(8, $this->duration_calc($ppn, 8, $additional_cost, 5), $selected_start_date, date('D jS M', $start_date_ts + (8 * $one_day))),
								array(11, $this->duration_calc($ppn, 11, $additional_cost, 10), $selected_start_date, date('D jS M', $start_date_ts + (11 * $one_day))),
								array(14, $this->duration_calc($ppn, 14, $additional_cost, 15), $selected_start_date, date('D jS M', $start_date_ts + (14 * $one_day)))
							);
						} 
						else
						{
							$duration = array(
								array(4, $this->duration_calc($ppn, 4, $additional_cost), $selected_start_date, date('D jS M', $start_date_ts + (4 * $one_day))),
								array(7, $this->duration_calc($ppn, 7, $additional_cost, 5), $selected_start_date, date('D jS M', $start_date_ts + (7 * $one_day))),
								array(11, $this->duration_calc($ppn, 11, $additional_cost, 10), $selected_start_date, date('D jS M', $start_date_ts + (11 * $one_day))),
								array(15, $this->duration_calc($ppn, 15, $additional_cost, 15), $selected_start_date, date('D jS M', $start_date_ts + (15 * $one_day)))
							);
						}
					}
					else if ($day === "Tuesday")
					{
						$duration = array(
							array(3, $this->duration_calc($ppn, 3, $additional_cost), $selected_start_date, date('D jS M', $start_date_ts + (3 * $one_day))),
							array(6, $this->duration_calc($ppn, 6, $additional_cost), $selected_start_date, date('D jS M', $start_date_ts + (6 * $one_day))),
							array(10, $this->duration_calc($ppn, 10, $additional_cost, 10), $selected_start_date, date('D jS M', $start_date_ts + (10 * $one_day))),
							array(13, $this->duration_calc($ppn, 13, $additional_cost, 15), $selected_start_date, date('D jS M', $start_date_ts + (13 * $one_day)))
						);
					}
					else
					{
						if ($diff == 3)
						{
							$duration = array(
								array(4, $this->duration_calc($ppn, 4, $additional_cost), $selected_start_date, date('D jS M', $start_date_ts + (4 * $one_day))),
								array(7, $this->duration_calc($ppn, 7, $additional_cost, 5), $selected_start_date, date('D jS M', $start_date_ts + (7 * $one_day))),
								array(10, $this->duration_calc($ppn, 10, $additional_cost, 10), $selected_start_date, date('D jS M', $start_date_ts + (10 * $one_day))),
								array(14, $this->duration_calc($ppn, 14, $additional_cost, 15), $selected_start_date, date('D jS M', $start_date_ts + (14 * $one_day)))
							);
						}
						else
						{
							$duration = array(
								array(3, (ceil((3 * $ppn) / 5) * 5) + (3 * $additional_cost), $selected_start_date, date('D jS M', $start_date_ts + (3 * $one_day))),
								array(7, $this->duration_calc($ppn, 7, $additional_cost, 5), $selected_start_date, date('D jS M', $start_date_ts + (7 * $one_day))),
								array(11, $this->duration_calc($ppn, 11, $additional_cost, 10), $selected_start_date, date('D jS M', $start_date_ts + (11 * $one_day))),
								array(14, $this->duration_calc($ppn, 14, $additional_cost, 15), $selected_start_date, date('D jS M', $start_date_ts + (14 * $one_day)))
							);
						}
					}
				}
				else
				{
					if ($day === "Monday")
					{
						$duration = array(
							array(4, $this->duration_calc($ppn, 4, $additional_cost), $selected_start_date,  date('D jS M', $start_date_ts + (4 * $one_day))),
							array(7, $this->duration_calc($ppn, 7, $additional_cost, 5), $selected_start_date, date('D jS M', $start_date_ts + (7 * $one_day))),
							array(11, $this->duration_calc($ppn, 11, $additional_cost, 10), $selected_start_date, date('D jS M', $start_date_ts + (11 * $one_day))),
							array(14, $this->duration_calc($ppn, 14, $additional_cost, 15), $selected_start_date, date('D jS M', $start_date_ts + (14 * $one_day)))						
						);
					}
					else if ($day === "Tuesday")
					{
						$duration = array(
							array(3, $this->duration_calc($ppn, 3, $additional_cost), $selected_start_date, date('D jS M', $start_date_ts + (3 * $one_day))),
							array(6, $this->duration_calc($ppn, 6, $additional_cost), $selected_start_date, date('D jS M', $start_date_ts + (6 * $one_day))),
							array(10, $this->duration_calc($ppn, 10, $additional_cost, 10), $selected_start_date, date('D jS M', $start_date_ts + (10 * $one_day))),
							array(13, $this->duration_calc($ppn, 13, $additional_cost, 15), $selected_start_date, date('D jS M', $start_date_ts + (13 * $one_day)))
						);
					}
					else
					{
						$duration = array(
							array(3, $this->duration_calc($ppn, 3, $additional_cost), $selected_start_date,  date('D jS M', $start_date_ts + (3 * $one_day))),
							array(7, $this->duration_calc($ppn, 7, $additional_cost, 5), $selected_start_date, date('D jS M', $start_date_ts + (7 * $one_day))),
							array(10, $this->duration_calc($ppn, 10, $additional_cost, 10), $selected_start_date, date('D jS M', $start_date_ts + (10 * $one_day))),
							array(14, $this->duration_calc($ppn, 14, $additional_cost, 15), $selected_start_date, date('D jS M', $start_date_ts + (14 * $one_day)))						
						);
					}
				}
			}
			else
			{
				// There are some closed/booked dates to deal with!
				// Difference between selected date and closed/booked date
				$diff = $this->date_difference($start_date, $start);
				
				// Any public holidays?
				if ($holiday_in_range)
				{
					// Difference between selected date and holiday date
					$hol_diff = $this->date_difference($start_date, $holiday_date);
					
					// Find out which is sooner, the public holiday, or the closed date
					// If it's the closed date then calculate all durations up to that date
					if (min($diff, $hol_diff) === $diff)
					{
						$duration = $this->get_complex_durations($diff, $selected_start_date, $start_date_ts, $one_day, $day, $ppn, $additional_cost);
					}
					else
					{
						// There is a public holiday before a closed date.
						// Calculate the durations to include the holiday, up to the closed date and no further						
						if ($day === "Monday")
						{
							if ($hol_diff == 7)
							{
								$duration = $this->get_closed_and_hol_durations(4, 8, 11, 14, $diff, $selected_start_date, $start_date_ts, $one_day, $ppn, $additional_cost);
							} 
							else
							{	
								$duration = $this->get_closed_and_hol_durations(4, 8, 11, 15, $diff, $selected_start_date, $start_date_ts, $one_day, $ppn, $additional_cost);						
							}
						}
						else if ($day === "Tuesday")
						{	
							$duration = $this->get_closed_and_hol_durations(3, 6, 10, 13, $diff, $selected_start_date, $start_date_ts, $one_day, $ppn, $additional_cost);
						}
						else
						{
							if ($hol_diff == 3)
							{
								$duration = $this->get_closed_and_hol_durations(4, 7, 10, 14, $diff, $selected_start_date, $start_date_ts, $one_day, $ppn, $additional_cost);
							}
							else
							{
								$duration = $this->get_closed_and_hol_durations(3, 7, 11, 14, $diff, $selected_start_date, $start_date_ts, $one_day, $ppn, $additional_cost);
							}						
						}
					}
				}
				else
				{
					$duration = $this->get_complex_durations($diff, $selected_start_date, $start_date_ts, $one_day, $day, $ppn, $additional_cost);
				}
			}
		}
		
		$data['durations'] = $duration; 
		
		echo json_encode($data);
	}
	
	
	// ---------------------------------------------------------------------------------------------------------------
	/**
	 * Get available durations & prices based on selected start_date for bunk barns
	 *
	 */
	public function get_bunk_barn_durations()
	{
		$accommodation_id = $this->input->post('id');
		$start_date = $this->input->post('start_date');
		
		// Get accommodation price per night (ppn)
		$ppn = $this->get_price_per_night($accommodation_id, $start_date);
		
		// Get all bookings up to 2 weeks after this start_date
		$start_date_ts = strtotime($start_date);
		$start_date = date('Y-m-d', $start_date_ts);
		$selected_start_date = date('D jS M', $start_date_ts);
		$day = date('l', $start_date_ts);
		$one_day = 60 * 60 * 24;
		
		$two_weeks_ahead = date('Y-m-d', $start_date_ts + (60 * 60 * 24 * 14));
		
		$duration = "";
		$bed_date_data = array();
		
		$this->load->model('booking_model');
		$accommodation_beds_query = $this->booking_model->get_total_beds($accommodation_id);
		$accommodation_row = $accommodation_beds_query->row();
		
		$query = $this->booking_model->get_bookings_within_two_weeks($accommodation_id, $start_date, $two_weeks_ahead);
		
		if ($query->num_rows() > 0)
		{
			foreach ($query->result() as $row)
			{
				$existing_start_date_ts = strtotime($row->start_date);
				$existing_end_date_ts = strtotime($row->end_date);
				$current_date = $existing_start_date_ts;
				
				while ($current_date <= $existing_end_date_ts)
				{  
			    	$total_beds = $accommodation_row->sleeps;
			  
					$bed_date_data = $this->guests_per_day($accommodation_id, $current_date, $bed_date_data, $total_beds);
					
					// Add a day to the current date  
			    	$current_date = $current_date + (60 * 60 * 24);   
				}
			}	
		}
		
		// Array = (nights, price, start_date, end_date)
		$duration = array();
		
		// Calculate difference in days between selected start date and closest date in $bed_date_data
		// that has 0 beds remaining.
		if (in_array(0, $bed_date_data))
		{
			// Sort array so closest date is first.
			ksort($bed_date_data);
			
			$closest_date_ts = array_search(0, $bed_date_data);
			
			$diff = $this->date_difference($start_date, date('d-m-Y', $closest_date_ts));
			$calculated_end_date = date('D jS M', $closest_date_ts);
			
			// Add duration option to array for every night up to difference calculated
			for ($i = 1; $i <= $diff; $i++)
			{
				$duration[] = array($i, (ceil(($i * $ppn) / 1) * 1), $selected_start_date, date('D jS M', $start_date_ts + ($i * $one_day)));
			}
		}
		else
		{
			// Add up to 2 weeks worth of dates to duration array
			for ($i = 1; $i <= 14; $i++)
			{
				$duration[] = array($i, (ceil(($i * $ppn) / 1) * 1), $selected_start_date, date('D jS M', $start_date_ts + ($i * $one_day)));
			}
		}
		
		$data['durations'] = $duration;
		
		echo json_encode($data);
	}
	
	
	// --------------------------------------------------------------------------------------------------------------
	/**
	 * Complex Durations calculations
	 *
	 */
	function get_complex_durations($diff, $selected_start_date, $start_date_ts, $one_day, $day, $ppn, $additional_cost)
	{
		if ($diff < 7)
		{
			$duration[] = array($diff, $this->duration_calc($ppn, $diff, $additional_cost), $selected_start_date, date('D jS M', $start_date_ts + ($diff * $one_day)));
		} 
		else if ($diff >= 7)
		{
			if ($day === 'Monday')
			{
				$duration[] = array(4, $this->duration_calc($ppn, 4, $additional_cost), $selected_start_date, date('D jS M', $start_date_ts + (4 * $one_day)));
			}
			else
			{
				$duration[] = array(3, $this->duration_calc($ppn, 3, $additional_cost), $selected_start_date, date('D jS M', $start_date_ts + (3 * $one_day)));
			}
		}

		if ($diff == 6)
		{
			$duration[] = array(6, $this->duration_calc($ppn, 6, $additional_cost), $selected_start_date, date('D jS M', $start_date_ts + (6 * $one_day)));
		}
		else if ($diff == 7)
		{				
			$duration[] = array(7, $this->duration_calc($ppn, 7, $additional_cost, 5), $selected_start_date, date('D jS M', $start_date_ts + (7 * $one_day)));
		}
		else if ($diff == 10)
		{		
			if ($day === 'Monday')
			{		
				$duration[] = array(7, $this->duration_calc($ppn, 7, $additional_cost, 5), $selected_start_date, date('D jS M', $start_date_ts + (7 * $one_day)));
			}
			else
			{
				$duration[] = array(6, $this->duration_calc($ppn, 6, $additional_cost), $selected_start_date, date('D jS M', $start_date_ts + (6 * $one_day)));
			}
			
			$duration[] = array(10, $this->duration_calc($ppn, 10, $additional_cost, 10), $selected_start_date, date('D jS M', $start_date_ts + (10 * $one_day)));
		}
		else if ($diff == 11)
		{
			$duration[] = array(7, $this->duration_calc($ppn, 7, $additional_cost, 5), $selected_start_date, date('D jS M', $start_date_ts + (7 * $one_day)));
			$duration[] = array(11, $this->duration_calc($ppn, 11, $additional_cost, 10), $selected_start_date, date('D jS M', $start_date_ts + (11 * $one_day)));
		}
		else if ($diff == 13)
		{
			$duration[] = array(6, $this->duration_calc($ppn, 6, $additional_cost), $selected_start_date, date('D jS M', $start_date_ts + (6 * $one_day)));
			$duration[] = array(13, $this->duration_calc($ppn, 13, $additional_cost, 15), $selected_start_date, date('D jS M', $start_date_ts + (13 * $one_day)));
		}
		else if ($diff == 14)
		{
			$duration[] = array(7, $this->duration_calc($ppn, 7, $additional_cost, 5), $selected_start_date, date('D jS M', $start_date_ts + (7 * $one_day)));
			
			if ($day === 'Monday')
			{
				$duration[] = array(11, $this->duration_calc($ppn, 11, $additional_cost, 10), $selected_start_date, date('D jS M', $start_date_ts + (11 * $one_day)));
			}
			else
			{
				$duration[] = array(10, $this->duration_calc($ppn, 10, $additional_cost, 10), $selected_start_date, date('D jS M', $start_date_ts + (10 * $one_day)));
			}
			
			$duration[] = array(14, $this->duration_calc($ppn, 14, $additional_cost, 15), $selected_start_date, date('D jS M', $start_date_ts + (14 * $one_day)));
		}
		
		return $duration;
	}
	
	
	// --------------------------------------------------------------------------------------------------------------
	/**
	 * Get durations when there's public holidays and closed dates to deal with
	 *
	 */
	function get_closed_and_hol_durations($range1, $range2, $range3, $range4, $diff, $selected_start_date, $start_date_ts, $one_day, $ppn, $additional_cost)
	{
		if ($diff >= $range1)
		{
			$duration[] = array($range1, $this->duration_calc($ppn, $range1, $additional_cost), $selected_start_date, date('D jS M', $start_date_ts + ($range1 * $one_day)));
			
			if ($diff > $range2)
			{
				$duration[] = array($range2, $this->duration_calc($ppn, $range2, $additional_cost), $selected_start_date, date('D jS M', $start_date_ts + ($range2 * $one_day)));
				
				if ($diff >= $range3)
				{
					$duration[] = array($range3, $this->duration_calc($ppn, $range3, $additional_cost), $selected_start_date, date('D jS M', $start_date_ts + ($range3 * $one_day)));
					
					if ($diff >= $range4)
					{
						$duration[] = array($range4, $this->duration_calc($ppn, $range4, $additional_cost), $selected_start_date, date('D jS M', $start_date_ts + ($range4 * $one_day)));
					}		
				}
			}
		}
		
		return $duration;
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
										
					$price = $price - (($price / 100) * $percentage_decrease);
				}
			}
		}
		
		return $price;
	}
	
	
	
	// ---------------------------------------------------------------------------------------------------------------
	/**
	 * Get Additional price for accommodation
	 *
	 */
	function get_additional_accommodation_cost($accommodation_id)
	{
		$this->load->model('booking_model');
		
		$cost_query = $this->booking_model->get_additional_cost($accommodation_id);
		if ($cost_query->num_rows() > 0)
		{
			$cost_row = $cost_query->row();
			$cost = $cost_row->additional_per_night_charge;
		}
		
		return $cost;
	}
	
	// ---------------------------------------------------------------------------------------------------------------
	/**
	 * Get Additional price for accommodation
	 *
	 */
	function duration_calc($ppn, $duration, $additional_cost, $percentage_discount = 0)
	{
		$price = (ceil(($duration * $ppn) / 5) * 5) + ($duration * $additional_cost);
	
		if ($percentage_discount > 0)
		{
			$price = ceil(($price - (($price / 100) * $percentage_discount)) / 5) * 5;
		}
		
		return $price;
	}
	
	
	// ---------------------------------------------------------------------------------------------------------------
	/**
	 * Get all public holidays from db. Return array of start and end dates
	 *
	 */
	function get_public_holidays()
	{
		echo json_encode($this->public_holidays());
	}
	
	
	// ---------------------------------------------------------------------------------------------------------------
	/**
	 * Get all public holidays from db. Return array of start and end dates
	 *
	 */
	function public_holidays()
	{
		$this->load->model('booking_model');
		$query = $this->booking_model->get_public_holidays();
		$public_holidays = array();
		
		if ($query->num_rows() > 0)
		{
			foreach($query->result() as $row)
			{
				$public_holidays[] = array(
					'start_date'	=> date('d-m-Y', strtotime($row->start_date)),
					'end_date'		=> date('d-m-Y', strtotime($row->end_date))
				);
			}
		}
		
		return $public_holidays;
	}
	
	
	// ---------------------------------------------------------------------------------------------------------------
	/**
	 * Get all closed dates
	 *
	 */
	function closed_dates($start_date_ts = 0)
	{
		$this->load->model('site_closed_model');
		
		if ($start_date_ts > 0)
		{
			$query = $this->site_closed_model->get_all($start_date_ts);
		}
		else
		{
			$query = $this->site_closed_model->get_all();
		}
		
		$closed_dates = array();
		
		if ($query->num_rows() > 0)
		{
			foreach($query->result() as $row)
			{
				$closed_dates[] = array(
					'start_date'	=> date('d-m-Y', strtotime($row->start_date)),
					'end_date'		=> date('d-m-Y', strtotime($row->end_date))
				);
			}
		}
		
		return $closed_dates;
	}

	
	// ---------------------------------------------------------------------------------------------------------------
	/**
	 * Get all closed dates
	 *
	 */
	function get_full_site_availability()
	{
		$this->load->model('accommodation_model');
		
		// Get all calendar entries
		$query = $this->accommodation_model->get_calendar();
		$unavailable_dates = array();
		
		if ($query->num_rows() > 0)
		{
			foreach($query->result() as $row)
			{
				// For every date between calendar start and end dates
				// add to unavailable_dates array
				$end_date_ts = strtotime($row->end_date);
				$current_date = strtotime($row->start_date);
				
				while ($current_date <= $end_date_ts)
				{  
					$unavailable_dates[] = date('d-m-Y', $current_date);
					
					// Add a day to the current date  
			    	$current_date = $current_date + (60 * 60 * 24); 
				}  
			}
		}
		
		echo json_encode($unavailable_dates);
	}
	
	
	// ---------------------------------------------------------------------------------------------------------------
	/**
	 * Get the difference in days between two dates
	 *
	 */
	function date_difference($start, $end)
	{
		$start = strtotime($start);
		$end = strtotime($end);
		
		$diff = floor(($end - $start)/(60 * 60 * 24));
		
		return $diff;
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


	// ---------------------------------------------------------------------------------------------------------------
	/**
	 * Accomodation - Start Booking Form
	 *
	 */
	function accommodation_booking_form()
	{
		$this->load->library('form_validation');
		
		$this->form_validation->set_rules('site_id', 'Site', 'trim|numeric');
		$this->form_validation->set_rules('accommodation_ids', 'Accommodation', 'trim|numeric');
		$this->form_validation->set_rules('start_date', 'Start Date', 'trim|required');
		$this->form_validation->set_rules('duration', 'Duration', 'trim|numeric|required');
		$this->form_validation->set_rules('adults', 'Adults', 'trim|numeric|required');
		$this->form_validation->set_rules('children', 'Children', 'trim|numeric');
		$this->form_validation->set_rules('total_price', 'Total Price', 'trim|numeric');
	}
	
}

/* End of file accommodation.php */
/* Location: ./application/controllers/accommodation.php */