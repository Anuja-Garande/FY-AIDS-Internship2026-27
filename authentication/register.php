<?php
session_start();
require_once("../config/db.php");

if (isset($_SESSION['user_id'])) {
    header("Location: ../dashboard.php");
    exit();
}

$error = "";
$success = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name     = trim($_POST['full_name']);
    $email    = trim($_POST['email']);
    $phone    = trim($_POST['phone']);
    $password = $_POST['password'];
    $confirm  = $_POST['confirm_password'];

    if ($password !== $confirm) {
        $error = "Passwords do not match.";
    } else {
        $check = $conn->prepare("SELECT id FROM users WHERE email=?");
        $check->bind_param("s", $email);
        $check->execute();

        if ($check->get_result()->num_rows > 0) {
            $error = "An account with this email already exists.";
        } else {
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO users (full_name, email, phone, password) VALUES (?,?,?,?)");
            $stmt->bind_param("ssss", $name, $email, $phone, $hashed);

            if ($stmt->execute()) {
                $newId = $stmt->insert_id;
                // create default settings row
                $s = $conn->prepare("INSERT INTO settings (user_id) VALUES (?)");
                $s->bind_param("i", $newId);
                $s->execute();

                $success = "Account created successfully! You can now log in.";
            } else {
                $error = "Something went wrong. Please try again.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Register | NeoFinance</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,400..900&family=Manrope:wght@400;500;600;700;800&family=IBM+Plex+Mono:wght@400;500;600&display=swap" rel="stylesheet">

<style>
:root{
    --ink:#0E2A1C;
    --ink-2:#153724;
    --ink-3:#1C4530;
    --paper:#F3EEDD;
    --paper-2:#EAE2C6;
    --gold:#C6A15B;
    --gold-bright:#EAD08E;
    --mint:#3FCB92;
    --text:#ECE6D6;
    --muted:#9FB6A6;
    --ink-muted:#6b7160;
    --line: rgba(198,161,91,.28);
}
*{box-sizing:border-box;}
body{
    margin:0;
    min-height:100vh;
    display:flex;align-items:center;justify-content:center;
    padding:30px 16px;
    font-family:'Manrope',sans-serif;
    background:
        radial-gradient(ellipse 60% 50% at 85% 15%, rgba(63,203,146,.14), transparent 60%),
        radial-gradient(ellipse 55% 45% at 5% 90%, rgba(198,161,91,.14), transparent 60%),
        linear-gradient(180deg, var(--ink) 0%, var(--ink-2) 100%);
    position:relative;
}
body::after{
    content:"";
    position:fixed; inset:0;
    background-image:repeating-linear-gradient(115deg, rgba(198,161,91,.05) 0px, rgba(198,161,91,.05) 1px, transparent 1px, transparent 7px);
    pointer-events:none;
}
h1,h2,h3,h4{font-family:'Fraunces',serif;font-weight:700;letter-spacing:-.01em;}
.mono{font-family:'IBM Plex Mono',monospace;}
a{text-decoration:none;}

.eyebrow{
    font-family:'IBM Plex Mono',monospace;
    font-size:11px;
    letter-spacing:.22em;
    text-transform:uppercase;
    color:var(--gold-bright);
    display:flex;align-items:center;gap:10px;
}
.eyebrow::before{content:"";width:20px;height:1px;background:var(--gold-bright);display:inline-block;}

.auth-shell{
    position:relative;z-index:1;
    width:100%;max-width:960px;
    display:flex;
    border-radius:22px;
    overflow:hidden;
    box-shadow:0 40px 90px rgba(0,0,0,.5);
    border:1px solid var(--line);
}

/* ---------- LEFT: brand / vault cover ---------- */
.auth-brand{
    flex:1 1 40%;
    background:linear-gradient(160deg, var(--ink-3), var(--ink-2));
    padding:44px 38px;
    position:relative;
    display:flex;
    flex-direction:column;
    justify-content:space-between;
    min-height:640px;
}
.auth-brand::before{
    content:"";
    position:absolute; inset:14px;
    border:1px solid rgba(198,161,91,.35);
    border-radius:14px;
    pointer-events:none;
}
.brand-mark{
    font-family:'Fraunces',serif;
    font-size:24px;font-weight:800;
    color:var(--gold-bright);
    display:flex;align-items:center;gap:10px;
}
.brand-mark .coin{display:inline-block;animation:coinspin 5s linear infinite;}
@keyframes coinspin{
    0%,80%{transform:scaleX(1);}
    90%{transform:scaleX(.15);}
    100%{transform:scaleX(1);}
}
.brand-rosette{
    position:absolute;
    right:20px;top:100px;
    width:150px;height:150px;
    opacity:.45;
}
.brand-quote{
    position:relative;z-index:1;
    color:var(--paper);
    font-family:'Fraunces',serif;
    font-size:26px;
    line-height:1.35;
    margin-top:30px;
}
.brand-quote span{color:var(--gold-bright);font-style:italic;}
.brand-perks{
    position:relative;z-index:1;
    list-style:none;padding:0;margin:26px 0 0;
    font-size:14px;
    color:var(--muted);
}
.brand-perks li{display:flex;align-items:center;gap:10px;margin-bottom:12px;}
.brand-serial{
    position:relative;z-index:1;
    font-size:11px;
    letter-spacing:.15em;
    color:var(--muted);
    text-transform:uppercase;
    padding-top:16px;
    border-top:1px dashed var(--line);
}

/* ---------- RIGHT: paper form ---------- */
.auth-form-panel{
    flex:1 1 60%;
    background:var(--paper);
    color:var(--ink);
    padding:44px 44px;
    display:flex;
    flex-direction:column;
    justify-content:center;
}
.auth-form-panel h2{
    color:var(--ink);
    font-size:30px;
    margin-bottom:4px;
}
.auth-form-panel .subtitle{
    color:var(--ink-muted);
    font-size:14.5px;
    margin-bottom:24px;
}
.auth-form-panel label{
    font-size:12.5px;
    font-weight:700;
    letter-spacing:.06em;
    text-transform:uppercase;
    color:var(--ink-muted);
    margin-bottom:6px;
    display:block;
}
.auth-form-panel .form-control{
    background:#fff;
    border:1px solid rgba(14,42,28,.18);
    color:var(--ink);
    border-radius:10px;
    padding:11px 14px;
    font-size:15px;
}
.auth-form-panel .form-control:focus{
    border-color:var(--gold);
    box-shadow:0 0 0 .2rem rgba(198,161,91,.2);
}
.btn-gold{
    background:linear-gradient(135deg, var(--gold-bright), var(--gold));
    color:var(--ink);
    border:none;
    border-radius:12px;
    padding:13px;
    font-weight:800;
    font-size:15px;
    position:relative;
    overflow:hidden;
    width:100%;
    transition:transform .2s;
}
.btn-gold::before{
    content:"";
    position:absolute; top:0; left:-60%;
    width:40%; height:100%;
    background:linear-gradient(120deg, transparent, rgba(255,255,255,.6), transparent);
    transform:skewX(-20deg);
    transition:left .6s;
}
.btn-gold:hover::before{left:130%;}
.btn-gold:hover{color:var(--ink);transform:translateY(-1px);}

.alert-vault{
    border-radius:10px;
    padding:12px 16px;
    font-size:14px;
    margin-bottom:18px;
    border:1px solid transparent;
}
.alert-vault.danger{background:rgba(217,122,95,.12);border-color:rgba(217,122,95,.4);color:#a4432a;}
.alert-vault.success{background:rgba(63,203,146,.12);border-color:rgba(63,203,146,.4);color:#1B7A4B;}
.alert-vault.success a{color:#1B7A4B;font-weight:800;text-decoration:underline;}

.form-footer{
    margin-top:20px;
    text-align:center;
    font-size:14px;
    color:var(--ink-muted);
}
.form-footer a{color:#1B7A4B;font-weight:700;}
.form-footer a:hover{color:var(--gold);}

.row-2{display:grid;grid-template-columns:1fr 1fr;gap:14px;}

@media (max-width:767px){
    .auth-shell{flex-direction:column;}
    .auth-brand{min-height:auto;padding:32px 28px;}
    .brand-quote{font-size:20px;margin-top:18px;}
    .brand-rosette{display:none;}
    .auth-form-panel{padding:36px 26px;}
    .row-2{grid-template-columns:1fr;}
}
</style>
</head>
<body>

<div class="auth-shell">

    <div class="auth-brand">
        <div class="brand-mark"><span class="coin">🪙</span> NeoFinance</div>

        <svg class="brand-rosette" viewBox="0 0 120 120" fill="none">
            <circle cx="60" cy="60" r="55" stroke="#EAD08E" stroke-width="1" stroke-dasharray="2 4"/>
            <circle cx="60" cy="60" r="42" stroke="#EAD08E" stroke-width="1"/>
            <circle cx="60" cy="60" r="30" stroke="#EAD08E" stroke-width="1" stroke-dasharray="1 3"/>
            <circle cx="60" cy="60" r="6" fill="#EAD08E"/>
        </svg>

        <div>
            <div class="eyebrow">Series 2026 · New Account</div>
            <p class="brand-quote">Open a new page in<br>your <span>financial ledger.</span></p>

            <ul class="brand-perks">
                <li>💸 Track income and expenses in one place</li>
                <li>🎯 Set savings goals that stick</li>
                <li>🤖 Get AI-powered spending insights</li>
                <li>🔒 Bank-grade password encryption</li>
            </ul>
        </div>

        <div class="brand-serial mono">NF · NEW LEDGER · CX 004822</div>
    </div>

    <div class="auth-form-panel">
        <h2>Create Account</h2>
        <p class="subtitle">Start your ledger — it takes less than a minute.</p>

        <?php if ($error): ?><div class="alert-vault danger"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>
        <?php if ($success): ?><div class="alert-vault success"><?php echo htmlspecialchars($success); ?> <a href="login.php">Login now</a></div><?php endif; ?>

        <form method="POST">
            <div class="mb-3">
                <label>Full Name</label>
                <input type="text" name="full_name" class="form-control" required>
            </div>
            <div class="mb-3">
                <label>Email</label>
                <input type="email" name="email" class="form-control" required>
            </div>
            <div class="mb-3">
                <label>Phone</label>
                <input type="text" name="phone" class="form-control">
            </div>
            <div class="row-2 mb-3">
                <div>
                    <label>Password</label>
                    <input type="password" name="password" class="form-control" required minlength="6">
                </div>
                <div>
                    <label>Confirm Password</label>
                    <input type="password" name="confirm_password" class="form-control" required minlength="6">
                </div>
            </div>
            <button class="btn-gold mt-2" type="submit">Register <i class="fa-solid fa-arrow-right ms-1"></i></button>
        </form>

        <p class="form-footer">
            Already have an account? <a href="login.php">Login</a>
        </p>
    </div>

</div>
</body>
</html>
