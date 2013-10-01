/**
 * Basecamp Style JavaScript
 *
 * @author potanin@UD
 * @version 1.3.1
 */
jQuery( document ).ready( function() {

  /* We are always unfolded */
  jQuery( 'body' ).removeClass( 'folded' );

  jQuery( '.menu-top .wp-submenu' ).hide();

  /* Menus are never open by default */
  jQuery( '.menu-top' ).removeClass( 'wp-menu-open' );

  jQuery( '.menu-top' ).mouseenter(function() {
    jQuery( '.wp-submenu', this ).show();
  } ).mouseleave( function() { jQuery( '.wp-submenu', this ).hide(); } );

} );

