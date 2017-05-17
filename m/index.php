<?php
/* ================================================================================
 * ファイル名   ：index.php
 * タイトル     ：モバイルログインページ
 * 作成者       ：間宮 直樹
 * 作成日       ：2008/11/12
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
include_once( CONFIG_DIR . 'title_conf.php' );          // ページタイトル定義ファイル
include_once( CONFIG_DIR . 'manage_conf.php' );         // 管理画面基本情報定義ファイル

// ヘッダ表示
UsersiteLayout::PrintHeaderMobileExt( "bgcolor=\"#000000\" text=\"#ffffff\" link=\"#ffffff\" VLINK=\"#ffffff\" ALINK=\"#000000\"", 0 );
main();
UsersiteLayout::PrintFooterMobileExt();

/* ************************************************************
 * 関数名       ：main()
 * タイトル     ：【管理】ログイン画面
 * ************************************************************/
function main()
{
    // エラー有り
    // (ログイン認証エラー)
    if( 0 == strcmp( "1", $_GET["error"] ) )
    {
        $strErrorTag = "<span style=\"color:red;\">ログインID、またはパスワードに誤りがあります。</span><br /><br />\n";
    }
    // (かんたんログイン認証エラー)
    elseif( 0 == strcmp( "2", $_GET["error"] ) )
    {
        $strErrorTag = "<span style=\"color:red;\">かんたんログインをご利用の場合は、一度IDとパスワードでログインしてからメニューの「かんたんログイン設定」を実行してください。</span><br /><br />\n";
    }
    else
    {
        $strErrorTag = "";
    }

    //キャリア取得
    $intCarrier       = StdFunc::GetAgentNumber();

?>
    <STYLE type="text/css">
    <!--
    A:link                      /* リンク */
    {
        color: #ffffff;
        background-color: #000000;
    }
    A:visited                   /* 既に見たリンク */
    {
        color: #ffffff;
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
            <img src="<?= TOP_LOGO_IMAGE_PATH ?>"><br /><br />
            <div style="background-color:<?= COLOR_PAGE_TITLE_BGCOLOR ?>;width:100%;text-align:center;"><span style="color:white;">VIX ログイン</span></div><br />
        </div>
        <div style="text-align:left;">
            <hr size="1"><span style="color:white;">ログインIDとパスワードを入力してログインしてください。</span><hr size="1"><br />
            <div style="text-align:left;">
                <?= $strErrorTag ?>
                <?
                // ＰＣ・WILLCOM・iPhoneなど、端末IDの取れないもの以外は簡単ログイン設置
                if( CARRIER_PC != $intCarrier )
                {
                    ?>
                    <form action="./check.php<?= DOCOMO_GUID ?>" method="POST" name="easy_login_form">
                        <input name="easy_login"   type="hidden" value="1" />
                        <?= DOCOMO_GUID_HIDDEN ?>
                        <div style="text-align:center;"><span style="color:white;">&#63883;</span><input type="submit" name="Submit" value="かんたんログイン" accesskey="5"></div><br />
                        <hr size="1">
                    </form>
                    <?
                }
                ?>
                <form action="./check.php<?= DOCOMO_GUID ?>" method="POST" name="login">
                    <span style="color:white;">ログインID</span><br />
                    　<input name="loginid"   type="text" size="20" maxlength="20" value="" <?= StdFunc::GetInputTagAttr( ATTR_ALPHABET , 20 , $intCarrier ) ?> /><br />
                    <span style="color:white;">パスワード</span><br />
                    　<input name="loginpass" type="text" size="20" maxlength="20" value="" <?= StdFunc::GetInputTagAttr( ATTR_ALPHABET , 20 , $intCarrier ) ?> /><br /><br />
                    <div style="text-align:center;"><span style="color:white;">&#63881;</span><input type="submit" name="Submit" value="ログイン" accesskey="3"></div><br />
                </form>
            </div>
            <hr size="1">
        </div>
    </div>
<?
}
