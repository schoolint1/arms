<div class="container mt-3" id="app-users" class="">
    <div class="row mb-3">
        <div class="col-4 form-group">
            <label for="inputUserSurname" class="form-label">Фамилия</label>
            <input type="text" class="form-control" id="inputUserSurname" v-model="user.surname" aria-describedby="emailHelp">
        </div>
        <div class="col-4 form-group">
            <label for="inputUserFirstname" class="form-label">Имя</label>
            <input type="text" class="form-control" id="inputUserFirstname" v-model="user.firstname" aria-describedby="emailHelp">
        </div>
        <div class="col-4 form-group">
            <label for="inputUserPatronymic" class="form-label">Отчество</label>
            <input type="text" class="form-control" id="inputUserPatronymic" v-model="user.patronymic" aria-describedby="emailHelp">
        </div>
    </div>
    <div class="row mb-3">
        <div class="col-4 form-group">
            <label for="inputUserGender" class="form-label">Пол</label>
            <select id="inputUserGender" class="form-control" v-model="user.gender">
                <option value="0">Пол не выбран</option>
                <option value="1">Мужской</option>
                <option value="2">Женский</option>
            </select>
        </div>
        <div class="col-4 form-group">
            <label for="inputUserBirthday" class="form-label">Дата рождения</label>
            <v-date-picker
                v-model="user.birthday"
                :model-config="modalDateConfig"
                :popover="{ placement: 'bottom' }">
                <template v-slot="{ inputValue, togglePopover }">
                    <div class="input-group">
                        <input type="text" readonly id="inputUserBirthday" class="form-control" :value="inputValue">
                        <div class="input-group-append">
                            <button class="btn btn-sm btn-primary" @click="togglePopover()">
                                <img src="/img/icon-calendar.svg" style="width: 1rem; height: 1rem; margin-bottom: 3px;" alt="Календарь">
                            </button>
                        </div>
                    </div>
                </template>
            </v-date-picker>
        </div>
    </div>
    <div class="row mb-3">
        <div class="col">
            <button type="button" class="btn btn-primary" v-on:click="onUpdateUser" :disabled="isSaveUserDisabel"><div class="spinner-border spinner-border-sm" role="status" aria-hidden="true" v-if="isSaveUserDisabel"><span class="sr-only">Loading...</span></div> Сохранить</button>
        </div>
    </div>
    
    <div class="row mb-3">
        <div class="col" style="line-height: 45px;">
            Группы: <template v-for="group in user.groups"><span class="inf-mark">{{ getGroup(group.groupId) }} <button type="button" v-on:click="onGroupDelete(group.id)"><img class="modal-icon" src="/img/icon_delete.png"></button></span></template> <button type="button" class="btn btn-outline-secondary" v-on:click="modalGroup.isShow = true"><img class="modal-icon" src="/img/icon_add.png"></button>
        </div>
    </div>
    <div class="row mb-3">
        <div class="col" style="line-height: 45px;">
            Доступ: <template v-for="userAccess in user.users"><span class="inf-mark">{{ userAccess.username }} <button type="button" v-on:click="onAccessDelete(userAccess.id)"><img class="modal-icon" src="/img/icon_delete.png"></button></span></template> <button type="button" class="btn btn-outline-secondary" v-on:click="onModalAccessShow"><img class="modal-icon" src="/img/icon_add.png"></button>
        </div>
    </div>
    <div class="row mb-3" v-if="isStudentGroupExist(user.groups)">
        <div class="col" style="line-height: 45px;">
            Классы: <template v-for="cls in user.classes"><span class="inf-mark">{{ cls.className }} <span class="inf-mark_sub">({{ cls.yearName }} уч. год)</span> <button type="button" v-on:click="onClassDelete(cls.id)"><img class="modal-icon" src="/img/icon_delete.png"></button></span></template> <button type="button" class="btn btn-outline-secondary" v-on:click="modalClass.isShow = true"><img class="modal-icon" src="/img/icon_add.png"></button>
        </div>
    </div>
    
    <modal v-if="modalGroup.isShow" @close="modalGroup.isShow = false">
        <!--
          you can use custom content here to overwrite
          default content
        -->
        <h5 slot="header">Добавить</h5>

        <div slot="body" class="row">
            <div class="col">
                <label for="modalGroupSelect">Группы</label>
                <select class="form-control" id="modalGroupSelect" v-model="modalGroup.groupId">
                    <option v-bind:value="group.id" v-for="group in groups">{{ group.name }}</option>
                </select>
            </div>
        </div>

        <template slot="footer">
            <button class="btn btn-primary" :disabled="modalGroup.isSave" @click="onGroupInsert"><div class="spinner-border spinner-border-sm" role="status" aria-hidden="true" v-if="modalGroup.isSave"><span class="sr-only">Loading...</span></div> Сохранить</button>
            <button class="btn btn-primary" @click="modalGroup.isShow = false">Закрыть</button>
        </template>
    </modal>
    
    <modal v-if="modalAccess.isShow" @close="modalAccess.isShow = false">
        <!--
          you can use custom content here to overwrite
          default content
        -->
        <h5 slot="header">Добавить</h5>

        <div slot="body" class="row">
            <div class="col">
                <div class="form-group">
                    <label for="modalAccessUsername">Имя пользователя</label>
                    <input type="text" class="form-control" id="modalAccessUsername" v-model="modalAccess.username">
                </div>
                <div class="form-group">
                    <label for="modalAccessPassword">Пароль</label>
                    <input type="password" class="form-control" id="modalAccessPassword" v-model="modalAccess.password">
                </div>
            </div>
        </div>

        <template slot="footer">
            <button class="btn btn-primary" :disabled="modalAccess.isSave" @click="onAccessInsert"><div class="spinner-border spinner-border-sm" role="status" aria-hidden="true" v-if="modalAccess.isSave"><span class="sr-only">Loading...</span></div> Сохранить</button>
            <button class="btn btn-primary" @click="modalAccess.isShow = false">Закрыть</button>
        </template>
    </modal>
    
    <modal v-if="modalClass.isShow" @close="modalClass.isShow = false">
        <!--
          you can use custom content here to overwrite
          default content
        -->
        <h5 slot="header">Добавить</h5>

        <div slot="body" class="row">
            <div class="col">
                <label for="modalClassSelect">Класс</label>
                <select class="form-control" id="modalGroupSelect" v-model="modalClass.classId">
                    <option v-bind:value="csl.id" v-for="csl in classes">{{ csl.name }}</option>
                </select>
            </div>
        </div>

        <template slot="footer">
            <button class="btn btn-primary" :disabled="modalClass.isSave" @click="onClassInsert"><div class="spinner-border spinner-border-sm" role="status" aria-hidden="true" v-if="modalClass.isSave"><span class="sr-only">Loading...</span></div> Сохранить</button>
            <button class="btn btn-primary" @click="modalClass.isShow = false">Закрыть</button>
        </template>
    </modal>
</div>

<script src="/assets/vue-components/modal/dist/build.js" type="text/javascript"></script>
<script type="text/javascript">
    var app2 = new Vue({
        el: '#app-users',
        data: {
            'groups': <?php echo json_encode($groups) ?>,
            'user': <?php echo json_encode($user) ?>,
            'classes': <?php echo json_encode($classes) ?>,
            isSaveUserDisabel: false,
            
            modalGroup: {
                isShow: false,
                isSave: false,
                groupId: 0
            },
                    
            modalAccess: {
                isShow: false,
                isSave: false,
                username: '',
                password: ''
            },
                    
            modalClass: {
                isShow: false,
                isSave: false,
                classId: 0
            },
            
            modalDateConfig: {
                type: 'string',
                mask: 'YYYY-MM-DD', // Uses 'iso' if missing
            }
        },
        methods: {
            isStudentGroupExist: function(userGroups) {
                for(let group in userGroups) {
                    if(userGroups[group].groupId == <?= $container->get('settings')['studentGroupId'] ?>) { // Номер группы ученика
                        return true;
                    }
                }
                return false;
            },
            getGroup: function(id) {
                for(let group in this.groups) {
                    if(this.groups[group].id == id) {
                        return this.groups[group].name;
                    }
                }
                return '';
            },
            onUpdateUser: function() {
                this.isSaveUserDisabel = true;
                var _self = this;
                $.ajax({
                    type: 'POST',
                    async: true,
                    url: '/api',
                    data: JSON.stringify({
                        'jsonrpc': '2.0',
                        'method': 'cfg_Personal_updateUser',
                        'params': {
                            'user': _self.user
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
                    _self.isSaveUserDisabel = false;
                });
            },
            onGroupDelete: function(recordId) {
                var _self = this;
                $.ajax({
                    type: 'POST',
                    async: true,
                    url: '/api',
                    data: JSON.stringify({
                        'jsonrpc': '2.0',
                        'method': 'cfg_Personal_deleteGroup',
                        'params': {
                            'id': recordId,
                            'userId': _self.user.id
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
                            _self.user.groups = data.result.groups;
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
            },
            onGroupInsert: function() {
                var _self = this;
                _self.modalGroup.isSave = true;
                $.ajax({
                    type: 'POST',
                    async: true,
                    url: '/api',
                    data: JSON.stringify({
                        'jsonrpc': '2.0',
                        'method': 'cfg_Personal_insertGroup',
                        'params': {
                            'userId': _self.user.id,
                            'groupId': _self.modalGroup.groupId
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
                            _self.user.groups = data.result.groups;
                            _self.modalGroup.isShow = false;
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
                    _self.modalGroup.isSave = false;
                });
            },
            onModalAccessShow: function() {
                this.modalAccess.username = '';
                this.modalAccess.password = '';
                this.modalAccess.isSave = false;
                this.modalAccess.isShow = true;
            },
            onAccessDelete: function(accessId) {
                var _self = this;
                $.ajax({
                    type: 'POST',
                    async: true,
                    url: '/api',
                    data: JSON.stringify({
                        'jsonrpc': '2.0',
                        'method': 'cfg_Personal_deleteAccess',
                        'params': {
                            'id': accessId,
                            'userId': _self.user.id
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
                            _self.user.users = data.result.users;
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
            },
            onAccessInsert: function() {
                var _self = this;
                _self.modalAccess.isSave = true;
                $.ajax({
                    type: 'POST',
                    async: true,
                    url: '/api',
                    data: JSON.stringify({
                        'jsonrpc': '2.0',
                        'method': 'cfg_Personal_insertAccess',
                        'params': {
                            'userId': _self.user.id,
                            'username': _self.modalAccess.username,
                            'password': _self.modalAccess.password
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
                            _self.user.users = data.result.users;
                            _self.modalAccess.isShow = false;
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
                    _self.modalAccess.isSave = false;
                });
            },
            onClassDelete: function(classId) {
                var _self = this;
                $.ajax({
                    type: 'POST',
                    async: true,
                    url: '/api',
                    data: JSON.stringify({
                        'jsonrpc': '2.0',
                        'method': 'cfg_Personal_deleteClasses',
                        'params': {
                            'id': classId,
                            'userId': _self.user.id
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
                            _self.user.classes = data.result.classes;
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
            },
            onClassInsert: function() {
                var _self = this;
                _self.modalClass.isSave = true;
                $.ajax({
                    type: 'POST',
                    async: true,
                    url: '/api',
                    data: JSON.stringify({
                        'jsonrpc': '2.0',
                        'method': 'cfg_Personal_insertClasses',
                        'params': {
                            'userId': _self.user.id,
                            'classId': _self.modalClass.classId
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
                            _self.user.classes = data.result.classes;
                            _self.modalClass.isShow = false;
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
                    _self.modalClass.isSave = false;
                });
            }
        }
    })
</script>