const hamburger = document.querySelector('.hamburger');
const navbar = document.querySelector('.navbar');

hamburger.addEventListener('click', () => {
    hamburger.classList.toggle('active');
    navbar.classList.toggle('active');
});

/*
// Name of the Login Person
  let name = document.getElementById("u");
  name.textContent="Boss";

*/
// progress bar 

function updateProgress(targetPercentage, elementId) {
  const percentageText = document.getElementById(elementId);
  const maskFull = document.getElementById("mask-full");
  const maskHalf = document.getElementById("mask-half");

  let currentPercentage = 0;

  const interval = setInterval(() => {
    if (currentPercentage >= targetPercentage) {
      clearInterval(interval);
    } else {
      currentPercentage++;
      percentageText.textContent = `${currentPercentage}%`;

      if (currentPercentage <= 50) {
        maskFull.style.transform = `rotate(${currentPercentage * 3.6}deg)`;
        maskHalf.style.transform = `rotate(0deg)`;
      } else {
        maskFull.style.transform = `rotate(180deg)`;
        maskHalf.style.transform = `rotate(${(currentPercentage - 50) * 3.6}deg)`;
      }
    }
  }, 20);
}

// Update attendance percentage
let targetAttendance = 80;
updateProgress(targetAttendance, "percentage");

// Update overall percentage
let targetOverall = 70;
updateProgress(targetOverall, "progress");