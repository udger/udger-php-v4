<?php
/**
 * UdgerParser - Local parser class
 * 
 * @package    UdgerParser
 * @author     The Udger.com Team (info@udger.com)
 * @license    http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 * @link       http://udger.com/products/local_parser
 */

namespace Udger;

use Udger\Helper\IPInterface;
use Udger\Helper\HeaderInterface;

/**
 * udger.com Local Parser Class
 * 
 * @package UdgerParser
 */
class Parser implements ParserInterface
{
    
    /**
     * IP address for parse
     * 
     * @type string
     */
    protected $ip;

    /**
     * Useragent data for parse
     * 
     * @type array
    */
    protected $data;

    /**
     * Path to the data file
     * 
     * @type string
     */
    protected $path; 
    
    /**
     * DB link
     * 
     * @type object
     */
    protected $dbdat;
    
    /**
     * @var IPInterface 
     */
    protected $ipHelper;
    
    /**
     * @var HeaderInterface 
     */
    protected $headerHelper;

    /**
     * @boolean LRU cache enable/disable
     */
    protected $cacheEnable = true;
    
    /**
     * @array LRU cache
     */
    protected $cacheUA = array();
    protected $cacheCH = array();
    
    /**
     * @int LRU cache size
     */
    protected $cacheSize = 3000;

    /**
     * @param IPInterface, HeaderInterface
     */
    public function __construct(IPInterface $ipHelper, HeaderInterface $headerHelper)
    {
        $this->ipHelper     = $ipHelper;
        $this->headerHelper = $headerHelper;
        $this->initData();
    }

    
    /**
     * Parse the useragent string and/or IP
     * 
     * @return array
     */
    public function parse()
    {
        $this->setDBdat();        
;
        // validate
        if (is_null($this->dbdat) === true) {
            return array('flag' => 3,
                'errortext' => 'data file not found');
        }
 
        //ret values
        $ret = array('user_agent' =>
                    array('ua_string' => '',
                            'sec_ch_ua'                     => '', 
                            'sec_ch_ua_full_version_list'   => '', 
                            'sec_ch_ua_mobile'              => '', 
                            'sec_ch_ua_full_version'        => '', 
                            'sec_ch_ua_platform'            => '',                                                                                     
                            'sec_ch_ua_platform_version'    => '', 
                            'sec_ch_ua_model'               => '', 
                            'ua_class'                      => '',
                            'ua_class_code'                 => '',
                            'ua'                            => '',
                            'ua_version'                    => '',
                            'ua_version_major'              => '',
                            'ua_uptodate_current_version'   => '',
                            'ua_family'                     => '',
                            'ua_family_code'                => '',
                            'ua_family_homepage'            => '',
                            'ua_family_vendor'              => '',
                            'ua_family_vendor_code'         => '',
                            'ua_family_vendor_homepage'     => '',
                            'ua_family_icon'                => '',
                            'ua_family_icon_big'            => '',
                            'ua_family_info_url'            => '',
                            'ua_engine'                     => '',
                            'os'                            => '',
                            'os_code'                       => '',
                            'os_homepage'                   => '',
                            'os_icon'                       => '',
                            'os_icon_big'                   => '',
                            'os_info_url'                   => '',
                            'os_family'                     => '',
                            'os_family_code'                => '',
                            'os_family_vendor'              => '',
                            'os_family_vendor_code'         => '',
                            'os_family_vendor_homepage'     => '',
                            'device_class'                  => '',
                            'device_class_code'             => '',
                            'device_class_icon'             => '',
                            'device_class_icon_big'         => '',
                            'device_class_info_url'         => '',
                            'device_marketname'             => '',
                            'device_brand'                  => '',
                            'device_brand_code'             => '',
                            'device_brand_homepage'         => '',
                            'device_brand_icon'             => '',
                            'device_brand_icon_big'         => '',
                            'device_brand_info_url'         => '',
                            'crawler_last_seen'             => '',
                            'crawler_category'              => '',
                            'crawler_category_code'         => '',
                            'crawler_respect_robotstxt'     => ''
                      ),
                    'ip_address' =>
                      array('ip'                            => '',
                            'ip_ver'                        => '',
                            'ip_classification'             => '',
                            'ip_classification_code'        => '',
                            'ip_hostname'                   => '',
                            'ip_last_seen'                  => '',
                            'ip_country'                    => '',
                            'ip_country_code'               => '',
                            'ip_city'                       => '',
                            'crawler_name'                  => '',
                            'crawler_ver'                   => '',
                            'crawler_ver_major'             => '',
                            'crawler_family'                => '',
                            'crawler_family_code'           => '',
                            'crawler_family_homepage'       => '',
                            'crawler_family_vendor'         => '',
                            'crawler_family_vendor_code'    => '',
                            'crawler_family_vendor_homepage'=> '',
                            'crawler_family_icon'           => '',
                            'crawler_family_info_url'       => '',
                            'crawler_last_seen'             => '',
                            'crawler_category'              => '',
                            'crawler_category_code'         => '',
                            'crawler_respect_robotstxt'     => '',
                            'datacenter_name'               => '',
                            'datacenter_name_code'          => '',
                            'datacenter_homepage'           => ''
                      )
        );

        if (!empty($this->data['ua'])) {
                      
            $usedCache = false;
            if($this->cacheEnable) {
                $retCache = $this->getCache('UA', md5($this->data['ua']) ); 
                if($retCache) {
                    $ret['user_agent'] = unserialize($retCache);
                    $usedCache = true;
                }
            }
            if(!$usedCache) {             
                $client_id = 0;
                $client_class_id = -1;
                $os_id = 0;
                $deviceclass_id = 0;
                $ret['user_agent']['ua_string'] = $this->data['ua'];
                $ret['user_agent']['ua_class'] = 'Unrecognized';
                $ret['user_agent']['ua_class_code'] = 'unrecognized';

                // crawler            
                $q = $this->dbdat->query("SELECT udger_crawler_list.id as botid,name,ver,ver_major,last_seen,respect_robotstxt,family,family_code,family_homepage,family_icon,vendor,vendor_code,vendor_homepage,crawler_classification,crawler_classification_code
                                              FROM udger_crawler_list
                                              LEFT JOIN udger_crawler_class ON udger_crawler_class.id=udger_crawler_list.class_id
                                              WHERE ua_string='" . $this->dbdat->escapeString($this->data['ua']) . "'");

                if ($r = $q->fetchArray(SQLITE3_ASSOC)) {

                    $client_class_id = 99;
                    $ret['user_agent']['ua_class']                  = 'Crawler';
                    $ret['user_agent']['ua_class_code']             = 'crawler';
                    $ret['user_agent']['ua']                        = $r['name'];
                    $ret['user_agent']['ua_version']                = $r['ver'];
                    $ret['user_agent']['ua_version_major']          = $r['ver_major'];
                    $ret['user_agent']['ua_family']                 = $r['family'];
                    $ret['user_agent']['ua_family_code']            = $r['family_code'];
                    $ret['user_agent']['ua_family_homepage']        = $r['family_homepage'];
                    $ret['user_agent']['ua_family_vendor']          = $r['vendor'];
                    $ret['user_agent']['ua_family_vendor_code']     = $r['vendor_code'];
                    $ret['user_agent']['ua_family_vendor_homepage'] = $r['vendor_homepage'];
                    $ret['user_agent']['ua_family_icon']            = $r['family_icon'];
                    $ret['user_agent']['ua_family_info_url']        = "https://udger.com/resources/ua-list/bot-detail?bot=" . $r['family'] . "#id" . $r['botid'];
                    $ret['user_agent']['crawler_last_seen']         = $r['last_seen'];
                    $ret['user_agent']['crawler_category']          = $r['crawler_classification'];
                    $ret['user_agent']['crawler_category_code']     = $r['crawler_classification_code'];
                    $ret['user_agent']['crawler_respect_robotstxt'] = $r['respect_robotstxt'];
                } else {
                    // client
                    $q = $this->dbdat->query("SELECT class_id,client_id,regstring,name,name_code,homepage,icon,icon_big,engine,vendor,vendor_code,vendor_homepage,uptodate_current_version,client_classification,client_classification_code
                                                  FROM udger_client_regex
                                                  JOIN udger_client_list ON udger_client_list.id=udger_client_regex.client_id
                                                  JOIN udger_client_class ON udger_client_class.id=udger_client_list.class_id
                                                  ORDER BY sequence ASC");
                    while ($r = $q->fetchArray(SQLITE3_ASSOC)) {
                        if (@preg_match($r["regstring"], $this->data['ua'], $result)) {
                            $client_id = $r['client_id'];
                            $client_class_id = $r['class_id'];
                            $ret['user_agent']['ua_class'] = $r['client_classification'];
                            $ret['user_agent']['ua_class_code'] = $r['client_classification_code'];
                            if (isset($result[1])) {
                                $ret['user_agent']['ua'] = $r['name'] . " " . $result[1];
                                $ret['user_agent']['ua_version'] = $result[1];
                                $ver_major = explode(".", $result[1]);
                                $ret['user_agent']['ua_version_major'] = $ver_major[0];
                            } else {
                                $ret['user_agent']['ua'] = $r['name'];
                                $ret['user_agent']['ua_version'] = '';
                                $ret['user_agent']['ua_version_major'] = '';
                            }
                            $ret['user_agent']['ua_uptodate_current_version']   = $r['uptodate_current_version'];
                            $ret['user_agent']['ua_family']                     = $r['name'];
                            $ret['user_agent']['ua_family_code']                = $r['name_code'];
                            $ret['user_agent']['ua_family_homepage']            = $r['homepage'];
                            $ret['user_agent']['ua_family_vendor']              = $r['vendor'];
                            $ret['user_agent']['ua_family_vendor_code']         = $r['vendor_code'];
                            $ret['user_agent']['ua_family_vendor_homepage']     = $r['vendor_homepage'];
                            $ret['user_agent']['ua_family_icon']                = $r['icon'];
                            $ret['user_agent']['ua_family_icon_big']            = $r['icon_big'];
                            $ret['user_agent']['ua_family_info_url']            = "https://udger.com/resources/ua-list/browser-detail?browser=" . $r['name'];
                            $ret['user_agent']['ua_engine']                     = $r['engine'];
                            break;
                        }
                    }
                    // os
                    $q = $this->dbdat->query("SELECT os_id,regstring,family,family_code,name,name_code,homepage,icon,icon_big,vendor,vendor_code,vendor_homepage
                                                  FROM udger_os_regex
                                                  JOIN udger_os_list ON udger_os_list.id=udger_os_regex.os_id
                                                  ORDER BY sequence ASC");
                    while ($r = $q->fetchArray(SQLITE3_ASSOC)) {
                        if (@preg_match($r["regstring"], $this->data['ua'], $result)) {
                            $os_id = $r['os_id'];
                            $ret['user_agent']['os']                        = $r['name'];
                            $ret['user_agent']['os_code']                   = $r['name_code'];
                            $ret['user_agent']['os_homepage']               = $r['homepage'];
                            $ret['user_agent']['os_icon']                   = $r['icon'];
                            $ret['user_agent']['os_icon_big']               = $r['icon_big'];
                            $ret['user_agent']['os_info_url']               = "https://udger.com/resources/ua-list/os-detail?os=" . $r['name'];
                            $ret['user_agent']['os_family']                 = $r['family'];
                            $ret['user_agent']['os_family_code']            = $r['family_code'];
                            $ret['user_agent']['os_family_vendor']          = $r['vendor'];
                            $ret['user_agent']['os_family_vendor_code']     = $r['vendor_code'];
                            $ret['user_agent']['os_family_vendor_homepage'] = $r['vendor_homepage'];
                            break;
                        }
                    }
                    // client_os_relation
                    if ($os_id == 0 AND $client_id != 0) {
                        $q = $this->dbdat->query("SELECT os_id,family,family_code,name,name_code,homepage,icon,icon_big,vendor,vendor_code,vendor_homepage
                                                      FROM udger_client_os_relation
                                                      JOIN udger_os_list ON udger_os_list.id=udger_client_os_relation.os_id
                                                      WHERE client_id=" . $client_id . " ");
                        if ($r = $q->fetchArray(SQLITE3_ASSOC)) {
                            $os_id = $r['os_id'];
                            $ret['user_agent']['os']                        = $r['name'];
                            $ret['user_agent']['os_code']                   = $r['name_code'];
                            $ret['user_agent']['os_homepage']               = $r['homepage'];
                            $ret['user_agent']['os_icon']                   = $r['icon'];
                            $ret['user_agent']['os_icon_big']               = $r['icon_big'];
                            $ret['user_agent']['os_info_url']               = "https://udger.com/resources/ua-list/os-detail?os=" . $r['name'];
                            $ret['user_agent']['os_family']                 = $r['family'];
                            $ret['user_agent']['os_family_code']            = $r['family_code'];
                            $ret['user_agent']['os_family_vendor']          = $r['vendor'];
                            $ret['user_agent']['os_family_vendor_code']     = $r['vendor_code'];
                            $ret['user_agent']['os_family_vendor_homepage'] = $r['vendor_homepage'];
                        }
                    }
                    //device
                    $q = $this->dbdat->query("SELECT deviceclass_id,regstring,name,name_code,icon,icon_big
                                                  FROM udger_deviceclass_regex
                                                  JOIN udger_deviceclass_list ON udger_deviceclass_list.id=udger_deviceclass_regex.deviceclass_id
                                                  ORDER BY sequence ASC");

                    while ($r = $q->fetchArray(SQLITE3_ASSOC)) {
                        if (@preg_match($r["regstring"], $this->data['ua'], $result)) {
                            $deviceclass_id                             = $r['deviceclass_id'];
                            $ret['user_agent']['device_class']          = $r['name'];
                            $ret['user_agent']['device_class_code']     = $r['name_code'];
                            $ret['user_agent']['device_class_icon']     = $r['icon'];
                            $ret['user_agent']['device_class_icon_big'] = $r['icon_big'];
                            $ret['user_agent']['device_class_info_url'] = "https://udger.com/resources/ua-list/device-detail?device=" . $r['name'];
                            break;
                        }
                    }
                    if ($deviceclass_id == 0 AND $client_class_id != -1) {
                        $q = $this->dbdat->query("SELECT deviceclass_id,name,name_code,icon,icon_big 
                                                  FROM udger_deviceclass_list
                                                  JOIN udger_client_class ON udger_client_class.deviceclass_id=udger_deviceclass_list.id
                                                  WHERE udger_client_class.id=" . $client_class_id . " ");
                        if ($r = $q->fetchArray(SQLITE3_ASSOC)) {
                            $deviceclass_id = $r['deviceclass_id'];
                            $ret['user_agent']['device_class']          = $r['name'];
                            $ret['user_agent']['device_class_code']     = $r['name_code'];
                            $ret['user_agent']['device_class_icon']     = $r['icon'];
                            $ret['user_agent']['device_class_icon_big'] = $r['icon_big'];
                            $ret['user_agent']['device_class_info_url'] = "https://udger.com/resources/ua-list/device-detail?device=" . $r['name'];
                        }
                    }

                    // device marketname
                    if($ret['user_agent']['os_family_code']) { 
                        $q = $this->dbdat->query("SELECT id,regstring FROM udger_devicename_regex WHERE 
                                                  ((os_family_code='".$ret['user_agent']['os_family_code']."' AND os_code='-all-') 
                                                  OR 
                                                  (os_family_code='".$ret['user_agent']['os_family_code']."' AND os_code='".$ret['user_agent']['os_code']."'))
                                                  order by sequence");
                        while ($r = $q->fetchArray(SQLITE3_ASSOC)) {
                            @preg_match($r["regstring"],$this->data['ua'],$result);                        

                            if(array_key_exists(1, $result)) {
                                $qC=$this->dbdat->query("SELECT marketname,brand_code,brand,brand_url,icon,icon_big
                                                         FROM udger_devicename_list
                                                         JOIN udger_devicename_brand ON udger_devicename_brand.id=udger_devicename_list.brand_id 
                                                         WHERE regex_id=".$r["id"]." and code = '".\SQLite3::escapeString(trim($result[1]))."' COLLATE NOCASE  ");

                                if($rC = $qC->fetchArray(SQLITE3_ASSOC)) {
                                    $ret['user_agent']['device_marketname']       = $rC['marketname'];
                                    $ret['user_agent']['device_brand']            = $rC['brand'];
                                    $ret['user_agent']['device_brand_code']       = $rC['brand_code'];
                                    $ret['user_agent']['device_brand_homepage']   = $rC['brand_url'];
                                    $ret['user_agent']['device_brand_icon']       = $rC['icon'];
                                    $ret['user_agent']['device_brand_icon_big']   = $rC['icon_big'];
                                    $ret['user_agent']['device_brand_info_url']   = "https://udger.com/resources/ua-list/devices-brand-detail?brand=".$rC['brand_code'];                             

                                    break;
                                }                
                            }
                        }
                    }
                    if($this->cacheEnable) {
                        $this->setCache('UA', md5($this->data['ua']) , serialize($ret['user_agent']) );
                    }
                }
            }           
        }
        
        if($ret['user_agent']['ua_class_code'] != 'crawler') {
        
          $usedCache = false;
          if($this->cacheEnable) {
              $retCache = $this->getCache('CH', md5(json_encode($this->data) ) ); 
              if($retCache) {
                  $ret['user_agent'] = unserialize($retCache);
                  $usedCache = true;
              }
          }
          
          if(!$usedCache) {         
         
            $ret['user_agent']['sec_ch_ua']                   = $this->data['SecChUa'];
            $ret['user_agent']['sec_ch_ua_full_version_list'] = $this->data['SecChUaFullVersionList'];
            $ret['user_agent']['sec_ch_ua_mobile']            = $this->data['SecChUaMobile'];
            $ret['user_agent']['sec_ch_ua_full_version']      = $this->data['SecChUaFullVersion'];
            $ret['user_agent']['sec_ch_ua_platform']          = $this->data['SecChUaPlatform'];
            $ret['user_agent']['sec_ch_ua_platform_version']  = $this->data['SecChUaPlatformVersion'];
            $ret['user_agent']['sec_ch_ua_model']             = $this->data['SecChUaModel'];
     
            if(!$this->data['SecChUaMobile'] || $this->data['SecChUaMobile'] == "?0") { // if not, ie it's desktop
                $this->data['SecChUaMobile'] = 0;
            }
            else {
                $this->data['SecChUaMobile'] = 1;
            }       
        
            if ($this->data['SecChUaFullVersionList'] || $this->data['SecChUa']) {
               
                      if($this->data['SecChUaFullVersionList']) { 
                         $regstringSearch = $this->data['SecChUaFullVersionList'];
                      }
                      else {
                         $regstringSearch = $this->data['SecChUa'];
                      }  
                      
                      $q = $this->dbdat->query("SELECT class_id,client_id,regstring,name,name_code,homepage,icon,icon_big,engine,vendor,vendor_code,vendor_homepage,uptodate_current_version,client_classification,client_classification_code
                                                    FROM udger_client_ch_regex
                                                    JOIN udger_client_list ON udger_client_list.id=udger_client_ch_regex.client_id
                                                    JOIN udger_client_class ON udger_client_class.id=udger_client_list.class_id
                                                    WHERE udger_client_ch_regex.mobile='".$this->data['SecChUaMobile']."'
                                                    ORDER BY sequence ASC");
    
    
    
                      while ($r=$q->fetchArray(SQLITE3_ASSOC)) {
  
    
                          if (@preg_match($r["regstring"],$regstringSearch,$result)) { 

                              $ver = $result[1];
                              if($this->data['SecChUaFullVersionList']) { 
                                 $ver_major = explode(".", $ver);
                                 $ver_major = $ver_major[0];
                              }
                              else {                             
                                 $ver_major = $ver;
                                 if($this->data['SecChUaFullVersion']) {
                                   $ver = trim($this->data['SecChUaFullVersion'], '"');
                                 }
                              }                                 
                              $client_id                                        = $r['client_id'];
                              $client_class_id                                  = $r['class_id'];
                              $ret['user_agent']['ua_class']                    = $r['client_classification'];
                              $ret['user_agent']['ua_class_code']               = $r['client_classification_code'];
                              $ret['user_agent']['ua']                          = $r['name']." ".$result[1];                    
                              $ret['user_agent']['ua_version']                  = $ver;
                              $ret['user_agent']['ua_version_major']            = $ver_major;
                              $ret['user_agent']['ua_uptodate_current_version'] = $r['uptodate_current_version'];
                              $ret['user_agent']['ua_family']                   = $r['name'];
                              $ret['user_agent']['ua_family_code']              = $r['name_code'];
                              $ret['user_agent']['ua_family_homepage']          = $r['homepage'];
                              $ret['user_agent']['ua_family_vendor']            = $r['vendor'];
                              $ret['user_agent']['ua_family_vendor_code']       = $r['vendor_code'];
                              $ret['user_agent']['ua_family_vendor_homepage']   = $r['vendor_homepage'];
                              $ret['user_agent']['ua_family_icon']              = $r['icon'];
                              $ret['user_agent']['ua_family_icon_big']          = $r['icon_big'];
                              $ret['user_agent']['ua_family_info_url']          = "https://udger.com/resources/ua-list/browser-detail?browser=".$r['name'];
                              $ret['user_agent']['ua_engine']                   = $r['engine']; 
                              break;
                          }                
                        
                        
                        
                      }
                  
                 }
                  
                $q = $this->dbdat->query("SELECT os_id,regstring,family,family_code,name,name_code,homepage,icon,icon_big,vendor,vendor_code,vendor_homepage
                                              FROM udger_os_ch_regex
                                              JOIN udger_os_list ON udger_os_list.id=udger_os_ch_regex.os_id
                                              WHERE 
                                              (udger_os_ch_regex.version = '".trim($this->data['SecChUaPlatformVersion'], '"')."') OR (udger_os_ch_regex.version = '-all-')
                                              ORDER BY sequence ASC");
                while ($r=$q->fetchArray(SQLITE3_ASSOC)) {
    
                    if (@preg_match($r["regstring"],$this->data['SecChUaPlatform'],$result)) {

                        $os_id                                          = $r['os_id'];
                        $ret['user_agent']['os']                        = $r['name'];
                        $ret['user_agent']['os_code']                   = $r['name_code'];
                        $ret['user_agent']['os_homepage']               = $r['homepage'];
                        $ret['user_agent']['os_icon']                   = $r['icon'];
                        $ret['user_agent']['os_icon_big']               = $r['icon_big'];
                        $ret['user_agent']['os_info_url']               = "https://udger.com/resources/ua-list/os-detail?os=".$r['name'];
                        $ret['user_agent']['os_family']                 = $r['family'];
                        $ret['user_agent']['os_family_code']            = $r['family_code'];
                        $ret['user_agent']['os_family_vendor']          = $r['vendor'];
                        $ret['user_agent']['os_family_vendor_code']     = $r['vendor_code'];
                        $ret['user_agent']['os_family_vendor_homepage'] = $r['vendor_homepage'];
                        break;
                    }
                }
 
                if ($this->data['SecChUaModel'] AND $ret['user_agent']['os_family_code']) {
                    $q = $this->dbdat->query("SELECT id FROM udger_devicename_regex WHERE 
                                              ((os_family_code='".$ret['user_agent']['os_family_code']."' AND os_code='-all-') 
                                              OR 
                                              (os_family_code='".$ret['user_agent']['os_family_code']."' AND os_code='".$ret['user_agent']['os_code']."'))
                                              order by sequence
                                              LIMIT 0,1");
                    
                    if ($r=$q->fetchArray(SQLITE3_ASSOC)) {

                      $qc = $this->dbdat->query("SELECT marketname,brand_code,brand,brand_url,icon,icon_big,deviceclass_id
                                                    FROM udger_devicename_list
                                                    JOIN udger_devicename_brand ON udger_devicename_brand.id=udger_devicename_list.brand_id 
                                                    WHERE regex_id='".$r["id"]."' and code = '".trim($this->data['SecChUaModel'], '"')."' ");
                      
                      if($rc=$qc->fetchArray(SQLITE3_ASSOC)) {
                                                  
                          $ret['user_agent']['device_marketname']     = $rc['marketname'];
                          $ret['user_agent']['device_brand']          = $rc['brand'];
                          $ret['user_agent']['device_brand_code']     = $rc['brand_code'];
                          $ret['user_agent']['device_brand_homepage'] = $rc['brand_url'];
                          $ret['user_agent']['device_brand_icon']     = $rc['icon'];
                          $ret['user_agent']['device_brand_icon_big'] = $rc['icon_big'];
                          $ret['user_agent']['device_brand_info_url'] = "https://udger.com/resources/ua-list/devices-brand-detail?brand=".$rc['brand_code'];
                          
                          $q=$this->dbdat->query("SELECT name,name_code,icon,icon_big
                                                        FROM udger_deviceclass_list                              
                                                        WHERE id='".$rc['deviceclass_id']."'");
                          $r=$q->fetchArray(SQLITE3_ASSOC); 
                          $ret['user_agent']['device_class']          = $r['name'];
                          $ret['user_agent']['device_class_code']     = $r['name_code'];
                          $ret['user_agent']['device_class_icon']     = $r['icon'];
                          $ret['user_agent']['device_class_icon_big'] = $r['icon_big'];
                          $ret['user_agent']['device_class_info_url'] = "https://udger.com/resources/ua-list/device-detail?device=".$r['name'];

                      }
                    }
                }
        
                if(!$ret['user_agent']['device_class'] AND $ret['user_agent']['ua_class_code'] != '') {
                    $q = $this->dbdat->query("SELECT name,name_code,icon,icon_big
                                                  FROM udger_deviceclass_list
                                                  JOIN udger_deviceclass_ch ON udger_deviceclass_list.id=udger_deviceclass_ch.device_id
                                                  WHERE udger_deviceclass_ch.mobile = '".$this->data['SecChUaMobile']."'");
                    $r=$q->fetchArray(SQLITE3_ASSOC); 
                    $ret['user_agent']['device_class']          = $r['name'];
                    $ret['user_agent']['device_class_code']     = $r['name_code'];
                    $ret['user_agent']['device_class_icon']     = $r['icon'];
                    $ret['user_agent']['device_class_icon_big'] = $r['icon_big'];
                    $ret['user_agent']['device_class_info_url'] = "https://udger.com/resources/ua-list/device-detail?device=".$r['name'];
                }
                
              }
              if($this->cacheEnable) {
                  $this->setCache('CH', md5(json_encode($this->data)) , serialize($ret['user_agent']) );
              }
        } 
        $this->initData(); //Deleting data for parsing
        

        if (!empty($this->ip)) {
            $ret['ip_address']['ip'] = $this->ip;
            $ipver = $this->ipHelper->getIpVersion($this->ip);
            
            if ($ipver !== false) {
                if ($ipver === IPInterface::IPv6) {
                    $this->ip = inet_ntop(inet_pton($this->ip));
                }

                $ret['ip_address']['ip_ver'] = $ipver;
                $q = $this->dbdat->query("SELECT udger_crawler_list.id as botid,ip_last_seen,ip_hostname,ip_country,ip_city,ip_country_code,ip_classification,ip_classification_code,
                                          name,ver,ver_major,last_seen,respect_robotstxt,family,family_code,family_homepage,family_icon,vendor,vendor_code,vendor_homepage,crawler_classification,crawler_classification_code
                                          FROM udger_ip_list
                                          JOIN udger_ip_class ON udger_ip_class.id=udger_ip_list.class_id
                                          LEFT JOIN udger_crawler_list ON udger_crawler_list.id=udger_ip_list.crawler_id
                                          LEFT JOIN udger_crawler_class ON udger_crawler_class.id=udger_crawler_list.class_id
                                          WHERE ip='" . $this->ip . "' ORDER BY sequence");
                
                if ($r = $q->fetchArray(SQLITE3_ASSOC)) {
                    $ret['ip_address']['ip_classification'] = $r['ip_classification'];
                    $ret['ip_address']['ip_classification_code'] = $r['ip_classification_code'];
                    $ret['ip_address']['ip_last_seen'] = $r['ip_last_seen'];
                    $ret['ip_address']['ip_hostname'] = $r['ip_hostname'];
                    $ret['ip_address']['ip_country'] = $r['ip_country'];
                    $ret['ip_address']['ip_country_code'] = $r['ip_country_code'];
                    $ret['ip_address']['ip_city'] = $r['ip_city'];

                    $ret['ip_address']['crawler_name'] = $r['name'];
                    $ret['ip_address']['crawler_ver'] = $r['ver'];
                    $ret['ip_address']['crawler_ver_major'] = $r['ver_major'];
                    $ret['ip_address']['crawler_family'] = $r['family'];
                    $ret['ip_address']['crawler_family_code'] = $r['family_code'];
                    $ret['ip_address']['crawler_family_homepage'] = $r['family_homepage'];
                    $ret['ip_address']['crawler_family_vendor'] = $r['vendor'];
                    $ret['ip_address']['crawler_family_vendor_code'] = $r['vendor_code'];
                    $ret['ip_address']['crawler_family_vendor_homepage'] = $r['vendor_homepage'];
                    $ret['ip_address']['crawler_family_icon'] = $r['family_icon'];
                    if ($r['ip_classification_code'] == 'crawler') {
                        $ret['ip_address']['crawler_family_info_url'] = "https://udger.com/resources/ua-list/bot-detail?bot=" . $r['family'] . "#id" . $r['botid'];
                    }
                    $ret['ip_address']['crawler_last_seen'] = $r['last_seen'];
                    $ret['ip_address']['crawler_category'] = $r['crawler_classification'];
                    $ret['ip_address']['crawler_category_code'] = $r['crawler_classification_code'];
                    $ret['ip_address']['crawler_respect_robotstxt'] = $r['respect_robotstxt'];
                } else {
                    $ret['ip_address']['ip_classification'] = 'Unrecognized';
                    $ret['ip_address']['ip_classification_code'] = 'unrecognized';
                }
                
                if ($this->ipHelper->getIpVersion($ret['ip_address']['ip']) === IPInterface::IPv4) {
                    
                    $ipLong = $this->ipHelper->getIpLong($ret['ip_address']['ip']);
                    
                    $q = $this->dbdat->query("select name,name_code,homepage 
                                       FROM udger_datacenter_range
                                       JOIN udger_datacenter_list ON udger_datacenter_range.datacenter_id=udger_datacenter_list.id
                                       where iplong_from <= " . $ipLong . " AND iplong_to >= " . $ipLong . " ");
                    
                    if ($r = $q->fetchArray(SQLITE3_ASSOC)) {
                        $ret['ip_address']['datacenter_name'] = $r['name'];
                        $ret['ip_address']['datacenter_name_code'] = $r['name_code'];
                        $ret['ip_address']['datacenter_homepage'] = $r['homepage'];
                    }
                }                
                else if ($this->ipHelper->getIpVersion($ret['ip_address']['ip']) === IPInterface::IPv6) {
                    $ipInt = $this->ipHelper->getIp6array($ret['ip_address']['ip']);
                    $q = $this->dbdat->query("select name,name_code,homepage 
                                          FROM udger_datacenter_range6
                                          JOIN udger_datacenter_list ON udger_datacenter_range6.datacenter_id=udger_datacenter_list.id
                                          where 
                                          iplong_from0 <= ".$ipInt[0]." AND iplong_to0 >= ".$ipInt[0]." AND
                                          iplong_from1 <= ".$ipInt[1]." AND iplong_to1 >= ".$ipInt[1]." AND
                                          iplong_from2 <= ".$ipInt[2]." AND iplong_to2 >= ".$ipInt[2]." AND
                                          iplong_from3 <= ".$ipInt[3]." AND iplong_to3 >= ".$ipInt[3]." AND
                                          iplong_from4 <= ".$ipInt[4]." AND iplong_to4 >= ".$ipInt[4]." AND
                                          iplong_from5 <= ".$ipInt[5]." AND iplong_to5 >= ".$ipInt[5]." AND
                                          iplong_from6 <= ".$ipInt[6]." AND iplong_to6 >= ".$ipInt[6]." AND
                                          iplong_from7 <= ".$ipInt[7]." AND iplong_to7 >= ".$ipInt[7]." 
                                          ");
                                                  
                    if ($r = $q->fetchArray(SQLITE3_ASSOC)) {
                        $ret['ip_address']['datacenter_name'] = $r['name'];
                        $ret['ip_address']['datacenter_name_code'] = $r['name_code'];
                        $ret['ip_address']['datacenter_homepage'] = $r['homepage'];
                    }
                }
            }

            $this->ip = '';
        }        
        return $ret;
    }


    /**
     * Set the useragent string
     * 
     * @param string
     * @return bool
     */
    public function setUA($ua)
    {
        $this->data['ua'] = $ua; 
        return true;
    }

    /**
     * Set the IP address
     * 
     * @param string
     * @return bool
     */
    public function setIP($ip)
    {
        $this->ip = $ip;
        return true;
    }

    /**
     * Set the http Headers
     * 
     * @param string
     * @return bool
     */
    public function setHeaders($headers)
    {
      $this->data = $this->headerHelper->parseHeaders($headers, $this->data);
      return true;
    }

    /**
     * Set the Sec-Ch-Ua
     * 
     * @param string
     * @return bool
     */
    public function setSecChUa($str)
    {
      $this->data['SecChUa'] = $str;
      return true;
    }

    /**
     * Set the Sec-Ch-Ua-Full-Version-List
     * 
     * @param string
     * @return bool
     */
    public function setSecChUaFullVersionList($str)
    {
      $this->data['SecChUaFullVersionList'] = $str;
      return true;
    }

    /**
     * Set the Sec-Ch-Ua-Mobile
     * 
     * @param string
     * @return bool
     */
    public function setSecChUaMobile($str)
    {
      $this->data['SecChUaMobile'] = $str;
      return true;
    }

    /**
     * Set the Sec-Ch-Ua-Full-Version
     * 
     * @param string
     * @return bool
     */
    public function setSecChUaFullVersion($str)
    {
      $this->data['SecChUaFullVersion'] = $str;
      return true;
    }

    /**
     * Set the Sec-Ch-Ua-Platform
     * 
     * @param string
     * @return bool
     */
    public function setSecChUaPlatform($str)
    {
      $this->data['SecChUaPlatform'] = $str;
      return true;
    }
    
    /**
     * Set the Sec-Ch-Ua-Platform-Version
     * 
     * @param string
     * @return bool
     */
    public function setSecChUaPlatformVersion($str)
    {
      $this->data['SecChUaPlatformVersion'] = $str;
      return true;
    }

    /**
     * Set the Sec-Ch-Ua-Model
     * 
     * @param string
     * @return bool
     */
    public function setSecChUaModel($str)
    {
      $this->data['SecChUaModel'] = $str;
      return true;
    }    
    
    /**
     * Create an empty data to parse
     */
    protected function initData()
    {
        $this->data                             = array();
        $this->data['ua']                       = '';
        $this->data['SecChUa']                  = '';
        $this->data['SecChUaFullVersionList']   = '';
        $this->data['SecChUaMobile']            = '';
        $this->data['SecChUaFullVersion']       = '';
        $this->data['SecChUaPlatform']          = '';
        $this->data['SecChUaPlatformVersion']   = '';
        $this->data['SecChUaModel']             = '';
    }
    
    /**
     * Open DB file 
     */
    protected function setDBdat()
    {
        if (is_null($this->dbdat)) {
            $this->dbdat = new \SQLite3($this->path, SQLITE3_OPEN_READONLY);
        }
    }
    
    /**
     * LRU cashe set 
     */
    protected function setCache($type, $key, $value) {
        if ($type == 'UA') {
          $this->cacheUA[$key] = $value;
          if (count($this->cacheUA) > $this->cacheSize) {
              array_shift($this->cacheUA);
          }
        }
        else if ($type == 'CH') {
          $this->cacheCH[$key] = $value;
          if (count($this->cacheCH) > $this->cacheSize) {
              array_shift($this->cacheCH);
          }
        }

    }
    
    /**
     * LRU cashe get 
     */
    protected function getCache($type, $key) {
        if ($type == 'UA') {
          if ( ! isset($this->cacheUA[$key])) {
              return null;
          }
          $tmpValue = $this->cacheUA[$key];
          unset($this->cacheUA[$key]);
          $this->cacheUA[$key] = $tmpValue;
          return $tmpValue;
        }
        else if ($type == 'CH') {
          if ( ! isset($this->cacheCH[$key])) {
              return null;
          }
          $tmpValue = $this->cacheCH[$key];
          unset($this->cacheCH[$key]);
          $this->cacheCH[$key] = $tmpValue;
          return $tmpValue;
        }

    }   
    
    /**
     * Set LRU cache enable/disable
     * 
     * @param bool
     * @return bool
     */
    public function setCacheEnable($set)
    {
        $this->cacheEnable = $set;
        return true;
    }
    
    /**
     * Set LRU cache size
     * 
     * @param Int
     * @return bool
     */
    public function setCacheSize($size)
    {
        $this->cacheSize = $size;
        return true;
    }

    /**
     * Clear LRU cache 
     * 
     * @return bool
     */
    public function clearCache()
    {
        $this->cacheUA = array();
        $this->cacheCH = array();
        return true;
    }
    
    /**
     * Set path to sqlite file
     * 
     * @param string
     * @return bool
     */
    public function setDataFile($path)
    {
        if (false === file_exists($path)) {
            throw new \Exception(sprintf("%s does not exist", $path));
        }   
        
        $this->path = $path;
        
        return true;
    }

}
