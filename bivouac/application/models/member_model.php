<?php

class Member_model extends CI_Model {

	function __construct()
	{
		parent::__construct();
	}
	
	function validate($username)
	{
		$this->db->where('username', $username);
		return $this->db->get('members');
	}
	
	function register($data)
	{
		$this->db->insert('members', $data);
	}
	
	function booking_member_check($member_id, $booking_id)
	{
		$sql = "SELECT booking_ref FROM bookings AS b, contacts AS c, members AS m WHERE b.contact_id = c.id AND c.member_id = m.id AND m.id = '" . $member_id . "' AND b.id = ". $booking_id;
		return $this->db->query($sql);
	}
	
	function get_member_bookings($member_id)
	{
		$sql = "SELECT b.id, booking_ref, start_date, end_date, total_price, amount_paid, payment_status FROM bookings AS b, contacts AS c WHERE c.id = b.contact_id AND c.member_id = " . $member_id;
		return $this->db->query($sql);
	}
	
	function get_member_info($member_id)
	{
		$this->db->where('member_id', $member_id);
		return $this->db->get('contacts');
	}
	
	function get_member_email($member_id)
	{
		$this->db->select('email_address');
		$this->db->where('id', $member_id);
		return $this->db->get('members');
	}
	
	function update_member($data, $id)
	{
		$this->db->where('id', $id);
		$this->db->update('members', $data);	
	}
	
	function update_contact($data, $id)
	{
		$this->db->where('member_id', $id);
		$this->db->update('contacts', $data);	
	}
	
	function update_contact_email($data, $id)
	{
		$this->db->where('member_id', $id);
		$this->db->update('contacts', $data);	
	}
	
	function update_booking($data, $id)
	{
		$this->db->where('id', $id);
		$this->db->update('bookings', $data);	
	}
}