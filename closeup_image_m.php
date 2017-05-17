<?php
/****************************************************************************
**ファイル名    ：closeup_image_m.php
**タイトル      ：画像表示ページ(モバイル、フォトアルバム用)
**作成日        ：2008/12/16
**内容          ：モバイルでフォトアルバムの画像、タイトル、説明を表示する
**
**更新履歴******************************************************************
**変更日        変更者      変更箇所            変更理由と変更内容
****************************************************************************/

$strPath = substr( $_SERVER['SCRIPT_FILENAME'], 0, strpos( $_SERVER['SCRIPT_FILENAME'], 'public_html' ) - 1 + strlen( "/" . 'public_html' . "/" ) );
$strPath = $strPath . "admin/";

// includeするライブラリ・コンフィグファイルのパスを定義する
define( 'LIB_DIR'   , $strPath . '_lib/');
define( 'CONFIG_DIR', $strPath . '_config/');

// ライブラリ・コンフィグファイルをinclude
include_once( LIB_DIR    . 'DataConvert.php' );         // データ変換用関数群
include_once( LIB_DIR    . 'PhotoDB.php' );             // フォトアルバム情報取得・更新関数群
include_once( LIB_DIR    . 'UsersiteLayout.php' );      // ヘッダ・フッタ表示
include_once( LIB_DIR    . 'AccessLog.php' );           // アクセスログ関数群
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
    header("Location:./m/photo/album_all.php". DOCOMO_GUID . "&s_id=" . $strSessionID );
}

// ヘッダ表示
$arrayLogin = UsersiteLayout::PrintHeaderMobileExt( "bgcolor=\"#000000\" text=\"#ffffff\" link=\"#ffffff\" VLINK=\"#ffffff\" ALINK=\"#000000\"", 1 );
$strSessionID = $arrayLogin["s_id"];
main( $strAlbumID, $strSessionID, $arrayLogin );
UsersiteLayout::PrintFooterMobileExt( $strSessionID );

function main( $strAlbumID, $strSessionID, $arrayLogin )
{
    // オリジナルファイル名
    $strImageName = $_GET["name"];

    // その他パラメータ
    $strPageNo      = $_GET["pgno"];
    $strImageFlag   = $_GET["image_id"];

    // 画像情報取得
    $arrayImage = PhotoDB::GetAlbumImageOne( intval( $strAlbumID )  ,
                                             $strImageName          );

    // 取得できた場合
    if( true != is_null( $arrayImage ) )
    {
        $strImageSumbName   = $arrayImage["image_name_sumbnail"];
        $strImageTitle      = $arrayImage["title"];
        $strImageComment    = $arrayImage["comment"];
        $strImageCreateDate = $arrayImage["create_date"];
        unset( $arrayImage );
    }

    // 画像表示パス
    $strImageURL = PATH_IMAGE_PHOTOALBUM . "/" . $strImageSumbName;

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
            <div style="background-color:#228B22;width:100%;text-align:center;"><span style="color:white;"><?= DataConvert::MetatagEncode( $strImageTitle ); ?></span></div>
            <div style="text-align:left;"><span style="color:white;"><?= DataConvert::ReturnCodeToBR( DataConvert::MetatagEncode( $strImageComment ) ); ?></span></div>
        </div>
        <hr size="1">
        <img src='<?= $strImageURL ?>' />
        <hr size="1">
        <div style="text-align:center;"><br /><br />
            <span style="color:white;">&#63882;[<a href="./m/photo/album_detail.php<?= $arrayLogin["query_param"] . "&album_id=" . $strAlbumID . "&pgno=" . $strPageNo . "&image_id=" . $strImageFlag ?>" accesskey="4">画像一覧へ</a>]</span><br />
            <span style="color:white;">&#63888;[<a href="./m/top.php<?= $arrayLogin["query_param"] ?>" accesskey="0">トップへ</a>]</span><br /><br />
        </div><br />
    </div>
    <?
}
?>
