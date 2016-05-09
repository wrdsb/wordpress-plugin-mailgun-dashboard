<?php
/*
* Plugin Name: Mailgun Dashboard
* Plugin URI: https://github.com/wrdsb/wordpress-plugin-mailgun-dashboard
* Description: Dashboard widgets and admin pages for Mailgun status and stats
* Author: WRDSB
* Author URI: https://github.com/wrdsb
* Version: 0.0.1
* License: GPLv2 or later
* GitHub Plugin URI: wrdsb/wordpress-plugin-mailgun-dashboard
* GitHub Branch: master
*/

require 'vendor/autoload.php';
use Mailgun\Mailgun;

$mg = new Mailgun(MAILGUN_APIKEY);
$domain = MAILGUN_DOMAIN;

# For testing our connection to the API:
# Get the last 25 log entries, and dump
# info to the browser.
$result = $mg->get("$domain/log", array('limit' => 25, 'skip'  => 0));

$httpResponseCode = $result->http_response_code;
$httpResponseBody = $result->http_response_body;

# Iterate through the results and echo the message IDs.
$logItems = $result->http_response_body->items;
foreach($logItems as $logItem){
    echo $logItem->message_id . "\n";
}

