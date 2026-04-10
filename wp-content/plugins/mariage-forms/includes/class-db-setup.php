<?php

class Mariage_DB_Setup {

    public static function create_tables() {
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();

        $sql_rsvp = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}mariage_rsvp (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            nom VARCHAR(255) NOT NULL,
            email VARCHAR(255) NOT NULL,
            presence VARCHAR(10) NOT NULL,
            nb_personnes INT DEFAULT 1,
            membres_groupe TEXT,
            enfants VARCHAR(10) DEFAULT 'non',
            nb_enfants INT DEFAULT 0,
            transport VARCHAR(20) DEFAULT 'voiture',
            discours VARCHAR(10) DEFAULT 'non',
            commentaire TEXT,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id)
        ) $charset_collate;";

        $sql_questionnaire = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}mariage_questionnaire (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            nom VARCHAR(255) NOT NULL,
            nb_personnes INT DEFAULT 1,
            allergies VARCHAR(10) DEFAULT 'non',
            texte_allergies TEXT,
            membres_groupe TEXT,
            enfants VARCHAR(10) DEFAULT 'non',
            nb_enfants INT DEFAULT 0,
            discours VARCHAR(10) DEFAULT 'non',
            commentaire TEXT,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id)
        ) $charset_collate;";

        $sql_photos = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}mariage_photos (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            nom_invite VARCHAR(255),
            file_url TEXT NOT NULL,
            file_type VARCHAR(50),
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id)
        ) $charset_collate;";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta($sql_rsvp);
        dbDelta($sql_questionnaire);
        dbDelta($sql_photos);
    }
}
