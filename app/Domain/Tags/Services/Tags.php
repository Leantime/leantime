<?php

namespace Leantime\Domain\Tags\Services {

    use Leantime\Domain\Canvas\Repositories\Canvas as CanvaRepository;
    use Leantime\Domain\Projects\Repositories\Projects as ProjectRepository;
    use Leantime\Domain\Tickets\Repositories\Tickets as TicketRepository;

    /**
     * @api
     */
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

        /**
         * @api
         */
        public function getTags(int $projectId, string $term): array
        {
            $tags = [];

            $ticketTags = $this->ticketRepository->getTags($projectId);
            $tags = $this->explodeAndMergeTags($ticketTags, $tags);

            $canvasTags = $this->canvasRepository->getTags($projectId);
            $tags = $this->explodeAndMergeTags($canvasTags, $tags);
            $unique = array_unique($tags);

            $tagArray = [];
            foreach ($unique as $tag) {
                if (str_contains($tag, strip_tags($term))) {
                    $tagArray[] = $tag;
                }
            }

            return $tagArray;
        }

        /**
         * @api
         */
        private function explodeAndMergeTags($dbTagValues, array $mergeInto): array
        {
            foreach ($dbTagValues as $tagGroup) {
                if (isset($tagGroup['tags']) && $tagGroup['tags'] != null) {
                    $tagArray = explode(',', $tagGroup['tags']);
                    $mergeInto = array_merge($tagArray, $mergeInto);
                }
            }

            return $mergeInto;
        }
    }
}
