<?php
/* ================================================================================
 * ファイル名   ：UserDB.php
 * タイトル     ：ユーザ情報取得・更新用クラス
 * 作成者       ：間宮 直樹
 * 作成日       ：2008/08/17
 * 内容         ：ユーザ情報の取得・更新処理を実施する。
 * 更新履歴*******************************************************************
 * 【変更日】  【変更者】      【変更箇所】        【変更理由と変更内容】
 *  2008/08/17  間宮 直樹       全体                新規作成
 * ================================================================================*/

$strPath = substr( $_SERVER['SCRIPT_FILENAME'], 0, strpos( $_SERVER['SCRIPT_FILENAME'], 'public_html' ) - 1 + strlen( "/" . 'public_html' . "/" ) );
$strPath = $strPath . "admin/";

// includeするライブラリ・コンフィグファイルのパスを定義する
define( 'LIB_DIR'   , $strPath . '_lib/');
define( 'CONFIG_DIR', $strPath . '_config/');

// ライブラリ・コンフィグファイルをinclude
include_once( LIB_DIR    . 'DataConvert.php' );         // データ変換用関数群
include_once( LIB_DIR    . 'WrapDB.php' );              // ＤＢ用関数群
include_once( LIB_DIR    . 'SQLAid.php' );              // ＳＱＬ条件文関数群
include_once( LIB_DIR    . 'AccessLog.php' );           // アクセスログ関数群
include_once( CONFIG_DIR . 'db_conf.php' );             // データベース定義ファイル
include_once( CONFIG_DIR . 'manage_conf.php' );         // 管理画面基本情報定義ファイル

class UserDB
{
    // ***********************************************************************
    // * 関数名     ：ユーザ情報内容ＤＢ登録処理
    // * 機能概要   ：ユーザ情報をＤＢに登録する
    // * 返り値     ：送信成功:true、送信失敗:false
    // ***********************************************************************
    function InsertUserData(    $strUserName        ,   // ユーザ名（漢字）
                                $strUserNameKana    ,   // ユーザ名（カナ）
                                $strLoginID         ,   // ログインID
                                $strPassword        ,   // パスワード
                                $strMailPc          ,   // PCメールアドレス
                                $strMailMobile      ,   // 携帯メールアドレス
                                $strMailBbs         ,   // BBS用メールアドレス
                                $strStatus          ,   // 役職
                                $strSectionID       ,   // 事業部
                                $strManageAuth      ,   // 管理権限
                                $strNotes           ,   // 備考
                                $strDelFlag         )   // 削除フラグ
    {
        $strErrorMessage = "";

        // ＤＢ接続
        $db = new WrapDB();
        $db->ConnectDB();

        //トランザクション開始
        $db->AutoCommitOffDB();

        // INSERTする
        $strSQL =   " INSERT INTO user       " .
                    " (                      " .
                    "   user_name           ," .
                    "   user_name_kana      ," .
                    "   login_id            ," .
                    "   password            ," .
                    "   mail_address_pc     ," .
                    "   mail_address_mobile ," .
                    "   bbs_write_mailaddress ," .
                    "   executive           ," .
                    "   section_id          ," .
                    "   manage_auth         ," .
                    "   notes               ," .
                    "   create_date         ," .
                    "   del_flag             " .
                    " )                      " .
                    " VALUES                 " .
                    " (                      " .
                    "    " . SQLAid::escapeStr( $strUserName                ) . ", " .
                    "    " . SQLAid::escapeStr( $strUserNameKana            ) . ", " .
                    "    " . SQLAid::escapeStr( $strLoginID                 ) . ", " .
                    "    " . SQLAid::escapeStr( $strPassword                ) . ", " .
                    "    " . SQLAid::escapeStr( $strMailPc                  ) . ", " .
                    "    " . SQLAid::escapeStr( $strMailMobile              ) . ", " .
                    "    " . SQLAid::escapeStr( $strMailBbs                 ) . ", " .
                    "    " . SQLAid::escapeNum( intval( $strStatus )        ) . ", " .
                    "    " . SQLAid::escapeNum( intval( $strSectionID )     ) . ", " .
                    "    " . SQLAid::escapeNum( intval( $strManageAuth )    ) . ", " .
                    "    " . SQLAid::escapeStr( $strNotes                   ) . ", " .
                    "    sysdate()                                               , " .
                    "    " . SQLAid::escapeNum( intval( $strDelFlag )       ) .
                    ")                   " ;
        // SQL実行
        $objUser = $db->QueryDB( $strSQL );

        // 結果成功したらCOMMIT、失敗ならROLLBACKしてエラーメッセージ設定
        if( DB_OK == $objUser )
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
            $strErrorMessage = "更新情報登録エラー";
        }

        return $strErrorMessage;
    }

    // ***********************************************************************
    // * 関数名     ：ユーザ情報内容ＤＢ更新処理
    // * 機能概要   ：ユーザ情報内容をＤＢ更新する
    // * 返り値     ：成功:true、失敗:false
    // ***********************************************************************
    function UpdateUserData(    $strUserID          ,   // ユーザID
                                $strUserName        ,   // ユーザ名（漢字）
                                $strUserNameKana    ,   // ユーザ名（カナ）
                                $strLoginID         ,   // ログインID
                                $strPassword        ,   // パスワード
                                $strMailPc          ,   // PCメールアドレス
                                $strMailMobile      ,   // 携帯メールアドレス
                                $strMailBbs         ,   // BBS用メールアドレス
                                $strStatus          ,   // 役職
                                $strSectionID       ,   // 事業部
                                $strManageAuth      ,   // 管理権限
                                $strNotes           ,   // 備考
                                $strDelFlag         )   // 削除フラグ
    {
        $strErrorMessage = "";

        // ＤＢ接続
        $db = new WrapDB();
        $db->ConnectDB();

        //トランザクション開始
        $db->AutoCommitOffDB();

        // UPDATE文
        $strSQL =   " UPDATE                    " .
                    "   user                    " .
                    " SET                       " .
                    "   user_name           =   " . SQLAid::escapeStr( $strUserName             ) . ", " .
                    "   user_name_kana      =   " . SQLAid::escapeStr( $strUserNameKana         ) . ", " .
                    "   login_id            =   " . SQLAid::escapeStr( $strLoginID              ) . ", " .
                    "   password            =   " . SQLAid::escapeStr( $strPassword             ) . ", " .
                    "   mail_address_pc     =   " . SQLAid::escapeStr( $strMailPc               ) . ", " .
                    "   mail_address_mobile =   " . SQLAid::escapeStr( $strMailMobile           ) . ", " .
                    "   bbs_write_mailaddress=  " . SQLAid::escapeStr( $strMailBbs              ) . ", " .
                    "   executive           =   " . SQLAid::escapeNum( intval( $strStatus     ) ) . ", " .
                    "   section_id          =   " . SQLAid::escapeNum( intval( $strSectionID  ) ) . ", " .
                    "   manage_auth         =   " . SQLAid::escapeNum( intval( $strManageAuth ) ) . ", " .
                    "   notes               =   " . SQLAid::escapeStr( $strNotes                ) . ", " .
                    "   del_flag            =   " . SQLAid::escapeNum( intval( $strDelFlag )    ) . ", " .
                    "   update_date         =   sysdate() " .
                    " WHERE                     " .
                    "   user_id             =   " . SQLAid::escapeNum( intval( $strUserID )     ) ;

        // SQL実行
        $objUser = $db->QueryDB( $strSQL );

        // 結果成功したらCOMMIT、失敗ならROLLBACKしてエラーメッセージ設定
        if( DB_OK == $objUser )
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
            $strErrorMessage = "更新情報更新エラー";
        }

        return $strErrorMessage;
    }

    // ***********************************************************************
    // * 関数名     ：ユーザ情報内容ＤＢ更新処理
    // * 機能概要   ：ユーザ情報内容をＤＢ更新する
    // * 返り値     ：成功:true、失敗:false
    // ***********************************************************************
    function UpdateUserLogin(   $strLoginID         )   // ログインID
    {
        $strErrorMessage = "";

        // ユーザエージェント取得(UA, OS, オリジナル)
        $arrayUA_and_OS = AccessLog::GetUAandOS();

        // モバイルの場合(OS取得できなかった場合)
        $strUA = "";
        if( 0 == strlen( $arrayUA_and_OS[1] ) )
        {
            // キャリア・機種取得
            $strUA = $arrayUA_and_OS[0];
        }

        // ＤＢ接続
        $db = new WrapDB();
        $db->ConnectDB();

        //トランザクション開始
        $db->AutoCommitOffDB();

        // UPDATE文
        $strSQL =   " UPDATE                " .
                    "   user                " .
                    " SET                   " .
                    "   last_login_time =   sysdate() , " ;

        // 携帯からのアクセスの場合、ユーザエージェント保存
        if( 0 < strlen( $strUA ) )
        {
            $strSQL =   $strSQL .
                    "   user_agent      =   " . SQLAid::escapeStr( $arrayUA_and_OS[2]   ) . ", " .
                    "   mobile_model    =   " . SQLAid::escapeStr( $strUA               ) . ", " ;
        }

        $strSQL =   $strSQL .
                    "   update_date     =   sysdate() " .
                    " WHERE                 " .
                    "   login_id        =   " . SQLAid::escapeStr( $strLoginID          ) ;

        // SQL実行
        $objUser = $db->QueryDB( $strSQL );

        // 結果成功したらCOMMIT、失敗ならROLLBACKしてエラーメッセージ設定
        if( DB_OK == $objUser )
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
            $strErrorMessage = "更新情報更新エラー";
        }

        return $strErrorMessage;
    }

    // ***********************************************************************
    // * 関数名     ：ユーザ情報取得処理
    // * 機能概要   ：ユーザ情報をすべて取得する
    // * 返り値     ：成功:SELECT結果、失敗:null
    // ***********************************************************************
    function GetUserAll(    &$intRows   ,   // 取得できたレコード数
                            $strWhere = " WHERE staff_status = 0 "  )
    {
        // ＤＢ接続
        $db = new WrapDB();
        $db->ConnectDB();

        // SELECTする
        $strSQL =   " SELECT                                             " .
                    "   user.user_id                as  user_id         ," .
                    "   user.user_name              as  user_name       ," .
                    "   user.user_name_kana         as  user_name_kana  ," .
                    "   user.login_id               as  login_id        ," .
                    "   user.password               as  password        ," .
                    "   user.mail_address_pc        as  mail_address_pc ," .
                    "   user.staff_status           as  staff_status    ," .
                    "   status_master.status_id     as  status_id       ," .
                    "   status_master.status_flag   as  status_flag     ," .
                    "   status_master.status_name   as  status_name     ," .
                    "   user.last_login_time        as  last_login_time ," .
                    "   user.notes                  as  notes           ," .
                    "   user.executive              as  executive       ," .
                    "   user.section_id             as  section_id      ," .
                    "   section_master.section_name as  section_name    ," .
                    "   user.manage_auth            as  manage_auth     ," .
                    "   user.create_date            as  create_date     ," .
                    "   user.el_status              as  el_status       ," .
                    "   user.del_flag               as  del_flag         " .
                    " FROM               " .
                    "   user             " .
                    "   INNER JOIN status_master " .
                    "       ON  status_master.status_id = 0 " .
                    "       AND user.executive          = status_master.status_flag " .
                    "   LEFT  JOIN section_master " .
                    "       ON  section_master.section_id = user.section_id " .
                    $strWhere .
                    " ORDER BY           " .
                    "   user_id ASC      " ;
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
                                    'mail_address_pc'   => $arrayResult["mail_address_pc"]  ,
                                    'staff_status'      => $arrayResult["staff_status"]     ,
                                    'status_id'         => $arrayResult["status_id"]        ,
                                    'status_flag'       => $arrayResult["status_flag"]      ,
                                    'status_name'       => $arrayResult["status_name"]      ,
                                    'last_login_time'   => $arrayResult["last_login_time"]  ,
                                    'notes'             => $arrayResult["notes"]            ,
                                    'executive'         => $arrayResult["executive"]        ,
                                    'section_id'        => $arrayResult["section_id"]       ,
                                    'section_name'      => $arrayResult["section_name"]     ,
                                    'manage_auth'       => $arrayResult["manage_auth"]      ,
                                    'create_date'       => $arrayResult["create_date"]      ,
                                    'el_status'         => $arrayResult["el_status"]        ,
                                    'del_flag'          => $arrayResult["del_flag"]         );
        }

        return $arrayUser;
    }

    // ***********************************************************************
    // * 関数名     ：ユーザ情報取得処理(指定ID)
    // * 返り値     ：成功:SELECT結果、失敗:null
    // ***********************************************************************
    function GetUserOfID(   $arrayUserID    ,   // ユーザID（配列）
                            &$intRows       )   // 取得できたレコード数
    {
        // ＤＢ接続
        $db = new WrapDB();
        $db->ConnectDB();

        // SELECTする
        $strSQL =   " SELECT            " .
                    "  user_id         ," .
                    "  user_name       ," .
                    "  user_name_kana  ," .
                    "  login_id        ," .
                    "  password        ," .
                    "  mail_address_pc ," .
                    "  last_login_time ," .
                    "  notes           ," .
                    "  executive       ," .
                    "  section_id      ," .
                    "  manage_auth     ," .
                    "  create_date     ," .
                    "  el_status       ," .
                    "  del_flag         " .
                    " FROM              " .
                    "   user            " .
                    " WHERE 0 = 0       " .
                    " AND  user_id in ( " ;

        // 配列数分、IN句を設定する
        $strUserQueryIn = "";
        for( $intCount = 0; $intCount < count( $arrayUserID ); $intCount++ )
        {
            if( 0 < strlen( $strUserQueryIn ) )
            {
                $strUserQueryIn = $strUserQueryIn . ", ";
            }

            $strUserQueryIn =   $strUserQueryIn .
                                SQLAid::escapeNum( $arrayUserID[ $intCount ] );
        }

        $strSQL =   $strSQL .
                    $strUserQueryIn .
                    "   ) " .
                    " GROUP BY user.user_id " .
                    " ORDER BY          " .
                    "   user_id ASC     " ;
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
                                    'mail_address_pc'   => $arrayResult["mail_address_pc"]  ,
                                    'last_login_time'   => $arrayResult["last_login_time"]  ,
                                    'notes'             => $arrayResult["notes"]            ,
                                    'executive'         => $arrayResult["executive"]        ,
                                    'section_id'        => $arrayResult["section_id"]       ,
                                    'manage_auth'       => $arrayResult["manage_auth"]      ,
                                    'create_date'       => $arrayResult["create_date"]      ,
                                    'el_status'         => $arrayResult["el_status"]        ,
                                    'del_flag'          => $arrayResult["del_flag"]         );
        }

        return $arrayUser;
    }

    // ***********************************************************************
    // * 関数名     ：ユーザ情報取得処理(１件のみ)
    // * 返り値     ：成功:SELECT結果、失敗:null
    // ***********************************************************************
    function GetInfoOne(    $intUserID      )
    {
        // ＤＢ接続
        $db = new WrapDB();
        $db->ConnectDB();

        // SELECTする
        $strSQL =   " SELECT                                                 " .
                    "   user.user_id                as  user_id             ," .
                    "   user.user_name              as  user_name           ," .
                    "   user.user_name_kana         as  user_name_kana      ," .
                    "   user.login_id               as  login_id            ," .
                    "   user.password               as  password            ," .
                    "   user.mail_address_pc        as  mail_address_pc     ," .
                    "   user.mail_address_mobile    as  mail_address_mobile ," .
                    "   user.bbs_write_mailaddress  as  bbs_write_mailaddress ," .
                    "   user.last_login_time        as  last_login_time     ," .
                    "   status_master.status_flag   as  status_flag         ," .
                    "   status_master.status_name   as  status_name         ," .
                    "   user.section_id             as  section_id          ," .
                    "   user.manage_auth            as  manage_auth         ," .
                    "   user.mobile_model           as  mobile_model        ," .
                    "   user.notes                  as  notes               ," .
                    "   user.create_date            as  create_date         ," .
                    "   user.el_status              as  el_status           ," .
                    "   user.del_flag               as  del_flag             " .
                    " FROM                                                   " .
                    "   user                                                 " .
                    "   INNER JOIN status_master                             " .
                    "       ON  status_master.status_id = 0                  " .
                    "       AND user.executive          = status_master.status_flag " .
                    " WHERE 0=0             " .
                    " AND   user_id   =     " . SQLAid::escapeNum( $intUserID );
        // SQL実行
        $objUser = $db->QueryDB( $strSQL );

        // 実行結果行数を取得
        $intRows = $db->GetNumRowsDB( $objUser );

        // 取得したデータを配列に格納
        if( 0 < $intRows )
        {
            // 取得行を配列化
            $arrayResult = $db->FetchArrayDB( $objUser );

            $arrayUser = array(     'user_id'               => $arrayResult["user_id"]              ,
                                    'user_name'             => $arrayResult["user_name"]            ,
                                    'user_name_kana'        => $arrayResult["user_name_kana"]       ,
                                    'login_id'              => $arrayResult["login_id"]             ,
                                    'password'              => $arrayResult["password"]             ,
                                    'mail_address_pc'       => $arrayResult["mail_address_pc"]      ,
                                    'mail_address_mobile'   => $arrayResult["mail_address_mobile"]  ,
                                    'bbs_write_mailaddress' => $arrayResult["bbs_write_mailaddress"],
                                    'last_login_time'       => $arrayResult["last_login_time"]      ,
                                    'status_flag'           => $arrayResult["status_flag"]          ,
                                    'status_name'           => $arrayResult["status_name"]          ,
                                    'section_id'            => $arrayResult["section_id"]           ,
                                    'manage_auth'           => $arrayResult["manage_auth"]          ,
                                    'mobile_model'          => $arrayResult["mobile_model"]         ,
                                    'notes'                 => $arrayResult["notes"]                ,
                                    'create_date'           => $arrayResult["create_date"]          ,
                                    'el_status'             => $arrayResult["el_status"]            ,
                                    'del_flag'              => $arrayResult["del_flag"]             );
        }
        else
        {
            $arrayUser = null;
        }

        return $arrayUser;
    }

    // ***********************************************************************
    // * 関数名     ：ログイン認証用ユーザデータ取得(１件のみ)
    // * 返り値     ：成功:SELECT結果、失敗:null
    // ***********************************************************************
    function GetLoginUser(  $strLoginID     ,
                            $strPassword    ,
                            $intLoginFlag   ,
                            &$intRows       )
    {
        // ＤＢ接続
        $db = new WrapDB();
        $db->ConnectDB();

        // SELECTする
        $strSQL =   " SELECT                " .
                    "   user_id             " .
                    " FROM                  " .
                    "   user                " .
                    " WHERE 0=0             " .
                    " AND   login_id    =   " . SQLAid::escapeStr( $strLoginID  ) .
                    " AND   password    =   " . SQLAid::escapeStr( $strPassword ) .
                    " AND   del_flag    = 0 " ;

        // 管理画面へのログインの場合は条件を追加
        if( LOGIN_MANAGE == $intLoginFlag )
        {
            $strSQL =   $strSQL .
                        " AND   manage_auth = 1 " ;
        }

        // SQL実行
        $objUser = $db->QueryDB( $strSQL );

        // 実行結果行数を取得
        $intRows = $db->GetNumRowsDB( $objUser );

        // 取得したデータを配列に格納
        if( 0 < $intRows )
        {
            // 取得行を配列化
            $arrayResult = $db->FetchArrayDB( $objUser );

            $arrayUser = array( 'user_id'   =>  $arrayResult["user_id"] );
        }
        else
        {
            $arrayUser = null;
        }

        return $arrayUser;
    }

    // ***********************************************************************
    // * 関数名     ：ログイン認証用ユーザデータ取得(かんたんログイン)
    // * 返り値     ：成功:SELECT結果、失敗:null
    // ***********************************************************************
    function GetLoginUserByMobileID(  &$intRows       )
    {
        $strMobileID = "";

        // 端末ID取得
        $arrayUA_and_OS = AccessLog::GetUAandOS();

        // ID取得判定
        // (取得できた場合)
        if( 0 < strlen( $arrayUA_and_OS[3] ) ||     // docomo
            0 < strlen( $arrayUA_and_OS[4] ) ||     // au
            0 < strlen( $arrayUA_and_OS[5] )   )    // softbank
        {
            if( 0 < strlen( $arrayUA_and_OS[3] ) )
            {
                $strMobileID = $arrayUA_and_OS[3];
            }
            elseif( 0 < strlen( $arrayUA_and_OS[4] ) )
            {
                $strMobileID = $arrayUA_and_OS[4];
            }
            elseif( 0 < strlen( $arrayUA_and_OS[5] ) )
            {
                $strMobileID = $arrayUA_and_OS[5];
            }
            else
            {
                $strMobileID = "dummy";
            }
        }
        // (取得できなかった場合)
        else
        {
            // NULLリターン
            $arrayUser = null;
            return $arrayUser;
        }

        // ＤＢ接続
        $db = new WrapDB();
        $db->ConnectDB();

        // SELECTする
        $strSQL =   " SELECT                " .
                    "   login_id            " .
                    " FROM                  " .
                    "   user                " .
                    " WHERE 0=0             " .
                    " AND   mobile_id   =   " . SQLAid::escapeStr( $strMobileID ) .
                    " AND   del_flag    = 0 " ;

        // SQL実行
        $objUser = $db->QueryDB( $strSQL );

        // 実行結果行数を取得
        $intRows = $db->GetNumRowsDB( $objUser );

        // 取得したデータを配列に格納
        if( 0 < $intRows )
        {
            // 取得行を配列化
            $arrayResult = $db->FetchArrayDB( $objUser );

            $arrayUser = array( 'login_id'  =>  $arrayResult["login_id"] );
        }
        else
        {
            $arrayUser = null;
        }

        return $arrayUser;
    }

    // ***********************************************************************
    // * 関数名     ：ログインID重複チェック用
    // * 返り値     ：成功:SELECT結果、失敗:null
    // ***********************************************************************
    function GetUserOfLoginID(  $strLoginID ,
                                &$intRows   )
    {
        // ＤＢ接続
        $db = new WrapDB();
        $db->ConnectDB();

        // SELECTする
        $strSQL =   " SELECT                " .
                    "   user_id            ," .
                    "   user_name          ," .
                    "   el_status          ," .
                    "   password            " .
                    " FROM                  " .
                    "   user                " .
                    " WHERE 0=0             " .
                    " AND   login_id    =   " . SQLAid::escapeStr( $strLoginID  ) ;

        // SQL実行
        $objUser = $db->QueryDB( $strSQL );

        // 実行結果行数を取得
        $intRows = $db->GetNumRowsDB( $objUser );

        // 取得したデータを配列に格納
        if( 0 < $intRows )
        {
            // 取得行を配列化
            $arrayResult = $db->FetchArrayDB( $objUser );

            $arrayUser = array( 'user_id'   =>  $arrayResult["user_id"]     ,
                                'user_name' =>  $arrayResult["user_name"]   ,
                                'el_status' =>  $arrayResult["el_status"]   ,
                                'password'  =>  $arrayResult["password"]    );
        }
        else
        {
            $arrayUser = null;
        }

        return $arrayUser;
    }

    // ***********************************************************************
    // * 関数名     ：BBS投稿アドレスからログインID取得
    // * 返り値     ：成功:SELECT結果、失敗:null
    // ***********************************************************************
    function GetLoginUserOfBbsMail( $strMail    ,
                                    &$intRows   )
    {
        // ＤＢ接続
        $db = new WrapDB();
        $db->ConnectDB();

        // SELECTする
        $strSQL =   " SELECT                " .
                    "   login_id            " .
                    " FROM                  " .
                    "   user                " .
                    " WHERE 0=0             " .
                    " AND   bbs_write_mailaddress like " . SQLAid::escapeLike( $strMail ) ;

        // SQL実行
        $objUser = $db->QueryDB( $strSQL );

        // 実行結果行数を取得
        $intRows = $db->GetNumRowsDB( $objUser );

        // 取得したデータを配列に格納
        if( 0 < $intRows )
        {
            // 取得行を配列化
            $arrayResult = $db->FetchArrayDB( $objUser );

            $arrayUser = array( 'login_id'   =>  $arrayResult["login_id"] );
        }
        else
        {
            $arrayUser = null;
        }

        return $arrayUser;
    }

    // ***********************************************************************
    // * 関数名     ：ログイン認証チェック
    // * 返り値     ：成功:SELECT結果、失敗:null
    // ***********************************************************************
    function GetLoginUserOfSession( $strSessionID   ,
                                    &$intRows       )
    {
        // ＤＢ接続
        $db = new WrapDB();
        $db->ConnectDB();

        // SELECTする
        $strSQL =   " SELECT                                                     " .
                    "   session.login_id            as  login_id                ," .
                    "   user.user_id                as  user_id                 ," .
                    "   user.user_name              as  user_name               ," .
                    "   user.manage_auth            as  manage_auth             ," .
                    "   user.mail_address_pc        as  mail_address_pc         ," .
                    "   user.mail_address_mobile    as  mail_address_mobile     ," .
                    "   user.bbs_write_mailaddress  as  bbs_write_mailaddress   ," .
                    "   user.el_status              as  el_status               ," .
                    "   user.mobile_id              as  mobile_id                " .
                    " FROM                  " .
                    "   session             " .
                    "   INNER JOIN user     " .
                    "       ON  user.login_id = session.login_id " .
                    " WHERE 0=0             " .
                    " AND   session_id  =   " . SQLAid::escapeStr( $strSessionID ) ;

        // SQL実行
        $objUser = $db->QueryDB( $strSQL );

        // 実行結果行数を取得
        $intRows = $db->GetNumRowsDB( $objUser );

        // 取得したデータを配列に格納
        if( 0 < $intRows )
        {
            // 取得行を配列化
            $arrayResult = $db->FetchArrayDB( $objUser );

            $arrayUser = array( 'login_id'              =>  $arrayResult["login_id"]                ,
                                'user_id'               =>  $arrayResult["user_id"]                 ,
                                'user_name'             =>  $arrayResult["user_name"]               ,
                                'manage_auth'           =>  $arrayResult["manage_auth"]             ,
                                'mail_address_pc'       =>  $arrayResult["mail_address_pc"]         ,
                                'mail_address_mobile'   =>  $arrayResult["mail_address_mobile"]     ,
                                'mobile_id'             =>  $arrayResult["mobile_id"]               ,
                                'el_status'             =>  $arrayResult["el_status"]               ,
                                'bbs_write_mailaddress' =>  $arrayResult["bbs_write_mailaddress"]   );

            // 認証OKの場合、最終アクセス日付を更新する
            UserDB::UpdateAccessDatetime( $strSessionID );
        }
        else
        {
            $arrayUser = null;
        }

        return $arrayUser;
    }

    // ***********************************************************************
    // * 関数名     ：ログイン後のセッション情報の更新処理
    // * 機能概要   ：指定のセッション情報が存在すればUPDATE、無ければINSERTする。
    // * 返り値     ：成功:true、失敗:false
    // ***********************************************************************
    function UpdateSessionOfLogin(  $strLoginID         ,   // ログインID（キー）
                                    $strSessionID       )   // セッションID
    {
        $intRetCode = true;

        // ＤＢ接続
        $db = new WrapDB();
        $db->ConnectDB();

        // 既にレコードが存在するかSELECT
        $strSelectSQL =     " SELECT                " .
                            "   session_id          " .
                            " FROM                  " .
                            "   session             " .
                            " WHERE 0=0             " .
                            " AND   login_id  =     " . SQLAid::escapeStr( $strLoginID ) ;

        // SQL実行
        $objSessionSelect = $db->QueryDB( $strSelectSQL );

        // 実行結果行数を取得
        $intSessionRows = $db->GetNumRowsDB( $objSessionSelect );

        // 携帯のキャリア・機種とguidを取得
        $arrayUA_and_OS = AccessLog::GetUAandOS();

        // 端末ID設定
        if( 0 < strlen( $arrayUA_and_OS[3] ) )
        {
            $strGuid = $arrayUA_and_OS[3];
        }
        elseif( 0 < strlen( $arrayUA_and_OS[4] ) )
        {
            $strGuid = $arrayUA_and_OS[4];
        }
        elseif( 0 < strlen( $arrayUA_and_OS[5] ) )
        {
            $strGuid = $arrayUA_and_OS[5];
        }

        // 機種・キャリア
        $strUA = $arrayUA_and_OS[0];

        //トランザクション開始
        $db->AutoCommitOffDB();

        // (存在すればUPDATE)
        if( 0 < $intSessionRows )
        {
            $strSQL =   " UPDATE                    " .
                        "   session                 " .
                        " SET                       " .
                        "   session_id          =   " . SQLAid::escapeStr( $strSessionID    ) . ", " .
                        "   last_access_time    =   sysdate() "                               . ", " .
                        "   guid                =   " . SQLAid::escapeStr( $strGuid         ) . ", " .
                        "   ua                  =   " . SQLAid::escapeStr( $strUA           ) . 
                        " WHERE                     " .
                        "   login_id            =   " . SQLAid::escapeStr( $strLoginID      ) ;
        }
        else
        {
            // INSERTする
            $strSQL =   " INSERT INTO session    " .
                        " (                      " .
                        "   login_id            ," .
                        "   session_id          ," .
                        "   last_access_time    ," .
                        "   guid                ," .
                        "   ua                   " .
                        " )                      " .
                        " VALUES                 " .
                        " (                      " .
                        "    " . SQLAid::escapeStr( $strLoginID         ) . ", " .
                        "    " . SQLAid::escapeStr( $strSessionID       ) . ", " .
                        "    sysdate()                                       , " .
                        "    " . SQLAid::escapeStr( $strGuid            ) . ", " .
                        "    " . SQLAid::escapeStr( $strUA              ) .
                        " )                      " ;
        }

        // SQL実行
        $objSession = $db->QueryDB( $strSQL );

        // 結果成功したらCOMMIT、失敗ならROLLBACKしてエラーメッセージ設定
        if( DB_OK == $objSession )
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
            $intRetCode = false;
        }

        // セッション更新成功なら、ユーザ情報の最終ログイン日付を更新
        if( true == $intRetCode )
        {
            $strErrorMsg = UserDB::UpdateUserLogin( $strLoginID );
            if( 0 < strlen( $strErrorMsg ) )
            {
                $intRetCode = 99;
            }
        }

        return $intRetCode;
    }

    // ***********************************************************************
    // * 関数名     ：ログアウト時のセッション削除
    // * 返り値     ：成功:true、失敗:false
    // ***********************************************************************
    function DeleteSession( $strSessionID   )   // セッションID
    {
        $intRetCode = true;

        // ＤＢ接続
        $db = new WrapDB();
        $db->ConnectDB();

        // 既にレコードが存在するかSELECT
        $strSelectSQL =     " SELECT                " .
                            "   login_id            " .
                            " FROM                  " .
                            "   session             " .
                            " WHERE 0=0             " .
                            " AND   session_id  =   " . SQLAid::escapeStr( $strSessionID ) ;

        // SQL実行
        $objSessionSelect = $db->QueryDB( $strSelectSQL );

        // 実行結果行数を取得
        $intSessionRows = $db->GetNumRowsDB( $objSessionSelect );

        //トランザクション開始
        $db->AutoCommitOffDB();

        // (存在すればDELETE)
        if( 0 < $intSessionRows )
        {
            $strSQL =   " DELETE FROM               " .
                        "   session                 " .
                        " WHERE                     " .
                        "   session_id          =   " . SQLAid::escapeStr( $strSessionID );

            // SQL実行
            $objSession = $db->QueryDB( $strSQL );

            // 結果成功したらCOMMIT、失敗ならROLLBACKしてエラーメッセージ設定
            if( DB_OK == $objSession )
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
                $intRetCode = false;
            }
        }
        else
        {
            // レコードが無ければ何もしない
            $db->CloseDB();
        }

        return $intRetCode;
    }

    // ***********************************************************************
    // * 関数名     ：認証OKの場合、最終アクセス日付を更新する
    // * 返り値     ：成功:true、失敗:false
    // ***********************************************************************
    function UpdateAccessDatetime( $strSessionID   )   // セッションID
    {
        $intRetCode = true;

        // ＤＢ接続
        $db = new WrapDB();
        $db->ConnectDB();

        // 既にレコードが存在するかSELECT
        $strSelectSQL =     " SELECT                " .
                            "   login_id            " .
                            " FROM                  " .
                            "   session             " .
                            " WHERE 0=0             " .
                            " AND   session_id  =   " . SQLAid::escapeStr( $strSessionID ) ;

        // SQL実行
        $objSessionSelect = $db->QueryDB( $strSelectSQL );

        // 実行結果行数を取得
        $intSessionRows = $db->GetNumRowsDB( $objSessionSelect );

        //トランザクション開始
        $db->AutoCommitOffDB();

        // (存在すればUPDATE)
        if( 0 < $intSessionRows )
        {
            $strSQL =   " UPDATE                    " .
                        "   session                 " .
                        " SET                       " .
                        "   last_access_time    =   sysdate() " .
                        " WHERE                     " .
                        "   session_id          =   " . SQLAid::escapeStr( $strSessionID );

            // SQL実行
            $objSession = $db->QueryDB( $strSQL );

            // 結果成功したらCOMMIT、失敗ならROLLBACKしてエラーメッセージ設定
            if( DB_OK == $objSession )
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
                $intRetCode = false;
            }
        }
        else
        {
            // レコードが無ければ何もしない
            $db->CloseDB();
        }

        return $intRetCode;
    }

    // ***********************************************************************
    // * 関数名     ：ユーザ情報内容ＤＢ更新処理(端末ID更新 or 削除)
    // * 返り値     ：成功:0、失敗:1、または9
    // ***********************************************************************
    function UpdateMobileID(    $strLoginID     ,   // ログインID
                                $intProcFlag    )   // 処理フラグ(0:端末ID更新、1:端末ID削除)
    {
        $intRetCode = 0;

        // 更新の場合
        if( 0 == $intProcFlag )
        {
            // ユーザエージェント取得(UA, OS, オリジナル)
            $arrayUA_and_OS = AccessLog::GetUAandOS();

            // ID取得判定
            // (取得できた場合)
            if( 0 < strlen( $arrayUA_and_OS[3] ) ||     // docomo
                0 < strlen( $arrayUA_and_OS[4] ) ||     // au
                0 < strlen( $arrayUA_and_OS[5] )   )    // softbank
            {
                if( 0 < strlen( $arrayUA_and_OS[3] ) )
                {
                    $strMobileID = $arrayUA_and_OS[3];
                }
                elseif( 0 < strlen( $arrayUA_and_OS[4] ) )
                {
                    $strMobileID = $arrayUA_and_OS[4];
                }
                elseif( 0 < strlen( $arrayUA_and_OS[5] ) )
                {
                    $strMobileID = $arrayUA_and_OS[5];
                }
                else
                {
                    $strMobileID = "";
                }
            }
            // (取得できなかった場合)
            else
            {
                // errorリターン
                $intRetCode = 9;
                return $intRetCode;
            }
        }
        // 削除の場合
        else
        {
            $strMobileID = "";
        }

        // ＤＢ接続
        $db = new WrapDB();
        $db->ConnectDB();

        //トランザクション開始
        $db->AutoCommitOffDB();

        // UPDATE文
        $strSQL =   " UPDATE                " .
                    "   user                " .
                    " SET                   " .
                    "   mobile_id       =   " . SQLAid::escapeStr( $strMobileID         ) .
                    " WHERE                 " .
                    "   login_id        =   " . SQLAid::escapeStr( $strLoginID          ) ;

        // SQL実行
        $objUser = $db->QueryDB( $strSQL );

        // 結果成功したらCOMMIT、失敗ならROLLBACKしてエラーメッセージ設定
        if( DB_OK == $objUser )
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
            $intRetCode = 1;
        }

        return $intRetCode;
    }

    // ***********************************************************************
    // * 関数名     ：ＢＢＳ投稿用アドレスからのユーザ検索
    // * 返り値     ：成功:SELECT結果、失敗:null
    // ***********************************************************************
    function GetUserOfBbsMail(  $strBBS_Mail    ,
                                &$intRows       )
    {
        // ＤＢ接続
        $db = new WrapDB();
        $db->ConnectDB();

        // SELECTする
        $strSQL =   " SELECT                " .
                    "   login_id            " .
                    " FROM                  " .
                    "   user                " .
                    " WHERE 0=0             " .
                    " AND   bbs_write_mailaddress like " . SQLAid::escapeLike( $strBBS_Mail ) .
                    " AND   del_flag    = 0 " ;

        // SQL実行
        $objUser = $db->QueryDB( $strSQL );

        // 実行結果行数を取得
        $intRows = $db->GetNumRowsDB( $objUser );

        // 取得したデータを配列に格納
        if( 0 < $intRows )
        {
            // 取得行を配列化
            $arrayResult = $db->FetchArrayDB( $objUser );

            $arrayUser = array( 'login_id'   =>  $arrayResult["login_id"] );
        }
        else
        {
            $arrayUser = null;
        }

        return $arrayUser;
    }

    // ***********************************************************************
    // * 関数名     ：指定メールアドレス存在チェック
    // * 返り値     ：成功:SELECT結果、失敗:null
    // ***********************************************************************
    function UsedMailCheck( $strPcMail      ,   // チェックするアドレス(PC)
                            $strMobileMail  ,   // チェックするアドレス(Mobile)
                            $arrayBbsMail   ,   // チェックするアドレス(投稿用)
                            &$intPcError    ,
                            &$intMobileError,
                            &$intBbsError   ,
                            $strUserID      )   // 変更対象ユーザは対象外
    {
        // ＤＢ接続
        $db = new WrapDB();
        $db->ConnectDB();

        $intPcError     = true;
        $intMobileError = true;
        $intBbsError    = true;

        $strMailAddressPcIn     = "";
        $strMailAddressMobileIn = "";
        $strMailAddressBbsIn    = "";

        // *********************
        // PCメール入力あり
        // *********************
        if( 0 < strlen( $strPcMail ) )
        {
            if( 0 < strlen( $strMailAddressPcIn ) )
            {
                $strMailAddressPcIn = $strMailAddressPcIn . " , ";
            }
            $strMailAddressPcIn = $strMailAddressPcIn . SQLAid::escapeStr( $strPcMail );

            // SELECTする
            $strSQL =   " (                 " .     // 既に他のユーザで使用されているかどうかチェック
                        " SELECT            " .
                        "   user_id as id   " .
                        " FROM              " .
                        "   user            " .
                        " WHERE 0=0                         " .
                        " AND   (    mail_address_pc       IN ( " . $strMailAddressPcIn . " ) " .
                        "         OR mail_address_mobile   IN ( " . $strMailAddressPcIn . " ) " .
                        "         OR bbs_write_mailaddress IN ( " . $strMailAddressPcIn . " ) " .
                        "       ) " ;

            // 指定ユーザIDがある場合＝ユーザ情報変更の場合
            if( 0 < strlen( $strUserID ) )
            {
                // 自分は対象外
                $strSQL = $strSQL .
                        " AND   user_id <>            " . SQLAid::escapeNum( intval( $strUserID )   ) ;
            }

            $strSQL =   $strSQL .
                        " )         " .
                        " UNION     " .
                        " (         " .
                        " SELECT                " .     // スレッドで使用されているアドレスかどうかチェック
                        "   thread_id as id     " .
                        " FROM                  " .
                        "   thread_master       " .
                        " WHERE 0=0             " .
                        " AND   thread_mailaddress IN ( " . $strMailAddressPcIn . " ) " .
                        " )         " ;

            // SQL実行
            $objInfo = $db->QueryDB( $strSQL );

            // 実行結果行数を取得
            $intRows = $db->GetNumRowsDB( $objInfo );
            if( 0 < $intRows )
            {
                $intPcError = false;
            }
            else
            {
                $intPcError = true;
            }
        }

        // モバイルメールあり
        if( 0 < strlen( $strMobileMail ) )
        {
            if( 0 < strlen( $strMailAddressMobileIn ) )
            {
                $strMailAddressMobileIn = $strMailAddressMobileIn . " , ";
            }
            $strMailAddressMobileIn = $strMailAddressMobileIn . SQLAid::escapeStr( $strMobileMail );

            // SELECTする
            $strSQL =   " (                 " .     // 既に他のユーザで使用されているかどうかチェック
                        " SELECT            " .
                        "   user_id as id   " .
                        " FROM              " .
                        "   user            " .
                        " WHERE 0=0                         " .
                        " AND   (    mail_address_pc       IN ( " . $strMailAddressMobileIn . " ) " .
                        "         OR mail_address_mobile   IN ( " . $strMailAddressMobileIn . " ) " .
                        "         OR bbs_write_mailaddress IN ( " . $strMailAddressMobileIn . " ) " .
                        "       ) " ;

            // 指定ユーザIDがある場合＝ユーザ情報変更の場合
            if( 0 < strlen( $strUserID ) )
            {
                // 自分は対象外
                $strSQL = $strSQL .
                        " AND   user_id <>            " . SQLAid::escapeNum( intval( $strUserID )   ) ;
            }

            $strSQL =   $strSQL .
                        " )         " .
                        " UNION     " .
                        " (         " .
                        " SELECT                " .     // スレッドで使用されているアドレスかどうかチェック
                        "   thread_id as id     " .
                        " FROM                  " .
                        "   thread_master       " .
                        " WHERE 0=0             " .
                        " AND   thread_mailaddress IN ( " . $strMailAddressMobileIn . " ) " .
                        " )         " ;

            // SQL実行
            $objInfo = $db->QueryDB( $strSQL );

            // 実行結果行数を取得
            $intRows = $db->GetNumRowsDB( $objInfo );
            if( 0 < $intRows )
            {
                $intMobileError = false;
            }
            else
            {
                $intMobileError = true;
            }
        }

        // 投稿用メールあり
        if( 0 < count( $arrayBbsMail ) )
        {
            if( true != is_array( $arrayBbsMail ) )
            {
                if( 0 < strlen( $strMailAddressBbsIn ) )
                {
                    $strMailAddressBbsIn = $strMailAddressBbsIn . " , ";
                }
                $strMailAddressBbsIn = $strMailAddressBbsIn . SQLAid::escapeStr( $arrayBbsMail );
            }
            else
            {
                // 配列の場合
                for( $intBbsCount = 0; $intBbsCount < count( $arrayBbsMail ); $intBbsCount++ )
                {
                    if( 0 < strlen( $strMailAddressBbsIn ) )
                    {
                        $strMailAddressBbsIn = $strMailAddressBbsIn . " , ";
                    }
                    $strMailAddressBbsIn = $strMailAddressBbsIn . SQLAid::escapeStr( $arrayBbsMail[ $intBbsCount ] );
                }
            }

            // SELECTする
            $strSQL =   " (                 " .     // 既に他のユーザで使用されているかどうかチェック
                        " SELECT            " .
                        "   user_id as id   " .
                        " FROM              " .
                        "   user            " .
                        " WHERE 0=0                         " .
                        " AND   (    mail_address_pc       IN ( " . $strMailAddressBbsIn . " ) " .
                        "         OR mail_address_mobile   IN ( " . $strMailAddressBbsIn . " ) " .
                        "         OR bbs_write_mailaddress IN ( " . $strMailAddressBbsIn . " ) " .
                        "       ) " ;

            // 指定ユーザIDがある場合＝ユーザ情報変更の場合
            if( 0 < strlen( $strUserID ) )
            {
                // 自分は対象外
                $strSQL = $strSQL .
                        " AND   user_id <>            " . SQLAid::escapeNum( intval( $strUserID )   ) ;
            }

            $strSQL =   $strSQL .
                        " )         " .
                        " UNION     " .
                        " (         " .
                        " SELECT                " .     // スレッドで使用されているアドレスかどうかチェック
                        "   thread_id as id     " .
                        " FROM                  " .
                        "   thread_master       " .
                        " WHERE 0=0             " .
                        " AND   thread_mailaddress IN ( " . $strMailAddressBbsIn . " ) " .
                        " )         " ;

            // SQL実行
            $objInfo = $db->QueryDB( $strSQL );

            // 実行結果行数を取得
            $intRows = $db->GetNumRowsDB( $objInfo );
            if( 0 < $intRows )
            {
                $intBbsError = false;
            }
            else
            {
                $intBbsError = true;
            }
        }
    }

}
