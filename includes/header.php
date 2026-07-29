<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/functions.php';

$logged_in = isLoggedIn();
$current_user = ($logged_in && !empty($_SESSION['user_id'])) ? getUser() : null;
if ($logged_in && !$current_user) {
    $db_fallback = Database::getInstance();
    $current_user = $db_fallback->fetch("SELECT * FROM admins WHERE id = ?", [$_SESSION['user_id']]);
}
$cart_count = getCartCount();
$unread_notifications = 0;
if ($logged_in) {
    $db_notif = Database::getInstance();
    $notif_row = $db_notif->fetch("SELECT COUNT(*) as cnt FROM notifications WHERE user_id = ? AND is_read = 0", [$_SESSION['user_id']]);
    $unread_notifications = $notif_row ? (int)$notif_row['cnt'] : 0;
}
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?= SITE_NAME ?> - AI Powered Smart Shopping Experience">
    <title><?= isset($page_title) ? htmlspecialchars($page_title) . ' | ' . SITE_NAME : SITE_NAME . ' - AI Powered Shopping' ?></title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css">
    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
    <link href="<?= BASE_URL ?>assets/css/style.css?v=4" rel="stylesheet">
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark sticky-top">
    <div class="container">
        <a class="navbar-brand" href="<?= BASE_URL ?>" style="display:flex;flex-direction:column;align-items:center;text-decoration:none;perspective:600px;">
            <div class="logo-3d-wrap" style="transform-style:preserve-3d;">
                <img src="<?= BASE_URL ?>assets/images/shopsphere logo.jpeg" alt="<?= SITE_NAME ?>" class="logo-3d-img" style="height:32px;width:auto;transform:translateZ(8px);transition:transform 0.4s cubic-bezier(0.23,1,0.32,1);filter:drop-shadow(0 2px 6px rgba(244,114,182,0.3));">
                <div class="logo-3d-glow"></div>
            </div>
            <small class="logo-3d-name" style="color:#4F8EF7;font-weight:800;font-size:0.85rem;line-height:1;margin-top:2px;transform:translateZ(4px);transition:all 0.3s ease;"><?= SITE_NAME ?></small>
        </a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="mainNav">
            <form class="mx-auto my-0" action="<?= BASE_URL ?>products.php" method="GET" id="navSearchForm" style="max-width:500px;width:100%;position:relative;">
                <div class="sb-3d-wrap">
                    <div class="sb-3d-glow"></div>
                    <div class="sb-3d-inner">
                        <div class="sb-3d-icon"><i class="fas fa-search"></i></div>
                        <input type="text" name="q" id="navSearchInput" placeholder="Search products, brands..." autocomplete="off" class="sb-3d-input">
                        <button type="submit" class="sb-3d-btn" id="navSearchBtn">
                            <i class="fas fa-arrow-right"></i>
                        </button>
                    </div>
                    <div id="navSearchDropdown" class="sb-3d-dropdown"></div>
                </div>
            </form>

            <ul class="navbar-nav ms-auto align-items-center">
                <li class="nav-item">
                    <a class="nav-link" href="<?= BASE_URL ?>">Home</a>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">Categories</a>
                    <div class="dropdown-menu mega-menu p-4">
                        <div class="row">
                            <?php
                            $db = Database::getInstance();
                            $cats = $db->fetchAll("SELECT * FROM categories WHERE status = 1 ORDER BY name ASC LIMIT 12");
                            $chunks = array_chunk($cats, 4);
                            foreach ($chunks as $chunk):
                            ?>
                            <div class="col-md-4">
                                <?php foreach ($chunk as $cat): ?>
                                <a class="dropdown-item d-flex align-items-center py-2" href="<?= BASE_URL ?>products.php?category=<?= htmlspecialchars($cat['slug']) ?>">
                                    <i class="fas fa-tag me-2 text-muted"></i><?= htmlspecialchars($cat['name']) ?>
                                </a>
                                <?php endforeach; ?>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?= BASE_URL ?>deals.php">Deals</a>
                </li>

                <?php if ($logged_in): ?>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
                        <i class="fas fa-user-circle me-1"></i><?= htmlspecialchars($current_user['name']) ?>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="<?= BASE_URL ?>profile.php"><i class="fas fa-user me-2"></i>Profile</a></li>
                        <li><a class="dropdown-item" href="<?= BASE_URL ?>orders.php"><i class="fas fa-box me-2"></i>My Orders</a></li>
                        <li><a class="dropdown-item" href="<?= BASE_URL ?>wishlist.php"><i class="fas fa-heart me-2"></i>Wishlist</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-danger" href="<?= BASE_URL ?>logout.php"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
                    </ul>
                </li>
                <?php else: ?>
                <li class="nav-item">
                    <a class="nav-link" href="<?= BASE_URL ?>login.php"><i class="fas fa-sign-in-alt me-1"></i>Login</a>
                </li>
                <li class="nav-item">
                    <a class="btn btn-outline-light btn-sm ms-2" href="<?= BASE_URL ?>register.php">Register</a>
                </li>
                <?php endif; ?>

                <?php if ($logged_in): ?>
                <li class="nav-item ms-2">
                    <a class="nav-link position-relative" href="<?= BASE_URL ?>notifications.php">
                        <i class="fas fa-bell fa-lg"></i>
                        <?php if ($unread_notifications > 0): ?>
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-warning cart-badge">
                            <?= $unread_notifications ?>
                        </span>
                        <?php endif; ?>
                    </a>
                </li>
                <?php endif; ?>

                <li class="nav-item ms-2">
                    <button class="theme-toggle-btn" id="themeToggle" title="Toggle theme"><i class="fas fa-moon"></i></button>
                </li>
                <li class="nav-item ms-2">
                    <a class="nav-link position-relative" href="<?= BASE_URL ?>cart.php">
                        <i class="fas fa-shopping-cart fa-lg"></i>
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger cart-badge" id="cart-count">
                            <?= $cart_count ?>
                        </span>
                    </a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<main style="min-height:0;">
<?php if (isset($_SESSION['flash'])): ?>
<div class="container mt-3">
    <?php foreach ($_SESSION['flash'] as $type => $msg): ?>
    <div class="alert alert-<?= $type === 'error' ? 'danger' : $type ?> alert-dismissible fade show">
        <?= htmlspecialchars($msg) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endforeach; ?>
    <?php unset($_SESSION['flash']); ?>
</div>
<?php endif; ?>

<script>
(function(){
  var t=localStorage.getItem('theme')||'dark';
  document.documentElement.setAttribute('data-theme',t);
  var btn=document.getElementById('themeToggle');
  if(btn){
    btn.innerHTML=t==='dark'?'<i class="fas fa-sun"></i>':'<i class="fas fa-moon"></i>';
    btn.addEventListener('click',function(){
      var cur=document.documentElement.getAttribute('data-theme');
      var next=cur==='dark'?'light':'dark';
      document.documentElement.setAttribute('data-theme',next);
      localStorage.setItem('theme',next);
      btn.innerHTML=next==='dark'?'<i class="fas fa-sun"></i>':'<i class="fas fa-moon"></i>';
    });
  }
})();
</script>

<script>
/* 3D Logo Mouse Tilt */
(function(){
  var brand=document.querySelector('.navbar-brand');
  var img=document.querySelector('.logo-3d-img');
  var name=document.querySelector('.logo-3d-name');
  if(!brand||!img)return;
  brand.addEventListener('mousemove',function(e){
    var r=brand.getBoundingClientRect();
    var x=(e.clientX-r.left)/r.width-0.5;
    var y=(e.clientY-r.top)/r.height-0.5;
    img.style.animation='none';
    img.style.transform='translateZ(14px) scale(1.12) rotateY('+(x*20)+'deg) rotateX('+(y*-20)+'deg)';
    img.style.filter='drop-shadow(0 4px 12px rgba(244,114,182,0.5))';
    if(name){
      name.style.transform='translateZ(10px) scale(1.06) rotateY('+(x*10)+'deg) rotateX('+(y*-10)+'deg)';
    }
  });
  brand.addEventListener('mouseleave',function(){
    img.style.animation='';
    img.style.transform='';
    img.style.filter='';
    if(name){
      name.style.transform='';
    }
  });
})();
</script>

<script>
/* 3D Nav Search */
(function(){
  var input=document.getElementById('navSearchInput');
  var dd=document.getElementById('navSearchDropdown');
  var form=document.getElementById('navSearchForm');
  var btn=document.getElementById('navSearchBtn');
  var timer=null;
  if(!input||!dd) return;

  input.addEventListener('input',function(){
    var q=this.value.trim();
    clearTimeout(timer);
    if(q.length<2){ dd.classList.remove('show'); dd.innerHTML=''; return; }
    dd.innerHTML='<div class="sb-dd-loading"><i class="fas fa-spinner fa-spin"></i> Searching...</div>';
    dd.classList.add('show');
    timer=setTimeout(function(){
      fetch('ajax/search.php?action=suggest&q='+encodeURIComponent(q))
        .then(function(r){return r.text();})
        .then(function(text){
          var data;
          try { data=JSON.parse(text); } catch(e){
            dd.innerHTML='<a href="<?= BASE_URL ?>products.php?q='+encodeURIComponent(q)+'" class="sb-dd-searchall"><i class="fas fa-search me-1"></i> Search "'+q+'" in all products</a>';
            return;
          }
          if(!data.success||!data.suggestions||!data.suggestions.length){
            dd.innerHTML='<a href="<?= BASE_URL ?>products.php?q='+encodeURIComponent(q)+'" class="sb-dd-searchall"><i class="fas fa-search me-1"></i> Search "'+q+'" in all products</a>';
            return;
          }
          var html='';
          data.suggestions.forEach(function(p){
            var img=(p.image&&p.image!=='default.png')?'assets/uploads/products/'+p.image:'';
            var placeholder='<div style="width:44px;height:44px;border-radius:10px;background:rgba(232,121,249,0.1);display:flex;align-items:center;justify-content:center;font-size:1rem;flex-shrink:0;">🔍</div>';
            html+='<a href="'+(p.url||'#')+'" class="sb-dd-item">';
            html+=img?'<img src="'+img+'" alt="">':placeholder;
            html+='<div class="sb-dd-info"><div class="sb-dd-name">'+(p.name||'')+'</div><div class="sb-dd-price">₹'+Number(p.price||0).toLocaleString('en-IN')+'</div></div></a>';
          });
          dd.innerHTML=html;
        })
        .catch(function(){ dd.innerHTML='<a href="<?= BASE_URL ?>products.php?q='+encodeURIComponent(input.value)+'" class="sb-dd-searchall"><i class="fas fa-search me-1"></i> Search in all products</a>'; });
    },300);
  });

  input.addEventListener('keydown',function(e){
    if(e.key==='Enter'){
      e.preventDefault();
      dd.classList.remove('show');
      form.submit();
    }
  });

  if(btn){
    btn.addEventListener('click',function(e){
      e.preventDefault();
      e.stopPropagation();
      btn.classList.remove('sb-btn-spin');
      void btn.offsetWidth;
      btn.classList.add('sb-btn-spin');
      setTimeout(function(){btn.classList.remove('sb-btn-spin');},650);
      dd.classList.remove('show');
      form.submit();
    });
  }

  document.addEventListener('click',function(e){
    if(!e.target.closest('.sb-3d-wrap')) dd.classList.remove('show');
  });
  input.addEventListener('focus',function(){ if(dd.innerHTML.trim()) dd.classList.add('show'); });
})();
</script>
<script>
document.addEventListener('click',function(e){
  var btn=e.target.closest('button:not(.sb-3d-btn):not(.m-p-btn):not(.add-to-cart-btn):not(.hero-3d-nav):not(.hero-3d-dot),.btn:not(.sb-3d-btn):not(.m-p-btn):not(.add-to-cart-btn),a.btn,input[type="submit"],input[type="button"]');
  if(!btn||btn.classList.contains('animating'))return;
  btn.classList.add('btn-3d-spin','animating');
  setTimeout(function(){btn.classList.remove('animating');},580);
},true);
</script>
