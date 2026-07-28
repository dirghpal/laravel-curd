<?php

namespace App\Http\Controllers\Api;

use App\Models\Comment;
use App\Models\Post;
use Illuminate\Http\Request;

class CommentController extends ApiController
{
    public function index(Post $post)
    {
        return $this->respondSuccess(
            $post->comments()->with('user:id,name')->latest()->get(),
            'Comments retrieved successfully.'
        );
    }

    public function store(Request $request, Post $post)
    {
        $data = $this->validateApi($request, ['body' => 'required|string|max:2000']);
        $comment = $post->comments()->create(['user_id' => $request->user()->id, 'body' => $data['body']]);

        return $this->respondSuccess($comment->load('user:id,name'), 'Comment created successfully.', 201);
    }

    public function destroy(Request $request, Comment $comment)
    {
        $user = $request->user();

        if ($comment->user_id !== $user->id && $user->role !== 'admin') {
            return $this->respondError('You do not have permission to delete this comment.', 403);
        }

        $comment->delete();

        return $this->respondSuccess(null, 'Comment deleted successfully.', 204);
    }
}
