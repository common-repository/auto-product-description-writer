/*global jQuery*/
/*global define */
/*global window */
/*global this*/
/*global tinymce*/
/*global document*/
/*global momoacgwc_admin*/
/*global console*/
/*global FileReader*/
/*global location*/
/*jslint this*/
/**
 * Woo Product Writer (momoacgwc) Admin Script
 */
jQuery(document).ready(function ($) {
    "use strict";
    function changeAdminTab(hash) {
        var mmtmsTable = $('.momo-be-tab-table');
        mmtmsTable.attr('data-tab', hash);
        mmtmsTable.find('.momo-be-admin-content.active').removeClass('active');
        var ul = mmtmsTable.find('ul.momo-be-main-tab');
        ul.find('li a').removeClass('active');
        $(ul).find('a[href=\\' + hash + ']').addClass('active');
        mmtmsTable.find(hash).addClass('active');
        $("html, body").animate({
            scrollTop: 0
        }, 1000);
    }
    function doNothing() {
        var mmtmsTable = $('.momo-be-tab-table');
        mmtmsTable.attr('data-tab', '#momo-eo-ei-event_card');
        return;
    }
    function init() {
        var hash = window.location.hash;
        if (hash === '' || hash === 'undefined') {
            doNothing();
        } else {
            changeAdminTab(hash);
        }
        $('#momo-be-form .switch-input').each(function () {
            var toggleContainer = $(this).parents('.momo-be-toggle-container');
            var afteryes = toggleContainer.attr('momo-be-tc-yes-container');
            if ($(this).is(":checked")) {
                $('#' + afteryes).addClass('active');
            } else {
                $('#' + afteryes).removeClass('active');
            }
        });
    }
    init();
    $('body').on('change', '#momo-be-form  .switch-input, .momo-be-mb-form .switch-input', function () {
        var toggleContainer = $(this).parents('.momo-be-toggle-container');
        var afteryes = toggleContainer.attr('momo-be-tc-yes-container');
        if ($(this).is(":checked")) {
            $('#' + afteryes).addClass('active');
            $(this).val('on');
        } else {
            $('#' + afteryes).removeClass('active');
            $(this).val('off');
        }
    });
    $('.momo-be-tab-table').on('click', 'ul.momo-be-main-tab li a, a.momo-inside-page-link', function (e) {
        e.preventDefault();
        var href = $(this).attr('href');
        changeAdminTab(href);
        window.location.hash = href;
    });
    $('body').on('click', '.momo-clear-api', function (e) {
        var $parent = $(this).closest('.momo-be-block');
        var $input = $parent.find('input');
        var $masked = $parent.find('.momo-block-with-asterix');
        $input.val('');
        $input.removeClass('momo-hidden');
        $masked.addClass('momo-hidden');
        $input.focus();
    });
    function refreshProductImageGallery(thumbnail, gallery) {
        // Find the "Update" buttons for the product image and gallery
        console.log('were here');
            // Get the new thumbnail ID value
            
            // Do something with the new thumbnail ID, such as displaying the updated image
            if (thumbnail !== '-1') {
                // Display the updated image using the newThumbnailId
                var imageUrl = wp.media.model.Attachment.get(thumbnail).attributes.url;
                console.log(imageUrl);
                $('#product-image-preview').attr('src', imageUrl);
                wp.media.featuredImage.set( thumbnail ? thumbnail : -1 );
            }
            if (Array.isArray(gallery) && gallery.length > 0) {
                var $image_gallery_ids = $( '#product_image_gallery' );
                var $product_images = $( '#product_images_container' ).find(
                    'ul.product_images'
                );
                var $el = $('.add_product_images').find('a');
                $.each(gallery, function(index, value) {
                    console.log(value);
                    var imageUrl = '';
                    var attachment = wp.media.attachment(value);

                    // Fetch the URL of the attachment
                    attachment.fetch().then(function() {
                        imageUrl = attachment.get('url');
                        $product_images.append(
                            '<li class="image" data-attachment_id="' +
                                value +
                                '"><img src="' +
                                imageUrl +
                                '" /><ul class="actions"><li><a href="#" class="delete" title="' +
                                $el.data( 'delete' ) +
                                '">' +
                                $el.data( 'text' ) +
                                '</a></li></ul></li>'
                        );
                    });
                });
                // Get the existing value of the input field
                var existingGallery = $image_gallery_ids.val();
                var combinedGallery = existingGallery ? existingGallery.split(',') : [];
                combinedGallery = combinedGallery.concat(gallery);
                combinedGallery = Array.from(new Set(combinedGallery));
                $image_gallery_ids.val(combinedGallery.join(','));
                
            }
        
    }
    $('body').on('click', '#momo-acg-wc-generate-product', function () {
        var $parent = $(this).closest('.momo-mb-side');
        var $poststuff = $(this).closest('#poststuff');
        var $postBody = $poststuff.find('#post-body-content');
        var $form = $poststuff.closest('form#post');
        var product_id = $form.find('input[name="post_ID"]').val();
        var $msgBox = $parent.find('#momo_be_mb_generate_product_messagebox');
        var $buttonBlock = $(this).closest('.momo-be-side-bottom');
        var $spinner = $buttonBlock.find('span.spinner');
        var title = $postBody.find('input[name="post_title"]').val();
        var language = $parent.find('select[name="momo_be_mb_language"]').val();
        var model = $parent.find('select[name="momo_be_mb_model"]').val();
        var temperature = $parent.find('input[name="momo_be_mb_temperature"]').val();
        var max_tokens = $parent.find('input[name="momo_be_mb_max_tokens"]').val();
        var top_p = $parent.find('input[name="momo_be_mb_top_p"]').val();
        var frequency_penalty = $parent.find('input[name="momo_be_mb_frequency_penalty"]').val();
        var presence_penalty = $parent.find('input[name="momo_be_mb_presence_penalty"]').val();
        var $addImage = $parent.find('input[name="momo_be_generate_image"]');
        var $addGallery = $parent.find('input[name="momo_be_generate_gallery"]');
        var ajaxdata = {};
        ajaxdata.product_id = product_id;
        ajaxdata.language = language;
        ajaxdata.model = model;
        ajaxdata.title = title;
        ajaxdata.temperature = temperature;
        ajaxdata.max_tokens = max_tokens;
        ajaxdata.top_p = top_p;
        ajaxdata.frequency_penalty = frequency_penalty;
        ajaxdata.presence_penalty = presence_penalty;
        if ($addImage.is(":checked")) {
            ajaxdata.addimage = 'on';
        } else {
            ajaxdata.addimage = 'off';
        }
        if ($addGallery.is(":checked")) {
            ajaxdata.addgallery = 'on';
        } else {
            ajaxdata.addgallery = 'off';
        }
        ajaxdata.security = momoacgwc_admin.momoacgwc_ajax_nonce;
        ajaxdata.action = 'momo_acg_wc_openai_generate_product';
        $.ajax({
            beforeSend: function () {
                $msgBox.html(momoacgwc_admin.generating_product);
                $msgBox.show();
                $spinner.addClass('is-active');
            },
            type: 'POST',
            dataType: 'json',
            url: momoacgwc_admin.ajaxurl,
            data: ajaxdata,
            success: function (data) {
                if (data.hasOwnProperty('status') && 'bad' === data.status) {
                    $msgBox.html(data.msg);
                    $msgBox.show();
                } else if (data.hasOwnProperty('status') && 'good' === data.status) {
                    if (null === tinymce.get('content')) {
                        var ohtml = $('#content').val();
                        $('#content').val(ohtml + data.content);
                    } else {
                        var olderHtml = tinymce.get('content').getContent();
                        tinymce.get('content').setContent(olderHtml + data.content);
                    }
                    if (null === tinymce.get('excerpt')) {
                        var ohtmle = $('#excerpt').val();
                        $('#excerpt').val(ohtmle + data.short);
                    } else {
                        var olderHtmlE = tinymce.get('excerpt').getContent();
                        tinymce.get('excerpt').setContent(olderHtmlE + data.short);
                    }
                    $msgBox.html(data.msg);
                    $msgBox.show();
                    refreshProductImageGallery(data.thumbnail, data.gallery);
                    
                    
                } else {
                    $msgBox.html(momoacgwc_admin.empty_response);
                    $msgBox.show();
                }
            },
            complete: function () {
                $spinner.removeClass('is-active');
            }
        });
    });
    $('body').on('click', '#momo_acg_wc_import_settings', function (e) {
        e.preventDefault();
        var $block = $(this).closest('.momo-two-column');
        var $parent = $block.closest('.momo-be-main-tabcontent');
        var $working = $parent.find('.momo-be-working');
        var $file = $block.find('input[name="settings[]"]');
        var files = $file.prop('files');
        var acceptable_file_type = $file.data('file_type');
        if (!files) {
            console.log('Missing File.');
            return;
        }
        var file = files[0];
        if (!window.File || !window.FileReader || !window.FileList || !window.Blob) {
            console.log('The File APIs are not fully supported in this browser.');
            return;
        }
        if (file.name.indexOf(acceptable_file_type) === -1) {
            console.log('Only accept ' + acceptable_file_type + ' file format.');
        } else {
            var reader = new FileReader();
            reader.readAsText(file);
            reader.onload = function (reader_event) {
                var jsonData = reader_event.target.result;
                var ajaxdata = {};
                ajaxdata.action = 'momo_acg_wc_import_settings';
                ajaxdata.security = momoacgwc_admin.momoacgwc_ajax_nonce;
                ajaxdata.jsondata = $.parseJSON(jsonData);
                $.ajax({
                    beforeSend: function () {
                        $working.addClass('show');
                    },
                    type: 'POST',
                    url: momoacgwc_admin.ajaxurl,
                    data: ajaxdata,
                    dataType: 'json',
                    success: function (data) {
                        console.log(data.message);
                        location.reload();
                    },
                    complete: function () {
                        $working.removeClass('show');
                    }
                });
            };
        }
    });
    $('body').on('click', '.momo-pb-overlay', function () {
        var target = $(this).data('target');
        var $target = $('body').find('#' + target);
        $target.removeClass('momo-pb-show');
    });
    $('body').on('click', 'i.momo-pb-close', function () {
        var $popbox = $(this).closest('.momo-popbox');
        $popbox.removeClass('momo-pb-show');
    });
    $('body').on('click', '.momo-pb-triggerer', function () {
        var $parent = $(this).closest('#openai-content-generator');

        var title = $parent.find('input[name="momo_be_mb_search_title"]').val();
        var target = $(this).data('target');
        var ajax = $(this).data('ajax');
        var header = $(this).data('header');
        var trigger = $(this).data('trigger');
        var data = $(this).data('djson');
        var $target = $('body').find('#' + target);
        var $header = $target.find('.momo-pb-header').find('span.header-text');
        $header.html(momoacgwc_admin[header]);
        $target.addClass('momo-pb-show');
        var $working = $target.find('.momo-be-working');
        var $contentBox = $target.find('.momo-pb-content').find('.content-html');
        var $msgBox = $target.find('.momo-pb-footer').find('.momo-pb-message');
        $msgBox.attr('class', 'momo-pb-message');
        var ajaxdata = {};
        ajaxdata.security = momoacgwc_admin.momoacgwc_ajax_nonce;
        ajaxdata.action = ajax;
        ajaxdata.title = title;
        ajaxdata.data = data !== undefined
            ? data
            : '';
        $.ajax({
            beforeSend: function () {
                $contentBox.val('');
                $working.addClass('show');
                $msgBox.html('');
                $msgBox.removeClass('show');
            },
            type: 'POST',
            dataType: 'json',
            url: momoacgwc_admin.ajaxurl,
            data: ajaxdata,
            success: function (data) {
                if (data.hasOwnProperty('status') && 'good' === data.status) {
                    $contentBox.html(data.content);
                    $msgBox.html(data.message);
                    $msgBox.addClass('show');
                    if (trigger !== undefined) {
                        $('body').trigger(trigger);
                    }
                } else {
                    $msgBox.html(data.message);
                    $msgBox.addClass('show');
                }
            },
            complete: function () {
                $working.removeClass('show');
            }
        });
    });
});