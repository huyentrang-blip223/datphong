@extends('layouts.app')
@section('title','Thêm sản phẩm')
@section('content')
<h1>Thêm sản phẩm</h1>
<div class="card"><form method="post" action="{{ route('seller.products.store') }}">@include('seller.products._form')</form></div>
@endsection
