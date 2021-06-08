<?php
if (count($credOneTime) > 0 || count($credRecurring) > 0) {
    echo '<label class="blue">Pay with a new payment method </label><br /><br />';
    if (isset($credOneTime['ec']) || isset($credRecurring['ec'])) {
        ?>
        <div class="row" id="xpaymeth_ec">
            <div class="area_action_check" data="checkbox4">
                <div class="col-xs-1 ">
                    <div class="checkbox checkbox-info" style="margin-top: 3px" >
                        <input type="checkbox" class="styled collapse-action1" id="checkbox4" onclick="CalculaFeeX(1);" data-toggle="collapse" data-target="#collapse1" >
                        <label></label>
                    </div>
                </div>
                <div class="col-xs-11 collapse-action1 <?php
                if (!isset($credOneTime['ec']) && !isset($credRecurring['ec'])) {
                    echo 'hidden';
                }
                ?>" data-toggle="collapse" data-target="#collapse1" >
                    <div class="combo_image" data="checkbox4">
                        <img src="<?php echo asset('img/echeck.png'); ?>">&nbsp;&nbsp;&nbsp;<label id="xnewaddec">Pay with Bank Account</label>
                    </div>

                </div>
            </div>
            <div class="row" style="margin: 0!important;">
                <div class="col-xs-12 collapse" id="collapse1" style="padding-top: 10px" aria-expanded="false">
                    <div class="row">
                        <div class="col-sm-8">
                            <input class="form-control" placeholder="Name on Checking or Savings Account" id="xppec_name" onblur="validate_ecname();">
                            <br/>
                        </div>
                        <div class="col-sm-4 form-group">
                            <select class="selectpicker" id="xppec_type">
                                <option>Checking</option>
                                <option>Savings</option>
                            </select>
                            <br/>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-6">
                            <input class="form-control" placeholder="9 Digits Routing Number" id="xppec_routing" onblur="validate_aba();">
                            <br/>
                        </div>
                        <div class="col-sm-6">
                            <input class="form-control" placeholder="Account Number" id="xppec_acc" onblur="validate_bank();">
                            <br/>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-6">

                        </div>
                        <div class="col-sm-6">
                            <input class="form-control" onpaste="return false;" placeholder="Confirm Account Number" id="xppec_confirm_acc" onblur="validate_bank();">
                            <br/>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }
    if (isset($credOneTime['ec']) || isset($credRecurring['ec']) && isset($credOneTime['cc']) || isset($credOneTime['amex']) || isset($credRecurring['amex']) || isset($credRecurring['cc'])) {
        //or
        ?>
        <div id="xpaymeth_hr">
            <hr style="margin: 5px 0 20px" class=" ">
            <div class="text-center  " style="width: 100%; margin-top: -30px">
                <label class="no-margin small grey" style="background-color: #ffffff; padding: 0 12px">Or</label>
            </div>
            <br/>
        </div>
        <?php
    }
    if (isset($credOneTime['cc']) || isset($credOneTime['amex']) || isset($credRecurring['amex']) || isset($credRecurring['cc'])) {
        ?>
        <div class="row" id="xpaymeth_cc">

            <div class="area_action_check" data="checkbox5">
                <div class="col-xs-1">
                    <div class="checkbox checkbox-info" style="margin-top: 3px">
                        <input type="checkbox" class="styled collapse-action2" id="checkbox5"  data-toggle="collapse" data-target="#collapse2" onclick="CalculaFeeX(2);">
                        <label></label>
                    </div>
                </div>
                <div class="col-xs-11">
                    <div class="combo_image" style="margin-top: 2px">
                        <span class="glyphicon glyphicon-credit-card hide-xs-screen grey" style="font-size: 20px; margin: 0 14px 0 0;vertical-align: top"></span> <span class="hide-xs-screen"><label id="xnewaddcc">Pay with</label></span>
                        <?php
                        if (isset($credOneTime['cc']) || isset($credRecurring['cc'])) {
                            echo '<img src="' . asset('img/visa.png') . '"> ';
                            echo ' <img src="' . asset('img/mastercard.png') . '"> ';
                            echo ' <img src="' . asset('img/discover.png') . '"> ';
                        }
                        if (isset($credOneTime['amex']) || isset($credRecurring['amex']))
                            echo ' <img src="' . asset('img/american.png') . '" id="ximgamex">';
                        ?>
                    </div>
                </div>
            </div>
            <div class="row " style="margin: 0 -8px!important;">
                <div class="col-xs-12 collapse" id="collapse2" style="padding-top: 20px" aria-expanded="false">

                    <div class="">
                        <div class="col-md-12">
                            <label>Name on Card</label>
                            <input placeholder="Name on Card" class="form-control" id="xcardname" onblur="validate_ccname();"/>
                            <br/>
                        </div>
                        <div class="col-md-12">
                            <label>Your Card Number</label>
                            <div class="input-group">
                                <span style="background-color: transparent" class="input-group-addon glyphicon glyphicon-credit-card"></span>
                                <input type="text" aria-describedby="basic-addon1" placeholder="Card Number" class="form-control" id="xcardnumber" onblur="validate_ccard();get_cctype()" onkeyup="CalculaFeeX(2)">
                            </div>
                            <br/>
                        </div>
                        <div class="col-md-12">
                            <div class="row">
                                <div class="col-xs-6">
                                    <label>Exp. Date</label>
                                    <input class="form-control" placeholder="MMYY" id="xexpdate" onblur="validate_expdate();">
                                    <br/>
                                </div>
                                <div class="col-xs-6">
                                    <label>Zip</label>
                                    <input class="form-control" placeholder="12345" id="xzip1" onblur="validate_zip1()">
                                    <br/>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>

        <?php
    }
    if (isset($credOneTime['cc']) || isset($credRecurring['amex']) && isset($credOneTime['amex']) || isset($credOneTime['cc']) || isset($credRecurring['amex']) || isset($credRecurring['cc'])) {
        if (isset($credOneTime['swipe']) || isset($credRecurring['swipe'])) {
            //or
            ?>
            <div id="xpaymeth_hr">
                <hr style="margin: 5px 0 20px" class=" ">
                <div class="text-center  " style="width: 100%; margin-top: -30px">
                    <label class="no-margin small grey" style="background-color: #ffffff; padding: 0 12px">Or</label>
                </div>
                <br/>
            </div>
            <?php
        }
    }
    //for swipe edit box for get track data:AJ - Accepted Payment
    if (isset($credOneTime['swipe'])) {
        ?>
        <div class="row" id="xpaymeth_dc">

            <div class="area_action_check" data="checkbox6">
                <div class="col-xs-1">
                    <div class="checkbox checkbox-info" style="margin-top: 3px">
                        <input type="checkbox" class="styled collapse-action5" id="checkbox6"  data-toggle="collapse" data-target="#collapse5" onclick="CalculaFeeX(5);focusswipe();checkswipedata1();">
                        <label></label>
                    </div>
                </div>
                <div class="col-xs-8">
                    <div class="combo_image" style="margin-top: 2px">
        <!--                        <span class="glyphicon glyphicon-credit-card hide-xs-screen grey" style="font-size: 20px; margin: 0 14px 0 0;vertical-align: top"></span> <span class="hide-xs-screen"><label id="xnewaddcc">Card Swipe</label></span>-->

                        <img src="<?php echo asset('img/swipe.png'); ?>">&nbsp;&nbsp;&nbsp;<label id="xnewaddsipe">Card Swipe</label>
                    </div>
                </div>

            </div>
            <div class="col-xs-3" id="swipeagain" hidden="true">
                <button type="button" id="btnswipeagain" onclick="swipeagain();" class="btn btn-primary btn-block btn-outline">Swipe Again</button>
            </div>
            <div class="row">
                <div class="col-xs-12 collapse" id="collapse5" aria-expanded="false">

                    <div class="">
                        <div class="col-md-9 form-group">
                            <input type="text" placeholder="Swipe Data" id="strackdata" tabindex="1" onblur="" style="opacity:0;filter:alpha(opacity=0)"/>
                            <div id="swipemessage"></div>
                            <div id="swipeagainmessage"></div>
                        </div>


                    </div>

                </div>
            </div>
        </div>

        <?php
    }
} else {
    echo "<div class='alert alert-info'> No credentials to make a payment, Please Contact your Payment Provider</div>";
}
?>