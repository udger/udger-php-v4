<?php
require_once __DIR__ . '/vendor/autoload.php';

// creates a new UdgerParser object
$factory = new Udger\ParserFactory(sys_get_temp_dir() . "/udgercache/udgerdb_v4.dat");
$parser = $factory->getParser();

// enable/disable LRU cache
$parser->setCacheEnable(false); // default is enable
//$parser->setCacheSize(4000);  // default size is 3000

try {
    $parser->setUA('Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/48.0.2564.97 Safari/537.36');
    $ret = $parser->parse();
    print_r($ret);
    
    $parser->setUA('Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)');
    $parser->setIP('66.249.76.243');   
    $ret = $parser->parse();
    print_r($ret);    

    $parser->setIP('66.249.76.243');   
    $ret = $parser->parse();
    print_r($ret);   
 
   
} catch (Exception $ex) {
    echo "Error: " . $ex->getMessage(). PHP_EOL;
}
