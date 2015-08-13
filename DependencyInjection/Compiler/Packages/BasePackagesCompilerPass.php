<?php

namespace Rj\FrontendBundle\DependencyInjection\Compiler\Packages;

use Rj\FrontendBundle\DependencyInjection\Compiler\BaseCompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

abstract class BasePackagesCompilerPass extends BaseCompilerPass
{
    abstract protected function getTaggedPackages($container);
    abstract protected function getPackagesService($container);

    public function process(ContainerBuilder $container)
    {
        $packages = array();
        $registeredPackages = $this->getRegisteredPackages($container);

        foreach ($this->getTaggedPackages($container) as $id => $tags) {
            if (empty($tags) || !isset($tags[0]['alias'])) {
                throw new \LogicException(
                    "The tag for the service with id '$id' must define an 'alias' attribute"
                );
            }

            $packageName = $tags[0]['alias'];

            if (isset($registeredPackages[$packageName])) {
                throw new \LogicException(
                    "A package named '$packageName' has already been registered"
                );
            }

            if (isset($packages[$packageName])) {
                throw new \LogicException(
                    "Multiple packages were found with alias '$packageName'. Package alias' must be unique"
                );
            }

            $packages[$packageName] = $id;
        }

        $this->addPackages($packages, $container);
    }

    protected function addPackages($packages, $container)
    {
        $packagesService = $this->getPackagesService($container);

        foreach ($packages as $name => $id) {
            $packagesService->addMethodCall('addPackage', array($name, new Reference($id)));
        }
    }

    /**
     * Retrieve packages that have already been registered.
     *
     * @return array with the packages' name as key
     */
    protected function getRegisteredPackages($container)
    {
        $arguments = $this->getPackagesService($container)->getArguments();

        if (!isset($arguments[1]) || count($arguments[1]) < 2) {
            return array();
        }

        $packageName = $arguments[1][0];
        $package = $arguments[1][1];

        return array($packageName => $package);
    }
}
