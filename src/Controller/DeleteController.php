<?php

/*
 * (c) Rob Bast <rob.bast@gmail.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace Alcohol\PasteBundle\Controller;

use Alcohol\PasteBundle\Entity\PasteManager;
use Alcohol\PasteBundle\Exception\StorageException;
use Alcohol\PasteBundle\Exception\TokenException;
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
        } catch (StorageException $e) {
            throw new NotFoundHttpException($e->getMessage(), $e);
        }

        try {
            $this->manager->delete($paste, $request->headers->get('X-Paste-Token', false));
        } catch (StorageException $e) {
            throw new NotFoundHttpException($e->getMessage(), $e);
        } catch (TokenException $e) {
            throw new AccessDeniedHttpException($e->getMessage(), $e);
        }

        return new Response('', 204);
    }
}
