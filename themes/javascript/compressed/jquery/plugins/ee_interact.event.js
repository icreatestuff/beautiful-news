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

(function(b){function f(a,d){return b(a).map(function(){return this.elements?b.makeArray(this.elements):this}).filter(function(){return this.name}).map(d)}function g(a){if(jQuery.nodeName(a,"textarea"))return!0;if(!jQuery.nodeName(a,"input"))return!1;a=a.type;return!a?!0:a=="text"||a=="password"||a=="search"||a=="url"||a=="email"||a=="tel"?!0:!1}function c(a,d,c){setTimeout(function(){var c=b.data(a,"_interact_cache"),e=a.value;c!==e&&(b.event.trigger("interact",d,a),b.data(a,"_interact_cache",e))},
c||0)}b.event.special.interact={setup:function(a,d){b.nodeName(this,"form")?f(this,function(){b.event.special.interact.setup.call(this,a,d)}):g(this)?(b.data(this,"_interact_cache",this.value),b.event.add(this,"keyup.specialInteract change.specialInteract",function(){c(this,a)}),b.event.add(this,"input.specialInteract cut.specialInteract paste.specialInteract",function(){c(this,a,25)})):b.event.add(this,"change.specialInteract",function(){b.event.trigger("interact",a,this)})},teardown:function(){b(this).unbind(".specialInteract")}}})(jQuery);
