<?php

$filePath        = __DIR__ . '/testOutput.txt';
$currentDateTime = date('Y-m-d H:i:s');
file_put_contents($filePath, "Script was run at: " . $currentDateTime . "\n", FILE_APPEND);
echo "Test script executed successfully.\n";
