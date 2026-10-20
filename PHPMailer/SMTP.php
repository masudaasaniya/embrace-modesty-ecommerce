<?php
/**
 * PHPMailer SMTP handler
 * PHPMailer - A full-featured email creation and transfer class
 * @package PHPMailer
 */

namespace PHPMailer\PHPMailer;

/**
 * PHPMailer SMTP class
 * 
 * @package PHPMailer
 */
class SMTP
{
    const CRLF = "\r\n";
    const LE = "\r\n";
    
    const DEFAULT_SECURE = 'tls'; // tls or ssl
    const DEFAULT_PORT = 587; // TLS port
    
    /**
     * The PHPMailer SMTP Version number
     * @var string
     */
    const VERSION = '6.8.0';

    /**
     * SMTP port
     * @var int
     */
    public $Port;

    /**
     * SMTP host
     * @var string
     */
    public $Host;

    /**
     * The connection to the SMTP server
     * @var resource|null
     */
    public $smtp_conn;

    /**
     * Error message
     * @var array
     */
    public $error = array();

    /**
     * The reply code of the last reply
     * @var int|null
     */
    public $last_reply_code;

    /**
     * Whether to use SMTP authentication
     * @var bool
     */
    public $SMTPAuth = false;

    /**
     * Whether to use TLS encryption
     * @var bool
     */
    public $SMTPSecure = 'tls';

    /**
     * Username for SMTP authentication
     * @var string
     */
    public $Username;

    /**
     * Password for SMTP authentication
     * @var string
     */
    public $Password;

    /**
     * Timeout for SMTP connection
     * @var int
     */
    public $Timeout = 15;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->Port = self::DEFAULT_PORT;
        $this->Host = '';
        $this->Username = '';
        $this->Password = '';
        $this->SMTPSecure = self::DEFAULT_SECURE;
    }

    /**
     * Connect to an SMTP server
     *
     * @param string $host    SMTP server IP or FQDN
     * @param int    $port    The port number to connect to
     * @param int    $timeout  How long to wait for the connection to open
     *
     * @return bool
     */
    public function Connect($host, $port = 0, $timeout = 0)
    {
        if ($port == 0) {
            $port = $this->Port;
        }
        if ($timeout == 0) {
            $timeout = $this->Timeout;
        }

        // Create socket connection
        $errno = 0;
        $errstr = '';
        
        if ($this->SMTPSecure == 'ssl') {
            $host = 'ssl://' . $host;
        }

        $this->smtp_conn = @fsockopen(
            $host,
            $port,
            $errno,
            $errstr,
            $timeout
        );

        if (!is_resource($this->smtp_conn)) {
            $this->error = array(
                'error' => "Failed to connect to server",
                'errno' => $errno,
                'errstr' => $errstr
            );
            return false;
        }

        // Get welcome message
        $announce = $this->get_lines();
        $this->last_reply_code = (int)substr($announce, 0, 3);

        // Send EHLO
        $this->SendCommand("EHLO localhost", "250");

        // Start TLS if needed
        if ($this->SMTPSecure == 'tls') {
            $this->SendCommand("STARTTLS", "220");
            
            // Enable crypto
            if (!stream_socket_enable_crypto(
                $this->smtp_conn,
                true,
                STREAM_CRYPTO_METHOD_TLS_CLIENT
            )) {
                $this->error = array('error' => 'TLS negotiation failed');
                return false;
            }

            // Send EHLO again after TLS
            $this->SendCommand("EHLO localhost", "250");
        }

        return true;
    }

    /**
     * Authenticate with the SMTP server
     *
     * @return bool
     */
    public function Authenticate()
    {
        // Send AUTH LOGIN command
        fputs($this->smtp_conn, "AUTH LOGIN" . self::CRLF);
        $response = $this->get_lines();
        
        // Check for 334 response (ready for username)
        if (substr($response, 0, 3) != '334') {
            $this->error = array('error' => 'AUTH LOGIN failed: ' . $response);
            return false;
        }
        
        // Send username (base64 encoded)
        fputs($this->smtp_conn, base64_encode($this->Username) . self::CRLF);
        $response = $this->get_lines();
        
        // Check for 334 response (ready for password)
        if (substr($response, 0, 3) != '334') {
            $this->error = array('error' => 'Username rejected: ' . $response);
            return false;
        }
        
        // Send password (base64 encoded)
        fputs($this->smtp_conn, base64_encode($this->Password) . self::CRLF);
        $response = $this->get_lines();
        
        // Check for 235 success
        if (substr($response, 0, 3) != '235') {
            $this->error = array('error' => 'Authentication failed: ' . $response);
            return false;
        }

        return true;
    }

    /**
     * Send an SMTP command
     *
     * @param string $command The command to send
     * @param string $expect  The expected response code
     *
     * @return bool
     */
    protected function SendCommand($command, $expect)
    {
        fputs($this->smtp_conn, $command . self::CRLF);
        $response = $this->get_lines();
        
        $this->last_reply_code = (int)substr($response, 0, 3);
        
        if ($expect != '' && substr($response, 0, 3) != $expect) {
            return false;
        }
        
        return true;
    }

    /**
     * Send mail
     *
     * @param string $from The address to send from
     * @param string $to   The address to send to
     * @param string $data The data to send
     *
     * @return bool
     */
    public function Mail($from, $to, $data)
    {
        // MAIL FROM
        if (!$this->SendCommand("MAIL FROM:<$from>", '250')) {
            return false;
        }

        // RCPT TO
        if (!$this->SendCommand("RCPT TO:<$to>", '250')) {
            return false;
        }

        // DATA
        if (!$this->SendCommand("DATA", '354')) {
            return false;
        }

        // Send message body
        fputs($this->smtp_conn, $data . self::CRLF);

        // Send end of message
        if (!$this->SendCommand("\r\n.", '250')) {
            return false;
        }

        return true;
    }

    /**
     * Send the QUIT command
     *
     * @return bool
     */
    public function Quit()
    {
        if (is_resource($this->smtp_conn)) {
            $this->SendCommand("QUIT", '221');
            fclose($this->smtp_conn);
        }
        return true;
    }

    /**
     * Get a line from the server
     *
     * @return string
     */
    protected function get_lines()
    {
        $data = '';
        $time = 0;
        
        while (!feof($this->smtp_conn)) {
            $line = fgets($this->smtp_conn, 515);
            $data .= $line;
            $time++;
            
            // Check for complete response (single line or multiline ending with space)
            if (substr($line, 3, 1) == ' ' || $time > 100) {
                break;
            }
        }
        
        return $data;
    }
}

