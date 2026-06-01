document.addEventListener('DOMContentLoaded', function () {
    Chart.register(ChartDataLabels);

    const ctx = document.getElementById('grafikMembaca').getContext('2d');

    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jum\'at', 'Sabtu', 'Minggu'],
            datasets: [{
                label: '2020',
                data: [3, 8, 6, 3, 3, 6, 5],
                backgroundColor: 'rgba(109, 74, 54, 0.85)',
                borderColor: 'rgba(109, 74, 54, 0.85)',
                borderWidth: 1,
                borderRadius: 3
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                title: {
                    display: true,
                    text: 'Jumlah Membaca Perhari',
                    font: { size: 14, weight: '600', family: 'Poppins' },
                    color: '#222',
                    padding: { bottom: 12 }
                },
                legend: { display: true, position: 'bottom' },
                datalabels: { display: false },
                tooltip: {
                    callbacks: {
                        label: function (context) {
                            return (context.dataset.label || '') + ': ' + context.parsed.y;
                        }
                    }
                }
            },
            scales: {
                y: {
                    min: 0,
                    max: 8,
                    ticks: { stepSize: 4 },
                    grid: { color: 'rgba(0,0,0,0.06)' }
                },
                x: { grid: { display: false } }
            }
        }
    });
});