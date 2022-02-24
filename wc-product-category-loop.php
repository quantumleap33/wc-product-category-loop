<?php
/**
 * @package WooCommerce product categories loop
 */
/*
Plugin Name: WooCommerce product categories loop
Plugin URI: https://www.gmitropapas.com
Description: WooCommerce product categories loop. Modified <strong>[product_categories]</strong> shortcode.
Version: 1.0
Author: George Mitropapas
Author URI: https://www.gmitropapas.com
License: GPLv2 or later
Text Domain: wc-product-category-loop
 */

/*
This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.

Copyright 2005-2015 Automattic, Inc.
*/

// Make sure we don't expose any info if called directly
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}


// Import Splide library
function gm_enqueue_splide_scripts() {
    wp_register_style( 'plugin-css', plugins_url( '/css/wc-product-category-loop_style.css', __FILE__ ) );
    wp_register_style( 'splide-css', plugins_url( '/css/splide.min.css', __FILE__ ) );
    wp_register_script( 'splide-js', plugins_url( '/js/splide.min.js', __FILE__ ) );
    wp_register_script( 'splide-init', plugins_url( '/js/splide-init.js', __FILE__ ) );
}
add_action( 'wp_enqueue_scripts', 'gm_enqueue_splide_scripts' );


function gm_product_categories( $atts ) {
    global $woocommerce_loop;

    $atts = shortcode_atts( array(
        'number'     => null,
        'orderby'    => 'name',
        'order'      => 'ASC',
        'columns'    => '4',
        'hide_empty' => 1,
        'parent'     => '',
        'ids'        => ''
    ), $atts );

    if ( isset( $atts['ids'] ) ) {
        $ids = explode( ',', $atts['ids'] );
        $ids = array_map( 'trim', $ids );
    } else {
        $ids = array();
    }

    $hide_empty = ( $atts['hide_empty'] == true || $atts['hide_empty'] == 1 ) ? 1 : 0;

    // get terms and workaround WP bug with parents/pad counts
    $args = array(
        'orderby'    => $atts['orderby'],
        'order'      => $atts['order'],
        'hide_empty' => $hide_empty,
        'include'    => $ids,
        'pad_counts' => true,
        'child_of'   => $atts['parent']
    );

    $product_categories = get_terms( 'product_cat', $args );

    if ( '' !== $atts['parent'] ) {
        $product_categories = wp_list_filter( $product_categories, array( 'parent' => $atts['parent'] ) );
    }

    if ( $hide_empty ) {
        foreach ( $product_categories as $key => $category ) {
            if ( $category->count == 0 ) {
                unset( $product_categories[ $key ] );
            }
        }
    }

    if ( $atts['number'] ) {
        $product_categories = array_slice( $product_categories, 0, $atts['number'] );
    }

    $columns = absint( $atts['columns'] );
    $woocommerce_loop['columns'] = $columns;

    ob_start();

    if ( $product_categories ) {
        // Enqueue Splide style and scripts
        wp_enqueue_style( 'plugin-css' );
        wp_enqueue_style( 'splide-css' );
        wp_enqueue_script( 'splide-js' );
        wp_enqueue_script( 'splide-init' );         

        ?>
        <div class="splide columns-<?php echo $columns;?>">
        <div class="splide__slider">        
        <div class="woocommerce splide__track columns-<?php echo $columns;?>">
        <ul class="products alternating-list splide__list">
        <?php
        foreach ( $product_categories as $category ) {
            ?>
            <li class="product-category splide__slide product">
                <?php echo '<h5>'.$category->description.'</h5>';
                ?>
                <?php the_field('cat_button_text'); ?>
                <a href="<?php echo get_category_link($category); ?>">  
                    <?php

                        $thumbnail_id = get_woocommerce_term_meta( $category->term_id, 'thumbnail_id', true );
                            $image = wp_get_attachment_image_src( $thumbnail_id, 'large')[0];
                            $image_alt = get_post_meta($thumbnail_id, '_wp_attachment_image_alt', true);
                            if ( $image ) {
                                echo '<img srcset="' . $image . '" alt="' . $image_alt . '" />';
                            }
                        ?>  
                 </a>
                 
            </li>         
            <?php
        } 

        woocommerce_product_loop_end();
    }

    woocommerce_reset_loop();

    return '<div class="woocommerce splide columns-' . $columns . '">' . ob_get_clean() . '</div></div></div>';
}
add_shortcode('gm_display_product_categories', 'gm_product_categories');
