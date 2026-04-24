    // Isi dari file: script.js

document.addEventListener('DOMContentLoaded', function() {
    
    // LANGKAH 1: DAFTARKAN PLUGIN
    // Baris ini HARUS ada di sini di awal kode JavaScript Anda.
    // Jika Anda TIDAK meletakkan baris ini, angka tidak akan muncul.
    Chart.register(ChartDataLabels);

    // 1. Dapatkan konteks (context) canvas
    const ctx = document.getElementById('ratingChart').getContext('2d');
    
    // 2. Data Rating Cerita Rakyat
    const labels = [
        'Lutung Kasarung', 
        'Timun Mas', 
        'Keong Mas', 
        'To Dilaling'
    ];
    const dataRatings = [4.83, 4.71, 4.68, 4.52];

    // 3. Konfigurasi Diagram
    const ratingChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'Jumlah Rating',
                data: dataRatings,
                backgroundColor: 'rgba(88, 129, 87, 0.8)', 
                borderColor: 'rgba(88, 129, 87, 0.8)', 
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            plugins: {
                title: {
                    display: true,
                    text: 'Rating Tertinggi Cerita Rakyat Rakyat',
                    font: { size: 18, weight: 'bold' }
                },
                legend: {
                    display: true,
                    position: 'bottom'
                },
                // KONFIGURASI DATALABELS DI SINI
                datalabels: {
                    anchor: 'center', 
                    align: 'center', 
                    color: 'white', 
                    font: {
                        weight: 'bold' 
                    },
                    formatter: function(value) {
                        return value.toFixed(2); 
                    }
                },
                // Tooltip agar tetap tampil dua desimal
                tooltip: {
                    callbacks: {
                        label: function(context) {
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
                    min: 4, 
                    max: 5, 
                    ticks: {
                        stepSize: 0.2,
                        callback: function(value) { return value.toFixed(1); }
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