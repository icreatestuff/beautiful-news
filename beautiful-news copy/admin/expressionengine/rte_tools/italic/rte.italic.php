<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * ExpressionEngine - by EllisLab
 *
 * @package		ExpressionEngine
 * @author		EllisLab Dev Team
 * @copyright	Copyright (c) 2003 - 2012, EllisLab, Inc.
 * @license		http://expressionengine.com/user_guide/license.html
 * @link		http://expressionengine.com
 * @since		Version 2.5
 * @filesource
 */
 
// ------------------------------------------------------------------------

/**
 * ExpressionEngine Italic RTE Tool
 *
 * @package		ExpressionEngine
 * @subpackage	RTE
 * @category	RTE
 * @author		EllisLab Dev Team
 * @link		http://expressionengine.com
 */
class Italic_rte {

	public $info = array(
		'name'			=> 'Italic',
		'version'		=> '1.0',
		'description'	=> 'Italicizes and de-italicizes text',
		'cp_only'		=> 'n'
	);
	
	private $EE;
	
	/**
	 * Constructor
	 *
	 * @access	public
	 */
	function __construct()
	{
		// Make a local reference of the ExpressionEngine super object
		$this->EE =& get_instance();
	}

	// --------------------------------------------------------------------

	/**
	 * Globals we need
	 *
	 * @access	public
	 */
	function globals()
	{
		$this->EE->lang->loadfile('rte');
		return array(
			'rte.italics'	=> array(
				'add'		=> lang('make_italics'),
				'remove'	=> lang('remove_italics')
			)
		);
	}
	
	// --------------------------------------------------------------------

	/**
	 * JS Defintion
	 *
	 * @access	public
	 */
	function definition()
	{
		ob_start(); ?>
		
		WysiHat.addButton('italic', {
			label:			EE.rte.italics.add,
			'toggle-text':	EE.rte.italics.remove
		});
		
<?php	$buffer = ob_get_contents();
		ob_end_clean(); 
		return $buffer;
	}

} // END Italic_rte

/* End of file rte.italic.php */
/* Location: ./system/expressionengine/rte_tools/italic/rte.italic.php */