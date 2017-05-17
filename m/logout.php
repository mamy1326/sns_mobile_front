<?php
/* ================================================================================
 * ファイル名   ：logout.php
 * タイトル     ：ログアウト処理
 * 作成者       ：間宮 直樹
 * 作成日       ：2008/08/16
 * 更新履歴*******************************************************************
 * 【変更日】  【変更者】      【変更箇所】        【変更理由と変更内容】
 * ================================================================================*/

$strPath = substr( $_SERVER['SCRIPT_FILENAME'], 0, strpos( $_SERVER['SCRIPT_FILENAME'], 'public_html' ) - 1 + strlen( "/" . 'public_html' . "/" ) );
$strPath = $strPath . "admin/";

// includeするライブラリ・コンフィグファイルのパスを定義する
define( 'LIB_DIR'   , $strPath . '_lib/');
define( 'CONFIG_DIR', $strPath . '_config/');

// ライブラリ・コンフィグファイルをinclude
include_once( LIB_DIR    . 'StdFunc.php' );             // その他関数群
include_once( LIB_DIR    . 'UserDB.php' );              // ユーザ情報取得・更新関数群
include_once( CONFIG_DIR . 'manage_conf.php' );         // 管理画面基本情報定義ファイル

main();

function main()
{
    $strSessionID = $_GET["s_id"];

    // sessionデータ削除
    UserDB::DeleteSession( $strSessionID );

    // ログイン画面へ
    header( "Location:./index.php" . DOCOMO_GUID );
}
