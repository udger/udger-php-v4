# Do not use for production environments. Version 4 is for testing purposes only.

# Udger client for PHP (data ver. 4)
Local parser is very fast and accurate useragent string detection solution. Enables developers to locally install and integrate a highly-scalable product.
We provide the detection of the devices (personal computer, tablet, Smart TV, Game console etc.), operating system, client SW type (browser, e-mail client etc.)
and devices market name (example: Sony Xperia Tablet S, Nokia Lumia 820 etc.).
It also provides information about IP addresses (Public proxies, VPN services, Tor exit nodes, Fake crawlers, Web scrapers, Datacenter name .. etc.)

- Tested with more the 1.000.000 unique user agents.
- Up to date data provided by https://udger.com/

### Requirements
 - php >= 5.5.0
 - ext-sqlite3 (http://php.net/manual/en/book.sqlite3.php)
 - datafile v4 (udgerdb_v4.dat) from https://data.udger.com/ 

### Features
- Fast
- LRU cache
- Released under the MIT


### Automatic updates download
- for autoupdate data use Udger data updater (https://udger.com/support/documentation/?doc=62)

### Author
- The Udger.com Team (info@udger.com)
                
### v3 format
For the previous data format (v3), please use https://github.com/udger/udger-php
