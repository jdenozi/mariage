<?php
$lieu_title   = get_theme_mod('lieu_title', 'Le Lieu');
$lieu_name    = get_theme_mod('lieu_name', 'Domaine de la Tour');
$lieu_adresse = get_theme_mod('lieu_adresse', '5 rue du Pas du Loup, Domaine de la Tour, 34070 Montpellier');
?>
<section class="section" id="lieu">
    <canvas class="section-canvas" id="canvas-lieu"></canvas>
    <div class="section-container lieu-container">
        <div class="section-header fade-in">
            <?php get_template_part('template-parts/floral-frame'); ?>
            <h2 class="section-title"><?php echo esc_html($lieu_title); ?></h2>
            <p class="section-subtitle"><?php echo esc_html($lieu_name); ?></p>
        </div>

        <p class="lieu-adresse fade-in"><?php echo esc_html($lieu_adresse); ?></p>

        <!-- Carte Google Maps -->
        <div class="lieu-map fade-in">
            <iframe
                src="https://maps.google.com/maps?q=<?php echo urlencode($lieu_adresse); ?>&output=embed"
                width="100%"
                height="350"
                style="border:0;"
                allowfullscreen=""
                loading="lazy"
                referrerpolicy="no-referrer-when-downgrade">
            </iframe>
        </div>

        <!-- Infos pratiques -->
        <div class="lieu-infos fade-in">
            <div class="lieu-info-card">
                <div class="lieu-info-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7z"/>
                        <circle cx="12" cy="9" r="2.5"/>
                    </svg>
                </div>
                <h3>Adresse</h3>
                <p>5 rue du Pas du Loup<br>34070 Montpellier</p>
            </div>
            <div class="lieu-info-card">
                <div class="lieu-info-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M4 11V6a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v5"/>
                        <rect x="3" y="11" width="18" height="7" rx="2"/>
                        <circle cx="7.5" cy="21" r="1.5"/>
                        <circle cx="16.5" cy="21" r="1.5"/>
                        <rect x="7" y="7" width="4" height="3" rx="1"/>
                        <rect x="13" y="7" width="4" height="3" rx="1"/>
                    </svg>
                </div>
                <h3>Tramway</h3>
                <p>Ligne <strong>3</strong> ou <strong>5</strong><br>Arret <strong>Estanove</strong></p>
            </div>
            <div class="lieu-info-card">
                <div class="lieu-info-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M5 10l1-6h12l1 6"/>
                        <rect x="3" y="10" width="18" height="7" rx="2"/>
                        <circle cx="7" cy="20.5" r="1.5"/>
                        <circle cx="17" cy="20.5" r="1.5"/>
                    </svg>
                </div>
                <h3>Parking</h3>
                <p><strong>15 places</strong> sur site</p>
            </div>
        </div>

        <!-- Bouton itineraire -->
        <div class="lieu-actions fade-in">
            <a href="https://www.google.com/maps/dir/?api=1&destination=<?php echo urlencode($lieu_adresse); ?>" target="_blank" rel="noopener" class="btn btn-primary lieu-btn">
                Itineraire
            </a>
        </div>
    </div>
</section>
