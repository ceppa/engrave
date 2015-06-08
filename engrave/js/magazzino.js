function list_magazzino()
{
	$('#titolo').html('Situazione magazzino');
	magazzino_flexi();
}

function toolbar_magazzino()
{
	show_headers(true);
	list_magazzino();
}


function magazzino_flexi()
{
	$("#flexi").html('<div id="flexi_table"></div>');
	$("#flexi_table").flexigrid({
		url: 'include/magazzino.php',
			dataType: 'json',
			colModel : 
			[
				{
					display: 'Tipo', 
					name : 'tipo_materiale.description', 
					width : 110, 
					sortable : true, 
					align: 'left'
				},
				{
					display: 'Fornitore', 
					name : 'anag_fornitori.denominazione', 
					width : 200, 
					sortable : true, 
					align: 'left'
				},
				{
					display: 'Codice', 
					name : 'parti.codice', 
					width : 100, 
					sortable : true, 
					align: 'left'
				},
				{
					display: 'Descrizione', 
					name : 'parti.descrizione', 
					width : 205, 
					sortable : true, 
					align: 'left'
				},
				{
					display: 'Unità', 
					name : 'um.um', 
					width : 40, 
					sortable : false, 
					align: 'left'
				},
				{
					display: 'Qtà', 
					name : 'qta', 
					width : 80, 
					sortable : false, 
					align: 'left'
				},
				{
					display: 'Qtà tot', 
					name : 'qta', 
					width : 80, 
					sortable : false, 
					align: 'left'
				},
				{
					display: 'Scorta Minima', 
					name : 'scorta_minima', 
					width : 80, 
					sortable : false, 
					align: 'left'
				}
			],
			buttons : 
			[
				{
					name: 'Carico', 
					bclass: 'carico', 
					onpress : carico_add
				},
				{
					name: 'Scarico', 
					bclass: 'scarico', 
					onpress : scarico_add
				},
				{
					separator: true
				}			
			],
			searchitems : 
			[
				{
					display: 'Codice', 
					name : 'parti.codice',
					isdefault: true
				},
				{
					display: 'Descrizione', 
					name : 'parti.descrizione'
				}/*,
				{
					display: 'Fornitore', 
					name : 'anag_fornitori.denominazione'
				}*/
			],
			params:
			[
				{
					name:'op', 
					value: 'list'
				}
			],
			sortname: "tipo_materiale.description,anag_fornitori.denominazione,codice",
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
		}
	);
	showflexi();
}
