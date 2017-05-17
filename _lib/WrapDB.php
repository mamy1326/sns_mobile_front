<?php
/***************************************************************************************************
 * ファイル名   ：WrapDB.php
 * タイトル     ：ＤＢ接続、操作関連のラップクラス
 * 関数         ：なし
 * 作成者       ：間宮 直樹
 * 作成日       ：2008.07.30
 * 内容         ：データベースに直接アクセスする処理をすべてまとめたクラス
 * 更新履歴 ****************************************************************************************
 * 【変更日】  【変更者】      【変更箇所】        【変更理由と変更内容】
 *  2008/07/28  間宮 直樹       全体                新規作成
 ***************************************************************************************************/

// includeするライブラリ・コンフィグファイルのパスを定義する
define( 'CONFIG_DIR', '../_config/');

// ライブラリ・コンフィグファイルをinclude
include_once( CONFIG_DIR . 'db_conf.php' );             // ＤＢ関連定義ファイル

// カレントのキャッシュの有効期限を設定(600分=10時間)。現在のデータベースの実行最大時間を設定する。
session_cache_expire(600);

/******************************************************************************
 * クラス名     ：WrapDB
 * 概要         ：データベース関連処理のラップクラス
 * プロパティ   ：$objDB                    データベースの接続識別子
 *                $strHostName              データベースサーバホスト名
 *                $strDBName                データベース名
 *                $strUserName              ユーザ名
 *                $strPassword              パスワード
 *                $strLastSQL               直前に実行したSQL文
 * メソッド     ：cl_WrapDB()               コンストラクタ（初期値設定）
 *                ConnectDB()               データベースへ接続
 *                CloseDB()                 データベースから切断
 *                QueryDB                   指定されたSQL文を実行
 *                ExecQueryDB               データベースがOCIのときのみ有効。
 *                                          バインド使用時にDB_Queryと合わせて使用
 *                GetNumRowsDB              ＳＱＬ文の実行結果の行数を取得
 *                FetchArrayDB              ＳＱＬ文の実行結果を連想配列に置換
 *                FreeResultDB              結果保持用メモリを開放
 *                ErrorResultDB             直近に実行されたSQL実行時の
 *                                          エラーメッセージを返す
 *                TransactDB                引数で指定されたトランザクションを実行
 *                AutoCommitOffDB           トランザクションの有効化
 ******************************************************************************/

/**
 * DB接続クラス
 *
 * DBへの接続を抽象化するクラス
 *
 */
class WrapDB
{
    //**************************************************************************
    //* cd_WrapDBクラスのプロパティ
    //**************************************************************************/
    var $objDB;                                 //データベースの接続識別子
    var $strDBKind;                             //データベース種別
    var $strHostName;                           //データベースサーバホスト名
    var $strDBName;                             //データベース名
    var $strUserName;                           //ユーザ名
    var $strPassword;                           //パスワード
    var $strLastSQL;                            //直前に実行したSQL文

    //**************************************************************************
    //* メソッド名  ：WrapDB(コンストラクタ)
    //* 概要        ：コンストラクタ（プロパティの初期値設定）
    //* 引数        ：なし
    //* 復帰値      ：なし
    //**************************************************************************/
    function WrapDB()
    {
        //データベース種別設定
        $this->strDBKind        = db_flgDB_Kind;
        //データベースサーバホスト名設定
        $this->strHostName      = db_strHostName;
        //データベース名設定
        $this->strDBName        = db_strDataBaseName;
        //データベースユーザ名設定
        $this->strUserName      = db_strUserName;
        //データベースパスワード設定
        $this->strPassword      = db_strPassword;
        //初期設定終了
        return;
    }
    
    //**************************************************************************
    //* 関数名  ：ConnectDB
    //* 概要    ：データベースへ接続する
    //* 引数    ：なし
    //* 復帰値  ：なし
    //**************************************************************************/
    function ConnectDB()
    {
        //ＤＢ種別によって処理をわける
        switch ( $this->strDBKind )
        {
            //PostgresSQLの場合
            case DB_KIND_PG:
                //データベースに接続し、プロパティのデータベース識別子にリソースを格納
                $this->objDB = pg_connect( "host=" . $this->strHostName . " dbname=" . $this->strDBName . " user=" . $this->strUserName . " password=" . $this->strPassword ) or die("データベース接続エラー");
                //処理を抜ける
                break;
            //MySQLの場合
            case DB_KIND_MYSQL:
                //データベースに接続し、プロパティのデータベース識別子にリソースを格納
                $this->objDB = mysql_connect( $this->strHostName, $this->strUserName, $this->strPassword ) or die("データベース接続エラー");
                mysql_select_db( $this->strDBName, $this->objDB );
                $objResult = mysql_query ( "SET NAMES utf8", $this->objDB );
                //処理を抜ける
                break;
            //OCIの場合
            case DB_KIND_OCI:
                //データベースに接続し、プロパティのデータベース識別子にリソースを格納
                $this->objDB = OCILogon( $this->strUserName, $this->strPassword, $this->strDBName ) or die("データベース接続エラー");
                //処理を抜ける
                break;
            //Oracleの場合
            case DB_KIND_ORACLE:
                //データベースに接続し、プロパティのデータベース識別子にリソースを格納
                $this->objDB = Ora_Logon( $this->strUserName . "@" . $this->strHostName , $this->strPassword );
                //処理を抜ける
                break;
            //POCIの場合
            case DB_KIND_POCI:
                require_once("DB.php");
                //データベースに接続し、プロパティのデータベース識別子にリソースを格納
                $this->objDB =& DB::connect( "oci8://" . $this->strUserName . ":" . $this->strPassword . "@" . $this->strHostName );
                $this->objDB->setOption( 'optimize', 'portability' );
                //処理を抜ける
                break;
            //MySQL(MDB2)
            case DB_KIND_MDB2_MYSQL:
                require_once 'MDB2.php';
                $dsn = array(
                            'phptype'  => 'mysql',
                            'username' => $this->strUserName,
                            'password' => $this->strPassword,
                            'hostspec' => $this->strHostName,
                            'database' => $this->strDBName,
                            );
                $options = array(
                            'debug'       => 2,
                            'portability' => MDB2_PORTABILITY_ALL,
                            );
                            
                $this->objDB =& MDB2::connect($dsn, $options);
                if (PEAR::isError($this->objDB)) {
                    die($this->objDB->getMessage());
                }
                $this->objDB->query('SET names utf8;');
                $this->objDB->setFetchMode(MDB2_FETCHMODE_ASSOC);
                break;
            //MySQL(PEAR::DB)
            case DB_KIND_PEAR_MYSQL:
                require_once("DB.php");
                $this->objDB =& DB::connect( 'mysql://' . $this->strUserName . ':' . $this->strPassword . '@' . $this->strHostName . '/' . $this->strDBName );
                $this->objDB->setOption( 'optimize', 'portability' );
                // SET NAMES クエリの発行(MySQL4.1用)
                $sql = "SET NAMES utf8";
                $result = $this->objDB->query($sql);
                break;
            //その他の場合
            default:
                //処理を抜ける
                break;
        }

        //プロパティのデータベース接続識別子がfalseでない場合
        if( false != $this->objDB )
        {
            //接続成功で呼び出し元に復帰
            return TRUE;
        }
        //プロパティのデータベース接続識別子がfalseの場合
        else
        {
            //接続失敗で呼び出し元に復帰
            return FALSE;
        }
    }

    //*****************************************************************************
    //* 関数名  ：CloseDB
    //* 概要    ：データベースから切断
    //* 引数    ：なし
    //* 復帰値  ：なし
    //*****************************************************************************/
    function CloseDB()
    {
        //ＤＢ種別によって処理をわける
        switch ( $this->strDBKind )
        {
            //PostgreSQLの場合
            case DB_KIND_PG:
                //データベースから切断
                pg_close( $this->objDB );
                //処理を抜ける
                break;
            //MySQLの場合
            case DB_KIND_MYSQL:
                //必要な終了作業なし。
                //（mysql_pconnectで接続しているので、スクリプト終了時に自動的にクローズする。）
                //処理を抜ける
                break;
            //OCIの場合
            case DB_KIND_OCI:
                //データベースから切断
                OCILogoff( $this->objDB );
                //処理を抜ける
                break;
            //Oracleの場合
            case DB_KIND_ORACLE:
                //データベースから切断
                Ora_Logoff( $this->objDB );
                //処理を抜ける
                break;
            //POCIの場合
            case DB_KIND_POCI:
                //データベースから切断
                $this->objDB->disconnect();
                //処理を抜ける
                break;
            //MySQL(PEAR::DB)
            case DB_KIND_PEAR_MYSQL:
                break;
            //その他の場合
            default:
                //処理を抜ける
                break;
        }

        //呼び出しもとに復帰する
        return;
    }

    //*****************************************************************************
    //* 関数名  ：QueryDB
    //* 概要    ：指定されたSQL文を実行
    //* 引数    ：$a_strSQL     …実行するＳＱＬ文
    //*           $a_blnBind    …BIND種別（DBがOCIの時のみ有効）
    //*                           null以外の場合はOCI_Parseのみ実行
    //* 復帰値  ：$objResult[実行結果]
    //*****************************************************************************/
    function QueryDB( $a_strSQL, $a_blnBind = null )
    {
        //ＤＢ種別によって処理をわける
        switch ( $this->strDBKind )
        {
            //PostgresSQLの場合
            case DB_KIND_PG:
                $objResult = pg_query( $this->objDB, $a_strSQL );
                break;
            //MySQLの場合
            case DB_KIND_MYSQL:
                $objResult = mysql_query ( $a_strSQL, $this->objDB );
                break;
            //OCIの場合
            case DB_KIND_OCI:
                //引数のSQL文が指定されている場合
                if ( $a_strSQL )
                {
                    //BIND種別が指定されていない場合
                    If ( null == $a_blnBind )
                    {
                        //標準モード
                        $objResult = OCIParse( $this->objDB, $a_strSQL );
                        $oeResult = @OCIExecute( $objResult, OCI_DEFAULT );
                    }
                    //BIND種別が指定されている場合
                    Else
                    {
                        //BINDモード
                        $objResult = OCIParse( $this->objDB, $a_strSQL );
                        $oeResult = true;
                    }
                }
                //引数のSQL文が指定されていない場合
                else
                {
                    //直前SQL吐き出しモード
                    return $this->strLastSQL;
                }
                break;
            //Oracleの場合
            case DB_KIND_ORACLE:
                $objResult = Ora_Open($this->objDB);
                var_dump($a_strSQL);
                $oeResult = Ora_Parse($obj_result, $a_strSQL);
                var_dump($objResult);
                var_dump($oeResult);
                $oeResult = Ora_Exec($objResult);
                var_dump($oeResult);
                break;
            //POCIの場合
            case DB_KIND_POCI:
                $objResult = $this->objDB->query($a_strSQL);
                if( DB::isError($objResult))
                {
                    $oeResult = false;
                }
                break;
            //MySQL(MDB2)
            case DB_KIND_MDB2_MYSQL:
                $objResult =& $this->objDB->query( $a_strSQL );
                // 結果がエラーでないかどうかを常にチェックします
                if (PEAR::isError($objResult)) {
                    die($objResult->getMessage());
                }
                break;
            //MySQL(PEAR::DB)
            case DB_KIND_PEAR_MYSQL :
                $objResult = $this->objDB->query($a_strSQL);
                if( DB::isError($objResult)){
                    $objResult = false;
                }
                break;
            //その他の場合
            default:
                break;
        }

        //SQL文の実行結果に失敗した場合
        if( is_null($objResult) || $objResult === false || $oeResult === false )
        {
            //直前に実行したSQL文保存用変数にnullを設定する
            $this->strLastSQL = null;
        }
        //SQL文の実行に成功した場合
        else
        {
            //直前に実行したSQL文保存用変数に引数のSQL文を実行する
            $this->strLastSQL = $a_strSQL;
        }
        return $objResult;
    }

    //*****************************************************************************
    //* 関数名  ：ExecQueryDB
    //* 引数    ：$a_strParseResult     …QueryDBの結果
    //*           $a_strSQL             …実行するSQL文
    //* 復帰値  ：$objResult            …実行結果
    //* 概要    ：データベースがOCIのときのみ有効。他の場合は何も行わない。
    //*           バインド使用時にDB_Queryと合わせて使用
    //*****************************************************************************/
    function ExecQueryDB( $a_strParseResult, $a_strSQL = null )
    {
        switch ( $this->strDBKind )
        {
            //PostgresSQLの場合
            case DB_KIND_PG:
                //何も処理をせずに抜ける
                break;
            //MySQLの場合
            case DB_KIND_MYSQL:
                //何も処理をせずに抜ける
                break;
            //OCIの場合
            case DB_KIND_OCI:
                //引数が指定されている場合
                if ( $a_strParseResult )
                {
                    //直前にパースしたSQLを実行する
                    $objResult = OCIExecute( $a_strParseResult, OCI_DEFAULT );
                }
                //引数が指定されていない場合（直前SQL吐き出しモード）
                else
                {
                    //直前に実行されたSQL文を復帰値に設定して復帰
                    return $this->strLastSQL;
                }
                break;
            //Oracleの場合
            case DB_KIND_ORACLE:
                //何も処理をせずに抜ける
                break;
            //POCIの場合
            case DB_KIND_POCI:
                //何も処理をせずに抜ける
                break;
            //MySQL(PEAR::DB)
            case DB_KIND_PEAR_MYSQL :
                break;
            //その他の場合
            default:
                //何も処理をせずに抜ける
                break;
        }

        //実行結果がnullかFalseの場合（実行失敗）
        if( is_null($objResult) || $objResult === false )
        {
            //直前に実行したSQL文にnullを設定
            $this->strLastSQL = null;
            //デバッグモードがONの場合
            if( c_dbg_mode == 1 )
            {
?>              <TABLE align="center" width="85%" border="1">
                    <TR>
                        <TD bgcolor="#B5DBE2" colspan="2">
                            <b>ＤＢクエリエラー</b>
                        </TD>
                    </TR>
                    <TR>
                        <TD colspan="2">
                            <pre><? print_r( DB_Error_Result( $result ) );  ?></pre>
                        </TD>
                    </TR>
                    <TR>
                        <TD colspan="2">
                        <p align="right"><b>バックトレース</b></p>
                        <pre wrap>
<?                          print_r(debug_backtrace());
?>                      </pre>
                        </TD>
                    </TR>
                </TABLE>
<?          }
            else
            {
?>              <TABLE align="center" width="400" height="150" border="1">
                    <TR>
                        <TD align="center" valign="middle" >
                            データベースエラー。</br>ご迷惑をお掛けしております。
                        </TD>
                    </TR>
                <TABLE>
<?          }
        }
        //実行結果がnullでもFalseでもない場合（実行成功）
        else
        {
            //直前に実行したSQL文に引数のSQL文を格納
            $this->strLastSQL = $a_strSQL;
        }
        return $objResult;
    }

    //*****************************************************************************
    //* 関数名  ：GetNumRowsDB
    //* 引数    ：$a_objSQLResult   …ＳＱＬ文の実行結果
    //* 復帰値  ：$intRows          …成功時[ＳＱＬ文実行結果の行数]
    //*                               失敗時[null]
    //* 概要    ：ＳＱＬ文の実行結果の行数を取得
    //*****************************************************************************/
    function GetNumRowsDB( $a_objSQLResult )
    {
        //ＤＢ種別によって処理をわける
        switch ( $this->strDBKind )
        {
            //PostgresSQLの場合
            case DB_KIND_PG:
                //実行結果の行数を取得
                $intRows = pg_num_rows( $a_objSQLResult );
                break;
            //MySQLの場合
            case DB_KIND_MYSQL:
                //実行結果の行数を取得
                $intRows = mysql_num_rows( $a_objSQLResult );
                break;
            //OCIの場合
            case DB_KIND_OCI:
                //直前に実行したSQL文を取得
                $this->strLastSQL = DB_Query( null, null );
                //nullの場合
                if( true == is_null( $this->strLastSQL ) )
                {
                    //nullを返す
                    return null;
                }
                //実行結果行数を取得するSQL文を作成
                $strSQL = 'SELECT COUNT(*) FROM ( ' . $this->strLastSQL . ' )';
                //SQL文をパース後、実行
                $objRSsub = OCIParse( $this->objDB, $strSQL );
                $intRows = @OCIExecute( $objRSsub, OCI_DEFAULT );
                //パース、実行に失敗した場合
                if( true == is_null( $objRSsub ) )
                {
                    //falseを返す
                    return null;
                }
                //結果配列を取得する
                OCIFetchInto($objRSsub, $aryResult);
                //行数を取得
                $intRows = $aryResult[0];
                //リソースを解放
                OCIFreeStatement( $objRSsub );
                //処理を抜ける
                break;
            //Oracleの場合
            case DB_KIND_ORACLE:
                //行数を取得
                $intRows = Ora_NumRows( $a_objSQLResult );
                var_dump($a_objSQLResult);
                //処理を抜ける
                break;
            //POCIの場合
            case DB_KIND_POCI:
                //行数を取得
                $intRows = $a_objSQLResult->numRows();
                //処理を抜ける
                break;
            //MySQL(MDB2)
            case DB_KIND_MDB2_MYSQL:
                $intRows = $a_objSQLResult->numRows();
                break;
            //MySQL(PEAR::DB)
            case DB_KIND_PEAR_MYSQL :
                $intRows = $a_objSQLResult->numRows();
                break;
            //その他の場合
            default:
                //処理を抜ける
                break;
        }

        return $intRows;
    }

    //*****************************************************************************
    //* 関数名  ：FetchArrayDB
    //* 引数    ：$a_objSQLResult   …ＳＱＬ文の実行結果
    //*           $a_intRow         …DB種別がMySQLのときのみ有効
    //*                               結果を取得する行番号を指定
    //* 復帰値  ：$objResult        …成功時[連想配列に置換した実行結果]
    //*                               失敗時[null]
    //* 概要    ：ＳＱＬ文の実行結果を連想配列に置換
    //*****************************************************************************/
    function FetchArrayDB( $a_objSQLResult, $a_intRow = null )
    {
        //ＤＢ種別によって処理をわける
        switch ( $this->strDBKind )
        {
            //PostgresSQLの場合
            case DB_KIND_PG:
                //実行結果を連想配列に置換
                $objResult = pg_fetch_array( $a_objSQLResult , $a_intRow);
                //処理を抜ける
                break;
            //MySQLの場合
            case DB_KIND_MYSQL:
                //引数が２つある場合
                if( $iRow != null)
                {
                    //行指定位置を変更
                    $flgResultDataSeek = mysql_data_seek( $a_objSQLResult, $a_intRow );
                    //行指定位置の変更に失敗した場合
                    if( false == $flgResultDataSeek )
                    {
                        //nullで呼び出し元に復帰
                        return null;
                    }
                }
                //実行結果を連想配列に置換
                $objResult = mysql_fetch_array( $a_objSQLResult );
                //処理を抜ける
                break;
            //OCIの場合
            case DB_KIND_OCI:
                //結果配列を取得する
                OCIFetchInto( $a_objSQLResult, $objResult, OCI_ASSOC );
                //結果配列の取得に失敗した場合
                if ( $objResult == null )
                {
                    //nullで呼び出し元に復帰
                    return null;
                }
                //実行結果を連想配列に置換
                $objResult = array_change_key_case($objResult,CASE_LOWER);
                //処理を抜ける
                break;
            //Oracleの場合
            case DB_KIND_ORACLE:
                //実行結果を連想配列に置換
                Ora_Fetch_Into( $a_objSQLResult, &$objResult, ORA_FETCHINTO_ASSOC );
                //処理を抜ける
                break;
            //POCIの場合
            case DB_KIND_POCI:
                //実行結果を連想配列に置換
                $objResult = $a_objSQLResult->fetchRow( DB_FETCHMODE_ASSOC );
                //処理を抜ける
                break;
            //MySQL(MDB2)
            case DB_KIND_MDB2_MYSQL:
                $objResult = $a_objSQLResult->fetchRow();
                break;
            //MySQL(PEAR::DB)
            case DB_KIND_PEAR_MYSQL :
                $objResult = $a_objSQLResult->fetchRow( DB_FETCHMODE_ASSOC );
                break;
            //その他の場合
            default:
                //処理を抜ける
                break;
        }

        //実行結果を復帰値に設定して呼び出し元に復帰
        return $objResult;
    }

    //*****************************************************************************
    //* 関数名  ：FreeResultDB
    //* 引数    ：$objSQLResult     …ＳＱＬ文の実行結果
    //* 復帰値  ：なし
    //* 概要    ：結果保持用メモリを開放する
    //*           結果メモリの使用量が多い場合のみ必要の可能性あり。通常は必要なし。
    //*****************************************************************************/
    function FreeResultDB( $a_objSQLResult )
    {
        //ＤＢ種別によって処理をわける
        switch ( $this->strDBKind )
        {
            //PostgresSQLの場合
            case DB_KIND_PG:
                //引数で指定された領域を開放する
                pg_free_result( $a_objSQLResult );
                //処理を抜ける
                break;
            //MySQLの場合
            case DB_KIND_MYSQL:
                //引数で指定された領域を開放する
                mysql_free_result( $a_objSQLResult );
                //処理を抜ける
                break;
            //OCIの場合
            case DB_KIND_OCI:
                //引数で指定された領域を開放する
                OCIFreeStatement( $a_objSQLResult );
                //処理を抜ける
                break;
            //Oracleの場合
            case DB_KIND_ORACLE:
                //引数で指定された領域を開放する
                Ora_Close( $a_objSQLResult );
                //処理を抜ける
                break;
            //POCIの場合
            case DB_KIND_POCI:
                //引数で指定された領域を開放する
                $a_objSQLResult->free();
                //処理を抜ける
                break;
            //MySQL(PEAR::DB)
            case DB_KIND_PEAR_MYSQL:
                $a_objSQLResult->free();
                break;
            //その他の場合
            default:
                //処理を抜ける
                break;
        }
        //呼び出しもとに復帰する
        return;
    }

    //*****************************************************************************
    //* 関数名  ：ErrorResultDB
    //* 引数    ：$a_objSQLResult   …ＳＱＬ文の実行結果
    //* 復帰値  ：$strErrorMsg      …直近に実行されたＤＢのエラーメッセージ
    //* 概要    ：直近に実行されたSQL実行時のエラーメッセージを返す
    //*           エラーが発生していない場合の復帰値は不定。
    //*           エラーが発生したときのみ当メソッドを呼び出すこと。
    //*****************************************************************************/
    function ErrorResultDB( $a_objSQLResult )
    {
        //ＤＢ種別によって処理をわける
        switch ( $this->strDBKind )
        {
            //PostgresSQLの場合
            case DB_KIND_PG:
                //直近のエラーメッセージを取得
                $strErrorMsg = pg_last_error( $this->objDB );
                //処理を抜ける
                break;
            //MySQLの場合
            case DB_KIND_MYSQL:
                //直近のエラーメッセージを取得
                //$strErrorMsg = mysql_error( $a_objSQLResult );
                $strErrorMsg = mysql_errno( $a_objSQLResult );
                //処理を抜ける
                break;
            //OCIの場合
            case DB_KIND_OCI:
                //直近のエラーメッセージを取得
                $strErrorMsg = ocierror( $a_objSQLResult );
                //処理を抜ける
                break;
            //Oracleの場合
            case DB_KIND_ORACLE:
                //直近のエラーメッセージを取得
                $strErrorMsg = Ora_ErrorCode( $a_objSQLResult );
                //処理を抜ける
                break;
            //POCIの場合
            case DB_KIND_POCI:
                //直近のエラーメッセージを取得
                $strErrorMsg = DB::isError( $a_objSQLResult );
                //処理を抜ける
                break;
            //MySQL(PEAR::DB)
            case DB_KIND_PEAR_MYSQL :
                $strErrorMsg = DB::isError( $a_objSQLResult );
                break;
            //その他の場合
            default:
                //処理を抜ける
                break;
        }

        //取得したメッセージを復帰値に設定して呼び出し元に復帰する
        return $strErrorMsg;
    }

    //*****************************************************************************
    //* 関数名  ：TransactDB
    //* 引数    ：$a_strTrunsactKind    …COMMITの場合  [DB_COM_COMMIT]
    //*                                   ROLLBACKの場合[DB_COM_ROLLBACK ]
    //* 復帰値  ：なし
    //* 概要    ：引数で指定されたトランザクションを実行する。
    //*****************************************************************************/
    function TransactDB( $a_strTrunsactKind )
    {
        //ＤＢ種別によって処理をわける
        switch ( $this->strDBKind )
        {
            //PostgresSQLの場合
            case DB_KIND_PG:
                //指定されたトランザクションによって処理をわける
                switch( $a_strTrunsactKind )
                {
                    //COMMITの場合
                    case DB_COM_COMMIT:
                        //COMMITを実行
                        $result = pg_query( $this->objDB, "COMMIT" );
                        //処理を抜ける
                        break;
                    //ROLLBACKの場合
                    case DB_COM_ROLLBACK:
                        //ROLLBACKを実行
                        $result = pg_query( $this->objDB, "ROLLBACK" );
                        //処理を抜ける
                        break;
                    //その他の場合
                    default:
                        //処理を抜ける
                        break;
                }
                break;
            //MySQLの場合
            case DB_KIND_MYSQL:
                //指定されたトランザクションによって処理をわける
                switch( $a_strTrunsactKind )
                {
                    //COMMITの場合
                    case DB_COM_COMMIT:
                        //COMMITを実行
                        $result = mysql_query( "COMMIT", $this->objDB );
                        //処理を抜ける
                        break;
                    //ROLLBACKの場合
                    case DB_COM_ROLLBACK:
                        //ROLLBACKを実行
                        $result = mysql_query( "ROLLBACK", $this->objDB );
                        //処理を抜ける
                        break;
                    //その他の場合
                    default:
                        //処理を抜ける
                        break;
                }
                break;
            //OCIの場合
            case DB_KIND_OCI:
                //指定されたトランザクションによって処理をわける
                switch( $a_strTrunsactKind )
                {
                    //COMMITの場合
                    case DB_COM_COMMIT:
                        //COMMITを実行
                        OCICommit( $this->objDB );
                        //処理を抜ける
                        break;
                    //ROLLBACKの場合
                    case DB_COM_ROLLBACK:
                        //ROLLBACKを実行
                        OCIRollback( $this->objDB );
                        //処理を抜ける
                        break;
                    //その他の場合
                    default:
                        //処理を抜ける
                        break;
                }
                break;
            //Oracleの場合
            case DB_KIND_ORACLE:
                //指定されたトランザクションによって処理をわける
                switch( $a_strTrunsactKind )
                {
                    //COMMITの場合
                    case DB_COM_COMMIT:
                        //COMMITを実行
                        Ora_Commit( $this->objDB );
                        //処理を抜ける
                        break;
                    //ROLLBACKの場合
                    case DB_COM_ROLLBACK:
                        //ROLLBACKを実行
                        Ora_Rollback( $this->objDB );
                        //処理を抜ける
                        break;
                    //その他の場合
                    default:
                        //処理を抜ける
                        break;
                }
                //処理を抜ける
                break;
            //POCIの場合
            case DB_KIND_POCI:
                //指定されたトランザクションによって処理をわける
                switch( $a_strTrunsactKind )
                {
                    //COMMITの場合
                    case DB_COM_COMMIT:
                        //COMMITを実行
                        $this->objDB->commit();
                        //処理を抜ける
                        break;
                    //ROLLBACKの場合
                    case DB_COM_ROLLBACK:
                        //ROLLBACKを実行
                        $this->objDB->rollback();
                        //処理を抜ける
                        break;
                }
                //処理を抜ける
                break;
            //MySQL(PEAR::DB)
            case DB_KIND_PEAR_MYSQL :
                switch( $a_strTrunsactKind )
                {
                    case DB_COM_COMMIT:
                            $this->objDB->query("COMMIT");
                            break;
                    case DB_COM_ROLLBACK:
                            $this->objDB->query("ROLLBACK");
                            break;
                }
                break;
            //その他の場合
            default:
                //処理を抜ける
                break;
        }
        //呼び出し元に復帰
        return;
    }

    //*****************************************************************************
    //* 関数名  ：DB_AutoCommit_Off
    //* 引数    ：なし
    //* 復帰値  ：なし
    //* 概要    ：トランザクションの有効化(データベースがMySQLのときのみ有効)
    //*****************************************************************************/
    function AutoCommitOffDB()
    {
        //データ種別によって処理をわける
        switch ( $this->strDBKind )
        {
            //PgSQLの場合
            case DB_KIND_PG:
                //トランザクション開始処理
                $result = pg_query( $this->objDB, "BEGIN" );
                //処理を抜ける
                break;
            //MySQLの場合
            case DB_KIND_MYSQL:
                //autocommitの値を0に設定
                mysql_query("set autocommit = 0 " , $this->objDB);
                //処理を抜ける
                break;
            //MySQL(PEAR::DB)
            case DB_KIND_PEAR_MYSQL:
                $this->objDB->query("SET AUTOCOMMIT = 0");
                $this->objDB->query("BEGIN");
                //$a_objSQLResult->free();
                break;
            //その他の場合
            default:
                //処理を抜ける
                break;
        }
        //呼び出しもとに復帰
        return;
    }

    //*****************************************************************************
    //* 関数名  ：GetAffectedRowsDB
    //* 引数    ：$a_objSQLResult : ＳＱＬ実行結果レコードセット
    //* 復帰値  ：影響を受けたタプルを返す。影響無しならFALSEが返る。
    //* 概要    ：更新系ＳＱＬ処理(UPDATE,INSERT)によって更新された行数を返す
    //*****************************************************************************/
    function GetAffectedRowsDB( $a_objSQLResult )
    {
        //データ種別によって処理をわける
        switch ( $this->strDBKind )
        {
            //PgSQLの場合
            case DB_KIND_PG:
                //影響を受けたタプルを返す
                return pg_cmdtuples( $a_objSQLResult );
                break;
            //その他の場合
            default:
                //処理を抜ける
                break;
        }
        return;
    }

    //*****************************************************************************
    //* 関数名  ：DB_GetOne (PEAR使用時のみ)
    //* 引数    ：$strSQL[実行するＳＱＬ文]
    //* 復帰値  ：$result[実行結果]
    //* 概要    ：クエリから先頭カラムの最初の結果を取得
    //*****************************************************************************/
    function getOneDB( $strSQL )
    {
        $db =   $this->objDB;

        switch ( $this->strDBKind )
        {
            case DB_KIND_PG:
                $result = NULL;
                break;
            case DB_KIND_MYSQL:
                $result = $db->getOne($strSQL);
                if( DB::isError($result))
                {
                    $oeResult = false;
                }
                break;
            case DB_KIND_OCI:
                $result = NULL;
                break;
            case DB_KIND_ORACLE:
                $result = NULL;
                break;
            case DB_KIND_POCI:
                $result = $db->getOne($strSQL);
                if( DB::isError($result))
                {
                    $oeResult = false;
                }
                break;
            case DB_KIND_PEAR_MYSQL:
                $result = $db->getOne($strSQL);
                if( DB::isError($result))
                {
                    $oeResult = false;
                }
                break;
            default:
                break;
        }

        if( is_null($result) || $result === false || $oeResult === false )
        {
            //クエリエラー
            $strLastSQL = null;
        }
        else
        {
            //成功
            $strLastSQL = $strSQL;
        }

        return( $result );
    }


    function getLastInsertId()
    {
        $db =   $this->objDB;

        switch ( $this->strDBKind )
        {
            case DB_KIND_PG:
                break;
            case DB_KIND_MYSQL:
                break;
            case DB_KIND_OCI:
                break;
            case DB_KIND_ORACLE:
                break;
            case DB_KIND_POCI:
                break;
            case DB_KIND_PEAR_MYSQL:
                $result = $db->query('SELECT LAST_INSERT_ID();');
                $objResult = $result->fetchRow( DB_FETCHMODE_ASSOC );
                return $objResult['LAST_INSERT_ID()'];
                break;
            default:
                break;
        }
    }
}
