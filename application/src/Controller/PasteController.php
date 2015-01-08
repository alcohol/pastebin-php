<?php

namespace Alcohol\PasteBundle\Controller;

use Alcohol\PasteBundle\Entity\PasteManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class PasteController
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
    public function createAction(Request $request)
    {
        $body = $request->get('paste');
        $size = ini_get('mbstring.func_overload') ? mb_strlen($body, '8bit') : strlen($body);

        if ($size > 1024 * 1024) {
            return new Response('Maximum size of 1MiB exceeded.', 413);
        }

        $paste = $this->manager->create($body);

        if (!$paste) {
            return new Response('Unable to persist paste to storage.', 503, ['Retry-After' => 300]);
        }

        return new Response($paste->getCode(), 201, [
            'Location' => '/' . $paste->getCode(),
            'X-Paste-Token' => $paste->getToken()
        ]);
    }

    /**
     * @param string $code
     * @return Response
     */
    public function readAction($code)
    {
        $paste = $this->manager->loadPasteByCode($code);

        if (!$paste) {
            return new Response(sprintf('Paste not found: %s', $code), 404);
        }

        return new Response($paste->getBody(), 200, ['Content-Type' => 'text/plain']);
    }

    /**
     * @param Request $request
     * @param string $code
     * @return Response
     */
    public function updateAction(Request $request, $code)
    {
        $paste = $this->manager->loadPasteByCode($code);

        if (!$paste) {
            return new Response(sprintf('Paste not found: %s', $code), 404);
        }

        $token = $request->headers->get('X-Paste-Token', false);

        if (false === $token || $token !== $paste->getToken()) {
            return new Response(sprintf('Paste not found: %s', $code), 404);
        }

        $body = $request->get('paste');
        $size = ini_get('mbstring.func_overload') ? mb_strlen($paste, '8bit') : strlen($paste);

        if ($size > 1024 * 1024) {
            return new Response('Maximum size of 1MiB exceeded.', 413);
        }

        $paste->setBody($body);

        if (!$this->manager->persist($paste)) {
            return new Response('Unable to persist updated paste to storage.', 503, ['Retry-After' => 300]);
        }

        return new Response('', 204);
    }

    /**
     * @param Request $request
     * @param string $code
     * @return Response
     */
    public function deleteAction(Request $request, $code)
    {
        $paste = $this->manager->loadPasteByCode($code);

        if (!$paste) {
            return new Response(sprintf('Paste not found: %s', $code), 404);
        }

        $token = $request->headers->get('X-Paste-Token', false);

        if (false === $token || $token !== $paste->getToken()) {
            return new Response(sprintf('Paste not found: %s', $code), 404);
        }

        if (!$this->manager->delete($paste)) {
            return new Response('Unable to delete paste from storage.', 503, ['Retry-After' => 300]);
        }

        return new Response('', 204);
    }
}
