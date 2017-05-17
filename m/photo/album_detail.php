<?php
/* ================================================================================
 * ファイル名   ：album_detail.php
 * タイトル     ：フォトアルバム　詳細表示画面
 * 作成者       ：間宮 直樹
 * 作成日       ：2008/11/16
 * 内容         ：指定されたIDの管理本部通信を表示する。
 *                ・画像のタグが存在する場合は、画像を表示するAタグリンクを設置。
 *                ・画像を表示を選んだ場合は全画像を表示（PCのみ）
 * 更新履歴*******************************************************************
 * 【変更日】  【変更者】      【変更箇所】        【変更理由と変更内容】
 * ================================================================================*/

$strPath = substr( $_SERVER['SCRIPT_FILENAME'], 0, strpos( $_SERVER['SCRIPT_FILENAME'], 'public_html' ) - 1 + strlen( "/" . 'public_html' . "/" ) );
$strPath = $strPath . "admin/";

// includeするライブラリ・コンフィグファイルのパスを定義する
define( 'LIB_DIR'   , $strPath . '_lib/');
define( 'CONFIG_DIR', $strPath . '_config/');
define( 'PAGE_SIZE' , '5');    // １ページに表示できるの数

include_once( LIB_DIR    . 'DataConvert.php' );         // データ変換用関数群
include_once( LIB_DIR    . 'PhotoDB.php' );             // フォトアルバム情報取得・更新関数群
include_once( LIB_DIR    . 'UsersiteLayout.php' );      // ヘッダ・フッタ表示
include_once( LIB_DIR    . 'AccessLog.php' );           // アクセスログ関数群
include_once( CONFIG_DIR . 'title_conf.php' );          // ページタイトル定義ファイル
include_once( CONFIG_DIR . 'manage_conf.php' );         // 管理画面基本情報定義ファイル

$strSessionID = $_GET["s_id"];  // セッションID

// アクセスログを書き込む
AccessLog::WriteAccessLog( LOG_ALBUM_FLAG );

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
main( $strPath, $strSessionID, $arrayLogin );
UsersiteLayout::PrintFooterMobileExt( $strSessionID );

function main( $strPath, $strSessionID, $arrayLogin )
{
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

    // フォトアルバムID
    $intAlbumID = intval( $_GET["album_id"] );

    // 画像表示ID
    $strImageFlag = $_GET["image_id"];
    if( 0 == strcmp( "1", $strImageFlag ) )    // 画像あり
    {
        $strImageButtonName = "画像表示しない";
        $strParamImageID    = "0";
    }
    else    // 画像なし
    {
        $strImageButtonName = "画像を表示する";
        $strParamImageID    = "1";
        $strImageFlag       = "0";
    }

    // フォトアルバム情報を取得する
    $arrayAlbum = PhotoDB::GetAlbumAndImage(    $intAlbumID    ,  // フォトアルバムID
                                                $intStartRow   ,   // 取得開始レコード
                                                PAGE_SIZE      ,   // 取得するレコード数
                                                $intAlbumCount ,   // 取得できたレコード数
                                                1              );  // LIMITなし

    // ******************************************************************
    // PCか携帯かを判別し、画像を表示するかリンクを表示するかを判定する
    // ******************************************************************

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
            <div style="background-color:#228B22;width:100%;text-align:center;"><span style="color:white;"><?= DataConvert::MetatagEncode( $arrayAlbum[0]["album_name"] ); ?></span></div>
            <div style="text-align:left;"><span style="color:white;"><?= DataConvert::ReturnCodeToBR( DataConvert::MetatagEncode( $arrayAlbum[0]["album_comment"] ) ); ?></span></div>
        </div>
        <?
        // 投稿用メールアドレスがある場合のみ、投稿するリンクを設置
        if( 0 < strlen( $arrayAlbum[0]["mail_address"] ) )
        {
            ?>
            <hr size="1">
            <div style="text-align:left;"><span style="color:white;">&#63880;</span><a href="./album_upload_riyou.php<?= $arrayLogin["query_param"] . "&album_id=" . $intAlbumID . "&image_id=" . $_GET["image_id"] . "&pgno=" . $intPageNo ?>" accesskey="2">画像を投稿する</a></div>
            <?
        }
        ?>
        <div style="text-align:left;"><span style="color:white;">&#63881;</span><a href="./album_pop_db.php<?= $arrayLogin["query_param"] . "&album_id=" . $intAlbumID . "&image_id=" . $_GET["image_id"] . "&pgno=" . $intPageNo ?>" accesskey="3">最新に更新する</a>
        <?
        // モバイルの場合のみボタン表示
        if( 0 != $intPc_or_Mobile )
        {
            ?>
            <div style="text-align:center;">
                <hr size="1">
                    <form action="./album_detail.php" method="GET" name="image_show">
                        <?= DOCOMO_GUID_HIDDEN ?>
                        <input type="hidden" name="s_id"        value="<?= $strSessionID ?>">
                        <input type="hidden" name="image_id"    value="<?= $strParamImageID ?>" />
                        <input type="hidden" name="album_id"    value="<?= $intAlbumID ?>" />
                        <input type="hidden" name="pgno"        value="<?= $intPageNo ?>" />
                        <input type="submit" name="Submit"      value="<?= $strImageButtonName ?>">
                    </form>
            </div>
            <?
        }
        ?>
        <hr size="1">
        <div style="text-align:left;">
            <?
            // 検索結果ゼロの場合
            if( 0 == strcmp( "0", $arrayAlbum[0]["image_count"] ) )
            {
                ?>
                <br />
                <span style="color:white;">登録されている画像はありません。</span>
                <?
            }
            else
            {
                // ページャー表示
                $strPager = StdFunc::WriteContents( $intAlbumCount      ,   // 総数
                                                    $intPageNo          ,   // 表示するページ番号
                                                    6                   ,   // 並び数(1 2 3)で３つ表示
                                                    PAGE_SIZE           ,   // １ページに表示するの数
                                                    "./album_detail.php" . $arrayLogin["query_param"] . "&album_id=" . $intAlbumID . "&image_id=" . $strImageFlag ,  // ページ番号を除いたベースURL
                                                    $intStartRow        ,   // Limit開始行
                                                    $intEndRow          ,   // Limit終了行
                                                    "枚"                );
                ?>
                <span style="color:white;"><?= $strPager ?></span>
                <br /><hr size="1">

                <?
                // 情報を取得する
                $arrayAlbumrmation = PhotoDB::GetAlbumAndImage( $intAlbumID    ,  // フォトアルバムID
                                                                $intStartRow   ,   // 取得開始レコード
                                                                PAGE_SIZE      ,   // 取得するレコード数
                                                                $intAlbumCount ,   // 取得できたレコード数
                                                                0              );  // LIMITあり

                // 取得した情報を表示する
                $strAlbumImage = "";
                for( $intCount = 0; $intCount < $intAlbumCount; $intCount++ )
                {
                    $strImageLink = "";

                    if( 0 < strlen( $arrayAlbumrmation[$intCount]["image_title"] ) )
                    {
                        $strImageTitle = DataConvert::MetatagEncode( $arrayAlbumrmation[$intCount]["image_title"] );
                    }
                    else
                    {
                        $strImageTitle = $arrayAlbumrmation[$intCount]["image_name"];
                    }

                    $strImageComment = "";
                    $strImageComment = DataConvert::ReturnCodeToBR( DataConvert::MetatagEncode( $arrayAlbumrmation[$intCount]["image_comment"] ) );

                    // モバイルの場合
                    if( 1 == $intPc_or_Mobile )
                    {
                        // リンクタグ作成
                        $strLingTag = "<a href='../../closeup_image_m.php" . $arrayLogin["query_param"] . "&album_id=" . $intAlbumID . "&name=" . $arrayAlbumrmation[$intCount]["image_name"] . "&image_id=" . $_GET["image_id"] . "&pgno=" . $intPageNo . "'>";

                        // リンク表示指定の場合
                        if( 0 == strcmp( "0", $strImageFlag ) )
                        {
                            // 画像のリンクタグを作成
                            $strImageLink       = $strLingTag . $strImageTitle . "</a>\n";
                        }
                        // 最小サイズ画像表示の場合
                        else
                        {
                            // 画像のリンク＆最小サイズ画像タグを作成
                            $strImageLink       =   $strLingTag .
                                                    "<img src='" . PATH_IMAGE_PHOTOALBUM . "/" . $arrayAlbumrmation[$intCount]["image_name_sumbnail_ss"] . "' /></a><br />".
                                                    "<span style=\"color:white;\">" . $strImageTitle . "</span>\n";
                        }
                    }
                    // PCの場合
                    else
                    {
                        list( $strOrgWidth, $strOrgHeight, $Orgtype, $Orgattr ) = getimagesize( $strPath . PATH_IMAGE_PHOTO_COPY . $arrayAlbumrmation[ $intCount ]["image_name"] );
                        $intWindowWidth     = intval( $strOrgWidth  ) + 100;
                        $intWindowHeight    = intval( $strOrgHeight ) + 100;

                        // 画像表示タグを作成
                        $strImageLink       =   "<a href=\"javascript:void(0);\" onClick=\"javascript:window.open('/admin/closeup_image.php?name=" . $arrayAlbumrmation[$intCount]["image_name"] . "&flg=0', 'name', 'width=" . $intWindowWidth . ",height=" . $intWindowHeight . ",toolbars=no,scrollbars=yes,resizable=no')\">" .
                                                "<img src='" . PATH_IMAGE_PHOTOALBUM . "/" . $arrayAlbumrmation[$intCount]["image_name_sumbnail"] . "' border=\"0\" /></a><br />".
                                                "<span style=\"color:#A0522D;\">■タイトル</span><br /><span style=\"color:white;\">" . $strImageTitle . "</span><br />" .
                                                "<span style=\"color:#A0522D;\">■説明</span><br /><span style=\"color:white;\">" . $strImageComment . "</span>\n";
                    }

                    $strAlbumImage =    $strAlbumImage .
                                        $strImageLink .
                                        "<hr size=\"1\">\n";
                }
                ?>
                <?= $strAlbumImage ?>
                <span style="color:white;"><?= $strPager ?></span>
                <hr size="1">
            <?
            }
            ?>
        </div>
        <div style="text-align:center;"><br /><br />
            <span style="color:white;">&#63882;[<a href="./album_all.php<?= $arrayLogin["query_param"] ?>" accesskey="4">一覧へ</a>]</span><br />
            <span style="color:white;">&#63888;[<a href="../top.php<?= $arrayLogin["query_param"] ?>" accesskey="0">トップへ</a>]</span><br /><br />
        </div><br />
    </div>
<?
}
