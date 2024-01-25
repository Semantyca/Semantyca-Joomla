<?php

namespace Semantyca\Component\SemantycaNM\Administrator\Model;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Uri\Uri;
use Semantyca\Component\SemantycaNM\Administrator\DTO\TemplateDTO;
use Semantyca\Component\SemantycaNM\Administrator\Exception\RecordNotFoundException;
use Semantyca\Component\SemantycaNM\Administrator\Helper\Constants;

class TemplateModel extends BaseDatabaseModel
{

	public function getList($currentPage, $itemsPerPage)
	{
		$db     = $this->getDatabase();
		$query  = $db->getQuery(true);
		$offset = ($currentPage - 1) * $itemsPerPage;

		$query
			->select($db->quoteName(array('id', 'name', 'reg_date')))
			->from($db->quoteName('#__semantyca_nm_templates'))
			->order('reg_date DESC')
			->setLimit($itemsPerPage, $offset);

		$db->setQuery($query);
		$templates = $db->loadObjectList();

		$queryCount = $db->getQuery(true)
			->select('COUNT(' . $db->quoteName('id') . ')')
			->from($db->quoteName('#__semantyca_nm_templates'));
		$db->setQuery($queryCount);
		$count   = $db->loadResult();
		$maxPage = (int) ceil($count / $itemsPerPage);

		return [
			'templates' => $templates,
			'count'     => $count,
			'current'   => $currentPage,
			'maxPage'   => $maxPage
		];
	}

	public function getTemplateByName($name): TemplateDTO
	{
		$db    = $this->getDatabase();
		$query = $db->getQuery(true)
			->select('id, reg_date, name, content, banner, max_articles, max_articles_short, wrapper')
			->from($db->quoteName('#__semantyca_nm_templates'))
			->where('name = ' . $db->quote($name));

		$db->setQuery($query);
		$row = $db->loadObject();

		if (empty($row))
		{
			throw new RecordNotFoundException(["The template has not been found"]);
		}

		$template                   = new TemplateDTO();
		$template->id               = $row->id;
		$template->regDate          = $row->reg_date;
		$template->content          = $row->content;
		$template->banner = $row->banner;
		$template->name             = $row->name;
		$template->maxArticles      = $row->max_articles;
		$template->maxArticlesShort = $row->max_articles_short;
		$template->wrapper          = $row->wrapper;

		if (empty($template->banner))
		{
			$params           = ComponentHelper::getParams(Constants::COMPONENT_NAME);
			$defaultBanner    = $params->get('default_banner', '');
			$imagePath        = explode('#', $defaultBanner)[0];
			$template->banner = Uri::root() . $imagePath;
		}


		return $template;
	}


	public function find($type, $name)
	{
		$db    = $this->getDatabase();
		$query = $db->getQuery(true);
		$query
			->select('*')
			->from($db->quoteName('#__semantyca_nm_templates'))
			->where('type = ' . (int) $type)
			->where('name = ' . (int) $name);
		$db->setQuery($query);

		return $db->loadObject();
	}

	public function createTemplate($name, $messageContent)
	{
		$db      = $this->getDatabase();
		$query   = $db->getQuery(true);
		$columns = array('name', 'message_content');
		$values  = array($db->quote($name), $db->quote($messageContent));
		$query
			->insert($db->quoteName('#__semantyca_nm_templates'))
			->columns($db->quoteName($columns))
			->values(implode(',', $values));
		$db->setQuery($query);
		$db->execute();

		return $db->insertid();
	}

	public function update($id, $messageContent)
	{
		$db         = $this->getDatabase();
		$query      = $db->getQuery(true);
		$fields     = array(
			$db->quoteName('content') . ' = ' . $db->quote($messageContent)
		);
		$conditions = array(
			$db->quoteName('id') . ' = ' . (int) $id
		);
		$query
			->update($db->quoteName('#__semantyca_nm_templates'))
			->set($fields)
			->where($conditions);
		$db->setQuery($query);

		return $db->execute();
	}

	public function deleteTemplate($id)
	{
		$db         = $this->getDatabase();
		$query      = $db->getQuery(true);
		$conditions = array(
			$db->quoteName('id') . ' = ' . (int) $id
		);
		$query
			->delete($db->quoteName('#__semantyca_nm_templates'))
			->where($conditions);
		$db->setQuery($query);

		return $db->execute();
	}


}
