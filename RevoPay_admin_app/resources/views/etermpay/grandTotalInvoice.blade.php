<div class="panel">
    <div class="panel-body">
        <div class="row total grey" id="xdiv_paymentamount">
            <div style="display: none">
                <div class="col-xs-6">
                    <label class="grey">Total Payment Amount:</label>
                </div>
                <div class="col-xs-6 text-right">
                    <label class="price no-margin" id="xprevtotal">$0.00</label>
                </div>
            </div>
        </div>
        <?php
        if ((!isset($credOneTime['ec']) || count($credOneTime['ec']) == 0) && (!isset($credOneTime['cc']) || count($credOneTime['cc']) == 0) && (!isset($credOneTime['amex']) || count($credOneTime['amex']) == 0) && (!isset($credRecurring['ec']) || count($credRecurring['ec']) == 0) && (!isset($credRecurring['cc']) || count($credRecurring['cc']) == 0) && (!isset($credRecurring['amex']) || count($credRecurring['amex']) == 0)) {

        } else {
            ?>

            <div class="row" id="xservice_fee">
                <div class="col-xs-6">
                    <h5 class="no-margin"><b>Convenience Fee:</b></h5>
                </div>
                <div class="col-xs-6 text-right " >
                    <h5 class="no-margin"><b><div id="xcfee">Select Payment Method</div></b></h5>
                </div>
            </div>
            <div class="row" id="xmodalservice_fee">
                <div class="col-xs-12">
                    <a type="button" style="cursor:pointer" class="underline" onclick="showModal('myModal_successfee')">Click here to learn more about this convenience fee</a>
                </div>
            </div>
            <hr style="margin-right: -15px; margin-left: -15px"/>
        <?php } ?>
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
        $popupcontent.='<div class="col-md-6">';
        $popupcontent.= '<img src="/img/echeck.png">&nbsp;';
        $popupcontent.= '<label>eCheck</label>';
        $popupcontent.='</div>';
        $popupcontent.='<div class="col-md-6">';
        $popupcontent.= '<label>$' . $credOneTime['ec'][0]->convenience_fee;
        if ($credOneTime['ec'][0]->convenience_fee_float != 0.00)
            $popupcontent.=' + ' . $credOneTime['ec'][0]->convenience_fee_float . '%';
        $popupcontent.='</label>';
        $popupcontent.='</div>';
    }
}elseif (isset($credOneTime['ec'])) {
    for ($i = 0; $i < count($credOneTime['ec']); $i++) {
        $popupcontent.= '<br>';
        $popupcontent.='<div class="col-md-8">';
        $popupcontent.= '<img src="/img/echeck.png">&nbsp;';
        $popupcontent.= '<label>eCheck</label>';
        $popupcontent.= '<label> (';
        $popupcontent.= $credOneTime['ec'][$i]->low_pay_range . ' - ' . $credOneTime['ec'][$i]->high_pay_range;
        $popupcontent.= ')</label>';
        $popupcontent.='</div>';
        $popupcontent.='<div class="col-md-4">';
        $popupcontent.= '<label>$' . $credOneTime['ec'][$i]->convenience_fee;
        if ($credOneTime['ec'][$i]->convenience_fee_float != 0.00)
            $popupcontent.=' + ' . $credOneTime['ec'][$i]->convenience_fee_float . '%';
        $popupcontent.='</label>';
        $popupcontent.='</div>';
    }
}


if (isset($credOneTime['cc']) && count($credOneTime['cc']) == 1) {
    if ($credOneTime['cc'][0]->convenience_fee + $credOneTime['cc'][0]->convenience_fee_float > 0) {
        $popupcontent.= '<br>';
        $popupcontent.='<div class="col-md-6">';
        $popupcontent.= '<img src="/img/visa.png">&nbsp;';
        $popupcontent.= '<label> Debit / Credit Card  </label>';
        $popupcontent.='</div>';
        $popupcontent.='<div class="col-md-6">';
        $popupcontent.= '<label>$' . $credOneTime['cc'][0]->convenience_fee;
        if ($credOneTime['cc'][0]->convenience_fee_float != 0.00)
            $popupcontent.=' + ' . $credOneTime['cc'][0]->convenience_fee_float . '%';
        $popupcontent.='</label>';
        $popupcontent.='</div>';
    }
}elseif (isset($credOneTime['cc'])) {
    for ($i = 0; $i < count($credOneTime['cc']); $i++) {
        $popupcontent.= '<br>';
        $popupcontent.='<div class="col-md-8">';
        $popupcontent.= '<img src="/img/visa.png">&nbsp;';
        $popupcontent.= '<label> Debit / Credit Card  </label>';
        $popupcontent.= '<label> (';
        $popupcontent.= $credOneTime['cc'][$i]->low_pay_range . ' - ' . $credOneTime['cc'][$i]->high_pay_range;
        $popupcontent.= ')</label>';
        $popupcontent.='</div>';
        $popupcontent.='<div class="col-md-4">';
        $popupcontent.= '<label>$' . $credOneTime['cc'][$i]->convenience_fee;
        if ($credOneTime['cc'][$i]->convenience_fee_float != 0.00)
            $popupcontent.=' + ' . $credOneTime['cc'][$i]->convenience_fee_float . '%';
        $popupcontent.='</label>';
        $popupcontent.='</div>';
    }
}


if (isset($credOneTime['amex']) && count($credOneTime['amex']) == 1) {
    if ($credOneTime['amex'][0]->convenience_fee + $credOneTime['amex'][0]->convenience_fee_float > 0) {
        $popupcontent.= '<br>';
        $popupcontent.='<div class="col-md-6">';
        $popupcontent.= '<img src="/img/american.png">&nbsp;';
        $popupcontent.= '<label> American Express </label>';
        $popupcontent.='</div>';
        $popupcontent.='<div class="col-md-6">';
        $popupcontent.= '<label>$' . $credOneTime['amex'][0]->convenience_fee;
        if ($credOneTime['amex'][0]->convenience_fee_float != 0.00)
            $popupcontent.=' + ' . $credOneTime['amex'][0]->convenience_fee_float . '%';
        $popupcontent.='</label>';
        $popupcontent.='</div>';
    }
}elseif (isset($credOneTime['amex'])) {
    for ($i = 0; $i < count($credOneTime['amex']); $i++) {
        $popupcontent.= '<br>';
        $popupcontent.='<div class="col-md-8">';
        $popupcontent.= '<img src="/img/visa.png">&nbsp;';
        $popupcontent.= '<label> American Express  </label>';
        $popupcontent.= '<label> (';
        $popupcontent.= $credOneTime['amex'][$i]->low_pay_range . ' - ' . $credOneTime['amex'][$i]->high_pay_range;
        $popupcontent.= ')</label>';
        $popupcontent.='</div>';
        $popupcontent.='<div class="col-md-4">';
        $popupcontent.= '<label>$' . $credOneTime['amex'][$i]->convenience_fee;
        if ($credOneTime['amex'][$i]->convenience_fee_float != 0.00)
            $popupcontent.=' + ' . $credOneTime['amex'][$i]->convenience_fee_float . '%';
        $popupcontent.='</label>';
        $popupcontent.='</div>';
    }
}


$popupcontent.="</div>";

$popupcontent.="<div id='xcfee_rc' style='display:none'>";
if (isset($credRecurring['ec']) && count($credRecurring['ec']) == 1) {
    if ($credRecurring['ec'][0]->convenience_fee + $credRecurring['ec'][0]->convenience_fee_float > 0) {
        $popupcontent.= '<br>';
        $popupcontent.='<div class="col-md-6">';
        $popupcontent.= '<img src="/img/echeck.png">&nbsp;';
        $popupcontent.= '<label> eCheck  </label>';
        $popupcontent.='</div>';
        $popupcontent.='<div class="col-md-6">';
        $popupcontent.= '<label>$' . $credRecurring['ec'][0]->convenience_fee;
        if ($credRecurring['ec'][0]->convenience_fee_float != 0.00)
            $popupcontent.=' + ' . $credRecurring['ec'][0]->convenience_fee_float . '%';
        $popupcontent.='</label>';
        $popupcontent.='</div>';
    }
}elseif (isset($credRecurring['ec'])) {
    for ($i = 0; $i < count($credRecurring['ec']); $i++) {
        $popupcontent.= '<br>';
        $popupcontent.='<div class="col-md-8">';
        $popupcontent.= '<img src="/img/echeck.png">&nbsp;';
        $popupcontent.= '<label>eCheck</label>';
        $popupcontent.= '<label> (';
        $popupcontent.= $credRecurring['ec'][$i]->low_pay_range . ' - ' . $credRecurring['ec'][$i]->high_pay_range;
        $popupcontent.= ')</label>';
        $popupcontent.='</div>';
        $popupcontent.='<div class="col-md-4">';
        $popupcontent.= '<label>$' . $credRecurring['ec'][$i]->convenience_fee;
        if ($credRecurring['ec'][$i]->convenience_fee_float != 0.00)
            $popupcontent.=' + ' . $credRecurring['ec'][$i]->convenience_fee_float . '%';
        $popupcontent.='</label>';
        $popupcontent.='</div>';
    }
}


if (isset($credRecurring['cc']) && count($credRecurring['cc']) == 1) {
    if ($credRecurring['cc'][0]->convenience_fee + $credRecurring['cc'][0]->convenience_fee_float > 0) {
        $popupcontent.= '<br>';
        $popupcontent.='<div class="col-md-6">';
        $popupcontent.= '<img src="/img/visa.png">&nbsp;';
        $popupcontent.= '<label> Debit / Credit Card  </label>';
        $popupcontent.='</div>';
        $popupcontent.='<div class="col-md-6">';
        $popupcontent.= '<label>$' . $credRecurring['cc'][0]->convenience_fee;
        if ($credRecurring['cc'][0]->convenience_fee_float != 0.00)
            $popupcontent.=' + ' . $credRecurring['cc'][0]->convenience_fee_float . '%';
        $popupcontent.='</label>';
        $popupcontent.='</div>';
    }
}elseif (isset($credRecurring['cc'])) {
    for ($i = 0; $i < count($credRecurring['cc']); $i++) {
        $popupcontent.= '<br>';
        $popupcontent.='<div class="col-md-8">';
        $popupcontent.= '<img src="/img/visa.png">&nbsp;';
        $popupcontent.= '<label> Debit / Credit Card  </label>';
        $popupcontent.= '<label> (';
        $popupcontent.= $credRecurring['cc'][$i]->low_pay_range . ' - ' . $credRecurring['cc'][$i]->high_pay_range;
        $popupcontent.= ')</label>';
        $popupcontent.='</div>';
        $popupcontent.='<div class="col-md-4">';
        $popupcontent.= '<label>$' . $credRecurring['cc'][$i]->convenience_fee;
        if ($credRecurring['cc'][$i]->convenience_fee_float != 0.00)
            $popupcontent.=' + ' . $credRecurring['cc'][$i]->convenience_fee_float . '%';
        $popupcontent.='</label>';
        $popupcontent.='</div>';
    }
}


if (isset($credRecurring['amex']) && count($credRecurring['amex']) == 1) {
    if ($credRecurring['amex'][0]->convenience_fee + $credRecurring['amex'][0]->convenience_fee_float > 0) {
        $popupcontent.= '<br>';
        $popupcontent.='<div class="col-md-6">';
        $popupcontent.= '<img src="/img/american.png">&nbsp;';
        $popupcontent.= '<label> American Express </label>';
        $popupcontent.='</div>';
        $popupcontent.='<div class="col-md-6">';
        $popupcontent.= '<label>$' . $credRecurring['amex'][0]->convenience_fee;
        if ($credRecurring['amex'][0]->convenience_fee_float != 0.00)
            $popupcontent.=' + ' . $credRecurring['amex'][0]->convenience_fee_float . '%';
        $popupcontent.='</label>';
        $popupcontent.='</div>';
    }
}elseif (isset($credRecurring['amex'])) {
    for ($i = 0; $i < count($credRecurring['amex']); $i++) {
        $popupcontent.= '<br>';
        $popupcontent.='<div class="col-md-8">';
        $popupcontent.= '<img src="/img/visa.png">&nbsp;';
        $popupcontent.= '<label> American Express  </label>';
        $popupcontent.= '<label> (';
        $popupcontent.= $credRecurring['amex'][$i]->low_pay_range . ' - ' . $credRecurring['amex'][$i]->high_pay_range;
        $popupcontent.= ')</label>';
        $popupcontent.='</div>';
        $popupcontent.='<div class="col-md-4">';
        $popupcontent.= '<label>$' . $credRecurring['amex'][$i]->convenience_fee;
        if ($credRecurring['amex'][$i]->convenience_fee_float != 0.00)
            $popupcontent.=' + ' . $credRecurring['amex'][$i]->convenience_fee_float . '%';
        $popupcontent.='</label>';
        $popupcontent.='</div>';
    }
}


$popupcontent.="</div>";
$popupcontent.="<br>";
$popupcontent.="<br>";
?>
@include('popup.popupsuccessfee')

