@extends('layouts.app')
@section('title', 'Thêm phòng')
@section('content')
<h1>Thêm phòng</h1>
<div class="card">
    <form method="post" action="{{ route('host.rooms.store') }}">
        @include('host.rooms._form')
    </form>
</div>
@endsection
