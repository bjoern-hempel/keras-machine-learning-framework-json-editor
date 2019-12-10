<?php

/**
 * Prints the given class (one liner).
 *
 * @param array $class
 * @param int $index
 * @param bool $onlyPrintTrainClasses
 */
function printClass(array $class, int &$index, bool $onlyPrintTrainClasses = false)
{
    /** Some output templates */
    $templatePlural = '%03d) Class %-30s Plural of "%s"';
    $templateSingular = '%03d) Class %-30s Singular of "%s"';
    $templateDeleted = '%03d) Class %-30s Was deleted%s';
    $templateDuplicate = '%03d) Class %-30s Duplicate of "%s"';
    $templateTrain = '%03d) Class %-30s %-50s %s';

    /** print plural */
    if (array_key_exists('plural', $class)) {
        if (!$onlyPrintTrainClasses) {
            $index++;
            print sprintf(
                $templatePlural,
                $index,
                sprintf('"%s":', $class['class']),
                $class['plural']
            );
            print "\n";
        }
        return;
    }

    /** print singular */
    if (array_key_exists('singular', $class)) {
        if (!$onlyPrintTrainClasses) {
            $index++;
            print sprintf(
                $templateSingular,
                $index,
                sprintf('"%s":', $class['class']),
                $class['singular']
            );
            print "\n";
        }
        return;
    }

    /** print duplicate */
    if (array_key_exists('duplicate', $class)) {
        if (!$onlyPrintTrainClasses) {
            $index++;
            print sprintf(
                $class['class'] === $class['duplicate'] ? $templateDeleted : $templateDuplicate,
                $index,
                sprintf('"%s":', $class['class']),
                $class['class'] === $class['duplicate'] ? '' : $class['duplicate']
            );
            print "\n";
        }
        return;
    }

    /** Some assertions */
    assert(array_key_exists('name', $class));
    assert(array_key_exists('GB', $class['name']));
    assert($class['name']['GB'] !== '');
    assert(array_key_exists('DE', $class['name']));
    assert($class['name']['DE'] !== '');
    assert(count($class['category-path']) > 0);

    $index++;
    print sprintf(
        $templateTrain,
        $index,
        sprintf('"%s":', $class['class']),
        $class['name']['GB'],
        implode(' > ', $class['category-path'][0])
    );
    print "\n";
}

/**
 * Prints the given cateogory (one liner).
 *
 * @param array $category
 */
function printCategory(array $category)
{
    print_r($category);

    print $category['category']."\n";

    exit;
}

/**
 * Calculates the category path (array output).
 *
 * @param array $structureCategories
 * @param string $categoryName
 * @param array $categoryPath
 * @return array
 */
function getCategoryPath(array $structureCategories, string $categoryName, array $categoryPath = array())
{
    assert(array_key_exists($categoryName, $structureCategories), sprintf('Missing category "%s".', $categoryName));

    array_unshift($categoryPath, $categoryName);

    $category = $structureCategories[$categoryName];

    if (!array_key_exists('parent-categories', $category)) {
        return $categoryPath;
    }

    $parentCategories = $category['parent-categories'];

    if (!is_array($parentCategories)) {
        return $categoryPath;
    }

    if (count($parentCategories) <= 0) {
        return $categoryPath;
    }

    return getCategoryPath($structureCategories, $parentCategories[0], $categoryPath);
}

/**
 * Builds the category structure.
 *
 * @param array $categories
 * @return array
 */
function buildStructureCategories(array $categories)
{
    $structure = array();

    foreach ($categories as $category) {
        $categoryName = $category['category'];

        if (count($category['parent-categories']) === 0) {
            $category['parent-categories'] = null;
        }
        if (count($category['related-categories']) === 0) {
            $category['related-categories'] = null;
        }

        $structure[$categoryName] = $category;
    }

    return $structure;
}

/**
 * Builds the class structure.
 *
 * @param array $classes
 * @param array $structureCategories
 * @return array
 */
function buildStructureClasses(array $classes, array $structureCategories)
{
    $structure = array();

    foreach ($classes as $class) {
        $className = $class['class'];

        if (!array_key_exists('categories', $class)) {
            $structure[$className] = $class;
            continue;
        }

        $categories = $class['categories'];

        $categoryPathArray = array();

        foreach ($categories as $category) {
            $categoryPath = getCategoryPath($structureCategories, $category);

            array_push($categoryPathArray, $categoryPath);
        }

        $class['category-path'] = $categoryPathArray;

        assert(!array_key_exists($className, $structure), 'Class name "%s" already exists!');

        $structure[$className] = $class;
    }

    return $structure;
}
