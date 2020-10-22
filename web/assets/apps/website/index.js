import $ from 'jquery';
import 'bootstrap';
import bsCustomFileInput from 'bs-custom-file-input';
import '@fortawesome/fontawesome-free/css/all.css';
import './css/index.scss';
import './helpers';

$(document).ready(function () {
  /********** General **********/
  function attachEvents() {
    bsCustomFileInput.init();
    $('select').appSelect();
    $('.autocomplete-input').appAutocomplete();
    $('.custom-file-input, .custom-file-url-input').appCustomFile();
    $('.collection').appCollection({
      onAddCallback: attachEvents,
    });
    $('.btn-confirm').on('click', function(e) {
      e.preventDefault();

      var href = $(this).attr('href');
      var text = $(this).attr('data-confirm-text');

      var response = confirm(text);
      if (!response) {
        return;
      }

      window.location.href = href;
    });
    $('.btn-prompt-add-query').on('click', function(e) {
      e.preventDefault();

      var href = $(this).attr('href');
      var text = $(this).attr('data-promt-add-query-text');
      var parameter = $(this).attr('data-promt-add-query-parameter');
      var defaultValue = $(this).attr('data-promt-add-query-default-value');

      var response = prompt(text, defaultValue);
      if (response === null) {
        return;
      }

      window.location.href = href +
        (href.indexOf('?') !== -1 ? '&' : '?') +
        parameter + '=' + response;
    });
  }
  attachEvents();

  $('.infinite-scroll-wrapper').appInfiniteScroll();

  /***** Tab hash *****/
  if (window.location.hash) {
    var tab = window.location.hash + '-tab';
    if ($(tab).length) {
      $(tab).trigger('click');
    }
  }

  /********** Specific **********/
  // Settings - Avatar Image
  if ($('#settings_image_avatarImage').length) {
    $('.avatars-selector .single-avatar-image').on('click', function() {
      var name = $(this).attr('data-name');

      $('.avatars-selector .single-avatar-image').removeClass('selected');
      $(this).addClass('selected');

      $('#settings_image_avatarImage').val(name);
    });
  }

  // Messaging
  var $messagingThreadMessages = $('#messaging-thread-messages');
  if ($messagingThreadMessages.length) {
    var $messagingThreadMessagesInner = $('#messaging-thread-messages-inner');
    $messagingThreadMessages.scrollTop($messagingThreadMessagesInner.outerHeight());

    $messagingThreadMessages.on('scroll', function() {
      var scrollTop = $(this).scrollTop();

      if (scrollTop === 0) {
        if ($messagingThreadMessagesInner.attr('data-has-more-prepend-entries') === 'false') {
          return;
        }

        loadMessages('prepend');
      }
    });

    var $messagingThreadMessagesForm = $('#messaging-thread-messages-wrapper form');
    $messagingThreadMessagesForm.on('submit', function(e) {
      e.preventDefault();

      var $messagingThreadMessagesFormSubmitButton = $messagingThreadMessagesForm.find('[type="submit"]');

      $messagingThreadMessagesFormSubmitButton.prop('disabled', true);

      $.ajax({
        type: 'POST',
        data: $messagingThreadMessagesForm.serialize(),
        success: function() {
          loadMessages('append', function() {
            $messagingThreadMessagesFormSubmitButton.prop('disabled', false);
            $messagingThreadMessagesForm.find('textarea').val('');
            $messagingThreadMessages.scrollTop($messagingThreadMessagesInner.outerHeight());
          });
        },
      });
    });

    setInterval(function() {
      loadMessages('append');
    }, 15000);
  }
});

/********** Functions **********/
function loadMessages(type, callback) {
  var loaderHtml = '<div class="loader text-center">' +
    '<i class="fas fa-spinner fa-spin fa-3x"></i>' +
  '<div>';
  var $messagingThreadMessages = $('#messaging-thread-messages');
  var $messagingThreadMessagesInner = $('#messaging-thread-messages-inner');

  var url = window.location.href;
  if (type === 'append') {
    url += '?since_id=' + ($messagingThreadMessagesInner.find('.thread-user-message:last')
      ? $messagingThreadMessagesInner.find('.thread-user-message:last').attr('data-id')
      : 0);

    $messagingThreadMessagesInner.append(loaderHtml);
  } else if (type === 'prepend') {
    url += '?until_id=' + ($messagingThreadMessagesInner.find('.thread-user-message:first')
      ? $messagingThreadMessagesInner.find('.thread-user-message:first').attr('data-id')
      : 0);

    $messagingThreadMessagesInner.prepend(loaderHtml);
  }

  $.get(url, function (responseHtml) {
    var $messagingThreadMessagesInnerResponse = $(responseHtml).find('#messaging-thread-messages-inner');
    var newMessagingThreadMessagesHtml = $messagingThreadMessagesInnerResponse
      ? $messagingThreadMessagesInnerResponse.html()
      : '';

    if (type === 'append') {
      $('#messaging-thread-messages-inner').append(newMessagingThreadMessagesHtml);
    } else if (type === 'prepend') {
      var messagesCurrentHeight = $messagingThreadMessagesInner.outerHeight();

      $('#messaging-thread-messages-inner').prepend(newMessagingThreadMessagesHtml);

      $messagingThreadMessagesInner.attr(
        'data-has-more-prepend-entries',
        $messagingThreadMessagesInnerResponse.attr('data-has-more')
      );

      setTimeout(function () {
        $messagingThreadMessages.scrollTop(
          $messagingThreadMessagesInner.outerHeight() - messagesCurrentHeight
        );
      });
    } else {
      $('#messaging-thread-messages-inner').html(newMessagingThreadMessagesHtml);

      $messagingThreadMessagesInner.removeAttr('data-has-more-prepend-entries');
    }

    $('#messaging-thread-messages-inner').find('.loader').remove();

    if (callback) {
      callback();
    }
  });
}