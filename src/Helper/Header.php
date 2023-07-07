<?php

namespace Udger\Helper;

class Header implements HeaderInterface{

    /**
     * Get headers
     * 
     * @param string $headers, array $ret
     * @return array
     */
    public function parseHeaders($headers,$ret)
    {        
        
        foreach(preg_split("/((\r?\n)|(\r\n?))/", $headers) as $line){
            
            $header = explode(": ", $line);
            
            if(strtolower($header[0]) == 'sec-ch-ua') {
               $ret['SecChUa'] = $header[1];
            }
            else if(strtolower($header[0]) == 'sec-ch-ua-full-version-list') {
               $ret['SecChUaFullVersionList'] = $header[1];
            }
            else if(strtolower($header[0]) == 'sec-ch-ua-mobile') {
               $ret['SecChUaMobile'] = $header[1]; 
            }
            else if(strtolower($header[0]) == 'sec-ch-ua-full-version') {
               $ret['SecChUaFullVersion'] = $header[1];
            }
            else if(strtolower($header[0]) == 'sec-ch-ua-platform') {
               $ret['SecChUaPlatform'] = $header[1];
            }
            else if(strtolower($header[0]) == 'sec-ch-ua-platform-version') {
               $ret['SecChUaPlatformVersion'] = $header[1];
            }
            else if(strtolower($header[0]) == 'sec-ch-ua-model') {
               $ret['SecChUaModel'] = $header[1];
            }
            else if(strtolower($header[0]) == 'user-agent') {
               //$ret['ua'] = $header[1];
               $ret['ua'] = str_replace($header[0].": ", "", $line);
            }
        } 
                
        return $ret;
    }
    

}
