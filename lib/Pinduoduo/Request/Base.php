<?php

namespace Pinduoduo\Request;

/**
 * 基础类
 */
abstract class Base
{

    // 是否需要AccessToken
    public function isNeedAccessToken()
    {
        return true;
    }

    public function getApiMethodName()
    {
        throw new \Exception('function getApiMethodName is not implement');
        // return "taobao.top.auth.token.refresh";
    }

    public function getParams()
    {
        $params = array();
        return $params;
    }

    public function checkParams()
    {
        return true;
    }

    protected function isNotNull($var)
    {
        return !is_null($var);
    }
}
