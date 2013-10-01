=== WP-Simplify - Clean up the Control Panel ===
Contributors: andypotanin
Donate link: http://twincitiestech.com/plugins/wp-simplify/
Tags: disable comments, disable tools, cleanup admin, simplify, cms, enhance back-end, back-end, post lock, prevent post deletion
Requires at least: 2.71
Tested up to: 3.2
Stable tag: trunk

 
== Description ==

The purpose of this plugin is to simplify the back-end for non-technical users.  If you are a web developer using WordPress as a CMS for a client, 99% of the time your client will not need to see the Tools or Plugins menus.  Furthermore, they may not use the comments feature at all.  This plugin will let you hide and rearrange back-end features.

Another useful feature is disabling all the default dashboard metaboxes, leaving only the ones added by plugins.
 
= New Features =
* Clean up editor screen: disable pingback, author, slug, and page attribute metaboxes.
* Hide the WP logo in the top left corner.
* Disable Pages
* Disable Appearance
* Hide the "Quick Edit" link on post & page overview pages.

= Features =
* Relocate "Plugins" and "Settings" links into footer
* Disable Comments  
* Disable Tools Menu  
* Disable Posts  
* Disable Links  
* Disable Default Dashboard Widgets  
* If front page is set to be a static page, it can be highlighted  

Be sure to visit the Settings page to select which features to disable.

== Installation ==

1. Upload all the files into your wp-content/plugins directory, be sure to put them into a folder called "wp-simplify"
2. Activate the plugin at the plugin administration page
3. Configure plugin settings on Settings -> General page

Please see the [wp-invoice plugin home page](http://twincitiestech.com/plugins/wp-simplify/) for details. 

== Frequently Asked Questions ==

Please see the [wp-invoice plugin home page](http://twincitiestech.com/plugins/wp-simplify/) for details. 

== Screenshots ==

1. Example of cleaned up back-end 
3. Default Dashboard Widgets can be disabled, while the plugin-generated ones are left
2. Configuration Settings

== Change Log ==

= Version 1.3.0 =
* WordPress 3.3 compatibility.

= Version 1.2 =
* Footer relocated menu fixes.

= Version 1.1 =
* Fixed issue with AJAX calls not working when 'Disable back-end access to non-administrators. " was enabled

= Version 0.71 =
* Re-releasing 0.70 with minor fix. (http://wordpress.org/support/topic/getting-rid-of-php-notice)

= Version 0.70 =
* Upgraded for WP 3.2
* Added "Lock Post" feature which prevents post deletion.

= Version 0.59 =
* Bug fix with admin theme CSS

= Version 0.58 =
* Security: prevent non-admins from accessing back-end.
* Plugins, settings & tools footer relocation improved.
* New admin theme added.
* Ability to rename "Posts" to "News"

= Version 0.57 =
* Added a function_exists check to remove_post_type_support()

= Version 0.56 =
* Added many new features - as suggested by Barra of  [ScratchWebDesign.com](http://ScratchWebDesign.com) 

= Version 0.54 =
* Bug fix: edit_themes and edit_plugins roles re-established upon deactivation, or change of setting.

= Version 0.53 =
* Added function to disable editing theme and plugin code form back-end.

= Version 0.52 =
* Added feature to relocate tools menu to footer, as opposed to hiding completely

== Change Log ==
= Version 0.51 =
* Fixed bug with plugin-added custom settings pages links being broken

= Version 0.50 =
* Initial Launch

== Update Log ==
N/A