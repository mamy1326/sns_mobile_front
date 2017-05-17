<?php
/* ================================================================================
 * ファイル名   ：info_complete.php
 * タイトル     ：【管理】管理本部通信更新完了ページ
 * 作成者       ：間宮 直樹
 * 作成日       ：2008/08/17
 * 内容         ：【管理】管理本部通信更新を実施し、完了画面を表示する。
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
include_once( LIB_DIR    . 'BbsDB.php' );               // Bbs情報
include_once( LIB_DIR    . 'UsersiteLayout.php' );      // ヘッダ・フッタ表示
include_once( LIB_DIR    . 'AccessLog.php' );           // アクセスログ関数群
include_once( LIB_DIR    . 'StdFunc.php' );             // 標準関数群
include_once( CONFIG_DIR . 'title_conf.php' );          // ページタイトル定義ファイル
include_once( CONFIG_DIR . 'manage_conf.php' );         // 管理画面基本情報定義ファイル

$strSessionID = $_GET["s_id"];  // セッションID

// ヘッダ表示
$arrayLogin = UsersiteLayout::PrintHeaderMobileExt( "bgcolor=\"#000000\" text=\"#ffffff\" link=\"#ffffff\" VLINK=\"#ffffff\" ALINK=\"#000000\"" );
main( $strSessionID, $arrayLogin );
UsersiteLayout::PrintFooterMobileExt( $strSessionID );

/* ************************************************************
 * 関数名       ：main()
 * ************************************************************/
function main( $strSessionID, $arrayLogin )
{
    // パラメータ取得
    $strThreadID    = $_GET["thread_id"];
    $strThreadName  = $_GET["thread_name"];
    $strBbsDate     = $_GET["date"];
    $strLoginID     = $_GET["login_id"];
    $strManage      = $_GET["manage_auth"];
    $strTitle       = $_GET["title"];
    $strBody        = $_GET["body"];

    // 自動エンコードされる場合
    if( true == get_magic_quotes_gpc() )
    {
        // デコード(余計な\マークを除去)
        $strTitle       = stripslashes( $strTitle );
        $strBody        = stripslashes( $strBody );
        $strThreadName  = stripslashes( $strThreadName );
    }

    // 投稿者メールアドレス取得
    $arrayUser =    UserDB::GetLoginUserOfSession(  $strSessionID   ,
                                                    &$intRows       );
    if( 0 < strlen( $arrayUser["bbs_write_mailaddress"] ) )
    {
        $arrayBBS_Mail = split( ",", $arrayUser["bbs_write_mailaddress"] );
        $strMail = $arrayBBS_Mail[0];
    }
    elseif( 0 < strlen( $arrayUser["mail_address_pc"] ) )
    {
        $strMail = $arrayUser["mail_address_pc"];
    }
    else
    {
        $strMail = $arrayUser["mail_address_mobile"];
    }

    // ***************************************************************************************
    // 更新情報をＤＢへアップデートする
    // ***************************************************************************************

    $intRetCode  = 0;
    // 新規登録の場合
    if( 0 == strlen( $strBbsDate ) )
    {
        // 登録処理
        BbsDB::InsertBbs(   $strThreadID    ,
                            $strBbsDate     ,
                            $strTitle       ,
                            $strMail        ,
                            $strBody        ,
                            ""              ,
                            $strThreadName  );
    }
    else
    {
        // 更新処理
        BbsDB::UpdateBbs(   $strThreadID    ,
                            $strBbsDate     ,   //
                            $strLoginID     ,   //
                            $strTitle       ,   //
                            $strBody        );  //
    }

    $strMessage = "BBSを更新しました。";

    ?>
    <!--タイトル部分-->
    <STYLE type="text/css">
    <!--
    A:link                      /* リンク */
    {
        color: #00CED1;
        background-color: #000000;
    }
    A:visited                   /* 既に見たリンク */
    {
        color: #FA8072;
        background-color: #000000;
    }
    A:active                    /* クリック時のリンク */
    {
        color: #ffffff;
        background-color: #000000;
    }
    A:hover                     /* カーソルが上にある時のリンク */
    {
        color: #000000;
        background-color: #ffffff;
    }
    -->
    </STYLE>
    <div style="background-color:#000000;<?= StdFunc::GetFontSizeOfCarrier( FONT_SIZE_ALL ) ?>">
        <div style="text-align:center;">
            <div style="background-color:<?= COLOR_PAGE_TITLE_BGCOLOR ?>;width:100%;text-align:center;"><span style="color:white;">BBS 入力・編集完了</span></div><br />
            <div style="background-color:black;text-align:left;"><span style="color:white;"><?= $strMessage ?></span></div><br />
        </div>
        <div style="text-align:center;"><br /><br />
            <span style="color:white;">&#63882;[<a href="./pop_db.php<?= $arrayLogin["query_param"] . "&thread_id=" . $strThreadID ?>" accesskey="4">BBSへ戻る</a>]</span><br />
            <span style="color:white;">&#63888;[<a href="/admin/m/top.php<?= $arrayLogin["query_param"] ?>" accesskey="0">トップへ</a>]</span><br /><br />
        </div><br />
    </div>
    <?
}
