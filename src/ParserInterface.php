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
    
    public function setDataFile($path);
}