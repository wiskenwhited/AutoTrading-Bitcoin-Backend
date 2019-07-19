<?php

namespace App\Http\Controllers;

use App\Auth\Auth;
use App\Http\Controllers\ApiController;
use App\Models\BlogPost;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class BlogController extends ApiController
{
    public function index(Request $request)
    {
        $query = BlogPost::whereStatusId(2);
        $filters = $this->getFilterData($request);

        if ($slug = array_get($filters, 'slug')) {
            $query->where('slug', trim($slug));
        }
        if ($text = array_get($filters, 'text')) {
            $query->where('text', 'LIKE', ltrim($text) .'%');
        }
        if ($title = array_get($filters, 'title')) {
            $query->where('title', 'LIKE', ltrim($title) .'%');
        }


        $total = $query->count();
        $query = $this->applyPaginationData($request, $query, ['page' => ['limit' => null]]);
        $posts = $query->get();

        return response()->json([
            'data' => $posts,
            'meta' => $this->getResponseMetadata($request, $total)
        ]);
    }
}