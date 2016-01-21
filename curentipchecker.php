<?
//0.0.0.0

/*
IP Change Script
James Blain
This script will check the external ip of a sever and then email a
message with the new ip if it has changed. This can be useful for
situations where your IP may change. Some examples of this are home
servers using DHCP routers to connect to ISPS

NOTE: The second line of this file will ALWAYS be the previous IP
this is because the script stores the old IP within itself.

You will also need to set emails and the iformation for the script to use.
*/

//Email Server Settings
$host = "ssl://smtpout.secureserver.net"; // Host address for email client
$port = "465";	// Port for email
$username = "user@domain.com"; //Username for email
$password = "password"; // Password for email

// Define Notification Email Settings
$from = "My Server <server@domain.com>"; // Address emails will come from
$to = "Bob IT Man <bob@aol.com>"; // Address for emails to go to
$to .= ", John Doe <john@aol.com.com>"; // additional emails here you may add extra lines if needed or delete them
$subject = "Server IP CHANGE | ".date('Y-m-d H:i:s');

//Define External IP Service it will need to return a plain Ip example "123.123.123.123"
$IpService = 'http://ipecho.net/plain';

// DO NOT EDIT BELOW THIS POINT!
// <----  SCRIPT STARTS BELOW THIS POINT! ----->
//   DO NOT EDIT BELOW THIS POINT!

//Use External Service to get current IP
$currentip = trim(file_get_contents($IpService));

//validate the IP
if(preg_match('/^\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}\z/', $currentip)) {
	//Get last known IP
	$filecontents = file(__FILE__);//Read this script in to an array
	$oldip = trim(substr($filecontents[1], 2)); //Pull the second line of this script and make it the old ip
	$filecontents[1] = "//".$currentip."\n"; //Reset the top of this script to the new ip in case it changed
	if( $oldip == $currentip ) {
		die();
	} else {
		$response = "NEW";
	}
} else {
	$response = "ERROR";
}

// SEND AN EMAIL
require_once ('Mail.php'); // PEAR Mail package
require_once ('Mail/mime.php'); // PEAR Mail_Mime packge

$headers = array ('From' => $from, 'To' => $to, 'Subject' => $subject);
 
//If we made it this far we need to update the IP and send an email
if($response == "ERROR") {
	// text and html versions of email.
	$text = "There was an error checking the IP of the server the response it got was ".$currentip;
	$html = "There was an error checking the IP of the server the response it got was ".$currentip;

} elseif($response == "NEW") {
	// text and html versions of email.
	$text = "The IP of your server has changed the new IP is ".$currentip." the old IP was ".$oldip;
	$html = "The IP of your server has changed the new IP is ".$currentip." the old IP was ".$oldip;
}

$mime = new Mail_mime();
$mime->setTXTBody($text);
$mime->setHTMLBody($html);

$body = $mime->get();
$headers = $mime->headers($headers);
 
$smtp = Mail::factory('smtp', array ('host' => $host, 'auth' => true, 'port' => $port, 'username' => $username,'password' => $password));

$mail = $smtp->send($to, $headers, $body);

//Determine if email alert was sent
if (!PEAR::isError($mail)) {
   // Write the new ip to the second line of this file
file_put_contents(__FILE__, implode("", $filecontents));
}



