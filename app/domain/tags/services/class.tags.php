<?php

namespace leantime\domain\services {

    use leantime\core;
    use leantime\domain\repositories;
    use DatePeriod;
    use DateTime;
    use DateInterval;

    class tags
    {
        private repositories\projects $projectRepository;
        private repositories\canvas $canvasRepository;
        private repositories\tickets $ticketRepository;

        public function __construct(
            repositories\projects $projectRepository,
            repositories\canvas $canvasRepository,
            repositories\tickets $ticketRepository
        ) {
            $this->projectRepository = $projectRepository;
            $this->canvasRepository = $canvasRepository;
            $this->ticketRepository = $ticketRepository;
        }

        public function getTags(int $projectId, string $term): array
        {
            $tags = array();

            $ticketTags = $this->ticketRepository->getTags($projectId);
            $tags = $this->explodeAndMergeTags($ticketTags, $tags);

            $canvasTags = $this->canvasRepository->getTags($projectId);
            $tags = $this->explodeAndMergeTags($canvasTags, $tags);
            $unique = array_unique($tags);

            $tagArray = [];
            foreach ($unique as $tag) {
                if (strpos($tag, strip_tags($term)) !== false) {
                    $tagArray[] = $tag;
                }
            }
            return $tagArray;
        }

        private function explodeAndMergeTags($dbTagValues, array $mergeInto): array
        {
            foreach ($dbTagValues as $tagGroup) {
                if (isset($tagGroup["tags"]) && $tagGroup["tags"] != null) {
                    $tagArray = explode(",", $tagGroup["tags"]);
                    $mergeInto = array_merge($tagArray, $mergeInto);
                }
            }

            return $mergeInto;
        }
    }
}
