function handleCredentialResponse(response) {
    const id_token = response.credential;
    fetch('php/google_verify.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'id_token=' + id_token
    })
    .then(response => {
        // Check if the response is okay before parsing JSON
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        return response.json();
    })
    .then(data => {
        console.log(data);
        if (data.success) {
            window.location.href = "./main-page.html";
        } else {
            // Display the specific error message from the PHP script
            alert(data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        // Fallback to a generic error if the network request itself fails
        alert('A network error occurred. Please try again.');
    });
}
