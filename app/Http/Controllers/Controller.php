<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public function createDirectory($folderName)
    {
        $uploadFolder = config('constants.upload_path');
        if (!is_dir($uploadFolder)) {
            mkdir($uploadFolder);
            chmod($uploadFolder, 0777);
        }
        $moduleFolder = $uploadFolder . '/' . $folderName;
        if (!is_dir($moduleFolder)) {
            mkdir($moduleFolder);
            chmod($moduleFolder, 0777);
        }
        return $moduleFolder . '/';
    }
}
