<?php

namespace Semantyca\Component\SemantycaNM\Site\Model;

use Exception;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Semantyca\Component\SemantycaNM\Site\Helper\SiteConsts;

class SiteStatModel extends BaseDatabaseModel
{
	public function updateOpens($id)
	{
		$db     = $this->getDatabase();
		$query  = $db->getQuery(true);
		$fields = array(
			$db->quoteName('status') . ' = ' . $db->quote(SiteConsts::HAS_BEEN_READ),
			$db->quoteName('opens') . ' = ' . $db->quoteName('opens') . ' + 1'
		);

		$query->update($db->quoteName('#__semantyca_nm_stats'))
			->set($fields)
			->where($db->quoteName('id') . ' = ' . $db->quote($id));

		$db->setQuery($query);
		$result = $db->execute();
		if (!$result)
		{
			throw new Exception('Failed to update stat record');
		}

		return true;
	}


}
