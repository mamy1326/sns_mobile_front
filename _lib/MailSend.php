<?php
/* ================================================================================
 * ファイル名   ：MailSend.php
 * タイトル     ：メール送信用クラス
 * 作成者       ：間宮 直樹
 * 作成日       ：2008/07/28
 * 内容         ：メール送信を実施する。
 * 更新履歴*******************************************************************
 * 【変更日】  【変更者】      【変更箇所】        【変更理由と変更内容】
 *  2008/07/28  間宮 直樹       全体                新規作成
 * ================================================================================*/

$strPath = substr( $_SERVER['SCRIPT_FILENAME'], 0, strpos( $_SERVER['SCRIPT_FILENAME'], 'public_html' ) - 1 + strlen( "/" . 'public_html' . "/" ) );
$strPath = $strPath . "admin/";

// includeするライブラリ・コンフィグファイルのパスを定義する
define( 'LIB_DIR'   , $strPath . '_lib/');
define( 'CONFIG_DIR', $strPath . '_config/');

include_once( LIB_DIR    . 'WrapDB.php' );              // ＤＢ用関数群
include_once( LIB_DIR    . 'SQLAid.php' );              // ＳＱＬ条件文関数群
include_once( LIB_DIR    . 'UserGroupDB.php' );         // ユーザグループ情報取得・更新関数群
include_once( LIB_DIR    . 'UserDB.php' );              // ユーザ情報取得・更新関数群
include_once( LIB_DIR    . 'MypageDB.php' );            // マイページ情報
include_once( CONFIG_DIR . 'db_conf.php' );             // データベース定義ファイル
include_once( CONFIG_DIR . 'manage_conf.php' );         // 管理画面基本情報定義ファイル

class MailSend
{
    // ***********************************************************************
    // * 関数名     ：メール送信
    // * 返り値     ：送信成功:true、送信失敗:false
    // ***********************************************************************
    function ManageMailSend(    $intSendStatus = 0  ,   // 送信種別(0:管理画面通常、1:管理本部通信通常、2:管理本部通信未閲覧者、3:フォトアルバム、4:BBS)
                                $strTitle           ,   // タイトル
                                $strMailBody        ,   // 本文
                                $strMailFrom        ,   // 差出人
                                $arrayMailTo        ,   // 送信先
                                $strMailLabel = "mail_address_pc" )
    {
        mb_language("ja");
        mb_internal_encoding("SJIS");   // メールサブジェクト、本文で使用する文字コードを、PHPソースコードに合わせる

        // ヘッダ情報に差出人などを記述
        $strHeader =    "From: " . $strMailFrom . "¥n".
                        "Reply-To: ". $strMailFrom ."¥n".
                        "Content-Type: text/plain;charset=iso-2022-jp¥n".
                        "X-Mailer: PHP/".phpversion();

        // 指定されたアドレス分、メールを送信
        $arrayResult = array();
        for( $intCount = 0; $intCount < count( $arrayMailTo ); $intCount++ )
        {
            // メール送信処理
            $arrayResult[] = mb_send_mail( $arrayMailTo[$intCount][$strMailLabel], $strTitle, $strMailBody, $strHeader );
        }

        return $arrayResult;
    }

    // ***********************************************************************
    // * 関数名     ：送信メール情報取得処理
    // * 機能概要   ：送信メール情報をすべて取得する。メールＩＤとユーザＩＤでソート
    // * 返り値     ：成功:SELECT結果、失敗:null
    // ***********************************************************************
    function GetSendMailAll(    &$intRows   )   // 取得できたレコード数
    {
        // ＤＢ接続
        $db = new WrapDB();
        $db->ConnectDB();

        // SELECTする
        $strSQL =   " SELECT                                    " .
                    "      mail.mail_id         as mail_id      " .
                    "     ,mail.send_date       as send_date    " .
                    "     ,mail.send_flag       as send_flag    " .
                    "     ,mail.mail_from       as mail_from    " .
                    "     ,mail.subject         as subject      " .
                    "     ,mail.body            as body         " .
                    "     ,list.mail_address    as mail_address " .
                    "     ,list.result          as result       " .
                    "     ,user.user_name       as user_name    " .
                    " FROM " .
                    "     manage_mail   mail                    " .
                    "     INNER JOIN mail_send_list list        " .
                    "         ON mail.mail_id = list.mail_id    " .
                    "     LEFT  JOIN user user                  " .
                    "         ON list.user_id = user.user_id    " .
                    " ORDER BY " .
                    "     mail.mail_id DESC, " .
                    "     list.user_id ASC   " ;

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
            $arrayUser[] = array(   'mail_id'       => $arrayResult["mail_id"]      ,
                                    'send_date'     => $arrayResult["send_date"]    ,
                                    'send_flag'     => $arrayResult["send_flag"]    ,
                                    'mail_from'     => $arrayResult["mail_from"]    ,
                                    'subject'       => $arrayResult["subject"]      ,
                                    'body'          => $arrayResult["body"]         ,
                                    'mail_address'  => $arrayResult["mail_address"] ,
                                    'result'        => $arrayResult["result"]       ,
                                    'user_name'     => $arrayResult["user_name"]    );
        }

        return $arrayUser;
    }


    // ***********************************************************************
    // * 関数名     ：メール送信結果のDB書き込み
    // * 返り値     ：すべて送信成功:true、送信失敗あり:false
    // ***********************************************************************
    function SendMailToDB(  $intSendStatus = 0  ,  // 送信種別(0:管理画面通常、1:管理本部通信通常、2:管理本部通信未閲覧者、3:フォトアルバム、4:BBS)
                            $strMailSubject     ,  // タイトル
                            $strMailBody        ,  // 本文
                            $strMailFrom        ,  // 差出人
                            $arrayMailToAddress ,  // 送信先
                            $arraySendResult    )  // 送信結果
    {
        $strErrorMessage = "";

        // ＤＢ接続
        $db = new WrapDB();
        $db->ConnectDB();

        //トランザクション開始
        $db->AutoCommitOffDB();

        // **************************
        // メール本文情報の書き込み
        // **************************
        // INSERTする
        $strSQL =   " INSERT INTO manage_mail   " .
                    " (                         " .
                    "   send_date              ," .
                    "   send_flag              ," .
                    "   mail_from              ," .
                    "   subject                ," .
                    "   body                    " .
                    " )                      " .
                    " VALUES                 " .
                    " (                      " .
                    "    sysdate()                                       , " .
                    "    " . SQLAid::escapeNum( $intSendStatus      ) . ", " .
                    "    " . SQLAid::escapeStr( $strMailFrom        ) . ", " .
                    "    " . SQLAid::escapeStr( $strMailSubject     ) . ", " .
                    "    " . SQLAid::escapeStr( $strMailBody        ) .
                    ")                   " ;

        // SQL実行
        $objMail = $db->QueryDB( $strSQL );

        // 失敗したらROLLBACKしてエラーリターン
        if( DB_OK != $objMail )
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

        // **************************
        // メール送信履歴の書き込み
        // **************************
        if( 0 < count( $arrayMailToAddress ) )
        {
            // 書き込んだメールID取得
            $strSelectSQL = " SELECT MAX( mail_id ) as mail_id FROM manage_mail ";

            $objMail = $db->QueryDB( $strSelectSQL );

            $arrayResult = $db->FetchArrayDB( $objMail );

            $intMailID = intval( $arrayResult["mail_id"] );

            // 送信した分だけ書き込み
            $strQueryValue = "";
            for( $intCount = 0; $intCount < count( $arrayMailToAddress ); $intCount++ )
            {
                if( 0 < strlen( $strQueryValue ) )
                {
                    $strQueryValue = $strQueryValue . ", ";
                }

                if( 0 < strlen( $arrayMailToAddress[$intCount]["user_id"] ) )
                {
                    $intUserID = intval( $arrayMailToAddress[$intCount]["user_id"] );
                }
                else
                {
                    $intUserID = 99999999;
                }

                $strQueryValue =    $strQueryValue .
                                    " ( " .
                                    SQLAid::escapeNum( $intMailID                                           ) . ", " .
                                    SQLAid::escapeNum( $intUserID                                           ) . ", " .
                                    SQLAid::escapeStr( $arrayMailToAddress[$intCount]["mail_address_pc"]    ) . ", " .
                                    SQLAid::escapeNum( $arraySendResult[$intCount]                          ) .
                                    " ) ";
            }

            // INSERTする
            $strSQL =   " INSERT INTO mail_send_list    " .
                        " (                             " .
                        "   mail_id                    ," .
                        "   user_id                    ," .
                        "   mail_address               ," .
                        "   result                      " .
                        " )                      " .
                        " VALUES                 " .
                        $strQueryValue;

            // SQL実行
            $objMailUsers = $db->QueryDB( $strSQL );

            // 失敗したらROLLBACKしてエラーリターン
            if( DB_OK != $objMailUsers )
            {
                //失敗
                $db->TransactDB( db_strDB_ROLLBACK );
                $db->CloseDB();
                $strErrorMessage = "メール送信結果情報登録エラー";
                return $strErrorMessage;
            }
        }
        //成功
        $db->TransactDB( db_strDB_COMMIT );
        $db->CloseDB();

        return $strErrorMessage;
    }

    // ***********************************************************************
    // * 関数名     ：メールアドレス取得（重複しない）
    // * 返り値     ：送信成功:true、送信失敗:false
    // ***********************************************************************
    function GetAddressOfUserAndGroup(   $arraySelectedGroup ,  // 送信するユーザグループ一覧
                                         $arraySelectedUser  ,  // 送信するユーザ一覧
                                         $arrayMailTo        ,  // 任意のアドレス一覧
                                         &$intMailRows       )  // 総アドレス数
    {
        $arrayAllAddress = array();

        // 選択グループがあれば、メールアドレス一覧を取得する
        $arrayGroupMailAddress = null;
        if( true == is_array( $arraySelectedGroup ) )
        {
            // 最初が空文字の場合は処理不要
            if( 0 < strlen( $arraySelectedGroup[0]["mail_address_pc"] ) )
            {
                $arrayGroupMailAddress = UserGroupDB::GetUserOfGroupID( $arraySelectedGroup, $intRowsGroup );
            }
            else
            {
                $arrayGroupMailAddress = null;
            }
        }

        // 選択ユーザがあれば、メールアドレス一覧を取得する
        $arrayUserMailAddress = null;
        if( true == is_array( $arraySelectedUser ) )
        {
            // 最初が空文字の場合は処理不要
            if( 0 < strlen( $arraySelectedUser[0]["mail_address_pc"] ) )
            {
                $arrayUserMailAddress = UserDB::GetUserOfID( $arraySelectedUser, $intRowsUser );
            }
            else
            {
                $arrayUserMailAddress = null;
            }
        }

        // 任意入力アドレス
        // 最初が空文字の場合は処理不要
        if( 0 == strlen( $arrayMailTo[0] ) )
        {
            $arrayMailTo = null;
        }

        // グループメール配列を、メールアドレス一覧配列に１つずつ入れていく
        for( $intCountGroup = 0; $intCountGroup < count( $arrayGroupMailAddress ); $intCountGroup++ )
        {
            // メールアドレス一覧配列に、グループメールの要素ｉ番目のアドレスが無い場合
            if( true != in_array( $arrayGroupMailAddress[$intCountGroup]["mail_address_pc"], $arrayAllAddress ) )
            {
                // 配列に追加
                $arrayAllAddress[] = array( "mail_address_pc"   => $arrayGroupMailAddress[$intCountGroup]["mail_address_pc"]    ,
                                            "user_id"           => $arrayGroupMailAddress[$intCountGroup]["user_id"]            );
            }
        }
        unset($arrayGroupMailAddress);

        // ユーザメール配列を、メールアドレス一覧配列に１つずつ入れていく
        for( $intCountUser = 0; $intCountUser < count( $arrayUserMailAddress ); $intCountUser++ )
        {
            // メールアドレス一覧配列に、ユーザメールの要素ｉ番目のアドレスが無い場合
            if( true != in_array( $arrayUserMailAddress[$intCountUser]["mail_address_pc"], $arrayAllAddress ) )
            {
                // 配列に追加
                $arrayAllAddress[] = array( "mail_address_pc"   => $arrayUserMailAddress[$intCountUser]["mail_address_pc"]  ,
                                            "user_id"           => $arrayUserMailAddress[$intCountUser]["user_id"]          );
            }
        }
        unset($arrayUserMailAddress);

        // 入力メール配列を、メールアドレス一覧配列に１つずつ入れていく
        for( $intCount = 0; $intCount < count( $arrayMailTo ); $intCount++ )
        {
            // メールアドレス一覧配列に、入力メールの要素ｉ番目のアドレスが無い場合
            if( true != in_array( $arrayMailTo[$intCount], $arrayAllAddress ) )
            {
                // 配列に追加
                $arrayAllAddress[] = array( "mail_address_pc"   => $arrayMailTo[$intCount]  ,
                                            "user_id"           => ""                       );
            }
        }

        $intMailRows = count( $arrayAllAddress );

        return $arrayAllAddress;

    }

    // ***********************************************************************
    // * 関数名     ：更新メール送信処理
    // * 返り値     ：送信成功:true、送信失敗:false
    // ***********************************************************************
    function UpdateMailSend(    $intProcFlag    ,   // 処理フラグ(0:フォトアルバム更新、1:スレッド更新)
                                $intID          ,   // アルバムID、またはスレッドID
                                $strName        )   // アルバム、またはスレッド名
    {
        // 対象のIDのフォトアルバムまたはスレッドの更新メール受信者氏名・ログインID・ユーザIDを取得する
        $arrayUserList = MypageDB::GetUpdateMailUserList(   $intProcFlag,
                                                            $intID      ,
                                                            $intRows    );

        // 処理フラグ判定
        // (フォトアルバム)
        if( HEAD_MANAGE_ALBUM == $intProcFlag )
        {
            // メールサブジェクト作成
            $strSubject = "フォトアルバム更新のお知らせ";

            // メール本文のテンプレート作成
            $strBodyTemp =  "お知らせです。¥n"                             .
                            "%s さん、お疲れさまです。¥n"                           .
                            "以下のフォトアルバムに新たに画像が追加されました!!¥n"  .
                            "---------------¥n"                                     .
                            "[%s]¥n"                                                .
                            "→%s¥n"                                                .
                            "---------------¥n"                                     .
                            "URLからフォトアルバムをご覧下さい。¥n"                 ;

            $strPath    = PATH_MOBILE_ALBUM_DETAIL;
            $strID_Name = "album_id";
        }
        // (スレッド)
        else
        {
            // メールサブジェクト作成
            $strSubject = "BBSスレッド投稿のお知らせ";

            // メール本文のテンプレート作成
            $strBodyTemp =  "お知らせです。¥n"                     .
                            "%s さん、お疲れさまです。¥n"                   .
                            "以下のBBSスレッドに新規投稿がありました!!¥n"   .
                            "---------------¥n"                             .
                            "[%s]¥n"                                        .
                            "→%s¥n"                                        .
                            "---------------¥n"                             .
                            "URLからBBSスレッドをご覧下さい。¥n"            ;

            $strPath    = PATH_MOBILE_THREAD_DETAIL;
            $strID_Name = "thread_id";
        }

        // 対象者分ループ
        $strLoginID_Work = "";
        for( $intCount = 0; $intCount < $intRows; $intCount++ )
        {
            // 対象者が公開ユーザとして登録されているかチェックする
            $intRetCode = MailSend::CheckAuthUser( $intProcFlag, $arrayUserList[$intCount]["user_id"], $intID );
            if( true != $intRetCode )
            {
                // 公開されていない場合、メール受信希望設定していても送信しない
                continue;
            }

            // 対象者の受信用メールアドレス取得
            $arrayMailList = MypageDB::GetMailListOfUser( $arrayUserList[$intCount]["user_id"] );
            if( NULL == $arrayMailList )
            {
                continue;
            }

            // URL作成してappendする。URLにはログインIDと自動ログインフラグを付ける⇒認証時に通るようにする
            $strUrl =   "http://" . $_SERVER["SERVER_NAME"] . $strPath .
                        DOCOMO_GUID .
                        "&" . $strID_Name . "=" . $intID .
                        "&login_id=" . $arrayUserList[$intCount]["login_id"] .
                        "&" . md5( PARAM_MAIL_DIRECT_LOGIN ) . "=1" ;

            // 本文作成
            $strBody = sprintf( $strBodyTemp, $arrayUserList[$intCount]["user_name"], $strName, $strUrl );

            // メール送信関数呼び出し
            MailSend::ManageMailSend(   0                   ,   // 送信種別(0:管理画面通常、1:管理本部通信通常、2:管理本部通信未閲覧者、3:フォトアルバム、4:BBS)
                                        $strSubject         ,   // タイトル
                                        $strBody            ,   // 本文
                                        MAIL_FROM_STANDARD  ,   // 差出人
                                        $arrayMailList      ,   // 送信先
                                        "receive_mail"      );  // 配列内メールアドレスラベル

            unset( $arrayMailList );
        }
    }

    // ***********************************************************************
    // * 関数名     ：指定ユーザIDが公開ユーザかどうかチェックする
    // * 返り値     ：成功:SELECT結果、失敗:null
    // ***********************************************************************
    function CheckAuthUser( $intProcFlag        ,
                            $strUserID          ,
                            $intID              )
    {
        // ＤＢ接続
        $db = new WrapDB();
        $db->ConnectDB();

        // 処理フラグ判定
        // (フォトアルバム)
        if( HEAD_MANAGE_ALBUM == $intProcFlag )
        {
            // SELECTする
            $strSQL =   " (                                 " .
                        " SELECT                            " .
                        "   main.del_flag   as  del_flag    " .
                        " FROM                              " .
                        "   photo_album   main              " .
                        " WHERE 0=0                         " .
                        " AND   main.album_id   =           " . SQLAid::escapeNum( $intID ) .
                        " AND   main.del_flag   = 0         " .     // 全体公開
                        " GROUP BY main.album_id            " .
                        " )     " .
                        " UNION " .
                        " (     " .
                        " SELECT                            " .      // 公開レベル設定されている場合の検索結果をUNIONする
                        "   main.del_flag   as  del_flag    " .
                        " FROM                              " .
                        "               photo_album         main                " .
                        "   INNER JOIN  photo_album_user    user                " .
                        "       ON  user.album_id = main.album_id               " .
                        "   INNER JOIN group_users  u_group                     " .
                        "       ON  user.user_or_group_id = u_group.group_id    " .
                        "       AND u_group.user_id = " . SQLAid::escapeNum( intval( $strUserID ) ) .
                        " WHERE 0=0                 " .
                        " AND   main.album_id   =   " . SQLAid::escapeNum( $intID ) .
                        " AND   main.del_flag = 1   " .     // 公開レベル設定
                        " GROUP BY main.album_id    " .
                        " ) " ;
        }
        // (スレッド)
        else
        {
            // SELECTする
            $strSQL =   " (                                 " .
                        " SELECT                            " .
                        "   main.del_flag   as  del_flag    " .
                        " FROM                              " .
                        "   thread_master   main            " .
                        " WHERE 0=0                         " .
                        " AND   main.thread_id  = " . SQLAid::escapeNum( $intID ) .
                        " AND   main.del_flag   = 0         " .     // 全体公開
                        " GROUP BY main.thread_id           " .
                        " ) " .
                        " UNION " .
                        " ( " .
                        " SELECT                            " .      // 公開レベル設定されている場合の検索結果をUNIONする
                        "   main.del_flag   as  del_flag    " .
                        " FROM                              " .
                        "               thread_master   main                    " .
                        "   INNER JOIN  thread_user     user                    " .
                        "       ON  user.thread_id = main.thread_id             " .
                        "   INNER JOIN group_users  u_group                     " .
                        "       ON  user.user_or_group_id = u_group.group_id    " .
                        "       AND u_group.user_id = " . SQLAid::escapeNum( intval( $strUserID ) ) .
                        " WHERE 0=0                 " .
                        " AND   main.thread_id  =   " . SQLAid::escapeNum( $intID ) .
                        " AND   main.del_flag = 1   " .     // 公開レベル設定
                        " GROUP BY main.thread_id   " .
                        " ) " ;
        }

        // SQL実行
        $objInfo = $db->QueryDB( $strSQL );

        // 実行結果行数を取得
        $intRows = $db->GetNumRowsDB( $objInfo );
        if( 0 < $intRows )
        {
            $intRetCode = true;
        }
        else
        {
            $intRetCode = false;
        }

        return $intRetCode;
    }

    // ***********************************************************************
    // * 関数名     ：メール送信(PEAR版)
    // * 機能概要   ：メールの送信を行う。
    // * 引数       ：IN ：?@送信先
    // *              IN ：?Aメール本文
    // *              IN ：?Bタイトル
    // *              IN ：?Cメール送信者アドレス
    // * 返り値     ：送信成功:true、送信失敗:false
    // ***********************************************************************
    function message_send( $strMailTo, $strBody, $strSubject, $strTransmitMailAddress )
    {
        //文字コードの変更
        $strSubject = mb_encode_mimeheader( $strSubject, "JIS", "SJIS" );   // ヘッダ（サブジェクト）の文字コード指定・変換
        $strBody    = mb_convert_encoding(  $strBody   , "JIS", "SJIS" );   // BODY（本文）の文字コード指定・変換

        $mail_server = array( 'host'=>'192.168.2.10', 'port'=>'25' );       // メールサーバのホスト設定

        // ヘッダ部の構成
        $Headers = array();
        $Headers['To']                          = $strMailTo;                               // 送信先
        $Headers['From']                        = $strTransmitMailAddress;                  // 送信元
        $Headers['Reply-to']                    = 'mamy1326@gmail.com';              // メーラーで返信時の戻り先
        $Headers['Return-Path']                 = 'mamy1326@gmail.com';              // Unknown User の戻り先
        $Headers['X-Mailer']                    = "Mail by Synerzy";                        // 普通はPHPのバージョンを書く？
        $Headers['Mime-Version']                = "1.0";
        $Headers['Content-Type']                = "text/plain; charset=¥"ISO-2022-JP¥"";    // HTMLかTEXTか。加えてcharsetも指定。
        $Headers['Content-Transfer-Encoding']   = "7bit";
        $Headers['Precedence']                  = "bulk";
        $Headers['Message-Id']                  = str_replace( ' ', '', microtime() );      // 現在時刻からメッセージIDを作成(半角スペースは削除)
        $Headers['Subject']                     = $strSubject;                              // サブジェクト
        $Headers['Date']                        = date( "D, j M Y G:i:s +0900");            // 送信日時

        // PHP標準のメールライブラリをインクルード(factory、send、などの入った標準ライブラリ)
        include_once 'Mail.php';

        // メールオブジェクト作成(SMTPサーバ認識)
        $mail_obj =& Mail::factory("smtp", $mail_server);

        // メール送信
        $res = $mail_obj->send( $strMailTo, $Headers, $strBody );


        if( PEAR::isError( $res ) )
        {
            //メール送信失敗時の処理を書く
            return FALSE;
        }
        else
        {
            //送信成功時の処理を書く
            return TRUE;
        }
    }
}
?>
