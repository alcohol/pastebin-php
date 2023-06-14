<?php

declare(strict_types=1);

/*
 * (c) Rob Bast <rob.bast@gmail.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace Paste\Security;

use Symfony\Bundle\FrameworkBundle\Routing\Attribute\AsRoutingConditionService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

#[AsRoutingConditionService(alias: 'route_token_checker')]
final readonly class RouteTokenChecker
{
    public function __construct(
        private string $headerName = 'X-Paste-Token'
    ) {
    }

    public function check(Request $request): bool
    {
        if (false === $request->headers->has($this->headerName)) {
            throw new BadRequestHttpException(sprintf('Bad request, missing expected header "%s".', $this->headerName));
        }

        return true;
    }
}
