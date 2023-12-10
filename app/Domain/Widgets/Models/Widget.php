<?php

namespace Leantime\Domain\Widgets\Models {

    /**
     *
     */
    class Widget
    {
        /**
         * Constructs a new instance of the class.
         *
         * @param string $id The ID of the widget.
         * @param string $name The name of the widget.
         * @param string $widgetUrl The URL of the widget.
         * @param string $widgetTrigger The trigger for the widget (default: "load").
         * @param int $gridMinWidth The minimum width of the grid (default: 1).
         * @param int $gridMinHeight The minimum height of the grid (default: 1).
         * @param int $gridX The x-coordinate of the widget on the grid (default: 0).
         * @param int $gridY The y-coordinate of the widget on the grid (default: 0).
         * @param int $gridHeight The height of the widget on the grid (default: 1).
         * @param int $gridWidth The width of the widget on the grid (default: 1).
         * @param string $widgetLoadingIndicator The loading indicator for the widget (default: "text").
         * @param string $widgetBackground The background for the widget (default: "default").
         */
        public function __construct(
            public string $id,
            public string $name,
            public string $widgetUrl,
            public string $widgetTrigger = "load",
            public int $gridMinWidth = 1,
            public int $gridMinHeight = 1,
            public int $gridX = 0,
            public int $gridY = 0,
            public int $gridHeight = 1,
            public int $gridWidth = 1,
            public string $widgetLoadingIndicator = "text",
            public string $widgetBackground = "default"
        ) {
        }
    }

}
