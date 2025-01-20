<?php

namespace ArchidektImporter\Includes\ImportDeckData;

use ArchidektImporter\Includes\ImportDeckData\ImportArchidektData as ImportArchidektData;
use ArchidektImporter\Includes\DecksPostType\UpdateDecksPostMeta as UpdateDecksPostMeta;

/**
 * Class ImportDeckDataAdminPage
 * This class creates an admin page for importing a new deck
 */
class ImportDeckDataAdminPage
{

    /**
     * Create a new Admin page for importing a new deck
     */
    public static function add_import_deck_admin_page()
    {
        add_submenu_page(
            'edit.php?post_type=deck',
            'Import Deck',
            'Import Deck',
            'manage_options',
            'import-deck-page',
            [self::class, 'import_deck_admin_page_content'],
            'dashicons-insert',
            25
        );
    }

    /**
     * Create a form for user to enter deck ID and fetch deck data
     */
    public static function import_deck_admin_page_content()
    {
?>
        <div class="wrap">
            <h2>Add/Update Deck</h2>
            <h3>Import New Deck</h3>
            <p>Enter deck ID from Archidekt to add a new deck or to update an existing deck.</p>
            <form action="" method="post">
                <label for="deck-id">Deck ID</label>
                <input type="text" name="deck-id" id="deck-id" class="regular-text">
                <input type="submit" name="import-deck-data" id="import-deck-data" class="button button-primary" value="Fetch Deck Data">
            </form>
            <h3>Update All Decks</h3>
            <p>Pull all decks data from Archidekt to update table.</p>
            <form action="" method="post">
                <input type="submit" name="fetch-all-decks-data" id="fetch-all-decks-data" class="button button-primary" value="Update All Decks">
            </form>
        </div>
<?php
    }

    /**
     * This function handles the Add New Deck form submission
     */
    public static function handle_form_submit()
    {
        if (isset($_POST['import-deck-data'])) {
            $deck_id = sanitize_text_field($_POST['deck-id']);
            $existing_deck = get_posts([
                'post_type' => 'deck',
                'meta_query' => [['key' => 'deck_id', 'value' => $deck_id]]
            ]);
            $deck_data = ImportArchidektData::import_deck_data($deck_id);
            if (empty($existing_deck)) {
                UpdateDecksPostMeta::create_new_deck_post($deck_id, $deck_data);
                echo '<div class="notice notice-success is-dismissible"><p>Deck data for "' . $deck_data['name'] . '" has been imported successfully.</p></div>';
            } else {
                $deck_post_id = $existing_deck[0]->ID;
                UpdateDecksPostMeta::update_deck_post_meta($deck_id, $deck_data, $deck_post_id);
                echo '<div class="notice notice-success is-dismissible"><p>Deck data for "' . $deck_data['name'] . '" has been updated successfully.</p></div>';
            }
        }
    }

    /**
     * This function handles the Update All Decks form submission
     */
    public static function handle_update_all_decks_button()
    {
        if (isset($_POST['fetch-all-decks-data'])) {
            $deck_posts = get_posts([
                'post_type' => 'deck',
                'numberposts' => -1
            ]);
            $updated_decks = '';
            foreach ($deck_posts as $deck) {
                $deck_post_id = $deck->ID;
                $deck_id = get_post_meta($deck_post_id, 'deck_id', true);
                if ($deck_id) {
                    $deck_data = ImportArchidektData::import_deck_data(($deck_id));
                    if ($deck_data) {
                        UpdateDecksPostMeta::update_deck_post_meta($deck_id, $deck_data, $deck_post_id);
                        $updated_decks .= '<li>' . $deck->post_title . '</li>';
                    } else {
                        echo '<div class="notice notice-error is-dismissible"><p>The deck data for ' . $deck->post_title . ' could not be imported from Archidekt at this time.</p></div>';
                    }
                } else {
                    echo '<div class="notice notice-error is-dismissible"><p>Deck ID for ' . $deck->post_title . ' could not be found.</p></div>';
                }
            }
            if ($updated_decks) {
                echo '<div class="notice notice-success is-dismissible"><p>The following decks have been updated successfully:<ul>' . $updated_decks . '</ul></p></div>';
            }
        }
    }
}

add_action('admin_menu', [ImportDeckDataAdminPage::class, 'add_import_deck_admin_page']);
add_action('admin_init', [ImportDeckDataAdminPage::class, 'handle_form_submit']);
add_action('admin_init', [ImportDeckDataAdminPage::class, 'handle_update_all_decks_button']);
