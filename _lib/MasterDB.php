<?php
/* ================================================================================
 * ファイル名   ：MasterDB.php
 * タイトル     ：マスタ情報操作用クラス
 * 作成者       ：間宮 直樹
 * 作成日       ：2008/08/16
 * 内容         ：各種マスタ情報の取得・更新処理を実施する。
 * 更新履歴*******************************************************************
 * 【変更日】  【変更者】      【変更箇所】        【変更理由と変更内容】
 *  2008/08/16  間宮 直樹       全体                新規作成
 * ================================================================================*/

// includeするライブラリ・コンフィグファイルのパスを定義する
define( 'LIB_DIR'   , '../_lib/');
define( 'CONFIG_DIR', '../_config/');

// ライブラリ・コンフィグファイルをinclude
include_once( LIB_DIR    . 'DataConvert.php' );         // データ変換用関数群
include_once( LIB_DIR    . 'WrapDB.php' );              // ＤＢ用関数群
include_once( LIB_DIR    . 'SQLAid.php' );              // ＳＱＬ条件文関数群
include_once( LIB_DIR    . 'AccessLog.php' );           // アクセスログ関数群
include_once( CONFIG_DIR . 'db_conf.php' );             // データベース定義ファイル
include_once( CONFIG_DIR . 'manage_conf.php' );         // 管理画面基本情報定義ファイル

class MasterDB
{
    /* ************************************************************
     * 関数名       ：GetStatusName()
     * タイトル     ：予約ステータス名取得
     * ************************************************************/
    function GetStatusName( $strSelectedStatusID    ,   // 選択されたステータスID
                            $intStatusFlag          )   // ステータス種別
    {
        $strStatusName = "";

        // ＤＢ接続
        $db = new WrapDB();
        $db->ConnectDB();

        // SELECT用SQL
        $strSelectSQL = " SELECT                    " .
                        "   status_name             " .
                        " FROM                      " .
                        "   status_master           " .
                        " WHERE                     " .
                        "   del_flag = 0            " .
                        " AND status_id   =         " . SQLAid::escapeNum( $strSelectedStatusID ) .
                        " AND status_flag =         " . SQLAid::escapeNum( $intStatusFlag       ) ;

        // SQL実行
        $objStatus = $db->QueryDB( $strSelectSQL );

        // 取得行数
        $rowStats = $db->GetNumRowsDB( $objStatus );

        // レコードを配列で取得
        $arrayResult = $db->FetchArrayDB( $objStatus );

        $strStatusName = $arrayResult["status_name"];

        unset( $objStatus );

        return $strStatusName;
    }

    /* ************************************************************
     * 関数名       ：GetOptionStatusMaster()
     * タイトル     ：予約ステータスオプションタグ作成
     * ************************************************************/
    function GetOptionStatusMaster( $strSelectedStatusID    ,   // 選択されたステータスID
                                    $intStatusFlag          ,   // ステータス種別
                                    &$strStatusName         )   // 選択されたステータスの名称（返却値）
    {
        $strStatusTag = "";

        // ＤＢ接続
        $db = new WrapDB();
        $db->ConnectDB();

        // SELECT用SQL
        $strSelectSQL = " SELECT                    " .
                        "   status_flag       ,     " .
                        "   status_name             " .
                        " FROM                      " .
                        "   status_master           " .
                        " WHERE                     " .
                        "   del_flag = 0            " .
                        " AND status_id   =         " . SQLAid::escapeNum( $intStatusFlag ) .
                        " ORDER BY                  " .
                        "   status_id               " ;
        // SQL実行
        $objStatus = $db->QueryDB( $strSelectSQL );

        // 取得行数
        $rowStats = $db->GetNumRowsDB( $objStatus );

        // 取得行数が１つでもあった場合はUPDATEする
        if( 0 < $rowStats )
        {
            for( $intCount = 0; $intCount < $rowStats; $intCount++ )
            {
                // レコードを配列で取得
                $arrayResult = $db->FetchArrayDB( $objStatus );

                // 選択されたものならSELECTED
                $strSelected = "";
                if( intval( $arrayResult["status_flag"] ) == intval( $strSelectedStatusID ) )
                {
                    $strSelected   = " selected ";
                    $strStatusName = $arrayResult["status_name"];
                }

                $strStatusTag = $strStatusTag .
                                "<option value='" . $arrayResult["status_flag"] . "'" . $strSelected . ">" . $arrayResult["status_name"] . "</option>¥n";
            }
        }

        unset( $objStatus );

        return $strStatusTag;
    }

    /* ************************************************************
     * 関数名       ：GetTagStatusMaster()
     * タイトル     ：予約ステータス各種タグ作成
     * ************************************************************/
    function GetTagStatusMaster(    $strSelectedStatusID    ,   // 選択されたステータスID
                                    $intStatusFlag          ,   // ステータス種別
                                    &$strStatusName         ,   // 選択されたステータスの名称（返却値）
                                    $intTagType             ,   // タグの種類(0:Option、1:Radio、2:CheckBox)
                                    $strTagName             )   // タグに含むname(Option:未使用、Radio・Check:name属性に使用)
    {
        $strStatusTag = "";

        // ＤＢ接続
        $db = new WrapDB();
        $db->ConnectDB();

        // SELECT用SQL
        $strSelectSQL = " SELECT                    " .
                        "   status_flag       ,     " .
                        "   status_name             " .
                        " FROM                      " .
                        "   status_master           " .
                        " WHERE                     " .
                        "   del_flag = 0            " .
                        " AND status_id   =         " . SQLAid::escapeNum( $intStatusFlag ) .
                        " ORDER BY                  " .
                        "   status_id               " ;

        // SQL実行
        $objStatus = $db->QueryDB( $strSelectSQL );

        // 取得行数
        $rowStats = $db->GetNumRowsDB( $objStatus );

        // 取得行数が１つでもあった場合はUPDATEする
        if( 0 < $rowStats )
        {
            $strTagFormat = "";
            $strSelectOrChecked = "";

            // タグ別処理
            switch( $intTagType )
            {
                case TAG_OPTION:
                    $strTagFormat       = "<option %s value='%s' %s>%s</option>¥n";
                    $strSelectOrChecked = " selected ";
                    $strTagName         = "";
                    break;

                case TAG_RADIO:
                    $strTagFormat       = "<input type='radio' name='%s' VALUE='%s' %s><font size='2' class='FBlack'>%s</font>¥n";
                    $strSelectOrChecked = " checked ";
                    break;

                case TAG_CHECKBOX:
                    $strTagFormat       = "<input type='checkbox' name='%s' value='%s' %s><font size='2' class='FBlack'>%s</font>¥n";
                    $strSelectOrChecked = " checked ";
                    break;
            }

            for( $intCount = 0; $intCount < $rowStats; $intCount++ )
            {
                // レコードを配列で取得
                $arrayResult = $db->FetchArrayDB( $objStatus );

                // 選択されたものならSELECTED
                $strSelected = "";
                if( intval( $arrayResult["status_flag"] ) == intval( $strSelectedStatusID ) )
                {
                    $strSelected   = $strSelectOrChecked;
                    $strStatusName = $arrayResult["status_name"];
                }

                // CheckBoxの場合
                if( TAG_CHECKBOX == $intTagType )
                {
                    $strTagNameBuf = $strTagName . "_" . $intCount;
                }
                else
                {
                    $strTagNameBuf = $strTagName;
                }

                $strStatusTag = $strStatusTag .
                                sprintf( $strTagFormat, $strTagNameBuf, $arrayResult["status_flag"], $strSelected, $arrayResult["status_name"] );
            }
        }

        unset( $objStatus );

        return $strStatusTag;
    }

    /* ************************************************************
     * 関数名       ：MasterCodeConvertOfText()
     * タイトル     ：テキストからマスタコードを返す
     * ************************************************************/
    function MasterCodeConvertOfText(   $intMasterFlag    ,   // マスタフラグ
                                        $strMasterText    )   // マスタテキスト
    {
        // ＤＢ接続
        $db = new WrapDB();
        $db->ConnectDB();

        // 事業部以外はマスタテーブルから
        if( MASTER_STATUS_SECTION != $intMasterFlag )
        {
            $strSelectSQL = " SELECT                            " .
                            "   status_flag     as  master_cd   " .
                            " FROM                              " .
                            "   status_master                   " .
                            " WHERE                             " .
                            "   del_flag = 0                    " .
                            " AND status_id   =                 " . SQLAid::escapeNum( $intMasterFlag ) .
                            " AND (    status_name     = " . SQLAid::escapeStr( $strMasterText ) .
                            "       OR status_name_csv = " . SQLAid::escapeStr( $strMasterText ) .
                            "     ) " ;
        }
        // 事業部は事業部マスタから
        else
        {
            $strSelectSQL = " SELECT                            " .
                            "   section_id      as  master_cd   " .
                            " FROM                              " .
                            "   section_master                  " .
                            " WHERE                             " .
                            "   del_flag = 0                    " .
                            " AND section_name =                " . SQLAid::escapeStr( $strMasterText ) ;
        }
        // SQL実行
        $objStatus = $db->QueryDB( $strSelectSQL );

        // 取得行数
        $rowStats = $db->GetNumRowsDB( $objStatus );

        if( 0 < $rowStats )
        {
            // レコードを配列で取得
            $arrayResult = $db->FetchArrayDB( $objStatus );

            $strMasterCode = $arrayResult["master_cd"];
        }
        else
        {
            $strMasterCode = "0";
        }

        unset( $objStatus );

        return $strMasterCode;
    }
}
