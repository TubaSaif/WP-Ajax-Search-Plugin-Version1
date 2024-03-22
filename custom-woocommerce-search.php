<?php
/*
Plugin Name: Ajax WooCommerce Search 
Description: Plugin for AJAX search with category dropdown for WooCommerce.
Version: 1.0 - 
*/
//-------------------------code for fetching categories and attach to custom_woocommerce_search_params object-------------------
function custom_woocommerce_search_scripts() {
	wp_enqueue_style("jquery-ui-styles");
    wp_enqueue_style('custom-woocommerce-search', plugins_url('/css/custom-woocommerce-search.css', __FILE__));
    wp_enqueue_script("jquery");
    wp_enqueue_script("jquery-ui-autocomplete");
    wp_enqueue_script('custom-woocommerce-search', plugins_url('/js/custom-woocommerce-search.js', __FILE__), array('jquery'), '1.0', true);
    
    $categories = get_terms('product_cat', array('hide_empty' => false));
    wp_localize_script('custom-woocommerce-search', 'custom_woocommerce_search_params', array(
        'categories' => $categories,
        'ajax_url' => admin_url('admin-ajax.php'),
    ));
    wp_enqueue_script('your-script', 'path-to-your-script.js', array('jquery'), '1.0', true);
    $shop_page_url = esc_url(get_permalink(wc_get_page_id('shop')));
    wp_localize_script('your-script', 'shopData', array(
    'shopURL' => $shop_page_url,
  ));

}
add_action('wp_enqueue_scripts', 'custom_woocommerce_search_scripts');

//------------------------------------------- Code for search bar/search input -------------------------------------------
function custom_woocommerce_ajax_search() {
    $category = isset($_POST['category']) ? sanitize_text_field($_POST['category']) : '';
    $search_query = isset($_POST['search_query']) ? sanitize_text_field($_POST['search_query']) : '';


    // taxonomy query based on whether a category is selected
    $tax_query = array();
    if (!empty($category) && $category !== 'select-category' && $category !== 'uncategorized') {
        $tax_query = array(
            array(
                'taxonomy' => 'product_cat',
                'field' => 'slug',
                'terms' => $category,
            ),
        );
    }

    // main query arguments
    $args = array(
        'post_type' => 'product',
        'tax_query' => $tax_query,
        's' => $search_query,
    );

    $query = new WP_Query($args);

 
    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();
            echo do_shortcode('[product id="' . get_the_ID() . '" ]');     
        }
        wp_reset_postdata();
    } else {
        echo 'No results found.';
    }

    die();
}
// --------------------------AJAX handler for auto-suggestions -------------------------------------
function custom_woocommerce_auto_suggest() {
    $category = isset($_POST['category']) ? sanitize_text_field($_POST['category']) : '';
    $search_query = isset($_POST['search_query']) ? sanitize_text_field($_POST['search_query']) : '';

  

    $tax_query = array();
    if (!empty($category) && $category !== 'select-category' && $category !== 'uncategorized') {
        $tax_query = array(
            array(
                'taxonomy' => 'product_cat',
                'field' => 'slug',
                'terms' => $category,
            ),
        );
    }



    // Combine tax query and meta query
    $args = array(
        'post_type' => 'product',
        'tax_query' => $tax_query,
        's' => $search_query,
        'relation' => 'AND', 
    );


    $query = new WP_Query($args);



    $suggestions = array();

    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();
            $product_id = get_the_ID();
            $product_title = get_the_title();
            $product_image = get_the_post_thumbnail_url($product_id, 'thumbnail');
            $product_url = get_permalink($product_id);

            $suggestions[] = array(
                'value' => $product_title,
                'image' => $product_image,
                'permalink' => $product_url,
            );
        }
        wp_reset_postdata();
    }
    echo json_encode($suggestions);

    wp_die(); 
}

add_action('wp_ajax_custom_woocommerce_auto_suggest', 'custom_woocommerce_auto_suggest');
add_action('wp_ajax_nopriv_custom_woocommerce_auto_suggest', 'custom_woocommerce_auto_suggest');


add_action('wp_ajax_custom_woocommerce_ajax_search', 'custom_woocommerce_ajax_search');
add_action('wp_ajax_nopriv_custom_woocommerce_ajax_search', 'custom_woocommerce_ajax_search');
