<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use App\Model\Customize;
use App\Model\Bin;
use App\Model\Companies;

class Properties extends Model {

    protected $table = 'properties';
    public $timestamps = false;

//    public function getPropertyUrlById($property_id) {
//
//        $propertyurl = DB::table($this->table)
//                ->where('id', '=', $property_id)
//                ->select('url_clients')
//                ->get();
//
//        return $propertyurl;
//    }
//


    function isValidPropertyID($partnerID, $companyID, $propertyID) {
        if (empty($propertyID)) {
            return false;
        }
        if (!empty($companyID)) {
            $tmprop = $this->where('id_companies', $companyID)->first('id');
            if (empty($tmprop)) {
                return false;
            }
            return true;
        } elseif (!empty($partnerID)) {
            $tmprop = $this->where('id_partners', $partnerID)->first('id');
            if (empty($tmprop)) {
                return false;
            }
            return true;
        }
        return false;
    }

    function getIdByPartnerSubdomain($partner, $subdomain) {
        $idpartner = DB::table('partners')->select('id')->where('partner_name', '=', $partner)->first();
        $result = $this->where('id_partners', '=', $idpartner['id'])->where('subdomain_clients', '=', $subdomain)->where('status_clients', '=', '1')->where('status_pp', '=', '1')->select('id', 'id_companies', 'id_partners', 'compositeID_clients', 'name_clients', 'address_clients', 'state_clients', 'zip_clients', 'phone_clients', 'url_clients', 'status_pp', 'logo', 'subdomain_clients', 'playout_id', 'email_address_clients', 'accounting_email_address_clients', 'city_clients')->first();
        $result['logo'] = $this->getPropertyLogo($result['logo'], $result['id_companies'], $result['id_partners']);
        return $result;
    }

    function getIdByPartnerSubdomainRedirection($partner, $subdomain) {
        $result = $this->where('redirectfrompartner', '=', $partner)->where('subdomain_clients', '=', $subdomain)->where('status_clients', '=', '1')->select('id', 'id_companies', 'id_partners', 'compositeID_clients', 'name_clients', 'address_clients', 'state_clients', 'zip_clients', 'phone_clients', 'url_clients', 'status_pp', 'logo', 'subdomain_clients', 'playout_id', 'email_address_clients', 'accounting_email_address_clients', 'city_clients')->first();
        $result['logo'] = $this->getPropertyLogo($result['logo'], $result['id_companies'], $result['id_partners']);
        return $result;
    }

    function getLoginSetting($idproperty, $idcompany, $idpartner) {
        $value = $this->getPropertySettings($idproperty, $idcompany, $idpartner, 'PAYMENT_NUMBER_LOGON_WELCOME');

        return $value;
    }

    function getIdByAPIacc($idapi) {
        $result = DB::table('properties')->where('id_api_account', $idapi)->select('id')->first();
        if (!empty($result)) {
            return $result['id'];
        }
        return $result;
    }

    function getQPaySetting($idproperty, $idcompany, $idpartner) {
        $value = $this->getPropertySettings($idproperty, $idcompany, $idpartner, 'PAYNOW');
        if ($value == '') {
            $value = 'First time paying your homeowner association online?';
        }
        return $value;
    }

    function getPropertySettings($idproperty, $idcompany, $idpartner, $key) {
        $obj_customize = new Customize();
// try to get the settings in the property
        $idgroups = $obj_customize->getPropertiesGroup($idproperty);
        if (empty($idgroups)) {//false
            $idgroups = $obj_customize->getCompaniesGroup($idcompany); //1279
            if (empty($idgroups)) {
                $id_groups = $obj_customize->getPartnersGroup($idpartner);
                if (!empty($id_groups)) {
                    $val = $obj_customize->getSettingsValue($id_groups, $key);
                    return $val;
                }
                return null;
            } else {
                $val = $obj_customize->getSettingsValue($idgroups, $key);
                if ($val != null) {
                    return $val;
                }
                $id_groups = $obj_customize->getPartnersGroup($idpartner);
                if (!empty($id_groups)) {
                    $val = $obj_customize->getSettingsValue($id_groups, $key);
                    return $val;
                }
                return null;
            }
        } else {//true
            $val = $obj_customize->getSettingsValue($idgroups, $key);
            if ($val != null) {
                return $val;
            }
            $idgroups = $obj_customize->getCompaniesGroup($idcompany);
            if (empty($idgroups)) {
                $id_groups = $obj_customize->getPartnersGroup($idpartner);
                if (!empty($id_groups)) {
                    $val = $obj_customize->getSettingsValue($id_groups, $key);
                    return $val;
                }
                return null;
            } else {
                $val = $obj_customize->getSettingsValue($idgroups, $key);
                if ($val != null) {
                    return $val;
                }
                $id_groups = $obj_customize->getPartnersGroup($idpartner);
                if (!empty($id_groups)) {
                    $val = $obj_customize->getSettingsValue($id_groups, $key);
                    return $val;
                }
                return null;
            }
        }
    }

    function getPaymentType($idproperty) {
        $result = DB::table('payment_type')->select('payment_type_id', 'payment_type_name', 'amount', 'qty', 'qtymax')->where('property_id', $idproperty)->orderBy('payment_type_name')->get();
        if ($result->isEmpty()) {
            $ids = $this->getOnlyIds($idproperty);
            $p_type = $this->getPropertySettings($idproperty, $ids['id_companies'], $ids['id_partners'], 'DEFAULTPC');
            if (empty($p_type)) {
                $p_type = 'Payment';
            }
            DB::table('payment_type')->insert(
                    ['property_id' => $idproperty, 'payment_type_name' => $p_type]
            );
            $result = DB::table('payment_type')->select('payment_type_id', 'payment_type_name', 'amount')->where('property_id', $idproperty)->orderBy('payment_type_name')->get();
        }
        return $result;
    }

    function getPaymentWebUserCategories($web_user_id) {
        $result = null;
        $resultx = DB::table('web_users_category')->select(DB::raw('id as payment_type_id, description as payment_type_name, amount as b_amount, bill_amount as amount, 0 as qty, 0 as qtymax,unlocked'))->where('web_user_id', $web_user_id)->get();
        if (!empty($resultx)) {
            $result = array();
            foreach ($resultx as $rs) {
                $rs = (array) $rs;
                if ($rs['unlocked'] == 0) {
                    unset($rs['unlocked']);
                }
                $result[] = $rs;
            }
        }
        return $result;
    }

    function getReccurringPaymentType($trans_id) {
        $result = DB::table('recurring_trans_categories')->select('amount', 'category_id')->where('trans_id', $trans_id)->get();
        return $result;
    }

    function getCategoriesXTrans($trans_id) {
        $result = DB::table('trans_categories')->select('amount', 'category_id')->where('trans_id', $trans_id)->get();
        return $result;
    }

    function getPaymentTypeByTransaction($transid, $property_id) {
        $result = DB::table('payment_type')
                ->select('payment_type_id', 'payment_type_name', 'amount')
                ->leftJoin('recurring_trans_categories', function ($join) {
                    $join->on('payment_type.id', '=', 'recurring_trans_categories.category_id')
                    ->where('property_id', $property_id);
                })
                ->join('accounting_recurring_transactions', 'accounting_recurring_transactions.trans_id', '=', 'recurring_trans_categories.trans_id')
                ->where('trans_id', $transid)
                ->get();
        return $result;
    }

    function getPropertyInfo($idproperty) {
        $result = DB::table('properties')->select('id', 'id_companies', 'id_partners', 'compositeID_clients', 'name_clients', 'address_clients', 'city_clients', 'state_clients', 'zip_clients', 'phone_clients', 'url_clients', 'email_address_clients', 'status_pp', 'logo', 'subdomain_clients', 'playout_id', 'accounting_email_address_clients', 'lockbox_id', 'bank_id', 'misc_field', 'units', 'fqc', 'date_stored', 'id_api_account', 'status_clients','support_url')->where('id', $idproperty)->first();
        if (empty($result)) {
            return null;
        }
        $result = (array) $result;
        $result['logo'] = $this->getPropertyLogo($result['logo'], $result['id_companies'], $result['id_partners']);
        $obj_companies = new Companies();
        $result['company_name'] = $obj_companies->getCompanyNameById($result['id_companies']);
        $obj_partner = new Partners();
        $result['partner_name'] = $obj_partner->get1PartnerInfo($result['id_partners'], 'partner_title');
        $result['partner_name_url'] = $obj_partner->get1PartnerInfo($result['id_partners'], 'partner_name');
        return $result;
    }

    function getPropertyInfoByMisc($misc, $id_level) {
        $result = DB::table('properties')->select('id', 'id_companies', 'id_s', 'compositeID_clients', 'name_clients', 'misc_field', 'status_clients')->where('misc_field', $misc)->where('id_companies', $id_level)->first();
        return $result;
    }

    function getPropertyLogo($logo, $id_companies, $id_partners) {
        if (!empty($logo) && file_exists('logos/merchants/' . $logo)) {
            return '/logos/merchants/' . $logo;
        } else {
            $obj_company = new Companies();
            return $obj_company->getCompanyLogo($id_companies, $id_partners);
        }
    }

    function getExistsAutopay($id_property, $id_companies, $id_partners) {
        $existsAuto = $this->getPropertySettings($id_property, $id_companies, $id_partners, 'FIXEDRECURRING');
        if ($existsAuto === "") {
            return 1;
        }
        return $existsAuto;
    }

    /**
     *
     * @param type $id_property
     * @param type $id_companies
     * @param type $id_partners
     * @param type $checkRestriction default false, true to check the CANCELRESTRICTIONS settings
     * @return string
     */
    function getFreqAutpay($id_property, $id_companies, $id_partners, $checkRestriction = false) {
        $freq = array('monthly' => 'Monthly', 'quarterly' => 'Quarterly', 'triannually' => 'Tri-Annually', 'biannually' => 'Semi-Annual', 'annually' => 'Annual', 'weekly' => 'Weekly', 'biweekly' => 'Bi-Weekly');

        if ($checkRestriction) {
            $cancel_restrictions = $this->getPropertySettings($id_property, $id_companies, $id_partners, 'CANCELRESTRICTIONS');
            if (!empty($cancel_restrictions) && $cancel_restrictions == 1) {
                return $freq;
            }
        }

        $arrayFreq = $this->getPropertySettings($id_property, $id_companies, $id_partners, 'FREQAUTOPAY');
        if (empty($arrayFreq)) {
            return $freq;
        }

        $explode = explode("|", $arrayFreq);
        $freq_autopay = array();
        for ($i = 0; $i < count($explode); $i++) {
            if ($explode[$i] != 'onetime' && $explode[$i] != 'untilcancel') {
                $freq_autopay[$explode[$i]] = $freq[$explode[$i]];
            }
        }
        return $freq_autopay;
    }

    /**
     *
     * @param type $id_property
     * @param type $id_companies
     * @param type $id_partners
     * @param type $checkRestriction default false, true to check the CANCELRESTRICTIONS settings
     * @return int
     */
    function getLimitAutopay($id_property, $id_companies, $id_partners, $checkRestriction = false) {
        if ($checkRestriction) {
            $cancel_restrictions = $this->getPropertySettings($id_property, $id_companies, $id_partners, 'CANCELRESTRICTIONS');
            if (!empty($cancel_restrictions) && $cancel_restrictions == 1) {
                return '';
            }
        }

        $limit = $this->getPropertySettings($id_property, $id_companies, $id_partners, 'DYNAMICLIMIT');
        if (empty($limit)) {
            return 0;
        }
        return $limit;
    }

    /**
     * @
     * @param type $id_property
     * @param type $id_companies
     * @param type $id_partners
     * @param type $checkRestriction default false, true to check the CANCELRESTRICTIONS settings
     * @return int
     */
    function getLimitFixAutopay($id_property, $id_companies, $id_partners, $checkRestriction = false) {
        if ($checkRestriction) {
            $cancel_restrictions = $this->getPropertySettings($id_property, $id_companies, $id_partners, 'CANCELRESTRICTIONS');
            if (!empty($cancel_restrictions) && $cancel_restrictions == 1) {
                return '';
            }
        }

        $limit = $this->getPropertySettings($id_property, $id_companies, $id_partners, 'MAXRECURRINGPAYMENTPERUSER');
        if (empty($limit)) {
            return 0;
        }
        return $limit;
    }

    /**
     *
     * @param type $id_property
     * @param type $id_companies
     * @param type $id_partners
     * @param type $checkRestriction default false, true to check the CANCELRESTRICTIONS settings
     * @return type
     */
    function getDaysAutopay($id_property, $id_companies, $id_partners, $checkRestriction = false) {
        $daysAuto = $this->getPropertySettings($id_property, $id_companies, $id_partners, 'DAYSAUTOPAY');

        if ($checkRestriction) {
            $cancel_restrictions = $this->getPropertySettings($id_property, $id_companies, $id_partners, 'CANCELRESTRICTIONS');
            if (!empty($cancel_restrictions) && $cancel_restrictions == 1) {
                $daysAuto = '1|31';
            }
        }

        if (empty($daysAuto)) {
            $daysAuto = '1|31';
        }
        $explode = explode("|", $daysAuto);
        $days = array();
        for ($a = $explode[0]; $a <= $explode[1]; $a++) {
            $days[] = $a;
        }
        return $days;
    }

    function getTextDrp($id_property, $id_companies, $id_partners) {
        $textDrp = $this->getPropertySettings($id_property, $id_companies, $id_partners, 'DYNAMICRECURRINGTEXT');
        if (empty($textDrp)) {
            return "text";
        }
        return base64_decode($textDrp);
    }

    /**
     *
     * @param type $id_property
     * @param type $id_companies
     * @param type $id_partners
     * @param type $checkRestriction default false, true to check the CANCELRESTRICTIONS settings
     * @return type
     */
    function getDaysDrp($id_property, $id_companies, $id_partners, $checkRestriction = false) {
        $dayDrp = $this->getPropertySettings($id_property, $id_companies, $id_partners, 'DRPDAYSAUTOPAY');

        if ($checkRestriction) {
            $cancel_restrictions = $this->getPropertySettings($id_property, $id_companies, $id_partners, 'CANCELRESTRICTIONS');
            if (!empty($cancel_restrictions) && $cancel_restrictions == 1) {
                $dayDrp = '1|31';
            }
        }

        if (empty($dayDrp)) {
            $dayDrp = '1|31';
        }
        $explode = explode("|", $dayDrp);
        $days = array();
        for ($a = $explode[0]; $a <= $explode[1]; $a++) {
            $days[] = $a;
        }
        return $days;
    }

    function getExistsDrp($id_property, $id_companies, $id_partners) {
        $existDrp = $this->getPropertySettings($id_property, $id_companies, $id_partners, 'DYNAMICRECURRING');
        if (empty($existDrp)) {
            return 0;
        }
        return $existDrp;
    }

    function getNeedHelp($id_property, $id_companies, $id_partners) {
        $needHelp = $this->getPropertySettings($id_property, $id_companies, $id_partners, 'NEWHELP');
        $obj_replace = new \App\CustomClass\ReplaceBodyTextSetting();
        if (empty($needHelp)) {
            return '<label><b>Need Help?</b></label><p>For Balance, Account #, Billing Issues, &amp; Maintenance requests please contact your Management Company at:<br><span class="fa fa-envelope-o"></span> <a href="" class="underline">abcmanagement@gmail.com</a><br><span class="fa fa-phone"></span> 305-555-1234</p>';
        }
        $needHelp = $obj_replace->ReplaceNeedHelp($needHelp);
        return $needHelp;
    }

    /**
     *
     * @param type $id_property
     * @param type $id_companies
     * @param type $id_partners
     * @param type $checkRestriction default false, true to check the CANCELRESTRICTIONS settings
     * @return string
     */
    function getFreqDrp($id_property, $id_companies, $id_partners, $checkRestriction = false) {
        $freq = array('monthly' => 'Monthly', 'quarterly' => 'Quarterly', 'triannually' => 'Tri-Annual', 'biannually' => 'Semi-Annual', 'annually' => 'Annual', 'weekly' => 'Weekly', 'biweekly' => 'Bi-Weekly');

        if ($checkRestriction) {
            $cancel_restrictions = $this->getPropertySettings($id_property, $id_companies, $id_partners, 'CANCELRESTRICTIONS');
            if (!empty($cancel_restrictions) && $cancel_restrictions == 1) {
                return $freq;
            }
        }

        $arrayFreq = $this->getPropertySettings($id_property, $id_companies, $id_partners, 'DRPFREQAUTOPAY');
        if (empty($arrayFreq)) {
            return $freq;
        }

        $explode = explode("|", $arrayFreq);
        $freq_autopay = array();
        for ($i = 0; $i < count($explode); $i++) {
            if ($explode[$i] != 'onetime' && $explode[$i] != 'untilcancel') {
                $freq_autopay[$explode[$i]] = $freq[$explode[$i]];
            }
        }
        return $freq_autopay;
    }

    function getNoCancelAuto($id_property, $id_companies, $id_partners, $checkRestriction = false) {
        $arrayFreq = $this->getPropertySettings($id_property, $id_companies, $id_partners, 'FREQAUTOPAY');

        if ($checkRestriction) {
            $cancel_restrictions = $this->getPropertySettings($id_property, $id_companies, $id_partners, 'CANCELRESTRICTIONS');
            if (!empty($cancel_restrictions) && $cancel_restrictions == 1) {
                return 0;
            }
        }

        if (empty($arrayFreq)) {
            return 0;
        }
        if (strpos($arrayFreq, 'untilcancel') !== false) {
            return 0;
        }
        return 1;
    }

    function getNoCancelDRP($id_property, $id_companies, $id_partners, $checkRestriction = false) {
        $arrayFreq = $this->getPropertySettings($id_property, $id_companies, $id_partners, 'DRPFREQAUTOPAY');

        if ($checkRestriction) {
            $cancel_restrictions = $this->getPropertySettings($id_property, $id_companies, $id_partners, 'CANCELRESTRICTIONS');
            if (!empty($cancel_restrictions) && $cancel_restrictions == 1) {
                return 0;
            }
        }

        if (empty($arrayFreq)) {
            return 0;
        }
        if (strpos($arrayFreq, 'untilcancel') !== false) {
            return 0;
        }
        return 1;
    }

    function get5yearInAdvance($cancel = true, $cmonths = 20) {
        $months = array('January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December');
        $time = date('m-Y');

        $date = explode("-", $time);
        $cont = 0;
        $pos = $date[0] - 1;
        $year = $date[1];
        $end_dates = array();
        $end = array();
        if ($cancel) {
            $end['value'] = "-1";
            $end['date'] = "Until Canceled";
            $end_dates[] = $end;
        }
        if ($cmonths > 0) {
            while ($cont++ < $cmonths * 12) {
                $end['value'] = $year . '|' . str_pad(($pos + 1), 2, 0, STR_PAD_LEFT);
                $end['date'] = $months[$pos] . ', ' . $year;
                $end_dates[] = $end;
//var_dump($end_dates);
                $pos++;
                if ($pos == 12) {
                    $pos = 0;
                    $year++;
                }
            }
        }
        return $end_dates;
    }

    function getcredBillCredentials($idproperty) {
        $array_cred = DB::table('merchant_account')->where('property_id', $idproperty)->whereIn('payment_method', ['ebill', 'mbill'])->first();
        return $array_cred;
    }

    function getcredOneTimeCredentials($idproperty, $method = "") {
        $array_cred = array();
        //ech

        if ($method != "") {
            $ec = DB::table('merchant_account')->select('merchant_account_id', 'high_ticket', 'low_pay_range', 'high_pay_range', 'convenience_fee', 'convenience_fee_float', 'property_id')->where('property_id', $idproperty)->where('payment_method', $method . 'ec')->where('is_recurring', 0)->get();
            if (empty($ec) || $ec->isEmpty()) {
                $ec = DB::table('merchant_account')->select('merchant_account_id', 'high_ticket', 'low_pay_range', 'high_pay_range', 'convenience_fee', 'convenience_fee_float', 'property_id')->where('property_id', $idproperty)->where('payment_method', 'ec')->where('is_recurring', 0)->get();
            }
        } else {
            $ec = DB::table('merchant_account')->select('merchant_account_id', 'high_ticket', 'low_pay_range', 'high_pay_range', 'convenience_fee', 'convenience_fee_float', 'property_id')->where('property_id', $idproperty)->where('payment_method', 'ec')->where('is_recurring', 0)->get();
        }
//cc
        if ($method != "") {
            $cc = DB::table('merchant_account')->select('merchant_account_id', 'high_ticket', 'low_pay_range', 'high_pay_range', 'convenience_fee', 'convenience_fee_float', 'property_id')->where('property_id', $idproperty)->where('payment_method', $method . 'cc')->where('is_recurring', 0)->get();
            if (empty($cc) || $cc->isEmpty()) {
                $cc = DB::table('merchant_account')->select('merchant_account_id', 'high_ticket', 'low_pay_range', 'high_pay_range', 'convenience_fee', 'convenience_fee_float', 'property_id')->where('property_id', $idproperty)->where('payment_method', 'cc')->where('is_recurring', 0)->get();
            }
        } else {
            $cc = DB::table('merchant_account')->select('merchant_account_id', 'high_ticket', 'low_pay_range', 'high_pay_range', 'convenience_fee', 'convenience_fee_float', 'property_id')->where('property_id', $idproperty)->where('payment_method', 'cc')->where('is_recurring', 0)->get();
        }
        //swipe
        if ($method != "") {
            $swipe = DB::table('merchant_account')
                    ->select('merchant_account_id', 'high_ticket', 'low_pay_range', 'high_pay_range', 'convenience_fee', 'convenience_fee_float', 'property_id')
                    ->where('property_id', $idproperty)
                    ->where('payment_method', $method . 'swipe')
                    ->where('is_recurring', 0)
                    ->get();
            if ($swipe->isEmpty()) {
                $swipe = DB::table('merchant_account')
                        ->select('merchant_account_id', 'high_ticket', 'low_pay_range', 'high_pay_range', 'convenience_fee', 'convenience_fee_float', 'property_id')
                        ->where('property_id', $idproperty)
                        ->where('payment_method', 'swipe')
                        ->where('is_recurring', 0)
                        ->get();
            }
        } else {
            $swipe = DB::table('merchant_account')
                    ->select('merchant_account_id', 'high_ticket', 'low_pay_range', 'high_pay_range', 'convenience_fee', 'convenience_fee_float', 'property_id')
                    ->where('property_id', $idproperty)
                    ->where('payment_method', 'swipe')
                    ->where('is_recurring', 0)
                    ->get();
        }


        //amex
        if ($method != "") {
            $amex = DB::table('merchant_account')->select('merchant_account_id', 'high_ticket', 'low_pay_range', 'high_pay_range', 'convenience_fee', 'convenience_fee_float', 'property_id')->where('property_id', $idproperty)->where('payment_method', $method . 'amex')->where('is_recurring', 0)->get();
            if (empty($amex) || $amex->isEmpty()) {
                $amex = DB::table('merchant_account')->select('merchant_account_id', 'high_ticket', 'low_pay_range', 'high_pay_range', 'convenience_fee', 'convenience_fee_float', 'property_id')->where('property_id', $idproperty)->where('payment_method', 'amex')->where('is_recurring', 0)->get();
            }
        } else {
            $amex = DB::table('merchant_account')->select('merchant_account_id', 'high_ticket', 'low_pay_range', 'high_pay_range', 'convenience_fee', 'convenience_fee_float', 'property_id')->where('property_id', $idproperty)->where('payment_method', 'amex')->where('is_recurring', 0)->get();
        }

        foreach ($cc as $pos => $c) {
            if (isset($c->merchant_account_id)) {
                $cc[$pos]->card_type = DB::table('card_type_fee')->where('id_merchant_account', $c->merchant_account_id)->get();
            }
        }

        //add service array to each credential, only to use in eterminal
        foreach ($ec as $pos => $ech) {
            if (isset($ech->property_id)) {
                $ec[$pos]->service = DB::table('service_fee')->join('service_type', 'service_fee.service_type', '=', 'service_type.id_type')->where('id_property', $ech->property_id)->where('payment_method', 'ec')->where('status', 1)->get();
            }
        }
        foreach ($cc as $pos => $c) {
            if (isset($c->property_id)) {
                $cc[$pos]->service = DB::table('service_fee')->join('service_type', 'service_fee.service_type', '=', 'service_type.id_type')->where('id_property', $c->property_id)->where('payment_method', 'cc')->where('status', 1)->get();
            }
        }
        foreach ($amex as $pos => $am) {
            if (isset($am->property_id)) {
                $amex[$pos]->service = DB::table('service_fee')->join('service_type', 'service_fee.service_type', '=', 'service_type.id_type')->where('id_property', $am->property_id)->where('payment_method', 'amex')->where('status', 1)->get();
            }
        }
        foreach ($swipe as $pos => $c) {
            if (isset($c->property_id)) {
                $swipe[$pos]->service = DB::table('service_fee')->join('service_type', 'service_fee.service_type', '=', 'service_type.id_type')->where('id_property', $c->property_id)->where('payment_method', 'swipe')->where('status', 1)->get();
            }
        }


        if (count($ec) > 0) {
            $array_cred['ec'] = $ec;
        }
        if (count($cc) > 0) {
            $array_cred['cc'] = $cc;
        }
        if (count($amex) > 0) {
            $array_cred['amex'] = $amex;
        }
        if (count($swipe) > 0) {
            $array_cred['swipe'] = $swipe;
        }

        return $array_cred;
    }

    function getcredRecurringCredentials($idproperty, $method = "") {
        $array_cred = array();
//ech
        if ($method != "") {
            $ec = DB::table('merchant_account')->select('merchant_account_id', 'high_ticket', 'low_pay_range', 'high_pay_range', 'convenience_fee', 'convenience_fee_float', 'convenience_fee_drp', 'convenience_fee_float_drp')->where('property_id', $idproperty)->where('payment_method', $method . 'ec')->where('is_recurring', 1)->get();
            if ($ec->isEmpty()) {
                $ec = DB::table('merchant_account')->select('merchant_account_id', 'high_ticket', 'low_pay_range', 'high_pay_range', 'convenience_fee', 'convenience_fee_float', 'convenience_fee_drp', 'convenience_fee_float_drp')->where('property_id', $idproperty)->where('payment_method', 'ec')->where('is_recurring', 1)->get();
            }
        } else {
            $ec = DB::table('merchant_account')->select('merchant_account_id', 'high_ticket', 'low_pay_range', 'high_pay_range', 'convenience_fee', 'convenience_fee_float', 'convenience_fee_drp', 'convenience_fee_float_drp')->where('property_id', $idproperty)->where('payment_method', 'ec')->where('is_recurring', 1)->get();
        }
//cc
        if ($method != "") {
            $cc = DB::table('merchant_account')->select('merchant_account_id', 'high_ticket', 'low_pay_range', 'high_pay_range', 'convenience_fee', 'convenience_fee_float', 'convenience_fee_drp', 'convenience_fee_float_drp')->where('property_id', $idproperty)->where('payment_method', $method . 'cc')->where('is_recurring', 1)->get();
            if ($cc->isEmpty()) {
                $cc = DB::table('merchant_account')->select('merchant_account_id', 'high_ticket', 'low_pay_range', 'high_pay_range', 'convenience_fee', 'convenience_fee_float', 'convenience_fee_drp', 'convenience_fee_float_drp')->where('property_id', $idproperty)->where('payment_method', 'cc')->where('is_recurring', 1)->get();
            }
        } else {
            $cc = DB::table('merchant_account')->select('merchant_account_id', 'high_ticket', 'low_pay_range', 'high_pay_range', 'convenience_fee', 'convenience_fee_float', 'convenience_fee_drp', 'convenience_fee_float_drp')->where('property_id', $idproperty)->where('payment_method', 'cc')->where('is_recurring', 1)->get();
        }
//swipe
        if ($method != "") {
            $swipe = DB::table('merchant_account')->select('merchant_account_id', 'high_ticket', 'low_pay_range', 'high_pay_range', 'convenience_fee', 'convenience_fee_float', 'convenience_fee_drp', 'convenience_fee_float_drp')->where('property_id', $idproperty)->where('payment_method', $method . 'swipe')->where('is_recurring', 1)->get();
            if ($swipe->isEmpty()) {
                $swipe = DB::table('merchant_account')->select('merchant_account_id', 'high_ticket', 'low_pay_range', 'high_pay_range', 'convenience_fee', 'convenience_fee_float', 'convenience_fee_drp', 'convenience_fee_float_drp')->where('property_id', $idproperty)->where('payment_method', 'swipe')->where('is_recurring', 1)->get();
            }
        } else {
            $swipe = DB::table('merchant_account')->select('merchant_account_id', 'high_ticket', 'low_pay_range', 'high_pay_range', 'convenience_fee', 'convenience_fee_float', 'convenience_fee_drp', 'convenience_fee_float_drp')->where('property_id', $idproperty)->where('payment_method', 'swipe')->where('is_recurring', 1)->get();
        }
//amex
        if ($method != "") {
            $amex = DB::table('merchant_account')->select('merchant_account_id', 'high_ticket', 'low_pay_range', 'high_pay_range', 'convenience_fee', 'convenience_fee_float', 'convenience_fee_drp', 'convenience_fee_float_drp')->where('property_id', $idproperty)->where('payment_method', $method . 'amex')->where('is_recurring', 1)->get();
            if ($amex->isEmpty()) {
                $amex = DB::table('merchant_account')->select('merchant_account_id', 'high_ticket', 'low_pay_range', 'high_pay_range', 'convenience_fee', 'convenience_fee_float', 'convenience_fee_drp', 'convenience_fee_float_drp')->where('property_id', $idproperty)->where('payment_method', 'amex')->where('is_recurring', 1)->get();
            }
        } else {
            $amex = DB::table('merchant_account')->select('merchant_account_id', 'high_ticket', 'low_pay_range', 'high_pay_range', 'convenience_fee', 'convenience_fee_float', 'convenience_fee_drp', 'convenience_fee_float_drp')->where('property_id', $idproperty)->where('payment_method', 'amex')->where('is_recurring', 1)->get();
        }



        foreach ($cc as $pos => $c) {
            $c = (array) $c;
            if (isset($c->merchant_account_id)) {
                $cc[$pos]['card_type'] = DB::table('card_type_fee')->where('id_merchant_account', $c['merchant_account_id'])->get();
            }
        }


        if (count($ec) > 0) {
            $array_cred['ec'] = $ec;
        }
        if (count($cc) > 0) {
            $array_cred['cc'] = $cc;
        }
        if (count($amex) > 0) {
            $array_cred['amex'] = $amex;
        }
        if (count($swipe) > 0) {
            $array_cred['swipe'] = $swipe;
        }


        return $array_cred;
    }

    function getTypeCredentialByCycle($property_id, $cycle) {
        $value = DB::table('merchant_account')->where('property_id', $property_id)->where('is_recurring', $cycle)->select('payment_method')->get();
        if (empty($value)) {
            return "";
        }
        $pay_method = array();
        for ($i = 0; $i < count($value); $i++) {
            $pay_method[] = $value[$i]['payment_method'];
        }
        return $pay_method;
    }

    function getHight_LowerTicket($data) {

        $velocities = array();
        $min = 0;
        $max = 0;
        if (isset($data['ec'])) {
            $min = $data['ec'][0]->low_pay_range;
            $max = $data['ec'][0]->high_pay_range;
            for ($i = 1; $i < count($data['ec']); $i++) {
                if ($data['ec'][$i]->low_pay_range < $min) {
                    $min = $data['ec'][$i]->low_pay_range;
                }
                if ($data['ec'][$i]->high_pay_range > $max) {
                    $max = $data['ec'][$i]->high_pay_range;
                }
            }
            $vel = array();
            $vel['low_pay_range'] = $min;
            $vel['high_pay_range'] = $max;
            $velocities['ec'] = $vel;
        }
        if (isset($data['cc'])) {
            $min = $data['cc'][0]->low_pay_range;
            $max = $data['cc'][0]->high_pay_range;
            for ($i = 1; $i < count($data['cc']); $i++) {
                if ($data['cc'][$i]->low_pay_range < $min) {
                    $min = $data['cc'][$i]->low_pay_range;
                }
                if ($data['cc'][$i]->high_pay_range > $max) {
                    $max = $data['cc'][$i]->high_pay_range;
                }
            }
            $vel = array();
            $vel['low_pay_range'] = $min;
            $vel['high_pay_range'] = $max;
            $velocities['cc'] = $vel;
        }
        if (isset($data['amex'])) {
            $min = $data['amex'][0]->low_pay_range;
            $max = $data['amex'][0]->high_pay_range;
            for ($i = 1; $i < count($data['amex']); $i++) {
                if ($data['amex'][$i]->low_pay_range < $min) {
                    $min = $data['amex'][$i]->low_pay_range;
                }
                if ($data['amex'][$i]->high_pay_range > $max) {
                    $max = $data['amex'][$i]->high_pay_range;
                }
            }

            $vel = array();
            $vel['low_pay_range'] = $min;
            $vel['high_pay_range'] = $max;
            $velocities['amex'] = $vel;
        }
        return $velocities;
    }

    function getCredentialsBytype($type, $idproperty) {

        if ($type == 'am' || $type == 'amex' || $type == 'ivr-amex') {
            $type = "amex";
        }
        if ($type == 'eterm-amex' || $type == 'eterm-am') {
            $type = "eterm-amex";
        }

        $var = DB::table('merchant_account')->select('merchant_account_id', 'payment_method', 'gateway', 'payment_source_key as key', 'payment_source_store_id as sid', 'payment_source_location_id as lid', 'payment_source_merchant_id as mid', 'high_ticket', 'low_pay_range', 'high_pay_range', 'convenience_fee', 'convenience_fee_float', 'convenience_fee_drp', 'convenience_fee_float_drp', 'property_id', 'pinlessdebit')->where('property_id', $idproperty)->where('payment_method', $type)->where('is_recurring', 0)->get();

        if ($var->isNotEmpty()) {
            return $var;
        }
        if ($var->isEmpty()) {

            if ($type == 'eterm-amex') {
                $var = DB::table('merchant_account')->select('merchant_account_id', 'payment_method', 'gateway', 'payment_source_key as key', 'payment_source_store_id as sid', 'payment_source_location_id as lid', 'payment_source_merchant_id as mid', 'high_ticket', 'low_pay_range', 'high_pay_range', 'convenience_fee', 'convenience_fee_float', 'convenience_fee_drp', 'convenience_fee_float_drp', 'property_id', 'pinlessdebit')->where('property_id', $idproperty)->where('payment_method', 'amex')->where('is_recurring', 0)->get();
            }
            if ($var->isEmpty()) {
                if ($type == 'swipe') {
                    $var = DB::table('merchant_account')->select('merchant_account_id', 'payment_method', 'gateway', 'payment_source_key as key', 'payment_source_store_id as sid', 'payment_source_location_id as lid', 'payment_source_merchant_id as mid', 'high_ticket', 'low_pay_range', 'high_pay_range', 'convenience_fee', 'convenience_fee_float', 'convenience_fee_drp', 'convenience_fee_float_drp', 'property_id', 'pinlessdebit')->where('property_id', $idproperty)->where('payment_method', 'swipe')->get();
                } else {
                    //credential to cc or ech if do not exist eterm or ivr credentials
                    $typem = substr($type, -2);
                    if ($var->isEmpty() && $typem == 'cc') {
                        $var = DB::table('merchant_account')->select('merchant_account_id', 'payment_method', 'gateway', 'payment_source_key as key', 'payment_source_store_id as sid', 'payment_source_location_id as lid', 'payment_source_merchant_id as mid', 'high_ticket', 'low_pay_range', 'high_pay_range', 'convenience_fee', 'convenience_fee_float', 'convenience_fee_drp', 'convenience_fee_float_drp', 'property_id', 'pinlessdebit')->where('property_id', $idproperty)->where('payment_method', $typem)->where('is_recurring', 0)->get();
                    } elseif ($var->isEmpty() && $typem == 'ec') {
                        $var = DB::table('merchant_account')->select('merchant_account_id', 'payment_method', 'gateway', 'payment_source_key as key', 'payment_source_store_id as sid', 'payment_source_location_id as lid', 'payment_source_merchant_id as mid', 'high_ticket', 'low_pay_range', 'high_pay_range', 'convenience_fee', 'convenience_fee_float', 'convenience_fee_drp', 'convenience_fee_float_drp', 'property_id', 'pinlessdebit')->where('property_id', $idproperty)->where('payment_method', $typem)->where('is_recurring', 0)->get();
                    }
                }
            }
        }

        return $var;
    }

    function getCredentialtype_isrecurring($type, $idproperty, $isrecurring) {

        if ($type == 'am' || $type == 'amex' || $type == 'ivr-amex') {
            $type = "amex";
        }
        if ($type == 'eterm-amex' || $type == 'eterm-am') {
            $type = "eterm-amex";
        }
        if (($type == 'swipe' || $type == 'eterm-swipe') && $isrecurring == 1) {
            $type = "eterm-cc";
        }
        if (($type == 'eterm-swipe')) {
            $type = "swipe";
        }

        $var = DB::table('merchant_account')->select('merchant_account_id', 'payment_method', 'gateway', 'payment_source_key as key', 'payment_source_store_id as sid', 'payment_source_location_id as lid', 'payment_source_merchant_id as mid', 'high_ticket', 'low_pay_range', 'high_pay_range', 'convenience_fee', 'convenience_fee_float', 'convenience_fee_drp', 'convenience_fee_float_drp', 'property_id', 'ppd_import_fee', 'pinlessdebit')->where('property_id', $idproperty)->where('payment_method', $type)->where('is_recurring', $isrecurring)->get();
        if ($var->isNotEmpty()) {
            return $var;
        }
        if ($var->isEmpty()) {
            if ($type == 'eterm-amex') {
                $var = DB::table('merchant_account')->select('merchant_account_id', 'payment_method', 'gateway', 'payment_source_key as key', 'payment_source_store_id as sid', 'payment_source_location_id as lid', 'payment_source_merchant_id as mid', 'high_ticket', 'low_pay_range', 'high_pay_range', 'convenience_fee', 'convenience_fee_float', 'convenience_fee_drp', 'convenience_fee_float_drp', 'property_id', 'ppd_import_fee', 'pinlessdebit')->where('property_id', $idproperty)->where('payment_method', 'amex')->where('is_recurring', $isrecurring)->get();
            }
            if ($var->isEmpty()) {
                if ($type == 'swipe') {
                    $var = DB::table('merchant_account')->select('merchant_account_id', 'payment_method', 'gateway', 'payment_source_key as key', 'payment_source_store_id as sid', 'payment_source_location_id as lid', 'payment_source_merchant_id as mid', 'high_ticket', 'low_pay_range', 'high_pay_range', 'convenience_fee', 'convenience_fee_float', 'convenience_fee_drp', 'convenience_fee_float_drp', 'property_id', 'ppd_import_fee', 'pinlessdebit')->where('property_id', $idproperty)->where('payment_method', 'swipe')->get();
                } else {
                    $typem = substr($type, -2);
                    if ($var->isEmpty() && $typem == 'cc') {
                        $var = DB::table('merchant_account')->select('merchant_account_id', 'payment_method', 'gateway', 'payment_source_key as key', 'payment_source_store_id as sid', 'payment_source_location_id as lid', 'payment_source_merchant_id as mid', 'high_ticket', 'low_pay_range', 'high_pay_range', 'convenience_fee', 'convenience_fee_float', 'convenience_fee_drp', 'convenience_fee_float_drp', 'property_id', 'ppd_import_fee', 'pinlessdebit')->where('property_id', $idproperty)->where('payment_method', $typem)->where('is_recurring', $isrecurring)->get();
                    } elseif ($var->isEmpty() && $typem == 'ec') {
                        $var = DB::table('merchant_account')->select('merchant_account_id', 'payment_method', 'gateway', 'payment_source_key as key', 'payment_source_store_id as sid', 'payment_source_location_id as lid', 'payment_source_merchant_id as mid', 'high_ticket', 'low_pay_range', 'high_pay_range', 'convenience_fee', 'convenience_fee_float', 'convenience_fee_drp', 'convenience_fee_float_drp', 'property_id', 'ppd_import_fee', 'pinlessdebit')->where('property_id', $idproperty)->where('payment_method', $typem)->where('is_recurring', $isrecurring)->get();
                    }
                }
            }
        }


        return $var;
    }

    function getConvenienceFee($property_id, $isrecurring, $total_amount, $type) {
        $var = DB::table('merchant_account')->select('payment_method', 'gateway', 'payment_source_key as key', 'payment_source_store_id as sid', 'payment_source_location_id as lid', 'payment_source_merchant_id as mid', 'high_ticket', 'low_pay_range', 'high_pay_range', 'convenience_fee', 'convenience_fee_float', 'convenience_fee_drp', 'convenience_fee_float_drp')->where('property_id', $property_id)->where('payment_method', $type)->where('is_recurring', $isrecurring)->first();
        if ($var == null) {
            return 0;
        }
        $tmpfee = $var['convenience_fee'];
        if ($var['convenience_fee_float'] > 0) {
            $tmpfee += $total_amount * $var['convenience_fee_float'] / 100;
        }
        return $tmpfee;
    }

    function getConvenienceFeeIvr($property_id, $total_amount) {
//cfee to IVR when exist or cfee to one time payment
//cc
        $cfee = array();
        $varcc = DB::table('merchant_account')->select('payment_method', 'gateway', 'payment_source_key as key', 'payment_source_store_id as sid', 'payment_source_location_id as lid', 'payment_source_merchant_id as mid', 'high_ticket', 'low_pay_range', 'high_pay_range', 'convenience_fee', 'convenience_fee_float', 'convenience_fee_drp', 'convenience_fee_float_drp')->where('property_id', $property_id)->where('payment_method', 'ivr-cc')->where('is_recurring', 0)->first();
        if ($varcc == null) {
            $varcc = DB::table('merchant_account')->select('payment_method', 'gateway', 'payment_source_key as key', 'payment_source_store_id as sid', 'payment_source_location_id as lid', 'payment_source_merchant_id as mid', 'high_ticket', 'low_pay_range', 'high_pay_range', 'convenience_fee', 'convenience_fee_float', 'convenience_fee_drp', 'convenience_fee_float_drp')->where('property_id', $property_id)->where('payment_method', 'cc')->where('is_recurring', 0)->first();
        }
        if ($varcc != null) {
            $tmpfeecc = $varcc['convenience_fee'];
            if ($varcc['convenience_fee_float'] > 0) {
                $tmpfeecc += $total_amount * $varcc['convenience_fee_float'] / 100;
            }
            $cfee['cc_fee'] = number_format(round($tmpfeecc, 2), 2);
        }

//ech
        $varec = DB::table('merchant_account')->select('payment_method', 'gateway', 'payment_source_key as key', 'payment_source_store_id as sid', 'payment_source_location_id as lid', 'payment_source_merchant_id as mid', 'high_ticket', 'low_pay_range', 'high_pay_range', 'convenience_fee', 'convenience_fee_float', 'convenience_fee_drp', 'convenience_fee_float_drp')->where('property_id', $property_id)->where('payment_method', 'ivr-ec')->where('is_recurring', 0)->first();
        if ($varec == null) {
            $varec = DB::table('merchant_account')->select('payment_method', 'gateway', 'payment_source_key as key', 'payment_source_store_id as sid', 'payment_source_location_id as lid', 'payment_source_merchant_id as mid', 'high_ticket', 'low_pay_range', 'high_pay_range', 'convenience_fee', 'convenience_fee_float', 'convenience_fee_drp', 'convenience_fee_float_drp')->where('property_id', $property_id)->where('payment_method', 'ec')->where('is_recurring', 0)->first();
        }
        if ($varec != null) {
            $tmpfeeec = $varec['convenience_fee'];
            if ($varec['convenience_fee_float'] > 0) {
                $tmpfeeec += $total_amount * $varec['convenience_fee_float'] / 100;
            }

            $cfee['ec_fee'] = number_format(round($tmpfeeec, 2), 2);
        }


        return $cfee;
    }

    function getConvenienceFeeDRP($property_id, $isrecurring, $type) {
        if ($type == 'am') {
            $type = "amex";
        }
        $var = DB::table('merchant_account')->select('payment_method', 'gateway', 'payment_source_key as key', 'payment_source_store_id as sid', 'payment_source_location_id as lid', 'payment_source_merchant_id as mid', 'high_ticket', 'low_pay_range', 'high_pay_range', 'convenience_fee', 'convenience_fee_float', 'convenience_fee_drp', 'convenience_fee_float_drp')->where('property_id', $property_id)->where('payment_method', $type)->where('is_recurring', $isrecurring)->first();

        if (empty($var) && $type == 'eterm-cc') {
            $type = "cc";
            $var = DB::table('merchant_account')->select('payment_method', 'gateway', 'payment_source_key as key', 'payment_source_store_id as sid', 'payment_source_location_id as lid', 'payment_source_merchant_id as mid', 'high_ticket', 'low_pay_range', 'high_pay_range', 'convenience_fee', 'convenience_fee_float', 'convenience_fee_drp', 'convenience_fee_float_drp')->where('property_id', $property_id)->where('payment_method', $type)->where('is_recurring', $isrecurring)->first();
        } else {
            if (empty($var) && $type == 'eterm-ec') {
                $type = "ec";
                $var = DB::table('merchant_account')->select('payment_method', 'gateway', 'payment_source_key as key', 'payment_source_store_id as sid', 'payment_source_location_id as lid', 'payment_source_merchant_id as mid', 'high_ticket', 'low_pay_range', 'high_pay_range', 'convenience_fee', 'convenience_fee_float', 'convenience_fee_drp', 'convenience_fee_float_drp')->where('property_id', $property_id)->where('payment_method', $type)->where('is_recurring', $isrecurring)->first();
            }
        }

        if ($var == null) {
            return 0;
        }
        $tmpfee = "";
        $var = (array) $var;
        if ($var['convenience_fee_drp'] > 0 && $var['convenience_fee_float_drp'] > 0) {
            $tmpfee = '$' . $var['convenience_fee_drp'] . '+' . $var['convenience_fee_float_drp'] . '%';
        } else {
            if ($var['convenience_fee_drp'] > 0) {
                $tmpfee = '$' . $var['convenience_fee_drp'];
            }
            if ($var['convenience_fee_float_drp'] > 0) {
                $tmpfee = $tmpfee . $var['convenience_fee_float_drp'] . '%';
            }
        }
        return $tmpfee;
    }

    function getCompanyInfoMinimal($idcompany) {
        $var = DB::table('companies')->where('id', '=', $idcompany)->select('company_name', 'phone_number')->first();
        return $var;
    }

    function getOnlyIds($id_property) {
        $var = $this->select('id_partners', 'id_companies')->where('id', $id_property)->first();
        return $var;
    }

    function getExistsBalance($id_property, $id_companies, $id_partners) {
        $existbalance = $this->getPropertySettings($id_property, $id_companies, $id_partners, 'SHOW_BALANCE');
        if (empty($existbalance)) {
            return 0;
        }
        return $existbalance;
    }

    function getCustomerServiceFrom($id_property, $id_companies, $id_partners) {
        $customerservice = $this->getPropertySettings($id_property, $id_companies, $id_partners, 'TICKETFROM');
        if (empty($customerservice)) {
            return 'customerservice@revopay.com';
        }
        return $customerservice;
    }

    function getUniqAuthCode($num) {
        $string = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ";
        $lenght = strlen($string) - 1;
        return substr($string, rand(0, $lenght), 1) .
                substr($string, rand(0, $lenght), 1) .
                substr($string, rand(0, $lenght), 1) .
                substr($string, rand(0, $lenght), 1) . $num;
    }

    function set1PropertyInfo($property_id, $key, $value) {
        DB::table('properties')->where('id', '=', $property_id)->update(array($key => $value));
    }

    public function getPropertyUpdate($data = array(), $id){

        $company = DB::table($this->table)
            ->select($data)
            ->where('id',$id)
            ->first();

        return json_decode(json_encode($company),true);
    }

    function get1PropertyInfo($property_id, $key) {
        $info = DB::table('properties')->select($key)->where('id', '=', $property_id)->first();
        $info = (object) $info;
        if (isset($info->$key)) {
            return $info->$key;
        }
    }

    function getLayoutID($property_id) {

        $result = DB::table('properties')->select('playout_id')->where('id', '=', $property_id)->first();

        if (!empty($result) && $result->playout_id > 0) {
            return $result->playout_id;
        }


        $id_company = $this->get1PropertyInfo($property_id, 'id_companies');
        $result = DB::table('companies')->select('clayout_id')->where('id', '=', $id_company)->first();
        $result = (object) $result;
        if (isset($result->clayout_id)) {
            if ($result->clayout_id > 0) {
                return $result->clayout_id;
            }
        }
        $id_partner = $this->get1PropertyInfo($property_id, 'id_partners');
        $result = DB::table('partners')->select('layout_id')->where('id', '=', $id_partner)->first();
        $result = (object) $result;
        if (isset($result->layout_id)) {
            return $result->layout_id;
        }
    }

    function getCompaniesLevel($idLevel) {
        $idgroups = DB::table('companies_settings_groups')->where('id_companies', '=', $idLevel)->select('id_groups')->first();
        return $idgroups['id_groups'];
    }

    function getPropertiesLevel($idLevel) {
        $idgroups = DB::table('properties_settings_groups')->where('id_properties', '=', $idLevel)->select('id_groups')->first();
        return $idgroups['id_groups'];
    }

    function getLabels_Layout($layout_id) {
        $labels = DB::table('labels_layout_values')->select('layout_name', 'layout_value')->where('id_layout', $layout_id)->get();
        $array_label = array();
        for ($i = 0; $i < count($labels); $i++) {
            $array_label[$labels[$i]->layout_name] = $labels[$i]->layout_value;
        }

//function to add defaults values
        $this->Add_Default_Layout($array_label);

        return $array_label;
    }

    function get1Labels_Layout($layout_id, $layout_name) {
        $labels = DB::table('labels_layout_values')->select('layout_value')->where('id_layout', $layout_id)->where('layout_name', $layout_name)->first();
        return $labels['layout_value'];
    }

    function getUserGuideSetting($idproperty, $idcompany, $idpartner) {
        $value = $this->getPropertySettings($idproperty, $idcompany, $idpartner, 'USERGUIDE');
        if (empty($value)) {
            //$value = 'http://customerservice.revopayments.com';
            $value = 'https://revopay.freshdesk.com/support/solutions/36000115481';
        }
        return $value;
    }

    function getTitleAccountSetting($idproperty, $idcompany, $idpartner) {
        $value = $this->getPropertySettings($idproperty, $idcompany, $idpartner, 'payment_number_reg_number');
        if (empty($value)) {
            $value = 'Account #';
        }
        return $value;
    }

    function getFraudControlrules($idproperty) {
        $value = DB::table('fraud_control')->where('property_id', $idproperty)->select('data')->first();
        $value = (array) $value;
        if (!empty($value)) {
            $value = $value['data'];
        } else {
            $value = '{"fraud1a":"checked","fraud2a":"checked","fraud3a":"checked","fraud4a":"checked","fraud5a":"checked","fraud6a":"checked","fraud7a":"checked","fraud8a":"checked","fraud9a":"checked","fraud10a":"unchecked","fraud1":"100","fraud2":"3","fraud3":"300","fraud4":"300","fraud5":"150","fraud7":"100","fraud8":"100","fraud9":"15.00","fraud10":"100"}';
        }

        $fraudC = json_decode($value, true);
        return $fraudC;
    }

    function getIdByCompanyComposite($idcompany, $pid) {
        $result = $this->where('id_companies', '=', $idcompany)->where('compositeID_clients', '=', $pid)->select('id')->first();
        if (empty($result)) {
            return 0;
        }
        return $result['id'];
    }

    function getIdByPartnerComposite($idcompany, $pid) {
        $result = $this->where('id_partners', '=', $idcompany)->where('compositeID_clients', '=', $pid)->select('id')->first();
        if (empty($result)) {
            return 0;
        }
        return $result['id'];
    }

    function getIdByPartnerIdentifier($idcompany, $pid, $type) {
        switch ($type) {
            case 0:
                $result = $this->where('status_clients', 1)->where('id_partners', '=', $idcompany)->where('compositeID_clients', '=', $pid)->select('id')->first();
                break;
            case 1:
                $result = $this->where('status_clients', 1)->where('id_partners', '=', $idcompany)->where('lockbox_id', '=', $pid)->select('id')->first();
                break;
            case 2:
                $result = $this->where('status_clients', 1)->where('id_partners', '=', $idcompany)->where('misc_field', '=', $pid)->select('id')->first();
                break;
            case 3:
                $result = $this->where('status_clients', 1)->where('id_partners', '=', $idcompany)->where('bank_id', '=', $pid)->select('id')->first();
                break;
        }
        if (empty($result)) {
            return 0;
        }
        return $result['id'];
    }

    function getIdByCompanyIdentifier($idcompany, $pid, $type) {
        switch ($type) {
            case 0:
                $result = $this->where('id_companies', '=', $idcompany)->where('compositeID_clients', '=', $pid)->select('id')->first();
                break;
            case 1:
                $result = $this->where('id_companies', '=', $idcompany)->where('lockbox_id', '=', $pid)->select('id')->first();
                break;
            case 2:
                $result = $this->where('id_companies', '=', $idcompany)->where('misc_field', '=', $pid)->select('id')->first();
                break;
            case 3:
                $result = $this->where('id_companies', '=', $idcompany)->where('bank_id', '=', $pid)->select('id')->first();
                break;
        }
        if (empty($result)) {
            return 0;
        }
        return $result['id'];
    }

//asking is this merchant has active vault function
    function isInactiveVault($property_id, $isrecurring) {
        $result = DB::table('merchant_account')->where('property_id', $property_id)->whereIn('payment_method', ['cc', 'amex'])->whereIn('gateway', ['nmi', 'fde4'])->where('novault', 1)->where('is_recurring', $isrecurring)->select('merchant_account_id')->first();
        $result1 = DB::table('merchant_account')->where('property_id', $property_id)->whereIn('payment_method', ['swipe'])->whereIn('gateway', ['nmi'])->where('novault', 1)->where('is_recurring', $isrecurring)->select('merchant_account_id')->first();
        if (!empty($result->merchant_account_id)) {
            return 1;
        } elseif (!empty($result1->merchant_account_id)) {
            return 1;
        } else {
            return 0;
        }
    }

//function to get the property url by Pankaj Pandey 29/10/2015
    public function getPropertyUrlById($property_id) {

        $propertyurl = DB::table($this->table)
                ->where('id', '=', $property_id)
                ->select('url_clients')
                ->get();

        return $propertyurl;
    }

    public function getPropertyNameById($property_id) {

        $propertyname = DB::table($this->table)
                ->where('id', '=', $property_id)
                ->select('name_clients')
                ->get();

        return $propertyname;
    }

    public function getPropertyEmailById($property_id) {

        $propertyemail = DB::table($this->table)
                ->where('id', '=', $property_id)
                ->select('accounting_email_address_clients')
                ->get();

        return $propertyemail;
    }

    public function hasETerminal($idproperty) {
        $result = DB::table('merchant_account')->where('property_id', '=', $idproperty)->where('payment_method', 'LIKE', 'eterm%')->count();
        if ($result > 0) {
            return true;
        }
        return false;
    }

    public function howMany($idpartner) {
        $result = $this->where('id_partners', '=', $idpartner)->count();
        if (empty($result)) {
            return 0;
        }
        return $result;
    }

    public function getMerchantsByPartner($idpartner) {
        $result = $this->where('id_partners', '=', $idpartner)->select('id', 'name_clients', 'id_companies', 'compositeID_clients')->get();
        return $result;
    }

    public function getMerchantsByCompany($idcompany) {
        $result = $this->where('id_companies', '=', $idcompany)->select('id', 'name_clients', 'id_companies', 'compositeID_clients')->get();
        return $result;
    }

    public function getMerchantById($id) {
        $result = $this->where('id', '=', $id)->get();
        return $result;
    }

    public function getMaxByType($idproperty, $type) {
        $result = DB::table('merchant_account')->where('property_id', '=', $idproperty)->where('payment_method', '=', $type)->max('high_pay_range');
        if (empty($result)) {
            return 0;
        }
        return $result;
    }

    public function createProperty($data) {
        $result = DB::table($this->table)->insertGetId($data);
        return $result;
    }

    public function getMerchantList($idlevel, $level, $whereCondition = array()) {

        $merchants = array();

        switch (strtoupper($level)) {
            case "B":
                $query = DB::table($this->table)
                        ->join('partners', 'partners.id', '=', 'properties.id_partners')
                        ->join('companies', 'companies.id', '=', 'properties.id_companies')
                        ->select('properties.id', 'partners.partner_title as partner', 'companies.company_name as group', 'properties.name_clients as merchant', 'properties.compositeID_clients as merchant_id', 'properties.units', DB::raw('\'0\' as a_users'), DB::raw('\'0\' as a2a_users'), 'companies.compositeID_companies as group_id', DB::raw('\'0\' as del_users'), DB::raw('\'0\' as ai_users'), DB::raw('\'0\' as na2a_users'), 'status_pp as status')
                        ->orderBy('merchant', 'asc');

//building search parameter if any only for export
                if (!empty($whereCondition)) {
                    foreach ($whereCondition as $key => $value) {
                        if (stristr($value, 'date')) {
                            $valueArray = explode('=', $value);
                            if ($valueArray[1] != '') {
                                $date = explode('/', $valueArray[1]);
                                $daterangecondition[] = $date[2] . '-' . $date[0] . '-' . $date[1];
                            }
                        } else {
                            $valueArray = explode('=', $value);
                            if ($valueArray[0] != 'search') {
                                if (($valueArray[0] == 'cc_svc') && ($valueArray[1] != '')) {
                                    if ($valueArray[1] == '0') {
                                        $query->whereNotIn('properties.id', function ($query) {
                                            $query->from('merchant_account');
                                            $query->select('property_id');
                                            $query->where('payment_method', 'like', '%cc%');
                                        });
                                    } else {
                                        $query->whereIn('properties.id', function ($query) {
                                            $query->from('merchant_account');
                                            $query->select('property_id');
                                            $query->where('payment_method', 'like', '%cc%');
                                        });
                                    }
                                } elseif (($valueArray[0] == 'ec_svc') && ($valueArray[1] != '')) {
                                    if ($valueArray[1] == '0') {
                                        $query->whereNotIn('properties.id', function ($query) {
                                            $query->from('merchant_account');
                                            $query->select('property_id');
                                            $query->where('payment_method', 'like', '%ec%');
                                            $query->orWhere('payment_method', '=', 'ebill');
                                        });
                                    } else {
                                        $query->whereIn('properties.id', function ($query) {
                                            $query->from('merchant_account');
                                            $query->select('property_id');
                                            $query->where('payment_method', 'like', '%ec%');
                                            $query->orWhere('payment_method', '=', 'ebill');
                                        });
                                    }
                                } elseif (($valueArray[0] != 'ec_svc') && ($valueArray[0] != 'cc_svc')) {
                                    $query->whereRaw($value);
                                }
                            }
                        }
                    }
                }
                $merchants = $query;
                break;
            case "P":
                $query = DB::table($this->table)
                        ->join('partners', 'partners.id', '=', 'properties.id_partners')
                        ->join('companies', 'companies.id', '=', 'properties.id_companies')
                        ->select('properties.id', 'partners.partner_title as partner', 'companies.company_name as group', 'properties.name_clients as merchant', 'properties.compositeID_clients as merchant_id', 'properties.units', DB::raw('\'0\' as a_users'), DB::raw('\'0\' as a2a_users'), 'companies.compositeID_companies as group_id', DB::raw('\'0\' as del_users'), DB::raw('\'0\' as ai_users'), DB::raw('\'0\' as na2a_users'), 'status_pp as status')
                        ->orderBy('merchant', 'asc')
                        ->whereIn('properties.id_partners', explode('!', $idlevel));
//building search parameter if any only for export

                if (!empty($whereCondition)) {
                    foreach ($whereCondition as $key => $value) {
                        if (stristr($value, 'date')) {
                            $valueArray = explode('=', $value);
                            if ($valueArray[1] != '') {
                                $date = explode('/', $valueArray[1]);
                                $daterangecondition[] = $date[2] . '-' . $date[0] . '-' . $date[1];
                            }
                        } else {
                            $valueArray = explode('=', $value);
                            if ($valueArray[0] != 'search') {
                                if (($valueArray[0] == 'cc_svc') && ($valueArray[1] != '')) {
                                    if ($valueArray[1] == '0') {
                                        $query->whereNotIn('properties.id', function ($query) {
                                            $query->from('merchant_account');
                                            $query->select('property_id');
                                            $query->where('payment_method', 'like', '%cc%');
                                        });
                                    } else {
                                        $query->whereIn('properties.id', function ($query) {
                                            $query->from('merchant_account');
                                            $query->select('property_id');
                                            $query->where('payment_method', 'like', '%cc%');
                                        });
                                    }
                                } elseif (($valueArray[0] == 'ec_svc') && ($valueArray[1] != '')) {
                                    if ($valueArray[1] == '0') {
                                        $query->whereNotIn('properties.id', function ($query) {
                                            $query->from('merchant_account');
                                            $query->select('property_id');
                                            $query->where('payment_method', 'like', '%ec%');
                                            $query->orWhere('payment_method', '=', 'ebill');
                                        });
                                    } else {
                                        $query->whereIn('properties.id', function ($query) {
                                            $query->from('merchant_account');
                                            $query->select('property_id');
                                            $query->where('payment_method', 'like', '%ec%');
                                            $query->orWhere('payment_method', '=', 'ebill');
                                        });
                                    }
                                } elseif (($valueArray[0] != 'ec_svc') && ($valueArray[0] != 'cc_svc')) {
                                    $query->whereRaw($value);
                                }
                            }
                        }
                    }
                }
                $merchants = $query;
                break;
            case "G":
                $query = DB::table($this->table)
                        ->join('partners', 'partners.id', '=', 'properties.id_partners')
                        ->join('companies', 'companies.id', '=', 'properties.id_companies')
                        ->select('properties.id', 'partners.partner_title as partner', 'companies.company_name as group', 'properties.name_clients as merchant', 'properties.compositeID_clients as merchant_id', 'properties.units', DB::raw('\'0\' as a_users'), DB::raw('\'0\' as a2a_users'), 'companies.compositeID_companies as group_id', DB::raw('\'0\' as del_users'), DB::raw('\'0\' as ai_users'), DB::raw('\'0\' as na2a_users'), 'status_pp as status')
                        ->orderBy('merchant', 'asc')
                        ->whereIn('properties.id_companies', explode('!', $idlevel));
//building search parameter if any only for export

                if (!empty($whereCondition)) {
                    foreach ($whereCondition as $key => $value) {
                        if (stristr($value, 'date')) {
                            $valueArray = explode('=', $value);
                            if ($valueArray[1] != '') {
                                $date = explode('/', $valueArray[1]);
                                $daterangecondition[] = $date[2] . '-' . $date[0] . '-' . $date[1];
                            }
                        } else {
                            $valueArray = explode('=', $value);
                            if ($valueArray[0] != 'search') {
                                if (($valueArray[0] == 'cc_svc') && ($valueArray[1] != '')) {
                                    if ($valueArray[1] == '0') {
                                        $query->whereNotIn('properties.id', function ($query) {
                                            $query->from('merchant_account');
                                            $query->select('property_id');
                                            $query->where('payment_method', 'like', '%cc%');
                                        });
                                    } else {
                                        $query->whereIn('properties.id', function ($query) {
                                            $query->from('merchant_account');
                                            $query->select('property_id');
                                            $query->where('payment_method', 'like', '%cc%');
                                        });
                                    }
                                } elseif (($valueArray[0] == 'ec_svc') && ($valueArray[1] != '')) {
                                    if ($valueArray[1] == '0') {
                                        $query->whereNotIn('properties.id', function ($query) {
                                            $query->from('merchant_account');
                                            $query->select('property_id');
                                            $query->where('payment_method', 'like', '%ec%');
                                            $query->orWhere('payment_method', '=', 'ebill');
                                        });
                                    } else {
                                        $query->whereIn('properties.id', function ($query) {
                                            $query->from('merchant_account');
                                            $query->select('property_id');
                                            $query->where('payment_method', 'like', '%ec%');
                                            $query->orWhere('payment_method', '=', 'ebill');
                                        });
                                    }
                                } elseif (($valueArray[0] != 'ec_svc') && ($valueArray[0] != 'cc_svc')) {
                                    $query->whereRaw($value);
                                }
                            }
                        }
                    }
                }
                $merchants = $query;
                break;
            case "M":
                $query = DB::table($this->table)
                        ->join('partners', 'partners.id', '=', 'properties.id_partners')
                        ->join('companies', 'companies.id', '=', 'properties.id_companies')
                        ->select('properties.id', 'partners.partner_title as partner', 'companies.company_name as group', 'properties.name_clients as merchant', 'properties.compositeID_clients as merchant_id', 'properties.units', DB::raw('\'0\' as a_users'), DB::raw('\'0\' as a2a_users'), 'companies.compositeID_companies as group_id', DB::raw('\'0\' as del_users'), DB::raw('\'0\' as ai_users'), DB::raw('\'0\' as na2a_users'), 'status_pp as status')
                        ->orderBy('merchant', 'asc')
                        ->whereIn('properties.id', explode('!', $idlevel));
//building search parameter if any only for export

                if (!empty($whereCondition)) {
                    foreach ($whereCondition as $key => $value) {
                        if (stristr($value, 'date')) {
                            $valueArray = explode('=', $value);
                            if ($valueArray[1] != '') {
                                $date = explode('/', $valueArray[1]);
                                $daterangecondition[] = $date[2] . '-' . $date[0] . '-' . $date[1];
                            }
                        } else {
                            $valueArray = explode('=', $value);
                            if ($valueArray[0] != 'search') {
                                if (($valueArray[0] == 'cc_svc') && ($valueArray[1] != '')) {
                                    if ($valueArray[1] == '0') {
                                        $query->whereNotIn('properties.id', function ($query) {
                                            $query->from('merchant_account');
                                            $query->select('property_id');
                                            $query->where('payment_method', 'like', '%cc%');
                                        });
                                    } else {
                                        $query->whereIn('properties.id', function ($query) {
                                            $query->from('merchant_account');
                                            $query->select('property_id');
                                            $query->where('payment_method', 'like', '%cc%');
                                        });
                                    }
                                } elseif (($valueArray[0] == 'ec_svc') && ($valueArray[1] != '')) {
                                    if ($valueArray[1] == '0') {
                                        $query->whereNotIn('properties.id', function ($query) {
                                            $query->from('merchant_account');
                                            $query->select('property_id');
                                            $query->where('payment_method', 'like', '%ec%');
                                            $query->orWhere('payment_method', '=', 'ebill');
                                        });
                                    } else {
                                        $query->whereIn('properties.id', function ($query) {
                                            $query->from('merchant_account');
                                            $query->select('property_id');
                                            $query->where('payment_method', 'like', '%ec%');
                                            $query->orWhere('payment_method', '=', 'ebill');
                                        });
                                    }
                                } elseif (($valueArray[0] != 'ec_svc') && ($valueArray[0] != 'cc_svc')) {
                                    $query->whereRaw($value);
                                }
                            }
                        }
                    }
                }
                $merchants = $query;
                break;
        }

        if ($level == 'P') {

        }

        return $merchants;
    }

    public function getUsersbyStatusProperty($idproperty, $status) {

        $webusercount = DB::table('web_users')
                ->select(DB::raw('count(*) as web_user_count'))
                ->where('web_status', '=', $status)
                ->where('property_id', '=', $idproperty)
                ->get();

        if (!empty($webusercount)) {
            return $webusercount[0]->web_user_count;
        } else {
            return 0;
        }
    }

    public function hasCC($idproperty) {

        $cantpayments = DB::table('merchant_account')
                ->select('*')
                ->where('payment_method', '=', 'cc')
                ->where('property_id', '=', $idproperty)
                ->get();

        if (count($cantpayments) > 0) {
            return true;
        } else {
            return false;
        }
    }

    public function hasEC($idproperty) {

        $cantpayments = DB::table('merchant_account')
                ->select('*')
                ->where('payment_method', '=', 'ec')
                ->where('property_id', '=', $idproperty)
                ->get();

        if (count($cantpayments) > 0) {
            return true;
        } else {
            return false;
        }
    }

    public function hasEV($idproperty) {

        $cantpayments = DB::table('merchant_account')
                ->select('*')
                ->where('payment_method', '=', 'ebill')
                ->where('property_id', '=', $idproperty)
                ->get();

        if (count($cantpayments) > 0) {
            return true;
        } else {
            return false;
        }
    }

    public function hasInv($idproperty) {

        $property_detail = DB::table($this->table)
                ->select('properties.id_partners', 'properties.id_companies')
                ->where('properties.id', '=', $idproperty)
                ->get();
        if (!empty($property_detail)) {
            $propertysettingval = $this->getPropertySettings($idproperty, $property_detail[0]['id_companies'], $property_detail[0]['id_partners'], 'EINVOICE');
            if ($propertysettingval > 0) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    public function isB2B($idproperty) {

        $property_detail = DB::table($this->table)
                ->select('properties.id_partners')
                ->where('properties.id', '=', $idproperty)
                ->get();

        if (!empty($property_detail)) {
            $partner_detail = DB::table('partners')
                    ->select('layout_id')
                    ->where('partners.id', '=', $property_detail[0]['id_partners'])
                    ->get();

            if (!empty($partner_detail)) {
                if ($partner_detail[0]['layout_id'] == 6) {
                    return true;
                } else {
                    return false;
                }
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    public function getMerchantDetail($idlevel, $level, $merchant_id) {

        $merchantdetail = array();
        if ($level == 'P') {
            $query = DB::table($this->table)
                    ->select('*')
                    ->where('properties.id', '=', $merchant_id);

            $merchantdetail = $query->get();
        }
        return $merchantdetail;
    }

    public function getMerchantPaymentCredential($idlevel, $level, $merchant_pay_id) {

        $merchantpaymentcredentialdetail = array();
        if ($level == 'P') {
            $query = DB::table('merchant_account')
                    ->select('*')
                    ->where('merchant_account.merchant_account_id', '=', $merchant_pay_id);

            $merchantpaymentcredentialdetail = $query->get();
        }
        return $merchantpaymentcredentialdetail;
    }

    public function updateMerchantDetail($merchantdata = array()) {

        DB::table($this->table)
                ->where('id', $merchantdata['id'])
                ->update(['last_updated_by' => 'admin', 'name_clients' => str_replace("'", '', $merchantdata['name_clients']), 'compositeID_clients' => str_replace("'", '', $merchantdata['compositeID_clients']), 'address_clients' => str_replace("'", '', $merchantdata['address_clients']), 'city_clients' => str_replace("'", '', $merchantdata['city_clients']), 'state_clients' => $merchantdata['state_clients'], 'zip_clients' => str_replace("'", '', $merchantdata['zip_clients']), 'contact_name_clients' => str_replace("'", '', $merchantdata['contact_name_clients']), 'email_address_clients' => str_replace("'", '', $merchantdata['email_address_clients']), 'units' => $merchantdata['units'], 'accounting_email_address_clients' => str_replace("'", '', $merchantdata['accounting_email_address_clients']), 'phone_clients' => $merchantdata['phone_clients']]);

        return true;
    }

    public function getMerchantProfile($idlevel, $merchant_id, $level) {

        $merchantdetail = array();
        $merchantdetail = $this->getPropertyInfo($merchant_id);
        return $merchantdetail;
    }

    public function getGroupsfromProperty($merchant_id) {

        $merchant_groups = array();
        $partner_id = $this->getPartnerIDByProperty($merchant_id);
        if ($partner_id) {
            $query = DB::table('companies')
                    ->select('id', 'company_name')
                    ->where('companies.id_partners', '=', $partner_id);

            $merchant_groups = $query->get();
            if (!empty($merchant_groups)) {
                return $merchant_groups;
            }
        }
        return $merchant_groups;
    }

    public function getPartnerIDByProperty($merchant_id) {

        $partner_id = array();
        $query = DB::table($this->table)
                ->select('id_partners')
                ->where('properties.id', '=', $merchant_id);
        $partner_id = $query->first();



        if (!empty($partner_id)) {
            return $partner_id->id_partners;
        }
        return false;
    }

    public function getCompanyPartnerIDbyMID($merchant_id) {

        $info = array();
        $query = DB::table($this->table)
                ->select('id_partners', 'id_companies')
                ->where('properties.id', '=', $merchant_id);
        $info = $query->get();
        if (!empty($info)) {
            return $info;
        }
        return false;
    }

    public function getMerchantAccountByPropertyId($idlevel, $propertyId, $level) {

        $merchant_account_info = array();
        $query = DB::table('merchant_account')
                ->where('merchant_account.property_id', '=', $propertyId);
        $merchant_account_info = $query;
        return $merchant_account_info;
    }

    public function getApplicationByPropertyId($idlevel, $propertyId, $level) {

        $application_lists = array();
        $query = DB::table($this->table)
                ->join('application', 'application.id_property', '=', 'properties.id')
                ->select('application.id', 'properties.name_clients as merchant', 'application.status', 'datenew as date')
                ->where('application.id_property', '=', $propertyId);
        $application_lists = $query;
        return $application_lists;
    }

    public function getEventHistoryByPropertyId($idlevel, $propertyId, $level) {

        $event_history_list = array();
        $query = DB::table('global_events')
                ->select('global_events.id', 'global_events.description', 'global_events.errortype', 'global_events.date')
                ->where('global_events.id_property', '=', $propertyId);
        $event_history_list = $query;
        return $event_history_list;
    }

    public function getContractsHistoryByPropertyId($idlevel, $propertyId, $level) {

        $contracts_history = array();
        $query = DB::table('contracts')
                ->where('contracts.property_id', '=', $propertyId);
        $contracts_history = $query;
        return $contracts_history;
    }

    public function getIVRAccountByPropertyId($idlevel, $propertyId, $level) {

        $ivr_account = array();
        $query = DB::table('IVR')
                ->where('IVR.id_property', '=', $propertyId);
        $ivr_account = $query;
        return $ivr_account;
    }

    public function getVelocityByPropertyId($idlevel, $propertyId, $level) {

        $velocities = array();
        $query = DB::table('velocities')
                ->select('id_v as id', 'velocities.*')
                ->where('velocities.id_property', '=', $propertyId);
        $velocities = $query;
        return $velocities;
    }

    public function getTicketReportByPropertyId($idlevel, $propertyId, $level) {

        $ticket_reports = array();
        $query = DB::table('tickets')
                ->select('ticket_id as id', 'ticket_date_submitted as date', 'ticket_name as name', 'ticket_lastname as lastname', 'ticket_email as email', 'ticket_phone as phone', 'ticket_type as type', 'ticket_status as status', 'ticket_user_type as reqby', 'ticket_user_id as id_user', 'flag')
                ->where('tickets.ticket_property', '=', $propertyId);
        $ticket_reports = $query;
        return $ticket_reports;
    }

    public function getFraudControlConfigByPropertyId($idlevel, $propertyId, $level) {

        $fraud_control = DB::table('fraud_control')
                ->select('data')
                ->where('fraud_control.property_id', '=', $propertyId)
                ->orderBy('id', 'desc')
                ->first();
        return $fraud_control;
    }

    public function updateMerchantGroup($idgroup, $id_property) {

        DB::table('properties')
                ->where('id', $id_property)
                ->update(['id_companies' => $idgroup]);

        return true;
    }

    public function updateMerchantPayCredentials($pay_credential_data = array()) {

        DB::table('merchant_account')
                ->where('merchant_account_id', $pay_credential_data['id'])
                ->update(['description' => $pay_credential_data['description'], 'payment_method' => $pay_credential_data['payment_method'], 'gateway' => $pay_credential_data['gateway'], 'payment_source_merchant_id' => $pay_credential_data['payment_source_merchant_id'], 'payment_source_key' => $pay_credential_data['payment_source_key'], 'payment_source_store_id' => $pay_credential_data['payment_source_store_id'], 'payment_source_location_id' => $pay_credential_data['payment_source_location_id'], 'low_pay_range' => $pay_credential_data['low_pay_range'], 'high_pay_range' => $pay_credential_data['high_pay_range'], 'high_ticket' => $pay_credential_data['high_ticket'], 'convenience_fee' => $pay_credential_data['convenience_fee'], 'is_recurring' => $pay_credential_data['is_recurring'], 'tag' => $pay_credential_data['tag']]);

        return true;
    }

    public function CreateOrUpdateProperty($propInfo) {
        if (isset($propInfo['payors'])) {
            unset($propInfo['payors']);
        }
        if (isset($propInfo['url_clients'])) {
            unset($propInfo['url_clients']);
        }
        if (!isset($propInfo['last_updated_by'])) {
            $propInfo['last_updated_by'] = 'api2';
        }

        $result = array();
        if (isset($propInfo['compositeID_clients']) && !empty($propInfo['compositeID_clients'])) {
            $propId = DB::table($this->table)
                    ->where('id_companies', $propInfo['id_companies'])
                    ->where('compositeID_clients', $propInfo['compositeID_clients'])
                    ->select('id')
                    ->first();
            if (!empty($propId['id'])) {
                if (isset($propInfo['subdomain_clients'])) {
                    unset($propInfo['subdomain_clients']);
                }
                if (isset($propInfo['id'])) {
                    unset($propInfo['id']);
                }
                DB::table($this->table)->where('id', $propId['id'])->update($propInfo);
                $result['type'] = 'updated';
                $result['id'] = $propId['id'];
            } else {
                if (isset($propInfo['id'])) {
                    unset($propInfo['id']);
                }
                $id = DB::table($this->table)->insertGetId($propInfo);
                $result['type'] = 'add';
                $result['id'] = $id;
            }
        } elseif (isset($propInfo['paypointID']) && !empty($propInfo['paypointID'])) {
            $propId = DB::table($this->table)
                    ->where('id_companies', $propInfo['id_companies'])
                    ->where('compositeID_clients', $propInfo['paypointID'])
                    ->select('id')
                    ->first();
            if (!empty($propId['id'])) {
                if (isset($propInfo['subdomain_clients'])) {
                    unset($propInfo['subdomain_clients']);
                }
                if (isset($propInfo['id'])) {
                    unset($propInfo['id']);
                }
                $propInfo['compositeID_clients'] = $propInfo['paypointID'];
                unset($propInfo['paypointID']);
                DB::table($this->table)->where('id', $propId['id'])->update($propInfo);
                $result['type'] = 'updated';
                $result['id'] = $propId['id'];
            } else {
                if (isset($propInfo['id'])) {
                    unset($propInfo['id']);
                }
                $propInfo['compositeID_clients'] = $propInfo['paypointID'];
                unset($propInfo['paypointID']);
                $id = DB::table($this->table)->insertGetId($propInfo);
                $result['type'] = 'add';
                $result['id'] = $id;
            }
        } else {
            $result['type'] = 'bad';
            $result['id'] = 0;
        }

        return $result;
    }

    public function getPropertiesByCompanyId($id_company) {

        return DB::table($this->table)->where('id_companies', $id_company)->get();
    }

    public function updateMerchant($id, $data) {
        DB::table($this->table)->where('id', '=', $id)->update($data);
    }

    public function Add_Default_Layout(&$labels) {
//first_name
        if (!isset($labels['first_name'])) {
            $labels['first_name'] = "First Name";
        }

//last_name
        if (!isset($labels['last_name'])) {
            $labels['last_name'] = "Last Name";
        }
    }

    function getPropertyIDbyVANTIV($mid) {
        $result = DB::table('merchant_account')->where('payment_source_merchant_id', '=', substr(trim($mid), -9))->select('property_id')->first();
        if (!empty($result)) {
            return $result['property_id'];
        }
        return 0;
    }

    function getAllMerchantAccount() {
        $data = DB::table('merchant_account')
                ->select('merchant_account_id', 'property_id', 'partner_title', 'company_name', 'name_clients', 'payment_method', 'gateway', 'is_recurring', 'low_pay_range', 'high_pay_range', 'high_ticket', 'payment_source_merchant_id', 'payment_source_store_id', 'payment_source_location_id', 'payment_source_key', 'convenience_fee', 'convenience_fee_float', 'convenience_fee_drp', 'convenience_fee_float_drp')
                ->join('properties', 'merchant_account.property_id', '=', 'properties.id')
                ->join('partners', 'properties.id_partners', '=', 'partners.id')
                ->join('companies', 'properties.id_companies', '=', 'companies.id')
        ;
        return $data;
    }

    function getMerchantAccountByLevelIdlevel() {

        $query = DB::table('merchant_account')
                ->select('merchant_account_id', 'property_id', 'partner_title', 'company_name', 'name_clients', 'payment_method', 'gateway', 'is_recurring', 'low_pay_range', 'high_pay_range', 'high_ticket', 'payment_source_merchant_id', 'payment_source_store_id', 'payment_source_location_id', 'payment_source_key', 'convenience_fee', 'convenience_fee_float', 'convenience_fee_drp', 'convenience_fee_float_drp')
                ->join('properties', 'merchant_account.property_id', '=', 'properties.id')
                ->join('partners', 'properties.id_partners', '=', 'partners.id')
                ->join('companies', 'properties.id_companies', '=', 'companies.id');

        return $query;
    }

    function getMerchantAccount($id) {
        $data = DB::table('merchant_account')
                        ->select('partner_title as Partner', 'company_name as Group', 'name_clients as Merchant', 'payment_method as Payment_Method', 'gateway', 'is_recurring', 'low_pay_range', 'high_pay_range', 'high_ticket', 'convenience_fee', 'convenience_fee_drp', 'convenience_fee_float', 'convenience_fee_float_drp')
                        ->join('properties', 'merchant_account.property_id', '=', 'properties.id')
                        ->join('partners', 'properties.id_partners', '=', 'partners.id')
                        ->join('companies', 'properties.id_companies', '=', 'companies.id')
                        ->where('merchant_account_id', '=', $id)->first();
        ;
        return $data;
    }

    /**
     * Gets all the id_property with a given setting key and setting value
     * @param string $key
     * @param string $value
     * @return array
     */
    public function getIdPropertyBySetting($key, $value) {
        $properties = DB::table($this->table)
                ->select('properties.id')
                ->join('properties_settings_groups', 'properties_settings_groups.id_properties', '=', 'properties.id')
                ->join('settings_values', 'settings_values.id_groups', '=', 'properties_settings_groups.id_settings_groups')
                ->where('settings_values.key', $key)
                ->where('settings_values.value', $value);

        $companies = DB::table($this->table)
                ->select('properties.id')
                ->join('companies_settings_groups', 'companies_settings_groups.id_companies', '=', 'properties.id_companies')
                ->join('settings_values', 'settings_values.id_groups', '=', 'companies_settings_groups.id_settings_groups')
                ->where('settings_values.key', $key)
                ->where('settings_values.value', $value);

        $partners = DB::table($this->table)
                ->select('properties.id')
                ->join('partners_settings_groups', 'partners_settings_groups.id_partners', '=', 'properties.id_partners')
                ->join('settings_values', 'settings_values.id_groups', '=', 'partners_settings_groups.id_settings_groups')
                ->where('settings_values.key', $key)
                ->where('settings_values.value', $value);


        return $partners->union($properties)
                        ->union($companies)
                        ->get();
    }

    /**
     * get the ids of all properties by status
     * @return array
     */
    function getAllPropertiesIds($status) {
        $result = DB::table('properties')
                ->where('status_pp', $status)
                ->select('id', 'id_companies', 'id_partners')
                ->get();
        return $result;
    }

    function getCardTypeFee($ccnumber, $cctype) {
        $obj_bin = new Bin();
        $type = $obj_bin->getBinCardInfo($ccnumber);
        $card_bin_type = "";

        if ($cctype == 'Visa') {
            $card_bin_type = 'v';
        } else {
            if ($cctype == 'MasterCard') {
                $card_bin_type = 'mc';
            } else {
                if ($cctype == 'Discover') {
                    $card_bin_type = 'd';
                }
            }
        }

        if ($type != null) {
            $type = (array) $type;
            if ($type['DebitCard'] == 1 || $type['GiftCard'] == 1) {
                $card_bin_type .= 'db';
            } else {
                if ($type['CreditCard'] == 1) {
                    $card_bin_type .= 'c';
                }
            }
        }
        return $card_bin_type;
    }

    function getBillAmmount($web_user_id) {
        $query = DB::table('web_users_category')
                ->select('description', 'bill_amount')
                ->where('web_user_id', $web_user_id)
                ->where('bill_amount', '>', 0)
                ->get();
        return $query;
    }

    function getServicesTypeByProperty($id_property) {
        $query = DB::table('service_type')
                ->join('service_fee', 'service_fee.service_type', '=', 'service_type.id_type')
                ->where('id_property', $id_property)
                ->where('status', 1)
                ->select('id_type', 'description')
                ->get();

        return $query;
    }

    function find_company($proId) {
        $pro_res = $this->find($proId);
        return Companies::find($pro_res->id_companies);
    }

    function getMerchantByFilter($level, $idlevel, $filter = null) {
        if ($level == 'B' || $level == 'A') {
            if ($level == 'B') {
                $partnersA = DB::table('branch_partner')->where('branch_id', $idlevel)->select('id_partners')->get();
                $partners = array();
                foreach ($partnersA as $pa) {
                    $partners[] = $pa->id_partners;
                }
            }
            $query = DB::table($this->table)
                    ->where('properties.status_clients', 1)
                    ->join('partners', 'properties.id_partners', 'partners.id')
                    ->join('companies', 'properties.id_companies', 'companies.id');
            if (isset($partners)) {
                $query->whereIn('properties.id_partners', $partners);
            }
            $query->select('properties.id as id', 'partners.partner_title', 'companies.company_name', 'properties.name_clients as name_client', 'compositeID_clients', 'status_pp', 'date_new', 'units', 'playout_id', 'company_name', 'playout_id');
        } elseif ($level == 'P') {
            $query = DB::table($this->table)
                    ->where('partners.id', $idlevel)
                    ->where('properties.status_clients', 1)
                    ->join('partners', 'properties.id_partners', 'partners.id')
                    ->join('companies', 'properties.id_companies', 'companies.id')
                    ->select('properties.id as id', 'partners.partner_title', 'companies.company_name', 'properties.name_clients as name_client', 'compositeID_clients', 'compositeID_companies as compositeID_companies', 'status_pp', 'date_new', 'units', 'playout_id', 'company_name', 'playout_id');
        } elseif ($level == 'G') {
            $query = DB::table($this->table)
                    ->where('companies.id', $idlevel)
                    ->where('properties.status_clients', 1)
                    ->join('companies', 'properties.id_companies', 'companies.id')
                    ->select('properties.id as id', 'companies.company_name', 'properties.name_clients as name_client', 'compositeID_clients', 'compositeID_companies as compositeID_companies', 'status_pp', 'date_new', 'units', 'playout_id', 'company_name', 'playout_id');
        }
        return $query;
    }

    function deleteMerchant($id) {
        DB::table($this->table)
                ->where('id', $id)
                ->update(['status_clients' => 0]);
    }

    function getMerchantdetail1($id) {

        $merchantdetails = DB::table($this->table)
                ->leftjoin('layout', 'properties.playout_id', 'layout.id_layout')
                ->leftjoin('partners', 'properties.id_partners', 'partners.id')
                ->leftjoin('companies', 'properties.id_companies', 'companies.id')
                ->select('properties.id as id', 'company_id_clients', 'name_clients', 'subdomain_clients', 'compositeID_clients', 'address_clients', 'city_clients', 'state_clients', 'zip_clients', 'email_address_clients', 'accounting_email_address_clients', 'phone_clients', 'url_clients', 'contact_name_clients', 'partners.partner_name as partner_name', 'companies.company_name as company_name', 'properties.logo as logo')
                ->where('properties.id', '=', $id)
                ->first();

        return $merchantdetails;
    }

    function getPropertyIdByCompositeIdClient($compositeidclient, $companyid) {
        $result = DB::table($this->table)
                        ->where('compositeID_clients', $compositeidclient)
                        ->where('id_companies', $companyid)
                        ->select('id')->first();
        if (!empty($result)) {
            return $result->id;
        }
        return null;
    }

    function getBankId($propertyid, $bankid) {
        $result = DB::table('merchant_banking')
                        ->where('property_id', $propertyid)
                        ->where('bank_identifier', $bankid)
                        ->select('id')->first();
        if (!empty($result)) {
            return $result->id;
        }
        return null;
    }

    function getCustomerId($propertyid, $bankid) {
        $result = DB::table('evpay_account')
                        ->where('property_id', $propertyid)
                        ->where('bank_id', $bankid)
                        ->select('customer_id')->first();
        if (!empty($result)) {
            return $result->customer_id;
        }
        return null;
    }

    function getPropertyIdByGroup($id_company) {
        $result = DB::table($this->table)
                        ->where('id_companies', $id_company)
                        ->select('id')
                        ->get()->toArray();
        $property_id = [];
        foreach ($result as $res) {
            $temp = $res->id;
            $property_id [] = $temp;
        }
        return $property_id;
    }

    function getMerchantByGroupId($id) {
        $result = DB::table($this->table)
                ->where('id_companies', $id)
                ->select('id', 'name_clients')
                ->get();
        return $result;
    }

    function getMerchants() {
        $result = DB::table($this->table)->where('status_clients', 1)->get();
        return $result;
    }

    function getPropertyByPGP($paypointid, $id_partner, $id_groups) {
        $result = DB::table($this->table)
                        ->where('compositeID_clients', $paypointid)
                        ->where('id_partners', $id_partner)
                        ->where('id_companies', $id_groups)
                        ->select('id')->first();

        if (!empty($result)) {
            return $result->id;
        } else {
            return 0;
        }
    }

    function moveProperty($idgroup, $idpartner){

        $merchants = DB::table('properties')
            ->where('id_companies', '=', $idgroup)
            ->get();

        $currentpartner = DB::table('properties')
            ->join('partners', 'properties.id_partners', '=', 'partners.id')
            ->where('properties.id_companies', '=', $idgroup)
            ->select('partners.partner_name')
            ->first();

        $newpartner = DB::table('partners')
            ->where('id', '=', $idpartner)
            ->select('partner_name')
            ->first();

        //var_dump($currentpartner->partner_name, $newpartner->partner_name);die;
        if(!empty($merchants)){
            foreach ($merchants as $merchant){
                $url_clients = str_replace("/master/".$currentpartner->partner_name,"/master/".$newpartner->partner_name, $merchant->url_clients);
                DB::table('properties')
                    ->where('id', '=', $merchant->id)
                    ->update([
                        'url_clients' => $url_clients,
                        'id_partners' => $idpartner
                    ]);

            }
        }
    }

}
