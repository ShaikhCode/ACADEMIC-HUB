function check() {
    let name = document.getElementById("name");
    let password = document.getElementById("password");
    let nameValue = name.value.trim();
    let passwordValue = password.value.trim();
    const errorMessage = document.getElementById("errorMessage");

    // Define separate arrays for Admin, Staff, and Students with just usernames and passwords
    const admins = [
        { username: 'admin', password: '123' },
        { username: 'admin1', password: '123' },
        { username: 'a1', password: '123' }
    ];

    const staff = [
        { username: 'staff', password: '123' },
        { username: 'staff1', password: '123' },
        { username: 's1', password: '123' }
    ];

    const students = [
        { username: 'student', password: '123' },
        { username: 'stud', password: '123' },
        { username: 's1', password: '123' }
    ];

    // Get selected role
    let roleElement = document.getElementById("role");
    let selectedRole = roleElement.value;

    // Define the user array based on the selected role
    let users;
    if (selectedRole === 'admin') {
        users = admins;
    } else if (selectedRole === 'staff') {
        users = staff;
    } else if (selectedRole === 'student') {
        users = students;
    }

    // Check if the entered username and password match any user in the selected role
    let user = users.find(user => user.username === nameValue && user.password === passwordValue);


    if (!user) {
        // If no matching user is found, show an error message
        name.classList.add("shake");
        errorMessage.style.visibility = "visible";

        setTimeout(function() {
            name.classList.remove("shake");
            errorMessage.style.visibility = "hidden";
        }, 500);
    } else {
        // Redirect based on the selected role
        let redirectPath = '';
        if (selectedRole === 'admin') {
            redirectPath = './admin/admin.html';  // Redirect for admin
        } else if (selectedRole === 'staff') {
            redirectPath = './staff/staff.html';  // Redirect for staff
        } else if (selectedRole === 'student') {
            redirectPath = './student/stud.html';  // Redirect for student
        }
        window.location.href = redirectPath;  
    }


    // Update the signup link
    const signupLink = document.getElementById('signup-link');
    signupLink.href = `signup-${selectedRole}.html`;
}
