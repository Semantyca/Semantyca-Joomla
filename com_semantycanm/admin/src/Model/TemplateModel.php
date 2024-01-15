<?php

namespace Semantyca\Component\SemantycaNM\Administrator\Model;

use Joomla\CMS\MVC\Model\BaseDatabaseModel;

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

	public function getTemplate($id)
	{
		$db    = $this->getDatabase();
		$query = $db->getQuery(true);
		$query
			->select('*')
			->from($db->quoteName('#__semantyca_nm_templates'))
			->where('id = ' . (int) $id);
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

	public function updateTemplate($id, $name, $messageContent)
	{
		$db         = $this->getDatabase();
		$query      = $db->getQuery(true);
		$fields     = array(
			$db->quoteName('name') . ' = ' . $db->quote($name),
			$db->quoteName('message_content') . ' = ' . $db->quote($messageContent)
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
