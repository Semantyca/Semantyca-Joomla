<?php

namespace Semantyca\Component\SemantycaNM\Administrator\Helper;

use Joomla\CMS\Factory;
use Joomla\CMS\Mail\MailerFactoryInterface;
use Joomla\CMS\Uri\Uri;
use Semantyca\Component\SemantycaNM\Administrator\Exception\MessagingException;
use Semantyca\Component\SemantycaNM\Administrator\Exception\UpdateRecordException;
use Semantyca\Component\SemantycaNM\Administrator\Model\MailingListModel;
use Semantyca\Component\SemantycaNM\Administrator\Model\StatModel;

class Messaging
{
	private MailingListModel $mailingListModel;
	private StatModel $statModel;
	private string $baseURL;

	public function __construct(MailingListModel $mailingListModel, StatModel $statModel)
	{
		$this->mailingListModel = $mailingListModel;
		$this->statModel        = $statModel;
		$this->baseURL = Uri::root();
	}

	public function sendEmail($body, $subject, $user_or_user_group, $newsletter_id): bool
	{
		$mailer = Factory::getContainer()->get(MailerFactoryInterface::class)->createMailer();
		$mailer->setSubject($subject);
		$mailer->isHtml(true);

		if (filter_var($user_or_user_group, FILTER_VALIDATE_EMAIL))
		{
			$this->mailingListModel->upsertSubscriber($user_or_user_group, $user_or_user_group);

			return $this->sendMessage($mailer, $user_or_user_group, $body, $newsletter_id);
		}
		else
		{
			$result      = true;
			$subscribers = $this->mailingListModel->getSubscribers($user_or_user_group);

			if (!$this->sendMessage($mailer, $subscribers, $body, $newsletter_id))
			{
				$result = false;
			};
		}

		return $result;
	}

	/**
	 * @throws MessagingException
	 * @throws UpdateRecordException
	 * @since
	 */
	function sendMessage($mailer, $recipients, $body, $newsletter_id): bool
	{
		$mailer->addRecipient($recipients);
		$model = $this->statModel;

		$stat_rec_id = $model->createStatRecord($recipients, Constants::SENDING_ATTEMPT, $newsletter_id);

		if ($stat_rec_id != 0)
		{
			$body .= '<img src="' . $this->baseURL . '/index.php?option=com_semantycanm&task=service.postStat?id=' . urlencode($stat_rec_id) . '" width="1" height="1" alt="" style="display:none;">';
			$mailer->setBody($body);
			$send = $mailer->send();

			if ($send !== true)
			{
				$this->statModel->updateStatRecord($stat_rec_id, Constants::MESSAGING_ERROR);
				throw new MessagingException(['Sending of the message was failed']);
			}
		}
		else
		{
			$this->statModel->updateStatRecord($stat_rec_id, Constants::MESSAGING_ERROR);
			throw new MessagingException(['Error sending email to ' . $recipients . '. The stat has not not initiated correctly']);
		}
		$this->statModel->updateStatRecord($stat_rec_id, Constants::HAS_BEEN_SENT);

		return true;
	}
}



