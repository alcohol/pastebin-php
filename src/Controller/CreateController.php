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
use LengthException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\ServiceUnavailableHttpException;

class CreateController
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
     * @return Response
     */
    public function __invoke(Request $request)
    {
        $body = $request->request->has('paste') ? $request->request->get('paste') : $request->getContent();

        try {
            $paste = $this->manager->create($body, $request->headers->get('X-Paste-Ttl', null));
        } catch (StorageException $exception) {
            throw new ServiceUnavailableHttpException(300, $exception->getmessage(), $exception);
        } catch (LengthException $exception) {
            throw new BadRequestHttpException($exception->getMessage(), $exception);
        }

        $response = sprintf("%s%s\n", $request->getUri(), $paste->getCode());

        return new Response($response, 201, [
            'Content-Type' => 'text/plain',
            'Location' => '/' . $paste->getCode(),
            'X-Paste-Token' => $paste->getToken(),
        ]);
    }
}
