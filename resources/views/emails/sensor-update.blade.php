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
<table width="100%" style="margin: 100px auto; background: #f5f5f5;max-width:800px;box-shadow: 4px 6px 7px 0px #00000057;" border="0" cellpadding="0"
       cellspacing="0">
    <tbody style="box-shadow: 4px 6px 7px 0px #00000057;">
    <tr>
        <td style="height: 7px;background: #0e1e38;line-height: 7px;float: left; width: 100%;padding: 0;border-top: 1px #ffffff solid;">
            &nbsp;
        </td>
    </tr>
    <tr>
        <td style="text-align: center; padding: 0px 0; background: #1b3151 !important;">
            <a href="#" style="text-decoration: none;font-size: 52px;text-transform: uppercase;font-weight: 900;">
                <span style="display: inline-block;color: #5867dd;cursor: pointer;"><img src="http://app.isenseonline.com/backend/images/logo-menu.png"  alt=""></span>
            </a>
        </td>
    </tr>

    <tr>
        <td style="background: #172b46; font-size: 16px; color:#FFF;padding:40px 0 0px;padding-left: 50px;font-family: sans-serif;">
            Hello
        </td>
    </tr>
    <tr>
        <td style="background: #172b46; font-size: 16px; color:#FFF;padding: 25px 0 15px;padding-left: 50px;font-family: sans-serif;">
            Please find the attachment for the scheduled report of device {{$data['deviceData']['device_name']}}.
        </td>
    </tr>
    <tr>
        <td style="background: #172b46;padding: 0 50px;font-size: 18px;font-family: sans-serif;line-height: 26px; padding-bottom: 30px;"></td>
    </tr>
    <tr>
        <td style="background: #172b46;color:#FFF;padding: 0 50px;font-size: 15px;font-family: sans-serif;line-height: 26px; padding-bottom: 30px;">
            Regards,<br>{{env('APP_NAME')}} Support Team
        </td>
    </tr>
    <tr>
        <td style="background: #172b46;color:#FFF;padding:15px 110px; font-size: 12px; font-family: sans-serif;color:#d2d2d2;text-align: center;">
            © 2019 {{env('APP_NAME')}}. All Rights Reserved.
        </td>
    </tr>
    </tbody>
</table>
</body>
</html>
