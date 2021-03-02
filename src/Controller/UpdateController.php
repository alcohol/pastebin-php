<?php

declare(strict_types=1);

/*
 * (c) Rob Bast <rob.bast@gmail.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace Paste\Controller;

use Paste\Exception\NotFoundException;
use Paste\Exception\StorageException;
use Paste\Repository\PasteRepository;
use Paste\Security\HashGenerator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\ServiceUnavailableHttpException;

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
            throw new BadRequestHttpException(sprintf('Bad request, missing expected header "%s".', $this->tokenHeader));
        }

        try {
            $paste = $this->repository->find($id);
        } catch (NotFoundException $exception) {
            throw new NotFoundHttpException(sprintf('Paste "%s" not found.', $id), $exception);
        }

        if (false === hash_equals((string) $request->headers->get($this->tokenHeader), $this->generator->generateHash($id))) {
            throw new NotFoundHttpException(sprintf('Paste "%s" not found.', $id));
        }

        $paste = $paste->update($request->getContent());

        $ttl = null;
        if ($request->headers->has('X-Paste-Ttl')) {
            $ttl = (int) $request->headers->get('X-Paste-Ttl'); // @codeCoverageIgnore
        }

        try {
            $this->repository->persist($paste, $ttl);
        } catch (StorageException $exception) {
            throw new ServiceUnavailableHttpException(300, 'Storage unavailable.', $exception);
        }

        return new Response(null, Response::HTTP_NO_CONTENT);
    }
}
