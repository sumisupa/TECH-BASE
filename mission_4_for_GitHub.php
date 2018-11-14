<?php
$DSN  = 'データベース名';
$DB_USER = 'ユーザー名';
$DB_PASS = 'パスワード';
$pdo = new PDO($DSN,$DB_USER,$DB_PASS);

/*************** テーブル作成 SQL **********************************/
$sql = 'CREATE TABLE IF NOT EXISTS mission_4_joematsui_table ( 
        id INT,
        name CHAR(32),
        comment TEXT,
        date DATETIME,
        pass CHAR(4)
        );';

$result = $pdo -> query($sql);
/*************** テーブル作成 SQL 終了******************************/

        $FORM_NAME = "";
        $FORM_COMMENT = "";
        $FORM_PASS = "";
        $DEFAULT_EDIT_NUMBER = "";

        //\\\\ passフラグ \\\\\\\\\\\
        $WRONG_PASS = 0;

        $COMMENT_MODE = $_POST['comment_button'];
        $DELETE_MODE = $_POST['delete_button'];
        $EDIT_MODE = $_POST['edit_button'];

        $POST_EDIT = $_POST['editnumber'];
        $POST_DELETE = $_POST['deletenumber'];
        $POST_NAME = $_POST['name'];
        $POST_COMMENT = $_POST['comment'];
        $POST_EDIT_FLAG = $_POST['editflag'];  
        $POST_DELETE_PASS = $_POST['deletepass'];

        if(isset($COMMENT_MODE)){
            $POST_PASS = $_POST['pass'];
        }else if(isset($DELETE_MODE)){
            $POST_PASS = $_POST['pass_delete'];
        }else if(isset($EDIT_MODE)){
            $POST_PASS = $_POST['pass_edit'];
        }

        //\\\\\\\\ コメント用DB 読み込みSQL \\\\\\\\\\
        $COMMENT_sql = "SELECT * FROM mission_4_joematsui_table ORDER BY id ASC";

        $COMMENT_sql_result = $pdo -> query($COMMENT_sql);

        //\\\\\\\\ 読み込んだデータを全てフェッチ \\\\\\\\
        $FILE = $COMMENT_sql_result -> fetchAll();

        if(isset($EDIT_MODE)||isset($DELETE_MODE)){
           $POST_NAME = "";
           $POST_COMMENT = "";
        }

/*************** 編集(No.2)処理 ***************************************/
    if(!empty($POST_EDIT_FLAG)){
        $sql = "UPDATE mission_4_joematsui_table SET name = '$POST_NAME', comment = '$POST_COMMENT', pass = '$POST_PASS' WHERE id = $POST_EDIT_FLAG;";
        $result = $pdo -> query($sql);
        $EDIT_FLAG = "";
    }
/*************** 編集(No.2)処理 終了 **********************************/

/*************** 削除処理 ********************************/
    if(isset($DELETE_MODE)){
        foreach($FILE as $FILES){
            if($POST_DELETE == (int)$FILES['id']){
                if(strcmp((string)$POST_PASS,$FILES['pass']) == 0){
                    $sql = 'DELETE FROM mission_4_joematsui_table WHERE id = '.$POST_DELETE.';';
                    $result = $pdo -> query($sql);
                    break;
                }else{
                    $WRONG_PASS = 1;
                    break;
                }
            }
        }
    }
/*************** 削除処理 終了 ****************************/

/*************** 編集(No.1)処理 ********************************/
    if(isset($EDIT_MODE)){
            foreach($FILE as $FILES){
                if($POST_EDIT == (int)$FILES['id']){
                    if(strcmp((string)$POST_PASS,$FILES['pass']) == 0){
                       $EDIT_FLAG = $POST_EDIT;
                       $FORM_NAME = $FILES['name'];
                       $FORM_COMMENT = $FILES['comment'];
                       $FORM_PASS = $FILES['pass'];
                        break;
                    }else{
                        $WRONG_PASS = 1;
                        break;
                    }
                }
            }
    }
/*************** 編集(No.1)処理 終了 ****************************/

/*************** 投稿情報保存処理 ***********************************/
    if(!empty($POST_NAME) && !empty($POST_COMMENT) && isset($COMMENT_MODE) && empty($POST_EDIT_FLAG)){
                $sql = $pdo -> prepare("INSERT INTO mission_4_joematsui_table(id,name,comment,date,pass) VALUES(:id,:name,:comment,:date,:pass)");

                 //投稿番号検索代入処理
               if(empty($FILE)){
                   $LAST_COMMENT_NUMBER = 0;
               }else{
                   $LAST_COMMENT_NUMBER = (int)$FILE[count($FILE)-1]['id'];
               }

               $id = $LAST_COMMENT_NUMBER+1;
               $name = $POST_NAME;
               $comment = $POST_COMMENT;
               $DATE = date("Y-m-d H:i:s");
               $pass = $POST_PASS;

               $sql -> bindParam(':id',$id,PDO::PARAM_INT);
               $sql -> bindParam(':name',$name,PDO::PARAM_STR);
               $sql -> bindParam(':comment',$comment,PDO::PARAM_STR);
               $sql -> bindParam(':date',$DATE,PDO::PARAM_STR);
               $sql -> bindParam(':pass',$pass,PDO::PARAM_STR);

               $sql -> execute();
    }

/*************** 投稿情報保存処理 終了 *******************************/
?>

<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8">
    <title>mission_4</title>
    <style>
    #number {
        width: 3em;
    }
    </style>
  </head>
  <body>
    <form action="" method="post">
           名前:<input type="text" name="name" value='<?php echo $FORM_NAME;?>' placeholder="氏名"/>
           コメント:<input type="text" name="comment" value='<?php echo $FORM_COMMENT;?>' placeholder="コメント"/>
           パスワード：<input type="text" name="pass" value='<?php echo $FORM_PASS;?>' minlength="4" maxlength="4" placeholder="４文字" title="パスワードは４文字で入力してください。">
           <input type="submit" name="comment_button" value = "送信"></br>

           削除対象番号:<input type="number" name="deletenumber" min="1" placeholder="00" id="number"/>
           パスワード:<input type="text" name="pass_delete" minlength="4" maxlength="4" placeholder="４文字" title="パスワードは４文字で入力してください。">
           <input type="submit" name="delete_button" value = "削除"></br>

           編集対象番号:<input type="number" name="editnumber" min="1" value='<?php echo $DEFAULT_EDIT_NUMBER;?>' placeholder="00" id="number"/>
                        <input type="hidden" name="editflag" value='<?php echo $EDIT_FLAG?>' />
           パスワード:<input type="text" name="pass_edit" minlength="4" maxlength="4" placeholder="４文字" title="パスワードは４文字で入力してください。">
           <input type="submit" name="edit_button" value = "編集"></br>
    </form>

    <?php
        if($WRONG_PASS == 1){
            echo "<b>パスワードが間違っているため、編集できません。</b></br>";
        }

        //\\\\\\ コメント用DB 再読み込み \\\\\\\\\\\\\\\\\\\\\\\\\
        $COMMENT_sql_result = $pdo -> query($COMMENT_sql);
        $FILE = $COMMENT_sql_result -> fetchAll();

        if(!empty($FILE)){
            foreach($FILE as $FILES){
                $date = new DateTime($FILES['date']);

                echo $FILES['id']."　";
                echo $FILES['name']."　";
                echo $FILES['comment']."　";
                echo $date -> format('Y/m/d H:i:s')."</br>";
            }
            unset($FILES);
            unset($date);
        }
    ?>

  </body>
</html>






