<?php

namespace Leantime\Domain\Widgets\Models {

    /**
     *
     */
    class Widget
    {
        /**
         * @param string $id
         * @param string $name
         * @param string $widgetUrl
         * @param string $widgetTrigger
         * @param int    $minGridHeight
         * @param int    $gridX
         * @param int    $gridY
         * @param int    $gridHeight
         * @param int    $gridWidth
         * @param string $widgetLoadingType
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
