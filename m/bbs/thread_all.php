<?php
/* ================================================================================
 * ファイル名   ：thread_all.php
 * タイトル     ：スレッド一覧
 * 作成者       ：間宮 直樹
 * 作成日       ：2008/12/01
 * 内容         ：スレッドの一覧を表示する。１ページに１０件まで。
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
include_once( LIB_DIR    . 'BbsDB.php' );               // BBSスレッド情報取得・更新関数群
include_once( CONFIG_DIR . 'title_conf.php' );          // ページタイトル定義ファイル
include_once( CONFIG_DIR . 'manage_conf.php' );         // 管理画面基本情報定義ファイル

$strSessionID = $_GET["s_id"];  // セッションID

// ヘッダ表示
$arrayLogin = UsersiteLayout::PrintHeaderMobileExt( "bgcolor=\"#000000\" text=\"#ffffff\" link=\"#ffffff\" VLINK=\"#ffffff\" ALINK=\"#000000\"" );
main( $strSessionID, $arrayLogin );
UsersiteLayout::PrintFooterMobileExt( $strSessionID );

function main( $strSessionID, $arrayLogin )
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

    // ****************************************
    // 総数取得
    // ****************************************

    // スレッド総数取得
    $arrayThread = BbsDB::GetThreadHome(    0                   ,   // 取得開始レコード
                                            PAGE_SIZE           ,   // 取得するレコード数
                                            $intThreadCountAll  ,   // 取得できたレコード数
                                            1                   );  // LIMITなし
    unset( $arrayThread );

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
            <div style="background-color:<?= COLOR_PAGE_TITLE_BGCOLOR ?>;width:100%;text-align:center;"><span style="color:white;">ＢＢＳスレッド　一覧</span></div><br />
        </div>
        <div style="text-align:left;">
            <?
            // 検索結果ゼロの場合
            if( 0 == $intThreadCountAll )
            {
                ?>
                <br />
                <span style="color:white;">公開されているスレッドはありません。</span>
                <?
            }
            else
            {
                // ページャー表示
                $strPager = StdFunc::WriteContents( $intThreadCountAll  ,   // 総数
                                                    $intPageNo          ,   // 表示するページ番号
                                                    3                   ,   // 並び数(1 2 3)で３つ表示
                                                    PAGE_SIZE           ,   // １ページに表示するの数
                                                    "./thread_all.php" . $arrayLogin["query_param"] ,  // ページ番号を除いたベースURL
                                                    $intStartRow        ,   // Limit開始行
                                                    $intEndRow          ,   // Limit終了行
                                                    "ｽﾚｯﾄﾞ"             );
                ?>
                <span style="color:white;"><?= $strPager ?></span>
                <br /><br /><hr size="1">

                <?
                // 情報を取得する
                $arrayThread = BbsDB::GetThreadHome(    $intStartRow    ,   // 取得開始レコード
                                                        PAGE_SIZE       ,   // 取得するレコード数
                                                        $intThreadCount ,   // 取得できたレコード数
                                                        0               );  // LIMITあり

                // 取得した情報を表示する
                $strThread = "";
                $intNowDatetime = intval( date( 'Ymd' ) );
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

                    $strThread =   $strThread .
                                        "<div mode=\"nowrap\">" .
                                        "<span style=\"color:#F0E68C;\">[" . $strShowDate . "]</span><br />" .
                                        "<span style=\"color:white;\"><a href=\"./mailbbs_gd/mailbbs_i.php" . $arrayLogin["query_param"] . "&thread_id=" . $arrayThread[$intCount]["thread_id"] . "\">" . DataConvert::MetatagEncode( $arrayThread[$intCount]["thread_name"] ) . "(" . $arrayThread[$intCount]["comment_count"] . ")</a></span>" .
                                        "</div>\n";
                }
                ?>
                <?= $strThread ?>
                <br />
                <hr size="1">
                <span style="color:white;"><?= $strPager ?></span>
                <hr size="1">
            <?
            }
            ?>
        </div>
        <div style="text-align:center;"><br /><br />
            <span style="color:white;">&#63888;[<a href="../top.php<?= $arrayLogin["query_param"] ?>" accesskey="0">トップへ</a>]</span><br /><br />
        </div><br />
    </div>
<?
}
