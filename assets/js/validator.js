document.addEventListener('DOMContentLoaded', function() {
    const formValidate = document.getElementById('form-validate-code');
    const btnValidate = document.getElementById('btn-validate');
    const validationCodeInput = document.getElementById('validation_code');
    const resultDiv = document.getElementById('validation-result');
    const btnScanQr = document.getElementById('btn-scan-qr');
    const qrReaderDiv = document.getElementById('qr-reader');

    let html5QrCode = null;

    /**
     * Activa o desactiva el spinner en un botón.
     */
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
    
    /**
     * Muestra el resultado de la validación en la UI.
     * @param {object} data - Los datos de la respuesta del servidor.
     */
    function displayResult(data) {
        let resultHTML = '';
        if (data.success && data.certificate) {
            const cert = data.certificate;
            resultHTML = `
                <div class="alert alert-success">
                    <h5 class="alert-heading"><i class="fas fa-check-circle"></i> Certificado Auténtico</h5>
                    <hr>
                    <p class="mb-1"><strong>Estudiante:</strong> ${cert.student_name}</p>
                    <p class="mb-1"><strong>Identificación:</strong> ${cert.student_id}</p>
                    <p class="mb-1"><strong>Curso:</strong> ${cert.course_name}</p>
                    <p class="mb-0"><strong>Fecha de Emisión:</strong> ${cert.issue_date}</p>
                </div>`;
        } else {
            resultHTML = `
                <div class="alert alert-danger">
                    <h5 class="alert-heading"><i class="fas fa-times-circle"></i> Certificado No Válido</h5>
                    <p class="mb-0">${data.message || 'El código ingresado no corresponde a ningún certificado emitido por nuestra institución.'}</p>
                </div>`;
        }
        resultDiv.innerHTML = resultHTML;
    }

    /**
     * Realiza la llamada AJAX para validar el código.
     * @param {string} code - El código de validación a enviar.
     */
    function validateCode(code) {
        toggleSpinner(btnValidate, true);
        resultDiv.innerHTML = '';

        const formData = new FormData();
        formData.append('action', 'validate_certificate_code');
        formData.append('validation_code', code);

        fetch('ajax_handler.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            displayResult(data);
            if (html5QrCode && html5QrCode.isScanning) {
                html5QrCode.stop();
            }
            qrReaderDiv.style.display = 'none';
        })
        .catch(error => {
            console.error('Error:', error);
            displayResult({ success: false, message: 'Ocurrió un error de comunicación.' });
        })
        .finally(() => {
            toggleSpinner(btnValidate, false);
        });
    }

    // Manejar el envío del formulario
    formValidate.addEventListener('submit', function(e) {
        e.preventDefault();
        const code = validationCodeInput.value.trim();
        if (code) {
            validateCode(code);
        }
    });

    // Manejar el botón de escanear QR
    btnScanQr.addEventListener('click', () => {
        qrReaderDiv.style.display = 'block';
        resultDiv.innerHTML = '';
        
        if (!html5QrCode) {
            html5QrCode = new Html5Qrcode("qr-reader");
        }
        
        if (html5QrCode.isScanning) {
            html5QrCode.stop();
        }

        const qrCodeSuccessCallback = (decodedText, decodedResult) => {
            validationCodeInput.value = decodedText;
            validateCode(decodedText);
        };
        const config = { fps: 10, qrbox: { width: 250, height: 250 } };

        html5QrCode.start({ facingMode: "environment" }, config, qrCodeSuccessCallback)
            .catch(err => {
                console.error("No se pudo iniciar el escáner QR", err);
                resultDiv.innerHTML = `<div class="alert alert-warning">No se pudo iniciar el escáner. Asegúrate de dar permisos a la cámara.</div>`;
            });
    });
});
