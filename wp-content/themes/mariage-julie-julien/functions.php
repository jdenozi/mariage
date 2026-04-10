<?php
/**
 * Mariage Julie & Julien - Functions
 */

// Customizer
require_once get_template_directory() . '/inc/customizer.php';

// Enqueue styles & scripts
function mariage_enqueue_assets() {
    // Google Fonts
    wp_enqueue_style(
        'google-fonts',
        'https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,600;1,300;1,400&family=Great+Vibes&family=Dancing+Script:wght@400;500;600;700&family=Montserrat:wght@300;400;500;600&display=swap',
        [],
        null
    );

    // Theme style
    wp_enqueue_style('mariage-style', get_stylesheet_uri(), [], '1.0');

    // Custom CSS
    wp_enqueue_style(
        'mariage-custom',
        get_template_directory_uri() . '/assets/css/custom.css',
        ['mariage-style'],
        '1.0'
    );

    // Flowers animation
    wp_enqueue_script(
        'mariage-flowers',
        get_template_directory_uri() . '/assets/js/flowers.js',
        [],
        '1.0',
        true
    );

    // Main JS
    wp_enqueue_script(
        'mariage-main',
        get_template_directory_uri() . '/assets/js/main.js',
        [],
        '1.5',
        true
    );

    // Pass AJAX URL and nonce to JS
    wp_localize_script('mariage-main', 'mariageAjax', [
        'ajaxurl' => admin_url('admin-ajax.php'),
        'nonce'   => wp_create_nonce('mariage_nonce'),
    ]);
}
add_action('wp_enqueue_scripts', 'mariage_enqueue_assets');

// Theme setup
function mariage_theme_setup() {
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    add_theme_support('html5', ['search-form', 'comment-form', 'gallery', 'caption']);
}
add_action('after_setup_theme', 'mariage_theme_setup');

// Set static front page programmatically
function mariage_set_front_page() {
    // Check if front page is already configured
    if (get_option('show_on_front') === 'page' && get_option('page_on_front')) {
        $page = get_post(get_option('page_on_front'));
        if ($page && $page->post_status === 'publish') {
            return;
        }
    }

    // Look for existing Accueil page
    $pages = get_posts([
        'post_type'   => 'page',
        'title'       => 'Accueil',
        'post_status' => 'publish',
        'numberposts' => 1,
    ]);

    if (!empty($pages)) {
        $page_id = $pages[0]->ID;
    } else {
        $page_id = wp_insert_post([
            'post_title'   => 'Accueil',
            'post_content' => '',
            'post_status'  => 'publish',
            'post_type'    => 'page',
        ]);
    }

    update_option('show_on_front', 'page');
    update_option('page_on_front', $page_id);
}
add_action('init', 'mariage_set_front_page');

// Allow SVG uploads
function mariage_allow_mime_types($mimes) {
    $mimes['svg'] = 'image/svg+xml';
    $mimes['mp4'] = 'video/mp4';
    $mimes['webm'] = 'video/webm';
    $mimes['mov'] = 'video/quicktime';
    return $mimes;
}
add_filter('upload_mimes', 'mariage_allow_mime_types');

// Increase upload size
function mariage_upload_size($size) {
    return 50 * 1024 * 1024; // 50MB
}
add_filter('upload_size_limit', 'mariage_upload_size');

// Scrape cagnotte amount from Un Grand Jour
function mariage_get_cagnotte_amount() {
    $cache_key = 'cagnotte_amount';
    $cached = get_transient($cache_key);

    if ($cached !== false) {
        return $cached;
    }

    $goal = get_theme_mod('cagnotte_goal', 5000);
    $url = get_theme_mod('cagnotte_url', 'https://www.ungrandjour.com/fr/mariage-julie-julien-montpellier');

    $response = wp_remote_get($url, [
        'timeout' => 10,
        'user-agent' => 'Mozilla/5.0 (compatible; WordPress)',
    ]);

    if (is_wp_error($response)) {
        return ['collected' => 0, 'goal' => $goal];
    }

    $body = wp_remote_retrieve_body($response);
    $collected = 0;

    // Match patterns like "150€ / 1400€" in product items
    if (preg_match_all('/(\d+)\s*€\s*\/\s*(\d+)\s*€/', $body, $matches)) {
        for ($i = 0; $i < count($matches[1]); $i++) {
            $collected += intval($matches[1][$i]);
        }
    }

    $result = ['collected' => $collected, 'goal' => $goal];

    // Cache for 30 minutes
    set_transient($cache_key, $result, 30 * MINUTE_IN_SECONDS);

    return $result;
}

// AJAX endpoint for cagnotte
function mariage_ajax_cagnotte() {
    check_ajax_referer('mariage_nonce', 'nonce');
    $data = mariage_get_cagnotte_amount();
    wp_send_json_success($data);
}
add_action('wp_ajax_get_cagnotte', 'mariage_ajax_cagnotte');
add_action('wp_ajax_nopriv_get_cagnotte', 'mariage_ajax_cagnotte');
