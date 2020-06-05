<?php

return [
    'adminEmail' => 'admin@example.com',
    'testKey' => 'testValue',
    'APP_ID' => '1400071200', //腾讯云 短信 appid
    'APP_KEY' => 'b3db056fb6e021a769cb65ee48275919', //腾讯云 短信 appkey
    'JWT_KEY_ADMIN' => 'juanpao', //JWT密钥
    'JWT_KEY_FORUM' => 'communityforum', //社区密钥
    'JWT_KEY_MERCHANT' => 'merchant', //社区密钥
    'JWT_KEY_SHOP' => 'shop', //社区密钥
    'JWT_KEY_SUPPLIER' => 'supplier', //供应商
    'JWT_KEY_PARTNER' => 'partner', //合伙人
//    //原测试公众号配置 无后台
//    'wx_config' => [
//        'app_id' => 'wx0e4e3c478dafdc44',                   //AppID
//        'secret' => 'db54e48fe228affa68c43a26c1200112',     //AppSecret
//        'token' => 'jUAMXy7itmAtu4EtmF_LyzcPIlIxN6k0RkTxE0yEmrqaIJkA2xWKNLhJmCoEcl88izEZwz2uGt4SfxV4f7Vck0YR8NFtpou28iEE7e9sUX_kJ2NyFSa5SvIWa3cHsEZPgADARMC',//Token
//        'aes_key' => '',                                    //EncodingAESKey，兼容与安全模式下请一定要填写
//        /**
//         * 指定api调用返回结果的类型:array(default)/collection/onject/raw/自定义类名
//         * 使用自定义类名时，构造函数将会接受一个 `EasyWeChat\Kernel\Http\Response`实例
//         */
//        'response_type' => 'array'
//    ],
//先公众号配置 有后台
    'wx_config' => [
        'APPID' => 'wx52095822757a8bf0', //AppID
        'MCHID' => '1496441282', //MCHID
        'KEY' => 'pp4djnjw4k2orevhobgaxqiuyljhbcz5', //KEY
        'APPSECRET' => 'f8714328c618aecd1bbc1f2ea4d25f19', //APPSECRET
        'SSLCERT_PATH' => './uploads/pem/1/15311933156653.pem', //SSLCERT_PATH
        'SSLKEY_PATH' => 'data:application/octet-stream;base64,LS0tLS1CRUdJTiBQUklWQVRFIEtFWS0tLS0tCk1JSUV2QUlCQURBTkJna3Foa2lHOXcwQkFRRUZBQVNDQktZd2dnU2lBZ0VBQW9JQkFRRHMrblgxaHhGU0w1L2EKL2p1Mm9OejlWQTNIZGVWN3Jva2FXN0Joa3dCV2FFa3FsYStVcUZDUGZjb2xDV2Nmei81WUY4cGx3WE40Q3JiUwpqSTVJM1RXUHROQ3Noa3NJRmtPSEhwMTJiNGwvUUlqUTEvK3BiQXZab3E5aSs4dXBOS2R4UERoS2tEWVhHaFZjClZKdHZMV1RUZ2h5NklzQVlhSXlTNmE3RjdiKzhhMVN5VjBFb0FGTDlraTNqa041SmFYN20vcmExT3dLakxUM3MKQ1Z3UFJ2UkNNd2tWNjVPb3VVU2psai9mNHVKQUUxRVdIWWpGMDBIWWNMNlR1UUsvSHE1RGFqSWlHbHdDbmVRegp6aEFRNXArYmxKSWwrZlZITmRERlgrV3lIUTBHYVJJZlpUa1FjdkFVejBYejRDdjMzUjZRMDFOOXpIcDdLcFBwClZYSzFCN1cxQWdNQkFBRUNnZjg1Wkc4dEI0a3FYbDVZcXpuTEFTcUVMOWNtZDJjY2pTaW5PWTErRkJ3QTBVRG4KRDFsMnAwemJjNXVCWE1XYzdzS3FreGh4akdocXFpMmMwRzJsTiszQTRBbXB2dHh0R1BkQndpaGdocUxHTHRyegp1Vnd4cS9reXZETzl4VDJOdlRMUU9jVm9TWVRRRTRFWTIyVEppRFRqQkovd2RIb1JiMENjVS92Lzg1UjhEUXNuCjlrTmdmY2FteFN0YVZWelY0TG5iRCtmcVA5MnBqZzZ0SnQ2Z3FhUnJrWmVKU0JFd3pSWUJoYkQ4Mmp6VDA1cTEKWVBhUElMTFJrbWFiUk9ZR0s1emhXa1NLMUVsblVIclAwVmhXNTM0dnQ1TEZEeUFjZmswVVdGOElZRDR0S1l0RgpiZjZzTVA2RVdRaVIwWThsSUpBc0hDRlpQbllMdTJNL3lQMHUzMEVDZ1lFQTkyRVkzUUJMS3cza2NIODZHZmx3ClV1d1czaFl0Y0xwOTJCaUR6TUZrQ0VlaXZBb3k5TlVRcnBndTdjZUVDZVcxeEhiNjdYTFVXT3B2Q2hRdFpRc0sKQU4vK1Y3MUVIVDEzWlFnNUg0WFdMRWx4VWd5M1VjNEhNdXJGUXVJR2U2NTJ3cXhXUWFPQTAwMXVhVVR5S3JnTgorRU1wR3dZcWNDTThqcTl3TUNPYVBNMENnWUVBOVR5VFhxQjJsdFNtT01FN0NjNzYrNGdxNVJwSVIvTlZ0M093CnVZRmRyYk4vdkJpaGU5aFUzUWprY1FXYjJheXVXTjBqOW10bEVuRUU3WkZzb0o4bXNVN3JQZXl4eWpJbStvVXgKTWZWanVTUFo2NElCQWJ6U0V6c0lRa2pmd0p4M2wvdHMxZmlRWkMweEszbmM5UU1yTVhRdEdhdkQyN282cnBnbQpWMWd4M0lrQ2dZQmNESE0xK04vL2UwSGZZbGY2UmtpM2NrWG9DWlNLOTduUDZQOVI5endEb0xRN0NBaUI5YTRwCmFWTTlBeHBzbkY4UVpiMWxFVzlXVHBWV3lMOURjK0liQlQ0YzQ3NHVxeXA4RzUwMXo1VXNFWC9yS2ZRa3FtY2YKV1NCaGpMMGcxSUE0Vyt3VXJJUkpHK3pUbXVZSlkwYy9jNjRkOGlOa2FwZ2o5Ny9sQ2JwZ21RS0JnUUNoMUJKdgpuRFlGZkZnZ0Zhc080dTRPZENIU1ExQzFZaWNMUXlXNGxGeXNGa3BSWm5PUmxRVVRReDMwVXo0d0cxcUZ1NUJTCnVUWGVRSVIrL0xzUkYxVGlKbkRuMFR6VmI5ajI4bE92WXY0bW16amZ4MDlBeFVoZmRsSVI0Nkw3cUlUbTN2eEkKQ3BuRjhXaUVCd3UvQnhOR3RDSmEwVlVTdDBhb0ZqRWU3RTBpK1FLQmdRREZJQ3JqaTlUV3cyZGZCT2xVeVZPdgo4NjljY01jSEc1V3VKRVJGRG0yQTZpcThiMTFQRHNDR056YXYzMmRqQTFET1hueWpjWlExRFF6UmN4cy9laENPCnhSTVZaNGFWMWkzNzRHRThVTjA4T1lhRWo0cHdpMVhINEJibWl4OW1RdVk1L2l0eURCdHRGYjBuclJqSExkT3UKMGs4T2RSYitWUUgzalNqNFBDNHpSUT09Ci0tLS0tRU5EIFBSSVZBVEUgS0VZLS0tLS0K', //SSLKEY_PATH
        "REPORT_LEVENL" => "1",
        "CURL_PROXY_HOST" => "0.0.0.0",
        "CURL_PROXY_PORT" => "0",
//        'token' => '11_8ojrNlma9QPXVMP1r6DmNMCYldBtvcboYqWq70_8X2uTN2egIlJye1YsFvV69Ko4ILrIjWCxfTlYZH2N1nHKPw9BXQOTI-IaskP6SxHfEO49I_VL4OwI7aBSyiMQDTeAJACPF',//Token
//        'aes_key' => 'wzwBBeNpFi409UVnLzuTn0u90U0YbyVBqZBa2JALf00',                                    //EncodingAESKey，兼容与安全模式下请一定要填写
        /**
         * 指定api调用返回结果的类型:array(default)/collection/onject/raw/自定义类名
         * 使用自定义类名时，构造函数将会接受一个 `EasyWeChat\Kernel\Http\Response`实例
         */
        'log' => [
            'level' => 'debug',
            'file' => __DIR__ . '/wechat.log',
        ],
    ],
    //插件配置
    'unit' => [
        'copyright' => [
            'title' => '自定义版权',
            'route' => 'copyright',
            'pic_url' => "https://api.juanpao.com/uploads/copyright.png",
            'price' => 1,
        ],
        'domainName' => [
            'title' => '自定义域名',
            'route' => 'domainName',
            'pic_url' => "http://juanpao999-1255754174.cos.cn-south.myqcloud.com/merchantapp%2F2019%2F02%2F01%2F15490128485c540f701faf6.png",
            'price' => 1,
        ],
        'signIn' => [
            'title' => '签到',
            'route' => 'signIn',
            'pic_url' => "http://juanpao999-1255754174.cos.cn-south.myqcloud.com/merchantapp%2F2019%2F02%2F01%2F15490128485c540f701faf6.png",
            'price' => 1,
        ],
        'integralMall' => [
            'title' => '积分商城',
            'route' => 'integralMall',
            'pic_url' => "http://juanpao999-1255754174.cos.cn-south.myqcloud.com/merchantapp%2F2019%2F02%2F01%2F15490128485c540f701faf6.png",
            'price' => 1,
        ],
        'group' => [
            'title' => '团购',
            'route' => 'group',
            'pic_url' => "http://juanpao999-1255754174.cos.cn-south.myqcloud.com/merchantapp%2F2019%2F02%2F01%2F15490128485c540f701faf6.png",
            'price' => 1,
        ]
    ],
    'copyright' => 'https://api.juanpao.com/uploads/copyright.png',
    'domainName' => '',
    'signIn' => ['status' => 0],
    'integralMall' => ['status' => 0],
    'theme' => ['theme' => 'ff5903', 'theme_text' => 'black', 'navigation' => '[{"name":"首页","filePut":"http://juanpao999-1255754174.cos.cn-south.myqcloud.com/system%2Ftheme%2F13%2F2019%2F03%2F01%2F15514338815c7900994f221.png","filePutSelection":"http://juanpao999-1255754174.cos.cn-south.myqcloud.com/system%2Ftheme%2F13%2F2019%2F03%2F01%2F15514338815c7900998996c.png","choice_page_name_view":"常用链接--首页","choice_page_type":"1","choice_page_name":"首页","choice_app_id":"","choice_page_url":"/pages/index/index/index"},{"name":"购物车","filePut":"http://juanpao999-1255754174.cos.cn-south.myqcloud.com/system%2Ftheme%2F13%2F2019%2F03%2F01%2F15514338815c790099ba266.png","filePutSelection":"http://juanpao999-1255754174.cos.cn-south.myqcloud.com/system%2Ftheme%2F13%2F2019%2F03%2F01%2F15514338815c790099dbb37.png","choice_page_name_view":"常用链接--购物车","choice_page_type":"1","choice_page_name":"购物车","choice_app_id":"","choice_page_url":"/pages/shopCart/shopCart/shopCart"},{"name":"我的","filePut":"http://juanpao999-1255754174.cos.cn-south.myqcloud.com/system%2Ftheme%2F13%2F2019%2F03%2F01%2F15514338825c79009a0e32c.png","filePutSelection":"http://juanpao999-1255754174.cos.cn-south.myqcloud.com/system%2Ftheme%2F13%2F2019%2F03%2F01%2F15514338825c79009a2b873.png","choice_page_name_view":"常用链接--我的","choice_page_type":"1","choice_page_name":"我的","choice_app_id":"","choice_page_url":"/pages/home/my/my"}]', 'bottom_text' => '#000', 'text_selection' => '#ff5903'],
    //公众号链接pei'z
    'wechat_link' => [
        ["name" => "首页",
            "url" => "/pages/index/index/index",
            "introduce" => "",
            "proposal" => ''
        ],
        [
            "name" => "我的",
            "url" => "/pages/home/my/my",
            "introduce" => "",
            "proposal" => ''
        ],
        [
            "name" => "购物车",
            "url" => "/pages/shopCart/shopCart/shopCart",
            "introduce" => "",
            "proposal" => ''
        ],
        [
            "name" => "订单列表",
            "url" => "/pages/order/order/order",
            "introduce" => "",
            "proposal" => ''
        ],
        [
            "name" => "优惠券",
            "url" => "/pages/home/coupons/coupons/coupons",
            "introduce" => "",
            "proposal" => ''
        ],
        [
            "name" => "领券中心",
            "url" => "/pages/home/couponRedemptionCenter/couponRedemptionCenter",
            "introduce" => "",
            "proposal" => ''
        ],
    ],
    //小程序链接配置
    'program_link' => [
        'often_link' => [
            ["name" => "首页",
                "url" => "/pages/index/index/index",
                "introduce" => "",
                "proposal" => ''
            ],
            [
                "name" => "我的",
                "url" => "/pages/home/my/my",
                "introduce" => "",
                "proposal" => ''
            ],
            [
                "name" => "购物车",
                "url" => "/pages/shopCart/shopCart/shopCart",
                "introduce" => "",
                "proposal" => ''
            ],
            [
                "name" => "订单列表",
                "url" => "/pages/order/order/order",
                "introduce" => "",
                "proposal" => ''
            ],
            [
                "name" => "优惠券",
                "url" => "/pages/home/coupons/coupons/coupons",
                "introduce" => "",
                "proposal" => ''
            ],
            [
                "name" => "领券中心",
                "url" => "/pages/home/couponRedemptionCenter/couponRedemptionCenter",
                "introduce" => "",
                "proposal" => ''
            ],
            [
                "name" => "秒杀",
                "url" => "/pages/seckill/seckill/seckill",
                "introduce" => "",
                "proposal" => ''
            ],
            [
                "name" => "签到",
                "url" => "/pages/clockIn/clockIn/clockIn",
                "introduce" => "",
                "proposal" => ''
            ],
            [
                "name" => "申请团长",
                "url" => "/group/creategroup/creategroup",
                "introduce" => "",
                "proposal" => ''
            ],
            [
                "name" => "申请门店",
                "url" => "/pages/supplier/supplierbrochure/supplierbrochure",
                "introduce" => "",
                "proposal" => ''
            ],
            [
                "name" => "商品总分类",
                "url" => "/pages/classification/classification/classification",
                "introduce" => "",
                "proposal" => ''
            ],
            [
                "name" => "积分商城",
                "url" => "/pages/integralMall/index/index/index",
                "introduce" => "",
                "proposal" => ''
            ],
            [
                "name" => "砍价首页",
                "url" => "/bargaining/pages/bargaining/Index/Index",
                "introduce" => "",
                "proposal" => ''
            ],
            [
                "name" => "直播列表",
                "url" => "/pages/live/live",
                "introduce" => "",
                "proposal" => ''
            ],
            [
                "name" => "门店列表",
                "url" => "/supplier/list/list",
                "introduce" => "",
                "proposal" => ''
            ],
            [
                "name" => "资质",
                "url" => "/pages/home/qualificationDetail/qualificationDetail?key='qualifications'",
                "introduce" => "",
                "proposal" => ''
            ],
            [
                "name" => "隐私政策",
                "url" => "/pages/home/qualificationDetail/qualificationDetail?key='privacy_policy'",
                "introduce" => "",
                "proposal" => ''
            ],
            [
                "name" => "用户协议",
                "url" => "/pages/home/qualificationDetail/qualificationDetail?key='user_protocol'",
                "introduce" => "",
                "proposal" => ''
            ],

        ],
        'type_link' => "/pages/goodsClassify/goodsClassify/goodsClassify",
        'goods_link' => "/pages/goodsItem/goodsItem/goodsItem",
    ],
//    //洪尧公众号配置 有后台
//    'wx_config' => [
//        'app_id' => 'wx77ed974c23c54a7d',                   //AppID
//        'secret' => '64b50927543d3122a845a8639983696a',     //AppSecret
////        'token' => '11_8ojrNlma9QPXVMP1r6DmNMCYldBtvcboYqWq70_8X2uTN2egIlJye1YsFvV69Ko4ILrIjWCxfTlYZH2N1nHKPw9BXQOTI-IaskP6SxHfEO49I_VL4OwI7aBSyiMQDTeAJACPF',//Token
//        'aes_key' => '6OCpXA1adLNidXkBUSCmVIqv58BlKIQsU8Apdp2bApj',                                    //EncodingAESKey，兼容与安全模式下请一定要填写
//        /**
//         * 指定api调用返回结果的类型:array(default)/collection/onject/raw/自定义类名
//         * 使用自定义类名时，构造函数将会接受一个 `EasyWeChat\Kernel\Http\Response`实例
//         */
//        'log' => [
//            'level' => 'debug',
//            'file' => __DIR__.'/wechat.log',
//        ],
//    ],
//    //水果乐 小程序配置 有后台
//    'mp_config' => [
//        'app_id' => 'wx7872d106db436d89',
//        'secret' => 'f9836294311473f814a6d46c7c751e5d',
//        // 下面为可选项
//        // 指定 API 调用返回结果的类型：array(default)/collection/object/raw/自定义类名
//        'response_type' => 'array',
//
//        'log' => [
//            'level' => 'debug',
//            'file' => __DIR__.'/wechat.log',
//        ],
//    ],
//支付宝配置
    'ali_config' => [
        'app_id' => "2017090808622215",
        //商户私钥
        'merchant_private_key' => "MIIEogIBAAKCAQEAoVs6jLhM+hxBeTz2DBZGqsAPSlxrSEKk2RcGQe1Zqqj+kBh9RVuQ3TdaleyckDQD1o5213vk91fJsT7crwNEAW+2j2Tag1CT3wG+wlfLnI97axMNAA6dJDB5eEI4njFRRdX0W5FPuYqRIUhI5WW1Sk++NXlyyKx8Z+b6MgAIiq7cjZpojVkSdqaH0+a+Bc6qFDh8F3Oz+iHs4lWpzoXoNHKgTWRAcjZgDNnalLh9UmjUPeMQpC06orbqQHYahbwrjwh+VZeUSxN+sL/l6lrm6dL6YqOf02Z321NYAoyRdOTQ+raVb4eaQEL/cMzMCQzuBE2ctCED0LYAqqST1yZ3AwIDAQABAoIBABPgUgkNluXkXyhZGxIIGHJmMDv/wHNpSjc3v9yVKUt9f8YuThgiHGkzrWP0fqDA14wxhnAq4dyaIs1Dqhmg9Fqc8UcerFAqt9xrsZztBbbmcdSRxzMvmangksX+mkzaVGGe5nf1IXYAnLoV1mzzp25c/lhF+p6/qJ9/82f/Ww4CJCZHTjW+jd6wS9KPG0sCekRgEOz3671hP741cY+3kpXkk55X/KxKZJcwivZbdc5fg9GddE6KHA1RXrVCnDljYU6mLcThxv3jyWoGp4Lert9yZBoP09CypVUVHcSGe9bw+4UO/no6T3Gd8QWJD/gA8i6TUFbi++10a5naA1DEYrkCgYEA1QxTFT9S3JM9EH5TzpFpD5xOxKSGtQri99luARTfoInQRWfyIh91Oah/MtmXtEnYGO3Sqr+a9bBtK/bVO2vHCgRpnU6fw38R/MSfUF5Lz4GR0EYUawkX9skAO196050MRL020TpRrVIquJnqbce2qaFl4/VBchwooU0uz7Iv3BcCgYEAweMHMOWsCaTvztQaBwMSAV/FRYLxyxwd6SkV1knNRkGqteBqkh8owJ01AoWOLotkrfVc6xPm8x6TDrevmgEDiY0sfEfUpwtnCQwfA2ho97FGmfDNC71zAI6VnSPOeMOOhUM48IhEfbkgN0Ecu0Kr6WRZHyIFsPVDOzTRuMRp8/UCgYBZsj6oi2iPhU4IS6dtKLta3ywqjjpIrrSwNNKjke404NDW59SBmUz5YX9sIUBFn3FjzX0Mdm7/UbAk+l78DXxXM1Rj7l5FKJKiIQYSCCFS0/JYBalDBykXtbhrRt+niE9KAX+6xxrsJdPmtKaGYbb94/3J49ASAtj4UE6NEzAjMQKBgF5Hgyunwuw9o86zHKTkPVElvMt8TQ8y8Oh77f5xjLvpGpWuNqQvOqXOzAQZ5XWEmsRsV26IEvmNmHzDnUQJ0iqE12jnlORVixi/KCWEE+a98VLR4SMgUFeo/d+XlcLrdNYgRgPQf12TM7MqmkoEtYucHCojZRkTaknT+VcbS1oxAoGAfioX9k0FIOZeQ3S93RfQKpByL+d3/IHdbCK3A/K2cpx12x3LtZu8tBQrWahQ3cKD/lY3zxkPG9umGDll3693lBabWaKYbU+Cp4hNJjijP0XcVEkUlvm1gf9d8EBtFnouT8K9UX4ziR7JALaZVPpRM1RmocymxO1GNBHVM2q+EMs=",
        //异步通知地址
        'notify_url' => "https://api2.juanpao.com/pay/alipay/notify_url",
        //同步跳转
        'return_url' => "https://web2.juanpao.com/adminMerchant/aliReturn.html",
        //编码格式
        'charset' => "UTF-8",
        //签名方式
        'sign_type' => "RSA2",
        //支付宝网关
        'gatewayUrl' => "https://openapi.alipay.com/gateway.do",
        //支付宝公钥,查看地址：https://openhome.alipay.com/platform/keyManage.htm 对应APPID下的支付宝公钥。
        'alipay_public_key' => "MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAnnTYStqQMf7xqU3UtLyIzf3Ug2h6O9oovvXSROWO1/11Chb/P6iYQR79LFLtpK5MLJcgrpKhlIeI/4Q3alYggaGJU5SlVeNwO7kEOZTjEGoJX8YeP1qThlFKXtcW8kXuGkiBlk63zr2eGnE6SiSgsAlyc9dLfnxDo78cAqsXMWv7F2YRHMNvOpdmoaBR5d0EXv+qs6PxhdmWmUeSqu6DfI2Nb/xFAgr0zORe6VLOTp8n9vbV3DIAwAhyrwpbm7U4YvJMWOIksY+mePyN50FW+WjfME69AsmJjQEwvid5hv3BAadD2XQP7SJrK5ZwDtqhLA5eXhPnncng7LONAxQYGQIDAQAB",
    ],
];
