<?php

namespace App\Http\Controllers;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Requests\PayMethods\PaymentFreqValidations;
use App\Http\Requests\PayMethods\PaymentProfileValidations;
use App\Models\Audit;
use App\Models\Invoices;
use App\Models\Properties;
use App\Models\CustomField;
use App\Models\Transations;
use App\Models\WebUsers;
use App\Providers\RevoPayAuditLogger;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\View;
use App\Models\Categories;
use App\Http\Requests\PayMethods\PayMethodValidations;
use App\CustomClass\PaymentProcessor;
use App\Models\Bin;
use Illuminate\Support\Facades\Crypt;

class TransactionController extends Controller {
    /* Function to prepare Make a Payment' page */


    public function showPay(Request $request) {
        $obj_property = new Properties();
        $obj_customfield = new CustomField();
        $obj_user = new User();
        
        $settings = session('settings');
        $web_user_id = Auth::user()->web_user_id;
        $property_id = Auth::user()->property_id;
        $paymentCategories = $obj_property->getPaymentWebUserCategories($web_user_id);
        if(empty($paymentCategories)){
            $paymentCategories = $obj_property->getPaymentType($property_id,$settings);
        }
        $customfield = $obj_customfield->getCustomfieldDetails($property_id);
        session(['customfield'=>json_encode($customfield)]);
        session(['categories'=>json_encode($paymentCategories)]);
        session()->save();
        $show_drp=false;
        $show_fixed=false;
        $show_onetime=false;
        $show_recurring=false;

        $credTypes=$obj_property->getCredTypes($property_id);
        if($credTypes['onetime']>0){
            $show_onetime=true;
        }
        if($credTypes['recurring']>0){
            $show_recurring=true;
        }
        //cases nullifying recurring
        if($show_recurring && (isset($settings['DYNAMICRECURRING']) && $settings['DYNAMICRECURRING']==1)){
            $show_drp=true;
        }
        if($show_recurring && (isset($settings['FIXEDRECURRING']) && $settings['FIXEDRECURRING']==1)){
            $show_fixed=true;
        }
        if(!$show_drp && !$show_fixed){
            $show_recurring=false;
        }
        if($show_recurring){
            $user_drp=$obj_user->getDRPByUser($web_user_id, $property_id);
            if($user_drp>0 && $show_drp){
                $show_drp=false;
            }
            $user_auto=$obj_user->getAutoCountByUser($web_user_id, $property_id);
            $autolimit=1000;
            if(isset($settings['MAXRECURRINGPAYMENTPERUSER'])){
                if($settings['MAXRECURRINGPAYMENTPERUSER']>0){
                    $autolimit=$settings['MAXRECURRINGPAYMENTPERUSER']*1;
                }
            }
            if($user_auto>=$autolimit){
                $show_recurring=false;
                $show_drp=false;
                $show_fixed=false;
            }
            session(['user_drp'=>$user_drp,'user_auto'=>$user_auto,'autolimit'=>$autolimit]);
            session()->save();
        }
        $highticketcc_0 = $obj_property->getMaxByType($property_id, 'cc');
        $highticketcc_1 = $obj_property->getMaxByType($property_id, 'cc',1);
        $highticketec_0 = $obj_property->getMaxByType($property_id, 'ec');
        $highticketec_1 = $obj_property->getMaxByType($property_id, 'ec',1);
        $highticketamex_0 = $obj_property->getMaxByType($property_id, 'amex');
        $highticketamex_1 = $obj_property->getMaxByType($property_id, 'amex',1);
        session(['show_recurring'=>$show_recurring,
            'show_onetime'=>$show_onetime,
            'show_drp'=>$show_drp,
            'show_fixed'=>$show_fixed,
            'htcc_0'=>$highticketcc_0,
            'htcc_1'=>$highticketcc_1,
            'htec_0'=>$highticketec_0,
            'htec_1'=>$highticketec_1,
            'htamx_0'=>$highticketamex_0,
            'htamx_1'=>$highticketamex_1]);
        session()->save();
        
        return view('makepayment.pay');
    }

    public function step2payone(Request $request) {
        $obj_property = new Properties();
        $property_id = Auth::user()->property_id;
        $web_user_id = Auth::user()->web_user_id;
        
        $input_data=$request->all();
        //validate amounts
        $total_amount=0;
        $categories=array();
        $user_categories=json_decode(session('categories'),true);
        foreach($input_data as $key=>$value){
            if(substr($key,0,10)=='xcheckpay_'){
                $nkey=str_replace('xcheckpay_','',$key);
                if(isset($input_data['xinputpay_'.$nkey])){
                    $amount=$input_data['xinputpay_'.$nkey]*$input_data['xqty_'.$nkey];
                    if($amount>0){
                        $total_amount+=$amount;
                        $cname=$this->getCatName($nkey,$user_categories);
                        $categories[]=array('id'=>$nkey,'amount'=>$amount,'description'=>$cname,'name'=>$cname,'qty'=>$input_data['xqty_'.$nkey]);
                    }
                }
            }
        }
        if($total_amount<=0){
            return redirect()->back()->withErrors(['Invalid Amount - Please review your information and try again']);
        }
        $input_data['categories']=$categories;
        $input_data['total_amount']=$total_amount;
        //validate date
        $exclude_novault=false;
        $paydate=$input_data['xmonth'].'-'.$input_data['xday'];
        if(strtotime($paydate)<strtotime(date('Y-m-d'))){
            return redirect()->back()->withErrors(['Invalid Date - Please review your information and try again']);
        }
        elseif(strtotime($paydate)>strtotime(date('Y-m-d'))){
            $exclude_novault=true;
        }
        $input_data['paydate']=$paydate;
        //read payment profiles and valid payment methods
        $credentials=$obj_property->getCredByAmountType($property_id, $total_amount,0,$exclude_novault);
        $input_data['credentials']=$credentials;
        //extract valid payment methods in cred
        $valid_paymethod=$this->extractPayMethods($credentials);
        $user_pay_methods=$obj_property->getPaymentProfilesUser($web_user_id, $valid_paymethod);
        session(['input_data1'=>$input_data,'credentials'=>$credentials,'payor_profiles'=>$user_pay_methods]);
        session()->save();
        return view('makepayment.pay2stepone');
    }
    
    public function step2payauto(Request $request) {
        return view('makepayment.pay2stepauto');
    }
    
    public function step3review(Request $request) {
        $obj_property = new Properties();
        $property_id = Auth::user()->property_id;
        $web_user_id = Auth::user()->web_user_id;
        
        $input_data=$request->all();
        
        $method=$input_data['method_type'];
        $pay_method=array();
        switch($method){
            case 'profile':
                $pay_method['method']='prf';
                $pay_method['profile_id']=$input_data['xprofile'];
                $pay_method['profile_details']=$this->extractProfile($pay_method['profile_id'],session('payor_profiles'));
                break;
            case 'ec':
                $pay_method['method']='ec';
                $pay_method['bankAccountName']=trim($input_data['bankAccountName']);
                $pay_method['bankAccountType']=trim($input_data['bankAccountType']);
                $pay_method['bankAccountRouting']=trim($input_data['bankAccountRouting']);
                $pay_method['bankAccountAccount']=trim($input_data['bankAccountAccount']);
                break;
            case 'cc':
                $pay_method['method']='cc';
                $pay_method['cardName']=trim($input_data['cardName']);
                $pay_method['cardNumber']=trim($input_data['cardNumber']);
                $pay_method['cardExp']=trim($input_data['cardExp']);
                $pay_method['cardZip']=trim($input_data['cardZip']);
                $pay_method['cardType']=$this->getCardType($pay_method['cardNumber']);
                break;
            default:
                //error
                return redirect()->back()->withErrors(['Invalid Payment Method - Please review your information and try again']);
        }
        session(['paymethod'=>$pay_method]);
        session()->save();
        return view('makepayment.payreview');
    }
    
    public function step4exec(Request $request) {
        $property_id = Auth::user()->property_id;
        $web_user_id = Auth::user()->web_user_id;
        $user = Auth::user();
        
        $payment_details=session('input_data1');
        $credentials=session('credentials');
        $payment_method=session('paymethod');
        $categories=$payment_details['categories'];
        var_dump($payment_details,$payment_method,$credentials);
        //verify mode
        if($payment_details['paytype']=='one'){
            //one time
            if(strtotime($payment_details['paydate'])==strtotime(date('Y-m-d'))){
                echo 'today';
                //is a onetime now
                //prepare record for table & paymentinfo for processor
                $paymentInfo=array();
                $paymentInfo['net_amount']=$payment_details['total_amount'];
                $paymentInfo['memo']='';
                
                $record=array();
                $record['property_id']=$property_id;
                $record['trans_web_user_id']=$web_user_id;
                $record['trans_net_amount']=$payment_details['total_amount'];
                $record['trans_status']=0;
                $record['trans_first_post_date']=date('Y-m-d H:i:s');
                $record['trans_last_post_date']=$record['trans_first_post_date'];
                $record['trans_final_post_date']=$record['trans_first_post_date'];
                $record['source']='WEB';
                $record['nacha_type']='WEB';
                $record['trans_account_number']=trim($user->account_number);
                $record['trans_user_name']=trim($user->first_name.' '.$user->last_name);
                $record['trans_type']=0;
                $record['last_updated_by'] = 'system';
                $record['data']='';
                $record['trans_profile_id']=0;
                $record['invoice_number']='';
                $record['orderid']='';
                
                switch($payment_method['method']){
                    case 'prf':
                        //profile
                        $record['trans_profile_id']=$payment_method['profile_id'];
                        $record['trans_gw_custnum']=$payment_method['profile_id'];
                        $record['trans_payment_type']=$payment_method['profile_details']['type'];
                        if($record['trans_payment_type']=='ec'){
                            $record['trans_card_type']=$payment_method['profile_details']['token']['ec_checking_savings'].'('.substr($payment_method['profile_details']['token']['ec_account_number'],-4).')';
                            $paymentInfo['ec_account_holder']=$payment_method['profile_details']['token']['ec_account_holder'];
                            $paymentInfo['ec_routing_number']=$payment_method['profile_details']['token']['ec_routing_number'];
                            $paymentInfo['ec_account_number']=$payment_method['profile_details']['token']['ec_account_number'];
                            $paymentInfo['ec_checking_savings']=$payment_method['profile_details']['token']['ec_checking_savings'];
                            $payment_method['method']='ec';
                        }
                        else {
                            $record['trans_card_type']=$payment_method['profile_details']['token']['cc_type'].'('.substr($payment_method['profile_details']['name'],-4).')';
                            $paymentInfo['token']=$payment_method['profile_details']['token']['vid'];
                            $paymentInfo['cc_type']=$payment_method['profile_details']['token']['cc_type'];
                        }
                        break;
                    case 'ec':
                        //ec
                        $record['trans_payment_type']='ec';
                        $record['trans_card_type']=$payment_method['bankAccountType'].'('.substr($payment_method['bankAccountAccount'],-4).')';
                        if($record['trans_user_name']==''){
                            $record['trans_user_name']=$payment_method['bankAccountName'];
                        }
                        $paymentInfo['ec_account_holder']=$payment_method['bankAccountName'];
                        $paymentInfo['ec_routing_number']=$payment_method['bankAccountRouting'];
                        $paymentInfo['ec_account_number']=$payment_method['bankAccountAccount'];
                        $paymentInfo['ec_checking_savings']=$payment_method['bankAccountType'];
                        break;
                    case 'cc':
                        //cc
                        if(substr($payment_method['cardType'],0,1)=='A'){
                            $record['trans_payment_type']='amex';
                        }
                        else {
                            $record['trans_payment_type']='cc';
                        }
                        $record['trans_card_type']=$payment_method['cardType'].'('.substr($payment_method['cardNumber'],-4).')';
                        if($record['trans_user_name']==''){
                            $record['trans_user_name']=$payment_method['cardName'];
                        }
                        $paymentInfo['cardname']=$payment_method['cardName'];
                        $paymentInfo['cardnumber']=$payment_method['cardNumber'];
                        $paymentInfo['exp_date']=$payment_method['cardExp'];
                        $paymentInfo['zip']=$payment_method['cardZip'];
                        $paymentInfo['cc_type']=$payment_method['cardType'];
                        break;
                }
                $record['trans_convenience_fee']=$this->extractCFee($credentials,$record['trans_payment_type']);
                $record['trans_total_amount']=$record['trans_net_amount']+$record['trans_convenience_fee'];
                $record['trans_descr']=$this->getPayment_descr($categories, $record['trans_convenience_fee']);
                $record['trans_source_key']=$this->extractCredID($credentials,$record['trans_payment_type']);
                //record ready for accounting_transactions
                $paymentInfo['fee']=$record['trans_convenience_fee'];
                //get credential
                $credential=$this->extractCredByID($credentials, $record['trans_source_key']);
                $result=$this->makePayment($record,$paymentInfo,$credential,$categories);
                //show receipt
                
            }
            else {
                //onetime future (autopayment)
            }
        }
        else {
            //auto
        }
        
    }
    
    function makePayment($record,$paymentInfo,$credential,$categories){
        $property_id = Auth::user()->property_id;
        $web_user_id = Auth::user()->web_user_id;
        $id_company=session('idCompany');
        $id_partner=session('idPartner');
        
        $obj_payment = new \App\CustomClass\PaymentProcessor();
        $obj_transaction = new Transations();
        
        $trans_id=$obj_transaction->addTransaction($record);
        $obj_transaction->addCatforTransaction($trans_id,$categories,$property_id,$id_company,$id_partner,$web_user_id);
        
        $credentialArray=array('mid'=>$credential->payment_source_merchant_id,
                               'lid'=>$credential->payment_source_location_id,
                               'sid'=>$credential->payment_source_store_id,
                               'key'=>$credential->payment_source_key,
                               'payment_method'=>$credential->payment_method,
                               'gateway'=>$credential->gateway);
        $paymentInfo['trans_id']=$trans_id;
        //the flow change per gateway related to cfee
        if($record['trans_convenience_fee']==0){
            //only make a payment
            $paymentInfo['total_amount']=$paymentInfo['net_amount'];
            $result=$obj_payment->RunTx($paymentInfo, $credentialArray);
            $obj_transaction->updatePayment($trans_id, $result);
            return $result;
        }
        else {
            //sometimes we need to do a separated payment for fee
            switch($credentialArray['gateway']){
                case 'bokf':
                case 'nmi':
                case 'fd4':
                case 'fde4':
                case 'prismpay':
                    $paymentInfo['total_amount']=$paymentInfo['net_amount']+$paymentInfo['fee'];
                    $result=$obj_payment->RunTx($paymentInfo, $credentialArray);
                    $obj_transaction->updatePayment($trans_id, $result);
                    return $result;
                    break;
                case 'express':
                    if($record['trans_payment_type']=='amex'){
                        $cfee=$paymentInfo['fee'];
                        $paymentInfo['fee']=0;
                        $paymentInfo['total_amount']=$paymentInfo['net_amount'];
                        $result=$obj_payment->RunTx($paymentInfo, $credentialArray);
                        if($result['response'] == 1){
                            $cfee_record=$record;
                            $cfee_record['trans_net_amount']=$cfee;
                            $cfee_record['trans_convenience_fee']=0;
                            $cfee_record['trans_total_amount']=$cfee;
                            $paymentInfo['total_amount']=$cfee;
                            $cfee_record['parent_trans_id']=$trans_id;
                            $cfee_record['is_convenience_fee_trans']=1;
                            $cfee_trans_id=$obj_transaction->addTransaction($cfee_record);
                            \Illuminate\Support\Facades\DB::table('accounting_transactions')->where('trans_id',$trans_id)->update(['cfee_trans_id'=>$cfee_trans_id]);
                            $result_fee=$result=$obj_payment->RunTx($paymentInfo, ['gateway'=>'express','isFee'=>true]);
                            $obj_transaction->updatePayment($cfee_trans_id, $result_fee);
                        }
                        $obj_transaction->updatePayment($trans_id, $result);
                        return $result;
                    }
                    else {
                        $paymentInfo['total_amount']=$paymentInfo['net_amount']+$paymentInfo['fee'];
                        $result=$obj_payment->RunTx($paymentInfo, $credentialArray);
                        $obj_transaction->updatePayment($trans_id, $result);
                        return $result;
                    }
                    break;
                case 'profistar':
                    $paymentInfo['total_amount'] = $paymentInfo['net_amount'];
                    $result=$obj_payment->RunTx($paymentInfo, $credentialArray);
                    if($result['response'] == 1){
                        $cfee_record=$record;
                        $cfee_record['trans_net_amount']=$paymentInfo['fee'];
                        $cfee_record['trans_convenience_fee']=0;
                        $cfee_record['trans_total_amount']=$paymentInfo['fee'];
                        $paymentInfo['total_amount']=$paymentInfo['fee'];
                        $cfee_record['parent_trans_id']=$trans_id;
                        $cfee_record['is_convenience_fee_trans']=1;
                        $cfee_trans_id=$obj_transaction->addTransaction($cfee_record);
                        \Illuminate\Support\Facades\DB::table('accounting_transactions')->where('trans_id',$trans_id)->update(['cfee_trans_id'=>$cfee_trans_id]);
                        $result_fee=$result=$obj_payment->RunTx($paymentInfo, ['gateway'=>'profistar']);
                        $obj_transaction->updatePayment($cfee_trans_id, $result_fee);
                    }
                    $obj_transaction->updatePayment($trans_id, $result);
                    return $result;
                    break;
            }
        }
    }
    
    function getCatName($nkey,$user_categories){
        foreach($user_categories as $cat){
            if($cat['payment_type_id']==$nkey){
                return $cat['payment_type_name'];
            }
        }
        return 'Payment';
    }
    
    function extractPayMethods($credentials){
        $valid=array();
        foreach($credentials as $cred){
            if(!in_array($cred->payment_method, $valid)){
                $valid[]=$cred->payment_method;
            }
        }
        return $valid;
    }
    
    function extractProfile($id,$profiles){
        $profile=array();
        foreach($profiles as $prof){
            if($prof['id']==$id){
                return $prof;
            }
        }
        return $profile;
    }
    
    function extractCFee($credentials,$type,$drp=false){
        $cfee=0;
        foreach($credentials as $cred){
            if($cred->payment_method==$type){
                if(!$drp){
                    return $cred->cfee_amount;
                }
                else {
                    return $cred->cfee_amount_drp;
                }
            }
        }
        return $cfee;
    }
    
    function extractCredID($credentials,$type){
        $cfee=0;
        foreach($credentials as $cred){
            if($cred->payment_method==$type){
                return $cred->merchant_account_id;
            }
        }
        return $cfee;
    }
    
    function extractCredByID($credentials, $id){
       foreach($credentials as $cred){
            if($cred->merchant_account_id==$id){
                return $cred;
            }
        } 
    }
    
    function getPayment_descr($categories, $cfee, $memo=null, $invnumber=null) {
        $detail = '';
        if (!empty($memo))
            $detail.='Memo- ' . $memo . "\n";
        $detail.='Payment Details:' . "\n";
        if (!empty($invnumber))
            $detail.='Invoice #:' . $invnumber . "\n";
        $total = 0;
        for ($i = 0; $i < count($categories); $i++) {
            if ($categories[$i]['amount'] > 0.00) {
                if (!isset($categories[$i]['qty'])) {
                    $categories[$i]['qty'] = 1;
                }
                $total+=$categories[$i]['amount'] * $categories[$i]['qty'];
                $detail.=$categories[$i]['name'] . ':' . $categories[$i]['qty'] . ' x $' . number_format($categories[$i]['amount'], 2, '.', ',') . "\n";
            }
        }

        if (!empty($cfee) && $cfee > 0) {
            $detail.='Convenience Fee: $' . number_format($cfee, 2);
            $detail.="\n";
        }
        $detail.='---------------------' . "\n";
        $detail.='Total Payment: $' . number_format($cfee + $total, 2, '.', ',');

        return $detail;
    }
    
    function getCardType($ccNum){
       $type="Unknow";
        if (preg_match("/^5[1-5][0-9]{14}$/", $ccNum))
                $type= "MasterCard";
 
        if (preg_match("/^4[0-9]{12}([0-9]{3})?$/", $ccNum))
                $type= "Visa";
 
        if (preg_match("/^3[47][0-9]{13}$/", $ccNum))
                $type= "AmericanExpress";
 
        if (preg_match("/^3(0[0-5]|[68][0-9])[0-9]{11}$/", $ccNum))
                $type= "DinersClub";
 
        if (preg_match("/^6011[0-9]{12}$/", $ccNum))
                $type= "Discover";
 
        if (preg_match("/^(3[0-9]{4}|2131|1800)[0-9]{11}$/", $ccNum))
                $type= "JCB";
        
        return $type; 
    }
    
    public function payHistory(Request $request) {
        return view('payHistory');
    }

    public function payHistoryDataTable(Request $request){
        $obj_trans = new Transations();
        $settings = session('settings');
        $data = $obj_trans->getTransByUsrId(Auth::user()->web_user_id);

        if($data->get()->isEmpty()){
            $dataMsg['message'] = Lang::get('messages.youHaventPay');
            return response()->json([
                'body' => View::make('components.dataNotFoundAlertMessage', $dataMsg)->render(),
                'code' => 1,
                'records' => 0
            ]);
        }


        $grid = \DataGrid::source($data);
        $grid->attributes(array("class" => "table table-responsive table-striped table-hover table-payhistory m-b-sm"));


        $grid->add('trans_id','')->cell(function ($value){
            return '<a class="showTransactionDetails cursorPointer" data="'.$value.'"><i class="fa fa-info-circle text-info"></i></a>';
        });
        $grid->add('trans_type',Lang::get('messages.status'))->cell(function ($value,$row){
           switch ($value){
               case(9):
                   return '<i data-toggle="tooltip" title="'.Lang::get('messages.voidedTransaction').'" class="fa fa-close text-info"></i>';
                   break;
               case(5):
                   return '<i data-toggle="tooltip" title="'.Lang::get('messages.refundedTransaction').'" class="fa fa-arrow-left text-info"></i>';
                   break;
               case(2):
                   return '<i data-toggle="tooltip" title="'.Lang::get('messages.returnedTransaction').'" class="fa fa-arrow-left text-danger"></i>';
                   break;
               case(1):
               case(0):
                    //trans_status
               switch ($row->trans_status){
                   case(1):
                       return '<i data-toggle="tooltip" title="'.Lang::get('messages.approvedTransaction').'" class="fa fa-check text-success"></i>';
                       break;
                   case(4):
                       return '<i data-toggle="tooltip" title="'.Lang::get('messages.voidedTransaction').'" class="fa fa-close text-info"></i>';
                       break;
                   case(0):
                       return '<i data-toggle="tooltip" data-html="true" title="'.Lang::get('messages.erroredTransaction').': <br/> '.$row->trans_result_error_desc .'" class="fa fa-close text-danger"></i>';
                       break;
                   default:
                       return '<i data-toggle="tooltip" title="'.Lang::get('messages.declinedTransaction').'" class="fa fa-close text-danger"></i>';
               }
               break;
               default:
                   return '<i data-toggle="tooltip" data-html="true" title="'.Lang::get('messages.unknown').'" class="fa fa-close text-muted"></i>';

           }
        })->style('text-align:center');
        $grid->add('trans_first_post_date',Lang::get('messages.date'))->cell(function ($value){
            return Lang::get('messages.'.strtolower(date('M',  strtotime($value)))). date(' j, Y, g:i a',  strtotime($value));
        });
        $grid->add('trans_net_amount',Lang::get('messages.amount'))->cell(function ($value){
            return '$'.number_format($value, 2);
        });
        $grid->add('trans_convenience_fee',Lang::get('messages.fee'))->cell(function ($value){
            return '$'.number_format($value, 2);
        });
        $grid->add('trans_total_amount',Lang::get('messages.totalPaid'))->cell(function ($value){
            return '$'.number_format($value, 2);
        });
        $grid->add('trans_card_type',Lang::get('messages.paymentMethod'))->cell(function ($value){
            if(substr_count(strtolower($value),'checking')>0 || substr_count(strtolower($value),'saving')>0)
                return '<img data-toggle="tooltip" title="'.$value.'" class="bankcheck" src="'.asset('images/bankcheck.svg').'">';
            elseif(substr_count(strtolower($value),'visa')>0)
                return '<img data-toggle="tooltip" title="'.$value.'" class="visa" src="'.asset('images/visa.svg').'">';
            elseif(substr_count(strtolower($value),'mastercard')>0)
                return '<img data-toggle="tooltip" title="'.$value.'" src="'.asset('images/mastercard.svg').'">';
            elseif(substr_count(strtolower($value),'discover')>0)
                return '<img data-toggle="tooltip" title="'.$value.'" class="visa" src="'.asset('images/discover.svg').'">';
            elseif(substr_count(strtolower($value),'american')>0)
                return '<img data-toggle="tooltip" title="'.$value.'" src="'.asset('images/amex.svg').'">';
            elseif(substr_count(strtolower($value),'cash')>0)
                return '<img data-toggle="tooltip" title="'.$value.'" src="'.asset('images/cash.svg').'">';
        })->style('text-align:center');
        $grid->add('trans_result_auth_code',Lang::get('messages.approvalCode'));

        /*
         * Handled the transactions that does not have invoices numbers
         */
        if($settings['INVSETTING'] !=1 ){
            $grid->add('invoice_number',Lang::get('messages.invoiceNumber'))->cell(function ($value){
                if(!is_null($value) && '' != trim($value)) {
                    $invoice = (new Invoices())->findInvoiceByNumber($value, Auth::user()->property_id);
                    if (!is_null($invoice)) {
                        return '<a href="' . route("viewinvoice", $invoice->id) . '">' . $value . '</a>';
                    }
                }
                return '';
            });
        }

        $grid->row(
            function ($row) {
                $row->cell('trans_card_type')->style('text-align:center');
                $row->cell('trans_type')->style('text-align:center');
            }
        );

        $itemsPerPage = 10;
        $grid->orderBy('trans_id', 'DESC');
        $grid->paginate($itemsPerPage);
        $body = View::make('components.ajaxGrid',['grid'=>$grid])->render();

        return response()->json([
            'body' => $body,
            'code' => 1,
            'records' => $grid->paginator->total()
        ]);
    }

    public function dashboard(Request $request){

        if(!Auth::check()){
            $obj_expired = new LoginController();
            $obj_expired->login();
        }
        $data = array();
        $idProperty = Auth::user()->property_id;
        $webUserId = Auth::user()->web_user_id;
        $obj_trans = new Transations();
        $data['trans'] = $obj_trans->getTransByUsrId_5($webUserId);
        $data['autopay'] = $obj_trans->getAutoTrans_dash($webUserId, $idProperty);

        return view('dashboard', $data);
    }

    public function autopay($ajax = 0,Request $request) {
        if(session()->has('new_onetime_payment')){
            $this->resetpay();
        }
        $settings = session('settings');
        $web_user_id = Auth::user()->web_user_id;
        $property_id = Auth::user()->property_id;

        $data = array();

        $obj_property = new Properties();
        $obj_user = new User();

        //get payments Categories
        $paymentCategories = $obj_property->getPaymentWebUserCategories($web_user_id);
        if(empty($paymentCategories)){
            $paymentCategories = $obj_property->getPaymentType($property_id,$settings);
        }
        $data['paymentCategories'] = $paymentCategories;

        //get recurring credential
        $data['credRecurring'] = $obj_property->getcredRecurringCredentials($property_id);

        //get autopay info
        $obj_trans = new Transations();
        $autopay = $obj_trans->getAutopayByUsrId($web_user_id);

        $data['autopays'] = $autopay;

        //get # of autopays
        $user_aytopays = $obj_user->getAutoCountByUser($web_user_id, $property_id);


        //get MAXRECURRINGPAYMENTPERUSER setting
        $autolimit = isset($settings['MAXRECURRINGPAYMENTPERUSER']) ? $settings['MAXRECURRINGPAYMENTPERUSER'] : '';
        $custnap = isset($settings['CUSTOMAUTOPAYMSG']) ? $settings['CUSTOMAUTOPAYMSG'] : '';
        if(trim($custnap)!=''){
            $data['custnap']=$custnap;
        }
        if (empty($autolimit))
            $autolimit = 1000; //no limit

        if ($autolimit <= $user_aytopays) {
            $data['noautopaymsg'] = 1;
        }

        $custnapd = isset($settings['DISABLEAUTOPAYMSG']) ? $settings['DISABLEAUTOPAYMSG'] : '';
        if($custnapd==1){
            $data['custnap']='&nbsp;';
        }


        $autoPaymentsHTML = View::make('components.autoPayments',array('data'=>$data))->render();
        if($ajax==0){
            return view('autopay', array('autoPaymentsHTMl'=>$autoPaymentsHTML,'data'=>$data));
        }
        else{
            return response()->json([
                'body'=> $autoPaymentsHTML,
                'code' => 1,
                'noalert'=>1
            ]);
        }

    }

    function cancelAutoPay($trans_id, Request $request) {
        $user = Auth::user();
        $web_user_id = $user->web_user_id;
        $obj_trans = new Transations();
        $idproperty = $user->property_id;

        if ($obj_trans->cancelAutopay($trans_id,$web_user_id)) {
            //log cancel autopay
            RevoPayAuditLogger::autopaymentDelete('user', array('operation' => 'Autopayment delete','trans_id'=>$trans_id), 'M', $idproperty, WebUsers::getAuditData($web_user_id), Auth::user());
            $obj_trans = new Transations();
            $autopay = $obj_trans->getAutopayByUsrId($web_user_id);
            $data['autopays'] = $autopay;
            $autoPaymentsHTML = View::make('components.autoPayments',array('data'=>$data))->render();

            return response()->json(array(
                'code' => 1,
                'message' => Lang::get('messages.successfullyMessage',['action'=>Lang::get('messages.cancelled'), 'variable'=>Lang::get('messages.autopay')]),
                'body'=>$autoPaymentsHTML));
        }

        return response()->json(array('code' => 0, 'message' => 'Sorry! We cannot cancel this AutoPay.'));
    }

    function editautocat($trans_id, Request $request) {

        $settings = session('settings');
        $web_user_id = Auth::user()->web_user_id;
        $property_id = Auth::user()->property_id;

        $obj_trans = new Transations();
        $obj_property = new Properties();
        $data = array();
        //get payments Categories
        //check if user has categories
        $paymentCategories = $obj_property->getPaymentWebUserCategories($web_user_id);

        if(empty($paymentCategories)){
            $paymentCategories = $obj_property->getPaymentType($property_id,$settings);
        }

        foreach ($paymentCategories as $category) {
            if(floatval($category->amount) > 0) {
                $category->isreadonly = true;
            }
        }

        //get saved categories by trans_id
        $recurringPaymentCat = $obj_property->getReccurringPaymentType($trans_id);

        for ($i = 0; $i < count($paymentCategories); $i++) {
            for ($j = 0; $j < count($recurringPaymentCat); $j++) {
                if ($paymentCategories[$i]->payment_type_id == $recurringPaymentCat[$j]->category_id) {
                    $paymentCategories[$i]->amount = $recurringPaymentCat[$j]->amount;
                    $paymentCategories[$i]->qty = $recurringPaymentCat[$j]->qty;
                    $paymentCategories[$i]->enabled = 1;
                    break;
                }
            }
        }

        $data['autopay_info'] = $obj_trans->getAutopayInfoByTrans_id($trans_id,$property_id,$web_user_id);

        if (!$data['autopay_info']) {
            $returnHtml = View::make('components.dataNotFoundAlert')->render();
            return response()->json([
                'body'=> $returnHtml,
                'code' => 1,
                'noalert'=>1
            ]);
        }


        $data['paymentCategories'] = $paymentCategories;
        session([
            'paymentCategories' => $paymentCategories,
        ]);
        session()->save();


        // credentials
        $data['dbcredentials'] = $obj_property->getcredRecurringCredentials($property_id);

        $data['trans_id'] = $trans_id;

        //var_dump($data['paymentCategories']); exit();

        $returnHtml = View::make('components.autopayEditAmountNew',$data)->render();

        return response()->json([
            'body'=> $returnHtml,
            'code' => 1,
            'noalert'=>1
        ]);


    }

    function editautocatfee($data, Request $request){

        $array = json_decode($data,1);

        if($array && isset($array['type']) && isset($array['amount'])){

            /*if((float)$array['amount']  == 0){
                return response()->json([
                    'message'=> 'The amount cannot be empty or zero',
                    'code' => 0,
                ]);
            }*/

            $web_user_id = Auth::user()->web_user_id;
            $property_id = Auth::user()->property_id;
            $obj_trans = new Transations();
            $obj_property = new Properties();
            $credentials = $obj_property->getCredentialtype_isrecurring($array['type'], $property_id, 1);
            $convfee = $obj_trans->getFee($credentials, $array['amount']);


            if($convfee['ERROR'] == 1){
                return response()->json([
                    'message'=> $convfee['ERRORCODE'],
                    'code' => 0,
                ]);
            }
            else{
                return response()->json([
                    'noalert'=>1,
                    'cfee'=> $convfee['CFEE'],
                    'code' => 1,
                ]);
            }
        }
        else{
            return response()->json([
                'message'=> 'Sorry. Incorrect data format',
                'code' => 0,
            ]);
        }



    }

    function savecategories($id,Request $request) {

        $web_user_id = Auth::user()->web_user_id;
        $property_id = Auth::user()->property_id;

        $data_submit = $request->all();
        unset($data_submit['_token']);

        $obj_transactions = new Transations();
        $autopay_info = $obj_transactions->getAutopayInfoByTrans_id($id,$property_id,$web_user_id);
        $autopay_info->idProperty= session('idProperty');
        $autopay_info->idPartner= session('idPartner');
        $autopay_info->idCompany= session('idCompany');

        $updated = $obj_transactions->updateReccuringTransCat($id, $data_submit,$autopay_info,$web_user_id);

        if(empty($updated)){
            return response()->json(array(
                'code' => 0,
                'message' => 'Sorry, the Autopayment cannot be updated.',
            ));
        }

        $current_cat = json_decode(json_encode(session('paymentCategories')), true);
        $before_updated = array();
        $categories = array();
        $before_amount = 0;
        $net_amount = 0;
        for($i=0;$i<count($current_cat);$i++){
            if(isset($current_cat[$i]['enabled']) && $current_cat[$i]['enabled'] == 1){
                $before_updated[$current_cat[$i]['payment_type_name']] = 'Amount: '.$current_cat[$i]['amount'];
                $before_amount += $current_cat[$i]['amount'];
            }
        }
        for($i=0;$i<count($updated);$i++){

            $categories[$updated[$i]['name']] = 'Amount: '.$updated[$i]['amount'];
            $net_amount += $updated[$i]['amount'];
        }
        if((float)$net_amount  == 0){
            return response()->json([
                'message'=> 'The amount cannot be empty or zero',
                'code' => 0,
            ]);
        }

        RevoPayAuditLogger::autopaymentUpdate('user', array('operation' => 'Update Autopayment Categories', 'info'=> array('trans_id'=> $id, 'total_amount' => $before_amount, 'categories' => $before_updated)), 'M', $property_id, WebUsers::getAuditData($web_user_id), Auth::user(), Auth::user()->username, Audit::$BEFORE_UPDATE);
        RevoPayAuditLogger::autopaymentUpdate('user', array('operation' => 'Update Autopayment Categories', 'info'=> array('trans_id'=> $id, 'total_amount' => $net_amount, 'categories' => $categories)), 'M', $property_id, WebUsers::getAuditData($web_user_id), Auth::user(), Auth::user()->username, Audit::$AFTER_UPDATE);
        $return_html = $this->autopay(1, $request);
        return response()->json(array(
            'code' => 1,
            'message' => Lang::get('messages.successfullyMessage',['action'=>Lang::get('messages.updated'), 'variable'=>Lang::get('messages.autopay')]),
            'body' => $return_html->getData(),
        ));

    }

    function editAutopayPaymentMethod($id, Request $request) {
        $settings = session('settings');//var_dump($settings);
        $web_user_id = Auth::user()->web_user_id;
        $property_id = Auth::user()->property_id;

        $data = array();
        $obj_property = new Properties();
        $obj_trans = new Transations();
        $array_method = $obj_property->getTypeCredentialByCycle($property_id, 1);

        //get payments profiles
        $obj_user = new User();

        $profiles = $obj_user->getPaymentProfiles($web_user_id);


        $recurring_trans = $obj_trans->getAutopayInfoByTrans_id($id,$property_id,$web_user_id);
        //$id_profile = $obj_trans->get1recurringInfo($id, 'profile_id');
        $pro_sel = $obj_user->getPaymentProfileById($web_user_id, $recurring_trans->profile_id);

        $data['profiles'] = array();
        if (!empty($pro_sel)) {
            $data['profiles'][0] = $pro_sel;
        }
        
        $validMethods = [];
        if ($recurring_trans->dynamic && isset($settings['DRPMETHODS'])) {
            $drpMethods = explode('|', $settings['DRPMETHODS']);
            if (in_array('ec', $drpMethods)) {
                array_push($validMethods, 'ec');
            }
            if (in_array('cc', $drpMethods)) {
                array_push($validMethods, 'cc');
                array_push($validMethods, 'amex');
            }
        } else {
            $validMethods = ['amex','cc','ec'];
        }
        
        $data['validDrpMethods'] = $validMethods;

        for ($i = 0; $i < count($profiles); $i++) {
            if (in_array($profiles[$i]->type, $array_method) && in_array($profiles[$i]->type, $validMethods)) {
                if (empty($pro_sel) || $profiles[$i]->id != $pro_sel->id) {
                    $data['profiles'][] = $profiles[$i];
                }
            }
        }

        $current_prf = [
            'profile_id' => $recurring_trans->profile_id,
            'name' => isset($pro_sel->name) ? $pro_sel->name : $recurring_trans->trans_card_type,
            'type' => isset($pro_sel->type) ? $pro_sel->type : $recurring_trans->trans_payment_type
        ];

        session([
            'data_profile' => $current_prf,
        ]);

        $data['dbcredentials'] = $obj_property->getcredRecurringCredentials($property_id);
        $html = View::make('components.autopayEditMethod',$data)->render();

        return response()->json([
            'body'=> $html,
            'code' => 1
        ]);
    }

    function saveAutopayPaymentMethod($type,$id,PaymentProfileValidations  $request){

        $idProfile = $request->get('savedPaymentMethodsSelect');
        $web_user_id = Auth::user()->web_user_id;
        $property_id = Auth::user()->property_id;

        $obj_transaction = new Transations();
        $obj_user = new User();

        $net_amount = $obj_transaction->get1recurringInfo($id, 'trans_recurring_net_amount');
        if (strtolower($type) == 'prf') {
            $prfInfo = $obj_user->getPaymentProfileById($web_user_id, $idProfile);
            if($prfInfo->type == 'am' || $prfInfo->type == 'am'){
                $prfInfo->type = 'amex';
            }
            $secure_prf = $this->validateDecrypt($prfInfo->token);
            $card_info = json_decode($secure_prf, true);
            $card_info['profile_id'] = $idProfile;
            $card_info['name'] = $prfInfo->name;
            if (strtolower($prfInfo->type) == 'ec') {
                //changing convenience fee
                $obj_property = new Properties();
                $credentials = $obj_property->getCredentialtype_isrecurring($prfInfo->type, $property_id, 1);
                $convfee = $obj_transaction->getFee($credentials, $net_amount);
                if ($convfee['ERROR'] == 1) {
                    return response()->json(array('code' => 0, 'message' => $convfee['ERRORCODE']));
                }
                $dynamic = $obj_transaction->get1recurringInfo($id, 'dynamic');
                if ($dynamic > 0) {
                    $convfee['CFEE'] = 0;
                }

                $obj_transaction->updateECReccurringMethod($id, $card_info, $web_user_id, $prfInfo->type, $convfee['CFEE']);
            } else {
                //changing convenience fee
                $obj_property = new Properties();
                $credentials = $obj_property->getCredentialtype_isrecurring($prfInfo->type, $property_id, 1);
                $convfee = $obj_transaction->getFee($credentials, $net_amount);
                if ($convfee['ERROR'] == 1) {
                    return response()->json(array('code' => 0, 'message' => $convfee['ERRORCODE']));
                }
                $dynamic = $obj_transaction->get1recurringInfo($id, 'dynamic');
                if ($dynamic > 0) {
                    $convfee['CFEE'] = 0;
                }
                $obj_transaction->updateCCReccurringMethod($id, $card_info, $web_user_id, $prfInfo->type, $convfee['CFEE']);
            }

            $data_updated = [
                'profile_id' => $idProfile,
                'name' => $prfInfo->name,
                'type' => $prfInfo->type,
            ];
            RevoPayAuditLogger::autopaymentUpdate('user', array('operation' => 'Update Autopayment Payment Method', 'data'=>session('data_profile')), 'M', $property_id, WebUsers::getAuditData($web_user_id), Auth::user(), Auth::user()->username, Audit::$BEFORE_UPDATE);
            RevoPayAuditLogger::autopaymentUpdate('user', array('operation' => 'Update Autopayment Payment Method', 'data'=>$data_updated), 'M', $property_id, WebUsers::getAuditData($web_user_id), Auth::user(), Auth::user()->username, Audit::$AFTER_UPDATE);
        }

        $return_html = $this->autopay(1, $request);
        return response()->json(array(
            'code' => 1,
            'message' => Lang::get('messages.successfullyMessage',['action'=>Lang::get('messages.updated'), 'variable'=>Lang::get('messages.autopay')]),
            'body' => $return_html->getData(),
        ));

    }

    function editAutopayFrequency($trans_id, Request $request) {

        $settings = session('settings');
        $web_user_id = Auth::user()->web_user_id;
        $property_id = Auth::user()->property_id;

        $data = array();
        $obj_transactions = new Transations();

        $autpayInfo = $obj_transactions->getAutopayInfoByTrans_id($trans_id, $property_id, $web_user_id);

        $selfreq = $autpayInfo->trans_schedule;
        $data['selfreq'] = $selfreq;

        $obj_property = new Properties();

        if ($autpayInfo->dynamic == 1) {
            $data['isdrp'] = 1;
            $freq = $obj_property->getFreqDrp($settings);
            $days = $obj_property->getDaysDrp($settings);
        } else {
            $data['isdrp'] = 0;
            $freq = $obj_property->getFreqAutpay($settings);
            $days = $obj_property->getDaysAutopay($settings);
        }

        $left = $autpayInfo->trans_numleft-1;

        if ($autpayInfo->trans_numleft > 900) {
            $selend = -1;
        } else if ($autpayInfo->trans_numleft != 0) {

            switch ($autpayInfo->trans_schedule) {
                case 'weekly':
                    $selend = date('Y|m', strtotime('+' . $left . ' week', strtotime($autpayInfo->trans_next_post_date)));
                    break;
                case 'yearly':
                    $selend = date('Y|m', strtotime('+' . $left . ' year', strtotime($autpayInfo->trans_next_post_date)));
                    break;
                case 'biannually':
                    $left = 6 * $left;
                    $selend = date('Y|m', strtotime('+' . $left . ' months', strtotime($autpayInfo->trans_next_post_date)));
                    break;
                case 'quaterly':
                case 'quarterly':
                    $left = 3 * $left;
                    $selend = date('Y|m', strtotime('+' . $left . ' months', strtotime($autpayInfo->trans_next_post_date)));
                    break;
                case 'triannually':
                    $left = 4 * $left;
                    $selend = date('Y|m', strtotime('+' . $left . ' months', strtotime($autpayInfo->trans_next_post_date)));
                    break;
                case 'biweekly':
                    $left = 14 * $left;
                    $selend = date('Y|m', strtotime('+' . $left . ' days', strtotime($autpayInfo->trans_next_post_date)));
                    break;
                case 'monthly':
                    $selend = date('Y|m', strtotime('+' . $left . ' month', strtotime($autpayInfo->trans_next_post_date)));
                    break;
                default :
                    $selend = date('Y|m', strtotime($autpayInfo->trans_next_post_date));
                    break;
            }
        }

        // REVO 1523: curmon is the selected month used for showing the days in that month when te form loads.
        // t is days in the month.  This is so that the form doesn't load with too many days in the month.
        $data['curmon'] = date('Y-m', strtotime($autpayInfo->trans_next_post_date));
        $data['daysinmonth'] = date('t', strtotime($autpayInfo->trans_next_post_date));
        $data['selday'] = date('j', strtotime($autpayInfo->trans_next_post_date));
        $data['selstart'] = date('Y|m', strtotime($autpayInfo->trans_next_post_date));
        $data['selend'] = $selend;
        $data['freq'] = $freq;
        $data['days'] = $days;

        //adding 5 years in advance to end and start day on the autopayments
        $data['y5inadvance'] = $obj_property->get5yearInAdvance();
        $data['y1inadvance'] = $obj_property->get5yearInAdvance(true,1);

        $autopay_frecuency = [
            'Payment Date' => $data['selday'],
            'Frecuency' => $data['selfreq'],
            'Start Date' => $data['selstart'],
            'End Date' => $data['selend'] == '-1' ? 'Until Canceled' : $data['selend'],
        ];
        session([
            'autopay_frecuency' => $autopay_frecuency,
        ]);
        $returnHtml = View::make('components.autopayeditfreq',$data)->render();

        return response()->json([
            'body'=> $returnHtml,
            'code' => 1,
            'noalert'=>1
        ]);
    }

    function saveAutopayFrequency($id, PaymentFreqValidations $request) {
        $obj_transactions = new Transations();
        $autopayInfo = $request->all();
        $autopayInfo['end_date'] = $autopayInfo['xenddate'];
        $obj_user = Auth::user();
        $webUserId = $obj_user->web_user_id;
        $idproperty = $obj_user->property_id;


        if (!empty($autopayInfo['xstartdate'])) {
            $tmp_day = explode("|", $autopayInfo['xstartdate']);
            $autopayInfo['next_day'] = $tmp_day[0] . "-" . $tmp_day[1] . "-" . $autopayInfo['xday'];
        } else {
            $mtx = $obj_transactions->getRecTransData($id);
            $tmp_day = explode('-', $mtx->trans_next_post_date);
            $autopayInfo['next_day'] = $tmp_day[0] . "-" . $tmp_day[1] . "-" . $autopayInfo['xday'];
        }

        $today = date("Y-m-d");
        $autopayInfo['next_day'] = date("Y-m-d", strtotime($autopayInfo['next_day']));

        if (strtotime($autopayInfo['next_day']) <= strtotime($today)) {
            return response()->json(array('code' => 0, 'message' => 'Next Payment Date should be in the future'));
        }

        if ($autopayInfo['xenddate'] != -1) {
            $tmp_day = explode("|", $autopayInfo['xenddate']);
            $autopayInfo['end_date'] = $tmp_day[0] . "-" . $tmp_day[1] . "-" . $autopayInfo['xday'];
            if (strtotime($autopayInfo['end_date']) < strtotime($autopayInfo['next_day'])) {
                return response()->json(array('code' => 0, 'message' => 'Next Payment Date should be greater than End Payment Date'));
            }
            $obj_mk=new MakePaymentController();
            $autopayInfo['trans_numleft'] = $obj_mk->calculateCycle($autopayInfo['xfreq'], strtotime($autopayInfo['next_day']), strtotime($autopayInfo['end_date']));
        }
        else {
           $autopayInfo['trans_numleft']=9999; 
        }
        
        $autopayInfo['trans_id'] = $id;
        $autopayInfo['freq'] = $autopayInfo['xfreq'];
        DB::table('accounting_recurring_transactions')->where('trans_id',$id)->update(['trans_next_post_date'=>$autopayInfo['next_day'],'trans_numleft'=>$autopayInfo['trans_numleft'],'trans_schedule'=>$autopayInfo['xfreq']]);
        
        $data_updated = [
            'Payment Date' => date('j', strtotime($autopayInfo['next_day'])),
            'Frecuency' => $autopayInfo['xfreq'],
            'Start Date' => date('Y|m', strtotime($autopayInfo['next_day'])),
            'End Date' => $autopayInfo['xenddate'] == '-1' ? 'Until Canceled' : $autopayInfo['xenddate'],
        ];
        RevoPayAuditLogger::autopaymentUpdate('user', array('operation' => 'Update Autopayment Frecuency', 'data'=>session('autopay_frecuency')), 'M', $idproperty, WebUsers::getAuditData($webUserId), Auth::user(), Auth::user()->username, Audit::$BEFORE_UPDATE);
        RevoPayAuditLogger::autopaymentUpdate('user', array('operation' => 'Update Autopayment Frecuency', 'data'=>$data_updated), 'M', $idproperty, WebUsers::getAuditData($webUserId), Auth::user(), Auth::user()->username, Audit::$AFTER_UPDATE);

        $return_html = $this->autopay(1, $request);
        return response()->json(array(
            'code' => 1,
            'message' => Lang::get('messages.successfullyMessage',['action'=>Lang::get('messages.updated'), 'variable'=>Lang::get('messages.autopay')]),
            'body' => $return_html->getData(),
        ));
    }

    public function payMethods(Request $request) {
        $obj_user = Auth::user();
        $webUserId = $obj_user->web_user_id;
        $idProperty = $obj_user->property_id;
        $profiles = $obj_user->getPaymentProfiles($webUserId);
        foreach ($profiles as $profile) {
            $profile->autoPayCount = 0;
            $autoPayCount = $obj_user->getAutoCountByUserProfile($webUserId, $idProperty, $profile->id);
            $profile->autoPayCount = $autoPayCount;
        }
        $credentials = $obj_user->getPayment_Credentials($webUserId,$idProperty);
        $paymentMethodsHTMl = View::make('components.paymentMethods',array('profiles'=>$profiles))->render();
        return view('payMethods', array('paymentMethodsHTMl'=>$paymentMethodsHTMl,'credentials'=>$credentials));
    }

    public function addPayMethods($type, $idEditPaymentMethod = null, PayMethodValidations $request) {

        $user = Auth::user();
        $idProperty = $user->property_id;
        $web_user_id = $user->web_user_id;
        $obj_user = new User();

        if(strtolower($type) == 'ec') {
            $data['ec_account_holder'] = $request->input("bankAccountName");
            $data['ec_routing_number'] = $request->input("bankAccountRouting");
            $data['ec_account_number'] = $request->input("bankAccountAccount");
            $data['ec_checking_savings'] = $request->input("bankAccountType");

            $idprofile = $obj_user->insertECpaymethod($data, session('idPartner'), session('idCompany'), $idProperty, $web_user_id);

            if($idEditPaymentMethod){
                $obj_transaction = new Transations();
                $net_amount = $obj_transaction->get1recurringInfo($idEditPaymentMethod, 'trans_recurring_net_amount');
                $data['name'] = "XXXX- " . substr($data['ec_account_number'], -4);
                //$data = json_encode($profileInfo);
                $data['profile_id'] = $idprofile;
                //changing convenience fee
                $obj_property = new Properties();
                $credentials = $obj_property->getCredentialtype_isrecurring($type, $idProperty, 1);
                $convfee = $obj_transaction->getFee($credentials, $net_amount);
                if ($convfee['ERROR'] == 1) {
                    return response()->json(array('code' => 0, 'message' => $convfee['ERRORCODE']));
                }
                $dynamic = $obj_transaction->get1recurringInfo($idEditPaymentMethod, 'dynamic');
                if ($dynamic > 0) {
                    $convfee['CFEE'] = 0;
                }

                $obj_transaction->updateECReccurringMethod($idEditPaymentMethod, $data, $web_user_id, $type, $convfee['CFEE']);

                $data_updated = [
                    'profile_id' => $data['profile_id'],
                    'name' =>  $data['name'],
                    'type' => 'ec',
                ];
                RevoPayAuditLogger::autopaymentUpdate('user', array('operation' => 'Update Autopayment Payment Method', 'data'=>session('data_profile')), 'M', $idProperty, WebUsers::getAuditData($web_user_id), Auth::user(), Auth::user()->username, Audit::$BEFORE_UPDATE);
                RevoPayAuditLogger::autopaymentUpdate('user', array('operation' => 'Update Autopayment Payment Method', 'data'=>$data_updated), 'M', $idProperty, WebUsers::getAuditData($web_user_id), Auth::user(), Auth::user()->username, Audit::$AFTER_UPDATE);
                RevoPayAuditLogger::paymentMethodCreate('user', array('operation' => 'Create payment method', 'type'=> 'ec', 'account'=> $data['name']), 'M', $idProperty, WebUsers::getAuditData($web_user_id), Auth::user());
                $return_html = $this->autopay(1, $request);
                return response()->json(array(
                    'code' => 1,
                    'message' => 'Autopayment succefully updated',
                    'body' => $return_html->getData(),
                ));
            }

            $ec_account = "XXXX - " . substr($request->input("bankAccountAccount"), -4);

            RevoPayAuditLogger::paymentMethodCreate('user', array('operation' => 'Create payment method', 'type'=> 'eCheck', 'account'=> $ec_account), 'M', $idProperty, WebUsers::getAuditData($web_user_id), Auth::user());


        } else {
            $cardNumber = $request->input("cardNumber");
            $cardName = $request->input("cardName");
            $cardExpDate = $request->input("cardExp");
            $cardZip = $request->input("cardZip");

            $today = new \DateTime();
            $expiryDate = \DateTime::createFromFormat('my', str_replace('/','',$cardExpDate));

            if($today >= $expiryDate) {
                return response()->json([
                    'message' => "The card is expired",
                    'code' => 0
                ]);
            }

            if(empty($cardName) || empty($cardNumber) || empty($cardExpDate) || empty($cardZip)) {
                return response()->json([
                    'message' => "Invalid Input",
                    'code' => 0
                ]);
            }

            $properties = new Properties();
            //BIN settings
            $obj_bin = new Bin();
            $card_data = array();
            $settings = session("settings");
            if (array_key_exists("BINCARD", $settings)) {
                if (!$obj_bin->ValidCCard($cardNumber, $settings["BINCARD"])) {
                    return response()->json([
                        'message' => "Invalid Card",
                        'code' => 0
                    ]);
                }
                $card_data = $obj_bin->getBinCardInfo($cardNumber);
            }
            $card_type_aux = null;
            $cardType = null;
            switch (substr($cardNumber, 0, 1)) {
                case 3:
                    $cardType = 'AmericanExpress';
                    $type = 'amex';
                    $card_type_aux = 'amex';
                    break;
                case 4:
                    $cardType = 'Visa';
                    $card_type_aux = 'v';
                    break;
                case 5:
                    $cardType = 'MasterCard';
                    $card_type_aux = 'mc';
                    break;
                case 6:
                    $cardType = 'Discover';
                    $card_type_aux = 'd';
                    break;
                default :
                    $cardType = 'Unknown';
                    $card_type_aux = 'Unknown';
                    break;
            }
            $credential = $this->getCredentialsForCard($type, $idProperty);
            if(array_key_exists("erroroccured", $credential) && $credential["erroroccured"]) {
                return response()->json(array('code' => $credential['errcode'], 'message' => $credential['message']));
            }
            $card_info['id_property'] = $idProperty;
            $card_info['cardnumber'] = $cardNumber;
            $card_info['cardname'] = $cardName;
            $card_info['cc_type'] = $cardType;
            $card_info['exp_date'] = $cardExpDate;
            $card_info['zip'] = $cardZip;
            $obj_paymentProcessor = new PaymentProcessor();

            $result = $obj_paymentProcessor->getToken($card_info, $credential);

            if (is_array($result)) {
                if($result['response'] != 1) {
                    return response()->json(array('code' => $result['response'], 'message' => $result['responsetext']));
                } else {
                    $result = $result['token'];
                }

            }
            $cc_name = "XXXX- " . substr($cardNumber, -4);
            $card_info['name'] = $cc_name;
            if (isset($card_data->DebitCard) && $card_data->DebitCard == 1) {
                $card_type_aux .='db';
            } else {
                $card_type_aux .= 'c';
            }
            $ccprofile_info = array(
                'card_type_fee' => $card_type_aux,
                'vid' => $result,
                'exp_date' => $cardExpDate,
                'cc_type' => $cardType,
                'ch_name' => $cardName
            );
            $ccjson = json_encode($ccprofile_info);
            $idprofile = $obj_user->insertCCpaymethod($ccjson, $cc_name, $type, $idProperty, $web_user_id);

            if($idEditPaymentMethod){
                $card_info['profile_id']=$idprofile;
                $obj_transaction = new Transations();
                $obj_properties = new Properties();

                $credentials = $obj_properties->getCredentialtype_isrecurring($type, $idProperty, 1);
                $net_amount = $obj_transaction->get1recurringInfo($idEditPaymentMethod, 'trans_recurring_net_amount');

                $convfee = $obj_transaction->getFee($credentials, $net_amount);
                $obj_transaction->updateCCReccurringMethod($idEditPaymentMethod, $card_info, $web_user_id, $type, $convfee['CFEE']);

                $data_updated = [
                    'profile_id' => $card_info['profile_id'],
                    'name' =>  $card_info['name'],
                    'type' => 'cc',
                ];
                RevoPayAuditLogger::autopaymentUpdate('user', array('operation' => 'Update Autopayment Payment Method', 'data'=>session('data_profile')), 'M', $idProperty, WebUsers::getAuditData($web_user_id), Auth::user(), Auth::user()->username, Audit::$BEFORE_UPDATE);
                RevoPayAuditLogger::autopaymentUpdate('user', array('operation' => 'Update Autopayment Payment Method', 'data'=>$data_updated), 'M', $idProperty, WebUsers::getAuditData($web_user_id), Auth::user(), Auth::user()->username, Audit::$AFTER_UPDATE);
                RevoPayAuditLogger::paymentMethodCreate('user', array('operation' => 'Create payment method', 'type'=> 'cc', 'name'=> $card_info['name']), 'M', $idProperty, WebUsers::getAuditData($web_user_id), Auth::user());
                $return_html = $this->autopay(1, $request);
                return response()->json(array(
                    'code' => 1,
                    'message' => 'Autopayment succefully updated',
                    'body' => $return_html->getData(),
                ));
            }
            RevoPayAuditLogger::paymentMethodCreate('user', array('operation' => 'Create payment method', 'type'=> $cardType, 'name'=> $cc_name), 'M', $idProperty, WebUsers::getAuditData($web_user_id), Auth::user());
        }
        $profiles = $obj_user->getPaymentProfiles($web_user_id);
        foreach ($profiles as $profile) {
            $profile->autoPayCount = 0;
            $autoPayCount = $obj_user->getAutoCountByUserProfile($web_user_id, $idProperty, $profile->id);
            $profile->autoPayCount = $autoPayCount;
        }
        $paymentMethodsHTMl = View::make('components.paymentMethods',array('profiles'=>$profiles))->render();

        return response()->json([
            'message' => Lang::get('messages.variableSavedSuccessfully', ['variable'=>Lang::get('messages.paymentMethod')]),
            'body'=> $paymentMethodsHTMl,
            'code' => 1
        ]);
    }

    public function deletePayMethods($id, Request $request) {

        $obj_user = Auth::user();
        $webUserId = $obj_user->web_user_id;
        $idProperty = $obj_user->property_id;
        $profile_db = DB::table('profiles')->where('web_user_id', $webUserId)->where('id', $id)->first();
        if($profile_db)
        {
            $obj_user->deleteProfile($id);
        }
        else{
            return response()->json([
                'message' => "You don't have permissions to delete this payment method",
                'code' => 0
            ]);
        }
        $profiles = $obj_user->getPaymentProfiles($webUserId);
        foreach ($profiles as $profile) {
            $profile->autoPayCount = 0;
            $autoPayCount = $obj_user->getAutoCountByUserProfile($webUserId, $idProperty, $profile->id);
            $profile->autoPayCount = $autoPayCount;
        }
        $paymentMethodsHTMl = View::make('components.paymentMethods',array('profiles'=>$profiles))->render();

        $autopays = DB::table('accounting_recurring_transactions')
            ->where('trans_web_user_id', '=', $webUserId)
            ->where('profile_id', '=', $id)
            ->where('trans_status', 1)
            ->where('property_id', $idProperty)
            ->get();

        foreach ($autopays as $autopay){

            DB::table('accounting_recurring_transactions')
                ->where('trans_id', '=', $autopay->trans_id)
                ->where('property_id', $idProperty)
                ->where('trans_web_user_id', '=', $webUserId)
                ->where('profile_id', '=', $id)
                ->update(['last_updated_by' => "cancelled by web_user",
                    'trans_status' => 4]);

            RevoPayAuditLogger::autopaymentDelete('user', array('operation' => 'Autopayment canceled by profile deleted', 'trans_id' => $autopay->trans_id, 'profile_id' => $id), 'M', $idProperty, WebUsers::getAuditData($webUserId), Auth::user());
        }

        RevoPayAuditLogger::paymentMethodDelete('user', array('operation' => 'Delete payment method', 'profile_id' => $id), 'M', Auth::user()->property_id, WebUsers::getAuditData(Auth::user()->web_user_id), Auth::user());

        return response()->json([
            'message' => Lang::get('messages.variableDeletedSuccessfully', ['variable'=>Lang::get('messages.paymentMethod')]),
            'body'=> $paymentMethodsHTMl,
            'code' => 1
        ]);
    }

    public function saveFrequenceAction(){
        return response()->json([
            'message' => '(Save freq) Data saved successfully',
            'code' => 1
        ]);
    }

    public function transdetail($trans_id,Request $request){
        $obj_trans = new Transations();
        $wu=0;
        if(is_object(Auth::user())){
            $wu=Auth::user()->web_user_id;
        }
        $result = $obj_trans->getTransdetail($trans_id, $wu);
        $msg = '<table class="table table-responsive table-striped table-hover">'
            . '<tbody>'
            . '<tr><td><b>'.Lang::get('messages.transaction').' Id</b></td><td>' . $result->trans_id . '</td></tr>'
            . '<tr><td><b>'.Lang::get('messages.paymentMethod').'</b></td><td>' . $result->trans_card_type . '</td></tr>'
            . '<tr><td><b>'.Lang::get('messages.details').'</b></td><td>' . str_replace("\n", "<br>", $result->trans_descr) . '</td></tr>'
            . '</tbody></table>';


        if($result){
            return response()->json([
                'body' => $msg,
                'code' => 1,
            ]);
        }else{
            return response()->json([
                'body' => View::make('components.dataNotFoundAlertMessage', ['message'=>Lang::get('messages.variableNotFound', ['variable'=>Lang::get('messages.transaction')])])->render(),
                'code' => 1,
            ]);
        }
    }


    function saveAutopayMethodAction($type, Request $request){
        return response()->json([
            'message' => '('.$type.') Data saved successfully',
            'code' => 1,
        ]);
    }
  
  
    // ---------------------------------------------------------------------------------------------------------------
    // QUICKPAY - dsantiago
    // ---------------------------------------------------------------------------------------------------------------

    function quickpaytest()
    {

        return view('quickpay.quickpay');
    }




    public function getCredentialsForCard($type, $idProperty)
    {
        $obj_properties = new Properties();
        $credential = $obj_properties->getCredentialtype_isrecurring($type, $idProperty, 0);
        if (count($credential) < 1) {
            $credential = $obj_properties->getCredentialtype_isrecurring($type, $idProperty, 1);
            if (count($credential) < 1) {
                return array('errcode' => 0, 'message' => 'Credit Card credential do not exist', "erroroccured" => true);
            }
        }
        return json_decode(json_encode($credential[0]),true);
    }

    public function resetpay(){
        session()->forget('customfield');
        session()->forget('categories');
        session()->forget('user_drp');
        session()->forget('user_auto');
        session()->forget('autolimit');
        session()->forget('show_recurring');
        session()->forget('show_onetime');
        session()->forget('show_drp');
        session()->forget('show_fixed');
        session()->forget('htcc_0');
        session()->forget('htcc_1');
        session()->forget('htec_0');
        session()->forget('htec_1');
        session()->forget('htamx_0');
        session()->forget('htamx_1');
        session()->forget('input_data1');
        session()->forget('credentials');
        session()->forget('payor_profiles');
        session()->forget('paymethod');
        session()->forget('response');
        session()->forget('new_auto');
        session()->forget('new_onetime_payment');
        session()->forget('novault');
        session()->save();
        return redirect()->route('autopay');
    }
    
    function autoextend($token, Request $request){
        $atoken= Crypt::decrypt($token);
        //$atoken=$token;
        list($time, $trans_id, $web_user_id, $property_id, $apikey) = explode('|', $atoken);
        
        if($web_user_id>0){
            $ruser=DB::table('web_users')->where('web_user_id',$web_user_id)->first();
            if(!empty($ruser)){
                if($ruser->web_status==0 || $ruser->web_status >= 999){
                    $web_user_id=0;
                }
            }
            else {
                $web_user_id=0;
            }
        }
        if($property_id<=0 || $trans_id<=0 || $web_user_id<=0){
            return view('oneclick.error',['errormsg'=>"Please contact Customer Services, Link is no longer Available. Ref APF45"]);
        }
        $transaction= DB::table('accounting_recurring_transactions')->where('trans_id',$trans_id)->first();
        if(empty($transaction)){
            return view('oneclick.error',['errormsg'=>"Please contact Customer Services, Link is no longer Available. Ref APF46"]);
        }
        if($transaction->trans_status!=3 || $transaction->trans_schedule=='onetime'){
            return view('oneclick.error',['errormsg'=>"Please contact Customer Services, Link is no longer Available. Ref APF47"]);
        }
        $data=array();
        $data['transaction']=$transaction;
        if($transaction->dynamic==0){
            $data['categories']= DB::table('recurring_trans_categories')->where('trans_id',$trans_id)->get();
        }
        $data['user']=$ruser;
        $data['validends']=$this->getCycleList($transaction->trans_schedule,$transaction->trans_last_post_date);
        return view('oneclick.autoextend',$data);
    }
    
    private function getCycleList($schedule,$last_day){
        $mk= new MakePaymentController();
        //first decide the next day
        $nextday=$last_day;
        while(strtotime($nextday)<strtotime('now')){
            $lastday=strtotime($nextday);
            $nextday=$mk->calculateNextDay($schedule, $lastday, 0);
        }
        $first_date=$nextday;
        $futures=array();
        $futures[]='untilcancel';
        $futures[]=$first_date;
        $lastday=$first_date;
        for($i=1;$i<50;$i++){
            $lastday=$mk->calculateNextDay($schedule, strtotime($lastday), 0);
            $futures[]=$lastday;
        }
         return $futures;
    }
    
    function saveautoextend(Request $request){
        $data=$request->all();
        if(!isset($data['transid'])){
            return view('oneclick.error',['errormsg'=>"Invalid Transaction Identifier"]);
        }
        if($data['transid']<=0){
            return view('oneclick.error',['errormsg'=>"Invalid Transaction Identifier"]);
        }
        
        $record= DB::table('accounting_recurring_transactions')->where('trans_id',$data['transid'])->first();
        if(empty($record)){
            return view('oneclick.error',['errormsg'=>"Invalid Transaction Identifier"]);
        }
        if($record->trans_status!=3){
            return view('oneclick.error',['errormsg'=>"Invalid Transaction Status"]);
        }
        $user= DB::table('web_users')->where('web_user_id',$record->trans_web_user_id)->first();
        RevoPayAuditLogger::autopaymentUpdate('user', array('operation' => 'Update Autopayment EndDate', 'data'=>json_encode($record)), 'M', $record->property_id, WebUsers::getAuditData($record->trans_web_user_id), $user, '', Audit::$BEFORE_UPDATE);
        $mk= new MakePaymentController();
        //calculate next date
        $nextday=$record->trans_last_post_date;
        $schedule=$record->trans_schedule;
        while(strtotime($nextday)<strtotime('now')){
            $lastday=strtotime($nextday);
            $nextday=$mk->calculateNextDay($schedule, $lastday, 0);
        }
        //cycles
        if($data['xenddate']!=-1){
            $cycles=$mk->calculateCycle($schedule, strtotime($nextday), strtotime($data['xenddate']));
        }
        else {
            $cycles=9999;
        }
        DB::table('accounting_recurring_transactions')->where('trans_id',$data['transid'])->update(['trans_status'=>1,'trans_next_post_date'=>$nextday,'trans_numleft'=>$cycles]);
        $data_updated = [
            'Payment Date' => date('j', strtotime($nextday)),
            'Frecuency' => $schedule,
            'Start Date' => date('Y|m', strtotime($nextday)),
            'End Date' => $data['xenddate'] == '-1' ? 'Until Canceled' : $data['xenddate'],
        ];
        RevoPayAuditLogger::autopaymentUpdate('user', array('operation' => 'Update Autopayment EndDate', 'data'=>$data_updated), 'M', $record->property_id, WebUsers::getAuditData($record->trans_web_user_id), $user, '', Audit::$AFTER_UPDATE);
        return view('oneclick.error',['errormsg'=>"Success! Autopayment Extended"]);
    }
   
}
