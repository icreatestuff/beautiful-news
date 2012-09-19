<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

 /**
 * Solspace - Freeform
 *
 * @package		Solspace:Freeform
 * @author		Solspace DevTeam
 * @copyright	Copyright (c) 2008-2012, Solspace, Inc.
 * @link		http://solspace.com/docs/addon/c/Freeform/
 * @filesource 	./system/expressionengine/third_party/freeform/models/
 */

 /**
 * Freeform - Freeform File Upload Model
 *
 * @package 	Solspace:Freeform
 * @author		Solspace DevTeam
 * @filesource 	./system/expressionengine/third_party/freeform/models/freeform_file_upload_model.php
 */

if ( ! class_exists('Freeform_Model'))
{
	require_once 'freeform_model.php';
}

class Freeform_file_upload_model extends Freeform_Model 
{
	//nonstandard id
	public $id = 'file_id';
}
//END Freeform_preference_model