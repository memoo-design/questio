<?php session_start(); ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Questio - Home</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
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
   <section id="about-us">
    <h1>About Us</h1>
    <p>
        Questio is a dynamic and user-friendly platform designed to revolutionize the way quizzes are created, shared, and evaluated. 
        Our goal is to empower educators, students, and professionals by providing an intuitive interface that simplifies the assessment process. 
        Whether you are a teacher managing student assessments, a student testing your knowledge, or an organization conducting skill evaluations, 
        Questio offers a seamless experience with automated grading, insightful analytics, and interactive learning tools.
    </p>
    <p>
        With Questio, education becomes more efficient, engaging, and insightful. Join us today and experience a smarter way to learn and assess knowledge!
    </p>
   </section>
    
    <h1 id="shead">Our Services</h1>
    <section id="services">
             
             <div id="service1text">
                      
                      <h3>Quiz Creation</h3>
                     <p> Easily create interactive quizzes with correct answers</p> 
             </div>
          
               <div id="service2text">  
            
              <h3>Student Management</h3>
              <p>Teachers can manage students and assign quizzes</p>
              </div>
          
             <div id="service3text"> 
            
               <h3>Auto-Grading</h3>
               <p>Automatic evaluation and scoring of student responses</p>
              </div>
    </section>
  
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

   </body>
   </html>
   <?php
include_once '../questio/components/guest_footer.php';
?>
