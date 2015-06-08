function toolbar_users()
{
	show_headers(true);
	users_list();
}

function users_list()
{
	showWait();
	$.post('include/users.php', { op: "list" }, 
		function(data) 
		{
			$('#titolo').html('gestione utenti - elenco');
			$('#content').html(data);
			showform();
		});
}

function users_add()
{
	show_headers(true);
	showWait();
	$.post('include/users.php', { op: "add" }, 
		function(data) 
		{
			$('#titolo').html('gestione utenti - nuovo');
			$('#content').html(data);
			$('#to_focus').focus();
			showform();
		});
}

function user_edit(id)
{
	show_headers(true);
	showWait();
	$.post('include/users.php', { op: "edit",user_to_edit: id }, 
		function(data) 
		{
			$('#titolo').html('gestione utenti - modifica');
			$('#content').html(data);
			$('#to_focus').focus();
			showform();
		});
}

function users_post(form)
{
	if(check_post($(form)))
	{
		var parm=$(form).serialize();
		showWait();

		$.post('include/users.php', parm, 
			function(data) 
			{
				var message='';
				var message_end=data.indexOf('|');
				if(message_end!=-1)
				{
					message=data.substr(0,message_end);
					notify(message);
					data=data.substr(message_end+1);
				}
				$('#titolo').html('gestione utenti - elenco');
				$('#content').html(data);	
				showform();
			});
	}
}

function check_post(form)
{
	var out=true;
	var email=form.find('[name=email]').val();
	var utente=trim(form.find('[name=utente]').val());
	var id=(form.find('[name=user_to_edit]')?form.find('[name=user_to_edit]').val():0);

	if(utente.length==0)
	{
		notify("1utente non valido");
		return false;
	}
	$.ajax({
			type:'POST',
			url:'include/users.php', 
			data:{ op: "checkduplicate",id: id,user:utente },
			cache: false,
			async: false
		}).done(function(data) 
			{
				if(data!='0')
				{
					notify("1utente già presente");
					out=false;
				}
			});

	if((email.indexOf(".") <= 2)
		|| (email.indexOf("@") <= 0))
	{
		notify("1email non valida");
		return false;
	}
	return out;
}

function user_reset(id,messaggio)
{
	var fRet;
	fRet=confirm(messaggio);
	if(fRet)
	{
		showWait();
		$.post('include/users.php', {op: 'reset', user_to_reset: id}, 
			function(data) 
			{
				var message='';
				var message_end=data.indexOf('|');
				if(message_end!=-1)
				{
					message=data.substr(0,message_end);
					notify(message);
					data=data.substr(message_end+1);
				}
				$('#content').html(data);	
				showform();
			});
	}
}

function user_del(id,messaggio)
{
	var fRet;
	fRet=confirm(messaggio);
	if(fRet)
	{
		showWait();
		$.post('include/users.php', {op: 'del', user_to_del: id}, 
			function(data) 
			{
				var message='';
				var message_end=data.indexOf('|');
				if(message_end!=-1)
				{
					message=data.substr(0,message_end);
					notify(message);
					data=data.substr(message_end+1);
				}
				$('#content').html(data);	
				showform();
			});
	}
}
