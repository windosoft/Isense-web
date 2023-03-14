<?php

$notificationFor = [
    "temperature",
    "humidity",
    "offline",
    "voltage",
];
$terminalType = ["iBeacon", "LORA", "RD06"];
$typeOfFacility = ["iBeacon", "TZ-TAG06", "TZ-TAG06B", "TZ-LoRa"];

$dayList = ['sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'enable'];

$timePeriod = ['today', 'yesterday', 'this_month', 'last_month', 'custom'];
$reportType = ['all', 'triggered'];

return [
    "admin" => "",
    'from_email' => 'no-reply@isenseonline.com',
    'admin_email' => 'no-reply@isenseonline.com',
    'from_name' => 'Sanse Web',
    "upload_path" => base_path() . "/public/uploads",
    "notification_for" => $notificationFor,
    "alarm_disabled_time" => 60,
    "android_key" => "AAAApl-Gc9w:APA91bGApAM4-EGjz6xgwF_6zg9JUKjI-hmv7wmsKDGJvKeV2YWI7oWnXcYqYsHZad6XVSEPNqxq32TuWmNcHGDJm2sp7otBYak0GKqlev2RmiahHK6G2SBfmFJdCl3tCHr3GaldnW5H",
    "ios_pem" => "/public/ios/apns.pem",
    "android" => "ANDROID",
    "play_store_url" => "https://play.google.com/store/apps/details?id=com.app.isense",
    "app_store_url" => "#",
    "terminal_type" => $terminalType,
    "type_of_facility" => $typeOfFacility,
    "sensor_expire" => 3,
    "day_list" => $dayList,
    "time_period" => $timePeriod,
    "report_type" => $reportType,
    'experttext_apikey' => 'fjme31cct849r2v',
    'experttext_apisecret' => 'q8ftgx5o3bykm2i',
    'experttext_user' => 'windosoft',
    'experttext_sender' => 'DEFAULT',
];
