<?php

namespace App\Models;

class TradingBotJob extends Model
{
    protected $fillable = [
        'job_id',
        'job_type',
        'job_arguments',
        'dispatch_count'
    ];

    public function scopeByJobType($query, $jobType)
    {
        return $query->where('job_type', $jobType);
    }

    public function scopeHasNotDispatchedJob($query)
    {
        return $query->where('dispatch_count', 0)->orWhere('dispatch_count', 1);
    }

    public function scopeByJobArguments($query, array $arguments)
    {
        return $query->where('job_arguments', 'LIKE', json_encode($arguments));
    }

    public function setJobArgumentsAttribute($arguments)
    {
        $this->attributes['job_arguments'] = json_encode($arguments);
    }

    public function getJobArgumentsAttribute()
    {
        return json_decode($this->attributes['job_arguments'], true);
    }
}