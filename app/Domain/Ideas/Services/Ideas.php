<?php

namespace Leantime\Domain\Ideas\Services {

    use Leantime\Domain\Ideas\Repositories\Ideas as IdeasRepository;

    class Ideas
    {
        private IdeasRepository $ideasRepository;

        public function __construct(IdeasRepository $ideasRepository)
        {
            $this->ideasRepository = $ideasRepository;
        }

        /**
         * @param ?int $projectId
         * @param ?int $board
         * @return array
         *
         * @api
         */
        public function pollForNewIdeas(?int $projectId = null, ?int $board = null): array
        {
            $ideas = $this->ideasRepository->getAllIdeas($projectId, $board);

            foreach ($ideas as $key => $idea) {
                $ideas[$key] = $this->prepareDatesForApiResponse($idea);
            }

            return $ideas;
        }

        /**
         * @param ?int $projectId
         * @param ?int $board
         * @return array
         *
         * @api
         */
        public function pollForUpdatedIdeas(?int $projectId = null, ?int $board = null): array
        {
            $ideas = $this->ideasRepository->getAllIdeas($projectId, $board);

            foreach ($ideas as $key => $idea) {
                $ideas[$key] = $this->prepareDatesForApiResponse($idea);
                $ideas[$key]['id'] = $idea['id'] . '-' . $idea['modified'];
            }

            return $ideas;
        }

        private function prepareDatesForApiResponse($idea) {

            if(dtHelper()->isValidDateString($idea['created'])) {
                $idea['created'] = dtHelper()->parseDbDateTime($idea['created'])->toIso8601ZuluString();
            }else{
                $idea['created'] = null;
            }

            if(dtHelper()->isValidDateString($idea['modified'])) {
                $idea['modified'] = dtHelper()->parseDbDateTime($idea['modified'])->toIso8601ZuluString();
            }else{
                $idea['modified'] = null;
            }

            return $idea;

        }
    }
}
