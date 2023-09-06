<?php

namespace Leantime\Domain\Tags\Services {

    use Leantime\Domain\Projects\Repositories\Projects as ProjectRepository;
    use Leantime\Domain\Canvas\Repositories\Canvas as CanvaRepository;
    use Leantime\Domain\Tickets\Repositories\Tickets as TicketRepository;
    class Tags
    {
        private ProjectRepository $projectRepository;
        private CanvaRepository $canvasRepository;
        private TicketRepository $ticketRepository;

        public function __construct(
            ProjectRepository $projectRepository,
            CanvaRepository $canvasRepository,
            TicketRepository $ticketRepository
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
