<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\ApiController;
use App\Models\BillingPackage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;


class BillingPackageController extends ApiController
{
    public function index(Request $request){
        $billingPackages = BillingPackage::get();
        return response()->json($billingPackages);

    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'price' => 'required|numeric',
            'enabled' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }
        if ($request->price <= 0) {
            return response()->json("Invalid price", 422);
        }

        $billing_package = BillingPackage::find($id);
        $billing_package->price = $request->price;
        $billing_package->enabled = $request->input('enabled');
        $billing_package->save();

        return response()->json($billing_package);
    }

    public function updateFeature(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'emails' => 'required|numeric',
            'sms' => 'required|numeric'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $billing_package = BillingPackage::find(BillingPackage::Feature1);
        $billing_package->emails = $request->input('emails');
        $billing_package->sms = $request->input('sms');
        $billing_package->save();

        return response()->json($billing_package);

    }
}