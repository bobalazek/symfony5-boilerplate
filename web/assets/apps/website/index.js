import $ from 'jquery';
import 'bootstrap';
import bsCustomFileInput from 'bs-custom-file-input';
import '@fortawesome/fontawesome-free/css/all.css';
import './css/index.scss';
import './helpers';
import AppWebSocket from './websocket';

var socket;
$(document).ready(function () {
  var websocketUrl = $('body').attr('data-websocket-url');
  if (websocketUrl) {
    socket = new AppWebSocket(websocketUrl, { debug: true });
  }

  setupEvents();
  setupTabHash();
  setupSettingsAvatarImage();
  setupMessaging();
});

/********** Functions **********/
function setupEvents() {
  bsCustomFileInput.init();
  $('select').appSelect();
  $('.infinite-scroll-wrapper').appInfiniteScroll();
  $('.autocomplete-input').appAutocomplete();
  $('.custom-file-input, .custom-file-url-input').appCustomFile();
  $('.collection').appCollection({
    onAddCallback: setupEvents,
  });

  $('.btn-confirm').on('click', function (e) {
    e.preventDefault();

    var href = $(this).attr('href');
    var text = $(this).attr('data-confirm-text');

    var response = confirm(text);
    if (!response) {
      return;
    }

    window.location.href = href;
  });

  $('.btn-prompt-add-query').on('click', function (e) {
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

function setupSettingsAvatarImage() {
  if ($('#settings_image_avatarImage').length === 0) {
    return;
  }

  $('.avatars-selector .single-avatar-image').on('click', function () {
    var name = $(this).attr('data-name');

    $('.avatars-selector .single-avatar-image').removeClass('selected');
    $(this).addClass('selected');

    $('#settings_image_avatarImage').val(name);
  });
}

function setupTabHash() {
  if (!window.location.hash) {
    return;
  }

  var tab = window.location.hash + '-tab';
  if ($(tab).length) {
    $(tab).trigger('click');
  }
}

function setupMessaging() {
  var $messagingThreadMessages = $('#messaging-thread-messages');
  if ($messagingThreadMessages.length === 0) {
    return;
  }

  var $messagingThreadMessagesInner = $('#messaging-thread-messages-inner');
  $messagingThreadMessages.scrollTop($messagingThreadMessagesInner.outerHeight());

  $messagingThreadMessages.on('scroll', function () {
    var scrollTop = $(this).scrollTop();

    if (scrollTop === 0) {
      if ($messagingThreadMessagesInner.attr('data-has-more-prepend-entries') === 'false') {
        return;
      }

      loadMessages('prepend');
    }
  });

  var $messagingThreadMessagesForm = $('#messaging-thread-messages-wrapper form');
  $messagingThreadMessagesForm.on('submit', function (e) {
    e.preventDefault();

    var $messagingThreadMessagesFormSubmitButton = $messagingThreadMessagesForm.find('[type="submit"]');

    $messagingThreadMessagesFormSubmitButton.prop('disabled', true);

    function afterMessagePost() {
      $messagingThreadMessagesFormSubmitButton.prop('disabled', false);
      $messagingThreadMessagesForm.find('textarea').val('');
      $messagingThreadMessages.scrollTop($messagingThreadMessagesInner.outerHeight());
    }

    $.ajax({
      type: 'POST',
      data: $messagingThreadMessagesForm.serialize(),
      success: function () {
        if (socket && socket.isAlive) {
          afterMessagePost();

          return;
        }

        loadMessages('append', afterMessagePost);
      },
    });
  });

  if (socket) {
    var channel = $messagingThreadMessages.attr('data-channel');
    socket.onChannel(channel, () => {
      loadMessages('append');
    });
  }

  setInterval(function () {
    // Don't do anything if the socket connection is alove
    if (socket && socket.isAlive) {
      return;
    }

    loadMessages('append');
  }, 30000);
}

function loadMessages(type, callback) {
  var loaderHtml = '<div class="loader text-center">' +
    '<i class="fas fa-spinner fa-spin fa-3x"></i>' +
  '<div>';
  var $messagingThreadMessages = $('#messaging-thread-messages');
  var $messagingThreadMessagesInner = $('#messaging-thread-messages-inner');

  var url = window.location.href;
  if (type === 'append') {
    url += '?after=' + ($messagingThreadMessagesInner.find('.thread-user-message:last').length
      ? $messagingThreadMessagesInner.find('.thread-user-message:last').attr('data-id')
      : 0);

    $messagingThreadMessagesInner.append(loaderHtml);
  } else if (type === 'prepend') {
    url += '?before=' + ($messagingThreadMessagesInner.find('.thread-user-message:first').length
      ? $messagingThreadMessagesInner.find('.thread-user-message:first').attr('data-id')
      : 0);

    $messagingThreadMessagesInner.prepend(loaderHtml);
  }

  $.get(url, function (responseHtml) {
    var $responseHtml = $(responseHtml);
    var $messagingThreadMessagesInnerResponse = $responseHtml.find('#messaging-thread-messages-inner');
    var newMessagingThreadMessagesHtml = $messagingThreadMessagesInnerResponse
      ? $messagingThreadMessagesInnerResponse.html()
      : '';

    if (!$('#messaging-thread-messages-inner').length) {
      $('#messaging-thread-messages').html(
        $responseHtml.find('#messaging-thread-messages').html()
      );

      $messagingThreadMessagesInner.find('.loader').remove();

      if (callback) {
        callback();
      }

      return;
    }

    if (type === 'append') {
      $messagingThreadMessagesInner.append(newMessagingThreadMessagesHtml);

      setTimeout(function () {
        // TODO: maybe only if you are actually scrolled to the bottom?
        $messagingThreadMessages.scrollTop($messagingThreadMessagesInner.outerHeight());
      });
    } else if (type === 'prepend') {
      var messagesCurrentHeight = $messagingThreadMessagesInner.outerHeight();

      $messagingThreadMessagesInner.prepend(newMessagingThreadMessagesHtml);

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
      $messagingThreadMessagesInner.html(newMessagingThreadMessagesHtml);

      $messagingThreadMessagesInner.removeAttr('data-has-more-prepend-entries');
    }

    $('#messaging-threads').html(
      $responseHtml.find('#messaging-threads').html()
    );

    $messagingThreadMessagesInner.find('.loader').remove();

    if (callback) {
      callback();
    }
  });
}
