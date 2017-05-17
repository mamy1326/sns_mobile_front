<?php
/* ================================================================================
 * ファイル名   ：ImageFunc.php
 * タイトル     ：画像操作用クラス
 * 作成者       ：間宮 直樹
 * 作成日       ：2008/09/18
 * 内容         ：各種画像の登録・表示を行うクラス
 * 更新履歴*******************************************************************
 * 【変更日】  【変更者】      【変更箇所】        【変更理由と変更内容】
 *  2008/09/18  間宮 直樹       全体                新規作成
 * ================================================================================*/

$strPath = substr( $_SERVER['SCRIPT_FILENAME'], 0, strpos( $_SERVER['SCRIPT_FILENAME'], 'public_html' ) - 1 + strlen( "/" . 'public_html' . "/" ) );
$strPath = $strPath . "admin/";

// includeするライブラリ・コンフィグファイルのパスを定義する
define( 'LIB_DIR'   , $strPath . '_lib/');
define( 'CONFIG_DIR', $strPath . '_config/');

// ライブラリ・コンフィグファイルをinclude
include_once( LIB_DIR    . 'DataConvert.php' );         // データ変換用関数群
include_once( LIB_DIR    . 'WrapDB.php' );              // ＤＢ用関数群
include_once( LIB_DIR    . 'SQLAid.php' );              // ＳＱＬ条件文関数群
include_once( LIB_DIR    . 'AccessLog.php' );           // アクセスログ関数群
include_once( CONFIG_DIR . 'db_conf.php' );             // データベース定義ファイル
include_once( CONFIG_DIR . 'manage_conf.php' );         // 管理画面基本情報定義ファイル

class ImageFunc
{
    // ***********************************************************************
    // * 関数名     ：画像サイズ取得
    // ***********************************************************************
    function GetImageSize(  $strFileName    ,   // ファイル名
                            &$intWidth      ,   // 幅（ピクセル）
                            &$intHeight     ,   // 高さ（ピクセル）
                            $intFlag        )   // 画像種類フラグ(0:フォトアルバム)
    {
        $strPath = substr( $_SERVER['SCRIPT_FILENAME'], 0, strpos( $_SERVER['SCRIPT_FILENAME'], 'public_html' ) - 1 + strlen( "/" . 'public_html' . "/" ) );

        // フラグ判定
        // (フォトアルバムの場合)
        if( 0 ==  $intFlag )
        {
            $strFileFullPath = $strPath . "admin/" . PATH_IMAGE_PHOTO_COPY . $strFileName;
        }

        list( $strWidth, $strHeight, $type, $attr ) = getimagesize( $strFileFullPath );
        $intWidth  = intval( $strWidth );
        $intHeight = intval( $strHeight );
    }

    // ***********************************************************************
    // * 関数名     ：サムネイル画像作成
    // * 返り値     ：画像フルパス
    // ***********************************************************************
    function CreateSumbnail(    $intFlg         ,   // サムネイル種類
                                $strFileName    ,   // テンポラリファイル名(拡張子あり)
                                $intWidth       ,   // 幅（ピクセル）
                                $intHeight      ,   // 高さ（ピクセル）
                                &$strSumbName   ,   // (OUT)サムネイル画像名
                                &$intSumbWidth  ,   // (OUT)サムネイル幅
                                &$intSumbHeight )   // (OUT)サムネイル高さ
    {
        // 最大幅と最大高さを指定
        if( 2 == $intFlg )
        {
            $intMaxWidth  = IMAGE_MOBILE_WIDTH_PX;
            $intMaxHeight = IMAGE_MOBILE_HEIGHT_PX;
        }
        else
        {
            $intMaxWidth  = IMAGE_MOBILE_WIDTH_PX_SS;
            $intMaxHeight = IMAGE_MOBILE_HEIGHT_PX_SS;
        }

        // 画像をメモリにバッファリングできるように、ディレクティブをONにする
        ini_set("output_buffering", "on");

        $intReturnCode = 0;

        // 画像データの縦横ピクセル数を取得
        list( $strWidth, $strHeight, $type, $attr ) = getimagesize( $strFileName );     // オリジナルファイルのサイズを取得
        $intOrgWidth  = intval( $strWidth );
        $intOrgHeight = intval( $strHeight );

        // サイズ調整が不要の場合(最大幅・高さ以下のサイズの画像の場合はそのままのサイズとする)
        if( $intWidth >= $intOrgWidth && $intHeight >= $intOrgHeight )
        {
            $intSumbWidth  = $intOrgWidth;
            $intSumbHeight = $intOrgHeight;
        }
        // 高さのほうが大きいか同じ場合
        elseif( $intOrgWidth <= $intOrgHeight )
        {
            // 割合を計算して幅と高さの値を決める(高さをMAXとして、幅をその割合値で計算する)
            $intSumbWidth  = ( $intOrgWidth / $intOrgHeight ) * $intMaxHeight;
            $intSumbHeight = $intMaxHeight;
        }
        // 幅のほうが大きいか同じ場合
        elseif( $intOrgHeight <= $intOrgWidth )
        {
            // 割合を計算して幅と高さの値を決める(幅をMAXとして、高さをその割合値で計算する)
            $intSumbWidth  = $intMaxWidth;
            $intSumbHeight = ( $intOrgHeight / $intOrgWidth ) * $intMaxWidth;
        }
        else
        {
            $intSumbWidth  = $intOrgWidth;
            $intSumbHeight = $intOrgHeight;
        }
//var_dump($strWidth . ":" . $intOrgWidth . ":" . $intSumbWidth);
        // 指定パス／ファイル名・指定サイズで画像を作成する
        $intReturnCode = ImageFunc::PrintImageStream( $strFileName, $intSumbWidth, $intSumbHeight, 100, $intFlg, $strSumbName );

        return $intReturnCode;
    }

    // ***********************************************************************
    // * 関数名     ：画像パス作成
    // * 機能概要   ：画像のパスを作成する
    // * 引数       ：IN ：画像名
    // *              IN ：種類
    // * 返り値     ：画像フルパス
    // ***********************************************************************
    function GetImagePath( $strImageName, $intImageType, $strPathBase = "../img" )
    {
        // ファイル名指定無し、またはファイルが無い場合
        if( 0 == strlen( $strImageName ) || true != file_exists( $strPathBase . "/pc/hoge/" . $strImageName ) )
        {
            $strImageName = "commingsoon.jpeg";
        }

        // 拡張子
        $strExe = ImageFunc::getFileExtention( $strImageName );

        // 一覧画像
        if( 0 == $intImageType )
        {
            $strExeType = "_ss";
        }
        else
        {
            $strExeType = "_s";
        }

        $strFirePath = $strPathBase . "/pc/hoge/" . substr( $strImageName, 0, strpos( $strImageName, $strExe ) ) . $strExeType . $strExe;
        return $strFirePath;
    }


    // ***********************************************************************
    // * 関数名     ：画像表示
    // * 機能概要   ：画像を指定のサイズで表示する。
    // * 引数       ：IN ：画像フルパス
    // *              IN ：幅
    // *              IN ：高さ
    // *              IN ：クオリティ
    // *              OUT：サムネイル画像名
    // * 返り値     ：画像
    // ***********************************************************************
    function PrintImageStream( $file, $width, $height, $quality = 90, $intFlg, &$strSmall )
    {
        // 拡張子取得
        $exe = ImageFunc::getFileExtention($file);
        if (strcasecmp($exe, ".jpg") == 0 || strcasecmp($exe, ".jpeg") == 0)
        {
            // jpegイメージでメモリに展開
            $imgInp = imagecreatefromjpeg($file);
        }
        else if (strcasecmp($exe, ".gif") == 0)
        {
            // gifイメージでメモリに展開
            $imgInp = imagecreatefromgif($file);
        }
        else if (strcasecmp($exe, ".png") == 0)
        {
            // pngイメージでメモリに展開
            $imgInp = imagecreatefrompng($file);
        }

        if (!$imgInp)
        {
            return false;
        }

        // 展開した画像データの縦横ピクセル数を取得
        $ix = imagesx($imgInp);
        $iy = imagesy($imgInp);

        // モバイルの場合は一度ファイル出力してから表示
        if( 3 == $intFlg || 2 == $intFlg )
        {
            if( 3 == $intFlg )
            {
                $strSmall = "_ss";
            }
            else
            {
                $strSmall = "_s";
            }
            $strOutputFile      = substr( $file, 0, strpos( $file, $exe ) ) . $strSmall . $exe;
        }

        // 展開した画像が指定サイズよりも小さい場合
        if ($ix < $width && $iy < $height)
        {
            // そのまま画像を名前を変えてコピーする
            if( true != copy( $file, $strOutputFile ) )
            {
                $intErrorCode = 9;
                return $intErrorCode;
            }
        }
        else
        {
            // 幅指定なし
            if ($width == NULL || $width == "")
            {
                // 画像そのままのサイズを幅とする
                $width = $ix;
            }

            // 高さ指定なし
            if ($height == NULL || $height == "")
            {
                // 画像そのままのサイズを高さとする
                $height = $iy;
            }

            // *********************************
            // サイズ変更後の画像データを生成
            // *********************************

            // TrueColorイメージの土台を作成
            $imOut = imagecreatetruecolor($width, $height);

            // 再サンプリングを行いイメージの一部をコピー、伸縮する
            // width、heightで指定したTrueColorキャンパス(imOut)に、ix、iyのサイズのイメージ(imgInp)を、座標ゼロ位置に展開する。
            imagecopyresampled( $imOut  ,   // 展開先のメモリ領域（TrueColorで定義済み）
                                $imgInp ,   // 展開元の画像
                                0       ,   // 展開先の座標（X）
                                0       ,   // 展開先の座標（Y）
                                0       ,   // 展開元の座標（X）
                                0       ,   // 展開元の座標（Y）
                                $width  ,   // 展開先の幅
                                $height ,   // 展開先の高さ
                                $ix     ,   // 展開元の幅
                                $iy     );  // 展開元の高さ

            // モバイルの場合は一度ファイル出力してから表示
            if( 3 == $intFlg || 2 == $intFlg )
            {
                if( 3 == $intFlg )
                {
                    $strSmall = "_ss";
                }
                else
                {
                    $strSmall = "_s";
                }
                $strOutputFile = substr( $file, 0, strpos( $file, $exe ) ) . $strSmall . $exe;

                // 画像を指定のクオリティで出力
                if( strcasecmp($exe, ".gif") == 0 )
                {
                    imagegif( $imOut, $strOutputFile );
                }
                else
                {
                    imagejpeg( $imOut, $strOutputFile, $quality );
                }

                imagedestroy($imOut);
            }

        }
        imagedestroy($imgInp);

        return $intErrorCode;
    }


    // ***********************************************************************
    // * 関数名     ：拡張子取得
    // ***********************************************************************
    function getFileExtention($name)
    {
        $ext = explode(".", $name);
        if (count($ext) > 1)
        {
            return ".".strtolower($ext[count($ext)-1]);
        }
        else
        {
            return "";
        }
    }

    // ***********************************************************************
    // * 関数名     ：アップロードファイルエラーチェック＆コピー関数
    // * 機能概要   ：アップロード指定されたファイルがある場合、ファイル形式、
    // *              ファイル存在チェックを実施し、テンポラリ領域へファイルを
    // *              コピーする。
    // * 引数       ：ラベル名、保存する画像名（拡張子なし）、保存したテンポラリファイルパス
    // * 返り値     ：正常終了：空文字、異常終了：エラーメッセージ文字列
    // ***********************************************************************
    function CheckUploadFileAndCopy($strName            ,
                                    $strImageName       ,
                                    $strTempDir         ,
                                    &$strExtension      ,
                                    &$strTmpImagePath   ,
                                    &$intWidth          ,
                                    &$intHeight         ,
                                    $intFlag = 0        ,
                                    $strFileNameMail = "" )
    {
        $intErrorCode = 0;

        // エラーが起きたら即breakして処理を終了できるように、必ず終わるdo-whileを使用する。
        do
        {
            $strErrorMsgFileWork = "";

            // 画像ファイル指定があった場合のみチェックする
            //アップロードファイルが画像ファイル(jpg,gif,png)かをチェック。
            if( 0 < strlen( $_FILES[ $strName ][ 'name' ] ) || 1 == $intFlag )  // メールからフォトアルバムに投稿の場合に画像があるときも処理
            {
                // アップロードの場合
                if( 0 == $intFlag )
                {
                    //アップロードされたテンポラリファイルのパス
                    $strUploadImageFile = $_FILES[ $strName ][ 'tmp_name' ];

                    // 画像存在チェック
                    if( true != file_exists( $strUploadImageFile ) )
                    {
                        $intErrorCode = 1;
                        break;
                    }

                    // ************************************
                    // 画像の種類をチェック
                    // ************************************
                    ImageFunc::ImageUpExtensionCheck(   $_FILES[ $strName ][ 'name' ]   ,   // (IN )PCでのファイル名
                                                        $strUploadImageFile             ,   // (IN )テンポラリファイル名
                                                        $_FILES[ $strName ][ 'type' ]   ,   // (IN )ファイルタイプ
                                                        $strExtension                   ,   // (OUT)拡張子
                                                        $strErrorMsgFileWork            );  // (OUT)エラーメッセージ

                    // エラー処理
                    if( 0 < strlen( $strErrorMsgFileWork ) )
                    {
                        $intErrorCode = 2;
                        break;
                    }
                }
                else
                {
                    $strUploadImageFile = $strFileNameMail;
                }

                //エラー処理(画像の拡張子存在チェック)
                if( 0 == strlen( $strExtension ) )
                {
                    $intErrorCode = 3;
                    break;
                }

                // *******************
                // 画像形式チェック
                // *******************

                // ファイルを文字列に読み込む
                $img = file_get_contents( $strUploadImageFile );

                // 正規表現用のエンコーディングをASCIIに設定する
                mb_regex_encoding('ASCII');

                // 読み込んだ画像データにphp文字列がある場合はエラー
                if( mb_eregi("<\?php", $img) )
                {
                    $intErrorCode = 4;
                    break;
                }

                // 画像サイズ、種類、imgタグ用の文字列を配列で取得する
                $aryImgSize = @getimagesize( $strUploadImageFile );

                if ( !$aryImgSize )
                {
                    $intErrorCode = 5;
                    break;
                }
                // jpeg, gif, png以外はエラー
                else if(    $aryImgSize["mime"] != "image/jpeg" &&
                            $aryImgSize["mime"] != "image/gif"  &&
                            $aryImgSize["mime"] != "image/png"     )
                {
                    $intErrorCode = 6;
                }

                // ***************************************
                // 一時ファイル領域に画像を保存する
                // ***************************************

                // 一時保存画像のパス(/usersite/images/img_temp/review/レビューID.拡張子)
                $strTmpImagePath = $strTempDir . $strImageName . $strExtension;

                // 新しい画像を保存する前に、以前までの一時ファイル削除（全ての画像ファイル形式のテンポラリファイルを削除する）
                ImageFunc::DeleteTempFile(  $strTempDir     ,    // 物理パス（ディレクトリ）
                                            $strImageName    );  // ファイル名（拡張子無し）

                //ファイルのコピー(アップロードファイル→一時保存ファイル)
                if( true != copy( $strUploadImageFile, $strTmpImagePath ) )
                {
                    $intErrorCode = 9;
                    break;
                }

                list( $strWidth, $strHeight, $type, $attr ) = getimagesize( $strTmpImagePath );     // オリジナルファイルのサイズを取得
                $intWidth  = intval( $strWidth );
                $intHeight = intval( $strHeight );
            }
            // テンポラリファイルが無い＝指定が無い場合
            else
            {
                $intErrorCode = 1;
                break;
            }

            break;
        }
        while( false );

        return $intErrorCode;

    }

    // ***********************************************************************
    // * 関数名     ：アップロード画像の画像形式チェック
    // * 機能概要   ：アップロードされてきた画像ファイルの形式をチェックする。
    // * 引数       ：IN ：?@画像名（PCから入力したファイル名）
    // *              IN ：?Aテンポラリファイル名
    // *              IN ：?B画像形式
    // *              OUT：?Cファイルの拡張子
    // *              OUT：エラーがあった際のメッセージ
    // * 返り値     ：引数のOUT
    // ***********************************************************************
    function ImageUpExtensionCheck( $strImgName, $strImgTmp, $strImgType, &$strExtension, &$strErrorMsg )
    {

        if( "" != $strImgName )
        {
            //ファイルを指定している場合
            if( is_uploaded_file( $strImgTmp ) == true )
            {
                if( $strImgType =="image/x-png" )
                {
                    $strExtension = ".png";
                }
                elseif( $strImgType =="image/x-jpg" )
                {
                    $strExtension = ".jpg";
                }
                elseif( $strImgType =="image/x-jpeg" )
                {
                    $strExtension = ".jpeg";
                }
                elseif( $strImgType =="image/jpeg" )
                {
                    $strExtension = ".jpeg";
                }
                elseif( $strImgType =="image/jpg" )
                {
                    $strExtension = ".jpg";
                }
                elseif( $strImgType =="image/pjpeg" )
                {
                    $strExtension = ".jpg";
                }
                elseif( $strImgType =="image/gif" )
                {
                    $strExtension = ".gif";
                }
                else
                {
                    //エラー
                    if( 0 < strlen( $strErrorMsg ) )
                    {
                        $strErrorMsg = $strErrorMsg . "<br />";
                    }
                    $strErrorMsg = $strErrorMsg . "・【画像】拡張子が違います。<br />";
                }
            }
            else
            {
                //指定されたファイルが有効でない場合
                if( 0 < strlen( $strErrorMsg ) )
                {
                    $strErrorMsg = $strErrorMsg . "<br />";
                }
                $strErrorMsg = $strErrorMsg . "・【画像】アップロード可能なファイルではありません。<br />";
            }
        }
        else
        {
            //ファイルを指定していない場合
            if( 0 < strlen( $strErrorMsg ) )
            {
                $strErrorMsg = $strErrorMsg . "<br />";
            }
            $strErrorMsg = $strErrorMsg . "・【画像】ファイルを指定してください。<br />";
        }
    }

    // ***********************************************************************
    // * 関数名     ：テンポラリファイル削除関数
    // * 機能概要   ：指定された物理パスとファイル名(拡張子なし)の画像を、サムネイルも含めて
    // *              以下の拡張子のファイルについてすべて削除する。
    // *              ?@.gif
    // *              ?A.png
    // *              ?B.jpg .jpeg
    // * 引数       ：?@削除対象テンポラリファイルが格納されている物理ディレクトリ名
    // *              ?Aファイル名(拡張子なし)
    // * 返り値     ：なし
    // ***********************************************************************
    function DeleteTempFile(    $strDirFullPath    ,
                                $strFileName        )
    {
        // 拡張子を配列化
        $aryExtention = array(  '.gif'      ,
                                '.png'      ,
                                '.jpg'      ,
                                '.jpeg'     ,
                                '_s.gif'    ,
                                '_s.png'    ,
                                '_s.jpg'    ,
                                '_s.jpeg'    );

        // 拡張子ごとにループ
        foreach ( $aryExtention as $key => $aryExtention )
        {
            $strFileFullPath = $strDirFullPath . $strFileName . $aryExtention;

            // 存在チェック
            if( true == file_exists( $strFileFullPath ) )
            {
                // 削除する
                unlink( $strFileFullPath );
            }
        }
    }

    // ***********************************************************************
    // * 関数名     ：本番ファイルコピー処理
    // * 機能概要   ：テンポラリファイル領域から本番ディレクトリにファイルコピーする。
    // *              コピー後、テンポラリファイルは削除する。
    // * 引数       ：テンポラリファイルパス、本番ファイルパス
    // * 返り値     ：正常終了：0、異常終了：9
    // ***********************************************************************
    function ImageCopyOfTemp(   $strImagePathTemp   ,
                                $strImagePath       )
    {
        $intReturnCode = 0;

        // ファイルがあるときだけ処理
        if( true == file_exists( $strImagePathTemp ) )
        {
            // コピー処理
            if( true != copy( $strImagePathTemp, $strImagePath ) )
            {
                $intReturnCode = 9;
            }

            unlink( $strImagePathTemp );
        }

        return $intReturnCode;
    }
}
