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

// Modal handlers for Add Transaction
const openModalBtn = document.getElementById('openModal');
const closeModalBtn = document.getElementById('closeModal');
const modal = document.getElementById('transactionModal');

if (openModalBtn && closeModalBtn && modal) {
    openModalBtn.onclick = () => {
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

// Edit transaction function
function editTransaction(id) {
    // Fetch transaction data via AJAX
    fetch(`../src/php/transactions/get.php?id=${id}`)
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                alert('Transaction not found');
                return;
            }

            // Populate form
            document.getElementById('editId').value = data.id;
            document.getElementById('editTipe').value = data.tipe;
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
            alert('Failed to load transaction data');
        });
}

// Delete transaction function
function deleteTransaction(id) {
    if (confirm('Are you sure you want to delete this transaction?')) {
        // Create a form and submit
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '../src/php/transactions/delete.php';

        const inputId = document.createElement('input');
        inputId.type = 'hidden';
        inputId.name = 'id';
        inputId.value = id;

        form.appendChild(inputId);
        document.body.appendChild(form);
        form.submit();
    }
}
