<?php

namespace App\Http\Controllers\Admin;

use App\Auth\Auth;
use App\Helpers\ImageHelper;
use App\Http\Controllers\ApiController;
use App\Models\FAQ;
use Carbon\Carbon;
use GrahamCampbell\Flysystem\FlysystemManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class FAQController extends ApiController
{
    public function show(Request $request, $id){
        $faq = FAQ::find($id);

        return response()->json($faq);
    }

    public function index(Request $request)
    {
        $query = FAQ::query();
        $filters = $this->getFilterData($request);

        if ($search = array_get($filters, 'search')) {
            $query->where(function($where) use ($search){
              $where->where('question', 'LIKE', '%'. trim($search) .'%')->orWhere('answer', 'LIKE', '%'.ltrim($search) .'%');
            });
        }

        $total = $query->count();
        $query = $this->applyPaginationData($request, $query, ['page' => ['limit' => null]]);
        $faq = $query->get();

        return response()->json([
            'data' => $faq,
            'meta' => $this->getResponseMetadata($request, $total)
        ]);
        return response()->json($faq);
    }

    public function store(FlysystemManager $flysystem, Auth $auth, Request $request)
    {
        $validator = Validator::make($request->all(), [
            'question' => 'required|max:1000',
            'answer' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $faq = new FAQ();
        $faq->question = $request->input('question');
        $faq->answer = $request->input('answer');
        if ($request->input('image'))
            $faq->image = $request->input('image');
        $faq->save();

        return response()->json($faq->fresh());

    }


    public function publish(Request $request, $id){

        $faq = FAQ::withTrashed()->find($id);

        if (!$faq) {
            return response()->json('FAQ does not exist.', 422);
        }
        $faq->deleted_at = null;
        $faq->status_id = FAQ::StatusPublished;
        $faq->save();

        return response()->json($faq);
    }

    public function unpublish(Request $request, $id){

        $faq = FAQ::withTrashed()->find($id);

        if (!$faq) {
            return response()->json('FAQ does not exist.', 422);
        }
        $faq->deleted_at = null;
        $faq->status_id = FAQ::StatusDraft;
        $faq->save();

        return response()->json($faq);
    }

    public function update(FlysystemManager $flysystem, Request $request, $id)
    {

        $validator = Validator::make($request->all(), [
            'question' => 'required|max:1000',
            'answer' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $faq = FAQ::find($id);

        if (!$faq) {
            return response()->json('FAQ does not exist.', 422);
        }
        $faq->question = $request->input('question');
        $faq->answer = $request->input('answer');
        if ($request->input('image'))
            $faq->image = $request->input('image');
        $faq->save();

        return response()->json($faq->fresh());
    }

    public function destroy(Request $request, $id){

        $faq = FAQ::withTrashed()->find($id);

        if (!$faq) {
            return response()->json('FAQ does not exist.', 422);
        }
        $faq->status_id = FAQ::StatusDeleted;
        $faq->save();
        $faq->delete();
        return response()->json([]);
    }
}