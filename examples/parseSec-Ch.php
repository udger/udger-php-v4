<?php
require_once __DIR__ . '/vendor/autoload.php';

// creates a new UdgerParser object
$factory = new Udger\ParserFactory(sys_get_temp_dir() . "/udgercache/udgerdb_v4.dat");
$parser = $factory->getParser();

// enable/disable LRU cache
$parser->setCacheEnable(false); // default is enable
//$parser->setCacheSize(4000);  // default size is 3000

try {   
     
    $parser->setSecChUaFullVersionList('"Chromium";v="104.0.5112.102", " Not A;Brand";v="99.0.0.0", "Google Chrome";v="104.0.5112.102"');
    $parser->setSecChUaMobile('?0');
    $parser->setSecChUaPlatform('"Windows"');
    $parser->setSecChUaPlatformVersion('"14.0.0"');        
    $ret = $parser->parse();
    print_r($ret);  
    
    $parser->setSecChUa('" Not;A Brand";v="99", "Google Chrome";v="97", "Chromium";v="97"');
    $parser->setSecChUaMobile('?0');
    $parser->setSecChUaFullVersion('"97.0.4692.71"');
    $ret = $parser->parse(); 
    print_r($ret);

    $parser->setSecChUa('"Chromium";v="104", " Not A;Brand";v="99", "Google Chrome";v="104"');
    $parser->setUA('Mozilla/5.0 (Linux; Android 11; CPH2001) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/104.0.0.0 Mobile Safari/537.36');
    $parser->setSecChUaMobile('?1');
    $parser->setSecChUaFullVersion('"104.0.5112.97"');
    $parser->setSecChUaPlatform('"Android"');
    $parser->setSecChUaPlatformVersion('"11.0.0"');
    $parser->setSecChUaModel('"CPH2001"');
    $ret = $parser->parse();
    print_r($ret);   
 
   
} catch (Exception $ex) {
    echo "Error: " . $ex->getMessage(). PHP_EOL;
}
