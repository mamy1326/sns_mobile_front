<?php
/* ================================================================================
 * ファイル名   ：AccessLog.php
 * タイトル     ：アクセス情報関連
 * 作成者       ：間宮 直樹
 * 作成日       ：2008/10/21
 * 内容         ：アクセスログを保存・読み込むFunction
 * 更新履歴*******************************************************************
 * 【変更日】  【変更者】      【変更箇所】        【変更理由と変更内容】
 *  2008/10/21  間宮 直樹       全体                新規作成
 * ================================================================================*/

// includeするライブラリ・コンフィグファイルのパスを定義する
define( 'CONFIG_DIR', '../_config/');
define( 'LIB_DIR'   , '../_lib/');

// ライブラリ・コンフィグファイルをinclude
include_once( LIB_DIR    . 'DataConvert.php' );         // データ変換用関数群
include_once( LIB_DIR    . 'WrapDB.php' );              // ＤＢ用関数群
include_once( LIB_DIR    . 'SQLAid.php' );              // ＳＱＬ条件文関数群
include_once( LIB_DIR    . 'UserDB.php' );              // ユーザ情報取得・更新関数群
include_once( CONFIG_DIR . 'db_conf.php' );             // データベース定義ファイル
include_once( CONFIG_DIR . 'manage_conf.php' );         // 管理画面基本情報定義ファイル

class AccessLog
{
    //****************************************************************************
    //*タイトル ：アクセスログ書き込み
    //*内容     ：アクセスログを書き込む
    //****************************************************************************
    function WriteAccessLog( $intLogFlag = LOG_INFO_FLAG )
    {
        // 曜日取得
        $strWeekDay = date( "D" );

        // IPアドレス取得
        $strIpAddress = $_SERVER['REMOTE_ADDR'];

        // リモートホスト取得
        $strRemortHost = @gethostbyaddr( $strIpAddress );

        // リモートホスト分割
        $arrayHosts = explode( '.', $strRemortHost );

        // 配列を逆順に並べ替え(ドメインを編集するため)
        $arrayHosts = array_reverse( $arrayHosts );

        // *************
        // ドメイン取得
        // *************

        // 2nd LD の長さを取得
        $int2ndLdLen = strlen( $arrayHosts[1] );

        // 2nd LD の長さが3以上の時（属性無し）
        if( $int2ndLdLen > 2 )
        {
            $strDomain = $arrayHosts[1] . '.' . $arrayHosts[0];
        }
        // 2nd LD の長さが2以下の時（属性有り）
        else
        {
            $strDomain = $arrayHosts[2] . '.' . $arrayHosts[1] . '.' . $arrayHosts[0];
        }

        // 見たページのURL取得
        $strPageURL = $_SERVER["REQUEST_URI"];

        // ログインID取得
        // （モバイル）
        if( 0 < strlen( $_GET["s_id"] ) || 0 < strlen( $_GET["login_id"] ) )
        {
            if( 0 < strlen( $_GET["login_id"] ) )
            {
                $strLoginID = $_GET["login_id"];
            }
            else
            {
                $arrayUser = UserDB::GetLoginUserOfSession( $_GET["s_id"], $intUserRows );
                $strLoginID = $arrayUser["login_id"];
                unset($arrayUser);
            }
        }
        // （PC）
        else
        {
            $strLoginID = $_COOKIE[ md5( COOKIE_VIX_LOGIN_ID ) ];
        }

        // アクセスログ種別
        // (管理本部通信)
        if( LOG_INFO_FLAG == $intLogFlag )
        {
            // 管理本部通信ID
            $intInfoID      = intval( $_GET["id"] );
            $intAlbumID     = NULL;
            $intThreadID    = NULL;

            $intID          = $intInfoID;

            $strWhere = " AND info_id   =   " . SQLAid::escapeNum( $intInfoID ) ;
        }
        // (フォトアルバム)
        elseif( LOG_ALBUM_FLAG == $intLogFlag )
        {
            // アルバムID
            $intInfoID      = NULL;
            $intAlbumID     = intval( $_GET["album_id"] );
            $intThreadID    = NULL;

            $intID          = $intAlbumID;

            $strWhere = " AND album_id  =   " . SQLAid::escapeNum( $intAlbumID ) ;
        }
        // (スレッド)
        else
        {
            // 管理本部通信ID
            $intInfoID      = NULL;
            $intAlbumID     = NULL;
            $intThreadID    = intval( $_GET["thread_id"] );

            $intID          = $intThreadID;

            $strWhere = " AND thread_id =   " . SQLAid::escapeNum( $intThreadID ) ;
        }

        // *****************************************************
        // アクセスログ存在チェック(ログインIDとINFO_IDで検索)
        // *****************************************************
        $arrayAccessLog = AccessLog::GetLogByID(    $strLoginID ,
                                                    $intLogFlag ,
                                                    $intID      ,
                                                    $intLogRows );

        // 存在すれば日付のみUPDATE
        if( 0 < $intLogRows )
        {
            // UPDATE文
            $strSQL =   " UPDATE                    " .
                        "   access_log              " .
                        " SET                       " .
                        "   date       =  sysdate() " .
                        " WHERE                     " .
                        "     login_id          =   " . SQLAid::escapeStr( $strLoginID    ) .
                        $strWhere                     .
                        " AND log_flag          =   " . SQLAid::escapeNum( $intLogFlag  ) ;
        }
        else
        {
            // ユーザエージェント取得(UA, OS, オリジナル)
            $arrayUA_and_OS = AccessLog::GetUAandOS();

            // SQL作成
            $strSQL =   " INSERT INTO access_log    " .
                        " (                         " .
                        "   date                   ," .
                        "   week_day               ," .
                        "   ip_address             ," .
                        "   remote_host            ," .
                        "   host_domain            ," .
                        "   view_page_url          ," .
                        "   login_id               ," .
                        "   log_flag               ," .
                        "   info_id                ," .
                        "   album_id               ," .
                        "   thread_id              ," .
                        "   ua                     ," .
                        "   os                     ," .
                        "   ua_org                 ," .
                        "   docomo_utn             ," .
                        "   au_subno               ," .
                        "   softbank_no             " .
                        " )                         " .
                        " VALUES                    " .
                        " (                         " .
                        "    sysdate() "                                  . ", " .
                        "    " . SQLAid::escapeStr( $strWeekDay         ) . ", " .
                        "    " . SQLAid::escapeStr( $strIpAddress       ) . ", " .
                        "    " . SQLAid::escapeStr( $strRemortHost      ) . ", " .
                        "    " . SQLAid::escapeStr( $strDomain          ) . ", " .
                        "    " . SQLAid::escapeStr( $strPageURL         ) . ", " .
                        "    " . SQLAid::escapeStr( $strLoginID         ) . ", " .
                        "    " . SQLAid::escapeStr( $intLogFlag         ) . ", " .
                        "    " . SQLAid::escapeNum( $intInfoID          ) . ", " .
                        "    " . SQLAid::escapeNum( $intAlbumID         ) . ", " .
                        "    " . SQLAid::escapeNum( $intThreadID        ) . ", " .
                        "    " . SQLAid::escapeStr( $arrayUA_and_OS[0]  ) . ", " .
                        "    " . SQLAid::escapeStr( $arrayUA_and_OS[1]  ) . ", " .
                        "    " . SQLAid::escapeStr( $arrayUA_and_OS[2]  ) . ", " .
                        "    " . SQLAid::escapeStr( $arrayUA_and_OS[3]  ) . ", " .
                        "    " . SQLAid::escapeStr( $arrayUA_and_OS[4]  ) . ", " .
                        "    " . SQLAid::escapeStr( $arrayUA_and_OS[5]  ) .
                        ")                          " ;
        }

        // ＤＢ接続
        $db = new WrapDB();
        $db->ConnectDB();

        //トランザクション開始
        $db->AutoCommitOffDB();

        // SQL実行
        $objAccess = $db->QueryDB( $strSQL );

        // 結果成功したらCOMMIT、失敗ならROLLBACKしてエラーメッセージ設定
        if( DB_OK == $objAccess )
        {
            //成功
            $db->TransactDB( db_strDB_COMMIT );
            $db->CloseDB();
        }
        else
        {
            //失敗
            $db->TransactDB( db_strDB_ROLLBACK );
            $db->CloseDB();
        }
    }

    // ***********************************************************************
    // * 関数名     ：アクセスログ取得(ログインID、ID版)
    // * 返り値     ：成功:SELECT結果、失敗:null
    // ***********************************************************************
    function GetLogByID(    $strLoginID ,
                            $intLogFlag ,
                            $intID      ,
                            &$intRows   )
    {
        // ＤＢ接続
        $db = new WrapDB();
        $db->ConnectDB();

        // SELECTする
        $strSQL =   " SELECT                 " .
                    "   date                 " .
                    " FROM                   " .
                    "   access_log           " .
                    " WHERE 0=0              " .
                    " AND login_id       =   " . SQLAid::escapeStr( $strLoginID    ) .
                    " AND log_flag       =   " . SQLAid::escapeNum( $intLogFlag    ) ;

        // 管理本部通信の場合
        if( LOG_INFO_FLAG == $intLogFlag )
        {
            $strSQL =   $strSQL .
                        " AND info_id        =   " . SQLAid::escapeNum( $intID ) ;
        }
        // フォトアルバムの場合
        elseif( LOG_ALBUM_FLAG == $intLogFlag )
        {
            $strSQL =   $strSQL .
                        " AND album_id       =   " . SQLAid::escapeNum( $intID ) ;
        }
        // BBSスレッドの場合
        else
        {
            $strSQL =   $strSQL .
                        " AND thread_id      =   " . SQLAid::escapeNum( $intID ) ;
        }

//echo($strSQL);
        // SQL実行
        $objInfo = $db->QueryDB( $strSQL );

        // 実行結果行数を取得
        $intRows = $db->GetNumRowsDB( $objInfo );

        // 取得したデータを配列に格納
        if( 0 < $intRows )
        {
            // 取得行を配列化
            $arrayResult = $db->FetchArrayDB( $objInfo );

            $arrayInfo   = array(   'date'  =>  $arrayResult["date"]    );
        }
        else
        {
            $arrayInfo = null;
        }

        return $arrayInfo;
    }

    //---------------------------------------------------------
    //  使用OS・ブラウザ解析 関数
    //---------------------------------------------------------
    function GetUAandOS()
    {
        // UA情報が存在する時は取得する
        if( isset( $_SERVER['HTTP_USER_AGENT'] ) )
        {
            $ua = $_SERVER['HTTP_USER_AGENT'];
        }
        // UA情報が存在しない時は空白を返す
        else
        {
            return array('','');
        }

        ////////////////////////////////////////////////////////////////////////////////

        // [UAの判定](Versionを受け取り(hits)付加する) //

        // Operaの時
        if( preg_match( '/Opera[ \/](\d\.\d)/', $ua, $hits ) )
        {
            $br = 'Opera ' . $hits[1];
        }
        // Lunascape の時
        elseif( preg_match( '/Lunascape (\d\.\d)/', $ua, $hits ) )
        {
            $br = 'Lunascape ' . $hits[1];
        }
        // Sleipnir の時
        elseif( preg_match( '/Sleipnir( Version |\/)(\d\.\d)/', $ua, $hits ) )
        {
            $br = 'Sleipnir ' . $hits[2];
        }
        // IE の時
        elseif( preg_match( '/MSIE (\d\.\d)/', $ua, $hits ) )
        {
            $br = 'IE ' . $hits[1];
        }
        // Netscape6以上の時
        elseif( preg_match( '/(Netscape|Navigator)[^\/]*\/(\d\.\d)/', $ua, $hits ) )
        {
            $br = 'Netscape ' . $hits[2];
        }
        // Chromeの時
        elseif( preg_match( '/Chrome\/(\d\.\d)/', $ua, $hits ) )
        {
            $br = 'Chrome ' . $hits[1];
        }
        // Safari3以上の時
        elseif( preg_match( '/Version\/(\d\.\d) Safari/', $ua, $hits ) )
        {
            $br = 'Safari ' . $hits[1];
        }
        // Safariの時
        elseif( preg_match('/Safari\/(\d*)/', $ua, $hits ) )
        {
            // ビルドNoによってバージョン判定
            if(    $hits[1] > 500)
            {
                $br = 'Safari 3.0';
            }
            elseif($hits[1] > 400)
            {
                $br = 'Safari 2.0';
            }
            elseif($hits[1] > 300)
            {
                $br = 'Safari 1.3';
            }
            elseif($hits[1] > 120)
            {
                $br = 'Safari 1.2';
            }
            elseif($hits[1] > 100)
            {
                $br = 'Safari 1.1';
            }
            else
            {
                $br = 'Safari 1.0';
            }
        }
        // Mozilla Firefoxの時
        elseif( preg_match( '/Firefox\/(\d\.\d)/', $ua, $hits ) )
        {
            $br = 'Firefox ' . $hits[1];
        }
        // Mozillaの時
        elseif( preg_match( '/rv:(\d\.\d)[^\)]*\) Gecko/', $ua, $hits ) )
        {
            $br = 'Mozilla ' . $hits[1];
        }
        // Netscape4以下の時
        elseif( preg_match( '/Mozilla\/(\d\.\d)(\d*) \[/', $ua, $hits ) )
        {
            $br = 'Netscape ' . $hits[1];
        }
        // PSPの時
        elseif( preg_match( '/PlayStation Portable\); (\d\.\d)/', $ua, $hits ) )
        {
            $br = 'PSP ' . $hits[1];
        }
        // Lynxの時
        elseif( preg_match( '/Lynx\/(\d\.\d)/', $ua, $hits ) )
        {
            $br = 'Lynx ' . $hits[1];
        }
        // Googlebotの時
        elseif( preg_match( '/Googlebot/', $ua, $hits ) )
        {
            $br = 'Googlebot';
        }
        // Yahoo! Slurpの時
        elseif( preg_match( '/Yahoo! Slurp/', $ua, $hits ) )
        {
            $br = 'Yahoo! Slurp';
        }
        // MSNBotの時
        elseif( preg_match( '/msnbot/', $ua, $hits ) )
        {
            $br = 'MSNBot';
        }
        // Ask Jeevesの時
        elseif( preg_match( '/Ask Jeeves/', $ua, $hits ) )
        {
            $br = 'Ask Jeeves';
        }
        // libwww-perlの時
        elseif( preg_match( '/libwww-perl\/(\d\.\d)/', $ua, $hits ) )
        {
            $br = 'libwww-perl ' . $hits[1];
        }
        // jig browserの時
        elseif( preg_match( '/jig browser web; (\d\.\d)/', $ua, $hits ) )
        {
            $br = 'jig browser ' . $hits[1];
        }
        // ドコモの時
        elseif( preg_match( '/DoCoMo\/\d\.\d(\/| )([^\/\(]*)/', $ua, $hits ) )
        {
            $br = 'DoCoMo ' . $hits[2];
            $strSerialNoDocomo = $_SERVER['HTTP_X_DCMGUID'];
        }
        // au の時
        elseif( preg_match( '/KDDI-(\w*) UP.Browser/', $ua, $hits ) )
        {
            $br = 'au ' . $hits[1];
            $strSerialNoAu = $_SERVER['HTTP_X_UP_SUBNO'];
        }
        // ソフトバンクの時
        elseif( preg_match( '/(SoftBank|Vodafone|J-PHONE)\/\d\.\d\/([^\/_]*)/', $ua, $hits ) )
        {
            $br = 'SoftBank ' . $hits[2];
            $strSerialNoSb = $_SERVER['HTTP_X_JPHONE_UID'];
        }
        // 不明の時
        else
        {
            // UA情報をそのまま利用
            $br = $ua;

            // mb_convert_encoding が利用出来る時
            if( function_exists('mb_convert_encoding') )
            {
                // 文字コードが「SJIS」では無い時
                if(mb_detect_encoding($br) != 'SJIS')
                {
                    // 文字コードを「SJIS」に変換
                    $br = mb_convert_encoding($br,'SJIS','ASCII,JIS,UTF-8,EUC-JP,SJIS');
                }
            }

            // バックスラッシュを削除
            $br = stripslashes($br);

            // 実体文字参照に変換する
            $br = preg_replace('/,/','&#44;',$br);
            $br = preg_replace('/</','&lt;' ,$br);
            $br = preg_replace('/>/','&gt;' ,$br);
        }

        ////////////////////////////////////////////////////////////////////////////////

        // [OSの判定] //

        // Windows の時
            if(preg_match('/Windows Vista|NT 6.0/',$ua,$oss)){$os = 'Windows Vista';}
        elseif(preg_match('/Windows XP|NT 5.1/'   ,$ua,$oss)){$os = 'Windows XP';}
        elseif(preg_match('/Windows 2000|NT 5.0/' ,$ua,$oss)){$os = 'Windows 2000';}
        elseif(preg_match('/Windows ME|Win 9x/'   ,$ua,$oss)){$os = 'Windows Me';}
        elseif(preg_match('/Windows 98|Win98/'    ,$ua,$oss)){$os = 'Windows 98';}
        elseif(preg_match('/Windows 95|Win95/'    ,$ua,$oss)){$os = 'Windows 95';}

        elseif(preg_match('/NT 5.2/'    ,$ua,$oss)){$os = 'Windows Server 2003';}
        elseif(preg_match('/NT(\d\.\d)/',$ua,$oss)){$os = 'Windows NT' . $oss[1];}
        elseif(preg_match('/Windows CE/',$ua,$oss)){$os = 'Windows CE';}

        // Mac OS の時
        elseif(preg_match('/Mac OS X (\d\d)_(\d)/',$ua,$oss)){$os = 'Mac OS X ' . $oss[1] . '.' . $oss[2];}
        elseif(preg_match('/Mac OS X/'  ,$ua,$oss)){$os = 'Mac OS X';}
        elseif(preg_match('/Mac/'       ,$ua,$oss)){$os = 'Mac OS';}

        // Linux の時
        elseif(preg_match('/TurboLinux/',$ua,$oss)){$os = 'Turbo Linux';}
        elseif(preg_match('/VineLinux/' ,$ua,$oss)){$os = 'Vine Linux';}
        elseif(preg_match('/Red Hat/'   ,$ua,$oss)){$os = 'RedHat Linux';}
        elseif(preg_match('/Debian/'    ,$ua,$oss)){$os = 'Debian Linux';}
        elseif(preg_match('/Fedora/'    ,$ua,$oss)){$os = 'Fedora';}
        elseif(preg_match('/Linux/'     ,$ua,$oss)){$os = 'Linux';}

        // Solaris の時
        elseif(preg_match('/SunOS/',$ua,$oss)){$os = 'Solaris';}

        // 不明の時
        else{$os = '';}

        // UA,OS を返す
        return array( $br, $os, $ua, $strSerialNoDocomo, $strSerialNoAu, $strSerialNoSb );

    }

    // ***********************************************************************
    // * 関数名     ：閲覧状況ユーザ一覧
    // * 返り値     ：成功:SELECT結果、失敗:null
    // ***********************************************************************
    function GetAccessUser( $strID      ,   // ID
                            $intFlag    ,   // LOG_INFO_FLAG:管理本部通信、LOG_ALBUM_FLAG:フォトアルバム、LOG_THREAD_FLAG:スレッド
                            $strDelFlag ,   // 公開レベル(0:全体、1:グループ限定、2:非公開)
                            &$intRows   )
    {
        // ＤＢ接続
        $db = new WrapDB();
        $db->ConnectDB();

        // 公開レベルによって取得するユーザ一覧を変える
        // (全体公開、非公開は、有効な全ユーザ)
        if( 1 != intval( $strDelFlag ) )
        {
            // SELECTする
            $strSQL =   " SELECT                                             " .
                        "   user.user_id                as  user_id         ," .
                        "   user.user_name              as  user_name       ," .
                        "   user.user_name_kana         as  user_name_kana  ," .
                        "   user.login_id               as  login_id        ," .
                        "   user.password               as  password        ," .
                        "   user.last_login_time        as  last_login_time ," .
                        "   user.notes                  as  notes           ," .
                        "   user.executive              as  executive       ," .
                        "   user.manage_auth            as  manage_auth     ," .
                        "   user.create_date            as  create_date     ," .
                        "   user.del_flag               as  del_flag        ," .
                        "   IF( access_log.date, 1, 0 ) as  access_flag     ," .
                        "   access_log.date             as  date             " .
                        " FROM               " .
                        "   user             " .
                        "   LEFT JOIN access_log " .
                        "       ON  user.login_id       = access_log.login_id   " .
                        "       AND access_log.log_flag = " . SQLAid::escapeNum( $intFlag ) ;

            // (管理本部通信)
            if( LOG_INFO_FLAG == $intFlag )
            {
                $strSQL =   $strSQL .
                        "       AND access_log.info_id =    " . SQLAid::escapeNum( intval( $strID ) ) ;
            }
            // (フォトアルバム)
            elseif( LOG_ALBUM_FLAG == $intFlag )
            {
                $strSQL =   $strSQL .
                        "       AND access_log.album_id =   " . SQLAid::escapeNum( intval( $strID ) ) ;
            }
            // (スレッド)
            else
            {
                $strSQL =   $strSQL .
                        "       AND access_log.thread_id =  " . SQLAid::escapeNum( intval( $strID ) ) ;
            }

            $strSQL =   $strSQL .
                        " WHERE 0=0             " .
                        " AND user.del_flag = 0 " .
                        " ORDER BY              " .
                        "   user_id ASC         " ;
        }
        // (公開レベル選択の場合)
        else
        {
            // 管理本部通信と公開グループを軸にSELECTする
            $strSQL =   " SELECT                                         " .
                        "   us.user_id              as  user_id         ," .
                        "   us.user_name            as  user_name       ," .
                        "   us.user_name_kana       as  user_name_kana  ," .
                        "   us.login_id             as  login_id        ," .
                        "   us.password             as  password        ," .
                        "   us.last_login_time      as  last_login_time ," .
                        "   us.notes                as  notes           ," .
                        "   us.executive            as  executive       ," .
                        "   us.manage_auth          as  manage_auth     ," .
                        "   us.create_date          as  create_date     ," .
                        "   us.del_flag             as  del_flag        ," .
                        "   IF( log.date, 1, 0 )    as  access_flag     ," .
                        "   log.date                as  date             " .
                        " FROM                                           " ;

            // (管理本部通信)
            if( LOG_INFO_FLAG == $intFlag )
            {
                $strSQL =   $strSQL .
                            "               information         info            " .     // 管理本部通信
                            "   INNER JOIN  information_user    info_group      " .     // 公開グループ
                            "       ON  info.info_id    = info_group.info_id    " .
                            "       AND info_group.flag = 1                     " .
                            "   INNER JOIN  group_users         g_users         " .     // グループユーザ情報
                            "       ON  info_group.user_or_group_id = g_users.group_id  " .
                            "   INNER JOIN  user                us              " .     // ユーザ情報
                            "       ON  g_users.user_id = us.user_id            " .
                            "   LEFT  JOIN  access_log          log             " .
                            "       ON  us.login_id       = log.login_id        " .
                            "       AND log.log_flag =  " . SQLAid::escapeNum( $intFlag         ) .
                            "       AND log.info_id  =  " . SQLAid::escapeNum( intval( $strID ) ) .
                            " WHERE 0=0                 " .
                            " AND info.info_id =        " . SQLAid::escapeNum( intval( $strID ) ) ;
            }
            // (フォトアルバム)
            elseif( LOG_ALBUM_FLAG == $intFlag )
            {
                $strSQL =   $strSQL .
                            "               photo_album         album                       " .     // フォトアルバム
                            "   INNER JOIN  photo_album_user    album_group                 " .     // 公開グループ
                            "       ON  album.album_id    = album_group.album_id            " .
                            "       AND album_group.flag = 1                                " .
                            "   INNER JOIN  group_users         g_users                     " .     // グループユーザ情報
                            "       ON  album_group.user_or_group_id = g_users.group_id     " .
                            "   INNER JOIN  user                us                          " .     // ユーザ情報
                            "       ON  g_users.user_id = us.user_id                        " .
                            "   LEFT  JOIN  access_log          log                         " .
                            "       ON  us.login_id       = log.login_id                    " .
                            "       AND log.log_flag  = " . SQLAid::escapeNum( $intFlag         ) .
                            "       AND log.album_id  = " . SQLAid::escapeNum( intval( $strID ) ) .
                            " WHERE 0=0                 " .
                            " AND album.album_id =      " . SQLAid::escapeNum( intval( $strID ) ) ;
            }
            // (スレッド)
            else
            {
                $strSQL =   $strSQL .
                            "               thread_master       thread                      " .     // スレッド
                            "   INNER JOIN  thread_user         thread_group                " .     // 公開グループ
                            "       ON  thread.thread_id    = thread_group.thread_id        " .
                            "       AND thread_group.flag = 1                               " .
                            "   INNER JOIN  group_users         g_users                     " .     // グループユーザ情報
                            "       ON  thread_group.user_or_group_id = g_users.group_id    " .
                            "   INNER JOIN  user                us                          " .     // ユーザ情報
                            "       ON  g_users.user_id = us.user_id                        " .
                            "   LEFT  JOIN  access_log          log                         " .
                            "       ON  us.login_id       = log.login_id                    " .
                            "       AND log.log_flag  = " . SQLAid::escapeNum( $intFlag         ) .
                            "       AND log.thread_id = " . SQLAid::escapeNum( intval( $strID ) ) .
                            " WHERE 0=0                 " .
                            " AND thread.thread_id =    " . SQLAid::escapeNum( intval( $strID ) ) ;
            }

            $strSQL =   $strSQL .
                        " GROUP BY          " .
                        "   us.user_id      " .
                        " ORDER BY          " .
                        "   us.user_id ASC  " ;
        }

        // SQL実行
        $objUser = $db->QueryDB( $strSQL );

        // 実行結果行数を取得
        $intRows = $db->GetNumRowsDB( $objUser );

        // 取得行を配列化
        $arrayUser = array();
        for( $intCount = 0; $intCount < $intRows; $intCount++ )
        {
            $arrayResult = $db->FetchArrayDB( $objUser );

            // 取得したデータを配列に格納
            $arrayUser[] = array(   'user_id'           => $arrayResult["user_id"]          ,
                                    'user_name'         => $arrayResult["user_name"]        ,
                                    'user_name_kana'    => $arrayResult["user_name_kana"]   ,
                                    'login_id'          => $arrayResult["login_id"]         ,
                                    'password'          => $arrayResult["password"]         ,
                                    'last_login_time'   => $arrayResult["last_login_time"]  ,
                                    'notes'             => $arrayResult["notes"]            ,
                                    'executive'         => $arrayResult["executive"]        ,
                                    'manage_auth'       => $arrayResult["manage_auth"]      ,
                                    'create_date'       => $arrayResult["create_date"]      ,
                                    'del_flag'          => $arrayResult["del_flag"]         ,
                                    'access_flag'       => $arrayResult["access_flag"]      ,
                                    'date'              => $arrayResult["date"]             );
        }

        return $arrayUser;
    }

}
