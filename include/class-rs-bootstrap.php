<?php
// https://wordpress.stackexchange.com/questions/217910/where-to-add-hooks-in-a-class
class RsBootstrapDlv {
  public $plugin;
  public function __construct( $plugin ) {
//      die('RsBootstrap1');
//      myvar_dd($this,'this_werewqwew_04');
    $this->plugin = $plugin;
  }
//  public function plugins_loaded() {}
  public function plugins_loaded() {
//    die('RsBootstrap12');
    $possible_hooks=[  'after_setup_theme', 'init', 'admin_init', 'wp' ];
    $possible_hooks[]='edit_user_profile';
    $possible_hooks[]='edit_user_profile_update';
    $possible_hooks[]='rs_wc_edit_account_form';
    $possible_hooks[]='woocommerce_save_account_details';
    $possible_hooks[]='woocommerce_before_checkout_form';
    $possible_hooks[]='woocommerce_before_cart';
    array_map( function( $hook ) {
//        $my_plugin=$this->plugin;
//        $is_exist=method_exists( $this->plugin, $hook );
//        myvar_dump(compact('my_plugin','hook','is_exist'),'$is_exist_34232');
      method_exists( $this->plugin, $hook ) && add_action( $hook, [ $this->plugin, $hook ] );
    }, $possible_hooks );
// 'plugins_loaded',
  }
}