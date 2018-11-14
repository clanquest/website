<?php
/**
 * SkinTemplate class for the ClanQuest skin
 *
 * @ingroup Skins
 */
class SkinClanQuest extends SkinTemplate {
	public $skinname = 'clanquest', $stylename = 'ClanQuest',
		$template = 'ClanQuestTemplate', $useHeadElement = false;

	/**
	 * Add CSS via ResourceLoader
	 *
	 * @param $out OutputPage
	 */
	public function initPage( OutputPage $out ) {

		$out->addMeta( 'viewport', 'width=device-width, initial-scale=1.0' );

		$out->addModuleStyles( array(
			'mediawiki.skinning.interface',
			'mediawiki.skinning.content.externallinks',
			'skins.clanquest'
		) );
		$out->addModules( array(
			'ext.wikiEditor.toolbar',
			'skins.clanquest.js'
		) );
	}

	/**
	 * Modify template variables specific to the clan quest skin.
	 * 
	 * @return $tpl Template object
	 */
	protected function prepareQuickTemplate() {
		$tpl = parent::prepareQuickTemplate();
		$out = $this->getOutput();

		$out->getRlClient()->setModuleStyles(['site.styles', 'ext.comments.css']); // blank out default wiki css
		$tpl->set('headscripts', $out->getRlClient()->getHeadHtml());

		return $tpl;
	}

	/**
	 * @param $out OutputPage
	 */
	function setupSkinUserCss( OutputPage $out ) {
		parent::setupSkinUserCss( $out );
	}
}
