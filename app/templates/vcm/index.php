<div class="row mt-3" id="app-reports">
    <div class="col-3">
        <users 
            v-bind:users="users"
            v-on:select-user="onExtreportGetUserData"></users>
    </div>
    <div class="col-9">
        <div class="head-content">
            <div><span v-if="selectedUser" class="head-content__selectedUser-name">{{ selectedUser.name }}</span></div>
            <button type="button" class="btn btn-lg btn-primary" :disabled="isDisabledAddBtn" v-on:click="onModalExtreportNew">Добавить заключение</button>
        </div>
        <div class="d-flex justify-content-center" v-if="isLoad">
            <div class="spinner-border text-primary" role="status">
                <span class="sr-only">Loading...</span>
            </div>
        </div>
        <template v-for="(itemExtreport, indexExtreport) in extreports">
            <div class="extreport-item">
                <div>
                    <div>
                        Заключение № {{ itemExtreport.docNumber }} от {{ extreportDate(itemExtreport.docDate) }}
                        <a v-bind:href="'/vcomis/report-' + itemExtreport.id" class="btn btn-outline-primary btn-sm">открыть</a>
                    </div>
                    <div>
                        <span class="badge badge-primary badge-pill app-badget-sm" v-for="(itemSpecialist, indexSpecialist) in itemExtreport.specialistsId">{{ specialists[itemSpecialist] }}</span>
                    </div>
                </div>
                <div>
                    <div class="btn-group" role="group" aria-label="Управление протоколом">
                        <button type="button" class="btn btn-primary btn-sm" @click="onExtreportGet($event, indexExtreport)">Редактировать</button>
                        <button type="button" class="btn btn-danger btn-sm" :disabled="itemExtreport.isDisabled" @click="onExtreportDelete($event, indexExtreport)">Удалить</button>
                    </div>
                </div>
            </div>
        </template>
    </div>
    
    <modal v-if="modalExtreport.isShow" @close="modalExtreport.isShow = false">
        <!--
          you can use custom content here to overwrite
          default content
        -->
        <h5 slot="header">Добавить / Изменить</h5>

        <div slot="body" class="row">
            <div class="col">
                <v-date-picker
                    v-model="modalExtreport.paramDate"
                    :model-config="modalDateConfig"
                    :popover="{ placement: 'bottom' }">
                    <template v-slot="{ inputValue, togglePopover }">
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text">Дата заключения</span>
                            </div>
                            <input type="text" readonly :value="inputValue">
                            <div class="input-group-append">
                                <button class="btn btn-sm btn-primary" @click="togglePopover ()">
                                    <img src="/img/icon-calendar.svg" style="width: 1rem; height: 1rem; margin-bottom: 3px;" alt="Календарь">
                                </button>
                            </div>
                        </div>
                    </template>
                </v-date-picker>
            </div>
            <div class="col">
                <div class="input-group">
                    <div class="input-group-prepend">
                        <span class="input-group-text">Номер заключения</span>
                    </div>
                    <input type="text" class="form-control" placeholder="Номер" v-model="modalExtreport.paramNumber">
                </div>
            </div>
        </div>

        <template slot="footer">
            <button class="btn btn-primary" :disabled="modalExtreport.isSave" @click="onExtreportSaveData"><div class="spinner-border spinner-border-sm" role="status" aria-hidden="true" v-if="modalExtreport.isSave"><span class="sr-only">Loading...</span></div> Сохранить</button>
            <button class="btn btn-primary" @click="modalExtreport.isShow = false">Закрыть</button>
        </template>
    </modal>
</div>

<script src="/assets/vue-components/users/dist/build.js" type="text/javascript"></script>
<script src="/assets/vue-components/modal/dist/build.js" type="text/javascript"></script>
<script type="text/javascript">
    var app2 = new Vue({
        el: '#app-reports',
        data: {
            users: <?php echo json_encode($users) ?>,
            specialists: <?php echo json_encode($specialists) ?>,

            selectedUser: null,
            extreports: [],
            isLoad: false,

            modalExtreport: {
                isShow: false,
                isSave: false,
                paramDate: '<?=$container->get('session')->getDate()->format('Y-m-d') ?>',
                paramNumber: '',
                paramId: 0
            },
                    
            modalDateConfig: {
                type: 'string',
                mask: 'YYYY-MM-DD', // Uses 'iso' if missing
            }
        },
        computed: {
            isDisabledAddBtn() {
                return this.selectedUser == null ? true : false;
            }
        },
        methods: {
            extreportDate: function(date) {
                return DateTime.fromSQL(date).toFormat('dd.LL.yyyy');
            },
            onExtreportGetUserData: function (user) {
                this.selectedUser = user;
                var _self = this;
                this.isLoad = true;
                this.extreports = [];
                $.ajax({
                    type: 'POST',
                    async: true,
                    url: '/api',
                    data: JSON.stringify({
                        'jsonrpc': '2.0',
                        'method': 'vcm_Extreports_get',
                        'params': {
                            'userId': _self.selectedUser.id
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
                            _self.extreports = data.result.reports;
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
            onExtreportSaveData: function() {
                if(this.modalExtreport.paramId == 0) {
                    this.onExtreportAddData();
                } else {
                    this.onExtreportUpdateData();
                }
            },
            onExtreportUpdateData: function() {
                var _self = this;
                this.modalExtreport.isSave = true;
                $.ajax({
                    type: 'POST',
                    async: true,
                    url: '/api',
                    data: JSON.stringify({
                        'jsonrpc': '2.0',
                        'method': 'vcm_Extreports_update',
                        'params': {
                            'id': _self.modalExtreport.paramId,
                            'docNumber': _self.modalExtreport.paramNumber,
                            'docDate': _self.modalExtreport.paramDate
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
                            _self.modalExtreport.isShow = false;
                            _self.modalExtreport.paramNumber = '';
                            _self.modalExtreport.paramId = 0;
                            _self.onExtreportGetUserData(_self.selectedUser);
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
                    _self.modalExtreport.isSave = false;
                });
            },
            onExtreportAddData: function() {
                var _self = this;
                
                if(this.selectedUser == null) {
                    new Noty({
                        type: 'error',
                        timeout: 6000,
                        text: 'Обучающийся не выбран',
                        animation: {
                            open : 'animated fadeInRight',
                            close: 'animated fadeOutRight'
                        }
                    }).show();
                    return;
                }
                
                this.modalExtreport.isSave = true;
                $.ajax({
                    type: 'POST',
                    async: true,
                    url: '/api',
                    data: JSON.stringify({
                        'jsonrpc': '2.0',
                        'method': 'vcm_Extreports_add',
                        'params': {
                            'userId': _self.selectedUser.id,
                            'docNumber': _self.modalExtreport.paramNumber,
                            'docDate': _self.modalExtreport.paramDate
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
                            _self.modalExtreport.isShow = false;
                            _self.modalExtreport.paramNumber = '';
                            _self.modalExtreport.paramId = 0;
                            _self.onExtreportGetUserData(_self.selectedUser);
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
                    _self.modalExtreport.isSave = false;
                });
            },
            onModalExtreportNew: function() {
                this.modalExtreport.isShow = true;
                this.modalExtreport.paramId = 0;
            },
            onExtreportGet: function(event, index) {
                this.modalExtreport.paramDate = new Date(this.extreports[index].docDate);
                this.modalExtreport.paramNumber = this.extreports[index].docNumber;
                this.modalExtreport.paramId = this.extreports[index].id;
                this.modalExtreport.isShow = true;
            },
            onExtreportDelete: function(event, index) {
                if(!confirm('Удалить заключение?')) {
                    return;
                }
                var _self = this;
                var id = this.extreports[index].id;
                this.$set(this.extreports[index],'isDisabled', true);
                $.ajax({
                    type: 'POST',
                    async: true,
                    url: '/api',
                    data: JSON.stringify({
                        'jsonrpc': '2.0',
                        'method': 'vcm_Extreports_delete',
                        'params': {
                            'id': id,
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
                        _self.extreports[index].isDisabled = false;
                    }
                    if (typeof data.result !== "undefined") {
                        if (data.result.status == 'ok') {
                            _self.extreports.splice(index, 1);
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
                            _self.extreports[index].isDisabled = false;
                        }
                    }
                })
            }
        }
    })
</script>
