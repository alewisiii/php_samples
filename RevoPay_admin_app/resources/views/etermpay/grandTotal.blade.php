<div class="panel panel-default" style="border: none; box-shadow: none; background-color: #F4F4F4; padding: 15px">
    <div class="panel-body" style="font-size: 12px">
        <div class="row total grey" id="xdiv_paymentamount">
            <div class="col-xs-6">
                <label class="grey">Total Payment Amount:</label>
            </div>
            <div class="col-xs-6 text-right">
                <label class="price no-margin" id="xprevtotal">$0.00</label>
            </div>
        </div>
        <br>
        <div class="row" id="walkin_div">
            <div class="col-xs-12">
                <label class="no-margin walkin_tooltip" data-toggle="tooltip" data-title="By activating this button, you will override any convenience fees and the business on file will be billed the fees"><input id="walkin" name="walkin" class="etermial_walkin" type="checkbox"><b> Is a Walk-In Payment</b></label>
            </div>
        </div>
        <br>
        <div class="row" id="phonefee_div">
            <div class="col-xs-6">
                <h5 class="no-margin"><b>Phone Fee: </b></h5>
            </div>
            <div class="col-xs-6 text-right">
                <div class="row">
                    <div class="col-xs-5">
                    </div>
                    <div class="col-xs-7">
                        <div class="input-group">
                            <span class="input-group-addon">$</span>
                            <input type="text" id="phonefee_value" class="form-control text-right input-active" placeholder="0.00" onblur="PhoneFeeEdited()">
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <br>
        <div class="row" id="xservice_fee">
            <div class="col-xs-6">
                <h5 class="no-margin"><b>Convenience Fee: </b></h5>
            </div>
            <div class="col-xs-6 text-right " >
                <h5 class="no-margin"><b><div id="xcfee">Select Payment Method</div></b></h5>
            </div>
        </div>
        <div class="row" id="xmodalservice_fee">
            <div class="col-xs-12">
                <a type="button" style="cursor:pointer" class="underline" onclick="showModal('myModal_successfee')">Click here to learn more about this convenience fee </a>
            </div>

        </div>
        <div class="row">
            <?php if (isset($settlement_disclaimer) && !empty($settlement_disclaimer)) { ?>
                <div class="col-xs-12" id="convFeeLink">
                    <a type="button" style="cursor:pointer" class="underline"  onclick="showModal('myDisclaimer')">Settlement Disclaimer </a>
                </div>
            <?php } ?>
        </div>

        <hr style="margin-right: -15px; margin-left: -15px"/>
        <div class="row" id="xdiv_grandtotal">
            <div class="col-xs-6">
                <h4 class="no-margin"><b class="total-dark">Grand Total:</b></h4>
            </div>
            <div class="col-xs-6 text-right">
                <h4 class="no-margin"><b class="total-dark" id="xgrandtotal">$0.00</b></h4>
            </div>
        </div>
    </div>
</div>

<?php
$popuphdr = "Convenience Fee";

$popupcontent = "<div id='xcfee_ot'>";
if (isset($credOneTime['ec']) && count($credOneTime['ec']) == 1) {
    if ($credOneTime['ec'][0]->convenience_fee + $credOneTime['ec'][0]->convenience_fee_float > 0) {
        $popupcontent.= '<br>';
        $popupcontent.= '<div class="row">';
        $popupcontent.='<div class="col-md-6">';
        $popupcontent.= '<img src="/img/echeck.png">&nbsp;';
        $popupcontent.= '<label> One-time eCheck</label>';
        $popupcontent.='</div>';
        $popupcontent.='<div class="col-md-6">';
        if ($credOneTime['ec'][0]->convenience_fee != 0.00 && $credOneTime['ec'][0]->convenience_fee_float != 0.00) {
            $popupcontent.= '<label>$' . $credOneTime['ec'][0]->convenience_fee . ' + ' . $credOneTime['ec'][0]->convenience_fee_float . '%';
            ;
        } else {
            if ($credOneTime['ec'][0]->convenience_fee != 0.00)
                $popupcontent.= '<label>$' . $credOneTime['ec'][0]->convenience_fee;
            if ($credOneTime['ec'][0]->convenience_fee_float != 0.00)
                $popupcontent.='<label>' . $credOneTime['ec'][0]->convenience_fee_float . '%';
        }
        $popupcontent.='</label>';
        $popupcontent.='</div>';
        $popupcontent.= '</div>';
    }
}elseif (isset($credOneTime['ec'])) {
    for ($i = 0; $i < count($credOneTime['ec']); $i++) {
        $popupcontent.= '<br>';
        $popupcontent.= '<div class="row">';
        $popupcontent.='<div class="col-md-8">';
        $popupcontent.= '<img src="/img/echeck.png">&nbsp;';
        $popupcontent.= '<label>eCheck</label>';
        $popupcontent.= '<label> (';
        $popupcontent.= $credOneTime['ec'][$i]->low_pay_range . ' - ' . $credOneTime['ec'][$i]->high_pay_range;
        $popupcontent.= ')</label>';
        $popupcontent.='</div>';
        $popupcontent.='<div class="col-md-4">';
        if ($credOneTime['ec'][$i]->convenience_fee != 0.00 && $credOneTime['ec'][$i]->convenience_fee_float != 0.00) {
            $popupcontent.= '<label>$' . $credOneTime['ec'][$i]->convenience_fee . ' + ' . $credOneTime['ec'][$i]->convenience_fee_float . '%';
        } else {
            if ($credOneTime['ec'][$i]->convenience_fee != 0.00)
                $popupcontent.= '<label>$' . $credOneTime['ec'][$i]->convenience_fee;
            if ($credOneTime['ec'][$i]->convenience_fee_float != 0.00)
                $popupcontent.='<label>' . $credOneTime['ec'][$i]->convenience_fee_float . '%';
        }
        $popupcontent.='</label>';
        $popupcontent.='</div>';
        $popupcontent.= '</div>';
    }
}


if (isset($credOneTime['cc']) && count($credOneTime['cc']) == 1) {
    if ($credOneTime['cc'][0]->convenience_fee + $credOneTime['cc'][0]->convenience_fee_float > 0) {
        $popupcontent.= '<br>';
        $popupcontent.= '<div class="row">';
        $popupcontent.='<div class="col-md-6">';
        $popupcontent.= '<span class="glyphicon glyphicon-credit-card"></span>&nbsp;';
        $popupcontent.= '<label> One-time Debit / Credit Card  </label>';
        $popupcontent.='</div>';
        $popupcontent.='<div class="col-md-6">';
        if ($credOneTime['cc'][0]->convenience_fee != 0.00 && $credOneTime['cc'][0]->convenience_fee_float != 0.00) {
            $popupcontent.= '<label>$' . $credOneTime['cc'][0]->convenience_fee . ' + ' . $credOneTime['cc'][0]->convenience_fee_float . '%';
        } else {
            if ($credOneTime['cc'][0]->convenience_fee != 0.00)
                $popupcontent.= '<label>$' . $credOneTime['cc'][0]->convenience_fee;
            if ($credOneTime['cc'][0]->convenience_fee_float != 0.00)
                $popupcontent.='<label>' . $credOneTime['cc'][0]->convenience_fee_float . '%';
        }
        $popupcontent.='</label>';
        $popupcontent.='</div>';
        $popupcontent.= '</div>';
    }
}elseif (isset($credOneTime['cc'])) {
    for ($i = 0; $i < count($credOneTime['cc']); $i++) {
        $popupcontent.= '<br>';
        $popupcontent.= '<div class="row">';
        $popupcontent.='<div class="col-md-8">';
        $popupcontent.= '<span class="glyphicon glyphicon-credit-card"></span>&nbsp;';
        $popupcontent.= '<label> One-time Debit / Credit Card  </label>';
        $popupcontent.= '<label> (';
        $popupcontent.= $credOneTime['cc'][$i]->low_pay_range . ' - ' . $credOneTime['cc'][$i]->high_pay_range;
        $popupcontent.= ')</label>';
        $popupcontent.='</div>';
        $popupcontent.='<div class="col-md-4">';
        if ($credOneTime['cc'][$i]->convenience_fee != 0.00 && $credOneTime['cc'][$i]->convenience_fee_float) {
            $popupcontent.= '<label>$' . $credOneTime['cc'][$i]->convenience_fee . ' + ' . $credOneTime['cc'][$i]->convenience_fee_float . '%';
        } else {
            if ($credOneTime['cc'][$i]->convenience_fee != 0.00)
                $popupcontent.= '<label>$' . $credOneTime['cc'][$i]->convenience_fee;
            if ($credOneTime['cc'][$i]->convenience_fee_float != 0.00)
                $popupcontent.='<label>' . $credOneTime['cc'][$i]->convenience_fee_float . '%';
        }
        $popupcontent.='</label>';
        $popupcontent.='</div>';
        $popupcontent.= '</div>';
    }
}


if (isset($credOneTime['amex']) && count($credOneTime['amex']) == 1) {
    if ($credOneTime['amex'][0]->convenience_fee + $credOneTime['amex'][0]->convenience_fee_float > 0) {
        $popupcontent.= '<br>';
        $popupcontent.= '<div class="row">';
        $popupcontent.='<div class="col-md-6">';
        $popupcontent.= '<img src="/img/american.png">&nbsp;';
        $popupcontent.= '<label> American Express </label>';
        $popupcontent.='</div>';
        $popupcontent.='<div class="col-md-6">';
        if ($credOneTime['amex'][0]->convenience_fee_float != 0.00 && $credOneTime['amex'][0]->convenience_fee_float . '%') {
            $popupcontent.= '<label>$' . $credOneTime['amex'][0]->convenience_fee . ' + ' . $credOneTime['amex'][0]->convenience_fee_float . '%';
        } else {

            if ($credOneTime['amex'][0]->convenience_fee != 0.00)
                $popupcontent.= '<label>$' . $credOneTime['amex'][0]->convenience_fee;
            if ($credOneTime['amex'][0]->convenience_fee_float != 0.00)
                $popupcontent.='<label>' . $credOneTime['amex'][0]->convenience_fee_float . '%';
        }
        $popupcontent.='</label>';
        $popupcontent.='</div>';
        $popupcontent.= '</div>';
    }
}elseif (isset($credOneTime['amex'])) {
    for ($i = 0; $i < count($credOneTime['amex']); $i++) {
        $popupcontent.= '<br>';
        $popupcontent.= '<div class="row">';
        $popupcontent.='<div class="col-md-8">';
        $popupcontent.= '<span class="glyphicon glyphicon-credit-card"></span>&nbsp;';
        $popupcontent.= '<label> American Express  </label>';
        $popupcontent.= '<label> (';
        $popupcontent.= $credOneTime['amex'][$i]->low_pay_range . ' - ' . $credOneTime['amex'][$i]->high_pay_range;
        $popupcontent.= ')</label>';
        $popupcontent.='</div>';
        $popupcontent.='<div class="col-md-4">';
        if ($credOneTime['amex'][$i]->convenience_fee != 0.00 && $credOneTime['amex'][$i]->convenience_fee_float . '%') {
            $popupcontent.= '<label>$' . $credOneTime['amex'][$i]->convenience_fee . ' + ' . $credOneTime['amex'][$i]->convenience_fee_float . '%';
        } else {
            if ($credOneTime['amex'][$i]->convenience_fee != 0.00)
                $popupcontent.= '<label>$' . $credOneTime['amex'][$i]->convenience_fee;
            if ($credOneTime['amex'][$i]->convenience_fee_float != 0.00)
                $popupcontent.='<label>' . $credOneTime['amex'][$i]->convenience_fee_float . '%';
        }
        $popupcontent.='</label>';
        $popupcontent.='</div>';
        $popupcontent.= '</div>';
    }
}


$popupcontent.="</div>";

$popupcontent.="<div id='xcfee_rc' style='display:none'>";

if (isset($credRecurring['ec']) && count($credRecurring['ec']) == 1) {
    if ($credRecurring['ec'][0]->convenience_fee + $credRecurring['ec'][0]->convenience_fee_float > 0) {
        $popupcontent.= '<br>';
        $popupcontent.= '<div class="row">';
        $popupcontent.='<div class="col-md-6">';
        $popupcontent.= '<img src="/img/echeck.png">&nbsp;';
        $popupcontent.= '<label> Recurring eCheck for Fixed Amount </label>';
        $popupcontent.='</div>';
        $popupcontent.='<div class="col-md-6">';
        if ($credRecurring['ec'][0]->convenience_fee != 0.00 && $credRecurring['ec'][0]->convenience_fee_float != 0.00) {
            $popupcontent.= '<label>$' . $credRecurring['ec'][0]->convenience_fee . ' + ' . $credRecurring['ec'][0]->convenience_fee_float . '%';
        } else {
            if ($credRecurring['ec'][0]->convenience_fee != 0.00)
                $popupcontent.= '<label>$' . $credRecurring['ec'][0]->convenience_fee;
            if ($credRecurring['ec'][0]->convenience_fee_float != 0.00)
                $popupcontent.='<label>' . $credRecurring['ec'][0]->convenience_fee_float . '%';
        }
        $popupcontent.='</label>';
        $popupcontent.='</div>';
        $popupcontent.= '</div>';
    }

    //Dynamic Recurring Payment
    if ($credRecurring['ec'][0]->convenience_fee_drp + $credRecurring['ec'][0]->convenience_fee_float_drp > 0) {
        $popupcontent.= '<br>';
        $popupcontent.= '<div class="row">';
        $popupcontent.='<div class="col-md-6">';
        $popupcontent.= '<img src="/img/echeck.png">&nbsp;';
        $popupcontent.= '<label>Recurring eCheck for Full Balance </label>';
        $popupcontent.='</div>';
        $popupcontent.='<div class="col-md-6">';
        if ($credRecurring['ec'][0]->convenience_fee_drp != 0.00 && $credRecurring['ec'][0]->convenience_fee_float_drp != 0.00) {
            $popupcontent.= '<label>$' . $credRecurring['ec'][0]->convenience_fee_drp . ' + ' . $credRecurring['ec'][0]->convenience_fee_float_drp . '%';
        } else {
            if ($credRecurring['ec'][0]->convenience_fee_drp != 0.00)
                $popupcontent.= '<label>$' . $credRecurring['ec'][0]->convenience_fee_drp;
            if ($credRecurring['ec'][0]->convenience_fee_float_drp != 0.00)
                $popupcontent.='<label>' . $credRecurring['ec'][0]->convenience_fee_float_drp . '%';
        }
        $popupcontent.='</label>';
        $popupcontent.='</div>';
        $popupcontent.= '</div>';
    }
}elseif (isset($credRecurring['ec'])) {
    for ($i = 0; $i < count($credRecurring['ec']); $i++) {
        $popupcontent.= '<br>';
        $popupcontent.= '<div class="row">';
        $popupcontent.='<div class="col-md-8">';
        $popupcontent.= '<img src="/img/echeck.png">&nbsp;';
        $popupcontent.= '<label>Recurring eCheck for Fixed Amount</label>';
        $popupcontent.= '<label> (';
        $popupcontent.= $credRecurring['ec'][$i]->low_pay_range . ' - ' . $credRecurring['ec'][$i]->high_pay_range;
        $popupcontent.= ')</label>';
        $popupcontent.='</div>';
        $popupcontent.='<div class="col-md-4">';
        if ($credRecurring['ec'][$i]->convenience_fee != 0.00 && $credRecurring['ec'][$i]->convenience_fee_float != 0.00) {
            $popupcontent.= '<label>$' . $credRecurring['ec'][$i]->convenience_fee . ' + ' . $credRecurring['ec'][$i]->convenience_fee_float . '%';
        } else {
            if ($credRecurring['ec'][$i]->convenience_fee != 0.00)
                $popupcontent.= '<label>$' . $credRecurring['ec'][$i]->convenience_fee;
            if ($credRecurring['ec'][$i]->convenience_fee_float != 0.00)
                $popupcontent.='<label>' . $credRecurring['ec'][$i]->convenience_fee_float . '%';
        }
        $popupcontent.='</label>';
        $popupcontent.='</div>';
        $popupcontent.= '</div>';
    }

    //Dynamic Recurring Payment
    for ($i = 0; $i < count($credRecurring['ec']); $i++) {
        $popupcontent.= '<br>';
        $popupcontent.= '<div class="row">';
        $popupcontent.='<div class="col-md-8">';
        $popupcontent.= '<img src="/img/echeck.png">&nbsp;';
        $popupcontent.= '<label>Recurring eCheck for Full Balance</label>';
        $popupcontent.= '<label> (';
        $popupcontent.= $credRecurring['ec'][$i]->low_pay_range . ' - ' . $credRecurring['ec'][$i]->high_pay_range;
        $popupcontent.= ')</label>';
        $popupcontent.='</div>';
        $popupcontent.='<div class="col-md-4">';
        if ($credRecurring['ec'][$i]->convenience_fee_drp != 0.00 && $credRecurring['ec'][$i]->convenience_fee_float_drp != 0.00) {
            $popupcontent.= '<label>$' . $credRecurring['ec'][$i]->convenience_fee_drp . ' + ' . $credRecurring['ec'][$i]->convenience_fee_float_drp . '%';
        } else {
            if ($credRecurring['ec'][$i]->convenience_fee_drp != 0.00)
                $popupcontent.= '<label>$' . $credRecurring['ec'][$i]->convenience_fee_drp;
            if ($credRecurring['ec'][$i]->convenience_fee_float_drp != 0.00)
                $popupcontent.='<label>' . $credRecurring['ec'][$i]->convenience_fee_float_drp . '%';
        }
        $popupcontent.='</label>';
        $popupcontent.='</div>';
        $popupcontent.= '</div>';
    }
}


if (isset($credRecurring['cc']) && count($credRecurring['cc']) == 1) {
    if ($credRecurring['cc'][0]->convenience_fee + $credRecurring['cc'][0]->convenience_fee_float > 0) {
        $popupcontent.= '<br>';
        $popupcontent.= '<div class="row">';
        $popupcontent.='<div class="col-md-6">';
        $popupcontent.= '<span class="glyphicon glyphicon-credit-card"></span>&nbsp;';
        $popupcontent.= '<label> Recurring Debit / Credit Card for Fixed Amount  </label>';
        $popupcontent.='</div>';
        $popupcontent.='<div class="col-md-6">';
        if ($credRecurring['cc'][0]->convenience_fee != 0.00 && $credRecurring['cc'][0]->convenience_fee_float != 0.00) {
            $popupcontent.= '<label>$' . $credRecurring['cc'][0]->convenience_fee . ' + ' . $credRecurring['cc'][0]->convenience_fee_float . '%';
        } else {
            if ($credRecurring['cc'][0]->convenience_fee != 0.00)
                $popupcontent.= '<label>$' . $credRecurring['cc'][0]->convenience_fee;
            if ($credRecurring['cc'][0]->convenience_fee_float != 0.00)
                $popupcontent.='<label>' . $credRecurring['cc'][0]->convenience_fee_float . '%';
        }
        $popupcontent.='</label>';
        $popupcontent.='</div>';
        $popupcontent.= '</div>';
    }
    //Dynamic Recurring Payment
    if ($credRecurring['cc'][0]->convenience_fee_drp + $credRecurring['cc'][0]->convenience_fee_float_drp > 0) {
        $popupcontent.= '<br>';
        $popupcontent.= '<div class="row">';
        $popupcontent.='<div class="col-md-6">';
        $popupcontent.= '<span class="glyphicon glyphicon-credit-card"></span>&nbsp;';
        $popupcontent.= '<label>Recurring Debit / Credit Card for Full Balance  </label>';
        $popupcontent.='</div>';
        $popupcontent.='<div class="col-md-6">';
        if ($credRecurring['cc'][0]->convenience_fee_drp != 0.00 && $credRecurring['cc'][0]->convenience_fee_float_drp != 0.00) {
            $popupcontent.= '<label>$' . $credRecurring['cc'][0]->convenience_fee_drp . ' + ' . $credRecurring['cc'][0]->convenience_fee_float_drp . '%';
        } else {
            if ($credRecurring['cc'][0]->convenience_fee_drp != 0.00)
                $popupcontent.= '<label>$' . $credRecurring['cc'][0]->convenience_fee_drp;
            if ($credRecurring['cc'][0]->convenience_fee_float_drp != 0.00)
                $popupcontent.='<label>' . $credRecurring['cc'][0]->convenience_fee_float_drp . '%';
        }
        $popupcontent.='</label>';
        $popupcontent.='</div>';
        $popupcontent.= '</div>';
    }
}elseif (isset($credRecurring['cc'])) {
    for ($i = 0; $i < count($credRecurring['cc']); $i++) {
        $popupcontent.= '<br>';
        $popupcontent.= '<div class="row">';
        $popupcontent.='<div class="col-md-8">';
        $popupcontent.= '<span class="glyphicon glyphicon-credit-card"></span>&nbsp;';
        $popupcontent.= '<label> Recurring Debit / Credit Card for Fixed Amount </label>';
        $popupcontent.= '<label> (';
        $popupcontent.= $credRecurring['cc'][$i]->low_pay_range . ' - ' . $credRecurring['cc'][$i]->high_pay_range;
        $popupcontent.= ')</label>';
        $popupcontent.='</div>';
        $popupcontent.='<div class="col-md-4">';
        if ($credRecurring['cc'][$i]->convenience_fee != 0.00 && $credRecurring['cc'][$i]->convenience_fee_float != 0.00) {
            $popupcontent.= '<label>$' . $credRecurring['cc'][$i]->convenience_fee . ' + ' . $credRecurring['cc'][$i]->convenience_fee_float . '%';
        } else {
            if ($credRecurring['cc'][$i]->convenience_fee != 0.00)
                $popupcontent.= '<label>$' . $credRecurring['cc'][$i]->convenience_fee;
            if ($credRecurring['cc'][$i]->convenience_fee_float != 0.00)
                $popupcontent.='<label>' . $credRecurring['cc'][$i]->convenience_fee_float . '%';
        }
        $popupcontent.='</label>';
        $popupcontent.='</div>';
        $popupcontent.= '</div>';
    }

    //Dynamic Recurring Payment
    for ($i = 0; $i < count($credRecurring['cc']); $i++) {
        $popupcontent.= '<br>';
        $popupcontent.= '<div class="row">';
        $popupcontent.='<div class="col-md-8">';
        $popupcontent.= '<span class="glyphicon glyphicon-credit-card"></span>&nbsp;';
        $popupcontent.= '<label> Recurring Debit / Credit Card for Full Balance </label>';
        $popupcontent.= '<label> (';
        $popupcontent.= $credRecurring['cc'][$i]->low_pay_range . ' - ' . $credRecurring['cc'][$i]->high_pay_range;
        $popupcontent.= ')</label>';
        $popupcontent.='</div>';
        $popupcontent.='<div class="col-md-4">';
        if ($credRecurring['cc'][$i]->convenience_fee_drp != 0.00 && $credRecurring['cc'][$i]->convenience_fee_float_drp != 0.00) {
            $popupcontent.= '<label>$' . $credRecurring['cc'][$i]->convenience_fee_drp . ' + ' . $credRecurring['cc'][$i]->convenience_fee_float_drp . '%';
        } else {
            if ($credRecurring['cc'][$i]->convenience_fee_drp != 0.00)
                $popupcontent.= '<label>$' . $credRecurring['cc'][$i]->convenience_fee_drp;
            if ($credRecurring['cc'][$i]->convenience_fee_float_drp != 0.00)
                $popupcontent.='<label>' . $credRecurring['cc'][$i]->convenience_fee_float_drp . '%';
        }
        $popupcontent.='</label>';
        $popupcontent.='</div>';
        $popupcontent.= '</div>';
    }
}


if (isset($credRecurring['amex']) && count($credRecurring['amex']) == 1) {
    if ($credRecurring['amex'][0]->convenience_fee + $credRecurring['amex'][0]->convenience_fee_float > 0) {
        $popupcontent.= '<br>';
        $popupcontent.= '<div class="row">';
        $popupcontent.='<div class="col-md-6">';
        $popupcontent.= '<img src="/img/american.png">&nbsp;';
        $popupcontent.= '<label> American Express </label>';
        $popupcontent.='</div>';
        $popupcontent.='<div class="col-md-6">';
        if ($credRecurring['amex'][0]->convenience_fee != 0.00 && $credRecurring['amex'][0]->convenience_fee_float != 0.00) {
            $popupcontent.= '<label>$' . $credRecurring['amex'][0]->convenience_fee . ' + ' . $credRecurring['amex'][0]->convenience_fee_float . '%';
        } else {
            if ($credRecurring['amex'][0]->convenience_fee != 0.00)
                $popupcontent.= '<label>$' . $credRecurring['amex'][0]->convenience_fee;
            if ($credRecurring['amex'][0]->convenience_fee_float != 0.00)
                $popupcontent.='<label>' . $credRecurring['amex'][0]->convenience_fee_float . '%';
        }
        $popupcontent.='</label>';
        $popupcontent.='</div>';
        $popupcontent.= '</div>';
    }
}elseif (isset($credRecurring['amex'])) {
    for ($i = 0; $i < count($credRecurring['amex']); $i++) {
        $popupcontent.= '<br>';
        $popupcontent.= '<div class="row">';
        $popupcontent.='<div class="col-md-8">';
        $popupcontent.= '<img src="/img/american.png">&nbsp;';
        $popupcontent.= '<label> American Express  </label>';
        $popupcontent.= '<label> (';
        $popupcontent.= $credRecurring['amex'][$i]->low_pay_range . ' - ' . $credRecurring['amex'][$i]->high_pay_range;
        $popupcontent.= ')</label>';
        $popupcontent.='</div>';
        $popupcontent.='<div class="col-md-4">';
        if ($credRecurring['amex'][$i]->convenience_fee != 0.00 && $credRecurring['amex'][$i]->convenience_fee_float != 0.00) {
            $popupcontent.= '<label>$' . $credRecurring['amex'][$i]->convenience_fee . ' + ' . $credRecurring['amex'][$i]->convenience_fee_float . '%';
        } else {
            if ($credRecurring['amex'][$i]->convenience_fee != 0.00)
                $popupcontent.= '<label>$' . $credRecurring['amex'][$i]->convenience_fee;
            if ($credRecurring['amex'][$i]->convenience_fee_float != 0.00)
                $popupcontent.='<label>' . $credRecurring['amex'][$i]->convenience_fee_float . '%';
        }
        $popupcontent.='</label>';
        $popupcontent.='</div>';
        $popupcontent.='</div>';
    }
}


$popupcontent.="</div>";
$popupcontent.="<br>";
$popupcontent.="<br>";
?>
@include('popup.popupsuccessfee')

<!--include_once 'popupsuccessfee.php';-->


<?php if (isset($settlement_disclaimer) && !empty($settlement_disclaimer)) { ?>
    <div id="myDisclaimer" class="modal fade" style="display: none">
        <div class="modal-dialog modal2">
            <div class="modal-content">
                <div class="modal-header">
                    <button aria-label="Close" data-dismiss="modal" class="close" type="button"><span aria-hidden="true">×</span></button>
                    <h4 id="xpopupheaderfee" class="no-margin">Settlement Disclaimer</h4>
                </div>
                <div class="modal-body">
                    <?php echo $settlement_disclaimer; ?>
                </div>
                <div class="modal-footer">
                    <div class="row">
                        <div class="col-sm-12 btn-margin-xs-screen"><button type="button" class="btn btn-primary form-control btn-full" data-dismiss="modal">Close</button></div>

                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php
}
?>