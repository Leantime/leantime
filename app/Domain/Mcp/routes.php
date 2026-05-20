<?php

use Illuminate\Support\Facades\Route;
use Leantime\Domain\Mcp\Controllers\Mcp;

Route::match(['GET', 'POST'], '/mcp', Mcp::class)->name('mcp');
