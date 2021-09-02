<?php

namespace App\Exceptions;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Routing\Router;
use Illuminate\Validation\ValidationException;
use Throwable;

class Handler extends ExceptionHandler
{
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

    /**
     * 重写异常
     * @Author ZhangHQ
     * @param \Illuminate\Http\Request $request
     * @param Throwable $e
     * @return \Illuminate\Http\JsonResponse|\Symfony\Component\HttpFoundation\Response
     * @throws \ReflectionException
     */
    public function render($request, Throwable $e)
    {

        if (method_exists($e, 'render') && $response = $e->render($request)) {

            return Router::toResponse($request, $response);
        } elseif ($e instanceof Responsable) {

            return $e->toResponse($request);
        }

        $e = $this->prepareException($this->mapException($e));

        foreach ($this->renderCallbacks as $renderCallback) {
            if (is_a($e, $this->firstClosureParameterType($renderCallback))) {
                $response = $renderCallback($e, $request);

                if (! is_null($response)) {
                    return $response;
                }
            }
        }

        if ($e instanceof HttpResponseException) {

            return $e->getResponse();
        } elseif ($e instanceof AuthenticationException) {

            return response([
                'code' => 403,
                'messages' => '无权限访问',
                'data' => []
            ], 403);
        } elseif ($e instanceof ValidationException) {

            return $this->convertValidationExceptionToResponse($e, $request);
        }
        return response([
            'code' => 500,
            'messages' => $e->getMessage(),
            'data' => []
        ], 500);
    }
}
