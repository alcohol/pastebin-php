<?php

/*
 * (c) Rob Bast <rob.bast@gmail.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace Alcohol\PasteBundle;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\DependencyInjection\Extension as BaseExtension;

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
