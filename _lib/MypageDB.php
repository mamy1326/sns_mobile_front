<?php
/* ================================================================================
 * ファイル名   ：MypageDB.php
 * タイトル     ：マイページ情報取得・更新用クラス
 * 作成者       ：間宮 直樹
 * 作成日       ：2008/12/13
 * 内容         ：マイページ情報の取得・更新処理を実施する。
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
include_once( LIB_DIR    . 'AccessLog.php' );           // アクセスログ関数群
include_once( CONFIG_DIR . 'db_conf.php' );             // データベース定義ファイル
include_once( CONFIG_DIR . 'manage_conf.php' );         // 管理画面基本情報定義ファイル

class MypageDB
{
    // ***********************************************************************
    // * 関数名     ：マイページ情報内容ＤＢ登録処理
    // * 返り値     ：送信成功:true、送信失敗:false
    // ***********************************************************************
    function SetMypageData( $strUserID              ,   // ユーザID
                            $strReceiveMail_1       ,   // 受信メール１
                            $strReceiveMail_2       ,   // 受信メール２
                            $strReceiveMail_3       ,   // 受信メール３
                            $arraySelectedAlbum     ,   // 受信するフォトアルバムIDの配列
                            $arraySelectedThread    )   // 受信するスレッドIDの配列
    {
        $intReturnCode = true;
        $strErrorMessage = "";

        // ＤＢ接続
        $db = new WrapDB();
        $db->ConnectDB();

        //トランザクション開始
        $db->AutoCommitOffDB();

        // エラーが起きたら即breakして処理を終了できるように、必ず終わるdo-whileを使用する。
        do
        {
            // ***************************
            // マイページ情報登録・更新
            // ***************************

            // マイページデータ存在チェック
            $strSQL_Mypage =    " SELECT            " .
                                "   user_id         " .
                                " FROM              " .
                                "   mypage_master   " .
                                " WHERE             " .
                                "   user_id =       " . SQLAid::escapeNum( intval( $strUserID ) );

            // SQL実行
            $objMypage = $db->QueryDB( $strSQL_Mypage );

            // 実行結果行数を取得
            $intRowsMypage = $db->GetNumRowsDB( $objMypage );

            unset($objMypage);

            // (なしの場合はINSERT)
            if( 0 == $intRowsMypage )
            {
                // INSERTする
                $strSQL =   " INSERT INTO mypage_master " .
                            " (                         " .
                            "   user_id                ," .
                            "   receive_mail_1         ," .
                            "   receive_mail_2         ," .
                            "   receive_mail_3          " .
                            " )                         " .
                            " VALUES                    " .
                            " (                         " .
                            "    " . SQLAid::escapeNum( intval( $strUserID )    ) . ", " .
                            "    " . SQLAid::escapeStr( $strReceiveMail_1       ) . ", " .
                            "    " . SQLAid::escapeStr( $strReceiveMail_2       ) . ", " .
                            "    " . SQLAid::escapeStr( $strReceiveMail_3       ) .
                            ")                          " ;
            }
            // (ありの場合はUPDATE)
            else
            {
                $strSQL =   " UPDATE                    " .
                            "   mypage_master           " .
                            " SET                       " .
                            "   receive_mail_1      =   " . SQLAid::escapeStr( $strReceiveMail_1    ) . ", " .
                            "   receive_mail_2      =   " . SQLAid::escapeStr( $strReceiveMail_2    ) . ", " .
                            "   receive_mail_3      =   " . SQLAid::escapeStr( $strReceiveMail_3    ) .
                            " WHERE                     " .
                            "   user_id             =   " . SQLAid::escapeNum( intval( $strUserID ) ) ;
            }

            // SQL実行
            $objResult = $db->QueryDB( $strSQL );

            // 結果成功したらCOMMIT、失敗ならROLLBACKしてエラーメッセージ設定
            if( DB_OK == $objResult )
            {
                //成功
                $db->TransactDB( db_strDB_COMMIT );

                // ****************************
                // フォトアルバム受信設定実施
                // ****************************
                if( true == is_array( $arraySelectedAlbum ) )
                {
                    // まずすべて削除する
                    // DELETE文
                    $strSQL_Delete =    " DELETE FROM               " .
                                        "   photo_album_receive     " .
                                        " WHERE                     " .
                                        "   user_id             =   " . SQLAid::escapeNum( intval( $strUserID ) ) ;

                    // SQL実行
                    $objDelete = $db->QueryDB( $strSQL_Delete );

                    // 結果成功したらCOMMIT、失敗ならROLLBACKしてエラーメッセージ設定
                    if( DB_OK == $objDelete )
                    {
                        // INSERTする
                        // 選択されたフォトアルバム数分、VALUE句を作成する
                        $strQueryValue = "";
                        for( $intCount = 0; $intCount < count( $arraySelectedAlbum ); $intCount++ )
                        {
                            if( 0 < strlen( $strQueryValue ) )
                            {
                                $strQueryValue = $strQueryValue . ", ";
                            }

                            $strQueryValue =    $strQueryValue .
                                                " ( " .
                                                SQLAid::escapeNum( intval( $strUserID )                     ) . ", " .
                                                SQLAid::escapeNum( intval( $arraySelectedAlbum[$intCount] ) ) .
                                                " ) ";
                        }

                        // INSERTする
                        $strSQL =   " INSERT INTO photo_album_receive   " .
                                    " (                                 " .
                                    "   user_id                        ," .
                                    "   album_id                        " .
                                    " )                                 " .
                                    " VALUES                            " .
                                    $strQueryValue;

                        // SQL実行
                        $objAlbum = $db->QueryDB( $strSQL );

                        // 失敗したらROLLBACKしてエラーリターン
                        if( DB_OK != $objAlbum )
                        {
                            //失敗
                            $db->TransactDB( db_strDB_ROLLBACK );
                            $strErrorMessage = "フォトアルバム受信設定登録エラー";
                            break;
                        }
                        else
                        {
                            $strErrorMessage = "";
                            $db->TransactDB( db_strDB_COMMIT );
                        }
                    }
                    else
                    {
                        //失敗
                        $db->TransactDB( db_strDB_ROLLBACK );
                        $strErrorMessage = "フォトアルバム受信設定削除エラー";
                        break;
                    }
                }

                // ****************************
                // スレッド受信設定実施
                // ****************************
                if( true == is_array( $arraySelectedThread ) )
                {
                    // まずすべて削除する
                    // DELETE文
                    $strSQL_Delete =    " DELETE FROM       " .
                                        "   thread_receive  " .
                                        " WHERE             " .
                                        "   user_id     =   " . SQLAid::escapeNum( intval( $strUserID ) ) ;

                    // SQL実行
                    $objDelete = $db->QueryDB( $strSQL_Delete );

                    // 結果成功したらCOMMIT、失敗ならROLLBACKしてエラーメッセージ設定
                    if( DB_OK == $objDelete )
                    {
                        // INSERTする
                        // 選択されたフォトアルバム数分、VALUE句を作成する
                        $strQueryValue = "";
                        for( $intCount = 0; $intCount < count( $arraySelectedThread ); $intCount++ )
                        {
                            if( 0 < strlen( $strQueryValue ) )
                            {
                                $strQueryValue = $strQueryValue . ", ";
                            }

                            $strQueryValue =    $strQueryValue .
                                                " ( " .
                                                SQLAid::escapeNum( intval( $strUserID )                         ) . ", " .
                                                SQLAid::escapeNum( intval( $arraySelectedThread[$intCount] )    ) .
                                                " ) ";
                        }

                        // INSERTする
                        $strSQL =   " INSERT INTO thread_receive    " .
                                    " (                             " .
                                    "   user_id                    ," .
                                    "   thread_id                   " .
                                    " )                             " .
                                    " VALUES                        " .
                                    $strQueryValue;

                        // SQL実行
                        $objThread = $db->QueryDB( $strSQL );

                        // 失敗したらROLLBACKしてエラーリターン
                        if( DB_OK != $objThread )
                        {
                            //失敗
                            $db->TransactDB( db_strDB_ROLLBACK );
                            $strErrorMessage = "スレッド情報登録エラー";
                            break;
                        }
                        else
                        {
                            $strErrorMessage = "";
                            $db->TransactDB( db_strDB_COMMIT );
                        }
                    }
                    else
                    {
                        //失敗
                        $db->TransactDB( db_strDB_ROLLBACK );
                        $strErrorMessage = "スレッド受信設定削除エラー";
                        break;
                    }
                }
            }
            else
            {
                //失敗
                $db->TransactDB( db_strDB_ROLLBACK );
                $strErrorMessage = "マイページ情報登録・更新エラー";
                break;
            }
            break;
        }
        while( false );

        $db->CloseDB();

        // エラー判定
        if( 0 < strlen( $strErrorMessage ) )
        {
            $intReturnCode = false;
        }
        else
        {
            $intReturnCode = true;
        }

        return $intReturnCode;
    }


    // ***********************************************************************
    // * 関数名     ：マイページ情報取得処理(１件のみ)
    // * 返り値     ：成功:SELECT結果、失敗:null
    // ***********************************************************************
    function GetMypageOne( $strSessionID, &$intRows )
    {
        // セッションIDからユーザIDを取得する
        $arrayUser = UserDB::GetLoginUserOfSession( $strSessionID   ,
                                                    $intUserRows    );

        $strUserID =  $arrayUser["user_id"];

        unset($arrayUser);

        // ＤＢ接続
        $db = new WrapDB();
        $db->ConnectDB();

        // SELECTする
        $strSQL =   " SELECT                 " .
                    "   user_id             ," .
                    "   receive_mail_1      ," .
                    "   receive_mail_2      ," .
                    "   receive_mail_3       " .
                    " FROM                   " .
                    "   mypage_master        " .
                    " WHERE 0=0              " .
                    " AND   user_id   =      " . SQLAid::escapeNum( intval( $strUserID ) );
        // SQL実行
        $objResult = $db->QueryDB( $strSQL );

        // 実行結果行数を取得
        $intRows = $db->GetNumRowsDB( $objResult );

        // 取得したデータを配列に格納
        if( 0 < $intRows )
        {
            // 取得行を配列化
            $arrayResult = $db->FetchArrayDB( $objResult );

            $arrayReturn = array(   'user_id'           => $arrayResult["user_id"]          ,
                                    'receive_mail_1'    => $arrayResult["receive_mail_1"]   ,
                                    'receive_mail_2'    => $arrayResult["receive_mail_2"]   ,
                                    'receive_mail_3'    => $arrayResult["receive_mail_3"]   );

            unset($objResult);
        }
        else
        {
            $arrayReturn = array(   'user_id'           => $strUserID   ,
                                    'receive_mail_1'    => ""           ,
                                    'receive_mail_2'    => ""           ,
                                    'receive_mail_3'    => ""           );
        }

        return $arrayReturn;
    }

    // ***********************************************************************
    // * 関数名     ：更新メール受信者リスト取得
    // * 返り値     ：成功:SELECT結果、失敗:null
    // ***********************************************************************
    function GetUpdateMailUserList( $intProcFlag,
                                    $intID      ,
                                    &$intRows   )
    {
        // ＤＢ接続
        $db = new WrapDB();
        $db->ConnectDB();

        // SELECTする
        $strSQL =   " SELECT                             " .
                    "   us.user_id      as  user_id     ," .
                    "   us.login_id     as  login_id    ," .
                    "   us.user_name    as  user_name    " .
                    " FROM                               " ;

        // 処理フラグ判定
        // (フォトアルバム)
        if( HEAD_MANAGE_ALBUM == $intProcFlag )
        {
            $strSQL =   $strSQL .
                        "               photo_album_receive receive     " ;     // フォトアルバム受信者リストと、ユーザマスタを繋げて、ログインID、ユーザ名取得

            $strWhere = " AND   receive.album_id   =    " . SQLAid::escapeNum( $intID );
        }
        // (スレッド)
        else
        {
            $strSQL =   $strSQL .
                        "               thread_receive      receive     " ;     // スレッド受信者リストと、ユーザマスタを繋げて、ログインID、ユーザ名取得

            $strWhere = " AND   receive.thread_id  =    " . SQLAid::escapeNum( $intID );
        }

        $strSQL =   $strSQL .
                    "   INNER JOIN  user                us          " .
                    "       ON  receive.user_id = us.user_id        " .
                    "       AND us.del_flag = 0                     " .
                    " WHERE 0=0                     " .
                    $strWhere   ;

        // SQL実行
        $objResult = $db->QueryDB( $strSQL );

        // 実行結果行数を取得
        $intRows = $db->GetNumRowsDB( $objResult );

        // 取得したデータを配列に格納
        $arrayReturn = array();
        if( 0 < $intRows )
        {
            for( $intCount = 0; $intCount < $intRows; $intCount++ )
            {
                // 取得行を配列化
                $arrayResult = $db->FetchArrayDB( $objResult );

                $arrayReturn[] = array( 'user_id'   => $arrayResult["user_id"]      ,
                                        'login_id'  => $arrayResult["login_id"]     ,
                                        'user_name' => $arrayResult["user_name"]    );
            }
            unset($objResult);
        }

        return $arrayReturn;
    }

    // ***********************************************************************
    // * 関数名     ：ユーザ別更新メールアドレスリスト取得
    // * 返り値     ：成功:SELECT結果、失敗:null
    // ***********************************************************************
    function GetMailListOfUser( $strUserID  )
    {
        // ＤＢ接続
        $db = new WrapDB();
        $db->ConnectDB();

        // SELECTする
        $strSQL =   " SELECT            " .
                    "   receive_mail_1, " .
                    "   receive_mail_2, " .
                    "   receive_mail_3  " .
                    " FROM              " .
                    "   mypage_master   " .
                    " WHERE 0=0         " .
                    " AND   user_id   = " . SQLAid::escapeNum( intval( $strUserID ) );

        // SQL実行
        $objResult = $db->QueryDB( $strSQL );

        // 実行結果行数を取得
        $intRows = $db->GetNumRowsDB( $objResult );

        // 取得したデータを配列に格納
        if( 0 < $intRows )
        {
            // 取得行を配列化
            $arrayResult = $db->FetchArrayDB( $objResult );

            $arrayReturn = array();

            if( 0 < strlen( $arrayResult["receive_mail_1"] ) )
            {
                $arrayReturn[] = array( "receive_mail" => $arrayResult["receive_mail_1"] );
            }
            elseif( 0 < strlen( $arrayResult["receive_mail_2"] ) )
            {
                $arrayReturn[] = array( "receive_mail" => $arrayResult["receive_mail_2"] );
            }
            elseif( 0 < strlen( $arrayResult["receive_mail_3"] ) )
            {
                $arrayReturn[] = array( "receive_mail" => $arrayResult["receive_mail_3"] );
            }

            unset($objResult);
        }
        else
        {
            $arrayReturn = NULL;
        }

        return $arrayReturn;
    }

}
