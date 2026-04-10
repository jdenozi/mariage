<?php
$nav_logo          = get_theme_mod('nav_logo', 'J & J');
$nav_programme     = get_theme_mod('nav_label_programme', 'Programme');
$nav_lieu          = get_theme_mod('nav_label_lieu', 'Le Lieu');
$nav_rsvp          = get_theme_mod('nav_label_rsvp', 'RSVP');
$nav_cagnotte      = get_theme_mod('nav_label_cagnotte', 'Cagnotte');
$nav_photos        = get_theme_mod('nav_label_photos', 'Photos');
$meta_desc         = get_theme_mod('site_meta_description', 'Mariage de Julie & Julien - 8 mai 2027');
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?php echo esc_attr($meta_desc); ?>">
    <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>

<nav class="site-nav" id="site-nav">
    <div class="nav-container">
        <a href="#hero" class="nav-logo"><?php echo esc_html($nav_logo); ?></a>
        <button class="nav-toggle" id="nav-toggle" aria-label="Menu">
            <span></span>
            <span></span>
            <span></span>
        </button>
        <div class="nav-links" id="nav-links">
            <a href="#agenda"><?php echo esc_html($nav_programme); ?></a>
            <a href="#lieu"><?php echo esc_html($nav_lieu); ?></a>
            <a href="#rsvp"><?php echo esc_html($nav_rsvp); ?></a>
            <a href="#cagnotte"><?php echo esc_html($nav_cagnotte); ?></a>
            <a href="#photos"><?php echo esc_html($nav_photos); ?></a>
        </div>
    </div>
</nav>
