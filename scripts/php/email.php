<?php
  /**************************************************
  CLASS: ##EMAIL

  Description:
  This class handles the creation and sending of all
  emails from any portion fo he site.

  Parameters:
  NA

  **************************************************/

  $e = new EMAIL();

  class EMAIL{

    var $mail;

    /**************************************************
    CONSTRUCTOR: ##EMAIL

    Description:
    Main class constructor

    GNDN

    **************************************************/
    function __construct(){}
    /*************************************************/





    /**************************************************
    METHOD: ##EMAIL_SENDMESSAGE

    Description:
    This method will take the private mail object and
    attempt to send a message using phpmailer

    Parameters:
    private var for mail

    Returns:
    result of sending message, success == 1

    **************************************************/
    function EMAIL_SENDMESSAGE(){
      $result = $this->mail->send();
      $this->mail->clearAddresses();
      return $result; // success = 1
    }
    /*************************************************/





    /**************************************************
    METHOD: ##EMAIL_SMTP

    Description:
    This method will set any required SMTP config
    options on the mail object to allow for secured
    mail sends through a specified gateway

    Parameters:
    NA

    Returns:
    NA

    **************************************************/
    function EMAIL_SMTP(){
      $this->mail->Host               = EMAIL_SMTPHOST;
      $this->mail->Port               = EMAIL_SMTPPORT;
      $this->mail->SMTPAuth           = EMAIL_SMTPAUTH;
      $this->mail->Username           = EMAIL_SMTUSER;
      $this->mail->Password           = EMAIL_SMTPPASS;
      $this->mail->SMTPSecure         = EMAIL_SMTPSECURE;
    }
    /*************************************************/





    /**************************************************
    METHOD: ##EMAIL_BUILDMESSAGE

    Description:
    This method will take in a number of parameters for
    things such as who to send to, the subject, etc.
    and build out a message to be sent

    Parameters:
    a = the to email address
    b = the subject
    c = the message to be sent as plain text to cover as many email clients as possible
    d = do we want to send as soon as we build successfully?

    Returns:
    true on success or the message value on failure, capture errors @ calling function

    **************************************************/
    function EMAIL_BUILDMESSAGE($a="",$b="",$c="",$d=true){
      require_once("phpmailer/PHPMailerAutoload.php");  // call in phpmailer class(es)
      $this->mail                         = new PHPMailer;
      $this->mail->SMTPDebug              = EMAIL_DEBUG;
      $this->mail->Debugoutput            = 'html';
      if(EMAIL_USESMTP){
        $this->EMAIL_SMTP();
          $this->mail->isSMTP();
      }
      $this->mail->From                   = EMAIL_FROMEMAIL;
      $this->mail->FromName               = EMAIL_FROMNAME;
      $this->mail->addReplyTo(EMAIL_FROMEMAIL,EMAIL_FROMNAME);
      $this->mail->isHTML(true);
      $this->mail->WordWrap               = 80;
      $this->mail->Subject                = $b;
      $this->mail->Body                   = str_replace("\n","<br />",strip_tags($c));
      $this->mail->AltBody                = $c;
      $this->mail->addAddress(trim($a));
      $result = $this->EMAIL_SENDMESSAGE();
      return $result;
    }
    /*************************************************/
  }
?>
