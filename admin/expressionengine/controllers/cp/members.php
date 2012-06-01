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
 * ExpressionEngine Member Management Class
 *
 * @package		ExpressionEngine
 * @subpackage	Control Panel
 * @category	Control Panel
 * @author		EllisLab Dev Team
 * @link		http://expressionengine.com
 */

class Members extends CI_Controller {

	// Default member groups.  We used these for translation purposes
	private $english		= array('Guests', 'Banned', 'Members', 'Pending', 'Super Admins');
	private $no_delete		= array('1', '2', '3', '4'); // Member groups that can not be deleted
	private $perpage		= 50;  // Number of results on the "View all member" page	
	
	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		
		$this->perpage = $this->config->item('memberlist_row_limit');
		
		if ( ! $this->cp->allowed_group('can_access_members'))
		{
			show_error(lang('unauthorized_access'));
		}

		$this->lang->loadfile('members');
		$this->load->model('member_model');
	}
	
	// --------------------------------------------------------------------

	/**
	 * Index function
	 *
	 * @return	mixed
	 */	
	public function index()
	{
		if ( ! $this->cp->allowed_group('can_access_members'))
		{
			show_error(lang('unauthorized_access'));
		}

		$this->cp->set_variable('cp_page_title', lang('members'));

		$this->javascript->compile();

		$this->load->vars(array('controller' => 'members'));

		$this->load->view('_shared/overview');
	}
	
	// --------------------------------------------------------------------

	/**
	 * View all members
	 *
	 * @return	mixed
	 */
	public function view_all_members()
	{
		if ( ! $this->cp->allowed_group('can_access_members'))
		{
			show_error(lang('unauthorized_access'));
		}
		
		$this->load->library('table');
		
		$columns = array(
			'member_id'		=> array('header' => array('data' => lang('id'), 'width' => '4%')),
			'username'		=> array(),
			'screen_name'	=> array('html' => FALSE),
			'email'			=> array(),
			'join_date'		=> array('html' => FALSE),
			'last_visit'	=> array('html' => FALSE),
			'group_id'		=> array('header' => lang('member_group')),
			'_check'		=> array(
				'header' => form_checkbox('select_all', 'true', FALSE, 'class="toggle_all"'),
				'sort' => FALSE
			)
		);
		
		$this->table->set_base_url('C=members'.AMP.'M=view_all_members');
		$this->table->set_columns($columns);
				
		// creating a member automatically fills the search box
		if ( ! ($member_name = $this->input->get_post('member_name')) &&
			 ! ($member_name = $this->session->flashdata('username')))
		{
			$member_name = '';
		}
		
		// Get order by and sort preferences for our initial state
		$order_by = ($this->config->item('memberlist_order_by')) ?
			$this->config->item('memberlist_order_by') : 'member_id';
		$sort = ($this->config->item('memberlist_sort_order')) ?
			$this->config->item('memberlist_sort_order') : 'asc';
		
		// Fix for an issue where users may have 'total_posts' saved
		// in their site settings for sorting members; but the actual
		// column should be total_forum_posts, so we need to correct
		// it until member preferences can be saved again with the
		// right value
		if ($order_by == 'total_posts')
		{
			$order_by = 'total_forum_posts';
		}
		
		$initial_state = array(
			'sort'	=> array($order_by => $sort)
		);
		
		$params = array(
			'member_name' => $member_name,
			'perpage'	=> $this->config->item('memberlist_row_limit')
		);
				
		$vars = $this->table->datasource('_member_search', $initial_state, $params);
		
		$this->javascript->output('
			$(".toggle_all").toggle(
				function(){		
					$("input.toggle").each(function() {
						this.checked = true;
					});
				}, function (){
					$("input.toggle").each(function() {
						this.checked = false;
					});
				}
			);
			
			// Keyword filter
			var indicator = $(".searchIndicator");

			$(".mainTable")
			.table("add_filter", $("#member_form"))
			.bind("tableload", function() {
				indicator.css("visibility", "");
			})
			.bind("tableupdate", function() {
				indicator.css("visibility", "hidden");
			});
		');

		// These variables are only set when one of the pull-down menus is used
		// We use it to construct the SQL query with
		
		$group_id = ($this->input->get_post('group_id')) ? $this->input->get_post('group_id') : '';
		$order	  = $this->input->get_post('order');		

		$vars['column_filter_options'] = array(
			'all'			=> lang('all'),
			'member_id'		=> lang('id'),
			'screen_name'	=> lang('screen_name'),
			'username'		=> lang('username'),
			'email'			=> lang('email')
		);

		$vars['column_filter_selected'] = ($this->input->get_post('column_filter')) ? $this->input->get_post('column_filter') : 'all';

		// remember previously selected values
		$vars['selected_group'] = $group_id;

		// message if we have one
		$vars['message'] = $this->session->flashdata('message');;

		// get all member groups for the dropdown list
		$member_groups = $this->member_model->get_member_groups();
		
		// first dropdown item is "all"
		$vars['member_groups_dropdown'] = array('' => lang('all'));
		
		foreach($member_groups->result() as $group)
		{
			$vars['member_groups_dropdown'][$group->group_id] = $group->group_title;
		}

		$vars['total_members'] = $this->member_model->count_members();
				
		// if we're looking at group 4 (pending), and require email activation, let's also give the option to resend their activation emails
		if ($group_id == '4' && $this->config->item('req_mbr_activation') == 'email' && $this->cp->allowed_group('can_admin_members'))
		{
			$vars['member_action_options'] = array('delete' => lang('delete_selected'), 'resend' => lang('resend_activation_emails'));
			$vars['delete_button_label'] = lang('submit');
		}
		else
		{
			$vars['member_action_options'] = array();
			$vars['form_hidden']['action'] = 'delete';
			$vars['delete_button_label'] = lang('delete_selected');
		}
		
		$this->javascript->compile();
		$this->cp->set_variable('cp_page_title', lang('view_members'));
		$this->load->view('members/view_members', $vars);
	}

	// ----------------------------------------------------------------

	/**
	 * member search
	 *
	 * @return void
	 */
	public function _member_search($state, $params)
	{
		$col_map = array('member_id', 'username', 'screen_name', 'email', 'join_date', 'last_visit');
		
		$search_value = $params['member_name'];
		$group_id = ($this->input->get_post('group_id')) ? $this->input->get_post('group_id') : '';
		$column_filter = ($this->input->get_post('column_filter')) ? $this->input->get_post('column_filter') : 'all';
		
		// Check for search tokens within the search_value
		$search_value = $this->_check_search_tokens($search_value);
		
		$perpage = $this->input->get_post('perpage');
		$perpage = $perpage ? $perpage : $params['perpage'];

		$members = $this->member_model->get_members($group_id, $perpage, $state['offset'], $search_value, $state['sort'], $column_filter);
		$members = $members ? $members->result_array() : array();
		
		$member_groups = $this->member_model->get_member_groups();
		$groups = array();
		
		foreach($member_groups->result() as $group)
		{
			$groups[$group->group_id] = $group->group_title;
		}
		
		$rows = array();
		
		while ($member = array_shift($members))
		{
			$rows[] = array(
				'member_id'		=> $member['member_id'],
				'username'		=> '<a href="'.BASE.AMP.'C=myaccount'.AMP.'id='.$member['member_id'].'">'.$member['username'].'</a>',
				'screen_name'	=> $member['screen_name'],
				'email'			=> '<a href="mailto:'.$member['email'].'">'.$member['email'].'</a>',
				'join_date'		=> $this->localize->decode_date('%Y-%m-%d', $member['join_date']),
				'last_visit'	=> ($member['last_visit'] == 0) ? ' - ' : $this->localize->set_human_time($member['last_visit']),
				'group_id'		=> $groups[$member['group_id']],		
				'_check'		=> '<input class="toggle" type="checkbox" name="toggle[]" value="'.$member['member_id'].'" />'
			);
		}
		
		return array(
			'rows' => $rows,
			'no_results' => '<p class="notice">'.lang('no_members_matching_that_criteria').'</p>',
			'pagination' => array(
				'per_page' => $perpage,
				'total_rows' => $this->member_model->count_members($group_id, $search_value, $column_filter)
			),
			
			'member_name' => $params['member_name'],
			'member_groups' => $member_groups
		);
	}

	// --------------------------------------------------------------------
	
	/**
	 * Looks through the member search string for search tokens (e.g. id:3
	 * or username:john)
	 * 
	 * @param string $search_string The string to look through for tokens
	 * @return string/array String if there are no tokens within the 
	 * 	string, otherwise it's an associative array with the tokens as 
	 * 	the keys
	 */
	private function _check_search_tokens($search_string = '')
	{
		if (strpos($search_string, ':') !== FALSE)
		{
			$search_array = array();
			$tokens = array('id', 'member_id', 'username', 'screen_name', 'email');
			
			foreach ($tokens as $token)
			{
				// This regular expression looks for a token immediately 
				// followed by one of three things:
				// - a value within double quotes
				// - a value within single quotes
				// - a value without spaces
				
				if (preg_match('/'.$token.'\:((?:"(.*?)")|(?:\'(.*?)\')|(?:[^\s:]+?))(?:\s|$)/i', $search_string, $matches))
				{
					// The last item within matches is what we want
					$search_array[$token] = end($matches);
				}
			}
			
			// If both ID and Member_ID are set, unset ID
			if (isset($search_array['id']) AND isset($search_array['member_id']))
			{
				unset($search_array['id']);
			}
			
			return $search_array;
		}
		
		return $search_string;
	}
	
	// --------------------------------------------------------------------

	/**
	 * Member Confirm
	 *
	 * Used to choose between emailing or deleting
	 *
	 * @access	public
	 * @return	mixed
	 */		
	public function member_confirm()
	{
		if ( ! $this->cp->allowed_group('can_access_members'))
		{
			show_error(lang('unauthorized_access'));
		}
		
		if ($this->input->post('action') == 'resend')
		{
			$this->resend_activation_emails();
		}
		else
		{
			$this->member_delete_confirm();
		}
	}

	// --------------------------------------------------------------------

	/**
	 * Resend Activation Emails
	 *
	 * Resend Pending Member's Activation Emails
	 *
	 * @return	mixed
	 */		
	public function resend_activation_emails()
	{
		if ( ! $this->cp->allowed_group('can_access_members') OR 
			$this->config->item('req_mbr_activation') !== 'email')
		{
			show_error(lang('unauthorized_access'));
		}
		
		if ($this->input->get('mid') !== FALSE)
		{
			$_POST['toggle'][] = $this->input->get('mid');
		}

		if ( ! $this->input->post('toggle'))
		{
			$this->functions->redirect(BASE.AMP.'C=members'.AMP.'M=view_all_members');
		}

		$damned = array();

		foreach ($_POST['toggle'] as $key => $val)
		{
			$damned[] = $val;
		}
		
		if (count($damned) == 0)
		{
			$this->functions->redirect(BASE.AMP.'C=members'.AMP.'M=view_all_members');
		}

		$this->load->library('email');
		$this->load->helper('text');

		$this->db->select('screen_name, username, email, authcode');
		$this->db->where_in('member_id', $damned);
		$query = $this->db->get('members');
		
		if ($query->num_rows() == 0)
		{
			$this->functions->redirect(BASE.AMP.'C=members'.AMP.'M=view_all_members');
		}
			
		$action_id = $this->functions->fetch_action_id('Member', 'activate_member');
		
		$template = $this->functions->fetch_email_template('mbr_activation_instructions');
		
		$swap = array(
						'site_name'			=> stripslashes($this->config->item('site_name')),
						'site_url'			=> $this->config->item('site_url')
					 );
		
		foreach($query->result_array() as $row)
		{
			$swap['name']			= ($row['screen_name'] != '') ? $row['screen_name'] : $row['username'];
			$swap['activation_url']	= $this->functions->fetch_site_index(0, 0).QUERY_MARKER.'ACT='.$action_id.'&id='.$row['authcode'];
			$swap['username']		= $row['username'];
			$swap['email']			= $row['email'];
												
			// Send email

			$this->email->EE_initialize();
			$this->email->wordwrap = TRUE;
			$this->email->from($this->config->item('webmaster_email'), $this->config->item('webmaster_name'));	
			$this->email->to($row['email']);
			$this->email->subject($this->functions->var_swap($template['title'], $swap));	
			$this->email->message(entities_to_ascii($this->functions->var_swap($template['data'], $swap)));		
			$this->email->send();
		}

		$this->session->set_flashdata('message_success', lang(($this->input->get('mid') !== FALSE) ? 'activation_email_resent' : 'activation_emails_resent'));
		$this->functions->redirect(BASE.AMP.'C=members'.AMP.'M=view_all_members');
	}

	// --------------------------------------------------------------------

	/**
	 * Delete Member (confirm)
	 *
	 * Warning message if you try to delete members
	 *
	 * @return	mixed
	 */		
	public function member_delete_confirm()
	{
		if ( ! $this->cp->allowed_group('can_access_members') OR ! $this->cp->allowed_group('can_delete_members'))
		{
			show_error(lang('unauthorized_access'));
		}

		$from_myaccount = FALSE;

		if ($this->input->get('mid') != '')
		{
			$from_myaccount = TRUE;
			$_POST['toggle'][] = $this->input->get('mid');
		}

		if ( ! isset($_POST['toggle']))
		{
			$this->functions->redirect(BASE.AMP.'C=members'.AMP.'M=view_all_members');
		}

		if ( ! is_array($_POST['toggle']) OR count($_POST['toggle']) == 0)
		{
			$this->functions->redirect(BASE.AMP.'C=members'.AMP.'M=view_all_members');
		}

		$damned = array();

		$vars['ids_delete'] = array();
		
		foreach ($this->input->post('toggle') as $key => $val)
		{
			// Is the user trying to delete himself?
			if ($this->session->userdata('member_id') == $val)
			{
				show_error(lang('can_not_delete_self'));
			}

			$damned[] = $val;
		}

		// Pass the damned on for judgement
		$vars['damned'] = $damned;

		if (count($damned) == 1)
		{
			$vars['user_name'] = $this->member_model->get_username($damned['0']);
		}
		else
		{
			$vars['user_name'] = '';
		}

		// Do the users being deleted have entries assigned to them?
		// If so, fetch the member names for reassigment

		if ($this->member_model->count_member_entries($damned)  > 0)
		{
			$vars['heirs'] = array(
				'' => lang('member_delete_dont_reassign_entries')
			);

			$group_ids = $this->member_model->get_members_group_ids($damned);
			
			// Find Valid Member Replacements
			$this->db->select('member_id, username, screen_name');
			$this->db->from('members');
			$this->db->where_in('group_id', $group_ids);
			$this->db->where_not_in('member_id', $damned);
			$this->db->order_by('screen_name');
			$heirs = $this->db->get();

			foreach($heirs->result() as $heir)
			{
				$name_to_use = ($heir->screen_name != '') ? $heir->screen_name : $heir->username;
				$vars['heirs'][$heir->member_id] = $name_to_use;
			}
		}

		$this->cp->set_variable('cp_page_title', lang('delete_member'));
		
		$this->load->view('members/delete_confirm', $vars);
	}

	// --------------------------------------------------------------------

	/**
	 * Login as Member
	 *
	 * Login as Member - SuperAdmins only!
	 *
	 * @return	mixed
	 */		
	public function login_as_member()
	{
		if ($this->session->userdata('group_id') != 1)
		{
			show_error(lang('unauthorized_access'));
		}

		$this->lang->loadfile('myaccount');

		$id = $this->input->get('mid');

		if ($id == '')
		{
			show_error(lang('unauthorized_access'));
		}

		if ($this->session->userdata['member_id'] == $id)
		{
			show_error(lang('unauthorized_access'));
		}

		$this->cp->set_variable('cp_page_title', lang('login_as_member'));

		// Fetch member data
		$this->db->from('members, member_groups');
		$this->db->select('members.screen_name, member_groups.can_access_cp');
		$this->db->where('member_id', $id);
		$this->db->where('member_groups.site_id', $this->config->item('site_id'));
		$this->db->where('members.group_id = '.$this->db->dbprefix('member_groups.group_id'));
		$query = $this->db->get();

		if ($query->num_rows() == 0)
		{
			show_error(lang('unauthorized_access'));
		}

		$vars['message'] = str_replace('%screen_name%', $query->row('screen_name') , lang('login_as_member_description'));

		$vars['form_hidden']['mid'] = $id;

		$vars['can_access_cp'] = ($query->row('can_access_cp')  == 'y') ? TRUE : FALSE;

		$this->load->view('members/login_as_member', $vars);
	}

	// --------------------------------------------------------------------

	/**
	 * Do Login as Member
	 *
	 * Do Login as Member - SuperAdmins only!
	 *
	 * @return	mixed
	 */
	public function do_login_as_member()
	{
		if ($this->session->userdata['group_id'] != 1)
		{
			show_error(lang('unauthorized_access'));
		}

		$id = $this->input->get_post('mid');

		if (($id == '') OR ($this->session->userdata('member_id') == $id))
		{
			show_error(lang('unauthorized_access'));
		}

		// Fetch member data
		$this->db->from('members, member_groups');
		$this->db->select('members.username, members.password, members.unique_id, members.member_id, members.group_id, member_groups.can_access_cp');
		$this->db->where('member_id', $id);
		$this->db->where('member_groups.site_id', $this->config->item('site_id'));
		$this->db->where('members.group_id = '.$this->db->dbprefix('member_groups.group_id'));
		$query = $this->db->get();

		if ($query->num_rows() == 0)
		{
			show_error(lang('unauthorized_access'));
		}

		$this->lang->loadfile('login');

		//  Do we allow multiple logins on the same account?
		if ($this->config->item('allow_multi_logins') == 'n')
		{
			// Kill old sessions first
			$this->session->gc_probability = 100;
			$this->session->delete_old_sessions();
			$expire = time() - $this->session->session_length;

			// See if there is a current session

			$this->db->select('ip_address, user_agent');
			$this->db->where('member_id', $query->row('member_id'));
			$this->db->where('last_activity >', $expire);
			$result = $this->db->get('sessions');

			// If a session exists, trigger the error message

			if ($result->num_rows() == 1)
			{
				if ($this->session->userdata['ip_address'] != $result->row('ip_address')  OR
					$this->session->userdata['user_agent'] != $result->row('user_agent')  )
				{
					show_error(lang('multi_login_warning'));
				}
			}
		}

		// Log the SuperAdmin login

		$this->logger->log_action(lang('login_as_user').':'.NBS.$query->row('username') );

		// Set cookie expiration to one year if the "remember me" button is clicked

		$expire = 0;
		$type = (isset($_POST['return_destination']) && $_POST['return_destination'] == 'cp') ? $this->config->item('admin_session_type') : $this->config->item('user_session_type');

		if ($type != 's')
		{
			$this->functions->set_cookie($this->session->c_expire , time()+$expire, $expire);
			$this->functions->set_cookie($this->session->c_anon , 1,  $expire);
		}

		// Create a new session
		$session_id = $this->session->create_new_session($query->row('member_id') , TRUE);

		// Delete old password lockouts
		$this->session->delete_password_lockout();

		// Redirect the user to the return page

		$return_path = $this->functions->fetch_site_index();

		if (isset($_POST['return_destination']))
		{
			if ($_POST['return_destination'] == 'cp')
			{
				$s = ($this->config->item('admin_session_type') != 'c') ? $this->session->userdata['session_id'] : 0;
				$return_path = $this->config->item('cp_url', FALSE).'?S='.$s;
			}
			elseif ($_POST['return_destination'] == 'other' && isset($_POST['other_url']) && stristr($_POST['other_url'], 'http'))
			{
				$return_path = $this->security->xss_clean(strip_tags($_POST['other_url']));
			}
		}

		$this->functions->redirect($return_path);
	}

	// --------------------------------------------------------------------

	/**
	 * Member Delete
	 *
	 * Delete Members
	 *
	 * @return	mixed
	 */		
	public function member_delete()
	{
		if ( ! $this->cp->allowed_group('can_access_members') OR ! $this->cp->allowed_group('can_delete_members'))
		{
			show_error(lang('unauthorized_access'));
		}

		if ( ! $this->input->post('delete') OR ! is_array($this->input->post('delete')))
		{
			$this->functions->redirect(BASE.AMP.'C=members'.AMP.'M=view_all_members');
		}

		$this->load->model('member_model');

		//  Fetch member ID numbers and build the query

		$ids = array();
		$member_ids = array();
		
		foreach ($this->input->post('delete') as $key => $val)
		{		
			if ($val != '')
			{
				$ids[] = "member_id = '".$this->db->escape_str($val)."'";
				$member_ids[] = $this->db->escape_str($val);
			}		
		}
		
		$IDS = implode(" OR ", $ids);

		// SAFETY CHECK
		// Let's fetch the Member Group ID of each member being deleted
		// If there is a Super Admin in the bunch we'll run a few more safeties
				
		$super_admins = 0;
		
		$query = $this->db->query("SELECT group_id FROM exp_members WHERE ".$IDS);		
		
		foreach ($query->result_array() as $row)
		{
			if ($query->row('group_id')  == 1)
			{
				$super_admins++;
			}
		}		
		
		if ($super_admins > 0)
		{
			// You must be a Super Admin to delete a Super Admin
		
			if ($this->session->userdata['group_id'] != 1)
			{
				show_error(lang('must_be_superadmin_to_delete_one'));
			}
			
			// You can't delete the only Super Admin
			$query = $this->member_model->count_members(1);
			
			if ($super_admins >= $query)
			{
				show_error(lang('can_not_delete_super_admin'));
			}
		}
		
		// If we got this far we're clear to delete the members
		$this->load->model('member_model');
		$this->member_model->delete_member($member_ids, $this->input->post('heir'));
		
		/** ----------------------------------
		/**  Email notification recipients
		/** ----------------------------------*/
		$this->db->select('DISTINCT(member_id), screen_name, email, mbr_delete_notify_emails');
		$this->db->join('member_groups', 'members.group_id = member_groups.group_id', 'left');
		$this->db->where('mbr_delete_notify_emails !=', '');
		$this->db->where_in('member_id', $member_ids);
		$group_query = $this->db->get('members');
		
		foreach ($group_query->result() as $member) 
		{
			$notify_address = $member->mbr_delete_notify_emails;

			$swap = array(
				'name'		=> $member->screen_name,
				'email'		=> $member->email,
				'site_name'	=> stripslashes($this->config->item('site_name'))
			);

			$this->lang->loadfile('member');
			$email_tit = $this->functions->var_swap(lang('mbr_delete_notify_title'), $swap);
			$email_msg = $this->functions->var_swap(lang('mbr_delete_notify_message'), $swap);

			// No notification for the user themselves, if they're in the list
			if (strpos($notify_address, $member->email) !== FALSE)
			{
				$notify_address = str_replace($member->email, "", $notify_address);
			}

			$this->load->helper('string');
			// Remove multiple commas
			$notify_address = reduce_multiples($notify_address, ',', TRUE);

			if ($notify_address != '')
			{
				// Send email
				$this->load->library('email');

				// Load the text helper
				$this->load->helper('text');

				foreach (explode(',', $notify_address) as $addy)
				{
					$this->email->EE_initialize();
					$this->email->wordwrap = FALSE;
					$this->email->from($this->config->item('webmaster_email'), $this->config->item('webmaster_name'));
					$this->email->to($addy);
					$this->email->reply_to($this->config->item('webmaster_email'));
					$this->email->subject($email_tit);
					$this->email->message(entities_to_ascii($email_msg));
					$this->email->send();
				}
			}
		}
		
		/* -------------------------------------------
		/* 'cp_members_member_delete_end' hook.
		/*  - Additional processing when a member is deleted through the CP
		*/
			$edata = $this->extensions->call('cp_members_member_delete_end', $member_ids);
			if ($this->extensions->end_script === TRUE) return;
		/*
		/* -------------------------------------------*/
		
		// Update
		$this->stats->update_member_stats();
			
		$cp_message = (count($ids) == 1) ? lang('member_deleted') :
										lang('members_deleted');

		$this->session->set_flashdata('message_success', $cp_message);
		$this->functions->redirect(BASE.AMP.'C=members'.AMP.'M=view_all_members');
	}

	// --------------------------------------------------------------------

	/**
	 * Member Group Manager
	 *
	 * Member group overview
	 *
	 * @return	mixed
	 */		
	public function member_group_manager()
	{
		if ( ! $this->cp->allowed_group('can_access_members') OR ! $this->cp->allowed_group('can_admin_mbr_groups'))
		{
			show_error(lang('unauthorized_access'));
		}

		$this->load->library('table');
		$this->load->library('pagination');

		$row_limit = $this->perpage;
		$offset = ($this->input->get('per_page') != '') ? $this->input->get('per_page') : 0;

		$query = $this->member_model->get_member_groups(array('can_access_cp', 'is_locked'), array(), $row_limit, $offset);	

		$groups = array(); // holder for group info
				
		foreach($query->result_array() as $row)
		{
			$group_name = $row['group_title'];
					
			if (in_array($group_name, $this->english))
			{
				$group_name = lang(strtolower(str_replace(" ", "_", $group_name)));
			}
	
			$groups[$row['group_id']]['group_id'] = $row['group_id'];
			$groups[$row['group_id']]['title'] = $group_name;						
			$groups[$row['group_id']]['can_access_cp'] = $row['can_access_cp'];
			$groups[$row['group_id']]['security_lock'] = ($row['is_locked'] == 'y') ? lang('locked') : lang('unlocked');
			$groups[$row['group_id']]['member_count'] = $this->member_model->count_members($row['group_id']);
			$groups[$row['group_id']]['delete'] = ( ! in_array($row['group_id'], $this->no_delete)) ? TRUE : FALSE;
		}

		$vars['clone_group_options'] = array();
		$g_query = $this->member_model->get_member_groups();

		foreach($g_query->result_array() as $row)
		{
			$vars['clone_group_options'][$row['group_id']] = $row['group_title'];
		}

		$config = array(
				'base_url'		=> BASE.AMP.'C=members'.AMP.'M=member_group_manager',
				'total_rows'	=> $g_query->num_rows(),
				'per_page'		=> $row_limit,
				'page_query_string'	=> TRUE,
				'first_link'	=> lang('pag_first_link'),
				'last_link'		=> lang('pag_last_link')
			);

		$this->pagination->initialize($config);

		$vars['paginate'] = $this->pagination->create_links();

		$this->cp->set_variable('cp_page_title', lang('member_groups'));

		$this->jquery->tablesorter('.mainTable', '{headers: {1: {sorter: false}, 5: {sorter: false}}, widgets: ["zebra"]}');
		
		$this->javascript->compile();
		
		$vars['groups'] = $groups;

        $this->cp->set_right_nav(array('create_new_member_group' => BASE.AMP.'C=members'.AMP.'M=edit_member_group'));

		$this->load->view('members/member_group_manager', $vars);
	}

	// --------------------------------------------------------------------

	/**
	 * Edit Member Group
	 *
	 * Edit/Create a member group form
	 */		
	public function edit_member_group()
	{
		$is_clone = FALSE;

		if ($this->session->userdata('group_id') != 1)
		{
			show_error(lang('only_superadmins_can_admin_groups'));
		}

		$this->load->library(array('addons', 'table'));
		$this->load->model(array(
			'channel_model', 'template_model', 'addons_model'
		));

		$this->javascript->output('
			$(".site_prefs").hide();
			$("#site_options_'.$this->config->item('site_id').'").show();
			
			$("#site_list_pulldown").change(function() {
				id = $("#site_list_pulldown").val();
				$(".site_prefs").fadeOut("500", function(){
					$("#site_options_"+id).fadeIn("500");
				});
			});
		');
	
		$this->javascript->compile();
		
		$this->lang->loadfile('admin');

		list($sites, $sites_dropdown) = $this->_get_sites();
		
		$group_id = (int) $this->input->get_post('group_id');
		$clone_id = (int) $this->input->get_post('clone_id');

		// $id is the id we will use as group_id, but it may not be the actual 
		// group_id depending on if this is a clone or if group_id was null
		
		$id = ( ! $group_id) ? 3 : $group_id;

		// If we're cloning, set $id to the member group id that we clone
		if ($clone_id)
		{
			$is_clone = TRUE;
			$id = $clone_id;
		}

		$this->cp->set_variable('cp_page_title', 
								($group_id !== 0) ? lang('edit_member_group') : lang('create_member_group'));
		$this->cp->set_breadcrumb(BASE.AMP.'C=members'.AMP.'M=member_group_manager', lang('member_groups'));
		
		$group_data = $this->_setup_group_data($id);

		$default_id = $this->config->item('site_id');
		
		list($group_title, $group_description) = $this->_setup_title_desc($group_id, $group_data, $is_clone);
		
		$page_title_lang = ($is_clone OR ! $group_id) ? 'member_cfg' : 'member_cfg_existing';
	
		$data = array(
			'action'			=> ( ! $group_id) ? 'submit' : 'update',
			'form_hidden'		=> array(
				'clone_id'			=> ( ! $clone_id) ? '' : $clone_id,
				'group_id'			=> $group_id
			),
			'group_data'		=> $this->_setup_final_group_data($sites, $group_data, $id, $is_clone),
			'group_description'	=> $group_description,
			'group_id'			=> $group_id,
			'page_title'		=> sprintf(lang($page_title_lang), $group_title),
			'group_title'		=> ($is_clone) ? '' : $group_title,
			'sites_dropdown'	=> $sites_dropdown,
			'module_data'		=> $this->_setup_module_data($id),
			'site_id'		=> $this->config->item('site_id'),
		);

		$this->load->view('members/edit_member_group', $data);
	}
	
	// --------------------------------------------------------------------
	
	/**
	 * Setup Final Group Data
	 *
	 * This function sets up the final group data array that's passed to the
	 * view file in order to construct the preferences tables.
	 *
	 * @param 	object 		DB object from the sites query
	 * @param 	array 		Array of data on the member group for each site
	 * @param 	int 		group id
	 *
	 * @return 	array 		
	 */
	private function _setup_final_group_data($sites, $group_data, $group_id, $is_clone = FALSE)
	{
		// Get the channel, module and template names and preferences
		list($channel_names, $channel_perms) = $this->_setup_channel_names($group_id);
		list($template_names, $template_perms) = $this->_setup_template_names($group_id);

		// Build the structural array
		$group_cluster = $this->_member_group_cluster($channel_perms, $template_perms, $group_id, $is_clone);

		$form = array();

		// Building this array for each site
		foreach ($sites->result() as $site)
		{
			foreach ($group_cluster as $group_name => $preferences)
			{
				// var_dump($group_name, $preferences[$site->site_id]);
				// If we're dealing with channel post privileges
				if (
					($group_name == 'cp_channel_post_privs' OR $group_name == "cp_template_access_privs") AND 
					isset($preferences[$site->site_id])
				)
				{
					// We'll conditionally set the language for the preference below
					$group_name_lang = '';
					
					switch ($group_name)
					{
						case 'cp_channel_post_privs':
							$current_permissions = $channel_perms[$site->site_id];
							$current_names = $channel_names;
							$group_name_lang = lang('can_post_in');
							break;
						case 'cp_template_access_privs':
							$current_permissions = $preferences[$site->site_id];
							$current_names = $template_names;
							$group_name_lang = lang('can_access_tg');
							break;
						default:
							continue;
							break;
					}

					foreach ($current_permissions as $current_id => $preference_value) 
					{
						$form[$site->site_id][$group_name][] = array(
							'label' => $group_name_lang . NBS . NBS . $this->_build_group_data_label(
								$current_names[$current_id],
								TRUE
							),
							'controls' => $this->_build_group_data_input(
								$current_id,
								$preference_value,
								$site->site_id
							)
						);
					}
				}
				// If we're building the security lock
				else if ($group_name == 'security_lock')
				{
					$form[$site->site_id][$group_name][] = array(
						'label' => '<strong class="notice">'.lang('enable_lock').'</strong><br />'.lang('lock_description'),
						'controls' => $this->_build_group_data_input(
							'is_locked',
							$group_data[$site->site_id]['is_locked'],
							$site->site_id
						)
					);
				}
				// Otherwise, loop through the keyed preferences
				else if ($group_name != 'cp_template_access_privs' AND $group_name != 'cp_channel_post_privs')
				{
					foreach ($preferences as $preference_name => $preference_value) 
					{
						$form[$site->site_id][$group_name][$preference_name] = array(
							'label' => $this->_build_group_data_label($preference_name),
							'controls' => $this->_build_group_data_input(
								$preference_name,
								$group_data[$site->site_id][$preference_name],
								$site->site_id
							)
						);
					}
				}
			}
		}

		return $form;
	}

	// ----------------------------------------------------------------------

	/**
	 * Build the module block's items
	 *
	 * @param integer $group_id The id of the group being edited
	 * 
	 * @return Array of module items, labels and form controls
	 */
	private function _setup_module_data($group_id)
	{
		// Don't show any Module-related preferences for Banned, Guests, or Pending.
		if ($group_id == 2 OR $group_id == 3 OR $group_id == 4)
		{
			return;
		}

		list($module_names, $module_perms) = $this->_setup_module_names($group_id);

		$module_data = array();

		foreach ($module_perms as $module_id => $module_value)
		{
			$module_data[] = array(
				'label' => lang('can_access_mod') . NBS . NBS . $this->_build_group_data_label(
					$module_names[$module_id],
					TRUE
				),
				'controls' => $this->_build_group_data_input(
					$module_id,
					$module_value,
					FALSE
				)
			);
		}

		return $module_data;
	}
	// ----------------------------------------------------------------------

	/**
	 * Builds the label for group data
	 * 
	 * @param string $lang_key Either the lang key or the language itself
	 * @param boolean $alert_override Pass in true if you want it to have the 
	 * 		notice class regardless
	 * 
	 * @return string The label for the item
	 */
	private function _build_group_data_label($lang_key, $alert_override = FALSE)
	{
		// Assign items to highlight
		$alert = array(
			'can_view_offline_system',
			'can_access_cp',
			'can_admin_channels',
			'can_admin_upload_prefs',
			'can_admin_templates',
			'can_delete_members',
			'can_admin_mbr_groups',
			'can_admin_mbr_templates',
			'can_ban_users',
			'can_admin_members',
			'can_admin_design',
			'can_admin_modules',
			'can_edit_categories',
			'can_delete_categories',
			'can_delete_self',
			'enable_lock'
		);

		$label = lang($lang_key, $lang_key);

		if (in_array($lang_key, $alert) OR $alert_override)
		{
			$label = '<strong class="notice">' . $label . '</strong>';	
		}

		return $label;
	}

	// ----------------------------------------------------------------------

	/**
	 * Builds the input item for the member group data
	 * 
	 * @param string $preference_name The preference's name, no site_id appended
	 * @param string $preference_value The preference's value
	 * @param integer $site_id The ID of the site we're dealing with
	 * 
	 * @return string The fully built input item for the form
	 */
	private function _build_group_data_input($preference_name, $preference_value, $site_id)
	{
		// Items that should be in an input box
		$text_inputs = array( 
			'search_flood_control',
			'prv_msg_send_limit',
			'prv_msg_storage_limit',
			'mbr_delete_notify_emails'
		);		
		
		$input = '';
		$input_name = ($site_id) ? $site_id . '_' . $preference_name : $preference_name;

		if (in_array($preference_name, $text_inputs)) 
		{
			$input = form_input($input_name, $preference_value, 'class="field"');
		}
		else
		{
			// If we're dealing with is_locked, use the correct lang keys
			if ($preference_name == 'is_locked') 
			{
				$yes_lang_key = 'locked';
				$no_lang_key = 'unlocked';
			}
			else
			{
				$yes_lang_key = 'yes';
				$no_lang_key = 'no';
			}

			$yes_id = $input_name . '_y';
			$no_id = $input_name . '_n';

			$input  = lang($yes_lang_key, $yes_id).NBS;
			$input .= form_radio(array(
				'name' => $input_name, 
				'id' => $yes_id, 
				'value' => 'y',
				'checked' => ($preference_value == 'y') ? TRUE : FALSE
			));
			$input .= NBS.NBS.NBS.NBS.NBS;
			$input .= lang($no_lang_key, $no_id).NBS;
			$input .= form_radio(array(
				'name' => $input_name,
				'id' => $no_id,
				'value' => 'n',
				'checked' => ($preference_value == 'n') ? TRUE : FALSE
			));
			$input .= NBS.NBS.NBS.NBS.NBS;
		}

		return $input;
	}

	// --------------------------------------------------------------------

	/**
	 * Assign clusters of member groups
	 *
	 * NOTE: the associative value (y/n) is the default setting used
	 * only when we are showing the "create new group" form
	 */
	private function _member_group_cluster($channel_perms, $template_perms, $group_id, $is_clone = FALSE)
	{
		$G = array(
			'security_lock'		=> array(
				'is_locked' 				=> 'n',
			),
			'site_access'	 	=> array (
				'can_view_online_system'	=> 'n',
				'can_view_offline_system'	=> 'n'
			),
			'mbr_account_privs' => array (
				'can_view_profiles'			=> 'n',
				'can_email_from_profile'	=> 'n',
				'can_edit_html_buttons'		=> 'n',
				'include_in_authorlist'		=> 'n',
				'include_in_memberlist'		=> 'n',
				'include_in_mailinglists'	=> 'y',
				'can_delete_self'			=> 'n',
				'mbr_delete_notify_emails'	=> $this->config->item('webmaster_email')
			),
			'commenting_privs' => array (
				'can_post_comments'			=> 'n',
				'exclude_from_moderation'	=> 'n'
			),

			'search_privs'		=> array (
				'can_search'				=> 'n',
				'search_flood_control'		=> '30'
			),

			'priv_msg_privs'	=> array (
				'can_send_private_messages'			=> 'n',
				'prv_msg_send_limit'				=> '20',
				'prv_msg_storage_limit'				=> '60',
				'can_attach_in_private_messages'	=> 'n',
				'can_send_bulletins'				=> 'n'
			),

			'global_cp_access' => array (
				'can_access_cp'		 		=> 'n',
				'can_access_content'		=> 'n',
				'can_access_publish'		=> 'n',
				'can_access_edit'			=> 'n',												
				'can_access_files'	 		=> 'n',
				'can_access_design'	 		=> 'n',
				'can_access_addons'			=> 'n',
				'can_access_modules'		=> 'n',
				'can_access_extensions'		=> 'n',
				'can_access_accessories'	=> 'n',
				'can_access_plugins'		=> 'n',												
				'can_access_fieldtypes'		=> 'n',
				'can_access_members'		=> 'n',
				'can_access_admin'	  		=> 'n',
				'can_access_sys_prefs'	 	=> 'n',
				'can_access_content_prefs'	=> 'n',
				'can_access_tools'			=> 'n',
				'can_access_comm'	 		=> 'n',
				'can_access_utilities'		=> 'n',
				'can_access_data'			=> 'n',
				'can_access_logs'	 		=> 'n'												
			),

			'cp_admin_privs'	=> array (
				'can_admin_channels'	 	=> 'n',
				'can_admin_upload_prefs' 	=> 'n',
				'can_admin_templates'		=> 'n',
				'can_admin_design' 			=> 'n',												
				'can_admin_members'	 		=> 'n',
				'can_admin_mbr_groups'  	=> 'n',
				'can_admin_mbr_templates'  	=> 'n',
				'can_delete_members'		=> 'n',
				'can_ban_users'		 		=> 'n',
				'can_admin_modules'	 		=> 'n'
			),

			'cp_email_privs' => array (
				'can_send_email'			=> 'n',
				'can_email_member_groups'	=> 'n',
				'can_email_mailinglist'		=> 'n',
				'can_send_cached_email'		=> 'n',
			),

			'cp_channel_privs'	=>  array(
				'can_view_other_entries'	=> 'n',
				'can_delete_self_entries'  	=> 'n',
				'can_edit_other_entries'	=> 'n',
				'can_delete_all_entries'	=> 'n',
				'can_assign_post_authors' 	=> 'n',
				'can_edit_categories'		=> 'n',
				'can_delete_categories'		=> 'n',
			),

			'cp_channel_post_privs'	=>  $channel_perms,

			'cp_comment_privs' => array (
				'can_moderate_comments'		=> 'n',
				'can_view_other_comments'	=> 'n',
				'can_edit_own_comments'	 	=> 'n',
				'can_delete_own_comments'	=> 'n',
				'can_edit_all_comments'	 	=> 'n',
				'can_delete_all_comments'	=> 'n'
			),
										
			'cp_template_access_privs' =>  $template_perms
		);

		// Super Admin Group can not be edited
		// If the form being viewed is the Super Admin one we only allow the name to be changed.
		if ($group_id === 1 AND $is_clone === FALSE)
		{
			$G = array('mbr_account_privs' => array (
				'include_in_authorlist' => 'n', 'include_in_memberlist' => 'n'
			));
		}

		return $G;
	}
	
	// --------------------------------------------------------------------

	/**
	 * Setup template names
	 *
	 * Assembles template names from the database for use in the group_data array
	 *
	 * @param 	int 	member group id used for permissions checking
	 * @return 	array 	array of template namesa and associated permissions
	 */
	private function _setup_template_names($id)
	{
		$template_names = array();
		$template_perms = array();
		$template_ids   = array();
		
		$templates = $this->db->select('group_id, group_name, site_id')
							  ->order_by('group_name')
							  ->get('template_groups');
		
		if ($id === 1)
		{
			foreach ($templates->result() as $row)
			{
				$template_names['template_id_'.$row->group_id] = $row->group_name;
				$template_perms[$row->site_id]['template_id_'.$row->group_id] = 'y';
			}

			$templates->free_result();
			
			return array($template_names, $template_perms);
		}

		$qry = $this->db->select('template_group_id')
						->get_where('template_member_groups', array(
							'group_id' => $id
						));

		foreach ($qry->result() as $row)
		{
			$template_ids[$row->template_group_id] = TRUE;
		}

		$qry->free_result();

		foreach ($templates->result() as $row)
		{
			$template_names['template_id_'.$row->group_id] = $row->group_name;
			$template_perms[$row->site_id]['template_id_'.$row->group_id] = isset($template_ids[$row->group_id]) ? 'y' : 'n';
		}

		$templates->free_result();

		return array($template_names, $template_perms);
	}

	// --------------------------------------------------------------------

	/**
	 * Setup Module Names 
	 *
	 * Sets up module names for use in the edit_member_group data array.
	 *
	 * @param 	int 	member group id
	 * @return 	array 	array of module names and associated permissions.
	 */
	private function _setup_module_names($id)
	{
		// Load Module Language Files.
		$mod_lang_files = $this->addons->get_files('modules');

		foreach ($mod_lang_files as $m => $i)
		{
			$this->lang->loadfile($m);
		}
		
		$module_names = array();
		$module_perms = array();
		$module_ids   = array();

		$modules = $this->db->select('module_id, module_name')
							->where('has_cp_backend', 'y')
							->order_by('module_name')
							->get('modules');

		if ($id === 1)
		{
			// Super admins get it all
			foreach ($modules->result() as $row)
			{
				$name = lang(strtolower($row->module_name . '_module_name'));
				$name = ucwords(str_replace('_', ' ', $name));

				$module_names['module_id_'.$row->module_id] = $name;
				$module_perms['module_id_'.$row->module_id] = 'y';
			}

			$modules->free_result();

			return array($module_names, $module_perms);
		}

		$qry = $this->db->select('module_id')
						->get_where('module_member_groups', array(
							'group_id' => $id
						));

		foreach ($qry->result() as $row)
		{
			$module_ids[$row->module_id] = TRUE;
		}

		$qry->free_result();

		foreach ($modules->result() as $row)
		{
			$name = lang(strtolower($row->module_name . '_module_name'));
			$name = ucwords(str_replace('_', ' ', $name));

			$module_names['module_id_'.$row->module_id] = $name;
			$module_perms['module_id_'.$row->module_id] = isset($module_ids[$row->module_id]) ? 'y' : 'n';	
		}

		$modules->free_result();

		return array($module_names, $module_perms);
	}
	
	// --------------------------------------------------------------------

	/**
	 * Setup channel names
	 *
	 * Gets channel names from the database and processes permissions, 
	 * based on member group id
	 *
	 * @param 	int 	member group id
	 * @return 	array 	array of channel names and associated permissions.
	 */
	private function _setup_channel_names($id)
	{
		$channel_names = array();
		$channel_perms = array();
		$channel_ids   = array();
		
		$channels = $this->db->select('channel_id, site_id, channel_title')
							 ->order_by('channel_title')
							 ->get('channels');

		// Super Admins get everything
		if ($id === 1)
		{
			foreach ($channels->result() as $row)
			{
				$channel_names['channel_id_'.$row->channel_id] = $row->channel_title;
				$channel_perms[$row->site_id]['channel_id_'.$row->channel_id] = 'y';
			}

			return array($channel_names, $channel_perms);
		}

		$qry = $this->db->select('channel_id')
						->get_where('channel_member_groups', array(
							'group_id'	=> $id
						));
			
		// Let's see what the members have access to.
		foreach ($qry->result() as $row)
		{
			$channel_ids[$row->channel_id] = TRUE;
		}

		$qry->free_result();

		foreach ($channels->result() as $row)
		{
			$channel_names['channel_id_'.$row->channel_id] = $row->channel_title;
			$channel_perms[$row->site_id]['channel_id_'.$row->channel_id] = (isset($channel_ids[$row->channel_id])) ? 'y' : 'n';
		}

		$channels->free_result();

		return array($channel_names, $channel_perms);
	}
	
	// --------------------------------------------------------------------

	/**
	 * Setup Group Data
	 *
	 * Sets up the initial array of member group data for use in edit_member_groups
	 *
	 * @param 	int 	member group id
	 * @return 	array 
	 */
	private function _setup_group_data($id)
	{
		$member_group_q = $this->db->get_where('member_groups',
										array('group_id' => $id));
		
		$group_data = array();
		
		foreach ($member_group_q->result_array() as $row)
		{
			$group_data[$row['site_id']] = $row;
		}
		
		$member_group_q->free_result();

		return $group_data;
	}
	
	// --------------------------------------------------------------------

	/**
	 * Get Sites
	 *
	 * Retrieves site_id and site_label for use in the edit_member_groups fn.
	 * Ideally I'd like to see the sites query coming from the session cache 
	 * in the future, but I do suppose this works for the time being.
	 *
	 * @return 	array 	$sites_q => DB Object, $sites_dropdown => array
	 */
	private function _get_sites()
	{
		$site_id = $this->config->item('multiple_sites_enabled') == 'y' ? '' : 1;

		if ($site_id != '')
		{
			$this->db->where('site_id', $site_id);
		}

		$sites_q = $this->db->select('site_id, site_label')
							->order_by('site_label')
							->get('sites');
		
		$sites_dropdown = array();
		
		// Setup Sites dropdown
		foreach ($sites_q->result() as $row)
		{
			$sites_dropdown[$row->site_id] = $row->site_label;
		}

		return array($sites_q, $sites_dropdown);
	}
	
	// --------------------------------------------------------------------

	/**
	 * Setup title description
	 *
	 * @param 	int 	member group id
	 * @param 	array 	group data
	 *
	 * @return 	array
	 */
	private function _setup_title_desc($group_id, $group_data, $is_clone)
	{
		$site_id = $this->config->item('site_id');
		
		$group_title = ( ! $group_id OR $is_clone) ? '' : $group_data[$site_id]['group_title'];
		$group_description = ( ! $group_id OR $is_clone) ? '' : $group_data[$site_id]['group_description'];

		// Can this be translated?
		if (isset($this->english[$group_title]))
		{
			$group_title = lang(strtolower(str_replace(' ', '_', $group_title)));
		}

		return array($group_title, $group_description);
	}

	// --------------------------------------------------------------------
	
	/**
	 * Member Config
	 *
	 * @return	mixed
	 */		
	public function member_config()
	{		
		if ( ! $this->cp->allowed_group('can_access_members') OR ! $this->cp->allowed_group('can_admin_members'))
		{
			show_error(lang('unauthorized_access'));
		}

		$this->lang->loadfile('admin');
		$this->load->library('table');

		$f_data =  array(

			'general_cfg'		=>	array(
					'allow_member_registration'	=> array('r', array('y' => 'yes', 'n' => 'no')),
					'req_mbr_activation'		=> array('s', array('none' => 'no_activation', 'email' => 'email_activation', 'manual' => 'manual_activation')),
					'require_terms_of_service'	=> array('r', array('y' => 'yes', 'n' => 'no')),
					'allow_member_localization'	=> array('r', array('y' => 'yes', 'n' => 'no')),
					'use_membership_captcha'	=> array('r', array('y' => 'yes', 'n' => 'no')),											
					'default_member_group'		=> array('f', 'member_groups'),
					'member_theme'				=> array('f', 'member_theme_menu'),
					'profile_trigger'			=> ''
					),

			'memberlist_cfg'		=>	array(
					'memberlist_order_by'		=> array('s', array('total_forum_posts'		=> 'total_posts',
						'screen_name'		=> 'screen_name',
						'total_comments'	=> 'total_comments',
						'total_entries'		=> 'total_entries',
						'join_date'			=> 'join_date')),
					'memberlist_sort_order'		=> array('s', array('desc' => 'memberlist_desc', 'asc' => 'memberlist_asc')),
					'memberlist_row_limit'		=> array('s', array('10' => '10', '20' => '20', '30' => '30', '40' => '40', '50' => '50', '75' => '75', '100' => '100'))
					),
			
'notification_cfg'		=>	array(
					'new_member_notification'	=> array('r', array('y' => 'yes', 'n' => 'no')),
					'mbr_notification_emails'	=> ''
											),
											
			'pm_cfg'			=>	array(
					'prv_msg_max_chars'			=> '',
					'prv_msg_html_format'		=> array('s', array('safe' => 'html_safe', 'none' => 'html_none', 'all' => 'html_all')),
					'prv_msg_auto_links'		=> array('r', array('y' => 'yes', 'n' => 'no')),
					'prv_msg_upload_path'		=> '',
					'prv_msg_max_attachments'	=> '',
					'prv_msg_attach_maxsize'	=> '',
					'prv_msg_attach_total'		=> ''
										 ),
											
			'avatar_cfg'		=>	array(
					'enable_avatars'		=> array('r', array('y' => 'yes', 'n' => 'no')),
					'allow_avatar_uploads'	=> array('r', array('y' => 'yes', 'n' => 'no')),
					'avatar_url'			=> '',
					'avatar_path'			=> '',
					'avatar_max_width'		=> '',
					'avatar_max_height'		=> '',
					'avatar_max_kb'			=> ''
											),
			'photo_cfg'		=>	array(
					'enable_photos'			=> array('r', array('y' => 'yes', 'n' => 'no')),
					'photo_url'				=> '',
					'photo_path'			=> '',
					'photo_max_width'		=> '',
					'photo_max_height'		=> '',
					'photo_max_kb'			=> ''
											),
			'signature_cfg'		=>	array(
					'allow_signatures'			=> array('r', array('y' => 'yes', 'n' => 'no')),
					'sig_maxlength'				=> '',
					'sig_allow_img_hotlink'		=> array('r', array('y' => 'yes', 'n' => 'no')),
					'sig_allow_img_upload'		=> array('r', array('y' => 'yes', 'n' => 'no')),
					'sig_img_url'				=> '',
					'sig_img_path'				=> '',
					'sig_img_max_width'			=> '',
					'sig_img_max_height'		=> '',
					'sig_img_max_kb'			=> ''
											)
			);

		$subtext = array(	
					'profile_trigger'			=> array('profile_trigger_notes'),
					'mbr_notification_emails'	=> array('separate_emails'),
					'default_member_group' 		=> array('group_assignment_defaults_to_two'),
					'avatar_path'				=> array('must_be_path'),
					'photo_path'				=> array('must_be_path'),
					'sig_img_path'				=> array('must_be_path'),
					'allow_member_localization'	=> array('allow_member_loc_notes')
				);
		
		/** -----------------------------
		/**  Blast through the array
		/** -----------------------------*/
				
		foreach ($f_data as $menu_head => $menu_array)				
		{
			$vars['menu_head'][$menu_head] = array();

			foreach ($menu_array as $key => $val)
			{

				$vars['menu_head'][$menu_head][$key]['preference'] = lang($key, $key);
				$vars['menu_head'][$menu_head][$key]['preference_subtext'] = '';
			
				// Preference sub-heading
				if (isset($subtext[$key]))
				{
					foreach ($subtext[$key] as $sub)
					{
						$vars['menu_head'][$menu_head][$key]['preference_subtext'] = lang($sub);
					}
				}

				$preference_controls = '';
				
				if (is_array($val))
				{
							
					if ($val['0'] == 's')
					{

						/** -----------------------------
						/** Drop-down menus
						/** -----------------------------*/

						$options = array();
						
						foreach ($val['1'] as $k => $v)
						{
							$options[$k] = lang($v);
						}

						$preference_controls['type'] = "dropdown";
						$preference_controls['id'] = $key;
						$preference_controls['options'] = $options;
						$preference_controls['default'] = $this->config->item($key);
					}
					elseif ($val['0'] == 'r')
					{
						/** -----------------------------
						/**  Radio buttons
						/** -----------------------------*/
					
						$radios = array();
					
						foreach ($val['1'] as $k => $v)
						{
							$selected = ($k == $this->config->item($key)) ? TRUE : FALSE;

							$radios[] = array(
											'label'		=> lang($v, "{$key}_{$k}"),
											'radio'		=> array(
																	'name' 		=> $key,
																	'id'		=> "{$key}_{$k}",
																	'value'		=> $k,
																	'checked'	=> ($k == $this->config->item($key)) ? TRUE : FALSE
																)
										  );
						}

						$preference_controls['type'] = "radio";
						$preference_controls['radio'] = $radios;
					}
					elseif ($val['0'] == 'f')
					{
						/** -----------------------------
						/**  Function calls
						/** -----------------------------*/
	
						switch ($val['1'])
						{
							case 'member_groups' :	
								$groups = $this->member_model->get_member_groups();

								$options = array();

								foreach ($groups->result() as $group)
								{
									$options[$group->group_id] = $group->group_title;
								}

								// Remove the Super Admin, Guests and Pending groups as they are not sensible choices
								unset($options[1], $options[3], $options[4]);
		
								$preference_controls['type'] = "dropdown";
								$preference_controls['id'] = 'default_member_group';
								$preference_controls['options'] = $options;
								$preference_controls['default'] = ($this->config->item('default_member_group') != '') ? $this->config->item('default_member_group') : '5';
								
								break;
							case 'member_theme_menu' : 	
								$themes = $this->member_model->get_theme_list(PATH_MBR_THEMES);

								$options = array();

								foreach ($themes as $file=>$name)
								{
									$options[$file] = $name;
								}

								$preference_controls['type'] = "dropdown";
								$preference_controls['id'] = 'member_theme';
								$preference_controls['options'] = $options;
								$preference_controls['default'] = $this->config->item($key);
								
								break;
						}
					}
				}
				else
				{
					/** -----------------------------
					/**  Text input fields
					/** -----------------------------*/
					$item = str_replace("\\'", "'", $this->config->item($key));

					$preference_controls['type'] = "text";
					$preference_controls['data'] = array(
															'id' 	=> $key,
															'name' 	=> $key,
															'value' => $item,
															'class'	=> 'field'
														);
				}
				
				$vars['menu_head'][$menu_head][$key]['preference_controls'] = $preference_controls;
			}
		}

		$this->cp->set_variable('cp_page_title', lang('member_prefs'));

		$this->jquery->tablesorter('table', '{
			headers: {},
			widgets: ["zebra"]
		}');

		$this->javascript->compile();
		
		$this->load->view('members/member_config', $vars);
	}

	// --------------------------------------------------------------------

	/**
	 * Update Config
	 *
	 * Update general preferences
	 *
	 * @return	mixed
	 */		
	public function update_config()
	{
		if ( ! $this->cp->allowed_group('can_access_members') OR ! $this->cp->allowed_group('can_admin_members'))
		{
			show_error(lang('unauthorized_access'));
		}
		
		$config_update = $this->config->update_site_prefs($_POST);
		
		// Member Avatars and Signatures are special little bunnies.  
		// Deal with them now.
		$this->db->update('members', array(
			'display_signatures'	=> $this->input->post('allow_signatures'),
			'display_avatars'		=> $this->input->post('enable_avatars')
		));

 		$loc = BASE.AMP.'C=members'.AMP.'M=member_config';

		if ( ! empty($config_update))
		{
			$this->load->helper('html');
			$this->session->set_flashdata('message_failure', ul($config_update, array('class' => 'bad_path_error_list')));
		}
		else
		{
			$this->session->set_flashdata('message_success', lang('preferences_updated'));			
		}
		
		$this->functions->redirect($loc);
	}

	// --------------------------------------------------------------------

	/**
	 * Update Member Group
	 *
	 * Create/update a member group
	 *
	 * @return	mixed
	 */		
	public function update_member_group()
	{
		//  Only super admins can administrate member groups
		if ($this->session->userdata['group_id'] != 1)
		{
			show_error(lang('only_superadmins_can_admin_groups'));
		}

		$edit = TRUE;
		
		$group_id = $this->input->post('group_id');
		$clone_id = $this->input->post('clone_id');

		unset($_POST['group_id']);
		unset($_POST['clone_id']);

		// Only super admins can edit the "super admin" group
		if ($group_id == 1  AND $this->session->userdata['group_id'] != 1)
		{
			show_error(lang('unauthorized_access'));
		}

		// No group name
		if ( ! $group_title = $this->input->post('group_title'))
		{
			show_error(lang('missing_group_title'));
		}
		
		$return = ($this->input->get_post('return')) ? TRUE : FALSE;
		unset($_POST['return']);
		
		// New Group? Find Max
		
		if (empty($group_id))
		{
			$edit = FALSE;
			
			$query = $this->db->query("SELECT MAX(group_id) as max_group FROM exp_member_groups");
			
			$group_id = $query->row('max_group') + 1;
		}
		
		// Group Title already exists?
		$this->db->from('member_groups')
					->where('group_title', $group_title)
					->where('group_id !=', $group_id);
		
		if ($this->db->count_all_results())
		{
			show_error(lang('group_title_exists'));
		}

		// get existing category privileges if necessary
		
		if ($edit == TRUE)
		{
			$query = $this->db->query("SELECT site_id, can_edit_categories, can_delete_categories FROM exp_member_groups WHERE group_id = '".$this->db->escape_str($group_id)."'");
			
			$old_cat_privs = array();
			
			foreach ($query->result_array() as $row)
			{
				$old_cat_privs[$row['site_id']]['can_edit_categories'] = $row['can_edit_categories'];
				$old_cat_privs[$row['site_id']]['can_delete_categories'] = $row['can_delete_categories'];
			}
		}
		
		if ($this->config->item('multiple_sites_enabled') == 'n')
		{
			$this->db->where('site_id', 1);
		}
		
		$query = $this->db->select('site_id')->get('sites');
		
		$module_ids = array();
		$channel_ids = array();
		$template_ids = array();
		$cat_group_privs = array('can_edit_categories', 'can_delete_categories');
				
		foreach($query->result_array() as $row)
		{
			$site_id = $row['site_id'];
		
			/** ----------------------------------------------------
			/**  Remove and Store Channel and Template Permissions
			/** ----------------------------------------------------*/
			
			$data = array('group_title' 		=> $this->input->post('group_title'),
						  'group_description'	=> $this->input->post('group_description'),
						  'site_id'				=> $site_id,
						  'group_id'			=> $group_id);
			
			// If editing Super Admin group, the is_locked field doesn't exist, so make sure we
			// got a value from the form before writing 0 to the database
			if ($this->input->post('is_locked') !== FALSE)
			{
				$data['is_locked'] = $this->input->post('is_locked');
			}
			
			foreach ($_POST as $key => $val)
			{
				if (substr($key, 0, strlen($site_id.'_channel_id_')) == $site_id.'_channel_id_')
				{
					if ($val == 'y')
					{
						$channel_ids[] = substr($key, strlen($site_id.'_channel_id_'));
					}
				}
				elseif (substr($key, 0, strlen('module_id_')) == 'module_id_')
				{
					if ($val == 'y')
					{
						$module_ids[] = substr($key, strlen('module_id_'));			
					}
				}
				elseif (substr($key, 0, strlen($site_id.'_template_id_')) == $site_id.'_template_id_')
				{
					if ($val == 'y')
					{
						$template_ids[] = substr($key, strlen($site_id.'_template_id_'));						
					}
				}
				elseif (substr($key, 0, strlen($site_id.'_')) == $site_id.'_')
				{
					$data[substr($key, strlen($site_id.'_'))] = $_POST[$key];
				}
				else
				{
					continue;
				}
				
				unset($_POST[$key]);
			}

			if ($edit === FALSE)
			{	
				$this->db->query($this->db->insert_string('exp_member_groups', $data));
				
				$uploads = $this->db->query("SELECT exp_upload_prefs.id FROM exp_upload_prefs WHERE site_id = '".$this->db->escape_str($site_id)."'");
				
				if ($uploads->num_rows() > 0)
				{
					foreach($uploads->result_array() as $upload)
					{
						$this->db->query("INSERT INTO exp_upload_no_access (upload_id, upload_loc, member_group) VALUES ('".$this->db->escape_str($upload['id'])."', 'cp', '{$group_id}')");
					}
				}
				
				if ($group_id != 1)
				{
					foreach ($cat_group_privs as $field)
					{
						$privs = array(
										'member_group' => $group_id,
										'field' => $field,
										'allow' => ($data[$field] == 'y') ? TRUE : FALSE,
										'site_id' => $site_id,
										'clone_id' => $clone_id
									);

						$this->_update_cat_group_privs($privs);	
					}
				}
				
				$cp_message = lang('member_group_created').NBS.NBS.$_POST['group_title'];			
			}
			else
			{			
				unset($data['group_id']);
				
				$this->db->query($this->db->update_string('exp_member_groups', $data, "group_id = '$group_id' AND site_id = '{$site_id}'"));
				
				if ($group_id != 1)
				{
					// update category group discrete privileges

					foreach ($cat_group_privs as $field)
					{
						// only modify category group privs if value changed, so we do not
						// globally overwrite existing defined privileges carelessly

						if ($old_cat_privs[$site_id][$field] != $data[$field])
						{
							$privs = array(
											'member_group' => $group_id,
											'field' => $field,
											'allow' => ($data[$field] == 'y') ? TRUE : FALSE,
											'site_id' => $site_id,
											'clone_id' => $clone_id
										);
	
							$this->_update_cat_group_privs($privs);						
						}
					}
				}
				
				$cp_message = lang('member_group_updated').NBS.NBS.$_POST['group_title'];
			}
		}

		// Update groups
		
		$this->db->query("DELETE FROM exp_channel_member_groups WHERE group_id = '$group_id'");
		$this->db->query("DELETE FROM exp_module_member_groups WHERE group_id = '$group_id'");
		$this->db->query("DELETE FROM exp_template_member_groups WHERE group_id = '$group_id'");

		if (count($channel_ids) > 0)
		{
			foreach ($channel_ids as $val)
			{
				$this->db->query("INSERT INTO exp_channel_member_groups (group_id, channel_id) VALUES ('$group_id', '$val')");
			}
		}
			
		if (count($module_ids) > 0)
		{
			foreach ($module_ids as $val)
			{
				$this->db->query("INSERT INTO exp_module_member_groups (group_id, module_id) VALUES ('$group_id', '$val')");
			}
		}
		
		if (count($template_ids) > 0)
		{
			foreach ($template_ids as $val)
			{
				$this->db->query("INSERT INTO exp_template_member_groups (group_id, template_group_id) VALUES ('$group_id', '$val')");
			}
	 	}	
		
		// Update CP log
		
		$this->logger->log_action($cp_message);			

  		$_POST['group_id'] = $group_id;

		$this->session->set_flashdata('message_success', $cp_message);
		$this->functions->redirect(BASE.AMP.'C=members'.AMP.'M=member_group_manager');
	}

	// --------------------------------------------------------------------

	/**
	 * Update Category Group Privileges
	 *
	 * Updates exp_category_groups privilege lists for
	 * editing and deleting categories
	 *
	 * @return	mixed
	 */		
	private function _update_cat_group_privs($params)
	{
		if ( ! is_array($params) OR empty($params))
		{
			return FALSE;
		}

		$expected = array('member_group', 'field', 'allow', 'site_id', 'clone_id');
		
		// turn parameters into variables
		
		foreach ($expected as $key)
		{
			// naughty!
			
			if ( ! isset($params[$key]))
			{
				return FALSE;
			}
			
			$$key = $params[$key];
		}
		
		$query = $this->db->query("SELECT group_id, ".$this->db->escape_str($field)." FROM exp_category_groups WHERE site_id = '".$this->db->escape_str($site_id)."'");
		
		// nothing to do?
		
		if ($query->num_rows() == 0)
		{
			return FALSE;
		}

		foreach ($query->result_array() as $row)
		{
			$can_do = explode('|', rtrim($row[$field], '|'));

			if ($allow === TRUE)
			{
				if (is_numeric($clone_id))
				{
					if (in_array($clone_id, $can_do) OR $clone_id == 1)
					{
						$can_do[] = $member_group;
					}						
				}
				elseif ($clone_id === FALSE)
				{
					$can_do[] = $member_group;
				}
			}
			else
			{
				$can_do = array_diff($can_do, array($member_group));
			}

			$this->db->query($this->db->update_string('exp_category_groups', array($field => implode('|', $can_do)), "group_id = '{$row['group_id']}'"));
		}
	}

	// --------------------------------------------------------------------

	/**
	 * Delete member group confirm
	 *
	 * Warning message shown when you try to delete a group
	 *
	 * @return	mixed
	 */		
	public function delete_member_group_conf()
	{
		//  Only super admins can delete member groups
		if ($this->session->userdata['group_id'] != 1)
		{
			show_error(lang('only_superadmins_can_admin_groups'));
		}		

		if ( ! $group_id = $this->input->get_post('group_id'))
		{
			return FALSE;
		}
		
		// You can't delete these groups
		if (in_array($group_id, $this->no_delete))
		{
			show_error(lang('unauthorized_access'));
		}
		
		$this->load->model('member_model');
		
		// Are there any members that are assigned to this group?
		$vars['member_count'] = $this->member_model->count_members($group_id);

		$query = $this->db->query("SELECT group_title FROM exp_member_groups WHERE site_id = '".$this->db->escape_str($this->config->item('site_id'))."' AND group_id = '".$this->db->escape_str($group_id)."'");
		$vars['group_title'] = $query->row('group_title');
		
		$vars['group_id'] = $group_id;
		
		$vars['form_hidden']['group_id'] = $group_id;
		$vars['form_hidden']['reassign'] = ($vars['member_count'] > 0) ? 'y' : 'n';

		if ($vars['member_count'] > 0)
		{
			$query = $this->db->query("SELECT group_title, group_id FROM exp_member_groups WHERE site_id = '".$this->db->escape_str($this->config->item('site_id'))."' AND group_id != '{$group_id}' order by group_title");
				
			foreach ($query->result() as $row)
			{
				$group_name = $row->group_title;
						
				if (in_array($group_name, $this->english))
				{
					$group_name = lang(strtolower(str_replace(" ", "_", $group_name)));
				}
						
				$vars['new_group_id'][$row->group_id] = $group_name;
			}		
		}			

		$this->cp->set_variable('cp_page_title', lang('delete_member_group'));
			
		$this->javascript->compile();
		
		$this->load->view('members/delete_member_group_conf', $vars);
	}

	// --------------------------------------------------------------------

	/**
	 * Delete Member Group
	 *
	 * @return	mixed
	 */		
	public function delete_member_group()
	{
		// Only super admins can delete member groups
		if ($this->session->userdata['group_id'] != 1)
		{
			show_error(lang('only_superadmins_can_admin_groups'));
		}

		if ( ! $group_id = $this->input->post('group_id'))
		{
			return FALSE;
		}
				
		if (in_array($group_id, $this->no_delete))
		{
			show_error(lang('unauthorized_access'));
		}
		
		$this->load->model('member_model');
			
		if ($this->input->get_post('reassign') == 'y' AND $this->input->get_post('new_group_id') != FALSE)
		{
			$new_group = $this->input->get_post('new_group_id');
		}
		else
		{
			$new_group = '';	
		}

		$this->member_model->delete_member_group($group_id, $new_group);

		$this->session->set_flashdata('message_success', lang('member_group_deleted'));
		$this->functions->redirect(BASE.AMP.'C=members'.AMP.'M=member_group_manager');
	}	

	// --------------------------------------------------------------------

	/**
	 * New Member Form
	 *
	 * Create a member profile form
	 *
	 * @return	mixed
	 */		
	public function new_member_form()
	{
		if ( ! $this->cp->allowed_group('can_access_members') OR ! $this->cp->allowed_group('can_admin_members'))
		{
			show_error(lang('unauthorized_access'));
		}
		
		$this->lang->loadfile('myaccount');
		$this->cp->set_variable('cp_page_title', lang('register_member'));
		
		// Find out if the user has access to any member groups
		$is_locked = ($this->session->userdata['group_id'] == 1) ? array() : array('is_locked' => 'n');
		$member_groups = $this->member_model->get_member_groups('', $is_locked);
		
		// If the user does not have access to any member groups, don't show the form
		// and explain the situation
		$vars['notice'] = ( ! $member_groups->num_rows());
		$vars['sys_admin_email'] = $this->config->item('webmaster_email');
		
		if ($vars['notice'] === TRUE)
		{
			return $this->load->view('members/register', $vars);
		}
		
		$this->load->library(array('form_validation', 'table'));
		$this->load->helper(array('string', 'snippets'));
		$this->load->language('calendar');
		
		$vars['custom_profile_fields'] = array();
		
		$config = array(
			array(
				'field'  => 'username', 
				'label'  => 'lang:username', 
				'rules'  => 'required|trim|valid_username[new]'
			),
			array(
				'field'  => 'screen_name',
				'label'  => 'lang:screen_name',
				'rules'  => 'trim|valid_screen_name[new]'
			),
			array(
				'field'  => 'password', 
				'label'  => 'lang:password', 
				'rules'  => 'required|valid_password[username]'
			),
			array(
				'field'  => 'password_confirm', 
				'label'  => 'lang:password_confirm', 
				'rules'  => 'required|matches[password]'
			),
			array(
				'field'  => 'email', 
				'label'  => 'lang:email', 
				'rules'  => 'trim|required|valid_user_email[new]'
			),
			array(
				'field'  => 'group_id', 
				'label'  => 'lang:member_group_assignment', 
				'rules'  => 'required|integer'
			)
		);

		$stock_member_fields = array(
			'url', 'location', 'occupation', 'interests', 'aol_im', 
			'yahoo_im', 'msn_im', 'icq', 'bio', 'bday_y', 'bday_m', 'bday_d'
		);
		
		foreach ($stock_member_fields as $fname)
		{
			$vars[$fname] = '';

			if ($this->input->post($fname) !== FALSE)
			{
				$vars[$fname] = $this->input->post($fname);
			}
		}


		// Birthday Options
		$vars['bday_d_options'] = array();

		$vars['bday_y_options'][''] = lang('year');
		
		for ($i = date('Y', $this->localize->now); $i > 1904; $i--)
		{
			$vars['bday_y_options'][$i] = $i;
		}

		$vars['bday_m_options'] = array(
			''	 => lang('month'),
			'01' => lang('cal_january'),
			'02' => lang('cal_february'),
			'03' => lang('cal_march'),
			'04' => lang('cal_april'),
			'05' => lang('cal_mayl'),
			'06' => lang('cal_june'),
			'07' => lang('cal_july'),
			'08' => lang('cal_august'),
			'09' => lang('cal_september'),
			'10' => lang('cal_october'),
			'11' => lang('cal_november'),
			'12' => lang('cal_december')
		);

		$vars['bday_d_options'][''] = lang('day');
		
		for ($i = 1; $i <= 31; $i++)
		{
		  $vars['bday_d_options'][$i] = $i;
		}

		if ($vars['url'] == '')
		{
		  $vars['url'] = 'http://';
		}

		// Extended profile fields
		$query = $this->member_model->get_all_member_fields(array(array('m_field_cp_reg' => 'y')), FALSE);
		
		if ($query->num_rows() > 0)
		{
			$vars['custom_profile_fields'] = $query->result_array();
			
			//  Add validation rules for custom fields
			foreach ($query->result_array() as $row)
			{
				$required  = ($row['m_field_required'] == 'n') ? '' : 'required';
				$c_config[] = array(
					'field'  => 'm_field_id_'.$row['m_field_id'], 
					'label'  => $row['m_field_label'], 
					'rules'  => $required
				);
			}
			
			$config = array_merge($config, $c_config);
		}

		$this->form_validation->set_rules($config);
		$this->form_validation->set_error_delimiters('<br /><span class="notice">', '</span>');

		if ($this->form_validation->run() === FALSE)
		{
			$this->javascript->compile();

			$vars['member_groups'] = array();

			foreach($member_groups->result() as $group)
			{
				// construct member_groups dropdown associative array
				$vars['member_groups'][$group->group_id] = $group->group_title;
			}

			$this->load->view('members/register', $vars);
		}
		else
		{
			$this->_register_member();
		}
	}
	
	// --------------------------------------------------------------------

	/**
	 * Register Member
	 *
	 * Create a member profile
	 *
	 * @return	mixed
	 */		
	public function _register_member()
	{
		$this->load->helper('security');

		$data = array();

		if ($this->input->post('group_id'))
		{
			if ( ! $this->cp->allowed_group('can_admin_mbr_groups'))
			{
				show_error(lang('unauthorized_access'));
			}

			$data['group_id'] = $this->input->post('group_id');
		}

		// -------------------------------------------
		// 'cp_members_member_create_start' hook.
		//  - Take over member creation when done through the CP
		//  - Added 1.4.2
		//
			$edata = $this->extensions->call('cp_members_member_create_start');
			if ($this->extensions->end_script === TRUE) return;
		//
		// -------------------------------------------

		// If the screen name field is empty, we'll assign is
		// from the username field.

		$data['screen_name'] = ($this->input->post('screen_name')) ? $this->input->post('screen_name') : $this->input->post('username');

		// Assign the query data

		$data['username'] 	= $this->input->post('username');
		$data['password']	= do_hash($this->input->post('password'));
		$data['email']		= $this->input->post('email');
		$data['ip_address']	= $this->input->ip_address();
		$data['unique_id']	= random_string('encrypt');
		$data['join_date']	= $this->localize->now;
		$data['language'] 	= $this->config->item('deft_lang');
		$data['timezone'] 	= ($this->config->item('default_site_timezone') && $this->config->item('default_site_timezone') != '') ? $this->config->item('default_site_timezone') : $this->config->item('server_timezone');
		$data['daylight_savings'] = ($this->config->item('default_site_dst') && $this->config->item('default_site_dst') != '') ? $this->config->item('default_site_dst') : $this->config->item('daylight_savings');
		$data['time_format'] = ($this->config->item('time_format') && $this->config->item('time_format') != '') ? $this->config->item('time_format') : 'us';

		// Was a member group ID submitted?

		$data['group_id'] = ( ! $this->input->post('group_id')) ? 2 : $_POST['group_id'];

		$base_fields = array('bday_y', 'bday_m', 'bday_d', 'url', 'location', 'occupation', 'interests', 'aol_im',
							'icq', 'yahoo_im', 'msn_im', 'bio');

		foreach ($base_fields as $val)
		{
			$data[$val] = ($this->input->post($val) === FALSE) ? '' : $this->input->post($val, TRUE);
		}

		if (is_numeric($data['bday_d']) && is_numeric($data['bday_m']))
		{
			$year = ($data['bday_y'] != '') ? $data['bday_y'] : date('Y');
			$mdays = $this->localize->fetch_days_in_month($data['bday_m'], $year);

			if ($data['bday_d'] > $mdays)
			{
				$data['bday_d'] = $mdays;
			}
		}
		
		// Clear out invalid values for strict mode
		foreach (array('bday_y', 'bday_m', 'bday_d') as $val)
		{
			if ($data[$val] == '')
			{
				unset($data[$val]);
			}
		}
		
		if ($data['url'] == 'http://')
		{
			$data['url'] = '';
		}
		
		// Extended profile fields
		$cust_fields = FALSE;
		$query = $this->member_model->get_all_member_fields(array(array('m_field_cp_reg' => 'y')), FALSE);
		
		if ($query->num_rows() > 0)
		{
			foreach ($query->result_array() as $row)
			{
				if ($this->input->post('m_field_id_'.$row['m_field_id']) !== FALSE)
				{
					$cust_fields['m_field_id_'.$row['m_field_id']] = $this->input->post('m_field_id_'.$row['m_field_id'], TRUE);
				}
			}
		}

		$member_id = $this->member_model->create_member($data, $cust_fields);

		// Write log file

		$message = lang('new_member_added');
		$this->logger->log_action($message.NBS.NBS.stripslashes($data['username']));

		// -------------------------------------------
		// 'cp_members_member_create' hook.
		//  - Additional processing when a member is created through the CP
		//
			$edata = $this->extensions->call('cp_members_member_create', $member_id, $data);
			if ($this->extensions->end_script === TRUE) return;
		//
		// -------------------------------------------

		// Update Stats
		$this->stats->update_member_stats();

		$this->session->set_flashdata(array(
			'message_success' => $message.NBS.'<b>'.stripslashes($data['username']).'</b>',
			'username' => stripslashes($data['screen_name'])
		));
		
		$this->functions->redirect(BASE.AMP.'C=members'.AMP.'M=view_all_members');
	}

	// --------------------------------------------------------------------

	/**
	 * Member Banning
	 *
	 * Member banning forms
	 *
	 * @return	mixed
	 */		
	public function member_banning()
	{
		if ( ! $this->cp->allowed_group('can_access_members') OR ! $this->cp->allowed_group('can_ban_users'))
		{
			show_error(lang('unauthorized_access'));
		}
		
		$this->load->library('table');
		
		$banned_ips	= $this->config->item('banned_ips');
		$banned_emails  = $this->config->item('banned_emails');
		$banned_usernames = $this->config->item('banned_usernames');
		$banned_screen_names = $this->config->item('banned_screen_names');

		$vars['banned_ips'] = '';
		$vars['banned_emails'] = '';
		$vars['banned_usernames'] = '';
		$vars['banned_screen_names'] = '';
		$vars['ban_action'] = $this->config->item('ban_action');
		$vars['ban_message'] = $this->config->item('ban_message');
		$vars['ban_destination'] = $this->config->item('ban_destination');
		
		$out		= '';
		$ips		= '';
		$email  	= '';
		$users  	= '';
		$screens	= '';
		
		if ($banned_ips != '')
		{			
			foreach (explode('|', $banned_ips) as $val)
			{
				$vars['banned_ips'] .= $val.NL;
			}
		}
		
		if ($banned_emails != '')
		{						
			foreach (explode('|', $banned_emails) as $val)
			{
				$vars['banned_emails'] .= $val.NL;
			}
		}
		
		if ($banned_usernames != '')
		{						
			foreach (explode('|', $banned_usernames) as $val)
			{
				$vars['banned_usernames'] .= $val.NL;
			}
		}

		if ($banned_screen_names != '')
		{						
			foreach (explode('|', $banned_screen_names) as $val)
			{
				$vars['banned_screen_names'] .= $val.NL;
			}
		}

		$this->cp->set_variable('cp_page_title', lang('user_banning'));

		$this->load->view('members/member_banning', $vars);
	}

	// --------------------------------------------------------------------

	/**
	 * Update Banning Data
	 *
	 * @return	mixed
	 */		
	public function update_banning_data()
	{
		if ( ! $this->cp->allowed_group('can_access_members') OR ! $this->cp->allowed_group('can_ban_users'))
		{
			show_error(lang('unauthorized_access'));
		}

		$this->load->model('site_model');
		
		foreach ($_POST as $key => $val)
		{
			$_POST[$key] = stripslashes($val);
		}
	
		$this->load->helper('string');
		$this->load->model('site_model');

		$banned_ips				= str_replace(NL, '|', $_POST['banned_ips']);
		$banned_emails 			= str_replace(NL, '|', $_POST['banned_emails']);
		$banned_usernames 		= str_replace(NL, '|', $_POST['banned_usernames']);
		$banned_screen_names 	= str_replace(NL, '|', $_POST['banned_screen_names']);
		
		$destination = ($_POST['ban_destination'] == 'http://') ? '' : $_POST['ban_destination'];
		
		$data = array(	
						'banned_ips'	  		=> $banned_ips,
						'banned_emails'			=> $banned_emails,
						'banned_emails'			=> $banned_emails,
						'banned_usernames'		=> $banned_usernames,
						'banned_screen_names'	=> $banned_screen_names,
						'ban_action'	  		=> $this->input->post('ban_action'),
						'ban_message'	 		=> $this->input->post('ban_message'),
						'ban_destination' 		=> $destination
					 );
				
		//  Preferences Stored in Database For Site
		$query = $this->site_model->get_site();

		foreach($query->result() AS $row)
		{
			$prefs = array_merge(unserialize(base64_decode($row->site_system_preferences)), $data);
			$this->site_model->update_site_system_preferences($prefs, $row->site_id);
		}
		
		$this->session->set_flashdata('message_success', lang('ban_preferences_updated'));		
		$this->functions->redirect(BASE.AMP.'C=members'.AMP.'M=member_banning');
	}

	// --------------------------------------------------------------------

	/**
	 * Custom Profile Fields
	 *
	 * This function show a list of current member fields and the
	 * form that allows you to create a new field.
	 *
	 * @return	mixed
	 */		
	public function custom_profile_fields($group_id = '')
	{
		if ( ! $this->cp->allowed_group('can_access_members') OR ! $this->cp->allowed_group('can_admin_members'))
		{
			show_error(lang('unauthorized_access'));
		}		

		// Fetch language file
		// There are some lines in the publish administration language file that we need.

		$this->lang->loadfile('admin_content');
		$this->load->library('table');

		$vars['fields'] = $this->member_model->get_custom_member_fields();

		$this->cp->set_variable('cp_page_title', lang('custom_profile_fields'));

		$this->jquery->tablesorter('.mainTable', '{headers: {3: {sorter: false}, 4: {sorter: false}},	widgets: ["zebra"]}');

		$this->javascript->compile();
		
		$this->cp->set_right_nav(array('create_new_profile_field' => BASE.AMP.'C=members'.AMP.'M=edit_profile_field'));
		
		$this->load->view('members/custom_profile_fields', $vars);
	}

	// --------------------------------------------------------------------

	/**
	 * Edit Profile Field
	 *
	 * This function lets you edit an existing custom field
	 *
	 * @return	mixed
	 */		
	public function edit_profile_field()
	{
		if ( ! $this->cp->allowed_group('can_access_members') OR ! $this->cp->allowed_group('can_admin_members'))
		{
			show_error(lang('unauthorized_access'));
		}

		$this->load->library('form_validation');
		$this->load->model('member_model');
        $this->load->library('table');

		// Fetch language file
		// There are some lines in the publish administration language file that we need.
		$this->lang->loadfile('admin_content');

		$this->cp->set_breadcrumb(BASE.AMP.'C=members'.AMP.'M=custom_profile_fields', lang('custom_profile_fields'));

		$type = ($m_field_id = $this->input->get_post('m_field_id')) ? 'edit' : 'new';

		$total_fields = '';
		
		if ($type == 'new')
		{
			$query = $this->db->count_all('member_fields');
			$total_fields = $query + 1;
			$vars['submit_label'] = lang('submit');
		}
		else
		{
			$vars['submit_label'] = lang('update');
		}

		$query = $this->db->get_where('member_fields', array('m_field_id'=>$m_field_id));

		if ($query->num_rows() == 0)
		{
			foreach ($this->db->list_fields('member_fields') as $f)
			{
				$$f = '';
			}
		}
		else
		{
			foreach ($query->row_array() as $key => $val)
			{
				$$key = $val;
			}
		}
		
		$vars['hidden_form_fields'] = array(
											'm_field_id' => $m_field_id,
											'cur_field_name' => $m_field_name
											);		

		$title = ($type == 'edit') ? 'edit_member_field' : 'create_member_field';

		// Field values
		// If a validation value is found, use it first, otherwise drop to the database provided value

		if ($type == 'new')
		{
			$m_field_order = $total_fields;
		}

		if ($m_field_width == '')
		{
			$m_field_width = '100%';
		}
		
		if ($m_field_maxl == '')
		{
			$m_field_maxl = '100';
		}

		if ($m_field_ta_rows == '')
		{
			$m_field_ta_rows = '10';
		}

		if ($m_field_required == '')
		{
			$m_field_required = 'n';
		}

		if ($m_field_public == '')
		{
			$m_field_public = 'y';
		}
		
		if ($m_field_reg == '')
		{
			$m_field_reg = 'n';
		}

		if ($m_field_cp_reg == '')
		{
			$m_field_cp_reg = 'n';
		}


		$vars['m_field_name'] = $m_field_name;
		$vars['m_field_label'] = $m_field_label;
		$vars['m_field_description'] = $m_field_description;
		$vars['m_field_order'] = $m_field_order;
		$vars['m_field_width'] = $m_field_width;
		$vars['m_field_maxl'] = $m_field_maxl;
		$vars['m_field_ta_rows'] = $m_field_ta_rows;
		$vars['m_field_list_items'] = $m_field_list_items;

		/** ---------------------------------
		/**  Field type
		/** ---------------------------------*/
		$vars['text_js'] = ($type == 'edit') ? 'none' : 'block';
		$vars['textarea_js'] = 'none';
		$vars['select_js'] = 'none';
		$vars['select_opt_js'] = 'none';

		switch ($m_field_type)
		{
			case 'select'	: $vars['select_js'] = 'block'; $vars['select_opt_js'] = 'block';
				break;
			case 'textarea' : $vars['textarea_js'] = 'block';
				break;
			case 'text'	 : $vars['text_js'] = 'block';
				break;
		}

		/**  Create the pull-down menu **/
		
		$vars['m_field_type_options'] = array(
											'text'=>lang('text_input'),
											'textarea'=>lang('textarea'),
											'select'=>lang('select_list')
											);
		$vars['m_field_type'] = $m_field_type;

		/**  Field formatting **/
		
		$vars['m_field_fmt_options'] = array(
											'none'=>lang('none'),
											'br'=>lang('auto_br'),
											'xhtml'=>lang('xhtml')
											);											
		$vars['m_field_fmt'] = $m_field_fmt;

		/**  Is field required? **/
		
		$vars['m_field_required_options'] = array(
		                                        'n'    => lang('no'),
		                                        'y'   => lang('yes') 
		                                        );
		                                        
        $vars['m_field_required'] = $m_field_required;

		/**  Is field public? **/
		
		$vars['m_field_public_options'] = array(
                                                'n'    => lang('no'),
                                                'y'   => lang('yes')
		                                        );
        
        $vars['m_field_public'] = $m_field_public;


		/**  Is field visible in reg page? **/

        $vars['m_field_reg_options'] = array(
                                                'n'    => lang('no'),
                                                'y'   => lang('yes')
		                                        );


		// Set our radio values- overriding w/post data if it exists
		foreach (array('m_field_required', 'm_field_public', 'm_field_reg', 'm_field_cp_reg') as $fname)
		{
			if ($this->input->post($fname) !== FALSE)
			{
				$vars[$fname] = $this->input->post($fname);
			}
			else
			{
				$vars[$fname] = $$fname;
			}
		}

		$this->cp->set_variable('cp_page_title', lang($title));

		$additional = '<script type="text/javascript">
					function showhide_element(id)
					{
						// set everything hidden
						document.getElementById("text_block").style.display = "none";
						document.getElementById("textarea_block").style.display = "none";
						document.getElementById("select_block").style.display = "none";
						
						// reveal the shown element
						document.getElementById(id+"_block").style.display = "block";
					}
			</script>';
		$this->cp->add_to_foot($additional);

		$this->javascript->compile();
				
		$this->load->view('members/edit_profile_field', $vars);
	}

	// ------------------------------------------------------------------
	
	/**
	 * Validate Custom Field
	 *
	 * @return void
	 */
	private function _validate_custom_field($edit)
	{
		$this->load->library('form_validation');
		
		$is_edit = ($edit == TRUE) ? 'y' : 'n';
		$this->form_validation->set_rules("m_field_name", 'lang:fieldname', 'required|callback__valid_fieldname['.$is_edit.']');
		$this->form_validation->set_rules("m_field_label", 'lang:fieldlabel', 'required');
		$this->form_validation->set_rules("m_field_description", '', '');
		$this->form_validation->set_rules("m_field_order", '', '');
		$this->form_validation->set_rules("m_field_width", '', '');	
		$this->form_validation->set_rules("m_field_list_items", '', '');				
		$this->form_validation->set_rules("m_field_maxl", '', '');
		$this->form_validation->set_rules("m_field_ta_rows", '', '');
		$this->form_validation->set_rules("m_field_fmt", '', '');
				
		$this->form_validation->set_error_delimiters('<br /><span class="notice">', '</span>');
	}

	// -------------------------------------------------------------------
	
	/**
	 * Validate Fieldname
	 *
	 * @param	string
	 * @param	string
	 */
	public function _valid_fieldname($str, $edit)
	{
		$this->lang->loadfile('admin_content');

		if (in_array($str, $this->cp->invalid_custom_field_names()))
		{
			$this->form_validation->set_message('_valid_fieldname', lang('reserved_word'));
			return FALSE;
		}

		if (preg_match('/[^a-z0-9\_\-]/i', $str))
		{
			$this->form_validation->set_message('_valid_fieldname', lang('invalid_characters'));
			return FALSE;
		}
				
		// Is the field name taken?
		
		$this->db->where('m_field_name', $str);
		$this->db->from('member_fields');
		$count =  $this->db->count_all_results();
		
		if (($edit == 'n' OR ($edit == 'y' && $str != $this->input->post('cur_field_name')))
			&& $count  > 0)
		{
			$this->form_validation->set_message('_valid_fieldname', lang('duplicate_field_name'));
			return FALSE;
		}
		
		return TRUE;
	}

	// --------------------------------------------------------------------

	/**
	 * Update Profile Fields
	 *
	 * This function alters the "exp_member_data" table, adding
	 * the new custom fields.
	 *
	 * @return	mixed
	 */		
	public function update_profile_fields()
	{
		if ( ! $this->cp->allowed_group('can_access_members') OR ! $this->cp->allowed_group('can_admin_members'))
		{
			show_error(lang('unauthorized_access'));
		}

		// If the $field_id variable is present we are editing an
		// existing field, otherwise we are creating a new one
		
		$edit = (isset($_POST['m_field_id']) AND $_POST['m_field_id'] != '') ? TRUE : FALSE;

		$this->_validate_custom_field($edit);

		if ($this->form_validation->run() === FALSE)
		{
			return $this->edit_profile_field();
		}			

		$this->lang->loadfile('admin_content');
		$this->load->model('member_model');

		unset($_POST['cur_field_name']);		

		if ($this->input->post('m_field_list_items') != '')
		{
			// Load the string helper
			$this->load->helper('string');

			$_POST['m_field_list_items'] = quotes_to_entities($_POST['m_field_list_items']);
		}

		// Construct the query based on whether we are updating or inserting
		if ($edit === TRUE)
		{
			$n = $_POST['m_field_maxl'];
		
			if ($_POST['m_field_type'] == 'text')
			{
				if ( ! is_numeric($n) OR $n == '' OR $n == 0)
				{
					$n = '100';
				}
			
				$f_type = 'varchar('.$n.') NULL DEFAULT NULL';
			}
			else
			{
				$f_type = 'text NULL DEFAULT NULL';
			}
		
			$this->db->query("ALTER table exp_member_data CHANGE m_field_id_".$_POST['m_field_id']." m_field_id_".$_POST['m_field_id']." $f_type");			
					
			$id = $_POST['m_field_id'];
			unset($_POST['m_field_id']);

			$this->db->query($this->db->update_string('exp_member_fields', $_POST, 'm_field_id='.$id));
		}
		else
		{
			if ($_POST['m_field_order'] == 0 OR $_POST['m_field_order'] == '')
			{
				$query = $this->member_model->count_records('member_fields');
			
				$total = $query->row('count')  + 1;
			
				$_POST['m_field_order'] = $total;
			}

			$n = $_POST['m_field_maxl'];
		
			if ($_POST['m_field_type'] == 'text')
			{
				if ( ! is_numeric($n) OR $n == '' OR $n == 0)
				{
					$n = '100';
				}
			
				$f_type = 'varchar('.$n.') NULL DEFAULT NULL';
			}
			else
			{
				$f_type = 'text NULL DEFAULT NULL';
			}
			
			unset($_POST['m_field_id']);

			$this->db->query($this->db->insert_string('exp_member_fields', $_POST));
									
			$this->db->query('ALTER table exp_member_data add column m_field_id_'.$this->db->insert_id().' '.$f_type);
			
			$sql = "SELECT exp_members.member_id
					FROM exp_members
					LEFT JOIN exp_member_data ON exp_members.member_id = exp_member_data.member_id
					WHERE exp_member_data.member_id IS NULL
					ORDER BY exp_members.member_id";
			
			$query = $this->db->query($sql);
			
			if ($query->num_rows() > 0)
			{
				foreach ($query->result_array() as $row)
				{
					$this->db->query("INSERT INTO exp_member_data (member_id) values ('{$row['member_id']}')");
				}
			}
		}
	
		$cp_message = ($edit) ? lang('field_updated') : lang('field_created');
		$this->session->set_flashdata('message_success', $cp_message);
		$this->functions->redirect(BASE.AMP.'C=members'.AMP.'M=custom_profile_fields');
	}

	// --------------------------------------------------------------------

	/**
	 * Delete Profile Field Confirm
	 *
	 * Warning message if you try to delete a custom profile field
	 *
	 * @return	mixed
	 */		
	public function delete_profile_field_conf()
	{
		if ( ! $this->cp->allowed_group('can_access_members') OR ! $this->cp->allowed_group('can_admin_members'))
		{
			show_error(lang('unauthorized_access'));
		}

		if ( ! ($m_field_id = $this->input->get_post('m_field_id')))
		{
			return FALSE;
		}
		
		$this->lang->loadfile('admin_content');

		$this->db->select('m_field_label');
		$this->db->from('member_fields');
		$this->db->where('m_field_id', $m_field_id);
		$query = $this->db->get();
		
		$vars['form_action'] = 'C=members'.AMP.'M=delete_profile_field'.AMP.'m_field_id='.$m_field_id;
		$vars['form_hidden'] = array('m_field_id'=>$m_field_id);
		$vars['field_name'] = $query->row('m_field_label');
				
		$this->cp->set_variable('cp_page_title', lang('delete_field'));
		
		$this->load->view('members/delete_profile_fields_confirm', $vars);
	}

	// --------------------------------------------------------------------

	/**
	 * Delete member profile field
	 *
	 * @return	mixed
	 */		
	public function delete_profile_field()
	{
		if ( ! $this->cp->allowed_group('can_access_members') OR ! $this->cp->allowed_group('can_admin_members'))
		{
			show_error(lang('unauthorized_access'));
		}

		if ( ! $m_field_id = $this->input->get_post('m_field_id'))
		{
			return false;
		}
		
		$query = $this->db->query("SELECT m_field_label FROM exp_member_fields WHERE m_field_id = '$m_field_id'");
		$m_field_label = $query->row('m_field_label') ;
				
		$this->db->query("ALTER TABLE exp_member_data DROP COLUMN m_field_id_".$m_field_id);
		$this->db->query("DELETE FROM exp_member_fields WHERE m_field_id = '$m_field_id'");
		
		$cp_message = lang('profile_field_deleted').NBS.NBS.$m_field_label;
		$this->logger->log_action($cp_message);		

		$this->session->set_flashdata('message_success', $cp_message);
		$this->functions->redirect(BASE.AMP.'C=members'.AMP.'M=custom_profile_fields');
	}

	// --------------------------------------------------------------------

	/**
	 * Edit Field Order
	 *
	 * @return	mixed
	 */		
	public function edit_field_order()
	{
		if ( ! $this->cp->allowed_group('can_access_members') OR ! $this->cp->allowed_group('can_admin_members'))
		{
			show_error(lang('unauthorized_access'));
		}

		$this->load->model('member_model');
		$this->lang->loadfile('admin_content');
		$this->load->library('table');

		$custom_fields = $this->member_model->get_custom_member_fields();
		
		$fields = array();
		
		foreach ($custom_fields->result() as $field)
		{
			$fields[] = array(
								'id'	=> $field->m_field_id,
								'label'	=> $field->m_field_label,
								'name'	=> $field->m_field_name,
								'value'	=> $field->m_field_order
							);
		}
				
		$vars['fields'] = $fields;
		
		$this->cp->set_variable('cp_page_title', lang('edit_field_order'));
		$this->cp->set_breadcrumb(BASE.AMP.'C=members'.AMP.'M=custom_profile_fields', lang('custom_profile_fields'));

		$this->load->view('members/edit_field_order', $vars);
	}

	// --------------------------------------------------------------------

	/**
	 * Update field order
	 *
	 * This function receives the field order submission
	 *
	 * @return	mixed
	 */		
	public function update_field_order()
	{
		if ( ! $this->cp->allowed_group('can_access_members') OR ! $this->cp->allowed_group('can_admin_members'))
		{
			show_error(lang('unauthorized_access'));
		}
				
		foreach ($_POST as $key => $val)
		{
			$this->db->set('m_field_order', $val);
			$this->db->where('m_field_name', $key);
			$this->db->update('member_fields');
		}

		$this->session->set_flashdata('message_success', lang('field_order_updated'));
		$this->functions->redirect(BASE.AMP.'C=members'.AMP.'M=edit_field_order');
	}

	// --------------------------------------------------------------------

	/**
	 * IP Search
	 *
	 * IP Search Form
	 *
	 * @return	mixed
	 */		
	public function ip_search()
	{
		if ( ! $this->cp->allowed_group('can_access_members') OR ! $this->cp->allowed_group('can_admin_members'))
		{
			show_error(lang('unauthorized_access'));
		}
		
		$message = '';
		$ip = ($this->input->get_post('ip_address') != FALSE) ? str_replace('_', '.',$this->input->get_post('ip_address')) : '';

		if ($this->input->get_post('error') == 2)
		{
			$message = lang('ip_search_no_results');
		}
		elseif ($this->input->get_post('error') == 1)
		{
			$message = lang('ip_search_too_short');
		}
		
        $this->load->library('table');

		$this->cp->set_variable('cp_page_title', lang('ip_search'));
		
		$this->javascript->compile();

		$vars['message'] = $message;

		$this->load->view('members/ip_search', $vars);
	}

	// --------------------------------------------------------------------

	/**
	 * Do IP Search
	 *
	 * Executes the search for IP address
	 *
	 * @return	mixed
	 */		
	public function do_ip_search()
	{
		if ( ! $this->cp->allowed_group('can_access_members') OR ! $this->cp->allowed_group('can_admin_members'))
		{
			show_error(lang('unauthorized_access'));
		}

		$this->lang->loadfile('members');
		$this->load->library('table');
		$this->load->library('pagination');
		$this->load->model('member_model');

		$grand_total = 0;

		$ip = str_replace('_', '.', $this->input->get_post('ip_address'));
		$url_ip = str_replace('.', '_', $ip);
	
		if ($ip == '')
		{
			$this->functions->redirect(BASE.AMP.'C=members'.AMP.'M=ip_search');
		}
	
		if (strlen($ip) < 3)
		{
			$this->functions->redirect(BASE.AMP.'C=members'.AMP.'M=ip_search'.AMP.'error=1'.AMP.'ip_address='.$url_ip);
		}

		//  Set some defaults for pagination

		$per_page = ($this->input->get('per_page') != '') ? $this->input->get('per_page') : '0';

		// Find Member Accounts with IP
		
		$this->db->from('members');
		$this->db->like('ip_address', $ip);
		$total = $this->db->count_all_results(); // for paging
		$grand_total += $total;

		$config['base_url'] = BASE.AMP.'C=members'.AMP.'M=do_ip_search'.AMP.'ip_address='.$url_ip;
		$config['per_page'] = '10';
		$config['total_rows'] = $total;
		$config['page_query_string'] = TRUE;
		$config['full_tag_open'] = '<p id="paginationLinks">';
		$config['full_tag_close'] = '</p>';
		$config['prev_link'] = '<img src="'.$this->cp->cp_theme_url.'images/pagination_prev_button.gif" width="13" height="13" alt="&lt;" />';
		$config['next_link'] = '<img src="'.$this->cp->cp_theme_url.'images/pagination_next_button.gif" width="13" height="13" alt="&gt;" />';
		$config['first_link'] = '<img src="'.$this->cp->cp_theme_url.'images/pagination_first_button.gif" width="13" height="13" alt="&lt; &lt;" />';
		$config['last_link'] = '<img src="'.$this->cp->cp_theme_url.'images/pagination_last_button.gif" width="13" height="13" alt="&gt; &gt;" />';

		$this->pagination->initialize($config);

		$vars['member_accounts_pagination'] = $this->pagination->create_links();
		$vars['members_accounts'] = $this->member_model->get_ip_members($ip, 10, $per_page);

		//  Find Channel Entries with IP
		
		$sql = "SELECT COUNT(*) AS count
				FROM exp_channel_titles t, exp_members m, exp_sites s
				WHERE t.ip_address LIKE '%".$this->db->escape_like_str($ip)."%'
				AND t.site_id = s.site_id
				AND t.author_id = m.member_id
				ORDER BY entry_id desc ";
		
		$query = $this->db->query($sql);
		$total = $query->row('count');

		$grand_total += $total;

		$config['total_rows'] = $total;
		$this->pagination->initialize($config);

		$sql = "SELECT s.site_label, t.entry_id, t.channel_id, t.title, t.ip_address, m.member_id, m.username, m.screen_name, m.email
				FROM exp_channel_titles t, exp_members m, exp_sites s
				WHERE t.ip_address LIKE '%".$this->db->escape_like_str($ip)."%'
				AND t.site_id = s.site_id
				AND t.author_id = m.member_id
				ORDER BY entry_id desc
				LIMIT {$per_page}, 10";
		
		$vars['channel_entries_pagination'] = $this->pagination->create_links();
		$vars['channel_entries'] = $this->db->query($sql);

		//  Find Comments with IP
		// But only if the comment module is installed

		$this->db->from('modules');
		$this->db->where('module_name', 'Comment');
		$comment_installed = $this->db->count_all_results();
		
		if ($comment_installed  == 1)
		{
			$sql = "SELECT COUNT(*) AS count
					FROM exp_channel_titles t, exp_members m, exp_sites s
					WHERE t.ip_address LIKE '%".$this->db->escape_like_str($ip)."%'
					AND t.site_id = s.site_id
					AND t.author_id = m.member_id
					ORDER BY entry_id desc ";
		
			$query = $this->db->query($sql);
			$total = $query->row('count');

			$grand_total += $total;

			$config['total_rows'] = $total;
			$this->pagination->initialize($config);

			$sql = "SELECT s.site_label, t.entry_id, t.channel_id, t.title, t.ip_address, m.member_id, m.username, m.screen_name, m.email
					FROM exp_channel_titles t, exp_members m, exp_sites s
					WHERE t.ip_address LIKE '%".$this->db->escape_like_str($ip)."%'
					AND t.site_id = s.site_id
					AND t.author_id = m.member_id
					ORDER BY entry_id desc
					LIMIT {$per_page}, 10";
		
			$vars['channel_entries_pagination'] = $this->pagination->create_links();
			$vars['channel_entries'] = $this->db->query($sql);
		}

		// Find Forum Topics with IP
		// But only if the forum module is installed

		$this->db->from('modules');
		$this->db->where('module_name', 'Forum');
		$forum_installed = $this->db->count_all_results();
		
		if ($forum_installed  == 1)
		{
			$sql = "SELECT COUNT(*) AS count
					FROM exp_forum_topics f, exp_members m, exp_forum_boards b
					WHERE f.ip_address LIKE '%".$this->db->escape_like_str($ip)."%'
					AND f.board_id = b.board_id
					AND f.author_id = m.member_id
					ORDER BY f.topic_id desc";
		
			$query = $this->db->query($sql);
			$total = $query->row('count');

			$grand_total += $total;

			$config['total_rows'] = $total;
			$this->pagination->initialize($config);

			$sql = "SELECT f.topic_id, f.forum_id, f.title, f.ip_address, m.member_id, m.screen_name, m.email, b.board_forum_url
					FROM exp_forum_topics f, exp_members m, exp_forum_boards b
					WHERE f.ip_address LIKE '%".$this->db->escape_like_str($ip)."%'
					AND f.board_id = b.board_id
					AND f.author_id = m.member_id
					ORDER BY f.topic_id desc
					LIMIT {$per_page}, 10";
		
			$vars['forum_topics_pagination'] = $this->pagination->create_links();
			$vars['forum_topics'] = $this->db->query($sql);
		
			//  Find Forum Posts with IP

			$sql = "SELECT COUNT(*) AS count
					FROM exp_forum_posts p, exp_members m
					WHERE p.ip_address LIKE '%".$this->db->escape_like_str($ip)."%'
					AND p.author_id = m.member_id
					ORDER BY p.topic_id desc";
		
			$query = $this->db->query($sql);
			$total = $query->row('count');

			$grand_total += $total;

			$config['total_rows'] = $total;
			$this->pagination->initialize($config);

			$sql = "SELECT p.post_id, p.forum_id, p.body, p.ip_address, m.member_id, m.screen_name, m.email, b.board_forum_url
					FROM exp_forum_posts p, exp_members m, exp_forum_boards b
					WHERE p.ip_address LIKE '%".$this->db->escape_like_str($ip)."%'
					AND p.author_id = m.member_id
					AND p.board_id = b.board_id
					ORDER BY p.topic_id desc
					LIMIT {$per_page}, 10";

			$vars['forum_posts_pagination'] = $this->pagination->create_links();
			$vars['forum_posts'] = $this->db->query($sql);
		
		}

		$this->cp->set_variable('cp_page_title', lang('ip_search'));
		
		$this->javascript->compile();

		$vars['grand_total'] = $grand_total;

		$this->load->view('members/ip_search_results', $vars);
	}

	// --------------------------------------------------------------------

	/**
	 * Member Validation
	 *
	 * @return	mixed
	 */		
	public function member_validation()
	{
		if ( ! $this->cp->allowed_group('can_access_members') OR ! $this->cp->allowed_group('can_admin_members'))
		{
			show_error(lang('unauthorized_access'));
		}
				
		$this->load->library('table');
		$vars['message'] = FALSE;

		$this->cp->set_variable('cp_page_title', lang('member_validation'));
	
		$this->jquery->tablesorter('.mainTable', '{headers: {1: {sorter: false}},	widgets: ["zebra"]}');

		$this->javascript->output('
			$("#toggle_all").click(function() {
				var checked_status = this.checked;
				$("input.toggle").each(function() {
					this.checked = checked_status;
				});
			});
		');

		$this->javascript->compile();
		
		$group_members = $this->member_model->get_group_members(4);

		if ($group_members->num_rows() == 0)
		{
			$vars['message'] = lang('no_members_to_validate');
		}

		$vars['member_list'] = $group_members;
		
		$vars['options']['activate'] = lang('validate_selected');

		if ($this->cp->allowed_group('can_delete_members'))
		{
			$vars['options']['delete'] = lang('delete_selected');
		}
		
		$this->load->view('members/activate', $vars);
	}

	// --------------------------------------------------------------------

	/**
	 * Validate Members
	 *
	 * Validate/Delete Selected Members
	 *
	 * @return	mixed
	 */		
	public function validate_members()
	{
		if ( ! $this->cp->allowed_group('can_access_members') OR ! $this->cp->allowed_group('can_admin_members'))
		{
			show_error(lang('unauthorized_access'));
		}
		
		if ( ! $this->cp->allowed_group('can_delete_members') && $this->input->post('action') != 'activate')
		{
			show_error(lang('unauthorized_access'));
		}
		
		if ( ! $this->input->post('toggle'))
		{
			return $this->member_validation();
		}

		$send_email = (isset($_POST['send_notification'])) ? TRUE : FALSE;
		
		if ($send_email == TRUE)
		{
			if ($this->input->post('action') == 'activate')
			{
				$template = $this->functions->fetch_email_template('validated_member_notify');
			}
			else
			{
				$template = $this->functions->fetch_email_template('decline_member_validation');
			}
			
			$this->load->library('email');
			$this->email->wordwrap = true;
		}

		$group_id = $this->config->item('default_member_group');

		// Load the text helper
		$this->load->helper('text');
		
		foreach ($_POST['toggle'] as $key => $val)
		{
			if ($send_email == TRUE)
			{
				$this->db->select('username, screen_name, email');
				$this->db->from('members');
				$this->db->where('member_id', $val);
				$this->db->where('email != ""');
				$query = $this->db->get();			
				
				if ($query->num_rows() == 1)
				{	
					$swap = array(
									'name'		=> ($query->row('screen_name')  != '') ? $query->row('screen_name')  : $query->row('username') ,
									'site_name'	=> stripslashes($this->config->item('site_name')),
									'site_url'	=> $this->config->item('site_url')
								 );

					$email_tit = $this->functions->var_swap($template['title'], $swap);
					$email_msg = $this->functions->var_swap($template['data'], $swap);
							
					$this->email->EE_initialize();
					$this->email->from($this->config->item('webmaster_email'), $this->config->item('webmaster_name'));	
					$this->email->to($query->row('email') );
					$this->email->subject($email_tit);	
					$this->email->message(entities_to_ascii($email_msg));		
					$this->email->send();
				}
			}
			
			if ($this->input->post('action') == 'activate')
			{
				$this->db->set('group_id', $group_id);
				$this->db->where('member_id', $val);
				$this->db->update('members');
			}
			else
			{
				$this->db->query("DELETE FROM exp_members WHERE member_id = '$val'");
				$this->db->query("DELETE FROM exp_member_data WHERE member_id = '$val'");
				$this->db->query("DELETE FROM exp_member_homepage WHERE member_id = '$val'");
				
				$message_query = $this->db->query("SELECT DISTINCT recipient_id FROM exp_message_copies WHERE sender_id = '$val' AND message_read = 'n'");

				$this->db->query("DELETE FROM exp_message_copies WHERE sender_id = '$val'");
				$this->db->query("DELETE FROM exp_message_data WHERE sender_id = '$val'");
				$this->db->query("DELETE FROM exp_message_folders WHERE member_id = '$val'");
				$this->db->query("DELETE FROM exp_message_listed WHERE member_id = '$val'");
				
				if ($message_query->num_rows() > 0)
				{
					foreach($message_query->result_array() as $row)
					{
						$count_query = $this->db->query("SELECT COUNT(*) AS count FROM exp_message_copies WHERE recipient_id = '".$row['recipient_id']."' AND message_read = 'n'");
						$this->db->query($this->db->update_string('exp_members', array('private_messages' => $count_query->row('count') ), "member_id = '".$row['recipient_id']."'"));
					}
				}
			}
		}
		
		$this->stats->update_member_stats();

		/* -------------------------------------------
		/* 'cp_members_validate_members' hook.
		/*  - Additional processing when member(s) are validated in the CP
		/*  - Added 1.5.2, 2006-12-28
		*/
			$edata = $this->extensions->call('cp_members_validate_members');
			if ($this->extensions->end_script === TRUE) return;
		/*
		/* -------------------------------------------*/
			
		$vars['message'] = ($this->input->post('action') == 'activate') ? lang('members_are_validated') : lang('members_are_deleted');

		$this->cp->set_variable('cp_page_title', $vars['message']);

		$this->load->view("members/message", $vars);
	}	
}
/* End of file members.php */
/* Location: ./system/expressionengine/controllers/cp/members.php */
