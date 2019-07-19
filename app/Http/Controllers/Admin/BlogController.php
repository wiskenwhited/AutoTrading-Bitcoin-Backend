<?php

namespace App\Http\Controllers\Admin;

use App\Auth\Auth;
use App\Helpers\ImageHelper;
use App\Http\Controllers\ApiController;
use App\Models\BlogPost;
use Carbon\Carbon;
use GrahamCampbell\Flysystem\FlysystemManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class BlogController extends ApiController
{
    public function show(Request $request, $id){
        $faq = BlogPost::find($id);
        return response()->json($faq);
    }


    public function index(Request $request)
    {
        $query = BlogPost::query();
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

    public function store(FlysystemManager $flysystem, Auth $auth, Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|max:255',
            'text' => 'required',
            'date_posted' => 'date'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $post = new BlogPost();
        $post->title = $request->input('title');
        $post->text = $request->input('text');
        $post->date_posted = Carbon::now();
        if ($request->input('date_posted'))
            $post->date_posted = $request->input('date_posted');
        if ($request->input('image'))
            $post->image = $request->input('image');
        $post->added_by = $auth->user()->id;

        $post->save();
        $post->slug = str_slug($post->id . '-' . $request->input('title'));
        $post->save();

        return response()->json($post->fresh());

    }

    public function publish(Request $request, $id)
    {

        $post = BlogPost::withTrashed()->find($id);

        if (!$post) {
            return response()->json('Post does not exist.', 422);
        }
        $post->deleted_at = null;
        $post->status_id = BlogPost::StatusPublished;
        $post->save();

        return response()->json($post);
    }

    public function unpublish(Request $request, $id)
    {

        $post = BlogPost::withTrashed()->find($id);

        if (!$post) {
            return response()->json('Post does not exist.', 422);
        }
        $post->deleted_at = null;
        $post->status_id = BlogPost::StatusDraft;
        $post->save();

        return response()->json($post);
    }

    public function update(FlysystemManager $flysystem, Request $request, $id)
    {

        $validator = Validator::make($request->all(), [
            'title' => 'required|max:255',
            'text' => 'required',
            'date_posted' => 'date',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $post = BlogPost::find($id);

        if (!$post) {
            return response()->json('Post does not exist.', 422);
        }
        $post->title = $request->input('title');
        $post->text = $request->input('text');
        if ($request->input('date_posted'))
            $post->date_posted = $request->input('date_posted');

        if ($request->input('image'))
            $post->image = $request->input('image');

        //TODO: check if slug is updated
        // $post->slug = str_slug($post->id . '-' . $request->input('title'));
        $post->save();

        return response()->json($post->fresh());
    }

    public function destroy(Request $request, $id)
    {

        $post = BlogPost::find($id);

        if (!$post) {
            return response()->json('Post does not exist.', 422);
        }
        $post->status_id = BlogPost::StatusDeleted;
        $post->save();
        $post->delete();
        return response()->json([]);
    }
}