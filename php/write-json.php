<?php

/* some configs and parameters */
$data = json_encode(json_decode(file_get_contents('php://input')), JSON_PRETTY_PRINT + JSON_UNESCAPED_UNICODE);
$rootPath = sprintf('%s/%s', dirname(__FILE__), '..');
$relativePath = 'data/mushrooms.json';
$absolutePath = sprintf('%s/%s', $rootPath, $relativePath);

/* write json content */
file_put_contents($absolutePath, $data);

/* build response */
$result = array(
    'success' => true,
    'message' => sprintf('The json file "%s" was successfully written.', $relativePath),
);

/* send response */
print json_encode($result, JSON_PRETTY_PRINT);
