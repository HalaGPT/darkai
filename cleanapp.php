<?php

set_time_limit(120); 

$dir = __DIR__ . '/uploads/'; 

$timeThreshold = 40;

$files = scandir($dir);
foreach ($files as $file) {
    if ($file === '.' || $file === '..') {
        continue;
    }
    $filePath = $dir . $file;
    if (is_file($filePath)) {
        if (time() - filemtime($filePath) > $timeThreshold) {
            unlink($filePath);
        }
    }
}
?>
