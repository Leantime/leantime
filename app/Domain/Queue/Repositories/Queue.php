<?php

namespace Leantime\Domain\Queue\Repositories;

use Carbon\CarbonImmutable;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\Log;
use Leantime\Core\Db\Db as DbCore;
use Leantime\Domain\Queue\Workers\Workers;
use Leantime\Domain\Users\Repositories\Users as UserRepo;

class Queue
{
    private ConnectionInterface $db;

    private UserRepo $users;

    public function __construct(DbCore $db, UserRepo $users)
    {
        $this->db = $db->getConnection();
        $this->users = $users;
    }

    public function queueMessageToUsers(array $recipients, string $message, string $subject = '', int $projectId = 0): void
    {
        $recipients = array_unique($recipients);

        foreach ($recipients as $recipient) {
            $thedate = CarbonImmutable::now('UTC')->format('Y-m-d H:i:s');
            // NEW : Allowing recipients to be emails or userIds
            // TODO : Accept a list of \user objects too ?
            if (is_int($recipient)) {
                $theuser = $this->users->getUser($recipient);
            } elseif (filter_var($recipient, FILTER_VALIDATE_EMAIL)) {
                $theuser = $this->users->getUserByEmail($recipient);
            } else {
                // skip invalid users
                continue;
            }

            // User might not be set because it's a new user
            if (! $theuser) {
                continue;
            }

            $userId = $theuser['id'];
            $userEmail = $theuser['username'];
            $msghash = md5($thedate.$subject.$message.$userEmail.$projectId);

            // msghash is the same message to the same user in the same second, so a duplicate
            // key is the same notification queued twice (e.g. a double-submitted patch): skip it.
            // Only that error is ignored; anything else (e.g. an oversized value) is logged.
            try {
                $this->db->table('zp_queue')->insert([
                    'msghash' => $msghash,
                    'channel' => Workers::EMAILS->value,
                    'userId' => $userId,
                    'subject' => $subject,
                    'message' => $message,
                    'thedate' => $thedate,
                    'projectId' => $projectId,
                ]);
            } catch (UniqueConstraintViolationException $e) {
                continue;
            } catch (\PDOException $e) {
                // Not report($e): a QueryException's message is the SQL with its bindings filled
                // in, so it carries the whole email subject and body.
                Log::error('Queue email could not be saved', [
                    'userId' => $userId,
                    'projectId' => $projectId,
                    'exception' => get_class($e),
                    'sqlState' => $e->getCode(),
                ]);
            }
        }
    }

    // TODO later : lists messages per user or per project ?

    /**
     * Lists the queued messages of one channel.
     *
     * Without a limit, every row of the channel ordered by userId, projectId, thedate. With a
     * limit, at most that many rows, oldest first (msghash breaking ties), limited in the query
     * so a caller that takes a batch never loads the whole backlog.
     *
     * @param  Workers  $channel  The channel to list.
     * @param  mixed  $recipients  Unused.
     * @param  int  $projectId  Unused.
     * @param  int|null  $limit  Most rows to return, oldest first; null for the whole channel.
     * @return false|array<int, array<string, mixed>> The rows as column arrays.
     */
    public function listMessageInQueue(Workers $channel, mixed $recipients = null, int $projectId = 0, ?int $limit = null): false|array
    {
        $query = $this->db->table('zp_queue')
            ->where('channel', $channel->value);

        if ($limit === null) {
            $query->orderBy('userId')
                ->orderBy('projectId')
                ->orderBy('thedate');
        } else {
            $query->orderBy('thedate')
                ->orderBy('msghash')
                ->limit($limit);
        }

        return array_map(fn ($item) => (array) $item, $query->get()->toArray());
    }

    /**
     * Deletes queued messages by hash.
     *
     * Each hash is its own DELETE, so the database decides which of two workers holding the same
     * row removes it: a caller that must act on a row at most once acts only on true.
     *
     * @param  string|array<int, string>  $msghashes  One hash or several.
     * @return bool True when every hash removed a row; false when any row was already gone, e.g.
     *              deleted by another worker. The rows still there are deleted either way.
     */
    public function deleteMessageInQueue(string|array $msghashes): bool
    {
        // NEW : Allowing one hash or an array of them
        $thehashes = is_string($msghashes) ? [$msghashes] : $msghashes;

        $everyHashRemovedARow = true;
        foreach ($thehashes as $msghash) {
            $deletedRows = $this->db->table('zp_queue')
                ->where('msghash', $msghash)
                ->delete();

            if ($deletedRows === 0) {
                $everyHashRemovedARow = false;
            }
        }

        return $everyHashRemovedARow;
    }

    /**
     * Queues one message on a channel. Every call writes its own row: the row's msghash is a
     * random id, never derived from the content, so a message identical to one queued in the
     * same second (the same notification raised twice, say) is kept, and workers claim and
     * delete each row on its own.
     *
     * thedate is stamped in UTC, never the request's timezone, so rows queued by users in
     * different timezones list oldest first in the order they were actually queued.
     *
     * A failed insert never throws, so a message that cannot be queued does not stop the
     * notifications sent beside it. It is logged without its content: by channel, user, project,
     * exception class and SQLSTATE only.
     *
     * @param  Workers  $channel  The channel whose worker runs the message.
     * @param  string  $subject  What that worker runs it with, e.g. the job class on DEFAULT and WEBHOOKS.
     * @param  string  $message  The serialized payload.
     * @param  int  $userId  The user the message belongs to.
     * @param  int  $projectId  The project the message belongs to; 0 for none.
     */
    public function addMessageToQueue(Workers $channel, string $subject, string $message, int $userId, int $projectId = 0): void
    {
        $thedate = CarbonImmutable::now('UTC')->format('Y-m-d H:i:s');
        // 128 random bits as 32 hex characters: unique per row, fits msghash VARCHAR(50).
        $msghash = bin2hex(random_bytes(16));

        try {
            $this->db->table('zp_queue')->insert([
                'msghash' => $msghash,
                'channel' => $channel->value,
                'userId' => $userId,
                'subject' => $subject,
                'message' => $message,
                'thedate' => $thedate,
                'projectId' => $projectId,
            ]);
        } catch (\PDOException $e) {
            // Not report($e), and never the message: a QueryException's message is the SQL with
            // its bindings filled in, so it carries the whole subject and payload.
            Log::error('Queue message could not be saved', [
                'channel' => $channel->value,
                'userId' => $userId,
                'projectId' => $projectId,
                'exception' => get_class($e),
                'sqlState' => $e->getCode(),
            ]);
        }
    }
}
