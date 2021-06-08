/*function resetParentCounters(){
 window.parent.resetCounters();
 window.parent.parent.scrollTo(0,0);
 }*/
var myVar;
var phonefee_edited = -1;
function focusswipe() {
    
    setTimeout(function () {
        $('#strackdata').focus();
    }, 100);

}

function swipeagain() {
    $('#strackdata').val('');stopswipe();
     
     $('#swipeagainmessage').html('<div class="alert alert-info alert-dismissible" id="swipeagainmessage1"  hidden><a href="#" class="close" data-dismiss="alert" aria-label="close" onclick="focusswipe();">&times;</a><strong>Please swipe card again!</strong></div>');
     $("#swipeagainmessage1").fadeTo(4000, 500).slideUp(500, function(){
               $("#swipeagainmessage1").slideUp(500);
                });
    $('#strackdata').focus();
    myVar = setInterval(checkswipedata, 1000);
}

$("#checkbox4").click(function () {
    $("#xsaprofile").show();
    $(".pprofile").attr('checked', false);
    $('#checkbox5').attr('checked', false);
    $('#checkbox6').attr('checked', false);
});

$("#checkbox5").click(function () {

    $(".pprofile").attr('checked', false);
    $('#checkbox4').attr('checked', false);
    if (novault == 1) {
        $("#xsaprofile").hide();
    }
    $('#checkbox6').attr('checked', false);
    if (novault == 1) {
        $("#xsaprofile").hide();
    }
});
//for swipe save as payment method checkbox :AJ - Accepted Payment
$("#checkbox6").click(function () {

    $(".pprofile").attr('checked', false);
    $('#checkbox4').attr('checked', false);
    if (novault == 1) {
        $("#xsaprofile").hide();
    }
    $('#checkbox5').attr('checked', false);
    if (novault == 1) {
        $("#xsaprofile").hide();
    }
});

function GoBack() {
    //resetParentCounters();
    $("#myModal_loading").modal();
    parent.location.reload();
}

function cleanData() {

    cleanacc();
    cleaninv();
    xatoken = "";
}

function cleanacc() {
    $("#xotherinfo").hide();
    $("#xsearch_usr").show();
    $("#xnext_step").hide();
}

function cleaninv() {
    $("#xsearch_inv").show();
    $("#xnext_step_inv").hide();
    $("#xotherinfoinv").hide();
}


function vpaydata() {
    //resetParentCounters();
    isgood = true;
    if ($("#checkbox5").is(":checked")) {
        //cc
        if (!validate_zip1()) {
            isgood = false;
        }
        if (!validate_expdate()) {
            isgood = false;
        }
        if (!validate_ccname()) {
            isgood = false;
        }
        if (!validate_ccnumber()) {
            isgood = false;
        }
        return isgood;
    }
    if ($("#checkbox4").is(":checked")) {
        //ec
        if (!validate_ecname()) {
            isgood = false;
        }
        if (!validate_aba()) {
            isgood = false;
        }
        if (!validate_bank()) {
            isgood = false;
        }
        return isgood;
    }
    return isgood;
}

//for saprate track2 data from swipe string :AJ - Accepted Payment
function gettrack2(withSentinal, trackData) {
//    debugger;
    var track2Start = -1;
    var track2End = -1;

    track2Start = trackData.indexOf(";");

    if ((!hasTrack2(trackData)) || (track2Start < 0))
        return null;

    track2End = trackData.indexOf("?", track2Start);

    if (track2Start >= track2End)
        return trackData;

    var track = "";

    if (withSentinal) {
        track = trackData.substring(track2Start, track2End + 1);
    } else {
        track = trackData.substring(track2Start + 1, track2End);
    }

    return track;

}

//for check track2 data is available in swipe string :AJ - Accepted Payment
function hasTrack2(trackData) {


    var i = trackData.indexOf(";");
    return (i >= 0 && trackData.indexOf("?", i + 1) >= 0);

}

//for fetch credit card number from swipe string :AJ - Accepted Payment
function getcc(trackData) {


    var ccStart = -1;
    var ccEnd = -1;

    if ((!hasTrack2(trackData)))
        return "";

    if (hasTrack2(trackData)) {
        ccStart = (trackData.indexOf(";") + 1);
        ccEnd = (trackData.indexOf("=", ccStart));
    }

    if ((ccStart > 0) && (ccEnd > 0) && (ccEnd > ccStart))
        return trackData.substring(ccStart, ccEnd);
    else
        return null;

}

//for fetch exp date from swipe string :AJ - Accepted Payment
function getExpYYMMTrack2(trackData) {

    var start = -1;
    var end = -1;

    if (!hasTrack2(trackData))
        return null;

    start = (trackData.indexOf("=") + 1);
    end = start + 4;

    if ((start > 0) && (end > 0) && (end > start)) {
        var ym = trackData.substring(start, end);
        var rym = ym.substring(2, 4) + ym.substring(0, 2);
        return rym;
    } else
        return null;

}

//for fetch name from swipe string :AJ - Accepted Payment
function getName(trackData) {


    var sbName;
    var sFirstName = null;
    var sLastName = null;
    var start = -1;
    var end = -1;


    if (!hasTrack1(trackData))
        return null;

    start = (trackData.indexOf("^") + 1);
    if (start > 0)
        end = trackData.indexOf("/", start);
    if (end > 0)
        sLastName = trackData.substring(start, end);
    if (end > 0)
        start = end + 1;
    end = trackData.indexOf("^", start);
    if (end > 0) {
        sFirstName = trackData.substring(start, end);
        if (sFirstName != null) {
            sFirstName = sFirstName.trim();
            sbName = sFirstName;
        }
    }
    if (sLastName != null)
        sbName = sbName + " " + sLastName;
    return sbName;


}

//for check track1 data is present in swipe string :AJ - Accepted Payment
function hasTrack1(trackData) {

    var i = trackData.indexOf("%");
    var j = (i >= 0) ? trackData.indexOf("^") : -1;
    var k = (j > 0) ? trackData.indexOf("^", j + 1) : -1;
    var l = (k > 0) ? trackData.indexOf("?") : -1;
    return (i >= 0 && l > k && k > j && j > i);


}

function xegostep4(opc) {
    
    if(!validDate){
        swal({
                title: "Invalid date!",
                text: "An invalid date is selected, please choose another date"
            });
            return false;
    }

    if($("#inlineRadio1").is(':checked') == true && isrecurring){

        var d = new Date();
        var currentday = d.getFullYear() + "/" + (d.getMonth()+1) + "/" + d.getDate();
        var selectdate = $("#drpdatep").datepicker( "getDate" );
        var drpstardate = selectdate.getFullYear() + "/" + (selectdate.getMonth()+1) + "/" + selectdate.getDate();

        if (drpstardate == currentday) {
            swal({
                title: "Attention",
                text: "Start Date should be a day in the future"
            });
            return false;
        }
    }

    if(!validate_phone_fee()){
        swal({
            title: "Invalid Phone Fee!",
            text: "Invalid Phone Fee, will be numeric"
        });
        return false;
    }
    
    if (validate_memo1()) {
        //resetParentCounters();
        if (opc == 'inv') {
            xamount = $("#xinvoice_amount").val();
            if (isNaN(xamount)) {
                xamount = 0;
            }
        }
        
        if (!$("#inlineRadio1").is(':checked') || isrecurring != 1) {
            var invalid_customField = false;
            for (var i = 0; tmp_cfield = document.getElementById("customfield_" + i); i++) {
                tmp_cfield = document.getElementById("customfield_" + i);
                if (tmp_cfield.dataset.customfield === "N") {
                    if (!validate_customField_numeric("customfield_" + i)) {
                        invalid_customField = true;
                    }
                } else if (tmp_cfield.dataset.customfield === "A") {
                    if (!validate_customField_alpha("customfield_" + i)) {
                        invalid_customField = true;
                    }
                }
            }
            if (invalid_customField) {
                swal({
                    title: "Attention",
                    text: "Please Custom Field are Required"
                });
                return false;
            }
        }


        if (profile == 0) {
            if (!$("#checkbox4").is(":checked")) {
                if (!$("#checkbox5").is(":checked")) {
                    if (!$("#checkbox6").is(":checked")) {
                        swal({
                            title: "Attention",
                            text: "Please select a Payment Method"

                        });

                        return false;
                    } else {
                        //for showing model for approve payment swipe string :AJ - Accepted Payment
                        if (!validate_trackdata()) {
                            return false;
                        }
                        var str_swipe = $("#strackdata").val();
                        var xcardname = getName(str_swipe);
                        var xcardnumber = getcc(str_swipe);
                        var xexpdate = getExpYYMMTrack2(str_swipe);
                        var xcvv = "";
                        var xzip = "";
                        switch (xcardnumber.substring(0, 1)) {
                            case 3:
                            case "3":
                                $("#xpopImage_paymethod").html("<img src='/img/american.png'>");
                                break;
                            case 4:
                            case "4":
                                $("#xpopImage_paymethod").html("<img src='/img/visa.png'>");
                                break;
                            case 5:
                            case "5":
                                $("#xpopImage_paymethod").html("<img src='/img/mastercard.png'>");
                                break;
                            case 6:
                            case "6":
                                $("#xpopImage_paymethod").html("<img src='/img/discover.png'>");
                                break;
                            default:
                                $("#xpopImage_paymethod").html("<img src='/img/visa.png'>");
                                break;
                        }
                        $("#xpopName_paymethod").html("XXXX-" + xcardnumber.substring(xcardnumber.length - 4, xcardnumber.length));

                    }
                } else {
                    var sdate=currentDate.getFullYear() + "-" + (currentDate.getMonth() + 1) + "-" + currentDate.getDate();
                    var stoday = new Date();
                    var sdate2=stoday.getFullYear() + "-" + (stoday.getMonth() + 1) + "-" + stoday.getDate();
                    if(novault==1 && sdate!=sdate2){
                        swal({
                            title: "Attention",
                            text: "You cannot make any CC payment in the future. Please select a Payment Method"

                        });
                    }
                    var str_cc = $("#xcardnumber").val();
                    var firstcharcard = str_cc.substring(0, 1);
                    switch (str_cc.substring(0, 1)) {
                        case 3:
                        case "3":
                            $("#xpopImage_paymethod").html("<img src='/img/american.png'>");
                            break;
                        case 4:
                        case "4":
                            $("#xpopImage_paymethod").html("<img src='/img/visa.png'>");
                            break;
                        case 5:
                        case "5":
                            $("#xpopImage_paymethod").html("<img src='/img/mastercard.png'>");
                            break;
                        case 6:
                        case "6":
                            $("#xpopImage_paymethod").html("<img src='/img/discover.png'>");
                            break;
                        default:
                            $("#xpopImage_paymethod").html("<img src='/img/visa.png'>");
                            break;
                    }
                    $("#xpopName_paymethod").html("XXXX-" + str_cc.substring(str_cc.length - 4, str_cc.length));
                }
            } else if(vpaydata()) {
                var str_ec = $("#xppec_acc").val();
                $("#xpopImage_paymethod").html("<img src='/img/echeck.png'>");
                $("#xpopName_paymethod").html("XXXX-" + str_ec.substring(str_ec.length - 4, str_ec.length));
            } else {
                swal({
                    title: "Attention",
                    text: "Please complete your payment information to continue"

                });
                return false;
            }
        }

        if (!validateVelocity()) {
            return false;
        }
        $("#xppayorinfo").html(simple_str);

        if (xamount > 0) {
            if (!vpaydata()) {
                /*$("#xpopupheader").html("Attention!");
                 $("#xpopupcontent").html("<label>Please complete your payment information to continue.</label>");
                 $('#myModal_success').modal();*/
                swal({
                    title: "Attention",
                    text: "Please complete your payment information to continue"

                });
                return false;
            }
        }

        if (opc == 'inv') {
            if (type == 'swipe') {
                if (!validate_trackdata()) {
                    return false;
                }
            }
            preparePopInv();
        } else {

            if (activeDRP > 0 && isrecurring && $("#inlineRadio1").is(':checked') == true) {
                $("#xpopupheader").html("Attention!");
                $("#xpopupcontent").html("<label>You have a Dynamic payment Active.</label>");
                $('#myModal_success').modal();
                return false;

            }
            if (limitAutopay != '0' && limitAutopay !='' ) //limitAutopay =0 mean the limit restrictions will be not apply to eterminal
            {

                if ((activeAuto + activeDRP + 1) > limitAutopay && isrecurring && $("#inlineRadio2").is(':checked') == true) {
                    $("#xpopupheader").html("Attention!");
                    $("#xpopupcontent").html("<label>You cannot make a autopay because you exceeded the limit for that.</label>");
                    $('#myModal_success').modal();
                    return false;

                }
            }


            if ((existsDrp == 1 && $("#inlineRadio1").is(':checked') == true && isrecurring) || parseFloat(xamount) > 0) {
                drawCatPopup();
                preparePopUp();
            } else {
                /*$("#xpopupheader").html("");
                 $("#xpopupcontent").html("<label>Please select a payment category before proceeding.</label>");
                 $('#myModal_success').modal();*/
                swal({
                    title: "Attention",
                    text: "Please select a payment category before proceeding"

                });
            }

        }

    }
}

var otwalkin_velocity_ec = new Object();
var otwalkin_velocity_cc = new Object();
var otwalkin_velocity_amex = new Object();
var otwalkin_velocity_swipe = new Object();

function eterm_calculateFee(type, amount, card_type) {
    amount = parseFloat(amount);
    $("#xservice_fee2").show();
    $("#xservice_phonefee2").show();
    $("#xservice_fee").show();
    $("#xservice_fee_inv").show();
    $("#walkin_div").hide();
    $("#phonefee_div").hide();
    $("#phonefee_value").val(0);


    var fee = 0;
    var flat_fee = 0;
    var porcent_fee = 0;

    otwalkin_velocity_ec = "";
    otwalkin_velocity_cc = "";
    otwalkin_velocity_amex = "";
    otwalkin_velocity_swipe = "";

    var select_date = currentDate.getFullYear() + "-" + (currentDate.getMonth() + 1) + "-" + currentDate.getDate();
    var onetimefuture = 0;
    if (select_date > xnowday) {
        onetimefuture = 1;
    }

    if (!isrecurring && !onetimefuture) {
        //is one time
        if (type == 'ec') {
            if (ot_fee_ec.length == 1) {
                fee += parseFloat(ot_fee_ec[0]['convenience_fee']);
                flat_fee = parseFloat(ot_fee_ec[0]['convenience_fee']);
                porcent_fee = ot_fee_ec[0]['convenience_fee_float'];
                if (ot_fee_ec[0]['convenience_fee_float'] > 0) {
                    var ffee = (amount * ot_fee_ec[0]['convenience_fee_float']) / 100;
                    fee = fee + parseFloat(ffee + 0.0001);
                }
                if ($('#etermpaymenttype').length) {
                    if ($('#etermpaymenttype').val() > "0") {
                        if (ot_fee_ec[0]['service']) {
                            var subtype_array = ot_fee_ec[0]['service'];

                            for (var i = 0; i < subtype_array.length; i++) {

                                if (subtype_array[i]['service_type'] == $('#etermpaymenttype').val()) {
                                    //if (subtype_array[i]['low_pay_range'] <= amount && subtype_array[i]['high_pay_range'] >= amount){
                                    fee = parseFloat(subtype_array[i]['convenience_fee']);
                                    flat_fee = parseFloat(subtype_array[i]['convenience_fee']);
                                    porcent_fee = subtype_array[i]['convenience_fee_float'];
                                    if (subtype_array[i]['convenience_fee_float'] > 0) {
                                        var ffee = (amount * subtype_array[i]['convenience_fee_float']) / 100;
                                        fee = fee + parseFloat(ffee + 0.0001);
                                        otwalkin_velocity_ec = {
                                            'low_pay_range': parseFloat(subtype_array[i]['low_pay_range']),
                                            'high_pay_range': parseFloat(subtype_array[i]['high_pay_range'])
                                        };
                                    }
                                    break;
                                    // }
                                }

                            }
                        } else {
                            $("#xpopupheader").html("Information");
                            $("#xpopupcontent").html("You can not make this type of payment");
                            $("#myModal_success").modal();
                            $("#checkbox4").attr("checked", false);
                            CalculaFeeX(2);
                        }
                    }
                }
            } else if (ot_fee_ec.length > 1) {
                for (var i = 0; i < ot_fee_ec.length; i++) {
                    if (ot_fee_ec[i]['low_pay_range'] <= amount && ot_fee_ec[i]['high_pay_range'] >= amount) {
                        fee += parseFloat(ot_fee_ec[i]['convenience_fee']);
                        flat_fee = parseFloat(ot_fee_ec[i]['convenience_fee']);
                        porcent_fee = ot_fee_ec[i]['convenience_fee_float'];
                        if (ot_fee_ec[i]['convenience_fee_float'] > 0) {
                            var ffee = (amount * ot_fee_ec[i]['convenience_fee_float']) / 100;
                            fee = fee + parseFloat(ffee + 0.0001);
                        }
                        if ($('#etermpaymenttype').length) {
                            if ($('#etermpaymenttype').val() > "0") {
                                if (ot_fee_ec[i]['service']) {
                                    var subtype_array = ot_fee_ec[i]['service'];
                                    for (var j = 0; j < subtype_array.length; j++) {

                                        if (subtype_array[j]['service_type'] == $('#etermpaymenttype').val()) {
                                            if (subtype_array[j]['low_pay_range'] <= amount && subtype_array[j]['high_pay_range'] >= amount) {
                                                fee = parseFloat(subtype_array[j]['convenience_fee']);
                                                flat_fee = parseFloat(subtype_array[j]['convenience_fee']);
                                                porcent_fee = subtype_array[j]['convenience_fee_float'];
                                                if (subtype_array[j]['convenience_fee_float'] > 0) {
                                                    var ffee = (amount * subtype_array[j]['convenience_fee_float']) / 100;
                                                    fee = fee + parseFloat(ffee + 0.0001);
                                                    otwalkin_velocity_ec = {
                                                        'low_pay_range': parseFloat(subtype_array[i]['low_pay_range']),
                                                        'high_pay_range': parseFloat(subtype_array[i]['high_pay_range'])
                                                    };
                                                }
                                                break;
                                            }
                                        }

                                    }
                                } else {
                                    $("#xpopupheader").html("Information");
                                    $("#xpopupcontent").html("You can not make this type of payment");
                                    $("#myModal_success").modal();
                                    $("#checkbox4").attr("checked", false);
                                    CalculaFeeX(2);
                                }
                            }
                        }
                        break;
                    }
                }
            }

            //setting eterminal onetime and ec
            if(includes(walkin_one_time,'ec') && fee > 0){
                $("#walkin_div").show();
               if($('.etermial_walkin').prop('checked')){
                   fee = 0;
                   $("#xservice_walkin").prop('hidden',false);
               }else{
                   $("#xservice_walkin").prop('hidden',true);
               }
            }else{
                $("#walkin_div").hide();
                $("#walkin").prop('checked',false);
            }

            if(typeof phonefee_one_time['ec'] === 'undefined'){
                $("#phonefee_value").val(0);
                $("#phonefee_div").hide();
            }else{
                $("#phonefee_div").show();
                if(phonefee_edited != -1){
                    $("#phonefee_value").val(phonefee_edited);
                }else{
                    $("#phonefee_value").val(phonefee_one_time['ec']);
                }
            }

        } else if (type == 'cc') {
            //alert(ot_fee_cc[0]);
            if (ot_fee_cc.length == 1) {
                fee += parseFloat(ot_fee_cc[0]['convenience_fee']);
                flat_fee = parseFloat(ot_fee_cc[0]['convenience_fee']);
                porcent_fee = ot_fee_cc[0]['convenience_fee_float'];
                if (ot_fee_cc[0]['convenience_fee_float'] > 0) {
                    var ffee = (amount * ot_fee_cc[0]['convenience_fee_float']) / 100;
                    fee = fee + parseFloat(ffee + 0.0001);
                }
                if (card_type) {
                    if (ot_fee_cc[0]['card_type']) {
                        var cardtype_array = ot_fee_cc[0]['card_type'];

                        for (var i = 0; i < cardtype_array.length; i++) {

                            if (cardtype_array[i]['type'] == card_type) {
                                fee = parseFloat(cardtype_array[i]['convenience_fee']);
                                flat_fee = parseFloat(cardtype_array[i]['convenience_fee']);
                                porcent_fee = cardtype_array[i]['convenience_fee_float'];
                                if (cardtype_array[i]['convenience_fee_float'] > 0) {
                                    var ffee = (amount * cardtype_array[i]['convenience_fee_float']) / 100;
                                    fee = fee + parseFloat(ffee + 0.0001);
                                }
                                break;
                            }

                        }
                    }
                }

                if ($('#etermpaymenttype').length) {
                    if ($('#etermpaymenttype').val() > "0") {
                        if (ot_fee_cc[0]['service']) {
                            var subtype_array = ot_fee_cc[0]['service'];
                            for (var i = 0; i < subtype_array.length; i++) {

                                if (subtype_array[i]['service_type'] == $('#etermpaymenttype').val()) {
                                    if (subtype_array[i]['low_pay_range'] <= amount && subtype_array[i]['high_pay_range'] >= amount) {
                                        fee = parseFloat(subtype_array[i]['convenience_fee']);
                                        flat_fee = parseFloat(subtype_array[i]['convenience_fee']);
                                        porcent_fee = subtype_array[i]['convenience_fee_float'];
                                        if (subtype_array[i]['convenience_fee_float'] > 0) {
                                            var ffee = (amount * subtype_array[i]['convenience_fee_float']) / 100;
                                            fee = fee + parseFloat(ffee + 0.0001);
                                            otwalkin_velocity_cc = {
                                                'low_pay_range': parseFloat(subtype_array[i]['low_pay_range']),
                                                'high_pay_range': parseFloat(subtype_array[i]['high_pay_range'])
                                            };
                                        }
                                        break;
                                    }
                                }

                            }
                        } else {
                            $("#xpopupheader").html("Information");
                            $("#xpopupcontent").html("You can not make this type of payment");
                            $("#myModal_success").modal();
                            $("#checkbox5").prop("checked", false);
                            CalculaFeeX(2);
                        }
                    }
                }
            } else if (ot_fee_cc.length > 1) {
                for (var i = 0; i < ot_fee_cc.length; i++) {
                    if (ot_fee_cc[i]['low_pay_range'] <= amount && ot_fee_cc[i]['high_pay_range'] >= amount) {
                        flat_fee = parseFloat(ot_fee_cc[i]['convenience_fee']);
                        porcent_fee = ot_fee_cc[i]['convenience_fee_float'];
                        fee += parseFloat(ot_fee_cc[i]['convenience_fee']);
                        if (ot_fee_cc[i]['convenience_fee_float'] > 0) {
                            var ffee = (amount * ot_fee_cc[i]['convenience_fee_float']) / 100;
                            fee = fee + parseFloat(ffee + 0.0001);
                        }
                        if (card_type) {
                            if (ot_fee_cc[i]['card_type']) {
                                var cardtype_array = ot_fee_cc[i]['card_type'];

                                for (var j = 0; j < cardtype_array.length; j++) {

                                    if (cardtype_array[j]['type'] == card_type) {
                                        fee = parseFloat(cardtype_array[j]['convenience_fee']);
                                        flat_fee = parseFloat(cardtype_array[j]['convenience_fee']);
                                        porcent_fee = cardtype_array[j]['convenience_fee_float'];
                                        if (cardtype_array[j]['convenience_fee_float'] > 0) {
                                            var ffee = (amount * cardtype_array[j]['convenience_fee_float']) / 100;
                                            fee = fee + parseFloat(ffee + 0.0001);
                                        }
                                        break;
                                    }

                                }
                            }
                        }

                        if ($('#etermpaymenttype').length) {
                            if ($('#etermpaymenttype').val() > "0") {
                                if (ot_fee_cc[i]['service'] != "") {
                                    var subtype_array = ot_fee_cc[i]['service'];
                                    for (var j = 0; j < subtype_array.length; j++) {

                                        if (subtype_array[j]['service_type'] == $('#etermpaymenttype').val()) {
                                            if (subtype_array[j]['low_pay_range'] <= amount && subtype_array[j]['high_pay_range'] >= amount) {
                                                fee = parseFloat(subtype_array[j]['convenience_fee']);
                                                flat_fee = parseFloat(subtype_array[j]['convenience_fee']);
                                                porcent_fee = subtype_array[j]['convenience_fee_float'];
                                                if (subtype_array[j]['convenience_fee_float'] > 0) {
                                                    var ffee = (amount * subtype_array[j]['convenience_fee_float']) / 100;
                                                    fee = fee + parseFloat(ffee + 0.0001);
                                                    otwalkin_velocity_cc = {
                                                        'low_pay_range': parseFloat(subtype_array[i]['low_pay_range']),
                                                        'high_pay_range': parseFloat(subtype_array[i]['high_pay_range'])
                                                    };
                                                }
                                                break;
                                            }
                                        }

                                    }
                                } else {
                                    $("#xpopupheader").html("Information");
                                    $("#xpopupcontent").html("You can not make this type of payment");
                                    $("#myModal_success").modal();
                                    $("#checkbox5").attr("checked", false);
                                    fee = 0;
                                }
                            }
                        }
                        break;
                    }
                }
            }
            //setting eterminal onetime and cc
            if(includes(walkin_one_time,'cc') && fee > 0){
                $("#walkin_div").show();
                if($('.etermial_walkin').prop('checked')){
                    fee = 0;
                    $("#xservice_walkin").prop('hidden',false);
                }else{
                    $("#xservice_walkin").prop('hidden',true);
                }
            }else{
                $("#walkin_div").hide();
                $("#walkin").prop('checked',false);
            }

            if(typeof phonefee_one_time['cc'] === 'undefined'){
                $("#phonefee_value").val(0);
                $("#phonefee_div").hide();
            }else{
                $("#phonefee_div").show();
                if(phonefee_edited != -1){
                    $("#phonefee_value").val(phonefee_edited);
                }else{
                    $("#phonefee_value").val(phonefee_one_time['cc']);
                }
            }

        } else if (type == 'am') {
            if (ot_fee_amex.length == 1) {
                fee += parseFloat(ot_fee_amex[0]['convenience_fee']);
                flat_fee = parseFloat(ot_fee_amex[0]['convenience_fee']);
                porcent_fee = ot_fee_amex[0]['convenience_fee_float'];
                if (ot_fee_amex[0]['convenience_fee_float'] > 0) {
                    var ffee = (amount * ot_fee_amex[0]['convenience_fee_float']) / 100;
                    fee = fee + parseFloat(ffee + 0.0001);
                }

                if ($('#etermpaymenttype').length) {
                    if ($('#etermpaymenttype').val() > "0") {
                        if (ot_fee_amex[0]['service'] != "") {
                            var subtype_array = ot_fee_amex[0]['service'];
                            for (var i = 0; i < subtype_array.length; i++) {

                                if (subtype_array[i]['service_type'] == $('#etermpaymenttype').val()) {
                                    if (subtype_array[i]['low_pay_range'] <= amount && subtype_array[i]['high_pay_range'] >= amount) {
                                        fee = parseFloat(subtype_array[i]['convenience_fee']);
                                        flat_fee = parseFloat(subtype_array[i]['convenience_fee']);
                                        porcent_fee = subtype_array[i]['convenience_fee_float'];
                                        if (subtype_array[i]['convenience_fee_float'] > 0) {
                                            var ffee = (amount * subtype_array[i]['convenience_fee_float']) / 100;
                                            fee = fee + parseFloat(ffee + 0.0001);
                                            otwalkin_velocity_amex = {
                                                'low_pay_range': parseFloat(subtype_array[i]['low_pay_range']),
                                                'high_pay_range': parseFloat(subtype_array[i]['high_pay_range'])
                                            };
                                        }
                                        break;
                                    }
                                }

                            }
                        } else {
                            $("#xpopupheader").html("Information");
                            $("#xpopupcontent").html("You can not make this type of payment");
                            $("#myModal_success").modal();
                            $("#checkbox5").attr("checked", false);
                            $("#xmodalservice_fee").hide();
                            $("#xservice_fee").hide();
                        }
                    }
                }

            } else if (ot_fee_amex.length > 1) {
                for (var i = 0; i < ot_fee_amex.length; i++) {
                    if (ot_fee_amex[i]['low_pay_range'] <= amount && ot_fee_amex[i]['high_pay_range'] >= amount) {
                        fee += parseFloat(ot_fee_amex[i]['convenience_fee']);
                        flat_fee = parseFloat(ot_fee_amex[i]['convenience_fee']);
                        porcent_fee = ot_fee_amex[i]['convenience_fee_float'];
                        if (ot_fee_amex[i]['convenience_fee_float'] > 0) {
                            var ffee = (amount * ot_fee_amex[i]['convenience_fee_float']) / 100;
                            fee = fee + parseFloat(ffee + 0.0001);
                        }

                        if ($('#etermpaymenttype').length) {
                            if ($('#etermpaymenttype').val() > "0") {
                                if (ot_fee_amex[i]['service'] != "") {
                                    var subtype_array = ot_fee_amex[i]['service'];
                                    for (var j = 0; j < subtype_array.length; j++) {

                                        if (subtype_array[j]['service_type'] == $('#etermpaymenttype').val()) {
                                            if (subtype_array[j]['low_pay_range'] <= amount && subtype_array[j]['high_pay_range'] >= amount) {
                                                fee = parseFloat(subtype_array[j]['convenience_fee']);
                                                flat_fee = parseFloat(subtype_array[j]['convenience_fee']);
                                                porcent_fee = subtype_array[j]['convenience_fee_float'];
                                                if (subtype_array[j]['convenience_fee_float'] > 0) {
                                                    var ffee = (amount * subtype_array[j]['convenience_fee_float']) / 100;
                                                    fee = fee + parseFloat(ffee + 0.0001);
                                                    otwalkin_velocity_amex = {
                                                        'low_pay_range': parseFloat(subtype_array[i]['low_pay_range']),
                                                        'high_pay_range': parseFloat(subtype_array[i]['high_pay_range'])
                                                    };
                                                }
                                                break;
                                            }
                                        }

                                    }
                                } else {
                                    $("#xpopupheader").html("Information");
                                    $("#xpopupcontent").html("You can not make this type of payment");
                                    $("#myModal_success").modal();
                                    $("#checkbox5").attr("checked", false);
                                    CalculaFeeX(2);
                                }
                            }
                        }
                        break;
                    }
                }
            }
            //setting eterminal onetime and amex
            if(includes(walkin_one_time,'amex') && fee > 0){
                $("#walkin_div").show();
                if($('.etermial_walkin').prop('checked')){
                    fee = 0;
                    $("#xservice_walkin").prop('hidden',false);
                }else{
                    $("#xservice_walkin").prop('hidden',true);
                }
            }else{
                $("#walkin_div").hide();
                $("#walkin").prop('checked',false);
            }

            if(typeof phonefee_one_time['amex'] === 'undefined'){
                $("#phonefee_value").val(0);
                $("#phonefee_div").hide();
            }else{
                $("#phonefee_div").show();
                if(phonefee_edited != -1){
                    $("#phonefee_value").val(phonefee_edited);
                }else{
                    $("#phonefee_value").val(phonefee_one_time['amex']);
                }
            }

        } else if (type == 'swipe') {

            if (ot_fee_swipe.length == 1) {
                fee += parseFloat(ot_fee_swipe[0]['convenience_fee']);
                flat_fee = parseFloat(ot_fee_swipe[0]['convenience_fee']);
                porcent_fee = ot_fee_swipe[0]['convenience_fee_float'];
                if (ot_fee_swipe[0]['convenience_fee_float'] > 0) {
                    var ffee = (amount * ot_fee_swipe[0]['convenience_fee_float']) / 100;
                    fee = fee + parseFloat(ffee + 0.0001);
                }
                if (card_type) {
                    if (ot_fee_swipe[0]['card_type']) {
                        var cardtype_array = ot_fee_swipe[0]['card_type'];

                        for (var i = 0; i < cardtype_array.length; i++) {

                            if (cardtype_array[i]['type'] == card_type) {
                                fee = parseFloat(cardtype_array[i]['convenience_fee']);
                                flat_fee = parseFloat(cardtype_array[i]['convenience_fee']);
                                porcent_fee = cardtype_array[i]['convenience_fee_float'];
                                if (cardtype_array[i]['convenience_fee_float'] > 0) {
                                    var ffee = (amount * cardtype_array[i]['convenience_fee_float']) / 100;
                                    fee = fee + parseFloat(ffee + 0.0001);
                                }
                                break;
                            }

                        }
                    }
                }

                if ($('#etermpaymenttype').length) {
                    if ($('#etermpaymenttype').val() > "0") {
                        if (ot_fee_swipe[0]['service']) {
                            var subtype_array = ot_fee_swipe[0]['service'];
                            for (var i = 0; i < subtype_array.length; i++) {

                                if (subtype_array[i]['service_type'] == $('#etermpaymenttype').val()) {
                                    if (subtype_array[i]['low_pay_range'] <= amount && subtype_array[i]['high_pay_range'] >= amount) {
                                        fee = parseFloat(subtype_array[i]['convenience_fee']);
                                        flat_fee = parseFloat(subtype_array[i]['convenience_fee']);
                                        porcent_fee = subtype_array[i]['convenience_fee_float'];
                                        if (subtype_array[i]['convenience_fee_float'] > 0) {
                                            var ffee = (amount * subtype_array[i]['convenience_fee_float']) / 100;
                                            fee = fee + parseFloat(ffee + 0.0001);
                                            otwalkin_velocity_cc = {
                                                'low_pay_range': parseFloat(subtype_array[i]['low_pay_range']),
                                                'high_pay_range': parseFloat(subtype_array[i]['high_pay_range'])
                                            };
                                        }
                                        break;
                                    }
                                }

                            }
                        } else {
                            $("#xpopupheader").html("Information");
                            $("#xpopupcontent").html("You can not make this type of payment");
                            $("#myModal_success").modal();
                            $("#checkbox6").prop("checked", false);
                            CalculaFeeX(5);
                        }
                    }
                }
            } else if (ot_fee_swipe.length > 1) {
                for (var i = 0; i < ot_fee_swipe.length; i++) {
                    if (ot_fee_swipe[i]['low_pay_range'] <= amount && ot_fee_swipe[i]['high_pay_range'] >= amount) {
                        flat_fee = parseFloat(ot_fee_swipe[i]['convenience_fee']);
                        porcent_fee = ot_fee_swipe[i]['convenience_fee_float'];
                        fee += parseFloat(ot_fee_swipe[i]['convenience_fee']);
                        if (ot_fee_swipe[i]['convenience_fee_float'] > 0) {
                            var ffee = (amount * ot_fee_swipe[i]['convenience_fee_float']) / 100;
                            fee = fee + parseFloat(ffee + 0.0001);
                        }
                        if (card_type) {
                            if (ot_fee_swipe[i]['card_type']) {
                                var cardtype_array = ot_fee_swipe[i]['card_type'];

                                for (var j = 0; j < cardtype_array.length; j++) {

                                    if (cardtype_array[j]['type'] == card_type) {
                                        fee = parseFloat(cardtype_array[j]['convenience_fee']);
                                        flat_fee = parseFloat(cardtype_array[j]['convenience_fee']);
                                        porcent_fee = cardtype_array[j]['convenience_fee_float'];
                                        if (cardtype_array[j]['convenience_fee_float'] > 0) {
                                            var ffee = (amount * cardtype_array[j]['convenience_fee_float']) / 100;
                                            fee = fee + parseFloat(ffee + 0.0001);
                                        }
                                        break;
                                    }

                                }
                            }
                        }

                        if ($('#etermpaymenttype').length) {
                            if ($('#etermpaymenttype').val() > "0") {
                                if (ot_fee_swipe[i]['service'] != "") {
                                    var subtype_array = ot_fee_swipe[i]['service'];
                                    for (var j = 0; j < subtype_array.length; j++) {

                                        if (subtype_array[j]['service_type'] == $('#etermpaymenttype').val()) {
                                            if (subtype_array[j]['low_pay_range'] <= amount && subtype_array[j]['high_pay_range'] >= amount) {
                                                fee = parseFloat(subtype_array[j]['convenience_fee']);
                                                flat_fee = parseFloat(subtype_array[j]['convenience_fee']);
                                                porcent_fee = subtype_array[j]['convenience_fee_float'];
                                                if (subtype_array[j]['convenience_fee_float'] > 0) {
                                                    var ffee = (amount * subtype_array[j]['convenience_fee_float']) / 100;
                                                    fee = fee + parseFloat(ffee + 0.0001);
                                                    otwalkin_velocity_cc = {
                                                        'low_pay_range': parseFloat(subtype_array[i]['low_pay_range']),
                                                        'high_pay_range': parseFloat(subtype_array[i]['high_pay_range'])
                                                    };
                                                }
                                                break;
                                            }
                                        }

                                    }
                                } else {
                                    $("#xpopupheader").html("Information");
                                    $("#xpopupcontent").html("You can not make this type of payment");
                                    $("#myModal_success").modal();
                                    $("#checkbox6").attr("checked", false);
                                    fee = 0;
                                }
                            }
                        }
                        break;
                    }
                }
            }
            //setting eterminal  swipe
            if(includes(walkin_one_time,'swipe') && fee > 0){
                $("#walkin_div").show();
                if($('.etermial_walkin').prop('checked')){
                    fee = 0;
                    $("#xservice_walkin").prop('hidden',false);
                }else{
                    $("#xservice_walkin").prop('hidden',true);
                }
            }else{
                $("#walkin_div").hide();
                $("#walkin").prop('checked',false);
            }
        }
    }
    if (isrecurring || onetimefuture) {
        //is recurring
        if ($("#inlineRadio1").is(":checked") && isrecurring) { //DRP Convenience fee
            if (type == 'ec') {
                if (rc_fee_ec.length == 1) {
                    fee += parseFloat(rc_fee_ec[0]['convenience_fee_drp']);
                    flat_fee = parseFloat(rc_fee_ec[0]['convenience_fee_drp']);
                    porcent_fee = rc_fee_ec[0]['convenience_fee_float_drp'];
                    if (rc_fee_ec[0]['convenience_fee_float_drp'] > 0) {
                        if (amount > 0) {
                            var ffee = (amount * rc_fee_ec[0]['convenience_fee_float_drp']) / 100;
                        } else {
                            var ffee = (rc_fee_ec[0]['convenience_fee_float_drp']) / 100;
                        }
                        fee = fee + parseFloat(ffee + 0.0001);
                    }
                } else if (rc_fee_ec.length > 1) {
                    for (var i = 0; i < rc_fee_ec.length; i++) {
                        if (rc_fee_ec[i]['low_pay_range'] <= amount && rc_fee_ec[i]['high_pay_range'] >= amount) {
                            fee += parseFloat(rc_fee_ec[i]['convenience_fee_drp']);
                            flat_fee = parseFloat(rc_fee_ec[i]['convenience_fee_drp']);
                            porcent_fee = rc_fee_ec[i]['convenience_fee_float_drp'];
                            if (rc_fee_ec[i]['convenience_fee_float_drp'] > 0) {
                                var ffee = (amount * rc_fee_ec[i]['convenience_fee_float_drp']) / 100;
                                fee = fee + parseFloat(ffee + 0.0001);
                            }
                            break;
                        }
                    }
                }

                //setting eterminal recurring and ec
                if(includes(walkin_recurring,'ec') && fee > 0){
                    $("#walkin_div").show();
                    if($('.etermial_walkin').prop('checked')){
                        fee = 0;
                        $("#xservice_walkin").prop('hidden',false);
                    }else{
                        $("#xservice_walkin").prop('hidden',true);
                    }
                }else{
                    $("#walkin_div").hide();
                    $("#walkin").prop('checked',false);
                }
                if(typeof phonefee_recurring['ec'] === 'undefined'){
                    $("#phonefee_value").val(0);
                    $("#phonefee_div").hide();
                }else{
                    $("#phonefee_div").show();
                    if(phonefee_edited != -1){
                        $("#phonefee_value").val(phonefee_edited);
                    }else{
                        $("#phonefee_value").val(phonefee_recurring['ec']);
                    }
                }

            } else if (type == 'cc') {
                if (rc_fee_cc.length == 1) {
                    fee += parseFloat(rc_fee_cc[0]['convenience_fee_drp']);
                    flat_fee = parseFloat(rc_fee_cc[0]['convenience_fee_drp']);
                    porcent_fee = rc_fee_cc[0]['convenience_fee_float_drp'];
                    if (rc_fee_cc[0]['convenience_fee_float_drp'] > 0) {
                        if (amount > 0) {
                            var ffee = (amount * rc_fee_cc[0]['convenience_fee_float_drp']) / 100;
                        } else {
                            var ffee = (rc_fee_cc[0]['convenience_fee_float_drp']) / 100;
                        }
                        fee = fee + parseFloat(ffee + 0.0001);
                    }
                    if (card_type) {
                        if (rc_fee_cc[0]['card_type']) {
                            var cardtype_array = rc_fee_cc[0]['card_type'];
                            for (var i = 0; i < cardtype_array.length; i++) {
                                if (cardtype_array[i]['type'] === card_type) {
                                    fee = parseFloat(cardtype_array[i]['convenience_fee_drp']);
                                    flat_fee = parseFloat(cardtype_array[i]['convenience_fee_drp']);
                                    porcent_fee = cardtype_array[i]['convenience_fee_float_drp'];
                                    if (cardtype_array[i]['convenience_fee_float_drp'] > 0) {
                                        if (amount > 0) {
                                            var ffee = (amount * cardtype_array[i]['convenience_fee_float_drp']) / 100;
                                        } else {
                                            var ffee = (cardtype_array[i]['convenience_fee_float_drp']) / 100;
                                        }
                                        fee = fee + parseFloat(ffee + 0.0001);
                                    }
                                    break;
                                }
                            }
                        }
                    }
                } else if (rc_fee_cc.length > 1) {
                    for (var i = 0; i < rc_fee_cc.length; i++) {
                        if (rc_fee_cc[i]['low_pay_range'] <= amount && rc_fee_cc[i]['high_pay_range'] >= amount) {
                            fee += parseFloat(rc_fee_cc[i]['convenience_fee_drp']);
                            flat_fee = parseFloat(rc_fee_cc[i]['convenience_fee_drp']);
                            porcent_fee = rc_fee_cc[i]['convenience_fee_float_drp'];
                            if (rc_fee_cc[i]['convenience_fee_float_drp'] > 0) {
                                var ffee = (amount * rc_fee_cc[i]['convenience_fee_float_drp']) / 100;
                                fee = fee + parseFloat(ffee + 0.0001);
                            }
                            if (card_type) {
                                if (rc_fee_cc[i]['card_type']) {
                                    var cardtype_array = rc_fee_cc[i]['card_type'];
                                    for (var j = 0; j < cardtype_array.length; j++) {
                                        if (cardtype_array[j]['type'] === card_type) {
                                            fee = parseFloat(cardtype_array[j]['convenience_fee_drp']);
                                            flat_fee = parseFloat(cardtype_array[j]['convenience_fee_drp']);
                                            porcent_fee = cardtype_array[j]['convenience_fee_float_drp'];
                                            if (cardtype_array[j]['convenience_fee_float_drp'] > 0) {
                                                if (amount > 0) {
                                                    var ffee = (amount * cardtype_array[j]['convenience_fee_float_drp']) / 100;
                                                } else {
                                                    var ffee = (cardtype_array[j]['convenience_fee_float_drp']) / 100;
                                                }
                                                fee = fee + parseFloat(ffee + 0.0001);
                                            }
                                            break;
                                        }
                                    }
                                }
                            }
                            break;
                        }
                    }
                }

                //setting eterminal recurring and cc
                if(includes(walkin_recurring,'cc') && fee > 0){
                    $("#walkin_div").show();
                    if($('.etermial_walkin').prop('checked')){
                        fee = 0;
                        $("#xservice_walkin").prop('hidden',false);
                    }else{
                        $("#xservice_walkin").prop('hidden',true);
                    }
                }else{
                    $("#walkin_div").hide();
                    $("#walkin").prop('checked',false);
                }

                if(typeof phonefee_recurring['cc'] === 'undefined'){
                    $("#phonefee_value").val(0);
                    $("#phonefee_div").hide();
                }else{
                    $("#phonefee_div").show();
                    if(phonefee_edited != -1){
                        $("#phonefee_value").val(phonefee_edited);
                    }else{
                        $("#phonefee_value").val(phonefee_recurring['cc']);
                    }
                }
            } else if (type == 'am') {
                if (rc_fee_amex.length == 1) {
                    fee += parseFloat(rc_fee_amex[0]['convenience_fee_drp']);
                    flat_fee = parseFloat(rc_fee_amex[0]['convenience_fee_drp']);
                    porcent_fee = rc_fee_amex[0]['convenience_fee_float_drp'];
                    if (rc_fee_amex[0]['convenience_fee_float_drp'] > 0) {
                        if (amount > 0) {
                            var ffee = (amount * rc_fee_amex[0]['convenience_fee_float_drp']) / 100;
                        } else {
                            var ffee = (rc_fee_amex[0]['convenience_fee_float_drp']) / 100;
                        }
                        fee = fee + parseFloat(ffee + 0.0001);
                    }
                } else if (rc_fee_amex.length > 1) {
                    for (var i = 0; i < rc_fee_amex.length; i++) {
                        if (rc_fee_amex[i]['low_pay_range'] <= amount && rc_fee_amex[i]['high_pay_range'] >= amount) {
                            fee += parseFloat(rc_fee_amex[i]['convenience_fee_drp']);
                            flat_fee = parseFloat(rc_fee_amex[i]['convenience_fee_drp']);
                            porcent_fee = rc_fee_amex[i]['convenience_fee_float_drp'];
                            if (rc_fee_amex[i]['convenience_fee_float_drp'] > 0) {
                                var ffee = (amount * rc_fee_amex[i]['convenience_fee_float_drp']) / 100;
                                fee = fee + parseFloat((ffee + 0.0001));
                            }
                            break;
                        }
                    }
                }
                //setting eterminal recurring and amex
                if(includes(walkin_recurring,'amex') && fee > 0){
                    $("#walkin_div").show();
                    if($('.etermial_walkin').prop('checked')){
                        fee = 0;
                        $("#xservice_walkin").prop('hidden',false);
                    }else{
                        $("#xservice_walkin").prop('hidden',true);
                    }
                }else{
                    $("#walkin_div").hide();
                    $("#walkin").prop('checked',false);
                }
                if(typeof phonefee_recurring['amex'] === 'undefined'){
                    $("#phonefee_value").val(0);
                    $("#phonefee_div").hide();
                }else{
                    $("#phonefee_div").show();
                    if(phonefee_edited != -1){
                        $("#phonefee_value").val(phonefee_edited);
                    }else{
                        $("#phonefee_value").val(phonefee_recurring['amex']);
                    }
                }
            } else if (type == 'swipe') {
                if (typeof card_type_fee === 'undefined' || card_type_fee === null) {
                    // variable is undefined or null
                    var card_type_fee="";
               }
                if (card_type_fee == 'am') {
                    if (rc_fee_amex.length == 1) {
                        fee += parseFloat(rc_fee_amex[0]['convenience_fee_drp']);
                        flat_fee = parseFloat(rc_fee_amex[0]['convenience_fee_drp']);
                        porcent_fee = rc_fee_amex[0]['convenience_fee_float_drp'];
                        if (rc_fee_amex[0]['convenience_fee_float_drp'] > 0) {
                            if (amount > 0) {
                                var ffee = (amount * rc_fee_amex[0]['convenience_fee_float_drp']) / 100;
                            } else {
                                var ffee = (rc_fee_amex[0]['convenience_fee_float_drp']) / 100;
                            }
                            fee = fee + parseFloat(ffee + 0.0001);
                        }
                    } else if (rc_fee_amex.length > 1) {
                        for (var i = 0; i < rc_fee_amex.length; i++) {
                            if (rc_fee_amex[i]['low_pay_range'] <= amount && rc_fee_amex[i]['high_pay_range'] >= amount) {
                                fee += parseFloat(rc_fee_amex[i]['convenience_fee_drp']);
                                flat_fee = parseFloat(rc_fee_amex[i]['convenience_fee_drp']);
                                porcent_fee = rc_fee_amex[i]['convenience_fee_float_drp'];
                                if (rc_fee_amex[i]['convenience_fee_float_drp'] > 0) {
                                    var ffee = (amount * rc_fee_amex[i]['convenience_fee_float_drp']) / 100;
                                    fee = fee + parseFloat((ffee + 0.0001));
                                }
                                break;
                            }
                        }
                    }
                } else if (rc_fee_cc.length == 1) {
                    fee += parseFloat(rc_fee_cc[0]['convenience_fee_drp']);
                    flat_fee = parseFloat(rc_fee_cc[0]['convenience_fee_drp']);
                    porcent_fee = rc_fee_cc[0]['convenience_fee_float_drp'];
                    if (rc_fee_cc[0]['convenience_fee_float_drp'] > 0) {
                        if (amount > 0) {
                            var ffee = (amount * rc_fee_cc[0]['convenience_fee_float_drp']) / 100;
                        } else {
                            var ffee = (rc_fee_cc[0]['convenience_fee_float_drp']) / 100;
                        }
                        fee = fee + parseFloat(ffee + 0.0001);
                    }
                    if (card_type) {
                        if (rc_fee_cc[0]['card_type']) {
                            var cardtype_array = rc_fee_cc[0]['card_type'];
                            for (var i = 0; i < cardtype_array.length; i++) {
                                if (cardtype_array[i]['type'] === card_type) {
                                    fee = parseFloat(cardtype_array[i]['convenience_fee_drp']);
                                    flat_fee = parseFloat(cardtype_array[i]['convenience_fee_drp']);
                                    porcent_fee = cardtype_array[i]['convenience_fee_float_drp'];
                                    if (cardtype_array[i]['convenience_fee_float_drp'] > 0) {
                                        if (amount > 0) {
                                            var ffee = (amount * cardtype_array[i]['convenience_fee_float_drp']) / 100;
                                        } else {
                                            var ffee = (cardtype_array[i]['convenience_fee_float_drp']) / 100;
                                        }
                                        fee = fee + parseFloat(ffee + 0.0001);
                                    }
                                    break;
                                }
                            }
                        }
                    }
                } else if (rc_fee_cc.length > 1) {
                    for (var i = 0; i < rc_fee_cc.length; i++) {
                        if (rc_fee_cc[i]['low_pay_range'] <= amount && rc_fee_cc[i]['high_pay_range'] >= amount) {
                            fee += parseFloat(rc_fee_cc[i]['convenience_fee_drp']);
                            flat_fee = parseFloat(rc_fee_cc[i]['convenience_fee_drp']);
                            porcent_fee = rc_fee_cc[i]['convenience_fee_float_drp'];
                            if (rc_fee_cc[i]['convenience_fee_float_drp'] > 0) {
                                var ffee = (amount * rc_fee_cc[i]['convenience_fee_float_drp']) / 100;
                                fee = fee + parseFloat(ffee + 0.0001);
                            }
                            if (card_type) {
                                if (rc_fee_cc[i]['card_type']) {
                                    var cardtype_array = rc_fee_cc[i]['card_type'];
                                    for (var j = 0; j < cardtype_array.length; j++) {
                                        if (cardtype_array[j]['type'] === card_type) {
                                            fee = parseFloat(cardtype_array[j]['convenience_fee_drp']);
                                            flat_fee = parseFloat(cardtype_array[j]['convenience_fee_drp']);
                                            porcent_fee = cardtype_array[j]['convenience_fee_float_drp'];
                                            if (cardtype_array[j]['convenience_fee_float_drp'] > 0) {
                                                if (amount > 0) {
                                                    var ffee = (amount * cardtype_array[j]['convenience_fee_float_drp']) / 100;
                                                } else {
                                                    var ffee = (cardtype_array[j]['convenience_fee_float_drp']) / 100;
                                                }
                                                fee = fee + parseFloat(ffee + 0.0001);
                                            }
                                            break;
                                        }
                                    }
                                }
                            }
                            break;
                        }
                    }
                }
                //setting eterminal  swipe
                if(includes(walkin_one_time,'swipe') && fee > 0){
                    $("#walkin_div").show();
                    if($('.etermial_walkin').prop('checked')){
                        fee = 0;
                        $("#xservice_walkin").prop('hidden',false);
                    }else{
                        $("#xservice_walkin").prop('hidden',true);
                    }
                }else{
                    $("#walkin_div").hide();
                    $("#walkin").prop('checked',false);
                }
            }
        } else {
            if (type == 'ec') {
                if (rc_fee_ec.length == 1) {
                    fee += parseFloat(rc_fee_ec[0]['convenience_fee']);
                    flat_fee = parseFloat(rc_fee_ec[0]['convenience_fee']);
                    porcent_fee = rc_fee_ec[0]['convenience_fee_float'];
                    if (rc_fee_ec[0]['convenience_fee_float'] > 0) {
                        var ffee = (amount * rc_fee_ec[0]['convenience_fee_float']) / 100;
                        fee = fee + parseFloat(ffee + 0.0001);
                    }
                } else if (rc_fee_ec.length > 1) {
                    for (var i = 0; i < rc_fee_ec.length; i++) {
                        if (rc_fee_ec[i]['low_pay_range'] <= amount && rc_fee_ec[i]['high_pay_range'] >= amount) {
                            fee += parseFloat(rc_fee_ec[i]['convenience_fee']);
                            flat_fee = parseFloat(rc_fee_ec[i]['convenience_fee']);
                            porcent_fee = rc_fee_ec[i]['convenience_fee_float'];
                            if (rc_fee_ec[i]['convenience_fee_float'] > 0) {
                                var ffee = (amount * rc_fee_ec[i]['convenience_fee_float']) / 100;
                                fee = fee + parseFloat(ffee + 0.0001);
                            }
                            break;
                        }
                    }
                }
                //setting eterminal recurring and ec
                if(includes(walkin_recurring,'ec') && fee > 0){
                    $("#walkin_div").show();
                    if($('.etermial_walkin').prop('checked')){
                        fee = 0;
                        $("#xservice_walkin").prop('hidden',false);
                    }else{
                        $("#xservice_walkin").prop('hidden',true);
                    }
                }else{
                    $("#walkin_div").hide();
                    $("#walkin").prop('checked',false);
                }

                if(typeof phonefee_recurring['ec'] === 'undefined'){
                    $("#phonefee_value").val(0);
                    $("#phonefee_div").hide();
                }else{
                    $("#phonefee_div").show();
                    if(phonefee_edited != -1){
                        $("#phonefee_value").val(phonefee_edited);
                    }else{
                        $("#phonefee_value").val(phonefee_recurring['ec']);
                    }
                }

            } else if (type == 'cc') {
                if (rc_fee_cc.length == 1) {
                    fee += parseFloat(rc_fee_cc[0]['convenience_fee']);
                    flat_fee = parseFloat(rc_fee_cc[0]['convenience_fee']);
                    porcent_fee = rc_fee_cc[0]['convenience_fee_float'];
                    if (rc_fee_cc[0]['convenience_fee_float'] > 0) {
                        var ffee = (amount * rc_fee_cc[0]['convenience_fee_float']) / 100;
                        fee = fee + parseFloat(ffee + 0.0001);
                    }
                    if (card_type) {
                        if (rc_fee_cc[0]['card_type']) {
                            var cardtype_array = rc_fee_cc[0]['card_type'];
                            for (var i = 0; i < cardtype_array.length; i++) {
                                if (cardtype_array[i]['type'] == card_type) {
                                    fee = parseFloat(cardtype_array[i]['convenience_fee']);
                                    flat_fee = parseFloat(cardtype_array[i]['convenience_fee']);
                                    porcent_fee = cardtype_array[i]['convenience_fee_float'];
                                    if (rc_fee_cc[0]['convenience_fee_float'] > 0) {
                                        var ffee = (amount * cardtype_array[i]['convenience_fee_float']) / 100;
                                        fee = fee + parseFloat(ffee + 0.0001);
                                    }
                                }
                            }
                        }
                    }
                } else if (rc_fee_cc.length > 1) {
                    for (var i = 0; i < rc_fee_cc.length; i++) {
                        if (rc_fee_cc[i]['low_pay_range'] <= amount && rc_fee_cc[i]['high_pay_range'] >= amount) {
                            fee += parseFloat(rc_fee_cc[i]['convenience_fee']);
                            flat_fee = parseFloat(rc_fee_cc[i]['convenience_fee']);
                            porcent_fee = rc_fee_cc[i]['convenience_fee_float'];
                            if (rc_fee_cc[i]['convenience_fee_float'] > 0) {
                                var ffee = (amount * rc_fee_cc[i]['convenience_fee_float']) / 100;
                                fee = fee + parseFloat(ffee + 0.0001);
                            }
                            if (rc_fee_cc[i]['card_type']) {
                                var cardtype_array = rc_fee_cc[i]['card_type'];
                                for (var j = 0; j < cardtype_array.length; j++) {
                                    if (cardtype_array[j]['type'] === card_type) {
                                        fee = parseFloat(cardtype_array[j]['convenience_fee']);
                                        flat_fee = parseFloat(cardtype_array[j]['convenience_fee']);
                                        porcent_fee = cardtype_array[j]['convenience_fee_float'];
                                        if (cardtype_array[j]['convenience_fee_float'] > 0) {
                                            if (amount > 0) {
                                                var ffee = (amount * cardtype_array[j]['convenience_fee_float']) / 100;
                                            } else {
                                                var ffee = (cardtype_array[j]['convenience_fee_float']) / 100;
                                            }
                                            fee = fee + parseFloat(ffee + 0.0001);
                                        }
                                        break;
                                    }
                                }
                            }
                            break;
                        }
                    }
                }
                //setting eterminal recurring and cc
                if(includes(walkin_recurring,'cc') && fee > 0){
                    $("#walkin_div").show();
                    if($('.etermial_walkin').prop('checked')){
                        fee = 0;
                        $("#xservice_walkin").prop('hidden',false);
                    }else{
                        $("#xservice_walkin").prop('hidden',true);
                    }
                }else{
                    $("#walkin_div").hide();
                    $("#walkin").prop('checked',false);
                }

                if(typeof phonefee_recurring['cc'] === 'undefined'){
                    $("#phonefee_value").val(0);
                    $("#phonefee_div").hide();
                }else{
                    $("#phonefee_div").show();
                    if(phonefee_edited != -1){
                        $("#phonefee_value").val(phonefee_edited);
                    }else{
                        $("#phonefee_value").val(phonefee_recurring['cc']);
                    }
                }
            } else if (type == 'am') {
                if (rc_fee_amex.length == 1) {
                    fee += parseFloat(rc_fee_amex[0]['convenience_fee']);
                    flat_fee = parseFloat(rc_fee_amex[0]['convenience_fee']);
                    porcent_fee = rc_fee_amex[0]['convenience_fee_float'];
                    if (rc_fee_amex[0]['convenience_fee_float'] > 0) {
                        var ffee = (amount * rc_fee_amex[0]['convenience_fee_float']) / 100;
                        fee = fee + parseFloat(ffee + 0.0001);
                    }
                } else if (rc_fee_amex.length > 1) {
                    for (var i = 0; i < rc_fee_amex.length; i++) {
                        if (rc_fee_amex[i]['low_pay_range'] <= amount && rc_fee_amex[i]['high_pay_range'] >= amount) {
                            fee += parseFloat(rc_fee_amex[i]['convenience_fee']);
                            flat_fee = parseFloat(rc_fee_amex[i]['convenience_fee']);
                            porcent_fee = rc_fee_amex[i]['convenience_fee_float'];
                            if (rc_fee_amex[i]['convenience_fee_float'] > 0) {
                                var ffee = (amount * rc_fee_amex[i]['convenience_fee_float']) / 100;
                                fee = fee + parseFloat((ffee + 0.0001));
                            }
                            break;
                        }
                    }
                }
                //setting eterminal recurring and amex
                if(includes(walkin_recurring,'amex') && fee > 0){
                    $("#walkin_div").show();
                    if($('.etermial_walkin').prop('checked')){
                        fee = 0;
                        $("#xservice_walkin").prop('hidden',false);
                    }else{
                        $("#xservice_walkin").prop('hidden',true);
                    }
                }else{
                    $("#walkin_div").hide();
                    $("#walkin").prop('checked',false);
                }

                if(typeof phonefee_recurring['amex'] === 'undefined'){
                    $("#phonefee_value").val(0);
                    $("#phonefee_div").hide();
                }else{
                    $("#phonefee_div").show();
                    if(phonefee_edited != -1){
                        $("#phonefee_value").val(phonefee_edited);
                    }else{
                        $("#phonefee_value").val(phonefee_recurring['amex']);
                    }
                }
            } else if (type == 'swipe') {
                if (typeof card_type_fee === 'undefined' || card_type_fee === null) {
                    // variable is undefined or null
                    var card_type_fee="";
                }
                if (card_type_fee == 'am') {
                    if (rc_fee_amex.length == 1) {
                        fee += parseFloat(rc_fee_amex[0]['convenience_fee']);
                        flat_fee = parseFloat(rc_fee_amex[0]['convenience_fee']);
                        porcent_fee = rc_fee_amex[0]['convenience_fee_float'];
                        if (rc_fee_amex[0]['convenience_fee_float'] > 0) {
                            var ffee = (amount * rc_fee_amex[0]['convenience_fee_float']) / 100;
                            fee = fee + parseFloat(ffee + 0.0001);
                        }
                    } else if (rc_fee_amex.length > 1) {
                        for (var i = 0; i < rc_fee_amex.length; i++) {
                            if (rc_fee_amex[i]['low_pay_range'] <= amount && rc_fee_amex[i]['high_pay_range'] >= amount) {
                                fee += parseFloat(rc_fee_amex[i]['convenience_fee']);
                                flat_fee = parseFloat(rc_fee_amex[i]['convenience_fee']);
                                porcent_fee = rc_fee_amex[i]['convenience_fee_float'];
                                if (rc_fee_amex[i]['convenience_fee_float'] > 0) {
                                    var ffee = (amount * rc_fee_amex[i]['convenience_fee_float']) / 100;
                                    fee = fee + parseFloat((ffee + 0.0001));
                                }
                                break;
                            }
                        }
                    }
                } else if (rc_fee_cc.length == 1) {
                    fee += parseFloat(rc_fee_cc[0]['convenience_fee']);
                    flat_fee = parseFloat(rc_fee_cc[0]['convenience_fee']);
                    porcent_fee = rc_fee_cc[0]['convenience_fee_float'];
                    if (rc_fee_cc[0]['convenience_fee_float'] > 0) {
                        var ffee = (amount * rc_fee_cc[0]['convenience_fee_float']) / 100;
                        fee = fee + parseFloat(ffee + 0.0001);
                    }
                    if (card_type) {
                        if (rc_fee_cc[0]['card_type']) {
                            var cardtype_array = rc_fee_cc[0]['card_type'];
                            for (var i = 0; i < cardtype_array.length; i++) {
                                if (cardtype_array[i]['type'] == card_type) {
                                    fee = parseFloat(cardtype_array[i]['convenience_fee']);
                                    flat_fee = parseFloat(cardtype_array[i]['convenience_fee']);
                                    porcent_fee = cardtype_array[i]['convenience_fee_float'];
                                    if (rc_fee_cc[0]['convenience_fee_float'] > 0) {
                                        var ffee = (amount * cardtype_array[i]['convenience_fee_float']) / 100;
                                        fee = fee + parseFloat(ffee + 0.0001);
                                    }
                                }
                            }
                        }
                    }
                } else if (rc_fee_cc.length > 1) {
                    for (var i = 0; i < rc_fee_cc.length; i++) {
                        if (rc_fee_cc[i]['low_pay_range'] <= amount && rc_fee_cc[i]['high_pay_range'] >= amount) {
                            fee += parseFloat(rc_fee_cc[i]['convenience_fee']);
                            flat_fee = parseFloat(rc_fee_cc[i]['convenience_fee']);
                            porcent_fee = rc_fee_cc[i]['convenience_fee_float'];
                            if (rc_fee_cc[i]['convenience_fee_float'] > 0) {
                                var ffee = (amount * rc_fee_cc[i]['convenience_fee_float']) / 100;
                                fee = fee + parseFloat(ffee + 0.0001);
                            }
                            if (rc_fee_cc[i]['card_type']) {
                                var cardtype_array = rc_fee_cc[i]['card_type'];
                                for (var j = 0; j < cardtype_array.length; j++) {
                                    if (cardtype_array[j]['type'] === card_type) {
                                        fee = parseFloat(cardtype_array[j]['convenience_fee']);
                                        flat_fee = parseFloat(cardtype_array[j]['convenience_fee']);
                                        porcent_fee = cardtype_array[j]['convenience_fee_float'];
                                        if (cardtype_array[j]['convenience_fee_float'] > 0) {
                                            if (amount > 0) {
                                                var ffee = (amount * cardtype_array[j]['convenience_fee_float']) / 100;
                                            } else {
                                                var ffee = (cardtype_array[j]['convenience_fee_float']) / 100;
                                            }
                                            fee = fee + parseFloat(ffee + 0.0001);
                                        }
                                        break;
                                    }
                                }
                            }
                            break;
                        }
                    }
                }
                //setting eterminal  swipe
                if(includes(walkin_one_time,'swipe') && fee > 0){
                    $("#walkin_div").show();
                    if($('.etermial_walkin').prop('checked')){
                        fee = 0;
                        $("#xservice_walkin").prop('hidden',false);
                    }else{
                        $("#xservice_walkin").prop('hidden',true);
                    }
                }else{
                    $("#walkin_div").hide();
                    $("#walkin").prop('checked',false);
                }
            }
        }
    }
    $("#xdiv_paymentamount").show();
    var phonefeevalue = parseFloat($("#phonefee_value").val());
    if(isNaN(phonefeevalue)){
        phonefeevalue = 0;
    }
    var walk_in = $('.etermial_walkin').prop('checked');
    if ($("#inlineRadio1").is(":checked") && isrecurring) {
        $("#byclicking").html("By clicking 'Approve AutoPay', I authorize my Payment Method to be charged the Total Amount and agree to <a href='http://revopay.com/docs/terms-conditions' target='_blank' style='font-size:12px'>Terms and Conditions.</a>'");
    } else {
        $("#byclicking").html("By clicking 'Approve Payment', I authorize my Payment Method to be charged the Total Amount and agree to<a href='http://revopay.com/docs/terms-conditions' target='_blank' style='font-size:12px'>Terms and Conditions.</a>'");
    }
    if (fee == 0 && noapplycfee == 0) {

        $("#xmodalservice_fee").hide();
        $("#xservice_fee").hide();
        $("#xservice_fee2").hide();
        $("#xservice_fee_inv").hide();
        if ($("#inlineRadio1").is(":checked") && isrecurring) {
            $("#xdiv_paymentamount").hide();
            $("#xgrandtotal2").html("Balance Owed");
            $("#xgrandtotal").html("Balance Owed");
            $("#xcphonefee").html("$" + (parseFloat(phonefeevalue)).toFixed(2));
            $("#inputphonefee").val((parseFloat(phonefeevalue)).toFixed(2));
            $("#inputwalkin").val(walk_in);
        } else {
            $("#xgrandtotal_inv").html("$" + (parseFloat(fee) + parseFloat(amount)).toFixed(2));
            $("#xgrandtotal").html("$" + (parseFloat(fee) + parseFloat(amount) + parseFloat(phonefeevalue)).toFixed(2));
            $("#xgrandtotal2").html("$" + (parseFloat(fee) + parseFloat(amount) + parseFloat(phonefeevalue)).toFixed(2));
            $("#xcphonefee").html("$" + (parseFloat(phonefeevalue)).toFixed(2));
            $("#inputphonefee").val((parseFloat(phonefeevalue)).toFixed(2));
            $("#inputwalkin").val(walk_in);
        }

    } else {
        $("#xmodalservice_fee").show();
        $("#xservice_fee").show();
        $("#xservice_fee2").show();
        $("#xservice_fee_inv").show();
        $("#xgrandtotal_inv").html("$" + (parseFloat(fee) + parseFloat(amount)).toFixed(2));
        $("#xgrandtotal").html("$" + (parseFloat(fee.toFixed(2)) + parseFloat(amount.toFixed(2)) + + parseFloat(phonefeevalue.toFixed(2))).toFixed(2));
        $("#xgrandtotal2").html("$" + (parseFloat(fee.toFixed(2)) + parseFloat(amount.toFixed(2))+ parseFloat(phonefeevalue.toFixed(2))).toFixed(2));
        $("#xcphonefee").html("$" + (parseFloat(phonefeevalue)).toFixed(2));
        $("#inputphonefee").val((parseFloat(phonefeevalue)).toFixed(2));
        $("#inputwalkin").val(walk_in);
        if ($("#inlineRadio1").is(":checked") && isrecurring) {
            $("#xdiv_paymentamount").hide();
            if (fee == 0) {
                $("#xmodalservice_fee").hide();
                $("#xservice_fee").hide();
                $("#xservice_fee2").hide();
                $("#xservice_fee_inv").hide();
                $("#xgrandtotal2").html("Balance Owed");
                $("#xgrandtotal").html("Balance Owed");
                $("#xcphonefee").html("$" + (parseFloat(phonefeevalue)).toFixed(2));
                $("#inputphonefee").val((parseFloat(phonefeevalue)).toFixed(2));
                $("#inputwalkin").val(walk_in);
                $("#byclicking").html("By clicking 'Approve AutoPay', I authorize my Payment Method to be charged the Total Amount and agree to <a href='http://revopay.com/docs/terms-conditions' target='_blank' style='font-size:12px'>Terms and Conditions.</a>'");
            } else {
                $("#xservice_fee2").hide();
                if (flat_fee > 0 && porcent_fee > 0) {

                    $("#xcfee2").html("$" + flat_fee.toFixed(2) + " + " + porcent_fee + "%");
                    $("#xcfee").html("$" + flat_fee.toFixed(2) + ' + ' + porcent_fee + "%");
                    feetext = "$" + flat_fee.toFixed(2) + " + " + porcent_fee + "%";
                    $("#xservice_fee2").show();
                    $("#xcphonefee").html("$" + (parseFloat(phonefeevalue)).toFixed(2));
                    $("#xphonefee2").html("$" + (parseFloat(phonefeevalue)).toFixed(2));
                    $("#inputphonefee").val((parseFloat(phonefeevalue)).toFixed(2));
                    $("#inputwalkin").val(walk_in);
                } else if (flat_fee > 0) {
                    $("#xcfee2").html("$" + flat_fee.toFixed(2));
                    $("#xcfee").html("$" + flat_fee.toFixed(2));
                    feetext = "$" + flat_fee.toFixed(2);
                    $("#xservice_fee2").show();
                    $("#xcphonefee").html("$" + (parseFloat(phonefeevalue)).toFixed(2));
                    $("#xphonefee2").html("$" + (parseFloat(phonefeevalue)).toFixed(2));
                    $("#inputphonefee").val((parseFloat(phonefeevalue)).toFixed(2));
                    $("#inputwalkin").val(walk_in);
                } else if (porcent_fee > 0) {
                    $("#xcfee2").html(porcent_fee + "%");
                    $("#xcfee").html(porcent_fee + "%");
                    feetext = porcent_fee + "%";
                    $("#xservice_fee2").show();
                    $("#xcphonefee").html("$" + (parseFloat(phonefeevalue)).toFixed(2));
                    $("#xphonefee2").html("$" + (parseFloat(phonefeevalue)).toFixed(2));
                    $("#inputphonefee").val((parseFloat(phonefeevalue)).toFixed(2));
                    $("#inputwalkin").val(walk_in);
                }
                $("#xhr").hide();
                $("#xgrandtotal2").html("Balance Owed");
                $("#xgrandtotal").html("Balance Owed");
                $("#xcphonefee").html("$" + (parseFloat(phonefeevalue)).toFixed(2));
                $("#xphonefee2").html("$" + (parseFloat(phonefeevalue)).toFixed(2));
                $("#inputphonefee").val((parseFloat(phonefeevalue)).toFixed(2));
                $("#inputwalkin").val(walk_in);
                $("#byclicking").html("By clicking 'Approve AutoPay', I authorize my Payment Method to be charged the Total Amount owed plus the " + feetext + " convenience fee and agree to <a href='http://revopay.com/docs/terms-conditions' target='_blank' style='font-size:12px'>Terms and Conditions.</a>'");

            }
        } else {
            if (noapplycfee == 1) {
                $("#xcphonefee").html("$" + (parseFloat(phonefeevalue)).toFixed(2));
                $("#xphonefee2").html("$" + (parseFloat(phonefeevalue)).toFixed(2));
                $("#inputphonefee").val((parseFloat(phonefeevalue)).toFixed(2));
                $("#inputwalkin").val(walk_in);
                $("#xcfee2").html("$" + parseFloat(fee.toFixed(2)).toFixed(2));
                $("#xcfee").html('<div class="col-xs-6"> </div><div class="input-group col-xs-6"><span class="input-group-addon">$</span><input type="text" id="inputcfee" class="form-control text-right input-active" onchange = "CalculateAmountEterm()" value="' + parseFloat(fee.toFixed(2)).toFixed(2) + '"></div>');
            } else {
                $("#xcphonefee").html("$" + (parseFloat(phonefeevalue)).toFixed(2));
                $("#xphonefee2").html("$" + (parseFloat(phonefeevalue)).toFixed(2));
                $("#inputphonefee").val((parseFloat(phonefeevalue)).toFixed(2));
                $("#inputwalkin").val(walk_in);
                $("#xcfee2").html("$" + parseFloat(fee.toFixed(2)).toFixed(2));
                $("#xcfee").html("$" + parseFloat(fee.toFixed(2)).toFixed(2));
            }
            $("#xcphonefee").html("$" + (parseFloat(phonefeevalue)).toFixed(2));
            $("#xphonefee2").html("$" + (parseFloat(phonefeevalue)).toFixed(2));
            $("#inputphonefee").val((parseFloat(phonefeevalue)).toFixed(2));
            $("#inputwalkin").val(walk_in);
            $("#xcfee_inv").html("$" + parseFloat(fee.toFixed(2)).toFixed(2));
            $("#xgrandtotal2").html("$" + (parseFloat(fee.toFixed(2)) + parseFloat(amount.toFixed(2)) + parseFloat(phonefeevalue.toFixed(2))).toFixed(2));

        }
    }
    if (!$("#payinv").is(":checked")) {
        drawCatPopup();
    }
    phonefee_edited = -1;
}

function CalculateAmountEterm() {
    var etermfee = $("#inputcfee").val();
    var etermamount = $("#xprevtotal").text().replace("$", "");
    if (etype == "inv") {
        etermamount = $("#xinvoice_amount").val();
    }
    $("#xgrandtotal").html("$" + (parseFloat(etermfee) + parseFloat(etermamount)).toFixed(2));
    $("#xcfee2").html("$" + parseFloat(etermfee).toFixed(2));
    $("#xgrandtotal2").html("$" + (parseFloat(etermfee) + parseFloat(etermamount)).toFixed(2));
}

function preparePopInv() {
    //resetParentCounters();
    //CalculateINVAmount();
    //$("#xshowcat_pay").html(paysummary);
    $("#xlabelhiderecurring").hide();
    $("#xlabelrecurring").html("Make a payment on:");
    $("#xlabelstartdate").html("<b>Date</b>");
    $("#myModal_Sapprove").modal();

}

function preparePopUp() {
    if (isrecurring) {
        $("#xlabelhiderecurring").show();
        $("#xlabelrecurring").html("Scheduled Recurring Payments:");
        $("#xlabelstartdate").html("<b>Start Date:</b>");
    } else {
        $("#xlabelhiderecurring").hide();
        $("#xlabelrecurring").html("Make a payment on:");
        $("#xlabelstartdate").html("<b>Date</b>");
    }
    if ($('checkbox4'))
        $("#myModal_Sapprove").modal();
}

function PrepareToCalculate() {
    if ($("#checkbox4").is(":checked")) {
        CalculaFeeX(1);
    } else if ($("#checkbox5").is(":checked")) {
        CalculaFeeX(2);
    } else if ($("#checkbox6").is(":checked")) {
        CalculaFeeX(5);
    } else {
        CalculaFeeX(3);
    }
}

function CalculateINVAmount() {

    var amount = $("#xinvoice_amount").val();
    if (isNaN(amount)) {
        amount = 0;
    }
    if (amount > inv_amount) {
        amount = inv_amount;
        $("#xinvoice_amount").val(amount);
    }
    if (type == "") {

    } else {
        if (typeof card_type_fee === 'undefined' || card_type_fee === null) {
                    // variable is undefined or null
                    var card_type_fee="";
               }
        if ($("#checkbox4").is(":checked"))
            card_type_fee = "";
        eterm_calculateFee(type, amount, card_type_fee);
    }
    xamount = amount;
    $("#xprevtotal2").html("$" + parseFloat(amount).toFixed(2));


}

function showconvFee() {
    ChangeIcon();
    var select_date = currentDate.getFullYear() + "-" + (currentDate.getMonth() + 1) + "-" + currentDate.getDate();
    var onetimefuture = 0;
    if (select_date > xnowday) {
        var onetimefuture = 1;
    }
    card_type_fee = "";
    if (!isrecurring) {
        if ($("#xselect_paymethod_0").is(":checked")) {
            var select_val = $("#xprof_0").val();
            var spl_select = select_val.split("_");
            type = spl_select[0];
            profile = spl_select[1];
            card_type_fee = spl_select[2];
        }
    } else {
        if ($("#xselect_paymethod_1").is(":checked")) {
            var select_val = $("#xprof_1").val();
            var spl_select = select_val.split("_");
            type = spl_select[0];
            profile = spl_select[1];
            card_type_fee = spl_select[2];
        }
    }

    var xshowmodalfee = 0;
    if (isrecurring || onetimefuture) {
        $("#xcfee_ot").hide();
        $("#xcfee_rc").show();
        checkPType(2);
        if (rc_fee_ec.length > 0) {
            for (var i = 0; i < rc_fee_ec.length; i++) {
                if (parseFloat(rc_fee_ec[i]["convenience_fee"]) > 0 || parseFloat(rc_fee_ec[i]["convenience_fee_float"]) > 0) {
                    xshowmodalfee = 1;
                    break;
                }
            }
        }
        if (rc_fee_cc.length > 0 && xshowmodalfee == 0) {
            for (var i = 0; i < rc_fee_cc.length; i++) {
                if (parseFloat(rc_fee_cc[i]["convenience_fee"]) > 0 || parseFloat(rc_fee_cc[i]["convenience_fee_float"]) > 0) {
                    xshowmodalfee = 1;
                    break;
                }
            }
        }
        if (rc_fee_amex.length > 0 && xshowmodalfee == 0) {
            for (var i = 0; i < rc_fee_amex.length; i++) {
                if (parseFloat(rc_fee_amex[i]["convenience_fee"]) > 0 || parseFloat(rc_fee_amex[i]["convenience_fee_float"]) > 0) {
                    xshowmodalfee = 1;
                    break;
                }
            }
        }

    } else {
        $("#xcfee_ot").show();
        $("#xcfee_rc").hide();
        checkPType(1);
        if (ot_fee_ec.length > 0) {
            for (var i = 0; i < ot_fee_ec.length; i++) {
                if (parseFloat(ot_fee_ec[i]["convenience_fee"]) > 0 || parseFloat(ot_fee_ec[i]["convenience_fee_float"]) > 0) {
                    xshowmodalfee = 1;
                    break;
                }
            }
        }
        if (ot_fee_cc.length > 0 && xshowmodalfee == 0) {
            for (var i = 0; i < ot_fee_cc.length; i++) {
                if (parseFloat(ot_fee_cc[i]["convenience_fee"]) > 0 || parseFloat(ot_fee_cc[i]["convenience_fee_float"]) > 0) {
                    xshowmodalfee = 1;
                    break;
                }
            }
        }
        if (ot_fee_amex.length > 0 && xshowmodalfee == 0) {
            for (var i = 0; i < ot_fee_amex.length; i++) {
                if (parseFloat(ot_fee_amex[i]["convenience_fee"]) > 0 || parseFloat(ot_fee_amex[i]["convenience_fee_float"]) > 0) {
                    xshowmodalfee = 1;
                    break;
                }
            }
        }
        if (ot_fee_swipe.length > 0 && xshowmodalfee == 0) {
            for (var i = 0; i < ot_fee_swipe.length; i++) {
                if (parseFloat(ot_fee_swipe[i]["convenience_fee"]) > 0 || parseFloat(ot_fee_swipe[i]["convenience_fee_float"]) > 0) {
                    xshowmodalfee = 1;
                    break;
                }
            }
        }
    }

    xvalidateCredentials();
    eterm_calculateFee(type, xamount, card_type_fee);
}

function CalculateEtermAmount() {
    var tmp_obj;
    var amount = 0;
    var tmp;

    for (var i = 0; tmp_obj = document.getElementById("xcheckpay_" + i); i++) {
        if (tmp_obj.checked) {
            tmp_obj = document.getElementById("xinputpay_" + i);
            tmp_dd_obj = document.getElementById("qtypay_" + i);
            tmp_dd_obj_val = 1;
            if (tmp_dd_obj) {
                tmp_dd_obj_val = tmp_dd_obj.value;
            }
            tmp = tmp_obj.value.replace(/,/g, "");
            tmp = parseFloat(tmp);
            if (!isNaN(tmp) && tmp > 0) {
                amount += tmp * tmp_dd_obj_val;
                tmp_obj.value = tmp.toFixed(2);
            }
        }
    }
    amount = parseFloat(amount).toFixed(2);

    $("#xprevtotal").html("$" + amount);
    $("#xprevtotal2").html("$" + amount);
    xamount = amount;

    eterm_calculateFee(type, xamount, card_type_fee);
    return amount;
}

function CalculateEtermAmountCat() {
    var tmp_obj;
    var amount = 0;
    var tmp;

    for (var i = 0; tmp_obj = document.getElementById("xcheckpay_" + i); i++) {
        if (tmp_obj.checked) {
            tmp_obj = document.getElementById("xinputpay_" + i);
            tmp_dd_obj = document.getElementById("qtypay_" + i);
            tmp_dd_obj_val = 1;
            if (tmp_dd_obj) {
                tmp_dd_obj_val = tmp_dd_obj.value;
            }
            tmp = tmp_obj.value.replace(/,/g, "");
            tmp = parseFloat(tmp);
            if (!isNaN(tmp) && tmp > 0) {
                amount += tmp * tmp_dd_obj_val;
                tmp_obj.value = tmp.toFixed(2);
            }
        }
    }
    amount = parseFloat(amount).toFixed(2);

    $("#xprevtotal").html("$" + amount);
    $("#xprevtotal2").html("$" + amount);
    xamount = amount;

    if ($('#inputcfee').length) {
        var xcfee = $("#inputcfee").val();
        xcontent.xcfee = xcfee;
    } else {
        eterm_calculateFee(type, xamount, card_type_fee);
    }
    return amount;
}

function changetext(text_type) {
    if (text_type == 1) {
        $("#xtextlabel").html('<h4 class="no-margin"><p><b class="small-font-2">Select Payment Date:</b></p></h4>');

    } else {
        $("#xtextlabel").html('<h4 class="no-margin"><p><b class="small-font-2">Select Start Date:</b></p></h4>');
    }
    showconvFee();

}

function CalculaFeeX(opc) {
    profile = 0;
    var select_date = currentDate.getFullYear() + "-" + (currentDate.getMonth() + 1) + "-" + currentDate.getDate();
    var onetimefuture = 0;
    if (select_date > xnowday) {
        var onetimefuture = 1;
    }

    type = "";
    if (opc == 1) {
        if ($("#checkbox4").is(":checked")) {
            type = "ec";
            profile = 0;
            $("#xsaprofile").show();

            if (!isrecurring && !onetimefuture){
                if(walkin_one_time.includes('ec')){
                    $("#walkin_div").show();
                }else{
                    $("#walkin_div").hide();
                    $("#walkin").prop('checked',false);
                }
                if(typeof phonefee_one_time['ec'] === 'undefined'){
                    $("#phonefee_value").val(0);
                    $("#phonefee_div").hide();
                }else{
                    $("#phonefee_div").show();
                    $("#phonefee_value").val(phonefee_one_time['ec']);
                }
            }else{
                if(walkin_recurring.includes('ec')){
                    $("#walkin_div").show();
                }else{
                    $("#walkin_div").hide();
                    $("#walkin").prop('checked',false);
                }
                if(typeof phonefee_recurring['ec'] === 'undefined'){
                    $("#phonefee_value").val(0);
                    $("#phonefee_div").hide();
                }else{
                    $("#phonefee_div").show();
                    $("#phonefee_value").val(phonefee_recurring['ec']);
                }
            }
        }else {
            $("#walkin_div").hide();
            $("#walkin").prop('checked',false);
            $("#phonefee_value").val(0);
            $("#phonefee_div").hide();
        }

    } else if (opc == 5) {
        if ($("#checkbox6").is(":checked")) {
            type = "swipe";
            profile = 0;
            $("#xsaveprofile_id").attr("checked", false);
            $("#xsaprofile").hide();

            if(walkin_one_time.includes('swipe')){
                $("#walkin_div").show();
                $("#phonefee_value").val(0);
                $("#phonefee_div").hide();
            }
        }else {
            $("#walkin_div").hide();
            $("#walkin").prop('checked',false);
            $("#phonefee_value").val(0);
            $("#phonefee_div").hide();
        }


    } else if (opc == 2) {
        if ($("#checkbox5").is(":checked")) {
            $("#xsaprofile").show();
            type = "am";
            if ($("#xcardnumber").val() == "" || $("#xcardnumber").val().substr(0, 1) != 3) {
                type = "cc";
            }
            if ($("#xcardnumber").val() == "") {
                card_type_fee = "";
            }

            profile = 0;

            if (!isrecurring && !onetimefuture){
                if(type == 'am'){
                    if(walkin_one_time.includes('amex')){
                        $("#walkin_div").show();
                    }else{
                        $("#walkin_div").hide();
                        $("#walkin").prop('checked',false);
                    }
                    if(typeof phonefee_one_time['amex'] === 'undefined'){
                        $("#phonefee_value").val(0);
                        $("#phonefee_div").hide();
                    }else{
                        $("#phonefee_div").show();
                        $("#phonefee_value").val(phonefee_one_time['amex']);
                    }
                }else{
                    if(walkin_one_time.includes('cc')){
                        $("#walkin_div").show();
                    }else{
                        $("#walkin_div").hide();
                        $("#walkin").prop('checked',false);
                    }
                    if(typeof phonefee_one_time['cc'] === 'undefined'){
                        $("#phonefee_value").val(0);
                        $("#phonefee_div").hide();
                    }else{
                        $("#phonefee_div").show();
                        $("#phonefee_value").val(phonefee_one_time['cc']);
                    }
                }
            }else{
                if(type == 'am'){
                    if(walkin_recurring.includes('amex')){
                        $("#walkin_div").show();
                    }else{
                        $("#walkin_div").hide();
                        $("#walkin").prop('checked',false);
                    }
                    if(typeof phonefee_recurring['amex'] === 'undefined'){
                        $("#phonefee_value").val(0);
                        $("#phonefee_div").hide();
                    }else{
                        $("#phonefee_div").show();
                        $("#phonefee_value").val(phonefee_recurring['amex']);
                    }
                }else{
                    if(walkin_recurring.includes('cc')){
                        $("#walkin_div").show();
                    }else{
                        $("#walkin_div").hide();
                        $("#walkin").prop('checked',false);
                    }
                    if(typeof phonefee_recurring['cc'] === 'undefined'){
                        $("#phonefee_value").val(0);
                        $("#phonefee_div").hide();
                    }else{
                        $("#phonefee_div").show();
                        $("#phonefee_value").val(phonefee_recurring['cc']);
                    }
                }
            }
        }else {
            $("#walkin_div").hide();
            $("#walkin").prop('checked',false);
            $("#phonefee_value").val(0);
            $("#phonefee_div").hide();
        }



    } else if (opc == 3) {
        $("#xsaveprofile_id").attr("checked", false);
        $("#walkin_div").hide();
        $("#walkin").prop('checked',false);
        $("#xsaprofile").show();
        ChangeIcon();
        $("#checkbox4").attr("checked", false);
        $("#checkbox5").attr("checked", false);
        $("#checkbox6").attr("checked", false);
        $('#collapse1').collapse('hide');
        $('#collapse2').collapse('hide');
        $('#collapse5').collapse('hide');
        if (!isrecurring && !onetimefuture) {
            if ($("#xselect_paymethod_0").is(":checked")) {
                var select_val = $("#xprof_0").val();
                var spl_select = select_val.split("_");
                type = spl_select[0];
                profile = spl_select[1];
                card_type_fee = spl_select[2];
                if(type == 'ec'){
                    if(walkin_one_time.includes('ec')){
                        $("#walkin_div").show();
                    }else{
                        $("#walkin_div").hide();
                        $("#walkin").prop('checked',false);
                    }
                    if(typeof phonefee_one_time['ec'] === 'undefined'){
                        $("#phonefee_value").val(0);
                        $("#phonefee_div").hide();
                    }else{
                        $("#phonefee_div").show();
                        $("#phonefee_value").val(phonefee_one_time['ec']);
                    }
                }else if(type == 'cc'){
                    if(walkin_one_time.includes('cc')){
                        $("#walkin_div").show();
                    }else{
                        $("#walkin_div").hide();
                        $("#walkin").prop('checked',false);
                    }
                    if(typeof phonefee_one_time['cc'] === 'undefined'){
                        $("#phonefee_value").val(0);
                        $("#phonefee_div").hide();
                    }else{
                        $("#phonefee_div").show();
                        $("#phonefee_value").val(phonefee_one_time['cc']);
                    }
                }else if(type == 'am'){
                    if(walkin_one_time.includes('amex')){
                        $("#walkin_div").show();
                    }else{
                        $("#walkin_div").hide();
                        $("#walkin").prop('checked',false);
                    }
                    if(typeof phonefee_one_time['amex'] === 'undefined'){
                        $("#phonefee_value").val(0);
                        $("#phonefee_div").hide();
                    }else{
                        $("#phonefee_div").show();
                        $("#phonefee_value").val(phonefee_one_time['amex']);
                    }
                }
            }else {
                $("#walkin_div").hide();
                $("#walkin").prop('checked',false);
                $("#phonefee_value").val(0);
                $("#phonefee_div").hide();
            }
        } else {
            if ($("#xselect_paymethod_1").is(":checked")) {
                var select_val = $("#xprof_1").val();
                var spl_select = select_val.split("_");
                type = spl_select[0];
                profile = spl_select[1];
                card_type_fee = spl_select[2];

                if(type == 'ec'){
                    if(walkin_recurring.includes('ec')){
                        $("#walkin_div").show();
                    }else{
                        $("#walkin_div").hide();
                        $("#walkin").prop('checked',false);
                    }
                    if(typeof phonefee_recurring['ec'] === 'undefined'){
                        $("#phonefee_value").val(0);
                        $("#phonefee_div").hide();
                    }else{
                        $("#phonefee_div").show();
                        $("#phonefee_value").val(phonefee_recurring['ec']);
                    }
                }else if(type == 'cc'){
                    if(walkin_recurring.includes('cc')){
                        $("#walkin_div").show();
                    }else{
                        $("#walkin_div").hide();
                        $("#walkin").prop('checked',false);
                    }
                    if(typeof phonefee_recurring['cc'] === 'undefined'){
                        $("#phonefee_value").val(0);
                        $("#phonefee_div").hide();
                    }else{
                        $("#phonefee_div").show();
                        $("#phonefee_value").val(phonefee_recurring['cc']);
                    }
                }else if(type == 'am'){
                    if(walkin_recurring.includes('amex')){
                        $("#walkin_div").show();
                    }else{
                        $("#walkin_div").hide();
                        $("#walkin").prop('checked',false);
                    }
                    if(typeof phonefee_recurring['amex'] === 'undefined'){
                        $("#phonefee_value").val(0);
                        $("#phonefee_div").hide();
                    }else{
                        $("#phonefee_div").show();
                        $("#phonefee_value").val(phonefee_recurring['amex']);
                    }
                }
            }else {
                $("#walkin_div").hide();
                $("#walkin").prop('checked',false);
                $("#phonefee_value").val(0);
                $("#phonefee_div").hide();
            }
        }

    }

    if (etype == "inv") {
        if ($("#xcardnumber").val() == "" && $("#strackdata").val() == "") {
            card_type_fee = "";
        }
        CalculateINVAmount();
    } else {
        CalculateEtermAmount();
    }

}

$('#inlineRadio1').click(function () {
    if (!$(this).attr('checked')) {
        $(this).prop('checked', true);
        $('#payment-details').collapse('hide');
    }
    $('#inlineRadio2').attr('checked', false);
    refreshRecurringDate();
    eterm_calculateFee(type, xamount, card_type_fee);

});

$('#inlineRadio2').click(function () {
    if (!$(this).attr('checked')) {
        $(this).attr('checked', true);
        $('#payment-details').collapse('show');
    }
    $('#inlineRadio1').attr('checked', false);
    refreshRecurringDate();
    eterm_calculateFee(type, xamount, card_type_fee);
});

function drawCatPopup() {
    $("#xshowcat_pay").show();
    $("#xdiv_paymentamount2").show();
    $("#xhideonrecurring").show();
    var popup_freq = $("#xfreq option:selected").text();
    $("#xpop_freq").html(popup_freq);
    var popup_edate = $("#xrenddate option:selected").text();
    $("#xpop_enddate").html(popup_edate);

    var result = "";
    for (var i = 0; tmp_obj = document.getElementById("xcheckpay_" + i); i++) {
        if (tmp_obj.checked) {
            tmp_obj = document.getElementById("xinputpay_" + i);
            tmp = tmp_obj.value.replace(/,/g, "");
            qty_obj = document.getElementById("qtypay_" + i);


            if (qty_obj == null) {
                qty = 1;
            } else {
                qty = qty_obj.value;
                qty = qty.replace("x", "");
            }
            tmp = parseFloat(tmp);
            if (!isNaN(tmp) && tmp > 0) {
                var xname = $("#xinputpay_" + i).attr("xname");
                var xid = tmp_obj.name;
                var xam = parseFloat($("#xinputpay_" + i).val()).toFixed(2);
                result += '<div class="col-xs-6"><b>' + xname + '</b></div><div class="col-xs-6 text-right">' + qty + ' x $' + xam + '</div>';
            }

        }
    }
    $("#xshowcat_pay").html(result);
    if ($("#inlineRadio1").is(':checked') && isrecurring) {
        $("#xdiv_paymentamount2").hide();
        $("#xhideonrecurring").hide();
        $("#xshowcat_pay").hide();
    }
}

function validateVelocity() {
    var select_date = currentDate.getFullYear() + "-" + (currentDate.getMonth() + 1) + "-" + currentDate.getDate();
    var onetimefuture = 0;
    if (select_date > xnowday) {
        var onetimefuture = 1;
    }
    var amount = xamount;

    if (!($("#inlineRadio1").is(':checked') && isrecurring)) {
        switch (type) {
            case 'ec':
                if (isrecurring || onetimefuture) {
                    if (amount < parseFloat(rc_velocity_ec['low_pay_range'])) {
                        $("#xpopupheader").html("Information");
                        $("#xpopupcontent").html("The minimum payment amount is $" + rc_velocity_ec['low_pay_range']);
                        $("#myModal_success").modal();
                        return false;
                    }
                    if (amount > parseFloat(rc_velocity_ec['high_pay_range'])) {
                        $("#xpopupheader").html("Information");
                        $("#xpopupcontent").html("Maximum amount to pay is $" + rc_velocity_ec['high_pay_range']);
                        $("#myModal_success").modal();
                        return false;
                    }
                } else {
                    //validation to walkin payments
                    if (otwalkin_velocity_ec != "") {
                        if (amount < parseFloat(otwalkin_velocity_ec['low_pay_range'])) {
                            $("#xpopupheader").html("Information");
                            $("#xpopupcontent").html("The minimum payment amount is $" + otwalkin_velocity_ec['low_pay_range']);
                            $("#myModal_success").modal();
                            return false;
                        }
                        if (amount > parseFloat(otwalkin_velocity_ec['high_pay_range'])) {
                            $("#xpopupheader").html("Information");
                            $("#xpopupcontent").html("Maximum amount to pay is $" + otwalkin_velocity_ec['high_pay_range']);
                            $("#myModal_success").modal();
                            return false;
                        }

                    } else {
                        if (amount < parseFloat(ot_velocity_ec['low_pay_range'])) {
                            $("#xpopupheader").html("Information");
                            $("#xpopupcontent").html("The minimum payment amount is $" + ot_velocity_ec['low_pay_range']);
                            $("#myModal_success").modal();
                            return false;
                        }
                        if (amount > parseFloat(ot_velocity_ec['high_pay_range'])) {
                            $("#xpopupheader").html("Information");
                            $("#xpopupcontent").html("Maximum amount to pay is $" + ot_velocity_ec['high_pay_range']);
                            $("#myModal_success").modal();
                            return false;
                        }
                    }
                }
                break;
            case 'cc':
                if (isrecurring || onetimefuture) {
                    if (amount < parseFloat(rc_velocity_cc['low_pay_range'])) {
                        $("#xpopupheader").html("Information");
                        $("#xpopupcontent").html("The minimum payment amount is $" + rc_velocity_cc['low_pay_range']);
                        $("#myModal_success").modal();
                        return false;
                    }
                    if (amount > parseFloat(rc_velocity_cc['high_pay_range'])) {
                        $("#xpopupheader").html("Information");
                        $("#xpopupcontent").html("Maximum amount to pay is $" + rc_velocity_cc['high_pay_range']);
                        $("#myModal_success").modal();
                        return false;
                    }
                } else {
                    if (otwalkin_velocity_cc != "") {
                        if (amount < parseFloat(otwalkin_velocity_cc['low_pay_range'])) {
                            $("#xpopupheader").html("Information");
                            $("#xpopupcontent").html("The minimum payment amount is $" + otwalkin_velocity_cc['low_pay_range']);
                            $("#myModal_success").modal();
                            return false;
                        }
                        if (amount > parseFloat(otwalkin_velocity_cc['high_pay_range'])) {
                            $("#xpopupheader").html("Information");
                            $("#xpopupcontent").html("Maximum amount to pay is $" + otwalkin_velocity_cc['high_pay_range']);
                            $("#myModal_success").modal();
                            return false;
                        }

                    } else {
                        if (amount < parseFloat(ot_velocity_cc['low_pay_range'])) {
                            $("#xpopupheader").html("Information");
                            $("#xpopupcontent").html("The minimum payment amount is $" + ot_velocity_cc['low_pay_range']);
                            $("#myModal_success").modal();
                            return false;
                        }
                        if (amount > parseFloat(ot_velocity_cc['high_pay_range'])) {
                            $("#xpopupheader").html("Information");
                            $("#xpopupcontent").html("Maximum amount to pay is $" + ot_velocity_cc['high_pay_range']);
                            $("#myModal_success").modal();
                            return false;
                        }
                    }
                }
                break;
            case 'swipe':
                if (isrecurring || onetimefuture) {
                    if (amount < parseFloat(rc_velocity_swipe['low_pay_range'])) {
                        $("#xpopupheader").html("Information");
                        $("#xpopupcontent").html("The minimum payment amount is $" + rc_velocity_swipe['low_pay_range']);
                        $("#myModal_success").modal();
                        return false;
                    }
                    if (amount > parseFloat(rc_velocity_swipe['high_pay_range'])) {
                        $("#xpopupheader").html("Information");
                        $("#xpopupcontent").html("Maximum amount to pay is $" + rc_velocity_swipe['high_pay_range']);
                        $("#myModal_success").modal();
                        return false;
                    }
                } else {
                    if (otwalkin_velocity_swipe != "") {
                        if (amount < parseFloat(otwalkin_velocity_swipe['low_pay_range'])) {
                            $("#xpopupheader").html("Information");
                            $("#xpopupcontent").html("The minimum payment amount is $" + otwalkin_velocity_swipe['low_pay_range']);
                            $("#myModal_success").modal();
                            return false;
                        }
                        if (amount > parseFloat(otwalkin_velocity_swipe['high_pay_range'])) {
                            $("#xpopupheader").html("Information");
                            $("#xpopupcontent").html("Maximum amount to pay is $" + otwalkin_velocity_swipe['high_pay_range']);
                            $("#myModal_success").modal();
                            return false;
                        }

                    } else {
                        if (amount < parseFloat(ot_velocity_swipe['low_pay_range'])) {
                            $("#xpopupheader").html("Information");
                            $("#xpopupcontent").html("The minimum payment amount is $" + ot_velocity_swipe['low_pay_range']);
                            $("#myModal_success").modal();
                            return false;
                        }
                        if (amount > parseFloat(ot_velocity_swipe['high_pay_range'])) {
                            $("#xpopupheader").html("Information");
                            $("#xpopupcontent").html("Maximum amount to pay is $" + ot_velocity_swipe['high_pay_range']);
                            $("#myModal_success").modal();
                            return false;
                        }
                    }
                }
                break;
            case 'amex':
            default:
                if (isrecurring || onetimefuture) {
                    if (amount < parseFloat(rc_velocity_amex['low_pay_range'])) {
                        $("#xpopupheader").html("Information");
                        $("#xpopupcontent").html("The minimum payment amount is $" + rc_velocity_amex['low_pay_range']);
                        $("#myModal_success").modal();
                        return false;
                    }
                    if (amount > parseFloat(rc_velocity_amex['high_pay_range'])) {
                        $("#xpopupheader").html("Information");
                        $("#xpopupcontent").html("Maximum amount to pay is $" + rc_velocity_amex['high_pay_range']);
                        $("#myModal_success").modal();
                        return false;
                    }
                } else {

                    if (otwalkin_velocity_amex != "") {
                        if (amount < parseFloat(otwalkin_velocity_amex['low_pay_range'])) {
                            $("#xpopupheader").html("Information");
                            $("#xpopupcontent").html("The minimum payment amount is $" + otwalkin_velocity_amex['low_pay_range']);
                            $("#myModal_success").modal();
                            return false;
                        }
                        if (amount > parseFloat(otwalkin_velocity_amex['high_pay_range'])) {
                            $("#xpopupheader").html("Information");
                            $("#xpopupcontent").html("Maximum amount to pay is $" + otwalkin_velocity_amex['high_pay_range']);
                            $("#myModal_success").modal();
                            return false;
                        }

                    } else {
                        if (amount < parseFloat(ot_velocity_amex['low_pay_range'])) {
                            $("#xpopupheader").html("Information");
                            $("#xpopupcontent").html("The minimum payment amount is $" + ot_velocity_amex['low_pay_range']);
                            $("#myModal_success").modal();
                            return false;
                        }
                        if (amount > parseFloat(ot_velocity_amex['high_pay_range'])) {
                            $("#xpopupheader").html("Information");
                            $("#xpopupcontent").html("Maximum amount to pay is $" + ot_velocity_amex['high_pay_range']);
                            $("#myModal_success").modal();
                            return false;
                        }
                    }
                }
                break;
        }
    }

    return true;
}

function xvalidateCredentials() {
    $("#xpaymeth_ec").show();
    $("#xpaymeth_cc").show();
    $("#xpaymeth_hr").show();
    $("#xprof_div_0").show();
    $("#xprof_div_1").show();

    if (etype == 'inv') {
        $("#xprof_div_1").hide();
        if (ot_velocity_ec.length == "") {
            $("#xpaymeth_ec").hide();
        }

        if (ot_velocity_cc.length == "" && ot_velocity_amex.length == "") {
            $("#xpaymeth_cc").hide();
        }

        if (ot_velocity_ec.length == "" || (ot_velocity_cc.length == "" && ot_velocity_amex.length == "")) {
            $("#xpaymeth_hr").hide();
        }
    } else {
        //onetime or recurring
        var select_date = currentDate.getFullYear() + "-" + (currentDate.getMonth() + 1) + "-" + currentDate.getDate();
        var onetimefuture = 0;
        if (select_date > xnowday) {
            var onetimefuture = 1;
        }

        if (onetimefuture || isrecurring) {
            $("#xprof_div_0").hide();
            if (rc_velocity_ec.length == "") {
                $("#xpaymeth_ec").hide();
            }

            if (rc_velocity_cc.length == "" && rc_velocity_amex.length == "") {
                $("#xpaymeth_cc").hide();
            }

            if (rc_velocity_ec.length == "" || (rc_velocity_cc.length == "" && rc_velocity_amex.length == "")) {
                $("#xpaymeth_hr").hide();
            }
        } else {
            $("#xprof_div_1").hide();
            if (ot_velocity_ec.length == "") {
                $("#xpaymeth_ec").hide();
            }
            if (ot_velocity_cc.length == "" && ot_velocity_amex.length == "") {
                $("#xpaymeth_cc").hide();
            }

            if (ot_velocity_ec.length == "" || (ot_velocity_cc.length == "" && ot_velocity_amex.length == "")) {
                $("#xpaymeth_hr").hide();
            }

        }


    }

}

$('.collapse-action3').click(function () {

    $('#collapse4').collapse('hide');
    $('#payinv').attr('checked', false);
    $("#step3a").hide();
    $("#step3b").show();
});

$('.collapse-action4').click(function () {
    $('#collapse3').collapse('hide');
    $('#paycustomer').attr('checked', false);
    $("#step3b").hide();
    $("#step3a").show();

});

function makepay() {
    //resetParentCounters();
    saveValue = false;

    if ($("#xsaveprofile_id").is(":checked")) {
        saveValue = true;
    }

    $("#myModal_Sapprove").modal("hide");
    $('#loading_layer').fadeIn();

    if (etype == 'inv') {
        var invoice_number = $("#xeinv").val();
        var temp_type = type;
        if (parseInt(profile) > 0)
            temp_type = "prf";

        var xturl = "/etermpay/" + xatoken + "/" + temp_type + "/";
        //var xturl = xturl_generated.replace('/token/','/'+ xatoken +'/');
        //xturl = xturl.replace('/temp_type','/'+ temp_type+'/' );

        var xcontent = {'source': 'eterm'};
        if ($("#xautomaticpayinv").is(':checked')) {
            xcontent.autopayinv = true;
        } else
            xcontent.autopayinv = false;

        if (profile > 0) {
            xcontent.profile_id = profile;
        } else if ($("#checkbox4").is(":checked")) {
            //ec
            xcontent.ec_account_holder = $("#xppec_name").val();
            xcontent.ec_account_lholder = '';
            xcontent.ec_routing_number = $("#xppec_routing").val();
            xcontent.ec_account_number = $("#xppec_acc").val();
            xcontent.ec_checking_savings = $("#xppec_type").val();

        } else if ($("#checkbox5").is(":checked")) {
            //cc
            xcontent.ccname = $("#xcardname").val();
            xcontent.ccnumber = $("#xcardnumber").val();
            xcontent.ccexp = $("#xexpdate").val();
            xcontent.cvv = $("#xcvv").val();
            xcontent.zip = $("#xzip1").val();

        } else if ($("#checkbox6").is(":checked")) {
            //not sure swipe using in inv or not :AJ - Accepted Payment
            //swipe
            var str_cc = $("#strackdata").val();
            var xcardname = getName(str_cc);
            var xcardnumber = getcc(str_cc);
            var xexpdate = getExpYYMMTrack2(str_cc);
            var xcvv = "";
            var xzip = "";
            var track2 = gettrack2(false, str_cc);
            xcontent.ccname = xcardname;
            xcontent.ccnumber = xcardnumber;
            xcontent.ccexp = xexpdate;
            xcontent.cvv = "";
            xcontent.zip = "";
            xcontent.Track2Data = track2;

        } else
            return false;

        if (profile == 0) {
            xcontent.saveprofile = saveValue;
        }

        xcontent.memo = "";
        var s = $("#xmemo").val();

        if ($('#inputcfee').length) {
            var xcfee = $("#inputcfee").val();
            xcontent.xcfee = xcfee;
        }

        if ($('#etermpaymenttype').length) {
            if ($('#etermpaymenttype').val() > "0") {
                xcontent.service = $('#etermpaymenttype').val();
            }
        }

        if (typeof s != 'undefined' && s != "") {
            s = s.replace("#", "");
            s = s.replace('/', "");
            s = s.replace('$', "");
            s = s.replace('\\', "");
            s = s.replace('%', "");
            xcontent.memo = s;
        }
        xcontent.achmode = "CCD";
        xcontent.start_date = currentDate.getFullYear() + "-" + (currentDate.getMonth() + 1) + "-" + currentDate.getDate();
        xcontent.inv_id = inv_id;
        xcontent.inv_number = invoice_number;
        var invoice_amount = $("#xinvoice_amount").val();
        if (isNaN(invoice_amount)) {
            invoice_amount = 0;
        }
        xcontent.net_amount = invoice_amount;
        xturl += JSON.stringify(xcontent);
        $.ajax({
            url: xurl + xturl
        }).done(function (data) {
            if (data.response == 1) {
                /*$("#xpopupheader").html("Approved");
                 $("#xpopupcontent").html(data.responsetext + " " + data.authcode);
                 $("#myModal_loading").modal("hide");*/
                $('#loading_layer').fadeOut();
                window.location.href = xurl + "/eterm/receipt/" + data.txid + "/" + data.timex + "/" + data.auto;
            } else {
                /*$("#xpopupheader").html("Error/Declined");
                 $("#xpopupcontent").html(data.responsetext);
                 $("#myModal_loading").modal("hide");
                 $("#myModal_success").modal();*/
                $('#loading_layer').fadeOut();
                swal({
                    title: "Error / Declined",
                    text: data.responsetext,
                    type: "error"
                });
            }
        });
    } else {
        var temp_type = type;
        if (parseInt(profile) > 0)
            temp_type = "prf";
        //var xturl = xturl_generated.replace('/token/','/'+ xatoken +'/');
        //xturl = xturl.replace('/temp_type','/'+ temp_type+'/' );
        var xturl = "/etermpay/" + xatoken + "/" + temp_type + "/";

        var xcontent = {'source': 'eterm'};
        xcontent.start_date = currentDate.getFullYear() + "-" + (currentDate.getMonth() + 1) + "-" + currentDate.getDate();
        xcontent.profile_id = profile;
        xcontent.memo = "";
        if ($('#inputcfee').length) {
            var xcfee = $("#inputcfee").val();
            xcontent.xcfee = xcfee;
        }
        if ($('#xwalkin').length) {
            var xwalkin = $("#inputwalkin").val();
            xcontent.xwalkin = xwalkin;
        }
        if ($('#xcphonefee').length) {
            var xphonefee = parseFloat($("#inputphonefee").val()).toFixed(2);
            xcontent.xphonefee = xphonefee;
        }
        if ($('#etermpaymenttype').length) {
            if ($('#etermpaymenttype').val() > "0") {
                xcontent.service = $('#etermpaymenttype').val();
            }
        }
        var xinvopc_tmp = $("#xinvopc").val();
        if (xinvopc_tmp != "") {
            xcontent.inv_number = xinvopc_tmp;
        }

        var s = $("#xmemo").val();
        if (typeof s != 'undefined' && s != "") {
            s = s.replace("#", "");
            s = s.replace('/', "");
            s = s.replace('$', "");
            s = s.replace('\\', "");
            s = s.replace('%', "");
            xcontent.memo = s;
        }
        if (customfield == "1") {
            var customfield1 = [];

            $('.customfield').each(function () {
                field_description = $(this).find('span').text();
                field_description = field_description.replace(":", "");
                field_description = field_description.replace("#", "");
                field_value = $(this).find('input').val();
                field_type = $(this).find('input').attr('type');
                field_id = $(this).find('input').data('id');
                customfield1.push({
                    'id': field_id,
                    'field_description': field_description,
                    'field_value': field_value,
                    'field_type': field_type
                });
            });

        }
        var categories = [];
        if (isrecurring) {
            xcontent.freq = $("#xfreq").val();

            var newdate = $("#xrenddate").val();
            var newdateformat=newdate.replace("|","-");
            xcontent.end_date = newdateformat;
            if ($("#inlineRadio1").is(":checked")) {
                xcontent.dynamic = 1;
                xcontent.balance = balance;
            } else
                xcontent.dynamic = 0;
        }

        var tmp_amount = 0;


        for (var i = 0; tmp_obj = document.getElementById("xcheckpay_" + i); i++) {
            if (tmp_obj.checked) {
                tmp_obj = document.getElementById("xinputpay_" + i);
                qty_obj = document.getElementById("qtypay_" + i);
                if (qty_obj == null) {
                    qty = 1;
                } else {
                    qty = qty_obj.value;
                    qty = qty.replace("x", "");
                }
                tmp = tmp_obj.value.replace(/,/g, "");
                tmp = parseFloat(tmp);
                tmp_amount += tmp;
                if (!isNaN(tmp) && tmp > 0) {
                    var xname = $("#xinputpay_" + i).attr("xname");
                    xname = xname.replace("/", "-");
                    xname = xname.replace("#", "");
                    xname = xname.replace('/', "");
                    xname = xname.replace('$', "");
                    xname = xname.replace('\\', "");
                    xname = xname.replace('%', "");
                    var xid = tmp_obj.name;
                    categories.push({'amount': tmp, 'qty': qty, 'id': xid, 'name': xname});
                }
            }
        }

        if (profile > 0) {
            xcontent.profile_id = profile;
        } else if ($("#checkbox4").is(":checked")) {
            //ec
            xcontent.ec_account_holder = $("#xppec_name").val();
            xcontent.ec_account_lholder = '';
            xcontent.ec_routing_number = $("#xppec_routing").val();
            xcontent.ec_account_number = $("#xppec_acc").val();
            xcontent.ec_checking_savings = $("#xppec_type").val();

        } else if ($("#checkbox5").is(":checked")) {
            //cc
            xcontent.ccname = $("#xcardname").val();
            xcontent.ccnumber = $("#xcardnumber").val();
            xcontent.ccexp = $("#xexpdate").val();
            xcontent.cvv = $("#xcvv").val();
            xcontent.zip = $("#xzip1").val();

        }
        //for pass swipe data for payment and save data in database :AJ - Accepted Payment
        else if ($("#checkbox6").is(":checked")) {

            var str_cc = $("#strackdata").val();
            var xcardname = getName(str_cc);
            var xcardnumber = getcc(str_cc);
            var xexpdate = getExpYYMMTrack2(str_cc);
            var xcvv = "";
            var xzip = "";
            var track2 = gettrack2(false, str_cc);
            xcontent.ccname = xcardname;
            xcontent.ccnumber = xcardnumber;
            xcontent.ccexp = xexpdate;
            xcontent.cvv = "";
            xcontent.zip = "";
            xcontent.Track2Data = track2;

        } else
            return false;

        if (profile == 0) {
            xcontent.saveprofile = saveValue;
        }

        xcontent.categories = categories;
        xcontent.customfield = customfield1;
        xturl += JSON.stringify(xcontent);

        $('#loading_layer').fadeIn();
        $.ajax({
            url: xturl

        }).done(function (data) {
            $('#loading_layer').fadeOut();
            if (data.response == 1) {

                if (data.auto == 2) {
                    window.location.href = xurl + "/eterm/receipt/" + data.txid + "/" + data.timex + "/" + data.auto + "/" + data.trans_id;
                } else {
                    window.location.href = xurl + "/eterm/receipt/" + data.txid + "/" + data.timex + "/" + data.auto;
                }
            } else {
                swal({
                    title: "Error / Declined",
                    text: data.responsetext,
                    type: "error"
                });
            }
        });
    }
}

function cleanECandCC() {
    //ec
    $("#xppec_name").val("");
    $("#xppec_routing").val("");
    $("#xppec_acc").val("");
    $("#xppec_confirm_acc").val("");
    //cc
    $("#xcardname").val("");
    $("#xcardnumber").val("");
    $("#xexpdate").val("");
    $("#xcvv").val("");
    $("#xzip1").val("");
    $("#strackdata").val("");
}

function showTransdetail(id) {
    //resetParentCounters();
    var xturl = xturl_generated_transdetails.replace('00000000000000000000', id)
    $.ajax({
        url: xturl
    }).done(function (data) {
        if (data.errcode == 0) {
            $("#xpopupheader").html("Payment Details");
            $("#xpopupcontent").html(data.msg);
            $('#myModal_success').modal();
        } else {
            $("#xpopupcontent").html("<label>" + data.msg + "</label>");
            $('#myModal_success').modal();
        }
    });
}

function clean2() {
    $("#checkbox4").prop("checked", false);
    $("#checkbox5").prop("checked", false);
    $("#xsaveprofile_id").prop("checked", false);
    $("#checkbox6").prop("checked", false);
}

function ChangeIcon() {
    var lenght = 0;
    var select_date = currentDate.getFullYear() + "-" + (currentDate.getMonth() + 1) + "-" + currentDate.getDate();
    var onetimefuture = 0;
    if (select_date > xnowday) {
        var onetimefuture = 1;
    }
    if (isrecurring || onetimefuture) {
        length = $('#xprof_1').length;
        if (length > 0) {
            var p = $("#xprof_1 option:selected").attr("id").split("|");
            var nme = $("#xprof_1 option:selected").attr("name");
        }
        if (length > 0) {
            if (p.length < 1) {
                return false;
            }
            switch (p[1]) {
                case 'visa':
                    $("#xicon_1").attr("src", "/img/visa.png");
                    $("#xpopImage_paymethod").html("<img src='/img/visa.png'>");
                    $("#xpopName_paymethod").html(nme);
                    break;
                case 'mastercard':
                    $("#xicon_1").attr("src", "/img/mastercard.png");
                    $("#xpopImage_paymethod").html("<img src='/img/mastercard.png'>");
                    $("#xpopName_paymethod").html(nme);
                    break;
                case 'discover':
                    $("#xicon_1").attr("src", "/img/discover.png");
                    $("#xpopImage_paymethod").html("<img src='/img/discover.png'>");
                    $("#xpopName_paymethod").html(nme);
                    break;
                case 'am':
                    $("#xicon_1").attr("src", "/img/american.png");
                    $("#xpopImage_paymethod").html("<img src='/img/american.png'>");
                    $("#xpopName_paymethod").html(nme);
                    break;
                case 'ec':
                    $("#xicon_1").attr("src", "/img/echeck.png");
                    $("#xpopImage_paymethod").html("<img src='/img/echeck.png'>");
                    $("#xpopName_paymethod").html(nme);
                    break;
                default:
                    break; //unknown profile
            }
        }
    } else {
        length = $('#xprof_0').length;
        if (length > 0) {
            var p = $("#xprof_0 option:selected").attr("id").split("|");
            var nme = $("#xprof_0 option:selected").attr("name");
            if (p.length < 1) {
                return false;
            }
            switch (p[1]) {
                case 'visa':
                    $("#xicon_0").attr("src", "/img/visa.png");
                    $("#xpopImage_paymethod").html("<img src='/img/visa.png'>");
                    $("#xpopName_paymethod").html(nme);
                    break;
                case 'mastercard':
                    $("#xicon_0").attr("src", "/img/mastercard.png");
                    $("#xpopImage_paymethod").html("<img src='/img/mastercard.png'>");
                    $("#xpopName_paymethod").html(nme);
                    break;
                case 'discover':
                    $("#xicon_0").attr("src", "/img/discover.png");
                    $("#xpopImage_paymethod").html("<img src='/img/discover.png'>");
                    $("#xpopName_paymethod").html(nme);
                    break;
                case 'am':
                    $("#xicon_0").attr("src", "/img/american.png");
                    $("#xpopImage_paymethod").html("<img src='/img/american.png'>");
                    $("#xpopName_paymethod").html(nme);
                    break;
                case 'ec':
                    $("#xicon_0").attr("src", "/img/echeck.png");
                    $("#xpopImage_paymethod").html("<img src='/img/echeck.png'>");
                    $("#xpopName_paymethod").html(nme);
                    break;
                default:
                    break; //unknown profile
            }
        }
    }

}

function get_cctype() {
    var cardnumber = $('#xcardnumber').val();
    var xcontent = {};
    xcontent.ccnumber = cardnumber;
    var aux = cardnumber.substr(0, 1);
    switch (aux) {
        case "4":
            cctype = 'Visa';
            break;
        case "5":
            cctype = 'MasterCard';
            break;
        case "6":
            cctype = 'Discover';
            break;
        default:
            cctype = 'Unknown';
            break;
    }
    xcontent.cctype = cctype;
    // alert(xcontent);
    if (aux == 4 || aux == 5 || aux == 6) {
        var xroute = '/getCCTypeFee/' + xatoken + '/';
        xroute += JSON.stringify(xcontent);
        $.ajax({
            url: xurl + xroute
        }).done(function (data) {
            card_type_fee = data;
            eterm_calculateFee(type, xamount, card_type_fee);
        });
    }
}

function get_cctypeSwipe() {
    var str_swipe = $("#strackdata").val();
    card_type_fee = "";
    if (str_swipe.length > 0) {
        var xcardnumber = getcc(str_swipe);
        if (xcardnumber == null) {
            xcardnumber = "";
        }

        var xcontent = {};
        xcontent.ccnumber = xcardnumber;
        var aux = xcardnumber.substr(0, 1);
        switch (aux) {
            case "3":
                cctype = 'AmericanExpress';
                card_type_fee = 'am';
                CalculaFeeX(5);
                break;
            case "4":
                cctype = 'Visa';
                card_type_fee = 'vdb';
                break;
            case "5":
                cctype = 'MasterCard';
                card_type_fee = 'mcdb';
                break;
            case "6":
                cctype = 'Discover';
                card_type_fee = 'ddb';
                break;
            default:
                cctype = 'Unknown';
                card_type_fee = 'vdb';
                break;
        }
        xcontent.cctype = cctype;
        // alert(xcontent);
        if (aux == 4 || aux == 5 || aux == 6) {
            var xroute = '/getCCTypeFee/' + xatoken + '/';
            xroute += JSON.stringify(xcontent);
            $.ajax({
                url: xurl + xroute,
                async: false
            }).done(function (data) {
                card_type_fee = data;
                CalculaFeeX(5);
            });
        }
    }
}


function validate_trackdata() {
    var xcardnumber = getcc($("#strackdata").val());
    if (xcardnumber == null) {
        xcardnumber = "";
    }
    if (!ValidateCCNumberSwipe(xcardnumber) && !IsEmpty('strackdata')) {
        return false;
    }

    if (!IsEmpty('strackdata')) {
        hideerror('strackdata');
        return true;
    } else {
        showerror('strackdata', 'Track data is required to Swipe payments');
    }
    return false;
}

//$(document).ready(function (){
//    
//});

function checkswipedata1(){
    myVar = setInterval(checkswipedata, 1000);
}
function checkswipedata() {
    //debugger;
    if($("#strackdata").val().length != 0){
         $('#swipemessage').html('<div class="alert alert-success alert-dismissible" id="swipemessage1"  hidden><a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a><strong>Swipe data received Successfully!</strong></div>');
         $("#swipemessage1").fadeTo(8000, 500).slideUp(500, function(){
               $("#swipemessage1").slideUp(500);
                });
        $('#swipeagain').show();
        stopswipe();
    }
}

function stopswipe() {
    clearInterval(myVar);
}

$('.etermial_walkin').click(function () {
    phonefee_edited = parseFloat($("#phonefee_value").val()).toFixed(2);
    $('.walkin_tooltip').tooltip("hide");
    CalculateEtermAmount();
});

function includes(container, value) {
    var returnValue = false;
    var pos = container.indexOf(value);
    if (pos >= 0) {
        returnValue = true;
    }
    return returnValue;
}

function PhoneFeeEdited() {
    if(!validate_phone_fee()){
        swal({
            title: "Invalid Phone Fee!",
            text: "Invalid Phone Fee field, This field must be a numeric positive value"
        });
        return false;
    }else{
        phonefee_edited = parseFloat($("#phonefee_value").val()).toFixed(2);
        CalculateEtermAmount();
    }
}
