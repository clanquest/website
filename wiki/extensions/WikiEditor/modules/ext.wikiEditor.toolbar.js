/*
 * JavaScript for WikiEditor Toolbar
 */
jQuery( function ( $ ) {
	if ( !$.wikiEditor.isSupported( $.wikiEditor.modules.toolbar ) ) {
		$( '.wikiEditor-oldToolbar' ).show();
		return;
	}
	// The old toolbar is still in place and needs to be removed so there aren't two toolbars
	$( '#toolbar' ).remove();
	// Add toolbar module
	// TODO: Implement .wikiEditor( 'remove' )
	$( '#wpTextbox1' ).wikiEditor(
		'addModule', $.wikiEditor.modules.toolbar.config.getDefaultConfig()
	);
	$( '#wpTextbox1' ).wikiEditor( 'addToToolbar', {
        'section': 'emoticons',
        'groups': {
            'main': {
                'label': ''
            }
        }
    });
    $( '#wpTextbox1' ).wikiEditor( 'addToToolbar', {
        'section': 'advanced',
        'groups': {
            'style': {
                'label': 'style'
            }
        }
    });
    $( '#wpTextbox1' ).wikiEditor( 'addToToolbar', {
        'section': 'advanced',
        'groups': {
            'columns': {
                'label': 'columns'
            }
        }
    });
    $('#wpTextbox1').wikiEditor('addToToolbar', {
        section: 'main',
        group: 'format',
        tools: {
            "underline": {
                label: 'Underline',
                type: 'button',
                icon: '//clanquest.org/wiki/images/c/cd/WEUnderlineButton.png',
                action: {
                    type: 'encapsulate',
                    options: {
                        pre: "<u>",
                        post: "</u>"
                    }
                }
            }
        }
    });
    $('#wpTextbox1').wikiEditor('addToToolbar', {
        section: 'main',
        group: 'format',
        tools: {
            "strike": {
                label: 'Strike',
                type: 'button',
                icon: '//clanquest.org/wiki/images/5/58/WEStrikeButton.png',
                action: {
                    type: 'encapsulate',
                    options: {
                        pre: "{{s|",
                        post: "}}"
                    }
                }
            }
        }
    });
    $('#wpTextbox1').wikiEditor('addToToolbar', {
        section: 'main',
        group: 'format',
        tools: {
            "left": {
                label: 'Left align',
                type: 'button',
                icon: '//clanquest.org/wiki/images/1/14/WELeftButton.png',
                action: {
                    type: 'encapsulate',
                    options: {
                        pre: "{{Left|",
                        post: "}}"
                    }
                }
            }
        }
    });
    $('#wpTextbox1').wikiEditor('addToToolbar', {
        section: 'main',
        group: 'format',
        tools: {
            "center": {
                label: 'Center',
                type: 'button',
                icon: '//clanquest.org/wiki/images/b/bd/WECenterButton.png',
                action: {
                    type: 'encapsulate',
                    options: {
                        pre: "<center>",
                        post: "</center>"
                    }
                }
            }
        }
    });
     $('#wpTextbox1').wikiEditor('addToToolbar', {
        section: 'main',
        group: 'format',
        tools: {
            "right": {
                label: 'Right align',
                type: 'button',
                icon: '//clanquest.org/wiki/images/5/5f/WERightButton.png',
                action: {
                    type: 'encapsulate',
                    options: {
                        pre: "{{Right|",
                        post: "}}"
                    }
                }
            }
        }
    });
    $('#wpTextbox1').wikiEditor('addToToolbar', {
        section: 'main',
        group: 'insert',
        tools: {
            "userpagelink": {
                label: 'Userpage link',
                type: 'button',
                icon: '//clanquest.org/wiki/images/f/f2/WEUserButton.png',
                action: {
                    type: 'encapsulate',
                    options: {
                        pre: "[[User:",
                        post: "]]"
                    }
                }
            }
        }
    });
    $('#wpTextbox1').wikiEditor('addToToolbar', {
        section: 'advanced',
        group: 'style',
        tools: {
            "fontsize": {
                label: 'Font Size',
                type: 'button',
                icon: '//clanquest.org/wiki/images/9/92/WESizeButton.png',
                action: {
                    type: 'encapsulate',
                    options: {
                        pre: "{{Resize|100%|",
                        post: "}}"
                    }
                }
            }
        }
    });
    $('#wpTextbox1').wikiEditor('addToToolbar', {
        section: 'advanced',
        group: 'style',
        tools: {
            "color": {
                label: 'Color',
                type: 'button',
                icon: '//clanquest.org/wiki/images/8/8b/WEColorButton.png',
                action: {
                    type: 'encapsulate',
                    options: {
                        pre: "{{Color|#|",
                        post: "}}"
                    }
                }
            }
        }
    });
    $('#wpTextbox1').wikiEditor('addToToolbar', {
        section: 'advanced',
        group: 'style',
        tools: {
            "font": {
                label: 'Font',
                type: 'button',
                icon: '//clanquest.org/wiki/images/0/0c/WEFontButton.png',
                action: {
                    type: 'encapsulate',
                    options: {
                        pre: "{{Font|fontface|",
                        post: "}}"
                    }
                }
            }
        }
    });
    $('#wpTextbox1').wikiEditor('addToToolbar', {
        section: 'advanced',
        group: 'style',
        tools: {
            "bbox": {
                label: 'Binding box',
                type: 'button',
                icon: '//clanquest.org/wiki/images/e/ed/WEBoxButton.png',
                action: {
                    type: 'encapsulate',
                    options: {
                        pre: "<box>",
                        post: "</box>"
                    }
                }
            }
        }
    });
    $('#wpTextbox1').wikiEditor('addToToolbar', {
        section: 'advanced',
        group: 'format',
        tools: {
            "spoiler": {
                label: 'Spoiler',
                type: 'button',
                icon: '//clanquest.org/wiki/images/4/44/WESpoilerButton.png',
                action: {
                    type: 'encapsulate',
                    options: {
                        pre: "{{Spoiler|titlehere|",
                        post: "}}"
                    }
                }
            }
        }
    });
    $('#wpTextbox1').wikiEditor('addToToolbar', {
        section: 'advanced',
        group: 'columns',
        tools: {
            "colbegin": {
                label: 'Begin first column',
                type: 'button',
                icon: '//clanquest.org/wiki/images/9/9a/WEColbeginButton.png',
                action: {
                    type: 'encapsulate',
                    options: {
                        pre: "{{col-begin}}",
                        post: ""
                    }
                }
            }
        }
    });
    $('#wpTextbox1').wikiEditor('addToToolbar', {
        section: 'advanced',
        group: 'columns',
        tools: {
            "col2": {
                label: 'Begin second column',
                type: 'button',
                icon: '//clanquest.org/wiki/images/7/70/WECol2Button.png',
                action: {
                    type: 'encapsulate',
                    options: {
                        pre: "{{col-2}}",
                        post: ""
                    }
                }
            }
        }
    });
    $('#wpTextbox1').wikiEditor('addToToolbar', {
        section: 'advanced',
        group: 'columns',
        tools: {
            "colend": {
                label: 'End the columns',
                type: 'button',
                icon: '//clanquest.org/wiki/images/9/9d/WEColendButton.png',
                action: {
                    type: 'encapsulate',
                    options: {
                        pre: "{{col-end}}",
                        post: ""
                    }
                }
            }
        }
    });
} );
