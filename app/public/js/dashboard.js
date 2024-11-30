document.addEventListener("DOMContentLoaded", function() {
    const ctx = document.getElementById("expensesPieChart").getContext("2d");

    // Récupérer les données des transactions
    const transactions = JSON.parse(document.getElementById('transactions-data').textContent);
    const categories = {};

    transactions.forEach(function(transaction) {
        if (transaction.type === 'expense') {
            categories[transaction.category.id] = categories[transaction.category.id] || { label: transaction.category.name, total: 0 };
            categories[transaction.category.id].total += Math.abs(transaction.amount);
        }
    });

    const labels = Object.values(categories).map(item => item.label);
    const data = Object.values(categories).map(item => item.total);

    new Chart(ctx, {
        type: "pie",
        data: {
            labels: labels,
            datasets: [{
                data: data,
                backgroundColor: [
                    "rgba(255, 99, 132, 0.6)",
                    "rgba(54, 162, 235, 0.6)",
                    "rgba(255, 206, 86, 0.6)",
                    "rgba(75, 192, 192, 0.6)",
                    "rgba(153, 102, 255, 0.6)",
                    "rgba(255, 159, 64, 0.6)"
                ],
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'top',
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            let label = context.label || '';
                            if (label) {
                                label += ': ';
                            }
                            label += context.raw.toLocaleString('fr-FR', { style: 'currency', currency: 'EUR' });
                            return label;
                        }
                    }
                }
            }
        }
    });
});