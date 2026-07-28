@extends('layouts.app')

@section('title', $product->name)

@section('content')
<h1>{{ $product->name }}</h1>
<p><strong>Price:</strong> {{ number_format($product->price, 2) }}</p>
<p><strong>Stock:</strong> {{ $product->stock }}</p>
<div>
    {!! nl2br(e($product->description)) !!}
</div>
<p class="mt-3"><a href="{{ route('products.index') }}" class="btn btn-secondary">Back to Products</a></p>
@endsection
