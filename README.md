# Udger client for PHP (data ver. 4)
Local parser is very fast and accurate useragent string detection solution. Enables developers to locally install and integrate a highly-scalable product.
We provide the detection of the devices (personal computer, tablet, Smart TV, Game console etc.), operating system, client SW type (browser, e-mail client etc.)
and devices market name (example: Sony Xperia Tablet S, Nokia Lumia 820 etc.).
It also provides information about IP addresses (Public proxies, VPN services, Tor exit nodes, Fake crawlers, Web scrapers, Datacenter name .. etc.)

- Tested with more the 1.000.000 unique user agents.
- Processes Google User-Agent Client Hints
- Up to date data provided by https://udger.com/

### Requirements
 - php >= 5.5.0
 - ext-sqlite3 (http://php.net/manual/en/book.sqlite3.php)
 - datafile v4 (udgerdb_v4.dat) from https://data.udger.com/ 

### Features
- Fast
- LRU cache
- Released under the MIT

### Install 
    composer require udger/udger-php-v4
    
### Usage
You should review the included examples (`parseUA-IP.php`, `parseSec-Ch.php` ... etc)

Here's a quick example:

```php
$factory = new Udger\ParserFactory(sys_get_temp_dir() . "/udgercache/udgerdb_v4.dat");
$parser = $factory->getParser();

// enable/disable LRU cache
$parser->setCacheEnable(false); // default is enable
//$parser->setCacheSize(4000);  // default size is 3000

try {   
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
```   

### Automatic updates download
- for autoupdate data use Udger data updater (https://udger.com/support/documentation/?doc=62)

### Author
- The Udger.com Team (info@udger.com)
                
### v3 format
For the previous data format (v3), please use https://github.com/udger/udger-php
