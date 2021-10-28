jQuery(document).ready(function($)
       {

//$( "#shipping_method li" ).click( function() {
//  console.log( "User clicked on 'foo.'" );
//});

// Put this in a document ready event
/*
$('.payment_method_cod').keyup(function(){
    var this_opc=parseFloat($(this).css('opacity'));
    this_opc=this_opc-0.1;
    this_opc=typeof  this_opc.toString();
    $(this).css('opacity','0.5')
    console.log('this_opc',this_opc);

//    $("div").css({background: "red"})

});
*/

var pm_cod='payment_method_cod';
var pm_bacs='payment_method_bacs';


/*
var paym_click_counter=0;

var ch_bttn=function (method){

    paym_click_counter++;
    console.log('vodka_check_paym_bttn №',paym_click_counter,'method=',method);
    var paym_elms_arr=$('div.'+pm_cod);
    var display_01=paym_elms_arr.css('display');
//    var display_bacs=$('div.payment_method_bacs').first().css('display');
    if('block'==display_01){
       console.log('display_01 - block!',display_01,'display_01',display_01);
        $('label[for='+pm_cod+']').css('opacity','0.9');
        $('label[for='+pm_bacs+']').css('opacity','0.5');
    } else {
        console.log('display_01 - none!',display_01,'display_01',display_01);
        $('label[for='+pm_cod+']').css('opacity','0.5');
        $('label[for='+pm_bacs+']').css('opacity','0.9');

    }
};
*/
//ch_bttn(1);

//$('body').bind( "click", function(e) {
//     e.stopPropagation();
//  console.log( 'e bind',e );
//  ch_bttn(3);
//});


//document.addEventListener("click", ch_bttn(3));

var attr_local='shipping_method_0_local_pickup10';

//var attr_np='shipping_method_0_nova_poshta_shipping5';
var attr_np='shipping_method_0_rasolo_shipping';

//var attr_courier='shipping_method_0_alg_wc_shipping7';
var attr_courier='shipping_method_0_rasolo_courier';

var rasolo_delvcompany_block=$('#rasolo_delvcompany_block').first();
var rasolo_delvcourier_block=$('#rasolo_delvcourier_block').first();
var is_rs_delvcomp_free=rasolo_delvcompany_block.hasClass('rs_free_mode');
//console.log('thatis checkout_tricks, is_rs_delvcomp_free=',is_rs_delvcomp_free);

function adjust_rs_delv_block(isonrequired){
//    console.log('letsadjust',isonrequired,is_rs_delvcomp_free);
    if(isonrequired){
        if(is_rs_delvcomp_free){
            rasolo_delvcompany_block.animate({'min-height':'100px','max-height':'120px'});
        } else {
            rasolo_delvcompany_block.animate({'min-height':'120px','max-height':'220px'});
        }

    } else {
        rasolo_delvcompany_block.animate({'min-height':'0','max-height':'0'});
    }
}

function adjust_shipping_buttons(event)
        {
var non_zero_shipping=$('.non_zero_shipping');
var checkbox_local=$('#'+attr_local);
var checkbox_np=$('#'+attr_np);
var checkbox_courier=$('#'+attr_courier);
var ch_local_checked=checkbox_local.attr('checked');
if('checked'==ch_local_checked){
    $('label[for='+attr_local+']').css('opacity','0.9');
} else {
    $('label[for='+attr_local+']').css('opacity','0.5');
}

var ch_np_checked=checkbox_np.attr('checked');

adjust_rs_delv_block('checked'==ch_np_checked);

var np_office_block=$('#np_office_field');
var np_mandatory=np_office_block.find('span.optional').first();
if('checked'==ch_np_checked){
    np_office_block.show();
    np_mandatory.text('(обязательно)');
    $('label[for='+attr_np+']').css('opacity','0.9');
} else {
    np_office_block.hide();
    np_mandatory.text('(не обязательно)');
    $('label[for='+attr_np+']').css('opacity','0.5');
}

var ch_courier_checked=checkbox_courier.attr('checked');
if('checked'==ch_courier_checked){
    rasolo_delvcourier_block.animate({'max-height':'120px'});
    non_zero_shipping.show();
    $('label[for='+attr_courier+']').css('opacity','0.9');
} else {
    rasolo_delvcourier_block.animate({'max-height':'0'});
    non_zero_shipping.hide();
    $('label[for='+attr_courier+']').css('opacity','0.5');
}
//console.log('ch_local_checked','==}'+ch_local_checked+'{==','ch_np_checked','==}'+ch_np_checked+'{==','ch_courier_checked','==}'+ch_courier_checked+'{==');

       }

adjust_shipping_buttons();

$( document ).ajaxComplete(function( event, xhr, settings ) {

        if(settings.url=='/?wc-ajax=update_order_review'){
            console.log('attr_np_objevent=',event,xhr, settings );
            adjust_rs_delv_block('checked'==$('#'+attr_np).attr('checked'));
        }

        var ajax_bacs_checked=$('#'+pm_bacs).attr('checked');
//        var cod_checked=$('#payment_method_cod').attr('checked');

        if('checked'==ajax_bacs_checked){
//            console.log('0001 Thus bacs checked (Ajax complete!). payment_method_bacs is chanded! bacs_checked='+ajax_bacs_checked+'. ',ajax_bacs_checked);
            $('label[for='+pm_bacs+']').css('opacity','0.9');
            $('label[for='+pm_cod+']').css('opacity','0.5');
        } else {
//            console.log('0002 Thus bacs undefined (Ajax complete!). payment_method_bacs is chanded! bacs_checked='+ajax_bacs_checked+'. ',ajax_bacs_checked);
            $('label[for='+pm_bacs+']').css('opacity','0.5');
            $('label[for='+pm_cod+']').css('opacity','0.9');
        }

        var ajax_cod_checked=$('#'+pm_cod).attr('checked');
//        var cod_checked=$('#payment_method_cod').attr('checked');

        if('checked'==ajax_cod_checked){
//            console.log('0003 Thus cod checked (Ajax complete!). payment_method_bacs is chanded! bacs_checked='+ajax_bacs_checked+'. ',ajax_bacs_checked);
            $('label[for='+pm_cod+']').css('opacity','0.9');
            $('label[for='+pm_bacs+']').css('opacity','0.5');
        } else {
//            console.log('0004 Thus cod undefined (Ajax complete!). payment_method_bacs is chanded! bacs_checked='+ajax_bacs_checked+'. ',ajax_cod_checked);
            $('label[for='+pm_cod+']').css('opacity','0.5');
            $('label[for='+pm_bacs+']').css('opacity','0.9');
        }



    $('#'+pm_bacs).change(function(){
        var bacs_checked=$(this).attr('checked');
//        var cod_checked=$('#payment_method_cod').attr('checked');

        if('checked'==bacs_checked){
//            console.log('0005 Thus bacs checked. payment_method_bacs is chanded! bacs_checked='+bacs_checked+'. pm_bacs=',pm_bacs,'. pm_cod=',pm_cod);
            $('label[for='+pm_bacs+']').css('opacity','0.9');
            $('label[for='+pm_cod+']').css('opacity','0.5');
        } else {
//            console.log('0006 Thus bacs undefined. payment_method_bacs is chanded! bacs_checked='+bacs_checked+'. ',bacs_checked);
            $('label[for='+pm_bacs+']').css('opacity','0.5');
            $('label[for='+pm_cod+']').css('opacity','0.9');
        }

    });

    $('#'+pm_cod).change(function(){
        var bacs_checked=$(this).attr('checked');

        if('checked'==bacs_checked){
//            console.log('0007 Thus cod checked. payment_method_bacs is chanded! bacs_checked='+bacs_checked+'. ',bacs_checked);
            $('label[for='+pm_cod+']').css('opacity','0.5');
            $('label[for='+pm_bacs+']').css('opacity','0.9');
        } else {
            var testpm_cod_label=$('label[for='+pm_cod+']');
            var testpm_bacs_label=$('label[for='+pm_cod+']');
//            console.log('0008 Thus cod unchecked. payment_method_cod is chanded! bacs_checked='+bacs_checked+'. testpm_cod_label=',testpm_cod_label,', testpm_bacs_label=',testpm_bacs_label);
            $('label[for='+pm_cod+']').css('opacity','0.9');
            $('label[for='+pm_bacs+']').css('opacity','0.5');
        }

    });


//    var paym_buttons = document.getElementsByClassName('woocommerce-checkout-review-order');
//    var paym_buttons = document.getElementsByClassName('woocommerce-checkout-payment');
//    var paym_buttons = document.getElementsByClassName('payment_method_cod');
//    console.log('paym_buttons',paym_buttons,'paym_buttons length=',paym_buttons.length);
//    for (var i = 0; i < paym_buttons.length; i++) {
//        paym_buttons[i].addEventListener("click", ch_bttn(1));
//    }





//  ch_bttn(1);


//  console.log( 'settings.url',settings.url,'event',event,'xhr',xhr );
  adjust_shipping_buttons();

  // settings.url tells us what event this is, so we can choose what code we need to run
  if( settings.url.indexOf('update_order_review') > -1 ) {


      var poshta_display=$('#nova_poshta_shipping_fields').css('display');
      var shipping_method_children=$('#shipping_method').children();
      var first_shipping=shipping_method_children.eq(0);
      var second_shipping=shipping_method_children.eq(1);
      var first_shipping_label=first_shipping.children('label').first();
      var second_shipping_label=second_shipping.children('label').first();
      if('block'==poshta_display){
//          console.log('first_shipping_label',first_shipping_label,'first_shipping',first_shipping,'poshta_display',poshta_display,'update_order_review', settings.url );
//          first_shipping_label.css('color','#000').css('opacity','0.9');
//          second_shipping_label.css('color','grey').css('opacity','0.5');
      } else {
//          console.log('second_shipping_label',second_shipping_label,'second_shipping',second_shipping,'poshta_display',poshta_display,'update_order_review', settings.url );
//          first_shipping_label.css('color','grey').css('opacity','0.5');
//          second_shipping_label.css('color','#000').css('opacity','0.9');
      }

    // update order form
//    doTotalUpdates();

  } else if( settings.url.indexOf('wc-ajax=checkout') > -1 ) {
    // Add messages after checkout here
//        doAfterCheckout();
  }

});

$( '.wc-ukr-shipping-checkbox' ).mouseup( function() {
    var shipping_info_display=jQuery('#nova-poshta-shipping-info').css('display');
    var custom_address_display=jQuery('#np_custom_address_block').css('display');
    if(shipping_info_display=='none'){
        $(this).css('opacity','0.5');
    } else {
        $(this).css('opacity','0.9');
    }
    console.log('custom_address_display',custom_address_display);
});


//$('label').on( "click", function() {
//    console.log('wc_payment_method6',this);
//});
//$("#filter").keyup(function(){
//});
       });