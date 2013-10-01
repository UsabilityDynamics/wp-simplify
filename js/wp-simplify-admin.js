/**
 * WP-Simplify Admin
 *
 * @author potanin@UD
 * @version 1.3.1
 */
if( typeof jQuery === 'function' ) {

  jQuery( document ).ready( function() {

    if( 'function' === typeof jQuery.fn.tabs ) {
      jQuery( '.wp_simplify_settings_tabs' ).tabs();
    }

  });

}
