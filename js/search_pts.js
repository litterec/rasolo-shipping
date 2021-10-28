jQuery(document).ready(function($)
       {

function rs_implode( glue, pieces ) {
    return ( ( pieces instanceof Array ) ? pieces.join ( glue ) : pieces );
}

if(typeof rs_api_key=='undefined'){
    return;
}
if(typeof dlv_comps=='undefined'){
    return;
}
if(typeof liveindroute=='undefined'){
    return;
}
if(typeof local_err2=='undefined'){
    return;
}
if(typeof rslt_title=='undefined'){
    return;
}
if(typeof id_trnsl=='undefined'){
    return;
}
if(typeof name_trnsl=='undefined'){
    return;
}
if(typeof phone_trnsl=='undefined'){
    return;
}
if(typeof addr_trnsl=='undefined'){
    return;
}
if(typeof select_city=='undefined'){
    return;
}
if(typeof city_selected=='undefined'){
    return;
}
if(typeof select_point=='undefined'){
    return;
}
if(typeof point_selected=='undefined'){
    return;
}
if(typeof edit_trnsl=='undefined'){
    return;
}
var slctd_pts=$('#slctd_pts');
var slctd_comp=$('#slctd_comp');
var cty_block_h6=$('#cty_src_block').children('h6').first();
var pts_src_block=$('#pts_src_block');
var pts_block_h6=pts_src_block.children('h6').first();
var point_full_data=$('#point_full_data');
var is_cty_srch=true;
//var ru_lng='ru'==$('html').first().attr('lang')
var ru_lng=true;
var input_str_val=null;
var loader_html='<div class="live_ajax_loader"></div>';
var city_rslt=$('#city_rslt');
var cty_select=$('#cty_select');
var pts_select=$('#pts_select');
var pts_ctxt=$('#pts_ctxt');
pts_ctxt.hide();
var pts_rslt=$('#pts_rslt');

var list_filter=$('.list_filter');

var comp_select=$('#crnt_comp').first();
//console.log('list_filter=',list_filter);
var reg_select=$('#crnt_reg').first();
var live_srch=$('.live_search');
var city_ctxt=$('#city_ctxt').first();
var cty_srch=$('#cty_search').first();
var pts_srch=$('#pts_search').first();
var cty_selected=$('#cty_selected').first();
var cty_input_rslt=$('#cty_input_rslt').first();
var pts_selected=$('#pts_selected').first();
var pts_title_input_rslt=$('#pts_title_input_rslt').first();
//console.log('pts_selected=',pts_selected);
var cty_output=cty_srch.closest('div.list_control').find('.srch_output').first();
var pts_output=pts_srch.closest('div.list_control').find('.srch_output').first();

var crnt_cty_num=false;
var crnt_cty_v='';
var crnt_cty_txt='';
var crnt_cty_st=''; // The city short title

var found_cities=0;
var found_points=0;

var crnt_pts_num=false;
var crnt_pts_v='';
var crnt_pts_txt='';
var crnt_pts_st=''; // The point short title
var crnt_pts_ph='';
var crnt_pts_ad='';
var crnt_pts_ds='';
var crnt_serv_title='';

var abramovka=$('a.abramovka');
var rasolo_delvcompany_block=$('#rasolo_delvcompany_block');

function item_increase(prev_value,max_value){
//    console.log('item_increase prev_value=',prev_value,'max_value=',max_value);
    if(false===prev_value){
        return 0;
    }
    if(false===max_value || max_value<=2){
        return false;
    }
    if(prev_value<max_value-2){
        return prev_value+1;
    }
    return max_value-1;
} // The end of item_increase

function item_decrease(prev_value){
    if(false===prev_value){
        return false;
    }
    if(prev_value<=0){
        return prev_value;
    }
    return prev_value-1;
} // The end of item_increase

function adjust_search_mode_msg(){


    live_srch.each(function(){
        var nxt=$(this).closest('span').first().next();
        var any=nxt.hasClass('abramovka_any');
//        console.log('Let us adjust adjust_search_mode_msg2!',ru_lng,any,nxt,this);
        var ttl;
        if(ru_lng){
            if(any){
                ttl=$(this).attr('data-ruany');
            } else {
                ttl=$(this).attr('data-rubeg');
            }

        } else {
            if(any){
                ttl=$(this).attr('data-enany');
            } else {
                ttl=$(this).attr('data-enbeg');
            }

        }
        nxt.attr('title',ttl);
    });
}

adjust_search_mode_msg();

abramovka.on('click',function(evnt){
    input_str_val=null;
    evnt.preventDefault();
    if(is_cty_srch){
        cty_srch.trigger('keyup');
    } else {
        pts_srch.trigger('keyup');
    }

    $(this).toggleClass('abramovka_any').toggleClass('abramovka_beg');
    adjust_search_mode_msg();
});

function adjust_src_statuses(){
    is_cty_srch=true;
    var reg_select_val=reg_select.val()
//        var comp_select_val=comp_select.val()
    if('0'==reg_select_val  ){
        live_srch.attr('disabled','disabled');
        abramovka.addClass('hold_anchor');
//        console.log( 'delv_filter000000=',comp_select.val(),reg_select.val() );
    } else {
        city_ctxt.show();
        cty_srch.removeAttr('disabled');
        abramovka.removeClass('hold_anchor');
//        console.log( 'delv_filter=111111',comp_select.val(),reg_select.val() );
    }

}

adjust_src_statuses();

list_filter.on('change', function() {
//    console.log('list_filter is changed2');
    is_cty_srch=true;
    reset_pts();
    cease_selected_cty();
    cease_selected_pts();
//    list_filter.css('background-color','#add8e6');
    console.log('Letus show city_ctxt',city_ctxt);
    city_ctxt.show();
    cty_select.remove();
    cty_srch.val('');
    cty_output.html('');
});

$('.delv_filter').on('change', adjust_src_statuses);

var timeout = null;

function change_select_css(num_of_crnt){
//    console.log('change_select_css num_of_crnt=',num_of_crnt);
    if(false===num_of_crnt){
        return;
    }
    var prnt_obj;
    if(is_cty_srch){
        prnt_obj=$('#cty_select');
    } else {
        prnt_obj=$('#pts_select');
    }
//    console.log('Let us paint',num_of_crnt,prnt_obj);
    prnt_obj.children().each(function(indx){

        if(num_of_crnt+1==indx){
            if(is_cty_srch){
                crnt_cty_v=$(this).attr('data-val');
                crnt_cty_txt=$(this).text();
                crnt_cty_st=$(this).attr('data-st');
            } else {
                crnt_pts_v=$(this).attr('data-val');

                crnt_pts_txt=$(this).text();
                if('undefined'==typeof crnt_pts_txt){
                    crnt_pts_txt='Unknown';
                }

                crnt_pts_st=$(this).attr('data-st');
                if('undefined'==typeof crnt_pts_st){
                    crnt_pts_st='';
                }

                crnt_pts_ph=$(this).attr('data-phone');
                if('undefined'==typeof crnt_pts_ph){
                    crnt_pts_ph='';
                }

                crnt_pts_ad=$(this).attr('data-ad');
                if('undefined'==typeof crnt_pts_ad){
                    crnt_pts_ad='';
                }

                crnt_pts_ds=$(this).attr('data-ds');
                if('undefined'==typeof crnt_pts_ds){
                    crnt_pts_ds='';
                }

            }
            $(this).css('background-color','#606060');
            $(this).css('color','#acacac');
//            console.log('num_of_crnt==indx prnt_obj.children indx=',indx,'num_of_crnt=',num_of_crnt,'$(this)=',$(this));
        } else {
            $(this).css('background-color','#fff');
            $(this).css('color','#000');
//            console.log('num_of_crnt!=indx prnt_obj.children indx=',indx,'num_of_crnt=',num_of_crnt,'$(this)=',$(this));
        }
    });

} // The end of change_select_css

function keyup_handler(event){
    event.preventDefault();
//    console.log('Key pressed is',event.keyCode,'is_cty_srch=',is_cty_srch);
    if(40==event.keyCode){
        if(is_cty_srch){
            cty_srch.blur();
            change_select_css(crnt_cty_num);
        } else {
            pts_srch.blur();
            change_select_css(crnt_pts_num,found_points);
        }


//            cty_select=$('#cty_select');
//            cty_select.focus();
//        var el_cty_sl=document.getElementById('cty_select')
//        if(null !==el_cty_sl){
//            el_cty_sl.focus();
//        }


        $('#cty_output').children().first().focus();
    } else if (38==event.keyCode){
        if(is_cty_srch){
            cty_srch.blur();
            change_select_css(crnt_cty_num);
        } else {
            pts_srch.blur();
            change_select_css(crnt_pts_num);
        }

    }
//    console.log('event_34_23=',event.keyCode);

    var that_kh = $(this);
    var that_pure = this;

    clearTimeout(timeout);

    timeout = setTimeout(function() {

        if(input_str_val===null || input_str_val!=that_pure.value){
            input_str_val=that_pure.value;
// 'ZGRlMTYyY2MzYzQ0M2JmNWE3M2U2OGM2MmU5Y2YyYzZjNDNlNGJiODZkODI0Mzcz'

            var  obj_to_ajax={
                    'cty_bait':that_pure.value,
                    'serv_domain':window.location.hostname,
                    'serv_passw':rs_api_key,
                    'crnt_comp':comp_select.val(),
                    'crnt_reg':reg_select.val()
                };
            if(!is_cty_srch){

                obj_to_ajax.is_any_place=pts_srch.closest('span').next().hasClass('abramovka_any');
                obj_to_ajax.crnt_cty=crnt_cty_v;
            } else {
                obj_to_ajax.is_any_place=cty_srch.closest('span').next().hasClass('abramovka_any');

            }

            that_kh.off('keyup paste'); // remove handler

            console.log('Beforeajax obj_to_ajax=',obj_to_ajax,'crnt_cty_v=',crnt_cty_v,'cty_output=',cty_output,'pts_output=',pts_output );
            var token=$('input[name="_token"]').val();
            if(is_cty_srch){
                crnt_cty_num=false;
                cty_output.html(loader_html);
            } else {
                crnt_pts_num=false;
                pts_output.html(loader_html);
            }

            console.log('Letusdo Ajaxfunc!!',Date.now(),liveindroute,obj_to_ajax);
            var geturl;
            rasolo_delvcompany_block.css('overflow','visible');
            geturl = $.ajax({
                type: "post",
                url: liveindroute,
                data: {
                    action: 'get_points',
                    src_keys: obj_to_ajax
                },
                headers: {
                    'X-CSRF-Token': token
                },

//                    beforeSend: function() {
//                            $("#loading").fadeIn('slow');
//                    },
                success: function(data) {
//                    console.log('ajax success, data=',data);
                    that_kh.on('keyup paste',keyup_handler); // restore handler

                    try {
                        var parsed=JSON.parse(data)
                    } catch (e) {
                        parsed={status:'error',
                            message:'Error has been encountered while json decoding procedure. Error: '+e+'. Data: '+data+'.',
                            data:'Empty data gag',
                            rc:999};
                    }
                    console.log('parsed=',parsed,'date=',Date.now());
//                            $("#loading").fadeOut('slow');
                    if(18994==parsed.rc){
                        var checkout_field=$('#rasolo_delivery_checkout_field');
                        checkout_field.fadeOut(1500,function(){
                            var emerg_delv=$('#rs_emerg_delv');
                            emerg_delv.fadeIn(2000,function(){

                                $('#rasolo_delivery_block').animate({'min-height':'100px'});
                                emerg_delv.children('p.failure').animate({height:'0',padding:'0',margin:'0'},function(){
                                    $(this).remove();
                                });
                            });
                            pts_title_input_rslt.remove();
                            var new_html='<label for="pts_title_input_rslt">';
                            new_html+=type_pts_info;
                            new_html+='</label>';
                            new_html+='<input ';
                            new_html+='placeholder="'+type_pts_ph+'"';
                            new_html+=' type="text" id="pts_title_input_rslt" name="pts_title_input_rslt" value=""/>';
                            new_html+='<input type="hidden" name="rs_free_mode" value="1" />';
                            emerg_delv.append(new_html);
//                            $('#pts_title_input_rslt')
                            checkout_field.remove();

                        });
                    } else if(200==parsed.rc){
//                        rasolo_delvcompany_block.animate({'max-height':'1340px'},'slow');
                        console.log('rasolo_delvcompany_block_css333');
                        if('string'==typeof parsed.message && 'object'==typeof parsed.data){

                            if(!Array.isArray(parsed.data)){
                                var bad_arr_html='<div class="empty_src_res">'+(ru_lng?'Ошибка в данных':'Data error')+'</div>';
                                if(is_cty_srch){
                                    cty_output.html(bad_arr_html);
                                } else {
                                    pts_output.html(bad_arr_html);
                                }
                                return;
                            }

                            var new_cty_html='';

                            var empty_array_html='<div class="empty_src_res">'+(ru_lng?'Нет данных':'No data')+'</div>';
                            if(parsed.data.length<1){
                                if(is_cty_srch){
                                    found_cities=0;
                                    crnt_cty_num=false;
                                    cty_output.html(empty_array_html);
                                } else {
                                    found_points=0;
                                    crnt_pts_num=false;
                                    pts_output.html(empty_array_html);
                                }
                                return;
                            }
                            if(is_cty_srch){
                                found_cities=parsed.data.length;
                            } else {
                                found_points=parsed.data.length;
//                                max_pts_num=parsed.data.length;
                            }


                            new_cty_html+='<div id="'+(is_cty_srch?'cty_select':'pts_select')+'" class="list_filter ajax_select">';

                            var select_msg;
                            if(is_cty_srch){
                                select_msg=(ru_lng?'Выберите город:':'Select city');
                            } else {
                                select_msg=(ru_lng?'Выберите точку:':'Select point');
                            }
                            new_cty_html+='<h6 data-val="0">'+select_msg+'</h6>';
//                                    new_cty_html+='<select id="cty_select" size="10" class="form-control list_filter delv_filter ajax_select">';
//                                    new_cty_html+='<option selected value="0">'+(ru_lng?'Выберите город:':'Select city')+'</option>';

                            parsed.data.forEach(function(item, i, arr) {
                                if(i>(is_cty_srch?found_cities:found_points)){
                                    return;
                                }
                                var phone_wedge='';
//                                console.log('parsed_1_item is ',item);
                                if(!is_cty_srch && null!=item.phone){
                                    phone_wedge='data-phone="'+item.phone+'" ';
                                }
                                var ad_wedge='';
                                if(!is_cty_srch && null!=item.address){
                                    ad_wedge='data-ad="'+item.address+'" ';
                                }


                                var ds_wedge='';
                                if(!is_cty_srch && comp_select.val()=='0' && null!=item.delvserv){
                                    var title_wedge='';
                                    if(typeof dlv_comps[item.delvserv]!='undefined'){
                                        title_wedge='title="'+dlv_comps[item.delvserv]+'" ';
                                    }

                                    ds_wedge=title_wedge+'class="dlvserv '+item.delvserv+'" data-ds="'+item.delvserv+'" ';
                                }
                                new_cty_html+='<div '+ds_wedge+phone_wedge+ad_wedge+'data-st="'+item.st+'" data-val="'+item.id+'">'+item.title+'</div>';
//                                        new_cty_html+='<option value="'+item.id+'">'+item.title+'</option>';
                            });

                            new_cty_html+='</div>';
//                            new_cty_html+='<div class="debug_livesrc">'+parsed.message+'</div>';

//                            console.log('parsed.data.length===222',parsed.data.length,'new_cty_html=',new_cty_html,cty_output);

                            if(is_cty_srch){
                                cty_output.html(new_cty_html);
                                cty_select=$('#cty_select');
                                var cty_sl_children=cty_select.children();
                                cty_sl_children.on('click', function() {
                                    var chosen_idx=$(this).index();
                                    if(chosen_idx<1){
                                        return;
                                    }
                                    crnt_cty_num=chosen_idx-1;
                                    var dataval=$(this).attr('data-val');
                                    if(crnt_cty_num>found_cities-1){
                                        crnt_cty_num=found_cities-1;
                                    }
//                                        $(this).children().first().text(ru_lng?'Еще города&hellip;':'Other cities&hellip;');
//                                        console.log('#cty_select is changed',this.value);
                                    crnt_cty_txt=$(this).text();
                                    crnt_cty_st=$(this).attr('data-st');
                                    console.log('#cty_select is clicked_456 dataval=',dataval,'crnt_cty_num=',crnt_cty_num,'crnt_cty_st=',crnt_cty_st,'found_cities=',found_cities,'$(this)=',$(this));
                                    change_select_css(crnt_cty_num);
                                    involve_selected_cty();
                                });
                            } else {
                                pts_output.html(new_cty_html);
                                pts_select=$('#pts_select');
                                var pts_sl_children=pts_select.children();
                                pts_sl_children.on('click', function() {
                                    var chosen_idx=$(this).index();
                                    if(chosen_idx<1){
                                        return;
                                    }
                                    crnt_pts_num=chosen_idx-1;

                                    if(crnt_pts_num>found_points-1){
                                        crnt_pts_num=found_points-1;
                                    }
//                                    var dataval2=$(this).attr('data-val');
//                                    console.log('#pts_select is clicked_222',dataval2,crnt_pts_num);
                                    crnt_pts_txt=$(this).text();
                                    crnt_pts_st=$(this).attr('data-st');
                                    crnt_pts_ph=$(this).attr('data-phone');
                                    crnt_pts_ad=$(this).attr('data-ad');
                                    crnt_pts_ds=$(this).attr('data-ds');
                                    crnt_serv_title=$(this).attr('title');
                                    change_select_css(crnt_pts_num);
                                    involve_selected_pts();
                                });

                            }
//                                    cty_select.on('click', function() {
//                                        console.log('#cty_select is clicked',this.value);
//                                    });


//                                    var event = new MouseEvent('mousedown');
//                                    document.getElementById('cty_select').dispatchEvent(event);


                        } else {
                            var local_error=local_err2+" Some error has been encountered ( Type of rc="+typeof parsed.rc+", msg="+typeof parsed.message+", data="+typeof parsed.data+")";
                            console.log(local_err2);
                            if(is_cty_srch){
                                cty_output.text(local_error);
                            } else {
                                pts_output.text(local_error);
                            }
                        }
                    } else {
                        var ret_code=parsed.rc.toString();
                        var err_txt;
                        if(793==ret_code || 804==ret_code){
                            err_txt=local_err3+' '+parsed.message;
                        } else {
                            err_txt=local_err2+" wrong responce type. ( Type of rc="+typeof parsed.rc+",  rc="+parsed.rc.toString()+",  msg="+parsed.message+", data="+typeof parsed.data+")";
                        }

                        console.log(err_txt);
                        if(is_cty_srch){
                            cty_output.text(err_txt);
                        } else {
                            pts_output.text(err_txt);
                        }
                    }

        //        $('#out').text('Val: ' + val);
        //            $(".search-appear").append(data);
                },
                error: function(returnval,textStatus, errorThrown){
                    console.log('Ajax error:','==}'+geturl.getAllResponseHeaders()+'{==',returnval);
                }

            });

        } else {

//            console.log('Letus wait for string changing',that_pure.value,Date.now());
        }


    }, 200);

} // The end of keyup_handler

//$('body').on('keypress',function(){
//    console.log('keypressed111')
//});

function involve_selected_cty(){
    if(false===crnt_cty_num){
        return;
    }
    cty_output.text('');
    city_ctxt.hide();
    cty_selected.text(crnt_cty_st).attr('title',crnt_cty_txt).show();
//    cty_selected.show();
    pts_ctxt.show();
    pts_srch.removeAttr('disabled');
    is_cty_srch=false;
    cty_srch.off('keyup paste')
    pts_srch.on('keyup paste',keyup_handler);
    pts_src_block.show();
    cty_block_h6.text(city_selected);
    city_ctxt.hide();
    city_rslt.show()
    cty_input_rslt.attr('value',crnt_cty_txt);
}

function involve_selected_pts(){
    if(false===crnt_pts_num){
        return;
    }
    pts_output.text('');
    pts_ctxt.hide();
    var new_title_arr=[];
    if('undefined'!=typeof crnt_pts_txt && crnt_pts_txt.length>3){
        new_title_arr.push(crnt_pts_txt);
    }

    if('undefined'!=typeof crnt_pts_ph && crnt_pts_ph.length>3){
        new_title_arr.push(crnt_pts_ph);
    }

    if('undefined'!=typeof crnt_pts_ad && crnt_pts_ad.length>3){
        new_title_arr.push(crnt_pts_ad);
    }

    if('undefined'!=typeof crnt_serv_title && crnt_serv_title.length>3){
        new_title_arr.push(crnt_serv_title);
    }

    var new_title;
    if(0==new_title_arr.length){
        new_title='Unknown data';
    } else {
        new_title=rs_implode(' * ',new_title_arr);
    }
    console.log('new_title_arr=',new_title_arr);
    pts_selected.text(crnt_pts_st).attr('title',new_title).addClass(crnt_pts_ds).show();
    var may_be_val=comp_select.val();
    if('string'==typeof crnt_pts_ds && crnt_pts_ds.length>0){
        pts_selected.addClass(crnt_pts_ds);
        slctd_comp.val(crnt_pts_ds);
        // crnt_pts_txt
    } else if('string'==typeof may_be_val && may_be_val.length>0){
        pts_selected.addClass(may_be_val);
        slctd_comp.val(may_be_val);
//    } else {
//        pts_selected.addClass('np');
    }
    slctd_pts.val(crnt_pts_txt);
//    console.log('Let us show selected pts=',pts_selected);
    pts_selected.show();
    pts_rslt.show();
//    pts_srch.removeAttr('disabled');
//    console.log('involve_selected_pts__',crnt_pts_st,crnt_pts_txt,crnt_pts_ph);
    var final_html='<h3>'+rslt_title+'</h3>';
    final_html+='<div class="row"><div class="col-md-6">'+id_trnsl+'</div>';
    final_html+='<div class="col-md-6">'+crnt_pts_v+'</div></div>';

    if('undefined'!=typeof crnt_pts_ds){
        final_html+='<div class="row"><div class="col-md-6">'+dlvcomp_trnsl+'</div>';

        if(typeof dlv_comps[crnt_pts_ds]!='undefined'){
            crnt_pts_ds=dlv_comps[crnt_pts_ds];
        }
        final_html+='<div class="col-md-6">'+crnt_pts_ds+'</div></div>';
    }

    final_html+='<div class="row"><div class="col-md-6">'+name_trnsl+'</div>';
    final_html+='<div class="col-md-6">'+crnt_pts_txt+'</div></div>';
    if('undefined'!=typeof crnt_pts_ph){
        final_html+='<div class="row"><div class="col-md-6">'+phone_trnsl+'</div>';
        final_html+='<div class="col-md-6">'+crnt_pts_ph+'</div></div>';
    }
    if('undefined'!=typeof crnt_pts_ad){
        final_html+='<div class="row"><div class="col-md-6">'+addr_trnsl+'</div>';
        final_html+='<div class="col-md-6">'+crnt_pts_ad+'</div></div>';
    }
    final_html+='<div class="row">';
    final_html+='<div class="col text-center">';
    final_html+='<a class="marg-top20 btn btn-small btn-info" href="/delpoints/'+crnt_pts_v+'/edit">'+edit_trnsl+'</a>';
    final_html+='</div>';
    final_html+='</div>';
    point_full_data.html(final_html);
    pts_block_h6.text(point_selected);
    pts_title_input_rslt.attr('value',crnt_pts_txt);

    console.log('Let us put to hidden!!! crnt_serv_title=',crnt_serv_title);
    $('#pts_serv_input_rslt').first().attr('value',crnt_serv_title);
    $('#pts_addr_input_rslt').first().attr('value',crnt_pts_ad);
    $('#pts_phone_input_rslt').first().attr('value',crnt_pts_ph);
}

function cease_selected_cty(){
    city_rslt.hide();
    pts_rslt.hide();
    pts_src_block.hide();
    city_ctxt.show();
    cty_srch.val('');
    cty_output.text('');
    cty_selected.removeAttr('title').text('').hide();
    cty_block_h6.text(select_city);
    cty_input_rslt.attr('value','');
}

function cease_selected_pts(){
//    console.log('pts_selected=',pts_selected);
    pts_rslt.hide();
    pts_selected.hide();
    pts_srch.attr('disabled',true);
//    pts_ctxt.show();
    pts_srch.val('');
    pts_output.text('');
    cty_selected.removeAttr('title').text('').hide();

    crnt_pts_num=false;
    crnt_pts_v='';
    crnt_pts_txt='';
    crnt_pts_st='';
    crnt_pts_ph='';
    point_full_data.text('');
    pts_block_h6.text(select_point);
    pts_title_input_rslt.attr('value','');
}

function reset_pts(){
    slctd_pts.val('');
    slctd_comp.val('');
    pts_selected.removeAttr('class').attr('class','data_selected');
    is_cty_srch=true;
    pts_srch.attr('disabled',true);
    pts_srch.val('');
    pts_srch.off('keyup paste')
    cty_srch.on('keyup paste',keyup_handler);
    $('.srch_output').text('');
}

function reset_all(){
    is_cty_srch=true;
    comp_select.val(0);
    reg_select.val(0);
    cty_srch.attr('disabled',true);
    change_select_color(list_filter);
    cease_selected_cty();
    cease_selected_pts();
    reset_pts();
    input_str_val=null;
}

document.onkeydown = function(e) {
    console.log('e.which=',e.which,'is_cty_srch!=',is_cty_srch);
    switch(e.which) {
//        case 37: // left
//        console.log('left keypressed111')
//        break;

        case 27: // Enter
//        console.log('let us resete.which=',e.which);
        reset_all();
        break;

        case 13: // Enter
        if(is_cty_srch){
            involve_selected_cty();
            console.log('Enter keypressed111',crnt_cty_num,crnt_cty_v,crnt_cty_txt);
        } else {
            involve_selected_pts();
            console.log('Enter pts keypressed222',crnt_pts_num,crnt_pts_v,crnt_pts_txt);
        }


        break;

        case 38: // up
        if(is_cty_srch){
            crnt_cty_num=item_decrease(crnt_cty_num);
            change_select_css(crnt_cty_num);
            console.log('up keypressed333',crnt_cty_num,crnt_cty_v,crnt_cty_txt);
        } else {
            crnt_pts_num=item_decrease(crnt_pts_num);
            change_select_css(crnt_pts_num);
            console.log('up keypressed444',crnt_pts_num,crnt_pts_v,crnt_pts_txt);
        }

        break;

//        case 39: // right
//        console.log('right keypressed111')
//        break;

        case 40: // down
        if(is_cty_srch){
            console.log('down keypressed before increasing! crnt_cty_num=',crnt_cty_num,'crnt_cty_v=',crnt_cty_v,'crnt_cty_txt=',crnt_cty_txt,'found_cities=',found_cities);
            crnt_cty_num=item_increase(crnt_cty_num,found_cities);
            change_select_css(crnt_cty_num);
            console.log('down keypressed after increasing! crnt_cty_num=',crnt_cty_num,'crnt_cty_v=',crnt_cty_v,'crnt_cty_txt=',crnt_cty_txt);

        } else {
            crnt_pts_num=item_increase(crnt_pts_num,found_points);

            change_select_css(crnt_pts_num);
            console.log('down keypressed111',crnt_pts_num,crnt_pts_v,crnt_pts_txt);
        }


        break;

        default: return; // exit this handler for other keys
    }
    e.preventDefault(); // prevent the default action (scroll / move caret)
};

cty_srch.on('keyup paste',keyup_handler);
cty_srch.on({
    keydown: function(e) {
        if (e.which === 32)
            return false;
        },
    change: function() {
        this.value = this.value.replace(/\s/g, "");
    }
});

       });
