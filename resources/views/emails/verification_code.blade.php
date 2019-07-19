@extends('emails.layout.template')
@section('content')

    <p>
        Hello, your verification code is: {{ $code }}
    </p>

@endsection