function list_fornitori()
{
	$('#titolo').html('Anagrafica fornitori');
	fornitori_flexi();
}

function toolbar_fornitori()
{
	show_headers(true);
	list_fornitori();
}

function fornitori_add()
{
	showWait();
	$.post('include/fornitori.php', { op: "add" }, 
		function(data) 
		{
			$('#titolo').html('fornitori - nuovo');
			$('#content').html(data);
			$('#submit').bind('click', fornitori_form_submit);
			$('#cancel').bind('click', fornitori_form_cancel);
			$('#provincia').bind('change', fornitori_provincia_changed);
			$('#citta').autocomplete("include/autocompleteCittaBackend.php", 
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
							fornitoriFillProvincia(row.extra)
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

function fornitori_edit()
{
	showWait();
	$.post('include/fornitori.php', { op: "edit", id:  flexiItemId}, 
		function(data) 
		{
			$('#titolo').html('fornitori - modifica');
			$('#content').html(data);
			var citta=$('#citta').val();
			$('#submit').bind('click', fornitori_form_submit);
			$('#cancel').bind('click', fornitori_form_cancel);
			$('#provincia').bind('change', fornitori_provincia_changed);

			$('#citta').autocomplete("include/autocompleteCittaBackend.php", 
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
							fornitoriFillProvincia(row.extra)
						},
//					extraParams:{from:$('#fromPlaceSelect').val(),exclude: excludeString,sn: 1},
//					rowNumber:n,
					selectOnly:1,
					mustMatch:1
				}); 
//				if(toFocus)
//					$('#citta').focus();
			$('#citta').val(citta);
			showform();
		});
}

function fornitori_provincia_changed()
{
	
}

function fornitori_form_submit()
{
	var notnull=new Array("denominazione","indirizzo","citta","cap");
	var magzero=new Array("provincia");

	var ok=form_validate(notnull,magzero);
	if(ok)
	{
		form_post("anag_fornitori");
		$("#flexi_table").flexReload();
		showflexi();
		$('#titolo').html('Anagrafica fornitori');
	}
}

function fornitori_form_cancel()
{
	showflexi();
	$('#titolo').html('Anagrafica fornitori');
}

function fornitori_delete()
{
	var id=flexiItemId.substring(3);
	if (confirm("Rimuovo il fornitore selezionato?")) 
	{ 
		showWait();
		$.post('include/fornitori.php', 
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

function fornitori_flexi()
{
	$("#flexi").html('<div id="flexi_table"></div>');
	$("#flexi_table").flexigrid({
		url: 'include/fornitori.php',
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
					name : 'indirizzo', 
					width : 205, 
					sortable : true, 
					align: 'left'
				},
				{
					display: 'Citt&agrave;', 
					name : 'citta', 
					width : 110, 
					sortable : true, 
					align: 'left'
				},
				{
					display: 'Prov', 
					name : 'provincia', 
					width : 40, 
					sortable : true, 
					align: 'left'
				},
				{
					display: 'CAP', 
					name : 'cap', 
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
					onpress : fornitori_add
				},
				{
					name: 'Edit', 
					bclass: 'edit', 
					onpress : fornitori_edit
				},
				{
					name: 'Delete', 
					bclass: 'delete', 
					onpress : fornitori_delete
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
					name : 'indirizzo'
				},
				{
					display: 'Citt&agrave;', 
					name : 'citta', 
					isdefault: true
				},
				{
					display: 'Provincia', 
					name : 'loc_province.provincia'
				},
				{
					display: 'CAP', 
					name : 'cap'
				},
				{
					display: 'P.IVA', 
					name : 'piva'
				},
				{
					display: 'CF', 
					name : 'cf'
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
			singleSelect:true,
			useRp: true,
			rp: 40,
			showTableToggleBtn: true,
			showToggleBtn: false,
			resizable: true,
			width: 'auto',
			height: 'auto',
			onRowSelected:fornitori_row_selected,
			onRowSelectedClick:fornitori_row_selected_click,
			onRowDeselected:fornitori_row_deselected,
		}
	);
	showflexi();
}

function fornitori_row_selected(itemId,row,grid)
{
	flexiItemId=itemId;
	$('.edit').show();
	$('.delete').show();
}

function fornitori_row_selected_click(itemId,row,grid)
{
	fornitori_edit()
}

function fornitori_row_deselected(itemId,row,grid)
{
}

function fornitoriFillProvincia(extra)
{
	if ((extra != null) && (extra != ""))
	{
		$('#provincia').val(extra[2]);
		$('#cap').val(extra[3]);
	}
}

