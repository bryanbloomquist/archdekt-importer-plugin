<?php

namespace ArchidektImporter\Includes\DecksPostType;

/**
 * Class DecksAdminPage
 * This class displays the admin page for the Decks post type page
 */
class DecksAdminPage
{

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
     * Add columns on Decks Admin page to display deck commander and partner
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
     * Display commander and partner names in the correct columns
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

add_action('admin_menu', [DecksAdminPage::class, 'remove_add_new_deck_from_admin_menu']);
add_action('admin_head', [DecksAdminPage::class, 'remove_add_new_deck_from_admin_head']);
add_filter('manage_deck_posts_columns', [DecksAdminPage::class, 'add_commander_column']);
add_action('manage_deck_posts_custom_column', [DecksAdminPage::class, 'display_commander_name'], 10, 2);
add_filter('manage_edit-deck_sortable_columns', [DecksAdminPage::class, 'make_commander_column_sortable']);
add_action('pre_get_posts', [DecksAdminPage::class, 'set_orderby_commander_column']);
