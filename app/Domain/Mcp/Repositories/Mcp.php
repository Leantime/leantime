<?php

namespace Leantime\Domain\Mcp\Repositories;

use Illuminate\Database\ConnectionInterface;
use Leantime\Core\Db\Db as DbCore;

class Mcp
{
    private ConnectionInterface $db;

    public function __construct(DbCore $db)
    {
        $this->db = $db->getConnection();
    }

    public function createOrTouchAgent(int $accessTokenId, int $userId, string $name): int
    {
        $existing = $this->db->table('zp_mcp_agents')->where('accessTokenId', $accessTokenId)->first();
        $now = now();

        if ($existing) {
            $this->db->table('zp_mcp_agents')->where('id', $existing->id)->update([
                'userId' => $userId,
                'name' => $name,
                'status' => 'active',
                'lastSeenAt' => $now,
                'updatedAt' => $now,
            ]);

            return (int) $existing->id;
        }

        return (int) $this->db->table('zp_mcp_agents')->insertGetId([
            'accessTokenId' => $accessTokenId,
            'userId' => $userId,
            'name' => $name,
            'status' => 'active',
            'lastSeenAt' => $now,
            'createdAt' => $now,
            'updatedAt' => $now,
        ]);
    }

    public function createRequestLog(array $values): int
    {
        $values['createdAt'] = $values['createdAt'] ?? now();
        $values['updatedAt'] = $values['updatedAt'] ?? now();

        return (int) $this->db->table('zp_mcp_requests')->insertGetId($values);
    }

    public function updateRequestLog(int $id, array $values): void
    {
        $values['updatedAt'] = now();
        $this->db->table('zp_mcp_requests')->where('id', $id)->update($values);
    }

    public function createToolCall(array $values): int
    {
        $values['createdAt'] = $values['createdAt'] ?? now();
        $values['updatedAt'] = $values['updatedAt'] ?? now();

        return (int) $this->db->table('zp_mcp_tool_calls')->insertGetId($values);
    }

    public function updateToolCall(int $id, array $values): void
    {
        $values['updatedAt'] = now();
        $this->db->table('zp_mcp_tool_calls')->where('id', $id)->update($values);
    }

    public function getToolCall(int $id): ?array
    {
        $row = $this->db->table('zp_mcp_tool_calls')->where('id', $id)->first();

        return $row ? (array) $row : null;
    }

    public function getToolCallByOperationId(string $operationId): ?array
    {
        $row = $this->db->table('zp_mcp_tool_calls')->where('operationId', $operationId)->first();

        return $row ? (array) $row : null;
    }

    public function findIdempotencyRecord(int $accessTokenId, string $toolName, int $projectId, string $idempotencyKey): ?array
    {
        $row = $this->db->table('zp_mcp_idempotency_keys')
            ->where('accessTokenId', $accessTokenId)
            ->where('toolName', $toolName)
            ->where('projectId', $projectId)
            ->where('idempotencyKey', $idempotencyKey)
            ->first();

        return $row ? (array) $row : null;
    }

    public function createIdempotencyRecord(array $values): int
    {
        $values['createdAt'] = $values['createdAt'] ?? now();
        $values['updatedAt'] = $values['updatedAt'] ?? now();
        $values['lastUsedAt'] = $values['lastUsedAt'] ?? now();

        return (int) $this->db->table('zp_mcp_idempotency_keys')->insertGetId($values);
    }

    public function findIdempotencyRecordByToolCallId(int $toolCallId): ?array
    {
        $row = $this->db->table('zp_mcp_idempotency_keys')->where('toolCallId', $toolCallId)->first();

        return $row ? (array) $row : null;
    }

    public function updateIdempotencyRecord(int $id, array $values): void
    {
        $values['updatedAt'] = now();
        $values['lastUsedAt'] = now();
        $this->db->table('zp_mcp_idempotency_keys')->where('id', $id)->update($values);
    }

    public function createApproval(array $values): int
    {
        $values['createdAt'] = $values['createdAt'] ?? now();
        $values['updatedAt'] = $values['updatedAt'] ?? now();
        $values['requestedAt'] = $values['requestedAt'] ?? now();

        return (int) $this->db->table('zp_mcp_approvals')->insertGetId($values);
    }

    public function getApproval(int $approvalId): ?array
    {
        $row = $this->db->table('zp_mcp_approvals')->where('id', $approvalId)->first();

        return $row ? (array) $row : null;
    }

    public function listApprovalsForUser(int $userId, bool $includeAll = false): array
    {
        $query = $this->db->table('zp_mcp_approvals')->orderByDesc('createdAt');

        if (! $includeAll) {
            $query->where(function ($builder) use ($userId) {
                $builder->where('requestedByUserId', $userId)
                    ->orWhere('resolvedByUserId', $userId);
            });
        }

        return array_map(fn ($item) => (array) $item, $query->get()->toArray());
    }

    public function updateApproval(int $approvalId, array $values): void
    {
        $values['updatedAt'] = now();
        $this->db->table('zp_mcp_approvals')->where('id', $approvalId)->update($values);
    }
}
