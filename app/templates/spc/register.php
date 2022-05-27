<div class="row mt-3" id="app-list">
    <div class="col">
        <div v-if="users.length == 0">Нет детей</div>
        <template v-else>
            <div style="display: flex; flex-direction: row; margin-bottom: .25rem; flex-flow: row-reverse; align-items: center;"><input type="checkbox" id="isOnlyMy" v-model="filter.isOnlyMy"><span style="margin-right: 1rem;">Только мои</span></div>
        <table class="table">
            <thead>
                <tr>
                    <th style="width: 3rem;">№</th>
                    <th>ФИО</th>
                    <th style="width: 15%;">Статус <div class="btn-group">
<button class="btn btn-secondary btn-sm dropdown-toggle" type="button" data-toggle="dropdown" aria-expanded="false">
    {{ filter.text }}
</button>
<div class="dropdown-menu">
    <button class="dropdown-item" @click="onSetFilter(-1)">без фильтра</button>
    <button class="dropdown-item" @click="onSetFilter(0)">не в работе</button>
    <button class="dropdown-item" @click="onSetFilter(1)">в работе</button>
    <button class="dropdown-item" @click="onSetFilter(2)">приостановлена работа</button>
    <button class="dropdown-item" @click="onSetFilter(3)">работа завершена</button>
</div>
</div></th>
                    <th style="width: 25%;">Специалист</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="(itemUser, indexUser) in filtusers">
                    <td>{{ indexUser + 1 }}</td>
                    <td class="spc-information">
                        <template v-if="itemUser.className.length">
                            <span v-for="className in itemUser.className" class="spc-user-class">{{ className }}</span>
                        </template>                     
                        {{ itemUser.name }} <span class="spc__buttons"><button class="btn btn-sm btn-primary" :disabled="itemUser.isDisabledGetUserInformationBtn" v-if="!itemUser.information" v-on:click="onGetInformation(itemUser)">Заключение</button>
                        <a class="btn btn-sm btn-primary" v-bind:href="'/plan?userId=' +  itemUser.userId">Расписание</a></span>
                        <div v-if="itemUser.information" v-html="itemUser.information"></div>
                    </td>
                    <td>
                        <select class="form-control" v-model="itemUser.status" @change="onChangeStatus($event, itemUser.id)">
                            <option value="0">не в работе</option>
                            <option value="1">в работе</option>
                            <option value="2">приостановлена работа</option>
                            <option value="3">работа завершена</option>
                        </select>
                    </td>
                    <td>
                        <select class="form-control" v-model="itemUser.specialistUserId" @change="onChangeSpecialist($event, itemUser.id)">
                            <option v-for="(itemOption, indexOption) in specialists" v-bind:value="indexOption">
                                {{ itemOption }}
                            </option>
                        </select>
                    </td>
                </tr>
            </tbody>
        </table>
        </template>
    </div>
</div>
<script src="/assets/js/jquery.toggler.js"></script>
<script type="text/javascript">
    $(function(){

        $('#isOnlyMy').checkToggler({
            labelOn:'On',
            labelOff:'Off'
        });

    });
</script>
<script type="text/javascript">
    var app2 = new Vue({
        el: '#app-list',
        data: {
            users: <?php echo json_encode($users) ?>,
            specialists: <?php echo json_encode($specialists) ?>,
            userId: <?= $container->get('session')->getUser()->getId(); ?>,
            
            filter: {
                status: -1,
                isOnlyMy: false,
                text: 'Фильтр'
            }
        },
        computed: {
            filtusers: function() {
                if(this.filter.status == -1 && !this.filter.isOnlyMy) {
                    return this.users;
                }
                var _status = this.filter.status;
                var _userId = this.userId;
                var _isOnlyMy = this.filter.isOnlyMy;
                return this.users.filter(function (item) {
                    let filter_status = (_status == -1) || (_status > -1) && (item.status == _status);
                    let filter_isnlymy = !_isOnlyMy || (_isOnlyMy && (item.specialistUserId == _userId));
                    return filter_status & filter_isnlymy;
                });
            }
        },
        methods: {
            onSetFilter: function(status) {
                this.filter.status = status;
                switch(status) {
                    case -1: this.filter.text = 'Фильтр'; break;
                    case 0: this.filter.text = 'не в работе'; break;
                    case 1: this.filter.text = 'в работе'; break;
                    case 2: this.filter.text = 'приостановлена работа'; break;
                    case 3: this.filter.text = 'работа завершена'; break;
                }
            },
            onChangeStatus: function (event, rblListId) {
                $.ajax({
                    type: 'POST',
                    async: true,
                    url: '/api',
                    data: JSON.stringify({
                        'jsonrpc': '2.0',
                        'method': 'spc_Register_setStatus',
                        'params': {
                            'rblListId': rblListId,
                            'status': event.target.value
                        },
                        'id': 1
                    }),
                    contentType: "application/json; charset=utf-8",
                    dataType: 'json'
                })
                .done(function (data) {
                    if (typeof data.error !== "undefined") {
                        new Noty({
                            type: 'error',
                            timeout: 6000,
                            text: data.error.message,
                            animation: {
                                open : 'animated fadeInRight',
                                close: 'animated fadeOutRight'
                            }
                        }).show();
                    }
                    if (typeof data.result !== "undefined") {
                        if (data.result.status == 'ok') {
                            new Noty({
                                type: 'success',
                                timeout: 6000,
                                text: 'Статус изменен',
                                animation: {
                                    open : 'animated fadeInRight',
                                    close: 'animated fadeOutRight'
                                }
                            }).show();
                        }
                        if (data.result.status == 'error') {
                            new Noty({
                                type: 'error',
                                timeout: 6000,
                                text: data.result.message,
                                animation: {
                                    open : 'animated fadeInRight',
                                    close: 'animated fadeOutRight'
                                }
                            }).show();
                        }
                    }
                });
            },
            onChangeSpecialist: function(event, rblListId) {
                $.ajax({
                    type: 'POST',
                    async: true,
                    url: '/api',
                    data: JSON.stringify({
                        'jsonrpc': '2.0',
                        'method': 'spc_Register_setSpecialist',
                        'params': {
                            'rblListId': rblListId,
                            'specialistId': event.target.value
                        },
                        'id': 1
                    }),
                    contentType: "application/json; charset=utf-8",
                    dataType: 'json'
                })
                .done(function (data) {
                    if (typeof data.error !== "undefined") {
                        new Noty({
                            type: 'error',
                            timeout: 6000,
                            text: data.error.message,
                            animation: {
                                open : 'animated fadeInRight',
                                close: 'animated fadeOutRight'
                            }
                        }).show();
                    }
                    if (typeof data.result !== "undefined") {
                        if (data.result.status == 'ok') {
                            new Noty({
                                type: 'success',
                                timeout: 6000,
                                text: 'Специалист изменен',
                                animation: {
                                    open : 'animated fadeInRight',
                                    close: 'animated fadeOutRight'
                                }
                            }).show();
                        }
                        if (data.result.status == 'error') {
                            new Noty({
                                type: 'error',
                                timeout: 6000,
                                text: data.result.message,
                                animation: {
                                    open : 'animated fadeInRight',
                                    close: 'animated fadeOutRight'
                                }
                            }).show();
                        }
                    }
                });
            },
            onGetInformation: function(user) {
                var _self = this;
                _self.$set(user, 'isDisabledGetUserInformationBtn', true);
                $.ajax({
                    type: 'POST',
                    async: true,
                    url: '/api',
                    data: JSON.stringify({
                        'jsonrpc': '2.0',
                        'method': 'spc_Register_getUserInformation',
                        'params': {
                            'userId': user.userId,
                            'specialistId': user.specialistId
                        },
                        'id': 1
                    }),
                    contentType: "application/json; charset=utf-8",
                    dataType: 'json'
                })
                .done(function (data) {
                    if (typeof data.error !== "undefined") {
                        new Noty({
                            type: 'error',
                            timeout: 6000,
                            text: data.error.message,
                            animation: {
                                open : 'animated fadeInRight',
                                close: 'animated fadeOutRight'
                            }
                        }).show();
                    }
                    if (typeof data.result !== "undefined") {
                        if (data.result.status == 'ok') {
                            _self.$set(user, 'information', data.result.information);
                        }
                        if (data.result.status == 'error') {
                            new Noty({
                                type: 'error',
                                timeout: 6000,
                                text: data.result.message,
                                animation: {
                                    open : 'animated fadeInRight',
                                    close: 'animated fadeOutRight'
                                }
                            }).show();
                        }
                    }
                })
                .always(function () {
                    _self.$set(user, 'isDisabledGetUserInformationBtn', false);
                });
            }
        }
    });
</script>