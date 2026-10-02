@extends('layouts.app')
@section('title', 'Trải nghiệm văn hóa')
@section('content')
<h1>Trải nghiệm văn hóa</h1>
<div class="card">
    <form method="get" action="{{ route('experiences.index') }}">
        <label>Tỉnh/huyện
            <input name="location" value="{{ request('location') }}">
        </label>
        <button class="btn" type="submit">Lọc trải nghiệm</button>
    </form>
</div>

@foreach($experiences as $experience)
    <div class="card">
        <h2><a href="{{ route('experiences.show', $experience) }}">{{ $experience->title }}</a></h2>
        <p>{{ $experience->homestay->name }} - {{ $experience->homestay->province }}</p>
        <p>{{ $experience->start_at->format('d/m/Y H:i') }} - {{ number_format($experience->price) }} / người</p>
        <p>Còn {{ max(0, $experience->capacity - $experience->booked_count) }} chỗ</p>
    </div>
@endforeach

{{ $experiences->links() }}
@endsection
