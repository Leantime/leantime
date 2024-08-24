<?php

namespace Leantime\Domain\Widgets\Services;

use Leantime\Core\Events\Eventhelpers;
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
            "name" => "widgets.title.welcome",
            "description" => "widgets.descriptions.welcome",
            "widgetUrl" => BASE_URL . "/widgets/welcome/get",
            "gridHeight" => 11,
            "gridWidth" => 12,
            "gridMinHeight" => 8,
            "gridMinWidth" => 6,
            "gridX" => 0,
            "gridY" => 0,
            "widgetBackground" => "",
            "widgetTrigger" => "load, every 5m",
            "alwaysVisible" => true,
            "noTitle" => true,

        ]);

        $this->availableWidgets["todos"] = app()->make("Leantime\Domain\Widgets\Models\Widget", [
            "id" => "todos",
            "name" => "widgets.title.my_todos",
            "description" => "widgets.descriptions.my_todos",
            "widgetUrl" => BASE_URL . "/widgets/myToDos/get",
            "gridHeight" => 30,
            "gridWidth" => 8,
            "gridMinHeight" => 16,
            "gridMinWidth" => 3,
            "gridX" => 0,
            "gridY" => 12,
            "alwaysVisible" => false,
            "noTitle" => false,

        ]);

        $this->availableWidgets["calendar"] = app()->make("Leantime\Domain\Widgets\Models\Widget", [
            "id" => "calendar",
            "name" => "widgets.title.calendar",
            "description" => "widgets.descriptions.calendar",
            "gridHeight" => 30,
            "gridWidth" => 4,
            "gridMinHeight" => 12,
            "gridMinWidth" => 3,
            "gridX" => 8,
            "gridY" => 12,
            "alwaysVisible" => false,
            "noTitle" => false,
            "widgetUrl" => BASE_URL . "/widgets/calendar/get",
        ]);

        $this->availableWidgets["myprojects"] = app()->make("Leantime\Domain\Widgets\Models\Widget", [
            "id" => "myprojects",
            "name" => "widgets.title.my_projects",
            "description" => "widgets.descriptions.my_projects",
            "gridHeight" => 22,
            "gridWidth" => 8,
            "gridMinHeight" => 10,
            "gridMinWidth" => 2,
            "gridX" => 0,
            "gridY" => 43,
            "alwaysVisible" => false,
            "noTitle" => false,
            "widgetUrl" => BASE_URL . "/widgets/myProjects/get",
        ]);

        $this->defaultWidgets = [
            "welcome"     => $this->availableWidgets["welcome"],
            "calendar"    => $this->availableWidgets["calendar"],
            "todos"       => $this->availableWidgets["todos"]
        ];

        $this->availableWidgets = self::dispatch_filter("availableWidgets", $this->availableWidgets);
        $this->defaultWidgets = self::dispatch_filter("defaultWidgets", $this->defaultWidgets, array("availableWidgets"=> $this->availableWidgets));
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
                    $widget["description"] = $this->availableWidgets[$widget["id"]]->description;
                    $widget["widgetTrigger"] = $this->availableWidgets[$widget["id"]]->widgetTrigger;
                    $widget["alwaysVisible"] = $this->availableWidgets[$widget["id"]]->alwaysVisible;
                    $widget["gridMinWidth"] = $this->availableWidgets[$widget["id"]]->gridMinWidth;
                    $widget["gridMinHeight"] = $this->availableWidgets[$widget["id"]]->gridMinHeight;
                    $widget["noTitle"] = $this->availableWidgets[$widget["id"]]->noTitle;
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
