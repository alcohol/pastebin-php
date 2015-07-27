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
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\ServiceUnavailableHttpException;

class UpdateController
{
    /** @var PasteRepository */
    protected $repository;

    /**
     * @param PasteRepository $repository
     */
    public function __construct(PasteRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @param Request $request
     * @param string $code
     *
     * @return Response
     */
    public function __invoke(Request $request, $code)
    {
        try {
            $paste = $this->repository->find($code);
        } catch (StorageException $exception) {
            throw new NotFoundHttpException();
        }

        if ($request->request->has('paste')) {
            $body = $request->request->get('paste');
        } else {
            $body = $request->getContent();
        }

        $paste->setBody($body);

        try {
            $this->repository->persist($paste, $request->headers->get('X-Paste-Ttl', null));
        } catch (StorageException $exception) {
            throw new ServiceUnavailableHttpException(300, $exception->getmessage(), $exception);
        }

        return new Response('', 204);
    }
}
