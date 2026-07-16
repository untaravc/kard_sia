@extends('form.layout')

@section('title', 'Terima kasih')

@section('content')
    <div class="card header">
        <div class="state">
            <div class="emoji">&#9989;</div>
            <h2>Tanggapan terekam</h2>
            <p>Terima kasih telah mengisi <b>{{ $form->title }}</b>.</p>
        </div>
    </div>
    @if($form->status == 1 && $form->allow_multiple)
        <div class="actions">
            <a href="{{ url('/form/' . $form->slug) }}" class="btn-link">Kirim tanggapan lain</a>
        </div>
    @endif
@endsection
