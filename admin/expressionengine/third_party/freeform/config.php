<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

 /**
 * Solspace - Freeform
 *
 * @package		Solspace:Freeform
 * @author		Solspace DevTeam
 * @copyright	Copyright (c) 2008-2012, Solspace, Inc.
 * @link		http://solspace.com/docs/addon/c/Freeform/
 * @version		4.0.6
 * @filesource 	./system/expressionengine/third_party/freeform/
 */

 /**
 * Freeform - Config
 *
 * NSM Addon Updater Config File
 *
 * @package 	Solspace:Freeform
 * @author		Solspace DevTeam
 * @filesource 	./system/expressionengine/third_party/freeform/config.php
 */

//since we are 1.x/2.x compatible, we only want this to run in 1.x just in case
if ( ! defined('FREEFORM_VERSION'))
{
	require_once 'constants.freeform.php';
}

$config['name']									= 'Freeform';
$config['version'] 								= FREEFORM_VERSION;
$config['nsm_addon_updater']['versions_xml'] 	= 'http://www.solspace.com/software/nsm_addon_updater/freeform';