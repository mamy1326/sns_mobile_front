<?php
/* ================================================================================
 * ファイル名   ：db_conf.php
 * タイトル     ：ＤＢ関連定義configファイル
 * 作成者       ：間宮 直樹
 * 作成日       ：2008/07/30
 * 内容         ：データベース接続に関する内容を定義する
 * 更新履歴*******************************************************************
 * 【変更日】  【変更者】      【変更箇所】        【変更理由と変更内容】
 *  2008/07/30  間宮 直樹       全体                新規作成
 * ================================================================================*/

// トランザクション指定用
define("db_strDB_COMMIT"    , 1 );                            //トランザクション[COMMIT]
define("db_strDB_ROLLBACK"  , 2 );                            //トランザクション[ROLLBACK]
define("DB_COM_COMMIT"      , 1 );
define("DB_COM_ROLLBACK"    , 2 );

// ＤＢ接続用
define("db_strHostName"         ,   "localhost"         );          // データベースサーバ名
define("db_strDataBaseName"     ,   "sns_mobile"        );          // データベース名
define("db_strUserName"         ,   "snsdb"             );          // ユーザー
define("db_strPassword"         ,   "SnsM0b1le"         );          // パスワード

// DB種類
define("db_flgDB_Kind"      ,2 );        // PEAR::MySQL

// DB種別[PostgresSQL]
define("DB_KIND_PG"         ,1 );

// DB種別[MySQL]
define("DB_KIND_MYSQL"      ,2 );

// DB種別[OCI]
define("DB_KIND_OCI"        ,3 );

// DB種別[Oracle]
define("DB_KIND_ORACLE"     ,4 );

// DB種別[POCI]
define("DB_KIND_POCI"       ,5 );

// DB種別[MySQL(MDB2)]
define("DB_KIND_MDB2_MYSQL" ,6 );

// DB種別[MySQL(PEAR::DB)]
define("DB_KIND_PEAR_MYSQL" ,7 );


// DB値define
define("DB_DATE_DEFAULT"    , "0000-00-00 00:00:00" );


?>
