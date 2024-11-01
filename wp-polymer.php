<?php
/**
 * Plugin Name: WordPress Polymer Plugin
 * Plugin URI: http://blocknot.es
 * Description: Add Polymer elements to your website!
 * Version: 2.0.4
 * Author: Mat
 * Author URI: http://blocknot.es
 * License: GPL3
 */
namespace wp_polymer;

require_once( plugin_dir_path( __FILE__ ) . 'conf.php' );
require_once( plugin_dir_path( __FILE__ ) . 'wp-polymer-admin.php' );

class wp_polymer
{
	var $components = array();
	var $import = array();
	var $list = array();

/* ========================================================================= */

	function __construct()
	{
	// --- ACTIONS --- //
		add_action( 'wp_enqueue_scripts', array( &$this, 'action_wp_enqueue_scripts' ) );
		add_action( 'wp_head', array( &$this, 'action_wp_head' ) );
		add_action( 'wp_footer', array( &$this, 'action_wp_footer' ) );
	// --- FILTERS --- //
		add_filter( 'template_include', array( &$this, 'filter_template_include' ), 99, 1 );
	// --- SHORTCODES --- //
		add_shortcode( 'polymer', array( &$this, 'shortcode_polymer' ) );
	}

/* ========================================================================= */

	function action_wp_enqueue_scripts()
	{
		global $post;
		wp_enqueue_script( 'polymer-webcomponentsjs', WPP_COMPONENTS_ . 'webcomponentsjs/webcomponents.js', array() );
		if( is_singular() )
		{
			// --- Options ---
			$autop = get_post_meta( $post->ID, 'polymer_autop', TRUE );
			// if( !empty( $poly_autop ) ) add_filter( 'the_content', 'wpautop' , 99 );
			if( empty( $autop ) ) remove_filter( 'the_content', 'wpautop' );
			// --- Includes ---
			$includes = get_post_meta( $post->ID, 'polymer_includes', TRUE );
			if( !empty( $includes ) ) $extra = explode( '|', str_replace( '||', '|', $includes ) );
			// --- Components ---
			$this->components = get_post_meta( $post->ID, 'polymer_components', TRUE );
			if( !empty( $this->components ) && is_array( $this->components ) )
			{
				if( isset( $extra ) )
				{
					foreach( $extra as $component )
					{
						if( !empty( $component ) && array_search( $component, $this->components ) === FALSE ) $this->components[] = $component;
					}
				}
				foreach( $this->components as $component ) $this->import[] = $component . '/' . $component . '.html';
			}
		}
	}

	function action_wp_head()
	{
		foreach( $this->import as $tag => $import ) echo '<link rel="import" href="', WPP_COMPONENTS_, $import,  "\" />\n";
	}

	function action_wp_footer()
	{
		global $post;
		if( is_singular() )
		{
			// --- Javascript code ---
			$javascript = get_post_meta( $post->ID, 'polymer_javascript', TRUE );
			if( !empty( $javascript ) ) echo "<script type=\"text/javascript\">\n", stripslashes( $javascript ), "\n</script>\n";
		}
	}

/* ========================================================================= */

	function filter_template_include( $template )
	{
		global $post;
		if( is_singular() )
		{
			$temp = get_post_meta( $post->ID, 'polymer_template', TRUE );
			if( !empty( $temp ) ) return plugin_dir_path( __FILE__ ) . 'wp-polymer-template.php';
		}
		return $template;
	}

/* ========================================================================= */

	function shortcode_polymer( $atts, $content = '' )
	{
		if( isset( $atts[0] ) && strpos( $atts[0], '-' ) > 0 )
		{
			$tag = $atts[0];
			if( array_search( $tag, $this->components ) !== FALSE )
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
}

if( !is_admin() )
{
	$wp_polymer = new wp_polymer();
}
else
{
	$wp_polymer_admin = new wp_polymer_admin();
}
