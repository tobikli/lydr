/**
 * Request to enable update notifications.
 *
 * @param $input
 * @param $state
 */
function ayecode_connect_updates($input,$state){
    jQuery.ajax({
        url: ajaxurl,
        type: 'POST',
        dataType: 'json',
        data: {
            action: 'ayecode_connect_updates',
            security: ayecode_connect.nonce,
            state: $state
        },
        beforeSend: function() {
            jQuery($input).prop('disabled', true);
            jQuery($input).closest('li').find('.spinner-border').toggleClass('d-none');
        },
        success: function(data, textStatus, xhr) {
            console.log(data);
            if(data.success){
                // yay
                // maybe switch off licence sync also
                if (!$state && jQuery('#ac-setting-licences').is(':checked')) {
                    jQuery('#ac-setting-licences').trigger('click');
                }

            }else{
                // oh dear, toggle it back
                jQuery($input).prop('checked', !$state);
                alert(ayecode_connect.error_msg);
            }
            jQuery($input).prop('disabled', false);
            jQuery($input).closest('li').find('.spinner-border').toggleClass('d-none');
        },
        error: function(xhr, textStatus, errorThrown) {
            alert(textStatus);
            jQuery($input).prop('disabled', false);
            jQuery($input).closest('li').find('.spinner-border').toggleClass('d-none');
        }
    }); // end of ajax
}

/**
 * Request to disconnect site from AyeCode Connect.
 * 
 * @param $input
 */
function ayecode_connect_disconnect($input){
    if(window.confirm(ayecode_connect.disconnect_msg)){
        jQuery.ajax({
            url: ajaxurl,
            type: 'POST',
            dataType: 'json',
            data: {
                action: 'ayecode_connect_disconnect',
                security: ayecode_connect.nonce
            },
            beforeSend: function() {
                jQuery($input).prop('disabled', true);
                jQuery($input).closest('p').find('.spinner-border').toggleClass('d-none');
            },
            success: function(data, textStatus, xhr) {
                console.log(data);
                if(data.success){
                    location.reload();
                }else{
                    // oh dear
                    alert(ayecode_connect.error_msg);
                }
                jQuery($input).prop('disabled', false);
                jQuery($input).closest('p').find('.spinner-border').toggleClass('d-none');
            },
            error: function(xhr, textStatus, errorThrown) {
                alert(textStatus);
            }
        }); // end of ajax
    }

}

/**
 * Request to enable licence sync.
 *
 * @param $input
 * @param $state
 */
function ayecode_connect_licences($input,$state){
    jQuery.ajax({
        url: ajaxurl,
        type: 'POST',
        dataType: 'json',
        data: {
            action: 'ayecode_connect_licences',
            security: ayecode_connect.nonce,
            state: $state
        },
        beforeSend: function() {
            jQuery($input).prop('disabled', true);
            jQuery($input).closest('li').find('.spinner-border').toggleClass('d-none');
        },
        success: function(data, textStatus, xhr) {
            console.log(data);
            if(data.success){
                // yay
            }else if(data.data){
                // oh dear, toggle it back
                jQuery($input).prop('checked', !$state);
                alert(data.data);
            }else{
                // oh dear, toggle it back
                jQuery($input).prop('checked', !$state);
                alert(ayecode_connect.error_msg);
            }
            jQuery($input).prop('disabled', false);
            jQuery($input).closest('li').find('.spinner-border').toggleClass('d-none');
        },
        error: function(xhr, textStatus, errorThrown) {
            alert(textStatus);
            jQuery($input).prop('disabled', false);
            jQuery($input).closest('li').find('.spinner-border').toggleClass('d-none');
        }
    }); // end of ajax
}

/**
 * Request to enable/disable support widget.
 *
 * @param $input
 * @param $state
 */
function ayecode_connect_support($input,$state){
    jQuery.ajax({
        url: ajaxurl,
        type: 'POST',
        dataType: 'json',
        data: {
            action: 'ayecode_connect_support',
            security: ayecode_connect.nonce,
            state: $state
        },
        beforeSend: function() {
            jQuery($input).prop('disabled', true);
            jQuery($input).closest('li').find('.spinner-border').toggleClass('d-none');
        },
        success: function(data, textStatus, xhr) {
            console.log(data);
            if(data.success){
                // yay
                if($state){
                    ayecode_connect_init_widget();
                }else{
                    window.Beacon('destroy');
                }
            }else if(data.data){
                // oh dear, toggle it back
                jQuery($input).prop('checked', !$state);
                alert(data.data);
            }else{
                // oh dear, toggle it back
                jQuery($input).prop('checked', !$state);
                alert(ayecode_connect.error_msg);
            }
            jQuery($input).prop('disabled', false);
            jQuery($input).closest('li').find('.spinner-border').toggleClass('d-none');
        },
        error: function(xhr, textStatus, errorThrown) {
            alert(textStatus);
            jQuery($input).prop('disabled', false);
            jQuery($input).closest('li').find('.spinner-border').toggleClass('d-none');
        }
    }); // end of ajax
}

/**
 * Request to enable/disable support user.
 *
 * @param $input
 * @param $state
 */
function ayecode_connect_support_user($input,$state){
    jQuery.ajax({
        url: ajaxurl,
        type: 'POST',
        dataType: 'json',
        data: {
            action: 'ayecode_connect_support_user',
            security: ayecode_connect.nonce,
            state: $state
        },
        beforeSend: function() {
            jQuery($input).prop('disabled', true);
            jQuery($input).closest('li').find('.spinner-border').toggleClass('d-none');
        },
        success: function(data, textStatus, xhr) {
            console.log(data);
            if(data.success){
                // yay
                if($state){
                    jQuery('.ac-support-user-status').removeClass('d-none').find('span').html(data.data.message);
                }else{
                    jQuery('.ac-support-user-status').addClass('d-none').find('span').html('');
                }
            }else if(data.data){
                // oh dear, toggle it back
                jQuery($input).prop('checked', !$state);
                alert(data.data);
            }else{
                // oh dear, toggle it back
                jQuery($input).prop('checked', !$state);
                alert(ayecode_connect.error_msg);
            }
            jQuery($input).prop('disabled', false);
            jQuery($input).closest('li').find('.spinner-border').toggleClass('d-none');
        },
        error: function(xhr, textStatus, errorThrown) {
            alert(textStatus);
            jQuery($input).prop('disabled', false);
            jQuery($input).closest('li').find('.spinner-border').toggleClass('d-none');
        }
    }); // end of ajax
}

/**
 * Request to enable/disable support user.
 *
 * @param $input
 * @param $state
 */
function ayecode_connect_install_must_use_plugin(){
    jQuery.ajax({
        url: ajaxurl,
        type: 'POST',
        dataType: 'json',
        data: {
            action: 'ayecode_connect_install_must_use_plugin',
            security: ayecode_connect.nonce,
        },
        beforeSend: function() {
            jQuery('.ac-get-plugins-fix button').prop('disabled', true);
            jQuery('.ac-get-plugins-fix .spinner-border').toggleClass('d-none');
        },
        success: function(data, textStatus, xhr) {
            console.log(data);

            if(data.success){
                jQuery('.ac-get-plugins-fix .alert').removeClass('alert-danger').addClass('alert-success').html(data.data);
            }else{
                jQuery('.ac-get-plugins-fix .alert').html(data.data);
            }
            
        },
        error: function(xhr, textStatus, errorThrown) {
            alert(textStatus);
            jQuery('.ac-get-plugins-fix .spinner-border').toggleClass('d-none');
        }
    }); // end of ajax
}


/**
 * Check server connection.
 */
function ayecode_connect_check_connection(){
    jQuery.ajax({
        url: ajaxurl,
        type: 'POST',
        dataType: 'json',
        data: {
            action: 'ayecode_connect_check_connection',
            security: ayecode_connect.nonce,
        },
        beforeSend: function() {
            jQuery('.ac-test-results').html('<div class="spinner-border spinner-border-sm" role="status"></div> Please wait...');
        },
        success: function(data, textStatus, xhr) {
            console.log(data);

            if(data.success){
                jQuery('.ac-test-results').html(data.data);
            }else{
                jQuery('.ac-test-results').html(data.data);
            }

        },
        error: function(xhr, textStatus, errorThrown) {
            alert(textStatus);
            jQuery('.ac-test-results').html('something went wrong');
        }
    }); // end of ajax
}

function ayecode_connect_clear_licenses(){
    jQuery.ajax({
        url: ajaxurl,
        type: 'POST',
        dataType: 'json',
        data: {
            action: 'ayecode_connect_clear_licenses',
            security: ayecode_connect.nonce,
        },
        beforeSend: function() {
            jQuery('.ac-test-results').html('<div class="spinner-border spinner-border-sm" role="status"></div> Please wait...');
        },
        success: function(data, textStatus, xhr) {
            console.log(data);

            if(data.success){
                jQuery('.ac-test-results').html(data.data);
            }else{
                jQuery('.ac-test-results').html(data.data);
            }

        },
        error: function(xhr, textStatus, errorThrown) {
            alert(textStatus);
            jQuery('.ac-test-results').html('something went wrong');
        }
    }); // end of ajax
}