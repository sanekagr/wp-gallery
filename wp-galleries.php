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

//class for recizing images
include 'includes/aq_resizer.php';

//https://codepen.io/nsom/pen/VbqLew

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
/**
 * Annnew post type and menue
 */
function add_new_posttype() {
    register_post_type( 'WP-Gallery',
        array(
            'labels' => array(
                'name' => __( 'WP-Gallery' ),
                'singular_name' => __( 'Image' ),
                'add_new' => __( 'Add New Image' ),
                'add_new_item' => __( 'Add New Image' ),
                'edit_item' => __( 'Edit Image ' ),
                'new_item' => __( 'Add New Image' ),
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


//add shortcode to title
function add_shortcodes($title, $id) {

    if(get_post_type($id) == 'wp-gallery') {
        return $title . '  [wp_gallery id=' . $id . ' size="small-gallery"]';
    } else {
        return $title;
    }

}

add_filter('the_title', 'add_shortcodes', 10, 2);


/**
 * Shortcode for the gallery
 * @param $atts
 * @param null $content
 * @return string
 */
function wp_gallery_shortcode($atts, $content=null) {

    ob_start();

    //Get Post ID from the shortcode
    $attributes = shortcode_atts(
        array(
            'id' => null,
            'size' => null
        ), $atts );

    $post_id = $attributes['id'];
    $size = $attributes['size'];

    //filtering of the Ppost details
    $args = array(
        'post_type' => 'attachment',
        'post_mime_type' => 'image',
        'numberposts' => -1,
        'post_status' => null,
        'post_parent' => $post_id
    );

    //get post details
    $image_posts_details = get_posts($args);

    $image_container = '<div class="container gallery_container"><div class="row">';

    foreach($image_posts_details  as $image_post) {

         // echo $image_post->guid. "</br>";
         // echo $image_post->post_title. "</br>";

        if($size == 'small-gallery') {
            $height = '250px';
            $grid = 3;
            $image = aq_resize($image_post->guid, 250, 250, true, true);
        } else  {
            $height = '480px';
            $grid = 6;
            $image = aq_resize($image_post->guid, 640, 480, true, true);
        }

         $image_container .= '<a href="' . $image_post->guid . '" class="lightbox_trigger col-' . $grid . '" data-toggle="lightbox"';
         $image_container .=  ' data-title = "'. $image_post->post_title . '" data-gallery="gallery" data-max-width="' . $height . '">';
         $image_container .= '<img src="' . $image . '" class="img-fluid rounded img-thumb" />';
         $image_container .= '</a>';
    }

    $image_container .= '</div></div';

    echo $image_container;

    wp_reset_postdata();
    return ob_get_clean();
}
add_shortcode('wp_gallery', 'wp_gallery_shortcode');
