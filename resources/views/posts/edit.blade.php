@extends('layouts.app')

@section('title', 'Edit Post')

@section('content')
<h1>Edit Post</h1>
<form method="POST" action="{{ route('posts.update', $post) }}">
    @csrf
    @method('PUT')
    @include('posts._form')
    <button class="btn btn-primary">Update</button>
</form>
@endsection
