<?php

class Report_model extends CI_Model {

	function __construct()
	{
		parent::__construct();
	}
	
	// -----------------------------------------------------------------------------------------------------------------
	// Get Queries
	
	function get_all_extras($site_id)
	{
		$this->db->select('id, name');
		$this->db->where('site_id', $site_id);			
		return $this->db->get('extras');
	}
	
	function get_bookings_with_extra($site_id, $extra_id)
	{
		$sql = "SELECT b.id, booking_ref, accommodation_ids, quantity, a.name, e.name AS extra_name, b.start_date, b.end_date, adults, children, babies, total_price
				FROM bookings AS b, accommodation as a, purchased_extras AS pe, extras AS e 
				WHERE b.id = pe.booking_id
				AND pe.extra_id = e.id
				AND a.id = b.accommodation_ids
				AND b.site_id = '" . $site_id . "' 
				AND pe.extra_id = '" . $extra_id . "'
				ORDER BY start_date";
				
		return $this->db->query($sql);
	}
	
	
	function get_future_bookings($site_id, $start)
	{
		$sql = "SELECT b.id, is_telephone_booking, booking_ref, accommodation_ids, a.name, b.type, start_date, end_date, adults, children, babies, first_name, last_name, total_price, payment_status, house_name, address_line_1, address_line_2, city, county, post_code
				FROM bookings AS b, accommodation AS a, contacts AS c 
				WHERE b.accommodation_ids = a.id
				AND b.contact_id = c.id
				AND b.site_id = '" . $site_id . "' 
				AND DATE_FORMAT(start_date, '%Y-%m-%d 00:00:00') > '" . $start . "'
				ORDER BY start_date";
				
		return $this->db->query($sql);
	}
	
	function get_outstanding_payment_bookings($site_id)
	{
		$sql = "SELECT b.id, booking_ref, accommodation_ids, a.name, start_date, end_date, adults, children, babies, first_name, last_name, email_address, daytime_number, total_price, amount_paid 
				FROM bookings AS b, accommodation AS a, contacts AS c 
				WHERE b.accommodation_ids = a.id
				AND b.contact_id = c.id
				AND b.site_id = '" . $site_id . "' 
				AND b.payment_status != 'fully paid'
				ORDER BY start_date";
				
		return $this->db->query($sql);
	}
	
	function get_bookings_from_end_date($site_id, $end)
	{
		$sql = "SELECT b.id, booking_ref, accommodation_ids, a.name, start_date, end_date, adults, children, babies, first_name, last_name, total_price 
				FROM bookings AS b, accommodation AS a, contacts AS c 
				WHERE b.accommodation_ids = a.id
				AND b.contact_id = c.id
				AND b.site_id = '" . $site_id . "' 
				AND DATE_FORMAT(end_date, '%Y-%m-%d') = '" . $end . "'
				ORDER BY end_date";
				
		return $this->db->query($sql);
	}
	
	function get_bookings_from_start_date($site_id, $start)
	{
		$sql = "SELECT b.id, booking_ref, accommodation_ids, a.name, start_date, end_date, adults, children, babies, first_name, last_name, total_price 
				FROM bookings AS b, accommodation AS a, contacts AS c 
				WHERE b.accommodation_ids = a.id
				AND b.contact_id = c.id
				AND b.site_id = '" . $site_id . "' 
				AND DATE_FORMAT(start_date, '%Y-%m-%d') = '" . $start . "'
				ORDER BY end_date";
				
		return $this->db->query($sql);
	}
	
	function get_hot_tub_bookings($site_id)
	{
		$sql = "SELECT b.id, booking_ref, accommodation_ids, first_name, last_name, pe.date
				FROM bookings AS b, contacts AS c, purchased_extras AS pe
				WHERE pe.extra_id = 12 
				AND pe.booking_id = b.id 
				AND b.contact_id = c.id
				ORDER BY pe.date";
	
		return $this->db->query($sql);
	}
	
	
	
	
	function get_single_booking($id)
	{
		$this->db->where('id', $id);
		return $this->db->get('bookings');
	}
	
	function get_bookings_next_two_weeks($start, $end)
	{
		$sql = "SELECT b.id, booking_ref, a.name, start_date, end_date, adults, children, babies, first_name, last_name, total_price 
				FROM bookings AS b, accommodation AS a, contacts AS c 
				WHERE b.accommodation_ids = a.id
				AND b.contact_id = c.id
				AND DATE_FORMAT(start_date, '%Y-%m-%d 00:00:00') BETWEEN '" . $start . "' AND '" . $end . "' 
				ORDER BY start_date";
				
		return $this->db->query($sql);
	}
	
	function get_contact($id)
	{
		$this->db->where('id', $id);
		return $this->db->get('contacts');
	}
	
	function get_extras($type_id, $site_id)
	{
		$this->db->where('extra_type', $type_id);
		$this->db->where('site_id', $site_id);
		return $this->db->get('extras');
	}
	
	function get_extra_name($id)
	{
		$this->db->select('name');
		$this->db->where('id', $id);
		return $this->db->get('extras');
	}
	
	function get_booked_extras($id)
	{
		$sql = "SELECT name, quantity, nights, purchased_extras.price 
				FROM purchased_extras, extras 
				WHERE purchased_extras.extra_id = extras.id 
				AND purchased_extras.booking_id = " . $id;
				
		return $this->db->query($sql);
	}
	
	function get_accommodation_name($id)
	{
		$this->db->select('name');
		$this->db->where('id', $id);
		return $this->db->get('accommodation');
	}
	
	
	// -----------------------------------------------------------------------------------------------------------------
	// Insert Queries
	

	
	// -----------------------------------------------------------------------------------------------------------------
	// Update Queries
	function update_booking($id, $data)
	{
		$this->db->where('id', $id);
		$this->db->update('bookings', $data);
	}
	
	// -----------------------------------------------------------------------------------------------------------------
	// Delete Queries
	function cancel_booking($id) 
	{
		$this->db->delete('calendar', array('booking_id' => $id)); 
	}
}