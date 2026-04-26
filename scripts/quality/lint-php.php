<?php
declare(strict_types=1);

$root = dirname(__DIR__, 2);
$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root));
$php = PHP_BINARY;

foreach ($iterator as $file) {
    if (!$file->isFile() || $file->getExtension() !== 'php') {
        continue;
    }

    $path = $file->getPathname();
    if (str_contains($path, DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR)
        || str_contains($path, DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR)) {
        continue;
    }

    $cmd = escapeshellarg($php) . ' -l ' . escapeshellarg($path);
    exec($cmd, $output, $code);
    if ($code !== 0) {
        fwrite(STDERR, implode(PHP_EOL, $output) . PHP_EOL);
        exit($code);
    }
}

echo "PHP lint passed\n";
