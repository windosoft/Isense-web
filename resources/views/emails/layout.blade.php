<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <meta name="x-apple-disable-message-reformatting">
    <meta name="viewport" content="width=device-width, initial-scale=1"/>
    <style>body {
            font-family: gotham-medium;
        }
    </style>
</head>
<body>
<table width="100%" style="margin: 100px auto; background: #f5f5f5;max-width:800px;" border="0" cellpadding="0"
       cellspacing="0">
    <tbody>
    <tr>
        <td style="height: 7px;background: #5867dd;line-height: 7px;float: left; width: 100%;padding: 0;border-top: 1px #ffffff solid;">
            &nbsp;
        </td>
    </tr>
    <tr>
        <td style="text-align: center; padding: 25px 0; background: rgb(35, 47, 62);">
            <a href="#" style="text-decoration: none;font-size: 52px;text-transform: uppercase;font-weight: 900;">
                <span style="display: inline-block;color: #5867dd;cursor: pointer;">I - </span>
                <b style="color: rgb(255, 255, 255);">Sense</b>
            </a>
        </td>
    </tr>

    @yield('content')

    <tr>
        <td style="background: #f5f5f5;padding: 0 50px;font-size: 18px;font-family: gotham-medium;line-height: 26px; padding-bottom: 30px;"></td>
    </tr>
    <tr>
        <td style="background: #f5f5f5;padding: 0 50px;font-size: 18px;font-family: gotham-medium;line-height: 26px; padding-bottom: 30px;">
            Regards,<br>{{env('APP_NAME')}} Support Team
        </td>
    </tr>
    <tr>
        <td style="background: #f5f5f5; padding:15px 110px; font-size: 12px; font-family: gotham-medium;color:#666666;text-align: center;">
            © 2019 {{env('APP_NAME')}}. All Rights Reserved.
        </td>
    </tr>
    </tbody>
</table>
</body>
</html>
