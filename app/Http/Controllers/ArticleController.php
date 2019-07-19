<?php

namespace App\Http\Controllers;

use App\Auth\Auth;
use App\Http\Controllers\ApiController;
use App\Models\Article;
use App\Models\BlogPost;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ArticleController extends ApiController
{
    public function index(Request $request)
    {
        $query = Article::whereStatusId(2);
        $filters = $this->getFilterData($request);
        $total = $query->count();
        $query = $this->applyPaginationData($request, $query, ['page' => ['limit' => null]]);
        $articles = $query->get();

        return response()->json([
            'data' => $articles,
            'meta' => $this->getResponseMetadata($request, $total)
        ]);
    }


    public function show(Request $request, $slug){
        $article = Article::whereSlug($slug)->first();
        if(!$article)
            abort(404);

        return response()->json($article);
    }
}