<?php
/**
 * Plugin Name: wp-galleries
 * Plugin URI:
 * Description:
 * Version: 1.0
 * Author: Alexander Agranov
 * Author URI: http://www.mywebsite.com
 */
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
//https://codepen.io/nsom/pen/VbqLew

//https://codepen.io/nsom/pen/VbqLew
//https://code.tutsplus.com/tutorials/how-to-create-an-instant-image-gallery-plugin-for-wordpress--wp-25321
//https://github.com/hlashbrooke/WordPress-Plugin-Template

//require_once 'includes/class-wp-galleries.php';
//Add static files
function wp_gallery_scripts(){
    //css
    wp_enqueue_style("ekko-lightbox-style", 'https://cdnjs.cloudflare.com/ajax/libs/ekko-lightbox/5.3.0/ekko-lightbox.css',false, "");
    wp_enqueue_style("bootstrap-style", 'https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.3.1/css/bootstrap.min.css',false, "");
    wp_enqueue_style("gallery-style", plugins_url('assets/css/front_gallery.css', __FILE__),false, "");

    //plugin_dir_url( __FILE__ ) . 'assets/foo-styles.css'

    //js
    wp_enqueue_script("bootstrap-script",'https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.3.1/js/bootstrap.min.js',array("jquery"), "",1);
    wp_enqueue_script("ekko-lightbox-script",'https://cdnjs.cloudflare.com/ajax/libs/ekko-lightbox/5.3.0/ekko-lightbox.min.js',array("jquery"), "",1);
    wp_enqueue_script("gallery-script", plugins_url('assets/js/gallery.js', __FILE__),array("jquery"), "",1);
}

add_action( 'wp_enqueue_scripts', 'wp_gallery_scripts' );

//add sizes for the gallery images
add_image_size( 'small-gallery', 250, 250, true );
add_image_size( 'medium-gallery', 640, 480, true );

// Registers the new post type and taxonomy

function add_new_posttype() {
    register_post_type( 'WP-Gallery',
        array(
            'labels' => array(
                'name' => __( 'WP-Gallery' ),
                'singular_name' => __( 'Image' ),
                //'add_new' => __( 'Add New Image' ),
                //'add_new_item' => __( 'Add New Image' ),
                'edit_item' => __( 'Edit Image' ),
                //'new_item' => __( 'Add New Image' ),
                'view_item' => __( 'View Image' ),
                'search_items' => __( 'Search Image' ),
                'not_found' => __( 'No Image found' ),
                'not_found_in_trash' => __( 'No Image found in trash' )
            ),
            'public' => true,
            'supports' => array( 'title', 'editor','custom-fields'), //thumbnail
            'capability_type' => 'post',
            'rewrite' => array("slug" => "gallery"), // Permalinks format
            'menu_position' => 90,
        )
    );
}
if ( is_admin() ) { // admin actions
    add_action( 'init', 'add_new_posttype' );
}

function wp_gallery_shortcode($atts, $content=null, $tag = '') {

    ob_start();
    // define attributes and their defaults
    extract( shortcode_atts( array (
        'type' => 'WP-Gallery',
        'order' => 'date',
        'orderby' => 'title',
        'posts' => -1,
        'color' => '',
        'fabric' => '',
        'category' => '',
    ), $atts ) );

    // define query parameters based on attributes
    $options = array(
        'post_type' => 'WP-Gallery',
        'order' => $order,
        'orderby' => $orderby,
        'posts_per_page' => 1,
        'color' => $color,
        'fabric' => $fabric,
        'category_name' => $category,
    );
    $the_query = new WP_Query( $options );

    if ( $the_query->have_posts() ) {

        $args = array(
            'post_type' => 'attachment',
            'post_mime_type' => 'image',
            'numberposts' => -1,
            'post_status' => null,
            'post_parent' => 110
        );

        $image_posts_details = get_posts($args);

        $image_container = '<div class="container gallery_container"><div class="row">';

        foreach($image_posts_details  as $image_post) {

             // echo $image_post->guid. "</br>";
             // echo $image_post->post_title. "</br>";

             $image_container .= '<a href="' . $image_post->guid . '" class="lightbox_trigger col-4" data-toggle="lightbox"';
             $image_container .=  ' data-title = "'. $image_post->post_title . '" data-gallery="gallery" data-max-width="640">';
             $image_container .= '<img src="' . $image_post->guid . '" class="img-fluid rounded img-thumb" />';
             $image_container .= '</a>';
             //$image_container .= '<p class="simple_capture">' . $image_post->post_title . '</p>';

        }

        $image_container .= '</div></div';

        echo $image_container;

        //print_r(get_the_ID());
          echo "<pre>";
          print_r($atts);
          echo "</pre>";
    }

    else {
        echo "Sorry no Galleries  Found";
    }

    $wporg_atts = shortcode_atts([
        'title' => 'WordPress.org',
    ], $atts, $tag);

    wp_reset_postdata();
    return ob_get_clean();
}
add_shortcode('wp_gallery', 'wp_gallery_shortcode');

/*//Admin
if ( is_admin() ) { // admin actions
    add_action( 'admin_enqueue_scripts', 'chrs_options_style' );
    add_action( 'admin_init', 'chrs_theme_options_init' );
    add_action( 'admin_menu', 'chrs_theme_options_add' );

}
function chrs_options_style() {
    wp_register_style( 'chrs_options_css',  plugins_url('css/admin-styles.css', __FILE__), false, '1.0.0' );
    wp_enqueue_style( 'chrs_options_css' );
}
function chrs_theme_options_init(){
    register_setting( 'chrs_options', 'chrs_theme_options');
}
function chrs_theme_options_add() {
    //add_menu_page( __( 'Theme Options', 'chrstheme' ), __( 'Theme Options', 'chrstheme' ), 'edit_theme_options', 'theme_options', 'chrs_theme_options_do',plugins_url( 'myplugin/images/icon.png' ),80.5);

    add_submenu_page( 'edit.php?post_type=simple_gallery', 'Gallery settings', 'Gallery settings', 'manage_options', 'my-custom-submenu-page', 'chrs_theme_options_do' );
}*/
