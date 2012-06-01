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

(function(a){function m(){var i=a("#dir_choice"),c=a(window).width()*0.95;c>974&&(c=974);d.dialog({width:c,height:615,resizable:!1,position:["center","center"],modal:!0,draggable:!0,title:EE.filebrowser.window_title,autoOpen:!1,zIndex:99999,open:function(){var c=e[f].directory;isNaN(c)||i.val(c);i.trigger("interact");a("#dir_choice").val()},close:function(){a.ee_filebrowser.reload();a("#keywords",d).val("")}});a("#file_browser_body").find("table").each(function(){b=a(this);if(b.data("table_config"))return!1});
c=b.data("table_config");b.table(c);b.table("add_filter",i);b.table("add_filter",a("#keywords"));var g=b.table("get_template");thumb_template=a("#thumbTmpl").remove().html();table_container=b.table("get_container");thumb_container=a("#file_browser_body");a("#view_type").change(function(){this.value=="thumb"?(b.detach(),b.table("set_container",thumb_container),b.table("set_template",thumb_template),b.table("add_filter",{per_page:36})):(thumb_container.html(b),b.table("set_container",table_container),
b.table("set_template",g),b.table("add_filter",{per_page:15}))});a("#upload_form",d).submit(a.ee_filebrowser.upload_start);a("#file_browser_body",d).addClass(n)}var d,f="",n="list",l=0,e={},j="",b=null,k;a.ee_filebrowser=function(){a.ee_filebrowser.endpoint_request("setup",function(b){dir_files_structure={};dir_paths={};d=a(b.manager).appendTo(document.body);for(var c in b.directories)l||(l=c),dir_files_structure[c]="";m();typeof a.ee_fileuploader!="undefined"&&a.ee_fileuploader({type:"filebrowser",
open:function(){a.ee_fileuploader.set_directory_id(a("#dir_choice").val())},close:function(){a("#file_uploader").removeClass("upload_step_2").addClass("upload_step_1");a("#file_browser").size()&&a.ee_filebrowser.reload()},trigger:"#file_browser #upload_form input"})})};a.ee_filebrowser.endpoint_request=function(b,c,d){typeof d=="undefined"&&a.isFunction(c)&&(d=c,c={});c=a.extend(c,{action:b});a.ajax({url:EE.BASE+"&"+EE.filebrowser.endpoint_url,type:"GET",dataType:"json",data:c,cache:!1,success:function(a){a.error?
j=a.error:typeof d=="function"&&d.call(this,a)}})};a.ee_filebrowser.add_trigger=function(b,c,g,h){h?e[c]=g:a.isFunction(c)?(h=c,c="userfile",e[c]={content_type:"any",directory:"all"}):a.isFunction(g)&&(h=g,e[c]={content_type:"any",directory:"all"});a(b).click(function(){if(j)return alert(j),!1;var b=this;f=c;e[f].directory!="all"?(a("#dir_choice",d).val(e[f].directory),a("#dir_choice_form .dir_choice_container",d).hide()):(a("#dir_choice",d).val(),a("#dir_choice_form .dir_choice_container",d).show());
d.dialog("open");k=function(a){h.call(b,a,c)};return!1})};a.ee_filebrowser.get_current_settings=function(){return e[f]};a.ee_filebrowser.placeImage=function(b){a.ee_filebrowser.endpoint_request("file_info",{file_id:b},function(a){k(a);d.dialog("close")});return!1};a.ee_filebrowser.clean_up=function(b){d!=void 0&&(b&&k(b),a("#keywords",d).val(""),d.dialog("close"))};a.ee_filebrowser.reload_directory=function(){a.ee_filebrowser.reload()};a.ee_filebrowser.reload=function(){b&&(b.table("clear_cache"),
b.table("refresh"))}})(jQuery);
