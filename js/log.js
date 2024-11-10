function check() {
    let name = document.getElementById("name");
    let nameValue = name.value.trim();
    const errorMessage = document.getElementById("errorMessage");
    // Redirect to the appropriate path based on the selected role
        let redirectPath = `admin/desc.html`;  // Construct path based on role
        window.location.href = redirectPath;  // Redirect the user
    let roleElement = document.getElementById("role");
    let role = roleElement.value;  // Get the selected role value
    
    //alert()

    // Validation for empty name field
    if (nameValue === "" || nameValue!="hello") {
        name.classList.add("shake");
        errorMessage.style.visibility = "visible";

        setTimeout(function() {
            name.classList.remove("shake");
            errorMessage.style.visibility = "hidden";
        }, 500);
    } else if(nameValue=="hello"){
        // Redirect to the appropriate path based on the selected role
        let redirectPath = `./admin/desc.html`;  // Construct path based on role
        window.location.href = redirectPath;  // Redirect the user
    }

    // Update the signup link
    const signupLink = document.getElementById('signup-link');
    signupLink.href = `signup-${role}.html`;
}