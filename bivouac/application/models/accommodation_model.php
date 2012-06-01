<?php

class Accommodation_model extends CI_Model {

	function __construct()
	{
		parent::__construct();
	}
	
	function get_types()
	{
		$query = $this->db->get('accommodation_types');
		return $query;
	}
	
	function get_types_for_site($id)
	{
		$this->db->where('site_id', $id);
		$query = $this->db->get('accommodation_types');	
		return $query;
	}
	

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

	function get_all_for_site($id)
	{
		$this->db->select('accommodation.id as id, photo_1, unit_id, status, accommodation.name as name, bedrooms, sleeps, description, additional_per_night_charge, accommodation_types.name as type_name');
		$this->db->from('accommodation');
		$this->db->join('accommodation_types', 'accommodation_types.id = accommodation.type');
		$this->db->where('accommodation.site_id', $id);
		$this->db->where('accommodation.status', 'open');
		return $this->db->get(); 
	}

	function get_single_row($id)
	{
		$this->db->select('a.id as id, a.site_id, dogs_allowed, photo_1, photo_2, photo_3, photo_4, photo_5, photo_6, type, unit_id, description, amenities, status, a.name as name, bedrooms, sleeps, additional_per_night_charge, t.name as type_name');
		$this->db->from('accommodation AS a');
		$this->db->join('accommodation_types AS t', 't.id = a.type');
		$this->db->where('a.id', $id);
		return $this->db->get();
	}
	
	function get_type_row($id)
	{
		$this->db->where('id', $id);
		return $this->db->get('accommodation_types');
	}
	
	function get_unit_code($id)
	{
		$this->db->select('unit_id');
		$this->db->where('id', $id);
		return $this->db->get('accommodation');
	}
	
	function get_calendar()
	{
		return $this->db->get('calendar');
	}
	
	function update_row($id, $data)
	{
		$this->db->where('id', $id);
		$this->db->update('accommodation', $data);
	}
	
	function insert_row($data)
	{
		$this->db->insert('accommodation', $data);	
	}
	
	function insert_type_row($data)
	{
		$this->db->insert('accommodation_types', $data);	
	}
	
	function update_type_row($id, $data)
	{
		$this->db->where('id', $id);
		$this->db->update('accommodation_types', $data);
	}
	
	function delete_row($data)
	{
		$this->db->delete('accommodation', $data);
	}
}