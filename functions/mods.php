<?php
/* 
 * Use this file to create additional modifications to 
 * the theme.
 * Functions included are:
 * 1. Disallow File Edits from within WP Admin - this is a security measure
 * 2. Change the WordPress title placeholder text for custom post types
 * 3. Add a custom class to the site front page
 * 4. Remove Emojis - we don't need them!
 * 5. Remove inline styles for comments - remove this if comments are needed
 * 6. Remove WordPress Version numbers from source code
 * 7. Add the active class to custom post type archives
 * 8. Add custom numeric pagination 
 */
 
/*
 * Prevent file edits from WP admin area for extra security 
 */
define('DISALLOW_FILE_EDIT', true);

/*
 * Filter the title for a given post type
 */
function xpand_change_default_title( $title ){
    $screen = get_current_screen();
    if ( 'put-post-type-name-here' == $screen->post_type ){
        $title = 'Enter your custom text here';
    }
    return $title;
}
// uncomment below to run this function
// add_filter( 'enter_title_here', 'xpand_change_default_title' );

/*
 * Add a custom body class to the front page.
 * Can be useful for adding styles that only 
 * appear on the front page
 */
add_filter( 'body_class', 'my_body_class' );
function my_body_class( $classes ) {
	if ( is_front_page() )
		$classes[] = 'site-front-page';
		return $classes;
}

/*
 * Remove emojis supplied by default from WordPres
 */
remove_action( 'admin_print_styles', 'print_emoji_styles' );
remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
remove_action( 'wp_print_styles', 'print_emoji_styles' );
remove_filter( 'wp_mail', 'wp_staticize_emoji_for_email' );
remove_filter( 'the_content_feed', 'wp_staticize_emoji' );
remove_filter( 'comment_text_rss', 'wp_staticize_emoji' );

/*
 * Remove inline styles for comments
 */
function xpand_remove_recent_comments_style() {
        global $wp_widget_factory;
        remove_action( 'wp_head', array( $wp_widget_factory->widgets['WP_Widget_Recent_Comments'], 'recent_comments_style' ) );
    }
add_action( 'widgets_init', 'xpand_remove_recent_comments_style' );

/*
 * Remove WordPress version number
 * Mainly to make it harder for hackers to find 
 * vulnerabilities based on WP version
 */
function xpand_remove_wp_version_strings( $src ) {
     global $wp_version;
     parse_str(parse_url($src, PHP_URL_QUERY), $query);
     if ( !empty($query['ver']) && $query['ver'] === $wp_version ) {
          $src = remove_query_arg('ver', $src);
     }
     return $src;
}
add_filter( 'script_loader_src', 'xpand_remove_wp_version_strings' );
add_filter( 'style_loader_src', 'xpand_remove_wp_version_strings' );
remove_action('wp_head', 'wp_generator');

/*
 * Add an active class to custom post types
 * So custom post types get the Bootstrap active class
 */
function xpand_custom_active_item_classes($classes = array(), $menu_item = false){            
        global $post;
        $classes[] = ($menu_item->url == get_post_type_archive_link($post->post_type)) ? 'current-menu-item active' : '';
        return $classes;
    }
add_filter( 'nav_menu_css_class', 'xpand_custom_active_item_classes', 10, 2 );

// Create pagination links instead of standard next/previous posts links
function xpand_custom_numeric_posts_nav() {

	if( is_singular() )
		return;

	global $wp_query;

	/** Stop execution if there's only 1 page */
	if( $wp_query->max_num_pages <= 1 )
		return;

	$paged = get_query_var( 'paged' ) ? absint( get_query_var( 'paged' ) ) : 1;
	$max   = intval( $wp_query->max_num_pages );

	/**	Add current page to the array */
	if ( $paged >= 1 )
		$links[] = $paged;

	/**	Add the pages around the current page to the array */
	if ( $paged >= 3 ) {
		$links[] = $paged - 1;
		$links[] = $paged - 2;
	}

	if ( ( $paged + 2 ) <= $max ) {
		$links[] = $paged + 2;
		$links[] = $paged + 1;
	}
	
	// Target the following css selectors to style your links
	echo '<ul class="post-pagination">' . "\n";

	/**	Previous Post Link */
	if ( get_previous_posts_link() )
		printf( '<li>%s</li>' . "\n", get_previous_posts_link() );

	/**	Link to first page, plus ellipses if necessary */
	if ( ! in_array( 1, $links ) ) {
		$class = 1 == $paged ? ' class="pagination-active"' : '';

		printf( '<li%s><a href="%s">%s</a></li>' . "\n", $class, esc_url( get_pagenum_link( 1 ) ), '1' );

		if ( ! in_array( 2, $links ) )
			echo '<li>…</li>';
	}

	/**	Link to current page, plus 2 pages in either direction if necessary */
	sort( $links );
	foreach ( (array) $links as $link ) {
		$class = $paged == $link ? ' class="pagination-active"' : '';
		printf( '<li%s><a href="%s">%s</a></li>' . "\n", $class, esc_url( get_pagenum_link( $link ) ), $link );
	}

	/**	Link to last page, plus ellipses if necessary */
	if ( ! in_array( $max, $links ) ) {
		if ( ! in_array( $max - 1, $links ) )
			echo '<li>…</li>' . "\n";

		$class = $paged == $max ? ' class="pagination-active"' : '';
		printf( '<li%s><a href="%s">%s</a></li>' . "\n", $class, esc_url( get_pagenum_link( $max ) ), $max );
	}

	/**	Next Post Link */
	if ( get_next_posts_link() )
		printf( '<li>%s</li>' . "\n", get_next_posts_link() );

	echo '</ul>' . "\n";

}

/* 
 * Add excerpt support to pages
 */
add_action( 'init', 'xpand_excerpts_to_pages' );
function xpand_excerpts_to_pages() {
     add_post_type_support( 'page', 'excerpt' );
}