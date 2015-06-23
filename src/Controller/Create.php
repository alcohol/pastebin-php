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

class Create
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
    public function __index(Request $request)
    {
        $input = $request->request->has('paste') ? $request->request->get('paste') : $request->getContent();

        try {
            $paste = $this->manager->create($input);
        } catch (StorageException $e) {
            throw new ServiceUnavailableHttpException(300, $e->getmessage(), $e);
        } catch (LengthException $e) {
            throw new BadRequestHttpException($e->getMessage(), $e);
        }

        $body = sprintf("%s%s\n", $request->getUri(), $paste->getCode());

        return new Response($body, 201, [
            'Content-Type' => 'text/plain',
            'Location' => '/' . $paste->getCode(),
            'X-Paste-Token' => $paste->getToken(),
        ]);
    }
}
