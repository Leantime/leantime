<?php

use Illuminate\Support\Facades\Route;
use Leantime\Domain\Files\Controllers\Upload;

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

/*
| File uploads were relocated here from the retired Api\Controllers\Files.
| The canonical route is /files/upload. The /api/files alias is kept so the Tiptap
| editor and Uppy file manager keep working without a JS change; both read the raw
| upload() metadata array off the JSON response, which Files\Controllers\Upload preserves.
*/

Route::post('/files/upload', [Upload::class, 'post'])->name('files.upload');

Route::post('/api/files', [Upload::class, 'post'])->name('files.upload.legacy');
