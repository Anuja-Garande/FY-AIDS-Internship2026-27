<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>AI & DS Student Project Portal</title>

<!-- Google Fonts -->
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

<!-- Font Awesome -->
<link rel="stylesheet"
href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

<style>

*{
    margin:0;
    padding:0;
    box-sizing:border-box;
    font-family:'Poppins',sans-serif;
}

html{
    scroll-behavior:smooth;
}

body{
    background:#f5f7fa;
    color:#333;
}

/*********************
 NAVBAR
*********************/

header{
    width:100%;
    background:#0d6efd;
    position:fixed;
    top:0;
    left:0;
    z-index:1000;
    box-shadow:0 2px 10px rgba(0,0,0,.2);
}

nav{
    width:90%;
    margin:auto;
    display:flex;
    justify-content:space-between;
    align-items:center;
    padding:15px 0;
}

.logo{
    color:white;
    font-size:28px;
    font-weight:700;
}

.logo span{
    color:#ffd43b;
}

nav ul{
    list-style:none;
    display:flex;
}

nav ul li{
    margin-left:30px;
}

nav ul li a{
    color:white;
    text-decoration:none;
    font-size:17px;
    transition:.3s;
}

nav ul li a:hover{
    color:#ffd43b;
}

/*********************
 HERO SECTION
*********************/

.hero{

height:100vh;

background:
linear-gradient(rgba(13,110,253,.85),
rgba(0,76,153,.85)),
url("https://images.unsplash.com/photo-1516321318423-f06f85e504b3?auto=format&fit=crop&w=1400&q=80");

background-size:cover;
background-position:center;

display:flex;
align-items:center;
justify-content:center;

text-align:center;

padding:20px;

color:white;

}

.hero-content{

max-width:900px;

}

.hero-content h4{

font-size:22px;
letter-spacing:2px;
margin-bottom:15px;

}

.hero-content h1{

font-size:58px;
margin-bottom:20px;

}

.hero-content p{

font-size:20px;
line-height:1.8;
margin-bottom:40px;

}

/*********************
 BUTTONS
*********************/

.btn{

display:inline-block;

padding:15px 40px;

background:#ffd43b;

color:#000;

font-size:20px;

font-weight:600;

border-radius:40px;

text-decoration:none;

transition:.4s;

margin:10px;

}

.btn:hover{

background:white;

transform:translateY(-5px);

box-shadow:0 10px 20px rgba(0,0,0,.3);

}

.btn2{

background:transparent;

border:2px solid white;

color:white;

}

.btn2:hover{

background:white;

color:#0d6efd;

}

/*********************
 SECTION
*********************/

section{

padding:80px 10%;

}

.section-title{

text-align:center;

font-size:40px;

color:#0d6efd;

margin-bottom:15px;

}

.section-sub{

text-align:center;

color:#666;

margin-bottom:50px;

font-size:18px;

}

/*********************
 RESPONSIVE
*********************/

@media(max-width:768px){

.hero-content h1{

font-size:38px;

}

.hero-content p{

font-size:17px;

}

nav{

flex-direction:column;

}

nav ul{

margin-top:15px;

flex-wrap:wrap;

justify-content:center;

}

nav ul li{

margin:10px;

}

.logo{

font-size:22px;

}

}

</style>

</head>

<body>

<header>

<nav>

<div class="logo">
AI <span>&</span> DS
</div>

<ul>

<li><a href="#">Home</a></li>

<li><a href="#about">About</a></li>

<li><a href="#features">Features</a></li>

<li><a href="#contact">Contact</a></li>

</ul>

</nav>

</header>

<!-- HERO SECTION -->

<section class="hero">

<div class="hero-content">

<h4>Department of Artificial Intelligence & Data Science</h4>

<h1>Student Project Portal</h1>

<p>

Learn • Build • Collaborate • Innovate

<br><br>

Develop real-world projects using
HTML, CSS, JavaScript, PHP & MySQL.

</p>

<a href="login.php" class="btn">

<i class="fa-solid fa-right-to-bracket"></i>

Student Login

</a>

<a href="#about" class="btn btn2">

Learn More

</a>

</div>

</section>
<!-- ===========================
        ABOUT SECTION
============================ -->

<section id="about">

<h2 class="section-title">About Student Project Portal</h2>

<p class="section-sub">
A Project-Based Learning platform developed for Second Year AI & DS students
to learn, collaborate and build real-world software projects.
</p>

<div class="about-container">

<div class="about-image">

<img src="https://cdn-icons-png.flaticon.com/512/3135/3135755.png"
alt="Student">

</div>

<div class="about-content">

<h2>Why This Portal?</h2>

<p>

The Student Project Portal provides an organized platform where students
can manage projects, submit tasks, collaborate with team members,
schedule meetings and track project progress.

</p>

<br>

<p>

During this internship students will learn complete web development
using HTML, CSS, JavaScript, PHP and MySQL while following Agile
Methodology.

</p>

<br>

<a href="login.php" class="btn">

Start Learning

</a>

</div>

</div>

</section>

<!-- ===========================
        FEATURES SECTION
============================ -->

<section id="features">

<h2 class="section-title">

Portal Features

</h2>

<p class="section-sub">

Everything required to manage a student project.

</p>

<div class="feature-grid">

<div class="card">

<i class="fa-solid fa-users"></i>

<h3>Team Management</h3>

<p>

Create project teams,
assign members,
track responsibilities.

</p>

</div>

<div class="card">

<i class="fa-solid fa-list-check"></i>

<h3>Task Management</h3>

<p>

Assign daily tasks,
update progress,
manage deadlines.

</p>

</div>

<div class="card">

<i class="fa-solid fa-calendar-check"></i>

<h3>Meeting Scheduler</h3>

<p>

Schedule meetings,
record attendance,
manage discussions.

</p>

</div>

<div class="card">

<i class="fa-solid fa-chart-line"></i>

<h3>Project Progress</h3>

<p>

Monitor work,
generate reports,
track completion.

</p>

</div>

</div>

</section>

<style>

/*************************
ABOUT SECTION
**************************/

.about-container{

display:flex;
align-items:center;
justify-content:space-between;
gap:60px;
flex-wrap:wrap;

}

.about-image{

flex:1;
text-align:center;

}

.about-image img{

width:100%;
max-width:380px;

}

.about-content{

flex:1;

}

.about-content h2{

font-size:36px;
margin-bottom:20px;
color:#0d6efd;

}

.about-content p{

font-size:18px;
line-height:1.8;
color:#555;

}

/*************************
FEATURES
**************************/

.feature-grid{

display:grid;

grid-template-columns:
repeat(auto-fit,minmax(250px,1fr));

gap:30px;

margin-top:40px;

}

.card{

background:white;

padding:35px;

border-radius:15px;

text-align:center;

box-shadow:0 10px 25px rgba(0,0,0,.08);

transition:.4s;

}

.card:hover{

transform:translateY(-12px);

box-shadow:0 20px 40px rgba(13,110,253,.25);

}

.card i{

font-size:55px;

color:#0d6efd;

margin-bottom:20px;

}

.card h3{

margin-bottom:15px;

font-size:24px;

}

.card p{

font-size:17px;

color:#666;

line-height:1.7;

}

/*************************
RESPONSIVE
**************************/

@media(max-width:768px){

.about-container{

flex-direction:column;

text-align:center;

}

.about-content h2{

font-size:30px;

}

}

</style>
<!-- ===========================
        TECHNOLOGIES
============================ -->

<section id="technology">

<h2 class="section-title">Technologies You'll Learn</h2>

<p class="section-sub">
Master modern web technologies through project-based learning.
</p>

<div class="feature-grid">

<div class="card">
<i class="fa-brands fa-html5" style="color:#e44d26;"></i>
<h3>HTML5</h3>
<p>Structure beautiful and responsive web pages.</p>
</div>

<div class="card">
<i class="fa-brands fa-css3-alt" style="color:#2965f1;"></i>
<h3>CSS3</h3>
<p>Create attractive user interfaces with responsive layouts.</p>
</div>

<div class="card">
<i class="fa-brands fa-js" style="color:#f7df1e;"></i>
<h3>JavaScript</h3>
<p>Add interactivity and dynamic features to websites.</p>
</div>

<div class="card">
<i class="fa-brands fa-php" style="color:#777bb4;"></i>
<h3>PHP</h3>
<p>Develop dynamic web applications using server-side scripting.</p>
</div>

<div class="card">
<i class="fa-solid fa-database"></i>
<h3>MySQL</h3>
<p>Store and manage application data efficiently.</p>
</div>

<div class="card">
<i class="fa-brands fa-bootstrap" style="color:#7952b3;"></i>
<h3>Bootstrap</h3>
<p>Build responsive websites quickly using Bootstrap.</p>
</div>

</div>

</section>

<!-- ===========================
        STATISTICS
============================ -->

<section class="stats">

<div class="stat-box">
<h1>100+</h1>
<p>Students</p>
</div>

<div class="stat-box">
<h1>25+</h1>
<p>Projects</p>
</div>

<div class="stat-box">
<h1>10+</h1>
<p>Technologies</p>
</div>

<div class="stat-box">
<h1>100%</h1>
<p>Hands-on Learning</p>
</div>

</section>

<!-- ===========================
        FACULTY MESSAGE
============================ -->

<section>

<h2 class="section-title">Faculty Message</h2>

<div class="message">

<p>

Welcome to the Student Project Portal.

This internship is designed to help you bridge the gap between classroom learning and real-world software development. Throughout this journey, you will work in teams, follow Agile practices, and develop industry-relevant projects using HTML, CSS, JavaScript, PHP, and MySQL.

We encourage you to collaborate, think creatively, and continuously improve your technical and communication skills.

</p>

<h3 style="margin-top:25px;color:#0d6efd;">
Department of Artificial Intelligence & Data Science
</h3>

</div>

</section>

<!-- ===========================
        CONTACT
============================ -->

<section id="contact">

<h2 class="section-title">Contact Us</h2>

<div class="feature-grid">

<div class="card">
<i class="fa-solid fa-building"></i>
<h3>Department</h3>
<p>Artificial Intelligence & Data Science</p>
</div>

<div class="card">
<i class="fa-solid fa-envelope"></i>
<h3>Email</h3>
<p>aids@college.edu</p>
</div>

<div class="card">
<i class="fa-solid fa-phone"></i>
<h3>Support</h3>
<p>+91 XXXXX XXXXX</p>
</div>

</div>

</section>

<!-- ===========================
        FOOTER
============================ -->

<footer>

<h2>Student Project Portal</h2>

<p>
Department of Artificial Intelligence & Data Science
</p>

<p>

Develop • Learn • Collaborate • Innovate

</p>

<br>

<div class="social">

<i class="fab fa-facebook"></i>

<i class="fab fa-instagram"></i>

<i class="fab fa-linkedin"></i>

<i class="fab fa-github"></i>

</div>

<br>

<p>

© 2026 AI & DS Department. All Rights Reserved.

</p>

</footer>

<!-- Back to Top -->

<button onclick="topFunction()" id="topBtn">

<i class="fa-solid fa-arrow-up"></i>

</button>

<style>

/************ Statistics ************/

.stats{

background:#0d6efd;

color:white;

display:grid;

grid-template-columns:repeat(auto-fit,minmax(200px,1fr));

text-align:center;

padding:70px 10%;

gap:30px;

}

.stat-box h1{

font-size:50px;

margin-bottom:10px;

}

.stat-box p{

font-size:20px;

}

/************ Faculty Message ************/

.message{

max-width:900px;

margin:auto;

background:white;

padding:40px;

border-radius:15px;

box-shadow:0 10px 30px rgba(0,0,0,.1);

font-size:18px;

line-height:1.9;

text-align:center;

}

/************ Footer ************/

footer{

background:#111827;

color:white;

text-align:center;

padding:60px 20px;

}

.social i{

font-size:28px;

margin:10px;

cursor:pointer;

transition:.3s;

}

.social i:hover{

color:#ffd43b;

}

/************ Top Button ************/

#topBtn{

display:none;

position:fixed;

bottom:30px;

right:30px;

background:#0d6efd;

color:white;

border:none;

padding:15px 18px;

border-radius:50%;

cursor:pointer;

font-size:20px;

box-shadow:0 5px 15px rgba(0,0,0,.3);

}

#topBtn:hover{

background:#084298;

}

</style>

<script>

let btn=document.getElementById("topBtn");

window.onscroll=function(){

if(document.body.scrollTop>300 || document.documentElement.scrollTop>300)

btn.style.display="block";

else

btn.style.display="none";

}

function topFunction(){

window.scrollTo({

top:0,

behavior:'smooth'

});

}

</script>

</body>
</html>