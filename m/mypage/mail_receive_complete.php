<?php
/* ================================================================================
 * ファイル名   ：mail_receive_complete.php
 * タイトル     ：更新メール受信設定完了ページ
 * 作成者       ：間宮 直樹
 * 作成日       ：2008/12/14
 * 内容         ：更新メール受信設定の入力完了画面。データベース登録を実施する。
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
include_once( LIB_DIR    . 'DataConvert.php' );         // データ変換用関数群
include_once( LIB_DIR    . 'MypageDB.php' );            // マイページ情報
include_once( CONFIG_DIR . 'title_conf.php' );          // ページタイトル定義ファイル
include_once( CONFIG_DIR . 'manage_conf.php' );         // 管理画面基本情報定義ファイル

/* ************************
 * メイン処理部
 * ************************/

$strSessionID = $_GET["s_id"];  // セッションID

// ヘッダ表示
$arrayLogin = UsersiteLayout::PrintHeaderMobileExt( "bgcolor=\"#000000\" text=\"#ffffff\" link=\"#ffffff\" VLINK=\"#ffffff\" ALINK=\"#000000\"" );
main( $strSessionID, $arrayLogin );
UsersiteLayout::PrintFooterMobileExt( $strSessionID );

function main( $strSessionID, $arrayLogin )
{
    $intReturnCode_Mypage = true;

    $strUserID          = $_GET["user_id"];
    $strReceiveMail_1   = DataConvert::cWrapTrim( $_GET["receive_mail_1"] );
    $strReceiveMail_2   = DataConvert::cWrapTrim( $_GET["receive_mail_2"] );
    $strReceiveMail_3   = DataConvert::cWrapTrim( $_GET["receive_mail_3"] );
    $arraySelectedAlbum = $_GET["album_id"];
    $arraySelectedThread= $_GET["thread_id"];

    // マイページ情報更新
    $intReturnCode_Mypage = MypageDB::SetMypageData(    $strUserID              ,   // ユーザID
                                                        $strReceiveMail_1       ,   // 受信メール１
                                                        $strReceiveMail_2       ,   // 受信メール２
                                                        $strReceiveMail_3       ,   // 受信メール３
                                                        $arraySelectedAlbum     ,   // 受信するフォトアルバムIDの配列
                                                        $arraySelectedThread    );  // 受信するスレッドIDの配列

    // エラー判定
    if( true != $intReturnCode_Mypage )
    {
        $strMessage = "<span style=\"color:red;\">設定に失敗しました。システム管理者に連絡してください。<br />";
    }
    else
    {
        $strMessage = "更新メール受信設定を完了しました。";
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
            <div style="background-color:<?= COLOR_PAGE_TITLE_BGCOLOR ?>;width:100%;text-align:center;"><span style="color:white;">更新メール受信設定完了</span></div>
        </div>
        <div style="text-align:left;">
            <span style="color:white;"><?= $strMessage ?></span><br /><br />
            <hr size="1" /><br />
        </div>
        <div style="text-align:center;"><br /><br />
            <span style="color:white;">&#63888;[<a href="../top.php<?= $arrayLogin["query_param"] ?>" accesskey="0">トップへ</a>]</span><br /><br />
        </div><br />
    </div>
    <?
}
