<?php

class Holiday_model extends CI_Model {

	function __construct()
	{
		parent::__construct();
	}
	
	function get_all()
	{
		$this->db->from('public_holidays')->order_by("start_date", "asc");
		$query = $this->db->get();
		return $query;
	}
	
	function get_all_for_site($id)
	{
		$this->db->from('public_holidays')->where('site_id', $id)->order_by("start_date", "asc");
		return $this->db->get();
	}
	
	function get_single_row($id)
	{
		$this->db->where('id', $id);
		return $this->db->get('public_holidays');
	}
	
	function update_row($id, $data)
	{
		$this->db->where('id', $id);
		$this->db->update('public_holidays', $data);
	}
	
	function insert_row($data)
	{
		$this->db->insert('public_holidays', $data);	
	}
	
	function delete_row($data)
	{
		$this->db->delete('public_holidays', $data);
	}
}