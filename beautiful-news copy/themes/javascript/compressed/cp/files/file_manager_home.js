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

$.ee_filemanager=$.filemanager||{};$(document).ready(function(){$.ee_filemanager.file_uploader();$.ee_filemanager.datatables();$.ee_filemanager.image_overlay();$.ee_filemanager.date_range();$.ee_filemanager.toggle_all();$.ee_filemanager.directory_change();$(".paginationLinks .first").hide();$(".paginationLinks .previous").hide()});
$.ee_filemanager.file_uploader=function(){$.ee_fileuploader({type:"filemanager",load:function(){$.template("filemanager_row",$("#filemanager_row").remove())},open:function(){$.ee_fileuploader.set_directory_id($("#dir_id").val())},after_upload:function(b,a){a.replace==!0&&$(".mainTable tbody tr:has(td:contains("+a.file_id+")):has(td:contains("+a.file_name+"))").remove();a.actions="";$.each(EE.fileuploader.actions,function(e,c){var b=c.replace("[file_id]",a.file_id).replace("[upload_dir]",a.upload_directory_prefs.id);
if(e=="delete")a.action_delete=b;else if(e!="image"||a.is_image)a.actions+=b+"&nbsp;&nbsp;"});if(typeof a.title=="undefined")a.title=a.name;if(a.is_image){var d=$("<a>",{id:"",href:a.upload_directory_prefs.url+a.file_name,title:a.file_name,text:a.title,rel:"#overlay","class":"less_important_link overlay"});a.link=d.wrap("<div>").parent().html()}else a.link=a.title;$(".mainTable tbody").prepend($.tmpl("filemanager_row",a));$("td.dataTables_empty").size()&&$("td.dataTables_empty").parent().remove();
a.replace!=!0&&$("#file_uploader").dialog("option","position","center");$(".mainTable").show();$(".mainTable").table("clear_cache")},trigger:"#action_nav a.upload_file"})};
$.ee_filemanager.directory_change=function(){function b(b){a[b]===void 0&&(b=0);jQuery.each(a[b],function(a,b){$("select#cat_id").empty().append(b)})}var a=EE.file.directoryInfo,d=RegExp("!-!","g");$.each(a,function(b,c){$.each(c,function(c,g){var f=new String;$.each(g,function(a,b){f+='<option value="'+b[0]+'">'+b[1].replace(d,String.fromCharCode(160))+"</option>"});a[b][c]=f})});$("#dir_id").change(function(){b(this.value)})};
$.ee_filemanager.date_range=function(){function b(){if($("#custom_date_start").val()!="yyyy-mm-dd"&&$("#custom_date_end").val()!="yyyy-mm-dd")focus_number=$("#date_range").children().length,$("#date_range").append('<option id="custom_date_option">'+$("#custom_date_start").val()+" to "+$("#custom_date_end").val()+"</option>"),document.getElementById("date_range").options[focus_number].selected=!0,$("#custom_date_picker").slideUp("fast"),oTable.fnDraw()}$("#custom_date_start_span").datepicker({dateFormat:"yy-mm-dd",
prevText:"<<",nextText:">>",onSelect:function(a){$("#custom_date_start").val(a);b()}});$("#custom_date_end_span").datepicker({dateFormat:"yy-mm-dd",prevText:"<<",nextText:">>",onSelect:function(a){$("#custom_date_end").val(a);b()}});$("#custom_date_start, #custom_date_end").focus(function(){$(this).val()=="yyyy-mm-dd"&&$(this).val("")});$("#custom_date_start, #custom_date_end").keypress(function(){$(this).val().length>=9&&b()});$("#date_range").change(function(){$("#date_range").val()=="custom_date"?
($("#custom_date_start").val("yyyy-mm-dd"),$("#custom_date_end").val("yyyy-mm-dd"),$("#custom_date_option").remove(),$("#custom_date_picker").slideDown("fast")):$("#custom_date_picker").hide()})};$.ee_filemanager.toggle_all=function(){$(".toggle_all").toggle(function(){$("input.toggle").each(function(){this.checked=!0})},function(){$("input.toggle").each(function(){this.checked=!1})})};
$.ee_filemanager.image_overlay=function(){$("a.overlay").live("click",function(){$("#overlay").hide().removeData("overlay");$("#overlay .contentWrap img").remove();$("<img />").appendTo("#overlay .contentWrap").load(function(){var b=$(this).clone().appendTo(document.body).show(),a=b.width(),d=b.height(),e=$(window).width()*0.8,c=$(window).height()*0.8;e/=a;c/=d;c=e>c?c:e;b.remove();c<1&&(d*=c,a*=c,$(this).height(d).width(a));$("#overlay").overlay({load:!0,speed:100,top:"center"})}).attr("src",$(this).attr("href"));
return!1});$("#overlay").css("cursor","pointer").click(function(){$(this).fadeOut(100)})};$.ee_filemanager.datatables=function(){$(".mainTable").table("add_filter",$("#filterform"))};
