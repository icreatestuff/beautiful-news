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
 * ExpressionEngine CP Home Page Class
 *
 * @package		ExpressionEngine
 * @subpackage	Control Panel
 * @category	Control Panel
 * @author		EllisLab Dev Team
 * @link		http://expressionengine.com
 */
class Homepage extends CI_Controller {

	/**
	 * Index function
	 *
	 * @access	public
	 * @return	void
	 */	
	function index()
	{
		$this->cp->get_installed_modules();
		$this->cp->set_variable('cp_page_title', lang('main_menu'));

		$message			= array();
		$show_notice		= $this->_checksum_bootstrap_files();
		$allowed_templates	= $this->session->userdata('assigned_template_groups');
		
		// Notices only show for super admins
		if ($this->session->userdata['group_id'] == 1)
		{
			if ($this->config->item('new_version_check') == 'y')
			{
				$message[] = $this->_version_check();
			}
			
			// Check to see if the config file matches the Core version constant
			if (str_replace('.', '', APP_VER) !== $this->config->item('app_version'))
			{
				$config_version = 	substr($this->config->item('app_version'), 0, 1).'.'.substr($this->config->item('app_version'), 1, 1).'.'.substr($this->config->item('app_version'), 2);
				$message[] = sprintf(lang('version_mismatch'), $config_version, APP_VER);
			}
			
			// Check to see if there are any items in the developer log
			$this->load->model('tools_model');
			$unviewed_developer_logs = $this->tools_model->count_unviewed_developer_logs();
			
			if ($unviewed_developer_logs > 0)
			{
				$message[] = sprintf(lang('developer_logs'), $unviewed_developer_logs, BASE.AMP.'C=tools_logs'.AMP.'M=view_developer_log');
			}
			
			$show_notice = ($show_notice OR ! empty($message));
		}
		
		$vars = array(
			'message'			=> implode($message, "\n\n"),
			'instructions'		=> lang('select_channel_to_post_in'),
			'show_page_option'	=> (isset($this->cp->installed_modules['pages'])) ? TRUE : FALSE,
			'info_message_open'	=> ($this->input->cookie('home_msg_state') != 'closed' && $show_notice) ? TRUE : FALSE,
			'no_templates'		=> sprintf(lang('no_templates_available'), BASE.AMP.'C=design'.AMP.'M=new_template_group'),
			
			'can_access_modify'		=> TRUE,
			'can_access_content'	=> TRUE,
			'can_access_templates'	=> (count($allowed_templates) > 0 && $this->cp->allowed_group('can_access_design')) ? TRUE : FALSE
		);
		
		
		// Pages module is installed, need to check perms
		// to see if the member group can access it.
		// Super admin sees all.
		
		if ($vars['show_page_option'] && $this->session->userdata('group_id') != 1)
		{
			$this->load->model('member_model');
			$vars['show_page_option'] = $this->member_model->can_access_module('pages');
		}
		
		$vars['recent_entries'] = $this->_recent_entries();

		// A few more permission checks
		
		if ( ! $this->cp->allowed_group('can_access_publish'))
		{
			$vars['show_page_option'] = FALSE;
			
			if ( ! $this->cp->allowed_group('can_access_edit') && ! $this->cp->allowed_group('can_admin_templates'))
			{
				$vars['can_access_modify'] = FALSE;
				
				if ( ! $this->cp->allowed_group('can_admin_channels')  && ! $this->cp->allowed_group('can_admin_sites'))
				{
					$vars['can_access_content'] = FALSE;
				}
			}
		}
		
		//  Comment blocks
		$vars['comments_installed']			= $this->db->table_exists('comments');
		$vars['can_moderate_comments']		= $this->cp->allowed_group('can_moderate_comments') ? TRUE : FALSE;
		$vars['comment_validation_count']	= ($vars['comments_installed']) ? $this->_total_validating_comments() : FALSE;	

		// Most recent comment and most recent entry
		$this->load->model('channel_model');

		$vars['cp_recent_ids'] = array(
			'entry'		=> $this->channel_model->get_most_recent_id('entry')
		);
		
		// 2.3.1 Patch
		if (version_compare(APP_VER, '2.3.1', '<') && $this->session->userdata('group_id') == 1)
		{
			$show_notice = TRUE;
			$vars['info_message_open'] = TRUE; // big mistakes cannot be hidden
			$vars['version'] = $this->_check_patch();
		}

		// Prep js
		$this->javascript->set_global('lang.close', lang('close'));
		
		if ($show_notice)
		{
			$this->javascript->set_global('importantMessage.state', $vars['info_message_open']);
		}

		$this->cp->add_js_script('file', 'cp/homepage');
		$this->javascript->compile();
		
		$this->load->view('homepage', $vars);
	}


	// --------------------------------------------------------------------
	
	/**
	 *  Get Comments Awaiting Validation
	 *
	 * Gets total number of comments with 'pending' status
	 *
	 * @access	private
	 * @return	integer
	 */
	function _total_validating_comments()
	{  
		$this->db->where('status', 'p');
		$this->db->where('site_id', (int) $this->config->item('site_id'));
		$this->db->from('comments');

		return $this->db->count_all_results();
  	}


	// --------------------------------------------------------------------
	
	/**
	 *  Get Recent Entries
	 *
	 * Gets total number of comments with 'pending' status
	 *
	 * @access	private
	 * @return	array
	 */
	function _recent_entries()
	{
		$this->load->model('channel_entries_model');
		$entries = array();

		$query = $this->channel_entries_model->get_recent_entries(10);
		
		if ($query && $query->num_rows() > 0)
		{
			$result = $query->result();
			foreach($result as $row)
			{
				$link = '';
				
				if (($row->author_id == $this->session->userdata('member_id')) OR $this->cp->allowed_group('can_edit_other_entries'))
				{
					$link = BASE.AMP.'C=content_publish'.AMP.'M=entry_form'.AMP.'channel_id='.$row->channel_id.AMP.'entry_id='.$row->entry_id;
				}
				

				$link = ($link == '') ? $row->title: '<a href="'.$link.'">'.$row->title.'</a>';
				
				$entries[] = $link;
			}
		}
		
		return $entries;
	}



	// --------------------------------------------------------------------

	/**
	 * Accept Bootstrap Checksum Changes
	 * 
	 * Updates the bootstrap file checksums with the new versions.
	 *
	 * @access	public
	 */
	function accept_checksums()
	{
		if ($this->session->userdata('group_id') != 1)
		{
			show_error(lang('unauthorized_access'));
		}
		
		$this->load->library('file_integrity');
		$changed = $this->file_integrity->check_bootstrap_files(TRUE);

		if ($changed)
		{
			foreach($changed as $site_id => $paths)
			{
				foreach($paths as $path)
				{
					$this->file_integrity->create_bootstrap_checksum($path, $site_id);
				}
			}
		}
		
		$this->functions->redirect(BASE.AMP.'C=homepage');
	}

	// --------------------------------------------------------------------

	/**
	 * Bootstrap Checksum Validation
	 * 
	 * Creates a checksum for our bootstrap files and checks their
	 * validity with the database
	 *
	 * @access	private
	 */
	function _checksum_bootstrap_files()
	{
		$this->load->library('file_integrity');
		$changed = $this->file_integrity->check_bootstrap_files();

		if ($changed)
		{
			// Email the webmaster - if he isn't already looking at the message
			
			if ($this->session->userdata('email') != $this->config->item('webmaster_email'))
			{
				$this->file_integrity->send_site_admin_warning($changed);
			}
			
			if ($this->session->userdata('group_id') == 1)
			{
				$this->load->vars(array('new_checksums' => $changed));
				return TRUE;
			}
		}
		
		return FALSE;
	}
	
	// --------------------------------------------------------------------

	/**
	 * EE Version Check function
	 * 
	 * Requests a file from ExpressionEngine.com that informs us what the current available version
	 * of ExpressionEngine.
	 *
	 * @access	private
	 * @return	bool|string
	 */	
	function _version_check()
	{	
		$download_url = $this->cp->masked_url('https://secure.expressionengine.com/download.php');
		
		$this->load->helper('version_helper');
			
		$version_file = get_version_info();
		
		if ( ! $version_file)
		{
			return sprintf(
				lang('new_version_error'),
				$download_url
			);
		}

		$new_release = FALSE;
		$high_priority = FALSE;

		// Do we have a newer version out?
		foreach ($version_file as $app_data)
		{
			if ($app_data[0] > APP_VER && $app_data[2] == 'high')
			{
				$new_release = TRUE;
				$high_priority = TRUE;
				$high_priority_release = array(
						'version'		=> $app_data[0],
						'build'			=> $app_data[1]
					);

				continue;
			}
			elseif ($app_data[1] > APP_BUILD && $app_data[2] == 'high')
			{
				// A build could sometimes be a security release.  So we can plan for it here.
				$new_release = TRUE;
				$high_priority = TRUE;
				$high_priority_release = array(
						'version'		=> $app_data[0],
						'build'			=> $app_data[1]
					);

				continue;					
			}
		}
		
		if (! $new_release)
		{
			return FALSE;
		}

		$cur_ver = end($version_file);

		// Extracting the date the build was released.  IF the build was 
		// released in the past 2 calendar days, we don't show anything
		// on the control panel home page unless it was a security release
		$date_threshold = mktime(0, 0, 0, 
							substr($cur_ver[1], 4, -2), // Month
							(substr($cur_ver[1], -2) + 2), // Day + 2 
							substr($cur_ver[1], 0, 4) // Year
					);		
		
		if (($this->localize->now < $date_threshold) && $high_priority != TRUE)
		{
			return FALSE;
		}		
		
		if ($high_priority)
		{
			return sprintf(lang('new_version_notice_high_priority'),
						   $high_priority_release['version'],	
						   $high_priority_release['build'],
						   $cur_ver[0],
						   $cur_ver[1],
						   $download_url,
						   $this->cp->masked_url($this->config->item('doc_url').'installation/update.html'));
		}
		else
		{
			return sprintf(lang('new_version_notice'),
						   $details['version'],
						   $download_url,
						   $this->cp->masked_url($this->config->item('doc_url').'installation/update.html'));					
		}
	}

	// --------------------------------------------------------------------

	/**
	 * EE 2.3.1 Patch Check
	 *
	 * @access	private
	 * @return	string
	 */
	private function _check_patch()
	{
		if (version_compare(APP_VER, '2.3.0', '<'))
		{
			$msg = '<span class="notice">Patch unsuccessful!</span><br><br>';
			$msg .= 'This patch cannot be applied to ExpressionEngine versions older than 2.3.0.<br><br>Please do a full upgrade to version 2.3.1 or contact <a href="http://expressionengine.com/forums">tech support</a> for more information.';
			return $msg;
		}

		$contents = file_get_contents(BASEPATH.'core/Security.php');
		$checksum = substr_count($contents, 'replace');
		
		unset($contents);
		
		if ($checksum != 40)
		{
			$msg = '<span class="notice">Patch unsuccessful!</span><br><br>';
			$msg .= 'Incorrect File: <kbd>system/codeigniter/core/Security.php</kbd>.<br><br>If you need assistance, please contact <a href="http://expressionengine.com/forums">tech support</a> for more information.';
			return $msg;
		}

		$this->config->update_site_prefs(array('app_version' => '231'));
		$msg = '<span class="go_notice">Patch successfully applied!</span><br><br>';
		$msg .= 'You are now on ExpressionEngine 2.3.1.';
		return $msg;
	}

}

/* End of file homepage.php */
/* Location: ./system/expressionengine/controllers/cp/homepage.php */
