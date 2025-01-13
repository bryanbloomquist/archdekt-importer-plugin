<?php

namespace ArchidektImporter\Includes;

class ImportNewDeck
{
  /**
   * Create a new Admin page for adding a new deck
   */
  public static function addNewDeckPage()
  {
    add_menu_page(
      'Add/Update Deck',
      'Add/Update Deck',
      'manage_options',
      'fetch-deck-data',
      [self::class, 'addNewDeckPageContent'],
      'dashicons-insert',
      25
    );
  }

  /**
   * Create a form for user to enter deck ID and fetch deck data
   */
  public static function addNewDeckPageContent()
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
  public static function checkUniqueDeckId()
  {
    if (isset($_POST['fetch-deck-data'])) {
      $deck_id = sanitize_text_field($_POST['deck-id']);

      $deck_id_exists = get_posts(array(
        'post_type' => 'deck',
        'meta_query' => array(['key' => 'archidekt_deck_id', 'value' => $deck_id]),
      ));

      $deck_data = self::importDeckData($deck_id);

      if ($deck_id_exists) {
        echo '<div class="notice notice-error is-dismissible"><p>Deck ID already exists. Please enter a unique Deck ID.</p></div>';

        self::checkForUpdates($deck_id, $deck_data);
      } else {
        if ($deck_data) {
          self::createNewDeckPost($deck_id, $deck_data);
          echo '<div class="notice notice-success is-dismissible"><p>Deck data fetched successfully.</p></div>';
        } else {
          echo '<div class="notice notice-error is-dismissible"><p>Deck data could not be fetched. Please check the Deck ID and try again.</p></div>';
        }
      }
    }
  }

  /**
   * Fetch deck data from Archidekt API
   */

  public static function importDeckData($deck_id)
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

  public static function createNewDeckPost($deck_id, $deck_data)
  {
    $deck_post_id = wp_insert_post(array(
      'post_title' => $deck_data['name'],
      'post_type' => 'deck',
      'post_status' => 'publish'
    ));
    update_post_meta($deck_post_id, 'archidekt_deck_id', $deck_id);
    update_post_meta($deck_post_id, 'archidekt_deck_data', $deck_data);
  }


  /**
   * Update existing Deck post if Deck ID already exists
   */

  public static function checkForUpdates($deck_id, $deck_data)
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

add_action('admin_menu', ['ArchidektImporter\Includes\ImportNewDeck', 'addNewDeckPage']);
add_action('admin_init', ['ArchidektImporter\Includes\ImportNewDeck', 'checkUniqueDeckId']);
