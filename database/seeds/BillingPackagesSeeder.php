<?php

use Illuminate\Database\Seeder;
use App\Models\Country;
use Illuminate\Support\Facades\Hash;

class BillingPackagesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        \App\Models\BillingPackage::truncate();
        if (!\App\Models\BillingPackage::find(1)) {
            $billing_package = new \App\Models\BillingPackage();
            $billing_package->package_name = 'Package 1';
            $billing_package->price = 0.2;
            $billing_package->description = 'Access to All exchanges for 30 days';
            $billing_package->sms = 100;
            $billing_package->emails = 100;
            $billing_package->save();
        }
        if (!\App\Models\BillingPackage::find(2)) {
            $billing_package = new \App\Models\BillingPackage();
            $billing_package->package_name = 'Package 2';
            $billing_package->price = 0.1;
            $billing_package->description = 'Access to A single exchange for 30 days';
            $billing_package->sms = 25;
            $billing_package->emails = 25;
            $billing_package->save();
        }
        if (!\App\Models\BillingPackage::find(3)) {
            $billing_package = new \App\Models\BillingPackage();
            $billing_package->package_name = 'Package 3';
            $billing_package->price = 0.005;
            $billing_package->sms = 3;
            $billing_package->emails = 3;
            $billing_package->description = 'Access to All exchanges for 1 day';
            $billing_package->save();
        }
        if (!\App\Models\BillingPackage::find(4)) {
            $billing_package = new \App\Models\BillingPackage();
            $billing_package->package_name = 'Package 4';
            $billing_package->price = 0.01;
            $billing_package->sms = 3;
            $billing_package->emails = 3;
            $billing_package->description = 'Access to A single exchange for 1 day';
            $billing_package->save();
        }
        if (!\App\Models\BillingPackage::find(5)) {
            $billing_package = new \App\Models\BillingPackage();
            $billing_package->package_name = 'Package 5';
            $billing_package->price = 0.005;
            $billing_package->sms = 1;
            $billing_package->emails = 1;
            $billing_package->description = 'Access to test mode for 30days + 1day Active mode for Singla exchange - For training purposes';
            $billing_package->save();
        }
        if (!\App\Models\BillingPackage::find(6)) {
            $billing_package = new \App\Models\BillingPackage();
            $billing_package->package_name = 'Feature 1';
            $billing_package->price = 0.01;
            $billing_package->sms = 1000;
            $billing_package->emails = 1000;
            $billing_package->description = '1000 emails and 1000 sms';
            $billing_package->is_feature = true;
            $billing_package->save();
        }
    }
}
