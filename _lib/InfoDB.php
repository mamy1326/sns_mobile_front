<?php
/* ================================================================================
 * ファイル名   ：InfoDB.php
 * タイトル     ：管理本部通信情報取得・更新用クラス
 * 作成者       ：間宮 直樹
 * 作成日       ：2008/08/17
 * 更新履歴*******************************************************************
 * 【変更日】  【変更者】      【変更箇所】        【変更理由と変更内容】
 *  2008/08/17  間宮 直樹       全体                新規作成
 * ================================================================================*/

// includeするライブラリ・コンフィグファイルのパスを定義する
define( 'LIB_DIR'   , '../_lib/');
define( 'CONFIG_DIR', '../_config/');

// ライブラリ・コンフィグファイルをinclude
include_once( LIB_DIR    . 'DataConvert.php' );         // データ変換用関数群
include_once( LIB_DIR    . 'WrapDB.php' );              // ＤＢ用関数群
include_once( LIB_DIR    . 'SQLAid.php' );              // ＳＱＬ条件文関数群
include_once( LIB_DIR    . 'AccessLog.php' );           // アクセスログ関数群
include_once( LIB_DIR    . 'PhotoDB.php' );             // フォトアルバム画像関数群
include_once( LIB_DIR    . 'UserDB.php' );              // ユーザ情報取得・更新関数群
include_once( CONFIG_DIR . 'db_conf.php' );             // データベース定義ファイル
include_once( CONFIG_DIR . 'manage_conf.php' );         // 管理画面基本情報定義ファイル

class InfoDB
{
    // ***********************************************************************
    // * 関数名     ：管理本部通信情報内容ＤＢ登録処理
    // * 機能概要   ：管理本部通信情報をＤＢに登録する
    // * 返り値     ：送信成功:true、送信失敗:false
    // ***********************************************************************
    function InsertInfoData(    $strOpenYY      ,   // 公開年
                                $strOpenMM      ,   // 公開月
                                $strOpenDD      ,   // 公開日
                                $strInfoTitle   ,   // タイトル
                                $strInfoBody    ,   // 本文（画像タグ変換前）
                                $strDelFlag     ,   // 削除フラグ
                                $arrayUserGroup )   // 選択されたユーザグループ
    {
        $strErrorMessage = "";

        // ログインユーザ名取得
        $strUserID = $_COOKIE[ md5( COOKIE_VIX_LOGIN_ID ) ];

        // ＤＢ接続
        $db = new WrapDB();
        $db->ConnectDB();

        //トランザクション開始
        $db->AutoCommitOffDB();

        // INSERTする
        $strSQL =   " INSERT INTO information" .
                    " (                      " .
                    "   info_date_yyyy      ," .
                    "   info_date_mm        ," .
                    "   info_date_dd        ," .
                    "   info_title          ," .
                    "   info_body           ," .
                    "   create_date         ," .
                    "   create_user         ," .
                    "   del_flag             " .
                    " )                      " .
                    " VALUES                 " .
                    " (                      " .
                    "    " . SQLAid::escapeStr( $strOpenYY      ) . ", " .
                    "    " . SQLAid::escapeStr( $strOpenMM      ) . ", " .
                    "    " . SQLAid::escapeStr( $strOpenDD      ) . ", " .
                    "    " . SQLAid::escapeStr( $strInfoTitle   ) . ", " .
                    "    " . SQLAid::escapeStr( $strInfoBody    ) . ", " .
                    "    sysdate()                                   , " .
                    "    " . SQLAid::escapeStr( $strUserID      ) . ", " .
                    "    " . SQLAid::escapeNum( $strDelFlag     ) .
                    ")                   " ;

        // SQL実行
        $objInfo = $db->QueryDB( $strSQL );

        // 結果成功したらCOMMIT、失敗ならROLLBACKしてエラーメッセージ設定
        if( DB_OK == $objInfo )
        {
            //成功
            $db->TransactDB( db_strDB_COMMIT );

            // ****************************
            // グループ情報の挿入
            // ****************************
            if( 0 < count( $arrayUserGroup ) )
            {
                // 今登録したグループのID取得
                $strSelectSQL = " SELECT MAX( info_id ) as info_id FROM information ";
                $objInfo = $db->QueryDB( $strSelectSQL );
                $arrayResult = $db->FetchArrayDB( $objInfo );
                $intInfoID = intval( $arrayResult["info_id"] );

                // 選択されたユーザ数分、VALUE句を作成する
                $strQueryValue = "";
                for( $intCount = 0; $intCount < count( $arrayUserGroup ); $intCount++ )
                {
                    if( 0 < strlen( $strQueryValue ) )
                    {
                        $strQueryValue = $strQueryValue . ", ";
                    }

                    $strQueryValue =    $strQueryValue .
                                        " ( " .
                                        SQLAid::escapeNum( $intInfoID ) . ", " .
                                        SQLAid::escapeNum( intval( $arrayUserGroup[$intCount] ) ) . ", " .
                                        " 1 " .
                                        " ) " ;
                }

                // INSERTする
                $strSQL =   " INSERT INTO information_user  " .
                            " (                             " .
                            "   info_id                    ," .
                            "   user_or_group_id           ," .
                            "   flag                        " .
                            " )                             " .
                            " VALUES                        " .
                            $strQueryValue;

                // SQL実行
                $objGroup = $db->QueryDB( $strSQL );

                // 失敗したらROLLBACKしてエラーリターン
                if( DB_OK != $objGroup )
                {
                    $strErrorMessage = "ユーザグループ情報登録エラー";
                }
            }
        }
        else
        {
            //失敗
            $strErrorMessage = TITLE_INFORMATION . "情報登録エラー";
        }

        // 結果成功したらCOMMIT、失敗ならROLLBACKしてエラーメッセージ設定
        if( 0 == strlen( $strErrorMessage ) )
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
            $strErrorMessage = TITLE_INFORMATION . "情報登録エラー";
        }

        return $strErrorMessage;
    }

    // ***********************************************************************
    // * 関数名     ：管理本部通信情報内容ＤＢ更新処理
    // * 機能概要   ：管理本部通信情報内容をＤＢ更新する
    // * 返り値     ：成功:true、失敗:false
    // ***********************************************************************
    function UpdateInfoData(    $strInfoID      ,   // ID
                                $strOpenYY      ,   // 公開年
                                $strOpenMM      ,   // 公開月
                                $strOpenDD      ,   // 公開日
                                $strInfoTitle   ,   // タイトル
                                $strInfoBody    ,   // 本文（画像タグ変換前）
                                $strDelFlag     ,   // 削除フラグ
                                $arrayUserGroup )   // 選択されたユーザグループ
    {
        $strErrorMessage = "";

        // ＤＢ接続
        $db = new WrapDB();
        $db->ConnectDB();

        //トランザクション開始
        $db->AutoCommitOffDB();

        // UPDATE文
        $strSQL =   " UPDATE                    " .
                    "   information             " .
                    " SET                       " .
                    "   info_date_yyyy      =   " . SQLAid::escapeStr( $strOpenYY               ) . ", " .
                    "   info_date_mm        =   " . SQLAid::escapeStr( $strOpenMM               ) . ", " .
                    "   info_date_dd        =   " . SQLAid::escapeStr( $strOpenDD               ) . ", " .
                    "   info_title          =   " . SQLAid::escapeStr( $strInfoTitle            ) . ", " .
                    "   info_body           =   " . SQLAid::escapeStr( $strInfoBody             ) . ", " .
                    "   del_flag            =   " . SQLAid::escapeNum( intval( $strDelFlag )    ) . ", " .
                    "   update_date         =   sysdate() " .
                    " WHERE                     " .
                    "   info_id             =   " . SQLAid::escapeNum( intval( $strInfoID )     ) ;

        // SQL実行
        $objInfo = $db->QueryDB( $strSQL );

        // 結果成功したらCOMMIT、失敗ならROLLBACKしてエラーメッセージ設定
        if( DB_OK == $objInfo )
        {
            //成功
            $db->TransactDB( db_strDB_COMMIT );

            // ****************************
            // グループ情報の挿入
            // ****************************
            if( 0 < count( $arrayUserGroup ) )
            {
                // グループを一端すべて削除する
                // DELETE文
                $strSQL =   " DELETE FROM               " .
                            "   information_user        " .
                            " WHERE                     " .
                            "   info_id             =   " . SQLAid::escapeNum( intval( $strInfoID ) ) ;

                // SQL実行
                $objGroupDel = $db->QueryDB( $strSQL );

                // 結果成功したらCOMMIT、失敗ならROLLBACKしてエラーメッセージ設定
                if( DB_OK == $objGroupDel )
                {
                    // 選択されたユーザ数分、VALUE句を作成する
                    $strQueryValue = "";
                    for( $intCount = 0; $intCount < count( $arrayUserGroup ); $intCount++ )
                    {
                        if( 0 < strlen( $strQueryValue ) )
                        {
                            $strQueryValue = $strQueryValue . ", ";
                        }

                        $strQueryValue =    $strQueryValue .
                                            " ( " .
                                            SQLAid::escapeNum( intval( $strInfoID ) ) . ", " .
                                            SQLAid::escapeNum( intval( $arrayUserGroup[$intCount] ) ) . ", " .
                                            " 1 " .
                                            " ) " ;
                    }

                    // INSERTする
                    $strSQL =   " INSERT INTO information_user  " .
                                " (                             " .
                                "   info_id                    ," .
                                "   user_or_group_id           ," .
                                "   flag                        " .
                                " )                             " .
                                " VALUES                        " .
                                $strQueryValue;

                    // SQL実行
                    $objGroup = $db->QueryDB( $strSQL );

                    // 失敗したらROLLBACKしてエラーリターン
                    if( DB_OK != $objGroup )
                    {
                        $strErrorMessage = "ユーザグループ情報登録エラー";
                    }
                }
            }
        }
        else
        {
            //失敗
            $strErrorMessage = TITLE_INFORMATION . "情報更新エラー";
        }

        // 結果成功したらCOMMIT、失敗ならROLLBACKしてエラーメッセージ設定
        if( 0 == strlen( $strErrorMessage ) )
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
            $strErrorMessage = TITLE_INFORMATION . "情報更新エラー";
        }

        return $strErrorMessage;
    }

    // ***********************************************************************
    // * 関数名     ：管理本部通信情報取得処理
    // * 機能概要   ：管理本部通信情報をすべて取得する
    // * 返り値     ：成功:SELECT結果、失敗:null
    // ***********************************************************************
    function GetInfoAll(    &$intRows       ,   // 取得できたレコード数
                            $intDelFlag = 0 )   // 取得する情報フラグ(0:すべて、1:公開しているもののみ、2:すべてかつ公開しているグループ取得)
    {
        // ＤＢ接続
        $db = new WrapDB();
        $db->ConnectDB();

        // SELECTする
        // 公開のみの場合
        if( 0 == $intDelFlag )
        {
            $strSQL =   " SELECT                 " .
                        "   info_id             ," .
                        "   info_date_yyyy      ," .
                        "   info_date_mm        ," .
                        "   info_date_dd        ," .
                        "   info_title          ," .
                        "   info_interval_from  ," .
                        "   info_interval_to    ," .
                        "   del_flag             " .
                        " FROM                   " .
                        "   information          " .
                        " ORDER BY               " .
                        "   info_date_yyyy DESC ," .
                        "   info_date_mm   DESC ," .
                        "   info_date_dd   DESC ," .
                        "   info_id        DESC  " ;
        }
        elseif( 1 == $intDelFlag )
        {
            // セッションIDからユーザIDを求める
            $arrayUser =    UserDB::GetLoginUserOfSession(  $_GET["s_id"]   ,
                                                            &$intRows       );

            $strSQL =   " SELECT                 " .
                        "   info.info_id            as  info_id             ," .
                        "   info.info_date_yyyy     as  info_date_yyyy      ," .
                        "   info.info_date_mm       as  info_date_mm        ," .
                        "   info.info_date_dd       as  info_date_dd        ," .
                        "   info.info_title         as  info_title          ," .
                        "   info.info_interval_from as  info_interval_from  ," .
                        "   info.info_interval_to   as  info_interval_to    ," .
                        "   info.del_flag           as  del_flag             " .
                        " FROM                   " .
                        "   information  info    " .
                        "   INNER JOIN information_user user    " .
                        "       ON info.info_id = user.info_id  " .
                        "   INNER JOIN group_users u_group      " .
                        "       ON  user.user_or_group_id = u_group.group_id " .
                        "       AND u_group.user_id = " . SQLAid::escapeNum( intval( $arrayUser["user_id"] ) ) .
                        " WHERE                  " .
                        "   del_flag <> 2        " .
                        " GROUP BY info.info_id  " .
                        " ORDER BY               " .
                        "   info_date_yyyy DESC ," .
                        "   info_date_mm   DESC ," .
                        "   info_date_dd   DESC ," .
                        "   info_id        DESC  " ;
        }
        else
        {
            $strSQL =   " SELECT                 " .
                        "   info.info_id            as  info_id             ," .
                        "   info.info_date_yyyy     as  info_date_yyyy      ," .
                        "   info.info_date_mm       as  info_date_mm        ," .
                        "   info.info_date_dd       as  info_date_dd        ," .
                        "   info.info_title         as  info_title          ," .
                        "   info.info_interval_from as  info_interval_from  ," .
                        "   info.info_interval_to   as  info_interval_to    ," .
                        "   info.del_flag           as  del_flag            ," .
                        "   u_group.group_id        as  group_id            ," .
                        "   u_group.group_name      as  group_name           " .
                        " FROM                   " .
                        "               information         info        " .
                        "   LEFT  JOIN  information_user    info_group  " .
                        "       ON info.info_id = info_group.info_id    " .
                        "   LEFT  JOIN  user_group          u_group     " .
                        "       ON info_group.user_or_group_id = u_group.group_id " .
                        " ORDER BY               " .
                        "   info_date_yyyy DESC ," .
                        "   info_date_mm   DESC ," .
                        "   info_date_dd   DESC ," .
                        "   info_id        DESC  " ;
        }

        // SQL実行
        $objInfo = $db->QueryDB( $strSQL );

        // 実行結果行数を取得
        $intRows = $db->GetNumRowsDB( $objInfo );

        // 取得行を配列化
        $arrayInfo = array();
        for( $intCount = 0; $intCount < $intRows; $intCount++ )
        {
            $arrayResult = $db->FetchArrayDB( $objInfo );

            // 取得したデータを配列に格納
            $arrayInfo[] = array(   'info_id'           => $arrayResult["info_id"]              ,
                                    'info_date_yyyy'    => $arrayResult["info_date_yyyy"]       ,
                                    'info_date_mm'      => $arrayResult["info_date_mm"]         ,
                                    'info_date_dd'      => $arrayResult["info_date_dd"]         ,
                                    'info_title'        => $arrayResult["info_title"]           ,
                                    'info_interval_from'=> $arrayResult["info_interval_from"]   ,
                                    'info_interval_to'  => $arrayResult["info_interval_to"]     ,
                                    'del_flag'          => $arrayResult["del_flag"]             ,
                                    'group_id'          => $arrayResult["group_id"]             ,
                                    'group_name'        => $arrayResult["group_name"]           );
        }

        return $arrayInfo;
    }

    // ***********************************************************************
    // * 関数名     ：管理本部通信情報取得処理(１件のみ)
    // * 返り値     ：成功:SELECT結果、失敗:null
    // ***********************************************************************
    function GetInfoOne(    $intInfoID      )
    {
        // ＤＢ接続
        $db = new WrapDB();
        $db->ConnectDB();

        // SELECTする
        $strSQL =   " SELECT                 " .
                    "   info_id             ," .
                    "   info_date_yyyy      ," .
                    "   info_date_mm        ," .
                    "   info_date_dd        ," .
                    "   info_title          ," .
                    "   info_body           ," .
                    "   info_interval_from  ," .
                    "   info_interval_to    ," .
                    "   create_date         ," .
                    "   del_flag             " .
                    " FROM                   " .
                    "   information          " .
                    " WHERE 0=0              " .
                    " AND   info_id   =      " . SQLAid::escapeNum( $intInfoID );
        // SQL実行
        $objInfo = $db->QueryDB( $strSQL );

        // 実行結果行数を取得
        $intRows = $db->GetNumRowsDB( $objInfo );

        // 取得したデータを配列に格納
        if( 0 < $intRows )
        {
            // 取得行を配列化
            $arrayResult = $db->FetchArrayDB( $objInfo );

            $arrayInfo   = array(   'info_id'           => $arrayResult["info_id"]              ,
                                    'info_date_yyyy'    => $arrayResult["info_date_yyyy"]       ,
                                    'info_date_mm'      => $arrayResult["info_date_mm"]         ,
                                    'info_date_dd'      => $arrayResult["info_date_dd"]         ,
                                    'info_title'        => $arrayResult["info_title"]           ,
                                    'info_body'         => $arrayResult["info_body"]            ,
                                    'info_interval_from'=> $arrayResult["info_interval_from"]   ,
                                    'info_interval_to'  => $arrayResult["info_interval_to"]     ,
                                    'create_date'       => $arrayResult["create_date"]          ,
                                    'del_flag'          => $arrayResult["del_flag"]             );
        }
        else
        {
            $arrayInfo = null;
        }

        return $arrayInfo;
    }

    // ***********************************************************************
    // * 関数名     ：管理本部通信情報取得処理(１件のみ)
    // * 返り値     ：成功:SELECT結果、失敗:null
    // ***********************************************************************
    function GetInfoOneANdGroup(    $intInfoID  ,
                                    &$intRows   )
    {
        // ＤＢ接続
        $db = new WrapDB();
        $db->ConnectDB();

        // SELECTする
        $strSQL =   " SELECT                                             " .
                    "   info.info_id            as  info_id             ," .
                    "   info.info_date_yyyy     as  info_date_yyyy      ," .
                    "   info.info_date_mm       as  info_date_mm        ," .
                    "   info.info_date_dd       as  info_date_dd        ," .
                    "   info.info_title         as  info_title          ," .
                    "   info.info_body          as  info_body           ," .
                    "   info.info_interval_from as  info_interval_from  ," .
                    "   info.info_interval_to   as  info_interval_to    ," .
                    "   info.create_date        as  create_date         ," .
                    "   info.del_flag           as  del_flag            ," .
                    "   u_group.group_id        as  group_id            ," .
                    "   u_group.group_name      as  group_name          ," .
                    "   u_group.group_note      as  group_note          ," .
                    "   u_group.create_date     as  group_create_date    " .
                    " FROM                   " .
                    "   information   info   " .
                    "   LEFT  JOIN information_user  user               " .
                    "       ON  user.info_id = info.info_id             " .
                    "   LEFT  JOIN user_group  u_group                  " .
                    "       ON  user.user_or_group_id = u_group.group_id" .
                    "       AND u_group.del_flag = 0                    " .
                    " WHERE 0=0              " .
                    " AND   info.info_id   = " . SQLAid::escapeNum( $intInfoID );
        // SQL実行
        $objInfo = $db->QueryDB( $strSQL );

        // 実行結果行数を取得
        $intRows = $db->GetNumRowsDB( $objInfo );

        // 取得したデータを配列に格納
        $arrayInfo = array();
        for( $intCount = 0; $intCount < $intRows; $intCount++ )
        {
            // 取得行を配列化
            $arrayResult = $db->FetchArrayDB( $objInfo );

            $arrayInfo[] = array(   'info_id'           => $arrayResult["info_id"]              ,
                                    'info_date_yyyy'    => $arrayResult["info_date_yyyy"]       ,
                                    'info_date_mm'      => $arrayResult["info_date_mm"]         ,
                                    'info_date_dd'      => $arrayResult["info_date_dd"]         ,
                                    'info_title'        => $arrayResult["info_title"]           ,
                                    'info_body'         => $arrayResult["info_body"]            ,
                                    'info_interval_from'=> $arrayResult["info_interval_from"]   ,
                                    'info_interval_to'  => $arrayResult["info_interval_to"]     ,
                                    'create_date'       => $arrayResult["create_date"]          ,
                                    'del_flag'          => $arrayResult["del_flag"]             ,
                                    'group_id'          => $arrayResult["group_id"]             ,
                                    'group_name'        => $arrayResult["group_name"]           ,
                                    'group_note'        => $arrayResult["group_note"]           ,
                                    'group_create_date' => $arrayResult["group_create_date"]    );
        }

        return $arrayInfo;
    }

    // ***********************************************************************
    // * 関数名     ：管理本部通信情報取得処理（主にトップページ用）
    // * 機能概要   ：指定されたレコード数取得する
    // * 返り値     ：成功:SELECT結果、失敗:null
    // ***********************************************************************
    function GetInfoHome(   $intStartRecordNum  ,   // 取得開始レコード
                            $intGetRecordCount  ,   // 取得するレコード数
                            &$intRows           ,   // 取得できたレコード数
                            $intFlag = 0        )   // 0:Limitあり、1:Limitなし
    {
        // セッションIDからユーザIDを求める
        $arrayUser =    UserDB::GetLoginUserOfSession(  $_GET["s_id"]   ,
                                                        &$intRows       );

        // ＤＢ接続
        $db = new WrapDB();
        $db->ConnectDB();

        // SELECTする
        $strSQL =   " ( " .
                    " SELECT                 " .
                    "   info.info_id            as  info_id           ," .
                    "   info.info_date_yyyy     as  info_date_yyyy    ," .
                    "   info.info_date_mm       as  info_date_mm      ," .
                    "   info.info_date_dd       as  info_date_dd      ," .
                    "   info.info_title         as  info_title        ," .
                    "   info.info_body          as  info_body         ," .
                    "   info.info_interval_from as  info_interval_from," .
                    "   info.info_interval_to   as  info_interval_to  ," .
                    "   info.create_date        as  create_date       ," .
                    "   info.del_flag           as  del_flag           " .
                    " FROM                      " .
                    "   information   info      " .
                    " WHERE 0=0                 " .
                    " AND   info.del_flag   = 0 " .     // 全体公開
                    " GROUP BY info.info_id     " .
                    " ) " .
                    " UNION " .
                    " ( " .
                    " SELECT                                           " .      // 公開レベル設定されている場合の検索結果をUNIONする
                    "   info.info_id            as  info_id           ," .
                    "   info.info_date_yyyy     as  info_date_yyyy    ," .
                    "   info.info_date_mm       as  info_date_mm      ," .
                    "   info.info_date_dd       as  info_date_dd      ," .
                    "   info.info_title         as  info_title        ," .
                    "   info.info_body          as  info_body         ," .
                    "   info.info_interval_from as  info_interval_from," .
                    "   info.info_interval_to   as  info_interval_to  ," .
                    "   info.create_date        as  create_date       ," .
                    "   info.del_flag           as  del_flag           " .
                    " FROM                   " .
                    "   information   info   " .
                    "   INNER JOIN information_user user        " .
                    "       ON info.info_id = user.info_id      " .
                    "   INNER JOIN group_users u_group          " .
                    "       ON  user.user_or_group_id = u_group.group_id " .
                    "       AND u_group.user_id = " . SQLAid::escapeNum( intval( $arrayUser["user_id"] ) ) .
                    " WHERE 0=0                 " .
                    " AND   info.del_flag   = 1 " .     // 公開レベル設定
                    " GROUP BY info.info_id     " .
                    " ) " .
                    " ORDER BY                  " .
                    "   info_date_yyyy DESC ," .
                    "   info_date_mm   DESC ," .
                    "   info_date_dd   DESC  " ;

                    // LIMITあり
                    if( 0 == $intFlag )
                    {
                        $strSQL =   $strSQL .
                                    " LIMIT " . SQLAid::escapeNum( $intStartRecordNum ) . " , " . SQLAid::escapeNum( $intGetRecordCount );
                    }

        // SQL実行
        $objInfo = $db->QueryDB( $strSQL );

        // 実行結果行数を取得
        $intRows = $db->GetNumRowsDB( $objInfo );

        // 取得行を配列化
        $arrayInfo = array();
        for( $intCount = 0; $intCount < $intRows; $intCount++ )
        {
            $arrayResult = $db->FetchArrayDB( $objInfo );

            // 取得したデータを配列に格納
            $arrayInfo[] = array(   'info_id'           => $arrayResult["info_id"]              ,
                                    'info_date_yyyy'    => $arrayResult["info_date_yyyy"]       ,
                                    'info_date_mm'      => $arrayResult["info_date_mm"]         ,
                                    'info_date_dd'      => $arrayResult["info_date_dd"]         ,
                                    'info_title'        => $arrayResult["info_title"]           ,
                                    'info_body'         => $arrayResult["info_body"]            ,
                                    'info_interval_from'=> $arrayResult["info_interval_from"]   ,
                                    'info_interval_to'  => $arrayResult["info_interval_to"]     ,
                                    'create_date'       => $arrayResult["create_date"]          ,
                                    'del_flag'          => $arrayResult["del_flag"]             );
        }

        return $arrayInfo;
    }

    // ***********************************************************************
    // * 関数名     ：管理本部通信の画像タグをAタグに変換する
    // * 機能概要   ：管理本部通信に登録された画像タグ部分を画像表示用Aタグに変換する
    // * 返り値     ：成功:SELECT結果、失敗:null
    // ***********************************************************************
    function ConvertInfoImage(  $strInfoBody            ,   // 変換する本文（DB取得そのままの情報）
                                $intCarriorFlag = 1     ,   // キャリアフラグ(0:PC、1:携帯)
                                $strImageFlag   = "0"   )   // モバイルの場合の画像表示フラグ(0:リンク表示、1:最小サイズ画像表示)
    {
        $strBodyWork = $strInfoBody;

        // ***************************************************
        // 文章→画像→文章→画像・・・という風に配列化する
        // ***************************************************

        // 画像がなくなるまでループ
        $intImagePosition = 0;
        $arrayBody = array();
        while( true )
        {
            // 画像の位置を取得する("<img src='"を検索)
            $intImagePositionNow = strpos( $strBodyWork, INFO_IMAGE_TAG_START, $intImagePosition );

            // 画像が見つからなくなったらループを抜ける
            if( false === $intImagePositionNow )
            {
                // そこまでのテキストを本文の最後までとして取得して配列にプラスする
                $arrayBody[] = array(   "type"  =>  "text"                                      ,
                                        "body"  =>  DataConvert::MetatagEncode( substr( $strBodyWork, $intImagePosition ) )  ,
                                        "image" =>  ""                                          );
                break;
            }

            // ******************************
            // 画像の手前のテキストを配列化
            // ******************************

            // テキストが始まる場所を取得
            $intTextStart   = $intImagePosition;

            // テキストの長さを取得
            $intTextLen     = $intImagePositionNow - $intTextStart;

            // テキスト取得
            $strText        = substr( $strBodyWork, $intTextStart, $intTextLen );

            // テキストが存在すれば配列に追加
            if( 0 < strlen( $strText ) )
            {
                $arrayBody[] = array(   "type"  =>  "text"      ,
                                        "body"  =>  DataConvert::MetatagEncode( $strText ) );
            }

            // *****************
            // 画像情報を配列化
            // *****************

            // 画像名が始まる場所を取得
            $intImageNameStart  = $intImagePositionNow + strlen( INFO_IMAGE_TAG_START );

            // 画像名の長さを取得
            $intImageNameLen    = strpos( $strBodyWork, "'", $intImageNameStart ) - $intImageNameStart;

            // 画像名を取得
            $strImageName       = substr( $strBodyWork, $intImageNameStart, $intImageNameLen );
//print($intImageNameStart . "から" . $intImageNameLen . "文字【" . $strImageName . "】<br>");

            // 携帯なのでサムネイル名を取得
            $arrayImageNameSumb = PhotoDB::GetImageSumbnail( $strImageName );

            if( 0 < count( $arrayImageNameSumb ) )
            {
                // モバイルの場合
                if( 1 == $intCarriorFlag )
                {
                    // リンク表示指定の場合
                    if( 0 == strcmp( "0", $strImageFlag ) )
                    {
                        // 画像のリンクタグを作成
                        $strImageLink       = "<a href='" . PATH_IMAGE_PHOTOALBUM . "/" . $arrayImageNameSumb["sumb"] . "'>[ 画像を表示する ]</a>¥n";
                    }
                    // 最小サイズ画像表示の場合
                    else
                    {
                        // 画像のリンク＆最小サイズ画像タグを作成
                        $strImageLink       = "<a href='" . PATH_IMAGE_PHOTOALBUM . "/" . $arrayImageNameSumb["sumb"] . "'><img src='" . PATH_IMAGE_PHOTOALBUM . "/" . $arrayImageNameSumb["sumb_ss"] . "' /></a>¥n";
                    }
                }
                // PCの場合
                else
                {
                    // 画像表示タグを作成
                    $strImageLink       = "<img src='" . PATH_IMAGE_PHOTOALBUM . "/" . $arrayImageNameSumb["sumb"] . "' />¥n";
                }

                // 配列作成
                $arrayBody[] = array(   "type"  =>  "image"         ,
                                        "body"  =>  $strImageLink   );
            }

            // **********************************************
            // 画像タグの終わりを検出し、次の開始位置を定義
            // **********************************************

            // 画像タグの終わりを検出
            $intImageTagEnd = strpos( $strBodyWork, ">", strpos( $strBodyWork, "'", $intImageNameStart ) + 1 ) + 1;

            // 次の位置を指定(最初の位置＋画像閉じタグ">"の位置)
            $intImagePosition = $intImageTagEnd;
        }

        // 配列化したものを連結
        $strInfoBodyTag = "";
        for( $intCount = 0; $intCount < count( $arrayBody ); $intCount++ )
        {
            // 文字列がある場合は連結
            if( 0 < strlen( $arrayBody[$intCount]["body"] ) )
            {
                $strInfoBodyTag = $strInfoBodyTag . $arrayBody[$intCount]["body"];
            }
        }
        return DataConvert::ReturnCodeToBR( $strInfoBodyTag );
    }


}
