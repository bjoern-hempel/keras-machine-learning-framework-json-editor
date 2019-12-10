#!/usr/bin/env php
<?php

/** some includes */
include sprintf('%s/%s', dirname(__FILE__), 'helper/file.php');
include sprintf('%s/%s', dirname(__FILE__), 'helper/structure.php');

/** Switch on assertion */
assert_options(ASSERT_ACTIVE,true);
assert_options(ASSERT_BAIL,true);

/** Assertion output */
assert_options(ASSERT_CALLBACK, function($file, $line, $code) {
    throw new Exception(sprintf('Assert failed in "%s" on line "%s".', $file, $line));
});

/** @var string $rootPath */
$rootPath = dirname(dirname(__FILE__));

/** @var string $jsonPathRelative */
$jsonPathRelative = 'data/ml.json';

/** @var string $dataPathRelative */
$dataPathRelative = 'data/target';

/** @var string $dataPathTrain */
$dataPathTrainRelative = 'data/train';

/** @var string $dataPathJsonRelative */
$dataPathJsonRelative = 'directoryData.json';

/** @var string $dataPathAbsolute */
$dataPathAbsolute = sprintf('%s/%s', $rootPath, $dataPathRelative);

/** @var string $dataPathAbsolute */
$dataPathTrainAbsolute = sprintf('%s/%s', $rootPath, $dataPathTrainRelative);

/** @var string $dataPathJsonAbsolute */
$dataPathJsonAbsolute = sprintf('%s/%s', $rootPath, $dataPathJsonRelative);

/** @var string $jsonPathAbsolute */
$jsonPathAbsolute = sprintf('%s/%s', $rootPath, $jsonPathRelative);

/** @var boolean $onlyPrintTrainClasses */
$onlyPrintTrainClasses = true;

/** @var boolean $summarize */
$summarize = false;

/** @var boolean $humanReadableSize */
$humanReadableSize = true;

/** @var boolean $createTrainData */
$createTrainData = false;

/** @var boolean $createTrainFolders */
$createTrainSymlinks = false;

/** @var boolean $loadDataFromFile */
$loadDataFromFile = true;

/** @var boolean $checkWithJsonDatabase */
$checkWithJsonDatabase = true;

/** @var boolean $onlyPrintTrainClasses */
$onlyPrintTrainClasses = true;

/** @var string $templateDirectory */
$templateDirectory = "%03d) %-80s %5s files     %s\n";

/** Create or read data structure file. */
if ($loadDataFromFile) {
    /** Some assertion. */
    assert(file_exists($dataPathJsonAbsolute));

    /** Get json content. */
    $directoryData = json_decode(file_get_contents($dataPathJsonAbsolute), true);
} else {
    /** Calculate data. */
    $directoryData = scanAndAnalyseDirectory($dataPathRelative, $rootPath, $summarize, $humanReadableSize);

    /** Save data to json file. */
    file_put_contents($dataPathJsonAbsolute, json_encode($directoryData));
}

/** Some pre calculations */
$data = json_decode(file_get_contents($jsonPathAbsolute), true);
$classes = $data['classes'];
$categories = $data['categories'];
$structureCategories = buildStructureCategories($categories);
$structureClasses = buildStructureClasses($classes, $structureCategories);

/** Print all calculated data. */
$counter = 0;
foreach ($directoryData as $folder => $data) {
    $class = basename($folder);

    assert(
        array_key_exists($class, $structureClasses),
        sprintf(
            'Class "%s" does not exist in database.',
            $class
        )
    );

    if ($createTrainData) {
        $sourcePath = sprintf('%s/%s', $rootPath, $folder);
        $targetPath = sprintf('%s/%s', $dataPathTrainAbsolute, basename($folder));

        if (is_file($targetPath) || is_link($targetPath)) {
            print sprintf('"%s" does already exists.'."\n", $targetPath);
            exit;
        }

        /** Create symlink or deep copy data. */
        if ($createTrainSymlinks) {
            symlink($sourcePath, $targetPath);
        } else {
            print sprintf('Copy: %s to %s'."\n", $sourcePath, $targetPath);
            copyDirectoryRecurse($sourcePath, $targetPath);
        }
    }

    /** print some information from current data structure */
    print sprintf($templateDirectory, ++$counter, $folder, $data['number'], $data['size']);
}

/** Beautify the output */
print "\n\n";
