@extends('layouts.layout')

@section('title')
    @lang('messages.myProfile')
@endsection

@section('content')
    <div class="row">
        @php
            $settings = session('settings');
            $disallowedFields = isset($settings['LOCKED_PROFILE_FIELDS_SELF']) ? explode("|",$settings['LOCKED_PROFILE_FIELDS_SELF']) : array();
            $acctTitle = isset($settings['PAYMENT_NUMBER_REG_NUMBER']) ? $settings['PAYMENT_NUMBER_REG_NUMBER'] : 'Account #';
            $accsetting = isset($settings['ACCSETTING']) ? $settings['ACCSETTING'] : 0;

        @endphp
        <div class="col-md-8 col-lg-8 col-xl-8">
            <div class="hpanel hgreen">
                <div class="panel-body">
                    <ul class="nav nav-tabs">
                        <li class="active"><a id="profile-tab" data-toggle="tab" data-label="@lang('messages.save')" href="#tab-profile"> @lang('messages.myProfile')</a></li>
                        <li class=""><a id="change-password-tab" data-toggle="tab" data-label="@lang('messages.save')" href="#tab-change-password">@lang('messages.changePasswordTab')</a></li>
                        <li class=""><a id="settings-tab" data-toggle="tab" data-label="@lang('messages.save') @lang('messages.settings')" href="#tab-notifications">@lang('messages.notificationsTab')</a></li>
                        <li class=""><a id="link-account-tab" data-toggle="tab" data-label="@lang('messages.link') @lang('messages.account')" href="#tab-link-account">@lang('messages.linkAccountToProfileTab')</a></li>
                    </ul>
                    <div class="tab-content">
                        <div class="m-b-lg"></div>
                        <div id="tab-profile" class="tab-pane active">
                            @if(!isset($settings['NOSOCIAL']) || (isset($settings['NOSOCIAL']) && $settings['NOSOCIAL']!=1))
                                <div class=" text-right btn-social-cont">
                                    @if(!in_array('gg', $social))
                                        <a href="{{ route('conxgg') }}" class="btn btn-danger btn-sm"><span class="fa fa-google-plus "></span></a>
                                    @else
                                        <a href="#" class="btn btn-danger btn-sm" disabled><span class="fa fa-google-plus"></span></a>
                                    @endif
                                    @if(!in_array('fb', $social))
                                        <a href="{{ route('conxfb') }}" class="btn btn-primary btn-sm">&nbsp;&nbsp;<span class="fa fa-facebook"></span>&nbsp;&nbsp;</a>
                                    @else
                                        <a href="#" class="btn btn-primary btn-sm" disabled>&nbsp;<span class="fa fa-facebook"></span>&nbsp;</a>
                                    @endif
                                    @if(!in_array('tw', $social))
                                        <a href="{{ route('conxtw') }}" class="btn btn-info btn-sm">&nbsp;<span class="fa fa-twitter"></span>&nbsp;</a>
                                    @else
                                        <a href="#" class="btn btn-info" disabled>&nbsp;<span class="fa fa-twitter"></span>&nbsp;</a>
                                    @endif
                                        {{--@if(!in_array('ln', $social))
                                            <a href="/" class="btn btn-info btn-sm">&nbsp;<span class="fa fa-linkedin"></span>&nbsp;</a>
                                        @else
                                            <a href="#" class="btn btn-info" disabled>&nbsp;<span class="fa fa-linkedin"></span>&nbsp;</a>
                                        @endif--}}
                                </div>
                            @endif
                            <form id="formTabProfile" action="{{ route('myprofileaction') }}" method="post">
                                <input type="hidden" name="_token" value="<?php echo csrf_token(); ?>" >
                                <div class="row">
                                    @if(!isset($settings['SHOWCOMPANYNAME']) || empty($settings['SHOWCOMPANYNAME']))
                                        <div class="col-sm-12 form-group">
                                            <label for="mpAccountNumber" id="mpAccountNumberLabel">{{ $acctTitle }}</label>
                                            <div class="input-group">
                                                <input id="mpAccountNumber" name="mpAccountNumber" class="form-control" placeholder="{{ $acctTitle }}" type="text" value="{{ Auth::user()->account_number }}" {{ in_array("account_number", $disallowedFields) || empty($disallowedFields) ? "readonly" : (($accsetting == 2 || $accsetting == 3) ? "required" : "") }} >
                                                <span class="input-group-addon">#</span>
                                            </div>
                                        </div>
                                    @else
                                        <div class="col-sm-6 form-group">
                                            <label for="mpCompanyName" id="mpCompanyNameLabel">@lang('messages.companyName')</label>
                                            <div class="input-group">
                                                <input id="mpCompanyName" name="mpCompanyName" class="form-control" placeholder="@lang('messages.companyName')" type="text" value="{{ Auth::user()->companyname }}">
                                                <span class="input-group-addon">
                                        <span class="fa fa-star-o"></span>
                                    </span>
                                            </div>
                                        </div>
                                        <div class="col-sm-6 form-group">
                                            <label for="mpAccountNumber" id="mpAccountNumberLabel">{{ $acctTitle }}</label>
                                            <div class="input-group">
                                                <input id="mpAccountNumber" name="mpAccountNumber" class="form-control" placeholder="{{ $acctTitle }}" type="text" value="{{ Auth::user()->account_number }}" {{ in_array("account_number", $disallowedFields) || empty($disallowedFields) ? "readonly" : (($accsetting == 2 || $accsetting == 3) ? "required" : "") }} >
                                                <span class="input-group-addon">#</span>
                                            </div>
                                        </div>
                                    @endif
                                    @if(isset($settings['BNAME']) && $settings['BNAME']==1 && Auth::user()->last_name=='')
                                        <div class="col-sm-12 form-group">
                                        <label for="mpFirstName" id="mpFirstNameLabel">@lang('messages.business_name')</label>
                                        <div class="input-group">
                                            <input id="mpFirstName" name="mpFirstName" class="form-control" placeholder="@lang('messages.business_name')" type="text" value="{{ Auth::user()->first_name }}" {{ in_array("first_name", $disallowedFields)?"readonly":"required" }}>
                                            <span class="input-group-addon">
                                    <span class="fa fa-user-o"></span>
                                </span>
                                            <input type="hidden" name="mpLastName" value="">
                                        </div>
                                    </div>
                                    @else
                                    <div class="col-sm-6 form-group">
                                        <label for="mpFirstName" id="mpFirstNameLabel">@lang('messages.first_name')</label>
                                        <div class="input-group">
                                            <input id="mpFirstName" name="mpFirstName" class="form-control" placeholder="@lang('messages.first_name')" type="text" value="{{ Auth::user()->first_name }}" {{ in_array("first_name", $disallowedFields)?"readonly":"required" }}>
                                            <span class="input-group-addon">
                                    <span class="fa fa-user-o"></span>
                                </span>
                                        </div>
                                    </div>
                                    <div class="col-sm-6 form-group">
                                        <label for="mpLastName" id="mpLastNameLabel">@lang('messages.last_name')</label>
                                        <div class="input-group">
                                            <input id="mpLastName" name="mpLastName" class="form-control" placeholder="@lang('messages.last_name')" type="text" value="{{ Auth::user()->last_name }}" {{ in_array("last_name", $disallowedFields)?"readonly":"" }}>
                                            <span class="input-group-addon">
                                    <span class="fa fa-user-o"></span>
                                </span>
                                        </div>
                                    </div>
                                    @endif
                                    <div class="col-sm-8 form-group">
                                        <label for="mpStreetAddress" id="mpStreetAddressLabel">@lang('messages.streetAddress')</label>
                                        <input id="mpStreetAddress" name="mpStreetAddress" class="form-control" placeholder="Street Address" type="text" value="{{ Auth::user()->address }}" {{ in_array("address", $disallowedFields)?"readonly":"" }}>
                                    </div>

                                    <div class="col-sm-4 form-group">
                                        <label for="mpAptSuite" id="mpAptSuiteLabel">@lang('messages.aptSuite')</label>
                                        <div class="input-group">
                                            <input id="mpAptSuite" name="mpAptSuite" class="form-control" placeholder="Apt Suite" type="text" value="{{ Auth::user()->address_unit }}" {{ in_array("address_unit", $disallowedFields)?"readonly":"" }}>
                                            <span class="input-group-addon">
                                    <span class="fa fa-map-marker"></span>
                                </span>
                                        </div>
                                    </div>

                                    <div class="col-sm-6 form-group">
                                        <label for="mpCity" id="mpCityLabel">@lang('messages.city')</label>
                                        <div class="input-group">
                                            <input id="mpCity" name="mpCity" class="form-control" placeholder="City" type="text" value="{{ Auth::user()->city }}" {{ in_array("city", $disallowedFields)?"readonly":"" }}>
                                            <span class="input-group-addon">
                                    <span class="fa fa-bullseye"></span>
                                </span>
                                        </div>
                                    </div>

                                    <div class="col-sm-6 form-group">
                                        <label for="mpState" id="mpStateLabel">@lang('messages.state')</label>
                                        <select id="mpState" name="mpState" class="js-select" style="width: 100%" {{ in_array("state", $disallowedFields)?"disabled":"" }}>
                                            @include('components.state')
                                        </select>
                                    </div>

                                    <div class="col-sm-6 form-group">
                                        <label for="mpZipcode" id="mpZipcodeLabel">@lang('messages.zipCode')</label>
                                        <div class="input-group">
                                            <input name="mpZipcode" id="mpZipcode" class="form-control zipcodeMask" placeholder="Zip Code" type="text" value="{{ Auth::user()->zip }}" {{ in_array("zip", $disallowedFields)?"readonly":"" }}>
                                            <span class="input-group-addon">
                                    <span class="fa fa-crosshairs"></span>
                                </span>
                                        </div>
                                    </div>

                                    <div class="col-sm-6 form-group">
                                        <label for="mpCountry" id="mpCountryLabel">@lang('messages.country')</label>
                                        <div class="input-group">
                                            <input name="mpCountry" id="mpCountry" class="form-control" placeholder="Country" type="text" value="USA" readonly  value="{{ Auth::user()->country }}">
                                            <span class="input-group-addon">
                                    <span class="fa fa-globe"></span>
                                </span>
                                        </div>
                                    </div>

                                    <div class="col-sm-6 form-group">
                                        <label for="mpEmailAddress" id="mpEmailAddressLabel">@lang('messages.emailAddress')</label>
                                        <div class="input-group">
                                            <input name="mpEmailAddress" id="mpEmailAddress" class="form-control emailMask" placeholder="Email" type="text" value="{{ Auth::user()->email_address }}" {{ in_array("email_address", $disallowedFields)?"readonly":"required" }}>
                                            <span class="input-group-addon">
                                    <span class="fa fa-envelope-o"></span>
                                </span>
                                        </div>
                                    </div>

                                    <div class="col-sm-6 form-group">
                                        <label for="mpPhone" id="mpPhoneLabel">@lang('messages.phone')</label>
                                        <div class="input-group">
                                            <input type="text" pattern="[0-9]*" inputmode="numeric" name="mpPhone" id="mpPhone" class="form-control phoneEUMask" placeholder="Phone Number" value="{{ Auth::user()->phone_number }}" {{ in_array("phone_number", $disallowedFields)?"readonly":"" }}>
                                            <span class="input-group-addon">
                                    <span class="fa fa-phone"></span>
                                </span>
                                        </div>
                                    </div>
                      
						<?php if($isGSBClient == 'Yes'): ?> 

        	                	<div class="row">
		    	                    <div class="col-lg-6 col-md-6 form-group">
                				        <h4>&nbsp;&nbsp;&nbsp;&nbsp;Delivery method for GSB bills</h4> 
                        			<div class="checkbox checkbox-success">&nbsp;&nbsp;&nbsp;&nbsp;
	                                    <input type="checkbox"  class="styled" id="e-delivery" name="e-delivery" <?php if (isset($online_delivery) && $online_delivery == 'T') echo ' checked'; ?>>
    	                                <label for="not_ot" >Electronic</label>
        		                    </div>                            
                		            <div class="checkbox checkbox-success">&nbsp;&nbsp;&nbsp;&nbsp;
                        	            <input type="checkbox"  class="styled" id="paper-delivery" name="paper-delivery" <?php if (isset($paper_delivery) && $paper_delivery == 'T') echo ' checked'; ?>>
                            	        <label for="not_auto" >Paper</label>
	                            </div>
    	                    </div>
        	             </div>
						<?php endif; ?>
						</div>
                            </form>
                        </div>
                        <div id="tab-change-password" class="tab-pane">
                            <form id="formTabChangePassword" action="{{ route('changepasswordaction') }}" method="post">
                                <input type="hidden" name="_token" value="<?php echo csrf_token(); ?>" >

                            <div class="row">

                                <div class="col-sm-6">
                                    <div class="row">

                                    <div class="col-sm-12 form-group">
                                        <label for="cpPassword" id="cpUsernameLabel">@lang('messages.username')</label>
                                        <div class="input-group">
                                            <input id="cpUsername" name="cpUsername" class="form-control" placeholder="Username" type="text" value="{{ Auth::user()->username }}">
                                            <span class="input-group-addon showUsernameControl cursor-pointer">
                                            <span class="fa fa-user-o"></span>
                                        </span>
                                        </div>

                                    </div>
                                    <div class="col-sm-12 form-group">
                                        <label for="cpPassword" id="cpPasswordLabel">@lang('messages.password')</label>
                                        <div class="input-group">
                                            <input id="cpPassword" name="cpPassword" class="form-control" placeholder="Password" type="password">
                                            <span class="input-group-addon showPasswordControl cursor-pointer">
                                            <span class="fa fa-search-plus"></span>
                                        </span>
                                        </div>
                                    </div>
                                    <div class="col-sm-12 form-group">
                                        <label for="cpRepeatPassword" id="cpRepeatPasswordLabel">@lang('messages.repeatPassword')</label>
                                        <div class="input-group">
                                            <input id="cpRepeatPassword" name="cpRepeatPassword" class="form-control" placeholder="Repeat Password" type="password">
                                            <span class="input-group-addon showPasswordControl cursor-pointer">
                                            <span class="fa fa-search-plus"></span>
                                        </span>
                                        </div>
                                    </div>
                                    </div>
                                </div>
                                <div class="col-sm-6 ">
                                    <div class="panel panel-default m-b-sm" style="border: none; box-shadow: none; background-color: #F4F4F4; padding: 15px; font-size: 12px">
                                        <div class="panel-body" id="passwordRequirements" style="border: none">
                                            <label>@lang('messages.passwordRules')</label><br />
                                            <span><i id="passwordSize" class="fa fa-close text-danger"></i> @lang('messages.passwordRule1')</span><br />
                                            <span><i id="passwordBegin" class="fa fa-close text-danger"></i> @lang('messages.passwordRule2')</span><br />
                                            <span><i id="passwordUppercase" class="fa fa-close text-danger"></i> @lang('messages.passwordRule3')</span><br />
                                            <span><i id="passwordLowercase" class="fa fa-close text-danger"></i> @lang('messages.passwordRule4')</span><br />
                                            <span><i id="passwordNumber" class="fa fa-close text-danger"></i> @lang('messages.passwordRule5')</span><br />
                                            <span><i id="passwordSpecial" class="fa fa-close text-danger"></i> @lang('messages.passwordRule6')</span>
                                        </div>
                                    </div>

                                </div>
                            </div>
                            </form>
                        </div>
                        <div id="tab-notifications" class="tab-pane">
                            <form id="formTabNotifications" action="{{ route('notificationsaction') }}" method="post">
                                <input type="hidden" name="_token" value="<?php echo csrf_token(); ?>" >
                            <div class="p-m">
                                <label class="icheck"><input name="nSuccess" type="checkbox" class="i-checks" {{ $notificatonSettings["SUCCESSFULPAYMENT"] }}> @lang('messages.notification1') </label>
                                <label class="icheck"><input name="nReturned" type="checkbox" class="i-checks" {{ $notificatonSettings["RETURNEDPAYMENT"] }}> @lang('messages.notification2') </label>
                                <label class="icheck"><input name="nDeclined" type="checkbox" class="i-checks" {{ $notificatonSettings["DECLINEDPAYMENT"] }}> @lang('messages.notification3') </label>
                                <label class="icheck"><input name="nExpirerecurring" type="checkbox" class="i-checks" {{ $notificatonSettings["RECURRINGEXPIRE"] }}> @lang('messages.notification4') </label>
                                <label class="icheck"><input name="nCompletedrecurring" type="checkbox" class="i-checks" {{ $notificatonSettings["RECURRINGCOMPLETED"] }}> @lang('messages.notification5') </label>
                                <label class="icheck"><input name="nOneclick" type="checkbox" class="i-checks" {{ $notificatonSettings["NOTONECLICK"] }}> @lang('messages.notification6') </label>
                                <label class="icheck"><input name="nPaperbill" type="checkbox" class="i-checks" {{ $notificatonSettings["PAPERINVOICE"] }}> @lang('messages.notification7') </label>
                            </div>
                            </form>
                        </div>
                        <div id="tab-link-account" class="tab-pane">
                            <form id="formTabLinkAccount" action="{{ route('linkaccountaction') }}" method="post">
                                <input type="hidden" name="_token" value="<?php echo csrf_token(); ?>" >
                            <div class="row">
                                <div class="col-sm-12 form-group">
                                    <div class="panel panel-default" style="border: none; box-shadow: none; background-color: #F4F4F4; padding: 15px">
                                        <div class="panel-body small" style="border: none">
                                            <b>@lang('messages.linkAccountRules')</b><br /><br />
                                            1. @lang('messages.linkAccountRule1')<br>
                                            2. @lang('messages.linkAccountRule2')<br>
                                            3. @lang('messages.linkAccountRule3')
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-12 form-group">
                                    <label for="laAccountNumber" id="laAccountNumberLabel">@lang('messages.accountLink')</label>
                                    <div class="input-group">
                                        <input name="laAccountNumber" id="laAccountNumber" class="form-control" placeholder="@lang('messages.accountNumber')" type="text">
                                        <span class="input-group-addon">
                                        <span class="fa fa-id-card-o"></span>
                                    </span>
                                    </div>
                                </div>
                                <div class="col-sm-12 form-group">
                                    <label for="laPassword" id="laPasswordLabel">@lang('messages.passwordLink')</label>
                                    <div class="input-group">
                                        <input name="laPassword" id="laPassword" class="form-control" placeholder="@lang('messages.password')" type="password">
                                        <span class="input-group-addon showPasswordControl cursor-pointer">
                                        <span class="fa fa-search-plus"></span>
                                    </span>
                                    </div>
                                </div>
                            </div>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="panel-footer">
                    <div class="panel-b-mobile">
                    <button type="submit" class="btn btn-primary btnSubmitPanelFooter ladda-button" data-style="zoom-in">@lang('messages.save')</button>
                    </div>
                </div>
            </div>
            @include('components.listLinkaccounts')

        </div>
        <div class="col-md-4 col-lg-4 col-xl-3 m-b-xl-xl">
            {!! \App\Http\Controllers\ComponentController::help() !!}
            {!! \App\Http\Controllers\ComponentController::ads() !!}
        </div>
    </div>
@endsection

@section('javascript')
    <script>
    	var account_number_changed = 0;
    	
        $('#mpState').val('{{Auth::user()->state}}');
        $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
            var target = $(e.target).attr("href") // activated tab
            var x = document.getElementById("linkedAccounts");
            if(target == '#tab-link-account'){
                x.style.display = "block";
            }else {
                x.style.display = "none";
            }
        });

        $(document).ready(function() {
            $('#cpRepeatPassword').keydown(function(event){
                if(event.ctrlKey==true && (event.which == '118' || event.which =='86')) {
                    event.preventDefault();
                }
            });
            $('#cpRepeatPassword').on("contextmenu", function(event){
                return false;
            });
        });        

        $(document).ready(function() {
            readonly = $('input[type=text][readonly]');
            disabled = $('select[disabled]');
            var submitForm = $('#tab-profile form');
            $('.nav-tabs a[data-toggle="tab"]').click(function () {
                submitForm = $($(this).attr('href')).find('form');
                $('.btnSubmitPanelFooter').html($(this).attr('data-label'));
            });
            $('.btnSubmitPanelFooter').click(function (){
                $('#mpState').attr('disabled', false);
                submitForm.submit();
            });

            $("#formTabProfile").validate({
                ignore: '*:not([name])',
                rules: {
                    mpCompanyName : { maxlength : 255},
                    mpAccountNumber : { accountNumber : true , maxlength: 100},
                    mpFirstName : { maxlength: 150 },
                    mpLastName : { maxlength: 150 },
                    //mpStreetAddress : { required: true },
                    mpAptSuite : { maxlength: 150 },
                    mpCity : { maxlength: 150 },
                    mpState : { maxlength: 15 },
                    mpZipcode : { maxlength: 5, minlength: 5},
                    mpCountry : { maxlength: 12 },
                    mpEmailAddress : { maxlength: 150, email:true},
                    mpPhone : {maxlength: 150, minlength: 10},
                },errorPlacement: function(error, element) {
                    error.insertAfter($('#'+element.attr('id')+'Label'));
                },
                errorElement: "span",
                submitHandler: function(form, event) {
                    ajaxSubmit(form, function (response) {
                        readonly.each(function (i,e) {
                            $('#'+e.id).prop('readonly',true);
                        });
                        disabled.each(function (i,e) {
                            $('#'+e.id).attr('disabled',true);
                        });
                    });
				if(account_number_changed == 1) {
					event.preventDefault();
					event.stopPropagation();
					account_number_changed = 0;
					setTimeout(function(){ window.location = "{{ route('myprofile')}}"; }, 4000);
				} else {                    
             		return false;
				}
                }
            });

            $("#formTabChangePassword").validate({
                ignore: '*:not([name])',
                rules: {
                    cpPassword : { required: true, maxlength: 100, customPassword: true},
                    cpRepeatPassword : { required: true, maxlength: 100 ,equalTo: "#cpPassword"},
                    cpUsername : { required: true, maxlength: 50},

                },errorPlacement: function(error, element) {
                    error.insertAfter($('#'+element.attr('id')+'Label'));
                },
                errorElement: "span",
                submitHandler: function(form) {
                    ajaxSubmit(form, function (data) {
                        $(form)[0].reset();
                        checks = $('#passwordRequirements i');
                        checks.removeClass("text-success");
                        checks.removeClass("fa-check");
                        checks.addClass("text-danger");
                        checks.addClass("fa-close");
                        $("#cpUsername").val(data.username);
                    });
                    return false;
                }
            });

            $("#formTabNotifications").validate({
                ignore: '*:not([name])',
                rules: {

                },errorPlacement: function(error, element) {
                    error.insertAfter($('#'+element.attr('id')+'Label'));
                },
                errorElement: "span",
                submitHandler: function(form) {
                    ajaxSubmit(form, function () {
                        $(form)[0].reset();
                    });
                    return false;
                }
            });

            $("#formTabLinkAccount").validate({
                ignore: '*:not([name])',
                rules: {
                    laAccountNumber : { required: true, accountNumber : true , maxlength: 100},
                    laPassword : { required: true, maxlength: 100},
                },errorPlacement: function(error, element) {
                    error.insertAfter($('#'+element.attr('id')+'Label'));
                },
                errorElement: "span",
                submitHandler: function(form) {
                    ajaxSubmit(form, function () {
                        $(form)[0].reset();
                        ajaxRequest('{{ route('linkedaccounts') }}',$('#linkedAccountsCont'));
                    });
                    return false;
                }
            });

            $('body').on('click', '.rmv-account-btn',function () {
                id=$(this).data('action');
                url = "{{ route('deletelinkaccount', array('id'=>0)) }}";
                url = url.replace("/0", "/"+id);

                swal({
                        title: "@lang('messages.areYouSure')",
                        text: "@lang('messages.onceDeletedCanNotRecovered2')",
                        type: "warning",
                        showCancelButton: true,
                        confirmButtonColor: "#DD6B55",
                        confirmButtonText: "@lang('messages.confirm')",
                        cancelButtonText: "@lang('messages.cancel')"
                    },
                    function () {
                        ajaxRequest(url, $('#linkedAccountsCont'));
                    });
            });

            $('body').on('click', '.login-account-btn',function () {
                id=$(this).data('action');
                url = "{{ route('loginlinkedaccount', array('id'=>0)) }}";
                url = url.replace("/0", "/"+id);
                swal({
                        title: "@lang('messages.areYouSure')",
                        text: "@lang('messages.sessionWillBeClosedAndLogged')",
                        type: "warning",
                        showCancelButton: true,
                        confirmButtonColor: "#4ea5e0",
                        confirmButtonText: "@lang('messages.login')",
                        cancelButtonText: "@lang('messages.cancel')"
                    },
                    function () {
                        window.location.href =url;
                    });
            });


                ajaxRequest('{{ route('linkedaccounts') }}',$('#linkedAccountsCont'));


        });

        $(document).ready(function () {
        	$(document).on('change', 'input[type="checkbox"]', function(e) {
        		if(e.target.name == "e-delivery" ) {
          		  	if(event.target.checked == false) {
            			if ($('#paper-delivery').prop('checked') == false) {                  			
            				alert("Must have at least 1 delivery method");
            				$("#e-delivery").prop( "checked", true );
            				}	
            			}
            		} else if(e.target.name == "paper-delivery" ) {
            			if(event.target.checked == false) {
            				if ($('#e-delivery').prop('checked') == false) {
            					alert("Must have at least 1 delivery method");
            					$("#paper-delivery").prop( "checked", true );
            			}	
            		}
            	}
        	 });
        	$("#mpAccountNumber").change(function(){ 
        	    account_number_changed = 1;
        	});
        	       	 
        });



    </script>
@endsection
