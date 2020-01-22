<?php

namespace Gentor\Olx\Api;

use Exception;

/**
 * Class OlxException
 * @package Gentor\Olx\Api
 */
class OlxException extends Exception
{
    /** @var \stdClass $details */
    protected $details;

    /**
     * OlxException constructor.
     * @param string $message
     * @param int $code
     * @param \stdClass $details
     * @param Exception|null $previous
     */
    public function __construct($message = "", $code = 0, $details, Exception $previous = null)
    {
        $this->details = $details;
        parent::__construct($message, $code, $previous);
    }

    /**
     * @return Exception|\stdClass
     */
    public function getDetails()
    {
        return $this->details;
    }

    /**
     * @return string
     */
    public function getDetailsJson()
    {
        return json_encode($this->details, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    }

    /**
     * @return bool
     */
    public function hasMissingParams()
    {
        if (isset($this->details->error->details)) {
            $details = (array)$this->details->error->details;
            foreach ($details as $key => $detail) {
                if (false !== strpos($key, 'params')) {
                    return true;
                }
            }
        }

        return false;
    }
}