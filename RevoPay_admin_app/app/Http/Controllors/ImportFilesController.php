<?php

namespace App\Http\Controllers;

use App\Model\ImportFiles;
use Illuminate\Http\Request;
use Crypt;
use DB;

class ImportFilesController extends Controller {

    function importFilesBatch($token) {
        $atoken = decrypt($token);
        $level = $atoken['level'];
        $idlevel = $atoken['level_id'];

        $obj_files = new ImportFiles();
        $data = $obj_files->getFilesBatch();


        $grid = \DataGrid::source($data);

        $grid->add('updated_timestamp', 'Time', true);
        $grid->add('batch_id', 'Batch ID');
        $grid->add('current_status', 'Status', true)->cell(function ($value) {

            switch ($value) {
                case 0;
                    return 'NEW_FILE';
                case 1;
                    return 'READING_FILE_START';
                case 2;
                    return 'PROCESS_FILE_FINISHED';
                case 3;
                    return 'READING_FILE_FAILED';
                case 4;
                    return 'UPLOAD_FAILED';
                case 5;
                    return 'UPLOAD_FINISHED';
                case 6;
                    return 'PROCESSING_FILE_ERROR';
                case 7;
                    return 'REINITIALISE';
                case 9;
                    return 'PROCESS_ERROR';
                case 98;
                    return 'DUPLICATE_FILE';
                case 99;
                    return 'INVALID_FILE';
                default:
                    return 'UNKNOWN';
            }
        });
        $grid->add('file_name', 'Filename');
        $grid->add('value_separator', 'Separator');
        $grid->add('id_level', 'ID Level', true);
        $grid->add('level', 'Level', true);
        $grid->add('processing_time', 'Processing Time');
        $grid->add('autopay_updated', 'Autopay Updated');
        $grid->add('categories_added', 'Categories Added');
        $grid->add('categories_updated', 'Categories Updated');
        $grid->add('fail_line', 'Fail line');
        $grid->add('row_count', 'Rows');
        $grid->add('web_users_added', 'Web Users Added');
        $grid->add('web_users_updated', 'Web Users Updated');
        $grid->add('actionvalue', 'Action')->style('text-align:right;');
        $grid->add('file_path','File Path')->style('display:none;');
        $grid->row(function ($row) {
            $fileName = $row->cell('file_name')->value;
            $file = env('sftp_folder_path') . $fileName;
            $trashFileName = pathinfo($row->cell('file_path')->value);
            $trashFile = env('sftp_folder_trash_path').$trashFileName['basename'];
            if (file_exists($file)) {
                $route = route('downloadfiles', ['file' => base64_encode($file)]);
                $row->cell('file_name')->value = '<a class="underline downloadFileLink" href="' . $route . '" target="_blank">' . $fileName . '</a>';
                $row->cell('actionvalue')->value = '<a class="downloadFileLink btn btn-default btn-xs" href="' . $route . '" target="_blank">Download</a>';
            } elseif(file_exists($trashFile)){
                $route = route('downloadfiles', ['file' => base64_encode($trashFile)]);
                $row->cell('file_name')->value = '<a class="underline downloadFileLink" href="' . $route . '" target="_blank">' . $fileName . '</a>';
                $row->cell('actionvalue')->value = '<a class="downloadFileLink btn btn-default btn-xs" href="' . $route . '" target="_blank">Download</a>';
            } else{
                $row->cell('actionvalue')->value = '';
            }
            $row->cell('file_path')->style('display:none;');
        });

        $grid->attributes(array("class" => "table table-responsive table-striped table-bordered table-hover dataTable no-footer reportaction"));

        $itemsPerPage = 15;
        $grid->orderBy('updated_timestamp', 'DESC');
        $grid->paginate($itemsPerPage);




        return view('reports.importFilesBatch', array(
            'token' => $token,
            'grid' => $grid,
        ));
    }

    function importFilesAudit($token) {
        $atoken = decrypt($token);
        $level = $atoken['level'];
        $idlevel = $atoken['level_id'];

        $obj_files = new ImportFiles();
        $data = $obj_files->getFilesAudit();

        $grid = \DataGrid::source($data);

        $grid->add('timestamp', 'Time', true);
        $grid->add('name', 'Filename');
        $grid->add('type', 'Type', true);
        $grid->add('event', 'Event', true);
        $grid->add('idlevel', 'ID Level', true);
        $grid->add('level', 'Level', true);

        $grid->attributes(array("class" => "table table-responsive table-striped table-bordered table-hover dataTable no-footer reportaction"));

        $itemsPerPage = 15;
        $grid->orderBy('timestamp', 'DESC');
        $grid->paginate($itemsPerPage);

        return view('reports.importFilesAudit', array(
            'token' => $token,
            'grid' => $grid,
        ));
    }

    public function downloadfiles($file) {
        $file = base64_decode($file);

        return response()->download($file);
    }

}
