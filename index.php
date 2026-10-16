<?php
require __DIR__.'/config.php';
$all = load_links(); $S = load_settings();
$links = array_values(array_filter($all, fn($l)=>!empty($l['active'])));
if (isset($_GET['go'])) {
  foreach ($all as &$l) if ($l['id']===$_GET['go'] && valid_url($l['url'])) {
    $l['clicks'] = ($l['clicks'] ?? 0) + 1; save_links($all);
    header('Location: '.$l['url'], true, 302); exit;
  }
  http_response_code(404); exit('Link not found.');
}
?>
<!doctype html>
<html lang="en"><head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Join the free <?=e(BRAND)?> cohort</title>
<meta name="description" content="Pick your track and join the free online cohort with <?=e(BRAND)?>.">
<meta name="theme-color" content="#020604">
<link rel="preload" href="fonts/Alro-Bold.woff2" as="font" type="font/woff2" crossorigin>
<link rel="stylesheet" href="style.css">
<link rel="icon" href="favicon.ico" sizes="any">
<link rel="icon" type="image/png" sizes="32x32" href="favicon-32.png">
<link rel="apple-touch-icon" href="apple-touch-icon.png">
</head><body><main class="wrap">
<div class="top"><img src="logo.png" alt="<?=e(BRAND)?>"><a href="https://<?=e($S['site'])?>"><?=e($S['site'])?></a></div>

<section class="step on" id="s1" style="margin-top:0">
  <span class="badge"><?=e($S['badge'])?></span>
  <h1><small>Join Our</small>Free Online<br><em>Cohort</em></h1>
  <p class="lead">Learn the digital skills businesses are hiring for, live with <?=e(BRAND)?>. Build real projects and grow with us.</p>
  <div style="margin-top:30px">
    <?php if(!$links): ?><div class="empty">No cohorts are open right now. Check back soon.</div><?php endif; ?>
    <?php foreach($links as $i=>$l): ?>
      <button class="card" data-i="<?=$i?>"><span class="num"><?=sprintf('%02d',$i+1)?></span><b><?=e($l['title'])?></b><span class="ar"><svg width="1em" height="1em" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M7 17L17 7M8 7h9v9"/></svg></span></button>
    <?php endforeach; ?>
  </div>
  <div class="pill"><span><?=e($S['start_label'])?></span><i></i><?=e($S['start_text'])?></div>
</section>

<section class="step" id="s2" aria-live="polite">
  <span class="badge">You picked</span>
  <div class="sel" style="margin-top:22px">
    <div class="t"><span id="n"></span><span class="ic" id="ic"></span></div>
    <div><h2 id="tt"></h2><p id="td"></p></div>
  </div>
  <ul class="list" id="steps">
    <li>Tap the button to open the WhatsApp group.</li>
    <li>Tap “Join group” and read the pinned rules.</li>
    <li>Introduce yourself and get ready for the first session.</li>
  </ul>
  <a class="btn" id="go" href="#"><span id="golabel">Join the WhatsApp group</span> <svg width="1em" height="1em" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M7 17L17 7M8 7h9v9"/></svg></a>
  <button class="btn ghost" id="back">Choose a different cohort</button>
</section>

<footer>
  <div><?=e($S['email'])?><br><?=e($S['social'])?></div><b><?=e($S['hashtag'])?></b>
</footer>
</main>
<script>
const L = <?=json_encode(array_map(fn($l)=>['id'=>$l['id'],'t'=>$l['title'],'d'=>$l['desc'],'ic'=>$l['icon'],'u'=>$l['url'],'cta'=>trim($l['cta'] ?? ''),'st'=>array_values($l['steps'] ?? [])],$links),JSON_HEX_TAG|JSON_HEX_AMP)?>;
const WA_STEPS=['Tap the button to open the WhatsApp group.','Tap “Join group” and read the pinned rules.','Introduce yourself and get ready for the first session.'];
const WEB_STEPS=['Tap the button to open the website in your browser.','Explore what '+<?=json_encode(BRAND)?>+' has to offer.','Come back here any time for more links.'];
const isWA=u=>/^https?:\/\/(chat\.whatsapp\.com|wa\.me)\//i.test(u||'');
const $=s=>document.querySelector(s), show=n=>{document.querySelectorAll('.step').forEach(x=>x.classList.toggle('on',x.id===n));scrollTo({top:0})};
document.querySelectorAll('.card').forEach(c=>c.onclick=()=>{
  const i=+c.dataset.i,l=L[i]; $('#n').textContent=String(i+1).padStart(2,'0'); $('#ic').textContent=l.ic;
  $('#tt').textContent=l.t; $('#td').textContent=l.d;
  const wa=isWA(l.u);
  $('#golabel').textContent=l.cta||(wa?'Join the WhatsApp group':'Visit Website');
  const steps=(l.st&&l.st.length)?l.st:(wa?WA_STEPS:WEB_STEPS), ul=$('#steps');
  ul.textContent=''; steps.forEach(s=>{const li=document.createElement('li'); li.textContent=s; ul.appendChild(li);});
  $('#go').href='?go='+encodeURIComponent(l.id); show('s2');
});
$('#back').onclick=()=>show('s1');
</script></body></html>
