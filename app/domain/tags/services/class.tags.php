<?php

namespace leantime\domain\services {

    use leantime\core;
    use leantime\domain\repositories;
    use DatePeriod;
    use DateTime;
    use DateInterval;

    class tags
    {
        private $projectRepository;
        private $ticketRepository;
        private $canvasRepository;

        public function __construct()
        {
            $this->projectRepository = new repositories\projects();
            $this->canvasRepository = new repositories\canvas();
            $this->ticketRepository = new repositories\tickets();
        }

        public function getTags(int $projectId, string $term): array
        {
            $tags = array();

            $ticketTags = $this->ticketRepository->getTags($projectId);
            $tags = $this->explodeAndMergeTags($ticketTags, $tags);

            $canvasTags = $this->canvasRepository->getTags($projectId);
            $tags = $this->explodeAndMergeTags($canvasTags, $tags);
            $unique =array_unique($tags);

            $tagArray=[];
            foreach($unique as $tag)  {
                if (strpos($tag, strip_tags($term)) !== false) { $tagArray[] = $tag; }
            }
            return $tagArray;
        }

        private function explodeAndMergeTags($dbTagValues, array $mergeInto): array
        {
            foreach ($dbTagValues as $tagGroup) {
                $tagArray = explode(",", $tagGroup["tags"]);
                $mergeInto = array_merge($tagArray, $mergeInto);
            }

            return $mergeInto;
        }
    }
}
