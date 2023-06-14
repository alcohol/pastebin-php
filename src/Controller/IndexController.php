<?php

declare(strict_types=1);

/*
 * (c) Rob Bast <rob.bast@gmail.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace Paste\Controller;

use Symfony\Component\HttpFoundation\AcceptHeader;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Twig\Environment;

#[Route(path: '/', name: 'paste.index', methods: [Request::METHOD_GET], stateless: true)]
final readonly class IndexController
{
    public function __construct(
        private Environment $engine
    ) {
    }

    public function __invoke(Request $request): Response
    {
        $accept = AcceptHeader::fromString($request->headers->get('Accept'));

        if ($accept->has('text/html')) {
            $body = $this->engine->render('index.html.twig');
            $headers = ['Content-Type' => 'text/html'];
        } else {
            $body = $this->engine->render('index.text.twig');
            $headers = ['Content-Type' => 'text/plain'];
        }

        $response = new Response($body, Response::HTTP_OK, $headers);
        $response
            ->setVary(['Accept', 'Accept-Encoding'])
            ->setEtag(md5((string) $response->getContent()))
            ->setTtl(300)
            ->setClientTtl(60)
        ;

        if (!$request->isNoCache()) {
            $response->isNotModified($request);
        }

        return $response;
    }
}
