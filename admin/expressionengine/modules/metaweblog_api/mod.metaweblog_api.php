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
 * ExpressionEngine Metaweblog API Module
 *
 * @package		ExpressionEngine
 * @subpackage	Modules
 * @category	Modules
 * @author		EllisLab Dev Team
 * @link		http://expressionengine.com
 */
class Metaweblog_api {

	var $return_data	= ''; 						// Bah!
	var $LB				= "\r\n";					// Line Break for Entry Output

	var $status			= '';						// Retrieving
	var $channel		= '';
	var $fields			= array();
	var $userdata		= array();

	var $title			= 'MetaWeblog API Entry';	// Default Title
	var $channel_id		= '1';						// Default Channel ID
	var $site_id		= '1';						// Default Site ID
	var $channel_url	= '';						// Channel URL for Permalink
	var $comment_url	= '';						// Comment URL for Permalink
	var $deft_category	= '';						// Default Category for Channel

	var $excerpt_field	= '1';						// Default Except Field ID
	var $content_field	= '2';						// Default Content Field ID
	var $more_field		= '3';						// Default More Field ID
	var $keywords_field = '0';						// Default Keywords Field ID
	var $upload_dir		= '';						// Upload Directory for Media Files

	var $field_name		= 'body';					// Default Field Name
	var $entry_status	= 'null';					// Entry Status from Configuration
	var $field_data		= array();					// Array of Field Data
	var $field_format	= array();					// Array of Field Formats
	var $categories 	= array();					// Categories (new/edit/get entry)
	var $assign_parents	= TRUE;						// Assign cat parents to post
	var $cat_parents	= array();					// Parent categories of new/edited entry

	var $parse_type		= FALSE;					// Use Typography class when sending entry?
	var $html_format	= 'none';					// Channel's HTML Formatting Preferences


	/**
	 * Constructor
	 *
	 * @access	public
	 */
	function Metaweblog_api()
	{
		// Make a local reference to the ExpressionEngine super object
		$this->EE =& get_instance();

		$this->EE->lang->loadfile('metaweblog_api');

		$id = ( isset($_GET['id']) && is_numeric($_GET['id'])) ? $_GET['id'] : '1';

		$this->assign_parents = ($this->EE->config->item('auto_assign_cat_parents') == 'n') ? FALSE : TRUE;

		/** ----------------------------------------
		/**  Configuration Options
		/** ----------------------------------------*/

		$query = $this->EE->db->get_where('metaweblog_api', array('metaweblog_id' => $id));

		if ($query->num_rows() > 0)
		{
			foreach($query->row_array() as $name => $pref)
			{
				$name = str_replace('metaweblog_', '', $name);
				$name = str_replace('_id', '', $name);

				if ($pref == 'y' OR $pref == 'n')
				{
					$this->{$name} = ($pref == 'y') ? TRUE : FALSE;
				}
				else
				{
					$this->{$name} = $pref;
				}
			}
		}
	}

	// --------------------------------------------------------------------

	/**
	 * Incoming MetaWeblog API Requests
	 *
	 * @access	public
	 * @return	void
	 */
	function incoming()
	{
		/** ---------------------------------
		/**  Load the XML-RPC Files
		/** ---------------------------------*/

		$this->EE->load->library('xmlrpc');
		$this->EE->load->library('xmlrpcs');

		/* ---------------------------------
		/*  Specify Functions
		/*	Normally, we would add a signature and docstring to the array for
		/*	each function, but since these are widespread and well known
		/*	functions I just skipped it.
		/* ---------------------------------*/

		$functions = array(	'metaWeblog.newPost'		=> array('function' => 'Metaweblog_api.newPost'),
							'metaWeblog.editPost'		=> array('function' => 'Metaweblog_api.editPost'),
							'metaWeblog.getPost'		=> array('function' => 'Metaweblog_api.getPost'),
							'metaWeblog.getCategories'	=> array('function' => 'Metaweblog_api.getCategories'),
							'metaWeblog.getRecentPosts'	=> array('function' => 'Metaweblog_api.getRecentPosts'),
							'metaWeblog.deletePost'		=> array('function' => 'Metaweblog_api.deletePost'),
							'metaWeblog.getUsersBlogs'	=> array('function' => 'Metaweblog_api.getUsersBlogs'),
							'metaWeblog.newMediaObject' => array('function' => 'Metaweblog_api.newMediaObject'),

							'blogger.getUserInfo'		=> array('function' => 'Metaweblog_api.getUserInfo'),
							'blogger.getUsersBlogs'		=> array('function' => 'Metaweblog_api.getUsersBlogs'),
							'blogger.deletePost'		=> array('function' => 'Metaweblog_api.deletePost'),

							'mt.getCategoryList'		=> array('function' => 'Metaweblog_api.getCategoryList'),
							'mt.get_postCategories'		=> array('function' => 'Metaweblog_api.get_postCategories'),
							'mt.getPostCategories'		=> array('function' => 'Metaweblog_api.get_postCategories'),							
							'mt.publishPost'			=> array('function' => 'Metaweblog_api.publishPost'),
							'mt.getRecentPostTitles'	=> array('function' => 'Metaweblog_api.getRecentPostTitles'),
							'mt.setPostCategories'		=> array('function' => 'Metaweblog_api.setPostCategories'),
							'mt.supportedMethods'		=> array('function' => 'this.listMethods'),
							'mt.supportedTextFilters'	=> array('function' => 'Metaweblog_api.supportedTextFilters')
							);

		/** ---------------------------------
		/**  Instantiate the Server Class
		/** ---------------------------------*/

		$this->EE->xmlrpcs->initialize(array('functions' => $functions, 'object' => $this, 'xss_clean' => FALSE));
		$this->EE->xmlrpcs->serve();
	}

	// --------------------------------------------------------------------

	/**
	 * Submit New Post
	 *
	 * @access	public
	 * @param	parameter list
	 * @return	void
	 */
	function newPost($plist)
	{
		$parameters = $plist->output_parameters();

		if ( ! $this->fetch_member_data($parameters['1'], $parameters['2']))
		{
			return $this->EE->xmlrpc->send_error_message('802', $this->EE->lang->line('invalid_access'));
		}

		/** ---------------------------------------
		/**  Parse Out Channel Information
		/** ---------------------------------------*/

		$this->parse_channel($parameters['0']);

		if ($this->entry_status != '' && $this->entry_status != 'null')
		{
			$this->status = $this->entry_status;
		}
		else
		{
			$this->status = ($parameters['4'] == '0') ? 'closed' : 'open';
		}

		/** ---------------------------------------
		/**  Default Channel Data for channel_id
		/** ---------------------------------------*/

		$this->EE->db->select('deft_comments, cat_group, deft_category, channel_title, channel_url,
								channel_notify_emails, channel_notify, comment_url');
		$this->EE->db->where('channel_id', $this->channel_id);
		$query = $this->EE->db->get('channels');

		if ($query->num_rows() == 0)
		{
			return $this->EE->xmlrpc->send_error_message('802', $this->EE->lang->line('invalid_channel'));
		}

		foreach($query->row_array() as $key => $value)
		{
			${$key} =  $value;
		}

		$notify_address = ($query->row('channel_notify')  == 'y' AND $query->row('channel_notify_emails')  != '') ? $query->row('channel_notify_emails')  : '';

		// Get channel field Settings
		$this->get_settings($this->channel_id, 'new');

		/** ---------------------------------------
		/**  Parse Data Struct
		/** ---------------------------------------*/

		$this->title = $parameters['3']['title'];
		$ping_urls	 = ( ! isset($parameters['3']['mt_tb_ping_urls'])) ? '' : implode("\n",$parameters['3']['mt_tb_ping_urls']);

		$this->field_data['excerpt']  = ( ! isset($parameters['3']['mt_excerpt'])) ? '' : $parameters['3']['mt_excerpt'];
		$this->field_data['content']  = ( ! isset($parameters['3']['description'])) ? '' : $parameters['3']['description'];
		$this->field_data['more']	  = ( ! isset($parameters['3']['mt_text_more'])) ? '' : $parameters['3']['mt_text_more'];
		$this->field_data['keywords'] = ( ! isset($parameters['3']['mt_keywords'])) ? '' : $parameters['3']['mt_keywords'];

		if (isset($parameters['3']['mt_allow_comments']))
		{
			$deft_comments = ($parameters['3']['mt_allow_comments'] == 1) ? 'y' : 'n';
		}

		if (isset($parameters['3']['categories']) && count($parameters['3']['categories']) > 0)
		{
			$cats = array();

			foreach($parameters['3']['categories'] as $cat)
			{
				if (trim($cat) != '')
				{
					$cats[] = $cat;
				}
			}

			if (count($cats) == 0 && ! empty($deft_category))
			{
				$cats = array($deft_category);
			}

			if (count($cats) > 0)
			{
				$this->check_categories(array_unique($cats));
			}
		}
		elseif( ! empty($deft_category))
		{
			$this->check_categories(array($deft_category));
		}

		if ( ! empty($parameters['3']['dateCreated']))
		{
			$entry_date = $this->iso8601_decode($parameters['3']['dateCreated']);
		}
		else
		{
			$entry_date = $this->EE->localize->now;
		}
		
		/** ---------------------------------
		/**  Build our query string
		/** --------------------------------*/

		$metadata = array(
							'channel_id'		=> $this->channel_id,
							'author_id'			=> $this->userdata['member_id'],
							'title'				=> $this->title,
							'ip_address'		=> $this->EE->input->ip_address(),
							'entry_date'		=> $entry_date,
							'edit_date'			=> gmdate("YmdHis", $entry_date),
							'year'				=> gmdate('Y', $entry_date),
							'month'				=> gmdate('m', $entry_date),
							'day'				=> gmdate('d', $entry_date),
							'status'			=> $this->status,
							'allow_comments'	=> $deft_comments,
							'ping_servers'		=> array()
						  );

		/** ---------------------------------------
		/**  Parse Channel Field Data
		/** ---------------------------------------*/

		$entry_data = array('channel_id' => $this->channel_id);

		// Default formatting for all of the channel's fields...

		foreach($this->fields as $field_id => $field_data)
		{
			$entry_data['field_ft_'.$field_id] = $field_data['1'];
		}

		$convert_breaks = ( ! isset($parameters['3']['mt_convert_breaks'])) ? '' : $parameters['3']['mt_convert_breaks'];

		if ($convert_breaks === '0')
		{
			// MarsEdit sends '0' as synonymous with 'none'
			$convert_breaks = 'none';
		}
		elseif ($convert_breaks != '')
		{
			$plugins = $this->fetch_plugins();

			if ( ! in_array($convert_breaks, $plugins))
			{
				$convert_breaks = '';
			}
		}

		if (isset($this->fields[$this->excerpt_field]))
		{
			if (isset($entry_data['field_id_'.$this->excerpt_field]))
			{
				$entry_data['field_id_'.$this->excerpt_field] .= $this->field_data['excerpt'];
			}
			else
			{
				$entry_data['field_id_'.$this->excerpt_field] = $this->field_data['excerpt'];
			}

			$entry_data['field_ft_'.$this->excerpt_field] = ($convert_breaks != '') ? $convert_breaks : $this->fields[$this->excerpt_field]['1'];
		}

		if (isset($this->fields[$this->content_field]))
		{
			if (isset($entry_data['field_id_'.$this->content_field]))
			{
				$entry_data['field_id_'.$this->content_field] .= $this->field_data['content'];
			}
			else
			{
				$entry_data['field_id_'.$this->content_field] = $this->field_data['content'];
			}

			$entry_data['field_ft_'.$this->content_field] = ($convert_breaks != '') ? $convert_breaks : $this->fields[$this->content_field]['1'];
		}

		if (isset($this->fields[$this->more_field]))
		{
			if (isset($entry_data['field_id_'.$this->more_field]))
			{
				$entry_data['field_id_'.$this->more_field] .= $this->field_data['more'];
			}
			else
			{
				$entry_data['field_id_'.$this->more_field] = $this->field_data['more'];
			}

			$entry_data['field_ft_'.$this->more_field] = ($convert_breaks != '') ? $convert_breaks : $this->fields[$this->more_field]['1'];
		}

		if (isset($this->fields[$this->keywords_field]))
		{
			if (isset($entry_data['field_id_'.$this->keywords_field]))
			{
				$entry_data['field_id_'.$this->keywords_field] .= $this->field_data['keywords'];
			}
			else
			{
				$entry_data['field_id_'.$this->keywords_field] = $this->field_data['keywords'];
			}

			$entry_data['field_ft_'.$this->keywords_field] = ($convert_breaks != '') ? $convert_breaks : $this->fields[$this->keywords_field]['1'];
		}

		/** ---------------------------------
		/**  Insert the entry data
		/** ---------------------------------*/

		$entry_data['site_id']	= $this->site_id;
		$entry_data['versioning_enabled'] = 'n';

		$data = array_merge($metadata, $entry_data);
		
		if (count($this->categories) > 0)
		{
			foreach($this->categories as $cat_id => $cat_name)
			{
				$data['category'][] = $cat_id;
			}
		}

		$this->EE->session->userdata = array_merge(
			$this->EE->session->userdata,
			array(
				'group_id'			=> $this->userdata['group_id'],
				'member_id'			=> $this->userdata['member_id'],
				'assigned_channels'	=> $this->userdata['assigned_channels']
			)
		);

		$this->EE->load->library('api');
		$this->EE->api->instantiate('channel_entries');
		$this->EE->api->instantiate('channel_fields');

		$this->EE->api_channel_fields->setup_entry_settings($this->channel_id, $data);
				
		if ( ! $this->EE->api_channel_entries->submit_new_entry($this->channel_id, $data))
		{
			$errors = $this->EE->api_channel_entries->get_errors();

			$response = array(
				'errors' => array($errors, 'array')
			);

			return $this->EE->xmlrpc->send_response($response);
		}
		
		//Return Entry ID of new entry - defaults to string, so nothing fancy
		$response = $this->EE->api_channel_entries->entry_id;

		return $this->EE->xmlrpc->send_response($response);
	}

	// --------------------------------------------------------------------

	/**
	 * Edit Post
	 *
	 * @access	public
	 * @param	parameter list
	 * @return	void
	 */
	function editPost($plist)
	{
		$parameters = $plist->output_parameters();

		if ( ! $this->fetch_member_data($parameters['1'], $parameters['2']))
		{
			return $this->EE->xmlrpc->send_error_message('802', $this->EE->lang->line('invalid_access'));
		}

		if ( ! $this->userdata['can_access_content'] && $this->userdata['group_id'] != '1')
		{
			return $this->EE->xmlrpc->send_error_message('803', $this->EE->lang->line('invalid_access'));
		}

		if ( ! $this->userdata['can_edit_other_entries'] && $this->userdata['group_id'] != '1')
		{
			// If there aren't any channels assigned to the user, bail out

			if (count($this->userdata['assigned_channels']) == 0)
			{
				return $this->EE->xmlrpc->send_error_message('804', $this->EE->lang->line('invalid_access'));
			}
		}

		/** ---------------------------------------
		/**  Retrieve Entry Information
		/** ---------------------------------------*/

		$entry_id = $parameters['0'];

		$sql = "SELECT wt.channel_id, wt.author_id, wt.title, wt.url_title,
				wb.channel_title, wb.channel_url
				FROM (exp_channel_titles wt, exp_channels wb)
				WHERE wt.channel_id = wb.channel_id
				AND wt.entry_id = '".$this->EE->db->escape_str($entry_id)."' ";

		$query = $this->EE->db->query($sql);

		if ($query->num_rows() == 0)
		{
			return $this->EE->xmlrpc->send_error_message('805', $this->EE->lang->line('no_entry_found'));
		}

		if ( ! $this->userdata['can_edit_other_entries'] && $this->userdata['group_id'] != '1')
		{
			if ($query->row('author_id')  != $this->userdata['member_id'])
			{
				return $this->EE->xmlrpc->send_error_message('806', $this->EE->lang->line('entry_uneditable'));
			}
		}

		$this->channel_id	= $query->row('channel_id');
		$this->title		= $query->row('title');

		$this->parse_channel($this->channel_id);

		if ($this->entry_status != '' && $this->entry_status != 'null')
		{
			$this->status = $this->entry_status;
		}
		else
		{
			$this->status = ($parameters['4'] == '0') ? 'closed' : 'open';
		}

		/** ---------------------------------------
		/**  Parse Channel Meta-Information
		/** ---------------------------------------*/

		$this->title = $parameters['3']['title'];

		$ping_urls		 = ( ! isset($parameters['3']['mt_tb_ping_urls'])) ? '' : implode("\n",$parameters['3']['mt_tb_ping_urls']);

		$this->field_data['excerpt']  = ( ! isset($parameters['3']['mt_excerpt'])) ? '' : $parameters['3']['mt_excerpt'];
		$this->field_data['content']  = ( ! isset($parameters['3']['description'])) ? '' : $parameters['3']['description'];
		$this->field_data['more']	  = ( ! isset($parameters['3']['mt_text_more'])) ? '' : $parameters['3']['mt_text_more'];
		$this->field_data['keywords'] = ( ! isset($parameters['3']['mt_keywords'])) ? '' : $parameters['3']['mt_keywords'];

		/** ---------------------------------
		/**  Build our query string
		/** ---------------------------------*/

		$metadata = array(
							'entry_id'			=> $entry_id,
							'title'				=> $this->title,
							'ip_address'		=> $this->EE->input->ip_address(),
							'status'			=> $this->status
						  );

		if (isset($parameters['3']['mt_allow_comments']))
		{
			$metadata['allow_comments'] = ($parameters['3']['mt_allow_comments'] == 1) ? 'y' : 'n';
		}

		if ( ! empty($parameters['3']['dateCreated']))
		{
			$metadata['entry_date'] = $this->iso8601_decode($parameters['3']['dateCreated']);
		}

		$metadata['edit_date'] = date("YmdHis");

		/** ---------------------------------------
		/**  Parse Channel Field Data
		/** ---------------------------------------*/

		$entry_data = array('channel_id' => $this->channel_id);

		$convert_breaks = ( ! isset($parameters['3']['mt_convert_breaks'])) ? '' : $parameters['3']['mt_convert_breaks'];

		if ($convert_breaks === '0')
		{
			// MarsEdit sends '0' as synonymous with 'none'
			$convert_breaks = 'none';
		}
		elseif ($convert_breaks != '')
		{
			$plugins = $this->fetch_plugins();

			if ( ! in_array($convert_breaks, $plugins))
			{
				$convert_breaks = '';
			}
		}

		if (isset($this->fields[$this->excerpt_field]))
		{
			if (isset($entry_data['field_id_'.$this->excerpt_field]))
			{
				$entry_data['field_id_'.$this->excerpt_field] .= $this->field_data['excerpt'];
			}
			else
			{
				$entry_data['field_id_'.$this->excerpt_field] = $this->field_data['excerpt'];
			}

			$entry_data['field_ft_'.$this->excerpt_field] = ($convert_breaks != '') ? $convert_breaks : $this->fields[$this->excerpt_field]['1'];
		}

		if (isset($this->fields[$this->content_field]))
		{
			if (isset($entry_data['field_id_'.$this->content_field]))
			{
				$entry_data['field_id_'.$this->content_field] .= $this->field_data['content'];
			}
			else
			{
				$entry_data['field_id_'.$this->content_field] = $this->field_data['content'];
			}

			$entry_data['field_ft_'.$this->content_field] = ($convert_breaks != '') ? $convert_breaks : $this->fields[$this->content_field]['1'];
		}

		if (isset($this->fields[$this->more_field]))
		{
			if (isset($entry_data['field_id_'.$this->more_field]))
			{
				$entry_data['field_id_'.$this->more_field] .= $this->field_data['more'];
			}
			else
			{
				$entry_data['field_id_'.$this->more_field] = $this->field_data['more'];
			}

			$entry_data['field_ft_'.$this->more_field] = ($convert_breaks != '') ? $convert_breaks : $this->fields[$this->more_field]['1'];
		}

		if (isset($this->fields[$this->keywords_field]))
		{
			if (isset($entry_data['field_id_'.$this->keywords_field]))
			{
				$entry_data['field_id_'.$this->keywords_field] .= $this->field_data['keywords'];
			}
			else
			{
				$entry_data['field_id_'.$this->keywords_field] = $this->field_data['keywords'];
			}

			$entry_data['field_ft_'.$this->keywords_field] = ($convert_breaks != '') ? $convert_breaks : $this->fields[$this->keywords_field]['1'];
		}

		/** ---------------------------------
		/**  Update the entry data
		/** ---------------------------------*/

		$this->EE->db->query($this->EE->db->update_string('exp_channel_titles', $metadata, "entry_id = '$entry_id'"));
		$this->EE->db->query($this->EE->db->update_string('exp_channel_data', $entry_data, "entry_id = '$entry_id'"));

		/** ---------------------------------
		/**  Insert Categories, if any
		/** ---------------------------------*/

		if ( ! empty($parameters['3']['categories']) && count($parameters['3']['categories']) > 0)
		{
			$this->check_categories($parameters['3']['categories']);
		}

		if (count($this->categories) > 0)
		{
			$this->EE->db->query("DELETE FROM exp_category_posts WHERE entry_id = '$entry_id'");

			foreach($this->categories as $cat_id => $cat_name)
			{
				$this->EE->db->query("INSERT INTO exp_category_posts
							(entry_id, cat_id)
							VALUES
							('".$entry_id."', '$cat_id')");
			}
		}

		/** ---------------------------------
		/**  Clear caches if needed
		/** ---------------------------------*/

		if ($this->EE->config->item('new_posts_clear_caches') == 'y')
		{
			$this->EE->functions->clear_caching('all');
		}
		else
		{
			$this->EE->functions->clear_caching('sql');
		}

		/** ---------------------------------
		/**  Count your chickens after they've hatched
		/** ---------------------------------*/

		$this->EE->stats->update_channel_stats($this->channel_id);

		/** ---------------------------------
		/**  Return Boolean TRUE
		/** ---------------------------------*/

		return $this->EE->xmlrpc->send_response(array(1,'boolean'));

	}

	// --------------------------------------------------------------------

	/**
	 * MT API: Publish Post
	 *
	 * @access	public
	 * @param	parameter list
	 * @return	void
	 */
	function publishPost($plist)
	{
		/** ---------------------------------
		/**  Clear caches
		/** ---------------------------------*/

		if ($this->EE->config->item('new_posts_clear_caches') == 'y')
		{
			$this->EE->functions->clear_caching('all');
		}
		else
		{
			$this->EE->functions->clear_caching('sql');
		}

		/** ---------------------------------
		/**  Return Boolean TRUE
		/** ---------------------------------*/

		return $this->EE->xmlrpc->send_response(array(1,'boolean'));

	}

	// --------------------------------------------------------------------

	/**
	 * Get a single post
	 *
	 * @access	public
	 * @param	parameter list
	 * @return	void
	 */
	function getPost($plist)
	{
		$parameters = $plist->output_parameters();

		return $this->getRecentPosts($plist, $parameters['0']);
	}

	// --------------------------------------------------------------------

	/**
	 * Get recent posts
	 *
	 * @access	public
	 * @param	parameter list
	 * @return	void
	 */
	function getRecentPosts($plist, $entry_id = '')
	{
		$parameters = $plist->output_parameters();

		if ( ! $this->fetch_member_data($parameters['1'], $parameters['2']))
		{
			return $this->EE->xmlrpc->send_error_message('802', $this->EE->lang->line('invalid_access'));
		}

		if ( ! $this->userdata['can_access_content'] && $this->userdata['group_id'] != '1')
		{
			return $this->EE->xmlrpc->send_error_message('803', $this->EE->lang->line('invalid_access'));
		}

		/** ---------------------------------------
		/**  Parse Out Channel Information
		/** ---------------------------------------*/

		if ($entry_id == '')
		{
			$this->parse_channel($parameters['0']);
			$limit = ( ! empty($parameters['3']) && is_numeric($parameters['3'])) ? $parameters['3'] : '10';
		}

		/** ---------------------------------------
		/**  Perform Query
		/** ---------------------------------------*/

		$sql = "SELECT DISTINCT(wt.entry_id), wt.title, wt.url_title, wt.channel_id,
				wt.author_id, wt.entry_date, wt.allow_comments, 
				exp_channel_data.*
				FROM	exp_channel_titles wt, exp_channel_data
				WHERE wt.entry_id = exp_channel_data.entry_id ";

		if ($this->userdata['group_id'] != '1' && ! $this->userdata['can_edit_other_entries'])
		{
			$sql .= "AND wt.author_id = '".$this->userdata['member_id']."' ";
		}

		if ($entry_id != '')
		{
			$sql .= "AND wt.entry_id = '{$entry_id}' ";
		}
		else
		{
			$sql .= str_replace('exp_channels.channel_id','wt.channel_id', $this->channel_sql)." ";
		}

		if ($entry_id == '')
		{
			$sql .= "ORDER BY entry_date desc LIMIT 0, {$limit}";
		}

		$query = $this->EE->db->query($sql);

		if ($query->num_rows() == 0)
		{
			return $this->EE->xmlrpc->send_error_message('805', $this->EE->lang->line('no_entries_found'));
		}

		if ($entry_id != '')
		{
			$this->parse_channel($query->row('channel_id') );
		}

		/** ----------------------------------------
		/**  Instantiate Typography class
		/** ----------------------------------------*/

	  	if ($this->parse_type === TRUE)
	  	{
			$this->EE->load->library('typography');
			$this->EE->typography->initialize(array(
						'encode_email'	=> FALSE)
						);
			$this->EE->config->set_item('enable_emoticons', 'n');
		}

		/** ---------------------------------------
		/**  Process Output
		/** ---------------------------------------*/

		$settings = array();
			$settings['html_format']	= $this->html_format;
			$settings['auto_links']		= 'n';
			$settings['allow_img_url']	= 'y';

		$response = array();

		foreach($query->result_array() as $row)
		{
			$convert_breaks = 'none';
			$link = $this->EE->functions->remove_double_slashes($this->comment_url.'/'.$query->row('url_title') .'/');
			
			// Fields:  Textarea and Text Input Only

			$this->field_data = array('excerpt' => '', 'content' => '', 'more' => '', 'keywords' => '');

			if (isset($this->fields[$this->excerpt_field]))
			{
				if ($this->parse_type === TRUE)
	  			{
	  				$settings['text_format'] = $row['field_ft_'.$this->excerpt_field];
	  
					$this->field_data['excerpt'] = $this->EE->typography->parse_type($row['field_id_'.$this->excerpt_field], $settings);
				}
				else
				{
					$this->field_data['excerpt'] .= $row['field_id_'.$this->excerpt_field];
				}
			}

			if (isset($this->fields[$this->content_field]))
			{
				$convert_breaks	= $row['field_ft_'.$this->content_field];

				if ($this->parse_type === TRUE)
	  			{
	  				$settings['text_format'] = $row['field_ft_'.$this->content_field];
	  
					$this->field_data['content'] = $this->EE->typography->parse_type($row['field_id_'.$this->content_field], $settings);
				}
				else
				{
					$this->field_data['content'] .= $row['field_id_'.$this->content_field];
				}
			}

			if (isset($this->fields[$this->more_field]))
			{
				if ($this->parse_type === TRUE)
	  			{
	  				$settings['text_format'] = $row['field_ft_'.$this->more_field];
	  
					$this->field_data['more'] = $this->EE->typography->parse_type($row['field_id_'.$this->more_field], $settings);
				}
				else
				{
					$this->field_data['more'] .= $row['field_id_'.$this->more_field];
				}
			}

			if (isset($this->fields[$this->keywords_field]))
			{
				if ($this->parse_type === TRUE)
	  			{
	  				$settings['text_format'] = $row['field_ft_'.$this->keywords_field];
	  
					$this->field_data['keywords'] = $this->EE->typography->parse_type($row['field_id_'.$this->keywords_field], $settings);
				}
				else
				{
					$this->field_data['keywords'] .= $row['field_id_'.$this->keywords_field];
				}
			}


			// Categories

			$cat_array = array();

			$sql = "SELECT	exp_categories.cat_id, exp_categories.cat_name
					FROM	exp_category_posts, exp_categories
					WHERE	exp_category_posts.cat_id = exp_categories.cat_id
					AND		exp_category_posts.entry_id = '".$row['entry_id']."'
					ORDER BY cat_id";

			$results = $this->EE->db->query($sql);

			if ($results->num_rows() > 0)
			{
				foreach($results->result_array() as $rrow)
				{
					$cat_array[] = array($rrow['cat_name'], 'string');
					//$cat_array[] = array($rrow['cat_id'], 'string');
				}
			}

			// Entry Data to XML-RPC form
			$pings = array();
			$entry_data = array(array(
										'userid' =>
										array($row['author_id'],'string'),
										'dateCreated' =>
										array(date('Ymd\TH:i:s',$row['entry_date']).'Z','dateTime.iso8601'),
										'blogid' =>
										array($row['channel_id'],'string'),
										'title' =>
										array($row['title'], 'string'),
										'mt_excerpt' =>
										array($this->field_data['excerpt'],'string'),
										'description' =>
										array($this->field_data['content'],'string'),
										'mt_text_more' =>
										array($this->field_data['more'],'string'),
										'mt_keywords' =>
										array($this->field_data['keywords'],'string'),
										'mt_convert_breaks' =>
										array($convert_breaks,'string'),
										'postid' =>
										array($row['entry_id'],'string'),
										'link' =>
										array($link,'string'),
										'permaLink' =>
										array($link,'string'),
										'categories' =>
										array($cat_array,'array'),
										'mt_allow_comments' =>
										array(($row['allow_comments'] == 'y') ? 1 : 0,'int'),
										'mt_tb_ping_urls' =>
										array($pings,'array')
										),
									'struct');

			array_push($response, $entry_data);
		}

		if ($entry_id != '')
		{
			return $this->EE->xmlrpc->send_response($entry_data);
		}
		else
		{
			return $this->EE->xmlrpc->send_response(array($response, 'array'));
		}
	}


	// --------------------------------------------------------------------

	/**
	 * MT API: get recent post title
	 *
	 * @access	public
	 * @param	parameter list
	 * @return	void
	 */
	function getRecentPostTitles($plist)
	{
		$parameters = $plist->output_parameters();

		if ( ! $this->fetch_member_data($parameters['1'], $parameters['2']))
		{
			return $this->EE->xmlrpc->send_error_message('802', $this->EE->lang->line('invalid_access'));
		}

		if ( ! $this->userdata['can_access_content'] && $this->userdata['group_id'] != '1')
		{
			return $this->EE->xmlrpc->send_error_message('803', $this->EE->lang->line('invalid_access'));
		}

		/** ---------------------------------------
		/**  Parse Out Channel Information
		/** ---------------------------------------*/

		$this->parse_channel($parameters['0']);
		$limit = ( ! empty($parameters['3']) && is_numeric($parameters['3'])) ? $parameters['3'] : '10';

		/** ---------------------------------------
		/**  Perform Query
		/** ---------------------------------------*/

		$sql = "SELECT DISTINCT(wt.entry_id), wt.title, wt.channel_id,
				wt.author_id, wt.entry_date
				FROM	exp_channel_titles wt, exp_channel_data
				WHERE wt.entry_id = exp_channel_data.entry_id ";

		if ($this->userdata['group_id'] != '1' && ! $this->userdata['can_edit_other_entries'])
		{
			$sql .= "AND wt.author_id = '".$this->userdata['member_id']."' ";
		}

		$sql .= str_replace('exp_channels.channel_id','wt.channel_id', $this->channel_sql)." ";

		$sql .= "ORDER BY entry_date desc LIMIT 0, {$limit}";

		$query = $this->EE->db->query($sql);

		if ($query->num_rows() == 0)
		{
			return $this->EE->xmlrpc->send_error_message('805', $this->EE->lang->line('no_entries_found'));
		}

		/** ---------------------------------------
		/**  Process Output
		/** ---------------------------------------*/

		$response = array();

		foreach($query->result_array() as $row)
		{
			// Entry Data to XML-RPC form

			$entry_data = array(array(
										'userid' =>
										array($row['author_id'],'string'),
										'dateCreated' =>
										array(date('Ymd\TH:i:s',$row['entry_date']).'Z','dateTime.iso8601'),
										'title' =>
										array($row['title'], 'string'),
										'postid' =>
										array($row['entry_id'],'string'),
										),
									'struct');

			array_push($response, $entry_data);
		}

		return $this->EE->xmlrpc->send_response(array($response, 'array'));
	}

	// --------------------------------------------------------------------

	/**
	 * MT API: get post categories
	 *
	 * @access	public
	 * @param	parameter list
	 * @return	void
	 */
	function get_postCategories($plist)
	{
		$parameters = $plist->output_parameters();

		if ( ! $this->fetch_member_data($parameters['1'], $parameters['2']))
		{
			return $this->EE->xmlrpc->send_error_message('802', $this->EE->lang->line('invalid_access'));
		}

		$query = $this->EE->db->query("SELECT channel_id FROM exp_channel_titles
							 WHERE entry_id = '".$this->EE->db->escape_str($parameters['0'])."'");

		if ($query->num_rows() == 0)
		{
			return $this->EE->xmlrpc->send_error_message('804', $this->EE->lang->line('invalid_channel'));
		}

		if ($this->userdata['group_id'] != '1' && ! in_array($query->row('channel_id') , $this->userdata['assigned_channels']))
		{
			return $this->EE->xmlrpc->send_error_message('803', $this->EE->lang->line('invalid_access'));
		}

		$cats = array();

		$sql = "SELECT	exp_categories.cat_id, exp_categories.cat_name
				FROM	exp_category_posts, exp_categories
				WHERE	exp_category_posts.cat_id = exp_categories.cat_id
				AND		exp_category_posts.entry_id = '".$this->EE->db->escape_str($parameters['0'])."'
				ORDER BY cat_id";

		$query = $this->EE->db->query($sql);

		if ($query->num_rows() > 0)
		{
			foreach($query->result_array() as $row)
			{
				$cat = array();

				$cat['categoryId'] = array($row['cat_id'],'string');
		  		$cat['categoryName'] = array($row['cat_name'],'string');
		  
		  		array_push($cats, array($cat, 'struct'));
			}
		}

		return $this->EE->xmlrpc->send_response(array($cats, 'array'));
	}

	// --------------------------------------------------------------------

	/**
	 * MT API: set post categories
	 *
	 * @access	public
	 * @param	parameter list
	 * @return	void
	 */
	function setPostCategories($plist)
	{
		$parameters = $plist->output_parameters();

		if ( ! $this->fetch_member_data($parameters['1'], $parameters['2']))
		{
			return $this->EE->xmlrpc->send_error_message('802', $this->EE->lang->line('invalid_access'));
		}

		if ( ! $this->userdata['can_access_content'] && $this->userdata['group_id'] != '1')
		{
			return $this->EE->xmlrpc->send_error_message('803', $this->EE->lang->line('invalid_access'));
		}

		if ( ! $this->userdata['can_edit_other_entries'] && $this->userdata['group_id'] != '1')
		{
			// If there aren't any channels assigned to the user, bail out

			if (count($this->userdata['assigned_channels']) == 0)
			{
				return $this->EE->xmlrpc->send_error_message('804', $this->EE->lang->line('invalid_access'));
			}
		}

		/** ---------------------------------------
		/**  Details from Parameters
		/** ---------------------------------------*/

		$entry_id = $parameters['0'];

		/** ---------------------------------------
		/**  Retrieve Entry Information
		/** ---------------------------------------*/

		$sql = "SELECT channel_id, author_id
				FROM exp_channel_titles
				WHERE entry_id = '".$entry_id."' ";

		$query = $this->EE->db->query($sql);

		if ($query->num_rows() == 0)
		{
			return $this->EE->xmlrpc->send_error_message('805', $this->EE->lang->line('no_entry_found'));
		}

		if ( ! $this->userdata['can_edit_other_entries'] && $this->userdata['group_id'] != '1')
		{
			if ($query->row('author_id')  != $this->userdata['member_id'])
			{
				return $this->EE->xmlrpc->send_error_message('806', $this->EE->lang->line('entry_uneditable'));
			}
		}

		$this->channel_id	= $query->row('channel_id') ;

		$this->parse_channel($this->channel_id);

		/** ---------------------------------------
		/**  Parse Categories
		/** ---------------------------------------*/

		if ( ! empty($parameters['3']) && count($parameters['3']) > 0)
		{
			$cats = array();

			foreach($parameters['3'] as $cat_data)
			{
				$cats[] = $cat_data['categoryId'];
			}

			if (count($cats) == 0 && ! empty($this->deft_category))
			{
				$cats = array($this->deft_category);
			}

			if (count($cats) > 0)
			{
				$this->check_categories($cats);
			}
		}
		else
		{
			return $this->EE->xmlrpc->send_response(array(1,'boolean'));
			//return $this->EE->xmlrpc->send_error_message('802', $this->EE->lang->line('entry_uneditable'));
		}

		/** ---------------------------------
		/**  Insert Categories, if any
		/** ---------------------------------*/

		$this->EE->db->query("DELETE FROM exp_category_posts WHERE entry_id = '$entry_id'");

		if (count($this->categories) > 0)
		{
			foreach($this->categories as $cat_id => $cat_name)
			{
				$this->EE->db->query("INSERT INTO exp_category_posts
							(entry_id, cat_id)
							VALUES
							('".$entry_id."', '$cat_id')");
			}
		}

		/** ---------------------------------
		/**  Clear caches if needed
		/** ---------------------------------*/

		if ($this->EE->config->item('new_posts_clear_caches') == 'y')
		{
			$this->EE->functions->clear_caching('all');
		}
		else
		{
			$this->EE->functions->clear_caching('sql');
		}

		/** ---------------------------------
		/**  Return Boolean TRUE
		/** ---------------------------------*/

		return $this->EE->xmlrpc->send_response(array(1,'boolean'));
	}

	// --------------------------------------------------------------------

	/**
	 * Fetch member data
	 *
	 * @access	public
	 * @param	parameter list
	 * @return	void
	 */
	function fetch_member_data($username, $password)
	{
		$this->EE->load->library('auth');
		
		if (FALSE == ($auth = $this->EE->auth->authenticate_username($username, $password)))
		{
			return FALSE;
		}

		// load userdata from Auth object, a few fields from the members table, but most from the group
		
		foreach (array('screen_name', 'member_id', 'email', 'url', 'group_id') as $member_item)
		{
			$this->userdata[$member_item] = $auth->member($member_item);
		}

		foreach ($this->EE->db->list_fields('member_groups') as $field)
		{
			$this->userdata[$field] = $auth->group($field);
		}

		/** -------------------------------------------------
		/**  Find Assigned Channels
		/** -------------------------------------------------*/

		$assigned_channels = array();

		if ($this->userdata['group_id'] == 1)
		{
			$result = $this->EE->db->query("SELECT channel_id FROM exp_channels");
		}
		else
		{
			$result = $this->EE->db->query("SELECT channel_id FROM exp_channel_member_groups WHERE group_id = '".$this->userdata['group_id']."'");
		}

		if ($result->num_rows() > 0)
		{
			foreach ($result->result_array() as $row)
			{
				$assigned_channels[] = $row['channel_id'];
			}
		}
		else
		{

			return FALSE; // Nowhere to Post!!
		}

		$this->userdata['assigned_channels'] = $assigned_channels;


		$this->EE->session->userdata = array_merge(
			$this->EE->session->userdata,
			$this->userdata
		);

		return TRUE;
	}

	// --------------------------------------------------------------------

	/**
	 * METAWEBLOG API: get categories
	 *
	 * @access	public
	 * @param	parameter list
	 * @return	void
	 */
	function getCategories($plist)
	{
		$parameters = $plist->output_parameters();

		if ( ! $this->fetch_member_data($parameters['1'], $parameters['2']))
		{
			return $this->EE->xmlrpc->send_error_message('802', $this->EE->lang->line('invalid_access'));
		}

		if ($this->userdata['group_id'] != '1' && ! in_array($parameters['0'], $this->userdata['assigned_channels']))
		{
			return $this->EE->xmlrpc->send_error_message('803', $this->EE->lang->line('invalid_channel'));
		}

		$this->parse_channel($parameters['0']);

		$cats = array();

		$sql = "SELECT exp_categories.cat_id, exp_categories.cat_name, exp_categories.cat_description
				FROM	exp_categories, exp_channels
				WHERE  FIND_IN_SET(exp_categories.group_id, REPLACE(exp_channels.cat_group, '|', ','))
				AND exp_channels.channel_id = '{$this->channel_id}'";

		$query = $this->EE->db->query($sql);

		if ($query->num_rows() > 0)
		{
			foreach($query->result_array() as $row)
			{
				$cat = array();

				$link = $this->EE->functions->remove_double_slashes($this->channel_url.'/C'.$row['cat_id'].'/');

				$cat['categoryId']		= array($row['cat_id'],'string');
		 		$cat['description']		= array(($row['cat_description'] == '') ? $row['cat_name'] : $row['cat_description'],'string');
		  		$cat['categoryName']	= array($row['cat_name'],'string');
		  		$cat['htmlUrl']			= array($link,'string');
		  		$cat['rssUrl']			= array($link,'string'); // No RSS URL for Categories
		  
		  		array_push($cats, array($cat, 'struct'));
			}
		}

		return $this->EE->xmlrpc->send_response(array($cats, 'array'));
	}

	// --------------------------------------------------------------------

	/**
	 * MT API: get category list
	 *
	 * @access	public
	 * @param	parameter list
	 * @return	void
	 */
	function getCategoryList($plist)
	{
		$parameters = $plist->output_parameters();

		if ( ! $this->fetch_member_data($parameters['1'], $parameters['2']))
		{
			return $this->EE->xmlrpc->send_error_message('802', $this->EE->lang->line('invalid_access'));
		}

		if ($this->userdata['group_id'] != '1' && ! in_array($parameters['0'], $this->userdata['assigned_channels']))
		{
			return $this->EE->xmlrpc->send_error_message('803', $this->EE->lang->line('invalid_channel'));
		}

		$this->parse_channel($parameters['0']);

		$cats = array();

		$sql = "SELECT exp_categories.cat_id, exp_categories.cat_name
				FROM	exp_categories, exp_channels
				WHERE  FIND_IN_SET(exp_categories.group_id, REPLACE(exp_channels.cat_group, '|', ','))
				AND exp_channels.channel_id = '{$this->channel_id}'";

		$query = $this->EE->db->query($sql);

		if ($query->num_rows() > 0)
		{
			foreach($query->result_array() as $row)
			{
				$cat = array();

				$cat['categoryId']   = array($row['cat_id'],'string');
		  		$cat['categoryName'] = array($row['cat_name'],'string');
		  
		  		array_push($cats, array($cat, 'struct'));
			}
		}

		return $this->EE->xmlrpc->send_response(array($cats, 'array'));
	}

	// --------------------------------------------------------------------

	/**
	 * Parses out received channel parameters
	 *
	 * @access	public
	 * @param	int
	 * @return	void
	 */
	function parse_channel($channel_id)
	{
		$channel_id			= trim($channel_id);
		$this->status		= 'open';

		$sql				= "SELECT channel_id, channel_url, comment_url, deft_category, channel_html_formatting, site_id FROM exp_channels WHERE ";
		$this->channel_sql	= $this->EE->functions->sql_andor_string($channel_id, 'exp_channels.channel_id');
			$sql				= (substr($this->channel_sql, 0, 3) == 'AND') ? $sql.substr($this->channel_sql, 3) : $sql.$this->channel_sql;
		$query				= $this->EE->db->query($sql);

		if ($query->num_rows() == 0)
		{
			return $this->EE->xmlrpc->send_error_message('804', $this->EE->lang->line('invalid_channel'));
		}

		$this->channel_id		= $query->row('channel_id');
		$this->channel_url		= $query->row('channel_url');
		$this->comment_url		= $query->row('comment_url');
		$this->deft_category	= $query->row('deft_category');
		$this->html_format		= $query->row('channel_html_formatting');
		$this->site_id			= $query->row('site_id');

		if ($this->site_id != $this->EE->config->item('site_id'))
		{
			$this->EE->config->site_prefs('', $this->site_id);

			$this->assign_parents = ($this->EE->config->item('auto_assign_cat_parents') == 'n') ? FALSE : TRUE;
		}

		foreach ($query->result_array() as $row)
		{
			if ( ! in_array($row['channel_id'], $this->userdata['assigned_channels']) && $this->userdata['group_id'] != '1')
			{
				return $this->EE->xmlrpc->send_error_message('803', $this->EE->lang->line('invalid_channel'));
			}
		}

		/** ---------------------------------------
		/**  Find Fields
		/** ---------------------------------------*/

		$query = $this->EE->db->query("SELECT field_name, field_id, field_type, field_fmt FROM exp_channel_fields, exp_channels
							  WHERE exp_channels.field_group = exp_channel_fields.group_id
							  {$this->channel_sql}
							  ORDER BY field_order");

		foreach($query->result_array() as $row)
		{
			$this->fields[$row['field_id']] = array($row['field_name'], $row['field_fmt']);
		}
	}

	// --------------------------------------------------------------------

	/**
	 * Check validity of categories
	 *
	 * @access	public
	 * @param	array
	 * @return	void
	 */
	function check_categories($array, $debug = '0')
	{
		$this->categories = array_unique($array);

		$sql = "SELECT exp_categories.cat_id, exp_categories.cat_name, exp_categories.parent_id
				FROM	exp_categories, exp_channels
				WHERE  FIND_IN_SET(exp_categories.group_id, REPLACE(exp_channels.cat_group, '|', ','))
				AND exp_channels.channel_id = '{$this->channel_id}'";

		$query = $this->EE->db->query($sql);

		if ($query->num_rows() == 0)
		{
			return $this->EE->xmlrpc->send_error_message('807', $this->EE->lang->line('invalid_categories'));
		}

		$good		= 0;
		$all_cats	= array();

		foreach($query->result_array() as $row)
		{
			$all_cats[$row['cat_id']] = $row['cat_name'];

			if (in_array($row['cat_id'], $this->categories) OR in_array($row['cat_name'], $this->categories))
			{
				$good++;
				$cat_names[$row['cat_id']] = $row['cat_name'];

				if ($this->assign_parents == TRUE && $row['parent_id'] != '0')
				{
					$this->cat_parents[$row['parent_id']] = 'Parent';
				}
			}
		}

		if ($good < count($this->categories))
		{
			return $this->EE->xmlrpc->send_error_message('807', $this->EE->lang->line('invalid_categories'));
		}
		else
		{
			$this->categories = $cat_names;

			if ($this->assign_parents == TRUE && count($this->cat_parents) > 0)
			{
				foreach($this->cat_parents as $kitty => $galore)
				{
					$this->categories[$kitty] = $all_cats[$kitty];
				}
			}
		}
	}

	// --------------------------------------------------------------------

	/**
	 * Delete Post
	 *
	 * @access	public
	 * @param	parameter list
	 * @return	void
	 */
	function deletePost($plist)
	{
		$parameters = $plist->output_parameters();

		if ( ! $this->fetch_member_data($parameters['2'], $parameters['3']))
		{
			return $this->EE->xmlrpc->send_error_message('802', $this->EE->lang->line('invalid_access'));
		}

		if (	$this->userdata['group_id'] != '1' AND
			 ! $this->userdata['can_delete_self_entries'] AND
			 ! $this->userdata['can_delete_all_entries'])
		{
			return $this->EE->xmlrpc->send_error_message('808', $this->EE->lang->line('invalid_access'));
		}
		
		$this->EE->session->userdata = array_merge(
			$this->EE->session->userdata,
			array(
				'group_id'			=> $this->userdata['group_id'],
				'member_id'			=> $this->userdata['member_id'],
				'assigned_channels'	=> $this->userdata['assigned_channels']
			)
		);

		// Delete the entry
		$this->EE->load->library('api');
		$this->EE->api->instantiate('channel_entries');

		$r = $this->EE->api_channel_entries->delete_entry($parameters['1']);

		if ( ! $r)
		{
			$errors = implode(', ', $this->EE->api_channel_entries->get_errors());

			return $this->EE->xmlrpc->send_error_message('809', $errors);
		}
		else
		{
			return $this->EE->xmlrpc->send_response(array(1,'boolean'));
		}
	}

	// --------------------------------------------------------------------

	/**
	 * METAWEBLOG API: new media object
	 *
	 * XSS Cleaning is bypassed when uploading a file through MetaWeblog API
	 *
	 * @access	public
	 * @param	parameter list
	 * @return	void
	 */
	function newMediaObject($plist)
	{
		$parameters = $plist->output_parameters();

		if ($this->upload_dir == '')
		{
			return $this->EE->xmlrpc->send_error_message('801', $this->EE->lang->line('invalid_access'));
		}

		if ( ! $this->fetch_member_data($parameters['1'], $parameters['2']))
		{
			return $this->EE->xmlrpc->send_error_message('802', $this->EE->lang->line('invalid_access'));
		}

		if ($this->userdata['group_id'] != '1' && ! in_array($parameters['0'], $this->userdata['assigned_channels']))
		{
			return $this->EE->xmlrpc->send_error_message('803', $this->EE->lang->line('invalid_channel'));
		}

		if ($this->userdata['group_id'] != '1')
		{
			$this->EE->db->where('upload_id', $this->upload_dir);
			$this->EE->db->where('member_group', $this->userdata['group_id']);

			if ($this->EE->db->count_all_results('upload_no_access') != 0)
			{
				return $this->EE->xmlrpc->send_error_message('803', $this->EE->lang->line('invalid_access'));
			}
		}

		$this->EE->db->select('server_path, url');
		$query = $this->EE->db->get_where('upload_prefs', array('id' => $this->upload_dir));

		if ($query->num_rows() == 0)
		{
			return $this->EE->xmlrpc->send_error_message('803', $this->EE->lang->line('invalid_access'));
		}

		/** -------------------------------------
		/**  upload the image
		/** -------------------------------------*/
		
		$this->EE->load->library('filemanager');
		
		// Disable XSS Filtering
		$this->EE->filemanager->xss_clean_off();
		
		// Figure out the FULL file path
		$file_path = $this->EE->filemanager->clean_filename(
			$parameters['3']['name'], 
			$this->upload_dir,
			array('ignore_dupes' => FALSE)
		);

		$filename = basename($file_path);

		// Check to see if we're dealing with relative paths
		if (strncmp($file_path, '..', 2) == 0)
		{
			$directory = dirname($file_path);
			$file_path = realpath(substr($directory, 1)).'/'.$filename;
		}
		
		// Upload the file and check for errors
		if (file_put_contents($file_path, $parameters['3']['bits']) === FALSE)
		{
			return $this->EE->xmlrpc->send_error_message(
				'810', 
				$this->EE->lang->line('unable_to_upload')
			);
		}
		
		// Send the file
		$result = $this->EE->filemanager->save_file(
			$file_path, 
			$this->upload_dir, 
			array(
				'title'     => $filename,
				'path'      => dirname($file_path),
				'file_name' => $filename
			)
		);

		// Check to see the result
		if ($result['status'] === FALSE)
		{
			$this->EE->xmlrpc->send_error_message(
				'810', 
				$result['message']
			);
		}

		// Build XMLRPC response
		$response = array(
			array(
				'url' => array(
					$query->row('url').$filename,
					'string'
				),
			),
			'struct'
		);

		return $this->EE->xmlrpc->send_response($response);
	}

	// --------------------------------------------------------------------

	/**
	 * BLOGGER API: send user information
	 *
	 * @access	public
	 * @param	parameter list
	 * @return	void
	 */
	function getUserInfo($plist)
	{
		$parameters = $plist->output_parameters();

		if ( ! $this->fetch_member_data($parameters['1'], $parameters['2']))
		{
			return $this->EE->xmlrpc->send_error_message('802', $this->EE->lang->line('invalid_access'));
		}

		$response = array(array(
								'nickname' =>
								array($this->userdata['screen_name'],'string'),
								'userid' =>
								array($this->userdata['member_id'],'string'),
								'url' =>
								array($this->userdata['url'],'string'),
								'email' =>
								array($this->userdata['email'],'string'),
								'lastname' =>
								array('','string'),
								'firstname' =>
								array('','string')
							  ),
						'struct');

		return $this->EE->xmlrpc->send_response($response);
	}

	// --------------------------------------------------------------------

	/**
	 * METAWEBLOG API: get user's blogs
	 *
	 * @access	public
	 * @param	parameter list
	 * @return	void
	 */
	function getUsersBlogs($plist)
	{
		$parameters = $plist->output_parameters();

		if ( ! $this->fetch_member_data($parameters['1'], $parameters['2']))
		{
			return $this->EE->xmlrpc->send_error_message('802', $this->EE->lang->line('invalid_access'));
		}

		$this->EE->db->select('channel_id, channel_title, channel_url');
		$this->EE->db->where_in('channel_id', $this->userdata['assigned_channels']);
		
		$query = $this->EE->db->get('channels');

		if ($query->num_rows() == 0)
		{
			return $this->EE->xmlrpc->send_error_message('804', $this->EE->lang->line('no_channels_found'));
		}

		$response = array();

		foreach($query->result_array() as $row)
		{
			$channel = array(array(
									"url" =>
									array($row['channel_url'],"string"),
									"blogid" =>
									array($row['channel_id'], "string"),
									"blogName" =>
									array($row['channel_title'], "string")),'struct');

			array_push($response, $channel);
		}

		return $this->EE->xmlrpc->send_response(array($response, 'array'));
	}

	// --------------------------------------------------------------------

	/**
	 * ISO-8601 time to server or UTC time
	 *
	 * @access	public
	 * @param	time
	 * @return	void
	 */
	function iso8601_decode($time, $utc=TRUE)
	{
		// return a time in the localtime, or UTC
		$t = 0;

		if (preg_match('/([0-9]{4})([0-9]{2})([0-9]{2})T([0-9]{2}):([0-9]{2}):([0-9]{2})/', $time, $regs))
		{
			/*
			if ($utc === TRUE)
			{
				$t = gmmktime($regs[4], $regs[5], $regs[6], $regs[2], $regs[3], $regs[1]);

				$time_difference = ($this->EE->config->item('server_offset') == '') ? 0 : $this->EE->config->item('server_offset');

				$server_time = time()+date('Z');
				$offset_time = $server_time + $time_difference*60;
				$gmt_time = time();

				$diff_gmt_server = ($gmt_time - $server_time) / 3600;
				$diff_blogger_server = ($offset_time - $server_time) / 3600;
				$diff_gmt_blogger = $diff_gmt_server - $diff_blogger_server;
				$gmt_offset = -$diff_gmt_blogger;

				$t -= $gmt_offset;
			}
			*/

			$t = mktime($regs[4], $regs[5], $regs[6], $regs[2], $regs[3], $regs[1]);
		}
		return $t;
	}

	// --------------------------------------------------------------------

	/**
	 * MT API:  supportedTextFilters
	 *
	 * @access	public
	 * @param	parameter list
	 * @return	void
	 */
	function supportedTextFilters($plist)
	{
		$plugin_list = $this->fetch_plugins();

		$plugins = array();

		foreach ($plugin_list as $val)
		{
			$name = ucwords(str_replace('_', ' ', $val));

			if ($name == 'Br')
			{
				$name = $this->EE->lang->line('auto_br');
			}
			elseif ($name == 'Xhtml')
			{
				$name = $this->EE->lang->line('xhtml');
			}

			$plugin = array(array(  'key' => array($val,'string'),
									'label' => array($name,'string')
									),
							 'struct');

			array_push($plugins, $plugin);
		}

		return $this->EE->xmlrpc->send_response(array($plugins, 'array'));
	}

	// --------------------------------------------------------------------

	/**
	 * Fetch installed plugins
	 *
	 * @access	public
	 * @return	void
	 */
	function fetch_plugins()
	{
		// Always available
		$plugins = array('br', 'xhtml');
		
		// Additional first or third-party plugins
		$this->EE->load->library('addons');

		foreach ($this->EE->addons->get_files('plugins') as $plugin)
		{
			$plugins[] = strtolower($plugin['class']);
		}
		
		sort($plugins);
		
		// Add None as the first option
		$plugins = array_merge(array('none'), $plugins);
		
		return $plugins;
	}


	// --------------------------------------------------------------------

	/**
	 * Get Settings for the channel
	 *
	 *
	 *
	 */
	function get_settings($channel_id, $which = 'new')
	{
		$this->EE->load->model('channel_model');
		$this->EE->load->library('api');
		$this->EE->api->instantiate('channel_fields');

		$this->EE->db->select('field_group');
		$this->EE->db->where('channel_id', $channel_id);
		$field_group = $this->EE->db->get('channels');

		$field_query = $this->EE->channel_model->get_channel_fields($field_group->row('field_group'));

		foreach ($field_query->result_array() as $row)
		{
			$field_data = '';
			$field_fmt = '';
			
			if ($which == 'edit')
			{
				$field_data = ( ! isset( $resrow['field_id_'.$row['field_id']])) ? '' : $resrow['field_id_'.$row['field_id']];
				$field_fmt	= ( ! isset( $resrow['field_ft_'.$row['field_id']] )) ? $row['field_fmt'] : $resrow['field_ft_'.$row['field_id']];

			}
			else // New entry- use the default setting
			{
				$field_fmt	= $row['field_fmt'];
			}

			// Settings that need to be prepped			
			$settings = array(
				'field_instructions'	=> trim($row['field_instructions']),
				'field_text_direction'	=> ($row['field_text_direction'] == 'rtl') ? 'rtl' : 'ltr',
				'field_fmt'				=> $field_fmt,
				'field_data'			=> $field_data,
				'field_name'			=> 'field_id_'.$row['field_id'],
			);

			$ft_settings = array();
			
			if (isset($row['field_settings']) && strlen($row['field_settings']))
			{
				$ft_settings = unserialize(base64_decode($row['field_settings']));
			}

			$settings = array_merge($row, $settings, $ft_settings);

			$this->EE->api_channel_fields->set_settings($row['field_id'], $settings);
		}
	}
}


/* End of file mod.metaweblog_api.php */
/* Location: ./system/expressionengine/modules/metaweblog_api/mod.metaweblog_api.php */
