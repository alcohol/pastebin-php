<?php

declare(strict_types=1);

/*
 * (c) Rob Bast <rob.bast@gmail.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace Alcohol\Paste\Controller;

use Alcohol\Paste\Exception\StorageException;
use Alcohol\Paste\Repository\PasteRepository;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\HttpFoundation\AcceptHeader;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\ServiceUnavailableHttpException;
use Symfony\Component\Routing\RouterInterface;

class CreateController
{
    /** @var PasteRepository */
    protected $repository;

    /** @var RouterInterface */
    protected $router;

    /** @var EngineInterface */
    private $engine;

    /**
     * @param EngineInterface $engine
     * @param RouterInterface $router
     * @param PasteRepository $repository
     */
    public function __construct(EngineInterface $engine, RouterInterface $router, PasteRepository $repository)
    {
        $this->engine = $engine;
        $this->router = $router;
        $this->repository = $repository;
    }

    /**
     * @param Request $request
     *
     * @return Response
     */
    public function __invoke(Request $request): Response
    {
        if ($request->request->has('paste')) {
            $body = $request->request->get('paste');
        } else {
            $body = $request->getContent();
        }

        if (empty($body)) {
            throw new BadRequestHttpException('No input received.');
        }

        $paste = $this->repository->create($body);

        $ttl = null;
        if ($request->headers->has('X-Paste-Ttl')) {
            $ttl = (int) $request->headers->get('X-Paste-Ttl');
        }

        try {
            $this->repository->persist($paste, $ttl);
        } catch (StorageException $exception) {
            throw new ServiceUnavailableHttpException(300, $exception->getMessage());
        }

        $location = $this
            ->router
            ->generate('paste.read', ['id' => $paste->getCode()], RouterInterface::ABSOLUTE_URL)
        ;

        $headers = [
            'Location' => $location,
            'X-Paste-Id' => $paste->getCode(),
        ];

        $accept = AcceptHeader::fromString($request->headers->get('Accept'));

        if ($accept->has('text/html')) {
            return new RedirectResponse($location, 303, $headers);
        }

        return new Response(sprintf("%s\n", $location), 201, $headers + ['Content-Type' => 'text/plain']);
    }
}
