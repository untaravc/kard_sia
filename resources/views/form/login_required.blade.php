@extends('form.layout')

@section('title', $form->title)

@section('content')
    <div class="card header">
        <div class="state">
            <div class="emoji">&#128273;</div>
            <h2>{{ $form->title }}</h2>
            <p>Formulir ini hanya dapat diisi setelah Anda login.</p>
            <div style="margin-top: 18px;">
                <a href="{{ url('/') }}" class="btn">Login</a>
            </div>
        </div>
    </div>
@endsection
