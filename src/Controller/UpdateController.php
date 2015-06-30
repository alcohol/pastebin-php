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
use Symfony\Component\HttpKernel\Exception\ServiceUnavailableHttpException;

class UpdateController
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
        } catch (TokenException $exception) {
            throw new AccessDeniedHttpException($exception->getMessage(), $exception);
        }

        if ($request->request->has('paste')) {
            $body =$request->request->get('paste');
        } else {
            $body = $request->getContent();
        }

        $paste->setBody($body);

        try {
            $this->manager->update(
                $paste,
                $request->headers->get('X-Paste-Token', false),
                $request->headers->get('X-Paste-Ttl', null)
            );
        } catch (StorageException $exception) {
            throw new ServiceUnavailableHttpException(300, $exception->getmessage(), $exception);
        }

        return new Response('', 204);
    }
}
