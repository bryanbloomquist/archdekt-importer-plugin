<?php

namespace ArchidektImporter\Includes\WinLossTracker;

/**
 * Class WinLossPage
 * This class creates an admin page for tracking win/loss data
 */
class WinLossPage
{

  /**
   * Create a new Admin subpage for tracking win/loss data
   */
  public static function add_win_loss_admin_page()
  {
    add_submenu_page(
      'edit.php?post_type=deck',
      'Win/Loss Tracker',
      'Win/Loss Tracker',
      'manage_options',
      'win-loss-page',
      [self::class, 'win_loss_admin_page_content'],
      25
    );
  }

  /**
   * Create a form for user to enter win/loss data
   */
  public static function win_loss_admin_page_content()
  {
    $all_decks = get_posts([
      'post_type'   => 'deck',
      'numberposts' => -1,
      'orderby'     => 'title',
      'order'       => 'ASC'
    ]);
?>
    <div class="wrap win-loss-tracker">
      <h2>Win/Loss Tracker</h2>
      <form action="" method="post" class="win-loss-form">
        <label for="deck-id">Deck</label>
        <select name="deck-id" id="deck-id">
          <?php foreach ($all_decks as $deck) : ?>
            <option value="<?php echo $deck->ID; ?>"><?php echo $deck->post_title; ?></option>
          <?php endforeach; ?>
        </select>
        <label for="date">Date of Match</label>
        <input type="date" name="date" id="date" class="regular-text">
        <label for="number-of-people">Number of Opponents</label>
        <form-group>
          <input type="radio" name="number-of-people" id="number-of-people-1" value="1">
          <label for="number-of-people-1">One [1]</label>
          <input type="radio" name="number-of-people" id="number-of-people-2" value="2">
          <label for="number-of-people-2">Two [2]</label>
          <input type="radio" name="number-of-people" id="number-of-people-3" value="3">
          <label for="number-of-people-3">Three [3]</label>
          <input type="radio" name="number-of-people" id="number-of-people-4" value="4">
          <label for="number-of-people-4">Four [4]</label>
        </form-group>
        <label for="win-loss">Win/Loss</label>
        <form-group>
          <input type="radio" name="win-loss" id="win-loss-win" value="win">
          <label for="win-loss-win">Win</label>
          <input type="radio" name="win-loss" id="win-loss-loss" value="loss">
          <label for="win-loss-loss">Loss</label>
        </form-group>
        <input type="submit" name="submit-win-loss" id="submit-win-loss" class="button button-primary" value="Submit Win/Loss Data">
      </form>
    </div>
<?php
  }

  /**
   * This function handles the Win/Loss form submission
   */
  public static function handle_win_loss_form_submit()
  {
    if (isset($_POST['submit-win-loss'])) {
      $win_loss_data = [
        'deck_post_id' => $_POST['deck-id'],
        'date' => sanitize_text_field($_POST['date']),
        'number_of_people' => sanitize_text_field($_POST['number-of-people']),
        'win_loss' => sanitize_text_field($_POST['win-loss'])
      ];
      self::update_win_loss_data($win_loss_data);
    }
  }

  /**
   * This function will handle the updating of the win/loss data
   */
  public static function update_win_loss_data($win_loss_data)
  {
    $new_win_loss_data = [
      'wld_id' => uniqid('wld_', false),
      'date' => $win_loss_data['date'],
      'number_of_people' => $win_loss_data['number_of_people'],
      'win_loss' => $win_loss_data['win_loss']
    ];

    $existing_win_loss_data = get_post_meta($win_loss_data['deck_post_id'], 'win_loss_data', true);

    if (empty($existing_win_loss_data)) {
      $existing_win_loss_data = [];
    }

    $existing_win_loss_data[] = $new_win_loss_data;

    update_post_meta($win_loss_data['deck_post_id'], 'win_loss_data', $existing_win_loss_data);

    $url  = '/wp-admin/post.php?post=' . $win_loss_data['deck_post_id'] . '&action=edit';
    $name = get_the_title($win_loss_data['deck_post_id']);

    echo '<div class="notice notice-success is-dismissible"><p>Win Loss Data for deck "<a href="' . $url . '">' . $name . '</a>" was added successfully.</p></div>';
  }
}

add_action('admin_menu', [WinLossPage::class, 'add_win_loss_admin_page']);
add_action('admin_init', [WinLossPage::class, 'handle_win_loss_form_submit']);
