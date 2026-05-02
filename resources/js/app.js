import './bootstrap';
import Alpine from 'alpinejs';
import ApexCharts from 'apexcharts';

window.ApexCharts = ApexCharts;
window.Alpine = Alpine;

Alpine.data('waterChart', (initialData) => ({
    chart: null,
    chartData: initialData,

    init() {
        if (!this.chartData || !this.$refs.chartCanvas) return;

        const options = {
            series: [{
                name: 'Pemakaian (m3)',
                data: this.chartData.series
            }],
            chart: {
                type: 'bar',
                height: 340,
                toolbar: { show: false },
                fontFamily: 'Plus Jakarta Sans, sans-serif',
                animations: {
                    enabled: true,
                    easing: 'easeinout',
                    speed: 800
                }
            },
            plotOptions: {
                bar: {
                    borderRadius: 6,
                    columnWidth: '45%',
                    distributed: false,
                }
            },
            dataLabels: { enabled: false },
            colors: ['#1AACB4'],
            xaxis: {
                categories: this.chartData.categories,
                axisBorder: { show: false },
                axisTicks: { show: false },
                labels: {
                    style: { colors: '#94a3b8', fontSize: '10px', fontWeight: 600 }
                }
            },
            yaxis: {
                labels: {
                    style: { colors: '#94a3b8', fontSize: '10px', fontWeight: 600 }
                }
            },
            grid: {
                borderColor: '#f1f5f9',
                strokeDashArray: 4,
                padding: { left: 0, right: 0 }
            },
            tooltip: {
                theme: 'light',
                y: { formatter: (val) => `${val} m3` }
            },
            responsive: [{
                breakpoint: 768,
                options: {
                    chart: {
                        height: 280
                    },
                    plotOptions: {
                        bar: {
                            columnWidth: '60%'
                        }
                    }
                }
            }]
        };

        this.chart = new window.ApexCharts(this.$refs.chartCanvas, options);
        this.chart.render();

        this.$watch('chartData', (newData) => {
            if (newData && this.chart) {
                this.chart.updateOptions({
                    xaxis: { categories: newData.categories }
                });
                this.chart.updateSeries([{ data: newData.series }]);
            }
        });
    }
}));

Alpine.start();
