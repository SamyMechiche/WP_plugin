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

    // Début formulaire
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
    wp_register_style('samy-temoignages-style', false);
    wp_enqueue_style('samy-temoignages-style');
    wp_add_inline_style('samy-temoignages-style', '
        .samy-form {
            margin-bottom: 20px;
        }
        .samy-form label {
            margin-right: 5px;
        }
        .samy-form select {
            margin-right: 10px;
        }
        .samy-stars {
            margin: 5px 0;
        }
    ');
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
