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

/** @var string $jsonPathRelative */
$jsonPathRelative = 'data/ml.json';

/** @var boolean $onlyPrintTrainClasses */
$onlyPrintTrainClasses = true;

/** @var string $rootPath */
$rootPath = dirname(dirname(__FILE__));

/** @var string $jsonPathAbsolute */
$jsonPathAbsolute = sprintf('%s/%s', $rootPath, $jsonPathRelative);

/** Some pre calculations */
$data = json_decode(file_get_contents($jsonPathAbsolute), true);
$classes = $data['classes'];
$categories = $data['categories'];
$structureCategories = buildStructureCategories($categories);
$structureClasses = buildStructureClasses($classes, $structureCategories);

/** Print out all classes */
$counter = 0;
foreach ($structureClasses as $class) {
    printClass($class, $counter, $onlyPrintTrainClasses);
}

/** Beautify the output */
print "\n\n";
