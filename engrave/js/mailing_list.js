function list_mailing_list()
{
	$('#titolo').html('Mailing List');
	mailing_list_flexi();
}

function toolbar_mailing_list()
{
	show_headers(true);
	list_mailing_list();
}

function mailing_list_add()
{
	showWait();
	$.post('include/mailing_list.php', { op: "add" }, 
		function(data) 
		{
			$('#titolo').html('mailing list - nuovo');
			$('#content').html(data);
			$('#submit').bind('click', mailing_list_form_submit);
			$('#cancel').bind('click', mailing_list_form_cancel);
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
							mailingListFillProvincia(row.extra)
						},
					selectOnly:1,
					mustMatch:1
				}); 
			showform();
		});
}

function mailing_list_edit()
{
	showWait();
	$.post('include/mailing_list.php', { op: "edit", id:  flexiItemId}, 
		function(data) 
		{
			$('#titolo').html('mailing list - modifica');
			$('#content').html(data);
			var citta=$('#citta').val();
			$('#submit').bind('click', mailing_list_form_submit);
			$('#cancel').bind('click', mailing_list_form_cancel);

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
							mailingListFillProvincia(row.extra)
						},
					selectOnly:1,
					mustMatch:1
				}); 
			$('#citta').val(citta);

			showform();
		});
}

function mailing_list_form_submit()
{
	var notnull=new Array("denominazione","indirizzo","citta");
	var magzero=new Array("provincia");

	var ok=form_validate(notnull,magzero);
	ok=ok&&form_validate_email_mailing_list(); 

	if(ok)
	{
		form_post("mailing_list");
		$("#flexi_table").flexReload();
		showflexi();
		$('#titolo').html('Mailing List');
	}
}


function form_validate_email_mailing_list()
{
	var email=$.trim($("#email").val());
	$("#email").val(email);
	var out;
	var emailok=validateEmail(email); 
	var mailingListChecked=$("#check_mailing_list").is(':checked');
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



function mailing_list_form_cancel()
{
	showflexi();
	$('#titolo').html('Mailing_list');
}

function mailing_list_delete()
{
	var id=flexiItemId.substring(3);
	if (confirm("Rimuovo il record selezionato?")) 
	{ 
		showWait();
		$.post('include/mailing_list.php', 
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

function mailing_list_flexi()
{
	$("#flexi").html('<div id="flexi_table"></div>');
	$("#flexi_table").flexigrid({
		url: 'include/mailing_list.php',
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
					display: 'email', 
					name : 'email', 
					width : 110, 
					sortable : true, 
					align: 'left'
				},
				{
					display: 'Checked', 
					name : 'check_mailing_list', 
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
					onpress : mailing_list_add
				},
				{
					name: 'Edit', 
					bclass: 'edit', 
					onpress : mailing_list_edit
				},
				{
					name: 'Delete', 
					bclass: 'delete', 
					onpress : mailing_list_delete
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
			onRowSelected:mailing_list_row_selected,
			onRowSelectedClick:mailing_list_row_selected_click,
			onRowDeselected:mailing_list_row_deselected,
		}
	);
	showflexi();
}

function mailing_list_row_selected(itemId,row,grid)
{
	flexiItemId=itemId;
	$('.edit').show();
	$('.delete').show();
}
function mailing_list_row_selected_click(itemId,row,grid)
{
	mailing_list_edit()
}
function mailing_list_row_deselected(itemId,row,grid)
{
	alert("row_deselected");
}

function mailingListFillProvincia(extra)
{
	if ((extra != null) && (extra != ""))
	{
		$('#provincia').val(extra[2]);
	}
}

