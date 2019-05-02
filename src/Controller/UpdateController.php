<?php declare(strict_types=1);

/*
 * (c) Rob Bast <rob.bast@gmail.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace Paste\Controller;

use Paste\Exception\InvalidTokenException;
use Paste\Exception\MissingTokenException;
use Paste\Exception\StorageException;
use Paste\Repository\PasteRepository;
use Paste\Security\HashGenerator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\ServiceUnavailableHttpException;

final class UpdateController
{
    /** @var PasteRepository */
    private $repository;
    /** @var HashGenerator */
    private $generator;

    public function __construct(PasteRepository $repository, HashGenerator $generator)
    {
        $this->repository = $repository;
        $this->generator = $generator;
    }

    public function __invoke(Request $request, string $id): Response
    {
        if (false === $request->headers->has('X-Paste-Token')) {
            throw new MissingTokenException();
        }

        try {
            $paste = $this->repository->find($id);
        } catch (StorageException $exception) {
            throw new NotFoundHttpException($exception->getMessage(), $exception);
        }

        if (false === hash_equals((string) $request->headers->get('X-Paste-Token'), $this->generator->generateHash($id))) {
            throw new InvalidTokenException();
        }

        $paste = $paste->update($request->getContent());

        $ttl = null;
        if ($request->headers->has('X-Paste-Ttl')) {
            $ttl = (int) $request->headers->get('X-Paste-Ttl'); // @codeCoverageIgnore
        }

        try {
            $this->repository->persist($paste, $ttl);
        // @codeCoverageIgnoreStart
        } catch (StorageException $exception) {
            throw new ServiceUnavailableHttpException(300, $exception->getMessage(), $exception);
        }
        // @codeCoverageIgnoreEnd

        return new Response('', 204);
    }
}
