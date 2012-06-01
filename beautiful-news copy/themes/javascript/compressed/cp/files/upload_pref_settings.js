/*!
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

$(document).ready(function(){$(".remove_size").click(function(){var a=$(this).attr("size_short_name_").substr(16),b=$(this).parent().parent();alert(a);$.ajax({dataType:"json",data:"id="+a,url:EE.BASE+"&C=content_files&M=delete_dimension",success:function(a){a.response==="success"?($.ee_notice(EE.lang.size_deleted,{type:"success",open:!0,close_on_click:!0}),$(b).fadeOut("slow",function(){$(this).remove()})):$.ee_notice(EE.lang.size_not_deleted,{type:"error",open:!0,close_on_click:!0})}});return!1})});
