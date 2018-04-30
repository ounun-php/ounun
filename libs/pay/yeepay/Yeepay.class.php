<?php
/** 命名空间 */
namespace pay\yeepay;
/**
 * 易宝支付
 * @author andsky
 *
 */
class Yeepay
{
    /**
     * 日志记录文件
     * @var string
     */
    private $_log_name      = 'YeePay_HTML.log';
    private $_debug         = false;

    #	商户编号p1_MerId,以及密钥merchantKey 需要从易宝支付平台获得
    private $p1_MerId       = "10001126856";																										#测试使用
    private $merchantKey	= "69cl522AV6q613Ii4W6u8K6XuW8vM1N6bFgyv769220IuYe9u37N4y7rI4Pl";		#测试使用

    # 产品通用接口测试请求地址
    private $reqURL_onLine;
    # 退款接口
    private $reqURL_RefOrd;
    # 订单查询
    private $QueryOrdURL_onLine;


    public function __construct($id,$key,$debug=false)
    {
        $this->p1_MerId     = $id;
        $this->merchantKey  = $key;
        $this->_debug       = $debug;
        if($this->_debug)
        {
            #	产品通用接口测试请求地址
            $this->reqURL_onLine        = "http://tech.yeepay.com:8080/robot/debug.action";
            #测试请求地址
            $this->QueryOrdURL_onLine	= "http://tech.yeepay.com:8080/robot/debug.action";
            # 退款接口测试请求地址
            $this->reqURL_RefOrd	    = "http://tech.yeepay.com:8080/robot/debug.action";

        }else
        {
            # 产品通用接口测试请求地址
            $this->reqURL_onLine        = "https://www.yeepay.com/app-merchant-proxy/node";
            # 订单查询
            $this->QueryOrdURL_onLine	= "https://www.yeepay.com/app-merchant-proxy/command";
            # 退款接口测试请求地址
            $this->reqURL_RefOrd	    = "https://www.yeepay.com/app-merchant-proxy/command";
        }
    }

    /**
     * 商家设置用户购买商品的支付信息
     */
    public function pay($Amt,$CallBackUrl,$Pid,$Pcat,$MP,$Pdesc,$Order="",$FrpId="")
    {
        ##易宝支付平台统一使用GBK/GB2312编码方式,参数如用到中文，请注意转码
        # 支付请求，固定值"Buy" .
        $p0_Cmd             = "Buy";

        #	商户订单号,选填.
        ##若不为""，提交的订单号必须在自身账户交易中唯一;为""时，易宝支付会自动生成随机的商户订单号.
        $p2_Order			= $Order;

        #	支付金额,必填.
        ##单位:元，精确到分.
        $p3_Amt				= $Amt;

        #	交易币种,固定值"CNY".
        $p4_Cur				= "CNY";

        #	商品名称
        ##用于支付时显示在易宝支付网关左侧的订单产品信息.
        $p5_Pid				= $Pid;

        #	商品种类
        $p6_Pcat			= $Pcat;

        #	商品描述
        $p7_Pdesc			= $Pdesc;

        #	商户接收支付成功数据的地址,支付成功后易宝支付会向该地址发送两次成功通知.
        $p8_Url				= $CallBackUrl;

        #	送货地址
        # 为"1": 需要用户将送货地址留在易宝支付系统;为"0": 不需要，默认为 "0".
        $p9_SAF             = "0";

        #	商户扩展信息
        ##商户可以任意填写1K 的字符串,支付成功时将原样返回.
        $pa_MP				= $MP;

        #	支付通道编码
        ##默认为""，到易宝支付网关.若不需显示易宝支付的页面，直接跳转到各银行、神州行支付、骏网一卡通等支付页面，该字段可依照附录:银行列表设置参数值.
        $pd_FrpId			= $FrpId;

        # 应答机制
        # 默认为"1": 需要应答机制;
        $pr_NeedResponse	= "1";

        # 调用签名函数生成签名串
        $hmac = $this->pay_hmac_string($p0_Cmd,$p2_Order,$p3_Amt,$p4_Cur,$p5_Pid,$p6_Pcat,$p7_Pdesc,$p8_Url,$p9_SAF,$pa_MP,$pd_FrpId,$pr_NeedResponse);
        exit("<html>
                <head>
                    <title>To YeePay Page</title>
                </head>
                <body onload='document.yeepay.submit();'>
                <form name='yeepay' action='{$this->reqURL_onLine}' method='post'>
                    <input type='hidden' name='p0_Cmd'			value='{$p0_Cmd}'>
                    <input type='hidden' name='p1_MerId'		value='{$this->p1_MerId}'>
                    <input type='hidden' name='p2_Order'		value='{$p2_Order}'>
                    <input type='hidden' name='p3_Amt'			value='{$p3_Amt}'>
                    <input type='hidden' name='p4_Cur'			value='{$p4_Cur}'>
                    <input type='hidden' name='p5_Pid'			value='{$p5_Pid}'>
                    <input type='hidden' name='p6_Pcat'			value='{$p6_Pcat}'>
                    <input type='hidden' name='p7_Pdesc'		value='{$p7_Pdesc}'>
                    <input type='hidden' name='p8_Url'			value='{$p8_Url}'>
                    <input type='hidden' name='p9_SAF'			value='{$p9_SAF}'>
                    <input type='hidden' name='pa_MP'			value='{$pa_MP}'>
                    <input type='hidden' name='pd_FrpId'		value='{$pd_FrpId}'>
                    <input type='hidden' name='pr_NeedResponse'	value='{$pr_NeedResponse}'>
                    <input type='hidden' name='hmac'			value='{$hmac}'>
                </form>
                </body>
             </html>");

    }


    # 签名函数生成签名串
    public function pay_hmac_string($p0_Cmd,$p2_Order,$p3_Amt,$p4_Cur,$p5_Pid,$p6_Pcat,$p7_Pdesc,$p8_Url,$p9_SAF,$pa_MP,$pd_FrpId,$pr_NeedResponse)
    {
        #进行签名处理，一定按照文档中标明的签名顺序进行
        $sbOld = "";
        #加入业务类型
        $sbOld = $sbOld.$p0_Cmd;
        #加入商户编号
        $sbOld = $sbOld.$this->p1_MerId;
        #加入商户订单号
        $sbOld = $sbOld.$p2_Order;
        #加入支付金额
        $sbOld = $sbOld.$p3_Amt;
        #加入交易币种
        $sbOld = $sbOld.$p4_Cur;
        #加入商品名称
        $sbOld = $sbOld.$p5_Pid;
        #加入商品分类
        $sbOld = $sbOld.$p6_Pcat;
        #加入商品描述
        $sbOld = $sbOld.$p7_Pdesc;
        #加入商户接收支付成功数据的地址
        $sbOld = $sbOld.$p8_Url;
        #加入送货地址标识
        $sbOld = $sbOld.$p9_SAF;
        #加入商户扩展信息
        $sbOld = $sbOld.$pa_MP;
        #加入支付通道编码
        $sbOld = $sbOld.$pd_FrpId;
        #加入是否需要应答机制
        $sbOld = $sbOld.$pr_NeedResponse;
        $this->_logstr($p2_Order,$sbOld,$this->_hmac_md5($sbOld,$this->merchantKey));
        return $this->_hmac_md5($sbOld,$this->merchantKey);
    }

    #   只有支付成功时易宝支付才会通知商户.
    #  支付成功回调有两次，都会通知到在线支付请求参数中的p8_Url上：浏览器重定向;服务器点对点通讯.
    public function callback($args)
    {
        #	解析返回参数.
        $r0_Cmd		= $args['r0_Cmd'];
        $r1_Code	= $args['r1_Code'];
        $r2_TrxId	= $args['r2_TrxId'];
        $r3_Amt		= $args['r3_Amt'];
        $r4_Cur		= $args['r4_Cur'];
        $r5_Pid		= $args['r5_Pid'];
        $r6_Order	= $args['r6_Order'];
        $r7_Uid		= $args['r7_Uid'];
        $r8_MP		= $args['r8_MP'];
        $r9_BType	= $args['r9_BType'];
        $hmac		= $args['hmac'];

        #	判断返回签名是否正确（True/False）
        $hmac_self  = $this->callback_hmac_string($r0_Cmd,$r1_Code,$r2_TrxId,$r3_Amt,$r4_Cur,$r5_Pid,$r6_Order,$r7_Uid,$r8_MP,$r9_BType);

        #	校验码正确.
        if($hmac== $hmac_self)
        {
            if($r1_Code=="1")
            {

                #	需要比较返回的金额与商家数据库中订单的金额是否相等，只有相等的情况下才认为是交易成功.
                #	并且需要对返回的处理进行事务控制，进行记录的排它性处理，在接收到支付结果通知后，判断是否进行过业务逻辑处理，不要重复进行业务逻辑处理，防止对同一条交易重复发货的情况发生.

                if($r9_BType=="1")
                {
                    echo "交易成功";
                    echo  "<br />在线支付页面返回";
                }elseif($r9_BType=="2")
                {
                    #如果需要应答机制则必须回写流,以success开头,大小写不敏感.
                    echo "success";
                    echo "<br />交易成功";
                    echo  "<br />在线支付服务器返回";
                }
            }

        }else
        {
            echo "交易信息被篡改";
        }

    }

    public function callback_hmac_string($r0_Cmd,$r1_Code,$r2_TrxId,$r3_Amt,$r4_Cur,$r5_Pid,$r6_Order,$r7_Uid,$r8_MP,$r9_BType)
    {

        #取得加密前的字符串
        $sbOld = "";
        #加入商家ID
        $sbOld = $sbOld.$this->p1_MerId;
        #加入消息类型
        $sbOld = $sbOld.$r0_Cmd;
        #加入业务返回码
        $sbOld = $sbOld.$r1_Code;
        #加入交易ID
        $sbOld = $sbOld.$r2_TrxId;
        #加入交易金额
        $sbOld = $sbOld.$r3_Amt;
        #加入货币单位
        $sbOld = $sbOld.$r4_Cur;
        #加入产品Id
        $sbOld = $sbOld.$r5_Pid;
        #加入订单ID
        $sbOld = $sbOld.$r6_Order;
        #加入用户ID
        $sbOld = $sbOld.$r7_Uid;
        #加入商家扩展信息
        $sbOld = $sbOld.$r8_MP;
        #加入交易结果返回类型
        $sbOld = $sbOld.$r9_BType;

        $this->_logstr($r6_Order,$sbOld,$this->_hmac_md5($sbOld,$this->merchantKey));
        return $this->_hmac_md5($sbOld,$this->merchantKey);

    }


    public function query($Order)
    {
        $p0_Cmd   = "QueryOrdDetail";	        #接口类型
        $p2_Order = $Order;						#商户订单号

        #	进行签名处理，一定按照文档中标明的签名顺序进行
        $sbOld = "";
        #	加入订单查询请求，固定值"QueryOrdDetail"
        $sbOld = $sbOld.$p0_Cmd;
        #	加入商户编号
        $sbOld = $sbOld.$this->p1_MerId;
        #	加入商户订单号
        $sbOld = $sbOld.$p2_Order;

        $hmac  = $this->_hmac_md5($sbOld,$this->merchantKey);

        $this->_logstr($p2_Order,$sbOld,$this->_hmac_md5($sbOld,$this->merchantKey));

        #	进行签名处理，一定按照文档中标明的签名顺序进行
        #	加入订单查询请求，固定值"QueryOrdDetail"
        $params = array
        (
            'p0_Cmd'    => $p0_Cmd,
            #	加入商户编号
            'p1_MerId'	=> $this->p1_MerId,
            #	加入商户订单号
            'p2_Order'	=> $p2_Order,
            #	加入校验码
            'hmac' 		=> $hmac
        );


        $pageContents = \plugins\yeepay\HttpClient::quickPost($this->QueryOrdURL_onLine, $params);

        $result = explode("\n",$pageContents);

        ## 声明查询结果
        $r0_Cmd					= "";	  #	取得业务类型
        $r1_Code				= "";     #	查询结果状态码
        $r2_TrxId				= "";	  #	易宝支付交易流水号
        $r3_Amt					= "";	  #	支付金额
        $r4_Cur					= "";	  #	交易币种
        $r5_Pid					= "";	  #	商品名称
        $r6_Order				= "";	  #	商户订单号
        $r8_MP					= "";	  #	商户扩展信息
        $rb_PayStatus		    = "";	  #	支付状态
        $rc_RefundCount	        = "";	  #	已退款次数
        $rd_RefundAmt		    = "";	  #	已退款金额
        $hmac					= "";     #	查询返回数据的签名串

        for($index=0;$index<count($result);$index++)
        {//数组循环
            $result[$index] = trim($result[$index]);
            if (strlen($result[$index]) == 0)
            {
                continue;
            }
            $aryReturn = explode("=",$result[$index]);
            $sKey = $aryReturn[0];
            $sValue = $aryReturn[1];
            if($sKey=="r0_Cmd")
            {
                #业务类型
                $r0_Cmd = $sValue;
            }elseif($sKey=="r1_Code")
            {
                #查询结果状态码
                $r1_Code = $sValue;
            }elseif($sKey == "r2_TrxId")
            {
                #易宝支付交易流水号
                $r2_TrxId = $sValue;
            }elseif($sKey == "r3_Amt")
            {
                #支付金额
                $r3_Amt = $sValue;
            }elseif($sKey == "r4_Cur")
            {
                #交易币种
                $r4_Cur = $sValue;
            }elseif($sKey == "r5_Pid")
            {
                #商品名称
                $r5_Pid = $sValue;
            }elseif($sKey == "r6_Order")
            {
                #商户订单号
                $r6_Order = $sValue;
            }elseif($sKey == "r8_MP")
            {
                #商户扩展信息
                $r8_MP = $sValue;
            }elseif($sKey == "rb_PayStatus")
            {
                #支付状态
                $rb_PayStatus = $sValue;
            }elseif($sKey == "rc_RefundCount")
            {
                #已退款次数
                $rc_RefundCount = $sValue;
            }elseif($sKey == "rd_RefundAmt")
            {
                #已退款金额
                $rd_RefundAmt = $sValue;
            }elseif($sKey == "hmac")
            {
                #取得校验码
                $hmac = $sValue;
            }else
            {
                echo $result[$index];
                return;
            }
        }


        #进行校验码检查 取得加密前的字符串
        $sbOld="";
        #加入业务类型
        $sbOld = $sbOld.$r0_Cmd;
        #加入查询操作是否成功
        $sbOld = $sbOld.$r1_Code;
        #加入易宝支付交易流水号
        $sbOld = $sbOld.$r2_TrxId;
        #加入支付金额
        $sbOld = $sbOld.$r3_Amt;
        #加入交易币种
        $sbOld = $sbOld.$r4_Cur;
        #加入商品名称
        $sbOld = $sbOld.$r5_Pid;
        #加入商户订单号
        $sbOld = $sbOld.$r6_Order;
        #加入商户扩展信息
        $sbOld = $sbOld.$r8_MP;
        #加入支付状态
        $sbOld = $sbOld.$rb_PayStatus;
        #加入已退款次数
        $sbOld = $sbOld.$rc_RefundCount;
        #加入已退款金额
        $sbOld = $sbOld.$rd_RefundAmt;

        echo "[".$sbOld."]";

        $sNewString = $this->_hmac_md5($sbOld,$this->merchantKey);

        $this->_logstr($r6_Order,$sbOld,$this->_hmac_md5($sbOld,$this->merchantKey));
        //校验码正确
        if($sNewString==$hmac)
        {
            if($r1_Code=="1")
            {
                echo "<br>查询成功!";
                echo "<br>订单号:".$r6_Order;
                echo "<br>易宝支付交易流水号:".$r2_TrxId;
                echo "<br>商品名称:".$r5_Pid;
                echo "<br>支付金额:".$r3_Amt;
                echo "<br>商户扩展信息:".$r8_MP;
                echo "<br>订单状态:".$rb_PayStatus;
                echo "<br>已退款次数:".$rc_RefundCount;
                echo "<br>已退款金额:".$rd_RefundAmt;
            } else if($r1_Code=="50")
            {
                echo "<br>该订单不存在";
                exit;
            } else
            {
                echo "<br>查询失败";
                exit;
            }
        } else
        {
            echo "<br>localhost:".$sNewString;
            echo "<br>YeePay:".$hmac;
            echo "<br>交易信息被篡改";
            exit;
        }
    }

    public function refund($TrxId,$Amt,$Desc)
    {
        $p0_Cmd 	= "RefundOrd";	    #接口类型
        $pb_TrxId   = $TrxId;			#易宝支付交易流水号
        $p3_Amt		= $Amt;				#退款金额
        $p4_Cur		= "CNY";			#交易币种,固定值"CNY".
        $p5_Desc    = $Desc;			#详细描述退款原因的信息.

        #	进行签名处理，一定按照文档中标明的签名顺序进行
        $sbOld ="";
        #	加入订单查询请求，固定值"QueryOrdDetail"
        $sbOld = $sbOld.$p0_Cmd;
        #	加入商户编号
        $sbOld = $sbOld.$this->p1_MerId;
        #	加入易宝支付交易流水号
        $sbOld = $sbOld.$pb_TrxId;
        #	加入退款金额
        $sbOld = $sbOld.$p3_Amt;
        #	加入交易币种
        $sbOld = $sbOld.$p4_Cur;
        #	加入退款说明
        $sbOld = $sbOld.$p5_Desc;

        $hmac	 = HmacMd5($sbOld,$this->merchantKey);

        $this->_logstr($pb_TrxId,$sbOld,HmacMd5($sbOld,$this->merchantKey));

        #	进行签名处理，一定按照文档中标明的签名顺序进行
        #	加入订单查询请求，固定值"QueryOrdDetail"
        $params = array
        (
            'p0_Cmd'    => $p0_Cmd,
             #	加入商户编号
            'p1_MerId'	=>  $this->p1_MerId,
             #	加入易宝支付交易流水号
            'pb_TrxId'	=>  $pb_TrxId,
             #	加入易宝支付交易流水号
            'p3_Amt'	=>  $p3_Amt,
             #	加入易宝支付交易流水号
            'p4_Cur'	=>  $p4_Cur,
             #	加入易宝支付交易流水号
            'p5_Desc'	=>  $p5_Desc,
             #	加入校验码
            'hmac' 		=>  $hmac
        );

        $pageContents = \plugins\yeepay\HttpClient::quickPost($this->reqURL_RefOrd, $params);
        $result       = explode("\n",$pageContents);

        ## 声明查询结果
        $r0_Cmd					= "";	  #	业务类型
        $r1_Code				= "";     #	退款申请结果
        $r2_TrxId				= "";	  #	易宝支付交易流水号
        $r3_Amt					= "";	  #	退款金额
        $r4_Cur					= "";	  #	交易币种
        $hmac					= "";     #	签名数据
        #echo "result.count:".count($result);
        for($index = 0;$index < count($result);$index++)
        {
            //数组循环
            $result[$index] = trim($result[$index]);
            if (strlen($result[$index]) == 0)
            {
                continue;
            }
            $aryReturn = explode("=",$result[$index]);
            $sKey = $aryReturn[0];
            $sValue = $aryReturn[1];
            if($sKey=="r0_Cmd")
            {
                #业务类型
                $r0_Cmd = $sValue;
            }elseif($sKey=="r1_Code")
            {
                #退款申请结果
                $r1_Code = $sValue;
            }elseif($sKey == "r2_TrxId")
            {
                #易宝支付交易流水号
                $r2_TrxId = $sValue;
            }elseif($sKey == "r3_Amt")
            {
                #退款金额
                $r3_Amt = $sValue;
            }elseif($sKey == "r4_Cur")
            {
                #交易币种
                $r4_Cur = $sValue;
            }elseif($sKey == "hmac")
            {
                #取得签名数据
                $hmac = $sValue;
            }else
            {
                echo $result[$index];
                return;
            }
        }


        #进行校验码检查 取得加密前的字符串
        $sbOld="";
        #加入业务类型
        $sbOld = $sbOld.$r0_Cmd;
        #加入退款申请是否成功
        $sbOld = $sbOld.$r1_Code;
        #加入易宝支付交易流水号
        $sbOld = $sbOld.$r2_TrxId;
        #加入退款金额
        $sbOld = $sbOld.$r3_Amt;
        #加入交易币种
        $sbOld = $sbOld.$r4_Cur;

        $sNewString = $this->_hmac_md5($sbOld,$this->merchantKey);

        $this->logstr($r2_TrxId,$sbOld,$this->_hmac_md5($sbOld,$this->merchantKey));
        //校验码正确
        if($sNewString==$hmac)
        {
            if($r1_Code=="1")
            {
                echo "<br>订单退款请求成功!";
                echo "<br>易宝支付交易流水号:".$r2_TrxId;
                echo "<br>退款金额:".$r3_Amt;
            } else
            {
                echo "<br>订单退款请求失败";
                exit;
            }
        } else
        {
            echo "<br>localhost::".$sNewString;
            echo "<br>YeePay:".$hmac;
            echo "<br>交易签名无效.";
            exit;
        }
    }

    private function _hmac_md5($data,$key)
    {
        // RFC 2104 HMAC implementation for php.
        // Creates an md5 HMAC.
        // Eliminates the need to install mhash to compute a HMAC
        // Hacked by Lance Rushing(NOTE: Hacked means written)

        // 需要配置环境支持iconv，否则中文参数不能正常处理
        $key  = iconv("GB2312","UTF-8",$key);
        $data = iconv("GB2312","UTF-8",$data);

        $b    = 64; // byte length for md5
        if (strlen($key) > $b)
        {
            $key = pack("H*",md5($key));
        }
        $key  = str_pad($key, $b, chr(0x00));
        $ipad = str_pad('', $b, chr(0x36));
        $opad = str_pad('', $b, chr(0x5c));
        $k_ipad = $key ^ $ipad ;
        $k_opad = $key ^ $opad;

        return md5($k_opad . pack("H*",md5($k_ipad . $data)));
    }

    private function _logstr($orderid,$str,$hmac)
    {
        $james=fopen($this->_log_name,"a+");
        fwrite($james,"\r\n".date("Y-m-d H:i:s")."|orderid[{$orderid}]|str[".$str."]|hmac[".$hmac."]");
        fclose($james);
    }
}
