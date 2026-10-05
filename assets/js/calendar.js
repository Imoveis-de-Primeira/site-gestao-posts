(function ($) {
    'use strict';

    var calendar;
    var $tooltip;

    function createTooltip() {
        if (!$('#ipgp-calendar-tooltip').length) {
            $tooltip = $('<div id="ipgp-calendar-tooltip" class="ipgp-calendar-tooltip" style="display:none; position:absolute; z-index:9999; background:#fff; padding:10px; border:1px solid #ccc; border-radius:4px; box-shadow:0 2px 4px rgba(0,0,0,0.2); font-size:13px; color:#1d2327;"></div>');
            $('body').append($tooltip);
        } else {
            $tooltip = $('#ipgp-calendar-tooltip');
        }
    }

    function showTooltip(info) {
        if (info.view.type !== 'dayGridMonth') return;
        var props = info.event.extendedProps;
        var html = '<strong>' + info.event.title + '</strong><br>';
        html += 'Autor: ' + props.author_name + '<br>';
        html += 'Data: ' + props.date;
        if (props.categories && props.categories.length) {
            html += '<br>Categorias: ' + props.categories.join(', ');
        }
        if (props.tags && props.tags.length) {
            html += '<br>Tags: ' + props.tags.join(', ');
        }
        $tooltip.html(html).css({
            top: info.jsEvent.pageY + 10 + 'px',
            left: info.jsEvent.pageX + 10 + 'px'
        }).show();
    }

    function hideTooltip() {
        if ($tooltip) {
            $tooltip.hide();
        }
    }

    function selectedPostTypes() {
        return $('#ipgp-calendar-post-types').val() || [];
    }

    function renderLegend(events) {
        var legend = $('#ipgp-author-legend');
        legend.empty();
        
        var authors = {};
        events.forEach(function (event) {
            var props = event.extendedProps;
            if (props && props.author_name) {
                if (!authors[props.author_name]) {
                    authors[props.author_name] = { color: props.color, count: 0 };
                }
                authors[props.author_name].count++;
            }
        });
        
        var sortedAuthors = Object.keys(authors).sort();
        sortedAuthors.forEach(function (name) {
            var data = authors[name];
            var item = $('<div class="ipgp-legend-item" style="display:flex; align-items:center; gap:8px; margin-bottom:4px;"></div>');
            var dot = $('<span class="ipgp-legend-color" style="display:inline-block; width:12px; height:12px; border-radius:50%;"></span>').css('background-color', data.color);
            var label = $('<span class="ipgp-legend-label"></span>').text(name + ' (' + data.count + ')');
            item.append(dot).append(label);
            legend.append(item);
        });
        
        $('#ipgp-calendar-total').text('Total: ' + events.length + ' artigos');
    }

    $(function () {
        createTooltip();

        var calendarEl = document.getElementById('ipgp-calendar');
        if (!calendarEl) return;

        calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            firstDay: 0,
            locale: 'pt-br',
            buttonText: { month: 'Mês', week: 'Semana', list: 'Lista' },
            eventOrder: 'author_name,title',
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridWeek,dayGridMonth,listMonth'
            },
            eventContent: function(arg) {
                var props = arg.event.extendedProps;
                
                var html = '<div class="fc-event-title" style="font-weight: bold; margin-bottom: 4px; white-space: normal;">' + arg.event.title + '</div>';
                
                if (arg.view.type !== 'dayGridMonth') {
                    html += '<div class="fc-event-details" style="font-size: 11px; opacity: 0.9; line-height: 1.3; white-space: normal;">';
                    html += 'Autor: ' + props.author_name + '<br>';
                    html += 'Data: ' + props.date;
                    if (props.categories && props.categories.length) {
                        html += '<br>Categorias: ' + props.categories.join(', ');
                    }
                    if (props.tags && props.tags.length) {
                        html += '<br>Tags: ' + props.tags.join(', ');
                    }
                    html += '</div>';
                }

                var wrap = document.createElement('div');
                wrap.innerHTML = html;
                wrap.style.padding = '4px';
                
                return { domNodes: [wrap] };
            },
            events: function (fetchInfo, successCallback, failureCallback) {
                $.ajax({
                    url: IPGP.ajaxUrl,
                    type: 'POST',
                    data: {
                        action: 'ipgp_calendar_events',
                        nonce: IPGP.nonce,
                        start: fetchInfo.startStr,
                        end: fetchInfo.endStr,
                        post_types: selectedPostTypes()
                    },
                    success: function (events) {
                        renderLegend(events);
                        successCallback(events);
                    },
                    error: function () {
                        failureCallback();
                    }
                });
            },
            eventMouseEnter: function(info) {
                showTooltip(info);
            },
            eventMouseLeave: function(info) {
                hideTooltip();
            },
            eventClick: function (info) {
                info.jsEvent.preventDefault();
                if (info.event.url) {
                    window.open(info.event.url, '_blank');
                }
            }
        });

        calendar.render();

        $('#ipgp-calendar-post-types').on('change', function () {
            calendar.refetchEvents();
        });
    });
})(jQuery);
