<?php
//Include the push notification class file
require_once('PushNotifications.php');

// Message payload
$msg_payload = array (
	'mtitle' => 'Notification title',
	'mdesc' => 'Notification body',
);

// Add your ID for android application 
$regId = 'ADD_YOUR_REGITATION_ID_HERE';

// Add your device token for iOS application
$deviceToken = 'DEVICE-TOKEN';

// Add URI for Windows application 
$uri = 'URI';

if ( class_exists( 'PushNotifications' ) ) {

// Send notifications for Android 
PushNotifications::android($msg_payload, $regId);    	
 
// Send notifications for iOS 
PushNotifications::iOS($msg_payload, $deviceToken);

// Send notifications for Windows
PushNotifications::WP8($msg_payload, $uri);   
}




?>