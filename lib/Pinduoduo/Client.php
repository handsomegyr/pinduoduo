<?php

/**
 * 服务端 API
 * 拼多多开放平台SDK提供了用户授权、授权码刷新、接口访问、消息接收等功能接口。
 * 
 * @author guoyongrong <handsomegyr@126.com>
 *
 */

namespace Pinduoduo;

class Client
{
    // 接口地址
    private $_url = 'https://gw-api.pinduoduo.com/api/router';

    private $sign_method = 'md5';

    private $data_type = 'JSON';

    private $version = 'V1';

    private $_accessToken = null;

    private $_clientId = null;

    private $_clientSecret = null;

    private $_request = null;

    public function __construct($clientId, $clientSecret)
    {
        $this->_clientId = $clientId;
        $this->_clientSecret = $clientSecret;
    }

    public function getClientId()
    {
        if (empty($this->_clientId)) {
            throw new \Exception("请设定clientId");
        }
        return $this->_clientId;
    }

    public function getClientSecret()
    {
        if (empty($this->_clientSecret)) {
            throw new \Exception("请设定clientSecret");
        }
        return $this->_clientSecret;
    }

    /**
     * 获取服务端的accessToken
     *
     * @throws Exception
     */
    public function getAccessToken()
    {
        if (empty($this->_accessToken)) {
            throw new \Exception("请设定access_token");
        }
        return $this->_accessToken;
    }

    /**
     * 设定服务端的access token
     *
     * @param string $accessToken            
     */
    public function setAccessToken($accessToken)
    {
        $this->_accessToken = $accessToken;
        return $this;
    }

    /**
     * 初始化认证的http请求对象
     */
    private function initRequest()
    {
        $this->_request = new \Pinduoduo\Http\Request($this->getAccessToken());
    }

    /**
     * 获取请求对象
     *
     * @return \Pinduoduo\Http\Request
     */
    private function getRequest()
    {
        if (empty($this->_request)) {
            $this->initRequest();
        }
        return $this->_request;
    }

    /**
     * 发送请求
     */
    public function sendRequest(\Pinduoduo\Request\Base $request, array $options = array())
    {
        $params = $request->getParams();
        $apiMethodName = $request->getApiMethodName();
        $needToken = $request->isNeedAccessToken();
        if ($needToken) {
            $params['access_token'] = $this->getAccessToken();
        }
        $params['client_id'] = $this->getClientId();
        $params['sign_method'] = $this->sign_method;
        $params['type'] = $apiMethodName;
        $params['data_type'] = $this->data_type;
        $params['timestamp'] = strval(time());
        $params['sign'] = $this->signature($params);

        $headers = array();
        $rst = $this->getRequest()->post($this->_url . $apiMethodName, $params, $headers);
        return $this->rst($rst);
    }

    /**
     * @param $params
     *
     * @return string
     */
    private function signature($params)
    {
        ksort($params);
        $paramsStr = '';
        array_walk($params, function ($item, $key) use (&$paramsStr) {
            if ('@' != substr($item, 0, 1)) {
                $paramsStr .= sprintf('%s%s', $key, $item);
            }
        });
        return strtoupper(md5(sprintf('%s%s%s', $this->getClientSecret(), $paramsStr, $this->getClientSecret())));
    }

    /**
     * 标准化处理服务端API的返回结果
     */
    public function rst($rst)
    {
        return $rst;
    }
}
