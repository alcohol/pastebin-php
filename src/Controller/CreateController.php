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
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\ServiceUnavailableHttpException;
use Symfony\Component\Routing\RouterInterface;

class CreateController
{
    /** @var PasteManager */
    protected $manager;

    /** @var RouterInterface */
    protected $router;

    /**
     * @param PasteManager $manager
     */
    public function __construct(PasteManager $manager, RouterInterface $router)
    {
        $this->manager = $manager;
        $this->router = $router;
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
        } catch (\LengthException $exception) {
            throw new BadRequestHttpException($exception->getMessage(), $exception);
        }

        $response = sprintf("%s\n", $this->router->generate(
            'paste.read',
            ['code' => $paste->getCode()],
            RouterInterface::ABSOLUTE_URL
        ));

        return new Response($response, 201, [
            'Content-Type' => 'text/plain',
            'Location' => $this->router->generate('paste.read', ['code' => $paste->getCode()]),
            'X-Paste-Token' => $paste->getToken(),
        ]);
    }
}
