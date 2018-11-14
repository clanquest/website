# Clan Quest’s Website

The following represents the codebase needed to run [Clan Quest’s website](https://clanquest.org). We invite all Clan Quest members to contribute to our website and expand its functionality. For instructions on how to do so, please see below.

## Website Components
* dynamic - scripts that generate dynamic content on the Clan Quest website (i.e. members’ home page, social hub, etc.)
* forumtheme - The Clan Quest forum style (including templates and css styles.)
* includes - scripts or tools that are necessary to initialize or run the website.
* prosilver-cq-base - base phpbb theme that the Clan Quest phpbb theme is based on, this should be rarely edited.
* static - static content sources included via index homepage (i.e. membership recruitment brochure)
* wiki - wiki files including extensions and skins for our MediaWiki installation


## Contributing

We accept contributions from all Clan Quest members. If you intend to contribute, please let us know on our [Clan Quest forums](https://clanquest.org/forums/) and we will add you as contributor to the [Clan Quest organization](https://github.com/clanquest). The code in this repository is licensed under the RECEX Shared Source license.

To contribute, develop your feature on a separate git branch (either on the main repository or on a fork) and send a pull request once it is ready. Send it for review for one of the [code owners](CODEOWNERS), and we will check if the code meets our safety and style standards. Note that if you plan on implementing a new feature, it may be worth checking with a code owner if the feature will be accepted in the first place to prevent unnecessary work.

### Contribution guidelines

Contributions to the codebase are expected to follow our standards:

* Don't make large infrastructural changes without checking with a code owner, since it may require special attention to deploy correctly.
* Keep safety in mind. This website runs on a shared Clan Quest server, so we need to take safety into account when accepting code contributions. Don't unnecessarily make use of the database or I/O.
* Keep performance in mind. We won't accept code that is unnecessarily waste server resources.
* Test your code. Make sure your website code works, is free of syntax errors, and doesn’t break any other website features.

### Developer setup

The Clan Quest website runs on a standard LAMP (Linux/Apache/MySQL/PHP) backend. Our server runs PHP 5.6 with MariaDB 10.1. You will need to populate config.php (inside the includes directory) with appropriate URLs and paths. Both PhpBB and MediaWiki should reside in the same database.

To set up phpBB we recommend a fresh install in /forums with symlinks to both the prosilver base style and Clan Quest style. 

To set up MediaWiki we recommend a fresh install in your /wiki with symlinks to both the Extensions and Skins directory. 