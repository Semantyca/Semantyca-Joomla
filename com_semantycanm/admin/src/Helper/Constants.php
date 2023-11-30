<?php

namespace Semantyca\Component\SemantycaNM\Administrator\Helper;

use JText;

class Constants
{
	const COMPONENT_NAME = "com_semantycanm";
	const MESSAGING_ERROR = -1;
	const UNDEFINED = 0;
	const SENDING_ATTEMPT = 1;
	const HAS_BEEN_SENT = 2;
	const HAS_BEEN_READ = 3;

	public static function getStatusText($status) {
		$status_dict = array(
			self::MESSAGING_ERROR => JText::_('MESSAGING_ERROR'),
			self::UNDEFINED => JText::_('UNDEFINED'),
			self::SENDING_ATTEMPT => JText::_('SENDING_ATTEMPT'),
			self::HAS_BEEN_SENT => JText::_('HAS_BEEN_SENT'),
			self::HAS_BEEN_READ => JText::_('HAS_BEEN_READ')
		);
		return $status_dict[$status];
	}
}
