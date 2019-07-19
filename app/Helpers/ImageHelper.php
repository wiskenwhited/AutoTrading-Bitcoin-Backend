<?php

namespace App\Helpers;


use GrahamCampbell\Flysystem\FlysystemManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Intervention\Image\ImageManagerStatic as Image;
use Illuminate\Support\Facades\File;

class ImageHelper
{
    public function __construct()
    {
        Image::configure(array('driver' => 'imagick'));
    }

    public function uploadImage(FlysystemManager $flysystem, Request $request, $folder = 'images', $file_property = 'image')
    {
        $file = $request->file($file_property);
        $filename = $this->filename($file->getClientOriginalName());
        $ext = pathinfo($filename, PATHINFO_EXTENSION);
        $file_path = $file->getRealPath();

        if (!$flysystem->has($folder))
            $flysystem->createDir($folder);

        if (substr($file->getMimeType(), 0, 5) == 'image') {
        // this is an image
            $stream = Image::make($file_path);
            $full_path = $folder . '/' . $filename;
            $uploaded = $flysystem->put($full_path, $stream->encode($ext)->__toString(), ['visibility' => 'public']);
        }else{
            $content = File::get($request->file($file_property)->getRealPath());
            $full_path = $folder . '/' . $filename;
            $uploaded = $flysystem->put($full_path, $content, ['visibility' => 'public']);
        }
        if ($uploaded)
            return $full_path;

        return false;
    }

    public function getFile(FlysystemManager $flysystem, $file)
    {
        return $flysystem->get($file);
    }

    public function listFiles(FlysystemManager $flysystem, $folder = '/')
    {
        return $flysystem->listContents($folder);
    }
    public function deleteFile(FlysystemManager $flysystem, $file)
    {
        return $flysystem->delete($file);
    }

    private function filename($originalName)
    {
        return str_random(5) . '_' . strtolower(str_replace(' ', '_', $originalName));
    }

    public function imageFullPath($file)
    {
        $url = Config::get('flysystem.connections.awss3.bucket');
        return 'https://' . $url . '.s3.amazonaws.com/' . $file;
    }
}