# 概要
モバイル版SNSです。
(フロント部分、mixiモバイルと同等の機能)

## 補足事項
- ガラケーでのsns開発、フロントのソース一式（mixiと同等の機能）
- 管理画面は開発当時の社名が多く入っているため、pushしていません

# 開発期間
2週間

# 機能概要

- top
    - /m/index.php からログイン認証し、/m/top.php
    - インフォメーション、フォトアルバム、BBS、簡単ログイン設定、各種通知メール設定のメニューがあります
- インフォメーション
    - 管理画面から発信したお知らせが、TOPに最大5件で表示されます
    - ユーザーごと、ユーザーグループごとに発信対象を設定できます
    - /m/info/info_all.php で全件、info_detail.php で1つの詳細を表示します
- フォトアルバム
    - mixiのフォトアルバムと同じ機能で、TOPに最大5件表示されます
    - 公開対象をユーザーごと、ユーザーグループごとに設定できます
    - フォトアルバムにたいし、投稿専用メールアドレスにsubject, body, 添付画像で投稿可能です
    - album_all.php で全件、album_detail.php でアルバム個別の画像が見れます
- BBS
    - mixiの日記と同じ機能で、TOPに最大5件表示されます
    - ただし日記にカテゴリー（スレッド）と言う概念があり、スレッドに対して日記を投稿するイメージです
    - 公開対象をユーザーごと、ユーザーグループごとに設定できます
    - BBSは、投稿専用メールアドレスにsubject, body, 添付画像で投稿可能です
    - thread_all.php で全件、/m/mailbbs_gd/mailbbs_i.php で個別の内容（画像付き）が見れます
- 更新メール受信設定
    - インフォメーション、フォトアルバム、スレッドへの新規投稿があった際、メール通知が受け取れる設定ができます
    - /m/mypage/mail_receive_input.php で一覧確認、設定入力を行います
- かんたんログイン設定
    - 端末IDからボタン1つでかんたんログインする設定を許可・解除できます
    - 一度でもID・PW認証が通っていれば、保存された端末IDを利用して設定が可能です
