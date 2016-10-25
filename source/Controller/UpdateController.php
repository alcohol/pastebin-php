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
     * @param string $id
     *
     * @return Response
     */
    public function __invoke(Request $request, string $id): Response
    {
        try {
            $paste = $this->repository->find($id);
        } catch (StorageException $exception) {
            throw new NotFoundHttpException();
        }

        $paste->setBody($request->getContent());

        $ttl = null;
        if ($request->headers->has('X-Paste-Ttl')) {
            $ttl = (int) $request->headers->get('X-Paste-Ttl');
        }

        try {
            $this->repository->persist($paste, $ttl);
        } catch (StorageException $exception) {
            throw new ServiceUnavailableHttpException(300, $exception->getMessage(), $exception);
        }

        return new Response('', 204);
    }
}
