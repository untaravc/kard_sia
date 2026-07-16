@extends('form.layout')

@section('title', $form->title)

@section('content')
    <div class="card header">
        <div class="state">
            <div class="emoji">&#128274;</div>
            <h2>{{ $form->title }}</h2>
            <p>{{ $message }}</p>
        </div>
    </div>
@endsection
