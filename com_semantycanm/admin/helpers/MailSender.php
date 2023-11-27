<?php

use Joomla\CMS\Mail\Mail;

class MailSender
{
	public static function sendEmail()
	{
		$app      = JFactory::getApplication();
		$config   = $app->get('mailfrom');
		$fromname = $app->get('fromname');

		$mail = new Mail();

		$sender = array(
			$config,
			$fromname
		);

		$mail->setSender($sender);
		$mail->addRecipient('recipient@example.com');
		$mail->setSubject('Your Subject');
		$mail->setBody('Your message');

		try
		{
			$send = $mail->send();

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



