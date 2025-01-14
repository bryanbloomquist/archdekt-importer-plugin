<?php

namespace ArchidektImporter\Includes;

/**
 * Class ImportNewDeck
 * This class creates an admin page for adding a new deck
 */
class ImportNewDeck
{
    /**
     * Create a new Admin page for adding a new deck
     */
    public static function add_new_deck_page()
    {
        add_menu_page(
            'Add/Update Deck',
            'Add/Update Deck',
            'manage_options',
            'fetch-deck-data',
            [self::class, 'add_new_deck_page_content'],
            'dashicons-insert',
            25
        );
    }

    /**
     * Create a form for user to enter deck ID and fetch deck data
     */
    public static function add_new_deck_page_content()
    {
?>
        <div class="wrap">
            <h2>Add/Update Deck</h2>
            <p>Enter deck ID from Archidekt to add a new deck or to update an existing deck.</p>
            <form action="" method="post">
                <label for="deck-id">Deck ID</label>
                <input type="text" name="deck-id" id="deck-id" class="regular-text">
                <input type="submit" name="fetch-deck-data" id="fetch-deck-data" class="button button-primary" value="Fetch Deck Data">
            </form>
        </div>
<?php
    }

    /**
     * Check if the deck ID is unique and fetch deck data
     */
    public static function check_for_unique_id()
    {
        if (isset($_POST['fetch-deck-data'])) {
            $deck_id = sanitize_text_field($_POST['deck-id']);

            $deck_id_exists = get_posts(array(
                'post_type' => 'deck',
                'meta_query' => array(['key' => 'archidekt_deck_id', 'value' => $deck_id]),
            ));

            $deck_data = self::import_deck_data($deck_id);

            if ($deck_id_exists) {
                echo '<div class="notice notice-error is-dismissible"><p>Deck ID already exists. Please enter a unique Deck ID.</p></div>';

                self::check_for_updates($deck_id, $deck_data);
            } else {
                if ($deck_data) {
                    self::create_new_deck_post($deck_id, $deck_data);
                    echo '<div class="notice notice-success is-dismissible"><p>Deck "' . $deck_data['name'] . '" data fetched successfully.</p></div>';
                } else {
                    echo '<div class="notice notice-error is-dismissible"><p>Deck data could not be fetched. Please check the Deck ID and try again.</p></div>';
                }
            }
        }
    }

    /**
     * Fetch deck data from Archidekt API
     */
    public static function import_deck_data($deck_id)
    {
        $api_url = "https://archidekt.com/api/decks/" . $deck_id . "/";

        $api_args = array(
            'headers' => array(
                'Accept' => 'application/json'
            )
        );

        $response = wp_remote_get($api_url, $api_args);

        if (is_wp_error($response)) {
            return false;
        }

        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);

        return $data;
    }

    /**
     * Create a new Deck post if the deck ID is unique
     */
    public static function create_new_deck_post($deck_id, $deck_data)
    {
        $sorted_deck    = ProcessIncomingDeck::sort_cards_by_type($deck_data);
        $deck_url       = "https://archidekt.com/decks/{$deck_id}";
        $commander      = $sorted_deck['Commander'][0]['card']['oracleCard']['name'];
        $partner        = $sorted_deck['Commander'][1]['card']['oracleCard']['name'];
        $identity       = ProcessIncomingDeck::get_color_identity($sorted_deck['Commander']);
        $misc_values    = ProcessIncomingDeck::calculate_misc_values($deck_data);
        $salt_sum       = $misc_values['salt_sum'];
        $deck_price     = number_format($misc_values['price'], 2);
        $total_mana     = $misc_values['mana_value'];
        $battles        = ProcessIncomingDeck::count_card_type($sorted_deck['Battle']);
        $planeswalkers  = ProcessIncomingDeck::count_card_type($sorted_deck['Planeswalker']);
        $creatures      = ProcessIncomingDeck::count_card_type($sorted_deck['Creature']);
        $sorceries      = ProcessIncomingDeck::count_card_type($sorted_deck['Sorcery']);
        $instants       = ProcessIncomingDeck::count_card_type($sorted_deck['Instant']);
        $artifacts      = ProcessIncomingDeck::count_card_type($sorted_deck['Artifact']);
        $enchantments   = ProcessIncomingDeck::count_card_type($sorted_deck['Enchantment']);
        $lands          = ProcessIncomingDeck::count_card_type($sorted_deck['Land']);
        $non_lands      = ($partner ? 98 : 99) - $lands;
        $average_mana   = number_format($total_mana / $non_lands, 2);

        $deck_post_id = wp_insert_post(array(
            'post_title' => $deck_data['name'],
            'post_type' => 'deck',
            'post_status' => 'publish'
        ));

        update_post_meta($deck_post_id, 'deck_id', $deck_id);
        update_post_meta($deck_post_id, 'deck_name', $deck_data['name']);
        update_post_meta($deck_post_id, 'deck_data', $deck_data);
        update_post_meta($deck_post_id, 'deck_url', $deck_url);
        update_post_meta($deck_post_id, 'commander', $commander);
        update_post_meta($deck_post_id, 'partner', $partner);
        update_post_meta($deck_post_id, 'identity', $identity);
        update_post_meta($deck_post_id, 'salt_sum', $salt_sum);
        update_post_meta($deck_post_id, 'deck_price', $deck_price);
        update_post_meta($deck_post_id, 'total_mana', $total_mana);
        update_post_meta($deck_post_id, 'average_mana', $average_mana);
        update_post_meta($deck_post_id, 'battles', $battles);
        update_post_meta($deck_post_id, 'planeswalkers', $planeswalkers);
        update_post_meta($deck_post_id, 'creatures', $creatures);
        update_post_meta($deck_post_id, 'sorceries', $sorceries);
        update_post_meta($deck_post_id, 'instants', $instants);
        update_post_meta($deck_post_id, 'artifacts', $artifacts);
        update_post_meta($deck_post_id, 'enchantments', $enchantments);
        update_post_meta($deck_post_id, 'lands', $lands);
    }

    /**
     * Update existing Deck post if Deck ID already exists
     */
    public static function check_for_updates($deck_id, $deck_data)
    {
        $deck_post = get_posts(array(
            'post_type' => 'deck',
            'meta_query' => array(['key' => 'archidekt_deck_id', 'value' => $deck_id]),
        ));
        $deck_post_id = $deck_post[0]->ID;
        $existing_deck_data = get_post_meta($deck_post_id, 'archidekt_deck_data', true);
        if ($deck_data['updatedAt'] > $existing_deck_data['updatedAt']) {
            update_post_meta($deck_post_id, 'archidekt_deck_data', $deck_data);
            if ($deck_data['name'] !== $existing_deck_data['name']) {
                wp_update_post(array(
                    'ID' => $deck_post_id,
                    'post_title' => $deck_data['name']
                ));
            }
            echo '<div class="notice notice-success is-dismissible"><p>Deck data updated successfully.</p></div>';
        }
    }
}

add_action('admin_menu', ['ArchidektImporter\Includes\ImportNewDeck', 'add_new_deck_page']);
add_action('admin_init', ['ArchidektImporter\Includes\ImportNewDeck', 'check_for_unique_id']);
