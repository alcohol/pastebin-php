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

class DeleteController
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
     * @param string $code
     *
     * @return Response
     */
    public function __invoke($code)
    {
        try {
            $paste = $this->repository->find($code);
        } catch (StorageException $exception) {
            throw new NotFoundHttpException();
        }

        try {
            $this->repository->delete($paste);
        } catch (StorageException $exception) {
            throw new NotFoundHttpException($exception->getMessage(), $exception);
        }

        return new Response('', 204);
    }
}
