function list_clienti()
{
	$('#titolo').html('Anagrafica clienti');
	clienti_flexi();
}

function toolbar_clienti()
{
	show_headers(true);
	list_clienti();
}

function clienti_add()
{
	showWait();
	$.post('include/clienti.php', { op: "add" }, 
		function(data) 
		{
			$('#titolo').html('clienti - nuovo');
			$('#content').html(data);
			$('#submit').bind('click', clienti_form_submit);
			$('#cancel').bind('click', clienti_form_cancel);
			$('#provincia_legale').bind('change', clienti_provincia_changed);
			$('#citta_legale').autocomplete("include/autocompleteCittaBackend.php", 
				{
					minChars:1, 
					matchSubset:1, 
					matchContains:0, 
					cacheLength:10, 
					formatItem:function(row) {
						return "<b>" + row[0] + "("+row[1]+")</b>"+
								"<br><i>"+row[4]+"</i>";
					},
					onItemSelect:function(row) {
							clientiFillProvinciaLegale(row.extra)
						},
//					extraParams:{from:$('#fromPlaceSelect').val(),exclude: excludeString,sn: 1},
//					rowNumber:n,
					selectOnly:1,
					mustMatch:1
				}); 
			$('#citta_destinazione').autocomplete("include/autocompleteCittaBackend.php", 
				{
					minChars:1, 
					matchSubset:1, 
					matchContains:0, 
					cacheLength:10, 
					formatItem:function(row) {
						return "<b>" + row[0] + "("+row[1]+")</b>"+
								"<br><i>"+row[4]+"</i>";
					},
					onItemSelect:function(row) {
							clientiFillProvinciaDestinazione(row.extra)
						},
//					extraParams:{from:$('#fromPlaceSelect').val(),exclude: excludeString,sn: 1},
//					rowNumber:n,
					selectOnly:1,
					mustMatch:1
				}); 
//				if(toFocus)
//					$('#citta').focus();
			showform();
		});
}

function clienti_edit()
{
	showWait();
	$.post('include/clienti.php', { op: "edit", id:  flexiItemId}, 
		function(data) 
		{
			$('#titolo').html('clienti - modifica');
			$('#content').html(data);
			var citta=$('#citta_legale').val();
			$('#submit').bind('click', clienti_form_submit);
			$('#cancel').bind('click', clienti_form_cancel);
			$('#provincia_legale').bind('change', clienti_provincia_changed);

			$('#citta_legale').autocomplete("include/autocompleteCittaBackend.php", 
				{
					minChars:1, 
					matchSubset:1, 
					matchContains:1, 
					cacheLength:10, 
					formatItem:function(row) {
						return "<b>" + row[0] + "("+row[1]+")</b>"+
								"<br><i>"+row[4]+"</i>";
					},
					onItemSelect:function(row) {
							clientiFillProvinciaLegale(row.extra)
						},
//					extraParams:{from:$('#fromPlaceSelect').val(),exclude: excludeString,sn: 1},
//					rowNumber:n,
					selectOnly:1,
					mustMatch:1
				}); 
			$('#citta_legale').val(citta);

			citta=$('#citta_destinazione').val();
			$('#citta_destinazione').autocomplete("include/autocompleteCittaBackend.php", 
				{
					minChars:1, 
					matchSubset:1, 
					matchContains:1, 
					cacheLength:10, 
					formatItem:function(row) {
						return "<b>" + row[0] + "("+row[1]+")</b>"+
								"<br><i>"+row[4]+"</i>";
					},
					onItemSelect:function(row) {
							clientiFillProvinciaDestinazione(row.extra)
						},
//					extraParams:{from:$('#fromPlaceSelect').val(),exclude: excludeString,sn: 1},
//					rowNumber:n,
					selectOnly:1,
					mustMatch:1
				}); 

//				if(toFocus)
//					$('#citta').focus();

			$('#citta_destinazione').val(citta);

			showform();
		});
}

function clienti_provincia_changed()
{
	
}

function clienti_form_submit()
{
	var notnull=new Array("denominazione","indirizzo_legale","citta_legale","cap_legale");
	var magzero=new Array("provincia_legale");

	var ok=form_validate(notnull,magzero);
	ok=ok&&form_validate_email(); 

	if(ok)
	{
		form_post("anag_clienti");
		$("#flexi_table").flexReload();
		showflexi();
		$('#titolo').html('Anagrafica clienti');
	}
}


function form_validate_email()
{
	var email=$.trim($("#email").val());
	$("#email").val(email);
	var out;
	var emailok=validateEmail(email); 
	var mailingListChecked=$("#mailing_list").is(':checked');
	if((mailingListChecked && (!emailok))||((!emailok)&&(email.length>0)))
	{
		out=false;
		$("#email").addClass("error");
	}
	else
	{
		out=true;
		$("#email").removeClass("error");
	}
	return out;
}



function clienti_form_cancel()
{
	showflexi();
	$('#titolo').html('Anagrafica clienti');
}

function clienti_delete()
{
	var id=flexiItemId.substring(3);
	if (confirm("Rimuovo il cliente selezionato?")) 
	{ 
		showWait();
		$.post('include/clienti.php', 
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

function clienti_flexi()
{
	$("#flexi").html('<div id="flexi_table"></div>');
	$("#flexi_table").flexigrid({
		url: 'include/clienti.php',
			dataType: 'json',
			colModel : 
			[
				{
					display: 'Denominazione', 
					name : 'denominazione', 
					width : 205, 
					sortable : true, 
					align: 'left'
				},
				{
					display: 'Indirizzo', 
					name : 'indirizzo_legale', 
					width : 205, 
					sortable : true, 
					align: 'left'
				},
				{
					display: 'Citt&agrave;', 
					name : 'citta_legale', 
					width : 110, 
					sortable : true, 
					align: 'left'
				},
				{
					display: 'Prov', 
					name : 'provincia_legale', 
					width : 40, 
					sortable : true, 
					align: 'left'
				},
				{
					display: 'CAP', 
					name : 'cap_legale', 
					width : 40, 
					sortable : true, 
					align: 'left'
				},
				{
					display: 'P.IVA', 
					name : 'piva', 
					width : 110, 
					sortable : true, 
					align: 'left'
				},
				{
					display: 'CF', 
					name : 'cf', 
					width : 110, 
					sortable : true, 
					align: 'left'
				},
				{
					display: 'email', 
					name : 'email', 
					width : 110, 
					sortable : true, 
					align: 'left'
				},
				{
					display: 'Mailing List', 
					name : 'mailing_list', 
					width : 55, 
					sortable : true, 
					align: 'left'
				},
				{
					display: 'Note', 
					name : 'note', 
					width : 205, 
					sortable : true, 
					align: 'left'
				}
			],
			buttons : 
			[
				{
					name: 'Add', 
					bclass: 'add', 
					onpress : clienti_add
				},
				{
					name: 'Edit', 
					bclass: 'edit', 
					onpress : clienti_edit
				},
				{
					name: 'Delete', 
					bclass: 'delete', 
					onpress : clienti_delete
				},
				{
					separator: true
				}			
			],
			searchitems : 
			[
				{
					display: 'Denominazione', 
					name : 'denominazione'
				},
				{
					display: 'Indirizzo', 
					name : 'indirizzo_legale'
				},
				{
					display: 'Citt&agrave;', 
					name : 'citta_legale', 
					isdefault: true
				},
				{
					display: 'Provincia', 
					name : 'loc_province.provincia'
				},
				{
					display: 'CAP', 
					name : 'cap_legale'
				},
				{
					display: 'P.IVA', 
					name : 'piva'
				},
				{
					display: 'CF', 
					name : 'cf'
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
			sortname: "denominazione",
			sortorder: "asc",
			usepager: true,
			useRp: true,
			rp: 40,
			showTableToggleBtn: false,
			showToggleBtn: false,
			resizable: false,
			width: 'auto',
			height: 'auto',
			singleSelect: true,
			onRowSelected:clienti_row_selected,
			onRowSelectedClick:clienti_row_selected_click,
			onRowDeselected:clienti_row_deselected,
		}
	);
	showflexi();
}

function clienti_row_selected(itemId,row,grid)
{
	flexiItemId=itemId;
	$('.edit').show();
	$('.delete').show();
}
function clienti_row_selected_click(itemId,row,grid)
{
	clienti_edit()
}
function clienti_row_deselected(itemId,row,grid)
{
	alert("row_deselected");
}

function clientiFillProvinciaLegale(extra)
{
	if ((extra != null) && (extra != ""))
	{
		$('#provincia_legale').val(extra[2]);
		$('#cap_legale').val(extra[3]);
	}
}

function clientiFillProvinciaDestinazione(extra)
{
	if ((extra != null) && (extra != ""))
	{
		$('#provincia_destinazione').val(extra[2]);
		$('#cap_destinazione').val(extra[3]);
	}
}
