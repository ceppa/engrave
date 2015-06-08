function toolbar_scarico()
{
	show_headers(true);
	scarico_list();
}


function scarico_list()
{
	$('#titolo').html('Scarico da magazzino');
	scarico_flexi();
}

function scarico_flexi()
{
	$("#flexi").html('<div id="flexi_table"></div>');
	$("#flexi_table").flexigrid({
		url: 'include/magazzino_scarico.php',
			dataType: 'json',
			colModel : 
			[
/*				{
					display: 'Data inserimento', 
					name : 'data_inserimento', 
					width : 100, 
					sortable : true, 
					align: 'left'
				},*/
				{
					display: 'Data Fattura/Ins', 
					name : 'data_movimento', 
					width : 120, 
					sortable : true, 
					align: 'left'
				},
				{
					display: 'Fattura', 
					name : 'numero_fattura', 
					width : 55, 
					sortable : true, 
					align: 'left'
				},
/*				{
					display: 'Ricevuta', 
					name : 'numero_ricevuta', 
					width : 55, 
					sortable : true, 
					align: 'left'
				},*/
				{
					display: 'Altro', 
					name : 'altra_destinazione', 
					width : 55, 
					sortable : true, 
					align: 'left'
				},
				{
					display: 'Codice - Qta', 
					name : 'lista_parti', 
					width : 410, 
					sortable : true, 
					align: 'left'
				}
			],
			buttons : 
			[
				{
					name: 'Add', 
					bclass: 'add', 
					onpress : scarico_add
				},
				{
					name: 'Edit', 
					bclass: 'edit', 
					onpress : scarico_edit
				},
				{
					separator: true
				}			
			],
			searchitems : 
			[
/*				{
					display: 'Data inserimento', 
					name : 'data_inserimento'
				},*/
				{
					display: 'Data Fattura/Ins', 
					name : 'data_movimento'
				},
				{
					display: 'Fattura', 
					name : 'numero_fattura'
				},
/*				{
					display: 'Ricevuta', 
					name : 'numero_ricevuta'
				},*/
				{
					display: 'Parti', 
					name : 'lista_parti'
				}
			],
			params:
			[
				{
					name:'op', 
					value: 'list'
				}
			],
			sortname: "data_movimento",
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
			onRowSelected:scarico_row_selected,
			onRowSelectedClick:scarico_row_selected_click
		}
	);
	showflexi();
}



function scarico_row_selected_click(itemId,row,grid)
{
	scarico_details();
}

function scarico_details()
{
	showWait();
	$.post('include/magazzino_scarico.php', { op: "details", id:  flexiItemId }, 
		function(data) 
		{

			$('#titolo').html('Scarico a magazzino - dettagli');
			$('#content').html(data);
			$('#cancel').bind('click', scarico_form_cancel);
			showform();
		});
}

function scarico_row_selected(itemId,row,grid)
{
	flexiItemId=itemId;
//	$('.edit').show();
//	$('.delete').show();
}

function scarico_add()
{
	showWait();
	$.post('include/magazzino_scarico.php', { op: "add" }, 
		function(data) 
		{
			scarico_form('Scarico da magazzino - nuovo',data);
			setAutocompleteScarico()

			$("#id_fattura").autocomplete("include/autocompleteFatturaBackend.php", 
			{
				minChars:1, 
				matchSubset:true, 
				matchContains:1, 
				cacheLength:10, 
				formatItem:function(row) {
					return "<b>"+row[0]+"</b>"+
							"<br><i>"+row[1]+"</i>";
				},
				onItemSelect:function(li) 
					{
						fatturaSelected(li)
					},
	//					extraParams:{from:$('#id_fornitore').val(),exclude: excludeString,sn: 1},
	//					extraParams:{id_fornitore:id_fornitore,id_row:i},
				rowNumber:i,
				selectOnly:1,
				mustMatch:1,
			});

/*			$("#id_ricevuta").autocomplete("include/autocompleteRicevutaBackend.php", 
			{
				minChars:1, 
				matchSubset:true, 
				matchContains:1, 
				cacheLength:10, 
				formatItem:function(row) {
					return "<b>"+row[0]+"</b>"+
							"<br><i>"+row[1]+"</i>";
				},
				onItemSelect:function(li) 
					{
						ricevutaSelected(li)
					},
	//					extraParams:{from:$('#id_fornitore').val(),exclude: excludeString,sn: 1},
	//					extraParams:{id_fornitore:id_fornitore,id_row:i},
				rowNumber:i,
				selectOnly:1,
				mustMatch:1,
			});
*/
			$("#id_magazzino_scarico_lost").autocomplete("include/autocompleteAltraDestinazioneBackend.php", 
			{
				minChars:1, 
				matchSubset:true, 
				matchContains:1, 
				cacheLength:10, 
				formatItem:function(row) {
					return "<b>"+row[0]+"</b>";
				},
				onItemSelect:function(li) 
					{
						magazzino_scarico_lostSelected(li);
					},
				rowNumber:i,
				selectOnly:1,
				mustMatch:0,
			});
			$('#id_fattura').focus();
			parteOnFocus();
		});
		
}

function deleteDataScarico()
{
	$("#data_row").remove();
}

function addDataScarico()
{
	var htmlCode='<tr id="data_row">'+
			'<td class="right">'+
			'data'+
			'</td>'+
			'<td class="left">'+
			'<input type="text" id="data_inserimento" name="data_inserimento">'+
			'</td></tr>';

	if($("#data_row").length == 0)
	{
		$('.form tr:last').after(htmlCode);	
		$("#data_inserimento").attr('readonly', true);
		$("#data_inserimento").datepicker({ dateFormat: "yy-mm-dd" });
		$('#data_inserimento').datepicker('setDate', 'today');
	}
}

function fatturaSelected(li)
{
	var sender=$("#id_fattura");
//	var dest=$("#id_ricevuta");
	var dest2=$("#id_magazzino_scarico_lost");
	var l=$.trim(sender.val());
	var id_fattura_ricevuta=$("#id_fattura_ricevuta");
	if(l.length)
	{
//		dest.val("");
		dest2.val("");
		id_fattura_ricevuta.val(li.extra[1]);
		clearDetails();
		deleteDataScarico();
	}
}


/*
function ricevutaSelected(li)
{
	var sender=$("#id_ricevuta");
	var dest=$("#id_fattura");
	var dest2=$("#id_magazzino_scarico_lost");
	var l=$.trim(sender.val());
	var id_fattura_ricevuta=$("#id_fattura_ricevuta");
	if(l.length)
	{
		dest.val("");
		dest2.val("");
		id_fattura_ricevuta.val(li.extra[1]);
		clearDetails();
	}
}
*/

function magazzino_scarico_lostSelected(li)
{
	var sender=$("#id_magazzino_scarico_lost");
	var dest=$("#id_fattura");
//	var dest2=$("#id_ricevuta");
	var l=$.trim(sender.val());
	var id_fattura_ricevuta=$("#id_fattura_ricevuta");
	if(l.length)
	{
		dest.val("");
//		dest2.val("");
		id_fattura_ricevuta.val(li.length?li.extra[0]:0);
		clearDetails();
		addDataScarico();
	}
}


function scarico_edit()
{
}

function setAutocompleteScarico()
{
	var i;
	var selector;
	var ac;
	var l=$("input[name$='_id_parte']").length;
	for(i=0;i<l;i++)
	{
		selector="#det_"+pad(i,2)+"_id_parte";
		ac = $(selector);
		if(ac[0].autocompleter==undefined)
		{
			ac.autocomplete("include/autocompleteParteScaricoBackend.php", 
			{
				minChars:1, 
				matchSubset:true, 
				matchContains:1, 
				cacheLength:10, 
				formatItem:function(row) {
					return "<b>"+row[0]+"</b>"+
							"<br><i>"+row[1]+"</i>";
				},
				onItemSelect:function(li) {
						parteScaricoSelected(li)
					},
	//					extraParams:{from:$('#id_fornitore').val(),exclude: excludeString,sn: 1},
	//					extraParams:{id_fornitore:id_fornitore,id_row:i},
				rowNumber:i,
				selectOnly:1,
				mustMatch:1,
			});
		}
		else
		{
			ac[0].autocompleter.flushCache();
			ac[0].autocompleter.setExtraParams({id_fornitore:id_fornitore});			
		}
	}
}


function parteScaricoSelected(li)
{
	var id_row=li.rowNumber;
	var sender="#det_"+pad(id_row,2)+"_id_parte";
	var l=$.trim($(sender).val());
	var valSelector="#det_"+pad(id_row,2)+"_hiddenid_parte";
	var qtySelector="#det_"+pad(id_row,2)+"_qta";
	if (li.extra.length)
	{
		var id=li.extra[1];
		var qta=li.extra[2];
		$(valSelector).val(id);
		$(qtySelector).val(qta);
		$(qtySelector).focus();
	}
	else
	{
		$(valSelector).val("");
		$(qtySelector).val("");
	}
}

function scarico_form(title,data)
{
	$('#titolo').html(title);
	$('#content').html(data);
	$('#submit').bind('click', scarico_form_submit);
	$('#cancel').bind('click', scarico_form_cancel);
	showform();
	
	$('input[name*="_qta"]').keydown(
		function(e) 
		{
			return onlyNumbersFloat(e,this);
		}
	);

}

function scarico_form_submit()
{
	var fattura=$("#id_fattura");
//	var ricevuta=$("#id_ricevuta");
	var magazzino_scarico_lost=$("#id_magazzino_scarico_lost");
	var ok=((fattura.val().length>0)||(magazzino_scarico_lost.val().length>0));
	if(!ok)
	{
		fattura.addClass("error");
//		ricevuta.addClass("error");
		magazzino_scarico_lost.addClass("error");
	}
	ok&=validate_scarico_form_details();
	if($("#id_fattura_ricevuta").val()==0)
		ok&=insertNewLost(magazzino_scarico_lost.val());

	if(ok)
	{
		
		if($("#data_inserimento").val()==dateNow())
			$("#data_row").remove();

		var l=$("input[name$='_qta']").length;
		for(i=0;i<l;i++)
		{
			qta=$("#det_"+pad(i,2)+"_qta");
			if(qta.val().length>0)
				qta.val("-"+(qta.val()));
		}

		if(fattura.val().length>0)
			fattura.val($("#id_fattura_ricevuta").val());
		else
			magazzino_scarico_lost.val($("#id_fattura_ricevuta").val());
		form_post("magazzino_scarico");
		$("#flexi_table").flexReload();
		showflexi();
		$('#titolo').html('Sarico da magazzino');
	}
}

function scarico_form_cancel()
{
	showflexi();
	$('#titolo').html('Scarico a magazzino');
}

function validate_scarico_form_details()
{
	var out=true;
	var parte;
	var qta;
	var prezzo;
	var j=0;
	var l=$("input[name$='_id_parte']").length;

	for(i=0;i<l;i++)
	{
		parte=$("#det_"+pad(i,2)+"_id_parte");
		qta=$("#det_"+pad(i,2)+"_qta");

		parte.val($.trim(parte.val()));
		qta.val($.trim(qta.val()));

		parte.removeClass("error");
		qta.removeClass("error");

		if(parte.val().length || qta.val().length)
		{
			j++;
			if((parte.val().length==0) || (qta.val().length==0))
				out=false;
			if(parte.val().length==0)
				parte.addClass("error");
			if(qta.val().length==0)
				qta.addClass("error");
		}
	}
	if(j==0)
	{
		$("#det_00_id_parte").addClass("error");
		$("#det_00_qta").addClass("error");
		out=false;
	}
	return out;
}

function parteOnFocus()
{
	var l=$("input[name$='_id_parte']").length;

	for(i=0;i<l;i++)
	{
		$("#det_"+pad(i,2)+"_id_parte").focus(function()
			{
				var arr=this.name.split("_");
				var j=Number(arr[1]);
				setExtraParam(j);
			});
	}
}

function setExtraParam(j)
{
	var excludeString="";
	var l=$("input[name$='_hiddenid_parte']").length;

	for(i=0;i<l;i++)
	{
		if(i!=j)
		{
			id_parte=$("#det_"+pad(i,2)+"_hiddenid_parte").val();
			if(id_parte!="")
				excludeString+=(id_parte+",");
		}
	}
	var ac = $("#det_"+pad(i,2)+"_id_parte")[0].autocompleter;
	ac.flushCache();
	ac.setExtraParams({exclude: excludeString});
}

function clearDetails()
{
	var l=$("input[name$='_id_parte']").length;

	for(i=0;i<l;i++)
	{
		$("#det_"+pad(i,2)+"_id_parte").val("");
		$("#det_"+pad(i,2)+"_hiddenid_parte").val(0);
		$("#det_"+pad(i,2)+"_qta").val("");
	}
}

function insertNewLost(newLost)
{
	var out=false
	$.ajax({
			async: false,
			url: 'include/magazzino_scarico.php',
			type: 'POST',
			data: 'op=newLost&newLost=' + newLost,
			success: function(resp) 
			{
				if($.isNumeric(resp))
				{
					$("#id_fattura_ricevuta").val(resp);
					out=true;
				}
			}
	});
	return out;
}

