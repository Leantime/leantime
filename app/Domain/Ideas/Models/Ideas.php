<?php

namespace Leantime\Domain\Ideas\Models;

class Ideas
{
    public $id;
    public $description;
    public $assumptions;
    public $data;
    public $conclusion;
    public $box;
    public $author;
    public $created;
    public $modified;
    public $canvasId;
    public $sortindex;
    public $status;
    public $milestoneId;
    public $tags;
    public $authorFirstname;
    public $authorLastname;
    public $authorProfileId;
    public $milestoneHeadline;
    public $milestoneEditTo;
    public $commentCount;

    public $title;
    public $projectId;

    public function __construct()
    {
    }

    
    // public function __construct(string $type = '')
    // {
    //     $this->box = $type;
    // }

    // public static function createFromType(string $type): self
    // {
    //     return new self($type);
    // }
}