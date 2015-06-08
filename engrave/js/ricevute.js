function list_ricevute()
{
	$('#titolo').html('Elenco ricevute');
	ricevute_flexi();
}

function toolbar_ricevute()
{
	show_headers(true);
	list_ricevute();
}

function ricevute_add()
{
	showWait();
	$.post('include/ricevute.php', { op: "add" }, 
		function(data) 
		{
			$('#titolo').html('ricevute - nuova');
			$('#content').html(data);
			$('#submit').bind('click', ricevute_form_submit);
			$('#cancel').bind('click', ricevute_form_cancel);
			$('#importo').val($('#importo').val().replace(".", ","));
			$('#data_incasso').after("<input type='button' value='clr' onclick='$(\"#data_incasso\").val(\"\")')>");

			$('#importo').keydown(
				function(e) 
				{
					return onlyNumbersFloat(e,this);
				}
			);
			showform();
		});
}

function ricevute_edit()
{
	showWait();
	$.post('include/ricevute.php', { op: "edit", id:  flexiItemId}, 
		function(data) 
		{
			$('#titolo').html('ricevute - modifica');
			$('#content').html(data);
			$('#submit').bind('click', ricevute_form_submit);
			$('#cancel').bind('click', ricevute_form_cancel);
			$('#importo').val($('#importo').val().replace(".", ","));
			$('#data_incasso').after("<input type='button' value='clr' onclick='$(\"#data_incasso\").val(\"\")')>");

			$('#importo').keydown(
				function(e) 
				{
					return onlyNumbersFloat(e,this);
				}
			);
			showform();
		});
}


function ricevute_form_submit()
{
	var notnull=new Array("data","cliente");
	var magzero=new Array("id_cliente");
		
	var ok=form_validate(notnull,magzero);
//	ok&=validate_ricevute_form_details();
	var importo=$('#importo').val().replace(",", ".");
	var ok=true;
	if(Number(importo)<=0)
	{
		ok=false;
		$('#importo').addClass("error");
	}
	else
		$('#importo').removeClass("error");
	
	if(ok)
	{
		$('#importo').val(importo);
		form_post("ricevute");
		$("#flexi_table").flexReload();
		showflexi();
		$('#titolo').html('Elenco ricevute');
	}
}


function validate_ricevute_form_details()
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

		descrizione.val($.trim(descrizione.val()));
		importo.val($.trim(importo.val()));

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


function ricevute_form_cancel()
{
	showflexi();
	$('#titolo').html('Elenco ricevute');
}

function ricevute_delete()
{
	var id=flexiItemId.substring(3);
	if (confirm("Rimuovo la fattura selezionata?")) 
	{ 
		$.post('include/ricevute.php', 
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

function ricevute_flexi()
{
	$("#flexi").html('<div id="flexi_table"></div>');
	$("#flexi_table").flexigrid({
		url: 'include/ricevute.php',
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
					display: 'Data', 
					name : 'data', 
					width : 110, 
					sortable : true, 
					align: 'left'
				},
				{
					display: 'Cliente / Descrizione', 
					name : 'cliente', 
					width : 205, 
					sortable : true, 
					align: 'left'
				},
				{
					display: 'Importo', 
					name : 'importo', 
					width : 80, 
					sortable : true, 
					align: 'left'
				},
				{
					display: 'Data Incasso', 
					name : 'data_incasso', 
					width : 110, 
					sortable : true, 
					align: 'left'
				},
				{
					display: 'Contanti', 
					name : 'contanti', 
					width : 50, 
					sortable : true, 
					align: 'left'
				},
				{
					display: 'Bonifico', 
					name : 'bonifico', 
					width : 50, 
					sortable : true, 
					align: 'left'
				}
			],
			buttons : 
			[
				{
					name: 'Add', 
					bclass: 'add', 
					onpress : ricevute_add
				},
				{
					name: 'Edit', 
					bclass: 'edit', 
					onpress : ricevute_edit
				},
				{
					name: 'Delete', 
					bclass: 'delete', 
					onpress : ricevute_delete
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
					display: 'Importo', 
					name : 'importo', 
				},
				{
					display: 'Data Incasso', 
					name : 'data_incasso', 
				},
				{
					display: 'Contanti', 
					name : 'contanti', 
				},
				{
					display: 'Bonifico', 
					name : 'bonifico', 
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
			onRowSelected:ricevute_row_selected,
			onRowSelectedClick:ricevute_row_selected_click,
			onRowDeselected:ricevute_row_deselected,
		}
	);
	showflexi();
}

function ricevute_row_selected(itemId,row,grid)
{
	flexiItemId=itemId;
	$('.edit').show();
	$('.delete').show();
}
function ricevute_row_selected_click(itemId,row,grid)
{
	ricevute_edit()
}
function ricevute_row_deselected(itemId,row,grid)
{
	alert("row_deselected");
}


function toolbar_corrispettivi()
{
	show_headers(true);
	select_corrispettivi_print();
}


function select_corrispettivi_print()
{
	showWait();
	$.post('include/stampe.php', { op: "select_corrispettivi_print" }, 
		function(data) 
		{
			$('#titolo').html('Registro dei corrispettivi');
			$('#content').html(data);
			showform();
		});
}
