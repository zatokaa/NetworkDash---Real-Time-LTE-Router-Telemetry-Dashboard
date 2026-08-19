import Chart from 'chart.js/auto';
import { createIcons, icons } from 'lucide';
import Sortable from 'sortablejs';

window.Chart = Chart;
window.createIcons = createIcons;
window.lucideIcons = icons;
window.Sortable = Sortable;

// Global Chart instances map
window.signalChartInstances = {};

/**
 * Initialize Drag and Drop for Dashboard Bento Grid
 */
window.initBentoSortable = function(containerId, onEndCallback) {
    const el = document.getElementById(containerId);
    if (!el) return null;

    if (el._sortableInstance) {
        el._sortableInstance.destroy();
    }

    el._sortableInstance = new Sortable(el, {
        animation: 250,
        handle: '.bento-drag-handle',
        ghostClass: 'opacity-30',
        chosenClass: 'scale-[1.01]',
        dragClass: 'shadow-2xl',
        onEnd: function() {
            const order = Array.from(el.children)
                .map(child => child.getAttribute('data-bento-id'))
                .filter(Boolean);
            if (typeof onEndCallback === 'function') {
                onEndCallback(order);
            }
        }
    });

    return el._sortableInstance;
};

/**
 * Render or update lightweight Bento telemetry signal chart (Single metric or All-in-One combo)
 */
window.renderSignalChart = function(canvasId, labels, dataPayload, metricName = 'all') {
    const canvas = document.getElementById(canvasId);
    if (!canvas) return;

    const ctx = canvas.getContext('2d');
    if (!ctx) return;

    // Destroy existing instance if present
    if (window.signalChartInstances[canvasId]) {
        window.signalChartInstances[canvasId].destroy();
    }

    const metricConfigs = {
        rsrp: {
            label: 'RSRP (Signal Strength)',
            lineColor: '#F2C94C',
            gradientStart: 'rgba(242, 201, 76, 0.35)',
            gradientEnd: 'rgba(242, 201, 76, 0.0)',
            unit: 'dBm',
            yAxisID: 'y'
        },
        rssi: {
            label: 'RSSI (Total Power)',
            lineColor: '#38BDF8',
            gradientStart: 'rgba(56, 189, 248, 0.35)',
            gradientEnd: 'rgba(56, 189, 248, 0.0)',
            unit: 'dBm',
            yAxisID: 'y'
        },
        rsrq: {
            label: 'RSRQ (Signal Quality)',
            lineColor: '#A78BFA',
            gradientStart: 'rgba(167, 139, 250, 0.35)',
            gradientEnd: 'rgba(167, 139, 250, 0.0)',
            unit: 'dB',
            yAxisID: 'y1'
        },
        sinr: {
            label: 'SINR (Signal-to-Noise)',
            lineColor: '#34D399',
            gradientStart: 'rgba(52, 211, 153, 0.35)',
            gradientEnd: 'rgba(52, 211, 153, 0.0)',
            unit: 'dB',
            yAxisID: 'y1'
        }
    };

    let datasets = [];
    let scales = {};
    let showLegend = false;

    if (metricName === 'all' && dataPayload.series) {
        showLegend = true;
        const s = dataPayload.series;

        datasets = [
            {
                label: 'RSRP (dBm)',
                data: s.rsrp || [],
                borderColor: '#F2C94C',
                backgroundColor: 'rgba(242, 201, 76, 0.08)',
                borderWidth: 2.2,
                fill: false,
                tension: 0.35,
                pointRadius: (s.rsrp && s.rsrp.length > 25) ? 0 : 3.5,
                pointHoverRadius: 6,
                pointBackgroundColor: '#F2C94C',
                pointBorderColor: '#0B0D0F',
                pointBorderWidth: 2,
                yAxisID: 'y',
            },
            {
                label: 'RSSI (dBm)',
                data: s.rssi || [],
                borderColor: '#38BDF8',
                backgroundColor: 'rgba(56, 189, 248, 0.08)',
                borderWidth: 2.2,
                fill: false,
                tension: 0.35,
                pointRadius: (s.rssi && s.rssi.length > 25) ? 0 : 3.5,
                pointHoverRadius: 6,
                pointBackgroundColor: '#38BDF8',
                pointBorderColor: '#0B0D0F',
                pointBorderWidth: 2,
                yAxisID: 'y',
            },
            {
                label: 'RSRQ (dB)',
                data: s.rsrq || [],
                borderColor: '#A78BFA',
                backgroundColor: 'rgba(167, 139, 250, 0.08)',
                borderWidth: 2.2,
                fill: false,
                tension: 0.35,
                pointRadius: (s.rsrq && s.rsrq.length > 25) ? 0 : 3.5,
                pointHoverRadius: 6,
                pointBackgroundColor: '#A78BFA',
                pointBorderColor: '#0B0D0F',
                pointBorderWidth: 2,
                yAxisID: 'y1',
            },
            {
                label: 'SINR (dB)',
                data: s.sinr || [],
                borderColor: '#34D399',
                backgroundColor: 'rgba(52, 211, 153, 0.08)',
                borderWidth: 2.2,
                fill: false,
                tension: 0.35,
                pointRadius: (s.sinr && s.sinr.length > 25) ? 0 : 3.5,
                pointHoverRadius: 6,
                pointBackgroundColor: '#34D399',
                pointBorderColor: '#0B0D0F',
                pointBorderWidth: 2,
                yAxisID: 'y1',
            },
        ];

        scales = {
            x: {
                grid: {
                    color: 'rgba(35, 41, 49, 0.6)',
                    drawBorder: false,
                },
                ticks: {
                    color: '#9CA3AF',
                    font: { family: 'JetBrains Mono', size: 10 },
                    maxRotation: 0,
                    autoSkip: true,
                    maxTicksLimit: 8,
                }
            },
            y: {
                type: 'linear',
                display: true,
                position: 'left',
                grid: {
                    color: 'rgba(35, 41, 49, 0.6)',
                    drawBorder: false,
                },
                title: {
                    display: true,
                    text: 'Power (dBm)',
                    color: '#9CA3AF',
                    font: { family: 'JetBrains Mono', size: 10 }
                },
                ticks: {
                    color: '#F2C94C',
                    font: { family: 'JetBrains Mono', size: 10 },
                    callback: (v) => `${v} dBm`
                }
            },
            y1: {
                type: 'linear',
                display: true,
                position: 'right',
                grid: {
                    drawOnChartArea: false,
                    drawBorder: false,
                },
                title: {
                    display: true,
                    text: 'Quality (dB)',
                    color: '#9CA3AF',
                    font: { family: 'JetBrains Mono', size: 10 }
                },
                ticks: {
                    color: '#34D399',
                    font: { family: 'JetBrains Mono', size: 10 },
                    callback: (v) => `${v} dB`
                }
            }
        };
    } else {
        const cfg = metricConfigs[metricName.toLowerCase()] || metricConfigs.rsrp;
        const values = Array.isArray(dataPayload) ? dataPayload : (dataPayload.values || []);

        const gradient = ctx.createLinearGradient(0, 0, 0, canvas.height || 260);
        gradient.addColorStop(0, cfg.gradientStart);
        gradient.addColorStop(1, cfg.gradientEnd);

        datasets = [{
            label: cfg.label,
            data: values,
            borderColor: cfg.lineColor,
            backgroundColor: gradient,
            borderWidth: 2.5,
            fill: true,
            tension: 0.35,
            pointRadius: values.length > 30 ? 0 : 3.5,
            pointHoverRadius: 6,
            pointBackgroundColor: cfg.lineColor,
            pointBorderColor: '#0B0D0F',
            pointBorderWidth: 2,
        }];

        scales = {
            x: {
                grid: {
                    color: 'rgba(35, 41, 49, 0.6)',
                    drawBorder: false,
                },
                ticks: {
                    color: '#9CA3AF',
                    font: { family: 'JetBrains Mono', size: 10 },
                    maxRotation: 0,
                    autoSkip: true,
                    maxTicksLimit: 8,
                }
            },
            y: {
                grid: {
                    color: 'rgba(35, 41, 49, 0.6)',
                    drawBorder: false,
                },
                ticks: {
                    color: '#9CA3AF',
                    font: { family: 'JetBrains Mono', size: 10 },
                    callback: (v) => `${v} ${cfg.unit}`
                }
            }
        };
    }

    window.signalChartInstances[canvasId] = new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: datasets
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: {
                mode: 'index',
                intersect: false,
            },
            plugins: {
                legend: {
                    display: showLegend,
                    position: 'top',
                    align: 'end',
                    labels: {
                        boxWidth: 10,
                        boxHeight: 10,
                        usePointStyle: true,
                        pointStyle: 'circle',
                        color: '#9CA3AF',
                        font: { family: 'JetBrains Mono', size: 11, weight: '600' },
                        padding: 14
                    }
                },
                tooltip: {
                    backgroundColor: '#171B20',
                    titleColor: '#F5F5F5',
                    bodyColor: '#FFFFFF',
                    borderColor: '#232931',
                    borderWidth: 1,
                    padding: 10,
                    cornerRadius: 10,
                    titleFont: { family: 'JetBrains Mono', size: 11, weight: 'bold' },
                    bodyFont: { family: 'JetBrains Mono', size: 11 },
                    callbacks: {
                        label: function(context) {
                            const lbl = context.dataset.label || '';
                            const val = context.parsed.y;
                            if (lbl.includes('dBm')) {
                                return ` ${lbl}: ${val} dBm`;
                            } else if (lbl.includes('dB')) {
                                return ` ${lbl}: ${val} dB`;
                            }
                            return ` ${lbl}: ${val}`;
                        }
                    }
                }
            },
            scales: scales
        }
    });
};

/**
 * Robust Lucide Icon Lifecycle Manager
 */
window.refreshIcons = function() {
    if (window.createIcons && window.lucideIcons) {
        window.createIcons({ icons: window.lucideIcons });
    }
};

// Initial DOM load
document.addEventListener('DOMContentLoaded', () => {
    window.refreshIcons();
});

// Livewire page navigation
document.addEventListener('livewire:navigated', () => {
    window.refreshIcons();
});

// Livewire 3 DOM Morph Hooks
document.addEventListener('livewire:init', () => {
    if (window.Livewire) {
        // After any morph update
        window.Livewire.hook('morph.updated', () => {
            window.refreshIcons();
        });

        // When new elements are inserted
        window.Livewire.hook('morph.added', () => {
            window.refreshIcons();
        });

        // After every Livewire server response/commit
        window.Livewire.hook('commit', ({ succeed }) => {
            succeed(() => {
                queueMicrotask(() => {
                    window.refreshIcons();
                });
            });
        });
    }
});

// MutationObserver fallback to catch any async DOM changes
if (typeof MutationObserver !== 'undefined') {
    let iconDebounceTimer = null;
    const observer = new MutationObserver((mutations) => {
        let hasNewIcons = false;
        for (const mutation of mutations) {
            if (mutation.type === 'childList') {
                for (const node of mutation.addedNodes) {
                    if (node.nodeType === 1) {
                        if (node.hasAttribute && node.hasAttribute('data-lucide')) {
                            hasNewIcons = true;
                            break;
                        }
                        if (node.querySelector && node.querySelector('[data-lucide]')) {
                            hasNewIcons = true;
                            break;
                        }
                    }
                }
            }
            if (hasNewIcons) break;
        }

        if (hasNewIcons) {
            clearTimeout(iconDebounceTimer);
            iconDebounceTimer = setTimeout(() => {
                window.refreshIcons();
            }, 10);
        }
    });

    document.addEventListener('DOMContentLoaded', () => {
        observer.observe(document.body, { childList: true, subtree: true });
    });
}
