<?php

namespace Leantime\Core\Exceptions;

use Illuminate\Translation\ArrayLoader;
use Illuminate\Translation\Translator;
use Illuminate\Validation\Factory as ValidationFactory;
use Throwable;

/**
 * Thrown when user-supplied input fails validation.
 *
 * Renders as HTTP 422 on the web/REST surfaces and JSON-RPC error -32602 ("Invalid params")
 * on /api/jsonrpc, with the per-field errors serialized into the JSON-RPC error `data` member.
 *
 * Per the agreed approach, this is a Leantime-owned type so the whole application sees ONE
 * validation exception — but services may still author rules with Laravel's Validator and let
 * the static validate() bridge run them and rethrow as this type. Field errors are carried as
 * a ['field' => ['message', ...]] map (the same shape Laravel's MessageBag::toArray() produces).
 */
class ValidationException extends LeantimeException
{
    protected int $statusCode = 422;

    protected int $rpcCode = -32602;

    /**
     * @param  array<string, array<int, string>>  $errors  Field => messages map.
     */
    public function __construct(array $errors = [], string $message = 'The given data was invalid.', ?Throwable $previous = null)
    {
        $this->errorData = $errors;
        parent::__construct($message, 0, $previous);
    }

    /**
     * Build directly from a field => messages map.
     *
     * @param  array<string, array<int, string>>  $errors
     */
    public static function withMessages(array $errors): self
    {
        return new self($errors);
    }

    /**
     * Run Laravel validation rules and either return the validated data or throw this
     * exception. The bridge that lets services use Laravel's Validator while the rest of
     * the app only ever handles Leantime's ValidationException.
     *
     * @param  array<string, mixed>  $data
     * @param  array<string, mixed>  $rules
     * @param  array<string, string>  $messages
     * @return array<string, mixed> The validated subset of $data.
     *
     * @throws static
     */
    public static function validate(array $data, array $rules, array $messages = []): array
    {
        // Leantime rebinds the container's "translator" to its own Language class, which is
        // NOT an Illuminate Translator — so the Validator facade cannot be constructed here.
        // Build a self-contained factory instead. (Wiring Leantime's i18n into validation
        // messages is future work; until then a message falls back to the rule key unless an
        // explicit override is passed in $messages.)
        $validator = (new ValidationFactory(new Translator(new ArrayLoader, 'en')))
            ->make($data, $rules, $messages);

        if ($validator->fails()) {
            throw new self($validator->errors()->toArray());
        }

        return $validator->validated();
    }
}
