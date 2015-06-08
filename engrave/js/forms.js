function form_validate(notnull,magzero)
{
	var formok=true;
	var fields = $("#editform").serializeArray();
	jQuery.each(fields, function(i, field)
	{
		var i=jQuery.inArray(field.name, notnull);
		var j=jQuery.inArray(field.name, magzero);
		var value=jQuery.trim(field.value);
		if(((i!=-1)&&(value.length==0))||
			((j!=-1)&&(value<=0)))
		{
			$('#'+field.name).addClass("error");
			formok=false;
		}
		else
			$('#'+field.name).removeClass("error");
    });
    return formok;
}

function form_post(table)
{
	$("#editform :checkbox").each(function() 
		{
			$(this).val($(this).is(':checked'));
			$(this).attr('checked', true);
		});
	
	var postdata="op=form_posted&form_table="+table+"&";
	postdata+=$("#editform").serialize();
	showWait();
	$.ajax({
		type:'POST',
		url:'include/forms.php', 
		data: postdata,
		cache: false,
		async: false
	}).done(function(data) 
		{
			if(data.length>0)
				alert(data);
		});
}

