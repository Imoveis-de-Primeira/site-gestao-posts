(function ($) {
    'use strict';

    function setStatus(form, text) {
        form.find('.ipgp-save-status').text(text).delay(2200).fadeOut(200, function () {
            $(this).text('').show();
        });
    }

    $(function () {
        $('#ipgp-settings-form').on('submit', function (event) {
            event.preventDefault();
            var form = $(this);
            var data = form.serializeArray();
            data.push({ name: 'action', value: 'ipgp_save_settings' });
            data.push({ name: 'nonce', value: IPGP.nonce });

            $.post(IPGP.ajaxUrl, data).done(function (response) {
                setStatus(form, response.success ? 'Salvo.' : 'Erro ao salvar.');
            });
        });

        $(document).on('submit', '#ipgp-goal-form', function (event) {
            event.preventDefault();
            var form = $(this);
            var data = form.serializeArray();
            data.push({ name: 'action', value: 'ipgp_save_goal' });
            data.push({ name: 'nonce', value: IPGP.nonce });

            $.post(IPGP.ajaxUrl, data).done(function (response) {
                setStatus(form, response.success ? 'Salvo.' : 'Erro ao salvar.');
                if (response.success) {
                    setTimeout(function() { window.location.reload(); }, 500);
                }
            });
        });

        $(document).on('click', '.ipgp-edit-goal', function(event) {
            event.preventDefault();
            var btn = $(this);
            $('#ipgp-goal-id').val(btn.data('id'));
            $('#ipgp-goal-name').val(btn.data('name'));
            $('#ipgp-goal-target').val(btn.data('target'));
            $('#ipgp-goal-start-date').val(btn.data('start'));
            $('#ipgp-goal-end-date').val(btn.data('end'));
            
            $('#ipgp-goal-submit-btn').text('Atualizar meta');
            $('#ipgp-goal-cancel-btn').show();
            $('#ipgp-goal-form-title').text('Editar Meta de publicação');
            
            $('html, body').animate({
                scrollTop: $("#ipgp-goal-form-title").offset().top - 50
            }, 500);
        });

        $(document).on('click', '#ipgp-goal-cancel-btn', function(event) {
            event.preventDefault();
            $('#ipgp-goal-id').val('');
            $('#ipgp-goal-form')[0].reset();
            $('#ipgp-goal-submit-btn').text('Adicionar meta');
            $('#ipgp-goal-cancel-btn').hide();
            $('#ipgp-goal-form-title').text('Adicionar Metas de publicação');
        });

        $(document).on('click', '.ipgp-delete-goal', function(event) {
            event.preventDefault();
            if (!confirm('Tem certeza?')) return;
            
            var id = $(this).data('id');
            var data = {
                action: 'ipgp_delete_goal',
                nonce: IPGP.nonce,
                goal_id: id
            };
            
            $.post(IPGP.ajaxUrl, data).done(function (response) {
                if (response.success) {
                    window.location.reload();
                }
            });
        });

        $('#ipgp-author-colors-form').on('submit', function (event) {
            event.preventDefault();
            var form = $(this);
            var data = form.serializeArray();
            data.push({ name: 'action', value: 'ipgp_save_author_colors' });
            data.push({ name: 'nonce', value: IPGP.nonce });

            $.post(IPGP.ajaxUrl, data).done(function (response) {
                setStatus(form, response.success ? 'Salvo.' : 'Erro ao salvar.');
            });
        });
    });
})(jQuery);
