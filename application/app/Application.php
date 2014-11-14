<?php

namespace Alcohol;

use Alcohol\PasteBundle\PasteBundle;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\MonologBundle\MonologBundle;
use Symfony\Bundle\SecurityBundle\SecurityBundle;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\Debug\Debug;

class Application extends Kernel
{
    /** @var string */
    protected $name = 'Pastebin';

    /**
     * Constructor.
     *
     * @param string  $environment The environment
     * @param bool    $debug       Whether to enable debugging or not
     * @api
     */
    public function __construct($environment, $debug)
    {
        parent::__construct($environment, $debug);

        if ($this->isDebug() && in_array($environment, ['dev', 'test'])) {
            Debug::enable();
        }
    }

    /**
     * Returns an array of bundles to register.
     *
     * @return \Symfony\Component\HttpKernel\Bundle\BundleInterface[] An array of bundle instances.
     * @api
     */
    public function registerBundles()
    {
        return [
            /** Third party bundles */
            new FrameworkBundle(),
            new MonologBundle(),
            new SecurityBundle(),
            /** Everything in src/ */
            new PasteBundle(),
        ];
    }

    /**
     * @param LoaderInterface $loader
     * @api
     */
    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $loader->load(__DIR__ . '/config/config.yml');
    }

    /**
     * @return string
     * @api
     */
    public function getCacheDir()
    {
        return $this->rootDir . '/../var/cache/' . $this->environment;
    }

    /**
     * @return string
     * @api
     */
    public function getLogDir()
    {
        return $this->rootDir . '/../var/log/' . $this->environment;
    }
}
