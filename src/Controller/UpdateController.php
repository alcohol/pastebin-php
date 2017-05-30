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
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\ServiceUnavailableHttpException;

final class UpdateController
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
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param string $id
     *
     * @return \Symfony\Component\HttpFoundation\Response
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
