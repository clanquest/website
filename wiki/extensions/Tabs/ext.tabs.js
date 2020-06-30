jQuery(function($) {
	/**
	 * Imitates the normal feature in browsers to scroll to an id that has the same id as the url fragment/hash.
	 * This makes it unnecessary to use actual ids on the tabs, which could cause the same id to occur twice in the same document.
	 * Does not scroll to exactly the tab's height, but just a bit above it.
	 */
	function moveToHash() {
		var hash = location.hash.substr(1).replace(/_/g,' ').trim();
		if (!hash || $(location.hash).length) {
			return; // if there's no hash defined, or an element on the page with the same hash already, stop looking for tabs
		}
		$('.tabs-tabbox .tabs-label:contains('+hash+')').each(function() {
			// double-check if the hash is indeed exactly the same as the label.
			// Does not match if hash is only a part of the label's contents, unlike jQuery's :contains() selector
			if (this.innerHTML.trim() !== hash) {
				return true; // continue the $.each() function
			}
			this.click(); // open the selected tab by default.
			window.scrollTo(0, $(this).offset().top);
			return false; // stop the $.each() function after the first match.
		});
	}

	// Credit for this testing method: 2astalavista @ http://stackoverflow.com/a/21095568/1256925
	// The font will be sans-serif if the :not() property is supported. The margin will be 1px if the sibling selector is supported.
	if ($('#tabs-inputform').css('font-family').replace(/["']/g,'') === 'sans-serif' && $('#tabs-inputform').css('margin') === '1px') {
		$(function() {
			$('body').addClass('tabs-oldbrowserscript'); // Make the unselected tabs hide when the browser loads this script
			$('.tabs-label').click(function(e) {
				$('#'+$(this).attr('for')).click(); e.preventDefault();
				return false;
			});
			$('.tabs-input').each(function() {
				if (this.checked) {
					$(this).addClass('checked'); // Adds checked class to each checked box
				}
			}).change(function() {
				if (!this.checked) {
					return $(this).removeClass('checked'); // for toggleboxes
				}
				$(this).siblings('.checked').removeClass('checked'); // Uncheck all currently checked siblings
				$(this).addClass('checked'); // and do check this box
				$(this).parents('.tabs').toggleClass('tabs').toggleClass('tabs'); // remove and readd class to recalculate styles for its children.
				// Credit: Fabrício Matté @ http://stackoverflow.com/a/21122724/1256925
			});
			moveToHash();
		});
	} else {
		$(moveToHash);
	}
	addEventListener('hashchange', moveToHash);

	/*
	 * System to fix toggle boxes in Android Browser
	 * Browser detection based on http://stackoverflow.com/a/15591516/1256925
	 * Idea for the use of <detail> and <summary> based on http://stackoverflow.com/q/21357641/1256925
	 */
	var nua = navigator.userAgent;
	var isAndroid = (nua.indexOf('Mozilla/5.0') > -1 && nua.indexOf('Android ') > -1 && nua.indexOf('AppleWebKit') > -1 && nua.indexOf('Chrome') === -1);
	function replaces() { //General replacement function for both tags
		var tagName = $(this).is('.tabs-container') ? 'details' : 'summary'; //determine the required tag name
		var $newNode = $('<'+tagName+'/>').html($(this).html());
		for (var i=0;i<this.attributes.length;i++) { //copy all attributes from the original element
			if (this.attributes[i].nodeName === 'for') {
				continue; //don't copy the label's for="" attribute, since it's not needed here.
			}
			$newNode.attr(this.attributes[i].nodeName, this.attributes[i].value);
		}
		return $newNode;
	}
	if (isAndroid) {
		$('.tabs-togglebox .tabs-container').not('.tabs-dropdown .tabs-container').replaceWith(replaces); //do not select dropdowns, which already work in Android
		$('.tabs-togglebox .tabs-label').not('.tabs-dropdown .tabs-label').each(function() {
			if ($(this).prevAll('input').prop('checked')) { //preserve open state of the toggle box
				$(this).parents('details').prop('open', true);
			}
		}).replaceWith(replaces); //Run this *after* the .tabs-container has finished, otherwise all .tabs-label elements will be skipped.
	}
});
