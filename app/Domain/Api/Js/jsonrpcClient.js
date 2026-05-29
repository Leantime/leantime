var leantime = leantime || {};

/**
 * Shared JSON-RPC 2.0 client.
 *
 * Calls a service method exposed via the '/api/jsonrpc' endpoint, addressed as
 * leantime.rpc.{Module}.{Service}.{method}. The endpoint is CSRF-exempt and
 * authenticates via the session cookie, so we only send X-Requested-With.
 *
 * Only service methods annotated with @api are callable (the endpoint enforces this).
 *
 * Usage:
 *   const result = await leantime.rpc('Tickets.Tickets.patchTicket', { id: 5, values: { status: 3 } });
 */
leantime.jsonrpc = (function () {

    /**
     * Invoke a single JSON-RPC method.
     *
     * @param {string} method  - dotted path WITHOUT the leantime.rpc prefix, e.g. 'Tickets.Tickets.patchTicket'
     * @param {object} params  - named parameters matched by name to the service method signature
     * @param {object} options - { id, signal } optional request id and AbortSignal
     * @returns {Promise<*>} resolves to the service return value, rejects with an Error {code, data} on RPC error
     */
    async function call(method, params, options) {
        params = params || {};
        options = options || {};

        const response = await fetch(leantime.appUrl + '/api/jsonrpc', {
            method: 'POST',
            credentials: 'include',
            signal: options.signal,
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: JSON.stringify({
                jsonrpc: '2.0',
                method: 'leantime.rpc.' + method,
                params: params,
                id: typeof options.id !== 'undefined' ? options.id : 1,
            }),
        });

        if (!response.ok) {
            const httpError = new Error('JSON-RPC request failed with HTTP ' + response.status);
            httpError.code = response.status;
            throw httpError;
        }

        const data = await response.json();

        if (data && data.error) {
            const rpcError = new Error(data.error.message || 'JSON-RPC error');
            rpcError.code = data.error.code;
            rpcError.data = data.error.data;
            throw rpcError;
        }

        return data ? data.result : undefined;
    }

    return { call: call };
})();

/**
 * Convenience alias: leantime.rpc('Module.Service.method', params, options) -> Promise.
 */
leantime.rpc = function (method, params, options) {
    return leantime.jsonrpc.call(method, params, options);
};
