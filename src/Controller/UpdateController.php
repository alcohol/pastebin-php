<?php declare(strict_types=1);

/*
 * (c) Rob Bast <rob.bast@gmail.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace Paste\Controller;

use Paste\Exception\StorageException;
use Paste\Repository\PasteRepository;
use Paste\Security\HashGenerator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class UpdateController
{
    private PasteRepository $repository;
    private HashGenerator $generator;
    private string $tokenHeader;

    public function __construct(PasteRepository $repository, HashGenerator $generator, string $tokenHeader = 'X-Paste-Token')
    {
        $this->repository = $repository;
        $this->generator = $generator;
        $this->tokenHeader = $tokenHeader;
    }

    public function __invoke(Request $request, string $id): Response
    {
        if (false === $request->headers->has($this->tokenHeader)) {
            return new Response(
                sprintf('Bad request, missing expected "%s" header.', $this->tokenHeader),
                Response::HTTP_BAD_REQUEST
            );
        }

        try {
            $paste = $this->repository->find($id);
        } catch (StorageException $exception) {
            return new Response(
                sprintf('Paste "%s" not found.', $id),
                Response::HTTP_NOT_FOUND
            );
        }

        if (false === hash_equals((string) $request->headers->get($this->tokenHeader), $this->generator->generateHash($id))) {
            return new Response(
                sprintf('Paste "%s" not found.', $id),
                Response::HTTP_NOT_FOUND
            );
        }

        $paste = $paste->update($request->getContent());

        $ttl = null;
        if ($request->headers->has('X-Paste-Ttl')) {
            $ttl = (int) $request->headers->get('X-Paste-Ttl'); // @codeCoverageIgnore
        }

        try {
            $this->repository->persist($paste, $ttl);
        } catch (StorageException $exception) {
            return new Response(
                $exception->getMessage(),
                Response::HTTP_SERVICE_UNAVAILABLE,
                ['Retry-After' => 300]
            );
        }

        return new Response('', Response::HTTP_NO_CONTENT);
    }
}
