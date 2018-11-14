$(document).ready(function() {
	$('#quick-mod-select').change(function() {
		var ajax_actions = ['lock', 'unlock', 'delete_topic', 'restore_topic', 'make_normal', 'make_sticky', 'make_announce', 'make_global'];
		if (ajax_actions.indexOf($(this).val()) != -1) {
			phpbb.ajaxify({
				selector: $('#quick-mod-form'),
				refresh: $(this).attr('data-refresh') !== undefined
			});
		}
		else {
			$('#quick-mod-form').unbind('submit');
		}
	});
});