<?php

namespace Semantyca\Component\SemantycaNM\Administrator\Helper;

use Joomla\CMS\Factory;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;
use Semantyca\Component\SemantycaNM\Administrator\Exception\NewsletterSenderException;
use Semantyca\Component\SemantycaNM\Administrator\Model\NewsLetterModel;

class NewsletterSender
{
	private $mailer;
	private $config;
	private NewsLetterModel $newsletterModel;
	private const TRACKING_PIXEL_TEMPLATE = '<img src="%sindex.php?option=com_semantycanm&task=SiteSubscriberEvent.postEvent&id=%s" width="1" height="1" alt="" style="display:none;">';

	public function __construct($newsletterModel)
	{
		$this->newsletterModel  = $newsletterModel;
		//define('JPATH_BASE', realpath(dirname(__FILE__) . '/../../../../'));
		require_once JPATH_BASE . '/includes/defines.php';
		require_once JPATH_BASE . '/includes/framework.php';

		$this->config = Factory::getConfig();
		$this->mailer = new PHPMailer(true);
		$this->setupMailer();
	}

	private function setupMailer()
	{
		$this->mailer->isSMTP();
		$this->mailer->Host       = $this->config->get('smtphost');
		$this->mailer->SMTPAuth   = $this->config->get('smtpauth');
		$this->mailer->Username   = $this->config->get('smtpuser');
		$this->mailer->Password   = $this->config->get('smtppass');
		$this->mailer->SMTPSecure = $this->config->get('smtpsecure');
		$this->mailer->Port       = $this->config->get('smtpport');
		$this->mailer->setFrom($this->config->get('mailfrom'), $this->config->get('fromname'));
	}

	/**
	 * @throws NewsletterSenderException|Exception
	 * @since 1.0
	 */
	public function sendNewsletter($newsletterId, $baseURL): bool
	{
		$results = $this->newsletterModel->findUnprocessedEvent($newsletterId);
		if (empty($results))
		{
			throw new NewsletterSenderException(["No data found for newsletter ID " . $newsletterId]);
		}

		foreach ($results as $row)
		{
			if (!$this->sendEmail($row, $baseURL))
			{
				throw new NewsletterSenderException(["Failed to send to: " . $row->subscriber_email]);
			}
		}

		return true;
	}

	/**
	 * @throws Exception
	 * @since 1.0
	 */
	private function sendEmail($rowData, $baseURL): bool
	{
		$trackingPixel  = sprintf(self::TRACKING_PIXEL_TEMPLATE, $baseURL, urlencode($rowData->trigger_token));
		$customizedBody = str_replace('</body>', $trackingPixel . '</body>', $rowData->message_content);

		$this->mailer->addAddress($rowData->subscriber_email);
		$this->mailer->isHTML(true);
		$this->mailer->Subject = $rowData->subject;
		$this->mailer->Body    = $customizedBody;

		if (!$this->mailer->send())
		{
			$this->mailer->clearAllRecipients();

			return false;
		}
		$this->mailer->clearAllRecipients();

		return true;
	}
}

