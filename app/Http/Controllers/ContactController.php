<?php

namespace App\Http\Controllers;

use App\Helpers\EmailHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ContactController extends ApiController
{
    public function send(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|max:500',
            'phone' => 'required|max:255',
            'subject' => 'required|max:500',
            'message' => 'required',
            'name' => 'required',
        ]);


        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $data = [
            'email' => $request->input('email'),
            'phone' => $request->input('phone'),
            'name' => $request->input('name'),
            'subject' => $request->input('subject'),
            'text' => $request->input('message'),
            'ip' => $request->ip()
        ];

        $success = EmailHelper::contactUs($data);

        return response()->json($success);

    }
}