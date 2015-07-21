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
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ReadController
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

        $response = new Response($paste->getBody(), 200, ['Content-Type' => 'text/plain']);
        $response
            ->setPublic()
            ->setETag(md5($response->getContent()))
            ->setTtl(60 * 60)
            ->setClientTtl(60 * 10)
        ;

        if (!$request->isNoCache()) {
            $response->isNotModified($request);
        }

        return $response;
    }
}
