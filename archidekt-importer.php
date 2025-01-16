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
 * Activate the plugin
 */
function activate_archidekt_importer()
{
    require_once(ADI_PLUGIN_PATH . 'Includes/ActivatePlugin.php');
    ActivatePlugin::activate_archidekt_importer();
}
register_activation_hook(__FILE__, 'activate_archidekt_importer');

/**
 * Enqueue the plugin styles
 */
wp_enqueue_style('archidekt-importer', ADI_PLUGIN_URL . 'Dist/CSS/style.css', [], time());

/**
 * Include the required files
 */
require_once ADI_PLUGIN_PATH . 'Includes/DecksPostType.php';
require_once ADI_PLUGIN_PATH . 'Includes/ImportNewDeck.php';
require_once ADI_PLUGIN_PATH . 'Includes/ProcessIncomingDeck.php';
require_once ADI_PLUGIN_PATH . 'Includes/ViewDecksData.php';
