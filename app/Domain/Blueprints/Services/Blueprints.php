<?php

declare(strict_types=1);

namespace Leantime\Domain\Blueprints\Services;

use DOMDocument;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\Facades\Log;
use Leantime\Core\Language as LanguageCore;
use Leantime\Domain\Blueprints\Models\CanvasTemplate;
use Leantime\Domain\Blueprints\Repositories\Blueprints as BlueprintsRepository;
use Leantime\Domain\Users\Repositories\Users as UserRepository;

/**
 * Blueprints service - business logic for unified canvas boards.
 *
 * Replaces the old Canvas\Services\Canvas by using the Blueprints repo
 * and TemplateRegistry directly instead of dynamically resolving variant repos.
 *
 * @api
 */
class Blueprints
{
    private BlueprintsRepository $blueprintsRepo;

    private TemplateRegistry $templateRegistry;

    private LanguageCore $language;

    /**
     * @param  BlueprintsRepository  $blueprintsRepo  Blueprints repository
     * @param  TemplateRegistry  $templateRegistry  Canvas template registry
     * @param  LanguageCore  $language  Language service for translations
     */
    public function __construct(
        BlueprintsRepository $blueprintsRepo,
        TemplateRegistry $templateRegistry,
        LanguageCore $language
    ) {
        $this->blueprintsRepo = $blueprintsRepo;
        $this->templateRegistry = $templateRegistry;
        $this->language = $language;
    }

    /**
     * Import a canvas board from an XML file.
     *
     * Parses the XML, validates its structure, then creates a new canvas board
     * with all items from the file.
     *
     * @param  string  $filename  Path to the XML file
     * @param  string  $canvasSlug  Canvas type slug (e.g., "swot", "lean")
     * @param  int  $projectId  Project identifier
     * @param  int  $authorId  Author user identifier
     * @return bool|int False on failure, or the new canvas board ID on success
     *
     * @throws BindingResolutionException
     *
     * @api
     */
    public function import(string $filename, string $canvasSlug, int $projectId, int $authorId): bool|int
    {
        $template = $this->templateRegistry->get($canvasSlug);
        if ($template === null) {
            Log::error("Blueprints import failed: unknown canvas slug '{$canvasSlug}'");

            return false;
        }

        $dom = new DOMDocument('1.0', 'UTF-8');
        $users = app()->make(UserRepository::class);

        $canvasData = file_get_contents($filename);
        if ($canvasData === false) {
            return false;
        }

        $oldErrorReporting = error_reporting(error_reporting() & ~E_WARNING);
        $status = $dom->loadXML($canvasData);
        error_reporting($oldErrorReporting);
        if ($status === false) {
            return false;
        }

        $canvasAry = ['projectId' => $projectId, 'author' => $authorId];
        $recordsAry = [];

        $canvasNodeList = $dom->getElementsByTagName('canvas');
        if ($canvasNodeList->count() !== 1) {
            return false;
        }

        $importedCanvasName = $canvasNodeList->item(0)->getAttribute('key');

        $titleNodeList = $canvasNodeList->item(0)->getElementsByTagName('title');
        if ($titleNodeList->count() !== 1) {
            return false;
        }
        $canvasAry['title'] = $titleNodeList->item(0)->nodeValue;

        $dataNodeList = $canvasNodeList->item(0)->getElementsByTagName('content');
        if ($dataNodeList->count() !== 1) {
            return false;
        }

        $elementNodeList = $dataNodeList->item(0)->getElementsByTagName('element');

        foreach ($elementNodeList as $elementNode) {
            if (! $elementNode->hasAttribute('key')) {
                return false;
            }
            $elementKey = $elementNode->getAttribute('key');

            $itemNodeList = $elementNode->getElementsByTagName('item');
            foreach ($itemNodeList as $itemName) {
                $authorNodeList = $itemName->getElementsByTagName('author');
                if ($authorNodeList->count() !== 1) {
                    return false;
                }
                if (! $authorNodeList->item(0)->hasAttribute('firstname')) {
                    return false;
                }
                $authorFirstname = $authorNodeList->item(0)->getAttribute('firstname');
                if (! $authorNodeList->item(0)->hasAttribute('lastname')) {
                    return false;
                }
                $authorLastname = $authorNodeList->item(0)->getAttribute('lastname');
                $author = $users->getUserIdByName($authorFirstname, $authorLastname);
                if ($author === false) {
                    $author = $authorId;
                }

                $descriptionNodeList = $itemName->getElementsByTagName('description');
                if ($descriptionNodeList->count() !== 1) {
                    return false;
                }
                $description = $descriptionNodeList->item(0)->nodeValue;

                $statusNodeList = $itemName->getElementsByTagName('status');
                if ($statusNodeList->count() !== 1) {
                    return false;
                }
                if (! $statusNodeList->item(0)->hasAttribute('key')) {
                    return false;
                }
                $statusKey = $statusNodeList->item(0)->getAttribute('key');

                $relatesNodeList = $itemName->getElementsByTagName('relates');
                if ($relatesNodeList->count() !== 1) {
                    return false;
                }
                if (! $relatesNodeList->item(0)->hasAttribute('key')) {
                    return false;
                }
                $relates = $relatesNodeList->item(0)->getAttribute('key');

                $assumptionsNodeList = $itemName->getElementsByTagName('assumptions');
                if ($assumptionsNodeList->count() !== 1) {
                    return false;
                }
                $assumptions = empty($assumptionsNodeList->item(0)->nodeValue) ? '' :
                    $dom->saveHTML($assumptionsNodeList->item(0)->firstChild);

                $importDataNodeList = $itemName->getElementsByTagName('data');
                if ($importDataNodeList->count() !== 1) {
                    return false;
                }
                $data = empty($importDataNodeList->item(0)->nodeValue) ? '' :
                    $dom->saveHTML($importDataNodeList->item(0)->firstChild);

                $conclusionNodeList = $itemName->getElementsByTagName('conclusion');
                if ($conclusionNodeList->count() !== 1) {
                    return false;
                }
                $conclusion = empty($conclusionNodeList->item(0)->nodeValue) ? '' :
                    $dom->saveHTML($conclusionNodeList->item(0)->firstChild);

                $recordsAry[] = [
                    'description' => $description,
                    'assumptions' => $assumptions,
                    'data' => $data,
                    'conclusion' => $conclusion,
                    'box' => $elementKey,
                    'author' => $author,
                    'status' => $statusKey,
                    'relates' => $relates,
                    'milestoneId' => '',
                ];
            }
        }

        $expectedCanvasKey = $template->getDatabaseType();
        if ($expectedCanvasKey !== $importedCanvasName) {
            return false;
        }

        $canvasType = $template->getDatabaseType();

        $canvasAry['title'] .= ' [imported]';
        if ($this->blueprintsRepo->existCanvas($projectId, $canvasAry['title'], $canvasType)) {
            return false;
        }

        $canvasId = $this->blueprintsRepo->addCanvas($canvasAry, $canvasType);
        if ($canvasId === false) {
            return false;
        }

        foreach ($recordsAry as $record) {
            $record['canvasId'] = $canvasId;
            $this->blueprintsRepo->addCanvasItem($record);
        }

        return (int) $canvasId;
    }

    /**
     * Get progress percentages for canvas boards in a project.
     *
     * Counts items per box type for each canvas and calculates what fraction
     * of box types have at least one item.
     *
     * @param  string  $projectId  Project identifier (empty string for all)
     * @param  array<int, string>  $boards  Array of database canvas types to check
     * @return array<string, float> Map of canvas type to max progress (0.0 to 1.0)
     *
     * @throws BindingResolutionException
     *
     * @api
     */
    public function getBoardProgress(string $projectId = '', array $boards = []): array
    {
        $values = $this->blueprintsRepo->getCanvasProgressCount((int) $projectId, $boards);

        $results = [];

        foreach ($values as $row) {
            $canvasType = $row['canvasType'];

            if (! isset($results[$canvasType])) {
                $results[$canvasType] = [];
            }

            if (! isset($results[$canvasType][$row['canvasId']])) {
                $template = $this->templateRegistry->getByDatabaseType($canvasType);
                $results[$canvasType][$row['canvasId']] = [];

                if ($template !== null) {
                    foreach ($template->boxes as $type => $box) {
                        $results[$canvasType][$row['canvasId']][$type] = 0;
                    }
                }
            }

            if ($row['box'] != '' && $row['boxItems'] > 0) {
                $results[$canvasType][$row['canvasId']][$row['box']]++;
            }
        }

        $progressResults = [];

        foreach ($results as $key => &$canvas) {
            $template = $this->templateRegistry->getByDatabaseType($key);
            $numOfBoxes = $template !== null ? count($template->boxes) : 1;

            if (! isset($progressResults[$key])) {
                $progressResults[$key] = '';
            }

            $maxProgress = 0;
            foreach ($canvas as $canvasId => $singleCanvas) {
                $numOfBoxesFilled = 0;
                foreach ($singleCanvas as $box) {
                    if ($box > 0) {
                        $numOfBoxesFilled++;
                    }
                }
                $progress = $numOfBoxesFilled / $numOfBoxes;
                if ($progress > $maxProgress) {
                    $maxProgress = $progress;
                }
            }

            $progressResults[$key] = $maxProgress;
        }

        return $progressResults;
    }

    /**
     * Get canvas boards ordered by last updated item.
     *
     * @param  int|null  $projectId  Project identifier (null for all)
     * @param  array<int, string>  $boards  Array of database canvas types to filter by
     * @return array<int, array<string, mixed>> List of canvas boards with modification dates
     *
     * @throws BindingResolutionException
     *
     * @api
     */
    public function getLastUpdatedCanvas(?int $projectId = null, array $boards = []): array
    {
        return $this->blueprintsRepo->getLastUpdatedCanvas((int) $projectId, $boards);
    }

    /**
     * Translate the box labels from a CanvasTemplate.
     *
     * Returns the boxes array with title values run through the language service.
     *
     * @param  CanvasTemplate  $template  Canvas template
     * @return array<string, array<string, mixed>> Translated box definitions
     */
    public function getTranslatedBoxes(CanvasTemplate $template): array
    {
        $boxes = $template->boxes;
        foreach ($boxes as $key => $data) {
            if (isset($data['title'])) {
                $boxes[$key]['title'] = $this->language->__($data['title']);
            }
        }

        return $boxes;
    }

    /**
     * Translate the status labels from a CanvasTemplate.
     *
     * @param  CanvasTemplate  $template  Canvas template
     * @return array<string, array<string, mixed>> Translated status labels
     */
    public function getTranslatedStatusLabels(CanvasTemplate $template): array
    {
        $statusLabels = $template->statusLabels;
        foreach ($statusLabels as $key => $data) {
            if (isset($data['title'])) {
                $statusLabels[$key]['title'] = $this->language->__($data['title']);
            }
        }

        return $statusLabels;
    }

    /**
     * Translate the relates labels from a CanvasTemplate.
     *
     * @param  CanvasTemplate  $template  Canvas template
     * @return array<string, array<string, mixed>> Translated relates labels
     */
    public function getTranslatedRelatesLabels(CanvasTemplate $template): array
    {
        $relatesLabels = $template->relatesLabels;
        foreach ($relatesLabels as $key => $data) {
            if (isset($data['title'])) {
                $relatesLabels[$key]['title'] = $this->language->__($data['title']);
            }
        }

        return $relatesLabels;
    }

    /**
     * Translate the data labels from a CanvasTemplate.
     *
     * @param  CanvasTemplate  $template  Canvas template
     * @return array<int, array<string, mixed>> Translated data labels
     */
    public function getTranslatedDataLabels(CanvasTemplate $template): array
    {
        $dataLabels = $template->dataLabels;
        foreach ($dataLabels as $key => $data) {
            if (isset($data['title'])) {
                $dataLabels[$key]['title'] = $this->language->__($data['title']);
            }
        }

        return $dataLabels;
    }

    /**
     * Translate the disclaimer string from a CanvasTemplate.
     *
     * @param  CanvasTemplate  $template  Canvas template
     * @return string Translated disclaimer, or empty string if none
     */
    public function getTranslatedDisclaimer(CanvasTemplate $template): string
    {
        if (empty($template->disclaimer)) {
            return '';
        }

        return $this->language->__($template->disclaimer);
    }
}
