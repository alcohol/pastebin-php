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
        $paste = $this->manager->create($request->get('paste'));

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

        $response = new Response($paste->getBody(), 200, ['Content-Type' => 'text/plain']);
        $response->setPublic();
        $response->setETag(md5($paste->getBody()));
        $response->setTtl(60 * 60);
        $response->setClientTtl(60 * 10);

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
        $paste->setBody($request->get('paste'));
        $this->manager->update($paste, $request->headers->get('X-Paste-Token', false));

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
        $this->manager->delete($paste, $request->headers->get('X-Paste-Token', false));

        return new Response('', 204);
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function indexAction(Request $request)
    {
        $href = $request->getUri();
        $host = $request->getHttpHost();

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
    &lt;command&gt; | curl -F 'paste=&lt;-' $host

ALTERNATIVELY
    use <a href='$form'>this form</a> to paste from a browser

SOURCE
    <a href='https://github.com/alcohol/sf-minimal-demo/tree/master/application'>github.com/alcohol</a>
</pre>
BODY;

        $response = new Response($body, 200);
        $response->setPublic();
        $response->setETag(md5($response->getContent()));
        $response->setTtl(60 * 60);
        $response->setClientTtl(60 * 10);

        if (!$request->isNoCache()) {
            $response->isNotModified($request);
        }

        return $response;
    }
}
