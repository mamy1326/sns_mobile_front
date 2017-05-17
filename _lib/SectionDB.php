<?php
/* ================================================================================
 * ファイル名   ：SectionDB.php
 * タイトル     ：事業部情報取得・更新用クラス
 * 作成者       ：間宮 直樹
 * 作成日       ：2008/12/08
 * 内容         ：事業部情報の取得・更新処理を実施する。
 * 更新履歴*******************************************************************
 * 【変更日】  【変更者】      【変更箇所】        【変更理由と変更内容】
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

class SectionDB
{
    // ***********************************************************************
    // * 関数名     ：事業部情報内容ＤＢ登録処理
    // * 機能概要   ：事業部情報をＤＢに登録する
    // * 返り値     ：送信成功:true、送信失敗:false
    // ***********************************************************************
    function InsertSectionData( $strSectionName     ,   // 事業部名
                                $strNotes           ,   // 説明
                                $strDelFlag         )   // 削除フラグ
    {
        $strErrorMessage = "";

        // ＤＢ接続
        $db = new WrapDB();
        $db->ConnectDB();

        //トランザクション開始
        $db->AutoCommitOffDB();

        // INSERTする
        $strSQL =   " INSERT INTO section_master    " .
                    " (                             " .
                    "   section_name   ," .
                    "   section_note   ," .
                    "   del_flag        " .
                    " )                 " .
                    " VALUES            " .
                    " (                 " .
                    "    " . SQLAid::escapeStr( $strSectionName     ) . ", " .
                    "    " . SQLAid::escapeStr( $strNotes           ) . ", " .
                    "    " . SQLAid::escapeNum( $strDelFlag         ) .
                    ")                   " ;

        // SQL実行
        $objUser = $db->QueryDB( $strSQL );

        // 結果成功したらCOMMIT、失敗ならROLLBACKしてエラーメッセージ設定
        if( DB_OK == $objUser )
        {
            //成功
            $db->TransactDB( db_strDB_COMMIT );
            $db->CloseDB();
        }
        else
        {
            //失敗
            $db->TransactDB( db_strDB_ROLLBACK );
            $db->CloseDB();
            $strErrorMessage = "事業部情報登録エラー";
        }

        return $strErrorMessage;
    }

    // ***********************************************************************
    // * 関数名     ：事業部情報内容ＤＢ更新処理
    // * 機能概要   ：事業部情報内容をＤＢ更新する
    // * 返り値     ：成功:true、失敗:false
    // ***********************************************************************
    function UpdateSectionData( $strSectionID       ,   // 事業部ID
                                $strSectionName     ,   // 事業部名
                                $strNotes           ,   // 説明
                                $strDelFlag         )   // 削除フラグ
    {
        $strErrorMessage = "";

        // ＤＢ接続
        $db = new WrapDB();
        $db->ConnectDB();

        //トランザクション開始
        $db->AutoCommitOffDB();

        // UPDATE文
        $strSQL =   " UPDATE                    " .
                    "   section_master          " .
                    " SET                       " .
                    "   section_name        =   " . SQLAid::escapeStr( $strSectionName          ) . ", " .
                    "   section_note        =   " . SQLAid::escapeStr( $strNotes                ) . ", " .
                    "   del_flag            =   " . SQLAid::escapeNum( intval( $strDelFlag )    ) .
                    " WHERE                     " .
                    "   section_id          =   " . SQLAid::escapeNum( intval( $strSectionID )  ) ;

        // SQL実行
        $objUser = $db->QueryDB( $strSQL );

        // 結果成功したらCOMMIT、失敗ならROLLBACKしてエラーメッセージ設定
        if( DB_OK == $objUser )
        {
            //成功
            $db->TransactDB( db_strDB_COMMIT );
            $db->CloseDB();
        }
        else
        {
            //失敗
            $db->TransactDB( db_strDB_ROLLBACK );
            $db->CloseDB();
            $strErrorMessage = "事業部情報更新エラー";
        }

        return $strErrorMessage;
    }

    // ***********************************************************************
    // * 関数名     ：事業部情報取得処理
    // * 機能概要   ：事業部情報をすべて取得する
    // * 返り値     ：成功:SELECT結果、失敗:null
    // ***********************************************************************
    function GetSectionAll(    &$intRows           )   // 取得できたレコード数
    {
        // ＤＢ接続
        $db = new WrapDB();
        $db->ConnectDB();

        // SELECTする
        $strSQL =   " SELECT            " .
                    "   section_id     ," .
                    "   section_name   ," .
                    "   section_note   ," .
                    "   del_flag        " .
                    " FROM              " .
                    "   section_master  " .
                    " ORDER BY          " .
                    "   section_id ASC  " ;

        // SQL実行
        $objUser = $db->QueryDB( $strSQL );

        // 実行結果行数を取得
        $intRows = $db->GetNumRowsDB( $objUser );

        // 取得行を配列化
        $arrayUser = array();
        for( $intCount = 0; $intCount < $intRows; $intCount++ )
        {
            $arrayResult = $db->FetchArrayDB( $objUser );

            // 取得したデータを配列に格納
            $arrayUser[] = array(   'section_id'    => $arrayResult["section_id"]   ,
                                    'section_name'  => $arrayResult["section_name"] ,
                                    'section_note'  => $arrayResult["section_note"] ,
                                    'del_flag'      => $arrayResult["del_flag"]     );
        }

        return $arrayUser;
    }

    // ***********************************************************************
    // * 関数名     ：事業部情報取得処理(１件のみ)
    // * 返り値     ：成功:SELECT結果、失敗:null
    // ***********************************************************************
    function GetSectionOne( $intSectionID )
    {
        // ＤＢ接続
        $db = new WrapDB();
        $db->ConnectDB();

        // SELECTする
        $strSQL =   " SELECT            " .
                    "   section_id     ," .
                    "   section_name   ," .
                    "   section_note   ," .
                    "   del_flag        " .
                    " FROM              " .
                    "   section_master  " .
                    " WHERE 0=0         " .
                    " AND   section_id =" . SQLAid::escapeNum( $intSectionID );
        // SQL実行
        $objUser = $db->QueryDB( $strSQL );

        // 実行結果行数を取得
        $intRows = $db->GetNumRowsDB( $objUser );

        // 取得したデータを配列に格納
        if( 0 < $intRows )
        {
            // 取得行を配列化
            $arrayResult = $db->FetchArrayDB( $objUser );

            $arrayUser = array(     'section_id'    => $arrayResult["section_id"]   ,
                                    'section_name'  => $arrayResult["section_name"] ,
                                    'section_note'  => $arrayResult["section_note"] ,
                                    'del_flag'      => $arrayResult["del_flag"]     );
        }
        else
        {
            $arrayUser = null;
        }

        return $arrayUser;
    }

    // ************************************************************
    // * 関数名       ：GetTagSection()
    // ************************************************************/
    function GetTagSection( $strSelectedID  ,   // 選択されたID
                            $intTagType     ,   // タグの種類(0:Option、1:Radio、2:CheckBox)
                            $strTagName     )   // タグに含むname(Option:未使用、Radio・Check:name属性に使用)
    {
        $strStatusTag = "";

        // ＤＢ接続
        $db = new WrapDB();
        $db->ConnectDB();

        // SELECT用SQL
        $strSelectSQL = " SELECT            " .
                        "   section_id     ," .
                        "   section_name   ," .
                        "   section_note   ," .
                        "   del_flag        " .
                        " FROM              " .
                        "   section_master  " .
                        " WHERE                     " .
                        "   del_flag = 0            " .
                        " ORDER BY                  " .
                        "   section_id              " ;

        // SQL実行
        $objSection = $db->QueryDB( $strSelectSQL );

        // 取得行数
        $rowSection = $db->GetNumRowsDB( $objSection );

        // 取得行数が１つでもあった場合
        if( 0 < $rowSection )
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

            for( $intCount = 0; $intCount < $rowSection; $intCount++ )
            {
                // 事業部なし
                if( 0 == $intCount )
                {
                    $strStatusTag = $strStatusTag .
                                    sprintf( $strTagFormat, $strTagNameBuf, "", "", "事業部なし" );
                }

                // レコードを配列で取得
                $arrayResult = $db->FetchArrayDB( $objSection );

                // 選択されたものならSELECTED
                $strSelected = "";
                if( 0 < strlen( $strSelectedID ) && intval( $arrayResult["section_id"] ) == intval( $strSelectedID ) )
                {
                    $strSelected   = $strSelectOrChecked;
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
                                sprintf( $strTagFormat, $strTagNameBuf, $arrayResult["section_id"], $strSelected, $arrayResult["section_name"] );
            }
        }

        unset( $objSection );

        return $strStatusTag;
    }

    // ************************************************************
    // * 関数名       ：GetSectionName()
    // ************************************************************/
    function GetSectionName( $strSelectedID )   // 選択されたID
    {
        $strStatusName = "";

        if( 0 == strlen( $strSelectedID ) )
        {
            $strStatusName = "事業部なし";
            return $strStatusName;
        }

        // ＤＢ接続
        $db = new WrapDB();
        $db->ConnectDB();

        $strSelectSQL = " SELECT            " .
                        "   section_id     ," .
                        "   section_name   ," .
                        "   section_note   ," .
                        "   del_flag        " .
                        " FROM              " .
                        "   section_master  " .
                        " WHERE             " .
                        "   del_flag = 0    " .
                        " AND section_id  = " . SQLAid::escapeNum( $strSelectedID ) ;

        // SQL実行
        $objStatus = $db->QueryDB( $strSelectSQL );

        // 取得行数
        $rowStats = $db->GetNumRowsDB( $objStatus );

        // レコードを配列で取得
        $arrayResult = $db->FetchArrayDB( $objStatus );

        $strStatusName = $arrayResult["section_name"];

        unset( $objStatus );

        return $strStatusName;
    }

}
