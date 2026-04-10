<?php

class Mariage_Admin_Page {

    public function __construct() {
        add_action('admin_menu', [$this, 'add_menu']);
    }

    public function add_menu() {
        add_menu_page(
            'Mariage - Reponses',
            'Mariage',
            'manage_options',
            'mariage-reponses',
            [$this, 'render_page'],
            'dashicons-heart',
            30
        );

        add_submenu_page(
            'mariage-reponses',
            'RSVP',
            'RSVP',
            'manage_options',
            'mariage-reponses',
            [$this, 'render_page']
        );

        add_submenu_page(
            'mariage-reponses',
            'Photos',
            'Photos',
            'manage_options',
            'mariage-photos',
            [$this, 'render_photos']
        );
    }

    public function render_page() {
        global $wpdb;
        $table = $wpdb->prefix . 'mariage_rsvp';
        Mariage_DB_Setup::create_tables();
        $results = $wpdb->get_results("SELECT * FROM $table ORDER BY created_at DESC");

        $total_oui = 0;
        $total_personnes = 0;
        $total_non = 0;
        $total_enfants = 0;
        foreach ($results as $r) {
            if ($r->presence === 'oui') {
                $total_oui++;
                $total_personnes += $r->nb_personnes;
                if (isset($r->nb_enfants)) $total_enfants += $r->nb_enfants;
            } else {
                $total_non++;
            }
        }
        ?>
        <div class="wrap">
            <h1>RSVP - Reponses</h1>

            <div style="display:flex;gap:20px;margin:20px 0;flex-wrap:wrap;">
                <div style="background:#d4edda;padding:15px 25px;border-radius:8px;">
                    <strong style="font-size:2em;color:#155724;"><?php echo $total_oui; ?></strong><br>
                    <span>Groupe(s) present(s)</span>
                </div>
                <div style="background:#f8d7da;padding:15px 25px;border-radius:8px;">
                    <strong style="font-size:2em;color:#721c24;"><?php echo $total_non; ?></strong><br>
                    <span>Absent(s)</span>
                </div>
                <div style="background:#d1ecf1;padding:15px 25px;border-radius:8px;">
                    <strong style="font-size:2em;color:#0c5460;"><?php echo $total_personnes; ?></strong><br>
                    <span>Total adultes</span>
                </div>
                <div style="background:#fff3cd;padding:15px 25px;border-radius:8px;">
                    <strong style="font-size:2em;color:#856404;"><?php echo $total_enfants; ?></strong><br>
                    <span>Enfants</span>
                </div>
            </div>

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
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($results)): ?>
                        <tr><td colspan="7">Aucune reponse pour le moment.</td></tr>
                    <?php else: ?>
                        <?php foreach ($results as $row):
                            $membres = [];
                            if (!empty($row->membres_groupe)) {
                                $membres = json_decode($row->membres_groupe, true);
                                if (!is_array($membres)) $membres = [];
                            }
                            $nb_enfants = isset($row->nb_enfants) ? $row->nb_enfants : 0;
                            $enfants = isset($row->enfants) ? $row->enfants : 'non';
                            $discours = isset($row->discours) ? $row->discours : 'non';
                            $commentaire = isset($row->commentaire) ? $row->commentaire : '';
                        ?>
                            <tr>
                                <td><?php echo esc_html($row->email); ?></td>
                                <td>
                                    <span style="color:<?php echo $row->presence === 'oui' ? '#155724' : '#721c24'; ?>;font-weight:bold;">
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
                                                    <br><small style="color:#721c24;">Allergies: <?php echo esc_html($allergy_text); ?></small>
                                                <?php endif; ?>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <em><?php echo esc_html($row->nom ?: '—'); ?></em>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($enfants === 'oui'): ?>
                                        Oui (<?php echo esc_html($nb_enfants); ?>)
                                    <?php else: ?>
                                        Non
                                    <?php endif; ?>
                                </td>
                                <td><?php echo $discours === 'oui' ? '<span style="color:#155724;font-weight:bold;">Oui</span>' : 'Non'; ?></td>
                                <td><?php echo esc_html($commentaire); ?></td>
                                <td><?php echo esc_html($row->created_at); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php
    }

    public function render_photos() {
        global $wpdb;
        $table = $wpdb->prefix . 'mariage_photos';
        Mariage_DB_Setup::create_tables();
        $results = $wpdb->get_results("SELECT * FROM $table ORDER BY created_at DESC");
        ?>
        <div class="wrap">
            <h1>Photos & Videos (<?php echo count($results); ?>)</h1>

            <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:15px;margin-top:20px;">
                <?php if (empty($results)): ?>
                    <p>Aucune photo pour le moment.</p>
                <?php else: ?>
                    <?php foreach ($results as $photo): ?>
                        <div style="border:1px solid #ddd;border-radius:8px;overflow:hidden;background:#fff;">
                            <?php if (strpos($photo->file_type, 'video') !== false): ?>
                                <video src="<?php echo esc_url($photo->file_url); ?>" controls style="width:100%;height:200px;object-fit:cover;"></video>
                            <?php else: ?>
                                <img src="<?php echo esc_url($photo->file_url); ?>" style="width:100%;height:200px;object-fit:cover;">
                            <?php endif; ?>
                            <div style="padding:8px;">
                                <strong><?php echo esc_html($photo->nom_invite ?: 'Anonyme'); ?></strong><br>
                                <small><?php echo esc_html($photo->created_at); ?></small>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
        <?php
    }
}
