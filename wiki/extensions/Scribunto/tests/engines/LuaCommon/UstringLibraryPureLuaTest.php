<?php

require_once __DIR__ . '/UstringLibraryTest.php';

// @codingStandardsIgnoreLine Squiz.Classes.ValidClassName.NotCamelCaps
class Scribunto_LuaUstringLibraryPureLuaTests extends Scribunto_LuaUstringLibraryTests {
	protected function setUp() {
		parent::setUp();

		// Override mw.ustring with the pure-Lua version
		$interpreter = $this->getEngine()->getInterpreter();
		$interpreter->callFunction(
			$interpreter->loadString( '
				local ustring = require( "ustring" )
				ustring.maxStringLength = mw.ustring.maxStringLength
				ustring.maxPatternLength = mw.ustring.maxPatternLength
				mw.ustring = ustring
			', 'fortest' )
		);
	}

	/**
	 * @dataProvider providePCREErrors
	 */
	public function testPCREErrors( $ini, $args, $error ) {
		// Not applicable
		$this->assertTrue( true );
	}

	public static function providePCREErrors() {
		return array(
			array( array(), array(), null ),
		);
	}
}
