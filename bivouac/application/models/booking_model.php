<?php

class Booking_model extends CI_Model {

	function __construct()
	{
		parent::__construct();
	}
	
	// -----------------------------------------------------------------------------------------------------------------
	// Get Queries
	
	function get_sites()
	{
		$query = $this->db->get('sites');
		return $query;
	}
	
	function get_all()
	{
		$query = $this->db->get('accommodation');
		return $query;
	}
	
	function get_voucher($voucher)
	{
		$this->db->where('name', $voucher);
		return $this->db->get('vouchers');
	}
	
	function get_booking_member($booking_id)
	{
		$sql = "SELECT screen_name, member_id, admin_access FROM bookings AS b, contacts AS c, members AS m WHERE b.contact_id = c.id AND c.member_id = m.id AND b.id = " . $booking_id;
		return $this->db->query($sql);
	}
	
	function get_type($id)
	{
		$sql = "SELECT t.name, t.id FROM accommodation AS a, accommodation_types AS t WHERE a.type = t.id AND a.id = " . $id;
		return $this->db->query($sql);
	}
	
	function get_all_accommodation($type_id = FALSE)
	{
		if ((boolean) $type_id === FALSE)
		{
			$sql = "SELECT a.id, a.name, dogs_allowed, description, type, sleeps, photo_1, additional_per_night_charge, t.name AS type_name FROM accommodation AS a, accommodation_types AS t WHERE a.type = t.id AND a.status = 'open' ORDER BY a.type ASC";

			$query = $this->db->query($sql);
		}
		else
		{
			$sql = "SELECT a.id, a.name, dogs_allowed, description, type, sleeps, photo_1, additional_per_night_charge, t.name AS type_name FROM accommodation AS a, accommodation_types AS t WHERE a.type = t.id AND type = '" . $type_id . "' AND a.status = 'open' ORDER BY a.type ASC";

			$query = $this->db->query($sql);
		}
		
		return $query;
	}
	
	function get_all_accommodation_types()
	{
		return $this->db->get('accommodation_types');
	}
	
	function get_all_dog_accommodation($type_id = FALSE)
	{
		if ((boolean) $type_id === FALSE)
		{
			$sql = "SELECT a.id, a.name, dogs_allowed, description, type, sleeps, photo_1, additional_per_night_charge, t.name AS type_name FROM accommodation AS a, accommodation_types AS t WHERE a.type = t.id AND a.status = 'open' AND a.dogs_allowed = 'yes' ORDER BY a.type ASC";

			$query = $this->db->query($sql);
		}
		else
		{
			$sql = "SELECT a.id, a.name, dogs_allowed, description, type, sleeps, photo_1, additional_per_night_charge, t.name AS type_name FROM accommodation AS a, accommodation_types AS t WHERE a.type = t.id AND type = '" . $type_id . "' AND a.status = 'open' AND a.dogs_allowed = 'yes' ORDER BY a.type ASC";

			$query = $this->db->query($sql);
		}
		
		return $query;
	}

	function get_all_from_accommodation_id($id)
	{
		$this->db->select('start_date, end_date, booking_id')->from('calendar')->where('accommodation_id', $id);
		$query = $this->db->get();
		return $query;
	}
	
	function get_accommodation($id)
	{
		$sql = "SELECT a.id, a.site_id, unit_id, status, additional_per_night_charge, type, a.name, description, sleeps, amenities, bedrooms, photo_1, photo_2, photo_3, photo_4, photo_5, photo_6, at.name AS type_name FROM accommodation AS a, accommodation_types AS at WHERE a.type = at.id AND a.id = " . $id;
		return $this->db->query($sql);
	}
	
	
	function get_bookings_within_two_weeks($id = 0, $start, $end)
	{
		if ($id !== 0)
		{
			$sql = "SELECT start_date, end_date FROM calendar WHERE accommodation_id = " . $id . " AND DATE_FORMAT(calendar.start_date, '%Y-%m-%d 00:00:00') BETWEEN '" . $start . "' AND '" . $end . "' ORDER BY start_date";
		}
		else
		{
			$sql = "SELECT start_date, end_date FROM calendar WHERE DATE_FORMAT(calendar.start_date, '%Y-%m-%d 00:00:00') BETWEEN '" . $start . "' AND '" . $end . "' ORDER BY start_date";
		}
		
		return $this->db->query($sql);
	}
	
	function get_calendar_dates()
	{
		return $this->db->get('calendar');
	}
	
	
	function get_calendar_entries_between_dates($start, $end)
	{
		$sql = "SELECT accommodation_id FROM calendar WHERE DATE_FORMAT(calendar.start_date, '%Y-%m-%d 00:00:00') BETWEEN '" . $start . "' AND '" . $end . "'";
		return $this->db->query($sql);
	}
	
	function get_price()
	{
		$query = $this->db->get('pricing_schema');
		return $query;
	}
	
	function get_high_price($id)
	{
		$sql = "SELECT high_price, at.name, at.id FROM accommodation AS a, accommodation_types AS at WHERE a.id = " . $id . " AND a.type = at.id";
		$query = $this->db->query($sql);
		return $query;
	}
	
	function get_additional_cost($id)
	{
		$sql = "SELECT additional_per_night_charge FROM accommodation WHERE id = " . $id;
		$query = $this->db->query($sql);
		return $query;
	}
	
	function get_total_beds($id)
	{
		$this->db->where('id', $id);
		return $this->db->get('accommodation');
	}
	
	function get_guests()
	{
		$sql = "SELECT accommodation_ids, start_date, end_date, adults, children FROM bookings";
		return $this->db->query($sql);
	}
	
	function get_guests_from_calendar($id)
	{
		$sql = "SELECT start_date, end_date, bunk_barn_guests, small_bunk_barn_guests FROM calendar WHERE accommodation_id = " . $id;
		return $this->db->query($sql);
	}
	
	function get_site_id($id)
	{
		$this->db->select('site_id');
		$this->db->where('id', $id);
		return $this->db->get('bookings');
	}
	
	function get_booking($id)
	{
		$this->db->where('id', $id);
		return $this->db->get('bookings');
	}
	
	function get_unpaid_bookings()
	{
		$this->db->select('id, booking_creation_date');
		$this->db->where('payment_status', 'Unpaid');
		$this->db->or_where('payment_status', 'unpaid');
		return $this->db->get('bookings');
	}
	
	function get_deposit_bookings()
	{
		$this->db->select('id, start_date, booking_creation_date');
		$this->db->where('payment_status', 'Deposit');
		$this->db->or_where('payment_status', 'deposit');
		return $this->db->get('bookings');
	}
	
	function get_bookings_next_two_weeks($start, $end)
	{
		$sql = "SELECT b.id, booking_ref, a.name, start_date, end_date, adults, children, babies, first_name, last_name, total_price, payment_status
				FROM bookings AS b, accommodation AS a, contacts AS c 
				WHERE b.accommodation_ids = a.id
				AND b.contact_id = c.id
				AND DATE_FORMAT(start_date, '%Y-%m-%d 00:00:00') BETWEEN '" . $start . "' AND '" . $end . "' 
				ORDER BY start_date";
				
		return $this->db->query($sql);
	}
	
	function get_bookings_limited($start, $limit = 20)
	{
		$sql = "SELECT b.id, booking_ref, a.name, start_date, end_date, adults, children, babies, first_name, last_name, total_price, amount_paid, payment_status 
				FROM bookings AS b, accommodation AS a, contacts AS c 
				WHERE b.accommodation_ids = a.id
				AND b.contact_id = c.id
				AND DATE_FORMAT(start_date, '%Y-%m-%d 00:00:00') > '" . $start . "' 
				ORDER BY start_date
				LIMIT " . $limit;
				
		return $this->db->query($sql);
	}
	
	function get_recent_bookings($limit = 30)
	{
		$sql = "SELECT b.id, booking_ref, a.name, start_date, end_date, adults, children, babies, first_name, last_name, total_price, amount_paid, payment_status 
				FROM bookings AS b, accommodation AS a, contacts AS c 
				WHERE b.accommodation_ids = a.id
				AND b.contact_id = c.id
				ORDER BY booking_creation_date
				DESC
				LIMIT " . $limit;
				
		return $this->db->query($sql);
	}
	
	function get_all_bookings($site_id = 1)
	{
		$sql = "SELECT b.id, is_telephone_booking, booking_ref, a.name, b.type, start_date, end_date, adults, children, babies, first_name, last_name, total_price, amount_paid, payment_status, house_name, address_line_1, address_line_2, city, county, post_code
				FROM bookings AS b, accommodation AS a, contacts AS c 
				WHERE b.accommodation_ids = a.id
				AND b.contact_id = c.id
				AND b.site_id = '" . $site_id . "' 
				ORDER BY start_date";
				
		return $this->db->query($sql);
	}
	
	function get_contact($id)
	{
		$this->db->where('id', $id);
		return $this->db->get('contacts');
	}
	
	function get_contact_from_booking_id($id)
	{
		$sql = "SELECT c.id, member_id, title, first_name, last_name, birth_day, birth_month, birth_year, house_name, address_line_1, address_line_2, city, county, post_code, daytime_number, mobile_number, email_address FROM contacts AS c, bookings AS b WHERE b.contact_id = c.id AND b.id = " . $id;
		return $this->db->query($sql);
	}
	
	function get_extra_types()
	{
		return $this->db->get('extra_types');
	}
	
	function get_extras($type_id, $site_id)
	{
		$this->db->where('extra_type', $type_id);
		$this->db->where('site_id', $site_id);
		$this->db->where('status', 'open');
		return $this->db->get('extras');
	}
	
	function get_extra_by_id($id)
	{
		$this->db->where('id', $id);
		return $this->db->get('extras');
	}
	
	function get_booked_extras($id)
	{
		$sql = "SELECT extra_id, extra_type, date, name, quantity, nights, purchased_extras.price FROM purchased_extras, extras WHERE purchased_extras.extra_id = extras.id AND purchased_extras.booking_id = " . $id;
		return $this->db->query($sql);
	}
	
	function get_public_holidays()
	{
		return $this->db->get('public_holidays');
	}
	
	function get_all_weddings()
	{
		$this->db->where('type', 'wedding');
		return $this->db->get('bookings');
	}
	
	function purchased_extras_by_id_and_date($id, $date)
	{
		$sql = "SELECT * FROM purchased_extras WHERE extra_id = '" . $id . "' AND date = '" . $date . "'";
		return $this->db->query($sql);
	}
	
	function purchased_extras_by_id($id, $booking_id = null)
	{
		if ($booking_id)
		{
			$sql = "SELECT * FROM purchased_extras WHERE extra_id = " . $id . " AND booking_id = " . $booking_id;
		}
		else
		{
			$sql = "SELECT * FROM purchased_extras WHERE extra_id = " . $id;
		}
		
		return $this->db->query($sql);
	}
	
	function get_all_offers()
	{
		$sql = "SELECT o.id, a.name, start_date, end_date, o.status, total_price, discount_price, percentage_discount
				FROM offers AS o, accommodation AS a
				WHERE o.accommodation_id = a.id";
				
		return $this->db->query($sql);
	}
	
	function get_offer($id)
	{
		$this->db->where('id', $id);
		$this->db->where('status', 'open');
		return $this->db->get('offers');
	}
	
	function get_offer_by_accommodation($id)
	{
		$this->db->where('accommodation_id', $id);
		$this->db->where('status', 'open');
		return $this->db->get('offers');
	}
	
	function close_offer($id)
	{
		$this->db->where('id', $id);
		$this->db->update('offers', array('status' => 'closed'));
	}
	
	// -----------------------------------------------------------------------------------------------------------------
	// Insert Queries
	
	function insert_booking_row($data)
	{
		$this->db->insert('bookings', $data);	
	}
	
	function insert_calendar_row($data)
	{
		$this->db->insert('calendar', $data);	
	}
	
	function insert_extra_purchases($data)
	{
		$this->db->insert_batch('purchased_extras', $data);	
	}
	
	function insert_extra_purchase($data)
	{
		$this->db->insert('purchased_extras', $data);	
	}
	
	function insert_contact_row($data)
	{
		$this->db->insert('contacts', $data);	
	}
	
	function insert_offer_row($data)
	{
		$this->db->insert('offers', $data);
	}
	
	// -----------------------------------------------------------------------------------------------------------------
	// Update Queries
	
	function update_total_price($id, $price)
	{
		$sql = "UPDATE bookings SET total_price = '" . $price . "' WHERE id = " . $id;
		return $this->db->query($sql);
	}
	
	function add_booking_contact($id, $contact_id)
	{
		$sql = "UPDATE bookings SET contact_id = '" . $contact_id . "' WHERE id = " . $id;
		$this->db->query($sql);
	}
	
	function update_contact($id, $data)
	{
		$this->db->where('id', $id);
		$this->db->update('contacts', $data);
	}

	
	function update_booking($id, $data)
	{
		$this->db->where('id', $id);
		$this->db->update('bookings', $data);
	}
	
	// -----------------------------------------------------------------------------------------------------------------
	// Delete Queries
	function delete_purchased_extras($booking_id)
	{
		$this->db->where('booking_id', $booking_id);
		$this->db->delete('purchased_extras');
	}
	
	function delete_booking_record($booking_id)
	{
		$this->db->where('id', $booking_id);
		$this->db->delete('bookings');
	}
	
	function delete_calendar_record($booking_id)
	{
		$this->db->where('booking_id', $booking_id);
		$this->db->delete('calendar');
	}
	
	function cancel_booking($id) 
	{
		$this->db->delete('calendar', array('booking_id' => $id)); 
	}
	
}	