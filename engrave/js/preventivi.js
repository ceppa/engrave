function list_preventivi()
{
	$('#titolo').html('Elenco preventivi');
	preventivi_flexi();
}

function toolbar_select_preventivi()
{
	show_headers(true);
	select_preventivi_print();
}

function toolbar_preventivi()
{
	show_headers(true);
	list_preventivi();
}

function select_preventivi_print()
{
	showWait();
	$.post('include/stampe.php', { op: "select_preventivi_print" }, 
		function(data) 
		{
			$('#titolo').html('preventivi - stampe');
			$('#content').html(data);
			showform();
		});
}

function preventivi_add()
{
	showWait();
	$.post('include/preventivi.php', { op: "add" }, 
		function(data) 
		{
			$('#titolo').html('preventivi - nuovo');
			$('#content').html(data);
			$('#submit').bind('click', preventivi_form_submit);
			$('#cancel').bind('click', preventivi_form_cancel);

			$('#data_approvazione').after("<input type='button' value='clr' onclick='$(\"#data_approvazione\").val(\"\")')>");

			$('#descrizione_titolo').val('In riferimento alla Vostra gentile richiesta, con la presente Vi inviamo la nostra offerta commerciale per:');
			$('#denominazione').change(denominazioneChange);
			$('#id_cliente').change(clienteChange);
			showform();
			$('#data').datepicker('setDate', 'today');
		});
}

function clienteChange(e)
{
	if(this.value>0)
		$('#denominazione').val("");
}

function denominazioneChange(e)
{
	this.value=$.trim(this.value);
	if(this.value.length>0)
		$('#id_cliente').val(0);
}

function preventivi_edit()
{
	showWait();
	$.post('include/preventivi.php', { op: "edit", id:  flexiItemId}, 
		function(data) 
		{
			$('#titolo').html('preventivi - modifica');
			$('#content').html(data);
			$('#submit').bind('click', preventivi_form_submit);
			$('#cancel').bind('click', preventivi_form_cancel);

			$('#data_approvazione').after("<input type='button' value='clr' onclick='$(\"#data_approvazione\").val(\"\")')>");

			$('#denominazione').change(denominazioneChange);
			$('#id_cliente').change(clienteChange);
			showform();
		});
}


function preventivi_form_submit()
{
	var notnull=new Array("data","descrizione_titolo","descrizione");
	var magzero=new Array();

	var ok=form_validate(notnull,magzero);
	var okDenominazione=(($("#id_cliente").val()>0)||($.trim($("#denominazione").val()).length>0));
	if(!okDenominazione)
	{
		$("#id_cliente").addClass("error");
		$("#denominazione").addClass("error");
	}
	ok=ok&&okDenominazione;
	if(ok)
	{
		form_post("preventivi");
		$("#flexi_table").flexReload();
		showflexi();
		$('#titolo').html('Elenco preventivi');
	}
}

function preventivi_form_cancel()
{
	showflexi();
	$('#titolo').html('Elenco preventivi');
}

function preventivi_delete()
{
	var id=flexiItemId.substring(3);
	if (confirm("Rimuovo il preventivo selezionato?")) 
	{ 
		showWait();
		$.post('include/preventivi.php', 
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

function preventivi_flexi()
{
	$("#flexi").html('<div id="flexi_table"></div>');
	$("#flexi_table").flexigrid({
		url: 'include/preventivi.php',
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
					display: 'Numero', 
					name : 'numero', 
					width : 110, 
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
					display: 'Data Approvazione', 
					name : 'data_approvazione', 
					width : 110, 
					sortable : true, 
					align: 'left'
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
					onpress : preventivi_add
				},
				{
					name: 'Edit', 
					bclass: 'edit', 
					onpress : preventivi_edit
				},
				{
					name: 'Print', 
					bclass: 'print', 
					onpress : preventivi_print
				},
				{
					name: 'Delete', 
					bclass: 'delete', 
					onpress : preventivi_delete
				},
				{
					separator: true
				}			
			],
			searchitems : 
			[
				{
					display: 'Cliente', 
					name : 'IFNULL(anag_clienti.denominazione,preventivi.denominazione)'
				},
				{
					display: 'Data', 
					name : 'data'
				},
				{
					display: 'Numero', 
					name : 'numero'
				},
				{
					display: 'Data approvazione', 
					name : 'data_approvazione'
				},
				{
					display: 'Note', 
					name : 'note'
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
			onRowSelected:preventivi_row_selected,
			onRowSelectedClick:preventivi_row_selected_click,
			onRowDeselected:preventivi_row_deselected
		}
	);
	showflexi();
}

function preventivi_row_selected(itemId,row,grid)
{
	flexiItemId=itemId;
	$('.edit').show();
	$('.delete').show();
	$('.print').show();
}
function preventivi_row_selected_click(itemId,row,grid)
{
	preventivi_edit()
}
function preventivi_row_deselected(itemId,row,grid)
{
	alert("row_deselected");
}

function preventivi_print()
{
	var mapForm = document.createElement("form");
    mapForm.target = "Map";
    mapForm.method = "POST";
    mapForm.action = "include/preventivi.php";

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
