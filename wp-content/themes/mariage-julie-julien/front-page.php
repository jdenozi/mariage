<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Mariage de Julie & Julien - 8 mai 2027 - Montpellier">
    <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<?php
// Data du mariage
$prenom1 = 'Julie';
$prenom2 = 'Julien';
$date_display = '8 mai 2027';
$date_iso = '2027-05-08T14:00:00';
$lieu_name = 'Domaine de la Tour';
$lieu_adresse = '5 rue du Pas du Loup, 34070 Montpellier';
?>

<!-- Navigation -->
<nav class="site-nav" id="site-nav">
    <ul class="nav-menu">
        <li><a href="#hero">Accueil</a></li>
        <li><a href="#programme">Programme</a></li>
        <li><a href="#lieu">Le Lieu</a></li>
        <li class="nav-logo"><?php echo esc_html(substr($prenom1, 0, 1) . ' & ' . substr($prenom2, 0, 1)); ?></li>
        <li><a href="#infos">Infos</a></li>
        <li><a href="#rsvp">RSVP</a></li>
    </ul>
</nav>

<!-- Hero Section -->
<section class="hero-section" id="hero">
    <div class="hero-content-wrapper">

        <!-- Photo en arche -->
        <div class="hero-arch">
            <img src="<?php echo get_template_directory_uri(); ?>/assets/img/couple.jpg"
                 alt="<?php echo esc_attr($prenom1 . ' & ' . $prenom2); ?>"
                 onerror="this.src='https://images.unsplash.com/photo-1519741497674-611481863552?w=400&h=500&fit=crop'">
        </div>

        <!-- Noms -->
        <h1 class="hero-names"><?php echo esc_html(strtoupper($prenom1)); ?></h1>
        <span class="hero-and">et</span>
        <h1 class="hero-names"><?php echo esc_html(strtoupper($prenom2)); ?></h1>

        <p class="hero-subtitle">se marient !</p>

        <div class="elegant-divider">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                <path d="M12 2l2.4 7.4H22l-6 4.6 2.3 7L12 16.4 5.7 21l2.3-7L2 9.4h7.6L12 2z"/>
            </svg>
        </div>

        <p class="hero-date"><?php echo esc_html(strtoupper($date_display)); ?></p>
        <p class="hero-location"><?php echo esc_html($lieu_name); ?>, Montpellier</p>

        <p class="hero-message">Nous avons hâte de célébrer avec vous !</p>
    </div>
</section>

<!-- Countdown Section -->
<section class="countdown-section" id="countdown" data-date="<?php echo esc_attr($date_iso); ?>">
    <div class="countdown-title">
        <span>Save the Date</span>
    </div>

    <div class="countdown-wrapper">
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
</section>

<!-- Programme Section -->
<section class="section-cream" id="programme">
    <div class="section-inner">
        <div class="section-header">
            <span class="section-label">Le Programme</span>
            <h2>de la journée</h2>
        </div>

        <div class="timeline">
            <div class="timeline-item">
                <div class="timeline-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                        <circle cx="9" cy="12" r="4"/>
                        <circle cx="15" cy="12" r="4"/>
                    </svg>
                </div>
                <div class="timeline-time">12h00</div>
                <div class="timeline-content">
                    <h4>Cérémonie</h4>
                    <p>Cérémonie laïque dans les jardins</p>
                </div>
            </div>

            <div class="timeline-item">
                <div class="timeline-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                        <path d="M8 22h8M12 17v5M7 2h10l-2 9a4 4 0 01-3 3 4 4 0 01-3-3L7 2z"/>
                    </svg>
                </div>
                <div class="timeline-time">13h00</div>
                <div class="timeline-content">
                    <h4>Vin d'honneur</h4>
                    <p>Cocktail et retrouvailles</p>
                </div>
            </div>

            <div class="timeline-item">
                <div class="timeline-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                        <circle cx="12" cy="12" r="8"/>
                        <circle cx="12" cy="12" r="4"/>
                    </svg>
                </div>
                <div class="timeline-time">16h00</div>
                <div class="timeline-content">
                    <h4>Dîner</h4>
                    <p>Repas de fête</p>
                </div>
            </div>

            <div class="timeline-item">
                <div class="timeline-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                        <path d="M9 18V5l12-2v13"/>
                        <circle cx="6" cy="18" r="3"/>
                        <circle cx="18" cy="16" r="3"/>
                    </svg>
                </div>
                <div class="timeline-time">22h00</div>
                <div class="timeline-content">
                    <h4>Soirée dansante</h4>
                    <p>Musique et célébration</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Lieu Section -->
<section class="section-cream" id="lieu">
    <div class="section-inner">
        <div class="lieu-grid">
            <div class="lieu-image arch-frame">
                <img src="<?php echo get_template_directory_uri(); ?>/assets/img/domaine.jpg"
                     alt="<?php echo esc_attr($lieu_name); ?>"
                     onerror="this.src='https://images.unsplash.com/photo-1464366400600-7168b8af9bc3?w=500&h=600&fit=crop'">
            </div>
            <div class="lieu-info">
                <span class="section-label">Le Lieu</span>
                <h3><?php echo esc_html($lieu_name); ?></h3>
                <p class="lieu-address">
                    5 rue du Pas du Loup<br>
                    34070 Montpellier
                </p>
                <a href="https://www.google.com/maps/dir/?api=1&destination=<?php echo urlencode($lieu_adresse); ?>"
                   target="_blank" rel="noopener" class="btn-outline">
                    Voir l'itinéraire
                </a>
            </div>
        </div>
    </div>
</section>

<!-- Infos Section -->
<section class="section-terracotta" id="infos">
    <div class="section-inner">
        <span class="section-label" style="color:rgba(255,255,255,0.8);">Infos pratiques</span>

        <div class="infos-grid">
            <div class="info-card">
                <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2">
                    <rect x="3" y="6" width="18" height="12" rx="2"/>
                    <circle cx="7" cy="21" r="2"/>
                    <circle cx="17" cy="21" r="2"/>
                </svg>
                <h4>Tramway</h4>
                <p>Ligne 3 ou 5<br>Arrêt Estanove</p>
            </div>
            <div class="info-card">
                <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2">
                    <rect x="3" y="5" width="18" height="14" rx="2"/>
                    <path d="M9 9h4a2 2 0 010 4H9V9z"/>
                </svg>
                <h4>Parking</h4>
                <p>15 places<br>sur le domaine</p>
            </div>
            <div class="info-card">
                <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2">
                    <path d="M20.84 4.61a5.5 5.5 0 00-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 00-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 000-7.78z"/>
                </svg>
                <h4>Dress Code</h4>
                <p>Chic décontracté<br>Tons naturels</p>
            </div>
        </div>

        <div class="color-palette-wrapper">
            <p>Palette suggérée</p>
            <div class="color-palette">
                <span class="color-swatch" style="background:#FAF8F3;"></span>
                <span class="color-swatch" style="background:#9BA88A;"></span>
                <span class="color-swatch" style="background:#C4856A;"></span>
                <span class="color-swatch" style="background:#E0CFA0;"></span>
            </div>
        </div>
    </div>
</section>

<!-- RSVP Section -->
<section class="rsvp-section" id="rsvp">
    <div class="section-inner">
        <span class="section-label" style="color:var(--gold-light);">Répondez</span>
        <h2>RSVP</h2>
        <p class="rsvp-intro">Merci de confirmer votre présence avant le 1er mars 2027</p>

        <?php echo do_shortcode('[mariage_rsvp]'); ?>
    </div>
</section>

<!-- Footer -->
<footer class="site-footer">
    <p class="footer-names"><?php echo esc_html($prenom1); ?> & <?php echo esc_html($prenom2); ?></p>
    <p class="footer-date"><?php echo esc_html($date_display); ?></p>
</footer>

<?php wp_footer(); ?>
</body>
</html>
