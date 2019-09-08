<?php

namespace App\Exceptions;

use App\Http\Controllers\Traits\JsonResponse;
use Exception;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;
use Overtrue\Socialite\AuthorizeFailedException;

class Handler extends ExceptionHandler
{
    use JsonResponse;
    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = [
        'password',
        'password_confirmation',
    ];

    /**
     * Report or log an exception.
     *
     * @param  \Exception  $exception
     * @return void
     */
    public function report(Exception $exception)
    {
        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Exception  $exception
     * @return \Illuminate\Http\Response
     */
    public function render($request, Exception $exception)
    {
        switch (true) {
            case $exception instanceof ValidationException:
                return $this->failed(
                    collect($exception->errors())->first()[0],
                    Response::HTTP_UNPROCESSABLE_ENTITY,
                    Response::HTTP_UNPROCESSABLE_ENTITY
                );
                break;
            case $exception instanceof AuthenticationException:
                return $this->unauthorized('登录认证失败');
                break;
            case $exception instanceof AuthorizeFailedException:
                return $this->unauthorized($exception->getMessage(), $exception->getCode());
                break;
            case $exception instanceof ModelNotFoundException:
                return $this->notFound('请求对象不存在');
                break;
            case $exception instanceof ThrottleRequestsException:
                return $this->failed($exception->getMessage(), $exception->getCode(), $exception->getStatusCode());
                break;
        }
        return parent::render($request, $exception);
    }
}
