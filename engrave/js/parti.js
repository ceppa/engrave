function list_parti()
{
	$('#titolo').html('anagrafica parti');
	parti_flexi();
}

function toolbar_parti()
{
	show_headers(true);
	list_parti();
}



function parti_edit()
{
	showWait();
	$.post('include/parti.php', { op: "edit", id:  flexiItemId}, 
		function(data) 
		{
			$('#titolo').html('Parti - modifica');
			$('#content').html(data);
			$('#submit').bind('click', parti_form_submit);
			$('#cancel').bind('click', parti_form_cancel);
			showform();
		});
}


function parti_form_submit()
{
	var notnull=new Array("codice","descrizione");
	var magzero=new Array("um_id","id_fornitore","id_tipo_materiale");

	var ok=form_validate(notnull,magzero);

	if(ok)
	{
		form_post("parti");
		$("#flexi_table").flexReload();
		showflexi();
		$('#titolo').html('anagrafica Parti');
	}
}

function parti_form_cancel()
{
	showflexi();
	$('#titolo').html('anagrafica Parti');
}


function parti_flexi()
{
	$("#flexi").html('<div id="flexi_table"></div>');
	$("#flexi_table").flexigrid({
		url: 'include/parti.php',
			dataType: 'json',
			colModel : 
			[
				{
					display: 'Codice', 
					name : 'codice', 
					width : 120, 
					sortable : true, 
					align: 'left'
				},
				{
					display: 'Descrizione', 
					name : 'descrizione', 
					width : 235, 
					sortable : true, 
					align: 'left'
				},
				{
					display: 'UM', 
					name : 'um', 
					width : 40, 
					sortable : true, 
					align: 'left'
				},
				{
					display: 'Fornitore', 
					name : 'fornitore', 
					width : 110, 
					sortable : true, 
					align: 'left'
				},
				{
					display: 'Tipo materiale', 
					name : 'tipo_materiale', 
					width : 200, 
					sortable : true, 
					align: 'left'
				},
				{
					display: 'Scorta minima', 
					name : 'scorta_minima', 
					width : 80, 
					sortable : true, 
					align: 'left'
				}
			],
			buttons : 
			[
				{
					name: 'Edit', 
					bclass: 'edit', 
					onpress : parti_edit
				},
				{
					separator: true
				}			
			],
			searchitems : 
			[
				{
					display: 'Codice', 
					name : 'codice',
					isdefault: true
				},
				{
					display: 'descrizione', 
					name : 'descrizione'
				},
				{
					display: 'Fornitore', 
					name : 'anag_fornitori.denominazione'
				},
				{
					display: 'Tipo materiale', 
					name : 'tipo_materiale.description'
				},
				{
					display: 'Scorta minima', 
					name : 'scorta_minima'
				}
			],
			params:
			[
				{
					name:'op', 
					value: 'list'
				}
			],
			sortname: "codice",
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
			onRowSelected:parti_row_selected,
			onRowSelectedClick:parti_row_selected_click,
			onRowDeselected:parti_row_deselected,
		}
	);
	showflexi();
}

function parti_row_selected(itemId,row,grid)
{
	flexiItemId=itemId;
	$('.edit').show();
}
function parti_row_selected_click(itemId,row,grid)
{
	parti_edit()
}
function parti_row_deselected(itemId,row,grid)
{
	alert("row_deselected");
}

