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
	private MailingListModel $mailingListModel;
	private StatModel $statModel;
	private string $baseURL = "http://localhost/joomla";

	public function __construct(MailingListModel $mailingListModel, StatModel $statModel)
	{
		$this->mailingListModel = $mailingListModel;
		$this->statModel        = $statModel;
	}

	public function sendEmail($body, $subject, $user_or_user_group, $newsletter_id): bool
	{
		$mailer = Factory::getContainer()->get(MailerFactoryInterface::class)->createMailer();
		$mailer->setSubject($subject);
		$mailer->isHtml(true);

		try
		{
			if (filter_var($user_or_user_group, FILTER_VALIDATE_EMAIL))
			{
				$independentSubscriberId = $this->mailingListModel->upsertSubscriber($user_or_user_group, $user_or_user_group);
				return $this->sendMessage($mailer, $user_or_user_group, $body, $newsletter_id, $independentSubscriberId);
			}
			else
			{
				$result      = true;
				$subscribers = $this->mailingListModel->getSubscribers($user_or_user_group);
				foreach ($subscribers as $subscriber)
				{
					if (!$this->sendMessage($mailer, $subscriber, $body, $newsletter_id, $subscriber->id))
					{
						$result = false;
					};
				}
			}
			return $result;
		}
		catch (Exception $e)
		{
			error_log($e);
			Log::add($e->getMessage(), Log::ERROR, Constants::COMPONENT_NAME);
		}
		return false;
	}

	function sendMessage($mailer, $recipient, $body, $newsletter_id, $subscriber_id): bool
	{
		$mailer->addRecipient($recipient);
		$stat_rec_id = $this->statModel->addStat($recipient, Constants::SENDING_ATTEMPT, $newsletter_id, $subscriber_id);
		if ($stat_rec_id != 0)
		{
			$body .= '<img src="' . $this->baseURL . '/administrator/index.php?option=com_semantycanm&task=service.postStat?id=' . urlencode($stat_rec_id) . '" width="1" height="1" alt="" style="display:none;">';
			$mailer->setBody($body);
			$send = $mailer->send();
			if ($send === true)
			{
				Log::add('Mail sent to ' . $recipient, Log::INFO, Constants::COMPONENT_NAME);
				$this->statModel->updateStatRecord($stat_rec_id, Constants::HAS_BEEN_SENT);
				return true;
			}
			else
			{
				Log::add('Error sending email to ' . $recipient, Log::WARNING, Constants::COMPONENT_NAME);
				$this->statModel->updateStatRecord($stat_rec_id, Constants::MESSAGING_ERROR);
			}
		}
		else
		{
			Log::add('Error sending email to ' . $recipient . '. The stat has not not initiated correctly', Log::WARNING, Constants::COMPONENT_NAME);
			$this->statModel->updateStatRecord($stat_rec_id, Constants::MESSAGING_ERROR);
		}

		return false;
	}
}



