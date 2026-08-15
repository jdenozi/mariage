<?php
/**
 * Mariage Theme - Admin RSVP
 */

// Register admin menu
function mariage_admin_menu() {
    add_menu_page(
        'RSVP',
        'RSVP',
        'manage_options',
        'mariage-rsvp',
        'mariage_rsvp_page',
        'dashicons-groups',
        25
    );
}
add_action('admin_menu', 'mariage_admin_menu');

// Enqueue admin assets
function mariage_admin_assets($hook) {
    if ($hook !== 'toplevel_page_mariage-rsvp') return;

    wp_enqueue_style('mariage-admin', get_template_directory_uri() . '/assets/css/admin.css', [], '1.0');
    wp_enqueue_script('mariage-admin', get_template_directory_uri() . '/assets/js/admin.js', ['jquery'], '1.0', true);
    wp_localize_script('mariage-admin', 'mariageAdmin', [
        'ajaxurl' => admin_url('admin-ajax.php'),
        'nonce'   => wp_create_nonce('mariage_admin_nonce'),
    ]);
}
add_action('admin_enqueue_scripts', 'mariage_admin_assets');

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

// RSVP Admin Page
function mariage_rsvp_page() {
    global $wpdb;
    $table = $wpdb->prefix . 'mariage_rsvp';

    if ($wpdb->get_var("SHOW TABLES LIKE '$table'") !== $table) {
        echo '<div class="wrap"><h1>Reponses RSVP</h1><p>La table n\'existe pas encore. Attendez qu\'une premiere reponse soit soumise.</p></div>';
        return;
    }

    // Ensure columns exist
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

    // Statistics
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

        <p><a href="<?php echo esc_url($export_url); ?>" class="button button-primary">Exporter en CSV</a></p>

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
