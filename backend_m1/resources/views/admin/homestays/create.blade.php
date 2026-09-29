@extends('layouts.app')
@section('title','Thêm homestay | ĐT-07')
@section('content')
<h1>Thêm homestay</h1><form method="post" action="{{ route('admin.homestays.store') }}" class="card">@csrf @include('admin.homestays._form')<button class="btn">Lưu</button></form>
@endsection
