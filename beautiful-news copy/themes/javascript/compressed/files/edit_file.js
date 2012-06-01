/*!
 * ExpressionEngine - by EllisLab
 *
 * @package		ExpressionEngine
 * @author		EllisLab Dev Team
 * @copyright	Copyright (c) 2003 - 2012, EllisLab, Inc.
 * @license		http://expressionengine.com/user_guide/license.html
 * @link		http://expressionengine.com
 * @since		Version 2.4
 * @filesource
 */

var EE=EE||{};EE.publish=EE.publish||{};EE.publish.edit_file=EE.publish.edit_file||{};
(function(a){EE.publish.edit_file.reset_tabs=function(){a(".panel-menu li").removeClass("current").filter(":first").addClass("current");a(".panels > div").removeClass("current").filter(":first").addClass("current")};EE.publish.edit_file.change_tabs=function(){EE.publish.edit_file.reset_tabs();a(".panel-menu li a").click(function(b){var c=a(this).data("panel");a(".panels").children().hide().removeClass("current").filter("#"+c).show().addClass("current");a(this).parent().addClass("current").siblings().removeClass("current");
b.preventDefault()})};EE.publish.edit_file.change_tabs();EE.publish.edit_file.image_tool_select=function(){a("#image_tools input[name=image_tool]").click(function(){a(this).parent().parent().siblings().find("div").slideUp();a(this).parent().siblings("div").slideDown();a(this).val()!="resize"&&(a("#resize_height").val(EE.filemanager.image_height),a("#resize_width").val(EE.filemanager.image_width));a("input[name=action]").val(a(this).val())})};EE.publish.edit_file.image_tool_select();a("form#edit_file_metadata").resize_scale({cancel_resize:"#cancel_resize",
default_height:EE.filemanager.image_height,default_width:EE.filemanager.image_width})})(jQuery);
