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
 * ExpressionEngine Search Module
 *
 * @package		ExpressionEngine
 * @subpackage	Modules
 * @category	Modules
 * @author		EllisLab Dev Team
 * @link		http://expressionengine.com
 */

class Search {

	var	$min_length		= 3;			// Minimum length of search keywords
	var	$cache_expire	= 2;			// How many hours should we keep search caches?
	var	$keywords		= "";
	var	$text_format	= 'xhtml';		// Excerpt text formatting
	var	$html_format	= 'all';		// Excerpt html formatting
	var	$auto_links		= 'y';			// Excerpt auto-linking: y/n
	var	$allow_img_url	= 'n';			// Excerpt - allow images:  y/n
	var	$channel_array 	= array();
	var	$cat_array  	= array();
	var $fields			= array();
	var $num_rows		= 0;

	protected $_meta 	= array();

	/**
	 * Constructor
	 */
	public function __construct()
	{
		// Make a local reference to the ExpressionEngine super object
		$this->EE =& get_instance();
	}

	/**
	 * Do Search
	 */
	function do_search()
	{
		$this->EE->lang->loadfile('search');
		
		// Get hidden meta vars 
		if (isset($_POST['meta']))
		{
			$this->_get_meta_vars();
		}
		
		/** ----------------------------------------
		/**  Profile Exception
		/** ----------------------------------------*/
		
		// This is an exception to the normal search routine.
		// It permits us to search for all posts by a particular user's screen name
		// We look for the "mbr" $_GET variable.  If it exsists it will
		// trigger our exception
		
		if ($this->EE->input->get_post('mbr'))
		{
			$this->_meta['result_page']	= ($this->EE->input->get_post('result_path') != '') ? $this->EE->input->get_post('result_path') : 'search/results';
			$_POST['keywords']		= '';
			$_POST['exact_match'] 	= 'y';
			$_POST['exact_keyword'] = 'n';
		}
		
		// RP can be used in a query string,
		// so we need to clean it a bit
		
		$this->_meta['result_page'] = str_replace(array('=', '&'), '', $this->_meta['result_page']);

		/** ----------------------------------------
		/**  Pulldown Addition - Any, All, Exact
		/** ----------------------------------------*/
		
		if (isset($this->_meta['where']) && $this->_meta['where'] == 'exact')
		{
			$_POST['exact_keyword'] = 'y';
		}
		
		/** ----------------------------------------
		/**  Do we have a search results page?
		/** ----------------------------------------*/
		
		// The search results template is specified as a parameter in the search form tag.
		// If the parameter is missing we'll issue an error since we don't know where to 
		// show the results
		
		if ( ! isset($this->_meta['result_page']) OR $this->_meta['result_page'] == '')
		{
			return $this->EE->output->show_user_error('general', array(lang('search_path_error')));
		}
		
		/** ----------------------------------------
		/**  Is the current user allowed to search?
		/** ----------------------------------------*/
		if ($this->EE->session->userdata('can_search') == 'n' AND $this->EE->session->userdata('group_id') != 1)
		{			
			return $this->EE->output->show_user_error('general', array(lang('search_not_allowed')));
		}
		
		/** ----------------------------------------
		/**  Flood control
		/** ----------------------------------------*/
		
		if ($this->EE->session->userdata['search_flood_control'] > 0 AND $this->EE->session->userdata['group_id'] != 1)
		{
			$cutoff = time() - $this->EE->session->userdata['search_flood_control'];

			$sql = "SELECT search_id FROM exp_search WHERE site_id = '".$this->EE->db->escape_str($this->EE->config->item('site_id'))."' AND search_date > '{$cutoff}' AND ";
			
			if ($this->EE->session->userdata['member_id'] != 0)
			{
				$sql .= "(member_id='".$this->EE->db->escape_str($this->EE->session->userdata('member_id'))."' OR ip_address='".$this->EE->db->escape_str($this->EE->input->ip_address())."')";
			}
			else
			{
				$sql .= "ip_address='".$this->EE->db->escape_str($this->EE->input->ip_address())."'";
			}
			
			$query = $this->EE->db->query($sql);
					
			$text = str_replace("%x", $this->EE->session->userdata['search_flood_control'], lang('search_time_not_expired'));
				
			if ($query->num_rows() > 0)
			{
				return $this->EE->output->show_user_error('general', array($text));
			}
		}
		
		/** ----------------------------------------
		/**  Did the user submit any keywords?
		/** ----------------------------------------*/
		
		// We only require a keyword if the member name field is blank
		
		if ( ! isset($_GET['mbr']) OR ! is_numeric($_GET['mbr']))
		{		
			if ( ! isset($_POST['member_name']) OR $_POST['member_name'] == '')
			{		
				if ( ! isset($_POST['keywords']) OR $_POST['keywords'] == "")
				{			
					return $this->EE->output->show_user_error('general', array(lang('search_no_keywords')));
				}
			}
		}
		
		/** ----------------------------------------
		/**  Strip extraneous junk from keywords
		/** ----------------------------------------*/
		if ($_POST['keywords'] != "")		
		{
			// Load the search helper so we can filter the keywords
			$this->EE->load->helper('search');

			$this->keywords = sanitize_search_terms($_POST['keywords']);
			
			/** ----------------------------------------
			/**  Is the search term long enough?
			/** ----------------------------------------*/
	
			if (strlen($this->keywords) < $this->min_length)
			{
				$text = lang('search_min_length');
				
				$text = str_replace("%x", $this->min_length, $text);
							
				return $this->EE->output->show_user_error('general', array($text));
			}

			// Load the text helper
			$this->EE->load->helper('text');

			$this->keywords = ($this->EE->config->item('auto_convert_high_ascii') == 'y') ? ascii_to_entities($this->keywords) : $this->keywords;
			
			/** ----------------------------------------
			/**  Remove "ignored" words
			/** ----------------------------------------*/
		
			if (( ! isset($_POST['exact_keyword']) OR $_POST['exact_keyword'] != 'y') && @include_once(APPPATH.'config/stopwords.php'))
			{
				$parts = explode('"', $this->keywords);
				
				$this->keywords = '';
				
				foreach($parts as $num => $part)
				{
					// The odd breaks contain quoted strings.
					if ($num % 2 == 0)
					{
						foreach ($ignore as $badword)
						{        
							$part = preg_replace("/\b".preg_quote($badword, '/')."\b/i","", $part);
						}
					}
					
					$this->keywords .= ($num != 0) ? '"'.$part : $part;
				}
								
				if (trim($this->keywords) == '')
				{
					return $this->EE->output->show_user_error('general', array(lang('search_no_stopwords')));
				}
			}
			
			/** ----------------------------------------
			/**  Log Search Terms
			/** ----------------------------------------*/
			
			$this->EE->functions->log_search_terms($this->keywords);
		}
		
		if (isset($_POST['member_name']) AND $_POST['member_name'] != "")
		{
			$_POST['member_name'] = $this->EE->security->xss_clean($_POST['member_name']);
		}
		
		/** ----------------------------------------
		/**  Build and run query
		/** ----------------------------------------*/
		
		$original_keywords = $this->keywords;
		$mbr = ( ! isset($_GET['mbr'])) ? '' : $_GET['mbr'];

		$sql = $this->build_standard_query();
		
		/** ----------------------------------------
		/**  No query results?
		/** ----------------------------------------*/
		
		if ($sql == FALSE)
		{	
			if (isset($this->_meta['no_results_page']) AND $this->_meta['no_results_page'] != '')
			{
				$hash = $this->EE->functions->random('md5');
				
				$data = array(
					'search_id'		=> $hash,
					'search_date'	=> time(),
					'member_id'		=> $this->EE->session->userdata('member_id'),
					'keywords'		=> ($original_keywords != '') ? $original_keywords : $mbr,
					'ip_address'	=> $this->EE->input->ip_address(),
					'total_results'	=> 0,
					'per_page'		=> 0,
					'query'			=> '',
					'custom_fields'	=> '',
					'result_page'	=> '',
					'site_id'		=> $this->EE->config->item('site_id')
				);
		
				$this->EE->db->query($this->EE->db->insert_string('exp_search', $data));
				
				return $this->EE->functions->redirect($this->EE->functions->create_url($this->EE->functions->extract_path("='".$this->_meta['no_results_page']."'")).'/'.$hash.'/');
			}
			else
			{
				return $this->EE->output->show_user_error('off', array(lang('search_no_result')), lang('search_result_heading'));
			}
		}
		
		/** ----------------------------------------
		/**  If we have a result, cache it
		/** ----------------------------------------*/
		
		$hash = $this->EE->functions->random('md5');
		
		$sql = str_replace("\\", "\\\\", $sql);
		
		// This fixes a bug that occurs when a different table prefix is used
		
		$sql = str_replace('exp_', 'MDBMPREFIX', $sql);

		$data = array(
			'search_id'		=> $hash,
			'search_date'	=> time(),
			'member_id'		=> $this->EE->session->userdata('member_id'),
			'keywords'		=> ($original_keywords != '') ? $original_keywords : $mbr,
			'ip_address'	=> $this->EE->input->ip_address(),
			'total_results'	=> $this->num_rows,
			'per_page'		=> (isset($_POST['RES']) AND is_numeric($_POST['RES']) AND $_POST['RES'] < 999 ) ? $_POST['RES'] : 50,
			'query'			=> addslashes(serialize($sql)),
			'custom_fields'	=> addslashes(serialize($this->fields)),
			'result_page'	=> $this->_meta['result_page'],
			'site_id'		=> $this->EE->config->item('site_id')
		);
		
		$this->EE->db->query($this->EE->db->insert_string('exp_search', $data));
		
		/** ----------------------------------------
		/**  Redirect to search results page
		/** ----------------------------------------*/

		// Load the string helper
		$this->EE->load->helper('string');
			
		$path = $this->EE->functions->remove_double_slashes(
			$this->EE->functions->create_url(
				trim_slashes($this->_meta['result_page'])
			).'/'.$hash.'/'
		);
		
		return $this->EE->functions->redirect($path);
	}
	
	// ------------------------------------------------------------------------
	
	/**
	 * Build Meta Array
	 *
	 * This builds the array of parameters that are stored in a secure hash in a hidden input
	 * on the search forms.  
	 */
	protected function _build_meta_array()
	{
		$meta = array(
			'status'				=> $this->EE->TMPL->fetch_param('status', ''),
			'channel'				=> $this->EE->TMPL->fetch_param('channel', ''),
			'category'				=> $this->EE->TMPL->fetch_param('category', ''),
			'search_in'				=> $this->EE->TMPL->fetch_param('search_in', ''),
			'where'					=> $this->EE->TMPL->fetch_param('where', 'all'),
			'show_expired'			=> $this->EE->TMPL->fetch_param('show_expired', ''),
			'show_future_entries'	=> $this->EE->TMPL->fetch_param('show_future_entries'),
			'result_page'			=> $this->EE->TMPL->fetch_param('result_page', 'search/results'),
			'no_results_page'		=> $this->EE->TMPL->fetch_param('no_result_page', '')
		);

		$meta = serialize($meta);

		if ( function_exists('mcrypt_encrypt') )
		{
			$init_size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB);
			$init_vect = mcrypt_create_iv($init_size, MCRYPT_RAND);

			$meta = mcrypt_encrypt(
				MCRYPT_RIJNDAEL_256,
				md5($this->EE->db->username.$this->EE->db->password),
				$meta,
				MCRYPT_MODE_ECB,
				$init_vect
			);
		}
		else
		{
			$meta = $meta.md5($this->EE->db->username.$this->EE->db->password.$meta);
		}
		
		
		return base64_encode($meta);
	}
	
	// ------------------------------------------------------------------------

	/**
	 * get Meta vars
	 *
	 * Get the meta variables on the POSTed form.
	 *
	 */
	protected function _get_meta_vars()
	{
		// Get data from the meta input
		
		if ( function_exists('mcrypt_encrypt') )
		{
			$init_size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB);
			$init_vect = mcrypt_create_iv($init_size, MCRYPT_RAND);

			$meta_array = rtrim(
				mcrypt_decrypt(
					MCRYPT_RIJNDAEL_256, 
					md5($this->EE->db->username.$this->EE->db->password), 
					base64_decode($_POST['meta']), 
					MCRYPT_MODE_ECB, 
					$init_vect
				), 
				"\0"
			);
		}
		else
		{
			$raw = base64_decode($_POST['meta']);

			$hash = substr($raw, -32);
			$meta_array = substr($raw, 0, -32);

			if ($hash != md5($this->EE->db->username.$this->EE->db->password.$meta_array))
			{
				$meta_array = '';
			}
		}
		
		$this->_meta = unserialize($meta_array);
		
		// Check for Advanced Form Inputs
		$valid_inputs = array('search_in', 'where');
		foreach ($valid_inputs as $current_input) 
		{
			if (
				( ! isset($this->_meta[$current_input]) OR $this->_meta[$current_input] === '') &&
				$this->EE->input->post($current_input)
			)
			{
				$this->_meta[$current_input] = $this->EE->input->post($current_input);
			}
		}
	}
	
	// ------------------------------------------------------------------------
	
	/** ---------------------------------------
	/**  Create the search query
	/** ---------------------------------------*/
	function build_standard_query()
	{
		$this->EE->load->model('addons_model');

		$channel_array	= array();

		/** ---------------------------------------
		/**  Fetch the channel_id numbers
		/** ---------------------------------------*/
			
		// If $_POST['channel_id'] exists we know the request is coming from the 
		// advanced search form. We set those values to the $channel_id_array		

		if (isset($_POST['channel_id']) AND is_array($_POST['channel_id']))
		{
			$channel_id_array = $_POST['channel_id'];
		}
		
		// Since both the simple and advanced search form have
		// $_POST['channel'], then we can safely find all of the
		// channels available for searching
		
		// By doing this for the advanced search form, we can discover
		// Which channels we are or are not supposed to search for, when
		// "Any Channel" is chosen
		
		$sql = "SELECT channel_id FROM exp_channels WHERE site_id = '".$this->EE->db->escape_str($this->EE->config->item('site_id'))."'";
		
		if (isset($this->_meta['channel']) AND $this->_meta['channel'] != '')
		{
			$sql .= $this->EE->functions->sql_andor_string($this->_meta['channel'], 'channel_name');
		}
		
		$query = $this->EE->db->query($sql);
		
		// If channel's are specified and there NO valid channels returned?  There can be no results!
		if ($query->num_rows() == 0)
		{
			return FALSE;
		}

		foreach ($query->result_array() as $row)
		{
			$channel_array[] = $row['channel_id'];
		}		
		
		/** ------------------------------------------------------
		/**  Find the Common Channel IDs for Advanced Search Form
		/** ------------------------------------------------------*/
		
		if (isset($channel_id_array) && $channel_id_array['0'] != 'null')
		{
			$channel_array = array_intersect($channel_id_array, $channel_array);
		}

		/** ----------------------------------------------
		/**  Fetch the channel_id numbers (from Advanced search)
		/** ----------------------------------------------*/
		
		// We do this up-front since we use this same sub-query in two places

		$id_query = '';
		
		if (count($channel_array) > 0)
		{
			foreach ($channel_array as $val)
			{
				if ($val != 'null' AND $val != '')
				{
					$id_query .= " exp_channel_titles.channel_id = '".$this->EE->db->escape_str($val)."' OR";
				}
			} 
			
			if ($id_query != '')
			{
				$id_query = substr($id_query, 0, -2);
				$id_query = ' AND ('.$id_query.') ';
			}
		}

        /** ----------------------------------------------
        /**  Limit to a specific member? We do this now
        /**  as there's a potential for this to bring the
        /**  search to an end if it's not a valid member
        /** ----------------------------------------------*/
        
		$member_array	= array();
		$member_ids		= '';
		
        if (isset($_GET['mbr']) AND is_numeric($_GET['mbr']))
        {
			$query = $this->EE->db->select('member_id')->get_where('members', array(
				'member_id' => $_GET['mbr']
			));
			
			if ($query->num_rows() != 1)
			{
				return FALSE;
			}
			else
			{
				$member_array[] = $query->row('member_id');
			}
        }
        else
        {
			if ($this->EE->input->post('member_name') != '')
			{
				$this->EE->db->select('member_id');
				
				if ($this->EE->input->post('exact_match') == 'y')
				{
					$this->EE->db->where('screen_name', $this->EE->input->post('member_name'));
				}
				else
				{
					$this->EE->db->like('screen_name', $this->EE->input->post('member_name'));
				}
				
				$query = $this->EE->db->get('members');
			
				if ($query->num_rows() == 0)
				{
					return FALSE;
				}
				else
				{
					foreach ($query->result_array() as $row)
					{
						$member_array[] = $row['member_id'];
					}
				}
			}
		}

		// and turn it into a string now so we only implode once
		if (count($member_array) > 0)
		{
			$member_ids = ' IN ('.implode(',', $member_array).') ';
		}
		
		unset($member_array);
		
		
		/** ---------------------------------------
		/**  Fetch the searchable field names
		/** ---------------------------------------*/

		$fields = array();

		// no need to do this unless there are keywords to search
		if (trim($this->keywords) != '')
		{
			$xql = "SELECT DISTINCT(field_group) FROM exp_channels WHERE site_id = '".$this->EE->db->escape_str($this->EE->config->item('site_id'))."'";

			if ($id_query != '')
			{
				$xql .= $id_query.' ';
				$xql = str_replace('exp_channel_titles.', '', $xql);
			}

			$query = $this->EE->db->query($xql);

			if ($query->num_rows() > 0)
			{
				$this->EE->db->select('field_id, field_name, field_search');
			
				// Build array of field groups
				$field_groups = array();
				foreach ($query->result_array() as $row)
				{
					$field_groups[] = $row['field_group'];
				}
				
				if (count($field_groups) > 0)
				{
					$this->EE->db->where_in('group_id', $field_groups);
				}

				$field_query = $this->EE->db->get('channel_fields');

				if ($field_query->num_rows() > 0)
				{
					foreach ($field_query->result_array() as $row)
					{
						if ($row['field_search'] == 'y')
						{
							$fields[] = $row['field_id'];
						}

						$this->fields[$row['field_name']] = array($row['field_id'], $row['field_search']);
					}
				}
			}			
		}


		/** ---------------------------------------
		/**  Build the main query
		/** ---------------------------------------*/
	
	
		$sql = "SELECT
			DISTINCT(exp_channel_titles.entry_id)
			FROM exp_channel_titles
			LEFT JOIN exp_channels ON exp_channel_titles.channel_id = exp_channels.channel_id 
			LEFT JOIN exp_channel_data ON exp_channel_titles.entry_id = exp_channel_data.entry_id ";

		// is the comment module installed?
		if ($this->EE->addons_model->module_installed('comment'))
		{
			$sql .= "LEFT JOIN exp_comments ON exp_channel_titles.entry_id = exp_comments.entry_id ";
		}

		$sql .= "LEFT JOIN exp_category_posts ON exp_channel_titles.entry_id = exp_category_posts.entry_id
			LEFT JOIN exp_categories ON exp_category_posts.cat_id = exp_categories.cat_id
			WHERE exp_channels.site_id = '".$this->EE->db->escape_str($this->EE->config->item('site_id'))."' ";
		
		/** ----------------------------------------------
		/**  We only select entries that have not expired 
		/** ----------------------------------------------*/
	
		if ( ! isset($this->_meta['show_future_entries']) OR $this->_meta['show_future_entries'] != 'yes')
		{
			$sql .= "\nAND exp_channel_titles.entry_date < ".$this->EE->localize->now." ";
		}
		
		if ( ! isset($this->_meta['show_expired']) OR $this->_meta['show_expired'] != 'yes')
		{
			$sql .= "\nAND (exp_channel_titles.expiration_date = 0 OR exp_channel_titles.expiration_date > ".$this->EE->localize->now.") ";
		}

		/** ----------------------------------------------
		/**  Add status declaration to the query
		/** ----------------------------------------------*/
		
		if (isset($this->_meta['status']) AND ($status = $this->_meta['status']) != '')
		{
			$status = str_replace('Open',	'open',	$status);
			$status = str_replace('Closed', 'closed', $status);
		
			$sql .= $this->EE->functions->sql_andor_string($status, 'exp_channel_titles.status');
			
			// add exclusion for closed unless it was explicitly used
			if (strncasecmp($status, 'not ', 4) == 0)
			{
				$status = trim(substr($status, 3));
			}
			
			$stati = explode('|', $status);
			
			if ( ! in_array('closed', $stati))
			{
				$sql .= "\nAND exp_channel_titles.status != 'closed' ";
			}
		}
		else
		{
			$sql .= "AND exp_channel_titles.status = 'open' ";
		}
		
		/** ----------------------------------------------
		/**  Set Date filtering
		/** ----------------------------------------------*/
		
		if (isset($_POST['date']) AND $_POST['date'] != 0)
		{
			$cutoff = $this->EE->localize->now - (60*60*24*$_POST['date']);
			
			if (isset($_POST['date_order']) AND $_POST['date_order'] == 'older')
			{
				$sql .= "AND exp_channel_titles.entry_date < ".$cutoff." ";
			}
			else
			{
				$sql .= "AND exp_channel_titles.entry_date > ".$cutoff." ";
			}
		}
		
		/** ----------------------------------------------
		/**  Add keyword to the query
		/** ----------------------------------------------*/
		
		if (trim($this->keywords) != '')
		{
			// So it begins
			$sql .= "\nAND (";
			
			/** -----------------------------------------
			/**  Process our Keywords into Search Terms
			/** -----------------------------------------*/
		
			$this->keywords = stripslashes($this->keywords);
			$terms = array();
			$criteria = (isset($this->_meta['where']) && $this->_meta['where'] == 'all') ? 'AND' : 'OR'; 
			
			if (preg_match_all("/\-*\"(.*?)\"/", $this->keywords, $matches))
			{
				for($m=0; $m < count($matches['1']); $m++)
				{
					$terms[] = trim(str_replace('"','',$matches['0'][$m]));
					$this->keywords = str_replace($matches['0'][$m],'', $this->keywords);
				}	
			}
	
			if (trim($this->keywords) != '')
			{
				$terms = array_merge($terms, preg_split("/\s+/", trim($this->keywords)));
  			}
  			
  			$not_and = (count($terms) > 2) ? ') AND (' : 'AND';
  			rsort($terms);
			$terms_like = $this->EE->db->escape_like_str($terms);
			$terms = $this->EE->db->escape_str($terms);  			
  			
  			/** ----------------------------------
			/**  Search in Title Field
			/** ----------------------------------*/
			
			if (count($terms) == 1 && isset($this->_meta['where']) && $this->_meta['where'] == 'word') // Exact word match
			{
				$sql .= "((exp_channel_titles.title = '".$terms['0']."' OR exp_channel_titles.title LIKE '".$terms_like['0']." %' OR exp_channel_titles.title LIKE '% ".$terms_like['0']." %') ";
				
				// and close up the member clause
				if ($member_ids != '')
				{
					$sql .= " AND (exp_channel_titles.author_id {$member_ids})) \n";
				}
				else
				{
					$sql .= ") \n";
				}
			}			
			elseif ( ! isset($_POST['exact_keyword']))  // Any terms, all terms
			{ 
				$mysql_function	= (substr($terms['0'], 0,1) == '-') ? 'NOT LIKE' : 'LIKE';	
				$search_term	= (substr($terms['0'], 0,1) == '-') ? substr($terms_like['0'], 1) : $terms_like['0'];
				
				// We have three parentheses in the beginning in case
				// there are any NOT LIKE's being used and to allow for a member clause
				$sql .= "\n(((exp_channel_titles.title $mysql_function '%".$search_term."%' ";
				
				for ($i=1; $i < count($terms); $i++) 
				{
					$mysql_criteria	= ($mysql_function == 'NOT LIKE' OR substr($terms[$i], 0,1) == '-') ? $not_and : $criteria;
					$mysql_function	= (substr($terms[$i], 0,1) == '-') ? 'NOT LIKE' : 'LIKE';
					$search_term	= (substr($terms[$i], 0,1) == '-') ? substr($terms_like[$i], 1) : $terms_like[$i];
					
					$sql .= "$mysql_criteria exp_channel_titles.title $mysql_function '%".$search_term."%' ";
				}
				
				$sql .= ")) ";
				
				// and close up the member clause
				if ($member_ids != '')
				{
					$sql .= " AND (exp_channel_titles.author_id {$member_ids})) \n";
				}
				else
				{
					$sql .= ") \n";
				}
			}
			else // exact phrase match
			{	
				$search_term = (count($terms) == 1) ? $terms_like[0] : $this->EE->db->escape_str($this->keywords);
				$sql .= "(exp_channel_titles.title LIKE '%".$search_term."%' ";
				
				// and close up the member clause
				if ($member_ids != '')
				{
					$sql .= " AND (exp_channel_titles.author_id {$member_ids})) \n";
				}
				else
				{
					$sql .= ") \n";
				}
			}
			
			/** ----------------------------------
			/**  Search in Searchable Fields
			/** ----------------------------------*/
			
			if (isset($this->_meta['search_in']) AND ($this->_meta['search_in'] == 'entries' OR $this->_meta['search_in'] == 'everywhere'))
			{
				if (count($terms) > 1 && isset($this->_meta['where']) && $this->_meta['where'] == 'all' && ! isset($_POST['exact_keyword']) && count($fields) > 0)
				{
					$concat_fields = "CAST(CONCAT_WS(' ', exp_channel_data.field_id_".implode(', exp_channel_data.field_id_', $fields).') AS CHAR)'; 
					
					$mysql_function	= (substr($terms['0'], 0,1) == '-') ? 'NOT LIKE' : 'LIKE';	
					$search_term	= (substr($terms['0'], 0,1) == '-') ? substr($terms['0'], 1) : $terms['0'];
							
					// Since Title is always required in a search we use OR
					// And then three parentheses just like above in case
					// there are any NOT LIKE's being used and to allow for a member clause
					$sql .= "\nOR ((($concat_fields $mysql_function '%".$search_term."%' ";
					
					for ($i=1; $i < count($terms); $i++) 
					{
						$mysql_criteria	= ($mysql_function == 'NOT LIKE' OR substr($terms[$i], 0,1) == '-') ? $not_and : $criteria;
						$mysql_function	= (substr($terms[$i], 0,1) == '-') ? 'NOT LIKE' : 'LIKE';
						$search_term	= (substr($terms[$i], 0,1) == '-') ? substr($terms_like[$i], 1) : $terms_like[$i];
						
						$sql .= "$mysql_criteria $concat_fields $mysql_function '%".$search_term."%' ";
					}
							
					$sql .= ")) ";
									
					// and close up the member clause
					if ($member_ids != '')
					{
						$sql .= " AND (exp_channel_titles.author_id {$member_ids})) \n";
					}
					else
					{
						$sql .= ") \n";
					}
				}
				else
				{
					foreach ($fields as $val)
					{					
						if (count($terms) == 1 && isset($this->_meta['where']) && $this->_meta['where'] == 'word')
						{
							$sql .= "\nOR ((exp_channel_data.field_id_".$val." LIKE '".$terms_like['0']." %' OR exp_channel_data.field_id_".$val." LIKE '% ".$terms_like['0']." %' OR exp_channel_data.field_id_".$val." LIKE '% ".$terms_like['0']." %' OR exp_channel_data.field_id_".$val." = '".$terms['0']."') ";
							
							// and close up the member clause
							if ($member_ids != '')
							{
								$sql .= " AND (exp_channel_titles.author_id {$member_ids})) ";
							}
							else
							{
								$sql .= ") ";
							}
						}
						elseif ( ! isset($_POST['exact_keyword']))
						{
							$mysql_function	= (substr($terms['0'], 0,1) == '-') ? 'NOT LIKE' : 'LIKE';	
							$search_term	= (substr($terms['0'], 0,1) == '-') ? substr($terms_like['0'], 1) : $terms_like['0'];
							
							// Since Title is always required in a search we use OR
							// And then three parentheses just like above in case
							// there are any NOT LIKE's being used and to allow for a member clause
							$sql .= "\nOR (((exp_channel_data.field_id_".$val." $mysql_function '%".$search_term."%' ";
					
							for ($i=1; $i < count($terms); $i++) 
							{
								$mysql_criteria	= ($mysql_function == 'NOT LIKE' OR substr($terms[$i], 0,1) == '-') ? $not_and : $criteria;
								$mysql_function	= (substr($terms[$i], 0,1) == '-') ? 'NOT LIKE' : 'LIKE';
								$search_term	= (substr($terms[$i], 0,1) == '-') ? substr($terms_like[$i], 1) : $terms_like[$i];
						
								$sql .= "$mysql_criteria exp_channel_data.field_id_".$val." $mysql_function '%".$search_term."%' ";
							}
							
							$sql .= ")) ";
							
							// and close up the member clause
							if ($member_ids != '')
							{
								$sql .= " AND (exp_channel_titles.author_id {$member_ids})) \n";
							}
							else
							{
								// close up the extra parenthesis
								$sql .= ") \n";
							}
						}
						else
						{
							$search_term = (count($terms) == 1) ? $terms_like[0] : $this->EE->db->escape_str($this->keywords);
							$sql .= "\nOR (exp_channel_data.field_id_".$val." LIKE '%".$search_term."%' ";

							// and close up the member clause
							if ($member_ids != '')
							{
								$sql .= " AND (exp_channel_titles.author_id {$member_ids})) \n";
							}
							else
							{
								// close up the extra parenthesis
								$sql .= ") \n";
							}							
						}
					}
				}
			}
			
			/** ----------------------------------
			/**  Search in Comments
			/** ----------------------------------*/

			if (isset($this->_meta['search_in']) AND $this->_meta['search_in'] == 'everywhere' AND $this->EE->addons_model->module_installed('comment'))
			{
				if (count($terms) == 1 && isset($this->_meta['where']) && $this->_meta['where'] == 'word')
				{
					$sql .= " OR (exp_comments.comment LIKE '% ".$terms_like['0']." %' ";
					
					// and close up the member clause
					if ($member_ids != '')
					{
						$sql .= " AND (exp_comments.author_id {$member_ids})) \n";
					}
					else
					{
						// close up the extra parenthesis
						$sql .= ") \n";
					}
				}
				elseif ( ! isset($_POST['exact_keyword']))
				{
					$mysql_function	= (substr($terms['0'], 0,1) == '-') ? 'NOT LIKE' : 'LIKE';	
					$search_term	= (substr($terms['0'], 0,1) == '-') ? substr($terms_like['0'], 1) : $terms_like['0'];
					
					// We have three parentheses in the beginning in case
					// there are any NOT LIKE's being used and to allow a member clause
					$sql .= "\nOR (((exp_comments.comment $mysql_function '%".$search_term."%' ";
					
					for ($i=1; $i < count($terms); $i++) 
					{
						$mysql_criteria	= ($mysql_function == 'NOT LIKE' OR substr($terms[$i], 0,1) == '-') ? $not_and : $criteria;
						$mysql_function	= (substr($terms[$i], 0,1) == '-') ? 'NOT LIKE' : 'LIKE';
						$search_term	= (substr($terms[$i], 0,1) == '-') ? substr($terms_like[$i], 1) : $terms_like[$i];
					
						$sql .= "$mysql_criteria exp_comments.comment $mysql_function '%".$search_term."%' ";
					}
				
					$sql .= ")) ";
					
					// and close up the member clause
					if ($member_ids != '')
					{
						$sql .= " AND (exp_comments.author_id {$member_ids})) \n";
					}
					else
					{
						// close up the extra parenthesis
						$sql .= ") \n";
					}
				}
				else
				{
					$search_term = (count($terms) == 1) ? $terms_like[0] : $this->EE->db->escape_str($this->keywords);
					$sql .= " OR ((exp_comments.comment LIKE '%".$search_term."%') ";

					// and close up the member clause
					if ($member_ids != '')
					{
						$sql .= " AND (exp_comments.author_id {$member_ids})) \n";
					}
					else
					{
						// close up the extra parenthesis
						$sql .= ") \n";
					}
				}
			}
			
			// So it ends
			$sql .= ") \n";
		}
		else
		{
			// there are no keywords at all.  Do we still need a member search?
			if ($member_ids != '')
			{
				
				$sql .= "AND (exp_channel_titles.author_id {$member_ids} ";

				// searching comments too?
				if (isset($this->_meta['search_in']) AND $this->_meta['search_in'] == 'everywhere' AND $this->EE->addons_model->module_installed('comment'))
				{
					$sql .= " OR exp_comments.author_id {$member_ids}";
				}
				
				$sql .= ")";
			}
		}
		//exit($sql);
		
		/** ----------------------------------------------
		/**  Limit query to a specific channel
		/** ----------------------------------------------*/
				
		if (count($channel_array) > 0)
		{		
			$sql .= $id_query;
		}
		
		/** ----------------------------------------------
		/**  Limit query to a specific category
		/** ----------------------------------------------*/
				
		// Check for different sets of category IDs, checking the parameters
		// first, then the $_POST
		if (isset($this->_meta['category']) AND $this->_meta['category'] != '' AND ! is_array($this->_meta['category']))
		{
			$this->_meta['category'] = explode('|', $this->_meta['category']);
		}
		else if (
			( ! isset($this->_meta['category']) OR $this->_meta['category'] == '') AND
			(isset($_POST['cat_id']) AND is_array($_POST['cat_id']))
		)
		{
			$this->_meta['category'] = $_POST['cat_id'];
		}
		else
		{
			$this->_meta['category'] = '';
		}
		
		if (is_array($this->_meta['category']))
		{		
			$temp = '';
		
			foreach ($this->_meta['category'] as $val)
			{
				if ($val != 'all' AND $val != '')
				{
					$temp .= " exp_categories.cat_id = '".$this->EE->db->escape_str($val)."' OR";
				}
			} 
			
			if ($temp != '')
			{
				$temp = substr($temp, 0, -2);
			
				$sql .= ' AND ('.$temp.') ';
			}
		}
		
		/** ----------------------------------------------
		/**  Are there results?
		/** ----------------------------------------------*/
		
		$query = $this->EE->db->query($sql);
					
		if ($query->num_rows() == 0)
		{
			return FALSE;
		}
		
		$this->num_rows = $query->num_rows();
	
		/** ----------------------------------------------
		/**  Set sort order
		/** ----------------------------------------------*/
	
		$order_by = ( ! isset($_POST['order_by'])) ? 'date' : $_POST['order_by'];
		$orderby = ( ! isset($_POST['orderby'])) ? $order_by : $_POST['orderby'];
	
		$end = '';
		
		switch ($orderby)
		{
			case 'most_comments':
				$end .= " ORDER BY comment_total ";
				break;
			case 'recent_comment':
				$end .= " ORDER BY recent_comment_date ";
				break;
			case 'title':
				$end .= " ORDER BY title ";
				break;
			default:
				$end .= " ORDER BY entry_date ";
				break;
		}
	
		$order = ( ! isset($_POST['sort_order'])) ? 'desc' : $_POST['sort_order'];
		
		if ($order != 'asc' AND $order != 'desc')
		{
			$order = 'desc';
		}
		
		$end .= " ".$order;
			
		$sql = "SELECT DISTINCT(t.entry_id), t.entry_id, t.channel_id, t.forum_topic_id, t.author_id, t.ip_address, t.title, t.url_title, t.status, t.dst_enabled, t.view_count_one, t.view_count_two, t.view_count_three, t.view_count_four, t.allow_comments, t.comment_expiration_date, t.sticky, t.entry_date, t.year, t.month, t.day, t.entry_date, t.edit_date, t.expiration_date, t.recent_comment_date, t.comment_total, t.site_id as entry_site_id,
				w.channel_title, w.channel_name, w.search_results_url, w.search_excerpt, w.channel_url, w.comment_url, w.comment_moderate, w.channel_html_formatting, w.channel_allow_img_urls, w.channel_auto_link_urls, w.comment_system_enabled, 
				m.username, m.email, m.url, m.screen_name, m.location, m.occupation, m.interests, m.aol_im, m.yahoo_im, m.msn_im, m.icq, m.signature, m.sig_img_filename, m.sig_img_width, m.sig_img_height, m.avatar_filename, m.avatar_width, m.avatar_height, m.photo_filename, m.photo_width, m.photo_height, m.group_id, m.member_id, m.bday_d, m.bday_m, m.bday_y, m.bio,
				md.*,
				wd.*
			FROM exp_channel_titles		AS t
			LEFT JOIN exp_channels 		AS w  ON t.channel_id = w.channel_id 
			LEFT JOIN exp_channel_data	AS wd ON t.entry_id = wd.entry_id 
			LEFT JOIN exp_members		AS m  ON m.member_id = t.author_id 
			LEFT JOIN exp_member_data	AS md ON md.member_id = m.member_id 
			WHERE t.entry_id IN (";
		
		foreach ($query->result_array() as $row)
		{
			$sql .= $row['entry_id'].',';
		}
		
		$sql = substr($sql, 0, -1).') '.$end;		
		
		return $sql;
	}

	// ------------------------------------------------------------------------

	/** ----------------------------------------
	/**  Total search results
	/** ----------------------------------------*/
	
	function total_results()
	{
		/** ----------------------------------------
		/**  Check search ID number
		/** ----------------------------------------*/
		
		// If the QSTR variable is less than 32 characters long we
		// don't have a valid search ID number
		
		if (strlen($this->EE->uri->query_string) < 32)
		{
			return '';
		}
		
		/** ----------------------------------------
		/**  Fetch ID number and page number
		/** ----------------------------------------*/
		
		$search_id = substr($this->EE->uri->query_string, 0, 32);

		/** ----------------------------------------
		/**  Fetch the cached search query
		/** ----------------------------------------*/
		
		$query = $this->EE->db->query("SELECT total_results FROM exp_search WHERE search_id = '".$this->EE->db->escape_str($search_id)."'");

		if ($query->num_rows() == 1)
		{
			return $query->row('total_results') ;
		}
		else
		{
			return 0;
		}
	}
	
	/** ----------------------------------------
	/**  Search keywords
	/** ----------------------------------------*/
	
	function keywords()
	{
		/** ----------------------------------------
		/**  Check search ID number
		/** ----------------------------------------*/
		
		// If the QSTR variable is less than 32 characters long we
		// don't have a valid search ID number
		
		if (strlen($this->EE->uri->query_string) < 32)
		{
			return '';
		}
		
		/** ----------------------------------------
		/**  Fetch ID number and page number
		/** ----------------------------------------*/
		
		$search_id = substr($this->EE->uri->query_string, 0, 32);

		/** ----------------------------------------
		/**  Fetch the cached search query
		/** ----------------------------------------*/
		
		$query = $this->EE->db->query("SELECT keywords FROM exp_search WHERE search_id = '".$this->EE->db->escape_str($search_id)."'");

		if ($query->num_rows() == 1)
		{
			// Load the XML Helper
			$this->EE->load->helper('xml');
	
			return $this->EE->functions->encode_ee_tags(xml_convert($query->row('keywords')));
		}
		else
		{
			return '';
		}
	}



	/** ----------------------------------------
	/**  Show search results
	/** ----------------------------------------*/
	
	function search_results()
	{
		// Fetch the search language file
		$this->EE->lang->loadfile('search');
		
		// Load Pagination Object
		$this->EE->load->library('pagination');
		$pagination = new Pagination_object(__CLASS__);
		
		// Capture Pagination Template
		$pagination->get_template();
		
		// Check to see if we're using old style pagination
		// TODO: Remove once old pagination is phased out
		$old_pagination = (strpos($this->EE->TMPL->template, LD.'if paginate'.RD) !== FALSE) ? TRUE : FALSE;
		
		// If we are using old pagination, log it as deprecated
		// TODO: Remove once old pagination is phased out
		if ($old_pagination)
		{
			$this->EE->load->library('logger');
			$this->EE->logger->developer('Deprecated template tag {if paginate}. Old style pagination in the Search Module has been deprecated in 2.4 and will be removed soon. Switch to the new Channel style pagination.', TRUE);
		}
		
		// Check search ID number
		// If the QSTR variable is less than 32 characters long we
		// don't have a valid search ID number
		
		if (strlen($this->EE->uri->query_string) < 32)
		{
			return $this->EE->output->show_user_error(
				'off', 
				array(lang('search_no_result')),
				lang('search_result_heading')
			);
		}
		
		// Clear old search results
		$this->EE->db->delete(
			'search',
			array(
				'site_id' => $this->EE->config->item('site_id'),
				'search_date <' => $this->EE->localize->now - ($this->cache_expire * 3600)
			)
		);
		
		// Fetch ID number and page number
		$pagination->offset = 0;
		$qstring = $this->EE->uri->query_string;

		// Parse page number
		if (preg_match("#^P(\d+)|/P(\d+)#", $qstring, $match))
		{
			$pagination->offset = (isset($match[2])) ? $match[2] : $match[1];
			$search_id = trim_slashes(str_replace($match[0], '', $qstring));
		}
		else
		{
			$pagination->offset = 0;
			$search_id = $qstring;
		}
		
		// If there is a slash in the search ID we'll kill everything after it.
		$search_id = trim($search_id); 
		$search_id = preg_replace("#/.+#", "", $search_id);
		
		// Fetch the cached search query
		$query = $this->EE->db->get_where('search', array('search_id' => $search_id));
		
		if ($query->num_rows() == 0 OR $query->row('total_results')  == 0)
		{
			return $this->EE->output->show_user_error('off', array(lang('search_no_result')), lang('search_result_heading'));
		}
		
		$fields = ($query->row('custom_fields') == '') ? array() : unserialize(stripslashes($query->row('custom_fields') ));
		$sql 	= unserialize(stripslashes($query->row('query')));
		$sql	= str_replace('MDBMPREFIX', 'exp_', $sql);
		
		$pagination->per_page = (int) $query->row('per_page');
		$res_page = $query->row('result_page');
		
		// Run the search query
		$query = $this->EE->db->query(preg_replace("/SELECT(.*?)\s+FROM\s+/is", 'SELECT COUNT(*) AS count FROM ', $sql));
		
		if ($query->row('count')  == 0)
		{
			return $this->EE->output->show_user_error('off', array(lang('search_no_result')), lang('search_result_heading'));
		}
		
		// Calculate total number of pages and add total rows
		$pagination->current_page 	= ($pagination->offset / $pagination->per_page) + 1;
		$pagination->total_rows 	= $query->row('count');
		
		// Figure out total number of pages for old style pagination
		// TODO: Remove once old pagination is phased out
		if ($old_pagination)
		{
			$total_pages = intval($pagination->total_rows / $pagination->per_page);

			if ($pagination->total_rows  % $pagination->per_page)
			{
				$total_pages++;
			}

			$page_count = lang('page').' '.$pagination->current_page.' '.lang('of').' '.$total_pages;
			
			$pager = '';
			
			if ($pagination->total_rows > $pagination->per_page)
			{
				$this->EE->load->library('pagination');

				$config = array(
					'base_url' 		=> $this->EE->functions->create_url($res_page.'/'.$search_id, 0, 0),
					'prefix'		=> 'P',
					'total_rows'	=> $pagination->total_rows,
					'per_page'		=> $pagination->per_page,
					'cur_page'		=> $pagination->offset,
					'first_link'	=> lang('pag_first_link'),
					'last_link'		=> lang('pag_last_link'),
					'uri_segment'	=> 0 // Allows $config['cur_page'] to override
				);

				$this->EE->pagination->initialize($config);
				$pager = $this->EE->pagination->create_links();
			}
		}
		
		// Build pagination if enabled
		if ($pagination->paginate === TRUE)
		{
			$pagination->build($pagination->total_rows);
		}
		
		// If we're paginating, old or new, limit the query and do it again
		if ($pagination->paginate === TRUE OR $old_pagination)
		{
			$sql .= " LIMIT ".$pagination->offset.", ".$pagination->per_page;
		}
		else if ($pagination->per_page > 0)
		{
			$sql .= " LIMIT 0, ".$pagination->per_page;
		}
		else
		{
			$sql .= " LIMIT 0, 100";
		}
		
		$query = $this->EE->db->query($sql);
		
		$output = '';
		
		if ( ! class_exists('Channel'))
		{
			require PATH_MOD.'channel/mod.channel.php';
		}
		
		unset($this->EE->TMPL->var_single['auto_path']);
		unset($this->EE->TMPL->var_single['excerpt']);
		unset($this->EE->TMPL->var_single['id_auto_path']);
		unset($this->EE->TMPL->var_single['full_text']);
		unset($this->EE->TMPL->var_single['switch']);
		
		foreach($this->EE->TMPL->var_single as $key => $value)
		{
			if (substr($key, 0, strlen('member_path')) == 'member_path')
			{
				unset($this->EE->TMPL->var_single[$key]);
			}
		}

		$channel = new Channel;

		// This allows the channel {absolute_count} variable to work
		$channel->p_page = ($pagination->per_page * $pagination->current_page) - $pagination->per_page;

		$channel->fetch_custom_channel_fields();
		$channel->fetch_custom_member_fields();
		$channel->query = $this->EE->db->query($sql);
		
		if ($channel->query->num_rows() == 0)
		{
			return $this->EE->TMPL->no_results();
		}
		
		$this->EE->load->library('typography');
		$this->EE->typography->initialize(array(
			'convert_curly'	=> FALSE,
			'encode_email'	=> FALSE
		));
		
		$channel->fetch_categories();
		$channel->parse_channel_entries();
		
		$tagdata = $this->EE->TMPL->tagdata;

		// Does the tag contain "related entries" that we need to parse out?
		if (count($this->EE->TMPL->related_data) > 0 AND count($channel->related_entries) > 0)
		{
			$channel->parse_related_entries();
		}
		
		if (count($this->EE->TMPL->reverse_related_data) > 0 AND count($channel->reverse_related_entries) > 0)
		{
			$channel->parse_reverse_related_entries();
		}
				
		$output = $channel->return_data;
		
		$this->EE->TMPL->tagdata = $tagdata;
		
		// Fetch member path variable
		// We do it here in case it's used in multiple places.		
		$m_paths = array();
		
		if (preg_match_all("/".LD."member_path(\s*=.*?)".RD."/s", $this->EE->TMPL->tagdata, $matches))
		{ 
			for ($j = 0; $j < count($matches['0']); $j++)
			{
				$m_paths[] = array($matches['0'][$j], $this->EE->functions->extract_path($matches['1'][$j]));
			}
		}
		
		// Fetch switch param
		$switch1 = '';
		$switch2 = '';
		
		if ($switch = $this->EE->TMPL->fetch_param('switch'))
		{
			if (strpos($switch, '|') !== FALSE)
			{
				$x = explode("|", $switch);
				
				$switch1 = $x['0'];
				$switch2 = $x['1'];
			}
			else
			{
				$switch1 = $switch;
			}
		}	
		
		/** -----------------------------
		/**  Result Loop - Legacy!
		/** -----------------------------*/
		
		$i = 0;
		
		foreach ($query->result_array() as $row)
		{
			if (isset($row['field_id_'.$row['search_excerpt']]) AND $row['field_id_'.$row['search_excerpt']])
			{
				$format = ( ! isset($row['field_ft_'.$row['search_excerpt']])) ? 'xhtml' : $row['field_ft_'.$row['search_excerpt']];
			
				$full_text = $this->EE->typography->parse_type(
					strip_tags($row['field_id_'.$row['search_excerpt']]),
					array(
						'text_format'	=> $format,
						'html_format'	=> 'safe',
						'auto_links'	=> 'y',
						'allow_img_url' => 'n'
					)
				);
				
				$excerpt = trim(strip_tags($full_text));
				
				if (strpos($excerpt, "\r") !== FALSE OR strpos($excerpt, "\n") !== FALSE)
				{
					$excerpt = str_replace(array("\r\n", "\r", "\n"), " ", $excerpt);
				}

				$excerpt = $this->EE->functions->word_limiter($excerpt, 50);
			}
			else
			{
				$excerpt = '';
				$full_text = '';
			}
			
			// Parse permalink path
			$url = ($row['search_results_url'] != '') ? $row['search_results_url'] : $row['channel_url'];		
			
			$path = $this->EE->functions->remove_double_slashes($this->EE->functions->prep_query_string($url).'/'.$row['url_title']);
			$idpath = $this->EE->functions->remove_double_slashes($this->EE->functions->prep_query_string($url).'/'.$row['entry_id']);
			
			$switch = ($i++ % 2) ? $switch1 : $switch2;
			$output = preg_replace("/".LD.'switch'.RD."/", $switch, $output, count(explode(LD.'switch'.RD, $this->EE->TMPL->tagdata)) - 1);
			$output = preg_replace("/".LD.'auto_path'.RD."/", $path, $output, count(explode(LD.'auto_path'.RD, $this->EE->TMPL->tagdata)) - 1);
			$output = preg_replace("/".LD.'id_auto_path'.RD."/", $idpath, $output, count(explode(LD.'id_auto_path'.RD, $this->EE->TMPL->tagdata)) - 1);
			$output = preg_replace("/".LD.'excerpt'.RD."/", preg_quote($excerpt), $output, count(explode(LD.'excerpt'.RD, $this->EE->TMPL->tagdata)) - 1);
			$output = preg_replace("/".LD.'full_text'.RD."/", preg_quote($full_text), $output, count(explode(LD.'full_text'.RD, $this->EE->TMPL->tagdata)) - 1);
		
			// Parse member_path
			
			if (count($m_paths) > 0)
			{
				foreach ($m_paths as $val)
				{
					$output = preg_replace(
						"/".preg_quote($val['0'], '/')."/",
						$this->EE->functions->create_url($val['1'].'/'.$row['member_id']),
						$output,
						1
					);
				}
			}
		
		}
		
		$this->EE->TMPL->tagdata = $output;
		
		// Add new pagination
		$this->EE->TMPL->tagdata = $pagination->render($this->EE->TMPL->tagdata);
		
		// Parse lang variables
		$swap = array(
			'lang:total_search_results'	=>	lang('search_total_results'),
			'lang:search_engine'		=>	lang('search_engine'),
			'lang:search_results'		=>	lang('search_results'),
			'lang:search'				=>	lang('search'),
			'lang:title'				=>	lang('search_title'),
			'lang:channel'				=>	lang('search_channel'),
			'lang:excerpt'				=>	lang('search_excerpt'),
			'lang:author'				=>	lang('search_author'),
			'lang:date'					=>	lang('search_date'),
			'lang:total_comments'		=>	lang('search_total_comments'),
			'lang:recent_comments'		=>	lang('search_recent_comment_date'),
			'lang:keywords'				=>	lang('search_keywords')
		);
		$this->EE->TMPL->template = $this->EE->functions->var_swap($this->EE->TMPL->template, $swap);

		// Add Old Style Pagination
		// TODO: Remove once old pagination is phased out
		if ($old_pagination)
		{
			if ($pager == '')
			{
				$this->EE->TMPL->template = preg_replace(
					"#".LD."if paginate".RD.".*?".LD."/if".RD."#s",
					'',
					$this->EE->TMPL->template
				);
			}
			else
			{
				$this->EE->TMPL->template = preg_replace(
					"#".LD."if paginate".RD."(.*?)".LD."/if".RD."#s",
					"\\1",
					$this->EE->TMPL->template
				);
			}

			$this->EE->TMPL->template = str_replace(
				LD.'paginate'.RD,
				$pager,
				$this->EE->TMPL->template
			);

			$this->EE->TMPL->template = str_replace(
				LD.'page_count'.RD,
				$page_count,
				$this->EE->TMPL->template
			);
		}
		
		return stripslashes($this->EE->TMPL->tagdata);
	}
	
	// --------------------------------------------------------------------------

	/**
	 * Simple Search Form
	 *
	 * Generate the simple search form
	 */
	function simple_form()
	{
		$meta = $this->_build_meta_array();

		$data['hidden_fields'] = array(
			'ACT'	=> $this->EE->functions->fetch_action_id('Search', 'do_search'),
			'XID'	=> '',
			'RES'	=> $this->EE->TMPL->fetch_param('results'),
			'meta'	=> $meta
		);
		
		if ($this->EE->TMPL->fetch_param('name') !== FALSE && 
			preg_match("#^[a-zA-Z0-9_\-]+$#i", $this->EE->TMPL->fetch_param('name')))
		{
			$data['name'] = $this->EE->TMPL->fetch_param('name');
		} 
		
		if ($this->EE->TMPL->fetch_param('id') !== FALSE && 
			preg_match("#^[a-zA-Z0-9_\-]+$#i", $this->EE->TMPL->fetch_param('id')))
		{
			$data['id'] = $this->EE->TMPL->fetch_param('id');
			$this->EE->TMPL->log_item('Simple Search Form:  The \'id\' parameter has been deprecated.  Please use form_id');
		}
		else
		{
			$data['id'] = $this->EE->TMPL->form_id;
		}
		
		$data['class'] = $this->EE->TMPL->form_class;
		
		$res  = $this->EE->functions->form_declaration($data);
		
		$res .= stripslashes($this->EE->TMPL->tagdata);
		
		$res .= "</form>";
		
		return $res;
	}


	/** ----------------------------------------
	/**  Advanced Search Form
	/** ----------------------------------------*/
	function advanced_form()
	{
		$this->EE->lang->loadfile('search');
		$this->EE->load->library('api');
		$this->EE->api->instantiate('channel_categories');
		
		/** ----------------------------------------
		/**  Fetch channels and categories
		/** ----------------------------------------*/
		
		// First we need to grab the name/ID number of all channels and categories
		
		$sql = "SELECT channel_title, channel_id, cat_group FROM exp_channels WHERE ";
		
		$sql .= "site_id = '".$this->EE->db->escape_str($this->EE->config->item('site_id'))."' ";
	
		if ($channel = $this->EE->TMPL->fetch_param('channel'))
		{
			$xql = "SELECT channel_id FROM exp_channels WHERE site_id = '".$this->EE->db->escape_str($this->EE->config->item('site_id'))."' ";
		
			$xql .= $this->EE->functions->sql_andor_string($channel, 'channel_name');
				
			$query = $this->EE->db->query($xql);
			
			if ($query->num_rows() > 0)
			{
				if ($query->num_rows() == 1)
				{
					$sql .= "AND channel_id = '".$query->row('channel_id') ."' ";
				}
				else
				{
					$sql .= "AND (";
					
					foreach ($query->result_array() as $row)
					{
						$sql .= "channel_id = '".$row['channel_id']."' OR ";
					}
					
					$sql = substr($sql, 0, - 3);
					
					$sql .= ") ";
				}
			}
		}
		
		$sql .= " ORDER BY channel_title";
		
		$query = $this->EE->db->query($sql);
		
		foreach ($query->result_array() as $row)
		{
			$this->channel_array[$row['channel_id']] = array($row['channel_title'], $row['cat_group']);
		}

		$nested = ($this->EE->TMPL->fetch_param('cat_style') !== FALSE && $this->EE->TMPL->fetch_param('cat_style') == 'nested') ? 'y' : 'n';
		

		/** ----------------------------------------
		/**  Build select list
		/** ----------------------------------------*/
		
		$channel_names = "<option value=\"null\" selected=\"selected\">".lang('search_any_channel')."</option>\n";

		// Load the form helper
		$this->EE->load->helper('form');

		foreach ($this->channel_array as $key => $val)
		{
			$channel_names .= "<option value=\"".$key."\">".form_prep($val['0'])."</option>\n";
		}
		
	
		$tagdata = $this->EE->TMPL->tagdata; 
		
		/** ----------------------------------------
		/**  Parse variables
		/** ----------------------------------------*/
		
		$swap = array(
			'lang:search_engine'			=> lang('search_engine'),
			'lang:search'					=> lang('search'),
			'lang:search_by_keyword'		=> lang('search_by_keyword'),
			'lang:search_in_titles'			=> lang('search_in_titles'),
			'lang:search_in_entries'		=> lang('search_entries'),
			'lang:search_everywhere'		=> lang('search_everywhere'),
			'lang:search_by_member_name'	=> lang('search_by_member_name'),
			'lang:exact_name_match'			=> lang('search_exact_name_match'),
			'lang:exact_phrase_match'		=> lang('search_exact_phrase_match'),
			'lang:also_search_comments'		=> lang('search_also_search_comments'),
			'lang:any_date'					=> lang('search_any_date'),
			'lang:today_and'				=> lang('search_today_and'),
			'lang:this_week_and'			=> lang('search_this_week_and'),
			'lang:one_month_ago_and'		=> lang('search_one_month_ago_and'),
			'lang:three_months_ago_and'		=> lang('search_three_months_ago_and'),
			'lang:six_months_ago_and'		=> lang('search_six_months_ago_and'),
			'lang:one_year_ago_and'			=> lang('search_one_year_ago_and'),
			'lang:channels'					=> lang('search_channels'),
			'lang:weblogs'					=> lang('search_channels'),
			'lang:categories'				=> lang('search_categories'),
			'lang:newer'					=> lang('search_newer'),
			'lang:older'					=> lang('search_older'),
			'lang:sort_results_by'			=> lang('search_sort_results_by'),
			'lang:date'						=> lang('search_date'),
			'lang:title'					=> lang('search_title'),
			'lang:most_comments'			=> lang('search_most_comments'),
			'lang:recent_comment'			=> lang('search_recent_comment'),
			'lang:descending'				=> lang('search_descending'),
			'lang:ascending'				=> lang('search_ascending'),
			'lang:search_entries_from'		=> lang('search_entries_from'),
			'lang:any_category'				=> lang('search_any_category'),
			'lang:search_any_words'			=> lang('search_any_words'),
			'lang:search_all_words'			=> lang('search_all_words'),
			'lang:search_exact_word'		=> lang('search_exact_word'),
			'channel_names' 				=> $channel_names
		);
	
		
		$tagdata = $this->EE->functions->var_swap($tagdata, $swap);
		
		$this->EE->TMPL->template = $this->EE->functions->var_swap($this->EE->TMPL->template, $swap);
		
		/** ----------------------------------------
		/**  Create form
		/** ----------------------------------------*/
		
		$meta = $this->_build_meta_array();
		 
		$data['class'] = $this->EE->TMPL->form_class;
		$data['hidden_fields'] = array(
			'ACT'	=> $this->EE->functions->fetch_action_id('Search', 'do_search'),
			'XID'	=> '',
			'RES'	=> $this->EE->TMPL->fetch_param('results'),
			'meta'	=> $meta
		);
		
		if ($this->EE->TMPL->fetch_param('name') !== FALSE && 
			preg_match("#^[a-zA-Z0-9_\-]+$#i", $this->EE->TMPL->fetch_param('name')))
		{
			$data['name'] = $this->EE->TMPL->fetch_param('name');
		} 
		
		if ($this->EE->TMPL->fetch_param('id') !== FALSE && 
			preg_match("#^[a-zA-Z0-9_\-]+$#i", $this->EE->TMPL->fetch_param('id')))
		{
			$data['id'] = $this->EE->TMPL->fetch_param('id');
			$this->EE->TMPL->log_item('Advanced Search Form:  The \'id\' parameter has been deprecated.  Please use form_id');
		}
		elseif ($this->EE->TMPL->form_id != '')
		{
			$data['id'] = $this->EE->TMPL->form_id;
		}
		else
		{
			$data['id'] = 'searchform';
		}
		
		$res  = $this->EE->functions->form_declaration($data);
		
		$res .= $this->search_js_switcher($nested, $data['id']);
		
		$res .= stripslashes($tagdata);
		
		$res .= "</form>";

		return $res;
	}

	/**
	 * JavaScript channel/category switch code
	 */
	function search_js_switcher($nested='n',$id='searchform')
	{
		$this->EE->load->library('api');
		$this->EE->api->instantiate('channel_categories');
		
		 $cat_array = $this->EE->api_channel_categories->category_form_tree(
			$nested, 
			$this->EE->TMPL->fetch_param('category')
		);
		
		ob_start();
?>
<script type="text/javascript">
//<![CDATA[

var firstcategory = 1;
var firststatus = 1;

function changemenu(index)
{ 
	var categories = new Array();
	
	var i = firstcategory;
	var j = firststatus;
	
	var theSearchForm = false
	
	if (document.searchform)
	{
		theSearchForm = document.searchform;
	}
	else if (document.getElementById('<?php echo $id; ?>'))
	{
		theSearchForm = document.getElementById('<?php echo $id; ?>');
	}
	
	if (theSearchForm.elements['channel_id'])
	{
		var channel_obj = theSearchForm.elements['channel_id'];
	}
	else
	{
		var channel_obj = theSearchForm.elements['channel_id[]'];
	}
	
	var channels = channel_obj.options[index].value;
	
	var reset = 0;

	for (var g = 0; g < channel_obj.options.length; g++)
	{
		if (channel_obj.options[g].value != 'null' && 
			channel_obj.options[g].selected == true)
		{
			reset++;
		}
	} 
  
	with (theSearchForm.elements['cat_id[]'])
	{	<?php
		
		foreach ($this->channel_array as $key => $val)
		{
		
		?>
		
		if (channels == "<?php echo $key ?>")
		{	<?php echo "\n";
			if (count($cat_array) > 0)
			{
				$last_group = 0;

				foreach ($cat_array as $k => $v)
				{
					if (in_array($v['0'], explode('|', $val['1'])))
					{

						if ($last_group == 0 OR $last_group != $v['0'])
						{?>
			categories[i] = new Option("-------", ""); i++; <?php echo "\n";
							$last_group = $v['0'];
						}

			// Note: this kludgy indentation is so that the JavaScript will look nice when it's renedered on the page		
			?>
			categories[i] = new Option("<?php echo addslashes($v['2']);?>", "<?php echo $v['1'];?>"); i++; <?php echo "\n";
					}
				}
			}
			  
			?>

		} // END if channels
			
		<?php
		 
		} // END OUTER FOREACH
		 
		?> 
		
		if (reset > 1)
		{
			 categories = new Array();
		}

		spaceString = eval("/!-!/g");
		
		with (theSearchForm.elements['cat_id[]'])
		{
			for (i = length-1; i >= firstcategory; i--)
				options[i] = null;
			
			for (i = firstcategory; i < categories.length; i++)
			{
				options[i] = categories[i];
				options[i].text = options[i].text.replace(spaceString, String.fromCharCode(160));
			}
			
			options[0].selected = true;
		}
		
	}
}

//]]>
</script>
	
		<?php
	
		$buffer = ob_get_contents();
		
		ob_end_clean();
	
		return $buffer;
	}

}
// END CLASS

/* End of file mod.search.php */
/* Location: ./system/expressionengine/modules/search/mod.search.php */
