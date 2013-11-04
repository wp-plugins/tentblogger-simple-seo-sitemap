<?php 
/*
Plugin Name: TentBlogger Simple SEO Sitemap
Plugin URI: http://tentblogger.com/seo-sitemap
Description: <a href="http://tentblogger.com/seo-sitemap">SEO Sitemap</a> attempts to streamline the sitemap generation process as much as possible. Automatic creation, submission, and daily execution.
Version: 2.5
Author: TentBlogger
Author URI: http://tentblogger.com
Author Email: info@tentblogger.com
License:

    Copyright 2011 - 2012 TentBlogger (info@tentblogger.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

/*------------------------------------------------------------------*
 * Admin Functions
/*------------------------------------------------------------------*/

/**
 * Includes localization files and displays and styles the administration dashboard.
 */
function tentblogger_seo_sitemap_admin() {

	// Localization
	if(function_exists('load_plugin_textdomain')) {
		load_plugin_textdomain('tentblogger-seo-sitmeap', false, dirname(plugin_basename(__FILE__)) . '/lang');
	} // end if

	// Admin menu
	if(function_exists('add_menu_page')) {
		tentblogger_seo_sitemap_load_file('tentblogger-seo-sitemap-admin-styles', '/tentblogger-simple-seo-sitemap/css/admin.css');
		tentblogger_seo_sitemap_load_file('tentblogger-seo-sitemap-admin-scripts', '/tentblogger-simple-seo-sitemap/javascript/admin.js', true);
    if(!my_menu_exists('tentblogger-handle')) {
      add_menu_page('TentBlogger', 'TB SEO Sitemap', 'administrator', 'tentblogger-handle', array($this, 'display'));
    }
    add_submenu_page('tentblogger-handle', 'SEO Sitemap', 'SEO Sitemap', 'administrator', 'tentblogger-seo-sitemap-handle', 'tentblogger_seo_sitemap_display');
	} // end if
	
	// Prepate the plugins options
	if(function_exists('add_option')) {
		add_option('tentblogger-seo-sitemap');
	} // end if
	
} // end tentblogger_seo_sitemap_admin
add_action('admin_menu', 'tentblogger_seo_sitemap_admin');

/** 
 * Actually load the administration panel dashboard.
 */
function tentblogger_seo_sitemap_display() {
	if(is_admin()) {
		include_once('views/admin.php');
	} // end if
} // end tentblogger_seo_sitemap_display

/*------------------------------------------------------------------*
 * Cron Functions
/*------------------------------------------------------------------*/

/**
 * Schedule the event responsible for generating the sitemap on a daily basis.
 */
function tentblogger_seo_sitemap_activation() {
	wp_schedule_event(time(), 'daily', 'tentblogger_seo_sitemap_event');
} // end tentblogger_seo_sitemap_activation
register_activation_hook(__FILE__, 'tentblogger_seo_sitemap_activation');

/**
 * Called via the cron job for actually generating the sitemap.
 */
function tentblogger_generate_sitemap_cron() {
	tentblogger_generate_sitemap(true);
} // end tentblogger_generate_sitemap_cron
add_action('tentblogger_seo_sitemap_event', 'tentblogger_generate_sitemap_cron');

/**
 * Clear out the sitemap generation cron job from WordPress schedule
 * upon plugin deactivation.
 */
function tentblogger_seo_sitemap_deactivation() {
	wp_clear_scheduled_hook('tentblogger_seo_sitemap_event');
} // end tentblogger_seo_sitemap_deactivation
register_deactivation_hook(__FILE__, 'tentblogger_seo_sitemap_deactivation');

/*------------------------------------------------------------------*
 * Core Functions
/*------------------------------------------------------------------*/

/**
 * Actually generates the sitemap. The function is accessible via the administrator's
 * dashboard and by the WordPress scheduler.
 *
 * @is_scheduled	Whether or not this function is being called via the scheduler.
 */
function tentblogger_generate_sitemap($is_scheduled) {
	
	if($is_scheduled || $_GET['tbss'] && strtolower($_GET['tbss']) == 'trigger') {
			
		global $wpdb;
		
		$options = get_option('tentblogger-seo-sitemap');
			
		$all_posts = array();		// track all of the posts that we've written
		$limit = 25;						// process 25 rows at a time (for big blogs)
		$processed_posts = 0; 	// used to track how many rows we've processed

		$total_posts = $wpdb->get_var("
			select count(ID) 
			from $wpdb->posts 
			where post_status = 'publish' and post_password = '' and post_type in ('post', 'page')
		");
				
		// open the file to begin writing the sitemap
		$handle = fopen(tentblogger_get_sitemap_location(), 'w');
		if($handle == null) { // we don't have permissions to do this. throw up an error message.
			if(!$is_scheduled) {
				echo "fail";
			} // end if
			exit;
		} // end if
			
		tentblogger_write_sitemap_header($handle);

		// first, write the homepage...
		$homepage = "\t<url>\n";
			$homepage .= "\t\t<loc>" . get_bloginfo('url') . "/</loc>\n";
			$homepage .= "\t\t<changefreq>daily</changefreq>\n";
			$homepage .= "\t\t<priority>1</priority>\n";
		$homepage .= "\t</url>\n";
		fwrite($handle, $homepage);
			
		// and now we write the posts...
		while($total_posts > $processed_posts) {

			$posts = $wpdb->get_results("
				select id, post_title, post_name, post_modified, post_type, guid
				from $wpdb->posts where post_status = 'publish' and post_password = '' and post_type in ('post', 'page') 
				order by post_modified DESC 
				limit $limit offset $processed_posts
			");
				
			// write a sitemap entry for each URL in the array
			foreach($posts as $post) {
				
				// create the entry for this post
				$current_post = '';
				$current_post .= "\t<url>\n";
					$current_post .= "\t\t<loc>" . get_permalink($post->id) . "</loc>\n";
					$current_post .= "\t\t<lastmod>" . date(DATE_W3C, strtotime($post->post_modified)) . "</lastmod>\n";

					if($post->post_type == 'post') {
						$current_post .= "\t\t<changefreq>weekly</changefreq>\n";
						$current_post .= "\t\t<priority>" . 0.8 . "</priority>\n";
					} else {
						$current_post .= "\t\t<changefreq>monthly</changefreq>\n";
						$current_post .= "\t\t<priority>" . 0.6 . "</priority>\n";
					} // end if/else
				
				$current_post .= "\t</url>\n";
				
				// only write this entry if we've not already done so
				if(!in_array($post->guid, $all_posts)) {
					fwrite($handle, $current_post);
					$all_posts[] = $post->guid;
				} // end if
				
			} // end foreach
			
			$processed_posts += $limit;
			
		} // end while
		
		// write out the footer
		$footer = '</urlset>';
		fwrite($handle, $footer);
		
		$date = date('F j, Y');
		$options['tentblogger-sitemap-date'] = $date;
		
		// gzip the sitemap
		tentblogger_gzip_sitemap();
		$options['tentblogger-sitemap-archive'] = $date;
		
		// submit to google and bing (stamp the time for reference)
		$time = date('g:i a');
		$sitemap_url = urlencode(get_bloginfo('url') . '/sitemap.xml.gz');
		
		$response = wp_remote_get('http://www.google.com/webmasters/tools/ping?sitemap=' . $sitemap_url);
		if(!is_wp_error($response) && $response['response']['code'] == '200') {
			fwrite($handle, "<!-- Successfully submitted sitemap to Google on " . $date . " (" . $time . ") -->\n");
		} // end if
		
		$response = wp_remote_get('http://www.bing.com/webmaster/ping.aspx?sitemap=' . $sitemap_url);
		if(!is_wp_error($response) && $response['response']['code'] == '200') {
			fwrite($handle, "<!-- Successfully submitted sitemap to Bing on " . $date . " (" . $time . ") -->\n");
		} // end if
		
		$options['tentblogger-sitemap-submit'] = $date;
		
		// save the options, close the file, clear the database result cache
		update_option('tentblogger-seo-sitemap', $options);
		fclose($handle);
		$wpdb->flush();
		if(!$is_scheduled) {
			echo $date; 		// for displaying the update via ajax
			exit;
		} // end if

	} // end if

} // end tentblogger_generate_sitemap
add_action('init', 'tentblogger_generate_sitemap');

/**
 * Creates a gzip archive of the sitemap and writes it to disk.
 */
function tentblogger_gzip_sitemap() {
		
	$handle = fopen(tentblogger_get_sitemap_location() . '.gz', 'w');
	$sitemap = implode("", file(tentblogger_get_sitemap_location()));
	if($handle != null) {
		fwrite($handle, gzencode($sitemap, 9));
		fclose($handle);
	} // end if

} // end tentblogger_gzip_sitemap

/**
 * Writes the header of the XML file to the sitemap.
 *
 * @handle	The resource used to write data to disk.
 */
function tentblogger_write_sitemap_header($handle) {
	
	$sitemap = '<?xml version="1.0" encoding="' . get_bloginfo('charset') . '"?>';
		$sitemap .= "\n";
		$sitemap .= '<urlset xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" ';
		$sitemap .= 'xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd" ';
		$sitemap .= 'xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
		$sitemap .= "\n";
	$sitemap .= '';
			
	fwrite($handle, $sitemap);
	
} // end tentblogger_write_sitemap_header

/*------------------------------------------------------------------*
 * Helper Functions
/*------------------------------------------------------------------*/

/**
	* Helper function for registering and loading scripts and styles.
	*
	* @name	The 	ID to register with WordPress
	* @file_path		The path to the actual file
	* @is_script		Optional argument for if the incoming file_path is a JavaScript source file.
	*/
function tentblogger_seo_sitemap_load_file($name, $file_path, $is_script = false) {
	$url = WP_PLUGIN_URL . $file_path;
	$file = WP_PLUGIN_DIR . $file_path;
	if(file_exists($file)) {
		if($is_script) {
			wp_register_script($name, $url);
			wp_enqueue_script($name);
		} else {
			wp_register_style($name, $url);
			wp_enqueue_style($name);
		} // end if
	} // end if
} // end tentblogger_seo_sitemap_load_file

/**
 * Returns whether or not this plugin is running on a blog that's private.
 */
function tentblogger_is_private_blog() {
	return get_option('blog_public') != 1;
} // end is_private_blog

/**
 * Returns the root directory of a WordPress installation. This is used
 * to write the sitemap into the site's root.
 *
 * http://stackoverflow.com/questions/2354633/wordpress-root-directory
 */
function tentblogger_get_sitemap_location() {

   $base = dirname(__FILE__);
   $path = false;
   if (@file_exists(dirname(dirname($base))."/wp-config.php"))
   {
       $path = dirname(dirname($base))."/sitemap.xml";
   }
   else
   if (@file_exists(dirname(dirname(dirname($base)))."/wp-config.php"))
   {
       $path = dirname(dirname(dirname($base)))."/sitemap.xml";
   }
   else
   $path = false;
   if ($path != false)
   {
       $path = str_replace("\\", "/", $path);
   }
   return $path;
	 
} // end tentblogger_get_sitemap_location

	
  /**
   * http://wordpress.stackexchange.com/questions/6311/how-to-check-if-an-admin-submenu-already-exists
   */
  function my_menu_exists( $handle, $sub = false){
    if( !is_admin() || (defined('DOING_AJAX') && DOING_AJAX) )
      return false;
    global $menu, $submenu;
    $check_menu = $sub ? $submenu : $menu;
    if( empty( $check_menu ) )
      return false;
    foreach( $check_menu as $k => $item ){
      if( $sub ){
        foreach( $item as $sm ){
          if($handle == $sm[2])
            return true;
        }
      } else {
        if( $handle == $item[2] )
          return true;
      }
    }
    return false;
  } // end my_menu_exists

?>