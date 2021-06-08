<?php

namespace App\Http\Controllers;

use App\Models\Audit;
use DB;
use Illuminate\Http\Request;
use App\Models\CustomPages;
use App\Providers\RevoPayAuditLogger;
use App\Models\WebUsers;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\View;

class CustomPageController extends Controller {
    
    function loadpage($subdomain,$locale='en'){
        session()->flush();
        \App::setLocale($locale);
        $cp=new CustomPages();
        $cpdata=$cp->getPageData($subdomain);
        if(empty($cpdata)){
            return view('custom.donotexists',['pageTitle' => 'Error!']);
        }
        if($cpdata['status']==0){
            return view ('custom.inactive',['pageTitle' => 'Inactive']);
        }
        //exists and active
        $level=$cpdata['level'];
        $idlevel=$cpdata['idlevel'];
        $cpsettings=json_decode($cpdata['custom_settings'],true);
        $Settings=$this->getSettings($level, $idlevel);
        return view('custom.findme',['data'=>$cpdata,'settings'=>$Settings, 'cpdata'=>$cpsettings , 'pageTitle' => 'Find Me']);
    }
    
    function findpage($subdomain,Request $request,$locale='en'){
        \App::setLocale($locale);
        $cp=new CustomPages();
        $cpdata=$cp->getPageData($subdomain);
        if(empty($cpdata)){
            return view('custom.donotexists',['pageTitle' => 'Error!']);
        }
        if($cpdata['status']==0){
            return view ('custom.inactive',['pageTitle' => 'Inactive']);
        }
        //exists and active
        $level=$cpdata['level'];
        $idlevel=$cpdata['idlevel'];
        $cpsettings=json_decode($cpdata['custom_settings'],true);
        $Settings=$this->getSettings($level, $idlevel);
        
        $input=$request->all();
        if(!isset($input['_class'])){
            return redirect()->route('loadpage',['subdomain'=>$subdomain,'locale'=>$locale]);
        }
        $wu=new \App\Models\WebUsers();
        if($level=='P'){
            $idproperty=0;
            $idcompany=0;
            $idpartner=$idlevel;
        }
        elseif($level=='G'){
            $idproperty=0;
            $idcompany=$idlevel;
            $idpartner=0;
        }
        elseif($level=='M'){
            $idproperty=$idlevel;
            $idcompany=0;
            $idpartner=0;
        }
        else {
            return redirect()->route('loadpage',['subdomain'=>$subdomain,'locale'=>$locale]);
        }
        session(['find_input'=>$input]);
        session()->save();
        if($input['_class']==1){
            //find account number
            $acc=trim($input['account_number']);
            if($acc==''){
                return redirect()->route('loadpage',['subdomain'=>$subdomain,'locale'=>$locale]);
            }
            $params=['account_number'=>$acc];
        }
        elseif($input['_class']==2){
            //find other parameters
            $fname=trim($input['fname']);
            $lname=trim($input['lname']);
            $email=trim($input['email']);
            $pname=trim($input['pname']);
            $address=trim($input['address']);
            $city=trim($input['city']);
            $state=trim($input['state']);
            $zip=trim($input['zip']);
            
            $params=array();
            if($fname!=''){
                $params['first_name']=$fname;
            }
            if($lname!=''){
                $params['last_name']=$lname;
            }
            if($email!=''){
                $params['email']=$email;
            }
            if($pname!=''){
                $params['name_clients']=$pname;
            }
            if($address!=''){
                $params['address']=$address;
            }
            if($city!=''){
                $params['city']=$city;
            }
            if($state!=''){
                $params['state']=$state;
            }
            if($zip!=''){
                $params['zip']=$zip;
            }
            if(count($params)==0){
                return redirect()->back()->withErrors(['Please input any personal information and try again']);
            }
        }
        else {
            return redirect()->route('loadpage',['subdomain'=>$subdomain,'locale'=>$locale]);
        }
        $params['includeUID']=true;
        $limit=10;
        if(isset($cpsettings['limit_search'])){
            $limit=($cpsettings['limit_search']*1)+2;
        }
        $result=$wu->FindOpen2($params, $idproperty, [1,46,998], false, $idcompany, $idpartner, $limit);
        if(count($result)>0){
            $limit=5;
            if(isset($cpsettings['limit_search'])){
                $limit=$cpsettings['limit_search']*1;
            }
            if(count($result)>$limit){
                return redirect()->back()->withErrors(['Results above the limit - Please change the way you search and try again']);
            }
            return view('custom.select',['data'=>$cpdata,'settings'=>$Settings, 'cpdata'=>$cpsettings , 'pageTitle' => 'Users Found!','users'=>$result]);
        }
        else {
            return redirect()->back()->withErrors(['User Not Found - Please review your information and try again']);
        }
    }
    
    function findpageget($subdomain,Request $request,$locale='en'){
        \App::setLocale($locale);
        $cp=new CustomPages();
        $cpdata=$cp->getPageData($subdomain);
        if(empty($cpdata)){
            return view('custom.donotexists',['pageTitle' => 'Error!']);
        }
        if($cpdata['status']==0){
            return view ('custom.inactive',['pageTitle' => 'Inactive']);
        }
        //exists and active
        $level=$cpdata['level'];
        $idlevel=$cpdata['idlevel'];
        $cpsettings=json_decode($cpdata['custom_settings'],true);
        $Settings=$this->getSettings($level, $idlevel);
        if(!session()->has('find_input')){
            return redirect()->route('loadpage',['subdomain'=>$subdomain,'locale'=>$locale]);
        }
        
        $input=session('find_input');
        $wu=new \App\Models\WebUsers();
        if($level=='P'){
            $idproperty=0;
            $idcompany=0;
            $idpartner=$idlevel;
        }
        elseif($level=='G'){
            $idproperty=0;
            $idcompany=$idlevel;
            $idpartner=0;
        }
        elseif($level=='M'){
            $idproperty=$idlevel;
            $idcompany=0;
            $idpartner=0;
        }
        else {
            return redirect()->route('loadpage',['subdomain'=>$subdomain,'locale'=>$locale]);
        }
        if($input['_class']==1){
            //find account number
            $acc=trim($input['account_number']);
            if($acc==''){
                return redirect()->route('loadpage',['subdomain'=>$subdomain,'locale'=>$locale]);
            }
            $params=['account_number'=>$acc];
        }
        elseif($input['_class']==2){
            //find other parameters
            $fname=trim($input['fname']);
            $lname=trim($input['lname']);
            $email=trim($input['email']);
            $pname=trim($input['pname']);
            $address=trim($input['address']);
            $city=trim($input['city']);
            $state=trim($input['state']);
            $zip=trim($input['zip']);
            $params=array();
            if($fname!=''){
                $params['first_name']=$fname;
            }
            if($lname!=''){
                $params['last_name']=$lname;
            }
            if($email!=''){
                $params['email_address']=$email;
            }
            if($pname!=''){
                $params['name_clients']=$pname;
            }
            if($address!=''){
                $params['address']=$address;
            }
            if($city!=''){
                $params['city']=$city;
            }
            if($state!=''){
                $params['state']=$state;
            }
            if($zip!=''){
                $params['zip']=$zip;
            }
        }
        else {
            return redirect()->route('loadpage',['subdomain'=>$subdomain,'locale'=>$locale]);
        }
        $params['includeUID']=true;
        $limit=10;
        if(isset($cpsettings['limit_search'])){
            $limit=($cpsettings['limit_search']*1)+2;
        }
        $result=$wu->FindOpen2($params, $idproperty, [1,46,998], false, $idcompany, $idpartner,$limit);
        if(count($result)>0){
            $limit=5;
            if(isset($cpsettings['limit_search'])){
                $limit=$cpsettings['limit_search']*1;
            }
            if(count($result)>$limit){
                return redirect()->back()->withErrors(['Results above the limit - Please change the way you search and try again']);
            }
            return view('custom.select',['data'=>$cpdata,'settings'=>$Settings, 'cpdata'=>$cpsettings , 'pageTitle' => 'Users Found!','users'=>$result]);
        }
        else {
            return redirect()->back()->withErrors(['User Not Found - Please review your information and try again']);
        }
    }
    
    function qpaypage($subdomain,Request $request,$idx='0',$locale='en'){
        \App::setLocale($locale);
        $cp=new CustomPages();
        $cpdata=$cp->getPageData($subdomain);
        if(empty($cpdata)){
            return view('custom.donotexists',['pageTitle' => 'Error!']);
        }
        if($cpdata['status']==0){
            return view ('custom.inactive',['pageTitle' => 'Inactive']);
        }
        //exists and active
        $level=$cpdata['level'];
        $idlevel=$cpdata['idlevel'];
        $cpsettings=json_decode($cpdata['custom_settings'],true);
        if($idx=='0'){
            if($request->session()->has('qpay1s')){
                $sdata=json_decode($request->session()->get('qpay1s'),true);
                $wuid=$sdata['wuid'];
            }
            else {
               return redirect()->route('loadpage',['subdomain'=>$subdomain]); 
            }
        }
        else {
            $wuid= base64_decode($idx);
        }
        $wu=new \App\Models\WebUsers();
        $request->session()->flush();
        if($wu->validateUserByscope($wuid, $level, $idlevel)){
            //load pay
            if(!isset($cpsettings['not_onetime'])){
                //read credentials for onetime
                $datauser=$wu->getWebUserdetailFull($wuid);
                $Settings=$this->getSettings('M',$datauser['merchant_id']);
                session(['settings'=>$Settings]);
                session()->save();
                if(!isset($sdata)){
                    $sdata=array();
                }
                $pid=$wu->get1UserInfo($wuid, 'property_id');
                //get onetime limits from merchant
                $onlycustompay=false;
                if(isset($cpsettings['onlycustompay']) && $cpsettings['onlycustompay']==1){
                    $onlycustompay=true;
                }
                $cpdata['limits']=$cp->getLimits4Custom($pid, 0, $onlycustompay);
                $limits=$cpdata['limits'];
                if(empty($limits['max'])){
                    return view ('custom.notpaymethod',['data'=>$cpdata,'settings'=>$Settings, 'cpdata'=>$cpsettings ,'pageTitle' => 'Missing Payment Method']);
                }
                return view('custom.qpaystep1',['data'=>$cpdata,'settings'=>$Settings, 'cpdata'=>$cpsettings , 'pageTitle' => 'Make a Payment','user'=>$datauser,'sdata'=>$sdata]);
            }
            else {
                return redirect()->route('entryautopay',['subdomain'=>$subdomain,'idx'=>$idx]);
            }
        }
        else {
            return redirect()->back()->withErrors(['msg', 'User Not Found - Please review your information and try again']);
        }
    }
    
    function qpaystep2page($subdomain,Request $request,$locale='en'){
        \App::setLocale($locale);
        $cp=new CustomPages();
        $cpdata=$cp->getPageData($subdomain);
        if(empty($cpdata)){
            return view('custom.donotexists',['pageTitle' => 'Error!']);
        }
        if($cpdata['status']==0){
            return view ('custom.inactive',['pageTitle' => 'Inactive']);
        }
        //exists and active
        $level=$cpdata['level'];
        $idlevel=$cpdata['idlevel'];
        $cpsettings=json_decode($cpdata['custom_settings'],true);

        $input_data=$request->all();
        if(isset($input_data['_class'])){
            list($wuid,$time)= explode('|',\Illuminate\Support\Facades\Crypt::decrypt($input_data['_class']));
            if(($time+3600)<time()){
                return redirect()->route('loadpage',['subdomain'=>$subdomain])->withErrors(['Invalid Data - Please review your information and try again']);
            }
            $input_data['wuid']=$wuid;
        }
        else {
            return redirect()->route('loadpage',['subdomain'=>$subdomain])->withErrors(['Invalid Data - Please review your information and try again']);
        }

        $wu=new \App\Models\WebUsers();

        if($wu->validateUserByscope($wuid, $level, $idlevel)){
            //update email if not empty
            if(trim($input_data['_email'])!=''){
                $wu->set1UserInfo($wuid, 'email_address', trim($input_data['_email']));
            }
            //validate amounts
            $total_amount=0;
            $categories=array();
            foreach($input_data as $key=>$value){
                if(substr($key,0,10)=='xcheckpay_'){
                    $nkey=str_replace('xcheckpay_','ipay_',$key);
                    if(isset($input_data[$nkey])){
                        $amount=$input_data[$nkey]*1;
                        if($amount>0){
                            $total_amount+=$amount;
                            $categories[]=array('id'=>str_replace('xcheckpay_','',$key),'amount'=>$amount,'description'=>$cp->getCatName(str_replace('xcheckpay_','',$key)),'name'=>$cp->getCatName(str_replace('xcheckpay_','',$key)),'qty'=>1);
                        }
                    }
                }
            }
            if($total_amount<=0){
                return redirect()->back()->withErrors([trans('messages.invalidamount')]);
            }
            $input_data['categories']=$categories;
            $input_data['total_amount']=$total_amount;
            //load payment method
            //get merchant from user
            $pid=$wu->get1UserInfo($wuid, 'property_id');
            $Settings=$this->getSettings('M',$pid);
            //get onetime credentials from merchant
            $onlycustompay=false;
            if(isset($cpsettings['onlycustompay']) && $cpsettings['onlycustompay']==1){
                $onlycustompay=true;
            }
            $credentials=$cp->getCredentials4Custom($pid, $total_amount,0,$onlycustompay);
            if(empty($credentials)){
                return view ('custom.notpaymethod',['data'=>$cpdata,'settings'=>$Settings, 'cpdata'=>$cpsettings ,'pageTitle' => 'Missing Payment Method']);
            }
            $input_data['credentials']=$credentials;
            session(['qpay1s'=>json_encode($input_data)]);
            session()->save();
            $datauser=$wu->getWebUserdetailFull($wuid);
            return view('custom.qpaystep2',['data'=>$cpdata,'settings'=>$Settings, 'cpdata'=>$cpsettings , 'pageTitle' => 'Make a Payment','user'=>$datauser,'credentials'=>$credentials]);
        }
        else {
            return redirect()->route('loadpage',['subdomain'=>$subdomain])->withErrors(['msg', 'Invalid Data - Please review your information and try again']);
        }
    }
    
    function qpaystep2apage($subdomain,Request $request,$locale='en'){
        \App::setLocale($locale);
        $cp=new CustomPages();
        $cpdata=$cp->getPageData($subdomain);
        if(empty($cpdata)){
            return view('custom.donotexists',['pageTitle' => 'Error!']);
        }
        if($cpdata['status']==0){
            return view ('custom.inactive',['pageTitle' => 'Inactive']);
        }
        //exists and active
        $level=$cpdata['level'];
        $idlevel=$cpdata['idlevel'];
        $cpsettings=json_decode($cpdata['custom_settings'],true);
        
        if(!session()->has('qpay1s')){
           return redirect()->route('loadpage',['subdomain'=>$subdomain])->withErrors(['Invalid Data - Please review your information and try again']); 
        }
        if(!session()->has('payment')){
           //return redirect()->route('loadpage',['subdomain'=>$subdomain])->withErrors(['Invalid Data - Please review your information and try again']); 
        }
        
        $input_data=json_decode(session()->get('qpay1s'),true);
        $wuid=$input_data['wuid'];
        $total_amount=$input_data['total_amount'];
        $wu=new \App\Models\WebUsers();

        if($wu->validateUserByscope($wuid, $level, $idlevel)){
            
            //load payment method
            //get merchant from user
            $pid=$wu->get1UserInfo($wuid, 'property_id');
            $Settings=$this->getSettings('M',$pid);
            //get onetime credentials from merchant
                        $onlycustompay=false;
            if(isset($cpsettings['onlycustompay']) && $cpsettings['onlycustompay']==1){
                $onlycustompay=true;
            }
            $credentials=$cp->getCredentials4Custom($pid, $total_amount,0,$onlycustompay);
            if(empty($credentials)){
                return view ('custom.notpaymethod',['data'=>$cpdata,'settings'=>$Settings, 'cpdata'=>$cpsettings ,'pageTitle' => 'Missing Payment Method']);
            }
            $datauser=$wu->getWebUserdetailFull($wuid);
            return view('custom.qpaystep2',['data'=>$cpdata,'settings'=>$Settings, 'cpdata'=>$cpsettings , 'pageTitle' => 'Make a Payment','user'=>$datauser,'credentials'=>$credentials,'sdata'=>json_decode(session()->get('payment'),true)]);
        }
        else {
            return redirect()->route('loadpage',['subdomain'=>$subdomain])->withErrors(['Invalid Data - Please review your information and try again']);
        }
    }
    
    function autopage($subdomain,Request $request,$idx=0,$locale='en'){
        \App::setLocale($locale);
        $cp=new CustomPages();
        $cpdata=$cp->getPageData($subdomain);
        if(empty($cpdata)){
            return view('custom.donotexists',['pageTitle' => 'Error!']);
        }
        if($cpdata['status']==0){
            return view ('custom.inactive',['pageTitle' => 'Inactive']);
        }
        //exists and active
        $level=$cpdata['level'];
        $idlevel=$cpdata['idlevel'];
        $cpsettings=json_decode($cpdata['custom_settings'],true);
        $Settings=$this->getSettings($level, $idlevel);
        if($idx=='0'){
            if($request->session()->has('qpay1s')){
                $sdata=json_decode($request->session()->get('qpay1s'),true);
                $wuid=$sdata['wuid'];
            }
            else {
               return redirect()->route('loadpage',['subdomain'=>$subdomain]); 
            }
        }
        else {
            list($wuid)= explode('|',base64_decode($idx));
        }
        $wu=new \App\Models\WebUsers();
        $request->session()->flush();
        if($wu->validateUserByscope($wuid, $level, $idlevel)){
            //load pay
            if(!isset($cpsettings['not_autopay'])){
                //read credentials for onetime
                $datauser=$wu->getWebUserdetailFull($wuid);
                $Settings=$this->getSettings('M',$datauser['merchant_id']);
                session(['settings'=>$Settings]);
                session()->save();
                if(count($datauser['autos'])>=$Settings['MAXRECURRINGPAYMENTPERUSER']){
                    return redirect()->back()->withErrors(['User is not allowed to create scheduled payments. Max limit reached.']);
                }
                if(!isset($sdata)){
                    $sdata=array('wuid'=>$wuid);
                }
                if(isset($cpsettings['allowed_days'])){
                    $allowedD=explode('|',$cpsettings['allowed_days']);
                }
                elseif(isset($Settings['DAYSAUTOPAY'])) {
                    list($a,$b)=explode('|',$Settings['DAYSAUTOPAY']);
                    $allowedD = array();
                    for ($j = $a; $j <= $b; $j++) {
                        $allowedD[] = $j;
                    }
                }else {
                    $allowedD = array();
                    for ($a = 1; $a <= 31; $a++) {
                        $allowedD[] = $a;
                    }
                }
                if(isset($cpsettings['allowed_days_drp'])){
                    $allowedDR=explode('|',$cpsettings['allowed_days_drp']);
                }
                elseif(isset($Settings['DRPDAYSAUTOPAY'])) {
                    list($a,$b)=explode('|',$Settings['DRPDAYSAUTOPAY']);
                    $allowedDR = array();
                    for ($j = $a; $j <= $b; $j++) {
                        $allowedDR[] = $j;
                    }
                }else {
                    $allowedDR = array();
                    for ($a = 1; $a <= 31; $a++) {
                        $allowedDR[] = $a;
                    }
                }
                $limitM=24;
                if(isset($cpsettings['limit_months'])){
                    $limitM=$cpsettings['limit_months'];
                }
                $allowedM=array();
                for($i=0;$i<=$limitM;$i++){
                    $allowedM[]=date('Y-m',strtotime('+'.$i.' months'));
                }
                if(isset($cpsettings['allowed_frequency'])){
                    $allowedF=$cpsettings['allowed_frequency'];
                }
                else {
                    $allowedF=array('monthly|Monthly', 'quarterly|Quarterly', 'triannually|Tri-Annual', 'biannually|Semi-Annual', 'annually|Annual', 'weekly|Weekly', 'biweekly|Bi-Weekly');
                }
                $allowedE=array();
                for($i=1;$i<=36;$i++){
                    $allowedE[]=date('Y-m',strtotime('+'.$i.' months'));
                }
                $pid=$wu->get1UserInfo($wuid, 'property_id');
                //get auto limits from merchant
                $onlycustompay=false;
                if(isset($cpsettings['onlycustompay']) && $cpsettings['onlycustompay']==1){
                    $onlycustompay=true;
                }
                
                $cpdata['limits']=$cp->getLimits4Custom($pid, 1, $onlycustompay);
                $limits=$cpdata['limits'];
                if(empty($limits['max'])){
                    return view ('custom.notpaymethod',['data'=>$cpdata,'settings'=>$Settings, 'cpdata'=>$cpsettings ,'pageTitle' => 'Missing Payment Method']);
                }
                
                $activeAutos = \Illuminate\Support\Facades\DB::table('accounting_recurring_transactions')->where('trans_web_user_id', $wuid)->where('trans_status', 1)->count();
                
                return view('custom.autostep1',['allowedE'=>$allowedE, 'allowedF'=>$allowedF, 'allowedM'=>$allowedM, 'allowedD'=>$allowedD, 'allowedDR'=>$allowedDR, 'data'=>$cpdata,'settings'=>$Settings, 'cpdata'=>$cpsettings , 'pageTitle' => 'Schedule a Payment','user'=>$datauser,'sdata'=>$sdata, 'activeAutos'=>$activeAutos]);
            }
            else {
                return redirect()->route('loadpage',['subdomain'=>$subdomain,'locale'=>$locale]);
            }
        }
        else {
            return redirect()->back()->withErrors(['User Not Found - Please review your information and try again']);
        }
    }
    
    function autopaystep2page($subdomain,Request $request,$locale='en'){
        \App::setLocale($locale);
        $cp=new CustomPages();
        $cpdata=$cp->getPageData($subdomain);
        if(empty($cpdata)){
            return view('custom.donotexists',['pageTitle' => 'Error!']);
        }
        if($cpdata['status']==0){
            return view ('custom.inactive',['pageTitle' => 'Inactive']);
        }
        //exists and active
        $level=$cpdata['level'];
        $idlevel=$cpdata['idlevel'];
        $cpsettings=json_decode($cpdata['custom_settings'],true);

        $input_data=$request->all();
        if(isset($input_data['_class'])){
            list($wuid,$time)= explode('|',\Illuminate\Support\Facades\Crypt::decrypt($input_data['_class']));
            if(($time+3600)<time()){
                return redirect()->route('loadpage',['subdomain'=>$subdomain])->withErrors(['Invalid Data - Please review your information and try again']);
            }
            $input_data['wuid']=$wuid;
        }
        else {
            return redirect()->route('loadpage',['subdomain'=>$subdomain])->withErrors(['Invalid Data - Please review your information and try again']);
        }

        $wu=new \App\Models\WebUsers();

        if($wu->validateUserByscope($wuid, $level, $idlevel)){
            //update email if not empty
            if(trim($input_data['_email'])!=''){
                $wu->set1UserInfo($wuid, 'email_address', trim($input_data['_email']));
            }
            if(isset($cpsettings['allowed_frequency'])){
                $allowedF=$cpsettings['allowed_frequency'];
            }
            else {
                $allowedF=array('monthly|Monthly', 'quarterly|Quarterly', 'triannually|Tri-Annual', 'biannually|Semi-Annual', 'annually|Annual', 'weekly|Weekly', 'biweekly|Bi-Weekly');
            }
            foreach($allowedF as $aF){
                list($v,$t)=explode('|',$aF);
                if($v==$input_data['xfreq']){
                    $input_data['xfreqtext']=$t;
                }
            }
            if($input_data['xtype']=='drp'){
                $input_data['total_amount']=0;
                $input_data['categories']=array();
                $total_amount=0;
            }
            else {
                //validate amounts
                $total_amount=0;
                $categories=array();
                foreach($input_data as $key=>$value){
                    if(substr($key,0,10)=='xcheckpay_'){
                        $nkey=str_replace('xcheckpay_','ipay_',$key);
                        if(isset($input_data[$nkey])){
                            $amount=$input_data[$nkey]*1;
                            if($amount>0){
                                $total_amount+=$amount;
                                $categories[]=array('id'=>str_replace('xcheckpay_','',$key),'amount'=>$amount,'description'=>$cp->getCatName(str_replace('xcheckpay_','',$key)),'name'=>$cp->getCatName(str_replace('xcheckpay_','',$key)),'qty'=>1);
                            }
                        }
                    }
                }
                if($total_amount<=0){
                    return redirect()->back()->withErrors([trans('messages.invalidamount')]);
                }
                $input_data['categories']=$categories;
                $input_data['total_amount']=$total_amount;
            }
            //load payment method
            //get merchant from user
            $pid=$wu->get1UserInfo($wuid, 'property_id');
            $Settings=$this->getSettings('M',$pid);
            //get auto credentials from merchant
            $onlycustompay=false;
            if(isset($cpsettings['onlycustompay']) && $cpsettings['onlycustompay']==1){
                $onlycustompay=true;
            }

            $credentials=$cp->getCredentials4Custom($pid, $total_amount,1,$onlycustompay,$input_data['xtype']);
            if(empty($credentials)){
                return view ('custom.notpaymethod',['data'=>$cpdata,'settings'=>$Settings, 'cpdata'=>$cpsettings ,'pageTitle' => 'Missing Payment Method']);
            }
            $input_data['credentials']=$credentials;
            session(['qpay1s'=>json_encode($input_data)]);
            session()->save();
            $datauser=$wu->getWebUserdetailFull($wuid);
            return view('custom.autopaystep2',['data'=>$cpdata,'settings'=>$Settings, 'cpdata'=>$cpsettings , 'pageTitle' => 'Make a Payment','user'=>$datauser,'credentials'=>$credentials]);
        }
        else {
            return redirect()->route('loadpage',['subdomain'=>$subdomain])->withErrors(['Invalid Data - Please review your information and try again']);
        }
    }
    
    function autopaystep2apage($subdomain,Request $request,$locale='en'){
        \App::setLocale($locale);
        $cp=new CustomPages();
        $cpdata=$cp->getPageData($subdomain);
        if(empty($cpdata)){
            return view('custom.donotexists',['pageTitle' => 'Error!']);
        }
        if($cpdata['status']==0){
            return view ('custom.inactive',['pageTitle' => 'Inactive']);
        }
        //exists and active
        $level=$cpdata['level'];
        $idlevel=$cpdata['idlevel'];
        $cpsettings=json_decode($cpdata['custom_settings'],true);

        $wu=new \App\Models\WebUsers();

        if(!session()->has('qpay1s')){
           return redirect()->route('loadpage',['subdomain'=>$subdomain])->withErrors(['Invalid Data - Please review your information and try again']); 
        }
        if(!session()->has('payment')){
           //return redirect()->route('loadpage',['subdomain'=>$subdomain])->withErrors(['Invalid Data - Please review your information and try again']); 
        }
        
        $input_data=json_decode(session()->get('qpay1s'),true);
        $wuid=$input_data['wuid'];
        $total_amount=$input_data['total_amount'];
        
        if($wu->validateUserByscope($wuid, $level, $idlevel)){
            if(isset($cpsettings['allowed_frequency'])){
                $allowedF=$cpsettings['allowed_frequency'];
            }
            else {
                $allowedF=array('monthly|Monthly', 'quarterly|Quarterly', 'triannually|Tri-Annual', 'biannually|Semi-Annual', 'annually|Annual', 'weekly|Weekly', 'biweekly|Bi-Weekly');
            }
            foreach($allowedF as $aF){
                list($v,$t)=explode('|',$aF);
                if($v==$input_data['xfreq']){
                    $input_data['xfreqtext']=$t;
                }
            }
            //load payment method
            //get merchant from user
            $pid=$wu->get1UserInfo($wuid, 'property_id');
            $Settings=$this->getSettings('M',$pid);
            //get auto credentials from merchant
            $onlycustompay=false;
            if(isset($cpsettings['onlycustompay']) && $cpsettings['onlycustompay']==1){
                $onlycustompay=true;
            }

            $credentials=$cp->getCredentials4Custom($pid, $total_amount,1,$onlycustompay,$input_data['xtype']);
            if(empty($credentials)){
                return view ('custom.notpaymethod',['data'=>$cpdata,'settings'=>$Settings, 'cpdata'=>$cpsettings ,'pageTitle' => 'Missing Payment Method']);
            }
            $input_data['credentials']=$credentials;
            $datauser=$wu->getWebUserdetailFull($wuid);
            return view('custom.autopaystep2',['data'=>$cpdata,'settings'=>$Settings, 'cpdata'=>$cpsettings , 'pageTitle' => 'Make a Payment','user'=>$datauser,'credentials'=>$credentials,'sdata'=>json_decode(session()->get('payment'),true)]);
        }
        else {
            return redirect()->route('loadpage',['subdomain'=>$subdomain])->withErrors(['Invalid Data - Please review your information and try again']);
        }
    }
   
    function exitpage($subdomain,Request $request,$locale='en'){
        \App::setLocale($locale);
        $cp=new CustomPages();
        $cpdata=$cp->getPageData($subdomain);
        if(empty($cpdata)){
            return view('custom.donotexists',['pageTitle' => 'Error!']);
        }
        if($cpdata['status']==0){
            return view ('custom.inactive',['pageTitle' => 'Inactive']);
        }
        //exists and active
        $level=$cpdata['level'];
        $idlevel=$cpdata['idlevel'];
        $cpsettings=json_decode($cpdata['custom_settings'],true);
        $Settings=$this->getSettings($level, $idlevel);
        session()->flush();
        session()->save();
        if(isset($cpsettings['nothx'])){
           return redirect()->route('loadpage',['subdomain'=>$subdomain]); 
        }
        return view('custom.exit',['data'=>$cpdata,'settings'=>$Settings, 'cpdata'=>$cpsettings , 'pageTitle' => 'Thank you!']);
    }
    
    function timeoutpage($subdomain,Request $request,$locale='en'){
        \App::setLocale($locale);
        $cp=new CustomPages();
        $cpdata=$cp->getPageData($subdomain);
        if(empty($cpdata)){
            return view('custom.donotexists',['pageTitle' => 'Error!']);
        }
        if($cpdata['status']==0){
            return view ('custom.inactive',['pageTitle' => 'Inactive']);
        }
        //exists and active
        $level=$cpdata['level'];
        $idlevel=$cpdata['idlevel'];
        $cpsettings=json_decode($cpdata['custom_settings'],true);
        $Settings=$this->getSettings($level, $idlevel);
        return view('custom.timeout',['data'=>$cpdata,'settings'=>$Settings, 'cpdata'=>$cpsettings , 'pageTitle' => 'Session Timeout']);
    }

    function qpayccpagea($subdomain,Request $request,$locale='en'){
        \App::setLocale($locale);
        $cp=new CustomPages();
        $cpdata=$cp->getPageData($subdomain);
        if(empty($cpdata)){
            return view('custom.donotexists',['pageTitle' => 'Error!']);
        }
        if($cpdata['status']==0){
            return view ('custom.inactive',['pageTitle' => 'Inactive']);
        }
        //exists and active
        $level=$cpdata['level'];
        $idlevel=$cpdata['idlevel'];
        $cpsettings=json_decode($cpdata['custom_settings'],true);
        $uivr=new \App\Models\Ivr();
        $wu = new \App\Models\WebUsers();
        if(!session()->has('qpay1s')){
           return redirect()->route('loadpage',['subdomain'=>$subdomain])->withErrors(['Session expired - Please try again']); 
        }
        if(!session()->has('payment')){
           return redirect()->route('loadpage',['subdomain'=>$subdomain])->withErrors(['Session expired - Please try again']); 
        }
        
        $input_data=json_decode(session()->get('qpay1s'),true);
        $wuid=$input_data['wuid'];
        $datauser=$wu->getWebUserdetailFull($wuid);
        $Settings=$this->getSettings('M',$datauser['merchant_id']);
        return view('custom.confirmation',['data'=>$cpdata,'settings'=>$Settings, 'cpdata'=>$cpsettings , 'pageTitle' => 'Review and Approve','user'=>$datauser]);

    }
    
    function autopayccpagea($subdomain,Request $request,$locale='en'){
        \App::setLocale($locale);
        $cp=new CustomPages();
        $cpdata=$cp->getPageData($subdomain);
        if(empty($cpdata)){
            return view('custom.donotexists',['pageTitle' => 'Error!']);
        }
        if($cpdata['status']==0){
            return view ('custom.inactive',['pageTitle' => 'Inactive']);
        }
        //exists and active
        $level=$cpdata['level'];
        $idlevel=$cpdata['idlevel'];
        $cpsettings=json_decode($cpdata['custom_settings'],true);
        $uivr=new \App\Models\Ivr();
        $wu = new \App\Models\WebUsers();
        if(!session()->has('qpay1s')){
           return redirect()->route('loadpage',['subdomain'=>$subdomain])->withErrors(['Session expired - Please try again']); 
        }
        if(!session()->has('payment')){
           return redirect()->route('loadpage',['subdomain'=>$subdomain])->withErrors(['Session expired - Please try again']); 
        }
        
        $input_data=json_decode(session()->get('qpay1s'),true);
        $wuid=$input_data['wuid'];
        $datauser=$wu->getWebUserdetailFull($wuid);
        $Settings=$this->getSettings('M',$datauser['merchant_id']);
        return view('custom.autoconfirmation',['data'=>$cpdata,'settings'=>$Settings, 'cpdata'=>$cpsettings , 'pageTitle' => 'Review and Approve','user'=>$datauser]);

    }
    
    function qpayccpage($subdomain,Request $request,$locale='en'){
        \App::setLocale($locale);
        $cp=new CustomPages();
        $cpdata=$cp->getPageData($subdomain);
        if(empty($cpdata)){
            return view('custom.donotexists',['pageTitle' => 'Error!']);
        }
        if($cpdata['status']==0){
            return view ('custom.inactive',['pageTitle' => 'Inactive']);
        }
        //exists and active
        $level=$cpdata['level'];
        $idlevel=$cpdata['idlevel'];
        $cpsettings=json_decode($cpdata['custom_settings'],true);

        $input_data=$request->all();
        if(isset($input_data['_class'])){
            list($wuid,$time)= explode('|',\Illuminate\Support\Facades\Crypt::decrypt($input_data['_class']));
/*
            if(($time+3600)<time()){
                return redirect()->route('loadpage',['subdomain'=>$subdomain])->withErrors(['Invalid Data - Please review your information and try again']);
            }
 * 
 */
            $input_data['wuid']=$wuid;
        }
        else {
            return redirect()->route('loadpage',['subdomain'=>$subdomain])->withErrors(['Invalid Data - Please review your information and try again']);
        }
        if(!session()->has('qpay1s')){
            return redirect()->route('loadpage',['subdomain'=>$subdomain])->withErrors(['Invalid Data - Please review your information and try again']);
        }
        
        //validate cc info
        $uivr=new \App\Models\Ivr();
        $wu = new \App\Models\WebUsers();
        
        unset($input_data['_token']);
        unset($input_data['_class']);
        $input_data['method']='cc';
        session()->put('payment',json_encode($input_data));
        session()->save();
        
        if(!isset($input_data['xcardnumber'])){
            return redirect()->back()->withErrors(['Invalid Payment Information - Please review your information and try again']);
        }
        if(!$uivr->isValidCardNumber($input_data['xcardnumber'])){
            return redirect()->back()->withErrors(['Invalid Payment Information - Please review your information and try again']);
        }
        if(isset($input_data['input_cfee_row']) && $input_data['input_cfee_row'] == '-'){
            return redirect()->back()->withErrors(['Invalid Payment Information - Please review your information and try again']);
        }
        //todo the others
        
        
        $datauser=$wu->getWebUserdetailFull($wuid);
        $Settings=$this->getSettings('M',$datauser['merchant_id']);
        return view('custom.confirmation',['data'=>$cpdata,'settings'=>$Settings, 'cpdata'=>$cpsettings , 'pageTitle' => 'Review and Approve','user'=>$datauser]);
    }
    
    function qpayecpage($subdomain,Request $request,$locale='en'){
        \App::setLocale($locale);
        $cp=new CustomPages();
        $cpdata=$cp->getPageData($subdomain);
        if(empty($cpdata)){
            return view('custom.donotexists',['pageTitle' => 'Error!']);
        }
        if($cpdata['status']==0){
            return view ('custom.inactive',['pageTitle' => 'Inactive']);
        }
        //exists and active
        $level=$cpdata['level'];
        $idlevel=$cpdata['idlevel'];
        $cpsettings=json_decode($cpdata['custom_settings'],true);

        $input_data=$request->all();
        if(isset($input_data['_class'])){
            list($wuid,$time)= explode('|',\Illuminate\Support\Facades\Crypt::decrypt($input_data['_class']));
            /*
            if(($time+3600)<time()){
                return redirect()->route('loadpage',['subdomain'=>$subdomain])->withErrors(['Invalid Data - Please review your information and try again']);
            }
             * 
             */
            $input_data['wuid']=$wuid;
        }
        else {
            return redirect()->route('loadpage',['subdomain'=>$subdomain])->withErrors(['Invalid Data - Please review your information and try again']);
        }
        if(!session()->has('qpay1s')){
            return redirect()->route('loadpage',['subdomain'=>$subdomain])->withErrors(['Invalid Data - Please review your information and try again']);
        }
        
        //validate ec info
        $uivr=new \App\Models\Ivr();
        $wu = new \App\Models\WebUsers();
        
        unset($input_data['_token']);
        unset($input_data['_class']);
        $input_data['method']='ec';
        $request->session()->put('payment',json_encode($input_data));
        $request->session()->save();
        
        if(!isset($input_data['xrouting']) || !isset($input_data['xbank'])){
            return redirect()->back()->withErrors(['Invalid Payment Information - Please review your information and try again']);
        }
        if(!$uivr->isValidRouting($input_data['xrouting'])){
            return redirect()->back()->withErrors(['Invalid Payment Information - Please review your information and try again']);
        }
        //todo the others
        
        
        $datauser=$wu->getWebUserdetailFull($wuid);
        $Settings=$this->getSettings('M',$datauser['merchant_id']);
        return view('custom.confirmation',['data'=>$cpdata,'settings'=>$Settings, 'cpdata'=>$cpsettings , 'pageTitle' => 'Review and Approve','user'=>$datauser]);
    }

    function autopayccpage($subdomain,Request $request,$locale='en'){
        \App::setLocale($locale);
        $cp=new CustomPages();
        $cpdata=$cp->getPageData($subdomain);
        if(empty($cpdata)){
            return view('custom.donotexists',['pageTitle' => 'Error!']);
        }
        if($cpdata['status']==0){
            return view ('custom.inactive',['pageTitle' => 'Inactive']);
        }
        //exists and active
        $level=$cpdata['level'];
        $idlevel=$cpdata['idlevel'];
        $cpsettings=json_decode($cpdata['custom_settings'],true);

        $input_data=$request->all();
        if(isset($input_data['_class'])){
            list($wuid,$time)= explode('|',\Illuminate\Support\Facades\Crypt::decrypt($input_data['_class']));
/*
            if(($time+3600)<time()){
                return redirect()->route('loadpage',['subdomain'=>$subdomain])->withErrors(['Invalid Data - Please review your information and try again']);
            }
 * 
 */
            $input_data['wuid']=$wuid;
        }
        else {
            return redirect()->route('loadpage',['subdomain'=>$subdomain])->withErrors(['Invalid Data - Please review your information and try again']);
        }
        if(!session()->has('qpay1s')){
            return redirect()->route('loadpage',['subdomain'=>$subdomain])->withErrors(['Invalid Data - Please review your information and try again']);
        }
        
        //validate cc info
        $uivr=new \App\Models\Ivr();
        $wu = new \App\Models\WebUsers();
        
        unset($input_data['_token']);
        unset($input_data['_class']);
        $input_data['method']='cc';
        session()->put('payment',json_encode($input_data));
        session()->save();
        
        if(!isset($input_data['xcardnumber'])){
            return redirect()->back()->withErrors(['Invalid Payment Information - Please review your information and try again']);
        }
        if(!$uivr->isValidCardNumber($input_data['xcardnumber'])){
            return redirect()->back()->withErrors(['Invalid Payment Information - Please review your information and try again']);
        }
        //todo the others
        
        
        $datauser=$wu->getWebUserdetailFull($wuid);
        $Settings=$this->getSettings('M',$datauser['merchant_id']);
        return view('custom.autoconfirmation',['data'=>$cpdata,'settings'=>$Settings, 'cpdata'=>$cpsettings , 'pageTitle' => 'Review and Approve','user'=>$datauser]);
    }
    
    function autopayecpage($subdomain,Request $request,$locale='en'){
        \App::setLocale($locale);
        $cp=new CustomPages();
        $cpdata=$cp->getPageData($subdomain);
        if(empty($cpdata)){
            return view('custom.donotexists',['pageTitle' => 'Error!']);
        }
        if($cpdata['status']==0){
            return view ('custom.inactive',['pageTitle' => 'Inactive']);
        }
        //exists and active
        $level=$cpdata['level'];
        $idlevel=$cpdata['idlevel'];
        $cpsettings=json_decode($cpdata['custom_settings'],true);

        $input_data=$request->all();
        if(isset($input_data['_class'])){
            list($wuid,$time)= explode('|',\Illuminate\Support\Facades\Crypt::decrypt($input_data['_class']));
            /*
            if(($time+3600)<time()){
                return redirect()->route('loadpage',['subdomain'=>$subdomain])->withErrors(['Invalid Data - Please review your information and try again']);
            }
             * 
             */
            $input_data['wuid']=$wuid;
        }
        else {
            return redirect()->route('loadpage',['subdomain'=>$subdomain])->withErrors(['Invalid Data - Please review your information and try again']);
        }
        if(!session()->has('qpay1s')){
            return redirect()->route('loadpage',['subdomain'=>$subdomain])->withErrors(['Invalid Data - Please review your information and try again']);
        }
        
        //validate ec info
        $uivr=new \App\Models\Ivr();
        $wu = new \App\Models\WebUsers();
        
        unset($input_data['_token']);
        unset($input_data['_class']);
        $input_data['method']='ec';
        $request->session()->put('payment',json_encode($input_data));
        $request->session()->save();
        
        if(!isset($input_data['xrouting']) || !isset($input_data['xbank'])){
            return redirect()->back()->withErrors(['Invalid Payment Information - Please review your information and try again']);
        }
        if(!$uivr->isValidRouting($input_data['xrouting'])){
            return redirect()->back()->withErrors(['Invalid Payment Information - Please review your information and try again']);
        }
        //todo the others
        
        
        $datauser=$wu->getWebUserdetailFull($wuid);
        $Settings=$this->getSettings('M',$datauser['merchant_id']);
        return view('custom.autoconfirmation',['data'=>$cpdata,'settings'=>$Settings, 'cpdata'=>$cpsettings , 'pageTitle' => 'Review and Approve','user'=>$datauser]);
    }

    function autopayexecutepage($subdomain,Request $request,$locale='en'){
        \App::setLocale($locale);
        $cp=new CustomPages();
        $cpdata=$cp->getPageData($subdomain);
        if(empty($cpdata)){
            return view('custom.donotexists',['pageTitle' => 'Error!']);
        }
        if($cpdata['status']==0){
            return view ('custom.inactive',['pageTitle' => 'Inactive']);
        }
        //exists and active
        $level=$cpdata['level'];
        $idlevel=$cpdata['idlevel'];
        $cpsettings=json_decode($cpdata['custom_settings'],true);
        if(!session()->has('qpay1s')){
           return redirect()->route('loadpage',['subdomain'=>$subdomain])->withErrors(['Invalid Data - Please review your information and try again']); 
        }
        if(!session()->has('payment')){
           return redirect()->route('loadpage',['subdomain'=>$subdomain])->withErrors(['Invalid Data - Please review your information and try again']); 
        }
        //extract data to submit payment
        
        $wu = new \App\Models\WebUsers();
        $mkpay = new MakePaymentController();
        $data_step1=json_decode(session()->get('qpay1s'),true);
        $data_step2=json_decode(session()->get('payment'),true);
        
        $web_user_id=$data_step1['wuid'];
        $typer=$data_step2['method'];
        $property_id=$wu->get1UserInfo($web_user_id, 'property_id');
        $onlycustompay=false;
        if(isset($cpsettings['onlycustompay']) && $cpsettings['onlycustompay']==1){
            $onlycustompay=true;
        }
        $credentials=$cp->getCredentials4CustomType($property_id, $data_step1['total_amount'], $typer,1,$onlycustompay,$data_step1['xtype']);
        $drp=$data_step1['xtype'];
        if($drp=='drp'){
            $xcfee=0;
            $drp=1;
        }
        else {
            $xcfee=$credentials['calculated_fee'];
            $drp=0;
            // if CFEE per card were used then ovewrites the normal CFEE
            if (isset($data_step2['input_cfee_row']) && is_numeric($data_step2['input_cfee_row'])) {
                $xcfee = $data_step2['input_cfee_row'];
            }
        }
        $datestart=date('Y-m-d 00:00:00',  strtotime($data_step1['xmonth'].'-'.$data_step1['xday']));
        $dateend=date('Y-m-d 00:00:00',  strtotime($data_step1['xend'].'-'.$data_step1['xday']));
        //from MakePayment
        //auto
        $record=array();
        $record['echeck_token']='';
        $record['data']='';
        $record['echeck_driver_license']='';
        $record['property_id']=$property_id;
        $record['trans_web_user_id']=$web_user_id;
        $record['last_updated_by'] = 'system';
        $record['trans_status']=1;
        $record['trans_first_post_date']=date('Y-m-d H:i:s');
        $record['trans_last_post_date']=$record['trans_first_post_date'];
        $record['trans_schedule']=$data_step1['xfreq'];
        if($drp==1){
            //drp
            $record['dynamic']=1;
            $record['trans_recurring_net_amount']=0;
            $record['trans_recurring_convenience_fee']=0;
            $record['trans_descr']='Dynamic AutoPayment';
            $categories=array();

        }
        else {
            //fix
            $categories=$data_step1['categories'];
            $record['dynamic']=0;
            $record['trans_recurring_net_amount']=$data_step1['total_amount'];

        }
        $record['trans_next_post_date']=$datestart;
        if($data_step1['xend']==-1){
            $record['trans_numleft']=9999;
        }
        else {
            //calculate cycles
            $record['trans_numleft']=$this->calculateCycle($data_step1['xfreq'], $datestart,$dateend);
        }
         if($typer=='ec'){
            //echeck
            $record['trans_payment_type']='ec';
            $record['trans_card_type']=$data_step2['xbanktype'].'('.substr(trim($data_step2['xbank']),-4).')';
            $record['echeck_account_holder']=trim($data_step2['xecname']);
            $record['echeck_routing_number']=trim($data_step2['xrouting']);
            $record['echeck_account_number']=trim($data_step2['xbank']);
            $record['echeck_account_type']=$data_step2['xbanktype'];
            $record['trans_source_key']=$this->extractCredID([$credentials],$record['trans_payment_type']);
            //create profile for ec
            $ec_name = "XXXX- " . substr(trim($data_step2['xbank']), -4);
            $ecprofile_info = array(
                'ec_account_holder' => $record['echeck_account_holder'],
                'ec_routing_number' => $record['echeck_routing_number'],
                'ec_account_number' => $record['echeck_account_number'],
                'ec_checking_savings' => $data_step2['xbanktype']
            );
            $ecjson = json_encode($ecprofile_info);
            $pid = \Illuminate\Support\Facades\DB::table('profiles')->insertGetId(['data' => '',
                                                       'wallet' => '',
                                                       'id_partner' => 0,
                                                       'id_company' => 0, 
                                                       'id_property' => $property_id, 
                                                       'web_user_id' => $web_user_id, 
                                                       'token' => Crypt::encrypt($ecjson), 
                                                       'name' => $ec_name, 
                                                       'type' => $record['trans_payment_type']]);
            RevoPayAuditLogger::paymentMethodCreate('user', array('operation' => 'Create payment method',
                'type'=> 'ec', 'name'=> $ec_name), 'M', $property_id, WebUsers::getAuditData($web_user_id), null);
            $record['profile_id']=$pid;
            $record['trans_gw_custnum']=$pid; 
         }
         else {
             //cc
             if(substr($mkpay->getCardType($data_step2['xcardnumber']),0,1)=='A'){
                $record['trans_payment_type']='amex';
            }
            else {
                $record['trans_payment_type']='cc';
            }
            $record['trans_card_type']=$mkpay->getCardType($data_step2['xcardnumber']).'('.substr($data_step2['xcardnumber'],-4).')';
            $record['echeck_account_holder']=$data_step2['xcardname'];
            $record['trans_source_key']=$this->extractCredID([$credentials],$record['trans_payment_type']);
            //get token and create profile
            $paymentInfo=array();
            $paymentInfo['cardname']=$data_step2['xcardname'];
            $paymentInfo['cardnumber']=$data_step2['xcardnumber'];
            $paymentInfo['exp_date']=$data_step2['xexpdate'];
            $paymentInfo['zip']=$data_step2['xzip'];
            $paymentInfo['cc_type']=$mkpay->getCardTypeShort($data_step2['xcardnumber']);
            $credential=$this->extractCredByID([$credentials], $record['trans_source_key']);
            $obj_payment = new \App\CustomClass\PaymentProcessor();
            $credentialArray=$credential;
            $result=$obj_payment->getToken($paymentInfo, $credentialArray);
            if (empty($result) || $result['response'] != 1) {
                return redirect()->route('showautono',['subdomain'=>$subdomain,'locale'=>$locale,'msg'=> base64_encode('Rejected Autopayment. Error creating Payment profile')]);
            }
            $cc_name = "XXXX- " . substr($data_step2['xcardnumber'], -4);
            $ccprofile_info = array(
                'vid' => $result['token'],
                'exp_date' => $paymentInfo['exp_date'],
                'cc_type' => $paymentInfo['cc_type'],
                'ch_name' => $paymentInfo['cardname']
            );
            $ccjson = json_encode($ccprofile_info);
            $pid = \Illuminate\Support\Facades\DB::table('profiles')->insertGetId(['data' => '',
                                                       'wallet' => '',
                                                       'id_partner' => 0,
                                                       'id_company' => 0, 
                                                       'id_property' => $property_id, 
                                                       'web_user_id' => $web_user_id, 
                                                       'token' => Crypt::encrypt($ccjson), 
                                                       'name' => $cc_name, 
                                                       'type' => $record['trans_payment_type']]);
            RevoPayAuditLogger::paymentMethodCreate('user', array('operation' => 'Create payment method',
                'type'=> $paymentInfo['cc_type'], 'name'=> $cc_name), 'M', $property_id, WebUsers::getAuditData($web_user_id), null);
            $record['profile_id']=$pid;
            $record['trans_gw_custnum']=$pid;
         }
         $record['trans_recurring_convenience_fee']=$xcfee;
         $record['trans_descr']=$mkpay->getPayment_descr($categories, $xcfee);
            
        $obj_transaction = new \App\Models\Transations();
        $trans_id=$obj_transaction->addAutoTransaction($record);

        if(isset($categories)){
            $obj_transaction->addCatforAutoTransaction($trans_id, $categories, $property_id, 0, 0, $web_user_id);
        }
        RevoPayAuditLogger::autopaymentCreate('user', array('operation' => 'Create Autopayment', 'Type'=> $typer,'trans_rec_id'=> $trans_id ), 'M', $property_id, WebUsers::getAuditData($web_user_id), null);
        $result=array('auto'=>1,'response'=>1,'txid'=>$trans_id,'startdate'=>$datestart);
        $settings=$this->getSettings('M',$property_id);
        $obj_email= new \App\CustomClass\Email();
        $obj_email->ScheduleReceiptCP($result,$settings,$property_id,$web_user_id);
        return redirect()->route('showautotx',['txid'=>$trans_id,'subdomain'=>$subdomain,'locale'=>$locale]);
    }
    
    function showautono($subdomain,$msg,Request $request, $locale='en'){
        \App::setLocale($locale);
        $cp=new CustomPages();
        $cpdata=$cp->getPageData($subdomain);
        if(empty($cpdata)){
            return view('custom.donotexists',['pageTitle' => 'Error!']);
        }
        if($cpdata['status']==0){
            return view ('custom.inactive',['pageTitle' => 'Inactive']);
        }
        //exists and active
        $level=$cpdata['level'];
        $idlevel=$cpdata['idlevel'];
        $cpsettings=json_decode($cpdata['custom_settings'],true);
        if(!session()->has('qpay1s')){
           return redirect()->route('loadpage',['subdomain'=>$subdomain])->withErrors(['Invalid Data - Please review your information and try again']); 
        }
        if(!session()->has('payment')){
           return redirect()->route('loadpage',['subdomain'=>$subdomain])->withErrors(['Invalid Data - Please review your information and try again']); 
        }
        $wu = new \App\Models\WebUsers();
        $data_step1=json_decode(session()->get('qpay1s'),true);
        $web_user_id=$data_step1['wuid'];
        $datauser=$wu->getWebUserdetailFull($web_user_id);     
        $Settings=$this->getSettings('M',$datauser['merchant_id']);
        return view('custom.autopaydeclined',['data'=>$cpdata,'settings'=>$Settings, 'cpdata'=>$cpsettings , 'pageTitle' => 'AutoPayment Declined','user'=>$datauser,'msg'=> base64_decode($msg)]); 
    }
    
    function showautotx($subdomain,$txid,Request $request, $locale='en'){
        \App::setLocale($locale);
        $cp=new CustomPages();
        $cpdata=$cp->getPageData($subdomain);
        if(empty($cpdata)){
            return view('custom.donotexists',['pageTitle' => 'Error!']);
        }
        if($cpdata['status']==0){
            return view ('custom.inactive',['pageTitle' => 'Inactive']);
        }
        //exists and active
        $level=$cpdata['level'];
        $idlevel=$cpdata['idlevel'];
        $cpsettings=json_decode($cpdata['custom_settings'],true);
        session()->flush();
        $transaction= json_decode(json_encode(\Illuminate\Support\Facades\DB::table('accounting_recurring_transactions')->where('trans_id',$txid)->first()),true);
        $wu = new \App\Models\WebUsers();
        $datauser=$wu->getWebUserdetailFull($transaction['trans_web_user_id']);
        $Settings=$this->getSettings('M',$datauser['merchant_id']);
        return view('custom.autopayapproved',['transaction'=>$transaction, 'data'=>$cpdata,'settings'=>$Settings, 'cpdata'=>$cpsettings , 'pageTitle' => 'Payment Approved!','user'=>$datauser]);                
    }
    
    function showapprove($subdomain,$txid,Request $request, $locale='en'){
        \App::setLocale($locale);
        $cp=new CustomPages();
        $cpdata=$cp->getPageData($subdomain);
        if(empty($cpdata)){
            return view('custom.donotexists',['pageTitle' => 'Error!']);
        }
        if($cpdata['status']==0){
            return view ('custom.inactive',['pageTitle' => 'Inactive']);
        }
        //exists and active
        $level=$cpdata['level'];
        $idlevel=$cpdata['idlevel'];
        $cpsettings=json_decode($cpdata['custom_settings'],true);
        $transaction= json_decode(json_encode(\Illuminate\Support\Facades\DB::table('accounting_transactions')->where('trans_id',$txid)->first()),true);
        $wu = new \App\Models\WebUsers();
        $datauser=$wu->getWebUserdetailFull($transaction['trans_web_user_id']);
        $Settings=$this->getSettings('M',$datauser['merchant_id']);
        return view('custom.approved',['transaction'=>$transaction, 'data'=>$cpdata,'settings'=>$Settings, 'cpdata'=>$cpsettings , 'pageTitle' => 'Payment Approved!','user'=>$datauser]);                
    }
    
    function showdeclined($subdomain,$txid,Request $request, $locale='en'){
        \App::setLocale($locale);
        $cp=new CustomPages();
        $cpdata=$cp->getPageData($subdomain);
        if(empty($cpdata)){
            return view('custom.donotexists',['pageTitle' => 'Error!']);
        }
        if($cpdata['status']==0){
            return view ('custom.inactive',['pageTitle' => 'Inactive']);
        }
        //exists and active
        $level=$cpdata['level'];
        $idlevel=$cpdata['idlevel'];
        $cpsettings=json_decode($cpdata['custom_settings'],true);
        $transaction= json_decode(json_encode(\Illuminate\Support\Facades\DB::table('accounting_transactions')->where('trans_id',$txid)->first()),true);
        $wu = new \App\Models\WebUsers();
        $datauser=$wu->getWebUserdetailFull($transaction['trans_web_user_id']);
        $Settings=$this->getSettings('M',$datauser['merchant_id']);
        return view('custom.declined',['transaction'=>$transaction,'data'=>$cpdata,'settings'=>$Settings, 'cpdata'=>$cpsettings , 'pageTitle' => 'Payment Declined','user'=>$datauser]);
    }
    
    function qpayexecutepage($subdomain,Request $request,$locale='en'){
        \App::setLocale($locale);
        $cp=new CustomPages();
        $cpdata=$cp->getPageData($subdomain);
        if(empty($cpdata)){
            return view('custom.donotexists',['pageTitle' => 'Error!']);
        }
        if($cpdata['status']==0){
            return view ('custom.inactive',['pageTitle' => 'Inactive']);
        }
        //exists and active
        $level=$cpdata['level'];
        $idlevel=$cpdata['idlevel'];
        $cpsettings=json_decode($cpdata['custom_settings'],true);
        if(!session()->has('qpay1s')){
           return redirect()->route('loadpage',['subdomain'=>$subdomain])->withErrors(['Invalid Data - Please review your information and try again']); 
        }
        if(!session()->has('payment')){
           return redirect()->route('loadpage',['subdomain'=>$subdomain])->withErrors(['Invalid Data - Please review your information and try again']); 
        }
        //extract data to submit payment
        
        $wu = new \App\Models\WebUsers();
        $mkpay = new MakePaymentController();
        $data_step1=json_decode(session()->get('qpay1s'),true);
        $data_step2=json_decode(session()->get('payment'),true);
        
        if(empty($data_step1) || empty($data_step2)){
            return redirect()->route('loadpage',['subdomain'=>$subdomain])->withErrors(['Session expired please try again']);
        }
        
        $web_user_id=$data_step1['wuid'];
        $typer=$data_step2['method'];
        $onlycustompay=false;
        if(isset($cpsettings['onlycustompay']) && $cpsettings['onlycustompay']==1){
            $onlycustompay=true;
        }
        $property_id=$wu->get1UserInfo($web_user_id, 'property_id');
        $settings= $this->getSettings('M', $property_id);
        $datauser=$wu->getWebUserdetailFull($web_user_id);
        $credentials=$cp->getCredentials4CustomType($property_id, $data_step1['total_amount'], $typer, 0, $onlycustompay);
        $xcfee=$credentials['calculated_fee'];
        if (isset($data_step2['input_cfee_row']) && is_numeric($data_step2['input_cfee_row'])) {
            $xcfee = $data_step2['input_cfee_row'];
        }
        //from Makepayment
        //prepare record for table & paymentinfo for processor
        $paymentInfo=array();
        $paymentInfo['net_amount']=$data_step1['total_amount'];
        $paymentInfo['memo']=trim($data_step1['memo']);

        $record['property_id']=$property_id;
        $record['trans_web_user_id']=$web_user_id;
        $record['trans_net_amount']=$data_step1['total_amount'];
        $record['trans_status']=0;
        $record['trans_first_post_date']=date('Y-m-d H:i:s');
        $record['trans_last_post_date']=$record['trans_first_post_date'];
        $record['trans_final_post_date']=$record['trans_first_post_date'];
        $record['source']='CP';
        $record['nacha_type']='WEB';
        $record['trans_account_number']=trim($datauser['account_number']);
        $record['trans_user_name']=trim($datauser['first_name'].' '.$datauser['last_name']);
        $record['trans_type']=0;
        $record['last_updated_by'] = 'system';
        $record['data']='';
        $record['trans_profile_id']=0;
        $record['invoice_number']='';
        $record['orderid']='';
        if ($typer == 'ec') {
           //ec
            $record['trans_payment_type']='ec';
            $record['trans_card_type']=$data_step2['xbanktype'].'('.substr(trim($data_step2['xbank']),-4).')';
            if($record['trans_user_name']==''){
                $record['trans_user_name']=trim($data_step2['xecname']);
            }
            $paymentInfo['ec_account_holder']=trim($data_step2['xecname']);
            $paymentInfo['ec_routing_number']=trim($data_step2['xrouting']);
            $paymentInfo['ec_account_number']=trim($data_step2['xbank']);
            $paymentInfo['ec_checking_savings']=$data_step2['xbanktype']; 
        }
        else {
            //cc
            if(substr($mkpay->getCardType($data_step2['xcardnumber']),0,1)=='A'){
                $record['trans_payment_type']='amex';
                $credentials=$cp->getCredentials4CustomType($property_id, $data_step1['total_amount'], 'amex', 0, $onlycustompay);
                $xcfee=$credentials['calculated_fee'];
                
            }
            else {
                $record['trans_payment_type']='cc';
            }
            $record['trans_card_type']=$mkpay->getCardType($data_step2['xcardnumber']).'('.substr($data_step2['xcardnumber'],-4).')';
            if($record['trans_user_name']==''){
                $record['trans_user_name']=$data_step2['xcardname'];
            }
            $paymentInfo['cardname']=$data_step2['xcardname'];
            $paymentInfo['cardnumber']=$data_step2['xcardnumber'];
            $paymentInfo['exp_date']=$data_step2['xexpdate'];
            $paymentInfo['zip']=$data_step2['xzip'];
            $paymentInfo['cc_type']=$mkpay->getCardTypeShort($data_step2['xcardnumber']);
            //Fraud Control
            $obj_fraud = new \App\CustomClass\FraudControl($property_id, $web_user_id, $data_step2['xcardnumber'], $record['trans_net_amount']);
            if ($obj_fraud->isFraud()) {
                return redirect()->back()->withErrors(['Possible Fraud Detected! Payment blocked. If you think this is a mistake, please call our customer service at (305) 252-8297 option 2']);
            }
            //verify BIN
            //BIN settings
            $obj_bin = new \App\Models\Bin();
            if(isset($settings['BINCARD'])){
                $bin = $settings['BINCARD'];
                if (!empty($bin) && $bin!='') {
                    $msg = $obj_bin->ValidCCard($data_step2['xcardnumber'], $bin);
                    if (!$msg) {
                        return redirect()->back()->withErrors(['This card type is not currently accepted']);
                    }
                }
            }
        }
        $categories=$data_step1['categories'];
        $record['trans_convenience_fee']=number_format($xcfee,2,'.','');
        $record['trans_total_amount']=number_format(($record['trans_net_amount']+$record['trans_convenience_fee']),2,'.','');
        $record['trans_descr']=$mkpay->getPayment_descr($categories, $record['trans_convenience_fee'],$paymentInfo['memo']);
        $record['trans_source_key']=$this->extractCredID([$credentials],$record['trans_payment_type']);
        //record ready for accounting_transactions
        $paymentInfo['fee']=$record['trans_convenience_fee'];
        //get credential
        $credential=$this->extractCredByID([$credentials], $record['trans_source_key']);
        $result=$mkpay->makePayment($record,$paymentInfo,$credential,$categories,true);
        if($result['response']==1){
            //approve
            $obj_cat = new \App\Models\Categories();
            $obj_cat->updateBalance($categories,$web_user_id);
            
            // adds the event log
            $auxUser = $wu->getMinimalDataFindId($property_id, $web_user_id);
            $auxUsername = !empty($auxUser->username) ? $auxUser->username : null;
            $auxTxId = !empty($result['txid']) ? $result['txid'] : 0;
            RevoPayAuditLogger::paymentSuccess('user', array('operation' => 'Payment success', 'data'=>$result ), 'M', $property_id, WebUsers::getAuditData($web_user_id), $auxUser, $auxUsername, Audit::$DATA_INSERT, $auxTxId);
            
            // send the payment receipt
            $email_obj=new \App\CustomClass\Email();
            $email_obj->PaymentReceiptCP($result,$settings,$web_user_id,$record['property_id']);
            return redirect()->route('showapprove',['subdomain'=>$subdomain,'txid'=>$result['txid'],'locale'=>$locale]);
        }
        else {
            // adds the event log
            $auxUser = $wu->getMinimalDataFindId($property_id, $web_user_id);
            $auxUsername = !empty($auxUser->username) ? $auxUser->username : null;
            $auxTxId = !empty($result['txid']) ? $result['txid'] : 0;
            RevoPayAuditLogger::paymentFailure('user', array('operation' => 'Payment failure', 'data' => $result), 'M', $property_id, WebUsers::getAuditData($web_user_id), $auxUser, $auxTxId);
            //declined
            return redirect()->route('showdeclined',['subdomain'=>$subdomain,'txid'=>$result['txid'],'locale'=>$locale]);
        }
    }
    
     /**
    * @param $interval
    * @param $datefrom
    * @param $dateto
    * @param bool $using_timestamps
    * @return false|float|int|string
    */
    function datediff($interval, $datefrom, $dateto, $using_timestamps = false)
    {
        /*
        $interval can be:
        yyyy - Number of full years
        q    - Number of full quarters
        m    - Number of full months
        y    - Difference between day numbers
               (eg 1st Jan 2004 is "1", the first day. 2nd Feb 2003 is "33". The datediff is "-32".)
        d    - Number of full days
        w    - Number of full weekdays
        ww   - Number of full weeks
        h    - Number of full hours
        n    - Number of full minutes
        s    - Number of full seconds (default)
        */

        if (!$using_timestamps) {
            $datefrom = strtotime($datefrom, 0);
            $dateto   = strtotime($dateto, 0);
        }

        $difference        = $dateto - $datefrom; // Difference in seconds
        $months_difference = 0;

        switch ($interval) {
            case 'yyyy': // Number of full years
                $years_difference = floor($difference / 31536000);
                if (mktime(date("H", $datefrom), date("i", $datefrom), date("s", $datefrom), date("n", $datefrom), date("j", $datefrom), date("Y", $datefrom)+$years_difference) > $dateto) {
                    $years_difference--;
                }

                if (mktime(date("H", $dateto), date("i", $dateto), date("s", $dateto), date("n", $dateto), date("j", $dateto), date("Y", $dateto)-($years_difference+1)) > $datefrom) {
                    $years_difference++;
                }

                $datediff = $years_difference+1;
            break;

            case "q": // Number of full quarters
                $quarters_difference = floor($difference / 7889250);
                //$quarters_difference--;
                $datediff = $quarters_difference+1;
            break;

            case "m": // Number of full months
                $months_difference = floor($difference / 2629750);

                while (mktime(date("H", $datefrom), date("i", $datefrom), date("s", $datefrom), date("n", $datefrom)+($months_difference), date("j", $dateto), date("Y", $datefrom)) < $dateto) {
                    $months_difference++;
                }

                $months_difference++;

                $datediff = $months_difference;
            break;

            case 'y': // Difference between day numbers
                $datediff = date("z", $dateto) - date("z", $datefrom);
            break;

            case "d": // Number of full days
                $datediff = floor($difference / 86400)+1;
            break;

            case "w": // Number of full weekdays
                $days_difference  = floor($difference / 86400)+1;
                $weeks_difference = floor($days_difference / 7); // Complete weeks
                $first_day        = date("w", $datefrom);
                $days_remainder   = floor($days_difference % 7);
                $odd_days         = $first_day + $days_remainder; // Do we have a Saturday or Sunday in the remainder?

                if ($odd_days > 7) { // Sunday
                    $days_remainder--;
                }

                if ($odd_days > 6) { // Saturday
                    $days_remainder--;
                }

                $datediff = ($weeks_difference * 5) + $days_remainder;
            break;

            case "ww": // Number of full weeks
                $days_difference  = floor($difference / 86400)+1;
                $weeks_difference = floor($days_difference / 7); // Complete weeks
                $datediff = $weeks_difference;
            break;

            case "h": // Number of full hours
                $datediff = floor($difference / 3600);
            break;

            case "n": // Number of full minutes
                $datediff = floor($difference / 60);
            break;

            default: // Number of full seconds (default)
                $datediff = $difference;
            break;
        }

        return $datediff;
    }
    
    public function calculateCycle($schedule, $datestart,$dateend) {
        $cycles=0;
        switch ($schedule) {
            case 'onetime':
                $cycles=1;
                break;
            case 'weekly':
                $cycles=$this->datediff('ww', $datestart, $dateend);
                $cycles++;
                break;
            case 'annually':
            case 'yearly':
                $cycles=$this->datediff('yyyy', $datestart, $dateend);
                if($cycles==0)$cycles++;
                break;
            case 'biannually':
                $cycles=$this->datediff('m', $datestart, $dateend);
                $cycles=($cycles/6);
                if($cycles-floor($cycles) >= 0.5){
                    $cycles=ceil($cycles);
                }else{
                    $cycles=floor($cycles);
                }
                if($cycles==0)$cycles++;
                break;
            case 'quaterly':
            case 'quarterly':
                $cycles=$this->datediff('q', $datestart, $dateend);
                if($cycles==0)$cycles++;
                break;
            case 'triannually':
                $cycles=$this->datediff('m', $datestart, $dateend);
                $cycles=($cycles/4);
                if($cycles-floor($cycles) >= 0.5){
                    $cycles=ceil($cycles);
                }else{
                    $cycles=floor($cycles);
                }
                if($cycles==0)$cycles++;
                break;
            case 'biweekly':
                $cycles=$this->datediff('ww', $datestart, $dateend);
                $cycles=floor($cycles/2);
                if($cycles-floor($cycles) >= 0.5){
                    $cycles=ceil($cycles);
                }else{
                    $cycles=floor($cycles);
                }
                if($cycles==0)$cycles++;
                break;
            case 'monthly':
            default :
                $cycles=$this->datediff('m', $datestart, $dateend);
                if($cycles==0)$cycles++;
                break;
        }
        return $cycles;
    }
    
    
    function getSettings($level,$idlevel){
        $Settings=array();
        $objSettingVal = new \App\Models\Properties();
        if($level=='P'){
            $Settings=$objSettingVal->getSettingsValues(0,0,$idlevel);
        } 
        elseif ($level=='G'){
            $ddata= \Illuminate\Support\Facades\DB::table('companies')->where('id',$idlevel)->select('id_partners')->first();
            $idpartner=$ddata->id_partners;
            $Settings=$objSettingVal->getSettingsValues(0,$idlevel,$idpartner);
        }
        elseif ($level=='M'){
            $ddata= \Illuminate\Support\Facades\DB::table('properties')->where('id',$idlevel)->select('id_companies','id_partners')->first();
            $idpartner=$ddata->id_partners;
            $idcompany=$ddata->id_companies;
            $Settings=$objSettingVal->getSettingsValues($idlevel,$idcompany,$idpartner);
        }
        if(!isset($Settings['ACCSETTING'])){
            if(isset($Settings['NONEWUSER']) && $Settings['NONEWUSER']==1){
                $Settings['ACCSETTING']=3;
            }
            else {
                $Settings['ACCSETTING']=2;
            }
            if(isset($Settings['NOTACCQP']) && $Settings['NOTACCQP']==1){
                $Settings['ACCSETTING']=4;
            }
        }
        if(!isset($Settings['INVSETTING'])){
            if(isset($Settings['NOTINVREQ']) && $Settings['NOTINVREQ']==1){
                $Settings['INVSETTING']=4;
            }
            else {
                $Settings['INVSETTING']=2;
            }
        }

        if(isset($Settings["NOTCAPTCHA2"])){
            $Settings["NOTCAPTCHA2"] = $Settings["NOTCAPTCHA2"];
        }
        if(isset($Settings["MAXRECURRINGPAYMENTPERUSER"])){
            if($Settings["MAXRECURRINGPAYMENTPERUSER"]<=0){
                $Settings['MAXRECURRINGPAYMENTPERUSER']=1000;
            }
        }
        else {
            $Settings['MAXRECURRINGPAYMENTPERUSER']=1000;
        }
        if(isset($Settings["NOTCAPTCHA4"])){
            $Settings["NOTCAPTCHA4"] = $Settings["NOTCAPTCHA4"];
        }
        return $Settings;
    }
    
    function extractCredID($credentials,$type){
        $cfee=0;
        foreach($credentials as $cred){
            if($cred['payment_method']==$type){
                return $cred['merchant_account_id'];
            }
        }
        if($type=='amex'){
            $cfee=$this->extractCredID($credentials, 'cc');
        }
        return $cfee;
    }
    
    function extractCredByID($credentials, $id){
       foreach($credentials as $cred){
            if($cred['merchant_account_id']==$id){
                return $cred;
            }
        } 
    }
    
    function getFeeTable($subdomain,Request $request,$locale='en'){
        \App::setLocale($locale);
        $cp=new CustomPages();
        $cpdata=$cp->getPageData($subdomain);
        if(empty($cpdata)){
            return view('custom.donotexists',['pageTitle' => 'Error!']);
        }
        if($cpdata['status']==0){
            return view ('custom.inactive',['pageTitle' => 'Inactive']);
        }
        //exists and active
        $level=$cpdata['level'];
        $idlevel=$cpdata['idlevel'];
        $cpsettings=json_decode($cpdata['custom_settings'],true);
        $input_data=json_decode(session()->get('qpay1s'),true);
        $wuid=$input_data['wuid'];
        $amount=$input_data['total_amount'];
        $array_cred = array();
        $xdata=$input_data['credentials'];
        if(!isset($input_data['xtype'])){
            //one time
            $type='one';
            $xtype='';
            foreach ($xdata as $data){
                if($data['payment_method']=='ec'){
                   $array_cred[] = [
                        'type'=>'ec',
                        'defaulttopay'=>$data['calculated_fee']
                    ]; 
                }
                elseif($data['payment_method']=='cc'){
                   $array_cred[] = [
                        'type'=>'cc',
                        'defaulttopay'=>$data['calculated_fee']
                    ]; 
                }
                elseif($data['payment_method']=='amex'){
                   $array_cred[] = [
                        'type'=>'amex',
                        'defaulttopay'=>$data['calculated_fee']
                    ]; 
                }
            }
        }
        else {
            //recurring
            $type='auto';
            $xtype=$input_data['xtype'];
            foreach ($xdata as $data){
                if($data['payment_method']=='ec'){
                   $array_cred[] = [
                        'type'=>'ec',
                        'defaulttopay'=>$data['calculated_fee'],
                        'defaulttopaydrp'=> $data['calculated_fee_drp']
                    ]; 
                }
                elseif($data['payment_method']=='cc'){
                   $array_cred[] = [
                        'type'=>'cc',
                        'defaulttopay'=>$data['calculated_fee'],
                        'defaulttopaydrp'=> $data['calculated_fee_drp']
                    ]; 
                }
                elseif($data['payment_method']=='amex'){
                   $array_cred[] = [
                        'type'=>'amex',
                        'defaulttopay'=>$data['calculated_fee'],
                        'defaulttopaydrp'=> $data['calculated_fee_drp']
                    ]; 
                }
            }
        }

        $data=array('cred'=>$array_cred,'type'=>$type,'auto_type'=>$xtype);
        $body = View::make('makepayment.tableCfee',$data)->render();

        return response()->json([
            'body' => $body,
            'code' => 1,
            'noalert'=>1
        ]);
    }

    
    
}
