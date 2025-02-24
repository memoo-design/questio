<?php session_start(); ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Questio - Home</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <!-- Include Animate.css for Bootstrap animations -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
<!-- AOS CSS -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.css">

    <link rel="stylesheet" href="public/css/index.css">
</head>
<body>
    <?php
include_once '../questio/components/guest_header.php';
?>

    <section id="Hero">
       <div id="hero-text">
           <h1>QUESTIO</h1>
           <h3>A Questioning Platform for Insights</h3>
           <h3>Create, Share, and Analyze quizzes with ease</h3>
       </div>
       <div id ="hero_img"></div>
       <a class="btn btn-primary sign-in-btn" href="../questio/pages/guest/register.php">Sign Up</a>
   </section>  
  <!-- About Us Section (Full-Width Black Background) -->
  <section id="aboutus" class="w-100 bg-black text-white text-center py-5 animate__animated animate__fadeIn">
    <div class="container">
        <h1 class="fw-bold animate__animated animate__slideInDown">About Us</h1>
        <p class="lead animate__animated animate__slideInLeft animate__delay-0.5s">
            Questio is a dynamic and user-friendly platform designed to revolutionize the way quizzes are created, shared, and evaluated. 
            Our goal is to empower educators, students, and professionals by providing an intuitive interface that simplifies the assessment process.
        </p>
        <p class="lead animate__animated animate__slideInRight animate__delay-0.git add s">
            With Questio, education becomes more efficient, engaging, and insightful. Join us today and experience a smarter way to learn and assess knowledge!
        </p>
    </div>
</section>
<div class="service-section mb-5 mt-5">
    <div class="container-fluid">
        <h1 class="text-center mb-5">Our Services</h1>
        
        <div class="row g-4 px-4">
            <div class="col-md-6 col-lg-4">
                <div class="service-box border border-dark border-3 rounded p-4 text-center" data-aos="fade-up">
                    <h3>Quiz Creation</h3>
                    <p>Easily create interactive quizzes with correct answers.</p>
                </div>
            </div>

            <div class="col-md-6 col-lg-4">
                <div class="service-box border border-dark border-3 rounded p-4 text-center" data-aos="fade-up" data-aos-delay="100">
                    <h3>Student Management</h3>
                    <p>Teachers can manage students and assign quizzes.</p>
                </div>
            </div>

            <div class="col-md-6 col-lg-4">
                <div class="service-box border border-dark border-3 rounded p-4 text-center" data-aos="fade-up" data-aos-delay="200">
                    <h3>Auto-Grading</h3>
                    <p>Automatic evaluation and scoring of student responses.</p>
                </div>
            </div>

            <div class="col-md-6 col-lg-4">
                <div class="service-box border border-dark border-3 rounded p-4 text-center" data-aos="fade-up" data-aos-delay="300">
                    <h3>Analytics & Reports</h3>
                    <p>Track student performance with real-time statistics and insights.</p>
                </div>
            </div>

            <div class="col-md-6 col-lg-4">
                <div class="service-box border border-dark border-3 rounded p-4 text-center" data-aos="fade-up" data-aos-delay="400">
                    <h3>Time-Limited Quizzes</h3>
                    <p>Enhance engagement with countdown timers for quizzes.</p>
                </div>
            </div>

            <div class="col-md-6 col-lg-4">
                <div class="service-box border border-dark border-3 rounded p-4 text-center" data-aos="fade-up" data-aos-delay="500">
                    <h3>Question Bank</h3>
                    <p>Store and reuse frequently asked questions for faster quiz creation.</p>
                </div>
            </div>
        </div>
    </div>
</div>

    <section id ="testimonial section">
    <h1>Why People Love QUESTIO</h1>
    <div class="testimonial">
    <FIELDSET>
    <Legend>AUTHOR</legend><em>"I’ve been using ThinkPads for over a decade, and they’ve never let me down. From business trips to rough outdoor conditions, these laptops perform flawlessly every time!</em></FIELDSET>
    </div>
    <div class="testimonial">
       <FIELDSET>
       <Legend>James T</legend><em>"The ThinkPad’s keyboard is a dream for someone like me who spends hours typing every day. Combine that with long battery life and robust security
           , and it’s the ideal laptop for my work needs.</em></FIELDSET>
       </div>
       <div class="testimonial">
           <FIELDSET>
           <Legend>Priya K</legend><em>As an IT professional, I appreciate the power and security features of the ThinkPad series. It handles heavy workloads with ease, and I trust it with my most sensitive data.</em></FIELDSET>
           </div>
       </section>
       <section id="Contact">
           <h1>Contact us</h1>
           <form>
               <label>"Enter your name: <input type="text"></label>
               <label>E-mail: <input type="text"></label>
               <label> Message<textarea cols="8" rows="8"></textarea></label>
               <button>SEND MESSAGE</button>
           </form>
       </section>
      <!-- AOS JS -->
      <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.js"></script>
<script>
    AOS.init();
</script>


       
   </body>
   </html>
   <?php
include_once '../questio/components/guest_footer.php';
?>
