<?php

namespace App\Http\Controllers\Traits;

use Illuminate\Support\Facades\Response;
use Symfony\Component\HttpFoundation\Response as FoundationResponse;

trait JsonResponse
{
    /**
     * @var int
     */
    protected $statusCode = FoundationResponse::HTTP_OK;

    /**
     * @var array 
     */
    protected $header = [];

    /**
     * @var array
     */
    public static $resourceAdditionalMeta = [
        'msg' => 'success',
        'error' => 0,
    ];

    /**
     * @return int
     */
    public function getStatusCode()
    {
        return $this->statusCode;
    }

    /**
     * @param $statusCode
     * @return $this
     */
    public function setStatusCode($statusCode)
    {
        $this->statusCode = $statusCode;
        return $this;
    }

    /**
     * @return array
     */
    public function getHeader()
    {
        return $this->header;
    }

    /**
     * @param array $header
     * @return $this
     */
    public function setHeader(array $header)
    {
        $this->header = $header;
        return $this;
    }

    /**
     * @param $data
     * @return mixed
     */
    public function respond($data)
    {
        return Response::json($data, $this->getStatusCode(), $this->getHeader());
    }

    /**
     * @param $msg
     * @param int $error
     * @param array|string $data
     * @return mixed
     */
    public function status($msg, $error = 0, $data = [])
    {
        $status = [
            'msg' => $msg,
            'error' => $error,
        ];

        if ($data) $status['data'] = $data;
        return $this->respond($status);
    }

    /**
     * @param $msg
     * @param int $error
     * @param array $data
     * @return mixed
     */
    public function message($msg, $error = 0, $data = [])
    {
        return $this->status($msg, $error, $data);
    }

    /**
     * @param $msg
     * @param $error
     * @param int $statusCode
     * @param array $data
     * @return mixed
     */
    public function failed($msg, $error, $statusCode = FoundationResponse::HTTP_BAD_REQUEST, $data =[])
    {
        return $this->setStatusCode($statusCode)->message($msg, $error, $data);
    }

    /**
     * @param $data
     * @param string $msg
     * @return mixed
     */
    public function success($data, $msg = 'success')
    {
        return $this->status($msg, 0, $data);
    }

    /**
     * @param string $msg
     * @param array $data
     * @return mixed
     */
    public function created($msg = 'created', $data = [])
    {
        return $this->setStatusCode(FoundationResponse::HTTP_CREATED)->message($msg, 0, $data);
    }

    /**
     * @param string $msg
     * @param int $code
     * @param array $data
     * @return mixed
     */
    public function unauthorized($msg = 'Unauthorized', $code = FoundationResponse::HTTP_UNAUTHORIZED, $data = [])
    {
        return $this->failed($msg, $code, FoundationResponse::HTTP_UNAUTHORIZED, $data);
    }

    /**
     * @param string $msg
     * @param int $code
     * @param array $data
     * @return mixed
     */
    public function forbidden($msg = 'Forbidden', $code = FoundationResponse::HTTP_FORBIDDEN, $data = [])
    {
        return $this->failed($msg, $code, FoundationResponse::HTTP_FORBIDDEN, $data);
    }

    /**
     * @param string $msg
     * @param int $code
     * @param array $data
     * @return mixed
     */
    public function notFound($msg = 'Not Found', $code = FoundationResponse::HTTP_NOT_FOUND, $data = [])
    {
        return $this->failed($msg, $code, FoundationResponse::HTTP_NOT_FOUND, $data);
    }

    public function invalidation($msg = 'Invalidation', $code = FoundationResponse::HTTP_UNPROCESSABLE_ENTITY, $data = [])
    {
        return $this->failed($msg, $code, FoundationResponse::HTTP_UNPROCESSABLE_ENTITY, $data);
    }

    /**
     * @param string $msg
     * @param int $code
     * @param array $data
     * @return mixed
     */
    public function internalError($msg = 'Internal Error', $code = FoundationResponse::HTTP_INTERNAL_SERVER_ERROR, $data = [])
    {
        return $this->failed($msg, $code, FoundationResponse::HTTP_INTERNAL_SERVER_ERROR, $data);
    }
}
