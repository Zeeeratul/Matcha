$(document).ready(function() {
	$(".delete").click(function() {
		var button = $(this);
		var div = button.parent();
		var id = div.attr('id');
		$.ajax({
			url: '../../removeRow',
			type: 'POST',
			data: {'id': id},
			dataType: 'XMLHttpRequest'
 	     });
		
		div.remove();
	});
});