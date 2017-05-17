/* *************************************************************************************
【概要】入力値に対して必須入力チェック、最大長チェック、文字種チェックを実施する。

【機能】
  ・空文字チェック（結果OK：空文字、結果NG：エラーメッセージ）
  ・レングスチェック（結果OK：空文字、結果NG：エラーメッセージ）
    →固定長の場合は指定された長さ、可変長の場合は指定された長さ以内のチェック。
  ・文字種チェック（結果OK：空文字、結果NG：エラーメッセージ）
    →以下の文字種をチェックする。
      上記を組み合わせ、以下の関数を実装する。
      ?@半角数字チェック（IsNumericExpand）
      ?A半角英字チェック（IsAlphaExpand）
      ?B半角英数字チェック（IsNumAlphaExpand）
      ?C全角チェック（IsZenkakuExpand）
      ?D全角カナチェック（IsZenkakuKanaExpand）
      ?E電話番号チェック（IsTelNoExpand）
      ?Fメールアドレスチェック（IsEMailExpand）
      ?G文字種チェックなし（IsLengthExpand）

【入力パラメータ(全てキャラクタ)】
  input_data        ：チェック対象の値
  hissu_flag        ：必須フラグ。0:任意、1:必須
  min_length        ：最低長の数値。文字数ではなくByte数。
  max_length        ：最大長の数値。文字数ではなくByte数。
  fixed_length_flag ：固定長チェックフラグ(0:範囲、1:固定長)
  error_msg_title   ：エラーメッセージ用タイトル文字列（入力フィールドのタイトル）
  char_type         ：チェック文字種
                        1:半角数字
                        2:半角英数字
                        3:全角
                        4:全角カナ
                        5:電話番号
                        6:メールアドレス
                        8:半角英数記号
                        9:半角英字
                        10:日付妥当性チェック（うるう年チェックも含む）
                        それ以外:文字種チェックなし

【返り値】
  チェックOKの場合、空文字を返す。
  チェックNGの場合、エラーメッセージ文字列を返す。
  エラーメッセージが複数ある場合は¥nで区切った文字列を返す。

【使用例】
  var ResultMsg = InputCheckExpand( input_data, "0", "2", "5", "0", "「【企業情報】電話番号（市外局番）」", "1" );
  if( ResultMsg.length > 0 )
  {
    alert(ResultMsg);
    return false;
  }
  return true;
*************************************************************************************** */
function InputCheckExpand( input_data, hissu_flag, min_length, max_length, fixed_length_flag, error_msg_title, char_type, ErrorMessage )
{
  var ResultMsg     = "";
  var ResultMsgWork = "";

  var error_length = "";
  var error_string = "";

  var result_length = true;
  var result_string = true;
  var result_string_datetime = 0;

  //必須入力チェック
  ResultMsgWork = HissuCheck( input_data, hissu_flag, error_msg_title );
  ResultMsg = ResultMsg + ResultMsgWork;
  ResultMsgWork = "";

  //必須入力不要で、入力値lengthがゼロでなければ以下チェック続行する
  if( input_data.length > 0 )
  {
    //レングスチェック
    result_length = LengthCheck( input_data, min_length, max_length, fixed_length_flag );

    // 文字種ごとに分岐してチェックする
    switch( char_type )
    {
      case "1":
        //数字チェック
        result_string = NumericCheck( input_data );
        break;

      case "2":
        //英数字チェック
        result_string = NumAlphaCheck( input_data );
        break;

      case "3":
        //全角チェック
        result_string = ZenkakuCheck( input_data );
        break;

      case "4":
        //全角カナチェック
        result_string = ZenkakuKanaCheck( input_data );
        break;

      case "5":
        //電話番号チェック
        result_string = TelNoCheck( input_data );
        break;

      case "6":
        //メールアドレスチェック
        result_string = EMailCheck( input_data );
        break;

      case "8":
        //英数記号チェック
        result_string = NumAlphaMarkCheck( input_data );
        break;

      case "9":
        //英字チェック
        result_string = AlphaCheck( input_data );
        break;

      case "10":
        //日付妥当性チェック
        result_string = DateCheckExpand( input_data, max_length );
        break;

      case "11":
        //年月日時分妥当性チェック
        result_string_datetime = DateTimeCheckExpand( input_data, 0 );
        break;

      case "12":
        //年月日時分妥当性チェック（過去日OK）
        result_string_datetime = DateTimeCheckExpand( input_data, 1 );
        break;

      default:
        //文字種チェックなし
        break;
    }
  }

  //エラーメッセージ作成
  if( result_length == false || result_string == false || result_string_datetime != 0 )
  {
    ResultMsg = CreateErrorMsg( ResultMsg, char_type, min_length, max_length, fixed_length_flag, error_msg_title, result_length, result_string, result_string_datetime );
  }

  // 既にエラーメッセージがある場合は改行を加えて追加する
  if( ResultMsg.length > 0 )
  {
    var crlf = "";
    if( ErrorMessage.length > 0 )
    {
      crlf = "¥n";
    }
    ResultMsg = crlf + ResultMsg;
  }

  return( ResultMsg );
}



/* ***************************************************************************
 *  function名  : CreateErrorMsg
 *  機能概要    : エラーメッセージ作成
 *  機能詳細    : 文字種、長さ範囲、固定長を判断し、エラーメッセージを作成する。
 *  戻値        : エラーメッセージ文字列。
 *  引数        : ResultMsg         (I) 追加したいエラーメッセージの文字列
 *                char_type         (I) 文字種
 *                min_length        (I) チェック最小文字列長
 *                max_length        (I) チェック最大文字列長
 *                fixed_length_flag (I) 固定長チェックフラグ(0:範囲、1:固定長)
 *                error_msg_title   (I) エラーメッセージ用タイトル文字列
 *                result_length     (I) レングスエラー結果
 *                result_string     (I) 文字種エラー結果
 **************************************************************************** */
function CreateErrorMsg( ResultMsg, char_type, min_length, max_length, fixed_length_flag, error_msg_title, result_length, result_string, result_string_datetime )
{
  var error_msg      = "";
  var string_msg     = "";
  var mail_msg       = "";
  var back_msg       = "";
  var max_length_msg = max_length;
  var min_length_msg = min_length;

  // 文字種ごとに分岐してエラーメッセージ作成する
  switch( char_type )
  {
    case "1":
      //数字チェック
      string_msg = "は、半角数字";
      break;

    case "2":
      //英数字チェック
      string_msg = "は、半角英数字";
      break;

    case "3":
      //全角チェック
      string_msg = "は、";
      max_length_msg = max_length_msg / 2;
      min_length_msg = min_length_msg / 2;
      break;

    case "4":
      //全角カナチェック
      string_msg = "は、カナ";
      break;

    case "5":
      //電話番号チェック
      string_msg = "は、半角数字";
      break;

    case "6":
      //メールアドレスチェック
      string_msg = "は、";
      mail_msg   = "に正しくない文字が入力されています。";
      break;

    case "8":
      //英数記号チェック
      string_msg = "は、半角英数記号";
      break;

    case "9":
      //英字チェック
      string_msg = "は、半角英字";
      break;

    case "10":
      //日付妥当性チェック
      string_msg = "に正しくない日付が入力されています。";
      break;

    case "11":
      //年月日時分妥当性チェック
      if( result_string_datetime == 1 )
      {
        string_msg = "に存在しない年月日が入力されています。";
      }
      else
      {
        string_msg = "に過去の年月日および時間は指定できません。";
      }
      break;

    default:
      //文字種チェックなし
      string_msg = "は、";
      break;
  }

  //（最小長が指定されている場合は範囲を示すエラーメッセージ）
  if( min_length > "0" )
  {
    //範囲を示すエラーメッセージ
    back_msg = min_length + "文字以上" + max_length_msg + "文字以下で入力してください。";
  }
  //（固定長の場合）
  else if( fixed_length_flag != "0" )
  {
    //固定長を示すエラーメッセージ
    back_msg = max_length_msg + "文字で入力してください。";
  }
  else
  {
    //可変長を示すエラーメッセージ
    back_msg = max_length_msg + "文字以内で入力してください。";
  }

  //（E-MAILの場合）
  if( char_type == "6" )
  {
    //レングスエラーの場合
    if( result_length != true )
    {
      //エラーメッセージ編集
      error_msg = error_msg_title + string_msg + back_msg;
    }

    //文字種エラーの場合
    if( result_string != true )
    {
      if( error_msg.length > 0 )
      {
        //既にメッセージがあったら改行して追加する
        error_msg += "¥n";
      }
      //エラーメッセージ編集
      error_msg += error_msg_title + mail_msg;
    }
  }
  //（日付の場合）
  else if( char_type == "10" || char_type == "11" )
  {
    error_msg = error_msg_title + string_msg;
  }
  else
  {
    //エラーメッセージ編集
    error_msg = error_msg_title + string_msg + back_msg;
  }


  return( error_msg );
}



/* ***************************************************************************
 *  function名  : ParamCheck
 *  機能概要    : 入力チェック関数のパラメータチェック関数(現在は未使用）
 *  機能詳細    : 以下のチェックを実施する。
 *                  ?@必須フラグの正当性チェック（0 or 1）
 *                  ?A最大長の正当性チェック(数字のみ)
 *                  ?B固定長フラグの正当性チェック（0 or 1）
 *                  ?C文字種フラグの正当性チェック（数字のみ）
 *  戻値        : チェックOKの場合は空文字。
 *                チェックNGの場合はエラーメッセージ文字列を返す。
 *                なお、チェックは上記?@〜?Cまで実施し、すべてのエラーメッセージを¥nで区切った文字列を返す。
 *  引数        : hissu_flag        (I) 入力必須フラグ(0:任意、1:必須)
 *                max_length        (I) チェック最大文字列長
 *                fixed_length_flag (I) 固定長チェックフラグ(0:範囲、1:固定長)
 *                char_type         (I) チェック文字種(1:半角数字、2:半角英字、)
 *                error_msg_title   (I) エラーメッセージ用タイトル文字列（入力フィールドのタイトル）
 **************************************************************************** */
function ParamCheck( hissu_flag, max_length, fixed_length_flag, char_type, error_msg_title )
{
  var ResultMsg = "";

  //必須フラグチェック
  if( hissu_flag != "0" && hissu_flag != "1" )
  {
    if( ResultMsg.length > 0 )
    {
      ResultMsg = ResultMsg + "¥n";
    }
    ResultMsg = ResultMsg + error_msg_title + "必須フラグは 「0(任意) か 1(必須)」 を設定してください。(" + hissu_flag + ")";
  }

  //最大長チェック
  var MaxLengthWork = NumericCheck( max_length.toString(), "" );
  if( MaxLengthWork.length > 0 )
  {
    if( ResultMsg.length > 0 )
    {
      ResultMsg = ResultMsg + "¥n";
    }
    ResultMsg = ResultMsg + error_msg_title + "最大長は 「数値」 で設定してください。(" + max_length + ")";
  }

  //固定長フラグチェック
  if( fixed_length_flag != "0" && fixed_length_flag != "1" )
  {
    if( ResultMsg.length > 0 )
    {
      ResultMsg = ResultMsg + "¥n";
    }
    ResultMsg = ResultMsg + error_msg_title + "固定長フラグは 「0(可変) か 1(必須)」 を設定してください。(" + fixed_length_flag + ")";
  }

  //文字種フラグチェック
  var CharTypeWork = NumericCheck( char_type.toString(), "" );
  if( CharTypeWork.length > 0 )
  {
    if( ResultMsg.length > 0 )
    {
      ResultMsg = ResultMsg + "¥n";
    }
    ResultMsg = ResultMsg + error_msg_title + "文字種フラグは 「数値」 で設定してください。(" + char_type + ")";
  }

  return( ResultMsg );
}


/* ***************************************************************************
 *  function名  : HissuCheck
 *  機能概要    : 必須入力チェック関数
 *  機能詳細    : 以下のチェックを実施する。
 *                  ?@必須フラグが 1 の場合、必須チェック。lengthがゼロならエラー。
 *  戻値        : チェックOKの場合は空文字。
 *                チェックNGの場合はエラーメッセージ文字列を返す。
 *  引数        : input_data        ：char：チェック対象の値
 *                hissu_flag        (I) 入力必須フラグ(0:任意、1:必須)
 *                error_msg_title   (I) エラーメッセージ用タイトル文字列（入力フィールドのタイトル）
 **************************************************************************** */
function HissuCheck( input_data, hissu_flag, error_msg_title )
{
  var ResultMsg = "";

  //必須チェックありで、長さゼロ（未入力）の場合はエラー
  if( hissu_flag == "1" && input_data.length == 0 )
  {
    ResultMsg = error_msg_title + "は、必ず入力して頂く必要があります。";
  }

  return( ResultMsg );
}


/* ***************************************************************************
 *  function名  : LengthCheck
 *  機能概要    : 最大長チェック関数
 *  機能詳細    : 以下のチェックを実施する。
 *                  ?@入力値が最大長を超えた場合はエラー。
 *  戻値        : チェックOKの場合は空文字。
 *                チェックNGの場合はエラーメッセージ文字列を返す。
 *  引数        : input_data        ：char：チェック対象の値
 *                min_length        (I) チェック最小文字列長
 *                max_length        (I) チェック最大文字列長
 *                fixed_length_flag (I) 固定長チェックフラグ(0:範囲、1:固定長)
 *                error_msg_title   (I) エラーメッセージ用タイトル文字列（入力フィールドのタイトル）
 *                char_type         (I) 文字種
 **************************************************************************** */
function LengthCheck( input_data, min_length, max_length, fixed_length_flag )
{
  var result_code = true;
  var string_len  = 0;

  //どちらも未入力の場合はレングスチェックしない
  if( min_length.length == 0 && max_length.length == 0 )
  {
    return( result_code );
  }

  //全角を2byteとして扱うため、まずはbyte単位で長さ取得
  var i = 0;
  var one_string = "";
  for( i = 0; i < input_data.length; i++ )
  {
    one_string = input_data.substr( i, 1 );
    //（全角の場合）
    if( ZenkakuCheck( one_string ) == true )
    {
      string_len += 2;
    }
    else
    {
      string_len += 1;
    }
  }
  //可変長で、最大長を超えている場合はエラー
  if( fixed_length_flag == "0" && string_len > max_length && min_length == "0" )
  {
    result_code = false;
  }

  //最小文字列長がゼロ以上なら範囲チェック
  if( ( fixed_length_flag == "0" && string_len > max_length ) || 
      ( min_length        >  "0" && string_len < min_length )    )
  {
    result_code = false;
  }

  //固定長で、長さがイコールではない場合はエラー
  if( fixed_length_flag == "1" && string_len != max_length )
  {
    result_code = false;
  }

  return( result_code );
}


/* ***************************************************************************
 *  function名  : NumericCheck
 *  機能概要    : 数値チェック
 *  機能詳細    : 入力値に対し以下のチェックを実施する。
 *                  ?@数値チェック
 *  戻値        : チェックOKの場合はtrue
 *                チェックNGの場合はfalse
 *  引数    : input_data        (I) チェック対象文字列
 **************************************************************************** */
function NumericCheck( input_data )
{
  var result_code = true;

  //正規表現で数字以外の場合エラー("/["〜"]/"の間で表現される値ではない場合、というmatch関数を用いたチェック)
  //"^"は「〜ではない」を表す。"0-9"は範囲を示す。"g"は文字列すべて、という意味。
  var reg = new RegExp(/[^0-9]/g);
  if( reg.test(input_data) == true )
  {
    result_code = false;
  }

  return( result_code );
}

/* ***************************************************************************
 *  function名  : AlphaCheck
 *  機能概要    : アルファベットチェック
 *  機能詳細    : 入力値に対し以下のチェックを実施する。
 *                  ?@アルファベットチェック
 *  戻値        : チェックOKの場合はtrue
 *                チェックNGの場合はfalse
 *  引数    : input_data        (I) チェック対象文字列
 **************************************************************************** */
function AlphaCheck( input_data )
{
  var result_code = true;

  //正規表現で数字以外の場合エラー("/["〜"]/"の間で表現される値ではない場合、というmatch関数を用いたチェック)
  //"^"は「〜ではない」を表す。"A-Z"は範囲を示す。"g"は文字列すべて、という意味。
  var reg = new RegExp(/[^a-zA-Z]/g);
  if( reg.test(input_data) == true )
  {
    result_code = false;
  }

  return( result_code );
}

/* ***************************************************************************
 *  function名  : NumAlphaCheck
 *  機能概要    : 英数チェック
 *  機能詳細    : 入力値に対し以下のチェックを実施する。
 *                  ?@英数チェック
 *  戻値        : チェックOKの場合はtrue
 *                チェックNGの場合はfalse
 *  引数    : input_data        (I) チェック対象文字列
 **************************************************************************** */
function NumAlphaCheck( input_data )
{
  var result_code = true;

  //正規表現で数字以外の場合エラー("/["〜"]/"の間で表現される値ではない場合、というmatch関数を用いたチェック)
  //"^"は「〜ではない」を表す。"A-Z"は範囲を示す。"g"は文字列すべて、という意味。
  var reg = new RegExp(/[^a-zA-Z0-9]/g);
  if( reg.test(input_data) == true )
  {
    result_code = false;
  }

  return( result_code );
}

/* ***************************************************************************
 *  function名  : NumAlphaMarkCheck
 *  機能概要    : 英数記号チェック
 *  機能詳細    : 入力値に対し以下のチェックを実施する。
 *                  ?@英数記号チェック
 *  戻値        : チェックOKの場合はtrue
 *                チェックNGの場合はfalse
 *  引数    : input_data        (I) チェック対象文字列
 **************************************************************************** */
function NumAlphaMarkCheck( input_data )
{
  var result_code = true;

  //正規表現で数字以外の場合エラー("/["〜"]/"の間で表現される値ではない場合、というmatch関数を用いたチェック)
  //"^"は「〜ではない」を表す。"A-Z"は範囲を示す。"g"は文字列すべて、という意味。
  var reg = new RegExp(/[^!-‾]/g);
  if( reg.test(input_data) == true )
  {
    result_code = false;
  }

  return( result_code );
}

/* ***************************************************************************
 *  function名  : ZenkakuCheck
 *  機能概要    : 全角チェック
 *  機能詳細    : 入力値に対し以下のチェックを実施する。
 *                  ?@全角チェック
 *  戻値        : チェックOKの場合はtrue
 *                チェックNGの場合はfalse
 *  引数    : input_data        (I) チェック対象文字列
 **************************************************************************** */
function ZenkakuCheck( input_data )
{
  var result_code = true;

  //１文字ずつ抜き出して、Unicode変換した後の値をチェックする
  var i = 0;
  var ch_buf = "";
  for( i = 0; i < input_data.length; i++ )
  {
    var ch_buf = input_data.charCodeAt(i);
    // １文字を抜き出してUnicode変換し、範囲を判定。（半角カタカナは不許可とする）
    //（全角文字のUnicode範囲以外の場合）
    if( ch_buf < 256 || (ch_buf >= 0xff61 && ch_buf <= 0xff9f) )
    {
      result_code = false;
      break;
    }
  }

  return( result_code );
}

/* ***************************************************************************
 *  function名  : ZenkakuKanaCheck
 *  機能概要    : 全角カナチェック
 *  機能詳細    : 入力値に対し以下のチェックを実施する。
 *                  ?@全角カナチェック
 *  戻値        : チェックOKの場合はtrue
 *                チェックNGの場合はfalse
 *  引数    : input_data        (I) チェック対象文字列
 **************************************************************************** */
function ZenkakuKanaCheck( input_data )
{
  var result_code = true;
  var ZenkakuKanaData = "　ァアィイゥウェエォオカガキギクグケゲコゴサザシジスズセゼソゾタダチヂッツヅテデトドナニヌネノハバパヒビピフブプヘベペホボポマミムメモャヤュユョヨラリルレロヮワヲンヴヵヶー";

  var i = 0;
  // 入力データの文字数分ループ
  for( i = 0; i < input_data.length; i++ )
  {
    //定義したカナに含まれない文字であればエラーとする
    if( ZenkakuKanaData.indexOf( input_data.charAt(i) ) == -1 )
    {
      result_code = false;
      break;
    }
  }
  return( result_code );
}

/* ***************************************************************************
 *  function名  : TelNoCheck
 *  機能概要    : 電話番号チェック
 *  機能詳細    : 入力値に対し以下のチェックを実施する。
 *                  ?@電話番号チェック（数字、"-"、"("、")"以外はエラー）
 *  戻値        : チェックOKの場合はtrue
 *                チェックNGの場合はfalse
 *  引数    : input_data        (I) チェック対象文字列
 **************************************************************************** */
function TelNoCheck( input_data )
{
  var result_code = true;

  //正規表現で数字以外の場合エラー("/["〜"]/"の間で表現される値ではない場合、というmatch関数を用いたチェック)
  //"^"は「〜ではない」を表す。"A-Z"は範囲を示す。"g"は文字列すべて、という意味。
  var reg = new RegExp(/[^0-9|(|)|-]/g);
  if( reg.test(input_data) == true )
  {
    result_code = false;
  }

  return( result_code );
}

/* ***************************************************************************
 *  function名  : EMailCheck
 *  機能概要    : メールアドレスチェック
 *  機能詳細    : 入力値に対し以下のチェックを実施する。
 *                  ?@メールアドレスチェック
 *  戻値        : チェックOKの場合はtrue
 *                チェックNGの場合はfalse
 *  引数    : input_data        (I) チェック対象文字列
 **************************************************************************** */
function EMailCheck( input_data )
{
  var result_code = true;

  //正規表現で数字以外の場合エラー("/["〜"]/"の間で表現される値ではない場合、というmatch関数を用いたチェック)
  //"^"は「〜ではない」を表す。"A-Z"は範囲を示す。"g"は文字列すべて、という意味。
  if( input_data.match(/^[A-Za-z0-9¥_¥.¥-]+¥@[A-Za-z0-9¥_¥.¥-]+¥.[A-Za-z0-9¥_¥.¥-]*[A-Za-z0-9]+$/) == null )
  {
    result_code = false;
  }

  return( result_code );
}

/* ***************************************************************************
 *  function名  : DateCheckExpand
 *  機能概要    : 年月日チェック
 *  機能詳細    : 入力値に対し以下のチェックを実施する。
 *                  ?@数値チェック
 *                  ?Aレングスチェック
 *                  ?B年月日妥当性チェック
 *  戻値        : チェックOKの場合はtrue
 *                チェックNGの場合はfalse
 *  引数    : input_data        (I) チェック対象文字列
 *            max_length        (I) 年月日の長さ(YYMMDD形式なら6、YYYYMMDD形式なら8)
 **************************************************************************** */
function DateCheckExpand( input_data, max_length )
{
  var result_code      = true;
  var result_code_num  = true;
  var result_code_len  = true;
  var result_code_ymd  = true;

  //数値チェック
  result_code_num = NumericCheck( input_data );

  //レングスチェック(YYMMDD形式は6桁、YYYYMMDD形式は8桁の固定長チェック)
  result_code_len = LengthCheck( input_data, "", max_length, "1" );

  //年月日妥当性チェック(6桁、8桁対応)
  result_code_ymd = DateCheck( input_data, max_length );

  //どこかにエラーがあった場合はfalse
  if( result_code_num != true || result_code_len != true || result_code_ymd != true )
  {
    result_code = false;
  }

  return( result_code );
}

/* ***************************************************************************
 *  function名  : DateCheckExpand
 *  機能概要    : 年月日チェック
 *  機能詳細    : 入力値に対し以下のチェックを実施する。
 *                  ?@数値チェック
 *                  ?Aレングスチェック
 *                  ?B年月日妥当性チェック
 *  戻値        : チェックOKの場合はtrue
 *                チェックNGの場合はfalse
 *  引数    : input_data        (I) チェック対象文字列
 *            max_length        (I) 年月日の長さ(YYMMDD形式なら6、YYYYMMDD形式なら8)
 **************************************************************************** */
function DateCheckExpand( input_data, max_length )
{
  var result_code      = true;
  var result_code_num  = true;
  var result_code_len  = true;
  var result_code_ymd  = true;

  //数値チェック
  result_code_num = NumericCheck( input_data );

  //レングスチェック(YYMMDD形式は6桁、YYYYMMDD形式は8桁の固定長チェック)
  result_code_len = LengthCheck( input_data, "", max_length, "1" );

  //年月日妥当性チェック(6桁、8桁対応)
  result_code_ymd = DateCheck( input_data, max_length );

  //どこかにエラーがあった場合はfalse
  if( result_code_num != true || result_code_len != true || result_code_ymd != true )
  {
    result_code = false;
  }

  return( result_code );
}

/* ***************************************************************************
 *  function名  : DateTimeCheckExpand
 *  機能概要    : 年月日時分チェック
 *  機能詳細    : 入力値に対し以下のチェックを実施する。
 *                  ?@数値チェック
 *                  ?Aレングスチェック
 *                  ?B年月日時分妥当性チェック（過去はエラー）
 *  戻値        : チェックOKの場合は0
 *                チェックNGの場合は1:存在しない日付、2:過去日
 *  引数        : input_data        (I) チェック対象文字列
 **************************************************************************** */
function DateTimeCheckExpand( input_data, past_flag )
{
  var result_code    = 0;
  var result_code_yy = true;
  var result_code_mm = true;
  var result_code_dd = true;
  var ch_yyyy        = 0;
  var ch_mm          = 0;
  var ch_dd          = 0;
  var yyyy_size      = 4;

  //年取得
  ch_yyyy = Number( input_data.substr( 0, yyyy_size ) );

  //月取得
  ch_mm   = Number( input_data.substr( yyyy_size, 2 ) );

  //日取得
  ch_dd   = Number( input_data.substr( yyyy_size + 2, 2 ) );

  //時取得
  ch_hh   = Number( input_data.substr( yyyy_size + 2 + 2, 2 ) );

  //分取得
  ch_mi   = Number( input_data.substr( yyyy_size + 2 + 2 + 2, 2 ) );

  //年チェック
  //（システム日付以前の場合エラー）
  var nowdate = new Date();

  //ブラウザチェック
  ret = isIE();
  //IEの場合
  if(ret == true)
  {
    var sys_yy  = Number( nowdate.getYear() );
  }
  else
  {
    var sys_yy  = 1900+Number( nowdate.getYear() );
  }

  var sys_mm  = Number( nowdate.getMonth() + 1 );
  var sys_dd  = Number( nowdate.getDate() );
  var sys_hh  = Number( nowdate.getHours() );
  var sys_mi  = Number( nowdate.getMinutes() );

  //月チェック
  if( 1 > ch_mm || ch_mm > 12 )
  {
    result_code_mm = false;
  }

  //３０日までの月の場合
  if( 4 == ch_mm || 6 == ch_mm || 9 == ch_mm || 11 == ch_mm )
  {
    //３０日を超えていたらエラー
    if( ch_dd > 30 )
    {
      result_code_dd = false;
    }
  }
  //２月の場合
  else if( 2 == ch_mm )
  {
    //うるう年チェック
    if( ( 0 == ch_yyyy % 4 && 0 < ch_yyyy % 100 ) || 0 == ch_yyyy % 400 )
    {
      //２９日を超えていたらエラー
      if( ch_dd > 29 )
      {
        result_code_dd = false;
      }
    }
    //うるう年以外
    else
    {
      //２８日を超えていたらエラー
      if( ch_dd > 28 )
      {
        result_code_dd = false;
      }
    }
  }
  //上記以外は３１日
  else
  {
    //３１日を超えていたらエラー
    if( ch_dd > 31 )
    {
      result_code_dd = false;
    }
  }

  //エラーチェック
  if( result_code_yy != true || result_code_mm != true || result_code_dd != true )
  {
    result_code = 1;
  }

  // 年月日時分の過去チェック
  sys_yy_str = sys_yy.toString();
  sys_mm_str = sys_mm.toString();
  sys_dd_str = sys_dd.toString();
  sys_hh_str = sys_hh.toString();
  sys_mi_str = sys_mi.toString();
  if( sys_mm_str.length == 1 )
  {
    sys_mm_str = "0" + sys_mm_str;
  }
  if( sys_dd_str.length == 1 )
  {
    sys_dd_str = "0" + sys_dd_str;
  }
  if( sys_hh_str.length == 1 )
  {
    sys_hh_str = "0" + sys_hh_str;
  }
  if( sys_mi_str.length == 1 )
  {
    sys_mi_str = "0" + sys_mi_str;
  }

  ch_yy_str = ch_yyyy.toString();
  ch_mm_str = ch_mm.toString();
  ch_dd_str = ch_dd.toString();
  ch_hh_str = ch_hh.toString();
  ch_mi_str = ch_mi.toString();
  if( ch_mm_str.length == 1 )
  {
    ch_mm_str = "0" + ch_mm_str;
  }
  if( ch_dd_str.length == 1 )
  {
    ch_dd_str = "0" + ch_dd_str;
  }
  if( ch_hh_str.length == 1 )
  {
    ch_hh_str = "0" + ch_hh_str;
  }
  if( ch_mi_str.length == 1 )
  {
    ch_mi_str = "0" + ch_mi_str;
  }

  // 過去日かどうかをチェックする
  if( 0 == past_flag )
  {
    sys_YYYYMMDDHHMM = sys_yy_str + sys_mm_str + sys_dd_str + sys_hh_str + sys_mi_str;
    ch_YYYYMMDDHHMM  = ch_yy_str  + ch_mm_str  + ch_dd_str  + ch_hh_str  + ch_mi_str;
    if( Number( sys_YYYYMMDDHHMM ) > Number( ch_YYYYMMDDHHMM ) )
    {
      result_code = 2;
    }
  }
//    alert(sys_YYYYMMDDHHMM + ":" + ch_YYYYMMDDHHMM);

  return( result_code );
}


/* ***************************************************************************
 *  function名  : UserInputCheckManage
 *  機能概要    : ユーザ情報入力チェック用function(管理用)
 **************************************************************************** */
function UserInputCheckManage()
{
  var ErrorMessage = "";

  // 名前取得
  var Name              = document.user_form.elements["user_name" ].value;

  // 名前カナ取得
  var NameKana          = document.user_form.elements["user_name_kana" ].value;

  // ログインＩＤ
  var LoginID           = document.user_form.elements["login_id" ].value;

  // パスワード
  var Password          = document.user_form.elements["password" ].value;

  // PCメール
  var Mail_Pc           = document.user_form.elements["mail_address_pc" ].value;

  // BBSメール
  var Mail_BBS          = document.user_form.elements["bbs_write_mailaddress" ].value;

  // 名前チェック
  ErrorMessage = ErrorMessage + InputCheckExpand( Name              ,   // 入力データ
                                                  '1'               ,   // 必須チェック(1:必須)
                                                  '1'               ,   // 最小桁数
                                                  '50'              ,   // 最大桁数
                                                  '0'               ,   // 範囲・固定長チェック(0:範囲チェック)
                                                  '【名前(漢字)】'  ,   // エラーメッセージタイトル
                                                  '3'               ,   // 全角
                                                  ErrorMessage      );  // エラーメッセージ

  // 名前カナチェック
  ErrorMessage = ErrorMessage + InputCheckExpand( NameKana          ,   // 入力データ
                                                  '1'               ,   // 必須チェック(1:必須)
                                                  '1'               ,   // 最小桁数
                                                  '50'              ,   // 最大桁数
                                                  '0'               ,   // 範囲・固定長チェック(0:範囲チェック)
                                                  '【名前(カナ)】'  ,   // エラーメッセージタイトル
                                                  '4'               ,   // 全角カナ
                                                  ErrorMessage      );  // エラーメッセージ

  // ログインID
  ErrorMessage = ErrorMessage + InputCheckExpand( LoginID           ,   // 入力データ
                                                  '1'               ,   // 必須チェック(1:必須)
                                                  '1'               ,   // 最小桁数
                                                  '20'              ,   // 最大桁数
                                                  '0'               ,   // 範囲・固定長チェック(0:範囲チェック)
                                                  '【ログインID】'  ,   // エラーメッセージタイトル
                                                  '8'               ,   // チェック文字種タイプ(半角英数記号)
                                                  ErrorMessage      );  // エラーメッセージ

  // パスワード
  ErrorMessage = ErrorMessage + InputCheckExpand( Password          ,   // 入力データ
                                                  '1'               ,   // 必須チェック(1:必須)
                                                  '1'               ,   // 最小桁数
                                                  '20'              ,   // 最大桁数
                                                  '0'               ,   // 範囲・固定長チェック(0:範囲チェック)
                                                  '【パスワード】'  ,   // エラーメッセージタイトル
                                                  '8'               ,   // チェック文字種タイプ(半角英数記号)
                                                  ErrorMessage      );  // エラーメッセージ

  // PCメール
  ErrorMessage = ErrorMessage + InputCheckExpand( Mail_Pc           ,   // 入力データ
                                                  '1'               ,   // 必須チェック(1:必須)
                                                  '1'               ,   // 最小桁数
                                                  '200'             ,   // 最大桁数
                                                  '0'               ,   // 範囲・固定長チェック(0:範囲チェック)
                                                  '【PCメールアドレス】'  ,   // エラーメッセージタイトル
                                                  '6'               ,   // チェック文字種タイプ(メールアドレス)
                                                  ErrorMessage      );  // エラーメッセージ
  // BBSメール
  ErrorMessage = ErrorMessage + InputCheckExpand( Mail_BBS          ,   // 入力データ
                                                  '1'               ,   // 必須チェック(1:必須)
                                                  '1'               ,   // 最小桁数
                                                  '1000'            ,   // 最大桁数
                                                  '0'               ,   // 範囲・固定長チェック(0:範囲チェック)
                                                  '【BBSメールアドレス】'  ,   // エラーメッセージタイトル
                                                  '0'               ,   // チェック文字種タイプ(半角英数記号)
                                                  ErrorMessage      );  // エラーメッセージ

  // エラーがあった場合は表示して終了
  if( ErrorMessage.length > 0 )
  {
    alert( ErrorMessage );
    return;
  }
  else
  {
    document.user_form.submit();
  }

}

/* ***************************************************************************
 *  function名  : AlbumInputCheckManage
 *  機能概要    : フォトアルバム情報入力チェック用function(管理用)
 **************************************************************************** */
function AlbumInputCheckManage()
{
  var ErrorMessage = "";

  // アルバム名
  var AlbumName         = document.album_form.elements["album_name"].value;

  // 投稿メール
  var Mail              = document.album_form.elements["mail_address" ].value;

  // 名前チェック
  ErrorMessage = ErrorMessage + InputCheckExpand( AlbumName         ,   // 入力データ
                                                  '1'               ,   // 必須チェック(1:必須)
                                                  '1'               ,   // 最小桁数
                                                  '100'             ,   // 最大桁数
                                                  '0'               ,   // 範囲・固定長チェック(0:範囲チェック)
                                                  '【タイトル】'    ,   // エラーメッセージタイトル
                                                  '0'               ,   // 文字種チェック無し
                                                  ErrorMessage      );  // エラーメッセージ

  // 投稿メール
  ErrorMessage = ErrorMessage + InputCheckExpand( Mail              ,   // 入力データ
                                                  '1'               ,   // 必須チェック(1:必須)
                                                  '1'               ,   // 最小桁数
                                                  '200'             ,   // 最大桁数
                                                  '0'               ,   // 範囲・固定長チェック(0:範囲チェック)
                                                  '【投稿用メールアドレス】'  ,   // エラーメッセージタイトル
                                                  '6'               ,   // チェック文字種タイプ(メールアドレス)
                                                  ErrorMessage      );  // エラーメッセージ

    // 公開レベル選択の場合は、１つ以上グループ選択していないとエラー
    if( true == document.album_form.del_flag[1].checked )
    {
        var GroupCheck = false;
        // グループ数ループ
        for( i = 0; i < document.album_form.elements['select_group[]'].length; i++ )
        {
            if( document.album_form.elements['select_group[]'][i].checked )
            {
                GroupCheck = true;
                break;
            }
        }

        if( true != GroupCheck )
        {
            if( 0 < ErrorMessage.length )
            {
                ErrorMessage = ErrorMessage + "¥n";
            }
            ErrorMessage = ErrorMessage + "【公開ユーザグループ】状態が「公開レベル選択」の場合は、公開ユーザグループを１つ以上選択してください。";
        }
    }

  // エラーがあった場合は表示して終了
  if( ErrorMessage.length > 0 )
  {
    alert( ErrorMessage );
    return;
  }
  else
  {
    document.album_form.submit();
  }

}

/* ***************************************************************************
 *  function名  : InfoInputCheckManage
 *  機能概要    : 管理本部通信入力チェック用function(管理用)
 **************************************************************************** */
function InfoInputCheckManage()
{
  var ErrorMessage = "";

  // 年月日時分を取得
  var SelectedYear      = document.info_form.elements["info_date_yyyy" ].options[ document.info_form.elements["info_date_yyyy" ].selectedIndex ].value;
  var SelectedMonth     = document.info_form.elements["info_date_mm"   ].options[ document.info_form.elements["info_date_mm"   ].selectedIndex ].value;
  var SelectedDay       = document.info_form.elements["info_date_dd"   ].options[ document.info_form.elements["info_date_dd"   ].selectedIndex ].value;

  var YYYYMMDD_HHMM     = SelectedYear + SelectedMonth + SelectedDay + "0000";

  // タイトル
  var Title           = document.info_form.elements["info_title"].value;

  // 年月日時分チェック実施
  ErrorMessage = ErrorMessage + InputCheckExpand( YYYYMMDD_HHMM  ,    // 入力データ
                                                  '1'            ,    // 必須チェック(1:必須)
                                                  '12'           ,    // 最小桁数
                                                  '12'           ,    // 最大桁数
                                                  '1'            ,    // 範囲・固定長チェック(1:固定長チェック)
                                                  '【公開年月日】',   // エラーメッセージタイトル
                                                  '12'           ,    // チェック文字種タイプ(12:年月日時分チェック(過去はエラーとしない))
                                                  ErrorMessage    );  // エラーメッセージ

  // 名前チェック
  ErrorMessage = ErrorMessage + InputCheckExpand( Title           ,   // 入力データ
                                                  '1'             ,   // 必須チェック(1:必須)
                                                  '1'             ,   // 最小桁数
                                                  '100'           ,   // 最大桁数
                                                  '0'             ,   // 範囲・固定長チェック(0:範囲チェック)
                                                  '【タイトル】'  ,   // エラーメッセージタイトル
                                                  '0'             ,   // 文字種チェック無し
                                                  ErrorMessage    );  // エラーメッセージ

    // 公開レベル選択の場合は、１つ以上グループ選択していないとエラー
    if( true == document.info_form.del_flag[1].checked )
    {
        var GroupCheck = false;
        // グループ数ループ
        for( i = 0; i < document.info_form.elements['select_group[]'].length; i++ )
        {
            if( document.info_form.elements['select_group[]'][i].checked )
            {
                GroupCheck = true;
                break;
            }
        }

        if( true != GroupCheck )
        {
            if( 0 < ErrorMessage.length )
            {
                ErrorMessage = ErrorMessage + "¥n";
            }
            ErrorMessage = ErrorMessage + "【公開ユーザグループ】状態が「公開レベル選択」の場合は、公開ユーザグループを１つ以上選択してください。";
        }
    }

  // エラーがあった場合は表示して終了
  if( ErrorMessage.length > 0 )
  {
    alert( ErrorMessage );
    return;
  }
  else
  {
    document.info_form.submit();
  }

}

/* ***************************************************************************
 *  function名  : UserGroupInputCheckManage
 *  機能概要    : ユーザグループ情報入力チェック用function(管理用)
 **************************************************************************** */
function UserGroupInputCheckManage()
{
  var ErrorMessage = "";

  // ユーザグループ名
  var GroupName         = document.usergroup_form.elements["group_name"].value;

  // ユーザグループ説明
  var GroupNote         = document.usergroup_form.elements["group_note"].value;

  // 名前チェック
  ErrorMessage = ErrorMessage + InputCheckExpand( GroupName         ,   // 入力データ
                                                  '1'               ,   // 必須チェック(1:必須)
                                                  '1'               ,   // 最小桁数
                                                  '100'             ,   // 最大桁数
                                                  '0'               ,   // 範囲・固定長チェック(0:範囲チェック)
                                                  '【ユーザグループ名】'    ,   // エラーメッセージタイトル
                                                  '0'               ,   // 文字種チェック無し
                                                  ErrorMessage      );  // エラーメッセージ

  // グループ説明チェック
  ErrorMessage = ErrorMessage + InputCheckExpand( GroupNote         ,   // 入力データ
                                                  '1'               ,   // 必須チェック(1:必須)
                                                  '1'               ,   // 最小桁数
                                                  '1000'            ,   // 最大桁数
                                                  '0'               ,   // 範囲・固定長チェック(0:範囲チェック)
                                                  '【ユーザグループ説明】'    ,   // エラーメッセージタイトル
                                                  '0'               ,   // 文字種チェック無し
                                                  ErrorMessage      );  // エラーメッセージ

  // エラーがあった場合は表示して終了
  if( ErrorMessage.length > 0 )
  {
    alert( ErrorMessage );
    return;
  }
  else
  {
    document.usergroup_form.submit();
  }

}

/* ***************************************************************************
 *  function名  : SectionInputCheckManage
 *  機能概要    : 事業部情報入力チェック用function(管理用)
 **************************************************************************** */
function SectionInputCheckManage()
{
  var ErrorMessage = "";

  // 事業部名
  var Name         = document.section_form.elements["section_name"].value;

  // 事業部説明
  var Note         = document.section_form.elements["section_note"].value;

  // 名前チェック
  ErrorMessage = ErrorMessage + InputCheckExpand( Name              ,   // 入力データ
                                                  '1'               ,   // 必須チェック(1:必須)
                                                  '1'               ,   // 最小桁数
                                                  '50'              ,   // 最大桁数
                                                  '0'               ,   // 範囲・固定長チェック(0:範囲チェック)
                                                  '【事業部名】'    ,   // エラーメッセージタイトル
                                                  '0'               ,   // 文字種チェック無し
                                                  ErrorMessage      );  // エラーメッセージ

  // 説明チェック
  ErrorMessage = ErrorMessage + InputCheckExpand( Note              ,   // 入力データ
                                                  '1'               ,   // 必須チェック(1:必須)
                                                  '1'               ,   // 最小桁数
                                                  '1000'            ,   // 最大桁数
                                                  '0'               ,   // 範囲・固定長チェック(0:範囲チェック)
                                                  '【事業部説明】'  ,   // エラーメッセージタイトル
                                                  '0'               ,   // 文字種チェック無し
                                                  ErrorMessage      );  // エラーメッセージ

  // エラーがあった場合は表示して終了
  if( ErrorMessage.length > 0 )
  {
    alert( ErrorMessage );
    return;
  }
  else
  {
    document.section_form.submit();
  }

}

/* ***************************************************************************
 *  function名  : MailInputCheckManage
 *  機能概要    : 送信メール情報入力チェック用function(管理用)
 **************************************************************************** */
function MailInputCheckManage()
{
  var ErrorMessage = "";

  // 差出人
  var MailFrom  = document.mail_form.elements["mail_from" ].value;

  // 送信先
  var MailTo    = document.mail_form.elements["mail_to" ].value;

  // タイトル
  var Title     = document.mail_form.elements["mail_subject" ].value;

  // 本文
  var Body      = document.mail_form.elements["mail_body" ].value;

  // 差出人メール
  ErrorMessage = ErrorMessage + InputCheckExpand( MailFrom          ,   // 入力データ
                                                  '1'               ,   // 必須チェック(1:必須)
                                                  '1'               ,   // 最小桁数
                                                  '200'             ,   // 最大桁数
                                                  '0'               ,   // 範囲・固定長チェック(0:範囲チェック)
                                                  '【差出人メールアドレス】'  ,   // エラーメッセージタイトル
                                                  '6'               ,   // チェック文字種タイプ(メールアドレス)
                                                  ErrorMessage      );  // エラーメッセージ

  // 送信先メール
  ErrorMessage = ErrorMessage + InputCheckExpand( MailTo            ,   // 入力データ
                                                  '0'               ,   // 必須チェック(1:任意)
                                                  '1'               ,   // 最小桁数
                                                  '200'             ,   // 最大桁数
                                                  '0'               ,   // 範囲・固定長チェック(0:範囲チェック)
                                                  '【送信先メールアドレス】'  ,   // エラーメッセージタイトル
                                                  '6'               ,   // チェック文字種タイプ(メールアドレス)
                                                  ErrorMessage      );  // エラーメッセージ

  // タイトル
  ErrorMessage = ErrorMessage + InputCheckExpand( Title             ,   // 入力データ
                                                  '1'               ,   // 必須チェック(1:必須)
                                                  '1'               ,   // 最小桁数
                                                  '500'             ,   // 最大桁数
                                                  '0'               ,   // 範囲・固定長チェック(0:範囲チェック)
                                                  '【メールタイトル】'  ,   // エラーメッセージタイトル
                                                  '0'               ,   // 文字種チェック無し
                                                  ErrorMessage      );  // エラーメッセージ

  // 名前カナチェック
  ErrorMessage = ErrorMessage + InputCheckExpand( Body              ,   // 入力データ
                                                  '1'               ,   // 必須チェック(1:必須)
                                                  '1'               ,   // 最小桁数
                                                  '10000'           ,   // 最大桁数
                                                  '0'               ,   // 範囲・固定長チェック(0:範囲チェック)
                                                  '【本文】'        ,   // エラーメッセージタイトル
                                                  '0'               ,   // 文字種チェック無し
                                                  ErrorMessage      );  // エラーメッセージ

    // 送信先メールアドレス入力の無い場合は、１つ以上グループ・ユーザ選択していないとエラー
    if( 0 == MailTo.length )
    {
        var GroupCheck = false;
        // グループ数ループ
        for( i = 0; i < document.mail_form.elements['select_group[]'].length; i++ )
        {
            if( document.mail_form.elements['select_group[]'][i].checked )
            {
                GroupCheck = true;
                break;
            }
        }

        var UserCheck = false;
        // ユーザ数ループ
        for( i = 0; i < document.mail_form.elements['select_user[]'].length; i++ )
        {
            if( document.mail_form.elements['select_user[]'][i].checked )
            {
                UserCheck = true;
                break;
            }
        }

        // グループもユーザも選択していないときはエラー
        if( true != GroupCheck && true != UserCheck )
        {
            if( 0 < ErrorMessage.length )
            {
                ErrorMessage = ErrorMessage + "¥n";
            }
            ErrorMessage = ErrorMessage + "【送信先】送信先メールアドレスを入力するか、グループ・ユーザを１つ以上選択してください。";
        }
    }

  // エラーがあった場合は表示して終了
  if( ErrorMessage.length > 0 )
  {
    alert( ErrorMessage );
    return;
  }
  else
  {
    document.mail_form.submit();
  }

}

/* ***************************************************************************
 *  function名  : ThreadInputCheckManage
 *  機能概要    : スレッド情報入力チェック用function(管理用)
 **************************************************************************** */
function ThreadInputCheckManage()
{
  var ErrorMessage = "";

  // タイトル
  var Title           = document.thread_form.elements["thread_name"].value;

  // 使用メールアドレス
  var Mail            = document.thread_form.elements["thread_mailaddress"].value;

  // 説明
  var Note            = document.thread_form.elements["thread_note"].value;

  // 名前チェック
  ErrorMessage = ErrorMessage + InputCheckExpand( Title           ,   // 入力データ
                                                  '1'             ,   // 必須チェック(1:必須)
                                                  '1'             ,   // 最小桁数
                                                  '100'           ,   // 最大桁数
                                                  '0'             ,   // 範囲・固定長チェック(0:範囲チェック)
                                                  '【スレッドタイトル】'  ,   // エラーメッセージタイトル
                                                  '0'             ,   // 文字種チェック無し
                                                  ErrorMessage    );  // エラーメッセージ

  // 使用メールアドレス
  ErrorMessage = ErrorMessage + InputCheckExpand( Mail              ,   // 入力データ
                                                  '1'               ,   // 必須チェック(1:必須)
                                                  '1'               ,   // 最小桁数
                                                  '200'             ,   // 最大桁数
                                                  '0'               ,   // 範囲・固定長チェック(0:範囲チェック)
                                                  '【使用メールアドレス】'  ,   // エラーメッセージタイトル
                                                  '6'               ,   // チェック文字種タイプ(メールアドレス)
                                                  ErrorMessage      );  // エラーメッセージ

  // 説明
  ErrorMessage = ErrorMessage + InputCheckExpand( Note            ,   // 入力データ
                                                  '1'             ,   // 必須チェック(1:必須)
                                                  '1'             ,   // 最小桁数
                                                  '10000'         ,   // 最大桁数
                                                  '0'             ,   // 範囲・固定長チェック(0:範囲チェック)
                                                  '【説明】'      ,   // エラーメッセージタイトル
                                                  '0'             ,   // 文字種チェック無し
                                                  ErrorMessage    );  // エラーメッセージ

    // 公開レベル選択の場合は、１つ以上グループ選択していないとエラー
    if( true == document.thread_form.del_flag[1].checked )
    {
        var GroupCheck = false;
        // グループ数ループ
        for( i = 0; i < document.thread_form.elements['select_group[]'].length; i++ )
        {
            if( document.thread_form.elements['select_group[]'][i].checked )
            {
                GroupCheck = true;
                break;
            }
        }

        if( true != GroupCheck )
        {
            if( 0 < ErrorMessage.length )
            {
                ErrorMessage = ErrorMessage + "¥n";
            }
            ErrorMessage = ErrorMessage + "【公開ユーザグループ】状態が「公開レベル選択」の場合は、公開ユーザグループを１つ以上選択してください。";
        }
    }

  // エラーがあった場合は表示して終了
  if( ErrorMessage.length > 0 )
  {
    alert( ErrorMessage );
    return;
  }
  else
  {
    document.thread_form.submit();
  }

}


/* ***************************************************************************
 *  function名  : isIE
 *  機能概要    : InternetExplorerの場合、trueを返却.
 *                
 **************************************************************************** */
function isIE() {
  var ret = false;
  if(navigator.appName=="Microsoft Internet Explorer" || navigator.userAgent.indexOf("MSIE")!=-1){
    ret = true;
  }
  return ret
}
