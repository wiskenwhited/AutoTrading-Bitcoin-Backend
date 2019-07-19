<?php

namespace App\Http\Controllers;

use App\Models\Exchange;
use Illuminate\Http\Request;

class ExchangeController extends ApiController
{
    public function index(Request $request)
    {
        $query = Exchange::getQuery();
        $total = $query->count();
        $exchanges = $this->applyPaginationData($request, $query)->get();

        return response()->json([
            'data' => $exchanges,
            'meta' => $this->getResponseMetadata($request, $total)
        ]);
    }

    public function show($id)
    {
        $suggestion = Exchange::findOrFail($id);

        return response()->json($suggestion);
    }
}