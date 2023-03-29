<?php

declare(strict_types=1);

namespace App\Client;

use Exception;
use TencentCloud\Common\Credential;
use TencentCloud\Common\Exception\TencentCloudSDKException;
use TencentCloud\Common\Profile\ClientProfile;
use TencentCloud\Common\Profile\HttpProfile;
use TencentCloud\Tms\V20200713\Models\TextModerationRequest;
use TencentCloud\Tms\V20200713\TmsClient;

class TmsService
{
    private $region;
    private $endpoint;
    private Credential $credential;

    public function __construct($secretId, $secretKey, $endpoint, $region)
    {
        $this->endpoint = $endpoint;
        $this->region = $region;
        // 实例化一个认证对象，入参需要传入腾讯云账户 SecretId 和 SecretKey
        // 密钥可前往官网控制台 https://console.cloud.tencent.com/cam/capi 进行获取
        $this->credential = new Credential($secretId, $secretKey);
    }

    public function checkText(string $content): bool | Exception
    {
        try {
            // 实例化一个http选项，可选的，没有特殊需求可以跳过
            $httpProfile = new HttpProfile();
            $httpProfile->setEndpoint($this->endpoint);

            // 实例化一个client选项，可选的，没有特殊需求可以跳过
            $clientProfile = new ClientProfile();
            $clientProfile->setHttpProfile($httpProfile);
            // 实例化要请求产品的client对象,clientProfile是可选的
            $client = new TmsClient($this->credential, $this->region, $clientProfile);

            // 实例化一个请求对象,每个接口都会对应一个request对象
            $req = new TextModerationRequest();

            $params = [
                "Content" => base64_encode($content),
            ];
            $req->fromJsonString(json_encode($params));

            // 返回的resp是一个TextModerationResponse的实例，与请求对象对应
            $resp = $client->TextModeration($req);
            if ($resp->Suggestion === 'Pass') {
                return true;
            } else {
                return false;
            }
        } catch (TencentCloudSDKException $e) {
            throw $e;
        }
    }
}
