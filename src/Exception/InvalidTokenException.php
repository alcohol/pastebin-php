<?php declare(strict_types=1);

/*
 * (c) Rob Bast <rob.bast@gmail.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace Paste\Exception;

use Paste\Exception;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class InvalidTokenException extends NotFoundHttpException implements Exception
{
    public function __construct(string $message = 'Invalid token.', \Exception $previous = null, int $code = 0)
    {
        parent::__construct($message, $previous, $code);
    }
}
