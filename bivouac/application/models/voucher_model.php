<?php

class Voucher_model extends CI_Model {

	function __construct()
	{
		parent::__construct();
	}
	
	function get_all()
	{
		$this->db->from('vouchers')->order_by("start_date", "asc");
		$query = $this->db->get();
		return $query;
	}
	
	function get_all_for_site($id)
	{
		$this->db->from('vouchers')->where('site_id', $id)->order_by("start_date", "asc");
		return $this->db->get();
	}
	
	function get_single_row($id)
	{
		$this->db->where('id', $id);
		return $this->db->get('vouchers');
	}
	
	function update_row($id, $data)
	{
		$this->db->where('id', $id);
		$this->db->update('vouchers', $data);
	}
	
	function insert_row($data)
	{
		$this->db->insert('vouchers', $data);	
	}
	
	function delete_row($data)
	{
		$this->db->delete('vouchers', $data);
	}
}