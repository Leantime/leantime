<?php

use Leantime\Core\Http\HttpKernel;

define('RESTRICTED', true);
define('ROOT', __DIR__);
define('APP_ROOT', dirname(__DIR__, 1));
define('LEAN_CLI', false);

require __DIR__.'/../vendor/autoload.php';

$app = require_once APP_ROOT . '/bootstrap/app.php';

$app->make(\Leantime\Core\Console\ConsoleKernel::class)->bootstrap();

// Register Leantime's CarbonImmutable date/time macros into Carbon's macro registry so
// Carbon's official PHPStan MacroExtension (vendor/nesbot/carbon/extension.neon) can resolve
// them (formatDateForUser, setToDbTimezone, ...). At runtime these are registered by the
// Localization middleware; PHPStan never runs that middleware, so register them here.
\Carbon\CarbonImmutable::mixin(new \Leantime\Core\Support\CarbonMacros);
