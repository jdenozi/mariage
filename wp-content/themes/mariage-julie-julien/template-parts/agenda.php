<?php
$title    = get_theme_mod('agenda_title', 'Programme de la journee');
$subtitle = get_theme_mod('agenda_subtitle', "Merci d'arriver imperativement a 11h30");
$events_raw = get_theme_mod('agenda_events', "11h30|Accueil des invites\n12h00|Ceremonie\n13h00|Vin d'honneur\n16h00|Diner\n22h00|Soiree dansante");
$events = array_filter(array_map('trim', explode("\n", $events_raw)));
?>
<section class="section section--alt" id="agenda">
    <canvas class="section-canvas" id="canvas-agenda"></canvas>
    <div class="section-container">
        <div class="section-header fade-in">
            <?php get_template_part('template-parts/floral-frame'); ?>
            <h2 class="section-title"><?php echo esc_html($title); ?></h2>
            <p class="section-subtitle"><?php echo esc_html($subtitle); ?></p>
        </div>

        <div class="timeline">
            <?php foreach ($events as $event) :
                $parts = explode('|', $event, 2);
                if (count($parts) < 2) continue;
                $time  = trim($parts[0]);
                $label = trim($parts[1]);
            ?>
            <div class="timeline-item fade-in">
                <div class="timeline-time"><?php echo esc_html($time); ?></div>
                <div class="timeline-dot"></div>
                <div class="timeline-content">
                    <h3><?php echo esc_html($label); ?></h3>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
