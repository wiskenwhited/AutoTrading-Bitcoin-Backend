@extends('emails.layout.template')
@section('content')
    <p>
        IP: {{ $ip }}
    </p>
    <p>
        Phone: {{ $phone }}
    </p>
    <p>
        Name: {{ $name }}
    </p>
    <p>
        Subject: {{ $subject }}
    </p>
    <p>
        Message: {!! nl2br($text) !!}
    </p>

@endsection