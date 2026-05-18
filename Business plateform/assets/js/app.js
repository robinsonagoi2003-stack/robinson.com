document.addEventListener('DOMContentLoaded', function() {
    const aiButtons = document.querySelectorAll('[data-prompt]');
    const promptField = document.getElementById('aiPrompt');
    const runAiButton = document.getElementById('runAi');
    const aiResult = document.getElementById('aiResult');

    aiButtons.forEach(function(button) {
        button.addEventListener('click', function() {
            promptField.value = button.dataset.prompt;
        });
    });

    if (runAiButton) {
        runAiButton.addEventListener('click', function() {
            const prompt = promptField.value.trim();
            if (!prompt) {
                aiResult.textContent = 'Enter a business question or choose a preset prompt.';
                return;
            }
            aiResult.textContent = 'Generating AI insight...';
            fetch('ai_action.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ prompt }),
            })
            .then(resp => resp.json())
            .then(data => {
                if (data.success) {
                    aiResult.textContent = data.response;
                } else {
                    aiResult.textContent = data.message || 'Unable to generate insight at the moment.';
                }
            })
            .catch(() => {
                aiResult.textContent = 'Network error while requesting AI insight.';
            });
        });
    }

    const renderChart = (elementId, chartType, labels, data, backgroundColor) => {
        const chartEl = document.getElementById(elementId);
        if (!chartEl || typeof Chart === 'undefined') return;
        new Chart(chartEl, {
            type: chartType,
            data: {
                labels,
                datasets: [{
                    data,
                    backgroundColor,
                    borderColor: backgroundColor,
                    borderWidth: 1,
                    fill: chartType === 'line',
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: chartType !== 'line' },
                },
            },
        });
    };

    const orderStatusCanvas = document.getElementById('orderStatusChart');
    const revenueTrendCanvas = document.getElementById('revenueTrendChart');

    if (orderStatusCanvas) {
        const chartData = JSON.parse(orderStatusCanvas.dataset.chart || '[]');
        const labels = chartData.map(item => item.status);
        const values = chartData.map(item => item.total);
        renderChart('orderStatusChart', 'doughnut', labels, values, ['#2563eb', '#10b981', '#f59e0b', '#ef4444']);
    }

    if (revenueTrendCanvas) {
        const chartData = JSON.parse(revenueTrendCanvas.dataset.chart || '[]');
        const labels = chartData.map(item => item.day);
        const values = chartData.map(item => item.total);
        renderChart('revenueTrendChart', 'line', labels, values, '#2563eb');
    }
});
