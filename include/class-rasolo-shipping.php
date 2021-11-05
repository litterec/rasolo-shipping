<?php
if ( !class_exists( 'RasoloShipping' ) ) {
    class RasoloShipping{
	    static private $DB_OPTIONS_KEY = '_rasolo_shipping';
	    static public $TEXTDOMAIN = 'rasolo-shipping';
	    static public $AJAXURL = 'https://cp.ra-solo.com.ua/liveajaxsearch';
	    static private $CAPABILITY = 'view_woocommerce_reports';
	    static private $SUBSCRIBE_VRF_DELAY = 12; // In seconds

	    static private $UNREDEEMED_REASONS = array(
            792=>'The current password is absent',
            677=>'The origin header is absent',
            791=>'The current domain name is absent',
            793=>'There is no appropriate account for your domain',
            18994=>'Payment for the service is overdue',
            994=>'Account for your domain has not been found',
            794=>'Your password is incorrect',
            803=>'User settings for your domain are not correct',
            804=>'The user account for your domain is currently locked',
            19331=>'The current user level is insufficient for this service',
        );

	    static private $ALLCITIES = array(
            689558=>'Vinnytsya',
            702569=>'Lutsk',
            709930=>'Dnipro',
            700051=>'Nikopol',
            697889=>'Pavlohrad',
            709717=>'Donetsk',
            713716=>'Alchevs‘k',
            712451=>'Berdyans‘k',
            707753=>'Horlivka',
            704508=>'Kramatorsk',
            702320=>'Makiivka',
            701822=>'Mariupol',
            693468=>'Sloviansk',
            686967=>'Zhytomyr',
            690548=>'Uzhhorod',
            687700=>'Zaporizhia',
            701404=>'Melitopol',
            707471=>'Ivano-Frankivs‘k',
            703448=>'Kyiv',
            712165=>'Bila Tserkva',
            705812=>'Kropyvnytskyi',
            702658=>'Luhansk',
            691999=>'Syevyerodonets‘k',
            702550=>'Lviv',
            700569=>'Mykolayiv',
            698740=>'Odesa',
            696643=>'Poltava',
            704147=>'Kremenchuk',
            695594=>'Rivne',
            692194=>'Sumy',
            691650=>'Ternopil',
            706483=>'Kharkiv',
            706448=>'Kherson',
            706369=>'Khmel‘nyts‘kyy',
            710791=>'Cherkasy',
            710735=>'Chernihiv',
            710719=>'Chernivtsi',
            694423=>'Sevastopol',
            693805=>'Simferopol',
            706524=>'Kerch',
        );
        static private $REGCITIES=array(701822,702320,694423,693805,707753,704147,712165,704508,701404,706524,700051,693468,712451,713716,697889,691999);

        private $api_key=false;
        private $cart_total=false;
        private $msg_instance=false;
        private $is_paid_up=false;
        private $free_mode=false;
        private $unpaid_reason=false;
        private $subsr_last_check_time=0;
        private $cour_thres=false;
        private $cour_cost=false;
        private $delv_thres=false;
        private $delv_cost=false;
        private $cour_cities=[];
        private $show_reg_cities=[];

        function __construct(){

            $this->fill_cart_total();

            $this_options=get_option(self::$DB_OPTIONS_KEY);
//            myvar_dump($this_options,'$this_options_3234_22_011');
            if(!is_array($this_options)){
                try {
                    $this_options=@unserialize($this_options);
                } catch(Exception $e) {
                    $this_options=array('key'=>false,'checktime'=>0);
                }
            }

            if(!empty($this_options) && is_array($this_options)){
                if(!empty($this_options['key'])){
                    $this->api_key=$this_options['key'];
                }
                if(!empty($this_options['checktime']) && is_integer($this_options['checktime'])){
                    $this->subsr_last_check_time=intval($this_options['checktime']);
                    if($this->subsr_last_check_time>intval(current_time('timestamp'))){
                        $this->subsr_last_check_time=0;
                    }
                }
                if(!empty($this_options['subscribed'])){
                    $this->is_paid_up=true;
                }

                if(!empty($this_options['freemode'])){
                    $this->free_mode=true;
                }

                if(!empty($this_options['crrthres'])){
                    $this->cour_thres=abs(round(floatval($this_options['crrthres']),2));
                    if($this->cour_thres>10000000.){
                        $this->cour_thres=10000000.;
                    }
                    if($this->cour_thres<0.01){
                        $this->cour_thres=false;
                    }
                }

                if(!empty($this_options['crrcost'])){
                    $this->cour_cost=abs(round(floatval($this_options['crrcost']),2));
                    if($this->cour_cost>10000000.){
                        $this->cour_cost=10000000.;
                    }
                    if($this->cour_cost<0.01){
                        $this->cour_cost=false;
                    }
                }

                if(!empty($this_options['delvthres'])){
                    $this->delv_thres=abs(round(floatval($this_options['delvthres']),2));
                    if($this->delv_thres>10000000.){
                        $this->delv_thres=10000000.;
                    }
                    if($this->delv_thres<0.01){
//                        myvar_dump($this,'crrthres02');
                        $this->delv_thres=false;
                    }
                }

                if(!empty($this_options['delvcost'])){
                    $this->delv_cost=abs(round(floatval($this_options['delvcost']),2));
                    if($this->delv_cost>1000000.){
                        $this->delv_cost=1000000.;
                    }
                    if($this->delv_cost<0.01){
                        $this->delv_cost=false;
                    }
                }

                if(!empty($this_options['reason'])){
                    $this->unpaid_reason=$this_options['reason'];
                }
                if(!empty($this_options['checktime'])){
                    $this->subsr_last_check_time=$this_options['checktime'];
                }

                if(!empty($this_options['courcities'])){
                    $this->cour_cities=$this_options['courcities'];
                }

                if(!empty($this_options['showregcities'])){
                    $this->show_reg_cities=true;
                }

//                myvar_dump($this_options,'$this_options_3234_1');
//                myvar_dd($this,'$this_options_3234_2');

//            } else {
//                $this->save_data();
            }
            if(class_exists('RasoloAdminMessages')){
                $this->msg_instance=new RasoloAdminMessages;
            };

        } // The end of __construct

        private function fill_cart_total(){
            global $woocommerce;
            if(false===$this->cart_total){
                if(is_object($woocommerce)){
                    if(is_object($woocommerce->cart )){
                        $carttotal = floatval($woocommerce->cart->cart_contents_total)+floatval($woocommerce->cart->tax_total);
                        if(abs($carttotal)>0.01){
                            $this->cart_total=$carttotal;
                        }
                    }
                }
            }

        } // The end of fill_cart_total

        public function admin_options_page(){
            if(!self::verify_user_access('from01')){
                return;
            }
            $this->check_paid_up();
                       ?><div class="wrap">
<form method="post" class="rs_admin_form" novalidate="novalidate">
<h2 class="left_h1"><?php
    _e( 'Ra-Solo Shipping options', self::$TEXTDOMAIN );
    ?></h2>
<hr />
<table class="form-table" role="presentation">

<tbody>
<tr>
<th scope="row"><label for="rs_api_key"><?php
    _e( 'Ra-Solo API key', self::$TEXTDOMAIN );
    ?></label></th>
<td><input name="rs_api_key" type="text" id="rs_api_key" value="<?php
    echo $this->api_key;
    ?>"
 class="regular-text"></td>
</tr>
<tr>
<th scope="row"><?php
    _e( 'Subscription status', self::$TEXTDOMAIN );
    ?></th>
<td><?php
//    myvar_dump($this,'$this_3423223',1);
    echo $this->is_paid_up?__( 'Activated', self::$TEXTDOMAIN ):__( 'Inactive', self::$TEXTDOMAIN );
    echo ' ( '.__( 'last check time', self::$TEXTDOMAIN ).' '.date('Y-m-d H:i:s',$this->subsr_last_check_time);
    $next_check_remain=self::$SUBSCRIBE_VRF_DELAY-(intval(current_time('timestamp'))-$this->subsr_last_check_time);
    if($next_check_remain<0){
        $next_check_remain=1;
    }
    echo ', '.sprintf(__( 'the next one in a %d sec', self::$TEXTDOMAIN ),$next_check_remain).' )';
    if(!$this->is_paid_up){
        if($this->unpaid_reason){
            echo ', '.__( 'the reason message', self::$TEXTDOMAIN ).': ';
            echo $this->unpaid_reason;
        } else {
            echo ', '.__( 'the reason is unknown', self::$TEXTDOMAIN ).'.';
        }
    }

    ?></td>
</tr>
<tr
<?php
    echo $this->free_mode?' title="'.__('Plugin functionality is limited, there are no requests to an external server.', self::$TEXTDOMAIN).'"':' title="'.__('All plugin functionality is activated, there are requests to an external server.', self::$TEXTDOMAIN).'"';
?>
    >
<th scope="row"><?php
    _e( 'Toggle free mode', self::$TEXTDOMAIN );
    ?></th>
<td><label><input type="checkbox" id="free_mode_switch" name="free_mode_switch"
                  <?php
echo $this->free_mode?' checked="checked"':'';

    ?> ></label>
    <span class="rs_admin_descr rs_admin_checkbox_descr"><?php
_e( 'Enable this mode if you do not need to use the full functionality of the plugin.<br>After turning on the free mode, there will be no requests to the external server.', self::$TEXTDOMAIN )

    ?></span>

    </td>
</tr><tr>
<th scope="row"><?php
    _e( 'Courier free delivery threshold', self::$TEXTDOMAIN );
    ?></th>
<td><label for="crrthres"><input
            min="0.0" max="1000000.0" class="admin_float"
            type="number" step="0.01" name="crrthres" id="crrthres"
 value="<?php
    echo round($this->cour_thres,2);
    ?>"
            /></label>     <span class="rs_admin_descr rs_admin_float_descr"><?php
_e( 'If the buyer puts goods to the cart for an amount greater or exceeding<br> the specified threshold, he receives free courier delivery.', self::$TEXTDOMAIN )

    ?></span>
<?php
?></td>
</tr><tr>
<th scope="row"><?php
    _e( 'Courier delivery cost', self::$TEXTDOMAIN );
    ?></th>
<td><label for="crrcost"><input
            min="0.0" max="1000000.0" class="admin_float"
            type="number" step="0.01" name="crrcost" id="crrcost"
 value="<?php
    echo round($this->cour_cost,2);
    ?>"
            /></label>     <span class="rs_admin_descr rs_admin_float_descr"><?php
_e( 'The specified cost of delivery by courier will be included in the order amount,<br> if the purchase amount does not exceed the threshold value.', self::$TEXTDOMAIN )

    ?></span>
<?php
?></td>
</tr>
<tr>
<th scope="row"><?php
    _e( 'Postal company free delivery threshold', self::$TEXTDOMAIN );
    ?></th>
<td><label for="delvthres"><input
            min="0.0" max="1000000.0" class="admin_float"
            type="number" step="0.01" name="delvthres" id="delvthres"
 value="<?php
    echo round($this->delv_thres,2);
    ?>"
            /></label>     <span class="rs_admin_descr rs_admin_float_descr"><?php
_e( 'If the buyer puts products to the cart for an amount greater than or exceeding<br> the specified threshold, he will receive free shipping by the postal company.', self::$TEXTDOMAIN )

    ?></span>
<?php
?></td>
</tr><tr>
<th scope="row"><?php
    _e( 'Postal company shipping cost', self::$TEXTDOMAIN );
    ?></th>
<td><label for="delvcost"><input
            min="0.0" max="1000000.0" class="admin_float"
            type="number" step="0.01" name="delvcost" id="delvcost"
 value="<?php
    echo round($this->delv_cost,2);
    ?>"
            /></label>     <span class="rs_admin_descr rs_admin_float_descr"><?php
_e( 'The specified cost of delivery by the postal company will be included in the order amount,<br> if the purchase amount does not exceed the threshold value.', self::$TEXTDOMAIN )
    ?></span>
<?php
?></td>
</tr>





<tr>
<th scope="row"><?php
    _e( 'Courier delivery cities', self::$TEXTDOMAIN );
    ?>
   <div class="rs_admin_cts_descr"> <label
 id="admin_reg_switch" class="admin_float" for="showregcities"><input
 type="checkbox" id="showregcities"
 <?php if($this->show_reg_cities){
 echo ' checked="checked"';
 }
 ?>
 name="showregcities"><?php
    _e( 'More cities', self::$TEXTDOMAIN );
    ?></label></div>
</th>
<td><?php

    foreach(self::$ALLCITIES as $nth_key=>$nth_cty){
        $cty_translated=__($nth_cty,self::$TEXTDOMAIN);
        $is_reg_cty=in_array($nth_key,self::$REGCITIES);
        if(!$this->show_reg_cities && $is_reg_cty){
            continue;
        }

    ?> <label class="admin_float admin_cty<?php
        if($is_reg_cty){
            echo ' admin_reg_cty';
        }
        ?>" for="cty<?php echo $nth_key; ?>"><input

<?php
if(in_array($nth_key,$this->cour_cities)){
    echo ' checked="checked" ';
}
?>

            type="checkbox" name="courcities[<?php
            echo $nth_key;
            ?>]" id="cty<?php echo $nth_key; ?>"
 value="<?php
 if(in_array($nth_key,$this->cour_cities)){
     ?> checked="checked" <?php
 }
    ?>"
            /> <?php
            echo $cty_translated;
            ?></label> <?php


    } // The end of foreach self::$ALLCITIES



    ?>    <div class="rs_admin_cts_descr"><?php
_e( 'Select the cities in which your company will provide the purchase courier delivery', self::$TEXTDOMAIN )
    ?></div>
<?php
?></td>
</tr>








</table>
<p class="submit"><input type="submit" name="submit" id="submit"
 class="button button-primary" value="<?php
    _e( 'Save changes', self::$TEXTDOMAIN );
    ?>"></p>
</form>
</div><?php


        }   // The end of admin_options_page

        private static function verify_user_access($debug_1=false,$debug_2=false){
//	    	$min_cpb=$this->get_current_capability();
    		$min_cpb=self::$CAPABILITY;

            if(current_user_can($min_cpb)){
                return true;
            };
//            myvar_dd(compact('debug_1','debug_2'),'somedebuginded');
//            die('user_access_failure2');

            return false;
    	} // The end of verify_user_access

        public function get_options_page_arguments(){
            $untranslatable_01='ru_RU'<>get_locale()?'Ra-Solo shipping plugin options page':'Страница настроек плагина доставки Ra-Solo';
            $opt_name=__( 'The rasolo shipping options', self::$TEXTDOMAIN );
            if('ru_RU'==get_locale()){
                $opt_name='Параметры плагина доставки &ndash; &laquo;Ra-Solo&raquo;';
            }
        return array(
            __( $untranslatable_01, self::$TEXTDOMAIN),
            $opt_name,
            self::$CAPABILITY,
            'rasolo_shipping_options_page',
            array($this, 'admin_options_page')
        );

        }

        private function put_adm_message($msg_txt,$msg_status='info',$msg_dismiss=false){
            if(empty($this->msg_instance)){
                return;
            }
            $this->msg_instance->set_message($msg_txt,$msg_status,$msg_dismiss);
        }


        private function save_data($debug_from=false){
//            if('processpost'==$debug_from){
//                myvar_dump($this,'free_mode_switch_not_empty5');
//            }
            if(!self::verify_user_access('fromsavedata',$debug_from)){
                return;
            }
//            if('processpost'==$debug_from){
//                myvar_dump($this,'free_mode_switch_not_empty15');
//            }
            $this->check_paid_up();
            $arr_to_save=array();
            $arr_to_save['key']=$this->api_key;

            if($this->is_paid_up){
                $arr_to_save['subscribed']=$this->is_paid_up;
            }
            if($this->unpaid_reason){
                $arr_to_save['reason']=$this->unpaid_reason;
            }
            if($this->cour_thres){
                $arr_to_save['crrthres']=round($this->cour_thres,2);
            }
            if($this->cour_cost){
                $arr_to_save['crrcost']=round($this->cour_cost,2);
            }
            if($this->delv_thres){
                $arr_to_save['delvthres']=round($this->delv_thres,2);
            }
            if($this->delv_cost){
                $arr_to_save['delvcost']=round($this->delv_cost,2);
            }

            if($this->show_reg_cities){
                $arr_to_save['showregcities']=true;
            }

            if(!empty($this->cour_cities)){
                $arr_to_save['courcities']=$this->cour_cities;
            }

            if($this->subsr_last_check_time>0 || !empty($this->subsr_last_check_time)){
                $arr_to_save['checktime']=$this->subsr_last_check_time;
            }
            if($this->free_mode){
                $arr_to_save['freemode']=1;
            } else {
                $arr_to_save['freemode']=0;
            }

//            if('processpost'==$debug_from){
//                myvar_dump($arr_to_save,'free_mode_switch_not_empty16');
//                myvar_dump($this,'free_mode_switch_not_empty17');
//            }


            update_option(self::$DB_OPTIONS_KEY,$arr_to_save);
//            if('processpost'==$debug_from){
//                myvar_dump($arr_to_save,'free_mode_switch_not_empty18');
//                myvar_dd($this,'free_mode_switch_not_empty19');
//            }

//            myvar_dump($debug_from,'$debug_from_323423234_1');
//            myvar_dump($arr_to_save,'$arr_to_save_323423234_1');
//            myvar_dd($this,'$this_323423234_1');
        }

        public function after_setup_theme(){
            if(empty($_POST['submit'])){
                return;
            }
            if(!self::verify_user_access('from_process')){
                return;
            }
            $need_save=false;
            $debug_arr=[];

            if(!empty($_POST['rs_api_key'])){
                $new_key_val=sanitize_text_field($_POST['rs_api_key']);
                if($new_key_val<>$this->api_key){
                    $need_save=true;
                    $debug_arr[]=1;
                    $this->api_key=$new_key_val;
                }
            }

            if(!empty($_POST['crrthres'])){
                $crr_thres=sanitize_text_field($_POST['crrthres']);
//                myvar_dump($this,'crrthres01');
                if(!empty($crr_thres)){
                    $crr_tth_float=round(floatval($crr_thres),2);
//                    myvar_dump($crr_tth_float,'$crr_tth_float02');
//                    myvar_dump($this,'crrthres02');
                    if($crr_tth_float>=0.01){
                        if(abs(floatval($this->cour_thres)-$crr_tth_float)>=0.01){
                            $this->cour_thres=$crr_tth_float;
                            $debug_arr[]=2;
                            $need_save=true;
                        }
//                        myvar_dump($crr_tth_float,'$crr_tth_float03');
//                        myvar_dump($this,'crrthres03_01');

                    }
                }

            } else {
                if(!empty($this->cour_thres)){
                    $need_save=true;
                }
                $this->cour_thres=false;
            }

            if(!empty($_POST['crrcost'])){
                $crr_cost=sanitize_text_field($_POST['crrcost']);
                if(!empty($crr_cost)){
                    $crr_cost_float=round(floatval($crr_cost),2);
                    if($crr_cost_float>=0.01){
                        if(abs(floatval($this->cour_cost)-$crr_cost_float)>=0.01){
                            $this->cour_cost=$crr_cost_float;
                            $need_save=true;
                            $debug_arr[]=3;
                        }
                    }
                }
            } else {
                if(!empty($this->cour_cost)){
                    $need_save=true;
                    $debug_arr[]=4;
                }
                $this->cour_cost=false;
            }

            if(!empty($_POST['delvthres'])){
                $delv_thres=sanitize_text_field($_POST['delvthres']);
                if(!empty($delv_thres)){
                    $delv_thres_float=round(floatval($delv_thres),2);
                    if($delv_thres_float>=0.01){
                        if(abs(floatval($this->delv_thres)-$delv_thres_float)>=0.01){
                            $this->delv_thres=$delv_thres_float;
                            $need_save=true;
                            $debug_arr[]=5;
                        }
                    }
                }
            } else {
                if(!empty($this->delv_thres)){
                    $need_save=true;
                    $debug_arr[]=6;
                }
                $this->delv_thres=false;
            }

            if(!empty($_POST['delvcost'])){
                $delv_cost=sanitize_text_field($_POST['delvcost']);
                if(!empty($delv_cost)){
                    $delv_cost_float=round(floatval($delv_cost),2);
                    if($delv_cost_float>=0.01){
                        if(abs(floatval($this->delv_cost)-$delv_cost_float)>=0.01){
                            $this->delv_cost=$delv_cost_float;
                            $need_save=true;
                            $debug_arr[]=7;
                        }
                    }
                }
            } else {
                if(!empty($this->delv_cost)){
                    $need_save=true;
                    $debug_arr[]=8;
                }
                $this->delv_cost=false;
            }

//            myvar_dump($this,'crrthres04');

            if(empty($_POST['free_mode_switch'])){
//                myvar_dd($_POST,'free_mode_switch_is_empty');
                if($this->free_mode){
                    $need_save=true;
                    $debug_arr[]=9;
                }
                $this->free_mode=false;
            } else {
//                myvar_dump($_POST,'free_mode_switch_not_empty');
                if(!$this->free_mode){
//                    myvar_dump($_POST,'free_mode_switch_not_empty2');
                    $need_save=true;
                    $debug_arr[]=10;
                }

                $this->free_mode=true;
//                myvar_dump($this,'free_mode_switch_not_empty3');
            }

            if(empty($_POST['showregcities'])){
//                myvar_dd($_POST,'free_mode_switch_is_empty');
                if($this->show_reg_cities){
                    $need_save=true;
                    $debug_arr[]=11;
                }
                $this->show_reg_cities=false;
            } else {
                if(!$this->show_reg_cities){
//                    myvar_dump($_POST,'free_mode_switch_not_empty2');
                    $need_save=true;
                    $debug_arr[]=12;
                }

                $this->show_reg_cities=true;
//                myvar_dump($this,'free_mode_switch_not_empty3');
            }

            if( !empty($_POST['courcities']) && is_array($_POST['courcities']) ){
//                $arr_keys=array_keys($_POST['courcities']);
//                myvar_dump($arr_keys,'$arr_keys_34525235');
//                myvar_dump($this,'free_mode_switch_not_empty_34525235');
//                myvar_dd($_POST,'free_mode_switch_not_empty_34525235');
                $old_courcities=$this->cour_cities;
                $this->cour_cities=array();
                foreach($_POST['courcities'] as $cty_key=>$nth_cty_code){
                    $code_sanitized=intval($cty_key);
                    if(empty(self::$ALLCITIES[$code_sanitized])){
                        continue;
                    }
                    $this->cour_cities[]=$code_sanitized;
                }
                if(count($old_courcities) != count($this->cour_cities) || array_diff($old_courcities, $this->cour_cities) !== array_diff($this->cour_cities, $old_courcities)){
                    $need_save=true;
                    $debug_arr[]=13;
//                    $debug_arr['a13']=count($old_courcities);
                }
            }

            if($need_save){
//                myvar_dump($this,'free_mode_switch_not_empty4');
                $this->save_data('processpost');
//                myvar_dump($this,'crrthres06_01');
                $this->put_adm_message(__('Your changes have been saved',self::$TEXTDOMAIN));
            } else {
//                myvar_dd($this,'free_mode_switch_not_empty6');
                if(!empty($_POST['submit'])){
                    $this->put_adm_message(__('No need to save the same data',self::$TEXTDOMAIN));
                }
            }
//            myvar_dump($debug_arr,'$debug_arr07_01');
//            myvar_dd($this,'crrthres07_01');

        } // The end of after_setup_theme

        public function get_key(){
            return $this->api_key;
        }
        private  function check_paid_up(){
// https://wordpress.stackexchange.com/questions/7278/how-can-you-check-if-you-are-in-a-particular-page-in-the-wp-admin-section-for-e
            global $pagenow;
            if(empty($_GET['page'])){
                return;
            }
            if('rasolo_shipping_options_page'<>$_GET['page']){
                return;
            }
            if(current_user_can('manage_options')){
                if('options-general.php'<>$pagenow){
                    return;
                }

            } else if(current_user_can(self::$CAPABILITY)) {
                if('admin.php'<>$pagenow){
                    return;
                }
            } else {
                return;
            }

            if(empty($_GET['page'])){
                return;
            }
            if('rasolo_shipping_options_page'<>$_GET['page']){
                return;
            }
            if(empty($this->api_key)){
                return;
            }
// May be we checked the subscription not so long ago
//            if($this->is_paid_up){
            $time_elapsed=intval(current_time('timestamp'))-intval($this->subsr_last_check_time);
            if($time_elapsed<self::$SUBSCRIBE_VRF_DELAY){
//                    myvar_dump('just_returning');
//                    myvar_dump($time_elapsed,'$time_elapsed just_returning');
                return;
            }
//            }
            $this->is_paid_up=false;
            $this->unpaid_reason=__( 'Unknown reason', self::$TEXTDOMAIN );

            $rs_headers=array('Origin: https://'.$_SERVER['HTTP_HOST']);

            $args = array(
                'rsaction'        => 'is_paid_up',
                'serv_passw'        => $this->api_key
            );

            $curl = curl_init(self::$AJAXURL );
            curl_setopt( $curl, CURLOPT_POST, true );
            curl_setopt( $curl, CURLOPT_POSTFIELDS, $args );
            curl_setopt( $curl, CURLOPT_RETURNTRANSFER, true );
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($curl, CURLOPT_HTTPHEADER, $rs_headers);
            try {
                $rs_postres = curl_exec( $curl );
            } catch(Exception $e) {
                return;
            }
            $some_err=curl_error($curl);
            curl_close( $curl );
            if(empty($rs_postres)){
                return;
            }

            try {
                $res_unser=@json_decode($rs_postres,true);
            } catch(Exception $e) {
                return;
            }
            $this->subsr_last_check_time=intval(current_time('timestamp'));
//            sleep(1);
            $crnt_time=current_time('timestamp');
            $last_check_human=date('Y-m-d H:i:s',$this->subsr_last_check_time);
            $crnt_time_human=date('Y-m-d H:i:s',$crnt_time);
//            myvar_dump(compact('last_check_human','crnt_time','crnt_time_human'),'$crnt_time_letusverify');
//            myvar_dump($this,'$this_letusverify');
            if(empty($res_unser['rc'])){
                return;
            }
            if(!is_integer($res_unser['rc'])){
                return;
            }
//            myvar_dump($res_unser,'$res_unser_342323');
            if(!empty($res_unser['newpassw'])){
                $this->api_key=sanitize_text_field($res_unser['newpassw']);
//                myvar_dump($this,'$newpasswreceivedthis_342323');

//            } else {
//                myvar_dump($this,'$newpassdidnotreceivedthis_342323');
            }
//            myvar_dump($this,'$this_unser_342342');
//            myvar_dump($res_unser,'$res_unser_342342');

            if(200<>$res_unser['rc']){
                if(isset(self::$UNREDEEMED_REASONS[$res_unser['rc']])){
                    $this->unpaid_reason=sanitize_text_field(trim(self::$UNREDEEMED_REASONS[$res_unser['rc']]));
                    $this->unpaid_reason=__( $this->unpaid_reason, self::$TEXTDOMAIN );
                }
                return;
            }

            $this->is_paid_up=true;
            $this->unpaid_reason=false;
            $this->save_data();
//            myvar_dump($res_unser,'$res_unser_aa32t324t3432');
//            myvar_dump($this,'$rs_postres_aa32t324t3432');
//            myvar_dd(compact('res_unser','rs_postres','some_err'),'$rs_postres');

        }

        public function is_free_mode(){
            return $this->free_mode;
        }

        public function get_courier_cost(){
            $this->fill_cart_total();
            if(false===$this->cart_total){
                return 109.;
            }

            if(false===$this->cour_thres){
                return 110.;
            }

            if($this->cart_total>=$this->cour_thres){
                return 111.;
            } else {
                return $this->cour_cost;
            }
        } // The end of courier_cost

        public function get_delv_cost(){
            $this->fill_cart_total();
            if(false===$this->cart_total){
                return false;
            }

            if(false===$this->delv_thres){
                return false;
            }

            if($this->cart_total>=$this->delv_thres){
                return false;
            } else {
                return $this->delv_cost;
            }
        } // The end of courier_cost

        public function is_city_courier($cty_code){
            return in_array($cty_code,$this->cour_cities);
        }

        public function woocommerce_before_checkout_form(){
            $this->echo_dsc_msg();
        } // The end of woocommerce_before_checkout_form

        public function woocommerce_before_cart(){
            $this->echo_dsc_msg();
        } // The end of woocommerce_before_cart

        private function echo_dsc_msg(){

            // There is the  local_pickup:24 method lavel
            $method_id=strval(WC()->session->get( 'chosen_shipping_methods' )[0]);
            if('rasolo_shipping'==$method_id){
                if(false===$this->delv_cost){
                    return;
                }
                if(false===$this->delv_thres){
                    return;
                }
                $this->fill_cart_total();
                if(false===$this->cart_total){
                    return;
                }
                if($this->cart_total<0.01){
                    wc_print_notice(
                        sprintf(
                        __('Get free delivery by the postal company – purchase products for the amount of% s UAH.', self::$TEXTDOMAIN )
                            ,strval($this->delv_thres))
                        , 'notice' );
                    return;
                }
                if($this->cart_total<$this->delv_thres){
                    $cart_diff=$this->delv_thres-$this->cart_total;
                    wc_print_notice(
                        sprintf(
                        __('Add another %s UAH to your cart and get free shipping by postal company.', self::$TEXTDOMAIN )
                            ,strval($cart_diff))
                        , 'notice' );
                    return;
                }

                wc_print_notice(
                        sprintf(
                        __('Your purchase will be delivered free of charge by the postal company, since the order amount has reached %s UAH.', self::$TEXTDOMAIN )
                            ,strval($this->delv_thres))
                        , 'notice' );
                    return;

            } else if('rasolo_courier'==$method_id) {
                if(false===$this->cour_cost){
                    return;
                }
                if(false===$this->cour_thres){
                    return;
                }
                $this->fill_cart_total();
                if(false===$this->cart_total){
                    return;
                }

                if($this->cart_total<0.01){
                    wc_print_notice(
                        sprintf(
                        __('Get free delivery by courier – purchase products for the amount of %s UAH.', self::$TEXTDOMAIN )
                            ,strval($this->cour_thres))
                        , 'notice' );
                    return;
                }
                if($this->cart_total<$this->cour_thres){
                    $cart_diff=$this->cour_thres-$this->cart_total;
                    wc_print_notice(
                        sprintf(
                        __('Add another %s UAH to your cart and get free courier delivery.', self::$TEXTDOMAIN )
                            ,strval($cart_diff))
                        , 'notice' );
                    return;
                }

                wc_print_notice(
                        sprintf(
                        __('Your purchase will be delivered free of charge by courier, since the order amount has reached %s UAH.', self::$TEXTDOMAIN )
                            ,strval($this->cour_thres))
                        , 'notice' );
                    return;

            }

        } // The end of echo_dsc_msg


    }
}
