<?php
/* ================================================================================
 * ファイル名   ：SQLAid.php
 * タイトル     ：SQL文作成支援クラス
 * 作成者       ：間宮 直樹
 * 作成日       ：2008/08/05
 * 内容         ：SQL文のWHERE句などを作成する際の文字変換等のクラス
 * 更新履歴*******************************************************************
 * 【変更日】  【変更者】      【変更箇所】        【変更理由と変更内容】
 *  2008/08/05  間宮 直樹       全体                新規作成
 * ================================================================================*/

class SQLAid
{
    // 文字列として扱う場合
    function escapeStr( $value )
    {
        $ret = $value . "";

        if( "" != $ret )
        {
            $ret = str_replace( "&#39;", "'", $ret );
            $ret = str_replace( "'", "''", $ret );
            $ret = str_replace( "&#92;", "\"", $ret);
            $ret = str_replace( "\\", "\\\\", $ret);

            $ret = " '" . $ret . "' ";
        }
        else
        { //空文字なので、Nullを与える
            $ret = " Null ";
        }

        return $ret;
    }

    // 数値として扱う場合
    function escapeNum( $value )
    {
        $ret = $value . "";

        if( "" != $ret )
        {
            //文字列内部にシングルクォートがある時に、置き換えを行う
            if( !is_numeric( $ret ) )
            {
                $ret = "Null";
            }
        $ret = " " . $ret . " ";
        }
        else
        {
            //空文字なので、Nullを与える
            $ret = " Null ";
        }
        return $ret;
    }

    // 前後LIKE検索として扱う場合
    function escapeLike( $value )
    {
        $strConv = SQLAid::escapeOnly( $value );
        return " '%" . $strConv . "%' ESCAPE '\\\\' ";
    }

    // 後方LIKE検索として扱う場合
    function escapeLikeForward( $value )
    {
        $strConv = SQLAid::escapeOnly( $value );
        return " '" . $strConv . "%' ESCAPE '\\\\' ";
    }

    // 前方LIKE検索として扱う場合
    function escapeLikeBackward( $value )
    {
        $strConv = SQLAid::escapeOnly( $value );
        return " '%" . $strConv . "' ESCAPE '\\\\' ";
    }

    // SQLSafeLike SQLSafeLikeForward SQLSafeLikeBackward 専用の内部関数
    function escapeOnly( &$strStringForSQL )
    {
        $strStringForSQL = $strStringForSQL . "";
        //エスケープキャラクタ
        $strConv = str_replace( "\\", "\\\\", $strStringForSQL );
        //文字列内部にシングルクォートがある時に、置き換えを行う
        $strConv = str_replace( "&#39;", "'", $strConv );
        $strConv = str_replace( "'", "''", $strConv );
        $strConv = str_replace( "&#92;", "\"", $strConv);
        //ワイルドカード
        $strConv = str_replace( "%", "\%", $strConv );
        $strConv = str_replace( "_", "\_", $strConv );
        return $strConv;
    }
}
