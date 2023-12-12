<?php

namespace Semantyca\Component\SemantycaNM\Administrator\Controller;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Log\Log;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Response\JsonResponse;
use Semantyca\Component\SemantycaNM\Administrator\DTO\ResponseDTO;
use Semantyca\Component\SemantycaNM\Administrator\Helper\Constants;
use Semantyca\Component\SemantycaNM\Administrator\Helper\Util;

class MailingListController extends BaseController
{
	public function add()
	{
		try
		{
			$app   = Factory::getApplication();
			$input = $app->input;

			if ($input->getMethod() === 'POST')
			{
				$mailing_lst_name   = $this->input->getString('mailinglistname');
				$mailing_lists      = $this->input->getString('mailinglists');
				$mailing_list_model = $this->getModel('MailingList');
				$user_group_model   = $this->getModel('UserGroup');
				$results            = $mailing_list_model->add($user_group_model, $mailing_lst_name, $mailing_lists);
				$responseDTO = new ResponseDTO(['success' => ['id' => $results]]);
			}
			else
			{
				Log::add("Only POST request allowed", Log::WARNING, Constants::COMPONENT_NAME);
			}
		}
		catch (\Exception $e)
		{
			$responseDTO = Util::getErrorDTO($e);
		}
		header('Content-Type: application/json; charset=UTF-8');
		echo new JsonResponse($responseDTO->toArray());
		Factory::getApplication()->close();
	}

	public function delete()
	{
		try
		{
			$app   = Factory::getApplication();
			$input = $app->input;

			if ($input->getMethod() === 'DELETE')
			{
				$ids_to_delete      = $this->input->getString('ids');
				$mailing_list_model = $this->getModel('MailingList');
				$results            = $mailing_list_model->remove($ids_to_delete);
				$responseDTO = new ResponseDTO(['success' => ['id' => $results]]);
			}
			else
			{
				Log::add("Only DELETE request allowed", Log::WARNING, Constants::COMPONENT_NAME);
			}
		}
		catch (\Exception $e)
		{
			$responseDTO = Util::getErrorDTO($e);
		}
		header('Content-Type: application/json; charset=UTF-8');
		echo new JsonResponse($responseDTO->toArray());
		Factory::getApplication()->close();
	}
}
