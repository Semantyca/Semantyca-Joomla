<?php

namespace unit\Model\Test;

require 'C:\Users\justa\PhpstormProjects\Semantyca-Joomla\com_semantycanm\admin\src\Model\MailingListModel.php';

use PHPUnit\Framework\TestCase;
use Semantyca\Component\SemantycaNM\Administrator\Model\MailingListModel;

class MailingListModelTest extends TestCase
{

	public function testAdd()
	{
		$mailingListModel = new MailingListModel();
		$mailingListModel->add('test@semantyca.com');

		$this->assertTrue($mailingListModel->contains('test@semantyca.com'));
	}
}
