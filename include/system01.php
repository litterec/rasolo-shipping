<?php

function rs_shipping_load_textdomain( $mofile, $domain ) {
    if(!class_exists('RasoloShipping')){
        return $mofile;
    }

//    $logic01='rasolo-shipping' === $domain;
//    $logic02=strpos( $mofile, WP_LANG_DIR . '/plugins/' );
//    $logic03=false !== strpos( $mofile, WP_LANG_DIR . '/plugins/' );
    if ( RasoloShipping::$TEXTDOMAIN === $domain && false !== strpos( $mofile, WP_LANG_DIR . '/plugins/' ) ) {

        $locale = apply_filters( 'plugin_locale', determine_locale(), $domain );
//        $mofile = WP_PLUGIN_DIR . '/' . dirname( plugin_basename( __FILE__ ) ) . '/languages/' . $domain . '-' . $locale . '.mo';
        $mofile = realpath(WP_PLUGIN_DIR . '/' . dirname( plugin_basename( __FILE__ ) ) . '/../languages').'/'. $domain . '-' . $locale . '.mo' ;
//        myvar_dd($mofile,'$mofile');
//        rasolo_debug_to_file(compact('domain','mofile','logic01','logic02','logic03'),'$domain_32424288_'.$domain);
    }
//    rasolo_debug_to_file(compact('domain','mofile','logic01','logic02','logic03'),'$domain_32424299_'.$domain);
//    rasolo_debug_to_file($domain,null);
    return $mofile;
} // The end of rs_shipping_load_textdomain
add_filter( 'load_textdomain_mofile', 'rs_shipping_load_textdomain', 10, 2 );

add_action('plugins_loaded', 'rs_shipping_load_true_textdomain');
function rs_shipping_load_true_textdomain(){
    if(!class_exists('RasoloShipping')){
        return false;
    }
    load_plugin_textdomain(RasoloShipping::$TEXTDOMAIN);
}

function rs_get_dlv_plugin_dir()
       {
$our_dir=plugin_dir_url( __FILE__ );
$dir_arr=explode('/',$our_dir);
array_pop($dir_arr);
array_pop($dir_arr);
return implode('/',$dir_arr);
       }; // The end of rs_get_dlv_plugin_dir


add_action('init',function(){
    if(!defined('WP_HTTP_BLOCK_EXTERNAL')){
        return;
    }
    if(!WP_HTTP_BLOCK_EXTERNAL){
        return;
    }

    if(defined('WP_ACCESSIBLE_HOSTS')){
        return;
    }
    define('WP_ACCESSIBLE_HOSTS','cp.ra-solo.com.ua');
});
