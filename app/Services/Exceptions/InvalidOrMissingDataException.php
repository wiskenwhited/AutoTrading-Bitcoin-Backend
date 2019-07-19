<?php

namespace App\Services\Exceptions;

use Exception;
use Throwable;

class InvalidOrMissingDataException extends Exception
{
    /**
     * @var array
     */
    private $data;

    public function __construct(array $data, $message = "", $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->data = $data;
    }

    /**
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }
}