<?php
require __DIR__.'/config.php';
session_start();
$_SESSION['csrf'] ??= bin2hex(random_bytes(16));
$msg = $err = '';

if ($_SERVER['REQUEST_METHOD']==='POST') {
  if (!hash_equals($_SESSION['csrf'], $_POST['csrf'] ?? '')) exit('Bad request.');
  $a = $_POST['action'] ?? '';
  if ($a==='login') {
    if (hash_equals(ADMIN_PASSWORD, $_POST['password'] ?? '')) { session_regenerate_id(true); $_SESSION['ok']=1; } else $err='Wrong password.';
  } elseif (!empty($_SESSION['ok'])) {
    $links = load_links();
    $f = fn($k)=>trim($_POST[$k] ?? '');
    if ($a==='settings') { $s=load_settings(); foreach(array_keys($s) as $k) $s[$k]=$f($k); save_settings($s); $msg='Page details saved.'; }
    if ($a==='logout') { session_destroy(); header('Location: admin.php'); exit; }
    if ($a==='add' || $a==='update') {
      $row = ['title'=>$f('title'),'tag'=>preg_replace('/[^A-Za-z0-9]/','',$f('tag')) ?: 'Cohort','desc'=>$f('desc'),'url'=>$f('url'),'icon'=>$f('icon') ?: '</>','active'=>isset($_POST['active'])];
      if (!$row['title'] || !valid_url($row['url'])) $err='Add a title and a valid http(s) link.';
      elseif ($a==='add') { $links[] = $row + ['id'=>bin2hex(random_bytes(4)),'clicks'=>0]; save_links($links); $msg='Cohort added.'; }
      else foreach ($links as &$l) if ($l['id']===$f('id')) { $l = $row + ['id'=>$l['id'],'clicks'=>$l['clicks'] ?? 0]; save_links($links); $msg='Changes saved.'; }
    }
    if ($a==='delete') { $links = array_filter($links, fn($l)=>$l['id']!==$f('id')); save_links($links); $msg='Cohort removed.'; }
    if ($a==='move') {
      $links = array_values($links); $i = array_search($f('id'), array_column($links,'id'));
      $j = $i + ($f('dir')==='up' ? -1 : 1);
      if ($i!==false && isset($links[$j])) { [$links[$i],$links[$j]] = [$links[$j],$links[$i]]; save_links($links); }
    }
  }
}
$in = !empty($_SESSION['ok']);
function fields($l=[]){ ?>
  <div class="grid">
    <div><label>Title</label><input name="title" required value="<?=e($l['title']??'')?>"></div>
    <div><label>Funnel tag (one word, shown as &lt;Tag/&gt;)</label><input name="tag" value="<?=e($l['tag']??'')?>"></div>
    <div class="full"><label>Short description</label><textarea name="desc"><?=e($l['desc']??'')?></textarea></div>
    <div class="full"><label>Destination link (WhatsApp or any URL)</label><input name="url" type="url" required value="<?=e($l['url']??'')?>"></div>
    <div><label>Icon (1–3 characters, e.g. &lt;/&gt;)</label><input name="icon" maxlength="3" value="<?=e($l['icon']??'')?>"></div>
    <div><label>&nbsp;</label><label style="color:var(--ink)"><input type="checkbox" name="active" style="width:auto" <?=($l['active']??true)?'checked':''?>> Visible on the page</label></div>
  </div>
<?php } ?>
<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<meta name="robots" content="noindex"><title>Manage cohorts · <?=e(BRAND)?></title>
<link rel="preload" href="fonts/Alro-Bold.woff2" as="font" type="font/woff2" crossorigin>
<link rel="stylesheet" href="style.css">
<link rel="icon" href="favicon.ico" sizes="any">
<link rel="icon" type="image/png" sizes="32x32" href="favicon-32.png">
<link rel="apple-touch-icon" href="apple-touch-icon.png">
</head><body><main class="wrap admin">
<img src="logo.png" alt="<?=e(BRAND)?>" style="height:46px;mix-blend-mode:lighten">
<h1 style="margin-top:6px">Manage cohorts</h1>
<?php if($msg):?><div class="msg"><?=e($msg)?></div><?php endif; if($err):?><div class="msg err"><?=e($err)?></div><?php endif; ?>

<?php if(!$in): ?>
<form method="post" class="panel"><h2>Admin login</h2>
  <input type="hidden" name="csrf" value="<?=e($_SESSION['csrf'])?>"><input type="hidden" name="action" value="login">
  <label>Password</label><input type="password" name="password" required autofocus>
  <div class="row"><button class="sm pri">Log in</button></div>
</form>
<?php else: ?>
<div class="row"><a class="sm" href="index.php" target="_blank">View live page</a>
 <form method="post" style="margin-left:auto"><input type="hidden" name="csrf" value="<?=e($_SESSION['csrf'])?>"><input type="hidden" name="action" value="logout"><button class="sm">Log out</button></form></div>

<form method="post" class="panel"><h2>Add a cohort</h2>
  <input type="hidden" name="csrf" value="<?=e($_SESSION['csrf'])?>"><input type="hidden" name="action" value="add">
  <?php fields(); ?><div class="row"><button class="sm pri">Add cohort</button></div>
</form>

<form method="post" class="panel"><details><summary><b>Page details</b><span class="stat">badge, start date, contacts</span></summary>
  <input type="hidden" name="csrf" value="<?=e($_SESSION['csrf'])?>"><input type="hidden" name="action" value="settings">
  <div class="grid"><?php foreach(load_settings() as $k=>$v): ?><div class="<?=strlen($v)>30?'full':''?>"><label><?=e(ucwords(str_replace('_',' ',$k)))?></label><input name="<?=e($k)?>" value="<?=e($v)?>"></div><?php endforeach; ?></div>
  <div class="row"><button class="sm pri">Save details</button></div></details></form>

<?php foreach(load_links() as $l): ?>
<div class="panel"><details>
  <summary><span style="font-size:22px"><?=e($l['icon'])?></span><b><?=e($l['title'])?></b>
    <?php if(!$l['active']):?><span class="stat" style="color:var(--accent)">hidden</span><?php endif;?>
    <span class="stat mono"><?=(int)$l['clicks']?> joins</span></summary>
  <form method="post"><input type="hidden" name="csrf" value="<?=e($_SESSION['csrf'])?>"><input type="hidden" name="id" value="<?=e($l['id'])?>">
    <?php fields($l); ?>
    <div class="row"><button class="sm pri" name="action" value="update">Save changes</button>
      <button class="sm" name="action" value="move" onclick="this.form.dir.value='up'">Move up</button>
      <button class="sm" name="action" value="move" onclick="this.form.dir.value='down'">Move down</button>
      <input type="hidden" name="dir">
      <button class="sm del" name="action" value="delete" style="margin-left:auto" onclick="return confirm('Remove this cohort?')">Remove</button></div>
  </form>
</details></div>
<?php endforeach; endif; ?>
</main></body></html>
