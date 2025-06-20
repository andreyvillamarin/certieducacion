// admin/js/students.js (Versión Final y Funcional)
document.addEventListener('DOMContentLoaded', function() {
    
    function handleAjaxForm(formId, url) {
        const form = document.getElementById(formId);
        if (form) {
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                const formData = new FormData(this);
                fetch(url, { method: 'POST', body: formData })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        window.location.reload();
                    } else { alert('Error: ' + data.message); }
                })
                .catch(error => console.error('Error:', error));
            });
        }
    }

    handleAjaxForm('addStudentForm', 'ajax_student_handler.php');
    handleAjaxForm('editStudentForm', 'ajax_student_handler.php');
    handleAjaxForm('uploadCsvForm', 'ajax_student_handler.php');

    const editStudentModalEl = document.getElementById('editStudentModal');
    if (editStudentModalEl) {
        editStudentModalEl.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            const studentId = button.dataset.id;
            
            const formData = new FormData();
            formData.append('action', 'get_student');
            formData.append('id', studentId);

            fetch('ajax_student_handler.php', { method: 'POST', body: formData })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        editStudentModalEl.querySelector('#edit_student_id').value = data.data.id;
                        editStudentModalEl.querySelector('#edit_name').value = data.data.name;
                        editStudentModalEl.querySelector('#edit_identification').value = data.data.identification;
                        editStudentModalEl.querySelector('#edit_phone').value = data.data.phone;
                        editStudentModalEl.querySelector('#edit_email').value = data.data.email;
                    }
                });
        });
    }

    const deleteConfirmModalEl = document.getElementById('deleteConfirmModal');
    if (deleteConfirmModalEl) {
        deleteConfirmModalEl.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            deleteConfirmModalEl.querySelector('#student_id_to_delete').value = button.dataset.id;
            deleteConfirmModalEl.querySelector('#student-name-to-delete').textContent = button.dataset.name;
        });
    }
});