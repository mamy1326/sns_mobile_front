<?php
/* ================================================================================
 * ファイル名   ：index.php
 * タイトル     ：トップページ
 * 作成者       ：間宮 直樹
 * 作成日       ：2008/11/13
 * 内容         ：以下を表示する。
 *                ・管理本部通信の最新５件のタイトルとリンクを表示
 *                ・管理本部通信の一覧へのリンクを「もっと見る」で設置
 *                ・ＢＢＳへの入り口を設置
 * 更新履歴*******************************************************************
 * 【変更日】  【変更者】      【変更箇所】        【変更理由と変更内容】
 *  2008/11/22  間宮            メニュー            かんたんログイン設置
 * ================================================================================*/

$strPath = substr( $_SERVER['SCRIPT_FILENAME'], 0, strpos( $_SERVER['SCRIPT_FILENAME'], 'public_html' ) - 1 + strlen( "/" . 'public_html' . "/" ) );
$strPath = $strPath . "admin/";

// includeするライブラリ・コンフィグファイルのパスを定義する
define( 'LIB_DIR'   , $strPath . '_lib/');
define( 'CONFIG_DIR', $strPath . '_config/');

include_once( LIB_DIR    . 'DataConvert.php' );         // データ変換用関数群
include_once( LIB_DIR    . 'UsersiteLayout.php' );      // ヘッダ・フッタ表示
include_once( LIB_DIR    . 'InfoDB.php' );              // Information関数群
include_once( LIB_DIR    . 'PhotoDB.php' );             // フォトアルバム関数群
include_once( LIB_DIR    . 'BbsDB.php' );               // ＢＢＳ関数群
include_once( CONFIG_DIR . 'title_conf.php' );          // ページタイトル定義ファイル
include_once( CONFIG_DIR . 'manage_conf.php' );         // 管理画面基本情報定義ファイル

$strSessionID = $_GET["s_id"];  // セッションID

// ヘッダ表示
$arrayLogin = UsersiteLayout::PrintHeaderMobileExt( "bgcolor=\"#000000\" text=\"#ffffff\" link=\"#ffffff\" VLINK=\"#ffffff\" ALINK=\"#000000\"" );
main( $strSessionID, $arrayLogin );
UsersiteLayout::PrintFooterMobileExt( $strSessionID );

function main( $strSessionID, $arrayLogin )
{
    $intNowDatetime = intval( date( 'Ymd' ) );

    // ****************************
    // Information取得（最新５件）
    // ****************************
    $arrayInformation = InfoDB::GetInfoHome(    0               ,   // 取得開始レコード
                                                5               ,   // 取得するレコード数
                                                $intInfoCount   );  // 取得できたレコード数

    $strInformation = "";
    for( $intCount = 0; $intCount < $intInfoCount; $intCount++ )
    {
        $strInformation =   $strInformation .
                            "<div mode=\"nowrap\">" .
                            "<span style=\"color:#F0E68C;\">[" . sprintf( "%02s", $arrayInformation[$intCount]["info_date_mm"] ) . "月" . sprintf( "%02s", $arrayInformation[$intCount]["info_date_dd"] ) . "日]</span><br />" .
                            "<span style=\"color:white;\"><a href=\"./info/info_detail.php" . $arrayLogin["query_param"] . "&id=" . $arrayInformation[$intCount]["info_id"] . "\">" . DataConvert::MetatagEncode( $arrayInformation[$intCount]["info_title"] ) ."</a></span>" .
                            "</div>\n";
    }

    // ****************************
    // フォトアルバム取得（最新５件）
    // ****************************
    $arrayPhotoAlbum = PhotoDB::GetPhotoHome(   0               ,   // 取得開始レコード
                                                5               ,   // 取得するレコード数
                                                $intPhotoCount  );  // 取得できたレコード数

    $strPhotoAlbum = "";
    for( $intCount = 0; $intCount < $intPhotoCount; $intCount++ )
    {
        $strImageDatetime = $arrayPhotoAlbum[$intCount]["new_image_date"];
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

        $strPhotoAlbum =    $strPhotoAlbum .
                            "<div mode=\"nowrap\">" .
                            "<span style=\"color:#F0E68C;\">[" . $strShowDate . "]</span><br />" .
                            "<span style=\"color:white;\"><a href=\"./photo/album_detail.php" . $arrayLogin["query_param"] . "&album_id=" . $arrayPhotoAlbum[$intCount]["album_id"] . "\">" . DataConvert::MetatagEncode( $arrayPhotoAlbum[$intCount]["album_name"] ) . "(" . $arrayPhotoAlbum[$intCount]["image_count"] . ")</a></span>" .
                            "</div>\n";
    }

    // ****************************
    // ＢＢＳスレッド取得（最新５件）
    // ****************************
    $arrayThread = BbsDB::GetThreadHome(    0               ,   // 取得開始レコード
                                            5               ,   // 取得するレコード数
                                            $intThreadCount );  // 取得できたレコード数

    $strThread = "";
    for( $intCount = 0; $intCount < $intThreadCount; $intCount++ )
    {
        $strCommentDatetime = $arrayThread[$intCount]["new_comment_date"];

        // 最新投稿日付を日付で表示するか日時で表示するかを判定する
        $intNewCommentDate = intval( substr( $strCommentDatetime, 0, 4 ) . substr( $strCommentDatetime, 5, 2 ) . substr( $strCommentDatetime, 8, 2 ) );

        // 昨日以前が最新コメントの場合は年月表示、今日が最新コメントの場合は時分表示
        // (昨日以前)
        if( $intNowDatetime > $intNewCommentDate )
        {
            // 年月
            $strShowDate = substr( $strCommentDatetime, 5, 2 ) . "月" . substr( $strCommentDatetime, 8, 2 ) . "日";
        }
        else
        {
            // 時分
            $strShowDate = substr( $strCommentDatetime, 11, 2 ) . ":" . substr( $strCommentDatetime, 14, 2 );
        }

        $strThread =    $strThread .
                        "<div mode=\"nowrap\">" .
                        "<span style=\"color:#F0E68C;\">[" . $strShowDate . "]</span><br />" .
                        "<span style=\"color:white;\"><a href=\"./bbs/mailbbs_gd/mailbbs_i.php" . $arrayLogin["query_param"] . "&thread_id=" . $arrayThread[$intCount]["thread_id"] . "\">" . DataConvert::MetatagEncode( $arrayThread[$intCount]["thread_name"] ) ."(" . $arrayThread[$intCount]["comment_count"] . ")</a></span>" .
                        "</div>\n";
    }

    //キャリア取得
    $intCarrier       = StdFunc::GetAgentNumber();
    if( CARRIER_EZWEB == $intCarrier )
    {
        $strFontSmall = ".span_white{ font-size:small; color:white; }\n";
    }
    else
    {
        $strFontSmall = ".span_white{ color:white; }\n";
    }
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
    <?= $strFontSmall ?>
    -->
    </STYLE>
    <div style="background-color:#000000;<?= StdFunc::GetFontSizeOfCarrier( FONT_SIZE_ALL ) ?>">
        <div style="text-align:center;">
            <img src="<?= TOP_LOGO_IMAGE_PATH ?>"><br /><br />
            <div style="background-color:<?= COLOR_PAGE_TITLE_BGCOLOR ?>;width:100%;text-align:center;"><span style="color:white;">TOPページ</span></div>
            <div style="text-align:left;"><span style="color:#FFD700;">お疲れさまです。<br /><?= $arrayLogin["user_name"] ?>さん</span></div><br />
            <div style="text-align:left;"><span style="color:white;">モバイルサイトのトップページです。</span></div><hr size="1"><br />
            <div style="background-color:#EE82EE;"><span style="color:white;">管理本部通信</span></div>
            <?
            if( 0 < strlen( $strInformation ) )
            {
                ?>
                <div style="text-align:left;"><span style="color:white;"><?= $strInformation ?></span></div>
                <div style="text-align:right;"><span style="color:red;">⇒</span><span style="color:white;"><a href="./info/info_all.php<?= $arrayLogin["query_param"] ?>">もっと見る</a></span></div>
                <?
            }
            else
            {
                ?>
                <div style="text-align:left;"><span style="color:white;">公開されている管理本部通信はありません。</span></div>
                <?
            }
            ?>
            <hr size="1"><br />
            <div style="background-color:#A0522D;"><span style="color:white;">フォトアルバム</span></div>
            <?
            if( 0 < strlen( $strPhotoAlbum ) )
            {
                ?>
                <div style="text-align:left;"><span style="color:white;"><?= $strPhotoAlbum ?></span></div>
                <div style="text-align:right;"><span style="color:red;">⇒</span><span style="color:white;"><a href="./photo/album_all.php<?= $arrayLogin["query_param"] ?>">もっと見る</a></span></div>
                <?
            }
            else
            {
                ?>
                <div style="text-align:left;"><span style="color:white;">公開されているフォトアルバムはありません。</span></div>
                <?
            }
            ?>
            <hr size="1"><br />
            <div style="background-color:#0000CD;"><span style="color:white;">ＢＢＳスレッド</span></div>
            <?
            if( 0 < strlen( $strThread ) )
            {
                ?>
                <div style="text-align:left;"><span style="color:white;"><?= $strThread ?></span></div>
                <div style="text-align:right;"><span style="color:red;">⇒</span><span style="color:white;"><a href="./bbs/thread_all.php<?= $arrayLogin["query_param"] ?>">もっと見る</a></span></div>
                <?
            }
            else
            {
                ?>
                <div style="text-align:left;"><span style="color:white;">公開されているスレッドはありません。</span></div>
                <?
            }
            ?>
            <hr size="1"><br />
            <div style="text-align:left;">
                <span style="color:#FA8072;">■その他メニュー</span><br />
                <?
                // ＰＣ・WILLCOM・iPhoneなど、端末IDの取れないもの以外はかんたんログイン設置
                if( CARRIER_PC != $intCarrier )
                {
                    ?>
                    <span style="color:white;">├<a href="./setup/easy_login.php<?= $arrayLogin["query_param"] ?>">かんたんログイン設定</a></span><br />
                    <?
                }
                ?>
                    <span style="color:white;">└<a href="./mypage/mail_receive_input.php<?= $arrayLogin["query_param"] ?>">更新メール受信設定</a></span><br />
            </div>
            <br /><br />
        </div>
    </div>
<?
}
