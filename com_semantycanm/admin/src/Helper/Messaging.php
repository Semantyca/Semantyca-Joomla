<?php

namespace Semantyca\Component\SemantycaNM\Administrator\Helper;

use Exception;
use Joomla\CMS\Factory;
use Joomla\CMS\Log\Log;
use Joomla\CMS\Mail\MailerFactoryInterface;
use Semantyca\Component\SemantycaNM\Administrator\Model\MailingListModel;
use Semantyca\Component\SemantycaNM\Administrator\Model\StatModel;

class Messaging
{
	private $mailingListModel;
	private $statModel;

	public function __construct(MailingListModel $mailingListModel, StatModel $statModel)
	{
		$this->mailingListModel = $mailingListModel;
		$this->statModel        = $statModel;
	}

	public function sendEmail($body, $subject, $user_or_user_group)
	{
		$mailer      = Factory::getContainer()->get(MailerFactoryInterface::class)->createMailer();
		$mailer->setSubject($subject);
		$mailer->setBody($body);
		$mailer->isHtml(true);

		try
		{
			if (filter_var($user_or_user_group, FILTER_VALIDATE_EMAIL))
			{
				$mailer->addRecipient($user_or_user_group);
				$stat_rec_id = $this->statModel->addStatRecord($user_or_user_group, Constants::SENDING_ATTEMPT, 5, 6);
				$send        = $mailer->send();
				if ($send !== true)
				{
					Log::add('Error sending email to ' . $user_or_user_group, Log::WARNING, 'com_semantycanm');
					$this->statModel->updateStatRecord($stat_rec_id, Constants::MESSAGING_ERROR);
				}
				else
				{
					Log::add('Mail sent to ' . $user_or_user_group, Log::INFO, 'com_semantycanm');
					$this->statModel->updateStatRecord($stat_rec_id, Constants::HAS_BEEN_SENT);
				}
			}
			else
			{
				$subscribers = $this->mailingListModel->getSubscribers($user_or_user_group);
				foreach ($subscribers as $subscriber)
				{
					$mailer->addRecipient($subscriber->email);
					$stat_rec_id = $this->statModel->addStatRecord($user_or_user_group, Constants::SENDING_ATTEMPT, 5, 6);
					$send = $mailer->send();
					if ($send !== true)
					{
						Log::add('Error sending email to ' . $subscriber->email, Log::WARNING, 'com_semantycanm');
						$this->statModel->updateStatRecord($stat_rec_id, Constants::MESSAGING_ERROR);
						//TODO it needs to add the error message
					}
					else
					{
						Log::add('Mail sent to ' . $subscriber->email, Log::INFO, 'com_semantycanm');
						$this->statModel->updateStatRecord($stat_rec_id, Constants::HAS_BEEN_SENT);
					}
				}
			}
		}
		catch (Exception $e)
		{
			error_log($e);
			Log::add($e->getMessage(), Log::ERROR, 'com_semantycanm');
		}
	}
}



