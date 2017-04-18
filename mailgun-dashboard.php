<?php
/*
* Plugin Name: Mailgun Dashboard
* Plugin URI: https://github.com/wrdsb/wordpress-plugin-mailgun-dashboard
* Description: Dashboard widgets and admin pages for Mailgun status and stats
* Author: WRDSB
* Author URI: https://github.com/wrdsb
* Version: 0.0.2
* License: GPLv2 or later
* GitHub Plugin URI: wrdsb/wordpress-plugin-mailgun-dashboard
* GitHub Branch: master
*/

require 'vendor/autoload.php';
use Mailgun\Mailgun;

$mg = new Mailgun(MAILGUN_APIKEY);
$domain = MAILGUN_DOMAIN;

function wrdsb_mailgun_get_current_list_address() {
	if ( is_multisite() ) {
		$blog_details = get_blog_details(get_current_blog_id());
		$my_domain = $blog_details->domain;
		$my_slug = str_replace('/','',$blog_details->path);
	} else {
		$parsed = parse_url(get_bloginfo('url'));
		$my_domain = $parsed['host'];
	}
	switch ($my_domain) {
		case "www.wrdsb.ca":
			if (empty($my_slug)) {
				return "www@hedwig.wrdsb.ca";
			} else {
				return "www-".$my_slug."@hedwig.wrdsb.ca";
			}
		case "staff.wrdsb.ca":
			if (empty($my_slug)) {
				return "staff@hedwig.wrdsb.ca";
			} else {
				return "staff-".$my_slug."@hedwig.wrdsb.ca";
			}
		case "schools.wrdsb.ca":
			if (empty($my_slug)) {
				return "schools@hedwig.wrdsb.ca";
			} else {
				return $my_slug."@hedwig.wrdsb.ca";
			}
		case "teachers.wrdsb.ca":
			if (empty($my_slug)) {
				return "teachers@hedwig.wrdsb.ca";
			} else {
				return "teachers-".$my_slug."@hedwig.wrdsb.ca";
			}
		case "llc.wrdsb.ca":
			return "llc@hedwig.wrdsb.ca";
		case "wcssaa.ca":
			return "wcssaa@hedwig.wrdsb.ca";
		case "labs.wrdsb.ca":
			return "wplabs-mailgun-lab@hedwig.wrdsb.ca";
		case "www.stswr.ca":
			return "www@bigbus.stswr.ca";
		default:
			return "no-list@hedwig.wrdsb.ca";
		}
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
