<?php
/* ================================================================================
 * ファイル名   ：riyou.php
 * タイトル     ：フォトアルバム投稿説明画面
 * 作成者       ：間宮 直樹
 * 作成日       ：2008/12/16
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
include_once( LIB_DIR    . 'DataConvert.php' );         // データ変換用関数群
include_once( LIB_DIR    . 'PhotoDB.php' );             // フォトアルバム情報取得・更新関数群
include_once( CONFIG_DIR . 'title_conf.php' );          // ページタイトル定義ファイル
include_once( CONFIG_DIR . 'manage_conf.php' );         // 管理画面基本情報定義ファイル

$strSessionID = $_GET["s_id"];  // セッションID

// フォトアルバムID
$strAlbumID = $_GET["album_id"];

// アクセス許可されているユーザかどうかチェック
$intRetCode = PhotoDB::CheckAuthAlbumUser( $strSessionID, $strAlbumID, $intUserRows, 0 );
if( true != $intRetCode )
{
    // 許可されていない場合はスレッド一覧へ
    header("Location:./album_all.php". DOCOMO_GUID . "&s_id=" . $strSessionID );
}

// ヘッダ表示
$arrayLogin = UsersiteLayout::PrintHeaderMobileExt( "bgcolor=\"#000000\" text=\"#ffffff\" link=\"#ffffff\" VLINK=\"#ffffff\" ALINK=\"#000000\"", 1 );
$strSessionID = $arrayLogin["s_id"];
main( $strAlbumID, $strSessionID, $arrayLogin );
UsersiteLayout::PrintFooterMobileExt( $strSessionID );

function main( $strAlbumID, $strSessionID, $arrayLogin )
{
    $strImageFlag   = $_GET["image_id"];
    $strPageNo      = $_GET["pgno"];

    // フォトアルバムの投稿用メールアドレス取得
    $arrayAlbum = PhotoDB::GetAlbumOne( intval( $strAlbumID ) );
    $strMail = $arrayAlbum["mail_address"];
    $strName = $arrayAlbum["album_name"];
    unset( $arrayAlbum );

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
            <div style="background-color:#228B22;width:100%;text-align:center;"><span style="color:white;">[<?= DataConvert::MetatagEncode( $strName ) ?>]への画像投稿方法</span></div>
        </div>
        <div style="text-align:left;">
            <span style="color:white;">フォトアルバムへの画像投稿は、メールで受け付けています。<br />画像を<a href="mailto:<?= $strMail ?>"><?= $strMail ?></a>宛に送信して下さい。<br />メールのタイトルが画像タイトル、本文が画像の説明として登録されます。<br />添付できる画像は１枚のみです。<br />テキストメールのみ対応ですので、デコメ・絵文字には対応していません。</span><hr size="1"><br />
        </div>
        <div style="text-align:center;">
            <div style="background-color:<?= COLOR_PAGE_TITLE_BGCOLOR ?>;width:100%;text-align:center;"><span style="color:white;">更新方法</span></div>
        </div>
        <div style="text-align:left;">
            <span style="color:white;">メールを送信したら、数秒後に「<a href="./album_pop_db.php<?= $arrayLogin["query_param"] . "&album_id=" . $strAlbumID . "&image_id=" . $strImageFlag . "&pgno=" . $strPageNo ?>" acceskey="4">更新</a>」をクリックしてください。<br>フォトアルバムがあなたが送信したメールを取り込んで反映します。</span><hr size="1"><br />
        </div>
        <div style="text-align:center;"><br /><br />
            <span style="color:white;">&#63882;[<a href="./album_pop_db.php<?= $arrayLogin["query_param"] . "&album_id=" . $strAlbumID . "&image_id=" . $strImageFlag . "&pgno=" . $strPageNo ?>" accesskey="4">画像一覧へ</a>]</span><br />
            <span style="color:white;">&#63888;[<a href="/admin/m/top.php<?= $arrayLogin["query_param"] ?>" accesskey="0">トップへ</a>]</span><br /><br />
        </div><br />
    </div>
<?
}
