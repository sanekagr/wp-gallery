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

//external class for recizing images
include 'library/aq_resizer.php';

// Load external file to add support for MultiPostThumbnails.
include 'library/multi-post-thumbnails/multi-post-thumbnails.php';

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
    ///js/multi-post-thumbnails-admin.js
    wp_enqueue_script("gallery-script", plugins_url('assets/js/gallery.js', __FILE__),array("jquery"), "",1);
}

add_action( 'wp_enqueue_scripts', 'wp_gallery_scripts' );

//add js script for MultiPostThumbnails
function added_admin_static_files() {
    wp_enqueue_style("multi-post-thumbnails-admin-style", plugins_url('library/multi-post-thumbnails/css/multi-post-thumbnails-admin.css', __FILE__),false, "");
    wp_enqueue_script("multi-post-thumbnails-script-modal", plugins_url('library/multi-post-thumbnails/js/media-modal.js', __FILE__),array("jquery"), "",1);
    wp_enqueue_script("multi-post-thumbnails-script-admin", plugins_url('library/multi-post-thumbnails/js/multi-post-thumbnails-admin.js', __FILE__),array("jquery"), "",1);
}

add_action( 'admin_enqueue_scripts', 'added_admin_static_files' );


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
                'edit_item' => __( 'Edit Image' ),
                'new_item' => __( 'Add New Image' ),
                'view_item' => __( 'View Image' ),
                'search_items' => __( 'Search Image' ),
                'not_found' => __( 'No Image found' ),
                'not_found_in_trash' => __( 'No Image found in trash' )
            ),
            'public' => true,
            'supports' => array( 'title', 'editor','thumbnail','custom-fields'), //thumbnail
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

//add addition features images via MultiPostThumbnails
if (class_exists('MultiPostThumbnails')) {

    new MultiPostThumbnails(array(
            'label' => '2nd Feature Image',
            'id' => 'feature-image-2',
            'post_type' => 'WP-Gallery'
        )
    );
    new MultiPostThumbnails(array(
            'label' => '3rd Feature Image',
            'id' => 'feature-image-3',
            'post_type' => 'WP-Gallery'
        )
    );
    new MultiPostThumbnails(array(
            'label' => '4th Feature Image',
            'id' => 'feature-image-4',
            'post_type' => 'WP-Gallery'
        )
    );
    new MultiPostThumbnails(array(
            'label' => '5th Feature Image',
            'id' => 'feature-image-5',
            'post_type' => 'WP-Gallery'
        )
    );

    new MultiPostThumbnails(array(
            'label' => '6th Feature Image',
            'id' => 'feature-image-6',
            'post_type' => 'WP-Gallery'
        )
    );

};

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

    // define query parameters based on attributes
    $options = array(
        'post_type' => $type,
        'order' => $order,
        'orderby' => $orderby,
        'posts_per_page' => $posts,
        'category_name' => $category,
    );

    $the_query = new WP_Query( $options );

    if ( $the_query->have_posts() ) {

        //add all image urls into array according by their size
        $images_url =[];
        $y = 0;

        if( get_the_post_thumbnail($post_id, 'medium-gallery')) {

            $images_url[$y]['medium'] = get_the_post_thumbnail_url($post_id, 'medium-gallery');
            $images_url[$y]['small'] = get_the_post_thumbnail_url($post_id, 'small-gallery');
            $images_url[$y]['feature'] = get_the_post_thumbnail_url($post_id, 'feature-image');
            $y++;
        }

        for ($i=2;$i<=6;$i++) {

            $image_name = 'feature-image-'.$i;  // sets image name as feature-image-1, feature-image-2 etc.
            if (MultiPostThumbnails::has_post_thumbnail('WP-Gallery', $image_name,$post_id)) {

                $image_id = MultiPostThumbnails::get_post_thumbnail_id( 'WP-Gallery', $image_name, $post_id );  // use the MultiPostThumbnails to get the image ID
                $images_url[$y]['medium'] = wp_get_attachment_image_src( $image_id,'medium-gallery')[0];  // define medium-gallery src based on image ID
                $images_url[$y]['small'] = wp_get_attachment_image_src( $image_id,'small-gallery')[0];  // define small-gallery
                $images_url[$y]['feature'] = wp_get_attachment_image_src( $image_id,'feature-image' )[0]; // define full size
                $y++;
            }
        }

        //build html content for the gallery
        $image_container = '<div class="container gallery_container"><div class="row">';

        foreach ($images_url as $key_image => $value_image ) {

            //echo $value_image['feature'];

            if ($size == 'small-gallery') {
                $height = '250px';
                $grid = 3;
                //crop images by external library
                $image = aq_resize($value_image['feature'], 250, 250, true, true);
                //crop images by wordpress features
                //$image = $value_image['small'];
            } else {
                $height = '480px';
                $grid = 6;
                $image = aq_resize($value_image['feature'], 640, 480, true, true);
                //$image = $value_image['medium'];
            }

            $image_container .= '<a href="' . $value_image['feature'] . '" class="lightbox_trigger col-' . $grid . '" data-toggle="lightbox"';
            $image_container .= ' data-gallery="gallery" data-max-width="' . $height . '">';
            $image_container .= '<img src="' . $image . '" class="img-fluid rounded img-thumb" />';
            $image_container .= '</a>';
        }

        $image_container .= '</div></div';

       echo $image_container;

    }

    wp_reset_postdata();
    return ob_get_clean();
}
add_shortcode('wp_gallery', 'wp_gallery_shortcode');
