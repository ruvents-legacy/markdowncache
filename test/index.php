<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once __DIR__.'/../vendor/autoload.php';

$markdownCache = new Vudaltsov\MarkdownCache\MarkdownCache(__DIR__.'/cache');
$markdownCache->render(__DIR__.'/source/test.md');
