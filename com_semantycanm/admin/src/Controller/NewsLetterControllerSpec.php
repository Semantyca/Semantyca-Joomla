<?php

namespace spec\Semantyca\Component\SemantycaNM\Site\Controller;

use PhpSpec\ObjectBehavior;
use Joomla\CMS\MVC\View\HtmlView;

class NewsLetterControllerSpec extends ObjectBehavior
{
	function let()
	{
		$this->beAnInstanceOf('Semantyca\Component\SemantycaNM\Site\Controller\NewsLetterController');
	}

	function it_is_initializable()
	{
		$this->shouldHaveType('Semantyca\Component\SemantycaNM\Site\Controller\NewsLetterController');
	}

	function it_gets_the_mailing_list_and_user_group_data(
		\Joomla\CMS\MVC\Model\BaseDatabaseModel $mailingListModel,
		\Joomla\CMS\MVC\Model\BaseDatabaseModel $userGroupModel,
		HtmlView                                $view
	)
	{
		$mailingListData = ['mailing_list_data'];
		$userGroupData   = ['user_group_data'];

		$mailingListModel->getList()->willReturn($mailingListData);
		$userGroupModel->getList()->willReturn($userGroupData);

		$this->getModel('MailingList')->willReturn($mailingListModel);
		$this->getModel('UserGroup')->willReturn($userGroupModel);

		$this->getView('Dashboard', 'html')->willReturn($view);

		$view->set('data', $mailingListData)->shouldBeCalled();
		$view->set('user_groups', $userGroupData)->shouldBeCalled();
		$view->set('mailing_lists', $mailingListData)->shouldBeCalled();
		$view->display()->shouldBeCalled();

		$this->getList();
	}
}
