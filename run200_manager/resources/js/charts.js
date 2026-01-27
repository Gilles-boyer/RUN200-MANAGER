import Chart from 'chart.js/auto';

// Configuration globale Racing Design System
Chart.defaults.color = '#9ca3af'; // carbon-400
Chart.defaults.borderColor = 'rgba(75, 85, 99, 0.3)'; // carbon-600/30
Chart.defaults.font.family = 'Inter, system-ui, sans-serif';

// Palette Racing
const racingColors = {
    primary: '#ef4444', // racing-red-500
    primaryLight: 'rgba(239, 68, 68, 0.2)',
    secondary: '#eab308', // checkered-yellow-500
    secondaryLight: 'rgba(234, 179, 8, 0.2)',
    success: '#22c55e', // status-success
    successLight: 'rgba(34, 197, 94, 0.2)',
    warning: '#f59e0b', // status-warning
    warningLight: 'rgba(245, 158, 11, 0.2)',
    danger: '#ef4444', // status-danger
    dangerLight: 'rgba(239, 68, 68, 0.2)',
    info: '#3b82f6', // status-info
    infoLight: 'rgba(59, 130, 246, 0.2)',
    carbon: {
        700: '#374151',
        800: '#1f2937',
        900: '#111827',
    }
};

// Palette pour graphiques multiples
const chartPalette = [
    '#ef4444', // racing-red
    '#eab308', // checkered-yellow
    '#3b82f6', // blue
    '#22c55e', // green
    '#a855f7', // purple
    '#f97316', // orange
    '#06b6d4', // cyan
    '#ec4899', // pink
];

// Configuration commune pour les line charts
const lineChartDefaults = {
    tension: 0.4,
    fill: true,
    pointRadius: 4,
    pointHoverRadius: 6,
};

// Initialisation des graphiques via Alpine.js
window.initChart = function(canvasId, type, data, options = {}) {
    const canvas = document.getElementById(canvasId);
    if (!canvas) return null;

    const ctx = canvas.getContext('2d');

    // Détruire le chart existant s'il y en a un
    if (window.chartInstances && window.chartInstances[canvasId]) {
        window.chartInstances[canvasId].destroy();
    }

    // Créer le nouveau chart
    const chart = new Chart(ctx, {
        type: type,
        data: data,
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        usePointStyle: true,
                        padding: 20,
                        color: '#9ca3af',
                    }
                },
                tooltip: {
                    backgroundColor: '#1f2937',
                    titleColor: '#fff',
                    bodyColor: '#d1d5db',
                    borderColor: 'rgba(239, 68, 68, 0.3)',
                    borderWidth: 1,
                    padding: 12,
                    cornerRadius: 8,
                    displayColors: true,
                }
            },
            scales: type === 'doughnut' || type === 'pie' ? {} : {
                x: {
                    grid: {
                        color: 'rgba(75, 85, 99, 0.2)',
                    },
                    ticks: {
                        color: '#9ca3af',
                    }
                },
                y: {
                    grid: {
                        color: 'rgba(75, 85, 99, 0.2)',
                    },
                    ticks: {
                        color: '#9ca3af',
                    },
                    beginAtZero: true,
                }
            },
            ...options
        }
    });

    // Stocker l'instance
    if (!window.chartInstances) {
        window.chartInstances = {};
    }
    window.chartInstances[canvasId] = chart;

    return chart;
};

// Helper pour créer un line chart d'évolution
window.createLineChart = function(canvasId, labels, datasets) {
    const data = {
        labels: labels,
        datasets: datasets.map((ds, index) => ({
            ...lineChartDefaults,
            label: ds.label,
            data: ds.data,
            borderColor: ds.color || chartPalette[index % chartPalette.length],
            backgroundColor: ds.backgroundColor || `${chartPalette[index % chartPalette.length]}33`,
            ...ds.options
        }))
    };

    return window.initChart(canvasId, 'line', data);
};

// Helper pour créer un doughnut chart
window.createDoughnutChart = function(canvasId, labels, data, colors = null) {
    const chartData = {
        labels: labels,
        datasets: [{
            data: data,
            backgroundColor: colors || chartPalette.slice(0, data.length),
            borderColor: '#1f2937',
            borderWidth: 2,
            hoverOffset: 4,
        }]
    };

    return window.initChart(canvasId, 'doughnut', chartData, {
        cutout: '60%',
        plugins: {
            legend: {
                position: 'right',
            }
        }
    });
};

// Helper pour créer un bar chart
window.createBarChart = function(canvasId, labels, datasets) {
    const data = {
        labels: labels,
        datasets: datasets.map((ds, index) => ({
            label: ds.label,
            data: ds.data,
            backgroundColor: ds.color || chartPalette[index % chartPalette.length],
            borderColor: ds.borderColor || chartPalette[index % chartPalette.length],
            borderWidth: 1,
            borderRadius: 4,
            ...ds.options
        }))
    };

    return window.initChart(canvasId, 'bar', data);
};

// Helper pour créer un bar chart horizontal
window.createHorizontalBarChart = function(canvasId, labels, data, color = null) {
    const chartData = {
        labels: labels,
        datasets: [{
            data: data,
            backgroundColor: color || racingColors.primary,
            borderColor: color || racingColors.primary,
            borderWidth: 1,
            borderRadius: 4,
        }]
    };

    return window.initChart(canvasId, 'bar', chartData, {
        indexAxis: 'y',
        plugins: {
            legend: {
                display: false,
            }
        }
    });
};

// Export pour utilisation globale
window.racingColors = racingColors;
window.chartPalette = chartPalette;

console.log('Racing Charts initialized 🏁');
