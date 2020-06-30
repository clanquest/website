<?php
/**
 * This tag extension creates the <tabs> and <tab> tags for creating tab interfaces and toggleboxes on wiki pages.
 * 
 * @example Tabs/Tabs.examples.txt
 *
 * @section LICENSE
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA.
 * http://www.gnu.org/copyleft/gpl.html
 * 
 * @file
 */

//Recommended tab width when reading this file: 4. (mainly for multi-line spanning concatenated strings)

class Tabs {
	/**
	 * Initiate the tags and parser function
	 * @param Parser &$parser
	 * @return boolean true
	 */
	public static function init( &$parser ) {
		$parser->tabsData = array(
			'tabsCount' => 0, // Counts the index of the <tabs> tag on the page. Increments by 1 before parsing the tag.
			'tabCount' => 0, // Same, but for <tab> instead.
			'addedStatics' => false, // checks if static styles have been added, so that it isn't done multiple times.
			'toStyle' => 0, // Counts the maximum amount of <tab> tags used within a single <tabs> tag. Used to determine the amount of lines to be added to the dynamic stylesheet.
			'nested' => false, // Keeps track of whether the <tab> is nested within a <tabs> or not.
			'tabNames' => array(), // Contains a list of the previously used tab names in that scope.
			'labels' => array(), // Lists the labels that need to be made within <tabs>. Example: array(1 => 'Tab 1', 2 => 'some tab label');
			//'dropdown' => false, // Used in combination with 'nested'; keeps track of whether the <tab> is nested inside a dropdown.
		);
		$parser->setHook('tab', array(new self(), 'renderTab'));
		$parser->setHook('tabs', array(new self(), 'renderTabs'));
		$parser->setFunctionHook('tab', array(new self(), 'renderPf'));
		return true;
	}
	
	/**
	 * Converts each <tab> into either a togglebox, or the contents of one tab within a <tabs> tag.
	 *
	 * @param string $input
	 * @param array $attr
	 * @param Parser $parser
	 * @return string
	 */
	public function renderTab($input, $attr = array(), $parser) {
		$form = $parser->tabsData['tabCount'] === 0 ? $this->insertCSSJS($parser) : ''; // init styles, set the return <form> tag as $form.
		++$parser->tabsData['tabCount'];
		$names = &$parser->tabsData['tabNames'];
		$nestAttr = isset($attr['nested']); //adding this attribute will restrict functionality, but allow nested tabs inside toggleboxes
		$nested = $parser->tabsData['nested'];
		if (isset($attr['name'])) {
			$attr['name'] = trim(htmlspecialchars($attr['name'])); // making the name attr safe to use
		}
		// Default value for the tab's given index: index attribute's value, or else the index of the tab with the same name as name attribute, or else the tab index
		if (!$nested && !$nestAttr) {
			$index = -1; // indices do nothing for non-nested tabs, so don't even bother doing the computations.
		} elseif (isset($attr['index']) && (intval($attr['index']) <= count($names) || $nestAttr)) {
			$index = intval($attr['index']); // if the index is given, and it isn't greater than the current index + 1.
		} elseif (isset($attr['index']) && $attr['index'] == '*') {
			$index = 0; //use wildcard index: this tab's contents shows up for every single tab;
			//this makes it possible to have a dropdown box inside a <tabs> box, which shows up for every tab.
		} elseif (isset($attr['name']) && array_search($attr['name'], $names) !== false) {
			$index = array_search($attr['name'], $names)+1; // if index is not defined, but the name is, use the index of the tabname.
		} else {
			$index = count($names)+1; // index of this tab in this scope. Plus one because tabs are 1-based, arrays are 0-based.
		}
		
		$classPrefix = '';
		if ($nested || $nestAttr) {// Note: This is defined seperately for toggleboxes, because of the different classes required.
			$classPrefix .= "tabs-content tabs-content-$index";
		}
		if (isset($attr['class'])) {
			$attr['class'] = trim("$classPrefix ".htmlspecialchars($attr['class']));
		} else {
			$attr['class'] = $classPrefix; // only the prefix if no classes have been defined
		}
		if ($index !== 0) {
			if (isset($names[$index-1])) { // if array $names already has a name defined at position $index, use that.
				$name = $names[$index-1]; // minus 1 because tabs are 1-based, arrays 0-based.
			} else { // otherwise, use the entered name, or the $index with a "Tab " prefix if it is not defined or empty.
				$name = trim(isset($attr['name']) && $attr['name'] ? $attr['name'] : wfMessage('tabs-tab-label', $index));
			}
		}
		if (!$nested && !$nestAttr) { // This runs when the tab is not nested inside a <tabs> tag.
			$container = $this->renderBox($input, $attr, $parser);
		} else { // this runs when the tab is nested inside a <tabs> tag.
			if ($index !== 0 && array_search($name, $names) === false) {// append name if it's not already in the list.
				$names[] = $name;
			}
			if (isset($attr['block'])) {
				$ib = 'tabs-block ';
			} elseif (isset($attr['inline'])) {
				$ib = 'tabs-inline ';
			} else {
				$ib = '';
			}
			$attr['class'] = $ib.$attr['class'];
			$container = array(
				'',
				'',
				'div',
				$this->getSafeAttrs($attr)
			);
			if ($index !== 0) {
				// Store the index and the name so this can be used within the <tabs> hook to create labels
				$parser->tabsData['labels'][intval($index)] = $name;
			}
		}
		if ($input === null) return ''; // return empty string if the tag is self-closing. This can be used to pre-define tabs for referring to via the index later.
		$parser->tabsData['nested'] = false; // temporary
		$newstr = $parser->recursiveTagParse($input);
		$parser->tabsData['nested'] = $nested; // revert
		return $form.$container[0].'<'.$container[2].$container[3].">$newstr</".$container[2].'>'.$container[1];
	}

	/**
	 * Function split from renderTab for readability
	 * Handles the situations for when a tab is placed outside a <tabs> interface,
	 * ie. for toggle boxes and dropdown boxes (hence the function name).
	 *
	 * @param string $input
	 * @param array $attr pass by reference so it can be modified from within.
	 * @param Parser $parser
	 * @return array The properties that should be applied to the return value for the calling renderTab function.
	 */
	public function renderBox($input, &$attr = array(), $parser) {
		$nameAttrs = array(
			'name'=>isset($attr['name']),
			'openname'=>isset($attr['openname']),
			'closename'=>isset($attr['closename']),
		);
		$checked = isset($attr['collapsed']) ? '' : ' checked="checked"';
		$id = 'Tabs_'.$parser->tabsData['tabCount'];
		$dropdown = isset($attr['dropdown']);
		if ($dropdown)
			// default width. Will be overridden by styles added by the user, if set.
			$attr['style'] = 'width:200px;' . (isset($attr['style']) ? $attr['style'] : '');
		/*
		 * If only one of the openname and closename attributes is defined, the both will take the defined one's value
		 * If neither is defined, but the name attribute is, both will take the name attribute's value
		 * If all three are undefined, the default "Show/Hide content" will be used
		 */
		if ($nameAttrs['openname'] && $nameAttrs['closename']) {
			$openname = htmlspecialchars($attr['openname']);
			$closename = htmlspecialchars($attr['closename']);
		} elseif ($nameAttrs['openname'] && !$nameAttrs['closename']) {
			$openname = $closename = htmlspecialchars($attr['openname']);
		} elseif ($nameAttrs['closename'] && !$nameAttrs['openname']) {
			$openname = $closename = htmlspecialchars($attr['closename']);
		} elseif (!$nameAttrs['openname'] && !$nameAttrs['closename'] && $nameAttrs['name']) {
			$openname = $closename = htmlspecialchars($attr['name']);
		} elseif (!$nameAttrs['openname'] && !$nameAttrs['closename']) {
			$openname = wfMessage('tabs-'.($dropdown?'dropdown-label':'toggle-open'));
			$closename = wfMessage('tabs-'.($dropdown?'dropdown-label':'toggle-close'));
		}
		// Check if the togglebox should be displayed inline. No need to check for the `block` attribute, since the default is display:block;
		$inline = isset($attr['inline']) ? ' tabs-inline' : '';
		$label = "<span class=\"tabs-open\">$openname</span><span class=\"tabs-close\">$closename</span>";
		if ($dropdown) {
			// negative tabindex allows :focus state on click, while not allowing the element to be tabbed to.
			$label = "<div class=\"tabs-label\" tabindex=\"-1\">$openname</div>";
		} else {
			$label = "<input class=\"tabs-input\" form=\"tabs-inputform\" type=\"checkbox\" id=\"$id\"$checked/>".
					"<label class=\"tabs-label\" for=\"$id\">$label</label>";
		}
		$attr['class'] = "tabs tabs-togglebox$inline ".($dropdown?'tabs-dropdown ':'').$attr['class'];
		$containAttrStr = $this->getSafeAttrs($attr);
		if (isset($attr['bgcolor'])) {
			// preg_split filters for ;{} characters and CSS comments, to prevent injection of any other styles than just the background-color.
			// Only the part of the value that's before the filtered characters will be included.
			$bgsplit = preg_split('/[;\{\}]|\/\*/', trim(htmlspecialchars($attr['bgcolor'])));
			$bgcolor = $bgsplit[0];
			$background =  "data-bgcolor=\"$bgcolor\"";
			$containAttrStr .= " $background";
			$css = '<style type="text/css">'.
					".tabs-dropdown[$background] .tabs-content,".
					".tabs-dropdown[$background] .tabs-container,".
					".tabs-dropdown[$background] li,".
					".tabs-dropdown[$background] ul,".
					".tabs-dropdown[$background] ol {".
						"background-color: $bgcolor".
					'}</style>';
		} else {
			$css = '';
		}
		$containerStyle = '';
		if (isset($attr['container'])) {
			$containerStyle = htmlspecialchars($attr['container']);
		}
		$container = array(
			"<div$containAttrStr>$css<div class=\"tabs-container\">$label",
			'</div></div>',
			$dropdown ? 'menu' : 'div',
			" class=\"tabs-content\" style=\"$containerStyle\""
		);
		return $container;
	}

	/**
	 * Converts each <tabs> tag to a tab layout.
	 *
	 * @param string $input
	 * @param array $attr
	 * @param Parser $parser
	 * @return string
	 */
	public function renderTabs($input, $attr = array(), $parser) {
		if (!isset($input)) return ''; // Exit if the tag is self-closing. <tabs> is a container element, so should always have something in it.
		$form = $parser->tabsData['tabCount'] === 0 ? $this->insertCSSJS($parser) : ''; // init styles, set the return <form> tag as $form.
		if ($parser->tabsData['tabsCount'] === 0) {
			$this->insertCSSJS($parser); // init styles
		}
		$count = ++$parser->tabsData['tabsCount'];
		$class = 'tabs tabs-tabbox';
		if (isset($attr['plain'])) $class .= ' tabs-plain';
		$attr['class'] = isset($attr['class']) ? "$class ".$attr['class'] : $class;
		$attrStr = $this->getSafeAttrs($attr);
		$containerStyle = '';
		if (isset($attr['container'])) {
			$containerStyle = htmlspecialchars($attr['container']);
		}
		
		// CLEARING:
		$tabnames = $parser->tabsData['tabNames']; // Copy this array's value, to reset it to this value after parsing the inner <tab>s.
		$parser->tabsData['tabNames'] = array(); // temporarily clear this array, so that only the <tab>s within this <tabs> tag are tracked.
		$parser->tabsData['labels'] = array(); // Reset after previous usage
		$parser->tabsData['nested'] = true;
		// PARSING
		$newstr = $parser->recursiveTagParse($input);
		// AND RESETTING (to their original values):
		$parser->tabsData['tabNames'] = $tabnames; // reset to the value it had before parsing the nested <tab>s. All nested <tab>s are "forgotten".
		$parser->tabsData['nested'] = false; // reset
		
		/**
		 * The default value for $labels creates a seperate input for the default tab, which has no label attached to it.
		 * This is to allow any scripts to be able to check easily if the user has changed the shown tab at all,
		 * by checking if this 0th input is checked.
		 */
		$labels = "<input type=\"radio\" form=\"tabs-inputform\" id=\"tabs-input-$count-0\" name=\"tabs-$count\" class=\"tabs-input tabs-input-0\" checked/>";
		$indices = array(); // this is to most accurately count the amount of <tab>s in this <tabs> tag.
		foreach ($parser->tabsData['labels'] as $i => $n) {
			$indices[] = $i;
			$labels .= $this->makeLabel($i, $n, $count);
		}
		if (!count($indices)) { // If no tabs have been defined, add this plain default tab.
			$indices[] = 1;
			$labels .= $this->makeLabel(1, 'Tab 1', $count);
		}

		$toStyle = &$parser->tabsData['toStyle'];
		if ($toStyle < count($indices)) { // only redefine the styles to be added to the head if we actually need to generate extra styles.
			$toStyle = count($indices);
			$this->insertCSSJS($parser); // reload dynamic CSS with new amount
		}
		
		return "$form<div$attrStr>$labels<div class=\"tabs-container\" style=\"$containerStyle\">$newstr</div></div>";
	}

	/**
	 * Renders parser function for simpler inline tab syntax ({{#tab:}})
	 * @param Parser $parser
	 * @param string $index A comma-seperated list of tab names or indices. Integers prefixed with '#' will always be interpreted as indices.
	 * @return string A converted list of <tab> tags, further to be processed by the parser.
	 */
	public function renderPf($parser, $index) {
		$index = explode(',', $index);
		$args = max(func_num_args(), count($index)+2);
		$argcount = func_num_args();
		$output = '';
		$nested = false;
		// start with 1, since that'll be the default index="" for the first tab.
		for ($i = 1; $i+1 < $args; $i++) {
			// $i+1 is used in this loop because the arguments are ($parser, $index, PARAM_1, PARAM_2, ...);
			// so to get PARAM_n, you must do func_get_arg(n+1).
			$val = trim($i+1 < $argcount ? func_get_arg($i+1) : '');
			if (preg_match("/^nested=/", $val) && $i+2 == $argcount) {
				//if the last parameter has |nested=true then make all tabs nested
				$nested = true;
				continue; //there may still be self-closing tags to define based on name. So, continue the loop.
			}
			if (preg_match("/^(\d+)\s*=/", $val, $matches)) { //if a parameter has |n=content, use n as index.
				$index_i = intval($matches[1]); //$matches stores the result of the above preg_match.
				$val = preg_replace("/^\d+\s*=\s*/", "", $val); //remove the index from the tab content.
			} else {
				$index_i = isset($index[$i-1]) ? trim($index[$i-1]) : '';
			}
			if (preg_match('/^#\d+$/',$index_i) && intval(substr($index_i,1)) > 0) {
				//only assign an index if the attribute is just digits, preceded by #
				$attr = 'index="'.substr($index_i,1).'"';
				$isname = false;
			} elseif ($index_i) {
				// only assign a name if the name attribute isn't just whitespace
				$attr = "name=\"$index_i\"";
				$isname = true;
			} else {
				// Default: fallback to the current index of the parameter within this parser function
				$attr = "index=\"$i\"";
				$isname = false;
			}
			if (preg_match('/^\$\d+$/', $val)) {
				// Copying over the value of other parameters for the syntax $n. Must not contain anything other than $n in the value.
				$ref = intval(substr($val, 1));
				if ($ref+1 < $argcount && $ref > 0) $refval = func_get_arg($ref+1); // Only do this when the referred-to value exists
				if (trim($refval)) $val = $refval; // only if the referred-to value is not empty, assign its value to this parameter
			}
			if ($val) { // if content is defined for this tab
				$output .= "<tab $attr>$val</tab>";
			} elseif ($isname) { // if no content is defined, but a name is defined. Makes it easier to define all tabs at the top.
				$output .= "<tab $attr />";
			} //otherwise, just don't append anything to the output.
		}
		if ($nested) {
			//if the last parameter was |nested=true, then convert all tabs to nested tabs.
			$output = preg_replace("/<tab /", '<tab nested ', $output);
		}
		return array( $output, 'noparse' => false );
	}
	
	/**
	 * Template for the tab label
	 * @param int $tabN The index of the individual tab.
	 * @param string $label The label that is going to appear to the user.
	 * @param int $tagN The index of the <tabs> tag on the page.
	 * @return string HTML code of the label
	 */
	public function makeLabel($tabN, $label, $tagN) {
		$label = htmlspecialchars($label);
		return "<input type=\"radio\" form=\"tabs-inputform\" id=\"tabs-input-$tagN-$tabN\" name=\"tabs-$tagN\" class=\"tabs-input tabs-input-$tabN\"/>".
				"<label class=\"tabs-label\" for=\"tabs-input-$tagN-$tabN\" data-tabpos=\"$tabN\">$label</label><wbr/>";
	}
	
	/**
	 * Filters list of entered parameters to only the HTML-safe attributes
	 * @param array $attr The full list of entered attributes
	 * [@param array $safe] The array in which to store the safe attributes
	 * @return array The list of safe attributes. Format: array(attrname => attrvalue)
	 */
	public function getSafeAttrs($attr, &$safe = array()) {
		$safeAttrs = array('class', 'id', 'title', 'style');
		$attrStr = '';
		foreach ($safeAttrs as $i) {
			if (isset($attr[$i])) {
				$safe[$i] = htmlspecialchars(trim($attr[$i]));
				if ($i == 'style') //escape the urls, to prevent users from loading images from disallowed sites.
					$safe[$i] = preg_replace("/[^;]+\s*url\s*\([^\)]+\)[^;]*;?/i", "/*$0*/", $safe[$i]);
				$attrStr .= " $i=\"".$safe[$i].'"';
			} else
				$safe[$i] = '';
		}
		return $attrStr;
	}
		
	/**
	 * Insert the static and dynamic CSS and JS into the page
	 * @param Parser $parser
	 * @return string Returns the form the input elements are assigned to via their form="" attribute for semantic purposes.
	 */
	public function insertCSSJS(&$parser) {
		$parserOut = $parser->getOutput();
		$parserOut->addHeadItem($this->createDynamicCss($parser), 'TabsStyles');
		if (!$parser->tabsData['addedStatics']) {
			$parser->tabsData['addedStatics'] = true;
			$parserOut->addModules( 'ext.tabs' );
			// this form is here to use for the form="" attribute in the inputs, for semantically correct usage of the <input> tag outside a <form> tag.
			return '<form id="tabs-inputform" class="tabs tabs-inputform" action="#"></form>';
		}
		return '';
	}

	public function createDynamicCss(&$parser) {
		$css = '';
		$class = array('', '.tabs-inline', '.tabs-block');
		$style = array('inline-block', 'inline', 'block');
		foreach ($class as $n => $c) {
			for ($i=1;$i<=$parser->tabsData['toStyle'];$i++) {
				$css .= ".tabs-input-$i:checked ~ .tabs-container $c.tabs-content-$i,\n";
			}
			$css .= ".tabs-input-0:checked ~ .tabs-container $c.tabs-content-1 {display:".$style[$n].";}\n";
		}
		$css .= "/* The same styles, but with .checked instead of :checked, for browsers that rely on the JavaScript fallback */\n".
				str_replace(':checked','.checked', $css);
		$css .= '.tabs-dropdown .tabs-content,'.
				'.tabs-dropdown .tabs-container,'.
				'.tabs-dropdown li,'.
				'.tabs-dropdown ul,'.
				'.tabs-dropdown ol {'.
					'background-color: '.wfMessage('tabs-dropdown-bgcolor').
				'}';
		return "<style type=\"text/css\" id=\"tabs-dynamic-styles\">/*<![CDATA[*/\n/* Dynamically generated tabs styles */\n$css\n/*]]>*/</style>";
	}
}
