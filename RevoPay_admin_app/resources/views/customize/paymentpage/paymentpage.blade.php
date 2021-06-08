@extends('layouts.master')
@section('title','Payment Page')
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
                Payment Page
            </h2>
            <small>
                <ol class="hbreadcrumb breadcrumb">
                    <li><a href="<?php echo route('dashboard', array('token' => $token)); ?>">Dashboard</a></li>
                    <li class="active">
                        <span>Payment Page</span>
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
        <form method="post" action="<?php echo route('saveppage'); ?>" id="formNotification" onsubmit="removeDis();">
            <input type="hidden" name="_token" value="<?php echo csrf_token(); ?>" >
            <input type="hidden" name="tokendata" value="<?php echo $atoken; ?>" >
            <?php if (isset($settings['LOCKED_PROFILE_FIELDS_SELF'])): ?>

                <div class="">
                    <label>Customer edit (user management) [empty box = editable by user | check = not editable].</label>
                    <br>
                    <div class="row">
                        <div class="col-md-2 form-group">
                            <div class="checkbox checkbox-success">
                                <input type="checkbox"  class="styled" id="locked_account_number" name="locked_account_number"<?php if (strpos($settings['LOCKED_PROFILE_FIELDS_SELF'], 'account_number') !== false) echo "checked='true'"; ?>>
                                <label for="locked_account_number" >
                                    Account#
                                </label>
                            </div>
                        </div>


                        <div class="col-md-2 form-group">
                            <div class="checkbox checkbox-success">
                                <input type="checkbox"  class="styled" id="locked_first_name" type="checkbox" name="locked_first_name" <?php if (strpos($settings['LOCKED_PROFILE_FIELDS_SELF'], 'first_name') !== false) echo "checked='true'"; ?>>
                                <label for="locked_first_name" >
                                    First name
                                </label>
                            </div>
                        </div>
                        <div class="col-md-2 form-group">
                            <div class="checkbox checkbox-success">
                                <input type="checkbox"  class="styled" id="locked_last_name" type="checkbox" name="locked_last_name" <?php if (strpos($settings['LOCKED_PROFILE_FIELDS_SELF'], 'last_name') !== false) echo "checked='true'"; ?>>
                                <label for="locked_last_name" >
                                    Last name
                                </label>
                            </div>
                        </div>
                        <div class="col-md-2 form-group">
                            <div class="checkbox checkbox-success">
                                <input type="checkbox"  class="styled" id="locked_email_address" type="checkbox" name="locked_email_address" <?php if (strpos($settings['LOCKED_PROFILE_FIELDS_SELF'], 'email_address') !== false) echo "checked='true'"; ?>>
                                <label for="locked_email_address" >
                                    Email address
                                </label>
                            </div>
                        </div>
                        <div class="col-md-2 form-group">
                            <div class="checkbox checkbox-success">
                                <input type="checkbox"  class="styled" id="locked_phone" name="locked_phone"<?php if (strpos($settings['LOCKED_PROFILE_FIELDS_SELF'], 'phone_number') !== false) echo "checked='true'"; ?> >
                                <label for="locked_phone" >
                                    Phone Number
                                </label>
                            </div>
                        </div>
                        <div class="col-md-2 form-group">
                            <div class="checkbox checkbox-success">
                                <input type="checkbox"  class="styled" id="locked_address" type="checkbox" name="locked_address" <?php if (preg_match("/\baddress\b/i", $settings['LOCKED_PROFILE_FIELDS_SELF'])) echo "checked='true'"; ?> >
                                <label for="locked_address" >
                                    Address
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-2 form-group">
                            <div class="checkbox checkbox-success">
                                <input type="checkbox"  class="styled" id="locked_unit" type="checkbox" name="locked_unit" <?php if (preg_match("/\baddress_unit\b/i", $settings['LOCKED_PROFILE_FIELDS_SELF'])) echo "checked='true'"; ?>>
                                <label for="locked_unit" >
                                    Unit
                                </label>
                            </div>
                        </div>
                        <div class="col-md-2 form-group">
                            <div class="checkbox checkbox-success">
                                <input type="checkbox"  class="styled" id="locked_city" name="locked_city"<?php if (strpos($settings['LOCKED_PROFILE_FIELDS_SELF'], 'city') !== false) echo "checked='true'"; ?> >
                                <label for="locked_city" >
                                    City
                                </label>
                            </div>
                        </div>

                        <div class="col-md-2 form-group">
                            <div class="checkbox checkbox-success">
                                <input type="checkbox"  class="styled" id="locked_state" type="checkbox" name="locked_state" <?php if (strpos($settings['LOCKED_PROFILE_FIELDS_SELF'], 'state') !== false) echo "checked='true'"; ?> >
                                <label for="locked_state" >
                                    State
                                </label>
                            </div>
                        </div>
                        <div class="col-xs-2 form-group">
                            <div class="checkbox checkbox-success">
                                <input type="checkbox"  class="styled" id="locked_zip" type="checkbox" name="locked_zip" <?php if (strpos($settings['LOCKED_PROFILE_FIELDS_SELF'], 'zip') !== false) echo "checked='true'"; ?>>
                                <label for="locked_zip" >
                                    Zip
                                </label>
                            </div>
                        </div>
                    </div>

            <?php endif; ?>
            <?php if (isset($settings['SHOWLIMIT1'])): ?>
                <div class="form-group">
                    <div class="checkbox checkbox-success">
                        <input type="checkbox"  class="styled" id="SHOWLIMIT1" name="SHOWLIMIT1" <?php if ($settings['SHOWLIMIT1'] == "1") echo 'checked'; ?> >
                        <label for="SHOWLIMIT1" >
                            Enable high ticket disclaimer on payment page.
                        </label>
                    </div>
                </div>
                    <br/>
            <?php endif; ?>
            <?php if (isset($settings['DEFAULTPC']) && $level != 'M'): ?>
                <div class="form-group">

                        <label>
                            Default Payment Categories <b>for new</b> merchants 
                        </label>
                        <input id="DEFAULTPC" name="DEFAULTPC" class="form-control" type="text"  value="<?php
                        if ($settings['DEFAULTPC']) {
                            echo $settings['DEFAULTPC'];
                        } else {
                            echo "Payment";
                        }
                        ?>">

                </div>
            <?php endif; ?>
                    <div class="row">        
            <?php if (isset($settings['SHOW_BALANCE'])): ?>
                        <div class="col-md-2">        
                <div class="form-group">
                    <div class="checkbox checkbox-success">
                        <input type="checkbox"  class="styled" id="SHOW_BALANCE" name="SHOW_BALANCE" <?php if ($settings['SHOW_BALANCE'] == "1") echo 'checked'; ?> >
                        <label for="SHOW_BALANCE" >
                            Show user balance
                        </label>
                    </div>
                </div>
                        </div>
            <?php endif; ?>

            <?php if (isset($settings['SHOW_ADDRESS'])): ?>
                        <div class="col-md-2">
                <div class="form-group">
                    <div class="checkbox checkbox-success">
                        <input type="checkbox"  class="styled" id="SHOW_ADDRESS" name="SHOW_ADDRESS" <?php if ($settings['SHOW_ADDRESS'] == "1") echo 'checked'; ?> >
                        <label for="SHOW_ADDRESS" >
                            Show user address
                        </label>
                    </div>
                </div>
                        </div>
            <?php endif; ?>

            <?php if (isset($settings['NOMEMO'])): ?>
                        <div class="col-md-2">
                <div class="form-group">
                    <div class="checkbox checkbox-success">
                        <input type="checkbox"  class="styled" id="nomemo" name="nomemo" <?php if ($settings['NOMEMO'] == "1") echo 'checked'; ?> >
                        <label for="nomemo" >
                            Hide memo field
                        </label>
                    </div>
                </div>
                        </div>
            <?php endif; ?>
            <?php if (isset($settings['NOSOCIAL'])): ?>
                        <div class="col-md-2">
                <div class="form-group">
                    <div class="checkbox checkbox-success">
                        <input type="checkbox"  class="styled" id="nosocial" name="nosocial" <?php if ($settings['NOSOCIAL'] == "1") echo 'checked'; ?> >
                        <label for="nosocial" >
                            Disable Social network connections
                        </label>
                    </div>
                </div>
                        </div>
            <?php endif; ?>
            <?php if (isset($settings['ONECLICK'])): ?>
                        <div class="col-md-2">
                <div class="form-group">
                    <div class="checkbox checkbox-success">
                        <input type="checkbox"  class="styled" id="oneclick" name="oneclick" <?php if (isset($settings['ONECLICK']) && $settings['ONECLICK'] == "1") echo 'checked'; ?> >
                        <label for="oneclick" >
                            Allow one click payments
                        </label>
                    </div>
                </div>
                        </div>
            <?php endif; ?>
                    <br />
                    </div>       
                    <div id="customnotsetup">
                        <label>Customize presented text when the Merchant is not setup to receive payments</label>
                        <div class="row">
                            <div class="col-md-2">
                                <?php
                                if(!isset($settings['CUSTOMNOTSETUPIMG'])){
                                    $settings['CUSTOMNOTSETUPIMG']=1;
                                }
                                $vcs=$settings['CUSTOMNOTSETUPIMG'];
                                if($vcs<0)$vcs=0;
                                if($vcs>2)$vcs=1;
                                ?>
                                <label>Custom Not setup logo or image.</label><br><br>
                                <div class="form-group borders p-m">
                                    <input type="radio"  class="form-inline" name="CUSTOMNOTSETUPIMG" value="0" <?php if($vcs==0){ echo 'checked';} ?>>&nbsp;<label class="form-inline">No Logo or Image</label><br>
                                    <input type="radio"  class="form-inline" name="CUSTOMNOTSETUPIMG" value="1" <?php if($vcs==1){ echo 'checked';} ?>>&nbsp;<label class="form-inline">Default Image</label><br>
                                    <input type="radio"  class="form-inline" name="CUSTOMNOTSETUPIMG" value="2" <?php if($vcs==2){ echo 'checked';} ?>>&nbsp;<label class="form-inline">Merchant Logo</label><br>
                                </div>
                            </div>
                            <div class="col-md-10">
                                <?php
                                if(!isset($settings['CUSTOMNOTSETUPTEXT'])){
                                    $settings['CUSTOMNOTSETUPTEXT']='';
                                }
                                ?>
                                <div class="form-group">
                                    <label>Custom Not setup text.</label><br>
                                    <textarea id="CUSTOMNOTSETUPTEXT" name="CUSTOMNOTSETUPTEXT" class="form-control tinymce-editor" style="min-height: 200px;" ><?php echo $settings['CUSTOMNOTSETUPTEXT']; ?> </textarea>

                                </div>
                            </div>
                        </div>
                    </div>
                    <br/>
                <div class="form-group">
                    <label>Custom text explaining Convenience Fees</label><br>
                    <textarea id="CFEETEXT" name="CFEETEXT" class="form-control" style="min-height: 50px;" ><?php if(isset($settings['CFEETEXT'])){echo $settings['CFEETEXT'];} ?> </textarea>
                </div>
            <?php if (isset($settings['SETTLMENT_DISCLAIMER'])): ?>
                    <br/>
                <div class="form-group">
                    <label>Settlement Disclaimer. Please tell us what your disclaimer states.</label><br>
                    <textarea id="SETTLMENT_DISCLAIMER" name="SETTLMENT_DISCLAIMER" class="form-control tinymce-editor" style="min-height: 200px;" ><?php echo $settings['SETTLMENT_DISCLAIMER']; ?> </textarea>

                </div>
            <?php endif; ?>

            <label>eTerminal:</label>
            <div class="row">
                <div class="col-md-4 form-group">
                    <div class="checkbox checkbox-success">
                        <input type="checkbox"  class="styled" id="cancel_restrictions" name="cancel_restrictions" <?php if (isset($settings['CANCELRESTRICTIONS']) && $settings['CANCELRESTRICTIONS'] == "1") echo 'checked'; ?> >
                        <label for="cancel_restrictions" >
                            Do not apply restrictions on eTerminal.
                        </label>
                    </div>
                </div>
                <div class="col-md-4 form-group">
                    <div class="checkbox checkbox-success">
                        <input type="checkbox"  class="styled" id="no_eterm_new_user" name="no_eterm_new_user" <?php if (isset($settings['NOETERMNEWUSER']) && $settings['NOETERMNEWUSER'] == "1") echo 'checked'; ?> >
                        <label for="no_eterm_new_user" >
                            Do not allow to create new users on eTerminal.
                        </label>
                    </div>
                </div>
            </div>
                <div class="row">
                <?php if (isset($settings['FIXEDRECURRING']) || isset($settings['DYNAMICRECURRING'])): ?>
                 <div class="col-xs-12"><label>AutoPayments:</label></div>
                <?php endif; ?>

                    <?php if (isset($settings['FIXEDRECURRING'])): ?>
                    <div class="col-md-4 form-group">
                            <div class="checkbox checkbox-success">
                                <input type="checkbox"  class="styled" id="FIXEDRECURRING" name="FIXEDRECURRING" <?php if ($settings['FIXEDRECURRING'] == "1") echo 'checked'; ?> >
                                <label for="FIXEDRECURRING" >
                                    Allow fixed recurring payments?
                                </label>
                            </div>
                    </div>
                        <?php endif; ?>
                    <?php if (isset($settings['DYNAMICRECURRING'])): ?>
                    <div class="col-md-4 form-group">
                            <div class="checkbox checkbox-success">
                                <input type="checkbox"  class="styled" id="DYNAMICRECURRING" name="DYNAMICRECURRING" <?php if ($settings['DYNAMICRECURRING'] == "1") echo 'checked'; ?>  onclick="drpcheck();">
                                <label for="DYNAMICRECURRING" >
                                    Allow dynamic recurring payments?
                                </label>
                            </div>
                    </div>
                    <?php endif; ?>
                    <?php if (isset($settings['AUTOREMINDER'])): ?>
                        <div class="col-md-4 form-group">
                            <div class="checkbox checkbox-success">
                                <input type="checkbox"  class="styled" id="AUTOREMINDER" name="AUTOREMINDER" <?php if (isset($settings['AUTOREMINDER']) && $settings['AUTOREMINDER'] == "1") echo 'checked'; ?> >
                                <label for="AUTOREMINDER" >
                                    Send AutoPay reminder.
                                </label>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
                <?php if (isset($settings['CUSTOMAUTOPAYMSG'])): ?>
                <br>
                <div class="form-group">
                    <label>Custom message to show when the Autopay limit is reached. Please input a dash (-) to remove the message.</label><br>
                    <textarea id="CUSTOMAUTOPAYMSG" name="CUSTOMAUTOPAYMSG" class="form-control tinymce-editor" style="max-height: 100px;min-height: 50px;" ><?php echo $settings['CUSTOMAUTOPAYMSG']; ?> </textarea>
                </div>
                <?php endif; ?>
                <?php if (isset($settings['EXISTINGAUTOPAYMSG'])): ?>
                <br>
                <div class="form-group">
                    <label>Custom message to show when the Payor has active autopayments. Please input a dash (-) to remove the message.</label><br>
                    <textarea id="EXISTINGAUTOPAYMSG" name="EXISTINGAUTOPAYMSG" class="form-control tinymce-editor" style="max-height: 100px;min-height: 50px;" ><?php echo $settings['EXISTINGAUTOPAYMSG']; ?> </textarea>
                </div>
                <?php endif; ?>
                <?php if(isset($settings['LATE_FEE_DISCLAIMER'])): ?>    
                    <div class="row">
                        <div class="col-xs-12">
                            <label>Late Fee pop-up content. Separate each line with pipeline "|" </label>
                            <textarea id="LATE_FEE_DISCLAIMER" name="LATE_FEE_DISCLAIMER" class="form-control" style="min-height: 50px;" ><?php echo $settings['LATE_FEE_DISCLAIMER']; ?> </textarea>
                    </div>
                    </div>
                <?php endif; ?>  
                <?php if (isset($settings['PAYDISCLAIMER'])): ?>
                <br>
                <div class="form-group">
                    <label>Payment Disclaimer. Custom message to show before Payment details section. Please input a dash (-) to remove the message.</label><br>
                    <textarea id="PAYDISCLAIMER" name="PAYDISCLAIMER" class="form-control tinymce-editor" style="max-height: 100px;" ><?php echo $settings['PAYDISCLAIMER']; ?> </textarea>
                </div>
                <?php endif; ?>
                <?php if (isset($settings['FIXEDRECURRING'])): ?>
                    <br/>
                    <div class="row">
                        <div class="col-md-12 form-group">
                            <legend>Fixed AutoPayment Settings:</legend>
                        </div>

                        <div class="col-md-12 form-group">
                            <label for="autolimit" >
                                Limit Active AutoPayments per user to:
                            </label>
                            <select id="MAXRECURRINGPAYMENTPERUSER" name="MAXRECURRINGPAYMENTPERUSER" class="form-control">
                                <option value="0"<?php if ($settings['MAXRECURRINGPAYMENTPERUSER'] == '0') echo " selected=\"selected\""; ?>>No limits</option>
                                <option value="1" <?php if ($settings['MAXRECURRINGPAYMENTPERUSER'] == '1') echo " selected=\"selected\""; ?>>1</option>
                                <option value="2"<?php if ($settings['MAXRECURRINGPAYMENTPERUSER'] == '2') echo " selected=\"selected\""; ?>>2</option>
                                <option value="3" <?php if ($settings['MAXRECURRINGPAYMENTPERUSER'] == '3') echo " selected=\"selected\""; ?>>3</option>
                                <option value="4" <?php if ($settings['MAXRECURRINGPAYMENTPERUSER'] == '4') echo " selected=\"selected\""; ?>>4</option>
                                <option value="5"<?php if ($settings['MAXRECURRINGPAYMENTPERUSER'] == '5') echo " selected=\"selected\""; ?>>5</option>
                            </select>
                        </div>
                        </div>
                    <br/>

                    <label>Frequency Options Allowed:</label>
                        <div class="row">

                            <div class="col-md-2 form-group">
                                <div class="checkbox checkbox-success">
                                    <input type="checkbox"  class="styled" id="Monthly" name="monthly"<?php if (strpos($settings['FREQAUTOPAY'], 'monthly') !== false) echo 'checked'; ?> >
                                    <label for="Monthly" >
                                        Monthly
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-2 form-group">
                                <div class="checkbox checkbox-success">
                                    <input type="checkbox"  class="styled" id="quarterly" name="quarterly"<?php if (strpos($settings['FREQAUTOPAY'], 'quarterly') !== false) echo 'checked'; ?> >
                                    <label for="quarterly" >
                                        Quarterly
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-2 form-group">
                                <div class="checkbox checkbox-success">
                                    <input type="checkbox"  class="styled" id="biannually" name="biannually"<?php if (strpos($settings['FREQAUTOPAY'], 'biannually') !== false) echo 'checked'; ?> >
                                    <label for="biannually" >
                                        Semi-Annual
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-2 form-group">
                                <div class="checkbox checkbox-success">
                                    <input type="checkbox"  class="styled" id="annually" name="annually"<?php if (strpos($settings['FREQAUTOPAY'], 'annually') !== false) echo 'checked'; ?> >
                                    <label for="annually" >
                                        Annual
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-2 form-group">
                                <div class="checkbox checkbox-success">
                                    <input type="checkbox"  class="styled" id="untilcancel" name="untilcancel"<?php if (strpos($settings['FREQAUTOPAY'], 'untilcancel') !== false) echo 'checked'; ?>  onclick="vrfuc(1);">
                                    <label for="untilcancel" >
                                        Until Canceled
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-2 form-group">
                                <div class="checkbox checkbox-success">
                                    <input type="checkbox"  class="styled" id="weekly" name="weekly"<?php if (strpos($settings['FREQAUTOPAY'], 'weekly') !== false) echo 'checked'; ?> >
                                    <label for="weekly" >
                                        Weekly
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="row">

                            <div class="col-md-2 form-group">
                                <div class="checkbox checkbox-success">
                                    <input type="checkbox"  class="styled" id="biweekly" name="biweekly"<?php if (strpos($settings['FREQAUTOPAY'], 'biweekly') !== false) echo 'checked'; ?> >
                                    <label for="biweekly" >
                                        Bi-Weekly
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-2 form-group">
                                <div class="checkbox checkbox-success">
                                    <input type="checkbox"  class="styled" id="triannually" name="triannually"<?php if (strpos($settings['FREQAUTOPAY'], 'triannually') !== false) echo 'checked'; ?> >
                                    <label for="triannually" >
                                        Tri-Annual
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-2 form-group">
                                <div class="checkbox checkbox-success">
                                    <input type="checkbox"  class="styled" id="onetime" name="onetime"<?php if (strpos($settings['FREQAUTOPAY'], 'onetime') !== false) echo 'checked'; ?> >
                                    <label for="onetime" >
                                        One Time
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 form-group">
                                <label for="STARTAUTPDAY" >
                                    AutoPay Date Range Allowed (Beginning Date)
                                </label>
                                <?php
                                if (!isset($settings['STARTAUTOPAYDAY'])) {
                                    $settings['STARTAUTOPAYDAY'] = 1;
                                }
                                ?>
                                <select id="STARTAUTOPAYDAY" name="STARTAUTOPAYDAY" class="form-control">

                                    <option value="1" <?php if ($settings['STARTAUTOPAYDAY'] == '1') echo " selected=\"selected\""; ?>>1</option>
                                    <option value="2"<?php if ($settings['STARTAUTOPAYDAY'] == '2') echo " selected=\"selected\""; ?>>2</option>
                                    <option value="3"<?php if ($settings['STARTAUTOPAYDAY'] == '3') echo " selected=\"selected\""; ?>>3</option>
                                    <option value="4"<?php if ($settings['STARTAUTOPAYDAY'] == '4') echo " selected=\"selected\""; ?>>4</option>
                                    <option value="5"<?php if ($settings['STARTAUTOPAYDAY'] == '5') echo " selected=\"selected\""; ?>>5</option>
                                    <option value="6"<?php if ($settings['STARTAUTOPAYDAY'] == '6') echo " selected=\"selected\""; ?>>6</option>
                                    <option value="7"<?php if ($settings['STARTAUTOPAYDAY'] == '7') echo " selected=\"selected\""; ?>>7</option>
                                    <option value="8"<?php if ($settings['STARTAUTOPAYDAY'] == '8') echo " selected=\"selected\""; ?>>8</option>
                                    <option value="9"<?php if ($settings['STARTAUTOPAYDAY'] == '9') echo " selected=\"selected\""; ?>>9</option>
                                    <option value="10"<?php if ($settings['STARTAUTOPAYDAY'] == '10') echo " selected=\"selected\""; ?>>10</option>
                                    <option value="11"<?php if ($settings['STARTAUTOPAYDAY'] == '11') echo " selected=\"selected\""; ?>>11</option>
                                    <option value="12"<?php if ($settings['STARTAUTOPAYDAY'] == '12') echo " selected=\"selected\""; ?>>12</option>
                                    <option value="13"<?php if ($settings['STARTAUTOPAYDAY'] == '13') echo " selected=\"selected\""; ?>>13</option>
                                    <option value="14"<?php if ($settings['STARTAUTOPAYDAY'] == '14') echo " selected=\"selected\""; ?>>14</option>
                                    <option value="15"<?php if ($settings['STARTAUTOPAYDAY'] == '15') echo " selected=\"selected\""; ?>>15</option>
                                    <option value="16"<?php if ($settings['STARTAUTOPAYDAY'] == '16') echo " selected=\"selected\""; ?>>16</option>
                                    <option value="17"<?php if ($settings['STARTAUTOPAYDAY'] == '17') echo " selected=\"selected\""; ?>>17</option>
                                    <option value="18"<?php if ($settings['STARTAUTOPAYDAY'] == '18') echo " selected=\"selected\""; ?>>18</option>
                                    <option value="19"<?php if ($settings['STARTAUTOPAYDAY'] == '19') echo " selected=\"selected\""; ?>>19</option>
                                    <option value="20"<?php if ($settings['STARTAUTOPAYDAY'] == '20') echo " selected=\"selected\""; ?>>20</option>
                                    <option value="21"<?php if ($settings['STARTAUTOPAYDAY'] == '21') echo " selected=\"selected\""; ?>>21</option>
                                    <option value="22"<?php if ($settings['STARTAUTOPAYDAY'] == '22') echo " selected=\"selected\""; ?>>22</option>
                                    <option value="23"<?php if ($settings['STARTAUTOPAYDAY'] == '23') echo " selected=\"selected\""; ?>>23</option>
                                    <option value="24"<?php if ($settings['STARTAUTOPAYDAY'] == '24') echo " selected=\"selected\""; ?>>24</option>
                                    <option value="25"<?php if ($settings['STARTAUTOPAYDAY'] == '25') echo " selected=\"selected\""; ?>>25</option>
                                    <option value="26"<?php if ($settings['STARTAUTOPAYDAY'] == '26') echo " selected=\"selected\""; ?>>26</option>
                                    <option value="27"<?php if ($settings['STARTAUTOPAYDAY'] == '27') echo " selected=\"selected\""; ?>>27</option>
                                    <option value="28"<?php if ($settings['STARTAUTOPAYDAY'] == '28') echo " selected=\"selected\""; ?>>28</option>
                                    <option value="29"<?php if ($settings['STARTAUTOPAYDAY'] == '29') echo " selected=\"selected\""; ?>>29</option>
                                    <option value="30"<?php if ($settings['STARTAUTOPAYDAY'] == '30') echo " selected=\"selected\""; ?>>30</option>
                                    <option value="31"<?php if ($settings['STARTAUTOPAYDAY'] == '31') echo " selected=\"selected\""; ?>>31</option>
                                </select>
                            </div>
                            <div class="col-md-6 form-group">
                                <label for="ENDAUTPDAY">
                                    AutoPay Date Range Allowed (End Date)
                                </label>
                                <?php
                                if (!isset($settings['ENDAUTOPAYDAY'])) {
                                    $settings['ENDAUTOPAYDAY'] = 31;
                                }
                                ?>
                                <select id="ENDAUTOPAYDAY" name="ENDAUTOPAYDAY" class="form-control">
                                    <option value="1" <?php if ($settings['ENDAUTOPAYDAY'] == '1') echo " selected=\"selected\""; ?>>1</option>
                                    <option value="2" <?php if ($settings['ENDAUTOPAYDAY'] == '2') echo " selected=\"selected\""; ?>>2</option>
                                    <option value="3" <?php if ($settings['ENDAUTOPAYDAY'] == '3') echo " selected=\"selected\""; ?>>3</option>
                                    <option value="4" <?php if ($settings['ENDAUTOPAYDAY'] == '4') echo " selected=\"selected\""; ?>>4</option>
                                    <option value="5" <?php if ($settings['ENDAUTOPAYDAY'] == '5') echo " selected=\"selected\""; ?>>5</option>
                                    <option value="6" <?php if ($settings['ENDAUTOPAYDAY'] == '6') echo " selected=\"selected\""; ?>>6</option>
                                    <option value="7" <?php if ($settings['ENDAUTOPAYDAY'] == '7') echo " selected=\"selected\""; ?>>7</option>
                                    <option value="8" <?php if ($settings['ENDAUTOPAYDAY'] == '8') echo " selected=\"selected\""; ?>>8</option>
                                    <option value="9" <?php if ($settings['ENDAUTOPAYDAY'] == '9') echo " selected=\"selected\""; ?>>9</option>
                                    <option value="10" <?php if ($settings['ENDAUTOPAYDAY'] == '10') echo " selected=\"selected\""; ?>>10</option>
                                    <option value="11" <?php if ($settings['ENDAUTOPAYDAY'] == '11') echo " selected=\"selected\""; ?>>11</option>
                                    <option value="12" <?php if ($settings['ENDAUTOPAYDAY'] == '12') echo " selected=\"selected\""; ?>>12</option>
                                    <option value="13" <?php if ($settings['ENDAUTOPAYDAY'] == '13') echo " selected=\"selected\""; ?>>13</option>
                                    <option value="14" <?php if ($settings['ENDAUTOPAYDAY'] == '14') echo " selected=\"selected\""; ?>>14</option>
                                    <option value="15" <?php if ($settings['ENDAUTOPAYDAY'] == '15') echo " selected=\"selected\""; ?>>15</option>
                                    <option value="16" <?php if ($settings['ENDAUTOPAYDAY'] == '16') echo " selected=\"selected\""; ?>>16</option>
                                    <option value="17" <?php if ($settings['ENDAUTOPAYDAY'] == '17') echo " selected=\"selected\""; ?>>17</option>
                                    <option value="18" <?php if ($settings['ENDAUTOPAYDAY'] == '18') echo " selected=\"selected\""; ?>>18</option>
                                    <option value="19" <?php if ($settings['ENDAUTOPAYDAY'] == '19') echo " selected=\"selected\""; ?>>19</option>
                                    <option value="20" <?php if ($settings['ENDAUTOPAYDAY'] == '20') echo " selected=\"selected\""; ?>>20</option>
                                    <option value="21" <?php if ($settings['ENDAUTOPAYDAY'] == '21') echo " selected=\"selected\""; ?>>21</option>
                                    <option value="22" <?php if ($settings['ENDAUTOPAYDAY'] == '22') echo " selected=\"selected\""; ?>>22</option>
                                    <option value="23" <?php if ($settings['ENDAUTOPAYDAY'] == '23') echo " selected=\"selected\""; ?>>23</option>
                                    <option value="24" <?php if ($settings['ENDAUTOPAYDAY'] == '24') echo " selected=\"selected\""; ?>>24</option>
                                    <option value="25" <?php if ($settings['ENDAUTOPAYDAY'] == '25') echo " selected=\"selected\""; ?>>25</option>
                                    <option value="26" <?php if ($settings['ENDAUTOPAYDAY'] == '26') echo " selected=\"selected\""; ?>>26</option>
                                    <option value="27" <?php if ($settings['ENDAUTOPAYDAY'] == '27') echo " selected=\"selected\""; ?>>27</option>
                                    <option value="28" <?php if ($settings['ENDAUTOPAYDAY'] == '28') echo " selected=\"selected\""; ?>>28</option>
                                    <option value="29" <?php if ($settings['ENDAUTOPAYDAY'] == '29') echo " selected=\"selected\""; ?>>29</option>
                                    <option value="30" <?php if ($settings['ENDAUTOPAYDAY'] == '30') echo " selected=\"selected\""; ?>>30</option>
                                    <option value="31" <?php if ($settings['ENDAUTOPAYDAY'] == '31') echo " selected=\"selected\""; ?>>31</option>
                                </select>
                            </div>
                        </div>
                <?php endif; ?>
                <br/>
                <?php if (isset($settings['DYNAMICRECURRING'])): ?>
                        <div class="row">
                            <div class="col-md-12 form-group">
                                <legend>Dynamic AutoPayment Settings:</legend>
                                
                            </div>
                        </div>

                    <label>Frequency Options Allowed:</label><br>
                        <div class="row">
                            <div class="col-md-2 form-group">
                                <div class="checkbox checkbox-success">
                                    <input type="checkbox"  class="styled" id="drpmonthly" name="drpmonthly"<?php if (strpos($settings['DRPFREQAUTOPAY'], 'monthly') !== false) echo 'checked'; ?> onclick="DRPFreqValidation()">
                                    <label for="drpmonthly" >
                                        Monthly
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-2 form-group">
                                <div class="checkbox checkbox-success">
                                    <input type="checkbox"  class="styled" id="drpquarterly" name="drpquarterly"<?php if (strpos($settings['DRPFREQAUTOPAY'], 'quarterly') !== false) echo 'checked'; ?> onclick="DRPFreqValidation()">
                                    <label for="drpquarterly" >
                                        Quarterly
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-2 form-group">
                                <div class="checkbox checkbox-success">
                                    <input type="checkbox"  class="styled" id="drpbiannually" name="drpbiannually"<?php if (strpos($settings['DRPFREQAUTOPAY'], 'biannually') !== false) echo 'checked'; ?> onclick="DRPFreqValidation()">
                                    <label for="drpbiannually" >
                                        Semi-Annual
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-2 form-group">
                                <div class="checkbox checkbox-success">
                                    <input type="checkbox"  class="styled" id="drpannually" name="drpannually"<?php if (strpos($settings['DRPFREQAUTOPAY'], 'annually') !== false) echo 'checked'; ?> onclick="DRPFreqValidation()">
                                    <label for="drpannually" >
                                        Annual
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-2 form-group">
                                <div class="checkbox checkbox-success">
                                    <input type="checkbox"  class="styled" id="drpuntilcancel" name="drpuntilcancel"<?php if (strpos($settings['DRPFREQAUTOPAY'], 'untilcancel') !== false) echo 'checked'; ?>   onclick="vrfuc(2);">
                                    <label for="drpuntilcancel" >
                                        Until Canceled
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-2 form-group">
                                <div class="checkbox checkbox-success">
                                    <input type="checkbox"  class="styled" id="drpweekly" name="drpweekly"<?php if (strpos($settings['DRPFREQAUTOPAY'], 'weekly') !== false) echo 'checked'; ?> >
                                    <label for="drpweekly" >
                                        Weekly
                                    </label>
                                </div>
                            </div>

                        </div>
                        <div class="row">

                            <div class="col-md-2 form-group">
                                <div class="checkbox checkbox-success">
                                    <input type="checkbox"  class="styled" id="drpbiweekly" name="drpbiweekly"<?php if (strpos($settings['DRPFREQAUTOPAY'], 'biweekly') !== false) echo 'checked'; ?> >
                                    <label for="drpbiweekly" >
                                        Bi-Weekly
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-2 form-group">
                                <div class="checkbox checkbox-success">
                                    <input type="checkbox"  class="styled" id="drptriannually" name="drptriannually"<?php if (strpos($settings['DRPFREQAUTOPAY'], 'triannually') !== false) echo 'checked'; ?> >
                                    <label for="drptriannually" >
                                        Tri-Annual
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 form-group">
                                <label>
                                    AutoPay Date Range Allowed (Beginning Date)
                                </label>
                                <?php
                                if (!isset($settings['DRPSTARTAUTOPAYDAY'])) {
                                    $settings['DRPSTARTAUTOPAYDAY'] = 1;
                                }
                                ?>
                                <select id="DRPSTARTAUTOPAYDAY" name="DRPSTARTAUTOPAYDAY" onchange="DRPstartvalidator()" class="form-control">

                                    <option value="1" <?php if ($settings['DRPSTARTAUTOPAYDAY'] == '1') echo " selected=\"selected\""; ?>>1</option>
                                    <option value="2" <?php if ($settings['DRPSTARTAUTOPAYDAY'] == '2') echo " selected=\"selected\""; ?>>2</option>
                                    <option value="3" <?php if ($settings['DRPSTARTAUTOPAYDAY'] == '3') echo " selected=\"selected\""; ?>>3</option>
                                    <option value="4" <?php if ($settings['DRPSTARTAUTOPAYDAY'] == '4') echo " selected=\"selected\""; ?>>4</option>
                                    <option value="5" <?php if ($settings['DRPSTARTAUTOPAYDAY'] == '5') echo " selected=\"selected\""; ?>>5</option>
                                    <option value="6" <?php if ($settings['DRPSTARTAUTOPAYDAY'] == '6') echo " selected=\"selected\""; ?>>6</option>
                                    <option value="7" <?php if ($settings['DRPSTARTAUTOPAYDAY'] == '7') echo " selected=\"selected\""; ?>>7</option>
                                    <option value="8" <?php if ($settings['DRPSTARTAUTOPAYDAY'] == '8') echo " selected=\"selected\""; ?>>8</option>
                                    <option value="9" <?php if ($settings['DRPSTARTAUTOPAYDAY'] == '9') echo " selected=\"selected\""; ?>>9</option>
                                    <option value="10" <?php if ($settings['DRPSTARTAUTOPAYDAY'] == '10') echo " selected=\"selected\""; ?>>10</option>
                                    <option value="11" <?php if ($settings['DRPSTARTAUTOPAYDAY'] == '11') echo " selected=\"selected\""; ?>>11</option>
                                    <option value="12" <?php if ($settings['DRPSTARTAUTOPAYDAY'] == '12') echo " selected=\"selected\""; ?>>12</option>
                                    <option value="13" <?php if ($settings['DRPSTARTAUTOPAYDAY'] == '13') echo " selected=\"selected\""; ?>>13</option>
                                    <option value="14" <?php if ($settings['DRPSTARTAUTOPAYDAY'] == '14') echo " selected=\"selected\""; ?>>14</option>
                                    <option value="15" <?php if ($settings['DRPSTARTAUTOPAYDAY'] == '15') echo " selected=\"selected\""; ?>>15</option>
                                    <option value="16" <?php if ($settings['DRPSTARTAUTOPAYDAY'] == '16') echo " selected=\"selected\""; ?>>16</option>
                                    <option value="17" <?php if ($settings['DRPSTARTAUTOPAYDAY'] == '17') echo " selected=\"selected\""; ?>>17</option>
                                    <option value="18" <?php if ($settings['DRPSTARTAUTOPAYDAY'] == '18') echo " selected=\"selected\""; ?>>18</option>
                                    <option value="19" <?php if ($settings['DRPSTARTAUTOPAYDAY'] == '19') echo " selected=\"selected\""; ?>>19</option>
                                    <option value="20" <?php if ($settings['DRPSTARTAUTOPAYDAY'] == '20') echo " selected=\"selected\""; ?>>20</option>
                                    <option value="21" <?php if ($settings['DRPSTARTAUTOPAYDAY'] == '21') echo " selected=\"selected\""; ?>>21</option>
                                    <option value="22" <?php if ($settings['DRPSTARTAUTOPAYDAY'] == '22') echo " selected=\"selected\""; ?>>22</option>
                                    <option value="23" <?php if ($settings['DRPSTARTAUTOPAYDAY'] == '23') echo " selected=\"selected\""; ?>>23</option>
                                    <option value="24" <?php if ($settings['DRPSTARTAUTOPAYDAY'] == '24') echo " selected=\"selected\""; ?>>24</option>
                                    <option value="25" <?php if ($settings['DRPSTARTAUTOPAYDAY'] == '25') echo " selected=\"selected\""; ?>>25</option>
                                    <option value="26" <?php if ($settings['DRPSTARTAUTOPAYDAY'] == '26') echo " selected=\"selected\""; ?>>26</option>
                                    <option value="27" <?php if ($settings['DRPSTARTAUTOPAYDAY'] == '27') echo " selected=\"selected\""; ?>>27</option>
                                    <option value="28" <?php if ($settings['DRPSTARTAUTOPAYDAY'] == '28') echo " selected=\"selected\""; ?>>28</option>
                                    <option value="29" <?php if ($settings['DRPSTARTAUTOPAYDAY'] == '29') echo " selected=\"selected\""; ?>>29</option>
                                    <option value="30" <?php if ($settings['DRPSTARTAUTOPAYDAY'] == '30') echo " selected=\"selected\""; ?>>30</option>
                                    <option value="31" <?php if ($settings['DRPSTARTAUTOPAYDAY'] == '31') echo " selected=\"selected\""; ?>>31</option>
                                </select>
                            </div>
                            <div class="col-md-6 form-group">
                                <label>
                                    AutoPay Date Range Allowed (End Date)
                                </label>
                                <?php
                                if (!isset($settings['DRPENDAUTOPAYDAY'])) {
                                    $settings['DRPENDAUTOPAYDAY'] = 31;
                                }
                                ?>
                                <select id="DRPENDAUTOPAYDAY" name="DRPENDAUTOPAYDAY" onchange="DRPendvalidator()" class="form-control">

                                    <option value="1" <?php if ($settings['DRPENDAUTOPAYDAY'] == '1') echo " selected=\"selected\""; ?>>1</option>
                                    <option value="2" <?php if ($settings['DRPENDAUTOPAYDAY'] == '2') echo " selected=\"selected\""; ?>>2</option>
                                    <option value="3" <?php if ($settings['DRPENDAUTOPAYDAY'] == '3') echo " selected=\"selected\""; ?>>3</option>
                                    <option value="4" <?php if ($settings['DRPENDAUTOPAYDAY'] == '4') echo " selected=\"selected\""; ?>>4</option>
                                    <option value="5" <?php if ($settings['DRPENDAUTOPAYDAY'] == '5') echo " selected=\"selected\""; ?>>5</option>
                                    <option value="6" <?php if ($settings['DRPENDAUTOPAYDAY'] == '6') echo " selected=\"selected\""; ?>>6</option>
                                    <option value="7" <?php if ($settings['DRPENDAUTOPAYDAY'] == '7') echo " selected=\"selected\""; ?>>7</option>
                                    <option value="8" <?php if ($settings['DRPENDAUTOPAYDAY'] == '8') echo " selected=\"selected\""; ?>>8</option>
                                    <option value="9" <?php if ($settings['DRPENDAUTOPAYDAY'] == '9') echo " selected=\"selected\""; ?>>9</option>
                                    <option value="10" <?php if ($settings['DRPENDAUTOPAYDAY'] == '10') echo " selected=\"selected\""; ?>>10</option>
                                    <option value="11" <?php if ($settings['DRPENDAUTOPAYDAY'] == '11') echo " selected=\"selected\""; ?>>11</option>
                                    <option value="12" <?php if ($settings['DRPENDAUTOPAYDAY'] == '12') echo " selected=\"selected\""; ?>>12</option>
                                    <option value="13" <?php if ($settings['DRPENDAUTOPAYDAY'] == '13') echo " selected=\"selected\""; ?>>13</option>
                                    <option value="14" <?php if ($settings['DRPENDAUTOPAYDAY'] == '14') echo " selected=\"selected\""; ?>>14</option>
                                    <option value="15" <?php if ($settings['DRPENDAUTOPAYDAY'] == '15') echo " selected=\"selected\""; ?>>15</option>
                                    <option value="16" <?php if ($settings['DRPENDAUTOPAYDAY'] == '16') echo " selected=\"selected\""; ?>>16</option>
                                    <option value="17" <?php if ($settings['DRPENDAUTOPAYDAY'] == '17') echo " selected=\"selected\""; ?>>17</option>
                                    <option value="18" <?php if ($settings['DRPENDAUTOPAYDAY'] == '18') echo " selected=\"selected\""; ?>>18</option>
                                    <option value="19" <?php if ($settings['DRPENDAUTOPAYDAY'] == '19') echo " selected=\"selected\""; ?>>19</option>
                                    <option value="20" <?php if ($settings['DRPENDAUTOPAYDAY'] == '20') echo " selected=\"selected\""; ?>>20</option>
                                    <option value="21" <?php if ($settings['DRPENDAUTOPAYDAY'] == '21') echo " selected=\"selected\""; ?>>21</option>
                                    <option value="22" <?php if ($settings['DRPENDAUTOPAYDAY'] == '22') echo " selected=\"selected\""; ?>>22</option>
                                    <option value="23" <?php if ($settings['DRPENDAUTOPAYDAY'] == '23') echo " selected=\"selected\""; ?>>23</option>
                                    <option value="24" <?php if ($settings['DRPENDAUTOPAYDAY'] == '24') echo " selected=\"selected\""; ?>>24</option>
                                    <option value="25" <?php if ($settings['DRPENDAUTOPAYDAY'] == '25') echo " selected=\"selected\""; ?>>25</option>
                                    <option value="26" <?php if ($settings['DRPENDAUTOPAYDAY'] == '26') echo " selected=\"selected\""; ?>>26</option>
                                    <option value="27" <?php if ($settings['DRPENDAUTOPAYDAY'] == '27') echo " selected=\"selected\""; ?>>27</option>
                                    <option value="28" <?php if ($settings['DRPENDAUTOPAYDAY'] == '28') echo " selected=\"selected\""; ?>>28</option>
                                    <option value="29" <?php if ($settings['DRPENDAUTOPAYDAY'] == '29') echo " selected=\"selected\""; ?>>29</option>
                                    <option value="30" <?php if ($settings['DRPENDAUTOPAYDAY'] == '30') echo " selected=\"selected\""; ?>>30</option>
                                    <option value="31" <?php if ($settings['DRPENDAUTOPAYDAY'] == '31') echo " selected=\"selected\""; ?>>31</option>

                                </select>
                            </div>
                        </div>


                        <?php if (isset($settings['DRPMETHODS'])): ?>
                            <label>
                                Dynamic Payment Methods Allowed
                            </label>
                            <div class="row">
                                <div class="col-md-2 form-group">

                                    <?php
                                    $opt = explode('|', $settings['DRPMETHODS']);
                                    ?>
                                    <div class="checkbox checkbox-success">
                                        <input type="checkbox"  class="styled" id="drpcc" name="drpcc" <?php if (in_array('cc', $opt)) echo 'checked'; ?>  onclick="drpcheck();">
                                        <label for="drpcc" >
                                            Credit Card
                                        </label>
                                    </div>

                                </div>
                                <div class="col-md-2 form-group">
                                    <div class="checkbox checkbox-success">
                                        <input type="checkbox"  class="styled" id="drpec" name="drpec" <?php if (in_array('ec', $opt)) echo 'checked'; ?>  onclick="drpcheck();">
                                        <label for="drpec" >
                                            E-Check
                                        </label>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                <?php endif; ?>
                <?php if(isset($settings['SHOWOPENBOX']) && isset($settings['OPENBOXCONTENT'])): ?>    
                    <div class="row">
                          <div class="col-xs-4">
                            <div class="checkbox checkbox-success">
                                <input type="checkbox"  class="styled" id="showopenbox" name="showopenbox" <?php if (isset($settings['SHOWOPENBOX']) && $settings['SHOWOPENBOX'] == "1") echo 'checked'; ?> style="cursor:pointer">
                                <label for="showopenbox" style="cursor:pointer">
                                    Show custom box on top of "Make a Payment" page.
                                </label>
                            </div>
                        </div>
                    </div>
                    <br>    
                    <div class="row">
                        <div class="col-xs-12">
                            <label>Custom Box content.</label>
                            <textarea id="OPENBOXCONTENT" name="OPENBOXCONTENT" class="form-control" style="min-height: 200px;" ><?php echo $settings['OPENBOXCONTENT']; ?> </textarea>
                    </div>
                    </div>
                    <?php endif; ?>     
                <div class="hr-line-dashed"></div>
                <div class="row">
                    <div class="col-md-2 col-xs-5">
                        <button class="btn btn-primary btn-full" type="submit">Save Settings</button>
                    </div>
                    <div class="col-md-10 col-xs-7">
                        <?php if ($level != 'M'): ?>
                            <div class="checkbox checkbox-success">
                                <input type="checkbox"  class="styled" id="propagate" name="propagate"   >
                                <label for="propagate" >
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



<?php if (isset($msgCode)) { ?>

    @include('components.messages')
    <?php
    $popuphdr = "Success!";
    $popupcontent = "";
    if (isset($global_messages[$msgCode])) {
        $popupcontent = $global_messages[$msgCode];
    }
    ?>

    <?php
}
?>
<?php if (isset($msgCode)) : ?>
    <script>
        $('#myModal_success').modal();
    </script>
<?php endif; ?>
<div id="myModal_popup" class="modal fade">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-body text-center">
                    <br/>
                    <h4 style="font-size: 25px" id="xpopupheader1">Sample of pop-up for Approval</h4>
                    <div id="xpopupcontent">
                        <img src="{{ asset('img/approval.png') }}" style="width: 100%;">
                    </div>
                </div>
                <div class="modal-footer">
                    <div class="row">
                        <div class="col-xs-12"><button type="button" class="btn btn-primary form-control btn-full" data-dismiss="modal">Close</button></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
@endsection

@section('footer1')

<script>
    function showapproval(){
        $('#myModal_popup').modal();
    }
    
    function pppayorcheck(){
        if ($('#pp_payor').is(':checked')) {
                $('#pp_payor_11').prop('checked', false);
                $('#pp_payor_2').prop('checked', false);
                $('#pp_payor_3').prop('checked', false);
                $('#pp_payor_4').prop('checked', false);
                $('#pp_payor_5').prop('checked', false);
        }
    }

    function ppsumcheck(){
        if ($('#pp_summary').is(':checked')) {
                $('#pp_summary_1').prop('checked', false);
                $('#pp_summary_2').prop('checked', false);
                $('#pp_summary_3').prop('checked', false);
                $('#pp_summary_4').prop('checked', false);
        }
    }

    function DRPstartvalidator() {
        if (Number($("#DRPSTARTAUTOPAYDAY").val()) > Number($("#DRPENDAUTOPAYDAY").val())) {
            $("#DRPENDAUTOPAYDAY").val($("#DRPSTARTAUTOPAYDAY").val());
        }
    }

    function DRPendvalidator() {

        if (Number($("#DRPSTARTAUTOPAYDAY").val()) > Number($("#DRPENDAUTOPAYDAY").val())) {
            $("#DRPSTARTAUTOPAYDAY").val($("#DRPENDAUTOPAYDAY").val());
        }
    }

    function drpcheck() {
        if ($('#DYNAMICRECURRING').is(':checked')) {
            var cont = 0;
            if ($('#drpcc').is(':checked'))
                cont++;
            if ($('#drpec').is(':checked'))
                cont++;
            if (cont == 0) {
                $('#drpcc').prop('checked', true);
                $('#drpec').prop('checked', true);
            }
        }
    }

    function DRPFreqValidation() {
        var cont = 0;
        if ($('#drpmonthly').is(':checked'))
            cont++;
        if ($('#drpquarterly').is(':checked'))
            cont++;
        if ($('#drpbiannually').is(':checked'))
            cont++;
        if ($('#drpannually').is(':checked'))
            cont++;
        if ($('#drponetime').is(':checked'))
            cont++;
        if ($('#drpweekly').is(':checked'))
            cont++;
        if ($('#drpbiweekly').is(':checked'))
            cont++;
        if (cont < 2) {
            if ($('#drpmonthly').is(':checked'))
                $('#drpmonthly').attr("disabled", true);
            if ($('#drpquarterly').is(':checked'))
                $('#drpquarterly').attr("disabled", true);
            if ($('#drpbiannually').is(':checked'))
                $('#drpbiannually').attr("disabled", true);
            if ($('#drpannually').is(':checked'))
                $('#drpannually').attr("disabled", true);
            if ($('#drponetime').is(':checked'))
                $('#drponetime').attr("disabled", true);
            if ($('#drpweekly').is(':checked'))
                $('#drpweekly').attr("disabled", true);
            if ($('#drpbiweekly').is(':checked'))
                $('#drpbiweekly').attr("disabled", true);
        } else {
            if ($('#drpmonthly').is(':checked'))
                $('#drpmonthly').removeAttr("disabled");
            if ($('#drpquarterly').is(':checked'))
                $('#drpquarterly').removeAttr("disabled");
            if ($('#drpbiannually').is(':checked'))
                $('#drpbiannually').removeAttr("disabled");
            if ($('#drpannually').is(':checked'))
                $('#drpannually').removeAttr("disabled");
            if ($('#drponetime').is(':checked'))
                $('#drponetime').removeAttr("disabled");
            if ($('#drpweekly').is(':checked'))
                $('#drpweekly').removeAttr("disabled");
            if ($('#drpbiweekly').is(':checked'))
                $('#drpbiweekly').removeAttr("disabled");
        }
    }

    function startvalidator() {
        if (Number($("#STARTAUTOPAYDAY").val()) > Number($("#ENDAUTOPAYDAY").val())) {
            $("#ENDAUTOPAYDAY").val($("#STARTAUTOPAYDAY").val());
        }
    }

    function endvalidator() {

        if (Number($("#STARTAUTOPAYDAY").val()) > Number($("#ENDAUTOPAYDAY").val())) {
            $("#STARTAUTOPAYDAY").val($("#ENDAUTOPAYDAY").val());
        }
    }

    function FreqValidation() {
        var cont = 0;
        if ($('#monthly').is(':checked'))
            cont++;
        if ($('#quarterly').is(':checked'))
            cont++;
        if ($('#biannually').is(':checked'))
            cont++;
        if ($('#annually').is(':checked'))
            cont++;
        if ($('#onetime').is(':checked'))
            cont++;
        if ($('#weekly').is(':checked'))
            cont++;
        if ($('#biweekly').is(':checked'))
            cont++;
        if (cont < 2) {
            if ($('#monthly').is(':checked'))
                $('#monthly').attr("disabled", true);
            if ($('#quarterly').is(':checked'))
                $('#quarterly').attr("disabled", true);
            if ($('#biannually').is(':checked'))
                $('#biannually').attr("disabled", true);
            if ($('#annually').is(':checked'))
                $('#annually').attr("disabled", true);
            if ($('#onetime').is(':checked'))
                $('#onetime').attr("disabled", true);
            if ($('#weekly').is(':checked'))
                $('#weekly').attr("disabled", true);
            if ($('#biweekly').is(':checked'))
                $('#biweekly').attr("disabled", true);
        } else {
            if ($('#monthly').is(':checked'))
                $('#monthly').removeAttr("disabled");
            if ($('#quarterly').is(':checked'))
                $('#quarterly').removeAttr("disabled");
            if ($('#biannually').is(':checked'))
                $('#biannually').removeAttr("disabled");
            if ($('#annually').is(':checked'))
                $('#annually').removeAttr("disabled");
            if ($('#onetime').is(':checked'))
                $('#onetime').removeAttr("disabled");
            if ($('#weekly').is(':checked'))
                $('#weekly').removeAttr("disabled");
            if ($('#biweekly').is(':checked'))
                $('#biweekly').removeAttr("disabled");
        }

    }

    function vrfuc(opc) {
        if (opc == 1) {
            if ($("#untilcancel").is(":checked")) {
                $("#drpuntilcancel").prop("checked", true);
            } else {
                $("#drpuntilcancel").prop("checked", false);
            }
        } else {
            if ($("#drpuntilcancel").is(":checked")) {
                $("#untilcancel").prop("checked", true);
            } else {
                $("#untilcancel").prop("checked", false);
            }

        }
    }

    function removeDis() {
        if ($('#monthly').is(':checked'))
            $('#monthly').removeAttr("disabled");
        if ($('#quarterly').is(':checked'))
            $('#quarterly').removeAttr("disabled");
        if ($('#biannually').is(':checked'))
            $('#biannually').removeAttr("disabled");
        if ($('#annually').is(':checked'))
            $('#annually').removeAttr("disabled");
        if ($('#onetime').is(':checked'))
            $('#onetime').removeAttr("disabled");
        if ($('#weekly').is(':checked'))
            $('#weekly').removeAttr("disabled");
        if ($('#biweekly').is(':checked'))
            $('#biweekly').removeAttr("disabled");
        if ($('#drpmonthly').is(':checked'))
            $('#drpmonthly').removeAttr("disabled");
        if ($('#drpquarterly').is(':checked'))
            $('#drpquarterly').removeAttr("disabled");
        if ($('#drpbiannually').is(':checked'))
            $('#drpbiannually').removeAttr("disabled");
        if ($('#drpannually').is(':checked'))
            $('#drpannually').removeAttr("disabled");
        if ($('#drponetime').is(':checked'))
            $('#drponetime').removeAttr("disabled");
        if ($('#drpweekly').is(':checked'))
            $('#drpweekly').removeAttr("disabled");
        if ($('#drpbiweekly').is(':checked'))
            $('#drpbiweekly').removeAttr("disabled");
        return true;
    }
    function validatewcfee()
    {
        if ($('#allow_walkinpayments').is(':checked'))
        {
            if ($('#walkin-cfee').value == null || $('#walkin-cfee').value < 0)
            {

            }
        }
    }
</script>
@endsection

