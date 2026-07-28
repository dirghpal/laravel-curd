@extends('layouts.app')

@section('title', 'Edit Product')

@section('content')
<h1>Edit Product</h1>
<form method="POST" action="{{ route('products.update', $product) }}">
    @csrf
    @method('PUT')
    @include('products._form')
    <button class="btn btn-primary">Update</button>
</form>
@endsection
