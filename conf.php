<?php
namespace wp_polymer;

define( 'WPP_GIT', 'https://github.com/' );
define( 'WPP_GIT_API', 'https://api.github.com/users/' );
define( 'WPP_GIT_API_POST', '/repos?per_page=30&page=' );
define( 'WPP_GIT_ARCHIVE', '/archive/master.zip' );
define( 'WPP_GIT_CDN', 'https://cdn.rawgit.com/' );
define( 'WPP_GIT_JSON', '/master/bower.json' );

define( 'WPP_COMPONENTS', plugin_dir_path( __FILE__ ) . 'components/' );
define( 'WPP_COMPONENTS_', plugin_dir_url( __FILE__ ) . 'components/' );
define( 'WPP_TMP_ZIP', WPP_COMPONENTS . '.tmp.zip' );
define( 'WPP_TMP_LST', WPP_COMPONENTS . '.tmp.lst' );

define( 'WPP_NONCE', 'wpp20151108' );
define( 'WPP_WEB_SEARCH', 'https://www.google.nl/search?q=' );
