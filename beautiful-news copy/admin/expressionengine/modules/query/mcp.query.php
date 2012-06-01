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
 * ExpressionEngine Query Module
 *
 * @package		ExpressionEngine
 * @subpackage	Modules
 * @category	Update File
 * @author		EllisLab Dev Team
 * @link		http://expressionengine.com
 */
class Query_mcp {

	var $version = '1.0';

	function Query_mcp()
	{
		// Make a local reference to the ExpressionEngine super object
		$this->EE =& get_instance();
	}
}
// END CLASS

/* End of file mcp.query.php */
/* Location: ./system/expressionengine/modules/query/mcp.query.php */