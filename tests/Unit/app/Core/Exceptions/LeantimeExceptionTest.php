<?php

namespace Unit\app\Core\Exceptions;

use Leantime\Core\Exceptions\AuthException;
use Leantime\Core\Exceptions\AuthorizationException;
use Leantime\Core\Exceptions\Contracts\LeantimeExceptionInterface;
use Leantime\Core\Exceptions\EntityExistsException;
use Leantime\Core\Exceptions\InvalidArgumentException;
use Leantime\Core\Exceptions\MissingParameterException;
use Leantime\Core\Exceptions\NotFoundException;
use Leantime\Core\Exceptions\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Unit\TestCase;

/**
 * The typed exception hierarchy: each carries its own HTTP status (honored by the global
 * ExceptionHandler because it implements HttpExceptionInterface) and JSON-RPC error code
 * (read by JsonRpcErrorResponse). Auth + validation are modeled as exceptions per the design.
 */
class LeantimeExceptionTest extends TestCase
{
    public function test_authorization_exception_carries_403_and_rpc_auth_code(): void
    {
        $e = new AuthorizationException;

        $this->assertInstanceOf(LeantimeExceptionInterface::class, $e);
        $this->assertInstanceOf(HttpExceptionInterface::class, $e);
        $this->assertSame(403, $e->getStatusCode());
        $this->assertSame(-32001, $e->getRpcCode());
        $this->assertSame([], $e->getErrorData());
        $this->assertNotSame('', $e->getClientMessage());
    }

    public function test_not_found_exception_carries_404(): void
    {
        $e = new NotFoundException;

        $this->assertSame(404, $e->getStatusCode());
        $this->assertSame(-32002, $e->getRpcCode());
    }

    public function test_validation_exception_carries_422_field_errors_and_invalid_params_code(): void
    {
        $errors = ['headline' => ['The headline is required.']];
        $e = ValidationException::withMessages($errors);

        $this->assertSame(422, $e->getStatusCode());
        $this->assertSame(-32602, $e->getRpcCode());
        $this->assertSame($errors, $e->getErrorData());
    }

    public function test_validate_bridge_returns_validated_data_on_success(): void
    {
        $validated = ValidationException::validate(
            ['name' => 'Acme', 'extra' => 'ignored'],
            ['name' => 'required|string'],
        );

        // validated() returns only the validated keys.
        $this->assertSame(['name' => 'Acme'], $validated);
    }

    public function test_validate_bridge_throws_leantime_type_with_field_errors_on_failure(): void
    {
        try {
            ValidationException::validate(['name' => ''], ['name' => 'required']);
            $this->fail('Expected a ValidationException to be thrown.');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('name', $e->getErrorData());
            $this->assertSame(-32602, $e->getRpcCode());
        }
    }

    public function test_retrofitted_exceptions_expose_http_status_and_rpc_code(): void
    {
        $this->assertSame(409, (new EntityExistsException)->getStatusCode());
        $this->assertSame(-32005, (new EntityExistsException)->getRpcCode());

        $this->assertSame(422, (new InvalidArgumentException)->getStatusCode());
        $this->assertSame(-32602, (new InvalidArgumentException)->getRpcCode());

        $missing = new MissingParameterException('x missing');
        $this->assertSame(422, $missing->getStatusCode());
        $this->assertSame(-32602, $missing->getRpcCode());
        $this->assertInstanceOf(LeantimeExceptionInterface::class, $missing);
    }

    public function test_retrofitted_exception_preserves_legacy_get_code(): void
    {
        // BC: the HTTP status historically lived in getCode(); keep it readable there too.
        $this->assertSame(409, (new EntityExistsException('dupe'))->getCode());
    }

    public function test_auth_exception_is_a_deprecated_authorization_alias(): void
    {
        $e = new AuthException('Invalid domain user');

        // It IS an AuthorizationException (a deprecated alias, not a second auth exception),
        // so it keeps the 403 status + -32001 rpc code while the AdvancedAuth plugin and
        // external installs that still throw the old class name keep working.
        $this->assertInstanceOf(AuthorizationException::class, $e);
        $this->assertSame(403, $e->getStatusCode());
        $this->assertSame(-32001, $e->getRpcCode());
        // Legacy ($message, $code) signature preserved, including getCode().
        $this->assertSame('Invalid domain user', $e->getMessage());
        $this->assertSame(403, $e->getCode());
    }
}
