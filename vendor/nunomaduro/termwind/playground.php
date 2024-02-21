<?php

require_once __DIR__.'/vendor/autoload.php';

use function Termwind\render;

render(<<<'HTML'
    <div class="mx-2 my-1">
        <div class="flex space-x-1">
            <span class="flex-1 truncate">Lorem ipsum dolor, sit amet consectetur adipisicing elit. Sunt illo et nisi omnis porro at, mollitia harum quas esse, aperiam dolorem ab recusandae fugiat nesciunt doloribus rem eaque nostrum itaque.</span>
            <span class="text-green">DONE</span>
        </div>
    </div>
HTML);
