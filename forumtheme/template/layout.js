$(document).ready(function() {
	$('#cq-navbar-toggle').click(function() {
		$('#cq-navbar').toggleClass('open');
	});

	function offsetAnchorClick() {
		if (location.hash.length !== 0)
			window.scrollTo(window.scrollX, window.scrollY - 50);
	}

	$(document).on('click', 'a[href^="#"]', function(evt) {
		// use click because this is caught before hashchange
		window.setTimeout(function() {
			offsetAnchorClick();
		}, 0);
	});

	window.setTimeout(offsetAnchorClick, 0);
});
