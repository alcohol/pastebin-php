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
use League\Plates\Engine;
use Symfony\Component\HttpFoundation\AcceptHeader;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\RouterInterface;

class ReadController
{
    /** @var PasteRepository */
    protected $repository;

    /** @var Engine */
    private $plates;
    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * @param Engine $plates
     * @param RouterInterface $router
     * @param PasteRepository $repository
     */
    public function __construct(Engine $plates, RouterInterface $router, PasteRepository $repository)
    {
        $this->plates = $plates;
        $this->router = $router;
        $this->repository = $repository;
    }

    /**
     * @param Request $request
     * @param string $id
     * @param bool $raw
     *
     * @return Response
     */
    public function __invoke(Request $request, string $id, $raw = false): Response
    {
        try {
            $paste = $this->repository->find($id);
        } catch (StorageException $exception) {
            throw new NotFoundHttpException();
        }

        $accept = AcceptHeader::fromString($request->headers->get('Accept'));

        if ($accept->has('text/html') && !$raw) {
            $body = $this->plates->render('read/html', [
                'paste' => $paste,
                'hrefNew' => $this->router->generate('paste.create', [], RouterInterface::ABSOLUTE_URL),
                'hrefRaw' => $this->router->generate('paste.read.raw', ['id' => $paste->getCode()], RouterInterface::ABSOLUTE_URL),
            ]);
            $headers = ['Content-Type' => 'text/html'];
        } else {
            $body = $paste;
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
