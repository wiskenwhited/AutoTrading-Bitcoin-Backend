<?php

namespace App\Http\Controllers;

use App\Models\Suggestion;
use Illuminate\Http\Request;

class SuggestionController extends ApiController
{
    public function index(Request $request)
    {
        //TODO: implement from switching the `active/test mode` appends `mode=active/test` to the `/suggestions` and `/trades` https://xchangerate.slack.com/archives/C6N8LH35K/p1503052194000137
        $query = Suggestion::getQuery();

        $filters = $this->getFilterData($request);
        if ($exchange = array_get($filters, 'exchange')) {
            $query->where('exchange', trim($exchange));
        }
        if ($coin = array_get($filters, 'coin')) {
            $query->where('coin', trim($coin));
        }

        $total = $query->count();
        $suggestions = $this->applyPaginationData($request, $query)->get();

        return response()->json([
            'data' => $suggestions,
            'meta' => $this->getResponseMetadata($request, $total)
        ]);
    }

    public function show($id)
    {
        $suggestion = Suggestion::findOrFail($id);

        return response()->json($suggestion);
    }
}