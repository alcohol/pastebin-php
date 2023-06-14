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
use Paste\Repository\PasteRepository;
use Paste\Security\HashGenerator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\ServiceUnavailableHttpException;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/{id}', name: 'paste.update', methods: [Request::METHOD_PUT], condition: 'service("route_token_checker").check(request)', stateless: true)]
final readonly class UpdateController
{
    public function __construct(
        private PasteRepository $repository,
        private HashGenerator $generator,
        private string $tokenHeader = 'X-Paste-Token'
    ) {
    }

    public function __invoke(Request $request, string $id): Response
    {
        try {
            $paste = $this->repository->find($id);
        } catch (NotFoundException $exception) {
            throw new NotFoundHttpException(sprintf('Paste "%s" not found.', $id), $exception);
        }

        if (false === hash_equals((string) $request->headers->get($this->tokenHeader), $this->generator->generateHash($id))) {
            throw new NotFoundHttpException(sprintf('Paste "%s" not found.', $id));
        }

        $paste = $paste->withBody($request->getContent());

        $ttl = null;

        if ($request->headers->has('X-Paste-Ttl')) {
            $ttl = (int) $request->headers->get('X-Paste-Ttl'); // @codeCoverageIgnore
        }

        $retries = 3;

        do {
            if (0 === $retries--) {
                throw new ServiceUnavailableHttpException(300, 'Storage unavailable.');
            }
        } while (!$this->repository->update($paste, $ttl));

        return new Response('', Response::HTTP_NO_CONTENT);
    }
}
