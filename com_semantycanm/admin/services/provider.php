<?php
namespace Semantyca\Component;

use Joomla\CMS\Dispatcher\ComponentDispatcherFactoryInterface;
use Joomla\CMS\Extension\ComponentInterface;
use Joomla\CMS\Extension\MVCComponent;
use Joomla\CMS\Extension\Service\Provider\ComponentDispatcherFactory;
use Joomla\CMS\Extension\Service\Provider\MVCFactory;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;

return new class implements ServiceProviderInterface {

	public function register(Container $container): void {
		$container->registerServiceProvider(new MVCFactory('\\Semantyca\\Component\\SemantycaNM'));
		$container->registerServiceProvider(new ComponentDispatcherFactory('\\Semantyca\\Component\\SemantycaNM'));
		$container->set(
			ComponentInterface::class,
			function (Container $container) {
				$component = new MVCComponent($container->get(ComponentDispatcherFactoryInterface::class));
				$component->setMVCFactory($container->get(MVCFactoryInterface::class));
				return $component;
			}
		);
	}
};