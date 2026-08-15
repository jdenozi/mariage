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
    $page_url = get_permalink($post->ID);
    ?>
    <p><strong>Glissez-deposez</strong> les images sur la zone de previsualisation. Cliquez sur une image pour modifier sa taille ou la supprimer.</p>

    <div class="deco-editor">
        <div class="deco-toolbar">
            <button type="button" class="button button-primary" id="add-decoration-btn">+ Ajouter une image</button>
            <span class="deco-help">Astuce: Glissez les images pour les positionner</span>
        </div>

        <div class="deco-canvas-container">
            <div class="deco-canvas" id="deco-canvas">
                <?php foreach ($decorations as $i => $deco):
                    if (empty($deco['image'])) continue;
                    $style = sprintf(
                        'left:%s%%;top:%s%%;width:%dpx;opacity:%s;z-index:%d;',
                        esc_attr($deco['left'] ?? 10),
                        esc_attr($deco['top'] ?? 10),
                        absint($deco['size'] ?? 150),
                        (absint($deco['opacity'] ?? 100) / 100),
                        intval($deco['zindex'] ?? 1)
                    );
                ?>
                    <div class="deco-item" data-index="<?php echo $i; ?>" style="<?php echo $style; ?>">
                        <img src="<?php echo esc_url($deco['image']); ?>" alt="" draggable="false">
                        <input type="hidden" name="decorations[<?php echo $i; ?>][image]" value="<?php echo esc_attr($deco['image']); ?>">
                        <input type="hidden" name="decorations[<?php echo $i; ?>][left]" value="<?php echo esc_attr($deco['left'] ?? 10); ?>" class="deco-left">
                        <input type="hidden" name="decorations[<?php echo $i; ?>][top]" value="<?php echo esc_attr($deco['top'] ?? 10); ?>" class="deco-top">
                        <input type="hidden" name="decorations[<?php echo $i; ?>][size]" value="<?php echo esc_attr($deco['size'] ?? 150); ?>" class="deco-size">
                        <input type="hidden" name="decorations[<?php echo $i; ?>][opacity]" value="<?php echo esc_attr($deco['opacity'] ?? 100); ?>" class="deco-opacity">
                        <input type="hidden" name="decorations[<?php echo $i; ?>][zindex]" value="<?php echo esc_attr($deco['zindex'] ?? 1); ?>" class="deco-zindex">
                        <div class="deco-item-controls">
                            <button type="button" class="deco-resize" data-action="smaller" title="Reduire">−</button>
                            <button type="button" class="deco-resize" data-action="bigger" title="Agrandir">+</button>
                            <button type="button" class="deco-delete" title="Supprimer">×</button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <style>
        .deco-editor { margin-top: 15px; }
        .deco-toolbar { display: flex; align-items: center; gap: 15px; margin-bottom: 10px; }
        .deco-help { color: #666; font-style: italic; font-size: 12px; }
        .deco-canvas-container { border: 2px dashed #ccc; border-radius: 8px; background: #f0f0f0; overflow: hidden; }
        .deco-canvas {
            position: relative;
            width: 100%;
            height: 500px;
            background: linear-gradient(135deg, #fdfcf5 0%, #f5f4ed 100%);
            overflow: hidden;
        }
        .deco-item {
            position: absolute;
            cursor: move;
            user-select: none;
            transition: box-shadow 0.2s, transform 0.1s;
        }
        .deco-item:hover { z-index: 999 !important; }
        .deco-item.dragging {
            opacity: 0.8;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
            transform: scale(1.05);
        }
        .deco-item.selected {
            outline: 3px solid #0073aa;
            outline-offset: 3px;
        }
        .deco-item img {
            width: 100%;
            height: auto;
            display: block;
            pointer-events: none;
        }
        .deco-item-controls {
            position: absolute;
            top: -30px;
            left: 50%;
            transform: translateX(-50%);
            display: none;
            gap: 5px;
            background: #fff;
            padding: 3px 8px;
            border-radius: 4px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.2);
        }
        .deco-item:hover .deco-item-controls,
        .deco-item.selected .deco-item-controls { display: flex; }
        .deco-item-controls button {
            width: 24px;
            height: 24px;
            border: none;
            background: #f0f0f0;
            cursor: pointer;
            border-radius: 3px;
            font-size: 16px;
            line-height: 1;
        }
        .deco-item-controls button:hover { background: #ddd; }
        .deco-delete:hover { background: #e74c3c !important; color: #fff; }
    </style>

    <script>
    jQuery(function($) {
        var canvas = $('#deco-canvas');
        var decoIndex = <?php echo count($decorations); ?>;

        // Add new decoration
        $('#add-decoration-btn').on('click', function() {
            var frame = wp.media({
                title: 'Choisir une image decorative',
                button: { text: 'Ajouter' },
                multiple: false,
                library: { type: 'image' }
            });

            frame.on('select', function() {
                var attachment = frame.state().get('selection').first().toJSON();
                var idx = decoIndex++;
                var item = $('<div class="deco-item" data-index="' + idx + '" style="left:10%;top:10%;width:150px;opacity:1;z-index:1;">' +
                    '<img src="' + attachment.url + '" draggable="false">' +
                    '<input type="hidden" name="decorations[' + idx + '][image]" value="' + attachment.url + '">' +
                    '<input type="hidden" name="decorations[' + idx + '][left]" value="10" class="deco-left">' +
                    '<input type="hidden" name="decorations[' + idx + '][top]" value="10" class="deco-top">' +
                    '<input type="hidden" name="decorations[' + idx + '][size]" value="150" class="deco-size">' +
                    '<input type="hidden" name="decorations[' + idx + '][opacity]" value="100" class="deco-opacity">' +
                    '<input type="hidden" name="decorations[' + idx + '][zindex]" value="1" class="deco-zindex">' +
                    '<div class="deco-item-controls">' +
                        '<button type="button" class="deco-resize" data-action="smaller" title="Reduire">−</button>' +
                        '<button type="button" class="deco-resize" data-action="bigger" title="Agrandir">+</button>' +
                        '<button type="button" class="deco-delete" title="Supprimer">×</button>' +
                    '</div>' +
                '</div>');
                canvas.append(item);
                initDraggable(item);
            });

            frame.open();
        });

        // Initialize drag for existing items
        $('.deco-item').each(function() {
            initDraggable($(this));
        });

        function initDraggable(item) {
            var isDragging = false;
            var startX, startY, startLeft, startTop;

            item.on('mousedown', function(e) {
                if ($(e.target).closest('.deco-item-controls').length) return;

                isDragging = true;
                item.addClass('dragging');

                var rect = canvas[0].getBoundingClientRect();
                startX = e.clientX;
                startY = e.clientY;
                startLeft = parseFloat(item.css('left')) / canvas.width() * 100;
                startTop = parseFloat(item.css('top')) / canvas.height() * 100;

                e.preventDefault();
            });

            $(document).on('mousemove', function(e) {
                if (!isDragging) return;

                var dx = (e.clientX - startX) / canvas.width() * 100;
                var dy = (e.clientY - startY) / canvas.height() * 100;

                var newLeft = Math.max(-20, Math.min(100, startLeft + dx));
                var newTop = Math.max(-20, Math.min(100, startTop + dy));

                item.css({ left: newLeft + '%', top: newTop + '%' });
                item.find('.deco-left').val(newLeft.toFixed(1));
                item.find('.deco-top').val(newTop.toFixed(1));
            });

            $(document).on('mouseup', function() {
                if (isDragging) {
                    isDragging = false;
                    item.removeClass('dragging');
                }
            });
        }

        // Resize buttons
        canvas.on('click', '.deco-resize', function() {
            var item = $(this).closest('.deco-item');
            var sizeInput = item.find('.deco-size');
            var currentSize = parseInt(sizeInput.val());
            var action = $(this).data('action');

            var newSize = action === 'bigger' ? currentSize + 30 : currentSize - 30;
            newSize = Math.max(30, Math.min(600, newSize));

            sizeInput.val(newSize);
            item.css('width', newSize + 'px');
        });

        // Delete button
        canvas.on('click', '.deco-delete', function() {
            $(this).closest('.deco-item').remove();
        });
    });
    </script>
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
                    'left'    => floatval($deco['left'] ?? 10),
                    'top'     => floatval($deco['top'] ?? 10),
                    'size'    => absint($deco['size'] ?? 150),
                    'opacity' => absint($deco['opacity'] ?? 100),
                    'zindex'  => intval($deco['zindex'] ?? 1),
                ];
            }
        }
    }

    update_post_meta($post_id, '_mariage_decorations', $decorations);
}
add_action('save_post', 'mariage_save_decorations_meta');
