@extends('layouts.app')

@section('title', $post->title)

@section('content')
<h1>{{ $post->title }}</h1>
<p><em>Published at: {{ optional($post->published_at)->toDateTimeString() }}</em></p>
<div>
    {!! nl2br(e($post->body)) !!}
</div>
<p class="mt-3"><a href="{{ route('posts.index') }}" class="btn btn-secondary">Back to Posts</a></p>
@endsection
