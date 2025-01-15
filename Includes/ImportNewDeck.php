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
            <h3>Add A New Deck</h3>
            <p>Enter deck ID from Archidekt to add a new deck or to update an existing deck.</p>
            <form action="" method="post">
                <label for="deck-id">Deck ID</label>
                <input type="text" name="deck-id" id="deck-id" class="regular-text">
                <input type="submit" name="fetch-deck-data" id="fetch-deck-data" class="button button-primary" value="Fetch Deck Data">
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
     * Check if the deck ID is unique and fetch deck data
     */
    public static function check_for_unique_id()
    {
        if (isset($_POST['fetch-deck-data'])) {
            $deck_id = sanitize_text_field($_POST['deck-id']);

            $deck_post = get_posts(array(
                'post_type' => 'deck',
                'meta_query' => array(['key' => 'deck_id', 'value' => $deck_id]),
            ));

            $deck_data = self::import_deck_data($deck_id);

            if ($deck_post) {
                $deck_post_id = $deck_post[0]->ID;
                echo '<div class="notice notice-success is-dismissible"><p>Deck "' . $deck_data['name'] . '" data has been updated successfully.</p></div>';
                self::update_deck_post_meta($deck_id, $deck_data, $deck_post_id);
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
     * Trigger to update all deck data
     */
    public static function update_all_deck_button_trigger()
    {
        if (isset($_POST['fetch-all-decks-data'])) {
            $deck_posts = get_posts(array(
                'post_type' => 'deck',
                'posts_per_page' => -1
            ));

            foreach ($deck_posts as $deck) {
                $deck_post_id = $deck->ID;
                $deck_id = get_post_meta($deck_post_id, 'deck_id', true);
                if ($deck_id) {
                    $deck_data = self::import_deck_data($deck_id);
                    if ($deck_data) {
                        self::update_deck_post_meta($deck_id, $deck_data, $deck_post_id);
                        echo '<div class="notice notice-success is-dismissible"><p>Deck "' . $deck_data['name'] . '" data has been updated successfully.</p></div>';
                    } else {
                        echo '<div class="notice notice-error is-dismissible"><p>The deck_data for "' . $deck->post_title . '" could not be found.</p></div>';
                    }
                } else {
                    echo '<div class="notice notice-error is-dismissible"><p>The deck_id for "' . $deck->post_title . '" could not be found.</p></div>';
                }
            }
            // echo '<div class="notice notice-success is-dismissible"><p>All decks data has been updated successfully.</p></div>';
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
        $deck_post_id = wp_insert_post(array(
            'post_title' => $deck_data['name'],
            'post_type' => 'deck',
            'post_status' => 'publish'
        ));

        self::update_deck_post_meta($deck_id, $deck_data, $deck_post_id);
    }

    /**
     * Update Deck Post Meta
     */
    public static function update_deck_post_meta($deck_id, $deck_data, $deck_post_id)
    {
        $sorted_deck    = ProcessIncomingDeck::sort_cards_by_type($deck_data);
        $deck_name      = $deck_data['name'];
        $deck_url       = "https://archidekt.com/decks/{$deck_id}";
        $commander      = $sorted_deck['Commander'][0]['card']['oracleCard']['name'];
        $partner        = $sorted_deck['Commander'][1]['card']['oracleCard']['name'];
        $identity       = ProcessIncomingDeck::get_color_identity($sorted_deck['Commander']);
        $misc_values    = ProcessIncomingDeck::calculate_misc_values($deck_data);
        $salt_sum       = $misc_values['salt_sum'];
        $deck_price     = "$" . number_format($misc_values['price'], 2);
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

        wp_update_post(['ID' => $deck_post_id, 'post_title' => $deck_name]);
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
}

add_action('admin_menu', ['ArchidektImporter\Includes\ImportNewDeck', 'add_new_deck_page']);
add_action('admin_init', ['ArchidektImporter\Includes\ImportNewDeck', 'check_for_unique_id']);
add_action('admin_init', ['ArchidektImporter\Includes\ImportNewDeck', 'update_all_deck_button_trigger']);
