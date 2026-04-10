<?php
$prenom1  = get_theme_mod('hero_prenom1', 'Julie');
$prenom2  = get_theme_mod('hero_prenom2', 'Julien');
$date     = get_theme_mod('hero_date', '8 mai 2027');
$lieu     = get_theme_mod('hero_lieu', 'Montpellier');
$datetime = get_theme_mod('hero_datetime', '2027-05-08T14:00:00');
$photo    = get_theme_mod('hero_photo');
$btn_text = get_theme_mod('hero_btn_text', 'Confirmer ma presence');

if (!$photo) {
    $photo = get_template_directory_uri() . '/assets/img/hero-bisou.jpg';
}
?>
<section class="hero" id="hero">
    <canvas id="hero-canvas" class="flower-canvas"></canvas>

    <div class="hero-box fade-in">
        <!-- Arabesques coins -->
        <svg class="hero-arabesque hero-arabesque--tl" viewBox="0 0 120 120" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M0 60 C0 30, 10 10, 60 0" stroke="var(--sage)" stroke-width="1.5" fill="none"/>
            <path d="M0 80 C5 40, 20 15, 80 0" stroke="var(--sage)" stroke-width="1" fill="none" opacity="0.6"/>
            <path d="M0 45 C8 25, 18 12, 45 0" stroke="var(--sage-dark)" stroke-width="1" fill="none" opacity="0.5"/>
            <circle cx="58" cy="2" r="3" fill="var(--sage)" opacity="0.4"/>
            <circle cx="2" cy="58" r="3" fill="var(--sage)" opacity="0.4"/>
            <path d="M5 50 C10 35, 15 25, 30 15 C25 28, 18 38, 5 50Z" fill="var(--sage)" opacity="0.08"/>
            <path d="M15 5 Q25 15, 20 30 Q15 20, 15 5Z" fill="var(--sage)" opacity="0.1"/>
            <path d="M0 35 C5 22, 12 12, 35 0" stroke="var(--sage)" stroke-width="0.8" fill="none" opacity="0.3" stroke-dasharray="3 4"/>
        </svg>
        <svg class="hero-arabesque hero-arabesque--tr" viewBox="0 0 120 120" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M120 60 C120 30, 110 10, 60 0" stroke="var(--sage)" stroke-width="1.5" fill="none"/>
            <path d="M120 80 C115 40, 100 15, 40 0" stroke="var(--sage)" stroke-width="1" fill="none" opacity="0.6"/>
            <path d="M120 45 C112 25, 102 12, 75 0" stroke="var(--sage-dark)" stroke-width="1" fill="none" opacity="0.5"/>
            <circle cx="62" cy="2" r="3" fill="var(--sage)" opacity="0.4"/>
            <circle cx="118" cy="58" r="3" fill="var(--sage)" opacity="0.4"/>
            <path d="M115 50 C110 35, 105 25, 90 15 C95 28, 102 38, 115 50Z" fill="var(--sage)" opacity="0.08"/>
            <path d="M105 5 Q95 15, 100 30 Q105 20, 105 5Z" fill="var(--sage)" opacity="0.1"/>
            <path d="M120 35 C115 22, 108 12, 85 0" stroke="var(--sage)" stroke-width="0.8" fill="none" opacity="0.3" stroke-dasharray="3 4"/>
        </svg>
        <svg class="hero-arabesque hero-arabesque--bl" viewBox="0 0 120 120" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M0 60 C0 90, 10 110, 60 120" stroke="var(--sage)" stroke-width="1.5" fill="none"/>
            <path d="M0 40 C5 80, 20 105, 80 120" stroke="var(--sage)" stroke-width="1" fill="none" opacity="0.6"/>
            <path d="M0 75 C8 95, 18 108, 45 120" stroke="var(--sage-dark)" stroke-width="1" fill="none" opacity="0.5"/>
            <circle cx="58" cy="118" r="3" fill="var(--sage)" opacity="0.4"/>
            <circle cx="2" cy="62" r="3" fill="var(--sage)" opacity="0.4"/>
            <path d="M5 70 C10 85, 15 95, 30 105 C25 92, 18 82, 5 70Z" fill="var(--sage)" opacity="0.08"/>
            <path d="M15 115 Q25 105, 20 90 Q15 100, 15 115Z" fill="var(--sage)" opacity="0.1"/>
        </svg>
        <svg class="hero-arabesque hero-arabesque--br" viewBox="0 0 120 120" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M120 60 C120 90, 110 110, 60 120" stroke="var(--sage)" stroke-width="1.5" fill="none"/>
            <path d="M120 40 C115 80, 100 105, 40 120" stroke="var(--sage)" stroke-width="1" fill="none" opacity="0.6"/>
            <path d="M120 75 C112 95, 102 108, 75 120" stroke="var(--sage-dark)" stroke-width="1" fill="none" opacity="0.5"/>
            <circle cx="62" cy="118" r="3" fill="var(--sage)" opacity="0.4"/>
            <circle cx="118" cy="62" r="3" fill="var(--sage)" opacity="0.4"/>
            <path d="M115 70 C110 85, 105 95, 90 105 C95 92, 102 82, 115 70Z" fill="var(--sage)" opacity="0.08"/>
            <path d="M105 115 Q95 105, 100 90 Q105 100, 105 115Z" fill="var(--sage)" opacity="0.1"/>
        </svg>

        <div class="hero-layout">
            <div class="hero-photo">
                <div class="hero-frame">
                    <img src="<?php echo esc_url($photo); ?>"
                         alt="<?php echo esc_attr($prenom1 . ' & ' . $prenom2); ?>">
                </div>
            </div>

            <div class="hero-content">
                <p class="hero-subtitle">Mariage</p>

                <h1><?php echo esc_html($prenom1); ?></h1>
                <p class="ampersand">&</p>
                <h1><?php echo esc_html($prenom2); ?></h1>

                <p class="hero-date"><?php echo esc_html($date); ?> &mdash; <?php echo esc_html($lieu); ?></p>

                <a href="#rsvp" class="btn btn-primary"><?php echo esc_html($btn_text); ?></a>

                <div class="countdown" id="countdown" data-date="<?php echo esc_attr($datetime); ?>">
                    <div class="countdown-item">
                        <span class="countdown-number" id="countdown-days">--</span>
                        <span class="countdown-label">Jours</span>
                    </div>
                    <div class="countdown-item">
                        <span class="countdown-number" id="countdown-hours">--</span>
                        <span class="countdown-label">Heures</span>
                    </div>
                    <div class="countdown-item">
                        <span class="countdown-number" id="countdown-minutes">--</span>
                        <span class="countdown-label">Minutes</span>
                    </div>
                    <div class="countdown-item">
                        <span class="countdown-number" id="countdown-seconds">--</span>
                        <span class="countdown-label">Secondes</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="scroll-indicator">
        <a href="#agenda">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M7 13l5 5 5-5M7 6l5 5 5-5"/>
            </svg>
        </a>
    </div>
</section>
