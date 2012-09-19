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
 * Freeform - Freeform Notification Model
 *
 * @package 	Solspace:Freeform
 * @author		Solspace DevTeam
 * @filesource 	./system/expressionengine/third_party/freeform/models/freeform_notification_model.php
 */

if ( ! class_exists('Freeform_Model'))
{
	require_once 'freeform_model.php';
}

class Freeform_notification_model extends Freeform_Model 
{
	//nonstandard name
	public $_table = 'freeform_notification_templates';
}
//END Freeform_preference_model