<?php
namespace App\Services\AtomicTransaction\Exception;
use Exception;
/**
 * Excepciones
 */
class AtomicTransactionException extends Exception
{
    public function __construct($message, $code = 0, Exception $previous = null) {
        if (!is_null($previous)) {
            if ($previous instanceof AtomicTransactionException ) {
                $message = $previous->getMessage();
                $code = $previous->getCode();
            }
        }
        parent::__construct($message, $code, $previous);
        
    }

    public function __toString() {
        return __CLASS__ . ": [{$this->code}]: {$this->message}\n";
    }

}