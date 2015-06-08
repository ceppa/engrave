function toolbar_carico()
{
	show_headers(true);
	carico_list();
}


function carico_list()
{
	$('#titolo').html('Carico a magazzino');
	carico_flexi();
}

function carico_flexi()
{
	$("#flexi").html('<div id="flexi_table"></div>');
	$("#flexi_table").flexigrid({
		url: 'include/magazzino_carico.php',
			dataType: 'json',
			colModel : 
			[
/*				{
					display: 'Data Inserimento', 
					name : 'data_inserimento', 
					width : 110, 
					sortable : true, 
					align: 'left'
				},*/
				{
					display: 'Fornitore', 
					name : 'fornitore', 
					width : 205, 
					sortable : true, 
					align: 'left'
				},
				{
					display: 'Fattura', 
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
					display: 'Codice - Qta - Prezzo', 
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
					onpress : carico_add
				},
				{
					name: 'Edit', 
					bclass: 'edit', 
					onpress : carico_edit
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
					display: 'Fattura', 
					name : 'numero_fattura'
				},
				{
					display: 'Data Fattura', 
					name : 'data_fattura'
				},
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
			sortname: "data_fattura",
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
			onRowSelected:carico_row_selected,
			onRowSelectedClick:carico_row_selected_click
		}
	);
	showflexi();
}


function carico_row_selected(itemId,row,grid)
{
	flexiItemId=itemId;
	$('.edit').show();
	$('.delete').show();
}

function carico_row_selected_click(itemId,row,grid)
{
	carico_details();
}

function carico_details()
{
	showWait();
	$.post('include/magazzino_carico.php', { op: "details", id:  flexiItemId }, 
		function(data) 
		{

			$('#titolo').html('Carico a magazzino - dettagli');
			$('#content').html(data);
			$('#cancel').bind('click', carico_form_cancel);
			showform();
		});
}

function carico_add()
{
	showWait();
	$.post('include/magazzino_carico.php', { op: "add" }, 
		function(data) 
		{
			var dialog_form_html=$("#dialog-form").html();
			if(dialog_form_html!=undefined)
				$("#dialog-form").remove();
				
			carico_form('Carico a magazzino - nuovo',data);
			newPartDialog();
		});
}

function parteCaricoSelected(li)
{
	var id_row=li.rowNumber;
	var sender="#det_"+pad(id_row,2)+"_id_parte";
	var l=$.trim($(sender).val());
	if (li.itemPresent)
	{
		var id=li.extra[1];
		var valSelector="#det_"+pad(id_row,2)+"_hiddenid_parte";
		var qtySelector="#det_"+pad(id_row,2)+"_qta";
		$(valSelector).val(id);
		$(qtySelector).val(1);
		$(qtySelector).focus();
	}
	else
	{
		if(l.length)
		{
			showNewPartDialog(l,$("#id_fornitore").val(),id_row);
		}

	}
}


function carico_edit()
{
	alert("todo - come fare a non incasinare magazzino");
	return;
	$.post('include/magazzino_carico.php', { op: "edit", id:  flexiItemId}, 
		function(data) 
		{
			carico_form('Carico a magazzino - modifica',data);
		});
}


function carico_form_submit()
{
	var notnull=new Array("numero_fattura","data_fattura");
	var magzero=new Array("id_fornitore");

	var ok=form_validate(notnull,magzero);
	ok&=validate_carico_form_details();
	if(ok)
	{
		form_post("magazzino_carico");
		$("#flexi_table").flexReload();
		showflexi();
		$('#titolo').html('Carico a magazzino');
	}
}

function validate_carico_form_details()
{
	var out=true;
	var parte;
	var qta;
	var prezzo;
	var j=0;
	var l=$("input[name$='_prezzo']").length;

	for(i=0;i<l;i++)
	{
		parte=$("#det_"+pad(i,2)+"_id_parte");
		qta=$("#det_"+pad(i,2)+"_qta");
		prezzo=$("#det_"+pad(i,2)+"_prezzo");

		parte.val($.trim(parte.val()));
		qta.val($.trim(qta.val()));
		prezzo.val($.trim(prezzo.val()));

		parte.removeClass("error");
		qta.removeClass("error");
		prezzo.removeClass("error");

		if(parte.val().length || qta.val().length || prezzo.val().length)
		{
			j++;
			if((parte.val().length==0) || (qta.val().length==0) || (prezzo.val().length==0))
				out=false;
			if(parte.val().length==0)
				parte.addClass("error");
			if(qta.val().length==0)
				qta.addClass("error");
			if(prezzo.val().length==0)
				prezzo.addClass("error");
		}
	}
	if(j==0)
	{
		$("#det_00_id_parte").addClass("error");
		$("#det_00_qta").addClass("error");
		$("#det_00_prezzo").addClass("error");
		out=false;
	}
	return out;
}


function carico_form_cancel()
{
	showflexi();
	$('#titolo').html('Carico a magazzino');
}


function carico_form(title,data)
{
	$('#titolo').html(title);
	$('#content').html(data);
	$('#submit').bind('click', carico_form_submit);
	$('#cancel').bind('click', carico_form_cancel);
	showform();

	$('input[name*="_prezzo"]').keydown(
		function(e) 
		{
			return onlyNumbersFloat(e,this);
		}
	);
	
	$('input[name*="_qta"]').keydown(
		function(e) 
		{
			return onlyNumbersFloat(e,this);
		}
	);


	if($('#id_fornitore').value!=undefined)
		setAutocompleteCarico($('#id_fornitore').value);

	$('#id_fornitore').change(function()
		{
			var ro=(this.value==0);
			clearCaricoDetails();
			if(!ro)
				setAutocompleteCarico(this.value)
			$('input[name*="_id_parte"]').attr('readonly', ro);
		});
	$('#id_fornitore').change();

	$('#data_fattura').datepicker('setDate', 'today');
}

function setAutocompleteCarico(id_fornitore)
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
			ac.autocomplete("include/autocompleteParteCaricoBackend.php", 
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
						parteCaricoSelected(li)
					},
	//					extraParams:{from:$('#id_fornitore').val(),exclude: excludeString,sn: 1},
						extraParams:{id_fornitore:id_fornitore,id_row:i},
				rowNumber:i,
				selectOnly:1,
				mustMatch:0,
			});
		}
		else
		{
			ac[0].autocompleter.flushCache();
			ac[0].autocompleter.setExtraParams({id_fornitore:id_fornitore});			
		}
	}
}

function clearCaricoDetails()
{
	$('input[name*="id_parte"]').val('');
	$('input[name*="_qta"]').val('');
	$('input[name*="_prezzo"]').val('');
}


function showNewPartDialog(newpart,id_fornitore,row)
{
	$("#det_"+pad(row,2)+"_hiddenid_parte").val("");
	$("#newpart_codice").val(newpart);
	$("#newpart_fornitore").val(id_fornitore);
	$("#newpart_row").val(row);
	$("#dialog-form").dialog("open");
}

function newPartDialog()
{
	var codice = $("#newpart_codice"),
			descrizione = $("#newpart_descrizione"),
			um = $("#newpart_um"),
			tipo_materiale = $("#newpart_id_tipo_materiale"),
			allFields = $( [] ).add( codice ).add( descrizione ).add( um ).add(tipo_materiale);


	$("#dialog-form").dialog({
			autoOpen: false,
			height: 420,
			width: 400,
			modal: true,
			buttons: {
				"inserisci": function() {
					var bValid = true;
					allFields.removeClass("ui-state-error");

					bValid = bValid && checkStringValue(codice);
					bValid = bValid && checkStringValue(descrizione);
					bValid = bValid && checkComboValue(um);
					bValid = bValid && checkComboValue(tipo_materiale);

					if ( bValid ) 
					{
						var data = $("fieldset#newpart_form input,fieldset#newpart_form select").serializeArray();
						data.push({ name: "op", value: "add_part" });
						
						$.ajax({
							type: 'POST',
							url: "include/magazzino_carico.php",
							data: data,
							success: function(result)
								{ 
									var row=$("#newpart_row").val();
									var l=$("input[name$='_id_parte']").length;
									for(i=0;i<l;i++)
									{
										selector="#det_"+pad(i,2)+"_id_parte";
										ac = $(selector);
										if(ac[0].autocompleter!=undefined)
											ac[0].autocompleter.flushCache();
									}
									if(result>0)
										$("#det_"+pad(row,2)+"_hiddenid_parte").val(result);
								},
								async:false
						});
						$(this).dialog("close");

					}
				},
				Cancel: function() 
				{
					$(this).dialog("close");
				}
			},
			close: function() 
			{
				var row=$("#newpart_row").val();
				allFields.val("").removeClass("ui-state-error");
				if($("#det_"+pad(row,2)+"_hiddenid_parte").val()=="")
				{
					$("#det_"+pad(row,2)+"_id_parte").val("");
					$("#det_"+pad(row,2)+"_id_parte").focus();
				}
				else
				{
					var qtySelector="#det_"+pad(row,2)+"_qta";
					$(qtySelector).val(1);
					$(qtySelector).focus();
				}
			}
		});

}

function checkStringValue(field)
{
	if(field.val().length==0) 
	{
		field.addClass("ui-state-error");
		field.focus();
		return false;
	}
	else
		return true;
}

function checkComboValue(field)
{
	if(field.val()==0) 
	{
		field.addClass("ui-state-error");
		field.focus();
		return false;
	}
	else
		return true;
}
