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

(function(b){var h={resize_width:"#resize_width",resize_height:"#resize_height",submit_resize:"",cancel_resize:"",oversized_class:"oversized",default_height:0,default_width:0,resize_confirm:"",callback_resize:"",callback_submit:"",callback_cancel:""};b.fn.resize_scale=function(i){return this.each(function(){var a=b.extend({},h,i),d=b(this),c=b(a.resize_width,d),e=b(a.resize_height,d),j=b(a.submit_resize,d),f=b(a.cancel_resize,d);a.default_height=parseInt(a.default_height,10);a.default_width=parseInt(a.default_width,
10);c.add(e).keyup(function(){f.show();var g=b(this),d=g.attr("id");(d==="resize_height"?c:e).val(Math.round((d==="resize_width"?a.default_height/a.default_width:a.default_width/a.default_height)*g.val()));c.val()>a.default_width||e.val()>a.default_height?(c.addClass(a.oversized_class),e.addClass(a.oversized_class)):(e.removeClass(a.oversized_class),c.removeClass(a.oversized_class));typeof a.callback_resize==="function"&&a.callback_resize.call(this,{width:c.val(),height:e.val()})});j.off("click",
"**").on("click",function(c){b("."+a.oversized_class).size()&&(confirm(a.resize_confirm)==!1?c.preventDefault():typeof a.callback_submit==="function"?a.callback_submit.call(this):d.trigger("submit"))});f.size()&&f.click(function(b){b.preventDefault();c.val(a.default_width).removeClass(a.oversized_class);e.val(a.default_height).removeClass(a.oversized_class);typeof a.callback_cancel==="function"&&a.callback_cancel.call(this,{width:c.val(),height:e.val()});f.hide()})})}})(jQuery);
