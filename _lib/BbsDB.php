<?php
/* ================================================================================
 * ファイル名   ：BbsDB.php
 * タイトル     ：BBS取得・更新用クラス
 * 作成者       ：間宮 直樹
 * 作成日       ：2008/11/19
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
include_once( LIB_DIR    . 'MailSend.php' );            // メール送信関数群
include_once( CONFIG_DIR . 'db_conf.php' );             // データベース定義ファイル
include_once( CONFIG_DIR . 'manage_conf.php' );         // 管理画面基本情報定義ファイル

class BbsDB
{
    // ***********************************************************************
    // * 関数名     ：管理本部通信情報内容ＤＢ登録処理
    // * 機能概要   ：管理本部通信情報をＤＢに登録する
    // * 返り値     ：送信成功:true、送信失敗:false
    // ***********************************************************************
    function InsertBbs( $strThreadID,
                        $now        ,   // 投稿日時
                        $subject    ,   // タイトル
                        $from       ,   // 送信者メールアドレス
                        $text       ,   // 本文
                        $attach     ,   // 添付ファイル名
                        $strThreadName = "" )
    {
        $strErrorMessage = "";

        // ログインユーザ名取得
        $arrayUser =    UserDB::GetLoginUserOfBbsMail(  $from   ,
                                                        $intRows );
        if( 1 > $intRows )
        {
            return false;
        }

        if( 0 == strlen( $now ) )
        {
            $strDate = " sysdate() ";
        }
        else
        {
            $strDate = SQLAid::escapeStr( date( "Y-m-d H:i", $now ) );
        }

        // ＤＢ接続
        $db = new WrapDB();
        $db->ConnectDB();

        //トランザクション開始
        $db->AutoCommitOffDB();

        // INSERTする
        $strSQL =   " INSERT INTO bbs_master " .
                    " (                      " .
                    "   thread_id           ," .
                    "   date                ," .
                    "   mail_address        ," .
                    "   login_id            ," .
                    "   title               ," .
                    "   body                ," .
                    "   image_name           " .
                    " )                      " .
                    " VALUES                 " .
                    " (                      " .
                    "    " . SQLAid::escapeNum( intval( $strThreadID )  ) . ", " .
                    "    " . $strDate . ", " .
                    "    " . SQLAid::escapeStr( $from                   ) . ", " .
                    "    " . SQLAid::escapeStr( $arrayUser["login_id"]  ) . ", " .
                    "    " . SQLAid::escapeStr( $subject                ) . ", " .
                    "    " . SQLAid::escapeStr( $text                   ) . ", " .
                    "    " . SQLAid::escapeStr( $attach                 ) .
                    " )                   " ;

        // SQL実行
        $objInfo = $db->QueryDB( $strSQL );

        // 結果成功したらCOMMIT、失敗ならROLLBACKしてエラーメッセージ設定
        if( DB_OK == $objInfo )
        {
            //成功
            $db->TransactDB( db_strDB_COMMIT );
            $db->CloseDB();

            // ■■■■■■■■■■■■■■■■
            // 更新メール希望者にメール
            // ■■■■■■■■■■■■■■■■
            MailSend::UpdateMailSend(   HEAD_MANAGE_THREAD      ,   // スレッド指定
                                        intval( $strThreadID )  ,   // スレッドID
                                        $strThreadName          );  // スレッド名（メール本文用）
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
    function UpdateBbs( $strThreadID    ,
                        $strBbsDate     ,   //
                        $strLoginID     ,   //
                        $strTitle       ,   //
                        $strBody        )   //
    {
        $strErrorMessage = "";

        // ＤＢ接続
        $db = new WrapDB();
        $db->ConnectDB();

        //トランザクション開始
        $db->AutoCommitOffDB();

        // UPDATE文
        $strSQL =   " UPDATE                    " .
                    "   bbs_master              " .
                    " SET                       " .
                    "   title               =   " . SQLAid::escapeStr( $strTitle    ) . ", " .
                    "   body                =   " . SQLAid::escapeStr( $strBody     ) .
                    " WHERE                     " .
                    "     thread_id         =   " . SQLAid::escapeNum( intval( $strThreadID ) ) .
                    " AND date              =   " . SQLAid::escapeStr( $strBbsDate  ) .
                    " AND login_id          =   " . SQLAid::escapeStr( $strLoginID  ) ;

        // SQL実行
        $objInfo = $db->QueryDB( $strSQL );

        // 結果成功したらCOMMIT、失敗ならROLLBACKしてエラーメッセージ設定
        if( DB_OK == $objInfo )
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
    // * 関数名     ：管理本部通信情報内容ＤＢ更新処理
    // * 機能概要   ：管理本部通信情報内容をＤＢ更新する
    // * 返り値     ：成功:true、失敗:false
    // ***********************************************************************
    function DeleteBbs( $strThreadID    ,
                        $strBbsDate     ,   //
                        $strLoginID     )   //
    {
        $strErrorMessage = "";

        // ＤＢ接続
        $db = new WrapDB();
        $db->ConnectDB();

        //トランザクション開始
        $db->AutoCommitOffDB();

        // UPDATE文
        $strSQL =   " UPDATE                    " .
                    "   bbs_master              " .
                    " SET                       " .
                    "   del_flag            = 1 " .
                    " WHERE                     " .
                    "     thread_id         =   " . SQLAid::escapeNum( intval( $strThreadID ) ) .
                    " AND date              =   " . SQLAid::escapeStr( $strBbsDate  ) .
                    " AND login_id          =   " . SQLAid::escapeStr( $strLoginID  ) ;

        // SQL実行
        $objInfo = $db->QueryDB( $strSQL );

        // 結果成功したらCOMMIT、失敗ならROLLBACKしてエラーメッセージ設定
        if( DB_OK == $objInfo )
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
    function GetBbsAll( $strThreadID    ,
                        &$intRows       )   // 取得できたレコード数
    {
        // ＤＢ接続
        $db = new WrapDB();
        $db->ConnectDB();

        // SELECTする
        $strSQL =   " SELECT             " .
                    "   date            ," .
                    "   mail_address    ," .
                    "   login_id        ," .
                    "   title           ," .
                    "   body            ," .
                    "   image_name       " .
                    " FROM               " .
                    "   bbs_master       " .
                    " WHERE              " .
                    "   del_flag = 0     " .
                    " AND thread_id =    " . SQLAid::escapeNum( intval( $strThreadID ) ) .
                    " ORDER BY           " .
                    "   date DESC        " ;

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
            $arrayInfo[] = array(   'date'          => $arrayResult["date"]         ,
                                    'mail_address'  => $arrayResult["mail_address"] ,
                                    'login_id'      => $arrayResult["login_id"]     ,
                                    'title'         => $arrayResult["title"]        ,
                                    'body'          => $arrayResult["body"]         ,
                                    'image_name'    => $arrayResult["image_name"]   );
        }

        return $arrayInfo;
    }

    // ***********************************************************************
    // * 関数名     ：管理本部通信情報取得処理(１件のみ)
    // * 返り値     ：成功:SELECT結果、失敗:null
    // ***********************************************************************
    function GetBbsOne( $strThreadID    ,
                        $strBbsDate     ,   // 投稿年月日時分秒
                        $strLoginID     ,   // 投稿者ログインID
                        &$intRows       )   // 取得できたレコード数
    {
        // ＤＢ接続
        $db = new WrapDB();
        $db->ConnectDB();

        // SELECTする
        $strSQL =   " SELECT             " .
                    "   date            ," .
                    "   mail_address    ," .
                    "   login_id        ," .
                    "   title           ," .
                    "   body            ," .
                    "   image_name       " .
                    " FROM               " .
                    "   bbs_master       " .
                    " WHERE 0=0          " .
                    " AND   thread_id  = " . SQLAid::escapeNum( intval( $strThreadID ) ) .
                    " AND   date       = " . SQLAid::escapeStr( $strBbsDate ) .
                    " AND   login_id   = " . SQLAid::escapeStr( $strLoginID ) ;
        // SQL実行
        $objInfo = $db->QueryDB( $strSQL );

        // 実行結果行数を取得
        $intRows = $db->GetNumRowsDB( $objInfo );

        // 取得したデータを配列に格納
        if( 0 < $intRows )
        {
            // 取得行を配列化
            $arrayResult = $db->FetchArrayDB( $objInfo );

            $arrayInfo   = array(   'date'          => $arrayResult["date"]         ,
                                    'mail_address'  => $arrayResult["mail_address"] ,
                                    'login_id'      => $arrayResult["login_id"]     ,
                                    'title'         => $arrayResult["title"]        ,
                                    'body'          => $arrayResult["body"]         ,
                                    'image_name'    => $arrayResult["image_name"]   );
        }
        else
        {
            $arrayInfo = null;
        }

        return $arrayInfo;
    }

    // ***********************************************************************
    // * 関数名     ：管理本部通信情報取得処理（主にトップページ用）
    // * 機能概要   ：指定されたレコード数取得する
    // * 返り値     ：成功:SELECT結果、失敗:null
    // ***********************************************************************
    function GetBbsLimit(   $strThreadID        ,
                            $intStartRecordNum  ,   // 取得開始レコード
                            $intGetRecordCount  ,   // 取得するレコード数
                            &$intRows           )   // 取得できたレコード数
    {
        // ＤＢ接続
        $db = new WrapDB();
        $db->ConnectDB();

        // SELECTする
        $strSQL =   " SELECT             " .
                    "   bbs_master.date         as date           ," .
                    "   bbs_master.mail_address as mail_address   ," .
                    "   bbs_master.login_id     as login_id       ," .
                    "   bbs_master.title        as title          ," .
                    "   bbs_master.body         as body           ," .
                    "   bbs_master.image_name   as image_name     ," .
                    "   user.user_name          as user_name       " .
                    " FROM               " .
                    "   bbs_master       " .
                    " LEFT JOIN user     " .
                    "   ON bbs_master.login_id = user.login_id " .
                    " WHERE                         " .
                    "   bbs_master.del_flag = 0     " .
                    " AND bbs_master.thread_id =    " . SQLAid::escapeNum( intval( $strThreadID ) ) .
                    " ORDER BY                      " .
                    "   bbs_master.date DESC        " .
                    " LIMIT " . SQLAid::escapeNum( $intStartRecordNum ) . " , " . SQLAid::escapeNum( $intGetRecordCount );

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
            $arrayInfo[] = array(   'date'          => $arrayResult["date"]         ,
                                    'mail_address'  => $arrayResult["mail_address"] ,
                                    'login_id'      => $arrayResult["login_id"]     ,
                                    'title'         => $arrayResult["title"]        ,
                                    'body'          => $arrayResult["body"]         ,
                                    'image_name'    => $arrayResult["image_name"]   ,
                                    'user_name'     => $arrayResult["user_name"]    );
        }

        return $arrayInfo;
    }

    // ***********************************************************************
    // * 関数名     ：スレッド情報取得処理（全件）
    // * 機能概要   ：スレッド情報をすべて取得する
    // * 返り値     ：成功:SELECT結果、失敗:null
    // ***********************************************************************
    function GetThreadAll(  &$intRows       ,   // 取得できたレコード数
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
                        "   thread_id           ," .
                        "   thread_mailaddress  ," .
                        "   thread_name         ," .
                        "   thread_note         ," .
                        "   create_date         ," .
                        "   del_flag             " .
                        " FROM                   " .
                        "   thread_master        " .
                        " ORDER BY               " .
                        "   create_date    DESC  " ;
        }
        // フロント用
        elseif( 1 == $intDelFlag )
        {
            // セッションIDからユーザIDを求める
            $arrayUser =    UserDB::GetLoginUserOfSession(  $_GET["s_id"]   ,
                                                            &$intRows       );

            $strSQL =   " SELECT                 " .
                        "   thread.thread_id            as  thread_id           ," .
                        "   thread.thread_mailaddress   as  thread_mailaddress  ," .
                        "   thread.thread_name          as  thread_name         ," .
                        "   thread.thread_note          as  thread_note         ," .
                        "   thread.create_date          as  create_date         ," .
                        "   thread.del_flag             as  del_flag             " .
                        " FROM                                          " .
                        "               thread_master   thread          " .
                        "   INNER JOIN  thread_user     user            " .
                        "       ON thread.thread_id = user.thread_id    " .
                        "   INNER JOIN  group_users     u_group         " .
                        "       ON  user.user_or_group_id = u_group.group_id " .
                        "       AND u_group.user_id = " . SQLAid::escapeNum( intval( $arrayUser["user_id"] ) ) .
                        " WHERE                     " .
                        "   del_flag <> 2           " .
                        " GROUP BY thread.thread_id " .
                        " ORDER BY                  " .
                        "   create_date    DESC     " ;
        }
        // 管理画面一覧用
        else
        {
            $strSQL =   " SELECT                 " .
                        "   thread.thread_id            as  thread_id           ," .
                        "   thread.thread_mailaddress   as  thread_mailaddress  ," .
                        "   thread.thread_name          as  thread_name         ," .
                        "   thread.thread_note          as  thread_note         ," .
                        "   thread.create_date          as  create_date         ," .
                        "   thread.del_flag             as  del_flag            ," .
                        "   u_group.group_id            as  group_id            ," .
                        "   u_group.group_name          as  group_name          ," .
                        "   COUNT( comment.date )       as  comment_count        " .
                        " FROM                                                  " .
                        "               thread_master   thread                  " .
                        "   LEFT  JOIN  thread_user     user                    " .
                        "       ON thread.thread_id = user.thread_id            " .
                        "   LEFT  JOIN  user_group          u_group             " .
                        "       ON  user.user_or_group_id = u_group.group_id    " .
                        "   LEFT  JOIN  bbs_master     comment                  " .
                        "       ON  thread.thread_id = comment.thread_id        " .
                        " GROUP BY thread.thread_id     " .
                        " ORDER BY               " .
                        "   create_date    DESC     " ;

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
            $arrayInfo[] = array(   'thread_id'             => $arrayResult["thread_id"]            ,
                                    'thread_mailaddress'    => $arrayResult["thread_mailaddress"]   ,
                                    'thread_name'           => $arrayResult["thread_name"]          ,
                                    'thread_note'           => $arrayResult["thread_note"]          ,
                                    'create_date'           => $arrayResult["create_date"]          ,
                                    'del_flag'              => $arrayResult["del_flag"]             ,
                                    'group_id'              => $arrayResult["group_id"]             ,
                                    'group_name'            => $arrayResult["group_name"]           ,
                                    'comment_count'         => $arrayResult["comment_count"]        );
        }

        return $arrayInfo;
    }

    // ***********************************************************************
    // * 関数名     ：ＢＢＳスレッド情報内容ＤＢ登録処理
    // * 機能概要   ：ＢＢＳスレッド情報をＤＢに登録する
    // * 返り値     ：送信成功:true、送信失敗:false
    // ***********************************************************************
    function InsertThreadData(  $strThreadName          ,   // スレッドタイトル
                                $strThreadMail          ,   // 使用メールアドレス
                                $strThreadComment       ,   // 説明
                                $strDelFlag             ,   // 削除フラグ
                                $arraySelectedUserGroup )   // 選択されたユーザグループ
    {
        $strErrorMessage = "";

        // ＤＢ接続
        $db = new WrapDB();
        $db->ConnectDB();

        //トランザクション開始
        $db->AutoCommitOffDB();

        // INSERTする
        $strSQL =   " INSERT INTO thread_master  " .
                    " (                          " .
                    "   thread_name             ," .
                    "   thread_mailaddress      ," .
                    "   thread_note             ," .
                    "   create_date             ," .
                    "   del_flag                 " .
                    " )                          " .
                    " VALUES                     " .
                    " (                          " .
                    "    " . SQLAid::escapeStr( $strThreadName      ) . ", " .
                    "    " . SQLAid::escapeStr( $strThreadMail      ) . ", " .
                    "    " . SQLAid::escapeStr( $strThreadComment   ) . ", " .
                    "    sysdate()                                       , " .
                    "    " . SQLAid::escapeNum( $strDelFlag         ) .
                    ")                           " ;


        // SQL実行
        $objSQL = $db->QueryDB( $strSQL );

        // 結果成功したらCOMMIT、失敗ならROLLBACKしてエラーメッセージ設定
        if( DB_OK == $objSQL )
        {
            //成功
            $db->TransactDB( db_strDB_COMMIT );

            // ****************************
            // グループ情報の挿入
            // ****************************
            if( 0 < count( $arraySelectedUserGroup ) )
            {
                // 今登録したスレッドのID取得
                $strSelectSQL = " SELECT MAX( thread_id ) as thread_id FROM thread_master ";
                $objInfo = $db->QueryDB( $strSelectSQL );
                $arrayResult = $db->FetchArrayDB( $objInfo );
                $intThreadID = intval( $arrayResult["thread_id"] );

                // 選択されたユーザ数分、VALUE句を作成する
                $strQueryValue = "";
                for( $intCount = 0; $intCount < count( $arraySelectedUserGroup ); $intCount++ )
                {
                    if( 0 < strlen( $strQueryValue ) )
                    {
                        $strQueryValue = $strQueryValue . ", ";
                    }

                    $strQueryValue =    $strQueryValue .
                                        " ( " .
                                        SQLAid::escapeNum( $intThreadID ) . ", " .
                                        SQLAid::escapeNum( intval( $arraySelectedUserGroup[$intCount] ) ) . ", " .
                                        " 1 " .
                                        " ) " ;
                }

                // INSERTする
                $strSQL =   " INSERT INTO thread_user   " .
                            " (                         " .
                            "   thread_id              ," .
                            "   user_or_group_id       ," .
                            "   flag                    " .
                            " )                         " .
                            " VALUES                    " .
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
            $strErrorMessage ="ＢＢＳスレッド情報登録エラー";
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
            $strErrorMessage = "ＢＢＳスレッド情報登録エラー";
        }

        return $strErrorMessage;
    }

    // ***********************************************************************
    // * 関数名     ：ＢＢＳスレッド情報内容ＤＢ更新処理
    // * 機能概要   ：ＢＢＳスレッド情報内容をＤＢ更新する
    // * 返り値     ：成功:true、失敗:false
    // ***********************************************************************
    function UpdateThreadData(  $strThreadID            ,   // スレッドID
                                $strThreadName          ,   // スレッドタイトル
                                $strThreadMail          ,   // 使用メールアドレス
                                $strThreadComment       ,   // 説明
                                $strDelFlag             ,   // 削除フラグ
                                $arraySelectedUserGroup )   // 選択されたユーザグループ
    {
        $strErrorMessage = "";

        // ＤＢ接続
        $db = new WrapDB();
        $db->ConnectDB();

        //トランザクション開始
        $db->AutoCommitOffDB();

        // UPDATE文
        $strSQL =   " UPDATE                    " .
                    "   thread_master           " .
                    " SET                       " .
                    "   thread_name         =   " . SQLAid::escapeStr( $strThreadName           ) . ", " .
                    "   thread_mailaddress  =   " . SQLAid::escapeStr( $strThreadMail           ) . ", " .
                    "   thread_note         =   " . SQLAid::escapeStr( $strThreadComment        ) . ", " .
                    "   del_flag            =   " . SQLAid::escapeNum( intval( $strDelFlag )    ) .
                    " WHERE                     " .
                    "   thread_id           =   " . SQLAid::escapeNum( intval( $strThreadID )   ) ;

        // SQL実行
        $objSQL = $db->QueryDB( $strSQL );

        // 結果成功したらCOMMIT、失敗ならROLLBACKしてエラーメッセージ設定
        if( DB_OK == $objSQL )
        {
            //成功
            $db->TransactDB( db_strDB_COMMIT );

            // ****************************
            // グループ情報の挿入
            // ****************************
            if( 0 < count( $arraySelectedUserGroup ) )
            {
                // グループを一端すべて削除する
                // DELETE文
                $strSQL =   " DELETE FROM   " .
                            "   thread_user " .
                            " WHERE         " .
                            "   thread_id = " . SQLAid::escapeNum( intval( $strThreadID ) ) ;

                // SQL実行
                $objGroupDel = $db->QueryDB( $strSQL );

                // 結果成功したらCOMMIT、失敗ならROLLBACKしてエラーメッセージ設定
                if( DB_OK == $objGroupDel )
                {
                    // 選択されたユーザ数分、VALUE句を作成する
                    $strQueryValue = "";
                    for( $intCount = 0; $intCount < count( $arraySelectedUserGroup ); $intCount++ )
                    {
                        if( 0 < strlen( $strQueryValue ) )
                        {
                            $strQueryValue = $strQueryValue . ", ";
                        }

                        $strQueryValue =    $strQueryValue .
                                            " ( " .
                                            SQLAid::escapeNum( intval( $strThreadID ) ) . ", " .
                                            SQLAid::escapeNum( intval( $arraySelectedUserGroup[$intCount] ) ) . ", " .
                                            " 1 " .
                                            " ) " ;
                    }

                    // INSERTする
                    $strSQL =   " INSERT INTO thread_user   " .
                                " (                         " .
                                "   thread_id              ," .
                                "   user_or_group_id       ," .
                                "   flag                    " .
                                " )                         " .
                                " VALUES                    " .
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
            $strErrorMessage = "ＢＢＳスレッド情報更新エラー";
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
            $strErrorMessage = "ＢＢＳスレッド情報更新エラー";
        }

        return $strErrorMessage;
    }

    // ***********************************************************************
    // * 関数名     ：ＢＢＳスレッド情報取得処理(ユーザグループ取得)
    // * 返り値     ：成功:SELECT結果、失敗:null
    // ***********************************************************************
    function GetThreadOneAndGroup(  $intThreadID ,
                                    &$intRows   )
    {
        // ＤＢ接続
        $db = new WrapDB();
        $db->ConnectDB();

        // SELECTする
        $strSQL =   " SELECT                 " .
                    "   thread.thread_id            as  thread_id           ," .
                    "   thread.thread_name          as  thread_name         ," .
                    "   thread.thread_mailaddress   as  thread_mailaddress  ," .
                    "   thread.thread_note          as  thread_note         ," .
                    "   thread.create_date          as  create_date         ," .
                    "   thread.del_flag             as  del_flag            ," .
                    "   u_group.group_id            as  group_id            ," .
                    "   u_group.group_name          as  group_name          ," .
                    "   u_group.group_note          as  group_note          ," .
                    "   u_group.create_date         as  group_create_date    " .
                    " FROM                                              " .
                    "               thread_master   thread              " .
                    "   LEFT  JOIN  thread_user     user                " .
                    "       ON  user.thread_id = thread.thread_id       " .
                    "   LEFT  JOIN user_group  u_group                  " .
                    "       ON  user.user_or_group_id = u_group.group_id" .
                    "       AND u_group.del_flag = 0                    " .
                    " WHERE 0=0                 " .
                    " AND   thread.thread_id =  " . SQLAid::escapeNum( $intThreadID );
        // SQL実行
        $objSQL = $db->QueryDB( $strSQL );

        // 実行結果行数を取得
        $intRows = $db->GetNumRowsDB( $objSQL );

        // 取得したデータを配列に格納
        $arrayAlbum = array();
        for( $intCount = 0; $intCount < $intRows; $intCount++ )
        {
            // 取得行を配列化
            $arrayResult = $db->FetchArrayDB( $objSQL );

            $arrayAlbum[] = array(  'thread_id'             => $arrayResult["thread_id"]            ,
                                    'thread_name'           => $arrayResult["thread_name"]          ,
                                    'thread_mailaddress'    => $arrayResult["thread_mailaddress"]   ,
                                    'thread_note'           => $arrayResult["thread_note"]          ,
                                    'create_date'           => $arrayResult["create_date"]          ,
                                    'del_flag'              => $arrayResult["del_flag"]             ,
                                    'group_id'              => $arrayResult["group_id"]             ,
                                    'group_name'            => $arrayResult["group_name"]           ,
                                    'group_note'            => $arrayResult["group_note"]           ,
                                    'group_create_date'     => $arrayResult["group_create_date"]    );
        }

        unset( $objSQL );

        return $arrayAlbum;
    }

    // ***********************************************************************
    // * 関数名     ：スレッド情報取得処理（主にトップページ用）
    // * 返り値     ：成功:SELECT結果、失敗:null
    // ***********************************************************************
    function GetThreadHome( $intStartRecordNum  ,   // 取得開始レコード
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
                    "   thread.thread_id            as  thread_id           ," .
                    "   thread.thread_name          as  thread_name         ," .
                    "   thread.thread_mailaddress   as  thread_mailaddress  ," .
                    "   thread.thread_note          as  thread_note         ," .
                    "   thread.create_date          as  create_date         ," .
                    "   thread.del_flag             as  del_flag            ," .
                    "   IFNULL( MAX( comment.date ), thread.create_date )   as  new_comment_date    ," .
                    "   COUNT( comment.date )       as  comment_count        " .
                    " FROM                          " .
                    "   thread_master   thread      " .
                    "   LEFT  JOIN  bbs_master     comment              " .
                    "       ON  thread.thread_id = comment.thread_id    " .
                    " WHERE 0=0                     " .
                    " AND   thread.del_flag   = 0   " .     // 全体公開
                    " GROUP BY thread.thread_id     " .
                    " ) " .
                    " UNION " .
                    " ( " .
                    " SELECT                                           " .      // 公開レベル設定されている場合の検索結果をUNIONする
                    "   thread.thread_id            as  thread_id           ," .
                    "   thread.thread_name          as  thread_name         ," .
                    "   thread.thread_mailaddress   as  thread_mailaddress  ," .
                    "   thread.thread_note          as  thread_note         ," .
                    "   thread.create_date          as  create_date         ," .
                    "   thread.del_flag             as  del_flag            ," .
                    "   IFNULL( MAX( comment.date ), thread.create_date )   as  new_comment_date    ," .
                    "   COUNT( comment.date )       as  comment_count        " .
                    " FROM                          " .
                    "               thread_master   thread              " .
                    "   LEFT  JOIN  bbs_master     comment              " .
                    "       ON  thread.thread_id = comment.thread_id    " .
                    "   INNER JOIN  thread_user     user                " .
                    "       ON  user.thread_id = thread.thread_id       " .
                    "   INNER JOIN group_users  u_group                 " .
                    "       ON  user.user_or_group_id = u_group.group_id" .
                    "       AND u_group.user_id = " . SQLAid::escapeNum( intval( $arrayUser["user_id"] ) ) .
                    " WHERE 0=0                 " .
                    " AND   thread.del_flag = 1 " .     // 公開レベル設定
                    " GROUP BY thread.thread_id " .
                    " ) " .
                    " ORDER BY              " .
                    "   new_comment_date DESC    " ;

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
            $arrayInfo[] = array(   'thread_id'             => $arrayResult["thread_id"]            ,
                                    'thread_name'           => $arrayResult["thread_name"]          ,
                                    'thread_mailaddress'    => $arrayResult["thread_mailaddress"]   ,
                                    'thread_note'           => $arrayResult["thread_note"]          ,
                                    'create_date'           => $arrayResult["create_date"]          ,
                                    'del_flag'              => $arrayResult["del_flag"]             ,
                                    'new_comment_date'      => $arrayResult["new_comment_date"]     ,
                                    'comment_count'         => $arrayResult["comment_count"]        );
        }

        return $arrayInfo;
    }

    // ***********************************************************************
    // * 関数名     ：指定メールアドレス存在チェック
    // * 返り値     ：成功:SELECT結果、失敗:null
    // ***********************************************************************
    function UsedMailOfUserAndThread(   $strMail        ,   // チェックするアドレス
                                        $strID          ,   // 自スレッドは対象外
                                        &$intRows       ,   // 取得できたレコード数
                                        $intFlag = HEAD_MANAGE_THREAD )
    {
        // ＤＢ接続
        $db = new WrapDB();
        $db->ConnectDB();

        // SELECTする
        $strSQL =   " (         " .
                    " SELECT                                " .     // スレッドで使用されているかチェック
                    "   thread_mailaddress  as  mailaddress " .
                    " FROM                          " .
                    "   thread_master               " .
                    " WHERE 0=0                     " .
                    " AND   thread_mailaddress =    " . SQLAid::escapeStr( $strMail           ) ;

        // 指定スレッドIDがある場合＝スレッド情報変更の場合
        if( HEAD_MANAGE_THREAD == $intFlag && 0 < strlen( $strID ) )
        {
            // 自スレッドは対象外
            $strSQL = $strSQL .
                    " AND   thread_id <>            " . SQLAid::escapeNum( intval( $strID )   ) ;
        }

        $strSQL =   $strSQL .
                    " )         " .
                    " UNION     " .
                    " (         " .
                    " SELECT                                " .     // ユーザ情報で使用されているかどうかチェック
                    "   mail_address_pc     as  mailaddress " .
                    " FROM                      " .
                    "   user                    " .
                    " WHERE 0=0                 " .
                    " AND   mail_address_pc =   " . SQLAid::escapeStr( $strMail ) .
                    " )         " .
                    " UNION     " .
                    " (         " .
                    " SELECT                                " .     // フォトアルバムで使用されているかチェック
                    "   mail_address        as  mailaddress " .
                    " FROM                          " .
                    "   photo_album                 " .
                    " WHERE 0=0                     " .
                    " AND   mail_address        =   " . SQLAid::escapeStr( $strMail           ) ;

        // 指定アルバムIDがある場合＝アルバム情報変更の場合
        if( HEAD_MANAGE_ALBUM == $intFlag && 0 < strlen( $strID ) )
        {
            // 自スレッドは対象外
            $strSQL = $strSQL .
                    " AND   album_id <>             " . SQLAid::escapeNum( intval( $strID )   ) ;
        }

        $strSQL =   $strSQL .
                    " ) " ;

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
            $arrayInfo[] = array(   'mailaddress'   => $arrayResult["mailaddress"]  );
        }

        return $arrayInfo;
    }

    // ***********************************************************************
    // * 関数名     ：ＢＢＳスレッド情報取得処理(１件)
    // * 返り値     ：成功:SELECT結果、失敗:null
    // ***********************************************************************
    function GetThreadOne(  $intThreadID )
    {
        // ＤＢ接続
        $db = new WrapDB();
        $db->ConnectDB();

        // SELECTする
        $strSQL =   " SELECT                 " .
                    "   thread_id           ," .
                    "   thread_name         ," .
                    "   thread_mailaddress  ," .
                    "   thread_note         ," .
                    "   create_date         ," .
                    "   del_flag             " .
                    " FROM                  " .
                    "   thread_master       " .
                    " WHERE 0=0             " .
                    " AND   thread_id =     " . SQLAid::escapeNum( $intThreadID );
        // SQL実行
        $objSQL = $db->QueryDB( $strSQL );

        // 実行結果行数を取得
        $intRows = $db->GetNumRowsDB( $objSQL );

        // 取得したデータを配列に格納
        if( 0 < $intRows )
        {
            // 取得行を配列化
            $arrayResult = $db->FetchArrayDB( $objSQL );

            $arrayAlbum = array(    'thread_id'             => $arrayResult["thread_id"]            ,
                                    'thread_name'           => $arrayResult["thread_name"]          ,
                                    'thread_mailaddress'    => $arrayResult["thread_mailaddress"]   ,
                                    'thread_note'           => $arrayResult["thread_note"]          ,
                                    'create_date'           => $arrayResult["create_date"]          ,
                                    'del_flag'              => $arrayResult["del_flag"]             );
        }
        else
        {
            $arrayAlbum = null;
        }

        unset( $objSQL );

        return $arrayAlbum;
    }

    // ***********************************************************************
    // * 関数名     ：スレッド情報取得処理（更新メール受信設定画面用）
    // * 返り値     ：成功:SELECT結果、失敗:null
    // ***********************************************************************
    function GetThreadMypage(   $strUserID          ,
                                $intStartRecordNum  ,   // 取得開始レコード
                                $intGetRecordCount  ,   // 取得するレコード数
                                &$intRows           ,   // 取得できたレコード数
                                $intFlag = 0        )   // 0:Limitあり、1:Limitなし
    {
        // ＤＢ接続
        $db = new WrapDB();
        $db->ConnectDB();

        // SELECTする
        $strSQL =   " ( " .
                    " SELECT                 " .
                    "   thread.thread_id            as  thread_id           ," .
                    "   thread.thread_name          as  thread_name         ," .
                    "   thread.thread_mailaddress   as  thread_mailaddress  ," .
                    "   thread.thread_note          as  thread_note         ," .
                    "   thread.create_date          as  create_date         ," .
                    "   thread.del_flag             as  del_flag            ," .
                    "   IFNULL( MAX( comment.date ), thread.create_date )   as  new_comment_date    ," .
                    "   COUNT( comment.date )       as  comment_count       ," .
                    "   receive.user_id             as  user_id              " .
                    " FROM                          " .
                    "   thread_master   thread      " .
                    "   LEFT  JOIN  bbs_master     comment              " .
                    "       ON  thread.thread_id = comment.thread_id    " .
                    "   LEFT  JOIN  thread_receive receive              " .
                    "       ON  thread.thread_id = receive.thread_id    " .
                    "       AND receive.user_id = " . SQLAid::escapeNum( intval( $strUserID ) ) .
                    " WHERE 0=0                     " .
                    " AND   thread.del_flag   = 0   " .     // 全体公開
                    " GROUP BY thread.thread_id     " .
                    " ) " .
                    " UNION " .
                    " ( " .
                    " SELECT                                           " .      // 公開レベル設定されている場合の検索結果をUNIONする
                    "   thread.thread_id            as  thread_id           ," .
                    "   thread.thread_name          as  thread_name         ," .
                    "   thread.thread_mailaddress   as  thread_mailaddress  ," .
                    "   thread.thread_note          as  thread_note         ," .
                    "   thread.create_date          as  create_date         ," .
                    "   thread.del_flag             as  del_flag            ," .
                    "   IFNULL( MAX( comment.date ), thread.create_date )   as  new_comment_date    ," .
                    "   COUNT( comment.date )       as  comment_count       ," .
                    "   receive.user_id             as  user_id              " .
                    " FROM                          " .
                    "               thread_master   thread              " .
                    "   LEFT  JOIN  bbs_master     comment              " .
                    "       ON  thread.thread_id = comment.thread_id    " .
                    "   INNER JOIN  thread_user     user                " .
                    "       ON  user.thread_id = thread.thread_id       " .
                    "   INNER JOIN group_users  u_group                 " .
                    "       ON  user.user_or_group_id = u_group.group_id" .
                    "       AND u_group.user_id = " . SQLAid::escapeNum( intval( $strUserID ) ) .
                    "   LEFT  JOIN  thread_receive receive              " .
                    "       ON  thread.thread_id = receive.thread_id    " .
                    "       AND receive.user_id = " . SQLAid::escapeNum( intval( $strUserID ) ) .
                    " WHERE 0=0                 " .
                    " AND   thread.del_flag = 1 " .     // 公開レベル設定
                    " GROUP BY thread.thread_id " .
                    " ) " .
                    " ORDER BY              " .
                    "   new_comment_date DESC    " ;

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
            $arrayInfo[] = array(   'thread_id'             => $arrayResult["thread_id"]            ,
                                    'thread_name'           => $arrayResult["thread_name"]          ,
                                    'thread_mailaddress'    => $arrayResult["thread_mailaddress"]   ,
                                    'thread_note'           => $arrayResult["thread_note"]          ,
                                    'create_date'           => $arrayResult["create_date"]          ,
                                    'del_flag'              => $arrayResult["del_flag"]             ,
                                    'new_comment_date'      => $arrayResult["new_comment_date"]     ,
                                    'comment_count'         => $arrayResult["comment_count"]        ,
                                    'user_id'               => $arrayResult["user_id"]              );
        }

        return $arrayInfo;
    }

    // ***********************************************************************
    // * 関数名     ：スレッド閲覧許可ユーザチェック
    // * 返り値     ：許可ユーザ:true、許可していないユーザ:false
    // ***********************************************************************
    function CheckAuthThreadUser(   $strSessionID   ,
                                    $strThreadID    ,
                                    &$intRows       ,   // 取得できたレコード数
                                    $intDebugFlag = 0 )
    {
        $intRetCode = false;

        // ＤＢ接続
        $db = new WrapDB();
        $db->ConnectDB();

        // スレッド情報とユーザ一覧取得
        // SELECTする
        $strSQL =   " SELECT                                " .
                    "   thread.del_flag     as  del_flag   ," .
                    "   ses.session_id      as  session_id  " .
                    " FROM                                                  " .
                    "               thread_master   thread                  " .
                    "   LEFT  JOIN  thread_user     th_user                 " .
                    "       ON  th_user.thread_id = thread.thread_id        " .
                    "   LEFT  JOIN user_group  u_group                      " .
                    "       ON  th_user.user_or_group_id = u_group.group_id " .
                    "       AND u_group.del_flag = 0                        " .
                    "   LEFT  JOIN  group_users     group_u                 " .
                    "       ON  u_group.group_id = group_u.group_id         " .
                    "   LEFT  JOIN  user     us                             " .
                    "       ON  group_u.user_id = us.user_id                " .
                    "       AND us.del_flag = 0                             " .
                    "   LEFT  JOIN  session     ses                         " .
                    "       ON  us.login_id = ses.login_id                  " .
                    "       AND ses.session_id = " . SQLAid::escapeStr( $strSessionID ) .
                    " WHERE 0=0                 " .
                    " AND   thread.thread_id =  " . SQLAid::escapeNum( intval( $strThreadID ) ) .
                    " GROUP BY session_id " ;

        // SQL実行
        $objSQL = $db->QueryDB( $strSQL );

        // 実行結果行数を取得
        $intRows = $db->GetNumRowsDB( $objSQL );

        // 取得行数ループ
        for( $intCount = 0; $intCount < $intRows; $intCount++ )
        {
            $arrayResult = $db->FetchArrayDB( $objSQL );

            // 全体公開なら無条件にOK
            if( 0 == strcmp( "0", $arrayResult["del_flag"] ) )
            {
                $intRetCode = true;
                break;
            }

            // 非公開なら無条件にNG
            if( 0 == strcmp( "2", $arrayResult["del_flag"] ) )
            {
                $intRetCode = false;
                break;
            }

            // セッションIDがあるかどうかチェックする。あればOK
            if( 0 == strcmp( $strSessionID, $arrayResult["session_id"] ) )
            {
                $intRetCode = true;
                break;
            }
        }

        return $intRetCode;
    }

}
