'use strict';

var wpNavMenu = window.wpNavMenu || {};
(function ($) {

  $(document).ready(function () {

    var $label = $('#page-section-menu-item-name'),
        $pageSelect = $('#page-section-menu-item-page'),
        $sectionSelect = $('#page-section-menu-item-section-title');

    function isNumber(o) {
      return !isNaN(o - 0) && o !== null && o !== "" && o !== false;
    }

    function resetPageSelect() {
      $pageSelect.data('permalink', '');
      $pageSelect.val(-1);
    }
    function resetSectionSelect() {
      $sectionSelect.empty();
      $sectionSelect.attr('disabled', 'disabled');
    }

    function invalidateForm() {
      $('#page-section-div').addClass('form-invalid');
    }

    $('#page-section-menu-item-page').on('change', function (e) {
      var page_id = e.target.value;

      if (!isNumber(page_id) || page_id == -1) {
        resetSectionSelect();
        return;
      }

      console.log(e);
      $.ajax('admin-ajax.php', {
        type: "POST",
        data: {
          action: 'select_page_sections',
          page_id: page_id,
          nonce: $('#page-section-nonce').val()
        }
      }).then(function (response) {
        var parsed = JSON.parse(response);

        $pageSelect.data('permalink', parsed.data.permalink);
        $sectionSelect.empty();

        Object.keys(parsed.data.sections).forEach(function (key) {
          $sectionSelect.append('<option value="' + key + '">' + parsed.data.sections[key] + '</option>');
        });

        $sectionSelect.trigger('change');

        $sectionSelect.removeAttr('disabled');
      }, function () /*jqxhr, status, error*/{
        resetPageSelect();
        resetSectionSelect();
      });
    });

    $('#page-section-menu-item-section-title').on('change', function () /*e*/{
      $label.val($sectionSelect.find('option:selected').text());
    });

    $('#submit-page-section-div').on('click', function () /*e*/{

      var processMethod = wpNavMenu.addMenuItemToBottom,
          $spinner = $('#page-section-div .spinner'),
          label = $label.val(),
          permalink = $pageSelect.data('permalink'),
          section = $sectionSelect.val();

      if ('' === permalink || '' === section) {
        invalidateForm();
        return false;
      }

      var url = permalink + '#' + section;

      if ('' === url || 'http://' == url) {
        invalidateForm();
        return false;
      }

      // Show the ajax spinner
      $spinner.addClass('is-active');
      console.log(wpNavMenu);
      wpNavMenu.addLinkToMenu(url, label, processMethod, function () {
        // Remove the ajax spinner
        $spinner.removeClass('is-active');
        // Set custom link form back to defaults
        $label.val('').blur();
        resetPageSelect();
        resetSectionSelect();
      });
    });
  });
})(jQuery);
