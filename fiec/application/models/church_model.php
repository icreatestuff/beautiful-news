<?php
class Church_model extends CI_Model {
	function get_all_churches()
	{
		return $this->db->get('ci_churches');		
	}
}