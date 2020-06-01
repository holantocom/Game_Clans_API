<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");
        
function autoloader($class) {
    include 'classes/' . $class . '.class.php';
}
spl_autoload_register('autoloader');

$api = new ApiRouter();