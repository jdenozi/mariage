<?php

class Mariage_Questionnaire_Handler {

    public function __construct() {
        add_action('wp_ajax_mariage_questionnaire', [$this, 'handle']);
        add_action('wp_ajax_nopriv_mariage_questionnaire', [$this, 'handle']);
    }

    public function handle() {
        check_ajax_referer('mariage_nonce', 'nonce');

        $nom = sanitize_text_field($_POST['nom'] ?? '');
        $nb_personnes = absint($_POST['nb_personnes'] ?? 1);
        $allergies = sanitize_text_field($_POST['allergies'] ?? 'non');
        $texte_allergies = sanitize_textarea_field($_POST['texte_allergies'] ?? '');
        $enfants = sanitize_text_field($_POST['enfants'] ?? 'non');
        $nb_enfants = absint($_POST['nb_enfants'] ?? 0);
        $discours = sanitize_text_field($_POST['discours'] ?? 'non');
        $commentaire = sanitize_textarea_field($_POST['commentaire'] ?? '');

        if (empty($nom)) {
            wp_send_json_error(['message' => 'Veuillez indiquer votre nom.']);
        }

        $valid_values = ['oui', 'non'];
        if (!in_array($allergies, $valid_values, true) ||
            !in_array($enfants, $valid_values, true) ||
            !in_array($discours, $valid_values, true)) {
            wp_send_json_error(['message' => 'Donnees invalides.']);
        }

        // Collect group members
        $membres = [];
        if ($nb_personnes > 1) {
            $raw_membres = isset($_POST['membres']) && is_array($_POST['membres']) ? $_POST['membres'] : [];

            for ($i = 0; $i < $nb_personnes - 1; $i++) {
                $m = $raw_membres[$i] ?? [];
                $m_nom = sanitize_text_field($m['nom'] ?? '');
                if (empty($m_nom)) {
                    wp_send_json_error(['message' => 'Veuillez indiquer le nom de toutes les personnes du groupe.']);
                }

                $m_allergies = sanitize_text_field($m['allergies'] ?? 'non');
                $m_texte = sanitize_textarea_field($m['texte_allergies'] ?? '');

                $membres[] = [
                    'nom' => $m_nom,
                    'allergies' => $m_allergies,
                    'texte_allergies' => ($m_allergies === 'oui') ? $m_texte : '',
                ];
            }
        }

        global $wpdb;
        $table = $wpdb->prefix . 'mariage_questionnaire';

        // Migrate: add columns if missing
        $cols = $wpdb->get_col("DESCRIBE $table", 0);
        if (!in_array('nb_personnes', $cols)) {
            $wpdb->query("ALTER TABLE $table ADD COLUMN nb_personnes INT DEFAULT 1 AFTER nom");
        }
        if (!in_array('membres_groupe', $cols)) {
            $wpdb->query("ALTER TABLE $table ADD COLUMN membres_groupe TEXT AFTER texte_allergies");
        }

        // Check existing by name
        $existing = $wpdb->get_row(
            $wpdb->prepare("SELECT id FROM $table WHERE nom = %s", $nom)
        );

        $data = [
            'nom'              => $nom,
            'nb_personnes'     => $nb_personnes,
            'allergies'        => $allergies,
            'texte_allergies'  => $allergies === 'oui' ? $texte_allergies : '',
            'membres_groupe'   => wp_json_encode($membres),
            'enfants'          => $enfants,
            'nb_enfants'       => $enfants === 'oui' ? $nb_enfants : 0,
            'discours'         => $discours,
            'commentaire'      => $commentaire,
        ];
        $format = ['%s', '%d', '%s', '%s', '%s', '%s', '%d', '%s', '%s'];

        if ($existing) {
            $data['created_at'] = current_time('mysql');
            $format[] = '%s';
            $wpdb->update($table, $data, ['id' => $existing->id], $format, ['%d']);
            wp_send_json_success(['message' => 'Vos reponses ont ete mises a jour. Merci !']);
        }

        $wpdb->insert($table, $data, $format);

        if ($wpdb->insert_id) {
            wp_send_json_success(['message' => 'Merci pour vos reponses !']);
        }

        wp_send_json_error(['message' => 'Une erreur est survenue. Veuillez reessayer.']);
    }
}
