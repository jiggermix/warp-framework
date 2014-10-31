<?php

/**
 * Exception abstract
 * @description Base abstract for all exceptions
 */

namespace Warp\Utils\Abstracts;

use Warp\Utils\Interfaces;

abstract class CustomException extends \Exception implements IException
{
    protected $message = "Unknown exception";
    private   $string;                            
    protected $code = 0;                   
    protected $file;                  
    protected $line;
    private   $trace;                             

    public function __construct($message = null, $code = 0)
    {
        if (!$message) {
            throw new $this("Unknown ". get_class($this));
        }
        parent::__construct($message, $code);
    }
    
    public function __toString()
    {
        return get_class($this) . " '{$this->message}' in {$this->file}({$this->line})\n"
                                . "{$this->getTraceAsString()}";
    }
}

?>