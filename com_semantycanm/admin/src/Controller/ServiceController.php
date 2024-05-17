<?php

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
use Semantyca\Component\SemantycaNM\Administrator\Helper\Messaging;
use Semantyca\Component\SemantycaNM\Administrator\Helper\Util;
use Semantyca\Component\SemantycaNM\Administrator\Model\MailingListModel;
use Semantyca\Component\SemantycaNM\Administrator\Model\StatModel;
use Semantyca\Component\SemantycaNM\Administrator\Model\SubscriberEventModel;


class ServiceController extends BaseController
{
	public function sendEmailAsync()
	{
		header(Constants::JSON_CONTENT_TYPE);
		try
		{
			$jsonInput = file_get_contents('php://input');
			$data      = json_decode($jsonInput, true);

			$subject     = $data['subject'] ?? '';
			$user_group  = $data['user_group'] ?? '';
			$encodedBody = $data['encoded_body'] ?? '';

			$errors = [];

			if (empty($user_group))
			{
				$errors[] = 'User group is required';
			}

			if (empty($encodedBody))
			{
				$errors[] = 'Encoded body is required';
			}

			if (!empty($errors))
			{
				throw new ValidationErrorException($errors);
			}

			$newLetterModel   = $this->getModel('NewsLetter');
			$statModel        = $this->getModel('Stat');
			$eventModel       = $this->getModel('SubscriberEvent');
			$mailingListModel = $this->getModel('MailingList');

			$newsletterId = $newLetterModel->upsert($subject, $encodedBody);

			/** @var StatModel $statModel */
			/** @var SubscriberEventModel $eventModel */
			/** @var MailingListModel $mailingListModel */
			$messagingHelper = new Messaging($newLetterModel, $statModel, $eventModel, $mailingListModel);
			$result          = $messagingHelper->sendEmail($user_group, $newsletterId);
			if ($result)
			{
				echo new JsonResponse($newsletterId);
			}
			else
			{
				throw new MessagingException(['An error happened while sending the message']);
			}

		}
		catch (ValidationErrorException|MessagingException|NewsletterSenderException $e)
		{
			http_response_code(400);
			echo new JsonResponse([
				'message' => $e->getMessage(),
				'errors'  => $e->getErrors()
			], 'error', true);
		}
		catch (\Throwable $e)
		{
			http_response_code(500);
			$errorMessage = $e->getMessage();
			$errorData    = $e->getErrors();

			if ($errorData)
			{
				Log::add($errorMessage . ' | Additional details: ' . json_encode($errorData), Log::ERROR, Constants::COMPONENT_NAME);
			}
			else
			{
				Log::add($errorMessage, Log::ERROR, Constants::COMPONENT_NAME);
			}

			echo new JsonResponse([
				'message' => $errorMessage,
				'errors'  => $errorData
			], 'error', true);
		} finally
		{
			Factory::getApplication()->close();
		}

	}

	public function getSubject()
	{
		header(Constants::JSON_CONTENT_TYPE);
		try
		{
			$type = $this->input->get->get('type', '', 'RAW');
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
		catch
		(\Throwable $e)
		{
			Log::add($e->getMessage(), Log::ERROR, Constants::COMPONENT_NAME);
			echo new JsonResponse($e->getMessage(), 'error', true);
		} finally
		{
			Factory::getApplication()->close();
		}
	}
}
