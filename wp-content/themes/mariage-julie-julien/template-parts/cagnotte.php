<?php
$title    = get_theme_mod('cagnotte_title', 'Cagnotte');
$subtitle = get_theme_mod('cagnotte_subtitle', 'Votre presence est notre plus beau cadeau');
$text     = get_theme_mod('cagnotte_text', "Votre presence a nos cotes est le plus beau des cadeaux.<br><br>Nous revons de partir au <strong>Nepal</strong> pour notre lune de miel ! Si vous souhaitez contribuer a cette aventure, une cagnotte est a votre disposition.");
$goal     = get_theme_mod('cagnotte_goal', 5000);
$url      = get_theme_mod('cagnotte_url', 'https://www.ungrandjour.com/fr/mariage-julie-julien-montpellier');
$btn_text = get_theme_mod('cagnotte_btn_text', 'Participer a la cagnotte');
$image    = get_theme_mod('cagnotte_image');

if (!$image) {
    $image = get_template_directory_uri() . '/assets/img/nepal.jpg';
}
?>
<section class="section" id="cagnotte">
    <canvas class="section-canvas" id="canvas-cagnotte"></canvas>
    <div class="section-container">
        <div class="section-header fade-in">
            <?php get_template_part('template-parts/floral-frame'); ?>
            <h2 class="section-title"><?php echo esc_html($title); ?></h2>
            <p class="section-subtitle"><?php echo esc_html($subtitle); ?></p>
        </div>

        <div class="cagnotte-card fade-in">
            <div class="cagnotte-image">
                <img src="<?php echo esc_url($image); ?>"
                     alt="<?php echo esc_attr($title); ?>">
            </div>
            <div class="cagnotte-icon">&#9992;</div>
            <p><?php echo wp_kses_post($text); ?></p>
            <div class="cagnotte-progress-wrapper" id="cagnotte-progress" data-goal="<?php echo esc_attr($goal); ?>">
                <div class="cagnotte-progress-labels">
                    <span class="cagnotte-collected"><span id="cagnotte-amount">...</span></span>
                    <span class="cagnotte-goal">Objectif : <?php echo number_format($goal, 0, ',', ' '); ?> &euro;</span>
                </div>
                <div class="cagnotte-progress-bar">
                    <div class="cagnotte-progress-fill" id="cagnotte-fill" style="width: 0%"></div>
                </div>
                <p class="cagnotte-percent" id="cagnotte-percent"></p>
            </div>

            <a href="<?php echo esc_url($url); ?>" class="btn btn-primary btn-large" target="_blank" rel="noopener noreferrer">
                <?php echo esc_html($btn_text); ?>
            </a>
        </div>
    </div>
</section>
