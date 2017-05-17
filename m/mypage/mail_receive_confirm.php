<?php
/* ================================================================================
 * ファイル名   ：mail_receive_confirm.php
 * タイトル     ：更新メール受信設定確認ページ
 * 作成者       ：間宮 直樹
 * 作成日       ：2008/12/14
 * 内容         ：更新メール受信設定の入力確認画面
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
    $strReturnFlag  = $_GET["confirm_return"];

    $intNowDatetime = intval( date( 'Ymd' ) );

    $strUserID          = $_GET["user_id"];
    $strReceiveMail_1   = DataConvert::cWrapTrim( $_GET["receive_mail_1"] );
    $strReceiveMail_2   = DataConvert::cWrapTrim( $_GET["receive_mail_2"] );
    $strReceiveMail_3   = DataConvert::cWrapTrim( $_GET["receive_mail_3"] );
    $arraySelectedAlbum = $_GET["album_id"];
    $arrayAlbumID       = $_GET["album_id_org"];
    $arrayAlbumDate     = $_GET["album_date"];
    $arrayAlbumName     = $_GET["album_name"];
    $arraySelectedThread= $_GET["thread_id"];
    $arrayThreadID      = $_GET["thread_id_org"];
    $arrayThreadDate    = $_GET["thread_date"];
    $arrayThreadName    = $_GET["thread_name"];

    // 自動エンコードされる場合
    if( true == get_magic_quotes_gpc() )
    {
        // 存在するフォトアルバム数デコード
        if( true == is_array( $arrayAlbumName ) )
        {
            $arrayAlbumNameWork = array();
            for( $intCount = 0; $intCount < count( $arrayAlbumName ); $intCount++ )
            {
                // デコード(余計な\マークを除去)
                $arrayAlbumNameWork[] = stripslashes( $arrayAlbumName[ $intCount ] );
            }
            $arrayAlbumName = $arrayAlbumNameWork;
            unset( $arrayAlbumNameWork );
        }

        // 存在するスレッド数デコード
        if( true == is_array( $arrayThreadName ) )
        {
            $arrayThreadNameWork = array();
            for( $intCount = 0; $intCount < count( $arrayThreadName ); $intCount++ )
            {
                // デコード(余計な\マークを除去)
                $arrayThreadNameWork[] = stripslashes( $arrayThreadName[ $intCount ] );
            }
            $arrayThreadName = $arrayThreadNameWork;
            unset( $arrayThreadNameWork );
        }
    }

    // 入力メールを配列化
    $arrayReceiveMail = array();
    if( 0 < strlen( $strReceiveMail_1 ) )
    {
        $arrayReceiveMail[] = $strReceiveMail_1;
    }

    if( 0 < strlen( $strReceiveMail_2 ) )
    {
        $arrayReceiveMail[] = $strReceiveMail_2;
    }

    if( 0 < strlen( $strReceiveMail_3 ) )
    {
        $arrayReceiveMail[] = $strReceiveMail_3;
    }

    // *****************
    // 入力エラー処理
    // *****************
    $strErrorMessage = "";

    if( 0 < strlen( $arrayReceiveMail[0] ) )
    {
        // メールアドレス１
        $strErrorMessage =  $strErrorMessage .
                            StdFunc::InputCheckExpand(  $strReceiveMail_1   ,   // 予約人数
                                                        0,                      // 必須フラグ（必須）
                                                        1,                      // 最小長
                                                        200,                    // 最大長
                                                        0,                      // 固定長フラグ（可変長）
                                                        "【ﾒｰﾙｱﾄﾞﾚｽ1】",        // エラーメッセージ用タイトル
                                                        6,                      // 文字種（ﾒｰﾙｱﾄﾞﾚｽ）
                                                        $strErrorMessage );     // 現時点でのエラーメッセージ（あれば改行を追加する）
    }

    if( 0 < strlen( $arrayReceiveMail[1] ) )
    {
        // メールアドレス２
        $strErrorMessage =  $strErrorMessage .
                            StdFunc::InputCheckExpand(  $strReceiveMail_2   ,   // 予約人数
                                                        0,                      // 必須フラグ（必須）
                                                        1,                      // 最小長
                                                        200,                    // 最大長
                                                        0,                      // 固定長フラグ（可変長）
                                                        "【ﾒｰﾙｱﾄﾞﾚｽ2】",        // エラーメッセージ用タイトル
                                                        6,                      // 文字種（ﾒｰﾙｱﾄﾞﾚｽ）
                                                        $strErrorMessage );     // 現時点でのエラーメッセージ（あれば改行を追加する）
    }

    if( 0 < strlen( $arrayReceiveMail[2] ) )
    {
        // メールアドレス３
        $strErrorMessage =  $strErrorMessage .
                            StdFunc::InputCheckExpand(  $strReceiveMail_3   ,   // 予約人数
                                                        0,                      // 必須フラグ（必須）
                                                        1,                      // 最小長
                                                        200,                    // 最大長
                                                        0,                      // 固定長フラグ（可変長）
                                                        "【ﾒｰﾙｱﾄﾞﾚｽ3】",        // エラーメッセージ用タイトル
                                                        6,                      // 文字種（ﾒｰﾙｱﾄﾞﾚｽ）
                                                        $strErrorMessage );     // 現時点でのエラーメッセージ（あれば改行を追加する）
    }

    // メールアドレス入力なしはエラー
    if( 0 == count( $arrayReceiveMail ) )
    {
        $strErrorMessage =  $strErrorMessage .
                            "<br />【ﾒｰﾙｱﾄﾞﾚｽ】は1つ以上入力してください｡";
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
            <div style="background-color:<?= COLOR_PAGE_TITLE_BGCOLOR ?>;width:100%;text-align:center;"><span style="color:white;">更新メール受信設定確認</span></div>
        </div>
        <div style="text-align:left;">
            <?
            // エラーがある場合
            if( strlen( $strErrorMessage ) > 0 )
            {
                ?>
                <span style="color:white;">入力に誤りがありました。<br />以下のメッセージに従い修正してください。<br /></span><br />
                <span style="color:red;"  ><?= $strErrorMessage ?></span><br /><br />
                <?
            }
            else
            {
                ?>
                <span style="color:white;">以下の内容で設定します。<br />よろしければ「設定する」ボタンを押してください。</span><br /><br />
            <?
            }
            ?>
            <hr size="1" /><br />
            <div style="background-color:#C71585;width:100%;text-align:center;"><span style="color:white;">受信メールアドレス<br /></span></div>
            <span style="color:white;">1:<?= $arrayReceiveMail[0] ?></span><br />
            <span style="color:white;">2:<?= $arrayReceiveMail[1] ?></span><br />
            <span style="color:white;">3:<?= $arrayReceiveMail[2] ?></span><br />
            <hr size="1" /><br />
            <div style="background-color:#A0522D;width:100%;text-align:center;"><span style="color:white;">フォトアルバム<br /></span></div>
            <?
                for( $intCount = 0; $intCount < count( $arrayAlbumID ); $intCount++ )
                {
                    // チェックされているか判定
                    $strAlbumChecked = "";

                    // 選択されているアルバムの場合はチェック
                    if( true == is_array( $arraySelectedAlbum ) )
                    {
                        if( true == in_array( $arrayAlbumID[$intCount], $arraySelectedAlbum ) )
                        {
                            $strAlbumChecked = "<span style=\"color:red;\">　⇒</span><span style=\"color:#FFFF00;\">受信する</span>";
                        }
                        else
                        {
                            $strAlbumChecked = "<span style=\"color:red;\">　⇒</span><span style=\"color:#A9A9A9;\">受信しない</span>";
                        }
                    }

                    ?>
                    <div mode="nowrap">
                        <span style="color:#F0E68C;"><?= $arrayAlbumDate[$intCount] ?></span><br />
                        <span style="color:white;"><?= DataConvert::MetatagEncode( $arrayAlbumName[$intCount] ); ?></span><br />
                        <?= $strAlbumChecked ?><br />
                    </div>
                    <?
                }
            ?>
            <hr size="1" /><br />
            <div style="background-color:#0000CD;width:100%;text-align:center;"><span style="color:white;">ＢＢＳスレッド<br /></span></div>
            <span style="color:white;">チェックして更新すると、次回更新時からお知らせメールを受信できます。<br /></span>
            <?
                for( $intCount = 0; $intCount < count( $arrayThreadID ); $intCount++ )
                {
                    // チェックされているか判定
                    $strThreadChecked = "";

                    // 選択されているスレッドの場合はチェック
                    if( true == is_array( $arraySelectedThread ) )
                    {
                        if( true == in_array( $arrayThreadID[$intCount], $arraySelectedThread ) )
                        {
                            $strThreadChecked = "<span style=\"color:red;\">　⇒</span><span style=\"color:#FFFF00;\">受信する</span>";
                        }
                        else
                        {
                            $strThreadChecked = "<span style=\"color:red;\">　⇒</span><span style=\"color:#A9A9A9;\">受信しない</span>";
                        }
                    }

                    ?>
                    <div mode="nowrap">
                        <span style="color:#F0E68C;"><?= $arrayThreadDate[$intCount] ?></span><br />
                        <span style="color:white;"><?= DataConvert::MetatagEncode( $arrayThreadName[$intCount] ); ?></span><br />
                        <?= $strThreadChecked ?><br />
                    </div>
                    <?
                }
            ?>
            <hr size="1" />
            <div style="text-align:center;vartical-align:middle;">
            <?
            // エラーがない場合
            if( strlen( $strErrorMessage ) == 0 )
            {
                ?>
                <form action="./mail_receive_complete.php<?= DOCOMO_GUID ?>" method="GET" name="mail_receive_complete_form">
                    <?= DOCOMO_GUID_HIDDEN ?>
                    <input type="hidden" name="s_id"    value="<?= $strSessionID ?>">
                    <input type="hidden" name="user_id" value="<?= $strUserID ?>">

                    <?
                    // メールアドレス展開
                    for( $intCount = 0; $intCount < count( $arrayReceiveMail ); $intCount++ )
                    {
                        $intMailNo = $intCount + 1;
                        ?>
                        <input type="hidden" name="receive_mail_<?= $intMailNo ?>"  value="<?= $arrayReceiveMail[ $intCount ] ?>">
                        <?
                    }

                    // 選択フォトアルバムID展開
                    for( $intCount = 0; $intCount < count( $arraySelectedAlbum ); $intCount++ )
                    {
                        ?>
                        <input type="hidden" name="album_id[]"  value="<?= $arraySelectedAlbum[ $intCount ] ?>">
                        <?
                    }

                    // 選択スレッドID展開
                    for( $intCount = 0; $intCount < count( $arraySelectedThread ); $intCount++ )
                    {
                        ?>
                        <input type="hidden" name="thread_id[]" value="<?= $arraySelectedThread[ $intCount ] ?>">
                        <?
                    }
                    ?>
                    <div style="text-align:center;"><span style="color:white;">&#63884;</span><input type="submit" name="mail_receive_complete" accesskey="6" value="設定する"></div>
                </form>
            <?
            }
            ?>
            <form action="./mail_receive_input.php<?= DOCOMO_GUID ?>" method="GET" name="mail_receive_input_form">
                <?= DOCOMO_GUID_HIDDEN ?>
                <input type="hidden" name="s_id"            value="<?= $strSessionID ?>">
                <input type="hidden" name="user_id"         value="<?= $strUserID ?>">
                <input type="hidden" name="confirm_return"  value="1">

                <?
                // メールアドレス展開
                for( $intCount = 0; $intCount < count( $arrayReceiveMail ); $intCount++ )
                {
                    $intMailNo = $intCount + 1;
                    ?>
                    <input type="hidden" name="receive_mail_<?= $intMailNo ?>"  value="<?= $arrayReceiveMail[ $intCount ] ?>">
                    <?
                }

                // 選択フォトアルバムID展開
                for( $intCount = 0; $intCount < count( $arraySelectedAlbum ); $intCount++ )
                {
                    ?>
                    <input type="hidden" name="album_id[]"  value="<?= $arraySelectedAlbum[ $intCount ] ?>">
                    <?
                }

                // 選択スレッドID展開
                for( $intCount = 0; $intCount < count( $arraySelectedThread ); $intCount++ )
                {
                    ?>
                    <input type="hidden" name="thread_id[]" value="<?= $arraySelectedThread[ $intCount ] ?>">
                    <?
                }
                ?>
                <div style="text-align:center;"><span style="color:white;">&#63882;</span><input type="submit" name="mail_receive_input" accesskey="4" value="　戻る　"></div>
            </form>
            </div>
            <hr size="1" />
        </div>
        <div style="text-align:center;"><br /><br />
            <span style="color:white;">&#63888;[<a href="../top.php<?= $arrayLogin["query_param"] ?>" accesskey="0">トップへ</a>]</span><br /><br />
        </div><br />
    </div>
    <?
}
