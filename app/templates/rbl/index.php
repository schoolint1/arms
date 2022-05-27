<div class="row mt-3" id="app-rablist">
    <div class="col-3">
        <classes 
            v-bind:classes="classes"
            v-on:select-class="onGetUserList"></classes>
    </div>
    <div class="col-9">
        <div class="d-flex justify-content-center" v-if="isLoad">
            <div class="spinner-border text-primary" role="status">
                <span class="sr-only">Loading...</span>
            </div>
        </div>
        <table class="table">
            <thead>
                <tr>
                    <th>№</th>
                    <th>ФИО</th>
                    <th>Городская комиссия</th>
                    <th>Внутренняя комиссия</th>
                    <th>Назначения</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="(itemUser, indexUser) in users">
                    <td>{{ indexUser + 1 }}</td>
                    <td>{{ itemUser.name }}</td>
                    <td>
                        <template v-for="(itemExtreports, indexExtreports) in itemUser.extreports">
                            <div>
                                <span v-if="itemExtreports.isNeed" class="badge badge-success app-badget"><img src="/img/icon_ok.svg" alt="Нужен" width="16"></span>
                                <span v-else class="badge badge-secondary app-badget"><img src="/img/icon_no.svg" alt="Не нужен" width="16"></span> 
                                {{ itemExtreports.specialist }}
                            </div>
                        </template>
                    </td>
                    <td>
                        <template v-for="(itemIncreports, indexIntreports) in itemUser.increports">
                            <div>
                                <span v-if="itemIncreports.isNeed" class="badge badge-success app-badget"><img src="/img/icon_ok.svg" alt="Нужен" width="16"></span>
                                <span v-else class="badge badge-secondary app-badget"><img src="/img/icon_no.svg" alt="Не нужен" width="16"></span> 
                                {{ itemIncreports.specialist }}
                            </div>
                        </template>
                    </td>
                    <td>
                        <div class="btn-group" role="group">
                        <template v-for="(itemSpecialist, indexSpecialist) in specialists">
                            <button v-bind:class="[(itemUser.slcspecialists.indexOf(indexSpecialist) > -1) ? 'btn-primary' : 'btn-outline-secondary', 'btn', 'btn-sm']" v-on:click="onSetStatusInList($event, indexSpecialist, indexUser)">{{ itemSpecialist }}</button>
                        </template>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<!-- template for the modal component -->
<script type="text/x-template" id="modal-template">
    <transition name="modal">
        <div class="modal-mask">
            <div class="modal-wrapper">
                <div class="modal-container">

                    <div class="modal-header">
                        <slot name="header">
                            default header
                        </slot>
                    </div>

                    <div class="modal-body">
                        <slot name="body">
                            default body
                        </slot>
                    </div>

                    <div class="modal-footer">
                        <slot name="footer">
                            default footer
                            <button class="btn btn-primary" @click="$emit('close')">Закрыть</button>
                        </slot>
                    </div>
                </div>
            </div>
        </div>
    </transition>
</script>

<script src="/assets/vue-components/classes/dist/build.js" type="text/javascript"></script>
<script type="text/javascript">
    // register modal component
    Vue.component('modal', {
        template: '#modal-template'
    });

    var app2 = new Vue({
        el: '#app-rablist',
        data: {
            classes: <?php echo json_encode($classes) ?>,
            specialists: <?php echo json_encode($specialists) ?>,
            isLoad: false,
            selectedClass: null,
            users: null
        },
        computed: {
        },
        methods: {
            onGetUserList: function (clss) {
                this.selectedClass = clss;
                _self = this;
                _self.isLoad = true;
                $.ajax({
                    type: 'POST',
                    async: true,
                    url: '/api',
                    data: JSON.stringify({
                        'jsonrpc': '2.0',
                        'method': 'rbl_Users_get',
                        'params': {
                            'classId': _self.selectedClass.id
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
                            _self.users = data.result.users;
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
                    _self.isLoad = false;
                });
            },
            onSetStatusInList: function(event, specialistId, userIndex) {
                event.target.disabled = true;
                var _self = this;
                var status = (_self.users[userIndex].slcspecialists.indexOf(specialistId) > -1) ? 1 : -1;
                
                $.ajax({
                    type: 'POST',
                    async: true,
                    url: '/api',
                    data: JSON.stringify({
                        'jsonrpc': '2.0',
                        'method': (status == -1) ? 'rbl_Users_addToList' : 'rbl_Users_delFromList',
                        'params': {
                            'userId': _self.users[userIndex].id,
                            'specialistId': specialistId
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
                            if(status == -1) {
                                _self.users[userIndex].slcspecialists.push(specialistId)
                            }
                            if(status == 1) {
                                for(var i = 0; i < _self.users[userIndex].slcspecialists.length; i++){ 
                                    if (_self.users[userIndex].slcspecialists[i] == specialistId) { 
                                        _self.users[userIndex].slcspecialists.splice(i, 1); 
                                    }
                                }
                            }
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
                    event.target.disabled = false;
                });
            }
        }
    })
</script>
