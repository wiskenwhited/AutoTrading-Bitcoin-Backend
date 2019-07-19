<?php

namespace App\Http\Controllers\Admin;


use App\Helpers\ImageHelper;
use App\Http\Controllers\ApiController;
use GrahamCampbell\Flysystem\FlysystemManager;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;


class FileController extends ApiController
{
    public function store(FlysystemManager $flysystem, Request $request)
    {
        if (!extension_loaded('imagick')) {
            echo 'imagick not installed';
            die;
        }
        $validator = Validator::make($request->all(), [
            'file' => 'required|file'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }


        if ($request->hasFile('file')) {
            $imageHelper = new ImageHelper();
            $image_path = $imageHelper->uploadImage($flysystem, $request, 'files', 'file');
            $file_full_path = $imageHelper->imageFullPath($image_path);

            return response()->json([
                'file_path' => $image_path,
                'file_full_path' => $file_full_path
            ]);
        }

        return response()->json(['File could not be uploaded', 500]);
    }

    public function index(FlysystemManager $flysystem, Request $request, $folder = '/')
    {
        $imageHelper = new ImageHelper();
        return response()->json($imageHelper->listFiles($flysystem, $folder));
    }

    public function delete(FlysystemManager $flysystem, $file)
    {
        $imageHelper = new ImageHelper();

        $imageHelper->deleteFile($flysystem, $file);
    }
}