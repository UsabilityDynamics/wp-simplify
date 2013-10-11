<?php
/**
 *
 */
namespace UsabilityDynamics\Simplify {

  class Bootstrap {

    /**
     * Simplify core version.
     *
     * @static
     * @property $version
     * @type {Object}
     */
    public static $version = '1.4.0';

    /**
     * Textdomain String
     *
     * @public
     * @property text_domain
     * @var string
     */
    public static $text_domain = 'wp-simplify';

    /**
     * Plugin Path
     *
     * @public
     * @property path
     * @var string
     */
    public static $path = null;

    /**
     * Plugin URL
     *
     * @public
     * @property url
     * @var string
     */
    public static $url = null;

    /**
     * Singleton Instance Reference.
     *
     * @public
     * @static
     * @property $instance
     * @type {Object}
     */
    public static $instance = false;

    /**
     * Adds all the plugin hooks
     *
     * @for Simplify
     * @method __construct
     * @since 0.5
     */
    public function __construct() {

      // Set Variables
      define( 'WP_Simplify_Path', self::$path = untrailingslashit( plugin_dir_path( __FILE__ ) ) );
      define( 'WP_Simplify_URL', self::$url = untrailingslashit( plugin_dir_url( __FILE__ ) ) );

      // Initialize hooks.
      add_action( 'after_setup_theme', array( $this, 'after_setup_theme' ) );

      // Register activation hook -> has to be in the main plugin file
      register_deactivation_hook( __FILE__, array( $this, 'handle_deactivation' ) );

    }

    /**
     * Setup Primary Hooks
     *
     * @method after_setup_theme
     * @for Simplify
     */
    public function after_setup_theme() {
      add_action( 'init', array( $this, 'init' ) );
      add_action( 'admin_init', array( $this, 'admin_init' ) );
      add_action( 'load-options-general.php', array( $this, 'general_settings_admin' ) );
      add_action( 'admin_menu', array( $this, 'admin_menu' ), 99 );
      add_action( 'admin_head', array( $this, 'admin_head' ) );
      add_action( 'favorite_actions', array( $this, 'favorite_actions' ) );
      add_action( 'template_redirect', array( $this, 'template_redirect' ), 1 );
      add_action( 'widgets_init', array( $this, 'widgets_init' ) );
      add_action( 'wp_dashboard_setup', array( $this, 'wp_dashboard_setup' ) );
      add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );

    }

    /**
     * Load global back-end scripts
     *
     * @since 1.3.0
     *
     */
    public function admin_enqueue_scripts() {
      wp_enqueue_style( 'wp-simplify' );
      wp_enqueue_style( 'wp-simplify-printer-fixes' );
    }

    /**
     * Remove settings and restore state on deactivation.
     *
     * @since 0.60
     *
     */
    public function handle_deactivation() {

      delete_option( 'wp_simplify' );

      $WP_Roles = new \WP_Roles;

      foreach( $WP_Roles->roles as $role => $role_caps ) {

        if( $role == 'administrator' ) {
          $WP_Roles->add_cap( $role, 'edit_themes' );
          $WP_Roles->add_cap( $role, 'edit_plugins' );
        }

      }

    }

    /**
     * Deletes all posts, pages, taxonomies and comments.
     *
     * @since 0.60
     *
     */
    public function init() {

      // Plug page actions -> Add Settings Link to plugin overview page
      add_filter( 'plugin_action_links', array( $this, 'plugin_action_links' ), 10, 2 );

      $wp_simplify = get_option( 'wp_simplify' );

      if( file_exists( self::get_instance()->url . '/css/wp-simplify-printer-fixes.css' ) ) {
        wp_register_style( 'wp-simplify-printer-fixes', self::$url . '/css/wp-simplify-printer-fixes.css', '', '', 'print' );
      }

      // If no settings exist, do nothing.
      if( !is_array( $wp_simplify ) )
        return;

      if( isset( $_REQUEST[ 'wp_simplify_nonce' ] ) ) {
        if( wp_verify_nonce( $_REQUEST[ 'wp_simplify_nonce' ], "wp_simplify_update" ) ) {
        }
      }

      if( !in_array( 'disable_theme_editor', $wp_simplify ) ) {
        // enable edit_themes

        $WP_Roles = new \WP_Roles;

        foreach( (array) $WP_Roles->roles as $role => $role_caps ) {

          if( $role == 'administrator' ) {
            $WP_Roles->add_cap( $role, 'edit_themes' );
            $WP_Roles->add_cap( $role, 'edit_plugins' );
          }

        }

      } else {
        $WP_Roles = new WP_Roles;
        foreach( (array) $WP_Roles->roles as $role => $role_caps ) {
          $WP_Roles->remove_cap( $role, 'edit_themes' );
          $WP_Roles->remove_cap( $role, 'edit_plugins' );
        }
      }

    }

    /**
     * Deletes all posts, pages, taxonomies and comments.
     *
     * @since 0.60
     *
     */
    public function plugin_action_links( $links, $file ) {

      if( $file == 'wp-simplify/wp-simplify.php' ) {
        $settings_link = '<a href="' . admin_url( 'options-general.php#wp_simplify_anchor' ) . '">' . __( 'Settings' ) . '</a>';
        array_unshift( $links, $settings_link ); // before other links
      }

      return $links;
    }

    /**
     * Deletes all posts, pages, taxonomies and comments.
     *
     * UI for triggering this method has been removed.
     *
     * @depreciated
     * @since 0.60
     */
    public function blank_wordpress() {
      global $wpdb;
      $wpdb->query( "TRUNCATE TABLE {$wpdb->terms}" );
      $wpdb->query( "TRUNCATE TABLE {$wpdb->term_relationships}" );
      $wpdb->query( "TRUNCATE TABLE {$wpdb->posts}" );
      $wpdb->query( "TRUNCATE TABLE {$wpdb->postmeta}" );
      $wpdb->query( "TRUNCATE TABLE {$wpdb->comments}" );
      $wpdb->query( "TRUNCATE TABLE {$wpdb->commentmeta}" );
      $wpdb->query( "TRUNCATE TABLE {$wpdb->term_taxonomy}" );
    }

    /**
     * Front-end functions.
     *
     *
     * @since 0.5
     */
    public function template_redirect() {

      $wp_simplify = get_option( 'wp_simplify' );

      if( !is_array( $wp_simplify ) ) {
        return;
      }

      if( isset( $_SERVER[ 'HTTPS' ] ) && in_array( 'force_ssl_on_front_end', $wp_simplify ) ) {

        if( $_SERVER[ 'HTTPS' ] != "on" ) {
          $url = "https://" . $_SERVER[ 'SERVER_NAME' ] . $_SERVER[ 'REQUEST_URI' ];
          header( "Location: $url" );
          exit;
        }
      }

    }

    /**
     * Adds all the plugin hooks
     *
     *
     * @since 0.5
     */
    public function admin_head() {

      $wp_simplify = get_option( 'wp_simplify' );

      if( !is_array( $wp_simplify ) ) {
        return;
      }

      if( in_array( 'disable_useless_metaboxes', $wp_simplify ) ) {

        self::remove_ui_elements( 'page', array(
          'postcustom',
          'trackbacksdiv',
          'revisionsdiv',
          'slugdiv',
          'authordiv'
        ) );
        self::remove_ui_elements( 'post', array(
          'postcustom',
          'trackbacksdiv',
          'revisionsdiv',
          'slugdiv',
          'authordiv'
        ) );
      }

      echo "<style type='text/css'>";

      $home_page_id = get_option( 'page_on_front' );
      if( in_array( 'highlight_homepage', $wp_simplify ) && get_option( 'show_on_front' ) == 'page' && !empty( $home_page_id ) ) {
        echo ".wp-list-table #post-{$home_page_id} { background: #FEFBDD; }";
      }

      if( in_array( 'disable_change_permalinks_button', $wp_simplify ) ) {
        echo "#change-permalinks, #edit-slug-buttons {display:none;}";
      }

      if( in_array( 'disable_quick_edit_link', $wp_simplify ) ) {
        echo ".row-actions .inline {display:none;}";
      }

      if( in_array( 'disable_pointers', $wp_simplify ) ) {
        remove_action( 'admin_print_footer_scripts', array( 'WP_Internal_Pointers', 'pointer_wp330_toolbar' ) );
        remove_action( 'admin_print_footer_scripts', array( 'WP_Internal_Pointers', 'pointer_wp330_media_uploader' ) );
        remove_action( 'admin_print_footer_scripts', array( 'WP_Internal_Pointers', 'pointer_wp330_saving_widgets' ) );
      }

      if( in_array( 'disable_wp_logo', $wp_simplify ) ) {
        echo "#header-logo, #wp-admin-bar-wp-logo {display:none;} #site-heading{padding-left:0px;}";
      }

      echo "</style>";

    }

    /**
     * Saves WP-Simplify settings on General Settings page
     *
     *
     * @since 1.0
     */
    public function general_settings_admin() {

      // Load WP-Simplify settings page script
      wp_enqueue_script( 'wp-simplify' );
      wp_enqueue_script( 'jquery-ui-tabs' );

      // Process settings
      if( isset( $_REQUEST[ 'wp_simplify_nonce' ] ) && wp_verify_nonce( $_REQUEST[ 'wp_simplify_nonce' ], "wp_simplify_update" ) ) {
        update_option( 'wp_simplify', $_REQUEST[ 'wp_simplify' ] );
      }

    }

    /**
     * Highest level admin-specific functions.
     *
     *
     * @since 1.0
     */
    public function admin_init() {
      global $current_user;

      //** Get current user's admin color settings, in case it's basecamp */
      $current_color = get_user_option( 'admin_color', $current_user->data->ID );

      $wp_simplify = get_option( 'wp_simplify' );

      //** Make sure it's alway an array */
      $wp_simplify = is_array( $wp_simplify ) ? $wp_simplify : array();

      // Forward non-admins to front-end
      if( in_array( 'disable_backend_access_to_non_admins', $wp_simplify ) ) {
        if( !current_user_can( 'level_10' ) && !current_user_can( 'access_backend' ) && !isset( $_REQUEST[ 'action' ] ) ) {
          header( 'Location:  ' . get_bloginfo( "url" ) );
          die();
        }
      }

      // Register scripts and styles
      wp_register_script( 'wp-simplify', self::$url . '/js/wp-simplify-admin.js', array( 'jquery' ) );
      wp_register_style( 'wp-simplify', self::$url . '/css/wp-simplify-admin.css' );

      // Add the field with the names and function to use for our new settings, put it in our new section
      add_settings_field( 'wp_simplify', 'WP-Simplify Settings', array( $this, 'settings_page' ), 'general' );

      // Register our setting so that $_POST handling is done for us and our callback function just has to echo the <input>
      register_setting( 'general', 'wp_simplify' );

      add_filter( 'admin_user_info_links', array( $this, 'admin_user_info_links' ), 0, 2 );

      // Enable Post and Page Locking
      if( in_array( 'allow_post_locking', $wp_simplify ) ) {
        add_action( 'post_submitbox_misc_actions', array( $this, 'show_post_locking_checkbox' ) );
        add_action( 'save_post', array( $this, 'save_post' ), 0, 2 );
        add_action( 'delete_post', array( $this, 'delete_post' ) );
        add_action( 'trash_post', array( $this, 'delete_post' ) );
        add_filter( 'post_updated_messages', array( $this, 'post_updated_messages' ) );
      }

      // Basecamp Styles are broken on new versions.
      if( version_compare( get_bloginfo( 'version' ), '3.0.0', '<' ) ) {

        // Allow user-level selction of Basecamp style
        wp_admin_css_color( 'basecamp', _x( 'Basecamp', 'admin color scheme' ), self::$url . '/css/admin-basecamp-style.css', array( '#000000', '#3a3a3a', '#666666', '#e5e5e5' ) );

        // If basecamp style is globally enforced we load the CSS
        if( in_array( 'basecamp_style', $wp_simplify ) ) {
          wp_register_style( 'basecamp', self::$url . '/css/admin-basecamp-style.css' );
          add_action( 'admin_enqueue_scripts', create_function( '', " wp_enqueue_style('basecamp'); " ) );
        }

        // If Basecamp style is either globally enforced, or set by user, we load scripts and do other stuff
        if( $current_color == 'basecamp' || in_array( 'basecamp_style', $wp_simplify ) ) {
          add_filter( "admin_body_class", create_function( '', ' return "wp_simplify_basecamp_style admin-color-basecamp"; ' ) );
          wp_enqueue_script( 'basecamp-script', self::$url . '/js/basecamp-script.js', array( 'jquery' ) );
        }

      }

    }

    /**
     * admin_user_info_links
     *
     * @param $links
     * @param $current_user
     *
     * @return mixed
     */
    public function admin_user_info_links( $links, $current_user ) {

      $links[ 5 ] = str_replace( "Howdy, ", "", $links[ 5 ] );

      return $links;

    }

    /**
     * Displays checkbox in "Publish" metabox
     *
     * @since 1.0
     */
    public function show_post_locking_checkbox() {
      global $post_id;

      $wps_locked_post = get_post_meta( $post_id, 'wps_locked_post', true );

      ?>
      <div class="misc-pub-section">
        <input type="checkbox" value="true" name="wps_locked_post" value="true" id="wps_locked_post" <?php checked( $wps_locked_post, 'true' ); ?>/>
        <label for="wps_locked_post"><?php _e( 'Do not allow post to be deleted.' ); ?></label>
      </div>

    <?php

    }

    /**
     * Displays checkbox in "Publish" metabox
     *
     * @since 1.0
     */
    public function save_post( $post_ID, $post ) {

      if( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
        return $post_ID;
      }

      // Save locked_post status
      update_post_meta( $post_ID, 'wps_locked_post', $_REQUEST[ 'wps_locked_post' ] );

    }

    /**
     * Displays checkbox in "Publish" metabox
     *
     * @since 1.0
     */
    public function delete_post( $post_id ) {

      $wps_locked_post = get_post_meta( $post_id, 'wps_locked_post', true );

      if( $wps_locked_post == 'true' ) {

        wp_redirect( admin_url( "post.php?post={$post_id}&action=edit&message=1911" ) );
        die();
      }

    }

    /**
     * Displays checkbox in "Publish" metabox
     *
     * @since 1.0
     */
    public function post_updated_messages( $messages ) {

      $messages[ 'post' ][ '1911' ] = __( 'This post is locked so it cannot be deleted or trashed. Unlock first to delete.' );
      $messages[ 'page' ][ '1911' ] = __( 'This post is locked so it cannot be deleted or trashed. Unlock first to delete.' );

      return $messages;
    }

    /**
     * Removes all metaboxes from given page
     *
     * Should be called by function in add_meta_boxes_$post_type
     * Cycles through all metaboxes
     *
     * @since 1.1
     */
    public function remove_ui_elements( $post_type, $remove_elements ) {
      global $wp_meta_boxes, $_wp_post_type_features;

      // Remove Metaboxes
      if( is_array( $wp_meta_boxes[ $post_type ] ) )
        foreach( $wp_meta_boxes[ $post_type ] as $context_slug => $priority_array ) {

          foreach( $priority_array as $priority_slug => $meta_box_array ) {

            foreach( $meta_box_array as $meta_box_slug => $meta_bog_data ) {

              if( in_array( $meta_box_slug, $remove_elements ) )
                unset( $wp_meta_boxes[ $post_type ][ $context_slug ][ $priority_slug ][ $meta_box_slug ] );
            }
          }
        }

      if( is_array( $_wp_post_type_features[ $post_type ] ) ) {
        // Remove features
        foreach( $_wp_post_type_features[ $post_type ] as $feature => $enabled ) {

          if( in_array( $feature, $remove_elements ) )
            unset( $_wp_post_type_features[ $post_type ][ $feature ] );

        }
      }
    }

    /**
     * {description missing}
     *
     * @since 1.0
     */
    public function wp_dashboard_setup() {

      $wp_simplify = get_option( 'wp_simplify' );

      if( !is_array( $wp_simplify ) ) {
        return;
      }

      if( in_array( 'disable_dashboard_metaboxes', $wp_simplify ) ) {
        self::remove_ui_elements( 'dashboard', array(
          'dashboard_right_now',
          'dashboard_recent_comments',
          'dashboard_incoming_links',
          'dashboard_plugins',
          'dashboard_quick_press',
          'dashboard_recent_drafts',
          'dashboard_primary',
          'dashboard_secondary',
          'dashboard_recent_comments'
        ) );
      }

      if( in_array( 'disable_right_now_widget', $wp_simplify ) ) {
        self::remove_ui_elements( 'dashboard', array( 'dashboard_right_now' ) );
      }

      if( in_array( 'disable_comments', $wp_simplify ) ) {
        self::remove_ui_elements( 'dashboard', array( 'dashboard_recent_comments' ) );
        self::remove_ui_elements( 'page', array( 'commentsdiv' ) );
        self::remove_ui_elements( 'post', array( 'commentsdiv' ) );
      }

      if( in_array( 'disable_posts', $wp_simplify ) ) {
        self::remove_ui_elements( 'dashboard', array( 'dashboard_quick_press', 'dashboard_recent_drafts' ) );
      }

    }

    /**
     * {description missing}
     *
     * @since 1.0
     */
    public function widgets_init() {

      $wp_simplify = get_option( 'wp_simplify' );

      if( !is_array( $wp_simplify ) ) {
        return;
      }

      if( in_array( 'disable_comments', $wp_simplify ) ) {
        unregister_widget( 'WP_Widget_Recent_Comments' );
      }

      if( in_array( 'disable_posts', $wp_simplify ) ) {
        unregister_widget( 'WP_Widget_Archives' );
        unregister_widget( 'WP_Widget_Recent_Posts' );
        unregister_widget( 'WP_Widget_Categories' );
      }

      if( in_array( 'disable_links', $wp_simplify ) ) {
        unregister_widget( 'WP_Widget_Links' );
      }

    }

    /**
     * {description missing}
     *
     * @since 1.0
     */
    public function favorite_actions( $actions ) {

      $wp_simplify = get_option( 'wp_simplify' );

      if( !is_array( $wp_simplify ) ) {
        return $actions;
      }

      if( in_array( 'disable_posts', $wp_simplify ) ) {
        unset( $actions[ 'post-new.php' ] );
        unset( $actions[ 'edit.php?post_status=draft' ] );
      }

      if( in_array( 'disable_pages', $wp_simplify ) ) {
        unset( $actions[ 'edit.php?post_type=page' ] );
        unset( $actions[ 'post-new.php?post_type=page' ] );
      }

      if( in_array( 'disable_comments', $wp_simplify ) ) {
        unset( $actions[ 'edit-comments.php' ] );
      }

      if( in_array( 'disable_media', $wp_simplify ) ) {
        unset( $actions[ 'media-new.php' ] );
      }

      return $actions;
    }

    /**
     * Adds all the plugin hooks
     *
     * @todo Have a body class be added when menu is relocated into footer.
     * @todo Improve stlying on footer-relocated links.
     *
     * @since 0.5
     */
    public function admin_menu() {
      global $menu, $submenu;

      $wp_simplify = get_option( 'wp_simplify' );

      if( !is_array( $wp_simplify ) )
        return;

      if( in_array( 'disable_comments', $wp_simplify ) ) {
        if( function_exists( 'remove_post_type_support' ) ) {
          remove_post_type_support( 'post', 'comments' );
          remove_post_type_support( 'page', 'comments' );
        }
        unset( $menu[ 25 ] );

      }

      if( in_array( 'disable_posts', $wp_simplify ) )
        unset( $menu[ 5 ] );

      if( in_array( 'disable_pages', $wp_simplify ) )
        unset( $menu[ 20 ] );

      if( in_array( 'disable_media', $wp_simplify ) )
        unset( $menu[ 10 ] );

      if( in_array( 'disable_appearance', $wp_simplify ) )
        unset( $menu[ 60 ] );

      if( in_array( 'disable_users', $wp_simplify ) )
        unset( $menu[ 70 ] );

      if( in_array( 'disable_links', $wp_simplify ) )
        unset( $menu[ 15 ] );

      if( in_array( 'rename_posts', $wp_simplify ) && $menu[ 5 ][ 0 ] == 'Posts' ) {

        $menu[ 5 ][ 0 ] = 'News';
        $menu[ 5 ][ 3 ] = 'News';

        $submenu[ 'edit.php' ][ 5 ][ 0 ]  = 'All News';
        $submenu[ 'edit.php' ][ 16 ][ 0 ] = 'News Tags';
      }

      if( in_array( 'rename_cforms', $wp_simplify ) && $menu[ 100 ][ 0 ] == 'cformsII' ) {
        $menu[ 100 ][ 0 ] = 'Forms';
        $menu[ 100 ][ 3 ] = 'Forms';
      }

      if( in_array( 'rename_shopp', $wp_simplify ) && $menu[ 26 ][ 0 ] == 'Shopp' ) {
        $menu[ 26 ][ 0 ] = 'Online Store';
        $menu[ 26 ][ 3 ] = 'Online Store';
      }

      if( in_array( 'disable_relocate_admin_menu_links', $wp_simplify ) ) {
        // Hide plugins from menu
        unset( $menu[ 65 ] );

        // Hide settings from menu
        unset( $menu[ 80 ] );

        // Hide tools menu
        unset( $menu[ 75 ] );

        add_filter( 'admin_footer_text', array( $this, 'relocated_nav_components' ) );

        //** Modify default WP password reset message */
        add_filter( "admin_body_class", create_function( '', ' return "wp_simplify_footer_relocated_links"; ' ) );

      }

      // Save media into temporary variable
      $media_section = $menu[ 10 ];

      // Move pages in place of media
      $menu[ 10 ] = $menu[ 20 ];

      // Put media in place of pages
      $menu[ 20 ] = $media_section;

    }

    /**
     * relocated_nav_components
     *
     * @since 1.0
     */
    public function relocated_nav_components() {
      global $submenu;

      $this_menu = $submenu;
      echo "<span class='wp_simplify_relocated_footer'>";

      if( is_array( $this_menu[ 'plugins.php' ] ) ) {

        foreach( $this_menu[ 'plugins.php' ] as $data ) {

          if( strpos( $data[ 2 ], '/' ) || !strpos( $data[ 2 ], '.php' ) )
            $data[ 2 ] = 'plugins.php?page=' . $data[ 2 ];

          $plugins_menu[ ] = "<a style='font-style: normal;' href='" . admin_url( $data[ 2 ] ) . "'>{$data[0]}</a> ";

        }
        echo '<span style="font-style: normal; margin-right: 5px;">Plugins: </span>' . implode( " | ", $plugins_menu ) . "<br />";

      }

      if( is_array( $this_menu[ 'options-general.php' ] ) ) {

        foreach( $this_menu[ 'options-general.php' ] as $data ) {

          if( strpos( $data[ 2 ], '/' ) || !strpos( $data[ 2 ], '.php' ) || !empty( $data[ 3 ] ) )
            $data[ 2 ] = 'options-general.php?page=' . $data[ 2 ];

          $options_menu[ ] = "<a style='font-style: normal;' href='" . admin_url( $data[ 2 ] ) . "'>{$data[0]}</a> ";

        }
        echo '<span style="font-style: normal; margin-right: 5px;">Settings: </span>' . implode( " | ", $options_menu ) . "<br />";

      }

      if( is_array( $this_menu[ 'tools.php' ] ) ) {

        foreach( $this_menu[ 'tools.php' ] as $data ) {

          if( strpos( $data[ 2 ], '/' ) || !strpos( $data[ 2 ], '.php' ) )
            $data[ 2 ] = 'tools.php?page=' . $data[ 2 ];

          $tools_menu[ ] = "<a style='font-style: normal;' href='" . admin_url( $data[ 2 ] ) . "'>{$data[0]}</a> ";

        }
        echo '<span style="font-style: normal; margin-right: 5px;">Tools: </span>' . implode( " | ", $tools_menu ) . "<br />";

      }

      echo "</span>";

    }

    /**
     * settings_page
     *
     * @since 1.0
     */
    public function settings_page() {

      // Mark our checkbox as checked if the setting is already true
      $wp_simplify = get_option( 'wp_simplify' );

      if( !is_array( $wp_simplify ) )
        $wp_simplify = array();

      ?>

      <input type="hidden" name="wp_simplify_nonce" value="<?php echo wp_create_nonce( 'wp_simplify_update' ); ?>"/>
      <a name="wp_simplify_anchor"></a>
      <div class="wp_simplify_settings_tabs">
      <ul class="wps_section_tabs">
        <li><a href="#wps_major_compontents"><?php _e( 'Major Components' ); ?></a></li>
        <li><a href="#wps_simplify_ui"><?php _e( 'Simplify UI' ); ?></a></li>
        <li><a href="#wps_miscellaneous"><?php _e( 'Miscellaneous' ); ?></a></li>
        <li><a href="#wps_security"><?php _e( 'Security' ); ?></a></li>
      </ul>

    <div id="wps_major_compontents" class="wp-tab-panel">
      <ul>
        <li><input name='wp_simplify[]' id='disable_posts' type='checkbox' value='disable_posts' class='code'  <?php if( in_array( 'disable_posts', $wp_simplify ) ) echo " checked='checked'  "; ?>/><label for="disable_posts"> Disable Posts</label></li>
        <li><input name='wp_simplify[]' id='disable_pages' type='checkbox' value='disable_pages' class='code'  <?php if( in_array( 'disable_pages', $wp_simplify ) ) echo " checked='checked'  "; ?>/><label for="disable_pages"> Disable Pages</label></li>
        <li><input name='wp_simplify[]' id='disable_links' type='checkbox' value='disable_links' class='code'  <?php if( in_array( 'disable_links', $wp_simplify ) ) echo " checked='checked'  "; ?>/><label for="disable_links"> Disable Links</label></li>
        <li><input name='wp_simplify[]' id='disable_media' type='checkbox' value='disable_media' class='code'  <?php if( in_array( 'disable_media', $wp_simplify ) ) echo " checked='checked'  "; ?>/><label for="disable_media"> Disable Media. <span class="description">Images can still be uploaded, this only hides the link from nav menu.</span></label></li>
        <li><input name='wp_simplify[]' id='disable_comments' type='checkbox' value='disable_comments' class='code' <?php if( in_array( 'disable_comments', $wp_simplify ) ) echo " checked='checked'  "; ?>/><label for="disable_comments"> Disable Comments. <span class="description">Doesn't affect the front-end.</span></label></li>
        <li><input name='wp_simplify[]' id='disable_appearance' type='checkbox' value='disable_appearance' class='code'  <?php if( in_array( 'disable_appearance', $wp_simplify ) ) echo " checked='checked'  "; ?>/><label for="disable_appearance"> Disable Appearance</label></li>
        <li><input name='wp_simplify[]' id='disable_users' type='checkbox' value='disable_users' class='code'  <?php if( in_array( 'disable_users', $wp_simplify ) ) echo " checked='checked'  "; ?>/><label for="disable_users"> Disable Users</label></li>
      </ul>
    </div>


    <div id="wps_simplify_ui" class="wp-tab-panel">
      <ul>
        <li><input name='wp_simplify[]' id='disable_useless_metaboxes' type='checkbox' value='disable_useless_metaboxes' class='code'  <?php if( in_array( 'disable_useless_metaboxes', $wp_simplify ) ) echo " checked='checked'  "; ?>/><label for="disable_useless_metaboxes"> Clean up editor screen. <span class="description">Disable pingback, author, slug, and page attribute metaboxes.</span></label></li>
        <li><input name='wp_simplify[]' id='disable_relocate_admin_menu_links' type='checkbox' value='disable_relocate_admin_menu_links' class='code'  <?php if( in_array( 'disable_relocate_admin_menu_links', $wp_simplify ) ) echo " checked='checked'  "; ?>/><label for="disable_relocate_admin_menu_links"> Relocate Settings, Tools and Plugins into Footer Text.</label></li>
        <li><input name='wp_simplify[]' id='highlight_homepage' type='checkbox' value='highlight_homepage' class='code'  <?php if( in_array( 'highlight_homepage', $wp_simplify ) ) echo " checked='checked'  "; ?>/><label for="highlight_homepage"> If front page is selected to be a static page, highlight it in back-end.</label></li>
        <li><input name='wp_simplify[]' id='disable_wp_logo' type='checkbox' value='disable_wp_logo' class='code'  <?php if( in_array( 'disable_wp_logo', $wp_simplify ) ) echo " checked='checked'  "; ?>/><label for="disable_wp_logo"> Hide the WP logo in the top left corner.</label></li>
        <li><input name='wp_simplify[]' id='disable_right_now_widget' type='checkbox' value='disable_right_now_widget' class='code'  <?php if( in_array( 'disable_right_now_widget', $wp_simplify ) ) echo " checked='checked'  "; ?>/><label for="disable_right_now_widget"> Disable 'Right Now' Dashboard Metabox.  </label></li>
        <li><input name='wp_simplify[]' id='disable_dashboard_metaboxes' type='checkbox' value='disable_dashboard_metaboxes' class='code'  <?php if( in_array( 'disable_dashboard_metaboxes', $wp_simplify ) ) echo " checked='checked'  "; ?>/><label for="disable_dashboard_metaboxes"> Better yet, disable <b>all</b> default dashboard metaboxes.</label></li>
        <?php if( version_compare( get_bloginfo( 'version' ), '3.0.0', '<' ) ) { ?>
          <li><input name='wp_simplify[]' id='basecamp_style' type='checkbox' value='basecamp_style' class='code'  <?php if( in_array( 'basecamp_style', $wp_simplify ) ) echo " checked='checked'  "; ?>/><label for="basecamp_style"> Force Basecamp admin style for all users.</label></li>
        <?php } ?>
        <li><input name='wp_simplify[]' id='disable_pointers' type='checkbox' value='disable_pointers' class='code'  <?php if( in_array( 'disable_pointers', $wp_simplify ) ) echo " checked='checked'  "; ?>/><label for="disable_pointers"> Disable WP 3.3 tool tips for all users.</label></li>
      </ul>
    </div>


    <div id="wps_miscellaneous" class="wp-tab-panel">
      <ul>
      <li><input name='wp_simplify[]' id='disable_change_permalinks_button' type='checkbox' value='disable_change_permalinks_button' class='code'  <?php if( in_array( 'disable_change_permalinks_button', $wp_simplify ) ) echo " checked='checked'  "; ?>/><label for="disable_change_permalinks_button"> Hide the "Change Permalinks" and "Edit" buttons on post & page edit pages.</label>

        <?php if (function_exists( 'cforms' )): ?>
      <li><input name='wp_simplify[]' id='rename_cforms' type='checkbox' value='rename_cforms' class='code'  <?php if( in_array( 'rename_cforms', $wp_simplify ) ) echo " checked='checked'  "; ?>/><label for="rename_cforms"> Rename "cformsII" to "Forms"  in menu. <span class="description">Phenomenal plugin, not a customer-friendly name.</span></label>
        <?php endif; ?>

        <?php if (class_exists( 'Shopp' )): ?>
      <li><input name='wp_simplify[]' id='rename_shopp' type='checkbox' value='rename_shopp' class='code'  <?php if( in_array( 'rename_shopp', $wp_simplify ) ) echo " checked='checked'  "; ?>/><label for="rename_shopp"> Rename "Shopp" to "Online Store" in menu.</label>
        <?php endif; ?>
      <li><input name='wp_simplify[]' id='rename_posts' type='checkbox' value='rename_posts' class='code'  <?php if( in_array( 'rename_posts', $wp_simplify ) ) echo " checked='checked'  "; ?>/><label for="rename_posts"> Rename "Posts" to "News" in menu.</label>

      <li><input name='wp_simplify[]' id='disable_quick_edit_link' type='checkbox' value='disable_quick_edit_link' class='code'  <?php if( in_array( 'disable_quick_edit_link', $wp_simplify ) ) echo " checked='checked'  "; ?>/><label for="disable_quick_edit_link"> Hide the "Quick Edit" link on post & page overview pages.</label>
      <li><input name='wp_simplify[]' id='disable_theme_editor' type='checkbox' value='disable_theme_editor' class='code'  <?php if( in_array( 'disable_theme_editor', $wp_simplify ) ) echo " checked='checked'  "; ?>/><label for="disable_theme_editor"> Disable theme editing for all users via control panel (prevents potential security risk).</label>

      </ul>
    </div>

    <div id="wps_security" class="wp-tab-panel">
      <ul>
        <li><input name='wp_simplify[]' id='disable_backend_access_to_non_admins' type='checkbox' value='disable_backend_access_to_non_admins' class='code'  <?php if( in_array( 'disable_backend_access_to_non_admins', $wp_simplify ) ) echo " checked='checked'  "; ?>/><label for="disable_backend_access_to_non_admins"> Disable back-end access to non-administrators. <span class="description">If enabled, on attempt to access /wp-admin, non-level 10 administrators will be forwarded to the frontend. This can be overwritten if uses has "access_backend" capability.</span></label></li>
        <li><input name='wp_simplify[]' id='force_ssl_on_front_end' type='checkbox' value='force_ssl_on_front_end' class='code'  <?php if( in_array( 'force_ssl_on_front_end', $wp_simplify ) ) echo " checked='checked'  "; ?>/><label for="force_ssl_on_front_end"> Always use SSL on front-end. <span class="description"></span></label></li>
        <li><input name='wp_simplify[]' id='allow_post_locking' type='checkbox' value='allow_post_locking' class='code'  <?php if( in_array( 'allow_post_locking', $wp_simplify ) ) echo " checked='checked'  "; ?>/><label for="allow_post_locking"> <?php _e( 'Allow post locking.' ); ?>
            <span class="description"><?php _( 'Willl show checkbox on post and page editing screens which will let you "lock" posts - or prevent them from being deleted.' ); ?></span></label></li>
      </ul>
    </div>

      </div><?php /* End WP-Simplify Tabs */ ?>

    <?php

    } // settings_page()

    /**
     * Get the WP-Simplify Singleton
     *
     * Concept based on the CodeIgniter get_instance() concept.
     *
     * @example
     *
     *      var settings = Simplify::get_instance()->Settings;
     *      var api = Simplify::$instance()->API;
     *
     * @static
     * @return object
     *
     * @method get_instance
     * @for Simplify
     */
    public static function &get_instance() {
      return self::$instance;
    }

  }

}
