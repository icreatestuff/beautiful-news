<?php

class Site_closed_model extends CI_Model {

	function __construct()
	{
		parent::__construct();
	}
	
	function get_all($start_date_ts = 0)
	{
		if ($start_date_ts > 0)
		{
			$start = date('Y-m-d', $start_date_ts);
			
			$sql = "SELECT * FROM site_closed_dates WHERE DATE_FORMAT(start_date, '%Y-%m-%d 00:00:00') > '" . $start . "' ORDER BY start_date";
			return $this->db->query($sql);
		}
		else
		{
			$this->db->from('site_closed_dates')->order_by("start_date", "asc");
			return $this->db->get();
		}
	}
	
	function get_all_for_site($id)
	{
		$this->db->from('site_closed_dates')->where('site_id', $id)->order_by("start_date", "asc");
		return $this->db->get();
	}
	
	function get_single_row($id)
	{
		$this->db->where('id', $id);
		return $this->db->get('site_closed_dates');
	}
	
	function update_row($id, $data)
	{
		$this->db->where('id', $id);
		$this->db->update('site_closed_dates', $data);
	}
	
	function insert_row($data)
	{
		$this->db->insert('site_closed_dates', $data);	
	}
	
	function delete_row($data)
	{
		$this->db->delete('site_closed_dates', $data);
	}
}