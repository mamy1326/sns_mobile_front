<?php
/* ================================================================================
 * ファイル名   ：UserGroupDB.php
 * タイトル     ：ユーザグループ情報取得・更新用クラス
 * 作成者       ：間宮 直樹
 * 作成日       ：2008/11/25
 * 内容         ：ユーザグループ情報の取得・更新処理を実施する。
 * 更新履歴*******************************************************************
 * 【変更日】  【変更者】      【変更箇所】        【変更理由と変更内容】
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
include_once( LIB_DIR    . 'UserDB.php' );              // ユーザ情報取得・更新関数群
include_once( LIB_DIR    . 'AccessLog.php' );           // アクセスログ関数群
include_once( CONFIG_DIR . 'db_conf.php' );             // データベース定義ファイル
include_once( CONFIG_DIR . 'manage_conf.php' );         // 管理画面基本情報定義ファイル

class UserGroupDB
{
    // ***********************************************************************
    // * 関数名     ：ユーザ情報内容ＤＢ登録処理
    // * 機能概要   ：ユーザ情報をＤＢに登録する
    // * 返り値     ：送信成功:true、送信失敗:false
    // ***********************************************************************
    function InsertUserGroupData(   $strGroupName       ,   // ユーザグループ名
                                    $strGroupNotes      ,   // 説明
                                    $arrayUserID        ,   // 選択されたユーザID（配列）
                                    $strDelFlag         )   // 削除フラグ
    {
        $strErrorMessage = "";

        // ＤＢ接続
        $db = new WrapDB();
        $db->ConnectDB();

        //トランザクション開始
        $db->AutoCommitOffDB();

        // ****************************
        // ユーザグループ情報の挿入
        // ****************************

        // INSERTする
        $strSQL =   " INSERT INTO user_group " .
                    " (                      " .
                    "   group_name          ," .
                    "   group_note          ," .
                    "   create_date         ," .
                    "   del_flag             " .
                    " )                      " .
                    " VALUES                 " .
                    " (                      " .
                    "    " . SQLAid::escapeStr( $strGroupName       ) . ", " .
                    "    " . SQLAid::escapeStr( $strGroupNotes      ) . ", " .
                    "    sysdate()                                       , " .
                    "    " . SQLAid::escapeNum( $strDelFlag         ) .
                    ")                   " ;

        // SQL実行
        $objGroup = $db->QueryDB( $strSQL );

        // 失敗したらROLLBACKしてエラーリターン
        if( DB_OK != $objGroup )
        {
            //失敗
            $db->TransactDB( db_strDB_ROLLBACK );
            $db->CloseDB();
            $strErrorMessage = "ユーザグループ情報登録エラー";
            return $strErrorMessage;
        }
        else
        {
            // グループID取得のためにCOMMITする
            $db->TransactDB( db_strDB_COMMIT );
        }

        // ****************************
        // ユーザ情報の挿入
        // ****************************
        if( 0 < count( $arrayUserID ) )
        {
            // 今登録したグループのID取得
            $strSelectSQL = " SELECT MAX( group_id ) as group_id FROM user_group ";

            $objGroup = $db->QueryDB( $strSelectSQL );

            $arrayResult = $db->FetchArrayDB( $objGroup );

            $intGroupID = intval( $arrayResult["group_id"] );

            // 選択されたユーザ数分、VALUE句を作成する
            $strQueryValue = "";
            for( $intCount = 0; $intCount < count( $arrayUserID ); $intCount++ )
            {
                if( 0 < strlen( $strQueryValue ) )
                {
                    $strQueryValue = $strQueryValue . ", ";
                }

                $strQueryValue =    $strQueryValue .
                                    " ( " .
                                    SQLAid::escapeNum( $intGroupID ) . ", " .
                                    SQLAid::escapeNum( $arrayUserID[$intCount] ) .
                                    " ) ";
            }

            // INSERTする
            $strSQL =   " INSERT INTO group_users " .
                        " (                      " .
                        "   group_id            ," .
                        "   user_id              " .
                        " )                      " .
                        " VALUES                 " .
                        $strQueryValue;

            // SQL実行
            $objGroupUsers = $db->QueryDB( $strSQL );

            // 失敗したらROLLBACKしてエラーリターン
            if( DB_OK != $objGroupUsers )
            {
                //失敗
                $db->TransactDB( db_strDB_ROLLBACK );
                $db->CloseDB();
                $strErrorMessage = "ユーザグループ情報登録エラー";
                return $strErrorMessage;
            }
        }

        //成功
        $db->TransactDB( db_strDB_COMMIT );
        $db->CloseDB();

        return $strErrorMessage;
    }

    // ***********************************************************************
    // * 関数名     ：ユーザ情報内容ＤＢ更新処理
    // * 機能概要   ：ユーザ情報内容をＤＢ更新する
    // * 返り値     ：成功:true、失敗:false
    // ***********************************************************************
    function UpdateUserGroupData(   $strGroupID         ,   // ユーザグループ名
                                    $strGroupName       ,   // 説明
                                    $strGroupNotes      ,   // 説明
                                    $arrayUserID        ,   // 選択されたユーザID（配列）
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
                    "   user_group              " .
                    " SET                       " .
                    "   group_name          =   " . SQLAid::escapeStr( $strGroupName            ) . ", " .
                    "   group_note          =   " . SQLAid::escapeStr( $strGroupNotes           ) . ", " .
                    "   del_flag            =   " . SQLAid::escapeNum( intval( $strDelFlag )    ) .
                    " WHERE                     " .
                    "   group_id            =   " . SQLAid::escapeNum( intval( $strGroupID )    ) ;

        // SQL実行
        $objUser = $db->QueryDB( $strSQL );

        // 結果成功したらCOMMIT、失敗ならROLLBACKしてエラーメッセージ設定
        if( DB_OK == $objUser )
        {
            //成功したら、グループ情報に関してはCOMMITする
            $db->TransactDB( db_strDB_COMMIT );

            // ********************
            // グループユーザ更新
            // ********************

            // グループユーザを一端すべて削除する
            // DELETE文
            $strSQL =   " DELETE FROM               " .
                        "   group_users             " .
                        " WHERE                     " .
                        "   group_id            =   " . SQLAid::escapeNum( intval( $strGroupID )    ) ;

            // SQL実行
            $objUser = $db->QueryDB( $strSQL );

            // 結果成功したらCOMMIT、失敗ならROLLBACKしてエラーメッセージ設定
            if( DB_OK == $objUser )
            {
                // INSERTする
                // 選択されたユーザ数分、VALUE句を作成する
                $strQueryValue = "";
                for( $intCount = 0; $intCount < count( $arrayUserID ); $intCount++ )
                {
                    if( 0 < strlen( $strQueryValue ) )
                    {
                        $strQueryValue = $strQueryValue . ", ";
                    }

                    $strQueryValue =    $strQueryValue .
                                        " ( " .
                                        SQLAid::escapeNum( intval( $strGroupID ) ) . ", " .
                                        SQLAid::escapeNum( $arrayUserID[$intCount] ) .
                                        " ) ";
                }

                // INSERTする
                $strSQL =   " INSERT INTO group_users " .
                            " (                      " .
                            "   group_id            ," .
                            "   user_id              " .
                            " )                      " .
                            " VALUES                 " .
                            $strQueryValue;

                // SQL実行
                $objGroupUsers = $db->QueryDB( $strSQL );

                // 失敗したらROLLBACKしてエラーリターン
                if( DB_OK != $objGroupUsers )
                {
                    //失敗
                    $db->TransactDB( db_strDB_ROLLBACK );
                    $db->CloseDB();
                    $strErrorMessage = "ユーザ情報登録エラー";
                    return $strErrorMessage;
                }
                else
                {
                    $strErrorMessage = "";
                    $db->TransactDB( db_strDB_COMMIT );
                    $db->CloseDB();
                }
            }
            else
            {
                //失敗
                $db->TransactDB( db_strDB_ROLLBACK );
                $db->CloseDB();
                $strErrorMessage = "ユーザ情報削除エラー";
                return $strErrorMessage;
            }
        }
        else
        {
            //失敗
            $db->TransactDB( db_strDB_ROLLBACK );
            $db->CloseDB();
            $strErrorMessage = "ユーザグループ情報更新エラー";
        }

        return $strErrorMessage;
    }

    // ***********************************************************************
    // * 関数名     ：ユーザ情報取得処理
    // * 機能概要   ：ユーザ情報をすべて取得する
    // * 返り値     ：成功:SELECT結果、失敗:null
    // ***********************************************************************
    function GetUserGroupAll(   &$intRows   )   // 取得できたレコード数
    {
        // ＤＢ接続
        $db = new WrapDB();
        $db->ConnectDB();

        // SELECTする
        $strSQL =   " SELECT                                            " .
                    "      user_group.group_id          as group_id     " .
                    "     ,user_group.group_name        as group_name   " .
                    "     ,user_group.group_note        as group_note   " .
                    "     ,user_group.create_date       as create_date  " .
                    "     ,user_group.del_flag          as del_flag     " .
                    "     ,count( group_users.user_id ) as user_count   " .
                    " FROM " .
                    "     user_group " .
                    "     LEFT JOIN group_users " .
                    "         ON user_group.group_id = group_users.group_id " .
                    " GROUP BY user_group.group_id " .
                    " ORDER BY " .
                    "     user_group.group_id ASC " ;

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
            $arrayUser[] = array(   'group_id'      => $arrayResult["group_id"]     ,
                                    'group_name'    => $arrayResult["group_name"]   ,
                                    'group_note'    => $arrayResult["group_note"]   ,
                                    'create_date'   => $arrayResult["create_date"]  ,
                                    'del_flag'      => $arrayResult["del_flag"]     ,
                                    'user_count'    => $arrayResult["user_count"]   );
        }

        return $arrayUser;
    }

    // ***********************************************************************
    // * 関数名     ：ユーザグループ情報取得処理
    // * 返り値     ：成功:SELECT結果、失敗:null
    // ***********************************************************************
    function GetInfoOne(    $strGroupID     )
    {
        // ＤＢ接続
        $db = new WrapDB();
        $db->ConnectDB();

        // SELECTする
        $strSQL =   " SELECT                                                 " .
                    "   user_group.group_id             as  group_id        ," .
                    "   user_group.group_name           as  group_name      ," .
                    "   user_group.group_note           as  group_note      ," .
                    "   user_group.create_date          as  create_date     ," .
                    "   user_group.del_flag             as  del_flag        ," .
                    "   user.user_id                    as  user_id         ," .
                    "   user.user_name                  as  user_name       ," .
                    "   user.del_flag                   as  user_del_flag   ," .
                    "   user.mail_address_pc            as  mail_address_pc ," .
                    "   user.executive                  as  executive        " .
                    " FROM                                                   " .
                    "   user_group                                           " .
                    "   LEFT  JOIN group_users                               " .
                    "       ON  group_users.group_id = user_group.group_id   " .
                    "   LEFT  JOIN user                                      " .
                    "       ON  group_users.user_id  = user.user_id          " .
                    " WHERE 0=0             " .
                    " AND   user_group.group_id  =     " . SQLAid::escapeNum( $strGroupID ).
                    " ORDER BY user.user_id " ;

        // SQL実行
        $objGroup = $db->QueryDB( $strSQL );

        // 実行結果行数を取得
        $intRows = $db->GetNumRowsDB( $objGroup );

        // 取得したデータを配列に格納
        $arrayGroup = array();
        for( $intCount = 0; $intCount < $intRows; $intCount++ )
        {
            // 取得行を配列化
            $arrayResult = $db->FetchArrayDB( $objGroup );

            $arrayGroup[] = array(  'group_id'          => $arrayResult["group_id"]         ,
                                    'group_name'        => $arrayResult["group_name"]       ,
                                    'group_note'        => $arrayResult["group_note"]       ,
                                    'create_date'       => $arrayResult["create_date"]      ,
                                    'del_flag'          => $arrayResult["del_flag"]         ,
                                    'user_id'           => $arrayResult["user_id"]          ,
                                    'user_name'         => $arrayResult["user_name"]        ,
                                    'user_del_flag'     => $arrayResult["user_del_flag"]    ,
                                    'mail_address_pc'   => $arrayResult["mail_address_pc"]  ,
                                    'executive'         => $arrayResult["executive"]        );
        }

        return $arrayGroup;
    }

    // ***********************************************************************
    // * 関数名     ：ユーザグループ情報取得処理(グループID指定版)
    // * 返り値     ：成功:SELECT結果、失敗:null
    // ***********************************************************************
    function GetUserGroupOfGroupID( $arraySelectedUserGroup ,
                                    &$intRows               )   // 取得できたレコード数
    {
        // IN句の中身を作る
        $strQueryValue = "";
        for( $intCount = 0; $intCount < count( $arraySelectedUserGroup ); $intCount++ )
        {
            if( 0 < strlen( $strQueryValue ) )
            {
                $strQueryValue = $strQueryValue . ", ";
            }

            $strQueryValue =    $strQueryValue .
                                SQLAid::escapeNum( $arraySelectedUserGroup[$intCount] ) ;
        }

        if( 0 == strlen( $strQueryValue ) )
        {
            $arrayUser = null;
            return $arrayUser;
        }

        // ＤＢ接続
        $db = new WrapDB();
        $db->ConnectDB();

        // SELECTする
        $strSQL =   " SELECT                                            " .
                    "      user_group.group_id          as group_id     " .
                    "     ,user_group.group_name        as group_name   " .
                    "     ,user_group.group_note        as group_note   " .
                    "     ,user_group.create_date       as create_date  " .
                    "     ,user_group.del_flag          as del_flag     " .
                    "     ,count( group_users.user_id ) as user_count   " .
                    " FROM " .
                    "     user_group " .
                    "     LEFT JOIN group_users " .
                    "         ON user_group.group_id = group_users.group_id " .
                    " WHERE 0 = 0 " .
                    " AND user_group.group_id in ( " . $strQueryValue . " ) ".
                    " GROUP BY user_group.group_id " .
                    " ORDER BY " .
                    "     user_group.group_id ASC " ;

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
            $arrayUser[] = array(   'group_id'      => $arrayResult["group_id"]     ,
                                    'group_name'    => $arrayResult["group_name"]   ,
                                    'group_note'    => $arrayResult["group_note"]   ,
                                    'create_date'   => $arrayResult["create_date"]  ,
                                    'del_flag'      => $arrayResult["del_flag"]     ,
                                    'user_count'    => $arrayResult["user_count"]   );
        }

        return $arrayUser;
    }

    // ***********************************************************************
    // * 関数名     ：ユーザグループ情報取得処理(グループID指定版,全メールアドレス取得)
    // * 返り値     ：成功:SELECT結果、失敗:null
    // ***********************************************************************
    function GetUserOfGroupID(  $arraySelectedUserGroup ,
                                &$intRows               )   // 取得できたレコード数
    {
        // IN句の中身を作る
        $strQueryValue = "";
        for( $intCount = 0; $intCount < count( $arraySelectedUserGroup ); $intCount++ )
        {
            if( 0 < strlen( $strQueryValue ) )
            {
                $strQueryValue = $strQueryValue . ", ";
            }

            $strQueryValue =    $strQueryValue .
                                SQLAid::escapeNum( $arraySelectedUserGroup[$intCount] ) ;
        }

        if( 0 == strlen( $strQueryValue ) )
        {
            $arrayUser = null;
            return $arrayUser;
        }

        // ＤＢ接続
        $db = new WrapDB();
        $db->ConnectDB();

        // SELECTする
        $strSQL =   " SELECT                                            " .
                    "     user.user_id          as  user_id             " .
                    "    ,user.mail_address_pc  as  mail_address_pc     " .
                    " FROM " .
                    "     user_group                " .
                    "     INNER JOIN group_users    " .
                    "         ON user_group.group_id = group_users.group_id " .
                    "     INNER JOIN user           " .
                    "         ON group_users.user_id = user.user_id         " .
                    " WHERE 0 = 0 " .
                    " AND user_group.group_id in ( " . $strQueryValue . " ) ".
                    " GROUP BY user.mail_address_pc " .
                    " ORDER BY " .
                    "     user.user_id ASC " ;

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
                                    'mail_address_pc'   => $arrayResult["mail_address_pc"]  );
        }

        return $arrayUser;
    }
}
