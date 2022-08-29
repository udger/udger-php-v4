<?php

namespace Udger;

/**
 *
 * @author tiborb
 */
interface ParserInterface {
    
    public function parse();
    
    
    public function setUA($ua);
    
    public function setIP($ip);
 

    public function setSecChUa($str); 
 
    public function setSecChUaFullVersionList($str);
    
    public function setSecChUaMobile($str); 
 
    public function setSecChUaFullVersion($str);

    public function setSecChUaPlatform($str); 
 
    public function setSecChUaPlatformVersion($str);
    
    public function setSecChUaModel($str);
        
        
    public function setHeaders($ip);
    
    
    public function setDataFile($path);
}