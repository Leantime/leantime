<?php

namespace Unit\app\Domain\Comments\Services;

use Leantime\Core\Language as LanguageCore;
use Leantime\Domain\Comments\Repositories\Comments as CommentRepository;
use Leantime\Domain\Comments\Services\Comments;
use Leantime\Domain\Projects\Services\Projects as ProjectService;
use Leantime\Domain\Reactions\Services\Reactions as ReactionsService;
use Unit\TestCase;

/**
 * Unit tests for the comment-reaction orchestration extracted from the
 * Comments/Reactions HxController into the Comments service.
 */
class CommentsServiceTest extends TestCase
{
    use \Codeception\Test\Feature\Stub;

    private function makeService(ReactionsService $reactionsService): Comments
    {
        return new Comments(
            $this->make(CommentRepository::class),
            $this->make(ProjectService::class),
            $this->make(LanguageCore::class),
            $reactionsService,
        );
    }

    public function test_toggle_rejects_unknown_reaction_type(): void
    {
        $added = false;
        $removed = false;

        $reactionsService = $this->make(ReactionsService::class, [
            'getReactionType' => fn () => false,
            'addReaction' => function () use (&$added) {
                $added = true;

                return true;
            },
            'removeReaction' => function () use (&$removed) {
                $removed = true;

                return true;
            },
        ]);

        $result = $this->makeService($reactionsService)->toggleCommentReaction(5, 99, 'bogus');

        $this->assertFalse($result);
        $this->assertFalse($added, 'No reaction should be added for an unknown type');
        $this->assertFalse($removed, 'No reaction should be removed for an unknown type');
    }

    public function test_toggle_off_removes_existing_same_reaction(): void
    {
        $removeCalls = [];
        $added = false;

        $reactionsService = $this->make(ReactionsService::class, [
            'getReactionType' => fn () => 'positive',
            // The targeted lookup for the clicked reaction returns the
            // existing same reaction, so the toggle removes it.
            'getUserReactions' => fn () => [['reaction' => 'thumbsup']],
            'removeReaction' => function ($userId, $module, $moduleId, $reaction) use (&$removeCalls) {
                $removeCalls[] = [$userId, $module, $moduleId, $reaction];

                return true;
            },
            'addReaction' => function () use (&$added) {
                $added = true;

                return true;
            },
        ]);

        $result = $this->makeService($reactionsService)->toggleCommentReaction(5, 99, 'thumbsup');

        $this->assertTrue($result);
        $this->assertFalse($added, 'Toggling off should not add a reaction');
        $this->assertSame([[5, 'comment', 99, 'thumbsup']], $removeCalls);
    }

    public function test_toggle_on_replaces_existing_sentiment(): void
    {
        $removeCalls = [];
        $addCalls = [];

        $reactionsService = $this->make(ReactionsService::class, [
            'getReactionType' => fn () => 'positive',
            'getUserReactions' => function ($userId, $module, $moduleId, $reaction = '') {
                // Targeted lookup for the clicked reaction: none yet.
                if ($reaction !== '') {
                    return [];
                }

                // Broad lookup: user currently has a different reaction.
                return [['reaction' => 'thumbsdown']];
            },
            'removeReaction' => function ($userId, $module, $moduleId, $reaction) use (&$removeCalls) {
                $removeCalls[] = [$userId, $module, $moduleId, $reaction];

                return true;
            },
            'addReaction' => function ($userId, $module, $moduleId, $reaction) use (&$addCalls) {
                $addCalls[] = [$userId, $module, $moduleId, $reaction];

                return true;
            },
        ]);

        $result = $this->makeService($reactionsService)->toggleCommentReaction(5, 99, 'thumbsup');

        $this->assertTrue($result);
        // Existing different reaction removed first, new one added.
        $this->assertSame([[5, 'comment', 99, 'thumbsdown']], $removeCalls);
        $this->assertSame([[5, 'comment', 99, 'thumbsup']], $addCalls);
    }

    public function test_toggle_on_with_no_existing_reactions_just_adds(): void
    {
        $removeCalls = [];
        $addCalls = [];

        $reactionsService = $this->make(ReactionsService::class, [
            'getReactionType' => fn () => 'positive',
            // No existing reactions at all (false is a valid repo return).
            'getUserReactions' => fn () => false,
            'removeReaction' => function (...$args) use (&$removeCalls) {
                $removeCalls[] = $args;

                return true;
            },
            'addReaction' => function ($userId, $module, $moduleId, $reaction) use (&$addCalls) {
                $addCalls[] = [$userId, $module, $moduleId, $reaction];

                return true;
            },
        ]);

        $result = $this->makeService($reactionsService)->toggleCommentReaction(5, 99, 'thumbsup');

        $this->assertTrue($result);
        $this->assertSame([], $removeCalls, 'Nothing to remove when there are no existing reactions');
        $this->assertSame([[5, 'comment', 99, 'thumbsup']], $addCalls);
    }

    public function test_get_comment_reactions_flattens_user_reaction_codes(): void
    {
        $reactionsService = $this->make(ReactionsService::class, [
            'getEntityReactionsWithUsers' => fn () => ['thumbsup' => ['count' => 2]],
            'getUserReactions' => fn () => [
                ['reaction' => 'thumbsup'],
                ['reaction' => 'heart'],
            ],
        ]);

        $result = $this->makeService($reactionsService)->getCommentReactions(99, 5);

        $this->assertSame(['thumbsup' => ['count' => 2]], $result['reactions']);
        $this->assertSame(['thumbsup', 'heart'], $result['userReactions']);
    }

    public function test_get_comment_reactions_handles_anonymous_user(): void
    {
        $reactionsService = $this->make(ReactionsService::class, [
            'getEntityReactionsWithUsers' => fn () => [],
            'getUserReactions' => fn () => [['reaction' => 'thumbsup']],
        ]);

        $result = $this->makeService($reactionsService)->getCommentReactions(99, 0);

        // No user id => no user reaction lookup, empty list, and empty reactions normalised to [].
        $this->assertSame([], $result['reactions']);
        $this->assertSame([], $result['userReactions']);
    }
}
