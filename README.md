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

### Pull request guidelines

In this repository we expect contributions to be sent in the form of a pull request, or PR for short. In addition to the contribution guidelines above, please keep in mind the following PR guidelines as well:

* Each PR should not contain more than one feature or fix. If you can't describe your change in one sentence without using the word "and", the PR probably does too many things. Limiting each PR to one change makes it easier to review and easier to find in the history of the repository.
    - This does not exclude having multiple commits on a PR branch.
* Describe the change you are making in the PR description. The title of the PR should be a single sentence explaining the change briefly. The description can explain the why and how if necessary.
* If you PR resolves a GitHub issue, make sure to link the PR by putting "resolves #0" where 0 is replaced with the issue number. This will automatically close the issue once the PR is merged.
* Make sure that your change works. Seriously, start up a local instance and make sure that your change actually does what you expect it to.
* If you make visible changes, include a screenshot in the PR description. This way the reviewer can review the style changes without having to pull the branch and running a local server. If relevant, include both a before and after screenshot.
* Be patient. Your code will be reviewed as quickly as possible by one of the repository owners, but they have real life obligations too, so it may take a bit. They may also request you to make changes to bring the code up to standards. This is not done to spite you, but to maintain a healthy code base that makes future contributions possible.
* Reviewed PRs are generally left for the original author to merge. However, if you want the PR to be merged as soon as the reviewer has approved it, you can request so in the PR description.

PRs failing to follow either the contribution or PR guidelines may be rejected without detailed review until the changes are brought up to standards.

### Developer setup

The Clan Quest website runs on a standard LAMP (Linux/Apache/MySQL/PHP) backend. Our server runs PHP 5.6 with MariaDB 10.1. You will need to populate config.php (inside the includes directory) with appropriate URLs and paths. Both PhpBB and MediaWiki should reside in the same database.

To set up phpBB we recommend a fresh install in /forums with symlinks to both the prosilver base style and Clan Quest style.

To set up MediaWiki we recommend a fresh install in your /wiki with symlinks to both the Extensions and Skins directory.
