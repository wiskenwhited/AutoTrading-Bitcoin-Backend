<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;

class TradingBotRequest extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'request_type',
        'json_payload',
        'json_response',
        'json_meta',
        'is_open'
    ];

    public function setJsonPayloadAttribute(array $value)
    {
        $this->attributes['json_payload'] = json_encode($value);
    }

    public function getJsonPayloadAttribute()
    {
        return json_decode(array_get($this->attributes, 'json_payload'), true);
    }

    public function setJsonResponseAttribute(array $value)
    {
        $this->attributes['json_response'] = json_encode($value);
    }

    public function getJsonResponseAttribute()
    {
        return json_decode(array_get($this->attributes, 'json_response'), true);
    }

    public function setJsonMetaAttribute(array $value)
    {
        $this->attributes['json_meta'] = json_encode($value);
    }

    public function getJsonMetaAttribute()
    {
        return json_decode(array_get($this->attributes, 'json_response'), true);
    }
}