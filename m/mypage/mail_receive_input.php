<?php
/* ================================================================================
 * ファイル名   ：mail_receive_input.php
 * タイトル     ：受信メール設定画面
 * 作成者       ：間宮 直樹
 * 作成日       ：2008/12/13
 * 内容         ：スレッド・フォトアルバムが更新された際のメール受信設定入力画面
 * 更新履歴*******************************************************************
 * 【変更日】  【変更者】      【変更箇所】        【変更理由と変更内容】
 * ================================================================================*/

$strPath = substr( $_SERVER['SCRIPT_FILENAME'], 0, strpos( $_SERVER['SCRIPT_FILENAME'], 'public_html' ) - 1 + strlen( "/" . 'public_html' . "/" ) );
$strPath = $strPath . "admin/";

// includeするライブラリ・コンフィグファイルのパスを定義する
define( 'LIB_DIR'   , $strPath . '_lib/');
define( 'CONFIG_DIR', $strPath . '_config/');

include_once( LIB_DIR    . 'DataConvert.php' );         // データ変換用関数群
include_once( LIB_DIR    . 'MypageDB.php' );            // マイページ情報
include_once( LIB_DIR    . 'UsersiteLayout.php' );      // ヘッダ・フッタ表示
include_once( LIB_DIR    . 'AccessLog.php' );           // アクセスログ関数群
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
    $strReturnFlag  = $_GET["confirm_return"];

    $intNowDatetime = intval( date( 'Ymd' ) );

    // 既存で戻りではない場合はＤＢから
    if( 0 == strlen( $strReturnFlag ) )
    {
        // マイページ情報を取得する
        $arrayMypage = MypageDB::GetMypageOne( $strSessionID, $intMypageRows );
        $strUserID          = $arrayMypage["user_id"];
        $strReceiveMail_1   = $arrayMypage["receive_mail_1"];
        $strReceiveMail_2   = $arrayMypage["receive_mail_2"];
        $strReceiveMail_3   = $arrayMypage["receive_mail_3"];
    }
    else
    {
        $strUserID          = $_GET["user_id"];
        $strReceiveMail_1   = $_GET["receive_mail_1"];
        $strReceiveMail_2   = $_GET["receive_mail_2"];
        $strReceiveMail_3   = $_GET["receive_mail_3"];
        $arraySelectedAlbum = $_GET["album_id"];
        $arraySelectedThread= $_GET["thread_id"];
    }

    // ユーザの参照できるフォトアルバムを取得する（一緒にメール受信設定情報も取得する）
    $arrayPhotoAlbum = PhotoDB::GetPhotoMypage( $strUserID      ,
                                                0               ,   // 取得開始レコード
                                                5               ,   // 取得するレコード数
                                                $intPhotoCount  ,   // 取得できたレコード数
                                                1               );  // LIMIT なし

    // ユーザの参照できるスレッドを取得する（一緒にメール受信設定情報も取得する）
    $arrayThread = BbsDB::GetThreadMypage(  $strUserID      ,
                                            0               ,   // 取得開始レコード
                                            5               ,   // 取得するレコード数
                                            $intThreadCount ,   // 取得できたレコード数
                                            1               );  // LIMIT なし

    //キャリア取得
    $intCarrier       = StdFunc::GetAgentNumber();

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
            <div style="background-color:<?= COLOR_PAGE_TITLE_BGCOLOR ?>;width:100%;text-align:center;"><span style="color:white;">更新メール受信設定</span></div>
        </div>
        <div style="text-align:left;">
            <form action="./mail_receive_confirm.php<?= DOCOMO_GUID ?>" method="GET" name="mail_receive">
                <?= DOCOMO_GUID_HIDDEN ?>
                <input type="hidden" name="s_id"    value="<?= $strSessionID ?>">
                <input type="hidden" name="user_id" value="<?= $strUserID ?>">

                <span style="color:white;">フォトアルバム、BBSスレッドに新規投稿があった場合、指定のメールアドレスでお知らせメールを受信できます。<br />メール受信を希望するフォトアルバム、BBSスレッドを選択して設定してください。<br /></span>
                <hr size="1" /><br />
                <div style="background-color:#C71585;width:100%;text-align:center;"><span style="color:white;">受信メールアドレス<br /></span></div>
                <span style="color:white;">受信を希望するメールアドレスを最大３つまで登録できます。<br /></span>
                　<input name="receive_mail_1" type="text" size="20" maxlength="200" value="<?= $strReceiveMail_1 ?>" <?= StdFunc::GetInputTagAttr( ATTR_ALPHABET , 200 , $intCarrier, FONT_SIZE_ALL ) ?> /><br />
                　<input name="receive_mail_2" type="text" size="20" maxlength="200" value="<?= $strReceiveMail_2 ?>" <?= StdFunc::GetInputTagAttr( ATTR_ALPHABET , 200 , $intCarrier, FONT_SIZE_ALL ) ?> /><br />
                　<input name="receive_mail_3" type="text" size="20" maxlength="200" value="<?= $strReceiveMail_3 ?>" <?= StdFunc::GetInputTagAttr( ATTR_ALPHABET , 200 , $intCarrier, FONT_SIZE_ALL ) ?> /><br />
                <span style="color:red;">※ケータイのアドレスを入力する場合、ケータイのドメイン指定受信を設定している方は<input type="text" name="domain" value="info@vix.co.jp" size="<?= strlen( "info@vix.co.jp" ); ?>" maxlength="<?= strlen( "info@vix.co.jp" ); ?>" <?= StdFunc::GetInputTagAttr( ATTR_ALPHABET , strlen( "info@vix.co.jp" ) , $intCarrier, FONT_SIZE_ALL ) ?> />をコピーしてメールを受信できるように設定してください。<br /><?= StdFunc::MobileMailSetting( $intCarrier ) ?></span><br /><br />
                <hr size="1" /><br />
                <div style="background-color:#A0522D;width:100%;text-align:center;"><span style="color:white;">フォトアルバム<br /></span></div>
                <span style="color:white;">チェックして更新すると、次回更新時からお知らせメールを受信できます。<br /></span>
                <?
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

                        // チェックされているか判定
                        $strAlbumChecked = "";
                        // DB取得の場合
                        if( 0 == strlen( $strReturnFlag ) )
                        {
                            // 選択されているアルバムの場合はチェック
                            if( 0 < strlen( $arrayPhotoAlbum[$intCount]["user_id"] ) )
                            {
                                $strAlbumChecked = " checked ";
                            }
                            else
                            {
                                $strAlbumChecked = "";
                            }
                        }
                        // 戻りの場合
                        else
                        {
                            // 選択されているアルバムの場合はチェック
                            if( true == is_array( $arraySelectedAlbum ) )
                            {
                                if( true == in_array( $arrayPhotoAlbum[$intCount]["album_id"], $arraySelectedAlbum ) )
                                {
                                    $strAlbumChecked = " checked ";
                                }
                                else
                                {
                                    $strAlbumChecked = "";
                                }
                            }
                        }

                        ?>
                        <div mode="nowrap">
                        <span style="color:#F0E68C;">[<?= $strShowDate ?>]</span><br />
                        <input type="checkbox" value="<?= $arrayPhotoAlbum[$intCount]["album_id"] ?>" name="album_id[]" <?= $strAlbumChecked ?>><span style="color:white;"><a href="../photo/album_detail.php<?= $arrayLogin["query_param"] ?>&album_id=<?= $arrayPhotoAlbum[$intCount]["album_id"] ?>"><?= DataConvert::MetatagEncode( $arrayPhotoAlbum[$intCount]["album_name"] ) ?>(<?= $arrayPhotoAlbum[$intCount]["image_count"]?>)</a></span>
                        <input type="hidden" name="album_id_org[]"  value="<?= $arrayPhotoAlbum[$intCount]["album_id"] ?>">
                        <input type="hidden" name="album_date[]"    value="[<?= $strShowDate ?>]">
                        <input type="hidden" name="album_name[]"    value="<?= DataConvert::MetatagEncode( $arrayPhotoAlbum[$intCount]["album_name"] ) ?>(<?= $arrayPhotoAlbum[$intCount]["image_count"]?>)">
                        </div>
                        <?
                    }
                ?>
                <hr size="1" /><br />
                <div style="background-color:#0000CD;width:100%;text-align:center;"><span style="color:white;">ＢＢＳスレッド<br /></span></div>
                <span style="color:white;">チェックして更新すると、次回更新時からお知らせメールを受信できます。<br /></span>
                <?
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

                        // チェックされているか判定
                        $strThreadChecked = "";
                        // DB取得の場合
                        if( 0 == strlen( $strReturnFlag ) )
                        {
                            if( 0 < strlen( $arrayThread[$intCount]["user_id"] ) )
                            {
                                $strThreadChecked = " checked ";
                            }
                            else
                            {
                                $strThreadChecked = "";
                            }
                        }
                        // 戻りの場合
                        else
                        {
                            // 選択されているアルバムの場合はチェック
                            if( true == is_array( $arraySelectedThread ) )
                            {
                                if( true == in_array( $arrayThread[$intCount]["thread_id"], $arraySelectedThread ) )
                                {
                                    $strThreadChecked = " checked ";
                                }
                                else
                                {
                                    $strThreadChecked = "";
                                }
                            }
                        }

                        ?>
                        <div mode="nowrap">
                        <span style="color:#F0E68C;">[<?= $strShowDate ?>]</span><br />
                        <input type="checkbox" value="<?= $arrayThread[$intCount]["thread_id"] ?>" name="thread_id[]" <?= $strThreadChecked ?>><span style="color:white;"><a href="../bbs/mailbbs_gd/mailbbs_i.php<?= $arrayLogin["query_param"] ?>&thread_id=<?= $arrayThread[$intCount]["thread_id"] ?>"><?= DataConvert::MetatagEncode( $arrayThread[$intCount]["thread_name"] ) ?>(<?= $arrayThread[$intCount]["comment_count"] ?>)</a></span>
                        <input type="hidden" name="thread_id_org[]"  value="<?= $arrayThread[$intCount]["thread_id"] ?>">
                        <input type="hidden" name="thread_date[]"    value="[<?= $strShowDate ?>]">
                        <input type="hidden" name="thread_name[]"    value="<?= DataConvert::MetatagEncode( $arrayThread[$intCount]["thread_name"] ) ?>(<?= $arrayThread[$intCount]["comment_count"]?>)">
                        </div>
                        <?
                    }
                ?>
                <hr size="1" />
                <div style="text-align:center;vartical-align:middle;">
                    <input type="submit" style="<?= StdFunc::GetFontSizeOfCarrier( FONT_SIZE_ALL ) ?>" name="Submit" value="設定確認" accesskey="6"><span style="color:white;">&#63884;</span>
                </div>
                <hr size="1" />
            </form>
        </div>
        <div style="text-align:center;"><br /><br />
            <span style="color:white;">&#63888;[<a href="../top.php<?= $arrayLogin["query_param"] ?>" accesskey="0">トップへ</a>]</span><br /><br />
        </div><br />
    </div>
<?
}
