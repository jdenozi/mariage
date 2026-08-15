<?php
/**
 * Mariage Julie & Julien - Functions
 */

// Admin RSVP
require_once get_template_directory() . '/inc/customizer.php';

// Theme setup
function mariage_theme_setup() {
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    add_theme_support('html5', ['search-form', 'comment-form', 'gallery', 'caption']);
}
add_action('after_setup_theme', 'mariage_theme_setup');

// Enqueue scripts for RSVP form
function mariage_enqueue_assets() {
    wp_enqueue_script(
        'mariage-main',
        get_template_directory_uri() . '/assets/js/main.js',
        [],
        '1.0',
        true
    );

    wp_localize_script('mariage-main', 'mariageAjax', [
        'ajaxurl' => admin_url('admin-ajax.php'),
        'nonce'   => wp_create_nonce('mariage_nonce'),
    ]);
}
add_action('wp_enqueue_scripts', 'mariage_enqueue_assets');

// RSVP Shortcode
function mariage_rsvp_shortcode() {
    ob_start();
    get_template_part('template-parts/rsvp');
    return ob_get_clean();
}
add_shortcode('mariage_rsvp', 'mariage_rsvp_shortcode');
