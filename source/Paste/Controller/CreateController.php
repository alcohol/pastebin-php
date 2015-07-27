<?php

/*
 * (c) Rob Bast <rob.bast@gmail.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace Alcohol\Paste\Controller;

use Alcohol\Paste\Exception\StorageException;
use Alcohol\Paste\Repository\PasteRepository;
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

    /**
     * @param PasteRepository $repository
     * @param RouterInterface $router
     */
    public function __construct(PasteRepository $repository, RouterInterface $router)
    {
        $this->repository = $repository;
        $this->router = $router;
    }

    /**
     * @param Request $request
     *
     * @return Response
     */
    public function __invoke(Request $request)
    {
        $body = $request->request->has('paste') ? $request->request->get('paste') : $request->getContent();

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
            ->generate('paste.read', ['code' => $paste->getCode()], RouterInterface::ABSOLUTE_URL)
        ;

        $headers = [
            'Location' => $location,
            'X-Paste-Id' => $paste->getCode(),
        ];

        if ($request->request->has('redirect')) {
            return new RedirectResponse($location, 303, $headers);
        }

        return new Response(sprintf("%s\n", $location), 201, $headers + ['Content-Type' => 'text/plain']);
    }
}
