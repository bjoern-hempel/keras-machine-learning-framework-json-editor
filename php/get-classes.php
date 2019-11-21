<?php

$rootPath = sprintf('%s/%s', dirname(__FILE__), '..');

$sourceRelative = 'data/source/.';
$targetRelative = 'data/target/.';

$sourceAbsolute = sprintf('%s/%s', $rootPath, $sourceRelative);
$targetAbsolute = sprintf('%s/%s', $rootPath, $targetRelative);

function getFolders($dir, $recursive = false, &$results = array())
{
    $files = scandir($dir);

    foreach ($files as $key => $value) {
        $path = realpath($dir . DIRECTORY_SEPARATOR . $value);
        if (is_dir($path)) {
            if ($value != "." && $value != "..") {
                $basename = basename($path);

                if ($recursive) {
                    getFolders($path, $recursive, $results);
                }

                $results[$basename] = array(
                    'path' => $path,
                );
            }
        }
    }

    return $results;
}

$return = array(
    'source' => $sourceRelative,
    'target' => $targetRelative,
    'classes' => getFolders($sourceAbsolute),
);

print json_encode($return, JSON_PRETTY_PRINT);
