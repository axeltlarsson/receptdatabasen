<?php
if ($_SERVER['REQUEST_METHOD'] != 'GET') {
  http_response_code(405);
  return false;
}

if (isset($_GET['site'])) {

  $site = $_GET['site'];
  error_log('Reverse proxy fetching: ' . $site);
  header('Access-Control-Allow-Origin: *');

  try {
    $html = file_get_contents($site);
    echo $html;
  } catch(Exception $e) {
    http_response_code(418);
  }
} else {
  http_response_code(400);
}
?>

