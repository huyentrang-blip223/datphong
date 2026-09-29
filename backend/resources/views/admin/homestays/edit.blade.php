@extends('layouts.app')
@section('title','Sửa homestay | ĐT-07')
@section('content')
<h1>Sửa homestay #{{ $homestay->id }}</h1><form method="post" action="{{ route('admin.homestays.update',$homestay) }}" class="card">@csrf @method('PUT') @include('admin.homestays._form')<button class="btn">Cập nhật</button></form>
@endsection
