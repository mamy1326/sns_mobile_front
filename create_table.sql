CREATE TABLE login_session (
  login_id varchar(20) NOT NULL COMMENT 'ログインID',
  session_id varchar(50) NOT NULL default '' COMMENT 'セッションID(ログインID+PW+ログイン年月日時分秒)のMD5',
  last_access_time datetime NOT NULL default '0000-00-00 00:00:00' COMMENT '最終アクセス日時',
  guid varchar(50) default NULL COMMENT '携帯の端末ID',
  ua varchar(100) default NULL COMMENT '携帯の機種等',
  FOREIGN KEY(login_id) REFERENCES users(login_id),
  PRIMARY KEY(login_id)
) COLLATE utf8mb4_bin COMMENT='ログインセッション管理テーブル';

CREATE TABLE users (
  user_id bigint unsigned NOT NULL auto_increment COMMENT 'ユーザID',
  user_name varchar(50) NOT NULL COMMENT 'ユーザ名(漢字)',
  user_name_kana varchar(50) NOT NULL COMMENT 'ユーザ名(カナ)',
  login_id varchar(20) NOT NULL COMMENT 'ログインID',
  password varchar(20) NOT NULL COMMENT 'パスワード',
  mail_address_pc varchar(200) default NULL COMMENT 'PCメールアドレス',
  mail_address_mobile varchar(200) default NULL COMMENT '携帯メールアドレス',
  bbs_write_mailaddress text COMMENT 'BBS投稿用メールアドレス(複数の場合は半角カンマ区切り)',
  executive int(2) NOT NULL default '0' COMMENT '役職コード',
  manage_auth tinyint(1) NOT NULL default '0' COMMENT '管理画面のログイン権限有無(0:無し、1:有り)',
  last_login_time datetime default NULL COMMENT '最終ログイン時間',
  mobile_id varchar(100) default NULL COMMENT 'かんたんログイン用端末ID',
  notes text COMMENT '備考',
  user_agent varchar(255) default NULL COMMENT 'ユーザエージェント(モバイルのみ)',
  mobile_model varchar(50) default NULL COMMENT '携帯モデル',
  create_date datetime NOT NULL default '0000-00-00 00:00:00' COMMENT '作成日',
  update_date datetime default NULL COMMENT '更新日',
  del_flag tinyint(1) NOT NULL default '0' COMMENT '削除フラグ',
  PRIMARY KEY(user_id),
  UNIQUE KEY login_id (login_id)
) COLLATE utf8mb4_bin COMMENT='ユーザ管理テーブル';

CREATE TABLE user_group (
  group_id bigint unsigned NOT NULL auto_increment COMMENT 'ユーザグループID',
  group_name varchar(100) NOT NULL COMMENT 'ユーザグループ名',
  group_note text COMMENT 'ユーザグループ説明',
  create_date datetime NOT NULL default '0000-00-00 00:00:00' COMMENT '作成日',
  del_flag tinyint(1) NOT NULL default '0' COMMENT '削除フラグ',
  PRIMARY KEY  (group_id)
) COLLATE utf8mb4_bin COMMENT='ユーザグループ管理テーブル';

CREATE TABLE group_users (
  group_id bigint unsigned NOT NULL default '0' COMMENT 'ユーザグループID',
  user_id bigint unsigned NOT NULL default '0' COMMENT 'ユーザID',
  PRIMARY KEY  (group_id,user_id)
) COLLATE utf8mb4_bin COMMENT='ユーザグループに所属するユーザID管理テーブル';

CREATE TABLE access_log (
  login_id varchar(50) NOT NULL COMMENT 'ログインID',
  access_datetime datetime NOT NULL COMMENT 'アクセス日時',
  week_day varchar(10) NOT NULL COMMENT 'アクセス曜日',
  ip_address varchar(50) NOT NULL COMMENT 'IPアドレス',
  remote_host varchar(100) default NULL COMMENT 'リモートホスト',
  host_domain varchar(50) default NULL COMMENT 'ドメイン',
  view_page_url text NOT NULL COMMENT '見たページURL',
  info_id int(8) default NULL COMMENT '管理本部通信ID',
  ua varchar(50) default NULL COMMENT '携帯UA',
  os varchar(50) default NULL COMMENT 'PCのOS',
  ua_org varchar(255) default NULL COMMENT 'UAオリジナル',
  docomo_utn varchar(50) default NULL COMMENT 'docomo番号',
  au_subno varchar(50) default NULL COMMENT 'au番号',
  softbank_no varchar(50) default NULL COMMENT 'SB番号',
  PRIMARY KEY  (login_id,access_datetime)
) COLLATE utf8mb4_bin COMMENT='アクセスログテーブル';

CREATE TABLE status_master (
  status_id int(8) NOT NULL COMMENT 'ステータスID',
  status_flag int(2) NOT NULL COMMENT 'ステータス種別',
  status_name varchar(50) NOT NULL default '' COMMENT 'ステータス名',
  reference varchar(50) default NULL COMMENT 'メモ',
  del_flag tinyint(1) NOT NULL default '0' COMMENT '削除フラグ',
  PRIMARY KEY  (status_id,status_flag)
) COLLATE utf8mb4_bin COMMENT='ステータスマスタ';

INSERT INTO status_master (status_id, status_flag, status_name, reference, del_flag) VALUES
(0, 0, '一般', '役職名', 0),
(0, 1, '代表取締役', '役職名', 0),
(0, 2, '取締役', '役職名', 0),
(0, 3, 'アルバイト', '役職名', 0),
(0, 4, 'パート', '役職名', 0),
(0, 5, '非常勤', '役職名', 0),
(0, 6, 'フリー', '役職名', 0),
(0, 99, 'その他', '役職名', 0),
(1, 0, '有効ユーザ', 'ユーザ状態', 0),
(1, 1, '利用停止ユーザ', 'ユーザ状態', 0),
(2, 0, '管理権限なし', '管理画面へのログイン権限', 0),
(2, 1, '管理権限あり', '管理画面へのログイン権限', 0),
(3, 0, '全体公開', '管理本部通信の公開状態', 0),
(3, 2, '非公開', '管理本部通信の公開状態', 0),
(4, 0, '閲覧していません', '管理本部通信の閲覧状況', 0),
(4, 1, '閲覧済み', '管理本部通信の閲覧状況', 0),
(5, 0, '利用中', '一般的な有効ステータス', 0),
(5, 1, '利用停止', '一般的な有効ステータス', 0),
(3, 1, '公開レベル選択', '管理本部通信の公開状態', 0),
(6, 0, '失敗', 'メール送信結果', 0),
(6, 1, '成功', 'メール送信結果', 0),
(7, 0, 'メール送信機能', 'メール送信機能種別', 0),
(7, 1, '管理本部通信', 'メール送信機能種別', 0),
(7, 2, '管理本部通信未閲覧者', 'メール送信機能種別', 0),
(7, 3, 'フォトアルバム', 'メール送信機能種別', 0),
(7, 4, 'ＢＢＳスレッド', 'メール送信機能種別', 0);

CREATE TABLE information (
  info_id bigint unsigned NOT NULL auto_increment COMMENT 'informationのID',
  info_date_yyyy varchar(4) NOT NULL default '' COMMENT '表示年',
  info_date_mm varchar(2) NOT NULL default '' COMMENT '表示月',
  info_date_dd varchar(2) NOT NULL default '' COMMENT '表示日',
  info_title varchar(100) NOT NULL default '' COMMENT 'タイトル',
  info_body text NOT NULL COMMENT '本文',
  info_interval_from datetime default NULL COMMENT '公開期間FROM',
  info_interval_to datetime default NULL COMMENT '公開期間TO',
  del_flag tinyint(1) NOT NULL default '0' COMMENT '表示・非表示フラグ',
  create_date datetime NOT NULL default '0000-00-00 00:00:00' COMMENT '作成日',
  create_user varchar(20) NOT NULL default '' COMMENT '作成者',
  update_date datetime default NULL COMMENT '更新日',
  PRIMARY KEY  (info_id)
) COLLATE utf8mb4_bin COMMENT='管理本部通信テーブル';

CREATE TABLE information_user (
  info_id bigint unsigned NOT NULL COMMENT '管理本部通信ID',
  user_or_group_id bigint unsigned NOT NULL default '0' COMMENT 'ユーザ or ユーザグループID',
  info_type tinyint(1) NOT NULL default '0' COMMENT '0:ユーザ、1:ユーザグループ',
  PRIMARY KEY  (info_id,user_or_group_id,info_type)
) COLLATE utf8mb4_bin COMMENT='管理本部通信で公開するユーザ、ユーザグループ';

CREATE TABLE photo_album (
  album_id bigint unsigned NOT NULL auto_increment COMMENT 'フォトアルバムID',
  album_name varchar(100) NOT NULL COMMENT 'フォトアルバム名',
  album_comment text NOT NULL COMMENT 'フォトアルバム解説',
  create_date datetime NOT NULL default '0000-00-00 00:00:00' COMMENT '作成日',
  del_flag tinyint(1) NOT NULL default '0' COMMENT '削除フラグ',
  PRIMARY KEY  (album_id)
) COLLATE utf8mb4_bin COMMENT='管理本部通信に使用するフォトアルバムのテーブル';

CREATE TABLE photo_album_user (
  album_id bigint unsigned NOT NULL COMMENT 'フォトアルバムID',
  user_or_group_id bigint unsigned NOT NULL default '0' COMMENT 'ユーザ、またはグループID',
  flag tinyint(1) NOT NULL default '0' COMMENT '0:ユーザ、1:グループ',
  FOREIGN KEY(album_id) REFERENCES photo_album(album_id),
  PRIMARY KEY  (album_id,user_or_group_id)
) COLLATE utf8mb4_bin COMMENT='フォトアルバム公開ユーザ設定テーブル';

CREATE TABLE information_image (
  album_id bigint unsigned NOT NULL COMMENT '属するフォトアルバムID',
  image_name varchar(50) NOT NULL COMMENT '保存されている画像名',
  image_name_sumbnail varchar(50) NOT NULL COMMENT 'サムネイル画像名(拡張子つき)',
  image_name_sumbnail_ss varchar(50) NOT NULL COMMENT 'モバイル一覧表示用最小サイズ画像名',
  use_information_id text COMMENT '画像が使用されている記事ID',
  comment text COMMENT '画像に対するコメント',
  del_flag tinyint(1) NOT NULL default '0' COMMENT '使用可能・不可能フラグ',
  create_date datetime NOT NULL default '0000-00-00 00:00:00' COMMENT '作成日',
  create_user varchar(20) NOT NULL default '' COMMENT '作成者',
  PRIMARY KEY  (album_id,image_name)
) COLLATE utf8mb4_bin COMMENT='管理本部通信で使用する画像管理テーブル';

CREATE TABLE manage_mail (
  mail_id bigint unsigned NOT NULL auto_increment COMMENT 'メールID',
  send_date datetime NOT NULL default '0000-00-00 00:00:00' COMMENT '送信日時',
  send_flag int(2) NOT NULL default '0' COMMENT '送信機能(0:メール送信機能、1:管理本部通信、2:管理本部通信未閲覧者、3:フォトアルバム、4:BBS)',
  mail_from varchar(200) NOT NULL default '' COMMENT '差出人アドレス',
  subject varchar(200) NOT NULL default '' COMMENT 'サブジェクト',
  body text NOT NULL COMMENT '本文',
  PRIMARY KEY  (mail_id)
) COLLATE utf8mb4_bin COMMENT='管理画面からの送信メール履歴テーブル';

CREATE TABLE mail_send_list (
  mail_id bigint unsigned NOT NULL COMMENT 'メールID',
  user_id bigint unsigned NOT NULL COMMENT '送信先ユーザID',
  mail_address varchar(200) NOT NULL COMMENT '送信アドレス',
  result tinyint(1) NOT NULL default '0' COMMENT 'メール送信結果',
  PRIMARY KEY  (mail_id,user_id)
) COLLATE utf8mb4_bin COMMENT='管理画面からのメール送信者リストテーブル';

CREATE TABLE thread_master (
  thread_id bigint unsigned NOT NULL auto_increment COMMENT 'スレッドID',
  thread_mailaddress varchar(200) NOT NULL COMMENT 'スレッド投稿用専用メールアドレス',
  thread_name varchar(200) NOT NULL COMMENT 'スレッド名',
  thread_note text COMMENT 'スレッド説明',
  create_date datetime NOT NULL default '0000-00-00 00:00:00' COMMENT '作成日',
  del_flag tinyint(1) NOT NULL default '0' COMMENT '0:全体公開、1:公開グループ指定、2:非公開',
  PRIMARY KEY  (thread_id)
) COLLATE utf8mb4_bin COMMENT='ＢＢＳスレッドマスタテーブル';

CREATE TABLE thread_user (
  thread_id bigint unsigned NOT NULL default '0' COMMENT 'スレッドID',
  user_or_group_id bigint unsigned NOT NULL default '0' COMMENT '公開するユーザ、またはグループID',
  flag tinyint(1) NOT NULL default '0' COMMENT '0:ユーザ、1:グループ',
  PRIMARY KEY  (thread_id,user_or_group_id)
) COLLATE utf8mb4_bin COMMENT='ＢＢＳスレッド公開ユーザ・グループ管理テ';

CREATE TABLE bbs_posted (
  thread_id bigint unsigned NOT NULL COMMENT 'スレッドID',
  posted_datetime datetime NOT NULL default '0000-00-00 00:00:00' COMMENT '投稿日付',
  mail_address varchar(200) NOT NULL COMMENT '投稿者メールアドレス',
  login_id varchar(20) NOT NULL COMMENT '投稿者ログインID',
  title varchar(100) default NULL COMMENT 'タイトル',
  body text COMMENT '本文',
  image_name varchar(50) default NULL COMMENT '添付画像名',
  del_flag tinyint(1) NOT NULL default '0' COMMENT '削除フラグ',
  PRIMARY KEY  (thread_id,posted_datetime,mail_address,login_id)
) COLLATE utf8mb4_bin COMMENT='BBSマスタテーブル';
