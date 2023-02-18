<?php

namespace leantime\domain\models\notifications;

class notification
{
    public int $id;
    public string $message;
    public string $subject;
    public int $projectId;
    public int $authorId;
    public bool|array $url;
    public mixed $entity;
    public string $module;
}
