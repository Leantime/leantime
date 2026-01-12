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

    public function listMessageInQueue(Workers $channel, mixed $recipients = null, int $projectId = 0): false|array
    {
        $results = $this->db->table('zp_queue')
            ->where('channel', $channel->value)
            ->orderBy('userId')
            ->orderBy('projectId')
            ->orderBy('thedate')
            ->get();

        return array_map(fn ($item) => (array) $item, $results->toArray());
    }

    public function deleteMessageInQueue(string|array $msghashes): bool
    {
        // NEW : Allowing one hash or an array of them
        $thehashes = is_string($msghashes) ? [$msghashes] : $msghashes;

        foreach ($thehashes as $msghash) {
            $this->db->table('zp_queue')
                ->where('msghash', $msghash)
                ->delete();
        }

        return true;
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
