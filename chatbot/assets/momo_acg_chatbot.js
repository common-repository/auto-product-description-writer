/*global jQuery*/
/*global define */
/*global window */
/*global this*/
/*global location*/
/*global document*/
/*global momoacg_chatbot*/
/*global momoacgwc_admin*/
/*global console*/
/*jslint this*/
/**
 * ACG chatbot Script
 */
jQuery(document).ready(function ($) {
    'use strict';
    let intervalId;
    $(".momo-acg-chatbot-circle").click(function () {
        $(".momo-acg-chatbot-circle").toggle('scale');
        $(".momo-acg-chatbox").toggle('scale');
    });
    $(".momo-acg-cb-toggle").click(function () {
        $(".momo-acg-chatbot-circle").toggle('scale');
        $(".momo-acg-chatbox").toggle('scale');
    });
    function momo_scroll_to_bottom($parent) {
        $parent.find('.momo-acg-cb-logs').scrollTop($parent.find('.momo-acg-cb-logs')[0].scrollHeight);
    }
    function momo_print_logs(message, type, $parent) {
        var output = '';
        if ('user' === type) {
            output = '<div class="acg-cb-message acg-user">';
            output += '<span class="acg-name">' + momoacg_chatbot.username + '</span>';
            output += '<span class="acg-message">' + message + '</span>';
            output += '</div>';
        }
        $parent.find('.momo-acg-cb-logs').append(output);

    }
    function toggleTypingDots($parent) {
        var dots = $parent.find('.typing-dot');
        var dotIndex = 0;
        if (intervalId) {
          clearInterval(intervalId); // Stop the animation
          intervalId = null;
          dots.removeClass('visible');
        } else {
          intervalId = setInterval(function() {
            $(dots[dotIndex]).toggleClass('visible');
            dotIndex = (dotIndex + 1) % dots.length;
  
            if (dots.filter('.visible').length === dots.length) {
              setTimeout(function() {
                dots.removeClass('visible');
                dotIndex = 0;
              }, 500);
            }
          }, 500);
        }
    }
    function momo_create_typing_bubble() {
        var bubble = $('<div class="momo-acg-chatbot-typing-container">');
        var bubbleText = $('<p class="momo-acg-chatbot-bubble-text">' + momoacg_chatbot.typing + '<span class="typing-dot">.</span><span class="typing-dot">.</span><span class="typing-dot">.</span></p>');
        bubble.append(bubbleText);
        return bubble;
    }
    function momo_generate_chat_content(question, $parent) {
        var response = '';
        var ajaxdata = {};
        var bubble;
        var $working = $parent.find('.momo-chatbot-working');
        ajaxdata.security = momoacg_chatbot.momoacgcb_ajax_nonce;
        ajaxdata.question = question;
        $.ajax({
            beforeSend: function (xhr) {
                xhr.setRequestHeader('X-WP-Nonce', momoacg_chatbot.momoacgcb_ajax_nonce);
                /* bubble = momo_create_typing_bubble();
                $parent.find('.momo-acg-cb-logs').append(bubble); */
                $working.addClass('show');
                toggleTypingDots($parent);
                momo_scroll_to_bottom($parent);
            },
            type: 'POST',
            dataType: 'json',
            url: momoacg_chatbot.rest_endpoint,
            data: ajaxdata,
            success: function (data) {
                $parent.find('.momo-acg-cb-logs').append(data.content);
                /* bubble.remove(); */
                toggleTypingDots($parent);
                momo_scroll_to_bottom($parent);
                $working.removeClass('show');
            },
            complete: function () {
                
            }
        });
        return response;
    }
    function momo_chatbot_generate_message(message, $parent) {
        momo_print_logs(message, 'user', $parent);
        momo_scroll_to_bottom($parent);
        momo_generate_chat_content(message, $parent);
    }
    function momo_trigger_button_click($this) {
        var $parent = $this.closest('.momo-acg-chatbox');
        var $input = $parent.find('.momo-acg-cb-input-form').find('input');
        var msg = $input.val();
        if (msg.trim() === '') {
            return false;
        }
        momo_chatbot_generate_message(msg, $parent);
        $input.val('');
    }
    $('body').on('click', '.momo-acg-cb-input-form i.sender-button', function (e) {
        e.preventDefault();
        momo_trigger_button_click($(this));
    });
    $('.momo-acg-cb-input-form input').keypress(function (e) {
        if (e.which === 13) {
            momo_trigger_button_click($(this));
            return false;
        }
        if (momoacg_chatbot.max_length > 0) {
            if (this.value.length === momoacg_chatbot.max_length) {
                e.preventDefault();
            } else if (this.value.length > momoacg_chatbot.max_length) {
                // Maximum exceeded
                this.value = this.value.substring(0, momoacg_chatbot.max_length);
            }
        }
    });
    $('body').on('click', '.momo-cs-cancel-new-ft', function () {
        var $popbox = $(this).closest('.momo-popbox');
        $popbox.removeClass('momo-pb-show');
    });
    $('body').on('click', '.momo-cs-add-new-qa-btn', function () {
        var $popbox = $(this).closest('.momo-popbox');
        var $working = $popbox.find('.momo-be-working');
        var ajaxdata = {};
        ajaxdata.security = momoacgwc_admin.momoacgwc_ajax_nonce;
        ajaxdata.action = 'momo_acg_ft_generate_qa_row';
        $.ajax({
            beforeSend: function () {
                $working.addClass('show');
            },
            type: 'POST',
            dataType: 'json',
            url: momoacgwc_admin.ajaxurl,
            data: ajaxdata,
            success: function (data) {
                console.log(data);
                $popbox.find('.momo-ft-qa-block').append(data.content);
            },
            complete: function () {
                $working.removeClass('show');
            },
            error: function (a,b) {
                console.log(a);
                console.log(b);
            }
        });
    });
    $('body').on('click', '.momo-cs-create-new-ft', function (e) {
        e.preventDefault();
        var ajaxdata = {};
        var postData = [];
        var $popbox = $(this).closest('.momo-popbox');
        var $msgBox = $popbox.find('.momo-pb-message');
        $msgBox.removeClass('warning').removeClass('show');
        var $working = $popbox.find('.momo-be-working');
        var $qaBlock = $popbox.find('.momo-ft-qa-block');
        var modelId = $popbox.find('input[name="model_id"]').val();
        if ('' === modelId) {
            $popbox.find('input[name="model_id"]').focus();
            return;
        }
        $qaBlock.find('.momo-ft-qa-row').each(function (index, tr) {
            var formValues = {};
            var ques = $(this).find('input[name="momo_ft_question"]').val();
            var ans = $(this).find('input[name="momo_ft_answer"]').val();
            formValues.prompt = ques;
            formValues.completion = ans;
            postData.push(formValues);
        });
        ajaxdata.model_id = modelId;
        ajaxdata.post_data = postData;
        ajaxdata.security = momoacgwc_admin.momoacgwc_ajax_nonce;
        ajaxdata.action = 'momo_acg_ft_create_new_model';
        $.ajax({
            beforeSend: function () {
                $working.addClass('show');
                $msgBox.html(momoacgwc_admin.queueing_qanda);
                $msgBox.show();
            },
            type: 'POST',
            dataType: 'json',
            url: momoacgwc_admin.ajaxurl,
            data: ajaxdata,
            success: function (data) {
                if (data.status === 'bad') {
                    $msgBox.html(data.message);
                    $msgBox.addClass('show').addClass('warning');
                } else if ('good' === data.status) {
                    $msgBox.html(data.message);
                    $msgBox.addClass('show');
                }
            },
            complete: function () {
                $working.removeClass('show');
            }
        });
    });
    $('body').on('click', 'i.momo-remove-ft-model', function () {
        var $td = $(this).closest('td');
        var $holder = $td.find('span.momo-remove-holder');
        var $tr = $td.closest('tr');
        var $parent = $tr.closest('.momo-be-block-section');
        var $msgBox = $parent.find('.momo-be-msg-block');
        var mid = $td.data('id');
        var ftid = $td.data('ftid');
        var ajaxdata = {};
        ajaxdata.security = momoacgwc_admin.momoacgwc_ajax_nonce;
        ajaxdata.action = 'momo_acg_remove_ft_model_by_id';
        ajaxdata.model_id = mid;
        ajaxdata.ft_id = ftid;
        $.ajax({
            beforeSend: function () {
                $holder.addClass('td-working');
            },
            type: 'POST',
            dataType: 'json',
            url: momoacgwc_admin.ajaxurl,
            data: ajaxdata,
            success: function (data) {
                if (data.status === 'bad') {
                    $msgBox.html(data.message);
                    $msgBox.show();
                } else if ('good' === data.status) {
                    $tr.remove();
                    $msgBox.html(data.message);
                    $msgBox.show();
                }
            },
            complete: function () {
                $holder.removeClass('td-working');
            }
        });
    });
    $('body').on('click', '.momo-add-content-for-training', function () {
        var $form = $(this).closest('.momo-cb-training-schedule-form');
        var $popbox = $form.closest('.momo-popbox');
        var $working = $popbox.find('.momo-be-working');
        var $msgBox = $popbox.find('.momo-pb-footer').find('.momo-pb-message');
        $msgBox.attr('class', 'momo-pb-message');
        var type = $form.data('type');
        var selectedValues = [];
        $form.find('input[type="checkbox"]:checked').each(function () {
            selectedValues.push($(this).val());
        });
        if (selectedValues.length === 0) {
            var message = $form.data('noselected');
            $msgBox.html(message).addClass('show').addClass('warning');
        } else {
            var ajaxdata = {};
            ajaxdata.action = 'momo_acg_cb_trainings_store_ids';
            ajaxdata.security = momoacgwc_admin.momoacgwc_ajax_nonce;
            ajaxdata.type = type;
            ajaxdata.ids = selectedValues;
            $.ajax({
                beforeSend: function () {
                    $working.addClass('show');
                },
                type: 'POST',
                dataType: 'json',
                url: momoacgwc_admin.ajaxurl,
                data: ajaxdata,
                success: function (data) {
                    console.log(data);
                    if ('good' === data.status) {
                        var table_id = 'current_training_list_of_' + type;
                        $('#' + table_id).find('tbody').html(data.content);
                        $form.find('input[name="momo_acg_cb_trainings_settings\\[' + data.variable + '\\]"').val(data.input);
                        $msgBox.html(data.message).addClass('show');
                        console.log($form.find('input[name="momo_acg_cb_trainings_settings\\[' + data.variable + '\\]"').val());
                        console.log($form.find('input[name="momo_acg_cb_trainings_settings\\[' + data.variable + '\\]"'));
                    }
                },
                complete: function () {
                    $working.removeClass('show');
                }
            });
        }
    });
    $('body').on('click', 'td.momo-table-action span.embed-process', function () {
        var $td = $(this).closest('td');
        var $holder = $(this);
        var $parent = $td.closest('.momoacg-chatbot-trainings-main');
        var $msgBox = $parent.find('.momo-be-msg-block');
        var pid = $td.data('id');
        var type = $td.data('type');
        var ajaxdata = {};
        ajaxdata.security = momoacgwc_admin.momoacgwc_ajax_nonce;
        ajaxdata.action = 'momo_acg_cb_embed_add_to_queue';
        ajaxdata.post_id = pid;
        ajaxdata.type = type;
        $.ajax({
            beforeSend: function () {
                $holder.addClass('td-working');
            },
            type: 'POST',
            dataType: 'json',
            url: momoacgwc_admin.ajaxurl,
            data: ajaxdata,
            success: function (data) {
                if (data.status === 'bad') {
                    $msgBox.html(data.message);
                    $msgBox.show();
                } else if ('good' === data.status) {
                    var table_id = 'current_training_list_of_' + type;
                    $('#' + table_id).find('tbody').html(data.content);
                    $msgBox.html(data.message);
                    $msgBox.show();
                }
            },
            complete: function () {
                $holder.removeClass('td-working');
            }
        });
    });
    $('body').on('click', 'td.momo-table-action span.embed-remove', function () {
        var $td = $(this).closest('td');
        var $holder = $(this);
        var $parent = $td.closest('.momoacg-chatbot-trainings-main');
        var $msgBox = $parent.find('.momo-be-msg-block');
        var pid = $td.data('id');
        var type = $td.data('type');
        var ajaxdata = {};
        ajaxdata.security = momoacgwc_admin.momoacgwc_ajax_nonce;
        ajaxdata.action = 'momo_acg_cb_embed_remove_from_list';
        ajaxdata.post_id = pid;
        ajaxdata.type = type;
        $.ajax({
            beforeSend: function () {
                $holder.addClass('td-working');
            },
            type: 'POST',
            dataType: 'json',
            url: momoacgwc_admin.ajaxurl,
            data: ajaxdata,
            success: function (data) {
                if (data.status === 'bad') {
                    $msgBox.html(data.message);
                    $msgBox.show();
                } else if ('good' === data.status) {
                    var table_id = 'current_training_list_of_' + type;
                    $('#' + table_id).find('tbody').html(data.content);
                    $msgBox.html(data.message);
                    $msgBox.show();
                }
            },
            complete: function () {
                $holder.removeClass('td-working');
            }
        });
    });
    $('body').on('click', 'td.momo-table-action span.embed-reschedule', function () {
        var $td = $(this).closest('td');
        var $holder = $(this);
        var $parent = $td.closest('.momoacg-chatbot-trainings-main');
        var $msgBox = $parent.find('.momo-be-msg-block');
        var pid = $td.data('id');
        var type = $td.data('type');
        var ajaxdata = {};
        ajaxdata.security = momoacgwc_admin.momoacgwc_ajax_nonce;
        ajaxdata.action = 'momo_acg_cb_embed_reschedule_to_queue';
        ajaxdata.post_id = pid;
        ajaxdata.type = type;
        $.ajax({
            beforeSend: function () {
                $holder.addClass('td-working');
            },
            type: 'POST',
            dataType: 'json',
            url: momoacgwc_admin.ajaxurl,
            data: ajaxdata,
            success: function (data) {
                if (data.status === 'bad') {
                    $msgBox.html(data.message);
                    $msgBox.show();
                } else if ('good' === data.status) {
                    var table_id = 'current_training_list_of_' + type;
                    $('#' + table_id).find('tbody').html(data.content);
                    $msgBox.html(data.message);
                    $msgBox.show();
                }
            },
            complete: function () {
                $holder.removeClass('td-working');
            }
        });
    });
});
