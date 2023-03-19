<?php
$front_dir = realpath(__DIR__ . DIRECTORY_SEPARATOR . '../front/') . DIRECTORY_SEPARATOR;

if (!function_exists('getDirContents')) {
    function getDirContents($dir, &$results = array())
    {
        $files = scandir($dir);

        foreach ($files as $key => $value) {
            $path = realpath($dir . DIRECTORY_SEPARATOR . $value);
            if (!is_dir($path)) {
                $results[] = $path;
            } else if ($value != "." && $value != "..") {
                getDirContents($path, $results);
                //$results[] = $path;
            }
        }
        return $results;
    }
}

$files = getDirContents($front_dir);
$length = strlen($front_dir);
$files = array_map(fn ($str) => substr($str, $length), $files);
$files = array_reduce($files, function ($carry, $item) use ($front_dir) {
    $path = $front_dir . $item;
    $item = substr($item, 0, -5);
    $item = str_replace(DIRECTORY_SEPARATOR, '.', $item);
    $carry[$item] = $path;
    return $carry;
}, []);

$files = array_map(function ($file) {
    $content = file_get_contents($file);
    return json_decode($content, true);
}, $files);
return $files;
