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

$(document).ready(function(){$("select[name=upload_dirs]").change(function(){var b=$(this).val();$.ajax({url:EE.BASE+"&C=content_files&M=get_dir_cats",type:"POST",data:{XID:EE.XID,upload_directory_id:b},success:function(a){a.error===!0?$("#file_cats").html(""):($("#file_cats").html('<fieldset class="holder">'+a+"</fieldset>"),$("#file_cats").find(".edit_categories_link").hide())},error:function(){$("#file_cats").html("")}})})});
