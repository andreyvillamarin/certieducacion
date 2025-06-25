console.log('certificates.js loaded');

document.addEventListener('DOMContentLoaded', function () {
    console.log('Certificates DOMContentLoaded');

    const studentSearchInput = document.getElementById('studentSearchInput');
    const availableStudentsListContainer = document.getElementById('availableStudentsListContainer');
    const selectedStudentsListContainer = document.getElementById('selectedStudentsListContainer');
    const selectedStudentIdsFormContainer = document.getElementById('selectedStudentIdsFormContainer');
    
    const availableCountDisplay = document.getElementById('availableCountDisplay');
    const selectedCountDisplay = document.getElementById('selectedCountDisplay');
    const noSelectedStudentsMessage = document.getElementById('noSelectedStudentsMessage');
    const clearSelectionBtn = document.getElementById('clearSelectionBtn');
    const generateButtonStudentCount = document.getElementById('generateButtonStudentCount');
    const generateCertForm = document.getElementById('generateCertForm');
    const btnGenerate = document.getElementById('btn-generate');

    // Almacenar todos los estudiantes originales para el filtrado y para restaurarlos
    let allAvailableStudentItems = Array.from(availableStudentsListContainer.querySelectorAll('.available-student-item'));
    let selectedStudentIds = new Set(); // Usar un Set para evitar duplicados y facilitar la búsqueda

    function updateCounts() {
        const visibleAvailableCount = Array.from(availableStudentsListContainer.querySelectorAll('.available-student-item:not(.d-none):not(.selected-item)')).length;
        availableCountDisplay.textContent = visibleAvailableCount;
        
        const currentSelectedCount = selectedStudentIds.size;
        selectedCountDisplay.textContent = currentSelectedCount;
        generateButtonStudentCount.textContent = currentSelectedCount;
        document.querySelectorAll('.selected-count-btn').forEach(el => el.textContent = currentSelectedCount);

        if (currentSelectedCount > 0) {
            noSelectedStudentsMessage.classList.add('d-none');
        } else {
            noSelectedStudentsMessage.classList.remove('d-none');
        }
        // Habilitar/deshabilitar botón de generar si no hay estudiantes seleccionados
        btnGenerate.disabled = currentSelectedCount === 0;
    }

    function renderSelectedStudents() {
        selectedStudentsListContainer.innerHTML = ''; // Limpiar lista actual
        selectedStudentIdsFormContainer.innerHTML = ''; // Limpiar inputs hidden

        if (selectedStudentIds.size === 0) {
            selectedStudentsListContainer.appendChild(noSelectedStudentsMessage);
        } else {
            allAvailableStudentItems.forEach(item => {
                const studentId = item.dataset.id;
                if (selectedStudentIds.has(studentId)) {
                    const studentName = item.dataset.name;
                    const studentIdentification = item.dataset.identification;

                    // Crear elemento para la lista de seleccionados
                    const selectedItem = document.createElement('div');
                    selectedItem.className = 'list-group-item list-group-item-sm d-flex justify-content-between align-items-center selected-student-display-item';
                    selectedItem.innerHTML = `
                        <span>${studentName} <small class="text-muted">(${studentIdentification})</small></span>
                        <button type="button" class="btn btn-xs btn-outline-danger remove-selected-btn" data-id="${studentId}" title="Quitar">
                            <i class="fas fa-times"></i>
                        </button>
                    `;
                    selectedStudentsListContainer.appendChild(selectedItem);

                    // Crear input hidden para el formulario
                    const hiddenInput = document.createElement('input');
                    hiddenInput.type = 'hidden';
                    hiddenInput.name = 'student_ids[]';
                    hiddenInput.value = studentId;
                    selectedStudentIdsFormContainer.appendChild(hiddenInput);
                }
            });
        }
        updateCounts();
    }

    function markAvailableStudentsAsSelected() {
        allAvailableStudentItems.forEach(item => {
            if (selectedStudentIds.has(item.dataset.id)) {
                item.classList.add('selected-item', 'disabled'); // 'disabled' y 'selected-item' para Bootstrap
                item.style.backgroundColor = '#e9ecef'; // Un color de fondo para indicar selección
            } else {
                item.classList.remove('selected-item', 'disabled');
                item.style.backgroundColor = '';
            }
        });
    }

    // Event Listener para la búsqueda de estudiantes
    if (studentSearchInput) {
        studentSearchInput.addEventListener('keyup', function () {
            const searchTerm = this.value.toLowerCase();
            allAvailableStudentItems.forEach(item => {
                const studentName = item.dataset.name.toLowerCase();
                const studentIdentification = item.dataset.identification.toLowerCase();
                if (studentName.includes(searchTerm) || studentIdentification.includes(searchTerm)) {
                    item.classList.remove('d-none');
                } else {
                    item.classList.add('d-none');
                }
            });
            updateCounts(); // Actualizar contador de disponibles visibles
        });
    }

    // Event Listener para seleccionar un estudiante de la lista de disponibles
    if (availableStudentsListContainer) {
        availableStudentsListContainer.addEventListener('click', function (e) {
            e.preventDefault();
            const targetItem = e.target.closest('.available-student-item');
            if (targetItem && !targetItem.classList.contains('selected-item')) {
                const studentId = targetItem.dataset.id;
                selectedStudentIds.add(studentId);
                markAvailableStudentsAsSelected();
                renderSelectedStudents();
            }
        });
    }

    // Event Listener para quitar un estudiante de la lista de seleccionados
    if (selectedStudentsListContainer) {
        selectedStudentsListContainer.addEventListener('click', function(e) {
            const removeButton = e.target.closest('.remove-selected-btn');
            if (removeButton) {
                const studentId = removeButton.dataset.id;
                selectedStudentIds.delete(studentId);
                markAvailableStudentsAsSelected();
                renderSelectedStudents();
            }
        });
    }

    // Event Listener para el botón de limpiar selección
    if (clearSelectionBtn) {
        clearSelectionBtn.addEventListener('click', function() {
            selectedStudentIds.clear();
            markAvailableStudentsAsSelected();
            renderSelectedStudents();
        });
    }
    
    // Validación antes de enviar el formulario
    if (generateCertForm) {
        generateCertForm.addEventListener('submit', function(e) {
            if (selectedStudentIds.size === 0) {
                e.preventDefault();
                alert('Debe seleccionar al menos un estudiante para generar certificados.');
                return false;
            }
            // Mostrar spinner en el botón de generar
            if(btnGenerate){
                const spinner = btnGenerate.querySelector('.spinner-border');
                if(spinner) spinner.classList.remove('d-none');
                btnGenerate.disabled = true;
            }
        });
    }

    // Inicializar contadores y estado visual
    markAvailableStudentsAsSelected();
    renderSelectedStudents(); // Para asegurar que la lista de seleccionados esté vacía inicialmente
    updateCounts(); // Llamada inicial para los contadores

    console.log('Certificates JS initialized');
});
