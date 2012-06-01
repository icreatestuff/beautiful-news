<?php

class Site_model extends CI_Model {

	function __construct()
	{
		parent::__construct();
	}
	
	function get_all()
	{
		$query = $this->db->get('sites');
		return $query;
	}
	
	function get_all_bar_current($id)
	{
		$this->db->where('id <>', $id);
		return $this->db->get('sites');
	}
	
	function get_single_row($id)
	{
		$this->db->where('id', $id);
		return $this->db->get('sites');
	}
	
	function update_row($id, $data)
	{
		$this->db->where('id', $id);
		$this->db->update('sites', $data);
	}
	
	function insert_row($data)
	{
		$this->db->insert('sites', $data);	
	}
	
	function delete_row($data)
	{
		$this->db->delete('sites', $data);
	}
}