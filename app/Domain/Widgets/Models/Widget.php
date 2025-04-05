<?php

namespace Leantime\Domain\Widgets\Models;

class Widget
{
    /**
     * Constructor for creating a new instance of the class.
     *
     * @param  string  $id  The unique identifier for the widget.
     * @param  string  $name  The name of the widget.
     * @param  string  $widgetUrl  The URL of the widget.
     * @param  string  $widgetTrigger  The trigger for loading the widget (default: "load").
     * @param  int  $gridMinWidth  The minimum width of the widget in the grid (default: 1).
     * @param  int  $gridMinHeight  The minimum height of the widget in the grid (default: 1).
     * @param  int  $gridX  The X position of the widget in the grid (default: 0).
     * @param  int  $gridY  The Y position of the widget in the grid (default: 0).
     * @param  int  $gridHeight  The height of the widget in the grid (default: 1).
     * @param  int  $gridWidth  The width of the widget in the grid (default: 1).
     * @param  string  $widgetLoadingIndicator  The loading indicator type for the widget (default: "text").
     * @param  string  $widgetBackground  The background type for the widget (default: "default").
     * @param  bool  $alwaysVisible  Indicates if the widget is always visible (default: false).
     * @return void
     */
    public function __construct(
        public string $id,
        public string $name,
        public string $widgetUrl,
        public string $description = '',
        public string $widgetTrigger = 'load',
        public int $gridMinWidth = 1,
        public int $gridMinHeight = 1,
        public int $gridX = 0,
        public int $gridY = 0,
        public int $gridHeight = 1,
        public int $gridWidth = 1,
        public bool $noTitle = false,
        public bool $fixed = false,
        public string $widgetLoadingIndicator = 'text',
        public string $widgetBackground = 'default',
        public bool $alwaysVisible = false
    ) {}
}
