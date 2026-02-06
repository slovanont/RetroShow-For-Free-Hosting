<?php
require_once "init.php";

$user = $_GET['user'] ?? '';
if ($user === '') die("This channel don't exist");

$stmt = $db->prepare("SELECT id FROM users WHERE login = ?");
$stmt->execute([$user]);
$owner = $stmt->fetch();

if (!$owner) die("Пользователь не найден");
$user_id = $owner['id'];
?>
<html>
<head>
<title>Канал <?=htmlspecialchars($user)?></title>
<link rel="icon" href="favicon.ico">
</head>

<body>

<table width="700" align="center" border="1" cellpadding="5">

<tr>
<td bgcolor="#CCCCCC">
<b>Канал:</b> <?=htmlspecialchars($user)?>
<div align="right"><a href="index.php">Home</a></div>
</td>
</tr>

<tr><td>
<?php
$stmt = $db->prepare("
    SELECT id, title, preview
    FROM videos
    WHERE user_id = ?
    ORDER BY id DESC
");
$stmt->execute([$user_id]);

while ($v = $stmt->fetch()) {
    echo '<table cellpadding="3"><tr>';
    echo '<td><a href="video.php?id='.$v['id'].'">
          <img src="'.$v['preview'].'" width="120" height="90"></a></td>';
    echo '<td><b><a href="video.php?id='.$v['id'].'">'.
         htmlspecialchars($v['title']).'</a></b></td>';
    echo '</tr></table>';
}
?>
</td></tr>
</table>

</body>
</html>
