<?php

namespace App\Http\Controllers\Admin;

use App\Auth\Auth;
use App\Helpers\ImageHelper;
use App\Http\Controllers\ApiController;
use App\Models\Article;
use App\Models\Contact;
use Carbon\Carbon;
use GrahamCampbell\Flysystem\FlysystemManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ContactController extends ApiController
{
    public function index(Request $request)
    {
        $query = Contact::query();
        $filters = $this->getFilterData($request);
        $total = $query->count();
        $query = $this->applyPaginationData($request, $query, ['page' => ['limit' => null]]);
        $articles = $query->get();

        return response()->json([
            'data' => $articles,
            'meta' => $this->getResponseMetadata($request, $total)
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|max:500',
            'address' => 'required|max:500',
            'phone' => 'required|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $contact = new Contact();
        $contact->email = $request->email;
        $contact->address = $request->address;
        $contact->phone = $request->phone;
        $contact->save();

        return response()->json([]);

    }

}