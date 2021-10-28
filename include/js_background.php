<?php

    add_action('wp_enqueue_scripts', function(){
//        wp_enqueue_script( 'rs_search_pts', plugin_dir_url( __FILE__ ) . 'js/search_pts.js', array('jquery'), '1.0' );
        if(is_checkout()){
            wp_enqueue_script( 'rs_search_pts',  rs_get_dlv_plugin_dir(). '/js/search_pts.js', array('jquery'), '1.0' );
        }
        if(is_checkout() || is_cart()){
            wp_enqueue_script( 'rs_checkout_script',  rs_get_dlv_plugin_dir(). '/js/checkout_tricks.js', array('jquery'), '1.0' );
//            wp_enqueue_style( 'rs_checkout_style', rs_get_dlv_plugin_dir()  . '/css/checkout.css' );
        }

    });


