<?php

class Pricing_model extends CI_Model {

	function __construct()
	{
		parent::__construct();
	}
	
	function get_all()
	{
		$this->db->from('pricing_schema')->order_by("start_date", "asc");
		$query = $this->db->get();
		return $query;
	}
	
	function get_all_for_site($id)
	{
		$this->db->from('pricing_schema')->where('site_id', $id)->order_by("start_date", "asc");
		return $this->db->get();
	}
	
	function get_single_row($id)
	{
		$this->db->where('id', $id);
		return $this->db->get('pricing_schema');
	}
	
	function update_row($id, $data)
	{
		$this->db->where('id', $id);
		$this->db->update('pricing_schema', $data);
	}
	
	function insert_row($data)
	{
		$this->db->insert('pricing_schema', $data);	
	}
	
	function delete_row($data)
	{
		$this->db->delete('pricing_schema', $data);
	}
	
	function check_start_date($start_date)
	{
		$this->db->where('start_date', $start_date);
		return $this->db->get('pricing_schema');
	}
}