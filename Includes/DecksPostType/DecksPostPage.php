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
		$has_win = get_post_meta($postID, 'has_win', true);
		Logger::write('Has win', $has_win);
		$win_loss_data = $win_loss_data ?: [];
		usort($win_loss_data, function ($a, $b) {
			return strtotime($b['date']) - strtotime($a['date']);
		});
	?>
		<div class="postbox win-loss-container">
			<h2 class="hndle">Win/Loss Record</h2>
			<table class="win-loss-table fixed">
				<thead>
					<tr>
						<th class="table-date">Date</th>
						<th class="table-opponents">Opponents</th>
						<th class="table-win-loss">Win/Loss</th>
						<th class="column-action">Action</th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ($win_loss_data as $data) : ?>
						<tr>
							<td class="table-date"><?php echo $data['date']; ?></td>
							<td class="table-opponents"><?php echo $data['number_of_people']; ?></td>
							<td class="table-win-loss <?php echo $data['win_loss']; ?>"><?php echo $data['win_loss']; ?></td>
							<td class="column-action">
								<form method="post">
									<input type="hidden" name="win-loss-id" value="<?php echo $data['wld_id']; ?>">
									<input type="hidden" name="post-id" value="<?php echo $postID; ?>">
									<button type="submit" name="delete-win-loss" value="Remove Record" class="button action-button">
										<span class="dashicons dashicons-arrow-left"></span> Remove Record
									</button>
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
								<button type="submit" name="submit-win-loss" id="submit-win-loss" class="button action-button">
									Submit Record <span class="dashicons dashicons-arrow-right"></span>
								</button>
							</td>
						</form>
					</tr>
				</tbody>
			</table>
		</div>
	<?php
	}

	/**
	 * This function handles the removal of a win/loss row
	 */
	public static function handle_remove_row()
	{
		if (isset($_POST['delete-win-loss'])) {
			$postID = $_POST['post-id'];
			$wld_ID = $_POST['win-loss-id'];
			$win_loss_data = get_post_meta($postID, 'win_loss_data', true);
			$win_loss_data = array_filter($win_loss_data, function ($data) use ($wld_ID) {
				return $data['wld_id'] !== $wld_ID;
			});
			$has_win = "Not Yet";
			foreach ($win_loss_data as $data) {
				if ($data['win_loss'] === 'win') {
					$has_win = "Winner";
					break;
				} else {
					$has_win = "Not Yet";
				}
			}
			update_post_meta($postID, 'win_loss_data', $win_loss_data);
			update_post_meta($postID, 'has_win', $has_win);
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

		// check to see if any of the $existing_win_loss_data entries have a win-loss value of 'win'
		$has_win = "Not Yet";
		foreach ($existing_win_loss_data as $data) {
			if ($data['win_loss'] === 'win') {
				$has_win = "Winner";
				break;
			} else {
				$has_win = "Not Yet";
			}
		}
		update_post_meta($win_loss_data['deck_post_id'], 'win_loss_data', $existing_win_loss_data);
		update_post_meta($win_loss_data['deck_post_id'], 'has_win', $has_win);
		wp_redirect(admin_url('post.php?post=' . $win_loss_data['deck_post_id'] . '&action=edit'));
		exit;
	}

	public static function display_power_ranking($post)
	{
		$postID = $post->ID;
		$power_ranking = get_post_meta($postID, 'power_ranking', true);
		if (empty($power_ranking)) {
			return;
		}
	?>
		<div class="postbox power-ranking-container">
			<h2 class="hndle power-ranking-header">Power Ranking <span class="power-ranking-value"><?php echo $power_ranking['total']; ?></span></h2>
			<p>
				<strong>Consistency:</strong>
				<?php if ($power_ranking['consistency'] == 2) {
					echo "High: Multiple tutors, lots of card draw, strong mana fixing. The deck can reliably enact its game plan every game.";
				} elseif ($power_ranking['consistency'] == 1) {
					echo "Medium: Some card selection and draw, but limited tutoring and may stumble if key cards are missing.";
				} else {
					echo "Low: Inconsistent draws, few ways to find specific cards, often \"does nothing\" in some games.";
				} ?>
			</p>
			<p>
				<strong>Speed:</strong>
				<?php if ($power_ranking['speed'] == 2) {
					echo "Fast: Turn 1-2 mana rocks, dorks, and fast ramp spells. Can threaten wins or strong boards by turn 4-5.";
				} elseif ($power_ranking['speed'] == 1) {
					echo "Medium: Ramps in the early turns but hits its stride around turns 5-7.";
				} else {
					echo "Slow: Most plays start around turn 4+. Ramp is minimal or clunky.";
				} ?>
			</p>
			<p>
				<strong>Resilience:</strong>
				<?php if ($power_ranking['resilience'] == 2) {
					echo "High: Has recursion, indestructible effects, hexproof, counterspells, and redundancy.";
				} elseif ($power_ranking['resilience'] == 1) {
					echo "Medium: Can recover from 1-2 board wipes or interaction.";
				} else {
					echo "Low: A single wipe or counterspell can dismantle the game plan.";
				} ?>
			</p>
			<p>
				<strong>Closing Power:</strong>
				<?php if ($power_ranking['closing_power'] == 2) {
					echo "Fast + Efficient: Infinite combos, two-card combos, commander damage kills, or large overrun-style wins.";
				} elseif ($power_ranking['closing_power'] == 1) {
					echo "Midrange: Wins through accumulated value or synergy engines that eventually overpower opponents.";
				} else {
					echo "Weak: Relies on janky or inconsistent win, may struggle to actually close games.";
				} ?>
			</p>
			<p>
				<strong>Interaction:</strong>
				<?php if ($power_ranking['interaction'] == 2) {
					echo "High: Multiple pieces of removal, counterspells, graveyard hate, etc.";
				} elseif ($power_ranking['interaction'] == 1) {
					echo "Medium: Some answers but may struggle against faster or combo-heavy decks.";
				} else {
					echo "Low: Minimal ability to stop opponents' strategies.";
				} ?>
			</p>
		</div>
	<?php
	}

	public static function display_power_ranking_form($post)
	{
		$postID = $post->ID;
	?>
		<div class="postbox power-ranking-container">
			<h2 class="hndle">Calculate Power Ranking</h2>
			<p>This is in no way an accurate way to determine your decks power</p>
			<form method="post" action="">
				<table class="power-ranking-table fixed">
					<tr>
						<th>Consistency</th>
						<td>
							<input type="radio" value="3" name="consistency" id="consistency-3">
							<label for="consistency-3"><strong>High:</strong> Multiple tutors, lots of card draw, strong mana fixing. The deck can reliably enact its game plan every game.</label>
						</td>
						<td>
							<input type="radio" value="2" name="consistency" id="consistency-2">
							<label for="consistency-2"><strong>Medium:</strong> Some card selection and draw, but limited tutoring and may stumble if key cards are missing.</label>
						</td>
						<td>
							<input type="radio" value="1" name="consistency" id="consistency-1">
							<label for="consistency-1"><strong>Low:</strong> Inconsistent draws, few ways to find specific cards, often "does nothing" in some games.</label>
						</td>
					</tr>
					<tr>
						<th>Speed</th>
						<td>
							<input type="radio" value="3" name="speed" id="speed-3">
							<label for="speed-3"><strong>Fast:</strong> Turn 1-2 mana rocks, dorks, and fast ramp spells. Can threaten wins or strong boards by turn 4-5.</label>
						</td>
						<td>
							<input type="radio" value="2" name="speed" id="speed-2">
							<label for="speed-2"><strong>Medium:</strong> Ramps in the early turns but hits its stride around turns 5-7.</label>
						</td>
						<td>
							<input type="radio" value="1" name="speed" id="speed-1">
							<label for="speed-1"><strong>Slow:</strong> Most plays start around turn 4+. Ramp is minimal or clunky.</label>
						</td>
					</tr>
					<tr>
						<th>Resilience</th>
						<td>
							<input type="radio" value="3" name="resilience" id="resilience-3">
							<label for="resilience-3"><strong>High:</strong> Has recursion, indestructible effects, hexproof, counterspells, and redundancy.</label>
						</td>
						<td>
							<input type="radio" value="2" name="resilience" id="resilience-2">
							<label for="resilience-2"><strong>Medium:</strong> Can recover from 1-2 board wipes or interaction.</label>
						</td>
						<td>
							<input type="radio" value="1" name="resilience" id="resilience-1">
							<label for="resilience-1"><strong>Low:</strong> A single wipe or counterspell can dismantle the game plan.</label>
						</td>
					</tr>
					<tr>
						<th>Closing Power</th>
						<td>
							<input type="radio" value="3" name="closing-power" id="closing-power-3">
							<label for="closing-power-3"><strong>Fast + Efficient:</strong> Infinite combos, two-card combos, commander damage kills, or large overrun-style wins.</label>
						</td>
						<td>
							<input type="radio" value="2" name="closing-power" id="closing-power-2">
							<label for="closing-power-2"><strong>Midrange:</strong> Wins through accumulated value or synergy engines that eventually overpower opponents.</label>
						</td>
						<td>
							<input type="radio" value="1" name="closing-power" id="closing-power-1">
							<label for="closing-power-1"><strong>Weak:</strong> Relies on janky or inconsistent win, may struggle to actually close games.</label>
						</td>
					</tr>
					<tr>
						<th>Interaction</th>
						<td>
							<input type="radio" value="3" name="interaction" id="interaction-3">
							<label for="interaction-3"><strong>High:</strong> Multiple pieces of removal, counterspells, graveyard hate, etc.</label>
						</td>
						<td>
							<input type="radio" value="2" name="interaction" id="interaction-2">
							<label for="interaction-2"><strong>Medium:</strong> Some answers but may struggle against faster or combo-heavy decks.</label>
						</td>
						<td>
							<input type="radio" value="1" name="interaction" id="interaction-1">
							<label for="interaction-1"><strong>Low:</strong> Minimal ability to stop opponents' strategies.</label>
						</td>
					</tr>
				</table>
				<input type="hidden" name="post-id" value="<?php echo $postID; ?>">
				<button type="submit" name="calculate-power-ranking" id="calculate-power-ranking" class="button action-button">
					Calculate Power Ranking <span class="dashicons dashicons-calculator"></span>
				</button>
				<form>
		</div>
<?php
	}

	/**
	 * This function handles the power ranking calculation
	 */
	public static function handle_power_ranking_calculation()
	{
		if (isset($_POST['calculate-power-ranking'])) {
			$postID = $_POST['post-id'];
			$consistency = $_POST['consistency'] - 1;
			$speed = $_POST['speed'] - 1;
			$resilience = $_POST['resilience'] - 1;
			$closing_power = $_POST['closing-power'] - 1;
			$interaction = $_POST['interaction'] - 1;
			$power_ranking = [
				'consistency' => $consistency,
				'speed' => $speed,
				'resilience' => $resilience,
				'closing_power' => $closing_power,
				'interaction' => $interaction,
				'total' => 0 + $consistency + $speed + $resilience + $closing_power + $interaction
			];
			$power_rank = 0 + $consistency + $speed + $resilience + $closing_power + $interaction;
			if (empty($_POST['consistency']) || empty($_POST['speed']) || empty($_POST['resilience']) || empty($_POST['closing-power']) || empty($_POST['interaction'])) {
				echo '<div class="error"><p>Please select all fields.</p></div>';
				return;
			}
			update_post_meta($postID, 'power_ranking', $power_ranking);
			update_post_meta($postID, 'power_rank', $power_rank);
			wp_redirect(admin_url('post.php?post=' . $postID . '&action=edit'));
			exit;
		}
	}
}

add_action('admin_init', [DecksPostPage::class, 'add_deck_info_sidebar_meta_box']);
add_action('edit_form_after_title', [DecksPostPage::class, 'display_win_loss_table']);
add_action('admin_init', [DecksPostPage::class, 'handle_remove_row']);
add_action('admin_init', [DecksPostPage::class, 'handle_win_loss_form_submit']);
add_action('edit_form_after_title', [DecksPostPage::class, 'display_power_ranking']);
add_action('edit_form_after_title', [DecksPostPage::class, 'display_power_ranking_form']);
add_action('admin_init', [DecksPostPage::class, 'handle_power_ranking_calculation']);
