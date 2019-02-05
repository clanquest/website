# phpBB Auth Mediawiki Extension

[![Latest Stable Version](https://poser.pugx.org/multidimensional/phpbbauth/v/stable.svg)](https://packagist.org/packages/multidimensional/phpbbauth)
[![Minimum PHP Version](http://img.shields.io/badge/php-%3E%3D%205.4-8892BF.svg)](https://php.net/)
[![License](https://poser.pugx.org/multidimensional/phpbbauth/license.svg)](https://packagist.org/packages/multidimensional/phpbbauth)
[![Total Downloads](https://poser.pugx.org/multidimensional/phpbbauth/d/total.svg)](https://packagist.org/packages/multidimensional/phpbbauth)

A [MediaWiki](http://www.mediawiki.org/) Extension for phpBB Authentication using [Auth_remoteuser](https://www.mediawiki.org/wiki/Extension:Auth_remoteuser).

## Requirements

* Mediawiki 1.27+
* phpBB 3.0+
* Extension: Auth_remoteuser 2.0+

## Installation

Download the extension and add it to your extensions folder, or install it by adding it to your ```composer.local.json``` file:

```
{
    "require": {
        "multidimensional/phpbbauth": "*"
    }
}
```

Setup the prerequisites for Auth_remoteuser in ```LocalSettings.php```.

```php
wfLoadExtension( 'Auth_remoteuser' );
$wgGroupPermissions['*']['createaccount'] = false;
$wgGroupPermissions['*']['autocreateaccount'] = true;
```

Activate the extension in ```LocalSettings.php``` and set your phpBB directory location.

```php
wfLoadExtension( 'Phpbbauth' );
$wgPhpbbAuthForumDirectory = './../phpBB3/';
$wgPhpbbAuthAbsolutePath = '//www.domain.com/phpBB3/';
require_once "$IP/extensions/Phpbbauth/PhpbbAuth.php";
```

That's it!

## Advanced Settings

You can specify the formatting that Mediawiki will use for users that are logged into your wiki. By default, the login has uppercase first letters and the rest lowercase. You can set the following in ```LocalSettings.php```

```php
$wgPhpbbAuthNameFormat = 'phpbb';
```

This will set your Mediawiki username to be the same as phpBB.

## License

    The MIT License (MIT)

    Copyright (c) 2018 multidimension.al
	
    Permission is hereby granted, free of charge, to any person obtaining a copy
    of this software and associated documentation files (the "Software"), to deal
    in the Software without restriction, including without limitation the rights
    to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
    copies of the Software, and to permit persons to whom the Software is
    furnished to do so, subject to the following conditions:

    The above copyright notice and this permission notice shall be included in
    all copies or substantial portions of the Software.

    THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
    IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
    FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
    AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
    LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
    OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
    THE SOFTWARE.
