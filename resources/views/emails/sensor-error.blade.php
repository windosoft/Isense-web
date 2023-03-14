@extends('emails.layout')

@section('content')
    <tr>
        <td style="background: #f5f5f5; font-size: 34px; font-weight: 700;font-family:gotham-bold;color:#53457a;padding: 25px 50px;">
            Hello Administrator
        </td>
    </tr>
    <tr>
        <td style="background: #f5f5f5;padding: 0 50px;font-size: 18px;font-family: gotham-light; font-weight: 400;line-height: 22px;">
            @if(isset($data['flag']) && $data['flag'] == 'tag')
                <p style="margin: 0;">Oops ... something went wrong. Tag list information is empty.</p>
            @else
                <p style="margin: 0;">Oops ... something went wrong. Sensor information was not updated in the server.
                    Please contact to support team.</p>
            @endif
        </td>
    </tr>
@endsection
