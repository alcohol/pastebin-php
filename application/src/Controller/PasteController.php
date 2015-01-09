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

        return new Response($request->getUri() . $paste->getCode(), 201, [
            'Content-Type' => 'text/plain',
            'Location' => '/' . $paste->getCode(),
            'X-Paste-Token' => $paste->getToken(),
        ]);
    }

    /**
     * @param Request $request
     * @param string $code
     * @return Response
     */
    public function readAction(Request $request, $code)
    {
        $paste = $this->manager->loadPasteByCode($code);

        if (!$paste) {
            return new Response(sprintf('Paste not found: %s', $code), 404);
        }

        $response = new Response($paste->getBody(), 200, ['Content-Type' => 'text/plain']);
        $response->setPublic();
        $response->setETag(md5($paste->getBody()));
        $response->setTtl(60 * 60);
        $response->setClientTtl(60 * 60);

        if (!$request->isNoCache()) {
            $response->isNotModified($request);
        }

        return $response;
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

    /**
     * @param Request $request
     * @return Response
     */
    public function indexAction(Request $request)
    {
        $href = $request->getUri();

        $form = <<<FORM
data:text/html,<form action="$href" method="POST" accept-charset="UTF-8">
<textarea name="paste" cols="100" rows="30"></textarea>
<br><button type="submit">paste</button></form>
FORM;

        $body = <<<BODY
<style>body { padding: 2em; }</style>
<pre>
DESCRIPTION
    paste: command line pastebin.

USING
    &lt;command&gt; | curl -F 'paste=&lt;-' paste.robbast.nl

ALTERNATIVELY
    use <a href='$form'>this form</a> to paste from a browser
</pre>
BODY;

        $response = new Response($body, 200);
        $response->setPublic();
        $response->setETag(md5($response->getContent()));
        $response->setTtl(60 * 60);
        $response->setClientTtl(60 * 60);

        if (!$request->isNoCache()) {
            $response->isNotModified($request);
        }

        return $response;
    }
}
