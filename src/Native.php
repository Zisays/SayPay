<?php

require_once('vendor/autoload.php');

use chillerlan\QRCode\QRCode;
use WeChatPay\Builder;
use WeChatPay\Crypto\Rsa;
use WeChatPay\Util\PemUtil;

// 设置参数

// 商户号
$merchantId = '';

// 从本地文件中加载「商户API私钥」，「商户API私钥」会用来生成请求的签名
$merchantPrivateKeyFilePath = 'file://apiclient_key.pem';
$merchantPrivateKeyInstance = Rsa::from($merchantPrivateKeyFilePath, Rsa::KEY_TYPE_PRIVATE);

// 「商户API证书」的「证书序列号」
$merchantCertificateSerial = '';

// 从本地文件中加载「微信支付平台证书」，用来验证微信支付应答的签名
$platformCertificateFilePath = 'file://cert.pem';
$platformPublicKeyInstance = Rsa::from($platformCertificateFilePath, Rsa::KEY_TYPE_PUBLIC);

// 从「微信支付平台证书」中获取「证书序列号」
$platformCertificateSerial = PemUtil::parseCertificateSerialNo($platformCertificateFilePath);

// 构造一个 APIv3 客户端实例
$instance = Builder::factory([
    'mchid'      => $merchantId,
    'serial'     => $merchantCertificateSerial,
    'privateKey' => $merchantPrivateKeyInstance,
    'certs'      => [
        $platformCertificateSerial => $platformPublicKeyInstance,
    ],
]);

try {
    $resp = $instance
        ->chain('v3/pay/transactions/native')
        ->post(['json' => [
            'mchid'        => $merchantId,
            'out_trade_no' => 'native12177525012014070332333',
            'appid'        => '',
            'description'  => '**科技',
            'notify_url'   => 'http://***.com/',
            'amount'       => [
                'total'    => 1,
                'currency' => 'CNY'
            ],
        ]]);

    //echo $resp->getStatusCode(), PHP_EOL;
    //echo $resp->getBody(), PHP_EOL;
} catch (\Exception $e) {
    // 进行错误处理
    echo $e->getMessage(), PHP_EOL;
    if ($e instanceof \GuzzleHttp\Exception\RequestException && $e->hasResponse()) {
        $r = $e->getResponse();
        echo $r->getStatusCode() . ' ' . $r->getReasonPhrase(), PHP_EOL;
        echo $r->getBody(), PHP_EOL, PHP_EOL, PHP_EOL;
    }
    echo $e->getTraceAsString(), PHP_EOL;
}


$data = json_decode($resp->getBody(),true);

// quick and simple:
echo '<img src="'.(new QRCode)->render($data['code_url']).'" alt="QR Code" width = 100 height = 100/>';