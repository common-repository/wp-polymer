
function getNext()
{
	jQuery( '#wpp_options .output' ).append( '&raquo; Next dependency... ' );
	var data = {
		action: 'wpp_request',
		op: 'get_next',
		security: ajax_nonce,
	};
	jQuery.post( ajaxurl, data, function( response ) {
		// console.log( response );		// DEBUG
		if( response.result == 1 )
		{
			jQuery( '#wpp_options .output' ).append( '<b>' + response.name + "</b>: done.\n" );
			getNext();
		}
		else
		{
			if( response.result === 0 ) jQuery( '#wpp_options .output' ).append( "none.\n&raquo; Finish." );
			else jQuery( '#wpp_options .output' ).append( '<b>' + response.name + '</b>: ' + response.message + ".\n" );
			jQuery( '#wpp_options .loading' ).hide();
			jQuery( '#wpp_options .refresh' ).show();
		}
	}).fail( function( response ) {
		jQuery( '#wpp_options .output' ).append( 'error<span class="error">' + response.responseText + "</span>.\n" );
	});
}

function removeList()
{
	jQuery( '#wpp_options .output' ).append( '&raquo; Removing components... ' );
	var data = {
		action: 'wpp_request',
		op: 'remove_list',
		security: ajax_nonce,
	};
	jQuery.post( ajaxurl, data, function( response ) {
		// console.log( response );		// DEBUG
		if( response.result == 1 )
		{
			jQuery( '#wpp_options .output' ).append( "done.\n" );
			getNext();
		}
		else
		{
			jQuery( response.message );
			jQuery( '#wpp_options .loading' ).hide();
			jQuery( '#wpp_options .refresh' ).show();
		}
	}).fail( function( response ) {
		jQuery( '#wpp_options .output' ).append( 'error<span class="error">' + response.responseText + "</span>.\n" );
	});
}

jQuery(document).ready(function($){
	var editor = document.getElementById( 'wpp_editor' );
	if( editor !== null )
	{
		CodeMirror.fromTextArea( document.getElementById( 'wpp_editor' ), {
			lineNumbers: true
		});
	}
	// --- update lists --- //
	jQuery('#btnUpdateLists').click( function( e ) {
		jQuery('html, body').animate( { scrollTop: 0 } );
		jQuery( '#wpp_options .buttons' ).hide();
		jQuery( '#wpp_options .components' ).hide();
		jQuery( '#wpp_options .output' ).show();
		jQuery( '#wpp_options .output' ).html( '<div class="loading"><img src="' + path_plugin + 'images/ajax-loader.gif" /></div>' );
		jQuery( '#wpp_options .output' ).append( '&raquo; Updating lists... ' );
		var data = {
			action: 'wpp_request',
			op: 'lists',
			security: ajax_nonce,
		};
		jQuery.post( ajaxurl, data, function( response ) {
			// console.log( response );		// DEBUG
			if( response.result == 1 ) jQuery( '#wpp_options .output' ).append( "done.\n" );
			else jQuery( '#wpp_options .output' ).append( "update error.\n" );
			jQuery( '#wpp_options .loading' ).hide();
			jQuery( '#wpp_options .refresh' ).show();
		}).fail( function( response ) {
			jQuery( '#wpp_options .output' ).append( 'error<span class="error">' + response.responseText + "</span>.\n" );
		});
	});
	// --- install --- //
	jQuery('.wpp_bc').click( function( e ) {
		var reinstall = false;
		jQuery('html, body').animate( { scrollTop: 0 } );
		jQuery( '#wpp_options .buttons' ).hide();
		jQuery( '#wpp_options .components' ).hide();
		jQuery( '#wpp_options .output' ).show();
		jQuery( '#wpp_options .output' ).html( '<div class="loading"><img src="' + path_plugin + 'images/ajax-loader.gif" /></div>' );
		if( jQuery(this).hasClass( 'available' ) ) reinstall = true;
		jQuery( '#wpp_options .output' ).append( '&raquo; Searching components for <b>' + $(this).attr( 'data-comp' ) + '</b>... ' );
		var data = {
			action: 'wpp_request',
			op: 'prepare',
			security: ajax_nonce,
			group: $(this).attr( 'data-group' ),
			comp: $(this).attr( 'data-comp' ),
		};
		jQuery.post( ajaxurl, data, function( response ) {
			// console.log( response );		// DEBUG
			if( response.result == 1 )
			{
				jQuery( '#wpp_options .output' ).append( "done.\n" );
				if( !reinstall ) getNext();
				else removeList();
			}
			else
			{
				jQuery( '#wpp_options .output' ).append( response.message + ".\n" );
				jQuery( '#wpp_options .loading' ).hide();
			}
		}).fail( function( response ) {
			jQuery( '#wpp_options .output' ).append( 'error<span class="error">' + response.responseText + "</span>.\n" );
		});
	});
	// --- remove --- //
	jQuery('.wpp_br').click( function( e ) {
		if( confirm( 'Confirm remove?' ) )
		{
			jQuery('html, body').animate( { scrollTop: 0 } );
			jQuery( '#wpp_options .output' ).show();
			jQuery( '#wpp_options .output' ).html( '<div class="loading"><img src="' + path_plugin + 'images/ajax-loader.gif" /></div>' );
			jQuery( '#wpp_options .output' ).append( '&raquo; Removing <b>' + $(this).attr( 'data-comp' ) + '</b>... ' );
			var data = {
				action: 'wpp_request',
				op: 'remove',
				security: ajax_nonce,
				group: $(this).attr( 'data-group' ),
				comp: $(this).attr( 'data-comp' ),
			};
			jQuery.post( ajaxurl, data, function( response ) {
				jQuery( '#wpp_options .loading' ).hide();
				jQuery( '#wpp_options .output' ).append( ( response.result == 1 ) ? "done.\n" : ( response.message + ".\n" ) );
				jQuery( '#wpp_options .refresh' ).show();
			}).fail( function( response ) {
				jQuery( '#wpp_options .output' ).append( 'error<span class="error">' + response.responseText + "</span>.\n" );
			});
		}
	});
	// --- components help --- //
	jQuery('.wpp_found .cmp').click( function() {
		window.open( 'https://www.google.nl/search?q=' + jQuery(this).text(), '_blank' );
	});
	// --- force import --- //
	jQuery('.wpp_installed .cmp').click( function() {
		var val = jQuery('#polymer_includes').val();
		if( !jQuery(this).hasClass( 'include' ) )
		{
			jQuery(this).addClass( 'include' );
			jQuery('#polymer_includes').val( val + '|' + $(this).text() + '|' );
		}
		else
		{
			jQuery(this).removeClass( 'include' );
			jQuery('#polymer_includes').val( val.replace( '|' + $(this).text() + '|', '' ) );
		}
	});
});
