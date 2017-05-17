<?php
/* ================================================================================
 * ファイル名   ：StdFunc.php
 * タイトル     ：標準的な機能のクラス群
 * 作成者       ：間宮 直樹
 * 作成日       ：2008/10/01
 * 内容         ：その他的な関数
 * 更新履歴*******************************************************************
 * 【変更日】  【変更者】      【変更箇所】        【変更理由と変更内容】
 *  2008/10/01  間宮 直樹       全体                新規作成
 * ================================================================================*/

$strPath = substr( $_SERVER['SCRIPT_FILENAME'], 0, strpos( $_SERVER['SCRIPT_FILENAME'], 'public_html' ) - 1 + strlen( "/" . 'public_html' . "/" ) );
$strPath = $strPath . "admin/";

// includeするライブラリ・コンフィグファイルのパスを定義する
define( 'LIB_DIR'   , $strPath . '_lib/');
define( 'CONFIG_DIR', $strPath . '_config/');

// ライブラリ・コンフィグファイルをinclude
include_once( LIB_DIR    . 'WrapDB.php' );              // ＤＢ用関数群
include_once( LIB_DIR    . 'SQLAid.php' );              // ＳＱＬ条件文関数群
include_once( CONFIG_DIR . 'manage_conf.php' );         // 管理画面基本情報定義ファイル

class StdFunc
{
    //****************************************************************************
    //*タイトル ：ユーザーエージェント判別関数
    //*内容     ：携帯のユーザーエージェントからキャリア番号を返す
    //****************************************************************************
    function GetAgentNumber()
    {
        if( eregi( "DoCoMo/2", $_SERVER["HTTP_USER_AGENT"] ) )
        {
            //ドコモ FOMA (XHTML)
            Return CARRIER_IMODE;
        }
        else if( eregi( "DoCoMo/1", $_SERVER["HTTP_USER_AGENT"] ) )
        {
            //ドコモ MOVA ブラウザフォン
            Return CARRIER_IMODE;
        }
        else if(eregi("J-PHONE", $_SERVER["HTTP_USER_AGENT"] ) )
        {
            //ボーダフォン 1G,2G
            Return CARRIER_VODAFONE;
        }
        else if( eregi("Vodafone|MOT-",$_SERVER["HTTP_USER_AGENT"] ) )
        {
            //ボーダフォン 3G  (XHTML)
            Return CARRIER_VODAFONE;
        }
        else if(isset($_SERVER["HTTP_X_JPHONE_MSNAME"] ) )
        {
            //ボーダフォン 旧1G?
            Return CARRIER_VODAFONE;
        }
        else if( eregi("SoftBank",$_SERVER["HTTP_USER_AGENT"] ) )
        {
            //SoftBank 3G
            Return CARRIER_VODAFONE;
        }
        else if( eregi("KDDI-", $_SERVER["HTTP_USER_AGENT"] ) )
        {
            //WAP2  (XHTML)
            Return CARRIER_EZWEB;
        }
        else if(eregi("UP.Browser", $_SERVER["HTTP_USER_AGENT"] ) )
        {
            //WAP1 (HDML)
            Return CARRIER_EZWEB;
        }
        else
        {
            //PC
            Return CARRIER_PC;
        }
    }

    //****************************************************************************
    //*タイトル ：携帯INPUTタグ用フォーマット定義関数
    //*内容     ：携帯キャリア別にINPUTタグの入力フォーマットを定義する
    //****************************************************************************
    function GetInputTagAttr(   $bytStyle       ,   // 入力文字種別(基準)
                                $bytMaxLength   ,   // 最大長
                                $bytCarrier     ,   // キャリア
                                $intFontSizeFlag = FONT_SIZE_ALL )
    {
        Switch($bytCarrier)
        {
            Case CARRIER_IMODE:
            Case CARRIER_VODAFONE:
                $strStyleWork = "";
                Switch($bytStyle)
                {
                    Case ATTR_HIRAGANA:
                        $strStyleWork = "h";
                        break;
                    Case ATTR_KATAKANA:
                        $strStyleWork = "hk";
                        break;
                    Case ATTR_ALPHABET:
                        $strStyleWork = "en";
                        break;
                    Case ATTR_NUMERIC:
                        $strStyleWork = "n";
                        break;
                    default:
                        $strStyleWork = "";
                        break;
                }

                // 入力文字種初期設定が必要な場合は設定する
                if( 0 < strlen( $strStyleWork ) )
                {
                    $CarrierKeyStyle = $CarrierKeyStyle . " style=\"-wap-input-format:&quot;*&lt;ja:" . $strStyleWork . "&gt;&quot;;" . StdFunc::GetFontSizeOfCarrier( $intFontSizeFlag );
                }
                $strStyleWork = "";

                // style属性を閉じる
                $CarrierKeyStyle = $CarrierKeyStyle . "\"";
                break;

            // au・softbankは、istyle、format、modeのすべてを記述する
            Case CARRIER_EZWEB:
                Switch($bytStyle)
                {
                    Case ATTR_HIRAGANA:
                        $CarrierKeyStyle = $CarrierKeyStyle . " style=\"-wap-input-format:*M;" . StdFunc::GetFontSizeOfCarrier( $intFontSizeFlag ) . "\" ";
                        break;
                    Case ATTR_KATAKANA:
                        $CarrierKeyStyle = $CarrierKeyStyle . " style=\"-wap-input-format:*M;" . StdFunc::GetFontSizeOfCarrier( $intFontSizeFlag ) . "\" ";
                        break;
                    Case ATTR_ALPHABET:
                        $CarrierKeyStyle = $CarrierKeyStyle . " style=\"-wap-input-format:*m;" . StdFunc::GetFontSizeOfCarrier( $intFontSizeFlag ) . "\" ";
                        break;
                    Case ATTR_NUMERIC:
                        $CarrierKeyStyle = $CarrierKeyStyle . " style=\"-wap-input-format:*N;" . StdFunc::GetFontSizeOfCarrier( $intFontSizeFlag ) . "\" ";
                        break;
                    default:
                        $CarrierKeyStyle = "";
                }
                break;
        }
        return $CarrierKeyStyle;
    }

    //****************************************************************************
    //*タイトル ：携帯キャリア別、メール受信設定画面へのリンク表示
    //****************************************************************************
    function MobileMailSetting( $bytCarrier )
    {
        $strMailURL = "";
        Switch($bytCarrier)
        {
            Case CARRIER_IMODE:
                $strMailURL = "docomoメール受信設定は<a href=\"http://docomo.ne.jp/cp/mailsetst.cgi\">コチラ</a>から。";
                break;
            Case CARRIER_EZWEB:
                $strMailURL = "auメール受信設定は<a href=\"http://www.au.kddi.com/cgi-bin/wau/meiwaku.cgi\">コチラ</a>から。";
                break;
            Case CARRIER_VODAFONE:
                $strMailURL = "";
                break;
        }

        return $strMailURL;
    }

    //****************************************************************************
    //* キャリア別、フォントサイズ指定
    //****************************************************************************
    function GetFontSizeOfCarrier( $intFontSizeFlag )
    {
        //キャリア取得
        $bytCarrier       = StdFunc::GetAgentNumber();

        $strFontSize = "";
        switch( $bytCarrier )
        {
            case CARRIER_IMODE:
                if( FONT_SIZE_ALL == $intFontSizeFlag )
                {
                    $strFontSize = "font-size:small;";
                }
                else
                {
                    $strFontSize = "font-size:smaller;";
                }
                break;
            case CARRIER_EZWEB:
                if( FONT_SIZE_ALL == $intFontSizeFlag )
                {
                    $strFontSize = "font-size:10px;";
                }
                else
                {
                    $strFontSize = "font-size:smaller;";
                }
                break;
            case CARRIER_VODAFONE:
                if( FONT_SIZE_ALL == $intFontSizeFlag )
                {
                    $strFontSize = "font-size:small;";
                }
                else
                {
                    $strFontSize = "font-size:smaller;";
                }
                break;
            default:
                if( FONT_SIZE_ALL == $intFontSizeFlag )
                {
                    $strFontSize = "font-size:14px;";
                }
                else
                {
                    $strFontSize = "font-size:12px;";
                }
                break;
        }

        return $strFontSize;
    }

    //****************************************************************************
    //  ページ機能目次を表示する
    //****************************************************************************
    function WriteContents( $lngDBRecordCount   ,   // DB取得最大行数
                            $lngNowPageNo       ,   // 選択されたページ数
                            $lngPageViewEnable  ,   // 表示するページャー数(1 2 3 4 5 6 なら"6")
                            $lngPageSize        ,   // １ページの表示件数
                            $strBaseURL         ,   // リンクのベースURL
                            &$lngStartRow       ,   // 表示するページのDB取得開始行数(Limit用)
                            &$lngEndRow         ,   // 表示するページのDB取得終了行数(Limit用)
                            $strString          )
    {
        $lngLoopContentsWrite   = 0;
        $iPageCount             = 0;
        $strPrintURL            = "";
        $lngLastCount           = 0;
        $lngWorkRecordCount     = 0;
        $lngCurrentRecord       = 0;  // 表示されているページ上、何ページ目を中央にするか？
        $lngBeforeCount         = 0;
        $lngAfterCount          = 0;
        $intErrorNo             = 0;

        // パラメータエラー判定
        if( "" == $lngDBRecordCount )
        {
            $intErrorNo = 1;
        }

        if( "" == $lngNowPageNo )
        {
            $intErrorNo = 2;
        }

        if( "" == $lngPageViewEnable )
        {
            $intErrorNo = 3;
        }

        if( "" == $lngPageSize )
        {
            $intErrorNo = 4;
        }

        if( "" == $strBaseURL )
        {
            $intErrorNo = 5;
        }

        if( 0 == $intErrorNo )
        {
            $lngWorkRecordCount = $lngDBRecordCount;

            if( 0 < StdFunc::InStr( $strBaseURL, "?" ) )
            {
                $strBaseURL = $strBaseURL . "&";
            }
            else
            {
                $strBaseURL = $strBaseURL . "?";
            }

            $intErrorNo = 0;
            //  Longとして桁あふれしないか試算する  ExecuteExpression( $lngNowPageNo, 0, "+", "Long", $intErrorNo );
            settype( $lngNowPageNo, "int" );
            if ( 0 != $intErrorNo )
            {
                //  オーバーフロー対策
                $lngNowPageNo = 0x7fffffff;
            }
            else
            {
                if( $lngNowPageNo < 0 )
                {
                    $lngNowPageNo = 1;
                }
            }

            //  ページ数計算
            $intErrorNo = "";
            //  Longとして桁あふれしないか試算する  ExecuteExpression( $lngPageSize, 0, "+", "Long", $intErrorNo );
            settype( $lngPageSize, "int" );
            if( $lngPageSize )
            {
                // 最大レコード数が、１ページに表示する件数と同じか、小さい場合
                if( $lngWorkRecordCount <= $lngPageSize )
                {
                    // 総ページ数は１ページとなる
                    $iPageCount = 1;
                }
                // 最大レコード数が、１ページに表示する件数より大きい場合（つまり２ページ以上の場合）
                else
                {
                    // 最大レコード数を１ページに表示する件数で割り、ページ数を求める
                    $iPageCount = ( (int)( $lngWorkRecordCount / $lngPageSize ) ); //(int) - 切り捨て配慮
                    if( 0 != ( $lngDBRecordCount % $lngPageSize ) )
                    {
                        // 余りがあれば、最終ページを１ページプラス
                        $iPageCount = $iPageCount + 1;
                    }
                }
            }

            //  表示ページ数から、選択ページの中央を求める
            if( $iPageCount < $lngPageViewEnable )
            {
                //  ページ数が満たない場合、最大値を入れてやる事で、左の・・・表示を抑制する
                $lngBeforeCount  = $iPageCount;
                $lngAfterCount   = $iPageCount;
            }
            else
            {
                $lngBeforeCount  = (int)( $lngPageViewEnable / 2 ); //(int) - 切り捨て配慮
                $lngAfterCount   = (int)( $lngPageViewEnable / 2 + 1 );
            }



            if ( 0 < $lngDBRecordCount )
            {
                $strBaseURL = str_replace( "?&", "?", $strBaseURL );
                if( is_int( $lngNowPageNo ) )
                {
                    if( StdFunc::CLng( $lngNowPageNo ) < 1 )
                    {
                        $lngNowPageNo = 1;
                    }
                    else
                    {
                        if( ( $lngDBRecordCount / $lngPageSize ) < StdFunc::CLng( $lngNowPageNo ) )
                        {
                            $lngNowPageNo = StdFunc::CLng( $lngDBRecordCount / $lngPageSize );
                            if( 0 != ( $lngDBRecordCount % $lngPageSize ) )
                            {
                                $lngNowPageNo = $lngNowPageNo + 1;
                            }
                        }
                    }
                }
                else
                {
                    $lngNowPageNo = 1;
                }

                $lngLastCount = ( $lngNowPageNo * $lngPageSize );
                if( StdFunc::CLng( $lngWorkRecordCount ) < StdFunc::CLng( $lngLastCount ) )
                {
                    $lngLastCount = $lngWorkRecordCount;
                }

                $strPrintURL = ( ( $lngNowPageNo - 1 ) * $lngPageSize ) + 1 . $strString . "〜" . $lngLastCount . $strString . " (全 " . $lngWorkRecordCount . $strString . ")<br />";

                $lngCurrentRecord = 1;
                if( $lngNowPageNo < $lngBeforeCount )
                {
                    $lngCurrentRecord = 1;
                }
                else
                {
                    if( $lngBeforeCount + 1 < $lngNowPageNo )
                    {
                        if( ceil( $lngWorkRecordCount / $lngPageViewEnable ) != $lngPageSize ) // 全ページ表示時の・・・回避
                        {
                            $strPrintURL = $strPrintURL . "・・・";
                            $lngCurrentRecord = $lngNowPageNo - $lngBeforeCount;
                            if( ( $iPageCount - $lngAfterCount ) < $lngNowPageNo )
                            {
                                $lngCurrentRecord = $iPageCount - $lngPageViewEnable + 1;
                            }
                        }
                    }
                }

                if( $lngCurrentRecord <= 0 )
                {
                    $lngCurrentRecord = 1;
                }
                $lngLoopContentsWrite = $lngCurrentRecord;

                //  端数切捨て
                $lngLoopContentsWrite = floor( $lngLoopContentsWrite );

                if( $lngLoopContentsWrite != $lngNowPageNo )
                {
                    $strPrintURL = $strPrintURL . " <font class='FBlack' ><a href = '" . $strBaseURL . "pgno=" . $lngLoopContentsWrite . $strOption . "'>" . $lngLoopContentsWrite . "</a>ﾍﾟｰｼﾞ</font>";
                }
                else
                {
                    $strPrintURL = $strPrintURL . " <font class='FontRed'>" . $lngLoopContentsWrite . "ﾍﾟｰｼﾞ</font> ";
                }

                //  カーソル移動のＵＲＬを生成
                for( $lngLoopContentsWrite = $lngLoopContentsWrite + 1; $lngLoopContentsWrite <= $iPageCount; $lngLoopContentsWrite ++ )
                {
                    if( $lngLoopContentsWrite != $lngNowPageNo )
                    {
                        $strPrintURL = $strPrintURL . " | <font class='FBlack' ><a href = '" . $strBaseURL . "pgno=" . $lngLoopContentsWrite . $strOption . "'>" . $lngLoopContentsWrite . "</a>ﾍﾟｰｼﾞ</font>";
                    }
                    else
                    {
                        $strPrintURL = $strPrintURL . " | <font class='FontRed'>" . $lngLoopContentsWrite . "ﾍﾟｰｼﾞ</font> ";
                    }

                    if( $lngLoopContentsWrite == $lngCurrentRecord + ( $lngPageViewEnable -1 ) )
                    {
                        if( $lngLoopContentsWrite < $iPageCount )
                        {
                            $strPrintURL = $strPrintURL . "・・・";
                            break;
                        }
                    }
                }
            }
            else
            {
                $strPrintURL = "0" . $strString . "〜0" . $strString . " (全 0" . $strString . ")<br/>";
            }

            //$lngStartRow,$lngEndRow レコードカーソルの位置を渡す。
            $lngStartRow = ( $lngNowPageNo - 1 ) * $lngPageSize;
            If ( "" == $lngStartRow )
            {
                $lngStartRow = 0;
            }

            $lngEndRow = $lngNowPageNo * $lngPageSize;
            If ( "" == $lngEndRow )
            {
                $lngEndRow = 0;
            }

            if ( $lngWorkRecordCount < $lngEndRow )
            {
                $lngEndRow = $lngWorkRecordCount;
            }
        }
        return( $strPrintURL );
    }


    function InStr( $strCheck, $strSearch )
    {
        $lngLength   = 0;
        $lngPosition = 0;

        if( "" == $strCheck )
            return $lngLength;


        if( "" == $strSearch )
            return $lngLength;


        if( mb_strlen( $strCheck ) < mb_strlen( $strSearch ) )
            return $lngLength;

        $lngPosition = mb_strpos( $strCheck, $strSearch );
        if( False === $lngPosition )
        {
            $lngPosition = 0;
        }
        else
        {
            $lngPosition ++;
        }
        return $lngPosition;
    }


    function CLng( $lngNumeric )
    {
        $lngWorkValue = $lngNumeric;

        if( Is_Numeric( $lngWorkValue ) )
        {
            //  範囲がByteに収まるか確認する。
            if( !( ( -0x80000000 <= $lngWorkValue ) && ( $lngWorkValue < 0x7fffffff ) ) )
            {
                //  結果が異なるので、既定範囲最大値にする
                $lngWorkValue = 0x7fffffff;
            }
            else
            {
                //  型を変更する。
                settype( $lngWorkValue, "int" );
            }
        }
        else
        {
            //  結果が異なるので、元の値を返す
            $lngWorkValue = $lngNumeric;
        }
        return $lngWorkValue;
    }

    /* *************************************************************************************
    【概要】入力値に対して必須入力チェック、最大長チェック、文字種チェックを実施する。

    【機能】
      ・空文字チェック（結果OK：空文字、結果NG：エラーメッセージ）
      ・レングスチェック（結果OK：空文字、結果NG：エラーメッセージ）
        →固定長の場合は指定された長さ、可変長の場合は指定された長さ以内のチェック。
      ・文字種チェック（結果OK：空文字、結果NG：エラーメッセージ）
        →以下の文字種をチェックする。
          上記を組み合わせ、以下の関数を実装する。
          ?@半角数字チェック（IsNumericExpand）
          ?A半角英字チェック（IsAlphaExpand）
          ?B半角英数字チェック（IsNumAlphaExpand）
          ?C全角チェック（IsZenkakuExpand）
          ?D全角カナチェック（IsZenkakuKanaExpand）
          ?E電話番号チェック（IsTelNoExpand）
          ?Fメールアドレスチェック（IsEMailExpand）
          ?G文字種チェックなし（IsLengthExpand）

    【入力パラメータ(全てキャラクタ)】
      $strInputData        ：チェック対象の値
      hissu_flag        ：必須フラグ。0:任意、1:必須
      min_length        ：最低長の数値。文字数ではなくByte数。
      max_length        ：最大長の数値。文字数ではなくByte数。
      fixed_length_flag ：固定長チェックフラグ(0:範囲、1:固定長)
      error_msg_title   ：エラーメッセージ用タイトル文字列（入力フィールドのタイトル）
      $intCharType      ：チェック文字種
                            1:半角数字
                            2:半角英数字
                            3:全角
                            4:全角カナ
                            5:電話番号
                            6:メールアドレス
                            8:半角英数記号
                            9:半角英字
                            10:日付妥当性チェック（うるう年チェックも含む）
                            それ以外:文字種チェックなし

    【返り値】
      チェックOKの場合、空文字を返す。
      チェックNGの場合、エラーメッセージ文字列を返す。
      エラーメッセージが複数ある場合は\nで区切った文字列を返す。
    *************************************************************************************** */
    function InputCheckExpand( $strInputData, $intHissuFlag, $intMinLength, $intMaxLength, $intFixedLengthFlag, $strErrorMsgTitle, $intCharType, $strErrorMessage )
    {
        //必須入力チェック
        $strResultMsgWork = StdFunc::HissuCheck( $strInputData, $intHissuFlag, $strErrorMsgTitle );
        $strResultMsg = $strResultMsg . $strResultMsgWork;
        $strResultMsgWork = "";

        $intResultLength = true;
        $intResultString = true;
        $intResultStringDatetime = 0;

        //必須入力不要で、入力値lengthがゼロでなければ以下チェック続行する
        if( strlen( $strInputData ) > 0 )
        {
            //レングスチェック
            $intResultLength = StdFunc::LengthCheck( $strInputData, $intMinLength, $intMaxLength, $intFixedLengthFlag );

            // 文字種ごとに分岐してチェックする
            switch( $intCharType )
            {
                case 1:
                    //数字チェック
                    $intResultString = StdFunc::NumericCheck( $strInputData );
                    break;

                case 2:
                    //英数字チェック
                    $intResultString = StdFunc::NumAlphaCheck( $strInputData );
                    break;

                case 3:
                    //全角チェック
                    $intResultString = StdFunc::ZenkakuCheck( $strInputData );
                    break;

                case 4:
                    //全角カナチェック
                    $intResultString = StdFunc::ZenkakuKanaCheck( $strInputData );
                    break;

                case 5:
                    //電話番号チェック
                    $intResultString = StdFunc::TelNoCheck( $strInputData );
                    break;

                case 6:
                    //メールアドレスチェック
                    $intResultString = StdFunc::EMailCheck( $strInputData );
                    break;

                case 8:
                    //英数記号チェック
                    $intResultString = StdFunc::NumAlphaMarkCheck( $strInputData );
                    break;

                case 9:
                    //英字チェック
                    $intResultString = StdFunc::AlphaCheck( $strInputData );
                    break;

                case 10:
                    //日付妥当性チェック
                    $intResultString = StdFunc::DateCheckExpand( $strInputData, $intMaxLength );
                    break;

                case 11:
                    //年月日時分妥当性チェック
                    $intResultStringDatetime = StdFunc::DateTimeCheckExpand( $strInputData, 0 );
                    break;

                case 12:
                    //年月日時分妥当性チェック（過去日OK）
                    $intResultStringDatetime = StdFunc::DateTimeCheckExpand( $strInputData, 1 );
                    break;

                default:
                    //文字種チェックなし
                    break;
            }
        }

        //エラーメッセージ作成
        if( $intResultLength == false || $intResultString == false || $intResultStringDatetime != 0 )
        {
            $strResultMsg = StdFunc::CreateErrorMsg( $strResultMsg, $intCharType, $intMinLength, $intMaxLength, $intFixedLengthFlag, $strErrorMsgTitle, $intResultLength, $intResultString, $intResultStringDatetime );
        }

        // 改行を加える(実際の連結は関数外で実行する)
        if( strlen( $strResultMsg ) > 0 )
        {
            $strCrlf = "";
            if( strlen( $strErrorMessage ) > 0 )
            {
                $strCrlf = "<br />";
            }
            $strResultMsg = $strCrlf . $strResultMsg;
        }

        return $strResultMsg;
    }


    /* ***************************************************************************
    *  function名  : CreateErrorMsg
    *  機能概要    : エラーメッセージ作成
    *  機能詳細    : 文字種、長さ範囲、固定長を判断し、エラーメッセージを作成する。
    *  戻値        : エラーメッセージ文字列。
    *  引数        : ResultMsg         (I) 追加したいエラーメッセージの文字列
    *                $intCharType         (I) 文字種
    *                min_length        (I) チェック最小文字列長
    *                max_length        (I) チェック最大文字列長
    *                fixed_length_flag (I) 固定長チェックフラグ(0:範囲、1:固定長)
    *                error_msg_title   (I) エラーメッセージ用タイトル文字列
    *                result_length     (I) レングスエラー結果
    *                result_string     (I) 文字種エラー結果
    **************************************************************************** */
    function CreateErrorMsg( $strResultMsg, $intCharType, $intMinLength, $intMaxLength, $intFixedLengthFlag, $strErrorMsgTitle, $intResultLength, $intResultString, $intResultStringDatetime )
    {
        $intMaxLengthMsg = $intMaxLength;
        $intMinLengthMsg = $intMinLength;

        // 文字種ごとに分岐してエラーメッセージ作成する
        switch( $intCharType )
        {
            case 1:
                //数字チェック
                $strStringMsg = "は、半角数字";
                break;

            case 2:
                //英数字チェック
                $strStringMsg = "は、半角英数字";
                break;

            case 3:
                //全角チェック
                $strStringMsg = "は、";
                $intMaxLengthMsg = $intMaxLengthMsg / 2;
                $intMinLengthMsg = $intMinLengthMsg / 2;
                break;

            case 4:
                //全角カナチェック
                $strStringMsg = "は、カナ";
                break;

            case 5:
                //電話番号チェック
                $strStringMsg = "は、半角数字";
                break;

            case 6:
                //メールアドレスチェック
                $strStringMsg = "は、";
                $strMailMsg   = "に正しくない文字が入力されています。";
                break;

            case 8:
                //英数記号チェック
                $strStringMsg = "は、半角英数記号";
                break;

            case 9:
                //英字チェック
                $strStringMsg = "は、半角英字";
                break;

            case 10:
                //日付妥当性チェック
                $strStringMsg = "に正しくない日付が入力されています。";
                break;

            case 11:
                //年月日時分妥当性チェック
                if( $intResultStringDatetime == 1 )
                {
                    $strStringMsg = "に存在しない年月日が入力されています。";
                }
                else
                {
                    $strStringMsg = "に過去の年月日および時間は指定できません。";
                }
                break;

            default:
                //文字種チェックなし
                $strStringMsg = "は、";
                break;
        }

        //（最小長が指定されている場合は範囲を示すエラーメッセージ）
        if( $intMinLength > 0 )
        {
            //範囲を示すエラーメッセージ
            $strBackMsg = $intMinLength . "文字以上" . $intMaxLengthMsg . "文字以下で入力してください。";
        }
        //（固定長の場合）
        else if( $intFixedLengthFlag != 0 )
        {
            //固定長を示すエラーメッセージ
            $strBackMsg = $intMaxLengthMsg . "文字で入力してください。";
        }
        else
        {
            //可変長を示すエラーメッセージ
            $strBackMsg = $intMaxLengthMsg . "文字以内で入力してください。";
        }

        //（E-MAILの場合）
        if( $intCharType == 6 )
        {
            //レングスエラーの場合
            if( $intResultLength != true )
            {
                //エラーメッセージ編集
                $strErrorMsg = $strErrorMsgTitle . $strStringMsg . $strBackMsg;
            }

            //文字種エラーの場合
            if( $intResultString != true )
            {
                if( strlen( $strErrorMsg ) > 0 )
                {
                    //既にメッセージがあったら改行して追加する
                    $strErrorMsg = $strErrorMsg . "<br />";
                }

                //エラーメッセージ編集
                $strErrorMsg = $strErrorMsg . $strErrorMsgTitle . $strMailMsg;
            }
        }
        //（日付の場合）
        else if( $intCharType == 10 || $intCharType == 11 )
        {
            $strErrorMsg = $strErrorMsgTitle . $strStringMsg;
        }
        else
        {
            //エラーメッセージ編集
            $strErrorMsg = $strErrorMsgTitle . $strStringMsg . $strBackMsg;
        }

        return $strErrorMsg;
    }

    /* ***************************************************************************
    *  function名  : HissuCheck
    *  機能概要    : 必須入力チェック関数
    *  機能詳細    : 以下のチェックを実施する。
    *                  ?@必須フラグが 1 の場合、必須チェック。lengthがゼロならエラー。
    *  戻値        : チェックOKの場合は空文字。
    *                チェックNGの場合はエラーメッセージ文字列を返す。
    *  引数        : $strInputData        ：char：チェック対象の値
    *                hissu_flag        (I) 入力必須フラグ(0:任意、1:必須)
    *                error_msg_title   (I) エラーメッセージ用タイトル文字列（入力フィールドのタイトル）
    **************************************************************************** */
    function HissuCheck( $strInputData, $intHissuFlag, $strErrorMsgTitle )
    {
        $strResultMsg = "";

        //必須チェックありで、長さゼロ（未入力）の場合はエラー
        if( $intHissuFlag == 1 && strlen( $strInputData ) == 0 )
        {
            $strResultMsg = $strErrorMsgTitle . "は、必ず入力して頂く必要があります。";
        }

        return $strResultMsg;
    }


    /* ***************************************************************************
     *  function名  : LengthCheck
     *  機能概要    : 最大長チェック関数
     *  機能詳細    : 以下のチェックを実施する。
     *                  ?@入力値が最大長を超えた場合はエラー。
     *  戻値        : チェックOKの場合は空文字。
     *                チェックNGの場合はエラーメッセージ文字列を返す。
     *  引数        : $strInputData        ：char：チェック対象の値
     *                min_length        (I) チェック最小文字列長
     *                max_length        (I) チェック最大文字列長
     *                fixed_length_flag (I) 固定長チェックフラグ(0:範囲、1:固定長)
     *                error_msg_title   (I) エラーメッセージ用タイトル文字列（入力フィールドのタイトル）
     *                char_type         (I) 文字種
     **************************************************************************** */
    function LengthCheck( $strInputData, $intMinLength, $intMaxLength, $intFixedLengthFlag )
    {
        $intResultCode = true;
        $intStringLen  = 0;

        //可変長で、最大長を超えている場合はエラー
        if( $intFixedLengthFlag == 0 && strlen( $strInputData ) > $intMaxLength && $intMinLength == 0 )
        {
            $intResultCode = false;
        }

        //最小文字列長がゼロ以上なら範囲チェック
        if( ( $intFixedLengthFlag == 0 && strlen( $strInputData ) > $intMaxLength ) || 
            ( $intMinLength        > 0 && strlen( $strInputData ) < $intMinLength )    )
        {
            $intResultCode = false;
        }

        //固定長で、長さがイコールではない場合はエラー
        if( $intFixedLengthFlag == 1 && strlen( $strInputData ) != $intMaxLength )
        {
            $intResultCode = false;
        }

        return $intResultCode;
    }


    /* ***************************************************************************
    *  function名  : NumericCheck
    *  機能概要    : 数値チェック
    *  機能詳細    : 入力値に対し以下のチェックを実施する。
    *                  ?@数値チェック
    *  戻値        : チェックOKの場合はtrue
    *                チェックNGの場合はfalse
    *  引数    : $strInputData        (I) チェック対象文字列
    **************************************************************************** */
    function NumericCheck( $strInputData )
    {
        $intResultCode = true;

        //正規表現で数字以外の場合エラー
        //"^"は「〜ではない」を表す。"0-9"は範囲を示す。
        if( false == mb_ereg( "^[0-9]+$", $strInputData ) )
        {
            $intResultCode = false;
        }

        return $intResultCode;
    }

    /* ***************************************************************************
     *  function名  : AlphaCheck
     *  機能概要    : アルファベットチェック
     *  機能詳細    : 入力値に対し以下のチェックを実施する。
     *                  ?@アルファベットチェック
     *  戻値        : チェックOKの場合はtrue
     *                チェックNGの場合はfalse
     *  引数    : $strInputData        (I) チェック対象文字列
     **************************************************************************** */
    function AlphaCheck( $strInputData )
    {
        $intResultCode = true;

        //正規表現で数字以外の場合エラー
        //"^"は「〜ではない」を表す。"A-Z"は範囲を示す。
        if( false == mb_ereg( "^[a-zA-Z]+$", $strInputData ) )
        {
            $intResultCode = false;
        }

        return $intResultCode;
    }

    /* ***************************************************************************
    *  function名  : NumAlphaCheck
    *  機能概要    : 英数チェック
    *  機能詳細    : 入力値に対し以下のチェックを実施する。
    *                  ?@英数チェック
    *  戻値        : チェックOKの場合はtrue
    *                チェックNGの場合はfalse
    *  引数    : $strInputData        (I) チェック対象文字列
    **************************************************************************** */
    function NumAlphaCheck( $strInputData )
    {
        $intResultCode = true;

        //正規表現で数字以外の場合エラー
        //"^"は「〜ではない」を表す。"A-Z"は範囲を示す。
        if( false == mb_ereg( "^[a-zA-Z0-9]+$", $strInputData ) )
        {
            $intResultCode = false;
        }

        return $intResultCode;
    }

    /* ***************************************************************************
    *  function名  : NumAlphaMarkCheck
    *  機能概要    : 英数記号チェック
    *  機能詳細    : 入力値に対し以下のチェックを実施する。
    *                  ?@英数記号チェック
    *  戻値        : チェックOKの場合はtrue
    *                チェックNGの場合はfalse
    *  引数    : $strInputData        (I) チェック対象文字列
    **************************************************************************** */
    function NumAlphaMarkCheck( $strInputData )
    {
        $intResultCode = true;

        //正規表現で数字以外の場合エラー("/["〜"]/"の間で表現される値ではない場合、というmatch関数を用いたチェック)
        //"^"は「〜ではない」を表す。"[!-‾]"は範囲を示す。
        if( false == mb_ereg( "^[!-‾]+$", $strInputData ) )
        {
            $intResultCode = false;
        }

        return $intResultCode;
    }

    /* ***************************************************************************
     *  function名  : ZenkakuCheck
     *  機能概要    : 全角チェック
     *  機能詳細    : 入力値に対し以下のチェックを実施する。
     *                  ?@全角チェック
     *  戻値        : チェックOKの場合はtrue
     *                チェックNGの場合はfalse
     *  引数    : $strInputData        (I) チェック対象文字列
     **************************************************************************** */
    function ZenkakuCheck( $strInputData )
    {
        $intResultCode = true;

        //１文字ずつ抜き出して、Unicode変換した後の値をチェックする
        for( $intCount = 0; $intCount < mb_strlen( $strInputData ); $intCount++ )
        {
            $strBuf = "";
            // マルチバイトで１文字取り出す
            $strBuf = mb_substr( $strInputData, $intCount, 1 );

            // 抜き出した文字が全角かどうかをチェックする
            if( !empty( $strBuf ) )
            {
                // 抜き出した文字が２バイトなら全角
                if( strlen($strBuf) > mb_strlen($strBuf) )
                {
                    continue;
                }
                // ２バイトに満たないなら全角ではないのでエラー
                else
                {
                    $intResultCode = false;
                    break;
                }
            }
        }

        return $intResultCode;
    }

    /* ***************************************************************************
    *  function名  : ZenkakuKanaCheck
    *  機能概要    : 全角カナチェック
    *  機能詳細    : 入力値に対し以下のチェックを実施する。
    *                  ?@全角カナチェック
    *  戻値        : チェックOKの場合はtrue
    *                チェックNGの場合はfalse
    *  引数    : $strInputData        (I) チェック対象文字列
    **************************************************************************** */
    function ZenkakuKanaCheck( $strInputData )
    {
        $intResultCode = true;

        if( false == mb_ereg( "^[ァ-ヶ]*$", $strInputData ) )
        {
            $intResultCode = false;
        }

        return $intResultCode;
    }

    /* ***************************************************************************
    *  function名  : TelNoCheck
    *  機能概要    : 電話番号チェック
    *  機能詳細    : 入力値に対し以下のチェックを実施する。
    *                  ?@電話番号チェック（数字、"-"、"("、")"以外はエラー）
    *  戻値        : チェックOKの場合はtrue
    *                チェックNGの場合はfalse
    *  引数    : $strInputData        (I) チェック対象文字列
    **************************************************************************** */
    function TelNoCheck( $strInputData )
    {
        $intResultCode = true;

        //正規表現で電話番号以外の場合エラー
        //"^"は「〜ではない」を表す。
        if( false == mb_ereg( "^[0-9|(|)|-]*$", $strInputData ) )
        {
            $intResultCode = false;
        }

        return $intResultCode;
    }

    /* ***************************************************************************
     *  function名  : EMailCheck
     *  機能概要    : メールアドレスチェック
     *  機能詳細    : 入力値に対し以下のチェックを実施する。
     *                  ?@メールアドレスチェック
     *  戻値        : チェックOKの場合はtrue
     *                チェックNGの場合はfalse
     *  引数    : $strInputData        (I) チェック対象文字列
     **************************************************************************** */
    function EMailCheck( $strInputData )
    {
        $intResultCode = true;

        //正規表現でメールアドレス番号以外の場合エラー
        //"^"は「〜ではない」を表す。
        if( false == mb_ereg( "^[A-Za-z0-9\_\.\-]+\@[A-Za-z0-9\_\.\-]+\.[A-Za-z0-9\_\.\-]*[A-Za-z0-9]*$", $strInputData ) )
        {
            $intResultCode = false;
        }

        return $intResultCode;
    }

    /* ***************************************************************************
    *  function名  : DateCheckExpand
    *  機能概要    : 年月日チェック
    *  機能詳細    : 入力値に対し以下のチェックを実施する。
    *                  ?@数値チェック
    *                  ?Aレングスチェック
    *                  ?B年月日妥当性チェック
    *  戻値        : チェックOKの場合はtrue
    *                チェックNGの場合はfalse
    *  引数    : $strInputData        (I) チェック対象文字列
    *            max_length        (I) 年月日の長さ(YYMMDD形式なら6、YYYYMMDD形式なら8)
    **************************************************************************** */
    function DateCheckExpand( $strInputData, $intMaxLength )
    {
        $intResultCode      = true;
        $intResultCodeNum   = true;
        $intResultCodeLen   = true;
        $intResultCodeYMD   = true;

        //数値チェック
        $intResultCodeNum = StdFunc::NumericCheck( $strInputData );

        //レングスチェック(YYMMDD形式は6桁、YYYYMMDD形式は8桁の固定長チェック)
        $intResultCodeLen = StdFunc::LengthCheck( $strInputData, 0, $intMaxLength, 1 );

        //年月日妥当性チェック(6桁、8桁対応)
        $intResultCodeYMD = StdFunc::DateCheck( $strInputData, $intMaxLength );

        //どこかにエラーがあった場合はfalse
        if( $intResultCodeNum != true || $intResultCodeLen != true || $intResultCodeYMD != true )
        {
            $intResultCode = false;
        }

        return $intResultCode;
    }

    /* ***************************************************************************
    *  function名  : DateTimeCheckExpand
    *  機能概要    : 年月日時分チェック
    *  機能詳細    : 入力値に対し以下のチェックを実施する。
    *                  ?@数値チェック
    *                  ?Aレングスチェック
    *                  ?B年月日時分妥当性チェック（過去はエラー）
    *  戻値        : チェックOKの場合は0
    *                チェックNGの場合は1:存在しない日付、2:過去日
    *  引数        : $strInputData        (I) チェック対象文字列
    **************************************************************************** */
    function DateTimeCheckExpand( $strInputData, $intPastFlag )
    {
        $intResultCode      = 0;
        $intResultCodeYY    = true;
        $intResultCodeMM    = true;
        $intResultCodeDD    = true;
        $intYYYY            = 0;
        $intMM              = 0;
        $intDD              = 0;
        $intHH              = 0;
        $intMI              = 0;
        $intYYYY_Size       = 4;

        $strYYYY = substr( $strInputData, 0, $intYYYY_Size );
        $strMM   = substr( $strInputData, $intYYYY_Size, 2 );
        $strDD   = substr( $strInputData, $intYYYY_Size + 2, 2 );
        $strHH   = substr( $strInputData, $intYYYY_Size + 2 + 2, 2 );
        $strMI   = substr( $strInputData, $intYYYY_Size + 2 + 2 + 2, 2 );

        //年取得
        $intYYYY = intval( $strYYYY );

        //月取得
        $intMM   = intval( $strMM );

        //日取得
        $intDD   = intval( $strDD );

        //時取得
        $intHH   = intval( $strHH );

        //分取得
        $intMI   = intval( $strMI );

        //月チェック
        if( 1 > $intMM || $intMM > 12 )
        {
            $intResultCodeMM = false;
        }

        //３０日までの月の場合
        if( 4 == $intMM || 6 == $intMM || 9 == $intMM || 11 == $intMM )
        {
            //３０日を超えていたらエラー
            if( $intDD > 30 )
            {
                $intResultCodeDD = false;
            }
        }
        //２月の場合
        else if( 2 == $intMM )
        {
            //うるう年チェック
            if( ( 0 == $intYYYY % 4 && 0 < $intYYYY % 100 ) || 0 == $intYYYY % 400 )
            {
                //２９日を超えていたらエラー
                if( $intDD > 29 )
                {
                    $intResultCodeDD = false;
                }
            }
            //うるう年以外
            else
            {
                //２８日を超えていたらエラー
                if( $intDD > 28 )
                {
                    $intResultCodeDD = false;
                }
            }
        }
        //上記以外は３１日
        else
        {
            //３１日を超えていたらエラー
            if( $intDD > 31 )
            {
                $intResultCodeDD = false;
            }
        }

        //エラーチェック
        if( $intResultCodeYY != true || $intResultCodeMM != true || $intResultCodeDD != true )
        {
            $intResultCode = 1;
        }

        // システム日付取得
        $strNowYear  = date( 'Y' );
        $strNowMonth = date( 'n' );
        $strNowDay   = date( 'j' );
        $strNowHH    = date( 'H' );
        $strNowMI    = date( 'i' );

        // 年月日時分の過去チェック
        if( strlen( $strNowMonth ) == 1 )
        {
            $strNowMonth = "0" . $strNowMonth;
        }
        if( strlen( $strNowDay ) == 1 )
        {
            $strNowDay = "0" . $strNowDay;
        }
        if( strlen( $strNowHH ) == 1 )
        {
            $strNowHH = "0" . $strNowHH;
        }
        if( strlen( $strNowMI ) == 1 )
        {
            $strNowMI = "0" . $strNowMI;
        }

        if( strlen( $strMM ) == 1 )
        {
            $strMM = "0" . $strMM;
        }
        if( strlen( $strDD ) == 1 )
        {
            $strDD = "0" . $strDD;
        }
        if( strlen( $strHH ) == 1 )
        {
            $strHH = "0" . $strHH;
        }
        if( strlen( $strMI ) == 1 )
        {
            $strMI = "0" . $strMI;
        }

        // 過去日かどうかをチェックする
        if( 0 == $intPastFlag )
        {
            $strInp_YYYYMMDDHHMM = $strYYYY    . $strMM       . $strDD     . $strHH    . $strMI;
            $strSys_YYYYMMDDHHMM = $strNowYear . $strNowMonth . $strNowDay . $strNowHH . $strNowMI;
//var_dump($strSys_YYYYMMDDHHMM . " > " . $strInp_YYYYMMDDHHMM );
            if( $strSys_YYYYMMDDHHMM > $strInp_YYYYMMDDHHMM )
            {
                $intResultCode = 2;
            }
        }
//var_dump($intResultCode);
        return $intResultCode;
    }

    // fgetcsvの文字化け対応関数
    function fgetcsv_reg(&$handle, $length = null, $d = ',', $e = '"')
    {
        $d = preg_quote($d);
        $e = preg_quote($e);
        $_line = "";
        while ($eof != true)
        {
            $_line .= (empty($length) ? fgets($handle) : fgets($handle, $length));
            $itemcnt = preg_match_all('/'.$e.'/', $_line, $dummy);
            if ($itemcnt % 2 == 0) $eof = true;
        }

        $_csv_line = preg_replace('/(?:\\r\\n|[\\r\\n])?$/', $d, trim($_line));
        $_csv_pattern = '/('.$e.'[^'.$e.']*(?:'.$e.$e.'[^'.$e.']*)*'.$e.'|[^'.$d.']*)'.$d.'/';

        preg_match_all($_csv_pattern, $_csv_line, $_csv_matches);

        $_csv_data = $_csv_matches[1];

        for($_csv_i=0; $_csv_i<count($_csv_data); $_csv_i++)
        {
            $_csv_data[$_csv_i] = preg_replace('/^'.$e.'(.*)'.$e.'$/s','$1',$_csv_data[$_csv_i]);
            $_csv_data[$_csv_i] = str_replace($e.$e, $e, $_csv_data[$_csv_i]);
        }
        return empty($_line) ? false : $_csv_data;
    }

}
