<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Crypt;
use Mail;

class ImportBatchController extends Controller {

    public function __construct() {
        $this->middleware(['auth','sadm']);
    }

    function importBatch($token, Request $request) {
        $atoken = decrypt($token);
        $level = $atoken['level'];
        $idlevel = $atoken['level_id'];
        if ($level == 'A' || $level == 'B') {
            return redirect(route('accessdenied'));
        }
        $objbatch = new \App\Model\ImportFiles();
        $id = $objbatch->getStpAccountId($idlevel,$level);
        $sftpFlag = false;
        if(isset($id) && $id != ''){
            $sftpFlag = true;
        }
        return view('importbatch.importbatch', array(
            'token' => $token,
            'sftpFlag' => $sftpFlag
        ));
    }

    function processBatch($token, Request $request) {
        $atoken = decrypt($token);
        $level = $atoken['level'];
        $idlevel = $atoken['level_id'];

        $post = $request->all();
        $inputMode = $post['inputMode'];
        $fileMode = $post['fileMode'];
        $fileType = $post['fileType'];
        $inputFq = $post['inputFq'];
        $inputSd = $post['inputSd'];
        $inputSy = $post['inputSy'];
        $inputSm = $post['inputSm'];
        $inputDynamic = '';
        if (isset($post['inputDynamic'])) {
            $inputDynamic = $post['inputDynamic'];
        }
        $file = $request->file('file');

        if (!empty($file)) {

            $newname = 'SBF7_' . md5($level . $idlevel . $file->getClientOriginalName()) . time();
            $ext = $file->getClientOriginalExtension();
            if ($fileMode == 'bankadapter') {

                $file->move('/var/tmp/batch', $newname . '.' . $ext);
            } else {
                $file->move('/var/tmp/batch', $newname);
            }



//        $file->move('/var/tmp/batch',$newname);
            $objbatch = new \App\Model\ImportBatchFile();
            $data = array();
            if ($fileMode == 'bankadapter') {

                $data['tmp_file'] = '/var/tmp/batch/' . $newname . '.' . $ext;
            } else {
                $data['tmp_file'] = '/var/tmp/batch/' . $newname;
            }

//        $data['tmp_file']='/var/tmp/batch/'.$newname;
            $data['real_file'] = $file->getClientOriginalName();
            $data['level'] = $level;
            $data['idlevel'] = $idlevel;
            $data['data'] = json_encode(array('type' => $fileType, 'mode' => $fileMode, 'fq' => $inputFq, 'sd' => $inputSd, 'sm' => $inputSm, 'sy' => $inputSy, 'dynamic' => $inputDynamic));
            switch ($inputMode) {
                case 'loadpayments':
                    $data['oper'] = 'L';
                    break;
                case 'updatepayments':
                    $data['oper'] = 'U';
                    break;
                case 'loadevendorpayments':
                    break;
            }
            $data['created_at'] = date('Y-m-d H:i:s');
            $tid = $objbatch->createBatchTask($data);
            exec('php /var/scripts/batchconsume.php ' . base64_encode($tid) . ' > /dev/null &');
            return redirect()->route('importbatchback', ['token' => $token]);
        } else {
            return view('importbatch.importbatch', ['token' => $token]);
        }
    }

    function importbatchback($token, Request $request) {
        $atoken = decrypt($token);
        $level = $atoken['level'];
        $idlevel = $atoken['level_id'];
        if ($level == 'A' || $level == 'B') {
            return redirect(route('accessdenied'));
        }
        $objbatch = new \App\Model\ImportBatchFile();
        $filter = \DataFilter::source($objbatch->getBackList($level, $idlevel));
        $filter->add('real_file', 'File', 'text');
        $filter->submit('search');
        $filter->reset('reset');
        $filter->build();
        $grid = \DataGrid::source($filter);
        $grid->add($token, $token)->style("display:none;");
        $grid->add('idimport_batch_file', 'id')->style("display:none;");
        $grid->add('created_at', 'Uploaded on', true);
        $grid->add('real_file', 'File');
        $grid->add('oper', 'Op', true);
        $grid->add('records', 'Lines', true);
        $grid->add('completed', 'Processed', true);
        $grid->add('errors', 'Errors', true);
        $grid->add('updated_at', 'Last Update', true);
        $grid->add('status', 'Status', true)->style("text-align:right;");
        $grid->row(
                function ($row) {
            $id = $row->cell('idimport_batch_file')->value;
            $token = $row->cells[0]->name;
            $sts = $row->cell('status')->value;
            switch ($sts) {
                case 0:
                    $sts = '<span class="label label-default">to Consume</span>';
                    break;
                case 1:
                    $ds = DB::table('importbatch_summary')->where('id_batch_file', $id)->count();
                    if ($ds > 0) {
                        $sts = '<a href="' . route('viewimportbatch', ['token' => $token, 'id' => $id]) . '"><span class="label label-info">Consuming...</span></a>';
                    } else {
                        $sts = '<span class="label label-info">Consuming...</span>';
                    }
                    break;
                case 90:
                    $ds = DB::table('importbatch_summary')->where('id_batch_file', $id)->count();
                    if ($ds > 0) {
                        $sts = '<a href="' . route('viewimportbatch', ['token' => $token, 'id' => $id]) . '"><span class="label label-success">Completed</span></a>';
                    } else {
                        $sts = '<span class="label label-success">Completed</span>';
                    }
                    break;
                case 99:
                    $sts = '<span class="label label-warning">Empty File</span>';
                    break;
                case 100:
                    $sts = '<span class="label label-danger">Missing data</span>';
                    break;
                case 101:
                    $sts = '<span class="label label-danger">Invalid Type</span>';
                    break;
                case 102:
                    $sts = '<span class="label label-danger">Invalid Operation</span>';
                    break;
            }
            $row->cell('status')->value = $sts;
            $row->cell('status')->style("text-align:right;");
            $oper = $row->cell('oper')->value;
            if ($oper == 'L') {
                $row->cell('oper')->value = 'Load';
            } elseif ($oper == 'U') {
                $row->cell('oper')->value = 'Update';
            }
            $row->cell('idimport_batch_file')->style("display:none;");
            $row->cells[0]->style("display:none;");
        }
        );
        $sql = $filter->query->toSql();
        $bindings = $filter->query->getBindings();
        $sql_ready = $sql;
        foreach ($bindings as $replace) {
            $sql_ready = preg_replace('/\?/', "'$replace'", $sql_ready, 1);
        }
        $sql_ready = encrypt($sql_ready);
        $itemsPerPage = 15;
        if ($request->get('itemspage')) {
            $itemsPerPage = $request->get('itemspage');
        }
        $grid->orderBy('idimport_batch_file', 'DESC');
        $grid->paginate($itemsPerPage);
        $grid->attributes(array("class" => "table table-responsive table-striped table-bordered table-hover dataTable no-footer"));
        $objbatch = new \App\Model\ImportFiles();
        $id = $objbatch->getStpAccountId($idlevel,$level);
        $sftpFlag = false;
        if(isset($id) && $id != ''){
            $sftpFlag = true;
        }
        return view('importbatch.importbatchback', array(
            'token' => $token,
            'grid' => $grid,
            'filter' => $filter,
            'sqlEncrypted' => $sql_ready,
            'itemspage' => $itemsPerPage,
            'sftpFlag' => $sftpFlag
        ));
    }

    function viewimportbatch($token, $id, Request $request) {
        $atoken = decrypt($token);
        $level = $atoken['level'];
        $idlevel = $atoken['level_id'];
        if ($level == 'A' || $level == 'B') {
            return redirect(route('accessdenied'));
        }
        $objbatch = new \App\Model\ImportBatchFile();
        $filter = \DataFilter::source($objbatch->getBackSummaryList($id));
        $filter->add('error', 'Message', 'text');
        $filter->submit('search');
        $filter->reset('reset');
        $filter->build();
        $grid = \DataGrid::source($filter);
        $grid->add('line', 'Line', true);
        $grid->add('error', 'Message')->cell(function ($value) {
            return trim($value);
        });
        $sql = $filter->query->toSql();
        $bindings = $filter->query->getBindings();
        $sql_ready = $sql;
        foreach ($bindings as $replace) {
            $sql_ready = preg_replace('/\?/', "'$replace'", $sql_ready, 1);
        }
        $sql_ready = encrypt($sql_ready);
        $itemsPerPage = 15;
        if ($request->get('itemspage')) {
            $itemsPerPage = $request->get('itemspage');
        }
        $grid->orderBy('line', 'ASC');
        $grid->paginate($itemsPerPage);
        $grid->attributes(array("class" => "table table-responsive table-striped table-bordered table-hover dataTable no-footer"));
        $fd = $objbatch->getBatchInfo($id);
        return view('importbatch.importbatchsummary', array(
            'token' => $token,
            'grid' => $grid,
            'filter' => $filter,
            'file' => $fd->real_file,
            'sqlEncrypted' => $sql_ready,
            'itemspage' => $itemsPerPage
        ));
    }

    function importbatchtag($token, Request $request) {
        $atoken = decrypt($token);
        $level = $atoken['level'];
        $idlevel = $atoken['level_id'];
        if ($level == 'A' || $level == 'B') {
            return redirect(route('accessdenied'));
        }
        $objbatch = new \App\Model\ImportFiles();
        $id = $objbatch->getStpAccountId($idlevel,$level);
        $sftpFlag = false;
        if(isset($id) && $id != ''){
            $sftpFlag = true;
        }
        return view('importbatch.importbatchtag', array(
            'token' => $token,
            'sftpFlag' => $sftpFlag
        ));
    }

    function processbatchtag($token, Request $request) {
        $atoken = decrypt($token);
        $level = $atoken['level'];
        $idlevel = $atoken['level_id'];

        $post = $request->all();
        $inputTag = strtoupper($post['inputTag']);
        $file = $request->file('file');
        if (!empty($file)) {
            $newname = 'SBL9_' . md5($level . $idlevel . $file->getClientOriginalName()) . time();
            $file->move('/var/tmp/batch', $newname);
            $objbatch = new \App\Model\ImportBatchFile();
            $data = array();
            $data['tmp_file'] = '/var/tmp/batch/' . $newname;
            $data['real_file'] = $file->getClientOriginalName();
            $data['level'] = $level;
            $data['idlevel'] = $idlevel;
            $data['data'] = json_encode(array('type' => 'tag', 'mode' => 'tag', 'dynamic' => 1, 'tag' => $inputTag));
            $data['oper'] = 'L';
            $data['created_at'] = date('Y-m-d H:i:s');
            $tid = $objbatch->createBatchTask($data);
            exec('php /var/scripts/batchconsume.php ' . base64_encode($tid) . ' > /dev/null &');
            return redirect()->route('importbatchback', ['token' => $token]);
        } else {
            return view('importbatch.importbatchtag', ['token' => $token]);
        }
    }

    function importbatchsftp($token, Request $request) {
        $atoken = decrypt($token);
        $level = $atoken['level'];
        $idlevel = $atoken['level_id'];
        if ($level == 'A' || $level == 'B') {
            return redirect(route('accessdenied'));
        }
        $objbatch = new \App\Model\ImportFiles();
        $filter = \DataFilter::source($objbatch->getFilesBatchType($idlevel, $level));
        $filter->add('file_name', 'File', 'text');
        $filter->submit('search');
        $filter->reset('reset');
        $filter->build();
        $grid = \DataGrid::source($filter);
        $grid->add($token, $token)->style("display:none;");
        $grid->add('batch_id', 'id')->style("display:none;");
        $grid->add('file_recieved_date', 'Uploaded on', true);
        $grid->add('file_name', 'File');
        $grid->add('row_count', 'Lines', true);
        $grid->add('success', 'Processed', true);
        $grid->add('fail_line', 'Errors', true);
        $grid->add('declined', 'Declined', true);
        $grid->add('updated_timestamp', 'Last Update', true);
        $grid->add('current_status', 'Status', true)->style("text-align:Left;");
        $grid->row(
            function ($row) {
                $id = $row->cell('batch_id')->value;
                $token = $row->cells[0]->name;
                $sts = $row->cell('current_status')->value;
                switch ($sts) {
                    case 0:
                        $sts = '<span class="label label-default">Received</span>';
                        break;
                    case 1:
                        $sts = '<span class="label label-info">Processing...</span>';
                        break;
                    case 2:
                        $ds = DB::table('import_filedb.import_batch_history')->where('batchid', $id)->count();
                        if ($ds > 0) {
                            $sts = '<a href="' . route('viewimportbatchsftp', ['token' => $token, 'id' => $id]) . '"><span class="label label-success">Completed</span></a>';
                        } else {
                            $sts = '<span class="label label-success">Completed</span>';
                        }

                        break;
                    case 3:
                        $sts = '<span class="label label-warning">Reading File Error</span>';
                        break;
                    case 6:
                        $sts = '<span class="label label-danger">Processing File Error</span>';
                        break;
                    case 7:
                        $sts = '<span class="label label-danger">Retry..</span>';
                        break;
                    case 8:
                        $sts = '<span class="label label-info">Processing</span>';
                        break;
                    case 9:
                        $ds = DB::table('import_filedb.import_batch_history')->where('batchid', $id)->count();
                        if ($ds > 0) {
                            $sts = '<a href="' . route('viewimportbatchsftp', ['token' => $token, 'id' => $id]) . '"><span class="label label-danger">Process Error</span></a>';
                        } else {
                            $sts = '<span class="label label-danger">Error</span>';
                        }
                        break;
                    case 99:
                        $sts = '<span class="label label-danger">Invalid File</span>';
                        break;
                }
                $row->cell('current_status')->value = $sts;
                $row->cell('current_status')->style("text-align:center;");
                $row->cell('batch_id')->style("display:none;");
                $row->cells[0]->style("display:none;");
            }
        );
        $sql = $filter->query->toSql();
        $bindings = $filter->query->getBindings();
        $sql_ready = $sql;
        foreach ($bindings as $replace) {
            $sql_ready = preg_replace('/\?/', "'$replace'", $sql_ready, 1);
        }
        $sql_ready = encrypt($sql_ready);
        $itemsPerPage = 15;
        if ($request->get('itemspage')) {
            $itemsPerPage = $request->get('itemspage');
        }
        $grid->orderBy('batch_id', 'DESC');
        $grid->paginate($itemsPerPage);
        $grid->attributes(array("class" => "table table-responsive table-striped table-bordered table-hover dataTable no-footer"));
        $id = $objbatch->getStpAccountId($idlevel,$level);
        $sftpFlag = false;
        if(isset($id) && $id != ''){
            $sftpFlag = true;
        }
        return view('importbatch.importbatchback', array(
            'token' => $token,
            'grid' => $grid,
            'filter' => $filter,
            'sqlEncrypted' => $sql_ready,
            'itemspage' => $itemsPerPage,
            'sftpFlag' => $sftpFlag
        ));
    }

    function viewimportbatchsftp($token, $id, Request $request) {
        $atoken = decrypt($token);
        $level = $atoken['level'];
        $idlevel = $atoken['level_id'];
        if ($level == 'A' || $level == 'B') {
            return redirect(route('accessdenied'));
        }
        $objbatch = new \App\Model\ImportBatchHistory();
        $filter = \DataFilter::source($objbatch->getBackSummaryList($id));
        $filter->add('resoponse_text', 'Message', 'text');
        $filter->submit('search');
        $filter->reset('reset');
        $filter->build();
        $grid = \DataGrid::source($filter);
        $grid->add('file_line_number', 'Line', true);
        $grid->add('date','Date',true);
        $grid->add('property_id','Paypoint',true);
        $grid->add('transaction_id','Trans ID',true);
        $grid->add('total_amount','Amount',true);
        $grid->add('conv_fee','Fee',true);
        $grid->add('resoponse_text', 'Message')->cell(function ($value) {
            return trim($value);
        });
        $sql = $filter->query->toSql();
        $bindings = $filter->query->getBindings();
        $sql_ready = $sql;
        foreach ($bindings as $replace) {
            $sql_ready = preg_replace('/\?/', "'$replace'", $sql_ready, 1);
        }
        $sql_ready = encrypt($sql_ready);
        $itemsPerPage = 15;
        if ($request->get('itemspage')) {
            $itemsPerPage = $request->get('itemspage');
        }
        $grid->orderBy('file_line_number', 'ASC');
        $grid->paginate($itemsPerPage);
        $grid->attributes(array("class" => "table table-responsive table-striped table-bordered table-hover dataTable no-footer"));
        $fd = $objbatch->getBatchInfo($id);
        return view('importbatch.importbatchsftpsummary', array(
            'token' => $token,
            'grid' => $grid,
            'filter' => $filter,
            'file' => $fd->file_name,
            'sqlEncrypted' => $sql_ready,
            'itemspage' => $itemsPerPage
        ));
    }
}
