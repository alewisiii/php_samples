@extends('layouts.master')
@section('title','ImportBatch')
@section('content')

<div class="normalheader ">
    <div class="hpanel">
        <div class="panel-body">
            <a class="small-header-action" href="">
                <div class="clip-header">
                    <i class="fa fa-arrow-up"></i>
                </div>
            </a>

            <div>
                <h2 class="font-light m-b-xs">
                    Import Batch Tool
                </h2>
                <small>
                    <ol class="hbreadcrumb breadcrumb">
                        <li><a href="<?php echo route('dashboard', array('token' => $token)); ?>">Dashboard</a></li>
                        <li class="active">
                            <span>Import Batch Tool</span>
                        </li>

                    </ol>
                </small>
            </div>

        </div>
    </div>
</div>

<div class="content">
    @include('importbatch.tabs')
    <div class="hpanel">
        <div class="panel-body">

            <form enctype="multipart/form-data" class="dropzone" id="form" action="<?php echo route('processbatch', array('token' => $token)); ?>" method="post">
                <input type="hidden" name="_token" value="<?php echo csrf_token(); ?>" >

                <div class="panel-shadow">

                    <p class="small">This tool can be used to import a batch of multiple preauthorized payments. This tool is useful for you to load pre-existing authorized payments into the system in order to consolidate all your payments.</p>
                    <br/>
                    <div class="panel panel-default" style="border: none; box-shadow: none; background-color: #F4F4F4; padding: 15px">
                        <div class="panel-body" style="font-size: 12px">
                            <h4>Instructions and Requirements</h4>
                            <span>Payment files can be uploaded in one of three formats: <b>CSV,NACHA or Bank Adapter File</b></span><br/><br/>
                            <a href="#" id="link_read_instructions" style="text-decoration: underline; color: #333333">Click here to read the complete instructions of the batch upload tool</a>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-sm-3 form-group">
                            <label>File</label>
                            <input type="file" name="file" required/>
                        </div>
                        <div class="col-sm-3 form-group" id="inputModes">
                            <label>Method of upload</label>
                            <div class="radio radio-success">
                                <input required type="radio" id="im1" name="inputMode" value="loadpayments" checked="checked">
                                <label for="im1">Load Payments</label>
                            </div>
                            <div class="radio radio-success">
                                <input required type="radio" id="im2" name="inputMode" value="updatepayments">
                                <label for="im2">Update Payments</label>
                            </div>
                        </div>
                        <div class="col-sm-3 form-group">
                            <div id="fileModes">
                                <label>File format</label>
                                <div class="radio radio-success">
                                    <input required id="fileModeCSV" value="csv" type="radio" checked name="fileMode">
                                    <label for="fileModeCSV">CSV file &nbsp;&nbsp;&nbsp;<span data="modalcsv" class="glyphicon glyphicon-info-sign icon-blue" aria-hidden="true"></span></label>
                                </div>
                                <div class="radio radio-success" id="fileModeNachaCont">
                                    <input required id="fileModeNACHA" value="nacha" type="radio" name="fileMode">
                                    <label for="fileModeNACHA">NACHA file &nbsp;&nbsp;&nbsp;<span data="modalnacha" class="glyphicon glyphicon-info-sign icon-blue" aria-hidden="true"></span></label>
                                </div>
                                <div class="radio radio-success" id="fileModeBankAdapterCont">
                                    <input required id="fileModeBA" value="bankadapter" type="radio" name="fileMode">
                                    <label for="fileModeBA">Bank Adapter File&nbsp;&nbsp;&nbsp;<span data="modalba" class="glyphicon glyphicon-info-sign icon-blue" aria-hidden="true"></span></label>
                                </div>
                            </div>
                            <div id="fileTypeCont">
                                <div class="hidden" id="fileTypeACHCont">
                                    <label><input required value="ach" type="radio" name="fileType" checked="checked">ACH transactions</label>
                                </div>
                            </div>
                        </div>

                    </div>
                    <br/>
                    <h4>Schedule Payment on:</h4>
                    <div class="row">
                        <div class="col-sm-3 form-group">
                            <label>Frequency</label>
                            <select required class="form-control" name="inputFq" id="inputFq">
                                <option value="now">Now</option>
                                <option value="onetime">One Time</option>
                                <option value="monthly">Monthly</option>
                                <option value="quarterly">Quarterly</option>
                                <option value="annually">Annual</option>
                                <option value="biannually">Biannual</option>
                                <option value="triannually">Tri-Annual</option>
                            </select>
                        </div>
                        <div class="col-sm-3 hide form-group" id="inputSdCont">
                            <label>Day of Month</label>
                            <select class="form-control" name="inputSd" id="inputSd">
                                <option value="1">1</option><option value="2">2</option><option value="3">3</option><option value="4">4</option><option value="5">5</option><option value="6">6</option><option value="7">7</option><option value="8">8</option><option value="9">9</option><option value="10">10</option><option value="11">11</option><option value="12">12</option><option value="13">13</option><option value="14">14</option><option value="15">15</option><option value="16">16</option><option value="17">17</option><option value="18">18</option><option value="19">19</option><option value="20">20</option><option value="21">21</option><option value="22">22</option><option value="23">23</option><option value="24">24</option><option value="25">25</option><option value="26">26</option><option value="27">27</option><option value="28">28</option><option value="29">29</option><option value="30">30</option><option value="31">31</option>		</select>
                        </div>
                    </div>
                    <div class="row hide" id="inputSminputSyCont">
                        <div class="col-sm-3 form-group">
                            <label>Start Month</label>
                            <select class="form-control" name="inputSm" id="inputSm">
                                <option value="01">Jan</option>
                                <option value="02">Feb</option>
                                <option value="03">Mar</option>
                                <option value="04">Apr</option>
                                <option value="05">May</option>
                                <option value="06">Jun</option>
                                <option value="07">Jul</option>
                                <option value="08">Aug</option>
                                <option value="09">Sep</option>
                                <option value="10">Oct</option>
                                <option value="11">Nov</option>
                                <option value="12">Dec</option>
                            </select>
                        </div>
                        <div class="col-sm-3 form-group">
                            <label>Start Year</label>
                            <select class="form-control" name="inputSy" id="inputSy">
                                <?php
                                for ($a = date('Y'); $a < date('Y') + 5; $a++) {
                                    echo '<option value="' . $a . '">' . $a . '</option>';
                                }
                                ?>
                            </select>
                        </div>
                    </div>

                    <div class="checkbox checkbox-success hide" id="inputDynamicCont">
                        <input type="checkbox" value="1" name="inputDynamic" id="inputDynamic"><label for="inputDynamic">Set those users to accept dynamic autopayments.</label>
                    </div>
                    <br/>
                    <div class="checkbox checkbox-success">
                        <input type="checkbox" required name="chkagree" id="chkagree">
                        <label for="chkagree">I agree with the <a id="termslink" class="underline">Terms.</a></label>
                    </div>


                    <div class="hr-line-dashed"></div>
                    <div class="form-group">
                        <button class="btn btn-primary" type="submit">Import</button>
                    </div>
                </div>

                <div class="modal fade" role="dialog" id="modalcsv">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="color-line"></div>
                            <div class="modal-header">
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                <h4 class="modal-title">Instructions and Requirements</h4>
                            </div>
                            <div class="modal-body small">
                                <span><b>CSV file – Load and Update Payments payments :</b></span>
                                <br/>
                                <br/>
                                - All of the columns listed below must be included in the order shown; blank columns are permitted.
                                <br/>
                                - To cancel or stop a recurring payment within the Batch, you should insert the value -1 in the Amount field.
                                <br/><br/>
                                <span><b>ACH Format:</b></span>
                                <br/>
                                <br/>
                                - User account,Account Holder name,Bank Account type (S - savings, C - checking),Property ID,Amount,Routing number,Bank Account number,User email address.
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-default btn-block" data-dismiss="modal">Close</button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal fade" role="dialog" id="modalnacha">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="color-line"></div>
                            <div class="modal-header">
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                <h4 class="modal-title">Instructions and Requirements:</h4>
                            </div>
                            <div class="modal-body small">

                                <span><b>NACHA file – Load Payments only:</b></span>
                                <br/>
                                <br/>
                                - The unique account #s for each user are at positions 41-54 and names are at positions 55-76
                                <br/>
                                - The Merchant/Property ID are at positions 41-50 of record type 5 (5 in first column)
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-default btn-block" data-dismiss="modal">Close</button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal fade" role="dialog" id="modalba">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="color-line"></div>
                            <div class="modal-header">
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                <h4 class="modal-title">Instructions and Requirements:</h4>
                            </div>
                            <div class="modal-body small">

                                <span><b>Bank Adapter File - Load Payments only:</b></span>
                                <br/>
                                - All of the columns listed below must be included in the order shown.
                                <br/>
                                - Fields required: Amount, FirstName or Custom2, AccountNumber, RoutingNumber, AccountType, AccountHolderName, Custom4 (in Group level), Custom3 (in Partner level).
                                <br/>
                                <br/>
                                <b>Fields:</b><br>
                                ID, <br>
                                DATE, <br>
                                AccountID, <br>
                                OrderID, <br>
                                Amount, <br>
                                IPAddress, <br>
                                Email, <br>
                                Phone, <br>
                                FirstName, <br>
                                LastName, <br>
                                Address1, <br>
                                Address2, <br>
                                City, <br>
                                State, <br>
                                ZipCode, <br>
                                Country, <br>
                                AccountNumber, <br>
                                RoutingNumber, <br>
                                AccountType, <br>
                                AccountHolderName, <br>
                                AccountHolderType, <br>
                                CheckNumber, <br>
                                SecCode, <br>
                                Description, <br>
                                RawResponse, <br>
                                Result, <br>
                                ResultDetails, <br>
                                ProcessorID, <br>
                                FileID, <br>
                                ACHFileID, <br>
                                Returned, <br>
                                ReturnDate, <br>
                                ReturnCode, <br>
                                CorrectiveData, <br>
                                ReturnFileID, <br>
                                ScrubNeeded, <br>
                                ScrubPassed, <br>
                                ScrubMessage, <br>
                                Custom1, <br>
                                Custom2, <br>
                                Custom3, <br>
                                Custom4, <br>
                                Custom5, <br>
                                Custom6, <br>
                                <br />
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-default btn-block" data-dismiss="modal">Close</button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal fade" role="dialog" id="modalterm">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="color-line"></div>
                            <div class="modal-header">
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                <h4 class="modal-title">Instructions and Requirements</h4>
                            </div>
                            <div class="modal-body small">

                                <h4>Please read the following Agreement:</h4>
                                <p class="small">
                                    The Recipient of funds (”Payee”) agrees that the associated payments are only for consumer payments, and that Payees have a valid, signed form on file allowing the Payee to debit the Payors bank deposit account and/or credit card (”Accounts”). Payee authorizes Revo Payments (”RP”) to debit the Payors bank deposit account or credit card account as indicated for all applicable payments and fees including but not limited to a convenience fee. The Payee agrees that they are responsible for the management of all payments. Payee acknowledges that the Payors bank statement for the Accounts will include a record of all payments and will serve as a receipt of payment. Payee represents that its authorization for the initiated payments is: in writing; readily identifiable as an authorization; contains clear and readily understandable terms; provides that the Payee (Originator bank) may revoke the authorization only by notifying the Payor (Receiver bank) in the manner specified in the authorization; and is either signed or similarly authenticated by the consumer. Further, Payee agrees to indemnify and hold RP harmless from and against any and all liabilities, losses, claims, damages, disputes, offsets, claims or counterclaims of any kind in any way related to the use or misuse of the software. This agreement and its parties and guarantors shall be governed by the laws, venue, and jurisdiction of Los Angeles County, California.
                                    <br/>
                                    By checking I Agree above, Payee understands and unconditionally accepts the terms of this Agreement.
                                </p>

                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-default btn-block" data-dismiss="modal">Close</button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal fade" role="dialog" id="modalall">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="color-line"></div>
                            <div class="modal-header">
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                <h4 class="modal-title">Instructions and Requirements</h4>
                            </div>
                            <div class="modal-body small">

                                <span><b>CSV file – Load and Update Payments payments :</b></span>
                                <br/>
                                <br/>
                                - All of the columns listed below must be included in the order shown; blank columns are permitted.
                                <br/>
                                - To cancel or stop a recurring payment within the Batch, you should insert the value -1 in the Amount field.
                                <br/><br/>
                                <span><b>ACH Format:</b></span>
                                <br/>
                                <br/>
                                - User account,Account Holder name,Bank Account type (S - savings, C - checking),Property ID,Amount,Routing number,Bank Account number,User email address.
                                <br/>
                                <br/>
                                <span><b>NACHA file – Load Payments only:</b></span>
                                <br/>
                                <br/>
                                - The unique account #s for each user are at positions 41-54 and names are at positions 55-76
                                <br/>
                                - The Merchant/Property ID are at positions 41-50 of record type 5 (5 in first column)
                                <br/>
                                <br/>
                                <span><b>Bank Adapter File - Load Payments only:</b></span>
                                <br/>
                                <br/>
                                - All of the columns listed below must be included in the order shown.
                                <br/>
                                - Fields required: Amount, FirstName or Custom2, AccountNumber, RoutingNumber, AccountType, AccountHolderName, Custom4 (in Group level), Custom3 (in Partner level).
                                <br/>
                                <br/>
                                <b>Fields:</b><br>
                                ID, <br>
                                DATE, <br>
                                AccountID, <br>
                                OrderID, <br>
                                Amount, <br>
                                IPAddress, <br>
                                Email, <br>
                                Phone, <br>
                                FirstName, <br>
                                LastName, <br>
                                Address1, <br>
                                Address2, <br>
                                City, <br>
                                State, <br>
                                ZipCode, <br>
                                Country, <br>
                                AccountNumber, <br>
                                RoutingNumber, <br>
                                AccountType, <br>
                                AccountHolderName, <br>
                                AccountHolderType, <br>
                                CheckNumber, <br>
                                SecCode, <br>
                                Description, <br>
                                RawResponse, <br>
                                Result, <br>
                                ResultDetails, <br>
                                ProcessorID, <br>
                                FileID, <br>
                                ACHFileID, <br>
                                Returned, <br>
                                ReturnDate, <br>
                                ReturnCode, <br>
                                CorrectiveData, <br>
                                ReturnFileID, <br>
                                ScrubNeeded, <br>
                                ScrubPassed, <br>
                                ScrubMessage, <br>
                                Custom1, <br>
                                Custom2, <br>
                                Custom3, <br>
                                Custom4, <br>
                                Custom5, <br>
                                Custom6, <br>
                                <br />
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-default btn-block" data-dismiss="modal">Close</button>
                            </div>
                        </div>
                    </div>
                </div>


            </form>
        </div>
    </div>

</div>
@endsection
@section('footer1')
<script>
    var inputSmOptions;
    $(document).ready(function(){
        inputSmOptions = $('#inputSm option').clone();
    });
</script>
<script src="{{ asset('js/importbatch.js') }}"></script>
@endsection


