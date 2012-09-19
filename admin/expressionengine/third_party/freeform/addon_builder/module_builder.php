<?php if ( ! defined('EXT')) exit('No direct script access allowed');
 
 /**
 * Solspace - Add-On Builder Framework
 *
 * @package		Add-On Builder Framework
 * @author		Solspace DevTeam
 * @copyright	Copyright (c) 2008-2011, Solspace, Inc.
 * @link		http://solspace.com/docs/
 * @version		1.2.3
 */
 
 /**
 * Module Builder
 *
 * A class that helps with the building of ExpressionEngine Modules by allowing Bridge enabled modules
 * to be extensions of this class and thus gain all of the abilities of it and its parents.
 *
 * @package 	Add-On Builder Framework
 * @subpackage	Add-On Builder
 * @author		Solspace DevTeam
 * @link		http://solspace.com/docs/
 */

if ( ! class_exists('Addon_builder_freeform'))
{
	require_once 'addon_builder.php';	
}

class Module_builder_freeform extends Addon_builder_freeform 
{

	public $module_actions		= array();
	public $hooks				= array();
	
	// Defaults for the exp_extensions fields
	public $extension_defaults	= array();	
	
	public $base				= '';
    
    // --------------------------------------------------------------------

	/**
	 * Constructor
	 *
	 * @access	public
	 * @return	null
	 */
    
	public function Module_builder_freeform($name = '')
	{		
		parent::Addon_builder_freeform($name);

		// --------------------------------------------
		//  Default CP Variables
		// --------------------------------------------

		if (REQ == 'CP')
		{
			//BASE is not set until AFTER sessions_end, and we don't want to clobber it.
			$base_const = defined('BASE') ? BASE :  SELF . '?S=0';
			
			//2.x adds an extra param for base
			if ( ! (APP_VER < 2.0) && substr($base_const, -4) != 'D=cp')
			{
				$base_const .= '&amp;D=cp';
			}
		
			// For 2.0, we have '&amp;D=cp' with BASE and 
			//	we want pure characters, so we convert it
			$this->base	= (APP_VER < 2.0) ? 
				$base_const . '&C=modules&M=' . $this->lower_name : 
				str_replace('&amp;', '&', $base_const) . 
					'&C=addons_modules&M=show_module_cp&module=' . $this->lower_name;
			
			$this->cached_vars['page_crumb']			= '';
			$this->cached_vars['page_title']			= '';
			$this->cached_vars['base_uri']				= $this->base;
			
			$this->cached_vars['onload_events']  		= '';
			
			$this->cached_vars['module_menu']			= array();
			$this->cached_vars['module_menu_highlight'] = 'module_home';
			$this->cached_vars['module_version'] 		= $this->version;
			
			// --------------------------------------------
			//  Default Crumbs for Module
			// --------------------------------------------

			if (APP_VER < 2.0)
			{
				$this->add_crumb(
					ee()->config->item('site_name'), 
					$base_const
				);
				
				$this->add_crumb(
					ee()->lang->line('modules'), 
					$base_const . AMP . 'C=modules'
				);
			}
			
			$this->add_crumb(
				ee()->lang->line($this->lower_name.'_module_name'), 
				$this->base
			);
		}
		
		// --------------------------------------------
        //  Module Installed and Up to Date?
        // --------------------------------------------
        
        if (REQ == 'PAGE' AND 
			constant(strtoupper($this->lower_name).'_VERSION') !== NULL AND 
			($this->database_version() == FALSE OR 
			 $this->version_compare(
				$this->database_version(), '<', 
				constant(strtoupper($this->lower_name).'_VERSION')
			 )
			)
		 )
		{
			$this->disabled = TRUE;
			
			if (empty($this->cache['disabled_message']) AND 
				! empty(ee()->lang->language[$this->lower_name.'_module_disabled']))
			{
				trigger_error(ee()->lang->line($this->lower_name.'_module_disabled'), E_USER_NOTICE);
				
				$this->cache['disabled_message'] = TRUE;
			}
		}
	}
	// END Module_builder_freeform()
	
	
	// --------------------------------------------------------------------

	/**
	 * Module Installer
	 *
	 * @access	public
	 * @return	bool
	 */

    public function default_module_install()
    {        
        $this->install_module_sql();
        $this->update_module_actions();
       	$this->update_extension_hooks();
        
        return TRUE;
    }
	// END default_module_install()
    

	// --------------------------------------------------------------------

	/**
	 * Module Uninstaller
	 *
	 * Looks for an db.[module].sql file as well as the old [module].sql file in the module's folder
	 *
	 * @access	public
	 * @return	bool
	 */

    public function default_module_uninstall()
    {
        $query = ee()->db->query(
			"SELECT module_id 
			 FROM 	exp_modules 
			 WHERE 	module_name = '" . ee()->db->escape_str($this->class_name) . "'"
		);
		
		$files = array($this->addon_path . $this->lower_name.'.sql', 
					   $this->addon_path . 'db.'.$this->lower_name.'.sql');
        
        foreach($files as $file)
        {
			if (file_exists($file))
			{
				if (preg_match_all(
					"/CREATE\s+TABLE\s+IF\s+NOT\s+EXISTS\s+`([^`]+)`/", 
					file_get_contents($file), 
					$matches)
				)
				{
					foreach($matches[1] as $table)
					{
						$sql[] = "DROP TABLE IF EXISTS `".ee()->db->escape_str($table)."`";
					}
				}
				
				break;
			}
		}
		
		$sql[] = "DELETE FROM 	exp_module_member_groups 
				  WHERE 		module_id = '" . $query->row('module_id') . "'";
				
        $sql[] = "DELETE FROM 	exp_modules 
				  WHERE 		module_name = '" . 
					ee()->db->escape_str($this->class_name) . "'";
        
		$sql[] = "DELETE FROM 	exp_actions 
				  WHERE 		class = '" . ee()->db->escape_str($this->class_name) . "'";
    
        foreach ($sql as $query)
        {
            ee()->db->query($query);
        }
        
        $this->remove_extension_hooks();

        return TRUE;
    }
    // END default_module_uninstall()
    

	// --------------------------------------------------------------------

	/**
	 * Module Update
	 *
	 * @access	public
	 * @return	bool
	 */

    public function default_module_update()
    {
        $this->update_module_actions();
    	$this->update_extension_hooks();
    	
    	unset($this->cache['database_version']);

        return TRUE;
    }
    // END default_module_update()

    
	// --------------------------------------------------------------------

	/**
	 * Install Module SQL
	 *
	 * Looks for an db.[module].sql file as well as the old [module].sql file in the module's folder
	 *
	 * @access	public
	 * @return	null
	 */
	
	public function install_module_sql()
	{
		$sql = array();
	
		// --------------------------------------------
        //  Our Install Queries
        // --------------------------------------------
        
        $files = array($this->addon_path . $this->lower_name.'.sql', 
					   $this->addon_path . 'db.'.$this->lower_name.'.sql');
					   
		foreach($files as $file)
		{
			if (file_exists($file))
			{  
				$sql = preg_split(
					"/;;\s*(\n+|$)/", 
					file_get_contents($file), 
					-1, 
					PREG_SPLIT_NO_EMPTY
				);
				
				foreach($sql as $i => $query)
				{
					$sql[$i] = trim($query);
				}
				
				break;
			}
		}
		
		// --------------------------------------------
        //  Module Install
        // --------------------------------------------
		
        foreach ($sql as $query)
        {
            ee()->db->query($query);
        }
	}
	//END install_module_sql()
	    
	
	// --------------------------------------------------------------------

	/**
	 * Module Actions
	 *
	 * Insures that we have all of the correct actions in the database for this module
	 *
	 * @access	public
	 * @return	array
	 */

	public function update_module_actions()
    {   	
    	$exists	= array();
    	
    	$query	= ee()->db->query(
			"SELECT method 
			 FROM 	exp_actions 
			 WHERE 	class = '" . ee()->db->escape_str($this->class_name) . "'" 
		);
    	
    	foreach ( $query->result_array() AS $row )
    	{
    		$exists[] = $row['method'];
    	}
    	
    	// --------------------------------------------
        //  Actions of Module Actions
        // --------------------------------------------
        
        $actions = ( is_array($this->actions) AND 
					 count($this->actions) > 0) ? 
						$this->actions : 
						$this->module_actions;
    	
    	// --------------------------------------------
        //  Add Missing Actions
        // --------------------------------------------
    	
    	foreach(array_diff($actions, $exists) as $method)
    	{
    		ee()->db->query(
				ee()->db->insert_string(
					'exp_actions', 
					array(	
						'class'		=> $this->class_name,
						'method'	=> $method
					)
				)
			);
    	}
    	
    	// --------------------------------------------
        //  Delete No Longer Existing Actions
        // --------------------------------------------
    	
    	foreach(array_diff($exists, $actions) as $method)
    	{
    		ee()->db->query(
				"DELETE FROM 	exp_actions 
				 WHERE 		 	class = '" . ee()->db->escape_str($this->class_name) . "' 
    			 AND 			method = '" . ee()->db->escape_str($method) . "'"
			);
    	}
    }
    // END update_module_actions()
    
	
	// --------------------------------------------------------------------

	/**
	 * Install/Update Our Extension for Module
	 *
	 * Tells ExpressionEngine what extension hooks 
	 * we wish to use for this module.  If an extension
	 * is part of a module, then it is the module's class 
	 * name with the '_extension' (1.x) or '_ext' 2.x
	 * suffix added on to it. 
	 *
	 * @access	public
	 * @return	null
	 */

	public function update_extension_hooks()
    {	
    	if ( ! is_array($this->hooks) OR 
			 count($this->hooks) == 0)
    	{
    		return TRUE;
    	}
    	
    	// --------------------------------------------
        //  First, Upgrade any EE 1.x Hooks to EE 2.x Format
		//	we are also re-enabling these because they get
		//	auto turned off on update to EE 2.0
        // --------------------------------------------
        
        if (APP_VER >= 2.0)
        {
        	ee()->db->query(
				ee()->db->update_string( 
					'exp_extensions', 
					array(
						'class' 	=> $this->extension_name,
						'enabled' 	=> 'y'
					), 
					array(	
						'class' 	=> $this->class_name . '_extension'
					)
				)
			);
        }
    	
    	// --------------------------------------------
        //  Determine Existing Methods
        // --------------------------------------------
    	
    	$exists	= array();
    	
    	$query	= ee()->db->query( 
			"SELECT method 
			 FROM 	exp_extensions 
			 WHERE 	class = '" . ee()->db->escape_str($this->extension_name) . "'"
		);
    	
    	foreach ( $query->result_array() AS $row )
    	{
    		$exists[] = $row['method'];
    	}
    	
    	// --------------------------------------------
        //  Extension Table Defaults
        // --------------------------------------------
        
		$this->extension_defaults = array(	
			'class'        => $this->extension_name,
			'settings'     => '',			
			'priority'     => 10,                                        				
			'version'      => $this->version,               				
			'enabled'      => 'y'                                        				
		);
    	
    	// --------------------------------------------
        //  Find Missing and Insert
        // --------------------------------------------
        
        $current_methods = array();
    	
    	foreach($this->hooks as $data)
    	{
    		// Default exp_extension fields, overwrite with any from array
    		$data = array_merge($this->extension_defaults, $data);
    	
    		$current_methods[] = $data['method'];
    	
    		if ( ! in_array($data['method'], $exists))
    		{
				ee()->db->query(
					ee()->db->insert_string(
						'exp_extensions', 
						$data
					)
				);
    		}
    		else
    		{
    			unset($data['settings']);
    			
    			ee()->db->query( 
					ee()->db->update_string( 
						'exp_extensions', 
						$data, 
						array(	
							'class' 	=> $data['class'], 
							'method' 	=> $data['method']
						)
					)
				);
    		}
    	}
    	
    	// --------------------------------------------
        //  Remove Old Hooks
        // --------------------------------------------
    	
    	foreach(array_diff($exists, $current_methods) as $method)
    	{
    		ee()->db->query(
				"DELETE FROM 	exp_extensions 
				 WHERE 			class = '" . ee()->db->escape_str($this->extension_name) . "' 
				 AND 			method = '" . ee()->db->escape_str($method) . "'"
			);
    	}
    }
    // END update_extension_hooks()
	
	
	// --------------------------------------------------------------------

	/**
	 * Remove Extension Hooks
	 *
	 * Removes all of the extension hooks that will be called for this module
	 *
	 * @access	public
	 * @return	null
	 */

	public function remove_extension_hooks()
    {
    	ee()->db->query(
			"DELETE FROM 	exp_extensions 
			 WHERE 			class = '" . 
				ee()->db->escape_str($this->extension_name) . "'"
		);
    				
    	// --------------------------------------------
        //  Remove from ee()->extensions->extensions array
        // --------------------------------------------
        
        foreach(ee()->extensions->extensions as $hook => $calls)
        {
        	foreach($calls as $priority => $class_data)
        	{
        		foreach($class_data as $class => $data)
        		{
					if ($class == $this->class_name OR 
						$class == $this->extension_name)
					{
						unset(ee()->extensions->extensions[$hook][$priority][$class]);
					}
				}
        	}
        }
    }
    // END remove_extension_hooks()
    	
	
	// --------------------------------------------------------------------
		
	/**
	 * Equalize Menu Text
	 *
	 * Goes through an array of Main Menu links and text so that we can equalize the width of the tabs.
	 * 
	 * @access	public
	 * @param	array
	 * @return	array
	 */
	
	public function equalize_menu($array = array())
	{
		$length = 1;
		
		foreach($array as $key => $data)
		{
			$length = (strlen(strip_tags($data['title'])) > $length) ? 
						strlen(strip_tags($data['title'])) : $length;
		}
		
		foreach ($array as $key => $data)
		{
			$i = ceil(($length - strlen(strip_tags($data['title'])))/2);
						
			$array[$key]['title'] = str_repeat("&nbsp;", $i) . 
										$data['title'] . 
										str_repeat("&nbsp;", $i);
		}
		
		return $array;
	}
	// END equalize_menu()
	
	// --------------------------------------------------------------------

	/**
	 * Set Encryption Key
	 *
	 * Insures that we have an encryption key set in the EE 2.x Configuration File
	 *
	 * @access	public
	 * @return	array
	 */

	public function set_encryption_key()
    {   	
    	if (APP_VER <= 2.0) return;
    	
    	if (ee()->config->item('encryption_key') != '') return;
    	
    	$config = array('encryption_key' => md5(ee()->db->username.ee()->db->password.rand()));
    	
    	ee()->config->_update_config($config);
    }
    // END set_encryption_key()
	
	// --------------------------------------------------------------------

	/**
	 * Module Specific No Results Parsing
	 *
	 * Looks for (your_module)_no_results and uses that, 
	 * otherwise it returns the default no_results conditional
	 *
	 *	@access		public
	 *	@return		string
	 */

    public function no_results()
    {
		if ( preg_match( 
				"/".LD."if " .preg_quote($this->lower_name)."_no_results" . 
					RD."(.*?)".LD.preg_quote(T_SLASH, '/')."if".RD."/s", 
				ee()->TMPL->tagdata, 
				$match 
			)
	 	)
		{
			return $match[1];
		}
		else
		{
			return ee()->TMPL->no_results();
		}
    }
    // END no_results()
    

    // ------------------------------------------------------------------------

	/**
	 * Sanitize Search Terms
	 *
	 * Filters a search string for security
	 *
	 * @access	public
	 * @param	string
	 * @return	string
	 */	
	public function sanitize_search_terms($str)
	{	
		if (APP_VER < 2.0)
		{			
			return $GLOBALS['REGX']->keyword_clean($str);
		}
		else
		{
			ee()->load->helper('search');
			
			return sanitize_search_terms($str);
		}
	}
	// END sanitize_search_terms()
}
// END Module_builder Class
