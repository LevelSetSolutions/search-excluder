<?php
/**
 * Plugin Name: Search Excluder
 * Plugin URI: http://www.levelsetsolutions.com/
 * Description: Provides the ability to exclude posts from search results.
 * Author: LevelSet Solutions
 * Version: 0.1
 * Author URI: http://www.levelsetsolutions.com/
 */




// WordPress Hooks
// -----------------------------------------------------------------------------
add_filter('posts_where', 'se_exclude_posts_from_search'); 
add_action('add_meta_boxes', 'se_meta_box_init'); 
add_action('save_post', 'se_save_post'); 




// Functions
// -----------------------------------------------------------------------------
/**
 * Modify the search query so excluded posts are not included in the results.
 *
 * @param string current sql where clause
 * @return string modified sql where clause
 */
function se_exclude_posts_from_search( $where )
{
	// create the Posts exclusion query
	global $wpdb;
	$excludeQuery = '';

	if (is_search())
	{
		$excludedPostList = se_get_excluded_ids();
		
		if (!empty($excludedPostList))
		{
			// exclude all posts in list
			$excludeQuery = " AND ($wpdb->posts.ID NOT IN ( " . implode(',', $excludedPostList) . " )) ";
		}
	}

	return $where . $excludeQuery;
}

/** 
 * Retrieve all post ids that have been excluded.
 *
 * @return array
 */
function se_get_excluded_ids() 
{
	global $wpdb;
	$ids = array();

	// query post_meta table for all post ids that have been excluded.
	$results = $wpdb->get_results( "SELECT post_id FROM $wpdb->postmeta WHERE meta_key = 'se_exclude_from_search' AND meta_value = 'on'");
	foreach ( $results as $post_meta ) 
	{
		$ids[] = $post_meta->post_id;
	}

	return $ids;
}

/**
 * Add search exluder meta box to all post types.
 */
function se_meta_box_init()
{
	// Add this metabox to all post types
	$post_types=get_post_types('','names'); 
	foreach ($post_types as $post_type ) {
		add_meta_box( 
			'search_excluder_post_section',
			'Search Excluder',
			'se_add_inner_meta_box',
			$post_type,
			'side',
			'high'
		);
	}
}

/** 
 * Prints the html content for our Search Excluder meta box.
 */
function se_add_inner_meta_box($post)
{		
	$excluded = get_post_meta($post->ID, 'se_exclude_from_search', true);

	// Render the metabox
	include dirname(__FILE__).'/templates/meta_box.php';
}

/** 
 * Save 'se_exclude_from_search' value.
 *
 * @param string post_id
 */
function se_save_post( $post_id )
{
	// verify the nonce
	if ( !wp_verify_nonce($_POST['se_save_meta_box'], 'se_save_post') )
	{
		return;
	}

	// verify if this is an auto save routine. 
	// If it is our form has not been submitted, so we dont want to do anything 
	if(defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) 
	{ 
		return;
	}

	// save the meta data
	update_post_meta( $post_id, 'se_exclude_from_search', $_POST['se_exclude_from_search'] );
}
