const ctx = document.getElementById('chart');
const disk_free_space = ctx.dataset.disk_free_space;
const disk_total_space = ctx.dataset.disk_total_space / 1024 / 1024;
const disk_used_space = (ctx.dataset.disk_total_space - disk_free_space) / 1024 / 1024;

console.log(disk_used_space, disk_total_space);

document.addEventListener("DOMContentLoaded", function () {
    chart.draw();
});

const data = {
    labels: ['Used Disk Space', 'Total Disk Space'
    ],
    datasets: [
        {
            name: "Disk Usage",
            chartType: 'pie',
            values: [disk_used_space, disk_total_space]
        },
    ],
}

const chart = new frappe.Chart("#chart", {  // or a DOM element,
    data: data,
    title: "My Awesome Chart",
    type: "pie", // or 'bar', 'line', 'pie', 'percentage'
    height: 300,
    colors: ["purple", "#ffa3ef", "light-blue"],
    axisOptions: {
        xAxisMode: "tick",
        xIsSeries: true
    },
    barOptions: {
        stacked: true,
        spaceRatio: 0.5
    },
    tooltipOptions: {
        formatTooltipX: (d) => (d + "").toUpperCase(),
        formatTooltipY: (d) => d + " pts"
    }
})

console.log(chart);

setTimeout(() => {
    chart.draw()
}, 300)

window.onresize = chart.draw();