
$(document).ready(
	function()
	{
		$.ajax(
			{
				url: "include/page.php"
			}).done(function( html ) 
		{
			var imageCount = $(html).find('img').length;
			var imagesLoaded = 0;
			$(html).hide()
                 .appendTo('#container')
                 .find('img')
                 .load( function() 
                 {
                     ++imagesLoaded;
                     if (imagesLoaded >= imageCount)
                     {
						showPage();
                     }
                  });
			setTimeout( function() 
				{ 
					if($("#loader").is(':visible'))
						showPage() 
				}, 5000 );
		})
	});

function showPage()
{
	$("#loader").fadeOut(1000);
	$("#bottom").show();
	hideNavigation();
	initfbbutton();
	$("#container").fadeIn(1000);
	showModule("main",true);
	initializeNavigation();
	$("[class^='map_']").click(function()
		{
			var module=$(this).attr("class").substr(4);
			showModule(module);
		});
}

function initfbbutton()
{
	$("#fbon").hide();
	$("#fboff").show();
	$("#fboff").mouseenter(function()
		{
			$("#fbon").fadeIn(150);
			$(this).fadeOut(150);
		}).mouseleave(function()
			{
/*				setTimeout(function () 
				{
					if($("#fbon:hover").length==0)
						$("#fbon").mouseleave();
				}, 150)
	*/			
			});
	$("#fbon").mouseleave(function()
		{
			$("#fboff").fadeIn(150);
			$(this).fadeOut(150);
		});

}

function showModule(module,immediate)
{
	if(module=="contatti")
		$("#bottom").width(400);
	else
		$("#bottom").width(800);
	immediate=(typeof(immediate)!="undefined"?immediate:false);
	var activeModule_id="div_"+module;
	$("#"+activeModule_id).show();

	var elems = $("[id^='div_']");
	var count = elems.length;

	elems.each(function()
		{
			if(($(this).is(':visible'))&&($(this).attr("id")!=activeModule_id))
			{
				if(immediate)
					$(this).hide();
				else					
					$(this).fadeOut(200);
			}
		});

	$("#activemodule").val(module);


	$("#fbon,#fboff").unbind("click");
	switch(module)
	{
		case "engraved":
			$("#fbon").removeClass().addClass("fbengraved");
			$("#fbon,#fboff").click(function(){openFacebookLink('a.399579846844261.1073741828');});
			break;
		case "graphic_design":
			$("#fbon").removeClass().addClass("fbgraphic_design");
			$("#fbon,#fboff").click(function(){openFacebookLink('a.399581120177467.1073741829');});
			break;
		case "fashion_style":
			$("#fbon").removeClass().addClass("fbfashion_style");
			$("#fbon,#fboff").click(function(){openFacebookLink('a.405443546257891.1073741830');});
			break;
		case "digital_print":
			$("#fbon").removeClass().addClass("fbdigital_print");
			$("#fbon,#fboff").click(function(){openFacebookLink('a.405449006257345.1073741831');});
			break;
		case "3d_print":
			$("#fbon").removeClass().addClass("fb3d_print");
			$("#fbon,#fboff").click(function(){openFacebookLink('a.405449409590638.1073741832');});
			break;
		case "furnishing_accessories":
			$("#fbon").removeClass().addClass("fbfurnishing_accessories");
			$("#fbon,#fboff").click(function(){openFacebookLink('a.405449689590610.1073741833');});
			break;
		case "advertising":
			$("#fbon").removeClass().addClass("fbadvertising");
			$("#fbon,#fboff").click(function(){openFacebookLink('a.405450402923872.1073741834');});
			break;
		default:
			$("#fbon").removeClass().addClass("fbdefault");
			$("#fbon,#fboff").click(function(){openlink('http://www.facebook.com/engravelab')}); 
			break;
		
			
	}

	function openFacebookLink(n)
	{
		var address="https://www.facebook.com/media/set/?set="+n+".399554550180124&type=3";
		openlink(address);
	}


	showNavigation(module);

}

function showNavigation(module)
{
	if((module!="main")&&(module!="contatti"))
		$("#navigation").fadeIn(200);
	else
		$("#navigation").fadeOut(200);
	var elems = $("[class^='navigation_']");

	elems.each(function()
		{
			var current_class=$(this).attr("class");
			var is_big=(current_class.substr(current_class.length-4)=="_big");
			var is_active=(current_class.substr(11,module.length)==module);
			if((is_active && is_big)||(!is_active && (!is_big)))
				$(this).show();
			else
				$(this).hide();
		});

}

function initializeNavigation()
{
	var elems = $("[class^='navigation_']");

	elems.each(function()
		{
			$(this).hover(navigation_mousein, navigation_mouseout );
			$(this).click(navigation_click);
		});
}

function navigation_click(sender)
{
	var current_class=sender.currentTarget.className;
	var module=current_class.substr(11);
	var is_big=(module.substr(module.length-4)=="_big");
	if(is_big)
		module=module.substr(0,module.length-4);
	showModule(module);
}

function navigation_mousein(sender)
{
	var current_class=sender.currentTarget.className;
	var module=current_class;
	var is_big=(current_class.substr(current_class.length-4)=="_big");
	if(is_big)
		module=module.substr(0,module.length-4);
	if(!is_big)
	{
		$("."+module+"_big").fadeIn(150);
		fadeAllOthers(module);
	}
}

function fadeAllOthers(module)
{
	var elems = $("[class^='navigation_'][class$='_big']");	
	elems.each(function()
		{
			var current_class=$(this).attr("class");
			var small_class=current_class.substr(11,current_class.length-15);
			if(small_class!=module.substr(11))
			{
				var is_visible=$(this).is(':visible');

				if(is_visible)
				{
					var is_not_active=(small_class!=$("#activemodule").val());
					if(is_not_active)
						$(this).fadeOut(150);
				}
			}
		});

}

function navigation_mouseout(sender)
{
	var current_class=sender.currentTarget.className;
	var module=current_class;
	var is_big=(current_class.substr(current_class.length-4)=="_big");
	if(is_big)
		module=module.substr(0,module.length-4);
	if((is_big)&&($("."+module).is(':visible')))
		$("."+current_class).fadeOut(150);
}

function hideNavigation()
{
	$("#navigation").hide();
}

function openlink(page)
{
	window.open(page,"_blank");
}
