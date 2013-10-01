jQuery(document).ready(function() {

  jQuery('#wp_simplify_settings_tabs').tabs();
  
    jQuery(".wp_simplify_show_advanced").click(function() {
      jQuery(".wp_simplify_advanced_options").toggle();
    });    
    
    jQuery("#blank_wordpress").click(function() {
    
      if(!jQuery(".wp_simplify_advanced_options").is(":visible"))
        return;

      var answer = confirm("Are you sure you want to delete all post types, meta data, comments and taxonomies? \nYou cannot undo this - the data will be gone forever.")
      if (answer){
        return true;
      }
      else{
        return false;
      }


    });

      
});