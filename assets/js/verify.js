document.addEventListener('DOMContentLoaded', function() {
    const formVerifyCode = document.getElementById('form-verify-code');
    const alertMessage = document.getElementById('alert-message');
    const btnVerifyCode = document.getElementById('btn-verify-code');
    
    // Funciones auxiliares (podrían moverse a un archivo global si se repiten mucho)
    function showAlert(message, type = 'danger') {
        alertMessage.innerHTML = message;
        alertMessage.className = `alert alert-${type} mt-3`;
        alertMessage.classList.remove('d-none');
    }

    function hideAlert() {
        alertMessage.classList.add('d-none');
    }

    function toggleSpinner(button, show) {
        const spinner = button.querySelector('.spinner-border');
        if (show) {
            button.disabled = true;
            spinner.classList.remove('d-none');
        } else {
            button.disabled = false;
            spinner.classList.add('d-none');
        }
    }


    formVerifyCode.addEventListener('submit', function(e) {
        e.preventDefault();
        hideAlert();
        toggleSpinner(btnVerifyCode, true);
        
        const formData = new FormData(formVerifyCode);
        formData.append('action', 'verify_code'); // La nueva acción que crearemos

        fetch('ajax_handler.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // ¡Éxito! Redirigir al dashboard del estudiante
                showAlert('Verificación exitosa. Redirigiendo...', 'success');
                window.location.href = data.redirect_url;
            } else {
                showAlert(data.message || 'El código ingresado es incorrecto.');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('Ocurrió un error de comunicación. Inténtalo de nuevo.');
        })
        .finally(() => {
            toggleSpinner(btnVerifyCode, false);
        });
    });
});
