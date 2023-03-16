<?php
$front_dir=__DIR__.DIRECTORY_SEPARATOR.'../front/';
$menu = file_get_contents($front_dir.'menu.json');

return [
    'menu'=>json_decode($menu)
];
