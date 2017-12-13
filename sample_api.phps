<?php
/*
 * There is no documentation for the API.  Instead, please use a developer console to see what you should be requesting
 * (with associated POST/GET parameters) and what you'll get back.  i.e. find a page with data that you want
 * to use and then look at the requests to see how to call it.
 * 
 * Please note, there is a difference between GET and POST - you must always use the right type.
 *
 * There is one exception to make your life easier!  When getting badge records, the JSON for OSM is split into two chunks, 
 * one bit for the name (which is always shown) and another for the records.  The API version gives this to you in one bigger chunk, 
 * much easier!
 * 
 * It is safe to ignore all _csrf POST parameters - they are only used by website logins.
 * 
 * There are a few secret functions to give you access to bits that you won't see called via AJAX on OSM:
 * https://www.onlinescoutmanager.co.uk/api.php?action=getUserRoles
 * https://www.onlinescoutmanager.co.uk/api.php?action=getSectionConfig
 * https://www.onlinescoutmanager.co.uk/api.php?action=getTerms
 * https://www.onlinescoutmanager.co.uk/api.php?action=getNotepads
 * https://www.onlinescoutmanager.co.uk/api.php?action=getMyScoutKey (takes sectionid and scoutid as a parameter)
 * 
 */

$apiid = X; // Get this from OSM
$token = 'XXX'; // Get this from OSM

$myEmail = 'abrown@onedego.co.uk'; // Only needed for authorising the first time
$myPassword = 'Fr33d0m1'; // Only needed for authorising the first time

$userid = 0; // You'll get this programmatically from authorising
$secret = '';// You'll get this programmatically from authorising

$base = 'https://www.onlinescoutmanager.co.uk/';

/* 
 * You need to authorise once to get the secret and userid that you should store for future use.  
 * 
 * After authorising, open OSM in your browser, go to the External Access page 
 * (in the Account dropdown menu) to give this API access to bits of your account.  
 * 
 * If your API is being used by others, please tell them to do this!
 */
 
$json = authorise(); // Only call the first time to get userid and secret
$secret = $json->secret; // Store somewhere
$userid = $json->userid; // Store somewhere

/*
 * Now that you have the secret and userid, you can do pretty much anything that OSM can do on the web.
 */

// List of sections and terms you have access to
$terms = perform_query('api.php?action=getTerms', array());
print_r($terms);

$termid = X; // Get this from getTerms
$sectionid = X; // Get this from getTerms

if (!is_numeric($sectionid)) {
	echo "<b>Section id must be a number!</b>";
	exit;
}
// Events
$events = perform_query('events.php?action=getEvents&sectionid='.$sectionid, array());
foreach ($events->items as $event) {
	$eventid = $event->eventid;
	$eventDetails = perform_query('events.php?action=getEvent&sectionid='.$sectionid.'&eventid='.$eventid, array());
	echo '<b>'.$eventDetails->name . '</b>: '.$eventDetails->startdate . ' - ' . $eventDetails->enddate.'<br />';
}


function authorise() {
	global $myEmail, $myPassword;
	$parts['password'] = $myPassword;
	$parts['email'] = $myEmail;
	return perform_query('users.php?action=authorise', $parts);
}
function perform_query($url, $parts) {
	global $apiid, $token, $base, $myEmail, $myPassword, $userid, $secret;

	$parts['token'] = $token;
	$parts['apiid'] = $apiid;
	
	if ($userid > 0) {
		$parts['userid'] = $userid;
	}
	if (strlen($secret) == 32) {
		$parts['secret'] = $secret;
	}
	
	$data = '';
	foreach ($parts as $key => $val) {
		$data .= '&'.$key.'='.urlencode($val);
	}

	$curl_handle = curl_init();
	curl_setopt($curl_handle, CURLOPT_URL, $base.$url);
	curl_setopt($curl_handle, CURLOPT_POSTFIELDS, substr($data, 1));
	curl_setopt($curl_handle, CURLOPT_POST, 1);
	curl_setopt($curl_handle, CURLOPT_CONNECTTIMEOUT, 2);
	curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, 1);
	$msg = curl_exec($curl_handle);
	return json_decode($msg);	
}
?>
