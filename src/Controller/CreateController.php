<?php

declare(strict_types=1);

/*
 * (c) Rob Bast <rob.bast@gmail.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace Paste\Controller;

use Paste\Entity\Paste;
use Paste\Repository\PasteRepository;
use Paste\Security\HashGenerator;
use Symfony\Component\HttpFoundation\AcceptHeader;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\RouterInterface;

#[Route(path: '/', name: 'paste.create', methods: [Request::METHOD_PUT, Request::METHOD_POST], stateless: true)]
final readonly class CreateController
{
    public function __construct(
        private RouterInterface $router,
        private PasteRepository $repository,
        private HashGenerator $generator,
    ) {
    }

    public function __invoke(Request $request): Response
    {
        if ($request->request->has('paste')) {
            $body = $request->request->get('paste');
        } else {
            $body = $request->getContent();
        }

        if (!\is_string($body) || '' === $body) {
            throw new BadRequestHttpException('No input received.');
        }

        $ttl = null;

        if ($request->headers->has('X-Paste-Ttl')) {
            $ttl = (int) $request->headers->get('X-Paste-Ttl'); // @codeCoverageIgnore
        }

        do {
            $paste = new Paste($this->repository->generateIdentifier(), $body);
        } while (!$this->repository->persist($paste, $ttl));

        $location = $this
            ->router
            ->generate('paste.read', ['id' => $paste->code], RouterInterface::ABSOLUTE_URL)
        ;

        $headers = [
            'Location' => $location,
            'X-Paste-Id' => $paste->code,
            'X-Paste-Token' => $this->generator->generateHash($paste->code),
        ];

        $accept = AcceptHeader::fromString($request->headers->get('accept'));

        if ($accept->has('text/html')) {
            return new RedirectResponse($location, Response::HTTP_SEE_OTHER, $headers);
        }

        return new Response(
            sprintf("%s\n", $location),
            Response::HTTP_CREATED,
            $headers + ['Content-Type' => 'text/plain']
        );
    }
}
