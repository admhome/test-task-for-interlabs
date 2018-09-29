/**
 * main scripts file
 */

/**
 * build data in table
 */
function buildTableData(json) {
    result = '';

    if (json.length > 0) {
        $(json).each(function(key, item){
            result += '<tr>\
                            <td>' + item.fio + '</td>\
                            <td>' + item.email + '</td>\
                            <td>' + item.address + '</td>\
                            <td><button type="button" class="btn btn-info js__user_edit" data-item="' + item.id + '">Изменить</button></td>\
                            <td class="text-center"><input type="checkbox" name="' + item.id + '" class="js__user_del__data" value="1"></td>\
                        </tr>';
        });
    }

    return result;
}

/**
 * load users data from table
 */
function loadUsersData() {
    $.ajax({
        url: '/ajax.php?action=getUsers',
        data: {
        },
        type: 'GET',
        dataType: 'json'
    })
    .done(function(json){
        $('#data_body').html(buildTableData(json));

        // именно тут необходимо "обновлять" обработчик элементов .js__user_edit,
        // т.к. эти элементы обновляются из динамического строителя внутренностей таблицы
        // с пользователями
        $('.js__user_edit').on('click', function(){
            console.log('[CLICK] js__user_edit');
            console.log('[DATA ATTR] ' + $(this).data('item'));

            $.ajax({
                url: '/ajax.php?action=getUserData',
                data: {'id': $(this).data('item')},
                type: 'POST',
                dataType: 'json'
            })
            .done(function(json){
                $('.js__modal_user_edit').removeClass('d-none');
                $('.js__modal_user_add').addClass('d-none');

                $('#modal_user_add').modal('show');

                $('#InputLogin').val(json[0].login);
                $('#InputFio').val(json[0].fio);
                $('#InputEmail').val(json[0].email);
                $('#InputAddress').val(json[0].address);
                $('#InputId').val(json[0].id);
            })
            .fail(function(xhr, status, errorThrown){
            });
        });
    })
    .fail(function(xhr, status, errorThrown){
        $('#data_body').html('<tr><td colspan="4">Can\'t obtain data!</td></tr>');
    });
}

/**
 * main runtime function
 */
$(function() {
     /*
        buttons
     */
    $('.js__user_add').on('click', function(){
        $('.js__modal_user_edit').addClass('d-none');
        $('.js__modal_user_add').removeClass('d-none');
    });

    $('.js__user_del').on('click', function(){
        delData = $('.js__user_del__data:checked').serialize();

        if (delData != '') {
            // need delete selected items
            $.ajax({
                url: '/ajax.php?action=delUsers',
                data: {'delData':delData},
                type: 'POST',
                dataType: 'json'
            })
            .done(function(json){
                if (typeof json.error != undefined) {
                    console.log('[DEBUG] We have an error!');
                }
                loadUsersData();
            })
            .fail(function(xhr, status, errorThrown){
                $('.js__data_body').html('<pre>status: ' + status + '</pre><pre>errorThrown: ' + errorThrown + '</pre>');
            });
        } else {
            $('#modal_message_body').html('<p>Нет выбранных строк для удаления,<br>нечего удалять</p>');
            $('#modal_message').modal('show');
        }
    });

    $('.js__modal_user_add').on('click', function(){
        userData = $('#modal-user-add-form').serialize();

        $.ajax({
            url: '/ajax.php?action=addUser',
            data: userData,
            type: 'POST',
            dataType: 'json'
        })
        .done(function(json){
            if (typeof json.error != undefined) {
                console.log('[DEBUG] We have an error!');
            }
            loadUsersData();
        })
        .fail(function(xhr, status, errorThrown){
            $('.js__data_body').html('<pre>status: ' + status + '</pre><pre>errorThrown: ' + errorThrown + '</pre>');
        });

        $('#modal_user_add').modal('hide');
    });

    $('.js__modal_user_edit').on('click', function(){
        userData = $('#modal-user-add-form').serialize();

        $.ajax({
            url: '/ajax.php?action=changeUser',
            data: userData,
            type: 'POST',
            dataType: 'json'
        })
            .done(function(json){
                if (typeof json.error != undefined) {
                    console.log('[DEBUG] We have an error!');
                }
                loadUsersData();
            })
            .fail(function(xhr, status, errorThrown){
                $('.js__data_body').html('<pre>status: ' + status + '</pre><pre>errorThrown: ' + errorThrown + '</pre>');
            });

        $('#modal_user_add').modal('hide');
    });

    if (typeof $('#data_body') != undefined) {
        loadUsersData();
    }
});
