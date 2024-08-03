document.addEventListener('DOMContentLoaded', function() {
    const authToken = localStorage.getItem('authToken');

    if (authToken) {
        fetch('php/getProfile.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ token: authToken })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('username-display').textContent = `Welcome, ${data.username}`;
                document.getElementById('username').value = data.username; // Set username in hidden input
                if (data.profile) {
                    document.getElementById('gender').value = data.profile.gender || '';
                    document.getElementById('dob').value = data.profile.dob || '';
                    document.getElementById('age').value = data.profile.age || '';
                    document.getElementById('contact').value = data.profile.contact || '';
                } else {
                    document.getElementById('first-time-message').style.display = 'block';
                }
            } else {
                throw new Error(data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error fetching profile. Please try again.');
        });
    } else {
        alert('User not logged in. Redirecting to login page.');
        window.location.href = 'login.html';
    }

    document.getElementById('logout-btn').addEventListener('click', function() {
        localStorage.removeItem('authToken');
        alert('You have been logged out.');
        window.location.href = 'login.html';
    });

    document.getElementById('profileForm').addEventListener('submit', function(event) {
        event.preventDefault();

        const formData = new FormData(this);
        formData.append('token', authToken);

        fetch('php/updateProfile.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Profile updated successfully.');
            } else {
                alert('Failed to update profile: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while updating the profile.');
        });
    });

    // Automatically calculate age based on DOB
    document.getElementById('dob').addEventListener('change', function() {
        const dob = new Date(this.value);
        const today = new Date();
        let age = today.getFullYear() - dob.getFullYear();
        const monthDiff = today.getMonth() - dob.getMonth();
        if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < dob.getDate())) {
            age--;
        }
        document.getElementById('age').value = age;
    });
});
