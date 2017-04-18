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

function wrdsb_mailgun_get_current_list_address() {
	$url = parse_url(get_bloginfo('url'));

	if ($url['host'] == 'www.wrdsb.ca'):
		return 'www@'.MAILGUN_DOMAIN;
	elseif ($url['host'] == 'schools.wrdsb.ca'):
		return substr($url['path'], 1).'@'.MAILGUN_DOMAIN;
	else:
		return explode(".", $url['host'])[0].'-'.substr($url['path'], 1).'@'.MAILGUN_DOMAIN;
	endif;
}

function wrdsb_mailgun_get_current_list_deliveries() {
	$mg = new Mailgun(MAILGUN_APIKEY);
	$domain = MAILGUN_DOMAIN;
	$stats = '';

	$address = wrdsb_mailgun_get_current_list_address();
	$result = $mg->get("$domain/tags/$address/stats", array('event' => 'delivered'));

	$httpResponseCode = $result->http_response_code;
	$httpResponseBody = $result->http_response_body;

	## Iterate through the results and echo the message IDs.
	$resultItems = $result->http_response_body->stats;
	foreach($resultItems as $resultItem){
	     $stats .= $resultItem->time.': '.$resultItem->delivered->total . "\n";
	}
	return $stats;
}

function wrdsb_mailgun_get_current_list_info() {
	$mg = new Mailgun(MAILGUN_APIKEY);
	$domain = MAILGUN_DOMAIN;
	$stats = '';

	$address = wrdsb_mailgun_get_current_list_address();
	$result = $mg->get("$domain/lists/$address");

	$httpResponseCode = $result->http_response_code;
	$httpResponseBody = $result->http_response_body;

	$list_info = $result->http_response_body->list;
	return $list_info;
}

function wrdsb_mailgun_get_current_list_members_count() {
	$mg = new Mailgun(MAILGUN_APIKEY);

	$address = wrdsb_mailgun_get_current_list_address();
	$result = $mg->get("https://api.mailgun.net/v3/lists/$address");

	$httpResponseCode = $result->http_response_code;
	$httpResponseBody = $result->http_response_body;

	## Iterate through the results and echo the message IDs.
	$list = $result->http_response_body->list;
	$members_count = $list->members_count;
	return $members_count;
}

#var_dump($result);

/**
 * Add a widget to the dashboard.
 *
 * This function is hooked into the 'wp_dashboard_setup' action below.
 */
function wrdsb_mailgun_add_dashboard_widgets() {
	wp_add_dashboard_widget(
        	'wrdsb_mailgun_dashboard_widget',         // Widget slug.
		'Email Subscriptions Status',         // Title.
		'wrdsb_mailgun_dashboard_widget_function' // Display function.
	);
}
add_action( 'wp_dashboard_setup', 'wrdsb_mailgun_add_dashboard_widgets' );

/**
 * Create the function to output the contents of our Dashboard Widget.
 */
function wrdsb_mailgun_dashboard_widget_function() {
	echo '<p><strong>"From" name:</strong> '. get_bloginfo('name') ."</p>";
	echo '<p><strong>"From" address:</strong> '. wrdsb_mailgun_get_current_list_address() ."</p>";
	echo '<p><strong>Subscriber count:</strong> '. wrdsb_mailgun_get_current_list_members_count() ."</p>";
}
