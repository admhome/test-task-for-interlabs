<div class="container">
    <!-- table -->
    <div class="row justify-content-center">
        <div class="col-xs-12">
            <table class="table table-hover table-responsive">
                <thead>
                <tr>
                    <td class="js_sortable js__sort_fio">ФИО</td>
                    <td class="js_sortable js__sort_email">E-mail</td>
                    <td class="js_sortable js__sort_address">Адрес</td>
                    <td>&nbsp;</td>
                    <td>Удалить</td>
                </tr>
                </thead>
                <tbody id="data_body" class="js__data_body">
                <tr>
                    <td colspan="4" class="text-center align-middle">
                        <!-- preloader -->
                        <img style="margin: 100px;" src="/img/preloader.gif" alt="">
                    </td>
                </tr>
                </tbody>
            </table>
        </div>
    </div>
    <!-- buttons -->
    <div class="row justify-content-center">
        <div class="col-xs-12">
            <button class="btn btn-success js__user_add" data-toggle="modal" data-target="#modal_user_add">Добавить</button>
            <button class="btn btn-danger js__user_del">Удалить</button>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <p class="small">Все данные вымышлены и получены из <a href="https://randus.org/" target="_blank">генератора</a>.</p>
        </div>
    </div>
</div>

<!-- modal window for user adding -->
<div class="modal fade" id="modal_user_add" tabindex="-1" role="dialog" aria-labelledby="modal_user_add_label" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modal_user_add_label">Новый пользователь</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="modal-user-add-form">
                    <div class="form-group">
                        <label for="InputLogin">Логин</label>
                        <input id="InputLogin" type="text" name="new_user_login" class="form-control" placeholder="Выберите логин">
                    </div>
                    <div class="form-group">
                        <label for="InputFio">Ф.И.О.</label>
                        <input id="InputFio" type="text" name="new_user_fio" class="form-control" placeholder="Укажите Ф.И.О.">
                    </div>
                    <div class="form-group">
                        <label for="InputEmail">E-mail</label>
                        <input id="InputEmail" type="email" name="new_user_email" class="form-control" placeholder="Укажите email">
                    </div>
                    <div class="form-group">
                        <label for="InputAddress">Адрес</label>
                        <input id="InputAddress" type="text" name="new_user_address" class="form-control" placeholder="Укажите адрес">
                    </div>

                    <input type="hidden" id="InputId" name="user_id">
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn d-none btn-success js__modal_user_add">Добавить</button>
                <button type="button" class="btn d-none btn-success js__modal_user_edit">Сохранить</button>
                <button type="button" class="btn " data-dismiss="modal">Закрыть</button>
            </div>
        </div>
    </div>
</div>

<!-- modal window for messages -->
<div class="modal fade" id="modal_message" tabindex="-1" role="dialog" aria-labelledby="modal_message_label" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modal_message_label">Сообщение</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="modal_message_body">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn " data-dismiss="modal">Закрыть</button>
            </div>
        </div>
    </div>
</div>