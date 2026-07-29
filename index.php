 <?php
require_once 'config/config.php';
require_once 'config/database.php';
require_once 'includes/functions.php';
require_once 'includes/functions_product.php';

$page_title = 'Home';
$featured_categories = getCategories();
$flash_sale_products = getFlashSaleProducts();
$trending_products = getTrendingProducts();
$featured_products = getFeaturedProducts();
$best_sellers = getBestSellerProducts();
$new_arrivals = getNewArrivals();

$db = Database::getInstance();
$offer_slides = $db->fetchAll("SELECT * FROM banners WHERE type='offer' AND position='carousel' AND status=1 ORDER BY sort_order ASC");
$reviews = $db->fetchAll("SELECT r.*, u.name as user_name, u.avatar FROM reviews r LEFT JOIN users u ON r.user_id = u.id WHERE r.rating >= 4 AND r.is_approved = 1 ORDER BY r.created_at DESC LIMIT 6");
$all_home_products = $db->fetchAll(
    "SELECT p.*, COALESCE(p.discount_price, p.price) AS sale_price,
            c.name AS category_name
     FROM products p
     LEFT JOIN categories c ON p.category_id = c.id
     WHERE p.status = 1
     ORDER BY p.created_at DESC
     LIMIT 20"
);
foreach ($all_home_products as &$p) {
    $images = getProductImages($p['id']);
    $img = !empty($images) ? ($images[0]['image'] ?? '') : '';
    $p['image'] = preg_replace('#^products/#', '', $img);
    $p['primary_image'] = $p['image'];
    if (!empty($p['image'])) {
        $p['image_url'] = BASE_URL . 'assets/uploads/products/' . $p['image'];
    } elseif (empty($p['image_url'])) {
        $p['image_url'] = 'https://placehold.co/400x400?text=Product';
    }
}
unset($p);
?>
<?php include 'includes/header.php'; ?>

<style>
/* ================================================================
   UNIQUE DESIGN SYSTEM - Pink Rose Theme
   ================================================================ */
:root {
  --uniq-primary: #F472B6;
  --uniq-primary-dark: #DB2777;
  --uniq-secondary: #FB7185;
  --uniq-accent: #FBBF24;
  --uniq-purple: #E879F9;
  --uniq-bg-dark: #e8f4fd;
  --uniq-bg-card: rgba(0,0,0,0.04);
  --uniq-border: rgba(0,0,0,0.08);
  --uniq-grad-1: linear-gradient(135deg, #F472B6 0%, #E879F9 50%, #FB7185 100%);
  --uniq-grad-2: linear-gradient(135deg, #DB2777 0%, #F472B6 100%);
  --uniq-grad-3: linear-gradient(135deg, #FB7185 0%, #FBBF24 100%);
  --uniq-grad-4: linear-gradient(135deg, #e8e8e8 0%, #d8d8d8 100%);
  --uniq-shadow: 0 8px 32px rgba(0,0,0,0.08);
  --uniq-glow: 0 0 30px rgba(0,0,0,0.04);
}

/* ===== HERO 3D ANIMATED BANNER ===== */
.hero-3d-section{position:relative;min-height:650px;overflow:hidden;background:#060614;perspective:1200px;perspective-origin:50% 40%;}
.hero-3d-bg{position:absolute;inset:0;background:radial-gradient(ellipse at 20% 40%,rgba(112,0,255,0.18) 0%,transparent 55%),radial-gradient(ellipse at 80% 60%,rgba(0,240,255,0.14) 0%,transparent 55%),radial-gradient(ellipse at 50% 100%,rgba(255,0,127,0.1) 0%,transparent 45%),linear-gradient(180deg,#060614 0%,#0a0a2e 50%,#060614 100%);}

/* 3D Grid Floor */
.hero-3d-grid{position:absolute;bottom:0;left:50%;transform:translateX(-50%) rotateX(65deg);width:200%;height:120%;transform-origin:center bottom;background-image:linear-gradient(rgba(0,240,255,0.06) 1px,transparent 1px),linear-gradient(90deg,rgba(0,240,255,0.06) 1px,transparent 1px);background-size:60px 60px;animation:gridScroll 8s linear infinite;pointer-events:none;opacity:0.5;mask-image:linear-gradient(to top,rgba(0,0,0,0.4) 0%,transparent 70%);-webkit-mask-image:linear-gradient(to top,rgba(0,0,0,0.4) 0%,transparent 70%);}
@keyframes gridScroll{0%{background-position:0 0;}100%{background-position:0 60px;}}

/* Stars */
.hero-stars{position:absolute;inset:0;pointer-events:none;transform-style:preserve-3d;}
.hero-stars span{position:absolute;width:2px;height:2px;background:#fff;border-radius:50%;animation:heroStarTwinkle var(--dur,3s) ease-in-out infinite var(--delay,0s);transform:translateZ(var(--tz,0px));}
@keyframes heroStarTwinkle{0%,100%{opacity:0.15;transform:translateZ(var(--tz,0px)) scale(1);}50%{opacity:1;transform:translateZ(var(--tz,0px)) scale(1.8);}}

/* Nebula */
.hero-nebula{position:absolute;top:-40%;left:-20%;width:140%;height:180%;background:radial-gradient(ellipse at center,rgba(112,0,255,0.08) 0%,transparent 70%);animation:nebulaDrift 20s ease-in-out infinite alternate;pointer-events:none;transform-style:preserve-3d;}
@keyframes nebulaDrift{0%{transform:translateZ(-100px) translate(0,0) rotate(0deg);}100%{transform:translateZ(-100px) translate(30px,-20px) rotate(3deg);}}

/* Aurora */
.hero-aurora{position:absolute;top:0;left:0;width:100%;height:100%;background:linear-gradient(135deg,rgba(0,240,255,0.04) 0%,transparent 40%,rgba(112,0,255,0.04) 60%,transparent 100%);animation:auroraShift 15s ease-in-out infinite alternate;pointer-events:none;}
@keyframes auroraShift{0%{opacity:0.5;transform:scale(1);}50%{opacity:1;transform:scale(1.05);}100%{opacity:0.5;transform:scale(1);}}

/* 3D Floating Orbs */
.hero-3d-orb{position:absolute;border-radius:50%;pointer-events:none;filter:blur(40px);animation:orbFloat var(--dur,8s) ease-in-out infinite var(--delay,0s);transform-style:preserve-3d;}
@keyframes orbFloat{0%,100%{transform:translate3d(0,0,0) scale(1);}33%{transform:translate3d(var(--tx,20px),var(--ty,-30px),var(--tz,40px)) scale(1.1);}66%{transform:translate3d(calc(var(--tx,20px)*-1),var(--ty,-30px),calc(var(--tz,40px)*-1)) scale(0.95);}}

.hero-3d-inner{position:relative;z-index:2;padding:40px 0 50px;transform-style:preserve-3d;}
.hero-3d-header{text-align:center;margin-bottom:30px;padding:0 15px;transform:translateZ(30px);}
.hero-3d-badge{display:inline-flex;align-items:center;gap:8px;padding:8px 20px;border-radius:50px;background:rgba(255,255,255,0.08);backdrop-filter:blur(12px);border:1px solid rgba(255,255,255,0.12);color:#00F0FF;font-size:0.82rem;font-weight:600;letter-spacing:0.5px;margin-bottom:16px;animation:badgePulse 3s ease-in-out infinite;}
@keyframes badgePulse{0%,100%{box-shadow:0 0 0 0 rgba(0,240,255,0.3);}50%{box-shadow:0 0 20px 4px rgba(0,240,255,0.15);}}
.hero-3d-title{font-size:clamp(2rem,5vw,3.2rem);font-weight:800;color:#fff;line-height:1.15;margin-bottom:20px;transform:translateZ(20px);}
.gradient-text{background:linear-gradient(135deg,#00F0FF,#7000FF);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;}
.hero-marquee{overflow:hidden;width:100%;max-width:700px;margin:0 auto 10px;mask-image:linear-gradient(90deg,transparent,#000 10%,#000 90%,transparent);-webkit-mask-image:linear-gradient(90deg,transparent,#000 10%,#000 90%,transparent);}
.hero-marquee-track{display:flex;gap:40px;white-space:nowrap;animation:heroMarqueeScroll 25s linear infinite;}
.hero-marquee-track span{color:rgba(255,255,255,0.6);font-size:0.85rem;font-weight:500;flex-shrink:0;}
@keyframes heroMarqueeScroll{0%{transform:translateX(0);}100%{transform:translateX(-50%);}}

/* 3D Card Container */
.hero-3d-cards{position:relative;width:100%;max-width:1100px;margin:0 auto;height:420px;perspective:1400px;transform-style:preserve-3d;}

/* Individual 3D Slide Card */
.hero-3d-card{position:absolute;left:50%;top:0;width:min(90%,700px);min-height:380px;border-radius:28px;overflow:hidden;cursor:pointer;transform-style:preserve-3d;transition:transform 0.8s cubic-bezier(0.23,1,0.32,1),opacity 0.8s ease,box-shadow 0.8s ease;will-change:transform,opacity;}
.hero-3d-card .card-inner{position:relative;width:100%;height:100%;min-height:380px;border-radius:28px;overflow:hidden;transform-style:preserve-3d;transition:transform 0.15s ease-out;}
.hero-3d-card .card-bg{position:absolute;inset:0;background-size:cover;background-position:center;background-repeat:no-repeat;transform:translateZ(0px);transition:transform 0.4s ease;}
.hero-3d-card .card-overlay{position:absolute;inset:0;background:linear-gradient(135deg,rgba(0,0,0,0.78) 0%,rgba(0,0,0,0.45) 50%,rgba(0,0,0,0.25) 100%);z-index:1;transition:opacity 0.4s ease;}
.hero-3d-card .card-shine{position:absolute;inset:0;z-index:5;border-radius:28px;pointer-events:none;background:linear-gradient(105deg,transparent 40%,rgba(255,255,255,0.06) 45%,rgba(255,255,255,0.1) 50%,rgba(255,255,255,0.06) 55%,transparent 60%);opacity:0;transition:opacity 0.3s ease;}

/* Card Shadows for 3D depth */
.hero-3d-card .card-shadow{position:absolute;inset:-2px;border-radius:30px;pointer-events:none;z-index:-1;transition:all 0.5s ease;}

/* Card Content */
.hero-3d-card .card-body{position:absolute;inset:0;z-index:3;display:flex;flex-direction:column;justify-content:flex-end;padding:40px 44px;transform:translateZ(40px);}
.hero-3d-card .card-tag{display:inline-flex;align-items:center;gap:6px;padding:6px 16px;border-radius:50px;background:linear-gradient(135deg,#00F0FF,#0070FF);color:#fff;font-size:0.75rem;font-weight:600;letter-spacing:0.3px;margin-bottom:14px;width:fit-content;transform:translateZ(20px);}
.hero-3d-card .card-body h2{font-size:clamp(1.6rem,3.5vw,2.4rem);font-weight:800;color:#fff;line-height:1.15;margin-bottom:10px;transform:translateZ(15px);}
.hero-3d-card .card-body .card-hl{font-weight:900;}
.hero-3d-card .card-body p{color:rgba(255,255,255,0.75);font-size:0.9rem;line-height:1.6;margin-bottom:22px;max-width:400px;transform:translateZ(10px);}
.hero-3d-card .card-cta{display:inline-flex;align-items:center;gap:10px;padding:12px 28px;border-radius:14px;color:#fff;font-size:0.88rem;font-weight:700;text-decoration:none;border:none;cursor:pointer;transition:all 0.35s cubic-bezier(0.23,1,0.32,1);box-shadow:0 8px 30px rgba(0,0,0,0.3);width:fit-content;transform:translateZ(25px);}
.hero-3d-card .card-cta:hover{transform:translateZ(30px) translateY(-3px) scale(1.03);box-shadow:0 14px 45px rgba(0,0,0,0.4);}

/* 3D Floating Particles inside cards */
.hero-3d-card .card-particles{position:absolute;inset:0;z-index:2;pointer-events:none;transform-style:preserve-3d;}
.hero-3d-card .card-particles span{position:absolute;font-size:1.5rem;opacity:0.2;animation:cardParticle3D var(--dur,6s) ease-in-out infinite var(--delay,0s);}
@keyframes cardParticle3D{0%,100%{transform:translate3d(0,0,0) rotate(0deg);opacity:0.15;}25%{transform:translate3d(10px,-15px,20px) rotate(15deg);opacity:0.4;}50%{transform:translate3d(-5px,-30px,35px) rotate(-5deg);opacity:0.3;}75%{transform:translate3d(15px,-10px,15px) rotate(10deg);opacity:0.45;}}

/* Card Glow Effects */
.hero-3d-card .card-glow{position:absolute;inset:0;z-index:0;border-radius:28px;pointer-events:none;opacity:0.6;}
.hero-3d-card .card-glow-cyan{background:radial-gradient(circle at 20% 80%,rgba(0,240,255,0.2) 0%,transparent 55%);}
.hero-3d-card .card-glow-purple{background:radial-gradient(circle at 80% 20%,rgba(112,0,255,0.2) 0%,transparent 55%);}
.hero-3d-card .card-glow-green{background:radial-gradient(circle at 20% 20%,rgba(0,255,136,0.15) 0%,transparent 55%);}
.hero-3d-card .card-glow-gold{background:radial-gradient(circle at 80% 80%,rgba(255,215,0,0.15) 0%,transparent 55%);}
.hero-3d-card .card-glow-pink{background:radial-gradient(circle at 50% 50%,rgba(255,0,127,0.15) 0%,transparent 55%);}

/* Card States */
.hero-3d-card.card-active{transform:translateX(-50%) translateZ(0px) rotateY(0deg) scale(1);opacity:1;z-index:10;}
.hero-3d-card.card-prev{transform:translateX(calc(-50% - 320px)) translateZ(-200px) rotateY(18deg) scale(0.75);opacity:0.35;z-index:5;pointer-events:none;}
.hero-3d-card.card-next{transform:translateX(calc(-50% + 320px)) translateZ(-200px) rotateY(-18deg) scale(0.75);opacity:0.35;z-index:5;pointer-events:none;}
.hero-3d-card.card-hidden{transform:translateX(-50%) translateZ(-500px) rotateY(0deg) scale(0.5);opacity:0;z-index:0;pointer-events:none;}
.hero-3d-card.card-active .card-inner{box-shadow:0 30px 80px rgba(0,0,0,0.5),0 0 60px rgba(0,240,255,0.08);}

/* 3D Carousel Navigation */
.hero-3d-nav{position:absolute;top:50%;transform:translateY(-50%);z-index:20;width:56px;height:56px;border-radius:50%;border:1px solid rgba(255,255,255,0.15);background:rgba(255,255,255,0.06);backdrop-filter:blur(16px);color:#fff;font-size:1.2rem;display:flex;align-items:center;justify-content:center;cursor:pointer;transition:all 0.35s cubic-bezier(0.23,1,0.32,1);transform-style:preserve-3d;}
.hero-3d-nav:hover{background:rgba(255,255,255,0.12);border-color:rgba(0,240,255,0.4);box-shadow:0 0 30px rgba(0,240,255,0.15);transform:translateY(-50%) translateZ(20px) scale(1.1);}
.hero-3d-nav.nav-prev{left:20px;}
.hero-3d-nav.nav-next{right:20px;}

/* 3D Pagination */
.hero-3d-dots{display:flex;justify-content:center;gap:10px;padding-top:10px;transform-style:preserve-3d;transform:translateZ(10px);}
.hero-3d-dot{width:10px;height:10px;border-radius:50%;background:rgba(255,255,255,0.2);cursor:pointer;transition:all 0.4s cubic-bezier(0.23,1,0.32,1);transform-style:preserve-3d;border:none;padding:0;}
.hero-3d-dot.dot-active{background:#00F0FF;width:32px;border-radius:5px;box-shadow:0 0 16px rgba(0,240,255,0.5);transform:translateZ(5px);}
.hero-3d-dot:hover:not(.dot-active){background:rgba(255,255,255,0.4);transform:translateZ(3px) scale(1.2);}

/* 3D Reflection */
.hero-3d-reflection{position:absolute;bottom:-80px;left:50%;transform:translateX(-50%) rotateX(180deg) scaleY(0.3);width:min(90%,700px);height:120px;border-radius:28px;overflow:hidden;pointer-events:none;opacity:0.08;filter:blur(4px);z-index:1;background-size:cover;background-position:center;transition:opacity 0.8s ease,background 0.8s ease;}

@media(max-width:768px){
  .hero-3d-section{min-height:auto;}
  .hero-3d-title{font-size:1.6rem;}
  .hero-3d-cards{height:340px;}
  .hero-3d-card{width:min(92%,340px);min-height:320px;}
  .hero-3d-card .card-inner{min-height:320px;}
  .hero-3d-card .card-body{padding:24px 24px;}
  .hero-3d-card .card-body h2{font-size:1.3rem;}
  .hero-3d-card.card-prev{transform:translateX(calc(-50% - 160px)) translateZ(-150px) rotateY(14deg) scale(0.7);opacity:0.25;}
  .hero-3d-card.card-next{transform:translateX(calc(-50% + 160px)) translateZ(-150px) rotateY(-14deg) scale(0.7);opacity:0.25;}
  .hero-3d-nav{width:44px;height:44px;font-size:1rem;}
  .hero-3d-nav.nav-prev{left:10px;}
  .hero-3d-nav.nav-next{right:10px;}
  .hero-3d-grid{background-size:40px 40px;}
}
/* ===== Sticky Category Strip ===== */
.m-cat-strip {
  background: rgba(232,232,232,0.95); backdrop-filter: blur(12px);
  border-bottom: 1px solid rgba(0,0,0,0.08);
  padding: 14px 0; position: sticky; top: var(--navbar-height); z-index: 99;
}
.m-cat-strip .container { display: flex; align-items: center; gap: 8px; }
.m-cat-pills { display: flex; gap: 6px; overflow-x: auto; scrollbar-width: none; padding: 2px 0; flex: 1; }
.m-cat-pills::-webkit-scrollbar { display: none; }
.m-cat-pill {
  flex-shrink: 0; padding: 6px 18px; border-radius: 50px;
  background: rgba(0,0,0,0.06); border: 1px solid rgba(0,0,0,0.08);
  color: #999; font-size: 0.8rem; font-weight: 500;
  cursor: pointer; transition: all 0.25s; white-space: nowrap; text-decoration: none;
}
.m-cat-pill:hover, .m-cat-pill.active { background: var(--uniq-primary); color: #fff; border-color: var(--uniq-primary); font-weight: 600; }

/* ===== Common Section ===== */
.m-section { padding: 36px 0; }
.m-section-header {
  display: flex; justify-content: space-between; align-items: center;
  margin-bottom: 20px;
}
.m-section-header h2 {
  font-size: 1.3rem; font-weight: 700; color: #333;
  display: flex; align-items: center; gap: 10px;
}
.m-section-header h2 i { font-size: 1rem; }
.m-section-header .m-view-all {
  font-size: 0.85rem; color: var(--uniq-primary); font-weight: 600;
  text-decoration: none; display: flex; align-items: center; gap: 4px;
}
.m-section-header .m-view-all:hover { text-decoration: underline; }

/* ===== Product Row ===== */
.m-prod-row {
  display: flex; gap: 14px; overflow-x: auto;
  padding: 6px 4px 16px; scroll-snap-type: x mandatory; scrollbar-width: none;
}
.m-prod-row::-webkit-scrollbar { display: none; }

.m-prod-card {
  flex-shrink: 0; width: 175px; scroll-snap-align: start;
  background: rgba(0,0,0,0.06); border: 1px solid rgba(0,0,0,0.08);
  border-radius: 20px; overflow: hidden;
  transition: all 0.4s cubic-bezier(0.34,1.56,0.64,1); cursor: pointer;
  position: relative;
}
.m-prod-card:hover { transform: translateY(-6px) scale(1.02); border-color: rgba(244,114,182,0.2); }
.m-prod-card::before {
  content: ''; position: absolute; inset: 0; border-radius: 20px;
  opacity: 0; transition: opacity 0.4s; pointer-events: none;
}
.m-prod-card:hover::before { opacity: 1; }

.m-prod-card .m-p-img {
  width: 100%; aspect-ratio: 1; overflow: hidden; position: relative;
  background: rgba(0,0,0,0.04);
}
.m-prod-card .m-p-img img {
  width: 100%; height: 100%; object-fit: cover;
  transition: transform 0.6s cubic-bezier(0.34,1.56,0.64,1);
}
.m-prod-card:hover .m-p-img img { transform: scale(1.08) rotate(-2deg); }
.m-prod-card .m-p-badge {
  position: absolute; top: 8px; left: 8px;
  padding: 3px 10px; border-radius: 50px;
  font-size: 0.6rem; font-weight: 700;
  background: linear-gradient(135deg, #F472B6, #E879F9); color: #fff;
}
.m-prod-card .m-p-body { padding: 12px 14px; position: relative; z-index: 1; }
.m-prod-card .m-p-name {
  font-size: 0.82rem; font-weight: 600; color: #333;
  white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
  margin-bottom: 4px;
}
.m-prod-card .m-p-meta { font-size: 0.68rem; color: #888; margin-bottom: 8px; }
.m-prod-card .m-p-price { display: flex; align-items: center; gap: 6px; }
.m-prod-card .m-p-price .curr { font-size: 1rem; font-weight: 700; color: #111; }
.m-prod-card .m-p-price .orig { font-size: 0.72rem; color: #666; text-decoration: line-through; }
.m-prod-card .m-p-btn {
  position: absolute; bottom: 12px; right: 12px;
  width: 34px; height: 34px; border-radius: 50%;
  background: var(--uniq-grad-1); color: #fff; border: none;
  display: flex; align-items: center; justify-content: center;
  font-size: 0.85rem; cursor: pointer; z-index: 2;
  transition: all 0.3s cubic-bezier(0.34,1.56,0.64,1);
  box-shadow: 0 4px 15px rgba(244,114,182,0.25);
}
.m-prod-card .m-p-btn:hover { transform: scale(1.2) rotate(90deg); }

/* Add to Cart - Animated States */
.m-prod-card .m-p-btn.adding {
  pointer-events: none;
  animation: addBtnSpin 0.6s cubic-bezier(0.4,0,0.2,1);
}
.m-prod-card .m-p-btn.added {
  background: linear-gradient(135deg,#00c853,#00e676) !important;
  transform: scale(1);
  animation: addBtnPop 0.5s cubic-bezier(0.34,1.56,0.64,1);
  pointer-events: none;
}
.m-prod-card .m-p-btn.added i::before { content: "\f00c"; }
@keyframes addBtnSpin {
  0% { transform: scale(1) rotate(0deg); }
  50% { transform: scale(1.3) rotate(180deg); }
  100% { transform: scale(1) rotate(360deg); }
}
@keyframes addBtnPop {
  0% { transform: scale(0.5); }
  50% { transform: scale(1.4); }
  100% { transform: scale(1); }
}

/* Ripple on button click */
.m-p-btn-ripple {
  position: absolute; border-radius: 50%; pointer-events: none;
  width: 20px; height: 20px; transform: translate(-50%,-50%) scale(0);
  background: rgba(255,255,255,0.5);
  animation: rippleOut 0.5s ease-out forwards;
}
@keyframes rippleOut {
  0% { transform: translate(-50%,-50%) scale(0); opacity: 1; }
  100% { transform: translate(-50%,-50%) scale(4); opacity: 0; }
}

/* Flying product clone */
.m-fly-clone {
  position: fixed; z-index: 99999; pointer-events: none;
  width: 60px; height: 60px; border-radius: 50%; overflow: hidden;
  box-shadow: 0 8px 30px rgba(244,114,182,0.5), 0 0 20px rgba(244,114,182,0.3);
  border: 3px solid rgba(255,255,255,0.9);
  transition: all 0.7s cubic-bezier(0.23,1,0.32,1);
  opacity: 1;
}
.m-fly-clone img { width: 100%; height: 100%; object-fit: cover; }
.m-fly-clone.fly-to-cart {
  opacity: 0.3;
  transform: scale(0.2) rotate(360deg);
}

/* Cart icon pulse when item added */
@keyframes cartBounce {
  0%,100% { transform: scale(1); }
  25% { transform: scale(1.35) rotate(-10deg); }
  50% { transform: scale(0.9) rotate(5deg); }
  75% { transform: scale(1.15) rotate(-3deg); }
}
.nav-link.cart-pulse i.fa-shopping-cart {
  animation: cartBounce 0.6s cubic-bezier(0.34,1.56,0.64,1);
}
@keyframes cartBadgePop {
  0% { transform: translate(-50%,-50%) scale(1); }
  50% { transform: translate(-50%,-50%) scale(1.5); }
  100% { transform: translate(-50%,-50%) scale(1); }
}
.cart-badge.badge-pop {
  animation: cartBadgePop 0.4s cubic-bezier(0.34,1.56,0.64,1);
}

/* Card flash on add */
.m-prod-card.card-flash {
  animation: cardFlash 0.4s ease;
}
@keyframes cardFlash {
  0% { box-shadow: 0 0 0 0 rgba(0,200,83,0.4); }
  50% { box-shadow: 0 0 30px 8px rgba(0,200,83,0.25); }
  100% { box-shadow: none; }
}

/* ===== Category Grid ===== */
.m-cat-grid {
  display: grid; grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
  gap: 12px;
}
.m-cat-item {
  background: rgba(0,0,0,0.06); border: 1px solid rgba(0,0,0,0.08);
  border-radius: 20px; padding: 20px 14px; text-align: center;
  transition: all 0.4s cubic-bezier(0.34,1.56,0.64,1);
  cursor: pointer; text-decoration: none;
}
.m-cat-item:hover { transform: translateY(-6px); border-color: rgba(244,114,182,0.3); background: rgba(244,114,182,0.06); }
.m-cat-item .m-cat-icon {
  width: 52px; height: 52px; border-radius: 16px;
  background: rgba(244,114,182,0.1);
  display: flex; align-items: center; justify-content: center;
  margin: 0 auto 12px; font-size: 1.3rem; color: var(--uniq-primary);
  transition: all 0.4s;
}
.m-cat-item:hover .m-cat-icon { background: var(--uniq-grad-1); color: #fff; transform: scale(1.1) rotate(-5deg); }
.m-cat-item .m-cat-label { font-size: 0.82rem; font-weight: 600; color: #333; }
.m-cat-item .m-cat-count { font-size: 0.7rem; color: #888; margin-top: 2px; }

/* ===== Flash Sale Banner ===== */
.m-flash {
  background: linear-gradient(135deg, #e0e0e0 0%, #e8e8e8 50%, #ece8f0 100%);
  border: 1px solid rgba(0,0,0,0.08);
  border-radius: 20px; padding: 24px 28px;
  display: flex; justify-content: space-between; align-items: center;
  gap: 20px; flex-wrap: wrap; position: relative; overflow: hidden;
  margin-bottom: 24px;
}
.m-flash::after {
  content: ''; position: absolute;
  top: -80%; left: -20%; width: 140%; height: 200%;
  background: linear-gradient(45deg, transparent 30%, rgba(244,114,182,0.04) 50%, transparent 70%);
  animation: mShimmer 4s infinite;
}
@keyframes mShimmer { 0% { transform: translateX(-100%) translateY(-50%) rotate(20deg); } 100% { transform: translateX(100%) translateY(50%) rotate(20deg); } }
.m-flash > * { position: relative; z-index: 2; }
.m-flash .mf-info h3 { color: var(--uniq-primary); font-weight: 800; font-size: 1.3rem; margin-bottom: 2px; }
.m-flash .mf-info p { color: #666; font-size: 0.85rem; margin: 0; }
.m-flash .mf-timer { display: flex; gap: 8px; }
.m-flash .mf-timer .tf-box {
  display: flex; flex-direction: column; align-items: center;
  background: rgba(244,114,182,0.08); backdrop-filter: blur(6px);
  border-radius: 12px; padding: 8px 12px; min-width: 48px;
  border: 1px solid rgba(244,114,182,0.15);
}
.m-flash .mf-timer .tf-box .num { color: var(--uniq-primary); font-size: 1.2rem; font-weight: 800; line-height: 1; }
.m-flash .mf-timer .tf-box .lbl { color: rgba(255,255,255,0.4); font-size: 0.6rem; text-transform: uppercase; font-weight: 600; }

/* ===== Promo Strip ===== */
.m-promo {
  display: grid; grid-template-columns: repeat(3, 1fr); gap: 14px;
}
.m-promo-card {
  border-radius: 20px; padding: 28px 24px; position: relative; overflow: hidden;
  min-height: 140px; display: flex; flex-direction: column; justify-content: flex-end;
  color: #fff; transition: all 0.4s cubic-bezier(0.34,1.56,0.64,1); cursor: pointer;
}
.m-promo-card:hover { transform: translateY(-5px) scale(1.01); }
.m-promo-card::after { content: ''; position: absolute; top: -30%; right: -20%; width: 160px; height: 160px; border-radius: 50%; background: rgba(0,0,0,0.08); }
.m-promo-card .mp-label { font-size: 0.7rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; opacity: 0.85; margin-bottom: 4px; position: relative; z-index: 2; }
.m-promo-card h4 { font-size: 1.3rem; font-weight: 900; margin-bottom: 2px; position: relative; z-index: 2; color: #fff; transform:translateZ(15px); }
.m-promo-card p { font-size: 0.82rem; font-weight: 500; opacity: 0.9; margin: 0; position: relative; z-index: 2; }
.m-p1 { background: linear-gradient(135deg, #F472B6, #DB2777); }
.m-p2 { background: linear-gradient(135deg, #E879F9, #C026D3); }
.m-p3 { background: linear-gradient(135deg, #FB7185, #BE123C); }
.m-promo-card { color: #fff; }

/* ===== Features ===== */
.m-features {
  border-top: 1px solid rgba(0,0,0,0.06); border-bottom: 1px solid rgba(0,0,0,0.06);
  padding: 28px 0;
}
.m-features .container { display: grid; grid-template-columns: repeat(4,1fr); gap: 16px; }
.m-feat-item { text-align: center; padding: 16px 12px; border-radius: 16px; transition: all 0.3s; }
.m-feat-item:hover { background: rgba(0,0,0,0.04); transform: translateY(-3px); }
.m-feat-item .mf-icon {
  width: 44px; height: 44px; border-radius: 14px;
  background: rgba(244,114,182,0.1); display: flex; align-items: center; justify-content: center;
  margin: 0 auto 10px; font-size: 1.1rem; color: var(--uniq-primary);
  transition: all 0.3s;
}
.m-feat-item:hover .mf-icon { background: var(--uniq-grad-1); color: #fff; }
.m-feat-item h6 { font-size: 0.82rem; font-weight: 800; color: #333; margin-bottom: 2px; transform:translateZ(10px); }
.m-feat-item p { font-size: 0.75rem; font-weight: 500; color: #888; margin: 0; }

/* ===== TOP SEARCH BAR ===== */
/* ===== Animations ===== */
.m-anim { opacity: 0; transform: translateY(30px); transition: opacity 0.7s cubic-bezier(0.34,1.56,0.64,1), transform 0.7s cubic-bezier(0.34,1.56,0.64,1); }
.m-anim.visible { opacity: 1; transform: translateY(0); }
.m-anim-left { opacity: 0; transform: translateX(-30px); transition: opacity 0.7s cubic-bezier(0.34,1.56,0.64,1), transform 0.7s cubic-bezier(0.34,1.56,0.64,1); }
.m-anim-left.visible { opacity: 1; transform: translateX(0); }

/* ===== Responsive ===== */
@media(max-width:991px){
  .m-promo { grid-template-columns: 1fr; }
  .m-features .container { grid-template-columns: repeat(2,1fr); }
  .offer-slide { min-height: 300px; }
  .offer-slide-content { text-align: center; max-width: 100%; }
  .offer-slide-desc { margin-left: auto; margin-right: auto; }
}
@media(max-width:767px){
  .m-cat-grid { grid-template-columns: repeat(3, 1fr); gap: 10px; }
  .m-cat-item { padding: 14px 10px; }
  .m-cat-item .m-cat-icon { width: 40px; height: 40px; font-size: 1rem; }
  .m-prod-card { width: 145px; }
  .m-flash { flex-direction: column; text-align: center; padding: 20px; }
  .offer-slide { min-height: 260px; padding: 30px 0; }
  .offer-slide-title { font-size: 1.5rem; }
  .offer-slide-desc { font-size: 0.85rem; }
  .offer-slide-btn { padding: 10px 24px; font-size: 0.85rem; }
  .offer-ctrl { width: 38px; height: 38px; font-size: 0.85rem; }
}
@media(max-width:480px){
  .m-cat-grid { grid-template-columns: repeat(2, 1fr); }
  .m-features .container { grid-template-columns: 1fr 1fr; }
  .m-prod-card { width: 130px; }
  .offer-slide { min-height: 220px; }
  .offer-slide-title { font-size: 1.25rem; }
  .offer-slide-badge { font-size: 0.7rem; padding: 4px 14px; }
}

/* ===== 3D PROMOTIONAL BANNER ===== */
.b-3d-wrap {
  position:relative; border-radius:24px; overflow:hidden;
  background:linear-gradient(135deg,#ececec 0%,#e8e8f0 40%,#ececec 100%);
  padding:60px 50px; margin:30px 0;
  transform-style:preserve-3d; perspective:1200px;
  transition: transform 0.3s ease-out, box-shadow 0.4s ease;
}
.b-3d-wrap:hover {
  box-shadow: 0 30px 80px rgba(244,114,182,0.15), 0 0 0 1px rgba(244,114,182,0.08);
}
.b-3d-bg {
  position:absolute; inset:0; overflow:hidden; pointer-events:none;
}
.b-3d-bg span {
  position:absolute; border-radius:50%; opacity:0.07;
}
.b-3d-bg span:nth-child(1){width:400px;height:400px;background:#E879F9;top:-80px;right:-60px;filter:blur(80px);}
.b-3d-bg span:nth-child(2){width:300px;height:300px;background:#F472B6;bottom:-60px;left:-60px;filter:blur(70px);}
.b-3d-bg span:nth-child(3){width:200px;height:200px;background:#FB7185;top:40%;left:40%;filter:blur(60px);}
.b-3d-grid {
  position:absolute; inset:0;
  background-image:linear-gradient(rgba(0,0,0,0.04) 1px,transparent 1px),linear-gradient(90deg,rgba(0,0,0,0.04) 1px,transparent 1px);
  background-size:40px 40px;
}
.b-3d-content { position:relative; z-index:2; display:flex; align-items:center; gap:40px; }
.b-3d-text { flex:1; }
.b-3d-text .b-label {
  display:inline-flex; align-items:center; gap:8px;
  padding:6px 18px; border-radius:50px; font-size:0.8rem; font-weight:700;
  background:rgba(167,139,250,0.15); border:1px solid rgba(167,139,250,0.3);
  color:#A78BFA; text-transform:uppercase; letter-spacing:1px; margin-bottom:16px;
  transform:translateZ(30px);
}
.b-3d-text h2 {
  font-size:2.8rem; font-weight:900; line-height:1.15; margin-bottom:12px;
  transform:translateZ(20px);
}
.b-3d-text h2 .t3d {
  display:inline-block;
  background:linear-gradient(135deg,#F472B6,#E879F9,#FB7185);
  -webkit-background-clip:text; -webkit-text-fill-color:transparent;
  filter:drop-shadow(0 4px 20px rgba(232,121,249,0.3));
  transform:perspective(600px) rotateX(2deg) rotateY(-2deg) translateZ(25px);
  text-shadow:0 0 40px rgba(244,114,182,0.15);
  animation:t3dPulse 3s ease-in-out infinite;
}
@keyframes t3dPulse { 0%,100%{filter:drop-shadow(0 4px 20px rgba(232,121,249,0.3));} 50%{filter:drop-shadow(0 4px 30px rgba(232,121,249,0.5));} }
.b-3d-text p { color: #555; font-size:1.05rem; font-weight:500; margin-bottom:24px; max-width:480px; transform:translateZ(12px); }
.b-3d-text .b-btn {
  display:inline-flex; align-items:center; gap:10px;
  padding:14px 32px; border-radius:50px; font-weight:700;
  background:linear-gradient(135deg,#F472B6,#DB2777); color:#fff; border:none;
  cursor:pointer; text-decoration:none; transition:all 0.35s cubic-bezier(0.23,1,0.32,1);
  transform:translateZ(20px); box-shadow:0 8px 25px rgba(244,114,182,0.3);
}
.b-3d-text .b-btn:hover { transform:translateZ(30px) translateY(-3px) scale(1.04); box-shadow:0 14px 40px rgba(244,114,182,0.4); }
.b-3d-visual { flex:1; display:flex; justify-content:center; align-items:center; gap:20px; transform:translateZ(30px); }
.b-3d-podium {
  position:relative; width:280px; height:220px;
  display:flex; align-items:flex-end; justify-content:center; gap:20px;
  transform-style:preserve-3d;
}
.b-3d-podium .pedestal {
  width:80px; height:120px; border-radius:12px 12px 4px 4px;
  background:linear-gradient(180deg,rgba(255,255,255,0.12),rgba(0,0,0,0.04));
  border:1px solid rgba(0,0,0,0.1);
  backdrop-filter:blur(10px); position:relative;
  display:flex; align-items:center; justify-content:center;
  transform:perspective(400px) rotateX(5deg) translateZ(10px);
  box-shadow:0 4px 30px rgba(0,0,0,0.3);
  transition:transform 0.4s cubic-bezier(0.23,1,0.32,1), box-shadow 0.3s;
}
.b-3d-podium .pedestal:hover {
  transform:perspective(400px) rotateX(5deg) translateZ(30px) scale(1.08);
  box-shadow:0 12px 40px rgba(244,114,182,0.25);
}
.b-3d-podium .pedestal:nth-child(1){height:100px;}
.b-3d-podium .pedestal:nth-child(2){height:150px;}
.b-3d-podium .pedestal:nth-child(3){height:120px;}
.b-3d-podium .pedestal img {
  width:52px; height:52px; object-fit:contain;
  position:absolute; top:-40px; filter:drop-shadow(0 8px 20px rgba(0,0,0,0.4));
  animation:floatP 3s ease-in-out infinite;
  transform:translateZ(20px);
}
.b-3d-podium .pedestal:nth-child(2) img { animation-delay:0.5s; }
.b-3d-podium .pedestal:nth-child(3) img { animation-delay:1s; }
@keyframes floatP { 0%,100%{transform:translateY(0);} 50%{transform:translateY(-8px);} }
.b-3d-floor {
  position:absolute; bottom:-8px; left:50%; transform:translateX(-50%);
  width:90%; height:30px;
  background:linear-gradient(90deg,transparent,rgba(167,139,250,0.1),transparent);
  border-radius:50%; filter:blur(10px);
}

/* ===== E-COMMERCE SALE BANNER ===== */
.b-sale-wrap {
  position:relative; border-radius:24px; overflow:hidden;
  background:linear-gradient(135deg,#e2e2e2 0%,#ece8f0 50%,#e8e8e8 100%);
  padding:50px 50px; margin:30px 0;
  transform-style:preserve-3d; perspective:1200px;
  transition: transform 0.3s ease-out, box-shadow 0.4s ease;
}
.b-sale-wrap:hover {
  box-shadow: 0 30px 80px rgba(244,114,182,0.15), 0 0 0 1px rgba(244,114,182,0.08);
}
.b-sale-bg {
  position:absolute; inset:0; overflow:hidden; pointer-events:none;
}
.b-sale-bg span {
  position:absolute; border-radius:50%; opacity:0.06;
}
.b-sale-bg span:nth-child(1){width:500px;height:500px;background:#E879F9;top:-120px;right:-80px;filter:blur(90px);}
.b-sale-bg span:nth-child(2){width:350px;height:350px;background:#FB7185;bottom:-100px;left:-50px;filter:blur(80px);}
.b-sale-content { position:relative; z-index:2; transform-style:preserve-3d; }
.b-sale-top { display:flex; align-items:center; justify-content:space-between; margin-bottom:30px; transform:translateZ(15px); }
.b-sale-top h3 {
  font-size:2rem; font-weight:900;
  background:linear-gradient(135deg,#F472B6,#E879F9);
  -webkit-background-clip:text; -webkit-text-fill-color:transparent;
}
.b-sale-top .b-countdown {
  display:flex; gap:12px;
}
.b-sale-top .b-countdown .cd-box {
  text-align:center; min-width:56px;
  padding:8px 10px; border-radius:12px;
  background:rgba(0,0,0,0.08); border:1px solid rgba(0,0,0,0.1);
  transform:translateZ(12px);
  transition:transform 0.3s ease-out, box-shadow 0.3s;
}
.b-sale-top .b-countdown .cd-box:hover {
  transform:translateZ(20px) scale(1.08);
  box-shadow:0 8px 25px rgba(244,114,182,0.2);
}
.b-sale-top .b-countdown .cd-box .num { display:block; font-size:1.4rem; font-weight:800; color:#111; line-height:1; }
.b-sale-top .b-countdown .cd-box .lbl { font-size:0.6rem; font-weight:600; color:#444; text-transform:uppercase; }
.b-sale-grid { display:grid; grid-template-columns:repeat(4,1fr); gap:16px; transform-style:preserve-3d; }
.b-sale-grid .sale-card {
  border-radius:16px; padding:20px; text-align:center;
  background:rgba(0,0,0,0.06); border:1px solid rgba(0,0,0,0.08);
  transition:all 0.35s cubic-bezier(0.23,1,0.32,1); text-decoration:none; display:block;
  transform:translateZ(8px);
}
.b-sale-grid .sale-card:hover { background:rgba(0,0,0,0.1); transform:translateZ(25px) translateY(-6px) scale(1.03); box-shadow:0 16px 40px rgba(0,0,0,0.2); }
.b-sale-grid .sale-card img { width:80px; height:80px; object-fit:contain; margin-bottom:10px; transition:transform 0.3s; }
.b-sale-grid .sale-card:hover img { transform:translateZ(15px) scale(1.08); }
.b-sale-grid .sale-card .s-name { font-size:0.85rem; font-weight:700; color:#333; margin-bottom:4px; }
.b-sale-grid .sale-card .s-price { font-size:0.9rem; font-weight:800; color:#F472B6; }
.b-sale-grid .sale-card .s-price del { color:rgba(0,0,0,0.3); font-size:0.75rem; margin-left:6px; font-weight:400; }

/* ===== MODERN MARKETING POSTER ===== */
.b-poster-wrap {
  position:relative; border-radius:24px; overflow:hidden;
  background:linear-gradient(135deg,#e8e8e8 0%,#ece8f0 50%,#e4e4e4 100%);
  padding:60px 50px; margin:30px 0;
  transform-style:preserve-3d; perspective:1200px;
  transition: transform 0.3s ease-out, box-shadow 0.4s ease;
}
.b-poster-wrap:hover {
  box-shadow: 0 30px 80px rgba(0,0,0,0.12), 0 0 0 1px rgba(45,212,191,0.1);
}
.b-poster-bg {
  position:absolute; inset:0; overflow:hidden; pointer-events:none;
}
.b-poster-bg span {
  position:absolute; border-radius:50%;
}
.b-poster-bg span:nth-child(1){width:600px;height:600px;background:radial-gradient(circle,rgba(45,212,191,0.08),transparent 70%);top:-200px;right:-100px;}
.b-poster-bg span:nth-child(2){width:400px;height:400px;background:radial-gradient(circle,rgba(244,114,182,0.06),transparent 70%);bottom:-150px;left:-80px;}
.b-poster-content { position:relative; z-index:2; display:flex; align-items:center; gap:40px; transform-style:preserve-3d; }
.b-poster-text { flex:1; transform-style:preserve-3d; }
.b-poster-text .bp-badge {
  display:inline-flex; align-items:center; gap:6px;
  padding:5px 16px; border-radius:50px; font-size:0.75rem; font-weight:700;
  background:rgba(45,212,191,0.12); border:1px solid rgba(45,212,191,0.25);
  color:#2DD4BF; text-transform:uppercase; letter-spacing:1px; margin-bottom:14px;
  transform:translateZ(30px);
}
.b-poster-text h2 {
  font-size:2.6rem; font-weight:900; line-height:1.1; margin-bottom:10px;
  transform:translateZ(20px);
}
.b-poster-text h2 .highlight {
  background:linear-gradient(135deg,#FB7185,#E879F9);
  -webkit-background-clip:text; -webkit-text-fill-color:transparent;
}
.b-poster-text p { color:#666; font-size:1rem; font-weight:500; margin-bottom:8px; max-width:420px; transform:translateZ(10px); }
.b-poster-text .bp-offers {
  display:flex; gap:20px; margin:20px 0; transform:translateZ(18px);
}
.b-poster-text .bp-offers .off-item {
  text-align:center; transition:transform 0.3s;
}
.b-poster-text .bp-offers .off-item:hover { transform:translateZ(25px) scale(1.08); }
.b-poster-text .bp-offers .off-item .big {
  font-size:1.6rem; font-weight:900; color:#F472B6; display:block; line-height:1;
}
.b-poster-text .bp-offers .off-item .sml { font-size:0.7rem; font-weight:600; color:#444; text-transform:uppercase; }
.b-poster-text .bp-btn {
  display:inline-flex; align-items:center; gap:10px;
  padding:14px 36px; border-radius:50px; font-weight:700; font-size:1rem;
  background:#fff; color:#0c0f1e; border:none; cursor:pointer; text-decoration:none;
  transition:all 0.35s cubic-bezier(0.23,1,0.32,1);
  transform:translateZ(20px); box-shadow:0 8px 25px rgba(0,0,0,0.08);
}
.b-poster-text .bp-btn:hover { transform:translateZ(30px) translateY(-3px) scale(1.04); box-shadow:0 14px 40px rgba(0,0,0,0.15); }
.b-poster-grid { flex:1; display:grid; grid-template-columns:repeat(2,1fr); gap:14px; max-width:350px; transform:translateZ(25px); transform-style:preserve-3d; }
.b-poster-grid .pg-card {
  border-radius:16px; padding:16px; text-align:center;
  background:rgba(255,255,255,0.05); border:1px solid rgba(0,0,0,0.1);
  backdrop-filter:blur(6px); transition:all 0.35s cubic-bezier(0.23,1,0.32,1);
  transform:translateZ(8px);
}
.b-poster-grid .pg-card:hover { transform:translateZ(22px) translateY(-5px) scale(1.03); background:rgba(0,0,0,0.1); box-shadow:0 12px 35px rgba(0,0,0,0.15); }
.b-poster-grid .pg-card img { width:60px; height:60px; object-fit:contain; margin-bottom:6px; transition:transform 0.3s; }
.b-poster-grid .pg-card:hover img { transform:translateZ(12px) scale(1.1); }
.b-poster-grid .pg-card .pg-name { font-size:0.7rem; font-weight:600; color: #555; }
.b-poster-grid .pg-card .pg-price { font-size:0.75rem; font-weight:800; color:#111; }

/* ===== SEASONAL CAMPAIGN – MONSOON ===== */
.b-rain-wrap {
  position:relative; border-radius:24px; overflow:hidden;
  background:linear-gradient(135deg,#e4e4e4 0%,#e8e8e8 40%,#ece8f0 100%);
  padding:50px 50px; margin:30px 0;
  transform-style:preserve-3d; perspective:1200px;
  transition: transform 0.3s ease-out, box-shadow 0.4s ease;
}
.b-rain-wrap:hover {
  box-shadow: 0 30px 80px rgba(232,121,249,0.12), 0 0 0 1px rgba(232,121,249,0.08);
}
.b-rain-bg {
  position:absolute; inset:0; overflow:hidden; pointer-events:none;
}
.b-rain-bg .rain-layer {
  position:absolute; inset:0;
  background-image:linear-gradient(transparent 0%,rgba(244,114,182,0.03) 50%,transparent 100%);
  background-size:100% 3px;
  animation:rainFall 0.8s linear infinite;
}
@keyframes rainFall { 0%{background-position:0 0;} 100%{background-position:0 20px;} }
.b-rain-bg span {
  position:absolute; border-radius:50%; opacity:0.08;
}
.b-rain-bg span:nth-child(2){width:350px;height:350px;background:#E879F9;top:-80px;right:-60px;filter:blur(80px);}
.b-rain-bg span:nth-child(3){width:250px;height:250px;background:#F472B6;bottom:-60px;left:-40px;filter:blur(70px);}
.b-rain-bg .b-umbrella {
  position:absolute; bottom:10px; right:30px; font-size:6rem; opacity:0.04;
}
.b-rain-content { position:relative; z-index:2; display:flex; align-items:center; gap:30px; transform-style:preserve-3d; }
.b-rain-text { flex:1; transform-style:preserve-3d; }
.b-rain-text .r-badge {
  display:inline-flex; align-items:center; gap:8px;
  padding:6px 18px; border-radius:50px; font-size:0.75rem; font-weight:700;
  background:rgba(167,139,250,0.12); border:1px solid rgba(167,139,250,0.25);
  color:#A78BFA; margin-bottom:14px;
  transform:translateZ(30px);
}
.b-rain-text h2 {
  font-size:2.4rem; font-weight:900; line-height:1.15; margin-bottom:10px;
  transform:translateZ(20px);
}
.b-rain-text h2 .r-grad {
  background:linear-gradient(135deg,#E879F9,#F472B6);
  -webkit-background-clip:text; -webkit-text-fill-color:transparent;
}
.b-rain-text p { color:#666; font-size:0.95rem; font-weight:500; max-width:400px; margin-bottom:20px; transform:translateZ(10px); }
.b-rain-text .r-btn {
  display:inline-flex; align-items:center; gap:10px;
  padding:13px 30px; border-radius:50px; font-weight:700;
  background:linear-gradient(135deg,#E879F9,#C026D3); color:#fff; border:none;
  cursor:pointer; text-decoration:none; transition:all 0.35s cubic-bezier(0.23,1,0.32,1);
  transform:translateZ(20px); box-shadow:0 8px 25px rgba(232,121,249,0.3);
}
.b-rain-text .r-btn:hover { transform:translateZ(30px) translateY(-3px) scale(1.04); box-shadow:0 14px 40px rgba(232,121,249,0.4); }
.b-rain-grid { flex:1; display:grid; grid-template-columns:repeat(2,1fr); gap:14px; max-width:380px; transform:translateZ(25px); transform-style:preserve-3d; }
.b-rain-grid .rc-card {
  border-radius:16px; padding:14px; text-align:center;
  background:rgba(0,0,0,0.06); border:1px solid rgba(0,0,0,0.08);
  transition:all 0.35s cubic-bezier(0.23,1,0.32,1); text-decoration:none; display:block;
  transform:translateZ(8px);
}
.b-rain-grid .rc-card:hover { background:rgba(0,0,0,0.1); transform:translateZ(22px) translateY(-5px) scale(1.03); box-shadow:0 12px 35px rgba(0,0,0,0.15); }
.b-rain-grid .rc-card img { width:64px; height:64px; object-fit:contain; margin-bottom:6px; transition:transform 0.3s; }
.b-rain-grid .rc-card:hover img { transform:translateZ(12px) scale(1.1); }
.b-rain-grid .rc-card .rc-name { font-size:0.75rem; font-weight:700; color:#333; }
.b-rain-grid .rc-card .rc-price { font-size:0.8rem; font-weight:800; color:#F472B6; }

/* ===== APPLE-INSPIRED PREMIUM ===== */
.b-apple-wrap {
  position:relative; border-radius:24px; overflow:hidden;
  background:linear-gradient(135deg,#e8e8e8,#ece8f0); padding:60px 50px; margin:30px 0;
  transform-style:preserve-3d; perspective:1200px;
  transition: transform 0.3s ease-out, box-shadow 0.4s ease;
}
.b-apple-wrap:hover {
  box-shadow: 0 30px 80px rgba(0,0,0,0.12), 0 0 0 1px rgba(0,0,0,0.06);
}
.b-apple-bg {
  position:absolute; inset:0; overflow:hidden; pointer-events:none;
}
.b-apple-bg .apple-glow {
  position:absolute; width:500px; height:500px; border-radius:50%;
  background:radial-gradient(circle,rgba(0,0,0,0.06),transparent 70%);
  top:-150px; right:-100px;
}
.b-apple-bg .apple-glow2 {
  position:absolute; width:400px; height:400px; border-radius:50%;
  background:radial-gradient(circle,rgba(0,0,0,0.04),transparent 70%);
  bottom:-100px; left:-80px;
}
.b-apple-content { position:relative; z-index:2; display:flex; align-items:center; gap:50px; transform-style:preserve-3d; }
.b-apple-text { flex:1; transform-style:preserve-3d; }
.b-apple-text .apple-tag {
  font-size:0.7rem; font-weight:700; letter-spacing:2px;
  color:#999; text-transform:uppercase; margin-bottom:12px;
  transform:translateZ(30px);
}
.b-apple-text h2 {
  font-size:2.8rem; font-weight:300; line-height:1.1; margin-bottom:10px; color:#111;
  transform:translateZ(20px);
}
.b-apple-text h2 strong { font-weight:900; background:linear-gradient(135deg,#111,rgba(0,0,0,0.6)); -webkit-background-clip:text; -webkit-text-fill-color:transparent; }
.b-apple-text p { color:#777; font-size:0.95rem; font-weight:500; max-width:400px; margin-bottom:24px; transform:translateZ(10px); }
.b-apple-text .apple-btn {
  display:inline-flex; align-items:center; gap:10px;
  padding:14px 36px; border-radius:50px; font-weight:700;
  background:#fff; color:#0a0a0a; border:none; cursor:pointer; text-decoration:none;
  font-size:0.95rem; transition:all 0.35s cubic-bezier(0.23,1,0.32,1);
  transform:translateZ(20px); box-shadow:0 8px 25px rgba(0,0,0,0.08);
}
.b-apple-text .apple-btn:hover { background:rgba(255,255,255,0.85); transform:translateZ(30px) translateY(-3px) scale(1.04); box-shadow:0 14px 40px rgba(0,0,0,0.12); }
.b-apple-visual { flex:1; display:flex; justify-content:center; align-items:center; position:relative; transform:translateZ(25px); transform-style:preserve-3d; }
.b-apple-visual .apple-display {
  position:relative; width:300px; height:200px; border-radius:20px;
  background:linear-gradient(135deg,rgba(0,0,0,0.1),rgba(0,0,0,0.04));
  border:1px solid rgba(0,0,0,0.1);
  backdrop-filter:blur(20px); box-shadow:0 20px 60px rgba(0,0,0,0.1),0 0 0 1px rgba(0,0,0,0.06) inset;
  display:flex; align-items:center; justify-content:center; gap:20px;
  transform-style:preserve-3d; transition:transform 0.3s ease-out;
}
.b-apple-visual .apple-display:hover {
  transform:translateZ(15px) scale(1.02);
}
.b-apple-visual .apple-display .ap-product {
  width:80px; height:80px; object-fit:contain;
  filter:drop-shadow(0 10px 30px rgba(0,0,0,0.4));
  animation:floatP 4s ease-in-out infinite;
  transform:translateZ(15px);
}
.b-apple-visual .apple-display .ap-product:nth-child(2){animation-delay:0.8s;}
.b-apple-visual .apple-display .ap-product:nth-child(3){animation-delay:1.6s;}
.b-apple-visual .apple-shine {
  position:absolute; top:-50px; left:-50px; width:100px; height:100px;
  background:radial-gradient(circle,rgba(0,0,0,0.1),transparent 70%);
  border-radius:50%; filter:blur(20px);
}
.b-apple-visual .apple-shine2 {
  position:absolute; bottom:-30px; right:-30px; width:80px; height:80px;
  background:radial-gradient(circle,rgba(244,114,182,0.04),transparent 70%);
  border-radius:50%; filter:blur(15px);
}

/* ===== GLASSMORPHISM & SOFT UI ===== */
.b-glass-wrap {
  position:relative; border-radius:24px; overflow:hidden;
  background:linear-gradient(135deg,#e8e8e8,#e0e0e0); padding:60px 50px; margin:30px 0;
  transform-style:preserve-3d; perspective:1200px;
  transition: transform 0.3s ease-out, box-shadow 0.4s ease;
}
.b-glass-wrap:hover {
  box-shadow: 0 30px 80px rgba(244,114,182,0.12), 0 0 0 1px rgba(244,114,182,0.08);
}
.b-glass-bg {
  position:absolute; inset:0; overflow:hidden; pointer-events:none;
}
.b-glass-bg .gl-orbs span {
  position:absolute; border-radius:50%; opacity:0.06;
}
.b-glass-bg .gl-orbs span:nth-child(1){width:400px;height:400px;background:#F472B6;top:-100px;left:-100px;filter:blur(80px);}
.b-glass-bg .gl-orbs span:nth-child(2){width:350px;height:350px;background:#E879F9;bottom:-120px;right:-80px;filter:blur(70px);}
.b-glass-bg .gl-orbs span:nth-child(2){width:250px;height:250px;background:#FB7185;top:40%;left:40%;filter:blur(60px);}
.b-glass-header { position:relative; z-index:2; text-align:center; margin-bottom:36px; transform:translateZ(20px); }
.b-glass-header h3 {
  font-size:2rem; font-weight:900;
  background:linear-gradient(135deg,#F472B6,#E879F9,#FB7185);
  -webkit-background-clip:text; -webkit-text-fill-color:transparent;
}
.b-glass-header p { color:#444; font-size:0.9rem; font-weight:600; }
.b-glass-grid { position:relative; z-index:2; display:grid; grid-template-columns:repeat(4,1fr); gap:20px; transform:translateZ(15px); transform-style:preserve-3d; }
.b-glass-grid .gl-card {
  border-radius:20px; padding:28px 20px; text-align:center;
  background:rgba(0,0,0,0.12);
  backdrop-filter:blur(20px); -webkit-backdrop-filter:blur(20px);
  border:1px solid rgba(0,0,0,0.15);
  box-shadow:0 8px 32px rgba(0,0,0,0.06);
  transition:all 0.4s cubic-bezier(0.34,1.56,0.64,1);
  text-decoration:none; display:block;
  transform:translateZ(8px);
}
.b-glass-grid .gl-card:hover {
  transform:translateZ(30px) translateY(-10px) scale(1.03);
  box-shadow:0 20px 55px rgba(0,0,0,0.15),0 0 0 1px rgba(0,0,0,0.15) inset;
}
.b-glass-grid .gl-card .gl-icon {
  width:60px; height:60px; margin:0 auto 14px; border-radius:16px;
  display:flex; align-items:center; justify-content:center; font-size:1.4rem;
  background:rgba(244,114,182,0.1); border:1px solid rgba(244,114,182,0.15);
  transition:transform 0.3s;
}
.b-glass-grid .gl-card:hover .gl-icon { transform:translateZ(15px) scale(1.1); }
.b-glass-grid .gl-card .gl-name { font-size:0.85rem; font-weight:700; color:#333; margin-bottom:4px; }
.b-glass-grid .gl-card .gl-desc { font-size:0.7rem; font-weight:500; color:#444; }
.b-glass-grid .gl-card .gl-price { font-size:0.9rem; font-weight:800; color:#F472B6; margin-top:8px; }

/* ===== RESPONSIVE ===== */
@media(max-width:991px){
  .b-3d-content,.b-poster-content,.b-rain-content,.b-apple-content{flex-direction:column;text-align:center;}
  .b-3d-text p,.b-poster-text p,.b-rain-text p,.b-apple-text p{margin-left:auto;margin-right:auto;}
  .b-3d-text .b-label,.b-poster-text .bp-badge,.b-rain-text .r-badge,.b-apple-text .apple-tag{justify-content:center;}
  .b-poster-text .bp-offers{justify-content:center;}
  .b-sale-grid{grid-template-columns:repeat(2,1fr);}
  .b-glass-grid{grid-template-columns:repeat(2,1fr);}
}
@media(max-width:575px){
  .b-3d-wrap,.b-sale-wrap,.b-poster-wrap,.b-rain-wrap,.b-apple-wrap,.b-glass-wrap{padding:30px 20px;}
  .b-3d-text h2,.b-poster-text h2,.b-rain-text h2,.b-apple-text h2{font-size:1.8rem;}
  .b-sale-grid,.b-glass-grid{grid-template-columns:1fr;}
  .b-poster-grid{grid-template-columns:1fr;max-width:100%;}
}
</style>

<!-- HERO — 3D ANIMATED BANNER -->
<section class="hero-3d-section" id="hero3dSection">
    <div class="hero-3d-bg"></div>
    <div class="hero-3d-grid"></div>
    <div class="hero-stars" id="heroStars"></div>
    <div class="hero-nebula"></div>
    <div class="hero-aurora"></div>

    <!-- 3D Floating Orbs -->
    <div class="hero-3d-orb" style="width:200px;height:200px;background:rgba(112,0,255,0.12);top:10%;left:5%;--tx:30px;--ty:-20px;--tz:60px;--dur:10s;--delay:0s;"></div>
    <div class="hero-3d-orb" style="width:150px;height:150px;background:rgba(0,240,255,0.1);top:60%;right:8%;--tx:-25px;--ty:-35px;--tz:40px;--dur:12s;--delay:2s;"></div>
    <div class="hero-3d-orb" style="width:120px;height:120px;background:rgba(255,0,127,0.08);top:30%;right:25%;--tx:20px;--ty:-25px;--tz:50px;--dur:9s;--delay:4s;"></div>

    <div class="hero-3d-inner">
        <!-- Heading -->
        <div class="hero-3d-header">
            <span class="hero-3d-badge"><i class="fas fa-bolt"></i> Exclusive Deals</span>
            <h1 class="hero-3d-title">ShopSphere's <span class="gradient-text">Galactic Grand Offer</span></h1>
            <div class="hero-marquee">
                <div class="hero-marquee-track">
                    <span>🔥 Flash Deals Live Now</span>
                    <span>✈️ 25% Off Flights</span>
                    <span>📱 10% Off Samsung</span>
                    <span>👟 Monsoon Specials</span>
                    <span>⚡ Hourly Flash Sale</span>
                    <span>🏷️ Up to 5% Storewide</span>
                    <span>🔥 Flash Deals Live Now</span>
                    <span>✈️ 25% Off Flights</span>
                    <span>📱 10% Off Samsung</span>
                    <span>👟 Monsoon Specials</span>
                    <span>⚡ Hourly Flash Sale</span>
                    <span>🏷️ Up to 5% Storewide</span>
                </div>
            </div>
        </div>

        <!-- 3D Cards Container -->
        <div class="hero-3d-cards" id="hero3dCards">

            <!-- Card 1: Flight Booking -->
            <div class="hero-3d-card card-active" data-index="0">
                <div class="card-inner">
                    <div class="card-bg" style="background:url('<?= BASE_URL ?>assets/images/uploads/WhatsApp Image 2026-07-24 at 8.36.44 AM.jpeg') center/cover no-repeat;"></div>
                    <div class="card-overlay"></div>
                    <div class="card-glow card-glow-cyan"></div>
                    <div class="card-shine"></div>
                    <div class="card-particles">
                        <span style="top:12%;left:8%;--dur:5s;--delay:0s;">✈</span>
                        <span style="top:20%;right:12%;--dur:7s;--delay:1s;">☁</span>
                        <span style="bottom:25%;left:15%;--dur:6s;--delay:0.5s;">🌍</span>
                        <span style="top:55%;right:8%;--dur:8s;--delay:2s;">☁</span>
                        <span style="bottom:10%;right:20%;--dur:5.5s;--delay:1.5s;">🧳</span>
                    </div>
                    <div class="card-body">
                        <span class="card-tag"><i class="fas fa-plane"></i> Travel</span>
                        <h2>Fly High with<br><span class="card-hl" style="color:#00F0FF;">25% Off</span></h2>
                        <p>Book flight tickets at unbeatable prices. Limited time offer on all domestic routes!</p>
                        <a href="<?= BASE_URL ?>book-flight.php" class="card-cta" style="background:linear-gradient(135deg,#00F0FF,#0070FF);">Book Now <i class="fas fa-arrow-right"></i></a>
                    </div>
                </div>
                <div class="card-shadow" style="box-shadow:0 30px 80px rgba(0,240,255,0.12),0 0 40px rgba(0,240,255,0.06);"></div>
            </div>

            <!-- Card 2: Samsung Mobile -->
            <div class="hero-3d-card card-next" data-index="1">
                <div class="card-inner">
                    <div class="card-bg" style="background:url('<?= BASE_URL ?>assets/images/uploads/WhatsApp Image 2026-07-24 at 8.36.45 AM.jpeg') center/cover no-repeat;"></div>
                    <div class="card-overlay"></div>
                    <div class="card-glow card-glow-purple"></div>
                    <div class="card-shine"></div>
                    <div class="card-particles">
                        <span style="top:12%;left:8%;--dur:5s;--delay:0s;">📱</span>
                        <span style="top:20%;right:12%;--dur:7s;--delay:1s;">⭐</span>
                        <span style="bottom:25%;left:15%;--dur:6s;--delay:0.5s;">🔔</span>
                        <span style="top:55%;right:8%;--dur:8s;--delay:2s;">✨</span>
                        <span style="bottom:10%;right:20%;--dur:5.5s;--delay:1.5s;">💬</span>
                    </div>
                    <div class="card-body">
                        <span class="card-tag" style="background:linear-gradient(135deg,#7000FF,#FF007F);"><i class="fas fa-mobile-alt"></i> Electronics</span>
                        <h2>Get <span class="card-hl" style="color:#b44dff;">10% Off</span><br>Latest Smartphones</h2>
                        <p>Samsung's newest collection with premium specs. Earn reward points on every purchase!</p>
                        <a href="<?= BASE_URL ?>products.php?category=mobile-phones" class="card-cta" style="background:linear-gradient(135deg,#7000FF,#b44dff);">Shop Phones <i class="fas fa-arrow-right"></i></a>
                    </div>
                </div>
                <div class="card-shadow" style="box-shadow:0 30px 80px rgba(112,0,255,0.12),0 0 40px rgba(112,0,255,0.06);"></div>
            </div>

            <!-- Card 3: Monsoon Shoes -->
            <div class="hero-3d-card card-hidden" data-index="2">
                <div class="card-inner">
                    <div class="card-bg" style="background:url('<?= BASE_URL ?>assets/images/uploads/moonsoon.jpeg') center/cover no-repeat;"></div>
                    <div class="card-overlay"></div>
                    <div class="card-glow card-glow-green"></div>
                    <div class="card-shine"></div>
                    <div class="card-particles">
                        <span style="top:12%;left:8%;--dur:5s;--delay:0s;">👟</span>
                        <span style="top:20%;right:12%;--dur:7s;--delay:1s;">💧</span>
                        <span style="bottom:25%;left:15%;--dur:6s;--delay:0.5s;">🌊</span>
                        <span style="top:55%;right:8%;--dur:8s;--delay:2s;">💧</span>
                        <span style="bottom:10%;right:20%;--dur:5.5s;--delay:1.5s;">👟</span>
                    </div>
                    <div class="card-body">
                        <span class="card-tag" style="background:linear-gradient(135deg,#00ff88,#00F0FF);"><i class="fas fa-umbrella"></i> Monsoon</span>
                        <h2>Step into <span class="card-hl" style="color:#00ff88;">Comfort</span><br>Special Discount</h2>
                        <p>Waterproof sneakers built for the rains. Premium grip, zero compromise on style.</p>
                        <a href="<?= BASE_URL ?>products.php?category=footwear" class="card-cta" style="background:linear-gradient(135deg,#00ff88,#00b87a);">Shop Shoes <i class="fas fa-arrow-right"></i></a>
                    </div>
                </div>
                <div class="card-shadow" style="box-shadow:0 30px 80px rgba(0,255,136,0.12),0 0 40px rgba(0,255,136,0.06);"></div>
            </div>

            <!-- Card 4: Live Flash Sale -->
            <div class="hero-3d-card card-hidden" data-index="3">
                <div class="card-inner">
                    <div class="card-bg" style="background:url('<?= BASE_URL ?>assets/images/uploads/scale.jpeg') center/cover no-repeat;"></div>
                    <div class="card-overlay"></div>
                    <div class="card-glow card-glow-gold"></div>
                    <div class="card-shine"></div>
                    <div class="card-particles">
                        <span style="top:12%;left:8%;--dur:5s;--delay:0s;">⚡</span>
                        <span style="top:20%;right:12%;--dur:7s;--delay:1s;">🔥</span>
                        <span style="bottom:25%;left:15%;--dur:6s;--delay:0.5s;">⏰</span>
                        <span style="top:55%;right:8%;--dur:8s;--delay:2s;">💰</span>
                        <span style="bottom:10%;right:20%;--dur:5.5s;--delay:1.5s;">⚡</span>
                    </div>
                    <div class="card-body">
                        <span class="card-tag" style="background:linear-gradient(135deg,#FFD700,#FFA500);"><i class="fas fa-bolt"></i> Flash Sale</span>
                        <h2>Live <span class="card-hl" style="color:#FFD700;">Flash Sale</span><br>On All Products</h2>
                        <p>Hourly flash deals with massive discounts. Hurry — stocks run out fast!</p>
                        <a href="<?= BASE_URL ?>deals.php" class="card-cta" style="background:linear-gradient(135deg,#FFD700,#FFA500);color:#0a0a2e;">Grab Deal <i class="fas fa-arrow-right"></i></a>
                    </div>
                </div>
                <div class="card-shadow" style="box-shadow:0 30px 80px rgba(255,215,0,0.12),0 0 40px rgba(255,215,0,0.06);"></div>
            </div>

            <!-- Card 5: Storewide Discount -->
            <div class="hero-3d-card card-hidden" data-index="4">
                <div class="card-inner">
                    <div class="card-bg" style="background:url('<?= BASE_URL ?>assets/images/uploads/WhatsApp Image 2026-07-24 at 8.36.46 AM.jpeg') center/cover no-repeat;"></div>
                    <div class="card-overlay"></div>
                    <div class="card-glow card-glow-pink"></div>
                    <div class="card-shine"></div>
                    <div class="card-particles">
                        <span style="top:12%;left:8%;--dur:5s;--delay:0s;">🏷️</span>
                        <span style="top:20%;right:12%;--dur:7s;--delay:1s;">🎉</span>
                        <span style="bottom:25%;left:15%;--dur:6s;--delay:0.5s;">💸</span>
                        <span style="top:55%;right:8%;--dur:8s;--delay:2s;">🎊</span>
                        <span style="bottom:10%;right:20%;--dur:5.5s;--delay:1.5s;">🏷️</span>
                    </div>
                    <div class="card-body">
                        <span class="card-tag" style="background:linear-gradient(135deg,#FF007F,#FFA500);"><i class="fas fa-tags"></i> Storewide</span>
                        <h2>Up to <span class="card-hl" style="color:#FF007F;">5% Off</span><br>On Everything</h2>
                        <p>Storewide savings on every category. Stack with reward points for maximum value!</p>
                        <a href="<?= BASE_URL ?>products.php" class="card-cta" style="background:linear-gradient(135deg,#FF007F,#cc0066);">Shop All <i class="fas fa-arrow-right"></i></a>
                    </div>
                </div>
                <div class="card-shadow" style="box-shadow:0 30px 80px rgba(255,0,127,0.12),0 0 40px rgba(255,0,127,0.06);"></div>
            </div>

        </div>

        <!-- 3D Reflection -->
        <div class="hero-3d-reflection" id="hero3dReflection"></div>

        <!-- Navigation Arrows -->
        <button class="hero-3d-nav nav-prev" id="hero3dPrev"><i class="fas fa-chevron-left"></i></button>
        <button class="hero-3d-nav nav-next" id="hero3dNext"><i class="fas fa-chevron-right"></i></button>

        <!-- 3D Dots -->
        <div class="hero-3d-dots" id="hero3dDots">
            <button class="hero-3d-dot dot-active" data-index="0"></button>
            <button class="hero-3d-dot" data-index="1"></button>
            <button class="hero-3d-dot" data-index="2"></button>
            <button class="hero-3d-dot" data-index="3"></button>
            <button class="hero-3d-dot" data-index="4"></button>
        </div>
    </div>
</section>

<!-- ===== CATEGORY STRIP ===== -->
<?php if (!empty($featured_categories)): ?>
<div class="m-cat-strip">
  <div class="container">
    <div class="m-cat-pills" id="catPills">
      <a href="products.php" class="m-cat-pill active">All</a>
      <?php foreach (array_slice($featured_categories, 0, 12) as $cat): ?>
      <a href="products.php?category=<?= urlencode($cat['slug']) ?>" class="m-cat-pill"><?= htmlspecialchars($cat['name']) ?></a>
      <?php endforeach; ?>
    </div>
  </div>
</div>
<?php endif; ?>

<!-- ===== BROWSE CATEGORIES ===== -->
<?php if (!empty($featured_categories)): ?>
<section class="m-section">
  <div class="container">
    <div class="m-section-header m-anim">
      <h2><i class="fas fa-th-large" style="color:var(--uniq-primary);"></i> Browse Categories</h2>
      <a href="categories.php" class="m-view-all">View All <i class="fas fa-chevron-right" style="font-size:0.7rem;"></i></a>
    </div>
    <div class="m-cat-grid m-anim">
      <?php foreach (array_slice($featured_categories, 0, 8) as $cat): ?>
      <a href="products.php?category=<?= urlencode($cat['slug']) ?>" class="m-cat-item">
        <div class="m-cat-icon"><i class="fas fa-tag"></i></div>
        <div class="m-cat-label"><?= htmlspecialchars($cat['name']) ?></div>
        <div class="m-cat-count"><?= $cat['product_count'] ?? 0 ?> items</div>
      </a>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<!-- ===== FLASH SALE ===== -->
<?php if (!empty($flash_sale_products)): ?>
<section class="m-section" style="background:rgba(0,0,0,0.04);">
  <div class="container">
    <div class="m-flash m-anim">
      <div class="mf-info">
        <h3><i class="fas fa-bolt me-2"></i>Flash Sale</h3>
        <p>Today's deals end at midnight</p>
      </div>
      <div class="mf-timer">
        <div class="tf-box"><span class="num" id="fs-h">00</span><span class="lbl">Hrs</span></div>
        <div class="tf-box"><span class="num" id="fs-m">00</span><span class="lbl">Min</span></div>
        <div class="tf-box"><span class="num" id="fs-s">00</span><span class="lbl">Sec</span></div>
      </div>
    </div>
    <div class="m-prod-row" id="flashRow">
      <?php foreach ($flash_sale_products as $product): ?>
      <div class="m-prod-card m-anim">
        <a href="product.php?slug=<?= htmlspecialchars($product['slug']) ?>">
          <div class="m-p-img">
            <img src="<?= htmlspecialchars($product['image_url'] ?: 'https://placehold.co/400x400?text=Product') ?>" alt="<?= htmlspecialchars($product['name']) ?>">
            <div class="m-p-badge">-<?= round((1 - ($product['sale_price'] ?? 0) / ($product['price'] ?? 1)) * 100) ?>%</div>
          </div>
        </a>
        <div class="m-p-body">
          <div class="m-p-name"><?= htmlspecialchars($product['name']) ?></div>
          <div class="m-p-meta">Flash Deal</div>
          <div class="m-p-price">
            <span class="curr">₹<?= number_format($product['sale_price'] ?? $product['price'], 0) ?></span>
            <?php if (!empty($product['price']) && ($product['sale_price'] ?? 0) < $product['price']): ?>
              <del class="orig">₹<?= number_format($product['price'], 0) ?></del>
            <?php endif; ?>
          </div>
        </div>
        <button class="m-p-btn add-to-cart-btn" data-product-id="<?= $product['id'] ?>"><i class="fas fa-plus"></i></button>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<!-- ===== 1. 3D Promotional Banner ===== -->
<section class="m-section">
  <div class="container">
    <div class="b-3d-wrap m-anim">
      <div class="b-3d-bg"><span></span><span></span><span></span></div>
      <div class="b-3d-grid"></div>
      <div class="b-3d-content">
        <div class="b-3d-text">
          <div class="b-label"><i class="fas fa-crown"></i> Premium Collection</div>
          <h2>Next-Gen <span class="t3d" style="transform:perspective(500px) rotateX(2deg) rotateY(-2deg);">3D Showcase</span><br>Experience in Style</h2>
          <p>Step into the future of shopping. Top brands, AI curated picks just for you.</p>
          <a href="products.php" class="b-btn">Shop Collection <i class="fas fa-arrow-right"></i></a>
        </div>
        <div class="b-3d-visual">
          <div class="b-3d-podium">
            <div class="pedestal"><img src="<?= BASE_URL ?>assets/uploads/products/Samsung Galaxy S25 Ultra.jpeg" alt="S25 Ultra"></div>
            <div class="pedestal"><img src="<?= BASE_URL ?>assets/uploads/products/iphone 16 pro max.jpeg" alt="iPhone"></div>
            <div class="pedestal"><img src="<?= BASE_URL ?>assets/uploads/products/oneplus 13 pro.jpeg" alt="OnePlus"></div>
          </div>
        </div>
      </div>
      <div class="b-3d-floor"></div>
    </div>
  </div>
</section>

<!-- ===== 2. E-commerce Sale Banner ===== -->
<section class="m-section">
  <div class="container">
    <div class="b-sale-wrap m-anim">
      <div class="b-sale-bg"><span></span><span></span></div>
      <div class="b-sale-content">
        <div class="b-sale-top">
          <h3><i class="fas fa-bolt me-2"></i>Flash Sale Deals</h3>
          <div class="b-countdown">
            <div class="cd-box"><span class="num" id="s-h">00</span><span class="lbl">Hrs</span></div>
            <div class="cd-box"><span class="num" id="s-m">00</span><span class="lbl">Min</span></div>
            <div class="cd-box"><span class="num" id="s-s">00</span><span class="lbl">Sec</span></div>
          </div>
        </div>
        <div class="b-sale-grid" id="saleGrid">
          <?php foreach (array_slice($flash_sale_products ?: $trending_products ?: $featured_products ?: $new_arrivals ?: $all_home_products ?: [], 0, 4) as $sale_product): ?>
          <a href="<?= BASE_URL ?>product.php?slug=<?= htmlspecialchars($sale_product['slug'] ?? '') ?>" class="sale-card">
            <img src="<?= htmlspecialchars($sale_product['image_url'] ?: 'https://placehold.co/400x400?text=Product') ?>" alt="<?= htmlspecialchars($sale_product['name'] ?? '') ?>">
            <div class="s-name"><?= htmlspecialchars($sale_product['name'] ?? '') ?></div>
            <div class="s-price">₹<?= number_format($sale_product['sale_price'] ?? $sale_product['price'] ?? 0, 0) ?>
              <?php if (!empty($sale_product['price']) && ($sale_product['sale_price'] ?? 0) < $sale_product['price']): ?>
                <del>₹<?= number_format($sale_product['price'], 0) ?></del>
              <?php endif; ?>
            </div>
          </a>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ===== 3. Modern Marketing Poster ===== -->
<section class="m-section">
  <div class="container">
    <div class="b-poster-wrap m-anim">
      <div class="b-poster-bg"><span></span><span></span></div>
      <div class="b-poster-content">
        <div class="b-poster-text">
          <div class="bp-badge"><i class="fas fa-fire"></i> Limited Edition</div>
          <h2>Don't miss the <span class="highlight">Big Sale</span></h2>
          <p>Up to 60% off the season's most wanted products. Bold deals, better prices.</p>
          <div class="bp-offers">
            <div class="off-item"><span class="big">60%</span><span class="sml"> Electronics</span></div>
            <div class="off-item"><span class="big">50%</span><span class="sml"> Fashion</span></div>
            <div class="off-item"><span class="big">40%</span><span class="sml"> Home</span></div>
          </div>
          <a href="products.php" class="bp-btn">Explore Offers <i class="fas fa-arrow-right"></i></a>
        </div>
        <div class="b-poster-grid">
          <?php
          $poster_products = array_slice($new_arrivals ?: $trending_products ?: $all_home_products ?: [], 0, 4);
          foreach ($poster_products as $pp):
          ?>
          <a href="<?= BASE_URL ?>product.php?slug=<?= htmlspecialchars($pp['slug'] ?? '') ?>" class="pg-card">
            <img src="<?= htmlspecialchars($pp['image_url'] ?: 'https://placehold.co/400x400?text=Product') ?>" alt="<?= htmlspecialchars($pp['name'] ?? '') ?>">
            <div class="pg-name"><?= htmlspecialchars($pp['name'] ?? '') ?></div>
            <div class="pg-price">₹<?= number_format($pp['sale_price'] ?? $pp['price'] ?? 0, 0) ?></div>
          </a>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ===== 4. Seasonal Campaign – Monsoon ===== -->
<section class="m-section">
  <div class="container">
    <div class="b-rain-wrap m-anim">
      <div class="b-rain-bg">
        <div class="rain-layer"></div>
        <span></span><span></span>
        <div class="b-umbrella">&#9730;</div>
        <div class="rain-layer" style="animation-duration:0.6s;"></div>
      </div>
      <div class="b-rain-content">
        <div class="b-rain-text">
          <div class="r-badge"><i class="fas fa-cloud-rain"></i> Monsoon Special</div>
          <h2>Rainy Day <span class="r-grad">Vibes</span><br>Cozy deals await</h2>
          <p>Don't let the rain stop you. Snug season picks with free shipping and surprise discounts.</p>
          <a href="products.php" class="r-btn">Shop Seasonal <i class="fas fa-umbrella"></i></a>
        </div>
        <div class="b-rain-grid">
          <?php
          $rain_products = array_slice($featured_products ?: $all_home_products ?: [], 0, 4);
          foreach ($rain_products as $rp):
          ?>
          <a href="<?= BASE_URL ?>product.php?slug=<?= htmlspecialchars($rp['slug'] ?? '') ?>" class="rc-card">
            <img src="<?= htmlspecialchars($rp['image_url'] ?: 'https://placehold.co/400x400?text=Product') ?>" alt="<?= htmlspecialchars($rp['name'] ?? '') ?>">
            <div class="rc-name"><?= htmlspecialchars($rp['name'] ?? '') ?></div>
            <div class="rc-price">₹<?= number_format($rp['sale_price'] ?? $rp['price'] ?? 0, 0) ?></div>
          </a>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ===== 5. Apple-Inspired Premium ===== -->
<section class="m-section">
  <div class="container">
    <div class="b-apple-wrap m-anim">
      <div class="b-apple-bg">
        <div class="apple-glow"></div>
        <div class="apple-glow2"></div>
      </div>
      <div class="b-apple-content">
        <div class="b-apple-text">
          <div class="apple-tag">PREMIUM SELECTION</div>
          <h2>Designed to impress. <strong>Built to last.</strong></h2>
          <p>Top-of-the-line products with unmatched design and engineering.</p>
          <a href="products.php" class="apple-btn">Discover <i class="fas fa-arrow-right"></i></a>
        </div>
        <div class="b-apple-visual">
          <div class="apple-display">
            <img src="<?= BASE_URL ?>assets/uploads/products/Apple MacBook Air M3.jpeg" class="ap-product" alt="MacBook">
            <img src="<?= BASE_URL ?>assets/uploads/products/Apple AirPods Pro 3.jpeg" class="ap-product" alt="AirPods">
            <img src="<?= BASE_URL ?>assets/uploads/products/Samsung Galaxy S25 Ultra.jpeg" class="ap-product" alt="S25 Ultra">
            <div class="apple-shine"></div>
            <div class="apple-shine2"></div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ===== 6. Glassmorphism & Soft UI ===== -->
<section class="m-section">
  <div class="container">
    <div class="b-glass-wrap m-anim">
      <div class="b-glass-bg">
        <div class="gl-orbs"><span></span><span></span></div>
        <div class="gl-orbs" style="position:absolute;top:50%;left:50%;width:200px;height:200px;background:rgba(244,114,182,0.04);border-radius:50%;filter:blur(50px);transform:translate(-50%,-50%);"></div>
      </div>
      <div class="b-glass-header">
        <h3>Popular Categories</h3>
        <p>Frosted-glass UI Selection</p>
      </div>
      <div class="b-glass-grid">
        <?php
        $glass_products = array_slice($all_home_products ?: $trending_products ?: $featured_products ?: [], 0, 4);
        $gl_icons = ['laptop','chair','headphones','mobile-alt'];
        $gl_descs = ['Top performing laptops','Comfortable designs','Premium headsets','Latest smartphones'];
        $i = 0;
        foreach ($glass_products as $gp):
        ?>
        <a href="<?= BASE_URL ?>product.php?slug=<?= htmlspecialchars($gp['slug'] ?? '') ?>" class="gl-card">
          <div class="gl-icon"><i class="fas fa-<?= $gl_icons[$i] ?? 'tag' ?>"></i></div>
          <div class="gl-name"><?= htmlspecialchars(implode(' ', array_slice(explode(' ',$gp['name'] ?? ''),0,2))) ?></div>
          <div class="gl-desc"><?= $gl_descs[$i] ?? '' ?> starting ₹<?= number_format($gp['sale_price'] ?? $gp['price'] ?? 0,0) ?></div>
          <div class="gl-price">₹<?= number_format($gp['sale_price'] ?? $gp['price'] ?? 0,0) ?></div>
        </a>
        <?php $i++; endforeach; ?>
      </div>
      <div class="b-glass-grid" style="margin-top:20px;">
        <?php
        $glass_products2 = array_slice($all_home_products ?: $featured_products ?: $new_arrivals ?: [], 4, 4);
        $gl_icons2 = ['tshirt','tablet','couch','book'];
        $gl_descs2 = ['Designer Fashion','Tablet & E-readers','Home essentials','Best books'];
        $i = 0;
        foreach ($glass_products2 as $gp2):
        ?>
        <a href="<?= BASE_URL ?>product.php?slug=<?= htmlspecialchars($gp2['slug'] ?? '') ?>" class="gl-card">
          <div class="gl-icon"><i class="fas fa-<?= $gl_icons2[$i] ?? 'star' ?>"></i></div>
          <div class="gl-name"><?= htmlspecialchars(implode(' ', array_slice(explode(' ',$gp2['name'] ?? ''),0,2))) ?></div>
          <div class="gl-desc"><?= $gl_descs2[$i] ?? 'Popular products' ?></div>
          <div class="gl-price">₹<?= number_format($gp2['sale_price'] ?? $gp2['price'] ?? 0,0) ?></div>
        </a>
        <?php $i++; endforeach; ?>
      </div>
    </div>
    <p class="b-note" style="text-align:center;margin-top:6px;font-size:0.75rem;color:rgba(255,255,255,0.2);">Soft. UI. Experience.</p>
  </div>
</section>

<!-- ===== TRENDING NOW ===== -->
<?php if (!empty($trending_products)): ?>
<section class="m-section" style="background:rgba(0,0,0,0.04);">
  <div class="container">
    <div class="m-section-header m-anim">
      <h2><i class="fas fa-fire" style="color:var(--uniq-secondary);"></i> Trending Now</h2>
      <a href="products.php?sort=trending" class="m-view-all">View All <i class="fas fa-chevron-right" style="font-size:0.7rem;"></i></a>
    </div>
    <div class="m-prod-row" id="trendingRow">
      <?php foreach ($trending_products as $product): ?>
      <div class="m-prod-card m-anim">
        <a href="product.php?slug=<?= htmlspecialchars($product['slug']) ?>">
          <div class="m-p-img">
            <img src="<?= htmlspecialchars($product['image_url'] ?: 'https://placehold.co/400x400?text=Product') ?>" alt="<?= htmlspecialchars($product['name']) ?>">
          </div>
        </a>
        <div class="m-p-body">
          <div class="m-p-name"><?= htmlspecialchars($product['name']) ?></div>
          <div class="m-p-meta">
            <?php if (isset($product['avg_rating'])): ?>
              <?php for ($i = 1; $i <= 5; $i++): ?>
                <i class="fas fa-star" style="font-size:0.55rem;color:<?= $i <= ($product['avg_rating'] ?? 0) ? 'var(--uniq-accent)' : 'rgba(255,255,255,0.1)' ?>"></i>
              <?php endfor; ?>
            <?php endif; ?>
          </div>
          <div class="m-p-price">
            <span class="curr">₹<?= number_format($product['sale_price'] ?? $product['price'], 0) ?></span>
            <?php if (!empty($product['price']) && ($product['sale_price'] ?? 0) < $product['price']): ?>
              <del class="orig">₹<?= number_format($product['price'], 0) ?></del>
            <?php endif; ?>
          </div>
        </div>
        <button class="m-p-btn add-to-cart-btn" data-product-id="<?= $product['id'] ?>"><i class="fas fa-plus"></i></button>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<!-- ===== FEATURED PRODUCTS ===== -->
<?php if (!empty($featured_products)): ?>
<section class="m-section">
  <div class="container">
    <div class="m-section-header m-anim">
      <h2><i class="fas fa-star" style="color:var(--uniq-purple);"></i> Featured Products</h2>
      <a href="products.php" class="m-view-all">View All <i class="fas fa-chevron-right" style="font-size:0.7rem;"></i></a>
    </div>
    <div class="m-prod-row" id="featuredRow">
      <?php foreach (array_slice($featured_products, 0, 10) as $product): ?>
      <div class="m-prod-card m-anim">
        <a href="product.php?slug=<?= htmlspecialchars($product['slug']) ?>">
          <div class="m-p-img">
            <img src="<?= htmlspecialchars($product['image_url'] ?: 'https://placehold.co/400x400?text=Product') ?>" alt="<?= htmlspecialchars($product['name']) ?>">
            <?php if (($product['sale_price'] ?? 0) > 0 && ($product['sale_price'] ?? 0) < ($product['price'] ?? 0)): ?>
              <div class="m-p-badge">-<?= round((1 - ($product['sale_price'] ?? 0) / $product['price']) * 100) ?>%</div>
            <?php endif; ?>
          </div>
        </a>
        <div class="m-p-body">
          <div class="m-p-name"><?= htmlspecialchars($product['name']) ?></div>
          <div class="m-p-meta">Featured</div>
          <div class="m-p-price">
            <span class="curr">₹<?= number_format($product['sale_price'] ?? $product['price'], 0) ?></span>
            <?php if (!empty($product['price']) && ($product['sale_price'] ?? 0) < $product['price']): ?>
              <del class="orig">₹<?= number_format($product['price'], 0) ?></del>
            <?php endif; ?>
          </div>
        </div>
        <button class="m-p-btn add-to-cart-btn" data-product-id="<?= $product['id'] ?>"><i class="fas fa-plus"></i></button>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<!-- ===== BEST SELLERS ===== -->
<?php if (!empty($best_sellers)): ?>
<section class="m-section" style="background:rgba(0,0,0,0.04);">
  <div class="container">
    <div class="m-section-header m-anim">
      <h2><i class="fas fa-trophy" style="color:var(--uniq-accent);"></i> Best Sellers</h2>
      <a href="products.php?sort=popular" class="m-view-all">View All <i class="fas fa-chevron-right" style="font-size:0.7rem;"></i></a>
    </div>
    <div class="m-prod-row" id="bestsellerRow">
      <?php foreach ($best_sellers as $product): ?>
      <div class="m-prod-card m-anim">
        <a href="product.php?slug=<?= htmlspecialchars($product['slug']) ?>">
          <div class="m-p-img">
            <img src="<?= htmlspecialchars($product['image_url'] ?: 'https://placehold.co/400x400?text=Product') ?>" alt="<?= htmlspecialchars($product['name']) ?>">
          </div>
        </a>
        <div class="m-p-body">
          <div class="m-p-name"><?= htmlspecialchars($product['name']) ?></div>
          <div class="m-p-meta">
            <?php if (isset($product['avg_rating'])): ?>
              <?php for ($i = 1; $i <= 5; $i++): ?>
                <i class="fas fa-star" style="font-size:0.55rem;color:<?= $i <= ($product['avg_rating'] ?? 0) ? 'var(--uniq-accent)' : 'rgba(255,255,255,0.1)' ?>"></i>
              <?php endfor; ?>
            <?php endif; ?>
          </div>
          <div class="m-p-price">
            <span class="curr">₹<?= number_format($product['sale_price'] ?? $product['price'], 0) ?></span>
            <?php if (!empty($product['price']) && ($product['sale_price'] ?? 0) < $product['price']): ?>
              <del class="orig">₹<?= number_format($product['price'], 0) ?></del>
            <?php endif; ?>
          </div>
        </div>
        <button class="m-p-btn add-to-cart-btn" data-product-id="<?= $product['id'] ?>"><i class="fas fa-plus"></i></button>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<!-- ===== NEW ARRIVALS ===== -->
<?php if (!empty($new_arrivals)): ?>
<section class="m-section">
  <div class="container">
    <div class="m-section-header m-anim">
      <h2><i class="fas fa-clock" style="color:var(--uniq-primary);"></i> New Arrivals</h2>
      <a href="products.php?sort=newest" class="m-view-all">View All <i class="fas fa-chevron-right" style="font-size:0.7rem;"></i></a>
    </div>
    <div class="m-prod-row" id="newArrivalsRow">
      <?php foreach (array_slice($new_arrivals, 0, 10) as $product): ?>
      <div class="m-prod-card m-anim">
        <a href="product.php?slug=<?= htmlspecialchars($product['slug']) ?>">
          <div class="m-p-img">
            <img src="<?= htmlspecialchars($product['image_url'] ?: 'https://placehold.co/400x400?text=Product') ?>" alt="<?= htmlspecialchars($product['name']) ?>">
          </div>
        </a>
        <div class="m-p-body">
          <div class="m-p-name"><?= htmlspecialchars($product['name']) ?></div>
          <div class="m-p-meta">Just In</div>
          <div class="m-p-price">
            <span class="curr">₹<?= number_format($product['sale_price'] ?? $product['price'], 0) ?></span>
            <?php if (!empty($product['price']) && ($product['sale_price'] ?? 0) < $product['price']): ?>
              <del class="orig">₹<?= number_format($product['price'], 0) ?></del>
            <?php endif; ?>
          </div>
        </div>
        <button class="m-p-btn add-to-cart-btn" data-product-id="<?= $product['id'] ?>"><i class="fas fa-plus"></i></button>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<!-- ===== ALL PRODUCTS ===== -->
<?php if (!empty($all_home_products)): ?>
<section class="m-section" style="background:rgba(0,0,0,0.04);">
  <div class="container">
    <div class="m-section-header m-anim">
      <h2><i class="fas fa-cube" style="color:var(--uniq-primary);"></i> All Products</h2>
      <a href="products.php" class="m-view-all">View All <i class="fas fa-chevron-right" style="font-size:0.7rem;"></i></a>
    </div>
    <div class="m-prod-row" id="allProductsRow">
      <?php foreach ($all_home_products as $product): ?>
      <div class="m-prod-card m-anim">
        <a href="product.php?slug=<?= htmlspecialchars($product['slug']) ?>">
          <div class="m-p-img">
            <img src="<?= htmlspecialchars($product['image_url'] ?: 'https://placehold.co/400x400?text=Product') ?>" alt="<?= htmlspecialchars($product['name']) ?>">
            <?php if (!empty($product['price']) && ($product['sale_price'] ?? 0) < $product['price']): ?>
              <div class="m-p-badge">-<?= round((1 - ($product['sale_price'] ?? 0) / $product['price']) * 100) ?>%</div>
            <?php endif; ?>
          </div>
        </a>
        <div class="m-p-body">
          <div class="m-p-name"><?= htmlspecialchars($product['name']) ?></div>
          <div class="m-p-meta"><?= htmlspecialchars($product['category_name'] ?? '') ?></div>
          <div class="m-p-price">
            <span class="curr">₹<?= number_format($product['sale_price'] ?? $product['price'], 0) ?></span>
            <?php if (!empty($product['price']) && ($product['sale_price'] ?? 0) < $product['price']): ?>
              <del class="orig">₹<?= number_format($product['price'], 0) ?></del>
            <?php endif; ?>
          </div>
        </div>
        <button class="m-p-btn add-to-cart-btn" data-product-id="<?= $product['id'] ?>"><i class="fas fa-plus"></i></button>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<!-- ===== FEATURES ===== -->
<section class="m-features">
  <div class="container">
    <div class="m-feat-item m-anim">
      <div class="mf-icon"><i class="fas fa-truck-fast"></i></div>
      <h6>Free Shipping</h6>
      <p>On orders over ₹999</p>
    </div>
    <div class="m-feat-item m-anim">
      <div class="mf-icon"><i class="fas fa-shield-halved"></i></div>
      <h6>Secure Payment</h6>
      <p>100% protected</p>
    </div>
    <div class="m-feat-item m-anim">
      <div class="mf-icon"><i class="fas fa-rotate-left"></i></div>
      <h6>Easy Returns</h6>
      <p>7-day hassle-free</p>
    </div>
    <div class="m-feat-item m-anim">
      <div class="mf-icon"><i class="fas fa-headset"></i></div>
      <h6>24/7 Support</h6>
      <p>Dedicated help</p>
    </div>
  </div>
</section>

<!-- ===== REVIEWS ===== -->
<?php if (!empty($reviews)): ?>
<section class="m-section" style="background:rgba(0,0,0,0.04);">
  <div class="container">
    <div class="m-section-header m-anim">
      <h2><i class="fas fa-quote-right" style="color:var(--uniq-purple);"></i> What Our Customers Say</h2>
    </div>
    <div class="m-prod-row" id="reviewRow">
      <?php foreach ($reviews as $review): ?>
      <div class="m-prod-card m-anim" style="width:280px;padding:0;">
        <div class="m-p-body">
          <div class="m-p-meta" style="color:var(--uniq-accent);margin-bottom:8px;">
            <?php for ($i = 1; $i <= 5; $i++): ?>
              <i class="fas fa-star" style="font-size:0.65rem;color:<?= $i <= ($review['rating'] ?? 5) ? 'var(--uniq-accent)' : 'rgba(0,0,0,0.1)' ?>"></i>
            <?php endfor; ?>
          </div>
          <div class="m-p-name" style="font-size:0.85rem;white-space:normal;display:-webkit-box;-webkit-line-clamp:3;-webkit-box-orient:vertical;"><?= htmlspecialchars($review['comment'] ?? '') ?></div>
          <div class="m-p-meta" style="margin-top:10px;">— <?= htmlspecialchars($review['user_name'] ?? 'Customer') ?></div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<script>
/* Flash Sale Countdown */
(function(){
    const end=new Date();end.setHours(23,59,59,999);
    function tick(){
        const d=end-new Date();
        if(d<=0){end.setDate(end.getDate()+1);tick();return;}
        document.getElementById('fs-h').textContent=String(Math.floor(d/36e5)).padStart(2,'0');
        document.getElementById('fs-m').textContent=String(Math.floor(d%36e5/6e4)).padStart(2,'0');
        document.getElementById('fs-s').textContent=String(Math.floor(d%6e4/1e3)).padStart(2,'0');
    }
    tick();setInterval(tick,1000);
})();

/* 3D Hero Banner - Mouse Tracking + Card Carousel */
(function(){
    var section=document.getElementById('hero3dSection');
    var cardsContainer=document.getElementById('hero3dCards');
    if(!section||!cardsContainer)return;

    var cards=Array.from(cardsContainer.querySelectorAll('.hero-3d-card'));
    var dots=document.querySelectorAll('.hero-3d-dot');
    var prevBtn=document.getElementById('hero3dPrev');
    var nextBtn=document.getElementById('hero3dNext');
    var reflection=document.getElementById('hero3dReflection');
    var current=0;
    var total=cards.length;
    var isAnimating=false;
    var autoTimer=null;
    var shineEl=null;

    /* --- Build shine element for all cards --- */
    cards.forEach(function(c){
        var shine=document.createElement('div');
        shine.className='card-shine';
        shine.style.cssText='position:absolute;inset:0;z-index:5;border-radius:28px;pointer-events:none;background:linear-gradient(105deg,transparent 40%,rgba(255,255,255,0.06) 45%,rgba(255,255,255,0.1) 50%,rgba(255,255,255,0.06) 55%,transparent 60%);opacity:0;transition:opacity 0.3s ease;';
        c.querySelector('.card-inner').appendChild(shine);
    });

    /* --- Card positioning --- */
    function positionCards(){
        cards.forEach(function(card,i){
            card.classList.remove('card-active','card-prev','card-next','card-hidden');
            var diff=i-current;
            if(diff===0){card.classList.add('card-active');}
            else if(diff===1||(diff===-(total-1))){card.classList.add('card-next');}
            else if(diff===-1||(diff===(total-1))){card.classList.add('card-prev');}
            else{card.classList.add('card-hidden');}
        });
        /* dots */
        dots.forEach(function(d,i){d.classList.toggle('dot-active',i===current);});
        /* reflection */
        if(reflection){
            var activeCard=cards[current];
            var bg=activeCard?activeCard.querySelector('.card-bg'):null;
            if(bg){reflection.style.background=bg.style.background;}
        }
    }

    function goTo(index){
        if(isAnimating||index===current)return;
        isAnimating=true;
        current=index;
        positionCards();
        setTimeout(function(){isAnimating=false;},800);
        resetAuto();
    }
    function next(){goTo((current+1)%total);}
    function prev(){goTo((current-1+total)%total);}

    prevBtn.addEventListener('click',prev);
    nextBtn.addEventListener('click',next);
    dots.forEach(function(d){
        d.addEventListener('click',function(){goTo(parseInt(this.dataset.index));});
    });

    /* --- Auto-play --- */
    function resetAuto(){clearInterval(autoTimer);autoTimer=setInterval(next,4000);}
    resetAuto();

    /* --- Mouse 3D Tilt on Active Card --- */
    section.addEventListener('mousemove',function(e){
        var rect=section.getBoundingClientRect();
        var mx=(e.clientX-rect.left)/rect.width-0.5;
        var my=(e.clientY-rect.top)/rect.height-0.5;

        var activeCard=cards[current];
        if(!activeCard)return;
        var inner=activeCard.querySelector('.card-inner');
        if(!inner)return;

        var rotY=mx*18;
        var rotX=-my*12;
        var translateZ=40+Math.abs(mx)*20+Math.abs(my)*15;
        inner.style.transform='rotateY('+rotY+'deg) rotateX('+rotX+'deg) translateZ('+translateZ+'px)';
        inner.style.transition='transform 0.1s ease-out';

        /* Shine follow mouse */
        var shinePos=50+mx*30;
        var shineElements=activeCard.querySelectorAll('.card-shine');
        shineElements.forEach(function(s){
            s.style.background='linear-gradient('+(105+rotY*2)+'deg,transparent '+(shinePos-15)+'%,rgba(255,255,255,0.08) '+(shinePos-5)+'%,rgba(255,255,255,0.15) '+shinePos+'%,rgba(255,255,255,0.08) '+(shinePos+5)+'%,transparent '+(shinePos+15)+'%)';
            s.style.opacity='1';
        });

        /* Parallax on background */
        var bg=activeCard.querySelector('.card-bg');
        if(bg){bg.style.transform='translateZ(-20px) translate('+(mx*-15)+'px,'+(my*-15)+'px) scale(1.08)';}

        /* Parallax on particles */
        var particles=activeCard.querySelectorAll('.card-particles span');
        particles.forEach(function(p,i){
            var depth=10+i*8;
            p.style.transform='translate3d('+(mx*depth)+'px,'+(my*depth)+'px,'+(depth)+'px)';
        });
    });

    section.addEventListener('mouseleave',function(){
        var activeCard=cards[current];
        if(!activeCard)return;
        var inner=activeCard.querySelector('.card-inner');
        if(inner){inner.style.transform='rotateY(0deg) rotateX(0deg) translateZ(40px)';inner.style.transition='transform 0.6s cubic-bezier(0.23,1,0.32,1)';}
        var bg=activeCard.querySelector('.card-bg');
        if(bg){bg.style.transform='translateZ(-20px) scale(1.08)';bg.style.transition='transform 0.6s ease';}
        var shines=activeCard.querySelectorAll('.card-shine');
        shines.forEach(function(s){s.style.opacity='0';});
    });

    /* --- Touch swipe for mobile --- */
    var touchStartX=0;
    section.addEventListener('touchstart',function(e){touchStartX=e.touches[0].clientX;},{passive:true});
    section.addEventListener('touchend',function(e){
        var diff=touchStartX-e.changedTouches[0].clientX;
        if(Math.abs(diff)>50){diff>0?next():prev();}
    },{passive:true});

    /* init */
    positionCards();
})();

/* Hero Stars Generator with 3D depth */
(function(){
    var c=document.getElementById('heroStars');
    if(!c)return;
    for(var i=0;i<100;i++){
        var s=document.createElement('span');
        s.style.left=Math.random()*100+'%';
        s.style.top=Math.random()*100+'%';
        var tz=(-200+Math.random()*400);
        s.style.setProperty('--tz',tz+'px');
        s.style.setProperty('--dur',(2+Math.random()*4)+'s');
        s.style.setProperty('--delay',(Math.random()*5)+'s');
        var size=(0.5+Math.random()*2.5);
        s.style.width=s.style.height=size+'px';
        s.style.opacity=(0.2+Math.abs(tz/400)*0.5);
        c.appendChild(s);
    }
})();

/* Add to Cart - Fly Animation */
(function(){
    var isMobile=window.innerWidth<768;

    document.addEventListener('click',function(e){
        var btn=e.target.closest('.m-p-btn.add-to-cart-btn');
        if(!btn||btn.classList.contains('adding')||btn.classList.contains('added'))return;
        e.preventDefault();
        e.stopPropagation();

        var productId=btn.dataset.productId;
        var card=btn.closest('.m-prod-card');
        var img=card?card.querySelector('.m-p-img img'):null;
        var cartIcon=document.querySelector('a[href*="cart.php"] .fa-shopping-cart');

        /* 1. Ripple effect */
        var ripple=document.createElement('span');
        ripple.className='m-p-btn-ripple';
        btn.style.overflow='hidden';
        btn.appendChild(ripple);
        setTimeout(function(){if(ripple.parentNode)ripple.remove();},600);

        /* 2. Button spin */
        btn.classList.add('adding');

        /* 3. Fly clone (desktop only) */
        if(!isMobile&&img&&cartIcon){
            var imgRect=img.getBoundingClientRect();
            var cartRect=cartIcon.getBoundingClientRect();
            var clone=document.createElement('div');
            clone.className='m-fly-clone';
            var cloneImg=document.createElement('img');
            cloneImg.src=img.src;
            clone.appendChild(cloneImg);
            clone.style.left=(imgRect.left+imgRect.width/2-30)+'px';
            clone.style.top=(imgRect.top+imgRect.height/2-30)+'px';
            document.body.appendChild(clone);

            requestAnimationFrame(function(){
                requestAnimationFrame(function(){
                    clone.classList.add('fly-to-cart');
                    clone.style.left=(cartRect.left+cartRect.width/2-15)+'px';
                    clone.style.top=(cartRect.top+cartRect.height/2-15)+'px';
                });
            });
            setTimeout(function(){if(clone.parentNode)clone.remove();},750);
        }

        /* 4. Actually add to cart via AJAX */
        fetch('ajax/cart.php',{
            method:'POST',
            headers:{'Content-Type':'application/x-www-form-urlencoded'},
            body:'action=add&product_id='+productId+'&quantity=1'
        }).then(function(r){return r.json();}).then(function(data){
            if(data.success){
                /* Show success state */
                btn.classList.remove('adding');
                btn.classList.add('added');

                /* Update cart badge */
                if(typeof data.cart_count!=='undefined'){
                    document.querySelectorAll('.cart-badge').forEach(function(badge){
                        var old=parseInt(badge.textContent)||0;
                        badge.textContent=data.cart_count;
                        badge.classList.remove('badge-pop');
                        void badge.offsetWidth;
                        badge.classList.add('badge-pop');
                    });
                }

                /* Cart icon bounce */
                if(cartIcon){
                    var navLink=cartIcon.closest('.nav-link');
                    if(navLink){
                        navLink.classList.remove('cart-pulse');
                        void navLink.offsetWidth;
                        navLink.classList.add('cart-pulse');
                        setTimeout(function(){navLink.classList.remove('cart-pulse');},700);
                    }
                }

                /* Card flash */
                if(card){
                    card.classList.remove('card-flash');
                    void card.offsetWidth;
                    card.classList.add('card-flash');
                    setTimeout(function(){card.classList.remove('card-flash');},500);
                }

                /* Reset after delay */
                setTimeout(function(){
                    btn.classList.remove('added');
                },2000);
            } else {
                /* Error - reset button */
                btn.classList.remove('adding');
                if(data.redirect){window.location.href=data.redirect;}
            }
        }).catch(function(){
            btn.classList.remove('adding');
        });
    });
})();

/* Sale Banner Countdown */
(function(){
    var se=document.getElementById('s-h');if(!se)return;
    const end=new Date();end.setHours(23,59,59,999);
    function st(){
        const d=end-new Date();
        if(d<=0){end.setDate(end.getDate()+1);st();return;}
        document.getElementById('s-h').textContent=String(Math.floor(d/36e5)).padStart(2,'0');
        document.getElementById('s-m').textContent=String(Math.floor(d%36e5/6e4)).padStart(2,'0');
        document.getElementById('s-s').textContent=String(Math.floor(d%6e4/1e3)).padStart(2,'0');
    }
    st();setInterval(st,1000);
})();

/* Scroll Animations */
const mObs=new IntersectionObserver(entries=>{
    entries.forEach(e=>{if(e.isIntersecting){e.target.classList.add('visible');mObs.unobserve(e.target);}});
},{threshold:0.08});
document.querySelectorAll('.m-anim,.m-anim-left').forEach(el=>mObs.observe(el));

/* Horizontal scroll with mouse wheel */
document.querySelectorAll('.m-prod-row').forEach(row=>{
    row.addEventListener('wheel',function(e){
        if(Math.abs(e.deltaX)>Math.abs(e.deltaY)) return;
        e.preventDefault();
        this.scrollLeft+=e.deltaY;
    },{passive:false});
});

/* Category pills active */
document.querySelectorAll('.m-cat-pill').forEach(pill=>{
    pill.addEventListener('click',function(){
        document.querySelectorAll('.m-cat-pill').forEach(p=>p.classList.remove('active'));
        this.classList.add('active');
    });
});

/* Drag to scroll on product rows */
document.querySelectorAll('.m-prod-row').forEach(function(row) {
  var isDown=false,startX,scrollLeft;
  row.addEventListener('mousedown',function(e){isDown=true;row.classList.add('dragging');startX=e.pageX-row.offsetLeft;scrollLeft=row.scrollLeft;});
  row.addEventListener('mouseleave',function(){isDown=false;row.classList.remove('dragging');});
  row.addEventListener('mouseup',function(){isDown=false;row.classList.remove('dragging');});
  row.addEventListener('mousemove',function(e){if(!isDown)return;e.preventDefault();row.scrollLeft=scrollLeft-(e.pageX-row.offsetLeft-startX)*2;});
});

</script>

<!-- ===== FLOATING AI ASSISTANT ===== -->
<style>
.ai-fab{position:fixed;bottom:28px;right:28px;z-index:9999;cursor:pointer;}
.ai-fab-btn{width:60px;height:60px;border-radius:50%;background:linear-gradient(135deg,#6C63FF,#FF6584);border:none;color:#fff;font-size:1.6rem;display:flex;align-items:center;justify-content:center;box-shadow:0 6px 30px rgba(108,99,255,0.4);transition:all 0.4s cubic-bezier(0.23,1,0.32,1);position:relative;overflow:visible;}
.ai-fab-btn:hover{transform:scale(1.1) rotate(-5deg);box-shadow:0 10px 40px rgba(108,99,255,0.5);}
.ai-fab-btn .ai-pulse{position:absolute;inset:-6px;border-radius:50%;border:2px solid rgba(108,99,255,0.3);animation:aiPulse 2s ease-out infinite;}
@keyframes aiPulse{0%{transform:scale(1);opacity:1;}100%{transform:scale(1.6);opacity:0;}}
.ai-fab-btn .ai-robot-icon{position:relative;z-index:2;animation:aiBob 2s ease-in-out infinite;}
@keyframes aiBob{0%,100%{transform:translateY(0);}50%{transform:translateY(-3px);}}
.ai-fab-tooltip{position:absolute;bottom:calc(100% + 12px);right:0;background:rgba(30,30,50,0.95);color:#fff;padding:8px 16px;border-radius:12px;font-size:0.8rem;font-weight:600;white-space:nowrap;opacity:0;transform:translateY(8px);transition:all 0.3s;pointer-events:none;backdrop-filter:blur(10px);}
.ai-fab:hover .ai-fab-tooltip{opacity:1;transform:translateY(0);}
.ai-fab-tooltip::after{content:'';position:absolute;top:100%;right:20px;border:6px solid transparent;border-top-color:rgba(30,30,50,0.95);}

/* Chat Panel */
.ai-chat-panel{position:fixed;bottom:100px;right:28px;width:380px;max-height:550px;border-radius:20px;background:var(--bg-card,#fff);box-shadow:0 20px 60px rgba(0,0,0,0.15),0 0 0 1px rgba(0,0,0,0.06);display:flex;flex-direction:column;overflow:hidden;z-index:9998;transform:scale(0.8) translateY(20px);opacity:0;pointer-events:none;transition:all 0.4s cubic-bezier(0.23,1,0.32,1);transform-origin:bottom right;}
.ai-chat-panel.open{transform:scale(1) translateY(0);opacity:1;pointer-events:auto;}
.ai-chat-header{background:linear-gradient(135deg,#6C63FF,#FF6584);padding:16px 20px;display:flex;align-items:center;gap:12px;color:#fff;}
.ai-chat-header .ai-avatar{width:38px;height:38px;border-radius:50%;background:rgba(255,255,255,0.2);display:flex;align-items:center;justify-content:center;font-size:1.2rem;backdrop-filter:blur(10px);}
.ai-chat-header .ai-info h4{font-size:0.9rem;font-weight:700;margin:0;}
.ai-chat-header .ai-info p{font-size:0.7rem;opacity:0.8;margin:0;}
.ai-chat-header .ai-close{margin-left:auto;background:none;border:none;color:#fff;font-size:1.2rem;cursor:pointer;padding:4px 8px;border-radius:8px;transition:background 0.2s;}
.ai-chat-header .ai-close:hover{background:rgba(255,255,255,0.2);}
.ai-chat-messages{flex:1;overflow-y:auto;padding:16px;display:flex;flex-direction:column;gap:12px;min-height:300px;max-height:350px;scroll-behavior:smooth;}
.ai-chat-messages::-webkit-scrollbar{width:4px;}
.ai-chat-messages::-webkit-scrollbar-thumb{background:rgba(108,99,255,0.3);border-radius:2px;}
.ai-msg{max-width:85%;padding:10px 14px;border-radius:16px;font-size:0.85rem;line-height:1.5;animation:aiMsgIn 0.3s ease-out;}
@keyframes aiMsgIn{from{opacity:0;transform:translateY(8px);}to{opacity:1;transform:translateY(0);}}
.ai-msg.bot{background:var(--bg-glass,rgba(108,99,255,0.08));color:var(--text-primary,#333);border-bottom-left-radius:4px;align-self:flex-start;border:1px solid rgba(108,99,255,0.1);}
.ai-msg.user{background:linear-gradient(135deg,#6C63FF,#FF6584);color:#fff;border-bottom-right-radius:4px;align-self:flex-end;}
.ai-msg.bot a{color:#6C63FF;font-weight:600;}
.ai-msg-bot-product{display:flex;align-items:center;gap:10px;padding:8px;background:var(--bg-card,rgba(255,255,255,0.8));border-radius:12px;margin-top:8px;border:1px solid var(--border-glass,rgba(0,0,0,0.06));transition:all 0.2s;cursor:pointer;text-decoration:none;color:inherit;}
.ai-msg-bot-product:hover{box-shadow:0 4px 12px rgba(0,0,0,0.08);transform:translateY(-1px);}
.ai-msg-bot-product img{width:40px;height:40px;border-radius:8px;object-fit:contain;}
.ai-msg-bot-product .ai-pname{font-size:0.75rem;font-weight:600;color:var(--text-primary,#333);}
.ai-msg-bot-product .ai-pprice{font-size:0.7rem;color:#6C63FF;font-weight:700;}
.ai-typing{display:flex;gap:4px;padding:10px 14px;background:var(--bg-glass,rgba(108,99,255,0.08));border-radius:16px;border-bottom-left-radius:4px;align-self:flex-start;width:fit-content;}
.ai-typing span{width:6px;height:6px;border-radius:50%;background:#6C63FF;animation:aiTypeBounce 1.2s ease-in-out infinite;}
.ai-typing span:nth-child(2){animation-delay:0.2s;}
.ai-typing span:nth-child(3){animation-delay:0.4s;}
@keyframes aiTypeBounce{0%,60%,100%{transform:translateY(0);}30%{transform:translateY(-6px);}}
.ai-chat-input{display:flex;gap:8px;padding:12px 16px;border-top:1px solid var(--border-glass,rgba(0,0,0,0.06));background:var(--bg-card,#fff);}
.ai-chat-input input{flex:1;border:1px solid var(--border-glass,rgba(0,0,0,0.1));border-radius:12px;padding:10px 14px;font-size:0.85rem;background:var(--bg-input,rgba(0,0,0,0.03));color:var(--text-primary,#333);outline:none;transition:border 0.2s;}
.ai-chat-input input:focus{border-color:#6C63FF;}
.ai-chat-input button{width:40px;height:40px;border-radius:12px;background:linear-gradient(135deg,#6C63FF,#FF6584);border:none;color:#fff;font-size:1rem;cursor:pointer;display:flex;align-items:center;justify-content:center;transition:all 0.2s;}
.ai-chat-input button:hover{transform:scale(1.05);}
.ai-chat-suggestions{display:flex;flex-wrap:wrap;gap:6px;padding:0 16px 12px;}
.ai-sug-btn{padding:5px 12px;border-radius:20px;font-size:0.7rem;font-weight:600;background:var(--bg-glass,rgba(108,99,255,0.08));color:#6C63FF;border:1px solid rgba(108,99,255,0.15);cursor:pointer;transition:all 0.2s;}
.ai-sug-btn:hover{background:#6C63FF;color:#fff;}
@media(max-width:480px){
  .ai-chat-panel{right:10px;left:10px;width:auto;bottom:90px;max-height:70vh;}
  .ai-fab{bottom:16px;right:16px;}
}
</style>

<div class="ai-fab" id="aiFab">
  <div class="ai-fab-tooltip">Ask AI Assistant</div>
  <button class="ai-fab-btn" id="aiFabBtn">
    <span class="ai-pulse"></span>
    <span class="ai-robot-icon"><i class="fas fa-robot"></i></span>
  </button>
</div>

<div class="ai-chat-panel" id="aiChatPanel">
  <div class="ai-chat-header">
    <div class="ai-avatar"><i class="fas fa-robot"></i></div>
    <div class="ai-info">
      <h4>ShopSphere AI</h4>
      <p>Always here to help</p>
    </div>
    <button class="ai-close" id="aiChatClose"><i class="fas fa-times"></i></button>
  </div>
  <div class="ai-chat-messages" id="aiChatMessages">
    <div class="ai-msg bot">Hi there! I'm your AI shopping assistant. Ask me anything about products, deals, or recommendations!</div>
  </div>
  <div class="ai-chat-suggestions" id="aiChatSuggestions">
    <button class="ai-sug-btn" data-q="Best deals today">Best deals</button>
    <button class="ai-sug-btn" data-q="Recommend phones under 20000">Phones under 20k</button>
    <button class="ai-sug-btn" data-q="Show trending products">Trending</button>
    <button class="ai-sug-btn" data-q="Help">What can you do?</button>
  </div>
  <div class="ai-chat-input">
    <input type="text" id="aiChatInput" placeholder="Ask about products..." autocomplete="off">
    <button id="aiChatSend"><i class="fas fa-paper-plane"></i></button>
  </div>
</div>

<script>
(function(){
  var fab=document.getElementById('aiFab');
  var panel=document.getElementById('aiChatPanel');
  var closeBtn=document.getElementById('aiChatClose');
  var fabBtn=document.getElementById('aiFabBtn');
  var messages=document.getElementById('aiChatMessages');
  var input=document.getElementById('aiChatInput');
  var sendBtn=document.getElementById('aiChatSend');
  var suggestions=document.getElementById('aiChatSuggestions');
  var isOpen=false;
  var history=[];

  function toggleChat(){
    isOpen=!isOpen;
    panel.classList.toggle('open',isOpen);
    if(isOpen){setTimeout(function(){input.focus();},400);}
  }
  fabBtn.addEventListener('click',toggleChat);
  closeBtn.addEventListener('click',function(){isOpen=false;panel.classList.remove('open');});

  function addMsg(text,isUser,products){
    var div=document.createElement('div');
    div.className='ai-msg '+(isUser?'user':'bot');
    div.textContent=text;
    messages.appendChild(div);
    if(products&&products.length){
      products.forEach(function(p){
        var prodLink=document.createElement('a');
        prodLink.className='ai-msg-bot-product';
        prodLink.href='<?= BASE_URL ?>product.php?slug='+encodeURIComponent(p.slug||'');
        prodLink.target='_blank';
        var imgSrc=p.primary_image?'assets/uploads/products/'+p.primary_image:'assets/uploads/products/placeholder.jpg';
        if(!imgSrc.startsWith('http'))imgSrc='<?= BASE_URL ?>'+imgSrc;
        prodLink.innerHTML='<img src="'+imgSrc+'" alt=""><div><div class="ai-pname">'+escapeHtml(p.name||'Product')+'</div><div class="ai-pprice">₹'+Number(p.sale_price||p.price||0).toLocaleString('en-IN')+'</div></div>';
        messages.appendChild(prodLink);
      });
    }
    messages.scrollTop=messages.scrollHeight;
  }

  function showTyping(){
    var t=document.createElement('div');
    t.className='ai-typing';t.id='aiTyping';
    t.innerHTML='<span></span><span></span><span></span>';
    messages.appendChild(t);
    messages.scrollTop=messages.scrollHeight;
  }
  function hideTyping(){var t=document.getElementById('aiTyping');if(t)t.remove();}

  function escapeHtml(s){var d=document.createElement('div');d.textContent=s;return d.innerHTML;}

  function sendMessage(msg){
    if(!msg||!msg.trim())return;
    var userMsg=msg.trim();
    addMsg(userMsg,true);
    history.push({role:'user',content:userMsg});
    input.value='';
    suggestions.style.display='none';
    showTyping();

    fetch('<?= BASE_URL ?>api/ai_chat.php',{
      method:'POST',
      headers:{'Content-Type':'application/json'},
      body:JSON.stringify({message:userMsg,history:history.slice(-10)})
    })
    .then(function(r){return r.json();})
    .then(function(data){
      hideTyping();
      if(data.success){
        addMsg(data.response||'I found something for you!',false,data.products||[]);
        history.push({role:'assistant',content:data.response});
      }else{
        addMsg('Sorry, something went wrong. Please try again.',false);
      }
    })
    .catch(function(){
      hideTyping();
      addMsg('Connection error. Please try again.',false);
    });
  }

  sendBtn.addEventListener('click',function(){sendMessage(input.value);});
  input.addEventListener('keydown',function(e){if(e.key==='Enter')sendMessage(input.value);});
  suggestions.addEventListener('click',function(e){
    var btn=e.target.closest('.ai-sug-btn');
    if(btn){sendMessage(btn.dataset.q);}
  });
})();
</script>

<?php include 'includes/footer.php'; ?>
