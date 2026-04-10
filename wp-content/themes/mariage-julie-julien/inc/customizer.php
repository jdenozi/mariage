<?php
/**
 * Mariage Theme - Admin Settings Page
 */

// Register admin menu
function mariage_admin_menu() {
    add_menu_page(
        'Mon Mariage',
        'Mon Mariage',
        'manage_options',
        'mariage-settings',
        'mariage_settings_page',
        'dashicons-heart',
        2
    );

    add_submenu_page('mariage-settings', 'Contenu du site', 'Contenu du site', 'manage_options', 'mariage-settings', 'mariage_settings_page');
    add_submenu_page('mariage-settings', 'Reponses RSVP', 'Reponses', 'manage_options', 'mariage-rsvp', 'mariage_rsvp_page');
    add_submenu_page('mariage-settings', 'Gestion Photos', 'Photos', 'manage_options', 'mariage-photos-admin', 'mariage_photos_page');
}
add_action('admin_menu', 'mariage_admin_menu');

// Enqueue admin assets
function mariage_admin_assets($hook) {
    $allowed = [
        'toplevel_page_mariage-settings',
        'mon-mariage_page_mariage-rsvp',
        'mon-mariage_page_mariage-photos-admin',
    ];
    if (!in_array($hook, $allowed)) return;
    wp_enqueue_media();
    wp_enqueue_style('mariage-admin', get_template_directory_uri() . '/assets/css/admin.css', [], '1.1');
    wp_enqueue_script('mariage-admin', get_template_directory_uri() . '/assets/js/admin.js', ['jquery'], '1.1', true);
    wp_localize_script('mariage-admin', 'mariageAdmin', [
        'ajaxurl' => admin_url('admin-ajax.php'),
        'nonce'   => wp_create_nonce('mariage_admin_nonce'),
    ]);
}
add_action('admin_enqueue_scripts', 'mariage_admin_assets');

// Save settings
function mariage_save_settings() {
    if (!isset($_POST['mariage_settings_nonce']) || !wp_verify_nonce($_POST['mariage_settings_nonce'], 'mariage_save_settings')) {
        return;
    }
    if (!current_user_can('manage_options')) return;

    $fields = [
        // Hero
        'hero_prenom1', 'hero_prenom2', 'hero_date', 'hero_lieu',
        'hero_datetime', 'hero_photo', 'hero_btn_text',
        // Navigation
        'nav_logo', 'nav_label_programme', 'nav_label_lieu', 'nav_label_rsvp',
        'nav_label_questionnaire', 'nav_label_cagnotte', 'nav_label_photos',
        // Agenda
        'agenda_title', 'agenda_subtitle', 'agenda_events',
        // Lieu
        'lieu_title', 'lieu_name', 'lieu_adresse',
        // RSVP
        'rsvp_title', 'rsvp_subtitle', 'rsvp_btn_text',
        // Questionnaire
        'questionnaire_title', 'questionnaire_subtitle', 'questionnaire_note_enfants', 'questionnaire_btn_text',
        // Cagnotte
        'cagnotte_title', 'cagnotte_subtitle', 'cagnotte_text',
        'cagnotte_goal', 'cagnotte_url', 'cagnotte_btn_text', 'cagnotte_image',
        // Photos
        'photos_title', 'photos_subtitle', 'photos_btn_text',
        // Footer
        'footer_names', 'footer_date',
        // Meta
        'site_meta_description',
    ];

    foreach ($fields as $field) {
        if (isset($_POST[$field])) {
            $value = wp_unslash($_POST[$field]);
            if ($field === 'cagnotte_text') {
                set_theme_mod($field, wp_kses_post($value));
            } elseif ($field === 'cagnotte_goal') {
                set_theme_mod($field, absint($value));
            } elseif (in_array($field, ['hero_photo', 'cagnotte_image', 'cagnotte_url'])) {
                set_theme_mod($field, esc_url_raw($value));
            } elseif (in_array($field, ['agenda_events', 'questionnaire_note_enfants'])) {
                set_theme_mod($field, sanitize_textarea_field($value));
            } else {
                set_theme_mod($field, sanitize_text_field($value));
            }
        }
    }

    delete_transient('cagnotte_amount');
    add_settings_error('mariage_settings', 'settings_saved', 'Modifications enregistrees !', 'success');
}
add_action('admin_init', 'mariage_save_settings');

// Delete photo AJAX
function mariage_delete_photo() {
    check_ajax_referer('mariage_admin_nonce', 'nonce');
    if (!current_user_can('manage_options')) wp_send_json_error();

    global $wpdb;
    $id = absint($_POST['photo_id']);
    $table = $wpdb->prefix . 'mariage_photos';
    $photo = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table WHERE id = %d", $id));

    if ($photo) {
        // Try to delete the file
        $upload_dir = wp_upload_dir();
        $file_path = str_replace($upload_dir['baseurl'], $upload_dir['basedir'], $photo->file_url);
        if (file_exists($file_path)) {
            wp_delete_file($file_path);
        }
        $wpdb->delete($table, ['id' => $id], ['%d']);
    }

    wp_send_json_success();
}
add_action('wp_ajax_mariage_delete_photo', 'mariage_delete_photo');

// Delete RSVP AJAX
function mariage_delete_rsvp() {
    check_ajax_referer('mariage_admin_nonce', 'nonce');
    if (!current_user_can('manage_options')) wp_send_json_error();

    global $wpdb;
    $wpdb->delete($wpdb->prefix . 'mariage_rsvp', ['id' => absint($_POST['item_id'])], ['%d']);
    wp_send_json_success();
}
add_action('wp_ajax_mariage_delete_rsvp', 'mariage_delete_rsvp');

// Delete Questionnaire AJAX
function mariage_delete_questionnaire() {
    check_ajax_referer('mariage_admin_nonce', 'nonce');
    if (!current_user_can('manage_options')) wp_send_json_error();

    global $wpdb;
    $wpdb->delete($wpdb->prefix . 'mariage_questionnaire', ['id' => absint($_POST['item_id'])], ['%d']);
    wp_send_json_success();
}
add_action('wp_ajax_mariage_delete_questionnaire', 'mariage_delete_questionnaire');

// Export CSV
function mariage_export_csv() {
    if (!isset($_GET['mariage_export']) || !current_user_can('manage_options')) return;
    if (!wp_verify_nonce($_GET['_wpnonce'], 'mariage_export')) return;

    global $wpdb;
    $type = sanitize_text_field($_GET['mariage_export']);

    if ($type !== 'rsvp') return;

    $table = $wpdb->prefix . 'mariage_rsvp';
    $results = $wpdb->get_results("SELECT * FROM $table ORDER BY created_at DESC");
    $filename = 'reponses-' . date('Y-m-d') . '.csv';
    $headers = ['Email', 'Presence', 'Nb personnes', 'Membres', 'Allergies', 'Enfants', 'Nb enfants', 'Discours', 'Commentaire', 'Date'];

    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=' . $filename);
    $output = fopen('php://output', 'w');
    fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF)); // UTF-8 BOM for Excel
    fputcsv($output, $headers, ';');

    foreach ($results as $row) {
        $membres = [];
        $allergies_list = [];
        if (!empty($row->membres_groupe)) {
            $group = json_decode($row->membres_groupe, true);
            if (is_array($group)) {
                foreach ($group as $m) {
                    $nom = is_array($m) ? $m['nom'] : $m;
                    $membres[] = $nom;
                    if (is_array($m) && isset($m['allergies']) && $m['allergies'] === 'oui' && !empty($m['texte_allergies'])) {
                        $allergies_list[] = $nom . ': ' . $m['texte_allergies'];
                    }
                }
            }
        }

        fputcsv($output, [
            $row->email,
            $row->presence === 'oui' ? 'Oui' : 'Non',
            $row->nb_personnes,
            implode(', ', $membres),
            !empty($allergies_list) ? implode(' | ', $allergies_list) : 'Aucune',
            isset($row->enfants) ? ($row->enfants === 'oui' ? 'Oui' : 'Non') : 'Non',
            isset($row->nb_enfants) ? $row->nb_enfants : 0,
            isset($row->discours) ? ($row->discours === 'oui' ? 'Oui' : 'Non') : 'Non',
            isset($row->commentaire) ? $row->commentaire : '',
            $row->created_at,
        ], ';');
    }
    fclose($output);
    exit;
}
add_action('admin_init', 'mariage_export_csv');

// Helper
function mariage_mod($key, $default = '') {
    return get_theme_mod($key, $default);
}

// ==========================================
// SETTINGS PAGE
// ==========================================
function mariage_settings_page() {
    $active_tab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : 'hero';

    $tabs = [
        'hero'          => 'Hero',
        'navigation'    => 'Navigation',
        'agenda'        => 'Programme',
        'lieu'          => 'Lieu',
        'rsvp'          => 'RSVP',
        'cagnotte'      => 'Cagnotte',
        'photos'        => 'Photos',
        'footer'        => 'Footer',
    ];
    ?>
    <div class="wrap mariage-admin">
        <h1>Contenu du site</h1>
        <?php settings_errors('mariage_settings'); ?>

        <nav class="nav-tab-wrapper">
            <?php foreach ($tabs as $tab_id => $tab_label) : ?>
                <a href="?page=mariage-settings&tab=<?php echo $tab_id; ?>"
                   class="nav-tab <?php echo $active_tab === $tab_id ? 'nav-tab-active' : ''; ?>">
                    <?php echo $tab_label; ?>
                </a>
            <?php endforeach; ?>
        </nav>

        <form method="post" class="mariage-form">
            <?php wp_nonce_field('mariage_save_settings', 'mariage_settings_nonce'); ?>
            <?php call_user_func('mariage_tab_' . $active_tab); ?>
            <p class="submit">
                <input type="submit" class="button-primary button-hero" value="Enregistrer">
            </p>
        </form>
    </div>
    <?php
}

// ==========================================
// TAB: Hero
// ==========================================
function mariage_tab_hero() {
    $photo = mariage_mod('hero_photo');
    if (!$photo) $photo = get_template_directory_uri() . '/assets/img/hero-bisou.jpg';
    ?>
    <div class="mariage-section">
        <h2>Section Hero (Accueil)</h2>
        <p class="description">Le bandeau principal avec les prenoms, la date et la photo du couple.</p>
        <table class="form-table">
            <tr>
                <th><label for="hero_prenom1">Prenom 1</label></th>
                <td><input type="text" id="hero_prenom1" name="hero_prenom1" class="regular-text" value="<?php echo esc_attr(mariage_mod('hero_prenom1', 'Julie')); ?>"></td>
            </tr>
            <tr>
                <th><label for="hero_prenom2">Prenom 2</label></th>
                <td><input type="text" id="hero_prenom2" name="hero_prenom2" class="regular-text" value="<?php echo esc_attr(mariage_mod('hero_prenom2', 'Julien')); ?>"></td>
            </tr>
            <tr>
                <th><label for="hero_date">Date affichee</label></th>
                <td><input type="text" id="hero_date" name="hero_date" class="regular-text" value="<?php echo esc_attr(mariage_mod('hero_date', '8 mai 2027')); ?>"></td>
            </tr>
            <tr>
                <th><label for="hero_lieu">Lieu</label></th>
                <td><input type="text" id="hero_lieu" name="hero_lieu" class="regular-text" value="<?php echo esc_attr(mariage_mod('hero_lieu', 'Montpellier')); ?>"></td>
            </tr>
            <tr>
                <th><label for="hero_datetime">Compte a rebours</label></th>
                <td>
                    <input type="datetime-local" id="hero_datetime" name="hero_datetime" value="<?php echo esc_attr(mariage_mod('hero_datetime', '2027-05-08T14:00:00')); ?>">
                    <p class="description">Date et heure exactes de la ceremonie</p>
                </td>
            </tr>
            <tr>
                <th><label for="hero_btn_text">Texte du bouton</label></th>
                <td><input type="text" id="hero_btn_text" name="hero_btn_text" class="regular-text" value="<?php echo esc_attr(mariage_mod('hero_btn_text', 'Confirmer ma presence')); ?>"></td>
            </tr>
            <tr>
                <th><label>Photo du couple</label></th>
                <td>
                    <div class="mariage-image-field">
                        <input type="hidden" id="hero_photo" name="hero_photo" value="<?php echo esc_attr(mariage_mod('hero_photo')); ?>">
                        <div class="mariage-image-preview" id="hero_photo_preview">
                            <img src="<?php echo esc_url($photo); ?>" alt="">
                        </div>
                        <div class="mariage-image-buttons">
                            <button type="button" class="button mariage-upload-btn" data-target="hero_photo">Choisir une image</button>
                            <button type="button" class="button mariage-remove-btn" data-target="hero_photo" <?php echo mariage_mod('hero_photo') ? '' : 'style="display:none"'; ?>>Supprimer</button>
                        </div>
                    </div>
                </td>
            </tr>
        </table>
    </div>
    <?php
}

// ==========================================
// TAB: Navigation
// ==========================================
function mariage_tab_navigation() {
    ?>
    <div class="mariage-section">
        <h2>Barre de navigation</h2>
        <p class="description">Le menu en haut du site et les meta-donnees.</p>
        <table class="form-table">
            <tr>
                <th><label for="nav_logo">Logo / Initiales</label></th>
                <td><input type="text" id="nav_logo" name="nav_logo" class="regular-text" value="<?php echo esc_attr(mariage_mod('nav_logo', 'J & J')); ?>"></td>
            </tr>
            <tr>
                <th><label for="nav_label_programme">Lien "Programme"</label></th>
                <td><input type="text" id="nav_label_programme" name="nav_label_programme" class="regular-text" value="<?php echo esc_attr(mariage_mod('nav_label_programme', 'Programme')); ?>"></td>
            </tr>
            <tr>
                <th><label for="nav_label_rsvp">Lien "RSVP"</label></th>
                <td><input type="text" id="nav_label_rsvp" name="nav_label_rsvp" class="regular-text" value="<?php echo esc_attr(mariage_mod('nav_label_rsvp', 'RSVP')); ?>"></td>
            </tr>
            <tr>
                <th><label for="nav_label_questionnaire">Lien "Questionnaire"</label></th>
                <td><input type="text" id="nav_label_questionnaire" name="nav_label_questionnaire" class="regular-text" value="<?php echo esc_attr(mariage_mod('nav_label_questionnaire', 'Questionnaire')); ?>"></td>
            </tr>
            <tr>
                <th><label for="nav_label_cagnotte">Lien "Cagnotte"</label></th>
                <td><input type="text" id="nav_label_cagnotte" name="nav_label_cagnotte" class="regular-text" value="<?php echo esc_attr(mariage_mod('nav_label_cagnotte', 'Cagnotte')); ?>"></td>
            </tr>
            <tr>
                <th><label for="nav_label_photos">Lien "Photos"</label></th>
                <td><input type="text" id="nav_label_photos" name="nav_label_photos" class="regular-text" value="<?php echo esc_attr(mariage_mod('nav_label_photos', 'Photos')); ?>"></td>
            </tr>
            <tr>
                <th><label for="site_meta_description">Meta description</label></th>
                <td>
                    <input type="text" id="site_meta_description" name="site_meta_description" class="large-text" value="<?php echo esc_attr(mariage_mod('site_meta_description', 'Mariage de Julie & Julien - 8 mai 2027')); ?>">
                    <p class="description">Description affichee dans Google et les partages sur les reseaux sociaux</p>
                </td>
            </tr>
        </table>
    </div>
    <?php
}

// ==========================================
// TAB: Agenda
// ==========================================
function mariage_tab_agenda() {
    ?>
    <div class="mariage-section">
        <h2>Programme de la journee</h2>
        <table class="form-table">
            <tr>
                <th><label for="agenda_title">Titre</label></th>
                <td><input type="text" id="agenda_title" name="agenda_title" class="regular-text" value="<?php echo esc_attr(mariage_mod('agenda_title', 'Programme de la journee')); ?>"></td>
            </tr>
            <tr>
                <th><label for="agenda_subtitle">Sous-titre</label></th>
                <td><input type="text" id="agenda_subtitle" name="agenda_subtitle" class="large-text" value="<?php echo esc_attr(mariage_mod('agenda_subtitle', "Merci d'arriver imperativement a 11h30")); ?>"></td>
            </tr>
            <tr>
                <th><label for="agenda_events">Evenements</label></th>
                <td>
                    <textarea id="agenda_events" name="agenda_events" rows="8" class="large-text"><?php echo esc_textarea(mariage_mod('agenda_events', "11h30|Accueil des invites\n12h00|Ceremonie\n13h00|Vin d'honneur\n16h00|Diner\n22h00|Soiree dansante")); ?></textarea>
                    <p class="description">Un evenement par ligne, format : <code>heure|titre</code></p>
                </td>
            </tr>
        </table>
    </div>
    <?php
}

// ==========================================
// TAB: Lieu
// ==========================================
function mariage_tab_lieu() {
    ?>
    <div class="mariage-section">
        <h2>Lieu de reception</h2>
        <table class="form-table">
            <tr>
                <th><label for="lieu_title">Titre de la section</label></th>
                <td><input type="text" id="lieu_title" name="lieu_title" class="regular-text" value="<?php echo esc_attr(mariage_mod('lieu_title', 'Le Lieu')); ?>"></td>
            </tr>
            <tr>
                <th><label for="lieu_name">Nom du lieu</label></th>
                <td><input type="text" id="lieu_name" name="lieu_name" class="regular-text" value="<?php echo esc_attr(mariage_mod('lieu_name', 'Domaine de la Tour')); ?>"></td>
            </tr>
            <tr>
                <th><label for="lieu_adresse">Adresse</label></th>
                <td>
                    <input type="text" id="lieu_adresse" name="lieu_adresse" class="large-text" value="<?php echo esc_attr(mariage_mod('lieu_adresse', '5 rue du Pas du Loup, Domaine de la Tour, 34070 Montpellier')); ?>">
                    <p class="description">Adresse complete utilisee pour la carte Google Maps et le lien itineraire</p>
                </td>
            </tr>
        </table>
    </div>
    <?php
}

// ==========================================
// TAB: RSVP
// ==========================================
function mariage_tab_rsvp() {
    ?>
    <div class="mariage-section">
        <h2>Formulaire RSVP</h2>
        <table class="form-table">
            <tr>
                <th><label for="rsvp_title">Titre</label></th>
                <td><input type="text" id="rsvp_title" name="rsvp_title" class="regular-text" value="<?php echo esc_attr(mariage_mod('rsvp_title', 'Confirmez votre presence')); ?>"></td>
            </tr>
            <tr>
                <th><label for="rsvp_subtitle">Sous-titre</label></th>
                <td><input type="text" id="rsvp_subtitle" name="rsvp_subtitle" class="large-text" value="<?php echo esc_attr(mariage_mod('rsvp_subtitle', 'Merci de nous confirmer votre venue avant le 1er avril 2027')); ?>"></td>
            </tr>
            <tr>
                <th><label for="rsvp_btn_text">Texte du bouton</label></th>
                <td><input type="text" id="rsvp_btn_text" name="rsvp_btn_text" class="regular-text" value="<?php echo esc_attr(mariage_mod('rsvp_btn_text', 'Envoyer ma reponse')); ?>"></td>
            </tr>
        </table>
    </div>
    <?php
}

// ==========================================
// TAB: Questionnaire
// ==========================================
function mariage_tab_questionnaire() {
    ?>
    <div class="mariage-section">
        <h2>Formulaire Questionnaire</h2>
        <table class="form-table">
            <tr>
                <th><label for="questionnaire_title">Titre</label></th>
                <td><input type="text" id="questionnaire_title" name="questionnaire_title" class="regular-text" value="<?php echo esc_attr(mariage_mod('questionnaire_title', 'Quelques questions')); ?>"></td>
            </tr>
            <tr>
                <th><label for="questionnaire_subtitle">Sous-titre</label></th>
                <td><input type="text" id="questionnaire_subtitle" name="questionnaire_subtitle" class="large-text" value="<?php echo esc_attr(mariage_mod('questionnaire_subtitle', 'Pour que cette journee soit parfaite pour tout le monde')); ?>"></td>
            </tr>
            <tr>
                <th><label for="questionnaire_note_enfants">Note sur les enfants</label></th>
                <td><textarea id="questionnaire_note_enfants" name="questionnaire_note_enfants" rows="4" class="large-text"><?php echo esc_textarea(mariage_mod('questionnaire_note_enfants', "Nous preferons que cette journee soit une occasion pour les adultes de profiter pleinement de la fete. Si vous le pouvez, nous vous encourageons a faire garder vos enfants pour cette soiree. Merci de votre comprehension !")); ?></textarea></td>
            </tr>
            <tr>
                <th><label for="questionnaire_btn_text">Texte du bouton</label></th>
                <td><input type="text" id="questionnaire_btn_text" name="questionnaire_btn_text" class="regular-text" value="<?php echo esc_attr(mariage_mod('questionnaire_btn_text', 'Envoyer mes reponses')); ?>"></td>
            </tr>
        </table>
    </div>
    <?php
}

// ==========================================
// TAB: Cagnotte
// ==========================================
function mariage_tab_cagnotte() {
    $image = mariage_mod('cagnotte_image');
    if (!$image) $image = get_template_directory_uri() . '/assets/img/nepal.jpg';
    ?>
    <div class="mariage-section">
        <h2>Cagnotte</h2>
        <table class="form-table">
            <tr>
                <th><label for="cagnotte_title">Titre</label></th>
                <td><input type="text" id="cagnotte_title" name="cagnotte_title" class="regular-text" value="<?php echo esc_attr(mariage_mod('cagnotte_title', 'Cagnotte')); ?>"></td>
            </tr>
            <tr>
                <th><label for="cagnotte_subtitle">Sous-titre</label></th>
                <td><input type="text" id="cagnotte_subtitle" name="cagnotte_subtitle" class="large-text" value="<?php echo esc_attr(mariage_mod('cagnotte_subtitle', 'Votre presence est notre plus beau cadeau')); ?>"></td>
            </tr>
            <tr>
                <th><label for="cagnotte_text">Texte descriptif</label></th>
                <td>
                    <textarea id="cagnotte_text" name="cagnotte_text" rows="5" class="large-text"><?php echo esc_textarea(mariage_mod('cagnotte_text', "Votre presence a nos cotes est le plus beau des cadeaux.<br><br>Nous revons de partir au <strong>Nepal</strong> pour notre lune de miel ! Si vous souhaitez contribuer a cette aventure, une cagnotte est a votre disposition.")); ?></textarea>
                    <p class="description">HTML autorise : <code>&lt;strong&gt;</code>, <code>&lt;br&gt;</code>, <code>&lt;em&gt;</code></p>
                </td>
            </tr>
            <tr>
                <th><label for="cagnotte_goal">Objectif</label></th>
                <td><input type="number" id="cagnotte_goal" name="cagnotte_goal" class="small-text" min="0" value="<?php echo esc_attr(mariage_mod('cagnotte_goal', 5000)); ?>"> &euro;</td>
            </tr>
            <tr>
                <th><label for="cagnotte_url">Lien de la cagnotte</label></th>
                <td><input type="url" id="cagnotte_url" name="cagnotte_url" class="large-text" value="<?php echo esc_attr(mariage_mod('cagnotte_url', 'https://www.ungrandjour.com/fr/mariage-julie-julien-montpellier')); ?>"></td>
            </tr>
            <tr>
                <th><label for="cagnotte_btn_text">Texte du bouton</label></th>
                <td><input type="text" id="cagnotte_btn_text" name="cagnotte_btn_text" class="regular-text" value="<?php echo esc_attr(mariage_mod('cagnotte_btn_text', 'Participer a la cagnotte')); ?>"></td>
            </tr>
            <tr>
                <th><label>Image destination</label></th>
                <td>
                    <div class="mariage-image-field">
                        <input type="hidden" id="cagnotte_image" name="cagnotte_image" value="<?php echo esc_attr(mariage_mod('cagnotte_image')); ?>">
                        <div class="mariage-image-preview" id="cagnotte_image_preview">
                            <img src="<?php echo esc_url($image); ?>" alt="">
                        </div>
                        <div class="mariage-image-buttons">
                            <button type="button" class="button mariage-upload-btn" data-target="cagnotte_image">Choisir une image</button>
                            <button type="button" class="button mariage-remove-btn" data-target="cagnotte_image" <?php echo mariage_mod('cagnotte_image') ? '' : 'style="display:none"'; ?>>Supprimer</button>
                        </div>
                    </div>
                </td>
            </tr>
        </table>
    </div>
    <?php
}

// ==========================================
// TAB: Photos
// ==========================================
function mariage_tab_photos() {
    ?>
    <div class="mariage-section">
        <h2>Section Photos & Videos</h2>
        <table class="form-table">
            <tr>
                <th><label for="photos_title">Titre</label></th>
                <td><input type="text" id="photos_title" name="photos_title" class="regular-text" value="<?php echo esc_attr(mariage_mod('photos_title', 'Photos & Videos')); ?>"></td>
            </tr>
            <tr>
                <th><label for="photos_subtitle">Sous-titre</label></th>
                <td><input type="text" id="photos_subtitle" name="photos_subtitle" class="large-text" value="<?php echo esc_attr(mariage_mod('photos_subtitle', 'Partagez vos plus beaux moments de cette journee')); ?>"></td>
            </tr>
            <tr>
                <th><label for="photos_btn_text">Texte du bouton</label></th>
                <td><input type="text" id="photos_btn_text" name="photos_btn_text" class="regular-text" value="<?php echo esc_attr(mariage_mod('photos_btn_text', 'Envoyer mes photos')); ?>"></td>
            </tr>
        </table>
    </div>
    <?php
}

// ==========================================
// TAB: Footer
// ==========================================
function mariage_tab_footer() {
    ?>
    <div class="mariage-section">
        <h2>Pied de page</h2>
        <table class="form-table">
            <tr>
                <th><label for="footer_names">Noms</label></th>
                <td><input type="text" id="footer_names" name="footer_names" class="regular-text" value="<?php echo esc_attr(mariage_mod('footer_names', 'Julie & Julien')); ?>"></td>
            </tr>
            <tr>
                <th><label for="footer_date">Date</label></th>
                <td><input type="text" id="footer_date" name="footer_date" class="regular-text" value="<?php echo esc_attr(mariage_mod('footer_date', '8 mai 2027')); ?>"></td>
            </tr>
        </table>
    </div>
    <?php
}

// ==========================================
// PAGE: RSVP Responses
// ==========================================
function mariage_rsvp_page() {
    global $wpdb;
    $table = $wpdb->prefix . 'mariage_rsvp';

    if ($wpdb->get_var("SHOW TABLES LIKE '$table'") !== $table) {
        echo '<div class="wrap"><h1>Reponses</h1><p>La table n\'existe pas encore. Activez le plugin Mariage Forms.</p></div>';
        return;
    }

    // Migrate: add new columns if missing
    $cols = $wpdb->get_col("DESCRIBE $table", 0);
    if (!in_array('membres_groupe', $cols)) {
        $wpdb->query("ALTER TABLE $table ADD COLUMN membres_groupe TEXT AFTER nb_personnes");
    }
    if (!in_array('enfants', $cols)) {
        $wpdb->query("ALTER TABLE $table ADD COLUMN enfants VARCHAR(10) DEFAULT 'non' AFTER membres_groupe");
        $wpdb->query("ALTER TABLE $table ADD COLUMN nb_enfants INT DEFAULT 0 AFTER enfants");
        $wpdb->query("ALTER TABLE $table ADD COLUMN discours VARCHAR(10) DEFAULT 'non' AFTER nb_enfants");
        $wpdb->query("ALTER TABLE $table ADD COLUMN commentaire TEXT AFTER discours");
    }

    $results = $wpdb->get_results("SELECT * FROM $table ORDER BY created_at DESC");

    $total_oui = $total_non = $total_personnes = $total_enfants = $total_allergies = $total_discours = 0;
    foreach ($results as $r) {
        if ($r->presence === 'oui') {
            $total_oui++;
            $total_personnes += $r->nb_personnes;
            if (isset($r->nb_enfants)) $total_enfants += $r->nb_enfants;
            if (isset($r->discours) && $r->discours === 'oui') $total_discours++;
            if (!empty($r->membres_groupe)) {
                $group = json_decode($r->membres_groupe, true);
                if (is_array($group)) {
                    foreach ($group as $m) {
                        if (is_array($m) && isset($m['allergies']) && $m['allergies'] === 'oui') $total_allergies++;
                    }
                }
            }
        } else {
            $total_non++;
        }
    }

    $export_url = wp_nonce_url(admin_url('admin.php?page=mariage-rsvp&mariage_export=rsvp'), 'mariage_export');
    ?>
    <div class="wrap mariage-admin">
        <h1>Reponses</h1>

        <div class="mariage-stats">
            <div class="mariage-stat mariage-stat--green">
                <span class="mariage-stat-number"><?php echo $total_oui; ?></span>
                <span class="mariage-stat-label">Present(s)</span>
            </div>
            <div class="mariage-stat mariage-stat--red">
                <span class="mariage-stat-number"><?php echo $total_non; ?></span>
                <span class="mariage-stat-label">Absent(s)</span>
            </div>
            <div class="mariage-stat mariage-stat--blue">
                <span class="mariage-stat-number"><?php echo $total_personnes; ?></span>
                <span class="mariage-stat-label">Total adultes</span>
            </div>
            <div class="mariage-stat mariage-stat--grey">
                <span class="mariage-stat-number"><?php echo $total_enfants; ?></span>
                <span class="mariage-stat-label">Enfants</span>
            </div>
            <div class="mariage-stat mariage-stat--red">
                <span class="mariage-stat-number"><?php echo $total_allergies; ?></span>
                <span class="mariage-stat-label">Allergies</span>
            </div>
            <div class="mariage-stat mariage-stat--green">
                <span class="mariage-stat-number"><?php echo $total_discours; ?></span>
                <span class="mariage-stat-label">Discours</span>
            </div>
        </div>

        <p><a href="<?php echo esc_url($export_url); ?>" class="button">Exporter en CSV</a></p>

        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th>Email</th>
                    <th>Presence</th>
                    <th>Membres (allergies)</th>
                    <th>Enfants</th>
                    <th>Discours</th>
                    <th>Commentaire</th>
                    <th>Date</th>
                    <th width="80"></th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($results)): ?>
                    <tr><td colspan="8">Aucune reponse pour le moment.</td></tr>
                <?php else: ?>
                    <?php foreach ($results as $row):
                        $membres = [];
                        if (!empty($row->membres_groupe)) {
                            $membres = json_decode($row->membres_groupe, true);
                            if (!is_array($membres)) $membres = [];
                        }
                        $enfants = isset($row->enfants) ? $row->enfants : 'non';
                        $nb_enfants = isset($row->nb_enfants) ? $row->nb_enfants : 0;
                        $discours = isset($row->discours) ? $row->discours : 'non';
                        $commentaire = isset($row->commentaire) ? $row->commentaire : '';
                    ?>
                        <tr id="rsvp-row-<?php echo $row->id; ?>">
                            <td><a href="mailto:<?php echo esc_attr($row->email); ?>"><?php echo esc_html($row->email); ?></a></td>
                            <td>
                                <span class="mariage-badge mariage-badge--<?php echo $row->presence === 'oui' ? 'green' : 'red'; ?>">
                                    <?php echo $row->presence === 'oui' ? 'Oui (' . esc_html($row->nb_personnes) . ')' : 'Non'; ?>
                                </span>
                            </td>
                            <td>
                                <?php if (!empty($membres)): ?>
                                    <?php foreach ($membres as $m):
                                        $nom = is_array($m) ? $m['nom'] : $m;
                                        $has_allergy = is_array($m) && isset($m['allergies']) && $m['allergies'] === 'oui';
                                        $allergy_text = is_array($m) ? ($m['texte_allergies'] ?? '') : '';
                                    ?>
                                        <div style="margin-bottom:4px;">
                                            <strong><?php echo esc_html($nom); ?></strong>
                                            <?php if ($has_allergy && !empty($allergy_text)): ?>
                                                <br><small><span class="mariage-badge mariage-badge--red">Allergies</span> <?php echo esc_html($allergy_text); ?></small>
                                            <?php endif; ?>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <em><?php echo esc_html($row->nom ?: '—'); ?></em>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($enfants === 'oui'): ?>
                                    <?php echo esc_html($nb_enfants); ?> enfant(s)
                                <?php else: ?>
                                    <span style="color:#999;">Non</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="mariage-badge mariage-badge--<?php echo $discours === 'oui' ? 'green' : 'grey'; ?>">
                                    <?php echo $discours === 'oui' ? 'Oui' : 'Non'; ?>
                                </span>
                            </td>
                            <td><?php echo esc_html($commentaire); ?></td>
                            <td><?php echo date('d/m/Y H:i', strtotime($row->created_at)); ?></td>
                            <td>
                                <button type="button" class="button-link mariage-delete-btn" data-type="rsvp" data-id="<?php echo $row->id; ?>">
                                    <span class="dashicons dashicons-trash" style="color:#b32d2e;"></span>
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <?php
}

// ==========================================
// PAGE: Photos Management
// ==========================================
function mariage_photos_page() {
    global $wpdb;
    $table = $wpdb->prefix . 'mariage_photos';

    if ($wpdb->get_var("SHOW TABLES LIKE '$table'") !== $table) {
        echo '<div class="wrap"><h1>Photos</h1><p>La table n\'existe pas encore. Activez le plugin Mariage Forms.</p></div>';
        return;
    }

    $results = $wpdb->get_results("SELECT * FROM $table ORDER BY created_at DESC");
    ?>
    <div class="wrap mariage-admin">
        <h1>Photos & Videos (<?php echo count($results); ?>)</h1>

        <?php if (empty($results)): ?>
            <p>Aucune photo pour le moment.</p>
        <?php else: ?>
            <div class="mariage-photos-grid">
                <?php foreach ($results as $photo): ?>
                    <div class="mariage-photo-card" id="photo-card-<?php echo $photo->id; ?>">
                        <div class="mariage-photo-media">
                            <?php if (strpos($photo->file_type, 'video') !== false): ?>
                                <video src="<?php echo esc_url($photo->file_url); ?>" controls></video>
                            <?php else: ?>
                                <img src="<?php echo esc_url($photo->file_url); ?>" alt="">
                            <?php endif; ?>
                        </div>
                        <div class="mariage-photo-info">
                            <strong><?php echo esc_html($photo->nom_invite ?: 'Anonyme'); ?></strong>
                            <small><?php echo date('d/m/Y H:i', strtotime($photo->created_at)); ?></small>
                        </div>
                        <button type="button" class="button mariage-delete-photo-btn" data-id="<?php echo $photo->id; ?>">
                            <span class="dashicons dashicons-trash"></span> Supprimer
                        </button>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
    <?php
}
