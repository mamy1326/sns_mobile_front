<?php
/* ================================================================================
 * ファイル名   ：album_pop_db.php
 * タイトル     ：メールからのフォトアルバム画像投稿取り込み機能
 * 作成者       ：間宮 直樹
 * 作成日       ：2008/12/17
 * 内容         ：アルバムごとのメールアドレスに投稿されたメール・画像をＤＢ登録し、
 *                アルバムに画像を追加できるようにする。
 * 更新履歴*******************************************************************
 * 【変更日】  【変更者】      【変更箇所】        【変更理由と変更内容】
 * ================================================================================*/

$strPath = substr( $_SERVER['SCRIPT_FILENAME'], 0, strpos( $_SERVER['SCRIPT_FILENAME'], 'public_html' ) - 1 + strlen( "/" . 'public_html' . "/" ) );
$strPath = $strPath . "admin/";

// includeするライブラリ・コンフィグファイルのパスを定義する
define( 'LIB_DIR'   , $strPath . '_lib/');
define( 'CONFIG_DIR', $strPath . '_config/');

// 画像投稿用メール設定
require_once("config.php");

include_once( LIB_DIR    . 'DataConvert.php' );         // データ変換用関数群
include_once( LIB_DIR    . 'PhotoDB.php' );             // フォトアルバム情報取得・更新関数群
include_once( LIB_DIR    . 'ImageFunc.php' );           // 画像処理関数群
include_once( LIB_DIR    . 'UserDB.php' );              // ユーザ情報取得・更新関数群
include_once( LIB_DIR    . 'MailSend.php' );            // メール送信関数群
include_once( CONFIG_DIR . 'title_conf.php' );          // ページタイトル定義ファイル
include_once( CONFIG_DIR . 'manage_conf.php' );         // 管理画面基本情報定義ファイル

// フォトアルバムID取得
$strAlbumID     = $_GET["album_id"];
$strPageNo      = $_GET["pgno"];
$strImageFlag   = $_GET["image_id"];

// フォトアルバムのメールアドレス、アルバム名取得
$arrayAlbum = PhotoDB::GetAlbumOneAndGroup( intval( $strAlbumID ), $intAlbumRows );
$strAlbumName = $arrayAlbum[0]["album_name"];
$user = $arrayAlbum[0]["mail_address"];
$pass = substr( $arrayAlbum[0]["mail_address"], 0, strpos( $arrayAlbum[0]["mail_address"], '@' ) );
unset($arrayAlbum);

/*-----------------*/
//文字化け対策
if (function_exists("mb_internal_encoding"))
{
    mb_internal_encoding("utf8");
}

// サーバ接続（ソケットオープン）
$sock = fsockopen($host, 110, $err, $errno, 10) or die("ｻｰﾊﾞｰに接続できません");

$buf = fgets($sock, 512);
if(substr($buf, 0, 3) != '+OK'){
    die($buf);
}

// 専用メールアドレスに対し、ID・PW認証をかける(内部システムのためID, PW設定なし)
$buf = _sendcmd("USER $user");
$buf = _sendcmd("PASS $pass");
$data = _sendcmd("STAT");//STAT -件数とサイズ取得 +OK 8 1234

sscanf($data, '+OK %d %d', $num, $size);

// *********************
// 新規投稿が無い場合
// *********************
if ($num == "0")
{
    // 更新せずにBBS画面へ遷移
    $buf = _sendcmd("QUIT");
    fclose($sock);

    header("Location:./album_detail.php". DOCOMO_GUID . "&s_id=" . $_GET["s_id"] . "&album_id=" . $strAlbumID . "&result=1" . "&pgno=" . $strPageNo . "&image_id=" . $strImageFlag );
    exit;
}

// *********************
// 新規投稿がある場合
// *********************

// 新規投稿の件数分ループ
for( $i = 1; $i <= $num; $i++ )
{
    $line = _sendcmd("RETR $i");  //RETR n -n番目のメッセージ取得（ヘッダ含）

    // 順番に取得し、配列化していく
    while (!ereg("^\.\r\n",$line))//EOFの.まで読む
    {
        // オープンしたソケットからデータ取得
        $line = fgets($sock,512);
        $dat[$i].= $line;
    }
    $data = _sendcmd("DELE $i");//DELE n n番目のメッセージ削除
}

$buf = _sendcmd("QUIT");
fclose($sock);

$write2 = false;

for( $j = 1; $j <= $num; $j++ )
{
    $write = true;
    $subject = $from = $text = $atta = $part = $attach = $filename = "";

    // 新規投稿のヘッダと本文を分割
    list($head, $body) = mime_split($dat[$j]);

    // ヘッダから日付の抽出し、$dateregへ格納
    eregi( "\nDate:[ \t]*([^\r\n]+)", $head, $datereg );

    $now = strtotime($datereg[1]);  // 時刻形式へ変換

    // 取得できない場合は現在時刻
    if( $now == -1 )
    {
        $now = time();
    }

    // ヘッダから改行の削除
    $head = ereg_replace("\r\n? ", "", $head);

    // サブジェクトの抽出
    if( eregi("\nSubject:[ \t]*([^\r\n]+)", $head, $subreg ) )
    {
        $subject = $subreg[1];
        while (eregi("(.*)=\?iso-2022-jp\?B\?([^?]+)\?=(.*)",$subject,$regs))   //MIME Bﾃﾞｺｰﾄﾞ
        {
            $subject = $regs[1].base64_decode($regs[2]).$regs[3];
        }

        while( eregi("(.*)=\?iso-2022-jp\?Q\?([^?]+)\?=(.*)",$subject,$regs))   //MIME Qﾃﾞｺｰﾄﾞ
        {
            $subject = $regs[1].quoted_printable_decode($regs[2]).$regs[3];
        }

        $subject = htmlspecialchars(convert($subject));

        // 拒否件名
        foreach ($deny_subj as $dsubj)
        {
            if( stristr($subject, $dsubj) )
            {
                $write = false;
            }
        }
    }

    // 送信者アドレスの抽出
    if( eregi("\nFrom:[ \t]*([^\r\n]+)", $head, $freg ) )
    {
        $from = addr_search($freg[1]);
    }
    elseif( eregi("\nReply-To:[ \t]*([^\r\n]+)", $head, $freg ) )
    {
        $from = addr_search($freg[1]);
    }
    elseif( eregi("\nReturn-Path:[ \t]*([^\r\n]+)", $head, $freg ) )
    {
        $from = addr_search($freg[1]);
    }

    // 拒否アドレス
    foreach ($deny_from as $dfrom)
    {
        if (stristr($from, $dfrom))
        {
            $write = false;
        }
    }

    // マルチパートならばバウンダリに分割
    if (eregi("\nContent-type:.*multipart/",$head))
    {
        eregi('boundary="([^"]+)"', $head, $boureg);
        $body = str_replace($boureg[1], urlencode($boureg[1]), $body);
        $part = split("\r\n--".urlencode($boureg[1])."-?-?",$body);
        if (eregi('boundary="([^"]+)"', $body, $boureg2))   //multipart/altanative
        {
            $body = str_replace($boureg2[1], urlencode($boureg2[1]), $body);
            $body = eregi_replace("\r\n--".urlencode($boureg[1])."-?-?\r\n","",$body);
            $part = split("\r\n--".urlencode($boureg2[1])."-?-?",$body);
        }
    }
    else
    {
        $part[0] = $dat[$j];// 普通のテキストメール
    }

    // 本文処理
    foreach ($part as $multi)
    {
        // 本文を分割
        list($m_head, $m_body) = mime_split($multi);

        // 改行を削除
        $m_body = ereg_replace("\r\n\.\r\n$", "", $m_body);

        // 本文ではない要素の場合は飛ばす
        if (!eregi("\nContent-type: *([^;\n]+)", $m_head, $type) )
        {
            continue;
        }

        // 抽出した本文スラッシュで区切る
        list($main, $sub) = explode("/", $type[1]);

        // 本文をデコード
        if (strtolower($main) == "text")
        {
            if (eregi("\nContent-Transfer-Encoding:.*base64", $m_head))
            {
                $m_body = base64_decode($m_body);
            }

            if (eregi("\nContent-Transfer-Encoding:.*quoted-printable", $m_head))
            {
                $m_body = quoted_printable_decode($m_body);
            }

            $text = convert($m_body);

            if ($sub == "html")
            {
                $text = strip_tags($text);
            }

            // 電話番号削除
//            $text = eregi_replace("([[:digit:]]{11})|([[:digit:]\-]{13})", "", $text);

            // 下線削除
//            $text = eregi_replace("[_]{25,}", "", $text);

            // mac削除
            $text = ereg_replace("\nContent-type: multipart/appledouble;[[:space:]]boundary=(.*)","",$text);

            // 広告等削除
            if (is_array($word))
            {
                $text = str_replace($word, "", $text);
            }

            // 文字数オーバー
            if (strlen($text) > $maxtext)
            {
                $text = substr($text, 0, $maxtext)."...";
            }
        }

        // 添付ファイル名を抽出
        if (eregi("name=\"?([^\"\n]+)\"?",$m_head, $filereg))
        {
            $filename = ereg_replace("[\t\r\n]", "", $filereg[1]);
            while (eregi("(.*)=\?iso-2022-jp\?B\?([^\?]+)\?=(.*)",$filename,$regs))
            {
                $filename = $regs[1].base64_decode($regs[2]).$regs[3];
                $filename = convert($filename);
            }

            // 拡張子は小文字で登録する
            $ext = strtolower( substr($filename,strrpos($filename,".")+1,strlen($filename)-strrpos($filename,".")) );
        }

        // 添付データをデコードして保存
        if (eregi("\nContent-Transfer-Encoding:.*base64", $m_head) && eregi($subtype, $sub))
        {
            // 添付ファイルを展開する
            $tmp = base64_decode($m_body);
            if (!$ext)
            {
                $ext = $sub;
            }

            // 投稿用メールアドレスからログインIDを取得する
            $arrayUser = UserDB::GetUserOfBbsMail( $from, $intUserRows );
            if( 0 == $intUserRows )
            {
                $strCommnetLoginID = "guest";
            }
            else
            {
                $strCommnetLoginID = $arrayUser["login_id"];
            }
            unset($arrayUser);

            // システム日付（月、日、時、分）取得
            $strDateTime = date( 'YnjHi' );

            // ファイル名をフォトアルバムID＋年月日時分＋シーケンスで作成する
            $filename = sprintf( "%08d", intval( $strAlbumID) ) . "_" . $strDateTime . "_1." . $ext;
//            $filename = $now . "_" . $strCommnetLoginID . "." . $ext;

            if (strlen($tmp) < $maxbyte && !eregi($viri, $filename) && $write)
            {
                // オリジナルファイルを保存(メモリ上のファイルを書き込み)
                $fp = fopen( $strPath . PATH_IMAGE_PHOTO_TEMP_COPY . $filename, "w" );
                fputs( $fp, $tmp );
                fclose( $fp );
                $attach = $filename;

                // 本番領域に画像作成＆保存
                if ( preg_match("/\.jpe?g$|\.png$|\.gif$/i",$filename) )
                {
                    $intRetCode = true;

                    // **********************************
                    // サムネイル、最小サムネイル作成
                    // **********************************

                    // 物理ファイルパス（オリジナル）
                    $strFileNameFullPath = $strPath . PATH_IMAGE_PHOTO_TEMP_COPY . $filename;
                    $arrayFileAndExt = split( "\.", $filename );

                    // サムネイルを作成する
                    $intErrorCode = ImageFunc::CreateSumbnail(  2                       ,   // サムネイル種類（2:普通の大きさのサムネイル）
                                                                $strFileNameFullPath    ,   // [オリジナル]テンポラリファイル名(拡張子あり)物理パス指定
                                                                IMAGE_MOBILE_WIDTH_PX   ,   // 最大幅（ピクセル）
                                                                IMAGE_MOBILE_HEIGHT_PX  ,   // 最大高さ（ピクセル）
                                                                $strSumbName            ,   // (OUT)サムネイル画像名（テンポラリ、_s、または_ss）
                                                                $intSumbWidth           ,   // (OUT)実際のサムネイル幅
                                                                $intSumbHeight          );  // (OUT)実際のサムネイル高さ

                    $strUpFileSumbFullpath = $strPath . PATH_IMAGE_PHOTO_TEMP_COPY . $arrayFileAndExt[0] . $strSumbName . "." . $arrayFileAndExt[1];
                    $strSumbFileName       = $arrayFileAndExt[0] . $strSumbName . "." . $arrayFileAndExt[1];

                    // サムネイルを作成する(一覧表示用の最小サイズ：100×100)
                    $intErrorCode = ImageFunc::CreateSumbnail(  3                           ,   // サムネイル種類（3:最小のサムネイル）
                                                                $strFileNameFullPath        ,   // [オリジナル]テンポラリファイル名(拡張子あり)物理パス指定
                                                                IMAGE_MOBILE_WIDTH_PX_SS    ,   // 最大幅（ピクセル）
                                                                IMAGE_MOBILE_HEIGHT_PX_SS   ,   // 最大高さ（ピクセル）
                                                                $strSumbName_SS             ,   // (OUT)サムネイル画像名（テンポラリ、_s、または_ss）
                                                                $intSumbWidth_SS            ,   // (OUT)実際のサムネイル幅
                                                                $intSumbHeight_SS           );  // (OUT)実際のサムネイル高さ

                    $strUpFileSumbFullpath_SS = $strPath . PATH_IMAGE_PHOTO_TEMP_COPY . $arrayFileAndExt[0] . $strSumbName_SS . "." . $arrayFileAndExt[1];
                    $strSumpFileName_SS       = $arrayFileAndExt[0] . $strSumbName_SS . "." . $arrayFileAndExt[1];

                    // ***********************
                    // フォトアルバム画像保存
                    // ***********************

                    // 本番ディレクトリにコピーする(オリジナル)
                    if( true != copy( $strFileNameFullPath, str_replace( 'photo_album_temp', 'photo_album', $strFileNameFullPath ) ) )
                    {
                        $intRetCode = false;
                    }
                    unlink( $strFileNameFullPath );

                    // 本番ディレクトリにコピーする(サムネイル)
                    if( true != copy( $strUpFileSumbFullpath, str_replace( 'photo_album_temp', 'photo_album', $strUpFileSumbFullpath ) ) )
                    {
                        $intRetCode = false;
                    }
                    unlink( $strUpFileSumbFullpath );

                    // 本番ディレクトリにコピーする(最小サイズサムネイル)
                    if( true != copy( $strUpFileSumbFullpath_SS, str_replace( 'photo_album_temp', 'photo_album', $strUpFileSumbFullpath_SS ) ) )
                    {
                        $intRetCode = false;
                    }
                    unlink( $strUpFileSumbFullpath_SS );

                    // 画像作成が成功した場合のみDB登録
                    if( true == $intRetCode )
                    {
                        $subject = trim($subject);
                        if($subject=="")
                        {
                            $subject = $nosubject;
                        }

                        // 画像情報をDBに格納
                        $strMessage =   PhotoDB::InsertPhotoData(   intval( $strAlbumID )   ,
                                                                    $filename               ,   // オリジナルサイズファイル名
                                                                    $strSumbFileName        ,   // サムネイルファイル名
                                                                    $strSumpFileName_SS     ,   // サムネイルファイル名
                                                                    $subject                ,   // 画像タイトル
                                                                    $text                   ,   // 画像説明
                                                                    "0"                     ,  // 削除フラグ
                                                                    $strCommnetLoginID      );

                        // 処理成功ならメール送信
                        if( 0 == strlen( $strMessage ) )
                        {
                            MailSend::UpdateMailSend(   HEAD_MANAGE_ALBUM       ,   // フォトアルバム指定
                                                        intval( $strAlbumID )   ,   // アルバムID
                                                        $strAlbumName           );  // アルバム名（メール本文用）
                        }
                    }
                }
            }
            else
            {
                $write = false;
            }
        }
    }
}


/* コマンド送信 */
function _sendcmd($cmd) {
    global $sock;
    fputs($sock, $cmd."\r\n");
    $buf = fgets($sock, 512);
    if(substr($buf, 0, 3) == '+OK') {
        return $buf;
    }
    else {
        die($buf);
    }
    return false;
}

/* ヘッダと本文を分割する */
function mime_split($data) {
    $part = split("\r\n\r\n", $data, 2);
    $part[1] = ereg_replace("\r\n[\t ]+", " ", $part[1]);

    return $part;
}
/* メールアドレスを抽出する */
function addr_search($addr) {
    if (eregi("[-!#$%&\'*+\\./0-9A-Z^_`a-z{|}‾]+@[-!#$%&\'*+\\/0-9=?A-Z^_`a-z{|}‾]+\.[-!#$%&\'*+\\./0-9=?A-Z^_`a-z{|}‾]+", $addr, $fromreg)) {
      return $fromreg[0];
    }
    else {
      return false;
    }
}
/* 文字コードコンバートauto→SJIS */
function convert($str) {
    if (function_exists('mb_convert_encoding')) {
        return mb_convert_encoding($str, "SJIS", "JIS,SJIS");
    }
    else if (function_exists('JcodeConvert')) {
        return JcodeConvert($str, 0, 2);
    }
    return true;
}

header("Location:./album_detail.php". DOCOMO_GUID . "&s_id=" . $_GET["s_id"] . "&album_id=" . $strAlbumID . "&result=0" . "&pgno=" . $strPageNo . "&image_id=" . $strImageFlag );
exit;
