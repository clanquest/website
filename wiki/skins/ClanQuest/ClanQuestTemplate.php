<?php
/**
 * BaseTemplate class for the ClanQuest skin
 *
 * @ingroup Skins
 */
class ClanQuestTemplate extends BaseTemplate {
	/**
	 * Outputs the entire contents of the page
	 */
	public function execute() {
		global $template, $phpbb_root_path, $user, $auth, $config;

		$nav = $this->data['content_navigation'];
		$xmlID = '';
		foreach ( $nav as $section => $links ) {
			foreach ( $links as $key => $link ) {
				if ( $section == 'views' && !( isset( $link['primary'] ) && $link['primary'] ) ) {
					$link['class'] = rtrim( 'collapsible ' . $link['class'], ' ' );
				}

				$xmlID = isset( $link['id'] ) ? $link['id'] : 'ca-' . $xmlID;
				$nav[$section][$key]['attributes'] =
					' id="' . Sanitizer::escapeId( $xmlID ) . '"';
				if ( $link['class'] ) {
					$nav[$section][$key]['attributes'] .=
						' class="' . htmlspecialchars( $link['class'] ) . '"';
					unset( $nav[$section][$key]['class'] );
				}
				if ( isset( $link['tooltiponly'] ) && $link['tooltiponly'] ) {
					$nav[$section][$key]['key'] =
						Linker::tooltip( $xmlID );
				} else {
					$nav[$section][$key]['key'] =
						Xml::expandAttributes( Linker::tooltipAndAccesskeyAttribs( $xmlID ) );
				}
			}
		}

		$this->data['namespace_urls'] = $nav['namespaces'];
		$this->data['view_urls'] = $nav['views'];
		$this->data['action_urls'] = $nav['actions'];
		$this->data['variant_urls'] = $nav['variants'];

		page_header($this->get('title'), false);
		
		// is the user logged in?
		if ($user->data['user_id'] != ANONYMOUS)
		{
			$u_login_logout = append_sid("/forums/ucp.php", 'mode=logout', true, $user->session_id);
		}
		else
		{
			$u_login_logout = append_sid("/forums/ucp.php", 'mode=login');
		}

		$template->assign_vars(array(
			'HIDE_TITLE'	=>	false,
			'S_DISPLAY_SEARCH' => false,
			'LARGE_HEADER' => false,
			'IS_MAIN_SITE'	=>	true,
			'IS_WIKI'	=>	true,
			'ROOT_PATH'	=>	'/forums/',
			'T_STYLESHEET_LINK'	=> '/forums/styles/clanquest/theme/stylesheet.css?assets_version=' . $config['assets_version'],
			'T_THEME_PATH'	=>	'/forums/styles/clanquest/theme',
			'U_PRIVATEMSGS'	=>	'/forums/ucp.php?i=pm&amp;folder=inbox',
			'U_PROFILE'	=>	'/forums/ucp.php',
			'U_USER_PROFILE' => '/forums/memberlist.php?mode=viewprofile&amp;u=' . $user->data['user_id'],
			'U_SEARCH_SELF' => append_sid("/forums/search.php", 'search_id=egosearch'),
			'U_SEARCH'	=>	'/forums/search.php',
			'U_ACP' => (($auth->acl_get('a_') && !empty($user->data['is_registered'])) ? append_sid("/forums/adm/index.php", false, true, $user->session_id) : ''),
			'U_MCP'	=> append_sid("/forums/mcp.php", 'i=main'),
			'U_LOGIN_LOGOUT'	=> $u_login_logout,
			'U_REGISTER'	=> append_sid("/forums/ucp.php", 'mode=register'),
			'T_JQUERY_LINK'	=>	!empty($config['allow_cdn']) && !empty($config['load_jquery_url']) ? $config['load_jquery_url'] : "/forums/assets/javascript/jquery.min.js?assets_version=" . $config['assets_version'],
			'T_ASSETS_PATH' => '/forums/assets',
			'CURRENT_USER_AVATAR'	=> str_replace(array($_SERVER['DOCUMENT_ROOT'], '../', './'), '', phpbb_get_user_avatar($user->data))
		));

		$template->set_custom_style(
			'cq_template', $phpbb_root_path . 'styles/clanquest/template');
		$template->set_filenames(array(
			'header' => 'overall_header.html',
			'footer' => 'overall_footer.html'
		));

		$template->assign_var('WIKI_NAMESPACES', $this->get_namespaces());
		$template->assign_var('WIKI_VARIANTS', $this->get_variants());
		$template->assign_var('WIKI_VIEWS', $this->get_views());
		$template->assign_var('WIKI_ACTIONS', $this->get_actions());
		$template->assign_var('WIKI_SCRIPTS', $this->get('headscripts'));

		$template->display('header');
		
		echo '<div id="content">';
		$this->html( 'bodycontent' );
		echo '</div>';

		if ( $this->data['catlinks'] ) {
			echo '<div class="clearfix-cq"></div>';
			$this->html( 'catlinks' );
		}
		if ( $this->data['dataAfterContent'] ) {
			$this->html( 'dataAfterContent' );
		}

		$template->display('footer');
	}

	protected function get_namespaces() {
		$o = '';
		foreach ( $this->data['namespace_urls'] as $link ) {
			$o .= '<li ' . $link['attributes'] . '><a href="' . htmlspecialchars($link['href']) . '" ';
			$o .= $link['key'];
			if (isset($link['rel'])) {
				$o .= ' rel="' . htmlspecialchars( $link['rel'] ) . '"';
			}
			$o .= '>' . htmlspecialchars( $link['text'] ) . '</a></li>';
		}
		return $o;
	}

	protected function get_variants() {
		$o = '';
		foreach ( $this->data['variant_urls'] as $link ) {
			$o .= '<li ' . $link['attributes'] . '><a href="' . htmlspecialchars($link['href']) . '" ';
			$o .= 'lang="' . htmlspecialchars( $link['lang'] ) . '" ';
			$o .= 'hreflang="' . htmlspecialchars( $link['hreflang'] ) . '" ';
			$o .= $link['key'];
			$o .= '>' . htmlspecialchars( $link['text'] ) . '</a></li>';
		}

		return $o;
	}

	protected function get_views() {
		$o = '';
		foreach ( $this->data['view_urls'] as $link ) {
			$o .= '<li' . $link['attributes'] . '><a href="';
			$o .= htmlspecialchars( $link['href'] );
			$o .= '" ' . $link['key'];
				if ( isset ( $link['rel'] ) ) {
					$o .= ' rel="' . htmlspecialchars( $link['rel'] ) . '"';
				}
			$o .= '>';
			if ( array_key_exists( 'text', $link ) ) {
				$o .= array_key_exists( 'img', $link )
					? '<img src="' . $link['img'] . '" alt="' . $link['text'] . '" />'
					: htmlspecialchars( $link['text'] );
			}
			$o .= '</a></li>';
		}

		return $o;
	}

	protected function get_actions() {
		if (count($this->data['action_urls']) <= 0)
			return '';
			
		$o = '<li><div class="dropdown-container header-profile">';
		$o .= '<a href="dropdown-trigger">More <i class="fa fa-caret-down"></i></a>';
		$o .= '<div class="dropdown hidden"><div class="user-dropdown-contents wiki-actions-dropdown"><ul>';
		foreach ( $this->data['action_urls'] as $link ) {
			$o .= '<li' . $link['attributes'] . '>';
			$o .= '<a href="';
			$o .= htmlspecialchars( $link['href'] );
			$o .= '" ' . $link ['key'];
			$o .= '>' . htmlspecialchars( $link['text'] );
			$o .= '</a></li>';
		}
		$o .= '</ul></div></div>';
		$o .= '</div></li>';
		return $o;
	}
}
