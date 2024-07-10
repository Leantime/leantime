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

        public function pollIdeas(): array
        {
            return $this->ideasRepository->getAllIdeas();
        }

        public function pollForUpdatedIdeas(): array
        {
            $ideas = $this->ideasRepository->getAllIdeas();

            foreach ($ideas as $key => $idea) {
                $ideas[$key]['id'] = $idea['id'] . '-' . $idea['modified'];
            }

            return $ideas;
        }
    }
}
