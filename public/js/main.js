// Password Visibility Toggle
function togglePasswordVisibility(btn) {
    const input = document.getElementById("password");
    const icon = btn.querySelector("i");

    if (input.type === "password") {
        input.type = "text";
        icon.classList.remove("fa-eye");
        icon.classList.add("fa-eye-slash");
    } else {
        input.type = "password";
        icon.classList.remove("fa-eye-slash");
        icon.classList.add("fa-eye");
    }
}

// Success Modal Auto Close
function closeSuccessModal() {
    const modal = document.getElementById("successModal");
    if (!modal) return;

    modal.classList.add("opacity-0");
    setTimeout(() => modal.remove(), 200);
}
setTimeout(() => {
    closeSuccessModal();
}, 2500);


// Chart
document.addEventListener("DOMContentLoaded", () => {
    const ctx = document.getElementById("expenseChart");

    // Only initialize chart if canvas exists
    if (ctx) {
        let expenseChart;

        const initChart = (labels, income, expense) => {
            if (expenseChart) {
                expenseChart.destroy();
            }

            expenseChart = new Chart(ctx, {
                type: "line",
                data: {
                    labels: labels,
                    datasets: [
                        {
                            label: "Pemasukan",
                            data: income,
                            borderColor: "#10b981",
                            backgroundColor: "rgba(16, 185, 129, 0.1)",
                            borderWidth: 2,
                            tension: 0,
                            pointRadius: 3,
                            pointHoverRadius: 6,
                            fill: true
                        },
                        {
                            label: "Pengeluaran",
                            data: expense,
                            borderColor: "#f43f5e",
                            backgroundColor: "rgba(244, 63, 94, 0.1)",
                            borderWidth: 2,
                            tension: 0,
                            pointRadius: 3,
                            pointHoverRadius: 6,
                            fill: true
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: true,
                            position: 'bottom',
                            labels: {
                                usePointStyle: true,
                                padding: 15,
                            }
                        },
                        tooltip: {
                            mode: 'index',
                            intersect: false,
                            callbacks: {
                                label: function (context) {
                                    return context.dataset.label + ': Rp ' + context.parsed.y.toLocaleString('id-ID');
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            grid: {
                                display: false
                            }
                        },
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: value => "Rp " + value.toLocaleString("id-ID"),
                            },
                            grid: {
                                color: '#f3f4f6'
                            }
                        }
                    },
                    interaction: {
                        mode: 'nearest',
                        axis: 'x',
                        intersect: false
                    }
                }
            });
        };

        // Initialize with default data
        if (typeof chartLabels !== 'undefined') {
            initChart(chartLabels, chartIncomeData, chartExpenseData);
        }

        // Filter Handler
        const btn = document.getElementById('filterBtn');
        const menu = document.getElementById('filterMenu');
        const text = document.getElementById('filterText');

        if (btn && menu && text) {
            btn.addEventListener('click', () => {
                menu.classList.toggle('hidden');
            });

            document.querySelectorAll('#filterMenu li').forEach(item => {
                item.addEventListener('click', () => {
                    const value = item.dataset.value;
                    text.textContent = item.textContent;
                    menu.classList.add('hidden');

                    // Update chart
                    updateChart(value);
                });
            });

            // Close menu when clicking outside
            document.addEventListener('click', (e) => {
                if (!btn.contains(e.target) && !menu.contains(e.target)) {
                    menu.classList.add('hidden');
                }
            });
        }

        // Function to update chart via AJAX
        function updateChart(filter) {
            fetch(`../src/php/api/get_chart_data.php?filter=${filter}`)
                .then(response => response.json())
                .then(data => {
                    initChart(data.labels, data.income, data.expense);
                })
                .catch(error => {
                    console.error('Error updating chart:', error);
                });
        }
    }


    // Modal handlers
    const openModalBtn = document.getElementById("openModal");
    const closeModalBtn = document.getElementById("closeModal");
    const modal = document.getElementById("transactionModal");

    if (openModalBtn && closeModalBtn && modal) {
        const addTransactionForm = modal.querySelector('form');

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

            modal.classList.remove("hidden");
            modal.classList.add("flex");
        };

        closeModalBtn.onclick = () => {
            modal.classList.add("hidden");
            modal.classList.remove("flex");
        };

        // Close modal when clicking outside
        modal.onclick = (e) => {
            if (e.target === modal) {
                modal.classList.add("hidden");
                modal.classList.remove("flex");
            }
        };

        // AJAX Submission
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

                // Button loading state
                const submitBtn = addTransactionForm.querySelector('button[type="submit"]');
                const originalText = submitBtn.textContent;
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin mr-2"></i>Adding...';

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
                            addTransactionForm.reset();
                            document.getElementById('nominalInput').value = '';
                            document.getElementById('nominalHidden').value = '';

                            setTimeout(() => {
                                modal.classList.add('hidden');
                                modal.classList.remove('flex');
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
                        submitBtn.disabled = false;
                        submitBtn.textContent = originalText;
                    });
            });
        }
    }

    // Currency formatting for nominal input
    const nominalInput = document.getElementById('nominalInput');
    const nominalHidden = document.getElementById('nominalHidden');
    if (nominalInput && nominalHidden) {
        // Format function
        function formatCurrency(input) {
            // Get only digits
            let value = input.value.replace(/\D/g, '');

            if (!value || value === '0') {
                input.value = '';
                nominalHidden.value = '';
                return;
            }
            // Convert to number and format
            const numberValue = parseInt(value, 10);
            const formatted = numberValue.toLocaleString('id-ID');

            // Update inputs
            input.value = 'Rp ' + formatted;
            nominalHidden.value = numberValue;
        }
        // On input event (typing, paste, etc)
        nominalInput.addEventListener('input', function (e) {
            formatCurrency(e.target);
        });
        // On focus, clear placeholder behavior
        nominalInput.addEventListener('focus', function (e) {
            if (e.target.value === '' || e.target.value === 'Rp 0') {
                e.target.value = '';
            }
        });
        // Prevent paste of non-numeric
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

    // Function to show messages in modal
    function showModalMessage(message, type) {
        const existingMsg = document.querySelector('.modal-message');
        if (existingMsg) existingMsg.remove();

        const modalContent = document.querySelector('#transactionModal .bg-white');
        const messageDiv = document.createElement('div');
        messageDiv.className = `modal-message p-3 rounded-lg mb-4 text-sm font-medium ${type === 'success' ? 'bg-emerald-100 text-emerald-700' : 'bg-rose-100 text-rose-700'
            }`;
        messageDiv.textContent = message;

        const title = modalContent.querySelector('h2');
        title.insertAdjacentElement('afterend', messageDiv);

        if (type === 'success') {
            setTimeout(() => messageDiv.remove(), 3000);
        }
    }
});