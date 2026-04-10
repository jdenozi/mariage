<?php
$title    = get_theme_mod('photos_title', 'Photos & Videos');
$subtitle = get_theme_mod('photos_subtitle', 'Partagez vos plus beaux moments de cette journee');
$btn_text = get_theme_mod('photos_btn_text', 'Envoyer mes photos');
?>
<section class="section section--alt" id="photos">
    <canvas class="section-canvas" id="canvas-photos"></canvas>
    <div class="section-container">
        <div class="section-header fade-in">
            <?php get_template_part('template-parts/floral-frame'); ?>
            <h2 class="section-title"><?php echo esc_html($title); ?></h2>
            <p class="section-subtitle"><?php echo esc_html($subtitle); ?></p>
        </div>

        <div class="fade-in">
            <form id="photos-upload-form" enctype="multipart/form-data">
                <div class="photos-upload-area" id="upload-area">
                    <div class="upload-icon">&#128247;</div>
                    <p><strong>Cliquez ou glissez vos fichiers ici</strong></p>
                    <p class="form-note">Photos (JPG, PNG) et videos (MP4, MOV) — max 50 Mo par fichier</p>
                    <input type="file" id="photo-input" name="photos[]" multiple
                           accept="image/jpeg,image/png,image/webp,video/mp4,video/quicktime,video/webm"
                           style="display:none;">
                </div>

                <div class="form-group">
                    <label for="photo-nom">Votre nom</label>
                    <input type="text" id="photo-nom" name="nom_invite" placeholder="Pour que l'on sache qui a pris cette merveille !">
                </div>

                <div class="upload-progress" id="upload-progress">
                    <div class="progress-bar">
                        <div class="progress-bar-fill" id="progress-bar-fill"></div>
                    </div>
                    <p class="form-note" id="upload-status">Envoi en cours...</p>
                </div>

                <div class="text-center">
                    <button type="submit" class="btn btn-primary" id="upload-btn"><?php echo esc_html($btn_text); ?></button>
                </div>

                <div id="photos-message" class="form-message"></div>
            </form>
        </div>

        <!-- Galerie -->
        <div class="photos-gallery" id="photos-gallery">
            <?php
            global $wpdb;
            $table = $wpdb->prefix . 'mariage_photos';
            if ($wpdb->get_var("SHOW TABLES LIKE '$table'") === $table) {
                $photos = $wpdb->get_results("SELECT * FROM $table ORDER BY created_at DESC");
                if ($photos) {
                    foreach ($photos as $photo) {
                        $is_video = strpos($photo->file_type, 'video') !== false;
                        ?>
                        <div class="gallery-item">
                            <?php if ($is_video): ?>
                                <video src="<?php echo esc_url($photo->file_url); ?>" controls></video>
                            <?php else: ?>
                                <img src="<?php echo esc_url($photo->file_url); ?>"
                                     alt="Photo de <?php echo esc_attr($photo->nom_invite); ?>">
                            <?php endif; ?>
                            <?php if ($photo->nom_invite): ?>
                                <div class="photo-author"><?php echo esc_html($photo->nom_invite); ?></div>
                            <?php endif; ?>
                        </div>
                        <?php
                    }
                }
            }
            ?>
        </div>
    </div>
</section>
