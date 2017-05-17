<?php
/* ================================================================================
 * ファイル名   ：info_detail.php
 * タイトル     ：管理本部通信　詳細表示画面
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

include_once( LIB_DIR    . 'DataConvert.php' );         // データ変換用関数群
include_once( LIB_DIR    . 'InfoDB.php' );              // Information情報
include_once( LIB_DIR    . 'UsersiteLayout.php' );      // ヘッダ・フッタ表示
include_once( LIB_DIR    . 'AccessLog.php' );           // アクセスログ関数群
include_once( CONFIG_DIR . 'title_conf.php' );          // ページタイトル定義ファイル
include_once( CONFIG_DIR . 'manage_conf.php' );         // 管理画面基本情報定義ファイル

$strSessionID = $_GET["s_id"];  // セッションID

// ヘッダ表示
$arrayLogin = UsersiteLayout::PrintHeaderMobileExt( "bgcolor=\"#000000\" text=\"#ffffff\" link=\"#ffffff\" VLINK=\"#ffffff\" ALINK=\"#000000\"" );
main( $strSessionID, $arrayLogin );
UsersiteLayout::PrintFooterMobileExt( $strSessionID );

function main( $strSessionID, $arrayLogin )
{
    // InformationID
    $intInfoID = intval( $_GET["id"] );

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

    // Information情報を取得する
    $arrayInfo =    InfoDB::GetInfoOne( $intInfoID      ,   // InformationID
                                        &$intRows       );  // 取得できたレコード数

    // アクセスログを書き込む
    AccessLog::WriteAccessLog( LOG_INFO_FLAG );

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
            <div style="background-color:#EE82EE;width:100%;text-align:center;"><span style="color:white;"><?= DataConvert::MetatagEncode( $arrayInfo["info_title"] ) ?></span></div>
        </div>
        <div style="text-align:left;">
            <span style="color:white;"><?= $arrayInfo["info_date_yyyy"] . "年&nbsp;" . $arrayInfo["info_date_mm"] . "月&nbsp;" . $arrayInfo["info_date_dd"] . "日" ?></span><br />
            <?
            // モバイルの場合のみボタン表示
            if( 0 != $intPc_or_Mobile )
            {
                ?>
                <div style="text-align:center;">
                        <form action="./info_detail.php" method="GET" name="image_show">
                            <?= DOCOMO_GUID_HIDDEN ?>
                            <input type="hidden" name="s_id"        value="<?= $strSessionID ?>">
                            <input type="hidden" name="image_id"    value="<?= $strParamImageID ?>" />
                            <input type="hidden" name="id"          value="<?= $intInfoID ?>" />
                            <input type="submit" name="Submit"      value="<?= $strImageButtonName ?>">
                        </form>
                    <hr size="1">
                </div>
                <?
            }
            ?>
            <span style="color:white;"><?= InfoDB::ConvertInfoImage( $arrayInfo[ "info_body" ], $intPc_or_Mobile, $strImageFlag ); ?><hr size="1"></span>
        </div>
        <div style="text-align:center;"><br /><br />
            <span style="color:white;">&#63887;[<a href="./info_all.php<?= $arrayLogin["query_param"] ?>" accesskey="9">一覧へ</a>]</span><br />
            <span style="color:white;">&#63888;[<a href="../top.php<?= $arrayLogin["query_param"] ?>" accesskey="0">トップへ</a>]</span><br /><br />
        </div><br />
    </div>
<?
}
