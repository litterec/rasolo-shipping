<?php

add_action('woocommerce_shipping_init', 'rasolo_shipping_method');
function rasolo_shipping_method() {

    if(!class_exists('RasoloShipping')){
        return;
    }


    if ( ! class_exists( 'WC_Rasolo_Shipping_Method' ) ) {
        class WC_Rasolo_Shipping_Method extends WC_Shipping_Method {

            public function __construct( $instance_id = 0) {
                $this->id = 'rasolo_shipping';
                $this->instance_id = absint( $instance_id );
                $this->domain = RasoloShipping::$TEXTDOMAIN;
                $this->method_title = __( 'Ra-Solo Delivery Company Shipping', $this->domain );
                $this->method_description = __( 'Shipping method with some delivery services', $this->domain );
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
                        'default'       => __( 'Delivery service', $this->domain ),
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
//                rasolo_debug_to_file($carttotal,'$carttotal');

//                $dsc=new RasoloSkDiscount();
//                $dsc->set_cart_amount($carttotal);
//                $dlv_cost=$dsc->get_deliv_cost();
//                rasolo_debug_to_file($dlv_cost,'$dlv_cost');

                $dlv_cost=0.;
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
                $dlv_cost='0';
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

add_filter('woocommerce_shipping_methods', 'add_rasolo_shipping');
function add_rasolo_shipping( $methods ) {
    $methods['rasolo_shipping'] = 'WC_Rasolo_Shipping_Method';
    return $methods;
}
