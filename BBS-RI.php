<?php
    //最初の準備
    //DB接続設定//
    $dsn = 'データベース名';
    $user = 'ユーザー名';
    $password = 'パスワード';
    $pdo = new PDO($dsn, $user, $password,
        array(PDO::ATTR_ERRMODE => PDO::ERRMODE_WARNING));
    
    //データベース内にテーブルを作成//
    //IF～は「もしまだこのテーブルが存在しないなら」という意味を持つ
    //これがないと2回目以降エラーが発生する
    $sql = "CREATE TABLE IF NOT EXISTS tbtest"
	." ("
	. "id INT AUTO_INCREMENT PRIMARY KEY,"  //自動で登録されているナンバリング
	. "name char(32),"  //名前を入れる。文字列、半角英数で32文字
	. "comment TEXT,"    //コメントを入れる。文字列、長めの文章も入る
	. "date char(20),"  //日付を入れる。文字列、半角英数で20文字
	. "pass TEXT"       //パスワードを入れる。文字列、長めの文章も入る
	.");";
	$stmt = $pdo->query($sql);


    //通常の投稿フォーム
	//INSERT文：データを入力（データレコードの挿入）
	if(strlen($_POST["name"])&&strlen($_POST["text"])&&empty($_POST["hiddeneditnum"])==1){
	    $sql = $pdo -> prepare("INSERT INTO tbtest (name, comment, date, pass) VALUES (:name, :comment, :date, :pass)");
    	$sql -> bindParam(':name', $name, PDO::PARAM_STR);
    	$sql -> bindParam(':comment', $comment, PDO::PARAM_STR);
    	$sql -> bindParam(':date', $date, PDO::PARAM_STR);
    	$sql -> bindParam(':pass', $pass, PDO::PARAM_STR);
    	
	    $name = $_POST["name"];
    	$comment = $_POST["text"];
    	$date = date("Y/m/d H:i:s");
    	$pass = $_POST["pass"];
	    $sql -> execute();
    	//bindParamの引数名（:name など）はテーブルのカラム名に併せるとミスが少なくなります。最適なものを適宜決めよう。

        //SELECT文：入力したデータレコードを抽出し、表示する
        //$rowの添字（[ ]内）は、4-2で作成したカラムの名称に併せる必要があります。
    	$sql = 'SELECT * FROM tbtest';
	    $stmt = $pdo->query($sql);
    	$results = $stmt->fetchAll();
	    foreach ($results as $row){
    		//$rowの中にはテーブルのカラム名が入る
	    	echo $row['id'].',';
    		echo $row['name'].',';
	    	echo $row['comment'].',';
	    	echo $row['date'].'<br>';
        	echo "<hr>";
	    }
	    
	    echo "コメントを受け付けました<br>";
	}
		
	//削除フォーム
	//DELETE文：入力したデータレコードを削除
	elseif(empty($_POST["delnum"])==0&&$_POST["delpass"]!=NULL){
	    $id = $_POST["delnum"];
	    $pass = $_POST["delpass"];
	    $sql = 'SELECT pass FROM tbtest WHERE id=:id';
	    $stmt = $pdo->prepare($sql);                  // ←差し替えるパラメータを含めて記述したSQLを準備し、
        $stmt->bindParam(':id', $id, PDO::PARAM_INT); // ←その差し替えるパラメータの値を指定してから、
        $stmt->execute();                             // ←SQLを実行する。
        $results = $stmt -> fetchAll();
        $a = 0;
        
        //passが同じなら消去
        if($results[0]['pass']==$pass){
            $sql = 'delete from tbtest where id=:id';
    	    $stmt = $pdo->prepare($sql);
        	$stmt->bindParam(':id', $id, PDO::PARAM_INT);
	        $stmt->execute();
	        $a =1;
        }
        
        //結果を表示
        $sql = 'SELECT * FROM tbtest';
	    $stmt = $pdo->query($sql);
    	$results = $stmt->fetchAll();
	    foreach ($results as $row){
    		//$rowの中にはテーブルのカラム名が入る
	    	echo $row['id'].',';
	    	echo $row['name'].',';
	    	echo $row['comment'].',';
	    	echo $row['date'].'<br>';
        	echo "<hr>";
    	}
    	
    	//削除したかどうかを判定
    	if($a == 1){
    	    echo "削除を受け付けました。<br>";
    	}
    	else{
    	    echo "投稿が存在しないか、パスワードが異なります。<br>";
    	}
    }
	

    //編集フォーム
    //UPDATE文：入力されているデータレコードの内容を編集
    //bindParamの引数（:nameなど）は4-2でどんな名前のカラムを設定したかで変える必要がある。
    elseif(empty($_POST["editnum"])==0&&$_POST["editpass"]!=NULL){
        //指定されたidの投稿を抽出
        $id = $_POST["editnum"]; //変更する投稿番号
        $editpass =$_POST["editpass"];
        $sql = 'SELECT * FROM tbtest WHERE id=:id ';
        $stmt = $pdo->prepare($sql);                  // ←差し替えるパラメータを含めて記述したSQLを準備し、
        $stmt->bindParam(':id', $id, PDO::PARAM_INT); // ←その差し替えるパラメータの値を指定してから、
        $stmt->execute();                             // ←SQLを実行する。
    	$results = $stmt->fetchAll();
    	$a =0;
        
        //passが正しいか判定し、投稿番号とコメントを変数に代入
        if($results[0]['pass']==$editpass){
            $editnum = $id;
            $editname = $results[0]['name'];
            $editcomment = $results[0]['comment'];
            $a = 1;
        }
        
        //表示用
        $sql = 'SELECT * FROM tbtest';
	    $stmt = $pdo->query($sql);
    	$results = $stmt->fetchAll();
	    foreach ($results as $row){
    		//$rowの中にはテーブルのカラム名が入る
	    	echo $row['id'].',';
    		echo $row['name'].',';
	    	echo $row['comment'].',';
	    	echo $row['date'].'<br>';
        	echo "<hr>";
	    }
	    
	    if($a ==1){
	        echo "下記フォームより、変更内容を送信してください。パスワードも変更されます。<br>";
	    }
	    else{
	        echo "投稿が存在しないか、パスワードが異なります。<br>";
	    }
    }
    
    //編集モードによる投稿
    elseif(strlen($_POST["name"])&&strlen($_POST["text"])&&empty($_POST["hiddeneditnum"])==0){
        $id = $_POST["hiddeneditnum"];   //編集する投稿番号
        $name = $_POST["name"];     //編集する名前
        $comment =$_POST["text"];
    	$date = date("Y/m/d H:i:s");
    	$pass = $_POST["pass"];

        $sql = 'UPDATE tbtest SET name=:name,comment=:comment,date=:date,pass=:pass WHERE id=:id';
    	$stmt = $pdo->prepare($sql);
	    $stmt->bindParam(':name', $name, PDO::PARAM_STR);
    	$stmt->bindParam(':comment', $comment, PDO::PARAM_STR);
	    $stmt->bindParam(':date', $date, PDO::PARAM_STR);
	    $stmt->bindParam(':pass', $pass, PDO::PARAM_STR);
	    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    	$stmt->execute();
    	
    	//表示用
    	$sql = 'SELECT * FROM tbtest';
	    $stmt = $pdo->query($sql);
    	$results = $stmt->fetchAll();
	    foreach ($results as $row){
    		//$rowの中にはテーブルのカラム名が入る
	    	echo $row['id'].',';
    		echo $row['name'].',';
	    	echo $row['comment'].',';
	    	echo $row['date'].'<br>';
        	echo "<hr>";
	    }
	    
	    $editnum = NULL;
	    
	    echo "編集を受け付けました。<br>";
    	
     }
    
    //最初に見せる用
    else{
    	$sql = 'SELECT * FROM tbtest';
	    $stmt = $pdo->query($sql);
    	$results = $stmt->fetchAll();
	    foreach ($results as $row){
    		//$rowの中にはテーブルのカラム名が入る
	    	echo $row['id'].',';
    		echo $row['name'].',';
	    	echo $row['comment'].',';
	    	echo $row['date'].'<br>';
        	echo "<hr>";
	    }
    }
		
    ?>


<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>BBS-RI</title>
</head>
<body>
        <form action="" method="post">
        <input type="text" name="name" placeholder = "名前" value = "<?php echo $editname;?>"> 
        <input type="text" name="text" placeholder = "コメント" value = "<?php echo $editcomment;?>">
        <input type="text" name="pass" placeholder = "パスワード">
        <input type="hidden" name="hiddeneditnum" value = "<?php echo $editnum;?>">
        <input type="submit" name="submit">
        <br>
        
        <input type="number" name="delnum" placeholder="削除対象番号" min="1" max = "<?php $num;?>"> 
        <input type="text" name="delpass" placeholder = "削除する投稿のパスワード">
        <input type="submit" name="delete" value="削除">
        <br>
        
        <input type="number" name="editnum" placeholder="編集対象番号" min="1" max = "<?php $num;?>">
        <input type="text" name="editpass" placeholder = "編集する投稿のパスワード">
        <input type="submit" name="edit" value="編集">
        <br>
        
    ※パスワードなしの投稿は削除も編集もできません

</body>
</html>
