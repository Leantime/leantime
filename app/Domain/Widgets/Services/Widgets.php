<?php

namespace Leantime\Domain\Widgets\Services;

use Illuminate\Contracts\Container\BindingResolutionException;
use Leantime\Core\Eventhelpers;
use Leantime\Core\Service;
use Leantime\Domain\Setting\Repositories\Setting;
use Leantime\Domain\Widgets\Models\Widget;

/**
 *
 */
class Widgets
{
    use Eventhelpers;

    /**
     * @var array
     */
    public array $availableWidgets = [];

    /**
     * @var array
     */
    public array $defaultWidgets = [];

    /**
     * @var Setting
     */
    public Setting $settingRepo;

    /**
     * __construct method.
     *
     * Initializes the object and sets the available widgets and default widgets.
     *
     * @param Setting $settingRepo The Setting repository object
     *
     * @return void
     */
    public function __construct(Setting $settingRepo)
    {

        $this->settingRepo = $settingRepo;

        $this->availableWidgets["welcome"] = app()->make("Leantime\Domain\Widgets\Models\Widget", [
            "id" => "welcome",
            "name" => "Welcome",
            "description" => "Description",
            "gridHeight" => 9,
            "gridWidth" => 12,
            "gridMinHeight" => 8,
            "gridMinWidth" => 4,
            "gridX" => 0,
            "gridY" => 0,
            "widgetBackground" => "",
            "widgetTrigger" => "load, every 1m",
            "alwaysVisible" => true,
            "widgetUrl" => BASE_URL . "/widgets/welcome/get",
        ]);

        $this->availableWidgets["calendar"] = app()->make("Leantime\Domain\Widgets\Models\Widget", [
            "id" => "calendar",
            "name" => "Calendar",
            "description" => "Description",
            "gridHeight" => 30,
            "gridWidth" => 4,
            "gridMinHeight" => 12,
            "gridMinWidth" => 2,
            "gridX" => 8,
            "gridY" => 10,
            "alwaysVisible" => false,
            "widgetUrl" => BASE_URL . "/widgets/calendar/get",
        ]);

        $this->availableWidgets["todos"] = app()->make("Leantime\Domain\Widgets\Models\Widget", [
            "id" => "todos",
            "name" => "My ToDos",
            "description" => "Description",
            "gridHeight" => 30,
            "gridWidth" => 8,
            "gridMinHeight" => 16,
            "gridMinWidth" => 2,
            "gridX" => 0,
            "gridY" => 10,
            "alwaysVisible" => false,
            "widgetUrl" => BASE_URL . "/widgets/myToDos/get",
        ]);

        $this->availableWidgets["myprojects"] = app()->make("Leantime\Domain\Widgets\Models\Widget", [
            "id" => "myprojects",
            "name" => "My Projects",
            "description" => "Description",
            "gridHeight" => 20,
            "gridWidth" => 8,
            "gridMinHeight" => 8,
            "gridMinWidth" => 2,
            "gridX" => 0,
            "gridY" => 10,
            "alwaysVisible" => false,
            "widgetUrl" => BASE_URL . "/widgets/myProjects/get",
        ]);

        $this->defaultWidgets = [
            "welcome"     => $this->availableWidgets["welcome"],
            "calendar"    => $this->availableWidgets["calendar"],
            "todos"       => $this->availableWidgets["todos"],
            "myprojects"  => $this->availableWidgets["myprojects"]
        ];
    }

    /**
     * Retrieves all available widgets.
     *
     * @return array The array of available widgets.
     */
    public function getAll(): array
    {
        return Eventhelpers::dispatch_filter("availableWidgets", $this->availableWidgets);
    }

    /**
     * Retrieves the active widgets for a specific user.
     *
     * @param int $userId The ID of the user.
     * @return array The array of active widgets.
     */
    public function getActiveWidgets(int $userId): array
    {

        $activeWidgets = $this->settingRepo->getSetting("usersettings." . $userId . ".dashboardGrid");

        $widgets = $this->defaultWidgets;

        if ($activeWidgets && $activeWidgets != '') {
            $unserializedData =  unserialize($activeWidgets);
            if (is_array($unserializedData)) {
                $unserializedData = array_sort($unserializedData, function ($a, $b) {

                    $first = intval($a['y'] . $a['x']);
                    $second = intval(($b['y'] ?? 0) . ($b['x'] ?? 0));
                    return $first - $second;
                });
            }

            $widgets = array();
            foreach ($unserializedData as $key => $widget) {
                if (isset($this->availableWidgets[$widget["id"]])) {
                    $widget["name"] = $this->availableWidgets[$widget["id"]]->name;
                    $widget["widgetBackground"] = $this->availableWidgets[$widget["id"]]->widgetBackground;


                    $widgets[$widget["id"]] = app()->make(Widget::class, $widget);
                }
            }
        }

        return $widgets;
    }

    /**
     * Resets the dashboard grid for a specific user.
     *
     * @param int $userId The ID of the user for whom the dashboard grid needs to be reset.
     * @return void
     */
    public function resetDashboard(int $userId): void
    {
        $this->settingRepo->deleteSetting("usersettings." . $userId . ".dashboardGrid");
    }
}
