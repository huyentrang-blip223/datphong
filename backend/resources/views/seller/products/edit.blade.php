@extends('layouts.app')
@section('title','Sửa sản phẩm')
@section('content')
<h1>Sửa sản phẩm</h1>
<div class="card"><form method="post" action="{{ route('seller.products.update', $product) }}">@method('put') @include('seller.products._form')</form></div>
@endsection
