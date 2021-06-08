@inject('Layout', 'App\Helpers\Layout')
@php
$layout_values = $Layout->getLayout($token);
@endphp

<div class="row radio-cont" id="step3b" >


    <div class="col-md-7" >
        <?php ?>
        @include('eterminal.showbalance')
        @include('etermpay.eservices')

        <?php if ($existsDrp == 1) {
            ?>
            @include('eterminal.paymentsInfo')

            <?php echo '<div class="collapse in" id="payment-details">'; ?>
            @include('eterminal.eterm_payType')

            <?php
            echo '</div>';
        } else {
            ?>
            @include('eterminal.paymentsInfo')

            <?php echo '<br />'; ?>

            @include('eterminal.eterm_payType')
        <?php }
        ?>



        <?php echo $profiles; ?>
        <?php echo $profiles1; ?>
        <div>
            @include('etermpay.eterm_paymethd')


        </div>
        <br>
        <div class="checkbox checkbox-info" id="xsaprofile">
            <input type="checkbox"  value="" id="xsaveprofile_id" name="xsaveprofile_name" class="styled styled-primary checkbox-active-input">
            <label for="xsaveprofile_id" class="blue"><b>Save this payment method</b></label>
        </div>
        <br>
        <div>
            <p class="grey small">By selecting "Save this payment method", you agree to our <a href="http://revopay.com/docs/terms-conditions" target="_blank">Payment Terms &amp; Conditions</a></p>
        </div>
        <br>

        @include('etermpay.grandTotal')
        <!--        include_once __DIR__ . '/../components/grandTotal.php';-->



        <div>
            <a type="button" onclick="xegostep4('cust')" class="btn btn-primary" id="xReview1">Review and Approve</a>
            <a type="button" onclick="window.history.back();" class="btn btn-default btn-full" id="xnext_step2" style="max-width: 100px;">Go Back</a>
        </div>


    </div>
    <div class="col-md-5 hidden-xs border-right-cont2 " >
        <div class="row">
            <div class="col-xs-6 form-group text-left">
                <h4 class="no-margin" style=""><p><b class="small-font-3 grey">&nbsp;</b></p></h4>
            </div>
            <div class="col-xs-6 form-group text-right">
                <h4 class="no-margin" style=""><p><b class="small-font-3 grey">&nbsp;</b></p></h4>
            </div>

        </div>
        <div>
            <div class="panel panel-default" style="border: none; box-shadow: none; background-color: #F4F4F4; padding: 15px">
                <div class="panel-body" style="font-size: 12px">

                <label class="blue">
                    <?php
                    if (empty($layout_values['label_user']))
                        $layout_values['label_user'] = "Payor";
                    echo $layout_values['label_user'];
                    ?> Information
                </label><br/>
                <div id="xpayorinfo3b">
                    <?php echo $str_custom; ?>
                    <label class="blue"><?php
                        if (empty($layout_values['label_merchant']))
                            $layout_values['label_merchant'] = "Property";
                        echo $layout_values['label_merchant'];
                        ?> Information</label><br/>
                    <?php echo $merchant['name_clients']; ?><br/>
                    <?php
                    if ($merchant['city_clients'] != '') {
                        echo $merchant['city_clients'] . ",";
                    }
                    ?>
                    <?php echo $merchant['state_clients']; ?> <?php echo $merchant['zip_clients']; ?><br/>
                </div>

                </div>
            </div>
        </div>
        <div>
            @include('etermpay.eterm_payhistory')
            <!--            include_once __DIR__ . '/../admin_components/eterm_payhistory.php'; -->
        </div>

    </div>
</div>








