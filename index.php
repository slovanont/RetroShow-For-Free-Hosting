<?php
require_once "init.php";
?>
<html>
<head>
<title>RetroShow</title>
<link rel="icon" href="favicon.ico">
</head>

<body link="#0000FF" alink="#0000FF" vlink="#0000FF">

<table width="700" align="center" cellpadding="5" cellspacing="0" border="1">

<tr>
<td colspan="2" bgcolor="#CCCCCC">
  <b>RetroShow</b> — Upload a Video
  <div align="right">
    <a href="index.php">Home</a> |
    <?php if (!isset($_SESSION['user_id'])): ?>
      <a href="register.php">Register</a> |
      <a href="login.php">Login</a>
    <?php else: ?>
      <a href="channel.php?user=<?=htmlspecialchars($_SESSION['user'])?>">My Channel</a> |
      <a href="upload.php">Upload Video</a> |
      <a href="logout.php">Logout</a>
    <?php endif; ?>
  </div>
</td>
</tr>

<tr><td colspan="2">
<b>Posted Video From Users:</b><br><br>

<?php
$page = max(1, intval($_GET['page'] ?? 1));
$per_page = 5;
$offset = ($page - 1) * $per_page;

$total = $db->query("SELECT COUNT(*) FROM videos")->fetchColumn();
$total_pages = ceil($total / $per_page);

$stmt = $db->query("
    SELECT 
        v.id,
        v.title,
        v.preview,
        u.login AS author
    FROM videos v
    JOIN users u ON u.id = v.user_id
    ORDER BY v.id DESC
    LIMIT $offset, $per_page
");

while ($row = $stmt->fetch()) {
    echo '<table cellpadding="3"><tr>';
    echo '<td><a href="video.php?id='.$row['id'].'">
          <img src="'.$row['preview'].'" width="120" height="90" border="2"></a></td>';
    echo '<td><b><a href="video.php?id='.$row['id'].'">'.
         htmlspecialchars($row['title']).'</a></b><br>';
    echo 'Автор: <a href="channel.php?user='.
         htmlspecialchars($row['author']).'">'.
         htmlspecialchars($row['author']).'</a></td>';
    echo '</tr></table>';
}
?>

</td></tr>
</table>
</body>
</html>
