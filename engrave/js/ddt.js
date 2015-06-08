function list_ddt()
{
	$('#titolo').html('anagrafica DDT');
	ddt_flexi();
}

function toolbar_ddt()
{
	show_headers(true);
	list_ddt();
}

function cliente_change()
{
	if($('#id_cliente').val()!=0)
		$('#id_fornitore').val(0);
}

function fornitore_change()
{
	if($('#id_fornitore').val()!=0)
		$('#id_cliente').val(0);
}

function ddt_add()
{
	showWait();
	$.post('include/ddt.php', { op: "add" }, 
		function(data) 
		{
			$('#titolo').html('DDT - nuovo');
			$('#content').html(data);
			$('#id_cliente').change(cliente_change);
			$('#id_fornitore').change(fornitore_change);
			$('#submit').bind('click', ddt_form_submit);
			$('#cancel').bind('click', ddt_form_cancel);
			showform();
			$('#data').datepicker('setDate', 'today');
			$('#data_trasporto').datepicker('setDate', 'today');
		});
}

function ddt_edit()
{
	showWait();
	$.post('include/ddt.php', { op: "edit", id:  flexiItemId}, 
		function(data) 
		{
			$('#titolo').html('DDT - modifica');
			$('#content').html(data);
			$('#id_cliente').change(cliente_change);
			$('#id_fornitore').change(fornitore_change);
			$('#submit').bind('click', ddt_form_submit);
			$('#cancel').bind('click', ddt_form_cancel);
			showform();
		});
}


function ddt_form_submit()
{
	var notnull=new Array("data","data_trasporto","riferimento","merce_aspetto","corriere","causale");
	var magzero=new Array("id_cliente_fornitore","num_colli","peso");

	var ok=form_validate(notnull,magzero);
	ok&=validate_ddt_form_details();

	if(ok)
	{
		form_post("ddt");
		$("#flexi_table").flexReload();
		showflexi();
		$('#titolo').html('anagrafica DDT');
	}
}

function ddt_form_cancel()
{
	showflexi();
	$('#titolo').html('anagrafica DDT');
}

function ddt_delete()
{
	var id=flexiItemId.substring(3);
	if (confirm("Rimuovo il DDT selezionato?")) 
	{ 
		showWait();
		$.post('include/ddt.php', 
			{ 
				op: "del", 
				id: id
			}, 
				function(data) 
				{
					$("#flexi_table").flexReload();
				}
			)
	}

}

function ddt_flexi()
{
	$("#flexi").html('<div id="flexi_table"></div>');
	$("#flexi_table").flexigrid({
		url: 'include/ddt.php',
			dataType: 'json',
			colModel : 
			[
				{
					display: 'Numero', 
					name : 'numero', 
					width : 50, 
					sortable : true, 
					align: 'left'
				},
				{
					display: 'Cliente/Fornitore', 
					name : 'cliente_fornitore', 
					width : 205, 
					sortable : true, 
					align: 'left'
				},
				{
					display: 'Data', 
					name : 'data', 
					width : 110, 
					sortable : true, 
					align: 'left'
				},
				{
					display: 'Data trasporto', 
					name : 'data_trasporto', 
					width : 110, 
					sortable : true, 
					align: 'left'
				},
				{
					display: 'Num Colli', 
					name : 'num_colli', 
					width : 40, 
					sortable : true, 
					align: 'left'
				},
				{
					display: 'Peso', 
					name : 'peso', 
					width : 40, 
					sortable : true, 
					align: 'left'
				},
				{
					display: 'Aspetto', 
					name : 'merce_aspetto', 
					width : 110, 
					sortable : true, 
					align: 'left'
				},
				{
					display: 'Trasporto a cura', 
					name : 'corriere', 
					width : 110, 
					sortable : true, 
					align: 'left'
				},
				{
					display: 'Causale', 
					name : 'causale', 
					width : 210, 
					sortable : true, 
					align: 'left'
				}
			],
			buttons : 
			[
				{
					name: 'Add', 
					bclass: 'add', 
					onpress : ddt_add
				},
				{
					name: 'Edit', 
					bclass: 'edit', 
					onpress : ddt_edit
				},
				{
					name: 'Print', 
					bclass: 'print', 
					onpress : ddt_print
				},
				{
					name: 'Delete', 
					bclass: 'delete', 
					onpress : ddt_delete
				},
				{
					separator: true
				}			
			],
			searchitems : 
			[
				{
					display: 'Numero', 
					name : 'numero',
					isdefault: true
				},
				{
					display: 'Data DDT', 
					name : 'data'
				},
				{
					display: 'Data trasporto', 
					name : 'data_trasporto'
				},
				{
					display: 'Cliente/Fornitore', 
					name : 'cliente_fornitore'
				},
				{
					display: 'Trasporto a cura', 
					name : 'corriere'
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
			onRowSelected:ddt_row_selected,
			onRowSelectedClick:ddt_row_selected_click,
			onRowDeselected:ddt_row_deselected,
		}
	);
	showflexi();
}

function ddt_row_selected(itemId,row,grid)
{
	flexiItemId=itemId;
	$('.edit').show();
	$('.delete').show();
	$('.print').show();
}
function ddt_row_selected_click(itemId,row,grid)
{
	ddt_edit()
}
function ddt_row_deselected(itemId,row,grid)
{
	alert("row_deselected");
}

function validate_ddt_form_details()
{
	var out=true;
	var descrizione;
	var qta;
	var note;
	var j=0;
	var l=$("input[name$='_descrizione']").length;

	for(i=0;i<l;i++)
	{
		descrizione=$("#det_"+pad(i,2)+"_descrizione");
		qta=$("#det_"+pad(i,2)+"_qta");
		note=$("#det_"+pad(i,2)+"_note");

		descrizione.val($.trim(descrizione.val()));
		qta.val($.trim(qta.val()));
		note.val($.trim(note.val()));

		descrizione.removeClass("error");
		qta.removeClass("error");

		if(descrizione.val().length || qta.val().length)
		{
			j++;
			if((descrizione.val().length==0) || (qta.val().length==0))
				out=false;
			if(descrizione.val().length==0)
				descrizione.addClass("error");
			if(qta.val().length==0)
				qta.addClass("error");
		}
	}
	if(j==0)
	{
		$("#det_00_descrizione").addClass("error");
		$("#det_00_qta").addClass("error");
		out=false;
	}
	return out;
}

function ddt_print()
{
	var mapForm = document.createElement("form");
    mapForm.target = "Map";
    mapForm.method = "POST";
    mapForm.action = "include/ddt.php";

    var mapInput = document.createElement("input");
    mapInput.type = "hidden";
    mapInput.name = "op";
    mapInput.value = 'print';
    mapForm.appendChild(mapInput);

    var mapInput = document.createElement("input");
    mapInput.type = "hidden";
    mapInput.name = "id";
    mapInput.value = flexiItemId;
    mapForm.appendChild(mapInput);
    document.body.appendChild(mapForm);

    map = window.open("", "Map", "status=0,title=0,height=600,width=800,scrollbars=1");
	if (map) 
		mapForm.submit();
	else
		alert('You must allow popups for this map to work.');
	document.body.removeChild(mapForm);
}
