//------------------------------------------------------------------------------
// Splash Plugin v 0.9.7 [14.02.2011]
// (C) 2010-2011 Evgeny Vrublevsky <veg@tut.by>
//------------------------------------------------------------------------------

jQuery.extend(
{
	splash: function(id, width, height)
	{
		var html =
			"<div id='"+id+"_container' style='z-index: 9999; position: fixed; top: 0; left: 0; width: 100%; height: 100%; _position: absolute; _top: expression(eval(document.documentElement.scrollTop ? document.documentElement.scrollTop : document.body.scrollTop)); _left: expression(eval(document.documentElement.scrollLeft ? document.documentElement.scrollLeft : document.body.scrollLeft)); _width: expression(eval(document.documentElement.clientWidth ? document.documentElement.clientWidth : document.body.clientWidth)); _height: expression(eval(document.documentElement.clientHeight ? document.documentElement.clientHeight : document.body.clientHeight));'>"+
			"<div id='"+id+"_shadow_bkg' style='position: absolute; top: 0; left: 0; width: 100%; height: 100%; background-color: #000; opacity: 0.7; _height: expression(eval(document.documentElement.clientHeight ? document.documentElement.clientHeight : document.body.clientHeight)); filter:alpha(opacity=70);'></div>"+
			"<div id='"+id+"_window' style='margin: 0px; padding: 0px; border: 0; position: absolute; top: 50%; left: 50%;'><div id='"+id+"'></div>"+
			"</div>";
		jQuery("body").append(html);
		jQuery("#"+id).bind("reposition", function()
		{
			var str = jQuery("#"+id+"_window").css("height");
			var height = {value: parseInt(str), measure: str.replace(parseInt(str).toString(), "")};
			if(!height.value) height = {value: jQuery("#"+id+"_window").height(), measure: "px"};
			var str = jQuery("#"+id+"_window").css("width");
			var width = {value: parseInt(str), measure: str.replace(parseInt(str).toString(), "")};
			if(!width.value) width = {value: jQuery("#"+id+"_window").width(), measure: "px"};
			jQuery("#"+id+"_window").css("margin-left", parseInt(-width.value/2)+width.measure).css("margin-top", parseInt(-height.value/2)+height.measure);
		});
		jQuery("#"+id).bind("resize", function(e, width, height)
		{
			if(width) 	jQuery("#"+id+"_window").css("width", width);
			if(height) 	jQuery("#"+id+"_window").css("height", height);
			jQuery("#"+id).trigger("reposition");
		});
		jQuery("#"+id).bind("close", function()
		{
			jQuery("#"+id).unbind("reposition");
			jQuery("#"+id).unbind("close");
			jQuery("#"+id).remove();
			jQuery("#"+id+"_window").remove();
			jQuery("#"+id+"_shadow_bkg").remove();
			jQuery("#"+id+"_container").remove();
		});
		jQuery("#"+id+"_shadow_bkg").click(function()
		{
			jQuery("#"+id).trigger("close");
			return false;
		});
		jQuery("#"+id).trigger("resize", [width, height]);
		return jQuery("#"+id);
	}
});
