<?php
/**
 * Plugin Name: Mariage Forms
 * Description: Gestion des formulaires RSVP, questionnaire et photos pour le mariage de Julie & Julien
 * Version: 1.0
 * Author: Julie & Julien
 * Text Domain: mariage-forms
 */

if (!defined('ABSPATH')) {
    exit;
}

define('MARIAGE_FORMS_PATH', plugin_dir_path(__FILE__));
define('MARIAGE_FORMS_URL', plugin_dir_url(__FILE__));

// Includes
require_once MARIAGE_FORMS_PATH . 'includes/class-db-setup.php';
require_once MARIAGE_FORMS_PATH . 'includes/class-rsvp-handler.php';
require_once MARIAGE_FORMS_PATH . 'includes/class-questionnaire-handler.php';
require_once MARIAGE_FORMS_PATH . 'includes/class-photos-handler.php';
require_once MARIAGE_FORMS_PATH . 'admin/class-admin-page.php';

// Activation hook : create tables
register_activation_hook(__FILE__, ['Mariage_DB_Setup', 'create_tables']);

// Init handlers
add_action('init', function () {
    new Mariage_RSVP_Handler();
    new Mariage_Questionnaire_Handler();
    new Mariage_Photos_Handler();
});

// Admin pages are now handled by the theme (Mon Mariage menu)
