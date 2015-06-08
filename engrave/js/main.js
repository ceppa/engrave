var flexiItemId=false;

function main()
{
	display_admin_nav();
	run_module("toolbar_magazzino");
}

function show_headers(doshow)
{
	if(doshow)
	{
		$('#header').show();
		$('#admin_nav').show();
	}
	else
	{
		$('#header').hide();
		$('#admin_nav').hide();
	}
}

function notify(message)
{
	if(message.substr(0,1)=='0')
		messagebox(message.substr(1));
	else
		errorbox(message.substr(1));
}

function messagebox(message)
{
	$("#messageBox").css("height","40px");
	$("#messageBox").removeClass().addClass("messagebox").stop(true).hide().html(message).slideDown(1000).delay(3000).slideUp(2000);
}
function errorbox(message)
{
	$("#messageBox").css("height","40px");
	$("#messageBox").removeClass().addClass("errorbox").stop(true).hide().html(message).slideDown(2000).delay(3000).slideUp(2000);
}

$(document).ready(
	function()
	{
		auth();
		$("#messageBox").hide();
	}
)

function display_admin_nav()	//async
{
	$.ajax({
			type:'POST',
			url:'include/main.php', 
			data:{ op: "display_admin_nav" },
			cache: false,
			async: false
		}).done(function(data) 
			{
				$('#admin_nav').html(data);

			});
}

function run_module(module)
{
	showWait();
	eval(module+"()");
	
}

function showform()
{
	$("#flexi").hide();
	$("#content").show();
	$(".date_class").attr('readonly', true);
	$(".date_class").datepicker({ dateFormat: "yy-mm-dd" })
}

function showflexi()
{
	$("#content").hide();
	$("#flexi").show();
}

function showWait()
{
	$("#flexi").hide();
	$("#content").show();
	$("#content").html("<img src='img/wait.gif' style='{ display: block; margin-left: auto; margin-right: auto;}' />");
}

function dateNow()
{
	var today = new Date();
	var dd = today.getDate();
	var mm = today.getMonth()+1; //January is 0!
	var yyyy = today.getFullYear();

	if(dd<10)
		dd='0'+dd

	if(mm<10) 
		mm='0'+mm

	return yyyy+'-'+mm+'-'+dd;
}
