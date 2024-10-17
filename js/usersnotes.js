$(function() {
    plugin_add_tab_in_user_modal(
      str_notes,
      'usersnotes_textarea',
      'usernotes',
    );
    
    const target = document.querySelector('.user-container-wrapper');
    const observer = new MutationObserver(function(mutations) {
      current_users.forEach((u, i) => {
        if (u.usernotes) {
          setIconNote(i, u.usernotes);
        }
      })
    });
    const config = { childList: true }
    observer.observe(target, config);

    $('.update-user-button').on('mouseup', function () {
      const value = $('#usersnotes_textarea').val();
      if (value.length) {
        setIconNote(last_user_index, value);
      } else {
        removeIconNote(last_user_index);
      }
    });
});

function setIconNote(n, notes) {
  const iconSelector = `.user-container[key="${n}"] .user-container-username .user-notes-icon`;
  if (!$(iconSelector).length) {
    $(`.user-container[key="${n}"] .user-container-username`)
      .append(`<i class="user-notes-icon icon-info-circled-1 tiptip" title="${notes}"> </i>`);
  } else {
    $(iconSelector).attr('title', notes);
  }
  $('.user-notes-icon').tipTip();
}

function removeIconNote(n) {
  $(`.user-container[key="${n}"] .user-container-username .user-notes-icon`).remove();
}