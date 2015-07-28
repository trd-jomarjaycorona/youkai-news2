<?php
// Translation
load_theme_textdomain('Avada', TEMPLATEPATH.'/languages');

// Default RSS feed links
add_theme_support('automatic-feed-links');

// Allow shortcodes in widget text
add_filter('widget_text', 'do_shortcode');

// Woocommerce Support
add_theme_support('woocommerce');
define('WOOCOMMERCE_USE_CSS', false);

// Register Navigation
register_nav_menu('main_navigation', 'Main Navigation');
register_nav_menu('top_navigation', 'Top Navigation');
register_nav_menu('404_pages', '404 Useful Pages');

// Content Width
if (!isset( $content_width )) $content_width = 1000;

/* Options Framework */
require_once(get_template_directory().'/admin/index.php');

// Post Formats
if($data['blog_layout'] == 'Large Alternate' || $data['blog_layout'] == 'Medium Alternate') {
	add_theme_support('post-formats', array('gallery', 'link', 'image', 'quote', 'video', 'audio', 'chat'));
}

// Auto plugin activation
// Reset activated plugins because if pre-installed plugins are already activated in standalone mode, theme will bug out.
if(get_option('avada_int_plugins', '0') == '0') {
	global $wpdb;
	$wpdb->query("UPDATE ". $wpdb->options ." SET option_value = 'a:0:{}' WHERE option_name = 'active_plugins';");
	if($wpdb->sitemeta) {
		$wpdb->query("UPDATE ". $wpdb->sitemeta ." SET meta_value = 'a:0:{}' WHERE meta_key = 'active_plugins';");
	}
	update_option('avada_int_plugins', '1');
}

if(get_option('avada_int_plugins', '0') == '1') {
	/**************************/
	/* Include LayerSlider WP */
	/**************************/

	$layerslider = get_template_directory() . '/framework/plugins/LayerSlider/layerslider.php';

	if(!$data['status_layerslider']) {
		include $layerslider;
		
		$layerslider_last_version = get_option('avada_layerslider_last_version', '1.0');

		// Activate the plugin if necessary
		if(get_option('avada_layerslider_activated', '0') == '0') {
			// Run activation script
			layerslider_activation_scripts();

			// Save a flag that it is activated, so this won't run again
			update_option('avada_layerslider_activated', '1');

			// Save the current version number of LayerSlider
			update_option('avada_layerslider_last_version', $GLOBALS['lsPluginVersion']);

		// Do version check
		} else if(version_compare($GLOBALS['lsPluginVersion'], $layerslider_last_version, '>')) {
			// Run again activation scripts for possible adjustments
			layerslider_activation_scripts();

			// Update the version number
			update_option('avada_layerslider_last_version', $GLOBALS['lsPluginVersion']);
		}
	}

	/**************************/
	/* Include Flexslider WP */
	/**************************/

	$flexslider = get_template_directory() . '/framework/plugins/tf-flexslider/wooslider.php';
	if(!$data['status_flexslider']) {
		include $flexslider;
	}

	/**************************/
	/* Include Posts Type Order */
	/**************************/

	$pto = get_template_directory() . '/framework/plugins/post-types-order/post-types-order.php';
	if($data['post_type_order']) {
		include $pto;
	}

	/************************************************/
	/* Include Previous / Next Post Pagination Plus */
	/************************************************/
	$pnp = 	get_template_directory() . '/framework/plugins/ambrosite-post-link-plus.php';
	include $pnp;

	/***********************/
	/* Include WPML Fixes  */
	/***********************/
	if(defined('ICL_SITEPRESS_VERSION')) {
		$wpml_include = get_template_directory() . '/framework/plugins/wpml.php';
		include $wpml_include;
	}
}

// TGM Plugin Activation
require_once dirname( __FILE__ ) . '/framework/class-tgm-plugin-activation.php';
add_action( 'tgmpa_register', 'avada_register_required_plugins' );

function avada_register_required_plugins() {
	/**
	 * Array of plugin arrays. Required keys are name and slug.
	 * If the source is NOT from the .org repo, then source is also required.
	 */
	$plugins = array(

		// This is an example of how to include a plugin pre-packaged with a theme
		array(
			'name'     				=> 'Revolution Slider', // The plugin name
			'slug'     				=> 'revslider', // The plugin slug (typically the folder name)
			'source'   				=> get_template_directory() . '/framework/plugins/revslider.zip', // The plugin source
			'required' 				=> false, // If false, the plugin is only 'recommended' instead of required
			'version' 				=> '3.0.95', // E.g. 1.0.0. If set, the active plugin must be this version or higher, otherwise a notice is presented
			'force_activation' 		=> false, // If true, plugin is activated upon theme activation and cannot be deactivated until theme switch
			'force_deactivation' 	=> false, // If true, plugin is deactivated upon theme switch, useful for theme-specific plugins
			'external_url' 			=> '', // If set, overrides default API URL and points to an external URL
		),

	);

	// Change this to your theme text domain, used for internationalising strings
	$theme_text_domain = 'tgmpa';

	/**
	 * Array of configuration settings. Amend each line as needed.
	 * If you want the default strings to be available under your own theme domain,
	 * leave the strings uncommented.
	 * Some of the strings are added into a sprintf, so see the comments at the
	 * end of each line for what each argument will be.
	 */
	$config = array(
		'domain'       		=> $theme_text_domain,         	// Text domain - likely want to be the same as your theme.
		'default_path' 		=> '',                         	// Default absolute path to pre-packaged plugins
		'parent_menu_slug' 	=> 'themes.php', 				// Default parent menu slug
		'parent_url_slug' 	=> 'themes.php', 				// Default parent URL slug
		'menu'         		=> 'install-required-plugins', 	// Menu slug
		'has_notices'      	=> true,                       	// Show admin notices or not
		'is_automatic'    	=> false,					   	// Automatically activate plugins after installation or not
		'message' 			=> '',							// Message to output right before the plugins table
		'strings'      		=> array(
			'page_title'                       			=> __( 'Install Required Plugins', $theme_text_domain ),
			'menu_title'                       			=> __( 'Install Plugins', $theme_text_domain ),
			'installing'                       			=> __( 'Installing Plugin: %s', $theme_text_domain ), // %1$s = plugin name
			'oops'                             			=> __( 'Something went wrong with the plugin API.', $theme_text_domain ),
			'notice_can_install_required'     			=> _n_noop( 'This theme requires the following plugin: %1$s.', 'This theme requires the following plugins: %1$s.' ), // %1$s = plugin name(s)
			'notice_can_install_recommended'			=> _n_noop( 'This theme recommends the following plugin: %1$s.', 'This theme recommends the following plugins: %1$s.' ), // %1$s = plugin name(s)
			'notice_cannot_install'  					=> _n_noop( 'Sorry, but you do not have the correct permissions to install the %s plugin. Contact the administrator of this site for help on getting the plugin installed.', 'Sorry, but you do not have the correct permissions to install the %s plugins. Contact the administrator of this site for help on getting the plugins installed.' ), // %1$s = plugin name(s)
			'notice_can_activate_required'    			=> _n_noop( 'The following required plugin is currently inactive: %1$s.', 'The following required plugins are currently inactive: %1$s.' ), // %1$s = plugin name(s)
			'notice_can_activate_recommended'			=> _n_noop( 'The following recommended plugin is currently inactive: %1$s.', 'The following recommended plugins are currently inactive: %1$s.' ), // %1$s = plugin name(s)
			'notice_cannot_activate' 					=> _n_noop( 'Sorry, but you do not have the correct permissions to activate the %s plugin. Contact the administrator of this site for help on getting the plugin activated.', 'Sorry, but you do not have the correct permissions to activate the %s plugins. Contact the administrator of this site for help on getting the plugins activated.' ), // %1$s = plugin name(s)
			'notice_ask_to_update' 						=> _n_noop( 'The following plugin needs to be updated to its latest version to ensure maximum compatibility with this theme: %1$s.', 'The following plugins need to be updated to their latest version to ensure maximum compatibility with this theme: %1$s.' ), // %1$s = plugin name(s)
			'notice_cannot_update' 						=> _n_noop( 'Sorry, but you do not have the correct permissions to update the %s plugin. Contact the administrator of this site for help on getting the plugin updated.', 'Sorry, but you do not have the correct permissions to update the %s plugins. Contact the administrator of this site for help on getting the plugins updated.' ), // %1$s = plugin name(s)
			'install_link' 					  			=> _n_noop( 'Begin installing plugin', 'Begin installing plugins' ),
			'activate_link' 				  			=> _n_noop( 'Activate installed plugin', 'Activate installed plugins' ),
			'return'                           			=> __( 'Return to Required Plugins Installer', $theme_text_domain ),
			'plugin_activated'                 			=> __( 'Plugin activated successfully.', $theme_text_domain ),
			'complete' 									=> __( 'All plugins installed and activated successfully. %s', $theme_text_domain ), // %1$s = dashboard link
			'nag_type'									=> 'updated' // Determines admin notice type - can only be 'updated' or 'error'
		)
	);

	tgmpa( $plugins, $config );
}

// Metaboxes
include_once(get_template_directory().'/framework/metaboxes.php');

// Extend Visual Composer
get_template_part('shortcodes');

// Custom Functions
get_template_part('framework/custom_functions');

// Plugins
include_once(get_template_directory().'/framework/plugins/multiple_sidebars.php');

// Widgets
get_template_part('widgets/widgets');

// Add post thumbnail functionality
//add_theme_support('post-thumbnails');
//add_image_size('blog-large', 669, 272, true);
//add_image_size('blog-medium', 320, 202, true);
//add_image_size('tabs-img', 52, 50, true);
//add_image_size('related-img', 180, 138, true);
//add_image_size('portfolio-one', 540, 272, true);
//add_image_size('portfolio-two', 460, 295, true);
//add_image_size('portfolio-three', 300, 214, true);
//add_image_size('portfolio-four', 220, 161, true);
//add_image_size('portfolio-full', 940, 400, true);
//add_image_size('recent-posts', 700, 441, true);
//add_image_size('recent-works-thumbnail', 66, 66, true);

// Register widgetized locations
if(function_exists('register_sidebar')) {
	register_sidebar(array(
		'name' => 'Blog Sidebar',
		'id' => 'avada-blog-sidebar',
		'before_widget' => '<div id="%1$s" class="widget %2$s">',
		'after_widget' => '</div>',
		'before_title' => '<div class="heading"><h3>',
		'after_title' => '</h3></div>',
	));
	/*
	register_sidebar(array(
		'name' => 'Footer Widget 1',
		'id' => 'avada-footer-widget-1',
		'before_widget' => '<div id="%1$s" class="footer-widget-col %2$s">',
		'after_widget' => '<div style="clear:both;"></div></div>',
		'before_title' => '<h3>',
		'after_title' => '</h3>',
	))
	*/;
	/*
	register_sidebar(array(
		'name' => 'Footer Widget 2',
		'id' => 'avada-footer-widget-2',
		'before_widget' => '<div id="%1$s" class="footer-widget-col %2$s">',
		'after_widget' => '<div style="clear:both;"></div></div>',
		'before_title' => '<h3>',
		'after_title' => '</h3>',
	));
	*/
	register_sidebar(array(
		'name' => 'Footer Widget 3',
		'id' => 'avada-footer-widget-3',
		'before_widget' => '<div id="%1$s" class="footer-widget-col %2$s">',
		'after_widget' => '<div style="clear:both;"></div></div>',
		'before_title' => '<h3>',
		'after_title' => '</h3>',
	))
	;
	/*
	register_sidebar(array(
		'name' => 'Footer Widget 4',
		'id' => 'avada-footer-widget-4',
		'before_widget' => '<div id="%1$s" class="footer-widget-col %2$s">',
		'after_widget' => '<div style="clear:both;"></div></div>',
		'before_title' => '<h3>',
		'after_title' => '</h3>',
	))
	*/;
}

// Register custom post types
add_action('init', 'pyre_init');
function pyre_init() {
	global $data;
	register_post_type(
		'avada_portfolio',
		array(
			'labels' => array(
				'name' => 'Portfolio',
				'singular_name' => 'Portfolio'
			),
			'public' => true,
			'has_archive' => true,
			'rewrite' => array('slug' => $data['portfolio_slug']),
			'supports' => array('title', 'editor', 'thumbnail','comments'),
			'can_export' => true,
		)
	);

	register_taxonomy('portfolio_category', 'avada_portfolio', array('hierarchical' => true, 'label' => 'Categories', 'query_var' => true, 'rewrite' => true));
	register_taxonomy('portfolio_skills', 'avada_portfolio', array('hierarchical' => true, 'label' => 'Skills', 'query_var' => true, 'rewrite' => true));

	register_post_type(
		'avada_faq',
		array(
			'labels' => array(
				'name' => 'FAQs',
				'singular_name' => 'FAQ'
			),
			'public' => true,
			'has_archive' => true,
			'rewrite' => array('slug' => 'faq-items'),
			'supports' => array('title', 'editor', 'thumbnail','comments'),
			'can_export' => true,
		)
	);

	register_taxonomy('faq_category', 'avada_faq', array('hierarchical' => true, 'label' => 'Categories', 'query_var' => true, 'rewrite' => true));

	register_post_type(
		'themefusion_elastic',
		array(
			'labels' => array(
				'name' => 'Elastic Slider',
				'singular_name' => 'Elastic Slide'
			),
			'public' => true,
			'has_archive' => true,
			'rewrite' => array('slug' => 'elastic-slide'),
			'supports' => array('title', 'thumbnail'),
			'can_export' => true,
			'menu_position' => 100,
		)
	);

	register_taxonomy('themefusion_es_groups', 'themefusion_elastic', array('hierarchical' => false, 'label' => 'Groups', 'query_var' => true, 'rewrite' => true));
}

// How comments are displayed
function avada_comment($comment, $args, $depth) {
	$GLOBALS['comment'] = $comment; ?>
	<?php $add_below = ''; ?>
	<li <?php comment_class(); ?> id="comment-<?php comment_ID() ?>">
	
		<div class="the-comment">
			<div class="avatar">
				<?php echo get_avatar($comment, 54); ?>
			</div>
			
			<div class="comment-box">
			
				<div class="comment-author meta">
					<strong><?php echo get_comment_author_link() ?></strong>
					<?php printf(__('%1$s at %2$s', 'Avada'), get_comment_date(),  get_comment_time()) ?></a><?php edit_comment_link(__(' - Edit', 'Avada'),'  ','') ?><?php comment_reply_link(array_merge( $args, array('reply_text' => __(' - Reply', 'Avada'), 'add_below' => $add_below, 'depth' => $depth, 'max_depth' => $args['max_depth']))) ?>
				</div>
			
				<div class="comment-text">
					<?php if ($comment->comment_approved == '0') : ?>
					<em><?php echo __('Your comment is awaiting moderation.', 'Avada') ?></em>
					<br />
					<?php endif; ?>
					<?php comment_text() ?>
				</div>
			
			</div>
			
		</div>

<?php }

/*function pyre_SearchFilter($query) {
	if ($query->is_search) {
		$query->set('post_type', 'post');
	}
	return $query;
}
add_filter('pre_get_posts','pyre_SearchFilter');*/

add_filter('wp_get_attachment_link', 'avada_pretty');
function avada_pretty($content) {
	$content = preg_replace("/<a/","<a rel=\"prettyPhoto[postimages]\"",$content,1);
	return $content;
}

require_once(get_template_directory().'/framework/plugins/multiple-featured-images/multiple-featured-images.php');

if( class_exists( 'kdMultipleFeaturedImages' )  && !$data['legacy_posts_slideshow']) {
		$i = 2;

		while($i <= $data['posts_slideshow_number']) {
	        $args = array(
	                'id' => 'featured-image-'.$i,
	                'post_type' => 'post',      // Set this to post or page
	                'labels' => array(
	                    'name'      => 'Featured image '.$i,
	                    'set'       => 'Set featured image '.$i,
	                    'remove'    => 'Remove featured image '.$i,
	                    'use'       => 'Use as featured image '.$i,
	                )
	        );

	        new kdMultipleFeaturedImages( $args );

	        $args = array(
	                'id' => 'featured-image-'.$i,
	                'post_type' => 'page',      // Set this to post or page
	                'labels' => array(
	                    'name'      => 'Featured image '.$i,
	                    'set'       => 'Set featured image '.$i,
	                    'remove'    => 'Remove featured image '.$i,
	                    'use'       => 'Use as featured image '.$i,
	                )
	        );

	        new kdMultipleFeaturedImages( $args );

	        $args = array(
	                'id' => 'featured-image-'.$i,
	                'post_type' => 'avada_portfolio',      // Set this to post or page
	                'labels' => array(
	                    'name'      => 'Featured image '.$i,
	                    'set'       => 'Set featured image '.$i,
	                    'remove'    => 'Remove featured image '.$i,
	                    'use'       => 'Use as featured image '.$i,
	                )
	        );

	        new kdMultipleFeaturedImages( $args );

	        $i++;
    	}

}

function avada_excerpt_length( $length ) {
	global $data;
	
	if(isset($data['excerpt_length_blog'])) {
		return $data['excerpt_length_blog'];
	}
}
add_filter('excerpt_length', 'avada_excerpt_length', 999);

function avada_admin_bar_render() {
	global $wp_admin_bar;
	$wp_admin_bar->add_menu( array(
		'parent' => 'site-name', // use 'false' for a root menu, or pass the ID of the parent menu
		'id' => 'smof_options', // link ID, defaults to a sanitized title value
		'title' => __('Theme Options', 'Avada'), // link title
		'href' => admin_url( 'themes.php?page=optionsframework'), // name of file
		'meta' => false // array of any of the following options: array( 'html' => '', 'class' => '', 'onclick' => '', target => '', title => '' );
	));
}
add_action( 'wp_before_admin_bar_render', 'avada_admin_bar_render' );

add_filter('upload_mimes', 'avada_filter_mime_types');
function avada_filter_mime_types($mimes)
{
	$mimes['ttf'] = 'font/ttf';
	$mimes['woff'] = 'font/woff';
	$mimes['svg'] = 'font/svg';
	$mimes['eot'] = 'font/eot';

	return $mimes;
}

function avada_process_tag( $m ) {
   if ($m[2] == 'dropcap' || $m[2] == 'highlight' || $m[2] == 'tooltip') {
      return $m[0];
   }

	// allow [[foo]] syntax for escaping a tag
	if ( $m[1] == '[' && $m[6] == ']' ) {
		return substr($m[0], 1, -1);
	}

   return $m[1] . $m[6];
}

function tf_content($limit, $strip_html) {
	global $data, $more;

	if(!$limit) {
		$limit = 285;
	}

	$test_strip_html = $strip_html;

	if($strip_html == "true" || $strip_html == true) {
		$test_strip_html = true;
	}

	if($strip_html == "false" || $strip_html == false) {
		$test_strip_html = false;
	}

	$custom_excerpt = false;

	$post = get_post(get_the_ID());

	$pos = strpos($post->post_content, '<!--more-->');

	if($test_strip_html) {
		$raw_content = strip_tags( get_the_content() );
		if($post->post_excerpt || $pos !== false) {
			$raw_content = strip_tags( get_the_excerpt() );
			$custom_excerpt = true;
			$more = 0;
		}
	} else {
		$raw_content = get_the_content();
		if($post->post_excerpt || $pos !== false) {
			$raw_content = get_the_excerpt();
			$custom_excerpt = true;
			$more = 0;
		}
	}

	if($raw_content && $custom_excerpt == false) {
		$content = $raw_content;

		$pattern = get_shortcode_regex();
		$content = preg_replace_callback("/$pattern/s", 'avada_process_tag', $content);

		$content = explode(' ', $content, $limit);
		if(count($content)>=$limit) {
			array_pop($content);
			if($data['disable_excerpts']) {
				$content = implode(" ",$content);
			} else {
				$content = implode(" ",$content).' &#91;...&#93;';
			}
		} else {
			$content = implode(" ",$content);
		}

		$content = apply_filters('the_content', $content);
		$content = str_replace(']]>', ']]&gt;', $content);

		$content = '<div class="excerpt-container">'.do_shortcode($content).'</div>';

		return $content;
	}

	if($custom_excerpt == true) {
		if($test_strip_html == true) {
			$content = get_the_content('');
			$content = strip_tags( $content );
			$pattern = get_shortcode_regex();
			$content = preg_replace_callback("/$pattern/s", 'avada_process_tag', $content);
			$content = apply_filters('the_content', $content);
			$content = str_replace(']]>', ']]&gt;', $content);
			$content = '<div class="excerpt-container">'.do_shortcode($content).'</div>';
		} else {
			$content = get_the_content('');
			$content = apply_filters('the_content', $content);
			$content = str_replace(']]>', ']]&gt;', $content);
		}
		return $content;
	}

	if(has_excerpt()) {
		$content = get_the_excerpt();
		return $content;
	}
}

function avada_scripts() {
	if (!is_admin() && !in_array( $GLOBALS['pagenow'], array( 'wp-login.php', 'wp-register.php' ) )) {
	wp_reset_query();

	global $data,$post;

	$slider_page_id = $post->ID;
	if(is_home() && !is_front_page()){
		$slider_page_id = get_option('page_for_posts');
	}

    wp_enqueue_script( 'jquery', false, array(), null, true);

    wp_deregister_script('ccgallery_modernizr');

//     wp_deregister_script( 'modernizr' );
//     wp_register_script( 'modernizr', get_bloginfo('template_directory').'/js/modernizr.js', array(), null, true);
// 	wp_enqueue_script( 'modernizr' );

  //  wp_deregister_script( 'jquery.carouFredSel' );
   // wp_register_script( 'jquery.carouFredSel', get_bloginfo('template_directory').'/js/jquery.carouFredSel-6.2.1-packed.js', array(), null, true);
    //if(is_single()) {
	//	wp_enqueue_script( 'jquery.carouFredSel' );
    //}
    
   // if(!$data['status_lightbox']) {
	    //wp_deregister_script( 'jquery.prettyPhoto' );
	   // wp_register_script( 'jquery.prettyPhoto', get_bloginfo('template_directory').'/js/jquery.prettyPhoto.js', array(), null, true);
		//wp_enqueue_script( 'jquery.prettyPhoto' );
	//}

//     wp_deregister_script( 'imagesLoaded' );
//     wp_register_script( 'imagesLoaded', get_bloginfo('template_directory').'/js/imagesLoaded.js', array(), null, true);
//     wp_enqueue_script( 'imagesLoaded' );
    
//     wp_deregister_script( 'jquery.isotope' );
//     wp_register_script( 'jquery.isotope', get_bloginfo('template_directory').'/js/jquery.isotope.min.js', array(), null, true);
	/*if(
		is_page_template('portfolio-one-column.php') || is_page_template('portfolio-one-column-text.php') ||
		is_page_template('portfolio-two-column.php') || is_page_template('portfolio-two-column-text.php') ||
		is_page_template('portfolio-three-column.php') || is_page_template('portfolio-three-column-text.php') ||
		is_page_template('portfolio-four-column.php') || is_page_template('portfolio-four-column-text.php') ||
		(is_home() && $data['blog_layout'] == 'Grid') || is_page_template('demo-gridblog.php') ||
		is_page_template('demo-timelineblog.php')
	) {*/
		//wp_enqueue_script( 'jquery.isotope' );
	//}

   // wp_deregister_script( 'jquery.flexslider' );
   // wp_register_script( 'jquery.flexslider', get_bloginfo('template_directory').'/js/jquery.flexslider-min.js', array(), null, true);
    //if(is_home() || is_single() || is_search() || is_archive() || get_post_meta($slider_page_id, 'pyre_slider_type', true) == 'flex2') {
	//	wp_enqueue_script( 'jquery.flexslider' );
	//}

// 	if(!$data['status_jqcycle']) {	
// 	    wp_deregister_script( 'jquery.cycle' );
// 	    wp_register_script( 'jquery.cycle', get_bloginfo('template_directory').'/js/jquery.cycle.lite.js', array(), null, true);
// 		wp_enqueue_script( 'jquery.cycle' );
// 	}

   // wp_deregister_script( 'jquery.fitvids' );
  //  wp_register_script( 'jquery.fitvids', get_bloginfo('template_directory').'/js/jquery.fitvids.js', array(), null, true);
 //   wp_enqueue_script( 'jquery.fitvids' );

//     wp_deregister_script( 'jquery.hoverIntent' );
//     wp_register_script( 'jquery.hoverIntent', get_bloginfo('template_directory').'/js/jquery.hoverIntent.minified.js', array(), null, true);
//   	wp_enqueue_script( 'jquery.hoverIntent' );

   // wp_deregister_script( 'jquery.easing' );
   // wp_register_script( 'jquery.easing', get_bloginfo('template_directory').'/js/jquery.easing.js', array(), null, false);
	//wp_enqueue_script( 'jquery.easing' );

// 	if(!$data['status_eslider']) {	
// 	    wp_deregister_script( 'jquery.eislideshow' );
// 	  //  wp_register_script( 'jquery.eislideshow', get_bloginfo('template_directory').'/js/jquery.eislideshow.js', array(), null, true);
// 	    //if(get_post_meta($slider_page_id, 'pyre_slider_type', true) == 'elastic') {
// 			wp_enqueue_script( 'jquery.eislideshow' );
// 		//}
// 	}

	//if(!$data['status_vimeo']) {
	  //  wp_deregister_script( 'froogaloop' );
	  //  wp_register_script( 'froogaloop', get_bloginfo('template_directory').'/js/froogaloop.js', array(), null, true);
	//	wp_enqueue_script( 'froogaloop' );
	//}

//     wp_deregister_script( 'jquery.placeholder' );
//     wp_register_script( 'jquery.placeholder', get_bloginfo('template_directory').'/js/jquery.placeholder.js', array(), null, true);
//  	wp_enqueue_script( 'jquery.placeholder' );

	//if(!$data['status_wpoint']) {
	  //  wp_deregister_script( 'jquery.waypoint' );
	  //  wp_register_script( 'jquery.waypoint', get_bloginfo('template_directory').'/js/jquery.waypoint.js', array(), null, true);
	//wp_enqueue_script( 'jquery.waypoint' );
	//}

	//wp_deregister_script('gmaps.api');
	//wp_register_script('gmaps.api', 'https://maps.google.com/maps/api/js?v=3.exp&amp;sensor=false&amp;language='.substr(get_locale(), 0, 2), array(), false, true);
	//if(is_page_template('contact.php') || is_page_template('contact-2.php')) {
		//wp_enqueue_script( 'gmaps.api' );
	//}

// 	if(!$data['status_gmap']) {
// 		wp_deregister_script( 'jquery.ui.map' );
// 		wp_register_script( 'jquery.ui.map', get_bloginfo('template_directory').'/js/gmap.js', array(), null, true);
// 		//if(is_page_template('contact.php') || is_page_template('contact-2.php')) {
// 		//	wp_enqueue_script( 'jquery.ui.map' );
// 		//}
// 	}

	//if(!$data['status_gauge']) {
	//	wp_deregister_script( 'jquery.gauge' );
	//	wp_register_script( 'jquery.gauge', get_bloginfo('template_directory').'/js/gauge.js', array(), null, true);
	//	wp_enqueue_script( 'jquery.gauge' );
	//}

// 	wp_deregister_script( 'jquery.ddslick.' );
// 	wp_register_script( 'jquery.ddslick', get_bloginfo('template_directory').'/js/jquery.ddslick.min.js', array(), null, true);
// 	wp_enqueue_script( 'jquery.ddslick' );

	//if($data['blog_pagination_type'] == 'Infinite Scroll' || is_page_template('demo-gridblog.php') || is_page_template('demo-timelineblog.php')) {
	 /*    wp_deregister_script( 'jquery.infinitescroll' );
	    wp_register_script( 'jquery.infinitescroll', get_bloginfo('template_directory').'/js/jquery.infinitescroll.min.js', array(), null, true);
		wp_enqueue_script( 'jquery.infinitescroll' ); */
	//}

  //  wp_deregister_script( 'avada' );
   // wp_register_script( 'avada', get_bloginfo('template_directory').'/js/main.js', array(), null, true);
	//wp_enqueue_script( 'avada' );
	}
}
add_action('wp_enqueue_scripts', 'avada_scripts');

function avada_admin_scripts() {
	global $pagenow;

	if (is_admin() && ($pagenow=='post.php')) {
    	wp_register_script('avada_vc_converter', get_bloginfo('template_directory').'/js/vc_converter.js');
    	wp_enqueue_script('avada_vc_converter');

    	wp_register_style('avada_vc_converter', get_bloginfo('template_directory').'/css/vc_converter.css');
    	wp_enqueue_style('avada_vc_converter');
	}
}
add_action('admin_enqueue_scripts', 'avada_admin_scripts');

add_filter('jpeg_quality', 'avada_image_full_quality');
add_filter('wp_editor_set_quality', 'avada_image_full_quality');
function avada_image_full_quality($quality) {
    return 100;
}

add_filter('get_archives_link', 'avada_cat_count_span');
add_filter('wp_list_categories', 'avada_cat_count_span');
function avada_cat_count_span($links) {
	$get_count = preg_match_all('#\((.*?)\)#', $links, $matches);

	if($matches) {
		$i = 0;
		foreach($matches[0] as $val) {
			$links = str_replace('</a> '.$val, ' '.$val.'</a>', $links);
			$links = str_replace('</a>&nbsp;'.$val, ' '.$val.'</a>', $links);
			$i++;
		}
	}

	return $links;
}

remove_action('wp_head', 'adjacent_posts_rel_link_wp_head');

add_filter('pre_get_posts','avada_SearchFilter');
function avada_SearchFilter($query) {
	global $data;
	if($query->is_search) {
		if($data['search_content'] == 'Only Posts') {
			$query->set('post_type', 'post');
		}

		if($data['search_content'] == 'Only Pages') {
			$query->set('post_type', 'page');
		}
	}
	return $query;
}

add_action('admin_head', 'avada_admin_css');
function avada_admin_css() {
	echo '<link rel="stylesheet" type="text/css" href="'.get_template_directory_uri().'/css/admin_shortcodes.css">';
}

/* Theme Activation Hook */
add_action('admin_init','avada_theme_activation');
function avada_theme_activation()
{
	global $pagenow;
	if(is_admin() && 'themes.php' == $pagenow && isset($_GET['activated'])) 
	{
		update_option('shop_catalog_image_size', array('width' => 500, 'height' => '', 0));
		update_option('shop_single_image_size', array('width' => 500, 'height' => '', 0));
		update_option('shop_thumbnail_image_size', array('width' => 120, 'height' => '', 0));
	}
}

// Register default function when plugin not activated
add_action('wp_head', 'avada_plugins_loaded');
function avada_plugins_loaded() {
	if(!function_exists('is_woocommerce')) {
		function is_woocommerce() { return false; }
	}
}

// Woocommerce Hooks
remove_action('woocommerce_before_main_content', 'woocommerce_breadcrumb', 20);
remove_action('woocommerce_before_shop_loop', 'woocommerce_result_count', 20);
remove_action('woocommerce_before_shop_loop', 'woocommerce_catalog_ordering', 30);

add_action('woocommerce_before_shop_loop', 'avada_woocommerce_catalog_ordering', 30);
function avada_woocommerce_catalog_ordering() {
	global $data;

	parse_str($_SERVER['QUERY_STRING'], $params);

	$query_string = '?'.$_SERVER['QUERY_STRING'];

	// replace it with theme option
	if($data['woo_items']) {
		$per_page = $data['woo_items'];
	} else {
		$per_page = 12;
	}

	$pob = !empty($params['product_orderby']) ? $params['product_orderby'] : 'default';
	$po = !empty($params['product_order'])  ? $params['product_order'] : 'asc';
	$pc = !empty($params['product_count']) ? $params['product_count'] : $per_page;

	$html = '';
	$html .= '<div class="catalog-ordering clearfix">';

	$html .= '<div class="orderby-order-container">';

	$html .= '<ul class="orderby order-dropdown">';
	$html .= '<li>';
	$html .= '<span class="current-li"><a>'.__('Sort by', 'Avada').' <strong>'.__('Default Order', 'Avada').'</strong></a></span>';
	$html .= '<ul>';
	$html .= '<li class="'.(($pob == 'default') ? 'current': '').'"><a href="'.tf_addURLParameter($query_string, 'product_orderby', 'default').'">'.__('Sort by', 'Avada').' <strong>'.__('Default Order', 'Avada').'</strong></a></li>';
	$html .= '<li class="'.(($pob == 'name') ? 'current': '').'"><a href="'.tf_addURLParameter($query_string, 'product_orderby', 'name').'">'.__('Sort by', 'Avada').' <strong>'.__('Name', 'Avada').'</strong></a></li>';
	$html .= '<li class="'.(($pob == 'price') ? 'current': '').'"><a href="'.tf_addURLParameter($query_string, 'product_orderby', 'price').'">'.__('Sort by', 'Avada').' <strong>'.__('Price', 'Avada').'</strong></a></li>';
	$html .= '<li class="'.(($pob == 'date') ? 'current': '').'"><a href="'.tf_addURLParameter($query_string, 'product_orderby', 'date').'">'.__('Sort by', 'Avada').' <strong>'.__('Date', 'Avada').'</strong></a></li>';
	$html .= '<li class="'.(($pob == 'rating') ? 'current': '').'"><a href="'.tf_addURLParameter($query_string, 'product_orderby', 'rating').'">'.__('Sort by', 'Avada').' <strong>'.__('Rating', 'Avada').'</strong></a></li>';
	$html .= '</ul>';
	$html .= '</li>';
	$html .= '</ul>';


	$html .= '<ul class="order">';
	if($po == 'desc'):
	$html .= '<li class="desc"><a href="'.tf_addURLParameter($query_string, 'product_order', 'asc').'"><i class="icon-arrow-up"></i></a></li>';
	endif;
	if($po == 'asc'):
	$html .= '<li class="asc"><a href="'.tf_addURLParameter($query_string, 'product_order', 'desc').'"><i class="icon-arrow-down"></i></a></li>';
	endif;
	$html .= '</ul>';

	$html .= '</div>';

	$html .= '<ul class="sort-count order-dropdown">';
	$html .= '<li>';
	$html .= '<span class="current-li"><a>'.__('Show', 'Avada').' <strong>'.$per_page.' '.__(' Products', 'Avada').'</strong></a></span>';
	$html .= '<ul>';
	$html .= '<li class="'.(($pc == $per_page) ? 'current': '').'"><a href="'.tf_addURLParameter($query_string, 'product_count', $per_page).'">'.__('Show', 'Avada').' <strong>'.$per_page.' '.__('Products', 'Avada').'</strong></a></li>';
	$html .= '<li class="'.(($pc == $per_page*2) ? 'current': '').'"><a href="'.tf_addURLParameter($query_string, 'product_count', $per_page*2).'">'.__('Show', 'Avada').' <strong>'.($per_page*2).' '.__('Products', 'Avada').'</strong></a></li>';
	$html .= '<li class="'.(($pc == $per_page*3) ? 'current': '').'"><a href="'.tf_addURLParameter($query_string, 'product_count', $per_page*3).'">'.__('Show', 'Avada').' <strong>'.($per_page*3).' '.__('Products', 'Avada').'</strong></a></li>';
	$html .= '</ul>';
	$html .= '</li>';
	$html .= '</ul>';
	$html .= '</div>';

	echo $html;
}

add_action('woocommerce_get_catalog_ordering_args', 'avada_woocommerce_get_catalog_ordering_args', 20);
function avada_woocommerce_get_catalog_ordering_args($args)
{
	parse_str($_SERVER['QUERY_STRING'], $params);

	$pob = !empty($params['product_orderby']) ? $params['product_orderby'] : 'default';
	$po = !empty($params['product_order'])  ? $params['product_order'] : 'asc';

	switch($pob) {
		case 'date':
			$orderby = 'date';
			$order = 'desc';
			$meta_key = ''; 
		break;
		case 'price':
			$orderby = 'meta_value_num';
			$order = 'asc';
			$meta_key = '_price';
		break;
		case 'popularity':
			$orderby = 'meta_value_num';
			$order = 'desc';
			$meta_key = 'total_sales';
		break;
		case 'title':
			$orderby = 'title';
			$order = 'asc';
			$meta_key = '';
		break;
		case 'default':
		default:
			$orderby = 'menu_order title';
			$order = 'asc';
			$meta_key = '';
		break;
	}

	switch($po) {
		case 'desc':
			$order = 'desc';
		break;
		case 'asc':
			$order = 'asc';
		break;
		default:
			$order = 'asc';
		break;
	}

	$args['orderby'] = $orderby;
	$args['order'] = $order;
	$args['meta_key'] = $meta_key;
	
	return $args;
}

add_filter('loop_shop_per_page', 'avada_loop_shop_per_page');
function avada_loop_shop_per_page()
{
	global $data;

	parse_str($_SERVER['QUERY_STRING'], $params);

	if($data['woo_items']) {
		$per_page = $data['woo_items'];
	} else {
		$per_page = 12;
	}

	$pc = !empty($params['product_count']) ? $params['product_count'] : $per_page;

	return $pc;
}

add_action('woocommerce_before_shop_loop_item_title', 'avada_woocommerce_thumbnail', 10);
remove_action('woocommerce_before_shop_loop_item_title', 'woocommerce_template_loop_product_thumbnail', 10);
function avada_woocommerce_thumbnail() {
	global $product, $woocommerce;

	$items_in_cart = array();

	if($woocommerce->cart->get_cart() && is_array($woocommerce->cart->get_cart())) {
		foreach($woocommerce->cart->get_cart() as $cart) {
			$items_in_cart[] = $cart['product_id'];
		}
	}

	$id = get_the_ID();
	$in_cart = in_array($id, $items_in_cart);
	$size = 'shop_catalog';

	$gallery = get_post_meta($id, '_product_image_gallery', true);
	$attachment_image = '';
	if(!empty($gallery)) {
		$gallery = explode(',', $gallery);
		$first_image_id = $gallery[0];
		$attachment_image = wp_get_attachment_image($first_image_id , $size, false, array('class' => 'hover-image'));
	}
	$thumb_image = get_the_post_thumbnail($id , $size);

	if($attachment_image) {
		$classes = 'crossfade-images';
	} else {
		$classes = '';
	}

	echo '<span class="'.$classes.'">';
	echo $attachment_image;
	echo $thumb_image;
	if($in_cart) {
		echo '<span class="cart-loading"><i class="icon-check"></i></span>';
	} else {
		echo '<span class="cart-loading"><i class="icon-spinner"></i></span>';
	}
	echo '</span';
}
add_filter('add_to_cart_fragments', 'avada_woocommerce_header_add_to_cart_fragment');
function avada_woocommerce_header_add_to_cart_fragment( $fragments ) {
	global $woocommerce;

	ob_start();
	?>
	<li class="cart">
		<?php if(!$woocommerce->cart->cart_contents_count): ?>
		<a href="<?php echo get_permalink(get_option('woocommerce_cart_page_id')); ?>"><?php _e('Cart', 'Avada'); ?></a>
		<?php else: ?>
		<a href="<?php echo get_permalink(get_option('woocommerce_cart_page_id')); ?>"><?php echo $woocommerce->cart->cart_contents_count; ?> <?php _e('Item(s)', 'Avada'); ?> - <?php echo woocommerce_price($woocommerce->cart->cart_contents_total); ?></a>
		<div class="cart-contents">
			<?php foreach($woocommerce->cart->cart_contents as $cart_item): //var_dump($cart_item); ?>
			<div class="cart-content">
				<a href="<?php echo get_permalink($cart_item['product_id']); ?>">
				<?php echo get_the_post_thumbnail($cart_item['product_id'], 'recent-works-thumbnail'); ?>
				<div class="cart-desc">
					<span class="cart-title"><?php echo $cart_item['data']->post->post_title; ?></span>
					<span class="product-quantity"><?php echo $cart_item['quantity']; ?> x <?php echo $woocommerce->cart->get_product_subtotal($cart_item['data'], $cart_item['quantity']); ?></span>
				</div>
				</a>
			</div>
			<?php endforeach; ?>
			<div class="cart-checkout">
				<div class="cart-link"><a href="<?php echo get_permalink(get_option('woocommerce_cart_page_id')); ?>"><?php _e('View Cart', 'Avada'); ?></a></div>
				<div class="checkout-link"><a href="<?php echo get_permalink(get_option('woocommerce_checkout_page_id')); ?>"><?php _e('Checkout', 'Avada'); ?></a></div>
			</div>
		</div>
		<?php endif; ?>
	</li>
	<?php
	$fragments['.top-menu .cart'] = ob_get_clean();

	ob_start();
	?>
	<li class="cart">
		<?php if(!$woocommerce->cart->cart_contents_count): ?>
		<a class="my-cart-link" href="<?php echo get_permalink(get_option('woocommerce_cart_page_id')); ?>"></a>
		<?php else: ?>
		<a class="my-cart-link my-cart-link-active" href="<?php echo get_permalink(get_option('woocommerce_cart_page_id')); ?>"></a>
		<div class="cart-contents">
			<?php foreach($woocommerce->cart->cart_contents as $cart_item): //var_dump($cart_item); ?>
			<div class="cart-content">
				<a href="<?php echo get_permalink($cart_item['product_id']); ?>">
				<?php echo get_the_post_thumbnail($cart_item['product_id'], 'recent-works-thumbnail'); ?>
				<div class="cart-desc">
					<span class="cart-title"><?php echo $cart_item['data']->post->post_title; ?></span>
					<span class="product-quantity"><?php echo $cart_item['quantity']; ?> x <?php echo $woocommerce->cart->get_product_subtotal($cart_item['data'], $cart_item['quantity']); ?></span>
				</div>
				</a>
			</div>
			<?php endforeach; ?>
			<div class="cart-checkout">
				<div class="cart-link"><a href="<?php echo get_permalink(get_option('woocommerce_cart_page_id')); ?>"><?php _e('View Cart', 'Avada'); ?></a></div>
				<div class="checkout-link"><a href="<?php echo get_permalink(get_option('woocommerce_checkout_page_id')); ?>"><?php _e('Checkout', 'Avada'); ?></a></div>
			</div>
		</div>
		<?php endif; ?>
	</li>
	<?php
	$fragments['#header .cart'] = ob_get_clean();
	
	return $fragments;
	
}

// Get Custom Field Template Values
function get_custom_field($field) {
	global $post;
	$custom_field_data = get_post_meta($post->ID, $field, false);
	if($custom_field_data) {
		if( count($custom_field_data) > 1 ) {
			return $custom_field_data;
		} else {
			return $custom_field_data[0];
		}
	} else {
		return false;
	}
}









/**
 * redirecting to profile.php
 */
function admin_index_redirect() {
	global $current_user;
	get_currentuserinfo();
	if($current_user->roles[0] == "editor"
		||$current_user->roles[0] == 'author'){
		//wp_redirect( get_admin_url(null,'edit.php'));
		echo "<script type='text/javascript'>window.location.href = '".get_admin_url(null,'edit.php')."';</script>";
		exit;
	}
}
add_action('admin_head-index.php', 'admin_index_redirect');



/**
 *  Admin Header Menu
 */
function action_in_admin_header() {

	global $current_user;
	get_currentuserinfo();
	
	
	
	// editer
	if($current_user->roles[0] == 'editor'){
		?>
		
		
		
		
		<?php
	}
	
	
	// author
	if($current_user->roles[0] == 'author'){
		?>
<style type="text/css">
#youkai_admin_menu_block {
	height : 20px;
}
.youkai_admin_menu {
	float:left;
	padding : 0 20px 20px 0;
	
}
</style>
<h1>妖怪ワールド管理画面 [<a href="<?php echo home_url();?>" target="_blank">サイトを開く</a>]</h1>
<div id="youkai_admin_menu_block">
	<div class="youkai_admin_menu"><a href="<?php echo get_admin_url(null,'post-new.php');?>">新規追加</a></div>
	<div class="youkai_admin_menu"><a href="<?php echo get_admin_url(null,'edit.php');?>">投稿一覧</a></div>
	<div class="youkai_admin_menu"><a href="<?php echo get_admin_url(null,'profile.php');?>">アカウント設定</a></div>
	<div class="youkai_admin_menu"><a href="<?php echo wp_logout_url();?>">ログアウト</a> (<?php echo $current_user->user_login; ?>でログイン中 )</div>
	<div class="clear"></div>
</div>
	<?php
	}
}
add_action('in_admin_header', 'action_in_admin_header');



/**
 *change admin login logo
 */
function _login_head(){
	?>
	<style>
		#login h1 a{
			background:url(<?php bloginfo('template_url'); ?>/images/img/admin/header_logo.png);
			background-repeat:no-repeat;
			width:396px;
			height:78px;
			margin: auto -40px;
		}
		@media screen and (-webkit-min-device-pixel-ratio:0) {
		#login h1 a{
			margin: auto -40px;
			background-size: 396px 78px !important;
		}
		}
	</style>
	<?php
}
add_action('login_head', '_login_head');



/**
 *  Admin "All" css
 */
function admin_head_css() {

	global $current_user;
	get_currentuserinfo();
	
	
	
	// editer
	if($current_user->roles[0] == 'editor'){
		
		

	}
	
	// author
	if($current_user->roles[0] == 'author'){
		?>

<style type="text/css">
#admin-bar-top-secondary, #adminmenuback,
#adminmenuwrap, #icon-edit
{display:none;}


#wpcontent, #wpfooter {
    margin-left: 30px;
}
.subsubsub .count {display:none;}
</style>
	<?php
	}
	
	
	
	// editer and author
	if($current_user->roles[0] == "editor"
		||$current_user->roles[0] == 'author'){
	?>
<style type="text/css">

</style>
		
	<?php
	}
	
	
	
}
add_action('admin_head', 'admin_head_css');


/**
 *  Admin "profile" css
 */
function admin_profile_css() {

	global $current_user;
	get_currentuserinfo();
	if($current_user->roles[0] == "editor"
		||$current_user->roles[0] == 'author'){
	?>
<style type="text/css">

#icon-profile {display:none;}
h3{display:none;}
table:nth-of-type(1){display:none;}

table:nth-of-type(2) tr:nth-child(2){display:none;}
table:nth-of-type(2) tr:nth-child(3){display:none;}
table:nth-of-type(2) tr:nth-child(4){display:none;}
table:nth-of-type(2) tr:nth-child(5){display:none;}

table:nth-of-type(3) tr:nth-child(2){display:none;}
table:nth-of-type(3) tr:nth-child(3){display:none;}
table:nth-of-type(3) tr:nth-child(4){display:none;}
table:nth-of-type(3) tr:nth-child(5){display:none;}

table:nth-of-type(4) tr:nth-child(1){display:none;}
table:nth-of-type(5) {display:none;}

</style>
	<?php
	}
}
add_action('admin_head-profile.php', 'admin_profile_css');


/**
 *  Admin "post-new" css
 */
function admin_post_new_css() {

	global $current_user;
	get_currentuserinfo();
	
	if($current_user->roles[0] == "editor"
		||$current_user->roles[0] == 'author'){
		?>
<style type="text/css">

#sbg_box, #icon-edit {display:none;}
.compat-attachment-fields {display:none;}
#advanced-sortables {display:none;}
#featured-image-2_post
,#featured-image-3_post
,#featured-image-4_post
,#featured-image-5_post
 {display:none;}
</style>
	<?php
	}
	
	
	if($current_user->roles[0] == 'author'){
	?>
<style type="text/css">
#major-publishing-actions,#visibility {display:none;}
#acf-youkai_image_2,#acf-youkai_post_type {display:none;}
#preview-action {display:none;}
.updated .below-h2 {display:none;}
</style>
	<?php
	}
	
}
	
add_action('admin_head-post-new.php', 'admin_post_new_css');
add_action('admin_head-post.php', 'admin_post_new_css');


/*
 *  only show myself posts
 */
function filter_other_post( $wp_query ) {
	global $pagenow, $current_user;

	if($pagenow == "edit.php" ) {
		if($current_user->roles[0] == "author") {
			$wp_query->query_vars['author'] = $current_user->ID;
		}
	}

}
add_action( "pre_get_posts", "filter_other_post" );

/*
 *  only show myself media
*/
function set_ajax_media_query( $wp_query ) {
	global $current_user;
	if( $wp_query ->query_vars['post_type'] != "attachment" ) {
		return;
	}
	get_currentuserinfo();
	if( $current_user->roles[0] == "administrator" 
		|| $current_user->roles[0] == "editor"
	) {
		return;
	}
	$wp_query->query_vars['author'] = $current_user->ID;
}
add_action( "pre_get_posts", "set_ajax_media_query" );


/*
 *   show post list by ajax
 *   
 *    sample 
 *      http://youkai-watch.localhost/admin/admin-ajax.php?action=get_youkai_post_list&youkai_post_type=1&offset=2
 *   
 */

function _get_youkai_post_list( $wp_query ) {
	global $wp_query;
	$offset            = $_REQUEST['offset'];
	$limit             = $_REQUEST['limit'];
	$category_name     = $_REQUEST['category_name'];
	// 1 : public_site
	// 2 : youkai_world
	$youkai_post_type  = $_REQUEST['youkai_post_type'];
	
	$offset = ($offset) ? $offset : 0;
	$limit = ($limit) ? $limit : 1;
	
	$args = array(
		'post_type' => 'post',
		'meta_query' => array(
			array(
				'key' => 'youkai_post_type',
				'value' => $youkai_post_type
			)
		),
		'post_status'    => 'publish',
		'offset'         => $offset,
		'posts_per_page' => $limit,
		'meta_key'       => 'youkai_sticky',
		'orderby'        => 'meta_value_num date',
		'myorder'        => 'DESC DESC',
	);
	if ($category_name) {
		$cat = get_category_by_slug($category_name);
		if ($cat->term_id) {
			$args['meta_query'][] = array(
				'key' => 'youkai_category',
				'value' => $cat->term_id
			);
		}
	}
	//print_r($args);
	query_posts($args);//実行
	//print_r($wp_query);
	
	$arr = array();
	if (!have_posts()) {
		echo "NO_DATA";
		die();
	}
	
	while(have_posts()): the_post();
		
		show_post_list_block($data);
		
	endwhile;
	die();
}
add_action('wp_ajax_get_youkai_post_list', '_get_youkai_post_list');
add_action('wp_ajax_nopriv_get_youkai_post_list', '_get_youkai_post_list');


/*
 *   admin post list columns adjust
 */
function custom_columns($columns) {
	unset($columns['comments']);
	unset($columns['tags']);
	return $columns;
}
add_filter( 'manage_posts_columns', 'custom_columns' );


function show_youkai_new_icom() {

	$post_time = strtotime(get_the_date('Y/m/d'));
	
	$new_flg = (strtotime("-1 week") < $post_time);
	
	?>
	<?php $youkai_new = get_custom_field('youkai_new_show_important');?>
	<?php if ($new_flg || ($youkai_new) == 1):?><img alt="youkai_new_show_important" src="<?php bloginfo('template_url'); ?><?php echo '/images/img/new_icon.png' ; ?> "><?php endif; ?>
	<?php 
}

function show_youkai_link($inside) {
	?>
	<?php if( get_field('youkai_url') != null ){ ?>
		<a href="<?php the_field('youkai_url'); ?>" target="_blank"><?php echo $inside; ?></a>
	<?php } else { ?>
		<a href="<?php the_permalink(); ?>" target="_blank"><?php echo $inside; ?></a>
	<?php } ?>
	<?php 
}


function get_youkai_image_tag($key) {
	$data = get_field($key);
	if (!$data) { return; }
	$alt = (strlen($data['alt'])) ? $data['alt'] : $key;
	$image_path = $data['url'];
	$image_path = apply_filters( 'jetpack_photon_url', $image_path );
	return '<img alt="'. $alt .'" src="'. $image_path .'" />';
}




/*
 *   show post list block
*/
function show_post_list_block ($data) {
	?>
	<?php $display_type = get_custom_field('youkai_display_type');?>
	<div class="<?php echo "display_type_" . $display_type ?>">
		<?php $post_type = get_custom_field('youkai_post_type');?>
		<div class="<?php echo "home_post_border_" . $post_type ?>">
			<div id="post-<?php the_ID(); ?>" <?php post_class($post_class.getClassAlign($post_count).' clearfix'); ?>>
				<?php if($data['blog_layout'] == 'Timeline'): ?>
				<?php if(is_null($prev_post_month )): ?>
					<h3 class="timeline-title"><?php echo get_the_date('F Y'); ?></h3>
				<?php elseif($prev_post_month != $post_month): ?>
					<h3 class="timeline-title"><?php echo get_the_date('F Y'); ?></h3>
				<?php endif; ?>
				<?php endif; ?>
				<div class="post-content-container" >
					<div class="post_img">
						<?php
							$img_tag = get_youkai_image_tag('youkai_image_1'); 
							if ($img_tag) { show_youkai_link($img_tag); }
						?>
					</div>
					<div class="icons_area">
						<div class="post-date-youkai">
							<p class="single-line-meta"><?php the_time('Y.m.d'); ?></p>
						</div> 
						<div class="post-new">
							<?php show_youkai_new_icom();?>
						</div>
					</div>
					
					
					<div class="post-content">
						<div class="post-category">
							<?php show_category_icon(); ?>
						</div>
						<h2 class="post-title">
							<?php 
								$inside_a_tag = get_the_title();
								show_youkai_link($inside_a_tag);
							?>
						</h2>
						<div class="youkai-content">
						<?php if( get_field('youkai_content') != null ){ ?>
							<?php echo the_field('youkai_content'); ?>
						<?php } ?>
						</div>
					</div>
					<div style="clear:both;"></div>
					<div class="meta-info"></div>
					
				</div>
			</div>
		</div>
	</div>
<?php 
}


/*
 * Side bar
 */

function show_youkai_side_bar($is_top) {

	wp_reset_query();
?>
	
	<div id="sidebar" style="<?php echo $sidebar_css; ?>">
		<div id="sticker">
		
		<?php if (!$is_top) { ?>
		<div>
			<img src="<?php bloginfo('template_url') ?>/images/content_header_img03.png" />
		</div>
		<div class="greyback">
			<table>
				<?php
					$border_i = 0;
					
					$args = array(
						'post_type' => 'post',
						'post_status' => 'publish',
						'order'=> 'DESC', 
						'orderby' => 'date',
						'posts_per_page' => 5,
						'meta_key'       => 'youkai_sticky',
						'orderby'        => 'meta_value_num date',
						'myorder'        => 'DESC DESC',
					);
					query_posts($args);
					while(have_posts()): the_post();
						$border_i++;
						?>
						<tr <?php if($border_i !== 1) {echo 'class="border"';} ?> >
							<td>
								<?php the_time('Y.m.d'); ?>
								<?php 
								$cat = get_the_category();
								$slug = $cat[0]->slug;
								if($slug == 'other'){
									echo '[その他]';
								?>
								<?php 
								}else{?>
								[<?php echo $cat[0]->name; ?>]
								<?php }?>
								<br />
								<?php if( get_field('youkai_url') != null ){ ?>
									<a href="<?php the_field('youkai_url'); ?>" target="_blank"><?php the_title(); ?></a>
								<?php } else { ?>
									<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
								<?php } ?>
							</td>
						</tr>
						<?php
					endwhile;
				?>
			</table>
		</div>
		<?php } ?>
		
		<div id="sidebar" style="<?php echo $sidebar_css; ?>">
			<div id="sticker">
			<?php
				if (!function_exists('dynamic_sidebar') || !dynamic_sidebar('Blog Sidebar')):
				endif;
			?>
			</div>
		</div>
		
		
		
		</div>
	</div>

<?php 
}



function show_menu() {
	?>
	
	<li class="menu-item menu-item-type-post_type menu-item-object-page menu-item-50"><a href="http://www.youkai-watch.jp/" target="_blank">----ゲーム----</a></li>
	<li class="menu-item menu-item-type-post_type menu-item-object-page menu-item-51"><a href="http://www.tv-tokyo.co.jp/anime/youkai-watch/index2.html" target="_blank">---TVアニメ----</a></li>
	<li class="menu-item menu-item-type-post_type menu-item-object-page menu-item-52"><a href="http://www.corocoro.tv/" target="_blank">コロコロコミック</a></li>
	<li class="menu-item menu-item-type-post_type menu-item-object-page menu-item-53"><a href="http://yw.b-boys.jp/member/menus/" target="_blank">---妖怪メダル---</a></li>
	<li class="menu-item menu-item-type-post_type menu-item-object-page menu-item-54"><a href="http://www.yokai-toritsukicard.com/countdown/"  target="_blank">とりつきカードバトル</a></li>
	<li class="menu-item menu-item-type-post_type menu-item-object-page menu-item-55"><a >トモダチウキウキペディア</a></li>
	<li class="menu-item menu-item-type-post_type menu-item-object-page menu-item-56"><a href="http://youkai-world.com/other/p1651/"  target="_blank">----音楽---</a></li>
	<li class="menu-item menu-item-type-post_type menu-item-object-page menu-item-57"><a href="http://kids.bc.mediafactory.jp/youkai-watch/dvd/" target="_blank">---DVD---</a></li>
	<li class="menu-item menu-item-type-post_type menu-item-object-page menu-item-58"><a href="http://www.eiga-yokai.jp/"  target="_blank">----映画---</a></li>
	<li class="menu-item menu-item-type-post_type menu-item-object-page menu-item-59"><a href="http://youkai-world.com/goods/"  target="_blank">---グッズ---</a></li>
	<li class="menu-item menu-item-type-post_type menu-item-object-page menu-item-60"><a href="#">プレゼント投稿企画</a></li>
	<?php
}




function my_posts_orderby($orderby, $query) {
	$orders = explode(' ', strtoupper($query->get('myorder')));
	if (1 < count($orders)) {
		$orderby_array = array();
		foreach (explode(',', str_replace(' DESC', '', $orderby)) as $i => $the_orderby) {
			if (!isset($orders[$i]) || !in_array($orders[$i], array('ASC', 'DESC')))
				$orderby_array[] = $the_orderby . ' DESC' ;
			else
				$orderby_array[] = $the_orderby . ' ' . $orders[$i];
		}
		$orderby = implode(',', $orderby_array);
	}
	return $orderby;
}
add_filter('posts_orderby', 'my_posts_orderby', 10, 2);



function show_category_icon() {
	
	$post_type = get_custom_field('youkai_post_type');
	if ($post_type != 1) { return; }
	
	$cat = get_the_category();
	if ($cat[0]->slug) {
		$image_path = '/images/img/category_icons/' . 'icon_' . $cat[0]->slug  . '.png' ;
		if (file_exists(get_template_directory() . $image_path)) {
			?>
			<img alt="youkai_category" src="<?php echo get_bloginfo('template_directory') . $image_path; ?>">
			<?php
		}
	}
}

/*
 *  send maile to editor when change status waiting revier
 */

add_action( 'transition_post_status' , 'tran_post_method',10, 3 ); 
function tran_post_method($new_status, $old_status, $post) {

	global $current_user;

	get_currentuserinfo();
	
	if($current_user->roles[0] != "author") {
		return;
	}
	if ($new_status != 'pending') {
		return;
	}
	
	$post_title = $post->post_title;
	// http://youkai-watch.localhost/admin/post.php?post=390&action=edit
	$edit_url =  admin_url('post.php?post=' . $post->ID . '&action=edit');
	
	$subject = "【妖怪ワールド】レビュー待ち通知";
	$message = "
下記の記事がレビュー待ちにとして保存されました。

タイトル：$post_title
管理画面：$edit_url

";
	
	$logs = "---------------\n";
	$logs .= date('Y/m/d H:i:s')."\n";
	$logs .= "$subject\n";
	$logs .= "$message\n";
	
	$users = get_users('role=editor');
	foreach ($users as $user) {
		$to = $user->user_email;
		$logs .= "$to\n";
		wp_mail( $to, $subject, $message, $headers = 'From:youkaiworld', $attachments = array() );
	}
	
	$file = get_template_directory() . '/pending_mail.log';
	file_put_contents($file, $logs, FILE_APPEND | LOCK_EX);
	
}
add_filter('wp_mail_from', 'my_mail_from');
function my_mail_from($addr) {
	return 'youkaiworld@sv101.sixcore.ne.jp';
}
add_filter('wp_mail_from_name', 'my_mail_from_name');
function my_mail_from_name($name) {
	return 'youkai_world';
}



function my_print_footer_scripts() {
	echo '<script type="text/javascript">
  //<![CDATA[
  jQuery(document).ready(function($){
    $(".categorychecklist input[type=checkbox]").each(function(){
      $check = $(this);
      var checked = $check.attr("checked") ? \' checked="checked"\' : \'\';
      $(\'<input type="radio" id="\' + $check.attr("id")
        + \'" name="\' + $check.attr("name") + \'"\'
  	+ checked
	+ \' value="\' + $check.val()
	+ \'"/>\'
      ).insertBefore($check);
      $check.remove();
    });
  });
  //]]>
  </script>';
}
add_action('admin_print_footer_scripts', 'my_print_footer_scripts', 21);


function custom_login_logo_link(){
	return get_bloginfo('home').'/';
}
add_filter('login_headerurl','custom_login_logo_link');

function custom_login_headertitle_link(){
	return '妖怪ウォッチ';
}
add_filter('login_headertitle','custom_login_headertitle_link');


// 投稿でビジュアルモードとテキストモードの切替を行えるプラグインを導入した際に
// 発生するpタグが削除される不具合を修正
add_filter( 'acf_the_content', 'wpautop' );


add_filter('acf/load_value/name=youkai_content_2', 'inject_jetpack_photon', 10, 3);
function inject_jetpack_photon($value, $post_id, $field){
	function __jetpack_photon($m){
		return $m[1].apply_filters( 'jetpack_photon_url', $m[2] ).$m[3];
	}
	return preg_replace_callback("/(<img [^>]*src=[\"'])(https?:\/\/[^\"']*(?:jpg|gif|png|bmp|jpeg)[^\"']*)([\"'][^>]*>)/","__jetpack_photon",$value);
}

function enforce_dashicons() {
	echo "<style type='text/css'>.mce-ico,.qt-dfw {font-family: dashicons !important;}</style>";	
}
add_action("admin_head","enforce_dashicons");
