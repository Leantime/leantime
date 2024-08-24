<?php

namespace Leantime\Domain\Api\Controllers;

use Leantime\Core\Controller\Controller;
use Leantime\Domain\Tags\Services\Tags as TagService;
use Symfony\Component\HttpFoundation\Response;

/**
 *
 */
class Tags extends Controller
{
    private TagService $tagService;

    /**
     * init - initialize private variables
     *
     * @access public
     *
     * @param TagService $tagService
     *
     * @return void
     */
    public function init(TagService $tagService): void
    {
        $this->tagService = $tagService;
    }

    /**
     * get - handle get requests
     *
     * @access public
     *
     * @param array $params parameters or body of the request
     *
     * @return Response
     */
    public function get(array $params): Response
    {
        $tags = $this->tagService->getTags(session("currentProject"), $params['term']);
        return $this->tpl->displayJson($tags);
    }

    /**
     * post - handle post requests
     *
     * @access public
     *
     * @param array $params parameters or body of the request
     *
     * @return Response
     */
    public function post(array $params): Response
    {
        return $this->tpl->displayJson(['status' => 'Not implemented'], 501);
    }

    /**
     * put - handle put requests
     *
     * @access public
     *
     * @param array $params parameters or body of the request
     *
     * @return Response
     */
    public function patch(array $params): Response
    {
        return $this->tpl->displayJson(['status' => 'Not implemented'], 501);
    }

    /**
     * delete - handle delete requests
     *
     * @access public
     *
     * @param array $params parameters or body of the request
     *
     * @return Response
     */
    public function delete(array $params): Response
    {
        return $this->tpl->displayJson(['status' => 'Not implemented'], 501);
    }
}
