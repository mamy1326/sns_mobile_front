<?php
/* ================================================================================
 * ファイル名   ：bbs_input.php
 * タイトル     ：BBS入力・編集画面
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

function main( $strSessionID, $arrayLogin )
{
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

    // 新規の場合
    if( 0 == strlen( $strBbsDate ) )
    {
        $strShowMessage  = "以下の内容でBBSに投稿します。<br>よろしければ「登録実行」ボタンを押してください。";
        $strButtonName   = "登録実行";
    }
    // 変更の場合
    else
    {
        $strShowMessage  = "以下の内容でBBSを更新します。<br>よろしければ「更新実行」ボタンを押してください。";
        $strButtonName   = "更新実行";
    }

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
            <div style="background-color:<?= COLOR_PAGE_TITLE_BGCOLOR ?>;width:100%;text-align:center;"><span style="color:white;">BBS 入力・編集確認</span></div><br />
            <div style="background-color:black;text-align:left;"><span style="color:white;"><?= $strShowMessage ?></span></div><br />
        </div>
        <div style="text-align:left;">
            <form action="./bbs_complete.php<?= DOCOMO_GUID ?>" method="GET" name="bbs_confirm">
                <span style="color:white;">タイトル</span><br />
                <div style="background-color:white;"><span style="color:black;"><?= DataConvert::MetatagEncode( $strTitle ); ?></span></div><br />
                <span style="color:white;">本文</span><br />
                <div style="background-color:white;"><span style="color:black;"><?= DataConvert::ReturnCodeToBR( DataConvert::MetatagEncode( $strBody ) ); ?></span></div><br />
                <input type="hidden" name="s_id"        value="<?= $strSessionID ?>">
                <input type="hidden" name="date"        value="<?= $strBbsDate ?>">
                <input type="hidden" name="login_id"    value="<?= $strLoginID ?>">
                <input type="hidden" name="title"       value="<?= DataConvert::MetatagEncode( $strTitle ); ?>">
                <input type="hidden" name="body"        value="<?= DataConvert::MetatagEncode( $strBody ); ?>">
                <input type="hidden" name="thread_id"   value="<?= $strThreadID ?>">
                <input type="hidden" name="thread_name" value="<?= DataConvert::MetatagEncode( $strThreadName ); ?>">
                <?= DOCOMO_GUID_HIDDEN ?>
                <div style="text-align:center;"><span style="color:white;">&#63884;</span><input type="submit" name="submit" value="<?= $strButtonName ?>" accesskey="6"></div>
            </form>
            <form action="./bbs_input.php<?= DOCOMO_GUID ?>" method="GET" name="bbs_confirm">
                <input type="hidden" name="s_id"        value="<?= $strSessionID ?>">
                <input type="hidden" name="date"        value="<?= $strBbsDate ?>">
                <input type="hidden" name="login_id"    value="<?= $strLoginID ?>">
                <input type="hidden" name="title"       value="<?= DataConvert::MetatagEncode( $strTitle ); ?>">
                <input type="hidden" name="body"        value="<?= DataConvert::MetatagEncode( $strBody ); ?>">
                <input type="hidden" name="thread_id"   value="<?= $strThreadID ?>">
                <input type="hidden" name="thread_name" value="<?= DataConvert::MetatagEncode( $strThreadName ); ?>">
                <?= DOCOMO_GUID_HIDDEN ?>
                <input type="hidden" name="confirm_return"  value="1">
                <div style="text-align:center;"><span style="color:white;">&#63882;</span><input type="submit" name="submit" value="戻る" accesskey="4"></div>
            </form>
        </div>
        <br />
    <?
}
