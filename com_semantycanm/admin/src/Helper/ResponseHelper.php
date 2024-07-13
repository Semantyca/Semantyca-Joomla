<?php
/**
 * @package     SemantycaNM
 * @subpackage  Administrator
 *
 * @copyright   Copyright (C) 2024 Semantyca. All rights reserved.
 * @license     GNU General Public License version 3 or later; see LICENSE.txt
 */

namespace Semantyca\Component\SemantycaNM\Administrator\Helper;

defined('_JEXEC') or die('Restricted access');

class ResponseHelper
{
	public static function success($data, $message = 'Processed with success')
	{
		return json_encode(array(
			'status'  => 'success',
			'data'    => $data,
			'message' => $message
		));
	}

	public static function error($message, $code = 400, $details = array())
	{
		return json_encode(array(
			'status'  => 'error',
			'message' => $message,
			'code'    => $code,
			'details' => $details
		));
	}
}
