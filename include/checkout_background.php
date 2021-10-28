<?php

define('RS_SHIPPING_COMP_META_KEY','_rs_shipping_comp');
define('RS_SHIPPING_PTS_META_KEY','_rs_shipping_pts');

function rs_get_regs_arr()
       {
return array(
        '0'=>'Выберите регион',
        'crm'=>'АРК',
        'vin'=>'Винницкая область',
        'vol'=>'Волынская область',
        'dpr'=>'Днепропетровская область',
        'dck'=>'Донецкая область',
        'zht'=>'Житомирская область',
        'zkp'=>'Закарпатская область',
        'zpr'=>'Запорожская область',
        'ivf'=>'Ивано-Франковская область',
        'kvr'=>'Киевская область',
        'kgd'=>'Кировоградская область',
        'lgn'=>'Луганская область',
        'lvv'=>'Львовская область',
        'nkl'=>'Николаевская область',
        'ods'=>'Одесская область',
        'plt'=>'Полтавская область',
        'rvn'=>'Ровенская область',
        'svs'=>'Севастополь город',
        'sum'=>'Сумская область',
        'trn'=>'Тернопольская область',
        'khk'=>'Харьковская область',
        'khs'=>'Херсонская область',
        'khm'=>'Хмельницкая область',
        'chr'=>'Черкасская область',
        'crn'=>'Черниговская область',
        'crc'=>'Черновицкая область',
    );
       } // Tne end of rs_get_regs_arr

//    add_action('wp_footer',function(){
//       echo '<div class="wpheadtest2">'.__('Some test','rasolo-shipping').'</div>'.chr(10);
//    });
// woocommerce_before_checkout_billing_form
// woocommerce_after_checkout_billing_form
// woocommerce_review_order_before_submit
// woocommerce_review_order_before_payment
// woocommerce_review_order_after_shipping
function rs_get_delv_serv_arr($crnt_dlv_code=false){
    if(!class_exists('RasoloShipping')){
        return false;
    }
    $dlvs=array(
        '0'=>__('Select company',RasoloShipping::$TEXTDOMAIN),
        'np'=>__('Nova Poshta',RasoloShipping::$TEXTDOMAIN),
        'dlv'=>__('Delivery',RasoloShipping::$TEXTDOMAIN),
        'sat'=>__('SAT',RasoloShipping::$TEXTDOMAIN),
        'ne'=>__('Night Express',RasoloShipping::$TEXTDOMAIN),
    );
    if(false===$crnt_dlv_code){
        return $dlvs;
    }
    if(empty($crnt_dlv_code)){
        return false;
    }
    unset($dlvs[0]);
    if(!isset($dlvs[$crnt_dlv_code])){
        return false;
    }
    return $dlvs[$crnt_dlv_code];
}

add_action('woocommerce_review_order_before_payment',function(){

    if(!class_exists('RasoloShipping')){
        return false;
    }

    // $checkout
    // $checkout->get_value( 'pts_selected' )
    // 'placeholder'   => __('Enter something','rasolo-shipping'),
    $rs_shipping_instance=new RasoloShipping();
    ?><div id="rasolo_delvcourier_block" class="rasolo_delvcompany_block">
        <h2><?php
        _e('Courier data',RasoloShipping::$TEXTDOMAIN)
        ?>
    </h2>
<label for="courier_title_input_rslt">
<?php
_e( 'Specify information about the final point of courier delivery','rasolo-shipping' );
?></label>
<input placeholder="<?php
_e( 'The exact address, how to find the house, floor, lock code, etc.',RasoloShipping::$TEXTDOMAIN );
?>" type="text" id="courier_title_input_rslt" name="courier_title_input_rslt" value=""/>
    </div>
<?php

//    $test01=$rs_shipping_instance->is_free_mode();
//    myvar_dump($test01,'$test01_01',1);
    $rs_api_key=$rs_shipping_instance->get_key();
    $delv_block_classes=['rasolo_delvcompany_block'];
    if($rs_free_mode=$rs_shipping_instance->is_free_mode()){
        $delv_block_classes[]='rs_free_mode';
    }

    ?><div class="<?php
    echo implode(' ',$delv_block_classes);
    ?>" id="rasolo_delvcompany_block">
    <h2><?php
        _e('Delivery point','rasolo-shipping')
        ?>
    </h2>

<div id="rs_emerg_delv">
    <p class="failure">
        <?php
_e( 'Server connection failure',RasoloShipping::$TEXTDOMAIN );
?>
    </p>
    </div><?php
    if($rs_free_mode){
        ?><label for="pts_title_input_rslt">
<?php
_e( 'Specify the point or office of delivery','rasolo-shipping' );
?></label>
<input type="hidden" name="rs_free_mode" value="1" />
<input placeholder="<?php
_e( 'Locality, branch or parcel machine number',RasoloShipping::$TEXTDOMAIN );
?>" type="text" id="pts_title_input_rslt"  name="pts_title_input_rslt" value=""/>
        <?php
    } else{
    ?>


    <div id="rasolo_delivery_checkout_field">
<?php
    $company_options=rs_get_delv_serv_arr();

    $reg_options=rs_get_regs_arr();

    add_action('wp_footer',function() use ($company_options,$rs_api_key){

        ?>
<script>
var dlv_comps=<?php echo json_encode($company_options, JSON_HEX_TAG); ?>;
var liveindroute = 'https://cp.ra-solo.com.ua/liveajaxsearch';
var local_err2='<?php
_e( 'An unexpected error has occurred','rasolo-shipping' );
?>.';
var local_err3='<?php
_e( 'Such a service does not exist or is not paid for',RasoloShipping::$TEXTDOMAIN );
?>.';
var rslt_title='<?php
_e( 'Such a city has been found',RasoloShipping::$TEXTDOMAIN );
?>.';
var id_trnsl='<?php
_e( 'Identifier',RasoloShipping::$TEXTDOMAIN );
?>';
var name_trnsl='<?php
_e( 'Name',RasoloShipping::$TEXTDOMAIN);
?>';
var phone_trnsl='<?php
_e( 'Phone',RasoloShipping::$TEXTDOMAIN);
?>';
var addr_trnsl='<?php
_e( 'Address',RasoloShipping::$TEXTDOMAIN);
?>';
var dlvcomp_trnsl='<?php
_e( 'Delivery Service',RasoloShipping::$TEXTDOMAIN);
?>';
var edit_trnsl='<?php
_e( 'Edit',RasoloShipping::$TEXTDOMAIN);
?>';
var select_city='<?php
_e( 'Select city',RasoloShipping::$TEXTDOMAIN);
?>';
var city_selected='<?php
_e( 'Such city has been selected',RasoloShipping::$TEXTDOMAIN);
?>';
var select_point='<?php
_e( 'Select point',RasoloShipping::$TEXTDOMAIN);
?>';
var point_selected='<?php
_e( 'Such a city has been selected',RasoloShipping::$TEXTDOMAIN);
?>';
var type_pts_info='<?php
_e( 'Specify the point or office of delivery',RasoloShipping::$TEXTDOMAIN);
?>';
var type_pts_ph='<?php
_e( 'Locality, branch or parcel machine number','rasolo-shipping' );
?>';
var rs_api_key='<?php
    echo $rs_api_key;
?>';
</script>
        <?php
    });

// classes of p: form-row form-control form-row-wide
// Class of span woocommerce-input-wrapper

    ?><p class="form-row form-control form-row-wide" id="crnt_comp_field"><label
  for="crnt_comp"><?php
        _e('Choose a delivery service',RasoloShipping::$TEXTDOMAIN)

        ?>&nbsp;</label><span><select
 name="crnt_comp" id="crnt_comp" class="select list_filter delv_filter">
<?php
foreach($company_options as $nth_key=>$nth_val){
    ?><option value="<?php
    echo $nth_key;
    ?>"><?php
    echo $nth_val;
    ?></option><?php
}
?>
</select></span></p><?php

    ?>
<input class="a" type="hidden" id="slctd_pts" name="slctd_pts"/>
<input class="a" type="hidden" id="slctd_comp" name="slctd_comp"/>
    <p class="form-row form-control form-row-wide" id="crnt_reg_field"><label
 for="crnt_reg"><?php
        _e('Buyer&apos;s region','rasolo-shipping');

        ?><span class="mandatory">*</span>&nbsp;</label><span><select
 name="crnt_reg" id="crnt_reg" class="select list_filter delv_filter">
<?php
foreach($reg_options as $nth_key=>$nth_val){
    ?><option value="<?php
    echo $nth_key;
    ?>"><?php
    echo $nth_val;
    ?></option><?php
}
?>
</select></span></p><?php


    ?>

<div id="cty_src_block" class="list_control">
    <p class="form-row form-control form-row-wide selected_ctxt" id="city_ctxt"><label
 for="cty_search"><?php
        _e('Find city',RasoloShipping::$TEXTDOMAIN)
        ?>&nbsp;</label><span><input
 data-ruany="Поиск контекста в любом месте названия города"
 data-rubeg="Поиск контекста в начале названия города"
 data-enany="Search for context anywhere in a city name"
 data-enbeg="Finding context at the beginning of a city name"
 type="text" class="live_search" name="cty_search" id="cty_search"></span>
    <a class="abramovka abramovka_any" href="#" title="Поиск контекста в любом месте названия города"></a>

    </p>

<p id="city_rslt" class="selected_rslt"
   class="form-row form-control form-row-wide"><span><?php
        _e('Such city has been selected','rasolo-shipping')
        ?></span>
    <span class="data_selected" id="cty_selected"></span>
<label for="cty_input_rslt"></label>
<input type="hidden" id="cty_input_rslt" name="cty_input_rslt" value=""/>

</p>
<div class="srch_output" id="cty_output"></div>

</div>
<div id="pts_src_block" class="list_control">
    <p class="form-row form-control form-row-wide selected_ctxt" id="pts_ctxt"><label
 for="pts_search"><?php
        _e('Find point',RasoloShipping::$TEXTDOMAIN)
        ?>&nbsp;</label><span><input disabled="disabled"
   data-ruany="Поиск контекста в любом месте названия пункта выдачи"
   data-rubeg="Поиск контекста в начале названия пункта выдачи"
   data-enany="Search for context anywhere in a point name"
   data-enbeg="Finding context at the beginning of a point  name"
      type="text" class="live_search" id="pts_search"></span>
        <a class="abramovka abramovka_any" href="#" title="Поиск контекста в любом месте названия пункта выдачи"></a>
    </p>
<p id="pts_rslt" class="selected_rslt"
   class="form-row form-control form-row-wide"><span><?php
        _e('Such point has been selected','rasolo-shipping')
        ?></span>
    <span class="data_selected" id="pts_selected"></span>
    <input class="rs_input_result" type="hidden" id="pts_title_input_rslt" name="pts_title_input_rslt" value=""/>
    <input class="rs_input_result" type="hidden" id="pts_serv_input_rslt" name="pts_serv_input_rslt" value=""/>
    <input class="rs_input_result" type="hidden" id="pts_addr_input_rslt" name="pts_addr_input_rslt" value=""/>
    <input class="rs_input_result" type="hidden" id="pts_phone_input_rslt" name="pts_phone_input_rslt" value=""/>
</p>
<div class="srch_output" id="pts_output"></div>


</div><!--  #pts_src_block -->

</div><!-- #rasolo_delivery_checkout_field --><?php
    } // The end of is_free_mode
    ?>

</div><!-- #rasolo_delvcompany_block -->


    <?php





});


// https://stackoverflow.com/questions/43532208/add-custom-checkout-field-to-order-in-woocommerce
add_action('woocommerce_checkout_process', function () {

    if(!class_exists('RasoloShipping')){
        return;
    }

// Check if set, if its not set add an error.
    // slctd_comp

    if(empty($_POST['shipping_method'])){
        wc_add_notice(__( 'You have to choose some shipping method',RasoloShipping::$TEXTDOMAIN ), 'error' );
        return;
    }

    if(empty($_POST['shipping_method'][0])){
        wc_add_notice(__( 'It is impossible to get the chosen shipping method','rasolo-shipping' ), 'error' );
        return;
    }
    $crnt_method=sanitize_text_field($_POST['shipping_method'][0]);
// rasolo_shipping rasolo_courier local_pickup:10

    if('rasolo_shipping'==$crnt_method){
        if ( empty( $_POST['pts_title_input_rslt']) ){
    //        wc_add_notice( implode(' ',array_keys($_POST)).strval(count(array_keys($_POST))).__( '_3_Please select a shipping destination',RasoloShipping::$TEXTDOMAIN), 'error' );
//            $arr_indexes=array_keys($_POST['shipping_method']);
            if(empty($_POST['rs_free_mode'])){
                wc_add_notice(__( 'Please select a pick-up point','rasolo-shipping' ), 'error' );
            } else {
                wc_add_notice(__( 'Please specify the post-office or pick-up point for the parcel delivery',RasoloShipping::$TEXTDOMAIN ), 'error' );
            }
            return;
        };
        $pts_result=sanitize_text_field($_POST['pts_title_input_rslt']);
        if ( mb_strlen($pts_result,'utf-8')<11 ){
            if(!empty($_POST['rs_free_mode'])){
                wc_add_notice(__( 'The post-office or pick-up point specification too short, type at least 10 symbols',RasoloShipping::$TEXTDOMAIN ), 'error' );
                return;
            }
        };

    } else if('rasolo_courier'==$crnt_method) {
        if ( empty( $_POST['courier_title_input_rslt']) ){
            wc_add_notice(__( 'Please specify courier data','rasolo-shipping' ), 'error' );
            return;
        }
        $courier_result=trim(sanitize_text_field($_POST['courier_title_input_rslt']));
        if ( mb_strlen($courier_result,'utf-8')<11 ){
            wc_add_notice(__( 'The courier data too short, type at least 10 symbols',RasoloShipping::$TEXTDOMAIN), 'error' );
            return;
        }

        //
//    } else if('local_pickup:10'==$crnt_method) {
// local_pickuphas been chosen...........
    }

});


add_action( 'woocommerce_checkout_update_order_meta', function ( $order_id ) {

    if(!class_exists('RasoloShipping')){
        return;
    }


// Delivery Point
    $pts_arr=array();
    if ( !empty( $_POST['pts_title_input_rslt']) ){
        $pts_arr[]='<div><span>'.__('Delivery information', RasoloShipping::$TEXTDOMAIN).':</span> <span>'.sanitize_text_field($_POST['pts_title_input_rslt']).'</span></div>';
    };

    if ( !empty( $_POST['crnt_reg']) ){
        $reg_options=rs_get_regs_arr();
        unset($reg_options['0']);
        $reg_index=trim(sanitize_text_field($_POST['crnt_reg']));
        if($reg_options[$reg_index]){
            $pts_arr[]='<div><span>'.__('Region', RasoloShipping::$TEXTDOMAIN).':</span> <span>'.$reg_options[$reg_index].'</span></div>';
        } else {
            $pts_arr[]='<div><span>'.__('Region', 'rasolo-shipping').':</span> <span>'.sanitize_text_field($_POST['crnt_reg']).'</span></div>';
        }

    }

    if ( !empty( $_POST['cty_input_rslt']) ){
        $pts_arr[]='<div><span>'.__('Locality', RasoloShipping::$TEXTDOMAIN).':</span> <span>'.sanitize_text_field($_POST['cty_input_rslt']).'</span></div>';
    }

    if ( !empty( $_POST['pts_addr_input_rslt']) ){
        $pts_arr[]='<div><span>'.__('Address', RasoloShipping::$TEXTDOMAIN).':</span> <span>'.sanitize_text_field($_POST['pts_addr_input_rslt']).'</span></div>';
    }

    if ( !empty( $_POST['pts_phone_input_rslt']) ){
        $pts_arr[]='<div><span>'.__('Phone', RasoloShipping::$TEXTDOMAIN).':</span> <span>'.sanitize_text_field($_POST['pts_phone_input_rslt']).'</span></div>';
    }

    if ( !empty( $_POST['courier_title_input_rslt']) ){
        $pts_arr[]='<div><span>'.__('Courier delivery information', RasoloShipping::$TEXTDOMAIN).':</span> <span>'.sanitize_text_field($_POST['courier_title_input_rslt']).'</span></div>';
    }




//    $pts_srv=rs_get_delv_serv_arr(sanitize_text_field( $_POST['slctd_comp']));
//    if (empty( $pts_srv) ){
//        return;
//    }

    if(!empty($pts_arr)){
        update_post_meta( $order_id, RS_SHIPPING_PTS_META_KEY, implode(chr(10),$pts_arr) );
    }

    if ( !empty( $_POST['pts_serv_input_rslt']) ){
        $pts_srv=sanitize_text_field($_POST['pts_serv_input_rslt']);
        $serv_info='<div><span>'.__('Delivery point', RasoloShipping::$TEXTDOMAIN).':</span> <span>'.$pts_srv.'</span></div>';

        update_post_meta( $order_id, RS_SHIPPING_COMP_META_KEY, $serv_info  );
    } else {
        delete_post_meta( $order_id, RS_SHIPPING_COMP_META_KEY  );
    }


});


add_action( 'woocommerce_admin_order_data_after_billing_address',function($order){
    if(!is_object($order)){
        return;
    }
    $slctd_comp = get_post_meta( $order->get_id(), RS_SHIPPING_COMP_META_KEY, true );
    $slctd_pts = get_post_meta( $order->get_id(), RS_SHIPPING_PTS_META_KEY, true );
    // Display the custom field:
    if(empty($slctd_pts)){
        return;
    }
    if(!empty($slctd_comp)){
        echo '<p><strong>' . __('Delivery Service', 'rasolo-shipping') . ': </strong>' . $slctd_comp  . '</p>'.chr(10);
    }

    echo '<p><strong>' . __('Delivery information',RasoloShipping::$TEXTDOMAIN) . ': </strong>' . $slctd_pts   . '</p>';

});


//add_filter( 'woocommerce_enable_order_notes_field', '__return_false', 1);
add_filter('woocommerce_checkout_fields', function($fields){
    $fields["shipping"]=array();
//    unset($fields['order']['order_comments']);
//    unset($fields["shipping"]);
//    rasolo_debug_to_file($fields,'$fields_5452523');
//    rasolo_debug_to_file(false,null);
    return $fields;
},99);

if(!function_exists('is_cart') || !function_exists('is_checkout') || is_cart() || is_checkout()){
    function rasolo_delivery_init() {
        wp_register_style('rasolo_delivery', rs_get_dlv_plugin_dir().'/css/style.css');
        wp_enqueue_style('rasolo_delivery');
    }

    add_action( 'init','rasolo_delivery_init');
}

/*
//  Display field value on the mail body
add_filter( 'woocommerce_email_order_meta_fields', 'rasolo_custom_checkout_field_display_email', 10, 1 );
function rasolo_custom_checkout_field_display_email( $fields, $sent_to_admin, $order  )
       {
if(!is_object($order)){
    return $fields;
}
$ord_id=$order->get_id();

$delv_serv=get_post_meta( $ord_id, RS_SHIPPING_COMP_META_KEY, true );
if(!empty($delv_serv)){
    $fields['delv_serv'] = array(
            'label' => __('Delivery Service','rasolo-shipping'),
            'value' => $delv_serv,
        );
}


$delv_point=get_post_meta( $ord_id, RS_SHIPPING_PTS_META_KEY, true );
if(!empty($delv_point)){
    $fields['delv_point'] = array(
            'label' => __('Delivery Point','rasolo-shipping'),
            'value' => $delv_point,
        );
}
           // Delivery Point
return $fields;
       } // The end of rasolo_custom_checkout_field_display_email
*/

add_filter( 'woocommerce_email_order_meta_fields', function ( $fields, $sent_to_admin, $order ) {
    if(!class_exists('RasoloShipping')){
        return $fields;
    }

    if(!is_object($order)){
        return $fields;
    }
    $this_id=intval($order->id);
    if($this_id<1){
        return $fields;
    }
    $delv_serv=get_post_meta( $this_id, RS_SHIPPING_COMP_META_KEY, true );
    if(!empty($delv_serv)){
        $fields['rs_delv_serv'] = array(
            'label' => __( 'Delivery Service','rasolo-shipping' ),
            'value' => $delv_serv,
        );
    }
    $delv_point=get_post_meta( $this_id, RS_SHIPPING_PTS_META_KEY, true );
    if(!empty($delv_point)){
        $fields['rs_delv_point'] = array(
            'label' => __( 'Delivery information',RasoloShipping::$TEXTDOMAIN ),
            'value' => $delv_point,
        );
    }

    return $fields;
}, 10, 3 );

add_action('woocommerce_order_details_after_order_table',function($order){
    if(!is_object($order)){
        return;
    }
    $some_id=intval($order->get_id());
    if($some_id<1){
        return;
    }
    $delv_serv=get_post_meta( $some_id, RS_SHIPPING_COMP_META_KEY, true );
    $res_arr=[];
    if(!empty($delv_serv)){
        $res_arr[]=$delv_serv;
    }

    $delv_point=get_post_meta( $some_id, RS_SHIPPING_PTS_META_KEY, true );
    if(!empty($delv_point)){
        $res_arr[]=$delv_point;
    }

    if(empty($res_arr)){
        return;
    }
    echo implode(chr(10),$res_arr);
});