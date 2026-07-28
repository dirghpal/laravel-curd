<?php

namespace App\Http\Controllers\Api;

use App\Models\Post;
use Illuminate\Http\Request;

class LikeController extends ApiController
{  
    public function store(Request $request, Post $post)
    {
        $like = $post->likes()->firstOrCreate(['user_id' => $request->user()->id]);

        return $this->respondSuccess([
            'liked' => true,
            'likes_count' => $post->likes()->count(),
        ], $like->wasRecentlyCreated ? 'Post liked successfully.' : 'Post is already liked.');
    }

    public function destroy(Request $request, Post $post)
    {
        $deleted = $post->likes()->where('user_id', $request->user()->id)->delete();

        if (! $deleted) {
            return $this->respondError('You have not liked this post.', 404);
        }

        return $this->respondSuccess([
            'liked' => false,
            'likes_count' => $post->likes()->count(),
        ], 'Post unliked successfully.');
    }
}
