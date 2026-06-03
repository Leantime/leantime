<?php

namespace Leantime\Core\Plugins\Attributes;

/**
 * Marks a JSON-RPC service method (or whole class) as requiring a named plugin to be enabled.
 *
 * Enforced at the JSON-RPC dispatch boundary. When the named plugin is disabled, calls return
 * a clean JSON-RPC error (-32004) in the response body with HTTP 200 — mirroring the convention
 * used by AuthorizationException / NotFoundException.
 *
 * Class-level usage gates every method on the class; method-level usage overrides per-method.
 *
 * Single-plugin gating only — the attribute is NOT `IS_REPEATABLE`, so PHP rejects
 * multiple `#[RequiresPlugin(...)]` on the same target at the language level. If a
 * future tool legitimately needs to require several plugins together, mark this
 * attribute repeatable and update the Jsonrpc dispatcher to iterate all instances
 * (AND-semantics — every named plugin must be enabled).
 *
 * Client surfaces (mobile, MCP) should query the core JSON-RPC `config.getSystemInfo` method
 * to gate UI client-side so the user never reaches a denied call. The attribute is the
 * server-side defense; the capabilities response is the client-side prevention.
 */
#[\Attribute(\Attribute::TARGET_METHOD | \Attribute::TARGET_CLASS)]
class RequiresPlugin
{
    public function __construct(public string $pluginName) {}
}
