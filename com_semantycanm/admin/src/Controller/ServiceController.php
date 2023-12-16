<?php

namespace Semantyca\Component\SemantycaNM\Administrator\Controller;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Log\Log;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Response\JsonResponse;
use ComSemantycanm\Admin\DTO\ResponseDTO;
use Semantyca\Component\SemantycaNM\Administrator\Helper\Constants;
use Semantyca\Component\SemantycaNM\Administrator\Helper\Messaging;
use Semantyca\Component\SemantycaNM\Administrator\Helper\Util;


class ServiceController extends BaseController
{
	public function sendEmail()
	{
		try
		{
			$subject     = $this->input->post->get('subject', '', 'RAW');
			$user_group  = $this->input->post->get('user_group', '', 'RAW');
			$encodedBody = $this->input->post->get('encoded_body', '', 'RAW');

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

			$mailingListModel = $this->getModel('MailingList');
			$statModel        = $this->getModel('Stat');
			$newLetterModel   = $this->getModel('NewsLetter');

			$newsLetterUpsertResults = $newLetterModel->upsert($subject, $encodedBody);

			$messagingHelper = new Messaging($mailingListModel, $statModel);
			$result          = $messagingHelper->sendEmail(urldecode($encodedBody), $subject, $user_group, $newsLetterUpsertResults);
			$responseDTO     = new ResponseDTO(['savingNewsLetter' => $result, 'sendEmail' => $result]);

		}
		catch (ValidationErrorException $e)
		{
			$responseDTO = new ResponseDTO(['validationError' => ['message' => $e->getErrors(), 'code' => $e->getCode()]]);

		}
		catch (\Exception $e)
		{
			$responseDTO = new ResponseDTO(['error' => ['message' => $e->getMessage(), 'code' => $e->getCode()]]);
		}
		header('Content-Type: application/json; charset=UTF-8');
		echo new JsonResponse($responseDTO->toArray());
		Factory::getApplication()->close();
	}

	public function postStat()
	{
		try
		{
			$id = $this->input->post->get('id', '', 'RAW');

			$statModel = $this->getModel('Stat');
			$statModel->updateStatRecord($id, Constants::HAS_BEEN_READ);
			header("Content-type: image/png");
			echo base64_decode("iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/wcAAwAB/wHtRbk7AAA=");
			Factory::getApplication()->close();

		}
		catch
		(\Exception $e)
		{
			error_log($e);
			Log::add($e->getMessage(), Log::ERROR, Constants::COMPONENT_NAME);
		}
	}

	public function getSubject()
	{
		try
		{
			$type = $this->input->get->get('type', '', 'RAW');
			if ($type == 'random')
			{
				$util        = new Util();
				$result      = $util->generateSubject();
			} else {
				$result = 'no subject';
			}
			$responseDTO = new ResponseDTO(['subject' => $result]);
		}
		catch
		(\Exception $e)
		{
			$responseDTO = new ResponseDTO(['error' => ['message' => $e->getMessage(), 'code' => $e->getCode()]]);
		}

		header('Content-Type: application/json; charset=UTF-8');
		echo new JsonResponse($responseDTO->toArray());
		Factory::getApplication()->close();
	}
}
