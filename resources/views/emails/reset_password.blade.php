@extends('emails.layout.template')
@section('content')

    <p>
        Hello, you have requested to reset your password, please click on this link to proceed:
        <a href="{{ web_url('#/reset-password/'. $token) }}">Reset password</a>
    </p>

@endsection