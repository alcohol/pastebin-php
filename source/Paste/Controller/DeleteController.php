<?php

/*
 * (c) Rob Bast <rob.bast@gmail.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace Alcohol\Paste\Controller;

use Alcohol\Paste\Entity\PasteManager;
use Alcohol\Paste\Exception\StorageException;
use Alcohol\Paste\Exception\TokenException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class DeleteController
{
    /** @var PasteManager */
    protected $manager;

    /**
     * @param PasteManager $manager
     */
    public function __construct(PasteManager $manager)
    {
        $this->manager = $manager;
    }

    /**
     * @param Request $request
     * @param string $code
     * @return Response
     */
    public function __invoke(Request $request, $code)
    {
        try {
            $paste = $this->manager->read($code);
        } catch (StorageException $exception) {
            throw new NotFoundHttpException($exception->getMessage(), $exception);
        }

        try {
            $this->manager->delete($paste, $request->headers->get('X-Paste-Token', false));
        } catch (StorageException $exception) {
            throw new NotFoundHttpException($exception->getMessage(), $exception);
        } catch (TokenException $exception) {
            throw new AccessDeniedHttpException($exception->getMessage(), $exception);
        }

        return new Response('', 204);
    }
}
