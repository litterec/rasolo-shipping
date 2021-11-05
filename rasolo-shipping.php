<?php
/*
 *
Plugin Name: Rasolo Shipping
Plugin URI: https://ra-solo.com.ua
Description: Shipping method plugin for different Ukrainian delivery services
Version: 1.0.0
Author: Ra-Solo Web Studio
Author URI: https://ra-solo.com.ua
*/




/**
 * Check if WooCommerce is active
 */
if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {

    require_once trailingslashit(dirname(__FILE__)).'include/rasolo-shipping-method.php';
    require_once trailingslashit(dirname(__FILE__)).'include/class-rasolo-shipping.php';

    $is_courier_delivery_needed=false;


    if(!class_exists('SxGeo')){
        require_once(dirname(__FILE__) . '/modules/SxGeo.php');
    };

    $SxGeo = new SxGeo(dirname(__FILE__) . '/modules/SxGeoCity.dat');
    if(function_exists('detect_remote_ip')){
        $client_ip = detect_remote_ip();
    } else if (!empty($_SERVER['REMOTE_ADDR'])) {
        $client_ip=$_SERVER['REMOTE_ADDR'];
    } else  {
        $client_ip ='192.168.0.1';
    }
    // 706483 - Kharkiv https://www.grid.ac/institutes/grid.495173.d
    // https://dateandtime.info/ru/citycoordinates.php?id=698740
    $city = $SxGeo->getCity($client_ip);
    $rs_shipping=new RasoloShipping();
    if(is_array($city) && !empty($city['city']) && !empty($city['city']['id'])){
        if($rs_shipping->is_city_courier($city['city']['id'])){
            require_once trailingslashit(dirname(__FILE__)).'include/courier-shipping-method.php';
        };
    }
// false &&
//    if(706483==$city['city']['id']){
//    }

    require_once trailingslashit(dirname(__FILE__)).'include/system01.php';
    require_once trailingslashit(dirname(__FILE__)).'include/js_background.php';
    require_once trailingslashit(dirname(__FILE__)).'include/checkout_background.php';
    require_once trailingslashit(dirname(__FILE__)).'include/class-rs-bootstrap.php';



    add_action( 'plugins_loaded', [new RsBootstrapDlv( $rs_shipping ), 'plugins_loaded' ], 0 );


/*
    if(is_admin()){
        add_action('after_setup_theme',function(){
            global $rasolo_shipping_data;

            if(!class_exists('RasoloShipping')){
                die('There is no class RasoloShipping');
            }
            $rasolo_shipping_data=New RasoloShipping();
            $rasolo_shipping_data->process_post_data();

        });
    };
*/


    add_action('admin_menu', function(){
//        global $rasolo_shipping_data;
        $rasolo_shipping_data=New RasoloShipping();
        call_user_func_array('add_options_page',
			$rasolo_shipping_data->get_options_page_arguments());

    });

}