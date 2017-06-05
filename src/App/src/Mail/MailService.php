<?php
/**
 * Created by PhpStorm.
 * User: anhhv
 * Date: 4/19/2017
 * Time: 4:53 PM
 */
namespace App\Mail;

use Zend\Mail\Message;
use Zend\Mime\Message as MimeMessage;
use Zend\Mime\Part as MimePart;
use Zend\Mail\Transport\Sendmail as SendmailTransport;
use Zend\Mail\Transport\Smtp as SmtpTransport;
use Zend\Mail\Transport\SmtpOptions;
use Zend\View\Model\ViewModel;
use Zend\View\Renderer\PhpRenderer;
use Zend\View\Resolver\AggregateResolver;
use Zend\View\Resolver\TemplatePathStack;

class MailService
{
    private $config;

    public function __construct($config)
    {
        $this->config = $config;
    }

    /**
     * Create a new email
     *
     * @var $data Default template variables
     */
    private function create($data = [])
    {
        return new Email($data);
    }

    /**
     * Send the constructed email
     *
     * @todo Add from name
     */
    private function send($email)
    {
        $message = $this->prepare($email);
        $config = $this->config['email'];
        //Send email
        if ($message && $config["active"]) {
            // Server SMTP config
            $transport = new SendmailTransport();
            // Relay SMTP
            if ($config["relay"]["active"]) {
                $transport = new SmtpTransport();
                $transportConfig = [
                    'name'              => "MyZend_Email",
                    'host'              => $config["relay"]["host"],
                    'connection_class'  => 'login',
                    'connection_config' => [
                        'username' => $config["relay"]["username"],
                        'password' => $config["relay"]["password"]
                    ]
                ];
                // Add port
                if ($config["relay"]["port"]) {
                    $transportConfig["port"] = $config["relay"]["port"];
                }
                // Add ssl
                if ($config["relay"]["ssl"]) {
                    $transportConfig["connection_config"]["ssl"] = $config["relay"]["ssl"];
                }
                $options   = new SmtpOptions($transportConfig);
                $transport->setOptions($options);
            }
            try {
            	return $transport->send($message);
            }catch(\Exception $e) {
                \LosMiddleware\LosLog\StaticLogger::save($e->getMessage());
            }
        }
    }

    /**
     * Return a preview of the email
     */
    public function preview($email)
    {
    }

    /**
     * Prepare email to send.
     */
    private function prepare($email)
    {
        $config = $this->config["email"];
        //Template Variables
        $templateVars = $config["template_vars"];
        $templateVars = array_merge($templateVars, $email->toArray());
        //If not layout, use default
        if (! $email->getLayoutName()) {
            $email->setLayoutName($config["defaults"]["layout_name"]);
        }

        //If not recipient, send to admin
        if (count($email->getTo()) == 0) {
            $email->addTo($config["emails"]["admin"]);
        }

        //If not sender, use default
        if (! $email->getFrom()) {
            $email->setFrom($config["defaults"]["from_email"]);
            $email->setFromName($config["defaults"]["from_name"]);
        }

        //Render system
        $renderer = new PhpRenderer();
        $resolver = new AggregateResolver();
        $stack = new TemplatePathStack();
        foreach ($config["template_path_stack"] as $path) {
            $stack->addPath($path);
        }
        
        $resolver->attach($stack);
        $renderer->setResolver($resolver);

        // Subject
        if (! $email->getSubject()) {
            $subjectView = $this->createView($templateVars, "subject", $email->getTemplateName());
            try {
                $email->setSubject($renderer->render($subjectView));
            } catch (\Exception $e) {
                $email->setSubject(null);
            }
        }
        
        // Text Content
        if (! $email->getTextContent()) {
            $textView = $this->createView($templateVars, "txt", $email->getTemplateName());
            $layoutTextView = new ViewModel($templateVars);
            $layoutTextView->setTemplate("/layout/txt/".$email->getLayoutName());
            try {
                $layoutTextView->setVariable("content", $renderer->render($textView));
                $email->setTextContent($renderer->render($layoutTextView));
            } catch (\Exception $e) {
                $email->setTextContent(null);
            }
        }

        // Html Content
        if (! $email->getHtmlContent()) {
            $htmlView = $this->createView($templateVars, "html", $email->getTemplateName());
            $layoutHtmlView = new ViewModel($templateVars);
            $layoutHtmlView->setTemplate("/layout/html/".$email->getLayoutName());
            try {
                $layoutHtmlView->setVariable("content", $renderer->render($htmlView));
                $email->setHtmlContent($renderer->render($layoutHtmlView));
            } catch (\Exception $e) {
                $email->setHtmlContent(null);
            }
        }

        //Create Zend Message
        $message = new Message();

        //From
        $message->setFrom($email->getFrom(), $email->getFromName());

        //Reply to
        if ($this->config['email']["defaults"]["reply_to"]) {
            $message->addReplyTo($this->config['email']["defaults"]["reply_to"], $this->config['email']["defaults"]["reply_to_name"]);
        }
        if ($email->getReplyTo()) {
            $message->addReplyTo($email->getReplyTo(), $email->getReplyToName());
        }

        //To recipients
        foreach ($email->getTo() as $emailAddress => $user) {
            if (is_object($user)) {
                if ($user->getMailOpt()) {
                    $message->addTo($emailAddress, $user->getFullName());
                }
            } else {
                $message->addTo($emailAddress, $user);
            }
        }

        //Cc recipients
        foreach ($email->getCc() as $emailAddress => $name) {
            if (is_object($user)) {
                if ($user->getMailOpt()) {
                    $message->addCc($emailAddress, $user->getFullName());
                }
            } else {
                $message->addCc($emailAddress, $user);
            }
        }

        //Bcc recipients
        foreach ($email->getBcc() as $emailAddress => $name) {
            if (is_object($user)) {
                if ($user->getMailOpt()) {
                    $message->addBcc($emailAddress, $user->getFullName());
                }
            } else {
                $message->addBcc($emailAddress, $user);
            }
        }

        //Subject
        if ($email->getSubject()) {
            $message->setSubject($email->getSubject());
        }

        // Body Multipart
        // Issue - not able to send TXT and HTML multibody
        // http://framework.zend.com/issues/browse/ZF2-196
        /*
		if($textContent) {
			$textContent = new MimePart($textContent);
			$textContent->type = "text/plain";
		}
        if($htmlContent) {
			$htmlContent = new MimePart($htmlContent);
			$htmlContent->type = "text/html";
        }
		$body = new MimeMessage();
		$body->setParts(array($textContent, $htmlContent));

        $message->setBody($body);
		$message->getHeaders()->get('content-type')->setType('multipart/alternative');
		*/

        //Body (Just html email right now)
        $htmlContent = new MimePart($email->getHtmlContent());
        $htmlContent->type = "text/html";

        $body = new MimeMessage();
        $body->setParts([$htmlContent]);

        $message->setBody($body);

        return $message;
    }

    public function createView($templateVars, $type, $template)
    {
        $view = new ViewModel($templateVars);
        if (! $template) {
            $template = $this->config['email']["defaults"]["template_name"];
        }
        $view->setTemplate("/".$type."/".$template);

        return $view;
    }

    public function sendAlertEmail($subject, $e)
    {
        $emailConfig = $this->config['email'];
        $message = "";
        $backtrace = "";
        if($e instanceof \Exception) {
            $message = "Error Code #" . $e->getCode() . " ==>> " . $e->getMessage();
            $backtrace = $e->getTraceAsString();
        }
        $mail = $this->create(
            array("subject" => $subject,
                'message' => $message,
                'serverInfo' => $_SERVER,
                'backtrace' => $backtrace,
                'cc' => $emailConfig['cc_emails']
            ));
        $mail->setTemplateName("system-alert");
        $this->send($mail);
    }

    public function sendEmail($email, $username, $code, $template = 'activation-email')
    {
        $mail = $this->create(
            array(
                'username' => $username,
                'email' => $email,
                'code' => $code,
                'subject' => '[568E] Verify your email address',
            ));
        $mail->addTo($email, $username);
        $mail->setTemplateName($template);
        $this->send($mail);
    }
}