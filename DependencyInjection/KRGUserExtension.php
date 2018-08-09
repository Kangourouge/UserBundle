<?php

namespace KRG\UserBundle\DependencyInjection;

use KRG\UserBundle\Controller\RegistrationController;
use KRG\UserBundle\Security\Firewall\AuthenticationSuccessHandler;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

class KRGUserExtension extends Extension
{
    /** @var ContainerBuilder */
    private $container;

    public function load(array $configs, ContainerBuilder $container)
    {
        $this->container = $container;
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');

        if (isset($config['registration']['confirmed_target_route'])) {
            $this->callServiceSetter(RegistrationController::class, 'setConfirmedTargetRoute', [
                $config['registration']['confirmed_target_route']
            ]);
        }

        if (isset($config['login']['admin_target_route'])) {
            $this->callServiceSetter(AuthenticationSuccessHandler::class, 'setAdminTargetRoute', [
                $config['login']['admin_target_route']
            ]);
        }

        if (isset($config['login']['user_target_route'])) {
            $this->callServiceSetter(AuthenticationSuccessHandler::class, 'setUserTargetRoute', [
                $config['login']['user_target_route']
            ]);
        }
    }

    /**
     * Call a service setter for setting up value
     *
     * @param       $serviceName
     * @param       $setter
     * @param array $values
     * @return bool|\Symfony\Component\DependencyInjection\Definition
     */
    public function callServiceSetter($serviceName, $setter, array $values)
    {
        if (null === $this->container) {
            return false;
        }

        $definition = $this->container->getDefinition($serviceName);
        if ($definition) {
            return $definition->addMethodCall($setter, $values);
        }

        return false;
    }
}
