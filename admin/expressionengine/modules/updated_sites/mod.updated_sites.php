<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * ExpressionEngine - by EllisLab
 *
 * @package		ExpressionEngine
 * @author		EllisLab Dev Team
 * @copyright	Copyright (c) 2003 - 2012, EllisLab, Inc.
 * @license		http://expressionengine.com/user_guide/license.html
 * @link		http://expressionengine.com
 * @since		Version 2.0
 * @filesource
 */
 
// ------------------------------------------------------------------------

/**
 * ExpressionEngine Updates Sites Module
 *
 * @package		ExpressionEngine
 * @subpackage	Modules
 * @category	Update File
 * @author		EllisLab Dev Team
 * @link		http://expressionengine.com
 */
class Updated_sites {

	var $return_data	= ''; 		// Bah!
	var $LB				= "\r\n";	// Line Break for Entry Output
	
	var $id				= 1;		// Id of Configuration
	var $allowed		= array();
	var $prune			= 500;
	var $throttle		= 15;		 // Minutes between pings

	/**
	  * Constructor
	  */
	function Updated_sites()
	{
		// Make a local reference to the ExpressionEngine super object
		$this->EE =& get_instance();
	}

	// ------------------------------------------------------------------------

	/**
	  * Incoming
	  * 
	  * Incoming MetaWeblog API Requests
	  *
	  * @access	public
	  * @return	mixed
	  */
	function incoming()
	{
		//  Load the XML-RPC Files
		$this->EE->load->library('xmlrpc');
		$this->EE->load->library('xmlrpcs');

		//  Specify Functions

		$functions = array( 'weblogUpdates.extendedPing' => array(
																  'function' => 'Updated_sites.extended',
																  'signature' => array(array('string', 'string','string', 'string')),
																  'docstring' => 'Extended Pings for An EE Site'),
							'weblogUpdates.ping' 		 => array(
																  'function' => 'Updated_sites.regular',
																  'signature' => array(array('string', 'string')),
																  'docstring' => 'Weblog.com Pings for An EE Site')
							);

		//  Instantiate the Server Class
		$this->EE->xmlrpcs->initialize(array('functions' => $functions, 'object' => $this));
		$this->EE->xmlrpcs->serve();
	}

	// ------------------------------------------------------------------------

	/**
	  * Load Config
	  * 
	  * Load Configuration Options
	  *
	  * @access	private
	  * @return	void
	  */
	function _load_config()
	{
		$this->EE->lang->loadfile('updated_sites');
		
		$this->id = ( ! $this->EE->input->get('id')) ? '1' : $this->EE->input->get_post('id');
		
		$query = $this->EE->db->get_where('updated_sites', array('updated_sites_id' => $this->id));
		
		if ($query->num_rows() > 0)
		{
   			$row = $query->row_array();
		
			$this->allowed = explode("\n", trim($row['updated_sites_allowed']));
			$this->prune	= $row['updated_sites_prune'];
		}
	}

	// ------------------------------------------------------------------------

	/**
	  * Extended
	  * 
	  * Extended Ping
	  *
	  * @access	public
	  * @param	mixed
	  * @return	string
	  */
	function extended($plist)
	{
		$parameters = $plist->output_parameters();
		
		$this->_load_config();
		
		if ($this->check_urls(array($parameters['1'], $parameters['2'], $parameters['3'])) !== TRUE)
		{
			return $this->error($this->EE->lang->line('invalid_access'));
		}
		
		if ($this->throttle_check($parameters['1']) !== TRUE)
		{
			return $this->error(str_replace('%X', $this->throttle, $this->EE->lang->line('too_many_pings')));
		}
		
		$data = array('ping_site_name'	=> $this->EE->security->xss_clean(strip_tags($parameters['0'])),
					  'ping_site_url'	=> $this->EE->security->xss_clean(strip_tags($parameters['1'])),
					  'ping_site_check'	=> $this->EE->security->xss_clean(strip_tags($parameters['2'])),
					  'ping_site_rss'	=> $this->EE->security->xss_clean(strip_tags($parameters['3'])),
					  'ping_date'		=> $this->EE->localize->now,
					  'ping_ipaddress'	=> $this->EE->input->ip_address(),
					  'ping_config_id'	=> $this->id);
					  
		$this->EE->db->insert('updated_site_pings', $data); 
		
		return $this->success();
	}

	// ------------------------------------------------------------------------

	/**
	  * regular
	  * 
	  * Regular/Decaf Channels.com Ping
	  *
	  * @access	public
	  * @param	mixed
	  * @return	string
	  */
	function regular($plist)
	{
		$parameters = $plist->output_parameters();
		
		$this->_load_config();
		
		if ($this->check_urls(array($parameters['1'])) !== TRUE)
		{
			return $this->error($this->EE->lang->line('invalid_access'));
		}
		
		if ($this->throttle_check($parameters['1']) !== TRUE)
		{
			return $this->error(str_replace('%X', $this->throttle, $this->EE->lang->line('too_many_pings')));
		}
		
		$data = array('ping_site_name'	=> $this->EE->security->xss_clean(strip_tags($parameters['0'])),
					  'ping_site_url'	=> $this->EE->security->xss_clean(strip_tags($parameters['1'])),
					  'ping_date'		=> $this->EE->localize->now,
					  'ping_ipaddress'	=> $this->EE->input->ip_address(),
					  'ping_config_id'	=> $this->id);
					  
		$this->EE->db->insert('updated_site_pings', $data); 
		
		return $this->success();
	}

	// ------------------------------------------------------------------------

	/**
	  * Check URLs
	  * 
	  * Validate Incoming URLs
	  *
	  * @access	public
	  * @param	array
	  * @return	bool
	  */
	function check_urls($urls)
	{
		if ( ! is_array($urls) OR count($urls) == 0 OR ! is_array($this->allowed) OR count($this->allowed) == 0)
		{
			return FALSE;
		}
		
		$approved = 'n';
		
		for($i=0, $s = count($urls); $i < $s && $approved == 'n'; ++$i)
		{
			if (trim($urls[$i]) == '')
			{
				continue;
			}
			
			if (stristr($urls[$i], '{') !== FALSE OR stristr($urls[$i], '}') !== FALSE)
			{
				return FALSE;
			}
			
			for	($l=0, $sl = count($this->allowed); $l < $sl && $approved == 'n'; ++$l)
			{
				if (trim($this->allowed[$l]) == '') continue;
				
				if (stristr($urls[$i], $this->allowed[$l]) !== FALSE)
				{
					$approved = 'y';
				}
			}
		}
		
		if ($approved == 'n')
		{
			return FALSE;
		}
		
		return TRUE;
	}

	// ------------------------------------------------------------------------

	/**
	  * Throttle_check
	  * 
	  * Security Check
	  *
	  * @access	public
	  * @param	string
	  * @return	bool
	  */
	function throttle_check($url)
	{

		//  Throttling - Only one ping every X minutes
		$or = "(ping_site_url = '".$this->EE->db->escape_str($url)."' OR ping_ipaddress = '".$this->EE->input->ip_address()."')";

		$this->EE->db->where($or, NULL, FALSE);
		$this->EE->db->where('ping_date >', $this->EE->localize->now-($this->throttle*60));
		$this->EE->db->from('updated_site_pings');
		
		$count = $this->EE->db->count_all_results();
		
							 
		if ($count > 0)
		{
			return FALSE;
		}  
		
		return TRUE;
	}

	// ------------------------------------------------------------------------

	/**
	  * Error
	  * 
	  * XML-RPC Error Message
	  *
	  * @access		public
	  * @param		string
	  * @return		mixed
	  */
	function error($message)
	{
		return $this->EE->xmlrpc->send_error_message('401', $message);
	}

	// ------------------------------------------------------------------------

	/**
	  * Success
	  * 
	  * USAGE: So Long and Thanks for All the Fish!
	  *
	  * @access	public
	  * @return	void
	  */
	function success()
	{
		/** ----------------------------------
		/**  Prune Database
		/** ----------------------------------*/
			
		srand(time());
	
		if ((rand() % 100) < 5) 
		{			  
			if ( ! is_numeric($this->prune) OR $this->prune == 0)
			{
				$this->prune = 500;
			}

			$this->EE->db->select_max('ping_id');
			$query = $this->EE->db->get('updated_site_pings');
			
			if ($query->num_rows() > 0)
			{
   				$row = $query->row_array();

				$this->EE->db->where('ping_id <', $row['ping_id'] -$this->prune);
				$this->EE->db->delete('updated_site_pings');
			}
		}

		// Send Success Message
		$response = array(
                 array(
                        'flerror' => array(FALSE, 'boolean'),
                        'message' => array($this->EE->lang->line('successful_ping'), 'string')
                     ),
                 'struct');
		
		return $this->EE->xmlrpc->send_response($response);
	}

	// ------------------------------------------------------------------------

	/**
	  * Pings
	  * 
	  * Entries Tag
	  *
	  * @access		public
	  * @return		mixed
	  */
	function pings()
	{
		// Build query
		$sql = "SELECT m.* FROM exp_updated_site_pings m, exp_updated_sites s
				WHERE m.ping_config_id = s.updated_sites_id ";
				
		if ($which = $this->EE->TMPL->fetch_param('which'))
		{
			$sql .= $this->EE->functions->sql_andor_string($which, 'updated_sites_short_name', 's');
		}
			
		$order  = $this->EE->TMPL->fetch_param('orderby');
		$sort	= $this->EE->TMPL->fetch_param('sort');
		
		switch($order)
		{
			case 'name' :
				$sql .= " ORDER BY m.ping_date ";
			break;
			case 'url' : 
				$sql .= " ORDER BY m.ping_site_url ";
			break;
			case 'rss' :
				$sql .= " ORDER BY m.ping_site_url ";
			break;
			default:
				$sql .= " ORDER BY m.ping_date ";
			break;
		}
		
		if ($sort == FALSE OR ($sort != 'asc' AND $sort != 'desc'))
		{
			$sort = "desc";
		}
		
		$sql .= $sort;
		
	
		if ( ! $this->EE->TMPL->fetch_param('limit'))
		{
			$sql .= " LIMIT 100";
		}
		else
		{
			$sql .= " LIMIT ".$this->EE->TMPL->fetch_param('limit');
		}

		$query = $this->EE->db->query($sql);
		
			if ($query->num_rows() == 0)
		{
			return $this->EE->TMPL->no_results();
		}
		
		$total_results = count($query->result_array());
	
		foreach($query->result_array() as $count => $row)
		{
			$tagdata = $this->EE->TMPL->tagdata;
		
			$row['count']			= $count+1;
			$row['total_results']	= $total_results;

			// Conditionals

			$tagdata = $this->EE->functions->prep_conditionals($tagdata, $row);

			// Parse "single" variables

			foreach ($this->EE->TMPL->var_single as $key => $val)
			{
				// parse {switch} variable

				if (strncmp($key, 'switch', 6) == 0)
				{
					$sparam = $this->EE->functions->assign_parameters($key);
					$sw = '';
					
					if (isset($sparam['switch']))
					{
						$sopt = explode("|", $sparam['switch']);
						
						if (count($sopt) == 2)
						{
							if (isset($switch[$sparam['switch']]) AND $switch[$sparam['switch']] == $sopt['0'])
							{
								$switch[$sparam['switch']] = $sopt['1'];
								
								$sw = $sopt['1'];
							}
							else
							{
								$switch[$sparam['switch']] = $sopt['0'];
								
								$sw = $sopt['0'];
							}
						}
					}
					
					$tagdata = $this->EE->TMPL->swap_var_single($key, $sw, $tagdata);
				}

				// {ping_date}

				if (strncmp($key, 'ping_date', 9) == 0)
				{
					if ( ! isset($row['ping_date']) OR $row['ping_date'] == 0)
					{
						$date = '-';
					}
					else
  					{
						$date = $this->EE->localize->decode_date($val, $row['ping_date']);
					}
				
					$tagdata = $this->EE->TMPL->swap_var_single($key, $date, $tagdata);
				}

				// Remaining Data

				if (in_array($key, array('ping_site_name', 'ping_site_url', 'ping_site_check', 'ping_site_rss', 'ping_ipaddress')))
				{
					$rdata = ( ! isset($row[$key]) OR $row[$key] == '') ? '-' : $row[$key];
				
					$tagdata = $this->EE->TMPL->swap_var_single($val, $rdata, $tagdata);
				}
			}
			
			$this->return_data .= $tagdata;
		}
		
		return $this->return_data;
	}
}


/* End of file mod.updated_sites.php */
/* Location: ./system/expressionengine/modules/updated_sites/mod.updated_sites.php */