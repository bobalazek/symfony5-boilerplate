/* global autocompleteUrl */

import $ from 'jquery';
import 'select2/dist/js/select2.full';
import 'select2/dist/css/select2.css';
import '@ttskch/select2-bootstrap4-theme/dist/select2-bootstrap4.css';

// App - Select
$.fn.appSelect = function() {
  return this.each(function() {
    var $this = $(this);

    if ($this.hasClass('has-select')) {
      return;
    }

    if ($this.find('option[value=""]').length) {
      $this.select2({
        theme: 'bootstrap4',
        allowClear: true,
        placeholder: '-- none --',
      });
    } else {
      $this.select2({
        theme: 'bootstrap4',
      });
    }

    $this.addClass('has-select');
  });
}

// App - Collection
$.fn.appCollection = function(options) {
  return this.each(function() {
    var $collection = $(this);

    if ($collection.hasClass('has-collection')) {
      return;
    }

    var $wrapper = $collection.parent();

    var $removeButton = $('<button class="btn btn-sm btn-light" type="button">' +
      '<i class="fas fa-minus"></i>' +
    '</button>');

    var $addButton = $('<button class="btn btn-sm btn-light" type="button">' +
      '<i class="fas fa-plus"></i>' +
    '</button>');
    $wrapper.append($addButton);

    $collection.attr('data-index', $collection.children().length);

    $addButton.on('click', function() {
      // General
      var prototype = $collection.attr('data-prototype');
      var index = parseInt($collection.attr('data-index'));

      // Prepare prototype
      prototype = prototype.replace(/__name__/g, index);
      var $prototype = $(prototype);

      addRemoveButton($prototype);

      $collection.append($prototype);
      $collection.attr('data-index', index + 1);

      if (options && options.onAddCallback) {
        options.onAddCallback();
      }
    });

    $.each($collection.children(), function() {
      addRemoveButton($(this));
    });

    function addRemoveButton($entry) {
      var $entryRemoveButton = $removeButton.clone();

      $entryRemoveButton.on('click', function() {
        $entry = $entryRemoveButton.closest('.collection-entry');

        $entry.remove();

        var index = parseInt($collection.attr('data-index'));
        $wrapper.attr('data-index', index - 1);
      });

      $entryRemoveButton.wrap(function() {
        return '<div class="clearfix"><div class="float-right">' +
          $(this).html() +
        '</div></div>';
      });

      var $entryRemoveButtonWrapper = $('<div class="float-right"></div>');
      $entryRemoveButtonWrapper.append($entryRemoveButton);

      var $entryRemoveButtonWrapperWrapper = $('<div class="clearfix"></div>');
      $entryRemoveButtonWrapperWrapper.append($entryRemoveButtonWrapper);

      $entry.append($entryRemoveButtonWrapperWrapper);
    }

    $collection.addClass('has-collection')
  });
}

// App - Autocomplete
$.fn.appAutocomplete = function() {
  return this.each(function() {
    var $this = $(this);

    if ($this.hasClass('has-autocomplete')) {
      return;
    }

    var type = $this.attr('data-type');

    // Clear previous attached dropdown
    $this.removeAttr('data-toggle');
    $this.removeClass('dropdown-toggle');
    $this.parent().removeClass('dropdown');
    $this.parent().find('.dropdown-menu').remove();
    $this.dropdown('dispose');

    // Attach dropdown
    $this.attr('autocomplete', 'off');
    $this.attr('data-toggle', 'dropdown');
    $this.addClass('dropdown-toggle');
    $this.parent().addClass('dropdown');
    $this.after('<div class="dropdown-menu"></div>');
    $this.dropdown();

    var $dropdownMenu = $this.parent().find('.dropdown-menu');

    updateDropdown([], '');

    var interval;
    $this.off('keyup', keyup).on('keyup', keyup);

    function keyup() {
      clearInterval(interval);
      interval = setTimeout(function() {
        doAjaxRequest($this.val());
      }, 250);
    }

    function doAjaxRequest(query) {
      $dropdownMenu.html('<a class="dropdown-item disabled" href="#">' +
        'Searching ...' +
      '</a>');

      $.ajax({
        url: autocompleteUrl + '?type=' + type + '&query=' + query,
      }).done(function(response) {
        updateDropdown(response.data, query);
      });
    }

    function updateDropdown(data, query) {
      $dropdownMenu.html('');

      if (data.length) {
        $.each(data, function(index, entry) {
          var name = entry.name.replace(new RegExp('(' + query + ')', 'i'), '<b>$1</b>');
          var $element = $('<a href="#" class="dropdown-item">' +
            name +
          '</a>');

          $element.on('click', function(e) {
            e.preventDefault();

            $this.val(entry.name);
            $this.dropdown('hide');
          });

          $dropdownMenu.append($element);
        });
      } else if (query.length < 2) {
        $dropdownMenu.html('<a class="dropdown-item disabled" href="#">' +
          'Please enter at least 2 characters ...' +
        '</a>');
      } else {
        $dropdownMenu.html('<a class="dropdown-item disabled" href="#">' +
          'No entries found for query: <b>' + query + '</b>' +
        '</a>');
      }
    }

    $this.addClass('has-autocomplete')
  });
}

// App - Custom file
$.fn.appCustomFile = function() {
  return this.each(function() {
    var $this = $(this);

    if ($this.hasClass('has-custom-file')) {
      return;
    }

    $this.off('change', callback).on('change', callback);

    function callback() {
      var $this = $(this);
      var $wrapper = $this.closest('.custom-file-wrapper');
      if ($wrapper.length === 0) {
        return;
      }

      if ($wrapper.hasClass('d-none')) {
        $wrapper.removeClass('d-none');
      }

      var $img = $wrapper.find('img');

      if ($img.length) {
        if ($this.hasClass('custom-file-url-input')) {
          $img.attr('src', $this.val());
        } else {
          var input = this;
          var file  = input.files[0];
          if (!file) {
            return;
          }

          var reader = new FileReader();
          reader.readAsDataURL(file);
          reader.onload = function (e) {
            $img.attr('src', e.target.result);
          };
        }

        if ($this.attr('type') === 'file') {
          $wrapper.find('.or-text').hide();
          $wrapper.find('input[inputmode="url"]').parent().hide();
        } else {
          $wrapper.find('.or-text').hide();
          $wrapper.find('.custom-file').parent().hide();
        }
      }
    }

    $this.addClass('has-custom-file')
  });
}

// App - Infinite scroll
$.fn.appInfiniteScroll = function() {
  return this.each(function() {
    var $this = $(this);

    if ($this.hasClass('has-infinite-scroll')) {
      return;
    }

    var $items = $this.find('.infinite-scroll-items');

    var isLoading = false;
    var hasMoreItems = true;

    $(window).on('scroll', function() {
      if (isInViewport($this.find('.infinite-scroll-pagination'))) {
        loadNewItems();
      }
    });
    loadNewItems();

    function isInViewport($el) {
      $el = $el.get(0);
      if (!$el) {
        return;
      }

      var bounding = $el.getBoundingClientRect();

      return (
        bounding.top >= 0 &&
        bounding.left >= 0 &&
        bounding.right <= (window.innerWidth || document.documentElement.clientWidth) &&
        bounding.bottom <= (window.innerHeight || document.documentElement.clientHeight)
      );
    }

    function loadNewItems() {
      if (isLoading || !hasMoreItems) {
        return;
      }

      isLoading = true;

      var $pagination = $this.find('.infinite-scroll-pagination');
      var $nextPageAnchor = $pagination.find('.pagination li.page-item:last a');
      if (
        $nextPageAnchor.length === 0 ||
        $nextPageAnchor.parent().hasClass('disabled')
      ) {
        hasMoreItems = false;
        $this.find('.infinite-scroll-pagination').html('');

        return;
      }

      var url = $nextPageAnchor.attr('href');

      $pagination.html('<i class="fas fa-spinner fa-2x fa-spin"></i>');
      $.get(url, function(html) {
        var $html = $(html);

        var $newItems = $html.find('.infinite-scroll-items');
        if ($newItems.find('> div').length === 0) {
          hasMoreItems = false;
          $this.find('.infinite-scroll-pagination').html('');

          return;
        }

        $items.append($newItems);

        var $newPagination = $html.find('.infinite-scroll-pagination');

        $pagination.html($newPagination.html());

        isLoading = false;
      });
    }

    $this.addClass('has-infinite-scroll');
  });
}
