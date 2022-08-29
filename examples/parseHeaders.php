<?php
require_once __DIR__ . '/vendor/autoload.php';

// creates a new UdgerParser object
$factory = new Udger\ParserFactory(sys_get_temp_dir() . "/udgercache/udgerdb_v4.dat");
$parser = $factory->getParser();

// enable/disable LRU cache
$parser->setCacheEnable(false); // default is enable
//$parser->setCacheSize(4000);  // default size is 3000

try {
    
    // set full http header
    $parser->setHeaders('Host: udger.com
Connection: keep-alive
Sec-Ch-Ua: "Chromium";v="104", " Not A;Brand";v="99", "Google Chrome";v="104"
Sec-Ch-Ua-Mobile: ?0
Sec-Ch-Ua-Full-Version: "104.0.5112.102"
Sec-Ch-Ua-Arch: "x86"
Sec-Ch-Ua-Platform: "Windows"
Sec-Ch-Ua-Platform-Version: "14.0.0"
Sec-Ch-Ua-Model: ""
Sec-Ch-Ua-Bitness: "64"
Sec-Ch-Ua-Full-Version-List: "Chromium";v="104.0.5112.102", " Not A;Brand";v="99.0.0.0", "Google Chrome";v="104.0.5112.102"
Upgrade-Insecure-Requests: 1
User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/104.0.0.0 Safari/537.36
Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.9
Sec-Fetch-Site: same-origin
Sec-Fetch-Mode: navigate
Sec-Fetch-User: ?1
Sec-Fetch-Dest: document
Referer: https://udger.com/
Accept-Encoding: gzip, deflate, br
Accept-Language: cs-CZ,cs;q=0.9,en;q=0.8,sk;q=0.7');

    $ret = $parser->parse(); 
    print_r($ret);
    
    // set limited http header (only these values will be used for parsing)
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
    print_r($ret);

    // set First Request http header
    $parser->setHeaders('Sec-Ch-Ua: "Chromium";v="104", " Not A;Brand";v="99", "Google Chrome";v="104"
Sec-Ch-Ua-Mobile: ?0
Sec-Ch-Ua-Platform: "Windows"
User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/104.0.0.0 Safari/537.36');

    $ret = $parser->parse();
    print_r($ret);


    
} catch (Exception $ex) {
    echo "Error: " . $ex->getMessage(). PHP_EOL;
}
