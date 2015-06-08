function toolbar_print_warehouse()
{
	show_headers(true);
	print_warehouse();
}

function toolbar_print_giacenze_critiche()
{
	show_headers(true);
	print_giacenze_critiche();
}

function toolbar_print_carichi()
{
	show_headers(true);
	print_carichi();
}

function toolbar_print_scarichi()
{
	show_headers(true);
	print_scarichi();
}



function print_warehouse()
{
	showWait();
	$.post('include/stampe.php', { op: "print_warehouse" }, 
		function(data) 
		{
			$('#titolo').html('Stampa magazzino');
			$('#content').html(data);
			showform();
			$('#date_to').change(function(){print_status(this.value)});
			$('#date_to').datepicker('setDate', 'today');
			$('#date_to').change();
		});
}

function print_giacenze_critiche()
{
	showWait();
	$.post('include/stampe.php', { op: "print_giacenze_critiche" }, 
		function(data) 
		{
			$('#titolo').html('Stampa giacenze critiche');
			$('#content').html(data);
			showform();
		});
}

function print_carichi()
{
	showWait();
	$.post('include/stampe.php', { op: "print_carichi" }, 
		function(data) 
		{
			$('#titolo').html('Stampa carichi per anno');
			$('#content').html(data);
			showform();
			$('#anno_carichi').change(function(){print_carichi_update(this.value)});
			$('#anno_carichi').datepicker('setDate', 'today');
			$('#anno_carichi').change();
		});
}

function print_scarichi()
{
	showWait();
	$.post('include/stampe.php', { op: "print_scarichi" }, 
		function(data) 
		{
			$('#titolo').html('Stampa scarichi per anno');
			$('#content').html(data);
			showform();
			$('#anno_scarichi').change(function(){print_scarichi_update(this.value)});
			$('#anno_scarichi').datepicker('setDate', 'today');
			$('#anno_scarichi').change();
		});
}

function print_carichi_update(anno)
{
	$.post('include/stampe.php', { op: "print_carichi_update",anno: anno }, 
		function(data) 
		{
			$('#div_result').html(data);
			showform();
		});	
}

function print_scarichi_update(anno)
{
	$.post('include/stampe.php', { op: "print_scarichi_update",anno: anno }, 
		function(data) 
		{
			$('#div_result').html(data);
			showform();
		});	
}

function print_status(to_date)
{
	$.post('include/stampe.php', { op: "status",to_date: to_date }, 
		function(data) 
		{
			$('#div_result').html(data);
			showform();
		});	
}

