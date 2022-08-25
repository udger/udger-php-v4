<?php

namespace Udger;


/**
 * Description of ParserFactory
 *
 * @author tiborb
 */
class ParserFactory {    
  
       
    /**
     * @var string $dataFile path to the data file
     */
    private $dataFile;
    
    /**
     * 
     * @param string $dataFile path to the data file
     */
    public function __construct($dataFile)
    {
        $this->dataFile = $dataFile;
    }

    /**
     * 
     * @return \Udger\Parser
     */
    public function getParser()
    {   
        $parser = new Parser(new Helper\IP());
        $parser->setDataFile($this->dataFile);
        return $parser;
    }
}
