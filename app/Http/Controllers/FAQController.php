<?php

namespace App\Http\Controllers;

use App\Auth\Auth;
use App\Http\Controllers\ApiController;
use App\Models\BlogPost;
use App\Models\FAQ;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class FAQController extends ApiController
{
    public function index(Request $request)
    {
        $query = FAQ::whereStatusId(2);
        $filters = $this->getFilterData($request);

        if ($search = array_get($filters, 'search')) {
            $query->where(function($where) use ($search){
                $where->where('question', 'LIKE', '%'. trim($search) .'%')->orWhere('answer', 'LIKE', '%'.ltrim($search) .'%');
            });
        }

        $total = $query->count();
        $query = $this->applyPaginationData($request, $query, ['page' => ['limit' => null]]);
        $faqs = $query->get();

        return response()->json([
            'data' => $faqs,
            'meta' => $this->getResponseMetadata($request, $total)
        ]);
    }
}