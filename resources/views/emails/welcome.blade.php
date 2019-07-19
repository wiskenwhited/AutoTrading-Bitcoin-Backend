@extends('emails.layout.template')
@section('content')

    <p>
        Welcome to XChangeRate
    </p>
    <p>
        Please verify your account by following this link:
        <a href="{{ web_url('#/verify/'. $hash) }}">Verify</a>
    </p>

@endsection