@extends('form.layout')

@section('title', $form->title)

@section('content')
    <form method="POST" action="{{ url('/form/' . $form->slug) }}" id="siaForm">
        @csrf

        <div class="card header">
            <h1>{{ $form->title }}</h1>
            @if($form->description)
                <p>{{ $form->description }}</p>
            @endif
            @if($auth)
                <span class="badge-login">Login sebagai: <b>{{ $auth['name'] ?: $auth['email'] }}</b></span>
            @endif
        </div>

        {{-- Guest identity capture (only when nobody is logged in) --}}
        @unless($auth)
            <div class="card field">
                <label class="q">Nama<span class="req">*</span></label>
                <input type="text" name="respondent_name" value="{{ old('respondent_name') }}" placeholder="Nama lengkap Anda">
                @if($errors->has('respondent_name'))
                    <div class="invalid">{{ $errors->first('respondent_name') }}</div>
                @endif
            </div>
            @if($form->collect_email)
                <div class="card field">
                    <label class="q">Email<span class="req">*</span></label>
                    <input type="email" name="respondent_email" value="{{ old('respondent_email') }}" placeholder="nama@email.com">
                    @if($errors->has('respondent_email'))
                        <div class="invalid">{{ $errors->first('respondent_email') }}</div>
                    @endif
                </div>
            @endif
        @endunless

        @foreach($form->fields as $field)
            @php
                $name = 'field_' . $field->id;
                $old = old($name);
                $hasError = $errors->has($name);
            @endphp
            <div class="card field">
                <label class="q">
                    {{ $field->label }}
                    @if($field->is_required)<span class="req">*</span>@endif
                </label>
                @if($field->description)
                    <p class="help">{{ $field->description }}</p>
                @endif

                @switch($field->type)
                    @case('textarea')
                        <textarea name="{{ $name }}" placeholder="{{ $field->placeholder }}">{{ $old }}</textarea>
                        @break

                    @case('number')
                        <input type="number" step="any" name="{{ $name }}" value="{{ $old }}" placeholder="{{ $field->placeholder }}">
                        @break

                    @case('email')
                        <input type="email" name="{{ $name }}" value="{{ $old }}" placeholder="{{ $field->placeholder }}">
                        @break

                    @case('date')
                        <input type="date" name="{{ $name }}" value="{{ $old }}">
                        @break

                    @case('select')
                        <select name="{{ $name }}">
                            <option value="">— Pilih —</option>
                            @foreach(($field->options ?: []) as $opt)
                                <option value="{{ $opt }}" {{ $old === $opt ? 'selected' : '' }}>{{ $opt }}</option>
                            @endforeach
                        </select>
                        @break

                    @case('radio')
                        @foreach(($field->options ?: []) as $i => $opt)
                            <label class="opt">
                                <input type="radio" name="{{ $name }}" value="{{ $opt }}" {{ $old === $opt ? 'checked' : '' }}>
                                <span>{{ $opt }}</span>
                            </label>
                        @endforeach
                        @break

                    @case('checkbox')
                        @php $oldArr = is_array($old) ? $old : []; @endphp
                        @foreach(($field->options ?: []) as $opt)
                            <label class="opt">
                                <input type="checkbox" name="{{ $name }}[]" value="{{ $opt }}" {{ in_array($opt, $oldArr) ? 'checked' : '' }}>
                                <span>{{ $opt }}</span>
                            </label>
                        @endforeach
                        @break

                    @case('rating')
                        @php $max = $field->max_rating ?: 5; $current = (int) $old; @endphp
                        <div class="scale">
                            @for($s = 1; $s <= $max; $s++)
                                <label class="scale-item">
                                    <input type="radio" name="{{ $name }}" value="{{ $s }}" {{ $current === $s ? 'checked' : '' }}>
                                    <span class="scale-num">{{ $s }}</span>
                                </label>
                            @endfor
                        </div>
                        @break

                    @default
                        <input type="text" name="{{ $name }}" value="{{ $old }}" placeholder="{{ $field->placeholder }}">
                @endswitch

                @if($hasError)
                    <div class="invalid">{{ $errors->first($name) }}</div>
                @endif
            </div>
        @endforeach

        <div class="actions">
            <button type="submit" class="btn">Kirim</button>
            <a href="{{ url('/form/' . $form->slug) }}" class="btn-link">Kosongkan formulir</a>
        </div>
    </form>
@endsection
