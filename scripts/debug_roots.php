<?php
$html = file_get_contents(__DIR__ . '/../storage/debug_customer_helps.html');
libxml_use_internal_errors(true);
$dom = new DOMDocument();
$dom->loadHTML('<!doctype html><html><body>' . $html . '</body></html>');
$body = $dom->getElementsByTagName('body')->item(0);
$i = 0;
foreach ($body->childNodes as $child) {
    if ($child->nodeType === XML_ELEMENT_NODE) {
        echo (++$i) . ': ' . $child->nodeName . PHP_EOL;
    }
}

// Also dump first-level nodes with HTML
foreach ($body->childNodes as $child) {
    if ($child->nodeType === XML_ELEMENT_NODE) {
        echo "--- NODE: " . $child->nodeName . " ---\n";
        echo $dom->saveHTML($child) . "\n\n";
    }
}
