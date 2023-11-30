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
     * @param Setting $settingRepo
     * @throws BindingResolutionException
     */
    public function __construct(Setting $settingRepo)
    {

        $this->settingRepo = $settingRepo;

        $this->availableWidgets["welcome"] = app()->make("Leantime\Domain\Widgets\Models\Widget", [
            "id" => "welcome",
            "name" => "Welcome",
            "gridHeight" => 9,
            "gridWidth" => 8,
            "gridMinHeight" => 4,
            "gridMinWidth" => 2,
            "gridX" => 0,
            "gridY" => 0,
            "widgetBackground" => "",
            "widgetTrigger" => "load, every 1m",
            "widgetUrl" => BASE_URL . "/widgets/welcome/get",
        ]);

        $this->availableWidgets["calendar"] = app()->make("Leantime\Domain\Widgets\Models\Widget", [
            "id" => "calendar",
            "name" => "Calendar",
            "gridHeight" => 17,
            "gridWidth" => 4,
            "gridMinHeight" => 12,
            "gridMinWidth" => 2,
            "gridX" => 8,
            "gridY" => 0,
            "widgetUrl" => BASE_URL . "/widgets/calendar/get",
        ]);

        $this->availableWidgets["todos"] = app()->make("Leantime\Domain\Widgets\Models\Widget", [
            "id" => "todos",
            "name" => "My ToDos",
            "gridHeight" => 34,
            "gridWidth" => 8,
            "gridMinHeight" => 16,
            "gridMinWidth" => 2,
            "gridX" => 0,
            "gridY" => 10,
            "widgetUrl" => BASE_URL . "/widgets/myToDos/get",
        ]);

        $this->defaultWidgets = [
            $this->availableWidgets["welcome"],
            $this->availableWidgets["calendar"],
            $this->availableWidgets["todos"],
        ];
    }

    /**
     * @return array
     * @throws BindingResolutionException
     */
    public function getAll(): array
    {
        return Eventhelpers::dispatch_filter("availableWidgets", $this->availableWidgets);
    }

    /**
     * Gets all widgets active for a user. If no widgets are active, it returns the default widgets.
     *
     * @param int $userId
     * @return array
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
            foreach($unserializedData as $key => $widget) {
                if(isset($this->availableWidgets[$widget["id"]])) {
                    $widget["name"] = $this->availableWidgets[$widget["id"]]->name;
                    $widget["widgetBackground"] = $this->availableWidgets[$widget["id"]]->widgetBackground;


                    $widgets[$widget["id"]] = app()->make(Widget::class, $widget);
                }
            }
        }

        return $widgets;
    }

    public function resetDashboard(int $userId): void {
        $this->settingRepo->deleteSetting("usersettings." . $userId . ".dashboardGrid");
    }
}
