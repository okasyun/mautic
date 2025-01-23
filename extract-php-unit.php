<?php

$inputFile  = 'junit-output.xml';
$outputFile = 'filtered-errors-failures.xml';

// XMLファイルを読み込む
if (!file_exists($inputFile)) {
    exit("Input file not found: $inputFile\n");
}

$xml = simplexml_load_file($inputFile);
if (false === $xml) {
    exit("Failed to load XML file\n");
}

// 新しいXML構造を作成
$outputXml = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><testsuites/>');

function processTestSuite($testsuite, $parentNode)
{
    // 新しいテストスイートノードを作成
    $newSuite = $parentNode->addChild('testsuite');

    // 属性をコピー
    foreach ($testsuite->attributes() as $key => $value) {
        $newSuite->addAttribute($key, (string) $value);
    }

    // 子のテストスイートを処理
    foreach ($testsuite->testsuite as $childSuite) {
        if ((int) $childSuite['errors'] > 0 || (int) $childSuite['failures'] > 0) {
            processTestSuite($childSuite, $newSuite);
        }
    }

    return $newSuite;
}

// メインの処理
foreach ($xml->testsuite as $testsuite) {
    if ((int) $testsuite['errors'] > 0 || (int) $testsuite['failures'] > 0) {
        processTestSuite($testsuite, $outputXml);
    }
}

// 整形されたXMLを出力
$dom                     = new DOMDocument('1.0');
$dom->preserveWhiteSpace = false;
$dom->formatOutput       = true;
$dom->loadXML($outputXml->asXML());
$dom->save($outputFile);

echo "\nFiltered errors and failures saved to: $outputFile\n";
