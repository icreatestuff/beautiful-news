<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Results extends CI_Controller {

	/**
	 * Index Page for this controller.
	 *
	 */
	public function index()
	{	
		// Get data from quick booking form
		// Form validates so process booking and move on to extras page
		foreach ($_POST as $key => $val)
		{
			$data[$key] = $this->input->post($key);			
		}
	
		if (!isset($data['start_date']))
		{
			redirect('booking.thebivouac.co.uk');
		}
	
		$data['accommodation_list'] = $this->get_available_accommodation($data['accommodation_type'], $data['dogs']);
	
		$this->load->view('results/index', $data);
	}
	
	public function process()
	{
		$this->results_form();
		
		if ($this->form_validation->run() == FALSE)
		{
			$this->index();
		}
		else
		{
			// Form validates so process booking and move on to extras page
			foreach ($_POST as $key => $val)
			{
				$data[$key] = $this->input->post($key);
			}
				
			$start_date_ts = strtotime($data['start_date']);
			$data['start_date'] = date('Y-m-d H:i:s', $start_date_ts);
			
			$end_date_ts = $start_date_ts + (60 * 60 * 24 * $data['duration']);
			$data['end_date'] = date('Y-m-d H:i:s', $end_date_ts);	
				
			$accommodation_ids = $data['accommodation_ids'];
			
			// Filter accommodation_ids array to remove blanks
			$accommodation_ids = array_filter($accommodation_ids);
			
			$data['accommodation_ids'] = implode("|", $accommodation_ids);
			
			$price = 0;
			
			$this->load->model('booking_model');
			
			// Calculate total_price and if there are any bunk barn guests?
			foreach ($accommodation_ids as $id)
			{
				if (!empty($id))
				{
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
								// Find all data keys that start with required_beds_X 
								// and get the name of accommodation with id of X.
								// Compare against queried accommodation
								if (isset($data['required_beds_' . $id]))
								{
									// Is this the large or small bunk barn? 11 = large, 21 = small
									if ($id == 11)
									{
										// Is this part of a multi-accommodation booking
										// We'll know if the value is greater than 0 for the remaining_beds key
										if ($data['required_beds_' . $id] > 0)
										{
											$large_bunk_barn_guests = $data['required_beds_' . $id];
										}
										else
										{
											// Add up all adults and children
											$large_bunk_barn_guests = (int) $data['adults'] + (int) $data['children'];
										}
										
										$accom_price = ceil(($ppn * $data['duration']) / 1) * 1;
										
										$price = $price + ($accom_price * $large_bunk_barn_guests);
									}
									else
									{
										// Is this part of a multi-accommodation booking
										// We'll know if the value is greater than 0 for the remaining_beds key
										if ($data['required_beds_' . $id] > 0)
										{
											$small_bunk_barn_guests = $data['required_beds_' . $id];
										}
										else
										{
											// Add up all adults and children
											$small_bunk_barn_guests = (int) $data['adults'] + (int) $data['children'];
										}
										
										$accom_price = ceil(($ppn * $data['duration']) / 1) * 1;
										
										$price = $price + ($accom_price * $small_bunk_barn_guests);
									}	
									
									// Unset the required_beds key as we now don't need it
									unset($data['required_beds_' . $id]);
								}
							}
							else
							{
								$accom_price = ceil(($ppn * $data['duration']) / 5) * 5;
							
								$price = $price + ($accom_price + ($accommodation_row->additional_per_night_charge * $data['duration']));
								
								// If the duration is 7 days or more they get a discount!
								if ($data['duration'] == 7)
								{
									// 5% discount
									$price = round($price - (($price / 100) * 5));
					
									// Round to nearest 5
									$price = ceil($price / 5) * 5;
								}
								else if ($data['duration'] == 10 || $data['duration'] == 11)
								{
									// 10% discount
									$price = round($price - (($price / 100) * 10));
					
									// Round to nearest 5
									$price = ceil($price / 5) * 5;
								}
								else if ($data['duration'] == 14)
								{
									// 15% discount
									$price = round($price - (($price / 100) * 15));
					
									// Round to nearest 5
									$price = ceil($price / 5) * 5;
								}
							}
						}	
					}
					
					// Are there any offers with this accommodation_id that might need closing?
					$offers = $this->booking_model->get_offer_by_accommodation($id);
					
					if ($offers->num_rows() > 0)
					{
						foreach ($offers->result() as $offer)
						{
							// Use offer start day + 1 so we don't close offers whose start_date is the end_date of a different booking
							$offer_date = date('Y-m-d H:i:s', (strtotime($offer->start_date) + (60 * 60 * 24)));
			
							if ($this->check_in_range($data['start_date'], $data['end_date'], $offer_date))
							{
								// Close this offer
								$this->booking_model->close_offer($offer->id);
							}	
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
					$price = $price + $dogs_price;
					
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
			
			$data['total_price'] = $price;
			
			// Check that the required_beds keys have been removed by now
			foreach ($data as $key => $val)
			{
				if (substr($key, 0, 14) === "required_beds_")
				{
					unset($data[$key]);
				}
			}			
						
			// Add payment status and calculate end_date from start_date + duration
			$data['payment_status'] = "Unpaid";
			
			// Set creation date to now
			$data['booking_creation_date'] = date('Y-m-d H:i:s');
			
			// Generate Booking Reference number FORMAT: ddmmyy_UNITID_#GUESTS
			// We need the unit Code for this accommodation
			$this->load->model('accommodation_model');
			
			// Get the first none blank accommodation_id so we can
			// build the booking ref
			foreach ($accommodation_ids as $id)
			{
				if (!empty($id))
				{
					$primary_id = $id;	
					break;
				}
			}
			
			$unit_query = $this->accommodation_model->get_unit_code($primary_id);
			$unit_row = $unit_query->row();
			
			$data['booking_ref'] = date('dmY', $start_date_ts) . "_" . $unit_row->unit_id . "_" . ((int) $data['adults'] + (int) $data['children']);		
		
			// Unset unused elements
			unset($data['results_submit']);	
			unset($data['duration']);
			unset($data['multiple-units']);
			
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
			unset($data['accommodation_ids']);
			unset($data['booking_creation_date']);
			
			foreach ($accommodation_ids as $id)
			{
				$data['accommodation_id'] = $id;
				$data['bunk_barn_guests'] = 0;
				$data['small_bunk_barn_guests'] = 0;
				
				// If id = 11, then add large bunk barn guests
				// Else if id = 21 then add small bunk barn guests to data
				
				if ($id == 11)
				{
					$data['bunk_barn_guests'] = $large_bunk_barn_guests;
				}
				else if ($id == 21)
				{
					$data['small_bunk_barn_guests'] = $small_bunk_barn_guests;
				}
			
				$this->booking_model->insert_calendar_row($data);
			}
			
			$this->load->library('session');
			$this->session->set_userdata('booking_id', $key_booking_data['booking_id']);
			
			redirect('booking/extras/' . $key_booking_data['booking_id']);
		}
	}
	
	
	// ---------------------------------------------------------------------------------------------------------------
	/**
	 * Get available accommodation from start_date and duration
	 *
	 */
	public function get_available_accommodation($type = FALSE, $dogs)
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
		$calendar_query = $this->booking_model->get_calendar_dates();
		
		
		// If there are dogs to process 
		if ((int) $dogs > 0)
		{
			// Get all accommodation that allow dogs
			$accommodation_query = $this->booking_model->get_all_dog_accommodation($type); // May need to modify this for a specific site in future
		}
		else
		{
			// Get all accommodation
			$accommodation_query = $this->booking_model->get_all_accommodation($type); // May need to modify this for a specific site in future
		}
	
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
					// Check if booking start_date is between calendar start_date and end_date
					if ($this->check_in_range($calendar->start_date, $calendar->end_date, $start_date))
					{
						// It is between calendar dates so remove this accom id in a minute
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
			}
			
			// Now all unavailable accommodation has been filtered out lets create the list
			if (count($all_accommodation) > 0)
			{
				$accommodation_list .= $this->create_accommodation_list($all_accommodation, $accommodation_list, $start_date, $post_duration, $type);
			}
			else
			{
				$accommodation_list .= "<li class='clearfix type-header'><h2>Sorry there are is no accommodation available for the dates and duration selected. <a href='https://booking.thebivouac.co.uk'>Please search again </a></h2></li>";
			}
		}
		else
		{
			// No bookings made in that timeframe so return all accommodation
			if ($accommodation_query->num_rows() > 0)
			{
				$accommodation_list .= $this->create_accommodation_list($accommodation_query->result(), $accommodation_list, $start_date, $post_duration, $type);
			}
			else
			{
				$accommodation_list .= "<li class='clearfix type-header'><h2>Sorry there is no accommodation available for the dates and duration selected. <a href='https://booking.thebivouac.co.uk'>Please search again </a></h2></li>";
			}
		}

		return $accommodation_list;
	}
	
	
	function create_accommodation_list($all_accommodation, $accommodation_list, $start_date, $duration, $type)
	{
	
		// Get full list of all accommodation_types
		$all_accommodation_types = $this->booking_model->get_all_accommodation_types();
		
		if ($all_accommodation_types->num_rows() > 0)
		{
			$all_types = $all_accommodation_types->result_array();
		}
		
		// If we're looking for a specific type then let's remove all other types from list
		if ($type)
		{
			foreach ($all_types as $key => $row)
			{
				if ($type !== $row['id'])
				{
					unset($all_types[$key]);
				}
			}
		}

		$type_header = "";
		
		foreach ($all_accommodation as $accommodation)
		{
			// Calculate price for this accommodation for this timeframe
			$ppn = $this->get_price_per_night($accommodation->id, $start_date);
			
			if ($accommodation->type_name == "Bunk Barn")
			{
				$accom_price = ceil(($ppn * $duration) / 1) * 1;
			}
			else
			{
				$accom_price = ceil(($ppn * $duration) / 5) * 5;
			}
			
			$price = $accom_price + ($accommodation->additional_per_night_charge * $duration);
			
			if ($accommodation->type_name != "Bunk Barn")
			{
				// If the duration is 7 days or more they get a discount!
				if ($duration == 7)
				{
					// 5% discount
					$price = round($price - (($price / 100) * 5));
	
					// Round to nearest 5
					$price = ceil($price / 5) * 5;
				}
				else if ($duration == 10 || $duration == 11)
				{
					// 10% discount
					$price = round($price - (($price / 100) * 10));
	
					// Round to nearest 5
					$price = ceil($price / 5) * 5;
				}
				else if ($duration == 13 || $duration == 14)
				{
					// 15% discount
					$price = round($price - (($price / 100) * 15));
	
					// Round to nearest 5
					$price = ceil($price / 5) * 5;
				}
			}
		
			// Is it a bunk barn?
			$bunk_barn_extra_type = "";
			
			if ($accommodation->type_name == "Bunk Barn")
			{
				$bunk_barn_extra_type = "per person";
				$bed_date_data = array();
				$total_beds = $accommodation->sleeps;
				$end_date_ts = strtotime($start_date) + (60 * 60 * 24 * $duration);
				$current_date = strtotime($start_date); 
				
				// Go through each day of the holiday and get the beds available
				while ($current_date <= $end_date_ts)
				{  
					$bed_date_data = $this->guests_per_day($accommodation->id, $current_date, $bed_date_data, $total_beds);
					
					// Add a day to the current date  
    				$current_date = $current_date + (60 * 60 * 24); 
				}
				
				// Search the bed data array for lowest number of beds across holiday.
				if (count($bed_date_data) > 0)
				{
					$remaining_beds = min($bed_date_data);	
				}				
				else
				{
					$remaining_beds = $total_beds;
				}
			}
			
			// Manage headers
			foreach ($all_types as $key => $row)
			{
				if ($row['name'] === "Camping Pitch" || $row['name'] === "Family Lodge")
				{
					break;
				}
				
				if ($accommodation->type_name === $row['name'])
				{
					// Set up headers						
					if ($accommodation->type_name !== $type_header) 
					{
						$type_header = $accommodation->type_name;
						$accommodation_list .= "<li class='clearfix type-header' data-type='" . $type_header . "'><h1>" . $type_header . "s</h1></li>";
						unset($all_types[$key]);
						break;
					}
				}				
			}
		
			if ($accommodation->type_name == "Bunk Barn")
			{
				$accommodation_list .= "<li class='clearfix' data-id='" . $accommodation->id . "' data-price='" . $price . "' data-sleeps='" . $remaining_beds . "' data-type='" . $accommodation->type_name . "'>";
			}
			else
			{
				$accommodation_list .= "<li class='clearfix' data-id='" . $accommodation->id . "' data-price='" . $price . "' data-sleeps='" . $accommodation->sleeps . "' data-type='" . $accommodation->type_name . "'>";
			}
			
			$accommodation_list .= "<h2>" . $accommodation->name . " &mdash; This would cost <b>&pound;" . $price . "</b> for " . $duration . " nights " . $bunk_barn_extra_type . "</h2>";
			$accommodation_list .= "<div class='accommodation-info'><img src='" . base_url() . "images/accommodation/" . $accommodation->photo_1 . "' width='140' alt='" . $accommodation->name . " main photo' />";
			$accommodation_list .= "<p class='accommodation-description'>" . $accommodation->description ."<br /><br />";
			
			if ($accommodation->type_name == "Bunk Barn")
			{
				$accommodation_list .= "Beds Available: " . $remaining_beds . "<br /><a href='#' class='lightbox-full-accommodation' title='View full accommodation detail'>Read more about this accommodation</a></p>";
				
				$options = "";
				for ($i=0; $i<=$remaining_beds; $i++)
				{
					if ($i == 1)
					{
						$options .= "<option value='" . $i . "'>Book " . $i . " bed</option>";
					}
					else
					{
						$options .= "<option value='" . $i . "'>Book " . $i . " beds</option>";
					}
				}
				
				$accommodation_list .= "<div class='accommodation-beds-container'><input type='hidden' name='accommodation_ids[]' value='' class='hidden_accommodation_id' /><h3>How many beds do you need</h3><select name='required_beds_" . $accommodation->id . "' class='bunk_barn_bed_select'>" . $options . "</select></div><a href='#' class='quick-book-link'>Book '" . $accommodation->name . "'</a><a href='#' class='need-multiple-units'>The total number of guests exceeds the maximum occupancy of this unit. Click here to book multiple units</a></div></li>";
			}
			else
			{
				$accommodation_list .= "Sleeps: " . $accommodation->sleeps . "<br /><a href='#' class='lightbox-full-accommodation' title='View full accommodation detail'>Read more about this accommodation</a></p>";
				$accommodation_list .= "<div class='accommodation-checkbox-container'><input type='checkbox' name='accommodation_ids[]' value='" . $accommodation->id . "' class='accommodation-checkbox' /> <h3>Add this accommodation</h3></div><a href='#' class='quick-book-link'>Book '" . $accommodation->name . "'</a><a href='#' class='need-multiple-units'>The total number of guests exceeds the maximum occupancy of this unit. Click here to book multiple units</a></div></li>";
			}
		}
		
		// Process any remaining $all_types, these should have no accommodation results
		foreach ($all_types as $row)
		{
			if ($row['name'] === "Camping Pitch" || $row['name'] === "Family Lodge")
			{
				break;
			}
		
			$accommodation_list .= "<li class='clearfix type-header' data-type='" . $row['name'] . "'><h1>" . $row['name'] . "s</h1></li>";
			$accommodation_list .= "<li class='clearfix type-header'><h2>Sorry there is no " . $row['name'] . "s available for the dates and duration selected.</h2></li>";
		}
		
		return $accommodation_list;
	}

	

	
	// --------------------------------------------------------------------------------------------------------------
	/**
	 * Booking Index Form.
	 *
	 */
	function results_form()
	{
		$this->load->library('form_validation');
		
		//$this->form_validation->set_rules('accommodation_ids[]', 'Accommodation', 'trim|required');
		$this->form_validation->set_rules('total_price', 'Total Price', 'trim|numeric');
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
}

/* End of file results.php */
/* Location: ./application/controllers/results.php */