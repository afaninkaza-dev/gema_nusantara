document.addEventListener('DOMContentLoaded', function () {

    // LANGKAH 1: DAFTARKAN PLUGIN
    // Baris ini HARUS ada di sini di awal kode JavaScript Anda.
    // Jika Anda TIDAK meletakkan baris ini, angka tidak akan muncul.
    Chart.register(ChartDataLabels);

    // 1. Dapatkan konteks (context) canvas
    const ctx = document.getElementById('grafikMembaca').getContext('2d');

    // 2. Data Rating Cerita Rakyat
    const labels = [
        'Senin',
        'Selasa',
        'Rabu',
        'Kamis',
        'Jumat',
        'Sabtu',
        'Minggu'
    ];
    const dataRatings = [3, 8, 6, 3, 3, 6, 5];

    // 3. Konfigurasi Diagram
    const ratingChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'Jumlah Cerita Yang Dibaca',
                data: dataRatings,
                backgroundColor: 'rgba(109, 74, 54, 0.9)',
                borderColor: 'rgba(109, 74, 54, 0.9)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            plugins: {
                title: {
                    display: true,
                    text: 'Jumlah Membaca Perhari',
                    font: { size: 18, weight: 'bold' }
                },
                legend: {
                    display: true,
                    position: 'bottom'
                },
                // KONFIGURASI DATALABELS DI SINI
                datalabels: {
                    anchor: 'center',
                    display: 'false',
                    align: 'center',
                    color: 'white',
                    font: {
                        weight: 'bold'
                    },
                    formatter: function (value) {
                        return value.toFixed(2);
                    }
                },
                // Tooltip agar tetap tampil dua desimal
                tooltip: {
                    callbacks: {
                        label: function (context) {
                            let label = context.dataset.label || '';
                            if (label) {
                                label += ': ';
                            }
                            if (context.parsed.y !== null) {
                                label += new Intl.NumberFormat('id-ID', { minimumFractionDigits: 2 }).format(context.parsed.y);
                            }
                            return label;
                        }
                    }
                }
            },
            scales: {
                y: {
                    min: 0,
                    max: 8,
                    ticks: {
                        stepSize: 4,
                        callback: function (value) { return value.toFixed(1); }
                    },
                    title: { display: false }
                },
                x: {
                    grid: { display: false }
                }
            }
        }
    });
});
