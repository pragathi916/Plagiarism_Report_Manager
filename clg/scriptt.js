function generateFileInputs() {
    const num = document.getElementById('num').value;
    const fileInputsContainer = document.getElementById('fileInputsContainer');
    fileInputsContainer.innerHTML = '';

    // Limit the number of file inputs to 3
    const limitedNum = Math.min(num, 3);

    for (let i = 0; i < limitedNum; i++) {
        const input = document.createElement('input');
        input.type = 'file';
        input.name = `file${i}`;
        input.id = `file${i}`;
        input.required = true; // Make file inputs required
        fileInputsContainer.appendChild(input);
        fileInputsContainer.appendChild(document.createElement('br'));
    }
}

function validateNumberInput(event) {
    if (event.target.value < 0) {
        event.target.value = 0;
    } else if (event.target.value > 3) {
        event.target.value = 3;
    }
}

function validatePhoneNumber(phone) {
    const phonePattern = /^\d{10}$/;
    return phonePattern.test(phone);
}

document.querySelector('form').addEventListener('submit', function(event) {
    const faculty = document.getElementById('faculty').value;
    const designation = document.getElementById('designation').value;
    const department = document.getElementById('department').value;
    const phone = document.getElementById('phone').value;
    const email = document.getElementById('email').value;
    const num = document.getElementById('num').value;
    const validDomains = ['@nmamit.in', '@nitte.edu.in'];
    const isEmailValid = validDomains.some(domain => email.endsWith(domain));
    const isPhoneValid = validatePhoneNumber(phone);

    if (!faculty) {
        alert('Faculty name is required');
        event.preventDefault();
    } else if (!designation) {
        alert('Designation is required');
        event.preventDefault();
    } else if (!department) {
        alert('Department is required');
        event.preventDefault();
    } else if (!phone) {
        alert('Phone number is required');
        event.preventDefault();
    } else if (!isPhoneValid) {
        alert('Please enter a valid 10-digit phone number');
        event.preventDefault();
    } else if (!email) {
        alert('Email is required');
        event.preventDefault();
    } else if (!isEmailValid) {
        alert('Please enter a valid email with @nmamit.in or @nitte.edu.in');
        event.preventDefault();
    } else if (!num) {
        alert('Number of files for Plagcheck is required');
        event.preventDefault();
    }
});

document.querySelector('.custom-button').addEventListener('click', function() {
    // Add functionality for the new button here
    alert('New Button Clicked!');
});

