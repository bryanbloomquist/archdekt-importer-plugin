<?php

/**
 * Archidekt Importer
 *
 * @package           ArchidektImporterPlugin
 * @author            Bryan Bloomquist
 * @copyright         2025 Bryan Bloomquist
 * @license           GPL-2.0-or-later
 *
 * Plugin Name:       Archidekt Importer Plugin
 * Description:       WordPress plugin for importing deck data from Archidekt.com
 * Version:           1.0.0
 * Author:            Bryan Bloomquist
 * Author URI:        https://bryanbloomquist.com
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       archidekt-importer-plugin
 * Domain Path:       /languages
 */

/**
 * If this file is called directly, then abort execution.
 */
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Define plugin constants
 */
define('ADI_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('ADI_PLUGIN_URL', plugin_dir_url(__FILE__));

/**
 * Check to see if a Custom Post Type named "deck" already exists before activating the plugin
 */
function activate_archidekt_importer()
{
    if (post_type_exists('deck')) {
        wp_die('Sorry, Archidekt Importer plugin could not be activated. A Custom Post Type named "decks" already exists.<br><a href="' . admin_url('plugins.php') . '">&laquo; Return to Plugins</a>');
    }
}
register_activation_hook(__FILE__, 'activate_archidekt_importer');

/**
 * Enqueue the plugin styles
 */
wp_enqueue_style('archidekt-importer', ADI_PLUGIN_URL . 'Dist/CSS/style.css', [], time());

/**
 * Include the required files
 */
require_once ADI_PLUGIN_PATH . 'Includes/DebugLogger/Logger.php';
require_once ADI_PLUGIN_PATH . 'Includes/DecksPostType/CreateDecksPostType.php';
require_once ADI_PLUGIN_PATH . 'Includes/DecksPostType/DecksAdminPage.php';
require_once ADI_PLUGIN_PATH . 'Includes/DecksPostType/DecksPostPage.php';
require_once ADI_PLUGIN_PATH . 'Includes/DecksPostType/UpdateDecksPostMeta.php';
require_once ADI_PLUGIN_PATH . 'Includes/ImportDeckData/GetColorIdentity.php';
require_once ADI_PLUGIN_PATH . 'Includes/ImportDeckData/ImportArchidektData.php';
require_once ADI_PLUGIN_PATH . 'Includes/ImportDeckData/ImportDeckDataAdminPage.php';
require_once ADI_PLUGIN_PATH . 'Includes/ImportDeckData/ProcessImportedData.php';
require_once ADI_PLUGIN_PATH . 'Includes/DecksDataTable/TableView.php';
require_once ADI_PLUGIN_PATH . 'Includes/DecksDataTable/RetrieveData.php';
require_once ADI_PLUGIN_PATH . 'Includes/DecksDataTable/Columns.php';
require_once ADI_PLUGIN_PATH . 'Includes/WinLossTracker/WinLossPage.php';
