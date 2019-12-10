<?php

/**
 * Returns the given size in byte into human readable size.
 *
 * @param $sizeByte
 * @return float|string
 */
function getHumanReadableSize($sizeByte)
{
    $templateNumber = '%8.2f %s';

    if ($sizeByte < 1024) {
        return sprintf($templateNumber, $sizeByte, 'Bytes');
    } elseif (($sizeByte >= 1024) && ($sizeByte < 1048576)) {
        return sprintf($templateNumber, $sizeByte / 1024, 'KB');
    } elseif (($sizeByte >= 1048576) && ($sizeByte < 1073741824)) {
        return sprintf($templateNumber, $sizeByte / 1048576, 'MB');
    }

    return sprintf($templateNumber, $sizeByte / 1073741824, 'GB');
}

/**
 * Scans a given directory recursively.
 *
 * @param string $dirRelative
 * @param string $root
 * @param bool $summarize
 * @param bool $humanReadableSize
 * @return array
 */
function scanAndAnalyseDirectory(string $dirRelative, string $root, bool $summarize = false, bool $humanReadableSize = false)
{
    $dirAbsolute = sprintf('%s/%s', $root, $dirRelative);
    $data = array();

    if ($dh = opendir($dirAbsolute)) {

        /** Read all items within $dirAbsolute. */
        while (($file = readdir($dh)) !== false) {

            /** Ignore . and .. */
            if ($file == '.' || $file == '..') {
                continue;
            }

            /** Build relative and absolute path of $file. */
            $pathRelative = sprintf('%s/%s', $dirRelative, $file);
            $pathAbsolute = sprintf('%s/%s', $root, $pathRelative);

            if ($summarize) {
                if (!array_key_exists($dirRelative, $data)) {
                    $data[$dirRelative] = array(
                        'number' => 0,
                        'size' => 0
                    );
                }
            }

            /** We have detected a file. Calculate the size. */
            if (is_file($pathAbsolute)) {
                if (!$summarize) {
                    if (!array_key_exists($dirRelative, $data)) {
                        $data[$dirRelative] = array(
                            'number' => 0,
                            'size' => 0
                        );
                    }
                }

                /** Calculate number and size. */
                $data[$dirRelative]['number']++;
                $data[$dirRelative]['size'] += filesize($pathAbsolute);

                continue;
            }

            $dataDeep = scanAndAnalyseDirectory($pathRelative, $root, $summarize);

            /** Summarize data. */
            if ($summarize) {
                foreach ($dataDeep as $item) {
                    $data[$dirRelative]['number'] += $item['number'];
                    $data[$dirRelative]['size'] += $item['size'];
                }
            }

            /** We have detected a folder. Search recursively. */
            $data = array_merge(
                $data,
                $dataDeep
            );
        }

        closedir($dh);

        if ($humanReadableSize) {
            foreach ($data as &$item) {
                $item['size'] = getHumanReadableSize($item['size']);
            }
        }

        return $data;
    }

    return $data;
}

/**
 * @param $source
 * @param $target
 */
function copyDirectoryRecurse($source, $target)
{
    $dir = opendir($source);
    @mkdir($target);

    while (false !== ($file = readdir($dir))) {
        if (($file != '.') && ($file != '..')) {
            if (is_dir($source . '/' . $file)) {
                copyDirectoryRecurse($source . '/' . $file, $target . '/' . $file);
            } else {
                copy($source . '/' . $file, $target . '/' . $file);
            }
        }
    }

    closedir($dir);
}
