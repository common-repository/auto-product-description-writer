/*global jQuery*/
/*global define */
/*global window */
/*global this*/
/*global location*/
/*global document*/
/*global momoacg_admin*/
/*global tinymce*/
/*global console*/
/*jslint this*/
/**
 * momowsw Admin Script
 */
jQuery(document).ready(function ($) {
    "use strict";
    function newline_converter(content) {
        console.log(content);
        // Convert double newlines to paragraph tags
        content = content.replace(/\n\n/g, '</p><p>');
      
        // Convert single newline to <br> tags
        content = content.replace(/\n/g, '<br>');
      
        // Wrap the entire content in <p> if necessary
        if (!content.startsWith('<p>')) {
          content = '<p>' + content + '</p>';
        }
      
        return content;
    }
    $('body').on('momo_acgwc_search_tinymce', function () {
        console.log('Textarea getting ready');
        tinymce.init({
            selector: 'textarea.momo_tinymce_trigger_textarea',
            plugins: '',
            toolbar: 'undo redo | bold italic | alignleft aligncenter alignright',
        });
    });
    $('body').trigger('momo_acgwc_search_tinymce');

    $('body').on('click', '.momo_search_log_update', function () {
        var $popbox = $(this).closest('.momo-popbox');
        var $working = $popbox.find('.momo-be-working');
        var $msgBox = $popbox.find('.momo-pb-footer').find('.momo-pb-message');
        $msgBox.attr('class', 'momo-pb-message');
        var id = $popbox.find('input[name="log_id"]').val();
        var template = $popbox.find('textarea[name="momo_mail_template"]').val();
        var ajaxdata = {};
        ajaxdata.action = 'momo_acgwc_searchlog_update';
        ajaxdata.security = momoacgwc_admin.momoacgwc_ajax_nonce;
        ajaxdata.log_id = id;
        ajaxdata.template = template;
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
                    $msgBox.html(data.message).addClass('show');
                }
            },
            complete: function () {
                $working.removeClass('show');
            }
        });
    });
    $('body').on('click', '.momo-sl-action.delete', function () {
        var $tr = $(this).closest('tr');
        var id = $(this).data('id');
        var $parent = $(this).closest('.momo-be-main-tabcontent');
        var $working = $parent.find('.momo-be-working');
        var $msgBox = $parent.find('.momo-be-msg-block ');
        var ajaxdata = {};
        ajaxdata.action = 'momo_acgwc_searchlog_delete';
        ajaxdata.security = momoacgwc_admin.momoacgwc_ajax_nonce;
        ajaxdata.log_id = id;
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
                    $msgBox.html(data.message).addClass('show');
                    $tr.remove();
                } else {
                    $msgBox.html(data.message).addClass('show');
                }
            },
            complete: function () {
                $working.removeClass('show');
            }
        });
    });
    $('body').on('click', '.momo-sl-action.email', function () {
        var id = $(this).data('id');
        var $parent = $(this).closest('.momo-be-main-tabcontent');
        var $working = $parent.find('.momo-be-working');
        var $msgBox = $parent.find('.momo-be-msg-block ');
        var ajaxdata = {};
        ajaxdata.action = 'momo_acgwc_searchlog_email';
        ajaxdata.security = momoacgwc_admin.momoacgwc_ajax_nonce;
        ajaxdata.log_id = id;
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
                    $msgBox.html(data.message).addClass('show');
                } else {
                    $msgBox.html(data.message).addClass('show');
                }
            },
            complete: function () {
                $working.removeClass('show');
            }
        });
    });
    $('.momo-acgwc-search-mail-table .tablenav-pages a').each(function() {
        var currentHref = $(this).attr('href');
        $(this).attr('href', currentHref + '#momo-be-sales-mail');
    });
});
