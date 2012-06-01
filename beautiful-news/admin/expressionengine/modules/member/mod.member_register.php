<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

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

// --------------------------------------------------------------------

/**
 * Member Management Module
 *
 * @package		ExpressionEngine
 * @subpackage	Modules
 * @category	Modules
 * @author		EllisLab Dev Team
 * @link		http://expressionengine.com
 */

class Member_register extends Member {

	var $errors = array();

	/**
	 * Member Registration Form
	 */
	public function registration_form()
	{
		// Do we allow new member registrations?
		if ($this->EE->config->item('allow_member_registration') == 'n')
		{
			$data = array(	
				'title' 	=> lang('member_registration'),
				'heading'	=> lang('notice'),
				'content'	=> lang('mbr_registration_not_allowed'),
				'link'		=> array(
					$this->EE->functions->fetch_site_index(),
					stripslashes($this->EE->config->item('site_name'))
				)
			);

			$this->EE->output->show_message($data);
		}

		// Is the current user logged in?
		if ($this->EE->session->userdata('member_id') != 0)
		{
			return $this->EE->output->show_user_error(
				'general', 
				array(lang('mbr_you_are_registered'))
			);
		}

		// Fetch the registration form
		$reg_form = $this->_load_element('registration_form');

		// Do we have custom fields to show?
		$query = $this->EE->db->where('m_field_reg', 'y')
			->order_by('m_field_order')
			->get('member_fields');

		// If not, we'll kill the custom field variables from the template
		if ($query->num_rows() == 0)
		{
			$reg_form = preg_replace("/{custom_fields}.*?{\/custom_fields}/s", "", $reg_form);
		}
		else
		{
			// Parse custom field data

			// First separate the chunk between the {custom_fields} variable pairs.
			$field_chunk = (preg_match("/{custom_fields}(.*?){\/custom_fields}/s", 
							$reg_form, $match)) ? $match['1'] : '';

			// Next, separate the chunck between the {required} variable pairs
			$req_chunk	= (preg_match("/{required}(.*?){\/required}/s", $field_chunk, $match)) ? $match['1'] : '';

			// Loop through the query result
			$str = '';

			foreach ($query->result_array() as $row)
			{
				$field  = '';
				$temp	= $field_chunk;

				// Replace {field_name}
				$temp = str_replace("{field_name}", $row['m_field_label'], $temp);

				if ($row['m_field_description'] == '')
				{
					$temp = preg_replace("/{if field_description}.+?{\/if}/s", "", $temp); 
				}
				else
				{
					$temp = preg_replace("/{if field_description}(.+?){\/if}/s", "\\1", $temp); 
				}

				$temp = str_replace("{field_description}", $row['m_field_description'], $temp);

				// Replace {required} pair
				if ($row['m_field_required'] == 'y')
				{
					$temp = preg_replace("/".LD."required".RD.".*?".LD."\/required".RD."/s", $req_chunk, $temp);
				}
				else
				{
					$temp = preg_replace("/".LD."required".RD.".*?".LD."\/required".RD."/s", '', $temp);
				}

				// Parse input fields

				// Set field width
				if (strpos($row['m_field_width'], 'px') === FALSE && 
					strpos($row['m_field_width'], '%') === FALSE)
				{
					$width = $row['m_field_width'].'px';
				}
				else
				{
					$width = $row['m_field_width'];
				}

				//  Textarea fields
				if ($row['m_field_type'] == 'textarea')
				{
					$rows = ( ! isset($row['m_field_ta_rows'])) ? '10' : $row['m_field_ta_rows'];

					$field = "<textarea style=\"width:{$width};\" name=\"m_field_id_".$row['m_field_id']."\"  cols='50' rows='{$rows}' class=\"textarea\" ></textarea>";
				}
				else
				{
					//  Text fields
					if ($row['m_field_type'] == 'text')
					{
						$maxlength = ($row['m_field_maxl'] == 0) ? '100' : $row['m_field_maxl'];

						$field = "<input type=\"text\" name=\"m_field_id_".$row['m_field_id']."\" value=\"\" class=\"input\" maxlength=\"$maxlength\" size=\"40\" style=\"width:{$width};\" />";
					}
					elseif ($row['m_field_type'] == 'select')
					{
						//  Drop-down fields
						$select_list = trim($row['m_field_list_items']);

						if ($select_list != '')
						{
							$field = "<select name=\"m_field_id_".$row['m_field_id']."\" class=\"select\">";

							foreach (explode("\n", $select_list) as $v)
							{
								$v = trim($v);

								 $field .= "<option value=\"$v\">$v</option>";
							}

							 $field .= "</select>";
						}
					}
				}

				$temp = str_replace("{field}", $field, $temp);

				$str .= $temp;
			}

			// since $str may have sequences that look like PCRE backreferences, 
			// the two choices are to escape them and use preg_replace() or to 
			// match the pattern and use str_replace().  This way happens 
			// to be faster in this case.
			if (preg_match("/".LD."custom_fields".RD.".*?".LD."\/custom_fields".RD."/s", 
						   $reg_form, $match))
			{
				$reg_form = str_replace($match[0], $str, $reg_form);	
			}
		}

		// {if captcha}
		if (preg_match("/{if captcha}(.+?){\/if}/s", $reg_form, $match))
		{
			if ($this->EE->config->item('use_membership_captcha') == 'y')
			{
				$reg_form = preg_replace("/{if captcha}.+?{\/if}/s", $match['1'], $reg_form);

				// Bug fix.  Deprecate this later..
				$reg_form = str_replace('{captcha_word}', '', $reg_form);

				if ( ! class_exists('Template'))
				{
					$reg_form = preg_replace("/{captcha}/", $this->EE->functions->create_captcha(), $reg_form);
				}
			}
			else
			{
				$reg_form = preg_replace("/{if captcha}.+?{\/if}/s", "", $reg_form); 
			}
		}

		$un_min_len = str_replace("%x", $this->EE->config->item('un_min_len'), 
									lang('mbr_username_length'));
		$pw_min_len = str_replace("%x", $this->EE->config->item('pw_min_len'), 
									lang('mbr_password_length'));

		// Time format selection menu
		$tf = "<select name='time_format' class='select'>\n";
		$tf .= "<option value='us'>".lang('united_states')."</option>\n";
		$tf .= "<option value='eu'>".lang('european')."</option>\n";
		$tf .= "</select>\n";

		// Parse languge lines
		$reg_form = $this->_var_swap($reg_form,
									array(
											'lang:username_length'	=> $un_min_len,
											'lang:password_length'	=> $pw_min_len,
											'form:localization'		=> $this->EE->localize->timezone_menu('UTC'),
											'form:time_format'		=> $tf,
											'form:language'			=> $this->EE->functions->language_pack_names('english')

										)
									);

		// Generate Form declaration
		$data['hidden_fields'] = array(
										'ACT'	=> $this->EE->functions->fetch_action_id('Member', 'register_member'),
										'RET'	=> $this->EE->functions->fetch_site_index(),
										'FROM'	=> ($this->in_forum == TRUE) ? 'forum' : '',
									  );

		if ($this->in_forum === TRUE)
		{
			$data['hidden_fields']['board_id'] = $this->board_id;
		}

		$data['id']	= 'register_member_form';

		// Return the final rendered form
		return $this->EE->functions->form_declaration($data).$reg_form."\n"."</form>";
	}

	// --------------------------------------------------------------------

	/**
	 * Register Member
	 */
	public function register_member()
	{
		// Do we allow new member registrations?
		if ($this->EE->config->item('allow_member_registration') == 'n')
		{
			return FALSE;
		}

		// Is user banned?
		if ($this->EE->session->userdata('is_banned') === TRUE)
		{
			return $this->EE->output->show_user_error(
				'general', 
				array(lang('not_authorized'))
			);
		}

		// Blacklist/Whitelist Check
		if ($this->EE->blacklist->blacklisted == 'y' && 
			$this->EE->blacklist->whitelisted == 'n')
		{
			return $this->EE->output->show_user_error(
				'general', 
				array(lang('not_authorized'))
			);
		}

		$this->EE->load->helper('url');

		// -------------------------------------------
		// 'member_member_register_start' hook.
		//  - Take control of member registration routine
		//  - Added EE 1.4.2
		//
			$edata = $this->EE->extensions->call('member_member_register_start');
			if ($this->EE->extensions->end_script === TRUE) return;
		//
		// -------------------------------------------

		// Set the default globals
		$default = array(
			'username', 'password', 'password_confirm', 'email', 
			'screen_name', 'url', 'location'
		);

		foreach ($default as $val)
		{
			if ( ! isset($_POST[$val])) $_POST[$val] = '';
		}

		if ($_POST['screen_name'] == '')
		{
			$_POST['screen_name'] = $_POST['username'];			
		}

		// Instantiate validation class
		if ( ! class_exists('EE_Validate'))
		{
			require APPPATH.'libraries/Validate.php';
		}
		
		$this->EE->load->helper('string');

		$VAL = new EE_Validate(array(
			'member_id'			=> '',
			'val_type'			=> 'new', // new or update
			'fetch_lang' 		=> TRUE,
			'require_cpw' 		=> FALSE,
		 	'enable_log'		=> FALSE,
			'username'			=> trim_nbs($_POST['username']),
			'cur_username'		=> '',
			'screen_name'		=> trim_nbs($_POST['screen_name']),
			'cur_screen_name'	=> '',
			'password'			=> $_POST['password'],
		 	'password_confirm'	=> $_POST['password_confirm'],
		 	'cur_password'		=> '',
		 	'email'				=> trim($_POST['email']),
		 	'cur_email'			=> ''
		 ));

		$VAL->validate_username();
		$VAL->validate_screen_name();
		$VAL->validate_password();
		$VAL->validate_email();

		// Do we have any custom fields?
		$query = $this->EE->db->select('m_field_id, m_field_name, m_field_label, m_field_type, m_field_list_items, m_field_required')
							  ->where('m_field_reg', 'y')
							  ->get('member_fields');

		$cust_errors = array();
		$cust_fields = array();

		if ($query->num_rows() > 0)
		{
			foreach ($query->result_array() as $row)
			{
				$field_name = 'm_field_id_'.$row['m_field_id'];

				// Assume we're going to save this data, unless it's empty to begin with
				$valid = isset($_POST[$field_name]) && $_POST[$field_name] != '';

				// Basic validations
				if ($row['m_field_required'] == 'y' && ! $valid)
				{
					$cust_errors[] = lang('mbr_field_required').'&nbsp;'.$row['m_field_label'];
				}				
				elseif ($row['m_field_type'] == 'select' && $valid)
				{
					// Ensure their selection is actually a valid choice
					$options = explode("\n", $row['m_field_list_items']);
					
					if (! in_array($_POST[$field_name], $options))
					{
						$valid = FALSE;
						$cust_errors[] = lang('mbr_field_invalid').'&nbsp;'.$row['m_field_label'];
					}
				}				
				
				if ($valid)
				{
					$cust_fields[$field_name] = $this->EE->security->xss_clean($_POST[$field_name]);
				}
			}
		}

		if (isset($_POST['email_confirm']) && $_POST['email'] != $_POST['email_confirm'])
		{
			$cust_errors[] = lang('mbr_emails_not_match');
		}

		if ($this->EE->config->item('use_membership_captcha') == 'y')
		{
			if ( ! isset($_POST['captcha']) OR $_POST['captcha'] == '')
			{
				$cust_errors[] = lang('captcha_required');
			}
		}

		if ($this->EE->config->item('require_terms_of_service') == 'y')
		{
			if ( ! isset($_POST['accept_terms']))
			{
				$cust_errors[] = lang('mbr_terms_of_service_required');
			}
		}

 
		// -------------------------------------------
		// 'member_member_register_errors' hook.
		//  - Additional error checking prior to submission
		//  - Added EE 2.5.0
		//
			$this->EE->extensions->call('member_member_register_errors', $this);
			if ($this->EE->extensions->end_script === TRUE) return;
		//
		// -------------------------------------------
 
		$errors = array_merge($VAL->errors, $cust_errors, $this->errors);

		// Display error is there are any
		if (count($errors) > 0)
		{
			return $this->EE->output->show_user_error('submission', $errors);
		}

		// Do we require captcha?
		if ($this->EE->config->item('use_membership_captcha') == 'y')
		{
			$query = $this->EE->db->query("SELECT COUNT(*) AS count FROM exp_captcha WHERE word='".$this->EE->db->escape_str($_POST['captcha'])."' AND ip_address = '".$this->EE->input->ip_address()."' AND date > UNIX_TIMESTAMP()-7200");

			if ($query->row('count')  == 0)
			{
				return $this->EE->output->show_user_error('submission', array(lang('captcha_incorrect')));
			}

			$this->EE->db->query("DELETE FROM exp_captcha WHERE (word='".$this->EE->db->escape_str($_POST['captcha'])."' AND ip_address = '".$this->EE->input->ip_address()."') OR date < UNIX_TIMESTAMP()-7200");
		}

		// Secure Mode Forms?
		if ($this->EE->config->item('secure_forms') == 'y')
		{
			$query = $this->EE->db->query("SELECT COUNT(*) AS count FROM exp_security_hashes WHERE hash='".$this->EE->db->escape_str($_POST['XID'])."' AND ip_address = '".$this->EE->input->ip_address()."' AND ip_address = '".$this->EE->input->ip_address()."' AND date > UNIX_TIMESTAMP()-7200");

			if ($query->row('count')  == 0)
			{
				return $this->EE->output->show_user_error('general', array(lang('not_authorized')));
			}

			$this->EE->db->query("DELETE FROM exp_security_hashes WHERE (hash='".$this->EE->db->escape_str($_POST['XID'])."' AND ip_address = '".$this->EE->input->ip_address()."') OR date < UNIX_TIMESTAMP()-7200");
		}
		
		$this->EE->load->helper('security');
		
		// Assign the base query data
		$data = array(
			'username'		=> trim_nbs($this->EE->input->post('username')),
			'password'		=> do_hash($_POST['password']),
			'ip_address'	=> $this->EE->input->ip_address(),
			'unique_id'		=> $this->EE->functions->random('encrypt'),
			'join_date'		=> $this->EE->localize->now,
			'email'			=> trim_nbs($this->EE->input->post('email')),
			'screen_name'	=> trim_nbs($this->EE->input->post('screen_name')),
			'url'			=> prep_url($this->EE->input->post('url')),
			'location'		=> $this->EE->input->post('location'),

			// overridden below if used as optional fields
			'language'		=> ($this->EE->config->item('deft_lang')) ? 
									$this->EE->config->item('deft_lang') : 'english',
			'time_format'	=> ($this->EE->config->item('time_format')) ? 
									$this->EE->config->item('time_format') : 'us',
			'timezone'		=> ($this->EE->config->item('default_site_timezone') && 
								$this->EE->config->item('default_site_timezone') != '') ? 
									$this->EE->config->item('default_site_timezone') : $this->EE->config->item('server_timezone'),
			'daylight_savings' => ($this->EE->config->item('default_site_dst') && 
									$this->EE->config->item('default_site_dst') != '') ? 
										$this->EE->config->item('default_site_dst') : $this->EE->config->item('daylight_savings')	
		);
		
		// Set member group

		if ($this->EE->config->item('req_mbr_activation') == 'manual' OR 
			$this->EE->config->item('req_mbr_activation') == 'email')
		{
			$data['group_id'] = 4;  // Pending
		}
		else
		{
			if ($this->EE->config->item('default_member_group') == '')
			{
				$data['group_id'] = 4;  // Pending
			}
			else
			{
				$data['group_id'] = $this->EE->config->item('default_member_group');
			}
		}
		
		// Optional Fields

		$optional = array(
			'bio'			=> 'bio',
			'language'		=> 'deft_lang',
			'timezone'		=> 'server_timezone',
			'time_format'	=> 'time_format'
		);

		foreach($optional as $key => $value)
		{
			if (isset($_POST[$value]))
			{
				$data[$key] = $_POST[$value];
			}
		}

		if ($this->EE->input->post('daylight_savings') == 'y')
		{
			$data['daylight_savings'] = 'y';
		}
		elseif ($this->EE->input->post('daylight_savings') == 'n')
		{
			$data['daylight_savings'] = 'n';
		}
		
		// We generate an authorization code if the member needs to self-activate
		if ($this->EE->config->item('req_mbr_activation') == 'email')
		{
			$data['authcode'] = $this->EE->functions->random('alnum', 10);
		}

		// Insert basic member data
		$this->EE->db->query($this->EE->db->insert_string('exp_members', $data));

		$member_id = $this->EE->db->insert_id();

		// Insert custom fields
		$cust_fields['member_id'] = $member_id;

		$this->EE->db->query($this->EE->db->insert_string('exp_member_data', $cust_fields));


		// Create a record in the member homepage table
		// This is only necessary if the user gains CP access, 
		// but we'll add the record anyway.

		$this->EE->db->query($this->EE->db->insert_string('exp_member_homepage', 
								array('member_id' => $member_id)));

		// Mailinglist Subscribe
		$mailinglist_subscribe = FALSE;

		if (isset($_POST['mailinglist_subscribe']) && is_numeric($_POST['mailinglist_subscribe']))
		{
			// Kill duplicate emails from authorizatin queue.
			$this->EE->db->where('email', $_POST['email'])
						 ->delete('mailing_list_queue');

			// Validate Mailing List ID
			$query = $this->EE->db->select('COUNT(*) as count')
								  ->where('list_id', $_POST['mailinglist_subscribe'])
								  ->get('mailing_lists');

			// Email Not Already in Mailing List
			$results = $this->EE->db->select('COUNT(*) as count')
									->where('email', $_POST['email'])
									->where('list_id', $_POST['mailinglist_subscribe'])
									->get('mailing_list');

			// INSERT Email
			if ($query->row('count')  > 0 && $results->row('count')  == 0)
			{
				$mailinglist_subscribe = TRUE;

				$code = $this->EE->functions->random('alnum', 10);

				if ($this->EE->config->item('req_mbr_activation') == 'email')
				{
					// Activated When Membership Activated
					$this->EE->db->query("INSERT INTO exp_mailing_list_queue (email, list_id, authcode, date)
								VALUES ('".$this->EE->db->escape_str($_POST['email'])."', '".$this->EE->db->escape_str($_POST['mailinglist_subscribe'])."', '".$code."', '".time()."')");
				}
				elseif ($this->EE->config->item('req_mbr_activation') == 'manual')
				{
					// Mailing List Subscribe Email
					$this->EE->db->query("INSERT INTO exp_mailing_list_queue (email, list_id, authcode, date)
								VALUES ('".$this->EE->db->escape_str($_POST['email'])."', '".$this->EE->db->escape_str($_POST['mailinglist_subscribe'])."', '".$code."', '".time()."')");

					$this->EE->lang->loadfile('mailinglist');
					$action_id  = $this->EE->functions->fetch_action_id('Mailinglist', 'authorize_email');

					$swap = array(
									'activation_url'	=> $this->EE->functions->fetch_site_index(0, 0).QUERY_MARKER.'ACT='.$action_id.'&id='.$code,
									'site_name'			=> stripslashes($this->EE->config->item('site_name')),
									'site_url'			=> $this->EE->config->item('site_url')
								 );

					$template = $this->EE->functions->fetch_email_template('mailinglist_activation_instructions');
					$email_tit = $this->EE->functions->var_swap($template['title'], $swap);
					$email_msg = $this->EE->functions->var_swap($template['data'], $swap);

					// Send email
					$this->EE->load->library('email');
					$this->EE->email->wordwrap = true;
					$this->EE->email->mailtype = 'plain';
					$this->EE->email->priority = '3';

					$this->EE->email->from($this->EE->config->item('webmaster_email'), $this->EE->config->item('webmaster_name'));
					$this->EE->email->to($_POST['email']);
					$this->EE->email->subject($email_tit);
					$this->EE->email->message($email_msg);
					$this->EE->email->send();
				}
				else
				{
					// Automatically Accepted
					$this->EE->db->query("INSERT INTO exp_mailing_list (list_id, authcode, email, ip_address)
										  VALUES ('".$this->EE->db->escape_str($_POST['mailinglist_subscribe'])."', '".$code."', '".$this->EE->db->escape_str($_POST['email'])."', '".$this->EE->db->escape_str($this->EE->input->ip_address())."')");
				}
			}
		}

		// Update
		if ($this->EE->config->item('req_mbr_activation') == 'none')
		{
			$this->EE->stats->update_member_stats();
		}

		// Send admin notifications
		if ($this->EE->config->item('new_member_notification') == 'y' && 
			$this->EE->config->item('mbr_notification_emails') != '')
		{
			$name = ($data['screen_name'] != '') ? $data['screen_name'] : $data['username'];

			$swap = array(
							'name'					=> $name,
							'site_name'				=> stripslashes($this->EE->config->item('site_name')),
							'control_panel_url'		=> $this->EE->config->item('cp_url'),
							'username'				=> $data['username'],
							'email'					=> $data['email']
						 );

			$template = $this->EE->functions->fetch_email_template('admin_notify_reg');
			$email_tit = $this->_var_swap($template['title'], $swap);
			$email_msg = $this->_var_swap($template['data'], $swap);

			$this->EE->load->helper('string');

			// Remove multiple commas
			$notify_address = reduce_multiples($this->EE->config->item('mbr_notification_emails'), ',', TRUE);

			// Send email
			$this->EE->load->helper('text');

			$this->EE->load->library('email');
			$this->EE->email->wordwrap = true;
			$this->EE->email->from($this->EE->config->item('webmaster_email'), $this->EE->config->item('webmaster_name'));
			$this->EE->email->to($notify_address);
			$this->EE->email->subject($email_tit);
			$this->EE->email->message(entities_to_ascii($email_msg));
			$this->EE->email->Send();
		}

		// -------------------------------------------
		// 'member_member_register' hook.
		//  - Additional processing when a member is created through the User Side
		//  - $member_id added in 2.0.1
		//
			$edata = $this->EE->extensions->call('member_member_register', $data, $member_id);
			if ($this->EE->extensions->end_script === TRUE) return;
		//
		// -------------------------------------------

		// Send user notifications
		if ($this->EE->config->item('req_mbr_activation') == 'email')
		{
			$action_id  = $this->EE->functions->fetch_action_id('Member', 'activate_member');

			$name = ($data['screen_name'] != '') ? $data['screen_name'] : $data['username'];

			$board_id = ($this->EE->input->get_post('board_id') !== FALSE && is_numeric($this->EE->input->get_post('board_id'))) ? $this->EE->input->get_post('board_id') : 1;

			$forum_id = ($this->EE->input->get_post('FROM') == 'forum') ? '&r=f&board_id='.$board_id : '';

			$add = ($mailinglist_subscribe !== TRUE) ? '' : '&mailinglist='.$_POST['mailinglist_subscribe'];

			$swap = array(
				'name'				=> $name,
				'activation_url'	=> $this->EE->functions->fetch_site_index(0, 0).QUERY_MARKER.'ACT='.$action_id.'&id='.$data['authcode'].$forum_id.$add,
				'site_name'			=> stripslashes($this->EE->config->item('site_name')),
				'site_url'			=> $this->EE->config->item('site_url'),
				'username'			=> $data['username'],
				'email'				=> $data['email']
			 );

			$template = $this->EE->functions->fetch_email_template('mbr_activation_instructions');
			$email_tit = $this->_var_swap($template['title'], $swap);
			$email_msg = $this->_var_swap($template['data'], $swap);

			// Send email
			$this->EE->load->helper('text');

			$this->EE->load->library('email');
			$this->EE->email->wordwrap = true;
			$this->EE->email->from($this->EE->config->item('webmaster_email'), $this->EE->config->item('webmaster_name'));
			$this->EE->email->to($data['email']);
			$this->EE->email->subject($email_tit);
			$this->EE->email->message(entities_to_ascii($email_msg));
			$this->EE->email->Send();

			$message = lang('mbr_membership_instructions_email');
		}
		elseif ($this->EE->config->item('req_mbr_activation') == 'manual')
		{
			$message = lang('mbr_admin_will_activate');
		}
		else
		{
			// Log user in (the extra query is a little annoying)
			$this->EE->load->library('auth');
			$member_data_q = $this->EE->db->get_where('members', array('member_id' => $member_id));
			
			$incoming = new Auth_result($member_data_q->row());
			$incoming->remember_me(60*60*24*182);
			$incoming->start_session();

			$message = lang('mbr_your_are_logged_in');
		}

		// Build the message
		if ($this->EE->input->get_post('FROM') == 'forum')
		{
			$query = $this->_do_form_query();

			$site_name	= $query->row('board_label') ;
			$return		= $query->row('board_forum_url') ;
		}
		else
		{
			$site_name = ($this->EE->config->item('site_name') == '') ? lang('back') : stripslashes($this->EE->config->item('site_name'));
			$return = $this->EE->config->item('site_url');
		}

		$data = array(
			'title' 	=> lang('mbr_registration_complete'),
			'heading'	=> lang('thank_you'),
			'content'	=> lang('mbr_registration_completed')."\n\n".$message,
			'redirect'	=> '',
			'link'		=> array($return, $site_name)
		);

		$this->EE->output->show_message($data);
	}

	// --------------------------------------------------------------------

	private function _do_form_query()
	{
		if ($this->EE->input->get_post('board_id') !== FALSE && 
			is_numeric($this->EE->input->get_post('board_id')))
		{
			return $this->EE->db->select('board_forum_url, board_id, board_label')
								->where('board_id', (int) $this->EE->input->get_post('board_id'))
								->get('forum_boards');
		}

		return $this->EE->db->select('board_forum_url, board_id, board_label')
							->where('board_id', 1)
							->get('forum_boards');
	}

	// --------------------------------------------------------------------

	/**
	 * Member Self-Activation
	 */
	public function activate_member()
	{
		// Fetch the site name and URL
		if ($this->EE->input->get_post('r') == 'f')
		{
			$query = $this->_do_form_query();

			$site_name	= $query->row('board_label') ;
			$return		= $query->row('board_forum_url') ;
		}
		else
		{
			$return 	= $this->EE->functions->fetch_site_index();
			$site_name 	= ($this->EE->config->item('site_name') == '') ? lang('back') : stripslashes($this->EE->config->item('site_name'));
		}

		// No ID?  Tisk tisk...
		$id  = $this->EE->input->get_post('id');

		if ($id == FALSE)
		{

			$data = array(	'title' 	=> lang('mbr_activation'),
							'heading'	=> lang('error'),
							'content'	=> lang('invalid_url'),
							'link'		=> array($return, $site_name)
						 );

			$this->EE->output->show_message($data);
		}

		// Set the member group
		$group_id = $this->EE->config->item('default_member_group');

		// Is there even a Pending (group 4) account for this particular user?
		$query = $this->EE->db->select('member_id, group_id, email')
							  ->where('group_id', 4)
							  ->where('authcode', $id)
							  ->get('members');

		if ($query->num_rows() == 0)
		{
			$data = array(	'title' 	=> lang('mbr_activation'),
							'heading'	=> lang('error'),
							'content'	=> lang('mbr_problem_activating'),
							'link'		=> array($return, $site_name)
						 );

			$this->EE->output->show_message($data);
		}

		$member_id = $query->row('member_id');

		if ($this->EE->input->get_post('mailinglist') !== FALSE && 
			is_numeric($this->EE->input->get_post('mailinglist')))
		{
			$expire = time() - (60*60*48);

			$this->EE->db->query("DELETE FROM exp_mailing_list_queue WHERE date < '$expire' ");

			$results = $this->EE->db->query("SELECT authcode
									FROM exp_mailing_list_queue
									WHERE email = '".$this->EE->db->escape_str($query->row('email') )."'
									AND list_id = '".$this->EE->db->escape_str($this->EE->input->get_post('mailinglist'))."'");

			$this->EE->db->query("INSERT INTO exp_mailing_list (list_id, authcode, email, ip_address)
								 VALUES ('".$this->EE->db->escape_str($this->EE->input->get_post('mailinglist'))."', '".$this->EE->db->escape_str($results->row('authcode') )."', '".$this->EE->db->escape_str($query->row('email') )."', '".$this->EE->db->escape_str($this->EE->input->ip_address())."')");

			$this->EE->db->query("DELETE FROM exp_mailing_list_queue WHERE authcode = '".$this->EE->db->escape_str($results->row('authcode') )."'");
		}

		// If the member group hasn't been switched we'll do it.

		if ($query->row('group_id')  != $group_id)
		{
			$this->EE->db->query("UPDATE exp_members SET group_id = '".$this->EE->db->escape_str($group_id)."' WHERE authcode = '".$this->EE->db->escape_str($id)."'");
		}

		$this->EE->db->query("UPDATE exp_members SET authcode = '' WHERE authcode = '$id'");

		// -------------------------------------------
		// 'member_register_validate_members' hook.
		//  - Additional processing when member(s) are self validated
		//  - Added 1.5.2, 2006-12-28
		//  - $member_id added 1.6.1
		//
			$edata = $this->EE->extensions->call('member_register_validate_members', $member_id);
			if ($this->EE->extensions->end_script === TRUE) return;
		//
		// -------------------------------------------

		// Upate Stats

		$this->EE->stats->update_member_stats();

		// Show success message
		$data = array(	'title' 	=> lang('mbr_activation'),
						'heading'	=> lang('thank_you'),
						'content'	=> lang('mbr_activation_success')."\n\n".lang('mbr_may_now_log_in'),
						'link'		=> array($return, $site_name)
					 );

		$this->EE->output->show_message($data);
	}
}
// END CLASS

/* End of file mod.member_register.php */
/* Location: ./system/expressionengine/modules/member/mod.member_register.php */