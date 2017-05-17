<?php
/* ================================================================================
 * ファイル名   ：riyou.php
 * タイトル     ：投稿説明画面
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
include_once( LIB_DIR    . 'UsersiteLayout.php' );      // ヘッダ・フッタ表示
include_once( LIB_DIR    . 'StdFunc.php' );             // 標準関数群
include_once( LIB_DIR    . 'BbsDB.php' );               // BBS用
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
    // スレッド別の投稿用メールアドレス取得
    $strThreadID = $_GET["thread_id"];
    $arrayThread = BbsDB::GetThreadOneAndGroup( $strThreadID, $intRows );
    $strMail = $arrayThread[0]["thread_mailaddress"];
    unset( $arrayThread );


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
            <div style="background-color:<?= COLOR_PAGE_TITLE_BGCOLOR ?>;width:100%;text-align:center;"><span style="color:white;">投稿方法</span></div><br />
        </div>
        <div style="text-align:left;">
            <span style="color:white;">掲示板への投稿は、メールで受け付けています。<br />画像やメッセージを<a href="mailto:<?= $strMail ?>"><?= $strMail ?></a>宛に送信して下さい。<br />添付できる画像は１枚のみです。<br />テキストメールのみ対応ですので、デコメ・絵文字には対応していません。</span><hr size="1"><br />
        </div>
        <div style="text-align:center;">
            <div style="background-color:<?= COLOR_PAGE_TITLE_BGCOLOR ?>;width:100%;text-align:center;"><span style="color:white;">更新方法</span></div><br />
        </div>
        <div style="text-align:left;">
            <span style="color:white;">メールを送信したら、数秒後に掲示板の「<a href="pop_db.php<?= $arrayLogin["query_param"] . "&thread_id=" . $strThreadID ?>" acceskey="4">更新</a>」をクリックしてください。<br>掲示板があなたが送信したメールを取り込んで反映します。</span><hr size="1"><br />
        </div>
        <div style="text-align:center;"><br /><br />
            <span style="color:white;">&#63887;[<a href="./pop_db.php<?= $arrayLogin["query_param"] . "&thread_id=" . $strThreadID ?>" accesskey="9">BBSへ</a>]</span><br />
            <span style="color:white;">&#63888;[<a href="/admin/m/top.php<?= $arrayLogin["query_param"] ?>" accesskey="0">VIX トップへ</a>]</span><br /><br />
        </div><br />
    </div>
<?
}
