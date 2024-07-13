<?php
/**
 * @package     SemantycaNM
 * @subpackage  Administrator
 *
 * @copyright   Copyright (C) 2024 Semantyca. All rights reserved.
 * @license     GNU General Public License version 3 or later; see LICENSE.txt
 */

namespace Semantyca\Component\SemantycaNM\Administrator\Controller;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Log\Log;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Response\JsonResponse;
use Semantyca\Component\SemantycaNM\Administrator\Exception\MessagingException;
use Semantyca\Component\SemantycaNM\Administrator\Exception\NewsletterSenderException;
use Semantyca\Component\SemantycaNM\Administrator\Exception\ValidationErrorException;
use Semantyca\Component\SemantycaNM\Administrator\Helper\Constants;
use Semantyca\Component\SemantycaNM\Administrator\Helper\LogHelper;
use Semantyca\Component\SemantycaNM\Administrator\Helper\Messaging;
use Semantyca\Component\SemantycaNM\Administrator\Helper\NewsletterValidator;
use Semantyca\Component\SemantycaNM\Administrator\Helper\ResponseHelper;
use Semantyca\Component\SemantycaNM\Administrator\Helper\Util;
use Semantyca\Component\SemantycaNM\Administrator\Model\MailingListModel;
use Semantyca\Component\SemantycaNM\Administrator\Model\NewslettersModel;
use Semantyca\Component\SemantycaNM\Administrator\Model\StatModel;
use Semantyca\Component\SemantycaNM\Administrator\Model\SubscriberEventModel;

class ServiceController extends BaseController
{
	public function sendEmailAsync(): void
	{
		header(Constants::JSON_CONTENT_TYPE);
		$app = Factory::getApplication();

		try
		{
			if ($_SERVER['REQUEST_METHOD'] !== 'POST')
			{
				http_response_code(405);
				echo ResponseHelper::error('methodNotAllowed', 'Method Not Allowed');
				$app->close();

				return;
			}

			$input         = json_decode(file_get_contents('php://input'), true);
			$newsletterDTO = NewsletterValidator::validateAndCreateDTO($input);
			/** @var NewslettersModel $newLetterModel */
			/** @var StatModel $statModel */
			/** @var SubscriberEventModel $eventModel */
			/** @var MailingListModel $mailingListModel */
			$newLetterModel   = $this->getModel('Newsletters');
			$statModel        = $this->getModel('Stat');
			$eventModel       = $this->getModel('SubscriberEvent');
			$mailingListModel = $this->getModel('MailingList');

			$messaging = new Messaging($newLetterModel, $statModel, $eventModel, $mailingListModel);
			$result    = $messaging->sendEmail($newsletterDTO, false);

			if ($result)
			{
				echo ResponseHelper::success($result);
			}
			else
			{
				throw new MessagingException(['An error occurred while sending the message']);
			}
		}
		catch (ValidationErrorException $e)
		{
			http_response_code(400);
			echo ResponseHelper::error('validationError', 400, $e->getErrors());
		}
		catch (MessagingException|NewsletterSenderException $e)
		{
			http_response_code(400);
			echo ResponseHelper::error('messagingError', 400, $e->getErrors());
		}
		catch (\Throwable $e)
		{
			http_response_code(500);
			LogHelper::logException($e, __CLASS__);
			echo ResponseHelper::error('error', 500,'An unexpected error occurred');
		} finally
		{
			$app->close();
		}
	}


	public function getSubject()
	{
		header(Constants::JSON_CONTENT_TYPE);
		$app = Factory::getApplication();

		try
		{
			$jsonInput = json_decode(file_get_contents('php://input'), true);
			$type      = $jsonInput['type'] ?? '';

			if ($type == 'random')
			{
				$util   = new Util();
				$result = $util->generateSubject();
			}
			else
			{
				$result = 'no subject';
			}
			echo new JsonResponse($result);
		}
		catch (\Throwable $e)
		{
			Log::add($e->getMessage(), Log::ERROR, Constants::COMPONENT_NAME);
			echo new JsonResponse($e->getMessage(), 'error', true);
		} finally
		{
			$app->close();
		}
	}
}
