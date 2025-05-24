<?php

namespace ArchidektImporter\Includes\DecksPostType;

use DateTime;
use ArchidektImporter\Includes\DebugLogger\Logger as Logger;

/**
 * Class DecksPostPage
 * This class displays the Decks post type page
 */
class DecksPostPage
{

	/**
	 * Add Meta Box to Decks page sidebar to display archidekt deck id
	 */
	public static function add_deck_info_sidebar_meta_box(): void
	{
		add_meta_box(
			'deck-id-meta-box',
			'Archidekt Informations',
			[self::class, 'display_deck_info_sidebar_meta_box'],
			'deck',
			'normal',
			'default',
		);
	}

	/**
	 * Display Deck Information Meta Box
	 */
	public static function display_deck_info_sidebar_meta_box($post): void
	{
		$deck_id = get_post_meta($post->ID, 'deck_id', true);
		$deck_data = get_post_meta($post->ID, 'deck_data', true);
		$deck_url = get_post_meta($post->ID, 'deck_url', true);
		$deck_name = $deck_data['name'];
		$last_updated = new DateTime($deck_data['updatedAt']);
		$last_updated = $last_updated->format('F j, Y, g:i a');
?>
		<p><strong>Deck Name:</strong> <?php echo $deck_name; ?></p>
		<p><strong>Deck ID:</strong> <?php echo $deck_id; ?></p>
		<p><strong>Updated:</strong> <?php echo $last_updated; ?></p>
		<p><a href="<?php echo $deck_url; ?>" target="_blank" rel="noreferrer noopener" class="button button-primary">View Deck in Archidekt <span class="dashicons dashicons-external"></span></a></p>
	<?php
	}

	public static function display_win_loss_table($post)
	{
		$postID = $post->ID;
		$win_loss_data = get_post_meta($postID, 'win_loss_data', true);
		$win_loss_data = $win_loss_data ?: [];
		usort($win_loss_data, function ($a, $b) {
			return strtotime($b['date']) - strtotime($a['date']);
		});
	?>
		<div class="postbox win-loss-container">
			<h2 class="hndle">Win/Loss Record</h2>
			<table class="win-loss-table widefat fixed">
				<thead>
					<tr>
						<th>Date</th>
						<th>Opponents</th>
						<th>Win/Loss</th>
						<th class="column-action">Action</th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ($win_loss_data as $data) : ?>
						<tr>
							<td><?php echo $data['date']; ?></td>
							<td><?php echo $data['number_of_people']; ?></td>
							<td class="<?php echo $data['win_loss']; ?>"><?php echo $data['win_loss']; ?></td>
							<td class="column-action">
								<form method="post">
									<input type="hidden" name="win-loss-id" value="<?php echo $data['wld_id']; ?>">
									<input type="hidden" name="post-id" value="<?php echo $postID; ?>">
									<input type="submit" name="delete-win-loss" value="Remove Row" class="button action-button">
								</form>
							</td>
						</tr>
					<?php endforeach; ?>
					<tr>
						<form action="" method="post" class="win-loss-form">
							<td>
								<input type="hidden" name="deck-id" id="deck-id" value="<?php echo $postID; ?>">
								<input type="date" name="date" id="date" aria-label="Date of Match">
							</td>
							<td>
								<input type="number" name="number-of-people" id="number-of-people" aria-label="Number of Opponents" min="1" max="4" step="1" value="2">
							</td>
							<td>
								<form-group>
									<input type="radio" name="win-loss" id="win-loss-win" value="win" aria-label="Win/Loss">
									<label for="win-loss-win">Win</label>
									<input type="radio" name="win-loss" id="win-loss-loss" value="loss" aria-label="Win/Loss">
									<label for="win-loss-loss">Loss</label>
								</form-group>
							</td>
							<td class="column-action">
								<input type="submit" name="submit-win-loss" id="submit-win-loss" class="button action-button" value="Submit Win/Loss Data">
							</td>
						</form>
					</tr>
				</tbody>
			</table>
		</div>
<?php
	}

	public static function handle_remove_row($post)
	{
		if (isset($_POST['delete-win-loss'])) {
			$postID = $_POST['post-id'];
			$wld_ID = $_POST['win-loss-id'];
			$win_loss_data = get_post_meta($postID, 'win_loss_data', true);
			$win_loss_data = array_filter($win_loss_data, function ($data) use ($wld_ID) {
				return $data['wld_id'] !== $wld_ID;
			});
			Logger::write('Win/Loss Data (removal)', $win_loss_data);
			update_post_meta($postID, 'win_loss_data', $win_loss_data);
			wp_redirect(admin_url('post.php?post=' . $postID . '&action=edit'));
			exit;
		}
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
			if (empty($_POST['date']) || empty($_POST['number-of-people']) || empty($_POST['win-loss'])) {
				echo '<div class="error"><p>Please fill in all fields.</p></div>';
				return;
			}
			Logger::write('Win/Loss Data (add)', $win_loss_data);
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

		wp_redirect(admin_url('post.php?post=' . $win_loss_data['deck_post_id'] . '&action=edit'));
		exit;
	}
}

add_action('admin_init', [DecksPostPage::class, 'add_deck_info_sidebar_meta_box']);
add_action('edit_form_after_title', [DecksPostPage::class, 'display_win_loss_table']);
add_action('admin_init', [DecksPostPage::class, 'handle_remove_row']);
add_action('admin_init', [DecksPostPage::class, 'handle_win_loss_form_submit']);
