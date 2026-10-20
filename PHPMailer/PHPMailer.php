<?php
/**
 * PHPMailer - PHP email creation and transport class
 * 
 * @package PHPMailer
 */

namespace PHPMailer\PHPMailer;

use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception as PHPMailerException;

/**
 * PHPMailer - PHP email creation and transport class
 */
class PHPMailer
{
    const CRLF = "\r\n";
    
    /**
     * Email priority
     * @var int
     */
    public $Priority = 3;

    /**
     * The character set of the message
     * @var string
     */
    public $CharSet = 'UTF-8';

    /**
     * The MIME Content-type of the message
     * @var string
     */
    public $ContentType = 'text/plain';

    /**
     * The message encoding
     * @var string
     */
    public $Encoding = '8bit';

    /**
     * The SMTP server hostname
     * @var string
     */
    public $Host = 'smtp.gmail.com';

    /**
     * The SMTP server port
     * @var int
     */
    public $Port = 587;

    /**
     * SMTP server timeout
     * @var int
     */
    public $Timeout = 15;

    /**
     * SMTP Class
     * @var SMTP
     */
    public $SMTP;

    /**
     * Whether to use SMTP authentication
     * @var bool
     */
    public $SMTPAuth = false;

    /**
     * SMTP secure type
     * @var string
     */
    public $SMTPSecure = 'tls';

    /**
     * The From email address
     * @var string
     */
    public $From = '';

    /**
     * The From name
     * @var string
     */
    public $FromName = '';

    /**
     * The sender email (Return-Path)
     * @var string
     */
    public $Sender = '';

    /**
     * The Email password
     * @var string
     */
    public $Password = '';

    /**
     * The SMTP username
     * @var string
     */
    public $Username = '';

    /**
     * The subject of the message
     * @var string
     */
    public $Subject = '';

    /**
     * The message body
     * @var string
     */
    public $Body = '';

    /**
     * The message AltBody
     * @var string
     */
    public $AltBody = '';

    /**
     * Word wrap
     * @var int
     */
    public $WordWrap = 0;

    /**
     * Whether to use MIME
     * @var bool
     */
    public $Mailer = 'smtp';

    /**
     * The message type
     * @var string
     */
    public $MessageType = '';

    /**
     * The array of To addresses
     * @var array
     */
    protected $to = array();

    /**
     * The array of CC addresses
     * @var array
     */
    protected $cc = array();

    /**
     * The array of BCC addresses
     * @var array
     */
    protected $bcc = array();

    /**
     * The array of ReplyTo addresses
     * @var array
     */
    protected $ReplyTo = array();

    /**
     * The array of attachments
     * @var array
     */
    protected $attachment = array();

    /**
     * The CustomHeaders
     * @var array
     */
    protected $CustomHeaders = array();

    /**
     * The most recent message error
     * @var string
     */
    public $ErrorInfo = '';

    /**
     * Constructor
     * 
     * @param bool $exceptions Should we throw external exceptions
     */
    public function __construct($exceptions = false)
    {
        $this->SMTP = new SMTP();
    }

    /**
     * Set the From and FromName
     * 
     * @param string $address
     * @param string $name
     * @param bool $auto
     */
    public function setFrom($address, $name = '', $auto = true)
    {
        $this->From = $address;
        $this->FromName = $name;
    }

    /**
     * Add a "To" address
     * 
     * @param string $address
     * @param string $name
     * 
     * @return bool
     */
    public function addAddress($address, $name = '')
    {
        $this->to[] = array($address, $name);
        return true;
    }

    /**
     * Add a "CC" address
     * 
     * @param string $address
     * @param string $name
     * 
     * @return bool
     */
    public function addCC($address, $name = '')
    {
        $this->cc[] = array($address, $name);
        return true;
    }

    /**
     * Add a "BCC" address
     * 
     * @param string $address
     * @param string $name
     * 
     * @return bool
     */
    public function addBCC($address, $name = '')
    {
        $this->bcc[] = array($address, $name);
        return true;
    }

    /**
     * Add a "ReplyTo" address
     * 
     * @param string $address
     * @param string $name
     * 
     * @return bool
     */
    public function addReplyTo($address, $name = '')
    {
        $this->ReplyTo[] = array($address, $name);
        return true;
    }

    /**
     * Set the subject of the message
     * 
     * @param string $subject
     * 
     * @return bool
     */
    public function Subject($subject)
    {
        $this->Subject = $subject;
        return true;
    }

    /**
     * Set the body of the message
     * 
     * @param string $body
     * @param string $altbody
     */
    public function Body($body, $altbody = '')
    {
        $this->Body = $body;
        if ($altbody != '') {
            $this->AltBody = $altbody;
        }
    }

    /**
     * Is SMTP connected?
     * @var bool
     */
    protected $connected = false;

    /**
     * Send via SMTP
     * 
     * @return bool
     */
    public function Send()
    {
        // Set the SMTP connection parameters
        $this->SMTP->Host = $this->Host;
        $this->SMTP->Port = $this->Port;
        $this->SMTP->Username = $this->Username;
        $this->SMTP->Password = $this->Password;
        $this->SMTP->SMTPAuth = $this->SMTPAuth;
        $this->SMTP->SMTPSecure = $this->SMTPSecure;
        $this->SMTP->Timeout = $this->Timeout;

        // Connect to the SMTP server
        if (!$this->SMTP->Connect($this->Host, $this->Port, $this->Timeout)) {
            $this->ErrorInfo = isset($this->SMTP->error['error']) ? $this->SMTP->error['error'] : 'Connection failed';
            return false;
        }

        // Authenticate if needed
        if ($this->SMTPAuth) {
            if (!$this->SMTP->Authenticate()) {
                $this->ErrorInfo = isset($this->SMTP->error['error']) ? $this->SMTP->error['error'] : 'Authentication failed';
                return false;
            }
        }

        // Build the headers
        $headers = '';
        $headers .= "From: " . $this->formatEmail($this->From, $this->FromName) . self::CRLF;
        
        if (count($this->ReplyTo) > 0) {
            foreach ($this->ReplyTo as $replyto) {
                $headers .= "Reply-To: " . $this->formatEmail($replyto[0], $replyto[1]) . self::CRLF;
            }
        }
        
        $headers .= "MIME-Version: 1.0" . self::CRLF;
        
        if ($this->ContentType == 'text/html') {
            $headers .= "Content-Type: text/html; charset=" . $this->CharSet . self::CRLF;
        } else {
            $headers .= "Content-Type: text/plain; charset=" . $this->CharSet . self::CRLF;
        }
        
        $headers .= "X-Mailer: PHPMailer " . SMTP::VERSION . self::CRLF;

        // Send to all recipients
        $failed_recipients = array();
        
        // To
        foreach ($this->to as $to_address) {
            if (!$this->SMTP->Mail($this->From, $to_address[0], $headers . self::CRLF . $this->Body)) {
                $failed_recipients[] = $to_address[0];
            }
        }
        
        // CC
        foreach ($this->cc as $cc_address) {
            if (!$this->SMTP->Mail($this->From, $cc_address[0], $headers . self::CRLF . $this->Body)) {
                $failed_recipients[] = $cc_address[0];
            }
        }
        
        // BCC
        foreach ($this->bcc as $bcc_address) {
            if (!$this->SMTP->Mail($this->From, $bcc_address[0], $headers . self::CRLF . $this->Body)) {
                $failed_recipients[] = $bcc_address[0];
            }
        }

        // Close connection
        $this->SMTP->Quit();

        return (count($failed_recipients) == 0);
    }

    /**
     * Format an email address
     * 
     * @param string $address
     * @param string $name
     * 
     * @return string
     */
    protected function formatEmail($address, $name = '')
    {
        if ($name == '') {
            return $address;
        }
        return $name . ' <' . $address . '>';
    }

    /**
     * Set email format to HTML
     */
    public function isHTML($ishtml = true)
    {
        if ($ishtml) {
            $this->ContentType = 'text/html';
        } else {
            $this->ContentType = 'text/plain';
        }
    }

    /**
     * Set mailer to use SMTP
     */
    public function isSMTP()
    {
        $this->Mailer = 'smtp';
    }

    /**
     * Get the instance
     * 
     * @return PHPMailer
     */
    public static function init()
    {
        return new self();
    }
}

