<?php
if ( !class_exists( 'RasoloShipping' ) ) {
    class RasoloShipping{
	    static private $DB_OPTIONS_KEY = '_rasolo_shipping';
	    static public $TEXTDOMAIN = 'rasolo-shipping';
	    static public $AJAXURL = 'https://cp.ra-solo.com.ua/liveajaxsearch';
	    static private $CAPABILITY = 'view_woocommerce_reports';
	    static private $SUBSCRIBE_VRF_DELAY = 120;
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

        private $api_key=false;
        private $msg_instance=false;
        private $is_paid_up=false;
        private $free_mode=false;
        private $unpaid_reason=false;
        private $subsr_last_check_time=0;

        function __construct(){
            $this_options=get_option(self::$DB_OPTIONS_KEY);
//            myvar_dump($this_options,'$this_options_3234_22_011');
            if(!is_array($this_options)){
                try {
                    $this_options=@unserialize($this_options);
                } catch(Exception $e) {
                    $this_options=array('key'=>false,'time'=>0);
                }
            }

            if(!empty($this_options) && is_array($this_options)){
                if(!empty($this_options['key'])){
                    $this->api_key=$this_options['key'];
                }
                if(!empty($this_options['time']) && is_integer($this_options['time'])){
                    $this->subsr_last_check_time=intval($this_options['time']);
                    if($this->subsr_last_check_time>intval(time())){
                        $this->subsr_last_check_time=0;
                    }
                }
                if(!empty($this_options['subscribed'])){
                    $this->is_paid_up=true;
                }
                if(!empty($this_options['freemode'])){
                    $this->free_mode=true;
                }

                if(!empty($this_options['reason'])){
                    $this->unpaid_reason=$this_options['reason'];
                }

//            } else {
//                $this->save_data();
            }
            if(class_exists('RasoloAdminMessages')){
                $this->msg_instance=new RasoloAdminMessages;
            };
//            myvar_dump($this_options,'$this_options_3234_1');
//            myvar_dd($this,'$this_options_3234_2');

        }

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
    echo ' ( '.__( 'last check time', self::$TEXTDOMAIN ).' '.date('H:i:s',$this->subsr_last_check_time);
    $next_check_remain=self::$SUBSCRIBE_VRF_DELAY-(intval(time())-$this->subsr_last_check_time);
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
    <span class="rs_admin_descr"><?php
_e( 'Enable this mode if you do not need to use the full functionality of the plugin.<br>After turning on the free mode, there will be no requests to the external server.', self::$TEXTDOMAIN )

    ?></span>

    </td>
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
            $arr_to_save['time']=$this->subsr_last_check_time;
            if($this->is_paid_up){
                $arr_to_save['subscribed']=$this->is_paid_up;
            }
            if($this->unpaid_reason){
                $arr_to_save['reason']=$this->unpaid_reason;
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

            if(!empty($_POST['rs_api_key'])){
                $new_key_val=sanitize_text_field($_POST['rs_api_key']);
                if($new_key_val<>$this->api_key){
                    $need_save=true;
                    $this->api_key=$new_key_val;
                }
            }

            if(empty($_POST['free_mode_switch'])){
//                myvar_dd($_POST,'free_mode_switch_is_empty');
                if($this->free_mode){
                    $need_save=true;
                }
                $this->free_mode=false;
            } else {
//                myvar_dump($_POST,'free_mode_switch_not_empty');
                if(!$this->free_mode){
//                    myvar_dump($_POST,'free_mode_switch_not_empty2');
                    $need_save=true;
                }

                $this->free_mode=true;
//                myvar_dump($this,'free_mode_switch_not_empty3');
            }
            if($need_save){
//                myvar_dump($this,'free_mode_switch_not_empty4');
                $this->save_data('processpost');
                $this->put_adm_message(__('Your changes have been saved',self::$TEXTDOMAIN));
            } else {
//                myvar_dd($this,'free_mode_switch_not_empty6');
                if(!empty($_POST['submit'])){
                    $this->put_adm_message(__('No need to save the same data',self::$TEXTDOMAIN));
                }
            }


//            myvar_dd($this,'er3r3eg_3234');

        } // The end of after_setup_theme

        public function get_key(){
            return $this->api_key;
        }
        private  function check_paid_up(){
// https://wordpress.stackexchange.com/questions/7278/how-can-you-check-if-you-are-in-a-particular-page-in-the-wp-admin-section-for-e
            global $pagenow;
            if('options-general.php'<>$pagenow){
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
            if($this->is_paid_up){
                $time_elapsed=intval(time())-$this->subsr_last_check_time;
                if($time_elapsed<self::$SUBSCRIBE_VRF_DELAY){
//                    myvar_dump('just_returning');
                    return;
                }
            }
//            sleep(3);
//            myvar_dump('letusverify');
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
//            $some_err=curl_error($curl);
            curl_close( $curl );
            if(empty($rs_postres)){
                return;
            }

            try {
                $res_unser=@json_decode($rs_postres,true);
            } catch(Exception $e) {
                return;
            }
            $this->subsr_last_check_time=intval(time());
            if(empty($res_unser['rc'])){
                return;
            }
            if(!is_integer($res_unser['rc'])){
                return;
            }
            if(200<>$res_unser['rc']){
                if(isset(self::$UNREDEEMED_REASONS[$res_unser['rc']])){
                    $this->unpaid_reason=sanitize_text_field(trim(self::$UNREDEEMED_REASONS[$res_unser['rc']]));
                    $this->unpaid_reason=__( $this->unpaid_reason, self::$TEXTDOMAIN );
                }
                return;
            }

            $this->is_paid_up=true;
            $this->unpaid_reason=false;
//            myvar_dump($res_unser,'$res_unser_32t324t3432',1);
//            myvar_dump($this,'$rs_postres_32t324t3432',1);
//            myvar_dd(compact('res_unser','rs_postres','some_err'),'$rs_postres');

        }

        public function is_free_mode(){
            return $this->free_mode;
        }

    }
}
