<?php

namespace Leantime\Domain\Api\Services;

use Leantime\Domain\Ideas\Repositories\Ideas as IdeasRepository;

/**
 * Internal shim wrapping idea data access for the legacy Api Ideation controller.
 *
 * NOT exposed via JSON-RPC: these methods operate by item id with no project
 * scoping. Idea mutations from the frontend now go through the authorized
 * wrappers on Leantime\Domain\Ideas\Services\Ideas (reorderIdeas /
 * bulkUpdateStatus / patchIdeaItem), which enforce editor + project access.
 *
 * @deprecated Will be removed once the Api Ideation controller is gone.
 */
class Ideas
{
    private IdeasRepository $ideasRepository;

    public function __construct(IdeasRepository $ideasRepository)
    {
        $this->ideasRepository = $ideasRepository;
    }

    public function updateIdeaSorting($payload): bool
    {
        return $this->ideasRepository->updateIdeaSorting($payload);
    }

    public function bulkUpdateIdeaStatus($payload): bool
    {
        return $this->ideasRepository->bulkUpdateIdeaStatus($payload);
    }

    public function updateIdeationSorting($payload): bool
    {
        return $this->ideasRepository->updateIdeaSorting($payload);
    }

    public function bulkUpdateIdeationStatus($payload): bool
    {
        return $this->ideasRepository->bulkUpdateIdeaStatus($payload);
    }

    public function patchCanvasItem(int $id, array $params): bool
    {
        return $this->ideasRepository->patchCanvasItem($id, $params);
    }
}
