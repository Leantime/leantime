<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Files Domain Routes
|--------------------------------------------------------------------------
|
| Backwards-compatibility redirect: legacy /download.php URLs were embedded
| in ticket descriptions by the old TinyMCE editor. The file was removed in
| the storage refactor but the URLs live on in the database. This route
| preserves those links permanently by forwarding all query parameters to
| the active /files/get endpoint.
|
*/

Route::get('/download.php', function () {
    $qs = request()->getQueryString();

    return redirect()->to('/files/get'.($qs ? '?'.$qs : ''), 301);
});
