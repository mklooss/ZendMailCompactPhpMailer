<?php

class PHPMailerZendCompact {
    /**
     * 
     * @var \PHPMailer\PHPMailer\PHPMailer
     */
    protected $mail = NULL;

    protected $files = array();
    protected $is_return_path_set = false;

    public function __construct() {
        require_once 'PHPMailer/src/Exception.php';
        require_once 'PHPMailer/src/PHPMailer.php';
        $this->mail = new \PHPMailer\PHPMailer\PHPMailer;
        $this->mail->isSendmail();
        $this->mail->CharSet = 'UTF-8';
        $this->mail->Encoding = 'base64';
        $this->mail->setLanguage('de');
    }

    /**
     * 
     * @param string $value
     * @return $this
     */
    public function setType($value) {
        return $this;
    }

    /**
     * 
     * @param string $name
     * @param string $value
     * @return $this
     */
    public function addHeader($name, $value = null)
    {
        $this->mail->addCustomHeader($name, $value);
        return $this;
    }

    /**
     * 
     * @param string $email
     * @param string $name
     * @return $this
     */
    public function addTo($email, $name = '')
    {
        $name = $this->removeUtf8Base64($name);
        $this->mail->addAddress($email, $name);
        return $this;
    }

    /**
     * 
     * @param string $bcc
     * @param string $name
     * @return $this
     */
    public function addBcc($bcc, $name = '')
    {
        $this->mail->addBCC($bcc);
        return $this;
    }

    /**
     * 
     * @param string $email
     * @return $this
     */
    public function setReturnPath($email)
    {
        $this->is_return_path_set = true;
        $this->mail->Sender = $email;
        return $this;
    }

    /**
     * 
     * @param string $email
     * @param string $name
     * @return $this
     */
    public function setReplyTo($email, $name = '')
    {
        $name = $this->removeUtf8Base64($name);
        $this->mail->addReplyTo($email, $name);
        return $this;
    }

    /**
     * 
     * @param string $text
     * @return $this
     */
    public function setBodyText($text)
    {
        $this->mail->Body = $text;
        return $this;
    }

    /**
     * 
     * @param string $message
     * @return $this
     */
    public function setBodyHTML($message)
    {
        $this->mail->msgHTML($message);
        return $this;
    }

    /**
     * 
     * @param string $value
     * @return $this
     */
    public function setSubject($value)
    {
        $value = $this->removeUtf8Base64($value);
        $this->mail->Subject = trim($value);
        return $this;
    }

    /**
     * 
     * @param string $email
     * @param string $name
     * @return $this
     */
    public function setFrom($email, $name = '')
    {
        $name = $this->removeUtf8Base64($name);
        $this->mail->setFrom($email, $name);
        return $this;
    }

    /**
     * 
     * @param Zend_Mail_Transport_Abstract $transport
     * @return $this
     * @throws Exception
     */
    public function send($transport = NULL)
    {
        $returnPathEmail = $this->getReturnPathFromDefaultTransport($transport);
        if ($returnPathEmail !== null)
        {
            $this->setReturnPath($returnPathEmail);
        }
        if (!$this->mail->send())
        {
            throw new Exception('Mailer Error: ' . $this->mail->ErrorInfo);
        }
        foreach ($this->files as $file)
        {
            @unlink($file);
        }
        return $this;
    }

    /**
     * 
     * @param string $body
     * @param string $mimeType
     * @param string $disposition
     * @param string $encoding
     * @param string $filename
     * @return $this
     */
    public function createAttachment($body,
                                     $mimeType    = \Zend_Mime::TYPE_OCTETSTREAM,
                                     $disposition = \Zend_Mime::DISPOSITION_ATTACHMENT,
                                     $encoding    = \Zend_Mime::ENCODING_BASE64,
                                     $filename    = null)
    {
        $path = tempnam();
        file_put_contents($path, $body);
        $this->mail->addAttachment($path, $filename, $encoding, $mimeType, $disposition);
        $this->files[] = $path;
        return $this;
    }

    /**
     * get Return Path by Zend_Mail Transport
     *  from Sendmail
     * 
     * @param \Zend_Mail_Transport_Abstract $transport
     * @return string|NULL
     */
    protected function getReturnPathFromDefaultTransport($transport = NULL)
    {
        $returnPathEmail = NULL;
        if (!$this->is_return_path_set)
        {
            if (is_null($transport))
            {
                $transport = \Zend_Mail::getDefaultTransport();
            }
            if (!is_null($transport) && $transport instanceof \Zend_Mail_Transport_Sendmail)
            {
                $parameters = array_filter((array) explode(' ', $transport->parameters));
                foreach ($parameters as $param)
                {
                    if (substr($param, 0, 2) === '-f')
                    {
                        $returnPathEmail = mb_substr($param, 2, NULL, 'UTF-8');
                        break;
                    }
                }
            }
        }
        return $returnPathEmail;
    }

    /**
     * Convert base64 Value for
     *   Zend_Mail back to plain
     * 
     * @param string $value
     * @return string
     */
    protected function removeUtf8Base64($value)
    {
        $value = trim($value);
        if (strstr($value, '=?utf-8?B?'))
        {
            $value = mb_substr($value, strlen('=?utf-8?B?'), NULL, 'UTF-8');
            $value = mb_substr($value, 0, -1 * strlen('?='), 'UTF-8');
            $value = base64_decode($value);
        }
        return $value;
    }
}
