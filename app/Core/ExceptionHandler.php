<?php

namespace Leantime\Core;

use Illuminate\Contracts\Debug\ExceptionHandler as ExceptionHandlerContract;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Http\Exceptions\HttpResponseException;
use Throwable;

/**
 * currently super basic exception handler that just pushes it off to symfony
 * @todo Implement a proper exception handler with filp/whoops
 **/
class ExceptionHandler implements ExceptionHandlerContract
{
    /**
     * Report or log an exception.
     *
     * @param  \Throwable $e
     * @return void
     *
     * @throws \Throwable
     */
    public function report(Throwable $e)
    {
        error_log($e);
    }

    /**
     * Determine if the exception should be reported.
     *
     * @param  \Throwable $e
     * @return bool
     */
    public function shouldReport(Throwable $e)
    {
        return true;
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Throwable               $e
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \Throwable
     */
    public function render($request, Throwable $e)
    {
        return match (true) {
            method_exists($e, 'render') && $response = $e->render($request) => $response,
            $e instanceof Responsable => $e->toResponse($request),
            $e instanceof HttpResponseException => $e->getResponse(),
            defined('LEAN_CLI') && LEAN_CLI => app(Template::class)->displayJson((array) $e),
            default => throw $e
        };
    }

    /**
     * Render an exception to the console.
     *
     * @param  \Symfony\Component\Console\Output\OutputInterface $output
     * @param  \Throwable                                        $e
     * @return void
     *
     * This method is not meant to be used or overwritten outside the framework.
     */
    public function renderForConsole($output, Throwable $e)
    {
        (new \Illuminate\Console\Application())->renderThrowable($e, $output);
    }
    
}
