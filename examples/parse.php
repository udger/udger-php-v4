<?php
require_once __DIR__ . '/vendor/autoload.php';

// creates a new UdgerParser object
$factory = new Udger\ParserFactory(sys_get_temp_dir() . "/udgercache/udgerdb_v4.dat");
$parser = $factory->getParser();

$parser->setCacheEnable(false);
//$parser->setCacheSize(4000);

try {
    //$parser->setUA('Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/48.0.2564.97 Safari/537.36');
    //$parser->setIP("66.249.64.1");
    
    $parser->setHeaders('Sec-Ch-Ua: "Chromium";v="104", " Not A;Brand";v="99", "Google Chrome";v="104"
Sec-Ch-Ua-Mobile: ?0
Sec-Ch-Ua-Full-Version: "104.0.5112.102"
Sec-Ch-Ua-Arch: "x86"
Sec-Ch-Ua-Platform: "Windows"
Sec-Ch-Ua-Platform-Version: "14.0.0"
Sec-Ch-Ua-Model: ""
Sec-Ch-Ua-Bitness: "64"
Sec-Ch-Ua-Full-Version-List: "Chromium";v="104.0.5112.102", " Not A;Brand";v="99.0.0.0", "Google Chrome";v="104.0.5112.102"
User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/104.0.0.0 Safari/537.36');



    $ret = $parser->parse();
    //var_dump($ret);
    $parser->setUA('Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/48.0.2564.97 Safari/537.36');
    $ret = $parser->parse();
    //var_dump($ret);
} catch (Exception $ex) {
    echo "Error: " . $ex->getMessage(). PHP_EOL;
}
