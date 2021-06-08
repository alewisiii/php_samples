@extends('layouts.master')
@section('title','Email Template')
@section('content')
<div class="normalheader">
    <div class="hpanel">
        <div class="panel-body">
            <a class="small-header-action" href="">
                <div class="clip-header">
                    <i class="fa fa-arrow-up"></i>
                </div>
            </a>

            <h2 class="font-light m-b-xs">
                Email Template
            </h2>
            <small>
                <ol class="hbreadcrumb breadcrumb">
                    <li><a href="<?php echo route('dashboard', array('token' => $token)); ?>">Dashboard</a></li>
                    <li class="active">
                        <span>Email Template</span>
                    </li>
                </ol>
            </small>
        </div>
    </div>
</div>
@include('components.tinymce')
<div class="content">
    <div class="hpanel">
        <div class="panel-body">

        <form method="post" action="<?php echo route('saveetemplates'); ?>" id="formNotification">
            <input type="hidden" name="_token" value="<?php echo csrf_token(); ?>" >
            <input type="hidden" name="tokendata" value="<?php echo $token; ?>" >

            <div class="row">
                <div class="col-md-9">
                    <ul class="nav nav-tabs" role="tablist">
                        <li id="pr-tab" class="active">
                            <a  onclick="prc()">Payment Receipts</a>
                        </li>
                        <li id="nt-tab" class="">
                            <a onclick="ntc()">Notifications</a>
                        </li>
                      </ul>
                    <div class="tab-content" id="myTabContent">
                        <div class="tab-pane" id="prtab" >
                            <br>
                           <?php if (isset($settings['SUCCESSFULEMAIL'])): ?>
                                <div class="row">
                                    <div class="col-lg-12">
                                        <b>Successful Payment Receipt</b>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-xs-12">
                                        <label>Subject:</label>
                                        <input type="text" class="form-control" id="SUCCESSFULEMAIL_SUBJECT" name="SUCCESSFULEMAIL_SUBJECT" value="<?php echo $settings['SUCCESSFULEMAIL_SUBJECT']; ?>">
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-xs-12">
                                        <br><textarea id="SUCCESSFULEMAIL" name="SUCCESSFULEMAIL" class="form-control tinymce-editor" style="min-height: 200px;"><?php echo $settings['SUCCESSFULEMAIL']; ?></textarea>
                                    </div>
                                </div>
                                <br>
                            <?php endif; ?>
                            <?php if (isset($settings['UNSUCCESSFULEMAIL'])): ?>
                                <div class="row">
                                    <div class="col-xs-12">
                                        <b>Unsuccessful Payment Receipt</b>
                                        <br/>
                                        <label>Subject:</label>
                                        <input class="form-control" type="text" id="UNSUCCESSFULEMAIL_SUBJECT" name="UNSUCCESSFULEMAIL_SUBJECT" value="<?php echo $settings['UNSUCCESSFULEMAIL_SUBJECT']; ?>">
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-xs-12">
                                        <br><textarea id="UNSUCCESSFULEMAIL" name="UNSUCCESSFULEMAIL" class="form-control tinymce-editor" style="min-height: 200px;"><?php echo $settings['UNSUCCESSFULEMAIL']; ?></textarea>
                                    </div>
                                </div>

                                <br/>
                            <?php endif; ?>
                            <div class="row">
                                <div class="col-lg-12">
                                    <b>Setup Scheduled Payment Receipt</b>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-xs-12">
                                    <label>Subject:</label>
                                    <input type="text" class="form-control" id="AUTOSUCCESSFULEMAIL_SUBJECT" name="AUTOSUCCESSFULEMAIL_SUBJECT" value="<?php echo $settings['AUTOSUCCESSFULEMAIL_SUBJECT']; ?>">
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-xs-12">
                                    <br><textarea id="AUTOSUCCESSFULEMAIL" name="AUTOSUCCESSFULEMAIL" class="form-control tinymce-editor" style="min-height: 200px;"><?php echo $settings['AUTOSUCCESSFULEMAIL']; ?></textarea>
                                </div>
                            </div>
                            <br> 
                            <div class="row">
                                <div class="col-lg-12">
                                    <b>Voided Payment Receipt</b>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-xs-12">
                                    <label>Subject:</label>
                                    <input type="text" class="form-control" id="VOIDSUCCESSFULEMAIL_SUBJECT" name="VOIDSUCCESSFULEMAIL_SUBJECT" value="<?php echo $settings['VOIDSUCCESSFULEMAIL_SUBJECT']; ?>">
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-xs-12">
                                    <br><textarea id="VOIDSUCCESSFULEMAIL" name="VOIDSUCCESSFULEMAIL" class="form-control tinymce-editor" style="min-height: 200px;"><?php echo $settings['VOIDSUCCESSFULEMAIL']; ?></textarea>
                                </div>
                            </div>
                            <br>
                            <div class="row">
                                <div class="col-lg-12">
                                    <b>Refunded Payment Receipt</b>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-xs-12">
                                    <label>Subject:</label>
                                    <input type="text" class="form-control" id="REFUNDSUCCESSFULEMAIL_SUBJECT" name="REFUNDSUCCESSFULEMAIL_SUBJECT" value="<?php echo $settings['REFUNDSUCCESSFULEMAIL_SUBJECT']; ?>">
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-xs-12">
                                    <br><textarea id="REFUNDSUCCESSFULEMAIL" name="REFUNDSUCCESSFULEMAIL" class="form-control tinymce-editor" style="min-height: 200px;"><?php echo $settings['REFUNDSUCCESSFULEMAIL']; ?></textarea>
                                </div>
                            </div>
                            <br>
                        </div>
                        <div class="tab-pane" id="nttab" >
                            <br>
                            <div class="row">
                                <div class="col-xs-12 form-group">
                                    <label>
                                        This is the email address that will be used in the "from:" field when reminding to customer autopayment:
                                    </label>
                                </div>
                                <div class="col-xs-4 form-group">
                                    <input id="default_from_reminder" name="default_from_reminder" class="form-control" type="text"  value="<?php if(isset($settings['DEFAULT_FROM_REMINDER'])){ echo $settings['DEFAULT_FROM_REMINDER'];}else { echo 'do_not_reply@revopay.com';} ?>">
                                </div>
                            </div>
                            <?php if (isset($settings['RECCURRINGENDEMAIL'])): ?>
                                <div class="row">
                                    <div class="col-xs-12">
                                        <b>Completed Scheduled Payment</b>
                                        <br/>
                                        <label>Subject:</label>
                                        <input type="text" class="form-control" id="RECCURRINGENDEMAIL_SUBJECT" name="RECCURRINGENDEMAIL_SUBJECT" value="<?php echo $settings['RECCURRINGENDEMAIL_SUBJECT']; ?>"><br>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-xs-12">
                                        <br><textarea id="RECCURRINGENDEMAIL" name="RECCURRINGENDEMAIL" class="form-control tinymce-editor" style="min-height: 200px;"><?php echo $settings['RECCURRINGENDEMAIL']; ?></textarea>
                                        <br/>
                                    </div>
                                </div>
                            <?php endif; ?>
                            <?php if (isset($settings['RECURRINGEXPIRESEMAIL'])): ?>
                                <div class="row">
                                    <div class="col-xs-12">
                                        <b>Close to expire Scheduled payment</b>
                                        <br/>
                                        <label>Subject:</label>
                                        <input class="form-control" type="text" id="RECURRINGEXPIRESEMAIL_SUBJECT" name="RECURRINGEXPIRESEMAIL_SUBJECT" value="<?php echo $settings['RECURRINGEXPIRESEMAIL_SUBJECT']; ?>"> <br>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-xs-12">
                                        <br><textarea id="RECURRINGEXPIRESEMAIL" name="RECURRINGEXPIRESEMAIL" class="form-control tinymce-editor" style="min-height: 200px;"><?php echo $settings['RECURRINGEXPIRESEMAIL']; ?></textarea>
                                        <br/>
                                    </div>
                                </div>
                            <?php endif; ?>
                            <?php if (isset($settings['INSFEMAIL'])): ?>
                                <div class="row">
                                    <div class="col-xs-12">
                                        <b>Returned payment</b><br/>
                                        <label>Subject:</label>
                                        <input type="text" id="INSFEMAIL_SUBJECT" name="INSFEMAIL_SUBJECT" class="form-control" value="<?php echo $settings['INSFEMAIL_SUBJECT']; ?>"> <br>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-xs-12">
                                        <br><textarea id="INSFEMAIL" name="INSFEMAIL" class="form-control tinymce-editor" style="min-height: 200px;"><?php echo $settings['INSFEMAIL']; ?></textarea>
                                        <br/>
                                    </div>
                                </div>
                            <?php endif; ?>
                            <div class="row">
                                <div class="col-xs-12">
                                    <b>Expired Card Notification</b><br/>
                                    <label>Subject:</label>
                                    <input type="text" id="EXPCARD_SUBJECT" name="EXPCARD_SUBJECT" class="form-control" value="<?php echo $settings['EXPCARD_SUBJECT']; ?>"> <br>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-xs-12">
                                    <br><textarea id="EXPCARDEMAIL" name="EXPCARDEMAIL" class="form-control tinymce-editor" style="min-height: 200px;"><?php echo $settings['EXPCARDEMAIL']; ?></textarea>
                                    <br/>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-xs-12">
                                    <b>Card close to Expire Notification</b><br/>
                                    <label>Subject:</label>
                                    <input type="text" id="TOEXP_SUBJECT" name="TOEXP_SUBJECT" class="form-control" value="<?php echo $settings['TOEXP_SUBJECT']; ?>"> <br>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-xs-12">
                                    <br><textarea id="TOEXPEMAIL" name="TOEXPEMAIL" class="form-control tinymce-editor" style="min-height: 200px;"><?php echo $settings['TOEXPEMAIL']; ?></textarea>
                                    <br/>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-xs-12">
                                    <b>Invoice Cancelled Notification</b><br/>
                                    <label>Subject:</label>
                                    <input type="text" id="INVOICECANCEL_SUBJECT" name="INVOICECANCEL_SUBJECT" class="form-control" value="<?php if(isset($settings['INVOICECANCEL_SUBJECT'])) echo $settings['INVOICECANCEL_SUBJECT']; ?>"> <br>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-xs-12">
                                    <br><textarea id="INVOICECANCELMAIL" name="INVOICECANCELMAIL" class="form-control tinymce-editor" style="min-height: 200px;"><?php if(isset($settings['INVOICECANCELMAIL'])) echo $settings['INVOICECANCELMAIL']; ?></textarea>
                                    <br/>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-xs-12">
                                    <b>Reminder Scheduled Payment</b><br/>
                                    <label>Subject:</label>
                                    <input type="text" id="AUTOREMINDEREMAIL_SUBJECT_TEMPLATE" name="AUTOREMINDEREMAIL_SUBJECT_TEMPLATE" class="form-control" value="<?php if(isset($settings['AUTOREMINDEREMAIL_SUBJECT_TEMPLATE'])) echo $settings['AUTOREMINDEREMAIL_SUBJECT_TEMPLATE']; ?>"> <br>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-xs-12">
                                    <br><textarea id="AUTOREMINDEREMAILTEMPLATE" name="AUTOREMINDEREMAILTEMPLATE" class="form-control tinymce-editor" style="min-height: 200px;"><?php if(isset($settings['AUTOREMINDEREMAILTEMPLATE'])) echo $settings['AUTOREMINDEREMAILTEMPLATE']; ?></textarea>
                                    <br/>
                                </div>
                            </div>
                            <?php if (isset($settings['PASSRESETEMAIL'])): ?>
                                <div class="row">
                                    <div class="col-xs-12">
                                        <b>Reset Password</b><br/>
                                        <label>Subject:</label>
                                        <input type="text" id="PASSRESETEMAIL_SUBJECT" name="PASSRESETEMAIL_SUBJECT" class="form-control" value="<?php echo $settings['PASSRESETEMAIL_SUBJECT']; ?>"> <br>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-xs-12">
                                        <br><textarea id="PASSRESETEMAIL" name="PASSRESETEMAIL" class="form-control tinymce-editor" style="min-height: 200px;"><?php echo $settings['PASSRESETEMAIL']; ?></textarea>
                                        <br/>
                                    </div>
                                </div>
                            <?php endif; ?>
                            <?php if (isset($settings['PASSCHANGEEMAIL'])): ?>
                                <div class="row">
                                    <div class="col-xs-12">
                                        <b>Change Password</b><br/>
                                        <label>Subject:</label>
                                        <input type="text" id="PASSCHANGEEMAIL_SUBJECT" name="PASSCHANGEEMAIL_SUBJECT" class="form-control" value="<?php echo $settings['PASSCHANGEEMAIL_SUBJECT']; ?>"> <br>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-xs-12">
                                        <br><textarea id="PASSCHANGEEMAIL" name="PASSCHANGEEMAIL" class="form-control tinymce-editor" style="min-height: 200px;"><?php echo $settings['PASSCHANGEEMAIL']; ?></textarea>
                                        <br/>
                                    </div>
                                </div>
                            <?php endif; ?>
                          
                        </div>
                      </div>
                </div>

                <div class="col-md-3">
                    <div class="panel panel-default" style="border: none; box-shadow: none; background-color: #F4F4F4; padding: 15px">
                    <div class="panel-body" style="font-size: 12px">

                        The following fields can be used in the email templates:<br><br>
                        <div data-toggle="tooltip" data-placement="left" title="place Merchant Logo here">[:LOGO:]</div>
                        <div data-toggle="tooltip" data-placement="left" title="place link to create a customer ticket here">[:TICKETLINK:]</div>
                        <div data-toggle="tooltip" data-placement="left" title="print transaction date here">[:TRANS_DATE:]</div>
                        <div data-toggle="tooltip" data-placement="left" title="print transaction reference number here">[:REFNUM:]</div>
                        <div data-toggle="tooltip" data-placement="left" title="print transaction authorization code here">[:AUTHNUM:]</div>
                        <div data-toggle="tooltip" data-placement="left" title="print merchant name here">[:DBA_NAME:]</div>
                        <div data-toggle="tooltip" data-placement="left" title="print payor's account number here">[:ACCOUNT_NUMBER:]</div>
                        <div data-toggle="tooltip" data-placement="left" title="print payor's first name here">[:FIRSTNAME:]</div>
                        <div data-toggle="tooltip" data-placement="left" title="print payor's last name here">[:LASTNAME:]</div>
                        <div data-toggle="tooltip" data-placement="left" title="print payor's unit here">[:UNIT:]</div>
                        <div data-toggle="tooltip" data-placement="left" title="print settlement disclaimer here">[:DISCLAIMER:]</div>
                        <div data-toggle="tooltip" data-placement="left" title="print payment details here">[:DESCRIPTION:]</div>
                        <div data-toggle="tooltip" data-placement="left" title="print group contact name here">[:CONTACTNAME:]</div>
                        <div data-toggle="tooltip" data-placement="left" title="print group name here">[:COMPANYNAME:]</div>
                        <div data-toggle="tooltip" data-placement="left" title="print autopayment next date here">[:NEXTDATE:]</div>
                        <div data-toggle="tooltip" data-placement="left" title="print autopayment start date here">[:STARTDATE:]</div>
                        <div data-toggle="tooltip" data-placement="left" title="place link to login page here">[:LOGINLINK:]</div>
                        <div data-toggle="tooltip" data-placement="left" title="print payment net amount here">[:NETAMOUNT:]</div>
                        <div data-toggle="tooltip" data-placement="left" title="print payor's address here">[:UADDR:]</div>
                        <div data-toggle="tooltip" data-placement="left" title="print payor's city here">[:UCITY:]</div>
                        <div data-toggle="tooltip" data-placement="left" title="print payor's state here">[:USTATE:]</div>
                        <div data-toggle="tooltip" data-placement="left" title="print payor's zip here">[:UZIP:]</div>
                        <div data-toggle="tooltip" data-placement="left" title="print payor's phone number here">[:UPHONE:]</div>
                        <div data-toggle="tooltip" data-placement="left" title="print payment source here">[:SOURCE:]</div>
                        <div data-toggle="tooltip" data-placement="left" title="print payor's email here">[:UEMAIL:]</div><br>
                        <div data-toggle="tooltip" data-placement="left" title="print payor's invoice number">[:INVOICE_NUMBER:]</div><br>
                        <div data-toggle="tooltip" data-placement="left" title="print autopayment's total amount">[:TOTAL_AMOUNT:]</div>
                        <div data-toggle="tooltip" data-placement="left" title="print autopayment's payment method">[:PAY_METHOD:]</div>
                        <div data-toggle="tooltip" data-placement="left" title="print autopayment's date">[:DATE_AUTO:]</div>
                        <div data-toggle="tooltip" data-placement="left" title="print autopayment's amount">[:AMOUNT:]</div>
                        <div data-toggle="tooltip" data-placement="left" title="print autopayment's cfee">[:CFEE:]</div><br>
                        <div data-toggle="tooltip" data-placement="left" title="print autopayment frequency here">[:FREQUENCY:]</div><br>
                        <div data-toggle="tooltip" data-placement="left" title="print card/bank account type here">[:CARDTYPE:]</div><br>
                        <div data-toggle="tooltip" data-placement="left" title="print custom fields">[:CUSTOMFIELD:]</div><br>

                    </div>
                    </div>
                </div>
            </div>

            <div class="hr-line-dashed"></div>

            <div class="row">
                <div class="col-xs-2">
                    <button class="btn btn-primary btn-full" type="submit">Save Settings</button>
                </div>
                <div class="col-xs-8">
                    <?php if ($level != 'M'): ?>
                        <div class="checkbox checkbox-success">
                            <input type="checkbox"  class="styled" id="propagate" name="propagate"  style="cursor:pointer" >
                            <label for="propagate" style="cursor:pointer">
                                Apply these settings to all levels below this one.
                            </label>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </form>


        </div>
    </div>
</div>
<script>
    function prc() {
        $('#nttab').hide();
        $('#nt-tab').removeClass('active');
        $('#prtab').show();
        $('#pr-tab').addClass('active');
    }
    function ntc() {
        $('#prtab').hide();
        $('#pr-tab').removeClass('active');
        $('#nttab').show();
        $('#nt-tab').addClass('active');
    }
    $(function () { prc(); });
</script>
@endsection
