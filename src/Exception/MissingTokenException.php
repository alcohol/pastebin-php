<?php declare(strict_types=1);

/*
 * (c) Rob Bast <rob.bast@gmail.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace Paste\Exception;

use Paste\Exception;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Throwable;

final class MissingTokenException extends BadRequestHttpException implements Exception
{
    public function __construct(string $message = 'Token header missing from request.', ?Throwable $previous = null, int $code = 0)
    {
        parent::__construct($message, $previous, $code);
    }
}
