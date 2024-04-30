<?php
// DB接続のためのコード
$host = 'localhost';
$user = 'root';
$password = 'root';
$db_name = 'MYSQL-rennshuu';

$dsn = "mysql:dbname=$db_name;host=$host;charset=utf8";
$pdo = new PDO($dsn, $user, $password);

$postNo = $_GET['no']; //投稿IDを取得

$stmt = $pdo->prepare("SELECT title, message FROM contents WHERE no = :no");
$stmt->execute(['no' => $postNo]);

if($row = $stmt->fetch(PDO::FETCH_ASSOC)){
    $post_title = "<h2>".$row['title']."</h2>";
    $post_message = "<p>".$row['message']."</p>";
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {  

    if (isset($_POST["commentText"])){
        $commentText = $_POST["commentText"];
        $errors = []; // エラーメッセージを格納する配列
    
        if (empty(trim($commentText))) { $errors[] = "コメントを入力してください。"; } // コメントが空白の場合
        if (strlen($commentText) > 50) { $errors[] = "コメントは50文字以内で入力してください。"; } // コメントが50文字以上の場合
        if (empty($errors)) {
            $sql = "INSERT INTO comments (contents_no, commentText) VALUES (:post_No, :commentText)";
            $stmt = $pdo->prepare($sql);
            $params = array(':post_No' => $postNo, ':commentText' => $commentText);
            $stmt->execute($params);
            header("Location:show.php?no=$postNo"); // リロード時の再投稿防止
            exit();
        } else {
            $error = current($errors); // 配列の最初の要素を取得
            while ($error !== false) { // 現在の要素がfalseでない間ループ
                echo "<p style='color: red;'>{$error}</p>";
                $error = next($errors); // 次の要素に移動
            }
        }
    }
    if(isset($_POST['delete_comment'])){

        $delete_comment = $_POST["delete_comment"];
        $sql = "DELETE FROM comments WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $params = array(':id' => $delete_comment);
        $stmt->execute($params);
        header("Location:show.php?no=$postNo"); // リロード時の再投稿防止
        exit();
    }
}

 // コメント一覧機能
$commentDetails = [];

$sql = "SELECT id, contents_no, commentText FROM comments ORDER BY id DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute();

// 取得したデータを $postDetails 配列に格納
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    if ($row['contents_no'] == $postNo) { // この投稿IDに関連するコメントのみを格納
        $commentDetails[] = [
            'id' => $row['id'],
            'commentText' => $row['commentText'],
            'postNo' => $row['contents_no']
        ];
    }
}
?>


<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>


<body>
    <h1>投稿詳細🌟</h1>
    <?php echo $post_title; ?>
    <?php echo $post_message; ?><br><br>

    <form action="show.php?no=<?php echo $postNo; ?>" method="post" onsubmit="return confirmSubmit();">
        <label for="commentText">コメント:</label>
        <textarea id="commentText" name="commentText"></textarea>
        <br><br>
        <input type="submit" value="送信">
    </form>

    <?php
    if (empty($commentDetails)) {
        echo "<p>まだコメントがありません。</p>";
    } else {
        $index = 0;
        while ($index < count($commentDetails)) {
            $comment = $commentDetails[$index];
            echo "<p>".$comment['commentText']."</p>";
            echo '<form action="show.php?no='.$postNo.'" method="post">';
            echo '<label for="delete_comment"></label>';
            echo '<input type="hidden" name="delete_comment" value="'.$comment["id"].'">';
            echo '<input type="submit" value="削除">';
            echo '</form>';
            $index++;
        }
    }
    ?>
</body>

<script>
       // フォーム送信時の確認ダイアログ
       function confirmSubmit() {
           // confirm関数で「OK」が押されたらtrue、それ以外はfalseを返す
           return confirm('本当にコメントしますか？');
       }
    </script>

</html>