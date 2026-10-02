@extends('layouts.app')
@section('title', 'Sửa phòng')
@section('content')
<h1>Sửa phòng</h1>
<div class="card">
    <form method="post" action="{{ route('host.rooms.update', $room) }}">
        @method('PUT')
        @include('host.rooms._form')
    </form>
</div>
@endsection
