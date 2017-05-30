<?php declare(strict_types=1);

/*
 * (c) Rob Bast <rob.bast@gmail.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace Paste\Controller;

use Paste\Exception\StorageException;
use Paste\Repository\PasteRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class DeleteController
{
    /**
     * @var \Paste\Repository\PasteRepository
     */
    protected $repository;

    /**
     * @param \Paste\Repository\PasteRepository $repository
     */
    public function __construct(PasteRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @param string $id
     *
     * @return \Symfony\Component\HttpFoundation\Response
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
