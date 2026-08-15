<?php
/**
 * Mariage Theme - Admin
 */

// Register admin menu (simplifie)
function mariage_admin_menu() {
    add_menu_page(
        'Mariage',
        'Mariage',
        'manage_options',
        'mariage-rsvp',
        'mariage_rsvp_page',
        'dashicons-heart',
        2
    );

    add_submenu_page('mariage-rsvp', 'Reponses RSVP', 'Reponses RSVP', 'manage_options', 'mariage-rsvp', 'mariage_rsvp_page');
    add_submenu_page('mariage-rsvp', 'Gestion Photos', 'Photos', 'manage_options', 'mariage-photos-admin', 'mariage_photos_page');
}
add_action('admin_menu', 'mariage_admin_menu');

// Enqueue admin assets
function mariage_admin_assets($hook) {
    $allowed = [
        'toplevel_page_mariage-rsvp',
        'mariage_page_mariage-photos-admin',
        'post.php',
        'post-new.php',
    ];
    if (!in_array($hook, $allowed)) return;
    wp_enqueue_media();
    wp_enqueue_style('mariage-admin', get_template_directory_uri() . '/assets/css/admin.css', [], '1.4');
    wp_enqueue_script('mariage-admin', get_template_directory_uri() . '/assets/js/admin.js', ['jquery'], '1.4', true);
    wp_localize_script('mariage-admin', 'mariageAdmin', [
        'ajaxurl' => admin_url('admin-ajax.php'),
        'nonce'   => wp_create_nonce('mariage_admin_nonce'),
    ]);
}
add_action('admin_enqueue_scripts', 'mariage_admin_assets');

// Delete photo AJAX
function mariage_delete_photo() {
    check_ajax_referer('mariage_admin_nonce', 'nonce');
    if (!current_user_can('manage_options')) wp_send_json_error();

    global $wpdb;
    $id = absint($_POST['photo_id']);
    $table = $wpdb->prefix . 'mariage_photos';
    $photo = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table WHERE id = %d", $id));

    if ($photo) {
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
    $headers = ['Email', 'Presence', 'Nb personnes', 'Membres', 'Allergies', 'Enfants', 'Nb enfants', 'Transport', 'Discours', 'Commentaire', 'Date'];

    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=' . $filename);
    $output = fopen('php://output', 'w');
    fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));
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
            isset($row->transport) ? $row->transport : '',
            isset($row->discours) ? ($row->discours === 'oui' ? 'Oui' : 'Non') : 'Non',
            isset($row->commentaire) ? $row->commentaire : '',
            $row->created_at,
        ], ';');
    }
    fclose($output);
    exit;
}
add_action('admin_init', 'mariage_export_csv');

// ==========================================
// PAGE: RSVP Responses
// ==========================================
function mariage_rsvp_page() {
    global $wpdb;
    $table = $wpdb->prefix . 'mariage_rsvp';

    if ($wpdb->get_var("SHOW TABLES LIKE '$table'") !== $table) {
        echo '<div class="wrap"><h1>Reponses</h1><p>La table n\'existe pas encore.</p></div>';
        return;
    }

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

    $total_oui = $total_non = $total_personnes = $total_enfants = $total_allergies = $total_discours = $total_voiture = 0;
    foreach ($results as $r) {
        if ($r->presence === 'oui') {
            $total_oui++;
            $total_personnes += $r->nb_personnes;
            if (isset($r->nb_enfants)) $total_enfants += $r->nb_enfants;
            if (isset($r->discours) && $r->discours === 'oui') $total_discours++;
            if (isset($r->transport) && $r->transport === 'voiture') $total_voiture++;
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
        <h1>Reponses RSVP</h1>

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
            <div class="mariage-stat mariage-stat--blue">
                <span class="mariage-stat-number"><?php echo $total_voiture; ?>/15</span>
                <span class="mariage-stat-label">Parking</span>
            </div>
        </div>

        <p><a href="<?php echo esc_url($export_url); ?>" class="button">Exporter en CSV</a></p>

        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th>Email</th>
                    <th>Presence</th>
                    <th>Membres</th>
                    <th>Enfants</th>
                    <th>Transport</th>
                    <th>Discours</th>
                    <th>Commentaire</th>
                    <th>Date</th>
                    <th width="50"></th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($results)): ?>
                    <tr><td colspan="9">Aucune reponse pour le moment.</td></tr>
                <?php else: ?>
                    <?php foreach ($results as $row):
                        $membres = [];
                        if (!empty($row->membres_groupe)) {
                            $membres = json_decode($row->membres_groupe, true);
                            if (!is_array($membres)) $membres = [];
                        }
                    ?>
                        <tr id="rsvp-row-<?php echo $row->id; ?>">
                            <td><a href="mailto:<?php echo esc_attr($row->email); ?>"><?php echo esc_html($row->email); ?></a></td>
                            <td>
                                <span class="mariage-badge mariage-badge--<?php echo $row->presence === 'oui' ? 'green' : 'red'; ?>">
                                    <?php echo $row->presence === 'oui' ? 'Oui (' . $row->nb_personnes . ')' : 'Non'; ?>
                                </span>
                            </td>
                            <td>
                                <?php foreach ($membres as $m):
                                    $nom = is_array($m) ? $m['nom'] : $m;
                                    $has_allergy = is_array($m) && isset($m['allergies']) && $m['allergies'] === 'oui';
                                ?>
                                    <div><?php echo esc_html($nom); ?>
                                    <?php if ($has_allergy): ?><small style="color:#b32d2e;"> (allergies: <?php echo esc_html($m['texte_allergies'] ?? ''); ?>)</small><?php endif; ?>
                                    </div>
                                <?php endforeach; ?>
                            </td>
                            <td><?php echo isset($row->enfants) && $row->enfants === 'oui' ? $row->nb_enfants : '-'; ?></td>
                            <td><?php echo esc_html($row->transport ?? '-'); ?></td>
                            <td><?php echo isset($row->discours) && $row->discours === 'oui' ? 'Oui' : '-'; ?></td>
                            <td><?php echo esc_html($row->commentaire ?? ''); ?></td>
                            <td><?php echo date('d/m/Y', strtotime($row->created_at)); ?></td>
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
        echo '<div class="wrap"><h1>Photos</h1><p>La table n\'existe pas encore.</p></div>';
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
                            <small><?php echo date('d/m/Y', strtotime($photo->created_at)); ?></small>
                        </div>
                        <button type="button" class="button mariage-delete-photo-btn" data-id="<?php echo $photo->id; ?>">
                            Supprimer
                        </button>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
    <?php
}

// ==========================================
// META BOX: Decorations par page
// ==========================================
function mariage_register_decorations_metabox() {
    add_meta_box(
        'mariage_decorations',
        'Images decoratives',
        'mariage_decorations_metabox_html',
        'page',
        'normal',
        'high'
    );
}
add_action('add_meta_boxes', 'mariage_register_decorations_metabox');

function mariage_decorations_metabox_html($post) {
    wp_nonce_field('mariage_decorations_save', 'mariage_decorations_nonce');
    $decorations = get_post_meta($post->ID, '_mariage_decorations', true);
    if (!is_array($decorations)) $decorations = [];
    ?>
    <p>Placez des images librement sur cette page. Les positions sont en % de la page.</p>

    <div id="decorations-list">
        <?php foreach ($decorations as $i => $deco): ?>
            <div class="decoration-item" data-index="<?php echo $i; ?>">
                <div class="decoration-preview">
                    <?php if (!empty($deco['image'])): ?>
                        <img src="<?php echo esc_url($deco['image']); ?>" alt="">
                    <?php endif; ?>
                </div>
                <div class="decoration-fields">
                    <input type="hidden" name="decorations[<?php echo $i; ?>][image]" value="<?php echo esc_attr($deco['image'] ?? ''); ?>" class="deco-image-input">
                    <button type="button" class="button deco-upload-btn">Choisir image</button>

                    <div class="decoration-position">
                        <label>
                            <select name="decorations[<?php echo $i; ?>][pos_v]">
                                <option value="top" <?php selected($deco['pos_v'] ?? 'top', 'top'); ?>>Haut</option>
                                <option value="bottom" <?php selected($deco['pos_v'] ?? 'top', 'bottom'); ?>>Bas</option>
                            </select>
                            <input type="number" name="decorations[<?php echo $i; ?>][v_value]" value="<?php echo esc_attr($deco['v_value'] ?? 10); ?>" class="small-text"> %
                        </label>
                        <label>
                            <select name="decorations[<?php echo $i; ?>][pos_h]">
                                <option value="left" <?php selected($deco['pos_h'] ?? 'left', 'left'); ?>>Gauche</option>
                                <option value="right" <?php selected($deco['pos_h'] ?? 'left', 'right'); ?>>Droite</option>
                            </select>
                            <input type="number" name="decorations[<?php echo $i; ?>][h_value]" value="<?php echo esc_attr($deco['h_value'] ?? 0); ?>" class="small-text"> %
                        </label>
                        <label>Taille: <input type="number" name="decorations[<?php echo $i; ?>][size]" value="<?php echo esc_attr($deco['size'] ?? 150); ?>" class="small-text"> px</label>
                        <label>Opacite: <input type="number" name="decorations[<?php echo $i; ?>][opacity]" value="<?php echo esc_attr($deco['opacity'] ?? 100); ?>" class="small-text"> %</label>
                        <label>Z: <input type="number" name="decorations[<?php echo $i; ?>][zindex]" value="<?php echo esc_attr($deco['zindex'] ?? 0); ?>" class="small-text"></label>
                    </div>
                    <button type="button" class="button button-link-delete deco-remove-btn">Supprimer</button>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <p><button type="button" class="button" id="add-decoration-btn">+ Ajouter une decoration</button></p>

    <template id="decoration-template">
        <div class="decoration-item" data-index="__INDEX__">
            <div class="decoration-preview"></div>
            <div class="decoration-fields">
                <input type="hidden" name="decorations[__INDEX__][image]" value="" class="deco-image-input">
                <button type="button" class="button deco-upload-btn">Choisir image</button>
                <div class="decoration-position">
                    <label><select name="decorations[__INDEX__][pos_v]"><option value="top">Haut</option><option value="bottom">Bas</option></select><input type="number" name="decorations[__INDEX__][v_value]" value="10" class="small-text"> %</label>
                    <label><select name="decorations[__INDEX__][pos_h]"><option value="left">Gauche</option><option value="right">Droite</option></select><input type="number" name="decorations[__INDEX__][h_value]" value="0" class="small-text"> %</label>
                    <label>Taille: <input type="number" name="decorations[__INDEX__][size]" value="150" class="small-text"> px</label>
                    <label>Opacite: <input type="number" name="decorations[__INDEX__][opacity]" value="100" class="small-text"> %</label>
                    <label>Z: <input type="number" name="decorations[__INDEX__][zindex]" value="0" class="small-text"></label>
                </div>
                <button type="button" class="button button-link-delete deco-remove-btn">Supprimer</button>
            </div>
        </div>
    </template>

    <style>
        .decoration-item { display:flex; gap:15px; padding:12px; background:#f9f9f9; border:1px solid #ddd; border-radius:4px; margin-bottom:10px; }
        .decoration-preview { width:70px; height:70px; background:#eee; border-radius:4px; overflow:hidden; flex-shrink:0; }
        .decoration-preview img { width:100%; height:100%; object-fit:contain; }
        .decoration-fields { flex:1; display:flex; flex-direction:column; gap:8px; }
        .decoration-position { display:flex; gap:10px; flex-wrap:wrap; }
        .decoration-position label { display:flex; align-items:center; gap:4px; font-size:12px; }
    </style>
    <?php
}

function mariage_save_decorations_meta($post_id) {
    if (!isset($_POST['mariage_decorations_nonce'])) return;
    if (!wp_verify_nonce($_POST['mariage_decorations_nonce'], 'mariage_decorations_save')) return;
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if (!current_user_can('edit_post', $post_id)) return;

    $decorations = [];
    if (isset($_POST['decorations']) && is_array($_POST['decorations'])) {
        foreach ($_POST['decorations'] as $deco) {
            if (!empty($deco['image'])) {
                $decorations[] = [
                    'image'   => esc_url_raw($deco['image']),
                    'pos_v'   => in_array($deco['pos_v'], ['top', 'bottom']) ? $deco['pos_v'] : 'top',
                    'v_value' => intval($deco['v_value']),
                    'pos_h'   => in_array($deco['pos_h'], ['left', 'right']) ? $deco['pos_h'] : 'left',
                    'h_value' => intval($deco['h_value']),
                    'size'    => absint($deco['size']),
                    'opacity' => absint($deco['opacity']),
                    'zindex'  => intval($deco['zindex']),
                ];
            }
        }
    }

    update_post_meta($post_id, '_mariage_decorations', $decorations);
}
add_action('save_post', 'mariage_save_decorations_meta');
