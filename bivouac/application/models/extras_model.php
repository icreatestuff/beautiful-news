<?php

class Extras_model extends CI_Model {

	function __construct()
	{
		parent::__construct();
	}
	
	function get_types()
	{
		$query = $this->db->get('extra_types');
		return $query;
	}

	function get_sites()
	{
		$query = $this->db->get('sites');
		return $query;
	}
	
	function get_all()
	{
		$query = $this->db->get('extras');
		return $query;
	}

	function get_all_for_site($id)
	{
		$this->db->select('extras.id as extra_id, photo_1, status, extras.name as extra_name, description, price, extra_types.name as type_name');
		$this->db->from('extras');
		$this->db->join('extra_types', 'extra_types.id = extras.extra_type');
		$this->db->where('site_id', $id);
		$query = $this->db->get();
		return $query;
	}

	
	function get_single_row($id)
	{
		$this->db->where('id', $id);
		return $this->db->get('extras');
	}
	
	function update_row($id, $data)
	{
		$this->db->where('id', $id);
		$this->db->update('extras', $data);
	}
	
	function insert_row($data)
	{
		$this->db->insert('extras', $data);	
	}
	
	function insert_type_row($data)
	{
		$this->db->insert('extra_types', $data);	
	}
	
	function delete_row($data)
	{
		$this->db->delete('extras', $data);
	}
}