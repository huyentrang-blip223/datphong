@extends('layouts.app')
@section('title','Sua homestay cua toi | DT-07')
@section('content')
<h1>Sua homestay cua toi</h1>
<div class="card">
    <strong>{{ $homestay->name }}</strong>
    <p>Trang nay chi dung de kiem tra ownership policy trong M1. Chuc nang quan ly phong, lich va gia se thuc hien o M2.</p>
</div>
@endsection
