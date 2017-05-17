<?php
/* ================================================================================
 * ファイル名   ：LearningDB.php
 * タイトル     ：イーラーニング情報取得・更新用クラス
 * 作成者       ：間宮 直樹
 * 作成日       ：2009/06/17
 * 内容         ：イーラーニング情報の取得・更新処理を実施する。
 * 更新履歴*******************************************************************
 * 【変更日】  【変更者】      【変更箇所】        【変更理由と変更内容】
 * ================================================================================*/

include_once( '../../_config/std_conf.php' );

// ライブラリ・コンフィグファイルをinclude
include_once( LIB_DIR    . 'DataConvert.php' );         // データ変換用関数群
include_once( LIB_DIR    . 'WrapDB.php' );              // ＤＢ用関数群
include_once( LIB_DIR    . 'SQLAid.php' );              // ＳＱＬ条件文関数群
include_once( CONFIG_DIR . 'db_conf.php' );             // データベース定義ファイル

class LearningDB
{
    // ***********************************************************************
    // * 関数名     ：問題登録処理
    // * 返り値     ：成功:true、失敗:false
    // ***********************************************************************
    function InsertQuestion(    $strQuestionTitle   ,   // 問題文
                                $strDelFlag         ,
                                $intAnswerID        ,   // 答えの番号
                                $arrayAnswerBody    ,   // 答えの文章
                                $strDateTime = null  )
    {
        $intRetCode = true;

        // ＤＢ接続
        $db = new WrapDB();
        $db->ConnectDB();

        //トランザクション開始
        $db->AutoCommitOffDB();

        // エラー中断用のループ。while(false)で必ず抜ける。
        do
        {
            // 登録番号付きの場合、最新の登録番号を取得する

            // ********************
            // 最初に問題を登録する
            // ********************

            // INSERTする。答えの番号はこれから登録するので、答えを登録し終わってから更新する。
            $strSQL =   " INSERT INTO learning_question_master " .
                        " (                         " .
                        "   question_body       ,   " .
                        "   question_answer_id  ,   " .
                        "   delete_flag         ,   " .
                        "   regist_date             " .
                        " )                         " .
                        " VALUES                    " .
                        " (                         " .
                        "    " . SQLAid::escapeStr( $strQuestionTitle   ) . ", " .
                        "    " . SQLAid::escapeNum( $intAnswerID        ) . ", " .
                        "    " . SQLAid::escapeNum( $strDelFlag         ) . ", " .
                        "    " . SQLAid::escapeStr( $strDateTime        ) .
                        ")                          " ;
//print("<!--".$strSQL."-->¥n");

            // SQL実行
            $objQuestion = $db->QueryDB( $strSQL );

            // 失敗したら処理中断
            if( true != $objQuestion )
            {
                //失敗
                $intRetCode = false;
                break;
            }

            // 登録した問題IDを取得
            $intQuestionID = mysql_insert_id();

            // ********************
            // 次に答えを登録する
            // ********************
            for( $intAnswerCount = 0; $intAnswerCount < count( $arrayAnswerBody ); $intAnswerCount++ )
            {
                $intAnswerID    = $intAnswerCount+1;
                $strAnswerBody  = $arrayAnswerBody[ $intAnswerCount ];

                // ****************
                // 回答をINSERTする
                // ****************
                $strSQL =   " INSERT INTO learning_answer_master " .
                            " (                         " .
                            "   answer_id           ,   " .
                            "   question_id         ,   " .
                            "   answer_body         ,   " .
                            "   delete_flag             " .
                            " )                         " .
                            " VALUES                    " .
                            " (                         " .
                            "    " . SQLAid::escapeNum( $intAnswerID    ) . ", " .
                            "    " . SQLAid::escapeNum( $intQuestionID  ) . ", " .
                            "    " . SQLAid::escapeStr( $strAnswerBody  ) . ", " .
                            "  0                                               " .
                            ")                          " ;
//print("<!--".$strSQL."-->¥n");
                // SQL実行
                $objAnswer = $db->QueryDB( $strSQL );

                // エラーの場合
                if( false == $objAnswer )
                {
                    $intRetCode = false;
                    break;
                }

            }// 回答ループ

            // エラーの場合
            if( true != $intRetCode )
            {
                break;
            }

            break;

        }while( false );


        // 結果成功したらCOMMIT、失敗ならROLLBACK
        if( false != $intRetCode )
        {
            //成功
            $db->TransactDB( DB_COM_COMMIT );
            $db->CloseDB();
        }
        else
        {
            //失敗
            $db->TransactDB( DB_COM_ROLLBACK );
            $db->CloseDB();
        }

        return $intRetCode;
    }

    // ***********************************************************************
    // * 関数名     ：問題登録処理
    // * 返り値     ：成功:true、失敗:false
    // ***********************************************************************
    function UpdateQuestion(    $intQuestionID      ,   // 問題ID
                                $strQuestionTitle   ,   // 問題文
                                $strDelFlag         ,
                                $intAnswerID        ,   // 答えの番号
                                $arrayAnswerID      ,   // 選択肢の文章
                                $arrayAnswerBody    )   // 選択肢の文章
    {
        $intRetCode = true;

        // ＤＢ接続
        $db = new WrapDB();
        $db->ConnectDB();

        //トランザクション開始
        $db->AutoCommitOffDB();

        // エラー中断用のループ。while(false)で必ず抜ける。
        do
        {
            // ********************
            // 最初に問題をUPDATEする
            // ********************

            // UPDATEする。答えの番号はこれから登録するので、答えを登録し終わってから更新する。
            $strSQL =   " UPDATE                    " .
                        "   learning_question_master " .
                        " SET                       " .
                        "   question_answer_id  =   " . SQLAid::escapeNum( $intAnswerID         ) . " , " .
                        "   question_body       =   " . SQLAid::escapeStr( $strQuestionTitle    ) .
                        " WHERE                     " .
                        "   question_id         =   " . SQLAid::escapeNum( $intQuestionID       ) ;
print("<!--".$strSQL."-->");

            // SQL実行
            $objQuestion = $db->QueryDB( $strSQL );

            // 失敗したら処理中断
            if( true != $objQuestion )
            {
                //失敗
                $intRetCode = false;
                break;
            }

            // ********************************
            // 現在の選択肢IDの最大値を求める
            // ********************************
            $strSQL =   " SELECT            " .
                        "   answer_id       " .
                        " FROM              " .
                        "   learning_answer_master " .
                        " WHERE question_id = " . SQLAid::escapeNum( $intQuestionID ) .
                        " ORDER BY answer_id DESC " .
                        " LIMIT 0, 1        " ;
            // SQL実行
            $objAnswerMax = $db->QueryDB( $strSQL );

            $arrayAnswerMax = $db->FetchArrayDB( $objAnswerMax );
            $intAnswerMax = intval( $arrayAnswerMax["answer_id"] );

            // ********************
            // 次に答えを登録する
            // ********************
            $intQuestionAnswerID = NULL;
            for( $intAnswerCount = 0; $intAnswerCount < count( $arrayAnswerBody ); $intAnswerCount++ )
            {
                $strAnswerID    = $arrayAnswerID[ $intAnswerCount ];
                $strAnswerBody  = $arrayAnswerBody[ $intAnswerCount ];

                // ***************
                // 既存か追加か？
                // ***************

                // SELECT
                $strSQL =   " SELECT            " .
                            "   question_id     " .
                            " FROM              " .
                            "   learning_answer_master " .
                            " WHERE answer_id = " . SQLAid::escapeNum( intval( $strAnswerID ) ) .
                            " AND question_id = " . SQLAid::escapeNum( $intQuestionID ) ;
                // SQL実行
                $objAnswerSelect = $db->QueryDB( $strSQL );

                // 実行結果行数を取得
                $rowsAnswer = $db->GetNumRowsDB( $objAnswerSelect );

                // 無いので追加INSERT
                if( 1 > $rowsAnswer )
                {
                    // 選択肢ID作成
                    $intAnswerMax++;

                    // ****************
                    // 回答をINSERTする
                    // ****************
                    $strSQL =   " INSERT INTO learning_answer_master " .
                                " (                         " .
                                "   answer_id           ,   " .
                                "   question_id         ,   " .
                                "   answer_body         ,   " .
                                "   delete_flag             " .
                                " )                         " .
                                " VALUES                    " .
                                " (                         " .
                                "    " . SQLAid::escapeNum( $intAnswerMax   ) . ", " .
                                "    " . SQLAid::escapeNum( $intQuestionID  ) . ", " .
                                "    " . SQLAid::escapeStr( $strAnswerBody  ) . ", " .
                                "  0                                               " .
                                ")                          " ;
                }
                // あるので更新UPDATE
                else
                {
                    // ****************
                    // 回答をUPDATEする
                    // ****************
                    $strSQL =   " UPDATE                " .
                                "   learning_answer_master " .
                                " SET                   " .
                                "   answer_body     =   " . SQLAid::escapeStr( $strAnswerBody           ) .
                                " WHERE                 " .
                                "   answer_id       =   " . SQLAid::escapeNum( intval( $strAnswerID )   ) .
                                " AND question_id   =   " . SQLAid::escapeNum( $intQuestionID ) ;
                }
print("<!--".$strSQL."-->");
                // SQL実行
                $objAnswer = $db->QueryDB( $strSQL );

                // エラーの場合
                if( false == $objAnswer )
                {
                    $intRetCode = false;
                    break;
                }

                // 答えかどうか？
                if( $intAnswerID == $intAnswerCount && true == is_null( $intQuestionAnswerID ) )
                {
                    if( 1 > $rowsAnswer )
                    {
                        // 登録した問題IDを取得
                        $intQuestionAnswerID = mysql_insert_id();
                    }
                    else
                    {
                        // 更新した問題IDを取得
                        $intQuestionAnswerID = intval( $strAnswerID );
                    }
                }

            }// 回答ループ

            // エラーの場合
            if( true != $intRetCode )
            {
                break;
            }

            break;

        }while( false );


        // 結果成功したらCOMMIT、失敗ならROLLBACK
        if( false != $intRetCode )
        {
            //成功
            $db->TransactDB( DB_COM_COMMIT );
            $db->CloseDB();
        }
        else
        {
            //失敗
            $db->TransactDB( DB_COM_ROLLBACK );
            $db->CloseDB();
        }

        return $intRetCode;
    }

    // ***********************************************************************
    // * 関数名     ：問題取得（全て）
    // * 機能概要   ：指定イーラーニングIDの設問を取得する
    // * 返り値     ：成功:イーラーニング設問配列、失敗:NULL
    // ***********************************************************************
    function GetQuestionAll(    &$intRows       ,
                                $intDeleteFlag = 9 )
    {
        // ＤＢ接続
        $db = new WrapDB();
        $db->ConnectDB();

        //トランザクション開始
        $db->AutoCommitOffDB();

        // 指定のIDのレコードがあるかチェックし、あればUPDATE、無ければINSERT
        $strSQL =   " SELECT                    " .
                    "   question_id         ,   " .
                    "   question_body       ,   " .
                    "   question_answer_id  ,   " .
                    "   regist_date         ,   " .
                    "   delete_flag             " .
                    " FROM                      " .
                    "   learning_question_master " ;

        if( 9 != $intDeleteFlag )
        {
            $strSQL =   $strSQL .
                        " WHERE delete_flag = " . SQLAid::escapeNum( $intDeleteFlag ) ;
        }

        $strSQL =   $strSQL .
                    " ORDER BY regist_date DESC, question_id DESC ";

print("<!--".$strSQL."-->¥n");
        // SQL実行
        $objQuestion = $db->QueryDB( $strSQL );

        // 実行結果行数を取得
        $intRows = $db->GetNumRowsDB( $objQuestion );

        // 取得行を配列化
        $arrayQuestion = array();
        for( $intCount = 0; $intCount < $intRows; $intCount++ )
        {
            $arrayResult = $db->FetchArrayDB( $objQuestion );

            // 取得したデータを配列に格納
            $arrayQuestion[] = array(   'question_id'           => $arrayResult["question_id"]          ,
                                        'question_body'         => $arrayResult["question_body"]        ,
                                        'question_answer_id'    => $arrayResult["question_answer_id"]   ,
                                        'regist_date'           => $arrayResult["regist_date"]          ,
                                        'delete_flag'           => $arrayResult["delete_flag"]          );
        }

        return $arrayQuestion;
    }

    // ***********************************************************************
    // * 関数名     ：問題取得（指定）
    // * 機能概要   ：指定イーラーニングIDの設問を取得する
    // * 返り値     ：成功:イーラーニング設問配列、失敗:NULL
    // ***********************************************************************
    function GetQuestionOne(    &$intRows       ,
                                $strQuestionID  ,
                                $intDeleteFlag = 9 )
    {
        // ＤＢ接続
        $db = new WrapDB();
        $db->ConnectDB();

        //トランザクション開始
        $db->AutoCommitOffDB();

        // 指定のIDのレコードがあるかチェックし、あればUPDATE、無ければINSERT
        $strSQL =   " SELECT                    " .
                    "   question_id         ,   " .
                    "   question_body       ,   " .
                    "   question_answer_id  ,   " .
                    "   delete_flag             " .
                    " FROM                      " .
                    "   learning_question_master " .
                    " WHERE  question_id =      " . SQLAid::escapeNum( intval( $strQuestionID ) );

        if( 9 != $intDeleteFlag )
        {
            $strSQL =   $strSQL .
                        " AND    delete_flag = " . SQLAid::escapeNum( $intDeleteFlag ) ;
        }
print("<!--".$strSQL."-->¥n");
        // SQL実行
        $objQuestion = $db->QueryDB( $strSQL );

        // 実行結果行数を取得
        $intRows = $db->GetNumRowsDB( $objQuestion );

        // 取得行を配列化
        if( 0 < $intRows )
        {
            $arrayResult = $db->FetchArrayDB( $objQuestion );

            // 取得したデータを配列に格納
            $arrayQuestion   = array(   'question_id'           => $arrayResult["question_id"]          ,
                                        'question_body'         => $arrayResult["question_body"]        ,
                                        'question_answer_id'    => $arrayResult["question_answer_id"]   ,
                                        'delete_flag'           => $arrayResult["delete_flag"]          );
        }
        else
        {
            $arrayQuestion = NULL;
        }

        return $arrayQuestion;
    }

    // ***********************************************************************
    // * 関数名     ：問題に対する全ての答え取得（問題ID指定）
    // * 機能概要   ：指定イーラーニングIDの回答を取得する
    // * 返り値     ：成功:イーラーニング回答配列、失敗:NULL
    // ***********************************************************************
    function GetAnswerAllOfQuestion(    &$intRows       ,
                                        $strQuestionID  )
    {
        // ＤＢ接続
        $db = new WrapDB();
        $db->ConnectDB();

        //トランザクション開始
        $db->AutoCommitOffDB();

        $strSQL =   " SELECT            " .
                    "   answer_id   ,   " .
                    "   question_id ,   " .
                    "   answer_body ,   " .
                    "   delete_flag     " .
                    " FROM              " .
                    "   learning_answer_master  " .
                    " WHERE question_id =       " . SQLAid::escapeNum( intval( $strQuestionID ) ) .
                    " ORDER BY question_id DESC," .
                    "   answer_id ASC           " ;
print("<!--".$strSQL."-->");
        // SQL実行
        $objAnswer = $db->QueryDB( $strSQL );

        // 実行結果行数を取得
        $intRows = $db->GetNumRowsDB( $objAnswer );

        // 取得行を配列化
        $arrayAnswer = array();
        for( $intCount = 0; $intCount < $intRows; $intCount++ )
        {
            $arrayResult = $db->FetchArrayDB( $objAnswer );

            // 取得したデータを配列に格納
            $arrayAnswer[] = array( 'answer_id'     => $arrayResult["answer_id"]    ,
                                    'question_id'   => $arrayResult["question_id"]  ,
                                    'answer_body'   => $arrayResult["answer_body"]  ,
                                    'delete_flag'   => $arrayResult["delete_flag"]  );
        }

        return $arrayAnswer;
    }


    // ***********************************************************************
    // * 関数名     ：問題の答えの正解取得（問題ID指定）
    // * 機能概要   ：指定イーラーニングIDの回答を取得する
    // * 返り値     ：成功:イーラーニング回答配列、失敗:NULL
    // ***********************************************************************
    function GetAnswerOfQuestion(   &$intRows       ,
                                    $strQuestionID  )
    {
        // ＤＢ接続
        $db = new WrapDB();
        $db->ConnectDB();

        //トランザクション開始
        $db->AutoCommitOffDB();

        $strSQL =   " SELECT                            " .
                    "   question.question_id        ,   " .
                    "   question.question_body      ,   " .
                    "   question.question_answer_id ,   " .
                    "   question.delete_flag            " .
                    " FROM                      " .
                    "   learning_question_master question " .
                    " INNER JOIN learning_answer_master answer " .
                    "   ON question.question_answer_id = answer.answer_id " .
                    " WHERE  question.question_id =      " . SQLAid::escapeNum( intval( $strQuestionID ) );
print("<!--".$strSQL."-->¥n");
        // SQL実行
        $objQuestion = $db->QueryDB( $strSQL );

        // 実行結果行数を取得
        $intRows = $db->GetNumRowsDB( $objQuestion );

        // 取得行を配列化
        if( 0 < $intRows )
        {
            $arrayResult = $db->FetchArrayDB( $objQuestion );

            // 取得したデータを配列に格納
            $arrayQuestion   = array(   'question_answer_id'    => $arrayResult["question_answer_id"]   ,
                                        'question_id'           => $arrayResult["question_id"]          ,
                                        'question_body'         => $arrayResult["question_body"]        ,
                                        'delete_flag'           => $arrayResult["delete_flag"]          );
        }
        else
        {
            $arrayQuestion = NULL;
        }

        return $arrayQuestion;
    }

    // ***********************************************************************
    // * 関数名     ：問題集登録処理
    // * 返り値     ：成功:true、失敗:false
    // ***********************************************************************
    function InsertCategory(    $strCategoryTitle   ,   // タイトル
                                $strCategoryBody    ,   // 説明文
                                $strDelFlag         ,
                                $arrayQuestionID    )   // 問題集にする個々の問題のID配列
    {
        $intRetCode = true;

        // ＤＢ接続
        $db = new WrapDB();
        $db->ConnectDB();

        //トランザクション開始
        $db->AutoCommitOffDB();

        // エラー中断用のループ。while(false)で必ず抜ける。
        do
        {
            // ********************
            // 最初に問題を登録する
            // ********************

            // INSERT
            $strSQL =   " INSERT INTO learning_category_master " .
                        " (                         " .
                        "   category_title      ,   " .
                        "   category_body       ,   " .
                        "   delete_flag             " .
                        " )                         " .
                        " VALUES                    " .
                        " (                         " .
                        "    " . SQLAid::escapeStr( $strCategoryTitle   ) . ", " .
                        "    " . SQLAid::escapeStr( $strCategoryBody    ) . ", " .
                        "    " . SQLAid::escapeNum( $strDelFlag         ) .
                        ")                          " ;
print("<!--".$strSQL."-->¥n");

            // SQL実行
            $objCategory = $db->QueryDB( $strSQL );

            // 失敗したら処理中断
            if( true != $objCategory )
            {
                //失敗
                $intRetCode = false;
                break;
            }

            // 登録した問題集IDを取得
            $intCategoryID = mysql_insert_id();

            // ********************
            // 次に問題を登録する
            // ********************
            for( $intCount = 0; $intCount < count( $arrayQuestionID ); $intCount++ )
            {
                $intQuestionID  = intval( $arrayQuestionID[ $intCount ] );

                // ****************
                // 問題をINSERTする
                // ****************
                $strSQL =   " INSERT INTO learning_category_questions_master " .
                            " (                         " .
                            "   category_id         ,   " .
                            "   question_id             " .
                            " )                         " .
                            " VALUES                    " .
                            " (                         " .
                            "    " . SQLAid::escapeNum( $intCategoryID    ) . ", " .
                            "    " . SQLAid::escapeNum( $intQuestionID  ) .
                            ")                          " ;
print("<!--".$strSQL."-->¥n");
                // SQL実行
                $objCategory = $db->QueryDB( $strSQL );

                // エラーの場合
                if( false == $objCategory )
                {
                    $intRetCode = false;
                    break;
                }

            }// 回答ループ

            // エラーの場合
            if( true != $intRetCode )
            {
                break;
            }

            break;

        }while( false );


        // 結果成功したらCOMMIT、失敗ならROLLBACK
        if( false != $intRetCode )
        {
            //成功
            $db->TransactDB( DB_COM_COMMIT );
            $db->CloseDB();
        }
        else
        {
            //失敗
            $db->TransactDB( DB_COM_ROLLBACK );
            $db->CloseDB();
        }

        return $intRetCode;
    }

    // ***********************************************************************
    // * 関数名     ：問題集登録更新処理
    // * 返り値     ：成功:true、失敗:false
    // ***********************************************************************
    function UpdateCategory(    $intCategoryID      ,   // 問題集ID
                                $strCategoryTitle   ,   // タイトル
                                $strCategoryBody    ,   // 問題集説明文
                                $strDelFlag         ,
                                $arrayQuestionID    )   // 登録する問題のID配列
    {
        $intRetCode = true;

        // ＤＢ接続
        $db = new WrapDB();
        $db->ConnectDB();

        //トランザクション開始
        $db->AutoCommitOffDB();

        // エラー中断用のループ。while(false)で必ず抜ける。
        do
        {
            // ********************
            // 最初に問題集をUPDATEする
            // ********************

            // UPDATE
            $strSQL =   " UPDATE                    " .
                        "   learning_category_master " .
                        " SET                       " .
                        "   category_title      =   " . SQLAid::escapeStr( $strCategoryTitle    ) . " , " .
                        "   category_body       =   " . SQLAid::escapeStr( $strCategoryBody     ) . " , " .
                        "   delete_flag         =   " . SQLAid::escapeNum( $strDelFlag          ) .
                        " WHERE                     " .
                        "   category_id         =   " . SQLAid::escapeNum( $intCategoryID       ) ;
print("<!--".$strSQL."-->");

            // SQL実行
            $objCategory = $db->QueryDB( $strSQL );

            // 失敗したら処理中断
            if( true != $objCategory )
            {
                //失敗
                $intRetCode = false;
                break;
            }

            // ********************
            // 次に問題を登録する
            // ********************
            for( $intCount = 0; $intCount < count( $arrayQuestionID ); $intCount++ )
            {
                $intQuestionID    = intval( $arrayQuestionID[ $intCount ] );

                // ***************
                // 既存か追加か？
                // ***************

                // SELECT
                $strSQL =   " SELECT                " .
                            "   category_id         " .
                            " FROM                  " .
                            "   learning_category_questions_master " .
                            " WHERE category_id =   " . SQLAid::escapeNum( $intCategoryID   ) .
                            " AND question_id   =   " . SQLAid::escapeNum( $intQuestionID   ) ;
                // SQL実行
                $objQuestion = $db->QueryDB( $strSQL );

                // 実行結果行数を取得
                $rowsQuestion = $db->GetNumRowsDB( $objQuestion );

                // 無いので追加INSERT
                if( 1 > $rowsQuestion )
                {
                    // ****************
                    // 問題をINSERTする
                    // ****************
                    $strSQL =   " INSERT INTO learning_category_questions_master " .
                                " (                         " .
                                "   category_id         ,   " .
                                "   question_id             " .
                                " )                         " .
                                " VALUES                    " .
                                " (                         " .
                                "    " . SQLAid::escapeNum( $intCategoryID  ) . ", " .
                                "    " . SQLAid::escapeNum( $intQuestionID  ) .
                                ")                          " ;
                }
                // ある場合はそのまま
                else
                {
                    ;
                }
print("<!--".$strSQL."-->");
                // SQL実行
                $objQuestion = $db->QueryDB( $strSQL );

                // エラーの場合
                if( false == $objQuestion )
                {
                    $intRetCode = false;
                    break;
                }

            }// 問題ループ

            // エラーの場合
            if( true != $intRetCode )
            {
                break;
            }

            break;

        }while( false );


        // 結果成功したらCOMMIT、失敗ならROLLBACK
        if( false != $intRetCode )
        {
            //成功
            $db->TransactDB( DB_COM_COMMIT );
            $db->CloseDB();
        }
        else
        {
            //失敗
            $db->TransactDB( DB_COM_ROLLBACK );
            $db->CloseDB();
        }

        return $intRetCode;
    }

    // ***********************************************************************
    // * 関数名     ：問題集取得（全て）
    // ***********************************************************************
    function GetCategoryAll(    &$intRows       ,
                                $intDeleteFlag = 9 )
    {
        // ＤＢ接続
        $db = new WrapDB();
        $db->ConnectDB();

        //トランザクション開始
        $db->AutoCommitOffDB();

        // 指定のIDのレコードがあるかチェックし、あればUPDATE、無ければINSERT
        $strSQL =   " SELECT                    " .
                    "   cate.category_id    ,   " .
                    "   cate.category_title ,   " .
                    "   cate.category_body  ,   " .
                    "   ug.period_date      ,   " .
                    "   ug.group_id         ,   " .
                    "   grp.group_name      ,   " .
                    "   cate.delete_flag        " .
                    " FROM                      " .
                    "   learning_category_master cate " .
                    " LEFT JOIN learning_user_group_master ug " .
                    "   ON cate.category_id = ug.category_id  " .
                    " LEFT JOIN user_group grp " .
                    "   ON ug.group_id = grp.group_id  " ;

        if( 9 != $intDeleteFlag )
        {
            $strSQL =   $strSQL .
                        " WHERE cate.delete_flag = " . SQLAid::escapeNum( $intDeleteFlag ) ;
        }

        $strSQL =   $strSQL .
                    " ORDER BY cate.category_id DESC ";

//print("<!--".$strSQL."-->¥n");
        // SQL実行
        $objCategory = $db->QueryDB( $strSQL );

        // 実行結果行数を取得
        $intRows = $db->GetNumRowsDB( $objCategory );

        // 取得行を配列化
        $arrayCategory = array();
        for( $intCount = 0; $intCount < $intRows; $intCount++ )
        {
            $arrayResult = $db->FetchArrayDB( $objCategory );

            // 取得したデータを配列に格納
            $arrayCategory[] = array(   'category_id'           => $arrayResult["category_id"]          ,
                                        'category_title'        => $arrayResult["category_title"]       ,
                                        'category_body'         => $arrayResult["category_body"]        ,
                                        'period_date'           => $arrayResult["period_date"]          ,
                                        'group_id'              => $arrayResult["group_id"]             ,
                                        'group_name'            => $arrayResult["group_name"]           ,
                                        'delete_flag'           => $arrayResult["delete_flag"]          );
        }

        return $arrayCategory;
    }

    // ***********************************************************************
    // * 関数名     ：問題集取得（指定）
    // ***********************************************************************
    function GetCategoryOne(    &$intRows       ,
                                $strCategoryID  ,
                                $intDeleteFlag = 9,
                                $strGroupID = "" )
    {
        // ＤＢ接続
        $db = new WrapDB();
        $db->ConnectDB();

        //トランザクション開始
        $db->AutoCommitOffDB();

        $strSQL =   " SELECT                    " .
                    "   cate.category_id         ,   " .
                    "   cate.category_title      ,   " .
                    "   cate.category_body       ,   " .
                    "   ug.period_date           ,   " .
                    "   cate.delete_flag             " .
                    " FROM                      " .
                    "   learning_category_master cate " .
                    " LEFT JOIN learning_user_group_master ug " .
                    "   ON cate.category_id = ug.category_id  " .
                    " WHERE  cate.category_id =      " . SQLAid::escapeNum( intval( $strCategoryID  ) ) ;

        if( 9 != $intDeleteFlag )
        {
            $strSQL =   $strSQL .
                        " AND    cate.delete_flag = " . SQLAid::escapeNum( $intDeleteFlag ) ;
        }

        if( 0 < strlen( $strGroupID ) )
        {
            $strSQL =   $strSQL .
                        " AND    ug.group_id      = " . SQLAid::escapeNum( intval( $strGroupID ) ) ;

        }
print("<!--".$strSQL."-->¥n");

        // SQL実行
        $objCategory = $db->QueryDB( $strSQL );

        // 実行結果行数を取得
        $intRows = $db->GetNumRowsDB( $objCategory );

        // 取得行を配列化
        if( 0 < $intRows )
        {
            $arrayResult = $db->FetchArrayDB( $objCategory );

            // 取得したデータを配列に格納
            $arrayQuestion   = array(   'category_id'           => $arrayResult["category_id"]          ,
                                        'category_title'        => $arrayResult["category_title"]       ,
                                        'category_body'         => $arrayResult["category_body"]        ,
                                        'period_date'           => $arrayResult["period_date"]          ,
                                        'delete_flag'           => $arrayResult["delete_flag"]          );
        }
        else
        {
            $arrayQuestion = NULL;
        }

        return $arrayQuestion;
    }

    // ***********************************************************************
    // * 関数名     ：問題集の問題取得（全て）
    // ***********************************************************************
    function GetCategoryQuestionAll(    &$intRows       ,
                                        $strCategoryID  ,
                                        $intDeleteFlag = 9 )
    {
        // ＤＢ接続
        $db = new WrapDB();
        $db->ConnectDB();

        //トランザクション開始
        $db->AutoCommitOffDB();

        // 指定のIDのレコードがあるかチェックし、あればUPDATE、無ければINSERT
        $strSQL =   " SELECT                    " .
                    "   cq.category_id      ,   " .
                    "   cq.question_id      ,   " .
                    "   q.question_body     ,   " .
                    "   q.delete_flag           " .
                    " FROM                      " .
                    "   learning_category_questions_master cq   " .
                    " INNER JOIN learning_question_master q     " .
                    "   ON cq.question_id = q.question_id       " .
                    " WHERE cq.category_id =    " . SQLAid::escapeNum( $strCategoryID );

        if( 9 != $intDeleteFlag )
        {
            $strSQL =   $strSQL .
                        " AND q.delete_flag = " . SQLAid::escapeNum( $intDeleteFlag ) ;
        }

        $strSQL =   $strSQL .
                    " ORDER BY cq.category_id DESC , cq.question_id ASC ";

print("<!--".$strSQL."-->¥n");
        // SQL実行
        $objCategory = $db->QueryDB( $strSQL );

        // 実行結果行数を取得
        $intRows = $db->GetNumRowsDB( $objCategory );

        // 取得行を配列化
        $arrayCategory = array();
        for( $intCount = 0; $intCount < $intRows; $intCount++ )
        {
            $arrayResult = $db->FetchArrayDB( $objCategory );

            // 取得したデータを配列に格納
            $arrayCategory[] = array(   'category_id'           => $arrayResult["category_id"]          ,
                                        'question_id'           => $arrayResult["question_id"]          ,
                                        'question_body'         => $arrayResult["question_body"]        ,
                                        'delete_flag'           => $arrayResult["delete_flag"]          );
        }

        return $arrayCategory;
    }

    // ***********************************************************************
    // * 関数名     ：対象者ユーザグループ登録処理
    // * 返り値     ：成功:true、失敗:false
    // ***********************************************************************
    function UpdateUserGroup(   $strCategoryID      ,   // 問題集ID
                                $arrayUserGroup     )   // 対象者ユーザグループID
    {
        $intRetCode = true;

        // ＤＢ接続
        $db = new WrapDB();
        $db->ConnectDB();

        //トランザクション開始
        $db->AutoCommitOffDB();

        // エラー中断用のループ。while(false)で必ず抜ける。
        do
        {
            $intCategoryID = intval( $strCategoryID );

            // *******************
            // 全て削除してから再登録
            // *******************

            // DELETE
            $strSQL =   " DELETE                " .
                        " FROM                  " .
                        "   learning_user_group_master " .
                        " WHERE category_id =   " . SQLAid::escapeNum( $intCategoryID   ) ;
            // SQL実行
            $objUserGroup = $db->QueryDB( $strSQL );

            // ********************
            // 対象者グループ登録
            // ********************
            for( $intCount = 0; $intCount < count( $arrayUserGroup ); $intCount++ )
            {
                $intUserGroupID = intval( $arrayUserGroup[$intCount] );

                // INSERT
                $strSQL =   " INSERT INTO learning_user_group_master " .
                            " (                         " .
                            "   category_id         ,   " .
                            "   group_id            ,   " .
                            "   delete_flag             " .
                            " )                         " .
                            " VALUES                    " .
                            " (                         " .
                            "    " . SQLAid::escapeStr( $intCategoryID  ) . ", " .
                            "    " . SQLAid::escapeStr( $intUserGroupID ) . ", " .
                            "    0 " .
                            ")                          " ;
print("<!--".$strSQL."-->¥n");

                // SQL実行
                $objUserGroup = $db->QueryDB( $strSQL );

                // 失敗したら処理中断
                if( true != $objUserGroup )
                {
                    //失敗
                    $intRetCode = false;
                    break;
                }
            }

            // エラーの場合
            if( true != $intRetCode )
            {
                break;
            }

            break;

        }while( false );


        // 結果成功したらCOMMIT、失敗ならROLLBACK
        if( false != $intRetCode )
        {
            //成功
            $db->TransactDB( DB_COM_COMMIT );
            $db->CloseDB();
        }
        else
        {
            //失敗
            $db->TransactDB( DB_COM_ROLLBACK );
            $db->CloseDB();
        }

        return $intRetCode;
    }

    // ***********************************************************************
    // * 関数名     ：対象者ユーザグループ登録処理
    // * 返り値     ：成功:true、失敗:false
    // ***********************************************************************
    function UpdateCategoryPeriod(  $strCategoryID  ,
                                    $strGroupID     ,
                                    $strPeriodDateY ,
                                    $strPeriodDateM ,
                                    $strPeriodDateD )
    {
        $intRetCode = true;

        $strDate = $strPeriodDateY . "-" . $strPeriodDateM . "-" . $strPeriodDateD;

        // ＤＢ接続
        $db = new WrapDB();
        $db->ConnectDB();

        //トランザクション開始
        $db->AutoCommitOffDB();

        // UPDATE
        $strSQL =   " UPDATE                    " .
                    "   learning_user_group_master " .
                    " SET                       " .
                    "   period_date         =   " . SQLAid::escapeStr( $strDate         ) .
                    " WHERE                     " .
                    "      category_id      =   " . SQLAid::escapeNum( $strCategoryID   ) .
                    " AND  group_id         =   " . SQLAid::escapeNum( $strGroupID      ) ;
print("<!--".$strSQL."-->");

        // SQL実行
        $objCategory = $db->QueryDB( $strSQL );

        // 結果成功したらCOMMIT、失敗ならROLLBACK
        if( false != $objCategory )
        {
            //成功
            $db->TransactDB( DB_COM_COMMIT );
            $db->CloseDB();
        }
        else
        {
            //失敗
            $db->TransactDB( DB_COM_ROLLBACK );
            $db->CloseDB();
        }

        return $intRetCode;
    }

    // ***********************************************************************
    // * 関数名     ：対象者グループ取得（問題集単位）
    // ***********************************************************************
    function GetUserGroupOfCategory(    &$intRows       ,
                                        $strCategoryID  )
    {
        // ＤＢ接続
        $db = new WrapDB();
        $db->ConnectDB();

        //トランザクション開始
        $db->AutoCommitOffDB();

        // SELECT
        $strSQL =   " SELECT                    " .
                    "   group_id            ,   " .
                    "   category_id         ,   " .
                    "   finish_flag         ,   " .
                    "   delete_flag             " .
                    " FROM                      " .
                    "   learning_user_group_master " .
                    " WHERE category_id     =   " . SQLAid::escapeNum( $strCategoryID ) .
                    " ORDER BY group_id         ";

print("<!--".$strSQL."-->¥n");
        // SQL実行
        $objUG = $db->QueryDB( $strSQL );

        // 実行結果行数を取得
        $intRows = $db->GetNumRowsDB( $objUG );

        // 取得行を配列化
        $arrayUG = array();
        for( $intCount = 0; $intCount < $intRows; $intCount++ )
        {
            $arrayResult = $db->FetchArrayDB( $objUG );

            // 取得したデータを配列に格納
            $arrayUG[] = array( 'group_id'      => $arrayResult["group_id"]     ,
                                'category_id'   => $arrayResult["category_id"]  ,
                                'finish_flag'   => $arrayResult["finish_flag"]  ,
                                'delete_flag'   => $arrayResult["delete_flag"]  );
        }

        return $arrayUG;
    }

    // ***********************************************************************
    // * 関数名     ：ランキング
    // ***********************************************************************
    function GetRanking(    $intCateID  ,
                            $intUsrID   ,
                            $intGrpID   )
    {
        // ＤＢ接続
        $db = new WrapDB();
        $db->ConnectDB();

        //トランザクション開始
        $db->AutoCommitOffDB();

        // ********************************************************
        // 問題集の人数＝分母。カテゴリIDが何人に出題されているか。
        // ********************************************************
        // SELECT
/*        $strSQL =   " SELECT                " .
                    "   gu.user_id      ,   " .
                    "   count( IF( rep.reply_result = 0,1,NULL ) ) as right_count " .   // 正解数をカウント(LEFT JOINなので、無ければゼロのはず)
                    " FROM                  " .
                    "   group_users   gu    " .
                    " LEFT JOIN learning_reply_master rep " .
                    "   ON  gu.user_id = rep.user_id " .
                    "   AND rep.category_id =   " . SQLAid::escapeNum( $intCateID ) .
                    " WHERE gu.group_id     =   " . SQLAid::escapeNum( $intGrpID ) .
                    " GROUP BY rep.user_id  " .
                    " ORDER BY gu.user_id   " ;*/
        $strSQL =   " SELECT                " .
                    "   gu.user_id          " .
                    " FROM                  " .
                    "   group_users   gu    " .
                    " WHERE gu.group_id     =   " . SQLAid::escapeNum( $intGrpID ) .
                    " ORDER BY gu.user_id   " ;
//print("<!--".$strSQL."-->¥n");

        // SQL実行
        $objUG = $db->QueryDB( $strSQL );

        // 実行結果行数を取得(分母)
        $intUserRows = $db->GetNumRowsDB( $objUG );

        $strSQL =   " SELECT                " .
                    "   user_id         ,   " .
                    "   count( IF( reply_result = 0,1,NULL ) ) as right_count " .   // 正解数をカウント(LEFT JOINなので、無ければゼロのはず)
                    " FROM                  " .
                    "   learning_reply_master " .
                    " WHERE group_id    =   " . SQLAid::escapeNum( $intGrpID ) .
                    "   AND category_id =   " . SQLAid::escapeNum( $intCateID ) .
                    " GROUP BY user_id      " .
                    " ORDER BY right_count DESC ,user_id      " ;
//print("<!--".$strSQL."-->¥n");
        // SQL実行
        $objCount = $db->QueryDB( $strSQL );
        $intRankRows = $db->GetNumRowsDB( $objUG );

        // ********************************************************
        // 問題集の人数分ループし、それぞれの「正解数」を取得する＝ランキング
        // ********************************************************
        $intRankNo = $intUserRows;
        for( $intCount = 0; $intCount < $intRankRows; $intCount++ )
        {
            $arrayCount = $db->FetchArrayDB( $objCount );

            $intRepCount = intval( $arrayCount["right_count"] );     // 正解数
            $intRepUsrID_Now = intval( $arrayCount["user_id"] );     // ユーザID

            // 自分は何位か？
            if( $intUsrID == $intRepUsrID_Now )
            {
                // 回答数ゼロの場合は最下位
                if( 0 == $intRepCount )
                {
                    break;
                }
                else
                {
                    $intRankNo = $intCount + 1;
                }
                break;
            }
            
        }

        // 取得したデータを配列に格納
        $arrayRank = array( 'ranking_member_count'  => $intUserRows ,
                            'ranking_now'           => $intRankNo   );

        return $arrayRank;
    }

    // ***********************************************************************
    // * 関数名     ：回答結果
    // ***********************************************************************
    function GetAnswerResult( $intUsrID, $intGrpID, $intCateID, &$rowsResult )
    {
        // ＤＢ接続
        $db = new WrapDB();
        $db->ConnectDB();

        //トランザクション開始
        $db->AutoCommitOffDB();

        // ********************************************************
        // 質問に対する回答結果を返す
        // ********************************************************
        // SELECT
        $strSQL =   " SELECT                " .
                    "   reply_result        " .
                    " FROM                  " .
                    "   learning_reply_master " .
                    " WHERE user_id     =   " . SQLAid::escapeNum( $intUsrID    ) .
                    "   AND group_id    =   " . SQLAid::escapeNum( $intGrpID    ) .
                    "   AND category_id =   " . SQLAid::escapeNum( $intCateID   ) .
                    " ORDER BY question_id  " ;
print("<!--".$strSQL."-->¥n");

        // SQL実行
        $objUG = $db->QueryDB( $strSQL );

        // 実行結果行数を取得
        $rowsResult = $db->GetNumRowsDB( $objUG );

        $arrayRank = array();
        for( $intCount = 0; $intCount < $rowsResult; $intCount++ )
        {
            $arrayResult = $db->FetchArrayDB( $objUG );

            $intRepResult = intval( $arrayResult["reply_result"] );    // 回答結果

            // 取得したデータを配列に格納
            $arrayRank[] = array( 'reply_result'  => $intRepResult );
        }

        return $arrayRank;
    }

    // ***********************************************************************
    // * 関数名     ：対象問題集の取得
    // ***********************************************************************
    function GetGroupOfSession( $strSessionID   ,
                                $intFlag = 9    ,
                                &$intRows       ,
                                $intLimit = 1000 ,
                                $intLimitStart = 0 )
    {
        // ＤＢ接続
        $db = new WrapDB();
        $db->ConnectDB();

        //トランザクション開始
        $db->AutoCommitOffDB();

        // SELECT
        $strSQL =   " SELECT                        " .
                    "   el_cate.category_id     ,   " .
                    "   el_cate.category_title  ,   " .
                    "   el_cate.category_body   ,   " .
                    "   el.group_id             ,   " .
                    "   ug.user_id                  " .
                    " FROM                          " .
                    "   session ses                 " .
                    " INNER JOIN user us            " .
                    "   ON ses.login_id = us.login_id  " .
                    " INNER JOIN group_users ug     " .
                    "   ON us.user_id = ug.user_id " .
                    " INNER JOIN learning_user_group_master el " .
                    "   ON ug.group_id = el.group_id   " .
                    " INNER JOIN learning_category_master el_cate   " .
                    "   ON el.category_id = el_cate.category_id     " .
                    " WHERE ses.session_id  =   " . SQLAid::escapeStr( $strSessionID ) .
                    " ORDER BY el.category_id DESC  " .
                    " LIMIT " . $intLimitStart . ", " . $intLimit ;

print("<!--".$strSQL."-->¥n");
        // SQL実行
        $objUG = $db->QueryDB( $strSQL );

        // 実行結果行数を取得
        $intRowsUG = $db->GetNumRowsDB( $objUG );

        // 取得行を配列化
        $arrayUG = array();
        $intRows = 0;
        for( $intCount = 0; $intCount < $intRowsUG; $intCount++ )
        {
            $arrayResult = $db->FetchArrayDB( $objUG );

            $intUserID = intval( $arrayResult["user_id"] );
            $intCateID = intval( $arrayResult["category_id"] );
            $intGrpID  = intval( $arrayResult["group_id"] );

            // **************************
            // １つも回答していないもの
            // **************************
            if( 0 == $intFlag )
            {
                // 回答マスタにユーザID、カテゴリIDが存在しないレコードのみ取得

                // SELECT
                $strSQL =   " SELECT                    " .
                            "   question_id             " .
                            " FROM                      " .
                            "   learning_reply_master   " .
                            " WHERE user_id         =   " . SQLAid::escapeNum( $intUserID ) .
                            " AND   group_id        =   " . SQLAid::escapeNum( $intGrpID  ) .
                            " AND   category_id     =   " . SQLAid::escapeNum( $intCateID ) ;
//                            " AND   reply_result   != 9 " ;

print("<!--".$strSQL."-->¥n");
                // SQL実行
                $objRep = $db->QueryDB( $strSQL );

                // 実行結果行数を取得
                $intRowsNew = $db->GetNumRowsDB( $objRep );

                // 回答レコードがゼロの場合は、新規とみなす。１レコード以上あればスルー。
                if( 0 < $intRowsNew )
                {
                    continue;
                }
            }
            // **************************
            // 回答中
            // **************************
            elseif( 1 == $intFlag )
            {
                // 回答マスタにユーザID、カテゴリIDが１つ以上存在するレコードで、未回答のレコードがあるもののみ取得

                // SELECT
                $strSQL =   " SELECT                    " .
                            "   question_id             " .
                            " FROM                      " .
                            "   learning_reply_master   " .
                            " WHERE user_id         =   " . SQLAid::escapeNum( $intUserID ) .
                            " AND   group_id        =   " . SQLAid::escapeNum( $intGrpID  ) .
                            " AND   category_id     =   " . SQLAid::escapeNum( $intCateID ) .
                            " AND   reply_result    = 9 " ;

print("<!--".$strSQL."-->¥n");
                // SQL実行
                $objRep = $db->QueryDB( $strSQL );

                // 実行結果行数を取得
                $intRowsDur = $db->GetNumRowsDB( $objRep );

                // 未回答の問題が無ければスルー。１つ以上あれば、回答中かつ、回答完了していないとみなす。
                if( 0 == $intRowsDur )
                {
                    continue;
                }
            }
            // **************************
            // 一通り回答済
            // **************************
            elseif( 2 == $intFlag )
            {
                // 全レコード回答済だが、不正解が１つ以上ある場合

                // SELECT
                $strSQL =   " SELECT                    " .
//                            "   question_id         ,   " .
                            "   count( IF( reply_result = 9,1,NULL ) ) as yet_count ,  " .
                            "   count( IF( reply_result = 1,1,NULL ) ) as miss_count " .
                            " FROM                      " .
                            "   learning_reply_master   " .
                            " WHERE user_id         =   " . SQLAid::escapeNum( $intUserID ) .
                            " AND   group_id        =   " . SQLAid::escapeNum( $intGrpID  ) .
                            " AND   category_id     =   " . SQLAid::escapeNum( $intCateID ) ;

print("<!--".$strSQL."-->¥n");
                // SQL実行
                $objRep = $db->QueryDB( $strSQL );

                // 実行結果行数を取得
                $intRowsNot = $db->GetNumRowsDB( $objRep );

                // レコードなしは未チャレンジ
                if( 0 == $intRowsNot )
                {
                    continue;
                }
                else
                {
                    $arrayNotComp = $db->FetchArrayDB( $objRep );
                    $intMissCount = intval( $arrayNotComp["miss_count"] );
                    $intYetCount  = intval( $arrayNotComp["yet_count"] );
                    if( 0 == $intMissCount || 0 != $intYetCount )
                    {
                        // 未回答がある場合は回答中、不正解が無い場合はcompleteなのでスルー
                        continue;
                    }
                }
            }
            // **************************
            // complete
            // **************************
            elseif( 3 == $intFlag )
            {
                // 回答マスタにユーザID、カテゴリIDが全問題分存在するレコードのみ取得(ただし不正解なし)

                // SELECT
                $strSQL =   " SELECT                    " .
                            "   count( IF( reply_result = 9,1,NULL ) ) as yet_count ,  " .
                            "   count( IF( reply_result = 1,1,NULL ) ) as miss_count ,  " .
                            "   count( IF( reply_result = 0,1,NULL ) ) as right_count   " .
                            " FROM                      " .
                            "   learning_reply_master   " .
                            " WHERE user_id         =   " . SQLAid::escapeNum( $intUserID ) .
                            " AND   group_id        =   " . SQLAid::escapeNum( $intGrpID  ) .
                            " AND   category_id     =   " . SQLAid::escapeNum( $intCateID ) ;

print("<!--".$strSQL."-->¥n");
                // SQL実行
                $objRep = $db->QueryDB( $strSQL );

                // 実行結果行数を取得
                $intRowsComp = $db->GetNumRowsDB( $objRep );

                $arrayComp = $db->FetchArrayDB( $objRep );

                // 未回答・不正解がある場合はスルー。回答開始していない場合はレコードが無いのでスルー。
                if( 0 < intval( $arrayComp["yet_count"] ) || 0 < intval( $arrayComp["miss_count"] ) || 0 == intval( $arrayComp["right_count"] ) )
                {
                    continue;
                }
            }

            $intRows++;

            // 取得したデータを配列に格納
            $arrayUG[] = array( 'category_id'       => $arrayResult["category_id"   ]   ,
                                'category_title'    => $arrayResult["category_title"]   ,
                                'category_body'     => $arrayResult["category_body" ]   ,
                                'group_id'          => $arrayResult["group_id"      ]   ,
                                'user_id'           => $arrayResult["user_id"       ]   );
        }

        return $arrayUG;
    }

    // ***********************************************************************
    // * 関数名     ：対象ユーザの対象問題集の回答結果を取得
    // ***********************************************************************
    function GetReplyResultOfUser(  $intUsrID   ,
                                    $intGrpID   ,
                                    $intCateID  ,
                                    &$intRows   )
    {
        // ＤＢ接続
        $db = new WrapDB();
        $db->ConnectDB();

        //トランザクション開始
        $db->AutoCommitOffDB();

        // SELECT
        $strSQL =   " SELECT                        " .
                    "   reply_id            ,       " .
                    "   question_id         ,       " .
                    "   reply_result        ,       " .
                    "   reply_answer_id             " .
                    " FROM                          " .
                    "   learning_reply_master       " .
                    " WHERE user_id             =   " . SQLAid::escapeNum( $intUsrID    ) .
                    " AND   group_id            =   " . SQLAid::escapeNum( $intGrpID    ) .
                    " AND   category_id         =   " . SQLAid::escapeNum( $intCateID   ) .
                    " ORDER BY reply_id DESC        " ;

print("<!--".$strSQL."-->¥n");

        // SQL実行
        $objUG = $db->QueryDB( $strSQL );

        // 実行結果行数を取得
        $intRows = $db->GetNumRowsDB( $objUG );

        // 取得行を配列化
        $arrayUG = array();
        for( $intCount = 0; $intCount < $intRows; $intCount++ )
        {
            $arrayResult = $db->FetchArrayDB( $objUG );

            // 取得したデータを配列に格納
            $arrayUG[] = array( 'reply_id'          => $arrayResult["reply_id"          ]   ,
                                'question_id'       => $arrayResult["question_id"       ]   ,
                                'reply_result'      => $arrayResult["reply_result"      ]   ,
                                'reply_answer_id'   => $arrayResult["reply_answer_id"   ]   );
        }

        return $arrayUG;
    }


    // ***********************************************************************
    // * 関数名     ：問題取得（ランダム）
    // ***********************************************************************
    function GetQuestionRandom( $intCateID, &$intRows )
    {
        // ＤＢ接続
        $db = new WrapDB();
        $db->ConnectDB();

        //トランザクション開始
        $db->AutoCommitOffDB();

        // 指定のIDのレコードがあるかチェックし、あればUPDATE、無ければINSERT
        $strSQL =   " SELECT                    " .
                    "   category_id         ,   " .
                    "   question_id             " .
                    " FROM                      " .
                    "   learning_category_questions_master " .
                    " WHERE 0=0                 " .
                    " AND   category_id     =   " . SQLAid::escapeNum( $intCateID   ) .
                    " ORDER BY RAND()           " ;    // SQLで取得時にランダムで取得する

print("<!--".$strSQL."-->¥n");
        // SQL実行
        $objQuestion = $db->QueryDB( $strSQL );

        // 実行結果行数を取得
        $intRows = $db->GetNumRowsDB( $objQuestion );

        // 取得行を配列化
        $arrayQuestion = array();
        for( $intCount = 0; $intCount < $intRows; $intCount++ )
        {
            $arrayResult = $db->FetchArrayDB( $objQuestion );

            // 取得したデータを配列に格納
            $arrayQuestion[] = array(   'question_id'           => $arrayResult["question_id"]          );
        }

        return $arrayQuestion;
    }

    // ***********************************************************************
    // * 関数名     ：指定ユーザ・指定問題集の回答順を設定する
    // * 返り値     ：成功:true、失敗:false
    // ***********************************************************************
    function SetQuestionOrder( $intUsrID, $intGrpID, $intCateID )
    {
        $intRetCode = true;

        // ＤＢ接続
        $db = new WrapDB();
        $db->ConnectDB();

        //トランザクション開始
        $db->AutoCommitOffDB();

        // エラー中断用のループ。while(false)で必ず抜ける。
        do
        {
            // 問題をランダムに取得
            $arrayQuestionRand = LearningDB::GetQuestionRandom( $intCateID, $intRowsRand );

            // ********************
            // 対象者回答順登録
            // ********************
            for( $intCount = 0; $intCount < $intRowsRand; $intCount++ )
            {
                $intReplyID     = $intCount + 1;
                $intQuID        = intval( $arrayQuestionRand[$intCount]["question_id"] );

                // INSERT
                $strSQL =   " INSERT INTO learning_reply_master " .
                            " (                         " .
                            "   reply_id            ,   " .
                            "   user_id             ,   " .
                            "   group_id            ,   " .
                            "   category_id         ,   " .
                            "   question_id         ,   " .
                            "   reply_result            " .
                            " )                         " .
                            " VALUES                    " .
                            " (                         " .
                            "    " . SQLAid::escapeNum( $intReplyID ) . ", " .
                            "    " . SQLAid::escapeNum( $intUsrID   ) . ", " .
                            "    " . SQLAid::escapeNum( $intGrpID   ) . ", " .
                            "    " . SQLAid::escapeNum( $intCateID  ) . ", " .
                            "    " . SQLAid::escapeNum( $intQuID    ) . ", " .
                            "    9 " .
                            ")                          " ;
print("<!--".$strSQL."-->¥n");

                // SQL実行
                $objUserGroup = $db->QueryDB( $strSQL );

                // 失敗したら処理中断
                if( true != $objUserGroup )
                {
                    //失敗
                    $intRetCode = false;
                    break;
                }

                // 先頭の問題ID取得
                if( 0 == $intCount )
                {
                    $intHeadQuID = $intQuID;
                }
            }

            // エラーの場合
            if( true != $intRetCode )
            {
                break;
            }

            break;

        }while( false );


        // 結果成功したらCOMMIT、失敗ならROLLBACK
        if( false != $intRetCode )
        {
            //成功
            $db->TransactDB( DB_COM_COMMIT );

            // 未回答の問題一覧を取得
            $arrayQuestion = LearningDB::GetYetReplyHead(   $intUsrID   ,
                                                            $intGrpID   ,
                                                            $intCateID  );  // 未回答の先頭の問題番号

            $db->CloseDB();
        }
        else
        {
            //失敗
            $db->TransactDB( DB_COM_ROLLBACK );
            $db->CloseDB();
            $arrayQuestion = NULL;
        }

        return $arrayQuestion;
    }

    // ***********************************************************************
    // * 関数名     ：未回答の問題取得
    // ***********************************************************************
    function GetYetReplyHead( $intUsrID, $intGrpID, $intCateID )
    {
        // ＤＢ接続
        $db = new WrapDB();
        $db->ConnectDB();

        //トランザクション開始
        $db->AutoCommitOffDB();

        // 問題マスタから、まだ回答していない問題を取得する
        $strSQL =   " SELECT                    " .
                    "   qu.question_id      ,   " .
                    "   qu.question_body    ,   " .
                    "   rep.reply_id            " .
                    " FROM                      " .
                    "   learning_question_master qu " .
                    " INNER JOIN learning_reply_master rep " .
                    "   ON qu.question_id = rep.question_id " .
                    " WHERE 0=0                 " .
                    " AND   rep.user_id     =   " . SQLAid::escapeNum( $intUsrID    ) .
                    " AND   rep.group_id    =   " . SQLAid::escapeNum( $intGrpID    ) .
                    " AND   rep.category_id =   " . SQLAid::escapeNum( $intCateID   ) .
                    " AND   rep.reply_result = 9 " .
                    " ORDER BY rep.reply_id     " .
                    " LIMIT 0 , 1               " ;

print("<!--".$strSQL."-->¥n");
        // SQL実行
        $objQuestion = $db->QueryDB( $strSQL );

        // 実行結果行数を取得
        $intRows = $db->GetNumRowsDB( $objQuestion );

        $arrayResult = $db->FetchArrayDB( $objQuestion );

        $intQ_no = intval( $arrayResult["reply_id"] );

        if( 0 < $intRows )
        {
            // 取得したデータを配列に格納
            $arrayQuestion = array( 'question_id'   => $arrayResult["question_id"   ]  ,
                                    'question_body' => $arrayResult["question_body" ]  ,
                                    'reply_id'      => $intQ_no                         );
        }
        else
        {
            $arrayQuestion = NULL;
        }

        return $arrayQuestion;
    }

    // ***********************************************************************
    // * 関数名     ：不正解の問題取得（先頭）
    // ***********************************************************************
    function GetMissReplyHead( $intUsrID, $intGrpID, $intCateID )
    {
        // ＤＢ接続
        $db = new WrapDB();
        $db->ConnectDB();

        //トランザクション開始
        $db->AutoCommitOffDB();

        // 問題マスタから、不正解問題を取得する
        $strSQL =   " SELECT                    " .
                    "   qu.question_id      ,   " .
                    "   qu.question_body    ,   " .
                    "   rep.reply_id            " .
                    " FROM                      " .
                    "   learning_question_master qu " .
                    " INNER JOIN learning_reply_master rep " .
                    "   ON qu.question_id = rep.question_id " .
                    " WHERE 0=0                 " .
                    " AND   rep.user_id     =   " . SQLAid::escapeNum( $intUsrID    ) .
                    " AND   rep.group_id    =   " . SQLAid::escapeNum( $intGrpID    ) .
                    " AND   rep.category_id =   " . SQLAid::escapeNum( $intCateID   ) .
                    " AND   rep.reply_result = 1 " .
                    " ORDER BY rep.reply_id     " .
                    " LIMIT 0 , 1               " ;

print("<!--".$strSQL."-->¥n");
        // SQL実行
        $objQuestion = $db->QueryDB( $strSQL );

        // 実行結果行数を取得
        $intRows = $db->GetNumRowsDB( $objQuestion );

        $arrayResult = $db->FetchArrayDB( $objQuestion );

        $intQ_no = intval( $arrayResult["reply_id"] );

        if( 0 < $intRows )
        {
            // 取得したデータを配列に格納
            $arrayQuestion = array( 'question_id'   => $arrayResult["question_id"   ]  ,
                                    'question_body' => $arrayResult["question_body" ]  ,
                                    'reply_id'      => $intQ_no                         );
        }
        else
        {
            $arrayQuestion = NULL;
        }

        return $arrayQuestion;
    }

    // ***********************************************************************
    // * 関数名     ：ＱＡ取得
    // ***********************************************************************
    function GetQA( $intUsrID, $intGrpID, $intCateID, &$intRows )
    {
        // ＤＢ接続
        $db = new WrapDB();
        $db->ConnectDB();

        //トランザクション開始
        $db->AutoCommitOffDB();

        // 問題マスタから、不正解問題を取得する
        $strSQL =   " SELECT                        " .
                    "   qu.question_body        ,   " .
                    "   ans.answer_body             " .
                    " FROM                      " .
                    "   learning_category_questions_master cate " .
                    " INNER JOIN learning_question_master qu    " .
                    "   ON  cate.question_id = qu.question_id   " .
                    " INNER JOIN learning_answer_master   ans   " .
                    "   ON  qu.question_id = ans.question_id    " .
                    "   AND qu.question_answer_id = ans.answer_id " .
                    " WHERE 0=0                 " .
                    " AND   cate.category_id =  " . SQLAid::escapeNum( $intCateID   ) ;

print("<!--".$strSQL."-->¥n");
        // SQL実行
        $objQuestion = $db->QueryDB( $strSQL );

        // 実行結果行数を取得
        $intRows = $db->GetNumRowsDB( $objQuestion );

        // 取得行を配列化
        $arrayQuestion = array();
        for( $intCount = 0; $intCount < $intRows; $intCount++ )
        {
            $arrayResult = $db->FetchArrayDB( $objQuestion );

            // 取得したデータを配列に格納
            $arrayQuestion[] = array(   'question_body' => $arrayResult["question_body" ]  ,
                                        'answer_body'   => $arrayResult["answer_body"   ]  );
        }

        return $arrayQuestion;
    }

    // ***********************************************************************
    // * 関数名     ：問題に対する答えを取得する
    // ***********************************************************************
    function GetReplyOrderAnswer( $intQuID )
    {
        // ＤＢ接続
        $db = new WrapDB();
        $db->ConnectDB();

        //トランザクション開始
        $db->AutoCommitOffDB();

        // SELECT
        $strSQL =   " SELECT                    " .
                    "   answer_id       ,       " .
                    "   answer_body             " .
                    " FROM                      " .
                    "   learning_answer_master  " .
                    " WHERE 0=0                 " .
                    " AND   question_id     =   " . SQLAid::escapeNum( $intQuID    ) .
                    " AND   delete_flag = 0     " .
                    " ORDER BY answer_id        " ;

print("<!--".$strSQL."-->¥n");
        // SQL実行
        $objQuestion = $db->QueryDB( $strSQL );

        // 実行結果行数を取得
        $intRows = $db->GetNumRowsDB( $objQuestion );

        // 取得行を配列化
        $arrayQuestion = array();
        for( $intCount = 0; $intCount < $intRows; $intCount++ )
        {
            $arrayResult = $db->FetchArrayDB( $objQuestion );

            // 取得したデータを配列に格納
            $arrayQuestion[] = array(   'answer_id'     => $arrayResult["answer_id"     ]  ,
                                        'answer_body'   => $arrayResult["answer_body"   ]  );
        }

        return $arrayQuestion;
    }

    // ***********************************************************************
    // * 関数名     ：問題総数
    // ***********************************************************************
    function GetMaxQuestionCount( $intCateID )
    {
        // ＤＢ接続
        $db = new WrapDB();
        $db->ConnectDB();

        //トランザクション開始
        $db->AutoCommitOffDB();

        // SELECT
        $strSQL =   " SELECT                                " .
                    "   count( question_id ) as qu_count    " .
                    " FROM                                  " .
                    "   learning_category_questions_master  " .
                    " WHERE 0=0                     " .
                    " AND   category_id     =       " . SQLAid::escapeNum( $intCateID ) ;

print("<!--".$strSQL."-->¥n");
        // SQL実行
        $objQuestion = $db->QueryDB( $strSQL );

        $arrayResult = $db->FetchArrayDB( $objQuestion );

        $intMaxQu = intval( $arrayResult["qu_count"] );

        return $intMaxQu;
    }

    // ***********************************************************************
    // * 関数名     ：残り問題総数
    // ***********************************************************************
    function GetMaxQuestionYetCount( $intUsrID, $intGrpID, $intCateID )
    {
        // ＤＢ接続
        $db = new WrapDB();
        $db->ConnectDB();

        //トランザクション開始
        $db->AutoCommitOffDB();

        // SELECT
        $strSQL =   " SELECT                    " .
                    "   count( reply_id ) as yet_count  " .
                    " FROM                      " .
                    "   learning_reply_master   " .
                    " WHERE 0=0                 " .
                    " AND   user_id         =   " . SQLAid::escapeNum( $intUsrID    ) .
                    " AND   group_id        =   " . SQLAid::escapeNum( $intGrpID    ) .
                    " AND   category_id     =   " . SQLAid::escapeNum( $intCateID   ) ;

print("<!--".$strSQL."-->¥n");
        // SQL実行
        $objQuestion = $db->QueryDB( $strSQL );

        $arrayResult = $db->FetchArrayDB( $objQuestion );

        $intMaxYet = intval( $arrayResult["yet_count"] );

        return $intMaxYet;
    }

    // ***********************************************************************
    // * 関数名     ：残り問題総数(不正解のみ)
    // ***********************************************************************
    function GetMaxMissQuestionCount( $intUsrID, $intGrpID, $intCateID )
    {
        // ＤＢ接続
        $db = new WrapDB();
        $db->ConnectDB();

        //トランザクション開始
        $db->AutoCommitOffDB();

        // SELECT
        $strSQL =   " SELECT                    " .
                    "   count( reply_id ) as miss_count  " .
                    " FROM                      " .
                    "   learning_reply_master   " .
                    " WHERE 0=0                 " .
                    " AND   user_id         =   " . SQLAid::escapeNum( $intUsrID    ) .
                    " AND   group_id        =   " . SQLAid::escapeNum( $intGrpID    ) .
                    " AND   category_id     =   " . SQLAid::escapeNum( $intCateID   ) .
                    " AND   reply_id        = 1 " ;

print("<!--".$strSQL."-->¥n");
        // SQL実行
        $objQuestion = $db->QueryDB( $strSQL );

        $arrayResult = $db->FetchArrayDB( $objQuestion );

        $intMaxMiss = intval( $arrayResult["miss_count"] );

        return $intMaxMiss;
    }

    // ***********************************************************************
    // * 関数名     ：指定ユーザ・指定問題集の回答結果をUPDATE
    // * 返り値     ：成功:true、失敗:false
    // ***********************************************************************
    function UpdateReplyResult( $intUsrID, $intGrpID, $intCateID, $intQuID, $intAnsID, $intRepID, $intResult )
    {
        $intRetCode = true;

        // ＤＢ接続
        $db = new WrapDB();
        $db->ConnectDB();

        //トランザクション開始
        $db->AutoCommitOffDB();

        // UPDATE
        $strSQL =   " UPDATE                    " .
                    "   learning_reply_master   " .
                    " SET                       " .
                    "   reply_result        =   " . SQLAid::escapeNum( $intResult   ) . " , " .
                    "   reply_answer_id     =   " . SQLAid::escapeNum( $intAnsID    ) .
                    " WHERE                     " .
                    "       reply_id        =   " . SQLAid::escapeNum( $intRepID    ) .
                    " AND   user_id         =   " . SQLAid::escapeNum( $intUsrID    ) .
                    " AND   group_id        =   " . SQLAid::escapeNum( $intGrpID    ) .
                    " AND   category_id     =   " . SQLAid::escapeNum( $intCateID   ) .
                    " AND   question_id     =   " . SQLAid::escapeNum( $intQuID     ) ;

print("<!--".$strSQL."-->¥n");

        // SQL実行
        $objUserGroup = $db->QueryDB( $strSQL );

        // 結果成功したらCOMMIT、失敗ならROLLBACK
        if( false != $objUserGroup )
        {
            //成功
            $db->TransactDB( DB_COM_COMMIT );
            $db->CloseDB();
        }
        else
        {
            //失敗
            $db->TransactDB( DB_COM_ROLLBACK );
            $db->CloseDB();
            $intRetCode = false;
        }

        return $intRetCode;
    }

    // ***********************************************************************
    // * 関数名     ：正解の選択肢IDを返す
    // ***********************************************************************
    function GetReplyAnswer( $intQuID )
    {
        // ＤＢ接続
        $db = new WrapDB();
        $db->ConnectDB();

        //トランザクション開始
        $db->AutoCommitOffDB();

        // SELECT
        $strSQL =   " SELECT                    " .
                    "   question_answer_id      " .
                    " FROM                      " .
                    "   learning_question_master  " .
                    " WHERE 0=0                 " .
                    " AND   question_id     =   " . SQLAid::escapeNum( $intQuID ) ;

print("<!--".$strSQL."-->¥n");
        // SQL実行
        $objQuestion = $db->QueryDB( $strSQL );

        // 実行結果行数を取得
        $intRows = $db->GetNumRowsDB( $objQuestion );

        $arrayResult = $db->FetchArrayDB( $objQuestion );
        $intResultID = intval( $arrayResult["question_answer_id"] );


        return $intResultID;
    }

    // ***********************************************************************
    // * 関数名     ：回答率
    // * 返り値     ：成功:true、失敗:false
    // ***********************************************************************
    function GetAnswerRate( $intUsrID, $intGrpID, $intCateID )
    {
        $intRetCode = true;

        // ＤＢ接続
        $db = new WrapDB();
        $db->ConnectDB();

        //トランザクション開始
        $db->AutoCommitOffDB();

        // SELECT
        $strSQL =   " SELECT                    " .
                    "   count( reply_result ) as answer_count ,  " .
                    "   count( IF( reply_result = 9,1,NULL ) ) as yet_count ,  " .
                    "   count( IF( reply_result = 1,1,NULL ) ) as miss_count ,  " .
                    "   count( IF( reply_result = 0,1,NULL ) ) as right_count   " .
                    " FROM                      " .
                    "   learning_reply_master   " .
                    " WHERE 0=0                 " .
                    " AND   user_id         =   " . SQLAid::escapeNum( $intUsrID    ) .
                    " AND   group_id        =   " . SQLAid::escapeNum( $intGrpID    ) .
                    " AND   category_id     =   " . SQLAid::escapeNum( $intCateID   ) ;
//print("<!--".$strSQL."-->¥n");

        // SQL実行
        $objQuestion = $db->QueryDB( $strSQL );

        $arrayResult = $db->FetchArrayDB( $objQuestion );
        $intAnswerCount= intval( $arrayResult["answer_count"] );    // 問題数
        $intYetCount   = intval( $arrayResult["yet_count"] );       // 未回答数
        $intRightCount = intval( $arrayResult["right_count"] );     // 正解数
        $intMissCount  = intval( $arrayResult["miss_count"] );      // 不正解数

        // 正解率
        if( 0 < $intRightCount )
        {
            $intRete = round( $intRightCount / ( $intRightCount + $intMissCount ) * 100 );
        }
        else
        {
            $intRete = 0;
        }

        return $intRete;
    }

    // ***********************************************************************
    // * 関数名     ：問題削除
    // * 返り値     ：成功:true、失敗:false
    // ***********************************************************************
    function DeleteQuestion(    $strQuestionID      )   // 問題ＩＤ
    {
        $intRetCode = true;

        // ＤＢ接続
        $db = new WrapDB();
        $db->ConnectDB();

        //トランザクション開始
        $db->AutoCommitOffDB();

        $intQuestionID = intval( $strQuestionID );

        // *******************
        // 回答を削除
        // *******************

        // DELETE
        $strSQL =   " DELETE                " .
                    " FROM                  " .
                    "   learning_reply_master " .
                    " WHERE question_id =   " . SQLAid::escapeNum( $intQuestionID ) ;
        // SQL実行
        $objReply = $db->QueryDB( $strSQL );

        // *******************
        // 選択肢を削除
        // *******************

        // DELETE
        $strSQL =   " DELETE                " .
                    " FROM                  " .
                    "   learning_answer_master " .
                    " WHERE question_id =   " . SQLAid::escapeNum( $intQuestionID ) ;
        // SQL実行
        $objAnswer = $db->QueryDB( $strSQL );

        // *******************
        // 問題を削除
        // *******************

        // DELETE
        $strSQL =   " DELETE                " .
                    " FROM                  " .
                    "   learning_question_master " .
                    " WHERE question_id =   " . SQLAid::escapeNum( $intQuestionID ) ;
        // SQL実行
        $objQuestion = $db->QueryDB( $strSQL );

        // *******************
        // 問題集から削除
        // *******************

        // DELETE
        $strSQL =   " DELETE                " .
                    " FROM                  " .
                    "   learning_category_questions_master " .
                    " WHERE question_id =   " . SQLAid::escapeNum( $intQuestionID ) ;
        // SQL実行
        $objCategory = $db->QueryDB( $strSQL );

        // 結果成功したらCOMMIT、失敗ならROLLBACK
        if( false != $objReply || false != $objAnswer || false != $objQuestion || false != $objCategory )
        {
            //成功
            $db->TransactDB( DB_COM_COMMIT );
            $db->CloseDB();
            $intRetCode = true;
        }
        else
        {
            //失敗
            $db->TransactDB( DB_COM_ROLLBACK );
            $db->CloseDB();
            $intRetCode = false;
        }

        return $intRetCode;
    }

    // ***********************************************************************
    // * 関数名     ：問題削除
    // * 返り値     ：成功:true、失敗:false
    // ***********************************************************************
    function DeleteQuestionOfDate(    $strRegistDate      )
    {
        $intRetCode = true;

        // ＤＢ接続
        $db = new WrapDB();
        $db->ConnectDB();

        //トランザクション開始
        $db->AutoCommitOffDB();

        // 日付から問題を取得
        $strSQL =   " SELECT                    " .
                    "   question_id             " .
                    " FROM                      " .
                    "   learning_question_master " .
                    " WHERE regist_date     =   " . SQLAid::escapeStr( $strRegistDate ) ;

//print("<!--".$strSQL."-->¥n");
        // SQL実行
        $objQuestion = $db->QueryDB( $strSQL );

        // 実行結果行数を取得
        $intRows = $db->GetNumRowsDB( $objQuestion );

        // 取得行を配列化
        $arrayQuestion = array();
        for( $intCount = 0; $intCount < $intRows; $intCount++ )
        {
            $arrayResult = $db->FetchArrayDB( $objQuestion );

            $intQuestionID = intval( $arrayResult["question_id"] );

            // *******************
            // 回答を削除
            // *******************

            // DELETE
            $strSQL =   " DELETE                " .
                        " FROM                  " .
                        "   learning_reply_master " .
                        " WHERE question_id =   " . SQLAid::escapeNum( $intQuestionID ) ;
            // SQL実行
            $objReply = $db->QueryDB( $strSQL );

            // *******************
            // 選択肢を削除
            // *******************

            // DELETE
            $strSQL =   " DELETE                " .
                        " FROM                  " .
                        "   learning_answer_master " .
                        " WHERE question_id =   " . SQLAid::escapeNum( $intQuestionID ) ;
            // SQL実行
            $objAnswer = $db->QueryDB( $strSQL );

            // *******************
            // 問題を削除
            // *******************

            // DELETE
            $strSQL =   " DELETE                " .
                        " FROM                  " .
                        "   learning_question_master " .
                        " WHERE question_id =   " . SQLAid::escapeNum( $intQuestionID ) ;
            // SQL実行
            $objQuestionDel = $db->QueryDB( $strSQL );

            // *******************
            // 問題集から削除
            // *******************

            // DELETE
            $strSQL =   " DELETE                " .
                        " FROM                  " .
                        "   learning_category_questions_master " .
                        " WHERE question_id =   " . SQLAid::escapeNum( $intQuestionID ) ;
            // SQL実行
            $objCategory = $db->QueryDB( $strSQL );

            // 結果成功したらCOMMIT、失敗ならROLLBACK
            if( false != $objReply || false != $objAnswer || false != $objQuestionDel || false != $objCategory )
            {
                //成功
                $db->TransactDB( DB_COM_COMMIT );
                $db->CloseDB();
                $intRetCode = true;
            }
            else
            {
                //失敗
                $db->TransactDB( DB_COM_ROLLBACK );
                $db->CloseDB();
                $intRetCode = false;
            }
        }

        return $intRetCode;
    }

}
