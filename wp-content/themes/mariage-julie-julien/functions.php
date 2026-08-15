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

// Enqueue styles and scripts
function mariage_enqueue_assets() {
    // Google Fonts
    wp_enqueue_style(
        'mariage-fonts',
        'https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,500;0,600;1,300;1,400&family=Great+Vibes&family=Montserrat:wght@300;400;500&display=swap',
        [],
        null
    );

    // Main theme style
    wp_enqueue_style(
        'mariage-style',
        get_stylesheet_uri(),
        ['mariage-fonts'],
        '1.1'
    );

    // Custom styles (new bohemian style)
    wp_enqueue_style(
        'mariage-custom-style',
        get_template_directory_uri() . '/assets/css/custom-style.css',
        ['mariage-style'],
        '1.1'
    );

    // Main JS
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

// Register decoration block
function mariage_register_blocks() {
    wp_register_script(
        'mariage-decoration-block',
        get_template_directory_uri() . '/blocks/decoration-image/index.js',
        ['wp-blocks', 'wp-element', 'wp-block-editor', 'wp-components'],
        '1.1',
        true
    );

    register_block_type('mariage/decoration-image', [
        'editor_script' => 'mariage-decoration-block',
    ]);
}
add_action('init', 'mariage_register_blocks');
