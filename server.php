<?php
class SocketChat
{
    private $timeout = 60;  //超时时间
    private $handShake = False; //默认未牵手
    private $master = 1;  //主进程
    private $port = 2000;  //监听端口
    private static $connectPool = [];  //连接池
    private static $accountPool=[]; //账号池
    private static $maxConnectNum = 1024; //最大连接数
    private static $chatUser = [];  //参与聊天的用户
    private static $isTellOnlineSate=false;

    public function __construct( $port = 0 )
    {
        !empty( $port ) && $this->port = $port;
        $this->startServer();
    }

    //开始服务器
    public function startServer()
    {
        $this->master = socket_create_listen( $this->port );
        $this->master = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        socket_set_option($this->master, SOL_SOCKET, SO_REUSEADDR, 1);
        socket_bind($this->master, '127.0.0.1', $this->port);
        socket_listen($this->master, 20);
        $this->runLog($this->port);
        if( !$this->master ) throw new \ErrorException("listen {$this->port} fail !");

        $this->runLog("Server Started : ".date('Y-m-d H:i:s'));
        $this->runLog("Listening on   : 127.0.0.1 port " . $this->port);
        $this->runLog("Master socket  : " . $this->master . " \n");

        self::$connectPool[] = $this->master;

        while( true ){
            $readFds = self::$connectPool;
            //阻塞接收客户端链接
            @socket_select( $readFds, $writeFds, $e = null, $this->timeout );

            foreach( $readFds as $socket ){
                //当前链接 是主进程
                if( $this->master == $socket ){

                    $client = socket_accept( $this->master );  //接收新的链接
                    $this->handShake = False;

                    if ($client < 0){
                        $this->log('clinet connect false!');
                        continue;
                    } else{
                        //超过最大连接数
                        if( count( self::$connectPool ) > self::$maxConnectNum )
                            continue;

                        //加入连接池
                        $this->connect( $client );
                    }

                }else{
                    //不是主进程,开始接收数据
                    $bytes = @socket_recv($socket, $buffer, 2048, 0);
                    //未读取到数据
                    $this->runLog("bytes数据长度：".$bytes);
                    if( $bytes == 0||$bytes==8 ){
                        $this->deleteAccountPool($socket);
                        $this->disConnect( $socket );
                    }else{
                        //未握手 先握手
                        if( !$this->handShake ){

                            $this->doHandShake( $socket, $buffer );
                        }else{

                            //如果是已经握完手的数据，广播其发送的消息
                            $buffer = $this->decode( $buffer ); 
                            $this->runLog($socket.'发来的消息：'.$buffer);
                            $len=strripos($buffer,'*');
                            $buffer=substr($buffer,0,$len);
                            $this->runLog($socket.'发来的消息：'.$buffer);
                            $this->dealMessage($socket,$buffer);
                            // $this->runLog("解码的信息：".$buffer);
                            //  foreach( self::$connectPool as $socket ){
                            //     if( $socket != $this->master ){
                            //     $this->send($socket,$buffer);
                            //     }
                            // }//给所有人发送信息。
                        }
                    }

                }
            }

        }
    }

    //处理来自客户端的消息
    function dealMessage($socket,$message)
    {
        if($message!=null)
        {
            $message=json_decode($message,true);
            if($message==null)
            {
                $this->runLog("isnull");
                $this->tellOnlineState($socket,null,2);
                $this->deleteAccountPool($socket);
                $this->disConnect($socket);
            }
            else{
                //Type:1.上线，2.下线，3.发送好友信息，4.发送群消息。
                switch($message['type'])
                {
                    case 1:
                        $this->runLog($socket.'|)(|'.$message['source_account']."上线");
                        if($this->addAccountPool($socket,$message['source_account']))
                            $this->tellOnlineState($socket,$message['source_account'],1);
                        break;
                    case 2:
                        $this->runLog($socket.'|)(|'.$message['source_account']."下线");
                        $this->tellOnlineState($socket,$message['source_account'],2);
                        $this->deleteAccountPool($socket);
                        $this->disConnect($socket);
                        break;
                    case 3:
                        $this->runLog($socket.'|)(|'.$message['source_account']."发来的好友消息");
                        $this->transFriendMessage($message);
                        break;
                    case 4:
                        $this->runLog($socket.'|)(|'.$message['source_account']."发来了群消息->".$message['target_account']);
                        $this->transGroupMessage($message);
                        break;
                    case 5:
                        $this->runLog($socket.'|)(|'.$message['source_account']."添加好友请求->".$message['target_account']);
                        $this->transFriendMessage($message);
                        break;
                    case 6:
                        $this->runLog($socket.'|)(|'.$message['source_account']."同意好友请求->".$message['target_account']);
                        $this->transFriendMessage($message);
                        break;
                    case 7:
                        $this->runLog($socket.'|)(|'.$message['source_account']."拒绝好友请求->".$message['target_account']);
                        $this->transFriendMessage($message);
                        break;
                    case 8:
                        $this->runLog($socket.'|)(|'.$message['source_account']."删除了好友->".$message['target_account']);
                        $this->transFriendMessage($message);
                        break;
                    case 9:
                        $this->runLog($socket.'|)(|'.$message['source_account']."请求加群->".$message['group_account']);
                        $this->transFriendMessage($message);
                        break;
                    case 10:
                        $this->runLog($socket.'|)(|'.$message['source_account']."同意加群请求->".$message['target_account']);
                        $this->transFriendMessage($message);
                        break;
                    case 11:
                        $this->runLog($socket.'|)(|'.$message['source_account']."拒绝加群请求->".$message['target_account']);
                        $this->transFriendMessage($message);
                        break;
                    case 12:
                        $this->runLog($socket.'|)(|'.$message['source_account']."解散了群->".$message['target_account']);
                        $this->transGroupMessage($message);
                        break;
                    case 13:
                        $this->runLog($socket.'|)(|'.$message['source_account']."退出了群->".$message['target_account']);
                        $this->transGroupMessage($message);
                        break; 
                    case 14:
                        $this->runLog($socket.'|)(|'.$message['source_account']."剔群->".$message['target_account']);
                        $this->transFriendMessage($message);
                    default:
                        $this->runLog("type is undfine");
                        break;
                }
            }
        }
        else
            $this->runLog("null");
    }
    //添加到用户池
    //socket为socket资源号 source为用户账号
    function addAccountPool($socket,$source)
    {
        $pools=count(self::$accountPool);
        $type=1;
      if($pools>0)
      {  
        foreach( self::$accountPool as $accountBind ){
            if($accountBind['source_account']==$source)
            {
                $msg=[
                    "type"=>"20"
                ];
                $this->runLog("账号：".$source."相等||".$accountBind['socket_account']."<->".$socket);
                $this->send($socket,$msg);
                $this->disConnect($socket);
                $type=0;
                return false;
            }

        }
      } 
     if($type==1){
            $accountBind=[
                'socket_account'=>$socket,
                'source_account'=>$source
            ];
        array_push(self::$accountPool,$accountBind);
        $this->runLog($accountBind['socket_account']."（socket资源号）和 ".$accountBind['source_account']."（用户账号）绑定");
        return true;
      }
    }
    //客户端链接处理函数
    function connect( $socket )
    {
        array_push( self::$connectPool, $socket );
        $this->runLog("\n" . $socket . " CONNECTED!");
        $this->runLog(date("Y-n-d H:i:s"));
    }

    //从用户池删除用户
    function deleteAccountPool($sokect)
    {
        foreach( self::$accountPool as $accountBind ){
            if($accountBind['socket_account']==$sokect)
            {
                $index = array_search( $accountBind, self::$accountPool );
                $this->runLog($accountBind['socket_account']."（socket资源号）和 ".$accountBind['source_account']."（用户账号）解除了绑定");
                if($index>=0)
                {
                    array_splice( self::$accountPool, $index, 1 );
                }
            }
        }
    }
    //客户端断开链接函数
    function disConnect( $socket )
    {
        $index = array_search( $socket, self::$connectPool );
        socket_close( $socket );
        $this->runLog( $socket . " DISCONNECTED!" );
        if ($index >= 0){
            array_splice( self::$connectPool, $index, 1 );
        }
    }  
    
    //用户上/下线通知  
    function tellOnlineState($socket,$source,$type)
    {
        if($source==null)
        {
            foreach( self::$accountPool as $accountBind )
            {
                if($accountBind['socket_account']==$socket)
                {
                    $source=$accountBind['source_account'];
                    break;
                }
            }
        }
        $connection=$this->connectDatabase();
        $sql="select friend_account from user_friends where user_account='{$source}'";
        $result=mysqli_query($connection,$sql);
        if(!$result)//查询检测
            die("query failed: ".mysqli_error($connection));
        //$row=mysqli_fetch_assoc($online_result);
        $row=mysqli_fetch_all($result,MYSQLI_ASSOC);
        $i=1;
        for($i=0;$i<count($row);$i++)
        {   
            foreach( self::$accountPool as $accountBind ){
                if($accountBind['source_account']!=$source)
                {
                    if($row[$i]['friend_account']==$accountBind['source_account'])
                    {
                         $this->runLog($accountBind['source_account']."次数：".$i);
                         $i++;
                        $msg=[
                            "type"=>$type,
                            "source_account"=>$source,
                            "target_account"=>$accountBind['source_account']
                            ];
                        $this->send($accountBind['socket_account'],$msg);
                        $msg=[
                            "type"=>'15',
                            "source_account"=>$accountBind['source_account'],
                            "target_account"=>$source
                        ];
                        $this->runLog($socket."发出>>type:".$type);
                        $this->send($socket,$msg);
                        break;
                    }
                }
            }       
        }
                
    }

    //好友消息传递
    function transFriendMessage($msg)
    {
        $findOnlineFriend=0;
        foreach( self::$accountPool as $accountBind )
        {
            if($accountBind['source_account']==$msg['target_account'])
            {
                $findOnlineFriend=1;
                date_default_timezone_set('PRC');
                $msg['time']=date('m/d G:i');
                $this->send($accountBind['socket_account'],$msg);
                break;
            }
        }
        if($findOnlineFriend==0)
        {
            //处理离线消息
        }
    }

    function transGroupMessage($groupMsg)
    {
        $connection=$this->connectDatabase();
        $sql="select member_account from group_members where group_account='{$groupMsg['target_account']}'";
        $result=mysqli_query($connection,$sql);
        if(!$result)//查询检测
            die("query failed: ".mysqli_error($connection));
        //$row=mysqli_fetch_assoc($online_result);
        $row=mysqli_fetch_all($result,MYSQLI_ASSOC);
        for($i=0;$i<count($row);$i++)
        {
            if($row[$i]['member_account']!=$groupMsg['source_account'])
            {
                $msg=$groupMsg;
                $msg['target_account']=$row[$i]['member_account'];
                $msg['group_account']=$groupMsg['target_account'];
                // $msg=[
                //     "type"=>$groupMsg['type'],
                //     "source_account"=>$groupMsg['source_account'],
                //     "source_name"=>$groupMsg['source_name'],
                //     "target_account"=>$row[$i]['member_account'],
                //     "group_account"=>$groupMsg['target_account']
                // ];
                $this->runLog("群成员：".$msg['target_account']);
                $this->transFriendMessage($msg);
            }
        }
        if($groupMsg['type']==12)
        {
            $group_account=$groupMsg['target_account'];
            $sql="delete from group_info where group_account='{$group_account}'";
            $add_result=mysqli_query($connection,$sql);
            $sql="delete from group_members where group_account='{$group_account}'";
            $add_result=mysqli_query($connection,$sql);
        }
        mysqli_close($connection);
    }

    //根据type来发送不同的数据
    function sendMessageTpye($type,$source,$accountBind,$socket)
    {
        $msg=[
                "type"=>$type,
                "source_account"=>$source,
                "target_account"=>$accountBind['source_account']
                ];
        $this->send($accountBind['socket_account'],$msg);
        $msg=[
                "type"=>$type,
                "source_account"=>$accountBind['source_account'],
                "target_account"=>$source
                ];
        $this->send($socket,$msg);
    }
    //处理发送信息
   public function send( $client, $msg )
    {
        $msg = $this->frame( json_encode( $msg ) );
        socket_write( $client, $msg, strlen($msg) );
    }

    //连接数据库
    function connectDatabase()
    {
        $localhost="localhost:3306";
        $user="root";
        $password="afj1043146498";
        $database="chatan";
        $connection=mysqli_connect($localhost,$user,$password,$database);
        return $connection;
    }
    function addOnlineUser($user)
    {
        $connection=$this->connectDatabase();

    }
    //握手协议
    function doHandShake($socket, $buffer)
    {
        list($resource, $host, $origin, $key) = $this->getHeaders($buffer);
        $upgrade  = "HTTP/1.1 101 Switching Protocol\r\n" .
            "Upgrade: websocket\r\n" .
            "Connection: Upgrade\r\n" .
            "Sec-WebSocket-Accept: " . $this->calcKey($key) . "\r\n\r\n";  //必须以两个回车结尾

        socket_write($socket, $upgrade, strlen($upgrade));
        $this->handShake = true;
        return true;
    }

    //获取请求头
    function getHeaders( $req )
    {
        $r = $h = $o = $key = null;
        if (preg_match("/GET (.*) HTTP/"              , $req, $match)) { $r = $match[1]; }
        if (preg_match("/Host: (.*)\r\n/"             , $req, $match)) { $h = $match[1]; }
        if (preg_match("/Origin: (.*)\r\n/"           , $req, $match)) { $o = $match[1]; }
        if (preg_match("/Sec-WebSocket-Key: (.*)\r\n/", $req, $match)) { $key = $match[1]; }
        return [$r, $h, $o, $key];
    }

    //验证socket
    function calcKey( $key )
    {
        //基于websocket version 13
        $accept = base64_encode(sha1($key . '258EAFA5-E914-47DA-95CA-C5AB0DC85B11', true));
        return $accept;
    }


    //打包函数 返回帧处理
    public function frame( $buffer )
    {
        $len = strlen($buffer);
        if ($len <= 125) {

            return "\x81" . chr($len) . $buffer;
        } else if ($len <= 65535) {

            return "\x81" . chr(126) . pack("n", $len) . $buffer;
        } else {

            return "\x81" . char(127) . pack("xxxxN", $len) . $buffer;
        }
    }

    //解码 解析数据帧
    function decode( $buffer )
    {
        $len = $masks = $data = $decoded = null;
        $len = ord($buffer[1]) & 127;

        if ($len === 126) {
            $masks = substr($buffer, 4, 4);
            $data = substr($buffer, 8);
        }
        else if ($len === 127) {
            $masks = substr($buffer, 10, 4);
            $data = substr($buffer, 14);
        }
        else {
            $masks = substr($buffer, 2, 4);
            $data = substr($buffer, 6);
        }
        for ($index = 0; $index < strlen($data); $index++) {
            $decoded .= $data[$index] ^ $masks[$index % 4];
        }
        return $decoded;
    }
    //打印运行信息
    public function runLog( $mess = '' )
    {
        echo $mess . PHP_EOL;
    }

    //系统日志
    public function log( $mess = '' )
    {
        @file_put_contents( './' . date("Y-m-d") . ".log", date('Y-m-d H:i:s') . "  " . $mess . PHP_EOL, FILE_APPEND );
    }
}
// $port=1238;
$port=18669;
new SocketChat($port);
