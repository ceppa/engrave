function list_cassa()
{
	$('#titolo').html('Gestiona cassa');
	cassa_flexi();
}

function toolbar_cassa()
{
	show_headers(true);
	list_cassa();
}

function toolbar_select_cassa()
{
	show_headers(true);
	select_cassa_print();
}

function select_cassa_print()
{
	showWait();
	$.post('include/stampe.php', { op: "select_cassa_print" }, 
		function(data) 
		{
			$('#titolo').html('Gestione cassa - stampe');
			$('#content').html(data);
			showform();
		});
}

function cassa_add()
{
	showWait();
	$.post('include/cassa.php', { op: "add" }, 
		function(data) 
		{
			cassa_form('Gestione cassa - nuovo inserimento',data)
			$('#data').datepicker('setDate', 'today');
		});
}

function entrata_uscita_change(sender)
{
	var id=sender.attr("id");
	var val=$("#"+id).val().replace(',', '.');
	var floatNumber=parseFloat(val);
	if(isNaN(floatNumber))
		floatNumber="";
	else 
		floatNumber=floatNumber.toFixed(2).replace('.',',');

	$("#"+id).val(floatNumber);
	if($("#"+id).val().length)
	{
		if(id=="entrata")
			$("#uscita").val("");
		else
			$("#entrata").val("");
	}
}

function cassa_form(title,data)
{
	$('#titolo').html(title);
	$('#content').html(data);
	$('#submit').bind('click', cassa_form_submit);
	$('#cancel').bind('click', cassa_form_cancel);
	$('#entrata').change(function()
		{
			entrata_uscita_change($(this));
		});
	$('#uscita').change(function()
		{
			entrata_uscita_change($(this));
		});

	$('#entrata').keydown(
		function(e) 
		{
			return onlyNumbersFloat(e,this);
		}
	);
	$('#uscita').keydown(
		function(e) 
		{
			return onlyNumbersFloat(e,this);
		}
	);
	showform();
}

function cassa_edit()
{
	showWait();
	$.post('include/cassa.php', { op: "edit", id:  flexiItemId}, 
		function(data) 
		{
			cassa_form('Gestione cassa - modifica',data);
			entrata_uscita_change($("#entrata"));
			entrata_uscita_change($("#uscita"));
		});
}

function cassa_form_submit()
{
	var notnull=new Array("data","descrizione");
	var magzero=new Array();

	var ok=form_validate(notnull,magzero);

	var cassaok=(($("#entrata").val().length>0)||($("#uscita").val().length>0));

	if(!cassaok)
	{
		$("#entrata").addClass("error");
		$("#uscita").addClass("error");
	}
	else
	{
		$("#entrata").removeClass("error");
		$("#uscita").removeClass("error");
	}

	ok&=cassaok;
	if(ok)
	{
		var entrata=$("#entrata").val().replace(',', '.');
		var uscita=$("#uscita").val().replace(',', '.');
		if(uscita.length>0)
			$("#entrata").val("-"+uscita);
		else
			$("#entrata").val(entrata);
		$("#entrata").attr("name", "importo");
		$("#uscita").remove();
		form_post("cassa");
		$("#flexi_table").flexReload();
		showflexi();
		$('#titolo').html('Gestione cassa');
	}
}

function cassa_form_cancel()
{
	showflexi();
	$('#titolo').html('Gestione cassa');
}

function cassa_delete()
{
	var id=flexiItemId.substring(3);
	if (confirm("Rimuovo l'inserimento selezionato?")) 
	{ 
		showWait();
		$.post('include/cassa.php', 
			{ 
				op: "del", 
				id: id
			}, 
				function(data) 
				{
					showflexi();
					$("#flexi_table").flexReload();
				}
			).always(function() 
			{
				showflexi();
			});
	}
}

function cassa_flexi()
{
	$("#flexi").html('<div id="flexi_table"></div>');
	$("#flexi_table").flexigrid({
		url: 'include/cassa.php',
			dataType: 'json',
			colModel : 
			[
				{
					display: 'Data', 
					name : 'data', 
					width : 110, 
					sortable : true, 
					align: 'left'
				},
				{
					display: 'Descrizione', 
					name : 'descrizione', 
					width : 300, 
					sortable : true, 
					align: 'left'
				},
				{
					display: 'Entrata', 
					name : 'entrata', 
					width : 60, 
					sortable : true, 
					align: 'right'
				},
				{
					display: 'Uscita', 
					name : 'uscita', 
					width : 60, 
					sortable : true, 
					align: 'right'
				}
			],
			buttons : 
			[
				{
					name: 'Add', 
					bclass: 'add', 
					onpress : cassa_add
				},
				{
					name: 'Edit', 
					bclass: 'edit', 
					onpress : cassa_edit
				},
				{
					name: 'Delete', 
					bclass: 'delete', 
					onpress : cassa_delete
				},
				{
					separator: true
				}			
			],
			searchitems : 
			[
				{
					display: 'Data', 
					name : 'data'
				},
				{
					display: 'Descrizione', 
					name : 'descrizione'
				}
			],
			params:
			[
				{
					name:'op', 
					value: 'list'
				}
			],
			sortname: "data",
			sortorder: "desc",
			usepager: true,
			useRp: true,
			rp: 40,
			showTableToggleBtn: false,
			showToggleBtn: false,
			resizable: false,
			width: 'auto',
			height: 'auto',
			singleSelect: true,
			onRowSelected:cassa_row_selected,
			onRowSelectedClick:cassa_row_selected_click,
			onRowDeselected:cassa_row_deselected,
		}
	);
	showflexi();
}

function cassa_row_selected(itemId,row,grid)
{
	flexiItemId=itemId;
	$('.edit').show();
	$('.delete').show();
}
function cassa_row_selected_click(itemId,row,grid)
{
	cassa_edit()
}
function cassa_row_deselected(itemId,row,grid)
{
	alert("row_deselected");
}

