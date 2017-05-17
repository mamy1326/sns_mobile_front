<?php
/* ================================================================================
 * ファイル名   ：check.php
 * タイトル     ：ログイン認証
 * 作成者       ：間宮 直樹
 * 作成日       ：2008/11/12
 * 更新履歴*******************************************************************
 * 【変更日】  【変更者】      【変更箇所】        【変更理由と変更内容】
 * ================================================================================*/

$strPath = substr( $_SERVER['SCRIPT_FILENAME'], 0, strpos( $_SERVER['SCRIPT_FILENAME'], 'public_html' ) - 1 + strlen( "/" . 'public_html' . "/" ) );
$strPath = $strPath . "admin/";

// includeするライブラリ・コンフィグファイルのパスを定義する
define( 'LIB_DIR'   , $strPath . '_lib/');
define( 'CONFIG_DIR', $strPath . '_config/');

// ライブラリ・コンフィグファイルをinclude
include_once( LIB_DIR    . 'DataConvert.php' );         // データ変換用関数群
include_once( LIB_DIR    . 'UserDB.php' );              // ユーザ情報取得・更新関数群
include_once( LIB_DIR    . 'MasterDB.php' );            // ステータスマスタ情報取得・更新関数群
include_once( CONFIG_DIR . 'title_conf.php' );          // ページタイトル定義ファイル
include_once( CONFIG_DIR . 'manage_conf.php' );         // 管理画面基本情報定義ファイル


    // POSTデータを変数に格納
    $strID      = $_POST["loginid"];
    $strPWD     = $_POST["loginpass"];
    $strEasy    = $_POST["easy_login"];

    // かんたんログインの場合はID認証
    if( 0 < strlen( $strEasy ) )
    {
        $arrayLoginUser =   UserDB::GetLoginUserByMobileID( $intRows    );
        if( 0 < $intRows )
        {
            $strID = $arrayLoginUser["login_id"];
        }
    }
    // ログイン認証データ取得
    else
    {
        $arrayLoginUser =   UserDB::GetLoginUser(   $strID      ,
                                                    $strPWD     ,
                                                    LOGIN_FRONT ,
                                                    $intRows    );
    }

    // 認証OK
    if( 0 != $intRows )
    {
        // ログインID＋PW＋ログイン年月日時分秒、の文字列をMD5化し、セッションIDとする
        $strSessionID = md5( $strID . $strPWD . date( 'YnjHis' ) );

        // セッション情報を更新する
        $intReturnCode  =   UserDB::UpdateSessionOfLogin(   $strID          ,
                                                            $strSessionID   );

        // セッションの更新が正常終了
        if( true == $intReturnCode )
        {
            // トップページへリダイレクト
            header( "Location:./top.php" . DOCOMO_GUID . "&s_id=" . $strSessionID );
        }
        else
        {
            // ログインページへリダイレクト
            header( "Location:./index.php" . DOCOMO_GUID . "&error=1&code=" . $intReturnCode );
        }
        exit;
    }
    // 認証NG
    else
    {
        // エラーコード設定
        if( 0 < strlen( $strEasy ) )
        {
            $strErrorCode = "2";
        }
        else
        {
            $strErrorCode = "1";
        }

        // ログインページへリダイレクト
        header( "Location:./index.php" . DOCOMO_GUID . "&error=" . $strErrorCode );
        exit;
    }
