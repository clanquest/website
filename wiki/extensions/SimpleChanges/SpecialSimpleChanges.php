<?php

class SpecialSimpleChanges extends SpecialRecentChanges {
	public function __construct( $name = 'SimpleChanges' ) {
		parent::__construct( $name );
	}

	/**
	 * Add our own modifications to the RC query
	 *
	 * @global array $wgContentNamespaces
	 * @global bool $wgSimpleChangesOnlyContentNamespaces
	 * @global bool $wgSimpleChangesOnlyLatest
	 *
	 * @inheritdoc
	 */
	protected function runMainQueryHook( &$tables, &$fields, &$conds, &$query_options, &$join_conds,
		$opts
	) {

		global $wgContentNamespaces, $wgSimpleChangesOnlyContentNamespaces, $wgSimpleChangesOnlyLatest;

		# don't count log entries toward limit of number of changes displayed
		$conds[] = 'rc_type != ' . RC_LOG;

		if ( $opts['namespace'] == '' && $wgSimpleChangesOnlyContentNamespaces &&
			$wgContentNamespaces != null ) {
			$contentNamespaces = $wgContentNamespaces;

			$condition = '(rc_namespace = ' . array_shift( $contentNamespaces );
			foreach ( $contentNamespaces as $namespace ) {
				$condition .= ' OR rc_namespace = ' . $namespace;
			}
			$condition .= ')';
			$conds[] = $condition;
		}
		if ( $wgSimpleChangesOnlyLatest ) {
			$conds[] = 'rc_this_oldid=page_latest';

			// Sometimes this is added by the parent, sometimes not.
			if ( !in_array( 'page', $tables ) ) {
				$tables[] = 'page';
				$fields[] = 'page_latest';
				$join_conds['page'] = array( 'LEFT JOIN', 'rc_cur_id=page_id' );
			}
		}

		return parent::runMainQueryHook( $tables, $fields, $conds, $query_options, $join_conds, $opts );
	}

	/**
	 * Creates the choose namespace selection
	 *
	 * @param FormOptions $opts
	 * @return string
	 */
	protected function namespaceFilterForm( FormOptions $opts ) {
		global $wgSimpleChangesOnlyContentNamespaces;

		if ( !$wgSimpleChangesOnlyContentNamespaces ) {
			return parent::namespaceFilterForm( $opts );
		}
		$nonContentNamespaces = array_diff( MWNamespace::getValidNamespaces(),
			MWNamespace::getContentNamespaces() );
		//Borrowed from parent class.
		//If $wgSimpleChangesOnlyContentNamespaces is true, we need to change the namespace
		//selector to only show content namespaces.
		$nsSelect = Html::namespaceSelector(
				array( 'selected' => $opts['namespace'], 'all' => '', 'exclude' => $nonContentNamespaces ),
				array( 'name' => 'namespace', 'id' => 'namespace' )
		);
		$nsLabel = Xml::label( $this->msg( 'simplechanges-contentnamespace' )->text(), 'namespace' );
		$invert = Xml::checkLabel(
				$this->msg( 'invert' )->text(), 'invert', 'nsinvert', $opts['invert'],
				array( 'title' => $this->msg( 'tooltip-invert' )->text() )
		);
		$associated = Xml::checkLabel(
				$this->msg( 'namespace_association' )->text(), 'associated', 'nsassociated',
				$opts['associated'], array( 'title' => $this->msg( 'tooltip-namespace_association' )->text() )
		);

		return array( $nsLabel, "$nsSelect $invert $associated" );
	}

	/**
	 * Send output to the OutputPage object, only called if not used feeds
	 * This function is a modified combination of SpecialRecentchanges::outputChangesList() &
	 * ChangesList::recentChangesLine()
	 *
	 * @global bool $wgSimpleChangesShowUser
	 * @param $rows Array of database rows
	 * @param $opts FormOptions
	 */
	public function outputChangesList( $rows, $opts ) {
		$limit = $opts['limit'];

		$counter = 1;
		$list = ChangesList::newFromContext( $this->getContext() );

		$rclistOutput = $list->beginRecentChangesList();

		$rclistOutput .= "\n<ul class=\"special-recent-changes\">\n";
		foreach ( $rows as $obj ) {
			if ( $limit == 0 ) {
				break;
			}
			$rc = RecentChange::newFromRow( $obj );
			$rc->counter = $counter++;

			$changeLine = false;

			if ( $rc->mAttribs['rc_log_type'] ) {
				// Log entries (old format) or log targets, and special pages
			} elseif ( $rc->mAttribs['rc_namespace'] == NS_SPECIAL ) {
				// Regular entries
			} else {
				$list->insertArticleLink( $changeLine, $rc, false, false );
				$changeLine = Html::openElement( 'li' ) . $changeLine;

				global $wgSimpleChangesShowUser;
				if ( $wgSimpleChangesShowUser ) {
					# from ChangesList::insertUserRelatedLinks()
					$user = ' edited by ' .
						Linker::userLink( $rc->mAttribs['rc_user'], $rc->mAttribs['rc_user_text'] );
					$changeLine .= Html::rawElement( 'span', array( 'class' => 'simplechanges-user' ), $user );
				}
				$changeLine .= Html::closeElement( 'li' ) . "\n";
			}

			if ( $changeLine !== false ) {
				$rclistOutput .= $changeLine;
				--$limit;
			}
		}
		$rclistOutput .= "\n</ul>\n";
		$rclistOutput .= $list->endRecentChangesList();
		$this->getOutput()->addHTML( $rclistOutput );
	}
}
