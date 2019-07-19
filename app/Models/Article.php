<?php

namespace App\Models;


use App\Helpers\ImageHelper;
use Illuminate\Database\Eloquent\SoftDeletes;

class Article extends Model
{
    use SoftDeletes;

    const StatusDraft = 1;
    const StatusPublished = 2;
    const StatusDeleted = 3;

    protected $appends = ['status', 'image_path'];

    public function getStatusAttribute()
    {
        switch ($this->status_id) {
            case self::StatusDraft:
                return "Draft";
            case self::StatusPublished:
                return "Published";
            case self::StatusDeleted:
                return "Deleted";
        }
    }

    public function getImagePathAttribute()
    {
        if ($this->image) {
            $imageHelper = new ImageHelper();
            return $imageHelper->imageFullPath($this->image);
        }

        return '';
    }
}