/**
 * Countdown
 *
 * @version 2.1
 *
 * @author Pecoes <http://c.wikia.com/wiki/User:Pecoes>
 * @author Asaba <http://dev.wikia.com/wiki/User:Asaba>
 *
 * Version 1 authors:
 * - Splarka <http://c.wikia.com/wiki/User:Splarka>
 * - Eladkse <http://c.wikia.com/wiki/User:Eladkse>
 *
 * documentation and examples at:
 * <http://dev.wikia.com/wiki/Countdown>
 */
 
/*jshint jquery:true, browser:true, devel:true, camelcase:true, curly:false, undef:true, bitwise:true, eqeqeq:true, forin:true, immed:true, latedef:true, newcap:true, noarg:true, unused:true, regexp:true, strict:true, trailing:false */
/*global mediaWiki:true*/
 
;(function (module, mw, $, undefined) {
 
	'use strict';
 
	var translations = $.extend(true, {
		// German (Deutsch)
		de: {
			and: 'und',
			second: 'Sekunde',
			seconds: 'Sekunden',
			minute: 'Minute',
			minutes: 'Minuten',
			hour: 'Stunde',
			hours: 'Stunden',
			day: 'Tag',
			days: 'Tage'
		},
		// English (English)
		en: {
			and: 'and',
			second: 'second',
			seconds: 'seconds',
			minute: 'minute',
			minutes: 'minutes',
			hour: 'hour',
			hours: 'hours',
			day: 'day',
			days: 'days'
		},
		// Spanish (Español)
		es: {
			and: 'y',
			second: 'segundo',
			seconds: 'segundos',
			minute: 'minuto',
			minutes: 'minutos',
			hour: 'hora',
			hours: 'horas',
			day: 'día',
			days: 'días'
		},
		// French (Français)
		fr: {
			and: 'et',
			second: 'seconde',
			seconds: 'secondes',
			minute: 'minute',
			minutes: 'minutes',
			hour: 'heure',
			hours: 'heures',
			day: 'jour',
			days: 'jours'
		},
		// Dutch (Nederlands)
		nl: {
			and: 'en',
			second: 'seconde',
			seconds: 'seconden',
			minute: 'minuut',
			minutes: 'minuten',
			hour: 'uur',
			hours: 'uur',
			day: 'dag',
			days: 'dagen'
		},
		// Chinese (中文)
		zh: {
			and: ' ',
			second: '秒',
			seconds: '秒',
			minute: '分',
			minutes: '分',
			hour: '小时',
			hours: '小时',
			day: '天',
			days: '天'
		},
		// Chinese (繁體中文)
		'zh-tw':{
			and: ' ',
			second: '秒',
			seconds: '秒',
			minute: '分',
			minutes: '分',
			hour: '小時',
			hours: '小時',
			day: '天',
			days: '天'
		}
	}, module.translations || {}),
	i18n = translations[
		mw.config.get('wgContentLanguage')
	] || translations.en;
 
	var countdowns = [];
 
	var NO_LEADING_ZEROS = 1;
 
	function output (i, diff) {
		/*jshint bitwise:false*/
		var delta, result, parts = [];
		delta = diff % 60;
		parts.unshift(delta + ' ' + i18n[delta === 1 ? 'second' : 'seconds']);
		diff = Math.floor(diff / 60);
		delta = diff % 60;
		parts.unshift(delta + ' ' + i18n[delta === 1 ? 'minute' : 'minutes']);
		diff = Math.floor(diff / 60);
		delta = diff % 24;
		parts.unshift(delta + ' ' + i18n[delta === 1 ? 'hour'   : 'hours'  ]);
		diff = Math.floor(diff / 24);
		parts.unshift(diff  + ' ' + i18n[diff  === 1 ? 'day'    : 'days'   ]);
		result = parts.pop();
		if (countdowns[i].opts & NO_LEADING_ZEROS) {
			while (parts.length && parts[0][0] === '0') {
				parts.shift();
			}
		}
		if (parts.length) {
			result = parts.join(', ') + ' ' + i18n.and + ' ' + result;
		}
		countdowns[i].node.text(result);
	}
 
	function end(i) {
		var c = countdowns[i].node.parent();
		switch (c.attr('data-end')) {
		case 'remove':
			c.remove();
			return true;
		case 'stop':
			output(i, 0);
			return true;
		case 'toggle':
			var toggle = c.attr('data-toggle');
			if (toggle && $(toggle).length) {
				$(toggle).css('display', 'inline');
				c.css('display', 'none');
				return true;
			}
			break;
		case 'callback':
			var callback = c.attr('data-callback');
			if (callback && $.isFunction(module[callback])) {
				output(i, 0);
				module[callback].call(c);
				return true;
			}
			break;
		}
		countdowns[i].countup = true;
		output(i, 0);
		return false;
	}
 
	function update () {
		var now = Date.now();
		var countdownsToRemove = [];
		$.each(countdowns.slice(0), function (i, countdown) {
			var diff = Math.floor((countdown.date - now) / 1000);
			if (diff <= 0 && !countdown.countup) {
				if (end(i)) countdownsToRemove.push(i);
			} else {
				output(i, Math.abs(diff));
			}
		});
		var x;
		while((x = countdownsToRemove.pop()) !== undefined) {
			countdowns.splice(x, 1);
		}
		if (countdowns.length) {
			window.setTimeout(function () {
				update();
			}, 1000);
		}
	}
 
	function getOptions (node) {
		/*jshint bitwise:false*/
		var text = node.parent().attr('data-options'),
			opts = 0;
		if (text) {
			if (/no-leading-zeros/.test(text)) {
				opts |= NO_LEADING_ZEROS;
			}
		}
		return opts;
	}
 
	function init() {
		var countdown = $('.countdown:not(.handled)');
		if (!countdown.length) return;
		$('.nocountdown').css('display', 'none');
		countdown
		.css('display', 'inline')
		.find('.countdowndate')
		.each(function () {
			var $this = $(this),
				date = (new Date($this.text())).valueOf();
			if (isNaN(date)) {
				$this.text('BAD DATE');
				return;
			}
			countdowns.push({
				node: $this,
				opts: getOptions($this),
				date: date,
			});
		});
		countdown.addClass('handled');
		if (countdowns.length) {
			update();
		}
	}
 
	mw.hook('wikipage.content').add(init);
 
}(window.countdownTimer = window.countdownTimer || {}, mediaWiki, jQuery));

/**
 * Add custom buttons in the toolbar
 *
 * @source: https://www.mediawiki.org/wiki/Snippets/Custom_buttons_in_the_toolbar
 * @rev: 2
 */

if ({ edit:1, submit:1 }[mw.config.get('wgAction')]) {
	mw.loader.using('mediawiki.action.edit', function () {
		if (mw.toolbar) {

			// Wikitable
			mw.toolbar.addButton(
				'//upload.wikimedia.org/wikipedia/commons/0/04/Button_array.png',
				'Insert a table',
				'{| class="wikitable"\n|-\n',
				'\n|}',
				'! header 1\n! header 2\n! header 3\n|-\n| row 1, cell 1\n| row 1, cell 2\n| row 1, cell 3\n|-\n| row 2, cell 1\n| row 2, cell 2\n| row 2, cell 3',
				'mw-editbutton-wikitable'
			);

			// Redirect
			mw.toolbar.addButton(
				'//upload.wikimedia.org/wikipedia/en/c/c8/Button_redirect.png',
				'Redirect',
				'#REDIRECT [[',
				']]',
				'Insert text',
				'mw-editbutton-redirect'
			);

			// Left
			mw.toolbar.addButton(
				'//clanquest.org/wiki/images/f/f1/TextLeft.png',
				'Text align left',
				'{{left|',
				'}}',
				'Left Align text',
				'mw-editbutton-left'
			);

			// Center
			mw.toolbar.addButton(
				'//clanquest.org/wiki/images/9/91/TextCenter.png',
				'Text align center',
				'<center>',
				'</center>',
				'Center text',
				'mw-editbutton-center'
			);

			// Right
			mw.toolbar.addButton(
				'//clanquest.org/wiki/images/f/f9/TextRight.png',
				'Text align right',
				'{{right|',
				'}}',
				'Right Align text',
				'mw-editbutton-right'
			);

			// Bold
			mw.toolbar.addButton(
				'//clanquest.org/wiki/images/a/a8/BoldEditButton.png',
				'Bold text',
				'<b>',
				'</b>',
				'Make text bold',
				'mw-editbutton-bold'
			);

			// Italics
			mw.toolbar.addButton(
				'//clanquest.org/wiki/images/2/21/ItalicEditButton.png',
				'Italicize text',
				'<i>',
				'</i>',
				'Make text italicized',
				'mw-editbutton-italics'
			);

			// Underline
			mw.toolbar.addButton(
				'//clanquest.org/wiki/images/1/12/UnderlineEditButton.png',
				'Underline text',
				'<u>',
				'</u>',
				'Make text underlined',
				'mw-editbutton-underline'
			);

			// Strike
			mw.toolbar.addButton(
				'//clanquest.org/wiki/images/7/79/StrikeEditButton.png',
				'Strike through text',
				'{{s|',
				'}}',
				'Strike through text',
				'mw-editbutton-strike'
			);

			// File Link
			mw.toolbar.addButton(
				'//clanquest.org/wiki/images/9/94/FileEditButton.png',
				'Link to a wiki file',
				'[[File:',
				']]',
				'Link to a file',
				'mw-editbutton-filelink'
			);

			// Color
			mw.toolbar.addButton(
				'//clanquest.org/wiki/images/d/df/AddColorButton.png',
				'Change text color',
				'{{color|#|',
				'}}',
				'Color text',
				'mw-editbutton-color'
			);

			// Black
			mw.toolbar.addButton(
				'//clanquest.org/wiki/images/2/20/Colorblack.png',
				'Make text black',
				'{{Black|',
				'}}',
				'Make text black',
				'mw-editbutton-black'
			);

			// Size
			mw.toolbar.addButton(
				'//clanquest.org/wiki/images/b/b1/SizeEditButton.png',
				'Change text size',
				'{{Resize|100%|',
				'}}',
				'Change Text Size',
				'mw-editbutton-size'
			);

			// Nowiki
			mw.toolbar.addButton(
				'//clanquest.org/wiki/images/2/2c/NoWikiButton.png',
				'Disable wiki formatting',
				'<nowiki>',
				'</nowiki>',
				'Disable wiki formatting',
				'mw-editbutton-nowiki'
			);

			// InternalLink
			mw.toolbar.addButton(
				'//clanquest.org/wiki/images/1/13/PageLinkButton.png',
				'Link to another page',
				'[[',
				']]',
				'Link to another page',
				'mw-editbutton-internal'
			);

			// ExternalLink
			mw.toolbar.addButton(
				'//clanquest.org/wiki/images/a/a4/LinkButton.png',
				'Link to an external page',
				'[',
				']',
				'Link to an external page',
				'mw-editbutton-external'
			);

			// User
			mw.toolbar.addButton(
				'//clanquest.org/wiki/images/e/e7/UserButton.png',
				'Link to a user page',
				'[[User:',
				'|]]',
				'',
				'mw-editbutton-user'
			);

			// NewLine
			mw.toolbar.addButton(
				'//clanquest.org/wiki/images/f/f1/NewLineButton.png',
				'Highlight a word to have it start a new line',
				'<br />',
				'',
				'',
				'mw-editbutton-newline'
			);

			// ColBegin
			mw.toolbar.addButton(
				'//clanquest.org/wiki/images/9/99/ColBeginButton.png',
				'Begin a left column',
				'{{Col-begin}}',
				'',
				'',
				'mw-editbutton-colbegin'
			);

			// Col2
			mw.toolbar.addButton(
				'//clanquest.org/wiki/images/0/00/Col2Button.png',
				'Begin a right column, must come after col-begin',
				'{{Col-2}}',
				'',
				'',
				'mw-editbutton-col2'
			);

			// ColEnd
			mw.toolbar.addButton(
				'//clanquest.org/wiki/images/7/75/ColEndButton.png',
				'End the columns',
				'{{Col-end}}',
				'',
				'',
				'mw-editbutton-colend'
			);

			// Font
			mw.toolbar.addButton(
				'//clanquest.org/wiki/images/9/98/FontButton.png',
				'Change the font face',
				'{{Font|(fonthere)|',
				'}}',
				'',
				'mw-editbutton-font'
			);

			// Spoiler
			mw.toolbar.addButton(
				'//clanquest.org/wiki/images/0/02/SpoilerButton.png',
				'Hide text under a spoiler',
				'{{Spoiler||',
				'}}',
				'',
				'mw-editbutton-spoiler'
			);

			// Smiley
			mw.toolbar.addButton(
				'//clanquest.org/wiki/images/6/64/SmileyButton.png',
				'Create a smiley',
				'{{Smiley|',
				'}}',
				'',
				'mw-editbutton-smiley'
			);
		}
	});
}

/**
 * Dynamic Navigation Bars. See [[Wikipedia:NavFrame]]
 * 
 * Based on script from en.wikipedia.org, 2008-09-15.
 *
 * @source www.mediawiki.org/wiki/MediaWiki:Gadget-NavFrame.js
 * @maintainer Helder.wiki, 2012–2013
 * @maintainer Krinkle, 2013
 */
( function () {

// Set up the words in your language
var collapseCaption = 'hide';
var expandCaption = 'show';

var navigationBarHide = '[' + collapseCaption + ']';
var navigationBarShow = '[' + expandCaption + ']';

/**
 * Shows and hides content and picture (if available) of navigation bars.
 *
 * @param {number} indexNavigationBar The index of navigation bar to be toggled
 * @param {jQuery.Event} e Event object
 */
function toggleNavigationBar( indexNavigationBar, e ) {
	var navChild,
		navToggle = document.getElementById( 'NavToggle' + indexNavigationBar ),
		navFrame = document.getElementById( 'NavFrame' + indexNavigationBar );

	// Prevent browser from jumping to href "#"
	e.preventDefault();

	if ( !navFrame || !navToggle ) {
		return false;
	}

	// If shown now
	if ( navToggle.firstChild.data == navigationBarHide ) {
		for ( navChild = navFrame.firstChild; navChild != null; navChild = navChild.nextSibling ) {
			if ( hasClass( navChild, 'NavPic' ) ) {
				navChild.style.display = 'none';
			}
			if ( hasClass( navChild, 'NavContent' ) ) {
				navChild.style.display = 'none';
			}
		}
		navToggle.firstChild.data = navigationBarShow;

	// If hidden now
	} else if ( navToggle.firstChild.data == navigationBarShow ) {
		for ( navChild = navFrame.firstChild; navChild != null; navChild = navChild.nextSibling ) {
			if ( $( navChild ).hasClass( 'NavPic' ) || $( navChild ).hasClass( 'NavContent' ) ) {
				navChild.style.display = 'block';
			}
		}
		navToggle.firstChild.data = navigationBarHide;
	}
}

/**
 * Adds show/hide-button to navigation bars.
 *
 * @param {jQuery} $content
 */
function createNavigationBarToggleButton( $content ) {
	var i, j, navFrame, navToggle, navToggleText, navChild,
		indexNavigationBar = 0,
		navFrames = $content.find( 'div.NavFrame' ).toArray();

	// Iterate over all (new) nav frames
	for ( i = 0; i < navFrames.length; i++ ) {
		navFrame = navFrames[i];
		// If found a navigation bar
		indexNavigationBar++;
		navToggle = document.createElement( 'a' );
		navToggle.className = 'NavToggle';
		navToggle.setAttribute( 'id', 'NavToggle' + indexNavigationBar );
		navToggle.setAttribute( 'href', '#' );
		$( navToggle ).on( 'click', $.proxy( toggleNavigationBar, null, indexNavigationBar ) );

		navToggleText = document.createTextNode( navigationBarHide );
		for ( navChild = navFrame.firstChild; navChild != null; navChild = navChild.nextSibling ) {
			if ( $( navChild ).hasClass( 'NavPic' ) || $( navChild ).hasClass( 'NavContent' ) ) {
				if ( navChild.style.display == 'none' ) {
					navToggleText = document.createTextNode( navigationBarShow );
					break;
				}
			}
		}

		navToggle.appendChild( navToggleText );
		// Find the NavHead and attach the toggle link (Must be this complicated because Moz's firstChild handling is borked)
		for ( j = 0; j < navFrame.childNodes.length; j++ ) {
			if ( $( navFrame.childNodes[j] ).hasClass( 'NavHead' ) ) {
				navFrame.childNodes[j].appendChild( navToggle );
			}
		}
		navFrame.setAttribute( 'id', 'NavFrame' + indexNavigationBar );
	}
}

mw.hook( 'wikipage.content' ).add( createNavigationBarToggleButton );

}());
