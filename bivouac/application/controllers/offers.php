<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Offers extends CI_Controller {

	/**
	 * Index Page for this controller.
	 *
	 */
	public function index()
	{
		$this->load->model('booking_model');
		
		// Get info about offer from offer ID
		$query = $this->booking_model->get_offer($this->input->post('offer_id'));
		
		if ($query->num_rows() > 0)
		{
			$offer = $query->row();
		
			// Use accommodation_id from offer data (unit_id)
			$query = $this->booking_model->get_accommodation($offer->accommodation_id);
			
			if ($query->num_rows() > 0)
			{
				$accommodation = $query->row();
				
				$booking_ref = date('dmY', strtotime($offer->start_date)) . "_" . $accommodation->unit_id . "_";
		
				// Create booking record
				$data = array(
					'site_id'				=> $this->input->post('site_id'),
					'type'					=> 'Offer',
					'booking_ref'			=> $booking_ref,
					'accommodation_ids'		=> $offer->accommodation_id,
					'start_date'			=> $offer->start_date,
					'end_date'				=> $offer->end_date,
					'adults'				=> 1,
					'children'				=> 0,
					'babies'				=> 0,
					'total_price'			=> $offer->discount_price,
					'payment_status'		=> 'unpaid',
					'booking_creation_date'	=> date('Y-m-d H:i:s'),
					'notes'					=> 'This booking was made through an offer. The original price of the accommodation would have been £' . $offer->total_price . '. The discounted price is £' .$offer->discount_price,
					'is_telephone_booking'	=> 'No'			
				);
								
				// Create Booking record
				$this->booking_model->insert_booking_row($data);
				
				// Create Calendar record
				// Get last insert ID from bookings table
				$booking_id = $this->db->insert_id();
				
				
				$calendar_data = array(
					'site_id'					=> $this->input->post('site_id'),
					'accommodation_id'			=> $offer->accommodation_id,
					'booking_id'				=> $booking_id,
					'bunk_barn_guests'			=> 0,
					'small_bunk_barn_guests'	=> 0,
					'start_date'				=> $offer->start_date,
					'end_date'					=> $offer->end_date					
				);
				
				$this->booking_model->insert_calendar_row($calendar_data);
				
				// Close the offer so it can't be used again!
				$this->booking_model->close_offer($offer->id);
				
				$this->session->set_userdata('booking_id', $booking_id);
				
				redirect('offers/guests');				
			}
		}
		else
		{
			echo "That offer has expired";
		}
	}
	
	
	function guests()
	{
		$booking_id = $this->session->userdata('booking_id');
		
		$this->guests_form();
	
		if ($this->form_validation->run() == FALSE)
		{		
			if (!isset($booking_id) || empty($booking_id))
			{
				redirect('booking');
			}
				
			$this->load->view('booking/guests');
		}
		else
		{
			foreach ($_POST as $key => $val)
			{
				$data[$key] = $this->input->post($key);
			}
			
			// Get current booking_ref
			$this->load->model('booking_model');
			$query = $this->booking_model->get_booking($booking_id);
			
			if ($query->num_rows() > 0)
			{
				$booking_ref = $query->row()->booking_ref;
				$booking_ref .= (int) $this->input->post('adults') + (int) $this->input->post('children');
			}
	
			// Update booking with new guest data
			$data = array(
				'adults'		=> $this->input->post('adults'),
				'children'		=> $this->input->post('children'),
				'babies'		=> $this->input->post('babies'),
				'booking_ref'	=> $booking_ref
			);
			
			$this->booking_model->update_booking($booking_id, $data);
			
			redirect('booking/extras/' . $booking_id);
		}
	}
	
	
	function guests_form()
	{
		$this->load->library('form_validation');
		
		$this->form_validation->set_rules('adults', 'Adults', 'trim|required');
		$this->form_validation->set_rules('children', '4-17\'s', 'trim|required');
		$this->form_validation->set_rules('babies', '0-3\'s', 'trim|required');
	}
}

/* End of file offers.php */
/* Location: ./application/controllers/offers.php */