<?php
/*
Plugin Name: Temoinage
Description: Plugin de gestion des témoignages
Version: 1.0
Author: Samy
*/

function samy_register_temoignage_post_type() {
    $args = array(
        'public' => true,
        'label'  => 'Témoignage',
        'supports' => array('title', 'editor', 'thumbnail'),
        'has_archive' => true,
        'rewrite' => array('slug' => 'temoignage'),
    );
    register_post_type( 'temoignage', $args );
}
add_action( 'init', 'samy_register_temoignage_post_type' );

function samy_affiche_temoignages_shortcode( $atts ) {

    // Gestion des options via GET
    $color = isset($_GET['color']) ? sanitize_text_field($_GET['color']) : '#FFD700';
    $count = isset($_GET['nombre']) ? intval($_GET['nombre']) : get_option('samy_temoignage_count', 3);

    // Début formulaire pour le choix des options
    $output = '<form method="get" class="samy-form">';
    $output .= '<label for="color">Choix de couleur :</label>';
    $output .= '<select name="color" id="color">';
    $colors = [
        '#FFD700' => 'Or',
        '#FF0000' => 'Rouge',
        '#0000FF' => 'Bleu',
        '#00FF00' => 'Vert'
    ];
    foreach ($colors as $hex => $label) {
        $selected = ($color === $hex) ? 'selected' : '';
        $output .= "<option value=\"$hex\" $selected>$label</option>";
    }
    $output .= '</select>';
    $output .= '<label for="tri">Trier par :</label>';
    $output .= '<select name="tri" id="tri">';
    $tri_options = [
        'desc' => 'Note décroissante',
        'asc'  => 'Note croissante'
    ];
    $tri = isset($_GET['tri']) ? $_GET['tri'] : 'desc';
    foreach ($tri_options as $val => $label) {
        $selected = ($tri === $val) ? 'selected' : '';
        $output .= "<option value=\"$val\" $selected>$label</option>";
    }
    $output .= '</select>';

    $output .= '<label for="nombre">Nombre :</label>';
    $output .= '<select name="nombre" id="nombre">';
    foreach ([1, 3, 5, 10] as $val) {
        $selected = ($count == $val) ? 'selected' : '';
        $output .= "<option value=\"$val\" $selected>$val</option>";
    }
    $output .= '</select>';

    $output .= '<button type="submit">Appliquer</button>';
    $output .= '</form><hr>';

    // Formulaire pour ajouter un témoignage
    if (is_user_logged_in()) { // Vérifie si l'utilisateur est connecté
        $output .= '<h2>Ajouter un témoignage</h2>';
        $output .= '<form method="post" action="" class="samy-temoignage-form">';
        $output .= '<div class="form-group">';
        $output .= '<label for="nom">Nom :</label>';
        $output .= '<input type="text" name="nom" id="nom" class="form-control" required>';
        $output .= '</div>';
        
        $output .= '<div class="form-group">';
        $output .= '<label for="poste">Poste :</label>';
        $output .= '<input type="text" name="poste" id="poste" class="form-control" required>';
        $output .= '</div>';
        
        $output .= '<div class="form-group">';
        $output .= '<label for="note">Note :</label>';
        $output .= '<select name="note" id="note" class="form-control" required>';
        $output .= '<option value="1">1 étoile</option>';
        $output .= '<option value="2">2 étoiles</option>';
        $output .= '<option value="3">3 étoiles</option>';
        $output .= '<option value="4">4 étoiles</option>';
        $output .= '<option value="5">5 étoiles</option>';
        $output .= '</select>';
        $output .= '</div>';
        
        $output .= '<div class="form-group">';
        $output .= '<label for="message">Témoignage :</label>';
        $output .= '<textarea name="message" id="message" class="form-control" required></textarea>';
        $output .= '</div>';
        
        $output .= '<div class="form-group">';
        $output .= '<button type="submit" name="submit_temoignage" class="submit-button">Envoyer</button>';
        $output .= '</div>';
        $output .= '</form>';

        // Traitement du formulaire d'ajout de témoignage
        if (isset($_POST['submit_temoignage'])) {
            $nom = sanitize_text_field($_POST['nom']);
            $poste = sanitize_text_field($_POST['poste']);
            $note = intval($_POST['note']);
            $message = sanitize_textarea_field($_POST['message']);

            // Création du témoignage
            $post_data = array(
                'post_title'   => $nom,
                'post_content' => $message,
                'post_status'  => 'publish',
                'post_type'    => 'temoignage',
                'meta_input'   => array(
                    'poste' => $poste,
                    'note'  => $note
                )
            );

            // Insérer le témoignage
            $post_id = wp_insert_post($post_data);

            // Message de confirmation
            if ($post_id) {
                $output .= '<p>Témoignage ajouté avec succès !</p>';
            }
        }
    } else {
        $output .= '<p>Vous devez être connecté pour ajouter un témoignage.</p>';
    }

    // Tri des témoignages
    $tri_order = ($tri === 'asc') ? 'ASC' : 'DESC';

    $the_query = new WP_Query([
        'post_type' => 'temoignage',
        'posts_per_page' => $count,
        'meta_key' => 'note',
        'orderby' => 'meta_value_num',
        'order' => $tri_order
    ]);

    $output .= '<ul>';
    if ( $the_query->have_posts() ) {
        while ( $the_query->have_posts() ) {
            $the_query->the_post();

            $output .= '<li>';
            $output .= '<h1><strong>' . esc_html( get_the_title() ) . '</strong></h1>';
            $output .= '<p>' . esc_html( get_the_content() ) . '</p>';

            $poste = get_field('poste');
            if ($poste) {
                $output .= '<p><strong>Poste:</strong> ' . esc_html($poste) . '</p>';
            }

            $note = get_field('note');
            if ($note) {
                $output .= '<p><strong>Note:</strong> ' . samy_affiche_etoiles($note, 5, $color) . '</p>';
            }

            $output .= '<br>';
            $output .= '</li>';
        }
    } else {
        $output .= '<li>Aucun témoignage trouvé.</li>';
    }
    $output .= '</ul>';

    wp_reset_postdata();
    return $output;
}



function samy_affiche_etoiles($note, $max = 5, $color = '#FFD700') {
    $note = intval($note);
    $output = '<div class="samy-stars" style="color: ' . esc_attr($color) . '; font-size: 20px;">';
    for ($i = 1; $i <= $max; $i++) {
        $output .= ($i <= $note) ? '★' : '☆';
    }
    $output .= '</div>';
    return $output;
}


function samy_enqueue_temoignages_style() {
    wp_enqueue_style(
        'samy-temoignages-style',
        plugin_dir_url(__FILE__) . 'css/temoignages.css',
        array(),
        '1.0.0'
    );
}
add_action('wp_enqueue_scripts', 'samy_enqueue_temoignages_style');


function samy_enqueue_temoignage_styles() {
    wp_enqueue_style(
        'mon-style',
        plugin_dir_url( __FILE__ ) . 'css/temoignage.css',
        [],
        '1.0'
    );
}
add_action( 'wp_enqueue_scripts', 'samy_enqueue_temoignage_styles' );

// Shortcode
add_shortcode( 'temoignages', 'samy_affiche_temoignages_shortcode' );
