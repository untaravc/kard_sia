<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Post;
use Illuminate\Http\Request;

class PostController extends Controller
{
    public function index(Request $request)
    {
        $dataContent = Post::query()->latest();
        $dataContent = $this->withFilter($dataContent, $request);
        $dataContent = $dataContent->paginate(10);

        return response()->json([
            'success' => true,
            'text' => 'Retrieve Posts Success',
            'result' => $dataContent,
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);

        $post = Post::create($data);

        return response()->json([
            'success' => true,
            'text' => 'Create Post Success',
            'result' => $post,
        ]);
    }

    public function show($id)
    {
        $post = Post::find($id);
        if (!$post) {
            return response()->json([
                'success' => false,
                'text' => 'Post not found',
                'result' => null,
            ], 404);
        }

        return response()->json([
            'success' => true,
            'text' => 'Retrieve Post Success',
            'result' => $post,
        ]);
    }

    public function update(Request $request, $id)
    {
        $post = Post::find($id);
        if (!$post) {
            return response()->json([
                'success' => false,
                'text' => 'Post not found',
                'result' => null,
            ], 404);
        }

        $data = $this->validateData($request);
        $post->update($data);

        return response()->json([
            'success' => true,
            'text' => 'Update Post Success',
            'result' => $post,
        ]);
    }

    public function destroy($id)
    {
        $post = Post::find($id);
        if (!$post) {
            return response()->json([
                'success' => false,
                'text' => 'Post not found',
                'result' => null,
            ], 404);
        }

        $post->delete();

        return response()->json([
            'success' => true,
            'text' => 'Delete Post Success',
            'result' => null,
        ]);
    }

    protected function validateData(Request $request)
    {
        return $this->validate($request, [
            'title' => 'nullable|string',
            'content' => 'nullable|string',
            'category_id' => 'nullable|integer',
            'section' => 'nullable|string',
            'auth_id' => 'nullable|integer',
            'auth_type' => 'nullable|string',
            'image_url' => 'nullable|string',
            'image_urls' => 'nullable',
            'release_at' => 'nullable|date',
            'attachment_urls' => 'nullable',
        ]);
    }

    protected function withFilter($dataContent, Request $request)
    {
        if ($request->keyword != null) {
            $dataContent = $dataContent->where(function ($query) use ($request) {
                $query->where('title', 'LIKE', '%' . $request->keyword . '%')
                    ->orWhere('content', 'LIKE', '%' . $request->keyword . '%')
                    ->orWhere('section', 'LIKE', '%' . $request->keyword . '%')
                    ->orWhere('auth_type', 'LIKE', '%' . $request->keyword . '%');
            });
        }

        if ($request->category_id != null) {
            $dataContent = $dataContent->where('category_id', $request->category_id);
        }

        if ($request->section != null) {
            $dataContent = $dataContent->where('section', $request->section);
        }

        if ($request->auth_id != null) {
            $dataContent = $dataContent->where('auth_id', $request->auth_id);
        }

        if ($request->auth_type != null) {
            $dataContent = $dataContent->where('auth_type', $request->auth_type);
        }

        return $dataContent;
    }
}
