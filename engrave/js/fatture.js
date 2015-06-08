function list_fatture()
{
	$('#titolo').html('Elenco fatture');
	fatture_flexi();
}

function toolbar_select_fatture()
{
	show_headers(true);
	select_fatture_print();
}

function toolbar_fatture()
{
	show_headers(true);
	list_fatture();
}

function select_fatture_print()
{
	showWait();
	$.post('include/stampe.php', { op: "select_fatture_print" }, 
		function(data) 
		{
			$('#titolo').html('Fatture - stampe');
			$('#content').html(data);
			showform();
		});
}

function fatture_add()
{
	showWait();
	$.post('include/fatture.php', { op: "add" }, 
		function(data) 
		{
			$('#titolo').html('Fatture - nuova');
			$('#content').html(data);
			$('#submit').bind('click', fatture_form_submit);
			$('#cancel').bind('click', fatture_form_cancel);

			$('input[name*="_prezzo"]').keydown(
				function(e) 
				{
					return onlyNumbersFloat(e,this);
				}
			);
			$('input[name*="_prezzo"]').addClass("right");
			showform();
			$('#data').datepicker('setDate', 'today');
		});
}

function fatture_edit()
{
	showWait();
	$.post('include/fatture.php', { op: "edit", id:  flexiItemId}, 
		function(data) 
		{
			$('#titolo').html('Fatture - modifica');
			$('#content').html(data);
			$('#submit').bind('click', fatture_form_submit);
			$('#cancel').bind('click', fatture_form_cancel);

			$('input[name*="_prezzo"]').keydown(
				function(e) 
				{
					return onlyNumbersFloat(e,this);
				}
			);
			$('input[name*="_prezzo"]').addClass("right");
			var l=$("input[name$='_prezzo']").length;
			for(i=0;i<l;i++)
				$('#det_'+pad(i,2)+
					'_prezzo').val($('#det_'+pad(i,2)+
					'_prezzo').val().replace(".", ","));

			showform();
		});
}


function fatture_form_submit()
{
	var notnull=new Array("data");
	var magzero=new Array("id_cliente");

	var ok=form_validate(notnull,magzero);
	ok&=validate_fatture_form_details();
	if(ok)
	{
		form_post("fatture");
		$("#flexi_table").flexReload();
		showflexi();
		$('#titolo').html('Elenco fatture');
	}
}


function validate_fatture_form_details()
{
	var out=true;
	var descrizione;
	var importo;
	var note;
	var j=0;
	var l=$("input[name$='_descrizione']").length;

	for(i=0;i<l;i++)
	{
		descrizione=$("#det_"+pad(i,2)+"_descrizione");
		importo=$("#det_"+pad(i,2)+"_prezzo");
		note=$("#det_"+pad(i,2)+"_note");

		descrizione.val($.trim(descrizione.val()));
		importo.val($.trim(importo.val()));
		note.val($.trim(note.val()));

		descrizione.removeClass("error");
		importo.removeClass("error");

		if(descrizione.val().length || importo.val().length)
		{
			j++;
			if((descrizione.val().length==0) || (importo.val().length==0))
				out=false;
			if(descrizione.val().length==0)
				descrizione.addClass("error");
			if(importo.val().length==0)
				importo.addClass("error");
		}
	}
	if((l>0)&&(j==0))
	{
		$("#det_00_descrizione").addClass("error");
		$("#det_00_prezzo").addClass("error");
		out=false;
	}
	return out;
}


function fatture_form_cancel()
{
	showflexi();
	$('#titolo').html('Elenco fatture');
}

function fatture_delete()
{
	var id=flexiItemId.substring(3);
	if (confirm("Rimuovo la fattura selezionata?")) 
	{ 
		showWait();
		$.post('include/fatture.php', 
			{ 
				op: "del", 
				id: id
			}, 
				function(data) 
				{
					$("#flexi_table").flexReload();
					showflexi();
				}
			)
	}
}

function fatture_flexi()
{
	$("#flexi").html('<div id="flexi_table"></div>');
	$("#flexi_table").flexigrid({
		url: 'include/fatture.php',
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
					display: 'Cliente', 
					name : 'cliente', 
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
					display: 'Rif. Ordine', 
					name : 'rif_ordine', 
					width : 110, 
					sortable : true, 
					align: 'left'
				},
				{
					display: 'Accomp.', 
					name : 'accompagnatoria', 
					width : 50, 
					sortable : true, 
					align: 'center'
				},
				{
					display: 'Incassata', 
					name : 'incassata', 
					width : 50, 
					sortable : true, 
					align: 'center'
				},
				{
					display: 'Note', 
					name : 'note', 
					width : 150, 
					sortable : true, 
					align: 'left'
				}
			],
			buttons : 
			[
				{
					name: 'Add', 
					bclass: 'add', 
					onpress : fatture_add
				},
				{
					name: 'Edit', 
					bclass: 'edit', 
					onpress : fatture_edit
				},
				{
					name: 'Print', 
					bclass: 'print', 
					onpress : fatture_print
				},
				{
					name: 'Delete', 
					bclass: 'delete', 
					onpress : fatture_delete
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
					display: 'Cliente', 
					name : 'cliente'
				},
				{
					display: 'Data', 
					name : 'data'
				},
				{
					display: 'Rif. Ordine', 
					name : 'rif_ordine'
				}
			],
			params:
			[
				{
					name:'op', 
					value: 'list'
				}
			],
			sortname: "numero",
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
			onRowSelected:fatture_row_selected,
			onRowSelectedClick:fatture_row_selected_click,
			onRowDeselected:fatture_row_deselected
		}
	);
	showflexi();
}

function fatture_row_selected(itemId,row,grid)
{
	flexiItemId=itemId;
	$('.edit').show();
	$('.delete').show();
	$('.print').show();
}
function fatture_row_selected_click(itemId,row,grid)
{
	fatture_edit()
}
function fatture_row_deselected(itemId,row,grid)
{
	alert("row_deselected");
}

function fatture_print()
{
	var mapForm = document.createElement("form");
    mapForm.target = "Map";
    mapForm.method = "POST";
    mapForm.action = "include/fatture.php";

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
