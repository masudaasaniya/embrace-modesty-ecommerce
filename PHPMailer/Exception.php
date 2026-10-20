<?php
/**
 * PHPMailer Exception file
 * PHPMailer - A full-featured email creation and transfer class
 * @package PHPMailer
 */

namespace PHPMailer\PHPMailer;

/**
 * PHPMailer exception handler
 * 
 * @package PHPMailer
 */
class Exception extends \Exception
{
    /**
     * Prettify error message output
     * 
     * @return string
     */
    public function errorMessage()
    {
        $htmlMsg = htmlspecialchars($this->getMessage(), ENT_QUOTES, 'UTF-8');
        return $htmlMsg;
    }
}

