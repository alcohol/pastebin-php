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
use Symfony\Component\HttpFoundation\AcceptHeader;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Twig\Environment;

#[Route(path: '/{id}/raw', name: 'paste.read.raw', defaults: ['raw' => true], methods: [Request::METHOD_GET], stateless: true)]
#[Route(path: '/{id}', name: 'paste.read', methods: [Request::METHOD_GET])]
final readonly class ReadController
{
    public function __construct(
        private Environment $engine,
        private PasteRepository $repository
    ) {
    }

    public function __invoke(Request $request, string $id, bool $raw = false): Response
    {
        try {
            $paste = $this->repository->find($id);
        } catch (NotFoundException $exception) {
            throw new NotFoundHttpException(sprintf('Paste "%s" not found.', $id), $exception);
        }

        $accept = AcceptHeader::fromString($request->headers->get('Accept'));

        if ($accept->has('text/html') && !$raw) {
            $body = $this->engine->render('read.html.twig', ['paste' => $paste]);
            $headers = ['Content-Type' => 'text/html'];
        } else {
            $body = $paste->body;
            $headers = ['Content-Type' => 'text/plain'];
        }

        $response = new Response($body, Response::HTTP_OK, $headers);
        $response
            ->setVary(['Accept', 'Accept-Encoding'])
            ->setEtag(md5((string) $response->getContent()))
            ->setTtl(3600)
            ->setClientTtl(300)
        ;

        if (!$request->isNoCache()) {
            $response->isNotModified($request);
        }

        return $response;
    }
}
