<?php 
require_once __DIR__ . '/../vendor/autoload.php'; // Autoload files using Composer autoload

use CIPack\HelloWorld\SayHello;
use CIPack\Helpers\Utils;


echo SayHello::world() . "\n";

echo Utils::decimal_br(12.76) . "\n";

echo Utils::date_br() . "\n";
