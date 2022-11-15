<?php

class PHPMailerZendCompact {
    /**
     * 
     * @var \PHPMailer\PHPMailer\PHPMailer
     */
    protected $mail = NULL;

    protected $files = array();

    public function __construct() {
        require_once 'PHPMailer/src/Exception.php';
        require_once 'PHPMailer/src/PHPMailer.php';
        $this->mail = new \PHPMailer\PHPMailer\PHPMailer;
        $this->mail->isSendmail();
        $this->mail->CharSet = 'UTF-8';
        $this->mail->Encoding = 'base64';
        $this->mail->setLanguage('de');
    }

    public function setType($value) {
        return $this;
    }

    public function addHeader($name, $value = null)
    {
        $this->mail->addCustomHeader($name, $value);
        return $this;
    }

    public function addTo($email, $name = '')
    {
        $this->mail->addAddress($email, $name);
        return $this;
    }

    public function addBcc($bcc)
    {
        $this->mail->addBCC($bcc);
        return $this;
    }

    public function setReturnPath($email)
    {
        $this->mail->Sender = $email;
        return $this;
    }

    public function setReplyTo($email, $name = '')
    {
        $this->mail->addReplyTo($address, $name);
        return $this;
    }

    public function setBodyText($text)
    {
        $this->mail->Body = $text;
        return $this;
    }

    public function setBodyHTML($message)
    {
        $this->mail->msgHTML($message);
        return $this;
    }

    public function setSubject($value)
    {
        if (strstr($value, '=?utf-8?B?'))
        {
            $value = mb_substr($value, strlen('=?utf-8?B?'), NULL, 'UTF-8');
            $value = mb_substr($value, 0, -1 * strlen('?='), 'UTF-8');
            $value = base64_decode($value);
        }
        $this->mail->Subject = trim($value);
        return $this;
    }

    public function setFrom($email, $name = '')
    {
        $this->mail->setFrom($email, $name);
        return $this;
    }

    public function send($transport = NULL)
    {
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
}
