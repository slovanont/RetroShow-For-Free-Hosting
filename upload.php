<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . "/init.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');

    if ($title === '') {
        $error = "Введите название видео.";
    } elseif (strlen($description) > 5000) {
        $error = "Описание не должно превышать 5000 символов.";
    } elseif (!isset($_FILES['video']) || $_FILES['video']['error'] !== UPLOAD_ERR_OK) {
        $error = "Ошибка при загрузке файла.";
    } else {

        $maxSize = 50 * 1024 * 1024; // 50 MB
        if ($_FILES['video']['size'] > $maxSize) {
            $error = "Файл слишком большой (макс. 50 MB).";
        } else {

            $allowed = ['mp4','avi','mov','wmv','flv','mp3'];
            $ext = strtolower(pathinfo($_FILES['video']['name'], PATHINFO_EXTENSION));

            if (!in_array($ext, $allowed)) {
                $error = "Формат файла не поддерживается.";
            } else {

                $stmt = $db->query("SELECT MAX(id)+1 FROM videos");
                $next_id = (int)$stmt->fetchColumn();
                if ($next_id <= 0) $next_id = 1;

                $video_dir  = "uploads/videos/";
                $thumb_dir  = "uploads/thumbs/";

                if (!is_dir($video_dir)) mkdir($video_dir, 0755, true);
                if (!is_dir($thumb_dir)) mkdir($thumb_dir, 0755, true);

                $video_file   = $video_dir . $next_id . "." . $ext;
                $preview_file = $thumb_dir . $next_id . ".jpg";

                if (!move_uploaded_file($_FILES['video']['tmp_name'], $video_file)) {
                    $error = "Ошибка при сохранении видео.";
                } else {

                    /* ===== getID3 (SEM ffmpeg) ===== */
                    require_once __DIR__ . "/getid3/getid3.php";
                    $getID3 = new getID3();
                    $getID3->option_md5_data = false;
                    $getID3->option_md5_data_source = false;

                    $info = $getID3->analyze($video_file);
                    $duration = isset($info['playtime_seconds'])
                        ? (int)$info['playtime_seconds']
                        : 0;

                    /* ===== PREVIEW ===== */
                    if (isset($_FILES['preview']) && $_FILES['preview']['size'] > 0) {
                        move_uploaded_file($_FILES['preview']['tmp_name'], $preview_file);
                    } else {
                        copy("uploads/thumbs/default.png", $preview_file);
                    }

                    /* ===== INSERT ===== */
                    $time = date("Y-m-d H:i:s");

                    $stmt = $db->prepare("
                        INSERT INTO videos
                        (title, description, file, preview, user_id, time, duration)
                        VALUES (?, ?, ?, ?, ?, ?, ?)
                    ");

                    $stmt->execute([
                        $title,
                        $description,
                        $video_file,
                        $preview_file,
                        $_SESSION['user_id'],
                        $time,
                        $duration
                    ]);

                    $success = "Видео успешно загружено! <a href='index.php'>На главную</a>";
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
<title>Загрузка видео</title>
<link rel="icon" href="favicon.ico">
</head>

<body link="#0000FF" alink="#0000FF" vlink="#0000FF">

<table width="700" align="center" cellpadding="5" cellspacing="0" border="1">
<tr>
<td colspan="2" bgcolor="#CCCCCC">
<b>RetroShow</b> — Upload a Video
<div align="right">
<a href="index.php">Home</a> |
<a href="channel.php?user=<?=htmlspecialchars($_SESSION['user'])?>">My Channel</a> |
<a href="upload.php">Upload Video</a> |
<a href="logout.php">Logout</a>
</div>
</td>
</tr>

<tr><td colspan="2">

<b>Upload Video:</b>

<?php if ($error): ?>
<br><em><?=htmlspecialchars($error)?></em><br><br>
<?php endif; ?>

<?php if ($success): ?>
<br><em><?=$success?></em><br><br>
<?php endif; ?>

<form method="post" enctype="multipart/form-data">
<table align="center" border="1" cellpadding="5">

<tr>
<td>Title:</td>
<td><input type="text" name="title" value="<?=htmlspecialchars($_POST['title'] ?? '')?>"></td>
</tr>

<tr>
<td>Description:</td>
<td>
<textarea name="description" rows="5" cols="50"><?=htmlspecialchars($_POST['description'] ?? '')?></textarea><br>
<small>Maximum 5000 characters</small>
</td>
</tr>

<tr>
<td>Video:</td>
<td>
<input type="file" name="video" accept="video/*,audio/*"><br>
<small>No conversion</small>
</td>
</tr>

<tr>
<td>Thumbnail:</td>
<td>
<input type="file" name="preview"><br>
<small>Optional or will gonna use the default thumbnail</small>
</td>
</tr>

<tr>
<td colspan="2" align="center">
<input type="submit" value="Upload Video">
</td>
</tr>

</table>
</form>

</td></tr>
</table>

</body>
</html>
