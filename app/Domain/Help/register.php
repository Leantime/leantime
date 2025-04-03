<?php

use Leantime\Core\Events\EventDispatcher;

EventDispatcher::addEventListener('leantime.domain.auth.*.userSignUpSuccess', function ($params) {

    $userId = session('userdata.id');
    $userRole = session('userdata.role');

    $helperService = app()->make(\Leantime\Domain\Help\Services\Helper::class);
    $helperService->createDefaultProject($userId, $userRole);



});
