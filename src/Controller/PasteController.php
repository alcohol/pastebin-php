<?php

namespace Alcohol\PasteBundle\Controller;

use Alcohol\PasteBundle\Entity\PasteManager;
use Alcohol\PasteBundle\Exception\StorageException;
use Alcohol\PasteBundle\Exception\TokenException;
use LengthException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\ServiceUnavailableHttpException;

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
        $input = $request->get('paste') ?: $request->getContent();

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

    /**
     * @param Request $request
     * @param string $code
     * @return Response
     */
    public function readAction(Request $request, $code)
    {
        try {
            $paste = $this->manager->read($code);
        } catch (StorageException $e) {
            throw new NotFoundHttpException($e->getMessage(), $e);
        }

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
        try {
            $paste = $this->manager->read($code);
        } catch (StorageException $e) {
            throw new NotFoundHttpException($e->getMessage(), $e);
        } catch (TokenException $e) {
            throw new AccessDeniedHttpException($e->getMessage(), $e);
        }

        $input = $request->get('paste') ?: $request->getContent();
        $paste->setBody($input);

        try {
            $this->manager->update($paste, $request->headers->get('X-Paste-Token', false));
        } catch (StorageException $e) {
            throw new ServiceUnavailableHttpException(300, $e->getmessage(), $e);
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
    &lt;command&gt; | curl --data-binary '@-' $host

ALTERNATIVELY
    use <a href='$form'>this form</a> to paste from a browser

SOURCE
    <a href='https://github.com/alcohol/sf-minimal-demo/'>github.com/alcohol</a>
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
