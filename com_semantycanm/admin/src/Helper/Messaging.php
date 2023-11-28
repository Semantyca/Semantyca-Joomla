<?php

namespace Semantyca\Component\SemantycaNM\Administrator\Helper;

use Joomla\CMS\Factory;
use Joomla\CMS\Mail\MailerFactoryInterface;

class Messaging
{
	public static function sendEmail($body, $subject, $user_group)
	{
		$mailer = Factory::getContainer()->get(MailerFactoryInterface::class)->createMailer();
		$mailer->setSubject($subject);
		$mailer->setBody($body);
		$mailer->addRecipient($user_group);

		try
		{
			$send = $mailer->send();
			if ($send !== true)
			{
				echo 'Error sending email: ' . $send->getMessage();
			}
			else
			{
				echo 'Mail sent!';
			}
		}
		catch (\PHPMailer\PHPMailer\Exception $e)
		{
			echo 'Error sending email: ' . $e->getMessage();
		}
	}
}



