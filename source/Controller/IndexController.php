<?php

declare(strict_types=1);

/*
 * (c) Rob Bast <rob.bast@gmail.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace Alcohol\Paste\Controller;

use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\HttpFoundation\AcceptHeader;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Process\Process;
use Symfony\Component\Routing\RouterInterface;

class IndexController
{
    /** @var RouterInterface */
    protected $router;

    /** @var EngineInterface */
    private $engine;

    /**
     * @param EngineInterface $engine
     * @param RouterInterface $router
     */
    public function __construct(EngineInterface $engine, RouterInterface $router)
    {
        $this->engine = $engine;
        $this->router = $router;
    }

    /**
     * @param Request $request
     *
     * @return Response
     */
    public function __invoke(Request $request): Response
    {
        $process = new Process('git log --pretty="%h" -n1 HEAD');
        $version = (0 === $process->run()) ? $process->getOutput() : 'head';
        $accept = AcceptHeader::fromString($request->headers->get('Accept'));
        $href = $this->router->generate('paste.create', [], RouterInterface::ABSOLUTE_URL);
        $variables = ['version' => $version, 'href' => $href];

        if ($accept->has('text/html')) {
            $body = $this->engine->render('index.html.twig', $variables);
            $headers = ['Content-Type' => 'text/html'];
        } else {
            $body = $this->engine->render('index.text.twig', $variables);
            $headers = ['Content-Type' => 'text/plain'];
        }

        $response = new Response($body, 200, $headers);
        $response
            ->setVary(['Accept', 'Accept-Encoding'])
            ->setEtag(md5($response->getContent()))
            ->setTtl(60)
            ->setClientTtl(300)
        ;

        if (!$request->isNoCache()) {
            $response->isNotModified($request);
        }

        return $response;
    }
}
