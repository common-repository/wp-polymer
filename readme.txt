=== WordPress Polymer Plugin ===
Contributors: blocknot.es
Tags: plugin,google,shortcode,page,posts,Post
Donate link: http://www.blocknot.es/home/me/
Requires at least: 4.0
Tested up to: 4.3.1
Stable tag: trunk
License: GPL3
License URI: http://www.gnu.org/licenses/gpl-3.0.txt

Add the latest Polymer elements to your website!
Polymer brings an implementation of Google Material Design to the web.

== Description ==

This plugin allows to add Polymer elements to your posts and pages, the same components used in Android OS. You can use the HTML editor with the Polymer tags directly or the shortcode *[polymer]* for all the elements. The correct HTML libraries will be imported automatically but must be installed from the plugin option page.
For Polymer documentation please look at the official website: [Polymer Project](http://www.polymer-project.org/)

This is a rewritten of my previous plugin [Polymer for WordPress](https://wordpress.org/plugins/polymer-components/), now it allows to use the latest Polymer libraries.

**Features**

* Polymer tags directly available in posts / pages with the HTML editor;
* components installer (install, remove and update);
* [polymer] shortcode to access all installed tags;
* auto import the necessary HTML components;
* force import components;
* Javascript editor in posts / pages admin;
* template override option;
* autop option.

**Shortcode**

[polymer ELEMENT-TAG ELEMENT-OPTIONS]ELEMENT-TEXT[/polymer]

Tags: core-icon, paper-button, paper-checkbox, paper-slider, etc.

Options: style, id, class, etc.

**Examples**

	[polymer paper-checkbox][/polymer]
	[polymer paper-button raised id="btn1" style="color: green"]A green button[/polymer]
	[polymer paper-item icon="home" label="Test link"]<a href="http://www.google.it" target="_blank"></a>[/polymer]

**Notes**

* autop option: the autop() Wordpress function adds p and br tags to the contents when a newline is found, but this can break the Polymer tags. This option allows to enable/disable autop() in posts / pages (plugin default: no autop)
* template override option: if it is enabled the plugin will load a special template which provides only the required components to run a Polymer app. This is useful if you want a "fullscreen" Polymer app

== Installation ==

1. Install the plugin
1. Activate it
1. Open Settings \ WP Polymer to install Polymer components
1. Edit a post or a page and insert Polymer tags
1. Optionally change the options in the editor meta box

== Frequently Asked Questions ==

= How can I do a specific thing with Polymer? =

I'm not a Polymer expert, please look in the [Polymer Project](http://www.polymer-project.org/) documentation on the official website or look for help in forums like [StackOverflow](http://stackoverflow.com/questions/tagged/polymer).

= How can I interact with the Polymer elements? =

You can add your Javascript code for your page or post in the Javascript editor under the content editor - Polymer Components meta box.
Sample code to open a dialog from a button click:

	window.addEventListener('polymer-ready', function(e) {
	  document.querySelector('#btn_test').addEventListener('click', function(e) {
	    document.querySelector('#my-dialog').toggle();
	  });
	});

== Screenshots ==

1. Some Polymer elements in a post
1. A dialog example

== Changelog ==

= 2.0.4 =
* Small fix to shortcode, minor improvements

= 2.0.1 =
* First release

== Upgrade Notice ==

= 2.0.4 =
* Small fix to shortcode, minor improvements

= 2.0.1 =
* First release
