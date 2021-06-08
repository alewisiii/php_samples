@php
    $input_data=session('input_data1');
@endphp

@extends('layouts.layout')

@section('title')
    @lang('messages.paymentapproved')
@endsection

@section('content')
    <div class="row">
        <div class="col-md-8 col-lg-8 col-xl-8">
            <div class="hpanel">
                <div class="panel-body">
                    <div class="tab-content">
                        <div id="step1" class="p-m tab-pane active">
                            <div class="panel-body text-center" style="border: none">
                                <div class="sa">
                                    <div class="sa-success">
                                        <div class="sa-success-tip"></div>
                                        <div class="sa-success-long"></div>
                                        <div class="sa-success-placeholder"></div>
                                        <div class="sa-success-fix"></div>
                                    </div>
                                </div>


                                <h4 class="text-center" style="text-transform: uppercase">@lang('messages.paymentapproved')</h4>
                                @include('makepayment.payresult')
                            </div>

                            <hr/>

                            @include('makepayment.paydetails')
                            @include('makepayment.paymethoddetails')
                            @include('makepayment.feePayTotalPanel')

                            
                            @php
                                // REVO 1671: if no profile_id (saved cc) the do not display.
                                $paymethod = session('paymethod');
                                if (isset($paymethod['profile_id'])) {
                            @endphp
                            <hr>
                            @include('makepayment.payoneclick')
                            @php
                                }
                            @endphp
                            <div class="panel-footer inside-tab panel-b-mobile">
                                <a href="{{ route('pay') }}" class="btn btn-primary btn-lg btn-block btn-footer">@lang('messages.newpay') <span class="pe-7s-angle-right"></span></a>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4 col-lg-4 col-xl-3">
            {!! \App\Http\Controllers\ComponentController::help() !!}
            {!! \App\Http\Controllers\ComponentController::ads() !!}
        </div>
    </div>
@endsection

@section('javascript')
    <script>
        $(document).ready(function() {

            var tagNotOneclick = '@lang('messages.notoneclick')';
            var tagOneclick = '@lang('messages.oneclick')';
            var btnsubmit = $('#btnoneclick');
            $("#formActiveOneClick").validate({
                ignore: '*:not([name])',
                rules: {
                    xocday : { required: true, maxlength: 2},
                    xocfreq : { required: true, maxlength: 255},
                },errorPlacement: function(error, element) {
                    error.insertAfter($('#'+element.attr('id')+'Label'));
                },
                errorElement: "span",
                submitHandler: function(form) {
                    if(btnsubmit.hasClass('disable-oneclick')){
                        ajaxSubmit(form, function (response) {
                            if(response.code == 1){
                                $('#alreadyOC').html('<div class="alert alert-success"> @lang('messages.oneclickReminderDisabled') </div>');
                                btnsubmit.html(tagOneclick);
                                btnsubmit.removeClass('disable-oneclick');
                                $('#formActiveOneClick').attr('action',"{{ route('activateoneclick') }}");
                            }
                        });
                        return false;
                    }
                    ajaxSubmit(form, function (response) {
                        if(response.code == 1){
                            $('#alreadyOC').html('<div class="alert alert-success"> @lang('messages.setReminderOneclick') </div>');
                            btnsubmit.html(tagNotOneclick);
                            btnsubmit.addClass('disable-oneclick');
                            $('#formActiveOneClick').attr('action',"{{ route('disableoneclick') }}");
                        }
                    });
                    return false;
                }
            });
        });
    </script>
@endsection

