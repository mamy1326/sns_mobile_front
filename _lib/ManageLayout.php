<?php
/* ================================================================================
 * ファイル名   ：ManageLayout.php
 * タイトル     ：管理画面各種レイアウト表示クラス
 * 作成者       ：間宮 直樹
 * 作成日       ：2008/08/05
 * 内容         ：管理画面表示を制御するクラス
 * 更新履歴*******************************************************************
 * 【変更日】  【変更者】      【変更箇所】        【変更理由と変更内容】
 *  2008/08/05  間宮 直樹       全体                新規作成
 * ================================================================================*/

$strPath = substr( $_SERVER['SCRIPT_FILENAME'], 0, strpos( $_SERVER['SCRIPT_FILENAME'], 'public_html' ) - 1 + strlen( "/" . 'public_html' . "/" ) );
$strPath = $strPath . "admin/";

// includeするライブラリ・コンフィグファイルのパスを定義する
define( 'LIB_DIR'   , $strPath . '_lib/');
define( 'CONFIG_DIR', $strPath . '_config/');

// ライブラリ・コンフィグファイルをinclude
include_once( CONFIG_DIR . 'title_conf.php' );          // ページタイトル定義ファイル
include_once( CONFIG_DIR . 'manage_conf.php' );         // 管理画面種別定義ファイル
include_once( LIB_DIR    . 'DataConvert.php' );         // データ変換用関数群


class ManageLayout
{
    // ***********************************************************************
    // * 関数名     ：ヘッダ表示
    // * 機能概要   ：ヘッダ部のHTMLを表示する。
    // * 引数       ：IN ：?@タイトル文字列
    // *              IN ：?A表示メニュー種別
    // *              IN ：?B個別適用CSS(無い場合はnull)
    // *              IN ：?C個別適用JS(無い場合はnull)
    // * 返り値     ：ヘッダHTMLタグ
    // ***********************************************************************
    function PrintHeader( $strTitle, $intMenuFlag = 0, $strCss = null, $strJs = null, $intMenuShowFlag = true )
    {
        // 管理画面ログイン認証実施(タブ有り＝ログイン画面以外すべてで認証必須)
        if( true == $intMenuShowFlag )
        {
            if( ( "" == $_COOKIE[ md5( COOKIE_VIX_LOGIN_ID )] ) || ( is_null( $_COOKIE[ md5( COOKIE_VIX_LOGIN_ID ) ] ) ) )
            {
                header( "Location:/admin/manage/index.php");
            }
        }

        // 個別スタイルシートがある場合は設定
        $strCssTag = "";
        if( true != is_null( $strCss ) && 0 < strlen( $strCss ) )
        {
            $strCssTag = "<link href='/admin/css/" . $strCss . "' rel='stylesheet' type='text/css' />¥n";
        }

        // 個別javascriptファイルがある場合は設定
        $strJsTag = "";
        if( true != is_null( $strJs ) && 0 < strlen( $strJs ) )
        {
            $strJsTag = "<script type='text/javascript' src='/admin/js/" . $strJs . "'></script>¥n";
        }

        ?>
        <!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
        <html lang="ja">
            <head>
                <meta http-equiv="Content-Type" content="text/html; charset=utf8" />
                <script type="text/javascript" src="/admin/js/PushSubmit.js"></script>
                <script type="text/javascript" src="/admin/js/StandardFunction.js"></script>
                <?= $strJsTag ?>
                <link href="/admin/manage/css/manage_style.css" rel="stylesheet" type="text/css" />
                <link href="/admin/manage/css/header_menu.css" rel="stylesheet" type="text/css" />
                <link href="/admin/manage/css/tab.css" rel="stylesheet" type="text/css" />
                <?= $strCssTag ?>
                <title><?= $strTitle ?></title>
            </head>

            <body topmargin="0" leftmargin="0">
                <table width="100%" height="100%" border="0" cellspacing="1" cellpadding="1" border="0">
                    <?
                    // ヘッダありのページ（ポップアップ以外など）の場合
                    if( 0 == strlen( $_GET["window_flag"] ) && 0 == strlen( $_POST["window_flag"] ) )
                    {
                        // ログインユーザ名取得
                        $arrayUser = UserDB::GetUserOfLoginID( $_COOKIE[ md5( COOKIE_LOGIN_ID ) ], $intRows );
                    ?>
                    <tr>
                        <td colspan="4" height="39">
                            <!--ロゴクリック時はユーザ情報ページへ-->
                            <table border="0" cellspacing="0" width="100%" height="39" cellpadding="0">
                                <tr rowspan="2">
                                    <td align="left" ><a href="/admin/manage/user/index.php"><img src="/admin/manage/img/logo.jpg" border="0" /></a></td>
                                    <?
                                    if( 0 < strlen( $_COOKIE[ md5( COOKIE_VIX_LOGIN_ID ) ] ) )
                                    {
                                        ?>
                                        <td align="right"><font size="2"><a href="/admin/manage/logout.php">ログアウト</a><br><br>お疲れさまです。<br><font size="2" color="#"><?= DataConvert::MetatagEncode( $arrayUser["user_name"] ); ?></font>さん</font></td>
                                        <?
                                    }
                                    ?>
                                </tr>
                            </table>

                            <?
                            // メニューありの場合
                            if( true == $intMenuShowFlag )
                            {
                                ?>
                                <!--タブメニュー部-->
                                <table border="0" cellspacing="0" width="100%" cellpadding="0">
                                    <tr>
                                        <td height="21">
                                            <table border="0" cellspacing="0" cellpadding="0" class="tab">
                                                <tr>
                                                  <?php
                                                    ManageLayout::printTab( $intMenuFlag, HEAD_MANAGE_USER      , TITLE_USER_INDEX      , '/admin/manage/user/index.php' );
                                                    ManageLayout::printTab( $intMenuFlag, HEAD_MANAGE_USER_GROUP, TITLE_USERGROUP_INDEX , '/admin/manage/user_group/index.php' );
                                                    ManageLayout::printTab( $intMenuFlag, HEAD_MANAGE_INFO      , TITLE_INFO_INDEX      , '/admin/manage/info/index.php' );
                                                    ManageLayout::printTab( $intMenuFlag, HEAD_MANAGE_ALBUM     , TITLE_PHOTO_INDEX     , '/admin/manage/info/photo/index.php' );
                                                    ManageLayout::printTab( $intMenuFlag, HEAD_MANAGE_MAIL      , TITLE_MAIL_INDEX      , '/admin/manage/mail/index.php' );
                                                    ManageLayout::printTab( $intMenuFlag, HEAD_MANAGE_THREAD    , TITLE_THREAD_INDEX    , '/admin/manage/bbs/index.php' );
                                                    ManageLayout::printTab( $intMenuFlag, HEAD_MANAGE_SECTION   , TITLE_SECTION_INDEX   , '/admin/manage/section/index.php' );
                                                    if( 0 == strcmp( "1", $arrayUser["el_status"] ) )
                                                    {
                                                        ManageLayout::printTab( $intMenuFlag, HEAD_MANAGE_EL   , TITLE_EL_INDEX   , '/admin/manage/el/index.php' );
                                                    }
                                                  ?>
                                                </tr>
                                            </table>
                                        </td>
                                    </tr>
                                </table>
                                <?
                            }
                            ?>
                        </td>
                    </tr>
                    <tr><td colspan="10"><hr size="1" /></td></tr>
                    <?
                    }
                    ?>
        <?
    }

    // ***********************************************************************
    // * 関数名     ：タブメニュー表示
    // * 引数       ：アクティブか、メニューID、メニューテキスト、リンクモジュールURL
    // * 返り値     ：タブメニューHTMLタグ（１個分）
    // ***********************************************************************
    function printTab( $intActive, $intShowMenuID, $strMenuText, $strMenuLink )
    {
        // アクティブなタブの場合はリンクを設定せずに色を変える
        if( $intActive == $intShowMenuID )
        {
            ?>
            <td><img border="0" src="<?= PATH_MANAGE_IMAGE ?>tab_main_left.gif" width="5" height="21"></td>
            <td background="<?= PATH_MANAGE_IMAGE ?>tab_main_bg.gif"><font size="2" color="#FFFFFF"><a style="color:#ffffff;" href="<?= $strMenuLink ?>" target="_top"><?= $strMenuText ?></a></font></td>
            <td><img border="0" src="<?= PATH_MANAGE_IMAGE ?>tab_main_right.gif" width="5" height="21"></td>
            <?
        }
        else
        {
            ?>
            <td><img border="0" src="<?= PATH_MANAGE_IMAGE ?>tab_left.gif" width="5" height="21"></td>
            <td background="<?= PATH_MANAGE_IMAGE ?>tab_bg.gif"><font size="2">　<a class="menulink" href="<?= $strMenuLink ?>" target="_top"><?= $strMenuText ?></a> &nbsp</font></td>
            <td><img border="0" src="<?= PATH_MANAGE_IMAGE ?>tab_right.gif" width="5" height="21"></td>
            <?php
        }
    }

    // ***********************************************************************
    // * 関数名     ：フッタ表示
    // * 機能概要   ：フッタ部のHTMLを表示する。
    // * 引数       ：なし
    // * 返り値     ：ヘッダHTMLタグ
    // ***********************************************************************
    function PrintFooter()
    {
        ?>
                </table>
            </body>
        </html>
        <?
    }

    // ***********************************************************************
    // * 関数名     ：左部サブメニュー表示
    // * 引数       ：親メニューID、アクティブメニュー番号、
    // * 返り値     ：タブメニューHTMLタグ
    // ***********************************************************************
    function printSubMenu( $intParentMenu, $intActiveSideMenu, $strParentMenuName )
    {
        // メニュー一覧を配列で取得する
        $arraySideMenu = ManageLayout::GetSideMenu( $intParentMenu, $intActiveSideMenu );

        ?>
        <td valign="top" width="183">
            <!-- 左メニュー開始 -->
            <table class="T100Orange" cellSpacing="1" cellPadding="3">
                <tr bgcolor="#B92932">
                    <td width="100%" id='menutitle' colspan="2">
                        <font color="#FFFFFF" size="2">&nbsp;<?= $strParentMenuName ?>&nbsp;</font>
                    </td>
                </tr>
            </table>
            <div id="menutd" width="183" bgcolor="#ececec">
                <table border="0" cellpadding="0" cellspacing="0" bgcolor="#ececec" width="100%" height="100%">
                    <tr>
                        <td valign="top" align="left" id="leftmenu">
                            <table border="0" cellpadding="0" cellspacing="0" class="caution" width="180" align="center">
                                <?php
                                if( true == is_array( $arraySideMenu ) )
                                {
                                    foreach( $arraySideMenu as $key => $value )
                                    {
                                        //子項目がある場合
                                        if( 0 < count( $value['items'] ) )
                                        {
                                            ?>
                                            <!-- サブメニュー表示 -->
                                            <tr>
                                                <td colspan="2">
                                                    <table id="MENUNAVI<?= $naviIndex ?>" border="0" cellspacing="1" cellpadding="3">
                                                        <?
                                                        // 子項目がある場合は表示
                                                        if( true == is_array( $value['items'] ) )
                                                        {
                                                            $strImage           =   '<img border="0" src="' . PATH_MANAGE_IMAGE . 'icon02.gif" width="12" height="12" />';
                                                            $aryTitleImage[0]   =   '<span style="height:16px; vertical-alagin:middle;%s">%s</span>';

                                                            // 子メニューループ
                                                            for( $intCountSubMenu = 0; $intCountSubMenu < count( $value['items'] ); $intCountSubMenu++ )
                                                            {
                                                                // アクティブなサブメニューの場合
                                                                if( $intCountSubMenu == $intActiveSideMenu )
                                                                {
                                                                    $strBackColor = "background-color:#E9967A;";
                                                                }
                                                                else
                                                                {
                                                                    $strBackColor = "";
                                                                }

                                                                //項目名
                                                                $itemName = $value['items'][$intCountSubMenu]['name'];

                                                                // 子メニューを作成
                                                                $itemName = sprintf( $aryTitleImage[0], $strBackColor, $itemName );
                                                                ?>
                                                                    <tr>
                                                                        <td width="12">&nbsp;</td>
                                                                        <td width="12" valign="top"><?= $strImage ?></td>
                                                                        <td align="left"><font size="2"><a class="menulink" href="/admin/manage/<?= $value['items'][$intCountSubMenu]['link'] ?>"><?= $itemName ?></a></font></td>
                                                                    </tr>
                                                                    <tr><td colspan="3"><hr size="1" /></td></tr>
                                                                <?
                                                            }
                                                        }
                                                        ?>
                                                    </table>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td width="165" colspan="2" height="20">&nbsp;</td>
                                            </tr>
                                            <?
                                        }
                                    }
                                }
                                ?>
                            </table>
                        </td>
                    </tr>
                </table>
            </div>
        </td>
        <?
    }

    // ***********************************************************************
    // * 関数名     ：サイドメニュー取得
    // * 機能概要   ：サイドメニューの配列をパラメータ別に作成して返す
    // * 引数       ：親メニューID、アクティブなサイドメニュー番号
    // * 返り値     ：
    // ***********************************************************************
    function GetSideMenu( $intParentMenu, $intActiveSideMenu )
    {
        $menuItems = array();

        // 親メニュー分岐
        switch( $intParentMenu )
        {
            // イーラーニング
            case HEAD_MANAGE_EL:
                $menuItems[] = array(   'name'  => TITLE_EL_INDEX,
                                        'items' => array(   array(  'name'      => TITLE_EL_QUESTION_INDEX ,
                                                                    'link'      => 'el/question/'           ),
                                                            array(  'name'      => TITLE_EL_CATEGORY_INDEX  ,
                                                                    'link'      => 'el/category/'           ),
                                                            array(  'name'      => TITLE_EL_CSV_INPUT      ,
                                                                    'link'      => 'el/csv_up/csv_input.php')
                                                        )
                                    );
                break;
            default:
                $menuItems = NULL;
                break;
        }

        return $menuItems;
    }

}
