<?php

class Mariage_RSVP_Handler {

    public function __construct() {
        add_action('wp_ajax_mariage_rsvp', [$this, 'handle']);
        add_action('wp_ajax_nopriv_mariage_rsvp', [$this, 'handle']);
    }

    public function handle() {
        check_ajax_referer('mariage_nonce', 'nonce');

        $email = sanitize_email($_POST['email'] ?? '');
        $presence = sanitize_text_field($_POST['presence'] ?? '');
        $nb_personnes = absint($_POST['nb_personnes'] ?? 1);
        $enfants = sanitize_text_field($_POST['enfants'] ?? 'non');
        $nb_enfants = absint($_POST['nb_enfants'] ?? 0);
        $discours = sanitize_text_field($_POST['discours'] ?? 'non');
        $commentaire = sanitize_textarea_field($_POST['commentaire'] ?? '');

        // Validation
        if (empty($email) || empty($presence)) {
            wp_send_json_error(['message' => 'Veuillez remplir tous les champs obligatoires.']);
        }

        if (!is_email($email)) {
            wp_send_json_error(['message' => 'Veuillez entrer une adresse email valide.']);
        }

        if (!in_array($presence, ['oui', 'non'], true)) {
            wp_send_json_error(['message' => 'Valeur de presence invalide.']);
        }

        // Collect group members (nom + allergies)
        $membres = [];
        if ($presence === 'oui') {
            if ($nb_personnes < 1) $nb_personnes = 1;

            $raw_membres = isset($_POST['membres']) && is_array($_POST['membres']) ? $_POST['membres'] : [];

            for ($i = 0; $i < $nb_personnes; $i++) {
                $m = $raw_membres[$i] ?? [];
                $m_nom = sanitize_text_field($m['nom'] ?? '');
                if (empty($m_nom)) {
                    wp_send_json_error(['message' => 'Veuillez indiquer le nom de toutes les personnes.']);
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
        $table = $wpdb->prefix . 'mariage_rsvp';

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

        $nom = !empty($membres) ? $membres[0]['nom'] : '';

        $data = [
            'nom'             => $nom,
            'presence'        => $presence,
            'nb_personnes'    => $presence === 'oui' ? $nb_personnes : 0,
            'membres_groupe'  => wp_json_encode($membres),
            'enfants'         => $presence === 'oui' ? $enfants : 'non',
            'nb_enfants'      => ($presence === 'oui' && $enfants === 'oui') ? $nb_enfants : 0,
            'discours'        => $presence === 'oui' ? $discours : 'non',
            'commentaire'     => $commentaire,
        ];
        $format = ['%s', '%s', '%d', '%s', '%s', '%d', '%s', '%s'];

        // Check if already submitted with same email
        $existing = $wpdb->get_row(
            $wpdb->prepare("SELECT id FROM $table WHERE email = %s", $email)
        );

        if ($existing) {
            $data['created_at'] = current_time('mysql');
            $format[] = '%s';
            $wpdb->update($table, $data, ['id' => $existing->id], $format, ['%d']);
            wp_send_json_success(['message' => 'Votre reponse a ete mise a jour. Merci !']);
        }

        $data['email'] = $email;
        $format[] = '%s';
        $wpdb->insert($table, $data, $format);

        if ($wpdb->insert_id) {
            wp_send_json_success(['message' => 'Merci pour votre reponse ! Nous avons hate de vous voir.']);
        }

        wp_send_json_error(['message' => 'Une erreur est survenue. Veuillez reessayer.']);
    }
}
