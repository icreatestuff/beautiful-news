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
class Tools extends CI_Controller {

	/**
	 * Constructor
	 *
	 * @access	public
	 */
	function __construct()
	{
		parent::__construct();
		
		if ( ! $this->cp->allowed_group('can_access_tools'))
		{
			show_error(lang('unauthorized_access'));
		}

		$this->lang->loadfile('tools');
	}
	
	// --------------------------------------------------------------------

	/**
	 * Index function
	 *
	 * @access	public
	 * @return	void
	 */	
	function index()
	{
		$this->cp->set_variable('cp_page_title', lang('tools'));

		$this->javascript->compile();

		$this->load->vars(array('controller' => 'tools'));

		$this->load->view('_shared/overview');
	}
	
}

/* End of file tools.php */
/* Location: ./system/expressionengine/controllers/cp/tools.php */