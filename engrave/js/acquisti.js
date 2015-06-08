var AcquistiNotNull=new Array("numero_fattura","data_fattura","data_pagamento","imponibile","valore_fattura");
var AcquistiMagZero=new Array("id_fornitore","id_modalita_pagamento","id_tipologia");

function list_acquisti()
{
	$('#titolo').html('anagrafica Acquisti');
	acquisti_flexi();
}

function toolbar_acquisti()
{
	show_headers(true);
	list_acquisti();
}


function acquisti_add()
{
	showWait();
	$.post('include/acquisti.php', { op: "add" },
		function(data)
		{
			$('#titolo').html('Acquisti - nuovo');
			$('#content').html(data);
			highlightMandatory();
			$('#data_ddt').after("<input type='button' value='clr' onclick='$(\"#data_ddt\").val(\"\")')>");
			$('#submit').bind('click', acquisti_form_submit);
			$('#cancel').bind('click', acquisti_form_cancel);
			$('#imponibile,#valore_fattura').keydown(
				function(e)
				{
					return onlyNumbersFloat(e,this);
				}
			);
			showform();
			$('#data_fattura').datepicker('setDate', 'today');
			$('#data_pagamento').datepicker('setDate', 'today');
		});
}

function acquisti_edit()
{
	showWait();
	$.post('include/acquisti.php', { op: "edit", id:  flexiItemId},
		function(data)
		{
			$('#titolo').html('Acquisti - modifica');
			$('#content').html(data);
			highlightMandatory();
			$('#data_ddt').after("<input type='button' value='clr' onclick='$(\"#data_ddt\").val(\"\")')>");
			$('#submit').bind('click', acquisti_form_submit);
			$('#cancel').bind('click', acquisti_form_cancel);
			$('#imponibile,#valore_fattura').keydown(
				function(e)
				{
					return onlyNumbersFloat(e,this);
				}
			);
			$('#imponibile').val($('#imponibile').val().replace(".", ","));
			$('#valore_fattura').val($('#valore_fattura').val().replace(".", ","));

			showform();
		});
}


function acquisti_form_submit()
{

	var ok=form_validate(AcquistiNotNull,AcquistiMagZero);
	ok&=validate_acquisti_form_details();

	if(ok)
	{
		$('#imponibile').val($('#imponibile').val().replace(",", "."));
		$('#valore_fattura').val($('#valore_fattura').val().replace(",", "."));
		form_post("acquisti");
		$("#flexi_table").flexReload();
		showflexi();
		$('#titolo').html('anagrafica Acquisti');
	}
}

function acquisti_form_cancel()
{
	showflexi();
	$('#titolo').html('anagrafica Acquisti');
}

function acquisti_delete()
{
	var id=flexiItemId.substring(3);
	if (confirm("Rimuovo l'acquisto selezionato?"))
	{
		showWait();
		$.post('include/acquisti.php',
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

function acquisti_flexi()
{
	$("#flexi").html('<div id="flexi_table"></div>');
	$("#flexi_table").flexigrid({
		url: 'include/acquisti.php',
			dataType: 'json',
			colModel :
			[
				{
					display: 'Fornitore',
					name : 'fornitore',
					width : 205,
					sortable : true,
					align: 'left'
				},
				{
					display: 'Numero Fattura',
					name : 'numero_fattura',
					width : 110,
					sortable : true,
					align: 'left'
				},
				{
					display: 'Data Fattura',
					name : 'data_fattura',
					width : 110,
					sortable : true,
					align: 'left'
				},
				{
					display: 'Modalita Pagamento',
					name : 'modalita_pagamento',
					width : 150,
					sortable : true,
					align: 'left'
				},
				{
					display: 'Data Pagamento',
					name : 'data_pagamento',
					width : 110,
					sortable : true,
					align: 'left'
				},
				{
					display: 'P/A',
					name : 'porto_assegnato',
					width : 40,
					sortable : true,
					align: 'left'
				},
				{
					display: 'Tipologia',
					name : 'tipologia',
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
					onpress : acquisti_add
				},
				{
					name: 'Edit',
					bclass: 'edit',
					onpress : acquisti_edit
				},
				{
					name: 'Print',
					bclass: 'print',
					onpress : acquisti_print
				},
				{
					name: 'Delete',
					bclass: 'delete',
					onpress : acquisti_delete
				},
				{
					separator: true
				}
			],
			searchitems :
			[
				{
					display: 'Fornitore',
					name : 'anag_fornitori.denominazione',
					isdefault: true
				},
				{
					display: 'Numero Fattura',
					name : 'numero_fattura'
				},
				{
					display: 'Data Fattura',
					name : 'data_fattura'
				},
				{
					display: 'Modalita Pagamento',
					name : 'acquisti_modalita_pagamento.descrizione'
				},
				{
					display: 'Data Pagamento',
					name : 'data_pagamento'
				},
				{
					display: 'Porto Assegnato',
					name : 'porto_assegnato'
				},
				{
					display: 'Tipologia',
					name : 'acquisti_tipologie.descrizione'
				}
			],
			params:
			[
				{
					name:'op',
					value: 'list'
				}
			],
			sortname: "data_fattura DESC,fornitore,numero_fattura",
			sortorder: "",
			usepager: true,
			useRp: true,
			rp: 40,
			showTableToggleBtn: false,
			showToggleBtn: false,
			resizable: false,
			width: 'auto',
			height: 'auto',
			singleSelect: true,
			onRowSelected:acquisti_row_selected,
			onRowSelectedClick:acquisti_row_selected_click,
			onRowDeselected:acquisti_row_deselected,
		}
	);
	showflexi();
}

function acquisti_row_selected(itemId,row,grid)
{
	flexiItemId=itemId;
	$('.edit').show();
	$('.delete').show();
//	$('.print').show();
}
function acquisti_row_selected_click(itemId,row,grid)
{
	acquisti_edit()
}
function acquisti_row_deselected(itemId,row,grid)
{
	alert("row_deselected");
}

function validate_acquisti_form_details()
{
	var out=true;
	var descrizione;
	var qta;
	var note;
	var j=0;
	var l=$("input[name$='_descrizione']").length;
return out;
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

function acquisti_print()
{
	var mapForm = document.createElement("form");
    mapForm.target = "Map";
    mapForm.method = "POST";
    mapForm.action = "include/acquisti.php";

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

function highlightMandatory()
{
	var lista=$.merge(AcquistiNotNull, AcquistiMagZero);
	jQuery.each(lista, function(i, field)
	{
		$("#"+field).closest('td').prev('td').css("font-weight","bold");
	});
}
