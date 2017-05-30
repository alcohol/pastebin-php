<?php declare(strict_types=1);

/*
 * (c) Rob Bast <rob.bast@gmail.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace Paste\Controller;

use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\HttpFoundation\AcceptHeader;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Process\Process;

final class IndexController
{
    /**
     * @var \Symfony\Bundle\FrameworkBundle\Templating\EngineInterface
     */
    private $engine;

    /**
     * @param \Symfony\Bundle\FrameworkBundle\Templating\EngineInterface $engine
     */
    public function __construct(EngineInterface $engine)
    {
        $this->engine = $engine;
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function __invoke(Request $request): Response
    {
        $process = new Process('git log --pretty="%h" -n1 HEAD');
        $version = (0 === $process->run()) ? $process->getOutput() : 'head';
        $accept = AcceptHeader::fromString($request->headers->get('Accept'));

        if ($accept->has('text/html')) {
            $body = $this->engine->render('index.html.twig', ['version' => $version]);
            $headers = ['Content-Type' => 'text/html'];
        } else {
            $body = $this->engine->render('index.text.twig', ['version' => $version]);
            $headers = ['Content-Type' => 'text/plain'];
        }

        $response = new Response($body, 200, $headers);
        $response
            ->setVary(['Accept', 'Accept-Encoding'])
            ->setEtag(md5($response->getContent()))
            ->setTtl(3600)
            ->setClientTtl(300)
        ;

        if (!$request->isNoCache()) {
            $response->isNotModified($request);
        }

        return $response;
    }
}
