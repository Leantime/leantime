# MCP

## Endpoint
- MCP endpoint: `POST /mcp`
- Health probe: `GET /mcp`
- Transport: JSON-RPC 2.0 request/response payloads

## Authentication
- MCP uses bearer tokens, not legacy `x-api-key` API users.
- Header: `Authorization: Bearer <token>`
- Minimum ability to connect: `mcp:connect`

## Token Issuance
- Create a token with the built-in console command:

```bash
php bin/leantime mcp:token:create test@leantime.io --preset=read-only --name=ops-agent
```

- List a user's MCP tokens:

```bash
php bin/leantime mcp:token:list test@leantime.io
```

- Revoke an MCP token by id:

```bash
php bin/leantime mcp:token:revoke 12 --email=test@leantime.io
```

- Optional abilities can be added with repeated `--ability` flags.
- Optional expiry can be set with `--expires-days=<n>`.

### Ability Presets
- `read-only`
  - `mcp:connect`
  - `mcp:read`
  - `projects:read`
  - `tickets:read`
  - `comments:read`
  - `users:read`
- `ticket-writer`
  - `mcp:connect`
  - `mcp:read`
  - `mcp:write`
  - `projects:read`
  - `projects:write`
  - `tickets:read`
  - `tickets:write`
  - `comments:read`
  - `comments:write`
  - `users:read`
- `time-writer`
  - `mcp:connect`
  - `mcp:read`
  - `mcp:write`
  - `projects:read`
  - `tickets:read`
  - `timesheets:write`
  - `comments:read`
  - `users:read`
- `full-agent`
  - `mcp:connect`
  - `mcp:read`
  - `mcp:write`
  - `projects:read`
  - `projects:write`
  - `tickets:read`
  - `tickets:write`
  - `comments:read`
  - `comments:write`
  - `users:read`
  - `timesheets:write`
- `admin`
  - `*`

## Request Headers
- `Authorization: Bearer <token>`
- `Content-Type: application/json`
- `Idempotency-Key: <key>`
  - Required for mutating tools.
- `Mcp-Session-Id: <client-session-id>`
  - Optional but recommended for correlating client activity.
- `X-Request-Id: <request-id>`
  - Optional. If omitted, the server generates one.
- `X-Correlation-Id: <correlation-id>`
  - Optional. Useful when one client action fans out into multiple MCP calls.

## Current Tools
- `projects.list`
- `projects.get`
- `projects.members`
- `projects.create`
- `projects.update`
- `projects.members.update`
- `tickets.create`
- `tickets.get`
- `tickets.search`
- `tickets.update`
- `tickets.update_status`
- `comments.list`
- `comments.add`
- `comments.delete`
- `users.get`
- `timesheets.log`
- `operations.get`
- `approvals.list`
- `approvals.resolve`

## Tool Contracts
- Tool names are stable contract names such as `tickets.search`.
- Each tool exposes a schema through `tools/list`.
- Each tool currently reports contract version `1.0.0` in its annotations.
- Breaking changes should create a new tool version and preserve existing contract names until clients migrate.

## Agent Access
- `full-agent` is the recommended non-wildcard preset for local autonomous agents.
- `admin` grants `*` and is broader than most local agents should need.
- Current preset coverage:
  - `read-only`: all read tools only
  - `ticket-writer`: project, ticket, and comment writes, but not timesheets
  - `time-writer`: timesheet writes, but not project/ticket/comment writes
  - `full-agent`: all currently implemented scoped MCP tools

## Approval Policy
- Read tools do not require approval.
- Mutating tools require an `Idempotency-Key`.
- Mutating tools can request approval by passing:
  - `_approvalMode: request`
  - `_approvalReason: <human-readable reason>`
- Managers, admins, and owners can resolve approvals with `approvals.resolve`.
- Rejected approvals mark the linked MCP operation as rejected and release its idempotency lifecycle.

## Async Policy
- Tools that support background execution accept `_async: true`.
- Async calls return a queued operation payload immediately.
- Poll completion with `operations.get`.
- Async execution uses the existing default queue worker path.

## Observability
- MCP request and tool-call metadata is stored in:
  - `zp_mcp_requests`
  - `zp_mcp_tool_calls`
  - `zp_mcp_idempotency_keys`
  - `zp_mcp_approvals`
  - `zp_mcp_agents`
- JSON-RPC responses on the legacy `/api/jsonrpc` endpoint advertise `/mcp` as the preferred automation successor.

## Examples
### Initialize

```json
{
  "jsonrpc": "2.0",
  "id": 1,
  "method": "initialize",
  "params": {
    "protocolVersion": "2025-03-26"
  }
}
```

### List Tools

```json
{
  "jsonrpc": "2.0",
  "id": 2,
  "method": "tools/list"
}
```

### Call A Read Tool

```json
{
  "jsonrpc": "2.0",
  "id": 3,
  "method": "tools/call",
  "params": {
    "name": "tickets.search",
    "arguments": {
      "projectId": 1,
      "term": "onboarding",
      "limit": 10
    }
  }
}
```

### Request Approval For A Write Tool

```json
{
  "jsonrpc": "2.0",
  "id": 4,
  "method": "tools/call",
  "params": {
    "name": "timesheets.log",
    "arguments": {
      "ticketId": 10,
      "kind": "GENERAL_BILLABLE",
      "hours": 2,
      "dateString": "2026-05-15 09:00:00",
      "_approvalMode": "request",
      "_approvalReason": "Backfill approved support work"
    }
  }
}
```

### Approve A Pending Operation

```json
{
  "jsonrpc": "2.0",
  "id": 5,
  "method": "tools/call",
  "params": {
    "name": "approvals.resolve",
    "arguments": {
      "approvalId": 42,
      "decision": "approve"
    }
  }
}
```
