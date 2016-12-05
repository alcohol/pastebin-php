<?php

declare(strict_types=1);

/*
 * (c) Rob Bast <rob.bast@gmail.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace AppBundle\Controller;

use AppBundle\Exception\StorageException;
use AppBundle\Repository\PasteRepository;
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
     * @param string $id
     *
     * @return Response
     */
    public function __invoke(string $id): Response
    {
        try {
            $paste = $this->repository->find($id);
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
