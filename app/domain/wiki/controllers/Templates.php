<?php

namespace Leantime\Domain\Wiki\Controllers {

    use Leantime\Core\Controller;
    use Leantime\Domain\Auth\Models\Roles;
    use Leantime\Domain\Wiki\Models\Wiki;
    use Leantime\Domain\Wiki\Services\Wiki as WikiService;
    use Leantime\Domain\Comments\Services\Comments as CommentService;
    use Leantime\Domain\Auth\Services\Auth;

    class Templates extends Controller
    {
        private WikiService $wikiService;
        private CommentService $commentService;

        public function init()
        {
        }

        public function get($params)
        {
            $this->tpl->displayPartial("wiki.templates");
        }
    }
}
