$(document).ready(function() {

	$("a.ajax").live("click", function(event) {
		event.preventDefault();
		$.post(this.href);
	});

	$('form.ajax input[type="submit"], form.ajax button[type="submit"]').live('click', function(event) {
		event.preventDefault();
		$(this).ajaxSubmit();
		return false;
	});

	$('[rel="popover"]').popover();
	$('[rel="tooltip"]').tooltip();

});