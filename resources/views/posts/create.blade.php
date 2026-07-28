@extends('layouts.app')

@section('title', 'Create Post')

@section('content')
<h1>Create Post</h1>
<form method="POST" action="{{ route('posts.store') }}">
    @csrf
    @include('posts._form')
    <button class="btn btn-primary">Save</button>
</form>
@endsection
