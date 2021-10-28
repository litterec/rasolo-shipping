<?php

add_action('woocommerce_shipping_init', 'courier_shipping_method');
function courier_shipping_method() {

    if(!class_exists('RasoloShipping')){
        return;
    }
    if ( ! class_exists( 'WC_Rasolo_Courier_Method' ) ) {
        class WC_Rasolo_Courier_Method extends WC_Shipping_Method {

            public function __construct( $instance_id = 0) {
                $this->id = 'rasolo_courier';
                $this->instance_id = absint( $instance_id );
                $this->domain = RasoloShipping::$TEXTDOMAIN;
                $this->method_title = __( 'Ra-Solo Courier Shipping', $this->domain );
                $this->method_description = __( 'Shipping method with courier', $this->domain );
                $this->supports = array(
                    'shipping-zones',
                    'instance-settings',
                    'instance-settings-modal',
                );
                $this->init();
            }

            ## Load the settings API
            function init() {
                $this->init_form_fields();
                $this->init_settings();
                $this->enabled = $this->get_option( 'enabled', $this->domain );
                $this->title   = $this->get_option( 'title', $this->domain );
                $this->info    = $this->get_option( 'info', $this->domain );
                add_action('woocommerce_update_options_shipping_' . $this->id, array($this, 'process_admin_options'));
             }

            function init_form_fields() {
                $this->instance_form_fields = array(
                    'title' => array(
                        'type'          => 'text',
                        'title'         => __('Title', $this->domain),
                        'description'   => __( 'Title to be displayed on site.', $this->domain ),
                        'default'       => __( 'Courier over a city', $this->domain ),
                    ),
                    'cost' => array(
                        'type'          => 'text',
                        'title'         => __('Cost', $this->domain),
                        'description'   => __( 'Enter a cost', $this->domain ),
                        'default'       => '',
                    ),
                );
            }

            public function calculate_shipping( $packages = array() ) {
                global $woocommerce;
                $carttotal = floatval($woocommerce->cart->cart_contents_total)+floatval($woocommerce->cart->tax_total);
//                myvar_dd($carttotal,'$carttotal');
//                $some_value=$this->get_field_value();
//                foreach($packages as $nth_key=>$nth_value ){
//                    rasolo_debug_to_file($nth_value,'$package_'.$nth_key.'_!_');
//                }

//                rasolo_debug_to_file($some_value,'$some_value_3242342');
//                rasolo_debug_to_file($this,'$this_3242342');
//                rasolo_debug_to_file($packages,'$packages_3242342');
//                rasolo_debug_to_file($carttotal,'$carttotal_3242342');
//                rasolo_debug_to_file($carttotal,null);

                $default_cost=$this->get_instance_option('cost');
                if(empty($default_cost)){
                    $dlv_cost=0.;
                } else {
                    $dlv_cost=abs(floatval($default_cost));
                    if(empty($dlv_cost)){
                        $dlv_cost=0.;
                    }
                     if($dlv_cost>1000000.){
                         $dlv_cost=0.;
                     }
                }
/*
                if(class_exists('RasoloSkDiscount')){
                    $dsc=new RasoloSkDiscount();
                    $dsc->set_cart_amount($carttotal);
                    $dlv_cost=$dsc->get_deliv_cost();
                    $dlv_cost=139.9;
                } else {
                    $dlv_cost=109.5;
                }
*/
//                rasolo_debug_to_file($dlv_cost,'$dlv_cost');
/*
                if($carttotal <5.){
                    $dlv_cost = 10.;
                } else if($carttotal < 100.){
                    $dlv_cost = 9.;
                } else if($carttotal < 150.){
                    $dlv_cost = 8.;
                } else if($carttotal < 200.){
                    $dlv_cost = 7.;
                } else if($carttotal < 250.){
                    $dlv_cost = 6.;
                } else {
                    $dlv_cost = 0.; //Free delivery for above 10 euro
                }
*/
//                    $dlv_cost=rand(20,99);
//                $dlv_cost='0';
                $rate = array(
                    'id'       => $this->id,
                    'label'    => $this->title,
                    'cost'     => $dlv_cost,
                    'calc_tax' => 'per_item'
                );
                $this->add_rate( $rate );
            } // The end of calculate_shipping

        }
    }
}

add_filter('woocommerce_shipping_methods', 'add_rs_courier_shipping');
function add_rs_courier_shipping( $methods ) {
    $methods['rasolo_courier'] = 'WC_Rasolo_Courier_Method';
    return $methods;
}
