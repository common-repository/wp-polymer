<?php
namespace wp_polymer;

require_once( plugin_dir_path( __FILE__ ) . 'conf.php' );
require_once( plugin_dir_path( __FILE__ ) . 'libs/ganon/ganon.php' );

/**
 > TODO LIST
   - Restore old features
   - Show tip: "Don't you know where to start? Try installing polymer-starter-kit and google-web-components"
 **/
class wp_polymer_admin
{
	var $available = array();
	var $groups = array( 'GoogleWebComponents', 'Polymer', 'PolymerElements', 'webcomponents' );
	var $components = array();
	var $ignore = array(
		'GoogleWebComponents' => array(
			'demos',							// [M]
			'googlewebcomponents.github.io',	// [M]
			'places-app',						// [A]
			'style-guide',						// [M]
		),
		'Polymer' => array(
			'blog',							// [M]
			'cdnjs',						// [M]
			'contacts',						// [M]
			'$core-',						// [D]
			'CustomElements',				// [D]
			'designer',						// [U]
			'docs',							// [D]
			'firebase-element',				// [D]
			'firebase-import',				// [D]
			'font-roboto',					// [D]
			'grunt-audit',					// [U]
			'grunt-vulcanize',				// [U]
			'gulp-audit',					// [U]
			'HTMLImports',					// [D]
			'inspector-elements',			// [T]
			'karma-browserstack-launcher',	// [M]
			'karma-crbot-reporter',			// [M]
			'layout',						// [D]
			'marked-element',				// [D]
			'MutationObservers',			// [D]
			'NodeBind',						// [M]
			'observe-js',					// [M]
			'$paper-',						// [D]
			'pica',							// [A]
			'platform',						// [D]
			'platform-dev',					// [D]
			'polycasts',					// [M]
			'polymer-dev',					// [D]
			'polymer-element-catalog',		// [M]
			'polymer-test-tools',			// [T]
			'polymer-tutorial',				// [M]
			'polymer.github.io',			// [M]
			'project',						// [M]
			'Promises',						// [M]
			// 'sampler-scaffold', 			// ?
			'ShadowDOM',					// [D]
			'TemplateBinding',				// [M]
			'test-fixture',					// [T]
			'todomvc',						// [A]
			'tools',						// [U]
			'topeka',						// [A]
			'topeka-elements',				// [A]
			'URL',							// [U]
			'vulcanize',					// [U]
			'wct-local',					// [T]
			'wct-sauce',					// [T]
			'web-component-tester',			// [T]
			'WeakMap',						// [D]
			'webcomponentsjs-dev',			// [D]
		),
		'PolymerElements' => array(
			'app-layout-templates',			// [M]
			'ContributionGuide',			// [M]
			'molecules',					// [M]
			'style-guide',					// [M]
			'test-all',						// [T]
		),
		'webcomponents' => array(
			'angular-interop',					// [M]
			'chrome-webcomponents-extension',	// [M]
			'element-boilerplate',				// [M]
			'generator-element',				// [M]
			'gold-standard',					// [M]
			'hello-world-element',				// [M]
			'hello-world-polymer',				// [M]
			'hello-world-xtag',					// [M]
			'less-interop',						// [M]
			'polymer-boilerplate',				// [M]
			'sass-interop',						// [M]
			'slush-element',					// [M]
			'webcomponents-icons',				// [M]
			'webcomponents.github.io',			// [M]
			'xtag-boilerplate',					// [M]
		),
	);
	var $hide_keys = array( 'polymer_autop', 'polymer_includes', 'polymer_javascript', 'polymer_template' );
	var $http_opts = array(
		'http' => array(
			'method' => 'GET',
			'header' => "User-Agent: Mozilla/5.0 (Windows NT 6.3; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/46.0.2490.80 Safari/537.36\r\n"
		)
	);
	var $messages = array();
	var $no_remove = array( 'webcomponentsjs' => TRUE );

	// var $_cache = array();
	var $_components = array();

/* ========================================================================= */

	function __construct()
	{
	// --- ACTIONS --- //
		add_action( 'add_meta_boxes', array( &$this, 'action_add_meta_boxes' ) );
		add_action( 'admin_enqueue_scripts', array( &$this, 'action_admin_enqueue_scripts' ) );
		// add_action( 'admin_head', array( &$this, 'action_admin_head' ) );
		// add_action( 'admin_footer', array( &$this, 'action_admin_footer' ) );
		// add_action( 'admin_init', array( &$this, 'action_admin_init' ) );
		add_action( 'admin_menu', array( &$this, 'action_admin_menu' ) );
		// add_action( 'admin_notices', array( &$this, 'action_admin_notices' ) );
		add_action( 'save_post', array( &$this, 'action_save_post' ) );
		add_action( 'wp_ajax_wpp_request', array( &$this, 'action_wp_ajax_wpp_request' ) );
	// --- FILTERS --- //
		add_filter( 'is_protected_meta', array( &$this, 'filter_is_protected_meta' ), 10, 2 );
	// --- SHORTCODES --- //
		add_shortcode( 'polymer', array( &$this, 'shortcode_polymer' ) );
	}

/* ========================================================================= */

	function action_add_meta_boxes()
	{
		add_meta_box( 'wp_polymer_meta', 'Polymer Options', array( &$this, 'wp_polymer_meta' ), 'post', 'normal', 'high' );
		add_meta_box( 'wp_polymer_meta', 'Polymer Options', array( &$this, 'wp_polymer_meta' ), 'page', 'normal', 'high' );
	}

	function action_admin_enqueue_scripts( $hook )
	{	// ACTION
		echo '<script type="text/javascript">var ajax_nonce = "', wp_create_nonce( WPP_NONCE ), '"; var path_plugin = "', plugin_dir_url( __FILE__ ), '"; </script>', "\n";
		wp_enqueue_style( 'wpp_style', plugin_dir_url( __FILE__ ) . 'wp-polymer-admin.css' );
		wp_enqueue_style( 'wpp_codemirror', plugin_dir_url( __FILE__ ) . 'libs/codemirror/codemirror.css' );
		wp_register_script( 'wpp_admin', plugin_dir_url( __FILE__ ) . 'wp-polymer-admin.js', array() );
		wp_register_script( 'wpp_codemirror', plugin_dir_url( __FILE__ ) . 'libs/codemirror/codemirror-javascript.min.js', array() );
		wp_enqueue_script( 'wpp_codemirror' );
		wp_enqueue_script( 'wpp_admin' );
	}

	function action_admin_menu()
	{	// ACTION
		add_options_page( 'Polymer Options', 'WP Polymer', 'manage_options', 'wpp_options', array( &$this, 'menu_page_options' ) );
	}

	// function action_admin_notices()
	// {
	// 	if( !empty( $this->messages ) )		// often empty because filled later
	// 	{
	// 		echo '<div class="updated">';
	// 		foreach( $this->messages as $message ) echo '<p>', $message, '</p>';
	// 		echo "</div>\n";
	// 	}
	// }

	function action_save_post( $post_id )
	{
		if( !isset( $_POST['wp-polymer-meta-nonce'] ) ) return;									// --- Return if nonce is not set
		if( !wp_verify_nonce( $_POST['wp-polymer-meta-nonce'], 'wp-polymer-meta' ) ) return;	// --- Return if nonce is not valid
		if( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;								// --- Return if this is an autosave
		if( isset( $_POST['post_type'] ) && $_POST['post_type'] == 'page' )						// --- Check the user's permissions
		{
			if( !current_user_can( 'edit_page', $post_id ) ) return;
		}
		else
		{
			if( !current_user_can( 'edit_post', $post_id ) ) return;
		}
		if( wp_is_post_revision( $post_id ) ) return;
		// --- Checks passed
		$this->_init();
		$post = get_post( $post_id );
		$content = apply_filters( 'the_content', $post->post_content );
		$meta = array();
		$dom = str_get_dom( $content );
		$components = array();
		// --- Prepare the full list of components
		if( !isset( $this->_cache ) )
		{
			$this->_cache = array();
			foreach( $this->components as $group ) foreach( $group as $comp => $value ) $this->_cache[$comp] = TRUE;
		}
		// --- Look for components in page
		foreach( $dom( '*' ) as $element )
		{	// Traverse the DOM
			if( isset( $this->_cache[$element->tag] ) ) $components[$element->tag] = TRUE;
		}
		// --- Save post meta
		$list = array_keys( $components );
		sort( $list );
		update_post_meta( $post_id, 'polymer_autop', isset( $_POST['polymer_autop'] ) && !empty( $_POST['polymer_autop'] ) );
		update_post_meta( $post_id, 'polymer_components', $list );
		update_post_meta( $post_id, 'polymer_includes', ( isset( $_POST['polymer_includes'] ) && !empty( $_POST['polymer_includes'] ) ) ? addslashes( $_POST['polymer_includes'] ) : '' );
		update_post_meta( $post_id, 'polymer_javascript', ( isset( $_POST['polymer_javascript'] ) && !empty( $_POST['polymer_javascript'] ) ) ? addslashes( $_POST['polymer_javascript'] ) : '' );
		update_post_meta( $post_id, 'polymer_template', isset( $_POST['polymer_template'] ) && !empty( $_POST['polymer_template'] ) );
	}

	function action_wp_ajax_wpp_request()
	{
		check_ajax_referer( WPP_NONCE, 'security', TRUE );		// automatically die if check is not passed
		$ret = array();
		$this->_init();
		switch( $_POST['op'] )
		{
			case 'get_next':
				$context = stream_context_create( $this->http_opts );
				$contents = @file_get_contents( WPP_TMP_LST, FALSE, $context );
				if( $contents !== FALSE )
				{
					$this->_components = unserialize( $contents );
					$ret = $this->_download();
				}
				else $ret['result'] = -2;
				break;
			case 'lists':
				$ret['result'] = 0;
				$items = array();
				foreach( $this->groups as $group )
				{
					$list = $this->_get_list( $group );
					if( !empty( $list ) )
					{
						if( file_put_contents( WPP_COMPONENTS . '.' . $group, serialize( $list ) ) !== FALSE ) $items[$group] = count( $list );
						else $ret['result'] = -3;
					}
					else $ret['result'] = -2;
				}
				if( $ret['result'] == 0 )
				{
					$ret['result'] = 1;
					$ret['items'] = json_encode( $items );
				}
				break;
			case 'prepare':
				$group = sanitize_text_field( $_POST['group'] );
				$comp = sanitize_text_field( $_POST['comp'] );
				if( isset( $this->components[$group][$comp] ) )
				{
					$this->_components[$comp] = $group;
					if( $this->_components( WPP_GIT_CDN . $group . '/' . $comp . '/master/bower.json', $this->_components ) > 0 )
					{
						ksort( $this->_components );
						$ret['components'] = implode( ',', array_keys( $this->_components ) );
						if( @file_put_contents( WPP_TMP_LST, serialize( $this->_components ) ) !== FALSE ) $ret['result'] = 1;
						else
						{
							$ret['result'] = -5;
							$ret['message'] = 'error saving components list';
						}
					}
					else
					{
						$ret['result'] = -4;
						$ret['message'] = 'invalid component';
					}
				}
				else
				{
					$ret['result'] = -3;
					$ret['message'] = 'invalid component (2)';
				}
				break;
			case 'remove':
				$group = sanitize_text_field( $_POST['group'] );
				$comp = sanitize_text_field( $_POST['comp'] );
				if( isset( $this->components[$group][$comp] ) || array_search( $comp, $this->available ) !== FALSE )
				{
					if( file_exists( WPP_COMPONENTS . $comp ) )
					{
						$this->_delete( WPP_COMPONENTS . $comp );		// TODO: check result ?
						$ret['result'] = 1;
					}
					else
					{
						$ret['result'] = -4; 
						$ret['message'] = 'invalid component';
					}
				}
				else
				{
					$ret['result'] = -3;
					$ret['message'] = 'invalid component (2)';
				}
				break;
			case 'remove_list':
				$context = stream_context_create( $this->http_opts );
				$contents = @file_get_contents( WPP_TMP_LST, FALSE, $context );
				if( $contents !== FALSE )
				{
					$this->_components = unserialize( $contents );
					$ret['result'] = 1;
					foreach( $this->_components as $repo => $user )
					{
						if( $this->_delete( WPP_COMPONENTS . $repo ) !== 1 )
						{
							$ret['result'] = -3;
							$ret['message'] = 'remove error';
							break;
						}
					}
				}
				else
				{
					$ret['result'] = -2;
					$ret['message'] = 'no components';
				}
				break;
			default:
				$ret['result'] = -1;
				break;
		}
		header( 'Content-Type: application/json; charset=utf-8' );
		echo json_encode( $ret );
		wp_die();
	}

/* ========================================================================= */

	function filter_is_protected_meta( $protected, $meta_key )
	{
		return ( in_array( $meta_key, $this->hide_keys ) ? TRUE : $protected );
	}

/* ========================================================================= */

	function shortcode_polymer( $atts, $content = '' )
	{
		if( !isset( $this->_cache ) )
		{
			$this->_cache = array();
			foreach( $this->components as $group ) foreach( $group as $comp => $value ) $this->_cache[$comp] = TRUE;
		}
		if( isset( $atts[0] ) && strpos( $atts[0], '-' ) > 0 )
		{
			$tag = $atts[0];
			if( array_search( $tag, $this->_cache ) !== FALSE )
			{
				$ret = '<' . $tag;
				foreach( $atts as $key => $value )
				{
					if( is_numeric( $key ) ) $ret .= ' ' . esc_attr( $value );
					else $ret .= ' ' . esc_attr( $key ) . '="' . esc_attr( $value ) . '"';
				}
				$ret .= '>' . do_shortcode( $content ) . '</' . $tag . '>';
				return $ret;
			}
		}
		return $content;
	}

/* ========================================================================= */

	function menu_page_options()
	{
		$this->_init();
?>
<div class="wrap" id="wpp_options">
	<table class="wpp_header"><tr>
		<td><h2>WP Polymer Options</h2></td>
		<td class="info">
			<div><a target="_blank" href="http://www.blocknot.es/home/me/"><img alt="Donate" src="http://www.paypalobjects.com/en_US/i/btn/btn_donate_LG.gif"></a></div>
			<div><a class="link" target="_blank" href="http://www.polymer-project.org/">Polymer Project</a></div>
			<div class="buttons"><button id="btnUpdateLists">Update components list</button></div>
		</td>
	</tr></table>
<?php
	if( !empty( $this->messages ) )
	{
		echo '<div class="output" style="display: block">';
		foreach( $this->messages as $message ) echo '<div><img class="ic_notice" src="', plugin_dir_url( __FILE__ ), 'images/ic_notice.png" alt="" /> ', $message, '</div>';
		echo '</div>';
	}
	else echo '<div class="output"></div>';
?>
	<div class="refresh"><button onclick="Javascript:window.location.reload();">Refresh</button></div>
<?php
	echo '<div class="components">', "\n";

	$others = $this->available;
	foreach( $this->groups as $group )
	{
		if( isset( $this->components[$group] ) )
		{
			$intro = '';
			echo "<h3>$group</h3>\n";
			echo '<table class="group">';
			foreach( $this->components[$group] as $comp => $value )
			{
				$available = ( array_search( $comp, $this->available ) !== FALSE );
				if( $available ) unset( $others[array_search($comp,$this->available)] );
				$pos = strpos( $comp, '-' );
				$pre = ( $pos !== FALSE ) ? substr( $comp, 0, $pos ) : '';
				echo '<tr>';
				if( $intro != $pre )
				{
					$intro  = $pre;
					echo '<td class="intro">', $intro, '</td>';
				}
				else echo '<td class="intro">&nbsp;</td>';
				echo '<td><img src="', plugin_dir_url( __FILE__ ), 'images/', $available ? 'ic_on.png' : 'ic_off.png', '" alt="" />&nbsp; ', $comp, '</td>';
				echo '<td class="links">';
				if( $available && !isset( $this->no_remove[$comp] ) ) echo '<input type="button" value="remove" data-group="', $group, '" data-comp="', $comp, '" class="wpp_br available" />&nbsp; ';
				echo '<input type="button" value="', $available ? 're' : '&nbsp;&nbsp;', 'install" data-group="', $group, '" data-comp="', $comp, '" class="wpp_bc', $available ? ' available' : '', '" /> &nbsp; ';
				echo '<a href="', WPP_GIT . $group . '/' . $comp, '" target="_blank">GIT page</a>';
				echo "</td></tr>\n";
			}
			echo "</table>\n";
		}
	}

	if( count( $others ) > 0 )
	{
		echo "<h3>Other installed components</h3>\n";
		echo '<table class="group">';
		foreach( $others as $other )
		{
			echo '<tr>';
			echo '<td><img src="', plugin_dir_url( __FILE__ ), 'images/ic_on.png', '" alt="" />&nbsp; ', $other, '</td>';
			echo '<td class="links">';
			if( !isset( $this->no_remove[$other] ) ) echo '<input type="button" value="remove" data-group="" data-comp="', $other, '" class="wpp_br available" />&nbsp; ';
			echo '<a href="', WPP_WEB_SEARCH, $other, '+site:github.com" target="_blank">web search</a>';
			echo "</td></tr>\n";
		}
		echo "</table>\n";
	}

	echo "</div>\n";
?>
</div>
<?php
	}

	function wp_polymer_meta( $post )
	{
		$this->_init();
		$components = get_post_meta( $post->ID, 'polymer_components', TRUE );
		$javascript = get_post_meta( $post->ID, 'polymer_javascript', TRUE );
		$includes = get_post_meta( $post->ID, 'polymer_includes', TRUE );
		$autop = get_post_meta( $post->ID, 'polymer_autop', TRUE );
		$template = get_post_meta( $post->ID, 'polymer_template', TRUE );
		wp_nonce_field( 'wp-polymer-meta', 'wp-polymer-meta-nonce' );
?>
<div id="wpp_meta_box">
	<div class="wpp_margt">
		<input type="checkbox" id="polymer_autop" name="polymer_autop"<?php echo empty( $autop ) ? '' : ' checked="checked"'; ?>/> <label for="polymer_autop">Enable autop (it could break multi line Polymer code)</label><br/>
		<input type="checkbox" id="polymer_template" name="polymer_template"<?php echo empty( $template ) ? '' : ' checked="checked"'; ?>/> <label for="polymer_template">Override template (it will show only the page content)</label>
	</div>
	<hr/>
	<div class="wpp_title wpp_margb">Javascript code (added in page footer):</div>
	<textarea name="polymer_javascript" id="wpp_editor" cols="80" rows="4"><?php echo stripslashes( $javascript ); ?></textarea>
	<div class="wpp_title wpp_margt">Components found in this page (red: not installed):</div>
	<div class="wpp_found"><?php
		if( !empty( $components ) && is_array( $components ) )
		{
			foreach( $components as $component )
			{
				echo '<span class="cmp', ( array_search( $component, $this->available ) !== FALSE ) ? '' : ' missing', '">', $component, '</span>';
			}
		}
	?></div>
	<hr/>
	<div><a href="<?php echo admin_url( 'options-general.php?page=wpp_options' ); ?>">Install new components</a></div>
	<hr/>
	<div class="wpp_title">Installed components (click to force import):</div>
	<div class="wpp_installed"><?php
		if( !empty( $this->available ) && is_array( $this->available ) )
		{
			foreach( $this->available as $component )
			{
				echo '<span class="cmp', ( strpos( $includes, "|$component|" ) !== FALSE ) ? ' include' : '', '">', $component, '</span>';
			}
		}
	?></div>
	<input type="hidden" id="polymer_includes" name="polymer_includes" value="<?php echo stripslashes( $includes ); ?>" />
</div>
<?php
	}

/* ========================================================================= */

	function _components( $src, &$components )
	{
		$ret = 1;
		$context = stream_context_create( $this->http_opts );
		$contents = @file_get_contents( $src, FALSE, $context );
		if( $contents !== FALSE )
		{
			$data = json_decode( $contents );
			if( isset( $data->dependencies ) )
			{
				foreach( $data->dependencies as $key => $dependency )
				{
					$p1 = strpos( $dependency, '/' );
					if( $p1 > 0 )
					{
						$user = substr( $dependency, 0, $p1 );
						$p2 = strpos( $dependency, '#', $p1 );
						if( $p2 > 0 )
						{
							$repo = substr( $dependency, $p1 + 1, $p2 - $p1 - 1 );
							if( !isset( $components[$repo] ) )
							{
								$components[$repo] = $user;
								$this->_components( WPP_GIT_CDN . $user . '/' . $repo . WPP_GIT_JSON, $components );
							}
						}
					}
				}
			}
		}
		else $ret = -1;
		return $ret;
	}

	function _delete( $dir )
	{ 
		if( is_dir( $dir ) )
		{ 
			$objects = scandir( $dir );
			foreach( $objects as $object )
			{ 
				if( $object != '.' && $object != '..' )
				{ 
					if( is_dir( $dir . '/' . $object ) ) $this->_delete( $dir . '/'. $object );
					else unlink( $dir . '/' . $object );
				}
			}
			rmdir( $dir );
			return 1;
		}
		return 0;
	}

	function _download()
	{
		$ret = array( 'result' => 0, 'message' => 'done' );
		foreach( $this->_components as $repo => $user )
		{
			$ret['name'] = $repo;
			if( !file_exists( WPP_COMPONENTS . $repo ) )
			{
				$err = $this->_get_file( WPP_GIT . $user . '/' . $repo . WPP_GIT_ARCHIVE, WPP_TMP_ZIP );
				if( !$err )
				{
					$err = $this->_unzip( WPP_TMP_ZIP, WPP_COMPONENTS );
					if( !$err )
					{
						if( @rename( WPP_COMPONENTS . $repo . '-master', WPP_COMPONENTS . $repo ) )
						{
							$ret['result'] = 1;
							$ret['message'] = 'ok';
						}
						else
						{
							$ret['result'] = -1;
							$ret['message'] = 'rename error';
						}
					}
					else
					{
						$ret['result'] = -2;
						$ret['message'] = 'unzip error ' . $err;
					}
				}
				else
				{
					$ret['result'] = -3;
					$ret['message'] = 'get file error ' . $err;
				}
			}
			else
			{
				//$ret['result'] = 2;
				//$ret['message'] = 'already installed';
				continue;
			}
			break;		// Only one then break;
		}
		return $ret;
	}

	function _get_file( $url, $output )
	{
		$fopen = TRUE;
		// $fopen = !empty( ini_get( 'allow_url_fopen' ) );
		$curl = function_exists( 'curl_version' );
		if( file_exists( $output ) ) unlink( $output );
		$err = 0;
		if( $fopen )
		{
			$fp = fopen( $url, 'r' );
			if( $fp !== FALSE )
			{
				if( file_put_contents( $output, $fp ) === FALSE ) $err = 102;
				fclose( $fp );
			}
			else $err = 101;
		}
		else if( $curl )
		{
			$fp = fopen( $output, 'wb' );
			if( $fp !== FALSE )
			{
				$ch = curl_init( $url );
				if( $ch !== FALSE )
				{
					curl_setopt( $ch, CURLOPT_FILE, $fp );
					curl_setopt( $ch, CURLOPT_HEADER, 0 );
					curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, TRUE );
					// curl_setopt( $ch, CURLOPT_REFERER, "http://www.php.net" );
					if( curl_exec( $ch ) === FALSE ) $err = 201;
					curl_close( $ch );
				}
				else $err = 200;
				fclose( $fp );
			}
			else $err = 202;
		}
		else $err = 1;
		return $err;
	}

	function _get_list( $user )
	{
		$done = FALSE;
		$page = 1;
		$list = array();
		$context = stream_context_create( $this->http_opts );
		$url = WPP_GIT_API . $user . WPP_GIT_API_POST;
		while( !$done )
		{
			$contents = @file_get_contents( $url . $page, FALSE, $context );
			if( $contents !== FALSE )
			{
				$items = json_decode( $contents );
				foreach( $items as $item ) $list[$item->name] = $item->full_name;
				if( count( $items ) < 30 ) break;
			}
			else break;
			$page++;
		}
		return $list;
	}

	function _init()
	{
		$context = stream_context_create( $this->http_opts );
		$list = scandir( WPP_COMPONENTS );
		foreach( $list as $item ) if( $item[0] != '.' ) $this->available[] = $item;
		foreach( $this->groups as $group )
		{
			$contents = @file_get_contents( WPP_COMPONENTS_ . '.' . $group, FALSE, $context );
			if( $contents !== FALSE )
			{
				$this->components[$group] = unserialize( $contents );
				if( isset( $this->ignore[$group] ) )
				{
					foreach( $this->ignore[$group] as $component )
					{
						if( isset( $this->components[$group][$component] ) ) unset( $this->components[$group][$component] );
						else if( $component[0] == '$' )
						{
							$ignore = substr( $component, 1 );
							foreach( $this->components[$group] as $key => $value )
							{
								if( strncmp( $key, $ignore, strlen( $ignore ) ) === 0 ) unset( $this->components[$group][$key] );
							}
						}
					}
				}
			}
			else $this->messages[] = $group . ' component list not available';
		}
	}

	function _unzip( $zipfile, $output )
	{
		require_once( plugin_dir_path( __FILE__ ) . 'libs/pclzip/pclzip.lib.php' );
		$err = 0;
		if( substr( $output, -1, 1 ) != '/' ) $output .= '/';
		$zip = new \PclZip( $zipfile );
		$zip->extract( PCLZIP_OPT_PATH, $output );		// 0 = ok
		/*
		if( class_exists( 'PharData' ) )
		{	// Using PharData
			try
			{
				$pd = new PharData( $zipfile );
				if( $pd->extractTo( $output ) )
				{
					// ok
				}
				else $ret = 6;
			}
			catch( Exception $e )
			{
				$ret = 5;
			}
		}
		else if( function_exists( 'zip_open' ) )
		{	// Using zip_open
			$zip = zip_open( $zipfile );
			if( is_resource( $zip ) )
			{
				$last_dir = FALSE;
				while( $entry = zip_read( $zip ) )
				{
					$name = $output . zip_entry_name( $entry );
					$is_dir = ( substr( zip_entry_name( $entry ), -1, 1 ) == '/' );
					if( $is_dir )
					{
						if( !file_exists( $name ) ) mkdir( $name, 0777, TRUE );
						if( !empty( $last_dir ) )
						{
							zip_entry_close( $last_dir );
							$last_dir = FALSE;
						}
						if( zip_entry_open( $zip, $entry ) ) $last_dir = $entry;
						else $err = 2;
					}
					else
					{
						if( file_put_contents( $name, zip_entry_read( $entry, zip_entry_filesize( $entry ) ) ) )
						{
							chmod( $name, 0777 );
						}
						else $err = 3;
					}
					//echo '<pre>', var_export( $name, TRUE ), "</pre>\n";
				}
				if( !empty( $last_dir ) ) zip_entry_close( $last_dir );
				zip_close( $zip );
			}
			else $err = 1;
		}
		else $err = 4;
		*/
		return $err;
	}
}
