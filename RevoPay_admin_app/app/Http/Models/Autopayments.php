<?php

namespace App\Http\Models;

use App\Model\Transations;
use Illuminate\Database\Eloquent\Model;
use DB;

//functions related to Autopayments

class Autopayments extends Model {

    protected $table = 'accounting_recurring_transactions';
    protected $primaryKey = 'trans_id';
    public $timestamps = false;
    public $incrementing = false;

    function getActive($level, $idlevel, $scope1 = '', $scope2 = '') {
        $query = DB::table('accounting_recurring_transactions')->where('trans_status', 1);
        if ($scope1 != '') {
            $query->where('trans_next_post_date', '>=', $scope1);
        }
        if ($scope2 != '') {
            $query->where('trans_next_post_date', '<=', $scope2);
        }
        if ($level == 'M') {
            $query->where('accounting_recurring_transactions.property_id', $idlevel);
        } elseif ($level == 'G') {
            $query->whereIn('accounting_recurring_transactions.property_id', function ($query) use ($idlevel) {
                $query->from('properties');
                $query->select('id as property_id');
                $query->where('id_companies', $idlevel);
            });
        } elseif ($level == 'P') {
            $query->whereIn('accounting_recurring_transactions.property_id', function ($query) use ($idlevel) {
                $query->from('properties');
                $query->select('id as property_id');
                $query->where('id_partners', $idlevel);
            });
        } else if ($level == 'B') {
            $partnersA = DB::table('branch_partner')->where('branch_id', $idlevel)->select('id_partners')->get();
            $partners = array();
            foreach ($partnersA as $pa) {
                $partners[] = $pa->id_partners;
            }
            $query->whereIn('accounting_recurring_transactions.property_id', function ($query) use ($partners) {
                $query->from('properties');
                $query->select('id as property_id');
                $query->whereIn('id_partners', $partners);
            });
        }
        $total = $query->count();
        return $total;
    }

    function getByFilter($level, $idlevel, $filter) {
        return $this->getTransactionsByFilter($level, $idlevel, 0, '', $filter);
    }

    function getXFields($level, $idlevel = 0, $export = false) {
        $obj_layout = new \App\Model\Layout();
        $layouts = $obj_layout->getLayoutValues($idlevel, $level);
        $nfields = [
            "accounting_recurring_transactions.trans_id" => "Trans ID",
            "trans_next_post_date" => "Next Date",
            "dynamic" => "dynamic",
            "trans_numleft" => "cycles",
            "trans_schedule" => "frequency",
            "trans_recurring_net_amount" => "amount",
            "trans_recurring_convenience_fee" => "Fee",
            "web_users.account_number" => $obj_layout->extractLayoutValue('label_acc_number', $layouts),
            "web_users.first_name" => $obj_layout->extractLayoutValue('label_user', $layouts) . " First Name",
            "web_users.last_name" => $obj_layout->extractLayoutValue('label_user', $layouts) . " Last Name",
            "trans_payment_type" => "method"
        ];
        if ($level != 'M' || $export) {
            $nfields['properties.name_clients'] = $obj_layout->extractLayoutValue('label_merchant', $layouts);
            $nfields['properties.compositeID_clients'] = 'PaypointID';
        }
        if ($level != 'G' && $level != 'M') {
            $nfields['companies.company_name'] = $obj_layout->extractLayoutValue('label_group', $layouts);
            $nfields['companies.compositeID_companies'] = 'CompanyID';
        }
        if ($level == 'B' || $level == 'A') {
            $nfields['partners.partner_title'] = $obj_layout->extractLayoutValue('label_partner', $layouts);
        }
        return $nfields;
    }

    function getTransactionsByFilter($level, $idlevel, $status = 1, $schedule = '', $filter = null) {
        $query = DB::table('accounting_recurring_transactions')->join('web_users', 'accounting_recurring_transactions.trans_web_user_id', 'web_users.web_user_id')
                ->join('properties', 'properties.id', 'accounting_recurring_transactions.property_id')
                ->where('properties.status_clients', 1);
        if ($status > 0) {
            $query->where('trans_status', $status);
        }
        if ($schedule != '') {
            $query->where('trans_schedule', 'not like', '%' . $schedule . '%');
        }
        if ($level == 'A') {
            $query->join('companies', 'properties.id_companies', 'companies.id');
            $query->join('partners', 'properties.id_partners', 'partners.id');
            $query->select('companies.compositeID_companies','trans_id as id', 'partners.partner_title', 'companies.company_name', 'properties.name_clients', 'trans_next_post_date', 'trans_numleft', 'web_users.account_number', DB::raw("CONCAT (web_users.first_name, ' ', web_users.last_name) as name"), 'trans_payment_type', 'trans_recurring_net_amount', 'trans_recurring_convenience_fee', DB::raw('(trans_recurring_convenience_fee+trans_recurring_net_amount) as net_charge'), 'dynamic', 'tag', 'properties.compositeID_clients', 'trans_schedule');
        }
        if ($level == 'B') {
            $partnersA = DB::table('branch_partner')->where('branch_id', $idlevel)->select('id_partners')->get();
            $partners = array();
            foreach ($partnersA as $pa) {
                $partners[] = $pa->id_partners;
            }
            $query->join('companies', 'properties.id_companies', 'companies.id');
            $query->join('partners', 'properties.id_partners', 'partners.id');
            $query->whereIn('properties.id_partners', $partners);
            $query->select('companies.compositeID_companies','trans_id as id', 'partners.partner_title', 'companies.company_name', 'properties.name_clients', 'trans_next_post_date', 'trans_numleft', 'web_users.account_number', DB::raw("CONCAT (web_users.first_name, ' ', web_users.last_name) as name"), 'trans_payment_type', 'trans_recurring_net_amount', 'trans_recurring_convenience_fee', DB::raw('(trans_recurring_convenience_fee+trans_recurring_net_amount) as net_charge'), 'dynamic', 'tag', 'properties.compositeID_clients', 'trans_schedule');
        }
        if ($level == 'M') {
            $query->where('accounting_recurring_transactions.property_id', $idlevel);
            $query->select('trans_id as id', 'properties.name_clients', 'trans_next_post_date', 'trans_numleft', 'web_users.account_number', DB::raw("CONCAT (web_users.first_name, ' ', web_users.last_name) as name"), 'trans_payment_type', 'trans_recurring_net_amount', 'trans_recurring_convenience_fee', DB::raw('(trans_recurring_convenience_fee+trans_recurring_net_amount) as net_charge'), 'dynamic', 'tag', 'properties.compositeID_clients', 'trans_schedule');
        } elseif ($level == 'G') {
            $query->join('companies', 'properties.id_companies', 'companies.id');
            $query->where('properties.id_companies', $idlevel);
            $query->select('trans_id as id', 'companies.company_name', 'properties.name_clients', 'trans_next_post_date', 'trans_numleft', 'web_users.account_number', DB::raw("CONCAT (web_users.first_name, ' ', web_users.last_name) as name"), 'trans_payment_type', 'trans_recurring_net_amount', 'trans_recurring_convenience_fee', DB::raw('(trans_recurring_convenience_fee+trans_recurring_net_amount) as net_charge'), 'dynamic', 'tag', 'properties.compositeID_clients', 'trans_schedule');
        } elseif ($level == 'P') {
            $query->join('companies', 'properties.id_companies', 'companies.id');
            $query->join('partners', 'properties.id_partners', 'partners.id');
            $query->where('properties.id_partners', $idlevel);
            $query->select('trans_id as id', 'partners.partner_title', 'companies.company_name', 'properties.name_clients', 'trans_next_post_date', 'trans_numleft', 'web_users.account_number', DB::raw("CONCAT (web_users.first_name, ' ', web_users.last_name) as name"), 'trans_payment_type', 'trans_recurring_net_amount', 'trans_recurring_convenience_fee', DB::raw('(trans_recurring_convenience_fee+trans_recurring_net_amount) as net_charge'), 'dynamic', 'tag', 'properties.compositeID_clients', 'trans_schedule');
        }


        if (!empty($filter) && isset($filter['rules'])) {
            $filters = $filter['rules'];
            foreach ($filters as $rule) {
                if (!isset($rule['data']) || !isset($rule['op']) || !isset($rule['field'])) {
                    continue;
                }
                $tofind = $rule['data'];
                if ($tofind == '') {
                    continue;
                }
                $tocmp = $rule['op'];
                $field = "";
                switch ($rule['field']) {
                    case 'partner':
                        switch ($tocmp) {
                            case 'cn':
                                $query->where('partners.partner_title', 'like', '%' . $tofind . '%');
                                break;
                            case 'nc':
                                $query->where('partners.partner_title', 'not like', '%' . $tofind . '%');
                                break;
                            case 'bw':
                                $query->where('partners.partner_title', 'like', $tofind . '%');
                                break;
                            case 'bn':
                                $query->where('partners.partner_title', 'not like', $tofind . '%');
                                break;
                            case 'ew':
                                $query->where('partners.partner_title', 'like', '%' . $tofind);
                                break;
                            case 'en':
                                $query->where('partners.partner_title', 'not like', '%' . $tofind);
                                break;
                        }
                        break;
                    case 'group':
                        switch ($tocmp) {
                            case 'cn':
                                $query->where('companies.company_name', 'like', '%' . $tofind . '%');
                                break;
                            case 'nc':
                                $query->where('companies.company_name', 'not like', '%' . $tofind . '%');
                                break;
                            case 'bw':
                                $query->where('companies.company_name', 'like', $tofind . '%');
                                break;
                            case 'bn':
                                $query->where('companies.company_name', 'not like', $tofind . '%');
                                break;
                            case 'ew':
                                $query->where('companies.company_name', 'like', '%' . $tofind);
                                break;
                            case 'en':
                                $query->where('companies.company_name', 'not like', '%' . $tofind);
                                break;
                        }
                        break;
                    case 'merchant':
                        switch ($tocmp) {
                            case 'cn':
                                $query->where('properties.name_clients', 'like', '%' . $tofind . '%');
                                break;
                            case 'nc':
                                $query->where('properties.name_clients', 'not like', '%' . $tofind . '%');
                                break;
                            case 'bw':
                                $query->where('properties.name_clients', 'like', $tofind . '%');
                                break;
                            case 'bn':
                                $query->where('properties.name_clients', 'not like', $tofind . '%');
                                break;
                            case 'ew':
                                $query->where('properties.name_clients', 'like', '%' . $tofind);
                                break;
                            case 'en':
                                $query->where('properties.name_clients', 'not like', '%' . $tofind);
                                break;
                        }
                        break;
                    case 'webname':
                        switch ($tocmp) {
                            case 'cn':
                                $query->where('web_users.first_name', 'LIKE', '%' . $tofind . '%');
                                break;
                            case 'nc':
                                $query->where('web_users.first_name', 'not LIKE', '%' . $tofind . '%');
                                break;
                            case 'bw':
                                $query->where('web_users.first_name', 'LIKE', $tofind . '%');
                                break;
                            case 'bn':
                                $query->where('web_users.first_name', 'not LIKE', $tofind . '%');
                                break;
                            case 'ew':
                                $query->where('web_users.first_name', 'LIKE', '%' . $tofind);
                                break;
                            case 'en':
                                $query->where('web_users.first_name', 'not LIKE', '%' . $tofind);
                                break;
                        }
                        break;
                    case 'webnamelast':
                        switch ($tocmp) {
                            case 'cn':
                                $query->where('web_users.last_name', 'LIKE', '%' . $tofind . '%');
                                break;
                            case 'nc':
                                $query->where('web_users.last_name', 'not LIKE', '%' . $tofind . '%');
                                break;
                            case 'bw':
                                $query->where('web_users.last_name', 'LIKE', $tofind . '%');
                                break;
                            case 'bn':
                                $query->where('web_users.last_name', 'not LIKE', $tofind . '%');
                                break;
                            case 'ew':
                                $query->where('web_users.last_name', 'LIKE', '%' . $tofind);
                                break;
                            case 'en':
                                $query->where('web_users.last_name', 'not LIKE', '%' . $tofind);
                                break;
                        }
                        break;
                    case 'webuser':
                        switch ($tocmp) {
                            case 'cn':
                                $query->where('web_users.account_number', 'like' , '%' . $tofind . '%');
                                break;
                            case 'nc':
                                $query->where('web_users.account_number', 'not like' , '%' . $tofind . '%');
                                break;
                            case 'bw':
                                $query->where('web_users.account_number', 'like' , $tofind . '%');
                                break;
                            case 'bn':
                                $query->where('web_users.account_number', 'not like' , $tofind . '%');
                                break;
                            case 'ew':
                                $query->where('web_users.account_number', 'like' , '%' . $tofind);
                                break;
                            case 'en':
                                $query->where('web_users.account_number', 'not like' , '%' . $tofind);
                                break;
                        }
                        break;
                    case 'tag':
                        switch ($tocmp) {
                            case 'cn':
                                $query->where('tag', 'like' , '%' . $tofind . '%');
                                break;
                            case 'nc':
                                $query->where('tag', 'not like' , '%' . $tofind . '%');
                                break;
                            case 'bw':
                                $query->where('tag', 'like' , $tofind . '%');
                                break;
                            case 'bn':
                                $query->where('tag', 'not like' , $tofind . '%');
                                break;
                            case 'ew':
                                $query->where('tag', 'like' , '%' . $tofind);
                                break;
                            case 'en':
                                $query->where('tag', 'not like' , '%' . $tofind);
                                break;
                        }
                        break;
                    case 'net_amount':
                        switch ($tocmp) {
                            case 'eq':
                                $query->where('accounting_recurring_transactions.trans_recurring_net_amount', '=', $tofind);
                                break;
                            case 'ne':
                                $query->where('accounting_recurring_transactions.trans_recurring_net_amount', '!=', $tofind);
                                break;
                            case 'lt':
                                $query->where('accounting_recurring_transactions.trans_recurring_net_amount', '<', $tofind);
                                break;
                            case 'le':
                                $query->where('accounting_recurring_transactions.trans_recurring_net_amount', '<=', $tofind);
                                break;
                            case 'gt':
                                $query->where('accounting_recurring_transactions.trans_recurring_net_amount', '>', $tofind);
                                break;
                            case 'ge':
                                $query->where('accounting_recurring_transactions.trans_recurring_net_amount', '>=', $tofind);
                                break;
                        }
                        break;
                    case 'schedule':
                        switch ($tocmp) {
                            case 'eq':
                                $query->where('trans_schedule', '=', $tofind);
                                break;
                        }
                        break;
                    case 'net_fee':
                        switch ($tocmp) {
                            case 'eq':
                                $query->where('accounting_recurring_transactions.trans_recurring_convenience_fee', '=', $tofind);
                                break;
                            case 'ne':
                                $query->where('accounting_recurring_transactions.trans_recurring_convenience_fee', '!=', $tofind);
                                break;
                            case 'lt':
                                $query->where('accounting_recurring_transactions.trans_recurring_convenience_fee', '<', $tofind);
                                break;
                            case 'le':
                                $query->where('accounting_recurring_transactions.trans_recurring_convenience_fee', '<=', $tofind);
                                break;
                            case 'gt':
                                $query->where('accounting_recurring_transactions.trans_recurring_convenience_fee', '>', $tofind);
                                break;
                            case 'ge':
                                $query->where('accounting_recurring_transactions.trans_recurring_convenience_fee', '>=', $tofind);
                                break;
                        }
                        break;
                    case 'num_left':
                        switch ($tocmp) {
                            case 'eq':
                                $query->where('accounting_recurring_transactions.trans_numleft', '=', $tofind);
                                break;
                            case 'ne':
                                $query->where('accounting_recurring_transactions.trans_numleft', '!=', $tofind);
                                break;
                            case 'lt':
                                $query->where('accounting_recurring_transactions.trans_numleft', '<', $tofind);
                                break;
                            case 'le':
                                $query->where('accounting_recurring_transactions.trans_numleft', '<=', $tofind);
                                break;
                            case 'gt':
                                $query->where('accounting_recurring_transactions.trans_numleft', '>', $tofind);
                                break;
                            case 'ge':
                                $query->where('accounting_recurring_transactions.trans_numleft', '>=', $tofind);
                                break;
                        }
                        break;

                    case 'pay_method':
                        if ($tofind == 'ec') {
                            $query->where('trans_payment_type', 'ec');
                        } else {
                            $query->where('trans_payment_type', 'cc');
                        }
                        break;
                    case 'stype':
                        if ($tofind == '0') {
                            $query->where('dynamic', 0);
                        } else {
                            $query->where('dynamic', 1);
                        }
                        break;
                    case 'trans_next_date':
                        switch ($tocmp) {
                            case 'eq':
                                $query->whereRaw('DATE(trans_next_post_date) = ?', [$tofind]);
                                break;
                            case 'ne':
                                $query->whereRaw('DATE(trans_next_post_date) != ?', [$tofind]);
                                break;
                            case 'lt':
                                $query->whereRaw('DATE(trans_next_post_date) < ?', [$tofind]);
                                break;
                            case 'le':
                                $query->whereRaw('DATE(trans_next_post_date) <= ?', [$tofind]);
                                break;
                            case 'gt':
                                $query->whereRaw('DATE(trans_next_post_date) > ?', [$tofind]);
                                break;
                            case 'ge':
                                $query->whereRaw('DATE(trans_next_post_date) >= ?', [$tofind]);
                                break;
                        }
                        break;
                    case 'status':
                        $query->where('trans_status', $tofind);
                        break;
                    case 'trans_last_date':
                        switch ($tocmp) {
                            case 'eq':
                                $query->whereRaw('DATE(trans_last_post_date) = ?', [$tofind]);
                                break;
                            case 'ne':
                                $query->whereRaw('DATE(trans_last_post_date) != ?', [$tofind]);
                                break;
                            case 'lt':
                                $query->whereRaw('DATE(trans_last_post_date) < ?', [$tofind]);
                                break;
                            case 'le':
                                $query->whereRaw('DATE(trans_last_post_date) <= ?', [$tofind]);
                                break;
                            case 'gt':
                                $query->whereRaw('DATE(trans_last_post_date) > ?', [$tofind]);
                                break;
                            case 'ge':
                                $query->whereRaw('DATE(trans_last_post_date) >= ?', [$tofind]);
                                break;
                        }
                        break;
                }
            }
        }
        return $query;
    }

    function getAdvancedFilters($level, $idlevel = 0) {
        $obj_layout = new \App\Model\Layout();
        $layouts = $obj_layout->getLayoutValues($idlevel, $level);
        $advFilter = [
            ['text' => 'Next date',
                'itemval' => 'trans_next_date',
                'ops' => [
                    ['op' => "eq", 'text' => "is equal to"],
                    ['op' => "ne", 'text' => "is not equal to"],
                    ['op' => "lt", 'text' => "is less than"],
                    ['op' => "le", 'text' => "is less or equal to"],
                    ['op' => "gt", 'text' => "is greater than"],
                    ['op' => "ge", 'text' => "is greater or equal to"]
                ],
                'dateType' => true
            ],
            ['text' => 'Last date',
                'itemval' => 'trans_last_date',
                'ops' => [
                    ['op' => "eq", 'text' => "is equal to"],
                    ['op' => "ne", 'text' => "is not equal to"],
                    ['op' => "lt", 'text' => "is less than"],
                    ['op' => "le", 'text' => "is less or equal to"],
                    ['op' => "gt", 'text' => "is greater than"],
                    ['op' => "ge", 'text' => "is greater or equal to"]
                ],
                'dateType' => true
            ],
            ['text' => 'transID',
                'itemval' => 'trans_id',
                'ops' => [
                    ['op' => "eq", 'text' => "is equal to"],
                    ['op' => "ne", 'text' => "is not equal to"],
                    ['op' => "lt", 'text' => "is less than"],
                    ['op' => "le", 'text' => "is less or equal to"],
                    ['op' => "gt", 'text' => "is greater than"],
                    ['op' => "ge", 'text' => "is greater or equal to"]
                ],
            ],
            ['text' => $obj_layout->extractLayoutValue('label_acc_number', $layouts),
                'itemval' => 'webuser',
                'ops' => [
                    ['op' => "bw", 'text' => "begins with"],
                    ['op' => "bn", 'text' => "does not begin with"],
                    ['op' => "ew", 'text' => "ends with"],
                    ['op' => "en", 'text' => "does not end with"],
                    ['op' => "cn", 'text' => "contains"],
                    ['op' => "nc", 'text' => "does not contain"]
                ],
            ],
            ['text' => 'Tag',
                'itemval' => 'tag',
                'ops' => [
                    ['op' => "bw", 'text' => "begins with"],
                    ['op' => "bn", 'text' => "does not begin with"],
                    ['op' => "ew", 'text' => "ends with"],
                    ['op' => "en", 'text' => "does not end with"],
                    ['op' => "cn", 'text' => "contains"],
                    ['op' => "nc", 'text' => "does not contain"]
                ],
            ],
            ['text' => $obj_layout->extractLayoutValue('label_user', $layouts) . ' First Name',
                'itemval' => 'webname',
                'ops' => [
                    ['op' => "bw", 'text' => "begins with"],
                    ['op' => "bn", 'text' => "does not begin with"],
                    ['op' => "ew", 'text' => "ends with"],
                    ['op' => "en", 'text' => "does not end with"],
                    ['op' => "cn", 'text' => "contains"],
                    ['op' => "nc", 'text' => "does not contain"]
                ],
            ],
            ['text' => $obj_layout->extractLayoutValue('label_user', $layouts) . ' Last Name',
                'itemval' => 'webnamelast',
                'ops' => [
                    ['op' => "bw", 'text' => "begins with"],
                    ['op' => "bn", 'text' => "does not begin with"],
                    ['op' => "ew", 'text' => "ends with"],
                    ['op' => "en", 'text' => "does not end with"],
                    ['op' => "cn", 'text' => "contains"],
                    ['op' => "nc", 'text' => "does not contain"]
                ],
            ],
            ['text' => 'Method',
                'itemval' => 'pay_method',
                'ops' => [
                    ['op' => "eq", 'text' => "is equal to"],
                ],
                'dataValues' => [
                    ['value' => 'ec', 'text' => 'E-Check'],
                    ['value' => 'cc', 'text' => 'Credit Card'],
                ]
            ],
            ['text' => 'Frequency',
                'itemval' => 'schedule',
                'ops' => [
                    ['op' => "eq", 'text' => "is equal to"],
                ],
                'dataValues' => [
                    ['value' => 'onetime', 'text' => 'One Time'],
                    ['value' => 'monthly', 'text' => 'Monthly'],
                    ['value' => 'annually', 'text' => 'Annually'],
                ]
            ],
            ['text' => 'Amount',
                'itemval' => 'net_amount',
                'ops' => [
                    ['op' => "eq", 'text' => "is equal to"],
                    ['op' => "ne", 'text' => "is not equal to"],
                    ['op' => "lt", 'text' => "is less than"],
                    ['op' => "le", 'text' => "is less or equal to"],
                    ['op' => "gt", 'text' => "is greater than"],
                    ['op' => "ge", 'text' => "is greater or equal to"]
                ],
            ],
            ['text' => 'Fee',
                'itemval' => 'net_fee',
                'ops' => [
                    ['op' => "eq", 'text' => "is equal to"],
                    ['op' => "ne", 'text' => "is not equal to"],
                    ['op' => "lt", 'text' => "is less than"],
                    ['op' => "le", 'text' => "is less or equal to"],
                    ['op' => "gt", 'text' => "is greater than"],
                    ['op' => "ge", 'text' => "is greater or equal to"]
                ],
            ],
            ['text' => 'Type',
                'itemval' => 'stype',
                'ops' => [
                    ['op' => "eq", 'text' => "is equal to"],
                ],
                'dataValues' => [
                    ['value' => 0, 'text' => 'Fixed'],
                    ['value' => 1, 'text' => 'Dynamic']
                ]
            ]
        ];
        if ($level == 'G') {
            $advFilter[] = ['text' => $obj_layout->extractLayoutValue('label_merchant', $layouts),
                'itemval' => 'merchant',
                'ops' => [
                    ['op' => "bw", 'text' => "begins with"],
                    ['op' => "bn", 'text' => "does not begin with"],
                    ['op' => "ew", 'text' => "ends with"],
                    ['op' => "en", 'text' => "does not end with"],
                    ['op' => "cn", 'text' => "contains"],
                    ['op' => "nc", 'text' => "does not contain"]
                ],
            ];
        } elseif ($level == 'P') {
            $advFilter[] = ['text' => $obj_layout->extractLayoutValue('label_group', $layouts),
                'itemval' => 'group',
                'ops' => [
                    ['op' => "bw", 'text' => "begins with"],
                    ['op' => "bn", 'text' => "does not begin with"],
                    ['op' => "ew", 'text' => "ends with"],
                    ['op' => "en", 'text' => "does not end with"],
                    ['op' => "cn", 'text' => "contains"],
                    ['op' => "nc", 'text' => "does not contain"]
                ],
            ];
            $advFilter[] = ['text' => $obj_layout->extractLayoutValue('label_merchant', $layouts),
                'itemval' => 'merchant',
                'ops' => [
                    ['op' => "bw", 'text' => "begins with"],
                    ['op' => "bn", 'text' => "does not begin with"],
                    ['op' => "ew", 'text' => "ends with"],
                    ['op' => "en", 'text' => "does not end with"],
                    ['op' => "cn", 'text' => "contains"],
                    ['op' => "nc", 'text' => "does not contain"]
                ],
            ];
        } elseif ($level == 'B' || $level == 'A') {
            $advFilter[] = ['text' => $obj_layout->extractLayoutValue('label_partner', $layouts),
                'itemval' => 'partner',
                'ops' => [
                    ['op' => "bw", 'text' => "begins with"],
                    ['op' => "bn", 'text' => "does not begin with"],
                    ['op' => "ew", 'text' => "ends with"],
                    ['op' => "en", 'text' => "does not end with"],
                    ['op' => "cn", 'text' => "contains"],
                    ['op' => "nc", 'text' => "does not contain"]
                ],
            ];
            $advFilter[] = ['text' => $obj_layout->extractLayoutValue('label_group', $layouts),
                'itemval' => 'group',
                'ops' => [
                    ['op' => "bw", 'text' => "begins with"],
                    ['op' => "bn", 'text' => "does not begin with"],
                    ['op' => "ew", 'text' => "ends with"],
                    ['op' => "en", 'text' => "does not end with"],
                    ['op' => "cn", 'text' => "contains"],
                    ['op' => "nc", 'text' => "does not contain"]
                ],
            ];
            $advFilter[] = ['text' => $obj_layout->extractLayoutValue('label_merchant', $layouts),
                'itemval' => 'merchant',
                'ops' => [
                    ['op' => "bw", 'text' => "begins with"],
                    ['op' => "bn", 'text' => "does not begin with"],
                    ['op' => "ew", 'text' => "ends with"],
                    ['op' => "en", 'text' => "does not end with"],
                    ['op' => "cn", 'text' => "contains"],
                    ['op' => "nc", 'text' => "does not contain"]
                ],
            ];
        }
        return $advFilter;
    }

    function getCompleteTransactionsByFilter($level, $idlevel, $status = 3, $filter = null) {
        $query = DB::table('accounting_recurring_transactions')->join('web_users', 'accounting_recurring_transactions.trans_web_user_id', 'web_users.web_user_id')
                ->join('properties', 'properties.id', 'accounting_recurring_transactions.property_id')
                ->where('trans_status', $status)
                ->where('properties.status_clients', 1);
        if ($level == 'M') {
            $query->where('accounting_recurring_transactions.property_id', $idlevel);
            $query->select('trans_id as id', 'properties.name_clients', 'trans_next_post_date', 'trans_numleft', 'web_users.account_number', DB::raw("CONCAT (web_users.first_name, ' ', web_users.last_name) as name"), 'trans_payment_type', 'trans_recurring_net_amount', 'trans_recurring_convenience_fee', DB::raw('(trans_recurring_convenience_fee+trans_recurring_net_amount) as net_charge'), 'dynamic', 'tag');
        } elseif ($level == 'G') {
            $query->join('companies', 'properties.id_companies', 'companies.id');
            $query->where('properties.id_companies', $idlevel);
            $query->select('trans_id as id', 'companies.company_name', 'properties.name_clients', 'trans_next_post_date', 'trans_numleft', 'web_users.account_number', DB::raw("CONCAT (web_users.first_name, ' ', web_users.last_name) as name"), 'trans_payment_type', 'trans_recurring_net_amount', 'trans_recurring_convenience_fee', DB::raw('(trans_recurring_convenience_fee+trans_recurring_net_amount) as net_charge'), 'dynamic', 'tag');
        } elseif ($level == 'P') {
            $query->join('companies', 'properties.id_companies', 'companies.id');
            $query->join('partners', 'properties.id_partners', 'partners.id');
            $query->where('properties.id_partners', $idlevel);
            $query->select('trans_id as id', 'partners.partner_title', 'companies.company_name', 'properties.name_clients', 'trans_next_post_date', 'trans_numleft', 'web_users.account_number', DB::raw("CONCAT (web_users.first_name, ' ', web_users.last_name) as name"), 'trans_payment_type', 'trans_recurring_net_amount', 'trans_recurring_convenience_fee', DB::raw('(trans_recurring_convenience_fee+trans_recurring_net_amount) as net_charge'), 'dynamic', 'tag');
        } elseif ($level == 'B') {
            $partnersA = DB::table('branch_partner')->where('branch_id', $idlevel)->select('id_partners')->get();
            $partners = array();
            foreach ($partnersA as $pa) {
                $partners[] = $pa->id_partners;
            }
            $query->join('companies', 'properties.id_companies', 'companies.id');
            $query->join('partners', 'properties.id_partners', 'partners.id');
            $query->whereIn('properties.id_partners', $partners);
            $query->select('trans_id as id', 'partners.partner_title', 'companies.company_name', 'properties.name_clients', 'trans_next_post_date', 'trans_numleft', 'web_users.account_number', DB::raw("CONCAT (web_users.first_name, ' ', web_users.last_name) as name"), 'trans_payment_type', 'trans_recurring_net_amount', 'trans_recurring_convenience_fee', DB::raw('(trans_recurring_convenience_fee+trans_recurring_net_amount) as net_charge'), 'dynamic', 'tag');
        } else {
            $query->join('companies', 'properties.id_companies', 'companies.id');
            $query->join('partners', 'properties.id_partners', 'partners.id');
            $query->select('trans_id as id', 'partners.partner_title', 'companies.company_name', 'properties.name_clients', 'trans_next_post_date', 'trans_numleft', 'web_users.account_number', DB::raw("CONCAT (web_users.first_name, ' ', web_users.last_name) as name"), 'trans_payment_type', 'trans_recurring_net_amount', 'trans_recurring_convenience_fee', DB::raw('(trans_recurring_convenience_fee+trans_recurring_net_amount) as net_charge'), 'dynamic', 'tag');
        }


        if (!empty($filter) && isset($filter['rules'])) {
            $filters = $filter['rules'];
            foreach ($filters as $rule) {
                if (!isset($rule['data']) || !isset($rule['op']) || !isset($rule['field'])) {
                    continue;
                }
                $tofind = $rule['data'];
                if ($tofind == '') {
                    continue;
                }
                $tocmp = $rule['op'];
                $field = "";
                switch ($rule['field']) {
                    case 'partner':
                        switch ($tocmp) {
                            case 'cn':
                                $query->where('partners.partner_title', 'like', '%' . $tofind . '%');
                                break;
                            case 'nc':
                                $query->where('partners.partner_title', 'not like', '%' . $tofind . '%');
                                break;
                            case 'bw':
                                $query->where('partners.partner_title', 'like', $tofind . '%');
                                break;
                            case 'bn':
                                $query->where('partners.partner_title', 'not like', $tofind . '%');
                                break;
                            case 'ew':
                                $query->where('partners.partner_title', 'like', '%' . $tofind);
                                break;
                            case 'en':
                                $query->where('partners.partner_title', 'not like', '%' . $tofind);
                                break;
                        }
                        break;
                    case 'group':
                        switch ($tocmp) {
                            case 'cn':
                                $query->where('companies.company_name', 'like', '%' . $tofind . '%');
                                break;
                            case 'nc':
                                $query->where('companies.company_name', 'not like', '%' . $tofind . '%');
                                break;
                            case 'bw':
                                $query->where('companies.company_name', 'like', $tofind . '%');
                                break;
                            case 'bn':
                                $query->where('companies.company_name', 'not like', $tofind . '%');
                                break;
                            case 'ew':
                                $query->where('companies.company_name', 'like', '%' . $tofind);
                                break;
                            case 'en':
                                $query->where('companies.company_name', 'not like', '%' . $tofind);
                                break;
                        }
                        break;
                    case 'merchant':
                        switch ($tocmp) {
                            case 'cn':
                                $query->where('properties.name_clients', 'like', '%' . $tofind . '%');
                                break;
                            case 'nc':
                                $query->where('properties.name_clients', 'not like', '%' . $tofind . '%');
                                break;
                            case 'bw':
                                $query->where('properties.name_clients', 'like', $tofind . '%');
                                break;
                            case 'bn':
                                $query->where('properties.name_clients', 'not like', $tofind . '%');
                                break;
                            case 'ew':
                                $query->where('properties.name_clients', 'like', '%' . $tofind);
                                break;
                            case 'en':
                                $query->where('properties.name_clients', 'not like', '%' . $tofind);
                                break;
                        }
                        break;
                    case 'webname':
                        switch ($tocmp) {
                            case 'cn':
                                $query->where('web_users.first_name', 'LIKE', '%' . $tofind . '%');
                                break;
                            case 'nc':
                                $query->where('web_users.first_name', 'not LIKE', '%' . $tofind . '%');
                                break;
                            case 'bw':
                                $query->where('web_users.first_name', 'LIKE', $tofind . '%');
                                break;
                            case 'bn':
                                $query->where('web_users.first_name', 'not LIKE', $tofind . '%');
                                break;
                            case 'ew':
                                $query->where('web_users.first_name', 'LIKE', '%' . $tofind);
                                break;
                            case 'en':
                                $query->where('web_users.first_name', 'not LIKE', '%' . $tofind);
                                break;
                        }
                        break;
                    case 'webnamelast':
                        switch ($tocmp) {
                            case 'cn':
                                $query->where('web_users.last_name', 'LIKE', '%' . $tofind . '%');
                                break;
                            case 'nc':
                                $query->where('web_users.last_name', 'not LIKE', '%' . $tofind . '%');
                                break;
                            case 'bw':
                                $query->where('web_users.last_name', 'LIKE', $tofind . '%');
                                break;
                            case 'bn':
                                $query->where('web_users.last_name', 'not LIKE', $tofind . '%');
                                break;
                            case 'ew':
                                $query->where('web_users.last_name', 'LIKE', '%' . $tofind);
                                break;
                            case 'en':
                                $query->where('web_users.last_name', 'not LIKE', '%' . $tofind);
                                break;
                        }
                        break;

                    case 'webuser':
                        switch ($tocmp) {
                            case 'cn':
                                $query->where('web_users.account_number', 'like' , '%' . $tofind . '%');
                                break;
                            case 'nc':
                                $query->where('web_users.account_number', 'not like' , '%' . $tofind . '%');
                                break;
                            case 'bw':
                                $query->where('web_users.account_number', 'like' , $tofind . '%');
                                break;
                            case 'bn':
                                $query->where('web_users.account_number', 'not like' , $tofind . '%');
                                break;
                            case 'ew':
                                $query->where('web_users.account_number', 'like' , '%' . $tofind);
                                break;
                            case 'en':
                                $query->where('web_users.account_number', 'not like' , '%' . $tofind);
                                break;
                        }
                        break;
                    case 'net_amount':
                        switch ($tocmp) {
                            case 'eq':
                                $query->where('accounting_recurring_transactions.trans_recurring_net_amount', '=', $tofind);
                                break;
                            case 'ne':
                                $query->where('accounting_recurring_transactions.trans_recurring_net_amount', '!=', $tofind);
                                break;
                            case 'lt':
                                $query->where('accounting_recurring_transactions.trans_recurring_net_amount', '<', $tofind);
                                break;
                            case 'le':
                                $query->where('accounting_recurring_transactions.trans_recurring_net_amount', '<=', $tofind);
                                break;
                            case 'gt':
                                $query->where('accounting_recurring_transactions.trans_recurring_net_amount', '>', $tofind);
                                break;
                            case 'ge':
                                $query->where('accounting_recurring_transactions.trans_recurring_net_amount', '>=', $tofind);
                                break;
                        }
                        break;
                    case 'net_fee':
                        switch ($tocmp) {
                            case 'eq':
                                $query->where('accounting_recurring_transactions.trans_recurring_convenience_fee', '=', $tofind);
                                break;
                            case 'ne':
                                $query->where('accounting_recurring_transactions.trans_recurring_convenience_fee', '!=', $tofind);
                                break;
                            case 'lt':
                                $query->where('accounting_recurring_transactions.trans_recurring_convenience_fee', '<', $tofind);
                                break;
                            case 'le':
                                $query->where('accounting_recurring_transactions.trans_recurring_convenience_fee', '<=', $tofind);
                                break;
                            case 'gt':
                                $query->where('accounting_recurring_transactions.trans_recurring_convenience_fee', '>', $tofind);
                                break;
                            case 'ge':
                                $query->where('accounting_recurring_transactions.trans_recurring_convenience_fee', '>=', $tofind);
                                break;
                        }
                        break;
                    case 'num_left':
                        switch ($tocmp) {
                            case 'eq':
                                $query->where('accounting_recurring_transactions.trans_numleft', '=', $tofind);
                                break;
                            case 'ne':
                                $query->where('accounting_recurring_transactions.trans_numleft', '!=', $tofind);
                                break;
                            case 'lt':
                                $query->where('accounting_recurring_transactions.trans_numleft', '<', $tofind);
                                break;
                            case 'le':
                                $query->where('accounting_recurring_transactions.trans_numleft', '<=', $tofind);
                                break;
                            case 'gt':
                                $query->where('accounting_recurring_transactions.trans_numleft', '>', $tofind);
                                break;
                            case 'ge':
                                $query->where('accounting_recurring_transactions.trans_numleft', '>=', $tofind);
                                break;
                        }
                        break;

                    case 'pay_method':
                        if ($tofind == 'ec') {
                            $query->where('trans_payment_type', 'ec');
                        } else {
                            $query->where('trans_payment_type', 'cc');
                        }
                        break;
                    case 'stype':
                        if ($tofind == '0') {
                            $query->where('dynamic', 0);
                        } else {
                            $query->where('dynamic', 1);
                        }
                        break;
                    case 'trans_next_date':
                        switch ($tocmp) {
                            case 'eq':
                                $query->whereRaw('DATE(trans_next_post_date) = ?', [$tofind]);
                                break;
                            case 'ne':
                                $query->whereRaw('DATE(trans_next_post_date) != ?', [$tofind]);
                                break;
                            case 'lt':
                                $query->whereRaw('DATE(trans_next_post_date) < ?', [$tofind]);
                                break;
                            case 'le':
                                $query->whereRaw('DATE(trans_next_post_date) <= ?', [$tofind]);
                                break;
                            case 'gt':
                                $query->whereRaw('DATE(trans_next_post_date) > ?', [$tofind]);
                                break;
                            case 'ge':
                                $query->whereRaw('DATE(trans_next_post_date) >= ?', [$tofind]);
                                break;
                        }
                        break;
                    case 'trans_last_date':
                        switch ($tocmp) {
                            case 'eq':
                                $query->whereRaw('DATE(trans_last_post_date) = ?', [$tofind]);
                                break;
                            case 'ne':
                                $query->whereRaw('DATE(trans_last_post_date) != ?', [$tofind]);
                                break;
                            case 'lt':
                                $query->whereRaw('DATE(trans_last_post_date) < ?', [$tofind]);
                                break;
                            case 'le':
                                $query->whereRaw('DATE(trans_last_post_date) <= ?', [$tofind]);
                                break;
                            case 'gt':
                                $query->whereRaw('DATE(trans_last_post_date) > ?', [$tofind]);
                                break;
                            case 'ge':
                                $query->whereRaw('DATE(trans_last_post_date) >= ?', [$tofind]);
                                break;
                        }
                        break;
                }
            }
        }
        return $query;
    }

    function getCancleTransactionsByFilter($level, $idlevel, $status = 4, $filter = null) {
        $query = DB::table('accounting_recurring_transactions')->join('web_users', 'accounting_recurring_transactions.trans_web_user_id', 'web_users.web_user_id')
                ->join('properties', 'properties.id', 'accounting_recurring_transactions.property_id')
                ->where('trans_status', $status)
                ->where('properties.status_clients', 1);
        if ($level == 'M') {
            $query->where('accounting_recurring_transactions.property_id', $idlevel);
            $query->select('trans_id as id', 'properties.name_clients', 'trans_next_post_date', 'trans_numleft', 'web_users.account_number', DB::raw("CONCAT (web_users.first_name, ' ', web_users.last_name) as name"), 'trans_payment_type', 'trans_recurring_net_amount', 'trans_recurring_convenience_fee', DB::raw('(trans_recurring_convenience_fee+trans_recurring_net_amount) as net_charge'), 'dynamic', 'tag');
        } elseif ($level == 'G') {
            $query->join('companies', 'properties.id_companies', 'companies.id');
            $query->where('properties.id_companies', $idlevel);
            $query->select('trans_id as id', 'companies.company_name', 'properties.name_clients', 'trans_next_post_date', 'trans_numleft', 'web_users.account_number', DB::raw("CONCAT (web_users.first_name, ' ', web_users.last_name) as name"), 'trans_payment_type', 'trans_recurring_net_amount', 'trans_recurring_convenience_fee', DB::raw('(trans_recurring_convenience_fee+trans_recurring_net_amount) as net_charge'), 'dynamic', 'tag');
        } elseif ($level == 'P') {
            $query->join('companies', 'properties.id_companies', 'companies.id');
            $query->join('partners', 'properties.id_partners', 'partners.id');
            $query->where('properties.id_partners', $idlevel);
            $query->select('trans_id as id', 'partners.partner_title', 'companies.company_name', 'properties.name_clients', 'trans_next_post_date', 'trans_numleft', 'web_users.account_number', DB::raw("CONCAT (web_users.first_name, ' ', web_users.last_name) as name"), 'trans_payment_type', 'trans_recurring_net_amount', 'trans_recurring_convenience_fee', DB::raw('(trans_recurring_convenience_fee+trans_recurring_net_amount) as net_charge'), 'dynamic', 'tag');
        } elseif ($level == 'B') {
            $partnersA = DB::table('branch_partner')->where('branch_id', $idlevel)->select('id_partners')->get();
            $partners = array();
            foreach ($partnersA as $pa) {
                $partners[] = $pa->id_partners;
            }
            $query->join('companies', 'properties.id_companies', 'companies.id');
            $query->join('partners', 'properties.id_partners', 'partners.id');
            $query->whereIn('properties.id_partners', $partners);
            $query->select('trans_id as id', 'partners.partner_title', 'companies.company_name', 'properties.name_clients', 'trans_next_post_date', 'trans_numleft', 'web_users.account_number', DB::raw("CONCAT (web_users.first_name, ' ', web_users.last_name) as name"), 'trans_payment_type', 'trans_recurring_net_amount', 'trans_recurring_convenience_fee', DB::raw('(trans_recurring_convenience_fee+trans_recurring_net_amount) as net_charge'), 'dynamic', 'tag');
        } else {
            $query->join('companies', 'properties.id_companies', 'companies.id');
            $query->join('partners', 'properties.id_partners', 'partners.id');
            $query->select('trans_id as id', 'partners.partner_title', 'companies.company_name', 'properties.name_clients', 'trans_next_post_date', 'trans_numleft', 'web_users.account_number', DB::raw("CONCAT (web_users.first_name, ' ', web_users.last_name) as name"), 'trans_payment_type', 'trans_recurring_net_amount', 'trans_recurring_convenience_fee', DB::raw('(trans_recurring_convenience_fee+trans_recurring_net_amount) as net_charge'), 'dynamic', 'tag');
        }


        if (!empty($filter) && isset($filter['rules'])) {
            $filters = $filter['rules'];
            foreach ($filters as $rule) {
                if (!isset($rule['data']) || !isset($rule['op']) || !isset($rule['field'])) {
                    continue;
                }
                $tofind = $rule['data'];
                if ($tofind == '') {
                    continue;
                }
                $tocmp = $rule['op'];
                $field = "";
                switch ($rule['field']) {
                    case 'partner':
                        switch ($tocmp) {
                            case 'cn':
                                $query->where('partners.partner_title', 'like', '%' . $tofind . '%');
                                break;
                            case 'nc':
                                $query->where('partners.partner_title', 'not like', '%' . $tofind . '%');
                                break;
                            case 'bw':
                                $query->where('partners.partner_title', 'like', $tofind . '%');
                                break;
                            case 'bn':
                                $query->where('partners.partner_title', 'not like', $tofind . '%');
                                break;
                            case 'ew':
                                $query->where('partners.partner_title', 'like', '%' . $tofind);
                                break;
                            case 'en':
                                $query->where('partners.partner_title', 'not like', '%' . $tofind);
                                break;
                        }
                        break;
                    case 'group':
                        switch ($tocmp) {
                            case 'cn':
                                $query->where('companies.company_name', 'like', '%' . $tofind . '%');
                                break;
                            case 'nc':
                                $query->where('companies.company_name', 'not like', '%' . $tofind . '%');
                                break;
                            case 'bw':
                                $query->where('companies.company_name', 'like', $tofind . '%');
                                break;
                            case 'bn':
                                $query->where('companies.company_name', 'not like', $tofind . '%');
                                break;
                            case 'ew':
                                $query->where('companies.company_name', 'like', '%' . $tofind);
                                break;
                            case 'en':
                                $query->where('companies.company_name', 'not like', '%' . $tofind);
                                break;
                        }
                        break;
                    case 'merchant':
                        switch ($tocmp) {
                            case 'cn':
                                $query->where('properties.name_clients', 'like', '%' . $tofind . '%');
                                break;
                            case 'nc':
                                $query->where('properties.name_clients', 'not like', '%' . $tofind . '%');
                                break;
                            case 'bw':
                                $query->where('properties.name_clients', 'like', $tofind . '%');
                                break;
                            case 'bn':
                                $query->where('properties.name_clients', 'not like', $tofind . '%');
                                break;
                            case 'ew':
                                $query->where('properties.name_clients', 'like', '%' . $tofind);
                                break;
                            case 'en':
                                $query->where('properties.name_clients', 'not like', '%' . $tofind);
                                break;
                        }
                        break;
                    case 'webname':
                        switch ($tocmp) {
                            case 'cn':
                                $query->where('web_users.first_name', 'LIKE', '%' . $tofind . '%');
                                break;
                            case 'nc':
                                $query->where('web_users.first_name', 'not LIKE', '%' . $tofind . '%');
                                break;
                            case 'bw':
                                $query->where('web_users.first_name', 'LIKE', $tofind . '%');
                                break;
                            case 'bn':
                                $query->where('web_users.first_name', 'not LIKE', $tofind . '%');
                                break;
                            case 'ew':
                                $query->where('web_users.first_name', 'LIKE', '%' . $tofind);
                                break;
                            case 'en':
                                $query->where('web_users.first_name', 'not LIKE', '%' . $tofind);
                                break;
                        }
                        break;
                    case 'webnamelast':
                        switch ($tocmp) {
                            case 'cn':
                                $query->where('web_users.last_name', 'LIKE', '%' . $tofind . '%');
                                break;
                            case 'nc':
                                $query->where('web_users.last_name', 'not LIKE', '%' . $tofind . '%');
                                break;
                            case 'bw':
                                $query->where('web_users.last_name', 'LIKE', $tofind . '%');
                                break;
                            case 'bn':
                                $query->where('web_users.last_name', 'not LIKE', $tofind . '%');
                                break;
                            case 'ew':
                                $query->where('web_users.last_name', 'LIKE', '%' . $tofind);
                                break;
                            case 'en':
                                $query->where('web_users.last_name', 'not LIKE', '%' . $tofind);
                                break;
                        }
                        break;

                    case 'webuser':
                        switch ($tocmp) {
                            case 'cn':
                                $query->where('web_users.account_number', 'like' , '%' . $tofind . '%');
                                break;
                            case 'nc':
                                $query->where('web_users.account_number', 'not like' , '%' . $tofind . '%');
                                break;
                            case 'bw':
                                $query->where('web_users.account_number', 'like' , $tofind . '%');
                                break;
                            case 'bn':
                                $query->where('web_users.account_number', 'not like' , $tofind . '%');
                                break;
                            case 'ew':
                                $query->where('web_users.account_number', 'like' , '%' . $tofind);
                                break;
                            case 'en':
                                $query->where('web_users.account_number', 'not like' , '%' . $tofind);
                                break;
                        }
                        break;
                    case 'net_amount':
                        switch ($tocmp) {
                            case 'eq':
                                $query->where('accounting_recurring_transactions.trans_recurring_net_amount', '=', $tofind);
                                break;
                            case 'ne':
                                $query->where('accounting_recurring_transactions.trans_recurring_net_amount', '!=', $tofind);
                                break;
                            case 'lt':
                                $query->where('accounting_recurring_transactions.trans_recurring_net_amount', '<', $tofind);
                                break;
                            case 'le':
                                $query->where('accounting_recurring_transactions.trans_recurring_net_amount', '<=', $tofind);
                                break;
                            case 'gt':
                                $query->where('accounting_recurring_transactions.trans_recurring_net_amount', '>', $tofind);
                                break;
                            case 'ge':
                                $query->where('accounting_recurring_transactions.trans_recurring_net_amount', '>=', $tofind);
                                break;
                        }
                        break;
                    case 'net_fee':
                        switch ($tocmp) {
                            case 'eq':
                                $query->where('accounting_recurring_transactions.trans_recurring_convenience_fee', '=', $tofind);
                                break;
                            case 'ne':
                                $query->where('accounting_recurring_transactions.trans_recurring_convenience_fee', '!=', $tofind);
                                break;
                            case 'lt':
                                $query->where('accounting_recurring_transactions.trans_recurring_convenience_fee', '<', $tofind);
                                break;
                            case 'le':
                                $query->where('accounting_recurring_transactions.trans_recurring_convenience_fee', '<=', $tofind);
                                break;
                            case 'gt':
                                $query->where('accounting_recurring_transactions.trans_recurring_convenience_fee', '>', $tofind);
                                break;
                            case 'ge':
                                $query->where('accounting_recurring_transactions.trans_recurring_convenience_fee', '>=', $tofind);
                                break;
                        }
                        break;
                    case 'num_left':
                        switch ($tocmp) {
                            case 'eq':
                                $query->where('accounting_recurring_transactions.trans_numleft', '=', $tofind);
                                break;
                            case 'ne':
                                $query->where('accounting_recurring_transactions.trans_numleft', '!=', $tofind);
                                break;
                            case 'lt':
                                $query->where('accounting_recurring_transactions.trans_numleft', '<', $tofind);
                                break;
                            case 'le':
                                $query->where('accounting_recurring_transactions.trans_numleft', '<=', $tofind);
                                break;
                            case 'gt':
                                $query->where('accounting_recurring_transactions.trans_numleft', '>', $tofind);
                                break;
                            case 'ge':
                                $query->where('accounting_recurring_transactions.trans_numleft', '>=', $tofind);
                                break;
                        }
                        break;

                    case 'pay_method':
                        if ($tofind == 'ec') {
                            $query->where('trans_payment_type', 'ec');
                        } else {
                            $query->where('trans_payment_type', 'cc');
                        }
                        break;
                    case 'stype':
                        if ($tofind == '0') {
                            $query->where('dynamic', 0);
                        } else {
                            $query->where('dynamic', 1);
                        }
                        break;
                    case 'trans_next_date':
                        switch ($tocmp) {
                            case 'eq':
                                $query->whereRaw('DATE(trans_next_post_date) = ?', [$tofind]);
                                break;
                            case 'ne':
                                $query->whereRaw('DATE(trans_next_post_date) != ?', [$tofind]);
                                break;
                            case 'lt':
                                $query->whereRaw('DATE(trans_next_post_date) < ?', [$tofind]);
                                break;
                            case 'le':
                                $query->whereRaw('DATE(trans_next_post_date) <= ?', [$tofind]);
                                break;
                            case 'gt':
                                $query->whereRaw('DATE(trans_next_post_date) > ?', [$tofind]);
                                break;
                            case 'ge':
                                $query->whereRaw('DATE(trans_next_post_date) >= ?', [$tofind]);
                                break;
                        }
                        break;
                    case 'trans_last_date':
                        switch ($tocmp) {
                            case 'eq':
                                $query->whereRaw('DATE(trans_last_post_date) = ?', [$tofind]);
                                break;
                            case 'ne':
                                $query->whereRaw('DATE(trans_last_post_date) != ?', [$tofind]);
                                break;
                            case 'lt':
                                $query->whereRaw('DATE(trans_last_post_date) < ?', [$tofind]);
                                break;
                            case 'le':
                                $query->whereRaw('DATE(trans_last_post_date) <= ?', [$tofind]);
                                break;
                            case 'gt':
                                $query->whereRaw('DATE(trans_last_post_date) > ?', [$tofind]);
                                break;
                            case 'ge':
                                $query->whereRaw('DATE(trans_last_post_date) >= ?', [$tofind]);
                                break;
                        }
                        break;
                }
            }
        }
        return $query;
    }

    function getOneTimeTransactionsByFilter($level, $idlevel, $status = 1, $schedule = 'onetime', $filter = null) {
        $query = DB::table('accounting_recurring_transactions')->join('web_users', 'accounting_recurring_transactions.trans_web_user_id', 'web_users.web_user_id')
                ->where('trans_schedule', 'like', '%' . $schedule . '%')
                ->join('properties', 'properties.id', 'accounting_recurring_transactions.property_id')
                ->where('trans_status', $status)
                ->where('properties.status_clients', 1);
        if ($level == 'M') {
            $query->where('accounting_recurring_transactions.property_id', $idlevel);
            $query->select('trans_id as id', 'properties.name_clients', 'trans_next_post_date', 'trans_numleft', 'web_users.account_number', DB::raw("CONCAT (web_users.first_name, ' ', web_users.last_name) as name"), 'trans_payment_type', 'trans_recurring_net_amount', 'trans_recurring_convenience_fee', DB::raw('(trans_recurring_convenience_fee+trans_recurring_net_amount) as net_charge'), 'dynamic', 'tag');
        } elseif ($level == 'G') {
            $query->join('companies', 'properties.id_companies', 'companies.id');
            $query->where('properties.id_companies', $idlevel);
            $query->select('trans_id as id', 'companies.company_name', 'properties.name_clients', 'trans_next_post_date', 'trans_numleft', 'web_users.account_number', DB::raw("CONCAT (web_users.first_name, ' ', web_users.last_name) as name"), 'trans_payment_type', 'trans_recurring_net_amount', 'trans_recurring_convenience_fee', DB::raw('(trans_recurring_convenience_fee+trans_recurring_net_amount) as net_charge'), 'dynamic', 'tag');
        } elseif ($level == 'P') {
            $query->join('companies', 'properties.id_companies', 'companies.id');
            $query->join('partners', 'properties.id_partners', 'partners.id');
            $query->where('properties.id_partners', $idlevel);
            $query->select('trans_id as id', 'partners.partner_title', 'companies.company_name', 'properties.name_clients', 'trans_next_post_date', 'trans_numleft', 'web_users.account_number', DB::raw("CONCAT (web_users.first_name, ' ', web_users.last_name) as name"), 'trans_payment_type', 'trans_recurring_net_amount', 'trans_recurring_convenience_fee', DB::raw('(trans_recurring_convenience_fee+trans_recurring_net_amount) as net_charge'), 'dynamic', 'tag');
        } elseif ($level == 'B') {
            $partnersA = DB::table('branch_partner')->where('branch_id', $idlevel)->select('id_partners')->get();
            $partners = array();
            foreach ($partnersA as $pa) {
                $partners[] = $pa->id_partners;
            }
            $query->join('companies', 'properties.id_companies', 'companies.id');
            $query->join('partners', 'properties.id_partners', 'partners.id');
            $query->whereIn('properties.id_partners', $partners);
            $query->select('trans_id as id', 'partners.partner_title', 'companies.company_name', 'properties.name_clients', 'trans_next_post_date', 'trans_numleft', 'web_users.account_number', DB::raw("CONCAT (web_users.first_name, ' ', web_users.last_name) as name"), 'trans_payment_type', 'trans_recurring_net_amount', 'trans_recurring_convenience_fee', DB::raw('(trans_recurring_convenience_fee+trans_recurring_net_amount) as net_charge'), 'dynamic', 'tag');
        } else {
            $query->join('companies', 'properties.id_companies', 'companies.id');
            $query->join('partners', 'properties.id_partners', 'partners.id');
            $query->select('trans_id as id', 'partners.partner_title', 'companies.company_name', 'properties.name_clients', 'trans_next_post_date', 'trans_numleft', 'web_users.account_number', DB::raw("CONCAT (web_users.first_name, ' ', web_users.last_name) as name"), 'trans_payment_type', 'trans_recurring_net_amount', 'trans_recurring_convenience_fee', DB::raw('(trans_recurring_convenience_fee+trans_recurring_net_amount) as net_charge'), 'dynamic', 'tag');
        }


        if (!empty($filter) && isset($filter['rules'])) {
            $filters = $filter['rules'];
            foreach ($filters as $rule) {
                if (!isset($rule['data']) || !isset($rule['op']) || !isset($rule['field'])) {
                    continue;
                }
                $tofind = $rule['data'];
                if ($tofind == '') {
                    continue;
                }
                $tocmp = $rule['op'];
                $field = "";
                switch ($rule['field']) {
                    case 'partner':
                        switch ($tocmp) {
                            case 'cn':
                                $query->where('partners.partner_title', 'like', '%' . $tofind . '%');
                                break;
                            case 'nc':
                                $query->where('partners.partner_title', 'not like', '%' . $tofind . '%');
                                break;
                            case 'bw':
                                $query->where('partners.partner_title', 'like', $tofind . '%');
                                break;
                            case 'bn':
                                $query->where('partners.partner_title', 'not like', $tofind . '%');
                                break;
                            case 'ew':
                                $query->where('partners.partner_title', 'like', '%' . $tofind);
                                break;
                            case 'en':
                                $query->where('partners.partner_title', 'not like', '%' . $tofind);
                                break;
                        }
                        break;
                    case 'group':
                        switch ($tocmp) {
                            case 'cn':
                                $query->where('companies.company_name', 'like', '%' . $tofind . '%');
                                break;
                            case 'nc':
                                $query->where('companies.company_name', 'not like', '%' . $tofind . '%');
                                break;
                            case 'bw':
                                $query->where('companies.company_name', 'like', $tofind . '%');
                                break;
                            case 'bn':
                                $query->where('companies.company_name', 'not like', $tofind . '%');
                                break;
                            case 'ew':
                                $query->where('companies.company_name', 'like', '%' . $tofind);
                                break;
                            case 'en':
                                $query->where('companies.company_name', 'not like', '%' . $tofind);
                                break;
                        }
                        break;
                    case 'merchant':
                        switch ($tocmp) {
                            case 'cn':
                                $query->where('properties.name_clients', 'like', '%' . $tofind . '%');
                                break;
                            case 'nc':
                                $query->where('properties.name_clients', 'not like', '%' . $tofind . '%');
                                break;
                            case 'bw':
                                $query->where('properties.name_clients', 'like', $tofind . '%');
                                break;
                            case 'bn':
                                $query->where('properties.name_clients', 'not like', $tofind . '%');
                                break;
                            case 'ew':
                                $query->where('properties.name_clients', 'like', '%' . $tofind);
                                break;
                            case 'en':
                                $query->where('properties.name_clients', 'not like', '%' . $tofind);
                                break;
                        }
                        break;
                    case 'webname':
                        switch ($tocmp) {
                            case 'cn':
                                $query->where('web_users.first_name', 'LIKE', '%' . $tofind . '%');
                                break;
                            case 'nc':
                                $query->where('web_users.first_name', 'not LIKE', '%' . $tofind . '%');
                                break;
                            case 'bw':
                                $query->where('web_users.first_name', 'LIKE', $tofind . '%');
                                break;
                            case 'bn':
                                $query->where('web_users.first_name', 'not LIKE', $tofind . '%');
                                break;
                            case 'ew':
                                $query->where('web_users.first_name', 'LIKE', '%' . $tofind);
                                break;
                            case 'en':
                                $query->where('web_users.first_name', 'not LIKE', '%' . $tofind);
                                break;
                        }
                        break;
                    case 'webnamelast':
                        switch ($tocmp) {
                            case 'cn':
                                $query->where('web_users.last_name', 'LIKE', '%' . $tofind . '%');
                                break;
                            case 'nc':
                                $query->where('web_users.last_name', 'not LIKE', '%' . $tofind . '%');
                                break;
                            case 'bw':
                                $query->where('web_users.last_name', 'LIKE', $tofind . '%');
                                break;
                            case 'bn':
                                $query->where('web_users.last_name', 'not LIKE', $tofind . '%');
                                break;
                            case 'ew':
                                $query->where('web_users.last_name', 'LIKE', '%' . $tofind);
                                break;
                            case 'en':
                                $query->where('web_users.last_name', 'not LIKE', '%' . $tofind);
                                break;
                        }
                        break;

                    case 'webuser':
                        switch ($tocmp) {
                            case 'cn':
                                $query->where('web_users.account_number', 'like' , '%' . $tofind . '%');
                                break;
                            case 'nc':
                                $query->where('web_users.account_number', 'not like' , '%' . $tofind . '%');
                                break;
                            case 'bw':
                                $query->where('web_users.account_number', 'like' , $tofind . '%');
                                break;
                            case 'bn':
                                $query->where('web_users.account_number', 'not like' , $tofind . '%');
                                break;
                            case 'ew':
                                $query->where('web_users.account_number', 'like' , '%' . $tofind);
                                break;
                            case 'en':
                                $query->where('web_users.account_number', 'not like' , '%' . $tofind);
                                break;
                        }
                        break;
                    case 'net_amount':
                        switch ($tocmp) {
                            case 'eq':
                                $query->where('accounting_recurring_transactions.trans_recurring_net_amount', '=', $tofind);
                                break;
                            case 'ne':
                                $query->where('accounting_recurring_transactions.trans_recurring_net_amount', '!=', $tofind);
                                break;
                            case 'lt':
                                $query->where('accounting_recurring_transactions.trans_recurring_net_amount', '<', $tofind);
                                break;
                            case 'le':
                                $query->where('accounting_recurring_transactions.trans_recurring_net_amount', '<=', $tofind);
                                break;
                            case 'gt':
                                $query->where('accounting_recurring_transactions.trans_recurring_net_amount', '>', $tofind);
                                break;
                            case 'ge':
                                $query->where('accounting_recurring_transactions.trans_recurring_net_amount', '>=', $tofind);
                                break;
                        }
                        break;
                    case 'net_fee':
                        switch ($tocmp) {
                            case 'eq':
                                $query->where('accounting_recurring_transactions.trans_recurring_convenience_fee', '=', $tofind);
                                break;
                            case 'ne':
                                $query->where('accounting_recurring_transactions.trans_recurring_convenience_fee', '!=', $tofind);
                                break;
                            case 'lt':
                                $query->where('accounting_recurring_transactions.trans_recurring_convenience_fee', '<', $tofind);
                                break;
                            case 'le':
                                $query->where('accounting_recurring_transactions.trans_recurring_convenience_fee', '<=', $tofind);
                                break;
                            case 'gt':
                                $query->where('accounting_recurring_transactions.trans_recurring_convenience_fee', '>', $tofind);
                                break;
                            case 'ge':
                                $query->where('accounting_recurring_transactions.trans_recurring_convenience_fee', '>=', $tofind);
                                break;
                        }
                        break;
                    case 'num_left':
                        switch ($tocmp) {
                            case 'eq':
                                $query->where('accounting_recurring_transactions.trans_numleft', '=', $tofind);
                                break;
                            case 'ne':
                                $query->where('accounting_recurring_transactions.trans_numleft', '!=', $tofind);
                                break;
                            case 'lt':
                                $query->where('accounting_recurring_transactions.trans_numleft', '<', $tofind);
                                break;
                            case 'le':
                                $query->where('accounting_recurring_transactions.trans_numleft', '<=', $tofind);
                                break;
                            case 'gt':
                                $query->where('accounting_recurring_transactions.trans_numleft', '>', $tofind);
                                break;
                            case 'ge':
                                $query->where('accounting_recurring_transactions.trans_numleft', '>=', $tofind);
                                break;
                        }
                        break;

                    case 'pay_method':
                        if ($tofind == 'ec') {
                            $query->where('trans_payment_type', 'ec');
                        } else {
                            $query->where('trans_payment_type', 'cc');
                        }
                        break;
                    case 'stype':
                        if ($tofind == '0') {
                            $query->where('dynamic', 0);
                        } else {
                            $query->where('dynamic', 1);
                        }
                        break;
                    case 'trans_next_date':
                        switch ($tocmp) {
                            case 'eq':
                                $query->whereRaw('DATE(trans_next_post_date) = ?', [$tofind]);
                                break;
                            case 'ne':
                                $query->whereRaw('DATE(trans_next_post_date) != ?', [$tofind]);
                                break;
                            case 'lt':
                                $query->whereRaw('DATE(trans_next_post_date) < ?', [$tofind]);
                                break;
                            case 'le':
                                $query->whereRaw('DATE(trans_next_post_date) <= ?', [$tofind]);
                                break;
                            case 'gt':
                                $query->whereRaw('DATE(trans_next_post_date) > ?', [$tofind]);
                                break;
                            case 'ge':
                                $query->whereRaw('DATE(trans_next_post_date) >= ?', [$tofind]);
                                break;
                        }
                        break;
                    case 'trans_last_date':
                        switch ($tocmp) {
                            case 'eq':
                                $query->whereRaw('DATE(trans_last_post_date) = ?', [$tofind]);
                                break;
                            case 'ne':
                                $query->whereRaw('DATE(trans_last_post_date) != ?', [$tofind]);
                                break;
                            case 'lt':
                                $query->whereRaw('DATE(trans_last_post_date) < ?', [$tofind]);
                                break;
                            case 'le':
                                $query->whereRaw('DATE(trans_last_post_date) <= ?', [$tofind]);
                                break;
                            case 'gt':
                                $query->whereRaw('DATE(trans_last_post_date) > ?', [$tofind]);
                                break;
                            case 'ge':
                                $query->whereRaw('DATE(trans_last_post_date) >= ?', [$tofind]);
                                break;
                        }
                        break;
                }
            }
        }
        return $query;
    }

    function getDynamicByWebUser($id) {

        $dyn = $this->where('trans_web_user_id', '=', $id)
                ->where('dynamic', '=', '1')
                ->where('trans_status', '=', '1')
                ->groupBy('tag')
                ->get();
        return count($dyn);
    }

    function getDetails($txid) {
        $detail = $this->where('trans_id', $txid)->first();
        $detail->category = \Illuminate\Support\Facades\DB::table('recurring_trans_categories')->where('trans_id', $txid)->get();
        return $detail;
    }

    function cancelAuto($txid) {
        $this->updateStatus($txid, 4);
    }

    function updateStatus($txid, $sts) {
        DB::table('accounting_recurring_transactions')->where('trans_id', $txid)->update(array('trans_status' => $sts));
    }

    function webuser() {
        return $this->belongsTo('\App\Model\WebUsers', 'trans_web_user_id');
    }

    function property() {
        return $this->belongsTo('\App\Model\Properties', 'property_id');
    }

    function saveautopaymentdetails($request, $recurr_id) {
        $data = array();
        if (null !== $request->input('trans_next_post_date')) {
            $data['trans_next_post_date'] = date('Y-m-d H:i:s', strtotime(trim($request->input('trans_next_post_date'))));
        }
        if (null !== $request->input('trans_last_post_date')) {
            if ($request->input('trans_last_post_date') == '-1') {
                $data['trans_last_post_date'] = $request->input('trans_last_post_date');
            } else {
                $day = date('d', strtotime(trim($request->input('trans_next_post_date'))));
                $newdate = trim($request->input('trans_last_post_date') . '|' . $day);
                $newdate = str_replace('|', '-', $newdate);
                $data['trans_last_post_date'] = date('Y-m-d H:i:s', strtotime($newdate));
            }
        }
        if (null !== $request->input('dynamic_trans')) {
            $data['dynamic'] = trim($request->input('dynamic_trans'));
        } else {
            $data['dynamic'] = 0;
        }
        if (null !== $request->input('frequency')) {
            $data['trans_schedule'] = trim($request->input('frequency'));
        }
        if (null !== $request->input('trans_recurring_convenience_fee')) {
            $data['trans_recurring_convenience_fee'] = trim($request->input('trans_recurring_convenience_fee'));
        }
        if (null !== $request->input('echeck_routing_number')) {
            $data['echeck_routing_number'] = trim($request->input('echeck_routing_number'));
        }
        if (null !== $request->input('echeck_account_number')) {
            $data['echeck_account_number'] = trim($request->input('echeck_account_number'));
        }
        if (null !== $request->input('trans_payment_type')) {
            $data['trans_payment_type'] = trim($request->input('trans_payment_type'));
        }
        if (null !== $request->input('trans_recurring_net_amount')) {
            $data['trans_recurring_net_amount'] = trim($request->input('trans_recurring_net_amount'));
            //recalculate new cfee
            $obj_transaction = new Transations();
            $data['trans_recurring_convenience_fee'] = $obj_transaction->getREC_ConvFee($data['trans_recurring_net_amount'], $request->input('property_id'), $data['trans_payment_type'], $data['dynamic']);
            $imported_flag = $obj_transaction->getREC_ImpFlag($request->input('property_id'), "ec");
            if ($imported_flag == 0) {
                if ($obj_transaction->get1recurringInfo($recurr_id, 'imported') == 1) {
                    $data['trans_recurring_convenience_fee'] = 0;
                }
            }
        }
        $data['tag'] = trim($request->input('tag'));

        if(($data['trans_schedule'] == 'quarterly') || ($data['trans_schedule'] == 'quaterly')) {
            //Adjust start date, if needed, to match begining month of a quarter
            $date_parts = explode("-", $data['trans_next_post_date']);
            $quarter_start_month = $date_parts[1];
            if($date_parts[1] > '10') {
                $quarter_start_month = '01';
                $date_parts[0] += 1;
            } else if($date_parts[1] > '07' && $date_parts[1] < '10') {
                $quarter_start_month = '10';
            } else if($date_parts[1] > '04' && $date_parts[1] < '07') {
                $quarter_start_month = '07';
            } else if($date_parts[1] > '01' && $date_parts[1] < '04') {
                $quarter_start_month = '04';
                if(substr($date_parts[2], 0, 2) == '31') {
                    $date_parts[2] = substr_replace($date_parts[2],'30', 0, 2);
                }
            }
            $data['trans_next_post_date'] = $date_parts[0].'-'.$quarter_start_month.'-'.$date_parts[2];
        }

        $obj_transaction = new Transations();
        if (isset($data['trans_next_post_date']) && isset($data['trans_last_post_date']))
            $data['trans_last_post_date'] = $obj_transaction->getformatenddate($data['trans_next_post_date'], $data['trans_last_post_date']);
        if (isset($data['trans_next_post_date']) && isset($data['trans_last_post_date']) && isset($data['trans_schedule']))
            $data['trans_numleft'] = $obj_transaction->getNumleft($data['trans_schedule'], $data['trans_next_post_date'], $data['trans_last_post_date']);
        DB::table('accounting_recurring_transactions')
                ->where('trans_id', $recurr_id)
                ->update($data);
    }

    function savestartomorrowcanceled($request, $recurr_id) {

        $today = date(now());
        $tomorrow = strtotime('+1 day', strtotime($today));
        $nextday = date('Y-m-d 00:00:00', $tomorrow);
        $record=DB::table('accounting_recurring_transactions')->where('trans_id', '=', $recurr_id)->first();
        if($record->trans_schedule=='quarterly' || $record->trans_schedule=='quaterly'){
            $date_parts = explode("-", $nextday);
            $quarter_start_month = $date_parts[1];
            if($date_parts[1] > '10') {
                $quarter_start_month = '01';
                $date_parts[0] += 1;
            } else if($date_parts[1] > '07' && $date_parts[1] < '10') {
                $quarter_start_month = '10';
            } else if($date_parts[1] > '04' && $date_parts[1] < '07') {
                $quarter_start_month = '07';
            } else if($date_parts[1] > '01' && $date_parts[1] < '04') {
                $quarter_start_month = '04';
                if(substr($date_parts[2], 0, 2) == '31') {
                    $date_parts[2] = substr_replace($date_parts[2],'30', 0, 2);
                }
            }
            $nextday = $date_parts[0].'-'.$quarter_start_month.'-'.$date_parts[2];
        }
        DB::table('accounting_recurring_transactions')
                ->where('trans_id', '=', $recurr_id)
                ->update(['trans_next_post_date' => $nextday, 'trans_status' => 1, 'trans_numleft'=>9999]);
    }

    function savestartnextmonthcanceled($request, $recurr_id) {

        $today = date(now());
        $month = strtotime('+1 month', strtotime($today));
        $nextmonth = date('Y-m-d 00:00:00', $month);
        $record=DB::table('accounting_recurring_transactions')->where('trans_id', '=', $recurr_id)->first();
        if($record->trans_schedule=='quarterly' || $record->trans_schedule=='quaterly'){
            $date_parts = explode("-", $nextmonth);
            $quarter_start_month = $date_parts[1];
            if($date_parts[1] > '10') {
                $quarter_start_month = '01';
                $date_parts[0] += 1;
            } else if($date_parts[1] > '07' && $date_parts[1] < '10') {
                $quarter_start_month = '10';
            } else if($date_parts[1] > '04' && $date_parts[1] < '07') {
                $quarter_start_month = '07';
            } else if($date_parts[1] > '01' && $date_parts[1] < '04') {
                $quarter_start_month = '04';
                if(substr($date_parts[2], 0, 2) == '31') {
                    $date_parts[2] = substr_replace($date_parts[2],'30', 0, 2);
                }
            }
            $nextmonth = $date_parts[0].'-'.$quarter_start_month.'-'.$date_parts[2];
        }
        DB::table('accounting_recurring_transactions')
                ->where('trans_id', '=', $recurr_id)
                ->update(['trans_next_post_date' => $nextmonth, 'trans_status' => 1, 'trans_numleft'=>9999]);
    }

    function getDRPAutopay($webuserid, $propertyid) {
        $result = DB::table('accounting_recurring_transactions')
                ->where('property_id', $propertyid)
                ->where('trans_web_user_id', $webuserid)
                ->where('dynamic', 1)
                ->where('trans_status', 1)
                ->get();
        return count($result);
    }

    function getAutopayCount($webuserid, $propertyid) {
        $result = DB::table('accounting_recurring_transactions')
                ->where('property_id', $propertyid)
                ->where('trans_web_user_id', $webuserid)
                ->where('trans_status', 1)
                ->get();
        return count($result);
    }

    function updateWalkIn($trans_id, $status){

      try{
          DB::table('transactions_phone_fee')
              ->where('trans_id', $trans_id)
              ->where('is_recurring', 1)
              ->update([
                  'walk_in' => $status,
              ]);
          return true;
      }catch (\Exception $e){
          return false;
        }
    }

    function getWalkIn($transID){
        $walkin = DB::table('transactions_phone_fee')
            ->where('trans_id', $transID)
            ->where('is_recurring', 1)
            ->select('trans_id', 'phone_fee', 'walk_in', 'is_recurring')
            ->first();

        return json_decode(json_encode($walkin), true);
    }

    function updatePhoneFee($trans_id, $value){

        try{
            DB::table('transactions_phone_fee')
                ->where('trans_id', $trans_id)
                ->where('is_recurring', 1)
                ->update([
                    'phone_fee' => $value,
                ]);
            return true;
        }catch (\Exception $e){
            return false;
        }
    }
    
    /**
     * Validates if an auto payment has an existing profile
     * @param int $trans_id the id of the auto payment
     * @return boolean true if the profile exists, otherwise false
     */
    function existsProfileByTransId($trans_id) {
        $hasProfile = false;
        $profileId = DB::table('accounting_recurring_transactions')
                ->join('profiles', 'profiles.id', '=', 'accounting_recurring_transactions.profile_id')
                ->where('accounting_recurring_transactions.trans_id', $trans_id)
                ->value('profiles.id');
        if (!empty($profileId)) {
            $hasProfile = true;
        }
        return $hasProfile;
    }

}
