<?php
/* ================================================================================
 * ファイル名   ：easy_login.php
 * タイトル     ：かんたんログイン表示画面
 * 作成者       ：間宮 直樹
 * 作成日       ：2008/11/22
 * 内容         ：かんたんログインの説明と設定方法、設定ボタン、解除ボタンの設置
 * 更新履歴*******************************************************************
 * 【変更日】  【変更者】      【変更箇所】        【変更理由と変更内容】
 * ================================================================================*/

$strPath = substr( $_SERVER['SCRIPT_FILENAME'], 0, strpos( $_SERVER['SCRIPT_FILENAME'], 'public_html' ) - 1 + strlen( "/" . 'public_html' . "/" ) );
$strPath = $strPath . "admin/";

// includeするライブラリ・コンフィグファイルのパスを定義する
define( 'LIB_DIR'   , $strPath . '_lib/');
define( 'CONFIG_DIR', $strPath . '_config/');

include_once( LIB_DIR    . 'DataConvert.php' );         // データ変換用関数群
include_once( LIB_DIR    . 'InfoDB.php' );              // Information情報
include_once( LIB_DIR    . 'UsersiteLayout.php' );      // ヘッダ・フッタ表示
include_once( LIB_DIR    . 'AccessLog.php' );           // アクセスログ関数群
include_once( LIB_DIR    . 'UserDB.php' );              // ユーザ情報取得・更新関数群
include_once( CONFIG_DIR . 'title_conf.php' );          // ページタイトル定義ファイル
include_once( CONFIG_DIR . 'manage_conf.php' );         // 管理画面基本情報定義ファイル

$strSessionID = $_GET["s_id"];  // セッションID

// ヘッダ表示
$arrayLogin = UsersiteLayout::PrintHeaderMobileExt( "bgcolor=\"#000000\" text=\"#ffffff\" link=\"#ffffff\" VLINK=\"#ffffff\" ALINK=\"#000000\"" );
main( $strSessionID, $arrayLogin );
UsersiteLayout::PrintFooterMobileExt( $strSessionID );

function main( $strSessionID, $arrayLogin )
{
    // セッションIDからユーザ情報を取得
    $arrayUserInfo = UserDB::GetLoginUserOfSession( $strSessionID   ,
                                                    $intRows        );  // (OUT)取得件数（基本的に１件のみ）

    // (かんたんログイン設定されている＝端末IDがある)
    if( 0 < strlen( $arrayUserInfo["mobile_id"] ) )
    {
        $strEasyMessage   = "設定あり";
    }
    else
    {
        $strEasyMessage   = "設定なし";
    }

    unset( $arrayUserInfo );

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
            <div style="background-color:<?= COLOR_PAGE_TITLE_BGCOLOR ?>;width:100%;text-align:center;"><span style="color:white;">かんたんログイン設定</span></div>
        </div>
        <div style="text-align:left;">
            <span style="color:white;">かんたんログインを設定すると、次回からログインID、パスワードの入力をせずにボタン１つでログインできます。<br /></span>
            <span style="color:red;">※携帯の機種変更をした場合は、再度設定が必要になる場合があります。<br /</span>
            <hr size="1">
            <span style="color:#FA8072;">■現在のかんたんログイン設定状況<br /></span>
            <span style="color:white;"><span style="color:red;">⇒</span><?= $strEasyMessage ?><br /></span>
            <hr size="1">
            <div style="background-color:#9932CC;width:100%;text-align:center;"><span style="color:white;">設定する</span></div>
            <span style="color:white;">「設定する」ボタンを押して、端末ID情報を送信してください。<br />次回ログインからかんたんログインがご利用になれます。<br /><br /></span>
            <form action="./easy_login_set.php<?= DOCOMO_GUID ?>" method="GET" name="login">
                <?= DOCOMO_GUID_HIDDEN ?>
                <input type="hidden" name="s_id"        value="<?= $strSessionID ?>">
                <input name="easy_flag"   type="hidden" value="0" />
                <div style="text-align:center;"><input type="submit" name="Submit" value="設定する" /></div><br />
            </form>
            <hr size="1">
            <div style="background-color:#9932CC;width:100%;text-align:center;"><span style="color:white;">設定を解除する</span></div>
            <span style="color:white;">かんたんログイン設定を解除する場合は「設定を解除する」ボタンを押してください。<br /><br /></span>
            <form action="./easy_login_set.php<?= DOCOMO_GUID ?>" method="GET" name="login">
                <?= DOCOMO_GUID_HIDDEN ?>
                <input type="hidden" name="s_id"        value="<?= $strSessionID ?>">
                <input name="easy_flag"   type="hidden" value="1" />
                <div style="text-align:center;"><input type="submit" name="Submit" value="設定を解除する" /></div><br />
            </form>
            <hr size="1">
        </div>
        <div style="text-align:center;"><br /><br />
            <span style="color:white;">&#63888;[<a href="../top.php<?= $arrayLogin["query_param"] ?>" accesskey="0">トップへ</a>]</span><br /><br />
        </div><br />
    </div>
<?
}
