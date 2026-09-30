<?php

namespace Leantime\Domain\Queue\Repositories;

use Illuminate\Database\ConnectionInterface;
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
            $thedate = date('Y-m-d H:i:s');
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
            } catch (\PDOException $e) {
                report($e);
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

    public function addMessageToQueue(Workers $channel, string $subject, string $message, int $userId, int $projectId = 0): void
    {
        $thedate = date('Y-m-d H:i:s');
        $msghash = md5($thedate.$subject.$message.$projectId);

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
            report($e);
        }
    }
}
