<?php
// DB接続のためのコード
$host = 'localhost';
$user = 'root';
$password = 'root';
$db_name = 'MYSQL-rennshuu';

$dsn = "mysql:dbname=$db_name;host=$host;charset=utf8";
$pdo = new PDO($dsn, $user, $password);


// 投稿のためのもの（エラー機能つき）
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $title = $_POST["title"];
    $message = $_POST["message"];
    $errors = []; // エラーメッセージを格納する配列
  
     if (empty(trim($title))) { $errors[] = "タイトルを入力してください。"; } // タイトルが空白の場合
     if (strlen($title) > 30) { $errors[] = "タイトルは30文字以内で入力してください。"; } // タイトルが30文字以上の場合
     if (empty(trim($message))) { $errors[] = "投稿内容を入力してください。"; } // 投稿内容が空白の場合
  
     if (empty($errors)) {
        $sql = "INSERT INTO contents (title, message) VALUES (:title, :message)";
        $stmt = $pdo->prepare($sql);
        $params = array(':title' => $title, ':message' => $message);
        $stmt->execute($params);
        header("Location: index.php"); // リロード時の再投稿防止
        exit();
     } else {
         $error = current($errors); // 配列の最初の要素を取得
         while ($error !== false) { // 現在の要素がfalseでない間ループ
             echo "<p style='color: red;'>{$error}</p>";
             $error = next($errors); // 次の要素に移動
         }
     }
 }
?>


<!DOCTYPE html>
<html lang="ja">
<head>
   <meta charset="UTF-8">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Laravel News</title>

   <script>
       // フォーム送信時の確認ダイアログ
       function confirmSubmit() {
           // confirm関数で「OK」が押されたらtrue、それ以外はfalseを返す
           return confirm('本当に投稿しますか？');
       }
    </script>
</head>
<body>
    <h1>Laravel News</h1>
    <form action="index.php" method="post" onsubmit="return confirmSubmit();">
        <label for="title">タイトル:</label>
        <input type="text" id="title" name="title">
        <br><br>
        <label for="message">投稿内容:</label>
        <textarea id="message" name="message"></textarea>
        <br><br>
        <input type="submit" value="投稿">
    </form>

    <h2>投稿一覧</h2>

    <?php
        $result_list = $pdo->query('SELECT * FROM contents');
        foreach ( $result_list as $row ):
            echo "タイトル: {$row['title']} <br>";
            echo "投稿内容: {$row['message']} <br>";
            echo "<a href=show.php?no={$row['no']}>詳細画面へ</a><br>";
        endforeach;
    ?>


</body>
</html>