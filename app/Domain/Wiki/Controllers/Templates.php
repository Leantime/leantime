<?php

namespace Leantime\Domain\Wiki\Controllers {

    use Leantime\Core\Controller;
    use Leantime\Domain\Auth\Models\Roles;
    use Leantime\Domain\Wiki\Models\Wiki;
    use Leantime\Domain\Wiki\Services\Wiki as WikiService;
    use Leantime\Domain\Comments\Services\Comments as CommentService;
    use Leantime\Domain\Auth\Services\Auth;
    use Symfony\Component\HttpFoundation\Response;

    /**
     *
     */
    class Templates extends Controller
    {
        private WikiService $wikiService;
        private CommentService $commentService;

        /**
         * @return void
         */
        public function init(): void
        {
        }

        /**
         * @param $params
         * @return void
         * @throws \Exception
         */
        public function get($params): Response
        {
            return $this->tpl->displayPartial("wiki.templates");
        }
    }
}
