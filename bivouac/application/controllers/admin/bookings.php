<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Bookings extends MY_Controller {

	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * Index Page for this controller.
	 *
	 */
	public function index()
	{	
		$data['sites'] = $this->data['sites'];
		$data['current_site'] = $this->data['current_site'];
		
		// Get the 30 most recent bookings
		// $start = date('Y-m-d');
		
		$this->load->model('booking_model');
		$data['bookings'] = $this->booking_model->get_recent_bookings();
		
		$this->load->view('admin/bookings/index', $data);
	}
	
	
	public function all_bookings()
	{
		$data['sites'] = $this->data['sites'];
		$data['current_site'] = $this->data['current_site'];
		
		$this->load->model('booking_model');
		$data['bookings'] = $this->booking_model->get_all_bookings($this->session->userdata('site_id'));
		
		$this->load->view('/admin/bookings/all_bookings', $data);
	}
	
	
	public function overview()
	{
		$data['sites'] = $this->data['sites'];
		$data['current_site'] = $this->data['current_site'];
		
		if ($this->uri->segment(4) === FALSE || !is_numeric($this->uri->segment(4)))
		{
			redirect('admin/bookings/index');
		}
		else
		{
			$booking_id = $this->uri->segment(4);
		
			//Get Booking Details
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
				if (is_numeric($accommodation_id))
				{
					$data['accommodation_details'][] = $this->booking_model->get_accommodation($accommodation_id);
				}
			}
			
			// Get Contact Details
			$data['contact_details'] = $this->booking_model->get_contact($contact_id);
			
			// Get Extras
			$data['extras'] = $this->booking_model->get_booked_extras($booking_id);
			
			$this->load->view('admin/bookings/overview', $data);
		}
	}
	
	
	public function edit_booking()
	{
		if (!$this->uri->segment(4))
		{
			redirect('/admin/bookings/all_bookings');
		}
		else
		{
			if (isset($_POST) && !empty($_POST) && (count($_POST) > 0))
			{
				// Process updates on booking
				$booking_id = $this->input->post('id');
				$payment_status = $this->input->post('payment_status');
				$accommodation_ids = $this->input->post('accommodation_ids');
				
				// Get current booking data
				$this->load->model('booking_model');
				$booking_query = $this->booking_model->get_booking($booking_id);
				
				if ($booking_query->num_rows > 0)
				{
					$booking = $booking_query->row();
					$price = $booking->total_price;
					$site_id = $booking->site_id;
					$duration = round((strtotime($booking->end_date) - strtotime($booking->start_date)) / (60 * 60 * 24));
				}
				
				$data = array(
					"notes" 				=> $this->input->post('notes'),
					"is_telephone_booking"	=> $this->input->post('is_telephone_booking')
				);
				
				// Payment Status Changes
				if (!empty($payment_status))
				{
					$data['payment_status'] = $payment_status;
					
					// Set amount paid
					if ($payment_status === "deposit")
					{	
						$data['amount_paid'] = $this->calculate_deposit($site_id, $price);
					} 
					else if ($payment_status === "fully paid")
					{
						$data['amount_paid'] = $price;
					}
					else
					{
						$data['amount_paid'] = 0;
					}
				}
				
				// Accommodation Changes
				if (!empty($accommodation_ids))
				{
					// Remove all current calendar entries
					$this->booking_model->delete_calendar_record($booking_id);
					
					// Add all new necessary calendar entries
					foreach ($accommodation_ids as $id)
					{
						$calendar_data = array(
							'accommodation_id' 			=> $id,
							'bunk_barn_guests' 			=> 0,
							'small_bunk_barn_guests'	=> 0, 
							'start_date'				=> $booking->start_date,
							'end_date'					=> $booking->end_date,
							'booking_id'				=> $booking_id,
							'site_id'					=> 1
						);
					
						$this->booking_model->insert_calendar_row($calendar_data);
					}
					
					// Calculate price difference of old accommodation compared to new.
					$accommodation_ids = array_filter($accommodation_ids);
			
					$data['accommodation_ids'] = implode("|", $accommodation_ids);
					
					$price = 0;
					
					// Calculate total_price and if there are any bunk barn guests?
					foreach ($accommodation_ids as $id)
					{
						if (!empty($id))
						{
							$accommodation_query = $this->booking_model->get_accommodation($id);
						
							if ($accommodation_query->num_rows > 0)
							{
								$accommodation_row = $accommodation_query->row();
				
								$ppn = $this->get_price_per_night($id, $booking->start_date);
								
								$price = $price + (($ppn * $duration) + ($accommodation_row->additional_per_night_charge * $duration));		
							}
						}
					}
					
					$data['total_price'] = $price;
					
					if ($booking->payment_status === "fully paid" && $booking->total_price == $booking->amount_paid)
					{
						// Calculate difference in old total and newly calculated totals.
						$refund = $booking->amount_paid - $price;
						
						if ($refund > 0)
						{
							$this->session->set_flashdata("user_message", "Because of the accommodation changes you have made to this booking you will need to refund the customer £" . $refund);
						}
					}
				}
				
				$this->booking_model->update_booking($booking_id, $data);
				
				redirect('/admin/bookings/all_bookings');
			}
			else
			{
				$data['sites'] = $this->data['sites'];
				$data['current_site'] = $this->data['current_site'];
			
				$booking_id = $this->uri->segment(4);
				
				// Get saved booking data.
				$this->load->model('booking_model');
				$data['bookings'] = $this->booking_model->get_booking($booking_id);
				
				if ($data['bookings']->num_rows() > 0)
				{
					$row = $data['bookings']->row();
									
					// See if there is a voucher to process for phone booking
					$voucher = $this->session->userdata('voucher');
					$this->session->unset_userdata('voucher');
					
					if (!isset($voucher) OR empty($voucher))
					{
						$voucher = FALSE;
					}
					
					// If voucher exists
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
							if ($row->type !== "voucher")
							{
								// Are we within the start_date and end_date of the voucher?
								if ($this->check_in_range($voucher_row->start_date, $voucher_row->end_date, "now"))
								{
									// Now check if there is a valid_from and valid_to and whether arrival date is between those
									if ($this->check_in_range($voucher_row->valid_from, $voucher_row->valid_to, $row->start_date))
									{
										// Get price of accommodation only by removing total price of extras from total_price
										// Get extras
										$extras_total_price = 0;
										$extras = $this->booking_model->get_booked_extras($booking_id);
										
										foreach($extras->result() as $extra)
										{
											$extras_total_price = (int) $extras_total_price + (int) $extra->price;
										}
									
										$accom_price = $row->total_price - $extras_total_price;
										
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
										$price = $row->total_price;	
									}							
								}
								else
								{
									$data['voucher_message'] = 'Sorry, that voucher has expired';
									$price = $row->total_price;	
								}
							}
							else
							{
								// A voucher has already been applied!
								$data['voucher_message'] = 'Sorry, a voucher has already been applied to this booking';
								$price = $row->total_price;	
							}
						}
						else
						{
							$data['voucher_message'] = 'Sorry, that voucher does not exist';
							$price = $row->total_price;
						}
					}
				
					// Get a list of all accommodation available on the booking start date and for the duration
					// they have already selected. Useful for switching a customer to a different unit.
					
					// Get Accommodation Details
					$accommodation_ids = trim($row->accommodation_ids, "|");
					$accommodation_ids = explode("|", $accommodation_ids);
					foreach ($accommodation_ids as $accommodation_id)
					{
						$data['accommodation_details'][] = $this->booking_model->get_accommodation($accommodation_id);
					} 
					
					$duration = round((strtotime($row->end_date) - strtotime($row->start_date)) / (60 * 60 * 24));
					$data['accommodation_list'] = $this->get_available_accommodation($row->start_date, $duration, $accommodation_ids);	
				}
				
				$this->load->view('/admin/bookings/edit_booking', $data);
			}
		}
	}
	
	
	public function cancel_booking()
	{
		// Get ID from POST
		$id = $this->input->post('id');
		
		// Delete all calendar entries associated with booking id
		$this->load->model('booking_model');
		$this->booking_model->cancel_booking($id);
		
		// Calculate refund amount if full amount has been paid.
		// Get payment_status, total_price and amount_paid
		$price_query = $this->booking_model->get_booking($id);
		
		if ($price_query->num_rows() > 0)
		{
			$price_row = $price_query->row();
			
			if ($price_row->payment_status === "fully paid" && $price_row->total_price == $price_row->amount_paid)
			{
				// Calculate deposit and remove from total amount to get refundable amount
				// Get site deposit percentage
				// Set default % to 20
				$deposit_perc = 30;
				$site_id = 1; // This may need to be set dynamically in the future
				
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
				
				$deposit = round(($price_row->total_price / 100) * $deposit_perc, 2);
				$refund = $price_row->total_price - $deposit;
			}
		}
		
		// Set payment_status to 'cancelled' for this booking.
		$this->booking_model->update_booking($id, array('payment_status' => 'cancelled'));
		
		// return refundable amount and success message
		$message = "Booking successfully cancelled.";
		
		if (isset($refund) && $refund > 0)
		{
			$message .= " Please refund this customer £" . $refund;
		}
		
		echo $message;
	}
	
	
	// ---------------------------------------------------------------------------------------------------------------
	/**
	 * Get all closed dates
	 *
	 */
	public function send_receipt()
	{
		$data['sites'] = $this->data['sites'];
		$data['current_site'] = $this->data['current_site'];
		
		// Send data to view for HTML email version
		// Get Booking Details		
		$this->load->model('booking_model');
		$booking_id = $this->uri->segment(4);
		
		if (isset($booking_id) && !empty($booking_id))
		{
			$data['booking_details'] = $this->booking_model->get_booking($booking_id);
			
			if ($data['booking_details']->num_rows() > 0)
			{
				$booking = $data['booking_details']->row();
				$accommodation_ids = trim($booking->accommodation_ids, "|");
				$accommodation_ids = explode("|", $accommodation_ids);
				$contact_id = $booking->contact_id;
				
				$data = $this->receipt_calculate_deposit($booking->site_id, $booking->total_price, $booking->start_date, $data);
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
				$contact = $data['contact_details']->row();
						
				$this->load->library('email');
				
				// Make sure we're sending html email
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
					echo 'error:<br />';
					echo $this->email->print_debugger();
					
				}	
				else
				{
					$this->load->view('admin/bookings/receipt_sent', $data);
				}
			}
		}
	}
	
	
	// ---------------------------------------------------------------------------------------------------------------
	/**
	 * Get available accommodation from start_date and duration
	 *
	 */
	public function get_available_accommodation($post_start_date, $post_duration, $current_accommodation)
	{		
		// Format dates
		$start_date_ts = strtotime($post_start_date);
		$start_date = date('Y-m-d H:i:s', $start_date_ts);
		
		$end_date_ts = $start_date_ts + (60 * 60 * 24 * $post_duration);
		$end_date_ts_plus_one = $end_date_ts + (60 * 60 * 24);
		$end_date = date('Y-m-d H:i:s', $end_date_ts);
		$end_date_plus_one = date('Y-m-d H:i:s', $end_date_ts_plus_one);
		
		
		// Get all calendar entries whose start_date is between chosen start dates and end date
		$this->load->model('booking_model');
		//$calendar_query = $this->booking_model->get_calendar_entries_between_dates($start_date, $end_date_plus_one);
		
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
					// We're not going to allow changes to the Bunk Barn so just remove it
					if ($row->type_name == "Bunk Barn")
					{
						unset($all_accommodation[$key]);
					}
				
					// Check if booking start_date is between calendar start_date and end_date
					if ($this->check_in_range($calendar->start_date, $calendar->end_date, $start_date))
					{
						// It is between calendar dates so remove this accom id in a minute
						if ($row->id === $accommodation_id)
						{
							// Check if it is a currenlty selected one on the booking.
							// If it is it needs to stay in the array
							if (!in_array($accommodation_id, $current_accommodation))
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
				$accommodation_list .= $this->create_accommodation_list($all_accommodation, $accommodation_list, $start_date, $post_duration);
			}
			else
			{
				$accommodation_list .= "<li class='clearfix type-header'><h3>Sorry there are no other units available for these dates.</h3></li>";
			}
		}

		return $accommodation_list;
	}
	
	
	function create_accommodation_list($all_accommodation, $accommodation_list, $start_date, $duration)
	{
		$type_header = "";
		
		foreach ($all_accommodation as $accommodation)
		{
			// Calculate price for this accommodation for this timeframe
			$ppn = $this->get_price_per_night($accommodation->id, $start_date);
			
			$price = ($ppn * $duration) + ($accommodation->additional_per_night_charge * $duration);
		
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
			
			// Set up headers
			if ($accommodation->type_name !== $type_header) 
			{
				$type_header = $accommodation->type_name;
				$accommodation_list .= "<li class='clearfix type-header'><h1>" . $type_header . "s</h1></li>";
			}
		
			$accommodation_list .= "<li class='clearfix'><h2>" . $accommodation->name . "</h2>";
			$accommodation_list .= "<div class='accommodation-checkbox-container'><input type='checkbox' name='accommodation_ids[]' value='" . $accommodation->id . "' class='accommodation-checkbox' /></div></li>";
		}
		
		return $accommodation_list;
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
	
	public function logout()
	{
		// Destroy the session
		$this->session->sess_destroy();
		
		// Return to booking homepage
		redirect('admin/bookings');
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
	 * Calculate Deposit Amount
	 *
	 */
	function calculate_deposit($site_id, $price)
	{
		// Get site deposit percentage
		// Set default % to 20
		$deposit_perc = 20;
		
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
		
		$deposit_amount = round(($price / 100) * $deposit_perc, 2);
		
		return $deposit_amount;
	}
	
	
	// ---------------------------------------------------------------------------------------------------------------
	/**
	 * Calculate if user can pay deposit and if so how much that would be
	 *
	 */
	function receipt_calculate_deposit($site_id, $price, $start_date, $data)
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

}

/* End of file bookings.php */
/* Location: ./application/controllers/admin/bookings.php */