<?php
/* ================================================================================
 * ファイル名   ：UsersiteLayout.php
 * タイトル     ：PCサイト各種レイアウト表示クラス
 * 作成者       ：間宮 直樹
 * 作成日       ：2008/07/16
 * 内容         ：PCサイトのヘッダ、フッタ、各パーツ類の表示処理を実施するクラス。
 * 更新履歴*******************************************************************
 * 【変更日】  【変更者】      【変更箇所】        【変更理由と変更内容】
 *  2008/07/16  間宮 直樹       全体                新規作成
 * ================================================================================*/

$strPath = substr( $_SERVER['SCRIPT_FILENAME'], 0, strpos( $_SERVER['SCRIPT_FILENAME'], 'public_html' ) - 1 + strlen( "/" . 'public_html' . "/" ) );
$strPath = $strPath . "admin/";

// includeするライブラリ・コンフィグファイルのパスを定義する
define( 'LIB_DIR'   , $strPath . '_lib/');
define( 'CONFIG_DIR', $strPath . '_config/');

// ライブラリ・コンフィグファイルをinclude
include_once( LIB_DIR    . 'StdFunc.php' );             // その他関数群
include_once( LIB_DIR    . 'UserDB.php' );              // ユーザ情報取得・更新関数群
include_once( CONFIG_DIR . 'title_conf.php' );          // ページタイトル定義ファイル
include_once( CONFIG_DIR . 'manage_conf.php' );         // 管理画面基本情報定義ファイル


class UsersiteLayout
{
    // ***********************************************************************
    // * 関数名     ：ヘッダ表示
    // * 機能概要   ：ヘッダ部のHTMLを表示する。
    // * 引数       ：IN ：?@タイトル文字列
    // *              IN ：?A個別適用CSS(無い場合はnull)
    // *              IN ：?B個別適用JS(無い場合はnull)
    // * 返り値     ：ヘッダHTMLタグ
    // ***********************************************************************
    function PrintHeader( $strTitle, $strCss = null, $strJs = null, $intPageType = 1 )
    {
        // 個別スタイルシートがある場合は設定
        $strCssTag = "";
        if( true != is_null( $strCss ) && 0 < strlen( $strCss ) )
        {
            $strCssTag = "<link href='" . $strCss . "' rel='stylesheet' type='text/css'>\n";
        }

        // 個別javascriptファイルがある場合は設定
        $strJsTag = "";
        if( true != is_null( $strJs ) && 0 < strlen( $strJs ) )
        {
            $strJsTag = "<script type='text/javascript' src='/tokyo/js/" . $strJs . "'></script>\n";
        }

        // ページ種別がセカンドページの場合
        $strMenuDefault = "";
        if( 0 != $intPageType )
        {
            $strCssTag = $strCssTag .
                         "<link href='/tokyo/css/DropDownMenu.css' rel='stylesheet' type='text/css'>\n";

            // ブラウザ判定
            // (IEの場合)
            if( false != eregi( "MSIE" , $_SERVER['HTTP_USER_AGENT']) )
            {
                $strCssTag = $strCssTag .
                             "<link href='/tokyo/css/DropDownMenu_IE.css' rel='stylesheet' type='text/css'>\n";
            }
            else
            {
                $strCssTag = $strCssTag .
                             "<link href='/tokyo/css/DropDownMenu_FireFox.css' rel='stylesheet' type='text/css'>\n";
            }

            $strJsTag = $strJsTag .
                        "<script type='text/javascript' src='/tokyo/js/DropDownMenu.js'></script>\n".
                        "<script type='text/javascript' src='/tokyo/js/navi.js'></script>\n".
                        "<script type='text/javascript' src='/tokyo/js/menu.js'></script>\n";

            // メニューを初期化するJS
            //$strMenuDefault = "onLoad=\"javascript:hideMenu();\"";
        }

        ?>
        <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
        <html xmlns="http://www.w3.org/1999/xhtml" lang="ja">
            <head>
                <meta http-equiv="Content-Type" content="text/html; charset=utf8">
                <meta name="description" content="<?= SEO_DISCRIPTION ?>">
                <meta name="keywords"    content="<?= SEO_KEYWORDS ?>">
                <meta name="author"      content="<?= SEO_AUTHOR ?>" >
                <script type="text/javascript" src="/tokyo/js/basic.js"></script>
                <?= $strJsTag ?>
                <link rel="shortcut icon" href="/tokyo/ico/favion.ico">
                <link rel="icon"          href="/tokyo/ico/favion.gif" type="image/gif">
                <link href="/tokyo/css/style.css" rel="stylesheet" type="text/css">
                <?= $strCssTag ?>
                <title><?= $strTitle ?></title>
            </head>

            <body <?= $strMenuDefault ?>>
                <div align="center">
                    <div class="pagesize">
        <?
    }


    // ***********************************************************************
    // * 関数名     ：フッタ表示
    // * 機能概要   ：フッタ部のHTMLを表示する。
    // * 引数       ：IN ：?@表示ページタイプ(0:トップページ、1:セカンドページ)
    // *              IN ：?A選択されているメニューID(反転表示制御を実施、0:未選択(初期))
    // * 返り値     ：ヘッダHTMLタグ
    // ***********************************************************************
    function PrintFooter( $intPageType = 1, $intSelectedMenu = 0 )
    {
        // ページタイプ判定
        // （セカンドページの場合）※トップページは何も表示しない
        if( 0 != $intPageType )
        {
            // メニュー表示関数呼び出し
            UsersiteLayout::ShowMenu( $intSelectedMenu );
        }

        ?>
                    </div>
                </div>
            </body>
        </html>
        <?
    }

    // ***********************************************************************
    // * 関数名     ：メニューバー表示
    // * 機能概要   ：メニュー部のHTMLを表示する。
    // * 引数       ：IN ：?@選択されているメニューID(反転表示制御を実施、0:未選択(初期))
    // * 返り値     ：メニューHTMLタグ
    // ***********************************************************************
    function ShowMenu( $intSelectedMenu = 0 )
    {
        // メニュー画像初期設定
        $strMenuImg_Home   = "menu_home.gif";
        $strMenuImg_System = "menu_system.gif";
        $strMenuImg_Prince = "menu_prince.gif";
        $strMenuImg_News   = "menu_news.gif";
        $strMenuImg_Info   = "menu_info.gif";
        $strMenuImg_Link   = "menu_link.gif";
        $strMenuImg_QA     = "menu_qa.gif";
        $strMenuImg_Line   = "menu_line.gif";

        // 画像変更用のタグ
        $strMouse_Home     = " onMouseover=\"document.img_home.src  ='" . DIR_ROOT_IMAGE_PC_MENU . "menu_home_ov.gif'\"   onMouseout=\"document.img_home.src  ='". DIR_ROOT_IMAGE_PC_MENU . "menu_home.gif'\""  ;
        $strMouse_System   = " onMouseover=\"document.img_system.src='" . DIR_ROOT_IMAGE_PC_MENU . "menu_system_ov.gif'\" onMouseout=\"document.img_system.src='". DIR_ROOT_IMAGE_PC_MENU . "menu_system.gif'\"";
        $strMouse_Prince   = " onMouseover=\"document.img_prince.src='" . DIR_ROOT_IMAGE_PC_MENU . "menu_prince_ov.gif'\" onMouseout=\"document.img_prince.src='". DIR_ROOT_IMAGE_PC_MENU . "menu_prince.gif'\"";
        $strMouse_News     = " onMouseover=\"document.img_news.src  ='" . DIR_ROOT_IMAGE_PC_MENU . "menu_news_ov.gif'\"   onMouseout=\"document.img_news.src  ='". DIR_ROOT_IMAGE_PC_MENU . "menu_news.gif'\""  ;
        $strMouse_Info     = " onMouseover=\"document.img_info.src  ='" . DIR_ROOT_IMAGE_PC_MENU . "menu_info_ov.gif'\"   onMouseout=\"document.img_info.src  ='". DIR_ROOT_IMAGE_PC_MENU . "menu_info.gif'\""  ;
        $strMouse_Link     = " onMouseover=\"document.img_link.src  ='" . DIR_ROOT_IMAGE_PC_MENU . "menu_link_ov.gif'\"   onMouseout=\"document.img_link.src  ='". DIR_ROOT_IMAGE_PC_MENU . "menu_link.gif'\""  ;
        $strMouse_QA       = " onMouseover=\"document.img_qa.src    ='" . DIR_ROOT_IMAGE_PC_MENU . "menu_qa_ov.gif'\"     onMouseout=\"document.img_qa.src    ='". DIR_ROOT_IMAGE_PC_MENU . "menu_qa.gif'\""    ;

        // サブメニュー表示位置
        // ブラウザ判定
        // (IEの場合)
        if( false != eregi( "MSIE" , $_SERVER['HTTP_USER_AGENT']) )
        {
            $strSubMenuTop_Home     = "510px";
//            $strSubMenuTop_System   = "483px";
//            $strSubMenuTop_Prince   = "458px";
//            $strSubMenuTop_News     = "510px";
//            $strSubMenuTop_Info     = "458px";
            $strSubMenuTop_System   = "483px";
            $strSubMenuTop_Prince   = "483px";
            $strSubMenuTop_News     = "510px";
            $strSubMenuTop_Info     = "458px";
            $strSubMenuTop_Link     = "510px";
            $strSubMenuTop_QA       = "510px";
        }
        else
        {
            $strSubMenuTop_Home     = "515px";
//            $strSubMenuTop_System   = "495px";
//            $strSubMenuTop_Prince   = "475px";
//            $strSubMenuTop_News     = "515px";
//            $strSubMenuTop_Info     = "475px";
            $strSubMenuTop_System   = "495px";
            $strSubMenuTop_Prince   = "495px";
            $strSubMenuTop_News     = "515px";
            $strSubMenuTop_Info     = "475px";
            $strSubMenuTop_Link     = "515px";
            $strSubMenuTop_QA       = "515px";
        }

        // 選択メニュー判定
        switch( $intSelectedMenu )
        {
            // HOMEの場合
            case 0:
                $strMenuImg_Home   = "menu_home_ov.gif";        // 反転画像
                $strMouse_Home     = "";                        // HOMEなので、画像はマウスが乗っても外れても変わらない
                break;
            // SYSTEMの場合
            case 1:
                $strMenuImg_System = "menu_system_ov.gif";
                $strMouse_System   = "";
                break;
            // PRINCEの場合
            case 2:
                $strMenuImg_Prince = "menu_prince_ov.gif";
                $strMouse_Prince   = "";
                break;
            // NEWSの場合
            case 3:
                $strMenuImg_News   = "menu_news_ov.gif";
                $strMouse_News     = "";
                break;
            // INFOの場合
            case 4:
                $strMenuImg_Info   = "menu_info_ov.gif";
                $strMouse_Info     = "";
                break;
            // LINKの場合
            case 5:
                $strMenuImg_Link   = "menu_link_ov.gif";
                $strMouse_Link     = "";
                break;
            // Q&Aの場合
            case 6:
                $strMenuImg_QA     = "menu_qa_ov.gif";
                $strMouse_QA       = "";
                break;
            default:
                break;
        }

        // LINE画像
        $strLineImgTag = "<li class='menu_border'><img src='" . DIR_ROOT_IMAGE_PC_MENU . "menu_line.gif' width='1' height='12' border='0'></li>\n";

        ?>
            <div align="left">
                <ul id="dd" align="left">
                    <li class="mainmenu" style="width:80px;"><img src="<?= DIR_ROOT_IMAGE_PC_MENU ?>menu.gif" width="59" height="19" border="0"></li>
                    <?= $strLineImgTag ?>
                    <!--ＨＯＭＥ-->
                    <li class="mainmenu"><div class="menu" id="mmenu0" onMouseOver="mopen(0, '<?= $strSubMenuTop_Home ?>');"   onMouseOut="mclosetime();"><img <?=$strMouse_Home?>   src="<?= DIR_ROOT_IMAGE_PC_MENU . $strMenuImg_Home ?>"   name="img_home"   alt="HOMEへ"   width="45" height="12" border="0" ></div>
                        <div class="submenu" id="menu0" onMouseOver="mcancelclosetime()" onMouseOut="mclosetime();">
                            <a id="HOME"            href="<?= DIR_PC_HOME ?>home.php"                  onMouseover="javascript:ChildMenuColorChange( 'HOME',            0, 'CHILD_MENU_SYSTEM', '<?= $strSubMenuTop_Home ?>'  , '<?= $strSubMenuLeft_Home ?>'   );" onMouseout="javascript:ChildMenuColorChange( 'HOME'           , 1, 'CHILD_MENU_SYSTEM' );" class="sub_menu">ＨＯＭＥへ</a>
                        </div>
                    </li>
                    <?= $strLineImgTag ?>
                    <!--SYSTEM-->
                    <li class="mainmenu"><div class="menu" id="mmenu1" onMouseOver="mopen(1, '<?= $strSubMenuTop_System ?>');" onMouseOut="mclosetime();"><img <?=$strMouse_System?> src="<?= DIR_ROOT_IMAGE_PC_MENU . $strMenuImg_System ?>" name="img_system" alt="SYSTEMへ" width="45" height="12" border="0" ></div>
                        <div class="submenu" id="menu1" onMouseOver="mcancelclosetime()" onMouseOut="mclosetime();">
<!--                            <a id="SYSTEM_CHARTER"  href="<?= DIR_PC_HOME ?>system/charter.php"        onMouseover="javascript:ChildMenuColorChange( 'SYSTEM_CHARTER' , 0, 'CHILD_MENU_SYSTEM', '<?= $strSubMenuTop_System ?>', '<?= $strSubMenuLeft_System ?>' );" onMouseout="javascript:ChildMenuColorChange( 'SYSTEM_CHARTER' , 1, 'CHILD_MENU_SYSTEM' );" class="sub_menu">貸切について</a>-->
                            <a id="SYSTEM_RESERVED" href="<?= DIR_PC_HOME ?>system/reserved_input.php" onMouseover="javascript:ChildMenuColorChange( 'SYSTEM_RESERVED', 0, 'CHILD_MENU_SYSTEM', '<?= $strSubMenuTop_System ?>', '<?= $strSubMenuLeft_System ?>' );" onMouseout="javascript:ChildMenuColorChange( 'SYSTEM_RESERVED', 1, 'CHILD_MENU_SYSTEM' );" class="sub_menu">来店予約</a>
                            <a id="SYSTEM"          href="<?= DIR_PC_HOME ?>system/index.php"          onMouseover="javascript:ChildMenuColorChange( 'SYSTEM'         , 0, 'CHILD_MENU_SYSTEM', '<?= $strSubMenuTop_System ?>', '<?= $strSubMenuLeft_System ?>' );" onMouseout="javascript:ChildMenuColorChange( 'SYSTEM'         , 1, 'CHILD_MENU_SYSTEM' );" class="sub_menu">料金システム</a>
                        </div>
                    </li>
                    <?= $strLineImgTag ?>
                    <!--PRINCE-->
                    <li class="mainmenu"><div class="menu" id="mmenu2" onMouseOver="mopen(2, '<?= $strSubMenuTop_Prince ?>');" onMouseOut="mclosetime();"><img <?=$strMouse_Prince?> src="<?= DIR_ROOT_IMAGE_PC_MENU . $strMenuImg_Prince ?>" name="img_prince" alt="PRINCEへ" width="45" height="12" border="0" ></div>
                        <div class="submenu" id="menu2" onMouseOver="mcancelclosetime()" onMouseOut="mclosetime();">
                            <a id="PRINCE_BLOG"  target="_blank" href="http://ameblo.jp/h1-kazuyoshi"    onMouseover="javascript:ChildMenuColorChange( 'PRINCE_BLOG',  0, 'CHILD_MENU_SYSTEM', '<?= $strSubMenuTop_Prince ?>' , '<?= $strSubMenuLeft_Prince ?>' );" onMouseout="javascript:ChildMenuColorChange( 'PRINCE_BLOG' , 1, 'CHILD_MENU_SYSTEM' );" class="sub_menu">日記／ブログ</a>
<!--                            <a id="PRINCE_RANK"  href="<?= DIR_PC_HOME ?>prince/ranking.php" onMouseover="javascript:ChildMenuColorChange( 'PRINCE_RANK',  0, 'CHILD_MENU_SYSTEM', '<?= $strSubMenuTop_Prince ?>' , '<?= $strSubMenuLeft_Prince ?>' );" onMouseout="javascript:ChildMenuColorChange( 'PRINCE_RANK' , 1, 'CHILD_MENU_SYSTEM' );" class="sub_menu">ランキング</a>-->
<!--                            <a id="PRINCE_PHOTO" href="<?= DIR_PC_HOME ?>prince/photo.php"   onMouseover="javascript:ChildMenuColorChange( 'PRINCE_PHOTO', 0, 'CHILD_MENU_SYSTEM', '<?= $strSubMenuTop_Prince ?>' , '<?= $strSubMenuLeft_Prince ?>' );" onMouseout="javascript:ChildMenuColorChange( 'PRINCE_PHOTO', 1, 'CHILD_MENU_SYSTEM' );" class="sub_menu">写真館</a>-->
<!--                            <a id="PRINCE"       href="<?= DIR_PC_HOME ?>prince/index.php"   onMouseover="javascript:ChildMenuColorChange( 'PRINCE',       0, 'CHILD_MENU_SYSTEM', '<?= $strSubMenuTop_Prince ?>' , '<?= $strSubMenuLeft_Prince ?>' );" onMouseout="javascript:ChildMenuColorChange( 'PRINCE',       1, 'CHILD_MENU_SYSTEM' );" class="sub_menu">プリンス</a>-->
                            <a id="PRINCE"       href="#"   onMouseover="javascript:ChildMenuColorChange( 'PRINCE',       0, 'CHILD_MENU_SYSTEM', '<?= $strSubMenuTop_Prince ?>' , '<?= $strSubMenuLeft_Prince ?>' );" onMouseout="javascript:ChildMenuColorChange( 'PRINCE',       1, 'CHILD_MENU_SYSTEM' );" class="sub_menu">プリンス</a>
                        </div>
                    </li>
                    <?= $strLineImgTag ?>
                    <!--Information, What'sNew-->
                    <li class="mainmenu"><div class="menu" id="mmenu3" onMouseOver="mopen(3, '<?= $strSubMenuTop_News ?>'  );" onMouseOut="mclosetime();"><img <?=$strMouse_News?>   src="<?= DIR_ROOT_IMAGE_PC_MENU . $strMenuImg_News ?>"   name="img_news"   alt="NEWSへ"   width="45" height="12" border="0" ></div>
                        <div class="submenu" id="menu3" onMouseOver="mcancelclosetime()" onMouseOut="mclosetime();">
<!--                            <a id="NEWS_MASSMEDIA" href="<?= DIR_PC_HOME ?>news/massmedia.php" onMouseover="javascript:ChildMenuColorChange( 'NEWS_MASSMEDIA',  0, 'CHILD_MENU_SYSTEM', '<?= $strSubMenuTop_News ?>' , '<?= $strSubMenuLeft_News ?>' );" onMouseout="javascript:ChildMenuColorChange( 'NEWS_MASSMEDIA' , 1, 'CHILD_MENU_SYSTEM' );" class="sub_menu">マスメディア各位</a>-->
                            <a id="NEWS"         href="<?= DIR_PC_HOME ?>news/index.php"     onMouseover="javascript:ChildMenuColorChange( 'NEWS',         0, 'CHILD_MENU_SYSTEM', '<?= $strSubMenuTop_News ?>' , '<?= $strSubMenuLeft_News ?>' );"     onMouseout="javascript:ChildMenuColorChange( 'NEWS' ,        1, 'CHILD_MENU_SYSTEM' );" class="sub_menu">更新情報</a>
                        </div>
                    </li>
                    <?= $strLineImgTag ?>
                    <!--aShionへのアクセス-->
                    <li class="mainmenu"><div class="menu" id="mmenu4" onMouseOver="mopen(4, '<?= $strSubMenuTop_Info ?>');"   onMouseOut="mclosetime();"><img <?=$strMouse_Info?>   src="<?= DIR_ROOT_IMAGE_PC_MENU . $strMenuImg_Info ?>"   name="img_info"   alt="INFOへ"   width="45" height="12" border="0" ></div>
                        <div class="submenu" id="menu4" onMouseOver="mcancelclosetime()" onMouseOut="mclosetime();">
                            <a id="INFO_MAP"     href="<?= DIR_PC_HOME ?>info/map.php"     onMouseover="javascript:ChildMenuColorChange( 'INFO_MAP',     0, 'CHILD_MENU_SYSTEM', '<?= $strSubMenuTop_Info ?>' , '<?= $strSubMenuLeft_Info ?>' );" onMouseout="javascript:ChildMenuColorChange( 'INFO_MAP'     , 1, 'CHILD_MENU_SYSTEM' );" class="sub_menu">お店までの地図</a>
                            <a id="INFO_RECRUIT" href="<?= DIR_PC_HOME ?>info/recruit.php" onMouseover="javascript:ChildMenuColorChange( 'INFO_RECRUIT', 0, 'CHILD_MENU_SYSTEM', '<?= $strSubMenuTop_Info ?>' , '<?= $strSubMenuLeft_Info ?>' );" onMouseout="javascript:ChildMenuColorChange( 'INFO_RECRUIT' , 1, 'CHILD_MENU_SYSTEM' );" class="sub_menu">求人募集</a>
                            <a id="INFO_INQUIRY" href="<?= DIR_PC_HOME ?>info/inquiry_input.php" onMouseover="javascript:ChildMenuColorChange( 'INFO_INQUIRY', 0, 'CHILD_MENU_SYSTEM', '<?= $strSubMenuTop_Info ?>' , '<?= $strSubMenuLeft_Info ?>' );" onMouseout="javascript:ChildMenuColorChange( 'INFO_INQUIRY' , 1, 'CHILD_MENU_SYSTEM' );" class="sub_menu">お問い合わせ</a>
                        </div>
                    </li>
                    <?= $strLineImgTag ?>
                    <!--LINK-->
                    <li class="mainmenu"><div class="menu" id="mmenu5" onMouseOver="mopen(5, '<?= $strSubMenuTop_Link ?>');"   onMouseOut="mclosetime();"><img <?=$strMouse_Link?>   src="<?= DIR_ROOT_IMAGE_PC_MENU . $strMenuImg_Link ?>"   name="img_link"   alt="LINKへ"   width="45" height="12" border="0" ></div>
                        <div class="submenu" id="menu5" onMouseOver="mcancelclosetime()" onMouseOut="mclosetime();">
<!--                            <a id="LINK"         href="<?= DIR_PC_HOME ?>link/index.php"   onMouseover="javascript:ChildMenuColorChange( 'LINK',         0, 'CHILD_MENU_SYSTEM', '<?= $strSubMenuTop_Link ?>' , '<?= $strSubMenuLeft_Link ?>' );" onMouseout="javascript:ChildMenuColorChange( 'LINK'         , 1, 'CHILD_MENU_SYSTEM' );" class="sub_menu">リンク</a>-->
                            <a id="LINK"       target="_blank"   href="http://club-shion.net/"   onMouseover="javascript:ChildMenuColorChange( 'LINK',         0, 'CHILD_MENU_SYSTEM', '<?= $strSubMenuTop_Link ?>' , '<?= $strSubMenuLeft_Link ?>' );" onMouseout="javascript:ChildMenuColorChange( 'LINK'         , 1, 'CHILD_MENU_SYSTEM' );" class="sub_menu" style="width:100px;">Prince Club Shion 大阪</a>
                        </div>
                    </li>
                    <?= $strLineImgTag ?>
                    <!--Ｑ＆Ａ-->
                    <li class="mainmenu"><div class="menu" id="mmenu6" onMouseOver="mopen(6, '<?= $strSubMenuTop_QA ?>');"     onMouseOut="mclosetime();"><img <?=$strMouse_QA?>     src="<?= DIR_ROOT_IMAGE_PC_MENU . $strMenuImg_QA ?>"     name="img_qa"     alt="Q&Aへ"   width="45" height="12" border="0" ></div>
                        <div class="submenu" id="menu6" onMouseOver="mcancelclosetime()" onMouseOut="mclosetime();">
                            <a id="QA"           href="<?= DIR_PC_HOME ?>qa/index.php" onMouseover="javascript:ChildMenuColorChange( 'QA', 0, 'CHILD_MENU_SYSTEM', '<?= $strSubMenuTop_QA ?>' , '<?= $strSubMenuLeft_QA ?>' );" onMouseout="javascript:ChildMenuColorChange( 'QA' , 1, 'CHILD_MENU_SYSTEM' );" class="sub_menu">Ｑ＆Ａ</a>
                        </div>
                    </li>
                    <?= $strLineImgTag ?>
                </ul>
                <ul id="dd_mainlogo" align="left">
                    <li class="copyright"><img src="<?= DIR_ROOT_IMAGE_PC ?>copyright.gif" alt="Copyrights (c) 2008 prince club Shion Tokyo All right reaerved."></li>
                    <li class="mainlogo" style="align:center;"><a href="<?= DIR_PC_HOME ?>home.php"><img src="<?= DIR_ROOT_IMAGE_PC ?>shion_logo.gif" width="135" height="61" border="0"></a></li>
                </ul>
            </div>
        <?
    }


    // ***********************************************************************
    // * 関数名     ：メニューバー表示
    // * 機能概要   ：メニュー部のHTMLを表示する。
    // * 引数       ：IN ：?@選択されているメニューID(反転表示制御を実施、0:未選択(初期))
    // * 返り値     ：メニューHTMLタグ
    // ***********************************************************************
    function ShowMenu_2( $intSelectedMenu = 0 )
    {
        // メニュー画像初期設定
        $strMenuImg_Home   = "menu_home.gif";
        $strMenuImg_System = "menu_system.gif";
        $strMenuImg_Prince = "menu_prince.gif";
        $strMenuImg_News   = "menu_news.gif";
        $strMenuImg_Info   = "menu_info.gif";
        $strMenuImg_Link   = "menu_link.gif";
        $strMenuImg_QA     = "menu_qa.gif";
        $strMenuImg_Line   = "menu_line.gif";

        // 画像変更用のタグ
        $strMouse_Home     = " onMouseover=\"document.img_home.src  ='" . DIR_ROOT_IMAGE_PC_MENU . "menu_home_ov.gif'\"   onMouseout=\"document.img_home.src  ='". DIR_ROOT_IMAGE_PC_MENU . "menu_home.gif'\""  ;
        $strMouse_System   = " onMouseover=\"document.img_system.src='" . DIR_ROOT_IMAGE_PC_MENU . "menu_system_ov.gif'\" onMouseout=\"document.img_system.src='". DIR_ROOT_IMAGE_PC_MENU . "menu_system.gif'\"";
        $strMouse_Prince   = " onMouseover=\"document.img_prince.src='" . DIR_ROOT_IMAGE_PC_MENU . "menu_prince_ov.gif'\" onMouseout=\"document.img_prince.src='". DIR_ROOT_IMAGE_PC_MENU . "menu_prince.gif'\"";
        $strMouse_News     = " onMouseover=\"document.img_news.src  ='" . DIR_ROOT_IMAGE_PC_MENU . "menu_news_ov.gif'\"   onMouseout=\"document.img_news.src  ='". DIR_ROOT_IMAGE_PC_MENU . "menu_news.gif'\""  ;
        $strMouse_Info     = " onMouseover=\"document.img_info.src  ='" . DIR_ROOT_IMAGE_PC_MENU . "menu_info_ov.gif'\"   onMouseout=\"document.img_info.src  ='". DIR_ROOT_IMAGE_PC_MENU . "menu_info.gif'\""  ;
        $strMouse_Link     = " onMouseover=\"document.img_link.src  ='" . DIR_ROOT_IMAGE_PC_MENU . "menu_link_ov.gif'\"   onMouseout=\"document.img_link.src  ='". DIR_ROOT_IMAGE_PC_MENU . "menu_link.gif'\""  ;
        $strMouse_QA       = " onMouseover=\"document.img_qa.src    ='" . DIR_ROOT_IMAGE_PC_MENU . "menu_qa_ov.gif'\"     onMouseout=\"document.img_qa.src    ='". DIR_ROOT_IMAGE_PC_MENU . "menu_qa.gif'\""    ;

        // サブメニュー表示位置
        $strSubMenuTop_Home     = "";
        $strSubMenuLeft_Home    = "";
        $strSubMenuTop_System   = "485px";
        $strSubMenuLeft_System  = "335px";
        $strSubMenuTop_Prince   = "462px";
        $strSubMenuLeft_Prince  = "400px";
        $strSubMenuTop_News     = "508px";
        $strSubMenuLeft_News    = "465px";
        $strSubMenuTop_Info     = "462px";
        $strSubMenuLeft_Info    = "530px";
        $strSubMenuTop_Link     = "";
        $strSubMenuLeft_Link    = "";
        $strSubMenuTop_QA       = "";
        $strSubMenuLeft_QA      = "";

        // 選択メニュー判定
        switch( $intSelectedMenu )
        {
            // HOMEの場合
            case 0:
                $strMenuImg_Home   = "menu_home_ov.gif";        // 反転画像
                $strMouse_Home     = "";                        // HOMEなので、画像はマウスが乗っても外れても変わらない
                break;
            // SYSTEMの場合
            case 1:
                $strMenuImg_System = "menu_system_ov.gif";
                $strMouse_System   = "";
                break;
            // PRINCEの場合
            case 2:
                $strMenuImg_Prince = "menu_prince_ov.gif";
                $strMouse_Prince   = "";
                break;
            // NEWSの場合
            case 3:
                $strMenuImg_News   = "menu_news_ov.gif";
                $strMouse_News     = "";
                break;
            // INFOの場合
            case 4:
                $strMenuImg_Info   = "menu_info_ov.gif";
                $strMouse_Info     = "";
                break;
            // LINKの場合
            case 5:
                $strMenuImg_Link   = "menu_link_ov.gif";
                $strMouse_Link     = "";
                break;
            // Q&Aの場合
            case 6:
                $strMenuImg_QA     = "menu_qa_ov.gif";
                $strMouse_QA       = "";
                break;
            default:
                break;
        }

        // LINE画像
        $strLineImgTag = "<td align='center' width='5' ><img src='" . DIR_ROOT_IMAGE_PC_MENU . "menu_line.gif' width='1' height='12' border='0'></td>\n";

        ?>
            <table border="0" cellspacing="0" cellpadding="0" width="700" height="80">
                <tr valign="middle">
                    <td align="left">
                        <table border="0" cellspacing="0" cellpadding="0">
                            <tr>
                                <!--子メニュー部-->
                                <td>
                                  <div id="CHILD_MENU_HOME" style="display:none;">
                                  </div>
                                </td>

                                <!--システムのサブメニュー部-->
                                <td id="CHILD_MENU_SYSTEM" style="display: none; position: absolute; ">
                                    <table border="0" cellpadding="0" cellspacing="0">
                                      <tr height="2">
                                        <td></td>
                                      </tr>
                                      <tr height="1">
                                        <td></td>
                                      </tr>
                                      <tr>
                                        <td id="SYSTEM_CHARTER" onClick="javascript:JumpMenu('<?= DIR_PC_HOME ?>system/charter.php');" onMouseover="javascript:ChildMenuColorChange( 'SYSTEM_CHARTER', 0, 'CHILD_MENU_SYSTEM', '<?= $strSubMenuTop_System ?>', '<?= $strSubMenuLeft_System ?>' );" onMouseout="javascript:ChildMenuColorChange( 'SYSTEM_CHARTER', 1, 'CHILD_MENU_SYSTEM' );" class="child_menu">貸切について</td>
                                      </tr>
                                      <tr height="1">
                                        <td></td>
                                      </tr>
                                      <tr>
                                        <td id="SYSTEM_RESERVED" onClick="javascript:JumpMenu('<?= DIR_PC_HOME ?>system/reserved_input.php');" onMouseover="javascript:ChildMenuColorChange( 'SYSTEM_RESERVED', 0, 'CHILD_MENU_SYSTEM', '<?= $strSubMenuTop_System ?>', '<?= $strSubMenuLeft_System ?>' );" onMouseout="javascript:ChildMenuColorChange( 'SYSTEM_RESERVED', 1, 'CHILD_MENU_SYSTEM' );" class="child_menu">来店予約</td>
                                      </tr>
                                    </table>
                                </td>

                                <td>
                                  <div id="CHILD_MENU_PRINCE" style="display:none;">
                                    <table border="0" cellpadding="0" cellspacing="0">
                                      <tr height="2">
                                        <td></td>
                                      </tr>
                                      <tr height="1">
                                        <td></td>
                                      </tr>
                                      <tr>
                                        <td id="PRINCE_BLOG"  onClick="javascript:JumpMenu('<?= DIR_PC_HOME ?>prince/blog.php');"    onMouseover="javascript:ChildMenuColorChange( 'PRINCE_BLOG',  0, 'CHILD_MENU_PRINCE', '<?= $strSubMenuTop_Prince ?>', '<?= $strSubMenuLeft_Prince ?>' );" onMouseout="javascript:ChildMenuColorChange( 'PRINCE_BLOG', 1, 'CHILD_MENU_PRINCE' );" class="child_menu">日記／ブログ</td>
                                      </tr>
                                      <tr height="1">
                                        <td></td>
                                      </tr>
                                      <tr>
                                        <td id="PRINCE_RANK"  onClick="javascript:JumpMenu('<?= DIR_PC_HOME ?>prince/ranking.php');" onMouseover="javascript:ChildMenuColorChange( 'PRINCE_RANK',  0, 'CHILD_MENU_PRINCE', '<?= $strSubMenuTop_Prince ?>', '<?= $strSubMenuLeft_Prince ?>' );" onMouseout="javascript:ChildMenuColorChange( 'PRINCE_RANK', 1, 'CHILD_MENU_PRINCE' );" class="child_menu">ランキング</td>
                                      </tr>
                                      <tr height="1">
                                        <td></td>
                                      </tr>
                                      <tr>
                                        <td id="PRINCE_PHOTO" onClick="javascript:JumpMenu('<?= DIR_PC_HOME ?>prince/photo.php');"   onMouseover="javascript:ChildMenuColorChange( 'PRINCE_PHOTO', 0, 'CHILD_MENU_PRINCE', '<?= $strSubMenuTop_Prince ?>', '<?= $strSubMenuLeft_Prince ?>' );" onMouseout="javascript:ChildMenuColorChange( 'PRINCE_PHOTO', 1, 'CHILD_MENU_PRINCE' );" class="child_menu">写真館</td>
                                      </tr>
                                    </table>
                                  </div>
                                </td>

                                <td>
                                  <div id="CHILD_MENU_NEWS" style="display:none;">
                                    <table border="0" cellpadding="0" cellspacing="0">
                                      <tr height="2">
                                        <td></td>
                                      </tr>
                                      <tr height="1">
                                        <td></td>
                                      </tr>
                                      <tr>
                                        <td id="NEWS_MASSMEDIA" onClick="javascript:JumpMenu('<?= DIR_PC_HOME ?>news/massmedia.php');" onMouseover="javascript:ChildMenuColorChange( 'NEWS_MASSMEDIA', 0, 'CHILD_MENU_NEWS', '<?= $strSubMenuTop_News ?>', '<?= $strSubMenuLeft_News ?>' );" onMouseout="javascript:ChildMenuColorChange( 'NEWS_MASSMEDIA', 1, 'CHILD_MENU_NEWS' );" class="child_menu">マスメディア各位</td>
                                      </tr>
                                    </table>
                                  </div>
                                </td>

                                <td>
                                  <div id="CHILD_MENU_INFO" style="display:none;">
                                    <table border="0" cellpadding="0" cellspacing="0">
                                      <tr height="2">
                                        <td></td>
                                      </tr>
                                      <tr height="1">
                                        <td></td>
                                      </tr>
                                      <tr>
                                        <td id="INFO_MAP"     onClick="javascript:JumpMenu('<?= DIR_PC_HOME ?>info/map.php');"     onMouseover="javascript:ChildMenuColorChange( 'INFO_MAP',     0, 'CHILD_MENU_INFO', '<?= $strSubMenuTop_Info ?>', '<?= $strSubMenuLeft_Info ?>' );" onMouseout="javascript:ChildMenuColorChange( 'INFO_MAP',     1, 'CHILD_MENU_INFO' );" class="child_menu">お店までの地図</td>
                                      </tr>
                                      <tr height="1">
                                        <td></td>
                                      </tr>
                                      <tr>
                                        <td id="INFO_RECRUIT" onClick="javascript:JumpMenu('<?= DIR_PC_HOME ?>info/recruit.php');" onMouseover="javascript:ChildMenuColorChange( 'INFO_RECRUIT', 0, 'CHILD_MENU_INFO', '<?= $strSubMenuTop_Info ?>', '<?= $strSubMenuLeft_Info ?>' );" onMouseout="javascript:ChildMenuColorChange( 'INFO_RECRUIT', 1, 'CHILD_MENU_INFO' );" class="child_menu">求人募集</td>
                                      </tr>
                                      <tr height="1">
                                        <td></td>
                                      </tr>
                                      <tr>
                                        <td id="INFO_INQUIRY" onClick="javascript:JumpMenu('<?= DIR_PC_HOME ?>info/inquiry.php');" onMouseover="javascript:ChildMenuColorChange( 'INFO_INQUIRY', 0, 'CHILD_MENU_INFO', '<?= $strSubMenuTop_Info ?>', '<?= $strSubMenuLeft_Info ?>' );" onMouseout="javascript:ChildMenuColorChange( 'INFO_INQUIRY', 1, 'CHILD_MENU_INFO' );" class="child_menu">お問い合わせ</td>
                                      </tr>
                                    </table>
                                  </div>
                                </td>

                                <td>
                                  <div id="CHILD_MENU_LINK" style="display:none;">
                                  </div>
                                </td>

                                <td>
                                  <div id="CHILD_MENU_QA" style="display:none;">
                                  </div>
                                </td>
                            </tr>

                            <!--親メニュー部-->
                            <tr valign="middle" align="left">
                                <td width="80" align="center"><img src="<?= DIR_ROOT_IMAGE_PC_MENU ?>menu.gif" width="59" height="19" border="0"></td>
                                <?= $strLineImgTag ?>
                                <td class="menu_image" onMouseover="ChildMenu( 'CHILD_MENU_HOME'   , 0, '<?= $strSubMenuTop_Home ?>'  , '<?= $strSubMenuLeft_Home ?>'   )" onMouseout="ChildMenu( 'CHILD_MENU_HOME' , 1 )">
                                    <a href="<?= DIR_PC_HOME ?>home.php"         <?= $strMouse_Home ?>  ><img src="<?= DIR_ROOT_IMAGE_PC_MENU . $strMenuImg_Home ?>"   name="img_home"   alt="HOMEへ"   width="45" height="12" border="0"></a>
                                </td>
                                <?= $strLineImgTag ?>
                                <td class="menu_image" onMouseover="ChildMenu( 'CHILD_MENU_SYSTEM' , 0, '<?= $strSubMenuTop_System ?>', '<?= $strSubMenuLeft_System ?>' )" onMouseout="ChildMenu( 'CHILD_MENU_SYSTEM' , 1 )">
                                    <a href="<?= DIR_PC_HOME ?>system/index.php" <?= $strMouse_System ?>><img src="<?= DIR_ROOT_IMAGE_PC_MENU . $strMenuImg_System ?>" name="img_system" alt="SYSTEMへ" width="45" height="12" border="0" ></a>
                                </td>
                                <?= $strLineImgTag ?>
                                <td class="menu_image" onMouseover="ChildMenu( 'CHILD_MENU_PRINCE' , 0, '<?= $strSubMenuTop_Prince ?>', '<?= $strSubMenuLeft_Prince ?>' )" onMouseout="ChildMenu( 'CHILD_MENU_PRINCE' , 1 )">
                                    <a href="<?= DIR_PC_HOME ?>prince/index.php" <?= $strMouse_Prince ?>><img src="<?= DIR_ROOT_IMAGE_PC_MENU . $strMenuImg_Prince ?>" name="img_prince" alt="PRINCEへ" width="45" height="12" border="0"><?= UsersiteLayout::ShowMenuChild(2); ?></a>
                                </td>
                                <?= $strLineImgTag ?>
                                <td class="menu_image" onMouseover="ChildMenu( 'CHILD_MENU_NEWS'   , 0, '<?= $strSubMenuTop_News ?>'  , '<?= $strSubMenuLeft_News ?>'   )" onMouseout="ChildMenu( 'CHILD_MENU_NEWS'   , 1 )">
                                    <a href="<?= DIR_PC_HOME ?>news/index.php"   <?= $strMouse_News ?>  ><img src="<?= DIR_ROOT_IMAGE_PC_MENU . $strMenuImg_News ?>"   name="img_news"   alt="NEWSへ"   width="45" height="12" border="0"><?= UsersiteLayout::ShowMenuChild(3); ?></a>
                                </td>
                                <?= $strLineImgTag ?>
                                <td class="menu_image" onMouseover="ChildMenu( 'CHILD_MENU_INFO'   , 0, '<?= $strSubMenuTop_Info ?>'  , '<?= $strSubMenuLeft_Info ?>'   )" onMouseout="ChildMenu( 'CHILD_MENU_INFO'   , 1 )">
                                    <a href="<?= DIR_PC_HOME ?>info/index.php"   <?= $strMouse_Info ?>  ><img src="<?= DIR_ROOT_IMAGE_PC_MENU . $strMenuImg_Info ?>"   name="img_info"   alt="INFOへ"   width="45" height="12" border="0"><?= UsersiteLayout::ShowMenuChild(4); ?></a>
                                </td>
                                <?= $strLineImgTag ?>
                                <td class="menu_image" onMouseover="ChildMenu( 'CHILD_MENU_LINK'   , 0, '<?= $strSubMenuTop_Link ?>'  , '<?= $strSubMenuLeft_Link ?>'   )" onMouseout="ChildMenu( 'CHILD_MENU_LINK'   , 1 )">
                                    <a href="<?= DIR_PC_HOME ?>link/index.php"   <?= $strMouse_Link ?>  ><img src="<?= DIR_ROOT_IMAGE_PC_MENU . $strMenuImg_Link ?>"   name="img_link"   alt="LINKへ" width="45" height="12" border="0"><?= UsersiteLayout::ShowMenuChild(5); ?></a>
                                </td>
                                <?= $strLineImgTag ?>
                                <td class="menu_image" onMouseover="ChildMenu( 'CHILD_MENU_QA'     , 0, '<?= $strSubMenuTop_QA ?>'    , '<?= $strSubMenuLeft_QA ?>'     )" onMouseout="ChildMenu( 'CHILD_MENU_QA'     , 1 )">
                                    <a href="<?= DIR_PC_HOME ?>qa/index.php"     <?= $strMouse_QA ?>    ><img src="<?= DIR_ROOT_IMAGE_PC_MENU . $strMenuImg_QA ?>"     name="img_qa"     alt="Q&Aへ"  width="45" height="12" border="0"><?= UsersiteLayout::ShowMenuChild(6); ?></a>
                                </td>
                                <?= $strLineImgTag ?>
                            </tr>
                        </table>
                    </td>
                    <td align="right" rowspan="2"><a href="<?= DIR_PC_HOME ?>home.php"><img src="<?= DIR_ROOT_IMAGE_PC ?>shion_logo.gif" width="135" height="51" border="0"></a></td>
                </tr>
                <tr valign="middle">
                    <td align="left" colspan="2"><img src="<?= DIR_ROOT_IMAGE_PC ?>copyright.gif" alt="Copyrights (c) 2008 prince club Shion Tokyo All right reaerved."></td>
                </tr>
            </table>
        <?
    }


    // ***********************************************************************
    // * 関数名     ：子メニュー表示
    // * 機能概要   ：子メニュー部のHTMLを表示する。
    // * 引数       ：IN ：?@表示する子メニューの親メニューID
    // * 返り値     ：子メニューHTMLタグ
    // ***********************************************************************
    function ShowMenuChild( $intSelectedMenu = 0 )
    {
        // 選択メニュー判定
        switch( $intSelectedMenu )
        {
            // HOMEの場合
            case 0:
                break;
            // SYSTEMの場合
            case 1:
                $strChildMenu = "\n".
                                "<div id=\"Menu" . $intSelectedMenu . "\" style=\"visibility:hidden\" onMouseout=\"pdMenu('Menu". $intSelectedMenu . "')\">\n" .
                                "               <a href='http://game.gr.jp/' >システムについて</a><br>\n".
                                "               <a href='http://www.openspc2.org/'>来店予約</a><br>\n".
                                "               <a href='http://www.impress.co.jp/'>貸切について</a><br>\n".
                                "</div>\n";
                break;
            // PRINCEの場合
            case 2:
                break;
            // NEWSの場合
            case 3:
                break;
            // INFOの場合
            case 4:
                break;
            // LINKの場合
            case 5:
                break;
            // Q&Aの場合
            case 6:
                break;
            default:
                break;
        }

        return $strChildMenu;
    }

    // ***********************************************************************
    // * 関数名     ：ヘッダ表示(モバイル)
    // * 機能概要   ：ヘッダ部のHTMLを表示する。
    // * 引数       ：IN ：?@タイトル文字列
    // * 返り値     ：ヘッダHTMLタグ
    // ***********************************************************************
    function PrintHeaderMobile( $strBodyDetail = "", $intPageType = 1 )
    {
        // アクセスログを書き込む
//        AccessLog::WriteAccessLog();

        // クラブ情報を取得する
/*        $strCulumnList =    "description_m,".
                            "keywords_m,".
                            "author_m,".
                            "body_setting_m,".
                            "club_name";
        ClubInfoDB::GetClubInfo(    "shion_tokyo"   ,
                                    $strCulumnList  ,
                                    &$arrayResult   );   // SELECT結果
*/
        // BODY定義
        if( 0 == strlen( $strBodyDetail ) )
        {
            $strBodyDetail = $arrayResult["body_setting_m"];
        }

        ?>
        <html>
            <head>
                <meta http-equiv="Content-Type" content="text/html; charset=utf8">
                <meta name="description" content="<?= $arrayResult["description_m"] ?>">
                <meta name="keywords"    content="<?= $arrayResult["keywords_m"] ?>">
                <meta name="author"      content="<?= $arrayResult["author_m"] ?>" >
                <title><?= $arrayResult["club_name"] ?></title>
            </head>

            <body <?= $strBodyDetail ?>>
        <?
    }

    // ***********************************************************************
    // * 関数名     ：ヘッダ表示(モバイル)
    // * 機能概要   ：ヘッダ部のHTMLを表示する。
    // * 引数       ：IN ：?@タイトル文字列
    // * 返り値     ：ヘッダHTMLタグ、ログインユーザ名、基本パラメータ
    // ***********************************************************************
    function PrintHeaderMobileExt( $strBodyDetail = "", $intPageType = 1, $intSiteType = 0 )
    {
        // **************************
        // 管理画面ログイン認証実施
        // **************************

        $intRows = 0;
        $strSessionID = $_GET["s_id"];  // セッションID

        // ログイン画面以外の場合にログイン認証を実施する
        if( 1 == $intPageType )
        {
            // *******************************************************************************************************
            // 自動ログイン設定ありの場合、ログイン処理（セッション新規登録処理）を実施し、セッションIDを取得する
            // *******************************************************************************************************
            // (自動ログインフラグあり)
            if( 0 == strcmp( "1", $_GET[ md5( PARAM_MAIL_DIRECT_LOGIN ) ] ) )
            {
                $strLoginID = $_GET["login_id"];

                // パスワード取得
                $arrayUser = UserDB::GetUserOfLoginID( $strLoginID, $intUserRows );
                if( 0 < $intUserRows )
                {
                    // ログインID＋PW＋ログイン年月日時分秒、の文字列をMD5化し、セッションIDとする
                    $strSessionID = md5( $strLoginID . $arrayUser["password"] . date( 'YnjHis' ) );

                    // セッション情報を更新する
                    $intReturnCode  =   UserDB::UpdateSessionOfLogin(   $strLoginID     ,
                                                                        $strSessionID   );

                    unset( $arrayUser );
                }
            }

            // セッションIDがある場合のみ認証
            if( 0 < strlen( $strSessionID ) )
            {
                // セッションIDをキーにして、ユーザDBを検索
                $arrayUserInfo = UserDB::GetLoginUserOfSession( $strSessionID   ,
                                                                $intRows        );  // (OUT)取得件数（基本的に１件のみ）
            }

            // ログインユーザが取得できない場合はログイン画面へ
            if( 0 == $intRows )
            {
                if( 0 == $intSiteType )
                {
                    header( "Location:/admin/m/index.php" . DOCOMO_GUID );
                }
                else
                {
                    header( "Location:/admin/el/index.php" . DOCOMO_GUID );
                }
            }

            // ログインユーザ名取得して返す
            $arrayLogin =  array(   "user_name"     =>  $arrayUserInfo["user_name"]             ,       // ログインユーザ名
                                    "query_param"   =>  DOCOMO_GUID . "&s_id=" . $strSessionID  ,       // 基本パラメータ設定
                                    "s_id"          =>  $strSessionID                           );
        }

        // アクセスログを書き込む
        AccessLog::WriteAccessLog();

        // BODY定義
        if( 0 == strlen( $strBodyDetail ) )
        {
            $strBodyDetail = $arrayResult["body_setting_m"];
        }

        // キャリア情報取得
        $intCarrior = StdFunc::GetAgentNumber();

        // キャリア判定
        $strNoCache = "";
        // (docomo)
        if( CARRIER_IMODE == $intCarrior )
        {
            header( "Content-Type: application/xhtml+xml; charset=utf8");
            ?>
            <!DOCTYPE html PUBLIC "-//WAPFORUM//DTD XHTML Mobile 1.0//EN" "http://www.wapforum.org/DTD/xhtml-mobile10.dtd">
            <?
        }
        // (au)
        elseif( CARRIER_EZWEB == $intCarrior )
        {
            header( "Content-Type: application/xhtml+xml; charset=utf8");
            header('Expires: Thu, 01 Jan 1970 00:00:00 GMT, -1');
            header('Cache-Control: no-cache, no-store, must-revalidate');
            header('Pragma: no-cache');
            ?>
            <!DOCTYPE html PUBLIC "-//WAPFORUM//DTD XHTML Mobile 1.0//EN" "http://www.wapforum.org/DTD/xhtml-mobile10.dtd">
            <?
            $strNoCache = "<meta http-equiv=\"Cache-Control\" content=\"no-cache\" />";
        }
        // (softbank)
        elseif( CARRIER_VODAFONE == $intCarrior )
        {
            header( "Content-Type: application/xhtml+xml; charset=utf8");
            ?>
            <!DOCTYPE html PUBLIC "-//WAPFORUM//DTD XHTML Mobile 1.0//EN" "http://www.wapforum.org/DTD/xhtml-mobile10.dtd">
            <?
        }
        // (その他)
        else
        {
            ?>
            <!DOCTYPE html PUBLIC "-//WAPFORUM//DTD XHTML Mobile 1.0//EN" "http://www.wapforum.org/DTD/xhtml-mobile10.dtd">
            <?
        }

        // タイトル設定
        if( 0 == $intSiteType )
        {
            $strHeaderTitle = "Mobile Site";
        }
        else
        {
            $strHeaderTitle = "イーラーニング";
        }

        ?>
        <html xmlns="http://www.w3.org/1999/xhtml" lang="ja" xml:lang="ja">
            <head>
                <title><?= $strHeaderTitle ?></title>
            </head>

            <body <?= $strBodyDetail ?> style="background-color:#000000;">
        <?

        return $arrayLogin;
    }


    // ***********************************************************************
    // * 関数名     ：フッタ表示
    // * 機能概要   ：フッタ部のHTMLを表示する。
    // * 引数       ：IN ：?@表示ページタイプ(0:トップページ、1:セカンドページ)
    // *              IN ：?A選択されているメニューID(反転表示制御を実施、0:未選択(初期))
    // * 返り値     ：ヘッダHTMLタグ
    // ***********************************************************************
    function PrintFooterMobileExt( $strSessionID = "", $intSiteType = 0 )
    {
        $strYear = date( "Y" );

        if( 0 == $intSiteType )
        {
            $strLogoutLink = "/admin/m/logout.php";
        }
        else
        {
            $strLogoutLink = "/admin/el/logout.php";
        }

        // セッションIDがあるときだけログアウトリンク表示
        if( 0 < strlen( $strSessionID ) )
        {
        ?>
                <div style="background-color:#000000;<?= StdFunc::GetFontSizeOfCarrier( FONT_SIZE_ALL ) ?>text-align:center;"><span style="color:white;">[<a href="<?= $strLogoutLink . DOCOMO_GUID ?>&s_id=<?= $strSessionID ?>">ログアウト</a>]</span><br /><br /></div>
        <?
        }
        ?>
                <div style="background-color:#000000;<?= StdFunc::GetFontSizeOfCarrier( FONT_SIZE_ALL ) ?>text-align:center;"><span style="color:white;">Copyright (C) <?= $strYear ?><br />hoge inc.<br />All Rights Reserved.</span><br /></div>
            </body>
        </html>
        <?
    }
}
