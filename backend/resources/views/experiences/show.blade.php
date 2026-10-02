@extends('layouts.app')
@section('title', $experience->title)
@section('content')
<h1>{{ $experience->title }}</h1>
<div class="card">
    <p><strong>Homestay:</strong> {{ $experience->homestay->name }}</p>
    <p><strong>Thời gian:</strong> {{ $experience->start_at->format('d/m/Y H:i') }}</p>
    <p><strong>Thời lượng:</strong> {{ $experience->duration_minutes }} phút</p>
    <p><strong>Giá:</strong> {{ number_format($experience->price) }} / người</p>
    <p><strong>Còn chỗ:</strong> {{ max(0, $experience->capacity - $experience->booked_count) }}</p>
    <p>{{ $experience->description }}</p>
</div>

@auth
    @if(auth()->user()->role === 'guest' && $experience->status === 'open')
        <div class="card">
            <form method="post" action="{{ route('experiences.book', $experience) }}">
                @csrf
                <label>Số người
                    <input type="number" min="1" max="20" name="people_count" value="{{ old('people_count', 1) }}" required>
                </label>
                <button class="btn" type="submit">Đặt trải nghiệm</button>
            </form>
        </div>
    @endif
@endauth
@endsection
