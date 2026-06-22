<?php

namespace App\Http\Controllers;

use App\Filters\TagFilters;
use App\Http\Requests\StoreTagRequest;
use App\Models\Tag;
use Illuminate\Http\Request;

class TagController extends Controller
{
    public function index(Request $request, TagFilters $filters)
    {
        $tags = Tag::withCount('issues')->filter($filters)->orderBy('name')->get();

        return view('tags.index', compact('tags'));
    }

    public function store(StoreTagRequest $request)
    {
        $tag = Tag::create($request->validated());

        return redirect()->route('tags.index')->with('success', 'Tag created.');
    }

    public function delete(Tag $tag)
    {
        if ($tag->deleted_at) {
            $tag->forceDelete();
        } else {
            $tag->delete();
        }

        return redirect()->route('tags.index')->with('success', 'Tag deleted.');
    }

    public function restore(Tag $tag)
    {
        $tag->restore();

        return redirect()->route('tags.index')->with('success', 'Tag restored.');
    }
}
