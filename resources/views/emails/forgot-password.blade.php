@extends('emails.layout')

@section('content')
    <tr>
        <td style="background: #f5f5f5; text-align: center; font-size: 34px; font-weight: 700;font-family:gotham-bold;color:#53457a;padding: 60px 0 25px;">
            Dear {{$data['username']}}
        </td>
    </tr>
    <tr>
        <td style="background: #f5f5f5;padding: 0 50px;font-size: 18px;font-family: gotham-light; font-weight: 400;line-height: 22px;padding-bottom: 39px;text-align: center;">
            <p style="margin-bottom: 0;">Need to reset your password? No problem. Just click below to get started.</p>
            <p style="margin-top: 0;">If you didn't request to change your password, you don't have to do anything.</p>
        </td>
    </tr>
    <tr>
        <td style="background: #f5f5f5;padding: 0 50px;font-size: 18px;font-family: gotham-light; font-weight: 400;line-height: 22px;text-align: center;padding-bottom: 42px;">
            <a id="resetPassword" class="font18"
               style="background: #fe590f;color: #ffffff;padding: 20px 60px;border-radius: 40px;font-size: 24px;text-decoration: none;"
               href="{{$data['url']}}">RESET PASSWORD</a>
        </td>
    </tr>
@endsection
