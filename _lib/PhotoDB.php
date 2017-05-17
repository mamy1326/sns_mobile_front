<?php
/* ================================================================================
 * ファイル名   ：PhotoDB.php
 * タイトル     ：フォトアルバム情報取得・更新用クラス
 * 作成者       ：間宮 直樹
 * 作成日       ：2008/08/17
 * 更新履歴*******************************************************************
 * 【変更日】  【変更者】      【変更箇所】        【変更理由と変更内容】
 *  2008/08/17  間宮 直樹       全体                新規作成
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

class PhotoDB
{
    // ***********************************************************************
    // * 関数名     ：フォトアルバム情報内容ＤＢ登録処理
    // * 機能概要   ：フォトアルバム情報をＤＢに登録する
    // * 返り値     ：送信成功:true、送信失敗:false
    // ***********************************************************************
    function InsertAlbumData(   $strAlbumName       ,   // フォトアルバム名
                                $strAlbumComment    ,   // フォトアルバム説明
                                $strDelFlag         ,   // 削除フラグ
                                $arrayUserGroup     ,   // 選択されたユーザグループ
                                $strAlbumMail = ""  )   // フォトアルバム投稿用メールアドレス
    {
        $strErrorMessage = "";

        // ＤＢ接続
        $db = new WrapDB();
        $db->ConnectDB();

        //トランザクション開始
        $db->AutoCommitOffDB();

        // INSERTする
        $strSQL =   " INSERT INTO photo_album    " .
                    " (                          " .
                    "   mail_address            ," .
                    "   album_name              ," .
                    "   album_comment           ," .
                    "   create_date             ," .
                    "   del_flag                 " .
                    " )                          " .
                    " VALUES                     " .
                    " (                          " .
                    "    " . SQLAid::escapeStr( $strAlbumMail       ) . ", " .
                    "    " . SQLAid::escapeStr( $strAlbumName       ) . ", " .
                    "    " . SQLAid::escapeStr( $strAlbumComment    ) . ", " .
                    "    sysdate()                                       , " .
                    "    " . SQLAid::escapeNum( $strDelFlag         ) .
                    ")                           " ;

        // SQL実行
        $objSQL = $db->QueryDB( $strSQL );

        // 結果成功したらCOMMIT、失敗ならROLLBACKしてエラーメッセージ設定
        if( DB_OK == $objSQL )
        {
            //成功
            $db->TransactDB( db_strDB_COMMIT );

            // ****************************
            // グループ情報の挿入
            // ****************************
            if( 0 < count( $arrayUserGroup ) )
            {
                // 今登録したアルバムのID取得
                $strSelectSQL = " SELECT MAX( album_id ) as album_id FROM photo_album ";
                $objInfo = $db->QueryDB( $strSelectSQL );
                $arrayResult = $db->FetchArrayDB( $objInfo );
                $intAlbumID = intval( $arrayResult["album_id"] );

                // 選択されたユーザ数分、VALUE句を作成する
                $strQueryValue = "";
                for( $intCount = 0; $intCount < count( $arrayUserGroup ); $intCount++ )
                {
                    if( 0 < strlen( $strQueryValue ) )
                    {
                        $strQueryValue = $strQueryValue . ", ";
                    }

                    $strQueryValue =    $strQueryValue .
                                        " ( " .
                                        SQLAid::escapeNum( $intAlbumID ) . ", " .
                                        SQLAid::escapeNum( intval( $arrayUserGroup[$intCount] ) ) . ", " .
                                        " 1 " .
                                        " ) " ;
                }

                // INSERTする
                $strSQL =   " INSERT INTO photo_album_user  " .
                            " (                             " .
                            "   album_id                   ," .
                            "   user_or_group_id           ," .
                            "   flag                        " .
                            " )                             " .
                            " VALUES                        " .
                            $strQueryValue;

                // SQL実行
                $objGroup = $db->QueryDB( $strSQL );

                // 失敗したらROLLBACKしてエラーリターン
                if( DB_OK != $objGroup )
                {
                    $strErrorMessage = "ユーザグループ情報登録エラー";
                }
            }
        }
        else
        {
            //失敗
            $strErrorMessage ="フォトアルバム情報登録エラー";
        }

        // 結果成功したらCOMMIT、失敗ならROLLBACKしてエラーメッセージ設定
        if( 0 == strlen( $strErrorMessage ) )
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
            $strErrorMessage = "フォトアルバム情報登録エラー";
        }

        return $strErrorMessage;
    }

    // ***********************************************************************
    // * 関数名     ：フォトアルバム情報内容ＤＢ更新処理
    // * 機能概要   ：フォトアルバム情報内容をＤＢ更新する
    // * 返り値     ：成功:true、失敗:false
    // ***********************************************************************
    function UpdateAlbumData(   $strAlbumID         ,   // フォトアルバムID
                                $strAlbumName       ,   // フォトアルバム名
                                $strAlbumComment    ,   // フォトアルバム説明
                                $strDelFlag         ,   // 削除フラグ
                                $arrayUserGroup     ,   // 選択されたユーザグループ
                                $strAlbumMail = ""  )   // フォトアルバム投稿用メールアドレス
    {
        $strErrorMessage = "";

        // ＤＢ接続
        $db = new WrapDB();
        $db->ConnectDB();

        //トランザクション開始
        $db->AutoCommitOffDB();

        // UPDATE文
        $strSQL =   " UPDATE                    " .
                    "   photo_album             " .
                    " SET                       " .
                    "   mail_address        =   " . SQLAid::escapeStr( $strAlbumMail            ) . ", " .
                    "   album_name          =   " . SQLAid::escapeStr( $strAlbumName            ) . ", " .
                    "   album_comment       =   " . SQLAid::escapeStr( $strAlbumComment         ) . ", " .
                    "   del_flag            =   " . SQLAid::escapeNum( intval( $strDelFlag )    ) . 
                    " WHERE                     " .
                    "   album_id            =   " . SQLAid::escapeNum( intval( $strAlbumID )    ) ;

        // SQL実行
        $objSQL = $db->QueryDB( $strSQL );

        // 結果成功したらCOMMIT、失敗ならROLLBACKしてエラーメッセージ設定
        if( DB_OK == $objSQL )
        {
            //成功
            $db->TransactDB( db_strDB_COMMIT );

            // ****************************
            // グループ情報の挿入
            // ****************************
            if( 0 < count( $arrayUserGroup ) )
            {
                // グループを一端すべて削除する
                // DELETE文
                $strSQL =   " DELETE FROM               " .
                            "   photo_album_user        " .
                            " WHERE                     " .
                            "   album_id            =   " . SQLAid::escapeNum( intval( $strAlbumID ) ) ;

                // SQL実行
                $objGroupDel = $db->QueryDB( $strSQL );

                // 結果成功したらCOMMIT、失敗ならROLLBACKしてエラーメッセージ設定
                if( DB_OK == $objGroupDel )
                {
                    // 選択されたユーザ数分、VALUE句を作成する
                    $strQueryValue = "";
                    for( $intCount = 0; $intCount < count( $arrayUserGroup ); $intCount++ )
                    {
                        if( 0 < strlen( $strQueryValue ) )
                        {
                            $strQueryValue = $strQueryValue . ", ";
                        }

                        $strQueryValue =    $strQueryValue .
                                            " ( " .
                                            SQLAid::escapeNum( intval( $strAlbumID ) ) . ", " .
                                            SQLAid::escapeNum( intval( $arrayUserGroup[$intCount] ) ) . ", " .
                                            " 1 " .
                                            " ) " ;
                    }

                    // INSERTする
                    $strSQL =   " INSERT INTO photo_album_user  " .
                                " (                             " .
                                "   album_id                   ," .
                                "   user_or_group_id           ," .
                                "   flag                        " .
                                " )                             " .
                                " VALUES                        " .
                                $strQueryValue;

                    // SQL実行
                    $objGroup = $db->QueryDB( $strSQL );

                    // 失敗したらROLLBACKしてエラーリターン
                    if( DB_OK != $objGroup )
                    {
                        $strErrorMessage = "ユーザグループ情報登録エラー";
                    }
                }
            }
        }
        else
        {
            //失敗
            $strErrorMessage = "フォトアルバム情報更新エラー";
        }

        // 結果成功したらCOMMIT、失敗ならROLLBACKしてエラーメッセージ設定
        if( 0 == strlen( $strErrorMessage ) )
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
            $strErrorMessage = "フォトアルバム情報更新エラー";
        }

        return $strErrorMessage;
    }

    // ***********************************************************************
    // * 関数名     ：フォトアルバム情報取得処理
    // * 機能概要   ：フォトアルバム情報をすべて取得する
    // * 返り値     ：成功:SELECT結果、失敗:null
    // ***********************************************************************
    function GetAlbumAll(   &$intRows           ,   // 取得できたレコード数
                            $intOpenFlag = 0    )   // 全件／公開のみフラグ(0:全件、1:公開のみ)
    {
        // ＤＢ接続
        $db = new WrapDB();
        $db->ConnectDB();

        // SELECTする
        $strSQL =   " SELECT                                         " .
                    "   photo.album_id          as album_id         ," .
                    "   photo.album_name        as album_name       ," .
                    "   photo.album_comment     as album_comment    ," .
                    "   photo.create_date       as create_date      ," .
                    "   count(image.image_name) as image_count      ," .
                    "   photo.del_flag          as del_flag          " .
                    " FROM                                      " .
                    "   photo_album                 photo       " .
                    "   LEFT JOIN information_image image       " .
                    "       ON photo.album_id = image.album_id  " ;

        // 公開のみの場合
        if( 1 == $intOpenFlag )
        {
            $strSQL =   $strSQL .
                        " WHERE photo.del_flag <> 2 ";
        }

        $strSQL =   $strSQL .
                    " GROUP BY photo.album_id                   " .
                    " ORDER BY                                  " .
                    "   photo.album_id DESC                     " ;

        // SQL実行
        $objSQL = $db->QueryDB( $strSQL );

        // 実行結果行数を取得
        $intRows = $db->GetNumRowsDB( $objSQL );

        // 取得行を配列化
        $arrayAlbum = array();
        for( $intCount = 0; $intCount < $intRows; $intCount++ )
        {
            $arrayResult = $db->FetchArrayDB( $objSQL );

            // 取得したデータを配列に格納
            $arrayAlbum[] = array(  'album_id'          => $arrayResult["album_id"]         ,
                                    'album_name'        => $arrayResult["album_name"]       ,
                                    'album_comment'     => $arrayResult["album_comment"]    ,
                                    'create_date'       => $arrayResult["create_date"]      ,
                                    'image_count'       => $arrayResult["image_count"]      ,
                                    'del_flag'          => $arrayResult["del_flag"]         );
        }

        unset( $objSQL );

        return $arrayAlbum;
    }

    // ***********************************************************************
    // * 関数名     ：フォトアルバム情報取得処理(１件のみ)
    // * 返り値     ：成功:SELECT結果、失敗:null
    // ***********************************************************************
    function GetAlbumOne(   $intAlbumID      )
    {
        // ＤＢ接続
        $db = new WrapDB();
        $db->ConnectDB();

        // SELECTする
        $strSQL =   " SELECT                 " .
                    "   album_id            ," .
                    "   mail_address        ," .
                    "   album_name          ," .
                    "   album_comment       ," .
                    "   create_date         ," .
                    "   del_flag             " .
                    " FROM                   " .
                    "   photo_album          " .
                    " WHERE 0=0              " .
                    " AND   album_id   =     " . SQLAid::escapeNum( $intAlbumID );
        // SQL実行
        $objSQL = $db->QueryDB( $strSQL );

        // 実行結果行数を取得
        $intRows = $db->GetNumRowsDB( $objSQL );

        // 取得したデータを配列に格納
        if( 0 < $intRows )
        {
            // 取得行を配列化
            $arrayResult = $db->FetchArrayDB( $objSQL );

            $arrayAlbum = array(    'album_id'          => $arrayResult["album_id"]         ,
                                    'mail_address'      => $arrayResult["mail_address"]     ,
                                    'album_name'        => $arrayResult["album_name"]       ,
                                    'album_comment'     => $arrayResult["album_comment"]    ,
                                    'create_date'       => $arrayResult["create_date"]      ,
                                    'del_flag'          => $arrayResult["del_flag"]         );
        }
        else
        {
            $arrayAlbum = null;
        }

        unset( $objSQL );

        return $arrayAlbum;
    }

    // ***********************************************************************
    // * 関数名     ：フォトアルバム情報取得処理(ユーザグループ取得)
    // * 返り値     ：成功:SELECT結果、失敗:null
    // ***********************************************************************
    function GetAlbumOneAndGroup(   $intAlbumID ,
                                    &$intRows   )
    {
        // ＤＢ接続
        $db = new WrapDB();
        $db->ConnectDB();

        // SELECTする
        $strSQL =   " SELECT                 " .
                    "   album.album_id          as  album_id            ," .
                    "   album.mail_address      as  mail_address        ," .
                    "   album.album_name        as  album_name          ," .
                    "   album.album_comment     as  album_comment       ," .
                    "   album.create_date       as  create_date         ," .
                    "   album.del_flag          as  del_flag            ," .
                    "   u_group.group_id        as  group_id            ," .
                    "   u_group.group_name      as  group_name          ," .
                    "   u_group.group_note      as  group_note          ," .
                    "   u_group.create_date     as  group_create_date    " .
                    " FROM                   " .
                    "   photo_album  album   " .
                    "   LEFT  JOIN photo_album_user  user               " .
                    "       ON  user.album_id = album.album_id          " .
                    "   LEFT  JOIN user_group  u_group                  " .
                    "       ON  user.user_or_group_id = u_group.group_id" .
                    "       AND u_group.del_flag = 0                    " .
                    " WHERE 0=0              " .
                    " AND   album.album_id = " . SQLAid::escapeNum( $intAlbumID );
        // SQL実行
        $objSQL = $db->QueryDB( $strSQL );

        // 実行結果行数を取得
        $intRows = $db->GetNumRowsDB( $objSQL );

        // 取得したデータを配列に格納
        $arrayAlbum = array();
        for( $intCount = 0; $intCount < $intRows; $intCount++ )
        {
            // 取得行を配列化
            $arrayResult = $db->FetchArrayDB( $objSQL );

            $arrayAlbum[] = array(  'album_id'          => $arrayResult["album_id"]         ,
                                    'mail_address'      => $arrayResult["mail_address"]     ,
                                    'album_name'        => $arrayResult["album_name"]       ,
                                    'album_comment'     => $arrayResult["album_comment"]    ,
                                    'create_date'       => $arrayResult["create_date"]      ,
                                    'del_flag'          => $arrayResult["del_flag"]         ,
                                    'group_id'          => $arrayResult["group_id"]         ,
                                    'group_name'        => $arrayResult["group_name"]       ,
                                    'group_note'        => $arrayResult["group_note"]       ,
                                    'group_create_date' => $arrayResult["group_create_date"]);
        }

        unset( $objSQL );

        return $arrayAlbum;
    }

    // ***********************************************************************
    // * 関数名     ：フォトアルバム情報取得処理（主にトップページ用）
    // * 機能概要   ：指定されたレコード数取得する
    // * 返り値     ：成功:SELECT結果、失敗:null
    // ***********************************************************************
    function GetPhotoHome(  $intStartRecordNum  ,   // 取得開始レコード
                            $intGetRecordCount  ,   // 取得するレコード数
                            &$intRows           ,   // 取得できたレコード数
                            $intFlag = 0        )   // 0:Limitあり、1:Limitなし
    {
        // セッションIDからユーザIDを求める
        $arrayUser =    UserDB::GetLoginUserOfSession(  $_GET["s_id"]   ,
                                                        $intRowsUser    );

        // ＤＢ接続
        $db = new WrapDB();
        $db->ConnectDB();

        // SELECTする
        $strSQL =   " ( " .
                    " SELECT                                                 " .
                    "   album.album_id              as  album_id            ," .
                    "   album.album_name            as  album_name          ," .
                    "   album.album_comment         as  album_comment       ," .
                    "   album.create_date           as  create_date         ," .
                    "   album.del_flag              as  del_flag            ," .
                    "   IFNULL( MAX( image.create_date ), album.create_date )   as  new_image_date    ," .
                    "   COUNT( image.create_date )  as  image_count          " .
                    " FROM                      " .
                    "   photo_album   album     " .
                    "   LEFT  JOIN  information_image   image           " .
                    "       ON  album.album_id = image.album_id         " .
                    " WHERE 0=0                 " .
                    " AND   album.del_flag  = 0 " .     // 全体公開
                    " GROUP BY album.album_id   " .
                    " ) " .
                    " UNION " .
                    " ( " .
                    " SELECT                                                 " .      // 公開レベル設定されている場合の検索結果をUNIONする
                    "   album.album_id              as  album_id            ," .
                    "   album.album_name            as  album_name          ," .
                    "   album.album_comment         as  album_comment       ," .
                    "   album.create_date           as  create_date         ," .
                    "   album.del_flag              as  del_flag            ," .
                    "   IFNULL( MAX( image.create_date ), album.create_date )   as  new_image_date    ," .
                    "   COUNT( image.create_date )  as  image_count          " .
                    " FROM                      " .
                    "   photo_album   album     " .
                    "   LEFT  JOIN  information_image   image   " .
                    "       ON  album.album_id = image.album_id " .
                    "   INNER JOIN photo_album_user user        " .
                    "       ON album.album_id = user.album_id   " .
                    "   INNER JOIN group_users u_group          " .
                    "       ON  user.user_or_group_id = u_group.group_id " .
                    "       AND u_group.user_id = " . SQLAid::escapeNum( intval( $arrayUser["user_id"] ) ) .
                    " WHERE 0=0                 " .
                    " AND   album.del_flag  = 1 " .     // 公開レベル設定
                    " GROUP BY album.album_id   " .
                    " ) " .
                    " ORDER BY                  " .
                    "   new_image_date DESC     " ;

                    // LIMITあり
                    if( 0 == $intFlag )
                    {
                        $strSQL =   $strSQL .
                                    " LIMIT " . SQLAid::escapeNum( $intStartRecordNum ) . " , " . SQLAid::escapeNum( $intGetRecordCount );
                    }

        // SQL実行
        $objSQL = $db->QueryDB( $strSQL );

        // 実行結果行数を取得
        $intRows = $db->GetNumRowsDB( $objSQL );

        // 取得行を配列化
        $arrayAlbum = array();
        for( $intCount = 0; $intCount < $intRows; $intCount++ )
        {
            $arrayResult = $db->FetchArrayDB( $objSQL );

            // 取得したデータを配列に格納
            $arrayAlbum[] = array(  'album_id'          => $arrayResult["album_id"]         ,
                                    'album_name'        => $arrayResult["album_name"]       ,
                                    'album_comment'     => $arrayResult["album_comment"]    ,
                                    'create_date'       => $arrayResult["create_date"]      ,
                                    'del_flag'          => $arrayResult["del_flag"]         ,
                                    'new_image_date'    => $arrayResult["new_image_date"]   ,
                                    'image_count'       => $arrayResult["image_count"]      );
        }

        return $arrayAlbum;
    }

    // ***********************************************************************
    // * 関数名     ：フォトアルバム画像情報取得処理
    // * 機能概要   ：指定されたIDに対するフォトアルバム画像情報をすべて取得する
    // * 返り値     ：成功:SELECT結果、失敗:null
    // ***********************************************************************
    function GetAlbumPhotoAll(  $intAlbumID         ,   // アルバムID
                                &$intRows           )   // 取得できたレコード数
    {
        // ＤＢ接続
        $db = new WrapDB();
        $db->ConnectDB();

        // SELECTする
        $strSQL =   " SELECT                    " .
                    "   image_name             ," .
                    "   image_name_sumbnail    ," .
                    "   use_information_id     ," .
                    "   title                  ," .
                    "   comment                ," .
                    "   create_date            ," .
                    "   del_flag                " .
                    " FROM                      " .
                    "   information_image       " .
                    " WHERE album_id =          " . SQLAid::escapeNum( $intAlbumID ) .
                    " ORDER BY                                  " .
                    "   create_date DESC                        " ;
        // SQL実行
        $objSQL = $db->QueryDB( $strSQL );

        // 実行結果行数を取得
        $intRows = $db->GetNumRowsDB( $objSQL );

        // 取得行を配列化
        $arrayAlbum = array();
        for( $intCount = 0; $intCount < $intRows; $intCount++ )
        {
            $arrayResult = $db->FetchArrayDB( $objSQL );

            // 取得したデータを配列に格納
            $arrayAlbum[] = array(  'image_name'            => $arrayResult["image_name"]           ,
                                    'image_name_sumbnail'   => $arrayResult["image_name_sumbnail"]  ,
                                    'use_information_id'    => $arrayResult["use_information_id"]   ,
                                    'title'                 => $arrayResult["title"]                ,
                                    'comment'               => $arrayResult["comment"]              ,
                                    'create_date'           => $arrayResult["create_date"]          ,
                                    'del_flag'              => $arrayResult["del_flag"]             );
        }

        unset( $objSQL );

        return $arrayAlbum;
    }

    // ***********************************************************************
    // * 関数名     ：フォトアルバム画像情報ＤＢ登録処理
    // * 機能概要   ：フォトアルバム画像情報をＤＢに登録する
    // * 返り値     ：送信成功:true、送信失敗:false
    // ***********************************************************************
    function InsertPhotoData(   $intAlbumID             ,
                                $strImageName_Org       ,   // オリジナルサイズファイル名
                                $strImageName_Sumb      ,   // サムネイルファイル名
                                $strImageName_Sumb_SS   ,   // サムネイルファイル名(最小サイズ)
                                $strImageTitle          ,   // 画像タイトル
                                $strImageComment        ,   // 画像説明
                                $strDelFlag             ,   // 削除フラグ
                                $strLoginID = ""        )   // ログインID（メールでの更新の場合は必須）
    {
        $strErrorMessage = "";

        // タイトル入力の無い場合は画像物理名
        if( 0 == strlen( $strImageTitle ) )
        {
            $strImageTitle = $strImageName_Org;
        }

        if( 0 == strlen( $strLoginID ) )
        {
            // ログインユーザ名取得
            $strLoginID = $_COOKIE[ md5( COOKIE_VIX_LOGIN_ID ) ];
        }

        // ＤＢ接続
        $db = new WrapDB();
        $db->ConnectDB();

        //トランザクション開始
        $db->AutoCommitOffDB();

        // INSERTする
        $strSQL =   " INSERT INTO information_image  " .
                    " (                              " .
                    "   album_id                    ," .
                    "   image_name                  ," .
                    "   image_name_sumbnail         ," .
                    "   image_name_sumbnail_ss      ," .
                    "   title                       ," .
                    "   comment                     ," .
                    "   create_date                 ," .
                    "   create_user                 ," .
                    "   del_flag                     " .
                    " )                              " .
                    " VALUES                         " .
                    " (                              " .
                    "    " . SQLAid::escapeStr( $intAlbumID             ) . ", " .
                    "    " . SQLAid::escapeStr( $strImageName_Org       ) . ", " .
                    "    " . SQLAid::escapeStr( $strImageName_Sumb      ) . ", " .
                    "    " . SQLAid::escapeStr( $strImageName_Sumb_SS   ) . ", " .
                    "    " . SQLAid::escapeStr( $strImageTitle          ) . ", " .
                    "    " . SQLAid::escapeStr( $strImageComment        ) . ", " .
                    "    sysdate()                                           , " .
                    "    " . SQLAid::escapeStr( $strLoginID             ) . ", " .
                    "    " . SQLAid::escapeNum( $strDelFlag             ) .
                    " )                           " ;

        // SQL実行
        $objSQL = $db->QueryDB( $strSQL );

        // 結果成功したらCOMMIT、失敗ならROLLBACKしてエラーメッセージ設定
        if( DB_OK == $objSQL )
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
            $strErrorMessage = "フォトアルバム画像情報登録エラー";
        }

        return $strErrorMessage;
    }

    // ***********************************************************************
    // * 関数名     ：フォトアルバム画像情報ＤＢ更新処理
    // * 機能概要   ：フォトアルバム画像情報をＤＢ更新する
    // * 返り値     ：成功:true、失敗:false
    // ***********************************************************************
    function UpdatePhotoData(   $strAlbumID         ,   // フォトアルバムID
                                $strImageName       ,   // オリジナルイメージファイル名
                                $strImageSumbName   ,   // サムネイルイメージファイル名
                                $strImageTitle      ,   // 画像タイトル
                                $strImageComment    ,   // 画像説明
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
                    "   information_image       " .
                    " SET                       " .
                    "   image_name          =   " . SQLAid::escapeStr( $strImageName            ) . ", " .
                    "   image_name_sumbnail =   " . SQLAid::escapeStr( $strImageSumbName        ) . ", " .
                    "   title               =   " . SQLAid::escapeStr( $strImageTitle           ) . ", " .
                    "   comment             =   " . SQLAid::escapeStr( $strImageComment         ) . ", " .
                    "   create_date         = sysdate() "                                         . ", " .
                    "   del_flag            =   " . SQLAid::escapeNum( intval( $strDelFlag )    ) . 
                    " WHERE 0=0                 " .
                    " AND  album_id         =   " . SQLAid::escapeNum( intval( $strAlbumID )    ) .
                    " AND  image_name       =   " . SQLAid::escapeStr( $strImageName            ) ;
        // SQL実行
        $objSQL = $db->QueryDB( $strSQL );

        // 結果成功したらCOMMIT、失敗ならROLLBACKしてエラーメッセージ設定
        if( DB_OK == $objSQL )
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
            $strErrorMessage = "フォトアルバム画像情報更新エラー";
        }

        return $strErrorMessage;
    }

    // ***********************************************************************
    // * 関数名     ：フォトアルバム画像情報ＤＢ削除処理
    // * 機能概要   ：フォトアルバム画像情報をＤＢ削除する
    // * 返り値     ：成功:true、失敗:false
    // ***********************************************************************
    function DeletePhotoData(   $strAlbumID         ,   // フォトアルバムID
                                $strImageName       )   // オリジナルイメージファイル名
    {
        $strErrorMessage = "";

        // ＤＢ接続
        $db = new WrapDB();
        $db->ConnectDB();

        //トランザクション開始
        $db->AutoCommitOffDB();

        // UPDATE文
        $strSQL =   " DELETE FROM               " .
                    "   information_image       " .
                    " WHERE 0=0                 " .
                    " AND  album_id         =   " . SQLAid::escapeNum( intval( $strAlbumID )    ) .
                    " AND  image_name       =   " . SQLAid::escapeStr( $strImageName            ) ;
        // SQL実行
        $objSQL = $db->QueryDB( $strSQL );

        // 結果成功したらCOMMIT、失敗ならROLLBACKしてエラーメッセージ設定
        if( DB_OK == $objSQL )
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
            $strErrorMessage = "フォトアルバム画像情報削除エラー";
        }

        return $strErrorMessage;
    }

    // ***********************************************************************
    // * 関数名     ：フォトアルバム画像情報取得処理(１件のみ)
    // * 返り値     ：成功:SELECT結果、失敗:null
    // ***********************************************************************
    function GetAlbumImageOne(  $intAlbumID     ,
                                $strImageName   )
    {
        // ＤＢ接続
        $db = new WrapDB();
        $db->ConnectDB();

        // SELECTする
        $strSQL =   " SELECT                 " .
                    "   image_name          ," .
                    "   image_name_sumbnail ," .
                    "   use_information_id  ," .
                    "   title               ," .
                    "   comment             ," .
                    "   create_date         ," .
                    "   create_user         ," .
                    "   del_flag             " .
                    " FROM                   " .
                    "   information_image    " .
                    " WHERE 0=0              " .
                    " AND   album_id   =     " . SQLAid::escapeNum( $intAlbumID ) .
                    " AND   image_name =     " . SQLAid::escapeSTr( $strImageName ) ;

        // SQL実行
        $objSQL = $db->QueryDB( $strSQL );

        // 実行結果行数を取得
        $intRows = $db->GetNumRowsDB( $objSQL );

        // 取得したデータを配列に格納
        if( 0 < $intRows )
        {
            // 取得行を配列化
            $arrayResult = $db->FetchArrayDB( $objSQL );

            $arrayAlbum = array(    'image_name'            => $arrayResult["image_name"]           ,
                                    'image_name_sumbnail'   => $arrayResult["image_name_sumbnail"]  ,
                                    'use_information_id'    => $arrayResult["use_information_id"]   ,
                                    'title'                 => $arrayResult["title"]                ,
                                    'comment'               => $arrayResult["comment"]              ,
                                    'create_date'           => $arrayResult["create_date"]          ,
                                    'create_user'           => $arrayResult["create_user"]          ,
                                    'del_flag'              => $arrayResult["del_flag"]             );
        }
        else
        {
            $arrayAlbum = null;
        }

        unset( $objSQL );

        return $arrayAlbum;
    }

    // ***********************************************************************
    // * 関数名     ：画像名（サムネイル）取得処理(１件のみ)
    // * 返り値     ：成功:SELECT結果、失敗:null
    // ***********************************************************************
    function GetImageSumbnail(  $strImageName   )
    {
        // ＤＢ接続
        $db = new WrapDB();
        $db->ConnectDB();

        // SELECTする
        $strSQL =   " SELECT                    " .
                    "   image_name_sumbnail    ," .
                    "   image_name_sumbnail_ss  " .
                    " FROM                      " .
                    "   information_image       " .
                    " WHERE 0=0                 " .
                    " AND   image_name =        " . SQLAid::escapeSTr( $strImageName ) ;

        // SQL実行
        $objSQL = $db->QueryDB( $strSQL );

        // 実行結果行数を取得
        $intRows = $db->GetNumRowsDB( $objSQL );

        // 取得したデータを配列に格納
        if( 0 < $intRows )
        {
            // 取得行を配列化
            $arrayResult = $db->FetchArrayDB( $objSQL );

            $arrayImageNameSumb = array(    'sumb'      =>  $arrayResult["image_name_sumbnail"]     ,
                                            'sumb_ss'   =>  $arrayResult["image_name_sumbnail_ss"]  );
        }
        else
        {
            $arrayImageNameSumb = null;
        }

        unset( $objSQL );

        return $arrayImageNameSumb;
    }

    // ***********************************************************************
    // * 関数名     ：フォトアルバム情報取得処理（主にトップページ用）
    // * 機能概要   ：指定されたレコード数取得する
    // * 返り値     ：成功:SELECT結果、失敗:null
    // ***********************************************************************
    function GetAlbumAndImage(  $intAlbumID         ,   // フォトアルバムID
                                $intStartRecordNum  ,   // 取得開始レコード
                                $intGetRecordCount  ,   // 取得するレコード数
                                &$intRows           ,   // 取得できたレコード数
                                $intFlag = 0        )   // 0:Limitあり、1:Limitなし
    {
        // ＤＢ接続
        $db = new WrapDB();
        $db->ConnectDB();

        // SELECTする
        $strSQL =   " SELECT                                                         " .
                    "   album.album_id                  as  album_id                ," .
                    "   album.mail_address              as  mail_address            ," .
                    "   album.album_name                as  album_name              ," .
                    "   album.album_comment             as  album_comment           ," .
                    "   COUNT( image.create_date )      as  image_count             ," .
                    "   image.image_name                as  image_name              ," .
                    "   image.image_name_sumbnail       as  image_name_sumbnail     ," .
                    "   image.image_name_sumbnail_ss    as  image_name_sumbnail_ss  ," .
                    "   image.title                     as  image_title             ," .
                    "   image.comment                   as  image_comment            " .
                    " FROM                      " .
                    "   photo_album     album   " .
                    "   LEFT  JOIN  information_image   image   " .
                    "       ON album.album_id = image.album_id  " .
                    " WHERE album.album_id =                    " . SQLAid::escapeNum( $intAlbumID ) .
                    " GROUP BY image.image_name                 " .
                    " ORDER BY                                  " .
                    "   image.create_date DESC                  " ;

                    // LIMITあり
                    if( 0 == $intFlag )
                    {
                        $strSQL =   $strSQL .
                                    " LIMIT " . SQLAid::escapeNum( $intStartRecordNum ) . " , " . SQLAid::escapeNum( $intGetRecordCount );
                    }

        // SQL実行
        $objSQL = $db->QueryDB( $strSQL );

        // 実行結果行数を取得
        $intRows = $db->GetNumRowsDB( $objSQL );

        // 取得行を配列化
        $arrayAlbum = array();
        for( $intCount = 0; $intCount < $intRows; $intCount++ )
        {
            $arrayResult = $db->FetchArrayDB( $objSQL );

            // 取得したデータを配列に格納
            $arrayAlbum[] = array(  'album_id'                  => $arrayResult["album_id"]                 ,
                                    'mail_address'              => $arrayResult["mail_address"]             ,
                                    'album_name'                => $arrayResult["album_name"]               ,
                                    'album_comment'             => $arrayResult["album_comment"]            ,
                                    'image_count'               => $arrayResult["image_count"]              ,
                                    'image_name'                => $arrayResult["image_name"]               ,
                                    'image_name_sumbnail'       => $arrayResult["image_name_sumbnail"]      ,
                                    'image_name_sumbnail_ss'    => $arrayResult["image_name_sumbnail_ss"]   ,
                                    'image_title'               => $arrayResult["image_title"]              ,
                                    'image_comment'             => $arrayResult["image_comment"]            );
        }

        return $arrayAlbum;
    }

    // ***********************************************************************
    // * 関数名     ：フォトアルバム情報取得処理（マイページ用）
    // * 機能概要   ：指定されたレコード数取得する
    // * 返り値     ：成功:SELECT結果、失敗:null
    // ***********************************************************************
    function GetPhotoMypage($strUserID          ,
                            $intStartRecordNum  ,   // 取得開始レコード
                            $intGetRecordCount  ,   // 取得するレコード数
                            &$intRows           ,   // 取得できたレコード数
                            $intFlag = 0        )   // 0:Limitあり、1:Limitなし
    {
        // ＤＢ接続
        $db = new WrapDB();
        $db->ConnectDB();

        // SELECTする
        $strSQL =   " ( " .
                    " SELECT                                                 " .
                    "   album.album_id              as  album_id            ," .
                    "   album.album_name            as  album_name          ," .
                    "   album.album_comment         as  album_comment       ," .
                    "   album.create_date           as  create_date         ," .
                    "   album.del_flag              as  del_flag            ," .
                    "   IFNULL( MAX( image.create_date ), album.create_date )   as  new_image_date    ," .
                    "   COUNT( image.create_date )  as  image_count         ," .
                    "   receive.user_id             as  user_id              " .
                    " FROM                      " .
                    "   photo_album   album     " .
                    "   LEFT  JOIN  information_image   image           " .
                    "       ON  album.album_id = image.album_id         " .
                    "   LEFT  JOIN  photo_album_receive receive         " .
                    "       ON  album.album_id = receive.album_id       " .
                    "       AND receive.user_id = " . SQLAid::escapeNum( intval( $strUserID ) ) .
                    " WHERE 0=0                 " .
                    " AND   album.del_flag  = 0 " .     // 全体公開
                    " GROUP BY album.album_id   " .
                    " ) " .
                    " UNION " .
                    " ( " .
                    " SELECT                                                 " .      // 公開レベル設定されている場合の検索結果をUNIONする
                    "   album.album_id              as  album_id            ," .
                    "   album.album_name            as  album_name          ," .
                    "   album.album_comment         as  album_comment       ," .
                    "   album.create_date           as  create_date         ," .
                    "   album.del_flag              as  del_flag            ," .
                    "   IFNULL( MAX( image.create_date ), album.create_date )   as  new_image_date    ," .
                    "   COUNT( image.create_date )  as  image_count         ," .
                    "   receive.user_id             as  user_id              " .
                    " FROM                      " .
                    "   photo_album   album     " .
                    "   LEFT  JOIN  information_image   image   " .
                    "       ON  album.album_id = image.album_id " .
                    "   INNER JOIN photo_album_user user        " .
                    "       ON album.album_id = user.album_id   " .
                    "   INNER JOIN group_users u_group          " .
                    "       ON  user.user_or_group_id = u_group.group_id " .
                    "       AND u_group.user_id = " . SQLAid::escapeNum( intval( $strUserID ) ) .
                    "   LEFT  JOIN  photo_album_receive receive         " .
                    "       ON  album.album_id = receive.album_id       " .
                    "       AND receive.user_id = " . SQLAid::escapeNum( intval( $strUserID ) ) .
                    " WHERE 0=0                 " .
                    " AND   album.del_flag  = 1 " .     // 公開レベル設定
                    " GROUP BY album.album_id   " .
                    " ) " .
                    " ORDER BY                  " .
                    "   new_image_date DESC     " ;

                    // LIMITあり
                    if( 0 == $intFlag )
                    {
                        $strSQL =   $strSQL .
                                    " LIMIT " . SQLAid::escapeNum( $intStartRecordNum ) . " , " . SQLAid::escapeNum( $intGetRecordCount );
                    }

        // SQL実行
        $objSQL = $db->QueryDB( $strSQL );

        // 実行結果行数を取得
        $intRows = $db->GetNumRowsDB( $objSQL );

        // 取得行を配列化
        $arrayAlbum = array();
        for( $intCount = 0; $intCount < $intRows; $intCount++ )
        {
            $arrayResult = $db->FetchArrayDB( $objSQL );

            // 取得したデータを配列に格納
            $arrayAlbum[] = array(  'album_id'          => $arrayResult["album_id"]         ,
                                    'album_name'        => $arrayResult["album_name"]       ,
                                    'album_comment'     => $arrayResult["album_comment"]    ,
                                    'create_date'       => $arrayResult["create_date"]      ,
                                    'del_flag'          => $arrayResult["del_flag"]         ,
                                    'new_image_date'    => $arrayResult["new_image_date"]   ,
                                    'image_count'       => $arrayResult["image_count"]      ,
                                    'user_id'           => $arrayResult["user_id"]          );
        }

        return $arrayAlbum;
    }

    // ***********************************************************************
    // * 関数名     ：フォトアルバム閲覧許可ユーザチェック
    // * 返り値     ：許可ユーザ:true、許可していないユーザ:false
    // ***********************************************************************
    function CheckAuthAlbumUser(    $strSessionID   ,
                                    $strAlbumID     ,
                                    &$intRows       ,   // 取得できたレコード数
                                    $intDebugFlag = 0 )
    {
        $intRetCode = false;

        // ＤＢ接続
        $db = new WrapDB();
        $db->ConnectDB();

        // スレッド情報とユーザ一覧取得
        // SELECTする
        $strSQL =   " SELECT                                " .
                    "   album.del_flag      as  del_flag   ," .
                    "   ses.session_id      as  session_id  " .
                    " FROM                                                      " .
                    "               photo_album         album                   " .
                    "   LEFT  JOIN  photo_album_user    album_user              " .
                    "       ON  album_user.album_id = album.album_id            " .
                    "   LEFT  JOIN user_group  u_group                          " .
                    "       ON  album_user.user_or_group_id = u_group.group_id  " .
                    "       AND u_group.del_flag = 0                            " .
                    "   LEFT  JOIN  group_users     group_u                     " .
                    "       ON  u_group.group_id = group_u.group_id             " .
                    "   LEFT  JOIN  user     us                                 " .
                    "       ON  group_u.user_id = us.user_id                    " .
                    "       AND us.del_flag = 0                                 " .
                    "   LEFT  JOIN  session     ses                             " .
                    "       ON  us.login_id = ses.login_id                      " .
                    "       AND ses.session_id =    " . SQLAid::escapeStr( $strSessionID ) .
                    " WHERE 0=0                     " .
                    " AND   album.album_id =        " . SQLAid::escapeNum( intval( $strAlbumID ) ) .
                    " GROUP BY session_id           " ;

        // SQL実行
        $objSQL = $db->QueryDB( $strSQL );

        // 実行結果行数を取得
        $intRows = $db->GetNumRowsDB( $objSQL );

        // 取得行数ループ
        for( $intCount = 0; $intCount < $intRows; $intCount++ )
        {
            $arrayResult = $db->FetchArrayDB( $objSQL );

            if( 0 != $intDebugFlag )
            {
                var_dump($arrayResult);
            }

            // 全体公開なら無条件にOK
            if( 0 == strcmp( "0", $arrayResult["del_flag"] ) )
            {
                $intRetCode = true;
                break;
            }

            // 非公開なら無条件にNG
            if( 0 == strcmp( "2", $arrayResult["del_flag"] ) )
            {
                $intRetCode = false;
                break;
            }

            // セッションIDがあるかどうかチェックする。あればOK
            if( 0 == strcmp( $strSessionID, $arrayResult["session_id"] ) )
            {
                $intRetCode = true;
                break;
            }
        }

        return $intRetCode;
    }

}
