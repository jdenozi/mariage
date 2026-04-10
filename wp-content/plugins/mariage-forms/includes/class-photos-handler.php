<?php

class Mariage_Photos_Handler {

    public function __construct() {
        add_action('wp_ajax_mariage_upload_photos', [$this, 'handle']);
        add_action('wp_ajax_nopriv_mariage_upload_photos', [$this, 'handle']);
        add_action('wp_ajax_mariage_get_photos', [$this, 'get_photos']);
        add_action('wp_ajax_nopriv_mariage_get_photos', [$this, 'get_photos']);
    }

    public function handle() {
        check_ajax_referer('mariage_nonce', 'nonce');

        if (empty($_FILES['photos'])) {
            wp_send_json_error(['message' => 'Aucun fichier selectionne.']);
        }

        $nom_invite = sanitize_text_field($_POST['nom_invite'] ?? '');
        $allowed_types = [
            'image/jpeg', 'image/png', 'image/webp',
            'video/mp4', 'video/quicktime', 'video/webm',
        ];
        $max_size = 50 * 1024 * 1024; // 50MB

        require_once ABSPATH . 'wp-admin/includes/file.php';
        require_once ABSPATH . 'wp-admin/includes/media.php';
        require_once ABSPATH . 'wp-admin/includes/image.php';

        global $wpdb;
        $table = $wpdb->prefix . 'mariage_photos';
        $uploaded = 0;
        $errors = [];

        $files = $_FILES['photos'];
        $file_count = is_array($files['name']) ? count($files['name']) : 1;

        for ($i = 0; $i < $file_count; $i++) {
            $file_type = is_array($files['type']) ? $files['type'][$i] : $files['type'];
            $file_size = is_array($files['size']) ? $files['size'][$i] : $files['size'];
            $file_error = is_array($files['error']) ? $files['error'][$i] : $files['error'];
            $file_tmp = is_array($files['tmp_name']) ? $files['tmp_name'][$i] : $files['tmp_name'];
            $file_name = is_array($files['name']) ? $files['name'][$i] : $files['name'];

            if ($file_error !== UPLOAD_ERR_OK) {
                $errors[] = "Erreur lors de l'envoi de $file_name.";
                continue;
            }

            if (!in_array($file_type, $allowed_types, true)) {
                $errors[] = "$file_name : type de fichier non autorise.";
                continue;
            }

            if ($file_size > $max_size) {
                $errors[] = "$file_name : fichier trop volumineux (max 50 Mo).";
                continue;
            }

            $upload = wp_handle_upload(
                [
                    'name'     => $file_name,
                    'type'     => $file_type,
                    'tmp_name' => $file_tmp,
                    'error'    => $file_error,
                    'size'     => $file_size,
                ],
                ['test_form' => false]
            );

            if (isset($upload['error'])) {
                $errors[] = "$file_name : " . $upload['error'];
                continue;
            }

            $wpdb->insert(
                $table,
                [
                    'nom_invite' => $nom_invite,
                    'file_url'   => $upload['url'],
                    'file_type'  => $file_type,
                ],
                ['%s', '%s', '%s']
            );
            $uploaded++;
        }

        if ($uploaded > 0) {
            $msg = $uploaded === 1
                ? 'Votre fichier a ete envoye avec succes !'
                : "$uploaded fichiers envoyes avec succes !";
            if (!empty($errors)) {
                $msg .= ' Certains fichiers n\'ont pas pu etre envoyes.';
            }
            wp_send_json_success(['message' => $msg]);
        }

        wp_send_json_error([
            'message' => 'Aucun fichier n\'a pu etre envoye. ' . implode(' ', $errors)
        ]);
    }

    public function get_photos() {
        global $wpdb;
        $table = $wpdb->prefix . 'mariage_photos';

        $photos = $wpdb->get_results("SELECT * FROM $table ORDER BY created_at DESC", ARRAY_A);
        wp_send_json_success(['photos' => $photos ?: []]);
    }
}
