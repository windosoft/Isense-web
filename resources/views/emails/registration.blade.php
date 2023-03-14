@extends('emails.layout')

@section('content')
    <tr>
        <td style="background: #f5f5f5; text-align: center; font-size: 34px; font-weight: 700;color:#53457a;padding: 60px 0 25px;">
            Welcome!
        </td>
    </tr>
    <tr>
        <td style="background: #f5f5f5;padding: 0 50px;font-size: 18px;font-weight: 400;line-height: 22px;">
            <p><strong>Hello {{$data['username']}}</strong></p>
            <p style="margin:0;">your account has been created on {{env('APP_NAME')}}. </p>
            <p>Below are you system generated credentials,</p>
            <p><strong>please change the password immediately after login.</strong></p>
            <p style="margin:0;">Email : <strong>{{$data['email']}}</strong></p>
            <p style="margin:0;">Password : <strong>{{$data['password']}}</strong></p>
            <p style="text-align: center;">
                <a style="margin-top:10px;display: inline-block;font-weight: 400;text-align: center;white-space: nowrap;vertical-align: middle;-webkit-user-select: none;-moz-user-select: none;-ms-user-select: none;user-select: none;border: 1px solid transparent;padding: 0.65rem 0.65rem;font-size: 19px;text-decoration: none;line-height: 1.25;border-radius: .25rem;-webkit-transition: color 0.15s ease-in-out, background-color 0.15s ease-in-out, border-color 0.15s ease-in-out, -webkit-box-shadow 0.15s ease-in-out;transition: color 0.15s ease-in-out, background-color 0.15s ease-in-out, border-color 0.15s ease-in-out, -webkit-box-shadow 0.15s ease-in-out;transition: color 0.15s ease-in-out, background-color 0.15s ease-in-out, border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;transition: color 0.15s ease-in-out, background-color 0.15s ease-in-out, border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out, -webkit-box-shadow 0.15s ease-in-out;color: #fff;background-color: #5867dd;border-color: #5867dd;"
                   href="{{$data['login_url']}}">Login Now</a>
            </p>
        </td>
    </tr>
@endsection
