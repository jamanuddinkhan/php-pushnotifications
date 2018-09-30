<?php 

/**
 * Sending push notifications to an iOS/Android/Windows Application
 * @package	Notification
 * @author	rajkhan.me
 * @version 1.3
 */

class PushNotifications {
	
	// API access key of Google API's Console for Android.
	private static $API_ACCESS_KEY = 'API_ACCESS_KEY';
	
	// private key's passphrase for iOS.
	private static $passphrase = 'joashp';
	
	//  Set name of push channel for (Windows Phone 8).
    private static $channelName = "joashp";
	
	// Change the above three variables as per application.
	public function __construct() {
		exit('Init function is not allowed');
	}
	
	/**
     * Send push notification to Android Application
     *
     * @param array
     * @param number
     * @return array
     */
	public function android($data, $reg_id) {
	        $url = 'https://android.googleapis.com/gcm/send';
	        $message = array(
	            'title' => $data['mtitle'],
	            'message' => $data['mdesc'],
	            'subtitle' => '',
	            'tickerText' => '',
	            'msgcnt' => 1,
	            'vibrate' => 1
	        );
	        
	        $headers = array(
	        	'Authorization: key=' .self::$API_ACCESS_KEY,
	        	'Content-Type: application/json'
	        );
	
	        $fields = array(
	            'registration_ids' => array($reg_id),
	            'data' => $message,
	        );
	
	    	return $this->useCurl($url, $headers, json_encode($fields));
    }
	
	/**
     * Send push notification to Windows Application
     *
     * @param array
     * @param string 
     * @return array
     */
	public function WP($data, $uri) {
		$delay = 2;
		$msg =  "<?xml version=\"1.0\" encoding=\"utf-8\"?>" .
		        "<wp:Notification xmlns:wp=\"WPNotification\">" .
		            "<wp:Toast>" .
		                "<wp:Text1>".htmlspecialchars($data['mtitle'])."</wp:Text1>" .
		                "<wp:Text2>".htmlspecialchars($data['mdesc'])."</wp:Text2>" .
		            "</wp:Toast>" .
		        "</wp:Notification>";
		
		$sendedheaders =  array(
		    'Content-Type: text/xml',
		    'Accept: application/*',
		    'X-WindowsPhone-Target: toast',
		    "X-NotificationClass: $delay"
		);
		
		$response = $this->useCurl($uri, $sendedheaders, $msg);
		
		$result = array();
		foreach(explode("\n", $response) as $line) {
		    $tab = explode(":", $line, 2);
		    if (count($tab) == 2)
		        $result[$tab[0]] = trim($tab[1]);
		}
		
		return $result;
	}
	
	/**
     * Send push notification for iOS Application
     *
     * @param array
     * @param string 
     * @return array
     */
	public function iOS($data, $devicetoken) {

		$deviceToken = $devicetoken;
		$ctx = stream_context_create();
		
		// include a certificate file
		stream_context_set_option($ctx, 'ssl', 'local_cert', 'ck.pem');
		stream_context_set_option($ctx, 'ssl', 'passphrase', self::$passphrase);

		// open a connection to the APNS server
		$fp = stream_socket_client(
			'ssl://gateway.sandbox.push.apple.com:2195', $err,
			$errstr, 60, STREAM_CLIENT_CONNECT|STREAM_CLIENT_PERSISTENT, $ctx);

		if (!$fp)
			exit("Failed to connect: $err $errstr" . PHP_EOL);

		// create the payload body
		$body['aps'] = array(
			'alert' => array(
			    'title' => $data['mtitle'],
                'body' => $data['mdesc'],
			 ),
			'sound' => 'default'
		);

		// encode the payload as JSON
		$payload = json_encode($body);

		// build the binary notification
		$msg = chr(0) . pack('n', 32) . pack('H*', $deviceToken) . pack('n', strlen($payload)) . $payload;

		// send it to the server
		$result = fwrite($fp, $msg, strlen($msg));
		
		// close the connection to the server
		fclose($fp);

		if (!$result)
			return 'Message not delivered' . PHP_EOL;
		else
			return 'Message successfully delivered' . PHP_EOL;
	}
	
	/**
     * Send push notification for iOS Application
     *
     * @param array (optional) 
     * @param string
	 * @param string
	 * @param string
     * @return array
     */
	private function useCurl(&$model, $url, $headers, $fields = null) {
		// create curl resource 
		$ch = curl_init();
		
		// is URL exist
		if ($url) {
			
			// set the URL
			curl_setopt($ch, CURLOPT_URL, $url);
			
			// set POST data
			curl_setopt($ch, CURLOPT_POST, true);
			
			// set header data
			curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
			
			// return the transfer as a string 
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	 
			// disable SSL Certificate support 
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			if ($fields) {
				curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
			}
	 
			// execute post
			$result = curl_exec($ch);
			if ($result === FALSE) {
				die('Curl failed: ' . curl_error($ch));
			}
			
			// close connection
			curl_close($ch);

			return $result;
        }
    }    
}
?>