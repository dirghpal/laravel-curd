<?php

namespace App\Http\Controllers\Api;

use App\Models\Post;
use Illuminate\Http\Request;

class PostController extends ApiController
{
    public function index(Request $request)
    {
        $perPage = (int) $request->query('per_page', 10);
        $posts = Post::withCount(['comments', 'likes'])->orderBy('id', 'desc')->paginate($perPage);
  
        return $this->respondSuccess(
            $posts->items(),
            'Posts retrieved successfully.',
            200,
            [
                'current_page' => $posts->currentPage(),
                'per_page' => $posts->perPage(),
                'total' => $posts->total(),
                'last_page' => $posts->lastPage(),
            ]
        );
    }

    public function store(Request $request)
    {
        $data = $this->validateApi($request, [
            'title' => 'required|string|max:255',
            'body' => 'required|string',
            'published_at' => 'nullable|date',
        ]);
     
        $post = Post::create($data);

        return $this->respondSuccess($post, 'Post created successfully.', 201);
    }

    public function show(Post $post)
    {
        return $this->respondSuccess($post->loadCount(['comments', 'likes']), 'Post details retrieved successfully.');
    }

    public function update(Request $request, Post $post)
    {
        $data = $this->validateApi($request, [
            'title' => 'required|string|max:255',
            'body' => 'required|string',
            'published_at' => 'nullable|date',
        ]);

        $post->update($data);

        return $this->respondSuccess($post, 'Post updated successfully.');
    }

    public function destroy(Post $post)
    {
        $post->delete();

        return $this->respondSuccess(null, 'Post deleted successfully.', 204);
    }
}
