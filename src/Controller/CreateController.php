<?php declare(strict_types=1);

/*
 * (c) Rob Bast <rob.bast@gmail.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace Paste\Controller;

use Paste\Entity\Paste;
use Paste\Exception\StorageException;
use Paste\Repository\PasteRepository;
use Paste\Security\HashGenerator;
use Symfony\Component\HttpFoundation\AcceptHeader;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;

final class CreateController
{
    private PasteRepository $repository;
    private RouterInterface $router;
    private HashGenerator $generator;

    public function __construct(
        RouterInterface $router,
        PasteRepository $repository,
        HashGenerator $generator
    ) {
        $this->router = $router;
        $this->repository = $repository;
        $this->generator = $generator;
    }

    public function __invoke(Request $request): Response
    {
        if ($request->request->has('paste')) {
            $body = $request->request->get('paste');
        } else {
            $body = $request->getContent();
        }

        if ('' === $body) {
            return new Response('No input received.', Response::HTTP_BAD_REQUEST);
        }

        $paste = Paste::create($body);

        $ttl = null;
        if ($request->headers->has('X-Paste-Ttl')) {
            $ttl = (int) $request->headers->get('X-Paste-Ttl'); // @codeCoverageIgnore
        }

        try {
            $paste = $this->repository->persist($paste, $ttl);
        } catch (StorageException $exception) {
            return new Response(
                $exception->getMessage(),
                Response::HTTP_SERVICE_UNAVAILABLE,
                ['Retry-After' => 300]
            );
        }

        $location = $this
            ->router
            ->generate('paste.read', ['id' => $paste->getCode()], RouterInterface::ABSOLUTE_URL)
        ;

        $headers = [
            'Location' => $location,
            'X-Paste-Id' => $paste->getCode(),
            'X-Paste-Token' => $this->generator->generateHash($paste->getCode()),
        ];

        $accept = AcceptHeader::fromString($request->headers->get('accept'));

        if ($accept->has('text/html')) {
            return new RedirectResponse($location, 303, $headers);
        }

        return new Response(
            sprintf("%s\n", $location),
            Response::HTTP_CREATED,
            $headers + ['Content-Type' => 'text/plain']
        );
    }
}
