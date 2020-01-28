$(document).ready(function() {
	var noticationHTML = $("#notif");

	$.ajax({
		url: '../../checkNotif.php',
		type: 'POST',
		dataType: 'json',
		success : function(data) {
			if (data == '1')
			{
				noticationHTML.css({"color":"black","font-size":"16px"});
				noticationHTML.html("<i class=\"fas fa-bell\"></i> My notifications");
			}
        }
	});

	setInterval(function() {

		$.ajax({
			url: '../../checkNotif.php',
			type: 'POST',
			dataType: 'json',
			success : function(data) {
				if (data == '1')
				{
					noticationHTML.css({"color":"black","font-size":"16px"});
					noticationHTML.html("<i class=\"fas fa-bell\"></i> My notifications");
				}
	        }
 	    });

    }, 3000);
});