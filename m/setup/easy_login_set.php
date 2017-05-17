<?php
/* ================================================================================
 * ファイル名   ：easy_login_set.php
 * タイトル     ：かんたんログイン設定画面
 * 作成者       ：間宮 直樹
 * 作成日       ：2008/11/22
 * 内容         ：かんたんログインの設定／解除処理と処理結果
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
include_once( LIB_DIR    . 'UserDB.php' );              // ユーザ情報取得・更新関数群
include_once( CONFIG_DIR . 'title_conf.php' );          // ページタイトル定義ファイル
include_once( CONFIG_DIR . 'manage_conf.php' );         // 管理画面基本情報定義ファイル

$strSessionID = $_GET["s_id"];  // セッションID

// ヘッダ表示
$arrayLogin = UsersiteLayout::PrintHeaderMobileExt( "bgcolor=\"#000000\" text=\"#ffffff\" link=\"#ffffff\" VLINK=\"#ffffff\" ALINK=\"#000000\"" );
main( $strSessionID, $arrayLogin );
UsersiteLayout::PrintFooterMobileExt( $strSessionID );

function main( $strSessionID, $arrayLogin )
{
    // パラメータ
    $strEasyFlag    = $_GET["easy_flag"];

    // セッションIDからユーザ情報を取得
    $arrayUserInfo = UserDB::GetLoginUserOfSession( $strSessionID   ,
                                                    $intRows        );  // (OUT)取得件数（基本的に１件のみ）

    // (設定する、の場合)
    if( 0 == strcmp( "0", $strEasyFlag ) )
    {
        $strTitle = "かんたんログイン設定完了";

        // 端末IDをユーザ情報に設定する
        $intReturnCode  =   UserDB::UpdateMobileID( $arrayUserInfo["login_id"]  ,   // PK
                                                    0                           );  // 端末ID更新
    }
    else
    {
        $strTitle = "かんたんログイン解除完了";

        // 端末IDをユーザ情報から削除する
        $intReturnCode  =   UserDB::UpdateMobileID( $arrayUserInfo["login_id"]  ,   // PK
                                                    1                           );  // 端末ID削除
    }

    unset( $arrayUserInfo );

    // 処理結果判定
    // (エラー)
    if( 0 != $intReturnCode )
    {
        // 端末ID取得できない場合
        if( 9 == $intReturnCode )
        {
            $strMessage = "<span style=\"color:red;\">ご使用の携帯電話の端末IDが取得できませんでした。<br />携帯電話の端末IDの送信を「しない」に設定している場合は解除してから、再度かんたんログイン設定を実施してください。</span>";
        }
        else
        {
            $strMessage = "<span style=\"color:red;\">かんたんログイン設定に失敗しました。<br />管理者のかたにご連絡下さい。</span>";
        }
    }
    else
    {
        if( 0 == strcmp( "0", $strEasyFlag ) )
        {
            $strMessage = "かんたんログインを設定しました。<br />次回ログインからかんたんログインがご利用になれます。";
        }
        else
        {
            $strMessage = "かんたんログインを解除しました。<br />次回ログインからはログインID・パスワード入力でログインしてください。";
        }
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
            <div style="background-color:<?= COLOR_PAGE_TITLE_BGCOLOR ?>;width:100%;text-align:center;"><span style="color:white;"><?= $strTitle ?></span></div>
        </div>
        <div style="text-align:left;">
            <span style="color:white;"><?= $strMessage ?><br /></span>
            <hr size="1">
        </div>
        <div style="text-align:center;"><br /><br />
            <span style="color:white;">&#63888;[<a href="../top.php<?= $arrayLogin["query_param"] ?>" accesskey="0">トップへ</a>]</span><br /><br />
        </div><br />
    </div>
<?
}
