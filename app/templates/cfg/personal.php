<div class="row mt-3" id="app-users">
    <div class="col-2">
        <h6 class="border-bottom border-gray pb-1 mb-0">Группы пользователей</h6>
        <div class="pt-2">
            <div class="btn btn-sm btn-block btn-primary mr-1"
                    v-for="(group, grouplIndex) in groups"
                    v-on:click="selectGroup($event, grouplIndex)"
                    v-bind:class="group.check ? 'btn-primary': 'btn-secondary'">
                {{ group.name }}
            </div>
        </div>
    </div>
    
    <div class="col-10">
        <div class="row pb-2">
            <div class="col-9">
                <div class="input-group">
                    <div class="input-group-prepend">
                        <div class="input-group-text">?</div>
                    </div>
                    <input type="text" class="form-control" id="inlineFormInputGroup" v-model="searchText" placeholder="Искать...">
                    <div class="input-group-prepend">
                        <button type="submit" class="btn btn-primary" v-on:click="searchUser($event)">Поиск</button>
                    </div>
                </div>
            </div>
            <div class="col-3 text-right">
                <div class="btn-group" role="group">
                    <button type="submit" class="btn btn-primary" v-on:click="onModalUserShow">Добавить...</button>
                    <button type="submit" class="btn btn-primary" v-on:click="onModalUserFronCSVShow">Добавить из файла...</button>
                </div>
            </div>
        </div>
        <div style="margin-bottom: 1em;">
            <div class="d-flex justify-content-center" v-if="isLoad">
                <div class="spinner-border text-primary" role="status">
                    <span class="sr-only">Loading...</span>
                </div>
            </div>
            <table class="table">
                <thead>
                    <tr>
                      <th scope="col">№</th>
                      <th scope="col">Фамилия</th>
                      <th scope="col">Имя</th>
                      <th scope="col">Отчество</th>
                      <th scope="col">Пол</th>
                      <th scope="col">Дата рождения</th>
                      <th scope="col" style="width: 210px;"></th>
                    </tr>
                </thead>
                <tbody>
                <template v-for="(user, indexUser) in users">
                    <tr class="personal-item">
                        <td>{{ user.id }}</td>
                        <td>{{ user.surname }}</td>
                        <td>{{ user.firstname }}</td>
                        <td>{{ user.patronymic }}</td>
                        <td>{{ getGender(user.gender) }}</td>
                        <td>{{ getBirthday(user.birthday) }}</td>
                        <td><span class="personal-item_edit"><a v-bind:href="'/config/personal-' + user.id" class="btn btn-primary btn-sm">Редактировать</a> <button class="btn btn-danger btn-sm" :disabled="user.isDelDisable" v-on:click="onDeleteUser(user)">Удалить</button></span></td>
                    </tr>
                </template>
                </tbody>
            </table>
            <template v-if="pages > 0">
            Страница: <div class="btn-group" role="group" aria-label="Basic example">
                <template v-for="i in pages">
                    <button type="button" class="btn" v-bind:class="((page+1) == i) ? 'btn-primary': 'btn-secondary'" v-on:click="selectPage($event, i)"> {{ i }}</button>
                </template>
            </div>
            </template>
        </div>
    </div>
    
    <modal v-if="modalUser.isShow" @close="modalUser.isShow = false">
        <!--
          you can use custom content here to overwrite
          default content
        -->
        <h5 slot="header">Добавить</h5>

        <div slot="body" class="row">
            <div class="col">
                <div class="row">
                    <div class="col-4 form-group">
                        <label for="inputUserSurname" class="form-label">Фамилия</label>
                        <input type="text" class="form-control" id="inputUserSurname" v-model="modalUser.user.surname" aria-describedby="emailHelp">
                    </div>
                    <div class="col-4 form-group">
                        <label for="inputUserFirstname" class="form-label">Имя</label>
                        <input type="text" class="form-control" id="inputUserFirstname" v-model="modalUser.user.firstname" aria-describedby="emailHelp">
                    </div>
                    <div class="col-4 form-group">
                        <label for="inputUserPatronymic" class="form-label">Отчество</label>
                        <input type="text" class="form-control" id="inputUserPatronymic" v-model="modalUser.user.patronymic" aria-describedby="emailHelp">
                    </div>
                </div>
                <div class="row">
                    <div class="col-4 form-group">
                        <label for="inputUserGender" class="form-label">Пол</label>
                        <select id="inputUserGender" class="form-control" v-model="modalUser.user.gender">
                            <option value="0">Пол не выбран</option>
                            <option value="1">Мужской</option>
                            <option value="2">Женский</option>
                        </select>
                    </div>
                    <div class="col-4 form-group">
                        <label for="inputUserBirthday" class="form-label">Дата рождения</label>
                        <v-date-picker
                            v-model="modalUser.user.birthday"
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
            </div>
        </div>

        <template slot="footer">
            <button class="btn btn-primary" :disabled="modalUser.isSave" @click="onUserInsert"><div class="spinner-border spinner-border-sm" role="status" aria-hidden="true" v-if="modalUser.isSave"><span class="sr-only">Loading...</span></div> Сохранить</button>
            <button class="btn btn-primary" @click="modalUser.isShow = false">Закрыть</button>
        </template>
    </modal>
    
    <modal v-if="modalUserFromCSV.isShow" @close="modalUserFromCSV.isShow = false">
        <!--
          you can use custom content here to overwrite
          default content
        -->
        <h5 slot="header">Добавить</h5>

        <div slot="body" class="row">
            <div class="col">
                <div class="row">
                    <div class="form-group">
                        <label for="fileCSV">Файл со списком людей в формате CSV</label>
                        <input type="file" id="fileCSV" class="form-control-file" accept=".csv" v-on:change="handleFileUpload($event.target.files)" />
                    </div>
                </div>
                <div class="row">
                    <pre class="col" style="overflow-y: auto;max-height: 200px;">{{ modalUserFromCSV.message }}</pre>
                </div>
            </div>
        </div>

        <template slot="footer">
            <button class="btn btn-primary" :disabled="modalUserFromCSV.isSave" @click="onUserFromCSVInsert"><div class="spinner-border spinner-border-sm" role="status" aria-hidden="true" v-if="modalUserFromCSV.isSave"><span class="sr-only">Loading...</span></div> Сохранить</button>
            <button class="btn btn-primary" @click="modalUserFromCSV.isShow = false">Закрыть</button>
        </template>
    </modal>
</div>

<script src="/assets/vue-components/modal/dist/build.js" type="text/javascript"></script>
<script type="text/javascript">
    var app2 = new Vue({
        el: '#app-users',
        data: {
            groups: <?php echo json_encode($groups) ?>,
            users: [],
            searchText: '',
            pages: 0,
            page: 0,
            isLoad: false,
            
            modalUser: {
                isShow: false,
                isSave: false,
                user: {
                    'surname': '',
                    'firstname': '',
                    'patronymic': '',
                    'gender': 0,
                    'birthday': null
                }
            },
                    
            modalUserFromCSV: {
                isShow: false,
                isSave: false,
                file: null,
                message: ''
            },
                    
            modalDateConfig: {
                type: 'string',
                mask: 'YYYY-MM-DD', // Uses 'iso' if missing
            }
        },
        computed: {
            
        },
        methods: {
            getGender: function(gender) {
                if(gender == 1) {
                    return 'м';
                }
                if(gender == 2) {
                    return 'ж';
                }
            },
            getBirthday: function(birthday) {
                let date = new Date(birthday);
                return date.toLocaleDateString("ru-RU");
            },
            selectGroup: function (event, grouplIndex) {
                this.$set(this.groups[grouplIndex], 'check', !this.groups[grouplIndex].check);
                //this.groups[grouplIndex].check = !this.groups[grouplIndex].check;
                this.page = 0;
                getUsers(this.groups, this.searchText, this.page);
            },
            selectPage: function (event, index) {
                this.page = index - 1;
                getUsers(this.groups, this.searchText, this.page);
            },
            searchUser:  function (event) {
                this.page = 0;
                getUsers(this.groups, this.searchText, this.page);
            },
            onModalUserFronCSVShow: function() {
                this.modalUserFromCSV.isSave = false;
                this.modalUserFromCSV.isShow = true;
                this.modalUserFromCSV.file = null;
            },
            handleFileUpload: function(file) {
                if(!file.length) {
                    return;
                }
                var _self = this;
                const result = toBase64(file[0]).catch(e => Error(e));
                if(result instanceof Error) {
                   new Noty({
                        type: 'error',
                        timeout: 6000,
                        text: result.message,
                        animation: {
                            open : 'animated fadeInRight',
                            close: 'animated fadeOutRight'
                        }
                    }).show();
                   return;
                }
                result.then((data) => {
                    _self.modalUserFromCSV.file = data;
                });
            },
            onUserFromCSVInsert: function() {
                var _self = this;
                _self.modalUserFromCSV.message = '';
                _self.modalUserFromCSV.isSave = true;
                $.ajax({
                    type: 'POST',
                    async: true,
                    url: '/api',
                    data: JSON.stringify({
                        'jsonrpc': '2.0',
                        'method': 'cfg_Personal_insertUserFromCSV',
                        'params': {
                            'file': _self.modalUserFromCSV.file
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
                            _self.modalUserFromCSV.isShow = false;
                        }
                        if (data.result.status == 'error') {
                            _self.modalUserFromCSV.message = data.result.message;
                            new Noty({
                                type: 'error',
                                timeout: 6000,
                                text: 'Ошибка загрузки',
                                animation: {
                                    open : 'animated fadeInRight',
                                    close: 'animated fadeOutRight'
                                }
                            }).show();
                        }
                    }
                })
                .always(function () {
                    _self.modalUserFromCSV.isSave = false;
                });
            },
            onModalUserShow: function() {
                this.modalUser.user.surname = '';
                this.modalUser.user.firstname = '';
                this.modalUser.user.patronymic = '';
                this.modalUser.user.gender = 0;
                this.modalUser.user.birthday = null;
                this.modalUser.isShow = true;
                this.modalUser.isSave = false;
            },
            onUserInsert: function() {
                var _self = this;
                _self.modalUser.isSave = true;
                $.ajax({
                    type: 'POST',
                    async: true,
                    url: '/api',
                    data: JSON.stringify({
                        'jsonrpc': '2.0',
                        'method': 'cfg_Personal_insertUser',
                        'params': {
                            'user': _self.modalUser.user
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
                            _self.modalUser.isShow = false;
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
                    _self.modalUser.isSave = false;
                });
            },
            onDeleteUser: function(user) {
                if (confirm("Удалить человека?") == false) {
                    return;
                }
                this.$set(user, 'isDelDisable', true);
                var _self = this;             
                $.ajax({
                    type: 'POST',
                    async: true,
                    url: '/api',
                    data: JSON.stringify({
                        'jsonrpc': '2.0',
                        'method': 'cfg_Personal_deleteUser',
                        'params': {
                            'id': user.id
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
                        _self.$set(user, 'isDelDisable', false);
                    }
                    if (typeof data.result !== "undefined") {
                        if (data.result.status == 'ok') {
                            for( let i = 0; i < _self.users.length; i++){
                                if(_self.users[i].id == user.id) {
                                    _self.users.splice(i, 1);
                                    break;
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
                            _self.$set(user, 'isDelDisable', false);
                        }
                    }
                })
            }
        }
    });
    
    const toBase64 = file => new Promise((resolve, reject) => {
        const reader = new FileReader();
        reader.readAsBinaryString(file);
        reader.onload = () => resolve(btoa(reader.result));
        reader.onerror = error => reject(error);
    });
    
    function getUsers(groups, searchText, page) {
        let g = [];
        groups.forEach(function(item, i, arr){
            if(item.check) {
                g.push(item.id);
            }
        });
        app2.isLoad = true;
        app2.users = [];
        $.ajax({
            type: 'POST',
            async: true,
            url: '/api',
            data: JSON.stringify({
                'jsonrpc': '2.0',
                'method': 'cfg_Users_get',
                'params': {
                    'groups': g,
                    'searchText': searchText,
                    'page': page
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
                    app2.isLoad = false;
                    app2.users = data.result.users;
                    app2.pages = data.result.pages;
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
            app2.isLoad = false;
        });
    }
</script>