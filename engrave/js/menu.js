function display_admin_nav()	//async
{
	$.ajax({
			type:'POST',
			url:'include/menu.php', 
			data:{ op: "display_admin_nav" },
			cache: false,
			async: false
		}).done(function(data) 
			{
				$('#admin_nav').html(data);
			});
}
