@extends('layouts.app')

@section('title', 'Posts')

@section('content')
<div class="d-flex justify-content-between mb-3">
    <h1>Posts</h1>
    <a href="{{ route('posts.create') }}" class="btn btn-primary">Create Post</a>
</div>

@if($posts->count())
<table class="table table-bordered">
    <thead>
        <tr>
            <th>ID</th>
            <th>Title</th>
            <th>Published At</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
    @foreach($posts as $post)
        <tr>
            <td>{{ $post->id }}</td>
            <td>{{ $post->title }}</td>
            <td>{{ optional($post->published_at)->toDateTimeString() }}</td>
            <td>
                <a href="{{ route('posts.show', $post) }}" class="btn btn-sm btn-secondary">Show</a>
                <a href="{{ route('posts.edit', $post) }}" class="btn btn-sm btn-warning">Edit</a>
                <form action="{{ route('posts.destroy', $post) }}" method="POST" style="display:inline-block" onsubmit="return confirm('Delete this post?')">
                    @csrf
                    @method('DELETE')
                    <button class="btn btn-sm btn-danger">Delete</button>
                </form>
            </td>
        </tr>
    @endforeach
    </tbody>
</table>

{{ $posts->links() }}
@else
<p>No posts yet.</p>
@endif
@endsection
