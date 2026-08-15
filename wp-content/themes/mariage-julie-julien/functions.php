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

// Enqueue styles for Gutenberg editor (preview)
function mariage_enqueue_editor_assets() {
    // Google Fonts for editor
    wp_enqueue_style(
        'mariage-editor-fonts',
        'https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,600;1,300;1,400&family=Great+Vibes&family=Dancing+Script:wght@400;500;600;700&family=Montserrat:wght@300;400;500;600&display=swap',
        [],
        null
    );

    // Editor-specific styles
    wp_enqueue_style(
        'mariage-editor-style',
        get_template_directory_uri() . '/assets/css/editor-style.css',
        ['mariage-editor-fonts'],
        '1.0'
    );
}
add_action('enqueue_block_editor_assets', 'mariage_enqueue_editor_assets');

// Theme setup
function mariage_theme_setup() {
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    add_theme_support('html5', ['search-form', 'comment-form', 'gallery', 'caption']);
}
add_action('after_setup_theme', 'mariage_theme_setup');

// Register decoration block
function mariage_register_blocks() {
    register_block_type(get_template_directory() . '/blocks/decoration-image');
}
add_action('init', 'mariage_register_blocks');

// Shortcodes pour les elements dynamiques
// [mariage_rsvp] - Formulaire RSVP
function mariage_rsvp_shortcode() {
    ob_start();
    get_template_part('template-parts/rsvp');
    return ob_get_clean();
}
add_shortcode('mariage_rsvp', 'mariage_rsvp_shortcode');

// [mariage_cagnotte] - Section cagnotte
function mariage_cagnotte_shortcode() {
    ob_start();
    get_template_part('template-parts/cagnotte');
    return ob_get_clean();
}
add_shortcode('mariage_cagnotte', 'mariage_cagnotte_shortcode');

// [mariage_photos] - Section photos
function mariage_photos_shortcode() {
    ob_start();
    get_template_part('template-parts/photos');
    return ob_get_clean();
}
add_shortcode('mariage_photos', 'mariage_photos_shortcode');

// [mariage_lieu] - Section lieu avec carte
function mariage_lieu_shortcode() {
    ob_start();
    get_template_part('template-parts/lieu');
    return ob_get_clean();
}
add_shortcode('mariage_lieu', 'mariage_lieu_shortcode');

// [mariage_agenda] - Section programme
function mariage_agenda_shortcode() {
    ob_start();
    get_template_part('template-parts/agenda');
    return ob_get_clean();
}
add_shortcode('mariage_agenda', 'mariage_agenda_shortcode');

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
