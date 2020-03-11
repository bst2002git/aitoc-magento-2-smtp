<?php

namespace Aitoc\Smtp\Plugin\Framework\Mail;

use Aitoc\Smtp\Model\Html2Text;
use Magento\Framework\Mail\Message as MailMessage;

class Message
{
    /**
     * @var \Aitoc\Smtp\Model\Config
     */
    private $config;

    public function __construct(
        \Aitoc\Smtp\Model\Config $config
    ) {
        $this->config = $config;
				error_log(__FILE__.'__construct = '."\n",3,'./tmp/error_log.log');
    }

    /**
     * @param MailMessage $subject
     * @return MailMessage
     */
    public function afterSetBody(MailMessage $subject)
    {
		error_log('afterSetBody= '."\n",3,'./tmp/error_log.log');
        if (!$this->config->plainEnabled()) {
            return $subject;
        }
				error_log('afterSetBody after plainEnabled()= '."\n",3,'./tmp/error_log.log');
        try {
            $body = $subject->getBody();
            if ($body instanceof \Zend\Mime\Message && !$body->isMultiPart()) {
                $reflection = new \ReflectionProperty(MailMessage::class, 'zendMessage');
                $reflection->setAccessible(true);
                /** @var \Zend\Mail\Message $zendMessage */
                $zendMessage = $reflection->getValue($subject);

                $plainContent = '';
                try {
                    $plainContent = Html2Text::convert($zendMessage->getBodyText());
										error_log('afterSetBody plainContent= '.print_r($plainContent,true)."\n",3,'./tmp/error_log.log');
                } catch (\Exception $e) {
                }

                $textPart = new \Zend\Mime\Part($plainContent);
                $textPart->setCharset($zendMessage->getEncoding());
                $textPart->setType(\Zend\Mime\Mime::TYPE_TEXT);
                $body->setParts(array_merge([$textPart], $body->getParts()));
                $zendMessage->setBody($body);
                $zendMessage->getHeaders()->get('content-type')->setType('multipart/alternative');
            }
        } catch (\Exception $e) {
        }

        return $subject;
    }

		public function afterSetBodyText(MailMessage $subject)
		{
			error_log('afterSetBodyText= '."\n",3,'./tmp/error_log.log');
		}
		public function afterSetBodyHtml(MailMessage $subject)
		{
			error_log('afterSetBodyHtml= '."\n",3,'./tmp/error_log.log');
		}
}
