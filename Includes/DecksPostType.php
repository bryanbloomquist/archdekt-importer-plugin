<?php

namespace ArchidektImporter\Includes;

class DecksPostType
{
  /**
   * Register a custom post type called "deck".
   */

  public static function registerDeckPostType(): void
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
      'labels'             => $labels,
      'public'             => false,
      'publicly_queryable' => false,
      'show_ui'            => true,
      'show_in_menu'       => true,
      'query_var'          => true,
      'rewrite'            => array('slug' => 'decks'),
      'capability_type'    => 'post',
      'has_archive'        => false,
      'hierarchical'       => false,
      'menu_position'      => null,
      'menu_icon'          => 'dashicons-database
      ',
      'supports'           => array('title', 'thumbnail'),
    );

    register_post_type('deck', $args);
  }

  /**
   * Remove the "Add New" submenu item from the admin menu.
   */
  public static function removeAddNewDeck(): void
  {
    global $submenu;
    unset($submenu['edit.php?post_type=deck'][10]);
  }

  /**
   * Remove Add New Deck button from Decks page
   */
  public static function removeAddNewDeckButton(): void
  {
    global $post_type;
    if ($post_type === 'deck') {
      echo '<style>
        #wpbody-content .wrap a.page-title-action {
          display: none;
        }
      </style>';
    }
  }

  /**
   * Display Deck Information
   */
  public static function displayDeckInformation($post): void
  {
    $deck_id = get_post_meta($post->ID, 'archidekt_deck_id', true);
    $deck_data = get_post_meta($post->ID, 'archidekt_deck_data', true);
    $sorted_deck_data = self::sortDeckData($deck_data);
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
                  <?php self::displayCardInfo($card); ?>
                <?php endforeach; ?>
              </div>
            <?php endif; ?>
          <?php endforeach; ?>
        </div>
      </div>
<?php endif;
  }

  /**
   * Sort cards by card type
   */
  public static function sortDeckData($deck_data)
  {
    $sorted_deck_data = [
      'Commander' => [],
      'Planeswalker' => [],
      'Battle' => [],
      'Creature' => [],
      'Sorcery' => [],
      'Instant' => [],
      'Artifact' => [],
      'Enchantment' => [],
      'Land' => [],
      'Sideboard' => [],
      'Maybeboard' => []
    ];

    foreach ($deck_data['cards'] as $card) {
      $category = $card['categories'][0];
      $sorted_deck_data[$category][] = $card;
    }

    return $sorted_deck_data;
  }

  /**
   * Display card information
   */
  private static function displayCardInfo($card): void
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
}

add_action('init', ['ArchidektImporter\Includes\DecksPostType', 'registerDeckPostType']);
add_action('admin_menu', ['ArchidektImporter\Includes\DecksPostType', 'removeAddNewDeck']);
add_action('admin_head', ['ArchidektImporter\Includes\DecksPostType', 'removeAddNewDeckButton']);
add_action('edit_form_after_title', ['ArchidektImporter\Includes\DecksPostType', 'displayDeckInformation']);
