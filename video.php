<?php
require_once "init.php";

$id = intval($_GET['id'] ?? 0);

$stmt = $db->prepare("SELECT * FROM videos WHERE id = ?");
$stmt->execute([$id]);
$video = $stmt->fetch();

if (!$video) {
    die("Видео не найдено");
}
?>
<html>
<head>
<title><?=htmlspecialchars($video['title'])?></title>
<link rel="shortcut icon" href="favicon.ico" type="image/x-icon">
<script type="text/javascript" src="jwplayer/jwplayer.js"></script>
</head>

<body link="#0000FF" alink="#0000FF" vlink="#0000FF">

<table width="700" align="center" cellpadding="5" cellspacing="0" border="1">

<tr>
<td colspan="2" bgcolor="#CCCCCC">
  <b>RetroShow</b> — Upload Video
  <div align="right">
    <a href="index.php">Home</a> |
    <?php if (!isset($_SESSION['user'])): ?>
      <a href="register.php">Register</a> |
      <a href="login.php">Login</a>
    <?php else: ?>
      <a href="channel.php?user=<?=htmlspecialchars($_SESSION['user'])?>">My Channel</a> |
      <a href="upload.php">Upload Video</a> |
      <a href="logout.php">logout</a>
    <?php endif; ?>
  </div>
</td>
</tr>

<tr><td colspan="2">

<table width="100%" border="1" cellpadding="5">

<tr>
<td bgcolor="#CCCCCC">
  <b><?=htmlspecialchars($video['title'])?></b>
  — автор:
  <a href="channel.php?user=<?=htmlspecialchars($video['user'])?>">
    <?=htmlspecialchars($video['user'])?>
  </a>
</td>
<td bgcolor="#CCCCCC" align="right">
  <em><?=htmlspecialchars($video['time'])?></em>
</td>
</tr>

<tr>
<td colspan="2">

<div id="mediaplayer"></div>

<script type="text/javascript">
jwplayer("mediaplayer").setup({
    file: "<?=htmlspecialchars($video['file'])?>",
    image: "<?=htmlspecialchars($video['preview'])?>",
    width: 425,
    height: 344,
    controlbar: "bottom",
    modes: [
        { type: "html5" },
        { type: "flash", src: "jwplayer/player.swf" }
    ]
});
</script>

<br>


<a href="<?=htmlspecialchars($video['file'])?>" download>
    Download Video(Original Format)
</a>

</td>
</tr>

<?php if (!empty($video['description'])): ?>
<tr>
<td colspan="2" bgcolor="#F5F5F5">
<b>Description:</b><br>
<?=nl2br(htmlspecialchars($video['description']))?>
</td>
</tr>
<?php endif; ?>

</table>

</td></tr>
</table>

</body>
</html>
