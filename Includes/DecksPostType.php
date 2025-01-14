<?php

namespace ArchidektImporter\Includes;

/**
 * Class DecksPostType
 * This class creates a custom post type for Decks
 */
class DecksPostType
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

    /**
     * Remove the "Add New" submenu item from the admin menu.
     */
    public static function remove_add_new_deck_from_admin_menu(): void
    {
        global $submenu;
        unset($submenu['edit.php?post_type=deck'][10]);
    }

    /**
     * Remove Add New Deck button from Decks page
     */
    public static function remove_add_new_deck_from_admin_head(): void
    {
        global $post_type;
        if ($post_type === 'deck') {
            echo '<style>';
            echo '#wpbody-content .wrap a.page-title-action { display: none; }';
            echo '</style>';
        }
    }

    /**
     * Display Deck Information
     */
    public static function display_deck_information($post): void
    {
        $deck_id = get_post_meta($post->ID, 'deck_id', true);
        $deck_data = get_post_meta($post->ID, 'deck_data', true);
        $sorted_deck_data = ProcessIncomingDeck::sort_cards_by_type($deck_data);
        $last_updated = $deck_data['updatedAt'];

        if ($deck_id) : ?>

            <div class="postbox">
                <div class="postbox-header">
                    <h2 class="hndle">Deck ID</h2>
                </div>
                <div class="inside"><?php echo $deck_id; ?></div>
            </div>

        <?php endif;
        if ($last_updated) : ?>

            <div class="postbox">
                <div class="postbox-header">
                    <h2 class="hndle">Last Updated</h2>
                </div>
                <div class="inside"><?php echo $last_updated; ?></div>
            </div>

        <?php endif;
        if ($sorted_deck_data) : ?>

            <div class="postbox">
                <div class="postbox-header">
                    <h2 class="hndle">Deck List</h2>
                </div>
                <div class="inside">
                    <?php foreach ($sorted_deck_data as $category => $cards) : ?>
                        <?php if (!empty($cards)) : ?>
                            <div class="deck-category">
                                <h3><?php echo $category; ?></h3>
                                <?php foreach ($cards as $card) : ?>
                                    <?php self::display_card_info($card); ?>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
            </div>

<?php endif;
    }

    /**
     * Display card information
     */
    private static function display_card_info($card): void
    {
        $quantity = esc_attr($card['quantity']);
        $name     = esc_attr($card['card']['oracleCard']['name']);
        $set      = esc_attr($card['card']['edition']['editioncode']);
        $number   = esc_attr($card['card']['collectorNumber']);
        $modifier = esc_attr($card['modifier']);
        echo '<pre>';
        echo $quantity . ' ' . $name . ' (' . strtoupper($set) . ') ' . $number;
        echo $modifier === "Foil" ? ' *F*' : '';
        echo '</pre>';
    }

    /**
     * Add columns on Decks page to display commander name and partner
     */
    public static function add_commander_column($columns): array
    {
        $date = $columns['date'];
        unset($columns['date']);
        $columns['commander'] = 'Commander';
        $columns['partner'] = 'Partner';
        $columns['date'] = $date;
        return $columns;
    }

    /**
     * Display commander name in the commander column
     */
    public static function display_commander_name($column, $post_id): void
    {
        $deck = get_post_meta($post_id);
        if ($column === 'commander') {
            echo $deck['commander'][0];
        }
        if ($column === 'partner') {
            echo $deck['partner'][0];
        }
    }

    /**
     * Make custom columns sortable
     */
    public static function make_commander_column_sortable($columns): array
    {
        $columns['commander'] = 'commander';
        $columns['partner'] = 'partner';
        return $columns;
    }

    /**
     * Order by commander column
     */
    public static function set_orderby_commander_column($query): void
    {
        if (!is_admin() || !$query->is_main_query()) {
            return;
        }
        if ($query->get('orderby') === 'commander') {
            $query->set('meta_key', 'commander');
            $query->set('orderby', 'meta_value');
        }
        if ($query->get('orderby') === 'partner') {
            $query->set('meta_key', 'partner');
            $query->set('orderby', 'meta_value');
        }
    }
}

add_action('init', [DecksPostType::class, 'register_deck_post_type']);
add_action('admin_menu', [DecksPostType::class, 'remove_add_new_deck_from_admin_menu']);
add_action('admin_head', [DecksPostType::class, 'remove_add_new_deck_from_admin_head']);
add_action('edit_form_after_title', [DecksPostType::class, 'display_deck_information']);
add_filter('manage_deck_posts_columns', [DecksPostType::class, 'add_commander_column']);
add_action('manage_deck_posts_custom_column', [DecksPostType::class, 'display_commander_name'], 10, 2);
add_filter('manage_edit-deck_sortable_columns', [DecksPostType::class, 'make_commander_column_sortable']);
add_action('pre_get_posts', [DecksPostType::class, 'set_orderby_commander_column']);
