<?php

namespace Alcohol\PasteBundle;

use Symfony\Component\HttpKernel\DependencyInjection\Extension as BaseExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class Extension extends BaseExtension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $config, ContainerBuilder $container)
    {
        $configuration = new Configuration($this->getAlias());

        $this->processConfiguration($configuration, $config);
    }

    /**
     * {@inheritdoc}
     */
    public function getAlias()
    {
        return 'paste';
    }

    /**
     * {@inheritdoc}
     */
    public function getConfiguration(array $config, ContainerBuilder $container)
    {
        return new Configuration($this->getAlias());
    }
}
