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
 * ================================================================================*/

$strPathHome = substr( $_SERVER['SCRIPT_FILENAME'], 0, strpos( $_SERVER['SCRIPT_FILENAME'], 'public_html' ) - 1 + strlen( "/" . 'public_html' . "/" ) );
$strPath = $strPathHome . "admin/";

// includeするライブラリ・コンフィグファイルのパスを定義する
define( 'LIB_DIR'   , $strPath . '_lib/');
define( 'CONFIG_DIR', $strPath . '_config/');
define( 'PAGE_SIZE' , '10');    // １ページに表示できるの数

include_once( LIB_DIR    . 'DataConvert.php' );         // データ変換用関数群
include_once( LIB_DIR    . 'UsersiteLayout.php' );      // ヘッダ・フッタ表示
include_once( LIB_DIR    . 'BbsDB.php' );               // BBS用
include_once( LIB_DIR    . 'UserDB.php' );              // ユーザ情報取得・更新関数群
include_once( CONFIG_DIR . 'title_conf.php' );          // ページタイトル定義ファイル
include_once( CONFIG_DIR . 'manage_conf.php' );         // 管理画面基本情報定義ファイル

$strSessionID = $_GET["s_id"];  // セッションID

// アクセスログを書き込む
AccessLog::WriteAccessLog( LOG_THREAD_FLAG );

$strThreadID = $_GET["thread_id"];

// アクセス許可されているユーザかどうかチェック
$intRetCode = BbsDB::CheckAuthThreadUser( $strSessionID, $strThreadID, $intUserRows, 0 );
if( true != $intRetCode )
{
    // 許可されていない場合はスレッド一覧へ
    header("Location:../thread_all.php". DOCOMO_GUID . "&s_id=" . $_GET["s_id"] );
}

// ヘッダ表示
$arrayLogin = UsersiteLayout::PrintHeaderMobileExt( "bgcolor=\"#000000\" text=\"#ffffff\" link=\"#ffffff\" VLINK=\"#ffffff\" ALINK=\"#000000\"" );
$strSessionID = $arrayLogin["s_id"];
main( $strSessionID, $arrayLogin, $strPathHome );
UsersiteLayout::PrintFooterMobileExt( $strSessionID );

function main( $strSessionID, $arrayLogin, $strPathHome )
{
    $strThreadID = $_GET["thread_id"];

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

    // ログイン者のログインID取得する
    $arrayUser =    UserDB::GetLoginUserOfSession(  $strSessionID   ,
                                                    &$intRows       );

    // スレッド情報取得
    $arrayThread = BbsDB::GetThreadOneAndGroup( $strThreadID, $intRows );
    $strTitle = $arrayThread[0]["thread_name"];
    $strBody  = $arrayThread[0]["thread_note"];
    unset( $arrayThread );

    // ****************************************
    // 総数取得
    // ****************************************

    // 全投稿数取得
    $arrayBbsAll = BbsDB::GetBbsAll( $strThreadID, $intRows );
    unset( $arrayBbsAll );

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
            <div style="background-color:#DC143C;width:100%;text-align:center;"><span style="color:white;"><?= DataConvert::MetatagEncode( $strTitle ); ?></span></div>
            <div style="text-align:left;"><span style="color:white;"><?= DataConvert::ReturnCodeToBR( DataConvert::MetatagEncode( $strBody ) ); ?></span></div>
        </div><hr size=1>
        <div style="text-align:left;"><span style="color:white;">&#63879;</span><a href="bbs_input.php<?= $arrayLogin["query_param"] . "&new_id=1&thread_id=" . $strThreadID . "&thread_name=" . urlencode( $strTitle ) ?>" accesskey="1">新規投稿する</a><hr size=1>
        <div style="text-align:left;"><span style="color:white;">&#63880;</span><a href="riyou.php<?= $arrayLogin["query_param"] . "&thread_id=" . $strThreadID ?>" accesskey="2">画像付きで投稿する</a><hr size=1>
        <div style="text-align:left;"><span style="color:white;">&#63881;</span><a href="pop_db.php<?= $arrayLogin["query_param"] . "&thread_id=" . $strThreadID ?>" accesskey="3">最新に更新する</a><hr size=1>
        <div style="text-align:left;">
            <?
            // 検索結果ゼロの場合
            if( 0 == $intRows )
            {
                ?>
                <br />
                <span style="color:white;">投稿がありません。</span>
                <?
            }
            else
            {
                // ページャー表示
                $strPager = StdFunc::WriteContents( $intRows            ,   // 総数
                                                    $intPageNo          ,   // 表示するページ番号
                                                    6                   ,   // 並び数(1 2 3)で３つ表示
                                                    PAGE_SIZE           ,   // １ページに表示するの数
                                                    "./mailbbs_i.php" . $arrayLogin["query_param"] . "&thread_id=" . $strThreadID ,  // ページ番号を除いたベースURL
                                                    $intStartRow        ,   // Limit開始行
                                                    $intEndRow          ,   // Limit終了行
                                                    "件"                );
                ?>
                <span style="color:white;"><?= $strPager ?></span>
                <br /><hr size="1">

                <?
                // 情報を取得する
                $arrayBbs   =   BbsDB::GetBbsLimit( $strThreadID    ,
                                                    $intStartRow    ,   // 取得開始レコード
                                                    PAGE_SIZE       ,   // 取得するレコード数
                                                    $intBbsCount   );   // 取得できたレコード数

                // 取得した情報を表示する
                $strBbs = "";
                for( $intCount = 0; $intCount < $intBbsCount; $intCount++ )
                {
                    // 投稿No.
                    $intNo = $intRows - $intCount;
                    if( 1 < $intPageNo )
                    {
                        // ページ番号が２以降の場合は、１ページに表示する件数をコメントナンバーから引く(こうしないと２ページ目、３ページ目なども件数が最大件数から数えられてしまう)
                        $intNo = $intNo - ( ( $intPageNo - 1 ) * PAGE_SIZE );
                    }

                    // 投稿者名
                    if( true == is_null( $arrayBbs[$intCount]["user_name"] ) || 0 == strlen( $arrayBbs[$intCount]["user_name"] ) )
                    {
                        $strUserName = "ゲスト投稿";
                    }
                    else
                    {
                        $strUserName = $arrayBbs[$intCount]["user_name"];
                    }

                    $strBbs =   $strBbs .
                                "<span style=\"color:white;\">[ " . $intNo ." ] </span><span style=\"color:#00BFFF;\">" . DataConvert::MetatagEncode( $arrayBbs[$intCount]["title"] ) . "</span><br />" .
                                "<span style=\"color:#F0E68C;\">" . $arrayBbs[$intCount]["date"] . "</span><span style=\"color:white;\">　" . DataConvert::MetatagEncode( $strUserName ) . "</span><br />" ;

                    // 画像がある場合
                    if( 0 < strlen( $arrayBbs[$intCount]["image_name"] ) )
                    {
                        // サムネイル確認
                        // (無い場合)
                        if( true != file_exists( $strPathHome . substr( PATH_IMAGE_BBS_SUMB, 1 ) . "/" . strtolower( $arrayBbs[$intCount]["image_name"] ) ) )
                        {
                            // 「/data」直下の画像をリンク
                            $strImageFilePath = PATH_IMAGE_BBS . "/" . strtolower( $arrayBbs[$intCount]["image_name"] );
                        }
                        // (ある場合)
                        else
                        {
                            // 「/data/s」配下の画像をリンク
                            $strImageFilePath = PATH_IMAGE_BBS_SUMB . "/" . strtolower( $arrayBbs[$intCount]["image_name"] );
                        }
                        $strBbs =   $strBbs .
                                    "<a href='" . $strImageFilePath . "'>[ 画像を表示する ]</a><br />\n";
                    }

                    $strBbs =   $strBbs .
                                "<span style=\"color:white;\">" . DataConvert::ReturnCodeToBR( DataConvert::MetatagEncode( $arrayBbs[$intCount]["body"] ) ) ."</span>\n" ;

                    // ログインユーザの投稿したコメントの場合は削除・編集を可能とする(管理者の場合も)
                    if( 0 == strcmp( $arrayUser["login_id"] , $arrayBbs[$intCount]["login_id"]  ) ||
                        0 == strcmp( "1"                    , $arrayUser["manage_auth"]         )   )
                    {
                        $strBbs =   $strBbs .
                                    "<br /><div style=\"text-align:right;\">".
                                    "<a href='./bbs_input.php"  .   $arrayLogin["query_param"] .
                                                                    "&date="        . $arrayBbs[$intCount]["date"] .
                                                                    "&login_id="    . $arrayBbs[$intCount]["login_id"] .
                                                                    "&manage_auth=" . $arrayUser["manage_auth"] .
                                                                    "&thread_id="   . $strThreadID .
                                    "'>[ 編集 ]</a>".
                                    "<a href='./bbs_delete_confirm.php" .
                                                                    $arrayLogin["query_param"] .
                                                                    "&date="        . $arrayBbs[$intCount]["date"] .
                                                                    "&login_id="    . $arrayBbs[$intCount]["login_id"] .
                                                                    "&manage_auth=" . $arrayUser["manage_auth"] .
                                                                    "&thread_id="   . $strThreadID .
                                    "'>[ 削除 ]</a><br /></div>\n";
                    }

                    $strBbs =   $strBbs . "<hr size=\"1\">\n" ;
                }
                ?>
                <?= $strBbs ?>
                <span style="color:white;"><?= $strPager ?></span>
                <hr size="1">
            <?
            }
            ?>
        </div>
        <div style="text-align:center;"><br /><br />
            <span style="color:white;">&#63882;[<a href="../thread_all.php<?= $arrayLogin["query_param"] ?>" accesskey="4">スレッド一覧へ</a>]</span><br />
            <span style="color:white;">&#63888;[<a href="../../top.php<?= $arrayLogin["query_param"] ?>" accesskey="0">トップへ</a>]</span><br /><br />
        </div><br />
    </div>
<?
}
