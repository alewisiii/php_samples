<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Lang;

class UtilsController extends Controller
{
    public function selectStarDay($setting, $data, $day, Request $request ){

        $settings = session('settings');
        $dayselect = $day;
        
        switch ($setting){
            case 'drp':
                $day_rang = $settings['DRPDAYSAUTOPAY'];
                /*if(isset($input_data['xdaydrp'])){
                    $dayselect = $input_data['xdaydrp'];
                }*/
                break;
            case 'fix':
                $day_rang = $settings['DAYSAUTOPAY'];
                /*if(isset($input_data['xdayfix'])){
                    $dayselect = $input_data['xdayfix'];
                }*/
                break;
            default : $day_rang = '1|31';
            break;
        }

        $date = explode('-',$data);
        $year = intval($date[0]);
        $month = intval($date[1]);

        $rang = explode('|',$day_rang);
        $starday = $rang[0];
        $endday = $rang[1];

        $daysofmonth = cal_days_in_month(CAL_GREGORIAN,$month,$year);

        if($endday > $daysofmonth){
            $endday = $daysofmonth;
        }
        $body="";

        for ($i=$starday; $i<=$endday;$i++){
            if($i == $dayselect)
                $body = $body."<option value='".$i."' selected>".date('jS',  strtotime($data.'-'.$i)).' '.Lang::get('messages.ofTheMonth')."</option>";
            else
                $body = $body."<option value='".$i."'>".date('jS',  strtotime($data.'-'.$i)).' '.Lang::get('messages.ofTheMonth')."</option>";
        }

        return response()->json([
            'body' => $body,
            'noalert' => true,
            'code' => 1,
        ]);


    }

    public function selectStarMonth($setting,$data, Request $request){

        $dateT = new \DateTime();
        $dateT->modify('first day of this month');
        $dateT = $dateT->format('Y-m');
        if($data <= date('d')){
            $allowedM=array();
        }else{
            $allowedM=array(date('Y-m'));
        }
        for($i=1;$i<12;$i++){
            $datetime = new \DateTime();
            $datetime->setDate(date('Y'), date('m'), 1);
            $datetime->add(new \DateInterval('P'.$i.'M'));
            $allowedM[]=$datetime->format('Y-m');
        }

        $body="";
        foreach ($allowedM as $month){
            if($data <= date('d') && $month == date("Y-m",strtotime($dateT."+ 1 month"))){
                $body = $body."<option value='".$month."' selected>".Lang::get('messages.'.strtolower(date('F',  strtotime(date($month.'-01'))))). date(', Y',  strtotime(date($month.'-01')))."</option>";
            }else{
                $body = $body."<option value='".$month."'>".Lang::get('messages.'.strtolower(date('F',  strtotime(date($month.'-01'))))). date(', Y',  strtotime(date($month.'-01')))."</option>";
            }
        }

        return response()->json([
            'body' => $body,
            'noalert' => true,
            'code' => 1,
        ]);
    }
    
    public function selectStarDayEdit($setting, $data, $startdate, Request $request ){

        $settings = session('settings');
        $input_data = session('input_data1');
        switch ($setting){
            case 'drp':
                $day_rang = $settings['DRPDAYSAUTOPAY'];
                break;
            case 'fix':
                $day_rang = $settings['DAYSAUTOPAY'];
                break;
            default : $day_rang = '1|31';
            break;
        }
        $data = str_replace('|', '-', $data);
        $date = explode('-',$data);
        $year = intval($date[0]);
        $month = intval($date[1]);

        $rang = explode('|',$day_rang);
        $starday = $rang[0];
        $endday = $rang[1];

        $daysofmonth = cal_days_in_month(CAL_GREGORIAN,$month,$year);

        // REVO 1523: If the day is greater than what's in the month then make the day the end-of-month.
        if ($startdate > $daysofmonth )
            $startdate = $daysofmonth;

        if($endday > $daysofmonth){
            $endday = $daysofmonth;
        }
        $body="";

        for ($i=$starday; $i<=$endday;$i++){
            if($i == $startdate)
                $body = $body."<option value='".$i."' selected>".date('jS',  strtotime($data.'-'.$i)).' '.Lang::get('messages.ofTheMonth')."</option>";
            else
                $body = $body."<option value='".$i."'>".date('jS',  strtotime($data.'-'.$i)).' '.Lang::get('messages.ofTheMonth')."</option>";
        }

        return response()->json([
            'body' => $body,
            'noalert' => true,
            'code' => 1,
        ]);


    }
}
