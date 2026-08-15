<?php
/**
 * Homepage Template - Style Boheme
 */

// Data
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
        <li class="nav-logo"><?php echo substr($prenom1, 0, 1) . ' & ' . substr($prenom2, 0, 1); ?></li>
        <li><a href="#infos">Infos</a></li>
        <li><a href="#rsvp">RSVP</a></li>
    </ul>
</nav>

<!-- Hero Section -->
<section class="hero-section" id="hero">
    <!-- Decorations botaniques -->
    <div class="botanical-decoration botanical-top-left">
        <svg width="120" height="150" viewBox="0 0 120 150" fill="none" stroke="#9BA88A" stroke-width="1" opacity="0.4">
            <path d="M60 150 Q60 100 40 60 Q30 40 10 20"/>
            <ellipse cx="25" cy="45" rx="15" ry="8" transform="rotate(-30 25 45)"/>
            <ellipse cx="45" cy="75" rx="12" ry="6" transform="rotate(-20 45 75)"/>
            <ellipse cx="35" cy="105" rx="14" ry="7" transform="rotate(-35 35 105)"/>
        </svg>
    </div>
    <div class="botanical-decoration botanical-top-right">
        <svg width="120" height="150" viewBox="0 0 120 150" fill="none" stroke="#9BA88A" stroke-width="1" opacity="0.4">
            <path d="M60 150 Q60 100 80 60 Q90 40 110 20"/>
            <ellipse cx="95" cy="45" rx="15" ry="8" transform="rotate(30 95 45)"/>
            <ellipse cx="75" cy="75" rx="12" ry="6" transform="rotate(20 75 75)"/>
            <ellipse cx="85" cy="105" rx="14" ry="7" transform="rotate(35 85 105)"/>
        </svg>
    </div>

    <!-- Lune et etoiles -->
    <div style="position:absolute;top:30px;right:15%;opacity:0.3;">
        <svg width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="#9BA88A" stroke-width="1">
            <path d="M21 12.79A9 9 0 1111.21 3 7 7 0 0021 12.79z"/>
        </svg>
    </div>
    <div style="position:absolute;top:60px;right:10%;opacity:0.2;">
        <svg width="10" height="10" viewBox="0 0 24 24" fill="#C9A962"><circle cx="12" cy="12" r="2"/></svg>
    </div>
    <div style="position:absolute;top:40px;right:20%;opacity:0.15;">
        <svg width="8" height="8" viewBox="0 0 24 24" fill="#C9A962"><circle cx="12" cy="12" r="2"/></svg>
    </div>

    <div style="max-width:1000px;margin:0 auto;padding:0 20px;">
        <!-- Photo en arche -->
        <div class="hero-arch" style="margin-bottom:40px;">
            <img src="<?php echo get_template_directory_uri(); ?>/assets/img/hero-bisou.jpg"
                 alt="<?php echo esc_attr($prenom1 . ' & ' . $prenom2); ?>"
                 onerror="this.src='https://images.unsplash.com/photo-1519741497674-611481863552?w=400&h=500&fit=crop'">
        </div>

        <!-- Noms -->
        <h1 class="hero-names"><?php echo esc_html($prenom1); ?></h1>
        <span class="hero-and">et</span>
        <h1 class="hero-names"><?php echo esc_html($prenom2); ?></h1>

        <p class="hero-subtitle">se marient !</p>

        <div class="elegant-divider" style="margin:30px auto;">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#C9A962" stroke-width="1">
                <path d="M12 2l2.4 7.4H22l-6 4.6 2.3 7L12 16.4 5.7 21l2.3-7L2 9.4h7.6L12 2z"/>
            </svg>
        </div>

        <p class="hero-date"><?php echo esc_html(strtoupper($date_display)); ?></p>
        <p class="hero-location"><?php echo esc_html($lieu_name); ?>, Montpellier</p>

        <p class="hero-message">Nous avons hate de celebrer avec vous !</p>
    </div>
</section>

<!-- Countdown Section -->
<section class="countdown-section section-sage" id="countdown" data-date="<?php echo esc_attr($date_iso); ?>">
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
<section class="section-cream" id="programme" style="padding:80px 20px;">
    <div style="max-width:900px;margin:0 auto;">
        <div style="text-align:center;margin-bottom:50px;">
            <p class="script-text" style="font-size:1.8rem;margin-bottom:5px;">Le Programme</p>
            <h2 style="font-family:var(--font-serif);font-size:2rem;letter-spacing:0.1em;text-transform:uppercase;margin:0;">de la journee</h2>
        </div>

        <div class="timeline">
            <!-- Ceremonie -->
            <div class="timeline-item">
                <div class="timeline-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2">
                        <circle cx="9" cy="12" r="4"/>
                        <circle cx="15" cy="12" r="4"/>
                    </svg>
                </div>
                <span class="timeline-time">12h00</span>
                <div class="timeline-content">
                    <h4>Ceremonie</h4>
                    <p>Ceremonie laique dans les jardins</p>
                </div>
            </div>

            <!-- Vin d'honneur -->
            <div class="timeline-item">
                <div class="timeline-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2">
                        <path d="M8 22h8M12 17v5M7 2h10l-2 9a4 4 0 01-3 3 4 4 0 01-3-3L7 2z"/>
                    </svg>
                </div>
                <span class="timeline-time">13h00</span>
                <div class="timeline-content">
                    <h4>Vin d'honneur</h4>
                    <p>Cocktail et retrouvailles</p>
                </div>
            </div>

            <!-- Diner -->
            <div class="timeline-item">
                <div class="timeline-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2">
                        <circle cx="12" cy="12" r="8"/>
                        <circle cx="12" cy="12" r="4"/>
                    </svg>
                </div>
                <span class="timeline-time">16h00</span>
                <div class="timeline-content">
                    <h4>Diner</h4>
                    <p>Repas de fete</p>
                </div>
            </div>

            <!-- Soiree -->
            <div class="timeline-item">
                <div class="timeline-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2">
                        <path d="M9 18V5l12-2v13"/>
                        <circle cx="6" cy="18" r="3"/>
                        <circle cx="18" cy="16" r="3"/>
                    </svg>
                </div>
                <span class="timeline-time">22h00</span>
                <div class="timeline-content">
                    <h4>Soiree dansante</h4>
                    <p>Musique et celebration jusqu'au bout de la nuit</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Lieu Section -->
<section class="section-cream" id="lieu" style="padding:80px 20px;">
    <div style="max-width:1100px;margin:0 auto;">
        <div class="lieu-section">
            <!-- Image en arche -->
            <div class="lieu-image arch-frame">
                <img src="https://images.unsplash.com/photo-1464366400600-7168b8af9bc3?w=500&h=600&fit=crop"
                     alt="<?php echo esc_attr($lieu_name); ?>">
            </div>

            <!-- Infos -->
            <div class="lieu-info">
                <h3>Le Lieu</h3>
                <h4><?php echo esc_html($lieu_name); ?></h4>
                <p class="lieu-address">
                    5 rue du Pas du Loup<br>
                    34070 Montpellier
                </p>
                <a href="https://www.google.com/maps/dir/?api=1&destination=<?php echo urlencode($lieu_adresse); ?>"
                   target="_blank" rel="noopener" class="btn-map">
                    Voir le plan
                </a>
            </div>
        </div>
    </div>
</section>

<!-- Infos pratiques -->
<section class="section-terracotta" id="infos" style="padding:60px 20px;">
    <div style="max-width:800px;margin:0 auto;text-align:center;">
        <h3 style="font-family:var(--font-script);font-size:2.5rem;margin-bottom:30px;">Infos pratiques</h3>

        <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(200px, 1fr));gap:30px;margin-top:30px;">
            <!-- Transport -->
            <div>
                <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="1.2" style="margin-bottom:15px;">
                    <rect x="3" y="6" width="18" height="12" rx="2"/>
                    <circle cx="7" cy="21" r="2"/>
                    <circle cx="17" cy="21" r="2"/>
                    <path d="M7 10h4M13 10h4"/>
                </svg>
                <h4 style="font-family:var(--font-serif);font-size:1.2rem;margin-bottom:10px;">Tramway</h4>
                <p style="font-size:0.9rem;opacity:0.9;">Ligne 3 ou 5<br>Arret Estanove</p>
            </div>

            <!-- Parking -->
            <div>
                <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="1.2" style="margin-bottom:15px;">
                    <rect x="3" y="5" width="18" height="14" rx="2"/>
                    <path d="M9 9h4a2 2 0 010 4H9V9z"/>
                </svg>
                <h4 style="font-family:var(--font-serif);font-size:1.2rem;margin-bottom:10px;">Parking</h4>
                <p style="font-size:0.9rem;opacity:0.9;">15 places disponibles<br>sur le domaine</p>
            </div>

            <!-- Dress code -->
            <div>
                <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="1.2" style="margin-bottom:15px;">
                    <path d="M12 2L8 6h8l-4-4zM8 6v12a2 2 0 002 2h4a2 2 0 002-2V6"/>
                </svg>
                <h4 style="font-family:var(--font-serif);font-size:1.2rem;margin-bottom:10px;">Dress Code</h4>
                <p style="font-size:0.9rem;opacity:0.9;">Chic decontracte<br>Tons naturels apprecies</p>
            </div>
        </div>

        <!-- Palette couleurs -->
        <div style="margin-top:40px;">
            <p style="font-size:0.85rem;letter-spacing:0.1em;text-transform:uppercase;margin-bottom:15px;opacity:0.8;">Palette suggeree</p>
            <div class="color-palette">
                <div class="color-swatch" style="background:#FAF8F3;"></div>
                <div class="color-swatch" style="background:#9BA88A;"></div>
                <div class="color-swatch" style="background:#C4856A;"></div>
                <div class="color-swatch" style="background:#E0CFA0;"></div>
            </div>
        </div>
    </div>
</section>

<!-- RSVP Section -->
<section class="rsvp-section section-sage" id="rsvp">
    <div style="max-width:600px;margin:0 auto;text-align:center;">
        <p class="script-text" style="font-size:2rem;margin-bottom:10px;color:var(--gold-light);">Repondez</p>
        <h2 style="font-family:var(--font-serif);font-size:2.5rem;letter-spacing:0.1em;margin-bottom:30px;">RSVP</h2>
        <p style="margin-bottom:40px;opacity:0.9;">Merci de confirmer votre presence avant le 1er mars 2027</p>

        <?php echo do_shortcode('[mariage_rsvp]'); ?>
    </div>
</section>

<!-- Footer -->
<footer class="site-footer">
    <!-- Decorations botaniques bas -->
    <div class="botanical-decoration botanical-bottom-left">
        <svg width="100" height="120" viewBox="0 0 100 120" fill="none" stroke="#9BA88A" stroke-width="1" opacity="0.3">
            <path d="M50 0 Q50 40 30 80 Q20 100 10 120"/>
            <ellipse cx="25" cy="55" rx="12" ry="6" transform="rotate(-25 25 55)"/>
            <ellipse cx="40" cy="85" rx="10" ry="5" transform="rotate(-30 40 85)"/>
        </svg>
    </div>
    <div class="botanical-decoration botanical-bottom-right">
        <svg width="100" height="120" viewBox="0 0 100 120" fill="none" stroke="#9BA88A" stroke-width="1" opacity="0.3">
            <path d="M50 0 Q50 40 70 80 Q80 100 90 120"/>
            <ellipse cx="75" cy="55" rx="12" ry="6" transform="rotate(25 75 55)"/>
            <ellipse cx="60" cy="85" rx="10" ry="5" transform="rotate(30 60 85)"/>
        </svg>
    </div>

    <p class="footer-names"><?php echo esc_html($prenom1); ?> & <?php echo esc_html($prenom2); ?></p>
    <p class="footer-date"><?php echo esc_html($date_display); ?></p>
</footer>
