(function ($) {
    'use strict';

    var authorChart = null;
    var categoryChart = null;
    var evolutionChart = null;
    var goalChart = null;
    var currentGoals = [];

    function selectedPostTypes() {
        return $('#ipgp-dashboard-post-types').val() || [];
    }

    function requestData() {
        var range = $('#ipgp-range').val();
        $('.ipgp-custom-range').prop('hidden', range !== 'custom');

        $.post(IPGP.ajaxUrl, {
            action: 'ipgp_dashboard_stats',
            nonce: IPGP.nonce,
            range: range,
            start_date: $('#ipgp-start-date').val(),
            end_date: $('#ipgp-end-date').val(),
            post_types: selectedPostTypes()
        }).done(function (response) {
            if (!response.success) {
                return;
            }

            renderTotals(response.data.totals);
            renderCharts(response.data.rankings);
            renderEvolution(response.data.evolution, response.data.range);
            renderHeatmap(response.data.heatmap, response.data.range);
            
            // Goals are now an array, find active ones
            currentGoals = response.data.goals || [];
            updateGoalsDropdown();
            
            // Sincronizando ranking de produtividade com request inicial (Removemos a action AJAX isolada)
            if (response.data.rankings && response.data.rankings.authors) {
                renderProductivity(response.data.rankings.authors);
            }
        });
    }

    function updateGoalsDropdown() {
        var select = $('#ipgp-active-goal-select');
        var now = new Date();
        now.setHours(0,0,0,0);
        
        var validGoals = currentGoals.filter(function(g) {
            return new Date(g.end_date + 'T00:00:00') >= now;
        });
        
        if (validGoals.length === 0) {
            $('#ipgp-goal-dashboard').hide();
            $('#ipgp-no-goal-dashboard').css('display', 'flex');
        } else {
            $('#ipgp-no-goal-dashboard').hide();
            $('#ipgp-goal-dashboard').css('display', 'flex');
            
            select.empty();
            validGoals.forEach(function(g, i) {
                select.append('<option value="' + i + '">' + g.name + '</option>');
            });
            
            if (validGoals.length > 1) {
                select.show();
            } else {
                select.hide();
            }
            
            renderGoal(validGoals[0]);
        }
    }

    function formatDate(dateStr) {
        if (!dateStr) return '';
        var parts = dateStr.split('-');
        if (parts.length === 3) {
            return parts[2] + '/' + parts[1] + '/' + parts[0];
        }
        return dateStr;
    }

    function renderProductivity(ranking) {
        var list = $('#ipgp-productivity-list');
        list.empty();
        
        if (!ranking || !ranking.length) {
            list.append('<li>Nenhum autor com publicacoes no periodo.</li>');
            return;
        }

        ranking.forEach(function(item) {
            if (item.total > 0) {
                list.append('<li><span>' + item.label + '</span><strong>' + item.total + ' posts</strong></li>');
            }
        });
    }

    function renderEvolution(rows, range) {
        var canvas = document.getElementById('ipgp-evolution-chart');
        if (!canvas) return;
        
        rows = rows || [];
        var dates = rows.map(function(r) { return r.date; }).sort();
        
        var startStr = range && range.start ? range.start.substring(0, 10) : '';
        var endStr = range && range.end ? range.end.substring(0, 10) : '';
        
        if (!startStr) {
            if (dates.length > 0) {
                startStr = dates[0];
            } else {
                var d = new Date();
                d.setDate(d.getDate() - 30);
                startStr = d.toISOString().substring(0, 10);
            }
        }

        if (!endStr) {
            if (dates.length > 0) {
                endStr = dates[dates.length - 1];
            } else {
                endStr = new Date().toISOString().substring(0, 10);
            }
        }
        
        var startDate = new Date(startStr + 'T00:00:00');
        var endDate = new Date(endStr + 'T00:00:00');
        if ((endDate - startDate) / (1000 * 60 * 60 * 24) > 366) {
            startDate = new Date(endDate.getTime() - (365 * 24 * 60 * 60 * 1000));
        }
        
        var pStart = String(startDate.getDate()).padStart(2, '0') + '/' + String(startDate.getMonth() + 1).padStart(2, '0') + '/' + startDate.getFullYear();
        var pEnd = String(endDate.getDate()).padStart(2, '0') + '/' + String(endDate.getMonth() + 1).padStart(2, '0') + '/' + endDate.getFullYear();
        $('#ipgp-evolution-period').text('(' + pStart + ' - ' + pEnd + ')');

        var diffDays = (endDate - startDate) / (1000 * 60 * 60 * 24);
        var isMonthly = diffDays > 31; // more than a month

        var valueMap = {};
        rows.forEach(function(r) { 
            var d = r.date;
            if (isMonthly && d) {
                d = d.substring(0, 7); // YYYY-MM
            }
            valueMap[d] = (valueMap[d] || 0) + (parseInt(r.total, 10) || 0);
        });

        var labels = [];
        var values = [];
        var current = new Date(startDate);
        
        if (isMonthly) {
            current.setDate(1); // Start at first of month
        }

        while (current <= endDate || (isMonthly && current.getFullYear() === endDate.getFullYear() && current.getMonth() === endDate.getMonth())) {
            var yyyy = current.getFullYear();
            var mm = String(current.getMonth() + 1).padStart(2, '0');
            
            if (isMonthly) {
                var key = yyyy + '-' + mm;
                labels.push(mm + '/' + yyyy);
                values.push(valueMap[key] || 0);
                current.setMonth(current.getMonth() + 1);
            } else {
                var dd = String(current.getDate()).padStart(2, '0');
                var key = yyyy + '-' + mm + '-' + dd;
                labels.push(dd + '/' + mm);
                values.push(valueMap[key] || 0);
                current.setDate(current.getDate() + 1);
            }
        }

        if (evolutionChart) {
            evolutionChart.data.labels = labels;
            evolutionChart.data.datasets[0].data = values;
            evolutionChart.update();
        } else {
            evolutionChart = new Chart(canvas, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{ label: 'Posts', data: values, backgroundColor: '#fdc83f', maxBarThickness: 24 }]
                },
                options: {
                    maintainAspectRatio: false,
                    indexAxis: 'x',
                    scales: {
                        x: { beginAtZero: true },
                        y: { beginAtZero: true }
                    },
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return context.raw + ' posts';
                                }
                            }
                        }
                    },
                    onClick: function(event, elements) {
                        if (elements.length > 0) {
                            var index = elements[0].index;
                            console.log('Clicked on evolution date:', labels[index], 'Value:', values[index]);
                        }
                    }
                }
            });
        }
    }

    function renderHeatmap(data, range) {
        var container = $('#ipgp-heatmap');
        container.empty();
        
        data = data || {};
        var dates = Object.keys(data).sort();

        var startStr = range && range.start ? range.start.substring(0, 10) : '';
        var endStr = range && range.end ? range.end.substring(0, 10) : '';

        if (!startStr) {
            if (dates.length > 0) {
                startStr = dates[0];
            } else {
                var d = new Date();
                d.setDate(d.getDate() - 30);
                startStr = d.toISOString().substring(0, 10);
            }
        }

        if (!endStr) {
            if (dates.length > 0) {
                endStr = dates[dates.length - 1];
            } else {
                endStr = new Date().toISOString().substring(0, 10);
            }
        }

        var startDate = new Date(startStr + 'T00:00:00');
        var endDate = new Date(endStr + 'T00:00:00');
        
        // Max 365 days for safety
        if ((endDate - startDate) / (1000 * 60 * 60 * 24) > 366) {
            startDate = new Date(endDate.getTime() - (365 * 24 * 60 * 60 * 1000));
        }

        var current = new Date(startDate);
        current.setDate(current.getDate() - current.getDay()); // Start on nearest Sunday
        
        while (current <= endDate) {
            var yyyy = current.getFullYear();
            var mm = String(current.getMonth() + 1).padStart(2, '0');
            var dd = String(current.getDate()).padStart(2, '0');
            var key = yyyy + '-' + mm + '-' + dd;
            
            var total = data[key] || 0;
            var level = 0;
            if (total > 0 && total <= 2) level = 1;
            else if (total > 2 && total <= 5) level = 2;
            else if (total > 5 && total <= 10) level = 3;
            else if (total > 10) level = 4;

            var title = 'Semana de ' + dd + '/' + mm + ': ' + total + ' posts';
            container.append('<div class="ipgp-heatmap-cell" data-level="' + level + '" title="' + title + '"></div>');
            
            current.setDate(current.getDate() + 7);
        }
    }

    function renderGoal(goal) {
        var dashboard = $('#ipgp-goal-dashboard');
        
        if (!goal) {
            dashboard.hide();
            return;
        }

        dashboard.show();
        $('#ipgp-goal-title').text('Metas: ' + (goal.name || ''));
        $('#ipgp-goal-period').text(formatDate(goal.start_date) + ' - ' + formatDate(goal.end_date));
        $('#ipgp-goal-current').text(goal.current);
        $('#ipgp-goal-target').text(goal.target);
        $('#ipgp-goal-remaining-posts').text(goal.posts_remaining + ' artigos');
        $('#ipgp-goal-remaining-days').text(goal.days_remaining + ' dias');
        $('#ipgp-goal-rate').text(goal.rate_needed + ' artigos/dia');
        $('#ipgp-goal-percent').text(goal.percent + '%');

        var canvas = document.getElementById('ipgp-goal-chart');
        
        var remaining = Math.max(0, goal.target - goal.current);
        var color = goal.percent >= 100 ? '#7bc96f' : '#fdc83f';

        if (goalChart) {
            goalChart.data.datasets[0].data = [goal.current, remaining];
            goalChart.data.datasets[0].backgroundColor[0] = color;
            goalChart.update();
        } else {
            goalChart = new Chart(canvas, {
                type: 'doughnut',
                data: {
                    labels: ['Atingido', 'Falta'],
                    datasets: [{
                        data: [goal.current, remaining],
                        backgroundColor: [color, '#ebedf0'],
                        borderWidth: 0
                    }]
                },
                options: {
                    rotation: -90,
                    circumference: 180,
                    cutout: '80%',
                    plugins: {
                        legend: { display: false },
                        tooltip: { 
                            enabled: true,
                            callbacks: {
                                label: function(context) {
                                    var label = context.label || '';
                                    if (label) {
                                        label += ': ';
                                    }
                                    label += context.raw + ' artigos';
                                    if (context.dataIndex === 0) {
                                        label += ' (' + goal.percent + '%)';
                                    }
                                    return label;
                                }
                            }
                        }
                    }
                }
            });
        }
    }

    function renderTotals(totals) {
        Object.keys(totals).forEach(function (key) {
            $('[data-metric="' + key + '"]').text(totals[key]);
        });
    }

    function renderCharts(rankings) {
        authorChart = renderBarChart('ipgp-authors-chart', authorChart, rankings.authors, false);
        categoryChart = renderBarChart('ipgp-categories-chart', categoryChart, rankings.categories, true);
    }

    function renderBarChart(id, currentChart, rows, isCategory) {
        var canvas = document.getElementById(id);
        var labels = rows.map(function (row) { return row.label; });
        var values = rows.map(function (row) { return parseInt(row.total, 10) || 0; });

        // Adjust canvas wrapper height dynamically if it's the categories chart and has many items
        if (isCategory && rows.length > 5) {
            canvas.parentElement.style.height = (rows.length * 30) + 'px';
            canvas.parentElement.parentElement.style.maxHeight = '300px';
            canvas.parentElement.parentElement.style.overflowY = 'auto';
        } else {
            canvas.parentElement.style.height = '130px';
            if (isCategory) {
                canvas.parentElement.parentElement.style.maxHeight = 'none';
                canvas.parentElement.parentElement.style.overflowY = 'visible';
            }
        }

        if (currentChart) {
            currentChart.data.labels = labels;
            currentChart.data.datasets[0].data = values;
            currentChart.options.indexAxis = isCategory ? 'y' : 'x';
            currentChart.update();
            return currentChart;
        }

        var chartOptions = {
            maintainAspectRatio: false,
            indexAxis: isCategory ? 'y' : 'x',
            scales: {
                x: { beginAtZero: true },
                y: { beginAtZero: true }
            },
            plugins: { 
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return context.raw + ' posts';
                        }
                    }
                }
            },
            onClick: function(event, elements) {
                if (elements.length > 0) {
                    var index = elements[0].index;
                    console.log('Clicked on', id, 'Item:', labels[index], 'Value:', values[index]);
                }
            }
        };

        return new Chart(canvas, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{ label: 'Posts', data: values, backgroundColor: '#fdc83f', maxBarThickness: 24 }]
            },
            options: chartOptions
        });
    }

    $(function () {
        $('#ipgp-range').on('change', requestData);
        $('#ipgp-dashboard-post-types').on('change', requestData);
        $('#ipgp-start-date, #ipgp-end-date').on('change', requestData);
        
        $('#ipgp-active-goal-select').on('change', function() {
            var index = $(this).val();
            var validGoals = currentGoals.filter(function(g) {
                return new Date(g.end_date + 'T00:00:00') >= new Date(new Date().setHours(0,0,0,0));
            });
            if (validGoals[index]) {
                renderGoal(validGoals[index]);
            }
        });

        requestData();
    });
})(jQuery);