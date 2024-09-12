<?php

namespace Leantime\Domain\Comments\Models;

use Carbon\CarbonImmutable;

class Comment
{
    public ?string $id;

    public ?string $text;

    public ?CarbonImmutable $date;

    public ?int $moduleId;

    public ?int $userId;

    public ?string $status;

    public ?string $firstname;

    public ?string $lastname;

    public ?string $profileId;

    public ?string $userModified;

    public array $replies = [];

    public int $commentParent = 0;

    public function mapRootDbArray(array $comment)
    {

        $this->id = $comment['id'] ?? '';
        $this->text = $comment['text'] ?? '';
        $this->date = dtHelper()->parseDbDateTime($comment['date']);
        $this->userId = $comment['userId'] ?? '';
        $this->userModified = $comment['userModified'] ?? '';
        $this->commentParent = $comment['commentParent'] ?? '';
        $this->status = $comment['status'] ?? '';
        $this->firstname = $comment['firstname'] ?? '';
        $this->lastname = $comment['lastname'] ?? '';
        $this->profileId = $comment['profileId'] ?? '';
    }

    public function mapRepliesDbArray(array $comment)
    {

        if ($comment['repliesId'] == null) {
            return false;
        }
        $this->id = $comment['repliesId'] ?? '';
        $this->text = $comment['repliesText'] ?? '';
        $this->date = dtHelper()->parseDbDateTime($comment['repliesDate'] ?? '');
        $this->userId = $comment['repliesUserId'] ?? '';
        $this->userModified = $comment['repliesUserModified'] ?? '';
        $this->commentParent = $comment['repliesCommentParent'] ?? '';
        $this->status = $comment['repliesStatus'] ?? '';
        $this->firstname = $comment['repliesFirstname'] ?? '';
        $this->lastname = $comment['repliesLastname'] ?? '';
        $this->profileId = $comment['repliesProfileId'] ?? '';
    }
}
