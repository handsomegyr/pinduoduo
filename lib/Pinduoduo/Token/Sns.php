<?php

namespace Pinduoduo\Token;

class Sns
{
    //商家授权
    const MERCHANT_AUTHORIZE_API_ARR = 'https://mms.pinduoduo.com/open.html';
    //移动端H5授权
    const H5_AUTHORIZE_API_ARR = 'https://mai.pinduoduo.com/h5-login.html';
    //多多客
    const JINBAO_AUTHORIZE_API_ARR = 'https://jinbao.pinduoduo.com/open.html';

    private $_client_id;

    private $_secret;

    private $_redirect_uri;

    private $_state = '';

    private $_view = 'web'; // web对应PC浏览器 or h5对应移动端浏览器

    private $_context;

    public function __construct($client_id, $secret)
    {
        if (empty($client_id)) {
            throw new \Exception('请设定$client_id');
        }
        if (empty($secret)) {
            throw new \Exception('请设定$secret');
        }
        $this->_state = uniqid();
        $this->_client_id = $client_id;
        $this->_secret = $secret;

        $opts = array(
            'http' => array(
                'follow_location' => 3,
                'max_redirects' => 3,
                'timeout' => 10,
                'method' => "POST",
                'header' => "Connection: close\r\n",
                'user_agent' => 'R&D'
            ),
            "ssl" => array(
                "verify_peer" => false,
                "verify_peer_name" => false,
            )
        );
        $this->_context = stream_context_create($opts);
    }

    /**
     * 设定回调地址
     *
     * @param string $redirect_uri            
     * @throws Exception
     */
    public function setRedirectUri($redirect_uri)
    {
        $redirect_uri = trim($redirect_uri);
        if (filter_var($redirect_uri, FILTER_VALIDATE_URL) === false) {
            throw new \Exception('$redirect_uri无效');
        }
        $this->_redirect_uri = urlencode($redirect_uri);
    }


    /**
     * 设定携带参数信息，请使用rawurlencode编码
     *
     * @param string $state            
     */
    public function setState($state)
    {
        $this->_state = $state;
    }

    public function setView($view)
    {
        $this->_view = $view;
    }

    /**
     * 获取认证地址的URL
     */
    public function getAuthorizeUrl($platformUrl, $is_redirect = true)
    {
        // https: //mms.pinduoduo.com/open.html?response_type=code&client_id=4b6**********************672e4c9a&redirect_uri=https://www.oauth.net/2/&state=1212
        $url = "{$platformUrl}?client_id={$this->_client_id}&redirect_uri={$this->_redirect_uri}&response_type=code&state={$this->_state}&view={$this->_view}";
        if (!empty($is_redirect)) {
            header("location:{$url}");
            exit();
        } else {
            return $url;
        }
    }

    /**
     * 获取access token
     *
     * @throws Exception
     * @return array
     */
    public function getAccessToken()
    {
        $code = isset($_GET['code']) ? trim($_GET['code']) : '';
        if ($code == '') {
            throw new \Exception('code不能为空');
        }
        $response = file_get_contents("https://open-api.pinduoduo.com/oauth/token?client_id={$this->_client_id}&client_secret={$this->_secret}&grant_type=authorization_code&code={$code}", false, $this->_context);
        $response = json_decode($response, true);

        return $response;
    }

    /**
     * 通过refresh token获取新的access token
     */
    public function getRefreshToken($refreshToken)
    {
        $response = file_get_contents("https://open-api.pinduoduo.com/oauth/token?client_id={$this->_client_id}&client_secret={$this->_secret}&grant_type=refresh_token&refresh_token={$refreshToken}", false, $this->_context);
        $response = json_decode($response, true);
        return $response;
    }
}
