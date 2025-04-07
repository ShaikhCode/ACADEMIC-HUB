<?php
session_start();  // Start the session to check if the user is logged in
?>
<!DOCTYPE html>
<html>
  <head>
    <!-- Basic Meta Information -->
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta
      name="viewport"
      content="width=device-width, initial-scale=1, shrink-to-fit=no"
    />
    <meta
      name="keywords"
      content="Academic Hub, School Management, College Management"
    />
    <meta
      name="description"
      content="Academic Hub - A comprehensive platform for managing educational administration with structured access for admins, staff, and students."
    />
    <meta name="author" content="Academic Hub Team" />
    <link rel="shortcut icon" href="img/favicon.png" type="image/x-icon" />

    <title>Academic Hub - Your Educational Management Solution</title>

    <!-- bootstrap core css -->
    <link rel="stylesheet" type="text/css" href="css/bootstrap.css" />
    <!--owl slider stylesheet -->
    <link
      rel="stylesheet"
      type="text/css"
      href="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/assets/owl.carousel.min.css"
    />

    <link
      rel="stylesheet"
      href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css"
      integrity="sha512-..."
      crossorigin="anonymous"
    />

    <!-- Custom styles -->
    <link href="css/style.css" rel="stylesheet" />
    <link href="css/responsive.css" rel="stylesheet" />
  </head>

  <body>
    <div class="hero_area">
      <div class="hero_bg_box">
        <div class="bg_img_box">
          <img src="img/hero-bg.png" alt="Academic Hub Background" />
        </div>
      </div>

      <!-- Header Section -->
      <header class="header_section">
        <div class="container-fluid">
          <nav class="navbar navbar-expand-lg custom_nav-container">
            <a class="navbar-brand" href="index.php">
              <span>
                <span style="font-size: 2em; color: red">A</span>CADEMIC-<span
                  style="font-size: 2em; color: red"
                  >H</span>UB
              </span>
            </a>

            <button
              class="navbar-toggler"
              type="button"
              data-toggle="collapse"
              data-target="#navbarSupportedContent"
              aria-controls="navbarSupportedContent"
              aria-expanded="false"
              aria-label="Toggle navigation"
            >
              <span class=""> </span>
            </button>

            <div class="collapse navbar-collapse" id="navbarSupportedContent">
              <ul class="navbar-nav">
                <li class="nav-item active">
                  <a class="nav-link" href="index.php">Home</a>
                </li>
                <li class="nav-item">
                  <a class="nav-link" href="about.php">About</a>
                </li>
                <li class="nav-item">
                  <a class="nav-link" href="service.php">Services</a>
                </li>
                <li class="nav-item">
                  <a class="nav-link" href="why.php">Why Us</a>
                </li>
                <li class="nav-item">
                  <a class="nav-link" href="team.php">Team</a>
                </li>
                <li class="nav-item">
                  <a class="nav-link" href="log.php"
                    ><i class="fa fa-user" aria-hidden="true"></i> Login</a
                  >
                </li>
                <form class="form-inline">
                  <button class="btn my-2 my-sm-0 nav_search-btn" type="submit">
                    <i class="fa fa-search" aria-hidden="true"></i>
                  </button>
                </form>
              </ul>
            </div>
          </nav>
        </div>
      </header>
      <!-- End Header Section -->

      <!-- Slider Section -->
      <section class="slider_section">
        <div id="customCarousel1" class="carousel slide" data-ride="carousel">
          <div class="carousel-inner">
            <!-- Slide 1 -->
            <div class="carousel-item active">
              <div class="container">
                <div class="row">
                  <div class="col-md-6">
                    <div class="detail-box">
                      <h1>
                        Academic <br />
                        Hub
                      </h1>
                      <p>
                        Academic Hub is an all-in-one educational platform
                        designed to streamline school and college
                        administration. It provides admins, staff, and students
                        with a structured, user-friendly interface to manage and
                        access essential academic information.
                      </p>
                      <div class="btn-box">
                        <a href="aboutphp" class="btn1"> Learn More </a>
                      </div>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="img-box">
                      <img src="img/slider3.png" alt="Academic Hub Image" />
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <!-- Slide 2 -->
            <div class="carousel-item">
              <div class="container">
                <div class="row">
                  <div class="col-md-6">
                    <div class="detail-box">
                      <h1>
                        Empower <br />
                        Education
                      </h1>
                      <p>
                        From attendance tracking to grades management, Academic
                        Hub empowers institutions to efficiently manage academic
                        records, notifications, and feedback. Students can
                        easily track their academic progress, receive timely
                        notifications, and provide feedback to faculty.
                      </p>
                      <div class="btn-box">
                        <a href="service.php" class="btn1">
                          Explore Services
                        </a>
                      </div>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="img-box">
                      <img src="img/slider2.png" alt="Educational Tools" />
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <!-- Slide 3 -->
            <div class="carousel-item">
              <div class="container">
                <div class="row">
                  <div class="col-md-6">
                    <div class="detail-box">
                      <h1>
                        Simplify <br />
                        Administration
                      </h1>
                      <p>
                        Academic Hub simplifies school administration with
                        role-based access, encrypted data storage, and printable
                        reports, ensuring secure and organized management of all
                        student, staff, and academic records.
                      </p>
                      <div class="btn-box">
                        <a href="why.php" class="btn1"> Discover Why </a>
                      </div>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="img-box">
                      <img
                        src="img/slider1.png"
                        alt="Administrative Simplification"
                      />
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <ol class="carousel-indicators">
            <li
              data-target="#customCarousel1"
              data-slide-to="0"
              class="active"
            ></li>
            <li data-target="#customCarousel1" data-slide-to="1"></li>
            <li data-target="#customCarousel1" data-slide-to="2"></li>
          </ol>
        </div>
      </section>
      <!-- End Slider Section -->
    </div>

    <!-- Service Section -->
    <section class="service_section layout_padding">
      <div class="service_container">
        <div class="container">
          <div class="heading_container heading_center">
            <h2>Academic Hub <span>Features</span></h2>
            <p>
              Discover the comprehensive features of Academic Hub, designed to
              streamline administration, improve accessibility, and enhance
              educational experiences.
            </p>
          </div>
          <div class="row">
            <!-- Feature 1 -->
            <div class="col-md-4">
              <div class="box">
                <div class="img-box">
                  <img src="img/s1.png" alt="Responsive Design" />
                </div>
                <div class="detail-box">
                  <h5>Responsive Design</h5>
                  <p>
                    Our platform adapts seamlessly across devices, from desktops
                    to smartphones, providing easy access to all users.
                  </p>
                  <a href="service.php"> Read More </a>
                </div>
              </div>
            </div>
            <!-- Feature 2 -->
            <div class="col-md-4">
              <div class="box">
                <div class="img-box">
                  <img src="img/s2.png" alt="Role-Based Access" />
                </div>
                <div class="detail-box">
                  <h5>Role-Based Access</h5>
                  <p>
                    Tailored access levels for admins, staff, and students
                    ensure each role has the right permissions for efficient
                    management.
                  </p>
                  <a href="service.php"> Read More </a>
                </div>
              </div>
            </div>
            <!-- Feature 3 -->
            <div class="col-md-4">
              <div class="box">
                <div class="img-box">
                  <img src="img/s3.png" alt="Data Security" />
                </div>
                <div class="detail-box">
                  <h5>Data Security</h5>
                  <p>
                    Academic Hub encrypts sensitive data and stores it locally,
                    ensuring access even during connectivity issues.
                  </p>
                  <a href="service.php"> Read More </a>
                </div>
              </div>
            </div>
          </div>
          <div class="btn-box">
            <a href="service.php"> View All Features </a>
          </div>
        </div>
      </div>
    </section>
    <!-- End Service Section -->

    <!-- About Section -->
    <section class="about_section layout_padding">
      <div class="container">
        <div class="heading_container heading_center">
          <h2>About <span>Academic Hub</span></h2>
          <p>
            Academic Hub is a comprehensive platform crafted to support school
            and college administration by streamlining academic, administrative,
            and student management tasks.
          </p>
        </div>
        <div class="row">
          <div class="col-md-6">
            <div class="img-box">
              <img src="img/about-img.png" alt="Academic Hub Team" />
            </div>
          </div>
          <div class="col-md-6">
            <div class="detail-box">
              <h3>Our Mission</h3>
              <p>
                To empower educational institutions by simplifying the
                management of attendance, grades, notifications, and more. Our
                goal is to provide a reliable and secure solution that enhances
                educational experiences for all users.
              </p>
              <a href="about.php"> Read More </a>
            </div>
          </div>
        </div>
      </div>
    </section>
    <!-- End About Section -->

    <!-- Why Choose Us Section -->
    <section class="why_section layout_padding">
      <div class="container">
        <div class="heading_container heading_center">
          <h2>Why Choose <span>Academic Hub</span></h2>
        </div>
        <div class="why_container">
          <div class="box">
            <div class="img-box">
              <img src="img/w1.png" alt="Enhanced Efficiency" />
            </div>
            <div class="detail-box">
              <h5>Enhanced Efficiency</h5>
              <p>
                With structured workflows for admins, staff, and students,
                Academic Hub helps institutions save time and reduce workload.
              </p>
            </div>
          </div>
          <div class="box">
            <div class="img-box">
              <img src="img/w2.png" alt="Data Security" />
            </div>
            <div class="detail-box">
              <h5>Data Security</h5>
              <p>
                All sensitive data is encrypted and backed up locally, ensuring
                data availability even during network issues.
              </p>
            </div>
          </div>
          <div class="box">
            <div class="img-box">
              <img src="img/w3.png" alt="Real-Time Notifications" />
            </div>
            <div class="detail-box">
              <h5>Real-Time Notifications</h5>
              <p>
                Keep students informed with instant updates on attendance,
                grades, and feedback responses, fostering engagement.
              </p>
            </div>
          </div>
          <div class="box">
            <div class="img-box">
              <img src="img/w4.png" alt="Easy Access and Control" />
            </div>
            <div class="detail-box">
              <h5>Easy Access and Control</h5>
              <p>
                A user-friendly interface and role-based access provide admins,
                staff, and students with an organized and intuitive experience.
              </p>
            </div>
          </div>
        </div>
        <div class="btn-box">
          <a href="why.php">Discover More</a>
        </div>
      </div>
    </section>
    <!-- End Why Choose Us Section -->

    <!-- team section -->
    <section class="team_section layout_padding">
      <div class="container-fluid">
        <div class="heading_container heading_center">
          <h2 class="">Our <span> Team</span></h2>
        </div>

        <div class="team_container">
          <div class="row">
            <div class="col-lg-3 col-sm-6">
              <div class="box">
                <div class="img-box">
                  <img src="img/team-1.jpg" class="img1" alt="Team leader" />
                </div>
                <div class="detail-box">
                  <h5>Hussain Shaikh</h5>
                  <p>Project Lead</p>
                </div>
                <div class="social_box">
                  <a href="#"><i class="fab fa-facebook-f"></i></a>
                  <a href="#"><i class="fab fa-twitter"></i></a>
                  <a
                    href="https://www.linkedin.com/in/mohd-hussain-shaikh-6610bb318?utm_source=share&utm_campaign=share_via&utm_content=profile&utm_medium=android_app"
                    ><i class="fab fa-linkedin-in"></i
                  ></a>
                  <a
                    href="https://www.instagram.com/hussain_shaikh_188/profilecard/?igsh=bDdxemFpaHd3em95"
                    ><i class="fab fa-instagram"></i
                  ></a>
                </div>
              </div>
            </div>
            <div class="col-lg-3 col-sm-6">
              <div class="box">
                <div class="img-box">
                  <img src="img/team-2.jpg" class="img1" alt="" />
                </div>
                <div class="detail-box">
                  <h5>Sujal Champanari</h5>
                  <p>Web Designer</p>
                </div>
                <div class="social_box">
                  <a href="#"><i class="fab fa-facebook-f"></i></a>
                  <a href="#"><i class="fab fa-twitter"></i></a>
                  <a
                    href="https://www.linkedin.com/in/mohd-hussain-shaikh-6610bb318?utm_source=share&utm_campaign=share_via&utm_content=profile&utm_medium=android_app"
                    ><i class="fab fa-linkedin-in"></i
                  ></a>
                  <a
                    href="https://www.instagram.com/hussain_shaikh_188/profilecard/?igsh=bDdxemFpaHd3em95"
                    ><i class="fab fa-instagram"></i
                  ></a>
                </div>
              </div>
            </div>
            <div class="col-lg-3 col-sm-6">
              <div class="box">
                <div class="img-box">
                  <img src="img/team-3.jpg" class="img1" alt="" />
                </div>
                <div class="detail-box">
                  <h5>Yug Bari</h5>
                  <p>Project testor</p>
                </div>
                <div class="social_box">
                  <a href="#"><i class="fab fa-facebook-f"></i></a>
                  <a href="#"><i class="fab fa-twitter"></i></a>
                  <a
                    href="https://www.linkedin.com/in/mohd-hussain-shaikh-6610bb318?utm_source=share&utm_campaign=share_via&utm_content=profile&utm_medium=android_app"
                    ><i class="fab fa-linkedin-in"></i
                  ></a>
                  <a
                    href="https://www.instagram.com/hussain_shaikh_188/profilecard/?igsh=bDdxemFpaHd3em95"
                    ><i class="fab fa-instagram"></i
                  ></a>
                </div>
              </div>
            </div>
            <div class="col-lg-3 col-sm-6">
              <div class="box">
                <div class="img-box">
                  <img src="img/team-4.jpg" class="img1" alt="" />
                </div>
                <div class="detail-box">
                  <h5>Sharvil Raut</h5>
                  <p>Frontend developer</p>
                </div>
                <div class="social_box">
                  <a href="#"><i class="fab fa-facebook-f"></i></a>
                  <a href="#"><i class="fab fa-twitter"></i></a>
                  <a
                    href="https://www.linkedin.com/in/mohd-hussain-shaikh-6610bb318?utm_source=share&utm_campaign=share_via&utm_content=profile&utm_medium=android_app"
                    ><i class="fab fa-linkedin-in"></i
                  ></a>
                  <a
                    href="https://www.instagram.com/hussain_shaikh_188/profilecard/?igsh=bDdxemFpaHd3em95"
                    ><i class="fab fa-instagram"></i
                  ></a>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>
    <!-- end team section -->

    <!-- Client section -->
    <section class="client_section layout_padding">
      <div class="container">
        <div class="heading_container heading_center">
          <h2>What Our <span>Clients Say</span></h2>
        </div>
        <div class="carousel-wrap">
          <div class="owl-carousel client_owl-carousel">
            <div class="item">
              <div class="box">
                <div class="img-box">
                  <img src="img/client1.jpg" alt="Client Image" />
                </div>
                <div class="detail-box">
                  <div class="client_id">
                    <div class="client_info">
                      <h6>John Doe</h6>
                      <p>Principal at Sunshine School</p>
                    </div>
                    <i class="fa fa-quote-left"></i>
                  </div>
                  <p>
                    "Academic Hub has transformed our administrative process,
                    making it easy to manage classes, students, and grades."
                  </p>
                </div>
              </div>
            </div>
            <!-- Additional testimonials as needed -->
            <div class="item">
              <div class="box">
                <div class="img-box">
                  <img src="img/client2.jpg" alt="Client Image" />
                </div>
                <div class="detail-box">
                  <div class="client_id">
                    <div class="client_info">
                      <h6>Gen Court</h6>
                      <p>Principal at St high School</p>
                    </div>
                    <i class="fa fa-quote-left"></i>
                  </div>
                  <p>
                    "Academic Hub has a simple and understanding interface our
                    administrative process, making it easy and in less time more
                    work is done."
                  </p>
                </div>
              </div>
            </div>
            <!-- Add more-->
          </div>
        </div>
      </div>
    </section>
    <!-- End Client section-->

    <!-- Information Section -->
    <section class="info_section layout_padding2">
      <div class="container">
        <div class="row">
          <!-- Contact Information -->
          <div class="col-md-6 col-lg-3 info_col">
            <div class="info_contact">
              <h4>Contact Us</h4>
              <div class="contact_link_box">
                <a href="#"
                  ><i class="fa fa-map-marker"></i> Dahanu, palghar, Maharashtra
                </a>
                <a href="#"><i class="fa fa-phone"></i> +91 1234567890</a>
                <a href="#"
                  ><i class="fa fa-envelope"></i> support@academichub.com</a
                >
              </div>
            </div>
            <div class="info_social">
              <a href="#"><i class="fab fa-facebook-f"></i></a>
              <a href="#"><i class="fab fa-twitter"></i></a>
              <a href="#"><i class="fab fa-linkedin-in"></i></a>
              <a href="#"><i class="fab fa-instagram"></i></a>
            </div>
          </div>
          <!-- About and Quick Links -->
          <div class="col-md-6 col-lg-3 info_col">
            <h4>About Us</h4>
            <p>
              Academic Hub provides a powerful platform for managing school or
              college administration, designed with modern educational needs in
              mind.
            </p>
          </div>
          <div class="col-md-6 col-lg-2 mx-auto info_col">
            <h4>Quick Links</h4>
            <div class="info_links">
              <a href="index.php">Home</a>
              <a href="about.php">About</a>
              <a href="service.php">Services</a>
              <a href="why.php">Why Us</a>
              <a href="team.php">Team</a>
            </div>
          </div>
          <!-- Subscription Form -->
          <div class="col-md-6 col-lg-3 info_col">
            <h4>Subscribe</h4>
            <form action="#">
              <input type="email" placeholder="Enter email" />
              <button type="submit">Subscribe</button>
            </form>
          </div>
        </div>
      </div>
    </section>
    <!-- End Information Section -->

    <!-- Footer Section -->
    <section class="footer_section">
      <div class="container">
        <p>
          &copy; <span id="displayYear"></span> All Rights Reserved by Academic
          Hub | Designed with passion by the Academic Hub Team.
        </p>
      </div>
    </section>
    <!-- End Footer Section -->

    <!-- Start Button -->
    <div class="start-btn">
      <a href="log.php">Start Now</a>
    </div>
    <!-- End Start Button -->

    <!-- jQery -->
    <script type="text/javascript" src="js/jquery-3.4.1.min.js"></script>
    <!-- popper js -->
    <script
      src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js"
      integrity="sha384-Q6E9RHvbIyZFJoft+2mJbHaEWldlvI9IOYy5n3zV9zzTtmI3UksdQRVvoxMfooAo"
      crossorigin="anonymous"
    ></script>
    <!-- bootstrap js -->
    <script type="text/javascript" src="js/bootstrap.js"></script>
    <!-- owl slider -->
    <script
      type="text/javascript"
      src="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/owl.carousel.min.js"
    ></script>
    <!-- custom js -->
    <script type="text/javascript" src="js/custom.js"></script>
    <!-- Google Map -->
    <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyCh39n5U-4IoWpsVGUHWdqB6puEkhRLdmI&callback=myMap"></script>
    <!-- End Google Map -->
    <script>
      document.getElementById("displayYear").innerHTML =
        new Date().getFullYear();
    </script>
  </body>
</html>
