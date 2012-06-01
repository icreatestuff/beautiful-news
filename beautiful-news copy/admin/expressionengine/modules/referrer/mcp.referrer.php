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
 * ExpressionEngine Referrer Module
 *
 * @package		ExpressionEngine
 * @subpackage	Modules
 * @category	Modules
 * @author		EllisLab Dev Team
 * @link		http://expressionengine.com
 */
class Referrer_mcp {

	/**
	  *  Constructor
	  */
	function Referrer_mcp( $switch = TRUE )
	{
		// Make a local reference to the ExpressionEngine super object
		$this->EE =& get_instance();
		
		$base_url = BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=referrer';
		
		$this->EE->cp->set_right_nav(array(
                'view_referrers'        => $base_url.AMP.'method=view',
                'clear_referrers'       => $base_url.AMP.'method=clear',
                'referrer_preferences'  => BASE.AMP.'C=admin_system'.AMP.'M=tracking_preferences'
		    ));		
	}

	// --------------------------------------------------------------------

	/**
	  *  Referrer Home Page
	  */
	function index()
	{
		$vars['cp_page_title'] = $this->EE->lang->line('referrers');

		$vars['num_referrers'] = $this->EE->db->count_all('referrers');

		$this->EE->load->library('javascript');
		$this->EE->javascript->compile();

		return $this->EE->load->view('index', $vars, TRUE);
	}

	// --------------------------------------------------------------------

	/**
	  *  View Referrers
	  */
	function view()
	{		
		$this->EE->load->library('pagination');
		$this->EE->load->library('javascript');
		$this->EE->load->library('table');
		$this->EE->load->helper('form');

		$this->EE->cp->set_breadcrumb(BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=referrer', $this->EE->lang->line('referrers'));

		$vars['cp_page_title'] = $this->EE->lang->line('view_referrers');

		$this->EE->jquery->tablesorter('.mainTable', '{
			headers: {5: {sorter: false}},
			widgets: ["zebra"]
		}');

		$this->EE->javascript->output(array(
				'$(".toggle_all").toggle(
					function(){
						$("input.toggle").each(function() {
							this.checked = true;
						});
					}, function (){
						var checked_status = this.checked;
						$("input.toggle").each(function() {
							this.checked = false;
						});
					}
				);')
		);

		$this->EE->cp->add_to_foot('<script type="text/javascript">function showHide(entryID, htmlObj, linkType) {

				extTextDivID = ("extText" + (entryID));
				extLinkDivID = ("extLink" + (entryID));

				if (linkType == "close")
				{
					document.getElementById(extTextDivID).style.display = "none";
					document.getElementById(extLinkDivID).style.display = "block";
					htmlObj.blur();
				}
				else
				{
					document.getElementById(extTextDivID).style.display = "block";
					document.getElementById(extLinkDivID).style.display = "none";
					htmlObj.blur();
				}

				}
				</script>');

		$vars['referrers'] = array(); // used to pass referrer info into view, but initialized here in case there are no results.

		$rownum = ($this->EE->input->get_post('rownum') != '') ? $this->EE->input->get_post('rownum') : 0;
		$perpage = 10;

		$search_str = '';
		$search_sql = '';
		$vars['search']['name'] = 'search';

		if ( isset($_GET['search']) OR isset($_POST['search']))
		{
			$search_str = (isset($_POST['search'])) ? stripslashes($_POST['search']) : base64_decode($_GET['search']);
		}

		if ($search_str != '')
		{
			// Load the search helper so we can filter the keywords
			$this->EE->load->helper('search');

			$s = preg_split("/\s+/", sanitize_search_terms($search_str));

			foreach($s as $part)
			{
				if (substr($part, 0, 1) == '-')
				{
					$search_sql .= "CONCAT_WS(' ', ref_from, ref_to, ref_ip, ref_agent) NOT LIKE '%".$this->EE->db->escape_like_str(substr($part, 1))."%' AND ";
				}
				else
				{
					$search_sql .= "CONCAT_WS(' ', ref_from, ref_to, ref_ip, ref_agent) LIKE '%".$this->EE->db->escape_like_str($part)."%' AND ";
				}
			}

			$sql = "WHERE (".substr($search_sql, 0, -4).")";
			
			$vars['search']['value'] = 	sanitize_search_terms($search_str);
		}
		else
		{
			$sql = "";
		}

		$query = $this->EE->db->query("SELECT COUNT(*) AS count FROM exp_referrers ".$sql);

		$vars['num_referrers'] = $query->row('count');
		
		if ($query->row('count')  == 0)
		{
			$vars['message'] = (isset($vars['search']['value'])) ? $this->EE->lang->line('referrer_no_results') : $this->EE->lang->line('no_referrers');
			return $this->EE->load->view('view', $vars, TRUE);
			exit;
		}

		$sites_query = $this->EE->db->query("SELECT site_id, site_label FROM exp_sites");
		$sites = array();

		foreach($sites_query->result_array() as $row)
		{
			$sites[$row['site_id']] = $row['site_label'];
		}

		$query = $this->EE->db->query("SELECT * FROM exp_referrers ".$sql." ORDER BY ref_id desc LIMIT $rownum, $perpage");

		$site_url = $this->EE->config->item('site_url');


		foreach($query->result_array() as $row)
		{

			// From
			$row['ref_from'] = str_replace('http://','',$row['ref_from']);

			if (strlen($row['ref_from']) > 40)
			{
				$from_pieces = explode('/',$row['ref_from']);

				$new_from = $from_pieces['0'].'/';

				for($p=1; $p < count($from_pieces); $p++)
				{
					if (strlen($from_pieces[$p]) + strlen($new_from) <= 40)
					{
						$new_from .= ($p == (count($from_pieces) - 1)) ? $from_pieces[$p] : $from_pieces[$p].'/';
					}
					else
					{
						$new_from .= '&#8230;';
						break;
					}
				}
			}
			else
			{
				$new_from = $row['ref_from'];
			}

			$vars['referrers'][$row['ref_id']]['from_link'] = $this->EE->functions->fetch_site_index().QUERY_MARKER.'URL='.urlencode($row['ref_from']);
			$vars['referrers'][$row['ref_id']]['from_url'] = $new_from;

			// To
			$vars['referrers'][$row['ref_id']]['to_link'] = $this->EE->functions->fetch_site_index().QUERY_MARKER.'URL='.urlencode($row['ref_to']);
			$vars['referrers'][$row['ref_id']]['to_url'] = '/'.ltrim(str_replace($site_url, '', $row['ref_to']), '/');

			// Date
			$vars['referrers'][$row['ref_id']]['date'] = ($row['ref_date'] != '' AND $row['ref_date'] != 0) ? $this->EE->localize->set_human_time($row['ref_date']) : '-';

			// IP
			$vars['referrers'][$row['ref_id']]['referrer_ip'] = ($row['ref_ip'] != '' AND $row['ref_ip'] != 0) ? $row['ref_ip'] : '-';

			// Agent
			$agent = ($row['ref_agent'] != '') ? $row['ref_agent'] : '-';

			if (strlen($agent) > 11)
			{
				$agent2 = '<span class="defaultBold">'.$this->EE->lang->line('ref_user_agent').'</span>:'.NBS."<a href=\"javascript:void(0);\" name=\"ext{$row['ref_id']}\" onclick=\"showHide({$row['ref_id']},this,'close');return false;\">[-]</a>".NBS.NBS.$agent;

				$agent = "<div id='extLink{$row['ref_id']}'><span class='defaultBold'>". $this->EE->lang->line('ref_user_agent').'</span>:'.NBS."<a href=\"javascript:void(0);\" name=\"ext{$row['ref_id']}\" onclick=\"showHide({$row['ref_id']},this,'open');return false;\">[+]</a>".NBS.NBS.preg_replace("/(.+?)\s+.*/", "\\1", $agent)."</div>";

				$agent .= '<div id="extText'.$row['ref_id'].'" style="display: none; padding:0;">'.$agent2.'</div>';
			}

			$vars['referrers'][$row['ref_id']]['user_agent'] = $agent;

			// Site
			$vars['referrers'][$row['ref_id']]['site'] = $sites[$row['site_id']];

			// Toggle checkbox
			$vars['referrers'][$row['ref_id']]['toggle'] = array(
																			'name'		=> 'toggle[]',
																			'id'		=> 'delete_box_'.$row['ref_id'],
																			'value'		=> $row['ref_id'],
																			'class'		=>'toggle'
																	);
		}

		// Pass the relevant data to the paginate class
		$config['base_url'] = BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=referrer'.AMP.'method=view';
		$config['total_rows'] = $vars['num_referrers'];
		$config['per_page'] = $perpage;
		$config['page_query_string'] = TRUE;
		$config['query_string_segment'] = 'rownum';
		$config['full_tag_open'] = '<p id="paginationLinks">';
		$config['full_tag_close'] = '</p>';
		$config['prev_link'] = '<img src="'.$this->EE->cp->cp_theme_url.'images/pagination_prev_button.gif" width="13" height="13" alt="&lt;" />';
		$config['next_link'] = '<img src="'.$this->EE->cp->cp_theme_url.'images/pagination_next_button.gif" width="13" height="13" alt="&gt;" />';
		$config['first_link'] = '<img src="'.$this->EE->cp->cp_theme_url.'images/pagination_first_button.gif" width="13" height="13" alt="&lt; &lt;" />';
		$config['last_link'] = '<img src="'.$this->EE->cp->cp_theme_url.'images/pagination_last_button.gif" width="13" height="13" alt="&gt; &gt;" />';

		$this->EE->pagination->initialize($config);

		$vars['pagination'] = $this->EE->pagination->create_links();

		$this->EE->javascript->compile();

		return $this->EE->load->view('view', $vars, TRUE);

	}

	// --------------------------------------------------------------------

	/**
	  *  Delete Confirm
	  */
	function delete_confirm()
	{
		if ( ! $this->EE->input->post('toggle'))
		{
			$this->EE->functions->redirect(BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=referrer');
		}

		$this->EE->load->helper('form');

		$this->EE->cp->set_breadcrumb(BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=referrer', $this->EE->lang->line('referrers'));

		$vars['cp_page_title'] = $this->EE->lang->line('delete_confirm');
		$vars['form_action'] = 'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=referrer'.AMP.'method=delete';

		foreach ($_POST['toggle'] as $key => $val)
		{
			$vars['damned'][] = $val;
		}
		
		if ($this->EE->db->table_exists('exp_blacklisted') === TRUE)
		{
			$vars['add_ips'] = $this->EE->lang->line('add_and_blacklist_ips');
			$vars['add_urls'] = $this->EE->lang->line('add_and_blacklist_urls');
			$vars['add_agents'] = $this->EE->lang->line('add_and_blacklist_agents');						
		}
		else
		{
			$vars['add_ips'] = $this->EE->lang->line('add_ips');
			$vars['add_urls'] = $this->EE->lang->line('add_urls');
			$vars['add_agents'] = $this->EE->lang->line('add_agents');				
		}

		$this->EE->javascript->compile();

		return $this->EE->load->view('delete_confirm', $vars, TRUE);
	}

	// --------------------------------------------------------------------

	/**
	  *  Delete Referrers
	  */
	function delete()
	{
		if ( ! $this->EE->input->post('delete'))
		{
			$this->EE->functions->redirect(BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=referrer');
		}

		$ids = array();
		$new = array('url'=>array(),'ip' => array(), 'agent' => array());
		$white = array('url'=>array(),'ip' => array(), 'agent' => array());

		$IDS = " ref_id IN('".implode("','", $this->EE->db->escape_str($_POST['delete']))."') ";

		//  Add To Blacklist?

		if (isset($_POST['add_urls']) OR isset($_POST['add_agents']) OR isset($_POST['add_ips']))
		{
			$query = $this->EE->db->query("SELECT ref_from, ref_ip, ref_agent FROM exp_referrers WHERE ".$IDS);

			if ($query->num_rows() == 0)
			{
				$this->EE->functions->redirect(BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=referrer');
			}

			//  New Values

			foreach($query->result_array() as $row)
			{
				if(isset($_POST['add_urls']))
				{
					$mod_url = str_replace('http://','',$row['ref_from']);
					$new['url'][] = str_replace('www.','',$mod_url);
				}

				if(isset($_POST['add_agents']))
				{
					$new['agent'][] = $row['ref_agent'];
				}

				if(isset($_POST['add_ips']))
				{
					$new['ip'][] = $row['ref_ip'];
				}
			}

			//  Add Current Blacklisted - but only if installed

			if ($this->EE->db->table_exists('exp_blacklisted') === TRUE)
			{
				$query			= $this->EE->db->get('blacklisted');
				$old['url']		= array();
				$old['agent']	= array();
				$old['ip']		= array();

				if ($query->num_rows() > 0)
				{
					foreach($query->result_array() as $row)
					{
						$old_values = explode('|',$row['blacklisted_value']);
						for ($i=0; $i < count($old_values); $i++)
						{
							$old[$row['blacklisted_type']][] = $old_values[$i];
						}
					}
				}

				//  Check for uniqueness and sort

				$new['url'] 	= array_unique(array_merge($old['url'],$new['url']));
				$new['agent']	= array_unique(array_merge($old['agent'],$new['agent']));
				$new['ip']		= array_unique(array_merge($old['ip'],$new['ip']));

				sort($new['url']);
				sort($new['agent']);
				sort($new['ip']);
				
				//  Put blacklist info back into database

				$this->EE->db->truncate('blacklisted');

				foreach($new as $key => $value)
				{
					$blacklisted_value = implode('|',$value);
					
					$data = array(	'blacklisted_type' 	=> $key,
									'blacklisted_value'	=> $blacklisted_value);

					$this->EE->db->insert('blacklisted', $data);
				}

				//  Current Whitelisted

				$query				= $this->EE->db->get('whitelisted');

				if ($query->num_rows() > 0)
				{
					foreach($query->result_array() as $row)
					{
						$white_values = explode('|',$row['whitelisted_value']);
						for ($i=0; $i < count($white_values); $i++)
						{
							if (trim($white_values[$i]) != '')
							{
								$white[$row['whitelisted_type']][] = $this->EE->db->escape_str($white_values[$i]);
							}
						}
					}
				}

				//  Using new blacklist members, clean out spam

				$new['url']		= array_diff($new['url'], $old['url']);
				$new['agent']	= array_diff($new['agent'], $old['agent']);
				$new['ip']		= array_diff($new['ip'], $old['ip']);
			}

			$modified_channels = array();

			foreach($new as $key => $value)
			{
				$name = ($key == 'url') ? 'from' : $key;

				if (count($value) > 0 && isset($_POST['add_'.$key.'s']))
				{
					sort($value);

					for($i=0; $i < count($value); $i++)
					{
						if ($value[$i] != '')
						{
							$sql = "DELETE FROM exp_referrers WHERE ref_{$name} LIKE '%".$this->EE->db->escape_like_str($value[$i])."%'";

							if (count($white[$key]) > 1)
							{
								$sql .=  " AND ref_{$name} NOT LIKE '%".implode("%' AND ref_{$name} NOT LIKE '%", $this->EE->db->escape_like_str($white[$key]))."%'";
							}
							elseif (count($white[$key]) > 0)
							{
								$sql .= "AND ref_{$name} NOT LIKE '%".$this->EE->db->escape_like_str($white[$key]['0'])."%'";
							}

							$this->EE->db->query($sql);

						}
					}
				}
			}
		}

		//  Delete Referrers
		$this->EE->db->query("DELETE FROM exp_referrers WHERE ".$IDS);

		$message = (count($ids) == 1) ? $this->EE->lang->line('referrer_deleted') : $this->EE->lang->line('referrers_deleted');

		$this->EE->session->set_flashdata('message_success', $message);
		$this->EE->functions->redirect(BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=referrer');
	}

	// --------------------------------------------------------------------

	/**
	  *  Clear Referrers
	  */
	function clear()
	{
		$this->EE->load->helper('form');

		$this->EE->cp->set_breadcrumb(BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=referrer', $this->EE->lang->line('referrers'));

		$vars['cp_page_title'] = $this->EE->lang->line('clear_referrers');
		$total = $this->EE->db->count_all('referrers');
		
		$vars['total'] = $total;

		$save = ( ! isset($_POST['save'])) ? '' : $_POST['save'];

		if ($save < 0)
		{
			$save = 0;
		}

		if (is_numeric($save) AND $save >= 0)
		{
			if ($save == 0)
			{
				$this->EE->db->truncate('referrers');
				$total = 0;
			}
			else
			{
				if ($total > $save)
				{
					$this->EE->db->select_max('ref_id', 'max_id');
					$query = $this->EE->db->get('referrers');

					$max = ($query->num_rows() == 0 OR ! is_numeric($query->row('max_id') )) ? 0 : $query->row('max_id') ;

					$save--;

					$id = $max - $save;

					$this->EE->db->where("ref_id < {$id}");
					$this->EE->db->delete('referrers');
				}
			}

			$this->EE->session->set_flashdata('message_success', $this->EE->lang->line('referrers_deleted'));
			$this->EE->functions->redirect(BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=referrer'.AMP.'method=clear');
		}

		return $this->EE->load->view('clear', $vars, TRUE);
	}
}
// END CLASS

/* End of file mcp.referrer.php */
/* Location: ./system/expressionengine/modules/referrer/mcp.referrer.php */