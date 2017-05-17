<?php
/* ================================================================================
 * ファイル名   ：DataConvert.php
 * タイトル     ：データ変換用クラス
 * 作成者       ：間宮 直樹
 * 作成日       ：2008/07/29
 * 内容         ：各種データ変換用
 * 更新履歴*******************************************************************
 * 【変更日】  【変更者】      【変更箇所】        【変更理由と変更内容】
 *  2008/07/29  間宮 直樹       全体                新規作成
 * ================================================================================*/

// includeするライブラリ・コンフィグファイルのパスを定義する
define( 'CONFIG_DIR', '../_config/');

class DataConvert
{
    // ***********************************************************************
    // * 関数名     ：改行コード、タブ、前後の空白を削除する
    // * 引数       ：IN ：?@変換対象文字列
    // * 返り値     ：変換後の文字列
    // ***********************************************************************
    function cWrapTrim( $value )
    {
        if( $value === "" || is_null( $value ) )
        {
            $ret = "";
        }
        else
        {
            $ret = str_replace( "\t", " ", $value );
            $ret = str_replace( array("\r\n","\r","\n"), "", $ret );
            $ret = trim( $ret );
        }
        return $ret;
    }

    // ***********************************************************************
    // * 関数名     ：<br />タグを改行コードに変換する。
    // * 機能概要   ：第一引数の文字列の'<br />'を改行コードに変換する。
    // *              第二引数によって、改行コードを変える。
    // * 引数       ：IN ：?@変換対象文字列
    // *              IN ：?A改行コード種別(0:'\n'、1:'\r\n'、2:'\r')
    // * 返り値     ：変換後の文字列
    // ***********************************************************************
    function BrToReturnCode(    $strBrToReturnCode      ,
                                $intCodelag             )
    {
        $strReturnCode = "";

        $strWork = $strBrToReturnCode;

        // リターンコード種別を判定
        //('\r\n')
        if( 1 == $intCodelag )
        {
            $strReturnCode = "\r\n";
        }
        elseif( 2 == $intCodelag )
        {
            $strReturnCode = "\r";
        }
        else
        {
            $strReturnCode = "\n";
        }

        // 改行コードに置き換える（大文字小文字を判別しない）
        $strWork = str_replace( "<br />", $strReturnCode, $strWork );

        return $strWork;
    }

    // ***********************************************************************
    // * 関数名     ：改行コードを<br />に変換する。
    // * 機能概要   ：第一引数の文字列の改行コードを'<br />'に変換する。
    // * 引数       ：IN ：?@変換対象文字列
    // * 返り値     ：変換後の文字列
    // ***********************************************************************
    function ReturnCodeToBR( $strReturnCodeToBr )
    {
        $strWork = $strReturnCodeToBr;

        // 文字列がある場合のみ変換する
        if( isset( $strWork ) && !( $strWork === "" ) )
        {
            //CR+LFの変換
            $strWork = str_replace( "\r\n", "<br />", $strWork );
            //CRの変換
            $strWork = str_replace( "\r", "<br />", $strWork );
            //LFの変換
            $strWork = str_replace( "\n", "<br />", $strWork );
        }
        else
        {
            $strWork = $strReturnCodeToBr;
        }

        return $strWork;
    }

    // ***********************************************************************
    // * 関数名     ：半角記号を全角に変換
    // ***********************************************************************
    function kigou2zenkaku($string)
    {
        $before = array('!',  '"',  '#',  '\$',  '%',  '&',  "'",  '\(',  '\)',  '=',  '‾',  '\|',  '-',  '\^',  '\\\\',
                        '`',  '\{',  '@',  '\[',  '\+',  '\*',  '}',  ';',  ':',  ']',  '<',  '>',  '\?',  '_',  ',',  '\.',  '/',  '｢',  '｣');
        $after  = array('！', '”', '＃', '＄', '％', '＆', '’', '（', '）', '＝', '〜', '｜', '−', '＾', '￥',
                        '｀', '｛', '＠', '［', '＋', '＊', '｝', '；', '：', '］', '＜', '＞', '？', '＿', '，', '．', '／', '「', '」');

        foreach ($before as $i=>$pattern)
        {
            $replacement  = $after[$i];
            $string = mb_ereg_replace($pattern, $replacement, $string);
        }

        return $string;
    }

    // ***********************************************************************
    // * 関数名     ：タグ用文字を表示文字に変換する
    // * 機能概要   ：
    // * 引数       ：IN ：?@変換対象文字列
    // * 返り値     ：変換後の文字列
    // ***********************************************************************
    function MetatagEncode( $value )
    {
        $code_sj = "utf-8";

        if( "" === $value || is_null($value) )
        {
            return "";
        }
        else
        {
            $ret = htmlspecialchars( $value, ENT_QUOTES );
            $ret = str_replace( "'", "&#39;", $ret );
            $ret = str_replace( "\"", "&#34;", $ret );

            for( $intCount = 0; ; $intCount++ )
            {
                $strStringOne = mb_substr( $ret, $intStringPoint, 1, $code_sj );

                if( 1 > strlen( $strStringOne ) )
                {
                    break;
                }

                // マルチバイト文字ではない場合のみ\マーク変換
                if( 2 > strlen( $strStringOne ) )
                {
                    $strStringOne = str_replace( '\\', '&#92;', $strStringOne );
                }

                $strStringAll = $strStringAll . $strStringOne;
                $intStringPoint++;
            }
            $ret = $strStringAll;

            return $ret;
        }
    }

    // ***********************************************************************
    // * 関数名     ：タグ用文字を表示文字に変換する
    // * 機能概要   ：
    // * 引数       ：IN ：?@変換対象文字列
    // * 返り値     ：変換後の文字列
    // ***********************************************************************
    function MetatagDecode( $value )
    {
        if( "" === $value || is_null($value) )
        {
            return "";
        }
        else
        {
            $ret = html_entity_decode( $value, ENT_QUOTES );
            $ret = str_replace( "&#39;", "'", $ret );
            $ret = str_replace( "&#34;", "\"", $ret );
            $ret = str_replace( '&#92;', '\\', $ret );
            return $ret;
        }
    }

    // ***********************************************************************
    // * 関数名     ：予め変換されたシングル・ダブルクォートを本来の文字に戻す
    // * 引数       ：IN ：?@変換対象文字列
    // * 返り値     ：変換後の文字列
    // ***********************************************************************
    function MetatagDecodeMagicQuoteGpc_ON( $value )
    {
        if( "" === $value || is_null($value) )
        {
            return "";
        }
        else
        {
            $ret = str_replace( "&#39;", "'", $value );
            $ret = str_replace( "&#34;", "\"", $ret );
            return $ret;
        }
    }

    // ***********************************************************************
    // * 関数名     ：全角カタカナをローマ字に変換する
    // * 返り値     ：変換後の文字列
    // ***********************************************************************
    function ZenKataKana2Roma( $string )
    {
        $arrayConvertTable = array(
            "ア"    =>  "a",      "イ"    =>    "i",      "ウ"  =>  "u",      "エ"=>"e",      "オ"=>"o",
            "カ"    =>  "ka",     "キ"    =>    "ki",     "ク"  =>  "ku",     "ケ"=>"ke",     "コ"=>"ko",
            "サ"    =>  "sa",     "シ"    =>    "shi",    "ス"  =>  "su",     "セ"=>"se",     'ソ'=>"so",
            "タ"    =>  "ta",     "チ"    =>    "chi",    "ツ"  =>  "tsu",    "テ"=>"te",     "ト"=>"to",
            "ナ"    =>  "na",     "ニ"    =>    "ni",     "ヌ"  =>  "nu",     "ネ"=>"ne",     "ノ"=>"no",
            "ハ"    =>  "ha",     "ヒ"    =>    "hi",     "フ"  =>  "fu",     "ヘ"=>"he",     "ホ"=>"ho",
            "マ"    =>  "ma",     "ミ"    =>    "mi",     "ム"  =>  "mu",     "メ"=>"me",     "モ"=>"mo",
            "ヤ"    =>  "ya",                             "ユ"  =>  "yu",                    "ヨ"=>"yo",
            "ラ"    =>  "ra",     "リ"    =>    "ri",     "ル"  =>  "ru",     "レ"=>"re",     "ロ"=>"ro",
            "ワ"    =>  "wa",                             "ヲ"  =>  "wo",                     "ン"=>"n",
            "ガ"    =>  "ga",     "ギ"    =>    "gi",     "グ"  =>  "gu",     "ゲ"=>"ge",     "ゴ"=>"go",
            "ザ"    =>  "za",     "ジ"    =>    "ji",     "ズ"  =>  "zu",     "ゼ"=>"ze",     "ゾ"=>"zo",
            "ダ"    =>  "da",     "ヂ"    =>    "di",     "ヅ"  =>  "du",     "デ"=>"de",     "ド"=>"do",
            "バ"    =>  "ba",     "ビ"    =>    "bi",     "ブ"  =>  "bu",     "ベ"=>"be",     "ボ"=>"bo",
            "パ"    =>  "pa",     "ピ"    =>    "pi",     "プ"  =>  "pu",     "ペ"=>"pe",     "ポ"=>"po",
            "キャ"  =>  "kya",    "キュ"  =>    "kyu",    "キョ"=>  "kyo",
            "ギャ"  =>  "gya",    "ギュ"  =>    "gyu",    "ギョ"=>  "gyo",
            "シャ"  =>  "sha",    "シュ"  =>    "shu",    "シェ"=>  "she",  "ショ"=>"sho",
            "ジャ"  =>  "ja",     "ジュ"  =>    "ju",     "ジェ"=>  "je",   "ジョ"=>"jo",
            "チャ"  =>  "cha",    "チュ"  =>    "chu",    "チェ"=>  "che",  "チョ"=>"cho",
            "ニャ"  =>  "nya",    "ニュ"  =>    "nyu",    "ニェ"=>  "nye",  "ニョ"=>"nyo",
            "ヒャ"  =>  "hya",    "ヒュ"  =>    "hyu",    "ヒョ"=>"hyo",
            "ビャ"  =>  "bya",    "ビュ"  =>    "byu",    "ビョ"=>"byo",
            "ピャ"  =>  "pya",    "ピュ"  =>    "pyu",    "ピョ"=>"pyo",
            "ミャ"  =>  "mya",    "ミュ"  =>    "myu",    "ミョ"=>"myo",
            "リャ"  =>  "rya",    "リュ"  =>    "ryu",    "リョ"=>"ryo",    "　" => "."        );

        $roma1 = strtr( $string, $arrayConvertTable );
        $roma1 = preg_replace( "/ッ([A-Z]{1})/", "\\1\\1", $roma1 );

        return $roma1;
    }

/**
     * ファイルポインタから行を取得し、CSVフィールドを処理する
     * @param resource handle
     * @param int length
     * @param string delimiter
     * @param string enclosure
     * @return ファイルの終端に達した場合を含み、エラー時にFALSEを返します。
     */
    function fgetcsv_reg (&$handle, $length = null, $d = ',', $e = '"')
    {
        $d = preg_quote($d);
        $e = preg_quote($e);
        $_line = "";

        while( ( $eof != true ) and ( !feof( $handle ) ) )
        {
            $_line .= (empty($length) ? fgets($handle) : fgets($handle, $length));
            $itemcnt = preg_match_all('/'.$e.'/', $_line, $dummy);
            if ($itemcnt % 2 == 0) $eof = true;
        }
        $_csv_line = preg_replace('/(?:\r\n|[\r\n])?$/', $d, trim($_line));
        $_csv_pattern = '/('.$e.'[^'.$e.']*(?:'.$e.$e.'[^'.$e.']*)*'.$e.'|[^'.$d.']*)'.$d.'/';
        preg_match_all($_csv_pattern, $_csv_line, $_csv_matches);
        $_csv_data = $_csv_matches[1];
        for($_csv_i=0;$_csv_i<count($_csv_data);$_csv_i++){
            $_csv_data[$_csv_i]=preg_replace('/^'.$e.'(.*)'.$e.'$/s','$1',$_csv_data[$_csv_i]);
            $_csv_data[$_csv_i]=str_replace($e.$e, $e, $_csv_data[$_csv_i]);
        }
        return empty($_line) ? false : $_csv_data;
    }
}
