<?php

namespace Leantime\Domain\Api\Services;

use Leantime\Domain\Ideas\Repositories\Ideas as IdeasRepository;

/**
 * Class Ideas
 *
 * Thin service wrapping idea/ideation data access for the API controllers.
 */
class Ideas
{
    private IdeasRepository $ideasRepository;

    /**
     * @api
     */
    public function __construct(IdeasRepository $ideasRepository)
    {
        $this->ideasRepository = $ideasRepository;
    }

    /**
     * Persists a new idea sort order.
     *
     * @param  mixed  $payload  Sorting payload from the request
     *
     * @api
     */
    public function updateIdeaSorting($payload): bool
    {
        return $this->ideasRepository->updateIdeaSorting($payload);
    }

    /**
     * Bulk updates the status of ideas.
     *
     * @param  mixed  $payload  Status payload from the request
     *
     * @api
     */
    public function bulkUpdateIdeaStatus($payload): bool
    {
        return $this->ideasRepository->bulkUpdateIdeaStatus($payload);
    }

    /**
     * Persists a new ideation sort order.
     *
     * @param  mixed  $payload  Sorting payload from the request
     *
     * @api
     */
    public function updateIdeationSorting($payload): bool
    {
        return $this->ideasRepository->updateIdeationSorting($payload);
    }

    /**
     * Bulk updates the status of ideations.
     *
     * @param  mixed  $payload  Status payload from the request
     *
     * @api
     */
    public function bulkUpdateIdeationStatus($payload): bool
    {
        return $this->ideasRepository->bulkUpdateIdeationStatus($payload);
    }

    /**
     * Patches a single canvas item.
     *
     * @param  int  $id  Canvas item id
     * @param  array  $params  Patch values
     *
     * @api
     */
    public function patchCanvasItem(int $id, array $params): bool
    {
        return $this->ideasRepository->patchCanvasItem($id, $params);
    }
}
