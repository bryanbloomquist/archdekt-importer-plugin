<?php

namespace ArchidektImporter\Includes\DecksPostType;

/**
 * Class CreateDecksPostType
 * This class creates a custom post type for Decks
 */
class CreateDecksPostType
{

	/**
	 * Register a custom post type called "deck".
	 */
	public static function register_deck_post_type(): void
	{
		$labels = array(
			'name'                  => _x('Decks', 'Post type general name', 'textdomain'),
			'singular_name'         => _x('Deck', 'Post type singular name', 'textdomain'),
			'menu_name'             => _x('Decks', 'Admin Menu text', 'textdomain'),
			'name_admin_bar'        => _x('Deck', 'Add New on Toolbar', 'textdomain'),
			'add_new'               => __('Add New', 'textdomain'),
			'add_new_item'          => __('Add New Deck', 'textdomain'),
			'new_item'              => __('New Deck', 'textdomain'),
			'edit_item'             => __('Edit Deck', 'textdomain'),
			'view_item'             => __('View Deck', 'textdomain'),
			'all_items'             => __('All Decks', 'textdomain'),
			'search_items'          => __('Search Decks', 'textdomain'),
			'parent_item_colon'     => __('Parent Decks:', 'textdomain'),
			'not_found'             => __('No Decks found.', 'textdomain'),
			'not_found_in_trash'    => __('No Decks found in Trash.', 'textdomain'),
			'featured_image'        => _x('Deck Commander Image', 'Overrides the “Featured Image” phrase for this post type. Added in 4.3', 'textdomain'),
			'set_featured_image'    => _x('Set commander image', 'Overrides the “Set featured image” phrase for this post type. Added in 4.3', 'textdomain'),
			'remove_featured_image' => _x('Remove commander image', 'Overrides the “Remove featured image” phrase for this post type. Added in 4.3', 'textdomain'),
			'use_featured_image'    => _x('Use as commander image', 'Overrides the “Use as featured image” phrase for this post type. Added in 4.3', 'textdomain'),
			'archives'              => _x('Deck archives', 'The post type archive label used in nav menus. Default “Post Archives”. Added in 4.4', 'textdomain'),
			'insert_into_item'      => _x('Insert into Deck', 'Overrides the “Insert into post”/”Insert into page” phrase (used when inserting media into a post). Added in 4.4', 'textdomain'),
			'uploaded_to_this_item' => _x('Uploaded to this Deck', 'Overrides the “Uploaded to this post”/”Uploaded to this page” phrase (used when viewing media attached to a post). Added in 4.4', 'textdomain'),
			'filter_items_list'     => _x('Filter Decks list', 'Screen reader text for the filter links heading on the post type listing screen. Default “Filter posts list”/”Filter pages list”. Added in 4.4', 'textdomain'),
			'items_list_navigation' => _x('Decks list navigation', 'Screen reader text for the pagination heading on the post type listing screen. Default “Posts list navigation”/”Pages list navigation”. Added in 4.4', 'textdomain'),
			'items_list'            => _x('Decks list', 'Screen reader text for the items list heading on the post type listing screen. Default “Posts list”/”Pages list”. Added in 4.4', 'textdomain'),
		);

		$args = array(
			'labels'                => $labels,
			'public'                => false,
			'publicly_queryable'    => false,
			'show_ui'               => true,
			'show_in_menu'          => true,
			'query_var'             => true,
			'rewrite'               => array('slug' => 'decks'),
			'capability_type'       => 'post',
			'has_archive'           => false,
			'hierarchical'          => false,
			'menu_position'         => null,
			'menu_icon'             => 'dashicons-database',
			'supports'              => array('title', 'thumbnail'),
		);

		register_post_type('deck', $args);
	}
}

add_action('init', [CreateDecksPostType::class, 'register_deck_post_type']);
