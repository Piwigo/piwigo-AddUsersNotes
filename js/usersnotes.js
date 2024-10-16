function fill_container_user_info(container, user_index) {
    let user = current_users[user_index];
    let registration_dates = user.registration_date.split(' ');
    container.attr('key', user_index);
    container.find(".user-container-username span").html(user.username);
    if (user.usernotes) {
      container.find(".user-notes-icon").tipTip({content:`${user.usernotes}`});
    } else {
      container.find(".user-notes-icon").hide();
    }
    container.find(".user-container-initials span").html(get_initials(user.username)).addClass(color_icons[user.id % 5]);
    container.find(".user-container-status span").html(user.status);
    container.find(".user-container-email span").html(user.email);
    generate_groups(container, user.groups);
    container.find(".user-container-registration-date").html(registration_dates[0]);
    container.find(".user-container-registration-time").html(registration_dates[1]);
    container.find(".user-container-registration-date-since").html(user.registration_date_since);
}

function fill_user_edit_summary(user_to_edit, pop_in, isGuest) {
    console.log(isGuest);
    if (isGuest) {
      pop_in.find('.user-property-initials span').removeClass(color_icons.join(' ')).addClass(color_icons[user_to_edit.id % 5]);
    } else {
      pop_in.find('.user-property-initials span').html(get_initials(user_to_edit.username)).removeClass(color_icons.join(' ')).addClass(color_icons[user_to_edit.id % 5]);
    }
    pop_in.find('.user-property-username span:first').html(user_to_edit.username); 
    
    
    if (user_to_edit.id === connected_user || user_to_edit.id === 1) {
        pop_in.find('.user-property-username .edit-username-specifier').show();
    } else {
        pop_in.find('.user-property-username .edit-username-specifier').hide();
    }
    pop_in.find('.user-property-username-change input').val(user_to_edit.username);
    pop_in.find('.user-property-password-change input').val('');
    pop_in.find('.user-property-permissions a').attr('href', `admin.php?page=user_perm&user_id=${user_to_edit.id}`);
    pop_in.find('.user-property-register').html(get_formatted_date(user_to_edit.registration_date));
    pop_in.find('.user-property-register').tipTip({content:`${registered_str}<br />${user_to_edit.registration_date_since}`});
    pop_in.find('.user-property-last-visit').html(get_formatted_date(user_to_edit.last_visit));
    pop_in.find('.user-property-last-visit').tipTip({content: `${last_visit_str}<br />${user_to_edit.last_visit_since}`});

    var usernotes_to_display = user_to_edit.usernotes ? user_to_edit.usernotes : '';
    // if (usernotes_to_display.length > 70) {
    //     usernotes_to_display = usernotes_to_display.substring(0, 70) + '<span title="' + user_to_edit.usernotes + '">...</span>'
    // }
    pop_in.find('.usernotes-title').html(usernotes_to_display);
    pop_in.find('.user-property-usernotes-change input').val(user_to_edit.usernotes);
}

$( document ).ready(function() {
    $('.edit-usernotes').click(function () {
        $('.user-property-usernotes').hide();
        $(this).hide();
        $('.user-property-usernotes-change').show().css('display', 'flex');
    })

    $('.edit-usernotes-cancel').click(function () {
        //possibly reset input value
        $('.user-property-usernotes').show();
        $('.edit-usernotes').show();
        $('.user-property-usernotes-change').hide();
    })

    jQuery('.tiptip').tipTip({
      delay: 0,
      fadeIn: 200,
      fadeOut: 200
    });
});

function fill_user_edit_update(user_to_edit, pop_in) {
    pop_in.find('.update-user-button').unbind("click").click(
    user_to_edit.id === guest_id ? update_guest_info : update_user_info);
    pop_in.find('.edit-username-validate').unbind("click").click(update_user_username);
    pop_in.find('.edit-password-validate').unbind("click").click(update_user_password);
	  pop_in.find('.edit-usernotes-validate').unbind("click").click(update_user_usernotes);
    pop_in.find('.delete-user-button').unbind("click").click(function () {
        $.confirm({
            title: title_msg.replace('%s', user_to_edit.username),
            buttons: {
                confirm: {
                    text: confirm_msg,
                    btnClass: 'btn-red',
                    action: function () {
                        delete_user(user_to_edit.id);
                    }
                },
                cancel: {
                    text: cancel_msg
                }
            },
            ...jConfirm_confirm_options
        });
    })
}


function update_user_usernotes() {
    let pop_in_container = $('.UserListPopInContainer');
    let ajax_data = {
        pwg_token: pwg_token,
        user_id: last_user_id
    };
    ajax_data['usernotes'] = pop_in_container.find('.user-property-input-usernotes').val();
    jQuery.ajax({
        url: "ws.php?format=json&method=pwg.users.setInfo",
        type: "POST",
        data: ajax_data,
        success: (raw_data) => {
            data = jQuery.parseJSON(raw_data);
            if (data.stat == 'ok') {
                if (last_user_index != -1) {
                    current_users[last_user_index].usernotes = data.result.users[0].usernotes;
                    usernotes_to_display = current_users[last_user_index].usernotes ? current_users[last_user_index].usernotes : '';
                    if (usernotes_to_display.length > 70) {
                        usernotes_to_display = usernotes_to_display.substring(0, 70) + '<span title="' + current_users[last_user_index].usernotes + '">...</span>';
                    }
                    $('#UserList .user-property-usernotes .usernotes-title').html(usernotes_to_display);
                    fill_container_user_info($('#user-table-content .user-container').eq(last_user_index), last_user_index);
                }
                $("#UserList .update-user-success").fadeIn().delay(1500).fadeOut(2500);
                $('.user-property-usernotes').show();
                $('.user-property-usernotes-change').hide();
                $('.edit-usernotes').show();
            }
        }
    })
}
