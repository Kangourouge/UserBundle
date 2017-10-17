<?php

namespace KRG\UserBundle\DependencyInjection;

use KRG\UserBundle\Controller\RegistrationController;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

/**
 * This is the class that loads and manages your bundle configuration.
 *
 * @link http://symfony.com/doc/current/cookbook/bundles/extension.html
 */
class KRGUserExtension extends Extension
{
    /**
     * @var ContainerBuilder
     */
    private $container;

    /**
     * {@inheritdoc}
     */
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
    }

    /**
     * Call a service setter for setting up value
     *
     * @param string $serviceName
     * @param string $setter
     * @param array $values
     * @return Definition|bool
     */
    public function callServiceSetter(string $serviceName, string $setter, array $values)
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
