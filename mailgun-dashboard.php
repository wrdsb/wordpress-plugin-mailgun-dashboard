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

function wrdsb_mailgun_get_current_list_subscriber_count() {
	$mg = new Mailgun(MAILGUN_APIKEY);
	$domain = MAILGUN_DOMAIN;
	
	$address = wrdsb_mailgun_get_current_list_address();
	$result = $mg->get("lists/$address");

	$httpResponseCode = $result->http_response_code;
	$httpResponseBody = $result->http_response_body;

	//return print_r($httpResponseBody, true);
	return $httpResponseBody->list->members_count;
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

#var_dump($result);

/**
 * Add a widget to the dashboard.
 *
 * This function is hooked into the 'wp_dashboard_setup' action below.
 */
function wrdsb_mailgun_add_dashboard_widgets() {
	wp_add_dashboard_widget(
        	'wrdsb_mailgun_dashboard_widget',         // Widget slug.
		'Mailgun Status',         // Title.
		'wrdsb_mailgun_dashboard_widget_function' // Display function.
	);
}
add_action( 'wp_dashboard_setup', 'wrdsb_mailgun_add_dashboard_widgets' );

/**
 * Create the function to output the contents of our Dashboard Widget.
 */
function wrdsb_mailgun_dashboard_widget_function() {
	echo '<div>'. wrdsb_mailgun_get_current_list_address() .'</div>';
	//echo '<div>'. wrdsb_mailgun_get_current_list_deliveries() .'</div>';
	echo '<div>'. wrdsb_mailgun_get_current_list_subscriber_count() .'</div>';
}
