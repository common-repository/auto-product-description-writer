/*global jQuery*/
/*global define */
/*global window */
/*global this*/
/*global location*/
/*global document*/
/*global momoacg_rssfeed_admin*/
/*global console*/
/*jslint this*/
/**
 * ChatGPT Frontend Script
 */
jQuery(document).ready(function ($) {
    "use strict";
    $('body').on('click', '.momo-rssfeed-generate-queue', function () {
        var $parent = $(this).closest('#momorssfeed-editor-main-form');
        var $tab = $(this).closest('.momo-be-main-tabcontent');
        var $msgBox = $parent.find('.momo-be-msg-block');
        $msgBox.html('').removeClass('show').removeClass('warning');
        var $working = $parent.closest('.momo-be-main-tabcontent').find('.momo-be-working');
        var url = $parent.find('input[name="momo_rssfeed_url"]').val();
        var status = $parent.find('select[name="momo_rssfeed_status"]').val();
        var category = $parent.find('select[name="momo_rssfeed_category"]').val();
        if ('' === url) {
            $msgBox.html(momoacg_rssfeed_admin.empty_feed_url).addClass('show').addClass('warning');
            $parent.find('input[name="momo_rssfeed_url"]').focus();
            return;
        }
        var ajaxdata = {};
        ajaxdata.url = url;
        ajaxdata.status = status;
        ajaxdata.category = category;
        ajaxdata.security = momoacg_rssfeed_admin.momoacg_ajax_nonce;
        ajaxdata.action = 'momo_rssfeed_generate_title_add_to_queue';
        $.ajax({
            beforeSend: function () {
                $working.addClass('show');
                $msgBox.html(momoacg_rssfeed_admin.generating_titles).addClass('show');
            },
            type: 'POST',
            dataType: 'json',
            url: momoacg_rssfeed_admin.ajaxurl,
            data: ajaxdata,
            success: function (data) {
                console.log(data);
                if (data.hasOwnProperty('status') && 'good' === data.status) {
                    $msgBox.html(data.msg).addClass('show').removeClass('warning');
                    $parent.find('input[name="momo_rssfeed_url"]').val('').focus();
                } else {
                    $msgBox.html(data.msg).addClass('show').addClass('warning');
                }
            },
            complete: function () {
                $working.removeClass('show');
            }
        });
    });
    $('body').on('click', '.momo-autoblog-generate-queue', function () {
        var $parent = $(this).closest('#momoautoblog-editor-main-form');
        var $msgBox = $parent.find('.momo-be-msg-block');
        $msgBox.html('').removeClass('show').removeClass('warning');
        var $working = $parent.closest('.momo-be-main-tabcontent').find('.momo-be-working');
        var tags = $parent.find('input[name="momo_autoblog_keywords"]').val();
        var status = $parent.find('select[name="momo_autoblog_status"]').val();
        var category = $parent.find('select[name="momo_autoblog_category"]').val();
        var nop = $parent.find('select[name="momo_autoblog_nop"]').val();
        var per = $parent.find('select[name="momo_autoblog_per"]').val();
        var nofpara = $parent.find('select[name="momo_autoblog_nof_para"]').val();
        var wstyle = $parent.find('select[name="momo_autoblog_writing_style"]').val();
        if ('' === tags) {
            $msgBox.html(momoacg_rssfeed_admin.empty_tag_field).addClass('show').addClass('warning');
            $parent.find('input[name="momo_autoblog_keywords"]').focus();
            return;
        }
        var $addImage = $parent.find('input[name="momo_autoblog_add_image"]');
        var $generateTitle = $parent.find('input[name="momo_autoblog_generate_title"]');
        var ajaxdata = {};
        ajaxdata.tags = tags;
        ajaxdata.status = status;
        ajaxdata.category = category;
        ajaxdata.nop = nop;
        ajaxdata.per = per;
        ajaxdata.nofpara = nofpara;
        ajaxdata.wstyle = wstyle;
        if ($addImage.is(":checked")) {
            ajaxdata.addimage = 'on';
        } else {
            ajaxdata.addimage = 'off';
        }
        if ($generateTitle.is(":checked")) {
            ajaxdata.title = 'on';
        } else {
            ajaxdata.title = 'off';
        }
        ajaxdata.security = momoacg_rssfeed_admin.momoacg_ajax_nonce;
        ajaxdata.action = 'momo_autoblog_add_to_queue';
        $.ajax({
            beforeSend: function () {
                $working.addClass('show');
                $msgBox.html(momoacg_rssfeed_admin.generating_cron).addClass('show');
            },
            type: 'POST',
            dataType: 'json',
            url: momoacg_rssfeed_admin.ajaxurl,
            data: ajaxdata,
            success: function (data) {
                console.log(data);
                if (data.hasOwnProperty('status') && 'good' === data.status) {
                    $msgBox.html(data.msg).addClass('show').removeClass('warning');
                    $parent.find('input[name="momo_autoblog_keywords"]').val('').focus();
                } else {
                    $msgBox.html(data.msg).addClass('show').addClass('warning');
                }
            },
            complete: function () {
                $working.removeClass('show');
            }
        });
    });
    $('body').on('click', 'i.momo-rssfeed-remove-cron', function () {
        var $td = $(this).closest('td');
        var $holder = $td.find('span.momo-remove-holder');
        var $tr = $td.closest('tr');
        var $parent = $(this).closest('.momo-be-main-tabcontent');
        var $container = $(this).closest('.momo-ms-admin-content-main');
        var $msgBox = $container.find('.momo-be-msg-block');
        var cid = $td.data('id');
        var ajaxdata = {};
        ajaxdata.security = momoacg_rssfeed_admin.momoacg_ajax_nonce;
        ajaxdata.action = 'momo_acg_rssfeed_delete_cron_by_id';
        ajaxdata.cron_id = cid;
        $.ajax({
            beforeSend: function () {
                $holder.addClass('td-working');
            },
            type: 'POST',
            dataType: 'json',
            url: momoacg_rssfeed_admin.ajaxurl,
            data: ajaxdata,
            success: function (data) {
                if (data.status === 'bad') {
                    $msgBox.html(data.msg);
                    $msgBox.show();
                } else if ('good' === data.status) {
                    $tr.remove();
                    $msgBox.html(data.msg);
                    $msgBox.show();
                }
            },
            complete: function () {
                $holder.removeClass('td-working');
            }
        });
    });
});