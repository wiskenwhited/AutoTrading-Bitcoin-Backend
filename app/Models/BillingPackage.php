<?php

namespace App\Models;


class BillingPackage extends Model
{
    const Package1 = 1;
    const Package2 = 2;
    const Package3 = 3;
    const Package4 = 4;
    const Package5 = 5;
    const Feature1 = 6;

    protected $appends = ['live_days', 'test_days', 'type'];

    public function package_rules()
    {
        $rules = [
            BillingPackage::Package1 => [
                'id' => BillingPackage::Package1,
                'exchanges' => -1,
                'live_duration' => 30,
                'test_duration' => 30,
                'type' => 'all-exchanges'
            ],
            BillingPackage::Package2 => [
                'id' => BillingPackage::Package2,
                'exchanges' => 1,
                'live_duration' => 30,
                'test_duration' => 30,
                'type' => 'one-exchanges'
            ],
            BillingPackage::Package3 => [
                'id' => BillingPackage::Package3,
                'exchanges' => -1,
                'live_duration' => 1,
                'test_duration' => 1,
                'type' => 'all-exchanges'
            ],
            BillingPackage::Package4 => [
                'id' => BillingPackage::Package4,
                'exchanges' => 1,
                'live_duration' => 1,
                'test_duration' => 1,
                'type' => 'one-exchanges'
            ],
            BillingPackage::Package5 => [
                'id' => BillingPackage::Package5,
                'exchanges' => 1,
                'live_duration' => 1,
                'test_duration' => 30,
                'type' => 'education'
            ],
            BillingPackage::Feature1 => [
                'id' => BillingPackage::Feature1,
                'type' => 'notifications',
                'test_duration' => 0,
                'live_duration' => 0
            ],
        ];

        return $rules;
    }

    public function getLiveDaysAttribute()
    {
        $package = $this->package_rules()[$this->id];
        return isset($package['live_duration']) ? $package['live_duration'] : null;
    }

    public function getTypeAttribute()
    {
        $package = $this->package_rules()[$this->id];
        return $package['type'];
    }

    public function getTestDaysAttribute()
    {
        $package = $this->package_rules()[$this->id];
        return isset($package['test_duration']) ? $package['test_duration'] : null;
    }
}