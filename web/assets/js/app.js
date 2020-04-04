// jQuery
import $ from 'jquery';

// Bootstrap
import 'bootstrap';
import bsCustomFileInput from 'bs-custom-file-input';

// Fontawesome
import '@fortawesome/fontawesome-free/css/all.css';

// App
import '../css/app.scss';
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
  if ($('#settings_image_avatarImage').length) {
    $('.avatars-selector .single-avatar-image').on('click', function() {
      var name = $(this).attr('data-name');

      $('.avatars-selector .single-avatar-image').removeClass('selected');
      $(this).addClass('selected');

      $('#settings_image_avatarImage').val(name);
    });
  }

  if ($('#country-codes-modal').length) {
    $('#country-codes-modal-save-button').on('click', function(e) {
      e.preventDefault();

      var $modal = $('#country-codes-modal');
      var url = $(this).attr('data-url');
      var value = $modal.find('select').val();

      if (value.length !== 0) {
        url = url.replace('*', value.join(','));
      }

      window.location.href = url;
    });
  }
});
