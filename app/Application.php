<?php

/*
 * (c) Rob Bast <rob.bast@gmail.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace Alcohol\PasteBundle;

use RuntimeException;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\MonologBundle\MonologBundle;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\Debug\Debug;
use Symfony\Component\HttpKernel\Kernel;

class Application extends Kernel
{
    /** @var string */
    protected $name = 'Pastebin';

    /**
     * @inheritDoc
     */
    public function __construct($environment, $debug)
    {
        if (!in_array($environment, ['test', 'dev', 'prod'], true)) {
            throw new RuntimeException('Unsupported environment: ' . $environment);
        }

        parent::__construct($environment, $debug);

        if ($this->isDebug()) {
            Debug::enable();
        }
    }

    /**
     * @inheritDoc
     */
    public function registerBundles()
    {
        return [
            /* 3rd party bundles */
            new FrameworkBundle(),
            new MonologBundle(),

            /* the application's "bundle" class */
            new Bundle(),
        ];
    }

    /**
     * @inheritDoc
     */
    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $config = sprintf('%s/config/%s.config.yml', __DIR__, $this->getEnvironment());

        if (!is_readable($config)) {
            throw new RuntimeException('Missing file: ' . $config);
        }

        $loader->load($config);
    }

    /**
     * @inheritDoc
     */
    public function getCacheDir()
    {
        return $this->rootDir . '/../var/cache/' . $this->environment;
    }

    /**
     * @inheritDoc
     */
    public function getLogDir()
    {
        return $this->rootDir . '/../var/log/' . $this->environment;
    }
}
