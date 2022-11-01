<?php

namespace App\Exceptions;

use Throwable;
use Carbon\CarbonInterval;
use Illuminate\Support\Facades\Log;
use App\Http\Resources\ErrorCollection;
use Illuminate\Database\QueryException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Validation\ValidationException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;


class Handler extends ExceptionHandler
{
    /**
     * A list of exception types with their corresponding custom log levels.
     *
     * @var array<class-string<\Throwable>, \Psr\Log\LogLevel::*>
     */
    protected $levels = [
        //
    ];

    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<\Throwable>>
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     *
     * @return void
     */
    public function register()
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    public function render($request, Throwable $exception)
    {
        $error = match (true) {
            $exception instanceof ThrottleRequestsException => ["statusCode" => 429,"message" => CarbonInterval::seconds($exception->getHeaders()["Retry-After"])->cascade()->forHumans()],
            $exception instanceof NotFoundHttpException     => ["statusCode" => 404,"message" => "Not found"],
            $exception instanceof QueryException            => ["statusCode" => 500,"message" => "Database Error"],
            $exception instanceof AuthenticationException   => ["statusCode" => 401,"message" => "Unauthenticated"],
            $exception instanceof ValidationException       => ["statusCode" => 422,"message" => (collect($exception->errors())->flatten())->first()],
            $exception instanceof AccessDeniedHttpException => ["statusCode" => 403,"message" => "Access Denied"],
            $exception instanceof AuthorizationException    => ["statusCode" => 403,"message" => "Access Denied"],
            default                                         => ["statusCode" => 404,"message" => "Unknown Error"],
        };

        Log::info(json_encode($error));

        return (new ErrorCollection(collect([])))->additional($error);
    }
}
