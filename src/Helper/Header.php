<?php

namespace Udger\Helper;

class Header implements HeaderInterface{

    /**
     * Get IP verison
     * 
     * @param string $headers, array $ret
     * @return array
     */
    public function parseHeaders($headers, $ret)
    {

        foreach(preg_split("/((\r?\n)|(\r\n?))/", $headers) as $line){
            
            $header = explode(": ", $line);
            
            if(strtolower($header[0]) == 'sec-ch-ua') {
               $ret['SecChUa'] = trim($header[1], '"');
            }
            else if(strtolower($header[0]) == 'sec-ch-ua-full-version-list') {
               $ret['SecChUaFullVersionList'] = trim($header[1], '"');
            }
            else if(strtolower($header[0]) == 'sec-ch-ua-mobile') { // ?0 or ?1
               if ($header[1] == "?1") {
                    $ret['SecChUaMobile'] = "1";
               }
               else {
                    $ret['SecChUaMobile'] = "0";
               }     
            }
            else if(strtolower($header[0]) == 'sec-ch-ua-full-version') {
               $ret['SecChUaFullVersion'] = trim($header[1], '"');
            }
            else if(strtolower($header[0]) == 'sec-ch-ua-platform') {
               $ret['SecChUaPlatform'] = trim($header[1], '"');
            }
            else if(strtolower($header[0]) == 'sec-ch-ua-platform-version') {
               $ret['SecChUaPlatformVersion'] = trim($header[1], '"');
            }
            else if(strtolower($header[0]) == 'sec-ch-ua-model') {
               $ret['SecChUaModel'] = trim($header[1], '"');
            }
            else if(strtolower($header[0]) == 'user-agent') {
               $ret['ua'] = $header[1];
            }
        } 
        
        
        return $ret;
    }
    

}
