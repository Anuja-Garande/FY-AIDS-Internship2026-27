<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>NeoFinance | Personal Expense Tracker</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,400..900&family=Manrope:wght@400;500;600;700;800&family=IBM+Plex+Mono:wght@400;500;600&display=swap" rel="stylesheet">

<style>
/* =========================================================
   NEOFINANCE — "ENGRAVED LEDGER" DESIGN SYSTEM
   Palette: deep banknote ink-green + foil gold + fresh mint
   Type: Fraunces (display) / Manrope (body) / IBM Plex Mono (data)
========================================================= */
:root{
    --ink:#0E2A1C;
    --ink-2:#153724;
    --ink-3:#1C4530;
    --paper:#F3EEDD;
    --paper-2:#EAE2C6;
    --gold:#C6A15B;
    --gold-bright:#EAD08E;
    --mint:#3FCB92;
    --rust:#D97A5F;
    --text:#ECE6D6;
    --muted:#9FB6A6;
    --line: rgba(198,161,91,.28);
}

*{box-sizing:border-box;}

body{
    background:var(--ink);
    color:var(--text);
    font-family:'Manrope',sans-serif;
    overflow-x:hidden;
}

h1,h2,h3,h4,h5{
    font-family:'Fraunces',serif;
    font-weight:700;
    letter-spacing:-.01em;
}

.mono{
    font-family:'IBM Plex Mono',monospace;
}

.eyebrow{
    font-family:'IBM Plex Mono',monospace;
    font-size:12px;
    letter-spacing:.22em;
    text-transform:uppercase;
    color:var(--gold);
    display:flex;
    align-items:center;
    gap:10px;
}
.eyebrow::before{
    content:"";
    width:22px;height:1px;
    background:var(--gold);
    display:inline-block;
}

a{text-decoration:none;}

/* ---------- background engraving texture ---------- */
.engrave-bg{
    position:relative;
}
.engrave-bg::after{
    content:"";
    position:absolute; inset:0;
    background-image:repeating-linear-gradient(115deg, rgba(198,161,91,.05) 0px, rgba(198,161,91,.05) 1px, transparent 1px, transparent 7px);
    pointer-events:none;
}

/* ---------- NAVBAR ---------- */
.navbar{
    background:rgba(14,42,28,.7);
    backdrop-filter:blur(16px);
    border-bottom:1px solid var(--line);
    padding-top:14px;
    padding-bottom:14px;
    transition:background .3s, padding .3s;
}
.navbar.scrolled{
    background:rgba(14,42,28,.94);
    padding-top:8px;
    padding-bottom:8px;
}
.navbar-brand{
    font-family:'Fraunces',serif;
    font-size:26px;
    font-weight:800;
    color:var(--gold-bright) !important;
    display:flex;align-items:center;gap:8px;
}
.navbar-brand .coin{
    display:inline-block;
    animation:coinspin 5s linear infinite;
}
@keyframes coinspin{
    0%,80%{transform:rotateY(0deg) scaleX(1);}
    90%{transform:rotateY(0deg) scaleX(.15);}
    100%{transform:rotateY(0deg) scaleX(1);}
}
.nav-link{
    color:var(--text) !important;
    margin-left:24px;
    font-size:14px;
    font-weight:600;
    position:relative;
}
.nav-link::after{
    content:"";
    position:absolute; left:0; bottom:-4px;
    width:0; height:1px;
    background:var(--gold);
    transition:width .25s;
}
.nav-link:hover{color:var(--gold-bright) !important;}
.nav-link:hover::after{width:100%;}
.nav-cta{
    margin-left:24px;
    padding:9px 22px;
    border-radius:30px;
    background:var(--gold);
    color:var(--ink) !important;
    font-weight:700;
    font-size:14px;
}
.nav-cta:hover{background:var(--gold-bright);}

/* ---------- HERO ---------- */
.hero{
    min-height:100vh;
    display:flex;
    align-items:center;
    padding-top:110px;
    padding-bottom:60px;
    position:relative;
    background:
        radial-gradient(ellipse 60% 50% at 85% 15%, rgba(63,203,146,.14), transparent 60%),
        radial-gradient(ellipse 55% 45% at 5% 90%, rgba(198,161,91,.14), transparent 60%),
        linear-gradient(180deg, var(--ink) 0%, var(--ink-2) 100%);
}
.hero h1{
    font-size:56px;
    line-height:1.08;
    color:var(--paper);
    margin:18px 0 20px;
}
.hero h1 em{
    font-style:italic;
    color:var(--gold-bright);
}
.hero p.lead-text{
    font-size:18px;
    color:var(--muted);
    max-width:480px;
    line-height:1.6;
}
.hero-btn{
    padding:14px 32px;
    border-radius:40px;
    font-weight:700;
    margin-right:12px;
    font-size:15px;
    display:inline-flex;
    align-items:center;
    gap:8px;
}
.btn-gold{
    background:linear-gradient(135deg, var(--gold-bright), var(--gold));
    color:var(--ink);
    border:none;
    position:relative;
    overflow:hidden;
}
.btn-gold::before{
    content:"";
    position:absolute; top:0; left:-60%;
    width:40%; height:100%;
    background:linear-gradient(120deg, transparent, rgba(255,255,255,.55), transparent);
    transform:skewX(-20deg);
    transition:left .6s;
}
.btn-gold:hover::before{left:130%;}
.btn-gold:hover{color:var(--ink);}
.btn-outline-paper{
    border:1px solid var(--line);
    color:var(--paper);
}
.btn-outline-paper:hover{
    border-color:var(--gold);
    color:var(--gold-bright);
    background:rgba(198,161,91,.08);
}

/* serial / trust strip */
.serial-strip{
    margin-top:38px;
    padding-top:18px;
    border-top:1px dashed var(--line);
    display:flex;
    flex-wrap:wrap;
    gap:14px 26px;
    font-size:12px;
    color:var(--muted);
}
.serial-strip span{display:flex;align-items:center;gap:8px;}
.serial-strip strong{color:var(--gold-bright);font-family:'IBM Plex Mono',monospace;}

/* ---------- NOTE CARD (signature element) ---------- */
.note-stage{
    position:relative;
    display:flex;
    align-items:center;
    justify-content:center;
    min-height:420px;
}
.note-photo-back{
    position:absolute;
    width:230px; height:150px;
    right:0; top:18px;
    border-radius:10px;
    object-fit:cover;
    opacity:.55;
    transform:rotate(9deg);
    box-shadow:0 20px 50px rgba(0,0,0,.5);
    filter:sepia(.25) saturate(1.1);
}
.note-card{
    position:relative;
    width:100%;
    max-width:420px;
    background:linear-gradient(160deg, var(--ink-3), var(--ink-2));
    border:1px solid var(--line);
    border-radius:18px;
    padding:26px 26px 22px;
    animation:noteFloat 5s ease-in-out infinite;
    box-shadow:0 30px 70px rgba(0,0,0,.55);
}
.note-card::before{
    content:"";
    position:absolute; inset:8px;
    border:1px solid rgba(198,161,91,.35);
    border-radius:12px;
    pointer-events:none;
}
@keyframes noteFloat{
    0%,100%{transform:translateY(0) rotate(-1.5deg);}
    50%{transform:translateY(-14px) rotate(0.5deg);}
}
.note-top{
    display:flex;
    justify-content:space-between;
    align-items:center;
    font-family:'IBM Plex Mono',monospace;
    font-size:11px;
    letter-spacing:.15em;
    color:var(--gold-bright);
    text-transform:uppercase;
}
.note-rosette{
    position:absolute;
    right:18px; top:60px;
    width:120px; height:120px;
    opacity:.5;
}
.note-balance{
    margin-top:38px;
}
.note-balance .label{
    font-family:'IBM Plex Mono',monospace;
    font-size:11px;
    letter-spacing:.15em;
    color:var(--muted);
    text-transform:uppercase;
    margin-bottom:6px;
}
.note-balance h2{
    color:var(--paper);
    font-size:42px;
    margin:0;
}
.note-bottom{
    margin-top:34px;
    padding-top:14px;
    border-top:1px dashed var(--line);
    display:flex;
    justify-content:space-between;
    font-size:10.5px;
    letter-spacing:.1em;
    color:var(--muted);
    text-transform:uppercase;
}
.note-badge{
    position:absolute;
    left:-16px; bottom:-16px;
    width:64px;height:64px;
    background:var(--gold);
    border-radius:50%;
    display:flex;align-items:center;justify-content:center;
    font-size:26px;
    box-shadow:0 12px 30px rgba(0,0,0,.4);
    animation:coinPulse 2.6s ease-in-out infinite;
}
@keyframes coinPulse{
    0%,100%{transform:scale(1);}
    50%{transform:scale(1.08);}
}

/* ---------- SECTIONS ---------- */
section{position:relative;}
.section-alt{background:var(--ink-2);}
.section-pad{padding:96px 0;}
.section-head{max-width:640px;margin:0 auto 56px;text-align:center;}
.section-head h2{font-size:38px;color:var(--paper);margin-top:14px;}
.section-head p{color:var(--muted);font-size:16px;margin-top:12px;}
.divider-medallion{
    width:34px;height:34px;
    margin:36px auto;
    opacity:.6;
}

/* ---------- FEATURE CARDS ---------- */
.feat-card{
    background:var(--ink-3);
    border:1px solid var(--line);
    border-radius:16px;
    padding:32px 26px;
    height:100%;
    transition:transform .3s, box-shadow .3s, border-color .3s;
}
.feat-card:hover{
    transform:translateY(-6px);
    border-color:var(--gold);
    box-shadow:0 20px 40px rgba(0,0,0,.35);
}
.feat-badge{
    width:56px;height:56px;
    border-radius:50%;
    background:var(--ink-2);
    border:1px solid var(--gold);
    display:flex;align-items:center;justify-content:center;
    font-size:26px;
    margin-bottom:18px;
}
.feat-card h4{color:var(--paper);font-size:19px;margin-bottom:10px;}
.feat-card p{color:var(--muted);font-size:14.5px;line-height:1.6;margin:0;}

.reveal{
    opacity:0;
    transform:translateY(26px);
    transition:opacity .6s ease, transform .6s ease;
}
.reveal.in{opacity:1;transform:translateY(0);}

/* ---------- ABOUT ---------- */
.about-frame{
    position:relative;
    border-radius:20px;
    overflow:hidden;
    border:1px solid var(--line);
}
.about-frame img{
    width:100%;height:100%;
    object-fit:cover;
    display:block;
    filter:saturate(1.05);
}
.about-frame::after{
    content:"";
    position:absolute; inset:0;
    background:linear-gradient(180deg, rgba(14,42,28,0) 40%, rgba(14,42,28,.85));
}
.about-frame .caption{
    position:absolute; left:20px; bottom:18px;
    z-index:2;
    font-family:'IBM Plex Mono',monospace;
    font-size:11px;
    letter-spacing:.15em;
    color:var(--gold-bright);
    text-transform:uppercase;
}

/* ---------- DASHBOARD PREVIEW (light "paper" card in the vault) ---------- */
.list-ledger{list-style:none;padding:0;margin:0;}
.list-ledger li{
    display:flex;align-items:center;gap:12px;
    padding:14px 16px;
    background:var(--ink-3);
    border:1px solid var(--line);
    border-radius:10px;
    margin-bottom:10px;
    color:var(--text);
    font-weight:600;
    font-size:14.5px;
}
.paper-card{
    background:var(--paper);
    color:var(--ink);
    border-radius:18px;
    padding:32px;
    box-shadow:0 30px 70px rgba(0,0,0,.4);
}
.paper-card h4{color:var(--ink);}
.paper-card .progress{height:12px;background:rgba(14,42,28,.1);border-radius:20px;}
.paper-card hr{border-color:rgba(14,42,28,.15);}
.paper-card .wallet-amt{color:#1B7A4B;font-size:34px;}
.paper-card .updated{color:#6b7160;font-size:13px;}

/* ---------- APP FEATURES ---------- */
.pill-card{
    background:var(--ink-3);
    border:1px solid var(--line);
    border-radius:14px;
    padding:26px 16px;
    text-align:center;
    transition:transform .25s,border-color .25s;
}
.pill-card:hover{transform:translateY(-4px);border-color:var(--mint);}
.pill-card .emo{font-size:30px;margin-bottom:8px;display:block;}
.pill-card h5{color:var(--paper);font-size:15px;margin:0;}

/* ---------- TESTIMONIALS ---------- */
.testi-card{
    background:var(--ink-3);
    border:1px solid var(--line);
    border-radius:16px;
    padding:30px;
    height:100%;
}
.testi-card .stars{color:var(--gold);letter-spacing:3px;font-size:15px;}
.testi-card p{color:var(--text);font-style:italic;font-size:15px;margin:16px 0;}
.testi-card h5{color:var(--gold-bright);font-size:15px;margin:0;}
.testi-card .role{color:var(--muted);font-size:12.5px;}

/* ---------- FAQ ---------- */
.accordion-item{
    background:var(--ink-3) !important;
    border:1px solid var(--line) !important;
    border-radius:12px !important;
    overflow:hidden;
    margin-bottom:12px;
}
.accordion-button{
    background:var(--ink-3) !important;
    color:var(--paper) !important;
    font-weight:700;
    font-family:'Fraunces',serif;
}
.accordion-button:focus{box-shadow:none;}
.accordion-button::after{filter:invert(72%) sepia(30%) saturate(500%) hue-rotate(1deg);}
.accordion-body{color:var(--muted);}

/* ---------- CONTACT ---------- */
.contact-card{
    background:var(--ink-3);
    border:1px solid var(--line);
    border-radius:16px;
    padding:30px;
}
.contact-card input,.contact-card textarea{
    background:var(--ink-2);
    border:1px solid var(--line);
    color:var(--text);
}
.contact-card input:focus,.contact-card textarea:focus{
    background:var(--ink-2);
    border-color:var(--gold);
    color:var(--text);
    box-shadow:0 0 0 .2rem rgba(198,161,91,.15);
}
.contact-card input::placeholder,.contact-card textarea::placeholder{color:var(--muted);}

/* ---------- FOOTER ---------- */
footer{background:#081910;border-top:1px solid var(--line);padding:50px 0 30px;}
footer h3{color:var(--gold-bright);}
footer p{color:var(--muted);}
.social-ico{
    color:var(--gold);
    font-size:20px;
    width:42px;height:42px;
    border:1px solid var(--line);
    border-radius:50%;
    display:inline-flex;align-items:center;justify-content:center;
    margin:0 5px;
    transition:background .25s,color .25s;
}
.social-ico:hover{background:var(--gold);color:var(--ink);}

/* ---------- RESPONSIVE ---------- */
@media (max-width:991px){
    .hero h1{font-size:40px;}
    .hero{padding-top:130px;}
    .note-photo-back{display:none;}
    .section-pad{padding:64px 0;}
}
@media (max-width:576px){
    .hero h1{font-size:32px;}
    .note-balance h2{font-size:32px;}
}
</style>
</head>

<body>

<!-- NAVBAR -->
<nav class="navbar navbar-expand-lg fixed-top" id="mainNav">
<div class="container">

<a class="navbar-brand" href="#home"><span class="coin">🪙</span> NeoFinance</a>

<button class="navbar-toggler bg-light" data-bs-toggle="collapse" data-bs-target="#menu">
<span class="navbar-toggler-icon"></span>
</button>

<div class="collapse navbar-collapse" id="menu">
<ul class="navbar-nav ms-auto align-items-lg-center">
<li class="nav-item"><a class="nav-link" href="#home">Home</a></li>
<li class="nav-item"><a class="nav-link" href="#features">Features</a></li>
<li class="nav-item"><a class="nav-link" href="#about">About</a></li>
<li class="nav-item"><a class="nav-link" href="#contact">Contact</a></li>
<li class="nav-item"><a class="nav-link" href="authentication/login.php">Login</a></li>
<li class="nav-item"><a class="nav-cta" href="authentication/register.php">Get Started</a></li>
</ul>
</div>

</div>
</nav>

<!-- HERO -->
<section id="home" class="hero engrave-bg">
<div class="container" style="position:relative;z-index:1;">
<div class="row align-items-center g-5">

<div class="col-lg-6">
<div class="eyebrow">₹ NeoFinance · Series 2026</div>

<h1>
Track every rupee<br>
like it's <em>engraved in gold</em>.
</h1>

<p class="lead-text">
NeoFinance turns everyday spending into a clear, considered ledger —
income, expenses, budgets and savings goals, all held in one calm place.
</p>

<div class="mt-4">
<a href="authentication/login.php" class="btn btn-gold hero-btn">
<i class="fa-solid fa-right-to-bracket"></i> Login
</a>
<a href="authentication/register.php" class="btn btn-outline-paper hero-btn">
<i class="fa-solid fa-user-plus"></i> Register
</a>
</div>

<div class="serial-strip mono">
<span>👥 <strong>10,000+</strong> households</span>
<span>💰 <strong>₹25 Cr+</strong> tracked</span>
<span>🔒 <strong>99%</strong> uptime</span>
</div>
</div>

<div class="col-lg-6">
<div class="note-stage">

<img class="note-photo-back" src="https://images.unsplash.com/photo-1565514160046-79d8ca6510b6?q=80&w=700&auto=format&fit=crop" alt="Indian rupee note texture">

<div class="note-card">
<div class="note-top">
<span>NeoFinance</span>
<span>₹ Reserve Ledger</span>
</div>

<svg class="note-rosette" viewBox="0 0 120 120" fill="none">
<circle cx="60" cy="60" r="55" stroke="#EAD08E" stroke-width="1" stroke-dasharray="2 4"/>
<circle cx="60" cy="60" r="42" stroke="#EAD08E" stroke-width="1"/>
<circle cx="60" cy="60" r="30" stroke="#EAD08E" stroke-width="1" stroke-dasharray="1 3"/>
<circle cx="60" cy="60" r="6" fill="#EAD08E"/>
</svg>

<div class="note-balance">
<p class="label">Wallet Balance</p>
<h2>₹1,25,000</h2>
</div>

<div class="note-bottom mono">
<span>NF · 2026 · CX 004821</span>
<span>Pay Any Amount 🤝</span>
</div>

<div class="note-badge">🪙</div>
</div>

</div>
</div>

</div>
</div>
</section>

<!-- ================= FEATURES ================= -->
<section id="features" class="section-pad">
<div class="container">

<div class="section-head">
<div class="eyebrow" style="justify-content:center;">Why NeoFinance</div>
<h2>Every feature, considered.</h2>
<p>Manage every rupee with smart insights and modern technology, wrapped in an interface that feels like your own private ledger.</p>
</div>

<div class="row g-4">

<div class="col-lg-4 col-md-6 reveal">
<div class="feat-card">
<div class="feat-badge">💸</div>
<h4>Expense Tracking</h4>
<p>Track every expense with categories, notes and instant analytics — nothing slips through unnoticed.</p>
</div>
</div>

<div class="col-lg-4 col-md-6 reveal">
<div class="feat-card">
<div class="feat-badge">📈</div>
<h4>Income Management</h4>
<p>Monitor salary, business income and freelance earnings, all reconciled in one place.</p>
</div>
</div>

<div class="col-lg-4 col-md-6 reveal">
<div class="feat-card">
<div class="feat-badge">🎯</div>
<h4>Savings Goals</h4>
<p>Set targets, add funds as you go, and watch each goal's progress bar fill in real time.</p>
</div>
</div>

<div class="col-lg-4 col-md-6 reveal">
<div class="feat-card">
<div class="feat-badge">📊</div>
<h4>Reports</h4>
<p>Daily, monthly and yearly reports with clean, exportable charts — built for clarity, not clutter.</p>
</div>
</div>

<div class="col-lg-4 col-md-6 reveal">
<div class="feat-card">
<div class="feat-badge">🔔</div>
<h4>Smart Alerts</h4>
<p>Budget reminders, bill alerts and spending notifications that reach you before it's too late.</p>
</div>
</div>

<div class="col-lg-4 col-md-6 reveal">
<div class="feat-card">
<div class="feat-badge">🤖</div>
<h4>AI Insights</h4>
<p>A rule-based intelligence engine that flags unusual spends and forecasts next month's outgoings.</p>
</div>
</div>

</div>
</div>
</section>

<!-- ================= ABOUT ================= -->
<section id="about" class="section-pad section-alt engrave-bg">
<div class="container" style="position:relative;z-index:1;">
<div class="row align-items-center g-5">

<div class="col-lg-6 reveal">
<div class="eyebrow">About the vault</div>
<h2 style="font-size:34px;color:var(--paper);margin-top:14px;">A modern ledger, built for real households.</h2>
<p style="color:var(--muted);font-size:16px;line-height:1.7;margin-top:16px;">
NeoFinance is a personal expense tracker that helps you manage income, expenses,
budgets, savings and financial goals through a secure, considered interface —
every rupee accounted for, nothing overwhelming.
</p>
</div>

<div class="col-lg-6 reveal">
<div class="about-frame" style="height:340px;">
<img src="https://images.unsplash.com/photo-1643393668532-4946bad40472?q=80&w=1200&auto=format&fit=crop" alt="Gold and silver coins">
<div class="caption">🪙 Every rupee, accounted for</div>
</div>
</div>

</div>
</div>
</section>

<!-- ================= DASHBOARD PREVIEW ================= -->
<section class="section-pad">
<div class="container">
<div class="row align-items-center g-5">

<div class="col-lg-6 reveal">
<div class="eyebrow">Your command center</div>
<h2 style="font-size:34px;color:var(--paper);margin-top:14px;">Your Financial Dashboard</h2>
<p style="color:var(--muted);font-size:16px;margin:14px 0 24px;">
Monitor income, expenses, savings and budgets from one powerful dashboard.
</p>

<ul class="list-ledger">
<li>💰 Wallet Balance</li>
<li>📉 Monthly Expenses</li>
<li>📈 Monthly Income</li>
<li>🎯 Savings Progress</li>
<li>📊 Analytics Charts</li>
</ul>
</div>

<div class="col-lg-6 reveal">
<div class="paper-card">
<h4 class="mb-4">Dashboard Preview</h4>

<div class="progress mb-3">
<div class="progress-bar" style="width:70%;background:#1B7A4B;">Savings 70%</div>
</div>
<div class="progress mb-3">
<div class="progress-bar" style="width:45%;background:#C6A15B;">Expenses 45%</div>
</div>
<div class="progress mb-3">
<div class="progress-bar" style="width:85%;background:#3FCB92;">Income 85%</div>
</div>

<hr>
<h5 class="mb-1" style="color:#6b7160;font-size:14px;">Current Wallet</h5>
<h2 class="wallet-amt">₹1,25,000</h2>
<p class="updated">Updated Today</p>
</div>
</div>

</div>
</div>
</section>

<!-- ================= APP FEATURES ================= -->
<section id="contact-preview" class="section-pad section-alt">
<div class="container">
<div class="section-head">
<div class="eyebrow" style="justify-content:center;">The full picture</div>
<h2>Everything You Need</h2>
<p>One application for complete financial management.</p>
</div>

<div class="row g-4">
<div class="col-md-3 col-6 reveal">
<div class="pill-card"><span class="emo">💳</span><h5>Wallet</h5></div>
</div>
<div class="col-md-3 col-6 reveal">
<div class="pill-card"><span class="emo">🏦</span><h5>Budgets</h5></div>
</div>
<div class="col-md-3 col-6 reveal">
<div class="pill-card"><span class="emo">📅</span><h5>Transactions</h5></div>
</div>
<div class="col-md-3 col-6 reveal">
<div class="pill-card"><span class="emo">🛡️</span><h5>Secure Login</h5></div>
</div>
</div>
</div>
</section>

<!-- ===================== TESTIMONIALS ===================== -->
<section class="section-pad">
<div class="container">
<div class="section-head">
<div class="eyebrow" style="justify-content:center;">Trusted by families</div>
<h2>What Our Users Say</h2>
<p>Trusted by students, professionals and families.</p>
</div>

<div class="row g-4">
<div class="col-md-4 reveal">
<div class="testi-card">
<div class="stars">★★★★★</div>
<p>"This app completely changed the way I manage my monthly expenses."</p>
<h5>Rahul Sharma</h5>
<div class="role">Software Engineer, Pune</div>
</div>
</div>

<div class="col-md-4 reveal">
<div class="testi-card">
<div class="stars">★★★★★</div>
<p>"Beautiful interface and very easy to use."</p>
<h5>Priya Patel</h5>
<div class="role">Freelance Designer</div>
</div>
</div>

<div class="col-md-4 reveal">
<div class="testi-card">
<div class="stars">★★★★★</div>
<p>"I finally started saving money thanks to NeoFinance."</p>
<h5>Aditya Mehta</h5>
<div class="role">College Student</div>
</div>
</div>
</div>
</div>
</section>

<!-- ===================== FAQ ===================== -->
<section class="section-pad section-alt">
<div class="container">
<div class="section-head" style="margin-bottom:40px;">
<div class="eyebrow" style="justify-content:center;">Good to know</div>
<h2>Frequently Asked Questions</h2>
</div>

<div class="accordion" id="faq" style="max-width:760px;margin:0 auto;">

<div class="accordion-item">
<h2 class="accordion-header">
<button class="accordion-button" data-bs-toggle="collapse" data-bs-target="#q1">
Is NeoFinance free?
</button>
</h2>
<div id="q1" class="accordion-collapse collapse show" data-bs-parent="#faq">
<div class="accordion-body">Yes. The basic version is completely free.</div>
</div>
</div>

<div class="accordion-item">
<h2 class="accordion-header">
<button class="accordion-button collapsed" data-bs-toggle="collapse" data-bs-target="#q2">
Is my data secure?
</button>
</h2>
<div id="q2" class="accordion-collapse collapse" data-bs-parent="#faq">
<div class="accordion-body">Your passwords are encrypted and your financial information is securely stored.</div>
</div>
</div>

<div class="accordion-item">
<h2 class="accordion-header">
<button class="accordion-button collapsed" data-bs-toggle="collapse" data-bs-target="#q3">
Can I export reports?
</button>
</h2>
<div id="q3" class="accordion-collapse collapse" data-bs-parent="#faq">
<div class="accordion-body">Yes. Reports can be exported to PDF, Excel and CSV.</div>
</div>
</div>

</div>
</div>
</section>

<!-- ===================== CONTACT ===================== -->
<section id="contact" class="section-pad">
<div class="container">
<div class="row g-5">

<div class="col-lg-5 reveal">
<div class="eyebrow">Reach out</div>
<h2 style="font-size:32px;color:var(--paper);margin-top:14px;">Contact Us</h2>
<p style="color:var(--muted);margin-top:14px;line-height:1.8;">
👤 Developer: <strong style="color:var(--paper);">Nidhish Patil</strong><br>
📱 Mobile: <strong style="color:var(--paper);">9579916788</strong><br>
✉️ Email: <strong style="color:var(--paper);">nidhishmpatil078@gmail.com</strong>
</p>
</div>

<div class="col-lg-7 reveal">
<div class="contact-card">
<form>
<div class="mb-3">
<input class="form-control" placeholder="Your Name">
</div>
<div class="mb-3">
<input class="form-control" placeholder="Email">
</div>
<div class="mb-3">
<textarea class="form-control" rows="5" placeholder="Message"></textarea>
</div>
<button class="btn btn-gold hero-btn" type="submit" style="margin-right:0;">Send Message ✉️</button>
</form>
</div>
</div>

</div>
</div>
</section>

<!-- ===================== FOOTER ===================== -->
<footer>
<div class="container text-center">
<h3>🪙 NeoFinance</h3>
<p>Modern Personal Expense Tracker</p>

<div class="mb-3 mt-3">
<a href="#" class="social-ico"><i class="fab fa-facebook-f"></i></a>
<a href="#" class="social-ico"><i class="fab fa-instagram"></i></a>
<a href="#" class="social-ico"><i class="fab fa-linkedin-in"></i></a>
<a href="#" class="social-ico"><i class="fab fa-github"></i></a>
</div>

<hr style="border-color:var(--line);max-width:400px;margin:24px auto;">
<p style="font-size:13px;">© 2026 NeoFinance | All Rights Reserved</p>
</div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Navbar shrink on scroll
window.addEventListener('scroll', function(){
    document.getElementById('mainNav').classList.toggle('scrolled', window.scrollY > 40);
});

// Scroll-reveal for cards
const revealEls = document.querySelectorAll('.reveal');
const io = new IntersectionObserver((entries) => {
    entries.forEach(e => { if (e.isIntersecting) e.target.classList.add('in'); });
}, { threshold: 0.15 });
revealEls.forEach(el => io.observe(el));
</script>

</body>
</html>
