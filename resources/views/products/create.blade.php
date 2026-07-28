@extends('layouts.app')

@section('title', 'Create Product')

@section('content')
<h1>Create Product</h1>
<form method="POST" action="{{ route('products.store') }}">
    @csrf
    @include('products._form')
    <button class="btn btn-primary">Save</button>
</form>
@endsection
