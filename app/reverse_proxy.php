<?php
if ($_SERVER['REQUEST_METHOD'] != 'GET') {
  http_response_code(405);
  return false;
}

function content_type_of_response($header) {
  foreach ($header as $field) {
    $key_value = explode(":", $field);
    if (strtolower($key_value[0]) == 'content-type')
      return $key_value[1];
  }
}
if (isset($_GET['site'])) {

  $site = $_GET['site'];
  error_log('Reverse proxy fetching: ' . $site);
  header('Access-Control-Allow-Origin: *');

  try {
    $contents = file_get_contents($site);
    $content_type = content_type_of_response($http_response_header);
    header('Content-Type: ' . $content_type);
    echo $contents;
  } catch(Exception $e) {
    http_response_code(418);
  }
} else {
  http_response_code(400);
}
?>

