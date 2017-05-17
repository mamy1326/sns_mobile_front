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
    $strBbsDate     = $_GET["date"];
    $strLoginID     = $_GET["login_id"];
    $strManage      = $_GET["manage_auth"];

    $strNewFlag     = $_GET["new_id"];

    // 確認から戻る場合
    $strReturnFlag  = $_GET["confirm_return"];

    $strOpenYY      = "";
    $strOpenMM      = "";
    $strOpenDD      = "";
    $strInfoTitle   = "";
    $strInfoBody    = "";
    $strCreateDate  = "";
    $strDelFlag     = "0";

    // 編集の場合のみ情報取得
    // 既存で戻りではない場合はＤＢから
    if( 0 == strlen( $strNewFlag ) && 0 == strlen( $strReturnFlag ) )
    {
        // ユーザエージェント取得(UA, OS, オリジナル)
        $arrayUA_and_OS = AccessLog::GetUAandOS();

        // OSがある場合はPC
        if( 0 < strlen( $arrayUA_and_OS[1] ) )
        {
            $intPc_or_Mobile = 0;   // PC
        }
        else
        {
            $intPc_or_Mobile = 1;   // Mobile
        }

        // BBS情報を取得する
        $arrayBbs  =    BbsDB::GetBbsOne(   $strThreadID    ,
                                            $strBbsDate     ,   // 投稿年月日時分秒
                                            $strLoginID     ,   // 投稿者ログインID
                                            $intRows        );  // 取得できたレコード数

        $strTitle       = $arrayBbs["title"];
        $strBody        = $arrayBbs["body"];
        $strThreadName  = urldecode( $_GET["thread_name"] );
    }
    // 戻りの場合はパラメータから
    else
    {
        $strTitle       = $_GET["title"];
        $strBody        = $_GET["body"];
        $strThreadName  = $_GET["thread_name"];

        // 自動エンコードされる場合
        if( true == get_magic_quotes_gpc() )
        {
            // デコード(余計な\マークを除去)
            $strTitle       = stripslashes( $strTitle );
            $strBody        = stripslashes( $strBody );
            $strThreadName  = stripslashes( $strThreadName );
        }
    }

    //キャリア取得
    $intCarrier       = StdFunc::GetAgentNumber();

    ?>
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
            <div style="background-color:<?= COLOR_PAGE_TITLE_BGCOLOR ?>;width:100%;text-align:center;"><span style="color:white;">BBS 入力・編集</span></div><br />
        </div>
        <div style="text-align:left;">
            <form action="./bbs_confirm.php<?= DOCOMO_GUID ?>" method="GET" name="bbs_input">
                <span style="color:white;">タイトル</span><br />
                　<input name="title"   type="text" size="20" maxlength="20" value="<?= DataConvert::MetatagEncode( $strTitle ); ?>" /><br />
                <span style="color:white;">本文</span><br />
                　<textarea name="body" cols="25" rows="5"><?= $strBody ?></textarea><br />
                <div style="text-align:center;"><span style="color:white;">&#63884;</span><input type="submit" name="submit" value="入力確認" accesskey="6"></div>
                <input type="hidden" name="s_id"        value="<?= $strSessionID ?>">
                <input type="hidden" name="date"        value="<?= $strBbsDate ?>">
                <input type="hidden" name="login_id"    value="<?= $strLoginID ?>">
                <input type="hidden" name="thread_id"   value="<?= $strThreadID ?>">
                <input type="hidden" name="thread_name" value="<?= DataConvert::MetatagEncode( $strThreadName ); ?>">
                <?= DOCOMO_GUID_HIDDEN ?>
            </form>
        </div>
        <div style="text-align:center;"><br /><br />
            <span style="color:white;">&#63882;[<a href="./mailbbs_i.php<?= $arrayLogin["query_param"] . "&thread_id=" . $strThreadID ?>" accesskey="4">BBSへ戻る</a>]</span><br />
            <span style="color:white;">&#63888;[<a href="/admin/m/top.php<?= $arrayLogin["query_param"] ?>" accesskey="0">トップへ</a>]</span><br /><br />
        </div><br />
    </div>
<?
}
