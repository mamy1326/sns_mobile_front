<?php
/* ================================================================================
 * ファイル名   ：manage_conf.php
 * タイトル     ：管理画面定義configファイル
 * 作成者       ：間宮 直樹
 * 作成日       ：2008/08/05
 * 内容         ：管理画面に関する各種値を定義する。
 * 更新履歴*******************************************************************
 * 【変更日】  【変更者】      【変更箇所】        【変更理由と変更内容】
 *  2008/08/05  間宮 直樹       全体                新規作成
 * ================================================================================*/

// 管理画面種別定義
define( 'HEAD_MANAGE_USER'          , 1 );      // ユーザ情報
define( 'HEAD_MANAGE_USER_GROUP'    , 2 );      // ユーザグループ
define( 'HEAD_MANAGE_INFO'          , 3 );      // 管理本部通信
define( 'HEAD_MANAGE_ALBUM'         , 4 );      // フォトアルバム
define( 'HEAD_MANAGE_MAIL'          , 5 );      // メール送信
define( 'HEAD_MANAGE_THREAD'        , 6 );      // ＢＢＳスレッド
define( 'HEAD_MANAGE_SECTION'       , 7 );      // 事業部
define( 'HEAD_MANAGE_EL'            , 8 );      // イーラーニング
define( 'HEAD_MANAGE_ACCESSLOG'     , 20 );     // アクセスログ

// マスタステータス
define( 'MASTER_STATUS_USER'        , 0  );     // ユーザ役職
define( 'MASTER_STATUS_USER_STATUS' , 1  );     // ユーザ状態
define( 'MASTER_STATUS_MANAGE'      , 2  );     // 管理権限
define( 'MASTER_STATUS_SHOW_INFO'   , 3  );     // 管理本部通信の公開状態
define( 'MASTER_STATUS_ACCESS_INFO' , 4  );     // 管理本部通信の閲覧状況
define( 'MASTER_STATUS_STANDARD'    , 5  );     // 一般的な有効状態フラグ名
define( 'MASTER_STATUS_MAIL_RESULT' , 6  );     // メール送信結果
define( 'MASTER_STATUS_MAIL_STATUS' , 7  );     // メール送信機能種別
define( 'MASTER_STATUS_SEX'         , 8  );     // 性別
define( 'MASTER_STATUS_STAFF_STATUS', 9  );     // スタッフ現況
define( 'MASTER_STATUS_EL_ANSWER'   , 10 );     // イーラーニング回答結果
define( 'MASTER_STATUS_DEFAULT'     , 11 );     // 公開・非公開
define( 'MASTER_STATUS_SECTION'     , 90 );     // 事業部

// メール送信時の種別
define( 'MAIL_SEND_STANDARD'        , 0  );     // 管理画面からの通常送信
define( 'MAIL_SEND_INFORMATION'     , 1  );     // 管理本部通信からの送信(正常時)
define( 'MAIL_SEND_INFO_NOT_SHOW'   , 2  );     // 管理本部通信からの送信(閲覧していない人向け)
define( 'MAIL_SEND_PHOTO_ALBUM'     , 3  );     // フォトアルバムからの送信
define( 'MAIL_SEND_BBS_THREAD'      , 4  );     // ＢＢＳスレッドからの送信
define( 'MAIL_SEND_EL_USER'         , 5  );     // EL対象者への送信

// タグ種別
define( 'TAG_OPTION'                , 0  );     // Optionタグ
define( 'TAG_RADIO'                 , 1  );     // RadioButtonタグ
define( 'TAG_CHECKBOX'              , 2  );     // Checkoxタグ

// アクセスログ種別
define( 'LOG_INFO_FLAG'             , 0  );     // 管理本部通信
define( 'LOG_ALBUM_FLAG'            , 1  );     // フォトアルバム
define( 'LOG_THREAD_FLAG'           , 2  );     // BBSスレッド

// COOKIE関連
define( 'COOKIE_DOMAIN_PATH'        , '/' );            // クッキードメインパス
define( 'COOKIE_LOGIN_ID'           , 'SNS_LOGIN_ID');  // ログイン認証OK時のログインIDのCOOKIE保存名

// ログイン定義
define( 'LOGIN_MANAGE'              , 0 );      // 管理画面へのログイン
define( 'LOGIN_FRONT'               , 1 );      // フロント画面へのログイン


// その他
define( 'PUBLIC_HTML'               , 'public_html' );  // ドキュメントルート
define( 'ALBUM_IMAGE_LINE_COUNT'    , 4 );              // フォトアルバム画像一覧の１行表示画像数
define( 'IMAGE_MOBILE_WIDTH_PX'     , 240 );            // モバイル用の画像の最大幅
define( 'IMAGE_MOBILE_HEIGHT_PX'    , 320 );            // モバイル用の画像の最大高さ
define( 'IMAGE_MOBILE_WIDTH_PX_SS'  , 100 );            // モバイル用の画像の最大幅(モバイル一覧用最小サイズ)
define( 'IMAGE_MOBILE_HEIGHT_PX_SS' , 100 );            // モバイル用の画像の最大高さ(モバイル一覧用最小サイズ)
define( 'DOCOMO_GUID'               , '?guid=ON' );     // docomoの端末ID取得用
define( 'DOCOMO_GUID_HIDDEN'        , '<input type="hidden" name="guid"        value="ON">' );     // docomoの端末ID取得用(hidden)
define( 'PARAM_MAIL_DIRECT_LOGIN'   , 'mail_direct_login' );    // 更新メールから直接アクセス時であることを示すパラメータ名
define( 'MAIL_FROM_STANDARD'        , 'info@mocotan.jp' );      // 管理部送信元メールアドレス（デフォルト）


//=== 携帯サイトのキャリア番号 ==========================================
define( 'CARRIER_PC'                , 0 );                                          //PC
define( 'CARRIER_IMODE'             , 1 );                                          //docomo
define( 'CARRIER_EZWEB'             , 2 );                                          //au
define( 'CARRIER_VODAFONE'          , 3 );                                          //Vodafone
define( 'CARRIER_UNKNOWN'           , 9 );                                          //不明
//=======================================================================

define( 'ATTR_HIRAGANA'             , '1' );                                        //Istyle定数
define( 'ATTR_KATAKANA'             , '2' );                                        //Istyle定数
define( 'ATTR_ALPHABET'             , '3' );                                        //Istyle定数
define( 'ATTR_NUMERIC'              , '4' );                                        //Istyle定数

// 文字サイズする場所の指定
define( 'FONT_SIZE_ALL'             , 1 );                                          // 全体のフォントサイズを指定
define( 'FONT_SIZE_SMALL'           , 2 );                                          // 全体より１サイズ小さくサイズを指定

define( 'TOP_LOGO_IMAGE_PATH'       , '/admin/m/img/logo.jpg' );        // トップロゴ画像パス
define( 'COLOR_PAGE_TITLE_BGCOLOR'  , '#DC143C' );                      // 各ページのタイトル背景色
define( 'INFO_IMAGE_TAG_START'      , "<img src='" );                   // 管理本部通信の本文に含まれる画像タグの始まり(本文に含まれる画像をリンクに変換する場合に使用する)

// パス関連
define( 'PATH_IMAGE_PHOTOALBUM'     , '/admin/img/photo_album' );       // フォトアルバム画像保存先ディレクトリパス
define( 'PATH_IMAGE_PHOTO_TEMP_COPY', 'img/photo_album_temp/' );        // フォトアルバム画像テンポラリファイル保存先ディレクトリパス(物理コピー用)
define( 'PATH_IMAGE_PHOTO_COPY'     , 'img/photo_album/' );             // フォトアルバム画像ディレクトリパス(物理コピー用)
define( 'PATH_IMAGE_PHOTO_TEMP'     , '/admin/img/photo_album_temp/' ); // フォトアルバム画像テンポラリファイル保存先ディレクトリパス(表示用)
define( 'PATH_IMAGE_BBS_SUMB'       , '/admin/m/bbs/mailbbs_gd/data/s' );   // BBS画像サムネイルディレクトリ
define( 'PATH_IMAGE_BBS'            , '/admin/m/bbs/mailbbs_gd/data' );     // BBS画像ディレクトリ

define( 'PATH_INFO_INDEX'           , '/admin/manage/info/index.php' );         // 【管理画面】管理本部通信一覧
define( 'PATH_ALBUM_INDEX'          , '/admin/manage/info/photo/index.php' );   // 【管理画面】フォトアルバム通信一覧
define( 'PATH_THREAD_INDEX'         , '/admin/manage/bbs/index.php' );          // 【管理画面】スレッド一覧

define( 'PATH_MOBILE_ALBUM_DETAIL'  , '/admin/m/photo/album_detail.php' );          // 【モバイル】フォトアルバム詳細
define( 'PATH_MOBILE_THREAD_DETAIL' , '/admin/m/bbs/mailbbs_gd/mailbbs_i.php' );    // 【モバイル】スレッド詳細

define( 'PATH_MANAGE_IMAGE'         , '/admin/manage/img/' );    // 【管理画面】画像ディレクトリ


?>
