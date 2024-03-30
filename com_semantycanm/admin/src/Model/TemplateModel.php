<?php

namespace Semantyca\Component\SemantycaNM\Administrator\Model;

use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Semantyca\Component\SemantycaNM\Administrator\DTO\TemplateDTO;
use Semantyca\Component\SemantycaNM\Administrator\Exception\RecordNotFoundException;


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
			->select('id, reg_date, name, content, wrapper')
			->from($db->quoteName('#__semantyca_nm_templates'))
			->where('name = ' . $db->quote($name));

		$db->setQuery($query);
		$row = $db->loadObject();

		if (empty($row))
		{
			throw new RecordNotFoundException(["The template has not been found"]);
		}

		$template          = new TemplateDTO();
		$template->id      = $row->id;
		$template->regDate = $row->reg_date;
		$template->content = $row->content;
		$template->name    = $row->name;
		$template->wrapper = $row->wrapper;

		$customFieldsQuery = $db->getQuery(true)
			->select('id, template_id, name, type, caption, default_value, is_available')
			->from($db->quoteName('#__semantyca_nm_custom_fields'))
			->where('template_id = ' . (int) $row->id);
		$db->setQuery($customFieldsQuery);
		$customFieldsRows = $db->loadObjectList();

		foreach ($customFieldsRows as $customFieldRow)
		{
			$decodedDefaultValue = json_decode($customFieldRow->default_value, true);
			$template->customFields[] = [
				'id'           => $customFieldRow->id,
				'name'         => $customFieldRow->name,
				'type'         => $customFieldRow->type,
				'caption'      => $customFieldRow->caption,
				'defaultValue' => $decodedDefaultValue,
				'isAvailable'  => $customFieldRow->is_available,
			];
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
		$db    = $this->getDatabase();
		$query = $db->getQuery(true);

		$fields     = [
			$db->quoteName('content') . ' = ' . $db->quote($messageContent['html']),
		];
		$conditions = [
			$db->quoteName('id') . ' = ' . (int) $id,
		];
		$query->update($db->quoteName('#__semantyca_nm_templates'))
			->set($fields)
			->where($conditions);
		$db->setQuery($query);
		$db->execute();

		$query = $db->getQuery(true)
			->delete($db->quoteName('#__semantyca_nm_custom_fields'))
			->where($db->quoteName('template_id') . ' = ' . (int) $id);
		$db->setQuery($query);
		$db->execute();

		foreach ($messageContent['customFields'] as $customField)
		{
			$query            = $db->getQuery(true);
			$columns          = ['template_id', 'name', 'type', 'caption', 'default_value', 'is_available'];
			$defaultValueJson = json_encode($customField['defaultValue']);
			$values           = [
				(int) $id,
				$db->quote($customField['name']),
				(int) $customField['type'],
				$db->quote($customField['caption']),
				$db->quote($defaultValueJson),
				(int) $customField['isAvailable'],
			];
			$query->insert($db->quoteName('#__semantyca_nm_custom_fields'))
				->columns($db->quoteName($columns))
				->values(implode(',', $values));
			$db->setQuery($query);
			$db->execute();
		}

		return true;
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
