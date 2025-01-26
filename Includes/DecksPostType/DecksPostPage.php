<?php

namespace ArchidektImporter\Includes\DecksPostType;

use DateTime;

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
						<th>Action</th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ($win_loss_data as $data) : ?>
						<tr>
							<td><?php echo $data['date']; ?></td>
							<td><?php echo $data['number_of_people']; ?></td>
							<td class="<?php echo $data['win_loss']; ?>"><?php echo $data['win_loss']; ?></td>
							<td>
								<form method="post">
									<input type="hidden" name="win-loss-id" value="<?php echo $data['wld_id']; ?>">
									<input type="hidden" name="post-id" value="<?php echo $postID; ?>">
									<input type="submit" name="delete-win-loss" value="Remove" class="button button-secondary">
								</form>
							</td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		</div>
<?php
	}

	public static function handle_remove_row()
	{
		if (isset($_POST['delete-win-loss'])) {
			$postID = intval($_POST['post-id']);
			$wld_ID = $_POST['win-loss-id'];
			$win_loss_data = get_post_meta($postID, 'win_loss_data', true);
			$win_loss_data = array_filter($win_loss_data, function ($data) use ($wld_ID) {
				return $data['wld_id'] !== $wld_ID;
			});
			update_post_meta($postID, 'win_loss_data', $win_loss_data);
		}
	}
}

add_action('admin_init', [DecksPostPage::class, 'add_deck_info_sidebar_meta_box']);
add_action('edit_form_after_title', [DecksPostPage::class, 'display_win_loss_table']);
add_action('admin_init', [DecksPostPage::class, 'handle_remove_row']);
