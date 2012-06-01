<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Booking extends CI_Controller {

	/**
	 * Index Page for this controller.
	 *
	 */
	public function index()
	{	
		$this->booking_index_form();
		
		if ($this->form_validation->run() == FALSE)
		{
			$admin = $this->session->userdata('is_admin');
			$is_logged_in = $this->session->userdata('is_logged_in');
			$data['is_admin'] = FALSE;
			
			if (isset($is_logged_in) && $is_logged_in === TRUE)
			{
				if (isset($admin) && $admin === "y")
				{
					$data['is_admin'] = TRUE;
				}
			}
		
			$this->load->view('booking/index', $data);
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
									// 5% discount
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
			unset($data['booking_submit']);	
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
	
	
	// --------------------------------------------------------------------------------------------------------------
	/**
	 * Extras choice page.
	 *
	 */
	public function extras()
	{
		if ($this->uri->segment(3) === FALSE || !is_numeric($this->uri->segment(3)))
		{
			redirect('booking/index');
		}
		else
		{
			// Check if session data booking_id equals segment 3
			$booking_id = $this->uri->segment(3);
			
			if ($this->session->userdata('booking_id') != $booking_id)
			{
				redirect('booking/index');
			}
			else
			{				
				//Manage Contact Page
				$this->extras_form();

				if ($this->form_validation->run() == FALSE)
				{	
					$data = array();
					$data['booking_id'] = $booking_id;
				
					// Get site_id from booking data
					$this->load->model('booking_model');
					$booking_query = $this->booking_model->get_booking($booking_id);
					$booking_row = $booking_query->row();
					
					$current_date = strtotime($booking_row->start_date);
					$end_date_ts = strtotime($booking_row->end_date);
					 
					while ($current_date < $end_date_ts)
					{  
						// Add this new day to the dates array  
						$dates[] = date('d-m-Y', $current_date);
						
						// Add a day to the current date  
				    	$current_date = $current_date + (60 * 60 * 24); 
					} 
					
					$data['total_price'] = $booking_row->total_price;
					$data['duration'] = $this->date_difference($booking_row->start_date, $booking_row->end_date);
					$data['babies'] = $booking_row->babies;
					
					// Get all extra types	
					$extra_types_query = $this->booking_model->get_extra_types();
					
					if ($extra_types_query->num_rows() > 0)
					{
						foreach ($extra_types_query->result() as $extra_type)
						{
							// If there are no babies we need to remove the baby provision (Id 5) extras
							if ($data['babies'] == 0 && $extra_type->id == 5)
							{
								continue;
							}
							
							// Get all extras with that type assigned
							$extras_query = $this->booking_model->get_extras($extra_type->id, $booking_row->site_id);
							
							if ($extras_query->num_rows() > 0)
							{
								// If extra_type->id == 2 we need to see if any dogs have been purchased.
								if ($extra_type->id == 2)
								{
									$all_extras_of_this_type = $extras_query->result();
									
									foreach ($all_extras_of_this_type as $extra => $val)
									{										
										// If id = 14 (Dogs)
										if ($val->id == 14)
										{
											// Get all purchases that have this extra ID
											$purchased_query = $this->booking_model->purchased_extras_by_id($val->id, $booking_id);

											// Are dogs allowed at this accommodation?
											if ($purchased_query->num_rows() == 0)
											{
												unset($all_extras_of_this_type[$extra]);
											}
										}
									}
								}
								
								// If extra type = 4 then it's an event.
								// Find out how many event event spaces are still available.
								if ($extra_type->id == 4)
								{
									$all_extras_of_this_type = $extras_query->result();
									
									foreach ($all_extras_of_this_type as $extra => $val)
									{
										$number_available = (int) $val->number_available;
										
										// Get all purchases that have this extra ID
										$purchased_query = $this->booking_model->purchased_extras_by_id($val->id);
										
										// If the number of rows returned is < the total number_available of that extra then we can show this date as
										// an option the user can select.
										if ($purchased_query->num_rows() > 0)
										{
											foreach ($purchased_query->result() as $purchase)
											{
												$number_available = $number_available - (int) $purchase->quantity;
											}
										}
										
										if ($number_available <= 0)
										{
											unset($all_extras_of_this_type[$extra]);
										}
										else
										{
											// Add number_available to extras_query
											$all_extras_of_this_type[$extra]->number_still_available = $number_available;
										}
									}
								}
								
								// If this is a specific dates select extra (Id: 6)
								// Find out what dates are available still.
								if ($extra_type->id == 6)
								{
									$all_extras_of_this_type = $extras_query->result();
								
									foreach ($all_extras_of_this_type as $extra => $val)
									{
										$selectable_dates = array();
										
										// Loop through all holiday dates and see if this extra has been purchased for that date
										foreach ($dates as $date)
										{
											// Get all purchases that have this extra ID and date
											$purchased_query = $this->booking_model->purchased_extras_by_id_and_date($val->id, $date);
											
											// If the number of rows returned is < the total number_available 
											// of that extra then we can show this date as an option the user can select.
											print_r($purchased_query->result());
											
											if ($purchased_query->num_rows() < $val->number_available)
											{
												$selectable_dates[] = strtotime($date);
											}
										}
										
										// If selectable_dates has no data then this extra isn't available at all during
										// the customers holiday so remove it altogether from the extra_query
										if (count($selectable_dates) <= 0)
										{
											unset($all_extras_of_this_type[$extra]);
										}
										else
										{
											// Add selectable_dates array to extras_query
											$all_extras_of_this_type[$extra]->selectable_dates = $selectable_dates;
										}
										
										$all_extras_of_this_type[$extra]->selectable_dates = $selectable_dates;
									}				
								}
								
								
								if ($extra_type->id == 6 || $extra_type->id == 4 || $extra_type->id == 2)
								{
									// Create Extra Types -> Extras array items
									$data['extra_types'][] = array(
										'id'			=> $extra_type->id,
										'name'			=> $extra_type->name,
										'extras_num'	=> count($all_extras_of_this_type),
										'extras'		=> $all_extras_of_this_type
									);	
								}
								else
								{
									// Create Extra Types -> Extras array items
									$data['extra_types'][] = array(
										'id'			=> $extra_type->id,
										'name'			=> $extra_type->name,
										'extras_num'	=> $extras_query->num_rows(),
										'extras'		=> $extras_query
									);
								}
							}
						}
					}	
					
					// Get extras already selected
					$data['selected_extras'] = $this->booking_model->get_booked_extras($booking_id);
					
					// Calculate price of extras already selected
					$data['extras_price'] = 0;
					
					if ($data['selected_extras']->num_rows() > 0)
					{
						foreach ($data['selected_extras']->result() as $sExtra)
						{
							$data['extras_price'] = $data['extras_price'] + intval($sExtra->price);
						}
					}					
					
					$this->load->view('booking/extras', $data);
				}
				else
				{
					// Extras form validates! Update booking total price and put extras in purchased table
					// Process Extras form results
					// Coming from form booking_id, submit, extra_price_x, extra_quantity_x, extra_nights_x
					foreach ($_POST as $key => $val)
					{
						$data[$key] = $this->input->post($key);
					}
					
					unset($data['submit']);	
					
					// First of all lets delete all references to this booking in the purchased_extras table,
					// then just write all the new/amended extras to it.
					$this->load->model('booking_model');
					
					$this->booking_model->delete_purchased_extras($data['booking_id']);
					
					$this->booking_model->update_total_price($data['booking_id'], $data['total_price']);
					
					$insert_data = array();
					
					foreach ($data as $key => $val)
					{
						// Get extras from $data where quantity is 1 or more and add them to insert_data
						if (substr($key, 0, 15) === "extra_quantity_")
						{
							if ($val > 0)
							{
								$extra_id = substr($key, 15);
								
								// Set nights
								if (isset($data['extra_nights_' . $extra_id]))
								{
									$nights = $data['extra_nights_' . $extra_id];
								}
								else
								{
									$nights = 0;
								}
								
								// Calculate price
								if ($nights === 0)
								{
									$price = round($val * $data['extra_price_' . $extra_id], 2);
								}
								else
								{
									$price = round($val * $data['extra_nights_' . $extra_id] * $data['extra_price_' . $extra_id], 2);
								}
							
								$insert_data[] = array(
									'extra_id' 		=> $extra_id,
									'booking_id'	=> $data['booking_id'],
									'quantity'		=> $val,
									'nights'		=> $nights,
									'price'			=> $price,
									'date'			=> ""
								);
							}
						}
						
						// Get extras from $data where dates is present insert_data
						if (substr($key, 0, 11) === "extra_date_")
						{
							if (is_array($val))
							{
								$extra_id = substr($key, 11);
								
								foreach ($val as $date)
								{
									// Insert as unique entries in purchased_entries table
									$insert_data[] = array(
										'extra_id' 		=> $extra_id,
										'booking_id'	=> $data['booking_id'],
										'quantity'		=> 1,
										'nights'		=> 0,
										'price'			=> $data['extra_price_' . $extra_id],
										'date'			=> $date
									);
								}
							}
						}
					}
					
					// Only try to insert if there is some data!
					if (isset($insert_data) && !empty($insert_data))
					{
						$this->booking_model->insert_extra_purchases($insert_data);
					}
					
					redirect('booking/contact/' . $data['booking_id']);
				}
			}
		}		
	}
	
	
	// --------------------------------------------------------------------------------------------------------------
	/**
	 * Add/Edit Extras choice page.
	 *
	 */
	public function edit_extras()
	{
		if ($this->uri->segment(3) === FALSE || !is_numeric($this->uri->segment(3)))
		{
			redirect('booking/index');
		}
		else
		{
			// Check if session data booking_id equals segment 3
			$booking_id = $this->uri->segment(3);
			
			if ($this->session->userdata('booking_id') != $booking_id)
			{
				redirect('booking/index');
			}
			else
			{				
				//Manage Extras Page
				$this->extras_form();

				if ($this->form_validation->run() == FALSE)
				{	
					$data = array();
					$data['booking_id'] = $booking_id;
				
					// Get site_id from booking data
					$this->load->model('booking_model');
					$booking_query = $this->booking_model->get_booking($booking_id);
					$booking_row = $booking_query->row();
					
					$current_date = strtotime($booking_row->start_date);
					$end_date_ts = strtotime($booking_row->end_date);
					 
					while ($current_date < $end_date_ts)
					{  
						// Add this new day to the dates array  
						$dates[] = date('d-m-Y', $current_date);
						
						// Add a day to the current date  
				    	$current_date = $current_date + (60 * 60 * 24); 
					} 
					
					$data['total_price'] = $booking_row->total_price;
					$data['duration'] = $this->date_difference($booking_row->start_date, $booking_row->end_date);
					$data['babies'] = $booking_row->babies;
					
					// Get all extra types	
					$extra_types_query = $this->booking_model->get_extra_types();
					
					if ($extra_types_query->num_rows() > 0)
					{
						foreach ($extra_types_query->result() as $extra_type)
						{
							// If there are no babies we need to remove the baby provision (Id 5) extras
							if ($data['babies'] == 0 && $extra_type->id == 5)
							{
								continue;
							}
						
							// Get all extras with that type assigned
							$extras_query = $this->booking_model->get_extras($extra_type->id, $booking_row->site_id);
							
							if ($extras_query->num_rows() > 0)
							{
								// If extra_type->id == 2 we need to see if any dogs have been purchased.
								// If there are no dog purchases at this stage then don't show dog extras
								if ($extra_type->id == 2)
								{
									$all_extras_of_this_type = $extras_query->result();
									
									foreach ($all_extras_of_this_type as $extra => $val)
									{										
										// If id = 14 (Dogs)
										if ($val->id == 14)
										{
											// Get all purchases that have this extra ID
											$purchased_query = $this->booking_model->purchased_extras_by_id($val->id, $booking_id);

											// Are dogs allowed at this accommodation?
											if ($purchased_query->num_rows() == 0)
											{
												unset($all_extras_of_this_type[$extra]);
											}
										}
									}
								}
							
								// If extra type = 5then it's an event.
								// Find out how many event event spaces are still available.
								if ($extra_type->id == 4)
								{
									$all_extras_of_this_type = $extras_query->result();
									
									foreach ($all_extras_of_this_type as $extra => $val)
									{
										$number_available = (int) $val->number_available;
										
										// Get all purchases that have this extra ID
										$purchased_query = $this->booking_model->purchased_extras_by_id($val->id);
										
										// If the number of rows returned is < the total number_available of that extra then we can show this date as
										// an option the user can select.
										if ($purchased_query->num_rows() > 0)
										{
											foreach ($purchased_query->result() as $purchase)
											{
												$number_available = $number_available - (int) $purchase->quantity;
											}
										}
										
										if ($number_available <= 0)
										{
											unset($all_extras_of_this_type[$extra]);
										}
										else
										{
											// Add number_available to extras_query
											$all_extras_of_this_type[$extra]->number_still_available = $number_available;
										}
									}
								}
								
								// If this is a specific dates select extra (Id: 6)
								// Find out what dates are available still.
								if ($extra_type->id == 6)
								{
									$all_extras_of_this_type = $extras_query->result();
								
									foreach ($all_extras_of_this_type as $extra => $val)
									{
										$selectable_dates = array();
										
										// Loop through all holiday dates and see if this extra has been purchased for that date
										foreach ($dates as $date)
										{
											// Get all purchases that have this extra ID and date
											$purchased_query = $this->booking_model->purchased_extras_by_id_and_date($val->id, $date);
											
											// If the number of rows returned is < the total number_available of that extra then we can show this date as
											// an option the user can select.
											if ($purchased_query->num_rows() < $val->number_available)
											{
												$selectable_dates[] = strtotime($date);
											}
										}
										
										// If selectable_dates has no data then this extra isn't available at all during
										// the customers holiday so remove it altogether from the extra_query
										if (count($selectable_dates) <= 0)
										{
											unset($all_extras_of_this_type[$extra]);
										}
										else
										{
											// Add selectable_dates array to extras_query
											$all_extras_of_this_type[$extra]->selectable_dates = $selectable_dates;
										}
									}				
								}
								
								
								if ($extra_type->id == 6 || $extra_type->id == 4 || $extra_type->id == 2)
								{
									// Create Extra Types -> Extras array items
									$data['extra_types'][] = array(
										'id'			=> $extra_type->id,
										'name'			=> $extra_type->name,
										'extras_num'	=> count($all_extras_of_this_type),
										'extras'		=> $all_extras_of_this_type
									);	
								}
								else
								{
									// Create Extra Types -> Extras array items
									$data['extra_types'][] = array(
										'id'			=> $extra_type->id,
										'name'			=> $extra_type->name,
										'extras_num'	=> $extras_query->num_rows(),
										'extras'		=> $extras_query
									);
								}
							}
						}
					}	
					
					// Get extras already selected
					$data['selected_extras'] = $this->booking_model->get_booked_extras($booking_id);
					
					// Calculate price of extras already selected
					$data['extras_price'] = 0;
					
					if ($data['selected_extras']->num_rows() > 0)
					{
						foreach ($data['selected_extras']->result() as $sExtra)
						{
							$data['extras_price'] = $data['extras_price'] + intval($sExtra->price);
						}
					}	

					$this->load->view('booking/edit_extras', $data);
				}
				else
				{
					// Extras form validates! Update booking total price and put extras in purchased table
					// Process Extras form results
					// Coming from form booking_id, submit, extra_price_x, extra_quantity_x, extra_nights_x
					foreach ($_POST as $key => $val)
					{
						$data[$key] = $this->input->post($key);
					}
					
					unset($data['submit']);	
				
					// First of all lets delete all references to this booking in the purchased_extras table,
					// then just write all the new/amended extras to it.
					$this->load->model('booking_model');
					
					$this->booking_model->delete_purchased_extras($data['booking_id']);
					
					// Update booking total_price
					$this->booking_model->update_total_price($data['booking_id'], $data['total_price']);
					
					// Get extras from $data where quantity is 1 or more and add them to insert_data
					foreach ($data as $key => $val)
					{
						if (substr($key, 0, 15) === "extra_quantity_")
						{
							if ($val > 0)
							{
								$extra_id = substr($key, 15);
								
								// Set nights
								if (isset($data['extra_nights_' . $extra_id]))
								{
									$nights = $data['extra_nights_' . $extra_id];
								}
								else
								{
									$nights = 0;
								}
								
								// Calculate price
								if ($nights === 0)
								{
									$price = round($val * $data['extra_price_' . $extra_id], 2);
								}
								else
								{
									$price = round($val * $data['extra_nights_' . $extra_id] * $data['extra_price_' . $extra_id], 2);
								}
							
								$insert_data[] = array(
									'extra_id' 		=> $extra_id,
									'booking_id'	=> $data['booking_id'],
									'quantity'		=> $val,
									'nights'		=> $nights,
									'price'			=> $price,
									'date'			=> ""
								);
							}
						}
						
						// Get extras from $data where dates is present insert_data
						if (substr($key, 0, 11) === "extra_date_")
						{
							if (is_array($val))
							{
								$extra_id = substr($key, 11);
								
								foreach ($val as $date)
								{
									// Insert as unique entries in purchased_entries table
									$insert_data[] = array(
										'extra_id' 		=> $extra_id,
										'booking_id'	=> $data['booking_id'],
										'quantity'		=> 1,
										'nights'		=> 0,
										'price'			=> $data['extra_price_' . $extra_id],
										'date'			=> $date
									);
								}
							}
						}
					}
					
					// Only try to insert if there is some data!
					if (isset($insert_data) && !empty($insert_data))
					{
						$this->booking_model->insert_extra_purchases($insert_data);
					}
					
					redirect('booking/overview/' . $data['booking_id']);
				}
			}
		}		
	}
	
	
	// --------------------------------------------------------------------------------------------------------------
	/**
	 * Booking Contact login form.
	 *
	 */
	public function contact_login()
	{
		$this->load->library('password_hash', array(10, FALSE));
		$this->load->model('member_model');
		
		// Hash given password and check against db.
		$hashed_password = $this->password_hash->HashPassword($this->input->post('password'));
		$username = $this->input->post('username');	
		$booking_id = $this->input->post('booking_id');	
		
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
					'user_id'		=> $valid_user->id
				);
				
				$this->session->set_userdata($data);

				// Use members details with this booking
				// Update booking with contact_id of logged in member
				$this->load->model('member_model');
				
				// Get contact_id
				$contact = $this->member_model->get_member_info($data['user_id']);
				
				if ($contact->num_rows() == 1)
				{
					$contact_row = $contact->row();
					
					$this->member_model->update_booking(array('contact_id' => $contact_row->id), $booking_id);	
					
					// Update booking_ref with last_name
					$this->load->model('booking_model');
					$booking_query = $this->booking_model->get_booking($booking_id);
					
					if ($booking_query->num_rows() == 1)
					{
						$booking = $booking_query->row();
						$new_booking_ref = array('booking_ref' => $booking->booking_ref . "_" . strtoupper($contact_row->last_name));

						$this->booking_model->update_booking($booking_id, $new_booking_ref);
					}
					
					// Proceed to booking overview page!
					redirect('booking/overview/' . $booking_id);
				}
			}
			else
			{
				$this->session->set_flashdata("message_type", "error");
				$this->session->set_flashdata("Username or Password don't match any users on our records.");
				redirect('booking/contact/' . $booking_id);
			}
		}
		else
		{
			$this->session->set_flashdata("message_type", "error");
			$this->session->set_flashdata("user_message", "Username or Password don't match any users on our records.");
			redirect('booking/contact/' . $booking_id);
		}
	}	
	
	
	function use_saved_contact()
	{
		$booking_id = $this->input->post('booking_id');	
		$use_details = $this->input->post('use_saved_details');
		
		if (isset($use_details) && $use_details == "y")
		{
			// Use members details with this booking
			// Update booking with contact_id of logged in member
			$this->load->model('member_model');
			
			// Get contact_id
			$contact = $this->member_model->get_member_info($this->session->userdata('user_id'));
			
			if ($contact->num_rows() == 1)
			{
				$contact_row = $contact->row();
				
				$this->member_model->update_booking(array('contact_id' => $contact_row->id), $booking_id);	
				
				// Update booking_ref with last_name
				$this->load->model('booking_model');
				$booking_query = $this->booking_model->get_booking($booking_id);
				
				if ($booking_query->num_rows() == 1)
				{
					$booking = $booking_query->row();
					$new_booking_ref = array('booking_ref' => $booking->booking_ref . "_" . strtoupper($contact_row->last_name));
	
					$this->booking_model->update_booking($booking_id, $new_booking_ref);
				}
				
				// Proceed to booking overview page!
				redirect('booking/overview/' . $booking_id);
			}
		}
		else
		{
			// Proceed to booking overview page!
			redirect('booking/contact/' . $booking_id);
		}
	}
	
	
	// --------------------------------------------------------------------------------------------------------------
	/**
	 * Booking Contact insert form.
	 *
	 */
	public function contact()
	{
		if ($this->uri->segment(3) === FALSE || !is_numeric($this->uri->segment(3)))
		{
			redirect('booking/index');
		}
		else
		{
			// Check if session data booking_id equals segment 3
			$booking_id = $this->uri->segment(3);
			
			if ($this->session->userdata('booking_id') != $booking_id)
			{
				redirect('booking/index');
			}
			else
			{
				$data['booking_id'] = $booking_id;
				
				//Manage Contact Page
				$this->contact_form();

				if ($this->form_validation->run() == FALSE)
				{	
					$this->load->view('booking/contact', $data);
				}
				else
				{
					// Form validates! Do stuff with the data.
					foreach ($_POST as $key => $val)
					{
						$contact_data[$key] = $this->input->post($key);
					}
					
					// Subscribe user to Mailchimp list if newsletter_registration is 'y'
					if (isset($contact_data['newsletter_registration']) && $contact_data['newsletter_registration'] === 'y') 
					{
						$this->mailchimp_list_subscribe($contact_data['email_address'], $contact_data['first_name'], $contact_data['last_name']);
					}
					
					// Create a member account using email_address, first_name, last_name, password
					
					$this->load->library('password_hash', array(10, FALSE));
					$this->load->model('member_model');
					
					$member_data = array(
						'username' 			=> $contact_data['email_address'],
						'screen_name' 		=> $contact_data['first_name'] . " " . $contact_data['last_name'],
						'password' 			=> $this->password_hash->HashPassword($contact_data['password']),
						'email_address'		=> $contact_data['email_address'],
						"admin_access"		=> "n"
					);
					
					$this->member_model->register($member_data);
					
					$contact_data['member_id'] = $this->db->insert_id();
					
					
					// If logged in user has admin access then don't log in new contact
					$this->load->model('booking_model');

					$admin = $this->session->userdata('is_admin');
					$is_logged_in = $this->session->userdata('is_logged_in');
					
					if ($is_logged_in !== TRUE || $admin !== "y")
					{
						// Log user in straight away by setting session variables.
						$session_data = array(
							'screen_name' 	=> $member_data['screen_name'],
							'is_logged_in' 	=> true,
							'is_admin'		=> "n",
							'site_id' 		=> $this->session->userdata('site_id'),
							'user_id'		=> $contact_data['member_id']
						);	
						
						$this->session->set_userdata($session_data);
					}
					
					// Remove unused variables
					unset($contact_data['submit']);
					unset($contact_data['booking_id']);
					unset($contact_data['terms_and_conditions']);
					unset($contact_data['newsletter_registration']);
					unset($contact_data['password']);
					unset($contact_data['password_confirmation']);
					
					// Insert contact into db
					$this->booking_model->insert_contact_row($contact_data);

					$contact_id = $this->db->insert_id();
					
					// Update booking record with new contact ID
					$this->booking_model->add_booking_contact($data['booking_id'], $contact_id);
					
					// Update booking_ref with last_name
					$booking_query = $this->booking_model->get_booking($data['booking_id']);
					
					if ($booking_query->num_rows() == 1)
					{
						$booking = $booking_query->row();
						$new_booking_ref = array('booking_ref' => $booking->booking_ref . "_" . strtoupper($contact_data['last_name']));

						$this->booking_model->update_booking($data['booking_id'], $new_booking_ref);
					}
					
					// Proceed to booking overview page!
					redirect('booking/overview/' . $data['booking_id']);
				}
			}	
		}
	}
	
	
	
	// --------------------------------------------------------------------------------------------------------------
	/**
	 * Booking Contact insert form.
	 *
	 */
	public function edit_contact()
	{
		if ($this->uri->segment(3) === FALSE || !is_numeric($this->uri->segment(3)))
		{		
			redirect('booking/index');
		}
		else
		{
			// Check if session data booking_id equals segment 3
			$booking_id = $this->uri->segment(3);
			$data['booking_id'] = $booking_id;
			
			if ($this->session->userdata('booking_id') != $booking_id)
			{
				redirect('booking/index');
			}
			else
			{
				//Manage Contact Page
				$this->edit_contact_form();
				$this->load->model('booking_model');

				if ($this->form_validation->run() == FALSE)
				{	
					// Get exisiting contact data to put in form
					$data['contact_details'] = $this->booking_model->get_contact_from_booking_id($booking_id);
					
					$this->load->view('booking/edit_contact', $data);
				}
				else
				{
					// Form validates! Do stuff with the data.
					foreach ($_POST as $key => $val)
					{
						$contact_data[$key] = $this->input->post($key);
					}
					
					$contact_id = $contact_data['contact_id'];
					$member_id = $contact_data['member'];
					
					// Update member account using email_address, first_name, last_name
					$this->load->model('member_model');
					
					$member_data = array(
						'username' 			=> $contact_data['email_address'],
						'screen_name' 		=> $contact_data['first_name'] . " " . $contact_data['last_name'],
						'email_address'		=> $contact_data['email_address']
					);
					
					$this->member_model->update_member($member_data, $member_id);
					
					// Update session variables.
					$session_data = array(
						'screen_name' 	=> $member_data['screen_name']
					);
				
					$this->session->set_userdata($session_data);
					
					// Remove unused variables
					unset($contact_data['submit']);
					unset($contact_data['contact_id']);
					
					// Update contact record
					$this->booking_model->update_contact($contact_id, $contact_data);
					
					// Proceed to booking overview page!
					redirect('booking/overview/' . $data['booking_id']);
				}
			}	
		}
	}
	
	
	// --------------------------------------------------------------------------------------------------------------
	/**
	 * Password Reset Form for contact page
	 *
	 */
	function forgot_password()
	{
		$this->forgot_password_form();
		
		$response_data = array("message_type" => "error");
		
		if ($this->form_validation->run() == FALSE)
		{
			$response_data['message'] = "Please enter a valid email address";
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
				
				// Send new password in email
				$this->email->from("booking@thebivouac.co.uk", "The Bivouac");
				$this->email->to($valid_user->email_address);
				
				$this->email->subject("Password Reset (Bivouac)");
				
				$this->email->message("Hello\n\nAs requested, your password has been reset.\n\nOnce you have logged in you should create a new password under your account settings for increased security.\n\nNew Password: " . $new_password . "\n\nYou can login to your Bivouac account by going to this url:\nhttp://booking.thebivouac.co.uk/account/login\n\nThank you,\nBivouac");
				
				// Send the email!
				if (!$this->email->send())
				{
					$response_data['message'] = "We were unable to send you your new password. Please try resetting it again. Thank you.";	
				}
				else
				{
					$response_data['message'] = "We have sent you a new password by email. Please create a new password when you have logged in under your account settings. Thank you";
					$response_data['message_type'] = "success";
				}
			}
			else
			{
				$response_data['message'] = "Email Address does not match any users on our records.";
			}
		}
		
		echo json_encode($response_data);
	}

	
	
	// --------------------------------------------------------------------------------------------------------------
	/**
	 * Booking Overview/Review Page.
	 *
	 */
	public function overview()
	{
		if ($this->uri->segment(3) === FALSE || !is_numeric($this->uri->segment(3)))
		{
			redirect('booking/index');
		}
		else
		{
			// Check if session data booking_id equals segment 3
			$booking_id = $this->uri->segment(3);
			
			if ($this->session->userdata('booking_id') != $booking_id)
			{
				redirect('booking/index');
			}
			else
			{
				$this->overview_form();
				
				if ($this->form_validation->run() == FALSE)
				{
					//Get Booking Details
					$this->load->model('booking_model');
					$data['booking_details'] = $this->booking_model->get_booking($booking_id);
					
					if ($data['booking_details']->num_rows() > 0)
					{
						$booking = $data['booking_details']->row();
						
						$accommodation_ids = explode("|", $booking->accommodation_ids);
						$contact_id = $booking->contact_id;
						
						$data = $this->calculate_deposit($booking->site_id, $booking->total_price, $booking->start_date, $data);
					}
					else
					{
						redirect('/booking/index');
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
					
					// Site Details
					$data['site_details'] = $this->booking_model->get_sites();
					
					$data['booking_id'] = $booking_id;
				
					$this->load->view('booking/overview', $data);
				}
				else
				{
					// We've got the amount they're paying now and any voucher codes so go to payment Page
					$this->session->set_userdata('what_paying', $this->input->post('what_paying'));
					$this->session->set_userdata('voucher', $this->input->post('voucher'));
					
					redirect('/booking/payment/' . $booking_id);
				}
			}
		}
	}
	
	// ---------------------------------------------------------------------------------------------------------------
	/**
	 * Booking Payment Page!
	 *
	 */
	public function payment()
	{
		require_once '/var/www/vhosts/thebivouac.co.uk/subdomains/booking/httpsdocs/application/third_party/cardsave/Config.php';
		require_once '/var/www/vhosts/thebivouac.co.uk/subdomains/booking/httpsdocs/application/third_party/cardsave/ISOCountries.php';
	
		$this->load->model('booking_model');
		$booking_id = $this->uri->segment(3);
	
		// If booking id is not in URI redirect away.
		if ($this->uri->segment(3) === FALSE || !is_numeric($this->uri->segment(3)))
		{
			redirect('booking/index');
		}
		
		// If logged in user has admin access then redirect them to this bookings
		// edit page in the backend so they can amend payment_status and show if
		// is telephone booking		
		$admin = $this->session->userdata('is_admin');
		$is_logged_in = $this->session->userdata('is_logged_in');
		
		if ($is_logged_in === TRUE && $admin === "y")
		{
			redirect('/admin/bookings/edit_booking/' . $booking_id);
		}
		else
		{
			// Check whether the booking in segment 3 is attached to the contact/member currently logged in
			// rather than to the session currently in place
			$user_booking_query = $this->booking_model->get_booking_member($booking_id);
			
			if ($user_booking_query->num_rows() == 1)
			{
				$user_booking_row = $user_booking_query->row();
				
				if ($user_booking_row->member_id == $this->session->userdata('user_id'))
				{
					$valid = TRUE;
				}
				else
				{
					$valid = FALSE;
				}
			}
			else
			{
				$valid = FALSE;
			}
		}
		
		// User Details
		$data['user'] = array(
			'screen_name' 	=> $this->session->userdata('screen_name'),
			'user_id'		=> $this->session->userdata('user_id')
		);
		
		// Get what_paying and voucher session data if available
		$what_paying_session = $this->session->userdata('what_paying');
		
		if (!isset($what_paying_session) OR empty($what_paying_session))
		{
			$what_paying_session = "balance";
			$this->session->set_userdata('what_paying', $what_paying_session);
		}
		
		$voucher = $this->session->userdata('voucher');
		$this->session->unset_userdata('voucher');
		
		if (!isset($voucher) OR empty($voucher))
		{
			$voucher = FALSE;
		}
		
		if ($valid === FALSE)
		{
			redirect('booking/index');
		}
		else
		{
			// Set booking_id in session if it doesn't exist
			$this->session->set_userdata('booking_id', $booking_id);
		
			$this->payment_form();
			
			if ($this->form_validation->run() == FALSE)
			{	
				$data['iclISOCountryList'] = $iclISOCountryList;
				$data['booking_id'] = $booking_id;
				
				// Get price and booking_ref
				$booking_query = $this->booking_model->get_booking($booking_id);				
				
				if ($booking_query->num_rows() == 1)
				{
					$booking_row = $booking_query->row();
					
					// Do we have a voucher code to deal with?
					// If so get new total price and save it to db
					if (!empty($voucher) && $voucher != FALSE)
					{
						// Does voucher exist in db?
						$voucher_query = $this->booking_model->get_voucher($voucher);
						
						if ($voucher_query->num_rows() == 1)
						{
							$voucher_row = $voucher_query->row();
							
							// We need to check if a vouher has already been used on this booking!
							// If it has we can't add another one.
							if ($booking_row->type !== "voucher")
							{
								// Are we within the start_date and end_date of the voucher?
								if ($this->check_in_range($voucher_row->start_date, $voucher_row->end_date, "now"))
								{
									// Now check if there is a valid_from and valid_to and whether arrival date is between those
									if ($this->check_in_range($voucher_row->valid_from, $voucher_row->valid_to, $booking_row->start_date))
									{
										// Get price of accommodation only by removing total price of extras from total_price
										// Get extras
										$extras_total_price = 0;
										$extras = $this->booking_model->get_booked_extras($booking_id);
										
										foreach($extras->result() as $extra)
										{
											$extras_total_price = (int) $extras_total_price + (int) $extra->price;
										}
									
										$accom_price = $booking_row->total_price - $extras_total_price;
										
										// Are we dealing with a % of fixed amount discount?
										if ($voucher_row->discount_price !== "0.00")
										{	
											$price = ($accom_price - $voucher_row->discount_price) + $extras_total_price;
										}
										else
										{
											$accom_discount = $accom_price - round(($accom_price / 100) * $voucher_row->discount_percentage, 2);
											$price = $accom_discount + $extras_total_price;
										}
										
										// Update booking total price
										$this->booking_model->update_booking($booking_id, array('total_price' => $price, 'type' => 'voucher'));
									}
									else
									{
										$data['voucher_message'] = 'Sorry, that voucher is not valid for the time of your holiday';
										$price = $booking_row->total_price;	
									}							
								}
								else
								{
									$data['voucher_message'] = 'Sorry, that voucher has expired';
									$price = $booking_row->total_price;	
								}
							}
							else
							{
								// A voucher has already been applied!
								$data['voucher_message'] = 'Sorry, a voucher has already been applied to this booking';
								$price = $booking_row->total_price;	
							}
						}
						else
						{
							$data['voucher_message'] = 'Sorry, that voucher does not exist';
							$price = $booking_row->total_price;
						}
					}
					else
					{
						$price = $booking_row->total_price;
					}
				
					// Check payment status to determine next steps?
					if (strtolower($booking_row->payment_status) === "fully paid")
					{
						// booking has been fully paid for already! So direct user to already paid for page
						$this->load->view('/booking/complete', $data);
						return;
					}
					else if (strtolower($booking_row->payment_status) === "deposit")
					{
						// Paying the balance.
						$price = $price - $booking_row->amount_paid;
					}
					
					// Price must be in pence so multiply by 100
					// Are we paying deposit, full amount or balance now?
					if ($what_paying_session === "deposit")
					{
						$data = $this->calculate_deposit($booking_row->site_id, $price, $booking_row->start_date, $data);
						
						if (isset($data['deposit_amount']) && $data['deposit_amount'] > 0)
						{
							$data['price'] = $data['deposit_amount'] * 100;
							$data['booking_description'] = "Deposit Payment for " . $booking_row->booking_ref;
						}
					}
					else
					{
						// Is this the balance or full amount?
						if ($what_paying_session === "balance")
						{
							$data['what_paying'] = "balance";
						}
						else
						{
							$data['what_paying'] = "full amount";
						}
						
						$data['price'] = 100 * $price;
						$data['booking_description'] = "Full Payment for " . $booking_row->booking_ref;
					}
					
					$data['booking_ref'] = $booking_row->booking_ref;
				}
				
				$this->load->view('booking/payment', $data);
			}
			else
			{
				foreach ($_POST as $field => $value) 
				{
					$$field = $value;
				}
			
				require_once "/var/www/vhosts/thebivouac.co.uk/subdomains/booking/httpsdocs/application/third_party/cardsave/ThePaymentGateway/PaymentSystem.php";
			
				// Format all data to be sent in the CardDetailsTransaction process
				// Set Payment Gateway entry urls
				$rgeplRequestGatewayEntryPointList = new RequestGatewayEntryPointList();
				$rgeplRequestGatewayEntryPointList->add("https://gw1.".$PaymentProcessorFullDomain, 100, 2);
				$rgeplRequestGatewayEntryPointList->add("https://gw2.".$PaymentProcessorFullDomain, 200, 2);
				$rgeplRequestGatewayEntryPointList->add("https://gw3.".$PaymentProcessorFullDomain, 300, 2);

				// Grab Merchant Details using config merchand ID and password
				$mdMerchantDetails = new MerchantDetails($MerchantID, $Password);
				
				// This is a sale transaction
				$ttTransactionType = new NullableTRANSACTION_TYPE(TRANSACTION_TYPE::SALE);
				$mdMessageDetails = new MessageDetails($ttTransactionType);
				
				// Set transaction controls to include certain results/checks
				$boEchoCardType = new NullableBool(true);
				$boEchoAVSCheckResult = new NullableBool(true);
				$boEchoCV2CheckResult = new NullableBool(true);
				$boEchoAmountReceived = new NullableBool(true);
				$nDuplicateDelay = new NullableInt(60);
				$boThreeDSecureOverridePolicy = new NullableBool(true);
				
				// Format transaction controls section
				$tcTransactionControl = new TransactionControl($boEchoCardType, $boEchoAVSCheckResult, $boEchoCV2CheckResult, $boEchoAmountReceived, $nDuplicateDelay, "",  "", $boThreeDSecureOverridePolicy,  "",  null, null);
			
				// Set Amount (Integer) in pence. E.G. £10.00 = 1000
				$nAmount = new NullableInt($Amount);
				
				// This should be 826 (GBP) from form
				$nCurrencyCode = new NullableInt($CurrencyISOCode);
				
				// Is users browser a mobile (1) or not (0)
				$nDeviceCategory = new NullableInt(0);
				
				// Set ThreeDSecureBrowserDetails section
				$tdsbdThreeDSecureBrowserDetails = new ThreeDSecureBrowserDetails($nDeviceCategory, "*/*",  $_SERVER["HTTP_USER_AGENT"]);
				
				// Set Transation Details Section
				// $orderID and $orderDescription from form
				$tdTransactionDetails = new TransactionDetails($mdMessageDetails, $nAmount, $nCurrencyCode, $OrderID, $OrderDescription, $tcTransactionControl, $tdsbdThreeDSecureBrowserDetails);
			
				// Pre-process start_date and expiry_date
				// Expiry month
				if ($ExpiryDateMonth != "")
				{
					$nExpiryDateMonth = new NullableInt($ExpiryDateMonth);
				}
				else
				{
					$nExpiryDateMonth = null;
				}
				
				// Expiry Year
				if ($ExpiryDateYear != "")
				{
					$nExpiryDateYear = new NullableInt($ExpiryDateYear);
				}
				else
				{
					$nExpiryDateYear = null;
				}
				
				// Set ExpiryDate section
				$ccdExpiryDate = new CreditCardDate($nExpiryDateMonth, $nExpiryDateYear);
				
				// Start Month
				if ($StartDateMonth != "")
				{
					$nStartDateMonth = new NullableInt($StartDateMonth);
				}
				else
				{
					$nStartDateMonth = null;
				}
				
				// Start Year
				if ($StartDateYear != "")
				{
					$nStartDateYear = new NullableInt($StartDateYear);
				}
				else
				{
					$nStartDateYear = null;
				}
				
				// Set SrtartDate section
				$ccdStartDate = new CreditCardDate($nStartDateMonth, $nStartDateYear);
				
				// Set Card Details section
				// $CardName, $CardNumber, $IssueNumber, $CV2 from form
				$cdCardDetails = new CardDetails($CardName, $CardNumber, $ccdExpiryDate, $ccdStartDate, $IssueNumber, $CV2);
				
				// Address
				// Get Address from contact details
				// Set $countryISOCode to 826 (UK) for now as we're only getting UK contact details anyway.
				$CountryISOCode = 826;
				if ($CountryISOCode != "" && $CountryISOCode != -1)
				{
					$nCountryCode = new NullableInt($CountryISOCode);
				}
				else
				{
					$nCountryCode = null;
				}
				
				$adBillingAddress = new AddressDetails($Address1, $Address2, $Address3, $Address4, $City, $State, $PostCode, $nCountryCode);
				
				// Set Customer Details Section
				// Get customer email_address, contact_number and IP Address
				$this->load->model('member_model');
				$contact_query = $this->member_model->get_member_info($this->session->userdata('user_id'));
				
				if ($contact_query->num_rows() == 1)
				{
					$contact_row = $contact_query->row();
					$email_address = $contact_row->email_address;
					$contact_number = $contact_row->daytime_number;
				}
				else
				{
					$email_address = "anonymous@test.com";
					$contact_number = "123456789";
				}
				
				$cdCustomerDetails = new CustomerDetails($adBillingAddress, $email_address, $contact_number, $_SERVER["REMOTE_ADDR"]);

				// Process Card Transaction
				$cdtCardDetailsTransaction = new CardDetailsTransaction($rgeplRequestGatewayEntryPointList, 1, null, $mdMerchantDetails, $tdTransactionDetails, $cdCardDetails, $cdCustomerDetails, $booking_id);
				
				// SEND CARD DETAILS TRANSACTION!
				$boTransactionProcessed = $cdtCardDetailsTransaction->processTransaction($goGatewayOutput, $tomTransactionOutputMessage);
								
				// CARD DETAILS TRANSACTION RESPONSE
				if ($boTransactionProcessed == false)
				{
					// Could not communicate with the payment gateway 
					$this->session->set_flashdata('user_message', "Oops something has gone wrong. We couldn't communicate with our payment gateway. No money has been charged to your account.");
					redirect('booking/payment/' . $booking_id);
				}
				else
				{					 
					switch ($goGatewayOutput->getStatusCode())
					{
						case 0:
							// status code of 0 - means transaction successful - Show success message
							// Update Booking Status and amount paid
							// Check what's been paid, i.e. deposit, full amount or balance
							if (isset($what_paying) && !empty($what_paying))
							{
								$balancing_payment = FALSE;
							
								if ($what_paying === "deposit")
								{
									$update_data = array(
										"amount_paid"		=> $Amount / 100,
										"payment_status"	=> "deposit"
									);
								}
								else if ($what_paying === "full amount")
								{
									$update_data = array(
										"amount_paid"		=> $Amount / 100,
										"payment_status"	=> "fully paid"
									);
								}
								else
								{
									$balancing_payment = TRUE;
								
									// Need to set the amount paid to the full amount
									$query = $this->booking_model->get_booking($booking_id);
									
									if ($query->num_rows() > 0)
									{
										$row = $query->result();
									
										$update_data = array(
											"amount_paid"		=> $row->total_price,
											"payment_status"	=> "fully paid"
										);
									}
								}
								
								$this->booking_model->update_booking($booking_id, $update_data);				
							
								$this->session->unset_userdata('what_paying');				
								
								$this->send_booking_emails($balancing_payment, $booking_id);
								
								$user_message = "Your booking has been confirmed and we have sent you an email of the booking details for your records.<br /><br />At any point you can " . anchor('/account/bookings', 'login to your account', array('title' => 'Login to your account')) . " to view your booking and pay off any outstanding balance.";
								
								$this->session->set_flashdata('user_message', $user_message);
								$this->session->set_flashdata('message_type', 'success');
								redirect('booking/payment_results/' . $booking_id);
							}
							else
							{
								echo 'what paying isnt set!';
							}
							break;
						case 3:
							// status code of 3 - means 3D Secure authentication required 
							$data['PaREQ'] = $tomTransactionOutputMessage->getThreeDSecureOutputData()->getPaREQ();
							$data['CrossReference'] = $tomTransactionOutputMessage->getCrossReference();
							$data['FormAttributes'] = " target=\"ACSFrame\"";
							$data['FormAction'] = $tomTransactionOutputMessage->getThreeDSecureOutputData()->getACSURL();
							$data['SiteSecureBaseURL'] = $SiteSecureBaseURL;
							
							$this->load->view('booking/payment_3D_secure', $data);
							break;
						case 4:
							// status code of 4 - means transaction deferred 
							$this->session->set_flashdata('user_message', $goGatewayOutput->getMessage());
							$this->session->set_flashdata('message_type', 'error');
							redirect('booking/payment_results/' . $booking_id);
							break;
						case 5:
							// status code of 5 - means transaction declined 
							if (!isset($what_paying))
							{
								$what_paying = "full_amount";
							}
							
							$this->session->set_flashdata('user_message', $goGatewayOutput->getMessage());
							$this->session->set_flashdata('message_type', 'error');
							$this->session->set_flashdata('what_paying', $what_paying);
							
							redirect('booking/payment_results/' . $booking_id);
							break;
						case 20:
							// status code of 20 - means duplicate transaction 
							if ($goGatewayOutput->getPreviousTransactionResult()->getStatusCode()->getValue() == 0)
							{
								$this->session->set_flashdata('message_type', 'success');
							}
							else
							{
								$this->session->set_flashdata('message_type', 'error');
						   	}
						   	
							$data['prev_message'] = $goGatewayOutput->getPreviousTransactionResult()->getMessage();
							$data['duplicate_transaction'] = TRUE;
							
							$this->load->view('booking/payment_results', $data);
							
							break;
						case 30:
							// status code of 30 - means an error occurred 
							$Message = $goGatewayOutput->getMessage();
							
							if ($goGatewayOutput->getErrorMessages()->getCount() > 0)
							{
								$Message .= "<br /><ul>";
			
								for ($i = 0; $i < $goGatewayOutput->getErrorMessages()->getCount(); $i++)
								{
									$Message .= "<li>" . $goGatewayOutput->getErrorMessages()->getAt($i) . "</li>";
								}
								
								$Message .= "</ul>";
							}
							
							if (!isset($what_paying))
							{
								$what_paying = "full_amount";
							}
							
							$this->session->set_flashdata('what_paying', $what_paying);
							$this->session->set_flashdata('user_message', $Message);
							$this->session->set_flashdata('message_type', 'error');
							redirect('booking/payment_results/' . $booking_id);
							
							break;
						default:
							// unhandled status code - reload payment form
							$this->session->set_flashdata('user_message', 'Code: ' . $goGatewayOutput->getStatusCode() . ', Message: ' . $goGatewayOutput->getMessage());
							$this->session->set_flashdata('message_type', 'error');
							redirect('booking/payment/' . $booking_id);
							break;
					}
				}
			}
		}
	}
	
	
	// ---------------------------------------------------------------------------------------------------------------
	/**
	 * 3D Secure Results form ACS
	 *
	 */
	public function payment_3d_secure_result()
	{
		$booking_id = $this->uri->segment(3);
		$PaRES = $this->input->post('PaRes');
		$CrossReference = $this->input->post('MD');
		
		// Get user details
		$this->load->model('booking_model');
		$query = $this->booking_model->get_booking_member($booking_id);
		
		if ($query->num_rows() > 0)
		{
			$row = $query->row();
			
			// User Details
			$data['user'] = array(
				'screen_name' 	=> $row->screen_name,
				'user_id'		=> $row->member_id
			);	
			
			// Set-up session again
			$session_data = array(
				'screen_name' 	=> $row->screen_name,
				'user_id'		=> $row->member_id,
				'is_logged_in'	=> TRUE,
				'is_admin'		=> 'n',
				'site_id' 		=> 1,
				'booking_id'	=> $booking_id
			);
			
			$this->session->set_userdata($data);
		}
		
		require_once '/var/www/vhosts/thebivouac.co.uk/subdomains/booking/httpsdocs/application/third_party/cardsave/Config.php';
		require_once "/var/www/vhosts/thebivouac.co.uk/subdomains/booking/httpsdocs/application/third_party/cardsave/ThePaymentGateway/PaymentSystem.php";

		$rgeplRequestGatewayEntryPointList = new RequestGatewayEntryPointList();
		$rgeplRequestGatewayEntryPointList->add("https://gw1." . $PaymentProcessorFullDomain, 100, 2);
		$rgeplRequestGatewayEntryPointList->add("https://gw2." . $PaymentProcessorFullDomain, 200, 2);
		$rgeplRequestGatewayEntryPointList->add("https://gw3." . $PaymentProcessorFullDomain, 300, 2);

		$mdMerchantDetails = new MerchantDetails($MerchantID, $Password);
	
		$tdsidThreeDSecureInputData = new ThreeDSecureInputData($CrossReference, $PaRES);
	
		$tdsaThreeDSecureAuthentication = new ThreeDSecureAuthentication($rgeplRequestGatewayEntryPointList, 1, null, $mdMerchantDetails, $tdsidThreeDSecureInputData, $booking_id);
		$boTransactionProcessed = $tdsaThreeDSecureAuthentication->processTransaction($goGatewayOutput, $tomTransactionOutputMessage);
	
		if ($boTransactionProcessed == false)
		{
			// Could not communicate with the payment gateway 
			$this->session->set_flashdata('user_message', "Couldn't communicate with payment gateway");
			redirect('booking/payment/' . $booking_id);
		}
		else
		{
			$what_paying_session = $this->uri->segment(4);
	
			switch ($goGatewayOutput->getStatusCode())
			{
				case 0:
					// status code of 0 - means transaction successful - Show success message
					// Update Booking Status and amount paid
					// Check what's been paid, i.e. deposit or full amount
					$balancing_payment = FALSE;
					
					// Get booking info
					$booking_query = $this->booking_model->get_booking($booking_id);
				
					if ($booking_query->num_rows() > 0)
					{
						$booking_row = $booking_query->row();

						$price = $booking_row->total_price;						
						
						if (isset($what_paying_session) && !empty($what_paying_session))
						{
							$balancing_payment = FALSE;
						
							if ($what_paying_session === "deposit")
							{
								$data = $this->calculate_deposit($booking_row->site_id, $price, $booking_row->start_date, $data);
								
								$update_data = array(
									"amount_paid"		=> $data['deposit_amount'],
									"payment_status"	=> "deposit"
								);
							}
							else if ($what_paying_session === "full_amount")
							{
								$update_data = array(
									"amount_paid"		=> $price,
									"payment_status"	=> "fully paid"
								);
							}
							else
							{
								$balancing_payment = TRUE;

								$update_data = array(
									"amount_paid"		=> $price,
									"payment_status"	=> "fully paid"
								);
							}
						}
						else
						{
							echo 'what paying isnt set!';
						}	
					
						$this->booking_model->update_booking($booking_id, $update_data);
					}
					
					$this->session->unset_userdata('what_paying');
					
					$this->send_booking_emails($balancing_payment, $booking_id);
					
					if ($what_paying_session === "deposit")
					{
						$user_message = "Your remaining balance has been fully paid and your booking status has been updated.<br /><br />At any point you can " . anchor('/account/bookings', 'login to your account', array('title' => 'Login to your account')) . " to view your booking.<br />We look forward to greeting you on arrival at Bivouac.";
					}
					else
					{
						$user_message = "Your booking has been confirmed and we have sent you an email of the booking details for your records.<br /><br />At any point you can " . anchor('/account/bookings', 'login to your account', array('title' => 'Login to your account')) . " to view your booking and pay off any outstanding balance.";
					}
					
					$this->session->set_flashdata('user_message', $user_message);
					$this->session->set_flashdata('message_type', 'success');
					redirect('booking/payment_results/' . $booking_id);
					break;
				case 5:
					// status code of 5 - means transaction declined 
					$this->session->set_flashdata('user_message', $goGatewayOutput->getMessage());
					$this->session->set_flashdata('message_type', 'error');
					redirect('booking/payment_results/' . $booking_id);
					break;
				case 20:
					// status code of 20 - means duplicate transaction 
					if ($goGatewayOutput->getPreviousTransactionResult()->getStatusCode()->getValue() == 0)
					{
						$this->session->set_flashdata('message_type', 'success');
					}
					else
					{
						$this->session->set_flashdata('message_type', 'error');
				   	}
				   	
					$data['prev_message'] = $goGatewayOutput->getPreviousTransactionResult()->getMessage();
					$data['duplicate_transaction'] = TRUE;
					
					$this->load->view('booking/payment_results', $data);
					
					break;
				case 30:
					// status code of 30 - means an error occurred 
					$Message = $goGatewayOutput->getMessage();
					if ($goGatewayOutput->getErrorMessages()->getCount() > 0)
					{
						$Message = $Message."<br /><ul>";
	
						for ($i = 0; $i < $goGatewayOutput->getErrorMessages()->getCount(); $i++)
						{
							$Message = $Message."<li>".$goGatewayOutput->getErrorMessages()->getAt($i)."</li>";
						}
						
						$Message = $Message."</ul>";
					}
					
					$this->session->set_flashdata('user_message', $Message);
					$this->session->set_flashdata('message_type', 'error');
					redirect('booking/payment_results/' . $booking_id);
					
					break;
				default:
					// unhandled status code - reload payment form
					$this->session->set_flashdata('user_message', 'Code: ' . $goGatewayOutput->getStatusCode() . ', Message: ' . $goGatewayOutput->getMessage());
					$this->session->set_flashdata('message_type', 'error');
					redirect('booking/payment/' . $booking_id);
					break;
			}
		}
	}
	
	
	// ---------------------------------------------------------------------------------------------------------------
	/**
	 * 3D Secure Results form ACS
	 *
	 */
	public function payment_results()
	{
		if ($this->uri->segment(3) === FALSE || !is_numeric($this->uri->segment(3)))
		{
			redirect('booking/index');
		}
		else
		{
			// Check if session data booking_id equals segment 3
			$booking_id = $this->uri->segment(3);
			
			// User Details
			$data['user'] = array(
				'screen_name' 	=> $this->session->userdata('screen_name'),
				'user_id'		=> $this->session->userdata('user_id')
			);
			
			if ($this->session->userdata('booking_id') != $booking_id)
			{
				redirect('booking/index');
			}
			else
			{		
				$this->load->view('booking/payment_results', $data);
			}
		}
	}
	
	
	// ---------------------------------------------------------------------------------------------------------------
	/**
	 * Get all closed dates
	 *
	 */
	private function send_booking_emails($balancing_payment, $booking_id = 0)
	{
		// Send data to view for HTML email version
		// Get Booking Details		
		$this->load->model('booking_model');
		$data['booking_details'] = $this->booking_model->get_booking($booking_id);
		
		if ($data['booking_details']->num_rows() > 0)
		{
			$booking = $data['booking_details']->row();
			$accommodation_ids = explode("|", $booking->accommodation_ids);
			$contact_id = $booking->contact_id;
			
			$data = $this->calculate_deposit($booking->site_id, $booking->total_price, $booking->start_date, $data);
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
		
		$data['booking_id'] = $booking_id;
		
		// Is this from a balancing payment?
		if ($balancing_payment === TRUE)
		{
			$htmlContent = $this->load->view('emails/balancing_receipt', $data, true);
		}
		else
		{
			$htmlContent = $this->load->view('emails/html_receipt', $data, true);
		}
		
		if ($data['contact_details']->num_rows() > 0)
		{
			$contact = $data['contact_details']->row();
						
			$this->load->library('email');
			
			// Make sure we're sending hmtl email
			$config = array(
				'mailtype' 	=> 'html'
			);
			
			$this->email->initialize($config);
	
			$this->email->from('booking@thebivouac.co.uk', 'The Bivouac');
			$this->email->to($contact->email_address);
			$this->email->bcc('booking@thebivouac.co.uk');
			
			$this->email->subject('Your Bivouac Booking Confirmation - REF.' . $booking->booking_ref);
			
			$this->email->message($htmlContent);
			
			// Send the email!
			if (!$this->email->send())
			{
				/*
				echo 'error:<br />';
				echo $this->email->print_debugger();
				*/
			}	
		}
		

		if (!isset($balancing_payment) || $balancing_payment === FALSE)
		{		
			// If booking has 3 or more units send additional email to admin
			if (count($accommodation_ids) >= 3)
			{
				$message .= "<html><body>";
				$message .= "Hello Bivouac,<br /><br />";
				$message .= "This is an automated email to inform you of a new large group booking (3 or more units under 1 booking).<br /><br />";
				$message .= "Booking Ref. " . $booking->booking_ref . "<br />";
				$message .= "Adults. " . $booking->adults . "<br />";
				$message .= "Children (4 - 17s) " . $booking->children . "<br />";
				$message .= "Babies (0 - 3s) " . $booking->babies . "<br /><br />---------------------<br /><br />";
				$message .= "Booking Contact. " . $contact->first_name . " " . $contact->last_name . "<br />";
				$message .= "Contact Email. " . $contact->email_address;
				$message .= "</body></html>";
			
				$this->email->clear();
				
				$this->email->from('booking@thebivouac.co.uk', 'The Bivouac');
				$this->email->to('booking@thebivouac.co.uk');
				$this->email->subject('Large Group Booking Notice' . $booking->booking_ref);
				
				$this->email->message($message);
				
				$this->email->send();
			}
		}
	}
	
	
	// ---------------------------------------------------------------------------------------------------------------
	/**
	 * Get all closed dates
	 *
	 */
	private function send_booking_emails_manual()
	{
		// Send data to view for HTML email version
		// Get Booking Details		
		$this->load->model('booking_model');
		$booking_id = $this->uri->segment(3);
		
		if (isset($booking_id) && !empty($booking_id))
		{
			$data['booking_details'] = $this->booking_model->get_booking($booking_id);
			
			if ($data['booking_details']->num_rows() > 0)
			{
				$booking = $data['booking_details']->row();
				$accommodation_ids = explode("|", $booking->accommodation_ids);
				$contact_id = $booking->contact_id;
				
				$data = $this->calculate_deposit($booking->site_id, $booking->total_price, $booking->start_date, $data);
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
			
			$data['booking_id'] = $booking_id;
			
			$htmlContent = $this->load->view('emails/html_receipt', $data, true);
			
			if ($data['contact_details']->num_rows() > 0)
			{						
				$this->load->library('email');
				
				// Make sure we're sending hmtl email
				$config = array(
					'mailtype' 	=> 'html'
				);
				
				$this->email->initialize($config);
		
				$this->email->from('booking@thebivouac.co.uk', 'The Bivouac');
				$this->email->to('booking@thebivouac.co.uk');
				
				$this->email->subject('Your Bivouac Booking Confirmation - REF.' . $booking->booking_ref);
				
				$this->email->message($htmlContent);
				
				// Send the email!
				if (!$this->email->send())
				{
					echo 'error:<br />';
					echo $this->email->print_debugger();
					
				}	
			}
		}
	}
	
	
	function receipt_preview()
	{
		// Send data to view for HTML email version
		// Get Booking Details		
		$booking_id = $this->uri->segment(3);
		
		$this->load->model('booking_model');
		$data['booking_details'] = $this->booking_model->get_booking($booking_id);
		
		if ($data['booking_details']->num_rows() > 0)
		{
			$booking = $data['booking_details']->row();
			$accommodation_ids = explode("|", $booking->accommodation_ids);
			$contact_id = $booking->contact_id;

			$data = $this->calculate_deposit($booking->site_id, $booking->total_price, $booking->start_date, $data);
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
		
		$data['booking_id'] = $booking_id;
		
		// Is this from a balancing payment?

		$htmlContent = $this->load->view('emails/html_receipt', $data);
	}
	
	
	
	// ---------------------------------------------------------------------------------------------------------------
	/**
	 * Get all closed dates
	 *
	 */
	public function get_unavailable_dates()
	{
		$data = array('response' => false);
	
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
					// Add this new day to the dates array  
					$data['dates'][] = date('d-m-Y', $current_date);
					
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
		$start_date = $this->input->post('start_date');
	
		// Get all bookings up to 2 weeks after this start_date
		$start_date_ts = strtotime($start_date);
		$start_date = date('Y-m-d', $start_date_ts);
		$selected_start_date = date('D jS M', strtotime($start_date));
		$day = date('l', $start_date_ts);
		$one_day = 60 * 60 * 24;
		
		$two_weeks_ahead = date('Y-m-d', $start_date_ts + ($one_day * 14));
		
		// Get all site closed dates
		$closed_dates = $this->closed_dates($start_date_ts);
		$closed_in_range = FALSE;
		
		$selected_start = date('Y-m-d', $start_date_ts);
		$duration = array();
		
		// run through all site closed_dates
		if (count($closed_dates) > 0)
		{
			// Closed start date
			$closed_start = $closed_dates[0]['start_date'];	
			
			if ($this->date_difference($start_date, $closed_start) < 15)
			{
				$closed_in_range = TRUE;
			}
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
		
		if ($closed_in_range)
		{
			$start = $closed_start;
		}
		else
		{
			$start = FALSE;
		}
		
		// Any closed dates to account for?
		if (!$start)
		{
			// No closed dates within 2 weeks
			// Any public holidays?
			if ($holiday_in_range)
			{
				$diff = $this->date_difference($start_date, $holiday_date);
				
				if ($day === "Monday")
				{
					if ($diff == 7)
					{
						$duration = array(
							array(4, $selected_start_date, date('D jS M', $start_date_ts + (4 * $one_day))),
							array(8, $selected_start_date, date('D jS M', $start_date_ts + (8 * $one_day))),
							array(11, $selected_start_date, date('D jS M', $start_date_ts + (11 * $one_day))),
							array(14, $selected_start_date, date('D jS M', $start_date_ts + (14 * $one_day)))
						);
					} 
					else
					{
						$duration = array(
							array(4, $selected_start_date, date('D jS M', $start_date_ts + (4 * $one_day))),
							array(7, $selected_start_date, date('D jS M', $start_date_ts + (7 * $one_day))),
							array(11, $selected_start_date, date('D jS M', $start_date_ts + (11 * $one_day))),
							array(15, $selected_start_date, date('D jS M', $start_date_ts + (15 * $one_day)))
						);
					}
				}
				else if ($day === "Tuesday")
				{
					$duration = array(
						array(3, $selected_start_date, date('D jS M', $start_date_ts + (3 * $one_day))),
						array(6, $selected_start_date, date('D jS M', $start_date_ts + (6 * $one_day))),
						array(10, $selected_start_date, date('D jS M', $start_date_ts + (10 * $one_day))),
						array(13, $selected_start_date, date('D jS M', $start_date_ts + (13 * $one_day)))
					);
				}
				else
				{
					if ($diff == 3)
					{
						$duration = array(
							array(4, $selected_start_date, date('D jS M', $start_date_ts + (4 * $one_day))),
							array(7, $selected_start_date, date('D jS M', $start_date_ts + (7 * $one_day))),
							array(10, $selected_start_date, date('D jS M', $start_date_ts + (10 * $one_day))),
							array(14, $selected_start_date, date('D jS M', $start_date_ts + (14 * $one_day)))
						);
					}
					else
					{
						$duration = array(
							array(3, $selected_start_date, date('D jS M', $start_date_ts + (3 * $one_day))),
							array(7, $selected_start_date, date('D jS M', $start_date_ts + (7 * $one_day))),
							array(11, $selected_start_date, date('D jS M', $start_date_ts + (11 * $one_day))),
							array(14, $selected_start_date, date('D jS M', $start_date_ts + (14 * $one_day)))
						);
					}
				}
			}
			else
			{
				if ($day === "Monday")
				{
					$duration = array(
						array(4, $selected_start_date,  date('D jS M', $start_date_ts + (4 * $one_day))),
						array(7, $selected_start_date, date('D jS M', $start_date_ts + (7 * $one_day))),
						array(11, $selected_start_date, date('D jS M', $start_date_ts + (11 * $one_day))),
						array(14, $selected_start_date, date('D jS M', $start_date_ts + (14 * $one_day)))						
					);
				}
				else if ($day === "Tuesday")
				{
					$duration = array(
						array(3, $selected_start_date, date('D jS M', $start_date_ts + (3 * $one_day))),
						array(6, $selected_start_date, date('D jS M', $start_date_ts + (6 * $one_day))),
						array(10, $selected_start_date, date('D jS M', $start_date_ts + (10 * $one_day))),
						array(13, $selected_start_date, date('D jS M', $start_date_ts + (13 * $one_day)))
					);
				}
				else
				{
					$duration = array(
						array(3, $selected_start_date,  date('D jS M', $start_date_ts + (3 * $one_day))),
						array(7, $selected_start_date, date('D jS M', $start_date_ts + (7 * $one_day))),
						array(10, $selected_start_date, date('D jS M', $start_date_ts + (10 * $one_day))),
						array(14, $selected_start_date, date('D jS M', $start_date_ts + (14 * $one_day)))						
					);
				}
			}
		}
		else
		{
			// There are some closed dates to deal with!
			// Difference between selected date and closed date
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
					$duration = $this->get_complex_durations($diff, $selected_start_date, $start_date_ts, $one_day, $day);
				}
				else
				{
					// There is a public holiday before a closed date.
					// Calculate the durations to include the holiday, up to the closed date and no further						
					if ($day === "Monday")
					{
						if ($hol_diff == 7)
						{
							$duration = $this->get_closed_and_hol_durations(4, 8, 11, 14, $diff, $selected_start_date, $start_date_ts, $one_day);
						} 
						else
						{	
							$duration = $this->get_closed_and_hol_durations(4, 8, 11, 15, $diff, $selected_start_date, $start_date_ts, $one_day);						
						}
					}
					else if ($day === "Tuesday")
					{	
						$duration = $this->get_closed_and_hol_durations(3, 6, 10, 13, $diff, $selected_start_date, $start_date_ts, $one_day);
					}
					else
					{
						if ($hol_diff == 3)
						{
							$duration = $this->get_closed_and_hol_durations(4, 7, 10, 14, $diff, $selected_start_date, $start_date_ts, $one_day);
						}
						else
						{
							$duration = $this->get_closed_and_hol_durations(3, 7, 11, 14, $diff, $selected_start_date, $start_date_ts, $one_day);
						}						
					}
				}
			}
			else
			{
				$duration = $this->get_complex_durations($diff, $selected_start_date, $start_date_ts, $one_day, $day);
			}
		}
		
		$data['durations'] = $duration; 
		
		echo json_encode($data);
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
		$start_date = date('Y-m-d H:i:s', $start_date_ts);
		
		$end_date_ts = $start_date_ts + (60 * 60 * 24 * $post_duration);
		$end_date_ts_plus_one = $end_date_ts + (60 * 60 * 24);
		$end_date = date('Y-m-d H:i:s', $end_date_ts);
		$end_date_plus_one = date('Y-m-d H:i:s', $end_date_ts_plus_one);
		
		
		// Get all calendar entries whose start_date is between chosen start dates and end date
		$this->load->model('booking_model');		
		$calendar_query = $this->booking_model->get_calendar_dates();

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
					// Check if booking start_date is between calendar start_date and end_date
					// Remove 1 day from calendar end_date so we don't include that when matching
					// This should stop accommodation being removed if a booking exists up until that day.
					$calendar_end = date("Y-m-d H:i:s", (strtotime($calendar->end_date) - 86400));
					
					$current_date = $start_date_ts;
				
					while ($current_date < $end_date_ts)
					{  
						$current_date_formatted = date('Y-m-d H:i:s', $current_date);
					
						if ($this->check_in_range($calendar->start_date, $calendar_end, $current_date_formatted))
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
									$bb_current_date = $start_date_ts;
									
									// Go through each day of the holiday and get the beds available
									while ($bb_current_date < $end_date_ts)
									{  
										$bed_date_data = $this->guests_per_day($row->id, $current_date, $bed_date_data, $total_beds);
										
										// Add a day to the current date  
					    				$bb_current_date = $current_date + (60 * 60 * 24); 
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
					
						// Add a day to the current date  
			    		$current_date = $current_date + (60 * 60 * 24); 
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
				$accommodation_list .= "<li class='clearfix type-header'><h2>Sorry there are is no accommodation available for the dates and duration selected.</h2></li>";
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
				$accommodation_list .= "<li class='clearfix type-header'><h2>Sorry there is no accommodation available for the dates and duration selected.</h2></li>";
			}
		}

		echo json_encode($accommodation_list);
	}
	
	
	function create_accommodation_list($all_accommodation, $accommodation_list, $start_date, $duration)
	{
		$duration_blocks = array();
		$holiday_in_range = FALSE;
		$holiday_dates = array();
		
		// Get all public holidays
		$public_holidays = $this->public_holidays();

		foreach ($public_holidays as $holiday)
		{			
			// Is holiday date a monday?
			if (date('l', strtotime($holiday['start_date'])) === "Monday")
			{
				$holiday_in_range = $this->check_in_range($start_date, date('Y-m-d H:i:s', strtotime($start_date) + (60 * 60 * 24 * $duration)), $holiday['start_date']);
				
				if ($holiday_in_range)
				{
					$holiday_dates[] = $holiday['start_date'];
				}
			}
		}
		
		
		// Calculate price for this accommodation for this timeframe
		// We must split the duration into it's smallest parts (e.g. 3,and 4 night chunks) if it is over 4 days
		// With each chunk we must query the price scheme and calculate price
		// for that chunk. When all chunks are caluculated we must add them to get final price.
		if ($duration <= 4)
		{
			$duration_blocks[$start_date] = $duration;
		}
		else
		{
			// Find out what day of the week the start_date is
			// It will be a Monday (1), Tuesday (2) of Friday (5)
			$arrival_day = date('w', strtotime($start_date));

			if ($arrival_day === '1')
			{
				$duration_blocks[$start_date] = 4;
				
				// Is there a public holiday to deal with here?
				if (array_key_exists(0, $holiday_dates) && (strtotime($start_date) + (60 * 60 * 24 * 7)) === strtotime($holiday_dates[0]))
				{
					$duration_blocks[date('Y-m-d H:i:s', strtotime($start_date) + (60 * 60 * 24 * 4))] = 4;
					
					// So we're currently on a Tuesday, the next Friday will be + 3 days
					if ($duration > 8) 
					{
						$duration_blocks[date('Y-m-d H:i:s', strtotime($start_date) + (60 * 60 * 24 * 8))] = 3;
					}
				}
				else
				{
					$duration_blocks[date('Y-m-d H:i:s', strtotime($start_date) + (60 * 60 * 24 * 4))] = 3;
					
					// So we're currently on a Monday, the next Friday will be + 4 days
					if ($duration > 7) 
					{
						$duration_blocks[date('Y-m-d H:i:s', strtotime($start_date) + (60 * 60 * 24 * 7))] = 4;
					}
					
				}
				
				if ($duration > 11) 
				{
					if (count($holiday_dates) > 1)
					{
						if (array_key_exists(1, $holiday_dates) && (strtotime($start_date) + (60 * 60 * 24 * 14)) === strtotime($holiday_dates[1]))
						{
							$duration_blocks[date('Y-m-d H:i:s', strtotime($start_date) + (60 * 60 * 24 * 11))] = 4;
						}
						else
						{
							$duration_blocks[date('Y-m-d H:i:s', strtotime($start_date) + (60 * 60 * 24 * 11))] = 3;
						}
					}
					else
					{
						if (array_key_exists(0, $holiday_dates) && (strtotime($start_date) + (60 * 60 * 24 * 14)) === strtotime($holiday_dates[0]))
						{
							$duration_blocks[date('Y-m-d H:i:s', strtotime($start_date) + (60 * 60 * 24 * 11))] = 4;
						}
						else
						{
							$duration_blocks[date('Y-m-d H:i:s', strtotime($start_date) + (60 * 60 * 24 * 11))] = 3;
						}
					}
				}
			} // End of Arrival Day = Monday
			
			// If start_date = Tuesday (2)
			if ($arrival_day === '2')
			{
				$duration_blocks[$start_date] = 3;
				
				// Is there a public holiday to deal with here?
				if (array_key_exists(0, $holiday_dates) && (strtotime($start_date) + (60 * 60 * 24 * 6)) === strtotime($holiday_dates[0]))
				{
					$duration_blocks[date('Y-m-d H:i:s', strtotime($start_date) + (60 * 60 * 24 * 3))] = 4;
					
					if ($duration > 7)
					{
						$duration_blocks[date('Y-m-d H:i:s', strtotime($start_date) + (60 * 60 * 24 * 7))] = 3;
					}
				}
				else
				{
					$duration_blocks[date('Y-m-d H:i:s', strtotime($start_date) + (60 * 60 * 24 * 3))] = 3;
					
					if ($duration > 6)
					{
						$duration_blocks[date('Y-m-d H:i:s', strtotime($start_date) + (60 * 60 * 24 * 6))] = 4;
					}
				}
				
				if ($duration > 10)
				{
					if (count($holiday_dates) > 1)
					{
						if (array_key_exists(1, $holiday_dates) && (strtotime($start_date) + (60 * 60 * 24 * 13)) === strtotime($holiday_dates[1]))
						{
							$duration_blocks[date('Y-m-d H:i:s', strtotime($start_date) + (60 * 60 * 24 * 10))] = 4;
						}
						else
						{
							$duration_blocks[date('Y-m-d H:i:s', strtotime($start_date) + (60 * 60 * 24 * 10))] = 3;
						}
					}
					else
					{
						if (array_key_exists(0, $holiday_dates) && (strtotime($start_date) + (60 * 60 * 24 * 13)) === strtotime($holiday_dates[0]))
						{
							$duration_blocks[date('Y-m-d H:i:s', strtotime($start_date) + (60 * 60 * 24 * 10))] = 4;
						}
						else
						{
							$duration_blocks[date('Y-m-d H:i:s', strtotime($start_date) + (60 * 60 * 24 * 10))] = 3;
						}
					}
				}	
			} // End of arrival day = tuesday
			
			// If start_date = Friday (5)
			if ($arrival_day === '5')
			{	
				// Is there a public holiday to deal with here?
				if (array_key_exists(0, $holiday_dates) && (strtotime($start_date) + (60 * 60 * 24 * 3)) === strtotime($holiday_dates[0]))
				{
					$duration_blocks[$start_date] = 4;
					$duration_blocks[date('Y-m-d H:i:s', strtotime($start_date) + (60 * 60 * 24 * 4))] = 3;
				}
				else
				{
					$duration_blocks[$start_date] = 3;
					$duration_blocks[date('Y-m-d H:i:s', strtotime($start_date) + (60 * 60 * 24 * 3))] = 4;
				}
				
				
				if ($duration > 7)
				{
					if (count($holiday_dates) > 1)
					{
						if (array_key_exists(1, $holiday_dates) && (strtotime($start_date) + (60 * 60 * 24 * 10)) === strtotime($holiday_dates[1]))
						{
							$duration_blocks[date('Y-m-d H:i:s', strtotime($start_date) + (60 * 60 * 24 * 7))] = 4;
							
							if ($duration > 10)
							{
								$duration_blocks[date('Y-m-d H:i:s', strtotime($start_date) + (60 * 60 * 24 * 11))] = 3;
							}
						}
						else
						{
							$duration_blocks[date('Y-m-d H:i:s', strtotime($start_date) + (60 * 60 * 24 * 7))] = 3;
							
							if ($duration > 9)
							{
								$duration_blocks[date('Y-m-d H:i:s', strtotime($start_date) + (60 * 60 * 24 * 10))] = 4;
							}
						}
					}
					else
					{
						if (array_key_exists(0, $holiday_dates) && (strtotime($start_date) + (60 * 60 * 24 * 10)) === strtotime($holiday_dates[0]))
						{
							$duration_blocks[date('Y-m-d H:i:s', strtotime($start_date) + (60 * 60 * 24 * 7))] = 4;
							
							if ($duration > 10)
							{
								$duration_blocks[date('Y-m-d H:i:s', strtotime($start_date) + (60 * 60 * 24 * 11))] = 3;
							}
						}
						else
						{
							$duration_blocks[date('Y-m-d H:i:s', strtotime($start_date) + (60 * 60 * 24 * 7))] = 3;
							
							if ($duration > 9)
							{
								$duration_blocks[date('Y-m-d H:i:s', strtotime($start_date) + (60 * 60 * 24 * 10))] = 4;
							}
						}
					}
				}
			}
		} // End of duration checking

		// Get full list of all accommodation_types
		$all_accommodation_types = $this->booking_model->get_all_accommodation_types();
		
		if ($all_accommodation_types->num_rows() > 0)
		{
			$all_types = $all_accommodation_types->result_array();
		}
	
		$type_header = "";
		
		foreach ($all_accommodation as $accommodation)
		{		
			$price = 0;
			
			// Foreach $duration_block we must get the price, round it up to the nearest 5
			// then add them all together and apply any discount for longer breaks
			foreach ($duration_blocks as $date => $mini_duration)
			{
				$ppn = $this->get_price_per_night($accommodation->id, $date);
				
				if ($accommodation->type_name == "Bunk Barn")
				{	
					$price = $price + ceil(($ppn * $mini_duration) / 1) * 1;;
				}
				else
				{
					$price = $price + ceil(($ppn * $mini_duration) / 5) * 5;
				}
			}

			$price = $price + ($accommodation->additional_per_night_charge * $duration);
		
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
				else if ($duration == 13 || $duration == 14 || $duration == 15)
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
				while ($current_date < $end_date_ts)
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
				$accommodation_list .= "<li class='clearfix' data-id='" . $accommodation->id . "' data-price='" . $price . "' data-sleeps='" . $remaining_beds . "' data-type='" . $accommodation->type_name . "' data-dogs='" . $accommodation->dogs_allowed . "'>";
			}
			else
			{
				$accommodation_list .= "<li class='clearfix' data-id='" . $accommodation->id . "' data-price='" . $price . "' data-sleeps='" . $accommodation->sleeps . "' data-type='" . $accommodation->type_name . "' data-dogs='" . $accommodation->dogs_allowed . "'>";
			}
			
			$accommodation_list .= "<h2>" . $accommodation->name . " &mdash; This would cost <b>&pound;" . $price . "</b> for " . $duration . " nights " . $bunk_barn_extra_type . "</h2>";
			$accommodation_list .= "<div class='accommodation-info'><img src='" . base_url() . "images/accommodation/" . $accommodation->photo_1 . "' width='140' alt='" . $accommodation->name . " main photo' />";
			$accommodation_list .= "<p class='accommodation-description'>" . $accommodation->description . "<br /><br />";
			
			if ($accommodation->type_name == "Bunk Barn")
			{
				$accommodation_list .= "Beds Available: " . $remaining_beds . "<br /><a href='#' class='lightbox-full-accommodation' title='View full accommodation detail'>Read more about this accommodation</a></p>";
				
				$options = "";
				for ($i = 0; $i <= $remaining_beds; $i++)
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
	 * Remove inactive/unpaid Bookings & Calendar Records.
	 *
	 */
	function remove_inactive_records()
	{
		$this->load->model('booking_model');
		
		// Get all bookings with payment_status 'unpaid'
		$bookings = $this->booking_model->get_unpaid_bookings();
		
		if ($bookings->num_rows() > 0)
		{
			foreach ($bookings->result() as $booking)
			{
				// Check booking_creation_time against now to see if
				// they are 30 minutes or more apart
				$creation = strtotime($booking->booking_creation_date);
				$now = strtotime('now');
				
				$difference = $now - $creation;
				
				if (($difference / 60) > 30)
				{
					// Delete booking record & any calendar records
					$this->booking_model->delete_booking_record($booking->id);
					$this->booking_model->delete_calendar_record($booking->id);
				}
			}
		}
	}
	
	
	
	// --------------------------------------------------------------------------------------------------------------
	/**
	 * Send balacing payment reminder for bookings who have only paid deposit
	 * and are 6 weeks away. This will be run by a cron script daily.
	 */
	function balance_reminder()
	{
		$this->load->model('booking_model');
		
		// Get all bookings with payment_status 'unpaid'
		$bookings = $this->booking_model->get_deposit_bookings();
		
		if ($bookings->num_rows() > 0)
		{
			foreach ($bookings->result() as $booking)
			{
				// Check start_date against today to see if there is a 6 week difference.
				$start_date = strtotime($booking->start_date);
				$now = strtotime(date('Y-m-d', strtotime('now')));
				$cut_off = ($start_date - $now) / (60 * 60 * 24 * 7);
				
				if (ceil($cut_off) == 7)
				{			
					// Send reminder email to contact about paying balance.
					$data['booking_details'] = $this->booking_model->get_booking($booking->id);
		
					if ($data['booking_details']->num_rows() > 0)
					{
						$booking = $data['booking_details']->row();
						$contact_id = $booking->contact_id;
					}
							
					// Get Contact Details
					$data['contact_details'] = $this->booking_model->get_contact($contact_id);
					
					$htmlContent = $this->load->view('emails/balancing_reminder', $data, true);
					
					if ($data['contact_details']->num_rows() > 0)
					{
						$contact = $data['contact_details']->row();
									
						$this->load->library('email');
						
						// Make sure we're sending hmtl email
						$config = array(
							'mailtype' 	=> 'html'
						);
						
						$this->email->initialize($config);
				
						$this->email->from('bookings@thebivouac.co.uk', 'The Bivouac');
						$this->email->to($contact->email_address);
						
						$this->email->subject('Bivouac Payment Reminer');
						
						$this->email->message($htmlContent);
						
						// Send the email!
						if (!$this->email->send())
						{
							/*
							echo 'error:<br />';
							echo $this->email->print_debugger();
							*/
						}	
					}
				}
			}
		}
	}
	
	
	
	// --------------------------------------------------------------------------------------------------------------
	/**
	 * Password reset validation
	 *
	 */
	function forgot_password_form()
	{
		$this->load->library('form_validation');
		
		$this->form_validation->set_rules('email_address', 'Email Address', 'trim|valid_email|required');
	}
	
	
	// --------------------------------------------------------------------------------------------------------------
	/**
	 * Payment results Form.
	 *
	 */
	function payment_results_form()
	{
		$this->load->library('form_validation');
		
		$this->form_validation->set_rules('what_paying', 'Amount Paying', 'trim|required');
	}
	
	
	// --------------------------------------------------------------------------------------------------------------
	/**
	 * Overview Form.
	 *
	 */
	function overview_form()
	{
		$this->load->library('form_validation');
		
		$this->form_validation->set_rules('what_paying', 'Amount Paying', 'trim|required');
	}
	
	// --------------------------------------------------------------------------------------------------------------
	/**
	 * Payment Form.
	 *
	 */
	function payment_form()
	{
		$this->load->library('form_validation');
		
		$this->form_validation->set_rules('Address1', 'Address Line 1', 'trim|required');
		$this->form_validation->set_rules('City', 'Town/City', 'trim|required');
		$this->form_validation->set_rules('PostCode', 'Postcode', 'trim|required');	
		$this->form_validation->set_rules('CardName', 'Name on Card', 'trim|required');
		$this->form_validation->set_rules('CardNumber', 'Card Number', 'trim|required|numeric');
		$this->form_validation->set_rules('ExpiryDateMonth', 'Expiry Month', 'trim|numeric|required');
		$this->form_validation->set_rules('ExpiryDateYear', 'Expiry Year', 'trim|numeric|required');
	}
	
	
	// --------------------------------------------------------------------------------------------------------------
	/**
	 * Booking Index Form.
	 *
	 */
	function booking_index_form()
	{
		$this->load->library('form_validation');
		
		$this->form_validation->set_rules('site_id', 'Site ID', 'trim|numeric');
		//$this->form_validation->set_rules('accommodation_ids[]', 'Accommodation', 'trim|required');
		$this->form_validation->set_rules('start_date', 'Arrival Date', 'trim|required');
		$this->form_validation->set_rules('duration', 'Number of nights', 'trim|numeric|required');
		$this->form_validation->set_rules('adults', 'Adults', 'trim|numeric|required');
		$this->form_validation->set_rules('children', 'Children', 'trim|numeric');
		$this->form_validation->set_rules('total_price', 'Total Price', 'trim|numeric');
	}
	
	
	// --------------------------------------------------------------------------------------------------------------
	/**
	 * Extras Form.
	 *
	 */
	function extras_form()
	{
		$this->load->library('form_validation');
		
		$this->form_validation->set_rules('total_price', 'Total Price', 'trim|required');
		$this->form_validation->set_rules('booking_id', 'Booking', 'trim|required');
	}
	
	
	// --------------------------------------------------------------------------------------------------------------
	/**
	 * Contact Form.
	 *
	 */
	function contact_form()
	{
		$this->load->library('form_validation');
		
		$this->form_validation->set_rules('title', 'Title', 'trim|required');
		$this->form_validation->set_rules('first_name', 'First name', 'trim|required|min_length[2]');
		$this->form_validation->set_rules('last_name', 'Surname', 'trim|required|min_length[2]');
		$this->form_validation->set_rules('birth_day', 'Birth Day', 'trim|numeric|required');
		$this->form_validation->set_rules('birth_month', 'Birth Month', 'trim|numeric|required');
		$this->form_validation->set_rules('birth_year', 'Birth Year', 'trim|numeric|required');
		$this->form_validation->set_rules('house_name', 'House name/number', 'trim|required');
		$this->form_validation->set_rules('address_line_1', 'Address Line 1', 'trim|required|min_length[2]');
		$this->form_validation->set_rules('address_line_2', 'Address Line 2', 'trim');
		$this->form_validation->set_rules('city', 'Town/City', 'trim|required|min_length[2]');
		$this->form_validation->set_rules('county', 'County', 'trim|required|min_length[2]');
		$this->form_validation->set_rules('post_code', 'Postcode', 'trim|required|min_length[6]');
		$this->form_validation->set_rules('daytime_number', 'Daytime Contact Number', 'trim|required|min_length[9]');
		$this->form_validation->set_rules('mobile_number', 'Mobile Contact Number', 'trim|numeric');
		$this->form_validation->set_rules('email_address', 'Email Address', 'trim|required|valid_email');
		$this->form_validation->set_rules('terms_and_conditions', 'Terms and Conditions', 'trim|required');
		$this->form_validation->set_rules('password', 'Password', 'trim|required|min_length[6]');
		$this->form_validation->set_rules('password_confirmation', 'Password Confirmation', 'trim|required|matches[password]');
	}
	
	// --------------------------------------------------------------------------------------------------------------
	/**
	 * Edit Contact Form.
	 *
	 */
	function edit_contact_form()
	{
		$this->load->library('form_validation');
		
		$this->form_validation->set_rules('title', 'Title', 'trim|required');
		$this->form_validation->set_rules('first_name', 'First name', 'trim|required|min_length[2]');
		$this->form_validation->set_rules('last_name', 'Surname', 'trim|required|min_length[2]');
		$this->form_validation->set_rules('birth_day', 'Birth Day', 'trim|numeric|required');
		$this->form_validation->set_rules('birth_month', 'Birth Month', 'trim|numeric|required');
		$this->form_validation->set_rules('birth_year', 'Birth Year', 'trim|numeric|required');
		$this->form_validation->set_rules('house_name', 'House name/number', 'trim|required');
		$this->form_validation->set_rules('address_line_1', 'Address Line 1', 'trim|required|min_length[2]');
		$this->form_validation->set_rules('address_line_2', 'Address Line 2', 'trim');
		$this->form_validation->set_rules('city', 'Town/City', 'trim|required|min_length[2]');
		$this->form_validation->set_rules('county', 'County', 'trim|required|min_length[2]');
		$this->form_validation->set_rules('post_code', 'Postcode', 'trim|required|min_length[6]');
		$this->form_validation->set_rules('daytime_number', 'Daytime Contact Number', 'trim|required|min_length[9]');
		$this->form_validation->set_rules('mobile_number', 'Mobile Contact Number', 'trim|numeric');
		$this->form_validation->set_rules('email_address', 'Email Address', 'trim|required|valid_email');
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
	
	
	// --------------------------------------------------------------------------------------------------------------
	/**
	 * Complex Durations calculations
	 *
	 */
	function get_complex_durations($diff, $selected_start_date, $start_date_ts, $one_day, $day)
	{
		if ($diff < 7)
		{
			$duration[] = array($diff, $selected_start_date,  date('D jS M', $start_date_ts + ($diff * $one_day)));
		} 
		else if ($diff >= 7)
		{
			if ($day === 'Monday')
			{
				$duration[] = array(4, $selected_start_date,  date('D jS M', $start_date_ts + (4 * $one_day)));
			}
			else
			{
				$duration[] = array(3, $selected_start_date,  date('D jS M', $start_date_ts + (3 * $one_day)));
			}
		}

		if ($diff == 6)
		{
			$duration[] = array(6, $selected_start_date,  date('D jS M', $start_date_ts + (6 * $one_day)));
		}
		else if ($diff == 7)
		{				
			$duration[] = array(7, $selected_start_date,  date('D jS M', $start_date_ts + (7 * $one_day)));
		}
		else if ($diff == 10)
		{		
			if ($day === 'Monday')
			{		
				$duration[] = array(7,$selected_start_date,  date('D jS M', $start_date_ts + (7 * $one_day)));
			}
			else
			{
				$duration[] = array(6,$selected_start_date,  date('D jS M', $start_date_ts + (6 * $one_day)));
			}
			
			$duration[] = array(10, $selected_start_date,  date('D jS M', $start_date_ts + (10 * $one_day)));
		}
		else if ($diff == 11)
		{
			$duration[] = array(7, $selected_start_date, date('D jS M', $start_date_ts + (7 * $one_day)));
			$duration[] = array(11, $selected_start_date, date('D jS M', $start_date_ts + (11 * $one_day)));
		}
		else if ($diff == 13)
		{
			$duration[] = array(6, $selected_start_date, date('D jS M', $start_date_ts + (6 * $one_day)));
			$duration[] = array(13, $selected_start_date, date('D jS M', $start_date_ts + (13 * $one_day)));
		}
		else if ($diff == 14)
		{
			$duration[] = array(7, $selected_start_date, date('D jS M', $start_date_ts + (7 * $one_day)));
			
			if ($day === 'Monday')
			{
				$duration[] = array(11, $selected_start_date, date('D jS M', $start_date_ts + (11 * $one_day)));
			}
			else
			{
				$duration[] = array(10, $selected_start_date, date('D jS M', $start_date_ts + (10 * $one_day)));
			}
			
			$duration[] = array(14, $selected_start_date, date('D jS M', $start_date_ts + (14 * $one_day)));
		}
		
		return $duration;
	}
	
	
	// --------------------------------------------------------------------------------------------------------------
	/**
	 * Get durations when there's public holidays and closed dates to deal with
	 *
	 */
	function get_closed_and_hol_durations($range1, $range2, $range3, $range4, $diff, $selected_start_date, $start_date_ts, $one_day)
	{
		if ($diff >= $range1)
		{
			$duration[] = array($range1, $selected_start_date, date('D jS M', $start_date_ts + ($range1 * $one_day)));
			
			if ($diff > $range2)
			{
				$duration[] = array($range2, $selected_start_date, date('D jS M', $start_date_ts + ($range2 * $one_day)));
				
				if ($diff >= $range3)
				{
					$duration[] = array($range3, $selected_start_date, date('D jS M', $start_date_ts + ($range3 * $one_day)));
					
					if ($diff >= $range4)
					{
						$duration[] = array($range4, $selected_start_date, date('D jS M', $start_date_ts + ($range4 * $one_day)));
					}		
				}
			}
		}
		
		return $duration;
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
	 * Calculate if user can pay deposit and if so how much that would be
	 *
	 */
	function calculate_deposit($site_id, $price, $start_date, $data)
	{
		// Can only be deposit if it is more than 6 weeks (42 days) from start_date
		if ($this->date_difference("now", $start_date) >= 42)
		{
			// Calculate Deposit
			$data['what_paying'] = "deposit";
			
			// Get site deposit percentage
			// Set default % to 20
			$deposit_perc = 30;
			
			$this->load->model('site_model');
			$site_query = $this->site_model->get_single_row($site_id);
			
			if ($site_query->num_rows() > 0)
			{
				$site_row = $site_query->row();
				
				if (!empty($site_row->deposit_percentage) && $site_row->deposit_percentage !== 0)
				{
					$deposit_perc = $site_row->deposit_percentage;
				}
			}
			
			$data['price'] = 100 * $price;
			$data['deposit_amount'] = round(($price / 100) * $deposit_perc, 2);
			$data['deposit_percentage'] = $deposit_perc;
		}
		else
		{
			$data['what_paying'] = "full amount";
			$data['price'] = 100 * $price;
			$data['deposit_amount'] = 0;
		}
		
		return $data;
	}
	
	
	// --------------------------------------------------------------------------------------------------------------
	/**
	 * Generate Random Password for reset form.
	 *
	 */
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
	
	
	
	// --------------------------------------------------------------------------------------------------------------
	/**
	 * Check entered email address to see if user already exists
	 *
	 */
	function check_email_address()
	{
		$this->load->model('member_model');
			
		// Email Address to match against members.
		$email = $this->input->post('email_address');
		
		// See if email address matches any member accounts that do not have admin access
		$validate = $this->member_model->validate($email);

		// If only 1 row is returned we have a valid user
		if ($validate->num_rows() > 0)
		{
			
			$data = array(
				"status" => "error",
				"message" => "It looks like you already have an account with us. Please login using the form on the left."
			);
			
			echo json_encode($data);
		}
	}
	
	
	
	// --------------------------------------------------------------------------------------------------------------
	/**
	 * Mailchimp List Subscribe.
	 *
	 */
	function mailchimp_list_subscribe($email_address, $first_name, $last_name)
    {
    	require_once '/var/www/vhosts/thebivouac.co.uk/subdomains/booking/httpsdocs/application/third_party/mailchimp-api-class/examples/inc/MCAPI.class.php';
		require_once '/var/www/vhosts/thebivouac.co.uk/subdomains/booking/httpsdocs/application/third_party/mailchimp-api-class/examples/inc/config.inc.php';
		
		$api = new MCAPI($apikey);
		
		$merge_vars = array(
			'FNAME' => $first_name, 
			'LNAME' => $last_name
		);
		
		$retval = $api->listSubscribe($listId, $email_address, $merge_vars);
		
		if ($api->errorCode)
		{
			return false;
		} 
		
		return true;
    }	
}

/* End of file booking.php */
/* Location: ./application/controllers/booking.php */	