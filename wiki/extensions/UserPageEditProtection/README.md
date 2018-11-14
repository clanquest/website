## UserPageEditProtection

UserPageEditProtection is an extension to MediaWiki that allows to restrict the edit access to user pages.


### Compatibility

* PHP 5.4+
* MediaWiki 1.23+

See also the "CHANGELOG.md" file provided with the code.


### Installation

(1) Obtain the code from [GitHub](https://github.com/wikimedia/mediawiki-extensions-UserPageEditProtection/releases)

(2) Extract the files in a directory called `UserPageEditProtection` in your `extensions/` folder.

(3) Add the following code at the bottom of your "LocalSettings.php" file:
```
require_once "$IP/extensions/UserPageEditProtection/UserPageEditProtection.php";
$wgOnlyUserEditUserPage = true;
```
(4) Go to "Special:Version" on your wiki to verify that the extension is successfully installed.

(5) Done.


### Configuration

This extension comes with an extra user right called "editalluserpages" to allow fine grained control. By default it is
assigned to the "sysop" user group. In case you would like to assign it to another user group e.g. "userpageeditor", add
the following code to you "LocalSettings.php" file right after the lines added in step (3) of the installation process:

```
$wgGroupPermissions['userpageeditor']['editalluserpages'] = true;
```
Revoking the permission for the "sysop" user group may be done by adding the following line:

```
$wgGroupPermissions['sysop']['editalluserpages'] = false;
```
