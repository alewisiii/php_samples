<script> var isrecurring = 0;</script>


<div class="panel panel-default" style="border: none; box-shadow: none; background-color: #F4F4F4; padding: 15px">
    <div class="panel-body" style="font-size: 12px">

        <div class="btn-group btn-group-justified <?php
        if (isset($hideSelectorType) && $hideSelectorType) {
            echo 'hidden';
        }
        ?>">
            <a id="xonetime" class="btn group-btn-focused group-btn1 btn-info <?php
            if (empty($credOneTime)) {
                echo 'disabled';
            }
            ?>" onclick="javascript:$('#recurring-payment').collapse('hide');$('#payment-details').collapse('show');isrecurring = 0;refreshOneTimeDate();changetext(1);">One time<span class="hide-xs-screen-2"> Payment</span></a>
            <a id="xrecurring" class="btn btn-default  group-btn-focused group-btn2 <?php
            if ((isset($existsAutopay) && $existsAutopay != 1 && isset($existsDrp) && $existsDrp != 1) || (empty($credRecurring))) {
                echo 'disabled';
            }
            ?>" data-toggle="collapse" data-target="#recurring-payment" onclick="javascript:$('#payment-details').collapse('hide'); isrecurring = 1; refreshRecurringDate();changetext(2);ChangeIcon();">Schedule <span class="hide-xs-screen-2">AutoPay</span></a>
        </div><br/>
        <div class="row">
            <div class="col-xs-5" id="xtextlabel"><h4 class="no-margin"><p><b class="small-font-2">Select Payment Date:</b></p></h4></div>
            <div class="col-xs-7 text-center"><h4 class="no-margin"><p><b id="date-label" data-ref="" class="small-font-2 datepicker-control"></b> &nbsp;<img data-ref="date-label" id="onedatep" class="datepicker-control <?php
                        if (isset($hideSelectorDate)) {
                            echo 'hidden';
                        }
                        ?>" src="<?php echo asset('img/calendar.png'); ?>"><img data-ref="date-label" id="autodatep" class="datepicker-control <?php
                                                                                                                                                                              if (isset($hideSelectorDate)) {
                                                                                                                                                                                  echo 'hidden';
                                                                                                                                                                              }
                                                                                                                                                                              ?>" src="/img/calendar.png" style="display:none;"><?php
                                                                                                                                                                          if (isset($existsDrp) && $existsDrp == 1) {
                                                                                                                                                                              echo '<img data-ref="date-label" id="drpdatep" class="datepicker-control" src="/img/calendar.png" style="display:none;">';
                                                                                                                                                                          }
                                                                                                                                                                          ?></p></h4></div>
        </div>

        <div class="collapse" id="recurring-payment">
            <br/>

            <?php if (isset($existsDrp) && $existsDrp == 1) { ?>
                <div class="radio radio-primary radio-inline">
                    <input class="radio-panel" type="radio" id="inlineRadio1" value="option1" name="radioInline"   checked="checked">
                    <label class="" for="inlineRadio1"> I want to be automatically debited for my full balance. <a data-toggle="tooltip" title="" class="underline tooltip_click tooltip1" data-original-title="By checking this option, I hereby authorize <?php
                        if (isset($merchant['name_clients']))
                            echo $merchant['name_clients'];
                        else
                            echo "RevoPay"
                            ?> to charge my payment method for my total balance plus any associated convenience fees on the schedule I establish below. I understand that this Auto Payment authorization can be canceled at any time by going to Manage AutoPay and clicking on cancel. ">Terms</a> </label>
                </div>
                <br/>
            <?php } if (isset($existsAutopay) && $existsAutopay == 1 && isset($existsDrp) && $existsDrp == 1) { ?>
                <br/>
                <div class="radio radio-primary radio-inline">
                    <input class="radio-panel" type="radio" id="inlineRadio2" value="option2" name="radioInline" >
                    <label class="" for="inlineRadio2"> I want to schedule a fixed $ payment amount.  <a data-toggle="tooltip" title="" class="underline tooltip_click tooltip2" data-original-title="After clicking on this option, you will only be charged the fixed payment amount you select. If a balance is owed, you will be responsible for updating your payment amount to avoid any potential late fees.">Learn More</a></label>
                </div>
                <br/>
                <?php
            }
            if ($data['existsAutopay'] == 1 || $data['existsDrp'] == 1) {
                ?>
                <br/>
                <div class="row form-group">
                    <div class="col-xs-6 text-center"><label class="margin10">Frequency</label></div>
                    <div class="col-xs-6">

                        <select class="selectpicker bs-select-hidden" id="xfreq">
                            <?php
                            if (isset($data['existsDrp']) && $data['existsDrp'] == 1) {
                                foreach ($data['freqDrp'] as $key => $value) {
                                    echo '<option value="' . trim($key) . '">' . $value . '</option>';
                                }
                            } else {
                                foreach ($data['freqAutopay'] as $key => $value) {
                                    echo '<option value="' . trim($key) . '">' . $value . '</option>';
                                }
                            }
                            ?>
                        </select>

                        <br/>
                    </div>
                </div>
                <div class="row form-group">
                    <div class="col-xs-6 text-center"><label class="margin10">End Date</label></div>
                    <div class="col-xs-6">
                        <select class="form-control selectpicker bs-select-hidden" id="xrenddate">
                            <?php
                            for ($i = 0; $i < count($data['enddate']); $i++) {
                                echo '<option value="' . $data['enddate'][$i]['value'] . '">' . $data['enddate'][$i]['date'] . '</option>';
                            }
                            ?>
                        </select>
                    </div>
                </div>
                <div class="row">
                    <div class="col-xs-12">
                        @include('components.frecuenciesInfo')
                    </div>
                </div>
                <?php
            } else {
                $removebutton1 = true;
                ?>
                <div class="row">
                    <div class="alert alert-warning">
                        <p>IMPORTANT! You currently have reached the limit for active Auto-Payments authorizing charges on your account. Thank you!</p>
                    </div>
                </div>
                <?php
            }
            ?>
            <br/>

        </div>
    </div>
</div>

<script>
    if (!array_credOneTime) {
        var array_credOneTime = <?php
            if (isset($array_credOneTime)) {
                echo $array_credOneTime;
            } else {
                echo '{}';
            }
            ?>;
    }
    if (!array_credRecurring) {
        var array_credRecurring = <?php
            if (isset($array_credRecurring)) {
                echo $array_credRecurring;
            } else {
                echo '{}';
            }
            ?>;
    }
    if (!array_ecOneTime) {
        var array_ecOneTime = <?php
            if (isset($array_ecOneTime)) {
                echo $array_ecOneTime;
            } else {
                echo '{}';
            }
            ?>;
    }
    if (!array_ecRecurring) {
        var array_ecRecurring = <?php
            if (isset($array_ecRecurring)) {
                echo $array_ecRecurring;
            } else {
                echo '{}';
            }
            ?>;
    }
    if (!array_credOneTimeAmex) {
        var array_credOneTimeAmex = <?php
            if (isset($array_credOneTimeAmex)) {
                echo $array_credOneTimeAmex;
            } else {
                echo '{}';
            }
            ?>;
    }

    if (!array_credRecurringAmex) {
        var array_credRecurringAmex = <?php
            if (isset($array_credRecurringAmex)) {
                echo $array_credRecurringAmex;
            } else {
                echo '{}';
            }
            ?>;
    }

</script>