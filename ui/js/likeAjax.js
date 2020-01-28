$(document).ready(function() {
	var valueButton = $("#like").html();
	if (valueButton == '<i class="fas fa-heart-broken"></i> Dislike')
		var bool = 1;
	else if (valueButton == '<i class="fas fa-heart"></i> Like')
		var bool = 0;
	$("#like").click(function() {
		var urlChunks = window.location.pathname.split('/');
		var username = urlChunks[urlChunks.length - 1];

		if (bool == 0)
			bool = 1;
		else
			bool = 0;
		 $.ajax({
			url: '../../likeUserAjax',
			type: 'POST',
			data: {'username':username, 'bool':bool}
 	     });
        if (bool == 0)
			$("#like").html('<i class="fas fa-heart"></i> Like');
		else
	 		$("#like").html('<i class="fas fa-heart-broken"></i> Dislike');

	});
});