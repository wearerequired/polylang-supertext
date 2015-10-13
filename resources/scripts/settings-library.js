/**
 * Sorry for this mess :-( we reused this from blogwerk
 * @author Michael Hadorn <michael.hadorn@blogwerk.com> (initial library)
 * @author Michael Sebel <michael@comotive.ch> (refactoring)
 */

Supertext = Supertext || {};

Supertext.CustomFieldsSettings = function ($) {
  var $customFieldsTree,
    $checkedCustomFieldIdsInput;

  function setCheckedCustomFields() {
    var checkedNodes = $customFieldsTree.jstree("get_checked", false);
    $checkedCustomFieldIdsInput.val(checkedNodes.join(','));
  }

  return {
    initialize: function (options) {
      options = options || {};

      var preselectedNodeIds = options.preselectedNodeIds || [];

      $customFieldsTree = $('#customFieldsTree');
      $checkedCustomFieldIdsInput = $('#checkedCustomFieldIdsInput');

      $customFieldsTree
        .jstree({
          'core': {
            'themes': {
              'name': 'default-dark'
            }
          },
          'plugins': ['checkbox'],
          'checkbox': {
            'keep_selected_style': false
          }
        });

      $customFieldsTree.jstree('select_node', preselectedNodeIds);

      $('#customfieldsSettingsForm').submit(setCheckedCustomFields);
    }
  }
}(jQuery);

// Letzer Button sichtbar schalten
jQuery(document).ready(function () {
  jQuery('#tblStFields tr:last input[type="button"]').toggle(true);

  Supertext.CustomFieldsSettings.initialize({
    preselectedNodeIds: savedCustomFieldIds
  });
});

function Remove_StField(nId) {
  jQuery('#trSupertext_' + nId).remove();
  if (jQuery('#tblStFields tr').length < 4) {
    Add_StField(0);
  } else {
    jQuery('#tblStFields tr:last input[type="button"]').toggle(true);
  }
}

function Add_StField() {
  // letzte ID holen
  var oLastRowId = jQuery('#tblStFields tr:last');

  var nNewId = 1;
  try {
    oLastRowId = jQuery('#tblStFields tr:last');
    nNewId = parseFloat(oLastRowId.attr('id').split('_')[1]);
    nNewId = nNewId + 1;
  }
  catch (err) {
    nNewId = 1;
  }

  var sFilePath = jQuery('#supertext_file_path').val();

  jQuery('#tblStFields tr:last').after('\
		<tr id="trSupertext_' + nNewId + '"> \
			<td> \
				' + jQuery('#supertext_select_user').val() + ' \
			</td> \
			<td> \
				<input type="text" name="fieldStUser[]" id="field_intern_' + nNewId + '" value="" style="width: 200px"> \
			</td> \
			<td> \
				<input type="text" name="fieldStApi[]" id="field_intern_' + nNewId + '" value="" style="width: 200px"> \
			</td> \
			<td> \
					<img src="' + sFilePath + '/images/delete.png" alt="Benutzer entfernen" title="' + Supertext.i18n.deleteUser + '" onclick="javascript: Remove_StField(' + nNewId + ');"> \
			</td> \
		</tr> \
  ');
}

function set_selects_indexes(arr_Indexs) {
  var i = 0;
  jQuery('#frmSupertext select[name=selStWpUsers\\[\\]]').each(function () {
    jQuery(this).val(arr_Indexs[i]);
    i++;
  });
}