<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

 /**
 * Solspace - Freeform
 *
 * @package		Solspace:Freeform
 * @author		Solspace DevTeam
 * @copyright	Copyright (c) 2008-2012, Solspace, Inc.
 * @link		http://solspace.com/docs/addon/c/Freeform/
 * @filesource 	./system/expressionengine/third_party/freeform/libraries/
 */

 /**
 * Freeform - Migration Library
 *
 * @package 	Solspace:Freeform
 * @author		Solspace DevTeam
 * @filesource 	./system/expressionengine/third_party/freeform/libraries/Freeform_migration.php
 */

if ( ! defined('APP_VER')) define('APP_VER', '2.0'); // EE 2.0's Wizard doesn't like CONSTANTs

$__parent_folder = rtrim(realpath(rtrim(dirname(__FILE__), "/") . '/../'), '/') . '/';

if ( ! class_exists('Addon_builder_freeform'))
{
	require_once $__parent_folder . 'addon_builder/addon_builder.php';
}

unset($__parent_folder);

class Freeform_migration extends Addon_builder_freeform
{
	public $cache_path;
	public $table_suffix			= '_legacy';
	private $batch_limit			= 10;
	private $migrate_attachments	= FALSE;
	private $errors					= array();
	private $upload_pref_id_map		= array();
	private	$tables					= array(
		'exp_freeform_attachments',
		'exp_freeform_entries',
		'exp_freeform_fields',
		'exp_freeform_preferences',
		'exp_freeform_templates',
		'exp_freeform_user_email'
	);

	private	$new_has_site_id		= array(
		'exp_freeform_fields',
		'exp_freeform_preferences',
		'exp_freeform_user_email'
	);

	// --------------------------------------------------------------------

	/**
	 * Constructor
	 *
	 * @access	public
	 * @return	object this
	 */

	public function __construct()
	{
		parent::__construct('freeform');
	}
	//END __construct


	// --------------------------------------------------------------------

	/**
	 * Upgrade: migrate notification templates
	 *
	 * @access public
	 * @return boolean
	 */

	public function upgrade_notification_templates()
	{
		// -------------------------------------
		//	Map table
		// -------------------------------------

		$trans	= array(
			'wordwrap'			=> 'wordwrap',
			'html'				=> 'allow_html',
			'template_name'		=> 'notification_name',
			'template_label'	=> 'notification_label',
			'data_from_name'	=> 'from_name',
			'data_from_email'	=> 'from_email',
			'data_title'		=> 'email_subject',
			'template_data'		=> 'template_data'
		);

		// -------------------------------------
		//	Capture from legacy
		// -------------------------------------

		$query	= ee()->db->get('exp_freeform_templates' . $this->table_suffix);

		// -------------------------------------
		//	Loop for each template
		// -------------------------------------

		foreach ( $query->result_array() as $row )
		{
			$insert	= array(
				'site_id'	=> ee()->config->item('site_id')
			);

			// -------------------------------------
			//	Remap data
			// -------------------------------------

			foreach ( $trans as $key => $val )
			{
				if ( ! empty( $row[$key] ) )
				{
					$insert[$val]	= $row[$key];
				}
			}

			// -------------------------------------
			//	Reply to
			// -------------------------------------

			if ( ! empty( $insert['from_email'] ) )
			{
				$insert['reply_to_email']	= $insert['from_email'];
			}

			// -------------------------------------
			//	Enable attachments?
			// -------------------------------------

			if ( ! empty( $insert['template_data'] ) AND
				strpos( $insert['template_data'], 'attach' ) !== FALSE )
			{
				$insert['include_attachments']	= 'y';
			}

			// -------------------------------------
			//	Convert
			// -------------------------------------

			$replace	= array(
				'{all_custom_fields}'	=> '{all_form_fields_string}'
			);

			$insert	= str_replace( array_keys( $replace ), $replace, $insert );

			// -------------------------------------
			//	Insert to new table
			// -------------------------------------

			ee()->db->insert(
				'freeform_notification_templates',
				$insert
			);
		}

		// -------------------------------------
		//	Return
		// -------------------------------------

		return TRUE;
	}
	//	End upgrade: migrate notification templates


	// --------------------------------------------------------------------

	/**
	 * Uninstall
	 *
	 * @access public
	 * @return boolean
	 */

	public function uninstall()
	{
		// -------------------------------------
		//	Drop
		// -------------------------------------

		ee()->db->query(
			'DROP TABLE IF EXISTS ' .
				implode( $this->table_suffix . ',', $this->tables ) .
				$this->table_suffix
		);

		// -------------------------------------
		//	Return
		// -------------------------------------

		return TRUE;
	}
	//	End uninstall


	// --------------------------------------------------------------------

	/**
	 * Upgrade: migrate preferences
	 *
	 * @access public
	 * @return boolean
	 */

	public function upgrade_migrate_preferences()
	{
		// -------------------------------------
		//	make sure prefs legacy is there
		// -------------------------------------

		$query = ee()->db->query(
			"SHOW TABLES
			 LIKE 'exp_freeform_preferences" .
			 	ee()->db->escape_str($this->table_suffix) . "'"
		);

		if ($query->num_rows() == 0)
		{
			return TRUE;
		}

		// -------------------------------------
		//	Prefs list
		// -------------------------------------

		$prefs	= array(
			'max_user_recipients'	=> 10,
			'spam_count'			=> 30,
			'spam_interval'			=> 60
		);

		// -------------------------------------
		//	Capture from legacy
		// -------------------------------------

		$query	= ee()->db->get('exp_freeform_preferences' . $this->table_suffix);

		// -------------------------------------
		//	Push into new prefs table
		// -------------------------------------

		foreach ( $query->result_array() as $row )
		{
			if ( isset( $prefs[ $row['preference_name'] ] ) === TRUE AND
				$prefs[ $row['preference_name'] ] != $row['preference_value'] )
			{

				ee()->db->insert(
					'freeform_preferences',
					array(
						'preference_name'	=> $row['preference_name'],
						'preference_value'	=> $row['preference_value'],
						'site_id'			=> ee()->config->item('site_id')
					)
				);
			}
		}

		// -------------------------------------
		//	Return
		// -------------------------------------

		return TRUE;
	}
	//	End upgrade: migrate preferences


	// --------------------------------------------------------------------

	/**
	 * Upgrade: rename tables
	 *
	 * @access public
	 * @return boolean
	 */

	public function upgrade_rename_tables()
	{
		// -------------------------------------
		//	Loop and rename
		// -------------------------------------

		foreach ( $this->tables as $name )
		{
			if ( ee()->db->table_exists($name) === FALSE AND
				ee()->db->table_exists($name.$this->table_suffix) === TRUE )
			{
				continue;
			}
			//do both the old and new tables exist?
			//whoops...
			else if (
				ee()->db->table_exists($name) === TRUE AND
				ee()->db->table_exists($name.$this->table_suffix) === TRUE
			)
			{
				//irrelevant table?
				if ( ! in_array($name, $this->new_has_site_id))
				{
					continue;
				}

				//is it already the new table we care about?
				if ($this->column_exists('site_id', $name))
				{
					continue;
				}
				//so, we already have a legacy table
				//and this is NOT the new schema, we need to drop
				//so update can add the new table with proper schema
				else
				{
					$table_count	= ee()->db->count_all($name);
					$l_table_count	= ee()->db->count_all($name.$this->table_suffix);

					//if the legacy table is empty and the old one isn't
					//then we need to keep the one with entries
					if ($table_count > 0 AND $l_table_count == 0)
					{
						ee()->db->query('DROP TABLE IF EXISTS ' . $name.$this->table_suffix);
						//no continue here because we now need the rename_table
						//to run
					}
					//drop bad table and move on
					else
					{
						ee()->db->query('DROP TABLE IF EXISTS ' . $name);
						continue;
					}

				}
			}

			ee()->db->query(
				'ALTER TABLE ' . $name .
				' RENAME TO ' . $name . $this->table_suffix
			);
		}

		// -------------------------------------
		//	Add tracking column
		// -------------------------------------

		if ( $this->column_exists(
				'new_entry_id',
				'exp_freeform_entries' . $this->table_suffix
			) === FALSE )
		{
			ee()->db->query(
				'ALTER TABLE `exp_freeform_entries' . $this->table_suffix . '`
				 ADD `new_entry_id` INT(10) UNSIGNED NOT NULL default 0'
			);
		}

		// -------------------------------------
		//	Rename any empty form_name column to the default of freeform_form
		// -------------------------------------

		ee()->db->update(
			'exp_freeform_entries' . $this->table_suffix,
			array('form_name'	=> 'freeform_form'),
			array('form_name'	=> '')
		);

		// -------------------------------------
		//	Add form label column
		// -------------------------------------

		if ( $this->column_exists(
				'form_label',
				'exp_freeform_entries' . $this->table_suffix
			) === FALSE )
		{
			ee()->db->query(
				'ALTER TABLE `exp_freeform_entries' . $this->table_suffix . '`
				ADD `form_label` VARCHAR(120) NOT NULL AFTER form_name'
			);
		}

		// -------------------------------------
		//	Copy form names over
		// -------------------------------------

		ee()->db->query(
			"UPDATE	exp_freeform_entries" . $this->table_suffix . "
			 SET	form_label = form_name"
		);

		// -------------------------------------
		//	Get form names
		// -------------------------------------

		$query = ee()->db->query(
			"SELECT		form_name
			 FROM		exp_freeform_entries" . $this->table_suffix . "
			 GROUP BY	form_name"
		);

		ee()->load->helper('url');

		foreach ( $query->result_array() as $row )
		{
			ee()->db->update(
				"exp_freeform_entries" . $this->table_suffix,
				array(
					'form_name' => url_title(
						$row['form_name'],
						ee()->config->item('word_separator'),
						TRUE
					)
				),
				array(
					'form_name' => $row['form_name']
				)
			);
		}

		// -------------------------------------
		//	Return
		// -------------------------------------

		return TRUE;
	}
	//	End upgrade: rename tables


	// --------------------------------------------------------------------

	/**
	 * Assign fields to form
	 *
	 * @access public
	 * @return boolean
	 */

	public function assign_fields_to_form ($form_id = '', $field_ids = array() )
	{
		// -------------------------------------
		//	Validate
		// -------------------------------------

		if ( empty( $form_id ) OR empty( $field_ids ) ) return FALSE;

		// -------------------------------------
		//	Go to Greg
		// -------------------------------------

		ee()->load->library('freeform_forms');

		$data['field_ids']		= implode( '|', $field_ids );
		$data['field_order']	= $data['field_ids'];

		ee()->freeform_forms->update_form( $form_id, $data );

		// -------------------------------------
		//	Return
		// -------------------------------------

		return TRUE;
	}
	//	End assign fields to form


	// --------------------------------------------------------------------

	/**
	 * Create field
	 *
	 * @access public
	 * @return integer
	 */

	public function create_field ($form_id = '', $field_name = '', $field_attr = array() )
	{
		// -------------------------------------
		//	Validate
		// -------------------------------------

		if ( empty( $form_id ) OR
			empty( $field_name ) )
		{
			$this->errors['missing_data_for_field_creation'] = lang(
				'missing_data_for_field_creation'
			);

			return FALSE;
		}

		// -------------------------------------
		//	Workaround name?
		// -------------------------------------

		if ( in_array( $field_name, array( 'status' ) ) )
		{
			$field_name	= $field_name . $this->table_suffix;
		}

		// -------------------------------------
		//	Cached?
		// -------------------------------------

		if ( ! empty( $this->cache['create_field'][$form_id.$field_name] ) )
		{
			return $this->cache['create_field'][$form_id.$field_name];
		}

		// -------------------------------------
		//	Valid name?
		// -------------------------------------

		if ( in_array($field_name, $this->data->prohibited_names ) )
		{
			$this->errors['field_name'] = str_replace(
				'%name%',
				$field_name,
				lang('freeform_reserved_field_name')
			);

			return FALSE;
		}

		// -------------------------------------
		//	Exists?
		// -------------------------------------

		ee()->load->model('freeform_field_model');
		ee()->freeform_field_model->clear_cache();
		//ee()->freeform_field_model->cache_enabled = FALSE;

		$row =	ee()->freeform_field_model
					->where('field_name', $field_name)
					->get_row();

		if ( $row AND isset( $row['field_id'] ) )
		{
			$this->cache['create_field'][$form_id.$field_name] = $row['field_id'];
			return $this->cache['create_field'][$form_id.$field_name];
		}

		// -------------------------------------
		//	field label
		// -------------------------------------

		$field_label = ( empty( $field_attr['field_label'] ) ) ?
							$field_name :
							$field_attr['field_label'];

		// -------------------------------------
		//	field type
		// -------------------------------------

		$field_type = ( $field_attr['field_type'] != 'textarea' ) ? 'text': 'textarea';

		// -------------------------------------
		//	load and save field
		// -------------------------------------

		ee()->load->library('freeform_fields');

		$available_fieldtypes = ee()->freeform_fields->get_available_fieldtypes();

		$field_instance =& ee()->freeform_fields->get_fieldtype_instance($field_type);

		// -------------------------------------
		//	field type
		// -------------------------------------

		if ( ! $field_type OR ! array_key_exists($field_type, $available_fieldtypes))
		{
			$this->errors['field_type'] = lang('invalid_fieldtype');

			return FALSE;
		}

		// -------------------------------------
		//	insert data
		// -------------------------------------

		$data 		= array(
			'field_name'		=> $field_name,
			'field_label'		=> $field_label,
			'field_type'		=> $field_type,
			'edit_date'			=> '0', //overridden if update
			'field_description'	=> '',
			'submissions_page'	=> 'y',
			'moderation_page'	=> 'y',
			'composer_use'		=> 'y',
			'settings'			=> json_encode($field_instance->save_settings())
		);

		ee()->load->model('freeform_field_model');

		$field_id = ee()->freeform_field_model->insert(
			array_merge(
				$data,
				array(
					'author_id'		=> ee()->session->userdata('member_id'),
					'entry_date'	=> ee()->localize->now,
					'site_id' 		=> ee()->config->item('site_id')
				)
			)
		);

		$field_instance->field_id = $field_id;

		$field_instance->post_save_settings();

		// -------------------------------------
		//	Return
		// -------------------------------------

		return $this->cache['create_field'][$form_id.$field_name] = $field_id;
	}
	//	End create field


	// --------------------------------------------------------------------

	/**
	 * Create upload field
	 *
	 * @access public
	 * @return integer
	 */

	public function create_upload_field ($form_id = '', $field_name = '', $field_attr = array() )
	{
		// -------------------------------------
		//	Validate
		// -------------------------------------

		if ( empty( $form_id ) OR empty( $field_name ) )
		{
			$this->errors['missing_data_for_field_creation']	= lang('missing_data_for_field_creation');
			return FALSE;
		}

		// -------------------------------------
		//	field label
		// -------------------------------------

		$field_label = (empty($field_attr['field_label'])) ?
						$field_name :
						$field_attr['field_label'];

		// -------------------------------------
		//	field name
		// -------------------------------------

		ee()->load->helper('url');

		$field_name	= url_title( $field_name, ee()->config->item('word_separator'), TRUE );

		// -------------------------------------
		//	Cached?
		// -------------------------------------

		if ( ! empty( $this->cache['create_upload_field'][$form_id.$field_name] ) )
		{
			return $this->cache['create_upload_field'][$form_id.$field_name];
		}

		// -------------------------------------
		//	Valid name?
		// -------------------------------------

		if ( in_array($field_name, $this->data->prohibited_names ) )
		{
			$this->errors['field_name'] = str_replace(
				'%name%',
				$field_name,
				lang('freeform_reserved_field_name')
			);

			return FALSE;
		}

		// -------------------------------------
		//	Exists?
		// -------------------------------------

		ee()->load->model('freeform_field_model');

		$row = ee()->freeform_field_model->get_row(array('field_name' => $field_name));

		if ( $row AND isset( $row['field_id'] ) )
		{
			return $this->cache['create_field'][$form_id.$field_name] = $row['field_id'];
		}

		// -------------------------------------
		//	field instance
		// -------------------------------------

		$field_type = 'file_upload';

		ee()->load->library('freeform_fields');

		$available_fieldtypes = ee()->freeform_fields->get_available_fieldtypes();
		$field_instance =& ee()->freeform_fields->get_fieldtype_instance($field_type);

		// -------------------------------------
		//	field type
		// -------------------------------------

		if ( ! $field_type OR ! array_key_exists($field_type, $available_fieldtypes))
		{
			$this->errors['field_type'] = lang('invalid_fieldtype');
			return FALSE;
		}

		// -------------------------------------
		//	default settings
		// -------------------------------------

		foreach ( $field_instance->default_settings as $key => $val )
		{
			$settings[$key]	= $val;

			if ( ! empty( $field_attr[$key] ) )
			{
				$settings[$key]	= $field_attr[$key];
			}
		}

		// -------------------------------------
		//	allowed file types forced?
		// -------------------------------------

		if ( ! empty( $field_attr['allowed_types'] ) )
		{
			if ( $field_attr['allowed_types'] == 'all' )
			{
				$settings['allowed_file_types']	= '*';
			}
			elseif ( $field_attr['allowed_types'] != 'img' )
			{
				$settings['allowed_file_types']	= '';
			}
		}

		// -------------------------------------
		//	allowed upload count
		// -------------------------------------

		if ( ! empty( $field_attr['allowed_upload_count'] ) )
		{
			$settings['allowed_upload_count']	= $field_attr['allowed_upload_count'];

			// You know. Maybe a spammer went to town on this person's
			// site and dumped a bunch of attachments in.
			// I think we protected against that in FF3, but whatevs
			if ( $settings['allowed_upload_count'] > $field_instance->max_files )
			{
				$settings['allowed_upload_count']	= 3;
			}
		}

		// -------------------------------------
		//	file upload location
		// -------------------------------------

		if ( ! empty( $field_attr['pref_id'] ) )
		{
			$settings['file_upload_location']	= $field_attr['pref_id'];
		}

		// -------------------------------------
		//	validate settings
		// -------------------------------------

		foreach ($settings as $key => $value)
		{
			$_POST[$key] = $value;
		}

		if ( ( $errors = $field_instance->validate_settings() ) !== TRUE )
		{
			$this->errors	= array_merge( $this->errors, $errors );
			return FALSE;
		}

		// -------------------------------------
		//	insert data
		// -------------------------------------

		$data 		= array(
			'field_name'		=> $field_name,
			'field_label'		=> $field_label,
			'field_type'		=> $field_type,
			'edit_date'			=> '0', //overridden if update
			'field_description'	=> '',
			'submissions_page'	=> 'y',
			'moderation_page'	=> 'y',
			'composer_use'		=> 'y',
			'settings'			=> json_encode($field_instance->save_settings())
		);

		ee()->load->model('freeform_field_model');

		$field_id = ee()->freeform_field_model->insert(
			array_merge(
				$data,
				array(
					'author_id'		=> ee()->session->userdata('member_id'),
					'entry_date'	=> ee()->localize->now,
					'site_id' 		=> ee()->config->item('site_id')
				)
			)
		);

		$field_instance->field_id = $field_id;

		$field_instance->post_save_settings();

		// -------------------------------------
		//	Return
		// -------------------------------------

		return $this->cache['create_upload_field'][$form_id.$field_name] = $field_id;
	}
	//	End create upload field


	// --------------------------------------------------------------------

	/**
	 * Create form
	 *
	 * @access public
	 * @return array
	 */

	public function create_form ( $form_name = '' )
	{
		// -------------------------------------
		//	Migrated / Unmigrated
		// -------------------------------------

		if ( empty( $form_name ) )
		{
			$this->errors['empty_form_name']	= lang('empty_form_name');
			return FALSE;
		}

		// -------------------------------------
		//	Cached?
		// -------------------------------------

		if ( ! empty( $this->cache['create_form'][$form_name] ) )
		{
			return $this->cache['forms'][$form_name];
		}
		// -------------------------------------
		//	Label
		// -------------------------------------

		$out['form_label']	= $form_name;

		$query	= ee()->db->query(
			"SELECT form_label
			 FROM exp_freeform_entries" . $this->table_suffix . "
			 WHERE form_name = '" . ee()->db->escape_str( $form_name ) . "'" );


		if ( $query->num_rows() > 0 )
		{
			$out['form_label']	= $query->row('form_label');
		}

		// -------------------------------------
		//	Clean name
		// -------------------------------------

		ee()->load->helper('url');
		$form_name		= url_title( $form_name, ee()->config->item('word_separator'), TRUE );

		// -------------------------------------
		//	Prohibited name?
		// -------------------------------------

		if ( in_array($form_name, $this->data->prohibited_names ) )
		{
			$this->errors['form_name'] = str_replace(
				'%name%',
				$form_name,
				lang('reserved_form_name')
			);

			return FALSE;
		}

		// -------------------------------------
		//	Collision?
		// -------------------------------------

		$query	= ee()->db->query(
			"SELECT form_id, form_name, form_label
			 FROM exp_freeform_forms
			 WHERE site_id = " . ee()->db->escape_str( ee()->config->item('site_id') ) . "
			 AND form_name = '" . ee()->db->escape_str( $form_name ) . "'" );

		if ( $query->num_rows() > 0 )
		{
			$out['form_name']	= $query->row('form_name');
			$out['form_label']	= $query->row('form_label');
			$out['form_id']		= $query->row('form_id');

			return $this->cache['create_form'][$form_name] = $out;
		}

		// -------------------------------------
		//	Load
		// -------------------------------------

		ee()->load->model('freeform_form_model');
		ee()->load->library('freeform_forms');

		// -------------------------------------
		//	Admin notification email
		// -------------------------------------

		//	The legacy FF did not save this value in the DB. But this value may be available in templates. We could search templates to determine that.

		// -------------------------------------
		//	User email field
		// -------------------------------------

		//	The legacy FF did not save this value in the DB. But this value may be available in templates. We could search templates to determine that.

		// -------------------------------------
		//	Notify admin
		// -------------------------------------

		//	The legacy FF did not save this value in the DB. But this value may be available in templates. We could search templates to determine that.

		$notify_admin	= 'n';

		// -------------------------------------
		//	Notify user
		// -------------------------------------

		//	The legacy FF did not save this value in the DB. But this value may be available in templates. We could search templates to determine that.

		$notify_user	= 'n';

		// -------------------------------------
		//	Insert data
		// -------------------------------------

		$data = array(
			'form_name'					=> $form_name,
			'form_label'				=> $out['form_label'],
			'default_status'			=> 'open',
			'author_id' 				=> ee()->session->userdata('member_id'),
			'notify_admin'				=> $notify_admin,
			'notify_user'				=> $notify_user
		);

		$form_id = ee()->freeform_forms->create_form($data);

		// -------------------------------------
		//	Return
		// -------------------------------------

		$out['form_id']		= $form_id;
		$out['form_name']	= $form_name;

		return $this->cache['create_form'][$form_name] = $out;
	}

	//	End create form

	// --------------------------------------------------------------------

	/**
	 * Get attachment profiles
	 *
	 * @access public
	 * @return array
	 */

	public function get_attachment_profiles( $collection = '' )
	{
		// -------------------------------------
		//	Cached?
		// -------------------------------------

		if ( isset( $this->cache['get_attachment_profiles'][$collection] ) ) return $this->cache['get_attachment_profiles'][$collection];

		// -------------------------------------
		//	Query
		// -------------------------------------

		$query	= ee()->db->query(
			"SELECT a.pref_id, p.name, p.allowed_types
			FROM exp_freeform_attachments" . $this->table_suffix . " a
			LEFT JOIN exp_upload_prefs p ON p.id = a.pref_id
			WHERE p.site_id = " . ee()->db->escape_str( ee()->config->item('site_id') ) . "
			GROUP BY p.id"
		);

		if ( $query->num_rows() == 0 ) return $this->cache['get_attachment_profiles'][$collection] = FALSE;

		// -------------------------------------
		//	Get allowed_upload_count
		// -------------------------------------
		// I thought a simple DB query could do this, but I gave up
		// -------------------------------------

		$cquery	= ee()->db->query(
			"SELECT entry_id, pref_id
			FROM exp_freeform_attachments" . $this->table_suffix
		);

		foreach ( $cquery->result_array() as $row )
		{
			$temp[ $row['pref_id'] ][ $row['entry_id'] ][]	= 1;
		}

		foreach ( $temp as $pref_id => $entries )
		{
			rsort( $entries );

			$counts[ $pref_id ]	= ( isset( $counts[ $pref_id ] ) AND count( $entries[0] ) > $counts[ $pref_id ] ) ? count( $entries[0] ): count( $entries[0] );
		}

		foreach ( $query->result_array() as $row )
		{
			$out[ $row['pref_id'] ]	= $row;
			$out[ $row['pref_id'] ]['allowed_upload_count']	= 1;

			if ( ! empty( $counts[ $row['pref_id'] ] ) )
			{
				$out[ $row['pref_id'] ]['allowed_upload_count']	= $counts[ $row['pref_id'] ];
			}
		}

		// -------------------------------------
		//	Return
		// -------------------------------------

		return $this->cache['get_attachment_profiles'][$collection] = $out;
	}

	//	End get attachment profiles

	// --------------------------------------------------------------------

	/**
	 * Get collection counts
	 *
	 * @access public
	 * @return array
	 */

	public function get_collection_counts( $collections = array() )
	{
		$counts	= array();

		// -------------------------------------
		//	Bother?
		// -------------------------------------

		if ( $this->legacy() === FALSE ) return $counts;

		// -------------------------------------
		//	Clean incoming
		// -------------------------------------

		foreach ( $collections as $key => $val )
		{
			$collections[ $key ]	= ee()->db->escape_str( $val );
		}

		// -------------------------------------
		//	Migrated / Unmigrated
		// -------------------------------------

		$table	= 'exp_freeform_entries' . $this->table_suffix;

		$sql	= "SELECT COUNT(*) AS count, form_name FROM " . $table . " WHERE new_entry_id = 0";

		if ( ! empty( $collections ) )
		{
			$sql	.= " AND form_name IN ('" . implode( "','", $collections ) . "')";
		}

		$sql	.= " GROUP BY form_name";

		$query	= ee()->db->query( $sql );

		foreach ( $query->result_array() as $row )
		{
			$counts[ $row['form_name'] ][ 'migrated' ]		= 0;
			$counts[ $row['form_name'] ][ 'unmigrated' ]	= $row['count'];
		}

		$sql	= "SELECT COUNT(*) AS count, form_name FROM " . $table . " WHERE new_entry_id != 0";

		if ( ! empty( $collections ) )
		{
			$sql	.= " AND form_name IN ('" . implode( "','", $collections ) . "')";
		}

		$sql	.= " GROUP BY form_name";

		$query	= ee()->db->query( $sql );

		foreach ( $query->result_array() as $row )
		{
			$counts[ $row['form_name'] ][ 'unmigrated' ]	= ( empty( $counts[ $row['form_name'] ][ 'unmigrated' ] ) ) ? 0: $counts[ $row['form_name'] ][ 'unmigrated' ];
			$counts[ $row['form_name'] ][ 'migrated' ]	= $row['count'];
		}

		// -------------------------------------
		//	Return
		// -------------------------------------

		return $counts;
	}

	//	End get collection counts

	// --------------------------------------------------------------------

	/**
	 * Get field type installed
	 *
	 * @access public
	 * @return boolean
	 */

	public function get_field_type_installed($field_type = '')
	{
		if ( empty( $field_type ) ) return FALSE;

		// -------------------------------------
		//	Cached?
		// -------------------------------------

		if ( ! empty( $this->cache['get_field_type_installed'][$field_type] ) ) return $this->cache['get_field_type_installed'][$field_type];

		// -------------------------------------
		//	Query
		// -------------------------------------

		ee()->load->model('freeform_fieldtype_model');
		$field_types	= ee()->freeform_fieldtype_model->installed_fieldtypes();

		return ! empty( $field_types['file_upload'] );
	}

	//	End get field type installed

	// --------------------------------------------------------------------

	/**
	 * Get fields
	 *
	 * @access public
	 * @return array
	 */

	public function get_fields()
	{
		// -------------------------------------
		//	Cached?
		// -------------------------------------

		if ( ! empty( $this->cache['get_fields'] ) ) return $this->cache['get_fields'];

		// -------------------------------------
		//	DB fetch
		// -------------------------------------

		$query	= ee()->db->query( "SELECT field_id, field_type, field_order, name AS field_name, label AS field_label FROM exp_freeform_fields" . $this->table_suffix . " ORDER BY field_order" );

		return $this->cache['get_fields']	= $this->prepare_keyed_result( $query, 'field_name' );
	}

	//	End get fields

	// --------------------------------------------------------------------

	/**
	 * Get fields for collection
	 *
	 * @access public
	 * @return array
	 */

	public function get_fields_for_collection( $collection = '', $show_empties = 'n' )
	{
		// -------------------------------------
		//	Get fields
		// -------------------------------------

		$fields	= $this->get_fields();

		if ( empty( $fields ) ) return FALSE;

		// -------------------------------------
		//	Show empties?
		// -------------------------------------

		if ( $show_empties != 'n' )
		{
			return $fields;
		}

		// -------------------------------------
		//	Yes filter to make sure we exclude empty fields now.
		// -------------------------------------

		$ors	= array();

		foreach ( array_keys( $fields ) as $val )
		{
			$ors[]	= $val . " != ''";
		}

		$sql	= "SELECT " . implode( ',', array_keys( $fields ) ) . "
			FROM exp_freeform_entries" . $this->table_suffix . "
			WHERE form_name = '" . ee()->db->escape_str( $collection ) . "'
			AND (" . implode( ' OR ', $ors ) . ")";

		$query	= ee()->db->query( $sql );

		if ( $query->num_rows() == 0 ) return FALSE;

		$out	= array();

		foreach ( $query->result_array() as $row )
		{
			foreach ( $row as $key => $val )
			{
				if ( $val == '' ) continue;

				$out[$key]	= $key;
			}
		}

		foreach ( array_keys( $fields ) as $val )
		{
			if ( in_array( $val, $out ) === FALSE ) unset( $fields[$val] );
		}

		if ( empty( $fields ) ) return FALSE;

		return $fields;
	}

	//	End get fields for collection

	// --------------------------------------------------------------------

	/**
	 * Get migration count
	 *
	 * @access public
	 * @return boolean
	 */

	public function get_migration_count( $collections = array() )
	{
		foreach ( $collections as $key => $val )
		{
			$collections[ $key ]	= ee()->db->escape_str( $val );
		}

		// -------------------------------------
		//	Unmigrated
		// -------------------------------------

		$table	= 'exp_freeform_entries' . $this->table_suffix;

		$sql	= "SELECT COUNT(*) AS count FROM " . $table . " WHERE new_entry_id = 0";

		if ( ! empty( $collections ) )
		{
			$sql	.= " AND form_name IN ('" . implode( "','", $collections ) . "')";
		}

		$query	= ee()->db->query( $sql );

		// -------------------------------------
		//	Return
		// -------------------------------------

		return $query->row('count');
	}
	//	End get migration count


	// --------------------------------------------------------------------

	/**
	 * Legacy
	 *
	 * @access public
	 * @return boolean
	 */

	public function legacy ()
	{
		if (isset($this->cache['legacy']))
		{
			return $this->cache['legacy'];
		}

		$this->cache['legacy'] = FALSE;

		// -------------------------------------
		//	DB check
		// -------------------------------------

		$query = ee()->db->query(
			"SHOW TABLES
			 LIKE 'exp_freeform_entries" . ee()->db->escape_str($this->table_suffix) . "'"
		);

		if ($query->num_rows() > 0)
		{
			$count = ee()->db
						->where('new_entry_id', 0)
						->count_all_results(
							'exp_freeform_entries' . $this->table_suffix
						);

			if ($count > 0 )
			{
				$this->cache['legacy'] = TRUE;
			}
		}

		// -------------------------------------
		//	Return
		// -------------------------------------

		return $this->cache['legacy'];
	}
	//	End legacy


	// --------------------------------------------------------------------

	/**
	 * Get errors
	 *
	 * @access	public
	 * @return	array
	 */

	public function get_errors()
	{
		if (empty($this->errors))
		{
			return FALSE;
		}

		return $this->errors;
	}
	// End get errors


	// --------------------------------------------------------------------

	/**
	 * Get legacy entry
	 *
	 * @access	public
	 * @return	array
	 */

	public function get_legacy_entry( $form_name )
	{
		// -------------------------------------
		//	SQL
		// -------------------------------------

		$sql	= "SELECT *
			FROM exp_freeform_entries" . $this->table_suffix . "
			WHERE new_entry_id = 0
			AND form_name = '" . ee()->db->escape_str( $form_name ) . "'
			LIMIT 1";

		$query	= ee()->db->query( $sql );

		if ( $query->num_rows() == 0 ) return FALSE;

		$entry	= $query->row_array();

		// -------------------------------------
		//	Get attachments
		// -------------------------------------

		$sql	= "SELECT *
			FROM exp_freeform_attachments" . $this->table_suffix . "
			WHERE entry_id = " . ee()->db->escape_str( $query->row('entry_id') );

		$aquery	= ee()->db->query( $sql );

		foreach ( $aquery->result_array() as $row )
		{
			$entry['attachments'][]	= $row;
		}

		// -------------------------------------
		//	Get email log
		// -------------------------------------

		$sql	= "SELECT email_count
			FROM exp_freeform_user_email" . $this->table_suffix . "
			WHERE entry_id = " . ee()->db->escape_str( $query->row('entry_id') );

		$aquery	= ee()->db->query( $sql );

		foreach ( $aquery->result_array() as $row )
		{
			$entry['user_emails'][]	= $row['email_count'];
		}

		// -------------------------------------
		//	Return
		// -------------------------------------

		return $entry;
	}
	// End get legacy entry


	// --------------------------------------------------------------------

	/**
	 * Get legacy entries
	 *
	 * @access	public
	 * @return	array
	 */

	public function get_legacy_entries( $form_name )
	{
		// -------------------------------------
		//	SQL
		// -------------------------------------

		$sql	= "SELECT *
			FROM exp_freeform_entries" . $this->table_suffix . "
			WHERE new_entry_id = 0
			AND form_name = '" . ee()->db->escape_str( $form_name ) . "'
			LIMIT " . $this->batch_limit;

		$query	= ee()->db->query( $sql );

		if ( $query->num_rows() == 0 ) return FALSE;

		$entries	= $this->prepare_keyed_result( $query, 'entry_id' );

		// -------------------------------------
		//	Get attachments
		// -------------------------------------

		$sql	= "SELECT *
			FROM exp_freeform_attachments" . $this->table_suffix . "
			WHERE entry_id IN (" . ee()->db->escape_str( implode( ',', array_keys( $entries ) ) ) . ")";

		$aquery	= ee()->db->query( $sql );

		foreach ( $aquery->result_array() as $row )
		{
			if ( empty( $row['entry_id'] ) ) continue;
			$entries[$row['entry_id']]['attachments'][]	= $row;
		}

		// -------------------------------------
		//	Get email log
		// -------------------------------------

		$sql	= "SELECT email_count
			FROM exp_freeform_user_email" . $this->table_suffix . "
			WHERE entry_id IN (" . ee()->db->escape_str( implode( ',', array_keys( $entries ) ) ) . ")";

		$equery	= ee()->db->query( $sql );

		foreach ( $equery->result_array() as $row )
		{
			if ( empty( $row['entry_id'] ) ) continue;
			$entries[$row['entry_id']]['user_emails'][]	= $row['email_count'];
		}

		// -------------------------------------
		//	Return
		// -------------------------------------

		return $entries;
	}

	// End get legacy entries

	// --------------------------------------------------------------------

	/**
	 * Set legacy entry
	 *
	 * @access	public
	 * @return	array
	 */

	public function set_legacy_entry( $form_id = '', $entry = array() )
	{
		// -------------------------------------
		//	Validate
		// -------------------------------------

		if ( empty( $form_id ) OR empty( $entry ) )
		{
			$this->errors['empty_form_name']	= lang('empty_form_name');
			return FALSE;
		}

		// -------------------------------------
		//	Library
		// -------------------------------------

		ee()->load->library('freeform_forms');
		ee()->load->library('freeform_fields');
		ee()->load->model('freeform_form_model');

		// -------------------------------------
		//	form data
		// -------------------------------------

		$form_data 		= $this->data->get_form_info($form_id);

		// -------------------------------------
		//	Workaround name?
		// -------------------------------------

		foreach ( array( 'status') as $name )
		{
			if ( isset( $entry[$name] ) )
			{
				$entry[$name.$this->table_suffix]	= $entry[$name];
				unset( $entry[$name] );
			}
		}

		// -------------------------------------
		//	validate
		// -------------------------------------

		$field_input_data	= array();
		$field_list			= array();

		foreach ($form_data['fields'] as $field_id => $field_data)
		{
			$field_list[$field_data['field_name']] = $field_data['field_label'];

			$field_post	= ( ! empty( $entry[ $field_data['field_name'] ] ) ) ? $entry[ $field_data['field_name'] ]: '';

			$field_input_data[$field_data['field_name']] = $field_post;
		}

		$defaults	= array(
			'author_id',
			'ip_address',
			'entry_date',
			'edit_date',
			'status'
		);

		foreach ( $defaults as $default )
		{
			if ( ! empty( $entry[ $default ] ) )
			{
				$field_input_data[ $default ]	= $entry[ $default ];
			}
		}

		// -------------------------------------
		//	correct for edit date
		// -------------------------------------

		if ( ! empty( $field_input_data['entry_date'] ) AND ! empty( $field_input_data['edit_date'] ) AND $field_input_data['entry_date'] == $field_input_data['edit_date'] )
		{
			$field_input_data['edit_date']	= 0;
		}

		// -------------------------------------
		//	attachments?
		// -------------------------------------

		if ( $this->migrate_attachments === TRUE AND ! empty( $entry['attachments'] ) )
		{
			//--------------------------------------
			//  Attachments? Add to entries table.
			//--------------------------------------

			$attachments	= $entry['attachments'];

			$temp	= array();

			foreach ( $attachments as $val )
			{
				$temp[ $val['pref_id'] ][]	= $val['filename'] . $val['extension'];
			}

			foreach ( $temp as $pref_id => $names )
			{
				if ( isset( $this->upload_pref_id_map[ $pref_id ]['field_id'] ) === TRUE )
				{
					$field_input_data[ $form_data['fields'][ $this->upload_pref_id_map[ $pref_id ]['field_id'] ]['field_name'] ]	= implode( "\n", $names );
				}
			}
		}

		unset( $entry['attachments'] );

		//form fields do thier own validation,
		//so lets just get results! (sexy results?)
		$this->errors = array_merge(
			$this->errors,
			ee()->freeform_fields->validate(
				$form_id,
				$field_input_data
			)
		);

		// -------------------------------------
		//	Errors
		// -------------------------------------

		if ( ! empty( $this->errors ) )
		{
			return FALSE;
		}

		// -------------------------------------
		//	Insert
		// -------------------------------------

		$entry_id = ee()->freeform_forms->insert_new_entry(
			$form_id,
			$field_input_data
		);

		//--------------------------------------
		//  Add attachments to file uploads table
		//--------------------------------------

		if ( ! empty( $attachments ) )
		{
			foreach ( $attachments as $attachment )
			{
				if ( empty( $this->upload_pref_id_map[ $attachment['pref_id'] ]['field_id'] ) ) continue;

				$field_id	= $this->upload_pref_id_map[ $attachment['pref_id'] ]['field_id'];

				if ( ( $file_id = $this->set_legacy_attachment( $form_id, $entry_id, $field_id, $attachment ) ) === FALSE )
				{
					//	return FALSE;
				}
			}
		}

		// -------------------------------------
		//	User emails?
		// -------------------------------------

		if ( ! empty( $entry['user_emails'] ) )
		{
			$insert	= array(
				'site_id'		=> ee()->config->item('site_id'),
				'author_id'		=> ( ! empty( $field_input_data['author_id'] ) ) ? $field_input_data['author_id']: '',
				'ip_address'	=> ( ! empty( $field_input_data['ip_address'] ) ) ? $field_input_data['ip_address']: '',
				'form_id'		=> $form_id,
				'entry_id'		=> $entry_id
			);

			foreach ( $entry['user_emails'] as $email_count )
			{
				$insert['email_count']	= $email_count;

				ee()->db->query(
					ee()->db->insert_string(
						'exp_freeform_user_email',
						$insert
					)
				);
			}
		}

		// -------------------------------------
		//	Record new entry id
		// -------------------------------------

		$sql	= ee()->db->update_string( 'exp_freeform_entries' . $this->table_suffix, array( 'new_entry_id' => $entry_id ), array( 'entry_id' => $entry['entry_id'] ) );

		ee()->db->query( $sql );

		// -------------------------------------
		//	Return
		// -------------------------------------

		return $entry_id;
	}

	// End set legacy entry

	// --------------------------------------------------------------------

	/**
	 * Set legacy attachment
	 *
	 * @access	public
	 * @return	array
	 */

	public function set_legacy_attachment( $form_id = '', $entry_id = '', $field_id = '', $attachment = array() )
	{
		// -------------------------------------
		//	Validate
		// -------------------------------------

		if ( empty( $form_id ) OR empty( $entry_id ) OR empty( $field_id ) OR empty( $attachment ) ) return FALSE;

		// -------------------------------------
		//	Prep
		// -------------------------------------

		$insert 	= array(
			'form_id' 		=> $form_id,
			'entry_id'		=> $entry_id,
			'field_id'		=> $field_id,
			'site_id' 		=> $this->EE->config->item('site_id'),
			'server_path' 	=> $attachment['server_path'],
			'filename'		=> $attachment['filename'],
			'extension' 	=> preg_replace('/^\./', '', $attachment['extension']),
			'filesize'		=> $attachment['filesize']
		);

		$insert['filename']	= $insert['filename'] . '.' . $insert['extension'];

		// -------------------------------------
		//	Library
		// -------------------------------------

		ee()->load->model('freeform_file_upload_model');

		// -------------------------------------
		//	Insert
		// -------------------------------------

		$file_id = ee()->freeform_file_upload_model->insert($insert);

		// -------------------------------------
		//	Return
		// -------------------------------------

		return $file_id;
	}

	// End set legacy attachment

	// --------------------------------------------------------------------

	/**
	 * Set property
	 *
	 * @access	public
	 * @return	null
	 */

	public function set_property( $property, $value )
	{
		if ( isset( $this->$property ) === FALSE ) return FALSE;

		$this->$property	= $value;

		return TRUE;
	}

	// End set property
}

//END Freeform_migration
