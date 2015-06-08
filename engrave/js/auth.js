function auth()
{
	$.post('include/auth.php', { op: "check" }, function(data)
		{
			var out=data.substr(0,1);
			var message=data.substr(1);
			if(message.length)
				notify(data);
	
			if(out=='0')
				main();
			else
				show_login();
		});
}

function show_login()
{
	show_headers(false);
	showWait();
	$.post('include/auth.php', { op: "show_login" }, function(data) {
		$('#content').html(data);
		$(':input').keypress(
		function(e) 
		{
			var keycode = (e.keyCode ? e.keyCode : e.which);
			if(keycode == '13')
				$("[name='login']").click();
		});

		showform();
		$('[name=user]').focus();
		});
}

function login_click(random_string)
{
	var user=$('[name=user]').val();
	var pass=hex_md5(random_string+hex_md5($('[name=password]').val()));

	showWait();
	$.post('include/auth.php', { op: "do_login", user: user, password: pass }, function(data)
		{
			var out=data.substr(0,1);
			var message=data.substr(1);
			if(message.length)
				notify(data);

			switch(parseInt(out))
			{
				case 0:
					main();
					break;
				case 1:
					show_login();
					break;
				case 2:
					show_expired();
					break;
			}
		});
}

function do_logout()
{
	showWait();
	show_headers(false);
	$.post('include/auth.php', { op: "do_logout" }, function(data) {
		$('#content').html(data);
		$(':input').keypress(
		function(e) 
		{
			var keycode = (e.keyCode ? e.keyCode : e.which);
			if(keycode == '13')
				//alert($("[name='login']"));
				$("[name='login']").click();
		});
		showform();
		$('[name=user]').focus();
		});
}

function show_forgotten()
{
	show_headers(false);
	$.post('include/auth.php', { op: "show_forgotten" }, function(data) {
		$('#content').html(data);
		showform();
		$('[name=loginuser]').focus();
		});

}

function post_forgotten()
{
	var user=$('[name=loginuser]').val();
	var email=$('[name=email]').val();

	$.post('include/auth.php', { op: "post_forgotten", user: user, email: email }, function(data)
		{
			var out=data.substr(0,1);
			var message=data.substr(1);
			if(message.length)
				notify(data);

			switch(out)
			{
				case '0':
					show_login();
					break;
				case '1':
					show_forgotten();
					break;
			}
		});
}

function show_expired()
{
	show_headers(false);
	$.post('include/auth.php', { op: "show_expired" }, function(data) 
	{
		$('#content').html(data);
		showform();
		$('[name=password1]').focus();
	});
}
function post_new_password()
{
	var password1=hex_md5($('[name=password1]').val());
	var password2=hex_md5($('[name=password2]').val());
	var id=$('[name=id]').val();

	if(password1!=password2)
	{
		notify("1le password non coincidono");
		return;
	}

	$.post('include/auth.php', { op: "post_new_password", id: id, password: password1 }, function(data)
		{
			var out=data.substr(0,1);
			var message=data.substr(1);
			if(message.length)
				notify(data);

			switch(out)
			{
				case '0':
					main();
					break;
				case '1':
					show_expired();
					break;
			}
		});
}
