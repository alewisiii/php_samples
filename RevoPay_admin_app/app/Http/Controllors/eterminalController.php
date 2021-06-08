<?php

namespace App\Http\Controllers;

use App\Http\Models\Audit;
use App\Model\EterminalSettings;
use App\Model\User;
use App\Model\Categories;
use \App\CustomClass\Promotions;
use App\Model\MerchantAccount;
use App\Model\Properties;
use App\Model\Transations;
use App\Model\WebUsers;
use App\Providers\RevoPayAuditLogger;
use Illuminate\Http\Request;
use App\Model\Message;
use App\CustomClass\PaymentProcessor;
use App\CustomClass\FraudControl;
use App\Model\Bin;
use App\CustomClass\Email;
use App\Model\Invoices;
use Illuminate\Support\Facades\Auth;
use App\Model\CustomField;

class eterminalController extends Controller {

    public function __construct() {
        $this->middleware(['auth','sadm']);
    }

    function eTerminal($token, Request $request) {
        $atoken = decrypt($token);
        $idproperty = $atoken['level_id'];
        $type = $atoken['level'];
        $idlevel = $idproperty;
        $level = $type;
        if ($type != "M") {
            return redirect(route('accessdenied'));
        }

        $obj_property = new Properties();
        $data = array();
        $data['pageTitle'] = "e-Terminal";

        if ($idproperty <= 0) {
            return redirect(route('accessdenied'));
        }

        $ntoken = encrypt(['level' => $type, 'id' => $idproperty, 'level_id' => $idproperty]);

        $ids = $obj_property->getOnlyIds($idproperty);

        $data['merchant'] = $obj_property->getPropertyInfo($idproperty);

        $data['atoken'] = $ntoken;
        $obj_layout = new \App\Model\Layout();
        $layouts = $obj_layout->getLayoutValues($idlevel, $level);
        $acctitle = $obj_layout->extractLayoutValue('label_acc_number', $layouts);
        $data['acctitle'] = $acctitle;
        $invtitle = $obj_property->getPropertySettings($idproperty, $ids['id_companies'], $ids['id_partners'], 'INVNUMBER');
        if (!empty($invtitle)) {
            $data['invtitle'] = $invtitle;
        } else {
            $data['invtitle'] = 'Invoice #';
        }

        //no new user in eterminal setting
        $noetermnewuser = $obj_property->getPropertySettings($idproperty, $ids['id_companies'], $ids['id_partners'], 'NOETERMNEWUSER');
        $data['noetermnewuser'] = $noetermnewuser;

        //fields not mandatory for new user in eterminal
        $notmandatorynewuser = $obj_property->getPropertySettings($idproperty, $ids['id_companies'], $ids['id_partners'], 'NOTMANDATORYETERM');
        $data['notmandatorynewuser'] = explode('|', $notmandatorynewuser);

        $data['einvsetting'] = $obj_property->getPropertySettings($idproperty, $ids['id_companies'], $ids['id_partners'], 'EINVOICE');

        $data['paymentCategories'] = $obj_property->getPaymentType($idproperty);

        $data['accsetting'] = $obj_property->getPropertySettings($idproperty, $ids['id_companies'], $ids['id_partners'], 'ACCSETTING');

        $data['invsetting'] = $obj_property->getPropertySettings($idproperty, $ids['id_companies'], $ids['id_partners'], 'INVSETTING');
        $data['invlabel'] = $obj_property->getPropertySettings($idproperty, $ids['id_companies'], $ids['id_partners'], 'INVLABEL');
        $data['group'] = $obj_property->getCompanyInfoMinimal($ids['id_companies']);

        //SETTINGS Auto Payments
        $data['existsAutopay'] = $obj_property->getExistsAutopay($idproperty, $ids['id_companies'], $ids['id_partners']);
        $data['freqAutopay'] = $obj_property->getFreqAutpay($idproperty, $ids['id_companies'], $ids['id_partners']);
        $data['limitAutopay'] = $obj_property->getLimitFixAutopay($idproperty, $ids['id_companies'], $ids['id_partners']);
        $data['daysAutopay'] = $obj_property->getDaysAutopay($idproperty, $ids['id_companies'], $ids['id_partners']);

        //SETTINGS DRP
        $data['existsDrp'] = $obj_property->getExistsDrp($idproperty, $ids['id_companies'], $ids['id_partners']);
        $data['textDrp'] = $obj_property->getTextDrp($idproperty, $ids['id_companies'], $ids['id_partners']);
        $data['freqDrp'] = $obj_property->getFreqDrp($idproperty, $ids['id_companies'], $ids['id_partners']);
        $data['daysDrp'] = $obj_property->getDaysDrp($idproperty, $ids['id_companies'], $ids['id_partners']);


        //adding 5 years in advance to end day on the autopayments
        $nocancel = $obj_property->getNoCancelAuto($idproperty, $ids['id_companies'], $ids['id_partners']);
        if ($nocancel == 1) {
            $data['enddate'] = $obj_property->get5yearInAdvance(false);
        } else {
            $data['enddate'] = $obj_property->get5yearInAdvance();
        }

        //get onetime credential
        $data['credOneTime'] = $obj_property->getcredOneTimeCredentials($idproperty, "eterm-");

        //get recurring hight ticket and lower ticket
        $data['velocityOt'] = $obj_property->getHight_LowerTicket($data['credOneTime']);
        //get recurring credential
        $data['credRecurring'] = $obj_property->getcredRecurringCredentials($idproperty, "eterm-");
        //get recurring hight ticket and lower ticket
        $data['velocityRc'] = $obj_property->getHight_LowerTicket($data['credRecurring']);

        $novault = $obj_property->isInactiveVault($idproperty, 0);

        $data['novault'] = $novault;

        $data['invsetting'] = $obj_property->getPropertySettings($idproperty, $ids['id_companies'], $ids['id_partners'], 'INVSETTING');

        //get payments Categories
        $paymentCategories = $obj_property->getPaymentType($idproperty);
        $data['paymentCategories'] = $paymentCategories;

        $data['fieldscontent'] = view('eterminal.fieldseterm', $data);
        $data['pageTitle'] = 'e-Terminal';
        return view('eterminal.eterm_new', ['data' => $data, 'token' => $data['atoken']]);
    }

    function etermNewuser(Request $request) {

        $data = array();
        $atoken = $request->input('xtoken');

        $accountNumber = request()->xname_account.'';
        if(ctype_digit($accountNumber)){
            if ($accountNumber == 0) {
                return redirect()->back()->with('error', "Account number can't be zero. Please verify and try again.")->withInput($request->all());
            }
        }

        $datatoken = decrypt($atoken);

//        $array_token = json_decode($datatoken, 1);
        $idproperty = $datatoken['level_id'];
        $type = $datatoken['level'];

        if (empty($type) || $type != "M" || empty($idproperty) || $idproperty <= 0) {
            return redirect(route('accessdenied'));
        }

//        $idadmin = $array_token['iduser'];
        $ntoken = encrypt(['level' => $type, 'id' => $idproperty, 'level_id' => $idproperty]);
        $newtoken = encrypt($datatoken);

        //security
        //$objAdminAuth = new AuthAdminController();
        //$objAdminAuth->checkAuthPermissions($array_token['iduser']);

        $data['atoken'] = $atoken;
//        $dtoken = $this->validateDecrypt($atoken);
//        list($idproperty, $time, $apikey) = explode("|", $dtoken);

        if ($idproperty <= 0) {
            $data['msg'] = "Sorry, this link is no longer available. Please contact Customer Service for assistance. Ref EAPNPJF43";
        }


        $obj_message = new Message();
        $obj_user = new User();
        $obj_property = new Properties();
        $obj_inv = new \App\Model\Invoices();

        $newUsr = array();
        $newUsr['property_id'] = $idproperty;
        $newUsr['web_status'] = 998;
        $newUsr['account_number'] = !empty($request->input('xname_account')) ? $request->input('xname_account') : "";
        $newUsr['last_updated_by'] = !empty(Auth::user()->username) ? Auth::user()->username : "Created by eTerminal";
        $newUsr['first_name'] = !empty($request->input('xname_firstname')) ? $request->input('xname_firstname') : "";
        $newUsr['last_name'] = !empty($request->input('xname_lastname')) ? $request->input('xname_lastname') : "";
        $newUsr['address'] = !empty($request->input('xname_address')) ? $request->input('xname_address') : "";
        $newUsr['address_unit'] = !empty($request->input('xname_address_unit')) ? $request->input('xname_address_unit') : "";
        $newUsr['city'] = !empty($request->input('xname_city')) ? $request->input('xname_city') : "";
        $newUsr['state'] = !empty($request->input('xname_state')) ? $request->input('xname_state') : "";
        $newUsr['zip'] = !empty($request->input('xname_zip')) ? $request->input('xname_zip') : "";
        $newUsr['phone_number'] = !empty($request->input('xname_phone')) ? $request->input('xname_phone') : "";
        $newUsr['email_address'] = !empty($request->input('xname_email')) ? $request->input('xname_email') : "";

        if (isset($newUsr['account_number']) && !empty($newUsr['account_number'])) {
            if ($obj_user->existAccNum($newUsr['account_number'], $idproperty)) {
                $data['msg'] = $obj_message->getMessageByKey('existacc');
            }
        }
        if ($newUsr['account_number'] == "" && $newUsr['first_name'] == "") {
            $data['msg'] = $obj_message->getMessageByKey('emptyfirstnameacc');
        }

        $web_user_id = $obj_user->CreateUser($newUsr);
        RevoPayAuditLogger::userProfileCreate('admin', array('operation' => 'Create User Profile', 'data' => $newUsr), $type, $idproperty, WebUsers::getAuditData($web_user_id), Auth::user(), Auth::user()->username, Audit::$DATA_INSERT);
        $newUsr['web_user_id'] = $web_user_id;

        $data['usr'] = $newUsr;

        $data["pageTitle"] = "e-Terminal";
        $ids = $obj_property->getOnlyIds($idproperty);
        $accsetting = $obj_property->getPropertySettings($idproperty, $ids['id_companies'], $ids['id_partners'], 'ACCSETTING');
        $invsetting = $obj_property->getPropertySettings($idproperty, $ids['id_companies'], $ids['id_partners'], 'INVSETTING');
        $data['einvsetting'] = $obj_property->getPropertySettings($idproperty, $ids['id_companies'], $ids['id_partners'], 'EINVOICE');
        $usrnempty = false;
        $data['accsetting'] = $accsetting;
        $data['invsetting'] = $invsetting;

        $companyname = $obj_property->getPropertySettings($idproperty, $ids['id_companies'], $ids['id_partners'], 'SHOWCOMPANYNAME');
        $data['showcompanyname'] = $companyname;

        //fields not mandatory for new user in eterminal
        $notmandatorynewuser = $obj_property->getPropertySettings($idproperty, $ids['id_companies'], $ids['id_partners'], 'NOTMANDATORYETERM');
        $data['notmandatorynewuser'] = explode('|', $notmandatorynewuser);

        //get customize account # and invoice #
        $acctitle = $obj_property->getPropertySettings($idproperty, $ids['id_companies'], $ids['id_partners'], 'PAYMENT_NUMBER_REG_NUMBER');
        if (!empty($acctitle)) {
            $data['acctitle'] = $acctitle;
        } else {
            $data['acctitle'] = 'Account #';
        }
        $invtitle = $obj_property->getPropertySettings($idproperty, $ids['id_companies'], $ids['id_partners'], 'INVNUMBER');
        if (!empty($invtitle)) {
            $data['invtitle'] = $invtitle;
        } else {
            $data['invtitle'] = 'Invoice #';
        }

        $data['invlabel'] = $obj_property->getPropertySettings($idproperty, $ids['id_companies'], $ids['id_partners'], 'INVLABEL');
        $data['group'] = $obj_property->getCompanyInfoMinimal($ids['id_companies']);

        //SETTINGS Auto Payments
        $data['existsAutopay'] = $obj_property->getExistsAutopay($idproperty, $ids['id_companies'], $ids['id_partners']);
        $data['freqAutopay'] = $obj_property->getFreqAutpay($idproperty, $ids['id_companies'], $ids['id_partners'], true);
        $data['limitAutopay'] = $obj_property->getLimitFixAutopay($idproperty, $ids['id_companies'], $ids['id_partners'], true);
        $data['daysAutopay'] = $obj_property->getDaysAutopay($idproperty, $ids['id_companies'], $ids['id_partners'], true);

        //SETTINGS DRP
        $data['existsDrp'] = $obj_property->getExistsDrp($idproperty, $ids['id_companies'], $ids['id_partners']);
        $data['textDrp'] = $obj_property->getTextDrp($idproperty, $ids['id_companies'], $ids['id_partners']);
        $data['freqDrp'] = $obj_property->getFreqDrp($idproperty, $ids['id_companies'], $ids['id_partners'], true);
        $data['daysDrp'] = $obj_property->getDaysDrp($idproperty, $ids['id_companies'], $ids['id_partners'], true);

        //adding 5 years in advance to end day on the autopayments
        $nocancel = $obj_property->getNoCancelAuto($idproperty, $ids['id_companies'], $ids['id_partners']);
        if ($nocancel == 1) {
            $data['enddate'] = $obj_property->get5yearInAdvance(false);
        } else {
            $data['enddate'] = $obj_property->get5yearInAdvance();
        }

        //get onetime credential
        $data['credOneTime'] = $obj_property->getcredOneTimeCredentials($idproperty, "eterm-");
        //get recurring hight ticket and lower ticket
        $data['velocityOt'] = $obj_property->getHight_LowerTicket($data['credOneTime']);
        //get recurring credential
        $data['credRecurring'] = $obj_property->getcredRecurringCredentials($idproperty, "eterm-");
        //get recurring hight ticket and lower ticket
        $data['velocityRc'] = $obj_property->getHight_LowerTicket($data['credRecurring']);

        //memo setting
        $nomemo = $obj_property->getPropertySettings($idproperty, $ids['id_companies'], $ids['id_partners'], 'NOMEMO');
        $data['nomemo'] = $nomemo;

        //SETTINGS CUSTOM CSS
        $data['custom_css_file'] = $obj_property->getPropertySettings($idproperty, $ids['id_companies'], $ids['id_partners'], 'CUSTOM_STYLESHEET');

        $walkin_payments = $obj_property->getPropertySettings($idproperty, $ids['id_companies'], $ids['id_partners'], 'ETERMWALKIN');
        $data['walkin_payments'] = $walkin_payments;

        $novault = $obj_property->isInactiveVault($idproperty, 0);
        $data['novault'] = $novault;

        //get layout

        $data['invsetting'] = $invsetting;

        //get payments Categories
        $paymentCategories = $obj_property->getPaymentType($idproperty);
        $data['paymentCategories'] = $paymentCategories;
        $data['merchant'] = $obj_property->getPropertyInfo($idproperty);
        $profiles = array();
        $data['xurl'] = "https://" . $_SERVER['SERVER_NAME'];
        $usrlist = $newUsr;
        $data['usr'] = $usrlist;
        $str_custom = $obj_user->customInfo($usrlist, $companyname);
        $data['str_custom'] = $str_custom;
        $str_simple = $obj_user->customSimpleInfo($usrlist, $companyname);
        $data['str_simple'] = $str_simple;
        $content = \Illuminate\Support\Facades\View::make('eterm_component.etermusermin', $data)->__toString();
        $data['content'] = $content;
        $data['utoken'] = \Illuminate\Support\Facades\Crypt::encrypt($idproperty . '|' . $usrlist['web_user_id'] . '|' . time() . '|' . config('app.appAPIkey'));
        $data['account_number'] = $usrlist['account_number'];
        $data['first_name'] = $usrlist['first_name'];
        $data['last_name'] = $usrlist['last_name'];
        $data['address'] = $usrlist['address'];
        $obj_trans = new Transations();
        $data['trans'] = $obj_trans->getTransByUsrId_5($usrlist['web_user_id']);

        $profiles['profiles'] = $obj_user->getPaymentProfiles_Credentials($usrlist['web_user_id'], $idproperty, 0);
        $profiles['isrecurring'] = 0;
        $cont_profiles = \Illuminate\Support\Facades\View::make('eterm_component.eterm_profile1', $profiles)->__toString();
        $data['profiles'] = $cont_profiles;
        $profiles['profiles'] = "";
        $profiles['profiles'] = $obj_user->getPaymentProfiles_Credentials($usrlist['web_user_id'], $idproperty, 1);
        $profiles['isrecurring'] = 1;
        $cont_profiles = \Illuminate\Support\Facades\View::make('eterm_component.eterm_profile1', $profiles)->__toString();
        $data['profiles1'] = $cont_profiles;


        if (isset($data['msg'])) {
            return view('eterminal.eterm_new', ['data' => $data, 'token' => $ntoken]);
        }
        $data['type'] = 'usr';
        return view('eterminal.eterm_pay', ['data' => $data, 'token' => $ntoken]);
    }

    function etermFindusr(Request $request) {

        $type = $request->input('xtype');
        $atoken = $request->input('xtoken');
        $dataarray = decrypt($atoken);
        $idproperty = $dataarray['level_id'];
        $level = $dataarray['level'];
        $idadmin = Auth::id();
        if (empty($level) || $level != "M" || empty($idproperty) || $idproperty <= 0) {
            return redirect(route('accessdenied'));
        }
        
        $ntoken = encrypt(['level' => $level, 'level_id' => $idproperty, 'id' => $idproperty]);
        $newtoken = encrypt($idproperty . '|' . $level . '|' . time() . '|' . config('app.appAPIkey'));

        $data = array();
        $data['atoken'] = $newtoken;
        $data["pageTitle"] = "e-Terminal";
        if ($type == "inv") {
            $inv_number = $request->input('xname_invnumber');
        } else {
            $account_number = trim($request->input('xname_account'));
//error_log($account_number,3, '/var/tmp/etermfind.log');
            $first_name = trim($request->input('xname_firstname'));
//error_log($first_name,3, '/var/tmp/etermfind.log');

            $last_name = trim($request->input('xname_lastname'));
            $address = trim($request->input('xname_address'));
        }


        if ($idproperty <= 0) {
            $data['msg'] = "Sorry, this link is no longer available. Please contact Customer Service for assistance. Ref EAPNPJF43";
        }
        //To Display Custom Fields in Payment Page --By Abhishek
        $obj_customfield = new CustomField();
        $customfield = $obj_customfield->getCustomfieldDetails($idproperty);
        $obj_user = new User();
        $obj_inv = new \App\Model\Invoices();
        $obj_message = new Message();
        $obj_property = new Properties();

        $ids = $obj_property->getOnlyIds($idproperty);
        $accsetting = $obj_property->getPropertySettings($idproperty, $ids['id_companies'], $ids['id_partners'], 'ACCSETTING');
        $invsetting = $obj_property->getPropertySettings($idproperty, $ids['id_companies'], $ids['id_partners'], 'INVSETTING');
        $usrnempty = false;
        $data['accsetting'] = $accsetting;
        $data['invsetting'] = $invsetting;

        $obj_layout = new \App\Model\Layout();
        $layouts = $obj_layout->getLayoutValues($idproperty, $level);
        $acctitle = $obj_layout->extractLayoutValue('label_acc_number', $layouts);
        $data['acctitle'] = $acctitle;
        $invtitle = $obj_property->getPropertySettings($idproperty, $ids['id_companies'], $ids['id_partners'], 'INVNUMBER');
        if (!empty($invtitle)) {
            $data['invtitle'] = $invtitle;
        } else {
            $data['invtitle'] = 'Invoice #';
        }

        $data['einvsetting'] = $obj_property->getPropertySettings($idproperty, $ids['id_companies'], $ids['id_partners'], 'EINVOICE');

        //fields not mandatory for new user in eterminal
        $notmandatorynewuser = $obj_property->getPropertySettings($idproperty, $ids['id_companies'], $ids['id_partners'], 'NOTMANDATORYETERM');
        $data['notmandatorynewuser'] = explode('|', $notmandatorynewuser);

        $data['invlabel'] = $obj_property->getPropertySettings($idproperty, $ids['id_companies'], $ids['id_partners'], 'INVLABEL');
        $data['group'] = $obj_property->getCompanyInfoMinimal($ids['id_companies']);

        //SETTINGS Auto Payments
        $data['existsAutopay'] = $obj_property->getExistsAutopay($idproperty, $ids['id_companies'], $ids['id_partners']);
        $data['freqAutopay'] = $obj_property->getFreqAutpay($idproperty, $ids['id_companies'], $ids['id_partners'], true);
        $data['limitAutopay'] = $obj_property->getLimitFixAutopay($idproperty, $ids['id_companies'], $ids['id_partners'], true);
        $data['daysAutopay'] = $obj_property->getDaysAutopay($idproperty, $ids['id_companies'], $ids['id_partners'], true);

        //SETTINGS DRP
        $data['existsDrp'] = $obj_property->getExistsDrp($idproperty, $ids['id_companies'], $ids['id_partners']);
        $data['textDrp'] = $obj_property->getTextDrp($idproperty, $ids['id_companies'], $ids['id_partners']);
        $data['freqDrp'] = $obj_property->getFreqDrp($idproperty, $ids['id_companies'], $ids['id_partners'], true);
        $data['daysDrp'] = $obj_property->getDaysDrp($idproperty, $ids['id_companies'], $ids['id_partners'], true);

        //adding 5 years in advance to end day on the autopayments
        $nocancel = $obj_property->getNoCancelAuto($idproperty, $ids['id_companies'], $ids['id_partners'], true);
        if ($nocancel == 1) {
            $data['enddate'] = $obj_property->get5yearInAdvance(false);
        } else {
            $data['enddate'] = $obj_property->get5yearInAdvance();
        }

        $companyname = $obj_property->getPropertySettings($idproperty, $ids['id_companies'], $ids['id_partners'], 'SHOWCOMPANYNAME');
        $data['showcompanyname'] = $companyname;

        //memo setting
        $nomemo = $obj_property->getPropertySettings($idproperty, $ids['id_companies'], $ids['id_partners'], 'NOMEMO');
        $data['nomemo'] = $nomemo;

        //SETTINGS CUSTOM CSS
        $data['custom_css_file'] = $obj_property->getPropertySettings($idproperty, $ids['id_companies'], $ids['id_partners'], 'CUSTOM_STYLESHEET');

        $noapplycfee = $obj_property->getPropertySettings($idproperty, $ids['id_companies'], $ids['id_partners'], 'NOAPPLYCFEE');
        $data['noapplycfee'] = $noapplycfee;

        $setting_balance = $obj_property->getPropertySettings($idproperty, $ids['id_companies'], $ids['id_partners'], 'SHOW_BALANCE');
        $data['setting_balance'] = $setting_balance;

        $noetermnewuser = $obj_property->getPropertySettings($idproperty, $ids['id_companies'], $ids['id_partners'], 'NOETERMNEWUSER');
        $data['noetermnewuser'] = $noetermnewuser;

        //Setting to show diferent services to pay in eterminal with different fee
        $eservices = $obj_property->getPropertySettings($idproperty, $ids['id_companies'], $ids['id_partners'], 'ETERMSERV');
        $data['eservices'] = $eservices;

        //get the services to show (type and description)
        if (isset($eservices) && $eservices == 1) {
            $eterm_services = $obj_property->getServicesTypeByProperty($idproperty);
            $data['eterm_services'] = $eterm_services;
        }

        //get onetime credential with services fee
        $data['credOneTime'] = $obj_property->getcredOneTimeCredentials($idproperty, "eterm-");

        //get recurring hight ticket and lower ticoodket
        $data['velocityOt'] = $obj_property->getHight_LowerTicket($data['credOneTime']);
        //get recurring credential
        $data['credRecurring'] = $obj_property->getcredRecurringCredentials($idproperty, "eterm-");
        //get recurring hight ticket and lower ticket
        $data['velocityRc'] = $obj_property->getHight_LowerTicket($data['credRecurring']);

        $novault = $obj_property->isInactiveVault($idproperty, 0);
        $data['novault'] = $novault;

        $data['invsetting'] = $invsetting;

        $data['merchant'] = $obj_property->getPropertyInfo($idproperty);
        $profiles = array();
        $data['xurl'] = "https://" . $_SERVER['SERVER_NAME'];
        switch ($type) {
            case 'inv':
                $paymentCategories = $obj_property->getPaymentType($idproperty);
                $data['paymentCategories'] = $paymentCategories;
                if (empty($inv_number)) {
                    $data['msg'] = $obj_message->getMessageByKey('etermninv');
                    $atoken = \Illuminate\Support\Facades\Crypt::encrypt($idproperty . "|" . time() . "|" . config("app.appAPIkey"));
                    $data['atoken'] = $atoken;
                    return view('eterminal.eterm_new', ['data' => $data, 'token' => $ntoken]);
                }
                $usrlist = $obj_inv->getUserByInv_num($inv_number, $idproperty);
                if (empty($usrlist)) {
                    $data['msg'] = $obj_message->getMessageByKey('noinv');
                    $atoken = \Illuminate\Support\Facades\Crypt::encrypt($idproperty . "|" . time() . "|" . config("app.appAPIkey"));
                    $data['atoken'] = $atoken;
                    return view('eterminal.eterm_new', ['data' => $data, 'token' => $ntoken]);
                }
                $datainv['invtitle'] = $data['invtitle'];
                $datainv['acctitle'] = $data['acctitle'];
                $datainv['merchant'] = $data['merchant'];
                $datainv['usr'] = $usrlist;
                $data['usr'] = $usrlist;
                $datainv['invoice'] = $obj_inv->getInvoiceByInvoice_number($inv_number, $idproperty, $usrlist->web_user_id);
                $active_inv = array('open', 'sent', 'paid');
                if(!in_array($datainv['invoice']['status'], $active_inv)) {
                    if ($datainv['invoice']['status'] == 'draft') {
                        $data['msg'] = $obj_message->getMessageByKey('inv_draft_status');
                    } else {
                        $data['msg'] = $obj_message->getMessageByKey('inv_status') . $datainv['invoice']['status'];
                    }
                }

                if (!isset($data['msg']) || empty($data['msg'])) {
                    $discount = $obj_inv->getInvoiceDiscount($datainv['invoice']['id'], $idproperty, $datainv['invoice']['amount'], $datainv['invoice']['invoice_date']);
                    $datainv['discount'] = $discount;

                    $infodicount = $obj_inv->getDiscountByInvoice($datainv['invoice']['id'], $idproperty, $datainv['invoice']['amount'], $datainv['invoice']['invoice_date']);
                    $datainv['infodiscount'] = $infodicount;

                    if ($datainv['invoice']['paid'] > 0) {
                        $datainv['invoice']['topay'] = $datainv['invoice']['amount'] - $datainv['invoice']['paid'];
                        $datainv['infodiscount']['discount'] = 0;
                    } else {
                        $datainv['invoice']['topay'] = $datainv['invoice']['amount'];
                    }

                    if ($datainv['infodiscount']['discount'] > 0) {
                        $datainv['invoice']['topay'] = $datainv['infodiscount']['discountamount'];
                    }

                    //invoice info
                    $items = $obj_inv->getInvoiceItems($datainv['invoice']['id'], $idproperty);
                    $datainv['items'] = $items;
                    $datainv['showcompanyname'] = $companyname;
                    $str_simple = $obj_user->customSimpleInfo($usrlist, $companyname);
                    $data['str_simple'] = $str_simple;
                    $str_custom = $obj_user->customInfo($usrlist, $companyname);
                    $pay_summary = \Illuminate\Support\Facades\View::make('invoice.inv_paymentSummary', $datainv)->__toString();
                    $data['paysummary'] = $pay_summary;
                    $content = \Illuminate\Support\Facades\View::make('invoice.eterm_invtopay', $datainv)->__toString();
                    $data['content'] = $content;
                    $data['invcontent'] = $content;
                    $data['utoken'] = \Illuminate\Support\Facades\Crypt::encrypt($idproperty . '|' . $usrlist->web_user_id . '|' . time() . '|' . config('app.appAPIkey'));
                    $data['str_custom'] = $str_custom;
                    $data['invoice'] = $datainv['invoice'];

                    $profiles['profiles'] = $obj_user->getPaymentProfiles_Credentials($usrlist->web_user_id, $idproperty, 0);
                    $profiles['isrecurring'] = 0;
                    $cont_profiles = \Illuminate\Support\Facades\View::make('eterm_component.eterm_profile1', $profiles)->__toString();
                    $data['profiles'] = $cont_profiles;
                }

                break;
            case 'usr':
                if (empty($account_number) && empty($address) &&
                        empty($first_name) && empty($last_name)) {
                    $data['msg'] = $obj_message->getMessageByKey('eterm1');
                }
                $usrlist = $obj_user->findByAccEOptionList($account_number, $address, $first_name, $last_name, $idproperty);
                if (count($usrlist) > 0) {
                    if (count($usrlist) == 1) { // only one user found
                        if ($usrlist[0]->web_status == 1 || $usrlist[0]->web_status == 998 || $usrlist[0]->web_status == 46) {
                            $web_user_id = $usrlist[0]->web_user_id;
                            $data['usr'] = $usrlist[0];
                            $str_custom = $obj_user->customInfo($usrlist[0], $companyname);
                            $str_simple = $obj_user->customSimpleInfo($usrlist[0], $companyname);
                            $data['str_simple'] = $str_simple;
                            $data['str_custom'] = $str_custom;
                            $data['utoken'] = \Illuminate\Support\Facades\Crypt::encrypt($idproperty . '|' . $usrlist[0]->web_user_id . '|' . time() . '|' . config('app.appAPIkey'));
                            $data['account_number'] = $account_number;
                            $data['first_name'] = $first_name;
                            $data['last_name'] = $last_name;
                            $data['address'] = $address;
                            $obj_trans = new Transations();
                            $data['trans'] = $obj_trans->getTransByUsrId_5($usrlist[0]->web_user_id);
                            $data['activeDRP'] = $obj_user->getDRPByUser($usrlist[0]->web_user_id, $idproperty);
                            $data['activeAuto'] = $obj_user->getAutoCountNoDRPByUser($usrlist[0]->web_user_id, $idproperty);

                            $profiles['profiles'] = $obj_user->getPaymentProfiles_Credentials($usrlist[0]->web_user_id, $idproperty, 0);
                            $profiles['isrecurring'] = 0;
                            $cont_profiles = \Illuminate\Support\Facades\View::make('eterm_component.eterm_profile1', $profiles)->__toString();
                            $data['profiles'] = $cont_profiles;

                            $profiles['profiles'] = "";
                            $profiles['profiles'] = $obj_user->getPaymentProfiles_Credentials($usrlist[0]->web_user_id, $idproperty, 1);
                            $profiles['isrecurring'] = 1;
                            $cont_profiles = \Illuminate\Support\Facades\View::make('eterm_component.eterm_profile1', $profiles)->__toString();
                            $data['profiles1'] = $cont_profiles;
                        } else {
                            switch ($usrlist[0]->web_status) {
                                case '0':
                                    if ($data['merchant']['name_clients']) {
                                        $chain = str_replace('[:DBA_NAME:]', $data['merchant']['name_clients'], $obj_message->getMessageByKey('eterm0'));
                                        $data['msg'] = $chain;
                                    } else {
                                        $data['msg'] = $obj_message->getMessageByKey('eterm0');
                                    }
                                    break;
                                case '999':
                                    if ($data['merchant']['name_clients']) {
                                        $chain = str_replace('[:DBA_NAME:]', $data['merchant']['name_clients'], $obj_message->getMessageByKey('eterm999'));
                                        $data['msg'] = $chain;
                                    } else {
                                        $data['msg'] = $obj_message->getMessageByKey('eterm999');
                                    }
                                    break;
                                default:
                                    $data['msg'] = $obj_message->getMessageByKey('eterm2');
                                    break;
                            }
                        }
                    } else {
                        $token = \Illuminate\Support\Facades\Crypt::encrypt($idproperty . "|" . time() . "|" . config("app.appAPIkey") . "|" . $first_name . "|" . $last_name . "|" . $account_number . "|" . $address . "||0");
                        return redirect()->route('etermUsrList', ['token1' => $token, 'token' => $ntoken]);
                    }
                } else {
                    $data['msg'] = $obj_message->getMessageByKey('eterm2');
                }
                break;

            default:
                //error
                break;
        }
        if (!isset($paymentCategories))
            $paymentCategories = array();
        //get payments Categories by user
        if (isset($web_user_id)) {
            $paymentCategories = $obj_property->getPaymentWebUserCategories($web_user_id);
        }
        if (count($paymentCategories) == 0) {
            //get payments Categories by properties
            $paymentCategories = $obj_property->getPaymentType($idproperty);
        }
        $data['paymentCategories'] = $paymentCategories;

        //add var to show the convenience fee (Trista)

        if ($obj_property->isInactiveVault($idproperty, 0)) {
            unset($data['credRecurring']['cc']);
            $data['noVault'] = 1;
        }
        $array_credOneTime = array();
        if (isset($data['credOneTime']['cc'])) {
            foreach ($data['credOneTime']['cc'] as $item) {
                $cards = array();
                $item = (array) $item;
                if (isset($item['card_type'])) {
                    foreach ($item['card_type'] as $card) {
                        $cards[] = [
                            'type' => $card->type,
                            'convenience_fee' => $card->convenience_fee,
                            'convenience_fee_float' => $card->convenience_fee_float,
                        ];
                    }
                }

                $array_credOneTime[] = [
                    'low_pay_range' => $item['low_pay_range'],
                    'high_pay_range' => $item['high_pay_range'],
                    'convenience_fee' => $item['convenience_fee'],
                    'convenience_fee_float' => $item['convenience_fee_float'],
                    'cards' => $cards
                ];
            }
        }

        $array_credRecurring = array();
        if (isset($data['credRecurring']['cc'])) {
            foreach ($data['credRecurring']['cc'] as $item) {
                $cards = array();
                $item = (array) $item;
                if (isset($item['card_type'])) {
                    foreach ($item['card_type'] as $card) {
                        $cards[] = [
                            'type' => $card->type,
                            'convenience_fee' => $card->convenience_fee,
                            'convenience_fee_float' => $card->convenience_fee_float,
                            'convenience_fee_drp' => $card->convenience_fee_drp,
                            'convenience_fee_float_drp' => $card->convenience_fee_float_drp
                        ];
                    }
                }

                $array_credRecurring[] = [
                    'low_pay_range' => $item['low_pay_range'],
                    'high_pay_range' => $item['high_pay_range'],
                    'convenience_fee' => $item['convenience_fee'],
                    'convenience_fee_float' => $item['convenience_fee_float'],
                    'convenience_fee_drp' => $item['convenience_fee_drp'],
                    'convenience_fee_float_drp' => $item['convenience_fee_float_drp'],
                    'cards' => $cards
                ];
            }
        }


        $array_credOneTimeAmex = array();
        if (isset($data['credOneTime']['amex'])) {
            foreach ($data['credOneTime']['amex'] as $item) {
                $item = (array) $item;
                $array_credOneTimeAmex[] = [
                    'low_pay_range' => $item['low_pay_range'],
                    'high_pay_range' => $item['high_pay_range'],
                    'convenience_fee' => $item['convenience_fee'],
                    'convenience_fee_float' => $item['convenience_fee_float'],
                ];
            }
        }

        $array_credOneTimeSwipe = array();
        if (isset($data['credOneTime']['swipe'])) {
            foreach ($data['credOneTime']['swipe'] as $item) {
                $cards = array();
                $item = (array) $item;
                if (isset($item['card_type']))
                    foreach ($item['card_type'] as $card) {
                        $cards[] = [
                            'type' => $card->type,
                            'convenience_fee' => $card->convenience_fee,
                            'convenience_fee_float' => $card->convenience_fee_float,
                        ];
                    }
                $array_credOneTimeSwipe[] = [
                    'low_pay_range' => $item['low_pay_range'],
                    'high_pay_range' => $item['high_pay_range'],
                    'convenience_fee' => $item['convenience_fee'],
                    'convenience_fee_float' => $item['convenience_fee_float'],
                    'cards' => $cards
                ];
            }
        }

        $array_credRecurringAmex = array();
        if (isset($data['credRecurring']['amex'])) {
            foreach ($data['credRecurring']['amex'] as $item) {
                $item = (array) $item;
                $array_credRecurringAmex[] = [
                    'low_pay_range' => $item['low_pay_range'],
                    'high_pay_range' => $item['high_pay_range'],
                    'convenience_fee' => $item['convenience_fee'],
                    'convenience_fee_float' => $item['convenience_fee_float'],
                    'convenience_fee_drp' => $item['convenience_fee_drp'],
                    'convenience_fee_float_drp' => $item['convenience_fee_float_drp'],
                ];
            }
        }


        $data['array_credOneTime'] = json_encode(array());
        if (isset($array_credOneTime)) {
            $data['array_credOneTime'] = json_encode($array_credOneTime);
        }

        $data['array_credRecurring'] = json_encode(array());
        if (isset($array_credRecurring)) {
            $data['array_credRecurring'] = json_encode($array_credRecurring);
        }

        $data['array_ecOneTime'] = json_encode(array());
        if (isset($data['credOneTime']['ec'])) {
            $data['array_ecOneTime'] = json_encode($data['credOneTime']['ec']);
        }

        $data['array_ecRecurring'] = json_encode(array());
        if (isset($data['credRecurring']['ec'])) {
            $data['array_ecRecurring'] = json_encode($data['credRecurring']['ec']);
        }

        $data['array_credOneTimeAmex'] = json_encode(array());
        if (isset($array_credOneTimeAmex)) {
            $data['array_credOneTimeAmex'] = json_encode($array_credOneTimeAmex);
        }

        $data['array_credRecurringAmex'] = json_encode(array());
        if (isset($array_credRecurringAmex)) {
            $data['array_credRecurringAmex'] = json_encode($array_credRecurringAmex);
        }

        $data['array_credOneTimeSwipe'] = json_encode(array());
        if (isset($array_credOneTimeSwipe))
            $data['array_credOneTimeSwipe'] = json_encode($array_credOneTimeSwipe);

        //setting eterminal Walk-In and Phone Fee
        $obj_eterminal = new EterminalSettings();
        $eterminal_settings = $obj_eterminal->getEterminalSettingsByMerchant($idproperty);
        $eterminal_permissions = $obj_eterminal->getEterminalPermissions($idadmin);
        if(count($eterminal_permissions) > 0){
            if(count($eterminal_settings) > 0){
                foreach ($eterminal_permissions as $permission){
                    if($permission->permissions_name == 'eTerminal Walk In'){
                        foreach ($eterminal_settings as $setting){
                            if($setting->walk_in == 1){
                                if($setting->is_recurring == 0){
                                    $array_walkin_OneTime[] = $setting->payment_method;
                                }else{
                                    $array_walkin_Recurring[] = $setting->payment_method;
                                }
                            }
                        }
                    }
                    if($permission->permissions_name == 'eTerminal Phone Fee'){
                        foreach ($eterminal_settings as $setting){
                            if($setting->phone_fee == 1){
                                if($setting->is_recurring == 0){
                                    if($setting->payment_method == 'ec'){
                                        $array_phonefee_OneTime['ec']=number_format($setting->phone_fee_value,2);
                                    }
                                    if($setting->payment_method == 'cc'){
                                        $array_phonefee_OneTime['cc']=number_format($setting->phone_fee_value,2);
                                    }
                                    if($setting->payment_method == 'amex'){
                                        $array_phonefee_OneTime['amex']=number_format($setting->phone_fee_value,2);
                                    }
                                }
                                if($setting->is_recurring == 1){
                                    if($setting->payment_method == 'ec'){
                                        $array_phonefee_Recurring['ec']=number_format($setting->phone_fee_value,2);
                                    }
                                    if($setting->payment_method == 'cc'){
                                        $array_phonefee_Recurring['cc']=number_format($setting->phone_fee_value,2);
                                    }
                                    if($setting->payment_method == 'amex'){
                                        $array_phonefee_Recurring['amex']=number_format($setting->phone_fee_value,2);
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }

        if (isset($array_walkin_OneTime)) {
            $data['array_walkin_OneTime'] = $array_walkin_OneTime;
        }

        if (isset($array_walkin_Recurring)) {
            $data['array_walkin_Recurring'] = $array_walkin_Recurring;
        }

        if (isset($array_phonefee_OneTime)) {
            $data['array_phonefee_OneTime'] = $array_phonefee_OneTime;
        }

        if (isset($array_phonefee_Recurring)) {
            $data['array_phonefee_Recurring'] = $array_phonefee_Recurring;
        }

        if (isset($data['msg'])) {
            $atoken = \Illuminate\Support\Facades\Crypt::encrypt($idproperty . "|" . time() . "|" . config("app.appAPIkey"));
            $data['atoken'] = $atoken;
            return view('eterminal.eterm_new', ['data' => $data, 'token' => $ntoken]);
        }
        $data['type'] = $type;



        return view('eterminal.eterm_pay', ['data' => $data, 'customfield' => $customfield, 'token' => $ntoken]);
    }

    function etermUsrList($token1, $token = null, Request $request) {

        $dtoken = decrypt($token1);


        list($idproperty, $time, $apikey, $first_name, $last_name, $account_number, $address, $web_user_id, $auto) = explode("|", $dtoken);
        $data = array();

        if (empty($token)) {
            $token = encrypt(['level' => "M", 'id' => $idproperty, 'level_id' => $idproperty]);
//            $obj_merchant = new MerchantAccount();
//            $idproperty = $obj_merchant->getproprtyid($idproperty);
        }
//        $mid = $idproperty;
        $level = 'M';
        $idlevel = $idproperty;

        $data['account_number'] = $account_number;
        $data['first_name'] = $first_name;
        $data['last_name'] = $last_name;
        $data['address'] = $address;
        $data['auto'] = $auto;

        $data["pageTitle"] = "e-Terminal";
        if ($idproperty <= 0) {
            $data['msg'] = "Sorry, this link is no longer available. Please contact Customer Service for assistance. Ref EAPNPJF43";
        }

        if ($apikey != config('app.appAPIkey')) {
            $data['msg'] = "Sorry, this link is no longer available. Please contact Customer Service for assistance. Ref EAPKMW13";
        }

        if ($time * 60 * 20 < time()) {
            $data['msg'] = "Sorry, Session Expired please refresh your browser";
        }

        $obj_user = new User();
        $obj_inv = new \App\Model\Invoices();
        $obj_message = new Message();
        $obj_property = new Properties();
        $obj_customfield = new CustomField();
        $customfield = $obj_customfield->getCustomfieldDetails($idproperty);
        $ids = $obj_property->getOnlyIds($idproperty);
        $accsetting = $obj_property->getPropertySettings($idproperty, $ids['id_companies'], $ids['id_partners'], 'ACCSETTING');
        $invsetting = $obj_property->getPropertySettings($idproperty, $ids['id_companies'], $ids['id_partners'], 'INVSETTING');
        $usrnempty = false;
        $data['accsetting'] = $accsetting;
        $data['einvsetting'] = $invsetting;

        //get customize account # and invoice #
        $obj_layout = new \App\Model\Layout();
        $layouts = $obj_layout->getLayoutValues($idlevel, $level);
        $acctitle = $obj_layout->extractLayoutValue('label_acc_number', $layouts);
        $data['acctitle'] = $acctitle;
        $invtitle = $obj_property->getPropertySettings($idproperty, $ids['id_companies'], $ids['id_partners'], 'INVNUMBER');
        if (!empty($invtitle)) {
            $data['invtitle'] = $invtitle;
        } else {
            $data['invtitle'] = 'Invoice #';
        }

        $companyname = $obj_property->getPropertySettings($idproperty, $ids['id_companies'], $ids['id_partners'], 'SHOWCOMPANYNAME');
        $data['showcompanyname'] = $companyname;

        //fields not mandatory for new user in eterminal
        $notmandatorynewuser = $obj_property->getPropertySettings($idproperty, $ids['id_companies'], $ids['id_partners'], 'NOTMANDATORYETERM');
        $data['notmandatorynewuser'] = explode('|', $notmandatorynewuser);

        $noapplycfee = $obj_property->getPropertySettings($idproperty, $ids['id_companies'], $ids['id_partners'], 'NOAPPLYCFEE');
        $data['noapplycfee'] = $noapplycfee;

        $setting_balance = $obj_property->getPropertySettings($idproperty, $ids['id_companies'], $ids['id_partners'], 'SHOW_BALANCE');
        $data['setting_balance'] = $setting_balance;

        //memo setting
        $nomemo = $obj_property->getPropertySettings($idproperty, $ids['id_companies'], $ids['id_partners'], 'NOMEMO');
        $data['nomemo'] = $nomemo;

        //SETTINGS CUSTOM CSS
        $data['custom_css_file'] = $obj_property->getPropertySettings($idproperty, $ids['id_companies'], $ids['id_partners'], 'CUSTOM_STYLESHEET');

        $noetermnewuser = $obj_property->getPropertySettings($idproperty, $ids['id_companies'], $ids['id_partners'], 'NOETERMNEWUSER');
        $data['noetermnewuser'] = $noetermnewuser;

        $walkin_payments = $obj_property->getPropertySettings($idproperty, $ids['id_companies'], $ids['id_partners'], 'ETERMWALKIN');
        $data['walkin_payments'] = $walkin_payments;

        $data['einvsetting'] = $obj_property->getPropertySettings($idproperty, $ids['id_companies'], $ids['id_partners'], 'EINVOICE');

        $data['invlabel'] = $obj_property->getPropertySettings($idproperty, $ids['id_companies'], $ids['id_partners'], 'INVLABEL');
        $data['group'] = $obj_property->getCompanyInfoMinimal($ids['id_companies']);

        //SETTINGS Auto Payments
        $data['existsAutopay'] = $obj_property->getExistsAutopay($idproperty, $ids['id_companies'], $ids['id_partners']);
        $data['freqAutopay'] = $obj_property->getFreqAutpay($idproperty, $ids['id_companies'], $ids['id_partners']);
        $data['limitAutopay'] = $obj_property->getLimitFixAutopay($idproperty, $ids['id_companies'], $ids['id_partners']);
        $data['daysAutopay'] = $obj_property->getDaysAutopay($idproperty, $ids['id_companies'], $ids['id_partners']);

        //SETTINGS DRP
        $data['existsDrp'] = $obj_property->getExistsDrp($idproperty, $ids['id_companies'], $ids['id_partners']);
        $data['textDrp'] = $obj_property->getTextDrp($idproperty, $ids['id_companies'], $ids['id_partners']);
        $data['freqDrp'] = $obj_property->getFreqDrp($idproperty, $ids['id_companies'], $ids['id_partners']);
        $data['daysDrp'] = $obj_property->getDaysDrp($idproperty, $ids['id_companies'], $ids['id_partners']);

        //adding 5 years in advance to end day on the autopayments
        $nocancel = $obj_property->getNoCancelAuto($idproperty, $ids['id_companies'], $ids['id_partners']);
        if ($nocancel == 1) {
            $data['enddate'] = $obj_property->get5yearInAdvance(false);
        } else {
            $data['enddate'] = $obj_property->get5yearInAdvance();
        }

        //get onetime credential
        $data['credOneTime'] = $obj_property->getcredOneTimeCredentials($idproperty, "eterm-");

        //get recurring hight ticket and lower ticket
        $data['velocityOt'] = $obj_property->getHight_LowerTicket($data['credOneTime']);
        //get recurring credential
        $data['credRecurring'] = $obj_property->getcredRecurringCredentials($idproperty, "eterm-");
        //get recurring hight ticket and lower ticket
        $data['velocityRc'] = $obj_property->getHight_LowerTicket($data['credRecurring']);

        $novault = $obj_property->isInactiveVault($idproperty, 0);
        $data['novault'] = $novault;

        //get layout
        $id_layout = $obj_property->getLayoutID($idproperty);
        $labels = $obj_property->getLabels_Layout($id_layout);
        $data['id_layout'] = $id_layout;
        $data['layout'] = $labels;

        $data['invsetting'] = $invsetting;
        if (!isset($paymentCategories))
            $paymentCategories = array();
        //get payments Categories by user
        if (isset($web_user_id)) {
            $paymentCategories = $obj_property->getPaymentWebUserCategories($web_user_id);
        }
        if (count($paymentCategories) == 0) {
            //get payments Categories by properties
            $paymentCategories = $obj_property->getPaymentType($idproperty);
        }
        $data['paymentCategories'] = $paymentCategories;

        $data['merchant'] = $obj_property->getPropertyInfo($idproperty);
        $profiles = array();
        $data['xurl'] = "https://" . $_SERVER['SERVER_NAME'];
        if ((empty($account_number) && empty($address) &&
                empty($first_name) && empty($last_name)) && (empty($web_user_id))) {
            $data['msg'] = $obj_message->getMessageByKey('eterm1');
        }
        if (empty($web_user_id)) {
            $usrlist = $obj_user->findByAccEOptionList($account_number, $address, $first_name, $last_name, $idproperty);
        } else {
            $usrlist = $obj_user->findUsr_wid($web_user_id, $idproperty);
        }

        if (count($usrlist) > 0) {
            if (count($usrlist) == 1) { // only one user found
                if ($usrlist[0]->web_status == 1 || $usrlist[0]->web_status == 998 || $usrlist[0]->web_status == 46) {
                    $data['usr'] = $usrlist[0];
                    $str_custom = $obj_user->customInfo($usrlist[0], $companyname);
                    $str_simple = $obj_user->customSimpleInfo($usrlist[0], $companyname);
                    $data['str_simple'] = $str_simple;
                    $data['str_custom'] = $str_custom;
                    $data['utoken'] = \Illuminate\Support\Facades\Crypt::encrypt($idproperty . '|' . $usrlist[0]->web_user_id . '|' . time() . '|' . config('app.appAPIkey'));
                    $data['account_number'] = $account_number;
                    $data['first_name'] = $first_name;
                    $data['last_name'] = $last_name;
                    $data['address'] = $address;
                    $obj_trans = new Transations();
                    $data['trans'] = $obj_trans->getTransByUsrId_5($usrlist[0]->web_user_id);
                    $data['activeDRP'] = $obj_user->getDRPByUser($usrlist[0]->web_user_id, $idproperty);
                    $data['activeAuto'] = $obj_user->getAutoCountNoDRPByUser($usrlist[0]->web_user_id, $idproperty);

                    $profiles['profiles'] = $obj_user->getPaymentProfiles_Credentials($usrlist[0]->web_user_id, $idproperty, 0);
                    $profiles['isrecurring'] = 0;
                    $cont_profiles = \Illuminate\Support\Facades\View::make('eterm_component.eterm_profile1', $profiles)->__toString();
                    $data['profiles'] = $cont_profiles;

                    $profiles['profiles'] = "";
                    $profiles['profiles'] = $obj_user->getPaymentProfiles_Credentials($usrlist[0]->web_user_id, $idproperty, 1);
                    $profiles['isrecurring'] = 1;
                    $cont_profiles = \Illuminate\Support\Facades\View::make('eterm_component.eterm_profile1', $profiles)->__toString();
                    $data['profiles1'] = $cont_profiles;
                } else {
                    switch ($usrlist[0]->web_status) {
                        case '0':
                            if ($data['merchant']['name_clients']) {
                                $chain = str_replace('[:DBA_NAME:]', $data['merchant']['name_clients'], $obj_message->getMessageByKey('eterm0'));
                                $data['msg'] = $chain;
                            } else {
                                $data['msg'] = $obj_message->getMessageByKey('eterm0');
                            }
                            break;
                        case '999':
                            if ($data['merchant']['name_clients']) {
                                $chain = str_replace('[:DBA_NAME:]', $data['merchant']['name_clients'], $obj_message->getMessageByKey('eterm999'));
                                $data['msg'] = $chain;
                            } else {
                                $data['msg'] = $obj_message->getMessageByKey('eterm999');
                            }
                            break;
                        default:
                            $data['msg'] = $obj_message->getMessageByKey('eterm2');
                            break;
                    }
                }
            } else {
                $data['usrlist'] = $usrlist;
                $data['multiple_search'] = true;
                $atoken = \Illuminate\Support\Facades\Crypt::encrypt($idproperty . "|" . time() . "|" . config("app.appAPIkey"));
                $data['atoken'] = $atoken;
                return view('eterminal.eterm_new', ['data' => $data, 'token' => $token]);
            }
        } else {
            $data['msg'] = $obj_message->getMessageByKey('eterm2');
        }


        if (isset($data['msg'])) {
            $atoken = \Illuminate\Support\Facades\Crypt::encrypt($idproperty . "|" . time() . "|" . config("app.appAPIkey"));
            $data['atoken'] = $atoken;
            return view('eterminal.eterm_new', ['data' => $data, 'token' => $token]);
        }
        $data['type'] = "usr";
        return view('eterminal.eterm_pay', ['data' => $data, 'customfield' => $customfield, 'token' => $token]);
    }

    function etermFind1Usr(Request $request) {
        $atoken = $request->input('xtoken');
        $auto = $request->input('auto');
        $web_user_id = $request->input('webuser');
        $obj_message = new Message();

        if (empty($auto)) {
            $auto = 0;
        }

        $token1 = decrypt($atoken);

        if (empty($web_user_id)) {
            list($idproperty, $web_user_id, $time, $apikey) = explode("|", $token1);
            $error = array();
            if ($idproperty <= 0) {
                return response()->json(array('errcode' => 83, 'msg' => $obj_message->getMessageByKey('invtoken')));
            }

            if ($time * 60 * 20 <= 0) {
                return response()->json(array('errcode' => 83, 'msg' => $obj_message->getMessageByKey('exptoken')));
            }

            if ($web_user_id <= 0) {
                return response()->json(array('errcode' => 83, 'msg' => $obj_message->getMessageByKey('invtoken')));
            }

            if ($apikey != config("app.appAPIkey")) {
                return response()->json(array('errcode' => 83, 'msg' => $obj_message->getMessageByKey('invApiKey')));
            }
        } else {
            if ($token1['level'] != "M") {
                return redirect(route('accessdenied'));
            }
            $idproperty = $token1['level_id'];
        }


        $token = encrypt($idproperty . "|" . time() . "|" . config("app.appAPIkey") . "|||||" . $web_user_id . "|" . $auto);
        return redirect()->route('etermUsrList', [$token]);
    }

    public function etermpay($token, $method, $info, Request $request) {


        $atoken = \Illuminate\Support\Facades\Crypt::decrypt($token);

        list($idproperty, $web_user_id, $time, $apikey) = explode('|', $atoken);

        $obj_user = new User();
        $obj_message = new Message();
        //$obj_sectoken = new SecToken();

        if (($time + 60 * 60) < time()) {
            return response()->json(array('response' => 260, 'responsetext' => $obj_message->getMessageByKey("exptime")));
        }
        if ($idproperty <= 0) {
            return response()->json(array('response' => 261, 'responsetext' => $obj_message->getMessageByKey("invtoken")));
        }
        if ($apikey != config('app.appAPIkey')) {
            return response()->json(array('response' => 261, 'responsetext' => $obj_message->getMessageByKey("invApiKey")));
        }

        $paymentInfo = json_decode($info, true);
        $paymentInfo['start_date'] = date("Y-m-d", strtotime($paymentInfo['start_date']));
        $paymentInfo['id_property'] = $idproperty;
        $paymentInfo['web_user_id'] = $web_user_id;

        if (empty($paymentInfo['profile_id'])) {
            unset($paymentInfo['profile_id']);
        }

        //error_log(print_r($paymentInfo,true),3,"/var/tmp/aaaaEterm.log");
        $obj_property = new Properties();

        $merchant = $obj_property->getPropertyInfo($idproperty);

        $obj_inv = new Invoices();


        $idcompany = $merchant['id_companies'];
        $idpartner = $merchant['id_partners'];

        $obj_users = new User();

        if ($obj_users->isInactiveUser($web_user_id)) {
            $aresult['responsetext'] = $obj_message->getMessageByKey('oneclick7');
            $aresult['response'] = 3;
            return response()->json($aresult);
        }

        $id_layout = $obj_property->getLayoutID($idproperty);

        if (isset($paymentInfo['email'])) {
            if (trim($paymentInfo['email']) != '') {
                $uemail = $obj_users->get1UserInfo($web_user_id, 'email_address');
                if (empty($uemail)) {
                    $obj_users->set1UserInfo($web_user_id, 'email_address', $paymentInfo['email']);
                }
            }
        }

        if (isset($paymentInfo['phone'])) {
            if (trim($paymentInfo['phone']) != '') {
                $uphone = $obj_users->get1UserInfo($web_user_id, 'phone_number');
                if (empty($uphone)) {
                    $obj_users->set1UserInfo($web_user_id, 'phone_number', $paymentInfo['phone']);
                }
            }
        }

        //get inv_id if u R paying an invoicereturn

        if (isset($paymentInfo['inv_number']) && !empty($paymentInfo['inv_number'])) {
            $obj_inv = new \App\Model\Invoices();
            $inv__info = $obj_inv->getInvoiceByInvoice_number($paymentInfo['inv_number'], $idproperty, $web_user_id);
            if (isset($inv__info['id']) && !empty($inv__info['id'])) {
                $paymentInfo['inv_id'] = $inv__info['id'];
                $invoiceAmount = $obj_inv->get1InvData($paymentInfo['inv_id'], 'amount');
                if(!isset($paymentInfo['net_amount'])){
                    $paymentInfo['net_amount']=0;
                    if(isset($paymentInfo['categories'])){
                        $net_amount = $this->calculateAmount($paymentInfo['categories']);
                        $paymentInfo['net_amount'] = $net_amount;
                    }
                }
                $paymentInfo['net_amount'] = $invoiceAmount < $paymentInfo['net_amount'] ? $invoiceAmount : $paymentInfo['net_amount'];
            }
        }


        if ($method == 'cc' || $method == 'am') { //pay cc
            $isrecurring = 0;
            if (isset($paymentInfo['freq']) || $paymentInfo['start_date'] > date("Y-m-d")) {
                $isrecurring = 1;
            }

            if ($obj_property->isInactiveVault($idproperty, $isrecurring)) {
//                return response()->json(array('response'=>33,'responsetext'=>'In If'));
                if ($isrecurring) {
                    return response()->json(array('response' => 33, 'responsetext' => 'Sorry! You cannot save a credit card to your profile.'));
                }

                $ntoken = \Illuminate\Support\Facades\Crypt::encrypt($idproperty . '|' . $web_user_id . '|' . time() . '|' . config('app.appAPIkey'));
                $ninfo = json_encode($paymentInfo);
                if (isset($paymentInfo['inv_id']) && !empty($paymentInfo['inv_id'])) {
                    $result = $this->paymentinv($ntoken, $ninfo, $request);
                    $result['timex'] = $ntoken;
                    return response()->json($result);
                }
                $result = $this->payment($ntoken, $ninfo, $request);
                $aresult = $result->getData(true);
                $aresult['timex'] = $ntoken;

                return response()->json($aresult);
            } else {
//                 return response()->json(array('response'=>33,'responsetext'=>'In Else'));
                $card_info = array();
                if (isset($paymentInfo['ccnumber'])) {
                    $card_info['ccnumber'] = $paymentInfo['ccnumber'];
                } else {
                    if (isset($paymentInfo['cc_number'])) {
                        $card_info['ccnumber'] = $paymentInfo['cc_number'];
                    }
                }
                $card_info['ccname'] = $paymentInfo['ccname'];
                $card_info['zip'] = $paymentInfo['zip'];
                $card_info['ccexp'] = $paymentInfo['ccexp'];
                $type = 'cc'; //other type with eterm-cc
                switch (substr($card_info['ccnumber'], 0, 1)) {
                    case 3:
                        $card_info['cctype'] = 'AmericanExpress';
                        $type = 'am';
                        break;
                    case 4:
                        $card_info['cctype'] = 'Visa';
                        break;
                    case 5:
                        $card_info['cctype'] = 'MasterCard';
                        break;
                    case 6:
                        $card_info['cctype'] = 'Discover';
                        break;
                    default:
                        $card_info['cctype'] = 'Unknown';
                        break;
                }

                $card_type_fee = $obj_property->getCardTypeFee($card_info['ccnumber'], $card_info['cctype']);

                $credential = $obj_property->getCredentialtype_isrecurring($type, $idproperty, 0);

                if (count($credential) < 1) {
                    $credential = $obj_property->getCredentialtype_isrecurring($type, $idproperty, 1);
                    if (count($credential) < 1) {
                        return response()->json(array('response' => 240, 'responsetext' => 'Credit Card credential do not exist'));
                    }
                }
                $credential = $credential[0];
                $obj_paymentProcessor = new PaymentProcessor();
                $card_info['id_property'] = $idproperty;
                if ($isrecurring == 1) {
                    $result = $obj_paymentProcessor->getToken($card_info, $credential);

                    if ($result['response'] != 1) {
                        return response()->json($result);
                    }
                    $cc_name = "XXXX- " . substr($card_info['ccnumber'], -4);
                    $ccprofile_info = array(
                        'vid' => $result['token'],
                        'exp_date' => $card_info['ccexp'],
                        'cc_type' => $card_info['cctype'],
                        'ch_name' => $card_info['ccname'],
                        'card_type_fee' => $card_type_fee
                    );


                    $ccjson = json_encode($ccprofile_info);
                    $profileid = $obj_users->insertCCpaymethod($ccjson, $cc_name, $type, $idproperty, $web_user_id);
                    RevoPayAuditLogger::paymentMethodCreate('admin', array('operation' => 'Create payment method CC', 'type' => $type, 'name' => $cc_name, 'profile_id' => $profileid), 'M', $idproperty, WebUsers::getAuditData($web_user_id), Auth::user());

                    $paymentInfo['profile_id'] = $profileid;
                    $paymentInfo['type'] = 'eterm-' . $type;
                } else {
                    if (isset($paymentInfo['saveprofile']) && !empty($paymentInfo['saveprofile'])) {
                        //create profile when is not recurring
                        $result = $obj_paymentProcessor->getToken($card_info, $credential);
                        if ($result['response'] != 1) {
                            return response()->json($result);
                        }
                        $cc_name = "XXXX- " . substr($card_info['ccnumber'], -4);
                        $ccprofile_info = array(
                            'vid' => $result['token'],
                            'exp_date' => $card_info['ccexp'],
                            'cc_type' => $card_info['cctype'],
                            'ch_name' => $card_info['ccname'],
                            'card_type_fee' => $card_type_fee
                        );


                        $ccjson = json_encode($ccprofile_info);
                        $profileid = $obj_users->insertCCpaymethod($ccjson, $cc_name, $type, $idproperty, $web_user_id);
                        RevoPayAuditLogger::paymentMethodCreate('admin', array('operation' => 'Create payment method CC', 'type' => $type, 'name' => $cc_name, 'profile_id' => $profileid), 'M', $idproperty, WebUsers::getAuditData($web_user_id), Auth::user());

                        $paymentInfo['profile_id'] = $profileid;
                        $paymentInfo['type'] = 'eterm-' . $type;
                    }
                }
                $paymentInfo['card_type_fee'] = $card_type_fee;
                $ntoken = \Illuminate\Support\Facades\Crypt::encrypt($idproperty . '|' . $web_user_id . '|' . time() . '|' . config('app.appAPIkey'));
                $ninfo = json_encode($paymentInfo);

                if (isset($paymentInfo['inv_id']) && !empty($paymentInfo['inv_id'])) {
                    $result = $this->paymentinv($ntoken, $ninfo, $request);
                    $aresult = $result->getData(true);
                    $aresult['timex'] = $ntoken;

                    if (!isset($paymentInfo['saveprofile']) || !$paymentInfo['saveprofile']) {
                        //delete profile
                        if (isset($profileid)) {
                            $obj_users->deleteProfile($profileid);
                            RevoPayAuditLogger::paymentMethodDelete('admin', array('operation' => 'Delete payment method', 'info' => 'Save payment method not selected', 'profile_id' => $profileid), 'M', $idproperty, WebUsers::getAuditData($web_user_id), Auth::user());
                        }
                    }
                    return response()->json($aresult);
                }
                $result = $this->payment($ntoken, $ninfo, $request);
                $aresult = $result->getData(true);
                $aresult['timex'] = $ntoken;
                if (!isset($aresult['auto']) || $aresult['auto'] == 0) {
                    if (!isset($paymentInfo['saveprofile']) || !$paymentInfo['saveprofile']) {
                        //delete profile
                        if (isset($profileid)) {
                            $obj_users->deleteProfile($profileid);
                        }
                    }
                }
                return response()->json($aresult);
            }
        } //for payment method is swipe :AJ - Accepted Payment
        elseif ($method == 'swipe') {
            $isrecurring = 0;
            if (isset($paymentInfo['freq']) || $paymentInfo['start_date'] > date("Y-m-d")) {
                $isrecurring = 1;
            }

            if ($obj_property->isInactiveVault($idproperty, $isrecurring)) {
                return response()->json(array('response' => 33, 'responsetext' => 'In If'));
                if ($isrecurring) {
                    return response()->json(array('response' => 33, 'responsetext' => 'Sorry! You cannot save a credit card to your profile.'));
                }

                $ntoken = \Illuminate\Support\Facades\Crypt::encrypt($idproperty . '|' . $web_user_id . '|' . time() . '|' . config('app.appAPIkey'));
                $ninfo = json_encode($paymentInfo);
                if (isset($paymentInfo['inv_id']) && !empty($paymentInfo['inv_id'])) {
                    $paymentInfo['swipe'] = 1;
                    $result = $this->paymentinv($ntoken, $ninfo, $request);
                    $result['timex'] = $ntoken;
                    return response()->json($result);
                }

                $result = $this->payment($ntoken, $ninfo, $request);
//                return response()->json(array('response'=>33,'responsetext'=>$result));
                $aresult = $result->getData(true);
                $aresult['timex'] = $ntoken;

                return response()->json($aresult);
            } else {
                $card_info = array();
                if (isset($paymentInfo['ccnumber'])) {
                    $card_info['ccnumber'] = $paymentInfo['ccnumber'];
                } else {
                    if (isset($paymentInfo['cc_number'])) {
                        $card_info['ccnumber'] = $paymentInfo['cc_number'];
                    }
                }
//                        return response()->json(array('response'=>33,'responsetext'=>$paymentInfo['ccexp']));
                $card_info['ccname'] = $paymentInfo['ccname'];
//                $card_info['zip']=$paymentInfo['zip'];
                $card_info['ccexp'] = $paymentInfo['ccexp'];

                $type = 'swipe'; //other type with eterm-cc
                switch (substr($card_info['ccnumber'], 0, 1)) {
                    case 3:
                        $card_info['cctype'] = 'AmericanExpress';
                        $type = 'am';
                        break;
                    case 4:
                        $card_info['cctype'] = 'Visa';
                        break;
                    case 5:
                        $card_info['cctype'] = 'MasterCard';
                        break;
                    case 6:
                        $card_info['cctype'] = 'Discover';
                        break;
                    default:
                        $card_info['cctype'] = 'Unknown';
                        break;
                }
                $card_type_fee = $obj_property->getCardTypeFee($card_info['ccnumber'], $card_info['cctype']);
//                 return response()->json(array('response'=>33,'responsetext'=>$card_type_fee));
                $credential = $obj_property->getCredentialtype_isrecurring($type, $idproperty, 0);
                if (count($credential) < 1) {
                    $credential = $obj_property->getCredentialtype_isrecurring($type, $idproperty, 1);
                    if (count($credential) < 1) {
                        return response()->json(array('response' => 240, 'responsetext' => 'Credit Card credential do not exist'));
                    }
                }
                $credential = $credential[0];
                $obj_paymentProcessor = new PaymentProcessor();
                $card_info['id_property'] = $idproperty;
                if ($isrecurring == 1) {
                    $result = $obj_paymentProcessor->getToken($card_info, $credential);

                    if ($result['response'] != 1) {
                        return response()->json($result);
                    }
                    $cc_name = "XXXX- " . substr($card_info['ccnumber'], -4);
                    $ccprofile_info = array(
                        'vid' => $result['token'],
                        'exp_date' => $card_info['ccexp'],
                        'cc_type' => $card_info['cctype'],
                        'ch_name' => $card_info['ccname'],
                        'card_type_fee' => $card_type_fee
                    );


                    $ccjson = json_encode($ccprofile_info);
                    $profileid = $obj_users->insertCCpaymethod($ccjson, $cc_name, $type, $idproperty, $web_user_id);
                    RevoPayAuditLogger::paymentMethodCreate('admin', array('operation' => 'Create payment method CC', 'type' => $type, 'name' => $cc_name, 'profile_id' => $profileid), 'M', $idproperty, WebUsers::getAuditData($web_user_id), Auth::user());


                    $paymentInfo['profile_id'] = $profileid;
                    $paymentInfo['type'] = 'eterm-' . $type;
                } else {
                    $paymentInfo['swipe'] = 1;
                    if (isset($paymentInfo['saveprofile']) && !empty($paymentInfo['saveprofile'])) {
                        //create profile when is not recurring
                        $result = $obj_paymentProcessor->getToken($card_info, $credential);
                        if ($result['response'] != 1) {
                            return response()->json($result);
                        }

                        $cc_name = "XXXX- " . substr($card_info['ccnumber'], -4);
                        $ccprofile_info = array(
                            'vid' => $result['token'],
                            'exp_date' => $card_info['ccexp'],
                            'cc_type' => $card_info['cctype'],
                            'ch_name' => $card_info['ccname'],
                            'card_type_fee' => $card_type_fee
                        );

//                      return response()->json(array('response'=>240,'responsetext'=>'Hello'));
                        $ccjson = json_encode($ccprofile_info);
                        $profileid = $obj_users->insertCCpaymethod($ccjson, $cc_name, $type, $idproperty, $web_user_id);
                        RevoPayAuditLogger::paymentMethodCreate('admin', array('operation' => 'Create payment method CC', 'type' => $type, 'name' => $cc_name, 'profile_id' => $profileid), 'M', $idproperty, WebUsers::getAuditData($web_user_id), Auth::user());

                        $paymentInfo['profile_id'] = $profileid;
                        $paymentInfo['type'] = 'eterm-' . $type;
                    }
                }
                $paymentInfo['card_type_fee'] = $card_type_fee;

                $ntoken = \Illuminate\Support\Facades\Crypt::encrypt($idproperty . '|' . $web_user_id . '|' . time() . '|' . config('app.appAPIkey'));

                $ninfo = json_encode($paymentInfo);
                if (isset($paymentInfo['inv_id']) && !empty($paymentInfo['inv_id'])) {
                    // return response()->json(array('response'=>240,'responsetext'=>'In If'));
                    $paymentInfo['swipe'] = 1;
                    $result = $this->paymentinv($ntoken, $ninfo, $request);
                    $aresult = $result->getData(true);
                    $aresult['timex'] = $ntoken;

                    if (!isset($paymentInfo['saveprofile']) || !$paymentInfo['saveprofile']) {
                        //delete profile
                        if (isset($profileid)) {
                            $obj_users->deleteProfile($profileid);
                            RevoPayAuditLogger::paymentMethodDelete('admin', array('operation' => 'Delete payment method', 'info' => 'Save payment method not selected', 'profile_id' => $profileid), 'M', $idproperty, WebUsers::getAuditData($web_user_id), Auth::user());
                        }
                    }
                    return response()->json($aresult);
                }

                $result = $this->payment($ntoken, $ninfo, $request);
                $aresult = $result->getData(true);
                $aresult['timex'] = $ntoken;
                if (!isset($aresult['auto']) || $aresult['auto'] == 0) {
                    if (!isset($paymentInfo['saveprofile']) || !$paymentInfo['saveprofile']) {
                        //delete profile
                        if (isset($profileid)) {
                            $obj_users->deleteProfile($profileid);
                            RevoPayAuditLogger::paymentMethodDelete('admin', array('operation' => 'Delete payment method', 'info' => 'Save payment method not selected', 'profile_id' => $profileid), 'M', $idproperty, WebUsers::getAuditData($web_user_id), Auth::user());
                        }
                    }
                }
                return response()->json($aresult);
            }
            return response()->json(array('response' => 272, 'responsetext' => 'Swipe Payment'));
        } elseif ($method == 'ec') {
            $ec_info = array();
            $type = 'eterm-ec';
            $ec_info['ec_account_holder'] = $paymentInfo['ec_account_holder'];
            $ec_info['ec_account_lholder'] = $paymentInfo['ec_account_lholder'];
            $ec_info['ec_routing_number'] = $paymentInfo['ec_routing_number'];
            $ec_info['ec_checking_savings'] = $paymentInfo['ec_checking_savings'];
            $ec_info['ec_account_number'] = $paymentInfo['ec_account_number'];
            $ecjson = json_encode($ec_info);
            $profileid = $obj_users->insertECpaymethod($ecjson, $idproperty, $web_user_id);
            RevoPayAuditLogger::paymentMethodCreate('admin', array('operation' => 'Create payment method EC', 'type' => 'ec', 'profile_id' => $profileid), 'M', $idproperty, WebUsers::getAuditData($web_user_id), Auth::user());

            $paymentInfo['profile_id'] = $profileid;
            $paymentInfo['type'] = $type;

            $ntoken = \Illuminate\Support\Facades\Crypt::encrypt($idproperty . '|' . $web_user_id . '|' . time() . '|' . config('app.appAPIkey'));
            $ninfo = json_encode($paymentInfo);
            if (isset($paymentInfo['inv_id']) && !empty($paymentInfo['inv_id'])) {
                $result = $this->paymentinv($ntoken, $ninfo, $request);
                $aresult = $result->getData(true);
                $aresult['timex'] = $ntoken;
                if (!isset($paymentInfo['saveprofile']) || !$paymentInfo['saveprofile']) {
                    //delete profile
                    if (isset($profileid)) {
                        $obj_users->deleteProfile($profileid);
                        RevoPayAuditLogger::paymentMethodDelete('admin', array('operation' => 'Delete payment method', 'info' => 'Save payment method not selected', 'profile_id' => $profileid), 'M', $idproperty, WebUsers::getAuditData($web_user_id), Auth::user());
                    }
                }
                return response()->json($aresult);
            }
            $result = $this->payment($ntoken, $ninfo, $request);
            $aresult = $result->getData(true);
            $aresult['timex'] = $ntoken;
            if (!isset($aresult['auto']) || $aresult['auto'] == 0) {
                if (!isset($paymentInfo['saveprofile']) || !$paymentInfo['saveprofile']) {
                    //delete profile
                    if (isset($profileid)) {
                        $obj_users->deleteProfile($profileid);
                        RevoPayAuditLogger::paymentMethodDelete('admin', array('operation' => 'Delete payment method', 'info' => 'Save payment method not selected', 'profile_id' => $profileid), 'M', $idproperty, WebUsers::getAuditData($web_user_id), Auth::user());
                    }
                }
            }


            return response()->json($aresult);
        } elseif ($method == 'prf') {
            $ntoken = \Illuminate\Support\Facades\Crypt::encrypt($idproperty . '|' . $web_user_id . '|' . time() . '|' . config('app.appAPIkey'));
            $profile = $obj_user->getPaymentProfileById1($paymentInfo['profile_id']);
            $paymentInfo['type'] = 'eterm-' . $profile->type;
            $ninfo = json_encode($paymentInfo);
            if (isset($paymentInfo['inv_id']) && !empty($paymentInfo['inv_id'])) {
                $result = $this->paymentinv($ntoken, $ninfo, $request);
                $aresult = $result->getData(true);
                $aresult['timex'] = $ntoken;
                return response()->json($aresult);
            }

            $result = $this->payment($ntoken, $ninfo, $request);
            $aresult = $result->getData(true);
            $aresult['timex'] = $ntoken;
            return response()->json($aresult);
        } else {
            return response()->json(array('response' => 272, 'responsetext' => 'Unknown payment method'));
        }
    }

    public function payment($token, $info, Request $request) {
        $atoken = $this->validateDecrypt($token);
        list($idproperty, $web_user_id, $time, $apikey) = explode('|', $atoken);

        $obj_user = new User();
        $obj_properties = new Properties();
        //$obj_sectoken = new SecToken();

        if (($time + 60 * 60) < time()) {
            //return response()->json(array('response'=>260,'responsetext'=>'Invalid Token'));
        }
        if ($idproperty <= 0) {
            return response()->json(array('response' => 261, 'responsetext' => 'Invalid Token'));
        }

        if ($web_user_id <= 0) {
            return response()->json(array('response' => 262, 'responsetext' => 'Invalid Token'));
        }

        if ($apikey != config('app.appAPIkey')) {
            return response()->json(array('response' => 262, 'responsetext' => 'Invalid Token'));
        }

        //validate token
//        $ipaddr = $request->ip();
//        $secToken_info= $obj_sectoken->isValidToken($token, $ipaddr);
//        if(!$secToken_info['response']){
//            return response()->json(array('response'=>261,'responsetext'=>$secToken_info['responsetext']));
//        }

        $paymentInfo = json_decode($info, true);
        $paymentInfo['id_property'] = $idproperty;
        $paymentInfo['web_user_id'] = $web_user_id;
        // error_log(print_r($paymentInfo,true),3,"/var/tmp/aaaaEterm1.log");
        if (empty($paymentInfo['categories']) && (!isset($paymentInfo['dynamic']) || $paymentInfo['dynamic'] == 0)) {
            $tmp = array();
            $tmp['amount'] = $paymentInfo['net_amount'];
            $tmp['qty'] = 1;
            $tmp['name'] = "Payment";
            $tmp['id'] = 0;
            $paymentInfo['categories'][] = $tmp;
        }


        $paymentInfo['start_date'] = date("Y-m-d", strtotime($paymentInfo['start_date']));
        $date_today = date("Y-m-d");
        if (strtotime($paymentInfo['start_date']) < strtotime($date_today)) {
            $paymentInfo['start_date'] = $date_today;
        }

        if (!isset($paymentInfo['profile_id']) || $paymentInfo['profile_id'] < 1) {
            if (isset($paymentInfo['ec_account_number']) && !empty($paymentInfo['ec_account_number'])) {
                //ec payment
                $ec_info = array();
                $ec_info['ec_account_holder'] = $paymentInfo['ec_account_holder'];
                $ec_info['ec_account_lholder'] = $paymentInfo['ec_account_lholder'];
                $ec_info['ec_routing_number'] = $paymentInfo['ec_routing_number'];
                $ec_info['ec_checking_savings'] = $paymentInfo['ec_checking_savings'];
                $ec_info['ec_account_number'] = $paymentInfo['ec_account_number'];
                $ecjson = json_encode($ec_info);
                $obj_users = new User();
                $profileid = $obj_users->insertECpaymethod($ecjson, $idproperty, $web_user_id);
                $paymentInfo['profile_id'] = $profileid;
                $paymentInfo['type'] = 'ec';
                $paymentInfo['payor_name'] = $paymentInfo['ec_account_holder'];
                if ($paymentInfo['source'] == 'eterm') { //to use eterminal credentials
                    $paymentInfo['type_cc'] = 'eterm-ec';
                } else {
                    if ($paymentInfo['source'] == 'ivr') {//to use IVR credentials
                        $paymentInfo['type_cc'] = 'ivr-ec';
                    }
                }
            } else if (isset($paymentInfo['ccnumber']) && !empty($paymentInfo['ccnumber'])) {
                $paymentInfo['payor_name'] = $paymentInfo['ccname'];
                //cc payment
                if ($paymentInfo['source'] == 'eterm') { //to use eterminal credentials
                    if (isset($paymentInfo['swipe'])) {
                        $paymentInfo['type_cc'] = 'swipe';
                    } else {
                        $paymentInfo['type_cc'] = 'eterm-cc';
                    }
                } else {
                    if ($paymentInfo['source'] == 'ivr') {
                        $paymentInfo['type_cc'] = 'ivr-cc';
                    }
                }

                $oneTime = !isset($paymentInfo['freq']) && $paymentInfo['start_date'] == date("Y-m-d");

                $paymentInfo['type'] = 'cc';
                switch (substr($paymentInfo['ccnumber'], 0, 1)) {
                    case 3:
                        $paymentInfo['cctype'] = 'AmericanExpress';
                        $paymentInfo['type'] = 'amex';
                        if (($paymentInfo['source'] == 'eterm' && $paymentInfo['type_cc'] != 'swipe') || (!$oneTime && $paymentInfo['type_cc'] == 'swipe')) {
                            $paymentInfo['type_cc'] = 'eterm-amex';
                        }
                        break;
                    case 4:
                        $paymentInfo['cctype'] = 'Visa';
                        break;
                    case 5:
                        $paymentInfo['cctype'] = 'MasterCard';
                        break;
                    case 6:
                        $paymentInfo['cctype'] = 'Discover';
                        break;
                    default :
                        $paymentInfo['cctype'] = 'Unknown';
                        break;
                }
            } else {
                return response()->json(array('response' => 447, 'responsetext' => 'Error! Please select a Payment Method'));
            }
        }

        $decrypted_token_array = null;
        if (isset($paymentInfo['profile_id']) && $paymentInfo['profile_id'] > 0) {
            $profile = $obj_user->getPaymentProfileById($web_user_id, $paymentInfo['profile_id']);
            if (empty($profile)) {
                $obj_user->deleteProfile($paymentInfo['profile_id']);
                return response()->json(array('response' => 547, 'responsetext' => 'Error! Empty payment profile'));
            }
            $decrypted_token = $this->validateDecrypt($profile->token);
            $paymentInfo['token'] = $decrypted_token_array = json_decode($decrypted_token, true);
            if ($profile->type == 'ec') {
                $paymentInfo['payor_name'] = $paymentInfo['token']['ec_account_holder'];
            } else {
                if (isset($paymentInfo['token']['ch_name'])) {
                    $paymentInfo['payor_name'] = $paymentInfo['token']['ch_name'];
                }
            }
            if (($paymentInfo['source'] != 'eterm' && $paymentInfo['source'] != 'ivr')) {
                $paymentInfo['type'] = $profile->type;
            }
        } else {
            $profile = new \stdClass();
        }

        $paymentInfo['start_date'] = date("Y-m-d", strtotime($paymentInfo['start_date']));

        if (isset($profile->type)) {
            $paymentInfo['card_info'] = $this->getCardInfo($decrypted_token, $profile->type, $profile->name);
        } else {
            $ccnumber = substr($paymentInfo['ccnumber'], -4);
            $paymentInfo['card_info']['card_type'] = $paymentInfo['cctype'] . " (" . $ccnumber . ")";
            $paymentInfo['card_info']['payment_type'] = $paymentInfo['type'];
        }


        $credential = array();
        if (!isset($paymentInfo['freq']) && $paymentInfo['start_date'] == date("Y-m-d")) { //one time credentials
            if (isset($paymentInfo['type_cc']) && !empty($paymentInfo['type_cc'])) {
                $pptype = $paymentInfo['type_cc'];
            } else {
                if (isset($paymentInfo['type']) && !empty($paymentInfo['type'])) {
                    $pptype = $paymentInfo['type'];
                } else {
                    if (isset($profile->type)) {
                        $pptype = $profile->type;
                    }
                }
            }
            $credential = $obj_properties->getCredentialsBytype($pptype, $idproperty);
        } else {

            $credential = $obj_properties->getCredentialtype_isrecurring($paymentInfo['type'], $idproperty, 1);
            if (empty($credential)) {
                if ($profile->type != 'ec') {
                    $str = "We’re sorry, you cannot schedule a credit card payment for the future. To make a credit card payment, you must make the payment today. If you wish to pay on a future date, please make the payment using a bank account.";
                } else {
                    $str = "We’re sorry, you cannot schedule an e-check payment for the future. To make an e-check payment, you must make the payment today. If you wish to pay on a future date, please make the payment using a different payment method.";
                }
                return response()->json(array('response' => 998, 'responsetext' => $str));
            }
        }

        if (!isset($profile->type)) {
            $profile->type = $paymentInfo['type'];
        }
        //calculate net amount and get convenience fee
        $net_amount = $this->calculateAmount($paymentInfo['categories']);
        $paymentInfo['net_amount'] = $net_amount;


        if (!isset($profile->token)) {
            $profile->token = \Illuminate\Support\Facades\Crypt::encrypt($ccnumber);
        }


        //Fraud Control
        if (!isset($paymentInfo['dynamic']) || $paymentInfo['dynamic'] != 1) {
            if ($paymentInfo['card_info']['payment_type'] == 'cc') {
                $obj_fraud = new FraudControl($idproperty, $web_user_id, $profile->token, $net_amount);
                if ($obj_fraud->isFraud()) {
                    return response()->json(array('response' => 999, 'responsetext' => 'Possible Fraud Detected! Payment blocked. If you think this is a mistake, please call our customer service at (305) 252-8297 option 2'));
                }
            }
        }
        //BIN settings
        $obj_bin = new Bin();
        $ids = $obj_properties->getOnlyIds($idproperty);
        $bin = $obj_properties->getPropertySettings($idproperty, $ids['id_companies'], $ids['id_partners'], "BINCARD");
        if (!empty($bin)) {
            if (!isset($paymentInfo['profile_id']) || empty($paymentInfo['profile_id'])) {
                if (isset($paymentInfo['ccnumber'])) {
                    $msg = $obj_bin->ValidCCard($paymentInfo['ccnumber'], $bin);
                    if (!$msg) {
                        return response()->json(array('response' => 777, 'responsetext' => "This card type is not currently accepted"));
                    }
                } else {
                    return response()->json(array('response' => 778, 'responsetext' => "Empty Credit Card"));
                }
            }
        }
        $obj_transactions = new Transations();

        //ask if payments is DRP
        $isdrp = (isset($paymentInfo['dynamic']) && $paymentInfo['dynamic'] == 1);
        $isPPD = (isset($paymentInfo['ppd']) && $paymentInfo['ppd'] == 1);
        if (isset($paymentInfo['card_type_fee'])) {
            $card_type_fee = $paymentInfo['card_type_fee'];
        } else {
            $card_type_fee = "";
        }
        if ($isdrp || $isPPD) {
            $paymentInfo['ppd'] = 1;
            if (date("Y-m-d") == date("Y-m-d", strtotime($paymentInfo['start_date']))) {
                if (isset($paymentInfo['end_date'])) {
                    $payend_date = $obj_transactions->getformatenddate($paymentInfo['start_date'], $paymentInfo['end_date']);
                    $start_date = $this->getNextpostdate($paymentInfo['freq'], $paymentInfo['start_date'], $payend_date);
                    $paymentInfo['start_date'] = $start_date;
                }
            }
            //Calculate Convenience Fee DRP
            //$conv_fee = $obj_transactions->getFeeDRP($credential, $net_amount, $card_type_fee);
            $conv_fee['ERROR'] = 0;
            $conv_fee['CFEE'] = number_format(0, 2);
            $conv_fee['TIER_APPLIED'] = 0;
        } else {
            //get fee and verify the velocities NO DYNAMIC
            if (isset($decrypted_token_array['card_type_fee'])) {
                $card_type_fee = $decrypted_token_array['card_type_fee'];
            }
            $service_type = "";
            if (isset($paymentInfo['service'])) {
                $service_type = $paymentInfo['service'];
            }
            $conv_fee = $obj_transactions->getFee($credential, $net_amount, $card_type_fee, $service_type);
        }


        if ($conv_fee['ERROR'] == 1) {
            return response()->json(array('response' => 0, 'responsetext' => $conv_fee['ERRORCODE']));
        }

        if (isset($paymentInfo['xcfee']) && $paymentInfo['xcfee'] != '') {
            $conv_fee['CFEE'] = $paymentInfo['xcfee'] * 1;
        }

        $obj_mail = new Email();

        //adding phone fee to cfee (eterminal settings)
        if(isset($paymentInfo['xwalkin'])){
            if($paymentInfo['xwalkin'] == 'false') {
                $paymentInfo['xwalkin'] = 0;
            }else {
                $paymentInfo['xwalkin'] = 1;
                $conv_fee['CFEE'] = 0;
            }
        }
      /*  if(isset($paymentInfo['xphonefee'])){

                $paymentInfo['fee'] = floatval($conv_fee['CFEE']) + floatval($paymentInfo['xphonefee']);
                $paymentInfo['total_amount'] = $net_amount + $paymentInfo['fee'];
        }else{
            $paymentInfo['fee'] = $conv_fee['CFEE'];
            $paymentInfo['total_amount'] = $conv_fee['CFEE'] + $net_amount;
        }*/

        $paymentInfo['fee'] = $conv_fee['CFEE'];
        $paymentInfo['total_amount'] = $conv_fee['CFEE'] + $net_amount;

        //putting a tier that is applied to this amount
        $credential = $credential[$conv_fee['TIER_APPLIED']];

        $invnumber = "";
        if (isset($paymentInfo['inv_number']) && !empty($paymentInfo['inv_number'])) {
            $invnumber = $paymentInfo['inv_number'];
        }
        if (!isset($paymentInfo['memo']))
            $paymentInfo['memo'] = '';

        //get payment description to insert in accounting transactions
        $paymentInfo['descr'] = $this->getPayment_descr($paymentInfo['categories'], $paymentInfo['fee'], $paymentInfo['memo'], $invnumber, $paymentInfo);

        //is recurring
        if (!isset($paymentInfo['trans_type'])) {
            if (!empty($paymentInfo['freq'])) {
                $paymentInfo['trans_type'] = 1;
            } else
                $paymentInfo['trans_type'] = 0;
        }
        //setting nacha_type. parameter b2b only came from Business vertical
        $paymentInfo['nacha_type'] = 'WEB';
        if (!empty($paymentInfo['b2b']))
            $paymentInfo['nacha_type'] = 'CCD';
        if (isset($paymentInfo['ppd'])) {
            $paymentInfo['nacha_type'] = 'PPD';
        }

        if (isset($paymentInfo['swipe'])) {
            $paymentInfo['source'] = 'Swipe';
        }

        $obj_paymentProcessor = new PaymentProcessor();
        $obj_customfield = new CustomField();

        if (date("Y-m-d") == date("Y-m-d", strtotime($paymentInfo['start_date']))) {

            //adding phone fee to cfee on One Time
            if(isset($paymentInfo['xphonefee'])){

                $paymentInfo['fee'] = floatval($conv_fee['CFEE']) + floatval($paymentInfo['xphonefee']);
                $paymentInfo['total_amount'] = $net_amount + $paymentInfo['fee'];
            }else{
                $paymentInfo['fee'] = $conv_fee['CFEE'];
                $paymentInfo['total_amount'] = $conv_fee['CFEE'] + $net_amount;
            }

            switch ($credential->gateway) {
                case 'bokf':
                case 'nmi':
                case 'fd4':
                case 'fde4':
                case 'trans1':
                case 'ppal':
                    $paymentInfo['trans_id'] = $this->runPayment($paymentInfo, $credential->key);

                    if (empty($paymentInfo['trans_id']) || $paymentInfo['trans_id'] == 0) {
                        return response()->json(array('response' => 88, 'responsetext' => "DB Communication error. Wait a minute and try again!"));
                    }
                    if (isset($paymentInfo['customfield'])) {
                        $obj_customfield->addTransaction($paymentInfo['trans_id'], $paymentInfo['customfield'], $paymentInfo['id_property'], $paymentInfo['web_user_id']);
                    }
                    if (isset($paymentInfo['profile_id'])) {

                        //for first time swipe payment with track2 data for nmi gateway:AJ - Accepted Payment
                        if (isset($paymentInfo['type']) && $paymentInfo['type'] == 'swipe' && !empty($paymentInfo['Track2Data'])) {
                            $response = $obj_paymentProcessor->RunSwipe($paymentInfo, $credential);
                        } else {
                            //for swipe save as credit card for nmi gateway:AJ - Accepted Payment
                            if (isset($paymentInfo['type']) && $paymentInfo['type'] == 'swipe') {
                                $paymentInfo['type'] == 'eterm-cc';
                            }

                            $response = $obj_paymentProcessor->runToken($paymentInfo, $credential);
                        }
                    } else {
                        //for first time swipe payment without saving for future use for nmi gateway:AJ - Accepted Payment
                        if (!empty($paymentInfo['Track2Data'])) {
                            $paymentInfo['Track2Data'] = $paymentInfo['Track2Data'];
                            $response = $obj_paymentProcessor->RunSwipe($paymentInfo, $credential);
                        } else {
                            $response = $obj_paymentProcessor->RunTx($paymentInfo, $credential);
                        }
                    }

                    $response['txid'] = $paymentInfo['trans_id'];
                    //send email
                    $obj_mail->PaymentReceipt($response, $paymentInfo);

                    //For swipe update payment for nmi gateway:AJ - Accepted Payment
                    if (!empty($paymentInfo['Track2Data'])) {
                        $this->updatePayment($paymentInfo['trans_id'], $response, 1);
                    } else {
                        $this->updatePayment($paymentInfo['trans_id'], $response);
                    }

                    //save trans_data only ec
                    if (isset($paymentInfo['trans_id']) && isset($paymentInfo['profile_id']) && $credential->gateway == 'bokf') {
                        $obj_transactions->SetTransData($paymentInfo['trans_id'], $paymentInfo['profile_id']);
                    }
                    break;
                case 'profistars':
                    //run frist payment (net amount)
                    $paymentInfo['trans_id'] = $this->runPayment($paymentInfo, $credential->key);
                    if (empty($paymentInfo['trans_id']) || $paymentInfo['trans_id'] == 0) {
                        return response()->json(array('response' => 88, 'responsetext' => "DB Communication error. Wait a minute and try again!"));
                    }
                    if (isset($paymentInfo['customfield'])) {
                        $obj_customfield->addTransaction($paymentInfo['trans_id'], $paymentInfo['customfield'], $paymentInfo['id_property'], $paymentInfo['web_user_id']);
                    }
                    $paymentInfo['total_amount'] = $paymentInfo['net_amount'];

                    $response = $obj_paymentProcessor->runToken($paymentInfo, $credential);
                    $response['txid'] = $paymentInfo['trans_id'];
                    //send email
                    $obj_mail->PaymentReceipt($response, $paymentInfo);

                    //update
                    $this->updatePayment($paymentInfo['trans_id'], $response);

                    //save trans_data only ec
                    if (isset($paymentInfo['trans_id']) && isset($paymentInfo['profile_id'])) {
                        $obj_transactions->SetTransData($paymentInfo['trans_id'], $paymentInfo['profile_id']);
                    }

                    //run secund payment (convenience fee)
                    if ($paymentInfo['fee'] > 0 && $response['response'] == 1) { //asking if profistars has convenience fee
                        $npaymentInfo=$paymentInfo;
                        $npaymentInfo['total_amount'] = $paymentInfo['fee'];
                        $npaymentInfo['new_net_amount'] = $paymentInfo['net_amount'];
                        $npaymentInfo['net_amount'] = $paymentInfo['fee'];
                        $npaymentInfo['fee'] = 0;
                        $npaymentInfo['parent_trans_id'] = $paymentInfo['trans_id'];
                        $npaymentInfo['trans_id'] = $this->runCfeePayment($npaymentInfo, $credential->key);
                        $convFeeCredential = new \stdClass();
                        $convFeeCredential->gateway = 'profistars';
                        $response1 = $obj_paymentProcessor->runToken($npaymentInfo, $convFeeCredential);
                        //update
                        $this->updatePayment($npaymentInfo['trans_id'], $response1);
                    }
                    break;
                case 'prismpay':
                    //run frist payment (net amount)
                    $paymentInfo['trans_id'] = $this->runPayment($paymentInfo, $credential->key);
                    if (empty($paymentInfo['trans_id']) || $paymentInfo['trans_id'] == 0) {
                        return response()->json(array('response' => 88, 'responsetext' => "DB Communication error. Wait a minute and try again!"));
                    }
                    if (isset($paymentInfo['customfield'])) {
                        $obj_customfield->addTransaction($paymentInfo['trans_id'], $paymentInfo['customfield'], $paymentInfo['id_property'], $paymentInfo['web_user_id']);
                    }
                    if ($profile->type == 'ec' || $profile->type == 'eterm-ec') {
                        $credential->payment_method = $profile->type;
                        $response = $obj_paymentProcessor->runToken($paymentInfo, $credential);
                        $response['txid'] = $paymentInfo['trans_id'];
                        //send email
                        $obj_mail->PaymentReceipt($response, $paymentInfo);

                        //update
                        $this->updatePayment($paymentInfo['trans_id'], $response);

                        //save trans_data only ec
                        if (isset($paymentInfo['trans_id']) && isset($paymentInfo['profile_id'])) {
                            $obj_transactions->SetTransData($paymentInfo['trans_id'], $paymentInfo['profile_id']);
                        }
                    } else {
                        if (isset($paymentInfo['profile_id'])) {

                            //for first time swipe payment with track2 data for nmi gateway:AJ - Accepted Payment
                            if (isset($paymentInfo['type']) && $paymentInfo['type'] == 'swipe' && !empty($paymentInfo['Track2Data'])) {
                                $response = $obj_paymentProcessor->RunSwipe($paymentInfo, $credential);
                            } else {
                                //for swipe save as credit card for nmi gateway:AJ - Accepted Payment
                                if (isset($paymentInfo['type']) && $paymentInfo['type'] == 'swipe') {
                                    $paymentInfo['type'] == 'eterm-cc';
                                }

                                $response = $obj_paymentProcessor->runToken($paymentInfo, $credential);
                            }
                        } else {
                            //for first time swipe payment without saving for future use for nmi gateway:AJ - Accepted Payment
                            if (!empty($paymentInfo['Track2Data'])) {
                                $paymentInfo['Track2Data'] = $paymentInfo['Track2Data'];
                                $response = $obj_paymentProcessor->RunSwipe($paymentInfo, $credential);
                            } else {
                                $response = $obj_paymentProcessor->RunTx($paymentInfo, $credential);
                            }
                        }

                        $response['txid'] = $paymentInfo['trans_id'];
                        //send email
                        $obj_mail->PaymentReceipt($response, $paymentInfo);

                        //For swipe update payment for nmi gateway:AJ - Accepted Payment
                        if (!empty($paymentInfo['Track2Data'])) {
                            $this->updatePayment($paymentInfo['trans_id'], $response, 1);
                        } else {
                            $this->updatePayment($paymentInfo['trans_id'], $response);
                        }
                    }
                    break;
                case 'express':
                    if ($profile->type == 'amex' || $profile->type == 'am' || (isset($paymentInfo['cctype']) && $paymentInfo['cctype'] == 'AmericanExpress')) {
                        $paymentInfo['trans_id'] = $this->runPayment($paymentInfo, $credential->mid);
                        if (empty($paymentInfo['trans_id']) || $paymentInfo['trans_id'] == 0) {
                            return response()->json(array('response' => 88, 'responsetext' => "DB Communication error. Wait a minute and try again!"));
                        }
                        if (isset($paymentInfo['customfield'])) {
                            $obj_customfield->addTransaction($paymentInfo['trans_id'], $paymentInfo['customfield'], $paymentInfo['id_property'], $paymentInfo['web_user_id']);
                        }
                        $paymentInfo['total_amount'] = $paymentInfo['net_amount'];
                        if (isset($paymentInfo['profile_id'])) {
                            $response = $obj_paymentProcessor->runToken($paymentInfo, $credential);
                        } else {
                            if (!empty($paymentInfo['Track2Data'])) {
                                $paymentInfo['Track2Data'] = $paymentInfo['Track2Data'];
                                $response = $obj_paymentProcessor->RunSwipe($paymentInfo, $credential);
                            } else {
                                $response = $obj_paymentProcessor->RunTx($paymentInfo, $credential);
                            }
                        }
                        $response['txid'] = $paymentInfo['trans_id'];
                        //send email
                        $obj_mail->PaymentReceipt($response, $paymentInfo);
                        //update
                        $this->updatePayment($paymentInfo['trans_id'], $response);
                        if ($paymentInfo['fee'] > 0 && $response['response'] == 1) { //asking if vantiv has convenience fee
                            $npaymentInfo=$paymentInfo;
                            $npaymentInfo['total_amount'] = $paymentInfo['fee'];
                            $npaymentInfo['new_net_amount'] = $paymentInfo['net_amount'];
                            $npaymentInfo['net_amount'] = $paymentInfo['fee'];
                            $npaymentInfo['fee'] = 0;
                            $npaymentInfo['parent_trans_id'] = $paymentInfo['trans_id'];
                            $npaymentInfo['trans_id'] = $this->runCfeePayment($npaymentInfo, $credential->mid);
                            $tmp_credential = $credential;
                            //$credential=array();
                            $credential->payment_method = 'am';
                            $credential->gateway = 'express';
                            $credential->isFee = true;
                            if (isset($npaymentInfo['profile_id'])) {
                                $response1 = $obj_paymentProcessor->runToken($npaymentInfo, $credential);
                            } else {
                                if (!empty($npaymentInfo['Track2Data'])) {
                                    $npaymentInfo['Track2Data'] = $npaymentInfo['Track2Data'];
                                    $response1 = $obj_paymentProcessor->RunSwipe($npaymentInfo, $credential);
                                } else {
                                    $response1 = $obj_paymentProcessor->RunTx($npaymentInfo, $credential);
                                }
                            }
                            $credential = $tmp_credential;
                            //update
                            $this->updatePayment($npaymentInfo['trans_id'], $response1);
                        }
                    } else {
                        $paymentInfo['trans_id'] = $this->runPayment($paymentInfo, $credential->mid);
                        if (empty($paymentInfo['trans_id']) || $paymentInfo['trans_id'] == 0) {
                            return response()->json(array('response' => 88, 'responsetext' => "DB Communication error. Wait a minute and try again!"));
                        }
                        if (isset($paymentInfo['customfield'])) {
                            $obj_customfield->addTransaction($paymentInfo['trans_id'], $paymentInfo['customfield'], $paymentInfo['id_property'], $paymentInfo['web_user_id']);
                        }
                        if (isset($paymentInfo['profile_id'])) {
                            //for first time swipe payment with track2 data for express gateway:AJ - Accepted Payment
                            if (isset($paymentInfo['type']) && $paymentInfo['type'] == 'swipe' && !empty($paymentInfo['Track2Data'])) {

                                $response = $obj_paymentProcessor->RunSwipe($paymentInfo, $credential);
                            }
                            //for swipe save as credit card for express gateway:AJ - Accepted Payment
                            else {

                                //for swipe save only
                                if (isset($paymentInfo['type']) && $paymentInfo['type'] == 'swipe') {
                                    $paymentInfo['type'] == 'eterm-cc';
                                }

                                $response = $obj_paymentProcessor->runToken($paymentInfo, $credential);
                            }
                        } else {
                            //for first time swipe payment without saving for future use for express gateway:AJ - Accepted Payment
                            if (!empty($paymentInfo['Track2Data'])) {
                                $paymentInfo['Track2Data'] = $paymentInfo['Track2Data'];
                                $response = $obj_paymentProcessor->RunSwipe($paymentInfo, $credential);
                            } else {
                                $response = $obj_paymentProcessor->RunTx($paymentInfo, $credential);
                            }
                        }
                        $response['txid'] = $paymentInfo['trans_id'];
                        //send email
                        $obj_mail->PaymentReceipt($response, $paymentInfo);
                        //update
                        //For swipe update payment for express gateway:AJ - Accepted Payment
                        if (!empty($paymentInfo['Track2Data'])) {
                            $this->updatePayment($paymentInfo['trans_id'], $response, 1);
                        } else {
                            $this->updatePayment($paymentInfo['trans_id'], $response);
                        }
                    }
                    break;
            }
            if ($response["response"] == 1) { //update balance
                $obj_cat = new Categories();
                $obj_cat->UpdateBalance($paymentInfo);
                $response['fee'] = $conv_fee['CFEE'];
                $response['total_amount'] = $conv_fee['CFEE'] + $net_amount;
            }

            //Update cfee without phone fee
            if(isset($paymentInfo['xphonefee'])){
                $paymentInfo['fee'] = $conv_fee['CFEE'];
                $paymentInfo['total_amount'] = $conv_fee['CFEE'] + $net_amount;
                $paymentInfo['xphonefee'] = 0;
            }

        } elseif (!isset($paymentInfo['freq'])) { //one time in the future
            if (date("Y-m-d", strtotime($paymentInfo['start_date'])) < date("Y-m-t", strtotime($paymentInfo['start_date']))) {
                $paymentInfo['eomonth'] = 0;
            } else {
                $paymentInfo['eomonth'] = 1;
            }
            $paymentInfo['trans_next_post_date'] = $paymentInfo['start_date'];
            $paymentInfo['freq'] = 'onetime';
            $paymentInfo['numleft'] = 1;
            $paymentInfo['dynamic'] = 0;
            if ($profile->type == 'ec') {
                $response['txid'] = $this->runECautopay($paymentInfo, $credential->key);
                $response['auto'] = 1;
            } else {
                if ($obj_properties->isInactiveVault($idproperty, 0)) {
                    return response()->json(array('response' => 0, 'responsetext' => "Error! Cannot Schedule Payment on the Future. Please contact your Payment Provider."));
                }
                $response['txid'] = $this->runCCautopay($paymentInfo, $credential->key);
                $response['auto'] = 1;
            }
            \App\Providers\RevoPayAuditLogger::autopaymentCreate('admin', array('operation' => 'create autopayment onetime ' . $response['txid'], 'date' => $paymentInfo['trans_next_post_date']), 'M', $idproperty, \App\Model\WebUsers::getAuditData($web_user_id), Auth::user());
            unset($paymentInfo['freq']);
            $obj_user->enableEbill($web_user_id);
            $response['responsetext'] = 'Payment schedule success';
            $response['response'] = 1;
        }

        if (date("Y-m-d") == $paymentInfo['start_date'] && isset($paymentInfo['freq'])) { //made already a payment and now schedule a recurring in the future
            if (date("Y-m-d", strtotime($paymentInfo['start_date'])) < date("Y-m-t", strtotime($paymentInfo['start_date']))) {
                $paymentInfo['eomonth'] = 0;
            } else {
                $paymentInfo['eomonth'] = 1;
            }
            if ($response['response'] == 1) {
                if (isset($paymentInfo['new_net_amount']) && $paymentInfo['new_net_amount'] > 0) {
                    $paymentInfo['fee'] = $paymentInfo['net_amount'];
                    $paymentInfo['net_amount'] = $paymentInfo['new_net_amount'];
                }
                $paymentInfo['end_date'] = $obj_transactions->getformatenddate($paymentInfo['start_date'], $paymentInfo['end_date']);
                $paymentInfo['trans_next_post_date'] = $this->getNextpostdate($paymentInfo['freq'], $paymentInfo['start_date'], $paymentInfo['end_date']);
                $numlft = $obj_transactions->getNumleft($paymentInfo['freq'], $paymentInfo['start_date'], $paymentInfo['end_date']);
                if ($numlft > 0)
                    $numlft--;

                if ($numlft == 0) { //Never numleft is gonna be 0. This is new request when user is putting by mistake a wrong enddate (biannully issue)
                    $numlft++;
                }
                $paymentInfo['numleft'] = $numlft;
                if ($numlft > 0) {
                    if ($profile->type == 'ec') {
                        $response['txid'] = $this->runECautopay($paymentInfo, $credential->key);
                        $response['auto'] = 1;
                    } else {
                        $response['txid'] = $this->runCCautopay($paymentInfo, $credential->key);
                        $response['auto'] = 1;
                    }
                    if (isset($paymentInfo['trans_id']) || !empty($paymentInfo['trans_id'])) {
                        $response['trans_id'] = $paymentInfo['trans_id'];
                        $response['auto'] = 2;
                    }
                } else {
                    $response['auto'] = 0;
                }
                \App\Providers\RevoPayAuditLogger::autopaymentCreate('admin', array('operation' => 'create autopayment ' . $paymentInfo['freq'] . ' ' . $response['trans_id'], 'date' => $paymentInfo['trans_next_post_date']), 'M', $idproperty, \App\Model\WebUsers::getAuditData($web_user_id), Auth::user());
                $obj_user->enableEbill($web_user_id);
                $response['responsetext'] = 'Payment Success, RecurringSuccess';
            }
        } elseif (date("Y-m-d") != $paymentInfo['start_date'] && isset($paymentInfo['freq'])) {//normal recurring payment
            if (date("Y-m-d", strtotime($paymentInfo['start_date'])) < date("Y-m-t", strtotime($paymentInfo['start_date']))) {
                $paymentInfo['eomonth'] = 0;
            } else {
                $paymentInfo['eomonth'] = 1;
            }
            $paymentInfo['end_date'] = $obj_transactions->getformatenddate($paymentInfo['start_date'], $paymentInfo['end_date']);
            $paymentInfo['trans_next_post_date'] = date($paymentInfo['start_date']);

            $numlft = $obj_transactions->getNumleft($paymentInfo['freq'], $paymentInfo['start_date'], $paymentInfo['end_date']);
            if ($numlft == 0) { //Never numleft is gonna be 0. This is new request when user is putting by mistake a wrong enddate (biannully issue)
                $numlft++;
            }
            $paymentInfo['numleft'] = $numlft;
            if ($profile->type == 'ec') {
                $response['txid'] = $this->runECautopay($paymentInfo, $credential->key);
                $response['auto'] = 1;
            } else {
                $response['txid'] = $this->runCCautopay($paymentInfo, $credential->key);
                $response['auto'] = 1;
            }
            \App\Providers\RevoPayAuditLogger::autopaymentCreate('admin', array('operation' => 'create autopayment ' . $paymentInfo['freq'] . ' ' . $response['txid'], 'date' => $paymentInfo['trans_next_post_date']), 'M', $idproperty, \App\Model\WebUsers::getAuditData($web_user_id), Auth::user());
            $obj_user->enableEbill($web_user_id);
            $response['responsetext'] = 'Recurring Success';
            $response['responsetext'] = 'Recurring Success';
            $response['response'] = 1;
            //send email
            $obj_mail->ScheduleReceipt($response, $paymentInfo);
        }
        if ($paymentInfo['trans_type'] == 1) {
            if (!isset($response['auto'])) {
                $response['auto'] = 1;
            }
        }

//        //ask attemps to change status to sectoken
//        if($response['response']!=1){
//            $obj_sectoken->addAttemps($token);
//        }

        if (isset($paymentInfo['orderid']) && !empty($paymentInfo['orderid'])) {
            $response['orderid'] = $paymentInfo['orderid'];
        }




        if (isset($profile->wallet) && strtolower($profile->wallet) == 'mp' && isset($profile->data) && $profile->data) {
            $data_profile_array = json_decode($profile->data, 1);
            if (isset($data_profile_array['oauth_verifier'])) {
                if ($response['response'] == 1) {
                    // sending postback to masterpass
                    try {
                        $objMasterpassController = new MasterpassController();
                        $objMasterpassController->masterpassPostback($data_profile_array, $response);
                    } catch (Exception $e) {
                        error_log('failed postback -> ' . print_f($response, true) . "\r\n", 3, '/var/tmp/mppostback.log');
                    }
                }
            }
        }



        if ($paymentInfo['source'] != 'qpay') {
            //asking for promotions if payment is approved
            if ($response['response'] == 1) {
                //verify post_url
                if (isset($paymentInfo['post_url']) && $paymentInfo['post_url'] != "") {
                    $posturl = base64_decode(str_replace(array('-', '_'), array('+', '/'), $paymentInfo['post_url']));
                    if (!empty($posturl)) {
                        $postdata = array();
                        $postdata['txid'] = $response['txid'];
                        $postdata['auto'] = 0;
                        if (isset($response['auto'])) {
                            $postdata['auto'] = $response['auto'];
                        }
                        $postdata['amount'] = $paymentInfo['net_amount'];
                        if (isset($response['authcode'])) {
                            $postdata['auth'] = $response['authcode'];
                        }
                        $postdata['account_number'] = $obj_user->get1UserInfo($web_user_id, 'account_number');
                        $postdata['paypointID'] = $obj_properties->get1PropertyInfo($idproperty, 'compositeID_clients');
                        $obj_post = new \App\CustomClass\UtilControl();
                        $pdata = array('result' => json_encode($postdata));
                        $obj_post->sendPostGetJSON($posturl, $pdata);
                    }
                }
                //which promotion applied for this merchant and user
                $obj_promo = new Promotions($web_user_id, $idproperty);
                if (!$obj_promo->getClasifyToPromo()) {
                    $promo_id = $obj_promo->getType();
                    if ($promo_id > 0) {
                        $obj_promo->getSetCodeAvailable($response['txid']);
                    }
                }
            }
            /*
              if($response['response'] == 1){
              RevoPayAuditLogger::paymentSuccess('admin', array('operation' => 'Payment made', 'data' => $response), 'M', $idproperty, WebUsers::getAuditData($web_user_id), Auth::user());
              }
              else{
              RevoPayAuditLogger::paymentFailure('admin', array('operation' => 'Payment failure', 'data' => $response), 'M', $idproperty, WebUsers::getAuditData($web_user_id), Auth::user());
              }
             */

            return response()->json($response);
        } else {
            /*
              if($response['response'] == 1){
              RevoPayAuditLogger::paymentSuccess('admin', array('operation' => 'Payment made', 'data' => $response, 'info'=>'qpay'), 'M', $idproperty, WebUsers::getAuditData($web_user_id), Auth::user());
              }
              else{
              RevoPayAuditLogger::paymentFailure('admin', array('operation' => 'Payment failure', 'data' => $response, 'info'=>'qpay'), 'M', $idproperty, WebUsers::getAuditData($web_user_id), Auth::user());
              }
             */
            return response()->json($response);
        }
    }

    function calculateAmount($categories) {
        $total = 0;
        for ($i = 0; $i < count($categories); $i++) {
            if (!isset($categories[$i]['qty'])) {
                $total += $categories[$i]['amount'];
            } else {
                $total += $categories[$i]['amount'] * $categories[$i]['qty'];
            }
        }
        return $total;
    }

    function getPayment_descr($categories, $cfee, $memo, $invnumber, $paymentInfo) {
        $detail = '';
        $phonefee = 0;
        if (!empty($memo)) {
            $detail .= 'Memo- ' . $memo . "\n";
        }
        $detail .= 'Payment Details:' . "\n";
        if (!empty($invnumber)) {
            $detail .= 'Invoice #:' . $invnumber . "\n";
        }
        $total = 0;
        for ($i = 0; $i < count($categories); $i++) {
            if ($categories[$i]['amount'] > 0.00) {
                if (!isset($categories[$i]['qty'])) {
                    $categories[$i]['qty'] = 1;
                }
                $total += $categories[$i]['amount'] * $categories[$i]['qty'];
                $detail .= $categories[$i]['name'] . ':' . $categories[$i]['qty'] . ' x $' . number_format($categories[$i]['amount'], 2, '.', ',') . "\n";
            }
        }

        if (!empty($cfee) && $cfee > 0) {
            $detail .= 'Convenience Fee: $' . number_format($cfee, 2);
            $detail .= "\n";
        }
        if (isset($paymentInfo['xphonefee']) && floatval($paymentInfo['xphonefee']) > 0) {
            $detail .= 'Phone Fee: $' . number_format(floatval($paymentInfo['xphonefee']), 2);
            $detail .= "\n";
            $phonefee = floatval($paymentInfo['xphonefee']);
        }
        $detail .= '---------------------' . "\n";
        $detail .= 'Total Payment: $' . number_format($cfee + $total + $phonefee, 2, '.', ',');

        return $detail;
    }

    function runPayment($paymentInfo, $key) {

        $obj_transaction = new Transations();
        $obj_eter_setting = new EterminalSettings();

        $trans_id = $obj_transaction->addTransaction($paymentInfo, $key);

        if (!isset($paymentInfo['inv_id']) || empty($paymentInfo['inv_number'])) {
            if (!isset($paymentInfo['dynamic']) || $paymentInfo['dynamic'] == 0) {
                //add tras_categories
                $obj_transaction->addTransCategories($paymentInfo['categories'], $trans_id, $paymentInfo['id_property'], $paymentInfo['web_user_id']);
            }
        }
        if(isset($paymentInfo['xphonefee']) && isset($paymentInfo['xwalkin'])){
            $obj_eter_setting->insertTransactionsPhoneFee($trans_id,floatval($paymentInfo['xphonefee']),$paymentInfo['xwalkin'],0);
        }
        return $trans_id;
    }

    function updatePayment($trans_id, $response, $swipe = 0) {
        $obj_transaction = new Transations();
        $trans_id = $obj_transaction->updatePayment($trans_id, $response, $swipe);
    }

    function runCfeePayment($paymentInfo, $key) {
        $obj_transaction = new Transations();
        $trans_id = $obj_transaction->addCfeeTransaction($paymentInfo, $key);
        return $trans_id;
    }

    function getCardInfo($token, $type, $name) {
        $token = json_decode($token, true);
        $result = array();
        $numbers = str_replace("X", "", $name);
        $numbers = str_replace("x", "", $numbers);
        $numbers = str_replace("-", "", $numbers);
        $numbers = str_replace(" ", "", $numbers);

        if ($type == 'ec') {
            $result['payment_type'] = 'ec';
            $result['card_type'] = $token['ec_checking_savings'] . ' (' . $numbers . ')';
        } elseif ($type == 'swipe') {
            $result['payment_type'] = 'swipe';
            $result['card_type'] = $token['cc_type'] . ' (' . $numbers . ')';
        } else {
            if ($type == 'am' || $type == 'amex') {
                $result['payment_type'] = 'amex';
            } else {
                $result['payment_type'] = 'cc';
            }
            $result['card_type'] = $token['cc_type'] . ' (' . $numbers . ')';
        }
        return $result;
    }

    function receipt_eterm($trans_id, $token, Request $request, $auto = -1, $txid = "") {
        $atoken = \Illuminate\Support\Facades\Crypt::decrypt($token);
        //old admin
        list($idproperty, $web_user_id, $time, $apikey) = explode('|', $atoken);
        $ntoken = encrypt(['level' => 'M', 'id' => $idproperty, 'level_id' => $idproperty]);
        //new admin
        /* list($datatoken)=explode('|',Crypt::decrypt($token));
          $array_token = json_decode($datatoken,1);
          $idproperty = $array_token['id'];
          $web_user_id = $array_token['web_user_id'];
          unset($array_token['web_user_id']);
          $atoken = Crypt::encrypt(json_encode($array_token).'|'.time().'|'.config('app.appAPIkey'));
         */

        //old admin
        if (($time + 3600) <= time()) {
            return response()->json(array('response' => 260, 'responsetext' => 'Invalid Token'));
        }

        if ($idproperty <= 0) {
            return response()->json(array('response' => 261, 'responsetext' => 'Invalid Token'));
        }
        //old admin
        if ($apikey != config('app.appAPIkey')) {
            return response()->json(array('response' => 261, 'responsetext' => 'Invalid Token'));
        }
        $obj_property = new Properties();
        $merchant = $obj_property->getPropertyInfo($idproperty);

        if ($idproperty != $merchant['id']) {
            return response()->json(array('response' => 261, 'responsetext' => 'Invalid Token'));
        }

        $idcompany = $merchant['id_companies'];
        $idpartner = $merchant['id_partners'];
        $setting_ftime = $obj_property->getLoginSetting($idproperty, $idcompany, $idpartner);
        $setting_qp = $obj_property->getQPaySetting($idproperty, $idcompany, $idpartner);
        $regsetting = $obj_property->getPropertySettings($idproperty, $idcompany, $idpartner, 'NOTREG');
        $base_parts = explode('/', $request->url());
        array_pop($base_parts);
        array_pop($base_parts);
        array_pop($base_parts);
        if ($auto >= 0) {
            array_pop($base_parts);
        }
        if (!empty($txid)) {
            array_pop($base_parts);
        }
        if ($auto < 0) {
            $auto = 0;
        }
        $base_url = implode('/', $base_parts);
        $data = array('pageTitle' => 'Receipt', 'merchant' => $merchant, 'setting_ftime' => $setting_ftime, 'setting_qp' => $setting_qp, 'base_url' => $base_url, 'isqpay' => 0, 'regsetting' => $regsetting);

        //api route
//        if ($request->secure()) {
//            $hostname = "https://" . $_SERVER['SERVER_NAME'];
//        } else {
//            $hostname = "http://" . $_SERVER['SERVER_NAME'];
//        }
//        $base_api = $hostname . '/master/index.php/api2';
//        $data['base_api'] = $base_api;
        $obj_user = new User();
        //usr info
        $usr = $obj_user->getUsrInfo($web_user_id);
        $usr = (array) $usr;
        $usr['web_user_id'] = $web_user_id;
        $data['usr'] = $usr;
        $data['auto'] = $auto;
        $data['managernotapproval'] = $obj_property->getPropertySettings($idproperty, $idcompany, $idpartner, 'manager_approval_not_required');

        //get trans info
        $obj_trans = new Transations();

        if ($auto == 1 || $auto == 2) {
            $tx = $obj_trans->getRecTransBy_wuid_tid_pid($trans_id, $idproperty, $web_user_id);
            $tx = (array) $tx;
            $tx['autopayInfo'] = $obj_trans->getAutopayByTransId($web_user_id, $trans_id);
            if ($tx['dynamic'] == 1) {
                $tx['trans_total_amount'] = 'Balance Owed';
                $tx['trans_net_amount'] = 0.00;
                $infoprofile = $obj_user->getPaymentProfileById($web_user_id, $tx['profile_id']);
                $infoprofile = (array) $infoprofile;
                $credfix = $obj_property->getCredentialtype_isrecurring("eterm-" . $infoprofile['type'], $idproperty, 1);
                $credfix = $credfix[0];
                $credfix = (array) $credfix;
                $tx['trans_convenience_fee'] = '$'.number_format(0,2);

             /*   if ($credfix['convenience_fee_drp'] > 0) {
                    $tx['trans_convenience_fee'] = '$' . $credfix['convenience_fee_drp'];
                }

                if ($credfix['convenience_fee_float_drp'] > 0) {
                    if ($credfix['convenience_fee_drp'] > 0) {
                        $tx['trans_convenience_fee'] .= ' + ' . $credfix['convenience_fee_float_drp'] . '%';
                    } else {
                        $tx['trans_convenience_fee'] = $credfix['convenience_fee_float_drp'] . '%';
                    }
                }*/
            } else {
                $tx['trans_type'] = 1;
                $tx['trans_total_amount'] = '$' . number_format($tx['trans_net_amount'] + $tx['trans_convenience_fee'], 2, '.', ',');
                $tx['trans_convenience_fee'] = '$' . number_format($tx['trans_convenience_fee'], 2, '.', ',');
            }
        } else {
            $tx = $obj_trans->getTransBy_wuid_tid_pid($trans_id, $idproperty, $web_user_id);
            if (empty($tx)) {
                //verify tx in auto
                $tx = $obj_trans->getRecTransBy_wuid_tid_pid($trans_id, $idproperty, $web_user_id);
                if (!empty($tx)) {
                    $tx['trans_type'] = 1;
                    $tx['autopayInfo'] = $obj_trans->getAutopayByTransId($web_user_id, $trans_id);
                    $trans_categories = $obj_trans->getRecTransPaymentType($trans_id, $idproperty, $web_user_id);
                    $tx['trans_total_amount'] = '$' . number_format($tx['trans_net_amount'] + $tx['trans_convenience_fee'], 2, '.', ',');
                    $tx['trans_convenience_fee'] = '$' . number_format($tx['trans_convenience_fee'], 2, '.', ',');
                }
            }
            if (!isset($tx['source'])) {
                $tx['source'] = 'web';
            }
            if (!empty($tx['invoice_number'])) {
                //get Invoice Info
                $obj_inv = new Invoices();
                $invoice = $obj_inv->getInvoiceByInvoice_number($tx['invoice_number'], $idproperty, $web_user_id);
                $data['invoice'] = $invoice;
                $trans_categories = '';
            } else {
                $trans_categories = $obj_trans->getTransPaymentType($trans_id, $idproperty, $web_user_id);
            }
        }
        if (!empty($txid)) {
            $data['auth'] = $obj_trans->get1TransInfo($txid, 'trans_result_auth_code');
            $tx['trans_result_auth_code'] = $data['auth'];
        }

        $trans_categories = $obj_trans->getRecTransPaymentType($trans_id, $idproperty, $web_user_id);
        $data['transCategories'] = $trans_categories;
        $data['oneclick'] = ''; // will be enable in the future

        if (isset($tx['trans_card_type'])) {
            switch (substr(strtolower($tx['trans_card_type']), 0, 3)) {
                case 'che':
                case 'sav':
                    $tx['type_card'] = '<img src="/img/echeck.png">';
                    break;
                case 'vis':
                    $tx['type_card'] = '<img src="/img/visa.png">';
                    break;
                case 'mas':
                    $tx['type_card'] = '<img src="/img/mastercard.png">';
                    break;
                case 'dis':
                    $tx['type_card'] = '<img src="/img/discover.png">';
                    break;
                case 'ame':
                    $tx['type_card'] = '<img src="/img/american.png">';
                    break;
                default:
                    break;
            }
        }

        //get phone fee
        $objphonefee = new EterminalSettings();
        $txphonefee = $objphonefee->getPhoneFeeTransaction($trans_id);
        $tx['phone_fee'] = isset($txphonefee->phone_fee) ? $txphonefee->phone_fee : '0.00';
        $tx['phone_fee_is_recurring'] = isset($txphonefee->is_recurring) ? $txphonefee->is_recurring : -1;

        $data['tx'] = $tx;

        $data['acctitle'] = $obj_property->getTitleAccountSetting($idproperty, $merchant['id_companies'], $merchant['id_partners']);
        $data['hidemerchantinfo'] = $obj_property->getPropertySettings($idproperty, $merchant['id_companies'], $merchant['id_partners'], 'HIDEMERCHANTINFO');
        $data['chat'] = $obj_property->getPropertySettings($idproperty, $idcompany, $idpartner, 'LIVECHAT');
        $data['chatcontent'] = $obj_property->getPropertySettings($idproperty, $idcompany, $idpartner, 'LIVECHATCONTENT');
        $data['atoken'] = $atoken;
        //asking for CompanyName
        $data['showcompanyname'] = $obj_property->getPropertySettings($idproperty, $merchant['id_companies'], $merchant['id_partners'], 'SHOWCOMPANYNAME');
        return view('eterm_receipt.eterm_landingPage', ['data' => $data, 'token' => $ntoken]);
//        return redirect()->route('receiptq', array('partner' => $partner, 'subdomain' => $subdomain, 'txid' => $trans_id, 'token' => $atoken, 'atoken' => $atoken, 'auto' => 0));
    }

    public function getCCType($token, $info) {
        $ainfo = json_decode($info, true);
        $obj_property = new Properties();
        $type = $obj_property->getCardTypeFee($ainfo['ccnumber'], $ainfo['cctype']);
        return response()->json($type);
    }

    function runCCautopay($paymentInfo, $key) {
        $obj_transaction = new Transations();
        $obj_eter_setting = new EterminalSettings();
        $trans_rec_id = $obj_transaction->addCCRecurringTransaction($paymentInfo, $key);
        if (count($paymentInfo['categories']) > 0) {
            //add tras_categories
            $obj_transaction->addReccuringTransCategories($paymentInfo['categories'], $trans_rec_id, $paymentInfo['id_property'], $paymentInfo['web_user_id']);
        }
        $obj_eter_setting->insertTransactionsPhoneFee($trans_rec_id,floatval($paymentInfo['xphonefee']),$paymentInfo['xwalkin'],1);
        return $trans_rec_id;
    }

    function getNextpostdate($freq, $startdate, $enddate) {
        $start = strtotime($startdate);
        $end = strtotime($enddate);

        switch ($freq) {
            case 'weekly':
                $start = strtotime('+1 week', $start);
                break;
            case 'annually':
            case 'yearly':
                $start = strtotime('+1 year', $start);
                break;
            case 'biannually':
                $start = strtotime('+6 months', $start);
                break;
            case 'quaterly':
            case 'quarterly':
                $start = strtotime('+3 months', $start);
                break;
            case 'triannually':
                $start = strtotime('+4 months', $start);
                break;
            case 'biweekly':
                $start = strtotime('+14 days', $start);
                break;
            case 'monthly':
                $start = strtotime('+1 months', $start);
                break;
            default:
                break;
        }
        if ($end >= $start) {
            return date('Y-m-d 00:00:00', $start);
        } else { //this is new request when user is putting by mistake a wrong enddate (biannully issue)
            return date('Y-m-d 00:00:00', $start);
        }
        return $startdate;
    }

    public function transdetail($token, $trans_id, Request $request) {
        $obj_trans = new Transations();
        $atoken = decrypt($token);


        /* if (($time + 60 * 20) < time()) {
          //return response()->json(array('errcode'=>260,'msg'=>'Invalid Token'));
          }
          if ($web_user_id <= 0 || $property_id <= 0) {
          return response()->json(array('errcode' => 261, 'msg' => 'Invalid Token'));
          } */

        $result = $obj_trans->getTransdetailById($trans_id);
        if (empty($result)) {
            return response()->json(array('errcode' => 270, 'msg' => 'Transactions not found'));
        }
        $msg = '<br><table class="table grey">'
                . '<thead><tr>'
                . '</tr></thead>'
                . '<tbody>'
                . '<tr align="left"><td>Transactions Id:'
                . '</td><td>' . $result['trans_id'] . '</td></tr>'
                . '<tr class="active" align="left"><td>Payment Method:'
                . '</td><td>' . $result['trans_card_type'] . '</td></tr>'
                . '<tr align="left"><td>Detail:'
                . '</td><td>' . str_replace("\n", "<br>", $result['trans_descr']) . '</td></tr>'
                . '</tbody></table>';
        return response()->json(array('errcode' => 0, 'msg' => $msg));
    }

    function paymentinv($token, $info, Request $request) {
        $atoken = Decrypt($token);
        list($idproperty, $web_user_id, $time, $apikey) = explode('|', $atoken);
        $obj_mail = new Email();
        //$obj_sectoken = new SecToken();

        if (($time + 60 * 60) < time()) {
            return response()->json(array('response' => 261, 'responsetext' => 'Invalid Token1'));
        }
        if ($idproperty <= 0) {
            return response()->json(array('response' => 261, 'responsetext' => 'Invalid Token2'));
        }

        if ($web_user_id <= 0) {
            return response()->json(array('response' => 261, 'responsetext' => 'Invalid Token3'));
        }

        if ($apikey != config('app.appAPIkey')) {
            return response()->json(array('response' => 261, 'responsetext' => 'Invalid Token4'));
        }

        $paymentInfo = json_decode($info, true);
        $paymentInfo['id_property'] = $idproperty;
        $paymentInfo['web_user_id'] = $web_user_id;
        $obj_user = new User();
        $obj_properties = new Properties();

        //check net_amount is greater than total amount to pay
        $obj_inv = new Invoices();
        $invoice_amount = $obj_inv->getInvoiceByInvoice_number($paymentInfo['inv_number'], $idproperty, $web_user_id)['amount'];
        $paymentInfo['net_amount'] = $paymentInfo['net_amount'] > $invoice_amount ? $invoice_amount : $paymentInfo['net_amount'];

        //var_dump($paymentInfo['net_amount']);die;

        if (!isset($paymentInfo['profile_id']) || $paymentInfo['profile_id'] < 1) {
            if (isset($paymentInfo['ec_account_number']) && !empty($paymentInfo['ec_account_number'])) {
                //ec payment
                $ec_info = array();
                $ec_info['ec_account_holder'] = $paymentInfo['ec_account_holder'];
                $ec_info['ec_account_lholder'] = $paymentInfo['ec_account_lholder'];
                $ec_info['ec_routing_number'] = $paymentInfo['ec_routing_number'];
                $ec_info['ec_checking_savings'] = $paymentInfo['ec_checking_savings'];
                $ec_info['ec_account_number'] = $paymentInfo['ec_account_number'];
                $ecjson = json_encode($ec_info);
                $obj_users = new User();
                $profileid = $obj_users->insertECpaymethod($ecjson, $idproperty, $web_user_id);
                RevoPayAuditLogger::paymentMethodCreate('admin', array('operation' => 'Create payment method EC', 'info' => 'Save payment method not selected', 'type' => 'ec', 'profile_id' => $profileid), 'M', $idproperty, WebUsers::getAuditData($web_user_id), Auth::user());

                $paymentInfo['profile_id'] = $profileid;
                if ($paymentInfo['source'] == 'eterm') { //to use eterminal credentials
                    $paymentInfo['type_cc'] = 'eterm-ec';
                } else {
                    if ($paymentInfo['source'] == 'ivr') {//to use IVR credentials
                        $paymentInfo['type_cc'] = 'ivr-ec';
                    }
                }
            } elseif (isset($paymentInfo['ccnumber']) && !empty($paymentInfo['ccnumber'])) {
                //cc payment
                $paymentInfo['type'] = 'cc';
                if ($paymentInfo['source'] == 'eterm') { //to use eterminal credentials
                    $paymentInfo['type_cc'] = 'eterm-cc';
                } else {
                    if ($paymentInfo['source'] == 'ivr') {//to use IVR credentials
                        $paymentInfo['type_cc'] = 'ivr-cc';
                    }
                }
                switch (substr($paymentInfo['ccnumber'], 0, 1)) {
                    case 3:
                        $paymentInfo['cctype'] = 'AmericanExpress';
                        $paymentInfo['type'] = 'amex';
                        break;
                    case 4:
                        $paymentInfo['cctype'] = 'Visa';
                        break;
                    case 5:
                        $paymentInfo['cctype'] = 'MasterCard';
                        break;
                    case 6:
                        $paymentInfo['cctype'] = 'Discover';
                        break;
                    default:
                        $paymentInfo['cctype'] = 'Unknown';
                        break;
                }
            }
        }
        $decrypted_token_array = null;
        if (isset($paymentInfo['profile_id'])) {
            $profile = $obj_user->getPaymentProfileById($web_user_id, $paymentInfo['profile_id']);
            $decrypted_token = Decrypt($profile->token);
            $paymentInfo['token'] = $decrypted_token_array = json_decode($decrypted_token, true);
        }

        $credential = array();

        if (isset($paymentInfo['type_cc']) && !empty($paymentInfo['type_cc'])) {
            $pptype = $paymentInfo['type_cc'];
        } else {
            if (isset($paymentInfo['type']) && !empty($paymentInfo['type'])) {
                $pptype = $paymentInfo['type'];
            } else {
                if (isset($profile['type'])) {
                    $pptype = $profile['type'];
                }
            }
        }

        $credential = $obj_properties->getCredentialsBytype($pptype, $idproperty);


        if (isset($profile->type)) {
            $paymentInfo['card_info'] = $this->getCardInfo($decrypted_token, $profile->type, $profile->name);
        } else {
            $ccnumber = substr($paymentInfo['ccnumber'], -4);
            $paymentInfo['card_info']['card_type'] = $paymentInfo['cctype'] . " (" . $ccnumber . ")";
            $paymentInfo['card_info']['payment_type'] = $paymentInfo['type'];
        }

        //get fee and verify the velocities
        $obj_transactions = new Transations();
        if (isset($paymentInfo['card_type_fee'])) {
            $card_type_fee = $paymentInfo['card_type_fee'];
        } else {
            $card_type_fee = "";
        }

        if (isset($decrypted_token_array['card_type_fee'])) {
            $card_type_fee = $decrypted_token_array['card_type_fee'];
        }
        $conv_fee = $obj_transactions->getFee($credential, $paymentInfo['net_amount'], $card_type_fee);
        if ($conv_fee['ERROR'] == 1) {
            if ($paymentInfo['source'] != 'qpay') {
                return response()->json(array('response' => 0, 'responsetext' => $conv_fee['ERRORCODE']));
            } else {
                return array('response' => 0, 'responsetext' => $conv_fee['ERRORCODE']);
            }
        }

        if (isset($paymentInfo['xcfee']) && $paymentInfo['xcfee'] != '') {
            $conv_fee['CFEE'] = $paymentInfo['xcfee'] * 1;
        }

        $paymentInfo['fee'] = $conv_fee['CFEE'];
        $paymentInfo['total_amount'] = $conv_fee['CFEE'] + $paymentInfo['net_amount'];

        //putting a tier that is applied to this amount
        $credential = $credential[$conv_fee['TIER_APPLIED']];

        //get payment description to insert in accounting transactions
        $paymentInfo['descr'] = $this->getPayInv_descr($paymentInfo['net_amount'], $paymentInfo['fee'], $paymentInfo['memo'], $paymentInfo['inv_number']);

        //is recurring
        $paymentInfo['trans_type'] = 0;

        //setting nacha_type. parameter b2b only came from Business vertical
        $paymentInfo['nacha_type'] = 'WEB';
        if (!empty($paymentInfo['achmode'])) {
            $paymentInfo['nacha_type'] = $paymentInfo['achmode'];
        }

        $obj_paymentProcessor = new PaymentProcessor();



        switch ($credential->gateway) {
            case 'bokf':
            case 'nmi':
            case 'fd4':
            case 'fde4':
            case 'trans1':
            case 'ppal':
                $paymentInfo['trans_id'] = $this->runPayment($paymentInfo, $credential->key);
                if (empty($paymentInfo['trans_id']) || $paymentInfo['trans_id'] == 0) {
                    return response()->json(array('response' => 88, 'responsetext' => "DB Communication error. Wait a minute and try again!"));
                }

                if (isset($paymentInfo['profile_id'])) {
                    $response = $obj_paymentProcessor->runToken($paymentInfo, $credential);
                } else {
                    $response = $obj_paymentProcessor->RunTx($paymentInfo, $credential);
                }
                //update
                $this->updatePayment($paymentInfo['trans_id'], $response);
                $this->updateInvPayment($paymentInfo['trans_id'], $response, $paymentInfo['inv_id'], $paymentInfo['net_amount']);

                //save trans_data only ec
                if (isset($paymentInfo['trans_id']) && isset($paymentInfo['profile_id'])) {
                    $obj_transactions->SetTransData($paymentInfo['trans_id'], $paymentInfo['profile_id']);
                }

                $obj_mail->PaymentReceipt($response, $paymentInfo);
                break;
            case 'profistars':
                //run frist payment (net amount)
                $paymentInfo['trans_id'] = $this->runPayment($paymentInfo, $credential->key);
                if (empty($paymentInfo['trans_id']) || $paymentInfo['trans_id'] == 0) {
                    return response()->json(array('response' => 88, 'responsetext' => "DB Communication error. Wait a minute and try again!"));
                }
                $paymentInfo['total_amount'] = $paymentInfo['net_amount'];
                $response = $obj_paymentProcessor->runToken($paymentInfo, $credential);
                //update
                $this->updatePayment($paymentInfo['trans_id'], $response);
                $this->updateInvPayment($paymentInfo['trans_id'], $response, $paymentInfo['inv_id'], $paymentInfo['net_amount']);
                //save trans_data only ec
                if (isset($paymentInfo['trans_id']) && isset($paymentInfo['profile_id'])) {
                    $obj_transactions->SetTransData($paymentInfo['trans_id'], $paymentInfo['profile_id']);
                }
                //send email
                $obj_mail->PaymentReceipt($response, $paymentInfo);
                if ($paymentInfo['fee'] > 0 && $response['response'] == 1) {
                    //run secund payment (convenience fee)
                    $credential->key = 'revo key';
                    $paymentInfo['total_amount'] = $paymentInfo['fee'];
                    $paymentInfo['new_net_amount'] = $paymentInfo['net_amount'];
                    $paymentInfo['net_amount'] = $paymentInfo['fee'];
                    $paymentInfo['fee'] = 0;
                    $paymentInfo['parent_trans_id'] = $paymentInfo['trans_id'];
                    $paymentInfo['trans_id'] = $this->runCfeePayment($paymentInfo, $credential->key);
                    $tmp_credential = $credential;
                    unset($credential);
                    $credential['gateway'] = 'profistars';
                    $response1 = $obj_paymentProcessor->runToken($paymentInfo, $credential);
                    //update
                    $this->updatePayment($paymentInfo['trans_id'], $response1);
                }
                break;
            case 'express':
                if (isset($profile) && ($profile->type == 'amex' || $profile->type == 'am')) {
                    //run frist payment if card type is American Express(net amount)
                    $paymentInfo['total_amount'] = $paymentInfo['net_amount'];
                    $paymentInfo['trans_id'] = $this->runPayment($paymentInfo, $credential->key);
                    if (empty($paymentInfo['trans_id']) || $paymentInfo['trans_id'] == 0) {
                        return response()->json(array('response' => 88, 'responsetext' => "DB Communication error. Wait a minute and try again!"));
                    }
                    $response = $obj_paymentProcessor->runToken($paymentInfo, $credential);
                    //update
                    $this->updatePayment($paymentInfo['trans_id'], $response);
                    $this->updateInvPayment($paymentInfo['trans_id'], $response, $paymentInfo['inv_id'], $paymentInfo['net_amount']);
                    //send email
                    $obj_mail->PaymentReceipt($response, $paymentInfo);
                    //run secund payment (convenience fee)
                    if ($paymentInfo['fee'] > 0 && $response['response'] == 1) {
                        $credential->key = 'revo key';
                        $paymentInfo['total_amount'] = $paymentInfo['fee'];
                        $paymentInfo['new_net_amount'] = $paymentInfo['net_amount'];
                        $paymentInfo['net_amount'] = $paymentInfo['fee'];
                        $paymentInfo['fee'] = 0;
                        $paymentInfo['parent_trans_id'] = $paymentInfo['trans_id'];
                        $paymentInfo['trans_id'] = $this->runCfeePayment($paymentInfo, $credential->key);
                        $tmp_credential = $credential;
                        $credential = array();
                        $credential['payment_method'] = 'am';
                        $credential['gateway'] = 'express';
                        $credential['isFee'] = true;
                        if (isset($paymentInfo['profile_id'])) {
                            $response1 = $obj_paymentProcessor->runToken($paymentInfo, $credential);
                        } else {
                            $response1 = $obj_paymentProcessor->RunTx($paymentInfo, $credential);
                        }
                        //update
                        $this->updatePayment($paymentInfo['trans_id'], $response1);
                    }
                } else {
                    $paymentInfo['trans_id'] = $this->runPayment($paymentInfo, $credential->key);
                    if (empty($paymentInfo['trans_id']) || $paymentInfo['trans_id'] == 0) {
                        return response()->json(array('response' => 88, 'responsetext' => "DB Communication error. Wait a minute and try again!"));
                    }

                    if (isset($paymentInfo['profile_id'])) {
                        $response = $obj_paymentProcessor->runToken($paymentInfo, $credential);
                    } else {
                        $response = $obj_paymentProcessor->RunTx($paymentInfo, $credential);
                    }
                    //update
                    $this->updatePayment($paymentInfo['trans_id'], $response);
                    $this->updateInvPayment($paymentInfo['trans_id'], $response, $paymentInfo['inv_id'], $paymentInfo['net_amount']);
                    $obj_mail->PaymentReceipt($response, $paymentInfo);
                }
                break;
        }

//        //ask attemps to change status to sectoken
//        if($response['response']!=1){
//            $obj_sectoken->addAttemps($token);
//        }
        if ($response['response'] == 1) {
            $response['fee'] = $conv_fee['CFEE'];
            $response['total_amount'] = $conv_fee['CFEE'] + $paymentInfo['net_amount'];
        }
        if ($response['response'] == 1 && isset($paymentInfo['autopayinv']) && $paymentInfo['autopayinv']) {
            $obj_user->setupAutoPayInv($idproperty, $web_user_id, $paymentInfo['inv_id'], $paymentInfo['profile_id']);
        }

        if ($paymentInfo['source'] != 'qpay') {
            //verify post_url
            if ($response['response'] == 1 && isset($paymentInfo['post_url']) && $paymentInfo['post_url'] != "") {
                $posturl = base64_decode(str_replace(array('-', '_'), array('+', '/'), $paymentInfo['post_url']));
                if (!empty($posturl)) {
                    $postdata = array();
                    $postdata['txid'] = $response['txid'];
                    $postdata['auto'] = 0;
                    $postdata['amount'] = $paymentInfo['net_amount'];
                    if (isset($response['authcode'])) {
                        $postdata['auth'] = $response['authcode'];
                    }
                    $postdata['account_number'] = $obj_user->get1UserInfo($web_user_id, 'account_number');
                    $postdata['invoice_number'] = $paymentInfo['inv_number'];
                    $postdata['paypointID'] = $obj_properties->get1PropertyInfo($idproperty, 'compositeID_clients');
                    $obj_post = new \App\CustomClass\UtilControl();
                    $pdata = array('result' => json_encode($postdata));
                    $obj_post->sendPostGetJSON($posturl, $pdata);
                }
            }
            return response()->json($response);
        } else {
            return $response;
        }
    }

    function getPayInv_descr($amount, $cfee, $memo, $inv_num) {
        $detail = '';
        if (!empty($memo)) {
            $detail .= 'Memo- ' . $memo . "\n";
        }
        $detail .= 'Payment Details:' . "\n";
        $detail .= "Invoice #" . ': ' . $inv_num . "\n";
        $detail .= "Amount" . ': $' . number_format($amount, 2, '.', ',') . "\n";


        if (!empty($cfee) && $cfee > 0) {
            $detail .= 'Convenience Fee: $' . number_format($cfee, 2);
        }
        $detail .= "\n" . '---------------------' . "\n";
        $detail .= 'Total Payment: $' . number_format($cfee + $amount, 2, '.', ',');

        return $detail;
    }

    function updateInvPayment($trans_id, $response, $inv_id, $amount) {
        $obj_transaction = new Transations();
        $trans_id = $obj_transaction->updatePaymentInv($trans_id, $response, $inv_id, $amount);
    }

    public function ssoInvoice($inv_number, $atoken) {
        $dataarray = decrypt($atoken);
        $idproperty = $dataarray['level_id'];
        $level = $dataarray['level'];

        if (empty($level) || $level != "M" || empty($idproperty) || $idproperty <= 0) {
            return redirect(route('accessdenied'));
        }

        $ntoken = encrypt(['level' => $level, 'level_id' => $idproperty, 'id' => $idproperty]);
        $newtoken = encrypt($idproperty . '|' . $level . '|' . time() . '|' . config('app.appAPIkey'));

        $data = array();
        $data['atoken'] = $newtoken;
        $data["pageTitle"] = "e-Terminal";

        if ($idproperty <= 0) {
            //@TODO invalid property ID
        }


        $obj_user = new User();
        $obj_inv = new \App\Model\Invoices();

        $obj_property = new Properties();

        $ids = $obj_property->getOnlyIds($idproperty);

        $accsetting = $obj_property->getPropertySettings($idproperty, $ids['id_companies'], $ids['id_partners'], 'ACCSETTING');
        $invsetting = $obj_property->getPropertySettings($idproperty, $ids['id_companies'], $ids['id_partners'], 'INVSETTING');
        $data['accsetting'] = $accsetting;
        $data['invsetting'] = $invsetting;

        $data['einvsetting'] = $obj_property->getPropertySettings($idproperty, $ids['id_companies'], $ids['id_partners'], 'EINVOICE');

        //fields not mandatory for new user in eterminal
        $notmandatorynewuser = $obj_property->getPropertySettings($idproperty, $ids['id_companies'], $ids['id_partners'], 'NOTMANDATORYETERM');
        $data['notmandatorynewuser'] = explode('|', $notmandatorynewuser);

        $data['invlabel'] = $obj_property->getPropertySettings($idproperty, $ids['id_companies'], $ids['id_partners'], 'INVLABEL');
        $data['group'] = $obj_property->getCompanyInfoMinimal($ids['id_companies']);

        //SETTINGS CUSTOM CSS
        $data['custom_css_file'] = $obj_property->getPropertySettings($idproperty, $ids['id_companies'], $ids['id_partners'], 'CUSTOM_STYLESHEET');

        //Setting to show diferent services to pay in eterminal with different fee
        $eservices = $obj_property->getPropertySettings($idproperty, $ids['id_companies'], $ids['id_partners'], 'ETERMSERV');
        $data['eservices'] = $eservices;

        //get the services to show (type and description)
        if (isset($eservices) && $eservices == 1) {
            $eterm_services = $obj_property->getServicesTypeByProperty($idproperty);
            $data['eterm_services'] = $eterm_services;
        }

        //get onetime credential with services fee
        $data['credOneTime'] = $obj_property->getcredOneTimeCredentials($idproperty, "eterm-");

        //get recurring hight ticket and lower ticoodket
        $data['velocityOt'] = $obj_property->getHight_LowerTicket($data['credOneTime']);
        //get recurring credential
        $data['credRecurring'] = $obj_property->getcredRecurringCredentials($idproperty, "eterm-");
        //get recurring hight ticket and lower ticket
        $data['velocityRc'] = $obj_property->getHight_LowerTicket($data['credRecurring']);

        $novault = $obj_property->isInactiveVault($idproperty, 0);
        $data['novault'] = $novault;

        //get layout
        $id_layout = $obj_property->getLayoutID($idproperty);
        $labels = $obj_property->getLabels_Layout($id_layout);
        $data['id_layout'] = $id_layout;
        $data['layout'] = $labels;

        $data['merchant'] = $obj_property->getPropertyInfo($idproperty);
        $profiles = array();
        $data['xurl'] = "https://" . $_SERVER['SERVER_NAME'];
        if (empty($inv_number)) {
            //@TODO error invalid invoice
        }

        $obj_inv = new Invoices();
        $obj_property = new Properties();
        $obj_user = new User();

        $usrlist = $obj_inv->getUserByInvID($inv_number, $idproperty);
        if (empty($usrlist)) {
            //@TODO error invoice without user
            return redirect()->back()->withErrors(['Invoice without user - Please review your information and try again']);
        }

        $ids = $obj_property->getOnlyIds($idproperty);
        $invtitle = $obj_property->getPropertySettings($idproperty, $ids['id_companies'], $ids['id_partners'], 'INVNUMBER');
        $acctitle = $obj_property->getPropertySettings($idproperty, $ids['id_companies'], $ids['id_partners'], 'PAYMENT_NUMBER_REG_NUMBER');
        $datainv['invtitle'] = !$invtitle ? $invtitle : 'Invoice #';
        $datainv['acctitle'] = !$acctitle ? $acctitle : 'Account #';

        $datainv['merchant'] = $obj_property->getPropertyInfo($idproperty);
        $datainv['usr'] = $usrlist;
        $datainv['invoice'] = $obj_inv->getInvoiceByInvoiceID($inv_number, $idproperty);

        $active_inv = array('open', 'sent', 'paid');
        if (!in_array($datainv['invoice']['status'], $active_inv)) {
            // error invoice with wrong status
        }
        $discount = $obj_inv->getInvoiceDiscount($datainv['invoice']['id'], $idproperty, $datainv['invoice']['amount'], $datainv['invoice']['invoice_date']);
        $datainv['discount'] = $discount;

        $infodicount = $obj_inv->getDiscountByInvoice($datainv['invoice']['id'], $idproperty, $datainv['invoice']['amount'], $datainv['invoice']['invoice_date']);
        $datainv['infodiscount'] = $infodicount;

        if ($datainv['invoice']['paid'] > 0) {
            $datainv['invoice']['topay'] = $datainv['invoice']['amount'] - $datainv['invoice']['paid'];
            $datainv['infodiscount']['discount'] = 0;
        } else {
            $datainv['invoice']['topay'] = $datainv['invoice']['amount'];
        }

        if ($datainv['infodiscount']['discount'] > 0) {
            $datainv['invoice']['topay'] = $datainv['infodiscount']['discountamount'];
        }
        //invoice info
        $items = $obj_inv->getInvoiceItems($datainv['invoice']['id'], $idproperty);
        $datainv['items'] = $items;
        $datainv['showcompanyname'] = $companyname = $obj_property->getPropertySettings($idproperty, $ids['id_companies'], $ids['id_partners'], 'SHOWCOMPANYNAME');
        ;
        $str_simple = $obj_user->customSimpleInfo($usrlist, $companyname);
        $data['str_simple'] = $str_simple;
        $str_custom = $obj_user->customInfo($usrlist, $companyname);
        $pay_summary = \Illuminate\Support\Facades\View::make('invoice.inv_paymentSummary', $datainv)->__toString();
        $data['paysummary'] = $pay_summary;
        $content = \Illuminate\Support\Facades\View::make('invoice.eterm_invtopay', $datainv)->__toString();
        $data['content'] = $content;
        $data['invcontent'] = $content;
        $data['utoken'] = \Illuminate\Support\Facades\Crypt::encrypt($idproperty . '|' . $usrlist->web_user_id . '|' . time() . '|' . config('app.appAPIkey'));
        $data['str_custom'] = $str_custom;
        $data['invoice'] = $datainv['invoice'];

        $profiles['profiles'] = $obj_user->getPaymentProfiles_Credentials($usrlist->web_user_id, $idproperty, 0);
        $profiles['isrecurring'] = 0;
        $cont_profiles = \Illuminate\Support\Facades\View::make('eterm_component.eterm_profile1', $profiles)->__toString();
        $data['profiles'] = $cont_profiles;
        if (!isset($paymentCategories))
            $paymentCategories = array();
        if (isset($web_user_id)) {
            $paymentCategories = $obj_property->getPaymentWebUserCategories($web_user_id);
        }
        if (count($paymentCategories) == 0) {
            //get payments Categories by properties
            $paymentCategories = $obj_property->getPaymentType($idproperty);
        }
        $data['paymentCategories'] = $paymentCategories;

        //add var to show the convenience fee (Trista)
        if ($obj_property->isInactiveVault($idproperty, 0)) {
            unset($data['credRecurring']['cc']);
            $data['noVault'] = 1;
        }
        $array_credOneTime = array();
        if (isset($data['credOneTime']['cc'])) {
            foreach ($data['credOneTime']['cc'] as $item) {
                $cards = array();
                $item = (array) $item;
                if (isset($item['card_type'])) {
                    foreach ($item['card_type'] as $card) {
                        $cards[] = [
                            'type' => $card->type,
                            'convenience_fee' => $card->convenience_fee,
                            'convenience_fee_float' => $card->convenience_fee_float,
                        ];
                    }
                }

                $array_credOneTime[] = [
                    'low_pay_range' => $item['low_pay_range'],
                    'high_pay_range' => $item['high_pay_range'],
                    'convenience_fee' => $item['convenience_fee'],
                    'convenience_fee_float' => $item['convenience_fee_float'],
                    'cards' => $cards
                ];
            }
        }

        $array_credRecurring = array();
        if (isset($data['credRecurring']['cc'])) {
            foreach ($data['credRecurring']['cc'] as $item) {
                $cards = array();
                $item = (array) $item;
                if (isset($item['card_type'])) {
                    foreach ($item['card_type'] as $card) {
                        $cards[] = [
                            'type' => $card['type'],
                            'convenience_fee' => $card['convenience_fee'],
                            'convenience_fee_float' => $card['convenience_fee_float'],
                            'convenience_fee_drp' => $card['convenience_fee_drp'],
                            'convenience_fee_float_drp' => $card['convenience_fee_float_drp']
                        ];
                    }
                }

                $array_credRecurring[] = [
                    'low_pay_range' => $item['low_pay_range'],
                    'high_pay_range' => $item['high_pay_range'],
                    'convenience_fee' => $item['convenience_fee'],
                    'convenience_fee_float' => $item['convenience_fee_float'],
                    'convenience_fee_drp' => $item['convenience_fee_drp'],
                    'convenience_fee_float_drp' => $item['convenience_fee_float_drp'],
                    'cards' => $cards
                ];
            }
        }


        $array_credOneTimeAmex = array();
        if (isset($data['credOneTime']['amex'])) {
            foreach ($data['credOneTime']['amex'] as $item) {
                $item = (array) $item;
                $array_credOneTimeAmex[] = [
                    'low_pay_range' => $item['low_pay_range'],
                    'high_pay_range' => $item['high_pay_range'],
                    'convenience_fee' => $item['convenience_fee'],
                    'convenience_fee_float' => $item['convenience_fee_float'],
                ];
            }
        }

        $array_credRecurringAmex = array();
        if (isset($data['credRecurring']['amex'])) {
            foreach ($data['credRecurring']['amex'] as $item) {
                $item = (array) $item;
                $array_credRecurringAmex[] = [
                    'low_pay_range' => $item['low_pay_range'],
                    'high_pay_range' => $item['high_pay_range'],
                    'convenience_fee' => $item['convenience_fee'],
                    'convenience_fee_float' => $item['convenience_fee_float'],
                    'convenience_fee_drp' => $item['convenience_fee_drp'],
                    'convenience_fee_float_drp' => $item['convenience_fee_float_drp'],
                ];
            }
        }


        $data['array_credOneTime'] = json_encode(array());
        if (isset($array_credOneTime)) {
            $data['array_credOneTime'] = json_encode($array_credOneTime);
        }

        $data['array_credRecurring'] = json_encode(array());
        if (isset($array_credRecurring)) {
            $data['array_credRecurring'] = json_encode($array_credRecurring);
        }

        $data['array_ecOneTime'] = json_encode(array());
        if (isset($data['credOneTime']['ec'])) {
            $data['array_ecOneTime'] = json_encode($data['credOneTime']['ec']);
        }

        $data['array_ecRecurring'] = json_encode(array());
        if (isset($data['credRecurring']['ec'])) {
            $data['array_ecRecurring'] = json_encode($data['credRecurring']['ec']);
        }

        $data['array_credOneTimeAmex'] = json_encode(array());
        if (isset($array_credOneTimeAmex)) {
            $data['array_credOneTimeAmex'] = json_encode($array_credOneTimeAmex);
        }

        $data['array_credRecurringAmex'] = json_encode(array());
        if (isset($array_credRecurringAmex)) {
            $data['array_credRecurringAmex'] = json_encode($array_credRecurringAmex);
        }

        //SETTINGS Auto Payments
        $data['existsAutopay'] = $obj_property->getExistsAutopay($idproperty, $ids['id_companies'], $ids['id_partners']);
        $data['freqAutopay'] = $obj_property->getFreqAutpay($idproperty, $ids['id_companies'], $ids['id_partners']);
        $data['limitAutopay'] = $obj_property->getLimitFixAutopay($idproperty, $ids['id_companies'], $ids['id_partners']);
        $data['daysAutopay'] = $obj_property->getDaysAutopay($idproperty, $ids['id_companies'], $ids['id_partners']);

        //SETTINGS DRP
        $data['existsDrp'] = $obj_property->getExistsDrp($idproperty, $ids['id_companies'], $ids['id_partners']);
        $data['textDrp'] = $obj_property->getTextDrp($idproperty, $ids['id_companies'], $ids['id_partners']);
        $data['freqDrp'] = $obj_property->getFreqDrp($idproperty, $ids['id_companies'], $ids['id_partners']);
        $data['daysDrp'] = $obj_property->getDaysDrp($idproperty, $ids['id_companies'], $ids['id_partners']);

        if (isset($data['msg'])) {
            $atoken = \Illuminate\Support\Facades\Crypt::encrypt($idproperty . "|" . time() . "|" . config("app.appAPIkey"));
            $data['atoken'] = $atoken;
            return view('eterminal.eterm_new', ['data' => $data, 'token' => $ntoken]);
        }
        $data['type'] = 'inv';

        return view('eterminal.eterm_pay', ['data' => $data, 'token' => $ntoken]);
    }

    function runECautopay($paymentInfo, $key) {
        $obj_transaction = new Transations();
        $obj_eter_setting = new EterminalSettings();
        $trans_rec_id = $obj_transaction->addECRecurringTransaction($paymentInfo, $key);
        if (count($paymentInfo['categories']) > 0) {
            //add tras_categories
            $obj_transaction->addReccuringTransCategories($paymentInfo['categories'], $trans_rec_id, $paymentInfo['id_property'], $paymentInfo['web_user_id']);
        }
        $obj_eter_setting->insertTransactionsPhoneFee($trans_rec_id,floatval($paymentInfo['xphonefee']),$paymentInfo['xwalkin'],1);
        return $trans_rec_id;
    }

}
