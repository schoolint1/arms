<?php
$specialistsConfig = $container->get('specialistsConfig');
if(array_key_exists($specialistId, $specialistsConfig)) {
    $specialistsConfig = $specialistsConfig[$specialistId];
} else {
    $specialistsConfig = null;
}
?>
<div class="row mt-3" id="app-reports">
    <div class="col-3">
        <users 
            v-bind:users="users"
            v-on:select-user="onReportGetUserData"></users>
    </div>
    <div class="col-9">
        <div class="head-content">
            <div><span v-if="selectedUser" class="head-content__selectedUser-name">{{ selectedUser.name }}</span></div>
            <button type="button" class="btn btn-lg btn-primary" :disabled="isDisabledAddBtn" v-on:click="onModalReportNew">Добавить заключение</button>
        </div>
        
        <div class="d-flex justify-content-center" v-if="isLoad">
            <div class="spinner-border text-primary" role="status">
                <span class="sr-only">Loading...</span>
            </div>
        </div>
        
        <div v-if="extreport.length" class="spc-extreport">
            <div style="font-size: 1.25rem; font-weight: bold;">Городская комиссия</div>
            <div v-for="itemExtreport in extreport">
                <div class="spc-extreport-title">
                    <span v-if="itemExtreport.isNeed == 1" class="badge badge-success app-badget"><img src="/img/icon_ok.svg" alt="Нужен" width="16"></span>
                    <span v-else class="badge badge-secondary app-badget"><img src="/img/icon_no.svg" alt="Не нужен" width="16"></span>
                    {{ itemExtreport.specialistName }}</div>
                <div class="extreport-recomendation">
                    {{ itemExtreport.recom }}
                </div>
            </div>
        </div>
        
        <template v-for="(itemReport, indexReport) in reports">
            <div class="intreport-item">
                <div class="intreport-item__head">
                    <div>
                        <span v-if="itemReport.isNeed == 1" class="badge badge-success app-badget"><img src="/img/icon_ok.svg" alt="Нужен" width="16"></span>
                        <span v-else class="badge badge-secondary app-badget"><img src="/img/icon_no.svg" alt="Не нужен" width="16"></span>
                        <span class="intreport-item__head__date">{{ reportDate(itemReport.docDate) }}</span>
                        <span>{{ reportType(itemReport.docType) }}</span>
                    </div>
                    <div>
                        <div class="btn-group" role="group" aria-label="Управление протоколом" v-if="itemReport.examUserId == userId">
                            <button type="button" class="btn btn-primary btn-sm" @click="onReportGet($event, indexReport)">Редактировать</button>
                            <button type="button" class="btn btn-danger btn-sm" :disabled="itemReport.isDisabled" @click="onReportDelete($event, indexReport)">Удалить</button>
                        </div>
                    </div>
                </div>
                <div>
                    {{ itemReport.val }}
                </div>
                <div v-if="itemReport.examUserId" class="text-right">
                    {{ itemReport.examUserName }}
                </div>
            </div>
        </template>
    </div>
    
    <modal v-if="modalReport.isShow" @close="modalReport.isShow = false">
        <!--
          you can use custom content here to overwrite
          default content
        -->
        <h5 slot="header">Добавить / Изменить</h5>

        <div slot="body">
            <div class="row align-items-center">
                <div class="col">
                   <v-date-picker
                        v-model="modalReport.param.docDate"
                        :min-date="minSchoolYearDate"
                        :max-date="maxSchoolYearDate"
                        :model-config="modalDateConfig"
                        :popover="{ placement: 'bottom' }">
                        <template v-slot="{ inputValue, togglePopover }">
                            <label for="modalReportDate" class="form-label">Дата заключения</label>
                            <div class="input-group">
                                <input type="text" readonly id="modalReportDate" class="form-control" :value="inputValue">
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
                    <label class="form-label">Нуждается ли в специалисте?</label>
                    <div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="modalReportSpecNeed" id="modalExtreportSpecNeed-NO" value="0" v-model="modalReport.param.isNeed">
                            <label class="form-check-label" for="modalReportSpecNeed-NO">
                                Не нуждается
                            </label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="modalReportSpecNeed" id="modalExtreportSpecNeed-YES" value="1" v-model="modalReport.param.isNeed">
                            <label class="form-check-label" for="modalReportSpecNeed-YES">
                                Нуждается
                            </label>
                        </div>
                    </div>
                </div> 
            </div>
<?php if(($specialistsConfig != null) && (count($specialistsConfig['columns']) > 0)): 
    foreach($specialistsConfig['columns'] AS $column): ?>
     <div class="row">
         <div class="col">
             <div class="form-group">
    <?php switch ($column['tag']) {
            case 'select':
                echo "<label for=\"modal-" . $column['name'] . "\" class=\"form-label\">" . $column['description'] . "</label>";
                echo "<select class=\"form-control\" v-model=\"modalReport.param." . $column['name'] . "\" id=\"modal-" . $column['name'] . "\">";
                foreach($column['options'] AS $optionIndex => $optionValue) {
                    echo "<option value=\"" . $optionIndex . "\">" . $optionValue . "</option>";
                }
                echo "</select>";
                break;

            default:
                break;
        }
    ?>
             </div>
         </div>
     </div>
<?php
    endforeach;
endif; ?>
            <div class="row">
                <div class="col">
                    <label for="modalReportVal" class="form-label">Заключение</label>
                    <textarea class="form-control" id="modalReportVal" rows="3" v-model="modalReport.param.val"></textarea>
                </div>
            </div>
        </div>

        <template slot="footer">
            <button class="btn btn-primary" :disabled="modalReport.isSave" @click="onReportSaveData"><div class="spinner-border spinner-border-sm" role="status" aria-hidden="true" v-if="modalReport.isSave"><span class="sr-only">Loading...</span></div> Сохранить</button>
            <button class="btn btn-primary" @click="modalReport.isShow = false">Закрыть</button>
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

            selectedUser: null,
            reports: [],
            extreport: [],
            isLoad: false,
            
            minSchoolYearDate: new Date('<?=  $container->get('session')->getSchoolYearBeginDate()->format('Y-m-d') ?>'),
            maxSchoolYearDate: new Date('<?=  $container->get('session')->getSchoolYearEndDate()->format('Y-m-d') ?>'),
            specialistId: <?= $specialistId ?>,
            userId: <?= $container->get('session')->getUser()->getId(); ?>,
            
            modalDateConfig: {
                type: 'string',
                mask: 'YYYY-MM-DD', // Uses 'iso' if missing
            },

            modalReport: {
                isShow: false,
                isSave: false,
                param: {
                    'docDate': '<?=$container->get('session')->getDate()->format('Y-m-d') ?>',
                    isNeed: null,
                    val: '',
<?php if(($specialistsConfig != null) && (count($specialistsConfig['columns']) > 0)): 
    foreach($specialistsConfig['columns'] AS $column):
        echo '\'' . $column['name'] . '\': ';
        if(is_string($column['default'])) 
            echo '\''. $column['default'] .'\',';
        else if(is_numeric($column['default']))
            echo $column['default'] . ',';
    endforeach;
 endif; ?>
                    id: 0
                }
            }
        },
        computed: {
            isDisabledAddBtn() {
                return this.selectedUser == null ? true : false;
            }
        },
        methods: {
            reportDate: function(date) {
                return DateTime.fromSQL(date).toFormat('dd.LL.yyyy');
            },
            reportType: function(typeId) {
                switch (typeId) {
                    case 0: return 'без нарушений';
                    case 1: return 'нарушение речи';
                    case 2: return 'нарушение письма';
                    case 3: return 'нарушение речи и письма';
                }
            },
            onReportGetUserData: function (user) {
                this.selectedUser = user;
                var _self = this;
                this.isLoad = true;
                this.reports = [];
                $.ajax({
                    type: 'POST',
                    async: true,
                    url: '/api',
                    data: JSON.stringify({
                        'jsonrpc': '2.0',
                        'method': 'spc_Reports_get',
                        'params': {
                            'userId': _self.selectedUser.id,
                            'specialistId': _self.specialistId
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
                            _self.reports = data.result.reports;
                            _self.extreport = data.result.extreport;
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
            onReportSaveData: function() {
                if(this.modalReport.param.isNeed == null) {
                    new Noty({
                        type: 'error',
                        timeout: 6000,
                        text: 'Не заполнено поле "Нуждается ли в специалисте?"',
                        animation: {
                            open : 'animated fadeInRight',
                            close: 'animated fadeOutRight'
                        }
                    }).show();
                    return;
                }
                if(this.modalReport.paramId == 0) {
                    this.onReportAddData();
                } else {
                    this.onReportUpdateData();
                }
            },
            onReportUpdateData: function() {
                var _self = this;
                this.modalReport.isSave = true;
                $.ajax({
                    type: 'POST',
                    async: true,
                    url: '/api',
                    data: JSON.stringify({
                        'jsonrpc': '2.0',
                        'method': 'spc_Reports_update',
                        'params': {
                            'userId': _self.selectedUser.id,
                            'specialistId': _self.specialistId,
                            'examUserId': _self.userId,
                            'param': _self.modalReport.param
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
                            _self.modalReport.isShow = false;
                            _self.modalReport.param.isNeed = 0;
                            _self.modalReport.param.val = '';
                            _self.modalReport.param.id = 0;
                            _self.onReportGetUserData(_self.selectedUser);
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
                    _self.modalReport.isSave = false;
                });
            },
            onReportAddData: function() {
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
                
                this.modalReport.isSave = true;
                $.ajax({
                    type: 'POST',
                    async: true,
                    url: '/api',
                    data: JSON.stringify({
                        'jsonrpc': '2.0',
                        'method': 'spc_Reports_add',
                        'params': {
                            'userId': _self.selectedUser.id,
                            'specialistId': _self.specialistId,
                            'examUserId': _self.userId,
                            'param': _self.modalReport.param
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
                            _self.modalReport.isShow = false;
                            _self.modalReport.param.isNeed = 0;
                            _self.modalReport.param.val = '';
                            _self.modalReport.param.id = 0;
                            _self.onReportGetUserData(_self.selectedUser);
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
                    _self.modalReport.isSave = false;
                });
            },
            onModalReportNew: function() {
                this.modalReport.isShow = true;
                this.modalReport.paramId = 0;
            },
            onReportGet: function(event, index) {
                this.modalReport.param = this.reports[index];
                this.modalReport.isShow = true;
            },
            onReportDelete: function(event, index) {
                if(!confirm('Удалить заключение?')) {
                    return;
                }
                var _self = this;
                var id = this.reports[index].id;
                this.$set(this.reports[index],'isDisabled', true);
                $.ajax({
                    type: 'POST',
                    async: true,
                    url: '/api',
                    data: JSON.stringify({
                        'jsonrpc': '2.0',
                        'method': 'spc_Reports_delete',
                        'params': {
                            'id': id,
                            'specialistId': _self.specialistId
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
                        _self.reports[index].isDisabled = false;
                    }
                    if (typeof data.result !== "undefined") {
                        if (data.result.status == 'ok') {
                            _self.reports.splice(index, 1);
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
                            _self.reports[index].isDisabled = false;
                        }
                    }
                })
            }
        }
    })
</script>
