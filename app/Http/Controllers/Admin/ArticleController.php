<?php

namespace App\Http\Controllers\Admin;

use App\Auth\Auth;
use App\Helpers\ImageHelper;
use App\Http\Controllers\ApiController;
use App\Models\Article;
use Carbon\Carbon;
use GrahamCampbell\Flysystem\FlysystemManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ArticleController extends ApiController
{
    public function show(Request $request, $id)
    {
        $article = Article::find($id);
        return response()->json($article);
    }


    public function index(Request $request)
    {
        $query = Article::query();
        $filters = $this->getFilterData($request);
        $total = $query->count();
        $query = $this->applyPaginationData($request, $query, ['page' => ['limit' => null]]);
        $articles = $query->get();

        return response()->json([
            'data' => $articles,
            'meta' => $this->getResponseMetadata($request, $total)
        ]);
    }

    public function update(FlysystemManager $flysystem, Request $request, $id)
    {

        $validator = Validator::make($request->all(), [
//            'title' => 'required|max:255',
//            'subtitle' => 'required|max:1000',
            'text' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $article = Article::find($id);

        if (!$article) {
            return response()->json('Post does not exist.', 422);
        }

        if ($request->input('title'))
            $article->title = $request->input('title');
        if ($request->input('subtitle'))
            $article->subtitle = $request->input('subtitle');
        $article->text = $request->input('text');
        if ($request->input('image'))
            $article->image = $request->input('image');

        $article->save();

        return response()->json($article->fresh());
    }
}