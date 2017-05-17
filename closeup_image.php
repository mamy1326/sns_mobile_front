<?php
/****************************************************************************
**ファイル名    ：CloseupImage.php
**タイトル      ：拡大画像ページ
**作成日        ：2008/09/19
**内容          ：拡大画像ページ
**
**更新履歴******************************************************************
**変更日        変更者      変更箇所            変更理由と変更内容
****************************************************************************/

$strPath = substr( $_SERVER['SCRIPT_FILENAME'], 0, strpos( $_SERVER['SCRIPT_FILENAME'], 'public_html' ) - 1 + strlen( "/" . 'public_html' . "/" ) );
$strPath = $strPath . "admin/";

// includeするライブラリ・コンフィグファイルのパスを定義する
define( 'LIB_DIR'   , $strPath . '_lib/');
define( 'CONFIG_DIR', $strPath . '_config/');

// ライブラリ・コンフィグファイルをinclude
include_once( LIB_DIR    . 'ImageFunc.php' );           // 画像処理関数群
include_once( CONFIG_DIR . 'title_conf.php' );          // ページタイトル定義ファイル
include_once( CONFIG_DIR . 'manage_conf.php' );         // 管理画面基本情報定義ファイル

$strImageName       = $_GET["name"];    // 画像名

// フラグ判定
// (フォトアルバムの場合)
if( 0 == strcmp( "0", $_GET["flg"] ) )
{
    ImageFunc::GetImageSize(    $strImageName   ,
                                $intWidth       ,
                                $intHeight      ,
                                0               );

    $strImageURL = PATH_IMAGE_PHOTOALBUM . "/" . $strImageName;
}


?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html lang="ja">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=Shift_JIS" />
        <META http-equiv="Content-Style-Type" content="text/css">
        <TITLE>拡大画像</TITLE>
        <script language="JavaScript">
            <!--
                window.moveTo(0,0);
            // -->
        </script>
    </HEAD>

    <BODY BGCOLOR=#fffbef LEFTMARGIN="0" TOPMARGIN="0" MARGINWIDTH="0" MARGINHEIGHT="0">
    <CENTER>
    <TABLE WIDTH=<?= $intWidth ?> BORDER=0 CELLPADDING=1 STYLE="border:1px solid #af9a86;">
        <TR>
            <TD ALIGN=center>
                <TABLE BORDER=0 CELLSPACING=0 CELLPADDING=0>
                    <TR>
                        <TD>&nbsp;</TD>
                    </TR>
                </TABLE>
                <font size="2">幅 <?= $intWidth ?> px × 高さ <?= $intHeight ?> px</font><br>
                <IMG SRC="<?= $strImageURL ?>" WIDTH=<?= $intWidth ?> HEIGHT=<?= $intHeight ?> BORDER=0 STYLE="border:1px solid #af9a86;">

                <TABLE BORDER=0 CELLSPACING=0 CELLPADDING=0>
                    <TR>
                        <TD>&nbsp;</TD>
                    </TR>
                </TABLE>

                <TABLE BORDER=0 CELLSPACING=0 CELLPADDING=0>
                    <TR>
                        <TD><A HREF="javascript:window.close();">閉じる</A></TD>
                    </TR>
                </TABLE>

                <TABLE BORDER=0 CELLSPACING=0 CELLPADDING=0>
                    <TR>
                        <TD>&nbsp;</TD>
                    </TR>
                </TABLE>

            </TD>
        </TR>
    </TABLE>
    </CENTER>

    </BODY>

</HTML>
