<?php

declare(strict_types=1);

/*
 * (c) Rob Bast <rob.bast@gmail.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace Paste;

use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;

class Kernel extends BaseKernel
{
    use MicroKernelTrait;

    public const ENVIRONMENTS = ['test', 'dev', 'prod'];

    public function __construct(string $environment, bool $debug)
    {
        if (!\in_array($environment, self::ENVIRONMENTS, true)) {
            throw new \RuntimeException(sprintf('Unsupported environment "%s", expected one of: %s', $environment, implode(', ', self::ENVIRONMENTS)));
        }

        parent::__construct($environment, $debug);
    }

    public function isDevelopment(): bool
    {
        return 'dev' === $this->getEnvironment();
    }

    public function isTesting(): bool
    {
        return 'test' === $this->getEnvironment();
    }

    public function isProduction(): bool
    {
        return 'prod' === $this->getEnvironment();
    }
}
