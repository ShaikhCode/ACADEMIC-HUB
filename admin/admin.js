document.addEventListener("DOMContentLoaded", function () {

  const toggleButton = document.getElementById("toggleButton");
  const formContainer = document.getElementById("avt");
  const profileImg = document.getElementById("profile-img");

  const hamburger = document.querySelector(".hamburger");
  const navbar = document.querySelector(".header .navbar");
  const overlay = document.querySelector(".overlay");

  hamburger.addEventListener("click", () => {
    hamburger.classList.toggle("active");
    navbar.classList.toggle("active");
    overlay.classList.toggle("active"); // Toggle the overlay
  });

  // Close menu when overlay is clicked
  overlay.addEventListener("click", () => {
    hamburger.classList.remove("active");
    navbar.classList.remove("active");
    overlay.classList.remove("active");
  });
});

document.addEventListener("DOMContentLoaded", function () {
  // Reset all forms on page load
  document.querySelectorAll("form").forEach((form) => form.reset());

  // Prevent form resubmission on refresh
  if (window.history.replaceState) {
    window.history.replaceState(null, null, window.location.href);
  }
});


//ON BOARDING
