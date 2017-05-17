<?php
// 受信メールサーバーの設定
// POP3サーバー
$host = "localhost";

// ユーザーID
$user = "";     // スレッドごとにDBから取得する

// パスワード
$pass = "";     // メールアドレスの@までの文字とする

// 画像保存ﾃﾞｨﾚｸﾄﾘ（パーミッション777等に変更）
$tmpdir  = "../../img/photo_album_temp/";

// 最大添付量（バイト・1ファイルにつき）※超えるものは保存しない
$maxbyte = 4096000; // 約4MB

// 最大本文文字数（半角で
$maxtext = 1000;

// 対応MIMEサブタイプ（正規表現）Content-Type: image/jpegの後ろの部分。octet-streamは危険かも
$subtype = "gif|jpe?g|png|bmp|pmd|mld|mid|smd|smaf|mpeg|kjx|3gpp|octet-stream";

// 投稿非許可アドレス（ログに記録しない）
$deny_from = array('163.com','bigfoot.com','boss.com');

// 投稿非許可件名（ログに記録しない）
$deny_subj = array('未承諾','広告','ocument','equest','essage','elivery');

// 保存しないファイル(正規表現)
$viri = ".+\.exe$|.+\.zip$|.+\.pif$|.+\.scr$";

// 本文から削除する文字列
$word[] = "会員登録は無料  充実した出品アイテムなら MSN オークション";
$word[] = "http://auction.msn.co.jp/";
$word[] = "Do You Yahoo!?";
$word[] = "Yahoo! BB is Broadband by Yahoo!";
$word[] = "http://bb.yahoo.co.jp/";
$word[] = "友達と24時間ホットライン「MSN メッセンジャー」、今すぐダウンロード！";
$word[] = "http://messenger.msn.co.jp";

// 添付メールのみ記録する？Yes=1 No=0（本文のみはログに載せない）
$imgonly = 0;

// 件名がないときの題名
$nosubject = "タイトル無し";

// 次の秒数以内の同一送信者からの連続投稿禁止（0で連続可）
$wtime = 0;

/*-- サムネイル--*/
//これ以上の大きい画像はjpg,pngのサムネイル作成
$W = 240;
$H = 240;
//サムネイル保存ディレクトリ（パーミッション777等に変更）
$thumb_dir = "../../img/photo_album/";
