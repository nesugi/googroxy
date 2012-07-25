<?php
error_reporting(0);
require 'privatedata.php';

if ($AUTH != @$_SERVER['HTTP_X_SHARED_SECRET']) {
   header("HTTP/1.2 404 Not Found");
   exit;
}

$url = @$_GET['q'];
if (!isset($url)) {
   header("HTTP/1.2 404 Not Found");
   exit;
}

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_ENCODING, '');
curl_setopt($ch, CURLOPT_TIMEOUT, 30);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);

if (isset($_SERVER['HTTP_USER_AGENT'])) {
   curl_setopt($ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
}
if (isset($_SERVER['HTTP_COOKIE'])) {
   curl_setopt( $ch, CURLOPT_COOKIE, $_SERVER['HTTP_COOKIE'] );
}
if (isset($_SERVER['HTTP_REFERER'])) {
   curl_setopt( $ch, CURLOPT_REFERER, $_SERVER['HTTP_REFERER'] );
}
if (isset($_SERVER['PHP_AUTH_USER']) && isset($_SERVER['PHP_AUTH_PW'])) {
   curl_setopt($ch, CURLOPT_USERPWD, "{$_SERVER['PHP_AUTH_USER']}:{$_SERVER['PHP_AUTH_PW']}");
}

if (count($_POST) > 0) {
   curl_setopt($ch, CURLOPT_POST, true);
   $postfields = array();
   foreach ($_POST as $key => $val) {
       $postfields[] = urlencode($key) . '=' . urlencode($val);
   }
   curl_setopt($ch, CURLOPT_POSTFIELDS, implode('&', $postfields));
}

$headers = array();
function header_callback($ch, $header) {
   global $headers;
   // we add our own content encoding
   // also, the content length might vary because of this
   if (false === stripos($header, 'Content-Encoding: ')
       && false === stripos($header, 'Content-Length: ')
       && false === stripos($header, 'Transfer-Encoding: ')) {
       $headers[] = $header;
   }
   return strlen($header);
}

curl_setopt($ch, CURLOPT_HEADERFUNCTION, 'header_callback');

$output = curl_exec($ch);
if (FALSE === $output) {
   header("HTTP/1.0 500 Server Error");
   print curl_error($ch);
   exit;
}
curl_close($ch);

foreach ($headers as $header) {
   header($header);
}
print $output;
?>