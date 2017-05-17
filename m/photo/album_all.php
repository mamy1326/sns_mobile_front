<?php
/* ================================================================================
 * ファイル名   ：album_all.php
 * タイトル     ：フォトアルバム一覧
 * 作成者       ：間宮 直樹
 * 作成日       ：2008/11/28
 * 内容         ：フォトアルバムの一覧を表示する。１ページに１０件まで。
 * 更新履歴*******************************************************************
 * 【変更日】  【変更者】      【変更箇所】        【変更理由と変更内容】
 * ================================================================================*/

$strPath = substr( $_SERVER['SCRIPT_FILENAME'], 0, strpos( $_SERVER['SCRIPT_FILENAME'], 'public_html' ) - 1 + strlen( "/" . 'public_html' . "/" ) );
$strPath = $strPath . "admin/";

// includeするライブラリ・コンフィグファイルのパスを定義する
define( 'LIB_DIR'   , $strPath . '_lib/');
define( 'CONFIG_DIR', $strPath . '_config/');
define( 'PAGE_SIZE' , '10');    // １ページに表示できるの数

// ライブラリ・コンフィグファイルをinclude
include_once( LIB_DIR    . 'UsersiteLayout.php' );      // ヘッダ・フッタ表示
include_once( LIB_DIR    . 'DataConvert.php' );         // データ変換用関数群
include_once( LIB_DIR    . 'PhotoDB.php' );             // フォトアルバム情報取得・更新関数群
include_once( CONFIG_DIR . 'title_conf.php' );          // ページタイトル定義ファイル
include_once( CONFIG_DIR . 'manage_conf.php' );         // 管理画面基本情報定義ファイル

$strSessionID = $_GET["s_id"];  // セッションID

// ヘッダ表示
UsersiteLayout::PrintHeaderMobileExt( "bgcolor=\"#000000\" text=\"#ffffff\" link=\"#ffffff\" VLINK=\"#ffffff\" ALINK=\"#000000\"" );
main( $strSessionID );
UsersiteLayout::PrintFooterMobileExt( $strSessionID );

function main( $strSessionID )
{
    $intNowDatetime = intval( date( 'Ymd' ) );

    // **********************
    // 現在のページ番号取得
    // **********************
    if( 0 == strlen( $_GET["pgno"] ) )
    {
        $intPageNo = 1;
    }
    else
    {
        $intPageNo = intval( $_GET["pgno"] );
    }

    // ****************************************
    // 総数取得
    // ****************************************

    // フォトアルバム取得
    $arrayAlbum = PhotoDB::GetPhotoHome( $intStartRow   ,   // 取得開始レコード
                                         PAGE_SIZE      ,   // 取得するレコード数
                                         $intAlbumCount ,   // 取得できたレコード数
                                         1              );  // LIMITなし
    unset( $arrayAlbum );

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
            <div style="background-color:<?= COLOR_PAGE_TITLE_BGCOLOR ?>;width:100%;text-align:center;"><span style="color:white;">フォトアルバム　一覧</span></div><br />
        </div>
        <div style="text-align:left;">
            <?
            // 検索結果ゼロの場合
            if( 0 == $intAlbumCount )
            {
                ?>
                <br />
                <span style="color:white;">公開されているフォトアルバムはありません。</span>
                <?
            }
            else
            {
                // ページャー表示
                $strPager = StdFunc::WriteContents( $intAlbumCount      ,   // 総数
                                                    $intPageNo          ,   // 表示するページ番号
                                                    3                   ,   // 並び数(1 2 3)で３つ表示
                                                    PAGE_SIZE           ,   // １ページに表示するの数
                                                    "./album_all.php" . DOCOMO_GUID . "&s_id=" . $strSessionID ,  // ページ番号を除いたベースURL
                                                    $intStartRow        ,   // Limit開始行
                                                    $intEndRow          ,   // Limit終了行
                                                    "件"                );
                ?>
                <span style="color:white;"><?= $strPager ?></span>
                <br /><br /><hr size="1">

                <?
                // 情報を取得する
                $arrayAlbum = PhotoDB::GetPhotoHome(    $intStartRow   ,   // 取得開始レコード
                                                        PAGE_SIZE      ,   // 取得するレコード数
                                                        $intAlbumCount ,   // 取得できたレコード数
                                                        0              );  // LIMITあり

                // 取得した情報を表示する
                $strPhotoAlbum = "";
                for( $intCount = 0; $intCount < $intAlbumCount; $intCount++ )
                {
                    $strImageDatetime = $arrayAlbum[$intCount]["new_image_date"];
                    // 最新投稿日付を日付で表示するか日時で表示するかを判定する
                    $intNewImageDate = intval( substr( $strImageDatetime, 0, 4 ) . substr( $strImageDatetime, 5, 2 ) . substr( $strImageDatetime, 8, 2 ) );

                    // 昨日以前が最新コメントの場合は年月表示、今日が最新コメントの場合は時分表示
                    // (昨日以前)
                    if( $intNowDatetime > $intNewImageDate )
                    {
                        // 年月
                        $strShowDate = substr( $strImageDatetime, 5, 2 ) . "月" . substr( $strImageDatetime, 8, 2 ) . "日";
                    }
                    else
                    {
                        // 時分
                        $strShowDate = substr( $strImageDatetime, 11, 2 ) . ":" . substr( $strImageDatetime, 14, 2 );
                    }

                    $strPhotoAlbum =   $strPhotoAlbum .
                                        "<div mode=\"nowrap\">" .
                                        "<span style=\"color:#F0E68C;\">[" . $strShowDate . "]</span><br />" .
                                        "<span style=\"color:white;\"><a href=\"./album_detail.php" . DOCOMO_GUID . "&s_id=" . $strSessionID . "&album_id=" . $arrayAlbum[$intCount]["album_id"] . "\">" . DataConvert::MetatagEncode( $arrayAlbum[$intCount]["album_name"] ) . "(" . $arrayAlbum[$intCount]["image_count"] . ")</a></span>" .
                                        "</div>\n";
                }
                ?>
                <?= $strPhotoAlbum ?>
                <br />
                <hr size="1">
                <span style="color:white;"><?= $strPager ?></span>
                <hr size="1">
            <?
            }
            ?>
        </div>
        <div style="text-align:center;"><br /><br />
            <span style="color:white;">&#63888;[<a href="../top.php<?= DOCOMO_GUID . "&s_id=" . $strSessionID ?>" accesskey="0">トップへ</a>]</span><br /><br />
        </div><br />
    </div>
<?
}
