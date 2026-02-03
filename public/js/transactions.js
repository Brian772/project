// Currency formatting for nominal inputs
function setupCurrencyFormat(inputId, hiddenId) {
    const nominalInput = document.getElementById(inputId);
    const nominalHidden = document.getElementById(hiddenId);

    if (nominalInput && nominalHidden) {
        function formatCurrency(input) {
            let value = input.value.replace(/\D/g, '');

            if (!value || value === '0') {
                input.value = '';
                nominalHidden.value = '';
                return;
            }

            const numberValue = parseInt(value, 10);
            const formatted = numberValue.toLocaleString('id-ID');

            input.value = 'Rp ' + formatted;
            nominalHidden.value = numberValue;
        }

        nominalInput.addEventListener('input', function (e) {
            formatCurrency(e.target);
        });

        nominalInput.addEventListener('focus', function (e) {
            if (e.target.value === '' || e.target.value === 'Rp 0') {
                e.target.value = '';
            }
        });

        nominalInput.addEventListener('paste', function (e) {
            e.preventDefault();
            const pastedText = (e.clipboardData || window.clipboardData).getData('text');
            const onlyNumbers = pastedText.replace(/\D/g, '');
            if (onlyNumbers) {
                e.target.value = onlyNumbers;
                formatCurrency(e.target);
            }
        });
    }
}

// Setup currency format for add modal
setupCurrencyFormat('nominalInput', 'nominalHidden');
setupCurrencyFormat('editNominalInput', 'editNominalHidden');

// Add Transaction Modal Handlers for Transactions Page
const addTransactionForm = document.querySelector('#transactionModal form');
if (addTransactionForm) {
    addTransactionForm.addEventListener('submit', function (e) {
        e.preventDefault();

        // Client-side validation
        const nominal = document.getElementById('nominalHidden').value;
        const tipe = addTransactionForm.querySelector('input[name="tipe"]:checked');
        const kategori = addTransactionForm.querySelector('input[name="kategori"]').value.trim();
        const aset = addTransactionForm.querySelector('input[name="aset"]').value.trim();

        if (!nominal || nominal === '0') {
            showModalMessage('Please enter a valid amount', 'error');
            return;
        }

        if (!tipe) {
            showModalMessage('Please select transaction type', 'error');
            return;
        }

        if (!kategori) {
            showModalMessage('Please enter a category', 'error');
            return;
        }

        if (!aset) {
            showModalMessage('Please enter an asset', 'error');
            return;
        }

        // Show loading state
        const submitBtn = addTransactionForm.querySelector('button[type="submit"]');
        const originalText = submitBtn.textContent;
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin mr-2"></i>Adding...';

        // Prepare form data
        const formData = new FormData(this);
        formData.append('ajax', '1');

        fetch(this.action, {
            method: 'POST',
            body: formData,
            headers: {
                'Accept': 'application/json'
            }
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showModalMessage('Transaction added successfully!', 'success');
                    // Reset form
                    addTransactionForm.reset();
                    document.getElementById('nominalInput').value = '';
                    document.getElementById('nominalHidden').value = '';
                    // Close modal after delay
                    setTimeout(() => {
                        modal.classList.add('hidden');
                        modal.classList.remove('flex');
                        // Reload page to show new transaction
                        window.location.reload();
                    }, 1500);
                } else {
                    showModalMessage(data.message || 'Failed to add transaction', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showModalMessage('An error occurred while adding transaction', 'error');
            })
            .finally(() => {
                // Reset button state
                submitBtn.disabled = false;
                submitBtn.textContent = originalText;
            });
    });
}

// Function to show messages in modal
function showModalMessage(message, type) {
    // Remove existing message
    const existingMsg = document.querySelector('.modal-message');
    if (existingMsg) existingMsg.remove();

    const modalContent = document.querySelector('#transactionModal .bg-white');
    const messageDiv = document.createElement('div');
    messageDiv.className = `modal-message p-3 rounded-lg mb-4 text-sm font-medium ${type === 'success' ? 'bg-emerald-100 text-emerald-700' : 'bg-rose-100 text-rose-700'
        }`;
    messageDiv.textContent = message;

    // Insert after title
    const title = modalContent.querySelector('h2');
    title.insertAdjacentElement('afterend', messageDiv);

    // Auto remove after 3 seconds for success
    if (type === 'success') {
        setTimeout(() => messageDiv.remove(), 3000);
    }
}

// Modal handlers for Add Transaction
const openModalBtn = document.getElementById('openModal');
const closeModalBtn = document.getElementById('closeModal');
const modal = document.getElementById('transactionModal');

if (openModalBtn && closeModalBtn && modal) {
    openModalBtn.onclick = () => {
        // Set default date to now
        const now = new Date();
        const year = now.getFullYear();
        const month = String(now.getMonth() + 1).padStart(2, '0');
        const day = String(now.getDate()).padStart(2, '0');
        const hours = String(now.getHours()).padStart(2, '0');
        const minutes = String(now.getMinutes()).padStart(2, '0');

        const dateInput = modal.querySelector('input[name="tanggal"]');
        if (dateInput) {
            dateInput.value = `${year}-${month}-${day}T${hours}:${minutes}`;
        }

        modal.classList.remove('hidden');
        modal.classList.add('flex');
    };

    closeModalBtn.onclick = () => {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    };

    modal.onclick = (e) => {
        if (e.target === modal) {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }
    };
}

// Modal handlers for Edit Transaction
const editModal = document.getElementById('editModal');
const closeEditModalBtn = document.getElementById('closeEditModal');
const editForm = document.getElementById('editForm');

if (closeEditModalBtn && editModal) {
    closeEditModalBtn.onclick = () => {
        editModal.classList.add('hidden');
        editModal.classList.remove('flex');
    };

    editModal.onclick = (e) => {
        if (e.target === editModal) {
            editModal.classList.add('hidden');
            editModal.classList.remove('flex');
        }
    };
}

// Handle Edit Form Submission via AJAX
if (editForm) {
    editForm.addEventListener('submit', function (e) {
        e.preventDefault();

        const formData = new FormData(this);
        formData.append('ajax', '1'); // Flag for PHP to return JSON

        fetch(this.action, {
            method: 'POST',
            body: formData,
            headers: {
                'Accept': 'application/json'
            }
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    window.location.reload();
                } else {
                    alert(data.message || 'Failed to update transaction');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while updating');
            });
    });
}

// Edit transaction function
function editTransaction(id) {
    // Fetch transaction data via AJAX
    fetch(`../src/php/transactions/get.php?id=${id}`)
        .then(async response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            const text = await response.text();
            try {
                return JSON.parse(text);
            } catch (e) {
                console.error('Server response:', text);
                throw new Error('Invalid JSON response from server');
            }
        })
        .then(data => {
            if (data.error) {
                alert(data.error);
                return;
            }

            // Populate form
            document.getElementById('editId').value = data.id;

            // Set radio button for type
            if (data.tipe === 'masuk') {
                document.getElementById('editTipeMasuk').checked = true;
            } else {
                document.getElementById('editTipeKeluar').checked = true;
            }

            document.getElementById('editKategori').value = data.kategori || '';
            document.getElementById('editAset').value = data.aset || '';
            document.getElementById('editKet').value = data.ket;

            // Format tanggal untuk datetime-local input
            if (data.tanggal) {
                const date = new Date(data.tanggal);
                const year = date.getFullYear();
                const month = String(date.getMonth() + 1).padStart(2, '0');
                const day = String(date.getDate()).padStart(2, '0');
                const hours = String(date.getHours()).padStart(2, '0');
                const minutes = String(date.getMinutes()).padStart(2, '0');
                document.getElementById('editTanggal').value = `${year}-${month}-${day}T${hours}:${minutes}`;
            }

            // Format nominal
            const editNominalInput = document.getElementById('editNominalInput');
            const editNominalHidden = document.getElementById('editNominalHidden');
            if (data.nominal) {
                const formattedNominal = parseInt(data.nominal).toLocaleString('id-ID');
                editNominalInput.value = 'Rp ' + formattedNominal;
                editNominalHidden.value = data.nominal;
            }

            // Show modal
            editModal.classList.remove('hidden');
            editModal.classList.add('flex');
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Failed to load transaction data: ' + error.message);
        });
}

// Custom Delete Modal Logic
let transactionIdToDelete = null;
const deleteModal = document.getElementById('deleteModal');
const deleteModalContent = document.getElementById('deleteModalContent');
const confirmDeleteBtn = document.getElementById('confirmDelete');
const cancelDeleteBtn = document.getElementById('cancelDelete');

function showDeleteModal(id) {
    transactionIdToDelete = id;
    if (deleteModal && deleteModalContent) {
        deleteModal.classList.remove('hidden');
        // Small delay to allow display:block to apply before opacity transition
        setTimeout(() => {
            deleteModal.classList.remove('opacity-0');
            deleteModalContent.classList.remove('scale-95');
            deleteModalContent.classList.add('scale-100');
        }, 10);
    }
}

function hideDeleteModal() {
    transactionIdToDelete = null;
    if (deleteModal && deleteModalContent) {
        deleteModal.classList.add('opacity-0');
        deleteModalContent.classList.remove('scale-100');
        deleteModalContent.classList.add('scale-95');
        setTimeout(() => {
            deleteModal.classList.add('hidden');
        }, 300);
    }
}

if (confirmDeleteBtn) {
    confirmDeleteBtn.onclick = () => {
        if (transactionIdToDelete) {
            // Create a form and submit
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '../src/php/transactions/delete.php';

            const inputId = document.createElement('input');
            inputId.type = 'hidden';
            inputId.name = 'id';
            inputId.value = transactionIdToDelete;

            form.appendChild(inputId);
            document.body.appendChild(form);
            form.submit();
        }
    };
}

if (cancelDeleteBtn) {
    cancelDeleteBtn.onclick = () => {
        hideDeleteModal();
    };
}

// Close on background click
if (deleteModal) {
    deleteModal.onclick = (e) => {
        if (e.target === deleteModal) {
            hideDeleteModal();
        }
    };
}

function deleteTransaction(id) {
    showDeleteModal(id);
}
